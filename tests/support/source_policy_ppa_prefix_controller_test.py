"""Isolated manual-owner API/RAM tests. No real files, signals or native proof."""
import contextlib
import importlib.util
import io
import json
from pathlib import Path, PurePosixPath
import sys
from types import SimpleNamespace
import unittest
from unittest.mock import Mock, patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
sys.path.insert(0, str(ROOT/'tests/support'))
SPEC = importlib.util.spec_from_file_location('prefix_owner_under_test', ROOT/'scripts/ci/source-policy-ppa-prefix-proof.py')
Q = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(Q)
import source_policy_ppa_join_test as T
import source_policy_descriptor_controller_test as C
J, M, S = Q.J, Q.M, Q.S


class Root(C.Root):
    parent = PurePosixPath('/owned')


class Controller(unittest.TestCase):
    def setUp(self):
        self.stack, self.events = contextlib.ExitStack(), []
        self.addCleanup(self.stack.close)
        def bind(owner, name, **kwargs):
            return self.stack.enter_context(patch.object(owner, name, **kwargs))
        self.root, self.runtime = Root(), Root()
        bind(M, 'target', return_value=self.root)
        bind(Q, '_runtime', return_value=self.runtime)
        bind(Q.os, 'environ', new={'EXPECTED_SHA': 'a'*40, 'GITHUB_RUN_ID': '1'})
        bind(Q.signal, 'SIGALRM', new=14, create=True)
        bind(Q.signal, 'SIG_BLOCK', new=0, create=True)
        bind(Q.signal, 'signal')
        self.alarm = bind(Q.signal, 'alarm', create=True)
        bind(Q.signal, 'pthread_sigmask', return_value={1}, create=True)
        bind(Q.sys, 'argv', new=['fixed', '--approved-prefix-proof'])
        self.states = bind(M, '_fd_state', return_value={'stable': (1, 2, 3)})
        self.children = bind(M, '_children_empty')
        self.preflight = bind(M, 'preflight', return_value={'model': True})
        bind(J.N, '_PosixCalls', return_value=Mock())
        def parent(budget):
            self.events.append('parent')
            raise S.Refusal('lease-membership')
        self.parent = bind(M, '_descriptor_sources', side_effect=parent)
        def acquire(root, budget, identities):
            self.events.append('acquire')
            identities.extend({'package': row[0], 'bytes': row[1], 'sha256': row[2]} for row in J.D.PINS)
        self.acquire = bind(M, 'acquire', side_effect=acquire)
        def guards(root, budget):
            self.events.append('guards')
            return ['real-guard-contract-modeled']
        self.guards = bind(M, '_descriptor_checks', side_effect=guards)
        self.context = SimpleNamespace(_storage=SimpleNamespace(_sizes={row[3]: row[4] for row in J.P._CATALOG}),
                                       cleanup_deadline=lambda: Q.time.monotonic()+30)
        def joined():
            self.events.append('join')
            return {'payloads': 3}
        self.entry = SimpleNamespace(_context=self.context, run=Mock(side_effect=joined))
        self.factory = bind(Q, '_FixedEntry', return_value=self.entry)
        bind(M, '_fixture_cleanup', side_effect=lambda root: self.events.append('fixture-cleanup'))
        def cleanup(root):
            self.events.append('source-cleanup')
            root.present = False
        self.cleanup = bind(M, 'cleanup', side_effect=cleanup)

    def run_main(self):
        output = io.StringIO()
        with contextlib.redirect_stdout(output):
            try:
                status = Q.main()
            except S.Refusal as error:
                self.fail('A modeled controller must return its bounded receipt: '+str(error))
        return status, json.loads(output.getvalue())

    def test_positive_requires_fresh_guards_and_retirement(self):
        code, receipt = self.run_main()
        self.assertEqual(0, code)
        self.assertEqual(['parent', 'acquire', 'guards', 'join', 'fixture-cleanup', 'source-cleanup'], self.events)
        self.assertTrue(receipt['prefix_proof_complete'])
        self.assertTrue(receipt['materialized'])
        self.assertTrue(receipt['extracted'])
        self.assertFalse(receipt['qualified'])
        self.assertFalse(receipt['executed'])
        self.assertFalse(receipt['dependency_closure_verified'])
        self.assertFalse(receipt['production_wiring'])
        self.assertEqual(12672176, receipt['materialized_payload_bytes'])
        self.assertEqual(0, receipt['owned_runtime_remaining'])
        self.assertEqual(0, receipt['owned_descriptor_delta'])
        self.assertFalse(self.root.present)
        self.assertEqual(540, self.alarm.call_args_list[0].args[0])
        self.assertLessEqual(self.alarm.call_args_list[-2].args[0], 30)

    def test_parent_failure_prevents_acquisition(self):
        self.parent.side_effect = S.Refusal('lease-directory')
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertEqual('lease-directory', receipt['reason'])
        self.acquire.assert_not_called()
        self.factory.assert_not_called()
        self.assertFalse(self.root.present)

    def test_guard_failure_never_constructs_join(self):
        self.guards.side_effect = S.Refusal('descriptor-nofollow-error')
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertEqual('fresh-owned-source-guards', receipt['stage'])
        self.factory.assert_not_called()
        self.assertNotIn('output', receipt)
        self.assertFalse(self.root.present)

    def test_partial_write_failure_is_not_reported_unmaterialized(self):
        self.context._storage._sizes = {'php': 12}
        self.entry.run.side_effect = S.Refusal('shared-work-budget')
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertTrue(receipt['materialized'])
        self.assertEqual(12, receipt['materialized_payload_bytes'])
        self.assertFalse(receipt['prefix_proof_complete'])
        self.assertNotIn('output', receipt)

    def test_unknown_runtime_is_never_marker_deleted(self):
        def join():
            self.runtime.present = True
            raise S.Refusal('storage-cleanup-unknown')
        self.entry.run.side_effect = join
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertEqual('prefix-cleanup-unknown', receipt['reason'])
        self.assertEqual('Unknown', receipt['owned_runtime_remaining'])
        self.assertFalse(receipt['prefix_proof_complete'])
        self.assertTrue(self.runtime.present)
        self.cleanup.assert_not_called()

    def test_final_fd_mismatch_prevents_completion(self):
        self.states.side_effect = [{'stable': (1, 2, 3)}]*3+[{'changed': (1, 2, 4)}]
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertFalse(receipt['prefix_proof_complete'])
        self.assertEqual('Unknown', receipt['owned_descriptor_delta'])
        self.assertNotIn('output', receipt)

    def test_cleanup_cannot_revive_expired_work(self):
        def join():
            self.factory.call_args.args[0].deadline = 0
            return {'payloads': 3}
        self.entry.run.side_effect = join
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertFalse(receipt['prefix_proof_complete'])
        self.assertEqual('work-deadline', receipt['reason'])
        self.assertEqual(0, receipt['owned_runtime_remaining'])

    def test_no_payload_evidence_is_not_success(self):
        self.context._storage._sizes = {'owner.json': 1}
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertFalse(receipt['prefix_proof_complete'])
        self.assertFalse(receipt['materialized'])
        self.assertEqual('prefix-materialization-evidence', receipt['reason'])

    def test_existing_runtime_refuses_before_acquisition(self):
        self.runtime.present = True
        code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.acquire.assert_not_called()
        self.factory.assert_not_called()
        self.assertTrue(self.runtime.present)
        self.assertEqual('Unknown', receipt['owned_runtime_remaining'])

    def test_existing_cleanup_deadline_is_not_renewed(self):
        self.context.cleanup_deadline = lambda: 49
        with patch.object(Q.time, 'monotonic', return_value=50):
            code, receipt = self.run_main()
        self.assertEqual(1, code)
        self.assertEqual('prefix-cleanup-unknown', receipt['reason'])
        self.assertFalse(receipt['prefix_proof_complete'])
        self.cleanup.assert_not_called()
        self.assertEqual([540, 0], [call.args[0] for call in self.alarm.call_args_list])

    def test_reconcile_only_never_adopts_retained_runtime(self):
        self.runtime.present = True
        with patch.object(Q.sys, 'argv', ['fixed', '--reconcile-prefix-proof']):
            with self.assertRaisesRegex(S.Refusal, 'presence-unknown'):
                Q.main()
        self.cleanup.assert_not_called()
        self.factory.assert_not_called()

    def test_semantic_controls_are_assertion_killed(self):
        original = S.require
        controls = (
            ('always-unavailable', 'test_positive_requires_fresh_guards_and_retirement', patch.object(Q, 'main', side_effect=S.Refusal('semantic'))),
            ('runtime-absence', 'test_unknown_runtime_is_never_marker_deleted', patch.object(Q, '_absent', return_value=None)),
            ('final-fds', 'test_final_fd_mismatch_prevents_completion', patch.object(S, 'require', side_effect=lambda ok, why: None if why == 'prefix-final-fd-reconciliation' else original(ok, why))),
        )
        for label, name, control in controls:
            with self.subTest(label=label), control:
                result = unittest.TextTestRunner(stream=io.StringIO()).run(Controller(name))
                # An unavailable main is caught by run_main below as an assertion.
                self.assertGreater(len(result.failures), 0, label)
                self.assertEqual([], result.errors, label)


class Binding(unittest.TestCase):
    def test_job_is_rechecked_before_source_io_and_providers_restore_on_failure(self):
        previous = J._ENTRY, J.N._CALLS
        roots = [PurePosixPath('/owned/adb-ppa-deb-1'), PurePosixPath('/different/adb-ppa-deb-1')]
        with (patch.object(M, 'target', side_effect=roots),
              patch.dict(Q.os.environ, {'EXPECTED_SHA': 'a'*40, 'GITHUB_RUN_ID': '1'}),
              patch.object(J.N, '_PosixCalls', return_value=Mock()),
              patch.object(J._Context, 'sources') as sources):
            entry = Q._FixedEntry(J.D.Budget())
            with self.assertRaisesRegex(S.Refusal, 'job-changed'):
                entry.run()
        sources.assert_not_called()
        self.assertIs(previous[0], J._ENTRY)
        self.assertIs(previous[1], J.N._CALLS)

    def test_real_fixed_entry_over_ram_join_restores_disabled_providers(self):
        model = T.Join('test_positive_all_streams_close_before_seal_and_cleanup')
        model.setUp()
        self.addCleanup(model.doCleanups)
        previous = J._ENTRY, J.N._CALLS
        with (patch.object(M, 'target', return_value=PurePosixPath('/owned/adb-ppa-deb-1')),
             patch.dict(Q.os.environ, {'EXPECTED_SHA': 'a'*40, 'GITHUB_RUN_ID': '1'}),
             patch.object(J.N, '_PosixCalls', return_value=model.calls)):
            entry = Q._FixedEntry(model.budget)
            facts = entry.run()
        self.assertEqual(3, facts['payloads'])
        self.assertTrue(entry._context._output_sealed)
        self.assertIs(previous[0], J._ENTRY)
        self.assertIs(previous[1], J.N._CALLS)
        self.assertFalse(entry._context._storage._created)
        model.clean()

    def test_untrusted_job_refuses_before_any_resource(self):
        with patch.dict(Q.os.environ, {}, clear=True), patch.object(M, 'preflight') as preflight:
            with self.assertRaisesRegex(S.Refusal, 'exact-job'):
                Q.main()
        preflight.assert_not_called()


if __name__ == '__main__':
    output = io.StringIO()
    suite = unittest.TestSuite(unittest.defaultTestLoader.loadTestsFromTestCase(case) for case in (Controller, Binding))
    result = unittest.TextTestRunner(stream=output).run(suite)
    if not result.wasSuccessful():
        sys.stderr.write(output.getvalue()[:6000])
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'skips': len(result.skipped)}))
    sys.exit(not result.wasSuccessful())
