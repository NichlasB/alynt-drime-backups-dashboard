"""Bounded ELF/verified-namespace DATA only. No IO, loader or execution grant."""
import hashlib
import json
import re
import struct
import time

MIB = 1024*1024
SERVER = 'usr/sbin/mariadbd'
LOADER = 'usr/lib64/ld-linux-x86-64.so.2'
INTERPRETER = '/lib64/ld-linux-x86-64.so.2'
ROOT_HASHES = {SERVER: '910b30f53e2c9812e7b3cdc37f40df2ca867738eb79ed799fc056c15774f5f19',
               LOADER: '73983b1895c124baa82e5f1c9c26ba0717f1440dff97a217fbb20ddbed8a3751'}
FEATURE_TAGS = {0x6ffffefa: 'config', 0x6ffffefb: 'depaudit', 0x6ffffefc: 'audit', 0x7ffffffd: 'auxiliary', 0x7fffffff: 'filter'}


class Refusal(Exception):
    pass


def require(ok, code):
    if not ok:
        raise Refusal(code)


def library(value):
    return type(value) is str and len(value) <= 128 and re.fullmatch(r'(?:lib[A-Za-z0-9_.+-]+|ld-linux-x86-64)\.so(?:\.[A-Za-z0-9_.+-]+)*', value) is not None


def eligible(path):
    return path in (SERVER, LOADER) or (path.startswith('usr/lib64/') and library(path[10:]))


def parse(data, deadline=None):
    require(type(data) in (bytes, bytearray) and 64 <= len(data) <= 128*MIB and data[:7] == b'\x7fELF\x02\x01\x01', 'elf-header')
    require(deadline is None or type(deadline) in (int, float) and 0 <= deadline < float('inf'), 'elf-deadline')
    kind, machine, version = struct.unpack_from('<HHI', data, 16)
    offset = struct.unpack_from('<Q', data, 32)[0]
    header, width, count = struct.unpack_from('<HHH', data, 52)
    require(kind in (2, 3) and machine == 62 and version == 1 and header == 64 and width == 56
            and 0 < count <= 128 and offset >= 64 and offset+width*count <= len(data), 'elf-program-table')
    work = 0
    def tick():
        nonlocal work
        work += 1
        require(work <= 2097152 and (deadline is None or time.monotonic() < deadline), 'elf-work-bound')
    def ascii_text(raw):
        require(0 < len(raw) <= 240 and all(32 <= c <= 126 for c in raw), 'elf-string-ascii')
        return raw.decode('ascii')
    loads, dynamic, interpreter = [], None, None
    for i in range(count):
        tick()
        typ, flags, start, address, physical, size, memory, align = struct.unpack_from('<IIQQQQQQ', data, offset+i*width)
        require(start+size <= len(data), 'elf-segment-bound')
        if typ == 1:
            require(size <= memory and (align in (0, 1) or align & (align-1) == 0 and address % align == start % align), 'elf-load-shape')
            require(not loads or address >= loads[-1][0], 'elf-load-order')
            loads.append((address, start, size))
        elif typ == 2:
            require(dynamic is None and 0 < size <= MIB and size % 16 == 0, 'elf-dynamic-bound')
            dynamic = (start, size, address)
        elif typ == 3:
            require(interpreter is None and 1 < size <= 240 and data[start+size-1] == 0
                    and 0 not in data[start:start+size-1], 'elf-interpreter')
            interpreter = ascii_text(data[start:start+size-1])
    require(bool(loads) and dynamic is not None, 'elf-dynamic-required')
    start, size, address = dynamic
    require(sum(1 for va, off, length in loads if off <= start and start+size <= off+length and va+start-off == address) == 1, 'elf-dynamic-mapping')
    tags, needed, paths, features, flag_tags, ended = {}, [], [], set(), set(), False
    for pos in range(start, start+size, 16):
        tick(); tag, value = struct.unpack_from('<qQ', data, pos)
        if tag == 0:
            ended = True; break
        if tag in FEATURE_TAGS: features.add(FEATURE_TAGS[tag])
        if tag in (30, 0x6ffffffb):
            require(tag not in flag_tags, 'elf-duplicate-tag'); flag_tags.add(tag)
        if tag == 30 and value & 1: features.add('origin-flag')
        if tag == 0x6ffffffb and value & 0x21002890: features.add('dynamic-search-flag')
        if tag == 1:
            needed.append(value)
        elif tag in (15, 29):
            paths.append(value)
        elif tag in (5, 10, 14):
            require(tag not in tags, 'elf-duplicate-tag'); tags[tag] = value
        require(len(needed) <= 64 and len(paths) <= 2, 'elf-dynamic-shape')
    require(ended, 'elf-dynamic-terminator')
    strings = b''
    if needed or paths or 14 in tags:
        require(5 in tags and 10 in tags and 0 < tags[10] <= MIB, 'elf-string-table')
        mapped = [off+tags[5]-va for va, off, length in loads if va <= tags[5] and tags[5]+tags[10] <= va+length]
        require(len(mapped) == 1, 'elf-string-mapping')
        strings = data[mapped[0]:mapped[0]+tags[10]]
    def text(index, empty=False):
        tick(); require(index < len(strings), 'elf-string-index')
        end = strings.find(b'\0', index, index+241)
        require(end >= index, 'elf-string-length')
        return '' if empty and end == index else ascii_text(strings[index:end])
    result = dict(machine='x86_64', bits=64, type=kind, interpreter=interpreter,
                  needed=[text(i) for i in needed], soname=text(tags[14]) if 14 in tags else None,
                  search_paths=[text(i, True) for i in paths], sha256=hashlib.sha256(data).hexdigest(), bytes=len(data),
                  dependency_closure_verified=False, executed=False, work=work, unsupported_features=sorted(features))
    require(all(library(n) for n in result['needed']) and len(set(result['needed'])) == len(result['needed'])
            and (result['soname'] is None or library(result['soname'])), 'elf-library-name')
    return result


def admit_selected(roles):
    require(type(roles) is dict and set(roles) == {SERVER, INTERPRETER[1:]}, 'elf-selected-identity')
    for path, canonical in ((SERVER, SERVER), (INTERPRETER[1:], LOADER)):
        row = roles[path]
        require(type(row) is dict and type(row.get('canonical')) is str and row['canonical'] == canonical
                and type(row.get('sha256')) is str and row['sha256'] == ROOT_HASHES[canonical], 'elf-selected-identity')


def clock(deadline):
    require(type(deadline) in (int, float) and 0 <= deadline < float('inf') and time.monotonic() < deadline, 'elf-deadline')
    return int((time.monotonic()-deadline+300)*1000)


def provider(tree, name, deadline):
    require(library(name), 'elf-library-name')
    path = 'usr/lib64/'+name
    row = tree.inspect(path, clock(deadline)); edges = []
    if row['kind'] == 'symlink':
        target = row['target']; stem = name.split('.so', 1)[0]+'.so.'
        require(library(target) and target.startswith(stem) and target != name
                and re.fullmatch(r'[0-9]+(?:\.[0-9]+)*', target[len(stem):]), 'elf-provider-alias')
        edges.append(dict(path=path, target=target, layer=row['layer'], entry=row['entry']))
        path = 'usr/lib64/'+target; row = tree.inspect(path, clock(deadline))
    require(row['kind'] == 'file' and not row['mode'] & 0o6000 and len(edges)+len(row.get('hardlinks', [])) <= 16, 'elf-provider-target')
    return path, row, edges


def metadata(row):
    fields = {'machine','bits','type','interpreter','needed','soname','search_paths','sha256','bytes','dependency_closure_verified','executed','work','unsupported_features'}
    require(type(row) is dict and all(type(k) is str for k in row) and set(row) == fields, 'elf-metadata')
    require(type(row['machine']) is str and row['machine'] == 'x86_64' and type(row['bits']) is int and row['bits'] == 64
            and type(row['type']) is int and row['type'] in (2,3) and type(row['bytes']) is int and 64 <= row['bytes'] <= 128*MIB
            and type(row['sha256']) is str and re.fullmatch('[a-f0-9]{64}', row['sha256'])
            and type(row['work']) is int and 0 < row['work'] <= 2097152
            and row['executed'] is False and row['dependency_closure_verified'] is False, 'elf-metadata')
    def text(value): return type(value) is str and len(value) <= 240 and all(32 <= ord(c) <= 126 for c in value)
    require(row['interpreter'] is None or text(row['interpreter']), 'elf-metadata')
    require(type(row['unsupported_features']) is list and len(row['unsupported_features']) <= 7
            and all(type(f) is str and f in set(FEATURE_TAGS.values()) | {'origin-flag','dynamic-search-flag'} for f in row['unsupported_features'])
            and len(set(row['unsupported_features'])) == len(row['unsupported_features']), 'elf-metadata')
    require(type(row['needed']) is list and len(row['needed']) <= 64 and all(library(n) for n in row['needed'])
            and len(set(row['needed'])) == len(row['needed']) and (row['soname'] is None or library(row['soname']))
            and type(row['search_paths']) is list and len(row['search_paths']) <= 2 and all(text(p) for p in row['search_paths']), 'elf-metadata')
    return row


def summary(row):
    return dict(sha256=row['sha256'], bytes=row['bytes'], type=row['type'], soname=row['soname'], needed=list(row['needed']), unsupported_features=list(row['unsupported_features']),
                interpreter_sha256=hashlib.sha256(row['interpreter'].encode('ascii')).hexdigest() if row['interpreter'] else None,
                search_path_count=len(row['search_paths']), search_path_hashes=[hashlib.sha256(p.encode('ascii')).hexdigest() for p in row['search_paths']])


def bound_metadata(node, observations, path):
    require(node['kind'] == 'file' and not node['mode'] & 0o6000, 'elf-selected-file')
    origin = node.get('content_origin', dict(path=path, layer=node['layer'], entry=node['entry'], sha256=node['sha256'], size=node['size']))
    require(eligible(origin['path']), 'elf-origin-domain')
    key = str(origin['layer'])+':'+str(origin['entry'])
    require(key in observations, 'elf-observation-missing')
    found = metadata(observations[key])
    require(found['sha256'] == node['sha256'] == origin['sha256'] and found['bytes'] == node['size'] == origin['size'], 'elf-content-origin')
    return found


def object_policy(path, found):
    require(not found['unsupported_features'], 'elf-dependency-feature-policy')
    require(not found['search_paths'], 'elf-search-policy')
    require(found['interpreter'] in (None, INTERPRETER), 'elf-interpreter-policy')
    if path == SERVER: require(found['interpreter'] == INTERPRETER, 'elf-interpreter-policy')
    if path == LOADER: require(found['interpreter'] is None and found['type'] == 3, 'elf-interpreter-policy')
    return dict(path=path, sha256=found['sha256'], interpreter='/'+LOADER if found['interpreter'] else None,
                needed=list(found['needed']), search_paths=list(found['search_paths']))


def capture_failure(error, stage, current, requested, roots):
    if 'elf_facts' in error.__dict__: return
    facts = dict(format='ADBUELF1', stage=stage,
                 path_sha256=hashlib.sha256(current.encode('utf-8')).hexdigest() if current is not None else None,
                 needed_name=requested, roots=roots, runtime_grant=False)
    error.elf_facts = facts if len(json.dumps(facts, ensure_ascii=True).encode('ascii')) <= 32768 else dict(format='ADBUELF1', withheld=True, runtime_grant=False)


def graph(tree, observations, deadline):
    require(type(observations) is dict and len(observations) <= 256 and all(type(k) is str and re.fullmatch('[0-9]{1,2}:[0-9]{1,5}', k) for k in observations), 'elf-observation-bound')
    roots, cache, providers, alias_edges, objects = {}, {}, {}, [], {}
    current, requested, stage = SERVER, None, 'root-origins'
    def descriptor(path):
        node = tree.inspect(path, clock(deadline))
        return bound_metadata(node, observations, path), node['snapshot']
    try:
        snapshot = None
        for path in (SERVER, LOADER):
            current = path; found, observed_snapshot = descriptor(path)
            require(snapshot is None or snapshot == observed_snapshot, 'elf-snapshot-binding')
            snapshot = observed_snapshot; cache[path] = found; roots[path] = summary(found)
        queue, queued, edges = [SERVER, LOADER], {SERVER, LOADER}, 0
        stage = 'dependency-graph'
        while queue:
            current = queue.pop(0); requested = None; found = cache[current]
            edges += len(found['needed'])+(found['interpreter'] is not None)
            require(edges <= 4096 and len(queued) <= 256, 'elf-graph-bound')
            tree.charge(len(found['needed'])+1, clock(deadline))
            objects[current] = object_policy(current, found)
            for requested in found['needed']:
                if requested in providers: continue
                path, node, aliases = provider(tree, requested, deadline)
                require(node['snapshot'] == snapshot, 'elf-snapshot-binding')
                supplied, observed_snapshot = descriptor(path)
                require(observed_snapshot == snapshot, 'elf-snapshot-binding')
                require(supplied['soname'] == requested and supplied['type'] == 3, 'elf-provider-soname')
                providers[requested] = dict(name=requested, path=path, sha256=supplied['sha256'])
                alias_edges.extend(aliases)
                if path not in queued:
                    require(len(queued) < 256, 'elf-graph-bound'); queued.add(path); queue.append(path); cache[path] = supplied
        stage, current, requested = 'graph-validation', None, None
        result = tree.closure(list(objects.values()), list(providers.values()), clock(deadline))
        result.update(static_dependency_closure_verified=True, relocations_verified=False, provider_alias_count=len(alias_edges), roots=roots,
            graph_sha256=hashlib.sha256(json.dumps([snapshot, objects, providers, alias_edges], sort_keys=True).encode()).hexdigest())
        return result
    except Exception as error:
        try: capture_failure(error, stage, current, requested, roots)
        except Exception: error.__dict__.setdefault('elf_facts', None)
        raise
