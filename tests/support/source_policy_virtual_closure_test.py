"""Synthetic-only foundation contracts; never acquisition or native execution."""
import copy
import importlib.util
import io
import json
from pathlib import Path
import socket
import os
import subprocess
import sys
import time
import tracemalloc
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('virtual', ROOT / 'scripts/manual/source_policy_virtual_closure.py')
v = importlib.util.module_from_spec(spec)
with patch.object(socket, 'socket', side_effect=AssertionError('network forbidden')):
    spec.loader.exec_module(v)
H = 'a'*64
IDENTITY = dict(manifest=H, config='b'*64, source='c'*64)
LAYERS = [(H, 'd'*64), ('e'*64, 'f'*64)]
RESOURCE = {}


def node(path, kind='directory', **changes):
    row = dict(path=path, kind=kind, mode=0o755, uid=0, gid=0,
               sha256=H if kind == 'file' else None, size=1 if kind == 'file' else 0,
               target=None)
    row.update(changes)
    return row


def meta(i=0, **changes):
    row = dict(ordinal=i, compressed=LAYERS[i][0], diff_id=LAYERS[i][1],
               compressed_verified=True, diff_verified=True)
    row.update(changes)
    return row


def model(layers=1, aliases=()):
    return v.VirtualClosure(IDENTITY, LAYERS[:layers], aliases)


class Contracts(unittest.TestCase):
    def test_direct_positive_and_no_runtime_grant(self):
        self.assertEqual((), v.real_aliases())
        m = model(); m.apply(meta(), [node('usr'), node('usr/lib'), node('usr/lib/a', 'file')], 0); m.finalize(1)
        receipt = m.resolve('usr/lib/a', 2)
        self.assertEqual('usr/lib/a', receipt['canonical'])
        self.assertEqual(H, receipt['sha256']); self.assertTrue(receipt['model_only'])
        self.assertFalse(receipt['runtime_grant'])
        self.assertEqual([], receipt['aliases'])
        with self.assertRaises(v.Refusal): m.apply(meta(), [], 3)

    def test_synthetic_alias_positive_and_default_denial(self):
        records = [node('usr'), node('usr/lib64'), node('usr/lib64/a', 'file'), node('lib64', 'symlink', target='usr/lib64')]
        edge = dict(path='lib64', target='usr/lib64', layer=0, entry=3)
        m = model(aliases=[edge]); m.apply(meta(), records, 0); m.finalize(1)
        self.assertEqual('usr/lib64/a', m.resolve('lib64/a', 2)['canonical'])
        m = model(); m.apply(meta(), records, 0); m.finalize(1)
        with self.assertRaises(v.Refusal): m.resolve('lib64/a', 2)
        for field, value in [('target', 'elsewhere'), ('layer', 1), ('entry', 2)]:
            bad = dict(edge); bad[field] = value
            m = model(aliases=[bad]); m.apply(meta(), records, 0); m.finalize(1)
            with self.assertRaises(v.Refusal): m.resolve('lib64/a', 2)

    def test_layer_authentication_and_atomic_failure(self):
        for key, value in [('ordinal', 1), ('compressed', 'b'*64), ('diff_id', 'b'*64), ('compressed_verified', False), ('diff_verified', False)]:
            m = model(); bad = meta(); bad[key] = value
            with self.assertRaises(v.Refusal): m.apply(bad, [node('a', 'file')], 0)
            m.apply(meta(), [node('good', 'file')], 1); m.finalize(2)
            with self.assertRaises(v.Refusal): m.resolve('a', 3)
            self.assertEqual(H, m.resolve('good', 4)['sha256'])
        m = model()
        with self.assertRaises(v.Refusal): m.apply(meta(), [node('a'), node('a')], 0)
        with self.assertRaises(v.Refusal): m.finalize(1)

    def test_whiteouts_apply_before_current_entries_in_either_order(self):
        for reverse in (False, True):
            m = model(2); m.apply(meta(), [node('d'), node('d/old', 'file')], 0)
            rows = [node('d/.wh..wh..opq', 'opaque'), node('d/new', 'file')]
            m.apply(meta(1), rows[::-1] if reverse else rows, 1); m.finalize(2)
            self.assertEqual(H, m.resolve('d/new', 3)['sha256'])
            with self.assertRaises(v.Refusal): m.resolve('d/old', 4)
        m = model(2); m.apply(meta(), [node('d'), node('d/a', 'file')], 0)
        m.apply(meta(1), [node('d/.wh.a', 'whiteout'), node('d/a', 'file', sha256='b'*64)], 1); m.finalize(2)
        self.assertEqual('b'*64, m.resolve('d/a', 3)['sha256'])

    def test_replacement_invalidation_and_directory_metadata(self):
        m = model(2); m.apply(meta(), [node('d'), node('d/a', 'file')], 0)
        m.apply(meta(1), [node('d', mode=0o700)], 1); m.finalize(2)
        self.assertEqual(H, m.resolve('d/a', 3)['sha256'])
        m = model(2); m.apply(meta(), [node('d'), node('d/a', 'file')], 0)
        m.apply(meta(1), [node('d', 'file', sha256='b'*64)], 1); m.finalize(2)
        with self.assertRaises(v.Refusal): m.resolve('d/a', 3)
        self.assertEqual('b'*64, m.resolve('d', 4)['sha256'])

    def test_escape_cycle_dangling_and_non_directory_refusals(self):
        for target in ('../../outside', 'missing', '/proc/self', 'a', 'usr//a', 'C:\\host', 'x\nsecret'):
            with self.assertRaises(v.Refusal):
                m = model(aliases=[dict(path='a', target=target, layer=0, entry=0)])
                m.apply(meta(), [node('a', 'symlink', target=target)], 0); m.finalize(1)
                m.resolve('a', 2)
        m = model()
        with self.assertRaises(v.Refusal): m.apply(meta(), [node('a', 'file'), node('a/b', 'file')], 0)
        for kind in ('hardlink', 'device', 'socket'):
            with self.assertRaises(v.Refusal): model().apply(meta(), [node('a', kind)], 0)
        for key in ('../a', '/a', 'a//b', 'a\\b', 'a\u202eb'):
            with self.assertRaises(v.Refusal): model().apply(meta(), [node(key)], 0)

    def test_virtual_budget_and_immutable_inputs(self):
        rows = [node('a', 'file')]; m = model(); m.apply(meta(), rows, 0)
        rows[0]['sha256'] = 'b'*64; m.finalize(1)
        self.assertEqual(H, m.resolve('a', 2)['sha256'])
        with self.assertRaises(v.Refusal): m.resolve('a', 1)
        with self.assertRaises(v.Refusal): model().apply(meta(), [], 300000)
        m._work = v.WORK_LIMIT
        with self.assertRaises(v.Refusal): m.resolve('a', 3)

    def test_dependency_graph_positive_refusals_and_no_runtime_claim(self):
        m = model(); m.apply(meta(), [node('app', 'file'), node('loader', 'file'), node('lib', 'file')], 0); m.finalize(1)
        objects = [dict(path='app', sha256=H, interpreter='/loader', needed=['lib.so'], search_paths=[]),
                   dict(path='loader', sha256=H, interpreter=None, needed=[], search_paths=[]),
                   dict(path='lib', sha256=H, interpreter=None, needed=[], search_paths=[])]
        providers = [dict(name='lib.so', path='lib', sha256=H)]
        result = m.closure(objects, providers, 2)
        self.assertEqual('dt-needed-closure', result['status']); self.assertFalse(result['runtime_grant'])
        for key, value in [('sha256', 'b'*64), ('needed', ['missing.so']), ('search_paths', ['$ORIGIN'])]:
            bad = copy.deepcopy(objects); bad[0][key] = value
            with self.assertRaises(v.Refusal): m.closure(bad, providers, 3)
        bad = copy.deepcopy(objects); bad[2]['needed'] = ['lib.so']
        with self.assertRaises(v.Refusal): m.closure(bad, providers, 4)
        with self.assertRaises(v.Refusal): m.closure(objects, providers*2, 5)

    def test_guard_removal_controls_kill_the_same_contract(self):
        # Guard family removal is confined to this isolated module in memory.
        source = (ROOT / 'scripts/manual/source_policy_virtual_closure.py').read_text()
        cases = [('layer-verification', lambda m: m.apply(meta(diff_verified=False), [node('a', 'file')], 0)),
                 ('work-budget', lambda m: m.apply(meta(), [], 300000))]
        for code, operation in cases:
            ns = {}; exec(source, ns)
            original = ns['require']
            def bypass(ok, reason, selected=code, original=original):
                if reason != selected: original(ok, reason)
            ns['require'] = bypass
            mutant = ns['VirtualClosure'](IDENTITY, LAYERS[:1])
            with self.assertRaises(AssertionError):
                with self.assertRaises(Exception): operation(mutant)
        ns = {}; exec(source, ns)
        original = ns['require']
        ns['require'] = lambda ok, reason: None if reason == 'canonical-key' else original(ok, reason)
        with self.assertRaises(AssertionError):
            with self.assertRaises(Exception): ns['key']('../a')

    def test_objects_cannot_invoke_magic(self):
        class Hostile:
            def __eq__(self, other): raise AssertionError('magic comparison')
            def __str__(self): raise AssertionError('magic conversion')
        for field in ('compressed', 'diff_id'):
            bad = meta(); bad[field] = Hostile()
            with self.assertRaises(v.Refusal): model().apply(bad, [], 0)
        bad = node('a'); bad['kind'] = Hostile()
        with self.assertRaises(v.Refusal): model().apply(meta(), [bad], 0)
        with self.assertRaises(v.Refusal): v.VirtualClosure(dict(IDENTITY, source=Hostile()), LAYERS[:1])

    def test_alias_whiteout_and_root_escape_semantic_controls(self):
        source = (ROOT / 'scripts/manual/source_policy_virtual_closure.py').read_text()
        # Each control first passes on the original; only one guard family changes.
        def alias_contract(ns):
            m = ns['VirtualClosure'](IDENTITY, LAYERS[:1], [dict(path='a', target='wrong', layer=0, entry=0)])
            m.apply(meta(), [node('a', 'symlink', target='b'), node('b', 'file')], 0); m.finalize(1)
            with self.assertRaises(Exception): m.resolve('a', 2)
        def whiteout_contract(ns):
            m = ns['VirtualClosure'](IDENTITY, LAYERS)
            m.apply(meta(), [node('d'), node('d/a', 'file')], 0)
            m.apply(meta(1), [node('d/.wh..wh..opq', 'opaque')], 1); m.finalize(2)
            with self.assertRaises(Exception): m.resolve('d/a', 3)
        def root_contract(ns):
            with self.assertRaises(Exception): ns['target']('', '../escape')
        for contract in (alias_contract, whiteout_contract, root_contract):
            ns = {}; exec(source, ns); contract(ns)
        ns = {}; exec(source, ns); original = ns['require']
        ns['require'] = lambda ok, reason: None if reason == 'alias-authorization' else original(ok, reason)
        with self.assertRaises(AssertionError): alias_contract(ns)
        for old, new, contract in [('del nodes[old]', 'pass', whiteout_contract),
                                   ("require(bool(parts), 'root-escape')\n            parts.pop()", 'if parts: parts.pop()', root_contract)]:
            self.assertEqual(1, source.count(old)); ns = {}; exec(source.replace(old, new), ns)
            with self.assertRaises(AssertionError): contract(ns)

    def test_absolute_alias_and_hop_cycle_caps(self):
        rows = [node('usr'), node('usr/a', 'file'), node('a', 'symlink', target='/usr/a')]
        m = model(aliases=[dict(path='a', target='/usr/a', layer=0, entry=2)])
        m.apply(meta(), rows, 0); m.finalize(1)
        self.assertEqual('usr/a', m.resolve('a', 2)['canonical'])
        aliases = [dict(path='n'+str(i), target='n'+str(i+1), layer=0, entry=i) for i in range(17)]
        rows = [node(edge['path'], 'symlink', target=edge['target']) for edge in aliases]+[node('n17', 'file')]
        m = model(aliases=aliases); m.apply(meta(), rows, 0); m.finalize(1)
        with self.assertRaisesRegex(v.Refusal, 'alias-budget-cycle'): m.resolve('n0', 2)
        m = model(); m.apply(meta(), [node('a', 'file')], 0); m.finalize(1)
        for i in range(256): m.resolve('a', i+2)
        with self.assertRaises(v.Refusal): m.resolve('a', 258)

    def test_max_node_fixture_allocation_and_entry_budget(self):
        tracemalloc.start(); started = time.monotonic()
        try:
            rows = [node('n'+str(i), 'file') for i in range(65536)]
            m = model(2); m.apply(meta(), rows, 0)
            with self.assertRaises(v.Refusal): m.apply(meta(1), [node('extra', 'file')], 1)
            m.apply(meta(1), [], 2); m.finalize(3)
            self.assertEqual(H, m.resolve('n65535', 4)['sha256'])
            peak = tracemalloc.get_traced_memory()[1]
        finally:
            elapsed = int((time.monotonic()-started)*1000); tracemalloc.stop()
        self.assertLess(peak, 512*1024*1024); self.assertLess(elapsed, 30000)
        RESOURCE.update(nodes=65536, python_peak_bytes=peak, elapsed_ms=elapsed)

    def test_no_io_in_model(self):
        with patch('builtins.open', side_effect=AssertionError('file IO')), patch.object(socket, 'socket', side_effect=AssertionError('network')), \
             patch.object(os, 'stat', side_effect=AssertionError('stat')), patch.object(os, 'readlink', side_effect=AssertionError('readlink')), \
             patch.object(subprocess, 'Popen', side_effect=AssertionError('process')):
            m = model(); m.apply(meta(), [node('a', 'file')], 0); m.finalize(1)
            self.assertEqual(H, m.resolve('a', 2)['sha256'])


if __name__ == '__main__':
    output = io.StringIO()
    result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun, failures=len(result.failures), errors=len(result.errors), network_calls=0, resource=RESOURCE)))
    if not result.wasSuccessful(): print(output.getvalue(), file=sys.stderr)
    sys.exit(0 if result.wasSuccessful() else 1)
