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
no whole-plugin review/readiness/checklist row is refreshed. Green ordinary CI
cannot erase this refusal.
158 owning local documentation targets and whitespace checks passed; scoped
source/security/resource/error/provenance/test-integrity review preserves all
recorded limits and the actual attempt source cohort. No fresh physical attempt
or artifact mutation was used to obtain green ordinary checks.

Harness/refusal publication `930273c176f7834b795d404d650c951474d707e0` is pushed;
[exact-head ordinary CI 41139628673](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41139628673)
succeeded. Actual PHP 7.4: 2,440 tests / 22,951 assertions; PHP 8.3: 2,440 /
22,956, no failures/skips on either. Admin 23/23 each, no failures/skips;
manifest, dependency audits, build, structure and PHPCS passed, zero known
dependency advisories/vulnerabilities. Existing Setup Node `punycode`/
`url.parse()` deprecation warnings remain tool-level warnings, not suppressed.
This docs-only closeout receives its own pushed exact-head ordinary CI and
clean/remote verification at handoff. No new payload attempt follows.

## Approved bounded refusal diagnostics — 2026-10-31

Restore baseline `1b10c0e908ad303732a54dd24e652ddf83b88819` was clean/synced
before the approved changes. User approves harness-only refusal diagnostics,
offline regressions/fresh prerequisites and **one** fresh static acquisition/
cleanup attempt. Original pins, limits, path predicate and transformations,
transport and cleanup rules remain unchanged. No normalization, acceptance
expansion, image fallback, extraction, execution or additional attempt is
authorized by this slice. Uploader/toolkit remain read-only.

The failure-only report adds bounded predicate, length/depth, segment flags,
member kind/index and SHA256 of at most the first 1,024 characters; it never
prints the name. Long names explicitly label a prefix hash. Offline tests
compare the original boolean decisions, exercise each refusal predicate,
non-text/long/surrogate inputs, tar context and report/cleanup propagation with
network blocked. Narrow check: 2 PHP tests / 12 assertions; 14 Python groups,
zero failures/errors/network calls. This is synthetic diagnostic evidence,
not artifact qualification. A subsequent synthetic symlink fixture reproduces
the observed 58-character/four-segment/U+0151 refusal facts without copying or
retaining the actual name. No diagnostic changes affect archive acceptance.

### One fresh static attempt: specific refusal, cleanup confirmed

Fresh prerequisites passed: related DATA/artifact 114 tests / 1,933 assertions,
no failures/skips; structure and whitespace checks passed. The single new
attempt used the exact approved CLI flag and unchanged pins/ceilings. Both
manifest/config chains and the 3,437-file pinned core metadata tree qualified.
The same runtime layers 0 and 1 passed full diff-ID checks; layer 2 compressed
bytes/SHA256 passed again, but its scan refused before its diff ID completed.

Actual safe report: `status=refused`, `failure_stage=wordpress-layer-2`,
`failure=archive-path`. Failure-only facts:

```text
predicate=ascii; characters=58; depth=4; member_type=symlink
entry_index=5043; layer_entry_index=210
hashed_characters=58; hash_scope=full-name
sample_sha256=8fa5a308ef4b272950d3fa0cbf7c32e2872c369fed8aa045f32a075fa68d5b85
sample_first_non_printable_ordinal=337 (U+0151; outside printable ASCII)
trailing_separator=false; empty_segment=false; dot_segment=false
parent_segment=false; leading_dot_slash_removed=false
directory_slashes_removed=false
```

This identifies a non-ASCII symlink **name** rejected by the existing printable-
ASCII predicate. It does not establish its link target, critical-role relevance,
safe normalization, or permission to broaden acceptance. No raw archive path
was printed or retained. The report label `sample_first_non_printable_ordinal`
means outside the guard's ASCII interval, not a claim that U+0151 is unprintable
Unicode. No later payload, core ZIP, ELF identity or native evidence qualified.

Cleanup removed seven exact owned files and root
`adb-linux-minimum-static-f5q6479a`; independent read-only existence check
confirmed it absent. No candidate files remain staged and no user data was
removed. Source readbacks were unchanged; actual attempt source-image SHA256
`77a69eb7b4954e0feba36871cd0a72e70666a2c07f74ce47cfc08e9ba4fb7462`.
That freezes the acquisition/reader/pins/tuple/trusted executable, not later
test/docs edits or a whole-host attestation. Candidate execution, database
operations, Docker image loading and rootfs extraction were all false. The
one-attempt allowance is consumed; no second acquisition follows this slice.

### Diagnostic-slice QA and publication

The initial red regression failed because generic refusals had no predicate
facts; green offline checks now exercise 14 groups with network blocked.
Final narrow rerun: 2 PHP tests / 12 assertions, no failures/skips. Related
DATA/artifact checks: 114 / 1,933, no failures/skips. Full trusted local PHP 8.5.1
suite: 2,440 tests / 22,949 assertions, no failures; two existing Sodium-
unavailable signing skips, not waived or represented as passing runtime proof.

PHPCS 244 files; admin 23/23, zero failures/skips; structure 149 runtime /
235 PHP support / 227 test files / 224 classes; build passed without tracked
asset delta. Strict Composer validation, Composer audit and npm moderate audit
passed with zero known advisories/vulnerabilities. Whitespace and 153 owning
local documentation targets passed. Scoped diff review covered failure-only
data bounds/privacy, original guard ordering/decisions, cleanup propagation,
non-vacuous accept/refuse tests and exact source/proof claim separation.
Existing runtime/includes, pins, tuple contracts, workflows and dependencies
are unchanged. Uploader/toolkit remain clean at their original heads. No
whole-plugin review, release-readiness or physical qualification is refreshed.

Diagnostic/refusal publication `524dc30a4bbf6465aa109e6607efca180de1e22c`
is pushed; [exact-head ordinary CI 41188227040](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41188227040)
passed. Actual PHP 7.4: 2,440 tests / 22,951 assertions; PHP 8.3: 2,440 /
22,956, neither with failures/skips. Admin 23/23 per matrix, no failures/skips;
manifest validation, audits, build, structure and PHPCS all passed, zero known
dependency advisories/vulnerabilities. Existing Setup Node `punycode` and
`url.parse()` deprecation warnings remain visible tool-level warnings.
The docs-only evidence closeout is independently pushed/exact-head CI-checked
at handoff. Historical 2026-10-30 QA/CI above remains historical, not refreshed
evidence for this diff. Ordinary CI cannot qualify the refused artifact or
authorize another physical attempt; no new retrieval follows publication.

## Approved Unicode-policy static attempt — 2026-10-31

Clean/synced restore baseline `5ec779bb7bbc73f1dc8f96f9d2e0d8fffa06f619`.
User approves one fresh digest-pinned static acquisition/owned-cleanup attempt
using the published tar-only Unicode policy, after fresh offline prerequisites.
All original payload/parser/member/file/time/TLS/cleanup ceilings and pins above
remain. No normalization, budget increase, fallback, second attempt, extraction,
image loading, candidate/native execution, site/database operation, installation,
release/deployment or production wiring. No LocalWP/live target or Novamira
operation applies. Uploader/toolkit remain clean/read-only at original heads.
Frozen acquisition/reader/pin/tool sources were read back after cleanup.
The one approved attempt is consumed; this checkpoint grants no wider retry.

### Actual result: static verified, cleanup independently confirmed

Fresh prerequisites: related DATA/artifact 114 tests / 1,933 assertions, no
failures/skips, including strict 23 offline groups and 23 assertion-killed
controls; structure/whitespace passed. Existing trusted Python 3.12.14 reports
Unicode database 15.0.0 and verified TLS/hostname checking. The acquisition and
reader, pin/tuple files and all executable/test/runtime/dependency/workflow
sources were unchanged from the baseline before the physical attempt.

Executed the fixed entry exactly once with `-I -B` and
`--approved-static-artifacts`. Exit 0, `status=static-verified`,
`cleanup=confirmed`, `source_unchanged=true`. Both immutable manifest/config
chains passed. All **25 compressed layer blobs and full uncompressed diff IDs**
passed (17 WordPress / eight MariaDB), including previously blocked layer 2.
The scan counted 26,567 entries and 948,137,472 expanded tar bytes within the
original ceilings. No normalization, alternate image, relaxed guard, retry or
budget increase was needed.

Payload totals: WordPress 212,262,998 compressed bytes; MariaDB 104,043,037;
core ZIP 32,963,838; aggregate payload 349,269,873 bytes. Total staged bytes
including four manifest/config files: 349,301,254. Complete pinned core tree
`a3bd7a4074a4c95c8af3b969346726273d68e167` / commit
`cc101b64012b16d087780657a2b828ccd7794a63`: all 3,437 file byte streams agree
with the pinned Git blob set, all 11 existing source SHA256 pins agree,
74,132,675 expanded core bytes. Core ZIP SHA256:
`f94b2d521ea9c38d965d1adf8ead55a1b45dc171795bd550e94cd2ce99c7a437`.
Git metadata modes/tree were verified separately; ZIP entry kinds were checked.
No host checkout/permissions or ZIP permission-bit equivalence is claimed.

Selected direct regular members reached bounded static ELF inspection:

| Role | Bytes | SHA256 |
| --- | ---: | --- |
| `usr/local/bin/php` | 16,803,880 | `51810786a5d7ab1e43e7d43077493d7dfced717cc81fc08270cde58b32731d29` |
| `usr/local/lib/php/extensions/no-debug-non-zts-20190902/mysqli.so` | 154,752 | `aa91cdc28133fac497aade6a658e83c219fcbbbfb809069969dd32c749a8657e` |
| `usr/sbin/mariadbd` | 25,418,352 | `93b8d1f5ae7141c6b53067d9fc8222683957e26ea87205dd372b1016777a2973` |

All three report ELF64 x86_64, type 3, uid/gid 0. Raw recorded modes are
33,261 for PHP/mysqli and 493 for mariadbd; no chmod or host permission
application occurred. PHP/server interpreter fields are
`/lib64/ld-linux-x86-64.so.2`; mysqli's is null. Direct `DT_NEEDED` strings:

- PHP: libargon2.so.1, libresolv.so.2, libreadline.so.8, libm.so.6, libdl.so.2,
  libxml2.so.2, libssl.so.1.1, libcrypto.so.1.1, libsqlite3.so.0, libz.so.1,
  libcurl.so.4, libonig.so.5, libc.so.6.
- mysqli: libc.so.6.
- mariadbd: libpcre2-8.so.0, libcrypt.so.1, liburing.so.2, libaio.so.1,
  libsystemd.so.0, libz.so.1, libssl.so.3, libcrypto.so.3, libstdc++.so.6,
  libm.so.6, libgcc_s.so.1, libc.so.6, ld-linux-x86-64.so.2.

All three report empty search-path lists and `dependency_closure_verified=false`.
These names are static declarations, not located/hashed dependency files,
successful symbol resolution, loaded module identity or safe execution evidence.
PHP_VERSION 7.4.33 and MariaDB package version remain config declarations, not
observed binary/client/server runtime identities. mysqlnd build/loading/ABI,
transitive dependencies, vulnerabilities and genuine native behaviour remain
unqualified. No `ldd`, loader or binary invocation occurred.

Cleanup removed **30 exact owned files** (four manifest/config files, 25 layer
blobs and core ZIP), then `adb-linux-minimum-static-kt_urma2`. Independent
read-only existence check confirmed that exact OS-temp child absent. No payload
or candidate data remains staged; no user data was removed. Source-image SHA256
`4dd6fa3d85ea30e61fbd112e60f0dbb483c54b3ef2de44fc874032f80f27d2af`
binds the acquisition/reader/pins/tuple/trusted executable readbacks, not a
whole-host or library-closure attestation. Tool facts: Python 3.12.14, OpenSSL
3.5.8, Unicode 15.0.0. Candidate execution, database operations, Docker image
loading and rootfs extraction were all false.

The historical refusals remain accurate for their source cohorts; this later
source-bound success closes their static path blocker. It does not establish
general OCI/rootfs application, container/network isolation, connection-free
runtime preflight, lab receiver integration, database qualification or product/
production readiness.

### Repository QA and publication

Trusted local PHP 8.5.1 full suite: 2,440 tests / 22,949 assertions, no failures,
two existing Sodium-unavailable signing skips. Fresh relevant checks: 114 /
1,933, no failures/skips, including 23 offline Python groups and 23 assertion-
killed controls. PHPCS 244 files; admin 23/23, no failures/skips; structure 149
runtime / 235 PHP support / 227 test files / 224 classes; build passed without
tracked generated changes. Strict Composer validation and Composer/npm moderate
audits passed, zero known project dependency advisories/vulnerabilities; these
audits do not clear the legacy donor images. Whitespace and 164 owning local
documentation targets passed. Final source/provenance/resource/error/cleanup/
claim-limit review confirms only three owning docs changed, no executable,
test, pin, tuple, runtime, workflow or dependency changes. Uploader/toolkit
remain clean/read-only; no historical whole-plugin readiness row is refreshed.
Evidence publication `85b67af7059d77085f63d5f804005157ef26b27b` is pushed;
[exact-head ordinary CI 41191157436](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41191157436)
passed. Actual PHP 7.4: 2,440 tests / 22,951 assertions; PHP 8.3: 2,440 /
22,956, no failures/skips. Admin 23/23 per matrix, no failures/skips; manifest,
audits, build, structure and PHPCS passed, zero known project dependency
advisories/vulnerabilities. Existing Setup Node `punycode` / `url.parse()`
deprecations remain visible tool-level warnings. This docs-only closeout is
pushed and independently exact-head CI/clean/remote verified at handoff. Ordinary
CI does not extend the physical proof's claim limits or authorize execution;
no further physical attempt follows publication.

## Exact resume recommendation

Implementation-plan progress: complete selected-layer/core-byte and selected
ELF static evidence now passes under the [published Unicode policy](SOURCE_POLICY_UNICODE_ARCHIVE_PATH_DESIGN.md),
with owned cleanup confirmed. Dependency closure and runtime/native admission
remain open; this is a real static-evidence milestone, not product completion.
Do not reopen Windows observer work or fill the queue with generic cleanup.

Follow-up: the approved [dependency/preflight admission design](SOURCE_POLICY_LINUX_MINIMUM_PREFLIGHT_ADMISSION_DESIGN.md)
selects staged non-starting inventory and PHP-only observations, with independent
host-observer feasibility first. It changes no executable or physical evidence.

Recommended next gated slice: **Linux Minimum-Lab Existing-Authority Read-Only
Host-Observer Feasibility and Owned Harmless-Child Cleanup Audit** on an approved
ephemeral Linux CI host. No candidate download/loading/execution, database/site
operation, elevation or permission change. Verify that the required observer is
possible under existing approved authority before further runtime-admission
implementation; the completed static acquisition authorizes none of that work.
