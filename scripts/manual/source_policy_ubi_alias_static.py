"""Separate bounded static DATA collector. No extraction or execution grant."""
import gzip
import hashlib
import importlib.util
import json
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


def collect(path, compressed, size, diff_id, budget, elf_policy=False, *, work_owner=None, layer_ordinal=None):
    s.require(work_owner is None or type(work_owner) is v._FixedWorkAuthority, 'work-owner-type')
    try:
        s.require((work_owner is None and layer_ordinal is None) or
                  (work_owner is not None and elf_policy is True and type(layer_ordinal) is int
                   and budget['deadline'] == work_owner.deadline), 'work-owner-mode')
        return _collect(path, compressed, size, diff_id, budget, elf_policy, work_owner, layer_ordinal)
    except Exception as error:
        if work_owner is not None: work_owner.fail(error)
        raise


def _collect(path, compressed, size, diff_id, budget, elf_policy, work_owner, layer_ordinal):
    s.require(type(elf_policy) is bool, 'explicit-elf-mode')
    elf = load('source_policy_ubi_elf_static.py', 'ubi_elf_parser') if elf_policy else None
    expected = s.digest(diff_id)
    s.require(type(size) is int and 0 < size <= 128 * s.MIB, 'alias-compressed-bound')
    s.digest('sha256:'+compressed)
    if work_owner is not None: work_owner.begin_layer(layer_ordinal, compressed, expected, budget['deadline'])
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
    records, ordinals, observations, bindings, elf_work = [], {}, {}, {}, 0
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
                        length, sha, body = 0, hashlib.sha256(), None
                        with archive.extractfile(member) as content:
                            while True:
                                s.require(time.monotonic() < budget['deadline'], 'scan-read-bound')
                                block = content.read(s.MIB)
                                if not block:
                                    break
                                if length == 0 and elf is not None and elf.eligible(row['path']) and (block[:4] == b'\x7fELF' or row['path'] in (elf.SERVER, elf.LOADER)):
                                    budget['elf_objects'] = budget.get('elf_objects', 0)+1
                                    s.require(budget['elf_objects'] <= 256, 'elf-object-bound')
                                    body = bytearray()
                                length += len(block); sha.update(block)
                                s.require(length <= member.size, 'critical-size')
                                if body is not None: body.extend(block)
                        s.require(length == member.size, 'critical-size')
                        row['sha256'] = sha.hexdigest()
                        if body is not None:
                            try:
                                if work_owner is None:
                                    parsed = elf.parse(body, budget['deadline'])
                                else:
                                    parsed, binding = _parse_owned(body, row, len(records), budget, work_owner, layer_ordinal, elf)
                            except elf.Refusal as error:
                                refused = s.Refusal(str(error))
                                if 'elf_string_table_facts' in error.__dict__:
                                    try: refused.elf_string_table_facts = elf.string_table_packet(error.elf_string_table_facts)
                                    except Exception: refused.elf_string_table_facts = None
                                raise refused from None
                            s.require(parsed['sha256'] == row['sha256'], 'elf-content-origin')
                            budget['elf_work'] = budget.get('elf_work', 0)+parsed['work']; elf_work += parsed['work']
                            if work_owner is None:
                                budget['elf_metadata'] = budget.get('elf_metadata', 0)+len(json.dumps(parsed, ensure_ascii=True).encode('ascii'))
                            else:
                                envelope_size = len(json.dumps(dict(metadata=parsed, binding=binding), ensure_ascii=True).encode('ascii'))
                                work_owner.retain(binding, envelope_size)
                                budget['elf_metadata'] = work_owner.snapshot()['observation_bytes']
                                bindings[len(records)] = binding
                            s.require(budget['elf_work'] <= 2097152 and budget['elf_metadata'] <= 2*s.MIB, 'elf-observation-bound')
                            observations[len(records)] = parsed
                            body = None
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
    result = dict(records=records, ordinals=ordinals,
                metadata=dict(compressed=compressed, diff_id=expected, compressed_verified=True, diff_verified=True),
                scan=dict(entries=len(records), expanded_bytes=reader.size, diff_id_verified=True))
    if elf_policy: result.update(elf=observations, elf_work=elf_work)
    if work_owner is not None:
        work_owner.verify_layer(result['metadata'], approved_profile(result, 0) if layer_ordinal == 0 else None)
        result['elf_bindings'] = bindings
    return result


def _metadata_digest(metadata):
    return hashlib.sha256(json.dumps(metadata, sort_keys=True, ensure_ascii=True).encode('ascii')).hexdigest()


def _parse_owned(body, row, index, budget, owner, ordinal, elf):
    # Source-owned frame pairs the real parser invocation with its ticket. No callback/handle enters the parser.
    context = dict(layer=ordinal, compressed=owner._layers[ordinal][0], diff_id=owner._layers[ordinal][1],
                   entry=index, archive=budget['entries'], path_sha256=hashlib.sha256(row['path'].encode('utf-8')).hexdigest(),
                   sha256=row['sha256'], bytes=row['size'])
    token, grant = owner.issue(context)
    try:
        owner.invoke(token, context)
        try:
            result = elf.parse(body, owner.deadline, bounded_strings=True, remaining_work=grant)
        except Exception as error:
            # Never replace the original parser cause with a broken diagnostic/settlement cause.
            try:
                cost = elf.access_receipt(error.__dict__.get('string_access_facts'))
                if cost is None: owner.poison()
                else: owner.settle(token, context, cost, None, None, False)
            except Exception:
                owner.poison()
            raise
        v.require(v.shape(result, ('format', 'metadata', 'cost', 'runtime_grant'))
                  and result['format'] == 'ADBUELFSA1' and result['runtime_grant'] is False, 'work-owner-result')
        metadata = elf.metadata(result['metadata'])
        cost = elf.access_receipt(result['cost'])
        v.require(cost is not None, 'work-owner-cost')
        summary = {k: metadata[k] for k in ('sha256', 'bytes', 'work')}
        binding = owner.settle(token, context, cost, summary, _metadata_digest(metadata), True)
        return metadata, binding
    except Exception as error:
        owner.fail(error)
        raise


def _owned_model(owner, first):
    v.require(type(owner) is v._FixedWorkAuthority, 'work-owner-type')
    try:
        return v.VirtualClosure(owner._identity, owner._layers, approved_profile(first, 0), hardlinks=True, work_owner=owner)
    except Exception as error:
        owner.fail(error)
        raise


def _owned_graph(tree, owner, layers):
    v.require(type(owner) is v._FixedWorkAuthority, 'work-owner-type')
    elf = None
    try:
        owner.admit_graph(tree)
        v.require(type(layers) is list and len(layers) == len(owner._layers), 'work-owner-graph-layers')
        elf = load('source_policy_ubi_elf_static.py', 'ubi_owned_graph')
        observations = {}
        seen = set()
        for ordinal, layer in enumerate(layers):
            v.require(type(layer) is dict and type(layer.get('elf')) is dict and type(layer.get('elf_bindings')) is dict
                      and len(layer['elf']) <= 256 and set(layer['elf']) == set(layer['elf_bindings']), 'work-owner-observations')
            v.require(v.shape(layer.get('metadata'), ('compressed', 'diff_id', 'compressed_verified', 'diff_verified'))
                      and (layer['metadata']['compressed'], layer['metadata']['diff_id']) == owner._layers[ordinal]
                      and layer['metadata']['compressed_verified'] is True and layer['metadata']['diff_verified'] is True,
                      'work-owner-graph-layer')
            for index, raw in layer['elf'].items():
                v.require(type(index) is int and 0 <= index < 65536, 'work-owner-observation-index')
                metadata = elf.metadata(raw)
                owner.check_observation(ordinal, index, layer['elf_bindings'][index], _metadata_digest(metadata))
                observations[str(ordinal)+':'+str(index)] = metadata; seen.add((ordinal, index))
        v.require(seen == set(owner._records), 'work-owner-observation-omitted')
        result = elf.graph(tree, observations, owner.deadline)
        owner.close_model(tree)
        return dict(result, work_owner=owner.snapshot())
    except Exception as error:
        owner.fail(error)
        if elf is not None and type(error) is elf.Refusal:
            refused = s.Refusal(str(error))
            try: refused.elf_facts = elf.failure_packet(error.__dict__.get('elf_facts'))
            except Exception: refused.elf_facts = None
            if 'provider_alias_facts' in error.__dict__:
                try: refused.provider_alias_facts = elf.provider_alias_packet(error.provider_alias_facts)
                except Exception: refused.provider_alias_facts = None
            owner.fail(refused)
            raise refused from error  # Exact helper-module type, finite primary cause retained.
        raise


def approved_profile(layer, ordinal):
    s.require(type(ordinal) is int and ordinal == 0 and layer['metadata']['compressed_verified'] is True
              and layer['metadata']['diff_verified'] is True, 'alias-profile')
    found = [(i, row) for i, row in enumerate(layer['records']) if row['path'] == 'lib64']
    s.require(len(found) == 1 and layer['ordinals'].get('lib64') == 659, 'alias-profile')
    index, row = found[0]
    s.require(row['kind'] == 'symlink' and row['target'] == 'usr/lib64', 'alias-profile')
    return [dict(path='lib64', target='usr/lib64', layer=0, entry=index)]
