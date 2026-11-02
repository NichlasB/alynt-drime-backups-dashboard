"""Offline entry contracts, not a genuine job, candidate or native proof."""
import copy
from contextlib import redirect_stdout
import hashlib
import importlib.util
import io
import inspect
import json
import os
from pathlib import Path
import shutil
import socket
import subprocess
import sys
import tempfile
import textwrap
import time
import unittest
from unittest.mock import patch

ROOT=Path(__file__).resolve().parents[2]
spec=importlib.util.spec_from_file_location('entry',ROOT/'scripts/ci/source-policy-legacy-ubi-static.py')
u=importlib.util.module_from_spec(spec)
with patch.object(socket,'socket',side_effect=AssertionError('network forbidden')): spec.loader.exec_module(u)
spec=importlib.util.spec_from_file_location('owner_fixtures',Path(__file__).with_name('source_policy_ubi_fixed_owner_test.py'))
f=importlib.util.module_from_spec(spec); spec.loader.exec_module(f)


def snapshot():
    return dict(work_used=12,parser_work=4,model_work=6,burned_work=0,grants=1,settled=1,
                verified_layers=10,records=1,observation_bytes=800,state='closed-model',model_bound=True,runtime_grant=False)


def report():
    return dict(status='static-dt-needed-closure-verified',source_unchanged=True,cleanup='complete',reported=True,
                layers_verified=10,namespace_layers_committed=10,access_profile='bounded-strings-v1',
                work_owner=snapshot(),elf_graph=dict(static_dependency_closure_verified=True,runtime_grant=False,model_only=True),
                runtime_grant=False,candidate_execution=False,image_loading=False,database_operations=False,rootfs_extraction=False,reporting='complete')


class Contracts(unittest.TestCase):
    def test_exact_selector_matrix_and_old_tuple_contract(self):
        for arg,flags in [('--approved-static-prerequisite',(False,False,False)),
                          ('--approved-alias-static-prerequisite',(True,False,False)),
                          ('--approved-elf-static-prerequisite',(True,True,False)),
                          ('--approved-bounded-elf-static-prerequisite',(True,True,True))]:
            values=tuple('true' if b else 'false' for b in flags)
            self.assertEqual(flags,u.selection([arg],*values))
            if not flags[2]: self.assertEqual(flags[:2],u.policy_flags([arg],*values[:2]))
            for i in range(3):
                for bad in (not flags[i], 'TRUE', None, 'false' if flags[i] else 'true'):
                    changed=list(values); changed[i]=bad
                    with self.assertRaises(u.s.Refusal): u.selection([arg],*changed)
        for args in ([],['--cleanup'],['--approved-bounded-elf-static-prerequisite','extra'],[object()]):
            with self.assertRaises(u.s.Refusal): u.selection(args,'true','true','true')

    def test_invalid_modes_refuse_before_effects(self):
        with patch.object(u,'Owned',side_effect=AssertionError('creation')),patch.object(u.acquire,'Client',side_effect=AssertionError('network')):
            for args in ((False,False,True),(True,False,True),(True,True,1)):
                with self.assertRaises(u.s.Refusal): u.proof(Path('never-created'),*args)

    def test_actual_fixed_authority_inputs_and_original_deadline(self):
        deadline=time.monotonic()+25
        config=dict(rootfs=dict(diff_ids=['sha256:'+'a'*64]*10))
        frozen={'fixed.py':'d'*64}
        env=dict(GITHUB_SHA='b'*40,GITHUB_RUN_ID='123',GITHUB_RUN_ATTEMPT='2')
        with patch.dict(os.environ,env): owner=u.create_work_authority(f.a,config,frozen,deadline)
        self.assertEqual(deadline,owner.deadline); self.assertEqual(0,owner.snapshot()['work_used'])
        self.assertEqual('b'*40,owner._run['head'])
        self.assertEqual(hashlib.sha256(b'123:2').hexdigest()[:32],owner._run['run'])
        self.assertEqual(hashlib.sha256(json.dumps(frozen,sort_keys=True).encode()).hexdigest(),owner._identity['source'])
        self.assertEqual([(sha,'a'*64) for sha,_ in u.LAYERS],owner._layers)

    def test_compact_real_collections_and_mandatory_real_pin_refusal(self):
        with f.fixture() as (owner,_,scan,_,_,_):
            first=scan(); compact=u.compact_layer(first)
            self.assertEqual({'metadata','elf','elf_bindings'},set(compact)); self.assertNotIn('records',compact)
            with patch.object(f.v.VirtualClosure,'charge',side_effect=AssertionError('parser replay')):
                tree,compact=u.apply_bounded_layer(f.a,None,owner,first,0)
            tree.finalize(owner.now())
            with patch.object(f.a,'_owned_graph',side_effect=AssertionError('graph before pins')) as graph:
                with self.assertRaisesRegex(f.e.Refusal,'elf-selected-identity'): u.bounded_graph(f.a,f.e,tree,owner,[compact])
                graph.assert_not_called()
        # Genuine generic positive remains distinct from the real fixed-root matcher.
        with f.fixture() as (owner,_,scan,_,_,_):
            first=scan(); tree=f.a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
            self.assertEqual(3,f.a._owned_graph(tree,owner,[u.compact_layer(first)])['objects'])

    def test_snapshot_and_success_data_validation_are_not_authority(self):
        self.assertEqual(snapshot(),u.provenance.owner_snapshot(snapshot()))
        self.assertTrue(u.successful(report(),True))
        for field,value in [('work_used',13),('grants',257),('settled',True),('state','ready'),('verified_layers',11),
                            ('runtime_grant',True),('model_bound',False),('observation_bytes',2097153)]:
            bad=snapshot(); bad[field]=value
            if field not in ('state','model_bound'): self.assertIsNone(u.provenance.owner_snapshot(bad))
            failed=report(); failed['work_owner']=bad; self.assertFalse(u.successful(failed,True))
        for field,value in [('status','selected-origins-verified'),('cleanup','unconfirmed'),('source_unchanged',False),
                            ('reported',False),('layers_verified',9),('access_profile','strict')]:
            bad=report(); bad[field]=value; self.assertFalse(u.successful(bad,True))

    def test_source_failure_does_not_skip_cleanup_or_replace_first_cause(self):
        result=dict(status='refused',failure='elf-first')
        with patch.object(u.acquire,'file_hash',side_effect=OSError('private')),patch.object(u,'cleanup',return_value=0) as clean,redirect_stdout(io.StringIO()):
            u.finish_report(result,Path('owned-root'),{'fixed':'a'*64})
        clean.assert_called_once(); self.assertIs(False,result['source_unchanged']); self.assertEqual('complete',result['cleanup'])
        self.assertEqual('elf-first',result['failure']); self.assertFalse(u.successful(result,False))

    def test_cleanup_runs_without_owner_handle_and_reporting_cannot_forge_success(self):
        for fault in ('cleanup','reporting'):
            result=dict(status='refused',failure='first')
            with patch.object(u.acquire,'file_hash',return_value='a'*64),patch.object(u,'cleanup',side_effect=OSError('private') if fault=='cleanup' else None,return_value=0) as clean:
                with patch('builtins.print',side_effect=OSError('private')) if fault=='reporting' else redirect_stdout(io.StringIO()):
                    u.finish_report(result,Path('owned-partial-root'),{'fixed':'a'*64})
            clean.assert_called_once(); self.assertEqual('first',result['failure']); self.assertFalse(u.successful(result,False))
            self.assertEqual('unconfirmed',result['cleanup'] if fault=='cleanup' else result['reporting'])

    def test_graph_refusal_cross_module_relay_retains_finite_cause(self):
        with f.fixture(first=f.rows(False)) as (owner,_,scan,_,_,_):
            first=scan(); tree=f.a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
            # Actual policy failure inside the separately loaded graph module.
            tree._nodes[f.e.SERVER]['mode'] |= 0o4000
            with self.assertRaisesRegex(f.a.s.Refusal,'elf-selected-file') as failure: f.a._owned_graph(tree,owner,[first])
            self.assertIsNotNone(f.e.failure_packet(failure.exception.elf_facts))
            self.assertEqual('poisoned',owner.snapshot()['state'])
        first=f.rows(False); first[-1]=f.x.member('usr/lib64/liba.so.1',data=f.f.t.binary(soname='libwrong.so.1'))
        with f.fixture(first=first) as (owner,_,scan,_,_,_):
            layer=scan(); tree,compact=u.apply_bounded_layer(f.a,None,owner,layer,0); tree.finalize(owner.now())
            with self.assertRaisesRegex(f.a.s.Refusal,'elf-provider-soname') as failure: f.a._owned_graph(tree,owner,[compact])
            packet=f.e.failure_packet(failure.exception.elf_facts); self.assertIsNotNone(packet)
            packet['roots'][f.e.SERVER]['needed'].append('libmutant.so.1')
            self.assertNotIn('libmutant.so.1',failure.exception.elf_facts['roots'][f.e.SERVER]['needed'])
        with f.fixture() as (owner,_,scan,_,_,_):
            layer=scan(); tree,compact=u.apply_bounded_layer(f.a,None,owner,layer,0); tree.finalize(owner.now())
            layer['elf'][next(iter(layer['elf']))]['work']=True
            with self.assertRaisesRegex(f.a.s.Refusal,'elf-metadata') as failure: f.a._owned_graph(tree,owner,[compact])
            self.assertIsNot(type(failure.exception.__cause__),f.e.Refusal)
            self.assertEqual(owner.snapshot(),failure.exception.fixed_owner_facts)
        self.assertIsNone(f.e.failure_packet(dict(format='ADBUELF1',raw='private')))

    def test_workflow_default_and_guarded_shell_source_check(self):
        workflow=(ROOT/'.github/workflows/source-policy-legacy-ubi-static.yml').read_text()
        section=workflow.split('      bounded_access_policy:',1)[1].split('permissions:',1)[0]
        self.assertIn('default: false',section); self.assertIn('if: always()',workflow)
        self.assertIn('true:true:false)',workflow); self.assertIn('true:true:true)',workflow)
        self.assertNotIn('schedule:',workflow); self.assertNotIn('pull_request:',workflow)
        shell=workflow.split('      - name: Admit checked-out source closure\n        shell: bash\n        run: |\n',1)[1].split('      - name:',1)[0]
        shell=textwrap.dedent(shell)
        bash=shutil.which('bash') if os.name!='nt' else str(Path(shutil.which('git')).parents[1]/'bin/bash.exe')
        with tempfile.TemporaryDirectory() as temp:
            parent=Path(temp)
            for relative in u.BOUNDED_SOURCES:
                path=parent/relative; path.parent.mkdir(parents=True,exist_ok=True); path.write_text('fixed\n')
            def git(*args):
                return subprocess.run(['git','-c','core.autocrlf=false','-c','user.name=Owned Fixture','-c','user.email=owned@example.invalid',*args],cwd=parent,capture_output=True,check=True,timeout=10).stdout.decode().strip()
            git('init','-q'); git('add','.'); git('-c','user.name=Owned Fixture','-c','user.email=owned@example.invalid','commit','-qm','owned')
            previous=git('rev-parse','HEAD'); (parent/'owned-doc.txt').write_text('docs only\n'); git('add','.'); git('commit','-qm','owned docs')
            head=git('rev-parse','HEAD')
            def run(expected,script=shell): return subprocess.run([bash,'-c',script],cwd=parent,env=dict(os.environ,EXPECTED_SHA=expected,BOUNDED_ACCESS_POLICY='true'),capture_output=True,timeout=10).returncode
            self.assertEqual(0,run(head)); self.assertNotEqual(0,run('f'*40))
            self.assertNotEqual(0,run(previous))
            with self.assertRaises(AssertionError): self.assertNotEqual(0,run(previous,shell.replace('[[ "$(git rev-parse HEAD)" == "$EXPECTED_SHA" ]]',':')))
            for relative in u.BOUNDED_SOURCES:
                path=parent/relative; path.write_text('changed\n'); self.assertNotEqual(0,run(head))
                with self.assertRaises(AssertionError): self.assertNotEqual(0,run(head,shell.replace('git diff --no-ext-diff --no-textconv --quiet','true')))
                path.write_text('fixed\n')

    def test_actual_proof_stops_before_layer_transport_with_one_real_authority(self):
        # Transport DATA are isolated doubles, not hash-verified metadata or an acquired candidate.
        manifest=dict(schemaVersion=2,mediaType='application/vnd.oci.image.manifest.v1+json',
            config=dict(digest='sha256:'+u.CONFIG,size=13902),layers=[dict(digest='sha256:'+sha,size=size,mediaType='application/vnd.oci.image.layer.v1.tar+gzip') for sha,size in u.LAYERS])
        config=dict(os='linux',architecture='amd64',rootfs=dict(type='layers',diff_ids=['sha256:'+'a'*64]*10),
            config=dict(User='mysql',Entrypoint=['docker-entrypoint.sh'],Cmd=['mariadbd'],Volumes={'/var/lib/mysql':{}},
                Env=['PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin','container=oci','GOSU_VERSION=1.17','MARIADB_VERSION=10.6.23']))
        captured=[]
        class OfflineClient:
            payload=0
            def __init__(self,owner,deadline): self.deadline=deadline; captured.append(self)
            def get(self,url,*args,**kw):
                if '/token?' in url: return b'{"token":"synthetic-only"}'
                if '/manifests/' in url: return json.dumps(manifest).encode()
                if url.endswith(u.CONFIG): return json.dumps(config).encode()
                raise u.s.Refusal('offline-before-layer')
        original=u.create_work_authority
        def authority(*args):
            owner=original(*args); captured.append(owner); return owner
        for policies in ((False,False,False),(True,False,False),(True,True,False),(True,True,True),(True,True,True,True)):
            captured.clear()
            with tempfile.TemporaryDirectory() as parent,patch.dict(os.environ,dict(GITHUB_SHA='b'*40,EXPECTED_SHA='b'*40,GITHUB_RUN_ID='321',GITHUB_RUN_ATTEMPT='1')):
                root=Path(parent).resolve()/'adb-legacy-ubi-321-1'; out=io.StringIO()
                with patch.object(u.acquire,'Client',OfflineClient),patch.object(u,'create_work_authority',side_effect=authority),patch.object(socket,'socket',side_effect=AssertionError('network')),redirect_stdout(out):
                    result=u.proof(root,*policies)
                final=json.loads(out.getvalue().splitlines()[-1]); self.assertEqual(1,result); self.assertEqual('offline-before-layer',final['failure'])
                self.assertEqual(2 if policies[2] else 1,len(captured))
                if policies[2]:
                    self.assertEqual(captured[0].deadline,captured[1].deadline)
                    self.assertEqual(0,final['work_owner']['grants']); self.assertEqual('poisoned',final['work_owner']['state'])
                else: self.assertNotIn('work_owner',final)
                self.assertFalse(final['candidate_execution']); self.assertEqual(0,final['layers_verified'])
                if os.name!='nt': self.assertEqual('complete',final['cleanup']); self.assertFalse(root.exists())
                else: self.assertEqual('unconfirmed',final['cleanup'])  # Windows is not Unix UID/mode evidence.

    def test_partial_constructor_attempts_exact_cleanup_without_false_absence(self):
        for corrupt in (False,True):
            with tempfile.TemporaryDirectory() as parent,patch.dict(os.environ,dict(EXPECTED_SHA='b'*40)):
                root=Path(parent).resolve()/'adb-legacy-ubi-321-1'; original=u.Owned; out=io.StringIO()
                def partial(path):
                    original(path)
                    if corrupt: (path/'owner.json').write_bytes(b'owned-test-invalid-marker')
                    raise OSError('fixture-only constructor interruption')
                with patch.object(u,'Owned',side_effect=partial),patch.object(u.acquire,'Client',side_effect=AssertionError('network')),patch.object(u,'cleanup',wraps=u.cleanup) as cleanup,redirect_stdout(out):
                    self.assertEqual(1,u.proof(root))
                cleanup.assert_called_once_with(root); final=json.loads(out.getvalue().splitlines()[-1])
                self.assertEqual('unexpected-tool-failure',final['failure'])
                if corrupt or os.name=='nt':
                    self.assertEqual('unconfirmed',final['cleanup']); self.assertTrue(root.exists())
                    if corrupt: self.assertEqual(b'owned-test-invalid-marker',(root/'owner.json').read_bytes())
                else:
                    self.assertEqual('complete',final['cleanup']); self.assertEqual(1,final['owned_files_removed']); self.assertFalse(root.exists())

    def test_assertion_killed_selector_pin_accounting_and_cleanup_controls(self):
        def selector():
            with self.assertRaises(u.s.Refusal): u.selection(['--approved-bounded-elf-static-prerequisite'],'true','true','TRUE')
        selector(); original=u.s.require
        with patch.object(u.s,'require',side_effect=lambda ok,code: original(True if code=='explicit-bounded-mode' else ok,code)):
            with self.assertRaises(AssertionError): selector()
        def pins():
            with f.fixture() as (owner,_,scan,_,_,_):
                first=scan(); tree,compact=u.apply_bounded_layer(f.a,None,owner,first,0); tree.finalize(owner.now())
                with self.assertRaises(f.e.Refusal): u.bounded_graph(f.a,f.e,tree,owner,[compact])
        pins()
        with patch.object(f.e,'admit_selected',return_value=None):
            with self.assertRaises(AssertionError): pins()  # Deliberately removed guard is detected, never accepted as evidence.
        def accounting():
            bad=report(); bad['work_owner']['work_used']+=1; self.assertFalse(u.successful(bad,True))
        accounting()
        with patch.object(u.provenance,'owner_snapshot',side_effect=lambda value: dict(value)):
            with self.assertRaises(AssertionError): accounting()
        witness=self.test_source_failure_does_not_skip_cleanup_or_replace_first_cause
        witness()
        source=inspect.getsource(u.finish_report)
        old="report['owned_files_removed'] = cleanup(root)"; self.assertEqual(1,source.count(old))
        scope=dict(u.__dict__); exec(compile(source.replace(old,"report['owned_files_removed'] = 0"),'<cleanup-guard-control>','exec'),scope)
        with patch.object(u,'finish_report',scope['finish_report']):
            with self.assertRaises(AssertionError): witness()


if __name__=='__main__':
    out=io.StringIO(); result=unittest.TextTestRunner(stream=out).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8','backslashreplace')[:6500])
    sys.exit(0 if result.wasSuccessful() else 1)
