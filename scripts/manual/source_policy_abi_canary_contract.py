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
         and sorted(h.get('SecurityOpt', [])) == sorted(['no-new-privileges', security, 'apparmor=docker-default'])
         and h.get('Memory') == h.get('MemorySwap') == 536870912 and h.get('NanoCpus') == 1000000000
         and h.get('PidsLimit') == 64 and h.get('PidMode') == '' and h.get('IpcMode') == 'private'
         and h.get('CgroupnsMode') == 'private'
         and h.get('Runtime') == 'runc' and h.get('UTSMode') == '' and h.get('UsernsMode') == ''
         and h.get('AutoRemove') is False and not h.get('Init')
         and h.get('RestartPolicy') == dict(Name='no', MaximumRetryCount=0)
         and not any(h.get(k) for k in ('Binds','Tmpfs','Devices','DeviceRequests','PortBindings',
             'ExtraHosts','VolumesFrom','Links','Dns','GroupAdd','Sysctls','CgroupParent'))
         and not row.get('Mounts') and h.get('PublishAllPorts') is False,
         'container-containment')
    need(h.get('LogConfig') == dict(Type='local', Config={'max-size':'16k','max-file':'1','compress':'false'})
         and sorted(h.get('Ulimits', []),key=lambda r:r['Name']) == [dict(Name='core',Hard=0,Soft=0),
             dict(Name='nofile',Hard=64,Soft=64)], 'container-limits')


def container_diagnostics(row, identity, frame, abi, security):
    """Finite mismatch names only; never authority and never used for admission."""
    result=dict(format='ADBOWNEDABIPROFILEDIAG1',status='compared',matched=False,
                mismatches=[],categories={},authority=False)
    if type(row) is not dict or type(row.get('Config')) is not dict or type(row.get('HostConfig')) is not dict:
        result.update(status='malformed',mismatches=['shape.inspect'])
        return result
    config=row['Config']; host=row['HostConfig']; failed=result['mismatches']

    def check(name, predicate):
        try:
            passed=bool(predicate())
        except (TypeError,ValueError,KeyError,AttributeError):
            passed=False
        if not passed:
            failed.append(name)

    # Mirror the unchanged guard, including its comparison/truthiness semantics.
    check('selector.abi',lambda:abi in ABIS)
    for name,expected in (('Image',identity),('Path','/canary'),('Args',[abi]),('AppArmorProfile','docker-default')):
        check('root.'+name,lambda name=name,expected=expected:row.get(name) == expected)
    for name,expected in (('Labels',labels(frame)),('Entrypoint',['/canary']),('Cmd',[abi]),
                          ('User','65532:65532'),('WorkingDir','/'),('Hostname','abi-canary')):
        check('Config.'+name,lambda name=name,expected=expected:config.get(name) == expected)
    check('Config.Env',lambda:config.get('Env') in (None, []))
    for name in ('OpenStdin','StdinOnce','Tty','Volumes','ExposedPorts','Healthcheck'):
        check('Config.'+name,lambda name=name:not config.get(name))
    for name,expected in (('ReadonlyRootfs',True),('Privileged',False),('AutoRemove',False),('PublishAllPorts',False)):
        check('HostConfig.'+name,lambda name=name,expected=expected:host.get(name) is expected)
    for name,expected in (('NetworkMode','none'),('CapDrop',['ALL']),('Memory',536870912),('MemorySwap',536870912),
                          ('NanoCpus',1000000000),('PidsLimit',64),('PidMode',''),('IpcMode','private'),
                          ('CgroupnsMode','private'),('Runtime','runc'),('UTSMode',''),('UsernsMode',''),
                          ('RestartPolicy',dict(Name='no',MaximumRetryCount=0)),
                          ('LogConfig',dict(Type='local',Config={'max-size':'16k','max-file':'1','compress':'false'}))):
        check('HostConfig.'+name,lambda name=name,expected=expected:host.get(name) == expected)
    check('HostConfig.SecurityOpt',lambda:sorted(host.get('SecurityOpt', [])) == sorted(['no-new-privileges',security,'apparmor=docker-default']))
    check('HostConfig.Ulimits',lambda:sorted(host.get('Ulimits', []),key=lambda r:r['Name']) ==
          [dict(Name='core',Hard=0,Soft=0),dict(Name='nofile',Hard=64,Soft=64)])
    for name in ('CapAdd','Init','Binds','Tmpfs','Devices','DeviceRequests','PortBindings','ExtraHosts',
                 'VolumesFrom','Links','Dns','GroupAdd','Sysctls','CgroupParent'):
        check('HostConfig.'+name,lambda name=name:not host.get(name))
    check('root.Mounts',lambda:not row.get('Mounts'))
    env=config.get('Env'); apparmor=row.get('AppArmorProfile')
    result['categories']=dict(
        environment='none' if env is None else 'empty' if env == [] else 'nonempty-list' if type(env) is list else 'other',
        default_path_only=env == ['PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin'],
        apparmor='missing' if apparmor is None else 'empty' if apparmor == '' else
                 'docker-default' if apparmor == 'docker-default' else 'other')
    result['matched']=not failed
    return result


START_LAYERS=('docker-daemon','containerd-shim','oci-runtime','runc','attach')
START_TOPICS=('permission','missing-path','exec-format','apparmor','seccomp','cgroup','namespace','unknown')
START_KINDS=('spawn-error','cli-exited','timeout','overflow','command-error')
START_STATES=('created','restarting','running','removing','paused','exited','dead')

# Whole source envelopes, not a decoder for the historical unknown message.
# Provenance/literal recipes are frozen here; no runtime source fetch or override.
SOURCE_ENVELOPES=(
    ('start-paused','moby/moby@6430e49a55babd9b8f4d08e70ecb2b68900770fe:daemon/start.go#32',
     'cannot start a paused container, try unpause instead'),
    ('start-removing','moby/moby@6430e49a55babd9b8f4d08e70ecb2b68900770fe:daemon/start.go#42,90',
     'container is marked for removal and cannot be started'),
    ('attach-paused','moby/moby@6430e49a55babd9b8f4d08e70ecb2b68900770fe:daemon/attach.go#36',
     'container {cid} is paused, unpause the container before attach'),
    ('attach-restarting','moby/moby@6430e49a55babd9b8f4d08e70ecb2b68900770fe:daemon/attach.go#39',
     'container {cid} is restarting, wait until the container is running'),
    ('api-empty-message-500','docker/cli@b8034c0ed70494a90c133461d145cd072d920d7c:vendor/github.com/docker/docker/client/request.go#262',
     'API returned a 500 (Internal Server Error) but provided no error-message'),
)
SOURCE_ENVELOPE_SHA=sha(encode(SOURCE_ENVELOPES)) # Cached catalog bookkeeping, not a message lookup.


def _source_templates(cid):
    need(type(cid) is str and re.fullmatch('[a-f0-9]{64}',cid),'source-envelope-cid')
    return tuple((family,('Error response from daemon: '+body.replace('{cid}',cid)).encode('ascii'))
                 for family,_,body in SOURCE_ENVELOPES)


def _source_envelope_scan(raw, cid):
    if type(raw) is not bytes or len(raw)>4096:return 'invalid',None,0
    # Charge one correlation-hash pass (performed by the caller), one validation
    # pass, two fixed CID slots and the cached digest. The view makes no raw copy.
    work=2*len(raw)+128+64
    end=len(raw)
    if raw.endswith(b'\n'):
        end-=1
        if end and raw[end-1]==13:end-=1
    if not 0<end<=1024:return 'invalid',None,work
    for index,byte in enumerate(raw):
        if index<end and not 32<=byte<=126:return 'invalid',None,work
    candidate=memoryview(raw)[:end];matches=[]
    for family,envelope in _source_templates(cid):
        work+=len(envelope)
        if candidate == envelope:matches.append(family)
    need(work<=2*len(raw)+1024,'source-envelope-work')
    if len(matches)>1:return 'ambiguous',None,work
    return ('matched',matches[0],work) if matches else ('unmatched',None,work)


def _source_eligible(packet):
    return packet['analysis']==dict(status='unclassified',layers=['docker-daemon'],topics=['unknown']) \
        and packet['streams']['stdout_complete'] and packet['streams']['stderr_complete']


def startup_source_envelope(packet, expected, raw):
    validate_startup(packet,expected)
    if not _source_eligible(packet):return None
    status='unavailable';family=None
    if raw is not None:
        need(type(raw) is bytes and len(raw)<=4096 and len(raw)==packet['streams']['stderr_bytes']
             and sha(raw)==packet['streams']['stderr_sha'],'source-envelope-correlation')
        status,family,_=_source_envelope_scan(raw,expected['cid'])
    result=dict(format='ADBOWNEDABISOURCEENV1',operation_sha=sha(encode(expected)),
                stderr_sha=packet['streams']['stderr_sha'],catalog_sha=SOURCE_ENVELOPE_SHA,
                status=status,family=family,authority=False,qualification=False)
    validate_startup_source(result,packet,expected)
    return result


def validate_startup_source(report, packet, expected):
    validate_startup(packet,expected)
    need(_source_eligible(packet),'source-envelope-eligibility')
    need(type(report) is dict and set(report)=={'format','operation_sha','stderr_sha','catalog_sha',
         'status','family','authority','qualification'} and type(report['format']) is str
         and report['format']=='ADBOWNEDABISOURCEENV1'
         and all(type(report[key]) is str for key in ('operation_sha','stderr_sha','catalog_sha'))
         and report['operation_sha']==sha(encode(expected)) and report['stderr_sha']==packet['streams']['stderr_sha']
         and report['catalog_sha']==SOURCE_ENVELOPE_SHA and report['authority'] is False
         and report['qualification'] is False,'source-envelope-packet')
    need(type(report['status']) is str and report['status'] in ('matched','unmatched','ambiguous','invalid','unavailable')
         and (report['status']=='matched' and type(report['family']) is str
              and report['family'] in tuple(row[0] for row in SOURCE_ENVELOPES)
              or report['status']!='matched' and report['family'] is None),'source-envelope-result')
    need(len(encode(report))<=512
         and len(encode(dict(startup_failure=packet,startup_source_envelope=report)))<=4096,'source-envelope-size')


def startup_binding(frame, image, cid, abi, negative):
    need(type(frame) is dict and frame == context(frame.get('head'),frame.get('run'),
         frame.get('attempt'),frame.get('source')), 'startup-context')
    need(type(image) is str and re.fullmatch('sha256:[a-f0-9]{64}',image)
         and type(cid) is str and re.fullmatch('[a-f0-9]{64}',cid)
         and type(abi) is str and abi in ABIS and type(negative) is bool, 'startup-binding')
    return dict(context_sha=sha(encode(frame)),image=image,cid=cid,abi=abi,policy='derived' if negative else 'builtin')


def _startup_scan(raw):
    """Fixed ASCII marker catalog; quoted/path/assignment tokens are never topics."""
    def result(status, layers=(), topics=()):
        return dict(status=status,layers=list(layers),topics=list(topics))
    if raw is None:
        return result('unavailable'),0
    if type(raw) is not bytes or len(raw)>4096:
        return result('invalid'),0
    if not raw:
        return result('unavailable'),0
    work=len(raw)
    if any(byte not in (9,10,13) and not 32<=byte<=126 for byte in raw):
        return result('invalid'),work
    text=raw.decode('ascii').replace('\r\n','\n'); work+=len(raw)
    lines=text.splitlines()
    if '\r' in text or len(lines)>16 or any(len(line)>1024 for line in lines):
        return result('invalid'),work
    # One bounded pass masks quoted spans. Malformed quoting is not repaired.
    masked=[]; quote=None; escaped=False
    for char in text:
        work+=1
        if quote:
            if char in ('\n','\r') or escaped and char not in (quote,'\\'):
                return result('invalid'),work
            masked.append(' ')
            if escaped: escaped=False
            elif char=='\\': escaped=True
            elif char==quote: quote=None
        elif char in ('"',"'",'`'):
            quote=char;masked.append(' ')
        else:
            masked.append(char)
    if quote or escaped:
        return result('invalid'),work
    # Mask whole untrusted path/URL/assignment/long-ID tokens; never return text.
    text=''.join(masked); safe=[]
    for token in re.finditer(r'\S+|\s+',text):
        word=token.group();work+=len(word)
        hidden='/' in word or '\\' in word or '=' in word or re.fullmatch('[a-fA-F0-9]{32,}',word)
        safe.append(' '*len(word) if hidden else word)
    lines=[line for line in ''.join(safe).splitlines() if line.strip()]
    if len(lines)>1:
        return result('ambiguous'),work
    if not lines:
        return result('unclassified'),work
    text=lines[0];work+=len(text)
    if text.startswith('Error response from daemon:'):
        layers=['docker-daemon']; body=text[len('Error response from daemon:'):]
    elif text.startswith('Error attaching:'):
        layers=['attach']; body=text[len('Error attaching:'):]
    else:
        return result('unclassified'),work
    if 'error response from daemon:' in body.lower() or 'error attaching:' in body.lower():
        return result('ambiguous'),work
    words=re.findall('[a-z0-9_-]+',body.lower());work+=len(body)
    layer_patterns={('failed','to','create','shim','task'):'containerd-shim',
                    ('oci','runtime','create','failed'):'oci-runtime',('runc','create','failed'):'runc'}
    topic_patterns={('permission','denied'):'permission',('operation','not','permitted'):'permission',
                    ('no','such','file','or','directory'):'missing-path',('exec','format','error'):'exec-format',
                    ('apparmor',):'apparmor',('seccomp',):'seccomp',('cgroup',):'cgroup',('namespace',):'namespace'}
    topics=set(); seen=[]
    for index in range(len(words)):
        for width in range(1,6):
            phrase=tuple(words[index:index+width]);work+=len(phrase)
            if phrase in layer_patterns:seen.append(layer_patterns[phrase])
            if phrase in topic_patterns:topics.add(topic_patterns[phrase])
    if len(seen)!=len(set(seen)) or seen!=sorted(seen,key=START_LAYERS.index):
        return result('ambiguous'),work
    layers.extend(seen)
    need(work<=16*len(raw)+1024,'startup-parser-work')
    return result('recognized' if seen or topics or 'attach' in layers else 'unclassified',
                  [layer for layer in START_LAYERS if layer in layers],
                  [topic for topic in START_TOPICS if topic in topics] or ['unknown']),work


def classify_startup(raw):
    return _startup_scan(raw)[0]


def startup_packet(frame, image, phase, binding, argv, out, err, eof, spawned, returncode, kind, flags):
    need(type(binding) is dict and set(binding)=={'context_sha','image','cid','abi','policy'}
         and binding.get('policy') in ('builtin','derived'), 'startup-binding-shape')
    expected=startup_binding(frame,image,binding.get('cid'),binding.get('abi'),binding['policy']=='derived')
    need(binding==expected and type(phase) is str and phase==binding['abi']+'-'+binding['policy']
         and type(argv) is list and argv==['/usr/bin/docker','--host','unix:///var/run/docker.sock',
                                         'start','--attach',binding['cid']], 'startup-operation')
    need(type(flags) is dict and set(flags)=={'timeout','limit','native','cleanup','input_path'}
         and type(flags['timeout']) is int and flags['timeout']==15 and type(flags['limit']) is int
         and flags['limit']==4096 and flags['native'] is True and flags['cleanup'] is False
         and flags['input_path'] is None, 'startup-operation-flags')
    need(type(out) is bytes and type(err) is bytes and len(out)+len(err)<=12288
         and type(eof) is tuple and len(eof)==2 and all(type(v) is bool for v in eof)
         and type(spawned) is bool and type(kind) is str and kind in START_KINDS, 'startup-observation')
    complete=all(eof) and len(out)+len(err)<=4096 and kind not in ('overflow','timeout','spawn-error')
    packet=dict(format='ADBOWNEDABISTARTFAIL1',operation='container-start-attach',authority=False,
        qualification=False,candidate_execution=False,sql=False,production_wiring=False,binding=dict(binding),
        outcome=dict(kind=kind,cli_spawned=spawned,returncode=returncode,
                     signal=-returncode if type(returncode) is int and returncode<0 else None),
        streams=dict(stdout_bytes=len(out),stderr_bytes=len(err),stdout_complete=complete and eof[0],
                     stderr_complete=complete and eof[1],stderr_sha=sha(err) if complete else None),
        analysis=classify_startup(err if complete else None),cleanup=None)
    validate_startup(packet,expected)
    return packet


def validate_startup(packet, expected):
    need(type(expected) is dict and set(expected)=={'context_sha','image','cid','abi','policy'}
         and all(type(value) is str for value in expected.values())
         and re.fullmatch('[a-f0-9]{64}',expected['context_sha'])
         and re.fullmatch('sha256:[a-f0-9]{64}',expected['image']) and re.fullmatch('[a-f0-9]{64}',expected['cid'])
         and expected['abi'] in ABIS and expected['policy'] in ('builtin','derived'), 'startup-expected-binding')
    keys={'format','operation','authority','qualification','candidate_execution','sql','production_wiring',
          'binding','outcome','streams','analysis','cleanup'}
    need(type(packet) is dict and set(packet)==keys and type(packet['format']) is str
         and packet['format']=='ADBOWNEDABISTARTFAIL1' and type(packet['operation']) is str
         and packet['operation']=='container-start-attach'
         and all(packet[key] is False for key in ('authority','qualification','candidate_execution','sql','production_wiring'))
         and type(packet['binding']) is dict and set(packet['binding'])==set(expected)
         and all(type(value) is str for value in packet['binding'].values())
         and packet['binding']==expected, 'startup-packet')
    outcome=packet['outcome']; streams=packet['streams']; analysis=packet['analysis']
    need(type(outcome) is dict and set(outcome)=={'kind','cli_spawned','returncode','signal'}
         and type(outcome['kind']) is str and outcome['kind'] in START_KINDS
         and type(outcome['cli_spawned']) is bool, 'startup-outcome')
    rc=outcome['returncode']; sig=outcome['signal']
    need((rc is None or type(rc) is int and -64<=rc<=255)
         and (sig is None or type(sig) is int and 1<=sig<=64)
         and sig==(-rc if type(rc) is int and rc<0 else None)
         and (outcome['cli_spawned'] or rc is None)
         and (outcome['kind']!='spawn-error' or not outcome['cli_spawned'])
         and (outcome['kind']!='cli-exited' or outcome['cli_spawned'] and rc is not None), 'startup-status')
    need(type(streams) is dict and set(streams)=={'stdout_bytes','stderr_bytes','stdout_complete','stderr_complete','stderr_sha'}
         and all(type(streams[k]) is int and 0<=streams[k]<=12288 for k in ('stdout_bytes','stderr_bytes'))
         and streams['stdout_bytes']+streams['stderr_bytes']<=12288
         and all(type(streams[k]) is bool for k in ('stdout_complete','stderr_complete')), 'startup-streams')
    complete=streams['stdout_complete'] and streams['stderr_complete']
    need((not complete and streams['stderr_sha'] is None or complete and type(streams['stderr_sha']) is str
          and re.fullmatch('[a-f0-9]{64}',streams['stderr_sha']))
         and (not complete or streams['stdout_bytes']+streams['stderr_bytes']<=4096)
         and (outcome['kind'] not in ('spawn-error','timeout','overflow') or not complete)
         and (outcome['kind']!='cli-exited' or complete)
         and (outcome['cli_spawned'] or not complete and streams['stdout_bytes']==streams['stderr_bytes']==0)
         and (outcome['kind']!='cli-exited' or rc!=0 or streams['stderr_bytes']>0), 'startup-completeness')
    need(type(analysis) is dict and set(analysis)=={'status','layers','topics'}
         and type(analysis['status']) is str and analysis['status'] in ('recognized','unclassified','ambiguous','unavailable','invalid')
         and type(analysis['layers']) is list and type(analysis['topics']) is list
         and all(type(n) is str for n in analysis['layers']+analysis['topics'])
         and analysis['layers']==[n for n in START_LAYERS if n in analysis['layers']]
         and analysis['topics']==[n for n in START_TOPICS if n in analysis['topics']]
         and (complete or analysis==dict(status='unavailable',layers=[],topics=[]))
         and (analysis['status'] not in ('unavailable','invalid','ambiguous') or not analysis['layers'] and not analysis['topics'])
         and (analysis['status']!='recognized' or ('docker-daemon' in analysis['layers'] or 'attach' in analysis['layers'])
              and bool(analysis['topics']) and not (analysis['layers']==['docker-daemon'] and analysis['topics']==['unknown'])),
         'startup-analysis')
    cleanup=packet['cleanup']
    if cleanup is not None:
        need(type(cleanup) is dict and set(cleanup)=={'status','basis','state','running','oom','exit_code'}
             and type(cleanup['basis']) is str and cleanup['basis']=='first-owned-cleanup-readback-after-failure'
             and type(cleanup['status']) is str and cleanup['status'] in ('observed','unavailable','refused'), 'startup-cleanup-shape')
        if cleanup['status']=='observed':
            need(type(cleanup['state']) is str and cleanup['state'] in START_STATES
                 and type(cleanup['running']) is bool and type(cleanup['oom']) is bool
                 and (not cleanup['running'] or cleanup['state'] not in ('created','exited','dead'))
                 and (cleanup['state'] not in ('running','paused') or cleanup['running'])
                 and (cleanup['state'] not in ('exited','dead') and cleanup['exit_code'] is None
                      or cleanup['state'] in ('exited','dead') and not cleanup['running']
                      and type(cleanup['exit_code']) is int and 0<=cleanup['exit_code']<=255), 'startup-cleanup-state')
        else:
            need(all(cleanup[k] is None for k in ('state','running','oom','exit_code')), 'startup-cleanup-unavailable')
    need(len(encode(packet))<=4096,'startup-packet-budget')


def startup_cleanup(packet, frame, image, cid, row):
    need(type(packet) is dict,'startup-cleanup-packet')
    binding=packet.get('binding',{})
    need(type(binding) is dict and type(binding.get('policy')) is str,'startup-cleanup-binding')
    expected=startup_binding(frame,image,cid,binding.get('abi'),binding.get('policy')=='derived')
    validate_startup(packet,expected)
    need(type(row) is dict and type(row.get('Id')) is str and row.get('Id')==cid
         and type(row.get('Image')) is str and row.get('Image')==image
         and type(row.get('Config')) is dict and type(row['Config'].get('Labels')) is dict
         and all(type(v) is str for v in row['Config']['Labels'].values())
         and row['Config'].get('Labels')==labels(frame), 'startup-cleanup-owner')
    state=row.get('State');need(type(state) is dict,'startup-cleanup-state')
    result=dict(status='observed',basis='first-owned-cleanup-readback-after-failure',state=state.get('Status'),
                running=state.get('Running'),oom=state.get('OOMKilled'),
                exit_code=state.get('ExitCode') if state.get('Status') in ('exited','dead') else None)
    changed=dict(packet,cleanup=result);validate_startup(changed,expected)
    return result


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
