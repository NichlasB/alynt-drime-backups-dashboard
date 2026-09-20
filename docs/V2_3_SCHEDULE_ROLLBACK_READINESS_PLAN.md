# V2.3 Schedule Rollback Readiness Plan

Status: planning-only readiness slice. This document does not approve implementation, release, deployment, live-site writes, broad client enablement, `schedule_rollback` runtime behavior, backup creation, cleanup/delete actions, restore actions, WPvivid schedule management, server-runner schedule management, arbitrary cron editing, arbitrary setting mutation, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`
- `docs/V2_3_SCHEDULE_APPLY_IMPLEMENTATION_PLAN.md`
- `docs/V2_3_ROLLBACK_METADATA_CAPTURE_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Goal

Define the approval gates, evidence requirements, protocol shape, UI posture, tests, and rollout proof that would be required before a future `schedule_rollback` action can be considered.

The immediate purpose is to decide whether rollback execution is worth designing next, not to add runtime behavior. The current released behavior remains:

- `schedule_apply` may change only the Alynt uploader scan cadence after a fresh matching preview and separate client-local Schedule Apply opt-in.
- Successful apply results may expose support-safe rollback-readiness metadata.
- `schedule_rollback` remains unavailable.
- The dashboard must render no rollback button, dispatch path, or runtime action.

## Recommended Approach

Treat rollback as a separate high-risk V2.3 sub-slice after metadata capture has been proven in live operation.

Recommended sequence:

1. Keep collecting and displaying rollback-readiness metadata from successful `schedule_apply` results.
2. Confirm the metadata is consistently present, support-safe, and enough to reconstruct the previous allowed cadence.
3. Design a non-mutating `schedule_rollback_preview` first, if rollback proceeds.
4. Only after preview proof, design a separately gated `schedule_rollback` apply action.
5. Keep all rollback work scoped to `alynt_scan_upload` until that flow has isolated tests and a low-risk pilot.

## Boundary

Allowed in this planning slice:

- document rollback readiness requirements;
- define candidate protocol extensions as draft-only;
- define UI and test expectations;
- define release and live proof gates;
- identify blockers before runtime design.

Not allowed in this planning slice:

- adding `schedule_rollback` to `allowed_actions`;
- adding dashboard rollback controls;
- adding dashboard dispatch code;
- adding client runtime rollback execution;
- changing schedule state;
- changing WPvivid, server-runner, Drime, retention, cleanup, delete, restore, credential, or arbitrary settings behavior;
- changing live sites, releases, tags, deployment artifacts, or production data.

## Preconditions Before Runtime Design

Do not design or implement runtime rollback until all of the following are true:

- at least one approved pilot client has proven successful `schedule_apply` with rollback metadata captured and displayed;
- the pilot is safely returned to the intended cadence after proof;
- the metadata contains previous cadence, applied cadence, previous next-run evidence, applied next-run evidence, before/after fingerprints, source action IDs, capture time, and expiry time;
- dashboard action history can show rollback-readiness evidence without raw internals;
- support export/diagnostics aggregate rollback-readiness without exposing sensitive details;
- tests prove the current system still rejects `schedule_rollback`;
- the user separately approves moving from readiness planning into runtime design.

## Candidate Future Protocol Shape

If rollback proceeds, prefer a two-step model:

1. `schedule_rollback_preview`
   - non-mutating;
   - references one successful `schedule_apply` action ID;
   - references the captured rollback metadata fingerprint;
   - asks the client to revalidate whether the current schedule still matches the post-apply state;
   - returns a redacted before/after preview.

2. `schedule_rollback`
   - mutating;
   - references a fresh successful `schedule_rollback_preview`;
   - applies only the previous allowlisted Alynt scan/upload cadence;
   - revalidates current schedule fingerprint immediately before mutation;
   - records a new audit row and reports rollback result through normal polling.

Draft request fields should be bounded and support-safe:

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

```json
{
  "action_type": "schedule_rollback",
  "schedule_rollback": {
    "schedule_id": "alynt_scan_upload",
    "rollback_preview_action_id": "00000000-0000-4000-8000-000000000001",
    "rollback_preview_fingerprint": "sha256-redacted-preview-fingerprint",
    "capability_version": 1
  }
}
```

The exact field names may change during a later approved implementation plan. The durable rule is that rollback must reference prior client-produced evidence and a fresh non-mutating preview. It must not accept free-form cadence, cron, option, path, command, or settings input from the dashboard.

## Safety Requirements For Any Future Rollback

Any runtime rollback design must:

- remain disabled by default on every client;
- require V1 pairing and V2 action opt-in;
- require separate local Schedule Apply opt-in and a later separate local Schedule Rollback opt-in;
- support only `alynt_scan_upload`;
- reject unsupported schedule IDs and unsupported cadence labels;
- reject raw cron expressions, raw WP-Cron arrays, crontab fragments, option names/values, paths, usernames, shell commands, package names, Drime identifiers, credentials, tokens, cookies, nonces, salts, signed URLs, and arbitrary settings payloads;
- require a fresh successful rollback preview;
- revalidate the current schedule fingerprint immediately before rollback;
- fail closed if the schedule changed since the captured apply or rollback preview;
- never disable all schedule execution;
- record dashboard and client audit history;
- preserve idempotency, replay protection, one-running-action locks, and rate limits;
- keep dashboard Drime-token-free and client-execution-owned.

## Dashboard UI Requirements

Until runtime rollback is separately implemented and released:

- render rollback as unavailable;
- display rollback metadata as evidence only;
- render no rollback form, button, link, AJAX action, or dispatch path;
- avoid wording that implies rollback can currently be performed.

If rollback preview is later approved:

- show it as a non-mutating preview only;
- require explicit operator confirmation that the preview does not change cadence;
- show the referenced apply action, previous cadence, current cadence, and expected rollback target;
- explain that a later apply step is still separate.

If rollback apply is later approved:

- require a fresh preview;
- require high-friction confirmation;
- show exactly which cadence will be restored;
- show that only the Alynt uploader scan cadence is affected;
- warn that upload-worker cadence, WPvivid, server-runner, Drime, retention, cleanup, delete, restore, and credentials are untouched.

## Test Plan For A Later Runtime Slice

Dashboard tests:

- controls hidden while rollback is unsupported;
- rollback preview controls hidden unless client capability explicitly reports support;
- rollback apply controls hidden unless a fresh rollback preview exists;
- signed payload contains only allowlisted rollback references;
- stale preview blocks rollback apply;
- action history labels rollback preview and rollback apply distinctly;
- support export redacts all rollback context and contains no raw internals.

Uploader tests:

- `schedule_rollback` rejected before opt-in;
- `schedule_rollback_preview` rejected before opt-in;
- unknown schedule IDs rejected;
- raw cron/free-form cadence/options/paths/commands rejected;
- expired or missing metadata rejected;
- stale current schedule fingerprint rejected;
- preview is non-mutating;
- apply changes only `alynt_scan_upload` cadence;
- idempotency replay returns prior state without reapplying;
- rollback result remains redacted and support-safe.

Cross-plugin tests:

- successful apply captures metadata;
- rollback preview reconciles to dashboard action history without mutation;
- rollback apply reconciles after a fresh preview;
- normal V1 polling, V2.1 `scan_upload_now`, V2.3 `schedule_preview`, and V2.3 `schedule_apply` continue to work unchanged.

## Release And Rollout Gates

Before any runtime rollback release:

1. Approve updated `PROTOCOL_V2.md` and `THREAT_MODEL_V2.md`.
2. Approve uploader implementation plan and dashboard implementation plan.
3. Create or confirm dashboard and uploader restore points.
4. Implement uploader support first with rollback disabled by default.
5. Implement dashboard support after client capability reporting exists.
6. Run applicable ds2 feature workflows for both repositories.
7. Run the ds3 pre-release workflow, including security, edge-case, adversarial tests, i18n, accessibility, docs, and uninstall/database reviews if storage changes.
8. Release uploader first.
9. Release dashboard second.
10. Enable on one low-risk pilot client only after explicit approval.
11. Prove preview-only behavior before enabling rollback apply.
12. Prove rollback apply, then return the pilot to intended cadence.

## Acceptance Criteria For This Planning Slice

- The implementation roadmap clearly identifies `schedule_rollback` as a future separately gated action.
- Protocol and threat-model docs reserve rollback without approving runtime behavior.
- The next implementation decision is explicit: either continue observing rollback metadata, or start a new approved runtime design slice for `schedule_rollback_preview`.
- No source code, release, deployment, live site, client setting, or production data changed.

## Approval Gate Before Runtime Work

Before runtime implementation begins, explicitly approve:

- whether to design `schedule_rollback_preview` first;
- whether rollback should remain limited to `alynt_scan_upload`;
- the required client-local rollback opt-in wording;
- metadata expiry/readiness policy;
- dashboard and uploader restore points;
- pilot target;
- release/deployment timing.
