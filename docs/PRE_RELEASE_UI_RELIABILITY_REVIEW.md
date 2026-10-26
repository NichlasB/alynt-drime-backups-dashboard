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

Current outcome2026-10-26: the repository repair and separately approved,
exact-target-confirmed [core-backed owned proof](SCHEMA_UPGRADE_CORE_OWNED_PROOF.md)
are complete. FULL03 Phase2 now complete for its two confirmed findings (Medium
failed export, High premature schema completion); no additional confirmed issue
in the resumed affected-path review. Actual core/database122/9, seven physical
DDL errors/retries,77/77 application checksums unchanged, fixtures removed. This
does not certify all database/lifecycle/security/release behavior. Resume04 next.
The dated gate/finding/recommendation paragraphs below are historical, superseded
by this outcome, not permission to repeat site operations or claim FULL complete.

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

## Resumed04 WordPress Best Practices — 2026-10-26

After263541f core-backed proof and exact-head CI succeeded, canonical04 Phase1
resumed read-only against the current candidate.141 runtime/manual PHP files
scanned (136 includes, root/uninstall,3 manual), plus directly related route,
action, storage, fixture and tooling context; PHPCS/WPCS/PHPCompatibility remains
configured for minimum WP6.0/PHP7.4. Manual scripts/tests are development-only,
excluded by the actual Build Release rsync rule, not public WordPress endpoints.

Summary: deprecated functions0; hook issues0; database API/preparation issues0;
options/transients issues0; meta-key issues0; AJAX issues0; REST issues0; asset
enqueue issues0; runtime path issues0; header/structure issues0; remaining coding
violations0. **No updates needed**, complete under the canonical no-change rule,
not a claim that later database/security/runtime reviews have passed.

Evidence: deprecated-call scans across owned PHP (excluding dependencies) found
none. Runtime callbacks are removable named methods registered in admin_menu,
admin_enqueue_scripts,rest_api_init and prefixed cron hooks. No AJAX or post/user
meta ownership. The enrollment REST namespace is versioned with explicit bearer
permission callback, per-argument validation/sanitation and handler-side one-time
pairing checks; client opt-in exchange deliberately is not a cookie/nonce form.
Admin form action selection is sanitized before action-specific nonce/payload
processing and capability-protected rendering; no authorization change selected.

DDL uses dbDelta/charset/prefix, metadata LIKE values are escaped/prepared, query
values use prepare or wpdb typed helpers; only closed internal identifiers are
interpolated. Schema marker/policy/bounded redacted log options use defaults and
autoload false. Enrollment hash keys/TTL and global/site poll-lock TTL are bounded;
transient locks remain advisory, not falsely claimed atomic mutexes or durable
authority. Assets have prefixed handles/version/footer and exact screen gating;
no request-time hardcoded plugin filesystem path. The manual proof's fixed LocalWP
path/owned SQL exceptions are intentional explicit-target development constraints.

Full local PHP315/1597 (two pre-existing Sodium skips), Node16/16 fail0/skip0,
PHPCS141, structure, build and dependency audits passed. Final release validation
rows stay pending. No site operation repeated for04. Core-backed schema proof does
not cover actual activation/deactivation/uninstall options/cron/data preservation;
the repository's mandatory isolated lifecycle acceptance remains an R3 boundary
before release.05 Database Review remains pending, not inferred complete here.

Recommended next gated slice: **Plugin Tester local-only Isolated Dashboard
Activation/Deactivation/Uninstall Lifecycle and Data-Preservation Owned Proof**.
Use a separately approved disposable WordPress namespace/fixtures and exact
cleanup; never activate/deactivate/uninstall or migrate the existing application
installation. This is distinct from the four-table schema-only proof and requires
fresh scope approval/target confirmation before lifecycle/fixture cron writes.

## D1 Diagnostics/Event-Log Path Organization — 2026-10-26

Source-scoped follow-up only, under explicit D1 file-operation approval (R3 for
the bounded moves, not authority for database/site/lifecycle actions). Review
baseline `e532c82e21388aa7ef7ac8c318c7f50115812afe` through source commit
`d68184498a2b1a6e50494489deb18899ee0a121b`, on `master` (also origin default).
All15 raw source-file SHA256 values match before/after; Git reports100% identity.
No functions, classes, traits, interfaces, namespaces, logic, compatibility,
schema8, version0.1.77, permissions or cron behavior changed. No forwarding stubs,
autoloader, file removal, architectural redesign or uploader migration.

Current navigation map (historical paths above/below remain valid at their
recorded commits; this table supersedes only current-path descriptions):

| Previous path | Current path |
|---|---|
| `includes/class-diagnostics.php` | `includes/diagnostics/class-diagnostics.php` |
| `includes/class-event-log-redactor.php` | `includes/diagnostics/class-event-log-redactor.php` |
| `includes/class-event-log.php` | `includes/diagnostics/class-event-log.php` |
| `includes/traits/trait-diagnostics-attention-history-metrics.php` | `includes/diagnostics/traits/trait-diagnostics-attention-history-metrics.php` |
| `includes/traits/trait-diagnostics-backup-source-metrics.php` | `includes/diagnostics/traits/trait-diagnostics-backup-source-metrics.php` |
| `includes/traits/trait-diagnostics-local-removal-metric-helpers.php` | `includes/diagnostics/traits/trait-diagnostics-local-removal-metric-helpers.php` |
| `includes/traits/trait-diagnostics-restore-readiness-metrics.php` | `includes/diagnostics/traits/trait-diagnostics-restore-readiness-metrics.php` |
| `includes/traits/trait-diagnostics-scheduler.php` | `includes/diagnostics/traits/trait-diagnostics-scheduler.php` |
| `includes/traits/trait-diagnostics-site-metric-helpers.php` | `includes/diagnostics/traits/trait-diagnostics-site-metric-helpers.php` |
| `includes/traits/trait-diagnostics-site-metrics.php` | `includes/diagnostics/traits/trait-diagnostics-site-metrics.php` |
| `includes/traits/trait-diagnostics-support-sections.php` | `includes/diagnostics/traits/trait-diagnostics-support-sections.php` |
| `includes/traits/trait-diagnostics-support.php` | `includes/diagnostics/traits/trait-diagnostics-support.php` |
| `includes/traits/trait-event-log-reporting.php` | `includes/diagnostics/traits/trait-event-log-reporting.php` |
| `includes/traits/trait-event-log-settings.php` | `includes/diagnostics/traits/trait-event-log-settings.php` |
| `includes/traits/trait-event-log-storage.php` | `includes/diagnostics/traits/trait-event-log-storage.php` |

Consumers: root `alynt-drime-backups-dashboard.php` plus five existing
`tests/support/` files: `admin-page-diagnostics-rendering-test-harness.php`,
`diagnostics-test-bootstrap.php`, `enrollment-rest-controller-bootstrap.php`,
`event-log-test-harness.php`, `poller-test-harness.php`. Only48 fixed path literals
changed across these six files, at their original positions. No fixture/proof/
source-identity manifest changed. Static checks found136 unique ordered loader
members/136 recursive includes,610 tracked paths with no case collision,492
executable/config consumer files with no stale D1 path;21 changed/moved PHP
files passed syntax checks. Build Release recursively includes the new runtime
paths and still excludes development tests/docs/tools; this is rule inspection,
not a built/installed package or deployed external-consumer qualification.

Translation availability resolved before moves using existing Local-bundled
WP-CLI2.12.0, no dependency installation. Source-only `i18n make-pot` ran before
wp_load with owned empty global/project configuration and `--skip-packages`;
`--exclude=build,docs,scripts` prevents ignored historical package contamination.
Baseline/current full extractions have identical message/comment/header
signatures (831 blocks each). The tracked POT has830 blocks and unrelated prior
reference drift. Only the one D1 reference for `Unnamed site` at site-metric
helper line139 was regenerated by a guarded projection from actual extraction;
other entries/metadata remain byte-unchanged. A full POT refresh/09 review remains
separate; D1 does not claim whole-plugin translation freshness.

Offline validation: targeted diagnostics/event-log/poller/enrollment/storage/
caller tests70/543 with no skips; fullPHP315/1597 with the two pre-existing
missing-Sodium signing skips; Node16/16 no failures/skips; PHPCS141; structure136
includes/192 support helpers/138 test files/136 test class names; deterministic
asset build with no tracked output change; strict Composer validation and both
dependency audits zero advisories. Existing isolated schema fixture worker ran
positive/bootstrap/activation and fault/retry/no-op controls with in-memory APIs;
this is not WordPress authentication, database, physical transaction or actual
activation/deactivation/uninstall proof. Existing122/9 native schema evidence
remains historical at its own source baseline and was not rerun or refreshed.

Publication: exact source-head
[CI40383732313](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40383732313)
completed successfully. Actual logs: PHP7.4 and8.3 each315 tests/1604 assertions,
no skips/failures; Node each16/16, fail0/skip0; PHPCS141, build, structure, manifest
validation and dependency audits passed. This supersedes pending new-tree
validation only; the prior e532c82/CI40374264343 result remains historical.

Canonical FEATURE_LIGHT_REVIEW_PROMPT source scope:22 changed paths (15
byte-identical owners,6 literal consumers,1 POT), risk limited to fixed load/path
resolution; issues0, auto-fixes0, manual decisions0. No introduced quality,
error-handling, WP-convention, edge/data or performance defect. No new bloat,
duplicate logic, file growth or UI behavior, so bloat/splitting and UI/UX workflows
are unnecessary. Affected03/04 caller/error/redaction/path conventions were
rechecked without refreshing historical whole-plugin review rows.

Final FEATURE_SECURITY_REVIEW_PROMPT focused gate: no confirmed introduced
security defect; input/output/query/auth/nonce guards and redaction bytes remain
identical, fixed paths are not user-controlled, load order is preserved, no new
write-capable surface or debug behavior. Regression handoff: no confirmed defect
requires new coverage. This is a source-scoped differential gate, not full13,
Database Review, release readiness or genuine installed runtime acceptance.

Uploader and toolkit remain unchanged; unknown external direct includes,
ignored operator scripts and deployed copies are not disproved by repository
search. No site/native/SQL/fixture/credential/schedule/lifecycle operation,
package, tag, release or deployment. Product progress stays2/8; D2/U1/U2/U3 remain
unapproved. **Recommended next gated slice: Plugin Tester local-only Isolated
Dashboard Activation/Deactivation/Uninstall Lifecycle and Data-Preservation
Owned Proof**, requiring separate scope approval and exact target confirmation.
