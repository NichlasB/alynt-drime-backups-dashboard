"""Dormant policy/model tests. Layer verification flags are DATA, not artifact proof."""
import copy
from contextlib import contextmanager
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
import textwrap
import time
import unittest
from unittest.mock import patch

spec=importlib.util.spec_from_file_location('diagnostic_fixtures',Path(__file__).with_name('source_policy_ubi_provider_alias_diagnostic_test.py'))
d=importlib.util.module_from_spec(spec); spec.loader.exec_module(d)
a,v,e,u=d.a,d.v,d.e,d.u
NAME='libgcc_s.so.1'; TARGET='libgcc_s-11-20240719.so.1'; PATH='usr/lib64/'+NAME; DEST='usr/lib64/'+TARGET
DIFF='004d2c90a65694c2830b06fddc1047d40063c6cb36fb31a5a3edfce9435326c6'


@contextmanager
def fixture(target=TARGET, soname=NAME, needed=(), kind='file', mode=0o755, missing=False):
    # No real image, GitHub authentication or root-pin factory success is asserted.
    config=dict(rootfs=dict(diff_ids=['sha256:'+DIFF]+['sha256:'+'a'*64]*9))
    frozen={key:'d'*64 for key in u.BOUNDED_SOURCES}
    env=dict(GITHUB_SHA='b'*40,EXPECTED_SHA='b'*40,GITHUB_RUN_ID='123',GITHUB_RUN_ATTEMPT='1')
    with patch.dict(os.environ,env):
        owner=u.create_work_authority(a,config,frozen,time.monotonic()+30); tree=None; compact=[]; observations={}
        rows=[d.row('usr'),d.row('usr/lib64'),d.row('lib64','symlink','usr/lib64'),d.row('usr/sbin')]
        bodies={e.SERVER:d.f.f.t.binary([NAME],interpreter=e.INTERPRETER),e.LOADER:d.f.f.t.binary(soname='ld-linux-x86-64.so.2')}
        if not missing and kind=='file': bodies[DEST]=d.f.f.t.binary(needed,soname=soname)
        for path,body in bodies.items():
            rows.append(dict(path=path,kind='file',target=None,mode=mode if path==DEST else 0o755,uid=0,gid=0,size=len(body),sha256=hashlib.sha256(body).hexdigest()))
        if not missing and kind!='file': rows.append(d.row(DEST,kind,'libother.so.1' if kind=='symlink' else None))
        while len(rows)<1700: rows.append(d.row('padding'+str(len(rows))))
        rows.append(d.row(PATH,'symlink',target))
        for i,(compressed,diff) in enumerate(owner._layers):
            owner.begin_layer(i,compressed,diff,owner.deadline); meta=dict(compressed=compressed,diff_id=diff,compressed_verified=True,diff_verified=True)
            found={}; bindings={}
            if i==0:
                for index,row in enumerate(rows):
                    if row['kind']=='file':
                        parsed,binding=a._parse_owned(bodies[row['path']],row,index,dict(entries=index+1),owner,0,e)
                        owner.retain(binding,len(json.dumps([parsed,binding]).encode('ascii')))
                        found[index]=parsed; bindings[index]=binding; observations['0:'+str(index)]=parsed
            owner.verify_layer(meta,[dict(path='lib64',target='usr/lib64',layer=0,entry=2)] if i==0 else None)
            if i==0: tree=v.VirtualClosure(owner._identity,owner._layers,[dict(path='lib64',target='usr/lib64',layer=0,entry=2)],hardlinks=True,work_owner=owner)
            tree.apply(dict(meta,ordinal=i),rows if i==0 else [],owner.now()); compact.append(dict(metadata=meta,elf=found,elf_bindings=bindings))
        tree.finalize(owner.now())
        context=dict(format='ADBUCAP1',profile='candidate-libgcc-alias-v1',head=owner._run['head'],run=owner._run['run'],
                     source=owner._identity['source'],manifest=a.PIN,config=a.CONFIG,snapshot=tree._snapshot_hash,layers=tuple(owner._layers))
        yield tree,owner,context,compact,observations


class Contracts(unittest.TestCase):
    def test_explicit_exact_provider_positive_and_default_still_refuses(self):
        with fixture() as (tree,owner,context,_,_):
            policy=e._CandidateAliasPolicy(tree,owner,context)
            with patch.object(tree,'inspect',wraps=tree.inspect) as reads:
                path,node,edges=e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
                self.assertEqual(DEST,path); self.assertEqual('file',node['kind']); self.assertEqual(1,len(edges)); self.assertEqual(2,reads.call_count)
        with fixture() as (tree,owner,_,_,_):
            with patch.object(tree,'inspect',wraps=tree.inspect) as reads:
                with self.assertRaisesRegex(e.Refusal,'^elf-provider-alias$'): e.provider(tree,NAME,owner.deadline)
                self.assertEqual(1,reads.call_count)

    def test_pure_exact_match_and_each_alias_tuple_negative(self):
        with fixture() as (tree,owner,context,_,_):
            row=tree.inspect(PATH,owner.now()); self.assertTrue(e._candidate_libgcc_alias(NAME,row,context['snapshot']))
            for field,value in [('path','usr/lib64/libother.so.1'),('target','libgcc_s-12-20240719.so.1'),('layer',True),('entry',1699),('snapshot','a'*64),('kind','file')]:
                bad=dict(row,**{field:value}); self.assertFalse(e._candidate_libgcc_alias(NAME,bad,context['snapshot']),field)
            self.assertFalse(e._candidate_libgcc_alias('libother.so.1',row,context['snapshot']))
            self.assertFalse(e._candidate_libgcc_alias(NAME,dict(row,extra='not-authority'),context['snapshot']))
            for field,value in [('mode',True),('uid',-1),('size',True),('runtime_grant',True),('model_only',False)]:
                self.assertFalse(e._candidate_libgcc_alias(NAME,dict(row,**{field:value}),context['snapshot']))

    def test_invalid_context_and_invalid_profile_do_not_inspect(self):
        for field,value in [('format','x'),('profile','strict'),('manifest','a'*64),('config','a'*64),('head','a'*40),('run','a'*32),
                            ('source','a'*64),('snapshot','a'*64),('layers',[])]:
            with fixture() as (tree,owner,context,_,_):
                bad=dict(context,**{field:value})
                with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                    with self.assertRaises(e.Refusal): e._CandidateAliasPolicy(tree,owner,bad)
        with fixture() as (tree,owner,context,_,_):
            for bad in (True,'candidate-libgcc-alias-v1',context,object()):
                with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                    with self.assertRaises(e.Refusal): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=bad)

    def test_actual_graph_retains_soname_and_target_security_after_alias_match(self):
        for options,reason in [({},None),({'soname':TARGET},'elf-provider-soname'),({'missing':True},'missing-node'),
                               ({'kind':'symlink'},'elf-provider-target'),({'mode':0o4755},'elf-provider-target'),({'needed':(NAME,)},'dependency-cycle')]:
            with fixture(**options) as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                if reason:
                    with self.assertRaisesRegex((e.Refusal,v.Refusal),reason): e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
                else:
                    graph=e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
                    self.assertEqual(3,graph['objects']); self.assertEqual(1,graph['provider_alias_count']); self.assertFalse(graph['runtime_grant'])

    def test_helper_constructs_same_module_policy_and_default_does_not_enable_it(self):
        with fixture() as (tree,owner,context,layers,_):
            graph=a._owned_graph(tree,owner,layers,candidate_alias_context=context)
            self.assertEqual(1,graph['provider_alias_count']); self.assertEqual('closed-model',graph['work_owner']['state'])
        with fixture() as (tree,owner,_,layers,_):
            with self.assertRaisesRegex(a.s.Refusal,'^elf-provider-alias$') as failure: a._owned_graph(tree,owner,layers)
            self.assertIsNotNone(e.provider_alias_packet(failure.exception.provider_alias_facts)); self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_context_exact_shapes_layers_and_copies_are_not_authority(self):
        self.assertEqual(tuple(pair[0] for pair in u.LAYERS),e._LIBGCC_LAYERS)
        with fixture() as (tree,owner,context,_,_):
            for bad in (json.dumps(dict(context,layers=[])),dict(context,extra=True),object(),dict(context,layers=list(context['layers']))):
                with self.assertRaises(e.Refusal): e._CandidateAliasPolicy(tree,owner,bad)
            for index in range(10):
                layers=list(context['layers']); layers[index]=('f'*64,layers[index][1])
                with self.assertRaises(e.Refusal): e._CandidateAliasPolicy(tree,owner,dict(context,layers=tuple(layers)))
            layers=list(context['layers']); layers[0]=(layers[0][0],'a'*64)
            with self.assertRaises(e.Refusal): e._CandidateAliasPolicy(tree,owner,dict(context,layers=tuple(layers)))
            policy=e._CandidateAliasPolicy(tree,owner,context); context['snapshot']='a'*64
            self.assertEqual(DEST,e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)[0])

    def test_reused_policy_rejects_stale_or_foreign_frame_before_any_read(self):
        actions=[lambda t,o:setattr(t,'_final',False),lambda t,o:setattr(t,'_snapshot_hash','f'*64),
                 lambda t,o:setattr(t,'_committed',9),lambda t,o:setattr(o,'_verified',True),
                 lambda t,o:setattr(t,'_work_owner',None),lambda t,o:setattr(o,'_model',None),
                 lambda t,o:o._run.update(head='f'*40),lambda t,o:o._run.update(run='f'*32),
                 lambda t,o:o._identity.update(source='f'*64),lambda t,o:o._identity.update(manifest='f'*64),
                 lambda t,o:o._identity.update(config='f'*64),lambda t,o:o._layers.__setitem__(9,('f'*64,'a'*64)),
                 lambda t,o:setattr(o,'_phase','closed-model'),lambda t,o:o.poison()]
        for action in actions:
            with fixture() as (tree,owner,context,_,_):
                policy=e._CandidateAliasPolicy(tree,owner,context); action(tree,owner)
                with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                    with self.assertRaises((e.Refusal,v.Refusal)): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
        with fixture() as (tree,owner,context,_,_),fixture() as (foreign,_,_,_,_):
            policy=e._CandidateAliasPolicy(tree,owner,context)
            with patch.object(foreign,'inspect',side_effect=AssertionError('protected read')):
                with self.assertRaises(e.Refusal): e.provider(foreign,NAME,owner.deadline,candidate_alias_policy=policy)

    def test_other_targets_and_alias_origins_refuse_before_target_lookup(self):
        for target in ('libgcc_s-12-20240719.so.1','libgcc_s-11-20240720.so.1','libforeign.so.1','../private','/private','libé.so.1',NAME,'x'*129):
            with fixture(target=target) as (tree,owner,context,_,_):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                with patch.object(tree,'inspect',wraps=tree.inspect) as reads:
                    with self.assertRaisesRegex(e.Refusal,'^elf-provider-alias$'): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
                    self.assertEqual(1,reads.call_count)
        for target in ('../private',NAME,'x'*129):
            with fixture(target=target) as (tree,owner,context,_,_):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                with patch.object(e,'_candidate_libgcc_alias',return_value=True),patch.object(tree,'inspect',wraps=tree.inspect) as reads:
                    with self.assertRaisesRegex(e.Refusal,'^elf-provider-alias$'): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
                    self.assertEqual(1,reads.call_count)  # Even an overbroad exception cannot bypass name/non-self requirements.
        for key,value in [('entry',True),('layer',1)]:
            with fixture() as (tree,owner,context,_,_):
                policy=e._CandidateAliasPolicy(tree,owner,context); original=tree.inspect
                def stale(path,now): return dict(original(path,now),**{key:value})
                with patch.object(tree,'inspect',side_effect=stale) as reads:
                    with self.assertRaisesRegex(e.Refusal,'^elf-provider-alias$'): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
                    self.assertEqual(1,reads.call_count)

    def test_post_match_metadata_origin_snapshot_and_link_guards_remain_effective(self):
        for key,value,reason in [('sha256','f'*64,'work-owner-final-origin'),('size',999,'work-owner-final-origin'),
                                 ('content_origin',dict(path=DEST,layer=0,entry=999,sha256='f'*64,size=99),'work-owner-final-origin'),
                                 ('hardlinks',[{}]*16,'elf-provider-target')]:
            with fixture() as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context); tree._nodes[DEST][key]=value
                with self.assertRaisesRegex((e.Refusal,v.Refusal),reason): e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
        for mutate,reason in [(lambda rows:rows.pop('0:6'),'elf-observation-missing'),
                              (lambda rows:rows['0:6'].update(type=2),'elf-provider-soname'),
                              (lambda rows:rows['0:6'].update(search_paths=['/tmp']),'elf-search-policy'),
                              (lambda rows:rows['0:6'].update(unsupported_features=['audit']),'elf-dependency-feature-policy'),
                              (lambda rows:rows['0:6'].update(interpreter='/tmp/loader'),'elf-interpreter-policy')]:
            with fixture() as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context); mutate(observations)
                # Deliberately corrupt DATA to exercise each downstream guard; not accepted owner metadata proof.
                with self.assertRaisesRegex((e.Refusal,v.Refusal),reason): e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
        with fixture() as (tree,owner,context,_,observations):
            policy=e._CandidateAliasPolicy(tree,owner,context); original=tree.inspect
            def stale(path,now): return dict(original(path,now),snapshot='f'*64) if path==DEST else original(path,now)
            with patch.object(tree,'inspect',side_effect=stale):
                with self.assertRaisesRegex(e.Refusal,'elf-snapshot-binding'): e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)

    def test_helper_revalidates_bindings_and_preserves_finite_failure_and_poison(self):
        with fixture() as (tree,owner,context,layers,_):
            layers[0]['elf'][6]['soname']='libforeign.so.1'
            with self.assertRaisesRegex(v.Refusal,'work-owner-origin'): a._owned_graph(tree,owner,layers,candidate_alias_context=context)
            self.assertEqual('poisoned',owner.snapshot()['state'])
        with fixture() as (tree,owner,context,layers,_):
            bad=dict(context,snapshot='f'*64)
            with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                with self.assertRaisesRegex(a.s.Refusal,'elf-candidate-policy-binding') as failure: a._owned_graph(tree,owner,layers,candidate_alias_context=bad)
            self.assertIsNone(failure.exception.elf_facts); self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_shared_deadline_budget_and_no_io_remain_enforced(self):
        with fixture() as (tree,owner,context,_,_):
            before=owner.snapshot()['work_used']; deadline=owner.deadline
            with patch('builtins.open',side_effect=AssertionError('IO')),patch.object(socket,'socket',side_effect=AssertionError('network')),patch.object(subprocess,'Popen',side_effect=AssertionError('process')):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                self.assertEqual(DEST,e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)[0])
            self.assertGreaterEqual(owner.snapshot()['work_used']-before,64); self.assertEqual(deadline,owner.deadline)
        with fixture() as (tree,owner,context,_,_):
            policy=e._CandidateAliasPolicy(tree,owner,context); owner._used=2097152
            with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                with self.assertRaises(v.Refusal): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
        with fixture() as (tree,owner,context,_,_):
            policy=e._CandidateAliasPolicy(tree,owner,context)
            with patch.object(time,'monotonic',return_value=owner.deadline+1),patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                with self.assertRaises(v.Refusal): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)

    def test_source_root_pins_and_fixed_entry_workflow_remain_unmodified(self):
        source=(d.ROOT/'scripts/ci/source-policy-legacy-ubi-static.py').read_text()
        workflow=(d.ROOT/'.github/workflows/source-policy-legacy-ubi-static.yml').read_text()
        self.assertNotIn('candidate_alias_context',source); self.assertNotIn('candidate-libgcc-alias-v1',workflow)
        self.assertLess(source.index('elf.admit_selected(roles)',source.index('def bounded_graph')),source.index('alias._owned_graph(tree',source.index('def bounded_graph')))
        with fixture() as (tree,owner,_,_,_):
            roles={key:tree.resolve(key,owner.now()) for key in u.WANTED}
            with self.assertRaisesRegex(e.Refusal,'elf-selected-identity'): e.admit_selected(roles)
        foreign=a.load('source_policy_ubi_elf_static.py','foreign_policy')
        with fixture() as (tree,owner,context,_,_):
            policy=foreign._CandidateAliasPolicy(tree,owner,context)
            with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                with self.assertRaisesRegex(e.Refusal,'elf-candidate-policy-type'): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)

    def test_default_tuple_context_and_owner_guard_removals_are_assertion_killed(self):
        def witness_default():
            with fixture() as (tree,owner,_,_,_):
                with self.assertRaisesRegex(e.Refusal,'^elf-provider-alias$'): e.provider(tree,NAME,owner.deadline)
        witness_default(); source=inspect.getsource(e.provider)
        old='valid_target and (strict or (candidate_alias_policy is not None and candidate_alias_policy._matches(name, row)))'
        self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'True'),'<default-control>','exec'),scope)
        with patch.object(e,'provider',scope['provider']):
            with self.assertRaises(AssertionError): witness_default()
        for old,key,value in [("row['entry'] == 1700",'entry',1699),("row['target'] == 'libgcc_s-11-20240719.so.1'",'target','libgcc_s-12-20240719.so.1')]:
            source=inspect.getsource(e._candidate_libgcc_alias); self.assertEqual(1,source.count(old))
            # Target has both literal and hash guards; remove the exact tuple identity together, not just redundant equality.
            changed=source.replace(old,'True')
            if key=='target':
                digest="hashlib.sha256(row['target'].encode('ascii')).hexdigest() == 'd359192c4c77928fa9d0d5be9e87f9a95cad61eaa2081a46213ae80d18bb2ec9'"
                self.assertEqual(1,changed.count(digest)); changed=changed.replace(digest,'True')
            scope=dict(e.__dict__); exec(compile(changed,'<tuple-control>','exec'),scope)
            with fixture() as (tree,owner,context,_,_):
                row=dict(tree.inspect(PATH,owner.now()),**{key:value})
                self.assertFalse(e._candidate_libgcc_alias(NAME,row,context['snapshot']))
                with self.assertRaises(AssertionError): self.assertFalse(scope['_candidate_libgcc_alias'](NAME,row,context['snapshot']))
        def witness_context():
            with fixture() as (tree,owner,context,_,_):
                with self.assertRaises(e.Refusal): e._CandidateAliasPolicy(tree,owner,dict(context,profile='other'))
        witness_context(); source=inspect.getsource(e._candidate_alias_frame)
        old="context['profile'] == 'candidate-libgcc-alias-v1'"; self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'True'),'<context-control>','exec'),scope)
        with patch.object(e,'_candidate_alias_frame',scope['_candidate_alias_frame']):
            with self.assertRaises(AssertionError): witness_context()
        def witness_binding():
            with fixture() as (tree,owner,context,_,_):
                policy=e._CandidateAliasPolicy(tree,owner,context); owner._identity.update(source='f'*64)
                with self.assertRaisesRegex(e.Refusal,'elf-candidate-policy-binding'): e.provider(tree,NAME,owner.deadline,candidate_alias_policy=policy)
        witness_binding(); source=textwrap.dedent(inspect.getsource(e._CandidateAliasPolicy._admit))
        old='owner._identity == tree._identity == dict(manifest=manifest, config=config, source=source)'; self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'True'),'<owner-binding-control>','exec'),scope)
        with patch.object(e._CandidateAliasPolicy,'_admit',scope['_admit']):
            with self.assertRaises(AssertionError): witness_binding()

    def test_graph_propagation_soname_and_content_guard_removals_are_assertion_killed(self):
        def positive():
            with fixture() as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                try: graph=e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
                except Exception as error: self.fail('positive DATA graph unexpectedly refused: '+type(error).__name__)
                self.assertEqual(3,graph['objects'])
        positive(); source=inspect.getsource(e.graph); old='provider(tree, requested, deadline, candidate_alias_policy=candidate_alias_policy)'
        self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'provider(tree, requested, deadline)'),'<propagation-control>','exec'),scope)
        with patch.object(e,'graph',scope['graph']):
            with self.assertRaises(AssertionError): positive()
        def soname():
            with fixture(soname=TARGET) as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                with self.assertRaisesRegex(e.Refusal,'elf-provider-soname'): e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
        soname(); source=inspect.getsource(e.graph); old="supplied['soname'] == requested"; self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'True'),'<soname-control>','exec'),scope)
        with patch.object(e,'graph',scope['graph']):
            with self.assertRaises(AssertionError): soname()
        def content():
            with fixture() as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context); observations['0:6']['sha256']='f'*64
                with self.assertRaises((e.Refusal,v.Refusal)) as failure: e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
                self.assertIs(type(failure.exception),e.Refusal); self.assertEqual('elf-content-origin',str(failure.exception))
        content(); source=inspect.getsource(e.bound_metadata)
        old="found['sha256'] == node['sha256'] == origin['sha256'] and found['bytes'] == node['size'] == origin['size']"
        self.assertEqual(1,source.count(old)); scope=dict(e.__dict__)
        exec(compile(source.replace(old,'True'),'<content-origin-control>','exec'),scope)
        with patch.object(e,'bound_metadata',scope['bound_metadata']):
            with self.assertRaises(AssertionError): content()  # Later closure refusal cannot hide the removed earlier guard.


if __name__=='__main__':
    out=io.StringIO(); result=unittest.TextTestRunner(stream=out).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8','backslashreplace')[:6500])
    sys.exit(0 if result.wasSuccessful() else 1)
