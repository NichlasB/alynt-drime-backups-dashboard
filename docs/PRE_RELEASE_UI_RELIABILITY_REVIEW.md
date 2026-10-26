# UI Reliability Pre-Release Review — 2026-10-26

## Phase 1 inventory and approved execution plan

Target Alynt Drime Backups Dashboard, slug/text domain
`alynt-drime-backups-dashboard`, root
`C:\Development\WordPress\Plugins\alynt-drime-backups-dashboard`, master,
main `alynt-drime-backups-dashboard.php`. Clean source baseline
`8822c7091f50f85a75198ebbb08851fb53648a3a`, version0.1.77 plus unpublished UI
changes; no permission to reuse its immutable released tag. Toolkit baseline
`270d393dcced71ac26cd67f522ea0842b94bc783` clean. Uploader is not a target.

FULL Phase1 completed read-only: checklist entry0.1.53, full-state0.1.28 and
prerequisite0.1.15 evidence are historical, not current readiness. Phase2 preserves
their dates/history, resets required review/validation rows for this exact source
candidate, and follows canonical prerequisites then01–07,07A,08–13, security last.
Optional00 skipped; no model-selection decision needed. Routine R1/R2 phases,
fixes, commit/push/CI covered by standing autonomous repository authority.
Stop for R3/auth/privacy/schema/public-contract/dependency decisions or blockers.
No site selected: no LocalWP/SQL/WordPress/SSH/credentials/schedule/native operations,
package/release/tag/installer/deployment. V2.3 deferral remains unchanged.

## Canonical prerequisite revalidation

- Build: existing admin-only esbuild, portable PHP lint/test/coverage, Node tests,
  lockfiles, PHPCS/WPCS/PHPCompatibility/PHPUnit/Brain Monkey dev dependencies,
  Composer allow-plugin, bootstrap, editor/license/readmes/changelog/settings/
  hooks/lifecycle/i18n/ignore scaffolds inspected. Automatic deployment watchers,
  concurrently/nodemon and unused frontend scaffold intentionally omitted by this
  project's no-autonomous-deploy posture; no template-driven dependency churn.
  No updates needed. Stale engineering-document command placeholders go to12.
- Observability: mature manage_options Diagnostics, action nonces, centralized
  Event_Log/redactor before persistence/export, non-autoloaded bounded options.
  Diagnostics disabled by default, warning/14 days/200 events; bounded1–90 days,
  10–1000 events. Separate always-on audit90 days/500 events is documented and
  intentional. Existing cron/poller/REST/admin instrumentation, guarded clear,
  authorized rendered exports, explicit purge/preserve uninstall. No updates
  needed. No public download, new storage, or runtime qualification claimed.
- Updater: Required, configuration-only canonical revalidation. Exact GitHub
  header/repo, aligned0.1.77 metadata, release-published/manual requested-tag
  checkout, same-tag concurrency, Node20 npm ci/build, single slug root, dev-only
  Composer excluded, recursive-build/dev/source/secret exclusions, idempotent
  asset overwrite. No updates needed. No package creation, release query,
  workflow dispatch or installed updater acceptance; fresh patch version later.

Fresh validation: build, PHP304/1489 with2 existing Sodium skips, Node16/16 no
skips/failures, PHPCS138, structure, strict Composer validate, npm/Composer audit
zero advisories, diff hygiene passed. Focused log/diagnostics/uninstall31/257
passed using isolated WordPress shims, not a real database/site.

## Ordered outcomes

01 complete, no source edits: runtime PHP138/source JS1 plus supporting source,
tests and build scripts scanned for dead/debug/TODO/temporary remnants. Matches
were intentional CLI/build/test reporting; explicit root includes/trait composition,
hooks and callable public methods are not unused merely without a direct call.
No confirmed cleanup defect or unresolved TODO. Do not delete dormant safeguards.

02 complete, no refactor selected: runtime include136 largest259 lines, admin
JS209, largest CSS171; no file above300/250/500. PHP token inventory flagged38
methods over50 lines, mostly cohesive renderers, bounded query/poll/validation/
dispatch orchestrators. Existing composition-root constructor and logger's
five-field call are deliberate, not grounds for a public-signature change.
Preserve guard ordering and stable interfaces; no mandatory file split/cycle or
new mixed-concern class found. Long-method soft-threshold debt remains documented,
not falsely reported as zero or used to launch an unrelated architecture project.

03 Phase1: one confirmed Medium additional export failure in
`trait-admin-page-diagnostics-event-log.php:51–55`: notice plus `[]` textarea/copy
still offered on JSON failure. Plan: omit only failed export controls, retain
event display/clear confirmation, add genuine failing encoder/positive regression.
Other reviewed flow: poller validates and classifies errors, handles storage
failure, fixed HTTP timeout/body limits, action-specific nonces and recoverable
notices, clear/remove confirmations, missing/empty evidence and local busy-state
recovery. No networking, storage or authentication fix authorized by this plan.

03 Medium export defect fixed: genuine INF encoding regression failed against
actual renderer, then passed2 tests/10 assertions after withholding only failed
export controls. Event review and clear-confirmation link remain available.
The first harness run exposed a two-argument-only add_query_arg shim, not a plugin
failure; shared test shim now supports the existing three-argument call. Added
shared textarea escaping shim so focused runs do not depend on test-file order.
No production auth/storage/clear-action change. Fresh PHPCS138/structure and
PHP306/1499 with2 pre-existing Sodium skips passed.
Bounded fix review: same escaped/translated output, same authorized page/data,
no new route, write, raw diagnostic log or dependency. This is not completion of
the blocked pipeline's final13 Security Audit. Node16/16, build and PHP syntax
passed; current exact-head publication evidence belongs to the toolkit record.

## Genuine approval boundary: schema-upgrade failure handling

Update2026-10-26: user approved the repository-only R3 foundation; see
[implementation/evidence](SCHEMA_UPGRADE_FAILURE_SAFETY_FOUNDATION.md). The code
repair and isolated positive/fault/retry controls now exist. The finding below
is retained as its historical rationale, not the current implementation.
FULL03 remains incomplete pending separately approved core-backed owned database
proof; do not substitute isolated API results for installed-schema qualification.
The next gate is that actual proof, no longer approval of the repository repair.

03 is **incomplete / Awaiting Approval**, not passed. High source finding:
`includes/class-storage.php:183` unconditionally updates the schema version after
three dbDelta calls; `maybe_upgrade():193–200` trusts an equal marker and returns.
`class-activator.php:27` and `class-plugin.php:60` call these void methods without
an installation failure result. A failed CREATE/ALTER with a successful option
write can therefore mark an incomplete installation current and suppress retries.
The current StorageSchemaTest asserts source declarations only, not this failure.

WordPress [dbDelta source](https://developer.wordpress.org/reference/functions/dbdelta/)
executes queries and returns its planned-change strings, not a reliable success
boolean. [wpdb::query](https://developer.wordpress.org/reference/classes/wpdb/query/)
can return false on error. A naive nonempty dbDelta result check is insufficient.
This is source-established risk; no failure was injected into a real database,
no database state inspected, and no claim that a deployed site is broken.

Recommended next gated slice: **repository-only Schema Upgrade Failure Detection
and Retry Safety Foundation**. Explicit R3 approval for bounded storage-lifecycle
behavior: verify required schema before recording completion, preserve retry on
partial failure, propagate a support-safe failure to owning callers, add isolated
fault/positive/no-op/retry tests. Preserve schema8 and existing data/contracts;
no speculative migration/schema bump, SQL execution, LocalWP, production, options,
activation, credentials or schedule changes during implementation. Real installed
database/lifecycle proof requires its own exact-target/owned-fixture authority.

Do not start dependent04–13/07A or claim full readiness before this gate is resolved.
04/05 prompts were read only to classify the boundary; neither completed. No
package, release or tag reuse. Toolkit owns the exact resume/publication state.
