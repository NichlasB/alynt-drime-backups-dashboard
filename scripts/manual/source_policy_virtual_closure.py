"""Dormant primitive-DATA namespace model. No IO, real alias policy or execution."""
import hashlib
import json
import re
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


class VirtualClosure:
    def __init__(self, identity, layers, aliases=()):
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
        self._last, self._work, self._roles, self._final, self._snapshot_hash = 0, 0, 0, False, None

    def _tick(self, now, cost=1):
        require(type(now) is int and self._last <= now < 300000 and self._work + cost <= WORK_LIMIT, 'work-budget')
        self._last = now
        self._work += cost

    def _record(self, row, index, ordinal):
        require(shape(row, ('path', 'kind', 'mode', 'uid', 'gid', 'sha256', 'size', 'target')), 'record-shape')
        name = key(row['path'])
        require(type(row['kind']) is str and row['kind'] in ('directory', 'file', 'symlink', 'whiteout', 'opaque'), 'record-type')
        require(type(row['mode']) is int and 0 <= row['mode'] <= 0o7777
                and all(type(row[k]) is int and 0 <= row[k] <= 4294967295 for k in ('uid', 'gid'))
                and type(row['size']) is int and 0 <= row['size'] <= 134217728, 'record-metadata')
        if row['kind'] == 'file':
            sha(row['sha256'])
        else:
            require(row['sha256'] is None and row['size'] == 0, 'non-file-content')
        if row['kind'] == 'symlink':
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

    def apply(self, metadata, records, now):
        self._tick(now)
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
        # Parents first: unsupported writes through an alias are never emulated.
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

    def finalize(self, now):
        self._tick(now)
        require(not self._final and self._committed == len(self._layers), 'final-phase')
        self._snapshot_hash = hashlib.sha256(json.dumps([self._identity, self._layers, self._aliases, self._nodes],
                                                       sort_keys=True, ensure_ascii=True).encode()).hexdigest()
        self._final = True
        return dict(status='namespace-consistent', snapshot=self._snapshot_hash, model_only=True, runtime_grant=False)

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
                return dict(canonical=name, sha256=row['sha256'], layer=row['layer'], entry=row['entry'],
                            aliases=edges, snapshot=self._snapshot_hash, model_only=True, runtime_grant=False)
        raise Refusal('unresolved')

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
