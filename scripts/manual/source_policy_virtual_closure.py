"""Primitive-DATA namespace model. No IO, default aliases or execution."""
import hashlib
import functools
import json
import re
import time
import unicodedata

WORK_LIMIT = 2097152


class Refusal(Exception):
    pass


def require(ok, reason):
    if not ok:
        raise Refusal(reason)


def sha(value):
    require(type(value) is str and re.fullmatch('[a-f0-9]{64}', value), 'digest')
    return value


def shape(value, fields):
    return type(value) is dict and all(type(k) is str for k in value) and set(value) == set(fields)


def text(value):
    require(type(value) is str and 0 < len(value) <= 240, 'text-bound')
    try:
        encoded = value.encode('utf-8', 'strict')
    except UnicodeError:
        raise Refusal('utf8') from None
    require(unicodedata.unidata_version == '15.0.0' and len(encoded) <= 240
            and not any(unicodedata.category(c) in ('Cc', 'Cf', 'Cs', 'Co', 'Cn', 'Zl', 'Zp') for c in value), 'text-domain')
    return value


def key(value):
    text(value)
    require(not value.startswith('/') and '\\' not in value and len(value.split('/')) <= 32
            and all(p not in ('', '.', '..') for p in value.split('/')), 'canonical-key')
    return value


def target(parent, literal):
    text(literal)
    require('\\' not in literal and ':' not in literal and '//' not in literal, 'target-domain')
    parts = [] if literal.startswith('/') else parent.split('/') if parent else []
    for item in literal.lstrip('/').split('/'):
        if item == '.':
            continue
        if item == '..':
            require(bool(parts), 'root-escape')
            parts.pop()
        else:
            require(bool(item), 'target-empty')
            parts.append(item)
    return key('/'.join(parts))


def real_aliases():
    return ()  # Never populated from a candidate, receipt or caller.


def _guard_owner(method):
    @functools.wraps(method)
    def guarded(self, *args, **kwargs):
        try:
            return method(self, *args, **kwargs)
        except Exception as error:
            self.fail(error)
            raise
    return guarded


def _guard_model(method):
    @functools.wraps(method)
    def guarded(self, *args, **kwargs):
        try:
            return method(self, *args, **kwargs)
        except Exception as error:
            if self._work_owner is not None: self._work_owner.fail(error)
            raise
    return guarded


def _data_hash(value):
    return hashlib.sha256(json.dumps(value, sort_keys=True, ensure_ascii=True).encode('ascii')).hexdigest()


class _WorkGrant:
    __slots__ = ()  # Identity only. Caller-writable attributes cannot alter the allowance.


class _FixedWorkAuthority:
    """Dormant synchronous DATA owner, not a sandbox or candidate attestation."""
    def __init__(self, identity, layers, run, deadline):
        require(shape(identity, ('manifest', 'config', 'source')), 'work-owner-identity')
        self._identity = {k: sha(identity[k]) for k in sorted(identity)}
        require(type(layers) in (list, tuple) and 0 < len(layers) <= 10, 'work-owner-layers')
        self._layers = []
        for pair in layers:
            require(type(pair) in (list, tuple) and len(pair) == 2, 'work-owner-layer')
            self._layers.append((sha(pair[0]), sha(pair[1])))
        require(shape(run, ('head', 'run', 'profile')) and type(run['head']) is str
                and re.fullmatch('[a-f0-9]{40}', run['head']) and type(run['run']) is str
                and re.fullmatch('[a-f0-9]{32}', run['run']) and type(run['profile']) is str
                and run['profile'] == 'bounded-strings-v1', 'work-owner-run')
        require(type(deadline) in (int, float) and time.monotonic() < deadline <= time.monotonic()+300,
                'work-owner-deadline')
        self._run, self._deadline = dict(run), deadline
        self._owner_hash = _data_hash([self._identity, self._layers, self._run, deadline])
        self._used = self._parser_work = self._model_work = self._burned = 0
        self._issued = self._settled = self._last = self._verified = self._bytes = 0
        self._phase, self._pending, self._model, self._active, self._aliases = 'ready', None, None, None, None
        self._records, self._indices, self._archive = {}, {}, 0

    @property
    def deadline(self):
        return self._deadline

    def _clock(self, now=None):
        require(self._phase == 'ready' and time.monotonic() < self.deadline, 'work-owner-deadline')
        tick = int((time.monotonic()-self.deadline+300)*1000) if now is None else now
        require(type(tick) is int and self._last <= tick < 300000, 'work-owner-clock')
        self._last = tick
        return tick

    @_guard_owner
    def now(self):
        return self._clock()

    @_guard_owner
    def begin_layer(self, ordinal, compressed, diff_id, deadline):
        self._clock()
        require(self._pending is None and self._active is None and type(ordinal) is int
                and ordinal == self._verified < len(self._layers) and deadline == self.deadline
                and (sha(compressed), sha(diff_id)) == self._layers[ordinal]
                and (ordinal == 0 or self._model is not None and self._model._committed == ordinal), 'work-owner-layer')
        self._active = ordinal

    @_guard_owner
    def verify_layer(self, metadata, aliases=None):
        self._clock()
        require(self._active is not None and self._pending is None
                and shape(metadata, ('compressed', 'diff_id', 'compressed_verified', 'diff_verified'))
                and (sha(metadata['compressed']), sha(metadata['diff_id'])) == self._layers[self._active]
                and metadata['compressed_verified'] is True and metadata['diff_verified'] is True, 'work-owner-layer')
        if self._active == 0:
            require(type(aliases) is list and len(aliases) == 1
                    and shape(aliases[0], ('path', 'target', 'layer', 'entry'))
                    and aliases[0]['path'] == 'lib64' and aliases[0]['target'] == 'usr/lib64'
                    and type(aliases[0]['layer']) is int and aliases[0]['layer'] == 0
                    and type(aliases[0]['entry']) is int and 0 <= aliases[0]['entry'] < 65536, 'work-owner-alias')
            self._aliases = [dict(aliases[0])]
        else: require(aliases is None, 'work-owner-alias')
        self._verified += 1; self._active = None

    def _context(self, value):
        require(shape(value, ('layer', 'compressed', 'diff_id', 'entry', 'archive', 'path_sha256', 'sha256', 'bytes')),
                'work-owner-context')
        require(type(value['layer']) is int and 0 <= value['layer'] < len(self._layers)
                and (sha(value['compressed']), sha(value['diff_id'])) == self._layers[value['layer']]
                and type(value['entry']) is int and 0 <= value['entry'] < 65536
                and type(value['archive']) is int and 1 <= value['archive'] <= 65536
                and type(value['bytes']) is int and 0 < value['bytes'] <= 134217728, 'work-owner-context')
        sha(value['path_sha256']); sha(value['sha256'])
        return dict(value)

    @_guard_owner
    def issue(self, context):
        self._clock(); context = self._context(context)
        require(self._pending is None and self._active == context['layer'] and self._issued < 256
                and context['entry'] > self._indices.get(context['layer'], -1)
                and context['archive'] > self._archive, 'work-owner-grant')
        require(WORK_LIMIT-self._used >= 2, 'work-owner-budget')
        grant = WORK_LIMIT-self._used-2
        self._used += 1; self._issued += 1
        token = _WorkGrant()
        self._pending = dict(token=token, context=context, granted=grant, invoked=False)
        self._indices[context['layer']] = context['entry']; self._archive = context['archive']
        return token, grant

    def _match(self, token, context):
        require(self._pending is not None and token is self._pending['token']
                and self._context(context) == self._pending['context'], 'work-owner-ticket')

    @_guard_owner
    def invoke(self, token, context):
        self._clock(); self._match(token, context)
        require(not self._pending['invoked'], 'work-owner-invocation')
        self._pending['invoked'] = True

    @staticmethod
    def _cost(value):
        # Independently validate scalar consumption, never trust a receipt as authority.
        counts = ('structural', 'mapping', 'search_bytes', 'copy_bytes', 'ascii_bytes', 'decode_bytes', 'boundary_bytes')
        require(shape(value, (*counts, 'format', 'complete', 'granted', 'used', 'requests', 'extent', 'runtime_grant'))
                and type(value['format']) is str and value['format'] == 'ADBUELFCOST1'
                and type(value['complete']) is bool and value['runtime_grant'] is False, 'work-owner-cost')
        require(all(type(value[k]) is int and 0 <= value[k] <= WORK_LIMIT for k in (*counts, 'granted', 'used'))
                and type(value['requests']) is int and 0 <= value['requests'] <= 67
                and type(value['extent']) is int and 0 <= value['extent'] <= 134217728, 'work-owner-cost')
        require(value['used'] == sum(value[k] for k in counts) <= value['granted']
                and value['mapping'] <= 256 and value['boundary_bytes'] <= 2 and value['structural'] >= value['requests']
                and value['search_bytes'] <= 241*value['requests']
                and value['copy_bytes'] == value['ascii_bytes'] == value['decode_bytes'] <= min(240*value['requests'], value['search_bytes'])
                and (not value['requests'] or value['extent'] > 0 and value['boundary_bytes'] == 2)
                and (not value['complete'] or bool(value['requests']) == bool(value['extent'])), 'work-owner-cost')
        return dict(value)

    @_guard_owner
    def settle(self, token, context, cost, summary, metadata_digest, success):
        self._match(token, context)
        require(self._phase == 'ready' and self._pending['invoked'] and type(success) is bool, 'work-owner-settlement')
        cost = self._cost(cost)
        require(cost['granted'] == self._pending['granted'] and cost['complete'] is success, 'work-owner-cost')
        if success:
            require(shape(summary, ('sha256', 'bytes', 'work')) and sha(summary['sha256']) == context['sha256']
                    and type(summary['bytes']) is int and summary['bytes'] == context['bytes']
                    and type(summary['work']) is int and summary['work'] == cost['used'], 'work-owner-origin')
            sha(metadata_digest)
        else: require(summary is None and metadata_digest is None, 'work-owner-failure')
        self._used += cost['used']+1; self._parser_work += cost['used']; self._settled += 1
        self._pending = None  # Unknown consumption burns; known consumption survives later failure.
        self._clock()
        if not success:
            self.poison(); return None
        binding = dict(format='ADBUOWN1', owner=self._owner_hash, sequence=self._settled,
                       context=dict(context), cost=cost, metadata_sha256=metadata_digest)
        origin = {k: context[k] for k in ('layer', 'entry', 'path_sha256', 'sha256', 'bytes')}
        self._records[(context['layer'], context['entry'])] = dict(digest=_data_hash(binding), origin=_data_hash(origin), retained=False)
        return binding

    def _binding(self, binding):
        require(shape(binding, ('format', 'owner', 'sequence', 'context', 'cost', 'metadata_sha256'))
                and type(binding['format']) is str and binding['format'] == 'ADBUOWN1'
                and sha(binding['owner']) == self._owner_hash and type(binding['sequence']) is int
                and 1 <= binding['sequence'] <= self._settled, 'work-owner-binding')
        context = self._context(binding['context']); self._cost(binding['cost']); sha(binding['metadata_sha256'])
        record = self._records.get((context['layer'], context['entry']))
        require(record is not None and record['digest'] == _data_hash(binding), 'work-owner-binding')
        return record

    @_guard_owner
    def retain(self, binding, size):
        self._clock(); record = self._binding(binding)
        require(not record['retained'] and type(size) is int and 0 < size <= 2097152-self._bytes, 'work-owner-observation-bound')
        self._bytes += size; record['retained'] = True

    @_guard_owner
    def bind_model(self, model):
        self._clock()
        require(type(model) is VirtualClosure and self._model is None and self._verified == 1
                and self._active is None and self._pending is None and model._identity == self._identity
                and model._layers == self._layers and list(model._aliases.values()) == self._aliases
                and model._hardlinks, 'work-owner-model')
        self._model = model

    @_guard_owner
    def debit_model(self, model, now, cost):
        self._clock(now)
        require(model is self._model and self._pending is None and self._active is None, 'work-owner-exclusive')
        require(type(cost) is int and 0 <= cost <= WORK_LIMIT-self._used, 'work-owner-budget')
        self._used += cost; self._model_work += cost

    @_guard_owner
    def admit_apply(self, model, metadata):
        require(model is self._model and model._committed < self._verified, 'work-owner-layer-unverified')

    @_guard_owner
    def admit_graph(self, model):
        self._clock()
        require(model is self._model and model._final and self._active is None and self._pending is None
                and self._verified == model._committed == len(self._layers), 'work-owner-graph')

    @_guard_owner
    def check_observation(self, layer, index, binding, metadata_digest):
        self._clock(); record = self._binding(binding)
        require(record['retained'] and type(layer) is int and type(index) is int
                and (layer, index) == (binding['context']['layer'], binding['context']['entry'])
                and sha(metadata_digest) == binding['metadata_sha256'], 'work-owner-origin')

    @_guard_owner
    def check_node(self, node, path):
        origin = node.get('content_origin', dict(path=path, layer=node['layer'], entry=node['entry'],
                                                sha256=node['sha256'], size=node['size']))
        record = self._records.get((origin['layer'], origin['entry']))
        facts = dict(layer=origin['layer'], entry=origin['entry'], path_sha256=hashlib.sha256(origin['path'].encode('utf-8')).hexdigest(),
                     sha256=origin['sha256'], bytes=origin['size'])
        require(record is not None and record['retained'] and record['origin'] == _data_hash(facts)
                and origin['sha256'] == node['sha256'] and origin['size'] == node['size'], 'work-owner-final-origin')

    @_guard_owner
    def close_model(self, model):
        self.admit_graph(model); self._phase = 'closed-model'

    def poison(self):
        if self._pending is not None:
            burn = self._pending['granted']+1
            self._used += burn; self._burned += burn; self._pending = None
        self._phase = 'poisoned'

    def fail(self, error):
        self.poison()
        try:
            error.fixed_owner_facts = self.snapshot()
        except Exception:
            self.poison()  # Diagnostics never replace the first error or reopen the owner.

    def snapshot(self):
        return dict(work_used=self._used, parser_work=self._parser_work, model_work=self._model_work,
                    burned_work=self._burned, grants=self._issued, settled=self._settled,
                    verified_layers=self._verified, records=len(self._records), observation_bytes=self._bytes,
                    state=self._phase, model_bound=self._model is not None, runtime_grant=False)


class VirtualClosure:
    @_guard_model
    def __init__(self, identity, layers, aliases=(), hardlinks=False, *, work_owner=None):
        self._work_owner = None
        require(work_owner is None or type(work_owner) is _FixedWorkAuthority, 'work-owner-type')
        self._work_owner = work_owner
        require(type(hardlinks) is bool, 'hardlink-policy')
        self._hardlinks = hardlinks
        require(shape(identity, ('manifest', 'config', 'source')), 'identity')
        self._identity = {k: sha(identity[k]) for k in sorted(identity)}
        require(type(layers) in (list, tuple) and 0 < len(layers) <= 10, 'layers')
        self._layers = []
        for pair in layers:
            require(type(pair) in (list, tuple) and len(pair) == 2, 'layer-pair')
            self._layers.append((sha(pair[0]), sha(pair[1])))
        require(type(aliases) in (list, tuple) and len(aliases) <= 256, 'aliases')
        self._aliases = {}
        for edge in aliases:
            require(shape(edge, ('path', 'target', 'layer', 'entry')), 'alias-shape')
            name = key(edge['path']); text(edge['target'])
            require(name not in self._aliases and type(edge['layer']) is int and 0 <= edge['layer'] < 10
                    and type(edge['entry']) is int and 0 <= edge['entry'] < 65536, 'alias-origin')
            self._aliases[name] = dict(edge)
        self._nodes, self._committed, self._entries = {}, 0, 0
        self._hardlink_edges = 0
        self._last, self._work, self._roles, self._final, self._snapshot_hash = 0, 0, 0, False, None
        if work_owner is not None: work_owner.bind_model(self)

    def _tick(self, now, cost=1):
        if self._work_owner is not None:
            self._work_owner.debit_model(self, now, cost)
            return
        require(type(now) is int and self._last <= now < 300000 and self._work + cost <= WORK_LIMIT, 'work-budget')
        self._last = now
        self._work += cost

    def _record(self, row, index, ordinal):
        require(shape(row, ('path', 'kind', 'mode', 'uid', 'gid', 'sha256', 'size', 'target')), 'record-shape')
        name = key(row['path'])
        require(type(row['kind']) is str and (row['kind'] in ('directory', 'file', 'symlink', 'whiteout', 'opaque')
                or self._hardlinks and row['kind'] == 'hardlink'), 'record-type')
        require(type(row['mode']) is int and 0 <= row['mode'] <= 0o7777
                and all(type(row[k]) is int and 0 <= row[k] <= 4294967295 for k in ('uid', 'gid'))
                and type(row['size']) is int and 0 <= row['size'] <= 134217728, 'record-metadata')
        if row['kind'] == 'file':
            sha(row['sha256'])
        else:
            require(row['sha256'] is None and row['size'] == 0, 'non-file-content')
        if row['kind'] in ('symlink', 'hardlink'):
            text(row['target'])
        else:
            require(row['target'] is None, 'non-link-target')
        leaf = name.rsplit('/', 1)[-1]
        if row['kind'] == 'whiteout':
            require(leaf.startswith('.wh.') and leaf not in ('.wh.', '.wh..wh..opq'), 'whiteout')
        elif row['kind'] == 'opaque':
            require(leaf == '.wh..wh..opq', 'opaque')
        else:
            require(not leaf.startswith('.wh.'), 'marker-as-file')
        return dict(row, layer=ordinal, entry=index)

    def _bind_hardlink(self, nodes, name, row, now):
        link = key(row['target']); parts = link.split('/')
        require(':' not in link and parts[0] not in ('proc', 'sys', 'dev')
                and link != name and not link.startswith(name+'/'), 'hardlink-target-domain')
        for i in range(1, len(parts)):
            self._tick(now)
            parent = nodes.get('/'.join(parts[:i]))
            require(parent is not None and parent['kind'] == 'directory', 'hardlink-target-parent')
        source = nodes.get(link)
        require(source is not None and source['kind'] == 'file', 'hardlink-target-regular')
        require(not row['mode'] & 0o6000 and all(row[k] == source[k] for k in ('mode', 'uid', 'gid')), 'hardlink-content-metadata')
        history = source.get('hardlinks', [])
        require(len(history) < 16 and not any(name in (edge['path'], edge['target']) for edge in history), 'hardlink-budget-cycle')
        require(self._hardlink_edges + len(history)+1 <= 65536, 'hardlink-history-budget')
        if self._work_owner is not None: self._tick(now, len(history)+1)
        self._hardlink_edges += len(history)+1
        if self._work_owner is None: self._tick(now, len(history)+1)
        origin = dict(source.get('content_origin', dict(path=link, layer=source['layer'], entry=source['entry'],
                                                       sha256=source['sha256'], size=source['size'])))
        edges = [dict(edge) for edge in history]+[dict(path=name, target=link, layer=row['layer'], entry=row['entry'])]
        return dict(row, kind='file', sha256=source['sha256'], size=source['size'], target=None,
                    content_origin=origin, hardlinks=edges)

    def _ordered_hardlinks(self, nodes, staged, now):
        for name, row in staged.items():  # Preserve archive order for content identity.
            self._tick(now)
            if row['kind'] in ('whiteout', 'opaque'):
                continue
            if row['kind'] == 'hardlink':
                try:
                    row = self._bind_hardlink(nodes, name, row, now)
                except Refusal as error:
                    # Fixed scalar observation only; never erase or replace the original refusal.
                    try:
                        source = nodes.get(row['target'])
                        error.hardlink_facts = dict(layer=row['layer'], entry=row['entry'],
                            path_sha256=hashlib.sha256(name.encode('utf-8')).hexdigest(),
                            target_sha256=hashlib.sha256(row['target'].encode('utf-8')).hexdigest(),
                            target_kind=source['kind'] if source is not None else 'missing',
                            target_layer=source['layer'] if source is not None else None,
                            target_entry=source['entry'] if source is not None else None,
                            metadata_equal={k: source is not None and source[k] == row[k] for k in ('mode', 'uid', 'gid')})
                    except Exception:
                        error.__dict__.setdefault('hardlink_facts', None)
                    raise
            old = nodes.get(name)
            if old and not (old['kind'] == row['kind'] == 'directory'):
                for child in tuple(nodes):
                    self._tick(now)
                    if child.startswith(name+'/'):
                        del nodes[child]
            nodes[name] = row
        return nodes

    @_guard_model
    def apply(self, metadata, records, now):
        self._tick(now)
        if self._work_owner is not None: self._work_owner.admit_apply(self, metadata)
        require(not self._final and self._committed < len(self._layers), 'layer-phase')
        require(shape(metadata, ('ordinal', 'compressed', 'diff_id', 'compressed_verified', 'diff_verified')), 'layer-shape')
        sha(metadata['compressed']); sha(metadata['diff_id'])
        require(type(metadata['ordinal']) is int and metadata['ordinal'] == self._committed
                and (metadata['compressed'], metadata['diff_id']) == self._layers[self._committed]
                and metadata['compressed_verified'] is True and metadata['diff_verified'] is True, 'layer-verification')
        require(type(records) is list and len(records) + self._entries <= 65536, 'entry-budget')
        staged = {}
        for index, row in enumerate(records):
            self._tick(now)
            result = self._record(row, index, self._committed)
            require(result['path'] not in staged, 'duplicate-entry')
            staged[result['path']] = result
        nodes = dict(self._nodes)
        # Whiteouts affect only the prior layer snapshot, regardless of tar order.
        for name, row in staged.items():
            if row['kind'] in ('whiteout', 'opaque'):
                parent, _, leaf = name.rpartition('/')
                removed = parent if row['kind'] == 'opaque' else key((parent+'/' if parent else '')+leaf[4:])
                for old in tuple(nodes):
                    self._tick(now)
                    if (not removed or old.startswith(removed+'/')
                            or (row['kind'] == 'whiteout' and old == removed)):
                        del nodes[old]
        if self._hardlinks:
            nodes = self._ordered_hardlinks(nodes, staged, now)
        else:
            # Strict default: parents first, with no hardlink support.
            for name in sorted(staged, key=lambda p: (p.count('/'), p)):
                row = staged[name]
                if row['kind'] in ('whiteout', 'opaque'):
                    continue
                old = nodes.get(name)
                if old and not (old['kind'] == row['kind'] == 'directory'):
                    for child in tuple(nodes):
                        self._tick(now)
                        if child.startswith(name+'/'):
                            del nodes[child]
                nodes[name] = row
        require(len(nodes) <= 65536, 'node-budget')
        for name in nodes:
            parts = name.split('/')
            for i in range(1, len(parts)):
                self._tick(now)
                parent = nodes.get('/'.join(parts[:i]))
                require(parent is not None and parent['kind'] == 'directory', 'entry-parent-directory')
        self._nodes, self._entries = nodes, self._entries + len(records)
        self._committed += 1

    @_guard_model
    def finalize(self, now):
        self._tick(now)
        require(not self._final and self._committed == len(self._layers), 'final-phase')
        self._snapshot_hash = hashlib.sha256(json.dumps([self._identity, self._layers, self._aliases, self._hardlinks, self._nodes],
                                                       sort_keys=True, ensure_ascii=True).encode()).hexdigest()
        self._final = True
        return dict(status='namespace-consistent', snapshot=self._snapshot_hash, model_only=True, runtime_grant=False)

    @_guard_model
    def resolve(self, path, now):
        self._tick(now)
        require(self._final and self._roles < 256, 'resolve-phase')
        self._roles += 1
        pending, walked, visited, edges = key(path).split('/'), [], set(), []
        while pending:
            self._tick(now)
            walked.append(pending.pop(0)); name = '/'.join(walked)
            require(walked[0] not in ('proc', 'sys', 'dev'), 'special-root')
            row = self._nodes.get(name)
            require(row is not None, 'missing-node')
            if row['kind'] == 'symlink':
                expected = self._aliases.get(name)
                require(expected is not None and all(row[k] == expected[k] for k in ('target', 'layer', 'entry')), 'alias-authorization')
                require(name not in visited and len(edges) < 16, 'alias-budget-cycle')
                visited.add(name); edges.append(dict(expected))
                full = target('/'.join(walked[:-1]), row['target'])
                full += '/'+'/'.join(pending) if pending else ''
                pending, walked = key(full).split('/'), []
            elif pending:
                require(row['kind'] == 'directory', 'selected-parent-directory')
            else:
                require(row['kind'] == 'file' and not row['mode'] & 0o6000, 'selected-regular-file')
                require(len(edges)+len(row.get('hardlinks', [])) <= 16, 'combined-link-budget')
                result = dict(canonical=name, sha256=row['sha256'], layer=row['layer'], entry=row['entry'],
                              aliases=edges, snapshot=self._snapshot_hash, model_only=True, runtime_grant=False)
                if 'content_origin' in row:
                    origin = row['content_origin']
                    result['content_origin'] = dict((k, origin[k]) for k in ('layer', 'entry', 'sha256', 'size'))
                    result['content_origin']['path_sha256'] = hashlib.sha256(origin['path'].encode('utf-8')).hexdigest()
                    result['hardlinks'] = [dict(layer=edge['layer'], entry=edge['entry'],
                        path_sha256=hashlib.sha256(edge['path'].encode('utf-8')).hexdigest(),
                        target_sha256=hashlib.sha256(edge['target'].encode('utf-8')).hexdigest()) for edge in row['hardlinks']]
                return result
        raise Refusal('unresolved')

    @_guard_model
    def charge(self, cost, now):
        require(self._work_owner is None or self._final, 'work-owner-no-replay')
        require(type(cost) is int and 0 <= cost <= WORK_LIMIT, 'inspection-work')
        self._tick(now, cost)

    @_guard_model
    def inspect(self, path, now):
        self._tick(now)
        require(self._final, 'inspection-phase')
        parts = key(path).split('/')
        require(parts[0] not in ('proc', 'sys', 'dev'), 'special-root')
        for i in range(1, len(parts)):
            self._tick(now); parent = self._nodes.get('/'.join(parts[:i]))
            require(parent is not None and parent['kind'] == 'directory', 'inspection-parent')
        node = self._nodes.get(path)
        require(node is not None, 'missing-node')
        if self._work_owner is not None and node['kind'] == 'file': self._work_owner.check_node(node, path)
        return dict(json.loads(json.dumps(node)), snapshot=self._snapshot_hash, model_only=True, runtime_grant=False)

    @_guard_model
    def closure(self, objects, providers, now):
        self._tick(now)
        require(type(objects) is list and 0 < len(objects) <= 256 and type(providers) is list and len(providers) <= 256, 'closure-budget')
        supplied, names, graph = {}, {}, {}
        for provider in providers:
            require(shape(provider, ('name', 'path', 'sha256')), 'provider-shape')
            name = provider['name']
            require(type(name) is str and re.fullmatch('[A-Za-z0-9_.+-]{1,128}', name) and name not in names, 'provider-name')
            names[name] = (key(provider['path']), sha(provider['sha256']))
        for row in objects:
            require(shape(row, ('path', 'sha256', 'interpreter', 'needed', 'search_paths')), 'object-shape')
            path = key(row['path'])
            require(path not in supplied and type(row['search_paths']) is list and row['search_paths'] == [], 'object-search')
            require(self.resolve(path, now)['sha256'] == sha(row['sha256']), 'object-origin')
            require(type(row['needed']) is list and len(row['needed']) <= 64
                    and all(type(n) is str and re.fullmatch('[A-Za-z0-9_.+-]{1,128}', n) for n in row['needed'])
                    and len(set(row['needed'])) == len(row['needed']), 'needed-shape')
            supplied[path] = dict(row, needed=list(row['needed']))
        edges = 0
        for path, row in supplied.items():
            links = []
            if row['interpreter'] is not None:
                require(type(row['interpreter']) is str and row['interpreter'].startswith('/'), 'interpreter')
                links.append(key(row['interpreter'][1:]))
            for name in row['needed']:
                require(name in names, 'needed-provider')
                link, digest = names[name]
                require(link in supplied and supplied[link]['sha256'] == digest, 'provider-origin')
                links.append(link)
            require(all(link in supplied for link in links), 'missing-object')
            edges += len(links); self._tick(now, len(links))
            require(edges <= 4096, 'edge-budget')
            graph[path] = links
        colors = {}
        for root in graph:
            stack = [(root, False)]
            while stack:
                self._tick(now)
                path, done = stack.pop()
                if done:
                    colors[path] = 2
                    continue
                require(colors.get(path) != 1, 'dependency-cycle')
                if colors.get(path) == 2:
                    continue
                colors[path] = 1; stack.append((path, True))
                stack.extend((link, False) for link in reversed(graph[path]))
        return dict(status='dt-needed-closure', objects=len(supplied), edges=edges, snapshot=self._snapshot_hash,
                    model_only=True, runtime_grant=False, runtime_profile='unestablished')
