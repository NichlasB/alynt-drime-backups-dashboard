# Off-Production Linux Owned Unix-Socket Server-Identity and Cleanup Audit

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

Publication, exact-head ordinary CI and one approved audit dispatch are pending.
Report actual source/run, normalized wire+SQL identity, snapshots/diagnostics and
cleanup; do not infer success from local DATA checks or the binary banner.

Implementation-plan progress: the missing owned identity/socket observation is
prepared. Next gate depends on actual evidence. A build-row mismatch requires an
explicit exact-row compatibility decision before a larger native proof. No
admission broadening, adapter experiment or new application milestone follows
from this audit authority.
