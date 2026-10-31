"""Access-only synthetic walks and mocked kernel objects; no physical audit."""
import importlib.util
import io
import json
from pathlib import Path
import socket
import stat
import subprocess
import sys
import types
import unittest
from unittest.mock import patch

path = Path(__file__).resolve().parents[2] / 'scripts/ci/source-policy-linux-observer-audit.py'
source = path.read_text(encoding='utf-8')
spec = importlib.util.spec_from_file_location('maps_walk', path)
a = importlib.util.module_from_spec(spec)
row = b'1000-2000 r--s 00000000 08:01 7 /NOT-A-HOST-PATH\n'
facts = {'payload_bytes': len(row), 'read_calls': 3, 'eof': True}


class WalkTests(unittest.TestCase):
    def walk(self, chunks, deadline=100):
        with patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', return_value=9) as opened, patch.object(a.os, 'close') as closed, patch.object(a.os, 'read', side_effect=chunks) as read, patch.object(a.time, 'monotonic', return_value=10):
            try: result = a.read_maps_at(3, deadline)
            finally: self.read_calls = read.call_args_list; self.closed = closed.call_args_list
            opened.assert_called_once_with('maps', a.os.O_RDONLY | a.os.O_CLOEXEC, dir_fd=3)
            return result

    def test_chunked_complete_walk_is_positive_not_atomic(self):
        data, counts = self.walk([row[:17], row[17:], b''])
        self.assertEqual(row, data); self.assertEqual(facts, counts)
        self.assertEqual([32769, 32752, 32769-len(row)], [call.args[1] for call in self.read_calls])
        self.assertEqual(1, len(self.closed)); self.assertEqual(1, len(a.parse_maps(data)))
        with self.assertRaises(a.Refusal): self.walk([row[:17]] * 16)

    def test_exact_cap_requires_eof_and_cap_plus_one_refuses(self):
        data, counts = self.walk([b'x' * 32768, b''])
        self.assertEqual(32768, len(data)); self.assertEqual(2, counts['read_calls'])
        self.assertEqual([32769, 1], [call.args[1] for call in self.read_calls])
        with self.assertRaises(a.Refusal) as caught: self.walk([b'x' * 32769, b''])
        self.assertEqual('maps-walk-byte-bound', str(caught.exception)); self.assertEqual(1, len(self.read_calls))
        with self.assertRaises(a.Refusal): self.walk([b'x' * 32768, b'x'])
        self.assertEqual(1, len(self.closed))

    def test_call_ceiling_includes_eof_and_never_restarts(self):
        self.assertEqual(16, self.walk([b'x'] * 15 + [b''])[1]['read_calls'])
        with self.assertRaises(a.Refusal) as caught: self.walk([b'x'] * 16 + [b''])
        self.assertEqual('maps-walk-call-bound', str(caught.exception)); self.assertEqual(16, len(self.read_calls))

    def test_deadline_denial_and_io_error_do_not_become_eof(self):
        with self.assertRaises(a.Refusal) as caught: self.walk([b''], deadline=5)
        self.assertEqual('maps-walk-deadline', str(caught.exception)); self.assertEqual([], self.read_calls)
        with self.assertRaises(PermissionError) as caught: self.walk([row[:5], PermissionError(1, 'SECRET')])
        self.assertEqual('maps-read', caught.exception.operation); self.assertFalse(caught.exception.facts['maps_complete'])
        self.assertEqual(2, caught.exception.facts['maps_walk']['read_calls'])
        with self.assertRaises(a.Refusal) as caught: self.walk([OSError('SECRET')])
        self.assertEqual('maps-walk-io', str(caught.exception)); self.assertNotIn('SECRET', repr(a.proc_read_details(caught.exception)))

    def observation(self, data=row, inode=7, pid=42, post_start=1234, body=b'A'*4096):
        status = ('42 (child) ' + ' '.join(['S', '10'] + ['0'] * 17 + ['1234']) + '\n').encode()
        after = status.replace(b'1234', str(post_start).encode())
        meta = types.SimpleNamespace(st_mode=stat.S_IFREG|0o600, st_nlink=1, st_size=4096, st_dev=123, st_ino=inode)
        with patch.object(a, 'read_at', side_effect=[status,after]), patch.object(a, 'read_maps_at', return_value=(data, facts)), patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', return_value=9) as opened, patch.object(a.os, 'close'), patch.object(a.os, 'fstat', return_value=meta), patch.object(a.os, 'major', return_value=8, create=True), patch.object(a.os, 'minor', return_value=1, create=True), patch.object(a.os, 'read', side_effect=[body, b'']), patch.object(a.time, 'monotonic', return_value=10):
            result = a.access_snapshot(3, pid, (10,1234), (8,1,7), 100)
            opened.assert_called_once_with('map_files/1000-2000', a.os.O_RDONLY | a.os.O_CLOEXEC, dir_fd=3)
            return result

    def test_real_object_binding_and_parser_guards_remain(self):
        value = self.observation(); self.assertTrue(value['fixture_backing_read']); self.assertEqual(facts, value['maps_walk'])
        self.assertNotIn('NOT-A-HOST-PATH', repr(value))
        for data, inode, pid in ((row.rstrip(b'\n'),7,42),(row.replace(b' 7 ',b' 8 '),7,42),(row,8,42),(row,7,43)):
            with self.assertRaises(a.Refusal): self.observation(data, inode, pid)
        with self.assertRaises(a.Refusal): self.observation(post_start=5678)
        with self.assertRaises(a.Refusal): self.observation(body=b'B'*4096)

    def test_versioned_report_never_grants_consistency_or_candidate(self):
        fields = a.access_report_fields()
        self.assertIn('report.update(access_report_fields())',source); self.assertIn('return access_exit_code(report)',source)
        self.assertEqual({'format':'ADBLHO2','evidence_scope':'existing-authority-access-feasibility','read_policy':'bounded-eof-stream-v1','mapping_consistency':'not-established','candidate_qualification':'not-established'}, fields)
        good = dict(fields, status='available', cleanup='confirmed', source_unchanged=True, authority_unchanged=True)
        self.assertEqual(0, a.access_exit_code(good)); self.assertEqual(0, a.access_exit_code(dict(good,status='unavailable')))
        for key, value in (('format','ADBLHO1'),('mapping_consistency','atomic'),('candidate_qualification','qualified'),('cleanup','unconfirmed'),('source_unchanged',False),('authority_unchanged',False),('status','refused')):
            self.assertEqual(1, a.access_exit_code(dict(good, **{key:value})))

    def test_all_access_denials_and_error_facts_are_safe(self):
        with patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', side_effect=PermissionError(1,'SECRET')):
            with self.assertRaises(PermissionError) as caught: a.read_maps_at(3,100)
        self.assertFalse(caught.exception.facts['maps_complete']); self.assertEqual(0,caught.exception.facts['maps_walk']['read_calls'])
        self.assertNotIn('SECRET', repr(caught.exception.facts))

    def test_semantic_controls_are_assertion_killed_without_disk_changes(self):
        mutations = [('if not chunk:', 'if True:', 'test_chunked_complete_walk_is_positive_not_atomic'),
                     ('len(payload) <= 32768', 'True', 'test_exact_cap_requires_eof_and_cap_plus_one_refuses'),
                     ('range(16)', 'range(17)', 'test_call_ceiling_includes_eof_and_never_restarts'),
                     ('time.monotonic() < deadline', 'True', 'test_deadline_denial_and_io_error_do_not_become_eof'),
                     ('(os.major(info.st_dev), os.minor(info.st_dev), info.st_ino) == key', 'True', 'test_real_object_binding_and_parser_guards_remain'),
                     ("parse_stat(read_at(proc, 'stat', 4096), pid) == identity", 'True', 'test_real_object_binding_and_parser_guards_remain'),
                     ("size == 4096 and sha == hashlib.sha256(b'A'*4096).hexdigest()", 'True', 'test_real_object_binding_and_parser_guards_remain'),
                     ("'map_files/' + unique[key]", "'UNTRUSTED-PATH'", 'test_real_object_binding_and_parser_guards_remain'),
                     ("'mapping_consistency':'not-established'", "'mapping_consistency':'atomic'", 'test_versioned_report_never_grants_consistency_or_candidate'),
                     ("'candidate_qualification':'not-established'", "'candidate_qualification':'qualified'", 'test_versioned_report_never_grants_consistency_or_candidate'),
                     ("report.get('cleanup') == 'confirmed'", 'True', 'test_versioned_report_never_grants_consistency_or_candidate')]
        original = a
        for old,new,method in mutations:
            self.assertGreater(source.count(old),0)
            image=types.ModuleType('walk_control'); image.__file__=str(path)
            exec(compile(source.replace(old,new),str(path),'exec'), vars(image))
            with patch.dict(globals(), {'a':image}): result=unittest.TextTestRunner(stream=io.StringIO()).run(WalkTests(method))
            self.assertEqual([],result.errors); self.assertGreater(len(result.failures),0,old)
        self.assertIs(a,original); self.assertEqual(source,path.read_text(encoding='utf-8'))


capture=io.StringIO()
with patch.object(socket,'socket',side_effect=AssertionError('No network')), patch.object(subprocess,'Popen',side_effect=AssertionError('No process')):
    spec.loader.exec_module(a)
    result=unittest.TextTestRunner(stream=capture).run(unittest.defaultTestLoader.loadTestsFromTestCase(WalkTests))
print(json.dumps({'tests':result.testsRun,'failures':len(result.failures),'errors':len(result.errors)}))
if not result.wasSuccessful(): print(capture.getvalue().encode('ascii','backslashreplace')[:3000].decode(),file=sys.stderr)
sys.exit(0 if result.wasSuccessful() else 1)
