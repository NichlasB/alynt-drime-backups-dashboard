# V2.3 Schedule Management Design

Status: design baseline only. This document does not approve implementation, release, deployment, broad client enablement, live-site writes, schedule changes, backup creation, cleanup/delete actions, restore actions, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_1_REQUEST_BACKUP_NOW_DESIGN.md`
- `docs/V2_1_SIGNED_DISPATCH_IMPLEMENTATION_PLAN.md`
- `docs/V2_2_REMOTE_ACTION_HISTORY_AUDIT_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Boundary

V2.3 is the first planned remote-action slice that may change persistent client behavior. It is therefore a higher-risk gated phase, even if the first implementation only manages backup scan/upload schedules.

The dashboard must continue to avoid client Drime API credentials. The dashboard coordinates schedule proposals; the client uploader validates and applies approved changes locally using its own settings, local capability declarations, and local audit trail.

V2.3 must not become generic settings management. It should manage only explicit backup-related schedules the client declares as safe and supported.

## Product Goal

Let an operator review and, after explicit confirmation, update supported backup-related schedules from the central dashboard without logging into each client site.

The operator should be able to answer:

- Which schedules does this client expose for dashboard management?
- What is the current cadence, next run, and schedule owner?
- What change is being proposed?
- What exactly will change before it is applied?
- Can the previous schedule be restored if the change is wrong?
- Who changed the schedule, when, and with what result?

## Non-Goals

V2.3 must not add:

- arbitrary cron editing;
- arbitrary option editing;
- arbitrary command execution;
- raw filesystem paths;
- raw WPvivid option blobs;
- raw cron arrays;
- Drime API token storage in the dashboard;
- retention cleanup;
- backup-set deletion;
- restore preparation;
- restore execution;
- dashboard-side schedule inference that bypasses client-declared capabilities.

V2.3 should not initially manage third-party schedules directly unless the uploader can expose a narrow, tested adapter for that schedule owner. For example, WPvivid schedule changes should remain deferred until the uploader has a stable, reversible, locally tested WPvivid schedule adapter.

## Recommended First Scope

Start with Alynt-owned schedules only:

1. Alynt scan/upload cadence.
2. Alynt server-runner package creation cadence only if the uploader can prove it owns and can safely update the site-user cron entry.

Defer these until later V2.3 sub-slices:

- WPvivid local backup schedules.
- WPvivid remote backup schedules.
- mixed server + WPvivid schedule templates.
- pausing/disabling all backup production.

The first release may be preview-only if needed. Preview-only is useful because it lets the dashboard display current schedule capability and produce before/after previews without applying changes.

## Capability Model

The client should publish a redacted schedule capability summary through the authenticated status payload or a V2 capability response.

Each capability should be an allowlisted object, for example:

```json
{
  "schedule_id": "alynt_scan_upload",
  "label": "Alynt scan/upload",
  "owner": "alynt_uploader",
  "manageable": true,
  "current_cadence": "every_15_minutes",
  "current_next_run_at": "2026-09-11T17:45:00Z",
  "supported_cadences": ["every_15_minutes", "every_30_minutes", "hourly"],
  "minimum_interval_seconds": 900,
  "can_disable": false,
  "requires_high_friction_disable": true,
  "rollback_supported": true
}
```

The client must not expose raw crontab lines, raw WP-Cron arrays, filesystem paths, usernames, shell commands, option blobs, backup package names, Drime object IDs, credentials, or tokens.

The dashboard must ignore unknown schedule IDs and unsupported fields. Unknown clients should remain compatible and simply show that schedule management is unavailable.

## Action Types

Recommended V2.3 actions:

- `schedule_preview`: ask the client to validate a proposed schedule change and return a redacted before/after preview.
- `schedule_apply`: apply a recent preview using a preview token or preview fingerprint.
- `schedule_rollback`: restore the prior schedule state captured by an earlier apply, if the client still considers that rollback valid.

Do not reuse V2.1 `scan_upload_now` for schedule changes. Schedule actions need separate capability checks, separate rate limits, and separate audit language.

## Preview Flow

1. Dashboard operator opens Site Detail and reviews schedule capabilities.
2. Operator chooses one supported schedule and one supported cadence.
3. Dashboard creates a signed `schedule_preview` intent.
4. Client validates:
   - remote-action opt-in;
   - action key and signature;
   - idempotency key and expiry;
   - schedule ID allowlist;
   - requested cadence allowlist;
   - minimum interval;
   - ownership of the target schedule;
   - whether the change would disable all backup production.
5. Client returns a redacted preview:
   - schedule ID and label;
   - current cadence;
   - proposed cadence;
   - current next run;
   - proposed next run estimate;
   - whether rollback metadata would be captured;
   - warnings and high-friction confirmation requirements.
6. Dashboard stores the preview in the existing V2 action history model with redacted arguments only.

Preview should not change client schedules.

## Apply Flow

1. Operator reviews a fresh preview.
2. Operator confirms the exact before/after change.
3. Dashboard creates a signed `schedule_apply` intent referencing the preview fingerprint or short-lived preview token.
4. Client revalidates the preview against current local schedule state.
5. Client stores rollback metadata locally before applying the change.
6. Client applies the schedule using local APIs or controlled owned-file update logic.
7. Client records a local audit event.
8. Dashboard learns the result through the action response and follow-up status polling.

If local schedule state changed since preview, the client should reject apply as `preview_stale` and require a new preview.

## Rollback Flow

Rollback should be supported for the first schedule-management implementation unless the chosen schedule target cannot safely provide it.

1. Operator selects a recent schedule action with rollback available.
2. Dashboard creates a signed `schedule_rollback` intent.
3. Client validates that rollback metadata is still current enough and belongs to the same schedule owner.
4. Client restores the prior schedule state.
5. Client records local audit evidence and reports a redacted result.

Rollback should not promise to undo backup runs that happened while the changed schedule was active. It restores schedule configuration only.

## Dashboard UI

### Sites Tab

The Sites tab should remain compact. It may show a small schedule-management availability hint only when action capabilities are enabled, for example:

- `Schedules: Manageable`
- `Schedules: Preview only`
- `Schedules: Not enabled`

Do not put schedule editing controls directly in the Sites table.

### Site Detail

Site Detail is the primary V2.3 UI.

Recommended sections:

- Schedule capabilities summary.
- Current schedules table.
- Preview schedule change panel.
- Recent schedule actions from the V2.2 action history.
- Rollback availability for recent applied changes.

Every apply control should include plain-language consequences:

- what will change;
- what will not change;
- whether backup production could be delayed;
- whether rollback metadata will be captured;
- whether a high-friction confirmation is required.

### Attention Tab

Schedule management should not create noise on the Attention tab unless a schedule action fails, becomes stale, or the client reports an unsafe schedule state.

Possible attention reasons:

- supported backup production schedule missing;
- schedule apply failed;
- schedule rollback failed;
- all backup production disabled;
- schedule capabilities changed after preview.

### Diagnostics And Support Export

Support output should include redacted aggregate schedule action counts and latest schedule-management state per site:

- capabilities reported/not reported;
- preview/apply/rollback counts;
- latest schedule action state;
- latest redacted result code;
- no raw schedule internals.

## Safety And Authorization

V2.3 must use the V2 signed action model rather than the V1 polling credential.

Required protections:

- per-site remote-action opt-in;
- per-action capability declaration;
- WordPress capability check for the dashboard operator;
- signed request;
- expiry;
- idempotency key;
- replay protection;
- bounded rate limits;
- action locks;
- stale-action detection;
- redacted dashboard and client audit logs;
- fresh preview required before apply;
- high-friction confirmation before disabling all backup production.

The dashboard must not synthesize schedule actions from raw user input. The UI should offer only client-declared schedule IDs and client-declared cadence choices.

## Data Storage

Prefer extending existing V2 action-history storage before adding a new table.

Dashboard storage should keep:

- action type;
- schedule ID;
- schedule label;
- redacted current cadence;
- redacted proposed cadence;
- preview fingerprint;
- state transitions;
- actor ID;
- timestamps;
- result code and support-safe summary.

Dashboard storage should not keep:

- raw cron lines;
- raw WP-Cron arrays;
- raw WPvivid options;
- usernames tied to shell execution;
- filesystem paths;
- package names;
- Drime identifiers;
- credentials;
- signatures or private keys in action records.

Client storage should keep the rollback metadata locally, not in the dashboard.

## Compatibility

Clients without V2.3 capability reporting remain valid V2.1/V2.2 clients. The dashboard should show schedule management as unavailable rather than degraded or unhealthy.

If a client reports a schedule capability that the dashboard version does not understand, the dashboard should ignore it and display a compatibility note.

## Test Plan

### Dashboard tests

- Sanitizes schedule capabilities and rejects forbidden fields.
- Renders schedule-management unavailable for clients without capabilities.
- Renders preview controls only for supported schedule IDs/cadences.
- Requires fresh preview before apply.
- Records redacted schedule preview/apply/rollback action rows.
- Displays stale preview and failed apply states.
- Excludes raw paths/options/cron lines from support export.

### Uploader tests

- Reports schedule capabilities without raw internals.
- Rejects unknown schedule IDs.
- Rejects unsupported cadence choices.
- Rejects apply without a matching fresh preview.
- Rejects apply when local schedule changed after preview.
- Captures rollback metadata before apply.
- Restores previous schedule on rollback.
- Records local audit events.
- Preserves current schedules on failed preview/apply.

### Integration checks

- Preview produces no schedule change.
- Apply changes only the selected schedule.
- Rollback restores only the selected schedule.
- Dashboard action history reconciles with client result.
- Existing V1 read-only polling remains unaffected.
- Existing V2.1 `scan_upload_now` remains unaffected.

## Implementation Order

1. Update protocol and threat-model documents for schedule actions.
2. Implement uploader-side redacted schedule capability reporting.
3. Implement dashboard capability ingestion and unavailable-state UI.
4. Implement `schedule_preview` only.
5. Pilot preview-only on one low-risk site.
6. Implement `schedule_apply` with fresh-preview requirement and rollback metadata.
7. Pilot apply on one low-risk site with a harmless cadence adjustment.
8. Implement `schedule_rollback`.
9. Pilot rollback on the same site.
10. Decide whether to broaden to additional Alynt-owned schedules.

## Approval Gate Before Code

Before any V2.3 code implementation, require explicit approval for:

- exact first managed schedule target;
- whether the first slice is preview-only or preview/apply/rollback;
- dashboard and uploader repository restore points;
- protocol and threat-model updates;
- local test target;
- live pilot target, if any;
- release/deployment plan;
- rollback plan.

Recommended first implementation slice: **preview-only schedule capability reporting and dashboard display**. This gives operational visibility with no schedule mutation and keeps the first V2.3 step as small as possible.
