"""Owned synthetic tar layers only; no acquisition, extraction or execution."""
import copy
import gzip
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import socket
import sys
import tarfile
import tempfile
import time
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
spec = importlib.util.spec_from_file_location('alias', ROOT/'scripts/manual/source_policy_ubi_alias_static.py')
a = importlib.util.module_from_spec(spec)
with patch.object(socket, 'socket', side_effect=AssertionError('network forbidden')):
    spec.loader.exec_module(a)


def member(name, kind=tarfile.REGTYPE, target='', data=b''):
    row = tarfile.TarInfo(name); row.type = kind; row.linkname = target
    row.mode = 0o755; row.size = len(data)
    return row, data


def fixture(rows):
    raw = io.BytesIO()
    with tarfile.open(fileobj=raw, mode='w') as archive:
        for row, data in rows: archive.addfile(row, io.BytesIO(data))
    expanded = raw.getvalue(); blob = gzip.compress(expanded)
    return blob, hashlib.sha256(blob).hexdigest(), 'sha256:'+hashlib.sha256(expanded).hexdigest()


class Contracts(unittest.TestCase):
    def scan(self, rows, **changes):
        blob, compressed, diff = fixture(rows)
        with tempfile.TemporaryDirectory() as parent:
            path = Path(parent)/'owned.blob'; path.write_bytes(blob)
            args = dict(path=path, compressed=compressed, size=len(blob), diff_id=diff,
                        budget=dict(entries=0, expanded=0, deadline=time.monotonic()+5))
            args.update(changes)
            return a.collect(**args)

    def rows(self):
        return [member('usr', tarfile.DIRTYPE), member('usr/lib64', tarfile.DIRTYPE)]+[
            member('pad'+str(i)) for i in range(656)]+[
            member('lib64', tarfile.SYMTYPE, 'usr/lib64'), member('usr/lib64/loader', data=b'loader')]

    def test_complete_layer_and_exact_alias_positive_no_grant(self):
        result = self.scan(self.rows())
        self.assertTrue(result['metadata']['diff_verified'])
        self.assertEqual(660, result['scan']['entries'])
        self.assertEqual(659, result['ordinals']['lib64'])
        profile = a.approved_profile(result, 0)
        self.assertEqual([dict(path='lib64', target='usr/lib64', layer=0, entry=658)], profile)
        meta = dict(result['metadata'], ordinal=0)
        identity = dict(manifest=a.PIN, config=a.CONFIG, source='c'*64)
        model = a.v.VirtualClosure(identity, [(meta['compressed'], meta['diff_id'])], profile)
        model.apply(meta, result['records'], 0); model.finalize(1)
        receipt = model.resolve('lib64/loader', 2)
        self.assertEqual('usr/lib64/loader', receipt['canonical'])
        self.assertEqual(hashlib.sha256(b'loader').hexdigest(), receipt['sha256'])
        self.assertFalse(receipt['runtime_grant']); self.assertEqual((), a.v.real_aliases())

    def test_compressed_diffid_and_bounds_refuse_without_returning_records(self):
        for changes in (dict(compressed='a'*64), dict(size=1), dict(diff_id='sha256:'+'b'*64),
                        dict(budget=dict(entries=65536, expanded=0, deadline=time.monotonic()+5)),
                        dict(budget=dict(entries=0, expanded=0, deadline=0)),
                        dict(budget=dict(entries=0, expanded=2048*a.s.MIB, deadline=time.monotonic()+5))):
            with self.assertRaises(a.s.Refusal): self.scan([member('file')], **changes)

    def test_alias_profile_is_fixed_not_candidate_minted(self):
        good = self.scan(self.rows())
        for ordinal in (1, True):
            with self.assertRaises(a.s.Refusal): a.approved_profile(good, ordinal)
        for field, value in [('target', 'elsewhere'), ('kind', 'file')]:
            bad = copy.deepcopy(good); bad['records'][658][field] = value
            with self.assertRaises(a.s.Refusal): a.approved_profile(bad, 0)
        bad = copy.deepcopy(good); bad['ordinals']['lib64'] = 658
        with self.assertRaises(a.s.Refusal): a.approved_profile(bad, 0)
        bad = copy.deepcopy(good); bad['metadata']['diff_verified'] = False
        with self.assertRaises(a.s.Refusal): a.approved_profile(bad, 0)

    def test_unsafe_names_sparse_special_hardlinks_and_duplicates_refuse(self):
        for rows in ([member('../escape')], [member('/absolute')], [member('a'), member('a')],
                     [member('a', tarfile.LNKTYPE, '../b')], [member('a', tarfile.FIFOTYPE)],
                     [member('a', tarfile.SYMTYPE, '../escape')]):
            with self.assertRaises(a.s.Refusal): self.scan(rows)
        row, _ = member('a'); row.pax_headers = {'GNU.sparse.unknown': '1'}
        with self.assertRaises(a.s.Refusal): self.scan([(row, b'')])
        # The approved collector may quarantine a canonical hardlink, never grant a missing target.
        result = self.scan([member('a', tarfile.LNKTYPE, 'b')]); meta = dict(result['metadata'], ordinal=0)
        model = a.v.VirtualClosure(dict(manifest=a.PIN, config=a.CONFIG, source='c'*64),
                                  [(meta['compressed'], meta['diff_id'])], hardlinks=True)
        with self.assertRaises(a.v.Refusal): model.apply(meta, result['records'], 0)

    def test_whiteout_records_are_not_files_and_apply_atomically(self):
        result = self.scan([member('d', tarfile.DIRTYPE), member('d/.wh.a'), member('d/.wh..wh..opq')])
        self.assertEqual(['directory', 'whiteout', 'opaque'], [r['kind'] for r in result['records']])
        with self.assertRaises(a.s.Refusal): self.scan([member('.wh.a', data=b'x')])

    def test_unapproved_links_and_late_replacement_cannot_resolve(self):
        result = self.scan(self.rows()); meta = dict(result['metadata'], ordinal=0)
        identity = dict(manifest=a.PIN, config=a.CONFIG, source='c'*64)
        extra = self.scan([member('lib64', tarfile.SYMTYPE, '/usr/lib64')])
        later = dict(extra['metadata'], ordinal=1)
        model = a.v.VirtualClosure(identity, [(meta['compressed'], meta['diff_id']),
                                  (later['compressed'], later['diff_id'])], a.approved_profile(result, 0))
        model.apply(meta, result['records'], 0); model.apply(later, extra['records'], 1); model.finalize(2)
        with self.assertRaises(a.v.Refusal): model.resolve('lib64/loader', 3)
        with self.assertRaises(a.s.Refusal): a.approved_profile(extra, 0)

    def test_original_reader_still_refuses_the_same_symlink(self):
        rows = self.rows(); blob, _, diff = fixture(rows)
        with tempfile.TemporaryDirectory() as parent:
            path = Path(parent)/'owned.blob'; path.write_bytes(blob)
            with self.assertRaisesRegex(a.s.Refusal, 'critical-parent-not-directory'):
                a.s.scan_layer(path, diff, ('lib64/loader',), {},
                               dict(entries=0, expanded=0, deadline=time.monotonic()+5))

    def test_hash_and_profile_guard_removals_are_assertion_killed(self):
        original = a.s.require
        for selected, operation in [('alias-compressed-identity', lambda: self.scan([member('a')], compressed='a'*64)),
                                    ('alias-profile', lambda: a.approved_profile(self.scan(self.rows()), 1)),
                                    ('diff-id-mismatch', lambda: self.scan([member('a')], diff_id='sha256:'+'b'*64))]:
            with self.assertRaises(a.s.Refusal): operation()
            def bypass(ok, reason, selected=selected):
                if reason != selected: original(ok, reason)
            with patch.object(a.s, 'require', bypass):
                with self.assertRaises(AssertionError):
                    with self.assertRaises(a.s.Refusal): operation()


if __name__ == '__main__':
    output = io.StringIO()
    result = unittest.TextTestRunner(stream=output).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun, failures=len(result.failures), errors=len(result.errors), network_calls=0)))
    if not result.wasSuccessful(): print(output.getvalue(), file=sys.stderr)
    sys.exit(0 if result.wasSuccessful() else 1)
