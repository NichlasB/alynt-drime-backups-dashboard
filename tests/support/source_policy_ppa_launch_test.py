"""RAM-only fixed launch/retirement tests;no candidate,process,FD or file IO."""
import ast
import io
import json
from pathlib import Path, PurePosixPath
import signal
import stat
import sys
from types import SimpleNamespace
import unittest
from unittest.mock import Mock, patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
sys.path.insert(0, str(ROOT/'tests/support'))
import source_policy_ppa_launch as X
import source_policy_ppa_prefix_controller_test as C
import source_policy_ppa_join_test as T
J, Q, S = X.J, C.Q, X.S


def facts(modules=False):
    return {'php': '8.5.12', 'sapi': 'cli', 'os': 'Linux', 'arch': 'x86_64', 'word_size': 8, 'zts': 0,
            'binary_matches': True, 'extension_dir_matches': True, 'ini_loaded': False, 'ini_scanned': False,
            'included': 0, 'prepend': '', 'append': '', 'pdo_mysql': False,
            'modules': sorted(['Core', 'json', 'pcre', 'standard']+(['mysqlnd', 'mysqli'] if modules else [])),
            'functions': dict.fromkeys(X.REQUIRED, True), 'optional': {'ctype_digit': False, 'iconv': False, 'mb_strlen': False},
            'client': 'mysqlnd 8.5.12' if modules else None, 'mysqlnd': 'mysqlnd 8.5.12' if modules else None,
            'mysqli': '8.5.12' if modules else None, 'report_mode': 3 if modules else None,
            'stats': {'connect_success': '0', 'connect_failure': '0', 'com_query': '0'} if modules else None}


class Pipe:
    def __init__(self, fd, value):
        self.fd, self.value, self.closed = fd, value, False

    def close(self):
        self.closed = True


class Process:
    def __init__(self, api, argv, stdout, stderr):
        self.api, self.args, self.pid = api, argv, 10+len(api.processes)
        self.stdout, self.stderr = Pipe(self.pid*2, stdout), Pipe(self.pid*2+1, stderr)
        self.code = None

    def poll(self):
        return self.code

    def wait(self, timeout):
        assert timeout >= 0
        if self.api.bad_wait:
            raise TimeoutError('synthetic-wait')
        if self.code is None:
            self.code = self.api.exit
        return self.code


class Selector:
    def __init__(self, process):
        self.process, self.closed = process, False
        self.map = {pipe.fd: SimpleNamespace(fd=pipe.fd, fileobj=pipe) for pipe in (process.stdout, process.stderr)}

    def get_map(self):
        return self.map

    def select(self, timeout):
        assert 0 <= timeout <= 0.05
        if self.process.api.expire:
            self.process.api.now += 21
            return []
        return [(key, 1) for key in self.map.values()]

    def unregister(self, pipe):
        self.map.pop(pipe.fd)

    def close(self):
        self.closed = True


class Calls:
    def __init__(self, model):
        self.model, self.now, self.processes, self.selectors, self.kills = model, 100, [], [], []
        self.change, self.stderr, self.exit, self.expire, self.bad_wait = None, b'', 0, False, False

    def profile(self):
        pass  # RAM only;never invoke the real process provider.

    def spawn(self, argv):
        assert self.model.calls.masked
        assert argv[0] == '/owned/adb-ppa-runtime-1/php' and argv[1] == '-n'
        assert stat.S_IMODE(self.model.calls.nodes['php'].st_mode) == 0o500
        assert all(stat.S_IMODE(self.model.calls.nodes[leaf].st_mode) == 0o400 for leaf in ('mysqlnd.so', 'mysqli.so'))
        modules = 'extension=/owned/adb-ppa-runtime-1/mysqlnd.so' in argv
        if modules:
            assert argv.index('extension=/owned/adb-ppa-runtime-1/mysqlnd.so') < argv.index('extension=/owned/adb-ppa-runtime-1/mysqli.so')
        row = facts(modules)
        if self.change:
            self.change(row, modules)
        process = Process(self, argv, json.dumps(row).encode('ascii'), self.stderr)
        self.processes.append(process)
        return process

    def identity(self, process):
        assert process in self.processes
        return process.pid, b'100'

    def selector(self, process):
        value = Selector(process)
        self.selectors.append(value)
        return value

    def read(self, fd, count):
        assert count == 4096
        pipe = next(pipe for process in self.processes for pipe in (process.stdout, process.stderr) if pipe.fd == fd)
        value, pipe.value = pipe.value[:97], pipe.value[97:]
        return value

    def clock(self):
        return self.now

    def kill(self, process, identity, argv):
        assert self.identity(process) == identity and tuple(process.args) == argv
        self.kills.append(process.pid)
        process.code = -9


class Launch(unittest.TestCase):
    def setUp(self):
        self.stack = []
        self.model = T.Join('test_positive_all_streams_close_before_seal_and_cleanup')
        self.model.setUp()
        self.addCleanup(self.model.doCleanups)
        self.api = Calls(self.model)
        original = self.model.calls.chmod
        def chmod(fd, mode):
            if mode == 0o500:
                self.assertEqual('php', self.model.calls.live[fd][0])
                self.assertEqual(0, self.model.calls.flags(fd))
                self.model.calls.event('chmod', 'php')
                self.model.calls.nodes['php'].st_mode = stat.S_IFREG | mode
            else:
                original(fd, mode)
        for control in (
            patch.object(Q.M, 'target', return_value=PurePosixPath('/owned/adb-ppa-deb-1')),
            patch.dict(Q.os.environ, {'EXPECTED_SHA': 'a'*40, 'GITHUB_RUN_ID': '1', 'PPA_EXECUTION_CONTRACT': X.CONTRACT}),
            patch.object(J.N, '_PosixCalls', return_value=self.model.calls),
            patch.object(X, '_NativeProcess', return_value=self.api),
            patch.object(self.model.calls, 'chmod', side_effect=chmod),
            patch.object(self.model.calls, 'clock', side_effect=self.api.clock),
            patch.object(self.model.source, 'clock', side_effect=self.api.clock),
            patch.object(X.time, 'monotonic', side_effect=self.api.clock),
        ):
            control.start()
            self.addCleanup(control.stop)
        self.model.budget.deadline = 640
        self.entry = Q._ConnectionFreeEntry(self.model.budget)

    def run_entry(self):
        return self.entry.run()

    def clean(self):
        self.model.clean()
        self.assertEqual(0, self.entry._context._scratch.usage[0])
        self.assertTrue(all(pipe.closed for process in self.api.processes for pipe in (process.stdout, process.stderr)))
        self.assertTrue(all(selector.closed for selector in self.api.selectors))

    def test_positive_both_fixed_phases_modes_retirement_and_false_grants(self):
        previous = J._ENTRY, J._JoinedOutput, X._PROCESS
        try:
            result = self.run_entry()
        except S.Refusal as error:
            self.fail('A valid RAM connection-free join must complete: '+str(error))
        observed = result['connection_free']
        self.assertTrue(observed['complete'])
        self.assertTrue(observed['executed'])
        self.assertEqual(2, len(observed['observations']))
        self.assertEqual(2, len(self.api.processes))
        self.assertEqual(0, observed['last_exit'])
        self.assertEqual([], self.api.kills)
        self.assertIs(previous[0], J._ENTRY)
        self.assertIs(previous[1], J._JoinedOutput)
        self.assertIs(previous[2], X._PROCESS)
        self.assertEqual('0400', result['output_mode'])
        self.assertFalse(observed['release_unknown'])
        self.clean()

    def test_default_process_boundary_refuses_before_mode_or_spawn(self):
        closed = X._ClosedProcess()
        output = SimpleNamespace(_context=object())
        with patch.object(X, '_PROCESS', closed), self.assertRaisesRegex(S.Refusal, 'process-disabled'):
            X._Launch(output).run()
        self.assertEqual([], self.api.processes)

    def test_unknown_decoder_blocks_mode_change_and_candidate(self):
        context = self.entry._context
        context._decoder_unknown, context._source_count = True, 2
        output = SimpleNamespace(_context=context, _storage=self.model.calls, _sealed=True, _active=None)
        with patch.object(X, '_PROCESS', self.api), self.assertRaisesRegex(S.Refusal, 'source-output-barrier'):
            X._Launch(output).run()
        self.assertEqual([], self.api.processes)
        self.assertFalse(any(event[0] == 'chmod' for event in self.model.calls.events))

    def test_wrong_version_and_configuration_refuse_without_fallback(self):
        self.api.change = lambda row, modules: row.update(php='8.5.13')
        with self.assertRaisesRegex(S.Refusal, 'runtime-profile'):
            self.run_entry()
        self.assertEqual(1, len(self.api.processes))
        self.clean()

    def test_wrong_client_module_version_refuses(self):
        def change(row, modules):
            if modules:
                row['mysqli'] = '8.5.13'
        self.api.change = change
        with self.assertRaisesRegex(S.Refusal, 'client-profile'):
            self.run_entry()
        self.clean()

    def test_extra_module_is_not_an_allowed_expansion(self):
        def change(row, modules):
            if modules:
                row['modules'] = sorted(row['modules']+['extra'])
        self.api.change = change
        with self.assertRaisesRegex(S.Refusal, 'module-delta'):
            self.run_entry()
        self.clean()

    def test_missing_required_function_refuses_but_optional_does_not_load(self):
        self.api.change = lambda row, modules: row['functions'].update(hash_hmac=False)
        with self.assertRaisesRegex(S.Refusal, 'required-functions'):
            self.run_entry()
        self.clean()

    def test_raw_stderr_is_not_returned_as_diagnostics(self):
        self.api.stderr = b'/private/path secret-cookie'
        with self.assertRaisesRegex(S.Refusal, 'baseline-startup'):
            self.run_entry()
        receipt = self.entry._context._candidate_observation.receipt()
        self.assertNotIn('private', json.dumps(receipt))
        self.assertTrue(receipt['executed'])
        self.assertFalse(receipt['complete'])
        self.clean()

    def test_timeout_kills_only_bound_process_and_retires_before_cleanup(self):
        self.api.expire = True
        with self.assertRaisesRegex(S.Refusal, 'probe-deadline'):
            self.run_entry()
        self.assertEqual([self.api.processes[0].pid], self.api.kills)
        self.clean()

    def test_live_output_bound_retires_child_without_raw_disclosure(self):
        self.api.stderr = b'X'*20000
        with self.assertRaisesRegex(S.Refusal, 'output-bound'):
            self.run_entry()
        self.assertEqual([self.api.processes[0].pid], self.api.kills)
        self.assertLessEqual(self.entry._context._candidate_observation.output_bytes, 16384+4096)
        self.clean()

    def test_unknown_reap_retains_namespace_without_retry(self):
        self.api.bad_wait = True
        with self.assertRaises(S.Refusal):
            self.run_entry()
        self.assertTrue(self.entry._context._candidate_observation.unknown)
        self.assertTrue(self.entry._context._decoder_unknown)
        self.assertTrue(self.model.calls.data)
        self.assertEqual([self.api.processes[0].pid], self.api.kills)  # One bound retirement;no retry after ambiguous reap.
        self.assertFalse(any(event[0] == 'unlink' for event in self.model.calls.events))

    def test_mode_restore_failure_cannot_publish_completed_observation(self):
        def fault(op, *args):
            if op == 'chmod' and args == ('php',) and len(self.api.processes) == 2:
                raise OSError('synthetic-restore')
        self.model.calls.fault = fault
        with self.assertRaises(S.Refusal):
            self.run_entry()
        observed = self.entry._context._candidate_observation
        self.assertTrue(observed.started)
        self.assertTrue(observed.unknown)
        self.assertFalse(observed.complete)
        self.assertFalse(any(event[0] == 'unlink' for event in self.model.calls.events))

    def test_registration_precedes_signal_restore(self):
        def fault(op, *args):
            if op == 'restore' and not self.model.calls.masked and hasattr(self.entry._context, '_candidate_observation'):
                owner = self.entry._context._candidate_observation
                storage = self.entry._context._storage
                files = [row[0] for row in storage._records if not row[1] and row[0] not in (storage._parent, storage._root)]
                if files:
                    self.assertIn(owner.mode_fd, files)
                if self.api.processes and self.api.processes[-1].poll() is None:
                    self.assertIs(owner.process, self.api.processes[-1])
                    self.assertEqual(self.api.identity(owner.process), owner.identity)
        self.model.calls.fault = fault
        try:
            self.run_entry()
        except S.Refusal as error:
            self.fail('Valid acquisition must remain registered across unmask: '+str(error))
        self.clean()

    def test_fd_metadata_drift_prevents_executable_mode_and_spawn(self):
        original = X._Launch.run
        def changed(owner):
            self.model.calls.nodes['php'].st_nlink = 2
            return original(owner)
        with patch.object(X._Launch, 'run', changed), self.assertRaises(S.Refusal):
            self.run_entry()
        self.assertEqual([], self.api.processes)
        self.assertEqual(0o400, stat.S_IMODE(self.model.calls.nodes['php'].st_mode))
        self.assertFalse(any(event[0] == 'unlink' for event in self.model.calls.events))

    def test_semantic_controls_are_assertion_killed(self):
        original = S.require
        text = (ROOT/'scripts/ci/source_policy_ppa_launch.py').read_text()
        owner = next(node for node in ast.parse(text).body if isinstance(node, ast.ClassDef) and node.name == '_Launch')
        source = ast.get_source_segment(text, next(node for node in owner.body if isinstance(node, ast.FunctionDef) and node.name == '_metadata'))
        before = "output._check_file('php', self.mode_fd, J.P._CATALOG[0][4], mode)"
        self.assertIn(before, source)
        scope = dict(vars(X))
        exec(compile(source.replace(before, 'pass  # semantic control'), '<trusted-launch-semantic-control>', 'exec'), scope)
        run_source = ast.get_source_segment(text, next(node for node in owner.body if isinstance(node, ast.FunctionDef) and node.name == 'run'))
        self.assertIn('storage._critical(acquire_mode)', run_source)
        run_scope = dict(vars(X))
        exec(compile(run_source.replace('storage._critical(acquire_mode)', 'acquire_mode()'), '<trusted-mode-acquisition-control>', 'exec'), run_scope)
        controls = (
            ('always-unavailable', 'test_positive_both_fixed_phases_modes_retirement_and_false_grants', patch.object(X._Launch, 'run', side_effect=S.Refusal('semantic'))),
            ('required-function', 'test_missing_required_function_refuses_but_optional_does_not_load', patch.object(S, 'require', side_effect=lambda ok, why: None if why == 'candidate-required-functions' else original(ok, why))),
            ('module-delta', 'test_extra_module_is_not_an_allowed_expansion', patch.object(S, 'require', side_effect=lambda ok, why: None if why == 'candidate-module-delta' else original(ok, why))),
            ('fd-before-mode', 'test_fd_metadata_drift_prevents_executable_mode_and_spawn', patch.object(X._Launch, '_metadata', scope['_metadata'])),
            ('mode-before-unmask', 'test_registration_precedes_signal_restore', patch.object(X._Launch, 'run', run_scope['run'])),
        )
        for label, name, control in controls:
            with self.subTest(label=label), control:
                result = unittest.TextTestRunner(stream=io.StringIO()).run(Launch(name))
                self.assertGreater(len(result.failures), 0, label)
                self.assertEqual([], result.errors, label)


class NativeShape(unittest.TestCase):
    def test_nonroot_linux_identity_is_required_before_process_effects(self):
        with (patch.object(X.sys, 'platform', 'linux'),
              patch.dict(sys.modules, {'resource': Resources()}),
              patch.object(X.os, 'getuid', return_value=0, create=True) as uid,
              patch.object(X.os, 'geteuid', return_value=0, create=True) as euid):
            with self.assertRaisesRegex(S.Refusal, 'process-platform'):
                X._NativeProcess().profile()
            uid.return_value, euid.return_value = 1000, 0
            with self.assertRaisesRegex(S.Refusal, 'process-platform'):
                X._NativeProcess().profile()
            euid.return_value = 1000
            X._NativeProcess().profile()

    def controller(self, observed=True):
        model = C.Controller('test_positive_requires_fresh_guards_and_retirement')
        model.setUp()
        self.addCleanup(model.doCleanups)
        control = patch.object(X, '_preflight', side_effect=model.preflight)
        control.start()
        self.addCleanup(control.stop)
        if observed:
            model.context._candidate_observation = SimpleNamespace(started=True, receipt=lambda: {'complete': True, 'release_unknown': False})
        return model

    def test_controller_has_separate_truthful_connection_free_success(self):
        model = self.controller()
        with (patch.object(Q.sys, 'argv', ['fixed', '--approved-connection-free-proof']),
              patch.dict(Q.os.environ, {'PPA_EXECUTION_CONTRACT': X.CONTRACT}),
              patch.object(Q, '_ConnectionFreeEntry', return_value=model.entry)):
            code, receipt = model.run_main()
        self.assertEqual(0, code)
        self.assertEqual('ADBPPARUNTIME1', receipt['format'])
        self.assertTrue(receipt['connection_free_complete'])
        self.assertTrue(receipt['executed'])
        self.assertFalse(receipt['qualified'])
        self.assertFalse(receipt['dependency_closure_verified'])
        self.assertFalse(receipt['production_wiring'])

    def test_materialization_alone_cannot_mint_execution_success(self):
        model = self.controller(observed=False)
        with (patch.object(Q.sys, 'argv', ['fixed', '--approved-connection-free-proof']),
              patch.dict(Q.os.environ, {'PPA_EXECUTION_CONTRACT': X.CONTRACT}),
              patch.object(Q, '_ConnectionFreeEntry', return_value=model.entry)):
            code, receipt = model.run_main()
        self.assertEqual(1, code)
        self.assertFalse(receipt['connection_free_complete'])
        self.assertFalse(receipt['executed'])
        self.assertEqual('candidate-result-barrier', receipt['reason'])

    def test_contract_refusal_precedes_acquisition_and_tool_preflight(self):
        model = self.controller()
        with patch.object(Q.sys, 'argv', ['fixed', '--approved-connection-free-proof']):
            with self.assertRaisesRegex(S.Refusal, 'candidate-contract'):
                Q.main()
        model.preflight.assert_not_called()
        model.acquire.assert_not_called()

    def test_diagnostics_are_finite_and_never_reemit_private_loader_text(self):
        self.assertEqual('memory-indication', X._diagnostic(b'private: failed to map segment from shared object'))
        self.assertEqual('unclassified', X._diagnostic(b'/private secret-cookie'))

    def test_native_spawn_keeps_existing_limits_minimal_environment_and_no_fds(self):
        argv = ['/owned/adb-ppa-runtime-1/php', '-n']
        with patch.object(X.subprocess, 'Popen', return_value=object()) as spawn:
            X._NativeProcess().spawn(argv)
        args = spawn.call_args
        self.assertEqual((argv,), args.args)
        self.assertIs(X._candidate_limit, args.kwargs['preexec_fn'])
        self.assertTrue(args.kwargs['close_fds'])
        self.assertTrue(args.kwargs['start_new_session'])
        self.assertEqual((), args.kwargs['pass_fds'])
        self.assertEqual(dict(X.ENV), args.kwargs['env'])
        self.assertEqual({'PATH', 'LANG', 'LC_ALL', 'PHP_INI_SCAN_DIR'}, set(args.kwargs['env']))
        self.assertEqual('', args.kwargs['env']['PHP_INI_SCAN_DIR'])

    def test_recovery_identity_mismatch_precedes_signal(self):
        api = X._NativeProcess()
        process = SimpleNamespace(pid=123, args=['/fixed', '-n'])
        with (patch.object(api, 'identity', return_value=(123, b'100')),
              patch.object(X.os, 'getpgid', return_value=123, create=True),
              patch.object(X.os, 'killpg', create=True) as kill,
              patch.object(X.signal, 'SIGKILL', 9, create=True)):
            for identity, argv in (((123, b'101'), ('/fixed', '-n')), ((123, b'100'), ('/wrong', '-n'))):
                with self.assertRaisesRegex(S.Refusal, 'recovery-identity'):
                    api.kill(process, identity, argv)
            kill.assert_not_called()
            api.kill(process, (123, b'100'), ('/fixed', '-n'))
            kill.assert_called_once_with(123, 9)


class Resources:
    """In-memory resource API; never changes host/child limits."""
    RLIMIT_AS, RLIMIT_CPU, RLIMIT_CORE = 9, 0, 4

    def __init__(self):
        self.values = {9: (X.PARENT_SOFT, X.CANDIDATE_MEMORY), 0: (-1, -1), 4: (0, 0)}
        self.calls, self.ignore = [], False

    def getrlimit(self, key):
        return self.values[key]

    def setrlimit(self, key, value):
        self.calls.append((key, value))
        if not self.ignore:
            self.values[key] = value


class AddressAdmission(unittest.TestCase):
    def setUp(self):
        self.api = Resources()
        self.control = patch.dict(sys.modules, {'resource': self.api})
        self.control.start()
        self.addCleanup(self.control.stop)

    def archive(self):
        stack = patch.object(X.sys, 'platform', 'linux')
        stack.start()
        self.addCleanup(stack.stop)
        stack = patch.object(J.D.A.Path, 'is_file', return_value=True)
        stack.start()
        self.addCleanup(stack.stop)
        stack = patch.object(J.D.A.subprocess, 'run', return_value=SimpleNamespace(returncode=0, stdout=b'x'))
        probe = stack.start()
        self.addCleanup(stack.stop)
        return probe

    def test_fixed_parent_profile_keeps_soft40_hard128_and_same_codec_probe(self):
        probe = self.archive()
        self.api.values[9] = (-1, -1)
        row = X._archive_preflight()
        self.assertEqual((40*1024*1024, 128*1024*1024), self.api.values[9])
        self.assertEqual(24*1024*1024, row['decoder_address_space'])
        self.assertEqual(X.ADDRESS_PROFILE, row['address_profile'])
        self.assertEqual(X.CANDIDATE_MEMORY, row['parent_address_space_hard'])
        self.assertIs(J.D.A.decoder_limit, probe.call_args.kwargs['preexec_fn'])
        self.assertEqual(list(J.D.A.DECODER_ARGS), probe.call_args.args[0])

    def test_parent_readback_failure_blocks_codec_start(self):
        probe = self.archive()
        self.api.values[9], self.api.ignore = (-1, -1), True
        with self.assertRaisesRegex(S.Refusal, 'parent-limits'):
            X._archive_preflight()
        probe.assert_not_called()

    def test_candidate_limit_requires_inherited_exact_profile_before_changes(self):
        for limits in ((X.PARENT_SOFT, X.PARENT_SOFT), (-1, -1), (X.CANDIDATE_MEMORY, X.CANDIDATE_MEMORY)):
            self.api.values[9] = limits
            with self.assertRaisesRegex(S.Refusal, 'inherited-limits'):
                X._candidate_limit()
            self.assertEqual([], self.api.calls)
        self.api.values[9] = (X.PARENT_SOFT, X.CANDIDATE_MEMORY)
        X._candidate_limit()
        self.assertEqual([(9, (X.CANDIDATE_MEMORY, X.CANDIDATE_MEMORY)), (0, (60, 60)), (4, (0, 0))], self.api.calls)

    def test_candidate_limit_readback_refuses_ineffective_set(self):
        self.api.ignore = True
        with self.assertRaisesRegex(S.Refusal, 'child-limits'):
            X._candidate_limit()

    def test_legacy_parent40_and_codec24_limits_are_unchanged(self):
        self.archive()
        J.D.A.preflight()
        self.assertEqual((40*1024*1024, 40*1024*1024), self.api.values[9])
        J.D.A.decoder_limit()
        self.assertEqual((24*1024*1024, 24*1024*1024), self.api.values[9])
        self.assertEqual((60, 60), self.api.values[0])
        self.assertEqual((0, 0), self.api.values[4])

    def test_fixed_override_restores_legacy_preflight_on_success_and_failure(self):
        original = J.D.A.preflight
        for failing in (False, True):
            def probe():
                self.assertIs(X._archive_preflight, J.D.A.preflight)
                if failing:
                    raise S.Refusal('synthetic-codec')
                return {'model': True}
            with patch.object(X.B, 'preflight', side_effect=probe):
                if failing:
                    with self.assertRaisesRegex(S.Refusal, 'synthetic-codec'):
                        X._preflight()
                else:
                    self.assertEqual({'model': True}, X._preflight())
            self.assertIs(original, J.D.A.preflight)

    def test_native_profile_rejects_wrong_parent_soft_hard_or_core_readback(self):
        with (patch.object(X.sys, 'platform', 'linux'),
              patch.object(X.os, 'getuid', return_value=1000, create=True),
              patch.object(X.os, 'geteuid', return_value=1000, create=True)):
            X._NativeProcess().profile()
            for key, value in ((9, (X.PARENT_SOFT, X.PARENT_SOFT)), (4, (-1, -1))):
                self.api.values[key] = value
                with self.assertRaisesRegex(S.Refusal, 'parent-limits'):
                    X._NativeProcess().profile()
                self.api.values = Resources().values

    def test_resource_guard_semantic_controls_are_assertion_killed(self):
        original = S.require
        controls = (
            ('candidate-parent-limits', 'test_parent_readback_failure_blocks_codec_start'),
            ('candidate-inherited-limits', 'test_candidate_limit_requires_inherited_exact_profile_before_changes'),
            ('candidate-child-limits', 'test_candidate_limit_readback_refuses_ineffective_set'),
        )
        for guard, name in controls:
            with self.subTest(guard=guard), patch.object(S, 'require', side_effect=lambda ok, why: None if why == guard else original(ok, why)):
                result = unittest.TextTestRunner(stream=io.StringIO()).run(AddressAdmission(name))
                self.assertGreater(len(result.failures), 0, guard)
                self.assertEqual([], result.errors, guard)


if __name__ == '__main__':
    output = io.StringIO()
    suite = unittest.TestSuite(unittest.defaultTestLoader.loadTestsFromTestCase(case) for case in (Launch, NativeShape, AddressAdmission))
    result = unittest.TextTestRunner(stream=output).run(suite)
    if not result.wasSuccessful():
        sys.stderr.write(output.getvalue()[:8000])
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'skips': len(result.skipped)}))
    sys.exit(not result.wasSuccessful())
