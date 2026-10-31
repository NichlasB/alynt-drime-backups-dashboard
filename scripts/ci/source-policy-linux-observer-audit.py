"""One owned existing-authority audit. No candidate, network, database or elevation."""
import errno
import hashlib
import json
import os
from pathlib import Path
import re
import select
import signal
import stat
import subprocess
import sys
import time

CHILD = """import mmap, os, select, sys
mapping = None
fd = None
try:
    fd = os.open(sys.argv[1], os.O_RDONLY | os.O_NOFOLLOW)
    mapping = mmap.mmap(fd, 4096, access=mmap.ACCESS_READ)
    if mapping[0] != 65: raise RuntimeError()
    os.write(1, b'READY\\n')
    ready, _, _ = select.select([0], [], [], 20)
    if not ready or os.read(0, 2) != b'q': raise RuntimeError()
    mapping.close(); mapping = None
    os.close(fd); fd = None
    os.write(1, b'CLOSED\\n')
except BaseException:
    sys.exit(1)
finally:
    if mapping is not None: mapping.close()
    if fd is not None: os.close(fd)
"""


class Refusal(Exception):
    pass


class ProcReadIncomplete(Refusal):
    def __init__(self, role, payload_bytes, limit, probe_bytes):
        super().__init__('proc-read-incomplete')
        self.facts = {'role': role, 'payload_bytes': payload_bytes,
                      'payload_limit': limit, 'eof_probe_bytes': probe_bytes}


def proc_read_details(error):
    if isinstance(error, ProcReadIncomplete):
        return {'proc_read': error.facts}
    return {}


class AccessDenied(PermissionError):
    def __init__(self, operation, number, facts):
        super().__init__(number, 'owned observer access denied')
        self.operation, self.facts = operation, facts


def need(ok, code):
    if not ok:
        raise Refusal(code)


def bounded_read(fd, limit):
    data = os.read(fd, limit + 1)
    need(len(data) <= limit, 'read-bound')
    return data


def parse_stat(data, pid):
    need(len(data) <= 4096 and b' (' in data and b') ' in data, 'stat-shape')
    found_pid = int(data.split(b' ', 1)[0])
    parts = data[data.rindex(b') ') + 2:].split()
    need(found_pid == pid and 20 <= len(parts) <= 64, 'stat-identity')
    need(parts[1].isdigit() and parts[19].isdigit(), 'stat-number')
    parent, start = int(parts[1]), int(parts[19])
    need(0 < parent <= 2147483647 and 0 < start < 2**63, 'stat-bound')
    return parent, start


def parse_maps(data):
    need(len(data) <= 32768 and data.endswith(b'\n'), 'maps-byte-bound')
    lines = data.decode('ascii').splitlines()
    need(len(lines) <= 256, 'maps-count-bound')
    result, start_previous, end_previous = [], -1, 0
    for line in lines:
        parts = line.split(None, 5)
        need(len(parts) >= 5 and re.fullmatch(r'[r-][w-][x-][ps]', parts[1]), 'maps-fields')
        match = re.fullmatch(r'([a-f0-9]{1,16})-([a-f0-9]{1,16})', parts[0])
        device = re.fullmatch(r'([a-f0-9]{1,8}):([a-f0-9]{1,8})', parts[3])
        need(match is not None and device is not None and re.fullmatch(r'[a-f0-9]{1,16}', parts[2])
             and re.fullmatch(r'[0-9]{1,20}', parts[4]), 'maps-shape')
        start, end = (int(value, 16) for value in match.groups())
        need(start >= end_previous and start > start_previous and end > start, 'maps-order')
        start_previous, end_previous = start, end
        inode = int(parts[4]); path = parts[5] if len(parts) == 6 else ''
        need(inode < 2**64 and not path.endswith(' (deleted)'), 'maps-deleted')
        if inode == 0:
            need('x' not in parts[1] or path in ('[vdso]', '[vsyscall]'), 'maps-anonymous-executable')
            continue
        result.append((parts[0], (int(device[1], 16), int(device[2], 16), inode), 'x' in parts[1]))
    need(0 < len(result) <= 256 or not result, 'maps-records')
    need(len({entry[1] for entry in result}) <= 64, 'maps-files-bound')
    return result


def parse_status(data):
    need(len(data) <= 16384, 'status-bound')
    found = {}
    for line in data.decode('ascii').splitlines():
        key, _, value = line.partition(':')
        if key in ('Uid', 'Gid', 'CapEff', 'NoNewPrivs'):
            need(key not in found, 'status-duplicate')
            found[key] = value.split()
    need(set(found) == {'Uid', 'Gid', 'CapEff', 'NoNewPrivs'}, 'status-fields')
    for key in ('Uid', 'Gid'):
        need(len(found[key]) == 4 and len(set(found[key])) == 1
             and re.fullmatch(r'[0-9]{1,10}', found[key][0]), 'status-owner')
    need(len(found['CapEff']) == 1 and re.fullmatch('[a-f0-9]{16}', found['CapEff'][0])
         and found['NoNewPrivs'] in (['0'], ['1']), 'status-authority')
    return {'uid': int(found['Uid'][0]), 'gid': int(found['Gid'][0]),
            'cap_eff': found['CapEff'][0], 'no_new_privs': int(found['NoNewPrivs'][0])}


def valid_owner(row, expected):
    return (isinstance(row, dict) and set(row) == set(expected)
            and type(row.get('launching')) is bool and type(row.get('uid')) is int
            and all(row[key] == value for key, value in expected.items() if key != 'launching'))


def cleanup_names(entries, uid):
    need(len(entries) <= 4 and len({name for name, _ in entries}) == len(entries), 'cleanup-count')
    for name, info in entries:
        need(name in ('owner.json', 'owner.next.json', 'child.json', 'mapped.bin')
             and stat.S_ISREG(info.st_mode) and info.st_uid == uid and info.st_nlink == 1
             and info.st_size <= 4096, 'cleanup-membership')
    return sorted(name for name, _ in entries)


def exit_code(status, clean, unchanged):
    return 0 if status in ('available', 'unavailable') and clean is True and unchanged is True else 1


def admit(env):
    need(env.get('GITHUB_ACTIONS') == 'true' and env.get('GITHUB_EVENT_NAME') == 'workflow_dispatch'
         and env.get('GITHUB_REPOSITORY') == 'NichlasB/alynt-drime-backups-dashboard'
         and env.get('GITHUB_REF') == 'refs/heads/master' and sys.platform == 'linux'
         and sys.flags.isolated and sys.flags.no_site and sys.dont_write_bytecode, 'context')
    head = env.get('GITHUB_SHA', '')
    need(re.fullmatch('[a-f0-9]{40}', head) and env.get('EXPECTED_SHA') == head
         and re.fullmatch('[0-9]{1,20}', env.get('GITHUB_RUN_ID', ''))
         and re.fullmatch('[0-9]{1,10}', env.get('GITHUB_RUN_ATTEMPT', ''))
         and env.get('RUNNER_OS') == 'Linux' and os.uname().machine == 'x86_64', 'exact-head')
    return head


def read_at(directory, name, limit):
    need(name in ('stat', 'status', 'maps'), 'proc-read-role')
    fd = os.open(name, os.O_RDONLY | os.O_CLOEXEC, dir_fd=directory)
    try:
        data = bounded_read(fd, limit)
        # Never stitch a changing proc snapshot from multiple payload reads.
        probe = os.read(fd, 1)
        if probe:
            raise ProcReadIncomplete(name, len(data), limit, len(probe))
        return data
    finally:
        os.close(fd)


def own_status():
    proc = os.open('/proc/self', os.O_RDONLY | os.O_DIRECTORY | os.O_CLOEXEC)
    try: return parse_status(read_at(proc, 'status', 16384))
    finally: os.close(proc)


def digest_fd(fd, maximum, deadline):
    meta = os.fstat(fd)
    need(stat.S_ISREG(meta.st_mode) and meta.st_nlink > 0 and 0 < meta.st_size <= maximum, 'hash-file-bound')
    digest, total = hashlib.sha256(), 0
    while True:
        need(time.monotonic() < deadline, 'hash-deadline')
        data = os.read(fd, 1024 * 1024)
        if not data: break
        total += len(data); need(total <= maximum, 'hash-byte-bound'); digest.update(data)
    need(total == meta.st_size, 'hash-size')
    return digest.hexdigest(), total


def file_digest(path, maximum=64*1024*1024):
    fd = os.open(path, os.O_RDONLY | os.O_CLOEXEC | os.O_NOFOLLOW)
    try: return digest_fd(fd, maximum, time.monotonic()+10)[0]
    finally: os.close(fd)


def source_identity():
    script = Path(__file__).resolve()
    workflow = script.parents[2] / '.github/workflows/source-policy-linux-observer-audit.yml'
    python = Path(sys.executable).resolve(strict=True)
    need(str(python).startswith('/usr/bin/python3') and not python.stat().st_mode & 0o6000, 'existing-python')
    try:
        capability = os.getxattr(python, 'security.capability')
    except OSError as error:
        need(error.errno == errno.ENODATA, 'python-capability-metadata')
        capability = b''
    need(not capability, 'python-file-capability')
    return hashlib.sha256(json.dumps({'script':file_digest(script), 'workflow':file_digest(workflow),
        'python':file_digest(python)}, sort_keys=True).encode()).hexdigest()


def write_record(root, name, row):
    data = json.dumps(row, sort_keys=True).encode()
    need(len(data) <= 4096, 'record-bound')
    fd = os.open(root / name, os.O_WRONLY | os.O_CREAT | os.O_EXCL | os.O_CLOEXEC | os.O_NOFOLLOW, 0o600)
    with os.fdopen(fd, 'wb') as stream: stream.write(data)


def decode_record(path):
    def pairs(items):
        result = {}
        for key, value in items:
            need(key not in result, 'record-duplicate'); result[key] = value
        return result
    with path.open('rb') as stream: data = stream.read(4097)
    need(len(data) <= 4096, 'record-byte-bound')
    return json.loads(data, object_pairs_hook=pairs)


def replace_owner(root, owner):
    write_record(root, 'owner.next.json', owner)
    os.replace(root / 'owner.next.json', root / 'owner.json')


def stage_context(head, image):
    parent = Path(os.environ['RUNNER_TEMP'])
    need(parent.is_absolute() and parent.resolve() == parent and parent.is_dir(), 'stage-parent')
    root = parent / ('adb-linux-observer-' + os.environ['GITHUB_RUN_ID'] + '-' + os.environ['GITHUB_RUN_ATTEMPT'])
    need(len(str(root)) <= 220, 'stage-path-bound')
    return root, {'head':head,'source_image':image,'uid':os.geteuid(),
        'run_id':os.environ['GITHUB_RUN_ID'],'attempt':os.environ['GITHUB_RUN_ATTEMPT'],'launching':False}


def remove_stage(root, expected):
    if not root.exists() and not root.is_symlink(): return
    info = root.lstat()
    need(stat.S_ISDIR(info.st_mode) and not root.is_symlink() and info.st_uid == expected['uid']
         and stat.S_IMODE(info.st_mode) == 0o700, 'cleanup-root')
    entries = [(p.name, p.lstat()) for p in root.iterdir()]
    names = cleanup_names(entries, expected['uid'])
    need('owner.json' in names, 'cleanup-owner-missing')
    owner = decode_record(root / 'owner.json')
    need(valid_owner(owner, expected), 'cleanup-owner')
    if owner['launching']:
        need('child.json' in names, 'cleanup-launch-unconfirmed')
        child = decode_record(root / 'child.json')
        need(set(child) == {'pid','parent','start'} and all(type(value) is int and value > 0 for value in child.values())
             and child['pid'] <= 2147483647, 'cleanup-child')
        deadline = time.monotonic() + 22
        while time.monotonic() < deadline:
            try:
                proc = os.open('/proc/' + str(child['pid']), os.O_RDONLY | os.O_DIRECTORY | os.O_CLOEXEC)
                try: identity = parse_stat(read_at(proc, 'stat', 4096), child['pid'])
                finally: os.close(proc)
                if identity != (child['parent'], child['start']): break
            except (FileNotFoundError, ProcessLookupError): break
            time.sleep(0.1)  # Wait only; never signal a PID obtained from a file.
        else: raise Refusal('cleanup-child-still-live')
    for name in names: (root / name).unlink()
    root.rmdir(); need(not root.exists(), 'cleanup-absent')


def snapshot(proc, pid, identity, fixture, deadline):
    need(parse_stat(read_at(proc, 'stat', 4096), pid) == identity, 'process-start-changed')
    try: rows = parse_maps(read_at(proc, 'maps', 32768))
    except PermissionError as error: raise AccessDenied('maps-read', error.errno, {}) from None
    unique = {}
    for address, key, executable in rows: unique.setdefault(key, address)
    need(fixture in unique and len(unique) <= 64, 'fixture-map-missing')
    records, total = [], 0
    # Known owned fixture first: ordinary file readability is not the access under audit.
    for key in [fixture] + sorted(key for key in unique if key != fixture):
        try: fd = os.open('map_files/' + unique[key], os.O_RDONLY | os.O_CLOEXEC, dir_fd=proc)
        except PermissionError as error:
            raise AccessDenied('fixture-mapfile-open' if key == fixture else 'library-mapfile-open', error.errno,
                {'maps_complete':True,'mapping_rows':len(rows),'unique_files':len(unique),
                 'files_hashed':len(records),'fixture_selected':key == fixture}) from None
        try:
            info = os.fstat(fd)
            need((os.major(info.st_dev), os.minor(info.st_dev), info.st_ino) == key, 'mapped-object-mismatch')
            sha, size = digest_fd(fd, 128*1024*1024, deadline)
            if key == fixture: need(size == 4096 and sha == hashlib.sha256(b'A'*4096).hexdigest(), 'fixture-object-mismatch')
            total += size; need(total <= 256*1024*1024, 'mapped-total-bound')
            records.append((key, size, sha))
        finally: os.close(fd)
    need(parse_stat(read_at(proc, 'stat', 4096), pid) == identity, 'process-start-changed')
    return {'mapping_rows':len(rows),'regular_files':len(records),'file_bytes':total,
        'cohort_sha256':hashlib.sha256(json.dumps(sorted(records)).encode()).hexdigest(),'fixture_backing_read':True}


def audit(head, image, root, owner):
    report = {'format':'ADBLHO1','head':head,'source_image':image,'status':'refused',
        'candidate_images':0,'candidate_execution':False,'database_operations':False,
        'privilege_changes':False,'candidate_qualification':'not-established','owned_stage':root.name}
    process = None; proc = None; created = False; ready = False; reaped = False
    deadline = time.monotonic() + 25; phase = 'stage-create'
    try:
        root.mkdir(mode=0o700); created = True
        write_record(root, 'owner.json', owner)
        fd = os.open(root / 'mapped.bin', os.O_WRONLY | os.O_CREAT | os.O_EXCL | os.O_CLOEXEC | os.O_NOFOLLOW, 0o600)
        with os.fdopen(fd, 'wb') as stream: stream.write(b'A'*4096)
        meta = (root / 'mapped.bin').stat(); fixture = (os.major(meta.st_dev),os.minor(meta.st_dev),meta.st_ino)
        phase = 'authority-before'; before = own_status()
        need(before['uid'] == os.geteuid() and before['gid'] == os.getegid(), 'current-identity')
        report['observer_authority'] = before; report['python'] = sys.version.split()[0]
        owner['launching'] = True; replace_owner(root, owner)
        phase = 'owned-child-start'
        process = subprocess.Popen([sys.executable,'-I','-S','-B','-c',CHILD,str(root/'mapped.bin')],
            stdin=subprocess.PIPE,stdout=subprocess.PIPE,stderr=subprocess.PIPE,bufsize=0,
            env={'LANG':'C.UTF-8'},start_new_session=True)
        proc = os.open('/proc/'+str(process.pid),os.O_RDONLY|os.O_DIRECTORY|os.O_CLOEXEC)
        identity = parse_stat(read_at(proc,'stat',4096),process.pid)
        need(identity[0] == os.getpid(), 'owned-child-parent')
        write_record(root,'child.json',{'pid':process.pid,'parent':identity[0],'start':identity[1]})
        report['child_pid'] = process.pid; report['child_start'] = identity[1]
        readable,_,_ = select.select([process.stdout],[],[],5)
        need(readable and os.read(process.stdout.fileno(),16) == b'READY\n','child-ready'); ready = True
        report['child_authority'] = parse_status(read_at(proc,'status',16384))
        need(report['child_authority'] == before, 'child-authority-changed')
        phase = 'mapped-file-observation'
        first = snapshot(proc,process.pid,identity,fixture,deadline); report['startup'] = first
        second = snapshot(proc,process.pid,identity,fixture,deadline); report['final'] = second
        need(first['cohort_sha256'] == second['cohort_sha256'], 'mapped-cohort-changed')
        report['status'] = 'available'; report['reason'] = 'bounded-kernel-file-reads-complete'
    except PermissionError as error:
        report.update(status='unavailable',reason='existing-authority-denied',phase=phase,errno=error.errno)
        report['operation'] = getattr(error,'operation','other-owned-operation')
        report['denial_facts'] = getattr(error,'facts',{})
    except Exception as error:
        report.update(status='refused',reason=str(error) if isinstance(error,Refusal) else 'unexpected-audit-failure',phase=phase)
        report.update(proc_read_details(error))
    finally:
        try:
            if process is not None:
                try:
                    out, err = process.communicate(input=b'q', timeout=2)
                except subprocess.TimeoutExpired:
                    process.terminate()
                    try: process.communicate(timeout=2)
                    except subprocess.TimeoutExpired: process.kill(); process.communicate(timeout=2)
                    raise Refusal('child-forced-termination')
                finally: reaped = process.poll() is not None
                need(process.returncode == 0 and err == b'' and out == (b'CLOSED\n' if ready else b'READY\nCLOSED\n'), 'child-closure')
                try:
                    parse_stat(read_at(proc,'stat',4096),process.pid)
                except (FileNotFoundError,ProcessLookupError): pass
                else: raise Refusal('owned-proc-identity-retained')
            report['child_reaped'] = reaped
            if created and (process is None or reaped):
                owner['launching'] = False; replace_owner(root, owner)
                remove_stage(root, owner)
            report['cleanup'] = 'confirmed' if created and not root.exists() else 'unconfirmed'
            report['observer_authority_after'] = own_status()
            report['authority_unchanged'] = report['observer_authority_after'] == report.get('observer_authority')
            need(report['authority_unchanged'], 'authority-after')
        except Exception as error:
            report['cleanup'] = 'unconfirmed'; report['status'] = 'refused'
            report['cleanup_reason'] = str(error) if isinstance(error,Refusal) else 'unexpected-cleanup-failure'
            report.update(proc_read_details(error))
        finally:
            if proc is not None: os.close(proc)
        report['source_unchanged'] = source_identity() == image
    return report


def main():
    try:
        need(sys.argv[1:] in (['--audit'],['--cleanup']), 'arguments')
        head = admit(os.environ)
    except Refusal:
        sys.stderr.buffer.write(b'Requires exact approved manual Linux host audit\n'); return 2
    try:
        workspace = Path(os.environ['GITHUB_WORKSPACE'])
        need(workspace.resolve() == workspace and Path.cwd() == workspace and workspace == Path(__file__).resolve().parents[2], 'workspace')
        git = subprocess.run(['/usr/bin/git','rev-parse','HEAD'],capture_output=True,timeout=5,env={'PATH':'/usr/bin:/bin'})
        need(git.returncode == 0 and git.stderr == b'' and git.stdout == (head+'\n').encode(),'checkout-head')
        image = source_identity(); root, owner = stage_context(head,image)
        if sys.argv[1] == '--cleanup':
            remove_stage(root,owner)
            report = {'format':'ADBLHO1-cleanup','head':head,'source_image':image,'owned_stage_absent':not root.exists()}
            print(json.dumps(report,sort_keys=True)); return 0
        def interrupted(signum, frame): raise Refusal('audit-interrupted')
        signal.signal(signal.SIGTERM,interrupted); signal.signal(signal.SIGINT,interrupted)
        report = audit(head,image,root,owner)
        print(json.dumps(report,sort_keys=True))
        return exit_code(report['status'],report['cleanup']=='confirmed',report['source_unchanged'])
    except Exception as error:
        report = {'format':'ADBLHO1','head':head,'status':'refused',
            'reason':str(error) if isinstance(error,Refusal) else 'audit-supervisor-preflight-or-cleanup-failed',
            'candidate_qualification':'not-established'}
        report.update(proc_read_details(error))
        print(json.dumps(report))
        return 1


if __name__ == '__main__':
    sys.exit(main())
