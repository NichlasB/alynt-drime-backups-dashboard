"""Synthetic hardlink DATA/origin contracts; no acquisition or native linking."""
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import sys
import tarfile
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('alias_tests', Path(__file__).with_name('source_policy_ubi_alias_test.py'))
t = importlib.util.module_from_spec(spec); spec.loader.exec_module(t)
a, v = t.a, t.a.v
H, J = hashlib.sha256(b'old').hexdigest(), hashlib.sha256(b'new').hexdigest()
IDENTITY = dict(manifest=a.PIN, config=a.CONFIG, source='c'*64)
PAIRS = [('a'*64, 'b'*64), ('d'*64, 'e'*64), ('f'*64, '1'*64)]


def row(path, kind='file', **changes):
    value = dict(path=path, kind=kind, mode=0o755, uid=0, gid=0,
                 sha256=H if kind == 'file' else None, size=3 if kind == 'file' else 0, target=None)
    value.update(changes); return value


def meta(i=0, **changes):
    value = dict(ordinal=i, compressed=PAIRS[i][0], diff_id=PAIRS[i][1], compressed_verified=True, diff_verified=True)
    value.update(changes); return value


def model(count=1, aliases=(), namespace=None, **changes):
    return (namespace or vars(v))['VirtualClosure'](IDENTITY, PAIRS[:count], aliases, hardlinks=True, **changes)


class Contracts(unittest.TestCase):
    def test_collector_preserves_link_metadata_without_reading_a_link_body(self):
        rows = [t.member('src', data=b'old'), t.member('link', tarfile.LNKTYPE, './src')]
        result = t.Contracts().scan(rows)
        self.assertEqual('hardlink', result['records'][1]['kind'])
        self.assertEqual('src', result['records'][1]['target'])
        self.assertIsNone(result['records'][1]['sha256']); self.assertEqual(0, result['records'][1]['size'])
        m = model(); m.apply(meta(), result['records'], 0); m.finalize(1)
        self.assertEqual(H, m.resolve('link', 2)['sha256'])
        for target in ('../src', '/src', 'C:src', 'src//a'):
            with self.assertRaises(a.s.Refusal): t.Contracts().scan([t.member('link', tarfile.LNKTYPE, target)])
        with self.assertRaises(a.s.Refusal): t.Contracts().scan([t.member('link', tarfile.LNKTYPE, 'src', data=b'x')])

    def test_exact_origin_positive_default_denial_and_no_grant(self):
        rows = [row('src'), row('link', 'hardlink', target='src')]
        with self.assertRaises(v.Refusal): v.VirtualClosure(IDENTITY, PAIRS[:1]).apply(meta(), rows, 0)
        m = model(); m.apply(meta(), rows, 0); m.finalize(1); result = m.resolve('link', 2)
        self.assertEqual(H, result['sha256']); self.assertFalse(result['runtime_grant'])
        self.assertEqual(dict(layer=0, entry=0, sha256=H, size=3,
            path_sha256=hashlib.sha256(b'src').hexdigest()), result['content_origin'])
        self.assertEqual(1, len(result['hardlinks'])); self.assertNotIn('target', result['hardlinks'][0])
        self.assertNotIn('path', result['content_origin'])
        result['content_origin']['sha256'] = J; result['hardlinks'].clear()
        self.assertEqual(H, m.resolve('link', 3)['content_origin']['sha256'])
        self.assertEqual(1, len(m.resolve('link', 4)['hardlinks']))

    def test_earlier_lower_origin_is_not_rebound_to_later_same_layer_replacement(self):
        m = model(2); m.apply(meta(), [row('afile')], 0)
        m.apply(meta(1), [row('zlink', 'hardlink', target='afile'), row('afile', sha256=J)], 1); m.finalize(2)
        self.assertEqual(H, m.resolve('zlink', 3)['sha256']); self.assertEqual(J, m.resolve('afile', 4)['sha256'])
        self.assertEqual(0, m.resolve('zlink', 5)['content_origin']['layer'])
        m = model(2); m.apply(meta(), [row('afile')], 0)
        m.apply(meta(1), [row('afile', sha256=J), row('zlink', 'hardlink', target='afile')], 1); m.finalize(2)
        self.assertEqual(J, m.resolve('zlink', 3)['sha256'])

    def test_content_survives_later_whiteout_and_source_directory_replacement(self):
        m = model(3); m.apply(meta(), [row('d', 'directory'), row('d/src'), row('link', 'hardlink', target='d/src')], 0)
        m.apply(meta(1), [row('d', sha256=J)], 1)
        m.apply(meta(2), [row('.wh.d', 'whiteout')], 2); m.finalize(3)
        self.assertEqual(H, m.resolve('link', 4)['sha256'])
        with self.assertRaises(v.Refusal): m.resolve('d/src', 5)
        m = model(2); m.apply(meta(), [row('src')], 0)
        with self.assertRaises(v.Refusal): m.apply(meta(1), [row('link', 'hardlink', target='src'), row('.wh.src', 'whiteout')], 1)
        m.apply(meta(1), [row('src', sha256=J), row('link', 'hardlink', target='src'), row('.wh.src', 'whiteout')], 2)
        m.finalize(3); self.assertEqual(J, m.resolve('link', 4)['sha256'])

    def test_missing_forward_self_special_symlink_and_directory_targets_refuse(self):
        cases = [[row('link', 'hardlink', target='missing')],
                 [row('link', 'hardlink', target='src'), row('src')],
                 [row('d', 'directory'), row('link', 'hardlink', target='d')],
                 [row('src'), row('sym', 'symlink', target='src'), row('link', 'hardlink', target='sym')],
                 [row('sym', 'symlink', target='d'), row('sym/src'), row('link', 'hardlink', target='sym/src')],
                 [row('proc', 'directory'), row('proc/src'), row('link', 'hardlink', target='proc/src')]]
        for rows in cases:
            with self.assertRaises(v.Refusal): model().apply(meta(), rows, 0)
        m = model(2); m.apply(meta(), [row('src')], 0)
        with self.assertRaises(v.Refusal): m.apply(meta(1), [row('src', 'hardlink', target='src')], 1)
        m = model(2); m.apply(meta(), [row('d', 'directory'), row('d/src')], 0)
        with self.assertRaisesRegex(v.Refusal, 'hardlink-target-domain'):
            m.apply(meta(1), [row('d', 'hardlink', target='d/src')], 1)
        for target in ('../src', '/src', 'C:src'):
            with self.assertRaises(v.Refusal): model().apply(meta(), [row('src'), row('link', 'hardlink', target=target)], 0)

    def test_metadata_refusal_is_atomic_and_input_cannot_forge_origin(self):
        for field, value in [('mode', 0o700), ('uid', 1), ('gid', 1), ('size', 1), ('sha256', J)]:
            m = model(); bad = row('link', 'hardlink', target='src'); bad[field] = value
            with self.assertRaises(v.Refusal) as caught: m.apply(meta(), [row('src'), bad], 0)
            if field in ('mode', 'uid', 'gid'):
                facts = caught.exception.hardlink_facts
                self.assertEqual(1, facts['entry']); self.assertEqual('file', facts['target_kind'])
                self.assertFalse(facts['metadata_equal'][field]); self.assertNotIn('path', facts)
            m.apply(meta(), [row('good')], 1); m.finalize(2)
            with self.assertRaises(v.Refusal): m.resolve('src', 3)
            self.assertEqual(H, m.resolve('good', 4)['sha256'])
        with self.assertRaises(v.Refusal): model().apply(meta(), [row('src', mode=0o4755), row('link', 'hardlink', target='src', mode=0o4755)], 0)
        with self.assertRaises(v.Refusal): model().apply(meta(), [dict(row('a'), content_origin={})], 0)
        errors = []; original = v.require
        def observed(ok, code):
            try: original(ok, code)
            except v.Refusal as error: errors.append(error); raise
        with patch.object(v, 'require', observed), patch.object(v.hashlib, 'sha256', side_effect=RuntimeError('diagnostic only')):
            with self.assertRaises(v.Refusal) as caught:
                model().apply(meta(), [row('src'), row('link', 'hardlink', target='src', mode=0o700)], 0)
        self.assertIs(errors[0], caught.exception); self.assertIsNone(caught.exception.hardlink_facts)

    def test_chain_and_combined_link_budget_are_finite(self):
        rows = [row('src')]+[row('l'+str(i), 'hardlink', target='src' if i == 0 else 'l'+str(i-1)) for i in range(16)]
        m = model(); m.apply(meta(), rows, 0); m.finalize(1)
        self.assertEqual(16, len(m.resolve('l15', 2)['hardlinks']))
        with self.assertRaises(v.Refusal): model().apply(meta(), rows+[row('l16', 'hardlink', target='l15')], 0)
        m = model(aliases=[dict(path='alias', target='l15', layer=0, entry=17)])
        m.apply(meta(), rows+[row('alias', 'symlink', target='l15')], 0); m.finalize(1)
        with self.assertRaises(v.Refusal): m.resolve('alias', 2)

    def test_complete_layer_gates_and_logical_work_budget_still_apply(self):
        for field in ('compressed_verified', 'diff_verified'):
            with self.assertRaises(v.Refusal): model().apply(meta(**{field: False}), [row('src'), row('link', 'hardlink', target='src')], 0)
        m = model(); m._work = v.WORK_LIMIT
        with self.assertRaises(v.Refusal): m.apply(meta(), [row('src'), row('link', 'hardlink', target='src')], 0)
        with self.assertRaises(a.s.Refusal): t.Contracts().scan([t.member('src', data=b'old'), t.member('link', tarfile.LNKTYPE, 'src')], diff_id='sha256:'+'0'*64)
        m = model(2); m._hardlink_edges = 65535
        m.apply(meta(), [row('src'), row('link', 'hardlink', target='src')], 0)
        self.assertEqual(65536, m._hardlink_edges)
        with self.assertRaisesRegex(v.Refusal, 'hardlink-history-budget'):
            m.apply(meta(1), [row('extra', 'hardlink', target='src')], 1)
        m.apply(meta(1), [], 2); m.finalize(3)
        self.assertEqual(H, m.resolve('link', 4)['sha256'])
        with self.assertRaises(v.Refusal): m.resolve('extra', 5)

    def test_guard_and_order_controls_are_assertion_killed(self):
        source = (ROOT/'scripts/manual/source_policy_virtual_closure.py').read_text()
        def metadata_contract(ns):
            with self.assertRaises(Exception): model(namespace=ns).apply(meta(), [row('src'), row('link', 'hardlink', target='src', mode=0o700)], 0)
        def order_contract(ns):
            m = model(2, namespace=ns); m.apply(meta(), [row('afile')], 0)
            m.apply(meta(1), [row('zlink', 'hardlink', target='afile'), row('afile', sha256=J)], 1); m.finalize(2)
            self.assertEqual(H, m.resolve('zlink', 3)['sha256'])
        def chain_contract(ns):
            rows = [row('src')]+[row('l'+str(i), 'hardlink', target='src' if i == 0 else 'l'+str(i-1)) for i in range(17)]
            with self.assertRaises(Exception): model(namespace=ns).apply(meta(), rows, 0)
        def combined_contract(ns):
            rows = [row('src')]+[row('l'+str(i), 'hardlink', target='src' if i == 0 else 'l'+str(i-1)) for i in range(16)]
            m = model(namespace=ns, aliases=[dict(path='alias', target='l15', layer=0, entry=17)])
            m.apply(meta(), rows+[row('alias', 'symlink', target='l15')], 0); m.finalize(1)
            with self.assertRaises(Exception): m.resolve('alias', 2)
        def history_contract(ns):
            m = model(namespace=ns); m._hardlink_edges = 65536
            with self.assertRaises(Exception): m.apply(meta(), [row('src'), row('link', 'hardlink', target='src')], 0)
        for contract in (metadata_contract, order_contract, chain_contract, combined_contract, history_contract):
            ns = {}; exec(source, ns); contract(ns)
        for selected, contract in [('hardlink-content-metadata', metadata_contract),
                                   ('hardlink-budget-cycle', chain_contract), ('combined-link-budget', combined_contract),
                                   ('hardlink-history-budget', history_contract)]:
            ns = {}; exec(source, ns); original = ns['require']
            ns['require'] = lambda ok, code, selected=selected, original=original: None if code == selected else original(ok, code)
            with self.assertRaises(AssertionError): contract(ns)
        before = 'for name, row in staged.items():  # Preserve archive order for content identity.'
        self.assertEqual(1, source.count(before)); ns = {}; exec(source.replace(before, before.replace('staged.items()', 'sorted(staged.items())')), ns)
        with self.assertRaises(AssertionError): order_contract(ns)

    def test_no_native_links_and_primitive_policy_types(self):
        import os
        with patch.object(os, 'link', side_effect=AssertionError('native link')), patch.object(os, 'symlink', side_effect=AssertionError('native symlink')):
            result = t.Contracts().scan([t.member('src', data=b'old'), t.member('link', tarfile.LNKTYPE, 'src')])
            m = model(); m.apply(meta(), result['records'], 0); m.finalize(1)
            self.assertEqual(H, m.resolve('link', 2)['sha256'])
        class Hostile:
            def __bool__(self): raise AssertionError('magic')
            def __str__(self): raise AssertionError('magic')
        with self.assertRaises(v.Refusal): v.VirtualClosure(IDENTITY, PAIRS[:1], hardlinks=Hostile())
        with self.assertRaises(v.Refusal): model().apply(meta(), [row('a', 'hardlink', target=Hostile())], 0)


if __name__ == '__main__':
    output = io.StringIO(); result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun, failures=len(result.failures), errors=len(result.errors), network_calls=0)), flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(output.getvalue().encode('utf-8', 'backslashreplace')[:3000])
    sys.exit(0 if result.wasSuccessful() else 1)
