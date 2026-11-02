"""Actual dormant components; synthetic archives only, never target qualification."""
import copy
from contextlib import contextmanager
import hashlib
import importlib.util
import io
import json
from pathlib import Path
import socket
import struct
import sys
import tempfile
import time
import unittest
from unittest.mock import patch

spec=importlib.util.spec_from_file_location('string_fixtures',Path(__file__).with_name('source_policy_ubi_string_access_test.py'))
f=importlib.util.module_from_spec(spec); spec.loader.exec_module(f)
a,v,e,x=f.t.a,f.t.v,f.e,f.t.t


def large_server():
    prefix=f.t.binary(['liba.so.1'],interpreter=e.INTERPRETER)
    data=bytearray(4096+1084894); data[:len(prefix)]=prefix
    struct.pack_into('<Q',data,280,1084894)
    for offset in (152,160): struct.pack_into('<Q',data,offset,len(data))
    return data


def rows(large=True):
    return x.Contracts().rows()+[x.member('usr/sbin',x.tarfile.DIRTYPE),
        x.member(e.SERVER,data=large_server() if large else f.t.binary(['liba.so.1'],interpreter=e.INTERPRETER)),
        x.member(e.LOADER,data=f.t.binary(soname='ld-linux-x86-64.so.2')),
        x.member('usr/lib64/liba.so.1',data=f.t.binary(soname='liba.so.1'))]


@contextmanager
def fixture(extra=(), first=None):
    blobs=[x.fixture(r) for r in ([rows() if first is None else first]+list(extra))]
    identity=dict(manifest=a.PIN,config=a.CONFIG,source='a'*64)
    layers=[(sha,diff[7:]) for _,sha,diff in blobs]; deadline=time.monotonic()+30
    owner=v._FixedWorkAuthority(identity,layers,dict(head='b'*40,run='c'*32,profile='bounded-strings-v1'),deadline)
    budget=dict(entries=0,expanded=0,deadline=deadline)
    with tempfile.TemporaryDirectory() as temp:
        paths=[]
        for i,(blob,_,_) in enumerate(blobs):
            path=Path(temp)/('owned-'+str(i)+'.blob'); path.write_bytes(blob); paths.append(path)
        def scan(i=0,**changes):
            blob,sha,diff=blobs[i]
            args=dict(path=paths[i],compressed=sha,size=len(blob),diff_id=diff,budget=budget,elf_policy=True,work_owner=owner,layer_ordinal=i)
            args.update(changes); return a.collect(**args)
        yield owner,budget,scan,blobs,identity,layers


def context(owner,body,entry=0,archive=1):
    compressed,diff=owner._layers[0]
    return dict(layer=0,compressed=compressed,diff_id=diff,entry=entry,archive=archive,
        path_sha256=hashlib.sha256(b'usr/lib64/liba.so.1').hexdigest(),sha256=hashlib.sha256(body).hexdigest(),bytes=len(body))


class Contracts(unittest.TestCase):
    def test_bootstrap_actual_parser_model_graph_and_default_refusal(self):
        with fixture() as (owner,budget,scan,blobs,identity,layers):
            first=scan(); used=owner.snapshot()['work_used']; self.assertGreater(used,0)
            self.assertIsNone(owner._model)
            tree=a._owned_model(owner,first); self.assertEqual(used,owner.snapshot()['work_used'])
            tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
            result=a._owned_graph(tree,owner,[first])
            self.assertEqual(3,result['objects']); self.assertEqual(2,result['edges']); self.assertFalse(result['runtime_grant'])
            state=owner.snapshot(); self.assertEqual('closed-model',state['state'])
            self.assertEqual(state['work_used'],state['parser_work']+state['model_work']+2*state['settled'])
        with fixture() as (_,_,scan,*_):
            with self.assertRaisesRegex(a.s.Refusal,'elf-string-table'): scan(work_owner=None,layer_ordinal=None)

    def test_exclusive_call_replay_and_foreign_settlement_poison(self):
        for action in ('second','foreign','context','twice','cost-grant','body-sha','body-size','body-work'):
            with fixture() as (owner,_,_,_,_,_):
                sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
                body=f.t.binary(soname='liba.so.1'); ctx=context(owner,body); token,grant=owner.issue(ctx)
                if action=='second':
                    with self.assertRaises(v.Refusal): owner.issue(ctx)
                else:
                    owner.invoke(token,ctx); result=e.parse(body,bounded_strings=True,remaining_work=grant,deadline=owner.deadline)
                    meta=result['metadata']; summary={k:meta[k] for k in ('sha256','bytes','work')}
                    if action=='cost-grant': result['cost']['granted']-=1
                    elif action=='body-sha': summary['sha256']='e'*64
                    elif action=='body-size': summary['bytes']+=1
                    elif action=='body-work': summary['work']+=1
                    other=object() if action=='foreign' else token; supplied=dict(ctx,entry=9) if action=='context' else ctx
                    if action=='twice':
                        owner.settle(token,ctx,result['cost'],summary,a._metadata_digest(meta),True)
                    with self.assertRaises(v.Refusal): owner.settle(other,supplied,result['cost'],summary,a._metadata_digest(meta),True)
                self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_issue_reserves_settlement_and_unknown_cost_burns(self):
        with fixture() as (owner,_,_,_,_,_):
            sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
            token,grant=owner.issue(context(owner,b'bad')); self.assertEqual(v.WORK_LIMIT-2,grant)
            owner.poison(); state=owner.snapshot()
            self.assertEqual(v.WORK_LIMIT,state['work_used']); self.assertEqual(grant+1,state['burned_work'])
            owner.poison(); self.assertEqual(state,owner.snapshot())
        with fixture() as (owner,_,_,_,_,_):
            owner._used=v.WORK_LIMIT-1
            sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
            with self.assertRaisesRegex(v.Refusal,'work-owner-budget'): owner.issue(context(owner,b'bad'))
            self.assertEqual(0,owner.snapshot()['grants'])
        body=f.t.binary(soname='liba.so.1'); measured=e.parse(body,bounded_strings=True,remaining_work=v.WORK_LIMIT)['cost']['used']
        for short in (0,1):
            with fixture() as (owner,budget,_,_,_,_):
                sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline); budget['entries']=1
                owner._used=v.WORK_LIMIT-measured-2+short
                row=dict(path='usr/lib64/liba.so.1',sha256=hashlib.sha256(body).hexdigest(),size=len(body))
                if short:
                    with self.assertRaisesRegex(e.Refusal,'elf-string-access-work'): a._parse_owned(body,row,0,budget,owner,0,e)
                    self.assertEqual('poisoned',owner.snapshot()['state'])
                else:
                    meta,_=a._parse_owned(body,row,0,budget,owner,0,e)
                    self.assertEqual(measured,meta['work']); self.assertEqual(v.WORK_LIMIT,owner.snapshot()['work_used'])
                self.assertLessEqual(owner.snapshot()['work_used'],v.WORK_LIMIT)
        with fixture() as (owner,budget,_,_,_,_):
            sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline); budget['entries']=1; owner._used=v.WORK_LIMIT-1
            row=dict(path='usr/lib64/liba.so.1',sha256=hashlib.sha256(body).hexdigest(),size=len(body))
            with patch.object(e,'parse',wraps=e.parse) as actual:
                with self.assertRaisesRegex(v.Refusal,'work-owner-budget'): a._parse_owned(body,row,0,budget,owner,0,e)
                actual.assert_not_called()

    def test_actual_parser_failure_keeps_identity_and_charges_known_work(self):
        with fixture() as (owner,budget,_,_,_,_):
            sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
            body=bytearray(b'\x7fELFbad'); row=dict(path='usr/lib64/liba.so.1',sha256=hashlib.sha256(body).hexdigest(),size=len(body))
            budget['entries']=1
            original=e.parse; caught=[]
            def observed(*args,**kw):
                try: return original(*args,**kw)
                except Exception as error: caught.append(error); raise
            with patch.object(e,'parse',side_effect=observed):
                with self.assertRaisesRegex(e.Refusal,'elf-header') as failure: a._parse_owned(body,row,0,budget,owner,0,e)
            self.assertIs(caught[0],failure.exception)
            state=owner.snapshot(); self.assertEqual('poisoned',state['state']); self.assertGreater(state['parser_work'],0)
            self.assertEqual(0,state['burned_work'])
            self.assertEqual(state,failure.exception.fixed_owner_facts)
        for broken in ('missing','validator'):
            with fixture() as (owner,budget,_,_,_,_):
                sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline); budget['entries']=1
                body=bytearray(b'\x7fELFbad'); row=dict(path='usr/lib64/liba.so.1',sha256=hashlib.sha256(body).hexdigest(),size=len(body)); caught=[]
                original=e.parse
                def damaged(*args,**kw):
                    try: return original(*args,**kw)
                    except Exception as error:
                        caught.append(error)
                        if broken=='missing': error.string_access_facts=None
                        raise
                with patch.object(e,'parse',side_effect=damaged),patch.object(e,'access_receipt',side_effect=RuntimeError('secondary')) if broken=='validator' else patch.object(e,'access_receipt',wraps=e.access_receipt):
                    with self.assertRaises(e.Refusal) as failure: a._parse_owned(body,row,0,budget,owner,0,e)
                self.assertIs(caught[0],failure.exception); self.assertEqual(v.WORK_LIMIT,owner.snapshot()['work_used'])
                self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_bad_complete_hash_never_returns_or_refunds_staging(self):
        with fixture() as (owner,_,scan,_,_,_):
            with self.assertRaisesRegex(v.Refusal,'work-owner-layer'): scan(diff_id='sha256:'+'0'*64)
            self.assertEqual(0,owner.snapshot()['verified_layers']); self.assertEqual('poisoned',owner.snapshot()['state'])
        with fixture() as (owner,_,scan,_,_,_):
            original=a.s.require
            def fail(ok,code): original(False if code=='diff-id-mismatch' else ok,code)
            with patch.object(a.s,'require',side_effect=fail):
                with self.assertRaisesRegex(a.s.Refusal,'diff-id-mismatch'): scan()
            self.assertGreater(owner.snapshot()['parser_work'],0); self.assertEqual(0,owner.snapshot()['verified_layers'])
            with patch.object(Path,'open',side_effect=AssertionError('poisoned IO')):
                with self.assertRaises(v.Refusal): scan()

    def test_hardlink_retained_origin_survives_later_layer_without_refund(self):
        first=rows(); first.append(x.member('usr/lib64/liborigin.so.1',data=f.t.binary(soname='liba.so.1')))
        later=[x.member('usr/lib64/liba.so.1',x.tarfile.LNKTYPE,'usr/lib64/liborigin.so.1')]
        with fixture([later],first) as (owner,_,scan,_,_,_):
            zero=scan(); tree=a._owned_model(owner,zero); tree.apply(dict(zero['metadata'],ordinal=0),zero['records'],owner.now())
            used=owner.snapshot()['work_used']; one=scan(1); self.assertEqual(used,owner.snapshot()['work_used'])
            tree.apply(dict(one['metadata'],ordinal=1),one['records'],owner.now()); tree.finalize(owner.now())
            result=a._owned_graph(tree,owner,[zero,one]); self.assertEqual(3,result['objects'])
            self.assertEqual(4,owner.snapshot()['records'])
        for replacement in ([x.member('usr/lib64/liba.so.1',data=f.t.binary(soname='liba.so.1',needed=[]))],
                            [x.member('usr/lib64/.wh.liba.so.1')],
                            [x.member('usr/lib64/.wh..wh..opq')]):
            with fixture([replacement]) as (owner,_,scan,_,_,_):
                zero=scan(); tree=a._owned_model(owner,zero); tree.apply(dict(zero['metadata'],ordinal=0),zero['records'],owner.now())
                before=owner.snapshot(); one=scan(1); tree.apply(dict(one['metadata'],ordinal=1),one['records'],owner.now()); tree.finalize(owner.now())
                if replacement[0][0].name.endswith('/liba.so.1'):
                    result=a._owned_graph(tree,owner,[zero,one]); self.assertEqual(3,result['objects'])
                    self.assertGreater(owner.snapshot()['parser_work'],before['parser_work']); self.assertEqual(4,owner.snapshot()['records'])
                else:
                    with self.assertRaises(v.Refusal): a._owned_graph(tree,owner,[zero,one])
                    self.assertGreaterEqual(owner.snapshot()['work_used'],before['work_used'])

    def test_origin_and_binding_tampering_cannot_become_graph_evidence(self):
        for action in ('meta','binding','missing','size','index','snapshot-origin'):
            with fixture() as (owner,_,scan,_,_,_):
                first=scan(); tree=a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
                first=copy.deepcopy(first); index=next(iter(first['elf']))
                if action=='meta': first['elf'][index]['sha256']='d'*64
                elif action=='binding': first['elf_bindings'][index]['sequence']+=1
                elif action=='missing': del first['elf_bindings'][index]
                elif action=='size': first['elf_bindings'][index]['context']['bytes']+=1
                elif action=='index': first['elf_bindings'][index]['context']['entry']+=1
                else:
                    node=tree._nodes[e.SERVER]
                    node['content_origin']=dict(path='usr/lib64/libdifferent.so.1',layer=node['layer'],entry=node['entry'],sha256=node['sha256'],size=node['size'])
                    node['hardlinks']=[]
                with self.assertRaises(v.Refusal): a._owned_graph(tree,owner,[first])
                self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_model_failure_double_charge_and_second_attachment_are_terminal(self):
        for action in ('charge','apply','second','outstanding'):
            with fixture() as (owner,_,scan,_,identity,layers):
                first=scan(); tree=a._owned_model(owner,first)
                with self.assertRaises(v.Refusal):
                    if action=='charge': tree.charge(first['elf_work'],owner.now())
                    elif action=='apply': tree.apply(dict(first['metadata'],ordinal=9),first['records'],owner.now())
                    elif action=='second': v.VirtualClosure(identity,layers,owner._aliases,hardlinks=True,work_owner=owner)
                    else: owner._active=0; owner.issue(context(owner,b'bad',entry=60000,archive=60000)); tree.finalize(owner.now())
                self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_typed_identity_deadline_and_exact_loaded_type(self):
        with fixture() as (owner,budget,scan,_,identity,layers):
            for deadline in (None,True,float('inf'),time.monotonic()+301):
                with self.assertRaises(v.Refusal): v._FixedWorkAuthority(identity,layers,owner._run,deadline)
            with self.assertRaises(a.s.Refusal): scan(work_owner=object())
        with fixture() as (owner,_,_,_,identity,layers):
            with self.assertRaises(v.Refusal): v.VirtualClosure(identity,layers,work_owner=object())
            for field,value in (('head','d'*39),('run',True),('profile','strict'),('profile',object())):
                with self.assertRaises(v.Refusal): v._FixedWorkAuthority(identity,layers,dict(owner._run,**{field:value}),owner.deadline)
            foreign=a.load('source_policy_virtual_closure.py','independent_owner')
            lookalike=foreign._FixedWorkAuthority(identity,layers,owner._run,owner.deadline)
            with patch.object(Path,'open',side_effect=AssertionError('foreign IO')):
                with self.assertRaisesRegex(a.s.Refusal,'work-owner-type'):
                    a.collect(None,layers[0][0],1,'sha256:'+layers[0][1],dict(deadline=owner.deadline),True,work_owner=lookalike,layer_ordinal=0)

    def test_deadline_cannot_be_restarted_or_expired_state_resumed(self):
        with fixture() as (owner,_,_,_,_,_):
            with self.assertRaises(AttributeError): owner.deadline=time.monotonic()+300
        with fixture() as (owner,_,_,_,_,_):
            with patch.object(v.time,'monotonic',return_value=owner.deadline+1):
                with self.assertRaisesRegex(v.Refusal,'work-owner-deadline'): owner.now()
            self.assertEqual('poisoned',owner.snapshot()['state'])
        with fixture() as (owner,_,scan,_,_,_):
            with self.assertRaisesRegex(a.s.Refusal,'work-owner-mode'): scan(budget=dict(entries=0,expanded=0,deadline=owner.deadline+1))

    def test_envelope_bytes_remain_inside_original_observation_cap(self):
        with fixture() as (owner,budget,scan,_,_,_):
            first=scan(); encoded=sum(len(json.dumps(dict(metadata=first['elf'][i],binding=b)).encode('ascii')) for i,b in first['elf_bindings'].items())
            self.assertEqual(encoded,budget['elf_metadata']); self.assertEqual(encoded,owner.snapshot()['observation_bytes'])
        with fixture() as (owner,_,scan,_,_,_):
            owner._bytes=2*a.s.MIB
            with self.assertRaisesRegex(v.Refusal,'work-owner-observation-bound'): scan()
            self.assertEqual(1,owner.snapshot()['settled']); self.assertGreater(owner.snapshot()['parser_work'],0)
        many=rows()+[x.member('usr/lib64/libextra'+str(i)+'.so.1',data=f.t.binary(soname='libextra'+str(i)+'.so.1')) for i in range(254)]
        with fixture(first=many) as (owner,_,scan,_,_,_):
            with self.assertRaisesRegex(a.s.Refusal,'elf-object-bound'): scan()
            self.assertEqual(256,owner.snapshot()['records']); self.assertEqual('poisoned',owner.snapshot()['state'])

    def test_predebit_settlement_layer_and_origin_semantic_controls_are_killed(self):
        with fixture() as (owner,_,scan,_,_,_):
            first=scan(); tree=a._owned_model(owner,first)
            before=owner.snapshot()['work_used']; tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now())
            self.assertGreater(owner.snapshot()['work_used'],before)
        # Assertion-killed controls: each witness passes normally and must fail when its
        # actual source-owned guard is bypassed. No production guard is weakened on disk.
        def predebit():
            with fixture() as (owner,_,scan,_,_,_):
                first=scan(); tree=a._owned_model(owner,first); owner._used=v.WORK_LIMIT
                with self.assertRaisesRegex(v.Refusal,'work-owner-budget'):
                    tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now())
                self.assertEqual({},tree._nodes); self.assertEqual(0,tree._committed)
        def origin():
            with fixture() as (owner,_,scan,_,_,_):
                first=scan(); tree=a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
                i=next(iter(first['elf_bindings'])); first['elf_bindings'][i]['context']['path_sha256']='d'*64
                with self.assertRaisesRegex(v.Refusal,'work-owner-binding'): a._owned_graph(tree,owner,[first])
        def layer():
            with fixture([[]]) as (owner,_,scan,_,_,_):
                first=scan(); tree=a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now())
                sha,diff=owner._layers[1]
                with self.assertRaisesRegex(v.Refusal,'work-owner-layer-unverified'):
                    tree.apply(dict(ordinal=1,compressed=sha,diff_id=diff,compressed_verified=True,diff_verified=True),[],owner.now())
                self.assertEqual(1,tree._committed)
        def settlement():
            with fixture() as (owner,_,_,_,_,_):
                sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
                body=f.t.binary(soname='liba.so.1'); ctx=context(owner,body); token,grant=owner.issue(ctx); owner.invoke(token,ctx)
                result=e.parse(body,bounded_strings=True,remaining_work=grant,deadline=owner.deadline)
                result['cost']['runtime_grant']=True
                with self.assertRaisesRegex(v.Refusal,'work-owner-cost'):
                    owner.settle(token,ctx,result['cost'],{k:result['metadata'][k] for k in ('sha256','bytes','work')},a._metadata_digest(result['metadata']),True)
        def exclusive():
            with fixture() as (owner,_,_,_,_,_):
                sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
                ctx=context(owner,b'bad'); token,_=owner.issue(ctx); owner.invoke(token,ctx)
                with self.assertRaisesRegex(v.Refusal,'work-owner-invocation'): owner.invoke(token,ctx)
        def matching():
            with fixture() as (owner,_,_,_,_,_):
                sha,diff=owner._layers[0]; owner.begin_layer(0,sha,diff,owner.deadline)
                body=f.t.binary(soname='liba.so.1'); ctx=context(owner,body); token,grant=owner.issue(ctx); owner.invoke(token,ctx)
                result=e.parse(body,bounded_strings=True,remaining_work=grant,deadline=owner.deadline)
                with self.assertRaisesRegex(v.Refusal,'work-owner-ticket'):
                    owner.settle(object(),ctx,result['cost'],{k:result['metadata'][k] for k in ('sha256','bytes','work')},a._metadata_digest(result['metadata']),True)
        def attachment():
            with fixture() as (owner,_,scan,_,identity,layers):
                first=scan(); a._owned_model(owner,first)
                with self.assertRaisesRegex(v.Refusal,'work-owner-model'): a._owned_model(owner,first)
        def final_origin():
            with fixture() as (owner,_,scan,_,_,_):
                first=scan(); tree=a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
                node=tree._nodes[e.SERVER]
                node['content_origin']=dict(path='usr/lib64/libdifferent.so.1',layer=node['layer'],entry=node['entry'],sha256=node['sha256'],size=node['size'])
                node['hardlinks']=[]
                with self.assertRaisesRegex(v.Refusal,'work-owner-final-origin'): a._owned_graph(tree,owner,[first])
        def no_replay():
            with fixture() as (owner,_,scan,_,_,_):
                first=scan(); tree=a._owned_model(owner,first); before=owner.snapshot()['work_used']
                with self.assertRaisesRegex(v.Refusal,'work-owner-no-replay'): tree.charge(first['elf_work'],owner.now())
                self.assertEqual(before,owner.snapshot()['work_used'])
        controls=[('debit_model',predebit),('check_observation',origin),('admit_apply',layer),('_cost',settlement),
                  ('invoke',exclusive),('_match',matching),('bind_model',attachment),('check_node',final_origin),('charge-replay',no_replay)]
        for method,witness in controls:
            witness()
            if method=='charge-replay':
                original=v.require
                def bypass_replay(ok,reason): return original(True if reason=='work-owner-no-replay' else ok,reason)
                with patch.object(v,'require',side_effect=bypass_replay):
                    with self.assertRaises(AssertionError,msg='Control survived: '+method): witness()
                continue
            bypass = dict(side_effect=lambda value: dict(value)) if method=='_cost' else dict(return_value=None)
            with patch.object(v._FixedWorkAuthority,method,**bypass):
                with self.assertRaises(AssertionError,msg='Control survived: '+method): witness()

    def test_no_io_or_handles_in_authority_and_in_memory_invocation(self):
        import os
        import subprocess
        with fixture() as (owner,budget,_,_,_,_):
            sha,diff=owner._layers[0]; body=f.t.binary(soname='liba.so.1')
            budget['entries']=1
            row=dict(path='usr/lib64/liba.so.1',sha256=hashlib.sha256(body).hexdigest(),size=len(body))
            with patch('builtins.open',side_effect=AssertionError('IO')),patch.object(socket,'socket',side_effect=AssertionError('network')),patch.object(subprocess,'Popen',side_effect=AssertionError('process')),patch.object(os,'stat',side_effect=AssertionError('host stat')):
                owner.begin_layer(0,sha,diff,owner.deadline)
                with patch.object(e,'parse',wraps=e.parse) as actual:
                    meta,binding=a._parse_owned(body,row,0,budget,owner,0,e)
                    actual.assert_called_once()
                    args,kwargs=actual.call_args
                    self.assertEqual((body,owner.deadline),args)
                    self.assertEqual({'bounded_strings','remaining_work'},set(kwargs)); self.assertIs(True,kwargs['bounded_strings'])
                    self.assertIs(int,type(kwargs['remaining_work']))
                self.assertEqual(meta['sha256'],row['sha256']); self.assertFalse(binding['cost']['runtime_grant'])
                self.assertNotIn('handle',owner.snapshot())

    def test_generic_contract_is_not_real_candidate_pin_or_cleanup_proof(self):
        with fixture() as (owner,_,scan,_,_,_):
            first=scan(); tree=a._owned_model(owner,first); tree.apply(dict(first['metadata'],ordinal=0),first['records'],owner.now()); tree.finalize(owner.now())
            roles={key:tree.resolve(key,owner.now()) for key in (e.SERVER,e.INTERPRETER[1:])}
            with self.assertRaisesRegex(e.Refusal,'elf-selected-identity'): e.admit_selected(roles)
            result=a._owned_graph(tree,owner,[first]); self.assertTrue(result['model_only']); self.assertFalse(result['runtime_grant'])


if __name__=='__main__':
    out=io.StringIO(); result=unittest.TextTestRunner(stream=out).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8','backslashreplace')[:6000])
    sys.exit(0 if result.wasSuccessful() else 1)
