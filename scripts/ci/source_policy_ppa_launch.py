"""Private fixed connection-free probe. Default process entry is CLOSED."""
import json
import os
import re
import selectors
import signal
import subprocess
import sys
import time

import source_policy_ppa_join as J
import source_policy_deb_stream as B

S = J.D.S
CONTRACT = 'ppa-connection-free-functional-v1'
ADDRESS_PROFILE = 'ppa-connection-free-as-40-128-v1'
PARENT_SOFT = 40*1024*1024
CANDIDATE_MEMORY = 128*1024*1024
ENV = {'PATH': '/usr/bin:/bin', 'LANG': 'C', 'LC_ALL': 'C', 'PHP_INI_SCAN_DIR': ''}
REQUIRED = ('json_encode', 'json_decode', 'hash', 'hash_equals', 'hash_hmac',
            'random_bytes', 'preg_match', 'serialize', 'unserialize', 'hrtime')
# Literal code only. No include/bootstrap, file IO, authentication or connection.
PROBE = r'''
$required = array('json_encode','json_decode','hash','hash_equals','hash_hmac','random_bytes','preg_match','serialize','unserialize','hrtime');
$functions = array(); foreach ($required as $name) { $functions[$name] = function_exists($name); }
$optional = array(); foreach (array('ctype_digit','iconv','mb_strlen') as $name) { $optional[$name] = function_exists($name); }
$loaded = get_loaded_extensions(); sort($loaded);
$has = extension_loaded('mysqlnd') && extension_loaded('mysqli');
$stats = $has ? mysqli_get_client_stats() : null;
$zero = $has ? array_intersect_key($stats, array_flip(array('connect_success','connect_failure','com_query'))) : null;
echo json_encode(array('php'=>PHP_VERSION,'sapi'=>PHP_SAPI,'os'=>PHP_OS_FAMILY,'arch'=>php_uname('m'),'word_size'=>PHP_INT_SIZE,'zts'=>PHP_ZTS,
'binary_matches'=>PHP_BINARY===$argv[2],'extension_dir_matches'=>ini_get('extension_dir')===$argv[1],
'ini_loaded'=>php_ini_loaded_file()!==false,'ini_scanned'=>php_ini_scanned_files()!==false,'included'=>count(get_included_files()),
'prepend'=>ini_get('auto_prepend_file'),'append'=>ini_get('auto_append_file'),'modules'=>$loaded,'functions'=>$functions,'optional'=>$optional,
'pdo_mysql'=>extension_loaded('pdo_mysql'),'client'=>$has ? mysqli_get_client_info() : null,
'mysqlnd'=>$has ? (new ReflectionExtension('mysqlnd'))->getVersion() : null,'mysqli'=>$has ? (new ReflectionExtension('mysqli'))->getVersion() : null,
'report_mode'=>$has ? (new mysqli_driver())->report_mode : null,'stats'=>$zero), JSON_THROW_ON_ERROR);
'''.strip()


def _facts(raw, modules):
    S.require(type(raw) is bytes and 0 < len(raw) <= 16384, 'candidate-output-bound')
    try:
        value = json.loads(raw)
    except (ValueError, UnicodeError):
        raise S.Refusal('candidate-json') from None
    keys = {'php', 'sapi', 'os', 'arch', 'word_size', 'zts', 'binary_matches', 'extension_dir_matches',
            'ini_loaded', 'ini_scanned', 'included', 'prepend', 'append', 'modules', 'functions',
            'optional', 'pdo_mysql', 'client', 'mysqlnd', 'mysqli', 'report_mode', 'stats'}
    S.require(type(value) is dict and set(value) == keys and value.get('php') == '8.5.12' and value.get('sapi') == 'cli'
              and value.get('os') == 'Linux' and value.get('arch') == 'x86_64'
              and type(value.get('word_size')) is int and value['word_size'] == 8
              and value.get('zts') is False, 'candidate-runtime-profile')
    S.require(value.get('binary_matches') is True and value.get('extension_dir_matches') is True
              and value.get('ini_loaded') is False and value.get('ini_scanned') is False
              and type(value.get('included')) is int and value['included'] == 0 and value.get('prepend') == value.get('append') == ''
              and value.get('pdo_mysql') is False, 'candidate-configuration')
    S.require(type(value.get('modules')) is list and 0 < len(value['modules']) <= 128
              and all(type(name) is str and re.fullmatch(r'[A-Za-z0-9_ ]{1,64}', name) for name in value['modules'])
              and value['modules'] == sorted(set(value['modules'])), 'candidate-module-inventory')
    S.require(type(value.get('functions')) is dict and set(value['functions']) == set(REQUIRED)
              and all(item is True for item in value['functions'].values()), 'candidate-required-functions')
    S.require(type(value.get('optional')) is dict and set(value['optional']) == {'ctype_digit', 'iconv', 'mb_strlen'}
              and all(type(item) is bool for item in value['optional'].values()), 'candidate-optional-observation')
    if modules:
        S.require(value.get('client') == value.get('mysqlnd') == 'mysqlnd 8.5.12'
                  and value.get('mysqli') == '8.5.12' and type(value.get('report_mode')) is int and value['report_mode'] == 3
                  and value.get('stats') == {'connect_success': '0', 'connect_failure': '0', 'com_query': '0'},
                  'candidate-client-profile')
    else:
        S.require(not {'mysqlnd', 'mysqli'} & set(value['modules']) and value.get('client') is None
                  and value.get('mysqlnd') is None and value.get('mysqli') is None
                  and value.get('stats') is None, 'candidate-baseline-modules')
    return value


def _archive_preflight():
    """Fixed manual-only replacement; legacy archive preflight is untouched."""
    import resource
    A = J.D.A
    S.require(sys.platform == 'linux' and A.struct.calcsize('P') == 8
              and A.Path('/usr/bin/zstd').is_file(), 'existing-decoder')
    resource.setrlimit(resource.RLIMIT_AS, (PARENT_SOFT, CANDIDATE_MEMORY))
    resource.setrlimit(resource.RLIMIT_CORE, (0, 0))
    S.require(resource.getrlimit(resource.RLIMIT_AS) == (PARENT_SOFT, CANDIDATE_MEMORY)
              and resource.getrlimit(resource.RLIMIT_CORE) == (0, 0), 'candidate-parent-limits')
    result = A.subprocess.run(list(A.DECODER_ARGS), input=A.DECODER_SAMPLE,
                              stdout=subprocess.PIPE, stderr=subprocess.DEVNULL, timeout=5,
                              preexec_fn=A.decoder_limit, env=A.decoder_environment())
    S.require(result.returncode == 0 and result.stdout == b'x', 'decoder-cap-preflight')
    return {'parent_address_space': PARENT_SOFT, 'parent_address_space_hard': CANDIDATE_MEMORY,
            'decoder_address_space': A.DECODER_MEMORY, 'decoder_window': A.WINDOW,
            'candidate_address_space': CANDIDATE_MEMORY, 'address_profile': ADDRESS_PROFILE,
            'synthetic_child_reaped': True}


def _preflight():
    # ONLY the exact-job connection-free controller calls this. No numeric input.
    previous = J.D.A.preflight
    try:
        J.D.A.preflight = _archive_preflight
        return B.preflight()  # Same zstd/gzip/xz samples,commands and24/24 codec callback.
    finally:
        J.D.A.preflight = previous


def _candidate_limit():
    import resource
    S.require(resource.getrlimit(resource.RLIMIT_AS) == (PARENT_SOFT, CANDIDATE_MEMORY)
              and resource.getrlimit(resource.RLIMIT_CORE) == (0, 0), 'candidate-inherited-limits')
    resource.setrlimit(resource.RLIMIT_AS, (CANDIDATE_MEMORY, CANDIDATE_MEMORY))
    resource.setrlimit(resource.RLIMIT_CPU, (60, 60))
    resource.setrlimit(resource.RLIMIT_CORE, (0, 0))
    S.require(resource.getrlimit(resource.RLIMIT_AS) == (CANDIDATE_MEMORY, CANDIDATE_MEMORY)
              and resource.getrlimit(resource.RLIMIT_CPU) == (60, 60)
              and resource.getrlimit(resource.RLIMIT_CORE) == (0, 0), 'candidate-child-limits')


class _ClosedProcess:
    def profile(self):
        raise S.Refusal('candidate-process-disabled')


class _NativeProcess:
    def profile(self):
        S.require(sys.platform == 'linux' and os.getuid() > 0 and os.getuid() == os.geteuid(), 'candidate-process-platform')
        import resource
        S.require(resource.getrlimit(resource.RLIMIT_AS) == (PARENT_SOFT, CANDIDATE_MEMORY)
                  and resource.getrlimit(resource.RLIMIT_CORE) == (0, 0), 'candidate-parent-limits')

    def spawn(self, argv):
        return subprocess.Popen(argv, stdin=subprocess.DEVNULL, stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                                close_fds=True, pass_fds=(), start_new_session=True, cwd='/', env=dict(ENV),
                                preexec_fn=_candidate_limit)  # Approved128/128 ONLY for PHP,not codecs.

    def identity(self, process):
        S.require(type(process.pid) is int and process.pid > 0, 'candidate-pid')
        with open('/proc/'+str(process.pid)+'/stat', 'rb') as source:
            raw = source.read(4097)
        S.require(len(raw) <= 4096 and b') ' in raw, 'candidate-start-bound')
        fields = raw.rsplit(b') ', 1)[1].split()
        S.require(len(fields) > 19 and fields[19].isdigit(), 'candidate-start-shape')
        return process.pid, fields[19]

    def selector(self, process):
        result = selectors.DefaultSelector()
        try:
            for pipe in (process.stdout, process.stderr):
                os.set_blocking(pipe.fileno(), False)
                result.register(pipe, selectors.EVENT_READ)
            return result
        except BaseException:
            result.close()
            raise

    read = staticmethod(os.read)
    clock = staticmethod(time.monotonic)

    def kill(self, process, identity, argv):
        S.require(tuple(process.args) == argv and self.identity(process) == identity
                  and os.getpgid(process.pid) == process.pid, 'candidate-recovery-identity')
        os.killpg(process.pid, signal.SIGKILL)


_PROCESS = _ClosedProcess()


def _diagnostic(raw):
    # Classification only,never raw loader paths/messages or an admission fact.
    if any(token in raw for token in (b'cannot allocate memory', b'Cannot allocate memory', b'failed to map segment',
                                     b'out of memory', b'Out of memory', b'unable to allocate', b'Unable to allocate')):
        return 'memory-indication'
    if b'cannot open shared object file' in raw:
        return 'provider-indication'
    if b'Unable to load dynamic library' in raw:
        return 'module-indication'
    return 'unclassified'


class _Launch:
    def __init__(self, output):
        self.output, self.context, self.api = output, output._context, _PROCESS
        self.process, self.identity, self.selector = None, None, None
        self.unknown, self.started, self.attempted, self.mode_fd = False, False, False, None
        self.phase, self.exit, self.output_bytes = 'admission', None, 0
        self.complete, self.observations = False, []
        self.diagnostic = 'none'

    def _metadata(self, mode):
        S.require(time.monotonic() < self.context.retirement_deadline(), 'candidate-retirement-deadline')
        output, storage = self.output, self.output._storage
        output._check_root()
        output._check_file('php', self.mode_fd, J.P._CATALOG[0][4], mode)
        S.require(storage.access(self.mode_fd) == 'read', 'candidate-mode-access')

    def _retire_process(self):
        process = self.process
        if process is None:
            return
        S.require(not self.unknown, 'candidate-release-unknown')  # No retry after ambiguous reap/close.
        try:
            if process.poll() is None:
                S.require(self.identity is not None, 'candidate-process-unknown')
                self.api.kill(process, self.identity, self.launch_argv)
            remaining = self.context.retirement_deadline()-self.api.clock()
            process.wait(timeout=min(5, max(0, remaining)))
            S.require(process.poll() is not None, 'candidate-reap')
        except BaseException:
            self.unknown = True
            raise
        finally:
            for pipe in (process.stdout, process.stderr):
                if not pipe.closed:
                    try:
                        pipe.close()
                    except BaseException:
                        self.unknown = True
            if self.selector is not None:
                try:
                    self.selector.close()
                except BaseException:
                    self.unknown = True
                self.selector = None
        S.require(not self.unknown, 'candidate-release-unknown')
        self.process, self.identity = None, None

    def _probe(self, modules):
        self.phase = 'modules' if modules else 'baseline'
        output, storage = self.output, self.output._storage
        self._metadata(0o500)
        root = output._parent+'/'+output._basename
        executable = root+'/php'
        argv = [executable, '-n', '-d', 'extension_dir='+root, '-d', 'auto_prepend_file=', '-d', 'auto_append_file=',
                '-d', 'display_errors=0', '-d', 'log_errors=0', '-d', 'mysqli.allow_persistent=0']
        if modules:
            argv += ['-d', 'extension='+root+'/mysqlnd.so', '-d', 'extension='+root+'/mysqli.so']
        argv += ['-r', PROBE, '--', root, executable]
        self.launch_argv = tuple(argv)  # Actual fixed argv,not a caller receipt/hash grant.
        self.context._budget.charge(8194)  # Two bounded proc-stat reads,capture + possible recovery.
        self.attempted = True
        def acquire():
            self.process = self.api.spawn(argv)  # Registered BEFORE unmask.
            self.started = True
            self.identity = self.api.identity(self.process)
        storage._critical(acquire)
        self.context._scratch._reserve(32768)  # Bounded buffers plus immutable parse copies.
        buffers = {self.process.stdout: bytearray(), self.process.stderr: bytearray()}
        try:
            def acquire_selector():
                self.selector = self.api.selector(self.process)
            storage._critical(acquire_selector)  # Registration before signal restore.
            deadline = min(self.context._budget.deadline, self.api.clock()+20)
            while self.selector.get_map():
                self.context.check()
                remaining = deadline-self.api.clock()
                S.require(remaining > 0, 'candidate-probe-deadline')
                for key, unused in self.selector.select(min(0.05, remaining)):
                    chunk = self.api.read(key.fd, 4096)
                    self.context._budget.charge(len(chunk))
                    self.output_bytes += len(chunk)
                    S.require(sum(map(len, buffers.values()))+len(chunk) <= 16384, 'candidate-output-bound')
                    if chunk:
                        buffers[key.fileobj].extend(chunk)
                    else:
                        self.selector.unregister(key.fileobj)
            self.exit = self.process.wait(timeout=max(0, deadline-self.api.clock()))
            stdout, stderr = bytes(buffers[self.process.stdout]), bytes(buffers[self.process.stderr])
            if self.exit != 0 or stderr:
                self.diagnostic = _diagnostic(stderr)
            S.require(self.exit == 0 and not stderr, 'candidate-'+self.phase+'-startup')
            return _facts(stdout, modules)
        finally:
            buffers.clear()
            stdout = stderr = chunk = None
            self.context._scratch._retained -= 32768
            self._retire_process()
            self._metadata(0o500)

    def run(self):
        self.api.profile()  # Default is CLOSED before mode/process effects.
        output, storage = self.output, self.output._storage
        S.require(output._sealed and output._active is None and self.context._source_count == 2
                  and not self.context._decoder_unknown and self.context._scratch.usage[0] == 0,
                  'candidate-source-output-barrier')
        changed = False
        try:
            def acquire_mode():
                self.mode_fd = storage.readonly(output._root, 'php', nofollow=True, inheritable=False)
            try:
                storage._critical(acquire_mode)  # Caller registration also precedes unmask.
            except BaseException:
                if self.mode_fd is None:
                    self.unknown = True
                    output._uncertain = self.context._decoder_unknown = True
                raise
            self._metadata(0o400)
            changed = True  # An ambiguous mode result must be reconciled,not assumed absent.
            S.require(storage._api.chmod(self.mode_fd, 0o500) is None, 'candidate-mode-result')
            self._metadata(0o500)
            self.observations.append(self._probe(False))
            self.observations.append(self._probe(True))
            S.require(set(self.observations[1]['modules'])-set(self.observations[0]['modules']) == {'mysqlnd', 'mysqli'}
                      and set(self.observations[0]['modules']) <= set(self.observations[1]['modules']), 'candidate-module-delta')
        finally:
            try:
                self._retire_process()
                if changed:
                    self._metadata(0o500)
                    S.require(storage._api.chmod(self.mode_fd, 0o400) is None, 'candidate-mode-restore')
                    self._metadata(0o400)
            except BaseException:
                self.unknown = True
                output._uncertain = self.context._decoder_unknown = True
                raise
            finally:
                if self.mode_fd is not None:
                    fd = self.mode_fd
                    self.mode_fd = None
                    try:
                        storage.close(fd)
                    except BaseException:
                        self.unknown = True
                        output._uncertain = self.context._decoder_unknown = True
                        raise
        self.complete, self.phase = True, 'complete'  # AFTER child/mode-FD restoration/retirement.

    def receipt(self):
        return {'attempted': self.attempted, 'executed': self.started, 'complete': self.complete,
                'stage': self.phase, 'last_exit': self.exit, 'output_bytes': self.output_bytes,
                'diagnostic': self.diagnostic, 'release_unknown': self.unknown, 'observations': self.observations}


class _ConnectionFreeOutput(J._JoinedOutput):
    def seal(self):
        result = super().seal()  # ALL source/decoder closure and fixed readback first.
        launch = _Launch(self)
        self._context._candidate_observation = launch  # Before any mode/process acquisition.
        launch.run()
        return result  # Preparation flags stay false;separate owner reports actual effects.
