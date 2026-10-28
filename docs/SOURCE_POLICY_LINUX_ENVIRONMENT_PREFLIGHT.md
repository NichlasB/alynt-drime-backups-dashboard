# Source-Policy Off-Production Linux Environment Preflight

## Authority, target and baseline — 2026-10-28

Approved next slice from the [fixed transport/charset foundation](SOURCE_POLICY_FIXED_TRANSPORT_CHARSET_FOUNDATION.md).
Clean/synced dashboard baseline `6ff6af68f068a8f701fc805ede1254f3d378cc71`.
R3 environment/image acquisition and owned ephemeral filesystem/process/container
cleanup only, with publication and exact-head CI. Autonomous R2 is not raised.

Resolved target: one explicitly dispatched GitHub-hosted Ubuntu24.04 x86_64 job
in NichlasB/alynt-drime-backups-dashboard, exact approved master SHA. This is an
ephemeral environment preflight, not a registered WordPress site or LocalWP
operation. No installed site, live host, production configuration/credentials,
client, Drime or existing database is accessed. No workstation toolchain is
installed. Database initialization, server startup/listener, authentication, SQL,
accounts/fixtures, WordPress bootstrap/lifecycle, actual adapter execution and
production wiring remain outside scope. PHP/WordPress/schema floors unchanged.

## Public provenance resolved before setup

Read-only upstream metadata/source acquisition, never a fresh production audit:

- [PHP8.5.12 release](https://www.php.net/releases/8_5_12.php) exists. The existing
  setup action supports exact patch versions and tools:none. Its v2 annotated
  tag resolved to commit `eb7c497e18156a6bbabfef1d3a82760b9eda3962`; this new
  workflow pins that commit, not the moving tag. Setup is inside the disposable
  runner only. CLI/module SHA256 values are observed identities, not independent
  build trust.
- Official [WordPress7.1.4 source](https://github.com/WordPress/WordPress/tree/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a)
  resolves to `db96f5eaa77a8d3e8eec8923a518fa2326f0d46a`. All11 audited library
  files fetched in memory match existing reviewed SHA256 pins exactly. The lab
  checks those pins again plus static version text; it never includes core code.
- Official [MariaDB10.11.18 image](https://hub.docker.com/_/mariadb) tag metadata
  identifies linux/amd64 manifest
  `sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5`,
  selected immutably; transfer metadata104,043,037 bytes. Multi-platform index
  `sha256:8020e05c4c498d06c87f0a1db010eb79bd6f8fb30e9b763d4690c34ce1e61008`.
  [Release notes](https://mariadb.com/docs/release-notes/community-server/10.11/10.11.18)
  establish release identity, not connected server compatibility. Full observed
  binary build text must be retained; no prefix broadening of native admission.
- Checkout action pinned to resolved v5 commit
  `fbc6f3992d24b796d5a048ff273f7fcc4a7b6c09`, persist-credentials:false for both
  checkouts. These are source/provenance decisions, not vendor vulnerability or
  complete distribution/authentication attestations.

## Fixed preflight, limits and cleanup

The dedicated workflow is workflow_dispatch only: no push/PR/schedule/native
service trigger. Exact SHA is checked before checkout/setup; fixed repository,
master, Linux/architecture checks; contents:read, ten-minute job timeout and
serialized runs. No production secrets, privileged mode, host bind mount, open
port, alternate version/backend or arbitrary command/host input.

The standalone PHP observer runs under PHP -n with explicit mysqlnd/mysqli,
64MiB memory and a20s owned-child timeout. It requires exact PHP/mysqlnd8.5.12,
no prepend/append or included application code, Unix transport support, all11
pins/version, fresh report mode3 and zero connect-success/failure/com_query
counters. It reads metadata only: no mysqli_init/connect/query, wp-load.php or
WordPress API. A fixed bounded capture rejects stderr, malformed/noisy/oversize
output and failed/timed-out children. Genuine core report-OFF remains unproven.

The digest-pinned image is pulled with a180s limit. The sole version-only
container is network:none, read-only root, explicit non-root runner UID/GID,
capabilities dropped, no-new-privileges, no healthcheck,16 PIDs/128MiB/one CPU,
20s timeout. Its entrypoint is replaced with fixed version/digest observations;
--no-defaults prevents configuration reads. The image's DB-volume location is
overridden by tiny private tmpfs, not an anonymous persistent database volume.
No entrypoint initialization or server starts. Actual client/server binary
digests and full version/build strings are observations, not wire/session facts.

A fresh mode700 run/attempt-owned directory reserves a <=96-byte future socket
path without creating/binding a socket. No DB principal or real socket access is
established. Exact labeled/captured-ID container cleanup and validated core-child
checkout cleanup run on exit and in an always step. Docker observer failures,
foreign labels/IDs, symlink/path/owner mismatch or unexpected private contents
refuse cleanup success; there is no global prune, foreign process kill or root
workspace deletion. PHP packages/image layers are ephemeral runner resources
removed by platform teardown, not an independently observed physical wipe.

## Validation and result status

Local repository-only gate/pin/workflow regressions6 tests/32 assertions pass.
Full local PHP8.5.1:1,962 tests/8,474 assertions, two existing Sodium skips only.
PHPCS222 files, admin16/16, structure149 runtime/227 support/203 test files/
200 class names, build, Composer strict validation/audit and npm moderate audit
pass, zero known advisories/vulnerabilities. Existing Git Bash -n and fixed
capture JavaScript syntax pass; no shell/native preflight executes on Windows.
No existing YAML library was available; none was installed. GitHub's actual
workflow parser/registration is the remaining canonical syntax gate before
dispatch, not a claim of completed local YAML-schema validation.

Runtime/includes, manual proof images, ordinary CI, assets, dependencies,
version/schema, public factories, application/CAS/form and production wiring
remain byte-unchanged from baseline. Historical native receipts are not refreshed.
Source-scoped workflow review covers exact-head admission, public source trust,
effect absence, bounded resources and owned cleanup; it is not full release
readiness, Database Review, UI review or native qualification.

Publication, exact-head ordinary CI and one opt-in lab dispatch remain pending.
Do not report readiness from source availability alone. The final record must
include actual run/head, runtime/core/build observations and cleanup outcome.

Implementation-plan progress: deployment-matched environment preflight prepared;
real socket/session/schema/escaping/CAS/failure and independent all-server release
proof remain separately gated. If the finite environment qualifies, the next
approval is one bounded owned Linux Unix-Socket Native Qualification/Cleanup
Proof with exact target/storage/process scope and fresh prerequisites. Any
observed build/profile mismatch requires its exact compatibility decision first;
do not change acceptance or improvise another environment on preflight authority.
