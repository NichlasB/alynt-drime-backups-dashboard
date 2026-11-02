"""Actual offline diagnostics; model/context DATA are not acquired-candidate proof."""
import copy
from contextlib import contextmanager, redirect_stdout
import hashlib
import importlib.util
import inspect
import io
import json
import os
from pathlib import Path
import socket
import subprocess
import sys
import time
import unittest
from unittest.mock import patch

ROOT=Path(__file__).resolve().parents[2]
spec=importlib.util.spec_from_file_location('owner_fixtures',Path(__file__).with_name('source_policy_ubi_fixed_owner_test.py'))
f=importlib.util.module_from_spec(spec); spec.loader.exec_module(f)
a,v,e,x=f.a,f.v,f.e,f.x
spec=importlib.util.spec_from_file_location('diagnostic_entry',ROOT/'scripts/ci/source-policy-legacy-ubi-static.py')
u=importlib.util.module_from_spec(spec); spec.loader.exec_module(u)
NAME='libgcc_s.so.1'


def row(path,kind='directory',target=None):
    return dict(path=path,kind=kind,target=target,mode=0o755,uid=0,gid=0,size=0,sha256=hashlib.sha256(b'').hexdigest() if kind=='file' else None)


def records(target):
    result=[row('usr'),row('usr/lib64'),row('lib64','symlink','usr/lib64'),row('usr/lib64/'+NAME,'symlink',target)]
    if target!=NAME and '/' not in target and len(('usr/lib64/'+target).encode())<=240:
        result.append(row('usr/lib64/'+target,'file'))
    return result


def model(target):
    tree=v.VirtualClosure(dict(manifest=a.PIN,config=a.CONFIG,source='a'*64),[('b'*64,'c'*64)])
    tree.apply(dict(ordinal=0,compressed='b'*64,diff_id='c'*64,compressed_verified=True,diff_verified=True),records(target),0)
    tree.finalize(1); return tree


def caught(target='libgcc_s.so.debug'):
    tree=model(target)
    try: e.provider(tree,NAME,time.monotonic()+5)
    except e.Refusal as error: return tree,error
    raise AssertionError('Expected actual provider refusal')


@contextmanager
def frame():
    # DATA-only verified flags, not archive hashes/authentication. Real entry/pins are never bypassed.
    frozen={key:'d'*64 for key in u.BOUNDED_SOURCES}; config=dict(rootfs=dict(diff_ids=['sha256:'+'a'*64]*10))
    env=dict(GITHUB_SHA='b'*40,EXPECTED_SHA='b'*40,GITHUB_RUN_ID='123',GITHUB_RUN_ATTEMPT='1')
    with patch.dict(os.environ,env):
        owner=u.create_work_authority(a,config,frozen,time.monotonic()+30); tree=None
        aliases=[dict(path='lib64',target='usr/lib64',layer=0,entry=2)]
        for i,(compressed,diff) in enumerate(owner._layers):
            metadata=dict(compressed=compressed,diff_id=diff,compressed_verified=True,diff_verified=True)
            owner.begin_layer(i,compressed,diff,owner.deadline); owner.verify_layer(metadata,aliases if i==0 else None)
            if i==0: tree=v.VirtualClosure(owner._identity,owner._layers,aliases,hardlinks=True,work_owner=owner)
            tree.apply(dict(metadata,ordinal=i),records('libgcc_s.so.debug') if i==0 else [],owner.now())
        tree.finalize(owner.now())
        try: e.provider(tree,NAME,owner.deadline)
        except e.Refusal as error:
            owner.fail(error); facts=error.provider_alias_facts
        report=dict(status='refused',failure='elf-provider-alias',metadata_verified=True,layers_verified=10,namespace_layers_committed=10,
                    elf_facts=dict(needed_name=NAME),access_profile='bounded-strings-v1')
        yield report,facts,tree,owner,frozen,config


class Contracts(unittest.TestCase):
    def test_positive_real_provider_and_no_read_for_invalid_request(self):
        tree=model('libgcc_s.so.1.2')
        path,node,edges=e.provider(tree,NAME,time.monotonic()+5)
        self.assertEqual('usr/lib64/libgcc_s.so.1.2',path); self.assertEqual('file',node['kind']); self.assertEqual(1,len(edges))
        with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
            with self.assertRaisesRegex(e.Refusal,'elf-library-name'): e.provider(tree,'../bad',time.monotonic()+5)

    def witness(self,target,provider=None):
        tree=model(target); original=e.require; primary=[]
        def watched(ok,reason):
            try: original(ok,reason)
            except e.Refusal as error:
                if reason=='elf-provider-alias': primary.append(error)
                raise
        with patch.object(tree,'inspect',wraps=tree.inspect) as reads,patch.object(e,'require',side_effect=watched):
            with self.assertRaisesRegex(e.Refusal,'^elf-provider-alias$') as failure: (provider or e.provider)(tree,NAME,time.monotonic()+5)
            self.assertEqual(1,reads.call_count); self.assertIs(primary[0],failure.exception)
        return failure.exception

    def test_actual_refusal_facts_and_each_predicate(self):
        cases=[('libggc_s.so.1','wrong-family'),('libgcc_s.so.debug','nonnumeric-suffix'),(NAME,'self-target'),
               ('libgcc_s.so.'+'1'*118,'invalid-library')]
        for target,reason in cases:
            error=self.witness(target); packet=e.provider_alias_packet(error.provider_alias_facts)
            self.assertIsNotNone(packet); self.assertEqual([reason],packet['failed_predicates'])
            self.assertEqual(hashlib.sha256(target.encode()).hexdigest(),packet['target_sha256'])
            self.assertEqual(target if e.library(target) else None,packet['target_literal'])
        for target in ('../private','libé.so.1'):
            _,error=caught(target); packet=e.provider_alias_packet(error.provider_alias_facts)
            self.assertIsNone(packet['target_literal']); self.assertNotIn(target,json.dumps(packet,ensure_ascii=False))

    def test_packet_rejects_hostile_data_and_copies_valid_facts(self):
        _,error=caught(); good=error.provider_alias_facts
        self.assertIsNotNone(e.provider_alias_packet(good)); self.assertIsNone(e.provider_alias_packet(json.dumps(good)))
        for field,value in [('format','other'),('snapshot','x'),('alias_layer',True),('alias_entry',65536),('target_utf8_bytes',241),
                            ('target_sha256','0'*64),('target_literal','libother.so.1'),('failed_predicates',[]),('runtime_grant',True)]:
            bad=copy.deepcopy(good); bad[field]=value; self.assertIsNone(e.provider_alias_packet(bad),field)
        bad=dict(good,secret='do-not-log'); self.assertIsNone(e.provider_alias_packet(bad))
        packet=e.provider_alias_packet(good); packet['checks']['not_self']=False
        self.assertTrue(good['checks']['not_self']); self.assertLessEqual(len(json.dumps(good).encode()),2048)

    def test_first_capture_and_diagnostic_failure_preserve_same_error(self):
        tree,error=caught(); first=copy.deepcopy(error.provider_alias_facts)
        alias=dict(tree._nodes['usr/lib64/'+NAME],snapshot=tree._snapshot_hash,target='libforeign.so.1')
        e.capture_provider_alias(error,NAME,alias); self.assertEqual(first,error.provider_alias_facts)
        with patch.object(e,'capture_provider_alias',side_effect=RuntimeError('secondary')):
            error=self.witness('libgcc_s.so.debug'); self.assertIsNone(error.provider_alias_facts)
        error=e.Refusal('elf-provider-alias'); error.provider_alias_facts=None
        e.capture_provider_alias(error,NAME,alias); self.assertIsNone(error.provider_alias_facts)

    def test_real_helper_module_relay_and_original_graph_packet(self):
        first=f.rows(False); first[-1]=x.member('usr/lib64/liba.so.1',x.tarfile.SYMTYPE,'libforeign.so.1')
        with f.fixture(first=first) as (owner,_,scan,_,_,_):
            layer=scan(); tree=u.apply_bounded_layer(a,None,owner,layer,0)[0]; tree.finalize(owner.now())
            with self.assertRaisesRegex(a.s.Refusal,'elf-provider-alias') as failure: a._owned_graph(tree,owner,[u.compact_layer(layer)])
            self.assertIsNotNone(e.provider_alias_packet(failure.exception.provider_alias_facts))
            self.assertIsNotNone(e.failure_packet(failure.exception.elf_facts)); self.assertEqual('poisoned',owner.snapshot()['state'])

    def retain(self,report,facts,tree,owner,frozen,config):
        u.retain_provider_alias_diagnostic(report,facts,a,e,tree,owner,frozen,config)

    def test_entry_context_positive_data_and_mismatch_rejection(self):
        with frame() as values:
            report,facts,tree,owner,frozen,config=values; self.retain(*values)
            packet=report['provider_alias_diagnostic']; self.assertIsNotNone(packet)
            self.assertEqual(facts['target_sha256'],packet['alias']['target_sha256']); self.assertEqual('b'*40,packet['context']['head'])
            self.assertEqual('refused',report['status']); self.assertLessEqual(len(json.dumps(packet).encode()),4096)
        for field,value in [('snapshot','e'*64),('alias_entry',9),('alias_layer',9)]:
            with frame() as (report,facts,tree,owner,frozen,config):
                facts=copy.deepcopy(facts); facts[field]=value; self.retain(report,facts,tree,owner,frozen,config)
                self.assertIsNone(report['provider_alias_diagnostic'],field)
        with frame() as values,patch.dict(os.environ,dict(GITHUB_SHA='e'*40)):
            self.retain(*values); self.assertIsNone(values[0]['provider_alias_diagnostic'])

    def test_context_validator_and_first_unavailable_report(self):
        with frame() as values:
            self.retain(*values); context=values[0]['provider_alias_diagnostic']['context']
            self.assertEqual(context,u.provenance.provider_alias_context(context))
            for field,value in [('head','x'),('manifest','a'*64),('profile','strict'),('layers_verified',True),('layer',10),('snapshot','x')]:
                bad=dict(context,**{field:value}); self.assertIsNone(u.provenance.provider_alias_context(bad),field)
        with frame() as values:
            values[0]['provider_alias_diagnostic']=None; self.retain(*values); self.assertIsNone(values[0]['provider_alias_diagnostic'])
        with frame() as values,patch.object(e,'provider_alias_packet',side_effect=RuntimeError('secondary')):
            self.retain(*values); self.assertIsNone(values[0]['provider_alias_diagnostic']); self.assertEqual('elf-provider-alias',values[0]['failure'])

    def test_four_guard_removal_controls_are_assertion_killed(self):
        source=inspect.getsource(e.provider)
        for old,target in [('library(target)','libgcc_s.so.'+'1'*118),('target.startswith(stem)','libggc_s.so.1'),
                           ('target != name',NAME),("re.fullmatch(r'[0-9]+(?:\\.[0-9]+)*', target[len(stem):])",'libgcc_s.so.debug')]:
            self.witness(target); self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
            exec(compile(source.replace(old,'True'),'<alias-guard-control>','exec'),scope)
            with self.assertRaises(AssertionError): self.witness(target,scope['provider'])

    def test_capture_retention_and_relay_controls_are_assertion_killed(self):
        def capture():
            _,error=caught(); self.assertIn('provider_alias_facts',error.__dict__); self.assertIsNotNone(error.__dict__['provider_alias_facts'])
        capture()
        with patch.object(e,'capture_provider_alias',return_value=None):
            with self.assertRaises(AssertionError): capture()
        def retain():
            with frame() as values:
                values[1]['secret']='do-not-log'; self.retain(*values); self.assertIsNone(values[0]['provider_alias_diagnostic'])
        retain()
        with patch.object(e,'provider_alias_packet',side_effect=lambda value: copy.deepcopy(value)):
            with self.assertRaises(AssertionError): retain()
        source=inspect.getsource(e.capture_provider_alias)
        old="if 'provider_alias_facts' in error.__dict__: return"
        self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'if False: return'),'<first-fact-control>','exec'),scope)
        def first_fact():
            tree,error=caught(); before=copy.deepcopy(error.provider_alias_facts)
            scope['capture_provider_alias'](error,NAME,dict(tree._nodes['usr/lib64/'+NAME],snapshot=tree._snapshot_hash,target='libforeign.so.1'))
            self.assertEqual(before,error.provider_alias_facts)
        with self.assertRaises(AssertionError): first_fact()

    def test_entry_context_validation_and_source_bound_relay_controls(self):
        source=inspect.getsource(u.retain_provider_alias_diagnostic); old="context = provenance.provider_alias_context("
        self.assertEqual(1,source.count(old))
        # A context-validator removal is killed by its forced-unavailable contract, not an earlier rejection.
        def check(retain):
            with frame() as values,patch.object(u.provenance,'provider_alias_context',return_value=None):
                retain(values[0],values[1],a,e,*values[2:]); self.assertIsNone(values[0]['provider_alias_diagnostic'])
        check(u.retain_provider_alias_diagnostic)
        scope=dict(u.__dict__); exec(compile(source.replace(old,'context = ('),'<context-validation-control>','exec'),scope)
        with self.assertRaises(AssertionError): check(scope['retain_provider_alias_diagnostic'])
        actual=inspect.getsource(u.proof)
        self.assertIn("if bounded_access_policy and 'provider_alias_facts' in error.__dict__:",actual)
        self.assertIn('retain_provider_alias_diagnostic(report, error.provider_alias_facts, alias, elf, tree, work_owner, frozen, config)',actual)
        # Entry failure does not prevent independently attempted cleanup/reporting (also tested by fixed-entry suite).
        self.assertIn('finish_report(report, root, frozen, work_owner)',actual)

    def test_cross_module_relay_failure_preserves_cause_and_owner_accounting(self):
        first=f.rows(False); first[-1]=x.member('usr/lib64/liba.so.1',x.tarfile.SYMTYPE,'libforeign.so.1')
        with f.fixture(first=first) as (owner,_,scan,_,_,_):
            layer=scan(); tree=u.apply_bounded_layer(a,None,owner,layer,0)[0]; tree.finalize(owner.now())
            loaded=a.load('source_policy_ubi_elf_static.py','relay_failure_control'); validator=loaded.provider_alias_packet; calls=[]
            def fail_second(value):
                calls.append(1)
                if len(calls)==2: raise RuntimeError('relay unavailable')
                return validator(value)
            with patch.object(a,'load',return_value=loaded),patch.object(loaded,'provider_alias_packet',side_effect=fail_second):
                with self.assertRaisesRegex(a.s.Refusal,'^elf-provider-alias$') as failure: a._owned_graph(tree,owner,[u.compact_layer(layer)])
            self.assertEqual(2,len(calls)); self.assertIsNone(failure.exception.provider_alias_facts)
            self.assertIs(type(failure.exception.__cause__),loaded.Refusal)
            self.assertIsNotNone(e.failure_packet(failure.exception.elf_facts)); self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_packet_and_context_boundaries_and_hostile_types(self):
        _,error=caught(); good=error.provider_alias_facts
        for field,value in [('requested_name',object()),('checks',[]),('target_literal','é'),('target_utf8_bytes',True),
                            ('alias_layer',-1),('alias_entry',-1),('failed_predicates',['nonnumeric-suffix']*2),
                            ('failed_predicates',['wrong-family','nonnumeric-suffix'])]:
            bad=copy.deepcopy(good); bad[field]=value; self.assertIsNone(e.provider_alias_packet(bad),field)
        for target in ('x'*241,'é'*121,'\ud800'):
            error=e.Refusal('elf-provider-alias')
            try: e.capture_provider_alias(error,NAME,dict(kind='symlink',target=target,snapshot='a'*64,layer=0,entry=0))
            except (e.Refusal,UnicodeError): pass
            self.assertIsNone(error.provider_alias_facts)
        with frame() as values:
            self.retain(*values); original=copy.deepcopy(values[0]['provider_alias_diagnostic'])
            values[1]['checks']['not_self']=False
            self.assertEqual(original,values[0]['provider_alias_diagnostic'])
        with frame() as values:
            values[4]['extra']='d'*64; self.retain(*values); self.assertIsNone(values[0]['provider_alias_diagnostic'])

    def test_pure_capture_and_relay_perform_no_io_or_execution(self):
        with frame() as values:
            before=values[3].snapshot()
            with patch('builtins.open',side_effect=AssertionError('IO')),patch.object(socket,'socket',side_effect=AssertionError('network')),patch.object(subprocess,'Popen',side_effect=AssertionError('process')),patch.object(os,'stat',side_effect=AssertionError('host stat')):
                self.retain(*values); self.assertIsNotNone(values[0]['provider_alias_diagnostic'])
            self.assertEqual(before,values[3].snapshot())

    def test_unavailable_diagnostic_still_attempts_independent_cleanup(self):
        # Isolated finalizer contract only; not physical Unix ownership/cleanup proof.
        with frame() as values,patch.object(e,'provider_alias_packet',side_effect=RuntimeError('secondary')):
            self.retain(*values); report=values[0]; report.update(runtime_grant=False)
            with patch.object(u.acquire,'file_hash',return_value='d'*64),patch.object(u,'cleanup',return_value=13) as cleanup,redirect_stdout(io.StringIO()):
                u.finish_report(report,Path('owned-synthetic-root'),values[4],values[3])
            cleanup.assert_called_once_with(Path('owned-synthetic-root'))
            self.assertIsNone(report['provider_alias_diagnostic']); self.assertEqual('elf-provider-alias',report['failure'])
            self.assertEqual('complete',report['cleanup']); self.assertFalse(u.successful(report,True))


if __name__=='__main__':
    out=io.StringIO(); result=unittest.TextTestRunner(stream=out).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8','backslashreplace')[:6500])
    sys.exit(0 if result.wasSuccessful() else 1)
