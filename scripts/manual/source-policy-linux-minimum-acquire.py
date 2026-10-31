"""One explicitly approved static acquisition. No Docker, extraction or execution."""
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import re
import ssl
import stat
import sys
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

HERE = Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('adb_linux_static', HERE / 'source_policy_linux_minimum_static.py')
static = importlib.util.module_from_spec(spec)
spec.loader.exec_module(static)
require, MIB = static.require, static.MIB
CATALOG = (
    ('wordpress', '08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4',
     'de3b9412d904e00792e7a1504f6bc38c17f44451c18bf75515b97e5f9d0bfe18',
     ('usr/local/bin/php', 'usr/local/lib/php/extensions/no-debug-non-zts-20190902/mysqli.so')),
    ('mariadb', '161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5',
     'b5077e20185da2defdc68763c9f87e9ce6c449207f9e8de120dee346b54ce696',
     ('usr/sbin/mariadbd',)),
)
ACCEPT = ', '.join(('application/vnd.docker.distribution.manifest.v2+json', 'application/vnd.oci.image.manifest.v1+json'))


def file_hash(path):
    result = hashlib.sha256()
    with path.open('rb') as stream:
        while True:
            data = stream.read(MIB)
            if not data:
                break
            result.update(data)
    return result.hexdigest()


class Owned:
    def __init__(self):
        self.parent = Path(tempfile.gettempdir()).resolve()
        self.root = Path(tempfile.mkdtemp(prefix='adb-linux-minimum-static-', dir=self.parent))
        self.files, self.reserved, self.total = set(), set(), 0

    def path(self, filename):
        require(re.fullmatch(r'[a-z0-9-]{1,90}\.(json|blob|zip)', filename) and len(self.files) < 96, 'owned-name-bound')
        require(filename not in self.reserved, 'owned-no-overwrite')
        self.reserved.add(filename)
        return self.root / filename

    def write(self, filename, data):
        path = self.path(filename)
        require(self.total + len(data) <= 512 * MIB, 'staging-byte-bound')
        with path.open('xb') as output:
            self.files.add(filename)  # Ownership begins only after exclusive creation succeeds.
            output.write(data)
        self.total += len(data)
        return path

    def cleanup(self):
        info = self.root.lstat()
        require(stat.S_ISDIR(info.st_mode) and not getattr(info, 'st_file_attributes', 0) & 0x400
                and not self.root.is_symlink() and self.root.resolve().parent == self.parent
                and re.fullmatch(r'adb-linux-minimum-static-[a-z0-9_]{8}', self.root.name), 'owned-root-changed')
        children = []
        for path in self.root.iterdir():
            entry = path.lstat()
            require(len(children) < 96 and path.name in self.files and stat.S_ISREG(entry.st_mode)
                    and not path.is_symlink() and not getattr(entry, 'st_file_attributes', 0) & 0x400
                    and entry.st_nlink == 1, 'owned-membership-changed')
            children.append(path)
        for path in children:
            path.unlink()
        self.root.rmdir()
        require(not self.root.exists(), 'owned-cleanup-unconfirmed')
        return len(children)


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


def redirect_allowed(url):
    parsed = urllib.parse.urlsplit(url)
    return (len(url) <= 16384 and parsed.scheme == 'https'
            and parsed.hostname == 'production.cloudfront.docker.com'
            and parsed.port in (None, 443) and not parsed.username and not parsed.password and not parsed.fragment)


class Client:
    def __init__(self, owner, deadline):
        require(not any(os.environ.get(key) for key in ('SSLKEYLOGFILE', 'SSL_CERT_FILE', 'SSL_CERT_DIR')), 'trust-override-refused')
        self.owner, self.deadline, self.metadata, self.payload = owner, deadline, 0, 0
        context = ssl.create_default_context()
        require(context.check_hostname and context.verify_mode == ssl.CERT_REQUIRED, 'tls-profile')
        self.opener = urllib.request.build_opener(urllib.request.ProxyHandler({}),
                                                 urllib.request.HTTPSHandler(context=context), NoRedirect())

    def get(self, url, limit, token=None, path=None, expected_size=None, expected_hash=None, blob=False):
        require(time.monotonic() < self.deadline and url.startswith('https://'), 'request-preflight')
        host = urllib.parse.urlsplit(url).hostname
        accept = ACCEPT if host == 'registry-1.docker.io' else ('application/zip' if host == 'codeload.github.com' else 'application/json')
        headers = {'Accept': accept, 'Accept-Encoding': 'identity', 'User-Agent': 'Alynt-owned-linux-static/1'}
        if token:
            require(urllib.parse.urlsplit(url).hostname == 'registry-1.docker.io', 'token-destination')
            headers['Authorization'] = 'Bearer ' + token
        started = time.monotonic()
        request = urllib.request.Request(url, headers=headers)
        try:
            response = self.opener.open(request, timeout=10)
        except urllib.error.HTTPError as error:
            location = error.headers.get('Location', '')
            code = error.code
            error.close()
            require(blob and code == 307 and redirect_allowed(location), 'http-or-redirect-refused')
            # Fresh request: Authorization is never forwarded to the CDN.
            response = self.opener.open(urllib.request.Request(location, headers={
                'Accept-Encoding': 'identity', 'User-Agent': 'Alynt-owned-linux-static/1'}), timeout=10)
        data, result, count = bytearray(), hashlib.sha256(), 0
        output = None
        try:
            if path:
                require(path.parent == self.owner.root and path.name in self.owner.reserved
                        and path.name not in self.owner.files, 'owned-output-scope')
            output = path.open('xb') if path else None
            if output:
                self.owner.files.add(path.name)
            with response:
                require(response.status == 200 and response.headers.get('Content-Encoding', 'identity') == 'identity', 'http-response')
                declared = response.headers.get('Content-Length')
                if declared is not None:
                    require(declared.isdigit() and int(declared) <= limit
                            and (expected_size is None or int(declared) == expected_size), 'http-size-header')
                while True:
                    require(time.monotonic() - started < 180 and time.monotonic() < self.deadline, 'request-time-bound')
                    block = response.read1(MIB)
                    if not block:
                        break
                    count += len(block)
                    require(count <= limit, 'response-byte-bound')
                    if output:
                        self.owner.total += len(block)
                        self.payload += len(block)
                        require(self.owner.total <= 512 * MIB, 'staging-byte-bound')
                        output.write(block)
                    else:
                        self.metadata += len(block)
                        require(self.metadata <= 32 * MIB, 'metadata-total-bound')
                        data.extend(block)
                    result.update(block)
            require((expected_size is None or count == expected_size)
                    and (expected_hash is None or result.hexdigest() == expected_hash), 'download-identity')
        finally:
            response.close()
            if output:
                output.close()
        return bytes(data) if path is None else {'bytes': count, 'sha256': result.hexdigest()}


def source_pins():
    text = (HERE / 'source-policy-supported-core-pins.php').read_text(encoding='utf-8')
    require(len(text) <= 65536, 'source-pin-file-bound')
    section = text.split("'6.0'   => array(", 1)[1].split("'7.1.3' => array(", 1)[0]
    pins = dict(re.findall(r"'([a-z-]+\.php)'\s*=>\s*'([a-f0-9]{64})'", section))
    require(len(pins) == 11 and static.CORE_COMMIT in section, 'source-pin-set')
    return {'wp-includes/' + key: value for key, value in pins.items()}


def proof():
    owner, phase = None, 'preflight'
    report = {'status': 'refused', 'candidate_execution': False, 'database_operations': False,
              'docker_image_loading': False, 'rootfs_extraction': False}
    frozen = {path: file_hash(path) for path in (Path(__file__), Path(static.__file__),
              HERE / 'source-policy-supported-core-pins.php', HERE / 'source-policy-linux-minimum-tuple.php', Path(sys.executable))}
    deadline = time.monotonic() + 1200
    try:
        require(sys.flags.isolated and sys.dont_write_bytecode, 'isolated-tool-flags')
        owner = Owned()
        client = Client(owner, deadline)
        report['owned_child'] = owner.root.name
        report['tool'] = {'python': sys.version.split()[0], 'ssl': ssl.OPENSSL_VERSION}
        plans = []
        for repo, pin, config_pin, roles in CATALOG:
            phase = repo + '-metadata'
            token = static.decode(client.get('https://auth.docker.io/token?service=registry.docker.io&scope=repository:library/' + repo + ':pull', 65536))['token']
            require(isinstance(token, str) and len(token) <= 16384, 'anonymous-read-token')
            base = 'https://registry-1.docker.io/v2/library/' + repo
            raw = client.get(base + '/manifests/sha256:' + pin, 2 * MIB, token, expected_hash=pin)
            manifest = static.decode(raw)
            owner.write(repo + '-manifest.json', raw)
            require(manifest.get('schemaVersion') == 2 and isinstance(manifest.get('layers'), list)
                    and 0 < len(manifest['layers']) <= 32, 'manifest-shape')
            config_hash, config_size = static.descriptor(manifest['config'], 2 * MIB)
            require(config_hash == config_pin, 'config-pin')
            raw = client.get(base + '/blobs/sha256:' + config_pin, 2 * MIB, token,
                             expected_size=config_size, expected_hash=config_pin, blob=True)
            config = static.decode(raw)
            owner.write(repo + '-config.json', raw)
            require(config.get('os') == 'linux' and config.get('architecture') == 'amd64'
                    and config['rootfs']['type'] == 'layers'
                    and len(config['rootfs']['diff_ids']) == len(manifest['layers']), 'config-profile')
            env = config['config']['Env']
            require(isinstance(env, list) and len(env) <= 128
                    and all(isinstance(item, str) and '=' in item and len(item) <= 2048 for item in env), 'config-environment-bound')
            environment = dict(item.split('=', 1) for item in env)
            require(len(environment) == len(env), 'config-environment-duplicate')
            require(repo != 'wordpress' or environment.get('PHP_VERSION') == '7.4.33', 'php-version-declaration')
            require(config['config'].get('Entrypoint') == ['docker-entrypoint.sh']
                    and config['config'].get('Cmd') == (['php-fpm'] if repo == 'wordpress' else ['mariadbd'])
                    and config['config'].get('Volumes') == ({'/var/www/html': {}} if repo == 'wordpress' else {'/var/lib/mysql': {}}), 'config-entry-volume-profile')
            total = 0
            for layer in manifest['layers']:
                sha, size = static.descriptor(layer, 128 * MIB)
                require(layer.get('mediaType') in ('application/vnd.docker.image.rootfs.diff.tar.gzip',
                        'application/vnd.oci.image.layer.v1.tar+gzip'), 'layer-media-type')
                total += size
            plans.append((repo, base, token, manifest, config, roles))
            report[repo] = {'manifest': pin, 'config': config_pin, 'layers': len(manifest['layers']), 'compressed_bytes': total,
                'version_declarations': {k: v for k, v in environment.items() if k in ('PHP_VERSION', 'PHP_INI_DIR', 'MARIADB_VERSION')},
                'entrypoint': config['config'].get('Entrypoint'), 'cmd': config['config'].get('Cmd'),
                'volumes': config['config'].get('Volumes'), 'runtime_observed': False}
        require(sum(report[repo]['compressed_bytes'] for repo, *_ in CATALOG) + 64 * MIB <= 512 * MIB, 'planned-download-bound')
        phase = 'core-tree-metadata'
        metadata = static.decode(client.get('https://api.github.com/repos/WordPress/WordPress/git/trees/' + static.CORE_TREE + '?recursive=1', 2 * MIB))
        modes, pins = static.core_modes(metadata), source_pins()
        print(json.dumps({'phase': 'metadata-qualified', 'image_layers': sum(len(p[3]['layers']) for p in plans), 'core_files': len(modes)}), flush=True)
        budget = {'expanded': 0, 'entries': 0, 'deadline': deadline}
        downloaded = {}
        for repo, base, token, manifest, config, wanted in plans:
            roles, scans = {}, []
            for i, layer in enumerate(manifest['layers']):
                phase = repo + '-layer-' + str(i)
                sha, size = static.descriptor(layer, 128 * MIB)
                if sha in downloaded:
                    path = downloaded[sha]
                    require(path.stat().st_size == size and file_hash(path) == sha, 'owned-cache-identity')
                else:
                    path = owner.path(sha + '.blob')
                    client.get(base + '/blobs/sha256:' + sha, 128 * MIB, token, path, size, sha, True)
                    downloaded[sha] = path
                scans.append(static.scan_layer(path, config['rootfs']['diff_ids'][i], wanted, roles, budget))
                print(json.dumps({'phase': phase, 'compressed_verified': size, 'expanded_verified': scans[-1]['expanded_bytes']}), flush=True)
            require(set(roles) == set(wanted), 'critical-role-set')
            report[repo]['roles'], report[repo]['scans'] = roles, scans
        phase = 'core-archive'
        path = owner.path('core.zip')
        report['core_download'] = client.get('https://codeload.github.com/WordPress/WordPress/zip/' + static.CORE_COMMIT, 64 * MIB, path=path)
        report['core'] = static.core_zip(path, modes, pins)
        report['archive_scan'] = {k: budget[k] for k in ('expanded', 'entries')}
        report['downloaded_payload_bytes'], report['staged_bytes'] = client.payload, owner.total
        report['status'] = 'static-verified'
    except Exception as error:
        report['failure_stage'] = phase
        report['failure'] = str(error) if isinstance(error, static.Refusal) else 'unexpected-static-failure'
        if isinstance(error, static.PathRefusal):
            report['path_refusal'] = error.facts
    finally:
        try:
            report['removed_owned_files'] = owner.cleanup() if owner else 0
            report['cleanup'] = 'confirmed'
        except Exception:
            report['cleanup'] = 'unconfirmed'
            report['retained_root'] = str(owner.root)
            report['status'] = 'refused'
        report['source_unchanged'] = all(file_hash(path) == sha for path, sha in frozen.items())
        if not report['source_unchanged']:
            report['status'] = 'refused'
        report['source_image_sha256'] = hashlib.sha256(json.dumps({path.name: sha for path, sha in frozen.items()}, sort_keys=True).encode()).hexdigest()
    return report


if __name__ == '__main__':
    if sys.argv[1:] != ['--approved-static-artifacts']:
        print('Requires explicitly approved owned static artifacts', file=sys.stderr)
        sys.exit(2)
    result = proof()
    print(json.dumps(result, sort_keys=True))
    sys.exit(0 if result['status'] == 'static-verified' and result['cleanup'] == 'confirmed' else 1)
