"""Real upstream bytes, pure preservation DATA only; no engine/kernel evidence."""
import ast
import base64
import copy
import hashlib
import importlib.util
import inspect
import io
import json
from pathlib import Path
import sys
import textwrap
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]


def load(name):
    spec = importlib.util.spec_from_file_location(name, ROOT / 'scripts/manual' / (name+'.py'))
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


m = load('source_policy_moby_policy')
i = load('source_policy_ubi_import_contract')
r = load('source_policy_ubi_runtime_contract')
RAW = base64.b64decode(b''.join((ROOT / 'tests/fixtures/moby-28.0.4/default.json.b64').read_bytes().splitlines()), validate=True)


def frame():
    return dict(format='ADBUMOBYCONTEXT1', manifest=r.MANIFEST, config=r.CONFIG, profile=m.MODEL,
                engine_version='28.0.4', engine_commit=m.MOBY_COMMIT, client_version='28.0.4', client_commit=m.CLI_COMMIT,
                head='a'*40, run='123', attempt=1, static_source='b'*64, runtime_source='c'*64, snapshot='d'*64, graph='e'*64)


def derive():
    return m.derive(RAW, frame(), m.SOURCE_PINS, i.Meter())


def verify(value):
    return m.verify(RAW, m.encode(value), frame(), m.SOURCE_PINS, i.Meter())


class Contracts(unittest.TestCase):
    def test_real_positive_delta_and_independent_verifier_preserve_input(self):
        self.assertEqual(12828, len(RAW)); self.assertEqual(m.BASELINE_SHA, hashlib.sha256(RAW).hexdigest())
        result = derive(); original = json.loads(RAW); changed = json.loads(result['policy'])
        self.assertEqual(31, len(original['syscalls'])); self.assertEqual(30, len(changed['syscalls']))
        self.assertEqual(406, sum(len(row['names']) for row in changed['syscalls']))
        self.assertEqual(original['archMap'], changed['archMap']); self.assertEqual(1, changed['defaultErrnoRet'])
        self.assertEqual(5, sum(row['names'] == ['personality'] for row in changed['syscalls']))
        self.assertTrue(all(name not in m.DENIED for row in changed['syscalls'] if row['action']=='SCMP_ACT_ALLOW' for name in row['names']))
        checked = m.verify(RAW, result['policy'], frame(), m.SOURCE_PINS, i.Meter())
        self.assertEqual(result['evidence'], checked); self.assertTrue(checked['preservation_verified_data'])
        self.assertFalse(checked['runtime_grant']); self.assertFalse(checked['launch_authority']); self.assertFalse(checked['kernel_verified'])
        self.assertIsNone(checked['transport_readback_sha']); self.assertEqual('unestablished', checked['abi_coverage'])
        self.assertEqual(RAW, base64.b64decode(b''.join((ROOT/'tests/fixtures/moby-28.0.4/default.json.b64').read_bytes().splitlines())))
        self.assertEqual(r.RUNTIME_SOURCES, m.LOCAL_SOURCES[:-2])
        self.assertEqual(64, len(m.runtime_source(tuple((path,'f'*64) for path in m.LOCAL_SOURCES))))

    def test_guard_order_raw_context_and_source_refuse_before_parsing(self):
        for raw, context, sources in [(RAW+b'\n',frame(),m.SOURCE_PINS),(RAW,dict(frame(),manifest='f'*64),m.SOURCE_PINS),
            (RAW,dict(frame(),client_commit='f'*40),m.SOURCE_PINS),(RAW,dict(frame(),attempt=True),m.SOURCE_PINS),
            (RAW,dict(frame(),runtime_source='b'*64),m.SOURCE_PINS),(RAW,dict(frame(),grant=True),m.SOURCE_PINS),
            (RAW,frame(),m.SOURCE_PINS[::-1]),(RAW,frame(),m.SOURCE_PINS[:-1])]:
            with self.subTest(context=context), patch.object(m,'_parse',side_effect=AssertionError('protected parser')):
                with self.assertRaises(m.Refusal): m.derive(raw,context,sources,i.Meter())
        with self.assertRaises(m.Refusal): m.runtime_source(tuple((path,'f'*64) for path in reversed(m.LOCAL_SOURCES)))

    def test_every_protected_top_level_rule_field_type_and_occurrence_is_checked(self):
        valid = json.loads(derive()['policy'])
        for key,value in [('defaultErrnoRet',True),('defaultErrnoRet',0),('defaultAction','SCMP_ACT_ALLOW'),('flags',[]),('archMap',[])]:
            with self.subTest(key=key), self.assertRaises(m.Refusal): verify(dict(valid,**{key:value}))
        for index,row in enumerate(valid['syscalls']):
            for key in row:
                altered=copy.deepcopy(valid)
                if key=='names': altered['syscalls'][index][key] = row[key][::-1] if len(row[key])>1 else ['foreign_call']
                elif key=='action': altered['syscalls'][index][key] = 'SCMP_ACT_LOG'
                elif key=='errnoRet': altered['syscalls'][index][key] = 1
                elif key=='comment': altered['syscalls'][index][key] = 'changed'
                else: altered['syscalls'][index][key] = {}
                with self.subTest(index=index,key=key), self.assertRaises(m.Refusal): verify(altered)
        for edit in ('remove','extra','reorder'):
            altered=copy.deepcopy(valid)
            if edit=='remove': altered['syscalls'].pop()
            elif edit=='extra': altered['syscalls'].append(copy.deepcopy(altered['syscalls'][-1]))
            else: altered['syscalls'][1:3]=reversed(altered['syscalls'][1:3])
            with self.subTest(edit=edit), self.assertRaises(m.Refusal): verify(altered)

    def test_all_denied_routes_arches_and_alias_requirements_remain_explicit(self):
        result=derive(); valid=json.loads(result['policy']); required=['SCMP_ARCH_X86_64','SCMP_ARCH_X86','SCMP_ARCH_X32']
        self.assertEqual(('socket','socketpair','socketcall','connect','bind','listen','accept','accept4','shutdown',
            'getsockname','getpeername','setsockopt','getsockopt','send','sendto','sendmsg','sendmmsg',
            'recv','recvfrom','recvmsg','recvmmsg','recvmmsg_time64'),m.DENIED)
        self.assertEqual(required,result['evidence']['required_abis']); self.assertEqual(0x40000000,result['evidence']['x32_syscall_bit'])
        self.assertIn('socketcall',m.DENIED); self.assertIn('recvmmsg_time64',m.DENIED)
        for name in m.DENIED:
            altered=copy.deepcopy(valid); altered['syscalls'][0]['names'].append(name)
            with self.subTest(name=name), self.assertRaises(m.Refusal): verify(altered)
        for index in range(3):
            altered=copy.deepcopy(valid)
            if index==0: altered['archMap'][0]['architecture']='SCMP_ARCH_X86'
            else: altered['archMap'][0]['subArchitectures'].pop(index-1)
            with self.assertRaises(m.Refusal): verify(altered)
        altered=copy.deepcopy(valid); altered['syscalls'][0]['names'].append('io_uring_setup')
        with self.assertRaises(m.Refusal): verify(altered)

    def test_real_selector_and_errno_occurrences_are_not_flattened(self):
        original=json.loads(RAW); valid=json.loads(derive()['policy'])
        for row in original['syscalls'][1:]:
            if row['names'] != ['socket']: self.assertIn(row,valid['syscalls'])
        errno=next(row for row in valid['syscalls'] if row['action']=='SCMP_ACT_ERRNO')
        self.assertEqual(dict(names=['clone3'],action='SCMP_ACT_ERRNO',errnoRet=38,excludes=dict(caps=['CAP_SYS_ADMIN'])),errno)
        # Boundary vectors below use a test-only predicate, never serialize away
        # selectors or claim execution of the Go resolver / kernel.
        def selected(row,arch,caps,kernel):
            ex=row.get('excludes',{}); inc=row.get('includes',{})
            return not (arch in ex.get('arches',[]) or any(cap in caps for cap in ex.get('caps',[])) or
                        ('minKernel' in ex and kernel>=tuple(map(int,ex['minKernel'].split('.'))))) and (
                (not inc.get('arches') or arch in inc['arches']) and all(cap in caps for cap in inc.get('caps',[])) and
                ('minKernel' not in inc or kernel>=tuple(map(int,inc['minKernel'].split('.')))))
        for arch in ('amd64','s390x'):
            for caps in ((),('CAP_SYS_ADMIN',),('CAP_SYS_PTRACE',)):
                for kernel in ((4,7),(4,8),(7,0)):
                    for ordinal,row in enumerate(original['syscalls'][1:],1):
                        if row['names']!=['socket']:
                            actual=valid['syscalls'][ordinal-(1 if ordinal>2 else 0)]
                            self.assertEqual(selected(row,arch,caps,kernel),selected(actual,arch,caps,kernel))

    def test_parser_shapes_and_shared_budgets_are_bounded_and_latched(self):
        for data in (b'{"x":1,"x":2}',b'{"x":NaN}',b'{"x":1.0}',b'\xff',b'x'*65537):
            with self.assertRaises(m.Refusal): m._parse(data,65536,i.Meter())
        with self.assertRaises(m.Refusal): m._parse(('['*20+'0'+']'*20).encode(),65536,i.Meter())
        for kind,limit in i.Meter.LIMITS.items():
            meter=i.Meter(); meter.charge(kind,limit)
            with self.assertRaises((m.Refusal,i.Refusal)): m.derive(RAW,frame(),m.SOURCE_PINS,meter)
            # observation is only used when a verified summary is retained.
            if kind=='observation': self.assertIsNotNone(meter.failure)

    def test_independent_verifier_does_not_call_the_transformer(self):
        result=derive()
        with patch.object(m,'derive',side_effect=AssertionError('correlated transformer')):
            self.assertTrue(m.verify(RAW,result['policy'],frame(),m.SOURCE_PINS,i.Meter())['preservation_verified_data'])

    def test_source_backed_pin_source_delta_and_protected_field_controls_are_killed(self):
        def pin():
            with self.assertRaisesRegex(m.Refusal,'baseline-pin'): m.derive(RAW.replace(b'writev',b'zzzzzz'),frame(),m.SOURCE_PINS,i.Meter())
        self.control(m._admit,'hashlib.sha256(raw).hexdigest() == BASELINE_SHA','True',pin)
        def sources():
            rows=list(m.SOURCE_PINS); rows[-1]=(rows[-1][0],'f'*64)
            with self.assertRaisesRegex(m.Refusal,'source-pins'): m.derive(RAW,frame(),tuple(rows),i.Meter())
        self.control(m._admit,'sources == SOURCE_PINS','True',sources)
        def top():
            valid=json.loads(derive()['policy']); valid['defaultErrnoRet']=True
            with self.assertRaisesRegex(m.Refusal,'protected-top'): verify(valid)
        self.control(m.verify,"_same({key:value for key,value in changed.items() if key!='syscalls'}, protected, meter)",'True',top)
        def fields():
            valid=json.loads(derive()['policy']); valid['syscalls'][1]['includes']['minKernel']='4.9'
            with self.assertRaisesRegex(m.Refusal,'protected-rule'): verify(valid)
        self.control(m.verify,"_same({key:value for key,value in actual.items() if key!='names'}, fields, meter)",'True',fields)
        def occurrences():
            valid=json.loads(derive()['policy']); valid['syscalls'][0]['names'][0]='foreign_call'
            with self.assertRaisesRegex(m.Refusal,'retained-name-order'): verify(valid)
        self.control(m.verify,"_same(actual.get('names'),names,meter)",'True',occurrences)

    def test_source_backed_network_abi_and_no_grant_controls_are_killed(self):
        def positive():
            try: result=derive()
            except (m.Refusal,i.Refusal) as error: self.fail('positive DATA unexpectedly refused: '+str(error))
            self.assertEqual(['SCMP_ARCH_X86_64','SCMP_ARCH_X86','SCMP_ARCH_X32'],result['evidence']['required_abis'])
            self.assertFalse(result['evidence']['runtime_grant'])
        self.control(m.derive,'name not in DENIED',"name not in DENIED or name == 'socketcall'",positive)
        self.control(m._evidence,"['SCMP_ARCH_X86_64','SCMP_ARCH_X86','SCMP_ARCH_X32']","['SCMP_ARCH_X86_64']",positive)
        self.control(m._evidence,'runtime_grant=False','runtime_grant=True',positive)

    def test_no_operational_imports_and_legacy_guard_stays_closed(self):
        tree=ast.parse(inspect.getsource(m))
        self.assertLessEqual({node.names[0].name for node in ast.walk(tree) if isinstance(node,ast.Import)},{'hashlib','json','re'})
        self.assertFalse(any(isinstance(node,ast.ImportFrom) for node in ast.walk(tree)))
        self.assertFalse(any(isinstance(node,ast.Call) and isinstance(node.func,ast.Name) and node.func.id in ('open','eval','exec','__import__') for node in ast.walk(tree)))
        with self.assertRaises(r.Refusal): r.restrict_policy(json.loads(RAW))
        self.assertEqual(19,len(r.NETWORK_CALLS))
        self.assertNotIn('source_policy_moby_policy',(ROOT/'.github/workflows/source-policy-legacy-ubi-static.yml').read_text())

    def control(self,function,old,new,witness):
        witness(); source=textwrap.dedent(inspect.getsource(function)); self.assertEqual(1,source.count(old))
        scope=dict(m.__dict__); exec(compile(source.replace(old,new),'<policy-control>','exec'),scope)
        with patch.object(m,function.__name__,scope[function.__name__]):
            with self.assertRaises(AssertionError): witness()


if __name__=='__main__':
    out=io.StringIO(); result=unittest.TextTestRunner(stream=out).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8','backslashreplace')[:14000])
    sys.exit(0 if result.wasSuccessful() else 1)
