"""Dormant fixed output-owner policy. Native storage is unconditionally disabled.

Tests replace the PRIVATE storage boundary with a RAM model, never a caller
provider. No parser/source join, public factory, syscall adapter or launch path.
An output seal is only policy preparation, not source or execution authority.
"""
import hashlib
import json
import re
import stat

import source_policy_deb_data as D
import source_policy_elf_ranges as E


_CATALOG = (
    ('cli', 'php8.5-cli', 'usr/bin/php8.5', 'php', 12282976,
     'eb85560c0bea6c5d1c06a93a602697efe94ad9803da400211469529f041929fa'),
    ('mysqlnd', 'php8.5-mysql', 'usr/lib/php/20250925/mysqlnd.so', 'mysqlnd.so', 218952,
     '837b346b8f11c34669bca7874bfc027c866ebe7ae1d954c2e496ce83341d0b20'),
    ('mysqli', 'php8.5-mysql', 'usr/lib/php/20250925/mysqli.so', 'mysqli.so', 170248,
     '6dfd427a916d649b7404d4f9c9aea94caaa1821d08c7c7c8852606d42c35fcd5'),
)


class _DisabledStorage:
    def profile(self):
        raise D.S.Refusal('prefix-native-disabled')


_STORAGE = _DisabledStorage()


def _guarded(method):
    def operation(self, *args):
        try:
            return method(self, *args)
        except BaseException:
            self._failed = True
            raise
    return operation


def _identity(info):
    return info.st_dev, info.st_ino


class _OutputOwner:
    """Internal lifecycle only. Never accepts catalog, storage or source facts.

    The future fixed trusted owner must construct this behind job/source/root
    admission. No current workflow imports it. Storage contracts are modeled,
    not evidence of POSIX atomic creation, no-follow or crash recovery.
    """

    def _storage_owner(self, budget, scratch):
        return _STORAGE

    def _cleanup_storage(self):
        pass  # RAM/default owner has no backend cleanup clock.

    def __init__(self, parent, basename, marker, budget, scratch):
        D.S.require(type(budget) is D.Budget and type(scratch) is E.Budget, 'prefix-original-budget')
        D.S.require(type(parent) is str and parent.startswith('/') and parent != '/'
                    and all(part not in ('', '.', '..') for part in parent[1:].split('/'))
                    and type(basename) is str and re.fullmatch(r'adb-ppa-runtime-[0-9]+', basename)
                    and type(marker) is bytes and 0 < len(marker) <= 256, 'prefix-fixed-root')
        try:
            value = json.loads(marker.decode('ascii'))
        except (ValueError, UnicodeError):
            raise D.S.Refusal('prefix-marker') from None
        D.S.require(type(value) is dict and set(value) == {'head', 'root'}
                    and value['root'] == basename and type(value['head']) is str
                    and re.fullmatch('[a-f0-9]{40}', value['head'])
                    and marker == json.dumps(value, sort_keys=True).encode('ascii'), 'prefix-marker')
        self._storage, self._budget, self._scratch = self._storage_owner(budget, scratch), budget, scratch
        self._uid = self._storage.profile()  # Closed BEFORE any resource access.
        D.S.require(type(self._uid) is int and self._uid >= 0, 'prefix-owner-profile')
        self._parent, self._basename = parent, basename
        self._root, self._root_identity, self._active = None, None, None
        self._files, self._complete = {}, set()
        self._uncertain, self._closed, self._sealed, self._failed = False, False, False, False
        claim_started = False
        try:
            budget.charge(0)
            budget.entry()
            claim_started = True
            self._root = self._storage.claim(parent, basename, 0o700, exclusive=True, nofollow=True)
            self._root_identity = _identity(self._storage.info(self._root))
            self._check_root()
            self._write_marker(marker)
        except BaseException:
            if not claim_started:
                self._closed = True
                raise
            # A claim with no returned identity cannot be recovered by pathname.
            if self._root is None:
                self._uncertain = True
            self.cleanup()
            raise

    def _live(self):
        D.S.require(not self._closed and not self._uncertain and not self._sealed and not self._failed, 'prefix-unavailable')
        self._budget.charge(0)
        self._check_root()

    def _check_root(self):
        info = self._storage.info(self._root)
        bound = self._storage.lookup(self._parent, self._basename)
        D.S.require(_identity(info) == _identity(bound) == self._root_identity
                    and stat.S_ISDIR(info.st_mode) and info.st_uid == self._uid
                    and stat.S_IMODE(info.st_mode) == 0o700, 'prefix-root-binding')

    def _check_file(self, leaf, handle, size, mode):
        info = self._storage.info(handle)
        bound = self._storage.lookup(self._root, leaf)
        D.S.require(_identity(info) == _identity(bound) == self._files[leaf]
                    and stat.S_ISREG(info.st_mode) and info.st_uid == self._uid
                    and info.st_nlink == 1 and info.st_size == size
                    and stat.S_IMODE(info.st_mode) == mode
                    and self._storage.inheritable(handle) is False, 'prefix-file-binding')

    def _create(self, leaf):
        D.S.require(leaf not in self._files, 'prefix-duplicate')
        self._budget.entry()
        handle = self._storage.create(self._root, leaf, 0o600, exclusive=True, nofollow=True, inheritable=False)
        # The modeled storage owns provisional handles until retire; no handles
        # or paths are ever returned by this policy owner.
        self._active = handle
        self._files[leaf] = _identity(self._storage.info(handle))
        self._check_file(leaf, handle, 0, 0o600)
        D.S.require(self._storage.access(handle) == 'write', 'prefix-write-access')
        return handle

    def _write(self, handle, chunk):
        self._scratch._reserve(len(chunk))
        try:
            offset = 0
            while offset < len(chunk):
                self._budget.charge(0)
                count = self._storage.write(handle, memoryview(chunk)[offset:])
                D.S.require(type(count) is int and 0 < count <= len(chunk)-offset, 'prefix-write-progress')
                self._budget.charge(count)
                offset += count
        finally:
            self._scratch._retained -= len(chunk)

    def _retire(self, handle):
        # A failed/ambiguous close is never retried on a possibly reused handle.
        self._active = None
        try:
            D.S.require(self._storage.close(handle) is None, 'prefix-close-result')
        except BaseException:
            self._uncertain = True
            raise

    def _write_marker(self, marker):
        handle = self._create('owner.json')
        self._write(handle, marker)
        self._check_file('owner.json', handle, len(marker), 0o600)
        self._retire(handle)
        self._marker = marker

    @_guarded
    def begin(self, role, package, member, kind, size):
        self._live()
        D.S.require(self._active is None, 'prefix-active-output')
        row = next((row for row in _CATALOG if row[0] == role), None)
        D.S.require(row is not None and all(type(item) is str for item in (role, package, member))
                    and type(size) is int and (package, member, size) == (row[1], row[2], row[4])
                    and type(kind) is bytes and kind in (b'0', b'\0'), 'prefix-selected-member')
        handle = self._create(row[3])
        self._current = row, handle, hashlib.sha256(), 0

    @_guarded
    def feed(self, chunk):
        self._live()
        D.S.require(self._active is not None and hasattr(self, '_current')
                    and type(chunk) is bytes and 0 < len(chunk) <= 65536, 'prefix-chunk')
        row, handle, digest, size = self._current
        D.S.require(size + len(chunk) <= row[4], 'prefix-member-size')
        self._write(handle, chunk)
        digest.update(chunk)
        self._current = row, handle, digest, size+len(chunk)

    @_guarded
    def finish(self):
        self._live()
        D.S.require(self._active is not None and hasattr(self, '_current'), 'prefix-active-output')
        row, handle, digest, size = self._current
        D.S.require(size == row[4] and digest.hexdigest() == row[5], 'prefix-member-identity')
        self._check_file(row[3], handle, size, 0o600)
        self._retire(handle)
        self._complete.add(row[0])
        del self._current

    def _readback(self, leaf, size, expected):
        handle = self._storage.readonly(self._root, leaf, nofollow=True, inheritable=False)
        self._active = handle
        self._check_file(leaf, handle, size, 0o400)
        D.S.require(self._storage.access(handle) == 'read', 'prefix-readonly-access')
        digest, offset = hashlib.sha256(), 0
        while offset < size:
            request = min(65536, size-offset)
            self._scratch._reserve(request)
            try:
                chunk = self._storage.read(handle, request)
                D.S.require(type(chunk) is bytes and 0 < len(chunk) <= request, 'prefix-readback-progress')
                self._budget.charge(len(chunk))
                digest.update(chunk)
                offset += len(chunk)
            finally:
                chunk = None
                self._scratch._retained -= request
        self._scratch._reserve(1)
        try:
            extra = self._storage.read(handle, 1)
            D.S.require(type(extra) is bytes and len(extra) <= 1, 'prefix-readback-progress')
            self._budget.charge(len(extra))
            D.S.require(not extra and digest.hexdigest() == expected, 'prefix-readback-identity')
        finally:
            extra = None
            self._scratch._retained -= 1
        self._check_file(leaf, handle, size, 0o400)
        self._retire(handle)

    @_guarded
    def seal(self):
        self._live()
        D.S.require(self._active is None and self._complete == {row[0] for row in _CATALOG}, 'prefix-incomplete')
        expected = {'owner.json', *(row[3] for row in _CATALOG)}
        D.S.require(self._storage.members(self._root, 4) == expected, 'prefix-membership')
        sizes = {'owner.json': len(self._marker), **{row[3]: row[4] for row in _CATALOG}}
        for leaf in sorted(expected):
            info = self._storage.lookup(self._root, leaf)
            D.S.require(_identity(info) == self._files[leaf] and stat.S_ISREG(info.st_mode)
                        and info.st_uid == self._uid and info.st_nlink == 1
                        and info.st_size == sizes[leaf] and stat.S_IMODE(info.st_mode) == 0o600,
                        'prefix-pre-mode-binding')
        for leaf in sorted(expected):
            # Metadata is checked again through the new read-only handle.
            self._storage.readonly_mode(self._root, leaf, self._files[leaf], 0o400)
        self._readback('owner.json', len(self._marker), hashlib.sha256(self._marker).hexdigest())
        for row in _CATALOG:
            self._readback(row[3], row[4], row[5])
        self._check_root()
        D.S.require(self._storage.members(self._root, 4) == expected, 'prefix-membership')
        self._budget.charge(0)
        D.S.require(self._active is None and self._scratch.usage[0] == 0, 'prefix-release-barrier')
        self._sealed = True
        return {'prepared_only': True, 'output_policy_sealed': True, 'materialized': False,
                'qualified': False, 'executed': False, 'dependency_closure_verified': False,
                'source_joined': False, 'native_enabled': False}

    def cleanup(self):
        if self._closed:
            D.S.require(not self._uncertain, 'prefix-cleanup-unknown')
            return
        self._closed = True  # Invalidate operations/seals even on cleanup failure.
        try:
            self._cleanup_storage()
            if self._active is not None:
                self._retire(self._active)
            D.S.require(not self._uncertain, 'prefix-cleanup-unknown')
            self._check_root()
            D.S.require(self._storage.members(self._root, 4) == set(self._files), 'prefix-cleanup-membership')
            for leaf, identity in self._files.items():
                info = self._storage.lookup(self._root, leaf)
                D.S.require(_identity(info) == identity and stat.S_ISREG(info.st_mode)
                            and info.st_uid == self._uid and info.st_nlink == 1
                            and stat.S_IMODE(info.st_mode) in (0o600, 0o400), 'prefix-cleanup-binding')
            for leaf in reversed(tuple(self._files)):
                self._check_root()
                D.S.require(self._storage.remove(self._root, leaf, self._files[leaf]) is None, 'prefix-remove-result')
            D.S.require(self._storage.members(self._root, 4) == set(), 'prefix-cleanup-residue')
            D.S.require(self._storage.remove_root(self._parent, self._basename, self._root, self._root_identity) is None,
                        'prefix-remove-root-result')
            D.S.require(self._storage.absent(self._parent, self._basename) is True, 'prefix-cleanup-root-residue')
        except BaseException:
            self._uncertain = True
            raise D.S.Refusal('prefix-cleanup-unknown') from None
