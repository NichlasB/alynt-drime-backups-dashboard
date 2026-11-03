"""Socket-free DATA positives/negatives; never an image or kernel proof."""
import copy
import gzip
import hashlib
import importlib.util
import inspect
import io
import json
from pathlib import Path
import sys
import tarfile
import textwrap
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]


def load(name):
    spec = importlib.util.spec_from_file_location(name, ROOT / 'scripts/manual' / (name + '.py'))
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


i = load('source_policy_ubi_import_contract')
r = load('source_policy_ubi_runtime_contract')


def fixture():
    # Deliberately NOT the pinned candidate config/layer bytes. No pin overrides.
    bodies = tuple((('synthetic-layer-%d\n' % n) * 40).encode() for n in range(10))
    compressed = tuple(gzip.compress(body, mtime=0) for body in bodies)
    layers = tuple((hashlib.sha256(blob).hexdigest(), len(blob), hashlib.sha256(body).hexdigest(), len(body))
                   for blob, body in zip(compressed, bodies))
    config = json.dumps(dict(os='linux', architecture='amd64', rootfs=dict(type='layers',
                        diff_ids=['sha256:' + row[2] for row in layers])), separators=(',', ':')).encode()
    return config, layers, compressed, bodies


def context():
    return dict(format='ADBURCONTEXT1', manifest=r.MANIFEST, config=r.CONFIG, head='a'*40,
                run='123', attempt=1, static_source='b'*64, runtime_source='c'*64,
                snapshot='d'*64, graph='e'*64, profile='ubi-version-only-v1')


def policy():
    return dict(defaultAction='SCMP_ACT_ERRNO', architectures=['SCMP_ARCH_X86_64'],
                syscalls=[dict(names=['read', 'write', 'socket', 'socketcall', 'bind', 'listen'], action='SCMP_ACT_ALLOW')])


def capture(meter=None):
    return r.Capture(context(), meter if meter is not None else i.Meter(), '1'*64, '2'*64)


class ImportContracts(unittest.TestCase):
    def test_streamed_archive_preserves_original_bytes_and_fixed_member_names(self):
        config, layers, compressed, bodies = fixture()
        meter = i.Meter(); meter.charge('expanded', 10000)  # Prior static pass shares this ledger.
        chunks = list(i.archive(config, layers, tuple(tuple(blob[n:n+7] for n in range(0, len(blob), 7)) for blob in compressed), meter))
        data = b''.join(chunks)
        self.assertLessEqual(max(map(len, chunks)), i.CHUNK)
        with tarfile.open(fileobj=io.BytesIO(data)) as archive:
            names = archive.getnames(); self.assertEqual(12, len(names))
            self.assertEqual(config, archive.extractfile(names[0]).read())
            manifest = json.load(archive.extractfile('manifest.json'))
            self.assertEqual([], manifest[0]['RepoTags'])
            self.assertEqual(names[2:], manifest[0]['Layers'])
            self.assertEqual(list(bodies), [archive.extractfile(name).read() for name in names[2:]])
            self.assertTrue(all(member.isfile() and member.uid == member.gid == member.mtime == 0 for member in archive))
        self.assertEqual(10000 + 2*sum(map(len, bodies)) + len(config) + len(data)-sum(map(len, bodies)), meter.used['expanded'])
        self.assertNotEqual(r.CONFIG, hashlib.sha256(config).hexdigest())

    def test_partial_sink_writes_preserve_all_bytes_and_zero_progress_refuses(self):
        config, layers, blobs, _ = fixture(); output = bytearray(); meter = i.Meter()
        def sink(chunk):
            size = min(3, len(chunk)); output.extend(chunk[:size]); return size
        receipt = i.drain(i.archive(config, layers, tuple((x,) for x in blobs), meter), sink, meter)
        self.assertEqual(hashlib.sha256(output).hexdigest(), receipt['sha256'])
        self.assertEqual(len(output), receipt['bytes']); self.assertFalse(receipt['launch_authority'])
        for value in (0, -1, True, 100000, None):
            with self.subTest(value=value), self.assertRaisesRegex(i.Refusal, 'sink-progress'):
                i.drain(iter((b'x',)), lambda chunk: value, i.Meter())

    def test_compressed_digest_size_diff_id_order_and_expanded_lengths_refuse(self):
        config, layers, blobs, _ = fixture()
        variants = []
        for index, value in ((0, 'f'*64), (1, layers[0][1]+1), (2, 'f'*64), (3, layers[0][3]+1)):
            rows = list(layers); row = list(rows[0]); row[index] = value; rows[0] = tuple(row); variants.append(tuple(rows))
        for rows in variants:
            with self.subTest(rows=rows[0]), self.assertRaises(i.Refusal):
                b''.join(i.archive(config, rows, tuple((x,) for x in blobs), i.Meter()))
        for streams in (tuple((x,) for x in reversed(blobs)), tuple((x+b'x',) for x in blobs), tuple((x[:-1],) for x in blobs)):
            with self.assertRaises(i.Refusal): b''.join(i.archive(config, layers, streams, i.Meter()))
        # Highly compressible output exercises max_length/unconsumed_tail, not
        # merely the small one-buffer path; buffers remain <=64KiB.
        body = b'x'*200000; blob = gzip.compress(body, mtime=0)
        rows = ((hashlib.sha256(blob).hexdigest(), len(blob), hashlib.sha256(body).hexdigest(), len(body)),)*10
        config = json.dumps(dict(os='linux', architecture='amd64', rootfs=dict(type='layers', diff_ids=['sha256:'+rows[0][2]]*10))).encode()
        self.assertLessEqual(max(map(len, i.archive(config, rows, ((blob,),)*10, i.Meter()))), i.CHUNK)

    def test_metadata_types_limits_duplicates_and_shape_are_checked_before_stream_reads(self):
        config, layers, _, _ = fixture()
        class Unreadable:
            def __iter__(self): raise AssertionError('protected stream read')
        cases = [(config, layers[:-1]), (b'{"os":"linux","os":"linux"}', layers),
                 (config+b' ', list(layers)), (b'{}', layers), (b'x'*(i.MIB+1), layers),
                 (b'{"ignored":NaN}', layers), (b'\xff', layers)]
        for config_data, rows in cases:
            with self.subTest(size=len(config_data)), self.assertRaises(i.Refusal):
                list(i.archive(config_data, rows, tuple(Unreadable() for _ in range(10)), i.Meter()))

    def test_shared_budgets_virtual_deadline_and_poison_preserve_first_cause(self):
        meter = i.Meter(); meter.charge('expanded', 2*1024*i.MIB)
        with self.assertRaisesRegex(i.Refusal, '^expanded-budget$'): meter.charge('expanded', 1)
        with self.assertRaisesRegex(i.Refusal, '^expanded-budget$'): meter.charge('work', 1)
        for kind, limit in [('work', 2097152), ('control', i.MIB), ('observation', 2*i.MIB)]:
            meter = i.Meter(); meter.charge(kind, limit)
            with self.assertRaisesRegex(i.Refusal, kind+'-budget'): meter.charge(kind, 1)
        meter = i.Meter(); meter.tick(570001)
        with self.assertRaisesRegex(i.Refusal, 'cleanup-reserve'): meter.reserve(0)
        meter = i.Meter(); meter.tick(10)
        with self.assertRaisesRegex(i.Refusal, 'clock'): meter.tick(9)
        config, layers, blobs, _ = fixture(); meter = i.Meter()
        stream = i.archive(config, layers, tuple((x,) for x in blobs), meter); next(stream); meter.tick(120001)
        with self.assertRaisesRegex(i.Refusal, 'import-timeout'): list(stream)

    def test_digest_and_partial_write_guards_are_assertion_killed(self):
        config, layers, blobs, _ = fixture(); rows = list(layers)
        rows[0] = ('f'*64,) + rows[0][1:]
        def witness_digest():
            with self.assertRaisesRegex(i.Refusal, 'compressed-identity'):
                list(i.archive(config, tuple(rows), tuple((x,) for x in blobs), i.Meter()))
        self.control(i, i.archive, "compressed_hash.hexdigest() == compressed_sha", 'True', witness_digest)
        def witness_progress():
            with self.assertRaisesRegex(i.Refusal, 'sink-progress'):
                i.drain(iter((b'x',)), lambda chunk: True, i.Meter())
        self.control(i, i.drain, 'type(written) is int', 'True', witness_progress)

    def control(self, module, function, old, new, witness):
        witness(); source = textwrap.dedent(inspect.getsource(function)); self.assertEqual(1, source.count(old))
        scope = dict(module.__dict__); exec(compile(source.replace(old, new), '<guard-control>', 'exec'), scope)
        with patch.object(module, function.__name__, scope[function.__name__]):
            with self.assertRaises(AssertionError): witness()


class RuntimeContracts(ImportContracts):
    # Inherited helper only; suite loader below excludes inherited test duplication.
    def test_candidate_context_and_finite_source_cohort(self):
        frame = r.context_frame(context()); self.assertFalse(frame['launch_authority'])
        for key, value in [('manifest', 'f'*64), ('config', 'f'*64), ('head', 'bad'), ('attempt', True),
                           ('run', '123\n'), ('profile', 'other'), ('runtime_source', 'b'*64), ('grant', True)]:
            with self.subTest(key=key), self.assertRaises(r.Refusal): r.context_frame(dict(context(), **{key:value}))
        sources = tuple((path, 'a'*64) for path in r.RUNTIME_SOURCES)
        self.assertEqual(64, len(r.runtime_source(sources)))
        for rows in (sources[::-1], sources[:-1], sources + (('arbitrary.py', 'a'*64),)):
            with self.assertRaises(r.Refusal): r.runtime_source(rows)
        static = load('source_policy_ubi_elf_static')
        self.assertEqual(static._LIBGCC_PIN, r.MANIFEST); self.assertEqual(static._LIBGCC_CONFIG, r.CONFIG)
        import ast
        assignments = ast.parse((ROOT / 'scripts/ci/source-policy-legacy-ubi-static.py').read_text()).body
        actual = {node.targets[0].id:ast.literal_eval(node.value) for node in assignments if isinstance(node, ast.Assign)
                  and isinstance(node.targets[0], ast.Name) and node.targets[0].id in ('LAYERS', 'BOUNDED_SOURCES')}
        self.assertEqual(actual['LAYERS'], r.LAYERS); self.assertEqual(actual['BOUNDED_SOURCES'], r.RUNTIME_SOURCES[:-2])
        rows = tuple((sha, size, 'f'*64, 512) for sha, size in r.LAYERS)  # Identity DATA, not admitted bytes.
        self.assertFalse(r.import_identity(context(), r.CONFIG, rows)['launch_authority'])
        for config_sha, descriptors in [(hashlib.sha256(fixture()[0]).hexdigest(), rows), (r.CONFIG, rows[::-1]),
                                        (r.CONFIG, fixture()[1])]:
            with self.assertRaises(r.Refusal): r.import_identity(context(), config_sha, descriptors)

    def test_restrictive_synthetic_subset_preserves_denials_without_claiming_kernel_enforcement(self):
        before = policy(); original = copy.deepcopy(before); derived = r.restrict_policy(before)
        self.assertEqual(original, before)
        self.assertEqual(['read', 'write'], derived['profile']['syscalls'][0]['names'])
        self.assertFalse(derived['kernel_verified']); self.assertFalse(derived['launch_authority'])
        for key, value in [('defaultAction', 'SCMP_ACT_ALLOW'), ('architectures', ['SCMP_ARCH_X86']), ('unknown', True)]:
            with self.assertRaises(r.Refusal): r.restrict_policy(dict(before, **{key:value}))
        for row in [dict(names=['read'], action='SCMP_ACT_ALLOW', args=[]), dict(names=['read'], action='SCMP_ACT_TRACE')]:
            with self.assertRaises(r.Refusal): r.restrict_policy(dict(before, syscalls=[row]))

    def test_launch_data_is_exact_nonprivileged_and_never_an_execution_handle(self):
        frame = context(); plan = r.launch_plan(frame, 1001, policy()); self.assertTrue(r.validate_plan(plan, frame, 1001, policy()))
        self.assertFalse(plan['launch_authority']); self.assertEqual(['/usr/sbin/mariadbd', '--no-defaults', '--version'], plan['argv'])
        for key, value in [('argv', ['/bin/sh']), ('network', 'host'), ('readonly', False), ('user', '0:0'),
                           ('capabilities', ['SYS_ADMIN']), ('devices', ['/dev/x']), ('environment', ['SECRET=bad']),
                           ('mounts', []), ('seccomp', 'unconfined'), ('extra', True), ('launch_authority', True),
                           ('readonly', 1), ('cpus', 1.0), ('capabilities', ()), ('privileged', 0)]:
            with self.subTest(key=key), self.assertRaises(r.Refusal):
                r.validate_plan(dict(plan, **{key:value}), frame, 1001, policy())
        for uid in (0, True, -1, '1001'):
            with self.assertRaises(r.Refusal): r.launch_plan(frame, uid, policy())

    def test_full_object_identity_exact_labels_mounts_and_owner_before_cleanup(self):
        frame = context(); plan = r.launch_plan(frame, 1001, policy())
        resource = dict(cid='1'*64, name=r.owner_name(frame), labels=r.owner_labels(frame),
                        image='sha256:'+r.CONFIG, mounts=plan['mounts'], state='exited')
        self.assertTrue(r.owned_object(resource, frame, '1'*64))
        for key, value in [('cid', '1'*12), ('name', 'foreign'), ('labels', {}), ('image', 'foreign'), ('mounts', []), ('state', 'paused')]:
            with self.subTest(key=key), self.assertRaises(r.Refusal): r.owned_object(dict(resource, **{key:value}), frame, '1'*64)
        with self.assertRaises(r.Refusal): r.owned_object(resource, frame, '2'*64)
        altered = copy.deepcopy(resource); altered['mounts'][0]['readonly'] = 1
        with self.assertRaises(r.Refusal): r.owned_object(altered, frame, '1'*64)

    def test_output_dual_channel_limits_deadlines_and_cleanup_do_not_manufacture_success(self):
        observed = capture(); observed.feed('stdout', r.VERSION, 1)
        summary = observed.finish(0, False, False, 'absent', 'absent', 2)
        self.assertTrue(summary['observation_valid_data']); self.assertFalse(summary['launch_authority'])
        self.assertEqual('1'*64, summary['cid']); self.assertEqual('2'*64, summary['containment_sha'])
        with self.assertRaisesRegex(r.Refusal, 'observation-closed'): observed.finish(0, False, False, 'absent', 'absent', 2)
        self.assertNotIn('version', summary); self.assertNotIn(r.VERSION.decode(), json.dumps(summary))
        for output, stderr, code, oom, timeout, primary, always in [
            (b'unknown warning\n', b'', 0, False, False, 'absent', 'absent'),
            (r.VERSION, b'warning', 0, False, False, 'absent', 'absent'),
            (r.VERSION, b'', 1, False, False, 'absent', 'absent'),
            (r.VERSION, b'', 0, True, False, 'absent', 'absent'),
            (r.VERSION, b'', None, False, True, 'absent', 'absent'),
            (r.VERSION, b'', 0, False, False, 'failed', 'absent'),
            (r.VERSION, b'', 0, False, False, 'absent', 'failed')]:
            observed = capture(); observed.feed('stdout', output, 1); observed.feed('stderr', stderr, 1)
            result = observed.finish(code, oom, timeout, primary, always, 2)
            self.assertFalse(result['observation_valid_data']); self.assertFalse(result['launch_authority'])
        observed = capture(); observed.feed('stdout', b'x'*8192, 1); observed.feed('stderr', b'x'*8192, 2)
        with self.assertRaisesRegex(r.Refusal, 'output-budget'): observed.feed('stderr', b'x', 3)
        result = observed.finish(None, False, True, 'absent', 'absent', 15001)
        self.assertEqual('output-budget', result['failure']); self.assertFalse(result['observation_valid_data'])
        for now in (15001, 600001):
            observed = capture()
            with self.assertRaises(r.Refusal): observed.feed('stdout', r.VERSION, now)
        meter = i.Meter(); meter.tick(555001)
        with self.assertRaisesRegex(i.Refusal, 'cleanup-reserve'): capture(meter)
        meter = i.Meter(); meter.charge('work', 2097152); observed = capture(meter)
        with self.assertRaisesRegex(r.Refusal, 'shared-budget'): observed.feed('stdout', r.VERSION, 1)
        self.assertEqual('shared-budget', observed.finish(None, False, True, 'absent', 'absent', 2)['failure'])
        for cid, identity in [('1'*12, '2'*64), ('1'*64, 'bad')]:
            with self.assertRaises(r.Refusal): r.Capture(context(), i.Meter(), cid, identity)
        for now in (True, None, -1, 600001):
            with self.assertRaisesRegex(r.Refusal, 'terminal-clock'): capture().finish(0, False, False, 'absent', 'absent', now)

    def test_context_effect_ownership_and_observation_guard_controls_are_assertion_killed(self):
        def candidate():
            with self.assertRaises(r.Refusal): r.context_frame(dict(context(), manifest='f'*64))
        self.control(r, r.context_frame, "frame['manifest'] == MANIFEST", 'True', candidate)
        def subset():
            self.assertNotIn('socket', [name for row in r.restrict_policy(policy())['profile']['syscalls'] for name in row['names']])
        self.control(r, r.restrict_policy, 'name not in NETWORK_CALLS', 'True', subset)
        def unsafe():
            plan = r.launch_plan(context(), 1001, policy()); plan['network'] = 'host'
            with self.assertRaises(r.Refusal): r.validate_plan(plan, context(), 1001, policy())
        self.control(r, r.validate_plan, 'same_data(plan, launch_plan(frame, uid, baseline))', 'True', unsafe)
        def foreign():
            plan = r.launch_plan(context(), 1001, policy())
            resource = dict(cid='1'*64, name=r.owner_name(context()), labels={}, image='sha256:'+r.CONFIG, mounts=plan['mounts'], state='exited')
            with self.assertRaises(r.Refusal): r.owned_object(resource, context(), '1'*64)
        self.control(r, r.owned_object, "row['labels'] == owner_labels(frame)", 'True', foreign)
        def cleanup():
            observed = capture(); observed.feed('stdout', r.VERSION, 1)
            self.assertFalse(observed.finish(0, False, False, 'failed', 'absent', 2)['observation_valid_data'])
        source = textwrap.dedent(inspect.getsource(r.Capture.finish)); old = "primary == independent == 'absent'"
        self.assertEqual(1, source.count(old)); scope = dict(r.__dict__)
        exec(compile(source.replace(old, 'True'), '<cleanup-control>', 'exec'), scope)
        cleanup()
        with patch.object(r.Capture, 'finish', scope['finish']):
            with self.assertRaises(AssertionError): cleanup()

    def test_no_operational_modules_calls_or_old_controller_changes(self):
        import ast
        for module in (i, r):
            tree = ast.parse(inspect.getsource(module))
            imports = [node.names[0].name for node in ast.walk(tree) if isinstance(node, ast.Import)]
            self.assertLessEqual(set(imports), {'hashlib', 'json', 're', 'struct', 'zlib'})
            self.assertFalse(any(isinstance(node, ast.ImportFrom) for node in ast.walk(tree)))
            self.assertFalse(any(isinstance(node, ast.Call) and isinstance(node.func, ast.Name) and node.func.id in ('open', 'exec', 'eval', '__import__') for node in ast.walk(tree)))
        source = (ROOT / 'scripts/ci/source-policy-legacy-ubi-static.py').read_text()
        self.assertNotIn('source_policy_ubi_runtime_contract', source)
        self.assertNotIn('source_policy_ubi_import_contract', source)


if __name__ == '__main__':
    suite = unittest.TestSuite()
    for cls in (ImportContracts, RuntimeContracts):
        suite.addTests(cls(name) for name in cls.__dict__ if name.startswith('test_'))
    out = io.StringIO(); result = unittest.TextTestRunner(stream=out).run(suite)
    print(json.dumps(dict(tests=result.testsRun, failures=len(result.failures), errors=len(result.errors), network_calls=0)), flush=True)
    if not result.wasSuccessful(): sys.stderr.buffer.write(out.getvalue().encode('utf-8', 'backslashreplace')[:12000])
    sys.exit(0 if result.wasSuccessful() else 1)
