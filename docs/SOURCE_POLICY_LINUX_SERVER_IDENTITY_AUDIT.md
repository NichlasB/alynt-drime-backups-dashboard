# Off-Production Linux Owned Unix-Socket Server-Identity and Cleanup Audit

Subsequent [exact-build admission/diagnostic design](SOURCE_POLICY_LINUX_EXACT_BUILD_QUALIFICATION_DESIGN.md)
selects the future single full-suffix lab candidate and genuine-OFF/resource
qualification requirements. It preserves this exact audit image and observations;
no native rerun, compatibility change or qualification grant is implied.

## Approval, target and baseline — 2026-10-28

Approved next slice from the [environment preflight](SOURCE_POLICY_LINUX_ENVIRONMENT_PREFLIGHT.md).
Clean/synced dashboard baseline `87d03dbc84e1825197609c2edd65da50a6de0088`.
Explicit R3 authority: one exact-head manual GitHub-hosted Ubuntu24.04 x86_64
job, NichlasB/alynt-drime-backups-dashboard, fresh prerequisites, one empty
disposable MariaDB instance, bounded identity reads and exact owned cleanup.
Autonomous R2 is not raised. No installed site/LocalWP/live/production access,
WordPress execution, application schemas/rows/users/options/cron, adapter/native
owner execution, compatibility-rule change, wiring, release/package/deploy or
workstation installation. Uploader/toolkit remain read-only.

The resolved target is the explicitly owned ephemeral lab, not Plugin Tester or
control-sitesmanage. The workflow rejects Docker endpoint/context overrides and
requires the runner-local unix:///var/run/docker.sock before resource operations.
No Novamira or site-specific operation is involved. No credentials/configuration
are copied from any registered site.

## Bounded implementation and public API basis

New manual-only identity workflow; ordinary CI and the earlier no-database
preflight sources remain byte-unchanged. Exact approved master SHA before setup,
contents:read, immutable checkout/setup/core/image pins from the owning preflight,
no push/PR/schedule/service trigger, ten-minute job bound and serialized runs.
Fresh unchanged preflight must pass and clean its own resources before the
database instance can be created. Its earlier historical receipt is not reused
as a fresh prerequisite.

[mariadb-install-db](https://mariadb.com/docs/server/clients-and-utilities/deployment-tools/mariadb-install-db)
creates system tables/accounts through vendor bootstrap SQL. That is a real
owned database write under this approval, not a read-only job. The initializer
uses --no-defaults, explicit empty data location, --skip-test-db and normal root
authentication. The resulting empty-password root is confined to this private
socket/network-disabled disposable database; it is not a production credential
or least-privilege application authentication proof. No additional users,
privilege changes to existing databases, application tables or WordPress schema.

One non-root UID/GID-matched container: network:none plus --skip-networking,
read-only root, no healthcheck, all capabilities dropped/no-new-privileges,
64 PIDs/512MiB/one CPU. Only the new mode700 owned socket/log directory is bound
to the host. Data256MiB and temporary16MiB storage are UID-owned private tmpfs,
not a persisted database volume. [Docker tmpfs semantics](https://docs.docker.com/engine/storage/tmpfs/)
include memory accounting and possible swap: removal is not a physical secure
wipe attestation. Platform teardown owns remaining ephemeral runtime/image layers.

Initializer <=40s, startup wait <=50s, one PHP client <=15s (connect2s/read3s),
two independent CLI snapshots <=10s each, server stop5s with8s outer bound.
Server statement2s/lock1s; connections configured10, MariaDB's documented minimum,
not an arbitrary lower value silently clamped by the server. Resource choices
belong to this audit, not changed20s/16-credit adapter bootstrap acceptance.
Internal bootstrap SQL is vendor initialization, not counted as an audited SELECT.

Exactly three literal audit SELECTs: CLI baseline, PHP identity row, CLI post-
close. Each independent observer counts all other PROCESSLIST IDs, all INNODB_TRX
and non-system schemas, including NULL/foreign DB sessions (no DB-name filter).
Expected0/0/0; observers exclude only themselves. The PHP reader uses
[explicit mysqli connection arguments](https://www.php.net/manual/en/mysqli.real-connect.php),
localhost/null port/exact owned socket/flags0, no imported handle, default socket,
TCP fallback, reconnect, arbitrary SQL or reporting-mode toggle. It records one
connect/one query, frees its result and attempts close independently even after
failure. Stage/errno only on failure, never raw SQL/credentials/driver messages.

Pure identity consistency checks do not authorize a supported version: a valid
unqualified suffix remains observable. mysqli's reported version must agree
with SELECT VERSION(), Unix-socket transport, private socket/principal, no TCP,
server read_only, autocommit/no transaction, zero other sessions/app schemas/trx.
read_only does not constrain privileged root; fixed source-scoped reads and the
empty/network-isolated target define the effect boundary here. PHP8.5.12 source
mysqlnd_wireprotocol.c was inspected: the legacy5.5.5- greeting prefix is removed
by mysqlnd. The report is the normalized mysqli guard input, not raw packet bytes.

After independent post-close evidence, normal stop requires exited0/no OOM.
Label+captured full container ID are checked before exact stop/remove; no global
prune or foreign process operation. Cleanup is exit-trapped and always-run.
Private files are removed by exact leaf names, with owner/symlink/path checks;
unknown contents refuse recursive deletion. Bounded warning/error/ready/shutdown
lines and log sizes/digests are retained even on body failure; diagnostic capture
failure does not prevent exact owned removal. Warnings are not suppressed,
whitelisted, used to change server IO settings or counted as negative controls.

## Repository validation and scoped review

- New identity contracts/gates24 tests/64 assertions; combined Linux preflight/
  identity30/96, no failures/skips. Positive observation includes the unqualified
  ubu2204 suffix; malformed/incomplete/other-session/application-schema/transport
  DATA fail. These are isolated DATA/gate tests, not database evidence.
- Four temporary guard removals (wire agreement, socket transport, other-session
  absence, application-schema absence) were assertion-killed; exact SHA256
  `6c4c0fe1c8531b41782d314ae52fffbcb8d7a15f421e8131bd36fb6baf336c1b`
  restored, then all targeted checks pass. No startup failure counts.
- Full local PHP8.5.1 PHPUnit --verbose --do-not-cache-result:1,986 tests/8,538
  assertions, no failures, two existing Sodium signing skips only. PHPCS224
  files, admin16/16, structure149 runtime/227 support/204 test files/201 classes,
  build, strict Composer validation, Composer/npm moderate audits pass; zero
  known advisories/vulnerabilities. Git Bash -n and both fixed JS block syntax
  checks pass. No native execution happens on Windows.

Source-scoped review covers target/effect/provenance, no endpoint input, native
failure mapping, bounded metadata and independent/partial cleanup. No genuine
WordPress authentication, physical transaction/uncertainty qualification, full
Database Review, UI workflow or whole-plugin release readiness is claimed.
Runtime/includes, manual proofs, old preflight/ordinary CI, assets/dependencies,
version/schema, all profile guards and production wiring remain byte-unchanged.

Implementation `d48b8f1243a466f21c01135a3a18dbb06bebf600` is pushed.
[Ordinary exact-head CI40783364418](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40783364418)
succeeded: actual PHP7.4/8.3 each1,986 tests/8,545 assertions, no failures/skips;
admin16/16/0 skipped each, all configured gates successful. GitHub registered
manual workflow399989831 as active. Static packaging inspection confirms scripts,
tests/docs/.github are excluded by the existing release workflow; no ZIP ran.

One [owned audit40783576271](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40783576271)
ran at exact implementation head, workflow_dispatch/job123151060272, all steps
successful in25s (2026-10-28 22:55:17–22:55:42 UTC). Fresh unchanged preflight
observed PHP/mysqlnd8.5.12,11 core7.1.4 pins, zero connections/queries before the
audit and the same CLI/module digests recorded in its owning record; exact
preflight resources were removed before the owned database instance.

Actual identity audit evidence:

- mysqli API-normalized greeting version and SQL VERSION() both
  `10.11.18-MariaDB-ubu2204`; host info `Localhost via UNIX socket`.
- Server comment `mariadb.org binary distribution`, machine x86_64,
  compile OS debian-linux-gnu, socket /run/adb-owned/native.sock. This is the
  container path alias of the private host socket, not a production path.
- no_tcp/read_only/autocommit each1; in_transaction0; CURRENT_USER root@localhost,
  connection ID4. No report-mode toggle: this CLI audit requires3, not a claim
  that genuine WordPress selected OFF or authenticated any application user.
- Before, during and independent post-close: zero other sessions, all-server
  INNODB_TRX and application schemas. The PHP client executed exactly one SELECT
  and returned close_ack=true. Three outer audit client SELECTs total; vendor
  system-table bootstrap SQL is separate and not represented as zero writes.
- Normal server stop verified not running/exited0/OOMKilled=false; bounded logs
  include ready-for-connections and Shutdown complete. Exit and always cleanup
  each report containers0/directories0/database tmpfs released. No retained
  application fixtures, WordPress state or installed lifecycle state existed.

Retained diagnostics, not suppressed or accepted as a qualification policy:

- Fresh version-only preflight again emitted MAC-address retrieval warning.
- bootstrap.log2,137 bytes, SHA256
  `ac7dc5f98381cbd6834c0a97a5d99fa74364a95b14fcfa81cce6f5ffc4535733`:
  MAC warning, two WSREP base-address warnings, one WSREP incoming-address warning,
  and io_uring_queue_init EPERM/kernel.io_uring_disabled diagnostic.
- server.log3,135 bytes, SHA256
  `11b35a8dd6b7e6b9a6f0ab985a97c40609c32271c53279a1b9b330f6f5d9e784`:
  WSREP address warnings, io_uring EPERM, and two ignored hostname/proxy privilege
  entries under --skip-name-resolve, followed by ready/shutdown records.
  The hostname is an ephemeral container ID, not an existing user/site identity.
- Filtered retained warning/error/start/stop records contain no ERROR line;
  this is not a full raw-log or harmless-warning classification. No server IO,
  network, account, acceptance or warning-suppression setting was changed and
  no second attempt ran. Successful metadata reads do not qualify those contexts
  for the full initializer, escape/CAS/failure or WordPress integration lanes.

This closes the owned socket/version observation, not native adapter admission.
Source inspection shows the current core7.1.4 exact candidate is
`10.11.18-MariaDB`, which differs from the actual normalized guard input. The
adapter/initializer was not invoked, so no actual native-owner refusal/success
is claimed. No prefix/suffix wildcard or supported-build list was broadened.
Evidence-only final-head CI/status is verified separately at handoff; native
observations belong to d48b8f1, not silently to the documentation closeout head.

Implementation-plan progress: missing intended-lab Unix-socket/wire identity
observation completed; the next meaningful step is an exact-build/qualification
policy decision, not generic safe-local maintenance. Recommended next gated
slice: **Repository-only Linux Exact-Build Admission and Diagnostic Qualification
Design**, using actual10.11.18-MariaDB-ubu2204 evidence and retained diagnostics.
Specify the precise code-owned row, preservation of historical Windows/minimum
lanes, CLI/report3 vs genuine core/OFF constraints, and warning/resource-profile
proof requirements before any implementation or larger native proof. Keep
database/native execution and production wiring disabled during that design;
do not use current observations as permission to ignore warnings or add wildcards.
