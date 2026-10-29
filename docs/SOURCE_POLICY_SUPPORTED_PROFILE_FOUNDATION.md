# Supported-Profile Source Rebaseline and Isolated Harness Safety Foundation

## Scope and restore point — 2026-10-29

Approved gate1 of the [qualification design](SOURCE_POLICY_SUPPORTED_PROFILE_QUALIFICATION_CLOSURE_DESIGN.md).
Repository-only R2 proof-harness work, clean/synced restore and coherent review
baseline `575a35aee01ba07de281168aaf834ff4c76b0aee`. No installed site/source or
metadata inspection, core/bootstrap execution, SQL, native sessions, fixtures,
accounts, permissions, credentials, controller/workflow dispatch, installation,
production wiring, package/release/tag/deployment. Uploader/toolkit untouched.
Declared PHP7.4/core6.0 support, version0.1.77/schema8 and runtime guards remain.

## Fixed source rows, not environment selection or execution authority

[`source-policy-supported-core-pins.php`](../scripts/manual/source-policy-supported-core-pins.php)
contains two immutable official source rows. All23 raw-file SHA256 values were
independently fetched/rehashed through GitHub's contents API at their exact
commits after implementation; no source bytes were installed or executed:

| Row | Official immutable commit | Selected source closure | Actual declarations |
|---|---|---|---|
| 6.0 | `cc101b64012b16d087780657a2b828ccd7794a63` | 11 files, including version.php | wpdb:wp-db.php; absint:functions.php; cache API:load.php |
| 7.1.3 | `fc9832bef919c2a08541248db23af90d14ffb082` | 12 files, including version.php | wpdb:class-wpdb.php; absint/cache API:load.php |

Initial-library includes are version/load/plugin/error/cache/database/formatting/
functions. Direct top-level edges add class-wp-hook from plugin, object-cache
from cache, option from functions, and7.1.3 filter-sentinel from plugin. These
edges are covered by pins before any core include. Method-local conditional
requires in load/functions/database are **not** a general WordPress bootstrap
closure: failure/bail/translation/admin/drop-in paths remain excluded/refused.
The future genuine per-profile qualification must establish the actual complete
executed closure and costs; these manifests alone do not establish that proof.

[`source-policy-supported-profile.php`](../scripts/manual/source-policy-supported-profile.php)
selects only exact string6.0/7.1.3 rows, no unknown/version-prefix fallback or
caller-supplied manifest. Its pure expected-mode predicate requires exact
PHP7.4.33/mode0 or PHP8.5.12/mode3. Windows NTS module names php7.dll/php8.dll are
candidate DATA, **not** observed artifacts or evidence that a runtime exists.
No bitness/ZTS/build/driver/server/transport qualification is inferred. The fixed
Windows entry still requires Windows/PHP8.5.12/mysqli/no PDO and its existing
scope/fixture gates; the minimum row enables **no new native runner**. Mode is
never set by the new helpers. JSON is not needed to read either manifest.

## Actual wiring and affected consumers

- Windows bootstrap now uses the fixed7.1.3 row for pins/include order and exact
  database/parser/cache bindings. It hashes version.php as well as the original
  11 libraries before including core; an installed7.1.4 version cannot silently
  inherit identical shared-library pins. The previous functions.php parser
  binding is corrected to load.php. Scope/ABI rejection still precedes helper
  loading and all target reads. Existing installed paths/ports/root inputs are
  not new authorization and were not accessed in this task.
- Adapter helper hashes use that same source row, and adapter/ambient image
  construction uses its fixed runtime-module name. Full loaded-file image
  collection consequently includes the new manifest/helpers. Strict Windows
  mode3, ambient core-created OFF, held receipts, exact acknowledgements,
  reconciliation, SQL cases and cleanup acceptance remain unchanged.
- [`source-policy-proof-reflection.php`](../scripts/manual/source-policy-proof-reflection.php)
  shares the prior private-input binder with conditional accessibility below
  PHP8.1, and a fixed-class constructor helper that invokes the **actual**
  protected handle-free constructor, never a parent/native constructor. It does
  not inject active/ready/facts/handles, return reflection methods or invoke
  arbitrary supplied callbacks. No production file loads these helpers.
- Bootstrap cases, adapter cases, ambient owner, ADBC1/ADBC2 owned operations and
  failed-connect diagnostic construction use that helper. The Linux worker also
  needs it because it consumes the same adapter/ambient operations; its helper
  require follows the unchanged no-injected-entry assertion and enters the
  frozen loaded image. This is a dependency correction, not a new Linux native
  qualification or approval to rerun it. The ADBC2 isolated worker now uses the
  actual shared binder/constructor, not its duplicate binder.
- The Linux preflight source-equality regression follows the moved fixed row,
  still comparing **all original11 hashes exactly** against the unchanged Linux
  pin file, excluding only the separately pinned7.1.3 version file. This is not
  a claim that7.1.3 and7.1.4 are interchangeable distributions.

Unchanged: all includes/bootstrap/production code, controllers, CI workflow YAML,
Linux pins, dependencies, assets, SQL/byte predicates, budgets and ADBC1/ADBC2
formats/acceptance. No generic profile/provider framework or automatic runtime
discovery added. Later minimum native integration must audit its invocation/read
and diagnostics paths rather than relabel these Windows8.5-only entry points as
PHP7.4-ready. No binary or installed WordPress changes are necessary for this
repository-only milestone.

## Non-vacuous isolated evidence and limits

New profile tests verify exact refs, source roles, positive rows, missing/unknown
versions/types, mode/PHP mismatches, module names and JSON/driver-free use. A
driver-free child executes the **actual extracted pre-load hash guard** against
existing repository bytes: original passes; changed digest and missing file
refuse. It never includes core or writes fixtures. Source-order checks require
ABI refusal before loading helpers, and hashing before any core include.

New reflection tests run the actual helper and actual dormant owner against a
minimal in-memory wpdb double whose parent constructor throws. The owner ends
not-ready/null-handle, factories still return unavailable, reconnect/check stay
false, and a private primitive input is successfully set without changing
constructor visibility. Unrelated receiver construction refuses. The same test
must pass on actual PHP7.4 CI, where private/protected accessibility is required;
PHP8.5 local success alone cannot cover that branch.

Two source-clone semantic controls remove respectively the actual exact-PHP and
mode predicates in memory, never modifying a file. The unchanged positive and
negative contract passes on the original and exits contract-killed on each
mutant, not startup failure. Positive profile/constructor/hash controls prevent
an always-unavailable implementation from counting as success. These are
synthetic API/source tests, not genuine WordPress authentication, database,
physical cleanup/transactions, native minimum-version or Windows qualification.

The original stale-binding/source tests failed before implementation (missing
new helper files and the literal old Windows parser binding); related tests pass
afterward. No existing assertions/skips/guards were weakened. Historical native
successes, including Linuxb0717fc/image and old Windows receipts, remain valid
**only for their recorded source images**. Changed shared helper/case images
are unqualified; their old physical evidence must not be refreshed or copied.
No whole-plugin review/checklist or release readiness is completed here.

## Validation and publication

Executed related isolated suites:134 tests/705 assertions, no failures/skips.
Both semantic controls were contract-killed; official source hash checks23/23.
Lint229 files, admin23/23, structure149 runtime/227 support/207 tests/204 class
names, build, strict Composer validation and Composer/npm audits pass. No known
dependency advisories/vulnerabilities and no tracked generated delta.
Final `php vendor/bin/phpunit --verbose --do-not-cache-result` on PHP8.5.1:
2,043 tests/9,208 assertions, no failures; only two existing missing-Sodium skips
(invalid-private-key and signature roundtrip). Four local owning-record links
resolve and `git diff --check` passes. Production/runtime/workflows/dependencies
and generated tracked assets remain byte-identical to575a35a. Exact-head CI
publication evidence follows the approved commit/push; local results do not
substitute for the actual PHP7.4 reflection branch on CI.

Implementation-plan progress: gate1's source-binding/isolated compatibility
foundation is implemented; genuine supported-profile evidence is still gated.
Recommended next gated slice: **Read-Only Supported-Profile Source/Runtime
Feasibility Audit**, audit-first for the minimum PHP7.4/core6.0 and current-source
Windows/TCP candidates. Resolve exact artifact availability and a suitable owned
target without SQL, authentication probes, bootstrap, fixtures, installation or
configuration changes. A site-backed audit requires current Site Operations/
Novamira checks and exact target confirmation. Only afterward approve each
bounded native qualification/cleanup attempt. Production provider/form wiring
remains a later gate; the application's whole-option writer is unchanged.
