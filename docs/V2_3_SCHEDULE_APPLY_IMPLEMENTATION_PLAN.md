# V2.3 Schedule Apply Implementation Plan

Status: implemented, released, and deployed as the guarded V2.3 `schedule_apply` slice. Dashboard support shipped through dashboard `0.1.25`; uploader support shipped through uploader `0.5.19`. This document does not approve broad client enablement, schedule rollback, backup creation, cleanup/delete actions, restore actions, WPvivid schedule management, server-runner schedule management, arbitrary cron editing, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`
- `docs/V2_3_PREVIEW_ONLY_IMPLEMENTATION_PLAN.md`
- `docs/V2_3_SCHEDULE_PREVIEW_IMPLEMENTATION_PLAN.md`
- `docs/V2_3_ROLLBACK_METADATA_CAPTURE_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Goal

Define the next V2.3 slice after the already released preview-only schedule capability and non-mutating `schedule_preview` action.

The dashboard should be able to ask a separately opted-in client to apply one previously previewed cadence change for one supported Alynt-owned schedule:

```text
schedule_id: alynt_scan_upload
owner: alynt_uploader
action_type: schedule_apply
```

This slice exists to safely move from "show me what would change" to "apply exactly that already-previewed change" without turning the dashboard into generic schedule/settings management.

## Boundary

Allowed:

- dashboard creates a signed `schedule_apply` intent;
- intent targets only `alynt_scan_upload`;
- intent references a fresh successful `schedule_preview` result by preview fingerprint or short-lived preview token;
- client revalidates local schedule state against the preview before applying;
- client applies only a client-declared supported cadence;
- client records a redacted local audit event;
- dashboard records redacted request/result state in existing V2 action history;
- normal dashboard polling reconciles the latest redacted client action result.

Not allowed:

- `schedule_rollback` runtime behavior;
- WPvivid schedule changes;
- server-runner schedule changes;
- disabling or pausing all backup production;
- arbitrary cron syntax or custom intervals;
- raw WP-Cron arrays, raw crontab lines, option names/values, usernames, paths, shell commands, package names, Drime IDs, credentials, signed URLs, or arbitrary settings payloads;
- backup creation, cleanup, deletion, restore preparation, or restore execution;
- dashboard-side Drime API access or Drime credential storage.

## First Managed Schedule Target

Use only the schedule target already proven by preview rollout:

- `schedule_id`: `alynt_scan_upload`
- owner: `alynt_uploader`
- cadence choices: client-declared allowlist only
- minimum interval: client-declared and revalidated locally
- disable/pause: not part of this slice
- rollback: not available in this slice; rollback metadata and runtime rollback remain later separately approved work

Do not include `alynt_server_runner` until the uploader can prove ownership, safe mutation, and rollback for that schedule. Do not include WPvivid schedules in this slice.

## Product Behavior

On Site Detail, when all requirements are met:

1. The site is v1-paired and active.
2. V2 remote actions are opted in.
3. The dashboard has a decryptable action private key.
4. Latest status reports compatible `remote_actions.schedule_management`.
5. Latest status reports `apply_supported: true` for `alynt_scan_upload`.
6. Latest status includes the same supported cadence used by the preview.
7. A fresh successful `schedule_preview` exists for the same site, schedule ID, proposed cadence, capability version, and current local schedule fingerprint.
8. No newer schedule capability evidence invalidates the preview.

Then the dashboard may show an **Apply Previewed Schedule Change** control next to the preview result.

The control must:

- restate the exact before/after cadence;
- explain that this changes future scan/upload timing only;
- explain that it does not create a backup immediately;
- explain that it does not change WPvivid, server-runner, Drime, retention, delete, cleanup, or restore behavior;
- require a capability + nonce check;
- require explicit operator confirmation that the change affects only future Alynt scan/upload cadence;
- submit a signed `schedule_apply` action that references the fresh preview.

Sites tab remains display-only for schedule management. It must not contain schedule apply controls.

## Fresh Preview Requirement

`schedule_apply` must never be built directly from form input alone.

The dashboard may dispatch apply only when it can reference a fresh preview result stored in action history. A preview is fresh only when all of the following hold:

- preview action state is `succeeded`;
- preview action type is `schedule_preview`;
- preview target schedule is `alynt_scan_upload`;
- preview result contains `would_change: true`;
- preview result includes a bounded preview fingerprint or token;
- preview result was created inside the approved freshness window, recommended default: 15 minutes;
- latest schedule capability has the same schedule ID, capability version, current cadence, and supported cadence allowlist;
- no newer schedule action for the same site/schedule supersedes it.

If any check fails, dashboard UI should show **Preview expired — run Check Now and preview again** or equivalent copy.

## Action Intent Shape

Extend the signed V2 action-intent body for `schedule_apply` with one bounded context object:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "action_type": "schedule_apply",
  "requested_at": "2026-09-15T10:00:00Z",
  "expires_at": "2026-09-15T10:05:00Z",
  "idempotency_key": "adb-act-example-0000000000000000",
  "schedule_apply": {
    "schedule_id": "alynt_scan_upload",
    "proposed_cadence": "every_30_minutes",
    "capability_version": 1,
    "preview_action_id": "00000000-0000-4000-8000-000000000001",
    "preview_fingerprint": "sha256-example-redacted-preview-fingerprint"
  }
}
```

Forbidden in the request:

- raw cron syntax;
- timestamps supplied by the dashboard for next-run calculation;
- current local schedule assumptions other than the preview reference;
- disable flags;
- shell commands;
- paths;
- WP-Cron event arrays;
- option names/values;
- package names;
- backup IDs;
- Drime IDs;
- arbitrary labels or descriptions.

## Client Validation

The uploader must reject apply when:

- V2 actions are not opted in;
- signature, timestamp, route, body hash, site UUID, dashboard site public ID, or key ID validation fails;
- action type is not allowlisted;
- schedule apply capability is disabled;
- `schedule_id` is unknown, not manageable, or not `alynt_scan_upload`;
- proposed cadence is not in the latest local supported-cadence allowlist;
- proposed cadence is below local minimum interval;
- preview action ID or preview fingerprint is missing, unknown, expired, or not bound to the same site/schedule/cadence;
- local current schedule state no longer matches the preview baseline;
- the change would disable all backup production;
- local schedule state cannot be persisted safely;
- idempotency state conflicts;
- a schedule action lock is already active.

The client must calculate and validate from local current state. It must not trust the dashboard to provide current cadence, current next-run time, rollback data, or local schedule metadata.

Recommended rejection/result codes:

- `schedule_apply_unavailable`
- `schedule_apply_preview_missing`
- `schedule_apply_preview_expired`
- `schedule_apply_preview_stale`
- `schedule_apply_unsupported_cadence`
- `schedule_apply_lock_busy`
- `schedule_apply_persist_failed`
- `schedule_apply_succeeded`

## Apply Response Shape

The client action result should remain support-safe and redacted:

```json
{
  "action_id": "00000000-0000-4000-8000-000000000000",
  "action_type": "schedule_apply",
  "state": "succeeded",
  "result_code": "schedule_apply_succeeded",
  "result_summary": "Alynt scan/upload schedule changed from every 15 minutes to every 30 minutes.",
  "schedule_apply": {
    "schedule_id": "alynt_scan_upload",
    "label": "Alynt scan/upload",
    "owner": "alynt_uploader",
    "previous_cadence": "every_15_minutes",
    "applied_cadence": "every_30_minutes",
    "previous_next_run_at": "2026-09-15T10:15:00Z",
    "new_next_run_at": "2026-09-15T10:30:00Z",
    "rollback_available": false,
    "rollback_expires_at": "",
    "warnings": []
  }
}
```

The response must not include raw schedule internals, raw cron arrays, raw crontab lines, paths, usernames, package names, Drime IDs, credentials, or arbitrary client-local details.

## Dashboard Storage

Use the existing V2 action-history table unless implementation proves a small schema addition is required. If schema changes become necessary, stop and add a database/uninstall review before coding.

Dashboard action records may store:

- `action_type = schedule_apply`;
- schedule ID;
- schedule label;
- previous cadence;
- applied cadence;
- previous next-run timestamp;
- new next-run timestamp;
- preview action ID;
- preview fingerprint hash;
- rollback availability flag and expiry, which must remain false/empty for this slice;
- actor ID;
- state transitions;
- result code and support-safe summary.

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

Rollback storage is deferred. For this slice, the client and dashboard may report/store only rollback availability as false and rollback expiry as empty.

## Dashboard Implementation Plan

1. Extend action capability parsing so `schedule_apply` remains hidden unless latest status declares `apply_supported: true` for `alynt_scan_upload`.
2. Add fresh-preview lookup logic in the remote action repository or a focused helper.
3. Add Site Detail apply UI only beside eligible preview results.
4. Add nonce/capability checks and explicit operator confirmation that apply changes only future Alynt scan/upload cadence.
5. Extend dispatcher allowlists/redaction to build and store only bounded `schedule_apply` context.
6. Extend action-history display, Diagnostics, and support export with redacted apply summaries.
7. Keep Sites tab compact and display-only.
8. Keep `schedule_rollback` hidden/unavailable.

## Uploader Implementation Plan

1. Extend local V2 action opt-in policy to allow `schedule_apply` only when the administrator separately enables schedule mutation.
2. Persist short-lived preview evidence or fingerprints from successful `schedule_preview` actions.
3. Validate `schedule_apply` against the preview, current local schedule state, cadence allowlist, locks, idempotency, and local opt-in.
4. Apply only the `alynt_scan_upload` schedule using local WordPress scheduling APIs.
5. Persist local audit/result evidence.
6. Report redacted latest action result through the existing status payload, with rollback unavailable.
7. Keep `schedule_rollback` rejected until separately implemented.

## Test Plan

Dashboard tests:

- apply controls hidden for v1-only clients;
- apply controls hidden when V2 opt-in is missing;
- apply controls hidden when schedule capability is missing or `apply_supported` is false;
- apply controls hidden without a fresh successful matching preview;
- stale/superseded preview requires a new preview;
- apply request body includes only allowlisted fields;
- nonce/capability enforcement for apply request;
- explicit operator confirmation is required before apply dispatch;
- action history renders apply result without exposing raw internals;
- support export includes only redacted apply summaries;
- `schedule_rollback` remains unavailable.

Uploader tests:

- V2 disabled rejects `schedule_apply`;
- schedule mutation disabled rejects `schedule_apply`;
- invalid signature, expired timestamp, wrong site UUID, wrong dashboard site public ID, and wrong key ID fail closed;
- unknown schedule ID is rejected;
- unsupported cadence is rejected;
- apply without matching preview is rejected;
- expired preview is rejected;
- stale local schedule state is rejected;
- valid apply changes only `alynt_scan_upload`;
- duplicate idempotency returns prior apply result without applying twice;
- failed persistence preserves the prior schedule;
- `schedule_rollback` remains rejected.

Cross-plugin checks:

- one local/low-risk client can preview and then apply `alynt_scan_upload` cadence;
- dashboard action history reconciles the apply result;
- latest status reports the new cadence after polling;
- existing V1 read-only polling still works;
- existing V2.1 `scan_upload_now` still works;
- existing `schedule_preview` still performs no mutation;
- no WPvivid, server-runner, Drime, cleanup, delete, or restore behavior changes.

## Release And Rollout Plan

1. Planning approval.
2. Restore point or clean baseline confirmation for dashboard and uploader repositories.
3. Implement uploader first with schedule mutation disabled by default.
4. Implement dashboard second with apply controls hidden until client capability is visible.
5. Run applicable ds2 feature workflows in both repositories, including feature light, structure/bloat, UI/UX, security, and documentation sync.
6. Run targeted ds3 pre-release workflows before each release; use full ds3 if implementation touches schema/lifecycle/packaging.
7. Release uploader first.
8. Release dashboard second.
9. Pilot on one low-risk live site with a harmless cadence change.
10. Verify dashboard row, Site Detail, action history, and client local schedule state.
11. Expand only after the pilot confirms no unintended schedule, backup, or Drime mutation.

## Acceptance Criteria

- Dashboard cannot dispatch apply without a fresh matching preview.
- Dashboard never sends raw cron, raw option, path, command, credential, package, Drime ID, or arbitrary settings fields.
- Dashboard offers only client-declared supported cadences.
- Client revalidates from local state and rejects stale previews.
- Apply changes only `alynt_scan_upload`.
- Apply result is redacted and safe for screenshots/support export.
- Apply result reports rollback unavailable.
- `schedule_rollback` is still impossible.
- Existing V1 polling, backup-source freshness, V2.1 `scan_upload_now`, V2.2 action reconciliation, and V2.3 `schedule_preview` remain unchanged.

## Post-Release Stabilization And Next Slice

Schedule Apply is now available only when a client separately opts in and the dashboard has a fresh successful matching preview. The next recommended slice is not rollback execution. It is rollback metadata capture/readiness:

- define exactly what support-safe previous-schedule evidence the client may retain locally after apply;
- define the bounded redacted fields the dashboard may display in action history;
- preserve `rollback_available: false` until a later `schedule_rollback` runtime slice is explicitly approved;
- verify that metadata capture does not expose raw cron arrays, option blobs, paths, credentials, Drime IDs, package names, or arbitrary settings;
- update docs/tests before any future rollback action is considered.

The planning baseline for that next slice is tracked in `docs/V2_3_ROLLBACK_METADATA_CAPTURE_PLAN.md`.

## Historical Approval Gate Before Code

Before implementation begins, explicitly approve:

- first managed target: `alynt_scan_upload`;
- first apply scope: cadence change only, no disable/pause;
- no WPvivid schedule management;
- no server-runner schedule management;
- no `schedule_rollback` runtime behavior;
- dashboard and uploader repo restore points or clean baselines;
- local test target;
- whether release/deployment should wait for both repositories to pass relevant feature/pre-release workflows;
- first live pilot target, if any.
