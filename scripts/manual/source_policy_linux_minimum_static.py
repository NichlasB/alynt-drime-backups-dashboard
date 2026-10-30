"""Bounded static readers only. Never extract, load or execute artifact contents."""
import gzip
import hashlib
import io
import json
import re
import struct
import tarfile
import time
import zipfile

MIB = 1024 * 1024
CORE_TREE = 'a3bd7a4074a4c95c8af3b969346726273d68e167'
CORE_COMMIT = 'cc101b64012b16d087780657a2b828ccd7794a63'


class Refusal(Exception):
    pass


def require(ok, code):
    if not ok:
        raise Refusal(code)


def digest(value):
    require(isinstance(value, str) and re.fullmatch(r'sha256:[a-f0-9]{64}', value), 'digest-shape')
    return value[7:]


def descriptor(row, limit):
    require(isinstance(row, dict) and type(row.get('size')) is int and 0 < row['size'] <= limit
            and 'urls' not in row, 'descriptor-bound')
    return digest(row.get('digest')), row['size']


def decode(data):
    require(len(data) <= 2 * MIB, 'json-bound')
    def pairs(items):
        result = {}
        for key, value in items:
            require(key not in result, 'duplicate-json-key')
            result[key] = value
        return result
    return json.loads(data, object_pairs_hook=pairs)


def name(value):
    require(isinstance(value, str) and 0 < len(value) <= 240
            and all(32 <= ord(c) <= 126 for c in value) and '\\' not in value
            and not value.startswith('/') and len(value.split('/')) <= 32
            and all(p not in ('', '.', '..') for p in value.split('/')), 'archive-path')
    return value


def elf(data):
    require(len(data) >= 64 and data[:7] == b'\x7fELF\x02\x01\x01', 'elf-header')
    kind, machine = struct.unpack_from('<HH', data, 16)
    offset = struct.unpack_from('<Q', data, 32)[0]
    width, count = struct.unpack_from('<HH', data, 54)
    require(kind in (2, 3) and machine == 62 and width == 56 and count <= 128
            and offset + width * count <= len(data), 'elf-program-table')
    segments, dynamic, interpreter = [], None, None
    for i in range(count):
        ptype, flags, start, address, physical, length, mem, align = struct.unpack_from('<IIQQQQQQ', data, offset + i * width)
        require(start + length <= len(data), 'elf-segment-bound')
        if ptype == 1:
            segments.append((address, start, length))
        elif ptype == 2:
            require(length <= MIB and length % 16 == 0 and dynamic is None, 'elf-dynamic-bound')
            dynamic = (start, length)
        elif ptype == 3:
            require(0 < length <= 240 and data[start + length - 1] == 0, 'elf-interpreter')
            interpreter = data[start:start + length - 1].decode('ascii')
    needed, paths, tags = [], [], {}
    if dynamic:
        ended = False
        for i in range(dynamic[0], sum(dynamic), 16):
            tag, value = struct.unpack_from('<qQ', data, i)
            if tag == 0:
                ended = True
                break
            if tag == 1:
                needed.append(value)
            elif tag in (15, 29):
                paths.append(value)
            elif tag in (5, 10):
                require(tag not in tags, 'elf-duplicate-string-table')
                tags[tag] = value
        require(ended and len(needed) <= 64 and len(paths) <= 2, 'elf-dynamic-shape')
    strings = b''
    if needed or paths:
        require(5 in tags and 10 in tags and tags[10] <= MIB, 'elf-string-table')
        mapped = [start + tags[5] - address for address, start, size in segments
                  if address <= tags[5] and tags[5] + tags[10] <= address + size]
        require(len(mapped) == 1, 'elf-string-mapping')
        strings = data[mapped[0]:mapped[0] + tags[10]]
    def text(index):
        require(index < len(strings), 'elf-string-index')
        end = strings.find(b'\0', index, index + 241)
        require(end >= index, 'elf-string-length')
        value = strings[index:end].decode('ascii')
        require(all(32 <= ord(c) <= 126 for c in value), 'elf-string-ascii')
        return value
    return {'machine': 'x86_64', 'bits': 64, 'type': kind, 'interpreter': interpreter,
            'needed': [text(i) for i in needed], 'search_paths': [text(i) for i in paths],
            'sha256': hashlib.sha256(data).hexdigest(), 'bytes': len(data),
            'dependency_closure_verified': False, 'executed': False}


class ScanReader:
    def __init__(self, source, budget):
        self.source, self.budget, self.size, self.sha = source, budget, 0, hashlib.sha256()

    def read(self, size):
        require(0 <= size <= MIB and time.monotonic() < self.budget['deadline'], 'scan-read-bound')
        data = self.source.read(size)
        self.size += len(data)
        self.budget['expanded'] += len(data)
        require(self.size <= 1024 * MIB and self.budget['expanded'] <= 2048 * MIB, 'expanded-bound')
        self.sha.update(data)
        return data


def scan_layer(path, diff_id, wanted, roles, budget):
    expected = digest(diff_id)
    seen = set()
    with gzip.open(path, 'rb') as compressed:
        reader = ScanReader(compressed, budget)
        with tarfile.open(fileobj=reader, mode='r|') as archive:
            for member in archive:
                budget['entries'] += 1
                require(budget['entries'] <= 65536 and not member.sparse
                        and not any(k.startswith('GNU.sparse') for k in member.pax_headers)
                        and 0 <= member.size <= 128 * MIB, 'tar-entry-bound')
                require(len(member.pax_headers) <= 32
                        and all(len(k) <= 128 and len(v) <= 4096 for k, v in member.pax_headers.items()), 'tar-metadata-bound')
                raw = member.name
                if raw.startswith('./'):
                    raw = raw[2:]
                if member.isdir():
                    raw = raw.rstrip('/')
                if raw in ('', '.') and member.isdir():
                    continue
                key = name(raw)
                require(key not in seen, 'tar-duplicate')
                seen.add(key)
                require(member.type in (tarfile.REGTYPE, tarfile.AREGTYPE, tarfile.DIRTYPE, tarfile.SYMTYPE, tarfile.LNKTYPE), 'tar-special-type')
                require(not any(role.startswith(key + '/') for role in wanted) or member.isdir(), 'critical-parent-not-directory')
                if member.issym() or member.islnk():
                    require(not any(key == role or role.startswith(key + '/') for role in wanted), 'critical-linked-path')
                directory, _, leaf = key.rpartition('/')
                if leaf.startswith('.wh.'):
                    require(member.isfile() and member.size == 0 and leaf != '.wh.', 'whiteout-shape')
                    target = directory if leaf == '.wh..wh..opq' else (directory + '/' if directory else '') + leaf[4:]
                    for role in list(roles):
                        if not target or role == target or role.startswith(target + '/'):
                            del roles[role]
                    continue
                if key in wanted:
                    require(member.isfile(), 'critical-not-regular')
                    content = io.BytesIO()
                    with archive.extractfile(member) as stream:
                        while True:
                            block = stream.read(MIB)
                            if not block:
                                break
                            require(content.tell() + len(block) <= 128 * MIB, 'critical-file-bound')
                            content.write(block)
                    require(content.tell() == member.size, 'critical-size')
                    roles[key] = elf(content.getvalue())
                    roles[key].update(mode=member.mode, uid=member.uid, gid=member.gid,
                                      pax_keys=sorted(member.pax_headers))
        while reader.read(MIB):
            pass
        require(reader.sha.hexdigest() == expected, 'diff-id-mismatch')
    return {'entries': len(seen), 'expanded_bytes': reader.size, 'diff_id_verified': True}


def tree_oid(files):
    require(len(files) <= 8192, 'tree-file-bound')
    root = {}
    for path, (mode, oid) in files.items():
        require(mode in ('100644', '100755') and re.fullmatch('[a-f0-9]{40}', oid), 'tree-file-identity')
        node = root
        parts = name(path).split('/')
        for part in parts[:-1]:
            require(not isinstance(node.get(part), tuple), 'tree-path-collision')
            node = node.setdefault(part, {})
        require(parts[-1] not in node, 'tree-path-collision')
        node[parts[-1]] = (mode, oid)
    def encode(node):
        rows = []
        for key in sorted(node, key=lambda p: (p + '/' if isinstance(node[p], dict) else p).encode('ascii')):
            entry = node[key]
            mode, oid = ('40000', encode(entry)) if isinstance(entry, dict) else entry
            rows.append(mode.encode() + b' ' + key.encode('ascii') + b'\0' + bytes.fromhex(oid))
        data = b''.join(rows)
        return hashlib.sha1(b'tree ' + str(len(data)).encode() + b'\0' + data).hexdigest()
    return encode(root)


def core_modes(metadata):
    require(metadata.get('sha') == CORE_TREE and metadata.get('truncated') is False
            and isinstance(metadata.get('tree'), list) and len(metadata['tree']) <= 16384, 'core-metadata')
    files = {}
    for entry in metadata['tree']:
        key = name(entry['path'])
        if entry['type'] == 'tree':
            require(entry['mode'] == '040000', 'core-directory-mode')
            continue
        require(entry['type'] == 'blob' and entry['mode'] in ('100644', '100755')
                and re.fullmatch('[a-f0-9]{40}', entry['sha']) and key not in files, 'core-file-mode')
        files[key] = (entry['mode'], entry['sha'])
    require(0 < len(files) <= 8192 and tree_oid(files) == CORE_TREE, 'core-metadata-tree')
    return files


def core_zip(path, modes, pins):
    observed, checked, total = {}, set(), 0
    prefix = 'WordPress-' + CORE_COMMIT + '/'
    with zipfile.ZipFile(path) as archive:
        require(len(archive.infolist()) <= 16384, 'zip-entry-count')
        seen = set()
        for entry in archive.infolist():
            require(entry.filename.startswith(prefix) and entry.filename not in seen, 'zip-prefix-duplicate')
            seen.add(entry.filename)
            key = entry.filename[len(prefix):].rstrip('/')
            if not key and entry.is_dir():
                continue
            key = name(key)
            require(not entry.flag_bits & 1 and entry.compress_type in (0, 8)
                    and ((entry.external_attr >> 16) & 0o170000) in (0, 0o040000, 0o100000), 'zip-entry-kind')
            if entry.is_dir():
                continue
            require(key in modes and key not in observed and entry.file_size <= 32 * MIB, 'zip-file-bound')
            total += entry.file_size
            require(total <= 128 * MIB, 'core-expanded-bound')
            blob = hashlib.sha1(b'blob ' + str(entry.file_size).encode() + b'\0')
            sha, count = hashlib.sha256(), 0
            with archive.open(entry) as stream:
                while True:
                    block = stream.read(MIB)
                    if not block:
                        break
                    count += len(block)
                    require(count <= entry.file_size, 'zip-read-bound')
                    blob.update(block)
                    sha.update(block)
            require(count == entry.file_size and blob.hexdigest() == modes[key][1], 'core-blob-mismatch')
            observed[key] = modes[key]
            if key in pins:
                require(sha.hexdigest() == pins[key], 'core-pin-mismatch')
                checked.add(key)
    require(set(observed) == set(modes) and checked == set(pins)
            and tree_oid(observed) == CORE_TREE, 'complete-core-tree')
    return {'files': len(observed), 'expanded_bytes': total, 'tree': CORE_TREE,
            'sha256_pins': len(checked), 'executed': False}
