"""Dormant fixed two-pass join. Default entry and concrete storage stay CLOSED.

No receipt/reader/provider arguments. Tests replace private entry/syscall/codec
boundaries only. Results remain preparation DATA, never physical qualification.
"""
import hashlib
import json
import re
import sys

import source_policy_deb_replay as R
import source_policy_deb_sources as L
import source_policy_ppa_prefix as P
import source_policy_ppa_storage as N

D, E = R.D, R.E
_KEY = object()


class _ClosedEntry:
    def context(self):
        raise D.S.Refusal('prefix-join-entry-disabled')


_ENTRY = _ClosedEntry()


class _Context:
    """Private origin. No active workflow constructs it; not a JSON capability."""
    def __init__(self, key, parent, head, run, budget, scratch):
        D.S.require(key is _KEY and type(parent) is str and parent.startswith('/') and parent != '/'
                    and all(part not in ('', '.', '..') for part in parent[1:].split('/'))
                    and type(head) is str and re.fullmatch('[a-f0-9]{40}', head)
                    and type(run) is str and re.fullmatch('[0-9]+', run)
                    and type(budget) is D.Budget and type(scratch) is E.Budget, 'join-context')
        self._parent, self._head, self._run = parent, head, run
        self._origin = parent, head, run
        self._budget, self._scratch, self._pins, self._catalog = budget, scratch, D.PINS, P._CATALOG
        self._source_binding, self._source_count, self._storage = None, 0, None
        self._cleanup_deadline = None
        self._decoder_unknown = False
        self._entered = False

    def check(self):
        self._budget.charge(0)
        D.S.require(self._pins == D.PINS and self._catalog == P._CATALOG, 'join-context-changed')
        D.S.require((self._parent, self._head, self._run) == self._origin, 'join-context-changed')

    def marker(self, basename):
        return json.dumps({'head': self._head, 'root': basename}, sort_keys=True).encode('ascii')

    def cleanup_deadline(self):
        if self._cleanup_deadline is None:
            self._cleanup_deadline = L._API.clock()+30
        return self._cleanup_deadline

    def retirement_deadline(self):
        if sys.exc_info()[0] is not None or D.time.monotonic() >= self._budget.deadline:
            return self.cleanup_deadline()
        return self._cleanup_deadline if self._cleanup_deadline is not None else self._budget.deadline

    def close_sources(self, sources):
        sources.close(_deadline=self.retirement_deadline())

    def close_decoder(self, decoder):
        try:
            decoder.close(_deadline=self.retirement_deadline())
            if decoder.process.poll() is None:
                self._decoder_unknown = True
                raise D.S.Refusal('join-decoder-release')
        except BaseException:
            self._decoder_unknown = True
            raise

    def sources(self):
        self.check()
        D.S.require(self._source_count < 2, 'join-source-rounds')
        name = 'adb-ppa-deb-'+self._run
        names = tuple(pin[0]+'_'+D.REVISION+'_amd64.deb' for pin in self._pins)
        sources = L._SourceSet(self._parent, name, self.marker(name), names, self._budget, self._scratch)
        try:
            binding = (P._identity(sources._parent_info), L._fingerprint(sources._root_info))
            D.S.require(self._source_binding is None or self._source_binding == binding, 'join-source-binding')
            self._source_binding = binding
            self._source_count += 1
            return sources
        except BaseException:
            self.close_sources(sources)
            raise


class _FirstPass(R._LeasePreparation):
    def __init__(self, context):
        self._context, self._streams = context, {}
        super().__init__(context.sources())

    def _checked_scan(self, source, member, package, category, primary):
        result = super()._checked_scan(source, member, package, category, primary)
        if primary:
            self._streams[package, category] = result[0]
        return result

    def _release_sources(self):
        self._context.close_sources(self._sources)

    def _release_decoder(self, decoder):
        self._context.close_decoder(decoder)

    def run(self):
        result = super().run()  # Existing close-before-commit barrier retained.
        D.S.require(self._sources._closed and not self._sources._uncertain
                    and len(self._streams) == 6 and result['prepared_only'] is True, 'join-first-release')
        for role, package, member, leaf, size, digest in self._context._catalog:
            actual = result['roles'].get(role, {})
            D.S.require((actual.get('package'), actual.get('path'), actual.get('bytes'), actual.get('sha256'))
                        == (package, member, size, digest), 'join-first-catalog')
        return _Summary(_KEY, self._context, tuple(sorted(self._streams.items())))


class _Summary:
    def __init__(self, key, origin, streams):
        D.S.require(key is _KEY and type(origin) is _Context and len(streams) == 6, 'join-summary-origin')
        self._origin, self._streams = origin, streams


class _JoinedOutput(P._OutputOwner):
    def __init__(self, context):
        self._context = context
        name = 'adb-ppa-runtime-'+context._run
        super().__init__(context._parent, name, context.marker(name), context._budget, context._scratch)

    def _storage_owner(self, budget, scratch):
        context = self._context
        context.check()
        D.S.require(context._source_count == 2 and context._storage is None, 'join-output-origin')
        storage = N._Storage(budget, scratch)
        storage._expected_parent = context._source_binding[0]
        context._storage = storage  # Before profile/claim can acquire resources.
        return storage

    def _cleanup_storage(self):
        storage = self._context._storage
        if storage._ready and storage._cleanup_deadline is None:
            storage.start_cleanup(self._context.cleanup_deadline())


def _stream(context, sources, index, member, category, output, expected):
    context.check()
    sources.check(True)
    sources.identity(index)
    package = context._pins[index][0]
    name, start, length = member
    raw = D.Slice(sources.cursor(index), start, length, context._budget)
    suffix = name[len(category+'.tar'):]
    decoder = R.C.Decoder(raw, suffix, context._budget) if suffix else None
    reader = R._Transcript(decoder or raw, context._budget)
    ledger, count, control, held, payload = hashlib.sha256(), 0, None, 0, None
    try:
        for event, value in D.walk_tar(reader, context._budget, package, category):
            if event == 'header':
                path, kind, size = value
                count += 1
            elif event == 'start':
                role, selected = value
                offset = 0
                if role:
                    output.begin(role, package, path, kind, size)
                elif selected:
                    context._scratch._reserve(size)
                    held, payload = size, bytearray(size)
            elif event == 'chunk':
                if role:
                    output.feed(value)
                elif payload is not None:
                    payload[offset:offset+len(value)] = value
                offset += len(value)
            else:
                digest, link = value
                ledger.update(json.dumps([package, category, path, kind.hex(), size, digest, link], separators=(',', ':')).encode('ascii')+b'\n')
                if role:
                    output.finish()
                elif payload is not None:
                    control = D.control(payload, package)
                    payload = None
                    context._scratch._retained -= held
                    held = 0
        facts = (*reader.facts(), count, ledger.hexdigest())
        D.S.require(category != 'control' or control is not None, 'join-control-missing')
        D.S.require(raw.remaining == 0 and facts == expected, 'join-stream-transcript')
    finally:
        payload = None
        context._scratch._retained -= held
        if decoder is not None:
            context.close_decoder(decoder)
    if decoder is not None:
        D.S.require(decoder.process.poll() is not None and type(decoder.process.returncode) is int
                    and decoder.process.returncode == 0, 'join-decoder-release')
    sources.identity(index)
    sources.check(True)


def _second_pass(context, summary):
    D.S.require(type(summary) is _Summary and summary._origin is context, 'join-summary-origin')
    expected = dict(summary._streams)
    sources, output = context.sources(), None
    try:
        identities = sources.seal()  # All three before parser/output construction.
        output = _JoinedOutput(context)
        try:
            for index, pin in enumerate(context._pins):
                members = D.ar_members(sources.cursor(index), context._budget)
                for member, category in zip(members, ('control', 'data')):
                    _stream(context, sources, index, member, category, output, expected[pin[0], category])
            D.S.require(sources.seal() == identities, 'join-final-source-seal')
        finally:
            context.close_sources(sources)  # A close/restore failure MUST precede/deny seal.
        context.check()
        prepared = output.seal()
        D.S.require(prepared['output_policy_sealed'] is True and prepared['source_joined'] is False
                    and prepared['materialized'] is False and prepared['native_enabled'] is False, 'join-output-result')
        result = {'prepared_only': True, 'join_policy_complete': True, 'materialized': False,
                  'qualified': False, 'executed': False, 'native_enabled': False,
                  'dependency_closure_verified': False, 'source_joined': False}
    finally:
        # One cleanup clock; do not reset work deadlines or skip registered FDs.
        storage = context._storage
        try:
            if storage is not None:
                if storage._ready and storage._cleanup_deadline is None:
                    storage.start_cleanup(context.cleanup_deadline())
                try:
                    if output is not None:
                        if context._decoder_unknown:
                            output._uncertain = True  # No unlink while child release is unknown.
                        output.cleanup()
                finally:
                    storage.retire()
        finally:
            if not sources._closed:
                context.close_sources(sources)
    context.check()  # Cleanup cannot revive expired/over-budget qualification.
    return result


def _run():
    context = _ENTRY.context()  # Unconditional default refusal before source IO.
    D.S.require(type(context) is _Context and context._source_count == 0 and not context._entered, 'join-context-origin')
    context._entered = True
    summary = _FirstPass(context).run()
    return _second_pass(context, summary)
