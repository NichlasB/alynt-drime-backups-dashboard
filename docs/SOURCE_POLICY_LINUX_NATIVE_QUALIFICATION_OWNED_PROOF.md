# Off-Production Linux Genuine-Core/OFF and Diagnostic Native Qualification

## Current scoped status — 2026-10-29

Pinned lab qualification **passed** atb0717fc54597712a823c3ebaf1ea497c39ed115b,
[run40864997754](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40864997754):
282 recorded checks/9 groups, genuine core-selected OFF/exact escaping/CAS/error
cases, independently zero owned all-server sessions/transactions while held and
after exit, exact fixture/resource cleanup. Scope is the recorded Linux/PHP8.5.12/
core7.1.4/MariaDB10.11.18-ubu2204 image only. No production admission/wiring,
full WordPress authentication/bootstrap, minimum PHP7.4/core6.0 compatibility,
fresh Windows/TCP qualification or whole-plugin release readiness follows.
Prior failed attempts below stay failed; their resume recommendations are
historical. Latest successful closeout and remaining gates are at the end.

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

### Publication and one fresh owned attempt

Repairbc82f729d718abb5dd976f2dfcc8e56e957f0c69 is pushed.
[Ordinary exact-head CI40855948097](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40855948097)
passes: PHP7.4/8.3 each2014/8756/no failures or skips; Node21/21/no skips each,
all configured build/structure/lint/manifest/audit gates successful. Rechecked
official immutable load.php/functions.php text/hash read-only in this task:
both match the existing manifest and only load.php declares absint/cache API.
This source-text check did not load WordPress or open a native connection.

One [manual attempt40856185813](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40856185813),
job123387633595, ran at that exact head, workflow_dispatch, from
2026-10-29 10:16:03 to10:16:34 UTC (31s), overall failure. Fresh unchanged
preflight and same pinned-core reacquisition succeeded with identical recorded
PHP/module/server/client digests and11 core pins. Version-only preflight
containers/core/directory cleanup passed before actual source reacquisition.

The actual genuine-library source audit now passes:29 checks, zero native
connections/queries, image9901894ed7440ea0facfa9bad03da460a1da683496ea4e0f72510bcf5d191392,
run0c8ed92a41a16f8c20907a014996af07. The stale declaration-binding refusal is
resolved in the native source layer, not merely the static regression.

The fresh network-none/read-only-root instance then initialized successfully.
Baseline: exact10.11.18-MariaDB-ubu2204, no TCP1/wsrep0/nativeAIO1/autocommit1/
in_transaction0/other sessions0/transactions0/non-system schemas0; host
io_uring_disabled0. Full bounded bootstrap2137B and server2461B diagnostic
contexts were accepted under unchanged strict policy, including exact known
io_uring-to-libaio and namespace/WSREP warnings. The prior padded-hour change
has isolated coverage, but this native attempt's hour10 does not physically
exercise single-digit hour padding.

Owned schema/table/account creation was reached. The sequential native worker
passed genuine stock core ambient connection, core-selected report-OFF, actual
outside-fixture privilege denial1142, prepare-without-SQL, owned ambient callback
and actual ambient-bound owner bootstrap preserving OFF. It then failed in
stage escape/phase native at46 checks, **zero completed native case groups**:
RuntimeException, diagnostic:null, status1/no signal/no spawn error/empty stdout.
No successful escaping round trip, complete9-group CAS/error qualification or
held-terminal receipt is claimed.

Reported assertion SHA256
f49523a32f85c7e6a655474deda16410124bba3a4836c81b12fcf805d69cbcee
maps to the reused helper's final "no report-mode restoration or toggle" check.
Read-only source inspection found adbb_check overwrites adbb_last_check on every
check, including successful finally/release checks before an earlier exception
reaches the outer catch. Therefore this digest is **last-check attribution, not
proof that reporting changed or that this was the first failing assertion**.
The compound round-trip acknowledgement/row-count/bytes condition and release
checks are not individually distinguished by current evidence. No root-cause
claim, diagnostic/runtime repair or further native attempt was made after failure.

Failure-path root reconciliation is distinct from the uncompleted held-terminal
success handshake: before dropping fixtures it requires all-server other
sessions0/INNODB_TRX0; after exact owned schema/account drops it requires
non-system schemas0/owned account0/other sessions0/transactions0. Cleanup reported
reconciliation_failure0 and normal stop exit0/notOOM under those unchanged
conditions. Final server3082B diagnostic context includes normal shutdown and
passed strict review. Exit/always cleanup each report containers0/directories0/
tmpfs released/reconciliation_failure0; preflight cleanup core_checkout0.
No existing application/site/LocalWP state or production wiring was touched.

Implementation-plan progress: canonical source admission is resolved; physical
qualification remains blocked at escaping/body-or-release failure attribution.
Recommended next gated slice: **Linux Harness-Only First-Failure Retention and
Bounded Escaping Diagnostics, Fresh Prerequisites and One Owned Diagnostic/
Qualification Attempt**. Preserve the first failing assertion/exception across
finally checks; distinguish fixed non-secret prepare/acknowledgement/row/byte-
equality/release facts without raw SQL, values, credentials or guard relaxation.
Use isolated source-backed regressions before any newly approved single attempt;
preserve runtime/cases/pins/budgets/acceptance and disabled wiring. No automatic
retry, release-readiness refresh or production-provider activation is authorized.
Evidence-only closeout ordinary CI/remote is verified separately at handoff;
the failed native run remains failed, never relabeled by ordinary CI success.

## First-failure retention and bounded escaping diagnostics — 2026-10-29

User approved this exact harness-only repair, validation/publication and one
fresh owned diagnostic/qualification attempt. R3 owned-lab scope only; restore
baselinee4f02aa129b3d86de820a490073e6331927af45c was clean/synced. No installed
WordPress/LocalWP/live target, Novamira action, runtime grant, provider activation,
packaging, release, tag or deployment is included. Existing prior failures remain
failed. The unchanged pinned manual lab/least-privilege fixture and exact owned
cleanup scope above apply; no new server, profile or runtime version is selected.

Only the private worker, its supervisor diagnostic parser and their existing
PHP/Node test homes change, plus these two owning records. adbb_check now captures
only a failed assertion before throwing. A pure write-once snapshot retains
failure-time stage/phase/counts/groups, assertion SHA256, fixed exception kind,
sanitized diagnostic and escaping facts. A thrown prepare/execute operation is
captured before finally with assertion hash("none"), not the previous successful
check. Error-handler exceptions likewise capture immediately. Neither successful
nor failed finally checks can replace an earlier captured failure; failure still
throws/exits and no success path is added.

Escaping diagnostics retain only fixed step prepare/execute/rows/bytes/release
and nullable booleans prepared/ack_exact/row_exact/bytes_equal. The exact original
acknowledgement, single-row and byte-equality predicates remain conjunctive and
short-circuited in their original order. One prepare and at most one execute;
no extra SQL, driver observation, reconnect, retry, fixture or returned value is
added. Separate escape_release is null before entry, false until the existing
close plus helper release checks complete, then true. It does not claim the
parent's independent all-server physical reconciliation or final fixture cleanup.

The supervisor accepts only the exact new private failure schema/field order,
fixed step enum, bool/null facts and existing bounded counts/types/diagnostics;
extra fields, paths/SQL/values and unknown/missing/malformed types are refused.
Existing1KiB diagnostic budget,16KiB stream-prefix hashes, child deadlines,
strict log review and exact held-terminal9-group success policy remain unchanged.
No raw exception message/class outside the fixed enum, trace, path, prepared SQL,
needle, result bytes or credential is retained. Diagnostics never grant admission.

Three PHP source-backed tests failed before implementation for absent first-
failure capture/inner-catch wiring. Updated and new Node tests failed on the
unsupported exact diagnostic schema. After implementation, PHP19/200 and
Node6/6 pass with no skips. PHP tests extract only the actual pure diagnostic
function declarations and the finite escaping try/catch/finally source block
into private PHP -n processes with isolated doubles; they do not load or run the
guarded worker, core, native handles, SQL or WordPress authentication. Positive
acceptance plus prepare/execute/ack/row/bytes/release refusal cases verify single
dispatch, short-circuit facts, release, first-assertion preservation even when
finally also fails, exception-type privacy and no failure snapshot on success.
Source wiring checks require capture-before-finally and captured output, never
the old last-check label. Node tests enforce exact schema, bool/null facts,
missing/extra/reordered/secret-bearing field refusal and sub1KiB bounded output;
such a failure record cannot satisfy the unchanged held-terminal validator.

Final related PHP49/296 and full local PHP8.5.1 PHPUnit2025/8883 pass; only the
two existing missing-Sodium signing skips occur in the full suite. PHPCS226
files, Node22/22/no skips, structure149 runtime/227 support/205 test files/202
class names, build, strict Composer validation and Composer/npm moderate audits
pass (zero known advisories/vulnerabilities). Bash -n, Node --check, PHP syntax
and diff whitespace pass. Diff review confirms runtime, old native cases,
source pins/preflight, shell, workflow, terminal contract, dependencies, metadata
and tracked assets are baseline-identical; uploader/toolkit clean/synced and
read-only. No local native execution occurred. Exact-head publication and the
one newly approved native outcome follow below. Dispatch requires successful
ordinary exact-head CI and fresh unchanged workflow prerequisites; no automatic
repair/retry is authorized.

### Publication verification blocker — no native dispatch

Implementation5641e0e2dd66b891a7ef8be4aeff498fb3a8ece4 is pushed.
[Exact-head ordinary CI40858584834](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40858584834)
failed: PHP7.4 job123395730096 completed2025 tests/8821 assertions with10
failures, all new diagnostic child cases exiting255 instead of0. The source-only
wiring regression passes; the child-case failures are not native proof failures.
PHP8.3 job123395730201 passes2025/8890, Node22/22/no skips and all configured
gates. Local PHP8.5.1 success above does not establish PHP7.4 compatibility.

Read-only diagnosis: the new pure source-backed child launcher uses PHP -n and
JSON output; PHP7.4's JSON module availability under -n is the leading portability
suspect. Current status assertion runs before the stderr assertion, so CI lacks
the actual child's diagnostic text; the exact fatal cause remains unobserved.
Do not claim that hypothesis as proven, skip the cases or accept exit255.

The CI-failure stop condition is reached. No compatibility implementation or
verification-bypass fix follows without approval. No manual owned qualification
was dispatched at this head (workflow history query returned none); the approved
single native attempt is unused, and no fresh lab/SQL/fixture/credential cleanup
was necessary in this task. Existing application state and wiring are untouched.

Implementation-plan progress: first-failure/escaping diagnostic implementation
is published, but publication verification is blocked at isolated PHP7.4 child
compatibility, before native qualification. Recommended next gated slice:
**PHP7.4 Isolated Diagnostic Child Portability and Failure Reporting Repair,
Exact-Head CI Recovery, Then Resume the Previously Approved Single Owned
Attempt**. Establish the actual child failure without raw secret-bearing output,
use a PHP7.4-compatible isolated encoding/launcher contract, preserve every
assertion, guard, native case, budget and disabled wiring, then require green
exact-head CI before the still-unused approved attempt. No new native retry or
scope expansion is authorized by this closeout. Evidence-only final-head checks
are reported separately at handoff; this failed verification is not relabeled.

## PHP7.4 isolated test-child compatibility and failure reporting — 2026-10-29

User approved this repository test-only repair, validation/commit/push/exact-head
CI and resumption of the still-unused single native attempt after green CI.
Restore baseline3cb91ff687819c4df88f3b919bfdeb087fc3e1c5 was clean/synced. Only
the existing qualification PHP test home plus these owning records change.
Native worker, supervisor, pure terminal contract, runtime, old cases, core pins,
preflight, shell, manual workflow, compatibility metadata and all guard/resource/
acceptance/cleanup rules remain baseline-identical. No installed WordPress,
LocalWP/live site, production wiring, package/release/tag/deploy or toolkit change.

The isolated PHP -n fixture launcher now disables json_encode/json_decode
explicitly, so a dependency on optional JSON cannot pass silently on a newer
runtime. Existing children return core serialized primitive arrays, decoded with
allowed_classes:false and an8KiB output assertion; existing observable first-
failure/escaping assertions remain. No runtime wire format or serialized storage
contract changes. These are owned synthetic test results, not imported data.

A private test-child warning handler converts diagnostics to a fixed exception;
the outer catch emits only ADBDIAG1, allowlisted exception kind, source line and
JSON availability, then exits1. Unknown exception names become other. The parent
asserts status0/empty stderr using only fixed parsed facts plus channel byte
counts and bounded-prefix SHA256 (stdout8KiB/stderr1KiB), never raw child streams,
messages, paths, SQL or values. Unexpected/malformed diagnostic text contributes
only its hash/length, not an untrusted label. No assertion is skipped or exit255
accepted. PHP-version-specific missing/disabled-function errors may be Error or
the fixed warning-handler RuntimeException; both must fail with JSON unavailable.

Before encoding repair, controlled local JSON-disabled runs produced the intended
failures: JSON fixture output raised Error/json0 through the bounded reporter,
and serialized output could not be decoded by the old JSON parent. After repair,
related PHP51/346 passes, including positive scalar/NUL-byte round trip, expected
missing-JSON refusal, safe thrown fixture reporting and all prior first-failure/
short-circuit/cleanup cases. This is a controlled dependency reproduction, not
retroactive observation of the hidden historical PHP7.4 fatal. The original
failed runs remain failed; actual PHP7.4 compatibility still requires new CI.

Full local PHP8.5.1 PHPUnit2027/8933 passes with only the two existing missing-
Sodium signing skips. PHPCS226 files, Node22/22/no skips, structure149 runtime/
227 support/205 test files/202 class names, build, strict Composer validation
and Composer/npm moderate audits pass (zero known advisories/vulnerabilities).
PHP syntax, Bash -n, Node --check and diff whitespace pass. Final scope review:
only one test file plus these two owning records changed; runtime, complete
native harness/controller/contract/cases/pins/preflight/shell/workflow, dependencies,
metadata and tracked assets unchanged. Uploader/toolkit remain clean/synced and
read-only. No local native execution occurred. Publication evidence follows
below before resuming the one approved native attempt. That attempt must
use fresh unchanged preflight and exact source; failure requires closeout, not
an automatic repair, second attempt, weakened predicate or production activation.

### Exact-head CI recovery and the resumed single native attempt

Repairf646509327bdafbc46f65d7b7f34cc4aab0974a7 is pushed.
[Ordinary exact-head CI40859970810](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40859970810)
passes: actual PHP7.4/8.3 each2027 tests/8940 assertions, no failures or skips;
Node22/22/no skips each and all configured manifest/audit/build/structure/lint
gates successful. This establishes actual PHP7.4 fixture compatibility; it does
not retroactively reveal the old hidden fatal or relabel prior failed CI runs.

After verifying exact successful CI, clean working tree and remote master at the
repair head, resumed the still-unused single approved native attempt exactly once:
[manual run40860191306](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40860191306),
job123401170338, workflow_dispatch, exactf646509,2026-10-29 10:46:29–10:47:01 UTC
(32s), overall failure. Fresh unchanged preflight and same pinned-core reacquisition
passed; all recorded PHP/module/server/client digests,11 core pins and exact
MariaDB image/version match the previous record. Version-only preflight completed
with zero database/SQL/WordPress execution and removed its owned resources.

New actual source audit passes29 checks/zero native connections/queries,
image31cffaa1b33e81029e774d85fc4fd02a1a52f6d8caa5e770c15e8fcc4ed88f8b,
run58c820f37b1b955f593290bd6749ee77. The fresh labeled private instance baseline
is exact10.11.18-MariaDB-ubu2204/noTCP1/wsrep0/nativeAIO1/autocommit1/in_transaction0/
other sessions0/transactions0/non-system schemas0, host io_uring_disabled0. Owned
schema/table/account creation and actual ambient/core-selected OFF/privilege
denial1142/owned initializer prerequisites were reached and passed sequentially.

Improved failure report now retains the original failure-time count41, not the
later46 reached through finally. Stage escape/phase native/groups0,
RuntimeException/diagnostic:null/status1/no signal or spawn error/empty stdout;
325-byte stderr record, within unchanged1KiB bound. Assertion SHA256
f8243cf6c8ea6b6a8caa52f48a77eff25d802b6a9d9bc38b6e09d352a84d1ce1
matches the exact genuine quote/backslash/NUL/percent/utf8mb4 round-trip assertion.
Facts: step execute, preparedtrue, ack_exactfalse, row_exactnull, bytes_equalnull,
escape_releasetrue. Thus checked preparation returned and execution returned an
acknowledgement unequal to affected1/errno0. Row count/byte equality were not
evaluated. The original assertion survives successful release checks; no report-
mode drift, returned-byte corruption, exact errno or caught exception within the
runtime execute mapper can be inferred from the boolean acknowledgement fact.
Zero original native groups complete; no successful round trip/9-group physical
qualification or held-terminal success handshake is claimed.

Read-only source diagnosis after failure: official pinned class-wpdb.php UTF-8
SHA256e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd matches the
committed manifest. Query/strip_invalid_text_from_query/strip_invalid_text show
non-ASCII validation and a capability-dependent conversion-query fallback; the
runtime execute mapper can return failed acknowledgements or collapse a caught
Throwable to affectedfalse/errnonull. Current facts do not distinguish those
paths. No core execution, SQL, runtime/source fix or additional attempt followed
this read-only inspection; do not disable validation or invent a missing helper
as the cause. Future diagnostic work must account for genuine query dependencies
and possible auxiliary validation queries, not assume one method call equals
one physical SQL command.

All strict full bounded diagnostic reviews passed: bootstrap2137B SHA256
5cebca325003a2e36c7a37077ad6c2fa0cc699860a19f64068d7ae324f6e9277;
initial server2450B SHA256629d0a01f553ee31c06a95302d380454104f88da7706af56ddc60cadc26b6461;
final server3071B SHA25691c5708edf9b0873a6dfd8f81176403363c53785dd86555d9b197b3e12802751.
Known namespace/WSREP/io_uring-to-libaio warnings remain visible and unchanged,
normal shutdown present. Escape close/ambient-handle/callback/report-mode release
checks all completed. Independent failure-path root reconciliation then requires
all-server other sessions0/INNODB_TRX0 before exact schema/account drops, followed
by non-system schemas0/owned account0/sessions0/transactions0. Cleanup reports
reconciliation_failure0 and normal stop exit0/notOOM under those existing rules.
Exit and always cleanup each report containers0/directories0/tmpfs released/
reconciliation_failure0; preflight cleanup core_checkout0. No existing application,
LocalWP/live site or production wiring was touched. This failure-path cleanup is
not the uncompleted held-terminal live/post-exit success proof.

Implementation-plan progress: test-child compatibility and first-failure
attribution are resolved; native qualification is blocked specifically at the
execution acknowledgement. Recommended next gated slice: **Linux Harness-Only
Bounded Native Acknowledgement and Query-Path Diagnostics, Fresh Prerequisites
and One New Owned Attempt**. Classify only existing acknowledgement fields into
fixed affected/errno/type categories and pure bounded source/query-path facts,
with isolated positive/negative regressions, no raw SQL/values/messages, extra
queries, runtime guard changes or automatic replay. Preserve source identity,
existing cases/acceptance/budgets/cleanup and disabled wiring. The approved single
attempt is now consumed; another native attempt requires new explicit approval.
Evidence-only closeout ordinary CI/remote is verified separately at handoff;
ordinary CI success must not relabel this failed native qualification.

## Bounded native acknowledgement/query-path diagnostics — 2026-10-29

User approved harness-only bounded acknowledgement/query-path diagnostics and
one fresh owned attempt through validation/commit/push/exact-head CI. R3 private
lab scope only; clean/synced restore baselinedd52d704d64e292a7282e6f808bc58a8cb39b82f.
Only worker/supervisor, existing PHP/Node test homes and these two owner records
change. Runtime guards, old native cases, pins/preflight, shell/workflow, receipt
success policy, resources/deadlines and production wiring remain unchanged.
No installed WordPress/LocalWP/live target or toolkit/release/deployment work.

The same single execute return is retained locally for its original exact
affected1/errno0 comparison and classified separately: exact array field shape,
affected one/zero/false/null/other and errno from a fixed finite code/null/other
domain. Strict type distinctions remain; malformed/reordered/unknown payloads
produce fixed other categories, never arbitrary strings or object values.
Preparation, one dispatch, row count and exact byte comparison stay unchanged.

Query facts are only prepared-ASCII, function availability for mb_strlen,
is_wp_error and the combined l10n pair, public ready and the public core
num_queries delta classified as unobserved/zero/one/two/many/unknown. Availability
checks invoke no helper; counter/property reads issue no SQL or driver call.
The counter is core bookkeeping, not independent physical wire accounting or
proof of which nested function ran. No query/state/session probing, callback,
replay, reconnect, extra statement or source/runtime fix is introduced. The
existing first-failure snapshot retains these facts before finally; no SQL,
prepared bytes, returned values, raw errno outside the finite domain, messages,
paths, credentials or native handles are emitted.

Supervisor parsing requires exact nested key order and strict bool/null/enum
domains. Unknown/missing/extra/reordered/secret-bearing fields refuse; unchanged
1KiB child diagnostic/16KiB stream-prefix limits and strict log review apply.
The diagnostic record still cannot satisfy the unchanged9-group held-terminal
validator. It is not used for a success/admission decision.

Before implementation the new PHP actual-source classifier test failed for the
missing classifier; new/updated Node positive schemas failed under the old
parser. Current relatedPHP56/446 and focusedNode7/7 pass. Existing PHP -n JSON-
disabled source-backed tests exercise actual pure functions and the finite
escaping block with isolated doubles, not the guarded worker/core/native body.
Positive acknowledgement and independent prepare/execute/ack/row/bytes/release
refusals retain one prepare/at-most-one execute/cleanup. Zero/one/two/many/unknown
counter categories do not alter positive acceptance. Strict scalar types,
malformed shape and private payloads are classified without leakage. Node tests
cover all finite categories, malformed types, missing/extra/reordered fields,
counter domain and sub1KiB complete records; no native qualification credit.

Read-only official source audit in this task verified all seven loaded core
library hashes against the unchanged11-file manifest: load.php declares
is_wp_error and wp_load_translations_early; those seven sources do not declare
mb_strlen or __. This does not itself establish runtime capabilities, a missing
dependency as the observed cause or permission to add a core include/extension.
The separately observed pinned query path may use conversion-query fallback;
diagnostics will distinguish facts without disabling genuine core validation.

Full local PHP8.5.1 suite2032/9033 passes, with only the two existing missing-
Sodium signing skips. PHPCS226 files, Node23/23/no skips, structure149 runtime/
227 support/205 test files/202 class names, build, strict Composer validation
and Composer/npm moderate audits pass, zero known advisories/vulnerabilities.
Bash -n, Node --check, PHP syntax and diff whitespace pass. Final diff/ownership
review confirms only the four harness/test files plus these two records change;
runtime/manual cases/pins/preflight/shell/workflow/terminal contract/dependencies/
metadata/assets unchanged; uploader/toolkit clean/synced and read-only. No local
native execution occurred. Publication and the approved single native outcome
follow below. Dispatch requires green exact-head ordinary CI, clean/
synced master and fresh unchanged prerequisites; no automatic native repair/retry.

### Publication and the single bounded diagnostic attempt

Implementation432cd6f4dee3f90f9a3d71dd0a96013740d45986 is pushed.
[Ordinary exact-head CI40863045789](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40863045789)
passes: PHP7.4/8.3 each2032 tests/9040 assertions/no failures or skips; Node23/23/
no skips each and every configured audit/manifest/build/structure/lint gate.
After exact-head/clean/remote checks, dispatched exactly one approved attempt:
[manual40863271720](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40863271720),
job123411591054, exact432cd6f, workflow_dispatch,2026-10-29 11:10:11–11:10:45 UTC
(34s), overall failure. No repair/retry followed this single native failure.

Fresh unchanged preflight/core reacquisition pass: same exact PHP8.5.12/mysqlnd/
mysqli, binary/module/server/client digests,11 core pins and MariaDB image/version
as recorded above; version-only preflight reports zero database/SQL/WordPress
execution, resources removed. Actual source audit29/zero connections/queries,
image4310c880ffffb449158e918bb3770fd37a13cae3bdbbf640a7e3b6a16514a787,
run648b84cf0d280386571acc72f6c3af4d. Fresh lab baseline exact10.11.18-MariaDB-
ubu2204/noTCP1/wsrep0/nativeAIO1/autocommit1/in_transaction0/other sessions0/
transactions0/non-system schemas0, host io_uring_disabled0. Owned fixture creation
and ambient/core-selected OFF/privilege denial/actual owner bootstrap pass.

Original first failure remains escape/native/check41/groups0/RuntimeException/
diagnostic:null, round-trip assertion SHA256
f8243cf6c8ea6b6a8caa52f48a77eff25d802b6a9d9bc38b6e09d352a84d1ce1.
484-byte bounded stderr, status1/no signal/no spawn error/empty stdout. Prepared
true, ack_exactfalse, row/byte facts null; acknowledgement shape true, affected
false, errno1064. Query facts: ASCIIfalse, mb_strlenfalse, is_wp_errortrue,
l10nfalse, core num_queries delta two, readytrue. Escape release checks complete.
This is the ordinary numeric syntax-error acknowledgement path, not the runtime
mapper's affectedfalse/errnonull caught-Throwable path. It does not prove which
two individual statements executed or authorize suppressing genuine non-ASCII
validation. No completed native groups, successful escaping byte comparison or
held-terminal9-group qualification is claimed.

### Read-only source-backed SQL alias diagnosis

The fixed private worker template is SELECT %s AS escaped; the alias is bare.
[MariaDB's official reserved-word reference](https://mariadb.com/docs/server/reference/sql-structure/sql-language-structure/reserved-words)
lists ESCAPED as reserved and requires quoting reserved identifiers. The exact
server revision reported by the pinned lab is197f92bee02d8e836f529f37625be69b83e7acbd.
Read-only primary-source inspection at that immutable revision confirms:

- [sql/lex.h](https://github.com/MariaDB/server/blob/197f92bee02d8e836f529f37625be69b83e7acbd/sql/lex.h#L226)
  maps ESCAPED to its keyword token; UTF-8 SHA256
  2ed431597be2ee2353e2db7b626cbda44c7a56a8c5cd60edc047f36ca789f9c0.
- [sql/sql_yacc.yy](https://github.com/MariaDB/server/blob/197f92bee02d8e836f529f37625be69b83e7acbd/sql/sql_yacc.yy#L9159)
  requires an identifier/string for the alias and places ESCAPED in the reserved
  rule, not a general identifier; UTF-8 SHA256
  465b1719d4a663afc6c1699e44edbf417a6ab3430376977f8546cd455b002b91.

This is a concrete source-backed sufficient syntax-refusal path consistent with
the observed1064. It is not a native alias-only reproduction or proof no other
later condition can fail. No raw SQL/error payload was retrieved from the child,
no parser/core execution, alias fix or further native attempt was performed.

All unchanged strict full diagnostic reviews pass: bootstrap2137B SHA256
e44da46361750a165e2c5e0f5db5ddf701c4b1483d137070a75d93b6d2e84894;
initial server2514B SHA25637a85ad970a6f3a493a44adb44affb2bf964eeef563e743c63ddef10fbc872e0;
final server3135B SHA2569fa9d3fdb65b8e37f370d8e7e1460c92f9b98beca52a379d98e773e25c727ffd,
known warnings visible and normal shutdown present. Independent failure-path
root reconciliation requires other all-server sessions0/INNODB_TRX0 before exact
schema/account drops, then non-system schemas0/owned account0/sessions0/trx0.
Cleanup reports reconciliation_failure0 and normal stop exit0/notOOM under those
unchanged conditions. Exit/always containers0/directories0/tmpfs released/
reconciliation_failure0; source preflight cleanup core_checkout0. This is not
the uncompleted held-terminal live/post-exit success proof. Existing application
state, LocalWP/live sites and production wiring remain untouched.

Implementation-plan progress: bounded diagnostics resolved the opaque execution
failure into a concrete private SQL-alias defect; full physical qualification
remains blocked. Recommended next gated slice: **Harness-Only Quoted ESCAPED
Alias Correction, Source-Backed Regression, Fresh Prerequisites and One New Owned
Qualification Attempt**. Quote the fixed alias in the template, retaining its
existing result property/exact bytes, the complete test value, core validation,
native cases, source guards, budgets, acceptance and disabled wiring. No change
to runtime queries or generic identifier policy belongs in that slice. The
current attempt is consumed; another requires explicit approval. Evidence-only
final-head ordinary CI/remote is verified separately at handoff; prior native
failures remain failed and qualification is not established by green ordinary CI.

## Quoted fixed ESCAPED alias correction — 2026-10-29

User approved quoting only the fixed alias, its regression, full validation/
publication and one fresh qualification attempt. R3 private owned lab scope only;
restore baseline7de84aedcd355d258fff89e01b7625e5db877ff0 was clean/synced.
The worker's single SQL-template change wraps the fixed escaped identifier in
backticks. It does not rename the result property, change a runtime query or add
a generic identifier policy. Exact affected1/errno0, one row, complete probe and
byte equality remain mandatory; no ASCII-only replacement, removed NUL/percent/
emoji, changed charset, validation bypass or relaxed SQL/error acceptance.

The existing PHP test home gains a static source regression requiring the quoted
template, fingerprinting the unchanged probe expression (SHA256
dd87c5cfcb62310d9636bc9951f5abcc8dcdb60d14eb8b53518a21577bdd67b5) and retaining
the exact result-property byte comparison. It failed on bare AS escaped before
the one-line repair and then relatedPHP57/451 passes. Existing actual-source
isolated escaping cases still exercise positive acceptance and independent
prepare/execute/ack/row/byte/release failures. Static quoting/probe evidence and
synthetic doubles are not a genuine SQL parser or native escaping proof.

Worker/test plus these two owning records are the only intended changes. Runtime,
all prior native cases, source pins/preflight, supervisor/terminal contract,
shell/workflow, dependencies, metadata/assets, guards, resource limits/deadlines,
diagnostic/log policies and disabled production wiring remain unchanged. No
installed WordPress/LocalWP/live-site/Novamira operation, package/release/tag/
deploy, uploader or toolkit change is included. Ordinary exact-head CI must pass
before the one approved manual dispatch with fresh unchanged prerequisites.
Native failure requires truthful evidence/cleanup closeout, not another retry.

Final local PHP8.5.1 PHPUnit2033/9038 passes, with only the two existing missing-
Sodium signing skips. Other QA passes: PHPCS226 files, Node23/23/no skips,
structure149 runtime/227 support/205 test files/202 class names, build, strict
Composer validation and Composer/npm moderate audits (zero known advisories/
vulnerabilities), PHP syntax/Bash -n/Node --check/diff whitespace. Diff review
confirms exactly one worker-line edit and one new existing-home test, plus the
two owning records; all protected runtime/native inputs remain baseline-identical.
Companion uploader/toolkit clean/synced and read-only. No local native execution.
Publication/native outcome is recorded below once complete.

### Successful quoted-alias qualification and publication

Repairb0717fc54597712a823c3ebaf1ea497c39ed115b is pushed.
[Ordinary exact-head CI40864770758](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40864770758)
passes: PHP7.4/8.3 each2033 tests/9045 assertions, no failures/skips; Node23/23/
no skips each and all configured manifest/audit/build/structure/lint gates.
After exact successful CI and clean/synced remote/master verification, dispatched
exactly one approved fresh attempt:
[manual40864997754](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40864997754),
job123417408896, workflow_dispatch, exactb0717fc,2026-10-29 11:23:22–11:23:54 UTC
(32s), **success**, including always cleanup. No retry or further native run.

Fresh unchanged preflight/core reacquisition passed with the previously recorded
exact PHP8.5.12/mysqlnd/mysqli/server/client digests,11 core pins and immutable
MariaDB image/version. Version-only preflight reports zero database/SQL/WordPress
execution and removes its owned resources before the actual proof. Actual
source audit29/zero connections/queries; new frozen image
379e8519d0913473d778981c51bba14af60f816fc19c4afa3f6713dc19557c98,
run06e2c1d87f76d6a5f3d50814f9200cde. Fresh lab baseline exact10.11.18-MariaDB-
ubu2204/noTCP1/wsrep0/nativeAIO1/autocommit1/in_transaction0/other sessions0/
transactions0/non-system schemas0, host io_uring_disabled0. No existing database.

The held terminal records **282 checks/9 groups**, pid3609, mode0/closedtrue,
the exact frozen image/run, independent_livezero/independent_post_exitzero and
qualificationlab-only. Genuine core created the separate ambient handle and
selected OFF; the dedicated owner preserved ambient identity/handle/callbacks/
reporting. The unchanged quote/backslash/NUL/percent/4-byte UTF8 probe prepared,
executed with exact affected1/errno0, returned exactly one row and identical bytes.
All nine original groups complete: positive view/save/cache/once-only observer,
no-op/stale ordering, conditional removal, update race, duplicate winner handling,
transform/reentry refusal, bounded lock failure, post-change uncertainty and
real false/errno1064/1062/1205 with no retry. Exact bytes/autoload/non-target data
and all prior acceptance predicates remain; the only worker fix was alias quoting.

The controller verified the correlated closed terminal while the child remained
alive, independently required other all-server sessions0/INNODB_TRX0, then sent
the exact run/image acknowledgement. After acknowledged child exit it repeated
the independent all-server zero check. This run therefore establishes the held-
live/post-exit success handshake, unlike earlier failure-path-only cleanup.
Final fixture cleanup independently rechecks zero other sessions/transactions,
drops only adb_owned/adbp, requires zero non-system schemas/owned account/
sessions/transactions, then normal server exit0/notOOM. Exit and always cleanup
report containers0/directories0/tmpfs released/reconciliation_failure0; source
preflight cleanup core_checkout0. Existing application/site state is untouched.

Unchanged strict full diagnostic review passed, known namespace/WSREP/io_uring-
to-libaio warnings visible, normal shutdown present. Bootstrap2137B SHA256
89163982bb5b5850bd2a054d81770dc1d3da374efc79a8321ad6ce80ff0913ca;
initial server2461B SHA256e711eef3d3c1590db84816f1912b0e75cfb8ff43ab4bf6d6030b3e3aacb82e51;
final server3082B SHA25684503df9085e829747bee1b82b1133abd064674f4bba78b37afbc5dd7f1e48a7.
No ERROR/unknown warning was ignored or new warning allowance added.

This resolves the concrete alias blocker and completes this pinned Linux native
qualification milestone. It is not a provider grant, live-site compatibility,
application race-fix deployment, whole-plugin review, full authentication proof,
minimum PHP7.4/core6.0 native proof or refreshed current-source Windows/TCP proof.
Old proof images/checklist counts remain historical. Public factories and form/
legacy/production wiring remain closed and unchanged; no package/release/deploy.

Implementation-plan progress: the Linux genuine-core/OFF/escaping/9-group native
and independent cleanup gate is now complete for this exact image. Existing
readiness designs still require supported-profile evidence before trusted lazy
provider and cohesive guarded form/legacy integration. Recommended next gated
slice: **Repository-only Minimum-Core/PHP7.4 and Current-Source Windows/TCP
Qualification Closure Design**. Resolve the finite remaining profile/source-
image/fixture authority and required genuine proofs without conflating this lab
with minimum support or existing LocalWP state. Keep it design-only: no native
experiments, storage/site operations, acceptance changes or production wiring.
Actual qualification targets/fixtures and later provider/form/release actions
require separate explicit authority. Evidence-only closeout exact-head ordinary
CI/remote is verified at handoff; it does not rerun or widen this native success.
