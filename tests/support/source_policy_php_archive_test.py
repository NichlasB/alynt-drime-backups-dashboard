"""Synthetic DATA/owned temporary files only; never acquire the pinned archive."""
import contextlib
import hashlib
import importlib.util
import io
import json
import os
from pathlib import Path
import struct
import tempfile
import unittest
from unittest.mock import patch

SPEC = importlib.util.spec_from_file_location('archive_data', Path(__file__).resolve().parents[2]/'scripts/ci/source-policy-php-archive-static.py')
M = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(M)


def member(name='usr/bin/php8.5', data=None, kind=b'0', link=b''):
    if data is None:
        data = bytearray(64)
        data[:7] = b'\x7fELF\x02\x01\x01'
        struct.pack_into('<HH', data, 16, 3, 62)
        struct.pack_into('<HH', data, 54, 56, 0)
    header = bytearray(512)
    header[:len(name)] = name.encode('ascii')
    header[124:136] = ('%011o\0' % len(data)).encode('ascii')
    header[156:157] = kind
    header[157:157+len(link)] = link
    header[257:263] = b'ustar\0'
    header[148:156] = b'        '
    header[148:156] = ('%06o\0 ' % sum(header)).encode('ascii')
    return bytes(header)+bytes(data)+bytes((-len(data)) % 512)


def env():
    return dict(GITHUB_ACTIONS='true', GITHUB_EVENT_NAME='workflow_dispatch',
                GITHUB_REPOSITORY='NichlasB/alynt-drime-backups-dashboard', GITHUB_REF='refs/heads/master',
                GITHUB_ACTOR='NichlasB', GITHUB_SHA='a'*40, EXPECTED_SHA='a'*40, GITHUB_RUN_ATTEMPT='1', GITHUB_RUN_ID='123',
                GITHUB_WORKFLOW_REF='NichlasB/alynt-drime-backups-dashboard/.github/workflows/'+M.WORKFLOW+'@refs/heads/master',
                RUNNER_TEMP=str(Path(tempfile.gettempdir()).resolve()))


class Checks(unittest.TestCase):
    def test_frame_positive_and_window_refusal(self):
        good = M.frame(b'\x28\xb5\x2f\xfd\x20\x01'+bytes(12))
        self.assertEqual(1, good['window_bytes'])
        M.admit_frame(good)
        large = M.frame(b'\x28\xb5\x2f\xfd\x00\x88'+bytes(12))
        self.assertEqual(128*M.MIB, large['window_bytes'])
        with self.assertRaisesRegex(M.S.Refusal, 'decoder-window-budget'):
            M.admit_frame(large)
        for raw in (bytes(18), b'\x28\xb5\x2f\xfd\x08'+bytes(13), b'\x28\xb5\x2f\xfd\x01'+bytes(13)):
            with self.assertRaises(M.S.Refusal):
                M.frame(raw)
        with self.assertRaisesRegex(M.S.Refusal, 'declared-expanded-budget'):
            M.admit_frame(dict(window_bytes=1, declared_content_bytes=M.EXPANDED+1))

    def test_inventory_positive_elf_and_cumulative_work(self):
        archive = member()+bytes(1024)
        row = M.inventory(io.BytesIO(archive))
        self.assertEqual(1, row['entries'])
        self.assertEqual(len(archive), row['expanded_bytes'])
        self.assertEqual('x86_64', row['roles']['cli']['machine'])
        self.assertFalse(row['roles']['cli']['executed'])
        with patch.object(M, 'EXPANDED', len(archive)-1), self.assertRaisesRegex(M.S.Refusal, 'expanded-budget'):
            M.inventory(io.BytesIO(archive))

    def test_archive_path_type_and_ambiguity_guards(self):
        for name in ('../x', '/x', './usr/bin/php8.5', 'C:/x', 'a//x'):
            with self.assertRaises(M.S.Refusal):
                M.inventory(io.BytesIO(member(name=name)+bytes(1024)))
        for raw in (member()+member()+bytes(1024), member(kind=b'x'), member(kind=b'3'), member()[:-1],
                    member(kind=b'2', data=b'', link=b'elsewhere')+bytes(1024), member()+bytes(1024)+b'not-padding'):
            with self.assertRaises(M.S.Refusal):
                M.inventory(io.BytesIO(raw))
        for raw in (b'77777777777\x80', b'           1', b'\x80'+bytes(11)):
            with self.assertRaises(M.S.Refusal):
                M.number(raw)

    def test_delivery_origin_and_redirect_guards(self):
        self.assertEqual(M.URL, M.safe_url(M.URL))
        for url in ('http://github.com/x', 'https://github.com.evil/x', 'https://u:p@github.com/x',
                    'https://github.com:444/x', 'https://127.0.0.1/x', 'https://github.com/x#fragment'):
            with self.assertRaises(M.S.Refusal):
                M.safe_url(url)
        handler = M.Redirects()
        handler.count = 3
        with self.assertRaisesRegex(M.S.Refusal, 'redirect-bound'):
            handler.redirect_request(None, None, 302, '', {}, M.URL)

    def test_exact_job_guards(self):
        good = env()
        self.assertEqual('adb-php-archive-123', M.target(good).name)
        for key in ('GITHUB_ACTIONS', 'GITHUB_EVENT_NAME', 'GITHUB_REPOSITORY', 'GITHUB_REF', 'GITHUB_ACTOR',
                    'GITHUB_WORKFLOW_REF', 'EXPECTED_SHA', 'GITHUB_RUN_ATTEMPT', 'GITHUB_RUN_ID'):
            bad = {**good, key: 'wrong'}
            with self.assertRaises(M.S.Refusal, msg=key):
                M.target(bad)

    def test_decoder_environment_retains_only_owned_tracking(self):
        with patch.dict(os.environ, {'RUNNER_TRACKING_ID': 'owned-test', 'GH_TOKEN': 'not-forwarded'}, clear=True):
            self.assertEqual({'PATH': '/usr/bin:/bin', 'RUNNER_TRACKING_ID': 'owned-test'}, M.decoder_environment())
        with patch.dict(os.environ, {}, clear=True):
            self.assertEqual({'PATH': '/usr/bin:/bin'}, M.decoder_environment())

    def test_exact_download_digest_and_size(self):
        class Response(io.BytesIO):
            status, headers = 200, {'Content-Length': '3'}
        with tempfile.TemporaryDirectory() as temp, patch.object(M, 'SIZE', 3), patch.object(M, 'SHA', hashlib.sha256(b'abc').hexdigest()):
            for body, accepted in ((b'abc', True), (b'abd', False), (b'ab', False), (b'abcd', False)):
                with patch.object(M.urllib.request, 'build_opener') as opener:
                    opener.return_value.open.return_value = Response(body)
                    path = Path(temp)/('archive-'+body.hex())
                    if accepted:
                        self.assertEqual(3, M.download(path)['bytes'])
                    else:
                        with self.assertRaises(M.S.Refusal):
                            M.download(path)

    def test_owned_cleanup_refuses_unknown_and_preserves_marker(self):
        with tempfile.TemporaryDirectory() as temp, patch.dict(os.environ, {'EXPECTED_SHA': 'a'*40}), patch.object(M.os, 'getuid', return_value=os.stat(temp).st_uid, create=True):
            root = Path(temp)/'owned'
            root.mkdir(mode=0o700)
            # Windows does not expose Unix mode enforcement; mock only stat mode.
            original = M.stat.S_IMODE
            with patch.object(M.stat, 'S_IMODE', side_effect=lambda mode: 0o700 if M.stat.S_ISDIR(mode) else original(mode)):
                (root/'owner.json').write_bytes(M.owner(root))
                (root/'foreign').write_bytes(b'preserve')
                with self.assertRaises(M.S.Refusal):
                    M.cleanup(root)
                self.assertTrue((root/'foreign').exists())
                (root/'foreign').unlink()
                (root/'archive.zst').write_bytes(b'owned data')
                self.assertEqual(0, M.cleanup(root))
                self.assertFalse(root.exists())

    def test_frame_guard_assertion_killed_control(self):
        # Deliberately bypass the implementation guard; the unchanged real test
        # must fail, not pass vacuously because all results are unavailable.
        with patch.object(M, 'admit_frame', lambda facts: None):
            result = unittest.TestResult()
            Checks('test_frame_positive_and_window_refusal').run(result)
        self.assertEqual(1, len(result.failures))
        self.assertEqual(0, len(result.errors))


if __name__ == '__main__':
    suite = unittest.defaultTestLoader.loadTestsFromTestCase(Checks)
    result = unittest.TestResult()
    with contextlib.redirect_stderr(io.StringIO()):
        suite.run(result)
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'network_calls': 0}))
    if not result.wasSuccessful():
        print(result.failures+result.errors, file=__import__('sys').stderr)
        raise SystemExit(1)
