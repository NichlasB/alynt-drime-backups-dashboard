"""Fixed PPA DEB DATA policy. Never install or materialize member paths."""
import hashlib
import importlib.util
import json
from pathlib import Path
import re
import time

ROOT = Path(__file__).resolve().parents[2]
SPEC = importlib.util.spec_from_file_location('php_archive_data', ROOT/'scripts/ci/source-policy-php-archive-static.py')
A = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(A)
S = A.S
REVISION = '8.5.12-1+ubuntu24.04.1+deb.sury.org+1'
PINS = (
    ('php8.5-cli', 3165930, '9ee42a60bccc4b2fb91624a05535702d9c45699e61d20ace18e39d69bf7e53c8'),
    ('php8.5-common', 850622, 'fc3adc82865c0046614cd577ad816db44a8923672236f7637eb7256673efc7d1'),
    ('php8.5-mysql', 129750, '81cf7b2d0330ce86d9287bed2bdbb7210f41ab3846bee9c161babdecdf436279'),
)
# Literal publisher build33861482 deliveries. No rolling pool, redirects or fallback.
DELIVERIES = {
    'php8.5-cli': 'https://launchpadlibrarian.net/882014196/php8.5-cli_'+REVISION+'_amd64.deb',
    'php8.5-common': 'https://launchpadlibrarian.net/882014197/php8.5-common_'+REVISION+'_amd64.deb',
    'php8.5-mysql': 'https://launchpadlibrarian.net/882014209/php8.5-mysql_'+REVISION+'_amd64.deb',
}
LIMIT = 192*A.MIB


class Budget:
    def __init__(self):
        self.total, self.entries, self.deadline = 0, 0, time.monotonic()+540
        self.roles, self.paths, self.types = {}, {}, {}
        self.inventory = hashlib.sha256()

    def charge(self, count):
        S.require(type(count) is int and count >= 0 and time.monotonic() < self.deadline, 'work-deadline')
        self.total += count
        S.require(self.total <= LIMIT, 'shared-work-budget')

    def entry(self):
        self.entries += 1
        S.require(self.entries <= 8192, 'shared-entry-budget')


class Slice:
    def __init__(self, file, start, length, budget):
        self.file, self.remaining, self.budget = file, length, budget
        file.seek(start)

    def read(self, count):
        S.require(type(count) is int and 0 <= count <= 65536, 'slice-read-bound')
        data = self.file.read(min(count, self.remaining))
        S.require(data or self.remaining == 0 or count == 0, 'slice-truncated')
        self.remaining -= len(data)
        self.budget.charge(len(data))
        return data


def exact(source, count):
    S.require(0 <= count <= 512, 'exact-read-bound')
    result = bytearray()
    while len(result) < count:
        chunk = source.read(count-len(result))
        S.require(chunk, 'data-truncated')
        result.extend(chunk)
    return bytes(result)


def ar_members(file, budget):
    size = file.seek(0, 2)
    reader = Slice(file, 0, size, budget)
    S.require(exact(reader, 8) == b'!<arch>\n', 'ar-magic')
    rows = []
    while reader.remaining:
        S.require(len(rows) < 3, 'ar-member-count')
        header = exact(reader, 60)
        S.require(header[58:] == b'`\n' and re.fullmatch(b'[0-9]+ *', header[48:58]), 'ar-header')
        raw = header[:16].rstrip(b' ')
        if raw.endswith(b'/'):
            raw = raw[:-1]
        name = raw.decode('ascii', 'strict')
        length, start = int(header[48:58]), file.tell()
        allowed = ({'debian-binary'}, {f'control.tar{s}' for s in ('', '.gz', '.xz', '.zst')},
                   {f'data.tar{s}' for s in ('', '.gz', '.xz', '.zst')})
        S.require(name in allowed[len(rows)] and length <= 16*A.MIB and length <= reader.remaining, 'ar-member-shape')
        if not rows:
            S.require(length == 4 and exact(reader, 4) == b'2.0\n', 'deb-version')
        else:
            file.seek(length, 1)
            reader.remaining -= length  # Compressed bytes are charged when consumed, not silently skipped.
        rows.append((name, start, length))
        if length % 2:
            S.require(exact(reader, 1) == b'\n', 'ar-padding')
    S.require(len(rows) == 3 and file.tell() == size, 'ar-complete')
    return rows[1:]


def virtual_name(value, kind, size):
    # A single literal Debian './' prefix is a virtual DATA key, never a host path.
    if value in ('.', './'):
        S.require(kind == b'5' and size == 0, 'virtual-root')
        return '.'
    if value.startswith('./'):
        value = value[2:]
    if kind == b'5' and value.endswith('/'):
        value = value[:-1]
    S.name(value)
    S.require(':' not in value, 'virtual-drive')
    return value


def control(data, package):
    S.require(0 < len(data) <= 65536 and b'\0' not in data, 'control-bound')
    fields, key = {}, None
    for line in data.decode('utf-8', 'strict').splitlines():
        if line.startswith((' ', '\t')):
            S.require(key is not None, 'control-continuation')
            fields[key] += ' '+line.strip()
        elif line:
            key, separator, value = line.partition(':')
            S.require(separator and re.fullmatch('[A-Za-z0-9-]{1,64}', key) and key.lower() not in fields and len(fields) < 128, 'control-field')
            key = key.lower()
            fields[key] = value.strip()
    S.require(fields.get('package') == package and fields.get('version') == REVISION and fields.get('architecture') == 'amd64', 'control-identity')
    deps = fields.get('depends', '')
    S.require(len(deps) <= 2048 and all(32 <= ord(c) <= 126 for c in deps), 'control-dependencies')
    return {'package': package, 'version': REVISION, 'architecture': 'amd64', 'depends': deps}


def tar_inventory(source, budget, package, category):
    seen, control_data, count = set(), None, 0
    while True:
        block = exact(source, 512)
        if block == bytes(512):
            S.require(exact(source, 512) == bytes(512), 'tar-end')
            while True:
                trailer = source.read(65536)
                if not trailer:
                    break
                S.require(not trailer.strip(b'\0'), 'tar-trailer')
            break
        budget.entry()
        count += 1
        S.require(A.number(block[148:156]) == sum(block[:148])+256+sum(block[156:]), 'tar-checksum')
        S.require(block[257:263] in (b'ustar\0', b'ustar '), 'tar-format')
        raw, kind, size = A.text(block[:100]), block[156:157], A.number(block[124:136])
        prefix = A.text(block[345:500]) if block[257:263] == b'ustar\0' else ''
        raw = prefix+'/'+raw if prefix else raw
        S.require(kind in (b'0', b'\0', b'5', b'1', b'2') and size <= 16*A.MIB, 'tar-type-member')
        name = virtual_name(raw, kind, size)
        S.require(name not in seen and (kind in (b'0', b'\0') or size == 0), 'tar-duplicate-size')
        seen.add(name)
        if category == 'data' and name != '.':
            S.require(name not in budget.paths or kind == budget.paths[name] == b'5', 'package-path-collision')
            budget.paths[name] = kind
        role = 'cli' if category == 'data' and package == 'php8.5-cli' and name == 'usr/bin/php8.5' else None
        match = re.fullmatch(r'usr/lib/php/[0-9]{8}/(mysqlnd|mysqli)\.so', name)
        if category == 'data' and package == 'php8.5-mysql' and match:
            role = match[1]
        selected = role is not None or (category == 'control' and name == 'control')
        S.require(not selected or kind in (b'0', b'\0'), 'selected-regular')
        S.require(not (category == 'control' and name == 'control') or size <= 65536, 'control-bound')
        payload, digest, remaining = bytearray(), hashlib.sha256(), size
        while remaining:
            chunk = source.read(min(remaining, 65536))
            S.require(chunk, 'tar-member-truncated')
            remaining -= len(chunk)
            digest.update(chunk)
            if selected:
                payload.extend(chunk)
        S.require(not exact(source, (-size) % 512).strip(b'\0'), 'tar-padding')
        link = A.text(block[157:257])
        budget.inventory.update(json.dumps([package, category, name, kind.hex(), size, digest.hexdigest(), link], separators=(',', ':')).encode('ascii')+b'\n')
        budget.types[kind.hex()] = budget.types.get(kind.hex(), 0)+1
        if role:
            S.require(role not in budget.roles, 'duplicate-role')
            budget.roles[role] = {'package': package, 'path': name, **S.elf(payload), 'versioned_symbols': 'Unknown'}
        elif selected:
            control_data = control(payload, package)
    S.require(category != 'control' or control_data is not None, 'control-missing')
    return {'entries': count, 'control': control_data} if category == 'control' else {'entries': count}
