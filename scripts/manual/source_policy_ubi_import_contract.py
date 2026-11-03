"""Proposed load-archive DATA codec. No files, sockets, engine or execution owner.

Input streams are caller-held bytes, not fresh static authority. Output is a
provisional archive prefix until ALL hashes pass; never wire directly to an
engine without separately qualified import/quarantine/readback admission.
"""
import hashlib
import json
import re
import zlib

MIB = 1024*1024
CHUNK = 65536


class Refusal(ValueError):
    pass


def require(condition, reason):
    if not condition:
        raise Refusal(reason)


def digest(value):
    return type(value) is str and re.fullmatch('[a-f0-9]{64}', value) is not None


class Meter:
    """Shared DATA accounting/virtual clock; not a wall-clock supervisor."""
    LIMITS = dict(expanded=2*1024*MIB, work=2097152, control=MIB, observation=2*MIB)

    def __init__(self):
        self.used = dict.fromkeys(self.LIMITS, 0)
        self.now = 0
        self.failure = None

    def check(self, condition, reason):
        if self.failure is None and not condition:
            self.failure = reason
        require(self.failure is None, self.failure)

    def tick(self, now):
        self.check(type(now) is int and self.now <= now <= 600000, 'clock-budget')
        self.now = now

    def reserve(self, phase_ms):
        self.check(type(phase_ms) is int and 0 <= phase_ms <= 120000
                   and self.now + phase_ms + 30000 <= 600000, 'cleanup-reserve')

    def charge(self, kind, amount):
        self.check(kind in self.LIMITS and type(amount) is int and amount >= 0, 'accounting-shape')
        self.check(self.used[kind] + amount <= self.LIMITS[kind], kind+'-budget')
        self.used[kind] += amount


def _object(pairs):
    result = {}
    for key, value in pairs:
        require(key not in result, 'config-duplicate')
        result[key] = value
    return result


def _constant(value):
    raise Refusal('config-constant')


def _plan(config, layers, meter):
    require(type(config) is bytes and 0 < len(config) <= MIB, 'config-size')
    require(type(layers) is tuple and len(layers) == 10, 'layer-count')
    meter.charge('control', len(config)); meter.charge('expanded', len(config))
    meter.charge('work', len(config))
    try:
        parsed = json.loads(config, object_pairs_hook=_object, parse_constant=_constant)
    except (ValueError, UnicodeError, RecursionError):
        raise Refusal('config-json') from None
    require(type(parsed) is dict and parsed.get('os') == 'linux'
            and parsed.get('architecture') == 'amd64', 'config-platform')
    root = parsed.get('rootfs')
    require(type(root) is dict and set(root) == {'type', 'diff_ids'} and root['type'] == 'layers'
            and type(root['diff_ids']) is list and len(root['diff_ids']) == 10, 'config-rootfs')
    for row, diff in zip(layers, root['diff_ids']):
        require(type(row) is tuple and len(row) == 4 and digest(row[0]) and digest(row[2])
                and type(row[1]) is int and 0 < row[1] <= 128*MIB
                and type(row[3]) is int and 0 < row[3] <= 2*1024*MIB, 'layer-descriptor')
        require(type(diff) is str and diff == 'sha256:'+row[2], 'diff-id-order')
    require(sum(row[1] for row in layers) <= 256*MIB, 'compressed-plan-budget')
    require(sum(row[3] for row in layers) <= 2*1024*MIB, 'expanded-plan-budget')
    name = hashlib.sha256(config).hexdigest()+'.json'
    names = ['layers/%02d-%s.tar' % (n, row[2]) for n, row in enumerate(layers)]
    manifest = json.dumps([dict(Config=name, RepoTags=[], Layers=names)], separators=(',', ':'), sort_keys=True).encode('ascii')
    meter.charge('control', len(manifest))
    meter.charge('work', len(manifest))
    return name, names, manifest


def _header(name, size):
    # Fixed digest-derived USTAR names, metadata and regular type; no image paths.
    header = bytearray(512)
    header[:len(name)] = name.encode('ascii')
    for offset, width, value in ((100, 8, 0o444), (108, 8, 0), (116, 8, 0), (124, 12, size), (136, 12, 0)):
        header[offset:offset+width] = (('%0*o' % (width-1, value))+'\0').encode('ascii')
    header[148:156] = b'        '; header[156:157] = b'0'
    header[257:265] = b'ustar\00000'
    header[148:156] = ('%06o\0 ' % sum(header)).encode('ascii')
    return bytes(header)


def archive(config, layers, streams, meter):
    """Yield bounded proposed archive bytes; errors invalidate every prior byte.

    A generic synthetic plan can pass this codec. Candidate pin/source/owner and
    engine-format admission are deliberately NOT provided by this function.
    """
    name, names, manifest = _plan(config, layers, meter)
    require(type(streams) is tuple and len(streams) == 10, 'stream-count')
    meter.reserve(120000)
    deadline = meter.now+120000

    def emit(data):
        for offset in range(0, len(data), CHUNK):
            meter.check(meter.now <= deadline, 'import-timeout')
            chunk = data[offset:offset+CHUNK]
            meter.charge('work', 1); meter.charge('expanded', len(chunk))
            yield chunk

    for member, data in ((name, config), ('manifest.json', manifest)):
        yield from emit(_header(member, len(data)))
        yield from emit(data)
        yield from emit(b'\0' * (-len(data) % 512))
    for member, row, stream in zip(names, layers, streams):
        compressed_sha, compressed_size, diff_sha, expanded_size = row
        yield from emit(_header(member, expanded_size))
        compressed_hash, diff_hash = hashlib.sha256(), hashlib.sha256()
        compressed_count = expanded_count = 0
        decoder = zlib.decompressobj(16+zlib.MAX_WBITS)
        for chunk in stream:
            meter.check(meter.now <= deadline, 'import-timeout')
            meter.charge('work', 1)
            require(type(chunk) is bytes and 0 < len(chunk) <= CHUNK, 'compressed-chunk')
            compressed_count += len(chunk)
            require(compressed_count <= compressed_size, 'compressed-length')
            compressed_hash.update(chunk)
            pending = chunk
            while pending:
                meter.charge('work', 1)
                try:
                    decoded = decoder.decompress(pending, CHUNK)
                except zlib.error:
                    raise Refusal('gzip-shape') from None
                require(not decoder.unused_data, 'gzip-trailing')
                pending = decoder.unconsumed_tail
                expanded_count += len(decoded)
                require(expanded_count <= expanded_size, 'expanded-length')
                meter.charge('expanded', len(decoded)); diff_hash.update(decoded)
                yield from emit(decoded)
        require(compressed_count == compressed_size and compressed_hash.hexdigest() == compressed_sha, 'compressed-identity')
        require(decoder.eof and expanded_count == expanded_size and diff_hash.hexdigest() == diff_sha, 'expanded-identity')
        yield from emit(b'\0' * (-expanded_count % 512))
    yield from emit(b'\0'*1024)
    meter.check(meter.now <= deadline, 'import-timeout')


def drain(chunks, inert_sink, meter):
    """Inert partial-write contract, NOT an engine pipe or process adapter."""
    digest_out = hashlib.sha256(); count = 0
    for chunk in chunks:
        require(type(chunk) is bytes and 0 < len(chunk) <= CHUNK, 'archive-chunk')
        offset = 0
        while offset < len(chunk):
            meter.charge('work', 1)
            written = inert_sink(chunk[offset:])
            require(type(written) is int and 0 < written <= len(chunk)-offset, 'sink-progress')
            meter.charge('expanded', written)
            digest_out.update(chunk[offset:offset+written]); offset += written; count += written
    return dict(format='ADBUIMPORTDATA1', bytes=count, sha256=digest_out.hexdigest(), launch_authority=False)
