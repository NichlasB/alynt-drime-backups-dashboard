"""Dormant fixed POSIX output adapter; no factory selects real calls.

The default syscall provider refuses before IO. Isolated tests replace _CALLS,
not directory/candidate policy. Python privacy is not an interpreter sandbox.
"""
import os
import stat

import source_policy_deb_sources as L
import source_policy_ppa_prefix as P

D = P.D


class _ClosedCalls:
    def profile(self):
        raise D.S.Refusal('prefix-syscalls-disabled')


class _PosixCalls(L._NativeAPI):
    """Unselected concrete calls. A later approved fixed owner must admit them."""
    def profile(self):
        result = super().profile()
        D.S.require(all(operation in os.supports_dir_fd for operation in (os.mkdir, os.unlink, os.rmdir))
                    and hasattr(os, 'fchmod'), 'storage-capabilities')
        return result

    mkdir = staticmethod(os.mkdir)
    write = staticmethod(os.write)
    read = staticmethod(os.read)
    def chmod(self, fd, mode):
        return os.fchmod(fd, mode)  # Resolved only after Linux admission.
    unlink = staticmethod(os.unlink)
    rmdir = staticmethod(os.rmdir)


_CALLS = _ClosedCalls()


class _Storage:
    def __init__(self, budget, scratch):
        D.S.require(type(budget) is D.Budget and type(scratch) is P.E.Budget, 'storage-original-budget')
        self._api, self._budget, self._scratch = _CALLS, budget, scratch
        self._records, self._iterators, self._files, self._sizes, self._leaves, self._ready = [], [], {}, {}, {}, False
        self._uncertain, self._created, self._root = False, False, None
        self._parent, self._parent_path, self._basename = None, None, None
        self._cleanup_deadline = None
        self._expected_parent = None

    def profile(self):
        self._uid, self._ro, directory = self._api.profile()  # Default is CLOSED.
        self._directory_flags = self._ro | directory
        self._write_flags = self._ro | os.O_WRONLY | os.O_CREAT | os.O_EXCL
        self._ready = True
        return self._uid

    def _live(self, cleanup=False):
        D.S.require(self._ready and not self._uncertain, 'storage-unavailable')
        if cleanup:
            D.S.require(self._cleanup_deadline is not None and self._api.clock() < self._cleanup_deadline, 'storage-cleanup-deadline')
        else:
            self._budget.charge(0)

    def _critical(self, action):
        previous = self._api.mask()
        try:
            return action()
        finally:
            try:
                self._api.restore(previous)
            except BaseException:
                self._uncertain = True
                raise D.S.Refusal('storage-mask-restore') from None

    def _open(self, name, flags, mode=None, parent=None):
        record = [None, False]
        def acquire():
            self._records.append(record)
            kwargs = {} if parent is None else {'dir_fd': parent}
            try:
                fd = self._api.open(name, flags, **kwargs) if mode is None else self._api.open(name, flags, mode, **kwargs)
                record[0] = fd  # Registered before validation/allocation/unmask.
                D.S.require(type(fd) is int and fd >= 0, 'storage-fd-shape')
                return fd
            finally:
                if record[0] is None:
                    self._records.remove(record)
        fd = self._critical(acquire)
        D.S.require(self._api.inheritable(fd) is False, 'storage-inheritance')
        return fd

    def _record(self, fd):
        row = next((row for row in self._records if row[0] == fd and not row[1]), None)
        D.S.require(row is not None, 'storage-retired')
        return row

    def close(self, fd):
        record = self._record(fd)
        def retire():
            record[1] = True  # Never retry a failed/reused numeric descriptor.
            try:
                D.S.require(self._api.close(fd) is None, 'storage-close-result')
            except BaseException:
                self._uncertain = True
                raise
        self._critical(retire)

    def _directory(self, info, private):
        D.S.require(stat.S_ISDIR(info.st_mode) and info.st_uid == self._uid
                    and (stat.S_IMODE(info.st_mode) == 0o700 if private else not info.st_mode & 0o022), 'storage-directory')

    def _bound_root(self):
        self._directory(self._api.fstat(self._parent), False)
        parent = self._api.stat(self._parent_path, follow_symlinks=False)
        self._directory(parent, False)
        D.S.require(P._identity(parent) == self._parent_id == P._identity(self._api.fstat(self._parent)), 'storage-parent-binding')
        info = self._api.fstat(self._root)
        self._directory(info, True)
        D.S.require(P._identity(info) == self._root_id
                    == P._identity(self._api.stat(self._basename, dir_fd=self._parent, follow_symlinks=False)), 'storage-root-binding')

    def claim(self, parent, basename, mode, *, exclusive, nofollow):
        self._live()
        D.S.require(self._parent is None and mode == 0o700 and exclusive is True and nofollow is True, 'storage-claim-shape')
        D.S.require(type(self._expected_parent) is tuple and len(self._expected_parent) == 2, 'storage-parent-origin')
        self._parent_path, self._basename = parent, basename
        before = self._api.stat(parent, follow_symlinks=False)
        self._parent = self._open(parent, self._directory_flags)
        self._parent_id = P._identity(self._api.fstat(self._parent))
        self._directory(before, False)
        D.S.require(P._identity(before) == self._parent_id == self._expected_parent, 'storage-parent-binding')
        def create():
            self._api.mkdir(basename, mode, dir_fd=self._parent)
            self._created = True  # Does NOT establish a bound current root.
        self._critical(create)
        self._root = self._open(basename, self._directory_flags, parent=self._parent)
        self._root_id = P._identity(self._api.fstat(self._root))
        self._bound_root()
        return self._root

    def info(self, fd):
        self._record(fd)
        return self._api.fstat(fd)

    def lookup(self, parent, leaf):
        D.S.require((parent == self._parent_path and leaf == self._basename)
                    or (parent == self._root and leaf in self._files), 'storage-lookup-context')
        return self._api.stat(leaf, dir_fd=self._parent if parent == self._parent_path else parent, follow_symlinks=False)

    def inheritable(self, fd):
        self._record(fd)
        return self._api.inheritable(fd)

    def access(self, fd):
        self._record(fd)
        value = self._api.flags(fd)
        D.S.require(value in (os.O_RDONLY, os.O_WRONLY), 'storage-access')
        return 'read' if value == os.O_RDONLY else 'write'

    def create(self, root, leaf, mode, *, exclusive, nofollow, inheritable):
        self._live()
        self._bound_root()
        D.S.require(root == self._root and leaf in {'owner.json', *(row[3] for row in P._CATALOG)}
                    and leaf not in self._files and mode == 0o600 and exclusive is True
                    and nofollow is True and inheritable is False, 'storage-leaf')
        fd = self._open(leaf, self._write_flags, mode, root)
        self._files[leaf] = P._identity(self.info(fd))
        self._sizes[leaf], self._leaves[fd] = 0, leaf
        return fd

    def write(self, fd, chunk):
        self._live()
        self._record(fd)
        D.S.require(self.access(fd) == 'write' and 0 < len(chunk) <= 65536, 'storage-write')
        count = self._api.write(fd, chunk)
        D.S.require(type(count) is int and 0 < count <= len(chunk), 'storage-write-progress')
        self._sizes[self._leaves[fd]] += count
        return count

    def readonly(self, root, leaf, *, nofollow, inheritable):
        self._live()
        self._bound_root()
        D.S.require(root == self._root and leaf in self._files and nofollow is True and inheritable is False, 'storage-readonly')
        fd = self._open(leaf, self._ro, parent=root)
        D.S.require(self.access(fd) == 'read', 'storage-readonly')
        return fd

    def read(self, fd, count):
        self._live()
        self._record(fd)
        D.S.require(self.access(fd) == 'read' and type(count) is int and 0 < count <= 65536, 'storage-read')
        return self._api.read(fd, count)

    def readonly_mode(self, root, leaf, identity, mode):
        self._live()
        D.S.require(identity == self._files.get(leaf) and mode == 0o400, 'storage-mode')
        fd = self.readonly(root, leaf, nofollow=True, inheritable=False)
        try:
            info, bound = self.info(fd), self.lookup(root, leaf)
            D.S.require(P._identity(info) == P._identity(bound) == identity
                        and stat.S_ISREG(info.st_mode) and info.st_uid == self._uid
                        and info.st_nlink == 1 and info.st_size == self._sizes[leaf]
                        and stat.S_IMODE(info.st_mode) == 0o600, 'storage-pre-mode')
            D.S.require(self._api.chmod(fd, mode) is None, 'storage-mode-result')
            after = self.info(fd)
            D.S.require(P._identity(after) == identity and stat.S_IMODE(after.st_mode) == mode
                        and after.st_size == info.st_size and after.st_uid == self._uid and after.st_nlink == 1,
                        'storage-mode-post')
        finally:
            self.close(fd)

    def members(self, root, limit):
        self._live(self._cleanup_deadline is not None)
        D.S.require(root == self._root and limit == 4, 'storage-membership')
        self._bound_root()
        self._api.seek(root, 0, os.SEEK_SET)
        iterator = [None, False]
        def acquire():
            self._iterators.append(iterator)
            iterator[0] = self._api.scan(root)
        # Iterator records have the same acquire-before-unmask obligation.
        self._critical(acquire)
        try:
            names = set()
            for entry in iterator[0]:
                D.S.require(len(names) < limit and entry.name not in names, 'storage-membership-bound')
                names.add(entry.name)
            return names
        finally:
            def retire():
                iterator[1] = True
                try:
                    D.S.require(iterator[0].close() is None, 'storage-iterator-close')
                except BaseException:
                    self._uncertain = True
                    raise
            self._critical(retire)

    def remove(self, root, leaf, identity):
        self._live(True)
        D.S.require(root == self._root and identity == self._files.get(leaf), 'storage-remove-context')
        self._bound_root()
        info = self.lookup(root, leaf)
        D.S.require(root == self._root and identity == self._files.get(leaf) == P._identity(info)
                    and stat.S_ISREG(info.st_mode) and info.st_uid == self._uid and info.st_nlink == 1
                    and stat.S_IMODE(info.st_mode) in (0o600, 0o400),
                    'storage-remove-binding')
        D.S.require(self._api.unlink(leaf, dir_fd=root) is None, 'storage-remove-result')

    def remove_root(self, parent, basename, root, identity):
        self._live(True)
        self._bound_root()
        D.S.require((parent, basename, root, identity) == (self._parent_path, self._basename, self._root, self._root_id)
                    and self.members(root, 4) == set(), 'storage-root-remove')
        D.S.require(self._api.rmdir(basename, dir_fd=self._parent) is None, 'storage-rmdir-result')
        self._created = False

    def absent(self, parent, basename):
        self._live(True)
        D.S.require((parent, basename) == (self._parent_path, self._basename), 'storage-absence-context')
        try:
            self._api.stat(basename, dir_fd=self._parent, follow_symlinks=False)
        except FileNotFoundError:
            return True
        return False

    def start_cleanup(self, deadline):
        D.S.require(self._cleanup_deadline is None and type(deadline) in (int, float), 'storage-cleanup-clock')
        self._cleanup_deadline = deadline

    def retire(self):
        """Retire backend-owned FDs even when a caller never received a token."""
        if not self._ready and not self._records and not self._created:
            return
        if self._cleanup_deadline is None:
            self.start_cleanup(self._api.clock()+30)
        for row in reversed(self._iterators):
            if row[1]:
                continue
            try:
                D.S.require(row[0] is not None and self._api.clock() < self._cleanup_deadline, 'storage-iterator-unknown')
                def retire_iterator():
                    row[1] = True
                    D.S.require(row[0].close() is None, 'storage-iterator-close')
                self._critical(retire_iterator)
            except BaseException:
                self._uncertain = True
        for record in reversed(self._records):
            if record[1]:
                continue
            try:
                D.S.require(self._api.clock() < self._cleanup_deadline, 'storage-cleanup-deadline')
                self.close(record[0])
            except BaseException:
                self._uncertain = True
        D.S.require(not self._uncertain and not self._created
                    and all(row[1] for row in self._records)
                    and all(row[1] for row in getattr(self, '_iterators', ())), 'storage-cleanup-unknown')
