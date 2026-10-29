# Socket-Free Dependency/Launch and Preflight Transcript Safety Foundation

## Authority, baseline and outcome — 2026-10-29

Approved local-only implementation of the next synthetic gate in the
[runtime-preflight integration design](SOURCE_POLICY_MINIMUM_RUNTIME_PREFLIGHT_INTEGRATION_DESIGN.md).
Clean/synced restore baseline `a686b433114673dd83d5ad0d67ec22763a04b1ea`.
R2: bounded development-only consistency code and isolated tests. No artifact
acquisition, installed profile/source/runtime discovery, filesystem/network/
process adapter, candidate-runtime/module loading, WordPress bootstrap,
authentication, SQL, fixtures on a site/database, permission/credential/schedule
change, installation, native experiment, packaging/release/tag or deployment.
No site is selected/touched and no Site Operations target confirmation or
Novamira call is appropriate for this repository-only slice.

Two new helpers are implemented and have **tests as their only consumers**.
They accept primitive supplied DATA, not genuine observations or authenticated
evidence. They neither mint a load/native permission nor hide a launcher behind
an unavailable factory. Successful paths return synthetic consistency/completion
with execution/load_authorized/native_qualified=false. Platform/filesystem flags
also remain false where exposed. Runtime booleans are consistency predicates,
never permission checks. Nothing is wired to production/bootstrap or existing
native workers/controllers. Version0.1.77, schema8, support/source/artifact pins,
TLS/downloader/archive/tree/PE/ownership policy, ADBM0 and ADBC1/2 are unchanged.
Uploader/toolkit are read-only; no whole-plugin readiness/Database Review refresh.

## Dependency/layout/profile DATA

`scripts/manual/source-policy-minimum-launch-data.php` is one cohesive final class:

- `inspect()` accepts only the exact eight-key layout: run, root, platform,
  artifacts, files, cwd_entries, environment, dependencies. All type/count/domain
  guards precede indexing, iteration or hashing. The supplied Windows path must
  be bounded, portable, absolute and end in its exact synthetic run marker;
  traversal/ADS/reserved names and an owned root inside the platform root refuse.
  This is string consistency, not realpath/reparse/ownership verification.
- The platform profile requires exact canonical Windows/System32 relations,
  bounded build syntax and a reviewed-platform-profile **claim**. The fixture's
  `10.0.12345` is intentionally fictional, not this machine's observed build.
  Caller labels/hash strings cannot verify Windows or installed CRT provenance.
- Preserve the fixed publisher ZIP/core-tree/12-source-pin expectations. The
  three exact application hash comparison references come from the separately
  authenticated [owned artifact proof](SOURCE_POLICY_MINIMUM_IMPORT_CLASSIFICATION_OWNED_PROOF.md),
  not a new publisher-pin update. File roles are exactly php.exe, php7.dll,
  ext/php_mysqli.dll and two synthetic worker.php/producer.php digest claims.
  Runtime roles are implicitly below `<root>/runtime/`; worker/producer are fixed
  relative roles, and CWD/TEMP/TMP are private work/tmp children. No arbitrary
  executable, DLL, source, search-path or environment override is returned.
- The supplied CWD entry list must be empty. Environment is exactly SystemRoot,
  WINDIR, PATH, TEMP, TMP with the reviewed profile/root relations: no inherited
  PHPRC, scan-dir, user PATH, extra secret/agent variable or case-alias key.
  No environment is actually read, cleared or set by the helper.
- Dependency DATA is the exact22 observed external names and their platform/CRT
  class claims; imagehlp lookalikes, unknown/API variants, missing names or
  hash-only CRT provenance refuse. This does not resolve an API set or a DLL.
  The frozen source/artifact/layout/environment/dependency claims contribute to
  a deterministic manifest correlation digest; worker/producer claims separately
  produce a source digest. Neither self-recorded digest establishes provenance.
- `runtime_template()` specifies base/mysqli expectations; `runtime_data()`
  compares exact types/keys/values. Windows/CLI/PHP7.4.33/int8/NTS, absent loaded/
  scanned INI/prepend/append, two frozen source roles and a compiled-base manifest
  claim are mandatory. Base requires mysqli absent; mysqli requires raw
  `mysqlnd 7.4.33`, separate internal function/class origin claims and integer0
  reporting mode before/after. There is no reporting-mode setter or reflection/
  module call. Compiled-module/source hashes are supplied claims, not discovery.
- `module_data()` bounds expected and observed claims independently to128 rows,
  32MiB each/256MiB total, rejecting duplicates, malformed types/paths, missing
  stage roles and application hash/path changes before canonical comparison.
  Base requires two fixed application modules; mysqli requires three. Kernel32
  and installed-CRT claims remain mandatory. Other declared platform modules
  must match their exact supplied Windows System32/WinSxS profile rows; names
  cannot smuggle an application role into that class. Unknown observed rows,
  partial sets or any digest/path/size/origin mismatch refuse. Matching forged
  platform labels still yield platform_verified=false: this models consistency
  of a supplied finite profile, **not authentication of an OS module**.

The later physical adapter must privately establish real artifact/host ownership
and provenance before using these shapes; no public function here establishes it.
The comparison model does not inventory Windows, parse a PE, read a file, resolve
an alias, verify a signature/catalog or call an arbitrary provider.

## Versioned wire and sticky ordered state

`scripts/manual/source-policy-minimum-preflight-transcript.php` implements a
separate producer/validator, leaving ADBM0's no-execution contract untouched.
Constructor inputs are strict run32hex/image64hex, two distinct bounded integer
PID/start-identity claims, source/base digest claims and stage module digest/count
claims. They are correlations, not live Process handles or authenticated facts.

`frame()` produces only finite payloads in the exact envelope:

```text
ADBMP1|run32|image64|base-or-mysqli|pid|sequence|kind|fixed-fields\n
ADBMP1-ACK|run32|image64|base-or-mysqli|pid|5\n
```

Each stage has eight exact events, with separate worker/controller/stdin methods:

| Sequence | Source | Kind | Exact payload contract |
| --- | --- | --- | --- |
| 1 | worker | start | Canonical start tick and fixed php.exe digest. |
| 2 | worker | profile | Fifteen fixed fields: Windows, cli,7.4.33,8,nts, four none values for INI/scanned/prepend/append, source and base digests, then stage-specific mysqlnd/internal/before-mode/after-mode values (absent in base). |
| 3 | worker | held | await-observer. |
| 4 | controller | modules | Exact stage module digest and canonical bounded count. |
| 5 | stdin | ACK | Sole correlated release response; no command/source payload. |
| 6 | worker | released | acknowledged. |
| 7 | controller | exit | 0 and drained. A worker exit claim cannot satisfy it. |
| 8 | controller | cleanup | process-absent, unchanged, then retained-owned in base/artifacts-absent in mysqli. |

Base must complete before mysqli starts. Source/run/image/PID/start/sequence/
payload/channel comparisons are exact; no type coercion, JSON, deserialization,
callable, arbitrary field or normalization fallback. Successful completion needs
all16 events including final controller cleanup. EOF or a held/exit claim alone
cannot finish. Invalid, stale, replayed, reordered, wrong-channel, CRLF/extra/
partial/overlong input latches the first safe failure; later correct frames
cannot reopen it, including after a previously completed synthetic sequence.

Frame length512, aggregate protocol input16KiB, and virtual elapsed strictly
below25,000ms are enforced. The finite16-event state also implies the design's
64-frame ceiling; aggregate accounting conservatively includes all channels,
not just stdout. No real clock or stream is accessed. Any nonempty stderr claim
is discarded and refuses; count saturates at4097 as an explicit overflow sentinel,
never a claim of the exact larger count. Raw frames/errors/paths are not retained
in state or summaries. Execution/load/native flags stay false on every outcome.

Separate public methods model source ownership but cannot authenticate callers;
someone able to supply all controller claims can synthesize completion. The later
private physical adapter, real monotonic timer, module observer, drained pipes,
process-object cleanup and independent absence checks remain unimplemented/gated.
No synthetic terminal changes any historical dependency-closure/unverified flag.

## Tests and source-scoped review

Initial new tests were red because the helper files were absent. The final tests
exercise actual helpers, real positive consistency/completion, and paired hostile
inputs rather than only expecting unavailable. The layout digest/source summary
and module summaries compose with the new wire in a positive synthetic test.
Fake internal origins, labels, type/identity/case/path/DLL/API/mode/INI/environment
changes, resource excesses, every event's order/channel/correlation, expiry/time
types, early EOF/exit, stderr overflow and missing cleanup are covered.

Twelve **in-memory** semantic controls are assertion-killed: path, dependency,
environment, role digest, runtime comparison, module set/path, frame equality,
channel separation, clock expiry, final cleanup/completion and stderr refusal.
Baseline passes in existing trusted PHP_BINARY `-n` with JSON disabled and no
mysqli/PDO-MySQL; mutants change one exact checked source expression in memory,
never on disk. Repository source SHA256s are compared after each child. These
are isolated PHP7.4-compatible source contracts, not physical Windows/process,
signature, runtime, authentication or database evidence. No claim is made that
each redundant numeric ceiling has an independent killed mutant; exact finite
state and payload equality also bound successful transcripts.

A token-level closed-call guard checks both actual sources: only whitelisted
pure primitives, their fixed class methods, and the existing pure path/catalog
functions. Launch DATA has one fixed pure-catalog declaration include; the wire
producer/validator is self-contained with **no includes or Launch_Data dependency**.
Composition review caught and removed the initial extra include chain instead
of widening the design's worker/producer closure. An actual standalone trusted
PHP `-n` test completes all16 events with only the transcript file included,
JSON disabled and no catalog/Launch_Data/mysqli loaded. This is source composition
evidence, not a launched legacy worker. No globals/request/environment data,
dynamic callable/class/receiver, IO/launcher/discovery or artifact/native helper
include. No arbitrary class call may hide behind an allowed method name.
This guard plus complete diff/caller/compatibility/resource/error/test review
protects the current closed source; it is not a formal PHP sandbox proof.
PHPCompatibilityWP/ordinary CI remain separate from actual legacy runtime proof.
No UI/UX or whole-plugin release/Database Review workflow is claimed.

## Verification, publication and next gate

Fresh related family before full QA: `php vendor/bin/phpunit --do-not-cache-result
--filter SourcePolicyMinimum`:157 tests/7,233 assertions, no failures/skips.
Changed-source PHPCS/PHPCompatibilityWP, structure and whitespace checks passed.

Final post-composition repository QA on existing PHP8.5.1:

- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,200 tests/
  16,441 assertions, no failures; only the two existing missing-Sodium invalid-key
  and signature-roundtrip skips. The refreshed minimum family157/7,233 has no skips.
- `npm run lint`:242 files pass. Admin23/23/zero failures/skips; structure149
  runtime/231 support/219 test files/216 class names and asset build pass.
  No generated tracked delta or package was created.
- Strict Composer validation, Composer audit and npm moderate audit pass,
  zero known advisories/vulnerabilities; no dependency install/update.
- 138 owning local links, final source/consumer/security/compatibility/error/
  test-integrity review, protected-source and whitespace checks pass. Existing
  runtime/bootstrap/qualified native workers, all source/artifact pins, downloader/
  archive/static/PE/tree/ownership helpers, manifests/locks/workflows and assets
  remain identical toa686b43. Only new dev helpers/tests and owning records change;
  uploader/toolkit remain clean and untouched at3dfcb15/c067eb6.

Implementation publication `2faf5bfbff11372c6130c69c3145673613bc1462` is pushed;
ordinary [CI40925149833](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40925149833)
succeeded for that exact head. Actual PHP7.4 log:2,200 tests/16,443 assertions;
PHP8.3:2,200/16,448, both without failures/skips. Admin23/23 each, zero failures/
skips; manifest/audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. This evidence-only closeout receives its own pushed exact-head
ordinary CI and clean/remote verification at handoff. No new test is skipped or
existing assertion weakened; no manual/native workflow is dispatched.
No artifact/site/runtime/native proof is replayed for this evidence closeout.

Implementation-plan progress: the closed synthetic dependency/layout/runtime/wire
foundation is implemented, not the host provenance audit or physical preflight.
Recommended next gated slice: **Read-Only Minimum-Profile Windows/VC Dependency
and Observer Feasibility Audit**. Inspect only non-secret existing host/runtime
metadata, exact Windows/CRT paths/provenance and bounded existing observer methods;
no downloads, installation/configuration/permission changes, authentication/SQL,
site inspection, artifact/runtime loading, native experiment or production wiring.
Unknown provenance or unsupported no-network verification must be reported before
any later owned process prerequisite or candidate-runtime launch approval.
