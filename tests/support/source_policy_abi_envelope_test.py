"""Source-format-derived synthetic envelopes only; never a native transcript."""
import copy
import importlib.util
import inspect
import io
import json
from pathlib import Path
import sys
import textwrap
import unittest
from unittest.mock import patch

ROOT=Path(__file__).resolve().parents[2]
spec=importlib.util.spec_from_file_location('startup_fixtures',ROOT/'tests/support/source_policy_abi_startup_test.py')
f=importlib.util.module_from_spec(spec);spec.loader.exec_module(f)
o=f.o;c=o.c
PREFIX=b'Error response from daemon: '
CASES={
    'start-paused':b'cannot start a paused container, try unpause instead',
    'start-removing':b'container is marked for removal and cannot be started',
    'attach-paused':b'container '+f.CID.encode()+b' is paused, unpause the container before attach',
    'attach-restarting':b'container '+f.CID.encode()+b' is restarting, wait until the container is running',
    'api-empty-message-500':b'API returned a 500 (Internal Server Error) but provided no error-message',
}
RAW=PREFIX+CASES['start-paused']+b'\n'


def report(raw=RAW,original=None,expected=None):
    return c.startup_source_envelope(f.packet(raw) if original is None else original,
                                     f.descriptor() if expected is None else expected,raw)


class Tests(unittest.TestCase):
    def test_all_five_exact_source_recipes_are_useful_without_rewriting_v1(self):
        for family,body in CASES.items():
            for ending in (b'',b'\n',b'\r\n'):
                raw=PREFIX+body+ending;original=f.packet(raw);saved=copy.deepcopy(original)
                result=report(raw,original)
                self.assertEqual('matched',result['status']);self.assertEqual(family,result['family'])
                self.assertEqual(dict(status='unclassified',layers=['docker-daemon'],topics=['unknown']),original['analysis'])
                c.validate_startup_source(result,original,f.descriptor())
                self.assertEqual(saved,original);self.assertFalse(result['authority']);self.assertFalse(result['qualification'])
                self.assertLessEqual(len(c.encode(result)),512)

    def test_whole_envelope_and_owned_identifier_are_not_substring_matches(self):
        raw=PREFIX+CASES['attach-paused']
        for bad in (raw+b' suffix',b'prefix '+raw,raw.upper(),raw.replace(b': ',b':  ',1),
                    raw.replace(f.CID.encode(),b'e'*64),raw.replace(f.CID.encode(),b'd'*12),
                    raw.replace(f.CID.encode(),b'"'+f.CID.encode()+b'"'),raw+b'\n\n',raw+b'\r'):
            result=report(bad)
            self.assertTrue(result is None or result['status']!='matched')
        for body in CASES.values():
            self.assertNotEqual('matched',report(PREFIX+body+b' TOKEN=private')['status'])

    def test_unknown_secret_text_is_unmatched_never_echoed(self):
        raw=PREFIX+b'https://private/path TOKEN=never-echo "opaque private"\n'
        result=report(raw)
        self.assertEqual('unmatched',result['status']);self.assertIsNone(result['family'])
        self.assertNotIn('private',c.encode(result).decode());self.assertNotIn('never-echo',c.encode(result).decode())
        original=f.packet(RAW);value=report(None,original)
        self.assertEqual('unavailable',value['status']);self.assertIsNone(value['family'])

    def test_hash_length_and_operation_correlation_precede_matching(self):
        old=RAW.replace(b'paused',b'qaused');original=f.packet(old)
        for raw in (RAW,RAW+b'x'):
            with patch.object(c,'_source_envelope_scan',side_effect=AssertionError('premature matcher')) as matcher:
                with self.assertRaises(c.Refusal):report(raw,original)
                matcher.assert_not_called()
        with self.assertRaises(c.Refusal):report(RAW,expected=dict(f.descriptor(),cid='e'*64))
        with self.assertRaises(f.c.Refusal):f.packet(RAW,argv=f.ARGV[:-1]+['e'*64])
        with self.assertRaises(f.c.Refusal):f.packet(RAW,flags=dict(f.FLAGS,limit=8192))

    def test_partial_recognized_and_malformed_observations_do_not_reach_matcher(self):
        for original in (f.packet(),f.packet(RAW,eof=(True,False),kind='command-error'),
                         f.packet(RAW,kind='timeout',returncode=None),f.packet(b'\x00')):
            with patch.object(c,'_source_envelope_scan',side_effect=AssertionError('ineligible matcher')) as matcher:
                self.assertIsNone(report(RAW,original));matcher.assert_not_called()
        class Unknown:
            def __eq__(self,other):raise AssertionError('magic comparison')
            def __str__(self):raise AssertionError('magic string')
        with self.assertRaises(c.Refusal):report(Unknown(),f.packet(RAW))
        for bad in (b'\x00',b'\xff',b'x'*4097,RAW+b'\n\n',b'x'*1025):
            status,family,work=c._source_envelope_scan(bad,f.CID)
            self.assertEqual('invalid',status);self.assertIsNone(family)

    def test_closed_schema_rejects_cross_receipts_catalogs_and_types(self):
        original=f.packet(RAW);good=report(RAW,original)
        for changes in (dict(extra='private'),dict(authority=True),dict(qualification=0),dict(format='other'),
                        dict(operation_sha='a'*64),dict(stderr_sha='b'*64),dict(catalog_sha='c'*64),
                        dict(status='unmatched'),dict(family='unknown'),dict(status=True)):
            with self.assertRaises(c.Refusal):c.validate_startup_source(dict(good,**changes),original,f.descriptor())
        foreign=f.packet(RAW,binding=c.startup_binding(f.FRAME,f.IMAGE,'e'*64,'amd64',False),argv=f.ARGV[:-1]+['e'*64])
        with self.assertRaises(c.Refusal):c.validate_startup_source(good,foreign,foreign['binding'])

    def test_combined_scan_work_and_serialization_stay_in_original_limits(self):
        samples=[RAW,PREFIX+b'a '*490,PREFIX+b'x'*950,PREFIX+b'_'*950,b'\n'.join([b'x'*256]*15),b'x'*4096]
        samples.extend(PREFIX+body+b'\r\n' for body in CASES.values())
        samples.extend(PREFIX+(atom*((length+len(atom)-1)//len(atom)))[:length]
                       for atom in (b'a ',b'1 ',b'_ ',b'- ',b'a:',b'a\t',b'"x" ',b'/x ')
                       for length in range(0,1025,7))
        for raw in samples:
            _,work=c._startup_scan(raw);_,_,added=c._source_envelope_scan(raw,f.CID)
            self.assertLessEqual(work+added,16*len(raw)+1024)
        source=report();original=f.packet(RAW)
        for state in ('created','restarting','running','removing','paused','exited','dead'):
            row=f.cleanup_row(state);row['State']['Running']=state in ('running','paused')
            projection=c.startup_cleanup(original,f.FRAME,f.IMAGE,f.CID,row)
            self.assertLessEqual(len(c.encode(projection)),256)
            changed=dict(original,cleanup=projection);c.validate_startup_source(source,changed,f.descriptor())
            self.assertLessEqual(len(c.encode(changed))+len(c.encode(source)),4096)

    def test_actual_owner_rejects_success_status_stderr_and_captures_once(self):
        value=f.owner()
        with f.FakeIO(err=RAW,rc=0),patch.object(c,'classify_startup',wraps=c.classify_startup) as broad:
            with self.assertRaisesRegex(c.Refusal,'bounded-command-failed'):value.run(f.ARGV,**f.FLAGS,startup=f.descriptor())
            self.assertEqual(1,broad.call_count)
        self.assertEqual(f.packet(RAW,returncode=0),value.startup_failure)
        self.assertEqual('start-paused',value.startup_source_envelope['family'])
        saved=copy.deepcopy(value.startup_source_envelope)
        with f.FakeIO(err=PREFIX+CASES['start-removing']+b'\n'):
            with self.assertRaises(c.Refusal):value.run(f.ARGV,**f.FLAGS,startup=f.descriptor())
        self.assertEqual(saved,value.startup_source_envelope)
        with f.FakeIO(err=b'',out=b'7\n',rc=0):
            healthy=f.owner();self.assertEqual(b'7\n',healthy.run(f.ARGV,**f.FLAGS,startup=f.descriptor()))
            self.assertIsNone(getattr(healthy,'startup_source_envelope',None))

    def test_source_fault_and_quota_exhaustion_preserve_original_evidence(self):
        value=f.owner()
        with f.FakeIO(err=RAW),patch.object(c,'startup_source_envelope',side_effect=RuntimeError('private source fault')):
            with self.assertRaises(c.Refusal):value.run(f.ARGV,**f.FLAGS,startup=f.descriptor())
        self.assertEqual(f.packet(RAW),value.startup_failure)
        self.assertTrue(value.startup_source_unavailable)
        self.assertIsNone(getattr(value,'startup_source_envelope',None))
        value.capture_startup_source(value.startup_failure,f.descriptor(),RAW)
        self.assertIsNone(getattr(value,'startup_source_envelope',None))
        for quota in ('control','observed'):
            value=f.owner();value.startup_failure=f.packet(RAW)
            setattr(value,quota,524288 if quota=='control' else 2097152)
            saved=copy.deepcopy(value.startup_failure)
            value.capture_startup_source(value.startup_failure,f.descriptor(),RAW)
            self.assertEqual(saved,value.startup_failure);self.assertTrue(value.startup_source_unavailable)
            self.assertIsNone(getattr(value,'startup_source_envelope',None))

    def test_outer_failure_and_actual_cleanup_keep_stable_source_binding(self):
        value=f.owner();value.prerequisites=lambda:value.run(f.ARGV,**f.FLAGS,startup=f.descriptor())
        def cleanup():
            value.capture_startup_cleanup(f.CID,f.cleanup_row('created'))
            return dict(containers=0,images=0,stage_absent=True)
        value.cleanup=cleanup;out=io.StringIO()
        with f.FakeIO(err=RAW),patch.object(o,'Owner',return_value=value),patch.object(o.sys,'argv',['owned','--approved-owned-abi-proof']),patch.object(o.sys,'stdout',out):
            self.assertEqual(1,o.main())
        result=json.loads(out.getvalue());failure=result['failure']
        c.validate_startup_source(failure['startup_source_envelope'],failure['startup_failure'],f.descriptor())
        self.assertIsNone(failure['startup_failure']['cleanup']['exit_code']);self.assertFalse(result['qualification'])
        original=f.packet;original_owner=f.owner;source=report()
        def owned():
            fixture=original_owner();fixture.startup_source_envelope=copy.deepcopy(source)
            return fixture
        with patch.object(f,'packet',side_effect=lambda:original(RAW)),patch.object(f,'owner',side_effect=owned):
            cleaned=f.Tests().cleanup_fixture()
        self.assertEqual('running',cleaned.startup_failure['cleanup']['state'])
        self.assertEqual(source,cleaned.startup_source_envelope)
        c.validate_startup_source(source,cleaned.startup_failure,f.descriptor())

    def test_frozen_catalog_is_not_a_caller_selector_and_conflicts_are_ambiguous(self):
        self.assertEqual(5,len(c.SOURCE_ENVELOPES));self.assertEqual(5,len(set(row[0] for row in c.SOURCE_ENVELOPES)))
        self.assertEqual(c.sha(c.encode(c.SOURCE_ENVELOPES)),c.SOURCE_ENVELOPE_SHA)
        self.assertEqual(('packet','expected','raw'),tuple(inspect.signature(c.startup_source_envelope).parameters))
        templates=c._source_templates(f.CID);candidate=templates[0][1]
        with patch.object(c,'_source_templates',return_value=(('start-paused',candidate),('start-removing',candidate))):
            status,family,work=c._source_envelope_scan(candidate,f.CID)
        self.assertEqual('ambiguous',status);self.assertIsNone(family)

    def test_five_source_backed_semantic_controls(self):
        def mutant(function,old,new):
            source=textwrap.dedent(inspect.getsource(function));self.assertEqual(1,source.count(old))
            namespace=dict(function.__globals__)
            exec(compile(source.replace(old,new),'<source-envelope-control>','exec'),namespace)
            return namespace[function.__name__]
        raw=RAW[:-1]+b' private suffix'
        self.assertEqual('unmatched',c._source_envelope_scan(raw,f.CID)[0])
        changed=mutant(c._source_envelope_scan,'candidate == envelope','bytes(candidate).startswith(envelope)')
        self.assertEqual('matched',changed(raw,f.CID)[0])
        foreign=(PREFIX+CASES['attach-paused']).replace(f.CID.encode(),b'e'*64)
        self.assertEqual('unmatched',c._source_envelope_scan(foreign,f.CID)[0])
        changed=mutant(c._source_envelope_scan,'_source_templates(cid)',"_source_templates('e'*64)")
        self.assertEqual('matched',changed(foreign,f.CID)[0])
        original=f.packet(RAW.replace(b'paused',b'qaused'))
        with self.assertRaises(c.Refusal):report(RAW,original)
        changed=mutant(c.startup_source_envelope,"sha(raw)==packet['streams']['stderr_sha']",'True')
        self.assertEqual('matched',changed(original,f.descriptor(),RAW)['status'])
        value=f.owner();value.startup_failure=f.packet(RAW)
        value.capture_startup_source(value.startup_failure,f.descriptor(),RAW);saved=copy.deepcopy(value.startup_source_envelope)
        next_raw=PREFIX+CASES['start-removing'];next_packet=f.packet(next_raw)
        value.capture_startup_source(next_packet,f.descriptor(),next_raw);self.assertEqual(saved,value.startup_source_envelope)
        changed=mutant(o.Owner.capture_startup_source,"if getattr(self,'startup_source_attempted',False):return",'if False:return')
        changed(value,next_packet,f.descriptor(),next_raw);self.assertNotEqual(saved,value.startup_source_envelope)
        value=f.owner();value.control=524288
        value.capture_startup_source(f.packet(RAW),f.descriptor(),RAW);self.assertIsNone(getattr(value,'startup_source_envelope',None))
        changed=mutant(o.Owner.capture_startup_source,'self.control+size<=524288','True')
        value.startup_source_attempted=False
        changed(value,f.packet(RAW),f.descriptor(),RAW);self.assertGreater(value.control,524288)
        self.assertEqual('matched',value.startup_source_envelope['status'])


if __name__=='__main__':
    stream=io.StringIO();result=unittest.TextTestRunner(stream=stream).run(unittest.defaultTestLoader.loadTestsFromTestCase(Tests))
    if not result.wasSuccessful():sys.stderr.write(stream.getvalue())
    print(json.dumps(dict(tests=result.testsRun,failures=len(result.failures),errors=len(result.errors),network_calls=0)))
    sys.exit(not result.wasSuccessful())
