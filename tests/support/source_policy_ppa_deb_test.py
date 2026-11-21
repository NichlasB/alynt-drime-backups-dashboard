"""Synthetic DEBs and owned temporary fixtures only; no PPA acquisition."""
import contextlib
import gzip
import hashlib
import importlib.util
import io
import json
import lzma
import os
from pathlib import Path
import random
import struct
import subprocess
import sys
import tempfile
import unittest
from unittest.mock import Mock, patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
import source_policy_deb_data as D
import source_policy_deb_stream as C
SPEC = importlib.util.spec_from_file_location('deb_runner', ROOT/'scripts/ci/source-policy-ppa-deb-static.py')
M = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(M)
SPEC = importlib.util.spec_from_file_location('tar_fixture', ROOT/'tests/support/source_policy_php_archive_test.py')
F = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(F)


def tar(*rows):
    return b''.join(rows)+bytes(1024)


def ar(*rows):
    data = bytearray(b'!<arch>\n')
    for name, value in rows:
        header = name.encode().ljust(16)+b'0'.ljust(12)+b'0'.ljust(6)+b'0'.ljust(6)+b'100644'.ljust(8)+str(len(value)).encode().ljust(10)+b'`\n'
        data.extend(header+value+(b'\n' if len(value) % 2 else b''))
    return bytes(data)


def identity(package):
    return ('Package: '+package+'\nVersion: '+D.REVISION+'\nArchitecture: amd64\nDepends: libc6 (>= 2.38)\n').encode()


def stream(data, budget):
    return D.Slice(io.BytesIO(data), 0, len(data), budget)


class Checks(unittest.TestCase):
    def inventory(self, raw, budget=None, package='php8.5-cli', category='data'):
        budget = budget or D.Budget()
        return D.tar_inventory(stream(raw, budget), budget, package, category)

    def test_ar_positive_and_closed_shapes(self):
        good = ar(('debian-binary', b'2.0\n'), ('control.tar', b'odd'), ('data.tar', b'data'))
        rows = D.ar_members(io.BytesIO(good), D.Budget())
        self.assertEqual(['control.tar', 'data.tar'], [r[0] for r in rows])
        self.assertEqual(b'odd', good[rows[0][1]:rows[0][1]+rows[0][2]])
        for bad in (good[:-1], good.replace(b'!<arch>', b'!<thin>', 1), good.replace(b'2.0\n', b'3.0\n', 1),
                    ar(('debian-binary', b'2.0\n'), ('data.tar', b''), ('control.tar', b'')),
                    good+ar(('extra', b''))[8:], good.replace(b'control.tar', b'control.zip', 1),
                    good.replace(b'odd\n', b'oddX', 1)):
            with self.assertRaises(D.S.Refusal):
                D.ar_members(io.BytesIO(bad), D.Budget())

    def test_exact_publisher_build_delivery_keeps_byte_pins(self):
        expected = (
            ('php8.5-cli', 3165930, '9ee42a60bccc4b2fb91624a05535702d9c45699e61d20ace18e39d69bf7e53c8', '882014196'),
            ('php8.5-common', 850622, 'fc3adc82865c0046614cd577ad816db44a8923672236f7637eb7256673efc7d1', '882014197'),
            ('php8.5-mysql', 129750, '81cf7b2d0330ce86d9287bed2bdbb7210f41ab3846bee9c161babdecdf436279', '882014209'),
        )
        self.assertEqual(tuple(row[:3] for row in expected), D.PINS)
        self.assertEqual({row[0] for row in expected}, set(D.DELIVERIES))
        for name, size, sha, number in expected:
            self.assertEqual('https://launchpadlibrarian.net/'+number+'/'+name+'_8.5.12-1+ubuntu24.04.1+deb.sury.org+1_amd64.deb', D.DELIVERIES[name])
        self.assertEqual(4146302, sum(row[1] for row in D.PINS))
        class Response(io.BytesIO):
            status, headers = 200, {'Content-Length': '3'}
        with tempfile.TemporaryDirectory() as temp:
            for name, size, sha, number in expected:
                with patch.object(M.urllib.request, 'build_opener') as opener:
                    opener.return_value.open.return_value = Response(b'abc')
                    M.download(Path(temp), (name, 3, hashlib.sha256(b'abc').hexdigest()), D.Budget())
                    self.assertEqual('https://launchpadlibrarian.net/'+number+'/'+name+'_8.5.12-1+ubuntu24.04.1+deb.sury.org+1_amd64.deb', opener.return_value.open.call_args.args[0])

    def test_positive_three_role_inventory_and_control(self):
        budget = D.Budget()
        self.inventory(tar(F.member('./', b'', b'5'), F.member('./usr/bin/php8.5')), budget)
        self.inventory(tar(F.member('./usr/lib/php/20250925/mysqlnd.so'), F.member('./usr/lib/php/20250925/mysqli.so')), budget, 'php8.5-mysql')
        row = self.inventory(tar(F.member('./control', identity('php8.5-cli'))), budget, category='control')
        self.assertEqual('amd64', row['control']['architecture'])
        self.assertEqual({'cli', 'mysqlnd', 'mysqli'}, set(budget.roles))
        self.assertEqual('x86_64', budget.roles['cli']['machine'])
        self.assertFalse(budget.roles['cli']['executed'])
        self.assertEqual('Unknown', budget.roles['cli']['versioned_symbols'])
        self.assertEqual(5, budget.entries)

    def test_virtual_names_and_original_profile_remain_separate(self):
        self.assertEqual('usr/bin/x', D.virtual_name('./usr/bin/x', b'0', 1))
        for name in ('../x', '/x', '././x', 'a//x', 'C:/x', 'a\\x', ''):
            with self.assertRaises(D.S.Refusal):
                D.virtual_name(name, b'0', 1)
        with self.assertRaises(D.S.Refusal):
            D.A.inventory(io.BytesIO(tar(F.member('./usr/bin/php8.5'))))

    def test_tar_refuses_ambiguity_and_selected_links(self):
        for raw in (tar(F.member('x'), F.member('./x')), tar(F.member(kind=b'2', data=b'', link=b'elsewhere')),
                    tar(F.member(kind=b'x')), tar(F.member(kind=b'3')), tar(F.member())[:-1],
                    tar(F.member())+b'nonzero', tar(F.member('control', b'bad'))):
            with self.assertRaises(D.S.Refusal):
                self.inventory(raw, category='control' if b'control' in raw[:100] else 'data')
        budget = D.Budget()
        self.inventory(tar(F.member('x')), budget)
        with self.assertRaisesRegex(D.S.Refusal, 'collision'):
            self.inventory(tar(F.member('x')), budget, 'php8.5-common')

    def test_control_exact_identity_and_missing_refusal(self):
        good = identity('php8.5-common')
        self.assertEqual(D.REVISION, D.control(good, 'php8.5-common')['version'])
        for bad in (good.replace(b'amd64', b'arm64'), good.replace(b'php8.5-common', b'php8.5-cli'), good+b'Package: duplicate\n', b' continuation\n'+good, good+b'Depends: x\n'):
            with self.assertRaises(D.S.Refusal):
                D.control(bad, 'php8.5-common')
        with self.assertRaisesRegex(D.S.Refusal, 'control-missing'):
            self.inventory(tar(), category='control')

    def test_shared_work_entry_and_deadline_bounds(self):
        budget = D.Budget()
        budget.total = D.LIMIT-1
        stream(b'ab', budget).read(1)
        with self.assertRaisesRegex(D.S.Refusal, 'work-budget'):
            stream(b'c', budget).read(1)
        budget = D.Budget()
        budget.entries = 8192
        with self.assertRaisesRegex(D.S.Refusal, 'entry-budget'):
            self.inventory(tar(F.member()), budget)
        budget = D.Budget()
        budget.deadline = 0
        with self.assertRaisesRegex(D.S.Refusal, 'deadline'):
            budget.charge(0)

    def test_all_three_packages_end_to_end_with_synthetic_elf(self):
        with tempfile.TemporaryDirectory() as temp:
            root = Path(temp)
            for package, size, sha in D.PINS:
                data = tar(F.member()) if package == 'php8.5-cli' else tar()
                if package == 'php8.5-mysql':
                    data = tar(F.member('usr/lib/php/20250925/mysqlnd.so'), F.member('usr/lib/php/20250925/mysqli.so'))
                (root/M.filename(package)).write_bytes(ar(('debian-binary', b'2.0\n'), ('control.tar', tar(F.member('control', identity(package)))), ('data.tar', data)))
            facts = M.inspect(root, D.Budget())
            self.assertEqual(3, len(facts['packages']))
            self.assertEqual(3, len(facts['roles']))
            self.assertFalse(facts['dependency_closure_verified'])
            (root/M.filename('php8.5-mysql')).write_bytes(ar(('debian-binary', b'2.0\n'), ('control.tar', tar(F.member('control', identity('php8.5-mysql')))), ('data.tar', tar())))
            with self.assertRaisesRegex(D.S.Refusal, 'roles-missing'):
                M.inspect(root, D.Budget())

    def test_target_tuple_and_redirect_guards(self):
        good = F.env()
        good['GITHUB_WORKFLOW_REF'] = good['GITHUB_WORKFLOW_REF'].replace(F.M.WORKFLOW, M.WORKFLOW)
        self.assertEqual('adb-ppa-deb-123', M.target(good).name)
        for key in ('GITHUB_ACTIONS', 'GITHUB_EVENT_NAME', 'GITHUB_REPOSITORY', 'GITHUB_REF', 'GITHUB_ACTOR', 'GITHUB_WORKFLOW_REF', 'EXPECTED_SHA', 'GITHUB_SHA', 'GITHUB_RUN_ATTEMPT', 'GITHUB_RUN_ID'):
            with self.assertRaises(D.S.Refusal, msg=key):
                M.target({**good, key: 'wrong'})
        with self.assertRaises(D.S.Refusal):
            M.filename('unapproved')
        with self.assertRaisesRegex(D.S.Refusal, 'redirect'):
            M.NoRedirect().redirect_request(None, None, 302, '', {}, 'https://evil.invalid/')

    def test_exact_acquisition_integrity_without_network(self):
        class Response(io.BytesIO):
            status, headers = 200, {'Content-Length': '3'}
        with tempfile.TemporaryDirectory() as temp:
            for index, body in enumerate((b'abc', b'abd', b'ab', b'abcd')):
                root = Path(temp)/str(index)
                root.mkdir()
                with patch.object(M.urllib.request, 'build_opener') as opener:
                    opener.return_value.open.return_value = Response(body)
                    pin = ('php8.5-cli', 3, hashlib.sha256(b'abc').hexdigest())
                    if index == 0:
                        self.assertEqual(3, M.download(root, pin, D.Budget())['bytes'])
                        self.assertEqual('https://launchpadlibrarian.net/882014196/php8.5-cli_8.5.12-1+ubuntu24.04.1+deb.sury.org+1_amd64.deb', opener.return_value.open.call_args.args[0])
                    else:
                        with self.assertRaises(D.S.Refusal):
                            M.download(root, pin, D.Budget())

    def test_owned_cleanup_preserves_foreign_files(self):
        with tempfile.TemporaryDirectory() as temp, patch.dict(os.environ, {'EXPECTED_SHA': 'a'*40}), patch.object(M.os, 'getuid', return_value=os.stat(temp).st_uid, create=True):
            root = Path(temp)/'owned'
            root.mkdir(mode=0o700)
            original = M.stat.S_IMODE
            with patch.object(M.stat, 'S_IMODE', side_effect=lambda mode: 0o700 if M.stat.S_ISDIR(mode) else original(mode)):
                (root/'owner.json').write_bytes(M.marker(root))
                foreign = root/'foreign'
                foreign.write_bytes(b'preserve')
                with self.assertRaises(D.S.Refusal):
                    M.cleanup(root)
                self.assertEqual(b'preserve', foreign.read_bytes())
                foreign.unlink()
                (root/M.filename('php8.5-cli')).write_bytes(b'owned compressed DATA')
                self.assertEqual(0, M.cleanup(root))
                self.assertFalse(root.exists())

    def test_http_refusal_retains_only_bounded_status(self):
        for code in (404, 503, True, 0, 600, 'private-status'):
            error = M.urllib.error.HTTPError('https://private.invalid/', code, 'private-message', {'Private': 'private-value'}, io.BytesIO(b'private-body'))
            facts = M.http_refusal(error)
            self.assertEqual({'reason': 'ppa-http-refusal', 'http_status': code if type(code) is int and 100 <= code <= 599 else 'Unknown'}, facts)
            self.assertNotIn('private', json.dumps(facts))
        with tempfile.TemporaryDirectory() as temp, patch.dict(os.environ, {'EXPECTED_SHA': 'a'*40}), patch.object(M.os, 'getuid', return_value=os.stat(temp).st_uid, create=True):
            root, output = Path(temp)/'owned', io.StringIO()
            original = M.stat.S_IMODE
            error = M.urllib.error.HTTPError('https://private.invalid/', 404, 'private-message', {}, None)
            with patch.object(M.stat, 'S_IMODE', side_effect=lambda mode: 0o700 if M.stat.S_ISDIR(mode) else original(mode)), patch.object(M, 'target', return_value=root), patch.object(M, 'preflight', return_value={}), patch.object(M, 'download', side_effect=error), patch.object(M.sys, 'argv', ['owned', '--approved-static-proof']), patch.object(M.signal, 'SIGALRM', 14, create=True), patch.object(M.signal, 'signal'), patch.object(M.signal, 'alarm', create=True), contextlib.redirect_stdout(output):
                self.assertEqual(1, M.main())
            receipt = json.loads(output.getvalue())
            self.assertEqual('ppa-http-refusal', receipt['reason'])
            self.assertEqual(404, receipt['http_status'])
            self.assertEqual(0, receipt['owned_deb_remaining'])
            self.assertFalse(receipt['static_inventory_complete'])
            self.assertFalse(root.exists())
            self.assertNotIn('private', output.getvalue())
            self.assertTrue(error.closed)

    def test_guard_controls_are_assertion_killed(self):
        for method, replacement, test in (
            ('virtual_name', lambda name, kind, size: name[2:] if name.startswith('./') else name, 'test_virtual_names_and_original_profile_remain_separate'),
            ('control', lambda data, package: {'version': D.REVISION}, 'test_control_exact_identity_and_missing_refusal'),
        ):
            with patch.object(D, method, replacement):
                result = unittest.TestResult()
                Checks(test).run(result)
            self.assertEqual(1, len(result.failures))
            self.assertEqual(0, len(result.errors))
        with patch.object(D.Budget, 'charge', lambda self, count: None):
            result = unittest.TestResult()
            Checks('test_shared_work_entry_and_deadline_bounds').run(result)
        self.assertEqual(1, len(result.failures))
        self.assertEqual(0, len(result.errors))
        wrong = {name: D.DELIVERIES['php8.5-cli'] for name in D.DELIVERIES}
        with patch.object(M, 'DELIVERIES', wrong):
            result = unittest.TestResult()
            Checks('test_exact_publisher_build_delivery_keeps_byte_pins').run(result)
        self.assertEqual(1, len(result.failures))
        self.assertEqual(0, len(result.errors))

    def test_window_refusal_before_decoder_start(self):
        raw = b'\x28\xb5\x2f\xfd\x00\x88'+bytes(12)
        with patch.object(C.subprocess, 'Popen') as start, self.assertRaisesRegex(D.S.Refusal, 'frame-budget'):
            C.Decoder(stream(raw, D.Budget()), '.zst', D.Budget())
        start.assert_not_called()

    def test_selector_setup_failure_reaps_already_started_child(self):
        child = Mock(stdin=io.BytesIO(), stdout=io.BytesIO())
        child.poll.return_value = None
        child.wait.return_value = 0
        with patch.object(C.subprocess, 'Popen', return_value=child), patch.object(C.selectors, 'DefaultSelector', side_effect=OSError('synthetic')):
            with self.assertRaises(OSError):
                C.Decoder(stream(b'data', D.Budget()), '.gz', D.Budget())
        child.kill.assert_called_once_with()
        child.wait.assert_called_once_with(timeout=5)
        self.assertTrue(child.stdin.closed and child.stdout.closed)


class LinuxChecks(unittest.TestCase):
    def test_real_codec_slice_pipes_and_reaping_synthetic_only(self):
        # Existing trusted tools only, never a candidate binary or package.
        code = 'import sys,json;sys.path.insert(0,'+repr(str(ROOT/'scripts/ci'))+');import source_policy_deb_stream as c;print(json.dumps(c.preflight()))'
        probe = subprocess.run([sys.executable, '-I', '-S', '-B', '-c', code], capture_output=True, timeout=20)
        self.assertEqual(0, probe.returncode, probe.stderr)
        facts = json.loads(probe.stdout)
        self.assertEqual(40*D.A.MIB, facts['parent_address_space'])
        self.assertTrue(facts['gzip'] and facts['xz'] and facts['zstd'])
        # Incompressible DATA exceeds both pipe capacities, exercising simultaneous
        # input/output readiness rather than only a tiny all-buffered success.
        data = random.Random(7).randbytes(131072)
        zstd = b'\x28\xb5\x2f\xfd\xa0'+struct.pack('<I', len(data))+struct.pack('<I', (len(data)<<3)|1)[:3]+data
        fixtures = {'.gz': gzip.compress(data), '.xz': lzma.compress(data, preset=0), '.zst': zstd}
        for suffix, raw in fixtures.items():
            budget = D.Budget()
            source = stream(raw, budget)
            decoder = C.Decoder(source, suffix, budget)
            try:
                output = bytearray()
                while True:
                    part = decoder.read(4093)
                    if not part:
                        break
                    output.extend(part)
                self.assertEqual(data, output)
                self.assertEqual(0, decoder.process.returncode)
                self.assertEqual(0, source.remaining)
                self.assertGreaterEqual(budget.total, len(raw)+len(data))
            finally:
                decoder.close()
            self.assertIsNotNone(decoder.process.poll())
        budget = D.Budget()
        decoder = C.Decoder(stream(gzip.compress(b'x')[:-4], budget), '.gz', budget)
        try:
            with self.assertRaises(D.S.Refusal):
                while decoder.read(4093):
                    pass
        finally:
            decoder.close()
        self.assertIsNotNone(decoder.process.poll())
        budget = D.Budget()
        decoder = C.Decoder(stream(gzip.compress(b'x'), budget), '.gz', budget)
        budget.total = D.LIMIT
        try:
            with self.assertRaisesRegex(D.S.Refusal, 'work-budget'):
                decoder.read(4093)
        finally:
            decoder.close()
        self.assertIsNotNone(decoder.process.poll())


if __name__ == '__main__':
    suite = unittest.defaultTestLoader.loadTestsFromTestCase(Checks)
    if sys.platform == 'linux':
        suite.addTests(unittest.defaultTestLoader.loadTestsFromTestCase(LinuxChecks))
    result = unittest.TestResult()
    suite.run(result)
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'network_calls': 0, 'linux_codec_test': sys.platform == 'linux'}))
    if not result.wasSuccessful():
        print(result.failures+result.errors, file=sys.stderr)
        raise SystemExit(1)
