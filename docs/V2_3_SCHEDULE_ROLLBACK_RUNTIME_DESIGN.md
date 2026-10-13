# V2.3 Schedule Rollback Runtime Design

Status: design-only candidate, opened with user approval on 2026-10-13. Runtime implementation, protocol activation, client opt-in, release, deployment, and live proof are not approved. `schedule_rollback` remains reserved, rejected, and absent from runtime controls and `allowed_actions`.

## Product Decision

Evaluate one narrowly bounded undo of a client-confirmed `schedule_apply` that changed the `alynt_scan_upload` cadence. The target is the previous client-owned cadence, never an operator-supplied value. This restores future Alynt scan/upload timing; it does not restore a backup or undo uploads.

The potential benefit is a source-linked recovery flow that reduces the chance of choosing the wrong previous cadence. The existing guarded reverse `schedule_preview` / `schedule_apply` flow already provided recovery during the PureCleanse pilot. That pilot proves preview and reverse apply, not runtime rollback or repeated operational demand. This design does not claim that rollback is essential or that deferral has ended.

Recommendation: first approve the local-only **V2.3 Schedule Rollback Safety Foundation** described below. Prove evidence binding, single consumption, concurrency, and persistence recovery before authorizing endpoint execution or dashboard controls. If those proofs fail or the operational benefit remains too small, retain the existing guarded Schedule Apply recovery flow.

## Scope And Ownership

- Only `alynt_scan_upload`, owned by `alynt_uploader`.
- Only the current supported cadence labels: `every_15_minutes`, `every_30_minutes`, and `hourly`, subject to current client policy and minimum interval.
- Only one successful changed apply, one fresh successful rollback preview, and one single-use rollback candidate on the same enrollment.
- Client-local remote actions, Schedule Apply, Schedule Rollback Preview, and a new separate Schedule Rollback execution opt-in must all be enabled. Execution opt-in defaults to off; neither preview nor apply opt-in implies it.
- Dashboard administrator confirms the exact site, source apply, current cadence, restored cadence, and expiry on Site Detail. No bulk/row-level action or unattended rollback.
- No WPvivid/server-runner schedule changes, schedule disablement, historical next-run restoration, backup creation, restore, cleanup, deletion, Drime actions, credentials, arbitrary settings, commands, or path inputs.

This slice changes dashboard repository documentation only. Companion uploader source is inspected as evidence; no uploader file or site is changed. Future code touches remote authorization and persistent scheduling and requires a separate high-risk implementation approval.

## Inspected Baseline And Gaps

| Source | Observation | Design consequence |
| --- | --- | --- |
| Uploader `includes/trait-remote-action-worker-schedule-fingerprints.php` | `schedule_fingerprint()` hashes schedule ID, cadence, and next-run timestamp. | Normal cron advancement invalidates the baseline. Keep this strict check; do not silently compare cadence alone. |
| Uploader `includes/trait-remote-action-worker-schedule-rollback-preview.php` | Preview uses client-owned apply metadata, checks expiry/fingerprint, expires after 900 seconds, and reports rollback unsupported. | Legacy preview is evidence-only. Execution needs its own explicit contract and complete fresh validation. |
| Uploader `includes/trait-remote-action-worker-schedule-apply.php` | Metadata is constructed after a changed cadence is persisted; its expiry is 3600 seconds. A no-change apply also creates metadata. | Exclude no-change applies. Execution needs durable pre-mutation evidence and crash recovery; historical metadata alone is insufficient. |
| Uploader `includes/class-cron.php` | `apply_scan_cadence()` clears the old scan event before scheduling the replacement. | Do not reuse it as a proven atomic rollback primitive. Test clear/schedule failures and recovery before runtime approval. |
| Dashboard `includes/traits/trait-remote-action-repository-rollback-preview-lookups.php` | Some missing metadata fields are tolerated for preview, including an absent/invalid expiry. | Execution must reject missing, malformed, expired, or incomplete evidence on both sides. |
| Dashboard `includes/traits/trait-remote-action-dispatcher-schedule-rollback-preview.php` | Current dispatcher sends preview only. | No existing rollback execution path is assumed or activated by this document. |

These are source observations and candidate requirements, not newly verified runtime defects or proof that recovery already exists. The critical section for concurrent schedule writers remains an implementation blocker until the Safety Foundation proves it.

## Candidate Contract And Compatibility

Use the existing protocol-v2 signed intent envelope, route, origin/site/key binding, request expiry, and action history. Proposed fields below are design-only, not accepted wire contracts today. Update both plugins' validators and shared fixtures only after implementation approval; do not change v1 polling or weaken existing unknown-field rejection.

Keep existing `schedule_management.capability_version: 1` semantics. Propose an additive `schedule_management.rollback_execution` object with:

- `contract_version: 1`;
- `enabled: true` only while all client-local opt-ins and safety checks pass;
- `requires_fresh_preview: true`;
- `max_preview_age_seconds: 900` (client policy may shorten, never lengthen this ceiling).

Future controls require that object, explicit `rollback_supported: true`, `rollback_preview_supported: true`, matching schedule-level support, and `schedule_rollback` in `allowed_actions`. Missing, inconsistent, unknown-version, stale, disabled, or unsupported capability fails closed. No new opt-in token or key reuse beyond the current enrolled identity is proposed.

Future execution-ready metadata and rollback-preview results additionally carry `rollback_contract_version: 1`. Legacy metadata/previews without this marker remain displayable but never executable. New metadata must bind the client enrollment/key context, source apply, schedule revision, before/after fingerprints, both cadence labels, and capture/expiry times. New previews bind the metadata fingerprint, source apply, same revision and enrollment, current fingerprint, both cadence labels, preview action ID, and creation/expiry times. Hash canonicalization requires shared cross-plugin fixtures; hashes are evidence bindings, not authorization secrets.

Candidate signed request extension:

```json
{
  "action_type": "schedule_rollback",
  "schedule_rollback": {
    "schedule_id": "alynt_scan_upload",
    "source_apply_action_id": "00000000-0000-4000-8000-000000000000",
    "preview_action_id": "00000000-0000-4000-8000-000000000001",
    "preview_fingerprint": "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa",
    "rollback_metadata_fingerprint": "bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb",
    "capability_version": 1,
    "rollback_contract_version": 1
  }
}
```

These seven nested keys are the complete proposed request allowlist. UUIDs and lowercase 64-hex hashes must be structurally validated. Reject extra keys, coercions, unsupported versions, and contradictory records. The target cadence, current state, expiry, and revision are obtained from client-owned records, not supplied by the dashboard. Existing envelope signature/site/expiry/idempotency checks still apply.

## Freshness, State Binding, And Single Consumption

1. Require a successful changed `schedule_apply`, unexpired execution-ready metadata, and a successful execution-ready `schedule_rollback_preview` for that exact apply on the same enrollment.
2. Effective deadline is the earliest of metadata expiry, preview expiry, preview creation plus 900 seconds, and signed-intent expiry. Check strict UTC timestamps and `now < deadline` at dispatch and again immediately before mutation. Queuing does not extend it; expiry at the boundary rejects.
3. Metadata lifetime remains at most 3600 seconds from capture. Do not extend it, regenerate it from dashboard history, or reinterpret legacy preview expiry to authorize execution.
4. Recompute both evidence fingerprints and the current schedule fingerprint from trusted local records/state. Require the current fingerprint to match the captured post-apply state and the preview baseline. Next-run advancement therefore requires rejecting this candidate, even when cadence is unchanged.
5. Require current cadence equals applied cadence, a supported safe previous cadence, enabled automatic scan, exactly one supported owned scan event, and no ambiguous schedule state. Preview warnings such as automatic scan disabled are blocking for execution.
6. Require a client-owned monotonic schedule revision and enrollment binding. An intervening apply, local edit, disable/re-enable, revoke/re-pair, or key replacement invalidates the candidate even if the cadence later returns to the same value. Existing hashes alone must not authorize this change-away/change-back case.
7. Claim the source apply candidate once, durably, under the schedule lock. Two different action IDs/idempotency keys cannot roll back the same apply. Exact duplicates return the original stored outcome without re-execution; conflicting duplicates reject.
8. Success consumes the candidate and every preview of it. Rejected pre-mutation requests may permit a fresh preview while metadata remains valid. A started/uncertain write keeps the candidate blocked until recovery is resolved. Rollback must not generate a new rollback candidate or recursively undo itself.

## Execution And Recovery Requirements

The future worker must recheck signature/policy/enrollment, evidence, expiry, revision, safe current state, and locks at execution time, not only when the REST endpoint accepts the request. No automatic dispatch retry or automatic new rollback action after a timeout.

Before any schedule write, persist a bounded client-local journal in the existing action store if it can provide the required durable semantics. It contains action/source/preview references, enrollment binding, revision, before state, intended cadence, operation phase, and recovery outcome. Dashboard-visible fields remain redacted. If that store cannot support safe claims and crash recovery, stop and seek separate storage/schema approval; do not silently add a table or option.

The critical section must coordinate remote apply/rollback, local cadence changes, activation/rescheduling, and normal cron advancement. A remote-action lock alone does not serialize independent WP-Cron/other option writers. The Safety Foundation must map all writers and demonstrate an enforceable lock or conditional-write strategy without overwriting unrelated cron events. Uncoordinated concurrent writes remain a blocker; a final read followed by an unprotected write is insufficient.

Once validated and journaled, change only the owned scan cadence. Compute a new next run from execution time plus the restored interval; never restore a historical timestamp or trigger scan/upload immediately. Verify the observed cadence and event shape before success. Preserve unrelated cron hooks and settings.

| Failure point | Required behavior |
| --- | --- |
| Evidence, journal, or candidate claim cannot be persisted before write | Reject without schedule mutation. |
| Clearing/replacing the event fails | Re-read actual state; do not claim success or assume the old event survived. |
| Replacement fails after clearing | Attempt bounded restoration of the pre-operation scan schedule only while the ownership/lock guards still hold; verify restoration and retain failure evidence. |
| Crash after claim or after schedule write | On restart, reconcile the durable journal with actual state. Never repeat a write blindly. |
| State matches intended result after crash | Finalize the stored outcome and consume candidate without another schedule change. |
| State matches original result | Close as failure only after verified recovery; keep source blocked against automatic retry. |
| State matches neither or recovery fails | Retain blocked/uncertain evidence, report failure requiring client-local administrator inspection, and prohibit automated overwrite. |
| Dashboard transport times out | Reconcile the same action through normal status polling. A timeout is not proof of rejection or permission to create another action. |

Any local recovery write belongs to a separately approved runtime implementation and must not become a general restore mechanism. Require deterministic failure injection plus isolated WordPress integration proof before a live pilot.

## Rate Limits And Audit

Keep one remote action in flight per site plus the schedule critical section. Proposed rollback limit is one attempt per 60 seconds per site, with success limited by single-use source metadata. Do not inherit the one-hour scan/upload limit in a way that prevents an intended short recovery window. Client policy may be stricter; disabled/expired policy must fail closed. Retain existing idempotency records for at least 24 hours and keep unresolved recovery journals from being evicted by bounded-history pruning.

Dashboard records must include the existing actor/site/action/timestamp/state fields plus source apply ID, preview ID, supported cadence labels, safe hashes/version, and verified recovery summary. Client records establish whether mutation actually occurred. Do not report success from HTTP acceptance, optimistic UI, or a later unrelated cadence snapshot.

Proposed terminal result context contains schedule ID, capability/rollback contract versions, source apply and preview IDs, previous/restored cadence, new next-run time, `changed`, `recovery_state`, and warning codes. Proposed recovery states: `not_needed`, `previous_schedule_restored`, `inspection_required`. Proposed codes: `schedule_rollback_succeeded`, `schedule_rollback_evidence_invalid`, `schedule_rollback_expired`, `schedule_rollback_state_changed`, `schedule_rollback_source_consumed`, `schedule_rollback_persist_failed`, and `schedule_rollback_recovery_required`. Existing generic authorization, busy, unsupported, and rate-limit codes still apply. These are candidate vocabulary, not implemented results.

Never expose raw cron arrays, option values, paths, commands, packages, backup/Drime IDs, credentials, signatures, raw bodies, or journal internals through history, logs, Diagnostics, or support export. Preserve normal backup freshness classification independently of action outcome.

## Operator Flow And Compatibility

1. Site Detail shows an eligible source apply and requests a non-mutating rollback preview.
2. After normal polling confirms preview success, show the client-confirmed before/after cadence and effective expiry. Missing/old evidence or any guard failure means unavailable with a concise reason.
3. A separately approved implementation may show an explicit confirmation: change future Alynt scan/upload cadence on this named site from the applied cadence to the previous cadence. Require WordPress capability, nonce, and confirmation at submit; fetch and validate stored records again server-side.
4. Show accepted/running as pending confirmation, then verified success, failure, or uncertain outcome. Uncertain results require client-local review, not another click.

V1-only and preview-only clients stay monitored; new controls stay hidden for old clients. Do not alter existing `schedule_apply` or legacy preview semantics to fit execution. New execution-ready evidence is additive and versioned. No dashboard database migration is assumed; any storage limitation triggers its own review.

## Required Proof Before Runtime Approval

| Proof group | Required cases |
| --- | --- |
| Binding and compatibility | Same-site source/preview/version/enrollment; wrong site/key/re-pair; legacy/missing fields; malformed expiry; unsupported capability; forbidden payload keys; no-change source apply. |
| Time and state | Exact expiry boundary; metadata expires before preview; queue crosses deadline; normal next-run advancement; intervening local/remote edit; change away and back; unknown/duplicate/missing event; automatic scan disabled. |
| Replay and concurrency | Exact duplicate returns prior result; conflicting duplicate; different IDs for same source; apply/rollback/local edits overlap; cron advances during validation/write; lock expiry; storage pruning. |
| Persistence and restart | Journal/claim failure causes no write; clear failure; replacement failure; recovery failure; crash at every operation phase; journal versus actual-state reconciliation; unrelated events preserved. |
| Result and UI | Acceptance never means success; delayed/out-of-order reports; timeout reconciles same action; unavailable controls; explicit confirmation; privacy in rendering/logs/export; backup evidence classification unchanged. |

Use real isolated WordPress scheduling APIs for persistence/concurrency/restart proof after a separately confirmed LocalWP target and database-write scope. Mocks alone cannot establish atomicity or recovery. Shared canonical fixtures must prove the candidate wire contract in both repositories. Current reserved-action rejection tests remain mandatory until the execution implementation is separately authorized.

## Gated Sequence And Next Slice

1. **This design slice:** dashboard documentation, protocol draft and threat-model draft; validate, commit, push, CI. No release/package is justified for docs alone.
2. **V2.3 Schedule Rollback Safety Foundation:** separate approval for client-first local code and focused tests proving versioned evidence, single-use claims, schedule writer coordination, and fault/restart recovery. Keep `schedule_rollback` unadvertised/rejected and dashboard controls absent. Confirm clean baselines/restore points in both repos first. Any WordPress/database proof needs its own isolated target confirmation.
3. **Guarded execution implementation:** only after Foundation evidence and product justification are reviewed; separately approve client opt-in, endpoint/worker execution, dashboard dispatch/confirmation/history, and the final protocol/threat-model contract. No schema change is implicitly authorized.
4. **Release/package and deployment:** review both plugins, packages, compatibility and recovery runbook; uploader first with execution opt-in off. Site deployment and enablement remain separate approvals.
5. **One live pilot:** select a low-risk site at that time, require fresh restore points and explicit schedule-mutation scope, prove preview/rollback/reconciliation, verify intended cadence and subsequent scan scheduling, and disable temporary opt-in. The historical PureCleanse preview approval does not authorize this pilot. Broad rollout requires further approval.

Completion of this document means the design is reviewable, not that the runtime requirements are implemented or tested. The next gated slice is **V2.3 Schedule Rollback Safety Foundation**, local-only and client-first; full rollback execution remains deferred until its evidence passes.
