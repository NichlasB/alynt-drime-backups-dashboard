"""One fixed archive as DATA. No member extraction, installation or execution."""
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import re
import signal
import stat
import struct
import subprocess
import sys
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[2]
SPEC = importlib.util.spec_from_file_location('static_data', ROOT / 'scripts/manual/source_policy_linux_minimum_static.py')
S = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(S)
MIB = 1024 * 1024
SIZE = 41599355
SHA = '6eec9def9f436e192051dda270ed531be5e4dabb87ad3bcbe5c8d24fad6ccab5'
URL = 'https://github.com/shivammathur/php-ubuntu/releases/download/builds/php_8.5.12-nts%2Bubuntu24.04.tar.zst'
# Concurrent address-space ceilings sum to the unchanged 64 MiB ceiling.
PARENT_MEMORY, DECODER_MEMORY, WINDOW = 40*MIB, 24*MIB, 8*MIB
EXPANDED = 192*MIB - SIZE
WORKFLOW = 'source-policy-php-archive-static.yml'


def frame(data):
    S.require(len(data) >= 18 and data[:4] == b'\x28\xb5\x2f\xfd', 'zstd-frame')
    flag = data[4]
    S.require(flag & 0x18 == 0 and flag & 3 == 0, 'zstd-reserved-dictionary')
    single, size_flag, offset = bool(flag & 32), flag >> 6, 5
    window = None
    if not single:
        descriptor = data[offset]
        base = 1 << (10 + (descriptor >> 3))
        window = base + (base >> 3)*(descriptor & 7)
        offset += 1
    width = (1 if single else 0, 2, 4, 8)[size_flag]
    content = int.from_bytes(data[offset:offset+width], 'little') if width else None
    if size_flag == 1:
        content += 256
    if single:
        window = content
    S.require(window is not None and window > 0, 'zstd-window-shape')
    return {'window_bytes': window, 'declared_content_bytes': content, 'checksum': bool(flag & 4)}


def admit_frame(facts):
    S.require(facts['window_bytes'] <= WINDOW, 'decoder-window-budget')
    S.require(facts['declared_content_bytes'] is None or facts['declared_content_bytes'] <= EXPANDED, 'declared-expanded-budget')


def safe_url(value):
    parsed = urllib.parse.urlsplit(value)
    S.require(parsed.scheme == 'https' and parsed.hostname in ('github.com', 'release-assets.githubusercontent.com', 'objects.githubusercontent.com')
              and parsed.port in (None, 443) and not parsed.username and not parsed.password and not parsed.fragment, 'delivery-origin')
    return value


class Redirects(urllib.request.HTTPRedirectHandler):
    def __init__(self):
        super().__init__()
        self.count = 0

    def redirect_request(self, req, fp, code, msg, headers, newurl):
        self.count += 1
        S.require(self.count <= 3, 'delivery-redirect-bound')
        return super().redirect_request(req, fp, code, msg, headers, safe_url(newurl))


def target(env):
    S.require(env.get('GITHUB_ACTIONS') == 'true' and env.get('GITHUB_EVENT_NAME') == 'workflow_dispatch'
              and env.get('GITHUB_REPOSITORY') == 'NichlasB/alynt-drime-backups-dashboard'
              and env.get('GITHUB_REF') == 'refs/heads/master' and env.get('GITHUB_ACTOR') == 'NichlasB'
              and env.get('GITHUB_WORKFLOW_REF') == 'NichlasB/alynt-drime-backups-dashboard/.github/workflows/'+WORKFLOW+'@refs/heads/master'
              and re.fullmatch('[a-f0-9]{40}', env.get('EXPECTED_SHA', ''))
              and env['EXPECTED_SHA'] == env.get('GITHUB_SHA') and env.get('GITHUB_RUN_ATTEMPT') == '1'
              and re.fullmatch('[0-9]+', env.get('GITHUB_RUN_ID', '')), 'exact-job')
    parent = Path(env['RUNNER_TEMP'])
    S.require(parent.is_absolute() and parent.resolve() == parent and not parent.is_symlink(), 'temporary-parent')
    return parent / ('adb-php-archive-'+env['GITHUB_RUN_ID'])


def owner(root):
    return json.dumps({'root': root.name, 'head': os.environ['EXPECTED_SHA']}, sort_keys=True).encode('ascii')


def cleanup(root):
    if not root.exists() and not root.is_symlink():
        return 0
    info = root.lstat()
    S.require(stat.S_ISDIR(info.st_mode) and not root.is_symlink() and root.resolve() == root
              and info.st_uid == os.getuid() and stat.S_IMODE(info.st_mode) == 0o700, 'owned-root')
    children = list(root.iterdir())
    S.require(len(children) <= 2 and {p.name for p in children} <= {'owner.json', 'archive.zst'}, 'owned-membership')
    for path in children:
        entry = path.lstat()
        S.require(stat.S_ISREG(entry.st_mode) and entry.st_uid == os.getuid() and entry.st_nlink == 1, 'owned-file')
    marker = root/'owner.json'
    S.require(marker.stat().st_size <= 256 and marker.read_bytes() == owner(root), 'owned-marker')
    for path in children:
        if path != marker:
            path.unlink()
    marker.unlink()
    root.rmdir()
    S.require(not root.exists(), 'cleanup-remaining')
    return 0


def download(path):
    digest, size = hashlib.sha256(), 0
    opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), Redirects())
    with opener.open(safe_url(URL), timeout=10) as response, path.open('xb') as output:
        S.require(response.status == 200 and response.headers.get('Content-Encoding') in (None, 'identity')
                  and response.headers.get('Content-Length') == str(SIZE), 'delivery-response')
        while True:
            chunk = response.read(65536)
            if not chunk:
                break
            size += len(chunk)
            S.require(size <= SIZE, 'compressed-size')
            digest.update(chunk)
            output.write(chunk)
    S.require(size == SIZE and digest.hexdigest() == SHA, 'archive-identity')
    return {'bytes': size, 'sha256': digest.hexdigest()}


def decoder_limit():
    import resource
    resource.setrlimit(resource.RLIMIT_AS, (DECODER_MEMORY, DECODER_MEMORY))
    resource.setrlimit(resource.RLIMIT_CPU, (60, 60))
    resource.setrlimit(resource.RLIMIT_CORE, (0, 0))


def decoder_environment():
    # Retain runner-owned process reconciliation, without forwarding credentials.
    result = {'PATH': '/usr/bin:/bin'}
    if 'RUNNER_TRACKING_ID' in os.environ:
        result['RUNNER_TRACKING_ID'] = os.environ['RUNNER_TRACKING_ID']
    return result


def preflight():
    import resource
    S.require(sys.platform == 'linux' and struct.calcsize('P') == 8 and Path('/usr/bin/zstd').is_file(), 'existing-decoder')
    resource.setrlimit(resource.RLIMIT_AS, (PARENT_MEMORY, PARENT_MEMORY))
    resource.setrlimit(resource.RLIMIT_CORE, (0, 0))
    # One synthetic raw-block frame: decode DATA only, not a candidate program.
    sample = b'\x28\xb5\x2f\xfd\x20\x01\x09\x00\x00x'
    result = subprocess.run(['/usr/bin/zstd', '-d', '-q', '-M8MB', '-c'], input=sample,
                            stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, timeout=5,
                            preexec_fn=decoder_limit, env=decoder_environment())
    S.require(result.returncode == 0 and result.stdout == b'x', 'decoder-cap-preflight')
    return {'parent_address_space': PARENT_MEMORY, 'decoder_address_space': DECODER_MEMORY, 'decoder_window': WINDOW, 'synthetic_child_reaped': True}


class Reader:
    def __init__(self, source):
        self.source, self.total = source, 0

    def read(self, count):
        S.require(0 <= count <= 65536, 'read-bound')
        data = self.source.read(count)
        self.total += len(data)
        S.require(self.total <= EXPANDED, 'expanded-budget')
        return data

    def exact(self, count):
        S.require(0 <= count <= 512, 'header-bound')
        data = self.read(count)
        S.require(len(data) == count, 'archive-truncated')
        return data


def number(raw):
    S.require(re.fullmatch(b'[0-7]+[\x00 ]*', raw) is not None, 'tar-number')
    return int(raw.rstrip(b'\0 '), 8)


def text(raw):
    value, _, rest = raw.partition(b'\0')
    S.require(not rest.strip(b'\0'), 'tar-text-padding')
    return value.decode('ascii', 'strict')


def inventory(source):
    reader, seen, roles, types = Reader(source), set(), {}, {}
    digest = hashlib.sha256()
    for entry in range(8193):
        block = reader.exact(512)
        if block == bytes(512):
            S.require(reader.exact(512) == bytes(512), 'tar-end')
            while True:
                trailer = reader.read(65536)
                if not trailer:
                    break
                S.require(not trailer.strip(b'\0'), 'tar-trailer')
            return {'entries': len(seen), 'expanded_bytes': reader.total, 'inventory_sha256': digest.hexdigest(), 'types': types, 'roles': roles}
        S.require(entry < 8192 and number(block[148:156]) == sum(block[:148])+256+sum(block[156:]), 'tar-header')
        S.require(block[257:263] in (b'ustar\0', b'ustar '), 'tar-format')
        name = text(block[:100])
        prefix = text(block[345:500]) if block[257:263] == b'ustar\0' else ''
        name = prefix+'/'+name if prefix else name
        kind, size = block[156:157], number(block[124:136])
        S.require(kind in (b'0', b'\0', b'5', b'1', b'2'), 'tar-type-pax-device-unsupported')
        if kind == b'5' and name.endswith('/'):
            name = name[:-1]
        S.name(name)
        S.require(':' not in name and name not in seen and size <= 16*MIB, 'tar-path-member-bound')
        seen.add(name)
        S.require(kind in (b'0', b'\0') or size == 0, 'tar-nonregular-size')
        link = text(block[157:257])
        types[kind.hex()] = types.get(kind.hex(), 0)+1
        role = ('cli' if name == 'usr/bin/php8.5' else 'mysqlnd' if name.endswith('/mysqlnd.so') else 'mysqli' if name.endswith('/mysqli.so') else None)
        sha, data = hashlib.sha256(), bytearray()
        remaining = size
        while remaining:
            chunk = reader.read(min(remaining, 65536))
            S.require(chunk, 'member-truncated')
            remaining -= len(chunk)
            sha.update(chunk)
            if role:
                data.extend(chunk)
        padding = (-size) % 512
        S.require(not reader.exact(padding).strip(b'\0'), 'tar-padding')
        digest.update(json.dumps([name, kind.hex(), size, sha.hexdigest(), link], separators=(',', ':')).encode('ascii')+b'\n')
        if role:
            S.require(role not in roles and kind in (b'0', b'\0'), 'selected-role-type')
            roles[role] = {'path': name, 'kind': 'regular', **S.elf(data), 'versioned_symbols': 'Unknown'}
    raise S.Refusal('entry-budget')


def scan(path):
    process = subprocess.Popen(['/usr/bin/zstd', '-d', '-q', '-M8MB', '-c', str(path)],
                               stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, preexec_fn=decoder_limit,
                               env=decoder_environment())
    try:
        result = inventory(process.stdout)
        S.require(process.wait(timeout=5) == 0, 'decoder-result')
    finally:
        if process.poll() is None:
            process.kill()
        process.wait(timeout=5)
        process.stdout.close()
    return {**result, 'decoder_reaped': True}


def main():
    root, receipt = target(os.environ), {'format': 'ADBPHPARCHIVE1', 'qualified': False, 'executed': False, 'extracted': False}
    def expired(signum, current):
        raise S.Refusal('job-deadline')
    signal.signal(signal.SIGALRM, expired)
    if sys.argv[1:] == ['--cleanup']:
        signal.alarm(30)
        cleanup(root)
        signal.alarm(0)
        print('{"owned_archive_remaining":0}')
        return 0
    S.require(sys.argv[1:] == ['--approved-static-proof'], 'entry-mode')
    signal.alarm(540)
    created = False
    try:
        S.require(not root.exists() and not root.is_symlink(), 'preexisting-owned-root')
        receipt['preflight'] = preflight()
        root.mkdir(mode=0o700)
        created = True
        with (root/'owner.json').open('xb') as marker:
            marker.write(owner(root))
        receipt['archive'] = download(root/'archive.zst')
        with (root/'archive.zst').open('rb') as archive:
            receipt['frame'] = frame(archive.read(18))
        admit_frame(receipt['frame'])
        receipt['inventory'] = scan(root/'archive.zst')
        S.require(set(receipt['inventory']['roles']) == {'cli', 'mysqlnd', 'mysqli'}, 'selected-roles-missing')
        receipt['reason'] = 'static-inventory-only-dependency-closure-unverified'
    except S.Refusal as error:
        receipt['reason'] = str(error)
    except Exception as error:
        receipt['reason'] = 'bounded-proof-error'
        receipt['error_class'] = type(error).__name__
    finally:
        signal.alarm(0)
        signal.alarm(30)
        if created:
            cleanup(root)
        receipt['owned_archive_remaining'] = 'Unknown' if root.exists() or root.is_symlink() else 0
        signal.alarm(0)
    output = json.dumps(receipt, sort_keys=True)
    S.require(len(output.encode('ascii')) <= 65536, 'diagnostic-bound')
    print(output)
    return 1 if 'inventory' not in receipt or receipt['reason'] != 'static-inventory-only-dependency-closure-unverified' else 0


if __name__ == '__main__':
    sys.exit(main())
