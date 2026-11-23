"""RAM-only syscall-shaped model; never calls os file/process operations."""
import os
import stat
from types import SimpleNamespace


class Calls:
    RO, DIRECTORY = 1 << 28, 1 << 29  # Model sentinels, not platform ABI claims.

    def __init__(self):
        self.nodes = {'/owned': self.info_value(1, stat.S_IFDIR | 0o700)}
        self.data, self.live, self.positions, self.iterators = {}, {}, {}, []
        self.events, self.next_fd, self.masked = [], 0, False
        self.now, self.short, self.fault = 0, 97, None

    def info_value(self, inode, mode):
        return SimpleNamespace(st_dev=1, st_ino=inode, st_mode=mode, st_uid=1000,
                               st_gid=1000, st_nlink=1, st_size=0, st_mtime_ns=1, st_ctime_ns=1)

    def event(self, op, *args):
        self.events.append((op, *args))
        if self.fault:
            self.fault(op, *args)

    def profile(self):
        self.event('profile')
        return 1000, self.RO, self.DIRECTORY

    def mask(self):
        previous = self.masked
        self.masked = True
        self.event('mask')
        return previous

    def restore(self, previous):
        self.masked = previous
        self.event('restore')

    def key(self, name, parent=None):
        if parent is None:
            assert name == '/owned'
            return name
        base = self.live[parent][0]
        assert base in ('/owned', 'adb-ppa-runtime-1') and '/' not in name
        return name

    def open(self, name, flags, mode=None, **kwargs):
        assert self.masked
        key = self.key(name, kwargs.get('dir_fd'))
        self.event('open', key, flags, mode)
        if mode is not None:
            assert mode == 0o600 and flags == self.RO | os.O_WRONLY | os.O_CREAT | os.O_EXCL
            if key in self.nodes:
                raise FileExistsError()
            self.nodes[key] = self.info_value(100+self.next_fd, stat.S_IFREG | mode)
            self.data[key] = bytearray()
        else:
            if key not in self.nodes:
                raise FileNotFoundError()
            assert flags == self.RO | (self.DIRECTORY if key in ('/owned', 'adb-ppa-runtime-1') else 0)
            if stat.S_ISLNK(self.nodes[key].st_mode):
                raise OSError('modeled-nofollow')
        fd = next(value for value in range(32) if value not in self.live)
        self.next_fd += 1
        self.live[fd], self.positions[fd] = (key, flags), 0
        return fd

    def stat(self, name, **kwargs):
        assert kwargs.get('follow_symlinks') is False
        key = self.key(name, kwargs.get('dir_fd'))
        self.event('stat', key)
        if key not in self.nodes:
            raise FileNotFoundError()
        return SimpleNamespace(**vars(self.nodes[key]))

    def fstat(self, fd):
        key = self.live[fd][0]
        self.event('fstat', key)
        return SimpleNamespace(**vars(self.nodes[key]))

    def mkdir(self, name, mode, **kwargs):
        assert self.masked and self.live[kwargs['dir_fd']][0] == '/owned'
        assert name == 'adb-ppa-runtime-1' and mode == 0o700
        self.event('mkdir')
        if name in self.nodes:
            raise FileExistsError()
        self.nodes[name] = self.info_value(99, stat.S_IFDIR | mode)

    def flags(self, fd):
        return self.live[fd][1] & (os.O_WRONLY | os.O_RDWR)

    def inheritable(self, fd):
        assert fd in self.live
        return False

    def write(self, fd, value):
        key = self.live[fd][0]
        self.event('write', key)
        assert self.flags(fd) == os.O_WRONLY
        count = min(len(value), self.short)
        self.data[key].extend(value[:count])
        self.nodes[key].st_size = len(self.data[key])
        return count

    def read(self, fd, count):
        key = self.live[fd][0]
        self.event('read', key)
        assert self.flags(fd) == os.O_RDONLY and 0 < count <= 65536
        offset = self.positions[fd]
        value = bytes(self.data[key][offset:offset+min(count, self.short)])
        self.positions[fd] += len(value)
        return value

    def chmod(self, fd, mode):
        key = self.live[fd][0]
        self.event('chmod', key)
        assert self.flags(fd) == os.O_RDONLY and mode == 0o400
        self.nodes[key].st_mode = stat.S_IFREG | mode

    def seek(self, fd, offset, whence):
        assert self.live[fd][0] == 'adb-ppa-runtime-1' and offset == 0 and whence == os.SEEK_SET

    def scan(self, fd):
        assert self.masked and self.live[fd][0] == 'adb-ppa-runtime-1'
        self.event('scan')
        api = self

        class Iterator:
            def __iter__(self):
                return iter(SimpleNamespace(name=name) for name in api.data)

            def close(self):
                assert api.masked
                api.iterators.remove(self)
                api.event('iterator-close')

        value = Iterator()
        self.iterators.append(value)
        return value

    def close(self, fd):
        assert self.masked
        key, flags = self.live.pop(fd)
        self.event('close', fd, key)

    def unlink(self, name, **kwargs):
        assert self.live[kwargs['dir_fd']][0] == 'adb-ppa-runtime-1'
        self.event('unlink', name)
        del self.data[name]
        del self.nodes[name]

    def rmdir(self, name, **kwargs):
        assert not self.data and name == 'adb-ppa-runtime-1'
        assert self.live[kwargs['dir_fd']][0] == '/owned'
        self.event('rmdir')
        del self.nodes[name]

    def clock(self):
        return self.now
