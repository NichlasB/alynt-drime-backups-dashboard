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
_LIBGCC_PIN = '14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1'
_LIBGCC_CONFIG = '2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35'
_LIBGCC_DIFF = '004d2c90a65694c2830b06fddc1047d40063c6cb36fb31a5a3edfce9435326c6'
_LIBGCC_LAYERS = (
    '2920d84eafa0cf94806ab58f0a2124f7b2d35bcbb06fc89a9106dcc28efe397a',
    '46735ccec920ba5e6df52c8787af94e3d7ea0fedb92ca37f7e0279c89506e13a',
    '189b0ff547a3cff8241702bd70b6be360d4841e72a74e438b3bd60d530e43007',
    'd982fdd339edcf849f97671ec949c25b570de306244a2b6b03e6441a586ffc8a',
    '82c48ac2fca221a7aa5d650e52f057a21f73cec9e1202ff995e30276c620c276',
    '444b4ce536f188518cbcc061ab236a7e8db460af28e5f870fe200a7ba0790b6b',
    '617276d6715b91a6fedc8af16badcec2e0520cc2c04a2f3b169027a47057abfb',
    'e6a3432e4f830821a37b4186e566ae46c3d9f09844600cb3b78d469c8d7ad8d5',
    'd1540db65d371923fb149b6317345a5c5670c090f8506e30947d59463716d300',
    'c1c3acb5fb6a522a08e27e306a765c58b15407644a6d204e2c57035a840bf07a',
)


class Refusal(Exception):
    pass


def require(ok, code):
    if not ok:
        raise Refusal(code)


def library(value):
    return type(value) is str and len(value) <= 128 and re.fullmatch(r'(?:lib[A-Za-z0-9_.+-]+|ld-linux-x86-64)\.so(?:\.[A-Za-z0-9_.+-]+)*', value) is not None


def _alias_checks(name, target):
    # Diagnostic decomposition only; never used to decide provider admission.
    stem = name.split('.so', 1)[0]+'.so.'
    return dict(library_name_valid=library(target), same_family_prefix=target.startswith(stem),
                not_self=target != name, numeric_suffix=re.fullmatch(r'[0-9]+(?:\.[0-9]+)*', target[len(stem):]) is not None)


def provider_alias_packet(value):
    fields = {'format', 'reason', 'requested_name', 'provider_path_sha256', 'snapshot', 'alias_layer', 'alias_entry',
              'target_utf8_bytes', 'target_sha256', 'target_literal', 'checks', 'failed_predicates', 'diagnostic_only', 'runtime_grant'}
    if type(value) is not dict or len(value) != len(fields) or not all(type(k) is str for k in value) or set(value) != fields: return None
    if not (type(value['format']) is str and value['format'] == 'ADBUELFPA1'
            and type(value['reason']) is str and value['reason'] == 'elf-provider-alias' and library(value['requested_name'])
            and value['diagnostic_only'] is True and value['runtime_grant'] is False): return None
    if not all(type(value[k]) is str and re.fullmatch('[a-f0-9]{64}', value[k]) for k in ('snapshot', 'provider_path_sha256', 'target_sha256')): return None
    if not (type(value['alias_layer']) is int and 0 <= value['alias_layer'] < 10
            and type(value['alias_entry']) is int and 0 <= value['alias_entry'] < 65536
            and type(value['target_utf8_bytes']) is int and 1 <= value['target_utf8_bytes'] <= 240
            and value['provider_path_sha256'] == hashlib.sha256(('usr/lib64/'+value['requested_name']).encode('ascii')).hexdigest()): return None
    checks = value['checks']; labels = ('library_name_valid', 'same_family_prefix', 'not_self', 'numeric_suffix')
    if not (type(checks) is dict and len(checks) == 4 and all(type(k) is str for k in checks)
            and set(checks) == set(labels) and all(type(checks[k]) is bool for k in labels)): return None
    failed = [reason for key, reason in zip(labels, ('invalid-library', 'wrong-family', 'self-target', 'nonnumeric-suffix')) if not checks[key]]
    if not (failed and type(value['failed_predicates']) is list and len(value['failed_predicates']) <= 4
            and all(type(p) is str for p in value['failed_predicates']) and value['failed_predicates'] == failed): return None
    literal = value['target_literal']
    if literal is None:
        if checks['library_name_valid']: return None
    else:
        if not library(literal): return None
        raw = literal.encode('ascii')
        if not (len(raw) == value['target_utf8_bytes'] and hashlib.sha256(raw).hexdigest() == value['target_sha256']
                and _alias_checks(value['requested_name'], literal) == checks): return None
    raw = json.dumps(value, ensure_ascii=True, separators=(',', ':'))
    return json.loads(raw) if len(raw.encode('ascii')) <= 2048 else None


def capture_provider_alias(error, name, row):
    require(type(error) is Refusal and str(error) == 'elf-provider-alias', 'elf-diagnostic-shape')
    if 'provider_alias_facts' in error.__dict__: return
    error.provider_alias_facts = None  # First attempt, including unavailable, is final.
    require(library(name) and type(row) is dict and row.get('kind') == 'symlink', 'elf-diagnostic-shape')
    target = row.get('target')
    require(type(target) is str and 1 <= len(target) <= 240, 'elf-diagnostic-shape')
    raw = target.encode('utf-8', 'strict')
    require(1 <= len(raw) <= 240, 'elf-diagnostic-shape')
    checks = _alias_checks(name, target)
    failed = [reason for key, reason in zip(('library_name_valid', 'same_family_prefix', 'not_self', 'numeric_suffix'),
              ('invalid-library', 'wrong-family', 'self-target', 'nonnumeric-suffix')) if not checks[key]]
    error.provider_alias_facts = provider_alias_packet(dict(format='ADBUELFPA1', reason='elf-provider-alias', requested_name=name,
        provider_path_sha256=hashlib.sha256(('usr/lib64/'+name).encode('ascii')).hexdigest(), snapshot=row.get('snapshot'),
        alias_layer=row.get('layer'), alias_entry=row.get('entry'), target_utf8_bytes=len(raw), target_sha256=hashlib.sha256(raw).hexdigest(),
        target_literal=target if library(target) else None, checks=checks, failed_predicates=failed, diagnostic_only=True, runtime_grant=False))


def eligible(path):
    return path in (SERVER, LOADER) or (path.startswith('usr/lib64/') and library(path[10:]))


def string_table_packet(value):
    fields = {'format', 'strtab_present', 'strsz_present', 'declared_strsz', 'limit_bytes', 'failed_predicates', 'diagnostic_only', 'runtime_grant'}
    if type(value) is not dict or len(value) != len(fields) or not all(type(k) is str for k in value) or set(value) != fields: return None
    if not (type(value['format']) is str and value['format'] == 'ADBUELFST1'
            and type(value['strtab_present']) is bool and type(value['strsz_present']) is bool
            and type(value['limit_bytes']) is int and value['limit_bytes'] == MIB
            and value['diagnostic_only'] is True and value['runtime_grant'] is False): return None
    size = value['declared_strsz']
    if value['strsz_present']:
        if type(size) is not int or not 0 <= size < 2**64: return None
    elif size is not None: return None
    failed = []
    if not value['strtab_present']: failed.append('missing-strtab')
    if not value['strsz_present']: failed.append('missing-strsz')
    elif size == 0: failed.append('zero-strsz')
    elif size > MIB: failed.append('strsz-over-limit')
    if not (failed and type(value['failed_predicates']) is list and len(value['failed_predicates']) <= 2
            and all(type(p) is str for p in value['failed_predicates']) and value['failed_predicates'] == failed): return None
    return dict(value, failed_predicates=list(failed))


def capture_string_table_failure(error, tags):
    require(type(error) is Refusal, 'elf-diagnostic-shape')
    if 'elf_string_table_facts' in error.__dict__: return
    require(type(tags) is dict and len(tags) <= 3 and all(type(k) is int and k in (5,10,14) for k in tags)
            and all(type(v) is int and 0 <= v < 2**64 for v in tags.values()), 'elf-diagnostic-shape')
    failed = []
    if 5 not in tags: failed.append('missing-strtab')
    if 10 not in tags: failed.append('missing-strsz')
    elif tags[10] == 0: failed.append('zero-strsz')
    elif tags[10] > MIB: failed.append('strsz-over-limit')
    facts = string_table_packet(dict(format='ADBUELFST1', strtab_present=5 in tags, strsz_present=10 in tags,
        declared_strsz=tags.get(10), limit_bytes=MIB, failed_predicates=failed, diagnostic_only=True, runtime_grant=False))
    require(facts is not None, 'elf-diagnostic-shape')
    error.elf_string_table_facts = facts


_COST_FIELDS = ('structural', 'mapping', 'search_bytes', 'copy_bytes', 'ascii_bytes', 'decode_bytes', 'boundary_bytes')


def access_receipt(value):
    fields = set(_COST_FIELDS) | {'format', 'complete', 'granted', 'used', 'requests', 'extent', 'runtime_grant'}
    if type(value) is not dict or len(value) != len(fields) or not all(type(k) is str for k in value) or set(value) != fields: return None
    if not (type(value['format']) is str and value['format'] == 'ADBUELFCOST1'
            and type(value['complete']) is bool and value['runtime_grant'] is False): return None
    if not all(type(value[k]) is int and 0 <= value[k] <= 2097152 for k in (*_COST_FIELDS, 'granted', 'used')): return None
    if not (type(value['requests']) is int and 0 <= value['requests'] <= 67
            and type(value['extent']) is int and 0 <= value['extent'] <= 128*MIB): return None
    if not (value['used'] == sum(value[k] for k in _COST_FIELDS) and value['used'] <= value['granted']
            and value['mapping'] <= 256 and value['boundary_bytes'] <= 2
            and value['structural'] >= value['requests']
            and value['search_bytes'] <= 241*value['requests']
            and value['copy_bytes'] == value['ascii_bytes'] == value['decode_bytes'] <= min(240*value['requests'], value['search_bytes'])): return None
    if value['requests'] and (not value['extent'] or value['boundary_bytes'] != 2): return None
    if value['complete'] and bool(value['requests']) != bool(value['extent']): return None
    return dict(value)


class _AccessCost:
    def __init__(self, granted, deadline):
        require(type(granted) is int and 0 <= granted <= 2097152, 'elf-string-access-budget')
        require(deadline is None or type(deadline) in (int,float) and 0 <= deadline < float('inf'), 'elf-deadline')
        self.granted, self.deadline, self.used, self.requests, self.extent = granted, deadline, 0, 0, 0
        self.counts = dict.fromkeys(_COST_FIELDS, 0)

    def _admit(self, amount):
        require(type(amount) is int and 0 <= amount <= 2097152, 'elf-string-access-cost')
        require(self.used+amount <= self.granted, 'elf-string-access-work')
        require(self.deadline is None or time.monotonic() < self.deadline, 'elf-string-access-deadline')
        self.used += amount

    def spend(self, category, amount):
        require(type(category) is str and category in _COST_FIELDS, 'elf-string-access-cost')
        self._admit(amount); self.counts[category] += amount

    def payload(self, length):
        require(type(length) is int and 0 <= length <= 240, 'elf-string-length')
        self._admit(3*length)
        self.counts['copy_bytes'] += length; self.counts['ascii_bytes'] += length; self.counts['decode_bytes'] += length

    def receipt(self, complete):
        result = access_receipt(dict(self.counts, format='ADBUELFCOST1', complete=complete, granted=self.granted,
            used=self.used, requests=self.requests, extent=self.extent, runtime_grant=False))
        require(result is not None, 'elf-string-access-receipt')
        return result


def _bounded_table(data, tags, loads, meter):
    require(5 in tags and 10 in tags and 0 < tags[10] <= len(data)
            and tags[5] <= 2**64-tags[10], 'elf-string-access-span')
    pointer, extent = tags[5], tags[10]; meter.extent = extent
    meter.spend('mapping', len(loads))
    mapped = [off+pointer-va for va, off, length in loads if va <= pointer and pointer-va <= length
              and extent <= length-(pointer-va)]
    require(len(mapped) == 1, 'elf-string-access-mapping')
    base = mapped[0]
    require(0 <= base <= len(data) and extent <= len(data)-base, 'elf-string-access-span')
    meter.spend('boundary_bytes', 2)
    require(data[base] == 0 and data[base+extent-1] == 0, 'elf-string-access-boundary')
    return base, extent


def _bounded_text(data, base, extent, index, empty, meter):
    require(type(data) in (bytes,bytearray) and type(base) is int and type(extent) is int
            and 0 <= base <= len(data) and 0 < extent <= len(data)-base
            and type(index) is int and 0 <= index < extent and type(empty) is bool
            and type(meter) is _AccessCost, 'elf-string-index')
    meter.spend('structural', 1)
    require(meter.requests < 67, 'elf-string-access-requests'); meter.requests += 1
    window = min(241, extent-index); position = base+index
    meter.spend('search_bytes', window)
    end = data.find(b'\0', position, position+window)
    require(end >= position and end-position <= 240, 'elf-string-length')
    length = end-position; meter.payload(length)
    raw = data[position:end]
    require((empty or length > 0) and all(32 <= c <= 126 for c in raw), 'elf-string-ascii')
    return raw.decode('ascii')


def parse(data, deadline=None, *, bounded_strings=False, remaining_work=None):
    require(type(bounded_strings) is bool, 'elf-string-access-mode')
    if not bounded_strings:
        require(remaining_work is None, 'elf-string-access-budget')
        return _parse(data, deadline, None)
    meter = _AccessCost(remaining_work, deadline)
    try:
        result = _parse(data, deadline, meter)
        meter.spend('structural', 0)
        return dict(format='ADBUELFSA1', metadata=result, cost=meter.receipt(True), runtime_grant=False)
    except Exception as error:
        try: error.__dict__.setdefault('string_access_facts', meter.receipt(False))
        except Exception: error.__dict__.setdefault('string_access_facts', None)
        raise


def _parse(data, deadline, meter):
    if meter is not None: meter.spend('structural', 1)
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
        if meter is not None:
            meter.spend('structural', 1)
            return
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
            if meter is not None: require(memory <= 2**64-address, 'elf-string-access-span')
            require(size <= memory and (align in (0, 1) or align & (align-1) == 0 and address % align == start % align), 'elf-load-shape')
            require(not loads or address >= loads[-1][0], 'elf-load-order')
            loads.append((address, start, size))
        elif typ == 2:
            require(dynamic is None and 0 < size <= MIB and size % 16 == 0, 'elf-dynamic-bound')
            dynamic = (start, size, address)
        elif typ == 3:
            if meter is not None:
                require(interpreter is None and 1 < size <= 240, 'elf-interpreter')
                meter.spend('structural', 4*(size-1)+1)
                require(data[start+size-1] == 0 and data.find(b'\0',start,start+size-1) == -1, 'elf-interpreter')
            else:
                require(interpreter is None and 1 < size <= 240 and data[start+size-1] == 0
                        and 0 not in data[start:start+size-1], 'elf-interpreter')
            interpreter = ascii_text(data[start:start+size-1])
    require(bool(loads) and dynamic is not None, 'elf-dynamic-required')
    start, size, address = dynamic
    if meter is not None: meter.spend('mapping', len(loads))
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
        if meter is not None:
            base, extent = _bounded_table(data, tags, loads, meter)
        else:
            try:
                require(5 in tags and 10 in tags and 0 < tags[10] <= MIB, 'elf-string-table')
            except Refusal as error:
                try: capture_string_table_failure(error, tags)
                except Exception: error.__dict__.setdefault('elf_string_table_facts', None)
                raise
            mapped = [off+tags[5]-va for va, off, length in loads if va <= tags[5] and tags[5]+tags[10] <= va+length]
            require(len(mapped) == 1, 'elf-string-mapping')
            strings = data[mapped[0]:mapped[0]+tags[10]]
    def text(index, empty=False):
        if meter is not None: return _bounded_text(data, base, extent, index, empty, meter)
        tick(); require(index < len(strings), 'elf-string-index')
        end = strings.find(b'\0', index, index+241)
        require(end >= index, 'elf-string-length')
        return '' if empty and end == index else ascii_text(strings[index:end])
    result = dict(machine='x86_64', bits=64, type=kind, interpreter=interpreter,
                  needed=[text(i) for i in needed], soname=text(tags[14]) if 14 in tags else None,
                  search_paths=[text(i, True) for i in paths], sha256=hashlib.sha256(data).hexdigest(), bytes=len(data),
                  dependency_closure_verified=False, executed=False, work=meter.used if meter is not None else work, unsupported_features=sorted(features))
    if meter is not None:
        meter.spend('structural', sum(len(n) for n in result['needed'])+len(result['needed'])+len(result['soname'] or ''))
    require(all(library(n) for n in result['needed']) and len(set(result['needed'])) == len(result['needed'])
            and (result['soname'] is None or library(result['soname'])), 'elf-library-name')
    if meter is not None: result['work'] = meter.used
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


def _candidate_alias_frame(context):
    fields = {'format', 'profile', 'manifest', 'config', 'head', 'run', 'source', 'snapshot', 'layers'}
    require(type(context) is dict and len(context) == len(fields) and all(type(k) is str for k in context)
            and set(context) == fields, 'elf-candidate-policy-context')
    require(type(context['format']) is str and context['format'] == 'ADBUCAP1'
            and type(context['profile']) is str and context['profile'] == 'candidate-libgcc-alias-v1'
            and type(context['manifest']) is str and context['manifest'] == _LIBGCC_PIN
            and type(context['config']) is str and context['config'] == _LIBGCC_CONFIG, 'elf-candidate-policy-context')
    for key, length in (('head',40), ('run',32), ('source',64), ('snapshot',64)):
        require(type(context[key]) is str and len(context[key]) == length
                and re.fullmatch('[a-f0-9]{'+str(length)+'}', context[key]), 'elf-candidate-policy-context')
    layers = context['layers']
    require(type(layers) is tuple and len(layers) == 10, 'elf-candidate-policy-layers')
    for index, pair in enumerate(layers):
        require(type(pair) is tuple and len(pair) == 2 and type(pair[0]) is str and pair[0] == _LIBGCC_LAYERS[index]
                and type(pair[1]) is str and len(pair[1]) == 64 and re.fullmatch('[a-f0-9]{64}', pair[1]), 'elf-candidate-policy-layers')
    require(layers[0][1] == _LIBGCC_DIFF, 'elf-candidate-policy-layers')
    return tuple(context[k] for k in ('manifest', 'config', 'source', 'head', 'run', 'snapshot', 'layers'))


def _candidate_libgcc_alias(name, row, snapshot):
    # One observed tuple, never a version/date pattern or a target-content hash.
    fields = {'path', 'kind', 'mode', 'uid', 'gid', 'sha256', 'size', 'target', 'layer', 'entry', 'snapshot', 'model_only', 'runtime_grant'}
    return bool(type(name) is str and name == 'libgcc_s.so.1' and type(row) is dict and len(row) == len(fields)
        and all(type(k) is str for k in row) and set(row) == fields
        and row['model_only'] is True and row['runtime_grant'] is False
        and type(row['mode']) is int and 0 <= row['mode'] <= 0o7777
        and all(type(row[k]) is int and 0 <= row[k] <= 4294967295 for k in ('uid', 'gid'))
        and row['sha256'] is None and type(row['size']) is int and row['size'] == 0
        and type(snapshot) is str and len(snapshot) == 64 and re.fullmatch('[a-f0-9]{64}', snapshot)
        and type(row.get('snapshot')) is str and row['snapshot'] == snapshot
        and type(row.get('path')) is str and row['path'] == 'usr/lib64/libgcc_s.so.1'
        and type(row.get('kind')) is str and row['kind'] == 'symlink'
        and type(row.get('layer')) is int and row['layer'] == 0
        and type(row.get('entry')) is int and row['entry'] == 1700
        and type(row.get('target')) is str and row['target'] == 'libgcc_s-11-20240719.so.1'
        and hashlib.sha256(row['target'].encode('ascii')).hexdigest() == 'd359192c4c77928fa9d0d5be9e87f9a95cad61eaa2081a46213ae80d18bb2ec9')


class _CandidateAliasPolicy:
    """Private per-call DATA policy. Only the fixed helper supplies typed owners.

    No job authentication or candidate attestation is conferred by this object.
    Actual fixed-entry selection remains absent; Python is not a hostile sandbox.
    """
    __slots__ = ('_tree', '_owner', '_frame')

    def __init__(self, tree, owner, context):
        self._frame = _candidate_alias_frame(context)
        self._tree, self._owner = tree, owner
        self._admit(tree, owner.deadline)

    def _admit(self, tree, deadline):
        owner = self._owner; manifest, config, source, head, run, snapshot, layers = self._frame
        require(tree is self._tree and tree._work_owner is owner and owner._model is tree
                and tree._final is True and type(tree._committed) is int and tree._committed == 10
                and type(owner._verified) is int and owner._verified == 10, 'elf-candidate-policy-owner')
        require(owner._identity == tree._identity == dict(manifest=manifest, config=config, source=source)
                and owner._run == dict(head=head, run=run, profile='bounded-strings-v1')
                and owner._layers == tree._layers == list(layers)
                and tree._snapshot_hash == snapshot and deadline == owner.deadline, 'elf-candidate-policy-binding')
        owner.admit_graph(tree)  # Original clock/phase check: poison is terminal.
        tree.charge(32, clock(deadline))  # Fixed bounded frame comparisons, shared work/deadline.

    def _matches(self, name, row):
        return _candidate_libgcc_alias(name, row, self._frame[5])


def _admit_candidate_policy(tree, policy, deadline):
    require(type(policy) is _CandidateAliasPolicy, 'elf-candidate-policy-type')
    policy._admit(tree, deadline)


def provider(tree, name, deadline, *, candidate_alias_policy=None):
    require(library(name), 'elf-library-name')
    if candidate_alias_policy is not None: _admit_candidate_policy(tree, candidate_alias_policy, deadline)
    path = 'usr/lib64/'+name
    row = tree.inspect(path, clock(deadline)); edges = []
    if row['kind'] == 'symlink':
        target = row['target']; stem = name.split('.so', 1)[0]+'.so.'
        try:
            valid_target = library(target) and target != name
            strict = valid_target and target.startswith(stem) and re.fullmatch(r'[0-9]+(?:\.[0-9]+)*', target[len(stem):])
            require(valid_target and (strict or (candidate_alias_policy is not None and candidate_alias_policy._matches(name, row))), 'elf-provider-alias')
        except Refusal as error:
            try: capture_provider_alias(error, name, row)
            except Exception: error.__dict__.setdefault('provider_alias_facts', None)
            raise
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


def failure_packet(value):
    fields = {'format', 'stage', 'path_sha256', 'needed_name', 'roots', 'runtime_grant'}
    if type(value) is not dict or any(type(k) is not str for k in value) or set(value) != fields: return None
    def digest(raw): return type(raw) is str and re.fullmatch('[a-f0-9]{64}', raw) is not None
    if not (type(value['format']) is str and value['format'] == 'ADBUELF1' and value['runtime_grant'] is False
            and type(value['stage']) is str and value['stage'] in ('root-origins', 'dependency-graph', 'graph-validation')
            and (value['path_sha256'] is None or digest(value['path_sha256']))
            and (value['needed_name'] is None or library(value['needed_name']))
            and type(value['roots']) is dict and len(value['roots']) <= 2
            and all(type(k) is str and k in (SERVER, LOADER) for k in value['roots'])): return None
    for root in value['roots'].values():
        if not (type(root) is dict and all(type(k) is str for k in root) and set(root) == {
                'sha256', 'bytes', 'type', 'soname', 'needed', 'unsupported_features', 'interpreter_sha256', 'search_path_count', 'search_path_hashes'}): return None
        if not (digest(root['sha256']) and type(root['bytes']) is int and 64 <= root['bytes'] <= 128*MIB
                and type(root['type']) is int and root['type'] in (2,3) and (root['soname'] is None or library(root['soname']))
                and type(root['needed']) is list and len(root['needed']) <= 64 and all(library(n) for n in root['needed'])
                and len(set(root['needed'])) == len(root['needed'])
                and type(root['unsupported_features']) is list and len(root['unsupported_features']) <= 7
                and all(type(f) is str and f in set(FEATURE_TAGS.values()) | {'origin-flag', 'dynamic-search-flag'} for f in root['unsupported_features'])
                and len(set(root['unsupported_features'])) == len(root['unsupported_features'])
                and (root['interpreter_sha256'] is None or digest(root['interpreter_sha256']))
                and type(root['search_path_count']) is int and 0 <= root['search_path_count'] <= 2
                and type(root['search_path_hashes']) is list and len(root['search_path_hashes']) == root['search_path_count']
                and all(digest(p) for p in root['search_path_hashes'])): return None
    raw = json.dumps(value, ensure_ascii=True, separators=(',', ':'))
    return json.loads(raw) if len(raw.encode('ascii')) <= 32768 else None


def capture_failure(error, stage, current, requested, roots):
    if 'elf_facts' in error.__dict__: return
    facts = dict(format='ADBUELF1', stage=stage,
                 path_sha256=hashlib.sha256(current.encode('utf-8')).hexdigest() if current is not None else None,
                 needed_name=requested, roots=roots, runtime_grant=False)
    error.elf_facts = facts if len(json.dumps(facts, ensure_ascii=True).encode('ascii')) <= 32768 else dict(format='ADBUELF1', withheld=True, runtime_grant=False)


def graph(tree, observations, deadline, *, candidate_alias_policy=None):
    require(type(observations) is dict and len(observations) <= 256 and all(type(k) is str and re.fullmatch('[0-9]{1,2}:[0-9]{1,5}', k) for k in observations), 'elf-observation-bound')
    roots, cache, providers, alias_edges, objects = {}, {}, {}, [], {}
    current, requested, stage = SERVER, None, 'root-origins'
    candidate_aliases = 0
    def descriptor(path):
        node = tree.inspect(path, clock(deadline))
        return bound_metadata(node, observations, path), node['snapshot']
    try:
        if candidate_alias_policy is not None: _admit_candidate_policy(tree, candidate_alias_policy, deadline)
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
                path, node, aliases = provider(tree, requested, deadline, candidate_alias_policy=candidate_alias_policy)
                if candidate_alias_policy is not None and requested == 'libgcc_s.so.1' and aliases == [dict(
                        path='usr/lib64/libgcc_s.so.1', target='libgcc_s-11-20240719.so.1', layer=0, entry=1700)]:
                    candidate_aliases += 1
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
        if candidate_alias_policy is not None:
            result['candidate_alias_policy'] = dict(format='ADBUCAPUSE1', profile='candidate-libgcc-alias-v1', matched_aliases=candidate_aliases, runtime_grant=False)
        return result
    except Exception as error:
        try: capture_failure(error, stage, current, requested, roots)
        except Exception: error.__dict__.setdefault('elf_facts', None)
        raise
