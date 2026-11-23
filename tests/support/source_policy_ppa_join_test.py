"""Isolated real adapter/join logic over RAM syscalls and immutable tar bytes."""
import hashlib
import ast
import gzip
import io
import json
from pathlib import Path
import sys
import unittest
from unittest.mock import patch, Mock

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
sys.path.insert(0, str(ROOT/'tests/support'))
import source_policy_ppa_join as J
import source_policy_deb_replay_test as T
import source_policy_deb_sources_fixture as F
import source_policy_ppa_storage_fixture as V

D, P, N, L = J.D, J.P, J.N, J.L
CATALOG = P._CATALOG


class Join(unittest.TestCase):
    def setUp(self):
        self.values = T.packets()
        self.pins = T.fixture_pins(self.values)
        self.bodies = (T.B.rich(pht=3500), T.B.rich(), T.B.image())
        self.catalog = tuple((*row[:4], len(body), hashlib.sha256(body).hexdigest()) for row, body in zip(CATALOG, self.bodies))
        self.budget, self.scratch, self.calls = D.Budget(), J.E.Budget(), V.Calls()
        marker = json.dumps({'head': 'a'*40, 'root': 'adb-ppa-deb-1'}, sort_keys=True).encode('ascii')
        names = tuple(pin[0]+'_'+D.REVISION+'_amd64.deb' for pin in self.pins)
        self.source = F.FakeAPI(self.values, names, marker)
        for item in (patch.object(D, 'PINS', self.pins), patch.object(P, '_CATALOG', self.catalog),
                     patch.object(L, '_API', self.source), patch.object(N, '_CALLS', self.calls)):
            item.start()
            self.addCleanup(item.stop)
        self.context = J._Context(J._KEY, '/owned', 'a'*40, '1', self.budget, self.scratch)

    def run_join(self):
        with patch.object(J._ENTRY, 'context', return_value=self.context):
            return J._run()

    def clean(self):
        self.assertEqual({}, self.source.live)
        self.assertEqual([], self.source.iterators)
        self.assertFalse(self.source.masked)
        self.assertEqual({}, self.calls.live)
        self.assertEqual([], self.calls.iterators)
        self.assertFalse(self.calls.masked)
        self.assertEqual({'/owned'}, set(self.calls.nodes))
        self.assertEqual({}, self.calls.data)
        self.assertEqual(0, self.scratch.usage[0])

    def test_positive_all_streams_close_before_seal_and_cleanup(self):
        original = P._OutputOwner.seal
        def seal(owner):
            self.assertEqual({}, self.source.live)
            self.assertEqual([], self.source.iterators)
            self.assertFalse(self.source.masked)
            return original(owner)
        with patch.object(P._OutputOwner, 'seal', seal):
            try:
                result = self.run_join()
            except D.S.Refusal as error:
                self.fail('A valid RAM join must complete: '+str(error))
        self.assertTrue(result['join_policy_complete'])
        for key in ('materialized', 'qualified', 'executed', 'native_enabled', 'source_joined', 'dependency_closure_verified'):
            self.assertFalse(result[key])
        self.assertEqual(2, self.context._source_count)
        self.assertIs(self.budget, self.context._storage._budget)
        self.assertIs(self.scratch, self.context._storage._scratch)
        self.assertGreater(self.budget.total, sum(map(len, self.bodies))*2)
        self.assertEqual(4, sum(row[0] == 'chmod' for row in self.calls.events))
        closed = [row[1] for row in self.calls.events if row[0] == 'close']
        self.assertLess(len(set(closed)), len(closed), 'Positive must exercise safe numeric FD reuse')
        self.clean()

    def test_default_entry_and_syscall_providers_are_closed(self):
        with self.assertRaisesRegex(D.S.Refusal, 'entry-disabled'):
            J._run()
        with patch.object(N, '_CALLS', N._ClosedCalls()), self.assertRaisesRegex(D.S.Refusal, 'syscalls-disabled'):
            N._Storage(self.budget, self.scratch).profile()
        self.assertEqual([], self.calls.events)
        self.assertEqual([], self.source.events)

    def test_summary_json_or_foreign_context_cannot_join(self):
        for value in ({'source_joined': True}, object()):
            with self.assertRaisesRegex(D.S.Refusal, 'summary-origin'):
                J._second_pass(self.context, value)
        summary = J._FirstPass(self.context).run()
        foreign = J._Context(J._KEY, '/owned', 'b'*40, '1', self.budget, self.scratch)
        with self.assertRaisesRegex(D.S.Refusal, 'summary-origin'):
            J._second_pass(foreign, summary)
        self.clean()

    def test_bad_first_source_refuses_before_output(self):
        self.source.bytes[self.source.names[-1]] = b'X'+self.values[-1][1:]
        with self.assertRaises(D.S.Refusal):
            self.run_join()
        self.assertFalse(any(row[0] == 'mkdir' for row in self.calls.events))
        self.clean()

    def test_failed_context_cannot_be_reentered(self):
        self.source.bytes[self.source.names[-1]] = b'X'+self.values[-1][1:]
        with self.assertRaises(D.S.Refusal):
            self.run_join()
        count = len(self.source.events)
        with self.assertRaisesRegex(D.S.Refusal, 'context-origin'):
            self.run_join()
        self.assertEqual(count, len(self.source.events))
        self.clean()

    def test_changed_second_source_refuses_before_output(self):
        summary = J._FirstPass(self.context).run()
        self.source.bytes[self.source.names[0]] = b'X'+self.values[0][1:]
        with self.assertRaises(D.S.Refusal):
            J._second_pass(self.context, summary)
        self.assertFalse(any(row[0] == 'mkdir' for row in self.calls.events))
        self.clean()

    def test_late_common_failure_never_seals(self):
        original = D.walk_tar
        def walker(reader, budget, package, category):
            yield from original(reader, budget, package, category)
            if self.context._source_count == 2 and package == 'php8.5-common' and category == 'data':
                raise D.S.Refusal('synthetic-late-common')
        with patch.object(D, 'walk_tar', side_effect=walker), patch.object(P._OutputOwner, 'seal', side_effect=AssertionError('seal reached')):
            with self.assertRaisesRegex(D.S.Refusal, 'late-common'):
                self.run_join()
        self.assertTrue(any(row[:2] == ('write', 'php') for row in self.calls.events))
        self.clean()

    def test_wrong_second_stream_transcript_refuses(self):
        summary = J._FirstPass(self.context).run()
        summary._streams = tuple((key, (0, '0'*64, 0, '0'*64)) for key, facts in summary._streams)
        with self.assertRaisesRegex(D.S.Refusal, 'stream-transcript'):
            J._second_pass(self.context, summary)
        self.clean()

    def test_per_stream_identity_checks_are_not_skipped(self):
        original, calls = L._SourceSet.identity, []
        def identity(owner, index):
            calls.append((self.context._source_count, index))
            return original(owner, index)
        with patch.object(L._SourceSet, 'identity', identity):
            self.run_join()
        self.assertEqual(18, sum(round == 2 for round, index in calls))
        self.clean()

    def test_backend_registration_precedes_unmask(self):
        def fault(op, *args):
            if op == 'restore' and self.context._storage is not None:
                storage = self.context._storage
                self.assertTrue(set(self.calls.live) <= {row[0] for row in storage._records if not row[1]})
        self.calls.fault = fault
        self.run_join()
        self.clean()

    def test_parent_mismatch_refuses_before_mkdir(self):
        self.calls.nodes['/owned'].st_ino = 999
        with self.assertRaises(D.S.Refusal):
            self.run_join()
        self.assertFalse(any(row[0] == 'mkdir' for row in self.calls.events))
        self.assertEqual({}, self.source.live)
        self.assertEqual({}, self.calls.live)

    def test_source_close_failure_cannot_reach_output_seal(self):
        original = L._SourceSet.close
        def close(owner, **kwargs):
            original(owner, **kwargs)
            if self.context._source_count == 2:
                raise D.S.Refusal('synthetic-source-close')
        with patch.object(L._SourceSet, 'close', close), patch.object(P._OutputOwner, 'seal', side_effect=AssertionError('seal reached')):
            with self.assertRaisesRegex(D.S.Refusal, 'source-close'):
                self.run_join()
        self.clean()

    def test_changed_context_and_original_budget_refuse(self):
        summary = J._FirstPass(self.context).run()
        self.context._head = 'b'*40
        with self.assertRaisesRegex(D.S.Refusal, 'context-changed'):
            J._second_pass(self.context, summary)
        self.context._head = 'a'*40
        self.budget.total = D.LIMIT-1
        with self.assertRaises(D.S.Refusal):
            J._second_pass(self.context, summary)
        self.assertGreater(self.budget.total, D.LIMIT)
        self.assertFalse(any(row[0] == 'mkdir' for row in self.calls.events))
        self.clean()

    def test_partial_claim_has_no_path_only_recovery(self):
        def fault(op, *args):
            if op == 'open' and args[0] == 'adb-ppa-runtime-1':
                raise OSError('synthetic-root-open')
        self.calls.fault = fault
        with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            self.run_join()
        self.assertIn('adb-ppa-runtime-1', self.calls.nodes)
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)
        self.assertFalse(any(row[0] == 'rmdir' for row in self.calls.events))

    def test_close_ambiguity_never_retries_and_still_closes_sources(self):
        failed = False
        def fault(op, *args):
            nonlocal failed
            if op == 'close' and not failed:
                failed = True
                raise OSError('synthetic-close')
        self.calls.fault = fault
        with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            self.run_join()
        closes = [row[1] for row in self.calls.events if row[0] == 'close']
        self.assertEqual(len(closes), len(set(closes)))
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)
        self.assertFalse(any(row[0] == 'unlink' for row in self.calls.events))

    def test_restore_failure_has_registered_fds_and_sticky_unknown(self):
        failed = False
        def fault(op, *args):
            nonlocal failed
            if op == 'restore' and self.calls.live and not failed:
                self.assertTrue(set(self.calls.live) <= {row[0] for row in self.context._storage._records})
                failed = True
                raise KeyboardInterrupt()
        self.calls.fault = fault
        with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            self.run_join()
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)
        self.assertFalse(self.calls.masked)

    def test_original_deadline_not_revived_by_cleanup(self):
        def fault(op, *args):
            if op == 'rmdir':
                self.budget.deadline = 0
        self.calls.fault = fault
        with self.assertRaisesRegex(D.S.Refusal, 'work-deadline'):
            self.run_join()
        self.clean()

    def test_false_cleanup_result_is_not_accepted(self):
        with patch.object(self.calls, 'unlink', return_value=None), self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
            self.run_join()
        self.assertEqual(4, len(self.calls.data))
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)

    def test_native_mode_rechecks_fd_metadata_after_policy_check(self):
        changed = False
        def fault(op, *args):
            nonlocal changed
            if op == 'open' and args[0] == 'owner.json' and args[2] is None and not changed:
                self.calls.nodes['owner.json'].st_nlink = 2
                changed = True
        self.calls.fault = fault
        with self.assertRaises(D.S.Refusal):
            self.run_join()
        self.assertFalse(any(row[:2] == ('chmod', 'owner.json') for row in self.calls.events))
        self.assertFalse(any(row[0] == 'read' for row in self.calls.events))
        self.assertFalse(any(row[0] == 'unlink' for row in self.calls.events))
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)

    def test_readback_corruption_denies_join(self):
        changed = False
        def fault(op, *args):
            nonlocal changed
            if op == 'read' and args == ('php',) and not changed:
                self.calls.data['php'][0] ^= 1
                changed = True
        self.calls.fault = fault
        with self.assertRaisesRegex(D.S.Refusal, 'readback-identity'):
            self.run_join()
        self.clean()

    def compressed(self, bad_exit=False):
        self.values = T.packets(suffix='.gz')
        D.PINS = self.pins = T.fixture_pins(self.values)
        self.budget, self.scratch = D.Budget(), J.E.Budget()
        self.context = J._Context(J._KEY, '/owned', 'a'*40, '1', self.budget, self.scratch)
        name = 'adb-ppa-deb-1'
        self.source = F.FakeAPI(self.values, tuple(pin[0]+'_'+D.REVISION+'_amd64.deb' for pin in self.pins), self.context.marker(name))
        L._API = self.source
        test, instances = self, []

        class Decoder:
            def __init__(self, raw, suffix, budget):
                test.assertEqual('.gz', suffix)
                compressed = bytearray()
                while raw.remaining:
                    compressed.extend(raw.read(min(65536, raw.remaining)))
                self.value, self.offset, self.closed, self.budget = gzip.decompress(compressed), 0, False, budget
                self.process = self
                self.returncode = None
                instances.append(self)

            def read(self, count):
                value = self.value[self.offset:self.offset+count]
                self.offset += len(value)
                self.budget.charge(len(value))
                return value

            def close(self, *, _deadline=None):
                test.assertIsNotNone(_deadline)
                self.closed = True
                self.returncode = 1 if bad_exit and test.context._source_count == 2 else 0

            def poll(self):
                return self.returncode

        return Decoder, instances

    def test_synthetic_codec_release_and_full_stream_positive(self):
        decoder, instances = self.compressed()
        with patch.object(J.R.C, 'Decoder', decoder):
            self.assertTrue(self.run_join()['join_policy_complete'])
        self.assertTrue(instances)
        self.assertTrue(all(instance.closed for instance in instances))
        self.clean()

    def test_synthetic_bad_decoder_exit_cannot_seal(self):
        decoder, instances = self.compressed(True)
        with patch.object(J.R.C, 'Decoder', decoder), patch.object(P._OutputOwner, 'seal', side_effect=AssertionError('seal reached')):
            with self.assertRaisesRegex(D.S.Refusal, 'decoder-release'):
                self.run_join()
        self.assertTrue(all(instance.closed for instance in instances))
        self.clean()

    def test_cleanup_clock_is_shared_and_not_reset(self):
        original, deadlines = L._SourceSet.close, []
        def close(owner, **kwargs):
            if self.context._source_count == 2:
                deadlines.append(kwargs.get('_deadline'))
            return original(owner, **kwargs)
        walker = D.walk_tar
        def fail(reader, budget, package, category):
            yield from walker(reader, budget, package, category)
            if self.context._source_count == 2 and package == 'php8.5-common' and category == 'data':
                self.source.now = self.calls.now = 7
                raise D.S.Refusal('synthetic-clock')
        with patch.object(L._SourceSet, 'close', close), patch.object(D, 'walk_tar', side_effect=fail):
            with self.assertRaisesRegex(D.S.Refusal, 'synthetic-clock'):
                self.run_join()
        self.assertEqual([37], deadlines)
        self.assertEqual(37, self.context._storage._cleanup_deadline)
        self.assertEqual(37, self.context.cleanup_deadline())
        self.clean()

    def test_unknown_decoder_release_preserves_namespace(self):
        decoder, instances = self.compressed()
        original = decoder.close
        def close(instance, **kwargs):
            original(instance, **kwargs)
            if self.context._source_count == 2:
                raise OSError('synthetic-unknown-child')
        with patch.object(J.R.C, 'Decoder', decoder), patch.object(decoder, 'close', close):
            with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
                self.run_join()
        self.assertTrue(self.context._decoder_unknown)
        self.assertFalse(any(row[0] in ('unlink', 'rmdir') for row in self.calls.events))
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)

    def test_bad_first_catalog_refuses_before_output(self):
        P._CATALOG = ((*self.catalog[0][:-1], '0'*64), *self.catalog[1:])
        self.context = J._Context(J._KEY, '/owned', 'a'*40, '1', self.budget, self.scratch)
        with self.assertRaisesRegex(D.S.Refusal, 'first-catalog'):
            self.run_join()
        self.assertFalse(any(row[0] == 'mkdir' for row in self.calls.events))
        self.clean()

    def test_close_ack_with_running_decoder_is_unknown(self):
        decoder, instances = self.compressed()
        original = decoder.close
        def close(instance, **kwargs):
            original(instance, **kwargs)
            if self.context._source_count == 2:
                instance.returncode = None
        with patch.object(J.R.C, 'Decoder', decoder), patch.object(decoder, 'close', close):
            with self.assertRaisesRegex(D.S.Refusal, 'cleanup-unknown'):
                self.run_join()
        self.assertTrue(self.context._decoder_unknown)
        self.assertFalse(any(row[0] in ('unlink', 'rmdir') for row in self.calls.events))
        self.assertEqual({}, self.calls.live)
        self.assertEqual({}, self.source.live)

    def test_decoder_wait_failure_still_retires_known_pipes(self):
        decoder = J.R.C.Decoder.__new__(J.R.C.Decoder)
        decoder.process = Mock()
        decoder.process.poll.return_value = None
        decoder.process.wait.side_effect = TimeoutError('synthetic')
        decoder.process.stdin.closed = decoder.process.stdout.closed = False
        decoder.selector = Mock()
        with patch.object(J.R.C.time, 'monotonic', return_value=40), self.assertRaises(TimeoutError):
            decoder.close(_deadline=30)
        decoder.process.wait.assert_called_once_with(timeout=0)
        decoder.process.stdin.close.assert_called_once()
        decoder.process.stdout.close.assert_called_once()
        decoder.selector.close.assert_called_once()

    def test_semantic_join_controls_are_assertion_killed(self):
        text = (ROOT/'scripts/ci/source_policy_ppa_join.py').read_text()
        tree = ast.parse(text)
        def mutate(name, before, after):
            source = ast.get_source_segment(text, next(node for node in tree.body if isinstance(node, ast.FunctionDef) and node.name == name))
            self.assertIn(before, source)
            scope = dict(vars(J))
            exec(compile(source.replace(before, after), '<trusted-join-semantic-control>', 'exec'), scope)
            return scope[name]
        stream = mutate('_stream', '    sources.identity(index)\n', '    context.check()\n')
        final = mutate('_second_pass', "D.S.require(sources.seal() == identities, 'join-final-source-seal')", 'context.check()')
        close = mutate('_second_pass', '            context.close_sources(sources)  # A close/restore failure MUST precede/deny seal.', '            pass  # semantic control')
        transcript = mutate('_stream', "raw.remaining == 0 and facts == expected", 'raw.remaining == 0')
        original = D.S.require
        controls = (
            ('always-unavailable', 'test_positive_all_streams_close_before_seal_and_cleanup', patch.object(J, '_run', side_effect=D.S.Refusal('semantic'))),
            ('per-stream-identities', 'test_per_stream_identity_checks_are_not_skipped', patch.object(J, '_stream', stream)),
            ('final-source-seal', 'test_per_stream_identity_checks_are_not_skipped', patch.object(J, '_second_pass', final)),
            ('source-close-order', 'test_positive_all_streams_close_before_seal_and_cleanup', patch.object(J, '_second_pass', close)),
            ('stream-transcript', 'test_wrong_second_stream_transcript_refuses', patch.object(J, '_stream', transcript)),
            ('first-catalog', 'test_bad_first_catalog_refuses_before_output', patch.object(D.S, 'require', side_effect=lambda ok, why: None if why == 'join-first-catalog' else original(ok, why))),
            ('native-pre-mode', 'test_native_mode_rechecks_fd_metadata_after_policy_check', patch.object(D.S, 'require', side_effect=lambda ok, why: None if why == 'storage-pre-mode' else original(ok, why))),
        )
        for label, name, control in controls:
            with self.subTest(label=label), control:
                result = unittest.TextTestRunner(stream=io.StringIO()).run(Join(name))
                self.assertGreater(len(result.failures), 0, label+' survived or only crashed')
                self.assertEqual([], result.errors, label+' died by error instead of assertion')


if __name__ == '__main__':
    output = io.StringIO()
    result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Join))
    if not result.wasSuccessful():
        sys.stderr.write(output.getvalue())
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'skips': len(result.skipped)}))
    raise SystemExit(not result.wasSuccessful())
