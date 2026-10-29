# Minimum-Profile Artifact Acquisition — Adapter and Refused Owned Attempt

## Authority, baseline and outcome — 2026-10-29

Explicit approval of Owned Minimum-Profile Artifact Acquisition, Static
Verification and Cleanup Proof, disposable artifacts only. R3 scope: fixed
HTTPS reads, new exact owned temporary files, static inspection and exact cleanup.
Clean/synced restore baseline `d8cbfbd38a41a95c9c874317b5ad1763fa8749cd`.
Implements the next adapter gate from the [design](SOURCE_POLICY_MINIMUM_ARTIFACT_RUNNER_DESIGN.md)
and [synthetic foundation](SOURCE_POLICY_MINIMUM_ARTIFACT_RUNNER_FOUNDATION.md).

**Outcome: adapter/prerequisites complete; real acquisition proof refused at the
first TLS connection. Cleanup confirmed. Minimum artifacts are NOT qualified.**
No installation, downloaded runtime/module/core loading, WordPress bootstrap,
site/database/LocalWP/live operation, SQL, credentials, permissions on existing
state, schedules, production/form wiring, runtime guards, schema/version,
dependencies, source pins, ADBC1/ADBC2, packaging, tag, release or deployment change.
Existing trusted development PHP is used as tooling, not the downloaded candidate.
No native database experiment or acquisition retry took place.

## Adapter and source-scoped review

Four development-only sources; no production or existing native-controller caller:

- `source-policy-minimum-owned.php`: generates a new
  `alynt-minimum-<32hex>` child beneath the resolved temporary parent, exclusive
  binary writes, bounded reads, exact created-file/directory tracking, root
  identity/realpath/link/marker checks and non-recursive tracked cleanup.
  Cleanup preflights the complete owned inventory before deleting anything,
  refuses unknown files/changed markers and rechecks paths before exact unlink/
  rmdir operations. It never deletes a temp parent, repo or site root. No stronger
  protection against a hostile same-user/operator or concurrent replacement is
  claimed; no protected staging/account/ACL/SDK work is reopened.
- `source-policy-minimum-archive.php`: uses already installed libzip
  [read-only and consistency flags](https://libzip.org/documentation/zip_open/),
  [external attributes](https://www.php.net/manual/en/ziparchive.getexternalattributesindex.php)
  and [bounded streams](https://www.php.net/manual/en/ziparchive.getstream.php).
  The fixed publisher SHA256 is checked before the PHP ZIP is opened. Complete
  metadata/type/path/collision/count/size validation precedes expansion; encryption,
  unsupported methods/host types, link/special modes and unsafe wrappers are
  refused. CRC/actual length are checked after bounded per-file reads. libzip,
  not a new custom ZIP parser, owns raw-format consistency; no extractTo API,
  archive permissions, link restoration or metadata-driven path alias is used.
- `source-policy-minimum-static.php`: complete fixed core-tree/per-file pins
  and the existing selected-role PE parser before manual regular-file extraction.
  Tracks selected image/direct-import hashes, permits only package-local or
  explicitly listed OS/CRT direct dependency names, and always leaves runtime
  loading/dependency-closure qualification false. Presence, transitive closure,
  NTS/runtime behavior and actual Windows loader compatibility are later gates.
  Extraction writes exclusively beneath runtime/source children and compares
  each readback digest. This path was exercised with synthetic owned payloads,
  **not reached by the refused real attempt**.
- `source-policy-minimum-acquire.php`: the exact two catalog URLs only;
  HTTPS-only option (modern string or legacy bitmask), peer/hostname verification,
  no redirects/proxies/netrc authentication/cookies/fallback/retry. 64MiB body
  ceiling,10-second connect/120-second request/30-second low-speed limits and
  outer10-minute provisioning ceiling; child-only768MiB memory limit. Fixed
  Windows/current development PHP preflight; no arbitrary executable or URL
  argument. Source/tool byte freeze, fixed stage/error-code diagnostics and
  finally-cleanup. Direct CLI invocation also requires the exact confirmation
  switch; including the source for tests starts no network or acquisition.

An auto-review rejected a proposed compatibility edit that removed the explicit
HTTPS restriction. It was not applied; the implemented conditional retains that
restriction. Peer/hostname verification, identity pins and refusal behavior were
never weakened. No TLS bypass or configuration mutation followed the failure.

The existing pure minimum-profile helpers and prior native-source inputs are unchanged;
new adapters have distinct side-effectful proof scope and are not treated as
pure-helper source-call or native-qualified evidence. Existing release rules
exclude scripts/tests/docs. No UI/UX or whole-plugin pre-release/Database Review
row is refreshed. Uploader/toolkit and application/runtime sources are untouched.

## Fresh prerequisites and single real attempt

Before acquisition: related minimum family **73 tests/1,844 assertions**, no
failures/skips; explicit new-source PHPCS/PHPCompatibilityWP pass. The13 new
tests contribute104 assertions and use only owned disposable synthetic ZIPs/files:
genuine libzip positive content/CRC/mode/wrapper decoding; traversal, symlink type,
special-mode, case and file/directory conflict rejection; local/central name
inconsistency; publisher mismatch before parsing; exact readback without executing
a PHP payload; overwrite/traversal, changed-marker and unknown-file refusal.
Each test removes its owned child and independently asserts it absent. These are
physical development filesystem/libzip checks, not native candidate-runtime proof.

One actual invocation only:

```text
php -d memory_limit=768M scripts/manual/source-policy-minimum-acquire.php --confirmed-owned-minimum-artifacts
```

Trusted tooling: PHP8.5.1, libzip1.11.2, cURL8.17.0; SSL backend subsequently
observed read-only as OpenSSL3.5.4. New child:
`alynt-minimum-9a46b4e802dd9ab2f5d1655d0f8d7608`.
Receipt: status refused, stage `download-php`, failure `fixed-download-refused`,
code60, elapsed119ms, cleanup confirmed; runtime_loading/site_database_operations
false. Trusted source/tool image SHA256:
`81df8b5e4e089c9f7c706cf05ae02295a4806e9b22dd6ba46ef3de97ba9cd48f`.

[cURL code60](https://curl.se/libcurl/c/libcurl-errors.html) denotes failure to
accept peer certificate verification. No PHP ZIP was written, core download was
not attempted, and real CRC/tree/pins/PE/dependency/extraction checks were not
reached. The child contained only its owned marker and was removed. A separate
read-only exact-path existence check confirmed the child absent; nothing material
or recoverable was retained. No artifact hashes or positive qualification are
invented from this failed transfer.

Read-only trusted-CLI metadata: curl.cainfo and openssl.cafile are unset;
CURLSSLOPT_NATIVE_CA is defined. This is consistent with a local trust-source
gap, but does not establish the precise failed certificate chain or accuse the
publisher of serving an invalid certificate. No CA file, Windows trust store,
PHP INI, environment or service was changed, and no further endpoint probe ran.

## Validation/publication and remaining gate

Post-attempt repository QA, existing PHP8.5.1:

- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,116 tests/
  11,052 assertions, no failures; only the two existing missing-Sodium invalid-key
  and signature-roundtrip skips. Fresh minimum family73/1,844 before the attempt;
  no skip, artificial native success or weakened guard was added.
- `npm run lint`:237 files pass; new adapter PHPCS/PHPCompatibilityWP pass;
  admin23/23/no skips; structure149 runtime/229 support/213 tests/210 class names.
- Build, Composer strict manifest validation/audit and npm moderate audit pass;
  zero known advisories/vulnerabilities, no install/update or generated tracked
  delta. Owning links, bounded source/consumer/error/cleanup/compatibility diff
  review and whitespace checks pass. Uploader/toolkit remain clean and untouched.

Exact-head ordinary CI is recorded after publication. No network acquisition or
candidate-runtime operation is repeated for publication. Green isolated CI will
not turn the refused proof into success.

Implementation-plan progress: bounded adapter and owned filesystem/libzip failure
prerequisites advance the minimum lane; real artifact qualification is blocked
by TLS trust before any archive acquisition. Native execution/wiring stay closed.
Recommended next gated slice: **Harness-only Windows Native-CA Trust Integration
and One Fresh Owned Artifact Acquisition/Cleanup Attempt**. The
[documented cURL native-CA option](https://curl.se/libcurl/c/CURLOPT_SSL_OPTIONS.html)
supports this observed Windows/OpenSSL/cURL combination without installing a CA
bundle or changing global configuration. Approve an explicit per-request native
trust-source choice, regressions, fresh prerequisites and one new fixed-URL attempt;
retain peer/hostname/HTTPS checks, pins, limits, no redirects/retries, exact cleanup
and all no-install/no-runtime-loading/no-site/database boundaries. Its actual TLS
success remains unproved; no fallback or revocation-disable option is recommended.
