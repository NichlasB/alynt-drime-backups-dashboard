# Minimum-Profile Core ZIP Host/Mode Repair and Owned Proof

Forward progress: the separately approved [selected import/classification proof](SOURCE_POLICY_MINIMUM_IMPORT_CLASSIFICATION_OWNED_PROOF.md)
identified the exact ImageHlp dependency and completed static acquisition and
owned payload readback/cleanup. Follow that record's dependency/runtime gate;
the historical refusal and proof limits below remain unchanged.

## Authority, baseline and outcome — 2026-10-29

Explicit R3 approval of the harness-only Core ZIP Host/Mode Diagnostic and
Evidence-Based Compatibility Repair with One Fresh Owned Acquisition/Cleanup
Proof. Restore baseline `29f7e96365ffaf19fc00b62567c058de96dc3141`, clean/synced
before edits. Follows the [native-CA proof](SOURCE_POLICY_MINIMUM_NATIVE_CA_OWNED_PROOF.md).

**Core mode repair passed the fresh full acquisition; the unchanged later import
classification guard refused completion. Exact cleanup confirmed.** No complete
static/runtime qualification is claimed. No installation, downloaded PHP/DLL/core
loading/execution, WordPress bootstrap, LocalWP/site/database/live work, SQL,
credentials, permissions on existing state, global configuration/CA store,
schedules, production/form wiring, native guards/budgets, dependencies, versions,
schema, ADBC1/ADBC2, package/release/tag or deployment changes. Existing trusted
PHP8.5.1 is tooling only. Uploader/toolkit are not modified.

## Bounded diagnostic, evidence and cleanup

The approved diagnostic phase uses new development-only
`scripts/manual/source-policy-minimum-core-diagnostic.php`. It calls the existing
fixed core download with native CA/TLS/HTTP limits, writes only an owned ZIP,
opens read-only libzip/CHECKCONS and summarizes at most8192 entries/128 metadata
groups/six safe examples. It never reads payload streams, expands, admits, runs
normal static verification or executes source. A synthetic owned ZIP test proves
host/kind observation without expansion/admission; diagnostic warnings are
redacted and opened ZIP/owned cleanup are protected by nested finally paths.
An uncertain constructor cleanup is never labeled confirmed by the final code.

One metadata-only invocation, then cleanup before repair/full proof:

```text
php -d memory_limit=768M scripts/manual/source-policy-minimum-core-diagnostic.php --confirmed-owned-core-metadata
```

Child `alynt-minimum-f5dd9118ccc1c90288a06661281825a0`; core ZIP32,963,838 bytes,
SHA256 correlation `f94b2d521ea9c38d965d1adf8ead55a1b45dc171795bd550e94cd2ce99c7a437`.
Observed3,856 entries:419 directories with host0/mode0/attributes16, and3,437
regular entries with host0/mode0/attributes0. Index0 is the exact commit wrapper;
index1 is its `index.php`, size405/packed243. This is not merely a directory-only
case. Diagnostic status metadata-observed-not-qualified, payload_expansion=false,
admission=false, runtime_loading=false, cleanup confirmed. Separate exact-path
read independently confirmed the child absent. No diagnostic artifact is retained.

The [Git ZIP writer](https://github.com/git/git/blob/master/archive-zip.c)
uses DOS-compatible attributes for ordinary non-executable entries. The
[ZIP specification](https://pkware.cachefly.net/webdocs/casestudies/APPNOTE.TXT)
defines host-dependent external attributes. These explain the observed encoding,
not the producer build identity; the actual byte diagnosis above is decisive.
No regular-file Git mode is inferred or defaulted from host0/mode0.

## Authenticated mode repair, not a mode fallback

- New `source-policy-minimum-core-modes.php` builds a sealed read-only mode/blob
  descriptor. Every recursive Git tree object's name/mode/object-ID byte encoding
  is recomputed with Git ordering, then matched to the declared object ID. The
  factory accepts only the **unchanged pinned root**
  `a3bd7a4074a4c95c8af3b969346726273d68e167`, with truncated=false; no caller trust
  root or public constructor. Ambiguous paths/case, duplicates, missing parents,
  changed mode/blob/tree IDs, symlinks/gitlinks/unknown types and over8192 records
  are refused. Primitive bounded metadata only; no runtime/native handle grant.
- A single fixed public [Git tree read](https://docs.github.com/en/rest/git/trees#get-a-tree)
  is added to catalog DATA: the pinned tree's API URL with recursive=1. No existing
  artifact URL, publisher digest, commit/root or source SHA256 pin changes. The
  downloader allows only the fixed php/core/tree selectors; tree metadata is capped
  at2MiB, versus unchanged64MiB archive caps. Same HTTPS/native-CA/peer/hostname,
  no redirect/proxy/auth/fallback/retry and request/time limits. No token is used
  by the proof; API truncation or refusal stops rather than switching endpoints.
- The full proof fetches and validates that manifest before archives. Root API
  assertions alone are insufficient: all graph hashes must match, and the fixed
  root must match. Core archive decoding requires the sealed descriptor. DOS
  files get only their exact authenticated per-path mode; absent descriptors or
  missing files refuse. Only the observed host0/zero-mode/exact0-or16 attribute
  representation is supported; Unix modes must agree with the manifest. No
  change to link/type/path/collision/inventory/CRC/budget checks.
- Before further admission, regular-file count must match and **every decoded
  blob** must hash to its authenticated object ID. The existing complete tree
  computed from decoded bytes and the independent11 critical source SHA256 pins
  plus schema.php pin remain mandatory and unchanged. The descriptor is DATA,
  not permission to load, extract, construct a native owner or change permissions.

Synthetic graph tests have real positive hashing/mode cases plus tampered/missing/
ambiguous/unsupported refusals; a consistent synthetic root cannot create the
fixed-root descriptor. An actual pinned public graph preflight independently
accepted3,437 files/418 directories, returned `index.php` mode100644, and refused
a changed graph. Thus a factory that always refuses would fail actual preflight.
The no-descriptor DOS-file regression still refuses, with no default100644.
An existing non-network cURL setter fixture only gains the new size-limit local;
its true/false and TLS guard assertions are not weakened.

## Fresh full proof and precise limits

Fresh related minimum suite86 tests/1,957 assertions, no failures/skips; changed
source PHPCS/PHPCompatibilityWP, structure and scoped security/diff review passed.
One full proof invocation **after** diagnostic cleanup and evidence-based repair:

```text
php -d memory_limit=768M scripts/manual/source-policy-minimum-acquire.php --confirmed-owned-minimum-artifacts
```

Child `alynt-minimum-d41810df6af4b7bb2c9b69d95a55be00`; trusted image SHA256
`80d17092386466143c60abeb5ee1aeb7c987bf3df2b564791fcdf629111d9596`.
PHP8.5.1/libzip1.11.2/cURL8.17.0/OpenSSL3.5.4, per-request Windows native CA,
child memory768MiB. Elapsed6,830ms; no retry of the full proof.

- Authenticated manifest:977,559 bytes; root as above,3,437 files/418 directories.
  JSON SHA256 correlation `b18d30863e7dfad681ae3d1c09d2dfa9af78c0ee5d176249c2f140940ca7b9c3`.
- PHP ZIP:26,132,214 bytes, unchanged publisher SHA256
  `14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace`;
  93 entries/70,083,558 expanded bytes decoded in memory; extraction=false.
- Core ZIP:32,963,838 bytes, SHA256 correlation as diagnostic;3,856 entries/
  74,132,675 expanded bytes. Inventory/CRC/length/authenticated-mode/blob checks
  passed; no regular mode was defaulted; extraction=false.
- Final status refused, stage static-identities, failure unexpected-import-refused,
  code0; runtime_loading/site_database_operations=false, cleanup confirmed.
  From the **unchanged source branch order**, reaching this error implies passage
  through complete core-tree/12 source-pin checks and selected three-role PE
  structure decoding. The receipt does not separately emit those intermediate
  results, PE hashes or the rejected import name; do not invent them or claim
  dependency closure/loader compatibility. The existing OS/CRT import allowlist
  is unchanged. Output extraction/readback was not reached.

Marker, metadata JSON and both ZIPs were removed with their exact child. An
independent exact-path check confirmed both diagnostic and proof children absent.
Nothing material is retained/recoverable; only non-secret findings remain. No
further artifact acquisition, import allowance change or native operation ran.

## Publication and next boundary

Post-proof repository QA and pushed exact-head CI are recorded below after
execution. No UI/UX, whole-plugin release readiness/Database Review, runtime or
native transaction evidence is refreshed. Production/bootstrap/native workers,
ownership/static/import policy, dependencies and package workflows are untouched.

Executed repository QA, existing PHP8.5.1:

- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,129 tests/
  11,165 assertions, no failures; only the two existing missing-Sodium invalid-key
  and signature-roundtrip skips. No added skip or weakened existing assertion.
- Focused minimum family86/1,957, no failures/skips; actual pinned-graph positive
  and changed-graph refusal passed separately without archive/code execution.
- PHPCS239 files/changed-source PHPCompatibilityWP, admin23/23/no skips,
  structure149 runtime/229 support/215 tests/212 class names, build, strict
  Composer validation and Composer/npm audits pass. Zero known advisories/
  vulnerabilities; no install/update or generated tracked delta.
- Owning links, complete scoped source/security/compatibility/consumer review,
  whitespace and protected-input checks pass. Existing artifact URL/digest/root/
  source-pin values are unchanged; catalog adds only the fixed metadata URL.
  Uploader/toolkit remain clean and untouched. Both exact children are absent.

Ordinary publication CI passed for exact implementation head
`60e253d1a8fd4e43267012dd95db400aa004aa31`:
[run40904744052](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40904744052).
Actual logs report PHP7.4:2,129 tests/11,167 assertions and PHP8.3:2,129 tests/
11,172 assertions, both successful with no skips. Each job's admin suite passed
23/23 with zero failures/skips; Composer/npm audits report zero advisories/
vulnerabilities. Build, structure and PHPCS steps passed in both jobs. This
evidence-only closeout is committed separately and its exact-head status is
verified at handoff. No new artifact/native operation runs for publication;
green CI does not complete the refused import boundary or qualify downloaded code.

Implementation-plan progress: the core host/mode and full decoded core source
identity boundary advanced; complete acquisition/static qualification is now
blocked at selected import classification. Recommended next gated slice:
**Harness-only Selected PE Import Diagnostic and Evidence-Based Dependency
Classification with One Fresh Owned Acquisition/Cleanup Proof**. Obtain bounded
name/role evidence from the pinned package, review each dependency explicitly,
add only evidence-supported classifications with regressions, and require fresh
prerequisites/cleanup. Do not guess the missing DLL, use arbitrary DLL search,
disable the allowlist, install dependencies or load code. Preserve all TLS/pins/
tree/mode/link/budget and no-install/no-runtime/no-site/database boundaries.
