"""Dormant pure-DATA ELF subset reader. No IO, artifact admission or execution.

Feed complete member scans in order (primary, metadata, strings as required).
The unchanged shared S.elf remains the external differential oracle. Matching
member hashes do NOT establish package, tar, decoder or source provenance.
"""
import hashlib
import struct

MIB = 1024 * 1024
MEMBER_LIMIT = 16 * MIB
SCRATCH_LIMIT = MIB
WORK_LIMIT = 192 * MIB
CHUNK_LIMIT = 65536


class Refusal(ValueError):
    """Fixed non-secret reason; no input bytes in diagnostics."""


def require(condition, reason):
    if not condition:
        raise Refusal(reason)


class Budget:
    """Shared raw-buffer/work accounting, not process memory or deadline proof."""

    def __init__(self):
        self._retained = self._peak = self._work = 0

    @property
    def usage(self):
        return (self._retained, self._peak, self._work)

    def _reserve(self, count):
        require(self._retained + count <= SCRATCH_LIMIT, 'elf-scratch-budget')
        self._retained += count
        self._peak = max(self._peak, self._retained)

    def _charge(self, count):
        self._work += count
        require(self._work <= WORK_LIMIT, 'elf-work-budget')


class Collector:
    """Bounded, fail-terminal member collector; result is member facts only.

    Only exact ints/bytes/Budget are accepted. No caller-provided ranges, hashes,
    readiness flags or callbacks. Two collectors can share one Budget; the later
    owning integration must also charge all other reads/entries/deadlines.
    """

    def __init__(self, size, budget=None):
        require(type(size) is int and 64 <= size <= MEMBER_LIMIT, 'elf-member-bound')
        require(budget is None or type(budget) is Budget, 'elf-budget-type')
        self._budget = budget if budget is not None else Budget()
        self._size, self._held, self._phase = size, 0, 'primary'
        self._header = self._table = None
        self._interpreters, self._windows = [], []
        self._carry = None
        self._expected = self._facts = None
        self._offset, self._digest = 0, hashlib.sha256()
        self._pht = None
        try:
            self._header = self._allocate(64)
        except Exception:
            self.close()
            raise

    @property
    def phase(self):
        return self._phase

    def _allocate(self, count):
        self._budget._reserve(count)
        self._held += count
        return bytearray(count)

    def _release(self):
        self._header = self._table = self._carry = None
        self._interpreters, self._windows = [], []
        self._budget._retained -= self._held
        self._held = 0

    def close(self):
        self._release()
        self._facts = None
        self._phase = 'refused'

    @staticmethod
    def _capture(buffer, start, offset, chunk):
        left, right = max(start, offset), min(start + len(buffer), offset + len(chunk))
        if left < right:
            buffer[left-start:right-start] = memoryview(chunk)[left-offset:right-offset]

    def _header_plan(self):
        header = self._header
        require(header[:7] == b'\x7fELF\x02\x01\x01', 'elf-header')
        kind, machine = struct.unpack_from('<HH', header, 16)
        offset = struct.unpack_from('<Q', header, 32)[0]
        width, count = struct.unpack_from('<HH', header, 54)
        require(kind in (2, 3) and machine == 62 and width == 56 and count <= 128
                and offset + width * count <= self._size, 'elf-program-table')
        self._kind, self._pht = kind, (offset, count)
        self._table = self._allocate(width * count)
        self._capture(self._table, offset, 0, header)

    def feed(self, offset, chunk):
        try:
            require(self._phase in ('primary', 'metadata', 'strings'), 'elf-scan-state')
            require(type(offset) is int and offset == self._offset, 'elf-scan-offset')
            require(type(chunk) is bytes and 0 < len(chunk) <= CHUNK_LIMIT,
                    'elf-chunk-bound')
            require(offset + len(chunk) <= self._size, 'elf-scan-overrun')
            self._budget._charge(len(chunk))
            self._digest.update(chunk)
            if self._phase == 'primary':
                self._capture(self._header, 0, offset, chunk)
                if self._pht is None and offset + len(chunk) >= 64:
                    self._header_plan()
                if self._pht is not None:
                    self._capture(self._table, self._pht[0], offset, chunk)
            elif self._phase == 'metadata':
                for start, buffer in self._interpreters:
                    self._capture(buffer, start, offset, chunk)
                self._dynamic_chunk(offset, chunk)
            else:
                for start, buffer in self._windows:
                    self._capture(buffer, start, offset, chunk)
            self._offset += len(chunk)
        except Exception:
            self.close()
            raise

    def _program_plan(self):
        loads, interpreters, dynamic = [], [], None
        for index in range(self._pht[1]):
            ptype, flags, start, address, physical, length, mem, align = struct.unpack_from(
                '<IIQQQQQQ', self._table, index * 56)
            require(start + length <= self._size, 'elf-segment-bound')
            if ptype == 1:
                loads.append((address, start, length))
            elif ptype == 2:
                require(length <= MIB and length % 16 == 0 and dynamic is None,
                        'elf-dynamic-bound')
                dynamic = (start, length)
            elif ptype == 3:
                require(0 < length <= 240, 'elf-interpreter')
                interpreters.append((start, length))
        self._loads, self._dynamic = loads, dynamic
        self._tags, self._needed, self._paths = {}, [], []
        self._ended, self._carry_used = False, 0
        self._interpreter = None
        self._release()
        self._interpreters = [(start, self._allocate(length)) for start, length in interpreters]
        if dynamic is not None:
            self._carry = self._allocate(16)
        return bool(interpreters) or dynamic is not None

    def _dynamic_chunk(self, offset, chunk):
        if self._dynamic is None or self._ended:
            return
        start, length = self._dynamic
        left, right = max(start, offset), min(start + length, offset + len(chunk))
        view = memoryview(chunk)
        while left < right and not self._ended:
            count = min(16 - self._carry_used, right - left)
            self._carry[self._carry_used:self._carry_used+count] = view[left-offset:left-offset+count]
            self._carry_used += count
            left += count
            if self._carry_used == 16:
                tag, value = struct.unpack_from('<qQ', self._carry)
                self._carry_used = 0
                if tag == 0:
                    self._ended = True
                elif tag == 1:
                    require(len(self._needed) < 64, 'elf-dynamic-shape')
                    self._needed.append(value)
                elif tag in (15, 29):
                    require(len(self._paths) < 2, 'elf-dynamic-shape')
                    self._paths.append(value)
                elif tag in (5, 10):
                    require(tag not in self._tags, 'elf-duplicate-string-table')
                    self._tags[tag] = value

    def _string_plan(self):
        require(self._dynamic is None or self._ended, 'elf-dynamic-shape')
        for start, buffer in self._interpreters:
            require(buffer[-1] == 0, 'elf-interpreter')
            try:
                self._interpreter = str(memoryview(buffer)[:-1], 'ascii')
            except UnicodeDecodeError:
                raise Refusal('elf-interpreter-ascii') from None
        refs = self._needed + self._paths
        ranges = []
        if refs:
            require(5 in self._tags and 10 in self._tags and self._tags[10] <= MIB,
                    'elf-string-table')
            address, size = self._tags[5], self._tags[10]
            mapped = [start + address - base for base, start, length in self._loads
                      if base <= address and address + size <= base + length]
            require(len(mapped) == 1, 'elf-string-mapping')
            for index in refs:
                require(index < size, 'elf-string-index')
                ranges.append((mapped[0] + index, min(241, size - index)))
        self._release()
        self._windows = [(start, self._allocate(length)) for start, length in ranges]
        return bool(refs)

    def _strings(self):
        values = []
        for start, buffer in self._windows:
            end = buffer.find(0)
            require(end >= 0, 'elf-string-length')
            try:
                value = str(memoryview(buffer)[:end], 'ascii')
            except UnicodeDecodeError:
                raise Refusal('elf-string-ascii') from None
            require(all(32 <= ord(char) <= 126 for char in value), 'elf-string-ascii')
            values.append(value)
        return values[:len(self._needed)], values[len(self._needed):]

    def _scan_identity(self):
        require(self._offset == self._size, 'elf-scan-truncated')
        observed = self._digest.hexdigest()
        require(self._expected is None or observed == self._expected, 'elf-replay-identity')
        self._expected = observed

    def finish_scan(self):
        try:
            require(self._phase in ('primary', 'metadata', 'strings'), 'elf-scan-state')
            self._scan_identity()
            if self._phase == 'primary' and self._program_plan():
                next_phase = 'metadata'
            elif self._phase == 'metadata' and self._string_plan():
                next_phase = 'strings'
            else:
                needed, paths = self._strings() if self._phase == 'strings' else ([], [])
                self._facts = (self._interpreter, tuple(needed), tuple(paths))
                self._release()
                self._phase = 'done'
                return self._phase
            self._phase = next_phase
            self._offset, self._digest = 0, hashlib.sha256()
            return self._phase
        except Exception:
            self.close()
            raise

    def result(self):
        try:
            require(self._phase == 'done', 'elf-result-unavailable')
            interpreter, needed, paths = self._facts
            return {'machine': 'x86_64', 'bits': 64, 'type': self._kind,
                    'interpreter': interpreter, 'needed': list(needed), 'search_paths': list(paths),
                    'sha256': self._expected, 'bytes': self._size,
                    'dependency_closure_verified': False, 'executed': False}
        except Exception:
            self.close()
            raise
