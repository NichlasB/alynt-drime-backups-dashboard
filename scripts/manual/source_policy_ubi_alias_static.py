"""Separate bounded static DATA collector. No extraction or execution grant."""
import gzip
import hashlib
import importlib.util
from pathlib import Path
import tarfile
import time

HERE = Path(__file__).resolve().parent


def load(filename, module):
    spec = importlib.util.spec_from_file_location(module, HERE / filename)
    result = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(result)
    return result


s = load('source_policy_linux_minimum_static.py', 'ubi_alias_reader')
v = load('source_policy_virtual_closure.py', 'ubi_alias_model')
PIN = '14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1'
CONFIG = '2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35'


def record(member, budget):
    s.require(budget['entries'] <= 65536 and not member.sparse
              and not any(k.startswith('GNU.sparse') for k in member.pax_headers)
              and 0 <= member.size <= 128 * s.MIB, 'tar-entry-bound')
    s.require(len(member.pax_headers) <= 32
              and all(len(k) <= 128 and len(val) <= 4096 for k, val in member.pax_headers.items()), 'tar-metadata-bound')
    raw = member.name[2:] if member.name.startswith('./') else member.name
    raw = raw.rstrip('/') if member.isdir() else raw
    if raw in ('', '.') and member.isdir():
        return None
    name = s.tar_name(raw)
    s.require(member.type in (tarfile.REGTYPE, tarfile.AREGTYPE, tarfile.DIRTYPE, tarfile.SYMTYPE, tarfile.LNKTYPE), 'alias-unsupported-type')
    s.require(all(type(val) is int and 0 <= val <= cap for val, cap in
                  ((member.mode, 0o7777), (member.uid, 4294967295), (member.gid, 4294967295))), 'record-metadata')
    kind = 'file' if member.isfile() else 'directory' if member.isdir() else 'hardlink' if member.islnk() else 'symlink'
    target = member.linkname if member.issym() or member.islnk() else None
    if member.islnk():
        target = s.tar_name(target[2:] if target.startswith('./') else target)
        s.require(':' not in target, 'hardlink-target')
    elif target is not None:
        try:
            v.target(name.rpartition('/')[0], target)
        except v.Refusal as error:
            raise s.Refusal(str(error)) from None
    if name.rsplit('/', 1)[-1].startswith('.wh.'):
        s.require(member.isfile() and member.size == 0 and name.rsplit('/', 1)[-1] != '.wh.', 'whiteout-shape')
        kind = 'opaque' if name.rsplit('/', 1)[-1] == '.wh..wh..opq' else 'whiteout'
    s.require(kind == 'file' or member.size == 0, 'non-file-content')
    return dict(path=name, kind=kind, mode=member.mode, uid=member.uid, gid=member.gid,
                sha256=None, size=member.size, target=target)


def collect(path, compressed, size, diff_id, budget):
    expected = s.digest(diff_id)
    s.require(type(size) is int and 0 < size <= 128 * s.MIB, 'alias-compressed-bound')
    s.digest('sha256:'+compressed)
    count, sha = 0, hashlib.sha256()
    with path.open('rb') as stream:
        while True:
            s.require(time.monotonic() < budget['deadline'], 'scan-read-bound')
            block = stream.read(s.MIB)
            if not block:
                break
            count += len(block); sha.update(block)
            s.require(count <= size, 'alias-compressed-identity')
    s.require(count == size and sha.hexdigest() == compressed, 'alias-compressed-identity')
    records, ordinals = [], {}
    with gzip.open(path, 'rb') as stream:
        reader = s.ScanReader(stream, budget)
        with tarfile.open(fileobj=reader, mode='r|', encoding='utf-8', errors='surrogateescape') as archive:
            for member in archive:
                budget['entries'] += 1
                try:
                    row = record(member, budget)
                    if row is None:
                        continue
                    s.require(row['path'] not in ordinals, 'tar-duplicate')
                    if row['kind'] == 'file':
                        length, sha = 0, hashlib.sha256()
                        with archive.extractfile(member) as content:
                            while True:
                                s.require(time.monotonic() < budget['deadline'], 'scan-read-bound')
                                block = content.read(s.MIB)
                                if not block:
                                    break
                                length += len(block); sha.update(block)
                                s.require(length <= member.size, 'critical-size')
                        s.require(length == member.size, 'critical-size')
                        row['sha256'] = sha.hexdigest()
                    records.append(row); ordinals[row['path']] = budget['entries']
                except s.Refusal as error:
                    error.alias_facts = dict(entry=budget['entries'],
                        kind={tarfile.REGTYPE: 'regular', tarfile.AREGTYPE: 'regular', tarfile.DIRTYPE: 'directory',
                              tarfile.SYMTYPE: 'symlink', tarfile.LNKTYPE: 'hardlink'}.get(member.type, 'other'),
                        name=s.critical_summary(member.name), target=s.critical_summary(member.linkname))
                    raise
        while reader.read(s.MIB):
            pass
        s.require(reader.sha.hexdigest() == expected, 'diff-id-mismatch')
    # No staging record escapes the function until both complete hashes verify.
    return dict(records=records, ordinals=ordinals,
                metadata=dict(compressed=compressed, diff_id=expected, compressed_verified=True, diff_verified=True),
                scan=dict(entries=len(records), expanded_bytes=reader.size, diff_id_verified=True))


def approved_profile(layer, ordinal):
    s.require(type(ordinal) is int and ordinal == 0 and layer['metadata']['compressed_verified'] is True
              and layer['metadata']['diff_verified'] is True, 'alias-profile')
    found = [(i, row) for i, row in enumerate(layer['records']) if row['path'] == 'lib64']
    s.require(len(found) == 1 and layer['ordinals'].get('lib64') == 659, 'alias-profile')
    index, row = found[0]
    s.require(row['kind'] == 'symlink' and row['target'] == 'usr/lib64', 'alias-profile')
    return [dict(path='lib64', target='usr/lib64', layer=0, entry=index)]
