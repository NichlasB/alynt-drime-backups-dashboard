"""Synthetic offline parser/transport/cleanup checks. No real network or artifacts."""
import gzip
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import socket
import ssl
import struct
import sys
import tarfile
import time
import unittest
from unittest.mock import patch
import urllib.error
import zipfile

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('acquire', ROOT / 'scripts/manual/source-policy-linux-minimum-acquire.py')
acquire = importlib.util.module_from_spec(spec)
with patch.object(socket, 'socket', side_effect=AssertionError('Network forbidden during import')), patch.object(socket, 'create_connection', side_effect=AssertionError('Network forbidden during import')):
    spec.loader.exec_module(acquire)
s = acquire.static
EXPECTED_PINS = json.loads(sys.stdin.read(65536))


def minimal_elf():
    data = bytearray(120)
    data[:7] = b'\x7fELF\x02\x01\x01'
    struct.pack_into('<HH', data, 16, 2, 62)
    struct.pack_into('<Q', data, 32, 64)
    struct.pack_into('<HH', data, 54, 56, 1)
    struct.pack_into('<IIQQQQQQ', data, 64, 1, 5, 0, 0, 0, len(data), len(data), 4096)
    return bytes(data)


def layer(entries):
    output = io.BytesIO()
    with tarfile.open(fileobj=output, mode='w') as archive:
        for path, kind, data in entries:
            info = tarfile.TarInfo(path)
            info.type = kind
            if kind in (tarfile.SYMTYPE, tarfile.LNKTYPE):
                info.linkname = '/outside'
            info.size = len(data) if kind == tarfile.REGTYPE else 0
            archive.addfile(info, io.BytesIO(data))
    raw = output.getvalue()
    return io.BytesIO(gzip.compress(raw)), 'sha256:' + hashlib.sha256(raw).hexdigest()


class Response(io.BytesIO):
    status = 200
    headers = {}


class StaticTests(unittest.TestCase):
    def refused(self, function, *args, **kwargs):
        with self.assertRaises(s.Refusal):
            function(*args, **kwargs)

    def budget(self):
        return {'expanded': 0, 'entries': 0, 'deadline': time.monotonic() + 10}

    def test_descriptor_and_json(self):
        self.assertEqual(('a' * 64, 1), s.descriptor({'digest': 'sha256:' + 'a' * 64, 'size': 1}, 1))
        for row in ({'digest': 'sha256:' + 'A' * 64, 'size': 1}, {'digest': 'sha256:' + 'a' * 64, 'size': True}, {'digest': 'sha256:' + 'a' * 64, 'size': 2}, {'digest': 'sha256:' + 'a' * 64, 'size': 1, 'urls': []}):
            self.refused(s.descriptor, row, 1)
        self.refused(s.decode, b'{"a":1,"a":2}')
        self.refused(s.decode, b' ' * (2 * s.MIB + 1))

    def test_paths_are_virtual_only_and_never_host_joins(self):
        self.assertEqual('var/lib/dpkg/lib:amd64.list', s.name('var/lib/dpkg/lib:amd64.list'))
        for value in ('../a', '/a', 'a/../b', 'a//b', 'a\\b', 'a\x00b', 'a/./b', 'a/' * 33 + 'b', 'a' * 241):
            self.refused(s.name, value)

    def test_path_refusal_diagnostics_are_bounded_private_and_preserve_guard(self):
        cases = [(None, 'type'), ('', 'empty'), ('a' * 241, 'length'), ('a/\u03b1', 'ascii'),
                 ('a\\b', 'backslash'), ('/a', 'absolute'), ('a/' * 32 + 'b', 'depth'),
                 ('a//b', 'segment'), ('a/./b', 'segment'), ('a/../b', 'segment'), ('a/', 'segment')]
        for value, reason in cases:
            with self.assertRaises(s.Refusal) as caught:
                s.name(value)
            self.assertTrue(hasattr(caught.exception, 'facts'), 'Path refusal must expose bounded predicate facts, not just a generic code.')
            self.assertEqual('archive-path', str(caught.exception))
            self.assertEqual(reason, caught.exception.facts['predicate'])
            self.assertLess(len(json.dumps(caught.exception.facts)), 1024)
        for value in ('a', 'a' * 240, 'a/' * 31 + 'b', 'var/lib/dpkg/lib:amd64.list'):
            self.assertEqual(value, s.name(value))
        for value in ('private/SECRET/../bad', 'SECRET' * 10000, 'surrogate/\udc80'):
            with self.assertRaises(s.Refusal) as caught:
                s.name(value)
            facts = caught.exception.facts
            encoded = value[:1024].encode('utf-8', 'surrogatepass')
            self.assertEqual(hashlib.sha256(encoded).hexdigest(), facts['sample_sha256'])
            self.assertEqual(min(len(value), 1024), facts['hashed_characters'])
            self.assertNotIn('SECRET', json.dumps(facts))
            self.assertLess(len(json.dumps(facts)), 1024)
        path, sha = layer([('./../escape', tarfile.REGTYPE, b'')])
        with self.assertRaises(s.Refusal) as caught:
            s.scan_layer(path, sha, (), {}, self.budget())
        facts = caught.exception.facts
        self.assertEqual('regular', facts['member_type'])
        self.assertEqual(1, facts['entry_index'])
        self.assertTrue(facts['leading_dot_slash_removed'])
        # Synthetic analogue of the observed facts, not the retained real name.
        path, sha = layer([('usr/share/locale/' + 'a' * 40 + '\u0151', tarfile.SYMTYPE, b'')])
        with self.assertRaises(s.Refusal) as caught:
            s.scan_layer(path, sha, (), {}, self.budget())
        facts = caught.exception.facts
        self.assertEqual(('ascii', 58, 4, 'symlink', 337),
                         (facts['predicate'], facts['characters'], facts['depth'], facts['member_type'],
                          facts['sample_first_non_printable_ordinal']))
        # Differential corpus uses the exact original boolean predicate.
        for value in [None, 3, [], *[v for v, _ in cases], *['a' * n for n in (0, 1, 239, 240, 241)]]:
            original = (isinstance(value, str) and 0 < len(value) <= 240
                        and all(32 <= ord(c) <= 126 for c in value) and '\\' not in value
                        and not value.startswith('/') and len(value.split('/')) <= 32
                        and all(p not in ('', '.', '..') for p in value.split('/')))
            try:
                s.name(value); accepted = True
            except s.Refusal:
                accepted = False
            self.assertEqual(bool(original), accepted)

    def test_path_facts_survive_refused_proof_reporting_and_owned_cleanup(self):
        error = s.PathRefusal('PRIVATE-SECRET/../file')
        with patch.object(acquire, 'Client') as client:
            client.return_value.get.side_effect = error
            report = acquire.proof()
        self.assertEqual('refused', report['status'])
        self.assertEqual('archive-path', report['failure'])
        self.assertEqual('segment', report['path_refusal']['predicate'])
        self.assertEqual('confirmed', report['cleanup'])
        self.assertEqual(0, report['removed_owned_files'])
        self.assertTrue(report['source_unchanged'])
        self.assertNotIn('PRIVATE-SECRET', json.dumps(report))

    def test_elf_bounds_and_no_runtime_claim(self):
        row = s.elf(minimal_elf())
        self.assertEqual('x86_64', row['machine'])
        self.assertFalse(row['executed'])
        self.assertFalse(row['dependency_closure_verified'])
        for offset, value in ((0, 0), (4, 1), (18, 3), (54, 0)):
            bad = bytearray(minimal_elf()); bad[offset] = value
            self.refused(s.elf, bytes(bad))
        bad = bytearray(minimal_elf()); struct.pack_into('<Q', bad, 96, 99999)
        self.refused(s.elf, bytes(bad))

    def test_complete_layer_hash_and_regular_role(self):
        path, sha = layer([('usr/local/bin/php', tarfile.REGTYPE, minimal_elf())])
        roles = {}; result = s.scan_layer(path, sha, ('usr/local/bin/php',), roles, self.budget())
        self.assertTrue(result['diff_id_verified'])
        self.assertEqual(120, roles['usr/local/bin/php']['bytes'])
        path.seek(0); self.refused(s.scan_layer, path, 'sha256:' + '0' * 64, (), {}, self.budget())

    def test_layer_traversal_links_duplicates_and_special_types_refuse(self):
        for entries in ([('../escape', tarfile.REGTYPE, b'')], [('usr/local', tarfile.SYMTYPE, b'')], [('usr/local/bin/php', tarfile.LNKTYPE, b'')], [('dev/device', tarfile.CHRTYPE, b'')], [('a', tarfile.REGTYPE, b''), ('a', tarfile.REGTYPE, b'')], [('usr/local', tarfile.REGTYPE, b'')]):
            path, sha = layer(entries)
            self.refused(s.scan_layer, path, sha, ('usr/local/bin/php',), {}, self.budget())

    def test_whiteouts_revoke_selected_roles(self):
        for key in ('usr/local/bin/.wh.php', 'usr/local/bin/.wh..wh..opq'):
            path, sha = layer([(key, tarfile.REGTYPE, b'')]); roles = {'usr/local/bin/php': {}}
            s.scan_layer(path, sha, ('usr/local/bin/php',), roles, self.budget())
            self.assertEqual({}, roles)

    def test_reader_rejects_oversized_reads_and_expired_budget(self):
        reader = s.ScanReader(io.BytesIO(b'x'), self.budget())
        self.refused(reader.read, s.MIB + 1)
        reader.budget['deadline'] = 0; self.refused(reader.read, 1)
        reader = s.ScanReader(io.BytesIO(b'x'), self.budget()); reader.size = 1024 * s.MIB
        self.refused(reader.read, 1)

    def test_tree_algorithm_and_existing_pin_source(self):
        self.assertEqual('4b825dc642cb6eb9a060e54bf8d69288fbee4904', s.tree_oid({}))
        self.assertEqual(EXPECTED_PINS, acquire.source_pins())
        self.assertEqual(11, len(EXPECTED_PINS))
        self.refused(s.tree_oid, {'x': ('120000', 'a' * 40)})
        self.refused(s.core_modes, {'sha': s.CORE_TREE, 'truncated': True, 'tree': []})

    def test_synthetic_core_zip_complete_bytes_modes_and_pins(self):
        payload = b'owned synthetic source\n'
        blob = hashlib.sha1(b'blob ' + str(len(payload)).encode() + b'\0' + payload).hexdigest()
        modes = {'a.php': ('100644', blob)}; expected_tree = s.tree_oid(modes)
        output = io.BytesIO()
        with zipfile.ZipFile(output, 'w') as archive:
            archive.writestr('WordPress-' + s.CORE_COMMIT + '/a.php', payload)
        with patch.object(s, 'CORE_TREE', expected_tree):
            result = s.core_zip(output, modes, {'a.php': hashlib.sha256(payload).hexdigest()})
            self.assertEqual(1, result['files']); self.assertFalse(result['executed'])
            output.seek(0); self.refused(s.core_zip, output, modes, {'a.php': '0' * 64})
            output.seek(0); self.refused(s.core_zip, output, {'other.php': ('100644', blob)}, {})

    def test_redirects_are_one_named_https_cdn_without_userinfo(self):
        self.assertTrue(acquire.redirect_allowed('https://production.cloudfront.docker.com/fixed?temporary=redacted'))
        for url in ('http://production.cloudfront.docker.com/a', 'https://attacker.invalid/a', 'https://u:p@production.cloudfront.docker.com/a', 'https://production.cloudfront.docker.com:444/a', 'https://production.cloudfront.docker.com/a#fragment'):
            self.assertFalse(acquire.redirect_allowed(url))

    def test_exact_owned_cleanup_no_overwrite_and_unknown_membership(self):
        owner = acquire.Owned()
        try:
            path = owner.write('owned.json', b'{}')
            self.refused(owner.path, 'owned.json')
            self.refused(owner.path, '../outside.blob')
            extra = owner.root / 'unexpected.json'; extra.write_bytes(b'owned test-only foreign simulation')
            self.refused(owner.cleanup); self.assertTrue(path.exists())
            extra.unlink()  # Exact test-created path only; never recursive cleanup.
            collision = owner.root / 'collision.json'; collision.write_bytes(b'owned collision simulation')
            with self.assertRaises(FileExistsError):
                owner.write('collision.json', b'must not overwrite')
            self.assertEqual(b'owned collision simulation', collision.read_bytes())
            self.refused(owner.cleanup); collision.unlink()
        finally:
            owner.cleanup()
        self.assertFalse(owner.root.exists())

    def test_download_bounds_digest_and_redirect_auth_stripping(self):
        owner = acquire.Owned()
        try:
            with patch.dict(acquire.os.environ, {'SSLKEYLOGFILE': '', 'SSL_CERT_FILE': '', 'SSL_CERT_DIR': ''}):
                client = acquire.Client(owner, time.monotonic() + 20)
            with patch.dict(acquire.os.environ, {'SSLKEYLOGFILE': 'never-created-test-keylog'}):
                self.refused(acquire.Client, owner, time.monotonic() + 20)
            calls = []
            def fake(request, timeout):
                calls.append(request)
                if len(calls) == 1:
                    raise urllib.error.HTTPError(request.full_url, 307, 'redirect', {'Location': 'https://production.cloudfront.docker.com/fixed'}, None)
                return Response(b'fixed')
            client.opener.open = fake
            result = client.get('https://registry-1.docker.io/fixed', 10, token='synthetic-token', blob=True, expected_hash=hashlib.sha256(b'fixed').hexdigest())
            self.assertEqual(b'fixed', result)
            self.assertIsNotNone(calls[0].get_header('Authorization')); self.assertIsNone(calls[1].get_header('Authorization'))
            client.opener.open = lambda *args, **kwargs: Response(b'oversized')
            self.refused(client.get, 'https://registry-1.docker.io/fixed', 1)
            self.refused(client.get, 'https://registry-1.docker.io/fixed', 20, expected_hash='0' * 64)
            client.deadline = 0; self.refused(client.get, 'https://registry-1.docker.io/fixed', 20)
        finally:
            owner.cleanup()


if __name__ == '__main__':
    require_flag = sys.argv[1:] == ['--offline-static-unit']
    if not require_flag:
        sys.exit(2)
    capture = io.StringIO()
    with patch.object(socket, 'socket', side_effect=AssertionError('Network forbidden in offline tests')), patch.object(socket, 'create_connection', side_effect=AssertionError('Network forbidden in offline tests')):
        result = unittest.TextTestRunner(stream=capture).run(unittest.defaultTestLoader.loadTestsFromTestCase(StaticTests))
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'network_calls': 0}))
    if not result.wasSuccessful():
        print(capture.getvalue(), file=sys.stderr)
    sys.exit(0 if result.wasSuccessful() else 1)
