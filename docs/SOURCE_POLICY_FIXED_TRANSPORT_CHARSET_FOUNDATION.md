# Source-Policy Fixed Transport and Core-Derived Charset Safety Foundation

## Approved boundary and baseline — 2026-10-28

Repository-only implementation of the next foundation in the
[deployment-matched design](SOURCE_POLICY_DEPLOYMENT_MATCHED_QUALIFICATION_DESIGN.md).
Clean/synced dashboard baseline `614902449a2cfb780ce00b6a96105993b216e91a`.
Explicit approval covers this R3 private transport/session boundary, isolated
API/IO tests, validation, commit, push and exact-head CI; autonomous R2 is not
raised. No site/SSH/LocalWP access, genuine authentication/bootstrap, native
connection, SQL/storage/fixture operation, service/account/permission/credential
change, release/package/tag/deployment or new native CI job. Version0.1.77,
schema8, PHP7.4/WordPress6.0 support, application writer/form and wiring unchanged.
Uploader `3dfcb15b5cab8191df7d984251986ead140ded52` and toolkit
`c067eb61883035a1764d830bccffc1e542d3e1dc` remain clean, synced and unmodified.

## Implemented responsibilities and private compatibility

- Transport_Profile is pure finite tuple consistency, not a connection grant.
  The private frame now requires12 exact keys, adding transport/socket/db_host.
  Missing old fields refuse; no coercion, default transport or fallback.
- TCP preserves core6.0/7.1.3, numeric loopback, explicit integer port, null socket
  and flags0. IPv6 retains raw bracketed DB_HOST and passes mysqlnd's bracketed
  host representation. Unix socket is a Linux-only core7.1.4 candidate: localhost,
  null port, explicit ASCII absolute path <=96 bytes, no traversal/control/NUL,
  remote/persistent/URI/drive/default endpoint or alternate socket search.
- Bootstrap_Profile binds the genuine parser method and its absint declaration
  before invocation. Raw trusted DB_HOST must equal the entire allowed tuple;
  parsed host/port/socket/IPv6 output must agree before parent initialization or
  allocation. Parent object initialization receives DB_HOST intact under the
  unchanged final db_connect refusal; the sole owned call uses distinct arguments.
- Configured utf8/utf8mb4 and empty/qualified collate remain exact snapshots of
  constants. Existing init/determine/set helpers compute negotiated values after
  connect. Session readback still independently requires utf8mb4/unicode_520,
  exact modes/limits/identity and unchanged options schema/index metadata.
- Connected-server rows remain exact:6.0/7.1.3 ->10.6.23-MariaDB, candidate7.1.4
  ->10.11.18-MariaDB. No prefixes or installed-binary-to-runtime grant. The
  candidate's intended PHP8.5.12/core/driver/distribution still needs native
  qualification; hosted PHP7.4/8.3 doubles do not establish that environment.
- Shared reporting is privately captured from the fixed source-bound
  mysqli_driver observer, not a request/frame flag. Only integer0/3 are accepted;
  socket candidate requires0. Bootstrap and active preparation/dispatch recheck
  the captured posture, never toggle/restore it. Post-dispatch changes revoke
  readiness and map to unavailable before a write or uncertain after submission,
  including zero/known-error acknowledgements. No replay/reconnect or raw error.
- Existing20s/16-credit/2s-connect/3s-read/3s-statement/1s-lock bounds, ready-last,
  order/reentry latches and independent partial driver/result/callback cleanup
  remain. Credentials/frame/report binding are cleared at terminal cleanup.
  Preflight refusal before a callback lease does not fabricate a complete callback
  cleanup receipt, even when zero native allocations occurred.

Private fixture frames/API declarations are explicitly migrated. The manual
Windows base composition also receives the new tuple/parser binding and the
existing formatting/functions/option pins already owned by the adapter harness;
functions provides absint. This is source migration only, not an executed audit.
Existing loaded-file image collection includes the new required runtime helper.
ADBC1/ADBC2 bytes, counter rules and historical receipts are unchanged.

## Non-vacuous isolated verification and scoped review

Three new classes add71 tests/230 assertions:46 tuple/server cases,17 initializer
transcripts and8 active reporting-context cases. They cover positive TCP/OFF/IPv6,
configured utf8 -> computed utf8mb4 -> independent readback, parser/source/old
frame/refusal ordering, argument positions, malformed tuples, platform/path
bounds, strict types/server identity, invalid/changed reporting, helper/readback
disagreement, no replay and once-only cleanup. The socket transcript executes the
actual dormant initializer with API doubles on Linux CI; Windows asserts refusal
before allocation, not a skip or simulated Linux runtime grant.

Initial incomplete fixture composition produced16 assertion failures; the positive
paths then passed after migration. A changed-report read initially mapped to a
storage error; the regression now requires unavailable before any write, versus
uncertain after one submitted attempt. Success paths require actual orchestration,
not an owner that always returns unavailable. Existing bootstrap/schema/source/
callback/guarded-entry tests remain, with no skips/assertions removed.

Eleven temporary semantic controls were assertion-killed and the five affected
source SHA256 values restored exactly: raw endpoint, Linux platform, parser
result, parser helper source, socket-argument type/position, required helper
charset, client readback, bootstrap reporting context, active reporting admission,
single-use object-reentry latch and post-dispatch reporting change. One initial
dataset-name filter selected no tests and was not counted; the actual object-
reentry worker was then executed and assertion-killed. No startup failures count.
Endpoint-position control ran with TCP doubles locally; positive Unix socket
argument checks belong to Linux isolated CI, not a physical socket proof.

Source-scoped review covered trust/guard ordering, complete tuple parsing,
configured/computed/observed charset separation, exact server rows, shared mode
capture, error confidentiality/uncertainty, budgets and cleanup. Source bindings
are not distribution authentication. RAM subclasses model only existing IO seams;
Bootstrap_Profile rejects subclasses for actual private initialization. No UI,
Database Review, genuine authentication, physical transaction/native transport or
whole-plugin release-readiness credit is claimed.

## Executed local QA and publication

- New transport/reporting suites71/230; combined bootstrap/transport/reporting
 228/801, no failures/skips after exact restoration.
- Final PHP8.5.1 full PHPUnit --verbose --do-not-cache-result:1,956 tests/8,442
  assertions, no failures; two existing Sodium-dependent signing skips only.
- npm lint/PHPCS221 files, admin16/16, check:structure149 runtime/227 support/
 202 test files/199 class names, build, strict Composer validation and
  Composer/npm moderate audits pass; zero known advisories/vulnerabilities.
- Final scoped diff/security/path review and diff hygiene pass. Bootstrap,
  Source_Policy/application writer, Native_Adapter/CAS SQL, guarded form, admin,
  schema/uninstall, assets, CI, dependencies and package metadata are unchanged.
  Uploader/toolkit statuses and heads unchanged; no rollout tracker write.

Exact-head publication evidence is pending until the implementation is pushed
and its configured CI completes; no success is inferred from local checks.

## Evidence limits and exact next approval gate

The earlier Windows/TCP native bootstrap, connected-release, adapter and ambient/
report-OFF receipts remain historical evidence for their recorded images. Changed
runtime/base composition stales those images for current-source qualification;
no receipt is rewritten, refreshed or reinterpreted as fresh native success.
Manual composition/helper source closure still needs an approved fresh audit and
preserved Windows/TCP regression, as well as intended Linux and minimum profiles.
The prior static production audit does not establish actual server/schema/session,
hook/cache/ambient state, activation or current-source compatibility.

Implementation-plan progress: deployment-matched isolated foundation implemented;
the next meaningful step is an environment/qualification gate, not maintenance.
Recommended next gated slice: **Off-Production Linux/PHP8.5.12/mysqlnd/
MariaDB10.11.18 Unix-Socket Environment Preflight**, preferably a separately
approved opt-in ephemeral GitHub Actions lab. Approve exact environment/image
acquisition, resource/ownership/cleanup boundaries before any setup; database
fixtures/native qualification remain separately gated. Do not install workstation
Docker/WSL/compilers, inspect production again, wire factories, migrate the legacy
writer, package or deploy on this foundation's authority.
