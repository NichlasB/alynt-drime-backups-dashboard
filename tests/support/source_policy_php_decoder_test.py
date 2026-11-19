"""Decoder regression: synthetic input only, never the archive/manual entry."""
from contextlib import contextmanager, redirect_stdout
import importlib.util
import io
import json
from pathlib import Path
import subprocess
import sys
import tempfile
from types import SimpleNamespace
import unittest
from unittest.mock import Mock, patch

PATH = Path(__file__).resolve().parents[2]/'scripts/ci/source-policy-php-archive-static.py'
SPEC = importlib.util.spec_from_file_location('decoder_test', PATH)
M = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(M)
TOOL = None


@contextmanager
def profile(result):
    resource = SimpleNamespace(RLIMIT_AS=1, RLIMIT_CORE=2, RLIMIT_CPU=3, setrlimit=Mock())
    with patch.dict(sys.modules, {'resource': resource}), patch.object(M.sys, 'platform', 'linux'), \
            patch.object(M.Path, 'is_file', return_value=True), patch.object(M.subprocess, 'run', return_value=result) as run:
        yield resource, run


class Checks(unittest.TestCase):
    def test_fixed_arguments_and_unchanged_budgets(self):
        self.assertEqual(('/usr/bin/zstd', '-d', '-q', '--no-asyncio', '-M8MB', '-c'), M.DECODER_ARGS)
        self.assertEqual((40*2**20, 24*2**20, 8*2**20), (M.PARENT_MEMORY, M.DECODER_MEMORY, M.WINDOW))
        self.assertEqual(64*2**20, M.PARENT_MEMORY+M.DECODER_MEMORY)
        self.assertEqual('28b52ffd200109000078', M.DECODER_SAMPLE.hex())

    def test_preflight_positive_uses_actual_guards_and_limit_hooks(self):
        with profile(subprocess.CompletedProcess([], 0, b'x')) as (resource, run):
            result = M.preflight()
            self.assertTrue(result['synthetic_child_reaped'])
            self.assertIn('--no-asyncio', run.call_args.args[0])
            resource.setrlimit.assert_any_call(resource.RLIMIT_AS, (M.PARENT_MEMORY, M.PARENT_MEMORY))
            self.assertEqual(list(M.DECODER_ARGS), run.call_args.args[0])
            self.assertEqual(M.DECODER_SAMPLE, run.call_args.kwargs['input'])
            self.assertEqual(5, run.call_args.kwargs['timeout'])
            self.assertIs(M.decoder_limit, run.call_args.kwargs['preexec_fn'])
            self.assertEqual(subprocess.DEVNULL, run.call_args.kwargs['stderr'])
            M.decoder_limit()
            resource.setrlimit.assert_any_call(resource.RLIMIT_AS, (M.DECODER_MEMORY, M.DECODER_MEMORY))
            resource.setrlimit.assert_any_call(resource.RLIMIT_CPU, (60, 60))
            resource.setrlimit.assert_any_call(resource.RLIMIT_CORE, (0, 0))

    def test_failure_facts_are_finite_and_cannot_replace_success_predicates(self):
        for code, out in ((104, b''), (1, b'x'), (-9, b''), (0, b''), (0, b'private-unexpected-output')):
            with profile(subprocess.CompletedProcess([], code, out)), self.assertRaisesRegex(M.S.Refusal, '^decoder-cap-preflight$') as caught:
                M.preflight()
            self.assertTrue(hasattr(caught.exception, 'decoder_preflight'), 'failed exit/output predicates must remain observable')
            self.assertEqual({'exit_code': code, 'stdout_bytes': len(out), 'stdout_matches': out == b'x'}, caught.exception.decoder_preflight)
            self.assertNotIn('private', json.dumps(caught.exception.decoder_preflight))

    def test_unavailable_tool_refuses_before_spawn_or_cap_change(self):
        with profile(subprocess.CompletedProcess([], 0, b'x')) as (resource, run), patch.object(M.Path, 'is_file', return_value=False):
            with self.assertRaisesRegex(M.S.Refusal, 'existing-decoder'):
                M.preflight()
            run.assert_not_called()
            resource.setrlimit.assert_not_called()

    def test_main_keeps_failure_facts_before_any_owned_io(self):
        with profile(subprocess.CompletedProcess([], 104, b'')), self.assertRaises(M.S.Refusal) as caught:
            M.preflight()
        with tempfile.TemporaryDirectory() as temp, patch.object(M, 'target', return_value=Path(temp)/'not-created'), \
                patch.object(M, 'preflight', side_effect=caught.exception), patch.object(M, 'download') as download, \
                patch.object(M.Path, 'mkdir') as mkdir, patch.object(M.sys, 'argv', ['proof', '--approved-static-proof']), \
                patch.object(M.signal, 'SIGALRM', 14, create=True), patch.object(M.signal, 'signal'), \
                patch.object(M.signal, 'alarm', create=True), redirect_stdout(io.StringIO()) as output:
            self.assertEqual(1, M.main())
            receipt = json.loads(output.getvalue())
            self.assertEqual(caught.exception.decoder_preflight, receipt['decoder_preflight'])
            self.assertEqual('decoder-cap-preflight', receipt['reason'])
            self.assertEqual(0, receipt['owned_archive_remaining'])
            self.assertFalse(receipt['qualified'])
            self.assertFalse((Path(temp)/'not-created').exists())
            download.assert_not_called()
            mkdir.assert_not_called()

    def test_scan_uses_identical_command_and_reaps_on_parser_failure(self):
        process = SimpleNamespace(stdout=Mock(), poll=Mock(return_value=None), kill=Mock(), wait=Mock(return_value=0))
        with patch.object(M.subprocess, 'Popen', return_value=process) as launch, \
                patch.object(M, 'inventory', side_effect=M.S.Refusal('fixture-refusal')):
            with self.assertRaisesRegex(M.S.Refusal, 'fixture-refusal'):
                M.scan(Path('owned-fixture'))
        self.assertEqual([*M.DECODER_ARGS, 'owned-fixture'], launch.call_args.args[0])
        self.assertIs(M.decoder_limit, launch.call_args.kwargs['preexec_fn'])
        process.kill.assert_called_once()
        process.wait.assert_called_once_with(timeout=5)
        process.stdout.close.assert_called_once()

    def test_argument_guard_assertion_killed_control(self):
        args = tuple(value for value in M.DECODER_ARGS if value != '--no-asyncio')
        with patch.object(M, 'DECODER_ARGS', args):
            result = unittest.TestResult()
            Checks('test_fixed_arguments_and_unchanged_budgets').run(result)
        self.assertEqual(1, len(result.failures))
        self.assertEqual(0, len(result.errors))

    if sys.platform == 'linux':
        def test_existing_linux_tool_synthetic_only_under_unchanged_caps(self):
            global TOOL
            load = "import importlib.util,json,resource,subprocess; s=importlib.util.spec_from_file_location('data',%r); m=importlib.util.module_from_spec(s); s.loader.exec_module(m);" % str(PATH)
            legacy = load+"resource.setrlimit(resource.RLIMIT_AS,(m.PARENT_MEMORY,m.PARENT_MEMORY)); r=subprocess.run([a for a in m.DECODER_ARGS if a!='--no-asyncio'],input=m.DECODER_SAMPLE,stdout=subprocess.PIPE,stderr=subprocess.DEVNULL,timeout=5,preexec_fn=m.decoder_limit,env=m.decoder_environment()); print(json.dumps({'exit_code':r.returncode,'stdout_bytes':len(r.stdout),'stdout_matches':r.stdout==b'x'}))"
            previous = subprocess.run([sys.executable, '-I', '-S', '-B', '-c', legacy], capture_output=True, timeout=10, env=M.decoder_environment())
            self.assertEqual(0, previous.returncode, 'legacy observer did not finish')
            old = json.loads(previous.stdout)
            self.assertEqual({'exit_code', 'stdout_bytes', 'stdout_matches'}, set(old))
            current = subprocess.run([sys.executable, '-I', '-S', '-B', '-c', load+"print(json.dumps(m.preflight()))"], capture_output=True, timeout=10, env=M.decoder_environment())
            self.assertEqual(0, current.returncode, 'synchronous decoder failed unchanged caps: '+current.stderr.decode('ascii', 'replace')[:2048])
            self.assertEqual({'parent_address_space': 40*2**20, 'decoder_address_space': 24*2**20, 'decoder_window': 8*2**20, 'synthetic_child_reaped': True}, json.loads(current.stdout))
            TOOL = {'legacy': old, 'synchronous': 'passed', 'archive_calls': 0}


if __name__ == '__main__':
    result = unittest.TestResult()
    unittest.defaultTestLoader.loadTestsFromTestCase(Checks).run(result)
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures), 'errors': len(result.errors), 'network_calls': 0, 'tool': TOOL}))
    if not result.wasSuccessful():
        print(result.failures+result.errors, file=sys.stderr)
        raise SystemExit(1)
