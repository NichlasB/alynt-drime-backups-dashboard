# V2.3 Cadence Authority Activation and Entry-Point Cohabitation Design

Approved design-only on 2026-10-14. Source baselines: dashboard `32ce465`, uploader `b62da2c`. This advances the activation/entry-point decision in [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md), following the dormant guarded callback milestone. It does **not** approve runtime implementation, permission creation, activation, migration on a site, release, deployment or rollback execution.

## Decision And Scope

Prefer an explicitly enabled client-owned **scheduled scan/enqueue cadence**, with WP-Cron serving only as a wake-up mechanism. Do not coordinate every WordPress cron writer or claim ownership of backup creation, uploads, cleanup, Drime or other plugins. Preserve legacy mode for never-enrolled/never-activated clients. Once a client enters authority mode, a blocked or uncertain authority must never silently fall back to a legacy scan.

Before activation, require a common policy boundary for every uploader scan/enqueue caller, durable mode/lifecycle evidence, failure-visible wake-ups, and a reviewed drain/recovery path. First authority-mode rollout should reject manual/CLI/remote scans rather than invent an immediate-scan authorization or reuse a scheduled claim for them. Existing uploads may continue under their existing independent policy; disabling scheduled scans is not cancellation of queued/in-flight uploads.

These are candidate implementation requirements. Current production still constructs `Cron($this)` without a dispatcher, uses the legacy scheduler and accepts its existing actions. The schema-2 authority `enabled` field and injected dispatcher `scan_enabled` boolean are model/test inputs, not an implemented administrator opt-in. Version-1 authority evidence still reports dispatch/execution unsupported. `schedule_rollback` remains reserved/rejected.

## Actual Source Inventory

Paths in this table are relative to the separate `alynt-drime-backups-uploader` repository. The future disposition is a design decision, **not current behavior**.

| Surface | Inspected source and current behavior | Candidate authority-mode disposition |
| --- | --- | --- |
| Scheduled scan | `includes/class-cron.php`: `SCAN_EVENT` calls `scan()`; optional injected guard wraps `perform_scan()`. Production does not inject it. | Only scheduled wake-ups may enter the reviewed claim/start/callback/completion path. Reject early, stale, blocked and uncertain wake-ups; never legacy fallback. |
| Local administrator scan | `includes/trait-plugin-admin-actions.php`: nonce/capability-protected `handle_scan_now()` calls `scan_and_queue()` directly. | Keep authentication, but reject scan in authority mode with a concise local reason until a separately approved manual-run policy exists. |
| CLI scan and run | `includes/class-cli-command.php`: `scan()` and `run()` call `scan_and_queue()`; `run()` then calls `upload_next()`. | Reject unsupported authority-mode scan; `run()` must not continue its upload loop after rejection. Standalone upload remains independent. CLI invocation is not mode authorization. |
| Signed remote scan | `includes/class-remote-action-worker.php`: accepted `scan_upload_now` runs direct scan/queue, then schedules upload through `trait-remote-action-worker-scan-upload.php`. | Future acceptance/capability and worker checks must both reject scan in authority mode. Recheck an already-accepted action before effects; no automatic rewrite into a due dispatch. Preserve signed request/idempotency rules. |
| Shared scan facade and raw queue | `includes/trait-plugin-scan-queue.php`: public `scan_and_queue()` scans and calls `Queue::add_many()`; `includes/class-queue.php` exposes `add()`, `add_many()` and `prepend()`. | A Cron-only guard is insufficient. Guard the shared facade before scanning and recheck before queue writes; route internal scheduled work through a non-recursive trusted seam. Inventory raw queue callers; do not expose a caller-controlled bypass flag/token. |
| Failed-item requeue | `includes/trait-plugin-failed-upload-actions.php`: protected administrator action prepends a locally validated failed item and clears its failed record. | Treat as an explicit independent queue mutation, not a cadence scan or rollback. Block it while mode transition or scan-effect reconciliation is uncertain. Preserve ordinary legacy behavior outside authority mode. |
| Upload worker and abort | Cron `UPLOAD_EVENT`, CLI upload/run, administrator upload call `includes/class-uploader.php::upload_next()`; clear-active can abort multipart work. | Existing worker lease, queue, retry, retention and abort policy remain independent. No claim that authority revocation stops an upload, prevents all deletion, or rolls back Drime effects. No new abort/cleanup permission. |
| Wake-up and local settings | Cron `maybe_schedule()` uses `auto_scan_enabled`, creates missing scan/upload events at fifteen-minute recurrence, or clears both. `includes/class-settings.php::update()` persists the settings option. | Mode-aware scheduling must not recreate an unguarded legacy scan. Keep existing automatic-scan choice required; preserve independent upload recurrence intentionally. Settings changes must invalidate relevant mode admission before new scan effects. |
| Legacy schedule apply/preview | `includes/trait-remote-action-worker-schedule-apply.php` calls Cron `apply_scan_cadence()`, which clears/recreates only the scan event. Existing preview hashes describe legacy cadence plus cron state. | Fail closed for legacy mutation in authority mode; report unsupported mode rather than claim a cron wake-up change altered authority. Native apply/rollback needs separate versioned action evidence and approval. |
| Enrollment and opt-out | `includes/trait-dashboard-connection-state-transitions.php` revokes/replaces connection state or disables remote actions; evidence derives binding from current pairing/key context. | Reject stale binding and close new admissions on revoke/re-pair/key or relevant permission changes. Do not inherit a new enrollment's permission or erase uncertain old work. A normal polling timestamp update is not an enrollment change. |
| Plugin lifecycle | `includes/class-activator.php` installs settings/connection only; deactivator clears scan/upload hooks; `uninstall.php` deletes a fixed option list, not the dedicated safety option. | No authority opt-in on plugin activation/upgrade. Preserve uncertain evidence through deactivation; explicitly design reinstall, downgrade and uninstall before active-mode shipping. Deactivation does not terminate an already-running PHP process. |

The uploader upload lease (`includes/trait-uploader-lock.php`) is a separate option-based ownership mechanism, not authority dispatch fencing. Queue duplicate checks use package signatures/indexes; they do not atomically couple queue writes, authority completion and remote upload effects. Direct third-party calls, old plugin code or arbitrary database writers are nonparticipants; no global enforcement guarantee is possible merely from a new dispatcher.

## Mode Permission And Trust

Candidate admission requires all of: recognized local mode/lifecycle, explicit client-local administrator consent, current valid enrollment binding, automatic scanning enabled, exact supported state/version, no transition/recovery uncertainty, and a due dispatch owned and started through the reviewed CAS. Model `enabled`, a valid hash, remote status, preview success, source provenance or dashboard request alone is insufficient.

Keep scheduled-cadence consent distinct from read-only pairing, remote scan permission, Schedule Apply, rollback-preview and any future rollback execution opt-in. No automatic consent inheritance on upgrade or from existing `schedule_mutation_enabled`. A future local control must use capability plus nonce and explain blocked manual/CLI/remote scans, potential scan suspension and continuing uploads. No such control or setting is added here.

Prefer storing durable mode/lifecycle and its monotonically changing permission generation with the authority/journal CAS, **not** a second independent boolean whose write can race the model. This requires a separately approved versioned storage extension; schema 2 currently has no such lifecycle/generation contract and must remain unchanged. Exact field names, migration version and limits belong to that implementation review. New status/public contracts need independent version review too.

Live connection/settings still reside in separate options. Putting a consent generation in authority storage does not make those writes atomic or eliminate a revocation race. Future adapters must recheck authoritative local context, close admission before relevant transitions, retain partial-failure evidence, and test stale caches and concurrent readers. A last context read cannot stop revocation immediately afterward or stop already-running effects; this limitation must remain explicit rather than promising instantaneous cancellation.

The present context binding requires remote actions enabled. Thus remote-action opt-out can block even local scheduled admission in the prototype. **Do not activate this dependency silently.** Initial authority-mode consent must explain it and require an explicit reviewed transition back to legacy; never remove the binding check just to keep scans running. A later local-only authority independent of pairing would be a different trust design.

## Candidate Lifecycle And Migration

Lifecycle labels below are design vocabulary, not installed schema fields or APIs.

| State | New scheduled scans | Transition requirement |
| --- | --- | --- |
| Legacy / never activated | Existing behavior unchanged. | No automatic initialization from status reads, plugin activation, missing state or schema inspection. |
| Preparing | No new authority or legacy scan admissions. | Explicit consent; supported single-site target; capture allowlisted existing cadence, context and current work; protect transition durably before changing wake-ups. |
| Active | Only due, fully guarded scheduled dispatch. | Recognized consent generation plus authoritative model/context. One owner; uncertainty blocks new admission. |
| Draining / inspection required | No new scan/enqueue admissions; no fallback. | Preserve claimed/started/recovery journals and reconcile admitted work. Stopped wake-ups or an expired lease do not prove no worker exists. |
| Disabled after authority use | No authority scan. | Return to legacy only through explicit verified handoff, with no unresolved scan effects, generation/revision invalidation and verified legacy wake-up. Missing/invalid state is not this verified state. |

Preparation must account for accepted remote work, a scan that entered before transition, and concurrent manual/CLI calls. A read showing an empty queue/expired lock is not proof that such processes stopped. Enforce the future shared admission/enqueue boundary and prove transition interleavings before activation. While uncertainty remains, pause new scans visibly; do not clear state, replay a scan, or restore legacy simply to regain liveness.

Migration preserves valid terminal legacy journals without native provenance, blocks unfinished ones, rejects unknown/malformed/autoloaded state, and retains capacity limits. Existing schema-2 initialization cannot re-enable invalidated authority; no reset/reinitialize shortcut is authorized. Choose a fresh future due time from approved transition time plus cadence interval, not an immediate scan, expired historical timestamp or catch-up burst. Treat invalid/duplicate/missing legacy scan events as an inspection gate, not guessed cadence.

Deactivation/reinstallation preserves evidence but cannot guarantee a stopped process. Older production versions do not understand authority mode and may run legacy scans: **downgrade is unsafe until a verified explicit handoff**, even if an older strict model reader rejects the new schema. Uninstall is destructive and currently omits the safety option while deleting queue/connection evidence; do not claim safe active-mode reinstall/recovery or silently add that option to deletion. Require a separate lifecycle/data-retention review and operator runbook before shipping active mode. Multisite authority activation is out of initial scope until per-blog identity and lifecycle are explicitly proved.

## Wake-Up Cohabitation And Effect Boundary

Prefer an allowlisted periodic wake-up using the existing scan hook, with the model alone deciding due work. Its recurrence is transport/liveness policy, **not** the displayed authority cadence or rollback fingerprint. Keep upload wake-ups independent and do not modify unrelated hooks. A missed event, stale WP-Cron write or failed wake-up registration cannot authorize a scan; it must produce a support-safe liveness warning. No global cron interceptor, arbitrary event editing, forced manual poll or self-triggering loop.

Future mode-aware `maybe_schedule()` must not undo a verified handoff or restore legacy after a guard error. Durable authority/transition state and WordPress cron cannot be committed in one current CAS: failed registration leaves a visible blocked/missing-wakeup condition, not falsely completed activation. Test old already-dispatched callbacks, duplicate/late/early wake-ups and an unrelated writer removing the event. The guarded path remains safe only if every actual callback uses the mode-aware admission boundary; liveness can still fail.

After a successful scheduled dispatch, retain completion-time plus interval semantics and no burst replay. Callback success means a successful scan/queue result, **not backup creation or completed upload**. Existing Cron returns void and discards dispatcher disposition; before activation, add reviewed local observability rather than infer a successful scan from wake-up timestamps or cron-health logging alone.

Queue persistence and model completion are distinct writes. A crash after queue effects but before completion must retain uncertainty; existing signature deduplication is not exactly-once proof and must not justify retry. No authority claim grants downstream Drime execution rights. A stronger fenced enqueue/outbox or transactional design, including affected queue schema and admitted work identities, needs a separate approval if required by proof. Do not widen this design into uploader lock/retention rewrites.

## Evidence Lifetime Decision

Preserve both deployed legacy cadence-plus-cron hashes and the dormant authority **version-1** fingerprint/inspection unchanged. The latter includes `next_due_at`; successful dispatch completion changes it without changing cadence or schedule revision, and `CadenceAuthorityEvidenceTest::test_guarded_scan_due_advancement_makes_prior_native_rollback_evidence_stale()` requires `source_stale`. Do not delete that check or extend expiry to bypass it.

Preferred future direction: a **new explicitly versioned cadence-change identity**, separate from operational due-state identity. Bind the former to enrollment, permission generation, mode, enabled state, cadence and monotonic change revision. Ordinary successful scan advancement may change operational due identity without invalidating cadence-change identity; apply, rollback, local cadence edit, mode/permission transition or re-enrollment must invalidate it, including change-away/change-back. Due dispatch ownership still uses exact current due/revision checks. This is not permission to reinterpret version 1 or migrate historical source evidence into the new version.

Before native rollback usability, require new authenticated Schedule Apply provenance in the same durable commit, source consumption, fresh versioned preview, verified signed intent and independently enabled rollback execution. The current native model marker proves a local model transformation only. Preserve source freshness (currently one hour), bounded preview/intent expiry and uncertainty blockers; no reused legacy action/preview or rollback-created source candidate. Define native rollback's future due from execution time plus restored interval, never restore a past run or uploaded data. New fields and validators/shared fixtures need separate contract approval. No current consumer should accept that proposed version yet.

## Recovery And Diagnostics

- Never-started `claimed` dispatch: allow only a separately authorized, exact owner/hash/context-bound cancellation after durable phase verification. Cancellation racing start must have one CAS winner. It is not a generic expired-lock reset and does not authorize a new scan automatically.
- `started` or `recovery_required`: retain evidence and pause admissions regardless of lease age. Reconcile scan/queue effects with client-local administrator review. Time elapsed, an empty queue or a successful upload is not proof of no scan effect. No current API safely clears started uncertainty.
- Failed completion/recovery-marker write: retain the durable started state, report a generic unconfirmed disposition, and do not infer rollback or successful due advancement.
- Context/mode transition during work: close new admissions and invalidate future evidence; preserve old owner/binding records for inspection. Do not rebind uncertain work to a new enrollment or permit legacy fallback.

Future diagnostics should distinguish model cadence/due, actual wake-up availability, last guarded disposition, admission state and uncertainty using bounded allowlisted values/timestamps. Export no binding, journal/owner internals, option/cron arrays, paths, package identifiers, keys, signatures or raw callbacks. Stored evidence is not a live health or backup guarantee. A new public status contract/UI remains gated; existing read-only classifications and literal-false execution flags stay intact.

## Proof Required Before Activation

| Boundary | Minimum additional proof; not performed by this design |
| --- | --- |
| Mode and permission | Strict local-only inputs, default disabled/no reads, unknown versions, partial consent writes, revocation/re-pair/key rotation, stale cache, current settings, old enrollment and normal polling timestamp changes. |
| Entry points | All scheduled/admin/CLI/remote/direct facade and raw queue callers inventoried; no boolean bypass, recursive guard, scan-before-rejection or upload-after-rejected CLI run; accepted remote actions revalidated. |
| Migration/lifecycle | Competing admissions during preparing/draining, unsupported/malformed/unfinished state, failed mode and wake-up writes, deactivate/reinstall, verified return to legacy, blocked downgrade, per-site scope. |
| Wake-up liveness | Duplicate/early/late/missing wake-ups, old callbacks, concurrent cron writers, registration failure, no unrelated event changes and no catch-up burst. |
| Effects/recovery | Crash before/after queue writes and completion, durable cancellation versus start, expiry without takeover, failed recovery writes, independent uploads/retention effects, protected records at capacity. |
| Evidence | Version-1 staleness retained; new cadence identity survives only ordinary due advancement, invalidates on semantic changes, cannot bless legacy journals or unauthenticated model commits; cross-plugin fixtures reject new versions until approved. |

Use pure tests first. Any real WordPress/database/concurrency proof requires a separately confirmed disposable target and mutation scope; mocks and the prior harmless counter fixture do not establish real queue/Drime safety. Before shipping, prove supported PHP/WordPress behavior and lifecycle with release opt-in off. Real client activation, remote execution, packages and live deployment remain distinct gates.

## Recommended Next Gated Slice

**V2.3 Cadence Authority Entry-Point Policy Foundation**, local-only and dormant. Implement a strict pure admission/mode evaluator with explicit internal entry-point classifications and a table-driven policy matrix for legacy, preparing, active, draining and invalid state; cover scheduled versus manual/CLI/remote scans and transition-related queue mutations. The evaluator may consume only explicitly injected trusted local snapshots; it must not authenticate arbitrary payloads or treat its result as an execution token.

Keep production constructors/callers unchanged, no permission control or option/schema writes, no actual mode transition, no site/DB access, no new accepted wire fields, and rollback disabled. This is the smallest worthwhile next implementation of the reviewed policy, not activation itself. It needs explicit approval for the new permission/entry-point semantics even though dormant. Durable consent/lifecycle storage, actual facade/wake-up integration, native evidence versioning and fixture effect proof remain subsequent bounded approvals; do not bundle them silently.

## Verification Record

This slice changes dashboard documentation only. Source inspection establishes the inventory and current limitations, not future runtime guarantees. No uploader files, tests, production factory, settings, schema, protocol, lifecycle hooks or site state are changed. Dashboard remains `0.1.77`; uploader remains `0.5.25`. No release/package is justified for design alone.

Executed local validation on 2026-10-14:

- Dashboard `php vendor/bin/phpunit --do-not-cache-result`: 299 tests / 1423 assertions, two existing Windows skips. `php vendor/bin/phpcs`: 138 files passed.
- Uploader `php vendor/bin/phpunit --do-not-cache-result --filter 'GuardedScan|CronGuarded|CadenceAuthority'`: 90 tests / 449 assertions, no skips. These preserve the existing dormant contracts, including due-advancement staleness; they do not test the proposed lifecycle/policy.
- Dashboard `npm run check:structure`, `npm run build`, `npm audit --audit-level=moderate`, `php ./composer.phar audit`, and `git diff --check`: passed. Audits report no vulnerabilities; generated assets are unchanged.
- Source-reference check resolved all 12 explicitly quoted uploader PHP paths and the local implementation-plan link. Direct call-site search confirmed scheduled, administrator, CLI and remote scan callers plus scan/requeue queue writes. Diff review contains only this design and the implementation-plan update.

No new tests or runtime proof are appropriate for an unimplemented design. No site/SQL/browser access or external archive was required; clean committed baselines provide the source checkpoint. Exact-commit push/CI verification remains the publication completion gate.

Publication receipt: design commit `ebd9e2ce021d115bb996668f53afd36c14292fec` is pushed to `origin/master`; exact-commit [CI 38534933714](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/38534933714) passed. PHP 7.4 and 8.3 each passed 299 tests / 1430 assertions without skips, plus PHPCS, structure, build and dependency audits. Uploader remains unchanged at `b62da2cd4d970329feb90986a850afdac42bf2c9`. The design milestone is complete, not activation safety proof. Next gate is the explicitly approved dormant Entry-Point Policy Foundation described above; no maintenance/package batch is warranted to avoid that decision.

## Entry-Point Policy Follow-Up (2026-10-14)

That foundation was separately approved and implemented as a pure classifier with trusted injected projections, synthetic policy-matrix tests and scoped reviews. Uploader `docs/V2_3_ENTRY_POINT_POLICY_FOUNDATION.md` records the exact contract and verification. It is not loaded in production; all existing callers, schema-2 storage, version-1 evidence and execution flags remain unchanged. Positive dispositions require another policy/atomic guard, never grant execution. The next meaningful approval is **local-only V2.3 Cadence Authority Durable Consent and Lifecycle Safety Foundation**, not real activation or rollback. Do not treat synthetic consent/handoff fields as an installed setting, a storage migration or authenticated evidence.

## Consent/Lifecycle Context Follow-Up (2026-10-14)

The subsequently approved dormant schema-3 foundation and isolated SQL/process proof preserve disabled authority and demonstrate tested persistence, not authenticated permission or activation history. [Trusted Consent Context and Lifecycle Admission Design](V2_3_TRUSTED_CONSENT_CONTEXT_DESIGN.md) now refines raw authoritative readers, unknown history, local consent provenance, observed epochs/context, failed revocation and the separate pre-admission/owned-dispatch contracts. No schema-3 row can establish active or verified-legacy permission. Its next separately gated slice is the dormant **Trusted Context Observation Safety Foundation**, using injected synthetic providers and no production wiring, actual consent, site/SQL writes or execution.
