"""Dormant fixed Linux descriptor leases. No opens occur on import.

Only the existing manual owner's private dormant entry constructs this set.
The active manual path is unchanged. Tests replace the private syscall boundary,
not the fixed catalog or the public API. This is not a filesystem snapshot.
"""
import hashlib
import os
import signal
import stat
import sys
import threading
import time

import source_policy_deb_data as D


class _NativeAPI:
    """Fixed syscalls, never a caller-supplied IO provider."""

    def profile(self):
        D.S.require(sys.platform == 'linux' and threading.current_thread() is threading.main_thread(), 'lease-platform')
        D.S.require(os.getuid() == os.geteuid() and os.open in os.supports_dir_fd
                    and os.stat in os.supports_dir_fd and os.scandir in os.supports_fd
                    and os.stat in os.supports_follow_symlinks and hasattr(os, 'pread')
                    and hasattr(signal, 'pthread_sigmask'), 'lease-capabilities')
        import fcntl
        self._fcntl = fcntl
        return os.getuid(), os.O_RDONLY | os.O_NOFOLLOW | os.O_CLOEXEC | os.O_NONBLOCK, os.O_DIRECTORY

    def flags(self, fd):
        return self._fcntl.fcntl(fd, self._fcntl.F_GETFL) & os.O_ACCMODE

    def mask(self):
        return signal.pthread_sigmask(signal.SIG_BLOCK, {signal.SIGALRM, signal.SIGINT})

    def restore(self, previous):
        signal.pthread_sigmask(signal.SIG_SETMASK, previous)

    open = staticmethod(os.open)
    stat = staticmethod(os.stat)
    fstat = staticmethod(os.fstat)
    inheritable = staticmethod(os.get_inheritable)
    seek = staticmethod(os.lseek)
    scan = staticmethod(os.scandir)
    close = staticmethod(os.close)
    clock = staticmethod(time.monotonic)

    def pread(self, fd, count, offset):
        return os.pread(fd, count, offset)


_API = _NativeAPI()


def _fingerprint(info):
    return tuple(getattr(info, key) for key in ('st_dev', 'st_ino', 'st_mode', 'st_uid', 'st_gid',
                 'st_nlink', 'st_size', 'st_mtime_ns', 'st_ctime_ns'))


class _Cursor:
    """Logical pread cursor: no fileno, write, descriptor duplication or reopen."""

    def __init__(self, owner, fd, size):
        self._owner, self._fd, self._size = owner, fd, size
        self._position, self._held = 0, 0

    def _live(self):
        D.S.require(not self._owner._closed, 'lease-retired')
        self._owner._budget.charge(0)

    def _release(self):
        self._owner._scratch._retained -= self._held
        self._held = 0

    def tell(self):
        self._live()
        return self._position

    def seek(self, offset, whence=0):
        self._live()
        D.S.require(type(offset) is int and type(whence) is int and whence in (0, 1, 2), 'lease-seek-type')
        position = offset + (0 if whence == 0 else self._position if whence == 1 else self._size)
        D.S.require(0 <= position <= self._size, 'lease-seek-bound')
        self._release()
        self._position = position
        return position

    def read(self, count):
        self._live()
        D.S.require(type(count) is int and 0 <= count <= 65536, 'lease-read-bound')
        self._release()
        if not count:
            return b''
        # A physical byte past the catalog size must be absent, not synthesized.
        request = min(count, self._size-self._position) if self._position < self._size else 1
        self._owner._scratch._reserve(request)
        self._held = request
        value = self._owner._api.pread(self._fd, request, self._position)
        D.S.require(type(value) is bytes and len(value) <= request, 'lease-read-shape')
        if self._position == self._size and value:
            self._owner._budget.charge(len(value))  # Refused growth is still work.
        D.S.require(bool(value) if self._position < self._size else not value, 'lease-physical-eof')
        self._position += len(value)
        return value


class _SourceSet:
    """Private owner of exactly parent/root/marker/three sources and one iterator.

    Construction arguments come only from target()/marker()/filename(), never a
    public request. Python module privacy is not a hostile-interpreter sandbox.
    """

    def __init__(self, parent, basename, marker, names, budget, scratch):
        self._api, self._budget, self._scratch = _API, budget, scratch
        self._owned, self._cursors, self._closed, self._uncertain = [], [], False, False
        self._parent_path, self._basename, self._marker_bytes = parent, basename, marker
        self._names, self._pins = names, D.PINS
        try:
            self._budget.charge(0)
            self._uid, self._flags, directory = self._api.profile()
            D.S.require(type(parent) is str and parent.startswith('/') and type(basename) is str
                        and basename.startswith('adb-ppa-deb-') and basename[12:].isdigit()
                        and type(marker) is bytes and 0 < len(marker) <= 256
                        and type(names) is tuple and len(names) == 3 and len(set(names)) == 3
                        and all(type(name) is str and '/' not in name and name not in ('', '.', '..', 'owner.json') for name in names), 'lease-fixed-shape')
            before = self._api.stat(parent, follow_symlinks=False)
            self._parent = self._open(parent, self._flags | directory)
            self._parent_info = self._api.fstat(self._parent)
            D.S.require(_fingerprint(before) == _fingerprint(self._parent_info), 'lease-parent-binding')
            self._directory(self._parent_info, False)
            before = self._api.stat(basename, dir_fd=self._parent, follow_symlinks=False)
            self._root = self._open(basename, self._flags | directory, self._parent)
            self._root_info = self._api.fstat(self._root)
            D.S.require(_fingerprint(before) == _fingerprint(self._root_info), 'lease-root-binding')
            self._directory(self._root_info, True)
            self._membership()
            self._marker, self._marker_info = self._file('owner.json', len(marker))
            self._sources = tuple(self._file(name, pin[1]) for name, pin in zip(names, self._pins))
            self.check()
        except BaseException:
            self.close()
            raise

    def _critical(self, action):
        previous = self._api.mask()
        try:
            return action()
        finally:
            try:
                self._api.restore(previous)
            except BaseException:
                self._uncertain = True
                raise D.S.Refusal('lease-mask-restore') from None

    def _open(self, name, flags, parent=None):
        item = ['fd', None]
        def acquire():
            self._owned.append(item)  # Allocate bookkeeping before the syscall.
            try:
                fd = self._api.open(name, flags, **({} if parent is None else {'dir_fd': parent}))
                D.S.require(type(fd) is int and fd >= 0, 'lease-descriptor-shape')
                item[1] = fd  # Before unmask; no post-open allocation.
                return fd
            finally:
                if item[1] is None:
                    self._owned.remove(item)
        fd = self._critical(acquire)
        D.S.require(self._api.flags(fd) == 0 and self._api.inheritable(fd) is False, 'lease-readonly')
        return fd

    def _directory(self, info, private):
        D.S.require(stat.S_ISDIR(info.st_mode) and info.st_uid == self._uid
                    and (stat.S_IMODE(info.st_mode) == 0o700 if private else not info.st_mode & 0o022), 'lease-directory')

    def _file(self, name, size):
        before = self._api.stat(name, dir_fd=self._root, follow_symlinks=False)
        fd = self._open(name, self._flags, self._root)
        info = self._api.fstat(fd)
        D.S.require(_fingerprint(before) == _fingerprint(info) and stat.S_ISREG(info.st_mode)
                    and info.st_uid == self._uid and info.st_nlink == 1 and not info.st_mode & 0o133
                    and info.st_size == size, 'lease-file')
        return fd, info

    def _retire(self, item):
        def release():
            try:
                result = self._api.close(item[1]) if item[0] == 'fd' else item[1].close()
                D.S.require(result is None, 'lease-close-result')
            except BaseException:
                self._uncertain = True
                raise
            finally:
                # Even an interrupted/failed close must never be retried on a
                # potentially reused numeric descriptor.
                self._owned.remove(item)
        self._critical(release)

    def _membership(self):
        self._budget.charge(0)
        self._api.seek(self._root, 0, os.SEEK_SET)
        item = ['iterator', None]
        def acquire():
            self._owned.append(item)
            try:
                item[1] = self._api.scan(self._root)
            finally:
                if item[1] is None:
                    self._owned.remove(item)
        self._critical(acquire)
        try:
            names = set()
            for entry in item[1]:
                D.S.require(len(names) < 4 and entry.name not in names, 'lease-membership-bound')
                names.add(entry.name)
            D.S.require(names == {'owner.json', *self._names}, 'lease-membership')
        finally:
            self._retire(item)

    def _bound(self, name, fd, original):
        D.S.require(_fingerprint(self._api.fstat(fd)) == _fingerprint(original)
                    and _fingerprint(self._api.stat(name, dir_fd=self._root, follow_symlinks=False)) == _fingerprint(original), 'lease-file-changed')

    def check(self, full=False):
        D.S.require(not self._closed and not self._uncertain, 'lease-unavailable')
        self._budget.charge(0)
        # Parent child timestamps can change without changing the admitted root.
        current = self._api.stat(self._parent_path, follow_symlinks=False)
        self._directory(current, False)
        self._directory(self._api.fstat(self._parent), False)
        D.S.require((current.st_dev, current.st_ino) == (self._parent_info.st_dev, self._parent_info.st_ino)
                    == (self._api.fstat(self._parent).st_dev, self._api.fstat(self._parent).st_ino), 'lease-parent-changed')
        D.S.require(_fingerprint(self._api.fstat(self._root)) == _fingerprint(self._root_info)
                    and _fingerprint(self._api.stat(self._basename, dir_fd=self._parent, follow_symlinks=False)) == _fingerprint(self._root_info), 'lease-root-changed')
        if full:
            self._membership()
        self._bound('owner.json', self._marker, self._marker_info)
        if full:
            marker = _Cursor(self, self._marker, len(self._marker_bytes))
            try:
                offset = 0
                while offset < len(self._marker_bytes):
                    chunk = marker.read(len(self._marker_bytes)-offset)
                    self._budget.charge(len(chunk))
                    D.S.require(chunk == memoryview(self._marker_bytes)[offset:offset+len(chunk)], 'lease-marker')
                    offset += len(chunk)
                D.S.require(marker.read(1) == b'', 'lease-marker')
            finally:
                marker._release()
        for name, (fd, info) in zip(self._names, self._sources):
            self._bound(name, fd, info)

    def cursor(self, index):
        D.S.require(not self._closed and type(index) is int and 0 <= index < 3, 'lease-cursor')
        fd, info = self._sources[index]
        cursor = _Cursor(self, fd, info.st_size)
        self._cursors.append(cursor)
        return cursor

    def identity(self, index):
        self.check()
        cursor = self.cursor(index)
        pin, digest = self._pins[index], hashlib.sha256()
        try:
            while cursor.tell() < pin[1]:
                chunk = cursor.read(min(65536, pin[1]-cursor.tell()))
                self._budget.charge(len(chunk))
                digest.update(chunk)
            D.S.require(cursor.read(1) == b'' and digest.hexdigest() == pin[2], 'lease-source-identity')
        finally:
            cursor._release()
            self._cursors.remove(cursor)
        self.check()
        return {'package': pin[0], 'bytes': pin[1], 'sha256': pin[2]}

    def seal(self):
        self.check(True)
        return [self.identity(index) for index in range(3)]

    def close(self, *, _deadline=None):
        if self._closed:
            D.S.require(not self._owned and not self._uncertain, 'lease-cleanup-unknown')
            return
        self._closed = True  # Invalidate every cursor before touching resources.
        for cursor in self._cursors:
            cursor._release()
        self._cursors.clear()
        deadline = self._api.clock()+30 if _deadline is None else min(self._api.clock()+30, _deadline)
        for item in tuple(reversed(self._owned)):
            if self._api.clock() >= deadline:
                self._uncertain = True
                break
            try:
                self._retire(item)
            except BaseException:
                self._uncertain = True
        D.S.require(not self._owned and not self._uncertain, 'lease-cleanup-unknown')
