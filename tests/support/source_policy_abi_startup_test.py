"""Synthetic startup evidence and fake owner IO only. Never a native transcript."""
import copy
import ast
from contextlib import ExitStack
import importlib.util
import inspect
import io
import json
from pathlib import Path
import tempfile
import sys
import textwrap
import unittest
from unittest.mock import patch

ROOT=Path(__file__).resolve().parents[2]


def load(name,path):
    spec=importlib.util.spec_from_file_location(name,ROOT/path)
    module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module);return module


c=load('startup_contract','scripts/manual/source_policy_abi_canary_contract.py')
o=load('startup_owner','scripts/ci/source-policy-owned-abi-canary.py')
FRAME=c.context('a'*40,'123','1','b'*64)
IMAGE='sha256:'+'c'*64
CID='d'*64
ARGV=['/usr/bin/docker','--host','unix:///var/run/docker.sock','start','--attach',CID]
FLAGS=dict(timeout=15,limit=4096,native=True,cleanup=False,input_path=None)
MESSAGE=b'Error response from daemon: OCI runtime create failed: runc create failed: exec format error\n'


def descriptor():
    return c.startup_binding(FRAME,IMAGE,CID,'amd64',False)


def packet(message=MESSAGE,**changes):
    args=dict(frame=FRAME,image=IMAGE,phase='amd64-builtin',binding=descriptor(),argv=ARGV,
              out=b'',err=message,eof=(True,True),spawned=True,returncode=1,kind='cli-exited',flags=FLAGS)
    args.update(changes)
    return c.startup_packet(**args)


def owner():
    value=o.Owner.__new__(o.Owner)
    value.started=0;value.phase='amd64-builtin';value.cleaning=False
    value.observed=value.control=value.native=value.cleanup_bytes=0
    value.frame=FRAME;value.image=IMAGE;value.labels=c.labels(FRAME)
    value.env={'PATH':'/usr/bin:/bin','HOME':'/owned','LANG':'C'}
    value.diagnostics=[];value.profile_diagnostic=None;value.startup_failure=None
    value.command_cleanup_issue=False;value.startup_cleanup_attempted=False;value.startup_cleanup_unavailable=False
    return value


class Pipe:
    def __init__(self,number):self.number=number;self.closed=False
    def fileno(self):return self.number
    def close(self):self.closed=True


class FakeIO:
    """All runner IO seams are fake; no child, OS pipe, signal or clock wait."""
    def __init__(self,out=b'',err=MESSAGE,rc=1,timeout=False,spawn_error=None,reap_error=False):
        self.out=Pipe(10);self.err=Pipe(11);self.rc=rc;self.now=0;self.timeout=timeout
        self.spawn_error=spawn_error;self.reap_error=reap_error;self.calls=[];self.waits=0;self.kills=[]
        self.returncode=None;self.pid=4242;self.stdout=self.out;self.stderr=self.err
        self.chunks={10:([out] if out else [])+[b''],11:([err] if err else [])+[b'']}
    def spawn(self,argv,**kwargs):
        self.calls.append((argv,kwargs))
        if self.spawn_error:raise self.spawn_error
        return self
    def poll(self):return self.returncode
    def wait(self,timeout):
        self.waits+=1
        if self.reap_error and self.waits>=2:raise RuntimeError('private reaper detail')
        if self.returncode is None:self.returncode=self.rc
        return self.returncode
    def read(self,fd,size):return self.chunks[fd].pop(0)
    def kill(self,pid,sig):self.kills.append((pid,sig));self.returncode=-9
    def selector(self):
        fixture=self
        class Selector:
            def __enter__(self):self.keys={};return self
            def __exit__(self,*args):return False
            def register(self,file,event,data):
                key=type('Key',(),{})();key.fileobj=file;key.data=data;self.keys[file]=key
            def unregister(self,file):del self.keys[file]
            def get_map(self):return self.keys
            def select(self,timeout):
                if fixture.timeout:fixture.now+=16;return []
                return [(key,None) for key in list(self.keys.values())]
        return Selector()
    def __enter__(self):
        self.stack=ExitStack()
        self.stack.enter_context(patch.object(o.subprocess,'Popen',side_effect=self.spawn))
        self.stack.enter_context(patch.object(o.selectors,'DefaultSelector',side_effect=self.selector))
        self.stack.enter_context(patch.object(o.os,'read',side_effect=self.read))
        self.stack.enter_context(patch.object(o.os,'killpg',side_effect=self.kill,create=True))
        self.stack.enter_context(patch.object(o.signal,'SIGKILL',9,create=True))
        self.stack.enter_context(patch.object(o.time,'monotonic',side_effect=lambda:self.now))
        return self
    def __exit__(self,*args):return self.stack.__exit__(*args)


def cleanup_row(status='running'):
    return dict(Id=CID,Image=IMAGE,Config=dict(Labels=c.labels(FRAME),Env=['PRIVATE_SECRET=never-echo']),
                State=dict(Status=status,Running=status=='running',OOMKilled=False,ExitCode=0,
                           Error='private error /path TOKEN=secret',Pid=1234))


class Tests(unittest.TestCase):
    def test_known_report_is_not_root_cause_or_authority(self):
        result=c.classify_startup(MESSAGE)
        self.assertEqual('recognized',result['status'])
        self.assertEqual(['docker-daemon','oci-runtime','runc'],result['layers'])
        self.assertEqual(['exec-format'],result['topics'])
        value=packet();c.validate_startup(value,descriptor())
        self.assertFalse(value['authority']);self.assertFalse(value['qualification'])
        self.assertNotIn('exec format error',c.encode(value).decode())
        attached=packet(b'Error attaching: OCI runtime create failed: permission denied\n')
        self.assertEqual(['oci-runtime','attach'],attached['analysis']['layers'])

    def test_missing_unknown_ambiguous_and_hostile_bytes(self):
        self.assertEqual('unavailable',c.classify_startup(None)['status'])
        self.assertEqual('unclassified',c.classify_startup(b'private-secret permission denied\n')['status'])
        self.assertEqual('ambiguous',c.classify_startup(MESSAGE+MESSAGE)['status'])
        for bad in (b'Error response from daemon: \x1bsecret',b'\x00',b'\xff',b'a'*4097,
                    b'a'*1025,b'a\n'*17,b'Error response from daemon: "unclosed'):
            self.assertEqual('invalid',c.classify_startup(bad)['status'])
        for bad in (b'Error response from daemon: "/private/seccomp permission denied"\n',
                    b'Error response from daemon: https://private/seccomp TOKEN=apparmor\n',
                    b'Error response from daemon: PRIVATE_SECCOMP_MARKER _apparmor 9cgroup\n'):
            value=c.classify_startup(bad)
            self.assertEqual('unclassified',value['status']);self.assertEqual(['unknown'],value['topics'])
            self.assertNotIn('private',c.encode(value).decode())

    def test_operation_binding_precedes_message_projection(self):
        for key,value in (('image','sha256:'+'e'*64),('phase','i386-builtin'),
                          ('argv',ARGV[:-1]+['e'*64]),('argv',['/bin/sh','-c','secret'])):
            with patch.object(c,'classify_startup',side_effect=AssertionError('premature classification')):
                with self.assertRaises(c.Refusal):packet(**{key:value})
        for key,value in (('native',False),('limit',8192),('timeout',16),('cleanup',True),('input_path','secret')):
            with self.assertRaises(c.Refusal):packet(flags=dict(FLAGS,**{key:value}))

    def test_partial_overflow_and_historical_hash_cannot_be_classified(self):
        for changes in (dict(eof=(True,False),kind='command-error'),dict(kind='timeout',returncode=None),
                        dict(kind='overflow',err=b'x'*8192)):
            value=packet(**changes)
            self.assertIsNone(value['streams']['stderr_sha'])
            self.assertEqual('unavailable',value['analysis']['status'])
        with self.assertRaises(c.Refusal):packet(returncode=0,err=b'')
        with self.assertRaises(c.Refusal):packet(eof=(True,False))
        self.assertEqual('unavailable',c.classify_startup(None)['status'])
        self.assertNotIn('80a868096821299612792f8e0917bc2abf47d10457d368b829edc8e23bdf720e',inspect.getsource(c))

    def test_packet_shape_types_and_false_flags(self):
        good=packet()
        for mutate in (lambda p:p.update(authority=True),lambda p:p.update(extra='private-secret'),
                       lambda p:p['outcome'].update(returncode=True),lambda p:p['streams'].update(stderr_bytes=True),
                       lambda p:p['analysis'].update(layers=['runc','docker-daemon']),
                       lambda p:p['binding'].update(cid='e'*64)):
            bad=copy.deepcopy(good);mutate(bad)
            with self.assertRaises(c.Refusal):c.validate_startup(bad,descriptor())
        self.assertLessEqual(len(c.encode(good)),4096)

    def test_projection_work_and_exact_boundaries(self):
        for raw in (MESSAGE,b'Error response from daemon: '+b'a '*490,b'\n'.join([b'a'*256]*15)):
            result,work=c._startup_scan(raw)
            self.assertLessEqual(work,16*len(raw)+1024)
            self.assertNotIn('message',result)

    def test_actual_runner_rejects_status_or_stderr_and_captures_once(self):
        value=owner()
        with FakeIO() as fixture:
            with self.assertRaisesRegex(o.c.Refusal,'bounded-command-failed'):
                value.run(ARGV,**FLAGS,startup=descriptor())
            self.assertEqual(1,len(fixture.calls));self.assertEqual(2,fixture.waits)
            self.assertTrue(fixture.out.closed and fixture.err.closed)
            self.assertEqual('',fixture.calls[0][1].get('input',''))
            self.assertNotIn('GITHUB_TOKEN',fixture.calls[0][1]['env'])
        self.assertEqual('recognized',value.startup_failure['analysis']['status'])
        self.assertEqual(c.sha(MESSAGE),value.diagnostics[0]['stderr_sha'])
        saved=copy.deepcopy(value.startup_failure)
        with FakeIO(err=b'Error response from daemon: permission denied\n',rc=0):
            with self.assertRaises(o.c.Refusal):value.run(ARGV,**FLAGS,startup=descriptor())
        self.assertEqual(saved,value.startup_failure)
        with FakeIO(err=b'',out=b'7\n',rc=0) as fixture:
            successful=owner();self.assertEqual(b'7\n',successful.run(ARGV,**FLAGS,startup=descriptor()))
            self.assertIsNone(successful.startup_failure);self.assertEqual(2,fixture.waits)

    def test_actual_runner_timeout_overflow_and_spawn_failure_do_not_gain_authority(self):
        for config,reason in ((dict(timeout=True),'command-deadline'),(dict(err=b'x'*8192),'command-output-budget')):
            with FakeIO(**config) as fixture:
                value=owner()
                with self.assertRaisesRegex(o.c.Refusal,reason):value.run(ARGV,**FLAGS,startup=descriptor())
                self.assertEqual('unavailable',value.startup_failure['analysis']['status'])
                self.assertIsNone(value.startup_failure['streams']['stderr_sha'])
                self.assertEqual(1,len(fixture.kills));self.assertTrue(fixture.out.closed and fixture.err.closed)
        error=OSError('private spawn error')
        with FakeIO(spawn_error=error):
            value=owner()
            with self.assertRaises(OSError) as caught:value.run(ARGV,**FLAGS,startup=descriptor())
            self.assertIs(error,caught.exception);self.assertFalse(value.startup_failure['outcome']['cli_spawned'])
            self.assertNotIn('private',c.encode(value.startup_failure).decode())

    def test_actual_runner_binding_budget_and_diagnostic_faults_cannot_mask_rejection(self):
        with FakeIO(),patch.object(o.c,'classify_startup',wraps=o.c.classify_startup) as classifier:
            value=owner()
            with self.assertRaises(o.c.Refusal):value.run(ARGV[:-1]+['e'*64],**FLAGS,startup=descriptor())
            classifier.assert_not_called();self.assertEqual(dict(status='unavailable',authority=False),value.startup_failure)
        with FakeIO():
            value=owner();value.control=524288
            with self.assertRaises(o.c.Refusal):value.run(ARGV,**FLAGS,startup=descriptor())
            self.assertEqual(524288,value.control)
            self.assertEqual(dict(status='unavailable',authority=False),value.startup_failure)
        error=OSError('private primary detail')
        with FakeIO(spawn_error=error),patch.object(o.Owner,'capture_startup',side_effect=RuntimeError('private diagnostics')):
            value=owner()
            with self.assertRaises(OSError) as caught:value.run(ARGV,**FLAGS,startup=descriptor())
            self.assertIs(error,caught.exception);self.assertNotIn('private',c.encode(value.startup_failure).decode())
        for startup in (None,descriptor()):
            value=owner();value.startup_failure=packet();saved=copy.deepcopy(value.startup_failure)
            with FakeIO(),patch.object(o.Owner,'capture_startup',side_effect=RuntimeError('later hook fault')):
                with self.assertRaises(o.c.Refusal):value.run(ARGV,**FLAGS,startup=startup)
            self.assertEqual(saved,value.startup_failure)

    def test_reap_failure_is_secondary_to_primary_and_never_success(self):
        with FakeIO(reap_error=True):
            value=owner()
            with self.assertRaisesRegex(o.c.Refusal,'bounded-command-failed'):
                value.run(ARGV,**FLAGS,startup=descriptor())
            self.assertTrue(value.command_cleanup_issue)
            self.assertEqual(1,value.startup_failure['outcome']['returncode'])
        with FakeIO(err=b'',rc=0,reap_error=True):
            value=owner()
            with self.assertRaises(RuntimeError):value.run(ARGV,**FLAGS,startup=descriptor())
            self.assertTrue(value.command_cleanup_issue);self.assertFalse(value.startup_failure['authority'])

    def test_owned_cleanup_projection_is_later_non_authoritative_and_strict(self):
        original=packet()
        for status in ('created','running','exited','dead'):
            state=c.startup_cleanup(original,FRAME,IMAGE,CID,cleanup_row(status))
            self.assertEqual(status,state['state'])
            self.assertEqual(0 if status in ('exited','dead') else None,state['exit_code'])
            self.assertNotIn('private',c.encode(state).decode());self.assertIsNone(original['cleanup'])
        for key,value in (('Id','e'*64),('Image','sha256:'+'e'*64),('Config',dict(Labels={}))):
            row=cleanup_row();row[key]=value
            with self.assertRaises(c.Refusal):c.startup_cleanup(original,FRAME,IMAGE,CID,row)
        row=cleanup_row();row['State']['Running']=0
        with self.assertRaises(c.Refusal):c.startup_cleanup(original,FRAME,IMAGE,CID,row)
        row=cleanup_row();row['State']['Running']=False
        with self.assertRaises(c.Refusal):c.startup_cleanup(original,FRAME,IMAGE,CID,row)

    def cleanup_fixture(self,foreign=False,projection_error=False):
        with tempfile.TemporaryDirectory(prefix='adb-startup-isolated-') as folder:
            value=owner();value.stage=Path(folder);value.startup_failure=packet()
            (value.stage/'marker.json').write_bytes(c.encode(dict(frame=FRAME,image=IMAGE,image_intent=True)))
            active=[CID];row=cleanup_row();calls=[];reads=[]
            if foreign:row['Config']['Labels']={}
            def inspect(cid):reads.append(cid);return copy.deepcopy(row)
            def docker(*args,**kwargs):
                calls.append(args)
                if args[0]=='stop':row['State'].update(Status='exited',Running=False);return b''
                self.assertEqual(('rm',CID),args);active.clear();return b''
            value.inspect=inspect;value.docker=docker;value.ids=lambda kind:list(active) if kind=='containers' else []
            with ExitStack() as stack:
                stack.enter_context(patch.object(o.os,'getuid',return_value=value.stage.stat().st_uid,create=True))
                stack.enter_context(patch.object(o.stat,'S_IMODE',return_value=0o700))
                if projection_error:stack.enter_context(patch.object(o.c,'startup_cleanup',side_effect=RuntimeError('private')))
                if foreign:
                    with patch.object(value,'capture_startup_cleanup',side_effect=AssertionError('foreign projection')) as projection:
                        with self.assertRaisesRegex(o.c.Refusal,'cleanup-cid-owner'):value.cleanup()
                        projection.assert_not_called();self.assertEqual([],calls)
                else:
                    self.assertEqual(dict(containers=0,images=0,stage_absent=True),value.cleanup())
                    self.assertEqual([CID,CID],reads)
                    self.assertEqual([('stop','--time','2',CID),('rm',CID)],calls)
                    state=value.startup_failure['cleanup']
                    self.assertEqual('refused' if projection_error else 'observed',state['status'])
                    if not projection_error:self.assertEqual('running',state['state']);self.assertIsNone(state['exit_code'])
            return value

    def test_actual_cleanup_reuses_existing_read_and_preserves_safe_removal(self):
        value=self.cleanup_fixture()
        saved=copy.deepcopy(value.startup_failure)
        value.capture_startup_cleanup(CID,cleanup_row('exited'))
        self.assertEqual(saved,value.startup_failure)
        self.cleanup_fixture(foreign=True);self.cleanup_fixture(projection_error=True)

    def test_projection_budget_and_unknown_objects_never_escape(self):
        value=owner();value.startup_failure=packet();value.cleanup_bytes=524288
        value.capture_startup_cleanup(CID,cleanup_row())
        self.assertTrue(value.startup_cleanup_unavailable);self.assertIsNone(value.startup_failure['cleanup'])
        self.assertEqual(524288,value.cleanup_bytes)
        class Unknown:
            def __eq__(self,other):raise AssertionError('magic comparison')
            def __str__(self):raise AssertionError('magic string')
        self.assertEqual('invalid',c.classify_startup(Unknown())['status'])
        bad=packet();bad['binding']['cid']=Unknown()
        with self.assertRaises(c.Refusal):c.validate_startup(bad,descriptor())

    def test_actual_outer_failure_emits_first_packet_after_cleanup_only(self):
        value=owner();value.prerequisites=lambda:value.run(ARGV,**FLAGS,startup=descriptor())
        def cleanup():
            value.capture_startup_cleanup(CID,cleanup_row('created'))
            return dict(containers=0,images=0,stage_absent=True)
        value.cleanup=cleanup;output=io.StringIO()
        with FakeIO(),patch.object(o,'Owner',return_value=value),patch.object(o.sys,'argv',['owned','--approved-owned-abi-proof']),patch.object(o.sys,'stdout',output):
            self.assertEqual(1,o.main())
        result=json.loads(output.getvalue())
        self.assertFalse(result['qualification']);self.assertEqual('unestablished',result['abi_coverage'])
        self.assertEqual('bounded-command-failed',result['failure']['reason'])
        diagnostic=result['failure']['startup_failure']
        self.assertEqual('created',diagnostic['cleanup']['state']);self.assertIsNone(diagnostic['cleanup']['exit_code'])
        self.assertFalse(diagnostic['authority']);self.assertNotIn(MESSAGE.decode().strip(),output.getvalue())
        self.assertEqual(dict(containers=0,images=0,stage_absent=True),result['cleanup'])

    def test_six_source_backed_semantic_controls(self):
        def mutant(function,old,new):
            source=textwrap.dedent(inspect.getsource(function));self.assertEqual(1,source.count(old))
            namespace=dict(function.__globals__)
            exec(compile(source.replace(old,new),'<startup-semantic-control>','exec'),namespace)
            return namespace[function.__name__]
        needle="argv==['/usr/bin/docker','--host','unix:///var/run/docker.sock',\n                                         'start','--attach',binding['cid']]"
        changed=mutant(c.startup_packet,needle,'True')
        args=dict(frame=FRAME,image=IMAGE,phase='amd64-builtin',binding=descriptor(),argv=ARGV[:-1]+['e'*64],
                  out=b'',err=MESSAGE,eof=(True,True),spawned=True,returncode=1,kind='cli-exited',flags=FLAGS)
        with self.assertRaises(c.Refusal):c.startup_packet(**args)
        self.assertEqual('ADBOWNEDABISTARTFAIL1',changed(**args)['format'])
        tree=ast.parse(textwrap.dedent(inspect.getsource(c._startup_scan)))
        assignment=next(n for n in ast.walk(tree) if isinstance(n,ast.Assign)
                        and any(isinstance(t,ast.Name) and t.id=='hidden' for t in n.targets))
        assignment.value=ast.Constant(False);ast.fix_missing_locations(tree);namespace=dict(c.__dict__)
        exec(compile(tree,'<startup-mask-control>','exec'),namespace)
        secret=b'Error response from daemon: https://private/seccomp TOKEN=apparmor\n'
        self.assertEqual('unclassified',c._startup_scan(secret)[0]['status'])
        self.assertEqual('recognized',namespace['_startup_scan'](secret)[0]['status'])
        changed=mutant(o.Owner.run,'if p.returncode != 0 or err:','if False:')
        with FakeIO(rc=0):
            with self.assertRaises(o.c.Refusal):owner().run(ARGV,**FLAGS,startup=descriptor())
        with FakeIO(rc=0):self.assertEqual(b'',changed(owner(),ARGV,**FLAGS,startup=descriptor()))
        process=type('ObservedChild',(),{'returncode':1})()
        new_message=b'Error response from daemon: permission denied\n'
        def capture(function,value):
            function(value,descriptor(),ARGV,FLAGS,bytearray(),bytearray(new_message),(True,True),process,
                     o.c.Refusal('bounded-command-failed'))
        value=owner();value.startup_failure=packet();original=copy.deepcopy(value.startup_failure)
        capture(o.Owner.capture_startup,value);self.assertEqual(original,value.startup_failure)
        changed=mutant(o.Owner.capture_startup," or getattr(self,'startup_failure',None) is not None",'')
        capture(changed,value);self.assertNotEqual(original,value.startup_failure)
        changed=mutant(o.Owner.capture_startup,'self.control+size<=524288','True')
        value=owner();value.control=524288;capture(o.Owner.capture_startup,value)
        self.assertEqual('unavailable',value.startup_failure['status'])
        value=owner();value.control=524288;capture(changed,value)
        self.assertGreater(value.control,524288);self.assertEqual('ADBOWNEDABISTARTFAIL1',value.startup_failure['format'])
        changed=mutant(c.startup_cleanup,"row['Config'].get('Labels')==labels(frame)",'True')
        foreign=cleanup_row();foreign['Config']['Labels']={}
        with self.assertRaises(c.Refusal):c.startup_cleanup(packet(),FRAME,IMAGE,CID,foreign)
        self.assertEqual('observed',changed(packet(),FRAME,IMAGE,CID,foreign)['status'])


if __name__=='__main__':
    stream=io.StringIO();result=unittest.TextTestRunner(stream=stream).run(unittest.defaultTestLoader.loadTestsFromTestCase(Tests))
    if not result.wasSuccessful():sys.stderr.write(stream.getvalue())
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)))
    sys.exit(not result.wasSuccessful())
