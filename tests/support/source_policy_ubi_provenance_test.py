"""Synthetic archives and actual original-refusal observation. No acquisition."""
import copy
import gzip
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import sys
import tarfile
import tempfile
import time
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('ubi', ROOT / 'scripts/ci/source-policy-legacy-ubi-static.py')
u = importlib.util.module_from_spec(spec); spec.loader.exec_module(u)
s, p = u.s, u.provenance


def context():
    return dict(head='a'*40, image='b'*64, run='c'*32, manifest=u.PIN, config=u.CONFIG,
                layer=0, compressed=u.LAYERS[0][0], compressed_verified=True, diff_id_verified=False)


def fail_scan(kind=tarfile.SYMTYPE, injected=False, name='lib64'):
    raw = io.BytesIO()
    with tarfile.open(fileobj=raw, mode='w') as archive:
        member = tarfile.TarInfo(name); member.type = kind
        member.linkname = 'usr/lib64' if kind == tarfile.SYMTYPE else ''
        archive.addfile(member)
    data = raw.getvalue(); errors = []; original = s.require
    def watched(ok, code):
        try: original(ok, code)
        except s.Refusal as error:
            errors.append(error); raise
    with tempfile.TemporaryDirectory() as parent:
        file = Path(parent) / 'layer.blob'; file.write_bytes(gzip.compress(data))
        with patch.object(s, 'require', watched):
            try:
                with patch.object(s, 'critical_summary', side_effect=RuntimeError('private-diagnostic-secret')) if injected else patch.object(s, 'critical_summary', s.critical_summary):
                    s.scan_layer(file, 'sha256:'+hashlib.sha256(data).hexdigest(), u.WANTED, {},
                                 dict(entries=0, expanded=0, deadline=time.monotonic()+5))
            except s.Refusal as caught:
                return caught, errors
    raise AssertionError('Original refusal missing')


class Contracts(unittest.TestCase):
    def test_actual_refusal_identity_and_bounded_positive_packet(self):
        error, errors = fail_scan()
        self.assertIs(error, errors[0]); self.assertEqual('critical-parent-not-directory', str(error))
        facts = error.critical_facts
        self.assertEqual('symlink', facts['kind']); self.assertEqual('loader', facts['role'])
        self.assertEqual('lib64', facts['name']['literal']); self.assertEqual('usr/lib64', facts['target']['literal'])
        result = p.packet(facts, context())
        self.assertEqual('ADBUPF1', result['format']); self.assertFalse(result['context']['diff_id_verified'])
        self.assertLessEqual(len(json.dumps(result, separators=(',', ':')).encode('ascii')), 2048)
        facts['name']['literal'] = 'tampered'
        self.assertEqual('lib64', result['failure']['name']['literal'])
        error, _ = fail_scan(name='usr/sbin/mariadbd')
        self.assertEqual('critical-linked-path', str(error))
        self.assertEqual('server', error.critical_facts['role'])
        self.assertEqual('ADBUPF1', p.packet(error.critical_facts, context())['format'])

    def test_regular_parent_is_still_refused_and_not_reclassified_as_alias(self):
        error, _ = fail_scan(tarfile.REGTYPE)
        self.assertEqual('regular', error.critical_facts['kind'])
        self.assertEqual('critical-parent-not-directory', str(error))
        self.assertIsNone(error.critical_facts['target']['literal'])

    def test_diagnostic_failure_and_first_capture_preserve_original(self):
        error, errors = fail_scan(injected=True)
        self.assertIs(error, errors[0]); self.assertEqual('critical-parent-not-directory', str(error))
        self.assertIsNone(error.critical_facts)
        error, _ = fail_scan(); first = error.critical_facts
        member = tarfile.TarInfo('other')
        s.capture_critical(error, member, 'other', (), dict(entries=999), 999)
        self.assertIs(first, error.critical_facts)
        with patch.object(s, 'capture_critical', side_effect=RuntimeError('secret')):
            error, errors = fail_scan()
            self.assertIs(error, errors[0]); self.assertEqual('critical-parent-not-directory', str(error))

    def test_malformed_context_or_facts_cannot_create_packet(self):
        error, _ = fail_scan()
        for field, bad in [('head', 'unknown'), ('image', 'secret'), ('run', None), ('manifest', 'a'*64),
                           ('config', 'a'*64), ('layer', 10), ('compressed', 'bad'),
                           ('compressed_verified', False), ('diff_id_verified', True)]:
            ctx = context(); ctx[field] = bad
            self.assertIsNone(p.packet(error.critical_facts, ctx))
        for field, bad in [('reason', 'secret'), ('role', 'host-path'), ('kind', 'unexpected'), ('entry', True), ('layer_entry', 0)]:
            facts = copy.deepcopy(error.critical_facts); facts[field] = bad
            self.assertIsNone(p.packet(facts, context()))
        facts = copy.deepcopy(error.critical_facts); facts['layer_entry'] = facts['entry']+1
        self.assertIsNone(p.packet(facts, context()))
        facts = copy.deepcopy(error.critical_facts); facts['name']['literal'] = 'lib64\nsecret'
        self.assertIsNone(p.packet(facts, context()))
        error, _ = fail_scan()
        with patch.object(p.json, 'dumps', return_value='x'*2049):
            self.assertIsNone(p.packet(error.critical_facts, context()))
        class Hostile:
            def __eq__(self, other): raise AssertionError('magic')
        facts = copy.deepcopy(error.critical_facts); facts['name']['hash_scope'] = Hostile()
        self.assertIsNone(p.packet(facts, context()))

    def test_raw_unsafe_names_are_only_bounded_hash_facts(self):
        for value in ('secret\nSQL', 'usr/lib64/'+'x'*2000, '\ud800secret', '../../outside', object()):
            facts = s.critical_summary(value)
            self.assertIsNone(facts['literal']); self.assertLessEqual(facts['hashed_characters'], 1024)
            self.assertTrue(p.summary(facts))


if __name__ == '__main__':
    output = io.StringIO()
    result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun, failures=len(result.failures), errors=len(result.errors), network_calls=0)))
    if not result.wasSuccessful(): print(output.getvalue(), file=sys.stderr)
    sys.exit(0 if result.wasSuccessful() else 1)
