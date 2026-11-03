"""Owned-canary DATA only. Separate from candidate contexts and SQL lab guards."""
import hashlib
import io
import json
import re
import struct
import tarfile

MODEL = 'owned-abi-canary-moby-28.0.4-v1'
LABEL = 'com.alynt.owned-abi'
SOURCES = (
    '.github/workflows/source-policy-owned-abi-canary.yml',
    'scripts/ci/source-policy-owned-abi-canary.py',
    'scripts/ci/source-policy-abi-core.h', 'scripts/ci/source-policy-abi-host.c',
    'scripts/ci/source-policy-abi-canary.c',
    'scripts/manual/source_policy_abi_canary_contract.py',
    'scripts/manual/source_policy_moby_policy.py',
    'scripts/manual/source_policy_ubi_import_contract.py',
    'tests/fixtures/moby-28.0.4/default.json.b64',
)
ABIS = ('amd64', 'i386', 'x32')
# Direct table order matches reviewed C routes. PNR aliases only use socketcall.
SUBS = ((1,8,3,2,4,5,18,13,6,7,14,15,11,16,20,12,17,19),
        (1,8,3,2,4,18,13,6,7,14,15,11,16,20,12,17,19,-1),
        (1,8,3,2,4,5,18,13,6,7,14,15,11,16,20,12,17,19))


class Refusal(ValueError):
    pass


def need(ok, reason):
    if not ok:
        raise Refusal(reason)


def encode(value):
    return json.dumps(value, sort_keys=True, separators=(',', ':')).encode('ascii')


def sha(data):
    return hashlib.sha256(data).hexdigest()


def context(head, run, attempt, source):
    need(type(head) is str and re.fullmatch('[a-f0-9]{40}', head)
         and type(run) is str and re.fullmatch('[1-9][0-9]{0,19}', run)
         and type(attempt) is str and re.fullmatch('[1-9][0-9]{0,4}', attempt)
         and type(source) is str and re.fullmatch('[a-f0-9]{64}', source), 'canary-context')
    return dict(format='ADBOWNEDABICONTEXT1', purpose=MODEL, head=head, run=run,
                attempt=attempt, source=source)


def policy(raw, frame, moby, meter):
    need(type(frame) is dict and frame == context(frame.get('head'), frame.get('run'),
         frame.get('attempt'), frame.get('source')), 'canary-only-context')
    need(type(raw) is bytes and len(raw) == 12828 and sha(raw) == moby.BASELINE_SHA, 'raw-pin')
    original = moby._parse(raw, 16384, meter)
    changed = moby._parse(raw, 16384, meter)
    changed['syscalls'] = [dict(row, names=[n for n in row['names'] if
        not (row['action'] == 'SCMP_ACT_ALLOW' and n in moby.DENIED)]) for row in changed['syscalls']]
    changed['syscalls'] = [row for row in changed['syscalls'] if row['names']]
    wire = moby.encode(changed)
    verify_policy(original, wire, moby, meter)
    return wire


def verify_policy(original, wire, moby, meter):
    # Independently walk the original, not the transformer's surviving tree.
    out = moby._parse(wire, 65536, meter)
    need(type(out) is dict and wire == moby.encode(out), 'canonical-policy')
    need(moby._same({k:v for k,v in original.items() if k != 'syscalls'},
                    {k:v for k,v in out.items() if k != 'syscalls'}, meter), 'protected-policy')
    cursor = removed = 0
    for row in original['syscalls']:
        names = [n for n in row['names'] if not (row['action'] == 'SCMP_ACT_ALLOW' and n in moby.DENIED)]
        removed += len(row['names'])-len(names)
        if not names:
            continue
        need(cursor < len(out['syscalls']), 'missing-rule')
        actual = out['syscalls'][cursor]
        need(moby._same({k:v for k,v in row.items() if k != 'names'},
                       {k:v for k,v in actual.items() if k != 'names'}, meter)
             and moby._same(actual.get('names'), names, meter), 'protected-rule')
        cursor += 1
    need(cursor == len(out['syscalls']) == 30 and removed == 22
         and sum(len(r['names']) for r in out['syscalls']) == 406, 'exact-delta')


def elf(data):
    need(type(data) is bytes and 64 <= len(data) <= 131072 and data[:7] == b'\x7fELF\x02\x01\x01', 'owned-elf')
    need(struct.unpack_from('<HH', data, 16) == (2,62), 'static-amd64')
    offset = struct.unpack_from('<Q', data, 32)[0]
    size,count = struct.unpack_from('<HH', data, 54)
    need(size == 56 and 1 <= count <= 16 and offset+size*count <= len(data), 'program-headers')
    executable = False
    for i in range(count):
        kind,flags,start,address,_,length,memory,_ = struct.unpack_from('<IIQQQQQQ', data, offset+i*size)
        need(kind not in (2,3) and start+length <= len(data), 'no-dynamic-loader')
        if kind == 1:
            need(address+memory < 0x100000000 and not (flags & 1 and flags & 2), 'low-address-wx')
            executable = executable or bool(flags & 1)
        if kind == 0x6474e551:
            need(not flags & 1, 'no-executable-stack')
    need(executable, 'executable-segment')
    return sha(data)


def labels(frame):
    return {LABEL:sha(encode(frame)), LABEL+'.profile':MODEL, LABEL+'.source':frame['source']}


def image(data, frame):
    elf(data)
    layer = io.BytesIO()
    with tarfile.open(fileobj=layer, mode='w', format=tarfile.USTAR_FORMAT) as archive:
        entry = tarfile.TarInfo('canary'); entry.size=len(data); entry.mode=0o555
        entry.uid=entry.gid=65532
        archive.addfile(entry, io.BytesIO(data))
    layer = layer.getvalue()
    config = encode(dict(architecture='amd64', os='linux',
        config=dict(User='65532:65532', WorkingDir='/', Entrypoint=['/canary'], Cmd=[], Env=[], Labels=labels(frame)),
        rootfs=dict(type='layers', diff_ids=['sha256:'+sha(layer)]), history=[dict(created_by=MODEL)]))
    name = sha(config)+'.json'
    payload = io.BytesIO()
    with tarfile.open(fileobj=payload, mode='w', format=tarfile.USTAR_FORMAT) as archive:
        for path,content in ((name,config),('layer.tar',layer),('manifest.json',
                encode([dict(Config=name, RepoTags=[], Layers=['layer.tar'])]))):
            entry=tarfile.TarInfo(path); entry.size=len(content); entry.mode=0o444
            archive.addfile(entry,io.BytesIO(content))
    wire=payload.getvalue(); need(len(wire) <= 1048576, 'image-budget')
    return 'sha256:'+sha(config), wire, sha(layer)


def image_readback(row, identity, frame):
    c=row.get('Config', {})
    need(row.get('Id') == identity and row.get('Os') == 'linux' and row.get('Architecture') == 'amd64'
         and c.get('User') == '65532:65532' and c.get('WorkingDir') == '/'
         and c.get('Entrypoint') == ['/canary'] and c.get('Cmd') in (None, [])
         and c.get('Env') in (None, []) and c.get('Labels') == labels(frame)
         and not any(c.get(k) for k in ('Volumes','ExposedPorts','Healthcheck','OnBuild','Shell'))
         and not row.get('RepoTags'), 'image-readback')


def container_readback(row, identity, frame, abi, security):
    c=row.get('Config', {}); h=row.get('HostConfig', {})
    need(abi in ABIS and row.get('Image') == identity and c.get('Labels') == labels(frame)
         and row.get('Path') == '/canary' and row.get('Args') == [abi]
         and row.get('AppArmorProfile') == 'docker-default'
         and c.get('Entrypoint') == ['/canary'] and c.get('Cmd') == [abi]
         and c.get('User') == '65532:65532' and c.get('WorkingDir') == '/'
         and c.get('Env') in (None, []) and c.get('Hostname') == 'abi-canary'
         and not any(c.get(k) for k in ('OpenStdin','StdinOnce','Tty','Volumes','ExposedPorts','Healthcheck')),
         'container-command')
    need(h.get('ReadonlyRootfs') is True and h.get('NetworkMode') == 'none'
         and h.get('Privileged') is False and h.get('CapDrop') == ['ALL'] and not h.get('CapAdd')
         and sorted(h.get('SecurityOpt', [])) == sorted(['no-new-privileges', security])
         and h.get('Memory') == h.get('MemorySwap') == 536870912 and h.get('NanoCpus') == 1000000000
         and h.get('PidsLimit') == 64 and h.get('PidMode') == '' and h.get('IpcMode') == 'private'
         and h.get('CgroupnsMode') == 'private'
         and h.get('Runtime') == 'runc' and h.get('UtsMode') == '' and h.get('UsernsMode') == ''
         and h.get('AutoRemove') is False and not h.get('Init')
         and h.get('RestartPolicy') == dict(Name='no', MaximumRetryCount=0)
         and not any(h.get(k) for k in ('Binds','Tmpfs','Devices','DeviceRequests','PortBindings',
             'ExtraHosts','VolumesFrom','Links','Dns','GroupAdd','Sysctls','CgroupParent'))
         and not row.get('Mounts') and h.get('PublishAllPorts') is False,
         'container-containment')
    need(h.get('LogConfig') == dict(Type='local', Config={'max-size':'16k','max-file':'1'})
         and sorted(h.get('Ulimits', []),key=lambda r:r['Name']) == [dict(Name='core',Hard=0,Soft=0),
             dict(Name='nofile',Hard=64,Soft=64)], 'container-limits')


def numbers(wire, count):
    need(type(wire) is bytes and len(wire) <= 4096 and re.fullmatch(rb'(?:-?[0-9]{1,12}\n)+', wire), 'probe-wire')
    rows=[int(n) for n in wire.splitlines()]
    need(len(rows) == count, 'probe-count')
    return rows


def observation(wire, abi, negative):
    need(abi in ABIS and type(negative) is bool, 'probe-selector')
    subs=SUBS[ABIS.index(abi)]+(tuple(range(1,21)) if abi == 'i386' else ())
    rows=numbers(wire,len(subs)+5)
    need(rows[:3] == [65532,1,2], 'native-state')
    need(rows[3] > 0 and rows[-1] == rows[3], 'abi-health-continuation')
    for sub,out in zip(subs,rows[4:-1]):
        expected = -1 if negative else 0 if sub == 8 else -9
        need((out >= 0 if sub == 1 and not negative else out == expected), 'native-syscall-result')
    return dict(abi=abi, routes=len(subs), positive=not negative, denied=negative,
                pid=rows[3], output_sha=sha(wire))
