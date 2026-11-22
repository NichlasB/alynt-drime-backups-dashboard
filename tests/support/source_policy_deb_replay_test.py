"""Immutable synthetic DEB preparation; no acquisition or materialized members."""
import ast
import gzip
import hashlib
import importlib.util
import io
import json
import lzma
from pathlib import Path
import struct
import sys
import unittest
from unittest.mock import Mock, patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
import source_policy_deb_replay as R
D, E = R.D, R.E


def load(name, relative):
    spec = importlib.util.spec_from_file_location(name, ROOT/relative)
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


F = load('tar_fixture', 'tests/support/source_policy_php_archive_test.py')
P = load('deb_fixture', 'tests/support/source_policy_ppa_deb_test.py')
B = load('elf_fixture', 'tests/support/source_policy_elf_ranges_test.py')


def packets(suffix='', cli=None, mysql=None, common=None):
    cli = cli if cli is not None else P.tar(F.member('./usr/bin/php8.5', B.rich(pht=3500)), F.member('usr/share/doc/cli-note', b'baseline-text'))
    mysql = mysql if mysql is not None else P.tar(F.member('usr/lib/php/20250925/mysqlnd.so', B.rich()), F.member('usr/lib/php/20250925/mysqli.so', B.image()))
    common = P.tar() if common is None else common
    compress = {'.gz': gzip.compress, '.xz': lambda value: lzma.compress(value, preset=0), '': lambda value: value}[suffix]
    return tuple(P.ar(('debian-binary', b'2.0\n'),
                      ('control.tar'+suffix, compress(P.tar(F.member('control', P.identity(name))))),
                      ('data.tar'+suffix, compress(body)))
                 for (name, size, sha), body in zip(D.PINS, (cli, common, mysql)))


def fixture_pins(values):
    # Only this isolated test patches the frozen catalog; no production input
    # admits caller-selected pins or a fixture/success switch.
    return tuple((pin[0], len(value), hashlib.sha256(value).hexdigest())
                 for pin, value in zip(D.PINS, values))


def prepared(values):
    with patch.object(D, 'PINS', fixture_pins(values)):
        return R.prepare(values)


def legacy(values):
    budget = D.Budget()
    for pin, value in zip(D.PINS, values):
        source = io.BytesIO(value)
        for member, category in zip(D.ar_members(source, budget), ('control', 'data')):
            name, start, length = member
            D.tar_inventory(D.Slice(source, start, length, budget), budget, pin[0], category)
    return budget


class FakeDecoder:
    """Small synthetic gzip test double; NOT native/process/resource evidence."""
    instances = []

    def __init__(self, source, suffix, budget):
        self.budget, self.closed = budget, False
        body = bytearray()
        while source.remaining:
            body.extend(source.read(min(65536, source.remaining)))
        self.output = io.BytesIO(gzip.decompress(body))
        self.process = Mock(returncode=0)
        self.process.poll.return_value = 0
        self.instances.append(self)

    def read(self, count):
        value = self.output.read(count)
        self.budget.charge(len(value))
        return value

    def close(self):
        self.closed = True
        self.output.close()


class Checks(unittest.TestCase):
    def test_frozen_legacy_digest_work_and_shape(self):
        raw = P.tar(F.member('./', b'', b'5'), F.member('./usr/bin/php8.5'),
                    F.member('usr/share/doc/info', b'text'), F.member('alias', b'', b'2', b'usr/bin/php8.5'))
        budget = D.Budget()
        result = D.tar_inventory(P.stream(raw, budget), budget, 'php8.5-cli', 'data')
        self.assertEqual({'entries': 4}, result)
        self.assertEqual(4096, budget.total)
        self.assertEqual(4, budget.entries)
        self.assertEqual({'30': 2, '32': 1, '35': 1}, budget.types)
        self.assertEqual('15ba01c96c2fefea93608d4ea28623bb35f13292d281d6f0ad18ed64565b09af', budget.inventory.hexdigest())
        self.assertEqual('72e80b4b02aec91fc55d0d3a528aa8c2c1e55c000d32c908f3eb43deb0488732', budget.roles['cli']['sha256'])
        # Preserve the legacy cross-package guard ordering, even for a selected
        # link that would independently fail the regular-file check.
        with self.assertRaisesRegex(D.S.Refusal, 'package-path-collision'):
            D.tar_inventory(P.stream(P.tar(F.member('./usr/bin/php8.5', b'', b'2')), budget), budget, 'php8.5-cli', 'data')

    def test_nonvacuous_legacy_parity_and_primary_only_registration(self):
        values = packets()
        expected, actual = legacy(values), prepared(values)
        self.assertEqual(expected.roles, actual['roles'])
        self.assertEqual(expected.inventory.hexdigest(), actual['inventory_sha256'])
        self.assertEqual(expected.types, actual['types'])
        self.assertEqual(7, actual['primary_entries'])
        self.assertEqual(15, actual['scanned_entries'])
        self.assertEqual([3, 1, 3], [p['data_passes'] for p in actual['packages']])
        self.assertEqual(['a.so', 'bb.so', 'a.so'], actual['roles']['cli']['needed'])
        self.assertEqual(['/rpath', '/runpath'], actual['roles']['cli']['search_paths'])
        self.assertTrue(actual['prepared_only'])
        self.assertEqual('immutable-input-bytes-only', actual['source_profile'])
        for field in ('qualified', 'executed', 'extracted', 'static_inventory_complete', 'dependency_closure_verified'):
            self.assertIs(False, actual[field])

    def test_global_bytes_include_rehashes_and_each_replayed_stream(self):
        values = packets()
        actual = prepared(values)
        lengths = []
        for value in values:
            members = D.ar_members(io.BytesIO(value), D.Budget())
            lengths.append(members[1][2])
        expected = 2*sum(map(len, values))+2*(len(values[0])+lengths[0]+len(values[2])+lengths[2])
        self.assertEqual(expected, actual['work_bytes'])
        self.assertLess(actual['scratch_peak'], E.SCRATCH_LIMIT)

    def test_all_three_pins_before_any_nested_parser(self):
        values = packets()
        pins = fixture_pins(values)
        bad = pins[:2]+((pins[2][0], pins[2][1], '0'*64),)
        with patch.object(D, 'PINS', bad), patch.object(D, 'ar_members', wraps=D.ar_members) as parse:
            with self.assertRaisesRegex(D.S.Refusal, 'replay-source-identity'):
                R.prepare(values)
            parse.assert_not_called()
        with patch.object(D, 'PINS', pins), patch.object(D, 'ar_members', wraps=D.ar_members) as parse:
            with self.assertRaisesRegex(D.S.Refusal, 'replay-source-size'):
                R.prepare(values[:2]+(values[2][:-1],))
            parse.assert_not_called()

    def test_input_types_have_no_user_reader_pin_or_conversion_hook(self):
        class Hostile:
            def __len__(self):
                raise AssertionError('foreign len callback')
        class TupleSubclass(tuple):
            pass
        class BytesSubclass(bytes):
            pass
        for value in (None, Hostile(), (), [b'x']*3, TupleSubclass((b'x',)*3),
                      (b'x', b'x', Hostile()), (b'x', b'x', bytearray()), (b'x', b'x', BytesSubclass())):
            with self.assertRaisesRegex(D.S.Refusal, 'replay-input-shape'):
                R.prepare(value)
        source = R._MemorySource(b'abc')
        self.assertFalse(hasattr(source, 'write'))
        self.assertEqual(b'abc', source.read(65536))
        self.assertEqual(1, source.seek(-2, 2))
        self.assertEqual(b'bc', source.read(2))
        for count in (-1, True, 65537, '1'):
            with self.assertRaises(D.S.Refusal):
                source.read(count)
        for offset, whence in ((True, 0), (0, True), (-4, 2), (4, 0), (0, 3)):
            with self.assertRaises(D.S.Refusal):
                source.seek(offset, whence)

    def test_source_rechecks_do_not_receive_ready_credit(self):
        values, identity = packets(), R._identity
        observed = []
        def check(data, pin, budget):
            observed.append(pin[0])
            if len(observed) == 4:
                raise D.S.Refusal('replay-source-identity')
            return identity(data, pin, budget)
        with patch.object(R, '_identity', check), patch.object(R._Preparation, '_commit') as commit:
            with self.assertRaisesRegex(D.S.Refusal, 'replay-source-identity'):
                prepared(values)
            commit.assert_not_called()
        self.assertEqual(['php8.5-cli', 'php8.5-common', 'php8.5-mysql', 'php8.5-cli'], observed)

    def test_unselected_replay_mutation_is_not_hidden_by_matching_elf(self):
        scan = R._Preparation._scan
        def changed(owner, source, member, package, category, primary):
            before = source._data
            if not primary and package == 'php8.5-cli':
                self.assertIn(b'baseline-text', before)
                source._data = before.replace(b'baseline-text', b'changed--text', 1)
            try:
                return scan(owner, source, member, package, category, primary)
            finally:
                source._data = before
        with patch.object(R._Preparation, '_scan', changed):
            with self.assertRaisesRegex(D.S.Refusal, 'replay-stream-identity'):
                prepared(packets())

    def test_trailer_eof_cannot_be_replaced_by_last_member_event(self):
        walk = D.walk_tar
        def premature(*args):
            for event, value in walk(*args):
                yield event, value
                if event == 'end':
                    return
        with patch.object(D, 'walk_tar', premature):
            with self.assertRaisesRegex(D.S.Refusal, 'replay-stream-incomplete'):
                prepared(packets())

    def test_strict_tar_negatives_through_the_shared_walker(self):
        good = P.tar(F.member('./usr/bin/php8.5'))
        cases = (good[:-1], good[:-1024]+bytes(512)+b'X'+bytes(511),
                 good+b'not-padding', good[:512]+good[512:-1024]+b'X'+bytes(1023),
                 P.tar(F.member('./usr/bin/php8.5'), F.member('./usr/bin/php8.5')),
                 P.tar(F.member('./usr/bin/php8.5', b'', b'2', b'elsewhere')),
                 P.tar(F.member('../bad')), P.tar(F.member(kind=b'x')),
                 bytes([good[0]^1])+good[1:])
        for raw in cases:
            with self.subTest(raw_size=len(raw)), self.assertRaises(D.S.Refusal):
                prepared(packets(cli=raw))

    def test_cross_package_and_duplicate_role_guards(self):
        collision = P.tar(F.member('usr/share/doc/cli-note', b'conflict'))
        with self.assertRaisesRegex(D.S.Refusal, 'package-path-collision'):
            prepared(packets(common=collision))
        duplicate = P.tar(F.member('usr/lib/php/20250925/mysqlnd.so'), F.member('usr/lib/php/20240924/mysqlnd.so'))
        with self.assertRaisesRegex(D.S.Refusal, 'duplicate-role'):
            prepared(packets(mysql=duplicate))
        with self.assertRaisesRegex(D.S.Refusal, 'selected-roles-missing'):
            prepared(packets(mysql=P.tar()))

    def test_failures_close_only_owned_collector_reservations(self):
        budgets, original = [], E.Budget.__init__
        def create(budget):
            original(budget)
            budgets.append(budget)
        bad = P.tar(F.member('usr/lib/php/20250925/mysqlnd.so', b'bad'+bytes(61)))
        with patch.object(E.Budget, '__init__', create), patch.object(R._Preparation, '_commit') as commit:
            with self.assertRaisesRegex(D.S.Refusal, 'elf-header'):
                prepared(packets(mysql=bad))
            commit.assert_not_called()
        self.assertTrue(budgets)
        self.assertTrue(all(budget.usage[0] == 0 for budget in budgets))
        reserved, reserve = [], E.Budget._reserve
        def hold(budget, count):
            reserved.append(count)
            reserve(budget, count)
        with patch.object(E.Budget, '__init__', create), patch.object(E.Budget, '_reserve', hold), patch.object(R, 'bytearray', side_effect=MemoryError, create=True):
            with self.assertRaises(MemoryError):
                prepared(packets())
        self.assertTrue(reserved, 'Control allocation must reserve shared scratch before allocation')
        self.assertEqual(len(P.identity('php8.5-cli')), reserved[0])
        self.assertTrue(all(budget.usage[0] == 0 for budget in budgets))

    def test_work_entry_and_deadline_refusals_share_the_original_budget(self):
        with patch.object(D, 'LIMIT', 1), self.assertRaisesRegex(D.S.Refusal, 'work-budget'):
            prepared(packets())
        original = D.Budget.__init__
        def expired(budget):
            original(budget)
            budget.deadline = 0
        with patch.object(D.Budget, '__init__', expired), self.assertRaisesRegex(D.S.Refusal, 'deadline'):
            prepared(packets())
        scan = R._Preparation._scan
        def full(owner, *args):
            if args[-1] is False:
                owner._budget.entries = 8192
            return scan(owner, *args)
        with patch.object(R._Preparation, '_scan', full), self.assertRaisesRegex(D.S.Refusal, 'entry-budget'):
            prepared(packets())

    def test_decoder_close_and_zero_exit_are_required_before_commit(self):
        values = packets('.gz')
        FakeDecoder.instances = []
        with patch.object(R.C, 'Decoder', FakeDecoder):
            result = prepared(values)
        self.assertEqual(3, result['packages'][0]['data_passes'])
        self.assertEqual(10, len(FakeDecoder.instances))
        self.assertTrue(all(item.closed for item in FakeDecoder.instances))
        close = FakeDecoder.close
        def nonzero(decoder):
            close(decoder)
            decoder.process.returncode = 7
        with patch.object(R.C, 'Decoder', FakeDecoder), patch.object(FakeDecoder, 'close', nonzero):
            with self.assertRaisesRegex(D.S.Refusal, 'replay-decoder-release'):
                prepared(values)

    def test_decoder_and_collectors_release_after_parse_failure(self):
        FakeDecoder.instances = []
        with patch.object(R.C, 'Decoder', FakeDecoder):
            with self.assertRaises(D.S.Refusal):
                prepared(packets('.gz', cli=P.tar(F.member(kind=b'x'))))
        self.assertTrue(FakeDecoder.instances and all(item.closed for item in FakeDecoder.instances))
        # A close failure after the primary ELF scan leaves an unfinished real
        # collector. The outer owner must still release it and never commit.
        FakeDecoder.instances = []
        budgets, original, close = [], E.Budget.__init__, FakeDecoder.close
        def create(budget):
            original(budget)
            budgets.append(budget)
        def failed_close(decoder):
            close(decoder)
            if len(FakeDecoder.instances) == 2:
                raise D.S.Refusal('synthetic-close-refusal')
        with patch.object(E.Budget, '__init__', create), patch.object(R.C, 'Decoder', FakeDecoder), patch.object(FakeDecoder, 'close', failed_close), patch.object(R._Preparation, '_commit') as commit:
            with self.assertRaisesRegex(D.S.Refusal, 'synthetic-close-refusal'):
                prepared(packets('.gz'))
            commit.assert_not_called()
        self.assertTrue(budgets and all(budget.usage[0] == 0 for budget in budgets))
        self.assertTrue(all(item.closed for item in FakeDecoder.instances))

    def test_fourth_pass_is_not_an_unbounded_retry(self):
        collector = Mock(phase='metadata')
        with patch.object(R.E, 'Collector', return_value=collector):
            with self.assertRaisesRegex(D.S.Refusal, 'replay-pass-bound'):
                prepared(packets())
        self.assertEqual(3, collector.finish_scan.call_count)
        collector.close.assert_called_once_with()

    def test_final_output_bound_is_not_a_new_credit(self):
        body = B.rich(needed=(0,)*64, paths=(), table=b'"'*240+b'\0')
        cli = P.tar(F.member('./usr/bin/php8.5', body))
        mysql = P.tar(F.member('usr/lib/php/20250925/mysqlnd.so', body), F.member('usr/lib/php/20250925/mysqli.so', body))
        with self.assertRaisesRegex(D.S.Refusal, 'prepared-output-bound'):
            prepared(packets(cli=cli, mysql=mysql))

    def test_no_new_entry_acquisition_or_production_wiring(self):
        source = (ROOT/'scripts/ci/source_policy_deb_replay.py').read_text()
        calls = {node.func.id for node in ast.walk(ast.parse(source)) if isinstance(node, ast.Call) and isinstance(node.func, ast.Name)}
        self.assertFalse(calls & {'open', 'eval', 'exec', '__import__'})
        self.assertNotIn('__main__', source)
        manual = ast.parse((ROOT/'scripts/ci/source-policy-ppa-deb-static.py').read_text())
        dormant = [node for node in manual.body if isinstance(node, ast.FunctionDef)
                   and node.name in ('_descriptor_sources', '_descriptor_preparation', 'descriptor_main')]
        self.assertEqual(3, len(dormant))
        for node in dormant:
            manual.body.remove(node)
        self.assertNotIn('source_policy_deb_replay', ast.unparse(manual))
        self.assertNotIn('_descriptor_preparation', ast.unparse(manual))
        workflow = (ROOT/'.github/workflows/source-policy-ppa-deb-static.yml').read_text()
        self.assertEqual(1, workflow.count('scripts/ci/source_policy_deb_replay.py'))
        self.assertIn('--approved-descriptor-proof', workflow)
        self.assertIn('--cleanup-descriptor-proof', workflow)
        for name in ('alynt-drime-backups-dashboard.php', 'scripts/ci/source_policy_deb_data.py'):
            self.assertNotIn('source_policy_deb_replay', (ROOT/name).read_text())

    def test_semantic_controls_are_assertion_killed_not_setup_errors(self):
        require = D.S.require
        controls = (
            (R, '_same_transcript', lambda *args: None, 'test_unselected_replay_mutation_is_not_hidden_by_matching_elf'),
            (R, '_identity', lambda *args: {}, 'test_all_three_pins_before_any_nested_parser'),
            (D.S, 'require', lambda ok, reason: None if reason == 'replay-stream-incomplete' else require(ok, reason), 'test_trailer_eof_cannot_be_replaced_by_last_member_event'),
            (D.S, 'require', lambda ok, reason: None if reason == 'replay-decoder-release' else require(ok, reason), 'test_decoder_close_and_zero_exit_are_required_before_commit'),
            (D.S, 'require', lambda ok, reason: None if reason == 'selected-roles-missing' else require(ok, reason), 'test_cross_package_and_duplicate_role_guards'),
            (D.Budget, 'charge', lambda *args: None, 'test_work_entry_and_deadline_refusals_share_the_original_budget'),
            (D.Budget, 'entry', lambda *args: None, 'test_work_entry_and_deadline_refusals_share_the_original_budget'),
        )
        for owner, name, replacement, test in controls:
            with patch.object(owner, name, replacement):
                result = unittest.TestResult()
                Checks(test).run(result)
            self.assertGreater(len(result.failures), 0, test)
            self.assertEqual([], result.errors, test)


class LinuxChecks(unittest.TestCase):
    def test_existing_native_codecs_with_synthetic_bytes_and_owned_reaping(self):
        # Existing trusted gzip/xz/zstd only, not a candidate/runtime or fit proof.
        for suffix in ('.gz', '.xz'):
            result = prepared(packets(suffix))
            self.assertEqual([3, 1, 3], [row['data_passes'] for row in result['packages']])
            self.assertEqual(15, result['scanned_entries'])
            self.assertFalse(result['static_inventory_complete'])
        def zstd(value):
            return b'\x28\xb5\x2f\xfd\xa0'+struct.pack('<I', len(value))+struct.pack('<I', (len(value)<<3)|1)[:3]+value
        values = []
        for pin, plain in zip(D.PINS, packets()):
            source = io.BytesIO(plain)
            encoded = []
            for row in D.ar_members(source, D.Budget()):
                name, start, length = row
                encoded.append((name+'.zst', zstd(plain[start:start+length])))
            values.append(P.ar(('debian-binary', b'2.0\n'), *encoded))
        self.assertEqual(15, prepared(tuple(values))['scanned_entries'])


if __name__ == '__main__':
    suite = unittest.defaultTestLoader.loadTestsFromTestCase(Checks)
    if sys.platform == 'linux':
        suite.addTests(unittest.defaultTestLoader.loadTestsFromTestCase(LinuxChecks))
    result = unittest.TestResult()
    suite.run(result)
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures),
                      'errors': len(result.errors), 'skips': len(result.skipped), 'linux_codecs': sys.platform == 'linux'}))
    if not result.wasSuccessful():
        print(result.failures + result.errors, file=sys.stderr)
        raise SystemExit(1)
