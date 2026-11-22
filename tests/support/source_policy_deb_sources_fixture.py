"""In-memory syscall model. Never opens a descriptor, file, socket or process."""
import os
import stat
from types import SimpleNamespace


class FakeAPI:
    def __init__(self, values, names, marker):
        self.names, self.marker = names, marker
        self.events, self.live, self.masked = [], {}, False
        self.next_fd, self.now, self.max_live = 0, 0, 0
        self.fault, self.short = None, 65536
        self.positions, self.iterators = {}, []
        self.bytes = {'owner.json': marker, **dict(zip(names, values))}
        self.infos = {}
        for index, name in enumerate(('/owned', 'adb-ppa-deb-1', 'owner.json', *names)):
            directory = index < 2
            self.infos[name] = SimpleNamespace(st_dev=1, st_ino=index+1,
                st_mode=(stat.S_IFDIR | 0o700) if directory else (stat.S_IFREG | 0o600),
                st_uid=1000, st_gid=1000, st_nlink=1, st_size=0 if directory else len(self.bytes[name]),
                st_mtime_ns=1, st_ctime_ns=1, st_atime_ns=1)

    def profile(self):
        self.events.append(('profile',))
        return 1000, 0x10000, 0x20000

    def mask(self):
        previous = self.masked
        self.masked = True
        self.events.append(('mask', previous))
        return previous

    def restore(self, previous):
        self.masked = previous
        self.events.append(('restore', previous))

    def _event(self, operation, *args):
        self.events.append((operation, *args))
        if self.fault:
            self.fault(operation, *args)

    def open(self, name, flags, **kwargs):
        assert self.masked
        self._event('open', name, flags, kwargs.get('dir_fd'))
        if name == '/owned':
            assert not kwargs
        elif name == 'adb-ppa-deb-1':
            assert self.live[kwargs['dir_fd']] == '/owned'
        else:
            assert self.live[kwargs['dir_fd']] == 'adb-ppa-deb-1'
        fd, self.next_fd = self.next_fd, self.next_fd+1
        self.live[fd] = name
        self.max_live = max(self.max_live, len(self.live)+len(self.iterators))
        self.positions[fd] = 0
        return fd

    def flags(self, fd):
        self._event('flags', fd)
        return 0

    def inheritable(self, fd):
        self._event('inherit', fd)
        return False

    def stat(self, name, **kwargs):
        assert kwargs.get('follow_symlinks') is False
        if name not in ('/owned', 'adb-ppa-deb-1'):
            assert self.live[kwargs['dir_fd']] == 'adb-ppa-deb-1'
        self._event('stat', name)
        return SimpleNamespace(**vars(self.infos[name]))

    def fstat(self, fd):
        self._event('fstat', self.live[fd])
        return SimpleNamespace(**vars(self.infos[self.live[fd]]))

    def seek(self, fd, offset, whence):
        assert offset == 0 and whence == os.SEEK_SET
        self._event('rewind', fd)
        self.positions[fd] = 0

    def scan(self, fd):
        assert self.masked and self.positions[fd] == 0 and not self.iterators
        self._event('scan', fd)
        self.positions[fd] = 1
        api = self

        class Iterator:
            def __iter__(self):
                return iter(SimpleNamespace(name=name) for name in api.bytes)

            def close(self):
                assert api.masked
                api.iterators.remove(self)
                api._event('iterator-close')

        value = Iterator()
        self.iterators.append(value)
        self.max_live = max(self.max_live, len(self.live)+len(self.iterators))
        return value

    def pread(self, fd, count, offset):
        assert not self.masked and 0 < count <= 65536
        name = self.live[fd]
        self._event('pread', name, count, offset)
        return self.bytes[name][offset:offset+min(count, self.short)]

    def close(self, fd):
        assert self.masked
        name = self.live.pop(fd)
        self._event('close', fd, name)

    def clock(self):
        return self.now
