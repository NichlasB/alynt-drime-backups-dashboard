# V2.4 Cleanup Preview Implementation Plan

Status: dashboard-side local implementation slice completed and validated; release/deployment/live enablement remain separate gates. This document narrows V2.4 to the non-mutating `cleanup_preview` action only. It does not approve `cleanup_apply`, Drime deletion, backup-set deletion, restore behavior, arbitrary filesystem browsing, dashboard-side Drime credentials, release, deployment, or live enablement.

Related artifacts:

- `docs/V2_4_CLEANUP_RETENTION_DESIGN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- `docs/V2_2_REMOTE_ACTION_HISTORY_AUDIT_PLAN.md`

## Goal

Implement the smallest safe V2.4 runtime-adjacent slice after separate approval: a signed, non-mutating `cleanup_preview` action that asks one opted-in client site to report support-safe aggregate cleanup eligibility for Alynt uploader-owned temporary artifacts.

The preview should answer:

- whether the client supports cleanup preview;
- which allowlisted local cleanup categories are eligible;
- how many eligible items exist per category;
- approximate bytes per category and total;
- age bands and reason codes;
- why preview is unavailable, unsupported, unsafe, or blocked.

The preview must not delete files, delete Drime objects, delete backup sets, change schedules, create backups, restore data, mutate dashboard records, browse arbitrary files, expose paths, or alter credentials.

## Scope

Included:

- protocol documentation for `cleanup_preview` only;
- threat-model documentation for preview-specific risks;
- uploader-side capability reporting for disabled-by-default cleanup preview support;
- uploader-side signed `cleanup_preview` validation and non-mutating evaluator;
- dashboard-side capability parsing and Site Detail preview control rendering;
- dashboard-side signed dispatch for `cleanup_preview`;
- dashboard action-history rendering for preview results;
- support-safe diagnostics aggregates for cleanup preview capability/action counts;
- focused tests in both repositories;
- a low-risk local/disposable proof after implementation, before any release.

Excluded:

- `cleanup_apply` runtime behavior;
- any deletion/mutation of local files or records;
- Drime remote retention or deletion;
- backup-set deletion;
- WPvivid backup pruning;
- server-runner cleanup outside uploader-owned staging/transient state;
- restore preparation or execution;
- dashboard Drime credentials;
- raw path/object display;
- Sites-row cleanup controls;
- live enablement or deployment without a later release gate.

## Safety Boundary

`cleanup_preview` is a remote action, but it must be non-mutating.

The dashboard may request a preview only when all of these are true:

- latest client status reports V2 actions enabled;
- latest client status reports cleanup preview capability;
- `cleanup_preview` is allowlisted by the client;
- the dashboard has a valid action key for the paired site;
- the operator passes capability and nonce checks;
- the request contains only allowlisted category slugs, scope, and capability version.

The client must own all file/path and registry decisions. The dashboard must never send paths, item names, object IDs, raw retention rules, or free-form criteria.

## Proposed Cross-Repository Sequence

### Slice 1 — Protocol And Threat Model

Update docs before runtime code:

1. Add `cleanup_management` capability shape to `docs/PROTOCOL_V2.md`.
2. Add `cleanup_preview` request/result shape to `docs/PROTOCOL_V2.md`.
3. Add cleanup-preview threats to `docs/THREAT_MODEL_V2.md`.
4. Keep `cleanup_apply` documented as reserved/deferred, not implemented.

Exit criteria:

- docs clearly state preview-only behavior;
- forbidden fields are explicit;
- `cleanup_apply`, Drime retention, delete, and restore remain unavailable.

### Slice 2 — Uploader Capability And Preview Evaluator

Implement in the companion uploader plugin first:

1. Add disabled-by-default client-local cleanup preview opt-in/policy.
2. Report `remote_actions.cleanup_management` only when V2 action support exists.
3. Add stable allowlisted category slugs, initially `uploader_temp_artifacts` and optionally `stale_uploader_transient_records`.
4. Add a read-only evaluator that computes aggregate counts/bytes/age bands from client-owned safe roots or registries.
5. Reject unsafe categories, scopes, request fields, and unavailable local state.
6. Add tests proving preview is non-mutating and never exposes raw paths.

Exit criteria:

- client can advertise preview capability only after explicit local opt-in;
- signed preview requests return support-safe aggregate evidence;
- schedule, backup, restore, delete, Drime, and credential behavior are unchanged.

### Slice 3 — Dashboard Capability Consumption And UI

Implementation status: completed locally in the dashboard codebase after the companion uploader proof. The dashboard now sanitizes optional `remote_actions.cleanup_management`, hides controls unless cleanup preview is explicitly supported by the latest client report, dispatches a fixed signed `cleanup_preview` request from Site Detail only, renders support-safe cleanup preview history details, and includes cleanup-preview aggregate counts in Diagnostics/support output. The slice remains unreleased and undeployed until a separate release gate.

Implemented dashboard behavior:

1. Sanitize optional `remote_actions.cleanup_management`.
2. Show a Site Detail `Cleanup Preview` section only when the latest client capability explicitly supports it.
3. Keep Sites rows display-only and quiet.
4. Dispatch signed `cleanup_preview` only from Site Detail with nonce/capability checks and clear non-mutating copy.
5. Render Remote Action History entries as `Cleanup Preview`.
6. Show category/count/byte/age-band/reason summaries without raw paths or object identifiers.
7. Add Diagnostics/support-safe aggregate counts for cleanup-preview support/action states.

Exit criteria:

- cleanup preview controls stay hidden for v1-only, V2-only, unsupported, or disabled clients;
- action history remains support-safe;
- no apply/delete button exists.

### Slice 4 — Local Proof And Release Readiness

Before release:

1. Run ds2 feature reviews that apply for both repositories.
2. Run targeted ds3 pre-release reviews before any release.
3. Test a disposable or explicitly approved low-risk site with preview-only support enabled.
4. Confirm preview does not mutate local artifacts or registry state.
5. Confirm normal dashboard polling and backup freshness classification are unaffected.

Exit criteria:

- one preview-only proof returns either support-safe aggregate evidence or a concrete fail-closed reason;
- no cleanup apply is available;
- no live rollout occurs without a separate release/deploy gate.

## Dashboard Request Shape

Planned signed body extension:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "action_type": "cleanup_preview",
  "requested_at": "2026-10-01T12:00:00Z",
  "expires_at": "2026-10-01T12:05:00Z",
  "idempotency_key": "adb-act-example-0000000000000000",
  "cleanup_preview": {
    "capability_version": 1,
    "scope": "safe_local_uploader_owned",
    "categories": [
      "uploader_temp_artifacts"
    ]
  }
}
```

Forbidden request fields:

- paths;
- filenames;
- package names;
- backup IDs;
- Drime object IDs;
- Drime credentials;
- signed URLs;
- arbitrary URLs;
- shell commands;
- SQL;
- regex/glob patterns;
- raw retention dates;
- arbitrary age thresholds;
- option names/values;
- free-form labels or reason text.

## Client Result Shape

Planned support-safe result summary:

```json
{
  "action_type": "cleanup_preview",
  "state": "succeeded",
  "result_code": "cleanup_preview_ready",
  "result_summary": "Cleanup preview is ready. No files or records were changed.",
  "cleanup_preview": {
    "capability_version": 1,
    "scope": "safe_local_uploader_owned",
    "preview_fingerprint": "sha256-redacted-preview-fingerprint",
    "expires_at": "2026-10-01T12:15:00Z",
    "categories": [
      {
        "category": "uploader_temp_artifacts",
        "eligible_count": 3,
        "approx_bytes": 10485760,
        "age_band": "older_than_7_days",
        "reason_code": "stale_temp_artifacts"
      }
    ],
    "total_eligible_count": 3,
    "total_approx_bytes": 10485760
  }
}
```

Allowed dashboard-visible fields must remain scalar or bounded arrays of sanitized objects. Raw paths, object identifiers, registry payloads, file names, Drime IDs, credentials, and raw response bodies must be rejected or redacted.

## Dashboard UI Requirements

- Site Detail only.
- Label: `Cleanup Preview`.
- Copy must say preview is non-mutating and does not delete files.
- Show capability state: unavailable, supported but disabled, ready, preview stale, or latest preview available.
- Show aggregate category rows only.
- No apply button.
- No Sites-row cleanup button.
- No raw details disclosure that could expose paths or identifiers.

## Test Plan

Dashboard tests:

- capability sanitizer accepts documented cleanup fields;
- unsafe capability fields are ignored/rejected;
- Site Detail controls hidden when cleanup preview is unsupported;
- Site Detail controls shown only for supported opted-in clients;
- request builder emits only allowlisted preview fields;
- action history renders support-safe cleanup preview details;
- diagnostics/support copy include only aggregate cleanup-preview counts.

Uploader tests:

- cleanup preview hidden/disabled by default;
- preview request rejected before local opt-in;
- unknown category rejected;
- unsupported scope rejected;
- forbidden request fields rejected;
- preview evaluator does not mutate filesystem, registry, queue, schedules, credentials, or Drime;
- result redaction rejects paths and object IDs;
- idempotency and locks behave deterministically.

Cross-plugin tests:

- v1-only clients remain monitored with cleanup controls hidden;
- preview-capable client accepts one signed preview request;
- dashboard records accepted/final preview state;
- normal backup freshness stays based on status evidence, not cleanup preview.

## Approval Gates

Required before implementation:

- approve protocol/threat-model documentation updates;
- confirm companion uploader repo target and restore-point recommendation;
- approve dashboard implementation after uploader capability proof;
- approve any release/deploy/pilot separately.

Required before `cleanup_apply` planning:

- successful preview-only release and pilot;
- threat-model update for destructive local cleanup;
- restore-point workflow for any pilot;
- explicit decision that cleanup apply is worth implementing.

## Acceptance Criteria For This Planning Slice

- A dedicated `cleanup_preview` implementation plan exists.
- It limits the next runtime step to non-mutating preview only.
- It defines dashboard/uploader sequencing and verification.
- It blocks cleanup apply, Drime deletion, backup-set deletion, restore, arbitrary filesystem browsing, dashboard Drime credentials, release, deploy, and live enablement behind later gates.
