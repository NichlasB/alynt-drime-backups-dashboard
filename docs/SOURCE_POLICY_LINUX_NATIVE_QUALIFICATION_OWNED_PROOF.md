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
Ordinary exact-head CI and the one manual native result are recorded below when
executed. Until then all native assertions above are requirements, not passes.
