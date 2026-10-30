# Linux Minimum-Profile Candidate and Exact-Build Feasibility Design

## Authority and decision — 2026-10-30

Approved repository-only design at clean/synced restore baseline
`95130db57ae748ffc02a79b820215500bca04177`. This follows the
[observer-route reassessment](SOURCE_POLICY_OBSERVER_DEADLINE_ROUTE_REASSESSMENT.md),
not a reopened Windows experiment. Documentation is the only changed artifact;
ordinary trusted repository QA, commit/push and exact-head CI are authorized.
No downloads of runtime/archive/layer payloads, provisioning, installation,
candidate execution, container launch, workflow dispatch, authentication, SQL,
fixtures, site operations or production-wiring changes. Read-only public JSON
metadata and source recipes were inspected; no candidate artifacts were saved.

**Select one conditional candidate: Linux/amd64 PHP 7.4.33 from a digest-pinned
official WordPress PHP-7.4 image, used solely as a PHP/mysqli runtime donor;
independently pinned genuine WordPress 6.0 sources; the existing digest-pinned
MariaDB lab image, but private numeric-loopback TCP instead of its old socket
profile.** This is source-feasible direction, **payload-unverified and
contract-blocked**, not an admitted, provisioned or physically qualified lab.
No minimum-support or production-connection claim changes.

The proposed server is exactly `10.11.18-MariaDB-ubu2204`, not the existing
core-6.0 contract's `10.6.23-MariaDB`. Preserve that rejection. Before any
implementation, separately approve the finite Linux lab tuple and its private
source-owned admission boundary. A runtime donor tagged with core 6.1.1 is not
permission to run that core or misreport its version as 6.0.

## Candidate identity and strength of evidence

| Component | Fixed candidate identity | Evidence and remaining limit |
| --- | --- | --- |
| Runtime donor | `docker.io/library/wordpress`, historical locator `6.1.1-php7.4-fpm`; Linux/amd64 platform manifest `sha256:08a1cb1fbe022cd08d5d19e6f95ab549194927959a297da41590d4f2d884f7b4` | Docker Hub tag JSON identifies this platform digest. No OCI config, layer, binary or actual runtime measurement was inspected. |
| Index receipt | `sha256:0b4b629f3f1389cb4e42570c452188d6f1ebe74d31aee6395bbb010450a0640e` | Metadata only; future acquisition must select the platform manifest above, not silently resolve a mutable tag/other architecture. |
| Expected runtime | PHP `7.4.33`, Linux x86_64, 64-bit CLI, non-ZTS; internal mysqli with built-in mysqlnd, expected client `mysqlnd 7.4.33` | Historical PHP/WordPress recipes support this expectation, not an attestation binding the selected image to those recipes. Actual binary, extension, build flags, client string, dependency/configuration identity and hashes remain unverified. |
| Genuine core | WordPress `6.0`, commit `cc101b64012b16d087780657a2b828ccd7794a63`, tree `a3bd7a4074a4c95c8af3b969346726273d68e167` | GitHub commit/tree metadata agrees with the existing minimum-artifact record. Preserve all 11 minimum per-file source pins; no source checkout/archive acquired here. |
| Server donor | Existing `mariadb@sha256:161be354206906ea8584929bde5ac59cdce0770fbfd5dd76131429f7dd80aab5` | Existing lab source/proof expects full identity `10.11.18-MariaDB-ubu2204`. Reusing the image does not transfer its old socket/core/PHP qualification; platform/config/binary identity must be bound and rechecked for the new lab. |
| Proposed transport | `tcp_loopback`, host `127.0.0.1`, integer port `3306`, `db_host` `127.0.0.1:3306`, socket `null` | Proposed fixed private namespace only, not LocalWP or live-site settings. No connection attempted. |

[Docker Hub tag metadata](https://hub.docker.com/v2/repositories/library/wordpress/tags/6.1.1-php7.4-fpm)
was read on 2026-10-30: reported last update 2022-11-16T22:24:05.857152Z and
amd64 compressed size 212,262,998 bytes. These are registry metadata, not verified
download size, unpacked-storage bounds, authenticity, freshness or vulnerability
clearance. Later acquisition must have independently fixed byte/file/time limits
and inspect the digest-addressed config/layers before loading a runtime. Abort
on unavailability/mismatch; no automatic donor, architecture or tag fallback.

Primary source context, not a reproducible-build or supply-chain attestation:

- [WordPress donor recipe at 97f75b5](https://github.com/docker-library/wordpress/blob/97f75b51f909fbd9894d128ea6893120cfd23979/latest/php7.4/fpm/Dockerfile)
  uses the then-mutable `php:7.4-fpm` base, builds mysqli, declares
  `VOLUME /var/www/html`, and defaults to its WordPress entrypoint and PHP-FPM.
  Recipe identity alone does not authenticate the immutable image's contents.
- [PHP recipe at b9f1715](https://github.com/docker-library/php/blob/b9f17156020c3aef71df681b27684533529347a7/7.4/bullseye/fpm/Dockerfile)
  specifies PHP 7.4.33 and `--enable-mysqlnd`. Its PHP source-tarball checksum
  is not a checksum of the image, executable, loaded extensions or libraries.
- [PHP 7.4.33 mysqlnd header](https://github.com/php/php-src/blob/php-7.4.33/ext/mysqlnd/mysqlnd.h)
  derives the mysqlnd version from PHP_VERSION. Verify the actual observed
  version later rather than substituting this expectation for observation.
- [Pinned genuine core commit](https://github.com/WordPress/WordPress/commit/cc101b64012b16d087780657a2b828ccd7794a63)
  binds the tree above. Donor `/usr/src/wordpress` content is never the core
  source under test; do not mount or include it as a substitute.

Other PHP-7.4 image tags and a public PHP-builder release were considered from
metadata only, not selected. A core-version tag or floating 7.4 installer does
not prove the exact PHP build. Do not add a second acquisition/installation
route, compile PHP, alter host INI/defaults or revive a custom loader as fallback.
Legacy runtime use is confined to isolated compatibility testing, not deployment.

## Existing contracts: what is reusable and what must not be relabeled

- [`Transport_Profile`](../includes/class-source-policy-transport-profile.php)
  permits core-6.0 numeric-loopback TCP independently of platform, but
  `server('6.0')` still returns exactly `10.6.23-MariaDB`.
  [`Native_Profile::admit`](../includes/class-source-policy-native-profile.php)
  consumes that mapping without a distinct lab identity. Core 6.0 plus the
  proposed 10.11.18 server therefore must fail today. Neither a matching TCP
  frame nor passing expected DATA is a connection grant.
- The exact proposed delta is **one lab-only tuple**: Linux/amd64 + verified
  PHP/mysqlnd 7.4.33 donor + genuine core-6.0 tree/pins + that immutable MariaDB
  image/full server identity + the fixed TCP endpoint above. Do not change the
  legacy core-6.0/Windows 10.6 row, core-7.1.4 socket row, version-prefix rules,
  suffixes or minimum declarations to admit it. The future design must trace
  every facts producer/consumer and establish non-forgeable source ownership;
  no arbitrary profile argument, injected ready handle, fake core version or
  test-only bypass in the production path. If separation is not achievable
  within a small finite boundary, stop rather than broadening acceptance.
- [`supported-core-pins`](../scripts/manual/source-policy-supported-core-pins.php)
  retains its genuine 6.0 files and Windows `php7.dll` runtime expectation.
  Preserve it; do not use PE/DLL receipts to authenticate an ELF runtime. New
  Linux identity would need explicit source-owned records, not overwrite these.
- The [existing Linux workflow](../.github/workflows/source-policy-linux-qualification.yml),
  [preflight](../scripts/ci/source-policy-linux-preflight.sh) and
  [native runner](../scripts/ci/source-policy-linux-qualification.php) bind
  PHP 8.5.12, genuine core 7.1.4, mode 3, Unix socket and a two-shared-module
  mysqlnd/mysqli loading model. Their 282/9 historical proof cannot qualify
  this candidate. Minimum core instead needs its existing mode-0 and autoload
  `no`/states `yes,no` contracts, genuine parser/escaping and actual adapter
  save/conflict/refusal coverage. Reuse ownership/cleanup patterns, not receipts
  or a caller-supplied replacement runtime. No workflow is edited/dispatched.

This proposal tests minimum PHP/core on one newer MariaDB build; it does not
establish minimum-database-version coverage, all Linux distributions, all PHP
7.4 builds, Windows minimum qualification or supported production connectivity.

## Proposed contained launch, resource and trust model — not implemented

Use an explicitly approved disposable Linux/amd64 CI lab, not this workstation,
Plugin Tester or control-sitesmanage. Keep the existing exact-head/manual
dispatch gate; no push-triggered native job or public input selecting a command,
image, mount, endpoint, executable, configuration, core or acceptance predicate.
Freeze source manifests, runner/action pins and budgets before a physical run.

The owned server would use Docker `none` networking with TCP bound only to
127.0.0.1:3306, rather than the old `--skip-networking` socket setup. The owned
PHP container would share **that exact owned server CID's network namespace**,
not host networking, through Docker's `container:<id>` mode. Docker documents
[none as loopback-only](https://docs.docker.com/engine/network/drivers/none/) and
[container-mode namespace sharing](https://docs.docker.com/engine/network/#container-networks).
This makes private loopback a plausible topology, not a tested connectivity or
isolation result. Reject foreign CIDs, published ports, bridge/default networks,
extra interfaces/routes and daemon mounts inside the worker; no external DNS
or credentials. Fresh namespace/config inspection must precede authentication.

Explicitly bypass donor entrypoint/FPM: fixed `/usr/local/bin/php` CLI with
`-n`, an empty controlled INI scan path, fixed mysqli loading and no
auto-prepend/append/bootstrap/plugin entry. Expected mysqlnd is built-in; do
not blindly copy the newer runner's `mysqlnd.so` load or retry with arbitrary
modules/configuration if it is absent. Connection-free preflight must first
prove actual module origins, exact versions/build/ABI/dependency paths, loaded
INI/extensions, report mode and zero connection counters. No genuine core or
candidate PHP is loaded for this design.

Trust the explicitly approved official digest/registry distribution, Docker
engine/kernel and CI host boundary, subject to later static artifact verification
and operator admission. Hashing a file is not an attestation, an ELF dependency
audit or protection from a malicious trusted host. No recursive OS verifier,
custom loader, compiler, global installer, new principal or permission changes.
If that bounded trust model is unacceptable, stop at the trust decision.

Future containment must account for image-declared volumes: read-only root alone
does not control the donor's `/var/www/html` volume. Require exact inspected
volume declarations and owned bounded tmpfs overrides; no anonymous persistent
volume, donor WordPress CWD or unknown write target. Core/source mounts are
read-only, CWD is owned/empty, output is bounded/redacted. Preserve existing
non-root, cap-drop, no-new-privileges and private ownership rules; specify PHP
limits before implementation, with server limits no broader than the existing
512 MiB/one CPU/64 PIDs, 256 MiB database tmpfs and 16 MiB temporary tmpfs.
At most the two owned containers plus bounded controller/child processes; no
new unbounded orchestration or automatic retry. The Windows candidate-128 and
tool-135 contracts are neither applied to Linux ELFs nor changed by this design.

Cleanup is a required result on success, refusal, timeout, cancellation and
partial startup: retain exact CID/labels/process ownership, independently bound
session identities, check live and post-exit releases, remove only owned mounts/
containers/temporary data, reconcile zero owned sessions/transactions/artifacts.
Never prune Docker globally, touch a service/site or call successful child exit
sufficient evidence of server-side release. Failed cleanup blocks continuation.
No such physical evidence has been generated here.

## Ordered gates and non-vacuous acceptance

1. **Next approval: repository-only Linux Minimum-Lab Exact-Tuple Admission and
   Source-Ownership Integration Design.** Resolve the full server mismatch and
   trace source/pin/facts ownership through existing admission/native callers
   without altering legacy acceptance. Design-only; no implementation, artifact
   acquisition, runtime/container launch or database operation. This is a real
   lab-contract decision, not another observer maintenance task.
2. Only after that design is approved, a bounded socket-free foundation may
   implement exact lab DATA/ownership checks with isolated tests. Require an
   accepted finite synthetic tuple plus independent rejection of changed image,
   runtime/client/core/server/suffix/transport/config/source-owner facts; ensure
   removing each mandatory guard causes a test failure. Distinguish positive
   DATA parsing from native readiness, handles or authorization. Prove all
   existing rows/rejections remain intact; no always-unavailable pseudo-proof.
3. Separately authorize digest-addressed acquisition/static config and complete
   source/dependency verification, with finite download/unpack limits and exact
   owned cleanup. Resolve unknown OCI config/layers/build linkage, binary/module
   hashes, volumes and runner sources before authorizing any runtime loading.
4. Separately approve connection-free runtime preflight; only after its genuine
   observations pass, approve one bounded native fixture qualification and exact
   target/ownership scope. Fresh actual report-OFF/ambient restoration, minimum
   core parser/escaping, non-reconnecting construction, save/conflict/failure and
   ADBC1/ADBC2 release evidence must be proved with owned storage and independent
   live/post-exit reconciliation. No adapter/form wiring or site activation is
   implied. Refusal or mismatch stops, without weakening budgets or retrying.

No physical result from older Linux, Windows or ordinary PHP-matrix CI substitutes
for those stages. No native test, Database Review, transaction proof, production
readiness or whole-plugin pre-release row is completed/refreshed by this design.
The full pre-release workflow remains reserved for a coherent release candidate.

## Plan position, validation and publication

Implementation-plan progress: one finite Linux minimum candidate is now identified
with primary metadata/source context, and the exact lab-contract mismatch is
isolated. Genuine minimum-profile native qualification remains open; Windows
observer work stays deferred, and production/form wiring stays disconnected.
Historical cumulative safe-local count remains 1/8 plus separately approved
proof work; this design is not product or physical qualification completion.

Only this design, the owning plan and reassessment forward pointer change.
Uploader/toolkit remain read-only at `3dfcb15`/`c067eb6`; no review checklist update.
No source/guard/test/workflow or artifact pin changes are authorized.

Executed ordinary repository QA with the trusted existing PHP 8.5.1, not the
candidate or genuine core/native environment:

- Focused minimum/supported/transport/Linux/observer/count PHP checks:
  `php vendor/bin/phpunit --do-not-cache-result --filter
  'SourcePolicyMinimum|SourcePolicySupportedProfile|SourcePolicyTransportProfile|SourcePolicyLinux|SourcePolicyToolCountCalibrationTest|SourcePolicyToolObserver'`:
  393 tests / 12,446 assertions, no failures/skips.
- Existing pure PowerShell snapshot/observer/count suites: respectively
  304 / 11, 435 / six, 339 / ten checks / assertion-killed controls; zero proof
  processes/fixtures, source bytes preserved. No deferred physical run reopened.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,325 tests /
  21,007 assertions, no failures; two existing Sodium-unavailable signing skips.
- `npm run lint`: PHPCS 243 files; `npm run test:admin`: 23/23, no failures/skips;
  `npm run check:structure`: 149 runtime / 233 PHP support / 224 test files /
  221 class names; `npm run build`: passed, no tracked asset delta.
- Strict Composer validation, Composer audit and npm moderate audit passed,
  zero known dependency advisories/vulnerabilities. These audits do not inspect
  or clear the proposed legacy donor image. No dependencies installed/changed.
- 157 owning local documentation targets and whitespace checks passed. An ad hoc
  required-prose check initially mismatched wrapped/cased Markdown; correcting
  only that checker passed, without changing guards or acceptance. Final scoped
  source/provenance, error/resource/cleanup, compatibility and proof-limit review
  confirms only three docs changed against baseline; no executable/pin changes.

Exact-head publication/CI evidence follows after push. These ordinary checks
cannot establish artifact availability beyond metadata, native behavior or trust.
