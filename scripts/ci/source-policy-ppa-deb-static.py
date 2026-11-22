"""ONE fixed PPA tuple: DATA inventory and owned cleanup, never installation."""
import hashlib
import errno
import gc
import json
import os
from pathlib import Path
import re
import signal
import stat
import subprocess
import ssl
import sys
import urllib.error
import urllib.request
import weakref

sys.path.insert(0, str(Path(__file__).resolve().parent))
from source_policy_deb_data import A, S, PINS, DELIVERIES, REVISION, Budget, Slice, ar_members, tar_inventory
from source_policy_deb_stream import Decoder, preflight

WORKFLOW = 'source-policy-ppa-deb-static.yml'


def http_refusal(error):
    # Status only: never URL, headers, message, body or transport credentials.
    code = error.code
    return {'reason': 'ppa-http-refusal', 'http_status': code if type(code) is int and 100 <= code <= 599 else 'Unknown'}


def memory_site(error):
    # Finite owned-code labels only; never traceback text, paths, locals or DATA.
    sites = {tar_inventory.__code__: 'tar-inventory', S.elf.__code__: 'elf-data',
             ar_members.__code__: 'ar-index', Decoder.__init__.__code__: 'decoder-setup',
             Decoder.read.__code__: 'decoder-read', download.__code__: 'acquisition',
             acquire.__code__: 'acquisition'}
    trace, label = error.__traceback__, 'Unknown'
    for depth in range(32):
        if trace is None:
            return label
        label = sites.get(trace.tb_frame.f_code, label)
        trace = trace.tb_next
    return 'Unknown'


def filename(package):
    S.require(package in {row[0] for row in PINS}, 'package-selection')
    return package+'_'+REVISION+'_amd64.deb'


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
    return parent/('adb-ppa-deb-'+env['GITHUB_RUN_ID'])


def marker(root):
    return json.dumps({'head': os.environ['EXPECTED_SHA'], 'root': root.name}, sort_keys=True).encode('ascii')


def _descriptor_sources(budget):
    """Fixed private construction; trusted owner releases writers/transport first."""
    root = target(os.environ)
    expected = marker(root)
    S.require(type(budget) is Budget, 'descriptor-owner-budget')
    import source_policy_deb_sources as L
    import source_policy_elf_ranges as E
    return L._SourceSet(str(root.parent), root.name, expected,
                        tuple(filename(pin[0]) for pin in PINS), budget, E.Budget())


def _descriptor_preparation(budget):
    """Fixed manual DATA qualification only; never production or candidate execution."""
    import source_policy_deb_replay as R
    sources = _descriptor_sources(budget)
    try:
        return R._LeasePreparation(sources).run()
    except R.E.Refusal as error:
        raise S.Refusal(str(error)) from None
    finally:
        sources.close()


def _fd_state():
    """Bounded metadata of THIS process only; never emit numbers or paths."""
    result = {}
    with os.scandir('/proc/self/fd') as entries:
        for entry in entries:
            S.require(len(result) < 128 and entry.name.isascii() and entry.name.isdecimal(), 'descriptor-proc-bound')
            info = os.fstat(int(entry.name))
            result[int(entry.name)] = (info.st_dev, info.st_ino, info.st_mode)
    return result


def _children_empty():
    with open('/proc/self/task/'+str(os.getpid())+'/children', 'rb') as source:
        S.require(source.read(4097) == b'', 'descriptor-child-remaining')


def _must_refuse(action, reason):
    try:
        action()
    except S.Refusal as error:
        if str(error) != reason:
            raise  # Preserve the real finite prerequisite refusal,not a new layer.
    else:
        raise S.Refusal('descriptor-guard-unavailable')


def _fixture_cleanup(root, budget=None):
    """Only ONE fixed extra fixture, never broaden legacy compressed-file cleanup."""
    if not root.exists() and not root.is_symlink():
        return
    info = root.lstat()
    S.require(stat.S_ISDIR(info.st_mode) and not root.is_symlink() and root.resolve() == root
              and info.st_uid == os.getuid() and stat.S_IMODE(info.st_mode) == 0o700, 'descriptor-fixture-root')
    owner = root/'owner.json'
    info = owner.lstat()
    S.require(stat.S_ISREG(info.st_mode) and info.st_uid == os.getuid() and info.st_nlink == 1
              and info.st_size <= 256, 'descriptor-fixture-marker')
    with owner.open('rb') as source:
        value = source.read(257)
        if budget is not None:
            budget.charge(len(value))
        S.require(value == marker(root), 'descriptor-fixture-marker')
    extra = root/'.descriptor-proof-extra'
    if not extra.exists() and not extra.is_symlink():
        return
    info = extra.lstat()
    S.require(info.st_uid == os.getuid(), 'descriptor-fixture-owner')
    if stat.S_ISLNK(info.st_mode):
        S.require(os.readlink(extra) == filename(PINS[0][0]), 'descriptor-fixture-link')
    else:
        first = (root/filename(PINS[0][0])).lstat()
        S.require(stat.S_ISREG(info.st_mode) and ((info.st_size == 0 and info.st_nlink == 1)
                  or (info.st_dev, info.st_ino, info.st_nlink, info.st_size)
                  == (first.st_dev, first.st_ino, 2, PINS[0][1])), 'descriptor-fixture-shape')
    extra.unlink()


def _descriptor_checks(root, budget):
    """Real owned guards, no syscall substitution; failures never grant replay."""
    baseline, groups = _fd_state(), []
    previous_mask = signal.pthread_sigmask(signal.SIG_BLOCK, set())
    sources = _descriptor_sources(budget)
    try:
        S.require(len(_fd_state()) == len(baseline)+6, 'descriptor-live-count')
        S.require(len(sources.seal()) == 3, 'descriptor-all-pins')
        groups.append('six-leases-and-all-pins')
        for fd, info in sources._sources:
            S.require(sources._api.flags(fd) == os.O_RDONLY and not os.get_inheritable(fd), 'descriptor-access')
            try:
                os.write(fd, b'X')
            except OSError as error:
                S.require(error.errno == errno.EBADF, 'descriptor-write-error')
            else:
                raise S.Refusal('descriptor-write-not-denied')
        groups.append('readonly-write-denial')
        child = "import errno,json,os,sys\nfor fd in json.loads(sys.argv[1]):\n try: os.fstat(fd)\n except OSError as e:\n  if e.errno!=errno.EBADF: sys.exit(2)\n else: sys.exit(3)\nprint('closed')"
        result = subprocess.run([sys.executable, '-I', '-S', '-B', '-c', child,
                                 json.dumps([item[1] for item in sources._owned if item[0] == 'fd'])],
                                stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, timeout=5,
                                close_fds=True, pass_fds=(), preexec_fn=A.decoder_limit, env=A.decoder_environment())
        S.require(result.returncode == 0 and result.stdout == b'closed\n', 'descriptor-child-inheritance')
        budget.charge(len(result.stdout))
        groups.append('trusted-child-no-inheritance')
        for unused in range(3):
            sources.check(True)
        groups.append('repeat-directory-rewind')
        cursor = sources.cursor(0)
    finally:
        sources.close()
    _must_refuse(cursor.tell, 'lease-retired')
    groups.append('retired-cursor')
    S.require(_fd_state() == baseline, 'descriptor-close-reconciliation')
    extra, first = root/'.descriptor-proof-extra', root/filename(PINS[0][0])
    try:
        with extra.open('xb'):
            pass
        _must_refuse(lambda: _descriptor_sources(budget), 'lease-membership-bound')
        groups.append('extra-membership')
    finally:
        _fixture_cleanup(root, budget)
    sources = _descriptor_sources(budget)
    try:
        os.link(first, extra)
        fd, original = sources._sources[0]
        _must_refuse(lambda: sources._bound(filename(PINS[0][0]), fd, original), 'lease-file-changed')
        _must_refuse(sources.check, 'lease-root-changed')
        groups.append('hardlink-fingerprint')
    finally:
        sources.close()
        _fixture_cleanup(root, budget)
    original_mode = stat.S_IMODE(first.lstat().st_mode)
    try:
        os.chmod(first, 0o622, follow_symlinks=False)
        _must_refuse(lambda: _descriptor_sources(budget), 'lease-file')
        groups.append('writable-mode-refusal')
    finally:
        os.chmod(first, original_mode, follow_symlinks=False)
    sources = _descriptor_sources(budget)
    try:
        os.symlink(filename(PINS[0][0]), extra)
        try:
            sources._open(extra.name, sources._flags, sources._root)
        except OSError as error:
            S.require(error.errno == errno.ELOOP, 'descriptor-nofollow-error')
        else:
            raise S.Refusal('descriptor-symlink-not-denied')
        groups.append('native-nofollow')
    finally:
        sources.close()
        _fixture_cleanup(root, budget)
    S.require(signal.pthread_sigmask(signal.SIG_BLOCK, set()) == previous_mask, 'descriptor-mask-reconciliation')
    S.require(_fd_state() == baseline, 'descriptor-guard-reconciliation')
    _children_empty()
    groups.append('mask-fd-and-child-reconciliation')
    budget.charge(0)
    return groups


def cleanup(root):
    if not root.exists() and not root.is_symlink():
        return 0
    info = root.lstat()
    S.require(stat.S_ISDIR(info.st_mode) and not root.is_symlink() and root.resolve() == root
              and info.st_uid == os.getuid() and stat.S_IMODE(info.st_mode) == 0o700, 'owned-root')
    allowed = {'owner.json'} | {filename(row[0]) for row in PINS}
    children = []
    with os.scandir(root) as entries:
        for entry in entries:
            S.require(len(children) < 4 and entry.name in allowed, 'owned-membership')
            path = root/entry.name
            item = path.lstat()
            S.require(stat.S_ISREG(item.st_mode) and item.st_uid == os.getuid() and item.st_nlink == 1, 'owned-file')
            children.append(path)
    owner = root/'owner.json'
    S.require(owner.stat().st_size <= 256 and owner.read_bytes() == marker(root), 'owned-marker')
    for path in children:
        if path != owner:
            path.unlink()
    owner.unlink()
    root.rmdir()
    S.require(not root.exists(), 'cleanup-remaining')
    return 0


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        raise S.Refusal('ppa-redirect-refused')


def download(root, pin, budget, opener):
    package, expected_size, expected_sha = pin
    path = root/filename(package)
    uri = DELIVERIES[package]
    size, digest = 0, hashlib.sha256()
    with opener.open(uri, timeout=10) as response, path.open('xb') as output:
        S.require(response.status == 200 and response.headers.get('Content-Length') == str(expected_size)
                  and response.headers.get('Content-Encoding') in (None, 'identity'), 'ppa-response')
        while True:
            chunk = response.read(65536)
            if not chunk:
                break
            size += len(chunk)
            budget.charge(len(chunk))
            S.require(size <= expected_size, 'ppa-size')
            digest.update(chunk)
            output.write(chunk)
    S.require(size == expected_size and digest.hexdigest() == expected_sha, 'ppa-identity')
    return {'package': package, 'bytes': size, 'sha256': digest.hexdigest()}


def acquire(root, budget, identities):
    # One default-verifying context for all fixed files; no per-file CA reloads.
    context = ssl.create_default_context()
    S.require(context.verify_mode == ssl.CERT_REQUIRED and context.check_hostname is True, 'tls-verification')
    opener = urllib.request.build_opener(urllib.request.ProxyHandler({}), NoRedirect(),
                                        urllib.request.HTTPSHandler(context=context))
    try:
        for pin in PINS:
            identities.append(download(root, pin, budget, opener))
    finally:
        opener.close()
    # Opener handlers retain their parent. Drop the entire owned transport cycle
    # before allocating selected ELF DATA; no heap/limit/acceptance relaxation.
    leases = weakref.ref(opener), weakref.ref(context)
    del opener, context
    gc.collect()
    S.require(all(lease() is None for lease in leases), 'transport-not-released')


def inspect(root, budget):
    results = []
    for package, size, sha in PINS:
        with (root/filename(package)).open('rb') as file:
            members = ar_members(file, budget)
            result = {'package': package, 'streams': []}
            for (name, start, length), category in zip(members, ('control', 'data')):
                source = Slice(file, start, length, budget)
                suffix = name[len(category+'.tar'):]
                decoder = Decoder(source, suffix, budget) if suffix else None
                try:
                    facts = tar_inventory(decoder or source, budget, package, category)
                finally:
                    if decoder:
                        decoder.close()
                S.require(source.remaining == 0, 'member-consumption')
                result['streams'].append({'member': name, 'compressed_bytes': length, 'decoder_reaped': decoder is not None, **facts})
            results.append(result)
    S.require(set(budget.roles) == {'cli', 'mysqlnd', 'mysqli'}, 'selected-roles-missing')
    return {'packages': results, 'roles': budget.roles, 'entries': budget.entries, 'work_bytes': budget.total,
            'types': budget.types, 'inventory_sha256': budget.inventory.hexdigest(), 'dependency_closure_verified': False}


def main():
    root = target(os.environ)
    def expired(signum, frame):
        raise S.Refusal('job-deadline')
    signal.signal(signal.SIGALRM, expired)
    if sys.argv[1:] == ['--cleanup']:
        signal.alarm(30)
        cleanup(root)
        signal.alarm(0)
        print('{"owned_deb_remaining":0}')
        return 0
    S.require(sys.argv[1:] == ['--approved-static-proof'], 'entry-mode')
    receipt = {'format': 'ADBPPADATA1', 'extracted': False, 'executed': False, 'qualified': False, 'static_inventory_complete': False}
    created = False
    signal.alarm(540)
    try:
        S.require(not root.exists() and not root.is_symlink(), 'preexisting-owned-root')
        budget = Budget()
        receipt['preflight'] = preflight()
        root.mkdir(mode=0o700)
        created = True
        with (root/'owner.json').open('xb') as owner:
            data = marker(root)
            budget.charge(len(data))
            owner.write(data)
        receipt['identities'] = []
        acquire(root, budget, receipt['identities'])
        receipt['transport_released'] = True
        receipt['inventory'] = inspect(root, budget)
        receipt['static_inventory_complete'] = True
        receipt['reason'] = 'data-inventory-only-no-runtime-admission'
    except S.Refusal as error:
        receipt['reason'] = str(error)
    except urllib.error.HTTPError as error:
        try:
            receipt.update(http_refusal(error))
        finally:
            error.close()
    except MemoryError as error:
        receipt['reason'], receipt['error_class'] = 'parent-memory-refusal', 'MemoryError'
        receipt['memory_site'] = memory_site(error)
    except Exception as error:
        receipt['reason'], receipt['error_class'] = 'bounded-proof-error', type(error).__name__
    finally:
        signal.alarm(30)
        if created:
            cleanup(root)
        receipt['owned_deb_remaining'] = 'Unknown' if root.exists() or root.is_symlink() else 0
        signal.alarm(0)
    output = json.dumps(receipt, sort_keys=True)
    S.require(len(output.encode('ascii')) <= 65536, 'diagnostic-bound')
    print(output)
    return 0 if receipt['static_inventory_complete'] else 1


def descriptor_main():
    """ONE exact-job owned DATA-only qualification; legacy main stays separate."""
    root = target(os.environ)
    def expired(signum, frame):
        raise S.Refusal('job-deadline')
    signal.signal(signal.SIGALRM, expired)
    cleanup_only = sys.argv[1:] == ['--cleanup-descriptor-proof']
    S.require(cleanup_only or sys.argv[1:] == ['--approved-descriptor-proof'], 'descriptor-entry-mode')
    signal.alarm(30 if cleanup_only else 540)
    if cleanup_only:
        _fixture_cleanup(root)
        cleanup(root)
        signal.alarm(0)
        print('{"owned_deb_remaining":0,"owned_extra_remaining":0}')
        return 0
    receipt = {'format': 'ADBPPADESCRIPTOR1', 'descriptor_proof_complete': False,
               'static_inventory_complete': False, 'qualified': False, 'executed': False, 'extracted': False}
    created, complete, baseline, inventory = False, False, None, None
    try:
        budget = Budget()
        receipt['stage'] = 'preflight'
        baseline = _fd_state()
        _children_empty()
        receipt['preflight'] = preflight()  # Original 40/24MiB caps and fixed codecs.
        S.require(_fd_state() == baseline, 'descriptor-preflight-reconciliation')
        S.require(not root.exists() and not root.is_symlink(), 'preexisting-owned-root')
        root.mkdir(mode=0o700)
        created = True
        with (root/'owner.json').open('xb') as owner:
            data = marker(root)
            budget.charge(len(data))
            owner.write(data)
        # Parent/type/access/capability admission before any PPA acquisition.
        receipt['stage'] = 'parent-admission'
        _must_refuse(lambda: _descriptor_sources(budget), 'lease-membership')
        S.require(_fd_state() == baseline, 'descriptor-parent-reconciliation')
        receipt['stage'] = 'acquisition'
        identities = []
        acquire(root, budget, identities)  # Fixed pins,TLS,no redirect,all writers/transport released.
        receipt['transport_released'] = True
        receipt['stage'] = 'owned-descriptor-guards'
        groups = ['parent-admission-and-missing-membership', *_descriptor_checks(root, budget)]
        receipt['stage'] = 'fixed-replay'
        inventory = _descriptor_preparation(budget)
        S.require(inventory['prepared_only'] is True and inventory['qualified'] is False
                  and inventory['identities'] == identities, 'descriptor-replay-identity')
        _children_empty()
        S.require(_fd_state() == baseline, 'descriptor-replay-reconciliation')
        groups.append('fixed-artifact-replay-and-reconciliation')
        receipt['groups'] = groups
        receipt['work_bytes'] = budget.total
        receipt['reason'] = 'owned-source-replay-only-no-runtime-admission'
        complete = True
    except S.Refusal as error:
        receipt['reason'] = str(error)
    except urllib.error.HTTPError as error:
        try:
            receipt.update(http_refusal(error))
        finally:
            error.close()
    except Exception as error:
        receipt['reason'], receipt['error_class'] = 'descriptor-proof-error', type(error).__name__
    finally:
        signal.alarm(30)
        try:
            if created:
                _fixture_cleanup(root)
                cleanup(root)
            _children_empty()
            S.require(baseline is not None and _fd_state() == baseline, 'descriptor-final-reconciliation')
            receipt['owned_deb_remaining'] = 0 if not root.exists() and not root.is_symlink() else 'Unknown'
            S.require(receipt['owned_deb_remaining'] == 0, 'descriptor-files-remaining')
            receipt['owned_extra_remaining'] = receipt['owned_descriptor_delta'] = receipt['owned_children_remaining'] = 0
        except Exception as error:
            complete = False
            receipt['reason'], receipt['cleanup_error_class'] = 'descriptor-cleanup-unknown', type(error).__name__
            receipt['owned_deb_remaining'] = receipt['owned_extra_remaining'] = receipt['owned_descriptor_delta'] = receipt['owned_children_remaining'] = 'Unknown'
        finally:
            signal.alarm(0)
    if complete:
        try:
            budget.charge(0)  # Cleanup cannot extend the original work deadline.
            receipt['descriptor_proof_complete'] = True
            receipt['inventory'] = inventory  # AFTER owned file/process/FD cleanup.
            receipt['stage'] = 'complete'
        except S.Refusal as error:
            complete = False
            receipt['reason'] = str(error)
    output = json.dumps(receipt, sort_keys=True)
    S.require(len(output.encode('ascii')) <= 65536, 'diagnostic-bound')
    print(output)
    return 0 if complete else 1


if __name__ == '__main__':
    sys.exit(descriptor_main() if sys.argv[1:] in (['--approved-descriptor-proof'], ['--cleanup-descriptor-proof']) else main())
