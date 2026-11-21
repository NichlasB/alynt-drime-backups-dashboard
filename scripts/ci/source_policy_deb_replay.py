"""Dormant replay preparation over immutable input bytes, never a proof entry.

No files, acquisition or candidate execution. The fixed catalog is checked before
nested parsing. This adapter does not establish on-disk ownership or process fit.
The manual runner still uses its legacy path and does not import this module.
"""
import hashlib
import json

import source_policy_deb_data as D
import source_policy_deb_stream as C
import source_policy_elf_ranges as E


class _MemorySource:
    """Private bounded read cursor; no write handle or source callback exported."""

    def __init__(self, data):
        self._data, self._position = data, 0

    def tell(self):
        return self._position

    def seek(self, offset, whence=0):
        D.S.require(type(offset) is int and type(whence) is int and whence in (0, 1, 2), 'replay-seek-type')
        position = offset + (0 if whence == 0 else self._position if whence == 1 else len(self._data))
        D.S.require(0 <= position <= len(self._data), 'replay-seek-bound')
        self._position = position
        return position

    def read(self, count):
        D.S.require(type(count) is int and 0 <= count <= 65536, 'replay-read-bound')
        result = self._data[self._position:self._position+count]
        self._position += len(result)
        return result


class _Transcript:
    def __init__(self, reader, budget):
        self._reader, self._budget = reader, budget
        self._digest, self._size, self._eof = hashlib.sha256(), 0, False

    def read(self, count):
        self._budget.charge(0)
        value = self._reader.read(count)
        D.S.require(type(value) is bytes and len(value) <= count, 'replay-reader-shape')
        self._digest.update(value)
        self._size += len(value)
        if count and not value:
            self._eof = True
        return value

    def facts(self):
        D.S.require(self._eof, 'replay-stream-incomplete')
        return self._size, self._digest.hexdigest()


def _identity(data, pin, budget):
    package, size, expected = pin
    D.S.require(len(data) == size, 'replay-source-size')
    digest = hashlib.sha256()
    for offset in range(0, len(data), 65536):
        chunk = data[offset:offset+65536]
        budget.charge(len(chunk))
        digest.update(chunk)
    D.S.require(digest.hexdigest() == expected, 'replay-source-identity')
    return {'package': package, 'bytes': size, 'sha256': expected}


def _same_transcript(primary, replay):
    D.S.require(primary == replay, 'replay-stream-identity')


class _Preparation:
    def __init__(self, packets, pins, budget, scratch):
        self._packets, self._pins = packets, pins
        self._budget, self._scratch = budget, scratch
        self._collectors, self._selected, self._roles = {}, {}, {}
        self._primary_entries = 0

    def _scan(self, source, member, package, category, primary):
        name, start, length = member
        raw = D.Slice(source, start, length, self._budget)
        suffix = name[len(category+'.tar'):]
        decoder = C.Decoder(raw, suffix, self._budget) if suffix else None
        reader = _Transcript(decoder or raw, self._budget)
        count, control, ledger, control_held = 0, None, hashlib.sha256(), 0
        try:
            for event, value in D.walk_tar(reader, self._budget, package, category):
                if event == 'header':
                    path, kind, size = value
                    count += 1
                    if primary and category == 'data' and path != '.':
                        D.S.require(path not in self._budget.paths or kind == self._budget.paths[path] == b'5', 'package-path-collision')
                        self._budget.paths[path] = kind
                elif event == 'start':
                    role, selected = value
                    collector, payload, offset = None, None, 0
                    if role:
                        if primary:
                            D.S.require(role not in self._selected, 'duplicate-role')
                            self._selected[role] = (package, path, size)
                            self._collectors[role] = E.Collector(size, self._scratch)
                        D.S.require(self._selected.get(role) == (package, path, size), 'replay-role-shape')
                        active = self._collectors[role]
                        if active.phase != 'done':
                            collector = active
                    elif selected:
                        self._scratch._reserve(size)
                        control_held = size
                        payload = bytearray(size)  # Only the existing <=64KiB control.
                elif event == 'chunk':
                    if collector is not None:
                        collector.feed(offset, value)
                    if payload is not None:
                        payload[offset:offset+len(value)] = value
                    offset += len(value)
                else:
                    digest, link = value
                    row = json.dumps([package, category, path, kind.hex(), size, digest, link], separators=(',', ':')).encode('ascii')+b'\n'
                    ledger.update(row)
                    if primary:
                        self._budget.inventory.update(row)
                        self._budget.types[kind.hex()] = self._budget.types.get(kind.hex(), 0)+1
                    if collector is not None:
                        collector.finish_scan()
                    if payload is not None:
                        control = D.control(payload, package)
                        payload = None
                        self._scratch._retained -= control_held
                        control_held = 0
            D.S.require(category != 'control' or control is not None, 'control-missing')
            facts = (*reader.facts(), count, ledger.hexdigest())
            D.S.require(raw.remaining == 0, 'member-consumption')
        finally:
            self._scratch._retained -= control_held
            if decoder is not None:
                decoder.close()
        if decoder is not None:
            D.S.require(type(decoder.process.returncode) is int and decoder.process.returncode == 0
                        and decoder.process.poll() is not None, 'replay-decoder-release')
        if primary:
            self._primary_entries += count
        return facts, control

    def _package(self, packet, pin):
        package = pin[0]
        source = _MemorySource(packet)
        members = D.ar_members(source, self._budget)
        control_facts, control = self._scan(source, members[0], package, 'control', True)
        primary, unused = self._scan(source, members[1], package, 'data', True)
        passes = 1
        while any(c.phase != 'done' for c in self._collectors.values()):
            D.S.require(passes < 3, 'replay-pass-bound')
            _identity(packet, pin, self._budget)
            replay, unused = self._scan(source, members[1], package, 'data', False)
            _same_transcript(primary, replay)
            passes += 1
        # These remain private until every package and final barrier succeeds.
        for role, collector in self._collectors.items():
            owner, path, size = self._selected[role]
            self._roles[role] = {'package': owner, 'path': path, **collector.result(), 'versioned_symbols': 'Unknown'}
            collector.close()
        self._collectors.clear()
        return {'package': package, 'control': control, 'data_passes': passes,
                'control_entries': control_facts[2], 'data_entries': primary[2]}

    def run(self):
        try:
            # Frozen internal pins only. All three identities precede ANY parser.
            identities = [_identity(packet, pin, self._budget) for packet, pin in zip(self._packets, self._pins)]
            packages = [self._package(packet, pin) for packet, pin in zip(self._packets, self._pins)]
            return self._commit(identities, packages)
        finally:
            for collector in self._collectors.values():
                collector.close()
            self._collectors.clear()

    def _commit(self, identities, packages):
        self._budget.charge(0)
        D.S.require(set(self._roles) == {'cli', 'mysqlnd', 'mysqli'}, 'selected-roles-missing')
        D.S.require(self._scratch.usage[0] == 0, 'replay-scratch-retained')
        result = {'prepared_only': True, 'source_profile': 'immutable-input-bytes-only',
                  'static_inventory_complete': False, 'qualified': False, 'executed': False, 'extracted': False,
                  'dependency_closure_verified': False, 'identities': identities, 'packages': packages,
                  'roles': self._roles, 'primary_entries': self._primary_entries,
                  'scanned_entries': self._budget.entries, 'work_bytes': self._budget.total,
                  'scratch_peak': self._scratch.usage[1], 'types': self._budget.types,
                  'inventory_sha256': self._budget.inventory.hexdigest()}
        D.S.require(len(json.dumps(result).encode('ascii')) <= 65536, 'prepared-output-bound')
        return result


def prepare(packets):
    """Preparation adapter only. No caller pins, readers or verification facts.

    Immutable input storage is outside the collector scratch counter. A later
    owned-descriptor factory must avoid retaining whole acquired packets and
    qualify ownership, stability and actual 40/24MiB process fit separately.
    """
    D.S.require(type(packets) is tuple and len(packets) == len(D.PINS)
                and all(type(packet) is bytes for packet in packets), 'replay-input-shape')
    owner = _Preparation(packets, D.PINS, D.Budget(), E.Budget())
    try:
        return owner.run()
    except E.Refusal as error:
        raise D.S.Refusal(str(error)) from None
