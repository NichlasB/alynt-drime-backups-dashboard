"""Fixed upstream policy preservation DATA; no IO, profile loader or executor.

Context/source declarations are copied DATA, not live engine/builtin provenance.
The independent verifier reparses the pinned original, not the transformer's tree.
"""
import hashlib
import json
import re

MODEL = 'candidate-moby-28.0.4-effect-denial-v1'
MOBY_COMMIT = '6430e49a55babd9b8f4d08e70ecb2b68900770fe'
CLI_COMMIT = 'b8034c0ed70494a90c133461d145cd072d920d7c'
MANIFEST = '14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1'
CONFIG = '2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35'
BASELINE_SHA = '9c1025c88ccaa517b648da571961838744ea2137f176bfe6a48b21294cae9c76'
SOURCE_PINS = (
    ('moby/profiles/seccomp/default.json', BASELINE_SHA),
    ('moby/profiles/seccomp/default_linux.go', '4529ff8a038f4365ba865d30be2468f6c64b80c832567d978e91906999cc632b'),
    ('moby/profiles/seccomp/seccomp_linux.go', 'b95bbcb005cd7f049b033e4f64d91a7f35c2faf707198bf2218075298d5c465e'),
    ('moby/profiles/seccomp/generate.go', 'b062504acf993cf85953040cc4e5eb4829251c6c6383660dc6c1c0a62cb675c5'),
    ('moby/profiles/seccomp/seccomp.go', '40d8214c6dfc7eb495753b437b6ca17b25da304c3b3fc3f1c933d15de067738e'),
    ('moby/profiles/seccomp/kernel_linux.go', '4ad0494e6d12f1cf30af8a55b3bd4ccd0b4b5fc65902b8888be7e0ed0d04591e'),
    ('moby/profiles/seccomp/seccomp_test.go', 'a1ae21a8ccb727231012635de9e9de01d8349453dddf9d75ef2876c1401b4914'),
    ('moby/daemon/seccomp_linux.go', 'ef55499aaa843322028734c74604ecba3a68686436320c1ad4bef2bc6b5b0860'),
    ('moby/daemon/oci_linux.go', '816b53630dca6536a0ea97a2e9156818ec2a855761c887e3b5372c06ca198b3a'),
    ('moby/daemon/config/config.go', '57fcdcb6cbc0e3a1aa49b302e5a5ec7c612703c7154e9081b779ebc1ffe13d51'),
    ('moby/vendor/github.com/opencontainers/runtime-spec/specs-go/config.go', '31dd6e0933c9e85c798319b307c8a2093c7866808f6f473f0cfa827a131b1b61'),
    ('cli/cli/command/container/opts.go', '5152629b6638c0a307da1c24a9758b443d8c66b1eee35d38ec77ed6265d29109'),
)
LOCAL_SOURCES = (
    'scripts/ci/source-policy-legacy-ubi-static.py', '.github/workflows/source-policy-legacy-ubi-static.yml',
    'scripts/manual/source-policy-linux-minimum-acquire.py', 'scripts/manual/source_policy_linux_minimum_static.py',
    'scripts/manual/source_policy_ubi_provenance.py', 'scripts/manual/source_policy_ubi_alias_static.py',
    'scripts/manual/source_policy_virtual_closure.py', 'scripts/manual/source_policy_ubi_elf_static.py',
    'scripts/manual/source_policy_ubi_import_contract.py', 'scripts/manual/source_policy_ubi_runtime_contract.py',
    'scripts/manual/source_policy_moby_policy.py', 'tests/fixtures/moby-28.0.4/default.json.b64',
)
DENIED = ('socket','socketpair','socketcall','connect','bind','listen','accept','accept4','shutdown',
          'getsockname','getpeername','setsockopt','getsockopt','send','sendto','sendmsg','sendmmsg',
          'recv','recvfrom','recvmsg','recvmmsg','recvmmsg_time64')


class Refusal(ValueError):
    pass


def require(value, reason):
    if not value:
        raise Refusal(reason)


def encode(value):
    return json.dumps(value, sort_keys=True, separators=(',', ':'), ensure_ascii=True).encode('ascii')


def _hex(value, length):
    return type(value) is str and re.fullmatch('[a-f0-9]{%d}' % length, value) is not None


def _frame(frame):
    keys = {'format','manifest','config','profile','engine_version','engine_commit','client_version','client_commit',
            'head','run','attempt','static_source','runtime_source','snapshot','graph'}
    require(type(frame) is dict and set(frame)==keys and all(type(frame[key]) is str for key in keys-{'attempt'}), 'context-shape')
    require(frame['format']=='ADBUMOBYCONTEXT1' and frame['manifest']==MANIFEST and frame['config']==CONFIG
            and frame['profile']==MODEL and frame['engine_version']=='28.0.4' and frame['engine_commit']==MOBY_COMMIT
            and frame['client_version']=='28.0.4' and frame['client_commit']==CLI_COMMIT, 'candidate-context')
    require(_hex(frame['head'],40) and re.fullmatch('[1-9][0-9]{0,19}',frame['run']) is not None
            and type(frame['attempt']) is int and 1<=frame['attempt']<=100000, 'run-context')
    require(all(_hex(frame[key],64) for key in ('static_source','runtime_source','snapshot','graph'))
            and frame['static_source']!=frame['runtime_source'], 'source-context')


def runtime_source(rows):
    require(type(rows) is tuple and len(rows)==len(LOCAL_SOURCES)
            and all(type(row) is tuple and len(row)==2 and type(row[0]) is str and row[0]==path and _hex(row[1],64)
                    for row,path in zip(rows,LOCAL_SOURCES)), 'local-source-cohort')
    return hashlib.sha256(encode(rows)).hexdigest()


def _object(pairs):
    result = {}
    for key,value in pairs:
        require(key not in result, 'duplicate-json-key')
        result[key] = value
    return result


def _reject(value):
    raise Refusal('non-integral-json')


def _parse(data, limit, meter):
    require(type(data) is bytes and 0<len(data)<=limit, 'policy-size')
    meter.charge('control',len(data)); meter.charge('expanded',len(data)); meter.charge('work',len(data))
    try:
        result=json.loads(data,object_pairs_hook=_object,parse_float=_reject,parse_constant=_reject)
    except (ValueError,UnicodeError,RecursionError):
        raise Refusal('policy-json') from None
    pending=[(result,0)]; visited=0
    while pending:
        value,depth=pending.pop(); visited+=1; meter.charge('work',1)
        require(depth<=16 and visited<=4096, 'policy-traversal')
        if type(value) is dict:
            require(len(value)<=512, 'policy-keys')
            pending.extend((child,depth+1) for child in value.values())
        elif type(value) is list:
            require(len(value)<=512, 'policy-list')
            pending.extend((child,depth+1) for child in value)
        else:
            require(type(value) in (str,int,bool,type(None)), 'policy-value')
    return result


def _admit(raw, frame, sources, meter):
    _frame(frame)
    require(type(sources) is tuple and len(sources)==len(SOURCE_PINS)
            and all(type(row) is tuple and len(row)==2 and all(type(value) is str for value in row) for row in sources)
            and sources == SOURCE_PINS, 'source-pins')
    require(type(raw) is bytes and len(raw)==12828 and hashlib.sha256(raw).hexdigest() == BASELINE_SHA, 'baseline-pin')
    meter.charge('control',len(encode(sources))); meter.charge('work',len(sources))
    baseline=_parse(raw,16384,meter)
    require(type(baseline) is dict and set(baseline)=={'defaultAction','defaultErrnoRet','archMap','syscalls'}
            and baseline['defaultAction']=='SCMP_ACT_ERRNO' and type(baseline['defaultErrnoRet']) is int
            and baseline['defaultErrnoRet']==1 and len(baseline['syscalls'])==31
            and sum(len(row['names']) for row in baseline['syscalls'])==428, 'baseline-shape')
    return baseline


def _same(actual, expected, meter):
    pending=[(actual,expected)]
    while pending:
        actual,expected=pending.pop(); meter.charge('work',1)
        if type(actual) is not type(expected):
            return False
        if type(expected) is dict:
            if len(actual)!=len(expected) or set(actual)!=set(expected): return False
            pending.extend((actual[key],value) for key,value in expected.items())
        elif type(expected) is list:
            if len(actual)!=len(expected): return False
            pending.extend(zip(actual,expected))
        elif actual!=expected:
            return False
    return True


def derive(raw, frame, sources, meter):
    # This tree is freshly parsed from raw, never supplied by the caller/verifier.
    profile=_admit(raw,frame,sources,meter); retained=[]
    for row in profile['syscalls']:
        meter.charge('work',len(row['names']))
        if row['action']=='SCMP_ACT_ALLOW':
            row['names']=[name for name in row['names'] if name not in DENIED]
        if row['names']: retained.append(row)
    profile['syscalls']=retained
    policy=encode(profile)
    return dict(policy=policy,evidence=verify(raw,policy,frame,sources,meter))


def verify(raw, policy, frame, sources, meter):
    baseline=_admit(raw,frame,sources,meter)
    changed=_parse(policy,65536,meter)
    require(type(changed) is dict and type(changed.get('syscalls')) is list
            and policy==encode(changed), 'canonical-derived')
    protected={key:value for key,value in baseline.items() if key!='syscalls'}
    require(_same({key:value for key,value in changed.items() if key!='syscalls'}, protected, meter), 'protected-top')
    output=changed['syscalls']; cursor=removed=0
    for original in baseline['syscalls']:
        names=[]
        for name in original['names']:
            meter.charge('work',1)
            if original['action']=='SCMP_ACT_ALLOW' and name in DENIED: removed+=1
            else: names.append(name)
        if not names: continue
        require(cursor<len(output) and type(output[cursor]) is dict, 'rule-count')
        actual=output[cursor]; fields={key:value for key,value in original.items() if key!='names'}
        require(_same({key:value for key,value in actual.items() if key!='names'}, fields, meter), 'protected-rule')
        require(_same(actual.get('names'),names,meter), 'retained-name-order')
        cursor+=1
    require(cursor==len(output)==30 and removed==22 and sum(len(row['names']) for row in output)==406, 'exact-delta')
    require(not any(name in DENIED or name.startswith('io_uring_') for row in output
                    if row['action']=='SCMP_ACT_ALLOW' for name in row['names']), 'network-route')
    return _evidence(policy,frame,sources,meter)


def _evidence(policy, frame, sources, meter):
    result=dict(format='ADBUMOBYPOLICY1',model=MODEL,context=dict(frame),baseline_sha=BASELINE_SHA,
                source_manifest_sha=hashlib.sha256(encode(sources)).hexdigest(),derived_sha=hashlib.sha256(policy).hexdigest(),
                proposed_transport_sha=hashlib.sha256(policy).hexdigest(),transport_readback_sha=None,
                denied_names=list(DENIED),rules_before=31,rules_after=30,names_before=428,names_after=406,
                required_abis=['SCMP_ARCH_X86_64','SCMP_ARCH_X86','SCMP_ARCH_X32'],x32_syscall_bit=0x40000000,
                preservation_verified_data=True,source_correspondence='not-established',abi_coverage='unestablished',
                kernel_verified=False,launch_authority=False,runtime_grant=False)
    summary=encode(result); require(len(summary)<=65536, 'summary-budget')
    meter.charge('observation',len(summary)+len(policy))
    return result
