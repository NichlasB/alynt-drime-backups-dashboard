# Linux Minimum-Lab Digest-Pinned Artifact Static Proof

## Approved audit-first scope — 2026-10-30

Clean/synced restore baseline `59d6b2736b40b4be07ab7f211f26262c21edacf3`.
User approves acquisition/static verification and exact owned cleanup only.
The [candidate design](SOURCE_POLICY_LINUX_MINIMUM_CANDIDATE_FEASIBILITY_DESIGN.md)
and [DATA foundation](SOURCE_POLICY_LINUX_MINIMUM_TUPLE_FOUNDATION.md) own the
immutable candidates and proof limits. This is an explicitly approved bounded
file/network proof, not permission to load/install/run candidate artifacts,
create Docker images/containers, bootstrap core, authenticate to a site/database,
run SQL, change credentials/permissions/services/schedules or alter production
wiring. No LocalWP/live target or Novamira operation applies. Uploader/toolkit
stay read-only. Ordinary repository validation, commit/push and CI remain in scope.

The main implementation-plan resume point is minimum-profile evidence, not
Windows observer work. Physical native qualification, receiver integration and
production/form wiring remain gated. This proof cannot complete whole-plugin
reviews or release readiness; no package/release/deployment or toolkit rows.

## Pre-payload audit and fixed limits

Read-only bounded registry metadata audit verified SHA256 of both pinned
Linux/amd64 manifests. WordPress runtime platform manifest `08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4`
describes 17 layers / 212,262,998 compressed bytes, maximum layer 91,634,870;
MariaDB manifest `161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5`
describes eight / 104,043,037, maximum 68,725,477. No mutable tag is resolved.

Digest-bound config blobs were inspected as JSON, not executed:

- Runtime config `de3b9412d904e00792e7a1504f6bc38c17f44451c18bf75515b97e5f9d0bfe18`,
  16,241 bytes: linux/amd64, PHP_VERSION 7.4.33, PHP_INI_DIR
  `/usr/local/etc/php`, entrypoint docker-entrypoint.sh, CMD php-fpm,
  declared volume `/var/www/html`, 17 rootfs diff IDs.
- Server config `b5077e20185da2defdc68763c9f87e9ce6c449207f9e8de120dee346b54ce696`,
  8,783 bytes: linux/amd64, MARIADB_VERSION `1:10.11.18+maria~ubu2204`,
  entrypoint docker-entrypoint.sh, CMD mariadbd, volume `/var/lib/mysql`,
  eight diff IDs. Package declaration is not an observed server-info string.

Before any layer/core payload retrieval, fix these ceilings:

- Two fixed registry repositories; at most 32 layers each. Metadata response
  at most 2 MiB, aggregate 32 MiB. One layer at most 128 MiB compressed;
  total staged/downloaded payload at most 512 MiB; core ZIP at most 64 MiB.
- Layer scan at most 1 GiB uncompressed per layer, 2 GiB aggregate; at most
  65,536 returned tar entries across the proof; individual member at most
  128 MiB. Parser reads at most 1 MiB each, including metadata, and refuses
  sparse/unknown special entries. No host extraction, link creation or rootfs
  assembly. Link metadata is never followed against the host filesystem.
- Core at most 8,192 files / 128 MiB expanded / 32 MiB per file; depth at most
  32 and relative path at most 240 bytes. Require complete pinned Git tree and
  all 11 existing minimum source SHA256 pins; no source includes/require/eval.
- Whole proof 20 minutes; per request at most 180 seconds, socket wait at most
  10 seconds, checked between bounded reads; no retry/resume or fallback.
  TLS peer/hostname validation stays on, no proxies/custom CA/credentials or
  TLS key logging. Anonymous public registry pull token is transient memory
  only, never logged/saved/forwarded to CDN. One explicit HTTPS blob redirect
  allowed only to the audited `production.cloudfront.docker.com`, without auth.
- One new randomly named direct child of the existing OS temporary directory,
  prefix `adb-linux-minimum-static-`, exclusively created flat internal filenames
  only. Never join archive paths to host paths. At most 96 owned files; no
  overwrite, recursive deletion, broad Docker cleanup or system configuration.
  Before deletion validate original resolved parent/root, absence of links/
  reparse points, and exact owned membership. Unknown children mean retain and
  report, not broaden cleanup. Remove only verified owned files and empty root.

Trusted analysis tooling is the existing configured bundled Python 3.12.14 /
OpenSSL 3.5.8 (not a candidate PHP/runtime); preflight observed CERT_REQUIRED and
hostname checking with default system trust. [Python documents Windows CA/ROOT
store loading](https://docs.python.org/3/library/ssl.html#ssl.SSLContext.load_default_certs).
The ordinary TLS trust probe returned only public metadata length, never a token.
Reject trust/keylog overrides; no installation, compiler, account or ACL work.

## Static method and claim limits

Verify each manifest/config/layer byte count and SHA256 before parsing, and each
layer's full uncompressed diff ID. Treat [registry descriptors](https://distribution.github.io/distribution/spec/api/)
and [layer changes/whiteouts](https://github.com/opencontainers/image-spec/blob/main/layer.md)
as untrusted bounded data. Record literal configuration, direct regular-file
critical roles and ELF architecture/interpreter/direct dependency metadata only.
Account for critical-role whiteouts and refuse linked ancestors/roles rather
than claiming a rootfs reconstruction. No dynamic loader, executable or shared
library is invoked. Direct ELF metadata is not actual loaded dependency closure,
PHP/mysqli/mysqlnd runtime identity, server wire identity or vulnerability clearance.

For genuine core 6.0, bind commit `cc101b64012b16d087780657a2b828ccd7794a63`
to tree `a3bd7a4074a4c95c8af3b969346726273d68e167`; independently verify complete
mode/blob tree metadata and actual archive bytes, plus existing SHA256 source
pins. No donor-bundled WordPress substitution, extraction or core execution.
Freeze trusted harness/pin/tool source hashes before the attempt, verify after
cleanup, report non-secret findings only. A refusal is a real result, not grounds
to relax limits, replace images or repeat an unapproved broader proof.

## One owned attempt: refused, cleanup confirmed

The single approved attempt used the new fixed
[acquisition entry](../scripts/manual/source-policy-linux-minimum-acquire.py) and
[static reader](../scripts/manual/source_policy_linux_minimum_static.py), never
Docker or an artifact executable. Metadata established the complete 3,437-file
core mode/blob tree against the pinned Git tree, plus both manifest/config
chains. This is metadata verification, not verification of core archive bytes.

Progress before refusal:

- WordPress layer 0: 31,412,630 compressed bytes verified; full uncompressed
  diff ID verified over 84,000,256 bytes.
- WordPress layer 1: 226 compressed bytes verified; full diff ID verified over
  3,584 bytes.
- WordPress layer 2 compressed blob was fully byte-count/SHA256 verified:
  `156740b07ef8a632f9f7bea4e57e4ee5541ade376adf9169351a1265382e39de`,
  91,634,870 bytes. Its static tar scan refused `archive-path` before completing
  the diff-ID check. The original diagnostic does not retain the rejected name
  or which path predicate failed. Do not infer traversal, non-ASCII, a Windows
  name issue, excess length or a safe normalization from that generic code.

The attempt exited 1 with `status=refused`, `failure_stage=wordpress-layer-2`.
No later layers, server payloads, core ZIP, critical ELF identities or complete
payload/static verification succeeded. No retry or relaxed limit follows.
Observed config PHP_VERSION/package declarations above remain distinct from
actual loaded runtime/server observations, of which there are none.

Cleanup removed **seven exact owned files** (four manifest/config files and
three compressed layer blobs), then the owned root
`adb-linux-minimum-static-7yqk6of0`. Independent read-only existence check was
false. No artifact data remains staged; it can only be reacquired from the
same pins under a fresh approval. No user data was removed. Candidate execution,
database operations, Docker image loading and rootfs extraction were all false.
Trusted source readbacks were unchanged; actual attempt source-image SHA256:
`e1fca363054a69539d3574534abae2968b1a9d145495b29e452ca6629b514c49`.
That image binds entry/static reader, existing source pins/tuple helper and
the trusted analysis executable; it is not an independent whole-host attestation.

## Repository validation and publication

New [offline tests](../tests/SourcePolicyLinuxMinimumArtifactTest.php) run 12
Python stdlib groups with network creation blocked during project import and
tests. They cover descriptors/duplicate JSON/bounds, virtual paths, ELF headers,
complete layer hash checks, links/traversal/duplicates/special entries, whiteouts,
scan bounds/time, existing core pins/tree calculation, synthetic complete-core
ZIP bytes/modes/pins, HTTPS redirect/auth stripping, digest/HTTP limits and exact
owned cleanup. Collision fixtures prove exclusive creation never overwrites
foreign bytes and cleanup refuses unknown membership before deleting anything.
Synthetic core fixtures do not qualify the genuine core archive.

An initial offline helper kwargs error was corrected before payload retrieval;
all safety checks passed before the attempt. No frozen acquisition/reader/pin/tool
source changed during/after the actual attempt. Later offline test strengthening blocks network
on import and makes host environment simulation explicit; it is not a retry.

Executed with trusted existing PHP 8.5.1 / bundled analysis Python:

- New artifact tests: 2 PHP tests / 12 assertions, including 12 offline Python
  groups, zero failures/errors/network calls. Related DATA/artifact checks:
  114 tests / 1,933 assertions, no failures/skips.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,440 tests /
  22,949 assertions, no failures; two existing Sodium-unavailable signing skips.
- PHPCS 244 files; admin 23/23, no failures/skips; structure 149 runtime /
  235 PHP support / 227 test files / 224 class names; asset build passed with no
  tracked generated delta. Strict Composer validation, Composer audit and npm
  moderate audit passed, zero known dependency advisories/vulnerabilities.

Local Windows QA selects the configured existing analysis Python with the
process-only `ADB_STATIC_ANALYSIS_PYTHON` variable; ordinary Ubuntu CI uses its
existing `/usr/bin/python3`. No interpreter/package is installed or downloaded.
Only the new development scripts/tests and owning docs change. Existing runtime
bootstrap/includes, PHP/profile/pin contracts, workflows/runners, dependencies,
client sites and production wiring remain untouched. Uploader/toolkit read-only;
no whole-plugin review/readiness/checklist row is refreshed. Publication evidence
follows after scoped review and push; green ordinary CI cannot erase this refusal.
158 owning local documentation targets and whitespace checks passed; scoped
source/security/resource/error/provenance/test-integrity review preserves all
recorded limits and the actual attempt source cohort. No fresh physical attempt
or artifact mutation was used to obtain green ordinary checks.

## Exact resume recommendation

Implementation-plan progress: acquisition/containment is implemented and partial
byte evidence/cleanup is established, but complete artifact qualification remains
blocked at an unidentified path refusal. No native or product completion claim.
Do not reopen Windows observer work or fill the queue with generic cleanup.

Recommended next gated slice: **Harness-Only Bounded Archive-Path Refusal
Diagnostics and One Fresh Linux Minimum-Lab Static Acquisition/Cleanup Attempt**,
same pins/target/limits, with offline regressions and fresh prerequisites first.
Approve one further payload acquisition because no blocked-layer bytes remain.
Capture only bounded non-secret predicate/length/hash facts to identify the
actual refusal; do not preapprove normalization, broader path acceptance, budget
changes, image fallback, extraction or execution. Stop before any such new
decision. Runtime loading, native experiments and production wiring stay disabled.
