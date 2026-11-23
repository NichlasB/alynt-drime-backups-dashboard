"""RAM-only storage protocol model, not a filesystem/permission proof."""
import stat
from types import SimpleNamespace


class Storage:
    def __init__(self):
        self.data, self.infos, self.live, self.events = {}, {}, {}, []
        self.next_handle, self.short, self.fault = 1, 3, None
        self.root = None

    def event(self, op, *args):
        self.events.append((op, *args))
        if self.fault:
            self.fault(op, *args)

    def profile(self):
        self.event('profile')
        return 1000

    def claim(self, parent, name, mode, **flags):
        self.event('claim')
        assert (parent, name, mode, flags) == ('/owned', 'adb-ppa-runtime-1', 0o700, {'exclusive': True, 'nofollow': True})
        if self.root is not None:
            raise FileExistsError()
        self.root = 0
        self.infos['root'] = self.make_info(0, stat.S_IFDIR | mode)
        return self.root

    def make_info(self, ino, mode):
        return SimpleNamespace(st_dev=1, st_ino=ino, st_mode=mode, st_uid=1000, st_nlink=1, st_size=0)

    def info(self, handle):
        self.event('info', handle)
        name = 'root' if handle == self.root else self.live[handle][0]
        return SimpleNamespace(**vars(self.infos[name]))

    def lookup(self, root, leaf):
        self.event('lookup', leaf)
        name = 'root' if root == '/owned' else leaf
        return SimpleNamespace(**vars(self.infos[name]))

    def handle(self, leaf, access):
        handle = self.next_handle
        self.next_handle += 1
        self.live[handle] = [leaf, access, 0]
        return handle

    def create(self, root, leaf, mode, **flags):
        self.event('create', leaf)
        assert root == self.root and mode == 0o600
        assert flags == {'exclusive': True, 'nofollow': True, 'inheritable': False}
        if leaf in self.data:
            raise FileExistsError()
        self.data[leaf] = bytearray()
        self.infos[leaf] = self.make_info(self.next_handle, stat.S_IFREG | mode)
        return self.handle(leaf, 'write')

    def inheritable(self, handle):
        self.event('inheritable', handle)
        return False

    def access(self, handle):
        return self.live[handle][1]

    def write(self, handle, chunk):
        leaf, access, _ = self.live[handle]
        self.event('write', leaf)
        assert access == 'write'
        count = min(len(chunk), self.short)
        self.data[leaf].extend(chunk[:count])
        self.infos[leaf].st_size = len(self.data[leaf])
        return count

    def close(self, handle):
        self.live.pop(handle)
        self.event('close', handle)  # A raised close is deliberately ambiguous.

    def readonly_mode(self, root, leaf, identity, mode):
        self.event('mode', leaf)
        assert root == self.root and mode == 0o400
        info = self.infos[leaf]
        assert (info.st_dev, info.st_ino) == identity
        info.st_mode = stat.S_IFREG | mode

    def readonly(self, root, leaf, **flags):
        self.event('readonly', leaf)
        assert root == self.root and flags == {'nofollow': True, 'inheritable': False}
        return self.handle(leaf, 'read')

    def read(self, handle, count):
        leaf, access, offset = self.live[handle]
        self.event('read', leaf)
        assert access == 'read' and 0 < count <= 65536
        chunk = bytes(self.data[leaf][offset:offset+min(count, self.short)])
        self.live[handle][2] += len(chunk)
        return chunk

    def members(self, root, limit):
        self.event('members')
        assert root == self.root and limit == 4
        return set(self.data)

    def remove(self, root, leaf, identity):
        self.event('remove', leaf)
        assert root == self.root and (self.infos[leaf].st_dev, self.infos[leaf].st_ino) == identity
        del self.data[leaf]
        del self.infos[leaf]

    def remove_root(self, parent, basename, root, identity):
        self.event('remove-root')
        assert not self.data and root == self.root and (self.infos['root'].st_dev, self.infos['root'].st_ino) == identity
        self.root = None

    def absent(self, parent, basename):
        self.event('absent')
        return self.root is None
