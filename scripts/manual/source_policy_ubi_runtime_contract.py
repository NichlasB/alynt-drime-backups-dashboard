"""Dormant version-only launch/observation DATA. No operational owner or IO.

Copies of these frames, synthetic profile derivations and successful validators
are NOT fresh static provenance, engine readback, kernel proof or launch grants.
There is deliberately no executor, production caller, workflow or ready handle.
"""
import hashlib
import json
import re

MANIFEST = '14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1'
CONFIG = '2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35'
LAYERS = (
    ('2920d84eafa0cf94806ab58f0a2124f7b2d35bcbb06fc89a9106dcc28efe397a', 39653524),
    ('46735ccec920ba5e6df52c8787af94e3d7ea0fedb92ca37f7e0279c89506e13a', 4771),
    ('189b0ff547a3cff8241702bd70b6be360d4841e72a74e438b3bd60d530e43007', 2001814),
    ('d982fdd339edcf849f97671ec949c25b570de306244a2b6b03e6441a586ffc8a', 6944875),
    ('82c48ac2fca221a7aa5d650e52f057a21f73cec9e1202ff995e30276c620c276', 338),
    ('444b4ce536f188518cbcc061ab236a7e8db460af28e5f870fe200a7ba0790b6b', 332),
    ('617276d6715b91a6fedc8af16badcec2e0520cc2c04a2f3b169027a47057abfb', 100462402),
    ('e6a3432e4f830821a37b4186e566ae46c3d9f09844600cb3b78d469c8d7ad8d5', 116),
    ('d1540db65d371923fb149b6317345a5c5670c090f8506e30947d59463716d300', 4018),
    ('c1c3acb5fb6a522a08e27e306a765c58b15407644a6d204e2c57035a840bf07a', 8227),
)
RUNTIME_SOURCES = (
    'scripts/ci/source-policy-legacy-ubi-static.py', '.github/workflows/source-policy-legacy-ubi-static.yml',
    'scripts/manual/source-policy-linux-minimum-acquire.py', 'scripts/manual/source_policy_linux_minimum_static.py',
    'scripts/manual/source_policy_ubi_provenance.py', 'scripts/manual/source_policy_ubi_alias_static.py',
    'scripts/manual/source_policy_virtual_closure.py', 'scripts/manual/source_policy_ubi_elf_static.py',
    'scripts/manual/source_policy_ubi_import_contract.py', 'scripts/manual/source_policy_ubi_runtime_contract.py',
)
NETWORK_CALLS = frozenset(('socket', 'socketpair', 'socketcall', 'connect', 'bind', 'listen', 'accept', 'accept4',
                         'sendto', 'sendmsg', 'sendmmsg', 'recvfrom', 'recvmsg', 'recvmmsg', 'shutdown',
                         'getsockname', 'getpeername', 'setsockopt', 'getsockopt'))
# Proposed literal, exercised synthetically only. Do not normalize unknown output.
VERSION = b'mariadbd  Ver 10.6.23-MariaDB for Linux on x86_64 (MariaDB Server)\n'


class Refusal(ValueError):
    pass


def require(condition, reason):
    if not condition:
        raise Refusal(reason)


def digest(value):
    return type(value) is str and re.fullmatch('[a-f0-9]{64}', value) is not None


def encoded(value):
    return json.dumps(value, sort_keys=True, separators=(',', ':'), ensure_ascii=True).encode('ascii')


def same_data(actual, expected):
    """Exact JSON types as well as values; fixed expected tree bounds traversal."""
    pending = [(actual, expected)]; work = 0
    while pending:
        work += 1
        if work > 4096:
            return False
        actual, expected = pending.pop()
        if type(actual) is not type(expected):
            return False
        if type(expected) is dict:
            if len(actual) != len(expected) or not all(type(key) is str for key in actual) or set(actual) != set(expected):
                return False
            pending.extend((actual[key], value) for key, value in expected.items())
        elif type(expected) is list:
            if len(actual) != len(expected):
                return False
            pending.extend(zip(actual, expected))
        elif type(expected) not in (str, int, bool, type(None)) or actual != expected:
            return False
    return True


def context_frame(frame):
    keys = {'format', 'manifest', 'config', 'head', 'run', 'attempt', 'static_source', 'runtime_source', 'snapshot', 'graph', 'profile'}
    require(type(frame) is dict and set(frame) == keys, 'context-shape')
    require(frame['format'] == 'ADBURCONTEXT1' and frame['profile'] == 'ubi-version-only-v1'
            and frame['manifest'] == MANIFEST and frame['config'] == CONFIG, 'candidate-context')
    require(type(frame['head']) is str and re.fullmatch('[a-f0-9]{40}', frame['head']) is not None
            and type(frame['run']) is str and re.fullmatch('[1-9][0-9]{0,19}', frame['run']) is not None
            and type(frame['attempt']) is int and 1 <= frame['attempt'] <= 100000, 'run-context')
    require(all(digest(frame[key]) for key in ('static_source', 'runtime_source', 'snapshot', 'graph'))
            and frame['static_source'] != frame['runtime_source'], 'source-context')
    return dict(frame, launch_authority=False)


def runtime_source(rows):
    require(type(rows) is tuple and len(rows) == len(RUNTIME_SOURCES), 'source-cohort')
    require(all(type(row) is tuple and len(row) == 2 and row[0] == path and digest(row[1])
                for row, path in zip(rows, RUNTIME_SOURCES)), 'source-cohort')
    return hashlib.sha256(encoded(rows)).hexdigest()


def import_identity(frame, config_sha, layers):
    context_frame(frame)
    require(config_sha == CONFIG and type(layers) is tuple and len(layers) == 10, 'import-identity')
    require(all(type(row) is tuple and len(row) == 4 and type(row[1]) is int
                and row[:2] == expected and digest(row[2]) and type(row[3]) is int and 0 < row[3] <= 2*1024**3
                for row, expected in zip(layers, LAYERS)), 'import-layer-identity')
    require(sum(row[3] for row in layers) <= 2*1024**3, 'import-expanded-budget')
    return dict(format='ADBURIMPORT1', context=context_frame(frame), engine_content='sha256:'+CONFIG,
                layers=layers, launch_authority=False)


def restrict_policy(baseline):
    """Narrow finite synthetic default-ERRNO allowlists; not general seccomp."""
    require(type(baseline) is dict and set(baseline) == {'defaultAction', 'architectures', 'syscalls'}, 'policy-shape')
    require(baseline['defaultAction'] == 'SCMP_ACT_ERRNO' and baseline['architectures'] == ['SCMP_ARCH_X86_64'], 'policy-baseline')
    rules = baseline['syscalls']
    require(type(rules) is list and 0 < len(rules) <= 512, 'policy-rules')
    seen = set(); selected = []
    for row in rules:
        require(type(row) is dict and set(row) == {'names', 'action'} and row['action'] == 'SCMP_ACT_ALLOW', 'policy-rule')
        names = row['names']
        require(type(names) is list and 0 < len(names) <= 512 and len(seen)+len(names) <= 512, 'policy-names')
        for name in names:
            require(type(name) is str and re.fullmatch('[a-z_][a-z0-9_]{0,63}', name) is not None and name not in seen, 'policy-name')
            seen.add(name)
        kept = [name for name in names if name not in NETWORK_CALLS]
        if kept:
            selected.append(dict(names=kept, action='SCMP_ACT_ALLOW'))
    profile = dict(defaultAction='SCMP_ACT_ERRNO', architectures=['SCMP_ARCH_X86_64'], syscalls=selected)
    return dict(profile=profile, baseline_sha=hashlib.sha256(encoded(baseline)).hexdigest(),
                derived_sha=hashlib.sha256(encoded(profile)).hexdigest(), kernel_verified=False, launch_authority=False)


def _mounts():
    return [dict(destination=path, type='tmpfs', readonly=True, options=['nosuid', 'nodev', 'noexec'], bytes=size)
            for path, size in (('/var/lib/mysql', 256*1024*1024), ('/tmp', 16*1024*1024))]


def launch_plan(frame, uid, baseline):
    context_frame(frame)
    require(type(uid) is int and 1 <= uid <= 2147483647, 'nonroot-user')
    policy = restrict_policy(baseline)
    return dict(format='ADBURPLAN1', context=dict(frame), image='sha256:'+CONFIG, user='%d:%d' % (uid, uid),
                argv=['/usr/sbin/mariadbd', '--no-defaults', '--version'], workdir='/', stdin=False, tty=False,
                environment=['PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin', 'container=oci',
                             'GOSU_VERSION=1.17', 'MARIADB_VERSION=10.6.23'],
                readonly=True, mounts=_mounts(), network='none', pid='private', ipc='private',
                capabilities=[], supplementary_groups=[], devices=[], privileged=False, ports=[], restart='no',
                no_new_privileges=True, healthcheck=False, memory=512*1024*1024, pids=64, cpus=1,
                seccomp=policy, required_readbacks=['engine-import', 'effective-container', 'closure-files', 'effect-controls'],
                launch_authority=False)


def validate_plan(plan, frame, uid, baseline):
    require(same_data(plan, launch_plan(frame, uid, baseline)), 'launch-data-mismatch')
    return True  # DATA equality only. No dispatcher consumes this result.


def owner_name(frame):
    context_frame(frame)
    return 'adb-ubi-version-'+frame['run']+'-'+str(frame['attempt'])+'-'+frame['head'][:12]


def owner_labels(frame):
    context_frame(frame)
    return {'adb.scope': 'ubi-version-only-v1', 'adb.head': frame['head'], 'adb.run': frame['run'],
            'adb.attempt': str(frame['attempt']), 'adb.source': frame['runtime_source']}


def owned_object(row, frame, cid):
    context_frame(frame)
    require(digest(cid) and type(row) is dict and set(row) == {'cid', 'name', 'labels', 'image', 'mounts', 'state'}, 'object-shape')
    require(row['cid'] == cid and row['name'] == owner_name(frame) and row['labels'] == owner_labels(frame)
            and row['image'] == 'sha256:'+CONFIG and same_data(row['mounts'], _mounts())
            and row['state'] in ('created', 'running', 'exited', 'dead'), 'object-ownership')
    return True  # Caller DATA is not independent inspection or cleanup authority.


class Capture:
    """Bounded interleaved DATA feeds; no process, clock or stream drainage IO."""
    def __init__(self, frame, meter, cid, containment_sha):
        self.frame = context_frame(frame)
        require(digest(cid) and digest(containment_sha), 'observation-identity')
        self.cid, self.containment_sha = cid, containment_sha
        meter.reserve(15000)
        self.meter, self.start = meter, meter.now
        self.counts = dict(stdout=0, stderr=0)
        self.hashes = dict(stdout=hashlib.sha256(), stderr=hashlib.sha256())
        self.output = bytearray()
        self.failure = None
        self.closed = False

    def fail(self, reason):
        if self.failure is None:
            self.failure = reason
        raise Refusal(self.failure)

    def feed(self, channel, chunk, now):
        if self.closed or self.failure is not None:
            self.fail('observation-closed')
        if channel not in ('stdout', 'stderr') or type(chunk) is not bytes:
            self.fail('output-shape')
        try:
            self.meter.tick(now)
        except ValueError:
            self.fail('clock-budget')
        if now-self.start > 15000:
            self.fail('candidate-timeout')
        if sum(self.counts.values())+len(chunk) > 16384:
            self.fail('output-budget')
        try:
            self.meter.charge('work', 1); self.meter.charge('observation', len(chunk))
        except ValueError:
            self.fail('shared-budget')
        self.hashes[channel].update(chunk); self.counts[channel] += len(chunk)
        if channel == 'stdout' and len(self.output) <= len(VERSION):
            self.output.extend(chunk[:len(VERSION)+1-len(self.output)])

    def finish(self, exit_code, oom, timed_out, primary, independent, now):
        require(not self.closed, 'observation-closed')
        require(type(now) is int and self.start <= now <= 600000, 'terminal-clock')
        require((exit_code is None or type(exit_code) is int and 0 <= exit_code <= 255)
                and type(oom) is bool and type(timed_out) is bool
                and primary in ('absent', 'failed', 'unknown') and independent in ('absent', 'failed', 'unknown'), 'terminal-shape')
        try:
            self.meter.tick(now)
        except ValueError:
            if self.failure is None:
                self.failure = 'clock-budget'
        valid = (self.failure is None and now-self.start <= 15000 and exit_code == 0 and not oom and not timed_out
                 and self.counts['stderr'] == 0 and self.counts['stdout'] == len(VERSION) and bytes(self.output) == VERSION)
        clean = primary == independent == 'absent'
        result = dict(format='ADBUROBS1', context=self.frame, scope='version-only-data', phase='candidate-version-data',
                      cid=self.cid, engine_content='sha256:'+CONFIG, containment_sha=self.containment_sha,
                      argv_sha=hashlib.sha256(encoded(['/usr/sbin/mariadbd', '--no-defaults', '--version'])).hexdigest(),
                      elapsed_ms=now-self.start,
                      counts=dict(self.counts), hashes={key:value.hexdigest() for key, value in self.hashes.items()},
                      exit=exit_code, oom=oom, timeout=timed_out, cleanup_primary=primary, cleanup_independent=independent,
                      failure=self.failure or (None if valid and clean else 'cleanup-unverified' if valid else 'observation-refused'),
                      observation_valid_data=valid and clean, launch_authority=False)
        require(len(encoded(result)) <= 65536, 'summary-budget')
        self.closed = True
        self.output.clear()
        return result
