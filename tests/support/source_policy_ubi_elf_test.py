"""Synthetic ELF bytes and verified virtual origins, never candidate execution."""
import copy
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import socket
import struct
import sys
import time
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('fixtures', Path(__file__).with_name('source_policy_ubi_alias_test.py'))
t = importlib.util.module_from_spec(spec); spec.loader.exec_module(t)
a, v = t.a, t.a.v
e = a.load('source_policy_ubi_elf_static.py', 'elf_contract')
spec = importlib.util.spec_from_file_location('entry_flags', ROOT/'scripts/ci/source-policy-legacy-ubi-static.py')
u = importlib.util.module_from_spec(spec)
with patch.object(socket, 'socket', side_effect=AssertionError('network forbidden')): spec.loader.exec_module(u)


def binary(needed=(), soname=None, interpreter=None, search=()):
    strings = bytearray(b'\0'); tags = []
    def text(value):
        index = len(strings); strings.extend(value.encode('ascii')+b'\0'); return index
    for value in needed: tags.append((1, text(value)))
    if soname is not None: tags.append((14, text(soname)))
    for value in search: tags.append((29, text(value)))
    tags = [(5, 0x401000), (10, len(strings))]+tags+[(0, 0)]
    data = bytearray(4096+len(strings)); count = 3 if interpreter else 2
    struct.pack_into('<16sHHIQQQIHHHHHH', data, 0, b'\x7fELF\x02\x01\x01'+b'\0'*9,
                     3, 62, 1, 0, 64, 0, 0, 64, 56, count, 0, 0, 0)
    programs = [(1, 4, 0, 0x400000, 0, len(data), len(data), 4096),
                (2, 4, 256, 0x400100, 0, len(tags)*16, len(tags)*16, 8)]
    if interpreter:
        raw = interpreter.encode('ascii')+b'\0'; data[1024:1024+len(raw)] = raw
        programs.insert(0, (3, 4, 1024, 0x400400, 0, len(raw), len(raw), 1))
    for index, row in enumerate(programs): struct.pack_into('<IIQQQQQQ', data, 64+56*index, *row)
    for index, row in enumerate(tags): struct.pack_into('<qQ', data, 256+16*index, *row)
    data[4096:] = strings
    return data


def namespace(changes=None, alias=False, hardlink=False, search=(), alias_target='liba.so.1.2'):
    files = {e.SERVER: binary(['liba.so.1'], interpreter=e.INTERPRETER, search=search),
             e.LOADER: binary(soname='ld-linux-x86-64.so.2'),
             'usr/lib64/'+alias_target if alias else 'usr/lib64/liba.so.1': binary(['libb.so.1'], soname='liba.so.1'),
             'usr/lib64/libb.so.1': binary(soname='libb.so.1')}
    if changes: changes(files)
    if hardlink: files['usr/lib64/libb-origin.so.1'] = files.pop('usr/lib64/libb.so.1')
    rows = [t.member('usr', t.tarfile.DIRTYPE), t.member('usr/lib64', t.tarfile.DIRTYPE), t.member('usr/sbin', t.tarfile.DIRTYPE)]
    rows += [t.member(path, data=data) for path, data in files.items()]
    if alias: rows.append(t.member('usr/lib64/liba.so.1', t.tarfile.SYMTYPE, alias_target))
    if hardlink:
        rows.append(t.member('usr/lib64/libb.so.1', t.tarfile.LNKTYPE, 'usr/lib64/libb-origin.so.1'))
    result = t.Contracts().scan(rows, elf_policy=True)
    meta = dict(result['metadata'], ordinal=0)
    m = v.VirtualClosure(dict(manifest=a.PIN, config=a.CONFIG, source='a'*64), [(meta['compressed'], meta['diff_id'])], hardlinks=True)
    m.charge(result['elf_work'], 0); m.apply(meta, result['records'], 1); m.finalize(2)
    observations = {'0:'+str(index): value for index, value in result['elf'].items()}
    return m, observations, files


class Contracts(unittest.TestCase):
    def test_real_byte_parser_positive_and_no_runtime_claim(self):
        data = binary(['liba.so.1'], interpreter=e.INTERPRETER)
        result = e.parse(data)
        self.assertEqual(hashlib.sha256(data).hexdigest(), result['sha256'])
        self.assertEqual(['liba.so.1'], result['needed']); self.assertEqual(e.INTERPRETER, result['interpreter'])
        self.assertEqual(64, result['bits']); self.assertFalse(result['executed'])
        self.assertFalse(result['dependency_closure_verified'])

    def test_header_program_and_segment_bounds_refuse(self):
        for offset, fmt, value in [(4, '<B', 1), (5, '<B', 2), (18, '<H', 3), (20, '<I', 0),
                                   (32, '<Q', 999999), (52, '<H', 63), (54, '<H', 1), (56, '<H', 129),
                                   (64+32, '<Q', 999999), (64+40, '<Q', 1), (64+48, '<Q', 3)]:
            data = binary(); struct.pack_into(fmt, data, offset, value)
            with self.assertRaises(e.Refusal): e.parse(data)
        for data in (b'not ELF', object(), binary()[:63]):
            with self.assertRaises(e.Refusal): e.parse(data)

    def test_dynamic_table_mapping_strings_and_interpreter_refuse(self):
        for offset, fmt, value in [(256+8, '<Q', 0x900000), (256+24, '<Q', 2**32),
                                   (256+2*16+8, '<Q', 999999), (4096+1, '<B', 255)]:
            data = binary(['liba.so.1']); struct.pack_into(fmt, data, offset, value)
            with self.assertRaises(e.Refusal): e.parse(data)
        data = binary(interpreter=e.INTERPRETER); data[1024] = 0
        with self.assertRaises(e.Refusal): e.parse(data)
        data = binary(['liba.so.1']); struct.pack_into('<q', data, 288, 5)
        with self.assertRaisesRegex(e.Refusal, 'elf-duplicate-tag'): e.parse(data)
        data = binary(interpreter=e.INTERPRETER); struct.pack_into('<I', data, 64+2*56, 3)
        with self.assertRaisesRegex(e.Refusal, 'elf-interpreter'): e.parse(data)
        with self.assertRaises(e.Refusal): e.parse(binary(), deadline=0)
        for tag in (0x6ffffefa, 0x6ffffefb, 0x6ffffefc, 0x7ffffffd, 0x7fffffff):
            data = binary(['liba.so.1']); struct.pack_into('<q', data, 288, tag)
            parsed = e.parse(data); self.assertTrue(parsed['unsupported_features'])
            with self.assertRaisesRegex(e.Refusal, 'elf-dependency-feature-policy'): e.object_policy(e.LOADER, parsed)
        for tag, value in ((30, 1), (0x6ffffffb, 0x800)):
            data = binary(['liba.so.1']); struct.pack_into('<qQ', data, 288, tag, value)
            parsed = e.parse(data); self.assertTrue(parsed['unsupported_features'])
            with self.assertRaisesRegex(e.Refusal, 'elf-dependency-feature-policy'): e.object_policy(e.LOADER, parsed)

    def test_actual_collector_to_graph_positive_and_fixed_same_directory_alias(self):
        m, observations, _ = namespace(alias=True)
        result = e.graph(m, observations, time.monotonic()+5)
        self.assertEqual('dt-needed-closure', result['status']); self.assertEqual(4, result['objects'])
        self.assertEqual(3, result['edges']); self.assertEqual(1, result['provider_alias_count'])
        self.assertTrue(result['static_dependency_closure_verified']); self.assertFalse(result['runtime_grant'])
        self.assertEqual('unestablished', result['runtime_profile'])
        m, observations, _ = namespace(alias=True, hardlink=True)
        result = e.graph(m, observations, time.monotonic()+5)
        self.assertEqual(4, result['objects']); self.assertEqual(3, result['edges'])
        self.assertFalse(result['runtime_grant'])

    def test_missing_provider_soname_search_and_interpreter_are_refusals(self):
        changes = [lambda files: files.pop('usr/lib64/libb.so.1'),
                   lambda files: files.update({'usr/lib64/liba.so.1': binary(soname='libwrong.so.1')}),
                   lambda files: files.update({e.SERVER: binary(interpreter='/host/loader')})]
        for change in changes:
            m, observations, _ = namespace(change)
            with self.assertRaises((e.Refusal, v.Refusal)): e.graph(m, observations, time.monotonic()+5)
        for search in ('', '$ORIGIN', '/usr/lib64'):
            m, observations, _ = namespace(search=[search])
            with self.assertRaisesRegex(e.Refusal, 'elf-search-policy') as caught: e.graph(m, observations, time.monotonic()+5)
            self.assertIn('roots', caught.exception.elf_facts)
            if search: self.assertNotIn(search, json.dumps(caught.exception.elf_facts))
        m, observations, _ = namespace(search=['$ORIGIN']); errors = []; original = e.require
        def watched(ok, code):
            try: original(ok, code)
            except e.Refusal as error: errors.append(error); raise
        with patch.object(e, 'require', watched), patch.object(e, 'capture_failure', side_effect=RuntimeError('diagnostics only')):
            with self.assertRaisesRegex(e.Refusal, 'elf-search-policy') as caught: e.graph(m, observations, time.monotonic()+5)
        self.assertIs(errors[0], caught.exception); self.assertIsNone(caught.exception.elf_facts)
        error = e.Refusal('elf-search-policy')
        e.capture_failure(error, 'dependency-graph', e.SERVER, None, {'data': 'x'*33000})
        self.assertTrue(error.elf_facts['withheld']); first = error.elf_facts
        e.capture_failure(error, 'other', e.LOADER, None, {}); self.assertIs(first, error.elf_facts)

    def test_observation_hash_origin_and_primitive_shape_are_bound(self):
        for key, value in [('sha256', 'a'*64), ('bytes', 1), ('needed', [object()])]:
            m, observations, _ = namespace(); bad = copy.deepcopy(observations); bad['0:3'][key] = value
            with self.assertRaises(e.Refusal): e.graph(m, bad, time.monotonic()+5)
        m, observations, _ = namespace()
        with self.assertRaises((e.Refusal, v.Refusal)): e.graph(m, {}, time.monotonic()+5)

    def test_provider_alias_cannot_escape_chain_or_change_its_library_family(self):
        for target in ('../outside', '/host/file', 'libforeign.so.2', 'liba.so.1', 'liba.so.debug'):
            rows = [t.member('usr', t.tarfile.DIRTYPE), t.member('usr/lib64', t.tarfile.DIRTYPE),
                    t.member('usr/lib64/liba.so.1', t.tarfile.SYMTYPE, target)]
            result = t.Contracts().scan(rows)
            meta = dict(result['metadata'], ordinal=0)
            m = v.VirtualClosure(dict(manifest=a.PIN, config=a.CONFIG, source='a'*64), [(meta['compressed'], meta['diff_id'])])
            m.apply(meta, result['records'], 0); m.finalize(1)
            with self.assertRaises((e.Refusal, v.Refusal)): e.provider(m, 'liba.so.1', time.monotonic()+5)

    def test_cycles_and_independent_work_limits_still_refuse(self):
        m, observations, _ = namespace(lambda files: files.update({'usr/lib64/libb.so.1': binary(['liba.so.1'], soname='libb.so.1')}))
        with self.assertRaises((e.Refusal, v.Refusal)): e.graph(m, observations, time.monotonic()+5)
        m, observations, _ = namespace(); m._work = v.WORK_LIMIT
        with self.assertRaises(v.Refusal): e.graph(m, observations, time.monotonic()+5)

    def test_collector_bad_diffid_never_returns_observations(self):
        with self.assertRaises(a.s.Refusal):
            t.Contracts().scan([t.member(e.SERVER, data=binary(interpreter=e.INTERPRETER))],
                               elf_policy=True, diff_id='sha256:'+'0'*64)
        with self.assertRaises(a.s.Refusal):
            t.Contracts().scan([t.member('usr/lib64/liba.so.1', data=binary(soname='liba.so.1'))],
                               elf_policy=True, budget=dict(entries=0, expanded=0, deadline=time.monotonic()+5, elf_objects=256))
        for field, cap in (('elf_work', 2097152), ('elf_metadata', 2*e.MIB)):
            data = binary(soname='liba.so.1'); parsed = e.parse(data)
            used = parsed['work'] if field == 'elf_work' else len(json.dumps(parsed, ensure_ascii=True).encode('ascii'))
            for initial, accepted in ((cap-used, True), (cap, False)):
                budget = dict(entries=0, expanded=0, deadline=time.monotonic()+5); budget[field] = initial
                if accepted:
                    result = t.Contracts().scan([t.member('usr/lib64/liba.so.1', data=data)], elf_policy=True, budget=budget)
                    self.assertEqual(cap, budget[field]); self.assertEqual(1, len(result['elf']))
                else:
                    with self.assertRaisesRegex(a.s.Refusal, 'elf-observation-bound'):
                        t.Contracts().scan([t.member('usr/lib64/liba.so.1', data=data)], elf_policy=True, budget=budget)

    def test_exact_graph_edge_budget_and_inspection_phase_are_discriminating(self):
        def dense(remove):
            def populate(files):
                files.clear(); files[e.SERVER] = binary(['libn0.so.1'], interpreter=e.INTERPRETER)
                files[e.LOADER] = binary(soname='ld-linux-x86-64.so.2')
                for i in range(97):
                    needed = ['libn'+str(j)+'.so.1' for j in range(i+1, min(i+65,97))]
                    if i == 0: needed = needed[:len(needed)-remove]
                    files['usr/lib64/libn'+str(i)+'.so.1'] = binary(needed, soname='libn'+str(i)+'.so.1')
            return populate
        m, observations, _ = namespace(dense(34)); result = e.graph(m, observations, time.monotonic()+5)
        self.assertEqual(4096, result['edges']); self.assertEqual(99, result['objects'])
        m, observations, _ = namespace(dense(0))
        with self.assertRaisesRegex(e.Refusal, 'elf-graph-bound'): e.graph(m, observations, time.monotonic()+5)
        m = v.VirtualClosure(dict(manifest=a.PIN, config=a.CONFIG, source='a'*64), [('a'*64,'b'*64)])
        with self.assertRaisesRegex(v.Refusal, 'inspection-phase'): m.inspect(e.SERVER, 0)
        for cost in (-1, True, v.WORK_LIMIT+1, object()):
            with self.assertRaisesRegex(v.Refusal, 'inspection-work'): m.charge(cost, 1)
        with self.assertRaisesRegex(e.Refusal, 'elf-deadline'): e.clock(float('inf'))

    def test_semantic_hash_and_search_guard_controls_are_assertion_killed(self):
        original = e.require
        for code, operation in [('elf-search-policy', lambda: e.object_policy(e.SERVER, e.parse(binary(interpreter=e.INTERPRETER, search=['$ORIGIN'])))),
                                ('elf-content-origin', self.bad_hash_binding),
                                ('elf-provider-alias', lambda: e.graph(*namespace(alias=True, alias_target='libforeign.so.1')[:2], time.monotonic()+5)),
                                ('elf-provider-soname', lambda: e.graph(*namespace(lambda files: files.update({'usr/lib64/liba.so.1': binary(soname='libwrong.so.1')}))[:2], time.monotonic()+5))]:
            with self.assertRaises((e.Refusal, v.Refusal)): operation()
            def bypass(ok, reason, code=code):
                if reason != code: original(ok, reason)
            with patch.object(e, 'require', bypass):
                with self.assertRaises(AssertionError):
                    with self.assertRaises((e.Refusal, v.Refusal)): operation()

    def bad_hash_binding(self):
        m, observations, _ = namespace(); observations['0:3']['sha256'] = 'a'*64
        return e.bound_metadata(m.inspect(e.SERVER, 3), observations, e.SERVER)

    def test_no_io_linker_or_handle_and_inspection_copy_isolation(self):
        import os
        import subprocess
        m, observations, _ = namespace()
        with patch('builtins.open', side_effect=AssertionError('file IO')), patch.object(socket, 'socket', side_effect=AssertionError('network')), \
             patch.object(subprocess, 'Popen', side_effect=AssertionError('execution')), patch.object(os, 'stat', side_effect=AssertionError('host stat')):
            result = e.graph(m, observations, time.monotonic()+5)
            self.assertFalse(result['runtime_grant']); self.assertNotIn('handle', result)
            first = m.inspect(e.SERVER, m._last+1); first['sha256'] = 'b'*64
            self.assertNotEqual('b'*64, m.inspect(e.SERVER, m._last+1)['sha256'])

    def test_fixed_selected_hash_admission_is_only_data_and_discriminates(self):
        roles = {e.SERVER: dict(canonical=e.SERVER, sha256=e.ROOT_HASHES[e.SERVER]),
                 'lib64/ld-linux-x86-64.so.2': dict(canonical=e.LOADER, sha256=e.ROOT_HASHES[e.LOADER])}
        e.admit_selected(roles)
        for key in roles:
            bad = copy.deepcopy(roles); bad[key]['sha256'] = '0'*64
            with self.assertRaises(e.Refusal): e.admit_selected(bad)

    def test_actual_cli_mode_flags_are_pure_and_guard_removal_is_killed(self):
        for arg, alias, elf, expected in [('--approved-static-prerequisite','false','false',(False,False)),
                                          ('--approved-alias-static-prerequisite','true','false',(True,False)),
                                          ('--approved-elf-static-prerequisite','true','true',(True,True))]:
            self.assertEqual(expected, u.policy_flags([arg], alias, elf))
        for args, alias, elf in [(['--approved-static-prerequisite'],'false','true'),
                                  (['--approved-elf-static-prerequisite'],'true','false'),
                                  (['--approved-elf-static-prerequisite'],'false','true'),
                                  (['--approved-elf-static-prerequisite'],True,'true'),
                                  (['--cleanup'],'true','true'), ([], 'false','false'), ([object()],'false','false')]:
            with self.assertRaises(u.s.Refusal): u.policy_flags(args, alias, elf)
        original = u.s.require
        with patch.object(u.s, 'require', lambda ok, code: None if code == 'explicit-elf-mode' else original(ok, code)):
            with self.assertRaises(AssertionError):
                with self.assertRaises(u.s.Refusal): u.policy_flags(['--approved-elf-static-prerequisite'], 'true', 'false')


if __name__ == '__main__':
    output = io.StringIO(); result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun, failures=len(result.failures), errors=len(result.errors), network_calls=0)), flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(output.getvalue().encode('utf-8', 'backslashreplace')[:3000])
    sys.exit(0 if result.wasSuccessful() else 1)
