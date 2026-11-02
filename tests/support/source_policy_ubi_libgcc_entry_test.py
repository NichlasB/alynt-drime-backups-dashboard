"""Fixed selection and source-context DATA tests, never candidate attestation."""
import copy
from contextlib import redirect_stdout
import importlib.util
import inspect
import io
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import textwrap
import unittest
from unittest.mock import patch

spec=importlib.util.spec_from_file_location('policy_fixtures',Path(__file__).with_name('source_policy_ubi_libgcc_policy_test.py'))
p=importlib.util.module_from_spec(spec); spec.loader.exec_module(p)
u,a,e=p.u,p.a,p.e
spec=importlib.util.spec_from_file_location('entry_fixtures',Path(__file__).with_name('source_policy_ubi_fixed_entry_test.py'))
q=importlib.util.module_from_spec(spec); spec.loader.exec_module(q)
ARG='--approved-libgcc-elf-static-prerequisite'


def material(owner):
    return dict(rootfs=dict(diff_ids=['sha256:'+pair[1] for pair in owner._layers])),{key:'d'*64 for key in u.BOUNDED_SOURCES}


class Contracts(unittest.TestCase):
    def test_explicit_selector_and_old_modes_remain_separate(self):
        self.assertEqual((True,True,True,True),u.libgcc_selection([ARG],'true','true','true','true'))
        for argument,flags,expected in [('--approved-static-prerequisite',('false','false','false'),(False,False,False,False)),
             ('--approved-alias-static-prerequisite',('true','false','false'),(True,False,False,False)),
             ('--approved-elf-static-prerequisite',('true','true','false'),(True,True,False,False)),
             ('--approved-bounded-elf-static-prerequisite',('true','true','true'),(True,True,True,False))]:
            self.assertEqual(expected,u.libgcc_selection([argument],*flags))
        for flags in [('true','true','true','false'),('false','true','true','true'),('true','false','true','true'),('true','true','false','true'),('true','true','true',True),('true','true','true','TRUE')]:
            with self.assertRaises(u.s.Refusal): u.libgcc_selection([ARG],*flags)
        with self.assertRaises(u.s.Refusal): u.libgcc_selection(['--approved-bounded-elf-static-prerequisite'],'true','true','true','true')

    def test_source_context_positive_and_each_binding_negative(self):
        with p.fixture() as (tree,owner,expected,_,_):
            config,frozen=material(owner); self.assertEqual(expected,u.libgcc_context(a,tree,owner,config,frozen))
        for change in ('source','config','head','run','snapshot','final','layers','owner'):
            with p.fixture() as (tree,owner,_,_,_):
                config,frozen=material(owner)
                if change=='source': frozen[u.BOUNDED_SOURCES[0]]='f'*64
                elif change=='config': config['rootfs']['diff_ids'][0]='sha256:'+'f'*64
                elif change=='head': os.environ['GITHUB_SHA']='f'*40
                elif change=='run': os.environ['GITHUB_RUN_ID']='999'
                elif change=='snapshot': tree._snapshot_hash=None
                elif change=='final': tree._final=False
                elif change=='layers': tree._committed=9
                else: owner._model=None
                with patch.object(tree,'inspect',side_effect=AssertionError('protected read')):
                    with self.assertRaises((u.s.Refusal,a.v.Refusal)): u.libgcc_context(a,tree,owner,config,frozen)

    def test_real_root_matcher_precedes_context_and_helper(self):
        with p.fixture() as (tree,owner,_,layers,_):
            config,frozen=material(owner)
            with patch.object(u,'libgcc_context',side_effect=AssertionError('context before pins')) as context,patch.object(a,'_owned_graph',side_effect=AssertionError('helper before pins')) as graph:
                with self.assertRaisesRegex(e.Refusal,'elf-selected-identity'): u.bounded_graph(a,e,tree,owner,layers,libgcc_alias_policy=True,config=config,frozen=frozen)
                context.assert_not_called(); graph.assert_not_called()

    def test_built_context_drives_actual_helper_and_requires_exact_usage(self):
        # Direct helper DATA positive only, not a root-pin-first genuine entry success.
        with p.fixture() as (tree,owner,_,layers,_):
            config,frozen=material(owner); context=u.libgcc_context(a,tree,owner,config,frozen)
            graph=a._owned_graph(tree,owner,layers,candidate_alias_context=context)
            self.assertTrue(u.libgcc_usage(graph['candidate_alias_policy'])); self.assertEqual('closed-model',graph['work_owner']['state'])
            self.assertEqual(1,graph['candidate_alias_policy']['matched_aliases'])

    def test_usage_and_new_success_are_not_just_selected_flags(self):
        usage=dict(format='ADBUCAPUSE1',profile='candidate-libgcc-alias-v1',matched_aliases=1,runtime_grant=False)
        report=q.report(); report['provider_alias_policy']='candidate-libgcc-alias-v1'; report['elf_graph']['candidate_alias_policy']=usage
        self.assertTrue(u.successful(report,True,True)); self.assertFalse(u.successful(report,False,True))
        for key,value in [('format','x'),('profile','strict'),('matched_aliases',0),('matched_aliases',2),('matched_aliases',True),('runtime_grant',True)]:
            bad=dict(usage,**{key:value}); self.assertFalse(u.libgcc_usage(bad)); failed=copy.deepcopy(report)
            failed['elf_graph']['candidate_alias_policy']=bad; self.assertFalse(u.successful(failed,True,True))
        for bad in (None,'ADBUCAPUSE1',dict(usage,extra=True)):
            self.assertFalse(u.libgcc_usage(bad))
        failed=copy.deepcopy(report); del failed['elf_graph']['candidate_alias_policy']; self.assertFalse(u.successful(failed,True,True))
        self.assertTrue(u.successful(q.report(),True))  # Original success DATA contract unchanged.

    def test_invalid_context_shapes_and_full_fixed_source_closure_refuse(self):
        with p.fixture() as (tree,owner,_,_,_):
            config,frozen=material(owner)
            for bad in ({},dict(frozen,extra='d'*64),dict(frozen,**{u.BOUNDED_SOURCES[0]:True})):
                with self.assertRaises(u.s.Refusal): u.libgcc_context(a,tree,owner,config,bad)
            for diffs in ([],tuple(config['rootfs']['diff_ids'])):
                with self.assertRaises(u.s.Refusal): u.libgcc_context(a,tree,owner,dict(rootfs=dict(diff_ids=diffs)),frozen)
        with patch.object(u,'Owned',side_effect=AssertionError('acquisition')):
            with self.assertRaises(u.s.Refusal): u.proof(Path('owned-not-created'),True,True,False,True)

    def test_root_and_real_job_binding_removal_controls_are_assertion_killed(self):
        def pins():
            with p.fixture() as (tree,owner,_,layers,_):
                config,frozen=material(owner)
                with self.assertRaisesRegex(e.Refusal,'elf-selected-identity'): u.bounded_graph(a,e,tree,owner,layers,libgcc_alias_policy=True,config=config,frozen=frozen)
        pins(); source=inspect.getsource(u.bounded_graph); self.assertEqual(1,source.count('elf.admit_selected(roles)'))
        scope=dict(u.__dict__); exec(compile(source.replace('elf.admit_selected(roles)','None'),'<mandatory-pin-control>','exec'),scope)
        with patch.object(u,'bounded_graph',scope['bounded_graph']):
            with self.assertRaises(AssertionError): pins()
        def head():
            with p.fixture() as (tree,owner,_,_,_):
                config,frozen=material(owner); os.environ['GITHUB_SHA']='f'*40; owner._run['head']='f'*40
                with self.assertRaises(u.s.Refusal): u.libgcc_context(a,tree,owner,config,frozen)
        head(); source=inspect.getsource(u.libgcc_context); old="head == os.environ['EXPECTED_SHA']"; self.assertEqual(1,source.count(old))
        scope=dict(u.__dict__); exec(compile(source.replace(old,'True'),'<exact-job-control>','exec'),scope)
        with patch.object(u,'libgcc_context',scope['libgcc_context']):
            with self.assertRaises(AssertionError): head()

    def test_usage_producer_and_validator_removal_controls_are_assertion_killed(self):
        def count():
            with p.fixture() as (tree,owner,context,_,observations):
                policy=e._CandidateAliasPolicy(tree,owner,context)
                result=e.graph(tree,observations,owner.deadline,candidate_alias_policy=policy)
                self.assertEqual(1,result['candidate_alias_policy']['matched_aliases'])
        count(); source=inspect.getsource(e.graph); old='candidate_aliases += 1'; self.assertEqual(1,source.count(old))
        scope=dict(e.__dict__); exec(compile(source.replace(old,'candidate_aliases += 0'),'<usage-producer-control>','exec'),scope)
        with patch.object(e,'graph',scope['graph']):
            with self.assertRaises(AssertionError): count()
        def success():
            report=q.report(); report['provider_alias_policy']='candidate-libgcc-alias-v1'
            self.assertFalse(u.successful(report,True,True))
        success()
        with patch.object(u,'libgcc_usage',return_value=True):
            with self.assertRaises(AssertionError): success()

    def test_workflow_independent_false_default_and_all_bash_dispatch_modes(self):
        workflow=(p.d.ROOT/'.github/workflows/source-policy-legacy-ubi-static.yml').read_text()
        section=workflow.split('      libgcc_alias_policy:',1)[1].split('permissions:',1)[0]; self.assertIn('default: false',section)
        body=workflow.split('      - name: One static prerequisite; no candidate execution\n        run: |\n',1)[1].split('      - name:',1)[0]
        body=textwrap.dedent(body)
        # Replace only fixed child invocations with inert markers: actual shell branching, never acquisition.
        import re
        body,count=re.subn(r'python3 -I -B scripts/ci/source-policy-legacy-ubi-static.py (--[a-z-]+)',r"printf '%s\\n' '\1'",body)
        self.assertEqual(5,count)
        bash=shutil.which('bash') if os.name!='nt' else str(Path(shutil.which('git')).parents[1]/'bin/bash.exe')
        import itertools
        expected={('false','false','false','false'):'--approved-static-prerequisite',('true','false','false','false'):'--approved-alias-static-prerequisite',
                  ('true','true','false','false'):'--approved-elf-static-prerequisite',('true','true','true','false'):'--approved-bounded-elf-static-prerequisite',
                  ('true','true','true','true'):ARG}
        for flags in itertools.product(('false','true'),repeat=4):
            env=dict(os.environ,**dict(zip(('ALIAS_POLICY','ELF_POLICY','BOUNDED_ACCESS_POLICY','LIBGCC_ALIAS_POLICY'),flags)))
            result=subprocess.run([bash,'-e','-c',body],env=env,capture_output=True,timeout=5)
            self.assertEqual(0 if flags in expected else 1,result.returncode,flags)
            if flags in expected: self.assertEqual(expected[flags],result.stdout.decode().strip())

    def test_context_failure_never_prevents_independent_finalization(self):
        # Finalizer call/first-cause proof only; physical Unix cleanup is separate.
        with p.fixture() as (tree,owner,_,_,_):
            config,frozen=material(owner); report=dict(status='refused',failure='libgcc-context')
            with patch.dict(os.environ,dict(GITHUB_SHA='f'*40)):
                with self.assertRaises(u.s.Refusal) as failure: u.libgcc_context(a,tree,owner,config,frozen)
            owner.fail(failure.exception)
            with patch.object(u.acquire,'file_hash',return_value='d'*64),patch.object(u,'cleanup',return_value=13) as cleanup,redirect_stdout(io.StringIO()):
                u.finish_report(report,Path('owned-not-created'),frozen,owner)
            cleanup.assert_called_once_with(Path('owned-not-created')); self.assertEqual('libgcc-context',report['failure'])
            self.assertEqual('complete',report['cleanup']); self.assertEqual('poisoned',report['work_owner']['state'])


if __name__=='__main__':
    out=io.StringIO(); result=unittest.TextTestRunner(stream=out).run(unittest.defaultTestLoader.loadTestsFromTestCase(Contracts))
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)),flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8','backslashreplace')[:6500])
    sys.exit(0 if result.wasSuccessful() else 1)
