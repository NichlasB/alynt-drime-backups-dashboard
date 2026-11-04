"""Isolated owned-profile tests. No compiler, Docker, sockets or native execution."""
import base64
import copy
import importlib.util
import inspect
import io
import json
import os
from pathlib import Path
import re
import stat as stat_module
import struct
import sys
import tarfile
import tempfile
import textwrap
import unittest
from unittest.mock import patch

ROOT=Path(__file__).resolve().parents[2]


def load(name,path):
    spec=importlib.util.spec_from_file_location(name,ROOT/path)
    m=importlib.util.module_from_spec(spec); spec.loader.exec_module(m); return m


c=load('abi_contract','scripts/manual/source_policy_abi_canary_contract.py')
m=load('abi_moby','scripts/manual/source_policy_moby_policy.py')
i=load('abi_meter','scripts/manual/source_policy_ubi_import_contract.py')
o=load('abi_owner','scripts/ci/source-policy-owned-abi-canary.py')
RAW=base64.b64decode(b''.join((ROOT/c.SOURCES[-1]).read_bytes().splitlines()),validate=True)
FRAME=c.context('a'*40,'123','1','b'*64)
PROFILE_CASES=json.loads((ROOT/'tests/fixtures/moby-28.0.4/owned-abi-readback-scope.json').read_text())


def binary():
    data=bytearray(128); data[:7]=b'\x7fELF\x02\x01\x01'
    struct.pack_into('<HH',data,16,2,62); struct.pack_into('<Q',data,32,64)
    struct.pack_into('<HH',data,54,56,1)
    struct.pack_into('<IIQQQQQQ',data,64,1,5,0,0x400000,0x400000,128,128,4096)
    return bytes(data)


def container():
    return dict(Image='sha256:'+'c'*64,Mounts=[],Path='/canary',Args=['amd64'],AppArmorProfile='docker-default',Config=dict(Labels=c.labels(FRAME),
        Entrypoint=['/canary'],Cmd=['amd64'],User='65532:65532',WorkingDir='/',Env=[],Hostname='abi-canary'),
        HostConfig=dict(ReadonlyRootfs=True,NetworkMode='none',Privileged=False,CapDrop=['ALL'],
            SecurityOpt=copy.deepcopy(PROFILE_CASES['explicit_default']['HostConfig']['SecurityOpt']),Memory=536870912,MemorySwap=536870912,
            NanoCpus=1000000000,PidsLimit=64,PidMode='',IpcMode='private',CgroupnsMode='private',
            Runtime='runc',UTSMode=PROFILE_CASES['explicit_default']['HostConfig']['UTSMode'],UsernsMode='',AutoRemove=False,
            RestartPolicy=dict(Name='no',MaximumRetryCount=0),PublishAllPorts=False,
            LogConfig=dict(Type='local',Config={'max-size':'16k','max-file':'1','compress':'false'}),
            Ulimits=[dict(Name='core',Hard=0,Soft=0),dict(Name='nofile',Hard=64,Soft=64)]))


def wire(abi,negative=False):
    subs=c.SUBS[c.ABIS.index(abi)]+(tuple(range(1,21)) if abi=='i386' else ())
    values=[65532,1,2,7]+[(-1 if negative else 3 if sub==1 else 0 if sub==8 else -9) for sub in subs]+[7]
    return b''.join(str(n).encode()+b'\n' for n in values)


class Tests(unittest.TestCase):
    def test_separate_context_rejects_candidate_or_malformed_identity(self):
        for key,value in (('head','A'*40),('run','0'),('attempt',True),('source','z'*64)):
            args=dict(head='a'*40,run='123',attempt='1',source='b'*64);args[key]=value
            with self.assertRaises(c.Refusal): c.context(**args)
        with self.assertRaises(c.Refusal): c.policy(RAW,dict(FRAME,graph='a'*64),m,i.Meter())
        with self.assertRaises(m.Refusal): m._frame(FRAME)

    def test_raw_pin_precedes_parser_and_real_subtraction_is_positive(self):
        with patch.object(m,'_parse',side_effect=AssertionError('protected parser called')):
            with self.assertRaisesRegex(c.Refusal,'raw-pin'): c.policy(RAW+b'\n',FRAME,m,i.Meter())
        out=json.loads(c.policy(RAW,FRAME,m,i.Meter()))
        self.assertEqual(30,len(out['syscalls']))
        self.assertEqual(406,sum(len(r['names']) for r in out['syscalls']))
        self.assertEqual(json.loads(RAW)['archMap'],out['archMap'])

    def test_independent_verifier_rejects_retained_mutations(self):
        good=json.loads(c.policy(RAW,FRAME,m,i.Meter())); original=json.loads(RAW)
        for key,value in (('defaultErrnoRet',38),('archMap',[]),('defaultAction','SCMP_ACT_ALLOW')):
            changed=copy.deepcopy(good);changed[key]=value
            with self.assertRaises(c.Refusal): c.verify_policy(original,m.encode(changed),m,i.Meter())
        for edit in ('arg','name','action','missing'):
            changed=copy.deepcopy(good)
            if edit=='arg': changed['syscalls'][-1]['args']=[]
            if edit=='name': changed['syscalls'][0]['names'].reverse()
            if edit=='action': changed['syscalls'][0]['action']='SCMP_ACT_ERRNO'
            if edit=='missing': changed['syscalls'].pop()
            with self.assertRaises(c.Refusal): c.verify_policy(original,m.encode(changed),m,i.Meter())

    def test_static_elf_rejects_loader_dynamic_high_address_and_wx(self):
        self.assertEqual(64,len(c.elf(binary())))
        for offset,fmt,value in ((64,'<I',3),(64,'<I',2),(68,'<I',7),(80,'<Q',0x100000000),(16,'<H',3)):
            bad=bytearray(binary());struct.pack_into(fmt,bad,offset,value)
            with self.assertRaises(c.Refusal): c.elf(bytes(bad))

    def test_owned_image_has_only_owned_file_no_tags_base_or_entry_override(self):
        identity,archive,layer=c.image(binary(),FRAME)
        with tarfile.open(fileobj=io.BytesIO(archive)) as outer:
            self.assertEqual([identity[7:]+'.json','layer.tar','manifest.json'],outer.getnames())
            config=json.load(outer.extractfile(identity[7:]+'.json'))
            with tarfile.open(fileobj=io.BytesIO(outer.extractfile('layer.tar').read())) as inner:
                self.assertEqual(['canary'],inner.getnames())
                self.assertEqual(0o555,inner.getmember('canary').mode)
            self.assertEqual(['sha256:'+layer],config['rootfs']['diff_ids'])
        row=dict(Id=identity,Os='linux',Architecture='amd64',Config=config['config'],RepoTags=[])
        c.image_readback(row,identity,FRAME)
        for key,value in (('Entrypoint',['/bin/sh']),('Healthcheck',{'Test':['CMD','x']}),('Labels',{})):
            bad=copy.deepcopy(row);bad['Config'][key]=value
            with self.assertRaises(c.Refusal): c.image_readback(bad,identity,FRAME)
        self.assertNotEqual(identity,c.image(binary(),dict(FRAME,run='124'))[0])

    def test_container_guards_non_vacuous_positive_and_mutation_matrix(self):
        row=container(); identity=row['Image']
        c.container_readback(row,identity,FRAME,'amd64','seccomp=builtin')
        mutations={'ReadonlyRootfs':False,'NetworkMode':'host','Privileged':True,'CapDrop':[],
            'CapAdd':['SYS_ADMIN'],'SecurityOpt':['seccomp=unconfined'],'Memory':0,'MemorySwap':-1,
            'NanoCpus':0,'PidsLimit':0,'PidMode':'host','IpcMode':'host','CgroupnsMode':'host','Binds':['/tmp:/tmp'],
            'Runtime':'other','UTSMode':'host','UsernsMode':'host','AutoRemove':True,'Init':True,
            'Devices':[{}],'DeviceRequests':[{}],'Tmpfs':{'/tmp':'rw'},'PortBindings':{'80/tcp':[{}]},
            'RestartPolicy':dict(Name='always',MaximumRetryCount=0),'LogConfig':{},'Ulimits':[]}
        for key,value in mutations.items():
            bad=copy.deepcopy(row);bad['HostConfig'][key]=value
            with self.subTest(key=key), self.assertRaises(c.Refusal):
                c.container_readback(bad,identity,FRAME,'amd64','seccomp=builtin')
        for key,value in (('User','0'),('Cmd',['/bin/sh']),('Labels',{}),('Env',['X=Y']),('OpenStdin',True)):
            bad=copy.deepcopy(row);bad['Config'][key]=value
            with self.assertRaises(c.Refusal): c.container_readback(bad,identity,FRAME,'amd64','seccomp=builtin')

    def test_explicit_single_file_uncompressed_log_contract(self):
        row=container();identity=row['Image']
        expected=dict(Type='local',Config={'max-size':'16k','max-file':'1','compress':'false'})
        row['HostConfig']['LogConfig']=copy.deepcopy(expected)
        c.container_readback(row,identity,FRAME,'amd64','seccomp=builtin')
        self.assertTrue(c.container_diagnostics(row,identity,FRAME,'amd64','seccomp=builtin')['matched'])
        bads=[]
        for value in (None,'true',True,False,0,'FALSE','0',''):
            bad=copy.deepcopy(expected)
            if value is None:del bad['Config']['compress']
            else:bad['Config']['compress']=value
            bads.append(bad)
        for key,value in (('max-size','32k'),('max-file','2'),('max-file',1),('extra','PRIVATE_LOG_VALUE')):
            bad=copy.deepcopy(expected);bad['Config'][key]=value;bads.append(bad)
        bad=copy.deepcopy(expected);bad['Type']='json-file';bads.append(bad)
        bads.extend((None,{},dict(Type='local',Config={})))
        for config in bads:
            bad=copy.deepcopy(row);bad['HostConfig']['LogConfig']=config
            with self.subTest(config=config):
                with self.assertRaises(c.Refusal):c.container_readback(bad,identity,FRAME,'amd64','seccomp=builtin')
                report=c.container_diagnostics(bad,identity,FRAME,'amd64','seccomp=builtin')
                self.assertFalse(report['matched']);self.assertIn('HostConfig.LogConfig',report['mismatches'])
                self.assertNotIn('PRIVATE_LOG_VALUE',c.encode(report).decode())

    def test_explicit_log_request_guard_and_diagnostic_semantic_controls(self):
        self.assertEqual(18,self.probe_fixture(o.Owner.probe)['routes'])
        source=textwrap.dedent(inspect.getsource(o.Owner.probe));needle="'compress=false'"
        self.assertEqual(1,source.count(needle));namespace=dict(o.__dict__)
        exec(compile(source.replace(needle,"'compress=true'"),'<owned-log-request-control>','exec'),namespace)
        with self.assertRaises(AssertionError):self.probe_fixture(namespace['probe'])
        row=container();row['HostConfig']['LogConfig']['Config']['compress']='true';identity=row['Image']
        with self.assertRaises(c.Refusal):c.container_readback(row,identity,FRAME,'amd64','seccomp=builtin')
        source=textwrap.dedent(inspect.getsource(c.container_readback))
        needle="h.get('LogConfig') == dict(Type='local', Config={'max-size':'16k','max-file':'1','compress':'false'})"
        self.assertEqual(1,source.count(needle));namespace=dict(c.__dict__)
        exec(compile(source.replace(needle,'True'),'<owned-log-guard-control>','exec'),namespace)
        namespace['container_readback'](row,identity,FRAME,'amd64','seccomp=builtin')
        self.assertFalse(c.container_diagnostics(row,identity,FRAME,'amd64','seccomp=builtin')['matched'])
        source=textwrap.dedent(inspect.getsource(c.container_diagnostics));needle="'compress':'false'"
        self.assertEqual(1,source.count(needle));namespace=dict(c.__dict__)
        exec(compile(source.replace(needle,"'compress':'true'"),'<owned-log-diagnostic-control>','exec'),namespace)
        self.assertTrue(namespace['container_diagnostics'](row,identity,FRAME,'amd64','seccomp=builtin')['matched'])

    def test_real_positive_required_before_negative_and_no_unavailable_pass(self):
        for abi in c.ABIS:
            self.assertTrue(c.observation(wire(abi),abi,False)['positive'])
            self.assertTrue(c.observation(wire(abi,True),abi,True)['denied'])
            with self.assertRaises(c.Refusal): c.observation(wire(abi,True),abi,False)
            with self.assertRaises(c.Refusal): c.observation(wire(abi),abi,True)
            for bad in (wire(abi).replace(b'65532',b'0'),wire(abi).replace(b'-9',b'-38'),
                        wire(abi)+b'7\n',wire(abi)[:-2]+b'8\n'):
                with self.assertRaises(c.Refusal): c.observation(bad,abi,False)

    def test_fixed_c_number_mapping_and_host_has_no_socket_path(self):
        source=(ROOT/'scripts/ci/source-policy-abi-canary.c').read_text()
        expected=[(41,359,41,1),(53,360,53,8),(42,362,42,3),(49,361,49,2),
            (50,363,50,4),(43,-1,43,5),(288,364,288,18),(48,373,48,13),
            (51,367,51,6),(52,368,52,7),(54,366,541,14),(55,365,542,15),
            (-1,-1,-1,9),(44,369,44,11),(46,370,518,16),(307,345,538,20),
            (-1,-1,-1,10),(45,371,517,12),(47,372,519,17),(299,337,537,19),(-1,417,-1,-1)]
        self.assertEqual(expected,[tuple(map(int,row)) for row in
            re.findall(r'\{(-?\d+),(-?\d+),(-?\d+),(-?\d+)\}',source)])
        self.assertEqual(c.SUBS,tuple(tuple(r[3] for r in expected if r[a]>=0) for a in range(3)))
        self.assertIn('0x40000000',source);self.assertIn('call32(102,sub',source)
        host=(ROOT/'scripts/ci/source-policy-abi-host.c').read_text()
        self.assertNotIn('arguments(',host);self.assertNotIn('routes[',host)
        self.assertIn('call64(0x40000027',host);self.assertIn('stack[0]!=1',host)
        self.assertEqual([18,18,18],[len(s) for s in c.SUBS])

    def test_entry_guard_rejects_before_source_or_subprocess(self):
        with patch.dict(os.environ,{},clear=True), patch.object(o.Owner,'freeze',side_effect=AssertionError('source read')):
            with self.assertRaisesRegex(o.c.Refusal,'fixed-entry'): o.Owner()

    def test_actual_prerequisite_refuses_missing_apparmor_before_tool_work(self):
        owner=o.Owner.__new__(o.Owner)
        def command(argv,**kwargs):
            self.assertEqual(['/usr/bin/docker','context','inspect','--format','{{.Endpoints.docker.Host}}'],argv)
            return b'unix:///var/run/docker.sock\n'
        def docker(*args,**kwargs):
            if args[0]=='version':return c.encode(dict(
                Server=dict(Version='28.0.4',GitCommit='6430e49',Os='linux',Arch='amd64'),
                Client=dict(Version='28.0.4',GitCommit='b8034c0',Os='linux',Arch='amd64')))
            self.assertEqual('info',args[0])
            return c.encode(dict(DefaultRuntime='runc',Runtimes={'runc':dict(path='runc')},
                                 SecurityOptions=['name=seccomp,profile=builtin']))
        owner.run=command;owner.docker=docker
        with self.assertRaisesRegex(o.c.Refusal,'runtime-profile'):owner.prerequisites()

    def test_profile_diagnostics_preserve_original_guard_and_discriminate_fields(self):
        original=inspect.getsource(c.container_readback).rstrip()
        # Only approved namespace, AppArmor and explicit compression comparisons differ.
        self.assertEqual(1,original.count("h.get('UTSMode')"))
        self.assertEqual(1,original.count("['no-new-privileges', security, 'apparmor=docker-default']"))
        self.assertEqual(1,original.count(",'compress':'false'"))
        restored=original.replace("h.get('UTSMode')","h.get('UtsMode')").replace(
            "['no-new-privileges', security, 'apparmor=docker-default']","['no-new-privileges', security]").replace(
            ",'compress':'false'",'')
        self.assertEqual('d2bf7e5ac211e4a315dd28c3ccc88e367165e46ef8c2d462cdc99100a5235280',c.sha(restored.encode()))
        positive=container()
        self.assertTrue(c.container_diagnostics(positive,positive['Image'],FRAME,'amd64','seccomp=builtin')['matched'])
        mutations=[('root','Image','other'),('root','Path','/bin/sh'),('root','Args',[]),
            ('root','AppArmorProfile',''),('root','Mounts',[{}])]
        mutations += [('Config',key,value) for key,value in dict(Labels={},Entrypoint=[],Cmd=[],User='0',
            WorkingDir='/tmp',Env=['PRIVATE_SECRET=do-not-echo'],Hostname='other',OpenStdin=True,StdinOnce=True,
            Tty=True,Volumes={'/x':{}},ExposedPorts={'1/tcp':{}},Healthcheck={'Test':['x']}).items()]
        mutations += [('HostConfig',key,value) for key,value in dict(ReadonlyRootfs=False,NetworkMode='host',
            Privileged=True,CapDrop=[],CapAdd=['SYS_ADMIN'],SecurityOpt=[],Memory=0,MemorySwap=-1,
            NanoCpus=0,PidsLimit=0,PidMode='host',IpcMode='host',CgroupnsMode='host',Runtime='other',
            UTSMode='host',UsernsMode='host',AutoRemove=True,Init=True,RestartPolicy={},Binds=['x'],Tmpfs={'x':'x'},
            Devices=[{}],DeviceRequests=[{}],PortBindings={'x':[]},ExtraHosts=['x'],VolumesFrom=['x'],Links=['x'],
            Dns=['x'],GroupAdd=['x'],Sysctls={'x':'x'},CgroupParent='x',PublishAllPorts=True,LogConfig={},Ulimits=[]).items()]
        for section,key,value in mutations:
            row=copy.deepcopy(positive);target=row if section=='root' else row[section];target[key]=value
            with self.subTest(section=section,key=key):
                with self.assertRaises(c.Refusal):c.container_readback(row,row['Image'] if key!='Image' else positive['Image'],FRAME,'amd64','seccomp=builtin')
                report=c.container_diagnostics(row,positive['Image'],FRAME,'amd64','seccomp=builtin')
                self.assertFalse(report['matched']);self.assertIn(section+'.'+key,report['mismatches'])
                self.assertNotIn('PRIVATE_SECRET',c.encode(report).decode());self.assertLess(len(c.encode(report)),8192)
        report=c.container_diagnostics(positive,positive['Image'],FRAME,'unknown','seccomp=builtin')
        self.assertIn('selector.abi',report['mismatches'])

    def test_profile_diagnostics_malformed_and_default_categories_never_echo_values(self):
        for value in (None,[],{'Config':None},{'HostConfig':[]},dict(container(),HostConfig=dict(SecurityOpt=[None,'secret']))):
            report=c.container_diagnostics(value,'sha256:'+'c'*64,FRAME,'amd64','seccomp=builtin')
            self.assertFalse(report['matched']);self.assertNotIn('secret',c.encode(report).decode())
        row=container();row['Config']['Env']=['PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin']
        row['AppArmorProfile']=''
        report=c.container_diagnostics(row,row['Image'],FRAME,'amd64','seccomp=builtin')
        self.assertTrue(report['categories']['default_path_only'])
        self.assertEqual('empty',report['categories']['apparmor'])
        self.assertEqual(['root.AppArmorProfile','Config.Env'],report['mismatches'])
        self.assertFalse(report['authority']);self.assertFalse(report['matched'])

    def test_owner_diagnostics_preserve_first_exception_and_cannot_authorize(self):
        owner=o.Owner.__new__(o.Owner);owner.image='sha256:'+'c'*64;owner.frame=FRAME;owner.phase='amd64-builtin'
        owner.control=owner.observed=0;owner.profile_diagnostic=None
        row=container();row['Config']['Env']=['PRIVATE_SECRET=do-not-echo']
        with self.assertRaisesRegex(o.c.Refusal,'container-command'):
            owner.profile_readback(row,'amd64','seccomp=builtin','created')
        saved=copy.deepcopy(owner.profile_diagnostic)
        self.assertEqual(['Config.Env'],saved['report']['mismatches'])
        row['AppArmorProfile']=''
        with self.assertRaises(o.c.Refusal):owner.profile_readback(row,'amd64','seccomp=builtin','pre-start')
        self.assertEqual(saved,owner.profile_diagnostic)
        error=o.c.Refusal('original-guard-error');owner.profile_diagnostic=None
        with patch.object(o.c,'container_readback',side_effect=error),patch.object(o.c,'container_diagnostics',side_effect=RuntimeError('secret')):
            with self.assertRaises(o.c.Refusal) as raised:owner.profile_readback(row,'amd64','seccomp=builtin','created')
        self.assertIs(error,raised.exception);self.assertEqual('unavailable',owner.profile_diagnostic['status'])
        self.assertNotIn('secret',c.encode(owner.profile_diagnostic).decode())

    def test_diagnostic_budget_and_forged_matched_report_cannot_change_refusal(self):
        owner=o.Owner.__new__(o.Owner);owner.image='sha256:'+'c'*64;owner.frame=FRAME;owner.phase='amd64-builtin'
        owner.control=524288;owner.observed=2097152;owner.profile_diagnostic=None
        row=container();row['Config']['Env']=['PRIVATE_SECRET=do-not-echo']
        with self.assertRaises(o.c.Refusal):owner.profile_readback(row,'amd64','seccomp=builtin','created')
        self.assertEqual(dict(status='unavailable',authority=False),owner.profile_diagnostic)
        self.assertEqual((524288,2097152),(owner.control,owner.observed))
        owner.profile_diagnostic=None;owner.control=owner.observed=0
        with patch.object(o.c,'container_diagnostics',return_value=dict(matched=True,authority=False)):
            with self.assertRaisesRegex(o.c.Refusal,'container-command'):
                owner.profile_readback(row,'amd64','seccomp=builtin','created')
        self.assertTrue(owner.profile_diagnostic['report']['matched'])
        with patch.object(o.c,'container_diagnostics',side_effect=AssertionError('diagnostics before rejection')):
            owner.profile_readback(container(),'amd64','seccomp=builtin','created')

    def test_diagnostic_and_authoritative_guard_bypass_semantic_controls(self):
        row=container();row['Config']['Env']=['PRIVATE_SECRET=do-not-echo']
        self.assertFalse(c.container_diagnostics(row,row['Image'],FRAME,'amd64','seccomp=builtin')['matched'])
        source=textwrap.dedent(inspect.getsource(c.container_diagnostics));needle="config.get('Env') in (None, [])"
        self.assertEqual(1,source.count(needle));namespace=dict(c.__dict__)
        exec(compile(source.replace(needle,'True'),'<owned-diagnostic-control>','exec'),namespace)
        self.assertTrue(namespace['container_diagnostics'](row,row['Image'],FRAME,'amd64','seccomp=builtin')['matched'])
        owner=o.Owner.__new__(o.Owner);owner.image=row['Image'];owner.frame=FRAME;owner.phase='amd64-builtin'
        owner.profile_diagnostic=None;owner.control=owner.observed=0
        with self.assertRaises(o.c.Refusal):owner.profile_readback(row,'amd64','seccomp=builtin','created')
        source=textwrap.dedent(inspect.getsource(o.Owner.profile_readback))
        needle='c.container_readback(row,self.image,self.frame,abi,security)'
        self.assertEqual(1,source.count(needle));namespace=dict(o.__dict__)
        exec(compile(source.replace(needle,'None'),'<owned-guard-control>','exec'),namespace)
        namespace['profile_readback'](owner,row,'amd64','seccomp=builtin','created')
        # The real rejection and mismatch assertions above would both be killed.

    def test_canonical_namespace_and_exact_explicit_security_profile(self):
        self.assertEqual('source-schema-subset-not-native-capture',PROFILE_CASES['kind'])
        self.assertEqual(m.MOBY_COMMIT,PROFILE_CASES['commit'])
        self.assertNotIn('UtsMode',PROFILE_CASES['explicit_default']['HostConfig'])
        positive=container();identity=positive['Image']
        c.container_readback(positive,identity,FRAME,'amd64','seccomp=builtin')
        bads=[]
        for value in ('host',None,False,[],{},'private'):
            row=copy.deepcopy(positive);row['HostConfig']['UTSMode']=value;bads.append(row)
        row=copy.deepcopy(positive);del row['HostConfig']['UTSMode'];row['HostConfig']['UtsMode']='';bads.append(row)
        row=copy.deepcopy(positive);row['HostConfig']['UTSMode']='host';row['HostConfig']['UtsMode']='';bads.append(row)
        for value in ('','unconfined','custom',None):
            row=copy.deepcopy(positive);row['AppArmorProfile']=value;bads.append(row)
        options=positive['HostConfig']['SecurityOpt']
        for value in (options[:-1],options+[options[-1]],options+['apparmor=unconfined'],
                      options[:-1]+['apparmor=custom'],options[:-1]+['apparmor=unconfined'],
                      options+['label=disable']):
            row=copy.deepcopy(positive);row['HostConfig']['SecurityOpt']=value;bads.append(row)
        implicit=copy.deepcopy(positive);implicit['AppArmorProfile']=PROFILE_CASES['implicit_default']['AppArmorProfile']
        implicit['HostConfig'].update(PROFILE_CASES['implicit_default']['HostConfig']);bads.append(implicit)
        for row in bads:
            with self.assertRaises(c.Refusal):c.container_readback(row,identity,FRAME,'amd64','seccomp=builtin')
            self.assertFalse(c.container_diagnostics(row,identity,FRAME,'amd64','seccomp=builtin')['matched'])
        negative=copy.deepcopy(positive);negative['HostConfig']['SecurityOpt']=['apparmor=docker-default','seccomp={}', 'no-new-privileges']
        c.container_readback(negative,identity,FRAME,'amd64','seccomp={}')
        with self.assertRaises(c.Refusal):c.container_readback(negative,identity,FRAME,'amd64','seccomp=builtin')

    def probe_fixture(self, function, bad=False):
        with tempfile.TemporaryDirectory(prefix='adb-abi-isolated-') as folder:
            owner=o.Owner.__new__(o.Owner);owner.stage=Path(folder);owner.frame=FRAME
            owner.source=FRAME['source'];owner.owner=c.sha(c.encode(FRAME));owner.labels=c.labels(FRAME)
            owner.image='sha256:'+'c'*64;owner.freeze=lambda:owner.source
            (owner.stage/'canary').write_bytes(binary())
            started=[]
            def docker(*args,**kwargs):
                if args[0]=='create':
                    self.assertEqual(['no-new-privileges','seccomp=builtin','apparmor=docker-default'],
                                     [args[index+1] for index,arg in enumerate(args) if arg=='--security-opt'])
                    self.assertEqual(['max-size=16k','max-file=1','compress=false'],
                                     [args[index+1] for index,arg in enumerate(args) if arg=='--log-opt'])
                    return b'd'*64+b'\n'
                if args[0]=='cp':Path(args[2]).write_bytes(b'changed' if bad else binary());return b''
                if args[0]=='start':started.append(True);return wire('amd64')
                raise AssertionError('unexpected modeled engine command')
            def readback(cid):
                self.assertEqual('d'*64,cid);row=container()
                row['State']=dict(Status='exited' if started else 'created',Running=False,ExitCode=0,OOMKilled=False,Error='')
                return row
            owner.docker=docker;owner.inspect=readback
            result=function(owner,'amd64',False,b'{}')
            self.assertEqual([True],started)
            return result

    def test_actual_owner_checks_executable_bytes_before_modeled_start(self):
        self.assertEqual(18,self.probe_fixture(o.Owner.probe)['routes'])
        with self.assertRaisesRegex(o.c.Refusal,'executable-readback'):
            self.probe_fixture(o.Owner.probe,bad=True)

    def test_cleanup_foreign_owner_refuses_before_any_engine_mutation(self):
        with tempfile.TemporaryDirectory(prefix='adb-abi-isolated-') as folder:
            stage=Path(folder);owner=o.Owner.__new__(o.Owner)
            owner.stage=stage;owner.frame=FRAME;owner.labels=c.labels(FRAME)
            (stage/'marker.json').write_bytes(c.encode(dict(frame=FRAME,image='sha256:'+'c'*64,image_intent=True)))
            owner.ids=lambda kind:['d'*64] if kind=='containers' else []
            owner.inspect=lambda cid:dict(Config=dict(Labels={}),Image='sha256:'+'c'*64,State=dict(Running=True))
            owner.docker=lambda *a,**k:self.fail('foreign engine mutation')
            with patch.object(os,'getuid',return_value=stage.stat().st_uid,create=True),patch.object(stat_module,'S_IMODE',return_value=0o700):
                with self.assertRaisesRegex(o.c.Refusal,'cleanup-cid-owner'):owner.cleanup()
            self.assertTrue((stage/'marker.json').exists())

    def test_owned_image_reconciliation_includes_untagged_and_intermediate_images(self):
        owner=o.Owner.__new__(o.Owner);owner.owner='a'*64;calls=[]
        def docker(*args,**kwargs):
            calls.append(args);return b'sha256:'+b'b'*64+b'\n'
        owner.docker=docker
        self.assertEqual(['sha256:'+'b'*64],owner.ids('images'))
        self.assertEqual(('image','ls','--all','--no-trunc','--quiet','--filter',
                          'label='+c.LABEL+'='+'a'*64),calls[0])

    def test_source_closure_default_off_and_cleanup_are_separate_from_sql(self):
        workflow=(ROOT/c.SOURCES[0]).read_text()
        self.assertIn('default: false',workflow);self.assertIn('if: always()',workflow)
        self.assertIn('--cleanup-only',workflow);self.assertIn('persist-credentials: false',workflow)
        self.assertEqual(9,len(c.SOURCES));self.assertTrue(all((ROOT/p).is_file() for p in c.SOURCES))
        controller=inspect.getsource(o)
        for forbidden in ('apt-get','docker.sock:/','--privileged','--rm','rmtree(',"'pull'",'mariadb','rootSQL'):
            self.assertNotIn(forbidden,controller)
        self.assertIn('self.image_intent=True; self.marker()',controller)
        self.assertLess(controller.index('preexisting-image'),controller.index('self.image_intent=True'))
        self.assertLess(controller.index('executable-readback'),controller.index("self.docker('start'"))

    def test_source_backed_semantic_guard_removal_controls(self):
        # Execute mutated REAL definitions in private namespaces; never edit sources.
        controls=[(c.elf,"kind not in (2,3)","True",lambda f:f(binary_loader())),
            (c.container_readback,"h.get('NetworkMode') == 'none'","True",lambda f:f(changed_container(),
                'sha256:'+'c'*64,FRAME,'amd64','seccomp=builtin')),
            (c.observation,"rows[3] > 0 and rows[-1] == rows[3]","True",lambda f:f(
                wire('amd64')[:-2]+b'8\n','amd64',False)),
            (c.policy,"sha(raw) == moby.BASELINE_SHA","True",lambda f:f(
                RAW.replace(b'"defaultErrnoRet": 1',b'"defaultErrnoRet": 2'),FRAME,m,i.Meter()))]
        killed=0
        for fn,old,new,call in controls:
            with self.assertRaises((c.Refusal,m.Refusal)): call(fn)
            source=textwrap.dedent(inspect.getsource(fn));self.assertEqual(1,source.count(old))
            ns=dict(fn.__globals__);exec(compile(source.replace(old,new),'<owned-semantic-control>','exec'),ns)
            call(ns[fn.__name__]);killed+=1
        fn=o.Owner.probe;source=textwrap.dedent(inspect.getsource(fn))
        needle="c.sha(target.read_bytes()) == c.sha((self.stage/'canary').read_bytes())"
        self.assertEqual(1,source.count(needle));ns=dict(fn.__globals__)
        exec(compile(source.replace(needle,'True'),'<owned-copy-control>','exec'),ns)
        self.assertEqual(18,self.probe_fixture(ns['probe'],bad=True)['routes']);killed+=1
        self.assertEqual(5,killed)


def binary_loader():
    data=bytearray(binary()+bytes(128));struct.pack_into('<H',data,56,2)
    struct.pack_into('<IIQQQQQQ',data,120,3,4,0,0,0,0,0,1)
    return bytes(data)


def changed_container():
    row=container();row['HostConfig']['NetworkMode']='host';return row


if __name__=='__main__':
    stream=io.StringIO();result=unittest.TextTestRunner(stream=stream).run(unittest.defaultTestLoader.loadTestsFromTestCase(Tests))
    if not result.wasSuccessful(): sys.stderr.write(stream.getvalue())
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)))
    sys.exit(not result.wasSuccessful())
