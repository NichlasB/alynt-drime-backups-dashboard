# Off-Production Linux Genuine-Core/OFF and Diagnostic Native Qualification

## Approval, audit-first plan and owned target — 2026-10-29

Approved next harness/owned proof from the
[exact candidate foundation](SOURCE_POLICY_LINUX_EXACT_BUILD_CANDIDATE_FOUNDATION.md).
Clean/synced restore-point baseline `7cabc013b934494107e3335827541f7f941a148b`.
Explicit R3 authority: source audit, private manual-only GitHub-hosted Ubuntu24.04
x86_64 lab, immutable PHP8.5.12/mysqlnd/core7.1.4/MariaDB image, fresh prerequisites,
one disposable options-schema table/account, one ordered body and exact cleanup.
Autonomous R2 is not raised. This is not Plugin Tester or an installed WordPress
site; no LocalWP/Novamira, registry-site, SSH or live operation is needed or run.
Only the new empty, labeled owned instance can receive SQL/account/fixture writes.
Existing users, tables, options, cron, services, credentials and installed plugin
lifecycle remain untouched. No production wiring, consent grants, activation, rollback,
runtime guard change, selected response loss, package, tag, release or deployment.

Audit decisions and implementation:

- New manual-only workflow with contents:read, immutable action/core refs,
  expected master SHA, one10-minute job, non-cancelling concurrency and always
  cleanup. No push/PR/schedule native trigger, site secret or shared DB service.
- Fresh unchanged no-database preflight removes its own checkout/container before
  the same official core commit is reacquired. New connection-free audit checks
  all11 source pins, genuine declaration/driver/parser/cache admission, closed
  public factories, zero connections/SQL and initial report3. Freeze the complete
  loaded producer/PHP/module/helper/controller/workflow image before any DB init.
- Reuse the existing nine-group ambient operation/case sources unchanged, rather
  than copy/rewrite cases or relax acceptance. Reflection supplies only approved
  constructor inputs/private frame/context; no active/ready/handle injection.
  Actual wpdb db_connect(false) creates the ambient and chooses OFF. The dedicated
  owner allocates its own handle and must preserve ambient identity/callbacks/OFF.
- PHP captures only fixed stage/assertion-digest failures. Controller buffers and
  deadlines are bounded; no raw exceptions/SQL/credentials are returned. The
  closed terminal is correlated to the parent's run/image/actual PID, held until
  independent all-server live reconciliation, then ACK/exit/post reconciliation.
  This is evidence correlation, not a production admission/authentication grant.

## Resource, fixture and cleanup contract

Same official amd64 image as the identity audit:
`mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5`.
Private Docker endpoint only; no overrides, network:none plus skip-networking,
non-root owned UID/GID, readonly root, all capabilities dropped, no-new-privileges,
64PIDs/512MiB/1CPU. Data256MiB and tmp16MiB are private UID tmpfs. Only the fresh
mode700 socket/log directory is bound into the container. No host/site data mount.
Fixed label+full CID and exact path/owner checks, never global prune/broad cleanup.

Fresh vendor system-table initialization is <=40s, readiness<=50s. PHP source
audit<=20s; native worker<=25s including held release, original body<20s, parent
observer<=5s and administrative client<=10s. Ambient read defaults3s, persistent
clients disabled; actual native initializer's20s/16-credit/2s-connect/3s-read/
3s-statement/1s-lock limits stay unchanged. Server cap remains10 connections,
stop5s under outer8s, with no IO/kernel/network/budget relaxation.

Unlike the earlier metadata-only read_only instance, this newly initialized lab
must allow explicitly owned fixture writes. No existing server is reconfigured.
Before creating adb_owned: exact version/no-network/wsrep/autocommit/no-transaction,
zero other all-server sessions/INNODB_TRX/application schemas are required.
Create only adb_owned.adbb_<12 run-bound hex>_options with the exact four-column,
three-index InnoDB/unicode_520 schema; copy no application rows. One adbp@localhost
account receives SELECT/INSERT/UPDATE/DELETE on that table only. Its random
password stays in the owned parent/child environment and administrative stdin,
never args/source/log output. Actual SELECT denial against mysql.global_priv
must show1142 before the proof; fixed process observers alone use fresh-lab root.
This is a disposable proof account, not application-user authentication.

Required prelude: actual owned prepare/execute round trip for quote, backslash,
NUL, percent and utf8mb4 bytes, plus independent ambient-preserving release.
Required body: positive view/save/cache/observer preservation; one-read noop/stale;
conditional removal; competing update and create conflict without replay;
transform/reentry refusal; bounded lock failure; post-commit admission uncertainty;
direct native1064/1062/1205 observations. Genuine core preparation/escaping and
charset negotiation execute, not stubs. All9 groups must complete in order.
Independent PROCESSLIST excludes only its current observer, never NULL/foreign
database sessions; INNODB_TRX is all-server. Held and post-exit states require0/0.
Finally preview no other sessions/trx, drop only the new schema/account, require
0 app schemas/owned accounts/sessions/trx, normal exit0/OOM=false, remove the exact
container/known leaves/directories and release tmpfs. On any failed evidence,
cleanup still runs but no successful qualification or automatic retry is claimed.
Tmpfs release is not physical secure-wipe attestation.

## Diagnostic evidence and source review

Server diagnostics are distinct from PHP/mysqli warnings, which still refuse.
Complete controlled startup/shutdown logs are bounded64KiB/128 lines/1,024 bytes
per line; selected warnings/readiness/AIO/shutdown records<=32. Size/hash and full
ASCII context are retained, not just filtered absence of ERROR. Exact known
warning texts are context checks only: unknown/changed warning, ERROR/fatal/crash,
secret-bearing output or overflow stops the attempt; a matching warning never
overrides a failed native operation/release/resource bound.

Vendor sources inspected read-only at MariaDB mariadb-10.11.18:
[AIO selection](https://github.com/MariaDB/server/blob/mariadb-10.11.18/tpool/aio_linux.cc),
[uring error](https://github.com/MariaDB/server/blob/mariadb-10.11.18/tpool/aio_liburing.cc),
[InnoDB fallback](https://github.com/MariaDB/server/blob/mariadb-10.11.18/storage/innobase/os/os0file.cc),
[WSREP address setup](https://github.com/MariaDB/server/blob/mariadb-10.11.18/sql/wsrep_mysqld.cc).
The default native AIO selector tries uring, then libaio; if native AIO cannot
initialize, InnoDB can turn native AIO off. Thus EPERM alone does not identify the
effective IO implementation. Record kernel.io_uring_disabled, observed
innodb_use_native_aio and full fallback lines together with actual bounded native
behavior. Do not enable kernel facilities or modify server IO options to green.
Address/MAC warnings occur in the network-disabled lab; require wsrep_on0, local
socket success and clean shutdown. Ignored hostname entries must bind the exact
ephemeral CID hostname, not a wildcard account grant. No networking/WSREP/account
widening to silence diagnostics. These sources explain behavior, not reproducible-
build attestation of the image or a production diagnostic exemption.

The exact pinned genuine core connector was read through the official repository
API before implementation; its own report-OFF call precedes allocation and
normal helper/session setup. No direct reporting toggle is added to the harness.
No full WordPress bootstrap, real-user authentication, default-constructor failure,
Windows/minimum native compatibility, UI or whole-plugin release-readiness claim.

## Isolated verification and publication

New isolated PHP7/63 and Node3/3 pass; combined preflight/identity/qualification
37 tests/159 assertions. Non-owned/extension-free invocations under open_basedir
refuse before core/source/socket access. Terminal DATA tests independently reject
source/run/PID/mode/group/type/closure changes and missing/extra/reordered fields.
Diagnostic tests require full bounded context and reject altered hostname/text,
unknown warnings, fatal/errors, NUL/truncated/unbounded data.
Two temporary controls were assertion-killed and exact SHA256 restored: omitted
closed requirement (PHP) and bypassed unknown-warning refusal (Node). Restored:
contract209e02574985cf9c0e8eb1a4355a4864727e66c3fa3f0817c1e27fda3a2f7302,
controller5bcc1768c16b5f38da344f429dd38cf4479ffb17443c23dbbea6862f561f1f6a.
Bash -n, Node --check, PHP syntax/scoped PHPCS pass. No local native body ran.
Runtime/prior manual cases/preflight/identity/core pin bytes remain unchanged.
Final local PHP8.5.1 PHPUnit --verbose --do-not-cache-result passes2,013 tests/
8,746 assertions, no failures; two existing missing-Sodium signing skips only.
PHPCS226 files, Node19/19 (no failures/skips), structure149 runtime/227 support/
205 test files/202 class names, build, strict Composer validation and Composer/npm
moderate audits pass; zero known advisories/vulnerabilities. Local links/diff/
scope review pass; generated tracked assets and uploader/toolkit remain unchanged.
Implementation `599b4b4ad24024c767335effc27cb00877358c4b` is pushed.
[Ordinary exact-head CI40849941384](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40849941384)
passed: actual PHP7.4/8.3 each2,013 tests/8,753 assertions, no failures/skips;
Node19/19/0 skipped each and all configured gates successful. GitHub registered
manual workflow400422878 as active. This is repository QA, not native success.

## Single blocked attempt, cleanup and exact resume gate

One [manual attempt40850202133](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40850202133),
job123367484397, ran at exact implementation head, workflow_dispatch,
2026-10-29 09:30:46–09:31:09 UTC (23s), overall failure.
Fresh unchanged environment preflight and exact pinned core reacquisition passed.
Observed PHP/mysqlnd8.5.12/all11 core pins, initial report3 and zero connections/
queries; CLI/module digests match the prior preflight record. Version-only image
check retained MAC warning. Its own container/directory/core cleanup completed.

The new step stopped at the **connection-free source audit** with
`Owned Linux supervision failed: Source audit refused`. The supervisor currently
discards the inner child status/stdout/stderr when that audit refuses, so the log
does not establish the exact inner failed predicate. Do not infer runtime/core/
driver incompatibility or successful source closure. No audited image terminal
was issued. Source control flow places database initialization, credential
generation, schema/account/fixture creation and the actual native body strictly
after that successful audit. None was reached: **zero completed native groups**,
no genuine OFF/escaping/CAS/failure/privilege or physical release qualification.

Exit cleanup and always cleanup both report containers0/directories0/tmpfs
released/reconciliation_failure0; always preflight cleanup reports core_checkout0.
No database instance/fixture was created by the failed qualification step, so
these are exact no-instance cleanup facts, not a native live/post-exit SQL proof.
No automatic retry, second database attempt or acceptance/runtime change ran.

A separate source-only finding was reproduced with synthetic Node input after
the failed attempt: reviewLog accepts `2026-10-29 09:31:02` but rejects the actual
MariaDB padded-hour form `2026-10-29  9:31:02` as Unclassified startup warning.
The unchanged preflight log demonstrates that form. Its strict two-digit hour
prefix regex is a harness defect requiring a precise formatting regression.
This is **not the observed source-audit failure cause**: diagnostic log review
was not reached. No code was repaired or native process rerun after failure.

Implementation-plan progress: opt-in qualification harness and isolated checks
published, but genuine Linux qualification is blocked before database startup.
Recommended next gated slice: **Harness-only Source-Audit Failure Diagnostics
and Padded-Hour Diagnostic Parsing Repair, Fresh Prerequisites and One New Owned
Linux Qualification Attempt**. Preserve all runtime/source/authentication guards,
budgets, case acceptance, owned scope and disabled production wiring; surface
bounded inner source-audit failure evidence and correct only timestamp framing,
then use fresh prerequisites before at most one explicitly approved owned attempt.
Evidence-only closeout remote/ordinary CI is verified separately at handoff; it
does not erase this failed manual run or promote pending native claims.

## Approved diagnostics/padded-hour repair and one fresh attempt — 2026-10-29

Explicit approval covers the preceding recommended harness-only repair,
regressions, publication and one new owned attempt after fresh prerequisites.
Clean/synced restore-point baseline77b31e05952772a92c4a75d23f100c8011433468.
Same R3 ephemeral Ubuntu/PHP/core/MariaDB target and disposable account/schema/
cleanup scope; no installed site/LocalWP/SSH or production operation. Risk tier,
runtime/source/authentication guards, nine-group acceptance, resources/deadlines,
credential handling, shell cleanup and manual-only workflow are unchanged.

Changes are limited to the supervisor, proof-worker failure metadata and existing
Node test file. No library/pin/helper/case or production-wiring edit:

- Worker records fixed phase plus stage/check/group/assertion-digest fields,
  an allowlisted exception class, and optional PHP diagnostic severity/component/
  line. The message/path are not retained. Warnings still throw/refuse exactly as
  before. Phase labels are observation only, never a new execution/admission path.
- Source-audit refusal now reports bounded child exit/signal/spawn-error facts,
  stream byte counts, truncation and SHA256 of at most16KiB per stream. Only a
  complete strict safe worker-failure record is surfaced; unknown/extra/raw
  fields and raw stderr/stdout/error messages are not emitted. Native failure
  reporting uses the same sanitization, without changing the success terminal.
- Timestamp framing accepts only canonical00–23 or space-padded0–9 hours with
  valid minute/second fields. Known warning messages/hostname checks, unknown
  warning refusal and full/selected log bounds remain unchanged. Bad padding,
  unpadded single hours, out-of-range fields or altered text still refuse.

Two new Node tests failed before implementation for the intended timestamp and
missing failure-evidence behavior, then passed. Current targeted Node5/5 covers
positive padded formats, malformed framing/changed text, metadata preservation,
strict field/type/domain refusal, no raw stream/message leakage, timeout facts,
oversized output and multibyte byte-exact16KiB hashing. Existing PHP gate/receipt/
identity/preflight combined37/159 pass. An optional temporary sanitization-bypass
control was rejected by tool safety review before application; no guard was
weakened and no mutation-control credit is claimed for this repair. Work continued
with non-mutating tests and actual red-to-green regression evidence.

Final local PHP8.5.1 full PHPUnit2013/8746 passes, two existing missing-Sodium
signing skips only; PHPCS226 files, Node21/21/no skips, structure149 runtime/
227 support/205 test files/202 class names, build, strict Composer validation and
Composer/npm moderate audits pass with zero known advisories/vulnerabilities.
Final byte-prefix Node tests pass after that full PHP run; no PHP code changed
after the full run. Bash -n, Node --check, PHP syntax and scoped lint pass.
Runtime, old native cases, source pins/preflight, shell, workflow, dependencies,
package metadata and tracked assets remain baseline-identical. Uploader/toolkit
remain clean/synced and read-only. No native run occurred locally.

Repair `47a307bba0ea0c6298260861d55e0ead85493623` is pushed.
[Ordinary exact-head CI40853406337](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40853406337)
passed: actual PHP7.4/8.3 each2013/8753, no failures/skips; Node21/21/0 skipped
each and all configured gates successful. No runtime/native compatibility credit.

### One newly approved blocked attempt and source-backed diagnosis

One [fresh manual attempt40853631245](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40853631245),
job123379011895, ran at exact repair head, workflow_dispatch,
2026-10-29 09:57:05–09:57:29 UTC (24s), overall failure. Fresh unchanged preflight
and same pinned-core reacquisition passed; PHP/module/binary digests and all11
core pins match the prior record, initial report3 and zero native connections/
queries. MAC warning retained, exact preflight resources removed.

Repaired reporting now shows status1/no signal/no spawn error, empty stdout,
worker stage source-audit, phase profile, checks16/groups0, RuntimeException and
diagnostic:null. Assertion digest
`cc3e43892868a661bc90feaeb3a713fef85a649be8d56d169b5826b89be282ce`
matches **genuine fixed source/endpoint admission**: the combined
Bootstrap_Profile::settings(frame) && Bootstrap_Profile::source(owner,binding)
predicate. No PHP warning was reported. Do not infer which operand short-circuited
from that combined check alone, or that subsequent native gates would pass.

Read-only official pinned source inspection then established a definite refusal
path, without another core/native execution:

- [load.php](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/load.php)
  declares both wp_using_ext_object_cache() and absint(); downloaded UTF-8 bytes
  hash3efd0934bd8a454a8d7076fbbd21504ea097a753125a48e64b8a1dad380d357a,
  exactly matching the committed/preflight pin.
- [functions.php](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/functions.php)
  declares neither; hashcac8a17e293df99f0683a88debd3ba5e5cce274183a4e9fd3c9f18375747f41c,
  also pin-identical.
- The Linux harness correctly binds cache_api_file to load.php but incorrectly
  binds parser_api_file to functions.php. The unchanged source guard requires
  absint's actual declaration filename to equal that binding. Thus this stale
  caller binding necessarily refuses if source() is evaluated; it does not
  justify weakening source identity or accepting arbitrary helper files.

The settings/source distinction and other later conditions remain unproven; this
is a source-backed sufficient refusal path, not a claim that all others pass.
No source-binding repair or further attempt was made after the failed check.
The padded-hour repair has isolated evidence; server-log review was not reached.

Qualification database initialization, credential generation, account/schema/
fixture creation and native body were not reached: **zero native groups**.
Exit and always cleanup each report containers0/directories0/tmpfs released/
reconciliation_failure0; source cleanup reports core_checkout0. These are exact
no-instance cleanup facts, not a physical live/post-exit database proof. Existing
site/application state and production wiring remain untouched.

Implementation-plan progress: bounded diagnostics and padded-hour repair complete;
Linux physical qualification is now blocked at a concrete stale helper binding,
not an unexplained error. Recommended next gated slice: **Linux Harness-Only
Canonical absint Declaration Binding Correction, Source-Backed Regression,
Fresh Prerequisites and One New Owned Qualification Attempt**. Correct the
private parser_api_file to pinned load.php, preserve every actual guard/budget/
acceptance/cleanup rule and disabled wiring, then use a fresh exact image before
at most one newly approved attempt. No generic source allowlist, runtime policy,
old receipt rewrite or production provider migration belongs in that slice.
Evidence-only final-head ordinary CI/remote is checked separately at handoff;
both historical failed manual attempts remain failed and qualification pending.

## Canonical absint declaration binding correction — 2026-10-29

User approved the exact harness-only correction, source-backed regression, fresh
prerequisites and one new owned Linux qualification attempt. Restore baseline:
clean/synced master6adb7aee239fe3f536a4445cc7bd1f62f4bc3e23. Only worker edit is
parser_api_file functions.php to load.php. Both absint and cache API declarations
belong to pinned load.php with the hash recorded above; the actual runtime
ReflectionFunction declaration/source guard is unchanged. No general helper
allowlist, predicate bypass or settings/source assumption is introduced.

The added existing-test-home regression reads the actual two worker bindings,
requires load.php for both and checks the existing 11-file manifest/load.php
SHA256. An equivalent read-only assertion against immutable baseline source
fails as expected for functions.php. This is static caller metadata/pin evidence,
not genuine core execution, authorization, database or physical cleanup proof.
An earlier concurrent PHPUnit invocation completed after the binding edit and
passed, so it is not credited as a red-before-change run. Current related
PHP gate/receipt/identity/preflight tests38/162 pass; source guard/cases and all
native acceptance remain unchanged. No mutation-control credit is claimed.

PHPCS226 files, Node21/21/no skips, structure149 runtime/227 support/205 tests/
202 class names, build, strict Composer validation and Composer/npm moderate
audits pass (zero known advisories/vulnerabilities); Bash -n, Node --check, PHP
syntax and diff whitespace pass. Full local PHP8.5.1 PHPUnit2014/8749 passes
with two existing missing-Sodium signing skips only. Publication evidence is
pending below before any dispatch. Worker/test plus these two owning records are the
only intended changes; runtime/manual cases/pins/shell/controller/workflow/
dependencies/metadata/assets, uploader and toolkit remain unchanged. No local
native execution, LocalWP/live/Novamira operation or release work is in scope.

One new manual attempt is authorized only after ordinary exact-head CI succeeds,
with fresh unchanged preflight and reacquired pinned core. Retain all original
guard, resource, source-identity, diagnostic, ADBC1/2 and cleanup conditions;
no automatic repair/retry if it fails. Previous failed runs remain failed.
