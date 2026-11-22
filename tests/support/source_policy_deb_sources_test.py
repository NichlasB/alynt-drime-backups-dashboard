"""Isolated API/virtual-FD tests; never invokes the native syscall provider."""
import ast
import copy
import io
import json
from pathlib import Path, PurePosixPath
import stat
import sys
from types import SimpleNamespace
import unittest
from unittest.mock import patch, Mock

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
sys.path.insert(0, str(ROOT/'tests/support'))
import source_policy_deb_sources as L
import source_policy_deb_replay as R
import source_policy_deb_sources_fixture as F
import source_policy_deb_replay_test as T
D, E, M = R.D, R.E, T.P.M


class Leases(unittest.TestCase):
    def setUp(self):
        self.values = T.packets()
        self.names = tuple(M.filename(row[0]) for row in D.PINS)
        self.api = F.FakeAPI(self.values, self.names, b'fixed-marker')
        for item in (patch.object(L, '_API', self.api), patch.object(D, 'PINS', T.fixture_pins(self.values))):
            item.start()
            self.addCleanup(item.stop)

    def owner(self):
        return L._SourceSet('/owned', 'adb-ppa-deb-1', b'fixed-marker', self.names, D.Budget(), E.Budget())

    def clean(self):
        self.assertEqual({}, self.api.live)
        self.assertEqual([], self.api.iterators)
        self.assertFalse(self.api.masked)
        closes = [row[1] for row in self.api.events if row[0] == 'close']
        self.assertEqual(len(closes), len(set(closes)))

    def test_successful_replay_is_closed_before_commit(self):
        expected = R.prepare(self.values)
        owner = self.owner()
        adapter, original = R._LeasePreparation(owner), R._Preparation._commit
        def commit(*args):
            self.clean()
            self.assertTrue(owner._closed)
            return original(adapter, *args)
        with patch.object(adapter, '_commit', side_effect=commit):
            actual = adapter.run()
        for key in ('identities', 'packages', 'roles', 'inventory_sha256', 'types', 'primary_entries', 'scanned_entries'):
            self.assertEqual(expected[key], actual[key], key)
        self.assertEqual({'cli', 'mysqlnd', 'mysqli'}, set(actual['roles']))
        self.assertFalse(actual['qualified'])
        self.assertFalse(actual['static_inventory_complete'])
        self.assertEqual('fixed-readonly-descriptor-leases-preparation-only', actual['source_profile'])
        self.assertLessEqual(self.api.max_live, 7)
        self.assertEqual(6, sum(row[0] == 'open' for row in self.api.events))
        self.assertEqual(0, owner._scratch.usage[0])
        self.assertEqual(23, sum(row[0] == 'scan' for row in self.api.events))
        self.assertEqual(26, sum(row[0] == 'pread' and row[1] in self.names and row[3] == len(self.api.bytes[row[1]]) for row in self.api.events))
        sizes = [len(value) for value in self.values]
        full_hash_bytes = 2*sum(sizes)+sum(2*(1+package['data_passes'])*size for package, size in zip(actual['packages'], sizes))
        immutable_hash_bytes = sum(sizes)+sum((package['data_passes']-1)*size for package, size in zip(actual['packages'], sizes))
        self.assertEqual(expected['work_bytes']+full_hash_bytes-immutable_hash_bytes+22*len(b'fixed-marker'), actual['work_bytes'])
        owner.close()
        self.clean()

    def test_short_reads_and_fd_zero_are_not_unavailable(self):
        self.api.short = 31
        owner = self.owner()
        self.assertEqual(0, owner._parent)
        try:
            facts = owner.seal()
        except D.S.Refusal:
            self.fail('A valid fake-backed source must not always return unavailable')
        self.assertEqual(T.fixture_pins(self.values)[0][2], facts[0]['sha256'])
        self.assertEqual({'cli', 'mysqlnd', 'mysqli'}, set(R._LeasePreparation(owner).run()['roles']))
        owner.close()
        self.clean()

    def test_all_hashes_precede_any_parser(self):
        owner = self.owner()
        self.api.bytes[self.names[-1]] = b'X'+self.values[-1][1:]
        with patch.object(D, 'ar_members', side_effect=AssertionError('parser reached')) as parser:
            with self.assertRaisesRegex(D.S.Refusal, 'identity'):
                R._LeasePreparation(owner).run()
            parser.assert_not_called()
        self.clean()

    def test_marker_and_membership_before_parser(self):
        for mutation in ('marker', 'extra', 'missing'):
            with self.subTest(mutation=mutation):
                owner, original = self.owner(), self.api.bytes.copy()
                if mutation == 'marker':
                    self.api.bytes['owner.json'] = b'X'+b'fixed-marker'[1:]
                elif mutation == 'extra':
                    self.api.bytes['fifth'] = b''
                else:
                    del self.api.bytes[self.names[-1]]
                with self.assertRaises(D.S.Refusal):
                    R._LeasePreparation(owner).run()
                self.api.bytes = original
                self.clean()

    def test_metadata_and_binding_mutations(self):
        for field, value in (('st_ino', 99), ('st_nlink', 2), ('st_uid', 0), ('st_size', 0),
                             ('st_mode', stat.S_IFLNK | 0o600), ('st_mtime_ns', 2), ('st_ctime_ns', 2)):
            with self.subTest(field=field):
                owner, original = self.owner(), copy.copy(self.api.infos[self.names[0]])
                setattr(self.api.infos[self.names[0]], field, value)
                with self.assertRaisesRegex(D.S.Refusal, 'changed'):
                    R._LeasePreparation(owner).run()
                self.api.infos[self.names[0]] = original
                self.clean()

    def test_parent_child_timestamps_and_atime_are_not_frozen(self):
        owner = self.owner()
        self.api.infos['/owned'].st_mtime_ns = 44
        self.api.infos[self.names[0]].st_atime_ns = 99
        self.assertEqual(3, len(owner.seal()))
        self.api.infos['/owned'].st_ino = 99
        with self.assertRaisesRegex(D.S.Refusal, 'parent-changed'):
            owner.check()
        owner.close()
        self.clean()

    def test_initial_permissions_and_source_types(self):
        for name, field, value in (('/owned', 'st_mode', stat.S_IFDIR | 0o777),
                ('adb-ppa-deb-1', 'st_mode', stat.S_IFDIR | 0o750),
                (self.names[0], 'st_mode', stat.S_IFREG | 0o700),
                (self.names[0], 'st_mode', stat.S_IFREG | 0o622),
                (self.names[0], 'st_mode', stat.S_IFIFO | 0o600),
                (self.names[0], 'st_nlink', 2), (self.names[0], 'st_uid', 0)):
            with self.subTest(name=name, field=field, value=value):
                original = copy.copy(self.api.infos[name])
                setattr(self.api.infos[name], field, value)
                with self.assertRaises(D.S.Refusal):
                    self.owner()
                self.api.infos[name] = original
                self.clean()
        for name in self.names:
            self.api.infos[name].st_mode = stat.S_IFREG | 0o644
        owner = self.owner()
        self.assertEqual(3, len(owner.seal()))
        owner.close()
        self.clean()

    def test_open_binding_race(self):
        def race(operation, *args):
            if operation == 'open' and args[0] == self.names[0]:
                self.api.infos[self.names[0]].st_ino += 1
        self.api.fault = race
        with self.assertRaisesRegex(D.S.Refusal, 'lease-file'):
            self.owner()
        self.clean()

    def test_access_and_inheritance_guards(self):
        for method, value in (('flags', 1), ('flags', 2), ('inheritable', True)):
            with self.subTest(method=method, value=value), patch.object(self.api, method, return_value=value):
                with self.assertRaisesRegex(D.S.Refusal, 'readonly'):
                    self.owner()
                self.clean()

    def test_registration_precedes_unmask(self):
        previous = self.api.restore
        def interrupt(mask):
            previous(mask)
            if len(self.api.live) == 1:
                raise KeyboardInterrupt()
        with patch.object(self.api, 'restore', side_effect=interrupt):
            with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
                self.owner()
        self.clean()
        self.assertEqual(1, sum(row[0] == 'close' for row in self.api.events))

    def test_interrupt_at_each_open_cleans_predecessors(self):
        for target in ('/owned', 'adb-ppa-deb-1', 'owner.json', *self.names):
            with self.subTest(target=target):
                def interrupt(operation, *args):
                    if operation == 'open' and args[0] == target:
                        raise KeyboardInterrupt()
                self.api.fault = interrupt
                with self.assertRaises(KeyboardInterrupt):
                    self.owner()
                self.clean()

    def test_close_error_blocks_publication_and_is_not_retried(self):
        owner = self.owner()
        adapter = R._LeasePreparation(owner)
        def fail(operation, *args):
            if operation == 'close' and args[1] == self.names[1]:
                raise OSError('ambiguous close')
        self.api.fault = fail
        with patch.object(adapter, '_commit', side_effect=AssertionError('published')) as commit:
            with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
                adapter.run()
            commit.assert_not_called()
        with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            owner.close()
        self.clean()

    def test_cursor_bounds_eof_growth_and_retirement(self):
        owner, cursor = self.owner(), None
        cursor = owner.cursor(0)
        for method, args in ((cursor.read, (True,)), (cursor.read, (65537,)),
                             (cursor.seek, (-1,)), (cursor.seek, (True,)), (cursor.seek, (0, True))):
            with self.assertRaises(D.S.Refusal):
                method(*args)
        self.assertFalse(hasattr(cursor, 'fileno'))
        self.assertFalse(hasattr(cursor, 'write'))
        self.api.bytes[self.names[0]] = b''
        with self.assertRaisesRegex(D.S.Refusal, 'physical-eof'):
            cursor.read(1)
        self.api.bytes[self.names[0]] = self.values[0]+b'growth'
        cursor.seek(len(self.values[0]))
        charged = owner._budget.total
        with self.assertRaisesRegex(D.S.Refusal, 'physical-eof'):
            cursor.read(1)
        self.assertEqual(charged+1, owner._budget.total)
        owner.close()
        with self.assertRaisesRegex(D.S.Refusal, 'retired'):
            cursor.tell()
        self.clean()

    def test_final_seal_catches_post_scan_change(self):
        owner = self.owner()
        adapter = R._LeasePreparation(owner)
        original = adapter._package
        def corrupt(index, pin):
            result = original(index, pin)
            if index == 2:
                self.api.bytes[self.names[0]] = b'X'+self.values[0][1:]
            return result
        with patch.object(adapter, '_package', side_effect=corrupt):
            with self.assertRaisesRegex(D.S.Refusal, 'identity'):
                adapter.run()
        self.clean()

    def test_post_stream_hash(self):
        owner = self.owner()
        adapter = R._LeasePreparation(owner)
        original = adapter._scan
        def corrupt(*args):
            result = original(*args)
            self.api.bytes[self.names[0]] = b'X'+self.values[0][1:]
            return result
        with patch.object(adapter, '_scan', side_effect=corrupt):
            with self.assertRaisesRegex(D.S.Refusal, 'identity'):
                adapter.run()
        self.clean()

    def test_parser_failure_cleans_scratch_and_sources(self):
        owner = self.owner()
        with patch.object(D, 'ar_members', side_effect=D.S.Refusal('synthetic-parser')):
            with self.assertRaisesRegex(D.S.Refusal, 'synthetic-parser'):
                R._LeasePreparation(owner).run()
        self.clean()
        self.assertEqual(0, owner._scratch.usage[0])

    def test_fake_decoder_release_before_source_teardown(self):
        values = T.packets('.gz')
        self.api.bytes.update(dict(zip(self.names, values)))
        for name, value in zip(self.names, values):
            self.api.infos[name].st_size = len(value)
        with patch.object(D, 'PINS', T.fixture_pins(values)), patch.object(R.C, 'Decoder', T.FakeDecoder):
            owner = self.owner()
            result = R._LeasePreparation(owner).run()
            self.assertEqual({'cli', 'mysqlnd', 'mysqli'}, set(result['roles']))
            self.assertTrue(all(decoder.closed for decoder in T.FakeDecoder.instances))
        self.clean()

    def test_deadline_and_scratch_refusal(self):
        for mode in ('deadline', 'scratch'):
            owner = self.owner()
            if mode == 'deadline':
                owner._budget.deadline = 0
            else:
                owner._scratch._retained = E.SCRATCH_LIMIT
            with self.assertRaises((D.S.Refusal, E.Refusal)):
                R._LeasePreparation(owner).run()
            self.clean()

    def test_exact_source_set_not_callback(self):
        with self.assertRaisesRegex(D.S.Refusal, 'source-owner'):
            R._LeasePreparation(Mock())

    def test_profile_unavailable_before_open(self):
        with patch.object(self.api, 'profile', side_effect=D.S.Refusal('lease-capabilities')):
            with self.assertRaisesRegex(D.S.Refusal, 'capabilities'):
                self.owner()
        self.assertFalse(any(row[0] == 'open' for row in self.api.events))

    def test_manual_entry_policy_is_reused_but_not_selected(self):
        class FakePath(PurePosixPath):
            def resolve(self):
                return self
            def is_symlink(self):
                return False
        env = {'GITHUB_ACTIONS': 'true', 'GITHUB_EVENT_NAME': 'workflow_dispatch',
               'GITHUB_REPOSITORY': 'NichlasB/alynt-drime-backups-dashboard', 'GITHUB_REF': 'refs/heads/master',
               'GITHUB_ACTOR': 'NichlasB', 'GITHUB_WORKFLOW_REF': 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/'+M.WORKFLOW+'@refs/heads/master',
               'EXPECTED_SHA': 'a'*40, 'GITHUB_SHA': 'a'*40, 'GITHUB_RUN_ATTEMPT': '1',
               'GITHUB_RUN_ID': '1', 'RUNNER_TEMP': '/owned'}
        marker = json.dumps({'head': 'a'*40, 'root': 'adb-ppa-deb-1'}, sort_keys=True).encode('ascii')
        self.api.bytes['owner.json'] = marker
        self.api.infos['owner.json'].st_size = len(marker)
        budget = D.Budget()
        budget.charge(123)
        deadline = budget.deadline
        with patch.dict(M.os.environ, env, clear=True), patch.object(M, 'Path', FakePath):
            result = M._descriptor_preparation(budget)
            self.assertEqual(deadline, budget.deadline)
            self.assertEqual(result['work_bytes'], budget.total)
            self.assertGreater(budget.total, 123)
            with patch.object(L, '_SourceSet', side_effect=AssertionError('IO reached')):
                with self.assertRaisesRegex(D.S.Refusal, 'owner-budget'):
                    M._descriptor_preparation(Mock())
                expired = D.Budget()
                expired.deadline = 0
                # The source owner, not a caller flag, rejects expiry before IO.
            with self.assertRaisesRegex(D.S.Refusal, 'work-deadline'):
                M._descriptor_preparation(expired)
        self.assertEqual(3, len(result['identities']))
        self.clean()
        with patch.dict(M.os.environ, {}, clear=True):
            count = len(self.api.events)
            with self.assertRaisesRegex(D.S.Refusal, 'exact-job'):
                M._descriptor_preparation(budget)
            self.assertEqual(count, len(self.api.events))
        tree = ast.parse((ROOT/'scripts/ci/source-policy-ppa-deb-static.py').read_text())
        main = next(node for node in tree.body if isinstance(node, ast.FunctionDef) and node.name == 'main')
        self.assertNotIn('_descriptor_preparation', ast.unparse(main))

    def test_native_binding_profile_and_flags_without_syscalls(self):
        api = L._NativeAPI()
        fcntl = SimpleNamespace(F_GETFL=3, fcntl=Mock(return_value=0))
        with patch.object(L.sys, 'platform', 'linux'), patch.object(L.os, 'getuid', return_value=1000, create=True), \
                patch.object(L.os, 'geteuid', return_value=1000, create=True), \
                patch.object(L.os, 'supports_dir_fd', {L.os.open, L.os.stat}), \
                patch.object(L.os, 'supports_fd', {L.os.scandir}), \
                patch.object(L.os, 'supports_follow_symlinks', {L.os.stat}), \
                patch.object(L.os, 'pread', Mock(), create=True), \
                patch.object(L.signal, 'pthread_sigmask', Mock(), create=True), \
                patch.object(L.os, 'O_NOFOLLOW', 0x10000, create=True), \
                patch.object(L.os, 'O_CLOEXEC', 0x20000, create=True), \
                patch.object(L.os, 'O_NONBLOCK', 0x40000, create=True), \
                patch.object(L.os, 'O_DIRECTORY', 0x80000, create=True), \
                patch.object(L.os, 'O_ACCMODE', 3, create=True), \
                patch.dict(sys.modules, {'fcntl': fcntl}):
            self.assertEqual((1000, 0x70000, 0x80000), api.profile())
            self.assertEqual(0, api.flags(7))  # O_RDONLY is zero, not a bit test.
            fcntl.fcntl.assert_called_once_with(7, 3)
            with patch.object(L.os, 'geteuid', return_value=0):
                with self.assertRaisesRegex(D.S.Refusal, 'capabilities'):
                    api.profile()
            with patch.object(L.os, 'supports_fd', set()):
                with self.assertRaisesRegex(D.S.Refusal, 'capabilities'):
                    api.profile()
            with patch.object(L.threading, 'current_thread', return_value=object()):
                with self.assertRaisesRegex(D.S.Refusal, 'platform'):
                    api.profile()
        with patch.object(L.sys, 'platform', 'win32'):
            with self.assertRaisesRegex(D.S.Refusal, 'platform'):
                api.profile()

    def test_decoder_explicitly_denies_source_descriptor_inheritance(self):
        process = Mock(returncode=0)
        process.poll.return_value = 0
        with patch.object(R.C.subprocess, 'Popen', return_value=process) as popen, \
                patch.object(R.C.selectors, 'DefaultSelector'), patch.object(R.C.os, 'set_blocking'):
            decoder = R.C.Decoder(Mock(), '.gz', D.Budget())
            self.assertIs(True, popen.call_args.kwargs['close_fds'])
            self.assertEqual((), popen.call_args.kwargs['pass_fds'])
            decoder.close()

    def test_ambiguous_iterator_close_and_mask_restore_are_terminal(self):
        for mode in ('iterator-close', 'restore'):
            with self.subTest(mode=mode):
                def fail(operation, *args):
                    if operation == 'iterator-close':
                        raise OSError('synthetic close')
                previous = self.api.restore
                def restore(mask):
                    previous(mask)
                    if self.api.iterators:
                        raise InterruptedError('synthetic restore')
                with patch.object(self.api, 'restore', side_effect=restore) if mode == 'restore' else patch.object(self.api, 'fault', fail):
                    with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
                        self.owner()
                self.clean()

    def test_preexisting_mask_restored_and_no_timer_or_handler_changes(self):
        self.api.masked = True
        owner = self.owner()
        owner.close()
        self.assertTrue(self.api.masked)
        self.api.masked = False
        self.clean()
        tree = ast.parse((ROOT/'scripts/ci/source_policy_deb_sources.py').read_text())
        calls = {node.func.attr for node in ast.walk(tree) if isinstance(node, ast.Call) and isinstance(node.func, ast.Attribute)}
        self.assertFalse(calls & {'alarm', 'signal', 'write', 'dup', 'dup2', 'chmod', 'unlink', 'rmdir'})

    def test_semantic_controls_are_assertion_killed(self):
        require = D.S.require
        controls = (
            (L._SourceSet, '_membership', lambda self: None, 'test_extra_member_is_a_required_refusal'),
            (L._SourceSet, '_bound', lambda *args: None, 'test_post_admission_inode_is_a_required_refusal'),
            (D.S, 'require', lambda ok, reason: None if reason == 'lease-readonly' else require(ok, reason), 'test_access_and_inheritance_guards'),
            (D.S, 'require', lambda ok, reason: None if reason == 'lease-source-identity' else require(ok, reason), 'test_post_stream_hash'),
            (D.S, 'require', lambda ok, reason: None if reason == 'lease-physical-eof' else require(ok, reason), 'test_cursor_bounds_eof_growth_and_retirement'),
            (L._SourceSet, 'seal', lambda self: [], 'test_all_hashes_precede_any_parser'),
            (L._SourceSet, 'close', lambda self: None, 'test_successful_replay_is_closed_before_commit'),
            (L._SourceSet, 'seal', lambda self: (_ for _ in ()).throw(D.S.Refusal('unavailable')), 'test_short_reads_and_fd_zero_are_not_unavailable'),
        )
        for owner, name, replacement, test in controls:
            with self.subTest(control=test), patch.object(owner, name, replacement):
                result = unittest.TestResult()
                Leases(test).run(result)
                self.assertGreater(len(result.failures), 0, test)
                self.assertEqual([], result.errors, test)

    def test_extra_member_is_a_required_refusal(self):
        owner = self.owner()
        self.api.bytes['fifth'] = b''
        try:
            with self.assertRaises(D.S.Refusal):
                owner.check(True)
        finally:
            owner.close()

    def test_post_admission_inode_is_a_required_refusal(self):
        owner = self.owner()
        self.api.infos[self.names[0]].st_ino += 1
        try:
            with self.assertRaises(D.S.Refusal):
                owner.check()
        finally:
            owner.close()

    def test_cleanup_deadline_unknown_does_not_claim_retirement(self):
        owner = self.owner()
        def advance(operation, *args):
            if operation == 'close':
                self.api.now += 31
        self.api.fault = advance
        with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            owner.close()
        self.assertEqual(5, len(self.api.live))  # Modelled uncertainty, not zero.
        calls = sum(row[0] == 'close' for row in self.api.events)
        with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            owner.close()
        self.assertEqual(calls, sum(row[0] == 'close' for row in self.api.events))

    def test_invalid_fd_never_establishes_close_ownership(self):
        for value in (True, -1, None, '7'):
            with self.subTest(value=value), patch.object(self.api, 'open', return_value=value):
                with self.assertRaisesRegex(D.S.Refusal, 'descriptor-shape'):
                    self.owner()
                self.clean()

    def test_read_shape_and_reverse_close_order(self):
        owner = self.owner()
        cursor = owner.cursor(0)
        with patch.object(self.api, 'pread', return_value=b'oversize'):
            with self.assertRaisesRegex(D.S.Refusal, 'read-shape'):
                cursor.read(1)
        owner.close()
        closes = [row[2] for row in self.api.events if row[0] == 'close']
        self.assertEqual([*reversed(self.names), 'owner.json', 'adb-ppa-deb-1', '/owned'], closes)
        self.clean()

    def test_actual_descriptor_profile_is_inside_output_barrier(self):
        owner = self.owner()
        adapter = R._LeasePreparation(owner)
        owner.close()
        adapter._roles = {'cli': {'data': ''}, 'mysqlnd': {}, 'mysqli': {}}
        size = len(json.dumps(adapter._commit([], [])).encode('ascii'))
        adapter._roles['cli']['data'] = 'x'*(65536-size)
        self.assertEqual(65536, len(json.dumps(adapter._commit([], [])).encode('ascii')))
        adapter._roles['cli']['data'] += 'x'
        with self.assertRaisesRegex(D.S.Refusal, 'prepared-output-bound'):
            adapter._commit([], [])
        self.clean()


if __name__ == '__main__':
    suite = unittest.defaultTestLoader.loadTestsFromTestCase(Leases)
    output = io.StringIO()
    result = unittest.TextTestRunner(stream=output).run(suite)
    if not result.wasSuccessful():
        sys.stderr.write(output.getvalue())
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'skips': len(result.skipped)}))
    sys.exit(not result.wasSuccessful())
