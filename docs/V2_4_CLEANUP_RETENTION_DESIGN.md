# V2.4 Cleanup And Retention Actions Design

Status: design-only decision record. This document does not approve implementation, release, deployment, live enablement, Drime deletion, remote retention mutation, restore behavior, arbitrary filesystem access, or dashboard-side Drime credential storage.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- `docs/V2_2_REMOTE_ACTION_HISTORY_AUDIT_PLAN.md`
- `docs/V2_3_SCHEDULE_ROLLBACK_PREVIEW_DESIGN.md`

## Goal

Define the safest possible first V2.4 direction for cleanup and retention actions after V2.1 request-backup, V2.2 action history, and V2.3 schedule-management stabilization.

The first V2.4 candidate should be a local client cleanup preview/apply flow for Alynt uploader-owned temporary artifacts only. It should answer:

- What client-owned local artifact categories are eligible for cleanup?
- How many items and approximate bytes would be cleaned?
- Which age bands and stable reason codes apply?
- Is the cleanup scope still current when the operator applies it?
- What did the client clean, skip, or reject?

The first V2.4 candidate must not delete Drime objects, delete backup sets, restore data, mutate WPvivid/server-runner schedules, change Drime credentials, browse arbitrary files, accept dashboard-provided paths, or run shell commands.

## Decision

V2.4 should start with a two-step client-owned cleanup model:

1. `cleanup_preview` is non-mutating and returns redacted aggregate counts only.
2. `cleanup_apply` is mutating and may execute only a fresh, matching, client-owned preview token/fingerprint.

The first runtime scope should be narrower than the broad title "Cleanup And Retention Actions":

- allowed first scope: Alynt uploader-owned local temporary/staging artifacts and stale local transient records that the client already knows how to safely classify;
- explicitly deferred: Drime remote retention, backup-set deletion, WPvivid backup pruning, server-runner package deletion outside uploader-owned staging, restore preparation, restore execution, and permanent dashboard-record deletion.

Remote Drime retention/delete work belongs to V2.5 or later, after inventory evidence, opaque backup-set references, policy checks, and destructive-action UX are mature.

## Current Baseline

The current V2 baseline already has:

- V2.1 signed `scan_upload_now`;
- V2.2 redacted remote-action history and stale reconciliation;
- V2.3 non-mutating `schedule_preview`;
- V2.3 guarded `schedule_apply` for `alynt_scan_upload` only;
- V2.3 non-mutating `schedule_rollback_preview` dashboard controls and one PureCleanse pilot proof;
- no `schedule_rollback`, cleanup, delete, restore, Drime mutation, or dashboard-side Drime credentials.

That baseline is sufficient for a design-only V2.4 decision record, but not sufficient to implement cleanup runtime behavior without additional protocol and threat-model work.

## Non-Goals

This design does not include:

- adding `cleanup_preview` or `cleanup_apply` to `allowed_actions`;
- rendering cleanup controls in the dashboard;
- changing dashboard or uploader runtime code;
- deleting local or remote backup packages;
- deleting Drime objects or changing Drime retention;
- deleting dashboard records, snapshots, action history, or support evidence;
- browsing filesystem paths or displaying raw paths;
- accepting dashboard-provided paths, package names, backup IDs, Drime object IDs, SQL, commands, or arbitrary retention scopes;
- changing uploader, WPvivid, server-runner, cron, schedule, credential, restore, or backup-creation behavior;
- changing database schema, uninstall behavior, or release packaging.

## Required Preconditions Before Runtime Implementation

Do not proceed from this design into runtime implementation until all of the following are true:

- `docs/PROTOCOL_V2.md` is updated with approved V2.4 action shapes;
- `docs/THREAT_MODEL_V2.md` is updated with cleanup-specific threats and controls;
- the uploader has a client-local allowlist of cleanup categories and owns all path resolution internally;
- the dashboard sends only opaque preview references and allowlisted category slugs, never paths or raw object identifiers;
- cleanup preview is implemented and proven before cleanup apply;
- cleanup apply requires a fresh preview fingerprint, expiry, idempotency key, client revalidation, and explicit operator confirmation;
- both dashboard and uploader tests prove unsafe fields, stale previews, unknown categories, and unsupported clients fail closed;
- a restore point/backout expectation exists for any pilot site before mutating cleanup is enabled;
- a separate release/deploy/live enablement approval gate is completed.

## Proposed Capability Shape

If runtime implementation is later approved, a client may advertise cleanup support only after a client-side release ships with cleanup disabled by default.

Candidate additive capability fields:

```json
{
  "remote_actions": {
    "allowed_actions": [
      "scan_upload_now",
      "schedule_preview",
      "schedule_apply"
    ],
    "cleanup_management": {
      "protocol_version": 2,
      "capability_version": 1,
      "enabled": true,
      "preview_supported": true,
      "apply_supported": false,
      "supported_categories": [
        "uploader_temp_artifacts",
        "stale_uploader_transient_records"
      ],
      "requires_fresh_preview": true,
      "max_preview_age_seconds": 900
    }
  }
}
```

Rules:

- `cleanup_preview` must not appear in `allowed_actions` until both dashboard and client support are released and the client-local cleanup preview opt-in is enabled.
- `cleanup_apply` must not appear in `allowed_actions` until cleanup preview has been implemented, proven, and separately approved.
- `apply_supported` must remain false for preview-only clients.
- Supported categories must be stable allowlisted slugs, not labels from arbitrary client state.

## Proposed Preview Request Shape

Draft-only signed action extension:

```json
{
  "action_type": "cleanup_preview",
  "cleanup_preview": {
    "capability_version": 1,
    "categories": [
      "uploader_temp_artifacts"
    ],
    "scope": "safe_local_uploader_owned"
  }
}
```

Rules:

- `categories` must be chosen from the latest client-declared allowlist.
- `scope` must be an allowlisted constant. The first approved value should be `safe_local_uploader_owned`.
- The request must not include paths, filenames, package names, backup IDs, Drime IDs, SQL, shell commands, regexes, glob patterns, retention dates, arbitrary age thresholds, raw option names/values, URLs, credentials, tokens, signed URLs, or free-form delete criteria.
- The client must compute all eligible items from local owned registries and internal path allowlists.

## Proposed Preview Result Shape

Draft-only client result summary:

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

Failure examples:

- `cleanup_preview_not_supported`
- `cleanup_preview_opt_in_required`
- `cleanup_preview_unknown_category`
- `cleanup_preview_scope_not_allowed`
- `cleanup_preview_local_state_unavailable`
- `cleanup_preview_unsafe_local_state`

## Proposed Apply Request Shape

Draft-only signed action extension:

```json
{
  "action_type": "cleanup_apply",
  "cleanup_apply": {
    "capability_version": 1,
    "preview_action_id": "00000000-0000-4000-8000-000000000000",
    "preview_fingerprint": "sha256-redacted-preview-fingerprint",
    "scope": "safe_local_uploader_owned"
  }
}
```

Rules:

- Apply must reference one fresh successful cleanup preview for the same site.
- The client must revalidate local state against the preview fingerprint immediately before mutation.
- The client must reject expired previews, fingerprint mismatches, unknown categories, unsafe state, missing opt-in, missing idempotency, and concurrent cleanup.
- The dashboard must not send paths, item lists, package names, object IDs, or operator-edited criteria.

## Proposed Apply Result Shape

Draft-only client result summary:

```json
{
  "action_type": "cleanup_apply",
  "state": "succeeded",
  "result_code": "cleanup_apply_completed",
  "result_summary": "Cleanup completed for uploader-owned temporary artifacts.",
  "cleanup_apply": {
    "capability_version": 1,
    "scope": "safe_local_uploader_owned",
    "source_preview_action_id": "00000000-0000-4000-8000-000000000000",
    "categories": [
      {
        "category": "uploader_temp_artifacts",
        "cleaned_count": 3,
        "skipped_count": 0,
        "approx_bytes_cleaned": 10485760,
        "reason_code": "stale_temp_artifacts"
      }
    ],
    "total_cleaned_count": 3,
    "total_skipped_count": 0,
    "total_approx_bytes_cleaned": 10485760
  }
}
```

Failure examples:

- `cleanup_apply_not_supported`
- `cleanup_apply_opt_in_required`
- `cleanup_apply_preview_missing`
- `cleanup_apply_preview_expired`
- `cleanup_apply_fingerprint_mismatch`
- `cleanup_apply_local_state_changed`
- `cleanup_apply_lock_busy`
- `cleanup_apply_unsafe_local_state`

## Dashboard UI Design For A Later Runtime Slice

The dashboard should not show cleanup controls until the latest client report explicitly advertises cleanup support and a separate dashboard implementation slice has been approved.

When runtime work is later approved:

- place cleanup controls on Site Detail only, not in the Sites row;
- start with preview-only controls;
- label the feature as local uploader cleanup, not backup deletion;
- show aggregate counts, approximate bytes, age bands, reason codes, and preview expiry;
- require explicit confirmation for apply, including text that cleanup may remove local client-owned temporary artifacts and cannot be undone by the dashboard;
- keep Remote Action History labels distinct: `Cleanup Preview` and `Cleanup Apply`;
- keep support-safe detail text available without raw paths or object identifiers.

## Dashboard Validation Rules

The dashboard must:

- build requests only from latest client capability and stored preview evidence;
- reject missing or stale preview references before apply dispatch;
- reject unsupported categories and unknown scopes;
- keep cleanup state independent from backup freshness classification;
- never display or store raw paths, package names, Drime IDs, credentials, signed URLs, raw response bodies, command strings, SQL, or arbitrary client internals;
- keep support copy aggregate-first and redacted.

## Client Validation Rules

The client must:

- keep cleanup actions disabled by default;
- require existing V1 pairing and V2 signed action opt-in;
- require separate client-local cleanup preview/apply policy before advertising support;
- own all path resolution and category eligibility decisions internally;
- enforce locks, idempotency, preview expiry, and fresh fingerprint revalidation;
- restrict cleanup to known uploader-owned safe roots and registry-owned transient state;
- fail closed when category, scope, local state, ownership, or preview evidence is uncertain.

## Threats To Add Before Runtime Implementation

The V2 threat model must be expanded before implementation with cleanup-specific threats:

- path traversal or unsafe root cleanup;
- raw path disclosure through preview/results/support exports;
- deleting active upload work or in-use temporary files;
- deleting restorable backup evidence rather than transient artifacts;
- stale preview apply after local state changes;
- replay or duplicate cleanup apply;
- cleanup category confusion between local artifacts and Drime retention;
- privilege confusion between dashboard operator approval and client-local cleanup opt-in;
- broad rollout before a pilot proves safe fail-closed behavior.

## Test Plan For A Later Runtime Slice

Dashboard tests:

- controls hidden without explicit cleanup capability;
- preview request contains only allowlisted category slugs, scope, and capability version;
- unsafe fields such as paths, package names, Drime IDs, URLs, commands, SQL, or raw criteria are never accepted or rendered;
- apply controls hidden until a fresh successful preview exists;
- apply request contains only preview action ID, preview fingerprint, scope, and capability version;
- action-history rendering labels preview/apply clearly and remains support-safe.

Uploader tests:

- cleanup actions rejected before local opt-in;
- unknown category rejected;
- unsupported scope rejected;
- paths or raw criteria in requests rejected;
- preview is non-mutating;
- apply requires fresh preview fingerprint and idempotency;
- stale, expired, mismatched, busy, or unsafe local state fails closed;
- cleanup affects only allowlisted uploader-owned transient artifacts.

Cross-plugin tests:

- V1-only sites stay monitored and cleanup controls remain hidden;
- cleanup-capable preview-only clients show preview controls only;
- one disposable pilot proves preview does not mutate;
- one separately approved pilot proves apply cleans only safe local uploader-owned transient artifacts;
- normal backup-source freshness remains based on status evidence, not cleanup action state.

## Release And Pilot Gate

Before any V2.4 runtime release:

- run applicable ds2 feature workflow reviews for dashboard and uploader changes;
- run targeted ds3 pre-release reviews, including security, i18n, accessibility, error handling, edge cases, performance, uninstall/database if storage changes, and release packaging;
- create restore points for any pilot client before enabling cleanup apply;
- release uploader support before dashboard controls depend on it;
- pilot on one low-risk site with explicit approval;
- keep `cleanup_apply` disabled by default after release until a per-site client opt-in is approved.

## Acceptance Criteria For This Design Slice

- A dedicated V2.4 cleanup/retention design artifact exists.
- The design limits the first V2.4 direction to client-owned local cleanup preview/apply.
- Drime deletion, backup-set deletion, restore, arbitrary filesystem browsing, and dashboard Drime credentials remain out of scope.
- Runtime implementation, release, deploy, push, and live enablement remain blocked behind later protocol/threat-model and approval gates.
