# Socket-Free Linux Minimum-Lab Tuple DATA Safety Foundation

## Authority and result — 2026-10-30

Approved repository-only implementation of the
[tuple/source-ownership contract design](SOURCE_POLICY_LINUX_MINIMUM_TUPLE_ADMISSION_DESIGN.md)
at clean/synced baseline `2d1de8378bff735045b42859614bd09a1b1dae84`.
R2 bounded development DATA/tests only. Ordinary trusted repository QA,
commit/push and exact-head CI are in scope. No runtime receiver integration,
downloads, artifact inspection, provisioning, candidate/core/native execution,
containers, workflow dispatch, SQL/authentication, site/LocalWP/live operations,
credentials/permissions, schedules, release/package/deploy or production wiring.
Site Operations was consulted; no site target/Novamira operation applies.

Implemented [one development-only helper](../scripts/manual/source-policy-linux-minimum-tuple.php),
not imported by the plugin bootstrap or any runtime include. It implements the
17-key `ADBLMT1` expected candidate descriptor and six-key bounded parent
correlation contract. `template()` returns only expected DATA; `consistent()`
compares every field/key/type strictly; `digest()` requires consistency then
hashes the canonical source-defined order; `correlates()` checks bounded frame
shape/scalars, the fixed tuple hash and every independent parent identity.
Key ordering may differ on input without changing the canonical digest. No
coercion, callback, object conversion, lookup, connection, provider, scope mint,
ready/native/dispatch flag or actual artifact/source verification is provided.

Actual core-6.0 + proposed MariaDB build remains refused by legacy profile
admission, now explicitly pinned in
[the existing adapter contract tests](../tests/SourcePolicyNativeAdapterContractTest.php).
No `includes`, bootstrap, factories, source/artifact pins, runtime API, manifests,
workflow, ADBC1/ADBC2 contract or support declaration changes. The candidate's
binary/config/dependency facts remain unknown, Windows observer work deferred,
and minimum native qualification/production connection and form wiring gated.

## Non-vacuous tests and corrections

- [Independent fixture and positive/rejection tests](../tests/SourcePolicyLinuxMinimumTupleTest.php)
  verify the exact row, canonical hashing/order independence, fresh template
  copies, complete public DATA-only method set and existing core commit/pin
  preservation. Every tuple field is changed independently; missing/extra keys,
  suffixes/NULs, huge/wrong-type values, nullable-key substitution and hostile
  stringable/callable objects reject without execution/conversion.
- Parent transcript tests cover exact head/image/run/PID/hash/event, independent
  mismatches on each side, complete shapes, lowercase hex/length, bounded strict
  integer PID and boundary values. Matching synthetic parent claims may still
  correlate; they are explicitly **not** source authentication or qualification.
  No unknown binary hashes or fake native success path is introduced.
- [Extension-free controls](../tests/SourcePolicyLinuxMinimumIsolationTest.php)
  execute actual helper source in existing trusted PHP `-n`, with URL reads off,
  only the two source/fixture directories allowed and network/process-launch
  functions disabled inside the child. The worker confirms those restrictions
  and absence of mysqli/pdo_mysql. Its parent launches only these owned pure
  test processes, not a candidate/runtime proof or site process.
- One unchanged positive/negative contract passes; **38 semantic controls are
  assertion-killed**, including always-unavailable, all 17 field guards, exact
  shape/nullable-key presence, digest validity, both frame validation gates,
  event/hash/PID, per-hex-field length/alphabet and four parent-identity guards.
  Only trusted test-memory source is mutated/evaluated. Unexpected diagnostics,
  anchor mismatch, syntax errors or crashes fail the test and do not count as
  assertion kills. SHA256 confirms actual helper bytes unchanged after every
  case; no on-disk source rewrite or native/storage fixture cleanup is needed.
- Token-based call-surface checks reject IO/loaders/launchers/dynamic invocation
  and verify the helper is absent from production imports. This is bounded
  static/isolated evidence, not a sandbox or protection against malicious PHP.

Red/green evidence: the independent positive test first failed because the
approved helper did not exist, then passed after implementation. A targeted
PHPCS array-layout error was corrected without changing behavior. Initial
control runs failed at startup because unquoted Windows `open_basedir` INI
values treat the semicolon as a comment, omitting the second directory. Quoting
the INI value itself fixed the harness without broadening its two allowed
roots or disabled-function list. The green worker explicitly checks the parsed
restrictions. No production guard/budget/acceptance change resolved those errors.

## Proof limits and next gated slice

These tests establish expected DATA consistency, parent correlation and effective
rejection tests only. They do not establish distribution provenance, actual
source-image bytes, OCI volumes/config, build/extension identity, authentication,
native/session/database behavior, transaction/release evidence, physical cleanup
or supported production connectivity. Private receiver scope and all three
runtime admission integration sites remain unimplemented/disabled. Earlier
physical receipts and whole-plugin review rows are not refreshed by unit CI.

Implementation-plan progress: the first executable minimum-lab DATA contract is
implemented with independently positive and guard-removal coverage. The next
meaningful gate is actual artifact evidence, not Windows observer maintenance
or more generic cleanup. This is not product/native qualification completion;
the historical 1/8 count is not relabeled as a completed autonomous batch.

Recommended next gated slice: **Linux Minimum-Lab Digest-Pinned Artifact
Acquisition, Static Verification and Owned Cleanup Proof**, audit-first and
without runtime loading. Approve bounded owned acquisition/static inspection
of only the selected official runtime platform image, pinned server image and
genuine core tree, with exact staging/cleanup scope and finite byte/file/time
limits fixed before payload retrieval. Abort if source/identity/trust/resource
prerequisites cannot be met; no mutable-tag fallback, Docker image loading,
installation, extraction execution, candidate/core loading or native/database
operation. That new download/file-effect authority is outside this foundation.
Connection-free execution and native proof retain separate later approvals.

## Validation and publication

### Foundation publication and approved compatibility recovery

Implementation `78f2ee4f301116173c09ad254acb07fd9b886b8f` was pushed;
[ordinary CI 41134455755](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41134455755)
failed on PHP 7.4: 2,435 tests / 22,597 assertions, 39 control-runner failures,
each unexpected exit 2 rather than positive 0/assertion-killed 1. PHP 8.3 passed
2,435 / 22,719, admin 23/23, no failures/skips. Those original child diagnostics
did not identify their inner exception; no native/artifact gate was closed.

The separately approved repository-only recovery starts at that clean/synced
failed head. Added bounded allowlisted phase/exception-kind/JSON-capability
facts, never raw exception text, paths, SQL or credentials. With JSON functions
explicitly disabled, the unchanged path reproduced exit 2 at `fixture`, kind
`Error`, JSON encode/decode availability 0, before any semantic assertion.
This establishes the missing-JSON failure path, not a retrospective capture
of the original runner's unrecorded exception. [PHP's installation documentation](https://www.php.net/manual/en/json.installation.php)
distinguishes always-enabled JSON in PHP 8 from its optional pre-8 build;
the extension-free PHP 7.4 child cannot assume that API is available.

The repair removes that dependency from the development helper/fixture, not
the isolation: the same two allowed directories, `-n`, URL-off and all ten
network/process-function restrictions remain, with JSON encode/decode now
disabled as well in **every** semantic control. No extension is loaded, test
skipped or acceptance loosened. The private fixed-row encoder handles only the
source-owned ASCII strings/integers/null, rejects unsafe future source edits,
and preserves exact canonical JSON bytes and the existing descriptor digest
`df9040cde903f45639faa5bf7e6db5dd3ed97bf6d5616f54cd39e4e3ff5c6c05`.
It is not a general JSON encoder or a new input API. An independent normal-JSON
reference and golden fixture digest verify byte equality; public DATA methods,
format/keys/pins, validators, runtime imports and legacy contracts are unchanged.

All 38 original assertion-killed controls remain, now JSON-disabled. Two further
in-memory assertion-killed encoder guard bypasses verify key/value restrictions;
unsafe quote/backslash/newline/overlong/bool/array source variants refuse. The
actual file hashes are preserved throughout. Diagnostic tests enforce bounded
allowlisted output and exclude supplied exception messages. No artifacts,
candidate/runtime proof, native/session/database or production operation occurs.

Fresh recovery QA with trusted existing PHP 8.5.1:

- Focused minimum/adapter/transport checks: 195 tests / 2,340 assertions, no
  failures/skips; targeted helper PHPCS passes.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,438 tests /
  22,937 assertions, no failures; two existing Sodium-unavailable signing skips.
- PHPCS 244 files; admin 23/23, no failures/skips; structure 149 runtime /
  235 PHP support / 226 test files / 223 class names; asset build passed with
  no tracked generated changes. Strict Composer validation, Composer audit and
  npm moderate audit passed, zero known dependency advisories/vulnerabilities.
- 151 owning local documentation targets, whitespace and final scoped
  source/security/resource/error/compatibility/test-integrity review passed.
  Only the development helper, four affected test files and two owning docs
  change; runtime/bootstrap, existing pins/manifests, workflows/runners and
  dependencies remain byte-unchanged. Uploader/toolkit remain clean/read-only.

Exact-head recovery publication follows after push; genuine PHP 7.4 native
qualification is not established by these ordinary checks.

### Historical foundation local QA (before recovery)

Focused checks executed with existing trusted PHP 8.5.1:
`php vendor/bin/phpunit --do-not-cache-result --filter
'SourcePolicyLinuxMinimum|SourcePolicyNativeAdapterContract|SourcePolicyTransportProfile'`:
192 tests / 2,115 assertions, no failures/skips. Targeted helper PHPCS and the
structure guard pass. Executed full ordinary repository QA:

- `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,435 tests /
  22,712 assertions, no failures; two existing Sodium-unavailable signing skips.
- `npm run lint`: PHPCS 244 files; admin tests 23/23, no failures/skips;
  structure: 149 runtime / 235 PHP support / 226 test files / 223 class names.
  Asset build passed without tracked generated changes.
- Strict Composer validation, Composer audit and npm moderate audit passed;
  zero known dependency advisories/vulnerabilities, no dependency changes.
- 164 owning local documentation targets, whitespace and final scoped
  source/security/resource/compatibility/error/test-integrity review passed.
  Runtime/bootstrap, manifests/pins, workflows/CI runners and dependencies
  are byte-unchanged against baseline. Only the one development helper,
  five new/affected test files and three owning documentation files change.

Ordinary QA is never a minimum-runtime, artifact-provenance or physical-source-
ownership qualification; the failed publication and approved recovery above
supersede the former pending-CI handoff, not any historical physical evidence.
Uploader/toolkit remain read-only; no toolkit checklist or whole-plugin
pre-release row changes. Full pre-release readiness is still pending.
