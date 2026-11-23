"""Isolated output policy checks. No real descriptor, file or process creation."""
import ast
import hashlib
import io
import json
from pathlib import Path
import stat
import sys
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
sys.path.insert(0, str(ROOT/'tests/support'))
import source_policy_ppa_prefix as P
import source_policy_ppa_prefix_fixture as F

CATALOG = P._CATALOG
PAYLOADS = (b'cli-fixture', b'mysqlnd-fixture', b'mysqli-fixture')


class Prefix(unittest.TestCase):
    def setUp(self):
        self.storage, self.budget, self.scratch = F.Storage(), P.D.Budget(), P.E.Budget()
        self.catalog = tuple((*row[:4], len(data), hashlib.sha256(data).hexdigest()) for row, data in zip(CATALOG, PAYLOADS))
        self.marker = json.dumps({'head': 'a'*40, 'root': 'adb-ppa-runtime-1'}).encode('ascii')
        for item in (patch.object(P, '_STORAGE', self.storage), patch.object(P, '_CATALOG', self.catalog)):
            item.start()
            self.addCleanup(item.stop)

    def owner(self, **changes):
        args = dict(parent='/owned', basename='adb-ppa-runtime-1', marker=self.marker, budget=self.budget, scratch=self.scratch)
        args.update(changes)
        return P._OutputOwner(**args)

    def populate(self, owner):
        for row, data in zip(self.catalog, PAYLOADS):
            owner.begin(row[0], row[1], row[2], b'0', row[4])
            owner.feed(data[:2])
            owner.feed(data[2:])
            owner.finish()

    def clean(self, owner):
        owner.cleanup()
        self.assertEqual({}, self.storage.live)
        self.assertEqual({}, self.storage.data)
        self.assertIsNone(self.storage.root)
        self.assertEqual(0, self.scratch.usage[0])

    def test_positive_and_exact_accounting_before_seal(self):
        owner = self.owner()
        self.populate(owner)
        self.assertEqual(len(self.marker)+sum(map(len, PAYLOADS)), self.budget.total)
        try:
            result = owner.seal()
        except P.D.S.Refusal:
            self.fail('Valid modeled output must seal; always-unavailable is not success')
        self.assertEqual({'prepared_only': True, 'output_policy_sealed': True, 'materialized': False,
                          'qualified': False, 'executed': False, 'dependency_closure_verified': False,
                          'source_joined': False, 'native_enabled': False}, result)
        self.assertEqual(2*(len(self.marker)+sum(map(len, PAYLOADS))), self.budget.total)
        self.assertEqual(5, self.budget.entries)
        self.assertEqual({}, self.storage.live)
        self.assertTrue(all(stat.S_IMODE(info.st_mode) == 0o400 for leaf, info in self.storage.infos.items() if leaf != 'root'))
        self.clean(owner)
        owner.cleanup()

    def test_default_native_boundary_has_no_resource_methods(self):
        storage = P._DisabledStorage()
        with patch.object(P, '_STORAGE', storage), self.assertRaisesRegex(P.D.S.Refusal, 'native-disabled'):
            self.owner()
        self.assertEqual({'profile'}, {name for name in vars(type(storage)) if not name.startswith('_')})
        self.assertFalse(any(row[0] == 'claim' for row in self.storage.events))

    def test_frozen_real_catalog(self):
        self.assertEqual((12282976, 218952, 170248), tuple(row[4] for row in CATALOG))
        self.assertEqual(('php', 'mysqlnd.so', 'mysqli.so'), tuple(row[3] for row in CATALOG))
        self.assertEqual((('cli', 'php8.5-cli', 'usr/bin/php8.5'),
                          ('mysqlnd', 'php8.5-mysql', 'usr/lib/php/20250925/mysqlnd.so'),
                          ('mysqli', 'php8.5-mysql', 'usr/lib/php/20250925/mysqli.so')), tuple(row[:3] for row in CATALOG))
        self.assertEqual('eb85560c0bea6c5d1c06a93a602697efe94ad9803da400211469529f041929fa', CATALOG[0][5])
        self.assertEqual('837b346b8f11c34669bca7874bfc027c866ebe7ae1d954c2e496ce83341d0b20', CATALOG[1][5])
        self.assertEqual('6dfd427a916d649b7404d4f9c9aea94caaa1821d08c7c7c8852606d42c35fcd5', CATALOG[2][5])

    def test_root_marker_and_budget_reject_before_claim(self):
        for changes in ({'parent': '/'}, {'parent': '/owned/../foreign'}, {'basename': '../php'},
                        {'marker': b'{}'}, {'marker': b'x'*257}, {'marker': self.marker.replace(b'a'*40, b'../evil')},
                        {'marker': b' '+self.marker}, {'marker': self.marker[:-1]+b', "root": "adb-ppa-runtime-1"}'},
                        {'budget': object()}, {'scratch': object()}):
            with self.subTest(changes=changes), self.assertRaises(P.D.S.Refusal):
                self.owner(**changes)
        self.assertEqual([], self.storage.events)

    def test_wrong_role_package_path_kind_size(self):
        for field, value in ((0, 'pdo_mysql'), (1, 'foreign'), (2, '../php'), (3, b'2'), (3, b'1'), (3, b'5'), (3, bytearray(b'0')), (4, True), (4, 1)):
            with self.subTest(field=field, value=value):
                owner = self.owner()
                args = [self.catalog[0][0], self.catalog[0][1], self.catalog[0][2], b'0', self.catalog[0][4]]
                args[field] = value
                with self.assertRaisesRegex(P.D.S.Refusal, 'selected-member'):
                    owner.begin(*args)
                with self.assertRaisesRegex(P.D.S.Refusal, 'unavailable'):
                    owner.seal()
                self.clean(owner)

    def test_duplicate_and_incomplete(self):
        owner = self.owner()
        with self.assertRaisesRegex(P.D.S.Refusal, 'incomplete'):
            owner.seal()
        self.clean(owner)
        owner = self.owner()
        self.populate(owner)
        row = self.catalog[0]
        with self.assertRaisesRegex(P.D.S.Refusal, 'duplicate'):
            owner.begin(row[0], row[1], row[2], b'0', row[4])
        self.clean(owner)

    def test_wrong_hash_never_seals(self):
        owner, row = self.owner(), self.catalog[0]
        owner.begin(row[0], row[1], row[2], b'0', row[4])
        owner.feed(b'X'*row[4])
        with self.assertRaisesRegex(P.D.S.Refusal, 'identity'):
            owner.finish()
        with self.assertRaisesRegex(P.D.S.Refusal, 'unavailable'):
            owner.seal()
        self.clean(owner)

    def test_no_feed_without_start_or_oversized_chunks(self):
        for chunk in (b'', b'x'*65537, bytearray(b'x')):
            owner = self.owner()
            with self.assertRaisesRegex(P.D.S.Refusal, 'chunk'):
                owner.feed(chunk)
            self.clean(owner)
        owner, row = self.owner(), self.catalog[0]
        owner.begin(row[0], row[1], row[2], b'0', row[4])
        with self.assertRaisesRegex(P.D.S.Refusal, 'member-size'):
            owner.feed(b'X'*(row[4]+1))
        self.clean(owner)

    def test_zero_boolean_or_oversized_write_progress(self):
        for count in (0, True, 1000):
            owner = self.owner()
            with patch.object(self.storage, 'write', return_value=count):
                row = self.catalog[0]
                owner.begin(row[0], row[1], row[2], b'0', row[4])
                with self.assertRaisesRegex(P.D.S.Refusal, 'write-progress'):
                    owner.feed(PAYLOADS[0])
            self.clean(owner)

    def test_write_fault_taints_but_owned_cleanup_is_available(self):
        owner, row = self.owner(), self.catalog[0]
        owner.begin(row[0], row[1], row[2], b'0', row[4])
        with patch.object(self.storage, 'write', side_effect=OSError('synthetic')):
            with self.assertRaises(OSError):
                owner.feed(PAYLOADS[0])
        with self.assertRaisesRegex(P.D.S.Refusal, 'unavailable'):
            owner.finish()
        self.clean(owner)

    def test_member_metadata_guards(self):
        for field, value in (('st_mode', stat.S_IFLNK | 0o600), ('st_mode', stat.S_IFREG | 0o700),
                             ('st_nlink', 2), ('st_uid', 0), ('st_size', 999)):
            owner, row = self.owner(), self.catalog[0]
            owner.begin(row[0], row[1], row[2], b'0', row[4])
            owner.feed(PAYLOADS[0])
            info = self.storage.infos['php']
            original = getattr(info, field)
            setattr(info, field, value)
            with self.assertRaisesRegex(P.D.S.Refusal, 'file-binding'):
                owner.finish()
            setattr(info, field, original)
            self.clean(owner)

    def test_foreign_membership_blocks_seal_and_cleanup(self):
        owner = self.owner()
        self.populate(owner)
        self.storage.data['foreign'] = bytearray(b'untouched')
        with self.assertRaisesRegex(P.D.S.Refusal, 'membership'):
            owner.seal()
        self.assertFalse(any(row[0] in ('mode', 'readonly', 'read') for row in self.storage.events))
        with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
            owner.cleanup()
        self.assertEqual(b'untouched', self.storage.data['foreign'])
        self.assertFalse(any(row[0] == 'remove' for row in self.storage.events))

    def test_readback_corruption(self):
        owner = self.owner()
        self.populate(owner)
        self.storage.data['php'][0] ^= 1
        with self.assertRaisesRegex(P.D.S.Refusal, 'readback-identity'):
            owner.seal()
        self.clean(owner)

    def test_physical_eof_probe_is_counted(self):
        owner = self.owner()
        self.populate(owner)
        original, before = self.storage.read, self.budget.total
        def growth(handle, count):
            value = original(handle, count)
            return value if value else b'X'
        with patch.object(self.storage, 'read', side_effect=growth), self.assertRaisesRegex(P.D.S.Refusal, 'readback-identity'):
            owner.seal()
        self.assertEqual(before+len(self.marker)+1, self.budget.total)
        self.clean(owner)

    def test_original_budget_and_closed_owner_not_replaced(self):
        self.budget.total, self.budget.entries = 107599159, 272
        owner = self.owner()
        self.assertIs(self.budget, owner._budget)
        self.assertIs(self.scratch, owner._scratch)
        self.populate(owner)
        owner.seal()
        self.assertEqual(107599159+2*(len(self.marker)+sum(map(len, PAYLOADS))), self.budget.total)
        self.assertEqual(277, self.budget.entries)
        self.clean(owner)
        with self.assertRaisesRegex(P.D.S.Refusal, 'unavailable'):
            owner.seal()

    def test_replaced_or_foreign_root_does_not_delete_any_leaf(self):
        for field, value in (('st_ino', 999), ('st_uid', 0), ('st_mode', stat.S_IFDIR | 0o777)):
            self.storage, self.budget = F.Storage(), P.D.Budget()
            P._STORAGE = self.storage
            owner = self.owner()
            self.populate(owner)
            setattr(self.storage.infos['root'], field, value)
            with self.assertRaisesRegex(P.D.S.Refusal, 'root-binding'):
                owner.seal()
            with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
                owner.cleanup()
            self.assertFalse(any(row[0] == 'remove' for row in self.storage.events))

    def test_failed_cleanup_remains_unknown(self):
        owner = self.owner()
        self.populate(owner)
        with patch.object(self.storage, 'remove', side_effect=OSError('synthetic')), self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
            owner.cleanup()
        with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
            owner.cleanup()
        self.assertEqual(4, len(self.storage.data))
        for method, result in (('remove', None), ('remove', False), ('remove_root', None), ('absent', False)):
            self.storage, self.budget = F.Storage(), P.D.Budget()
            P._STORAGE = self.storage
            owner = self.owner()
            self.populate(owner)
            with patch.object(self.storage, method, return_value=result), self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
                owner.cleanup()
            with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
                owner.cleanup()

    def test_preexisting_root_and_interrupted_claim_are_not_taken_over(self):
        self.storage.claim('/owned', 'adb-ppa-runtime-1', 0o700, exclusive=True, nofollow=True)
        with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
            self.owner()
        self.assertEqual(0, self.storage.root)
        self.assertFalse(any(row[0] in ('create', 'remove-root') for row in self.storage.events))
        self.storage = F.Storage()
        P._STORAGE = self.storage
        with patch.object(self.storage, 'claim', side_effect=KeyboardInterrupt()), self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
            self.owner()
        self.assertIsNone(self.storage.root)

    def test_readback_short_or_wrong_access(self):
        for method, result in (('read', b''), ('access', 'write')):
            owner = self.owner()
            self.populate(owner)
            with patch.object(self.storage, method, return_value=result), self.assertRaises(P.D.S.Refusal):
                owner.seal()
            self.clean(owner)

    def test_readback_metadata_change(self):
        owner = self.owner()
        self.populate(owner)
        def race(op, *args):
            if op == 'read' and args == ('php',):
                self.storage.infos['php'].st_ino += 1
        self.storage.fault = race
        with self.assertRaisesRegex(P.D.S.Refusal, 'file-binding'):
            owner.seal()
        self.storage.fault = None
        with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
            owner.cleanup()
        self.assertFalse(any(row[0] == 'remove' for row in self.storage.events))

    def test_close_ambiguity_is_sticky_and_not_retried(self):
        owner = self.owner()
        self.populate(owner)
        def fault(op, *args):
            if op == 'close':
                raise OSError('ambiguous')
        self.storage.fault = fault
        with self.assertRaises(OSError):
            owner.seal()
        closes = len([row for row in self.storage.events if row[0] == 'close'])
        self.storage.fault = None
        for _ in range(2):
            with self.assertRaisesRegex(P.D.S.Refusal, 'cleanup-unknown'):
                owner.cleanup()
        self.assertEqual(closes, len([row for row in self.storage.events if row[0] == 'close']))

    def test_original_work_deadline_entries_and_scratch(self):
        for field, value in (('total', P.D.LIMIT), ('deadline', 0), ('entries', 8192)):
            budget = self.budget
            setattr(budget, field, value)
            with self.assertRaises(P.D.S.Refusal):
                self.owner()
            self.budget = P.D.Budget()
            self.storage = F.Storage()
            P._STORAGE = self.storage
        owner, row = self.owner(), self.catalog[0]
        owner.begin(row[0], row[1], row[2], b'0', row[4])
        self.scratch._retained = P.E.SCRATCH_LIMIT
        with self.assertRaises(P.E.Refusal):
            owner.feed(PAYLOADS[0])
        self.scratch._retained = 0
        self.clean(owner)

    def test_import_has_no_storage_or_execution_entry(self):
        tree = ast.parse((ROOT/'scripts/ci/source_policy_ppa_prefix.py').read_text())
        imports = [alias.name for node in tree.body if isinstance(node, ast.Import) for alias in node.names]
        self.assertEqual(['hashlib', 'json', 're', 'stat', 'source_policy_deb_data', 'source_policy_elf_ranges'], imports)
        self.assertFalse(any(isinstance(node, ast.If) for node in tree.body))

    def test_semantic_guard_controls_are_assertion_killed(self):
        original = P.D.S.require
        checks = (
            ('always-unavailable', 'test_positive_and_exact_accounting_before_seal', None),
            ('member-hash', 'test_wrong_hash_never_seals', 'prefix-member-identity'),
            ('file-binding', 'test_member_metadata_guards', 'prefix-file-binding'),
            ('namespace', 'test_foreign_membership_blocks_seal_and_cleanup', 'prefix-membership'),
        )
        for label, name, reason in checks:
            with self.subTest(label=label):
                if reason is None:
                    mutation = patch.object(P._OutputOwner, 'seal', side_effect=P.D.S.Refusal('semantic-control'))
                else:
                    mutation = patch.object(P.D.S, 'require', side_effect=lambda condition, code, target=reason:
                                            None if code == target else original(condition, code))
                with mutation:
                    result = unittest.TextTestRunner(stream=io.StringIO()).run(Prefix(name))
                self.assertGreater(len(result.failures), 0, label+' survived or only crashed')
                self.assertEqual([], result.errors, label+' failed by error instead of assertion')


if __name__ == '__main__':
    suite = unittest.defaultTestLoader.loadTestsFromTestCase(Prefix)
    result = unittest.TextTestRunner(stream=io.StringIO()).run(suite)
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'skips': len(result.skipped)}))
    if not result.wasSuccessful():
        for test, error in result.failures+result.errors:
            print(str(test)+'\n'+error, file=sys.stderr)
        raise SystemExit(1)
