"""Tar-only literal Unicode and containment regressions; synthetic BytesIO only."""
import hashlib
import io
import json
from pathlib import Path
import tarfile
import time
import types
import unittest
from unittest.mock import patch
import zipfile

ROLE = 'usr/local/bin/php'
OTHER = 'usr/local/lib/php/extensions/no-debug-non-zts-20190902/mysqli.so'
KILLED_CONTROLS = 0


def suite(s, layer, elf_bytes, controls=True):
    class UnicodeTests(unittest.TestCase):
        def budget(self):
            return {'expanded': 0, 'entries': 0, 'deadline': time.monotonic() + 10}

        def scan(self, entries, roles=None, wanted=(ROLE,), budget=None, **profile):
            path, digest = layer(entries, **profile)
            roles = {} if roles is None else roles
            row = s.scan_layer(path, digest, wanted, roles, budget or self.budget())
            return row, roles

        def test_positive_roles_and_no_link_reads(self):
            try:
                extract = tarfile.TarFile.extractfile
                with patch.object(s, 'elf', wraps=s.elf) as inspect, patch.object(tarfile.TarFile, 'extractfile', autospec=True, side_effect=extract) as reads:
                    row, roles = self.scan([('usr/local', tarfile.DIRTYPE, b''),
                        ('share/\u0151', tarfile.SYMTYPE, b''), (ROLE, tarfile.REGTYPE, elf_bytes()),
                        (OTHER, tarfile.REGTYPE, elf_bytes())], wanted=(ROLE, OTHER), linkname='../../outside')
                self.assertEqual(2, inspect.call_count)
                self.assertEqual([ROLE, OTHER], [call.args[1].name for call in reads.call_args_list])
                self.assertEqual({ROLE, OTHER}, set(roles))
                self.assertTrue(row['diff_id_verified'])
                self.assertEqual(hashlib.sha256(elf_bytes()).hexdigest(), roles[ROLE]['sha256'])
                self.assertFalse(roles[ROLE]['executed'])
            except s.Refusal as error:
                self.fail('Genuine synthetic positive refused: ' + str(error))
            path, digest = layer([(ROLE, tarfile.REGTYPE, elf_bytes())])
            with self.assertRaises(s.Refusal):
                s.scan_layer(path, 'sha256:' + '0' * 64, (ROLE,), {}, self.budget())

        def test_utf8_bounds_categories_version_and_private_facts(self):
            self.assertTrue(hasattr(s, 'tar_name'), 'Tar-only policy must exist independently of ASCII core names.')
            for value in ('a' * 240, '\u0151' * 120, '\U0001f642' * 60, 'a/' * 31 + 'b', 'a/o\u030b', 'a/\u00a0'):
                try:
                    self.assertEqual(value, s.tar_name(value))
                except s.Refusal as error:
                    self.fail('Positive virtual name refused: ' + str(error))
            cases = [(None, 'type'), ('', 'empty'), ('a' * 241, 'length'),
                     ('\u0151' * 120 + 'a', 'utf8-byte-length'), ('\udcff', 'utf8'),
                     ('a/' * 32 + 'b', 'depth'), ('a/../b', 'segment'), ('a//b', 'segment'),
                     ('a/./b', 'segment'), ('/a', 'absolute'), ('a\\b', 'backslash')]
            cases += [('SECRET/' + c, 'unicode-category') for c in ('\0', '\x7f', '\x85', '\u200d', '\u202e', '\ue000', '\u0378', '\ufdd0', '\u2028', '\u2029')]
            for value, code in cases:
                with self.assertRaises(s.Refusal) as caught:
                    s.tar_name(value)
                self.assertEqual(code, caught.exception.facts['predicate'])
                self.assertNotIn('SECRET', json.dumps(caught.exception.facts))
                self.assertLess(len(json.dumps(caught.exception.facts)), 1024)
            with patch.object(s.unicodedata, 'unidata_version', 'not-the-owned-version'):
                with self.assertRaises(s.Refusal) as caught:
                    s.tar_name('safe')
                self.assertEqual('unicode-version', caught.exception.facts['predicate'])

        def test_decoder_profiles_and_invalid_sequences(self):
            for format in (tarfile.USTAR_FORMAT, tarfile.GNU_FORMAT, tarfile.PAX_FORMAT):
                try:
                    row, roles = self.scan([('share/\u0151', tarfile.SYMTYPE, b''), (ROLE, tarfile.REGTYPE, elf_bytes())], format=format)
                    self.assertTrue(row['diff_id_verified']); self.assertIn(ROLE, roles)
                except s.Refusal as error:
                    self.fail('Supported synthetic header refused: ' + str(error))
                with self.assertRaises(s.Refusal) as caught:
                    self.scan([('bad/\udcff', tarfile.REGTYPE, b'')], format=format)
                self.assertEqual('utf8', caught.exception.facts['predicate'])
            row, roles = self.scan([('header', tarfile.REGTYPE, b'')], wanted=(), pax_headers={'header': {'path': 'share/\u0151'}})
            self.assertEqual(1, row['entries']); self.assertEqual({}, roles)
            row, _ = self.scan([('share/' + 'a' * 101 + '\u0151', tarfile.REGTYPE, b'')], wanted=(), format=tarfile.GNU_FORMAT)
            self.assertEqual(1, row['entries'])
            # Explicit decoder parameters, not a locale-dependent accidental pass.
            path, digest = layer([])
            with patch.object(tarfile, 'open', wraps=tarfile.open) as opened:
                s.scan_layer(path, digest, (), {}, self.budget())
            self.assertEqual('utf-8', opened.call_args.kwargs.get('encoding'))
            self.assertEqual('surrogateescape', opened.call_args.kwargs.get('errors'))

        def test_effective_duplicates_and_literal_identity(self):
            names = ('a/\u0151', 'a/o\u030b', 'a/A', 'a/a', 'a/\uff0e\uff0e', 'a/\uff0fb', 'a/%2e%2e')
            try:
                row, _ = self.scan([(key, tarfile.REGTYPE, b'') for key in names], wanted=())
            except s.Refusal as error:
                self.fail('Literal identities refused: ' + str(error))
            self.assertEqual(len(names), row['entries'])
            for entries in ([('./a/\u0151', tarfile.REGTYPE, b''), ('a/\u0151', tarfile.REGTYPE, b'')],
                            [('a/\u0151/', tarfile.DIRTYPE, b''), ('a/\u0151', tarfile.DIRTYPE, b'')],
                            [('header', tarfile.REGTYPE, b''), ('a/\u0151', tarfile.REGTYPE, b'')]):
                with self.assertRaises(s.Refusal) as caught:
                    self.scan(entries, wanted=(), pax_headers={'header': {'path': 'a/\u0151'}})
                self.assertEqual('tar-duplicate', str(caught.exception))
            for key in ('././a/\u0151', 'a/../\u0151'):
                with self.assertRaises(s.Refusal):
                    self.scan([(key, tarfile.REGTYPE, b'')], wanted=())

        def test_critical_guard_order_and_no_early_reads(self):
            original = s.require
            for prefix in ([], [(OTHER, tarfile.REGTYPE, elf_bytes())]):
                for key, kind, failure in ((ROLE, tarfile.SYMTYPE, 'critical-linked-path'),
                        (ROLE, tarfile.LNKTYPE, 'critical-linked-path'),
                        ('usr/local', tarfile.SYMTYPE, 'critical-parent-not-directory'),
                        ('usr/local', tarfile.LNKTYPE, 'critical-parent-not-directory'),
                        ('usr/local', tarfile.REGTYPE, 'critical-parent-not-directory'),
                        (ROLE, tarfile.DIRTYPE, 'critical-not-regular')):
                    trace = []
                    def guard(ok, code):
                        trace.append((bool(ok), code)); original(ok, code)
                    extract = tarfile.TarFile.extractfile
                    with patch.object(s, 'require', side_effect=guard), patch.object(s, 'elf', wraps=s.elf) as inspected, patch.object(tarfile.TarFile, 'extractfile', autospec=True, side_effect=extract) as reads:
                        try:
                            self.scan(prefix + [(key, kind, b'')], wanted=(ROLE, OTHER))
                        except Exception as error:
                            self.assertIsInstance(error, s.Refusal)
                            self.assertEqual(failure, str(error))
                        else:
                            self.fail('Forbidden critical entry admitted')
                        self.assertIn((False, failure), trace)
                        self.assertEqual(len(prefix), reads.call_count)
                        self.assertEqual(len(prefix), inspected.call_count)
            row, roles = self.scan([('usr/local/bin/ph\u0440', tarfile.REGTYPE, elf_bytes())])
            self.assertTrue(row['diff_id_verified']); self.assertEqual({}, roles)

        def test_whiteout_identity_and_conservative_order(self):
            for key in ('usr/local/bin/.wh.php', 'usr/local/.wh.bin', 'usr/local/bin/.wh..wh..opq', '.wh..wh..opq'):
                _, roles = self.scan([(key, tarfile.REGTYPE, b'')], roles={ROLE: {'prior': True}})
                self.assertEqual({}, roles)
            for key in ('usr/local/bin/.wh.php-extra', 'usr/local/bin/\uff0ewh.php', 'usr/local/bin/.wh.ph\u0301p', 'usr/local/bin/.WH.php'):
                _, roles = self.scan([(key, tarfile.REGTYPE, b'')], roles={ROLE: {'prior': True}})
                self.assertEqual({ROLE: {'prior': True}}, roles)
            for key, kind, data in (('a/.wh..', tarfile.REGTYPE, b''), ('a/.wh...', tarfile.REGTYPE, b''),
                    ('.wh.', tarfile.REGTYPE, b''), ('a/.wh.b', tarfile.SYMTYPE, b''), ('a/.wh.b', tarfile.REGTYPE, b'x')):
                with self.assertRaises(s.Refusal):
                    self.scan([(key, kind, data)])
            regular = (ROLE, tarfile.REGTYPE, elf_bytes())
            marker = ('usr/local/bin/.wh.php', tarfile.REGTYPE, b'')
            _, roles = self.scan([regular, marker]); self.assertEqual({}, roles)
            _, roles = self.scan([marker, regular]); self.assertEqual({ROLE}, set(roles))
            # New explicit regular bytes in a later layer can restore evidence.
            _, roles = self.scan([regular], roles={}); self.assertIn(ROLE, roles)

        def test_metadata_and_scan_budgets_precede_names(self):
            for profile, budget in (({'pax_headers': {'share/\u0151': {str(i): 'x' for i in range(33)}}}, self.budget()),
                    ({'pax_headers': {'share/\u0151': {'x': 'a' * 4097}}}, self.budget()),
                    ({}, {'expanded': 0, 'entries': 65536, 'deadline': time.monotonic() + 10}),
                    ({}, {'expanded': 0, 'entries': 0, 'deadline': -1})):
                self.assertTrue(hasattr(s, 'tar_name'))
                with patch.object(s, 'tar_name', wraps=s.tar_name) as names:
                    with self.assertRaises(s.Refusal):
                        self.scan([('share/\u0151', tarfile.REGTYPE, b'')], budget=budget, **profile)
                    self.assertEqual(0, names.call_count)

        def test_core_consumers_stay_ascii(self):
            self.assertEqual('a', s.name('a'))
            self.assertEqual('4b825dc642cb6eb9a060e54bf8d69288fbee4904', s.tree_oid({}))
            for function, args in ((s.name, ('\u0151',)), (s.tree_oid, ({'\u0151': ('100644', 'a' * 40)},)),
                    (s.core_modes, ({'sha': s.CORE_TREE, 'truncated': False, 'tree': [{'path': '\u0151', 'type': 'blob', 'mode': '100644', 'sha': 'a' * 40}]},))):
                try:
                    function(*args)
                except Exception as error:
                    self.assertIsInstance(error, s.Refusal)
                    self.assertEqual('archive-path', str(error))
                else:
                    self.fail('Unicode leaked into an ASCII consumer')
            archive = io.BytesIO()
            with zipfile.ZipFile(archive, 'w') as zip:
                zip.writestr('WordPress-' + s.CORE_COMMIT + '/\u0151', b'')
            archive.seek(0)
            with self.assertRaises(s.Refusal) as caught:
                s.core_zip(archive, {}, {})
            self.assertEqual('archive-path', str(caught.exception))

        def test_semantic_controls(self):
            global KILLED_CONTROLS
            path = Path(s.__file__)
            original = path.read_bytes()
            source = original.decode('utf-8').replace('\r\n', '\n')
            mutations = [
                ('def tar_name(value):\n', "def tar_name(value):\n    raise PathRefusal(value, 'always')\n", 'test_positive_roles_and_no_link_reads'),
                ('def tar_name(value):\n', 'def tar_name(value):\n    return value\n', 'test_utf8_bounds_categories_version_and_private_facts'),
                ('not 0 < len(value) <= 240', 'False', 'test_utf8_bounds_categories_version_and_private_facts'),
                ('if len(encoded) > 240:', 'if False:', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("if any(unicodedata.category(c) in ('Cc', 'Cf', 'Cs', 'Co', 'Cn', 'Zl', 'Zp') for c in value):", 'if False:', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("unicodedata.unidata_version != '15.0.0'", 'False', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("\n    if '\\\\' in value:", '\n    if False:', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("\n    if value.startswith('/'):", '\n    if False:', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("if len(value.split('/')) > 32:", 'if False:', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("any(part in ('', '.', '..') for part in value.split('/'))", 'False', 'test_utf8_bounds_categories_version_and_private_facts'),
                ("require(key not in seen, 'tar-duplicate')", "require(True, 'tar-duplicate')", 'test_effective_duplicates_and_literal_identity'),
                ("require(not any(role.startswith(key + '/') for role in wanted) or member.isdir(), 'critical-parent-not-directory')", "require(True, 'critical-parent-not-directory')", 'test_critical_guard_order_and_no_early_reads'),
                ("require(not any(key == role or role.startswith(key + '/') for role in wanted), 'critical-linked-path')", "require(True, 'critical-linked-path')", 'test_critical_guard_order_and_no_early_reads'),
                ("require(member.isfile(), 'critical-not-regular')", "require(True, 'critical-not-regular')", 'test_critical_guard_order_and_no_early_reads'),
                ('target = tar_name(target)', 'target = target', 'test_whiteout_identity_and_conservative_order'),
                ('del roles[role]', 'pass', 'test_whiteout_identity_and_conservative_order'),
                ('key = tar_name(raw)', 'key = name(raw)', 'test_positive_roles_and_no_link_reads'),
                ('def name(value):\n', 'def name(value):\n    return tar_name(value)\n', 'test_core_consumers_stay_ascii'),
                ('def tar_name(value):\n', "def tar_name(value):\n    value = unicodedata.normalize('NFC', value)\n", 'test_effective_duplicates_and_literal_identity'),
                ('if key in wanted:', 'if False:', 'test_positive_roles_and_no_link_reads'),
                ('reader.sha.hexdigest() == expected', 'True', 'test_positive_roles_and_no_link_reads'),
                (", encoding='utf-8', errors='surrogateescape'", '', 'test_decoder_profiles_and_invalid_sequences'),
                ("value.encode('utf-8', 'strict')", "value.encode('utf-8', 'replace')", 'test_utf8_bounds_categories_version_and_private_facts'),
            ]
            for old, new, method in mutations:
                self.assertEqual(1, source.count(old), 'Control target must be unique: ' + old)
                image = types.ModuleType('synthetic_static_control')
                image.__file__ = str(path)
                exec(compile(source.replace(old, new, 1), str(path), 'exec'), vars(image))
                cases = [case for case in suite(image, layer, elf_bytes, controls=False) if case._testMethodName == method]
                self.assertEqual(1, len(cases))
                result = unittest.TextTestRunner(stream=io.StringIO()).run(unittest.TestSuite(cases))
                self.assertEqual([], result.errors, 'Control must die by an assertion, not a harness/import error: ' + old)
                self.assertGreater(len(result.failures), 0, 'Required semantic/configuration control survived: ' + old)
            self.assertEqual(hashlib.sha256(original).digest(), hashlib.sha256(path.read_bytes()).digest())
            KILLED_CONTROLS = len(mutations)

    cases = unittest.defaultTestLoader.loadTestsFromTestCase(UnicodeTests)
    return unittest.TestSuite(case for case in cases if controls or case._testMethodName != 'test_semantic_controls')
