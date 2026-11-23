"""Bounded codec pipes over a DEB byte slice; no member staging files."""
import os
from pathlib import Path
import selectors
import subprocess
import time

# This helper is imported only after the fixed DATA policy in the same directory.
from source_policy_deb_data import A, S

COMMANDS = {
    '.zst': A.DECODER_ARGS,
    '.gz': ('/usr/bin/gzip', '-d', '-c'),
    '.xz': ('/usr/bin/xz', '--decompress', '--stdout', '--memlimit-decompress=8MiB'),
}
SAMPLES = {
    '.zst': A.DECODER_SAMPLE,
    '.gz': bytes.fromhex('1f8b080000000000020aab00008316dc8c01000000'),
    '.xz': bytes.fromhex('fd377a585a000004e6d6b4460200210100000000372797d6010000780000000045aeef83f8ee160a00011901a52c81cc1fb6f37d010000000004595a'),
}


def preflight():
    result = A.preflight()  # Existing 40/24 MiB caps and zero-core policy.
    for suffix in ('.gz', '.xz'):
        command = COMMANDS[suffix]
        S.require(Path(command[0]).is_file(), 'codec-unavailable')
        probe = subprocess.run(list(command), input=SAMPLES[suffix], stdout=subprocess.PIPE,
                               stderr=subprocess.DEVNULL, timeout=5, preexec_fn=A.decoder_limit, env=A.decoder_environment())
        S.require(probe.returncode == 0 and probe.stdout == b'x', 'codec-preflight')
    return {**result, 'gzip': True, 'xz': True, 'zstd': True}


class Decoder:
    def __init__(self, source, suffix, budget):
        S.require(suffix in COMMANDS, 'codec-profile')
        if suffix == '.zst':
            S.require(source.remaining >= 18, 'zstd-frame-bound')
            # Inspect without charging/consuming a different slice or materializing it.
            position = source.file.tell()
            head = source.file.read(18)
            source.file.seek(position)
            budget.charge(len(head))
            facts = A.frame(head)
            S.require(facts['window_bytes'] <= A.WINDOW and (facts['declared_content_bytes'] is None
                      or facts['declared_content_bytes'] <= 192*A.MIB-budget.total), 'codec-frame-budget')
        self.source, self.budget, self.pending, self.ended = source, budget, b'', False
        self.selector = None
        self.process = subprocess.Popen(list(COMMANDS[suffix]), stdin=subprocess.PIPE, stdout=subprocess.PIPE,
                                        stderr=subprocess.DEVNULL, preexec_fn=A.decoder_limit, env=A.decoder_environment(),
                                        close_fds=True, pass_fds=())
        try:
            self.selector = selectors.DefaultSelector()
            for pipe, event in ((self.process.stdin, selectors.EVENT_WRITE), (self.process.stdout, selectors.EVENT_READ)):
                os.set_blocking(pipe.fileno(), False)
                self.selector.register(pipe, event)
        except BaseException:
            self.close()
            raise

    def read(self, count):
        S.require(0 <= count <= 65536, 'codec-read-bound')
        if not count or self.ended:
            return b''
        while True:
            self.budget.charge(0)
            for key, event in self.selector.select(min(1, max(0, self.budget.deadline-time.monotonic()))):
                if key.fileobj is self.process.stdin:
                    if not self.pending:
                        self.pending = self.source.read(65536)
                    if not self.pending:
                        self.selector.unregister(self.process.stdin)
                        self.process.stdin.close()
                    else:
                        try:
                            written = os.write(self.process.stdin.fileno(), self.pending[:65536])
                            self.pending = self.pending[written:]
                        except BlockingIOError:
                            pass
                        except BrokenPipeError:
                            raise S.Refusal('codec-input-refused') from None
                else:
                    try:
                        data = os.read(self.process.stdout.fileno(), count)
                    except BlockingIOError:
                        continue
                    if data:
                        self.budget.charge(len(data))
                        return data
                    self.ended = True
                    S.require(not self.pending and self.source.remaining == 0, 'codec-input-incomplete')
                    S.require(self.process.wait(timeout=5) == 0, 'codec-exit')
                    return b''

    def close(self, *, _deadline=None):
        if self.process.poll() is None:
            self.process.kill()
        timeout = 5 if _deadline is None else min(5, max(0, _deadline-time.monotonic()))
        try:
            self.process.wait(timeout=timeout)
        finally:
            for pipe in (self.process.stdin, self.process.stdout):
                if not pipe.closed:
                    pipe.close()
            if self.selector is not None:
                self.selector.close()
