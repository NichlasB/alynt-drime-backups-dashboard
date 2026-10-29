# Minimum-Profile Native-CA Integration and Owned Acquisition Attempt

Forward progress: the approved [core host/mode repair and proof](SOURCE_POLICY_MINIMUM_CORE_MODES_OWNED_PROOF.md)
passed authenticated core mode/blob decoding, then stopped at the unchanged later
import-classification guard with exact cleanup. Follow that record's resume gate;
this historical host/mode refusal is not rewritten as complete qualification.

## Authority, restore baseline and outcome — 2026-10-29

Explicitly approved R3 harness-only Windows Native-CA Trust Integration and one
fresh owned acquisition/cleanup attempt. Clean/synced dashboard restore point:
`6aef5b231d41c3607129fc0ae2132f2829f47f11`. Follows the
[prior TLS refusal](SOURCE_POLICY_MINIMUM_ARTIFACT_ACQUISITION_OWNED_PROOF.md)
and preserves the [fixed artifact/runner design](SOURCE_POLICY_MINIMUM_ARTIFACT_RUNNER_DESIGN.md).

**TLS recovery succeeded; the new attempt was then refused at core archive host/
mode admission. Cleanup confirmed. Complete artifact qualification is NOT done.**
No installation, downloaded runtime/module/core execution or loading, WordPress
bootstrap, site/database/LocalWP/live operation, SQL, credentials, permissions on
existing state, global INI/environment/CA-store changes, schedules, production/
form wiring, native guards/budgets, schema/version, source pins, dependencies,
ADBC1/ADBC2, packaging, tag, release or deployment changes. The existing trusted
PHP8.5.1 CLI is tooling, not the acquired PHP7.4 candidate. No acquisition retry
or native database experiment took place. Uploader/toolkit remain untouched.

## Small implementation boundary and review

Only `scripts/manual/source-policy-minimum-acquire.php` changes executable code:

- Pure `adbmin_native_ca_profile` recognizes exactly the reviewed Windows/
  cURL8.17.0/OpenSSL3.5.4 profile and boolean native-CA option availability. No
  implicit casting, alternative backend, version discovery or missing-option
  fallback. Actual observed facts are checked before curl_init/connection.
- Each of the two fixed requests sets only `CURLSSLOPT_NATIVE_CA` through
  CURLOPT_SSL_OPTIONS. The [official cURL contract](https://curl.se/libcurl/c/CURLOPT_SSL_OPTIONS.html)
  uses the OS certificate store for verification with this Windows/OpenSSL
  backend; other configured/build-time CA locations are not disabled. No CA
  bundle is installed and no client certificate is automatically selected.
- Existing HTTPS-only restriction, peer=true/hostname=2, no redirects/proxy/netrc
  authentication/fallback/retry, response/effective-URL checks, publisher SHA256,
  immutable core/tree/source pins, all size/time/memory limits, exclusive ownership
  and finally-cleanup remain unchanged. No revocation-disable, best-effort
  revocation, BEAST allowance, CAINFO override or automatic-client-cert option.
- Receipt adds observed SSL backend and the fixed per-request trust-source label.
  No certificate bodies, trust-store contents, secrets or raw server diagnostics
  are recorded. No new production/native-worker/bootstrap caller is added.

`tests/SourcePolicyMinimumNativeCaTest.php` adds exact positive/negative profile
DATA, actual source guard-before-handle/TLS option checks and four in-memory
guard-removal controls. The same positive/refusal contract assertion-kills removal
of OS, cURL version, TLS backend or native-capability checks; source SHA256 is
unchanged before/after each control. Baseline passes in an existing PHP `-n`
child. Children do not call cURL or acquire artifacts; this is isolated policy
evidence, not a simulated TLS success or a candidate-runtime proof.

Source-scoped review checks only the changed request/policy boundary and directly
affected tests/records: expected versus observed trust source, strict versions,
unchanged TLS/identity guards, no fallback/auto-authentication, cleanup order and
PHP7.4 compatibility. No UI/UX, whole-plugin pre-release/Database Review,
authentication or physical native-transaction readiness is claimed/refreshed.

## Fresh prerequisites and the one new attempt

Fresh minimum family: **81 tests/1,899 assertions**, no failures/skips. New policy
class:7 tests/51 assertions, four controls assertion-killed, baseline passed.
Changed-source PHPCS/PHPCompatibilityWP, structural check and scoped whitespace/
security review passed before any acquisition. The focused red test first
confirmed the policy implementation was absent; a preliminary test-code syntax
error was corrected before recording that meaningful red state.

Single real invocation:

```text
php -d memory_limit=768M scripts/manual/source-policy-minimum-acquire.php --confirmed-owned-minimum-artifacts
```

Observed tooling: PHP8.5.1, libzip1.11.2, cURL8.17.0, OpenSSL3.5.4;
`tls_trust:windows-native-ca-per-request`, child memory limit768MiB.
New exact temporary child: `alynt-minimum-b1a860dfc8ce47dad7cf9c277510bb8f`.
Trusted source/tool image SHA256:
`33cdc646300cda3e3f27c79f9ca831304f44f5e0e036a03b0390737a3ce33921`.

- Both fixed requests passed the still-enabled TLS/hostname/HTTPS checks and
  exact HTTP/effective-URL checks. The previous cURL60 boundary was passed.
- PHP ZIP:26,132,214 bytes; SHA256
  `14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace`,
  exactly the unchanged publisher pin. Read-only libzip consistency and complete
  inventory/CRC/actual-length decoding passed:93 entries,70,083,558 expanded bytes
  in memory; `extraction:false`. This establishes archive byte identity/decoding,
  not PE/import/dependency/NTS/loader or native PHP7.4 runtime qualification.
- Core request completed and its owned ZIP reached read-only libzip parsing,
  then refused at stage `archive-core`, failure `core-file-mode-refused`, code0.
  The unchanged guard rejects a core entry whose ZIP host code is not Unix3,
  after earlier supported-host/type checks. The receipt did **not** capture entry
  index/name, directory-versus-file, exact host/mode, core ZIP digest or counts.
  Do not infer which entry triggered it or label it a harmless directory yet.
- Whole attempt: status refused, elapsed4,701ms, runtime_loading=false,
  site_database_operations=false, cleanup confirmed. Core complete-tree/per-file
  pins, selected PE/import checks and all real output extraction/readback were
  not reached. No guard, source-mode expectation or pin was relaxed after refusal.

The owned marker and both temporary ZIPs were removed with their exact child;
an independent read-only exact-path check confirmed the child absent. Nothing
material is retained or recoverable; only non-secret receipt facts are recorded.
No runtime/source output directories were extracted. No subsequent network probe,
archive re-acquisition, global TLS change or physical experiment was run.

## Publication and remaining gate

Post-attempt ordinary repository QA and exact-head CI are recorded after their
execution below. Green isolated CI does not complete core/archive/runtime proof.
Existing pure helpers, archive/ownership/static adapters, source pins, native
controllers, runtime, configuration/dependencies and package workflows stay
byte-identical to6aef5b2; only the harness request file, new tests and records change.

Post-attempt source review also made cURL option setup's boolean return mandatory:
an options-setup refusal throws before curl_exec. A fresh no-cURL PHP `-n` child
evaluates the actual extracted setup/guard block with a stubbed setter, accepting
true and rejecting false. No network is used by that test. Latest focused family:
82 tests/1,905 assertions, no failures/skips; changed-source lint clean. This
strengthens configuration admission without changing any option, pin or budget.
It changes the harness image relative to the historical4,701ms receipt above;
there is no new physical acquisition result for the latest image. Complete
post-hardening QA and exact-head publication verification follow at handoff.

Executed final repository QA on existing PHP8.5.1:

- `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,125 tests/11,113
  assertions, no failures; only the two existing missing-Sodium invalid-key and
  signature-roundtrip skips. No added skip or weakened existing assertion.
- Latest minimum family82/1,905, no failures/skips; four backend guard controls
  assertion-killed and option-setter true/false contract passed without cURL.
- PHPCS237 files/changed-source PHPCompatibilityWP, admin23/23/no skips,
  structure149 runtime/229 support/214 tests/211 class names, build, strict
  Composer manifest validation and both dependency audits pass. Zero known
  advisories/vulnerabilities; no install/update or generated tracked delta.
- Owning links, scope/TLS/identity/error/cleanup/compatibility diff review and
  whitespace checks pass. Protected source/configuration and artifact pins are
  byte-identical to6aef5b2; uploader/toolkit are clean and untouched. Exact child
  absence was independently confirmed. No physical acquisition is repeated.

Publication `78eb11f082216af032646488eecc010e8293a4e4` is pushed;
ordinary [CI40899707594](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40899707594)
succeeded at that exact head. Actual PHP7.4 log:2,125 tests/11,115 assertions;
PHP8.3:2,125/11,120, each without failures/skips. Admin23/23/no failures/skips on
each job; manifest/audits/build/structure/PHPCS passed with zero known advisories/
vulnerabilities. Existing Node setup/cleanup DEP0040/DEP0169 warnings are nonfatal;
no dependency update is included. This docs-only evidence closeout receives its
own pushed exact-head ordinary CI and clean/remote verification at handoff.
No physical acquisition or native operation is repeated. Ordinary CI does not
retroactively qualify the refused core archive or any candidate runtime.

Implementation-plan progress: explicit Windows-native trust and the PHP publisher
ZIP integrity/inventory boundary advanced; the remaining core host/mode refusal
is the next gated qualification decision, not a successful complete acquisition.
Recommended next gated slice: **Harness-only Core ZIP Host/Mode Diagnostic and
Evidence-Based Compatibility Repair with One Fresh Owned Acquisition/Cleanup
Proof**. First obtain bounded non-secret host/mode/kind evidence; correct only a
representation distinction supported by that evidence and synthetic regressions.
Do not assume or default regular-file Git modes, bypass link/type checks, change
pins or weaken complete-tree verification. Require fresh prerequisites and exact
owned cleanup; preserve TLS/native-CA checks and all no-install/no-runtime-loading/
no-site/database boundaries. No further acquisition is authorized by this record.
