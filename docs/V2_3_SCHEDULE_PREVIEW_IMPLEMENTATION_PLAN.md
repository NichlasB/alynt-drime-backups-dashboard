# V2.3 Schedule Preview Action Implementation Plan

Status: implemented, released, and deployed as the non-mutating V2.3 `schedule_preview` slice. Dashboard support shipped in dashboard `0.1.22`; client-side support shipped in uploader `0.5.18`. Fleet rollout and read-only post-rollout verification confirmed active tracked sites advertise preview-only schedule capability and remain healthy. This document still does not approve `schedule_apply`, `schedule_rollback`, live-site writes beyond the already approved rollout, schedule changes, backup creation, cleanup/delete actions, restore actions, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`
- `docs/V2_3_PREVIEW_ONLY_IMPLEMENTATION_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Goal

Document and preserve the V2.3 signed, non-mutating `schedule_preview` action boundary.

The dashboard should be able to ask an explicitly opted-in client:

> If this dashboard proposed changing one supported Alynt-owned schedule to one of your declared supported cadences, what would the before/after schedule posture look like?

The client validates the proposal locally and returns a redacted preview. No schedule is changed.

## Boundary

This slice is still non-mutating.

Allowed:

- dashboard creates a signed `schedule_preview` intent;
- client validates the proposed schedule ID and cadence against its own declared capability;
- client returns redacted before/after preview evidence;
- dashboard records the preview request/result in existing V2 action history;
- normal dashboard polling reconciles the latest redacted client action state.

Not allowed:

- `schedule_apply`;
- `schedule_rollback`;
- changing WP-Cron, crontabs, WPvivid settings, uploader settings, or any client schedule;
- creating fresh backups;
- deleting, cleaning up, or restoring backups;
- Drime API credential storage or dashboard-side Drime access;
- raw cron expressions, raw crontab lines, raw WP-Cron arrays, raw WPvivid option blobs, filesystem paths, usernames, shell commands, package names, Drime IDs, signed URLs, or arbitrary settings payloads.

## First Managed Schedule Target

Use only the existing preview-only capability target:

- `schedule_id`: `alynt_scan_upload`
- owner: `alynt_uploader`
- supported cadence choices: client-declared allowlist only
- free-form cadence input: not allowed
- disable/pause: not part of this slice

Do not include `alynt_server_runner` until the uploader can prove ownership and safe validation of that schedule. Do not include WPvivid schedules in this slice.

## Product Behavior

On Site Detail, when all requirements are met:

1. The site is v1-paired and active.
2. V2 remote actions are opted in.
3. The dashboard has a decryptable action private key.
4. Latest status reports `remote_actions.schedule_management.enabled = true`.
5. Latest status reports `preview_only = true`.
6. Latest status includes a recognized `alynt_scan_upload` schedule capability.
7. The client declares at least one supported cadence.

Then the dashboard may show a **Preview Schedule Change** panel.

The panel should:

- show the current cadence and next run;
- offer only client-declared supported cadence choices;
- explain that preview does not apply changes;
- submit a signed `schedule_preview` action;
- display the resulting before/after preview in action history after reconciliation.

Sites tab remains compact. It should not contain schedule-edit controls. It may continue showing schedule availability/preview-only hints.

## Action Intent Shape

Extend the signed V2 action-intent body for `schedule_preview` with one bounded redacted context object:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "action_type": "schedule_preview",
  "requested_at": "2026-09-12T17:30:00Z",
  "expires_at": "2026-09-12T17:35:00Z",
  "idempotency_key": "adb-act-example-0000000000000000",
  "schedule_preview": {
    "schedule_id": "alynt_scan_upload",
    "proposed_cadence": "every_30_minutes",
    "capability_version": 1
  }
}
```

Forbidden in the request:

- raw cron syntax;
- timestamps supplied by the dashboard for next-run calculation;
- shell commands;
- paths;
- WP-Cron event arrays;
- option names/values;
- package names;
- backup IDs;
- Drime IDs;
- arbitrary labels or descriptions;
- disable flags.

## Client Validation

The uploader must reject the preview when:

- V2 actions are not opted in;
- signature, timestamp, route, body hash, site UUID, dashboard site public ID, or key ID validation fails;
- action type is not allowlisted;
- schedule preview capability is disabled;
- `schedule_id` is unknown or not manageable;
- `schedule_id` is not `alynt_scan_upload`;
- proposed cadence is not in the latest local supported-cadence allowlist;
- proposed cadence is below local minimum interval;
- local schedule state cannot be read safely;
- local action idempotency state conflicts;
- a separate schedule action lock is already active.

The uploader must calculate the preview from local current state. It must not trust the dashboard to provide current cadence, current next-run time, or local schedule metadata.

## Preview Response Shape

The client action result should remain support-safe and redacted:

```json
{
  "action_id": "00000000-0000-4000-8000-000000000000",
  "action_type": "schedule_preview",
  "state": "succeeded",
  "result_code": "schedule_preview_ready",
  "result_summary": "Schedule preview is ready. No schedule was changed.",
  "schedule_preview": {
    "schedule_id": "alynt_scan_upload",
    "label": "Alynt scan/upload",
    "owner": "alynt_uploader",
    "current_cadence": "every_15_minutes",
    "proposed_cadence": "every_30_minutes",
    "current_next_run_at": "2026-09-12T17:45:00Z",
    "proposed_next_run_estimate_at": "2026-09-12T18:00:00Z",
    "would_change": true,
    "apply_supported": false,
    "rollback_supported": false,
    "warnings": []
  }
}
```

The response must not include raw schedule internals, raw cron arrays, raw crontab lines, paths, usernames, package names, Drime IDs, credentials, or arbitrary client-local details.

## Dashboard Storage

Prefer the existing V2 action-history table.

Dashboard action records may store:

- `action_type = schedule_preview`;
- schedule ID;
- schedule label;
- current cadence;
- proposed cadence;
- current next run;
- proposed next run estimate;
- result code;
- state transitions;
- actor ID;
- timestamps;
- short operator-safe summary.

Dashboard action records must not store:

- raw cron lines;
- raw WP-Cron arrays;
- raw crontab fragments;
- raw WPvivid option blobs;
- filesystem paths;
- usernames tied to shell execution;
- package names;
- Drime identifiers;
- credentials;
- private keys;
- signatures;
- raw remote response bodies.

## Dashboard Implementation Status

Implemented in dashboard `0.1.22`:

1. Dashboard action-type allowlists recognize `schedule_preview` without enabling apply/rollback.
2. Action redaction/validation supports bounded `schedule_preview` request/result context.
3. Site Detail includes a preview form behind existing capability checks.
4. UI controls are restricted to client-declared `alynt_scan_upload` cadence choices.
5. Dispatch uses the existing signed V2 dispatcher.
6. Redacted preview context is stored in existing action history.
7. Latest client-reported schedule-preview action reconciles through existing V2.2 action history.
8. Diagnostics/support aggregate counts include schedule preview actions.
9. Sites tab remains display-only for schedule management.

## Uploader Implementation Status

Implemented in uploader `0.5.18`:

1. Local V2 allowed-action policy can expose `schedule_preview` alongside `scan_upload_now` after action opt-in.
2. Request validation covers `schedule_preview.schedule_id` and `schedule_preview.proposed_cadence`.
3. Alynt scan/upload schedule preview reads current WP-Cron schedule posture without writing.
4. The client calculates redacted before/after preview from local capability declarations.
5. Local action state/result is persisted through the existing action store.
6. The redacted latest action result is reported through the existing status payload.
7. `schedule_apply` and `schedule_rollback` remain rejected until separately implemented.
8. Existing scan/upload-now behavior remains unchanged.

## Test Plan

Dashboard tests:

- `schedule_preview` controls hidden for v1-only clients.
- Controls hidden when V2 opt-in is missing.
- Controls hidden when schedule capability is missing.
- Controls hidden for unsupported schedule IDs.
- Form offers only client-declared cadence choices.
- Nonce/capability enforcement for preview request.
- Signed body includes only allowlisted preview fields.
- Redaction rejects paths, raw cron, commands, credentials, Drime IDs, and arbitrary labels.
- Action history renders preview result without implying a schedule was applied.
- `schedule_apply` and `schedule_rollback` remain unavailable.

Uploader tests:

- V2 disabled rejects `schedule_preview`.
- Invalid signature, expired timestamp, wrong site UUID, wrong dashboard site public ID, and wrong key ID fail closed.
- Unknown schedule ID is rejected.
- Unsupported cadence is rejected.
- Free-form cron expression is rejected.
- Valid preview returns before/after data without changing WP-Cron schedule state.
- Duplicate idempotency returns prior preview result without recalculating conflicting input.
- `schedule_apply` and `schedule_rollback` are rejected.
- Status payload reports only redacted preview result.

Cross-plugin checks:

- One local/low-risk client can preview `alynt_scan_upload` cadence.
- The client schedule remains unchanged before and after preview.
- Dashboard action history reconciles the preview result.
- Existing `scan_upload_now` still works.
- Existing read-only polling still works.

## Release And Rollout Status

Completed:

- Dashboard `0.1.22` released and deployed to `control.sitesmanage.com`.
- Uploader `0.5.18` released and rolled out across the 14 active tracked live sites.
- Post-rollout read-only health check confirmed all active dashboard rows working, uploader `0.5.18`, queue 0, failed 0, warning_count 0, and preview-only schedule evidence.

## Future Schedule-Slice Release And Rollout Plan

Use this order for future schedule-management slices, especially any slice that moves beyond preview-only behavior:

1. Planning approval.
2. Restore point or clean baseline confirmation for dashboard and uploader repositories.
3. Uploader implementation first, with new schedule action types disabled by default unless the client administrator separately opts in.
4. Dashboard implementation second, with controls hidden until client capability is visible.
5. Local/low-risk verification.
6. Feature workflow and pre-release workflow as appropriate.
7. Release uploader first.
8. Release dashboard second.
9. Pilot on one low-risk live site.
10. Expand only after tests and live verification prove the slice preserves its approved safety boundary.

## Acceptance Criteria

- Previewing a schedule change does not change a schedule.
- Dashboard never sends raw cron, raw option, path, command, credential, package, Drime ID, or arbitrary settings fields.
- Dashboard offers only client-declared supported cadences.
- Client validates from local state and ignores dashboard-supplied assumptions.
- Preview result is redacted and safe for screenshots/support export.
- `schedule_apply` and `schedule_rollback` are still impossible.
- Existing V1 polling, backup-source freshness, V2.1 `scan_upload_now`, and V2.2 action reconciliation remain unchanged.

## Approval Gate Before Future Code

Before any future schedule-management code implementation, explicitly approve:

- exact schedule target;
- exact action scope;
- whether any apply/rollback behavior is in scope;
- no WPvivid schedule management;
- dashboard and uploader repo restore points or clean baselines;
- local test target;
- whether release/deployment should wait for both repos to pass the relevant feature/pre-release workflows;
- first live pilot target, if any.
