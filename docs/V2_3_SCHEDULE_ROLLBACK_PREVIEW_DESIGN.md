# V2.3 Schedule Rollback Preview Design

Status: planning/design slice only. This document does not approve implementation, release, deployment, live-site writes, broad client enablement, `schedule_rollback_preview` runtime behavior, `schedule_rollback` runtime behavior, backup creation, cleanup/delete actions, restore actions, WPvivid schedule management, server-runner schedule management, arbitrary cron editing, arbitrary setting mutation, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`
- `docs/V2_3_SCHEDULE_APPLY_IMPLEMENTATION_PLAN.md`
- `docs/V2_3_ROLLBACK_METADATA_CAPTURE_PLAN.md`
- `docs/V2_3_SCHEDULE_ROLLBACK_READINESS_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`

## Goal

Design the next safe runtime-adjacent step before any rollback execution: a non-mutating `schedule_rollback_preview` action that asks the client uploader whether one previous `schedule_apply` action can still be rolled back safely.

The preview answers:

- Which successful apply action is being considered for rollback?
- Is rollback metadata still present, unexpired, and support-safe?
- Does the current local schedule still match the post-apply schedule fingerprint captured by the client?
- Which cadence would be restored if a later, separately approved rollback apply action existed?
- Why is rollback preview unavailable, stale, expired, unsupported, or unsafe?

The preview must not change cadence, mutate WP-Cron, touch WPvivid/server-runner schedules, create/delete backups, clean up files, restore data, alter Drime, or change credentials.

## Current Baseline

The released V2.3 schedule-management baseline is:

1. `schedule_preview`: implemented, non-mutating, scoped to `alynt_scan_upload`.
2. `schedule_apply`: implemented, guarded, scoped to `alynt_scan_upload`, disabled by default per client, requires a fresh successful preview and client-local Schedule Apply opt-in.
3. Rollback-readiness metadata: displayed as support evidence only.
4. `schedule_rollback_preview`: reserved, not implemented.
5. `schedule_rollback`: reserved, not implemented.

## Non-Goals

This design does not include:

- adding `schedule_rollback_preview` or `schedule_rollback` to current `allowed_actions`;
- rendering dashboard rollback controls in the current release;
- adding dashboard dispatch code;
- adding client action handlers;
- adding client-local Schedule Rollback opt-in controls;
- changing the database schema;
- changing action-history storage shape;
- changing current release packages or live sites;
- implementing mutating rollback execution.

## Required Preconditions Before Runtime Implementation

Do not implement `schedule_rollback_preview` until all of the following are true:

- at least one low-risk pilot has proven `schedule_apply` and support-safe rollback metadata;
- the pilot has been returned to its intended cadence after proof;
- rollback metadata includes source apply action ID, schedule ID, previous cadence, applied cadence, previous next-run evidence, applied next-run evidence, before/after redacted fingerprints, capture time, and expiry time;
- dashboard action history displays rollback-readiness metadata without raw internals;
- tests prove `schedule_rollback` remains unavailable;
- the user explicitly approves moving from this design into runtime implementation.

## Proposed Capability Shape

If runtime preview is later approved, the client may advertise support through `remote_actions.schedule_management` only after a client-side release ships with preview support disabled by default.

Candidate additive fields:

```json
{
  "remote_actions": {
    "allowed_actions": [
      "scan_upload_now",
      "schedule_preview",
      "schedule_apply"
    ],
    "schedule_management": {
      "protocol_version": 2,
      "capability_version": 1,
      "enabled": true,
      "apply_supported": true,
      "rollback_preview_supported": false,
      "rollback_supported": false
    }
  }
}
```

Runtime implementation rule:

- `rollback_preview_supported: true` must require a separate client-local Schedule Rollback Preview opt-in.
- `rollback_supported: true` must remain false until a later rollback-apply slice is designed, implemented, and approved.
- `schedule_rollback_preview` must not be advertised in `allowed_actions` until both dashboard and client support are released and the client-local preview opt-in is enabled.

## Proposed Request Shape

Draft-only signed action extension:

```json
{
  "action_type": "schedule_rollback_preview",
  "schedule_rollback_preview": {
    "schedule_id": "alynt_scan_upload",
    "source_apply_action_id": "00000000-0000-4000-8000-000000000000",
    "rollback_metadata_fingerprint": "sha256-redacted-metadata-fingerprint",
    "capability_version": 1
  }
}
```

Rules:

- `schedule_id` must equal `alynt_scan_upload`.
- `source_apply_action_id` must reference one previous successful client-confirmed `schedule_apply`.
- `rollback_metadata_fingerprint` must match the client-owned metadata for that apply.
- No target cadence is accepted from the dashboard.
- No raw cron, WP-Cron array, crontab fragment, option name/value, filesystem path, username, package name, Drime identifier, credential, token, signed URL, shell command, SQL, or arbitrary settings value may appear.

## Proposed Client Evaluation

The client owns all rollback-preview eligibility checks:

1. Verify V1 pairing and V2 signed action request.
2. Confirm action type is allowlisted and locally opted in.
3. Confirm `schedule_id` is exactly `alynt_scan_upload`.
4. Find the referenced successful `schedule_apply`.
5. Load the client-owned rollback metadata.
6. Confirm metadata fingerprint and capability version.
7. Confirm metadata is unexpired.
8. Recompute current schedule fingerprint.
9. Confirm current schedule still matches the post-apply fingerprint.
10. Confirm previous cadence remains allowlisted and safe.
11. Return a support-safe preview result without mutation.

The client must fail closed when any check is missing, unsupported, stale, expired, mismatched, or unsafe.

## Proposed Result Shape

Draft-only client result summary:

```json
{
  "action_type": "schedule_rollback_preview",
  "state": "succeeded",
  "result_code": "schedule_rollback_preview_ready",
  "result_summary": "Schedule rollback preview is ready. No schedule was changed.",
  "schedule_rollback_preview": {
    "schedule_id": "alynt_scan_upload",
    "source_apply_action_id": "00000000-0000-4000-8000-000000000000",
    "current_cadence": "every_30_minutes",
    "rollback_cadence": "every_15_minutes",
    "current_next_run_at": "2026-09-20T14:30:00Z",
    "rollback_next_run_estimate": "2026-09-20T14:15:00Z",
    "would_change": true,
    "capability_version": 1,
    "expires_at": "2026-09-20T15:00:00Z"
  }
}
```

Failure examples:

- `schedule_rollback_preview_not_supported`
- `schedule_rollback_preview_opt_in_required`
- `schedule_rollback_preview_metadata_missing`
- `schedule_rollback_preview_metadata_expired`
- `schedule_rollback_preview_fingerprint_mismatch`
- `schedule_rollback_preview_current_state_changed`
- `schedule_rollback_preview_unsupported_schedule`
- `schedule_rollback_preview_unsafe_previous_cadence`

## Dashboard UI Design

Until runtime preview is approved and released:

- keep rendering rollback as unavailable;
- keep showing rollback-readiness metadata as evidence only;
- render no preview/apply rollback form, button, or link.

If runtime preview is later approved:

- show a distinct "Preview Rollback" control only on Site Detail and only when the latest client report explicitly supports rollback preview;
- describe it as non-mutating;
- require nonce/capability checks and a clear confirmation that no schedule will change;
- show source apply action, current cadence, rollback target cadence, and expiry;
- record it in Remote Action History as `Schedule Rollback Preview`;
- keep `Schedule Rollback` apply hidden/unavailable.

## Dashboard Validation Rules

The dashboard must:

- build the request only from stored support-safe action-history metadata and latest client capability;
- never let the operator choose a free-form target cadence;
- never accept raw cron or arbitrary settings input;
- reject missing or stale source apply metadata before dispatch;
- treat preview results as support-safe evidence only;
- keep backup/source freshness classification independent from rollback-preview state.

## Test Plan For A Later Runtime Slice

Dashboard tests:

- rollback-preview controls remain hidden without explicit capability;
- controls remain hidden for unsupported schedule IDs;
- request payload contains only source apply reference, metadata fingerprint, schedule ID, and capability version;
- no target cadence, cron, path, option, command, or credential field is accepted;
- action history labels preview as non-mutating and distinct from rollback apply;
- stale or missing apply metadata blocks dispatch;
- `schedule_rollback` controls remain hidden.

Uploader tests:

- preview action rejected before client-local preview opt-in;
- unknown schedule IDs rejected;
- missing metadata rejected;
- expired metadata rejected;
- mismatched metadata fingerprint rejected;
- changed current schedule fingerprint rejected;
- preview is non-mutating;
- result payload contains no raw cron/options/paths/commands/credentials;
- `schedule_rollback` remains rejected.

Cross-plugin tests:

- successful apply captures metadata;
- preview references the captured apply;
- preview result reconciles into dashboard history without mutation;
- subsequent `schedule_preview`, `schedule_apply`, and `scan_upload_now` continue to work unchanged.

## Release And Rollout Plan For A Later Runtime Slice

If approved later:

1. Update protocol and threat model from reserved to implemented-preview-only status.
2. Implement uploader support first, disabled by default.
3. Release uploader.
4. Implement dashboard UI/dispatch second, hidden until client capability appears.
5. Release dashboard.
6. Enable on one low-risk pilot only after explicit approval.
7. Prove preview is non-mutating.
8. Do not implement rollback apply in the same slice.

## Acceptance Criteria For This Design Slice

- Dedicated `schedule_rollback_preview` design exists.
- The design keeps rollback preview non-mutating and client-owned.
- The design explicitly keeps `schedule_rollback` execution out of scope.
- Roadmap/protocol references point to this design without approving runtime behavior.
- No source code, release, deployment, live site, client setting, production data, or runtime behavior changed.

