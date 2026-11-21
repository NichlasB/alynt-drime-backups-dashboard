"""Synthetic byte-only differential and semantic controls; no artifact IO."""
import ast
import hashlib
import importlib.util
import json
from pathlib import Path
import random
import struct
import sys
import unittest
from unittest.mock import patch

ROOT = Path(__file__).resolve().parents[2]
sys.path.insert(0, str(ROOT/'scripts/ci'))
import source_policy_elf_ranges as E
SPEC = importlib.util.spec_from_file_location('elf_oracle', ROOT/'scripts/manual/source_policy_linux_minimum_static.py')
S = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(S)
CHUNKS = (1, 7, 63, 64, 511, 512, 4093, 65536)


def image(programs=(), payloads=(), size=4096, pht=64):
    data = bytearray(size)
    data[:7] = b'\x7fELF\x02\x01\x01'
    struct.pack_into('<HH', data, 16, 3, 62)
    struct.pack_into('<Q', data, 32, pht)
    struct.pack_into('<HH', data, 54, 56, len(programs))
    for index, row in enumerate(programs):
        ptype, start, length, address = row
        struct.pack_into('<IIQQQQQQ', data, pht + index*56,
                         ptype, 0, start, address, 0, length, 0, 0)
    for start, value in payloads:
        data[start:start+len(value)] = value
    return bytes(data)


def dynamic(entries):
    return b''.join(struct.pack('<qQ', *row) for row in entries)


def rich(needed=(0, 5, 0), paths=(11, 18), table=b'a.so\0bb.so\0/rpath\0/runpath\0',
         extra=(), pht=64, dynamic_start=512, table_start=2048):
    entries = [(5, 0x4000 + table_start), (10, len(table))]
    entries += [(1, value) for value in needed]
    entries += [(15 if i == 0 else 29, value) for i, value in enumerate(paths)]
    entries += list(extra) + [(0, 0)]
    raw = dynamic(entries)
    return image([(1, 0, 4096, 0x4000), (2, dynamic_start, len(raw), 0)],
                 [(dynamic_start, raw), (table_start, table)], pht=pht)


def scan(reader, data, chunk=65536):
    for offset in range(0, len(data), chunk):
        reader.feed(offset, data[offset:offset+chunk])
    return reader.finish_scan()


def collect(data, chunk=65536, budget=None):
    reader = E.Collector(len(data), budget)
    phases = []
    while reader.phase != 'done':
        phases.append(reader.phase)
        scan(reader, data, chunk)
    return reader, reader.result(), phases


def changed(data, fmt, offset, *values):
    copy = bytearray(data)
    struct.pack_into(fmt, copy, offset, *values)
    return bytes(copy)


class Checks(unittest.TestCase):
    def parity(self, data, chunks=CHUNKS):
        expected = S.elf(data)
        for chunk in chunks:
            with self.subTest(chunk=chunk):
                reader, actual, phases = collect(data, chunk)
                self.assertEqual(expected, actual)
                self.assertEqual(hashlib.sha256(data).hexdigest(), actual['sha256'])
                self.assertFalse(actual['executed'])
                self.assertFalse(actual['dependency_closure_verified'])
                self.assertLessEqual(len(phases), 3)
                self.assertEqual(0, reader._budget.usage[0])

    def refusal(self, data, reason=None, chunks=(1, 63, 65536)):
        with self.assertRaises((S.Refusal, UnicodeDecodeError)):
            S.elf(data)
        for chunk in chunks:
            with self.subTest(chunk=chunk):
                with self.assertRaisesRegex(E.Refusal, reason or 'elf-'):
                    collect(data, chunk)

    def test_nonempty_ordered_dependencies_paths_and_digest(self):
        data = rich()
        self.parity(data)
        _, result, phases = collect(data)
        self.assertEqual(['a.so', 'bb.so', 'a.so'], result['needed'])
        self.assertEqual(['/rpath', '/runpath'], result['search_paths'])
        self.assertEqual(['primary', 'metadata', 'strings'], phases)

    def test_late_pht_and_backwards_metadata_need_three_scans(self):
        data = rich(pht=3500, dynamic_start=128, table_start=512)
        self.parity(data)
        self.assertEqual(3, len(collect(data)[2]))

    def test_zero_phdr_empty_phases_and_full_body_hash(self):
        self.parity(image())
        self.assertEqual(['primary'], collect(image())[2])
        self.parity(image(payloads=[(4095, b'X')]))

    def test_pht_overlaps_header_and_ignored_fields(self):
        # PT_LOAD=1 also encodes e_phnum=1 in the overlapping header bytes.
        data = image([(1, 0, 0, 0)], pht=56)
        self.parity(data)
        self.parity(changed(image(), '<Q', 24, (1 << 64)-1))
        self.parity(changed(image(), '<B', 7, 255))  # OSABI ignored by oracle.
        self.parity(changed(image(), '<H', 16, 2))
        self.parity(image([(99, 0, 0, 0)]*128, size=8192), (1, 511, 65536))

    def test_interpreters_checked_all_and_last_pht_order_wins(self):
        data = image([(3, 1024, 4, 0), (3, 512, 3, 0)],
                     [(1024, b'/a\0\0'), (512, b'\x01b\0')])
        self.parity(data)
        self.assertEqual('\x01b', collect(data)[1]['interpreter'])
        bad = image([(3, 1024, 3, 0), (3, 512, 3, 0)],
                    [(1024, b'bad'), (512, b'/b\0')])
        self.refusal(bad, 'elf-interpreter')

    def test_interpreter_length_empty_and_nonascii(self):
        for value in (b'\0', b'x'*239+b'\0'):
            self.parity(image([(3, 512, len(value), 0)], [(512, value)]))
        for value in (b'', b'x'*240+b'\0', b'\xff\0'):
            self.refusal(image([(3, 512, len(value), 0)], [(512, value)]), 'elf-interpreter')
        self.parity(image([(3, 8000, 240, 0)]*128, [(8000, b'x'*239+b'\0')], size=16384), (7, 65536))

    def test_first_null_unused_large_strsz_and_ignored_tags(self):
        self.parity(image([(2, 512, 64, 0)], [(512, dynamic([(10, E.MIB+1), (99, 7), (0, 0), (10, 2)]))]))
        self.parity(image([(2, 512, 16, 0)], [(512, dynamic([(0, 0)]))]))
        self.parity(rich(extra=[(-1, 42)]))

    def test_exact_string_boundaries_and_empty_string(self):
        for count in (0, 1, 239, 240):
            self.parity(rich(needed=(0,), paths=(), table=b'x'*count+b'\0'))
        self.refusal(rich(needed=(0,), paths=(), table=b'x'*241+b'\0'), 'elf-string-length')
        for table in (b'abc', b'\xff\0', b'\x1f\0', b'\x7f\0'):
            self.refusal(rich(needed=(0,), paths=(), table=table))

    def test_needed_and_path_count_bounds(self):
        self.parity(rich(needed=(0,)*64, paths=(0, 0), table=b'\0'))
        self.refusal(rich(needed=(0,)*65, paths=(), table=b'\0'), 'elf-dynamic-shape')
        self.refusal(rich(needed=(), paths=(0, 0, 0), table=b'\0'), 'elf-dynamic-shape')

    def test_dynamic_shape_duplicates_and_missing_null(self):
        for programs, entries, reason in (
            ([(2, 512, 0, 0)], [], 'elf-dynamic-shape'),
            ([(2, 512, 15, 0)], [], 'elf-dynamic-bound'),
            ([(2, 512, E.MIB+16, 0)], [], 'elf-segment-bound'),
            ([(2, 512, 16, 0), (2, 512, 16, 0)], [(0, 0)], 'elf-dynamic-bound'),
            ([(2, 512, 48, 0)], [(10, 1), (10, 1), (0, 0)], 'elf-duplicate-string-table'),
            ([(2, 512, 48, 0)], [(5, 1), (5, 1), (0, 0)], 'elf-duplicate-string-table'),
            ([(2, 512, 16, 0)], [(99, 0)], 'elf-dynamic-shape'),
        ):
            self.refusal(image(programs, [(512, dynamic(entries))]), reason)

    def test_string_tags_index_and_unique_mapping(self):
        self.refusal(rich(needed=(1,), paths=(), table=b'\0'), 'elf-string-index')
        self.refusal(rich(needed=(0,), paths=(), table=b'\0', extra=[(10, 1)]), 'elf-duplicate-string-table')
        good = rich()
        # Second identical LOAD makes even an identical mapping ambiguous.
        data = bytearray(good)
        struct.pack_into('<H', data, 56, 3)
        data[176:232] = data[64:120]
        self.refusal(bytes(data), 'elf-string-mapping')
        for entries in ([(1, 0), (0, 0)], [(5, 0), (10, 1), (1, 0), (0, 0)],
                        [(5, 0), (10, E.MIB+1), (1, 0), (0, 0)]):
            self.refusal(image([(2, 512, len(entries)*16, 0)], [(512, dynamic(entries))]))

    def test_header_and_every_segment_file_extent(self):
        for offset, fmt, value, reason in (
            (0, '<B', 0, 'elf-header'), (4, '<B', 1, 'elf-header'),
            (5, '<B', 2, 'elf-header'), (16, '<H', 1, 'elf-program-table'),
            (18, '<H', 183, 'elf-program-table'), (54, '<H', 55, 'elf-program-table'),
            (56, '<H', 129, 'elf-program-table'), (32, '<Q', (1 << 64)-1, 'elf-program-table'),
        ):
            self.refusal(changed(image(), fmt, offset, value), reason)
        for ptype in (0, 1, 2, 3, 99):
            self.refusal(image([(ptype, 4095, 2, 0)]), 'elf-segment-bound')
        self.refusal(changed(image(), '<Q', 32, 4097), 'elf-program-table')

    def test_replay_hash_checks_unselected_tail_bytes(self):
        data = rich()
        for phase in ('metadata', 'strings'):
            reader = E.Collector(len(data))
            while reader.phase != phase:
                scan(reader, data)
            with self.assertRaisesRegex(E.Refusal, 'elf-replay-identity'):
                scan(reader, data[:-1]+b'X')
            self.assertEqual('refused', reader.phase)
            self.assertEqual(0, reader._budget.usage[0])

    def test_partial_result_and_repeated_finish_are_terminal(self):
        for phase in ('primary', 'metadata', 'strings'):
            reader = E.Collector(4096)
            while reader.phase != phase:
                scan(reader, rich())
            with self.assertRaisesRegex(E.Refusal, 'elf-result-unavailable'):
                reader.result()
            self.assertEqual('refused', reader.phase)
        reader = E.Collector(4096)
        reader.feed(0, image()[:-1])
        with self.assertRaisesRegex(E.Refusal, 'elf-scan-truncated'):
            reader.finish_scan()
        for phase in ('metadata', 'strings'):
            reader = E.Collector(4096)
            while reader.phase != phase:
                scan(reader, rich())
            reader.feed(0, rich()[:-1])
            with self.assertRaisesRegex(E.Refusal, 'elf-scan-truncated'):
                reader.finish_scan()
        for action in (lambda r: r.finish_scan(), lambda r: r.feed(0, b'x')):
            reader, result, phases = collect(rich())
            with self.assertRaisesRegex(E.Refusal, 'elf-scan-state'):
                action(reader)
            with self.assertRaises(E.Refusal):
                reader.result()

    def test_exact_input_types_offsets_and_no_conversion_callbacks(self):
        class Hostile:
            def __len__(self):
                raise AssertionError('unexpected len callback')
            def __bytes__(self):
                raise AssertionError('unexpected bytes callback')
        class IntSubclass(int):
            pass
        class BytesSubclass(bytes):
            pass
        class BudgetSubclass(E.Budget):
            pass
        for size in (True, 63, E.MEMBER_LIMIT+1, '64', IntSubclass(64), Hostile()):
            with self.assertRaises(E.Refusal):
                E.Collector(size)
        for budget in (False, {}, Hostile(), BudgetSubclass()):
            with self.assertRaises(E.Refusal):
                E.Collector(64, budget)
        for offset, chunk in ((True, b'x'), (1, b'x'), (-1, b'x'), (IntSubclass(0), b'x'),
                              (0, Hostile()), (0, bytearray(b'x')), (0, BytesSubclass(b'x')),
                              (0, b''), (0, bytes(65537)), (0, bytes(65))):
            reader = E.Collector(64)
            with self.assertRaises(E.Refusal):
                reader.feed(offset, chunk)
            self.assertEqual('refused', reader.phase)
        reader = E.Collector(64)
        reader.feed(0, b'x')
        with self.assertRaisesRegex(E.Refusal, 'elf-scan-offset'):
            reader.feed(0, b'x')

    def test_bounded_owned_scratch_shared_budget_and_release(self):
        budget = E.Budget()
        data = rich(needed=(0,)*64, paths=(0, 0), table=b'x'*240+b'\0')
        first, second = E.Collector(len(data), budget), E.Collector(len(data), budget)
        for phase in range(3):
            scan(first, data, 7)
            scan(second, data, 63)
        self.assertEqual(S.elf(data), first.result())
        self.assertEqual(S.elf(data), second.result())
        retained, peak, work = budget.usage
        self.assertEqual(0, retained)
        self.assertLessEqual(peak, 2*66*241)
        self.assertEqual(len(data)*6, work)
        # A maximum-size body is hashed, not retained. This is accounting, not
        # an RLIMIT_AS/physical package-fit experiment.
        large = image(size=E.MEMBER_LIMIT)
        collector, result, phases = collect(large, budget=budget)
        self.assertEqual(S.elf(large), result)
        self.assertLessEqual(budget.usage[1], 2*66*241)
        # A failed third reservation must not release another collector's bytes.
        budget = E.Budget()
        with patch.object(E, 'SCRATCH_LIMIT', 128):
            first, second = E.Collector(64, budget), E.Collector(64, budget)
            with self.assertRaisesRegex(E.Refusal, 'elf-scratch-budget'):
                E.Collector(64, budget)
            self.assertEqual(128, budget.usage[0])
            first.close()
            self.assertEqual(64, budget.usage[0])
            second.close()
            self.assertEqual(0, budget.usage[0])

    def test_scratch_and_work_refusals_do_not_return_credit(self):
        with patch.object(E, 'SCRATCH_LIMIT', 64):
            reader = E.Collector(4096)
            with self.assertRaisesRegex(E.Refusal, 'elf-scratch-budget'):
                scan(reader, rich())
            self.assertEqual(0, reader._budget.usage[0])
        with patch.object(E, 'WORK_LIMIT', 4096):
            reader = E.Collector(4096)
            scan(reader, rich())
            with self.assertRaisesRegex(E.Refusal, 'elf-work-budget'):
                reader.feed(0, b'x')
            self.assertEqual(4097, reader._budget.usage[2])
            self.assertEqual(0, reader._budget.usage[0])

    def test_allocation_failure_close_and_result_copy(self):
        budget = E.Budget()
        with patch.object(E, 'bytearray', side_effect=MemoryError, create=True):
            with self.assertRaises(MemoryError):
                E.Collector(4096, budget)
        self.assertEqual(0, budget.usage[0])
        reader = E.Collector(4096, budget)
        scan(reader, rich())
        with patch.object(E, 'bytearray', side_effect=MemoryError, create=True):
            with self.assertRaises(MemoryError):
                scan(reader, rich())
        self.assertEqual(0, budget.usage[0])
        # Failure after one interpreter buffer was allocated inside the list
        # construction must release exactly this collector's reservation too.
        reader = E.Collector(4096, budget)
        calls = []
        def allocate(count):
            calls.append(count)
            if len(calls) == 3:
                raise MemoryError
            return bytearray(count)
        data = image([(3, 512, 2, 0), (3, 1024, 2, 0)], [(512, b'x\0'), (1024, b'y\0')])
        with patch.object(E, 'bytearray', side_effect=allocate, create=True):
            with self.assertRaises(MemoryError):
                scan(reader, data)
        self.assertEqual(0, budget.usage[0])
        self.assertEqual('refused', reader.phase)
        reader, expected, phases = collect(rich())
        reader.result()['needed'].clear()
        self.assertEqual(expected, reader.result())
        reader.close()
        reader.close()
        self.assertEqual(0, reader._budget.usage[0])
        with self.assertRaises(E.Refusal):
            reader.result()

    def test_seeded_positive_and_mutated_oracle_differential(self):
        randomizer = random.Random(8512)
        for index in range(80):
            names = [randomizer.choice((b'', b'a', b'long-name', b'/path'))+b'\0' for _ in range(4)]
            offsets, table = [], b''
            for name in names:
                offsets.append(len(table))
                table += name
            data = rich(needed=tuple(randomizer.choices(offsets, k=randomizer.randrange(1, 8))),
                        paths=tuple(randomizer.choices(offsets, k=randomizer.randrange(3))), table=table,
                        pht=3500 if index % 2 else 64)
            self.parity(data, (randomizer.choice(CHUNKS),))
            altered = bytearray(data)
            altered[randomizer.randrange(len(altered))] ^= 255
            try:
                expected = S.elf(bytes(altered))
            except (S.Refusal, UnicodeDecodeError):
                with self.assertRaises(E.Refusal):
                    collect(bytes(altered), 63)
            else:
                self.assertEqual(expected, collect(bytes(altered), 63)[1])

    def test_no_io_imports_or_runtime_wiring(self):
        path = ROOT/'scripts/ci/source_policy_elf_ranges.py'
        tree = ast.parse(path.read_text())
        imports = [alias.name for node in ast.walk(tree) if isinstance(node, ast.Import) for alias in node.names]
        self.assertEqual(['hashlib', 'struct'], imports)
        self.assertFalse(any(isinstance(node, ast.ImportFrom) for node in ast.walk(tree)))
        calls = {node.func.id for node in ast.walk(tree) if isinstance(node, ast.Call) and isinstance(node.func, ast.Name)}
        self.assertFalse(calls & {'open', 'eval', 'exec', '__import__', 'compile'})
        for relative in ('scripts/ci/source_policy_deb_data.py', 'scripts/ci/source-policy-ppa-deb-static.py',
                         '.github/workflows/source-policy-ppa-deb-static.yml', 'alynt-drime-backups-dashboard.php'):
            self.assertNotIn('source_policy_elf_ranges', (ROOT/relative).read_text())

    def test_semantic_controls_are_assertion_killed_not_setup_errors(self):
        strings = E.Collector._strings
        require = E.require
        controls = (
            (E.Collector, '_scan_identity', lambda self: None, 'test_replay_hash_checks_unselected_tail_bytes'),
            (E.Collector, '_scan_identity', lambda self: None, 'test_partial_result_and_repeated_finish_are_terminal'),
            (E.Collector, '_strings', lambda self: (sorted(strings(self)[0]), strings(self)[1]), 'test_nonempty_ordered_dependencies_paths_and_digest'),
            (E.Budget, '_reserve', lambda self, count: None, 'test_scratch_and_work_refusals_do_not_return_credit'),
            (E.Budget, '_charge', lambda self, count: None, 'test_scratch_and_work_refusals_do_not_return_credit'),
            (E.Collector, 'result', lambda self: {}, 'test_partial_result_and_repeated_finish_are_terminal'),
            (E, 'require', lambda ok, code: None if code == 'elf-segment-bound' else require(ok, code), 'test_header_and_every_segment_file_extent'),
        )
        # Each selected control must produce an assertion failure, not import or
        # fixture errors. No source is changed or expected result weakened.
        for owner, method, replacement, name in controls:
            with patch.object(owner, method, replacement):
                result = unittest.TestResult()
                Checks(name).run(result)
            self.assertGreater(len(result.failures), 0, name)
            self.assertEqual([], result.errors, name)


if __name__ == '__main__':
    result = unittest.TestResult()
    unittest.defaultTestLoader.loadTestsFromTestCase(Checks).run(result)
    print(json.dumps({'tests': result.testsRun, 'failures': len(result.failures),
                      'errors': len(result.errors), 'skips': len(result.skipped)}))
    if not result.wasSuccessful():
        print(result.failures + result.errors, file=sys.stderr)
        raise SystemExit(1)
