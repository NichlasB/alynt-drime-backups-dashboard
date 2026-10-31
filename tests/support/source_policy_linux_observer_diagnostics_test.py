"""Synthetic proc-read diagnostics only: no proc, process, network or owned stage."""
import ast
import importlib.util
import io
import json
from pathlib import Path
import socket
import subprocess
import sys
import types
import unittest
from unittest.mock import patch

path = Path(__file__).resolve().parents[2] / 'scripts/ci/source-policy-linux-observer-audit.py'
source = path.read_text(encoding='utf-8')
spec = importlib.util.spec_from_file_location('observer_diagnostics', path)
a = importlib.util.module_from_spec(spec)


class DiagnosticTests(unittest.TestCase):
    def test_incomplete_roles_and_exact_counts_without_payload_disclosure(self):
        for role, limit in (('stat', 4096), ('status', 16384), ('maps', 32768)):
            with patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', return_value=9), patch.object(a.os, 'close') as close, patch.object(a.os, 'read', side_effect=[b'SECRET\xff', b'\xfe']) as read:
                with self.assertRaises(a.Refusal) as caught: a.read_at(3, role, limit)
                self.assertEqual('proc-read-incomplete', str(caught.exception))
                self.assertEqual({'proc_read': {'role': role, 'payload_bytes': 7, 'payload_limit': limit, 'eof_probe_bytes': 1}}, a.proc_read_details(caught.exception))
                self.assertEqual([limit + 1, 1], [call.args[1] for call in read.call_args_list])
                self.assertNotIn('SECRET', json.dumps(a.proc_read_details(caught.exception)))
                close.assert_called_once_with(9)

    def test_eof_success_overflow_and_access_denial_preserve_acceptance(self):
        for payload in (b'', b'abc', b'abcd'):
            with patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', return_value=9), patch.object(a.os, 'close'), patch.object(a.os, 'read', side_effect=[payload, b'']) as read:
                self.assertEqual(payload, a.read_at(3, 'maps', 4))
                self.assertEqual([5, 1], [call.args[1] for call in read.call_args_list])
        with patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', return_value=9), patch.object(a.os, 'close') as close, patch.object(a.os, 'read', return_value=b'abcde') as read:
            with self.assertRaises(a.Refusal) as caught: a.read_at(3, 'maps', 4)
            self.assertEqual('read-bound', str(caught.exception)); self.assertEqual({}, a.proc_read_details(caught.exception))
            read.assert_called_once_with(9, 5); close.assert_called_once_with(9)
        denied = PermissionError(1, 'PRIVATE EXCEPTION')
        with patch.object(a.os, 'O_CLOEXEC', 524288, create=True), patch.object(a.os, 'open', side_effect=denied):
            with self.assertRaises(PermissionError) as caught: a.read_at(3, 'maps', 4)
            self.assertIs(denied, caught.exception); self.assertEqual({}, a.proc_read_details(caught.exception))

    def test_fixed_role_and_all_report_paths_are_source_backed(self):
        with patch.object(a.os, 'open', side_effect=AssertionError('No open')):
            with self.assertRaises(a.Refusal) as caught: a.read_at(3, '/PRIVATE/path', 4)
        self.assertEqual('proc-read-role', str(caught.exception)); self.assertEqual({}, a.proc_read_details(caught.exception))
        self.assertEqual({}, a.proc_read_details(RuntimeError('SECRET')))
        tree = ast.parse(source)
        for owner, count in (('audit', 2), ('main', 1)):
            node = next(node for node in tree.body if isinstance(node, ast.FunctionDef) and node.name == owner)
            calls = [node for node in ast.walk(node) if isinstance(node, ast.Call) and isinstance(node.func, ast.Attribute) and isinstance(node.func.value, ast.Name) and node.func.value.id == 'report' and node.func.attr == 'update' and len(node.args) == 1 and isinstance(node.args[0], ast.Call) and isinstance(node.args[0].func, ast.Name) and node.args[0].func.id == 'proc_read_details']
            self.assertEqual(count, len(calls))
            self.assertTrue(all(ast.unparse(call) == 'report.update(proc_read_details(error))' for call in calls))

    def test_diagnostics_and_eof_controls_are_assertion_killed_in_memory(self):
        mutations = [('if probe:', 'if False:', 'test_incomplete_roles_and_exact_counts_without_payload_disclosure'),
                     ("'role': role", "'role': 'status'", 'test_incomplete_roles_and_exact_counts_without_payload_disclosure'),
                     ("return {'proc_read': error.facts}", 'return {}', 'test_incomplete_roles_and_exact_counts_without_payload_disclosure'),
                     ('phase=phase)\n        report.update(proc_read_details(error))', 'phase=phase)\n        proc_read_details(error)', 'test_fixed_role_and_all_report_paths_are_source_backed')]
        original = a
        for old, new, method in mutations:
            self.assertEqual(1, source.count(old))
            image = types.ModuleType('diagnostic_control'); image.__file__ = str(path)
            changed = source.replace(old, new, 1)
            exec(compile(changed, str(path), 'exec'), vars(image))
            with patch.dict(globals(), {'a': image, 'source': changed}):
                result = unittest.TextTestRunner(stream=io.StringIO()).run(DiagnosticTests(method))
            self.assertEqual([], result.errors); self.assertGreater(len(result.failures), 0, old)
        self.assertIs(a, original); self.assertEqual(source, path.read_text(encoding='utf-8'))


capture = io.StringIO()
with patch.object(socket, 'socket', side_effect=AssertionError('No network')), patch.object(subprocess, 'Popen', side_effect=AssertionError('No process')):
    spec.loader.exec_module(a)
    result = unittest.TextTestRunner(stream=capture).run(unittest.defaultTestLoader.loadTestsFromTestCase(DiagnosticTests))
print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors)}))
if not result.wasSuccessful(): print(capture.getvalue().encode('ascii', 'backslashreplace')[:3000].decode(), file=sys.stderr)
sys.exit(0 if result.wasSuccessful() else 1)
