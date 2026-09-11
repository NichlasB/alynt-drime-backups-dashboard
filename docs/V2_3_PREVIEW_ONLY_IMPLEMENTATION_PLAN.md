# V2.3 Preview-Only Schedule Capability Implementation Plan

Status: implementation planning only. This document does not approve code changes, release, deployment, live-site writes, schedule changes, backup creation, cleanup/delete actions, restore actions, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Goal

Implement the first V2.3 slice as visibility only:

1. the uploader reports redacted schedule-management capability;
2. the dashboard allowlists and stores that capability inside the existing sanitized status snapshot;
3. the dashboard displays schedule-management availability and current schedule posture;
4. no schedule preview/apply/rollback action is dispatched;
5. no client schedule is changed.

This slice should help the operator answer:

- Does this site expose any dashboard-manageable schedules?
- Which schedule owner controls each supported schedule?
- What is the current cadence and next run?
- Which cadence choices could be offered later?
- Is this only preview/reporting, or can the dashboard apply changes? For this slice the answer must be preview/reporting only.

## Non-Goals

Do not implement:

- `schedule_preview`;
- `schedule_apply`;
- `schedule_rollback`;
- arbitrary cron editing;
- WPvivid schedule editing;
- server-runner crontab editing;
- backup creation;
- retention cleanup;
- delete backup sets;
- restore preparation or execution;
- dashboard-side Drime API credentials;
- raw cron, crontab, WP-Cron array, shell command, filesystem path, WPvivid option, package name, Drime object ID, token, or credential exposure.

## First Managed Schedule Target

Start with read-only reporting for the Alynt scan/upload schedule:

```text
schedule_id: alynt_scan_upload
owner: alynt_uploader
preview_only: true
apply_supported: false
rollback_supported: false
```

The first slice may also report `alynt_server_runner` as unavailable/not manageable if the uploader can safely detect that a server-runner schedule exists but cannot prove ownership. It should not advertise `alynt_server_runner` as manageable until a later implementation can prove safe ownership and rollback.

## Uploader-Side Producer Plan

Repository: `C:\Development\WordPress\Plugins\alynt-drime-backups-uploader`

### 1. Schedule capability service

Add a small service responsible for building a redacted schedule capability summary.

Recommended responsibility:

- inspect Alynt-owned scan/upload schedule state using WordPress scheduling APIs;
- map the current interval to an allowlisted cadence label;
- compute or expose the next scheduled run timestamp when available;
- emit only allowlisted scalar fields;
- degrade to unavailable when schedule state is unknown or unsafe.

### 2. Allowed schedule fields

The uploader may emit:

- `protocol_version`;
- `capability_version`;
- `enabled`;
- `preview_only`;
- `apply_supported`;
- `rollback_supported`;
- `schedules`;
- `schedule_id`;
- `label`;
- `owner`;
- `manageable`;
- `current_cadence`;
- `current_next_run_at`;
- `supported_cadences`;
- `minimum_interval_seconds`;
- `can_disable`;
- `requires_high_friction_disable`;
- `rollback_supported`.

It must not emit:

- raw cron hooks beyond approved public labels;
- raw recurrence arrays;
- raw crontab lines;
- usernames;
- shell commands;
- filesystem paths;
- raw WPvivid options;
- package names;
- Drime identifiers;
- credentials, tokens, nonces, cookies, or salts.

### 3. Initial cadence allowlist

Use stable labels rather than raw cron expressions:

- `every_15_minutes`;
- `every_30_minutes`;
- `hourly`;
- `daily`;
- `weekly`;
- `unknown`.

The first slice should expose only cadences the client can identify confidently. `unknown` should display as unavailable rather than unhealthy.

### 4. Status payload integration

Attach the capability under:

```text
remote_actions.schedule_management
```

This should remain optional and additive. Older dashboards must ignore it; newer dashboards must treat its absence as unavailable.

### 5. Uploader tests

Add focused tests for:

- schedule capability object is absent or unavailable when remote actions are disabled;
- `alynt_scan_upload` capability is redacted and allowlisted;
- next-run timestamp is ISO-8601 or omitted;
- unsupported cadence degrades to `unknown` or unavailable;
- forbidden fields are never emitted;
- no schedule is changed while building the capability.

## Dashboard-Side Consumer Plan

Repository: `C:\Development\WordPress\Plugins\alynt-drime-backups-dashboard`

### 1. Payload validator

Extend the V2 `remote_actions` sanitizer to allow optional `schedule_management`.

Validator rules:

- accept only documented fields;
- cap string lengths;
- cap array lengths;
- allow only recognized schedule IDs and cadence labels;
- ignore unsupported schedules;
- reject payloads with forbidden path/secret/command-like keys or values;
- require `preview_only: true`, `apply_supported: false`, and `rollback_supported: false` for the first slice.

### 2. Storage

Use existing snapshot storage. Do not add a table in the preview-only slice.

The sanitized `remote_actions.schedule_management` object can live inside the redacted snapshot JSON because it is status evidence, not an action record.

### 3. UI

Add read-only display in Site Detail first:

- schedule-management status: `Unavailable`, `Preview only`, or `Capability reported`;
- schedule ID and label;
- owner;
- current cadence;
- next run, if known;
- supported cadence labels;
- clear copy: `Preview only — this dashboard cannot apply schedule changes yet.`

Sites tab should remain compact. If added, use only a tiny hint such as:

- `Schedules: Preview only`
- `Schedules: Not enabled`

Do not add schedule editing controls in this slice.

### 4. Diagnostics/support copy

Add aggregate support-safe counts:

- sites reporting schedule capability;
- sites with preview-only schedule capability;
- sites with unavailable schedule capability;
- recognized schedules by ID.

Do not export raw schedule internals.

### 5. Dashboard tests

Add focused tests for:

- v1/v2 clients without schedule capability still validate and render normally;
- valid `alynt_scan_upload` capability is preserved in sanitized payload;
- unsafe schedule fields are rejected;
- unsupported schedule IDs are ignored or marked unavailable;
- Site Detail shows preview-only copy;
- no schedule action buttons render;
- diagnostics support copy includes only aggregate safe fields.

## Cross-Plugin Contract Fixture

Add or update a shared JSON fixture that contains:

- normal remote-action capability;
- `schedule_management.preview_only=true`;
- one `alynt_scan_upload` schedule;
- supported cadence labels;
- no raw cron/path/secret fields.

Use the same fixture shape in both repositories so the dashboard accepts what the uploader emits.

## Recommended Implementation Order

1. Uploader: add docs/status payload notes for preview-only schedule capability.
2. Uploader: implement the redacted capability producer and focused tests.
3. Dashboard: add a fixture from the uploader output.
4. Dashboard: extend sanitizer/validator and focused tests.
5. Dashboard: add Site Detail display and diagnostics aggregates.
6. Run full test/lint suites in both repos.
7. Prepare separate commits for uploader and dashboard.
8. Release uploader first with capability reporting only.
9. Release dashboard after the producer shape is stable.
10. Verify on a local or low-risk site that capability display appears and no schedule changes occur.

## Acceptance Criteria

- Existing sites without schedule capability continue to work.
- Uploader status payload can include preview-only `remote_actions.schedule_management`.
- Dashboard accepts sanitized preview-only schedule capability.
- Dashboard displays current schedule posture without editing controls.
- No `schedule_preview`, `schedule_apply`, or `schedule_rollback` requests are sent.
- No client schedules, WP-Cron events, crontabs, WPvivid options, backups, Drime files, settings, or credentials are changed.
- Support exports remain redacted and aggregate-first.

## Approval Gate Before Code

Before implementation begins, confirm:

- first managed target: `alynt_scan_upload`;
- first slice: preview-only capability reporting and display;
- no schedule mutation actions;
- dashboard and uploader repo restore points or clean baselines;
- whether to implement uploader producer first, dashboard consumer first with fixture, or both in one coordinated branch;
- local test target for display verification;
- whether release/deployment is deferred until both repos pass pre-release workflows.

Recommended next coding step: implement the uploader-side redacted `alynt_scan_upload` capability producer first, because the dashboard should consume a real producer shape rather than inventing one.
