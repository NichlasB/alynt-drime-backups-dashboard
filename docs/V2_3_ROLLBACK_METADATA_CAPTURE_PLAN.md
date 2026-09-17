# V2.3 Rollback Metadata Capture Plan

Status: local implementation slice. This document does not approve release, deployment, live-site writes, broad client enablement, `schedule_rollback` runtime behavior, backup creation, cleanup/delete actions, restore actions, WPvivid schedule management, server-runner schedule management, arbitrary cron editing, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`
- `docs/V2_3_SCHEDULE_APPLY_IMPLEMENTATION_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Goal

Stabilize the evidence model after guarded Schedule Apply by capturing enough support-safe previous-schedule metadata to make future rollback design possible, without implementing rollback execution.

This slice should answer:

- what schedule state changed;
- what previous cadence and next-run evidence existed before apply;
- whether the local client believes a rollback candidate could theoretically exist;
- why rollback is still unavailable in the current release;
- what proof the operator/support team can inspect after an apply.

## Boundary

Allowed:

- client-local capture of bounded previous-schedule evidence before a successful `schedule_apply`;
- client-local retention of that evidence with a short, explicit expiry;
- redacted capability/result fields that describe rollback readiness without enabling rollback;
- dashboard display of support-safe metadata in action history, Site Detail, Diagnostics, and support export;
- tests proving `schedule_rollback` remains rejected/unavailable.

Not allowed:

- `schedule_rollback` action dispatch or execution;
- dashboard UI buttons for rollback;
- WPvivid schedule changes;
- server-runner schedule changes;
- raw cron arrays, raw crontab lines, raw option names/values, usernames, filesystem paths, commands, package names, Drime IDs, credentials, signed URLs, or arbitrary settings payloads;
- dashboard-side Drime API access or Drime credential storage;
- backup creation, cleanup, deletion, restore preparation, or restore execution.

## Proposed Metadata Model

The client may store rollback-readiness metadata locally, scoped to one successful `schedule_apply` action:

```json
{
  "rollback_metadata": {
    "captured": true,
    "available": false,
    "reason": "schedule_rollback_runtime_not_implemented",
    "schedule_id": "alynt_scan_upload",
    "owner": "alynt_uploader",
    "source_action_id": "00000000-0000-4000-8000-000000000000",
    "source_preview_action_id": "00000000-0000-4000-8000-000000000001",
    "previous_cadence": "every_15_minutes",
    "applied_cadence": "every_30_minutes",
    "previous_next_run_at": "2026-09-15T10:15:00Z",
    "applied_next_run_at": "2026-09-15T10:30:00Z",
    "current_schedule_fingerprint_before": "sha256-redacted-before",
    "current_schedule_fingerprint_after": "sha256-redacted-after",
    "captured_at": "2026-09-15T10:00:00Z",
    "expires_at": "2026-09-15T11:00:00Z"
  }
}
```

The exact field names may differ during implementation, but the intent is:

- `captured` means previous-state evidence was recorded locally;
- `available` must remain `false` in this slice;
- `reason` must explain why rollback execution is not available;
- fingerprints are redacted hashes only;
- timestamps are ISO-8601 UTC strings;
- cadence values come from the existing allowlisted cadence vocabulary.

## Dashboard Display

Dashboard action history may show:

- rollback metadata captured/not captured;
- rollback execution unavailable;
- previous cadence to applied cadence;
- captured/expiry time if present;
- concise reason code such as `schedule_rollback_runtime_not_implemented`.

Dashboard-side local hardening may add richer display and support aggregates for already-redacted rollback-readiness metadata before uploader-side metadata capture ships. That local-only dashboard work must still preserve the same evidence-only boundary and must not add rollback buttons, dispatch, or runtime execution.

Dashboard must not show:

- raw schedule internals;
- raw WordPress option names or values;
- raw cron arrays;
- local paths;
- shell/user context;
- credentials or Drime identifiers.

## Client Storage And Retention

The client should store rollback metadata only in the existing bounded remote-action state/audit store unless implementation proves a separate option is needed.

Retention should be short and explicit. Recommended default: one hour after apply, or the same operational window chosen for future rollback design. Expired metadata should remain as historical audit summary if already in the bounded action history, but it must not advertise rollback availability.

## Compatibility

Existing dashboard and uploader clients without rollback metadata remain compatible. Missing metadata means:

- rollback metadata: not captured;
- rollback available: false;
- rollback action: unavailable.

The status payload should remain additive and schema-compatible unless implementation proves a schema-version change is necessary.

## Test Plan

Uploader tests:

- successful `schedule_apply` captures support-safe metadata locally;
- metadata uses only allowlisted scalar fields;
- metadata expires or reports unavailable after its readiness window;
- `schedule_rollback` remains rejected/unavailable even when metadata exists;
- failed apply does not advertise rollback metadata as usable;
- stale local schedule state still rejects apply before metadata capture.

Dashboard tests:

- action history displays captured rollback metadata without raw internals;
- Site Detail status remains rollback-unavailable and does not expose rollback controls;
- diagnostics/support context includes only redacted rollback-readiness fields already present in the bounded action context;
- missing metadata degrades to rollback unavailable;
- `schedule_rollback` controls remain hidden/unavailable.

Cross-plugin checks:

- one approved test client can preview/apply and report rollback metadata capture;
- dashboard poll reconciles and displays rollback readiness correctly;
- no rollback action can be dispatched;
- existing V1 polling, V2.1 `scan_upload_now`, V2.3 `schedule_preview`, and V2.3 `schedule_apply` behavior remains unchanged.

## Workflow Gates

Before code:

1. Refresh dashboard and uploader repo context.
2. Confirm both working trees are clean or create/confirm restore points.
3. Update protocol/threat model only for metadata fields; do not approve rollback execution.
4. Implement uploader first, dashboard second.
5. Run applicable ds2 feature reviews for both repositories.
6. Run targeted ds3 before release.
7. Release/deploy only after separate approval.

## Acceptance Criteria

- The client captures bounded support-safe previous-schedule metadata after successful `schedule_apply`.
- The dashboard can sanitize, store, and display rollback-readiness evidence.
- `rollback_available` remains false unless a later runtime rollback slice is approved.
- `schedule_rollback` remains impossible.
- No raw schedule internals, filesystem paths, commands, credentials, package names, Drime IDs, or arbitrary settings are exposed.
- Existing released remote actions continue to work unchanged.

## Approval Gate Before Code

Before implementation begins, explicitly approve:

- metadata capture only, no rollback execution;
- first managed target remains `alynt_scan_upload`;
- retention window for rollback-readiness metadata;
- dashboard and uploader restore points or clean baselines;
- local/pilot target for proof;
- release/deployment timing.
