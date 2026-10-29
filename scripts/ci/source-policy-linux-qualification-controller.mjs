// Fixed private proof supervision. Importing pure checks never spawns a process.
import { spawn, spawnSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readFileSync, lstatSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

export function validTerminal(row, image, run, pid) {
  return Boolean(row && typeof row === 'object' && !Array.isArray(row) && Object.keys(row).join(',') === 'event,image,run,pid,checks,groups,mode,closed'
    && typeof image === 'string' && /^[a-f0-9]{64}$/.test(image) && typeof run === 'string' && /^[a-f0-9]{32}$/.test(run) && Number.isSafeInteger(pid) && pid > 0
    && row.event === 'closed' && row.image === image && row.run === run && row.pid === pid
    && Number.isSafeInteger(row.checks) && row.checks > 0 && row.checks <= 99999
    && row.groups === 9 && row.mode === 0 && row.closed === true);
}

// Diagnostics only: never raw streams, paths, messages or a success/admission grant.
export function childFailureSummary(result) {
  const channel = value => {
    const raw = Buffer.from(typeof value === 'string' ? value : '', 'utf8');
    return { bytes: raw.length, truncated: raw.length > 16384,
      sha256_prefix: createHash('sha256').update(raw.subarray(0, 16384)).digest('hex') };
  };
  let worker = null;
  if (typeof result.stderr === 'string' && result.stderr.length <= 1024 && /^\{[^\r\n]+\}\n$/.test(result.stderr)) {
    try {
      const row = JSON.parse(result.stderr);
      const diagnostic = row.diagnostic;
      const validDiagnostic = diagnostic === null || (diagnostic && Object.keys(diagnostic).join(',') === 'severity,source,line'
        && [2,8,256,512,1024,2048,4096,8192,16384].includes(diagnostic.severity)
        && ['worker','core','other'].includes(diagnostic.source) && Number.isSafeInteger(diagnostic.line) && diagnostic.line > 0 && diagnostic.line <= 100000);
      const escape = row.escape;
      const ack = escape?.ack, query = escape?.query;
      const validAck = ack === null || (ack && !Array.isArray(ack) && Object.keys(ack).join(',') === 'shape,affected,errno'
        && typeof ack.shape === 'boolean' && ['one','zero','false','null','other'].includes(ack.affected)
        && ['0','1062','1064','1142','1146','1205','1213','1366','2006','2013','null','other'].includes(ack.errno));
      const validQuery = query === null || (query && !Array.isArray(query) && Object.keys(query).join(',') === 'ascii,mb_strlen,is_wp_error,l10n,commands,ready'
        && ['ascii','mb_strlen','is_wp_error','l10n'].every(key => typeof query[key] === 'boolean')
        && ['unobserved','zero','one','two','many','unknown'].includes(query.commands) && (query.ready === null || typeof query.ready === 'boolean'));
      const validEscape = escape === null || (escape && !Array.isArray(escape)
        && Object.keys(escape).join(',') === 'step,prepared,ack_exact,row_exact,bytes_equal,ack,query'
        && ['prepare','execute','rows','bytes','release'].includes(escape.step)
        && ['prepared','ack_exact','row_exact','bytes_equal'].every(key => escape[key] === null || typeof escape[key] === 'boolean') && validAck && validQuery);
      if (Object.keys(row).join(',') === 'event,stage,phase,checks,groups,assertion,exception,diagnostic,escape,escape_release' && row.event === 'failure'
        && ['source-audit','ambient','escape','positive','noop-stale','update','race','duplicate','transform-reentry','lock','post-change','errno'].includes(row.stage)
        && ['runtime','core-pins','core-load','owned-load','cache','profile','image','native'].includes(row.phase)
        && Number.isSafeInteger(row.checks) && row.checks >= 0 && row.checks <= 99999
        && Number.isSafeInteger(row.groups) && row.groups >= 0 && row.groups <= 9 && typeof row.assertion === 'string' && /^[a-f0-9]{64}$/.test(row.assertion)
        && ['RuntimeException','Error','TypeError','ValueError','ParseError','ArgumentCountError','ReflectionException','other'].includes(row.exception) && validDiagnostic && validEscape
        && (row.escape_release === null || typeof row.escape_release === 'boolean')) worker = row;
    } catch { /* Malformed child DATA is represented only by bounded stream digests. */ }
  }
  return { status: Number.isSafeInteger(result.status) && result.status >= 0 && result.status <= 255 ? result.status : null,
    signal: ['SIGKILL','SIGTERM'].includes(result.signal) ? result.signal : null,
    error: ['ETIMEDOUT','ENOBUFS','ENOENT','EACCES'].includes(result.error?.code) ? result.error.code : null,
    stdout: channel(result.stdout), stderr: channel(result.stderr), worker };
}

// Context-limited diagnostic review, not a general harmless-warning whitelist.
export function reviewLog(raw, hostname) {
  if (typeof raw !== 'string' || Buffer.byteLength(raw) > 65536 || /[^\x09\x0a\x0d\x20-\x7e]/.test(raw) || typeof hostname !== 'string' || !/^[a-f0-9]{12}$/.test(hostname)
    || /\[ERROR\]|fatal|crash|assertion.+fail/i.test(raw)) throw Error('Diagnostic profile refused');
  const lines = raw.split(/\r?\n/).filter(Boolean);
  if (lines.length > 128 || lines.some(line => Buffer.byteLength(line) > 1024)) throw Error('Unbounded diagnostics');
  for (const line of lines.filter(line => line.includes('[Warning]'))) {
    const message = line.replace(/^\d{4}-\d\d-\d\d (?:[01]\d|2[0-3]| [0-9]):[0-5]\d:[0-5]\d +\d+ \[Warning\] /, '');
    const known = [
      'failed to retrieve the MAC address',
      'WSREP: Failed to guess base node address. Set it explicitly via wsrep_node_address.',
      'WSREP: Guessing address for incoming client connections failed. Try setting wsrep_node_incoming_address explicitly.',
      'mariadbd: io_uring_queue_init() failed with EPERM: sysctl kernel.io_uring_disabled has the value 2, or 1 and the user of the process is not a member of sysctl kernel.io_uring_group. (see man 2 io_uring_setup).',
      `'user' entry 'root@${hostname}' ignored in --skip-name-resolve mode.`,
      `'proxies_priv' entry '@% root@${hostname}' ignored in --skip-name-resolve mode.`,
    ];
    if (!known.includes(message)) throw Error('Unclassified startup warning');
  }
  const selected = lines.filter(line => /\[Warning\]|\[ERROR\]|ready for connections|Shutdown complete|uring|aio/i.test(line));
  if (selected.length > 32) throw Error('Unbounded selected diagnostics');
  return { bytes: Buffer.byteLength(raw), sha256: createHash('sha256').update(raw).digest('hex'), selected, full: lines };
}

const args = ['-n', '-d', 'extension=mysqlnd', '-d', 'extension=mysqli', '-d', 'memory_limit=64M',
  '-d', 'mysqli.allow_persistent=0', '-d', 'default_socket_timeout=3', '-d', 'mysqlnd.net_read_timeout=3',
  'scripts/ci/source-policy-linux-qualification.php', '--owned-linux-qualification'];

function reconcile(cid) {
  const sql = 'SELECT (SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()),(SELECT COUNT(*) FROM information_schema.INNODB_TRX);';
  const result = spawnSync('docker', ['exec', cid, 'mariadb', '--no-defaults', '--protocol=SOCKET',
    '--socket=/run/adb-owned/native.sock', '--user=root', '--skip-reconnect', '--connect-timeout=2',
    '--batch', '--raw', '--skip-column-names', '--execute=' + sql], { timeout: 5000, maxBuffer: 4096, encoding: 'utf8' });
  if (result.error || result.signal || result.status !== 0 || result.stderr !== '' || result.stdout !== '0\t0\n')
    throw Error('Independent all-server release reconciliation failed');
}

async function main() {
  if (process.platform !== 'linux' || process.env.GITHUB_ACTIONS !== 'true' || process.env.GITHUB_EVENT_NAME !== 'workflow_dispatch'
    || process.env.GITHUB_REPOSITORY !== 'NichlasB/alynt-drime-backups-dashboard' || process.env.GITHUB_REF !== 'refs/heads/master'
    || !/^[a-f0-9]{40}$/.test(process.env.EXPECTED_SHA ?? '') || process.env.EXPECTED_SHA !== process.env.GITHUB_SHA
    || !/^[0-9]+$/.test(process.env.GITHUB_RUN_ID ?? '') || !/^[0-9]+$/.test(process.env.GITHUB_RUN_ATTEMPT ?? '')
    || process.argv.length !== 3 || !['audit', 'proof', 'diagnostics'].includes(process.argv[2])) throw Error('Fixed owned job required');
  const stage = process.env.RUNNER_TEMP + '/adb-linux-qualification-' + process.env.GITHUB_RUN_ID + '-' + process.env.GITHUB_RUN_ATTEMPT;
  const run = createHash('sha256').update(process.env.GITHUB_RUN_ID + ':' + process.env.GITHUB_RUN_ATTEMPT).digest('hex').slice(0, 32);
  if (process.argv[2] === 'audit') {
    const result = spawnSync('php', [...args, 'audit'], { timeout: 20000, maxBuffer: 16384, encoding: 'utf8' });
    if (result.error || result.signal || result.status !== 0 || result.stderr !== '' || !/^\{[^\r\n]+\}\n$/.test(result.stdout))
      throw Error('Source audit refused: ' + JSON.stringify(childFailureSummary(result)));
    const row = JSON.parse(result.stdout);
    if (row.event !== 'audit' || !/^[a-f0-9]{64}$/.test(row.image) || row.run !== run || row.native_connections !== 0 || row.sql_queries !== 0 || row.checks < 1) throw Error('Invalid source audit');
    process.stdout.write(result.stdout); return;
  }
  const cid = readFileSync(stage + '/owned.cid', 'utf8').trim();
  if (!/^[a-f0-9]{64}$/.test(cid)) throw Error('Exact owned container required');
  if (process.argv[2] === 'diagnostics') {
    for (const file of ['bootstrap.log', 'server.log']) {
      const path = stage + '/socket/' + file; const info = lstatSync(path);
      if (!info.isFile() || info.isSymbolicLink() || info.uid !== process.getuid() || info.size > 65536) throw Error('Unowned diagnostics');
      const raw = readFileSync(path, 'utf8');
      if (process.env.QUALIFICATION_PASSWORD && raw.includes(process.env.QUALIFICATION_PASSWORD)) throw Error('Secret-bearing diagnostics refused');
      process.stdout.write(JSON.stringify({ diagnostics: file, ...reviewLog(raw, cid.slice(0, 12)) }) + '\n');
    }
    return;
  }
  if (!/^[a-f0-9]{64}$/.test(process.env.QUALIFICATION_IMAGE ?? '')) throw Error('Audited image required');
  reconcile(cid);
  const child = spawn('php', [...args, 'proof'], { stdio: ['pipe', 'pipe', 'pipe'] });
  let out = '', err = '', terminal = null, failure = null;
  const deadline = setTimeout(() => { failure = Error('Owned child deadline'); child.kill('SIGKILL'); }, 25000);
  child.stdin.on('error', () => { failure = Error('Owned release channel failed'); });
  child.stdout.on('data', chunk => {
    try {
      out += chunk.toString('utf8');
      if (out.length > 4096 || terminal) throw Error('Unexpected child output');
      if (!out.includes('\n')) return;
      if (!/^\{[^\r\n]+\}\n$/.test(out)) throw Error('Invalid child record');
      terminal = JSON.parse(out);
      if (!validTerminal(terminal, process.env.QUALIFICATION_IMAGE, run, child.pid)) throw Error('Invalid held terminal');
      reconcile(cid); // Child remains alive after closing its own sessions.
      child.stdin.end('ACK:' + run + ':' + process.env.QUALIFICATION_IMAGE + '\n');
    } catch (error) { failure = error; child.kill('SIGKILL'); }
  });
  child.stderr.on('data', chunk => { err += chunk.toString('utf8'); if (err.length > 1024) { failure = Error('Unbounded child diagnostic'); child.kill('SIGKILL'); } });
  await new Promise((resolve, reject) => {
    child.on('error', error => { clearTimeout(deadline); reject(error); });
    child.on('close', (code, signal) => {
      clearTimeout(deadline);
      const diagnostic = JSON.stringify(childFailureSummary({ status: code, signal, stdout: out, stderr: err }));
      if (failure || signal || code !== 0 || err !== '' || !terminal) reject(failure ?? Error('Owned child refused: ' + diagnostic)); else resolve();
    });
  });
  reconcile(cid);
  process.stdout.write(JSON.stringify({ ...terminal, independent_live: 'zero', independent_post_exit: 'zero', qualification: 'lab-only' }) + '\n');
}

if (process.argv[1] === fileURLToPath(import.meta.url)) {
  main().catch(error => { process.stderr.write('Owned Linux supervision failed: ' + error.message + '\n'); process.exitCode = 1; });
}
