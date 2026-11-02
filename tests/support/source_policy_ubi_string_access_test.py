"""Synthetic byte/grant tests only. No artifact, native or installed-site work."""
import ast
import copy
import importlib.util
import inspect
import io
import json
from pathlib import Path
import struct
import sys
import tracemalloc
import unittest
from unittest.mock import patch

spec = importlib.util.spec_from_file_location('elf_fixtures', Path(__file__).with_name('source_policy_ubi_elf_test.py'))
t = importlib.util.module_from_spec(spec); spec.loader.exec_module(t)
e = t.e


def image(extent=1084894, needed=('liba.so.1',), soname=None, search=(), total=None):
    prefix = t.binary(needed, soname=soname, search=search)
    data = bytearray(total if total is not None else 4096+extent)
    data[:len(prefix)] = prefix
    struct.pack_into('<Q', data, 280, extent)
    for offset in (96,104): struct.pack_into('<Q', data, offset, len(data))
    return data


def bounded(data, remaining=2097152, **options):
    return e.parse(data, bounded_strings=True, remaining_work=remaining, **options)


def mutated(function, transform, additions=None):
    tree = ast.parse(inspect.getsource(function)); transform(tree)
    namespace = dict(function.__globals__)
    if additions: namespace.update(additions)
    exec(compile(ast.fix_missing_locations(tree), '<owned-in-memory-semantic-control>', 'exec'), namespace)
    return namespace[function.__name__]


def observed_access(change=None):
    finds, slices = [], []
    def find(data, pattern, start, end):
        finds.append((start,end)); return data.find(pattern,start,end)
    def sliced(data, start, end):
        slices.append((start,end)); return data[start:end]
    class Witness(ast.NodeTransformer):
        def visit_Call(self, node):
            self.generic_visit(node)
            if isinstance(node.func,ast.Attribute) and isinstance(node.func.value,ast.Name) and node.func.value.id == 'data' and node.func.attr == 'find':
                return ast.copy_location(ast.Call(ast.Name('_witness_find',ast.Load()),[ast.Name('data',ast.Load())]+node.args,[]),node)
            return node
        def visit_Subscript(self, node):
            self.generic_visit(node)
            if isinstance(node.value,ast.Name) and node.value.id == 'data' and isinstance(node.slice,ast.Slice):
                return ast.copy_location(ast.Call(ast.Name('_witness_slice',ast.Load()),[node.value,node.slice.lower,node.slice.upper],[]),node)
            return node
    def instrument(tree):
        if change: change(tree)
        Witness().visit(tree)
    return mutated(e._bounded_text,instrument,{'_witness_find':find,'_witness_slice':sliced}), finds, slices


class Contracts(unittest.TestCase):
    def test_explicit_large_positive_preserves_strict_default_and_wrapper_boundary(self):
        data = image()
        with self.assertRaisesRegex(e.Refusal, '^elf-string-table$'): e.parse(data)
        result = bounded(data)
        self.assertEqual('ADBUELFSA1', result['format']); self.assertEqual(['liba.so.1'], result['metadata']['needed'])
        self.assertFalse(result['runtime_grant']); self.assertTrue(result['cost']['complete'])
        self.assertEqual(1084894, result['cost']['extent']); self.assertEqual(result['metadata']['work'], result['cost']['used'])
        with self.assertRaisesRegex(e.Refusal, 'elf-metadata'): e.metadata(result)
        for extent in (16, e.MIB):
            data = image(extent); old = e.parse(data); new = bounded(data)['metadata']
            self.assertEqual({k:v for k,v in old.items() if k != 'work'}, {k:v for k,v in new.items() if k != 'work'})

    def test_typed_opt_in_grants_and_deadline_reject_without_magic(self):
        class Hostile:
            def __bool__(self): raise AssertionError('magic bool')
            def __int__(self): raise AssertionError('magic int')
        for mode in (1, 'true', None, Hostile()):
            with self.assertRaisesRegex(e.Refusal, 'elf-string-access-mode'): e.parse(image(16), bounded_strings=mode)
        for remaining in (None, True, -1, 2097153, float('inf'), '100', Hostile()):
            with self.assertRaisesRegex(e.Refusal, 'elf-string-access-budget'): bounded(image(16), remaining)
        with self.assertRaisesRegex(e.Refusal, 'elf-string-access-budget'): e.parse(image(), remaining_work=100)
        with self.assertRaisesRegex(e.Refusal, 'elf-string-access-work') as caught: bounded(image(16), 0)
        self.assertEqual(0, caught.exception.string_access_facts['used'])
        with self.assertRaisesRegex(e.Refusal, 'elf-string-access-deadline'): bounded(image(16), deadline=0)
        with patch.object(e.time,'monotonic',side_effect=[1,1,1,11]):
            with self.assertRaisesRegex(e.Refusal,'elf-string-access-deadline') as caught: bounded(image(16),deadline=10)
        self.assertGreater(caught.exception.string_access_facts['used'],0)
        for bad in (True, float('nan'), float('inf'), Hostile()):
            with self.assertRaisesRegex(e.Refusal, 'elf-deadline'): bounded(image(16), deadline=bad)

    def test_whole_extent_mapping_refuses_wrap_eof_memory_and_ambiguity(self):
        for mutation in ('eof','memory','wrap','zero','ambiguous'):
            data = image()
            if mutation == 'eof': struct.pack_into('<Q', data, 280, len(data))
            if mutation == 'memory': struct.pack_into('<Q', data, 96, 4096+32)
            if mutation == 'wrap': struct.pack_into('<Q', data, 264, 2**64-8)
            if mutation == 'zero': struct.pack_into('<Q', data, 280, 0)
            if mutation == 'ambiguous':
                struct.pack_into('<H', data, 56, 3)
                struct.pack_into('<IIQQQQQQ', data, 176, 1,4,4096,0x401000,0,len(data)-4096,len(data)-4096,4096)
            with self.assertRaises(e.Refusal): bounded(data)
        for index in (4096, len(image())-1):
            data = image(); data[index] = 88
            with self.assertRaisesRegex(e.Refusal, 'elf-string-access-boundary'): bounded(data)

    def test_string_offsets_suffixes_empty_rules_and_exact_window(self):
        data = image(512, needed=(), search=['x'*240]); self.assertEqual(['x'*240], bounded(data)['metadata']['search_paths'])
        with self.assertRaisesRegex(e.Refusal, 'elf-string-length'): bounded(image(512, needed=(), search=['x'*241]))
        data = image(64); struct.pack_into('<Q', data, 296, 0)
        with self.assertRaisesRegex(e.Refusal, 'elf-string-ascii'): bounded(data)
        data = image(64, needed=(), search=['x']); struct.pack_into('<Q', data, 296, 63)
        self.assertEqual([''], bounded(data)['metadata']['search_paths'])
        data = image(64); struct.pack_into('<Q', data, 296, 64)
        with self.assertRaisesRegex(e.Refusal, 'elf-string-index'): bounded(data)
        data = image(64); data[4097:4110] = b'prefixliba.so'; data[4110:4113] = b'.1\0'
        struct.pack_into('<Q', data, 296, 7)
        self.assertEqual(['liba.so.1'], bounded(data)['metadata']['needed'])
        data = image(512, needed=(), search=['x'*241]); data[4096+512-1] = 0
        with self.assertRaisesRegex(e.Refusal, 'elf-string-length'): bounded(data)

    def test_exact_precharge_and_sequential_grants_have_no_reset_or_refund(self):
        data = image(64); result = bounded(data); cost = result['cost']['used']
        self.assertEqual(cost, bounded(data, cost)['cost']['used'])
        with self.assertRaisesRegex(e.Refusal, 'elf-string-access-work') as caught: bounded(data, cost-1)
        self.assertLessEqual(caught.exception.string_access_facts['used'], cost-1)
        self.assertFalse(caught.exception.string_access_facts['complete'])
        remaining = 2*cost
        for _ in range(2): remaining -= bounded(data, remaining)['cost']['used']
        self.assertEqual(0, remaining)
        with self.assertRaises(e.Refusal): bounded(data, remaining)
        meter = e._AccessCost(0, None)
        with self.assertRaises(e.Refusal): meter.spend('search_bytes', 1)
        self.assertEqual(0, meter.used)
        actual, finds, slices = observed_access()
        with self.assertRaisesRegex(e.Refusal,'elf-string-access-work'):
            actual(b'\0abc\0',0,5,1,False,e._AccessCost(4,None))
        self.assertEqual([],finds); self.assertEqual([],slices)
        with self.assertRaisesRegex(e.Refusal,'elf-string-access-work'):
            actual(b'\0abc\0',0,5,1,False,e._AccessCost(13,None))
        self.assertEqual([(1,5)],finds); self.assertEqual([],slices)

    def test_large_unused_interiors_do_not_create_a_full_table_copy(self):
        data = image(6*e.MIB)
        tracemalloc.start()
        try:
            result = bounded(data); peak = tracemalloc.get_traced_memory()[1]
        finally: tracemalloc.stop()
        self.assertLess(peak, 128*1024, 'A full-table/suffix copy must not be hidden behind small counters.')
        self.assertLessEqual(result['cost']['search_bytes'], 241)
        self.assertEqual(len('liba.so.1'), result['cost']['copy_bytes'])
        self.assertEqual(result['cost']['copy_bytes'], result['cost']['ascii_bytes'])
        self.assertEqual(result['cost']['copy_bytes'], result['cost']['decode_bytes'])
        actual, finds, slices = observed_access()
        with self.assertRaisesRegex(e.Refusal,'elf-string-length'):
            actual(b'\0'+b'x'*1024+b'\0',0,1026,1,False,e._AccessCost(2097152,None))
        self.assertEqual([(1,242)],finds); self.assertEqual([],slices)

    def test_maximum_existing_body_and_extent_limits_remain_bounded(self):
        data = image(128*e.MIB-4096, total=128*e.MIB)
        result = bounded(data); self.assertEqual(128*e.MIB, result['metadata']['bytes'])
        self.assertLessEqual(result['cost']['search_bytes'], 241)
        data.append(0)
        with self.assertRaisesRegex(e.Refusal, 'elf-header'): bounded(data)

    def test_cost_receipt_shape_and_same_first_failure_are_preserved(self):
        result = bounded(image(64)); good = result['cost']
        self.assertEqual(good, e.access_receipt(good))
        for key, value in [('used',True), ('used',good['granted']+1), ('extent',128*e.MIB+1),
                           ('runtime_grant',True), ('complete',1), ('search_bytes',16148), ('requests',68), ('format','secret')]:
            self.assertIsNone(e.access_receipt(dict(good, **{key:value})))
        self.assertIsNone(e.access_receipt(dict(good, secret='do-not-log')))
        for key in good: self.assertIsNone(e.access_receipt({k:v for k,v in good.items() if k != key}))
        for malformed in (None, [], {'secret':object()}, {str(i):0 for i in range(100)}): self.assertIsNone(e.access_receipt(malformed))
        class Hostile:
            def __eq__(self, other): raise AssertionError('receipt magic')
        for key,value in [('format',Hostile()),('granted',float('nan')),('extent',Hostile())]:
            self.assertIsNone(e.access_receipt(dict(good,**{key:value})))
        copied = e.access_receipt(good); copied['used'] = 0
        self.assertNotEqual(copied['used'], good['used'])
        original = e.require; seen = []
        def watched(ok, reason):
            try: original(ok, reason)
            except e.Refusal as error: seen.append(error); raise
        data = image(); data[-1] = 88
        with patch.object(e, 'require', watched), patch.object(e._AccessCost, 'receipt', side_effect=RuntimeError('do-not-log')):
            with self.assertRaisesRegex(e.Refusal, 'elf-string-access-boundary') as caught: bounded(data)
        self.assertIs(seen[0], caught.exception); self.assertIsNone(caught.exception.string_access_facts)

    def test_origin_provider_and_feature_guards_are_not_replaced_by_cost_data(self):
        model, observations, files = t.namespace()
        result = bounded(files[e.SERVER]); meta = result['metadata']
        self.assertEqual(observations['0:3']['sha256'], meta['sha256'])
        bad = copy.deepcopy(meta); bad['sha256'] = 'a'*64
        with self.assertRaisesRegex(e.Refusal, 'elf-content-origin'):
            e.bound_metadata(model.inspect(e.SERVER, 3), {'0:3':bad}, e.SERVER)
        for row in (bounded(image(64, search=['$ORIGIN']))['metadata'],):
            with self.assertRaisesRegex(e.Refusal, 'elf-search-policy'): e.object_policy(e.SERVER, row)
        with self.assertRaises(e.Refusal): e.metadata(result)

    def test_exact_67_requests_and_68th_refusal_and_interpreter_cost(self):
        names = ['libn'+str(i)+'.so.1' for i in range(64)]
        result = bounded(image(8192, needed=names, soname='libowner.so.1', search=['a','b']))
        self.assertEqual(67, result['cost']['requests']); self.assertLessEqual(result['cost']['search_bytes'], 16147)
        meter = e._AccessCost(2097152, None); data = b'\0x\0'
        for _ in range(67): e._bounded_text(data,0,3,1,False,meter)
        with self.assertRaisesRegex(e.Refusal, 'elf-string-access-requests'): e._bounded_text(data,0,3,1,False,meter)
        result = bounded(t.binary(['liba.so.1'], interpreter=e.INTERPRETER))
        self.assertEqual(e.INTERPRETER, result['metadata']['interpreter']); self.assertFalse(result['runtime_grant'])
        with self.assertRaisesRegex(e.Refusal, 'elf-dynamic-shape'): bounded(image(8192, needed=names+['libextra.so.1']))
        with self.assertRaisesRegex(e.Refusal, 'elf-library-name'): bounded(image(8192, needed=['liba.so.1']*2))
        data = image(64); struct.pack_into('<q',data,288,0x6ffffefa)
        with self.assertRaisesRegex(e.Refusal,'elf-dependency-feature-policy'):
            e.object_policy(e.LOADER,bounded(data)['metadata'])

    def test_actual_default_mapping_window_precharge_and_origin_controls_are_killed(self):
        def enable_default(tree): tree.body[0].args.kw_defaults[0] = ast.Constant(True)
        unsafe = mutated(e.parse, enable_default)
        with self.assertRaises(AssertionError):
            with self.assertRaises(e.Refusal): unsafe(image(), remaining_work=2097152)
        original = e.require
        data = image(); struct.pack_into('<H',data,56,3)
        struct.pack_into('<IIQQQQQQ',data,176,1,4,4096,0x401000,0,len(data)-4096,len(data)-4096,4096)
        with self.assertRaises(e.Refusal): bounded(data)
        with patch.object(e, 'require', lambda ok, code: None if code == 'elf-string-access-mapping' else original(ok, code)):
            with self.assertRaises(AssertionError):
                with self.assertRaises(e.Refusal): bounded(data)
        with patch.object(e, 'require', lambda ok, code: None if code == 'elf-string-access-work' else original(ok, code)):
            with self.assertRaises(AssertionError):
                with self.assertRaises(e.Refusal): e._AccessCost(0,None).spend('search_bytes',1)
        def widen_window(tree):
            for node in ast.walk(tree):
                if isinstance(node,ast.Constant) and type(node.value) is int and node.value == 241: node.value = 242
        unsafe_text = mutated(e._bounded_text, widen_window); meter = e._AccessCost(2097152,None)
        with self.assertRaises(e.Refusal): unsafe_text(b'\0'+b'x'*241+b'\0',0,243,1,False,meter)
        with self.assertRaises(AssertionError): self.assertEqual(241,meter.counts['search_bytes'])
        def remove_find_bound(tree):
            for node in ast.walk(tree):
                if isinstance(node,ast.Call) and isinstance(node.func,ast.Attribute) and node.func.attr == 'find':
                    node.args[2] = ast.Call(ast.Name('len',ast.Load()),[ast.Name('data',ast.Load())],[])
        unsafe, finds, _ = observed_access(remove_find_bound)
        with self.assertRaises(e.Refusal): unsafe(b'\0'+b'x'*1024+b'\0',0,1026,1,False,e._AccessCost(2097152,None))
        with self.assertRaises(AssertionError): self.assertLessEqual(finds[0][1]-finds[0][0],241)
        with patch.object(e,'require',lambda ok,code: None if code == 'elf-string-length' else original(ok,code)):
            with self.assertRaises(AssertionError):
                with self.assertRaisesRegex(e.Refusal,'^elf-string-length$'):
                    e._bounded_text(b'\0xxxx',0,5,1,False,e._AccessCost(2097152,None))
        model, observations, _ = t.namespace(); observations['0:3']['sha256'] = 'a'*64
        with patch.object(e,'require',lambda ok,code: None if code == 'elf-content-origin' else original(ok,code)):
            with self.assertRaises(AssertionError):
                with self.assertRaises(e.Refusal): e.bound_metadata(model.inspect(e.SERVER,3),observations,e.SERVER)

    def test_opt_in_and_receipts_have_no_io_process_or_view_escape(self):
        import os
        import socket
        import subprocess
        data = image(64)
        with patch('builtins.open',side_effect=AssertionError('file IO')), patch.object(socket,'socket',side_effect=AssertionError('network')), \
             patch.object(subprocess,'Popen',side_effect=AssertionError('execution')), patch.object(os,'stat',side_effect=AssertionError('host stat')):
            result = bounded(data); encoded = json.dumps(result)
            self.assertFalse(result['runtime_grant']); self.assertNotIn('handle',result); self.assertNotIn('buffer',result)
            self.assertIsInstance(json.loads(encoded)['cost']['used'],int)


if __name__ == '__main__':
    output = io.StringIO(); result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(output.getvalue().encode('utf-8','backslashreplace')[:5000])
    sys.exit(0 if result.wasSuccessful() else 1)
