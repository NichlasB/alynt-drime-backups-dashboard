"""Pure fixture/mocked IO checks only; no process, proc, network or actual cleanup."""
import importlib.util
import ast
import errno
import io
import json
from pathlib import Path
import socket
import stat
import subprocess
import types
import time
import unittest
from unittest.mock import patch
import sys

path = Path(__file__).resolve().parents[2] / 'scripts/ci/source-policy-linux-observer-audit.py'
spec = importlib.util.spec_from_file_location('observer_audit', path)
a = importlib.util.module_from_spec(spec)
with patch.object(subprocess, 'Popen', side_effect=AssertionError('Process forbidden')), patch.object(socket, 'socket', side_effect=AssertionError('Network forbidden')):
    spec.loader.exec_module(a)


def row(address='1000-2000', perms='r--s', inode=7, path='owned'):
    return (f'{address} {perms} 00000000 08:01 {inode} {path}\n').encode()


class ObserverTests(unittest.TestCase):
    def test_stat_exact_pid_parent_and_start(self):
        fields = ['S', '10'] + ['0'] * 17 + ['1234']
        data = ('42 (name with ) spaces) ' + ' '.join(fields) + '\n').encode()
        self.assertEqual((10, 1234), a.parse_stat(data, 42))
        for value, pid in ((data, 43), (b'broken', 42), (b'x' * 4097, 42)):
            with self.assertRaises(a.Refusal): a.parse_stat(value, pid)

    def test_complete_bounded_maps_and_no_path_authority(self):
        parsed = a.parse_maps(row(path='/untrusted/SECRET') + row('2000-3000', 'r-xp', 9, '/other'))
        self.assertEqual(('1000-2000', (8, 1, 7), False), parsed[0])
        self.assertEqual(2, len(parsed)); self.assertNotIn('SECRET', repr(parsed))
        many = b''.join(row(f'{4096*i:x}-{4096*(i+1):x}', inode=0) for i in range(1,258))
        for data in (row(path='x' * 32768), many, row() + row(), row('1000-3000') + row('2000-4000'), row(address='2000-1000'), row(perms='bad'), row(perms='r-xp', inode=0, path=''), row().rstrip(b'\n')):
            with self.assertRaises(a.Refusal): a.parse_maps(data)
        self.assertEqual([], a.parse_maps(row(inode=0, perms='r-xp', path='[vdso]')))

    def test_capability_fields_are_actual_and_complete(self):
        value = b'Name:\tpython\nUid:\t1001\t1001\t1001\t1001\nGid:\t1001\t1001\t1001\t1001\nCapEff:\t0000000000000000\nNoNewPrivs:\t0\n'
        self.assertEqual({'uid':1001,'gid':1001,'cap_eff':'0000000000000000','no_new_privs':0}, a.parse_status(value))
        for bad in (b'', value.replace(b'CapEff:', b'Missing:'), value.replace(b'0000000000000000', b'not-hex'), value + b'CapEff:\t0000000000000000\n'):
            with self.assertRaises(a.Refusal): a.parse_status(bad)

    def test_owner_and_cleanup_membership_before_deletion(self):
        expected = {'head':'a'*40,'source_image':'b'*64,'uid':1001,'run_id':'12','attempt':'1','launching':False}
        self.assertTrue(a.valid_owner(dict(expected), expected))
        for key, val in (('uid',0), ('head','c'*40), ('launching','false'), ('extra',True)):
            bad=dict(expected);bad[key]=val;self.assertFalse(a.valid_owner(bad,expected))
        meta=types.SimpleNamespace(st_mode=stat.S_IFREG|0o600,st_uid=1001,st_nlink=1,st_size=4096)
        self.assertEqual(['mapped.bin','owner.json'], a.cleanup_names([('mapped.bin',meta),('owner.json',meta)],1001))
        for files in ([('foreign',meta)],[('mapped.bin',types.SimpleNamespace(st_mode=stat.S_IFLNK,st_uid=1001,st_nlink=1,st_size=2))],[('mapped.bin',meta)]*5):
            with self.assertRaises(a.Refusal): a.cleanup_names(files,1001)

    def test_no_claim_of_qualification_from_a_completed_denial(self):
        self.assertEqual(0,a.exit_code('unavailable',True,True))
        self.assertEqual(0,a.exit_code('available',True,True))
        for status, clean, unchanged in (('refused',True,True),('available',False,True),('available',True,False),('unavailable','true',True)):
            self.assertEqual(1,a.exit_code(status,clean,unchanged))

    def test_read_bounds_and_hash_checks_are_not_vacuous(self):
        with patch.object(a.os,'read',return_value=b'abc') as read:
            self.assertEqual(b'abc',a.bounded_read(4,3))
            read.assert_called_once_with(4,4)
        with patch.object(a.os,'read',return_value=b'xxxx'):
            with self.assertRaises(a.Refusal): a.bounded_read(4,3)
        for extra in (b'',b'x'):
            with patch.object(a.os,'O_CLOEXEC',524288,create=True), patch.object(a.os,'open',return_value=9), patch.object(a.os,'close'), patch.object(a.os,'read',side_effect=[b'abc',extra]) as read:
                if extra:
                    with self.assertRaises(a.Refusal): a.read_at(3,'maps',10)
                else: self.assertEqual(b'abc',a.read_at(3,'maps',10))
                self.assertEqual([11,1],[call.args[1] for call in read.call_args_list])

    def test_snapshot_positive_and_denied_file_object_are_distinct(self):
        fields=['S','10']+['0']*17+['1234']
        status=('42 (child) '+' '.join(fields)+'\n').encode()
        def read_at(directory,name,limit): return status if name=='stat' else row(path='/not-a-host-path/SECRET')
        meta=types.SimpleNamespace(st_mode=stat.S_IFREG|0o600,st_nlink=1,st_size=4096,st_dev=123,st_ino=7)
        with patch.object(a.os,'O_CLOEXEC',524288,create=True), patch.object(a,'read_at',side_effect=read_at), patch.object(a.os,'open',return_value=9) as opened, patch.object(a.os,'close'), patch.object(a.os,'fstat',return_value=meta), patch.object(a.os,'major',return_value=8,create=True), patch.object(a.os,'minor',return_value=1,create=True), patch.object(a.os,'read',side_effect=[b'A'*4096,b'']):
            try: value=a.snapshot(3,42,(10,1234),(8,1,7),time.monotonic()+10)
            except (a.Refusal,PermissionError) as error: self.fail('Valid independent synthetic backing-object read refused: '+str(error))
            self.assertEqual((1,4096,True),(value['regular_files'],value['file_bytes'],value['fixture_backing_read']))
            self.assertEqual('map_files/1000-2000',opened.call_args.args[0]);self.assertEqual(3,opened.call_args.kwargs['dir_fd'])
            self.assertNotIn('SECRET',repr(value))
        with patch.object(a.os,'O_CLOEXEC',524288,create=True), patch.object(a,'read_at',side_effect=read_at), patch.object(a.os,'open',side_effect=PermissionError(errno.EPERM,'synthetic denial')):
            with self.assertRaises(PermissionError) as caught: a.snapshot(3,42,(10,1234),(8,1,7),time.monotonic()+10)
            self.assertEqual('fixture-mapfile-open',caught.exception.operation)
            self.assertEqual({'maps_complete':True,'mapping_rows':1,'unique_files':1,'files_hashed':0,'fixture_selected':True},caught.exception.facts)

    def test_fixed_child_is_read_only_bounded_and_compile_valid(self):
        tree=ast.parse(a.CHILD);compile(tree,'owned-harmless-child','exec')
        byte_values=[node.value for node in ast.walk(tree) if isinstance(node,ast.Constant) and isinstance(node.value,bytes)]
        self.assertIn(b'READY\n',byte_values);self.assertIn(b'CLOSED\n',byte_values)
        self.assertEqual(['mmap','os','select','sys'],[alias.name for node in tree.body if isinstance(node,ast.Import) for alias in node.names])
        for text in ('access=mmap.ACCESS_READ','os.O_RDONLY | os.O_NOFOLLOW','select.select([0], [], [], 20)','mapping.close()'):
            self.assertIn(text,a.CHILD)
        for text in ('subprocess','socket','write_bytes','O_WRONLY','exec(','eval('): self.assertNotIn(text,a.CHILD)

    def test_removed_bounds_and_guards_are_assertion_killed_in_memory(self):
        source=path.read_text(encoding='utf-8')
        mutations=[("len(data) <= 32768",'True','test_complete_bounded_maps_and_no_path_authority'),
                   ("len(lines) <= 256",'True','test_complete_bounded_maps_and_no_path_authority'),
                   ("start >= end_previous",'True','test_complete_bounded_maps_and_no_path_authority'),
                   ("name in ('owner.json', 'owner.next.json', 'child.json', 'mapped.bin')",'True','test_owner_and_cleanup_membership_before_deletion'),
                   ("found_pid == pid",'True','test_stat_exact_pid_parent_and_start'),
                   ("clean is True and unchanged is True",'True','test_no_claim_of_qualification_from_a_completed_denial'),
                   ('def snapshot(proc, pid, identity, fixture, deadline):\n',"def snapshot(proc, pid, identity, fixture, deadline):\n    raise PermissionError('always unavailable')\n",'test_snapshot_positive_and_denied_file_object_are_distinct')]
        original=a
        for old,new,method in mutations:
            self.assertEqual(1,source.count(old))
            image=types.ModuleType('control');image.__file__=str(path)
            exec(compile(source.replace(old,new,1),str(path),'exec'),vars(image))
            with patch.dict(globals(),{'a':image}):
                result=unittest.TextTestRunner(stream=io.StringIO()).run(ObserverTests(method))
            self.assertEqual([],result.errors);self.assertGreater(len(result.failures),0,old)
        self.assertIs(a,original);self.assertEqual(source,path.read_text(encoding='utf-8'))

    def test_import_and_non_owned_guard_are_effect_free(self):
        with patch.object(a.subprocess,'Popen',side_effect=AssertionError('No child')), patch.object(a.os,'open',side_effect=AssertionError('No proc/files')):
            with self.assertRaises(a.Refusal): a.admit({})


capture=io.StringIO()
with patch.object(socket,'socket',side_effect=AssertionError('No network')), patch.object(subprocess,'Popen',side_effect=AssertionError('No process')):
    result=unittest.TextTestRunner(stream=capture).run(unittest.defaultTestLoader.loadTestsFromTestCase(ObserverTests))
print(json.dumps({'tests':result.testsRun,'failures':len(result.failures),'errors':len(result.errors)}))
if not result.wasSuccessful(): print(capture.getvalue().encode('ascii','backslashreplace')[:3000].decode(),file=sys.stderr)
sys.exit(0 if result.wasSuccessful() else 1)
