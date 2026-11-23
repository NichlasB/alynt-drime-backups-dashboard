"""Controller/cleanup API models ONLY. No real descriptors, children or files."""
import ast
import contextlib
import io
import json
from pathlib import Path
import stat
import sys
from types import SimpleNamespace
import unittest
from unittest.mock import Mock, MagicMock, patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'tests/support'))
import source_policy_deb_sources_test as F
M, D = F.M, F.D


class Root:
    name = 'adb-ppa-deb-1'

    def __init__(self):
        self.present = False

    def exists(self):
        return self.present

    def is_symlink(self):
        return False

    def mkdir(self, mode):
        assert mode == 0o700 and not self.present
        self.present = True

    def __truediv__(self, name):
        assert name == 'owner.json'
        return SimpleNamespace(open=lambda mode: io.BytesIO())


class Controller(unittest.TestCase):
    def setUp(self):
        self.root, self.events = Root(), []
        self.identities = [{'package': p[0], 'bytes': p[1], 'sha256': p[2]} for p in D.PINS]
        self.stack = contextlib.ExitStack()
        self.addCleanup(self.stack.close)
        def bind(owner, name, **kwargs):
            return self.stack.enter_context(patch.object(owner, name, **kwargs))
        bind(M, 'target', return_value=self.root)
        bind(M.os, 'environ', new={'EXPECTED_SHA': 'a'*40})
        bind(M.signal, 'SIGALRM', new=14, create=True)
        bind(M.signal, 'signal')
        self.alarm = bind(M.signal, 'alarm', create=True)
        bind(M.sys, 'argv', new=['fixed', '--approved-descriptor-proof'])
        self.states = bind(M, '_fd_state', return_value={'fixed': (1, 2, 3)})
        self.children = bind(M, '_children_empty')
        self.preflight = bind(M, 'preflight', return_value={'model': True})
        def parent(budget):
            self.events.append('parent')
            raise D.S.Refusal('lease-membership')
        self.sources = bind(M, '_descriptor_sources', side_effect=parent)
        def acquire(root, budget, identities):
            self.events.append('acquire')
            identities.extend(self.identities)
        self.acquire = bind(M, 'acquire', side_effect=acquire)
        def checks(root, budget):
            self.events.append('guards')
            return ['guard-model']
        self.guards = bind(M, '_descriptor_checks', side_effect=checks)
        def replay(budget):
            self.events.append('replay')
            self.budget = budget
            return {'prepared_only': True, 'qualified': False, 'identities': self.identities}
        self.replay = bind(M, '_descriptor_preparation', side_effect=replay)
        def fixture(root):
            self.events.append('fixture-cleanup')
        self.fixture = bind(M, '_fixture_cleanup', side_effect=fixture)
        def cleanup(root):
            self.events.append('file-cleanup')
            root.present = False
        self.cleanup = bind(M, 'cleanup', side_effect=cleanup)

    def run_main(self):
        output = io.StringIO()
        with contextlib.redirect_stdout(output):
            code = M.descriptor_main()
        return code, json.loads(output.getvalue()), output.getvalue()

    def test_success_requires_ordered_prerequisites_and_cleanup(self):
        code, value, raw = self.run_main()
        self.assertEqual(0, code)
        self.assertEqual(['parent', 'acquire', 'guards', 'replay', 'fixture-cleanup', 'file-cleanup'], self.events)
        self.assertTrue(value['descriptor_proof_complete'])
        self.assertFalse(value['qualified'])
        self.assertFalse(value['executed'])
        self.assertFalse(value['extracted'])
        self.assertFalse(value['static_inventory_complete'])
        self.assertIn('inventory', value)
        self.assertEqual(0, value['owned_descriptor_delta'])
        self.assertFalse(self.root.present)
        self.assertLessEqual(len(raw), 65536)
        self.assertEqual([540, 30, 0], [call.args[0] for call in self.alarm.call_args_list])

    def test_parent_profile_failure_precedes_acquisition(self):
        self.sources.side_effect = D.S.Refusal('lease-directory')
        code, value, raw = self.run_main()
        self.assertEqual(1, code)
        self.assertEqual('lease-directory', value['reason'])
        self.assertEqual('parent-admission', value['stage'])
        self.acquire.assert_not_called()
        self.replay.assert_not_called()
        self.assertNotIn('inventory', value)
        self.assertFalse(self.root.present)

    def test_guard_failure_never_dispatches_replay(self):
        self.guards.side_effect = D.S.Refusal('descriptor-nofollow-error')
        code, value, raw = self.run_main()
        self.assertEqual(1, code)
        self.assertEqual('owned-descriptor-guards', value['stage'])
        self.replay.assert_not_called()
        self.assertNotIn('inventory', value)
        self.assertEqual(0, value['owned_deb_remaining'])

    def test_cleanup_unknown_never_publishes_inventory(self):
        self.fixture.side_effect = OSError('private path and bytes')
        code, value, raw = self.run_main()
        self.assertEqual(1, code)
        self.assertFalse(value['descriptor_proof_complete'])
        self.assertEqual('Unknown', value['owned_descriptor_delta'])
        self.assertNotIn('inventory', value)
        self.assertNotIn('private', raw)
        self.cleanup.assert_not_called()

    def test_replay_and_final_resource_failure_suppress_facts(self):
        for operation in ('replay', 'fd', 'children'):
            with self.subTest(operation=operation):
                child = Controller('test_success_requires_ordered_prerequisites_and_cleanup')
                child.setUp()
                try:
                    if operation == 'replay':
                        child.replay.side_effect = D.S.Refusal('replay-stream-identity')
                    elif operation == 'fd':
                        child.states.side_effect = [{'fixed': (1, 2, 3)}]*3+[{'leaked': (1, 3, 4)}]*2
                    else:
                        child.children.side_effect = [None, D.S.Refusal('descriptor-child-remaining'), D.S.Refusal('descriptor-child-remaining')]
                    code, value, raw = child.run_main()
                    self.assertEqual(1, code)
                    self.assertNotIn('inventory', value)
                    self.assertFalse(value['descriptor_proof_complete'])
                finally:
                    child.doCleanups()

    def test_wrong_inventory_is_not_a_qualification(self):
        self.replay.side_effect = lambda budget: {'prepared_only': True, 'qualified': True, 'identities': self.identities}
        code, value, raw = self.run_main()
        self.assertEqual(1, code)
        self.assertNotIn('inventory', value)
        self.assertEqual('descriptor-replay-identity', value['reason'])

    def test_cleanup_cannot_extend_work_deadline(self):
        previous = self.cleanup.side_effect
        def expire(root):
            previous(root)
            self.budget.deadline = 0
        self.cleanup.side_effect = expire
        code, value, raw = self.run_main()
        self.assertEqual(1, code)
        self.assertFalse(value['descriptor_proof_complete'])
        self.assertEqual('work-deadline', value['reason'])
        self.assertNotIn('inventory', value)

    def test_http_failure_is_closed_and_privacy_safe(self):
        error = M.urllib.error.HTTPError('https://private.invalid/', 404, 'private error', {}, None)
        self.acquire.side_effect = error
        code, value, raw = self.run_main()
        self.assertEqual(1, code)
        self.assertTrue(error.closed)
        self.assertEqual(404, value['http_status'])
        self.assertNotIn('private', raw)
        self.assertNotIn('inventory', value)
        self.assertFalse(self.root.present)

    def test_cleanup_mode_never_acquires_or_replays(self):
        with patch.object(M.sys, 'argv', ['fixed', '--cleanup-descriptor-proof']):
            code, value, raw = self.run_main()
        self.assertEqual(0, code)
        self.assertEqual({'owned_deb_remaining': 0, 'owned_extra_remaining': 0}, value)
        self.acquire.assert_not_called()
        self.preflight.assert_not_called()
        self.replay.assert_not_called()

    def test_oversized_receipt_is_refused_after_cleanup(self):
        self.replay.side_effect = lambda budget: {'prepared_only': True, 'qualified': False, 'identities': self.identities, 'large': 'x'*65536}
        with self.assertRaisesRegex(D.S.Refusal, 'diagnostic-bound'):
            self.run_main()
        self.assertFalse(self.root.present)

    def test_semantic_controls_are_assertion_killed(self):
        require = D.S.require
        controls = ((M, '_must_refuse', lambda *args: None, 'test_success_requires_ordered_prerequisites_and_cleanup'),
                    (D.S, 'require', lambda ok, reason: None if reason == 'descriptor-replay-identity' else require(ok, reason), 'test_wrong_inventory_is_not_a_qualification'),
                    (D.Budget, 'charge', lambda *args: None, 'test_cleanup_cannot_extend_work_deadline'))
        for owner, name, replacement, test in controls:
            with self.subTest(test=test), patch.object(owner, name, replacement):
                result = unittest.TestResult()
                Controller(test).run(result)
                self.assertGreater(len(result.failures), 0)
                self.assertEqual([], result.errors)


class Cleanup(unittest.TestCase):
    def model(self):
        root, owner, extra, first = (MagicMock() for unused in range(4))
        root.name = 'adb-ppa-deb-1'
        root.exists.return_value, root.is_symlink.return_value = True, False
        root.resolve.return_value = root
        root.lstat.return_value = SimpleNamespace(st_mode=stat.S_IFDIR | 0o700, st_uid=1000)
        marker = json.dumps({'head': 'a'*40, 'root': root.name}, sort_keys=True).encode('ascii')
        owner.lstat.return_value = SimpleNamespace(st_mode=stat.S_IFREG | 0o600, st_uid=1000, st_nlink=1, st_size=len(marker))
        owner.open.return_value.__enter__.side_effect = lambda: io.BytesIO(marker)
        extra.exists.return_value, extra.is_symlink.return_value = True, False
        extra.lstat.return_value = SimpleNamespace(st_mode=stat.S_IFREG | 0o600, st_uid=1000, st_nlink=1, st_size=0, st_dev=1, st_ino=7)
        first.lstat.return_value = SimpleNamespace(st_dev=1, st_ino=8)
        root.__truediv__.side_effect = lambda name: owner if name == 'owner.json' else extra if name == '.descriptor-proof-extra' else first
        self.addCleanup(patch.stopall)
        patch.object(M.os, 'getuid', return_value=1000, create=True).start()
        patch.dict(M.os.environ, {'EXPECTED_SHA': 'a'*40}).start()
        return root, owner, extra, first

    def test_only_empty_owned_extra_is_removed(self):
        root, owner, extra, first = self.model()
        M._fixture_cleanup(root)
        extra.unlink.assert_called_once_with()
        root.unlink.assert_not_called()
        first.unlink.assert_not_called()

    def test_foreign_extra_and_marker_refuse_deletion(self):
        root, owner, extra, first = self.model()
        extra.lstat.return_value.st_uid = 0
        with self.assertRaises(D.S.Refusal):
            M._fixture_cleanup(root)
        extra.unlink.assert_not_called()
        extra.lstat.return_value.st_uid = 1000
        owner.open.return_value.__enter__.side_effect = lambda: io.BytesIO(b'foreign')
        with self.assertRaisesRegex(D.S.Refusal, 'marker'):
            M._fixture_cleanup(root)
        extra.unlink.assert_not_called()

    def test_hardlink_and_symlink_are_exactly_bound(self):
        root, owner, extra, first = self.model()
        info = extra.lstat.return_value
        info.st_ino, info.st_size, info.st_nlink = 8, D.PINS[0][1], 2
        M._fixture_cleanup(root)
        extra.unlink.assert_called_once_with()
        extra.unlink.reset_mock()
        info.st_ino = 9
        with self.assertRaises(D.S.Refusal):
            M._fixture_cleanup(root)
        extra.unlink.assert_not_called()
        info.st_mode = stat.S_IFLNK | 0o777
        with patch.object(M.os, 'readlink', return_value=M.filename(D.PINS[0][0])):
            M._fixture_cleanup(root)
        extra.unlink.assert_called_once_with()
        extra.unlink.reset_mock()
        with patch.object(M.os, 'readlink', return_value='../../foreign'):
            with self.assertRaises(D.S.Refusal):
                M._fixture_cleanup(root)
        extra.unlink.assert_not_called()

    def test_workflow_closure_precedes_fixed_entry_and_always_cleanup(self):
        source = (ROOT/'.github/workflows/source-policy-ppa-deb-static.yml').read_text()
        required = ('source_policy_deb_sources.py', 'source_policy_deb_replay.py', 'source_policy_elf_ranges.py',
                    'source_policy_deb_data.py', 'source_policy_deb_stream.py', 'source-policy-ppa-deb-static.py',
                    'source-policy-php-archive-static.py', 'source_policy_linux_minimum_static.py',
                    'source_policy_ppa_join.py', 'source_policy_ppa_storage.py', 'source_policy_ppa_prefix.py',
                    'source-policy-ppa-prefix-proof.py')
        for name in required:
            self.assertLess(source.index(name), source.index('--approved-prefix-proof'))
        self.assertIn('if: always()', source)
        self.assertIn('--reconcile-prefix-proof', source)
        self.assertNotIn('pip install', source)
        self.assertNotIn('apt-get', source)
        tree = ast.parse((ROOT/'scripts/ci/source-policy-ppa-deb-static.py').read_text())
        legacy = next(node for node in tree.body if isinstance(node, ast.FunctionDef) and node.name == 'main')
        self.assertIn('--approved-static-proof', ast.unparse(legacy))
        self.assertNotIn('_descriptor_preparation', ast.unparse(legacy))


if __name__ == '__main__':
    suite = unittest.TestSuite(unittest.defaultTestLoader.loadTestsFromTestCase(case) for case in (Controller, Cleanup))
    output = io.StringIO()
    result = unittest.TextTestRunner(stream=output).run(suite)
    if not result.wasSuccessful():
        sys.stderr.write(output.getvalue()[:3000])
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'skips': len(result.skipped)}))
    sys.exit(not result.wasSuccessful())
