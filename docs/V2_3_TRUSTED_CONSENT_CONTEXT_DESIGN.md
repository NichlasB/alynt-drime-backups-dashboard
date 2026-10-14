# V2.3 Trusted Consent Context and Lifecycle Admission Design

Approved local-only, design-only on 2026-10-14. Source checkpoints: dashboard `1f78c44`, uploader `b5813a7`. This refines [the activation/cohabitation design](V2_3_CADENCE_AUTHORITY_ACTIVATION_DESIGN.md) and advances [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) after the dormant schema-3 and isolated storage milestones. It approves no code, schema, permission control, site access, runtime wiring, activation, release, deployment or rollback execution.

## Preferred Decision

Introduce a separately reviewed **read-only trusted local context observation boundary**, before implementing a consent command or activation. It must distinguish authoritative facts from normalized/defaulted values and authenticated consent from a synthetic model boolean. An exact-shaped array, local binding hash, dashboard token, signed remote action or successful CAS is not administrator authorization.

**No current schema-3 state can produce an active or verified-legacy policy projection.** Schema 3 has only preparing/draining/disabled phases, requires authority disabled, records synthetic consent, and has `new_model`/`unknown` history rather than activation/handoff provenance. Do not fabricate missing policy fields, interpret those history labels as never-activated, downgrade schema or add `active` to validation to make an adapter work. A valid negative observation is useful; positive admission remains a later permission/provenance and lifecycle gate.

All decisions below are candidate requirements, not installed APIs or persisted/public fields. Production continues using the legacy scheduler; the new policy/model are not wired into it. A shadow/dormant reader must not block existing production behavior simply because current state cannot establish authority history.

## Source-Backed Boundary Inventory

Uploader paths below are relative to the separate `alynt-drime-backups-uploader` repository.

| Source | Current behavior and consequence |
| --- | --- |
| `includes/class-cadence-authority-entry-point-policy.php` | Pure exact 16-field private classifier. Four-field result always has dispatch/execution false. Missing policy blocks scans, upload/abort are neutral, rollback always blocked. It authenticates neither input nor provenance. |
| `includes/class-cadence-authority-lifecycle-store.php` | Strict schema 3; consent/revoke/rebind atomically change permission generation and schedule revision, but authenticate no caller. All authority remains disabled. Failed writes can retain prior consent. |
| `includes/trait-cadence-authority-lifecycle-preparation.php` | Explicit preparation, no read initialization. Older migration retains terminal records, rejects unfinished work, sets unknown history and no consent. New row is not never-activated proof. |
| `includes/class-schedule-safety-store.php` | Authoritative reload plus three-attempt exact-byte CAS; rereads binding on contention. Internal clock/backend exceptions are not a redacted request response or a guaranteed no-write result. |
| `includes/class-schedule-safety-option-cas.php` | Authoritative state reads, prepared conditional updates and cache invalidation. Its option allowlist is safety state/cron only; do not silently broaden it to all settings or credentials. |
| `includes/class-settings.php` | `get()` merges defaults and uses get-option; `update()` writes a separate option. `site_uuid()` can generate/persist identity. None proves current authority consent. |
| `includes/class-dashboard-connection.php` | `get()` merges/sanitizes stored state using cached option API. `site_uuid_for_remote_actions()` reads existing settings without generating identity, but is still not an authoritative raw permission reader. |
| `includes/trait-dashboard-connection-sanitizers.php` | Normalizes/coerces flags/timestamps and fills defaults. Helpful for existing behavior, insufficient to prove raw field presence/types for new trusted admission. |
| `includes/trait-cadence-authority-evidence-context.php` | Existing version-1 binding includes site UUID, dashboard/client origins and site ID, polling/action key context plus pairing/remote-opt-in times; requires paired/status/remote opt-in. No authenticated cadence consent/history. |
| `includes/class-cadence-authority-evidence.php` | Redacted evidence reports model observation, not execution permission. Its status projection is not the private policy projection. Keep version 1 and due-advancement staleness unchanged. |
| `includes/trait-plugin-admin-actions.php` | Existing administrator actions use manage-options capability and action nonce. This supplies a future local authorization pattern, not an already implemented consent handler. |
| `includes/trait-dashboard-connection-state-transitions.php` | Revoke/remote-disable change a separate option; authenticated-read timestamp updates rewrite a copied whole state. Nonsemantic timestamp changes must not invalidate authority identity, but these writers are not atomically coupled to the lifecycle CAS. |
| `includes/trait-dashboard-connection-enrollment.php` | Pairing/remote opt-in uses separate token/context validation and option writes. Pairing/remote opt-in must not grant cadence authority or transfer old consent. |
| `includes/class-cadence-authority-scan-dispatcher.php` | Existing owner-context callback requires exactly binding/scan-enabled and compares it around work. It is a different contract from the 16-field pre-admission projection; synthetic injection is not trusted permission. |
| `includes/class-cron.php`, `includes/class-plugin.php` | Optional dispatcher exists, but production constructs Cron with only the plugin. No real admission/context adapter is installed. |

The completed uploader `docs/V2_3_LIFECYCLE_ISOLATED_STORAGE_PROOF.md` establishes tested local CAS ordering, retained uncertainty and interrupted acknowledgement. It does not establish administrator consent, global writer coordination, actual effects or distributed cache/host-failure safety. That proof is historical evidence, not rerun by this design.

## Separate the Three Contracts

1. **Consent command:** future client-local authenticated mutation, with explicit intent and epoch/context revalidation. It may record a narrowly scoped preparing grant only after a separately reviewed provenance/transition contract; a store method alone cannot be that boundary.
2. **Pre-admission observation:** explicit read-only collection of current local facts, with no initialization, repair, identity generation or fallback. It may classify a candidate entry point but grants no ownership/effects. It must reject incomplete provenance rather than fill boolean history fields.
3. **Owned-dispatch revalidation:** future owner/hash/revision/due/epoch-bound checks after claim/start. Its own pending claim is legitimate. Do not feed a pre-claim `pending != none` rejection or volatile observation timestamps into the existing exact two-field context callback; that would reject every legitimate owned callback or mask missing epoch protection.

No caller-supplied bypass switch, serialized projection, admin request body, REST status or remote token may substitute for any of these contracts. Invocation provenance comes from closed local construction and reviewed request/writer paths, not from the spelling of a `trusted` boolean.

## Authoritative Read and Snapshot Requirements

The future observer defaults to dormant with zero reads/hooks until explicitly invoked by a trusted caller or test fixture. Use fixed, separately reviewed readers for the current blog's raw settings/connection, strict lifecycle snapshot and injected UTC clock. Do not call install helpers, `Settings::site_uuid()`, preparation, consent or scheduling APIs. Missing identity/state is unavailable, not initialization or verified legacy.

Require actual present, bounded scalar values; do not turn missing flags, numeric/string booleans, malformed serialization or normalized defaults into permission. Readers must distinguish absence, corruption, storage failure and a valid false value. They must bypass or independently prove option-cache freshness at the intended boundary, not assume two reads through the same cache are independent. Raw connection/private verifier material stays inside the binding builder and is never logged/exported or returned to callers.

Initial candidate authority remains single-site and paired/remote-opt-in-bound, consistent with the existing reviewed binding. Reuse its version-1 semantic identity without changing public evidence; adding/removing domain inputs or supporting unpaired authority is a separate version/trust decision. Pure binding compatibility tests must protect key/credential/origin/site/pairing-time changes and exclusion of ordinary polling timestamps. Current remote opt-out blocks that candidate authority; explain scan suspension and require explicit reviewed handoff before legacy resumes. Do not silently remove the dependency for liveness.

Read the lifecycle and context around one another, compare strict semantic identity, permission epoch, lifecycle/revision and relevant settings, and reject changes observed during collection. The model epoch comes from authoritative storage, not a posted counter, timestamp or caller echo. This detects some interleavings, **not** all ABA races or atomicity across options. Future meaningful settings/enrollment writers need an admission-closing generation/transition protocol; existing timestamp writers that copy a whole option require review so a stale copy cannot resurrect a semantic flag. Do not rewrite those writers in this design.

An observation is short-lived, one-operation data. The existing policy permits at most 300 seconds, requires observed-at <= now < expiry and rejects overflow; that maximum is not a cache TTL, lease or permission lifetime. Prefer rebuilding immediately before admission and before a CAS/start/effect boundary. A later read cannot stop changes immediately afterward or cancel an already-running queue/upload operation.

## Private Projection Mapping and Current Gaps

| Existing policy field group | Required source; current limitation |
| --- | --- |
| `policy_version`, `mode`, `lifecycle` | Versioned supported authority semantics and validated lifecycle, not status/request labels. Schema-3 phase is non-active; never map absent/disabled state to legacy. |
| `ever_activated`, `handoff_verified` | Durable, reviewed activation/handoff provenance. Neither exists in schema 3; `new_model`/`unknown`, epoch zero, no journal or no pending work cannot supply either boolean. |
| `consent` | Current authenticated client-local grant for this purpose, epoch and context, established by a closed authorized writer. Schema-3 synthetic consent cannot supply this fact. |
| `permission_generation`, `current_permission_generation` | Observed model epoch and an independently refreshed authoritative epoch. Copying one cached value into both does not prove freshness or prevent ABA. |
| `binding`, `current_binding` | Validated stored enrollment binding and freshly reconstructed current local identity. Never accept a request/status binding as the current identity. |
| `auto_scan_enabled`, `model_enabled` | Strict current settings fact and authoritative lifecycle flag. Existing merged/truthy settings getters are not raw proof; schema 3 requires model disabled. |
| `pending` | Conservatively summarize both journals and dispatch, including all claimed/started/recovery states. Expiry never becomes none. If any recovery uncertainty exists, classify it accordingly; do not hide it behind a newer terminal receipt. |
| `next_due_at`, `observed_at`, `expires_at` | Valid model due and bounded injected UTC observation window. Do not infer cadence consent from due time or normal evidence freshness. |

Until every required fact has a trustworthy source, the observer must withhold a compatible positive projection and produce a bounded generic unavailable/blocked result; the existing classifier can be called with absent policy to remain blocked. Non-admitting schema-3 observations may be retained internally for tests/diagnosis, never interpreted as active/legacy permission. No public payload, new stored field, schema version or error-code vocabulary is finalized here.

## Future Local Consent Authorization and Lifecycle Rules

A future command uses a dedicated client-local administrator surface, the existing narrow manage-options capability pattern, an action-specific nonce and literal intentional enable/revoke intent. Nonces prove request intent, not authorization. Do not reuse dashboard pairing, remote scan, Schedule Apply or rollback-preview opt-in. Derive binding, clock and current epoch on the server; any submitted expected epoch is only a concurrency precondition. Preserve purpose separation and do not log nonce/token/credential material.

WordPress nonces are not one-use consumption records. Duplicate intent must be protected by the exact expected epoch and the separately reviewed command/idempotency contract, not by assuming nonce verification rejects every replay. A successful grant/revoke must not automatically refresh and retry the epoch from a stale submission.

Scheduled workers do not need an administrator session. They would rely on a durable site-owned grant established by that authorized local writer, then revalidate current epoch/context and independently acquire guarded dispatch ownership. A future audit/provenance design must specify grant scope and revocation lifetime without treating an issuer tag, stored user ID or signature-shaped string as proof by itself. Arbitrary same-privilege plugin/DB writers remain outside this integrity boundary; no cryptographic proof or new secret/HMAC scheme is invented here.

Authenticated grant must not bless an existing synthetic `consent: true` row. Preparation and consent provenance need an explicitly versioned storage/transition review; retain old journals, unknown history and disabled authority. No active phase, history inference, provenance migration or activation-time generation is introduced by this design. Enrollment/key/origin replacement cannot inherit consent. Off/on context changes need epoch invalidation; a matching hash after change-away-and-back alone is not sufficient.

Revocation, rebind and meaningful permission changes must close admissions durably before activation-related or effectful transitions can proceed. Failure to persist closure cannot be reported as disabled: the old durable grant may remain. A future active-mode design needs a provable closure/failure policy before shipping; generic error handling or a transient in-memory denial cannot revoke other processes. Existing independent emergency settings remain unchanged and are not an invented authority fallback. Preserve draining journals on partial failure; never reset epochs, delete uncertain work or infer successful handoff from time elapsed.

## Failure, Ambiguous Outcome and Privacy

Missing/unsupported/corrupt/expired context, invalid UTC values, changed epochs/bindings, pending work and read errors all prevent admission. Catch internal provider exceptions at any future request adapter, expose only stable allowlisted reasons/false execution flags, and keep raw stacks/SQL/options/keys/owners private. A failed read does not authorize initialization; a failed grant/revoke does not authorize retry or active-mode fallback.

After a write error, exception or lost acknowledgement, re-read authoritative state under unchanged authenticated intent and inspect the exact epoch/context/result. Do not automatically replay an old grant, refresh its epoch, reverse the operation or declare no write. Even a matching resulting value is observation, not fresh request permission or permission to execute. The isolated after-commit termination proof is one concrete outcome, not certification of outages at every database-commit boundary.

Upload-next/abort remain neutral classifications with their independent existing authorization, worker and retention behavior. Consent revocation is not cancellation of uploads or rollback of Drime effects. `schedule_rollback` stays rejected; no context observation, nonce or grant overrides that boundary.

## Required Test Layers and Acceptance Gates

| Layer | Future evidence needed, not supplied by this design |
| --- | --- |
| Pure/injected observer | Default zero reads; absent/malformed/extra/type/oversize fields; clocks/expiry/overflow; true versus truthy flags; wrong site/context, revoked/ABA epoch, history unknown; schema 3 never active/legacy; stable redacted results and unchanged inputs. |
| Reader/provider compatibility | Raw field presence versus defaults/coercion; stale caches; no UUID creation/option writes; binding compatibility; polling timestamp exclusion; auto-scan false; unsupported schema and contradictory observations; provider exceptions. |
| Local request authorization | Unauthenticated user, missing capability, wrong/replayed nonce/intent, forged binding/epoch, separate-purpose token, cross-site request and failed/ambiguous grant/revoke. Test the real handler; mocks cannot certify WordPress authorization. |
| Owned dispatch/effects | Context/epoch changes before claim, after claim/start and around queue effects; own pending claim versus foreign uncertainty; atomic fencing/retained recovery and no stale replay. The current two-field callback is not certification of the proposed richer contract. |
| SQL/process/lifecycle | Separate approved disposable proof for any new schema/provenance/writer coupling, before/after-commit interruption, uncoordinated connection/settings writer, failed closure, downgrade/handoff, deactivate/reinstall and supported cache/environment scope. |

Before real activation, all scan/queue/manual/CLI/remote callers and relevant local writers must use the reviewed boundary, with explicit compatibility, wake-up, recovery and lifecycle proof. A dormant successful unit test or source review is not release/runtime permission. Full applicable pre-release workflows and real lifecycle acceptance remain required before shipping an active feature; no new full-suite clearance is claimed here.

## Exact Recommended Next Gated Slice

**Local-only V2.3 Trusted Context Observation Safety Foundation.** Implement the bounded dormant read/validation boundary with explicitly injected raw settings/connection/lifecycle providers, strict semantic binding/current-epoch observation, no read-side identity creation or state writes, and adversarial unit/provider tests. Keep it outside production construction/includes; use disposable in-memory inputs only, with no site/SQL/credential or actual permission operations. Withhold active and verified-legacy projections from every current schema-3 state, and keep execution flags false. This is a new permission/context interpretation boundary requiring separate approval, not actual consent/activation or a broad writer refactor.

Follow-on gates are authenticated local consent/provenance storage and command design/implementation, its isolated WordPress authorization/concurrency proof, then separately reviewed owner/facade/writer/wake-up integration and lifecycle/release/activation. Do not combine them into the reader foundation or add cleanup/package work to avoid the decision.

## Validation and Ownership

This slice changes dashboard documentation only. Source baselines remain recoverable committed checkpoints; uploader is read-only and requires no new restore archive. No LocalWP, Novamira, browser, SQL, live-site, automation, release or deploy operation is needed or performed. Dashboard stays `0.1.77`; uploader stays `0.5.25`. No new tests are appropriate for unimplemented candidate semantics; existing regression checks preserve the current contracts. Executed local checks and exact-commit publication results are recorded after verification, not inferred from historical runs.

Executed on 2026-10-14: uploader `php vendor/bin/phpunit --do-not-cache-result --filter 'CadenceLifecycle|CadenceAuthority|GuardedScan|CronGuarded'` passed **302 tests / 1035 assertions**, no skips. These test the unchanged model/policy/dispatcher, not this future adapter or authentication. Dashboard `php vendor/bin/phpunit --do-not-cache-result` passed **299 tests / 1423 assertions**, two existing Windows skips. `php vendor/bin/phpcs` passed 138 files; `npm run check:structure`, `npm run build`, `npm audit --audit-level=moderate`, `php composer.phar audit` and diff checks passed. Both audits reported zero vulnerabilities; generated assets stayed unchanged. Source-reference check resolved all 16 quoted uploader PHP files and the two local document links. No tests/assertions/skips/dependencies were changed. Uploader remains clean/synced at `b5813a7`; exact-commit dashboard CI is the publication completion gate.

Publication receipt: design commit `d9512e4064a3309c0632a19e4303d98bd23bef44` is pushed to `origin/master`; exact-commit [CI 38559459861](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/38559459861) passed PHP 7.4/8.3, each 299 tests / 1430 assertions without skips, plus PHPCS/structure/build/dependency audits. The approved documentation milestone is complete, not reader implementation, authenticated permission or runtime safety proof. The uploader remains unchanged. This receipt-only follow-up changes no tested code and also requires exact-head CI verification before handoff.

## Observation Foundation Follow-Up (2026-10-14)

The separately approved dormant observer now validates exact raw fact slices against the strict schema-3 model, compares fresh observed epochs/context and returns redacted non-admitting dispositions. It is test-loaded only, creates/writes no state or identity and cannot derive active/verified-legacy permission. Uploader `docs/V2_3_TRUSTED_CONTEXT_OBSERVATION_FOUNDATION.md` records 85 focused cases / 503 assertions, full regression and proof limits. Actual provider freshness, authenticated consent, writer/effect fencing and provenance remain unimplemented. The next meaningful gated slice is **local-only V2.3 Authenticated Local Consent and Durable Admission-Closure Design**, with production wiring, actual permission, activation and rollback still disabled.

## Consent And Closure Design Follow-Up (2026-10-14)

The separately approved [authenticated consent and admission-closure design](V2_3_AUTHENTICATED_CONSENT_ADMISSION_CLOSURE_DESIGN.md) specifies candidate local command authorization, successor-ledger provenance/receipts, non-replaying duplicate and superseded outcomes, closure-before-semantic-write ordering and failed/ambiguous closure limits. It changes documentation only; schema-3 synthetic consent remains untrusted, and no provider/handler, migration, activation or rollback is installed. The next meaningful gate is **local-only V2.3 Consent Ledger and Command Receipt Safety Foundation**, with in-memory fixtures and production wiring unchanged. Real WordPress authorization, SQL/process, writer/effect and lifecycle proof are not replaced by that dormant model.
