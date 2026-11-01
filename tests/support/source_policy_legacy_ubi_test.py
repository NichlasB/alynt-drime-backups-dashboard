"""Offline tests of actual fixed metadata, cleanup and critical-path reader."""
import copy
import gzip
import hashlib
import importlib.util
import io
import json
import os
from pathlib import Path
import socket
import sys
import tarfile
import tempfile
import time
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('ubi', ROOT / 'scripts/ci/source-policy-legacy-ubi-static.py')
u = importlib.util.module_from_spec(spec)
with patch.object(socket, 'socket', side_effect=AssertionError('network forbidden')):
    spec.loader.exec_module(u)


def fixed():
    m = {'schemaVersion': 2, 'mediaType': 'application/vnd.oci.image.manifest.v1+json',
         'config': {'digest': 'sha256:' + u.CONFIG, 'size': 13902},
         'layers': [{'digest': 'sha256:' + sha, 'size': size,
                     'mediaType': 'application/vnd.oci.image.layer.v1.tar+gzip'} for sha, size in u.LAYERS]}
    c = {'os': 'linux', 'architecture': 'amd64', 'rootfs': {'type': 'layers', 'diff_ids': ['sha256:' + 'a'*64]*10},
         'config': {'User': 'mysql', 'Entrypoint': ['docker-entrypoint.sh'], 'Cmd': ['mariadbd'],
                    'Volumes': {'/var/lib/mysql': {}},
                    'Env': ['PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                            'container=oci', 'GOSU_VERSION=1.17', 'MARIADB_VERSION=10.6.23']}}
    return m, c


class Contracts(unittest.TestCase):
    def test_metadata_positive_and_independent_refusals(self):
        m, c = fixed()
        self.assertTrue(u.admit(m, c))
        for key, value in [('schemaVersion', 1), ('mediaType', 'other'), ('layers', [])]:
            bad = copy.deepcopy(m); bad[key] = value
            with self.assertRaises(u.s.Refusal): u.admit(bad, c)
        for key, value in [('digest', 'sha256:'+'b'*64), ('size', 1), ('mediaType', 'other')]:
            bad = copy.deepcopy(m); bad['layers'][0][key] = value
            with self.assertRaises(u.s.Refusal): u.admit(bad, c)
        for key, value in [('os', 'windows'), ('architecture', 'arm64')]:
            bad = copy.deepcopy(c); bad[key] = value
            with self.assertRaises(u.s.Refusal): u.admit(m, bad)
        for key, value in [('User', 'root'), ('Entrypoint', ['sh']), ('Cmd', ['--initialize']), ('Volumes', {}), ('Env', [])]:
            bad = copy.deepcopy(c); bad['config'][key] = value
            with self.assertRaises(u.s.Refusal): u.admit(m, bad)
        bad = copy.deepcopy(c); bad['rootfs']['diff_ids'] = []
        with self.assertRaises(u.s.Refusal): u.admit(m, bad)

    def test_job_scope_refuses_before_creation(self):
        with tempfile.TemporaryDirectory() as parent:
            env = dict(GITHUB_ACTIONS='true', GITHUB_EVENT_NAME='workflow_dispatch',
                       GITHUB_REPOSITORY='NichlasB/alynt-drime-backups-dashboard', GITHUB_REF='refs/heads/master',
                       GITHUB_WORKFLOW_REF='NichlasB/alynt-drime-backups-dashboard/.github/workflows/source-policy-legacy-ubi-static.yml@refs/heads/master',
                       EXPECTED_SHA='a'*40, GITHUB_SHA='a'*40, GITHUB_RUN_ID='123', GITHUB_RUN_ATTEMPT='1', RUNNER_TEMP=str(Path(parent).resolve()))
            root = u.target(env)
            self.assertFalse(root.exists())
            for key in ('GITHUB_ACTIONS', 'GITHUB_EVENT_NAME', 'GITHUB_REPOSITORY', 'GITHUB_REF',
                        'GITHUB_WORKFLOW_REF', 'EXPECTED_SHA', 'GITHUB_SHA', 'GITHUB_RUN_ID', 'GITHUB_RUN_ATTEMPT'):
                bad = dict(env); bad[key] = 'foreign'
                with self.assertRaises(u.s.Refusal): u.target(bad)
            self.assertFalse(root.exists())

    def test_cleanup_unknown_leaf_is_preserved_and_exact_owned_cleanup_works(self):
        with tempfile.TemporaryDirectory() as parent:
            root = Path(parent).resolve() / 'adb-legacy-ubi-123-1'
            owned = u.Owned(root)
            owned.write('manifest.json', b'{}')
            foreign = root / 'foreign.json'; foreign.write_bytes(b'keep')
            with patch.object(u.os, 'getuid', return_value=root.stat().st_uid, create=True):
                with self.assertRaises(u.s.Refusal): owned.cleanup()
                self.assertEqual(b'keep', foreign.read_bytes())
                self.assertTrue((root / 'manifest.json').exists())
                foreign.unlink()
                # Windows has no permission-mode equivalence; Unix owner/mode proof is CI-only.
                if os.name != 'nt': self.assertEqual(2, owned.cleanup())
            if root.exists():
                (root / 'manifest.json').unlink(); (root / 'owner.json').unlink(); root.rmdir()

    def test_actual_reader_positive_and_alias_bypass_semantic_control(self):
        for linked in (False, True):
            raw = io.BytesIO()
            with tarfile.open(fileobj=raw, mode='w') as archive:
                member = tarfile.TarInfo('lib64')
                member.type = tarfile.SYMTYPE if linked else tarfile.DIRTYPE
                member.linkname = 'usr/lib64' if linked else ''
                archive.addfile(member)
            data = raw.getvalue()
            with tempfile.TemporaryDirectory() as parent:
                p = Path(parent) / 'layer.blob'; p.write_bytes(gzip.compress(data))
                args = (p, 'sha256:'+hashlib.sha256(data).hexdigest(), u.WANTED, {},
                        {'entries': 0, 'expanded': 0, 'deadline': time.monotonic()+5})
                if linked:
                    with self.assertRaisesRegex(u.s.Refusal, 'critical-parent-not-directory'): u.s.scan_layer(*args)
                    original = u.s.require
                    # Two independent critical-alias guards protect this same boundary.
                    def bypass(ok, code):
                        if code not in ('critical-parent-not-directory', 'critical-linked-path'): original(ok, code)
                    args[-1].update(entries=0, expanded=0)
                    with patch.object(u.s, 'require', bypass):
                        with self.assertRaises(AssertionError):
                            with self.assertRaises(u.s.Refusal): u.s.scan_layer(*args)
                else:
                    self.assertTrue(u.s.scan_layer(*args)['diff_id_verified'])


suite = unittest.defaultTestLoader.loadTestsFromTestCase(Contracts)
result = unittest.TextTestRunner(stream=io.StringIO()).run(suite)
print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'network_calls': 0}))
if not result.wasSuccessful():
    for _, trace in result.failures + result.errors: print(trace, file=sys.stderr)
sys.exit(0 if result.wasSuccessful() else 1)
