# Alynt Drime Backups Dashboard V2 Remote Actions Plan

This document captures future remote-operation planning for **Alynt Drime Backups Dashboard**. It is intentionally separate from the v1 implementation plan because v1 is an operational read-only monitoring dashboard, while remote actions introduce a materially different security and reliability model.

Status: planning only. No runtime implementation is approved by this document.

## Boundary Decision

Version 1 remains read-only:

- dashboard-generated pairing token;
- explicit client-site opt-in;
- dashboard polling of one fixed authenticated read-only status endpoint;
- local dashboard registry/history actions only;
- no dashboard-to-client or dashboard-to-Drime mutation.

Version 2 may explore remote operations only as narrowly scoped, signed, auditable action intents. It must not become arbitrary remote command execution, generic filesystem browsing, or centralized Drime credential storage.

## Core Architecture Principle

The dashboard should not store client Drime API credentials in v2.

Preferred architecture:

1. The dashboard creates a signed, site-scoped, action-scoped intent.
2. The client uploader validates the dashboard credential, action type, nonce/idempotency key, capability, freshness window, and local opt-in policy.
3. The client uploader performs the action locally using its own existing settings and Drime credentials.
4. The dashboard learns the result through a status/action-history endpoint or the existing polling pipeline.
5. Every action is recorded in a redacted audit trail on both sides.

This preserves the current ownership boundary: the dashboard coordinates; the client site executes.

## Non-Negotiable V2 Requirements

Before any remote action ships:

- new protocol document, separate from `PROTOCOL_V1.md`;
- new threat model for command authorization, replay, confused deputy, compromise, rollback, and destructive action risks;
- explicit client-side opt-in for remote actions, separate from read-only monitoring opt-in;
- per-action capability checks on the dashboard and client;
- per-site remote-action enablement and disablement;
- action allowlist with stable action identifiers;
- signed requests with replay protection, expiry, idempotency keys, and nonce storage;
- no generic command strings or shell execution;
- no raw filesystem paths, package contents, credentials, salts, cookies, nonces, signed URLs, or Drime tokens in dashboard-visible payloads;
- dry-run/preview mode for destructive or persistent changes;
- explicit administrator confirmation for every destructive or persistent action;
- bounded rate limits, queue limits, retries, lock expiry, and stale-action handling;
- durable audit trail with redacted inputs, actor, site, action, timestamps, state transitions, and result summaries;
- rollback or recovery guidance for each action type;
- compatibility handling for clients that support read-only v1 but not remote actions;
- release and deployment plan that upgrades the uploader/client side before enabling dashboard action controls.

## Action Classes

Risk cutoff: **V2.1 is the only initial low-risk candidate** because it is non-destructive when implemented as a bounded request/scan action. Every slice after V2.1 is a higher-risk gated phase. V2.2 is still non-destructive, but it hardens the action model and auditability before more sensitive controls are considered. V2.3 and later mutate persistent behavior, delete/clean data, or affect restore posture and must be treated as high-risk.

### Candidate V2.1: Request Backup Now

Purpose: let an operator request a client site to create and/or scan for a backup now, then observe the result through normal status polling.

Risk: lowest acceptable remote-action risk. This creates load and state on the client, but it is not inherently destructive. It is the only initial low-risk candidate for v2 implementation.

Recommended constraints:

- disabled by default per client site;
- action choices are explicit, for example `server_backup_now`, `wpvivid_backup_now`, or `scan_upload_now`;
- no arbitrary schedule or shell command input;
- client enforces concurrency locks and minimum intervals;
- dashboard displays accepted, running, succeeded, failed, timed out, and rate-limited states;
- status payload remains redacted and does not expose local paths or Drime secrets.

This should be the first implemented remote-action slice if v2 proceeds.

### Candidate V2.2: Action History And Audit UI Hardening

Purpose: make remote-action state visible, reviewable, and support-safe before any persistent behavior change is allowed.

Risk: non-destructive, but still gated because it defines the operator evidence model for later higher-risk actions.

Recommended constraints:

- implement a dashboard-side action history view before schedule, cleanup, delete, or restore controls;
- show actor, site, action type, requested time, accepted/running/completed/failed state, final result summary, and correlation ID;
- show only redacted arguments and results;
- separate operator-visible history from deeper support diagnostics;
- include filters for pending, running, failed, destructive, and recent actions;
- preserve bounded retention and cleanup rules for action history;
- make failed or stale actions visible without exposing raw response bodies, paths, Drime identifiers, or credentials;
- add matching client-side audit summaries so dashboard records can be reconciled with client-local execution records.

This slice should happen immediately after V2.1 so later higher-risk slices inherit consistent audit behavior instead of adding it retroactively.

### Candidate V2.3: Schedule Management

Purpose: update approved backup or scan/upload schedules from the dashboard.

Risk: higher-risk gated phase. It changes future backup behavior and therefore requires previews, rollback metadata, and explicit administrator confirmation.

Recommended constraints:

- client publishes a redacted schedule capabilities document;
- dashboard only offers schedules the client declares as supported;
- every proposed change has a before/after preview;
- client stores prior schedule state for rollback;
- dashboard records who changed what and when through the V2.2 action history/audit UI;
- schedule changes cannot disable all backup production without explicit high-friction confirmation.

### Candidate V2.4: Cleanup And Retention Actions

Purpose: clean local outbox/staging artifacts or request remote retention cleanup.

Risk: higher-risk gated phase. This is destructive or semi-destructive and must not be implemented until V2.1 and V2.2 are proven.

Recommended constraints:

- dry-run required before apply;
- dry-run result contains counts, categories, age bands, and byte totals, not raw paths or Drime object IDs;
- apply requires a fresh dry-run token so the operator cannot apply stale cleanup scope;
- client executes cleanup locally and reports redacted totals;
- no dashboard-side Drime API credentials;
- no raw delete-by-path or delete-by-id command from the dashboard.

### Candidate V2.5: Delete Backup Sets

Purpose: delete specific backup sets from the approved destination.

Risk: higher-risk gated phase. This is destructive and restore-readiness-sensitive.

Recommended constraints:

- strongly defer until inventory evidence is mature;
- dashboard can select only client-reported opaque backup-set references created for this action flow;
- client validates that the backup set is safe to delete under local retention policy;
- two-step confirmation with preview, apply, and post-delete verification;
- never expose raw Drime object IDs, signed URLs, local file paths, or API tokens to the dashboard.

### Candidate V2.6: Restore Preparation

Purpose: identify, validate, and stage a restore candidate without overwriting production data.

Risk: higher-risk gated phase. It can be non-destructive when limited to staging/validation, but it is restore-readiness-sensitive and can mislead operators if evidence is incomplete.

Recommended constraints:

- first support only restore-candidate inspection and staging validation;
- no production overwrite in this slice;
- client validates checksums, sidecars, manifest compatibility, and required restore components;
- dashboard shows readiness summaries, not raw paths or package internals;
- require a separate restore-runbook approval before execution is designed.

### Candidate V2.7: Restore Execution

Purpose: perform a restore.

Risk: very high. This should not be implemented until backup-now, audit history, schedule management, inventory, cleanup, and restore preparation are mature and tested.

Recommended constraints:

- disposable/staging restore drills first;
- mandatory fresh backup/restore point before execution;
- high-friction human approval gates;
- explicit target environment protections so production cannot be restored accidentally;
- detailed rollback/runbook;
- no fully automated production restore in early v2.

## Features To Avoid Or Keep Out Of Scope

### Dashboard Drime Token Storage

Avoid. Centralizing Drime credentials in the dashboard increases blast radius. Prefer client-side execution using existing local credentials.

### Generic Filesystem Browsing

Avoid. If file evidence is needed, expose redacted, bounded, purpose-built backup inventory summaries instead of browsable paths.

### Arbitrary Command Execution

Do not implement. It would turn the dashboard into a remote shell/control panel and invalidate the purpose-built backup safety model.

### Arbitrary URL Health Checks

Keep separate from backup operations. If needed later, design it as a dedicated monitoring feature with SSRF protections and without inheriting backup-action privileges.

## Suggested V2 Sequence

1. V2 planning baseline: protocol, threat model, action audit schema, and client opt-in model.
2. V2.1 request backup now / scan-upload now as the only initial low-risk candidate.
3. V2.2 action history and operator audit UI hardening before any higher-risk controls.
4. V2.3 schedule management with preview and rollback as a higher-risk gated phase.
5. V2.4 cleanup dry-run/apply for local artifacts only as a higher-risk gated phase.
6. V2.5 remote retention/delete planning after inventory evidence is mature as a higher-risk gated phase.
7. V2.6 restore preparation as a higher-risk gated phase.
8. V2.7 restore execution only after staging drills and explicit production safety gates.

## Acceptance Gate For Starting V2 Implementation

Do not implement v2 remote actions until:

- v1 read-only acceptance is complete or explicitly deferred;
- dashboard and uploader repositories each have a clean release baseline;
- a v2 protocol document is approved;
- a v2 threat model is approved;
- a client-side action opt-in UX is designed;
- action audit storage is designed;
- the first action slice has focused tests in both repositories;
- a live rollout plan exists that keeps all remote actions disabled until each client explicitly opts in.
