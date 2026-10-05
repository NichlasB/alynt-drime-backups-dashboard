# V2.3 Schedule Rollback Decision Record

Status: planning-only decision record.

Date: 2026-10-05

This document records the next product decision after the completed non-mutating Schedule Rollback Preview proof. It does not approve runtime rollback execution, does not change dashboard or client behavior, and does not add protocol fields, UI controls, client opt-ins, deployment steps, or live-site changes.

## Current Baseline

The dashboard and uploader have already shipped the lower-risk schedule-management ladder:

1. Passive `alynt_scan_upload` schedule capability reporting and dashboard display.
2. Non-mutating signed `schedule_preview`.
3. Guarded signed `schedule_apply` for `alynt_scan_upload` cadence changes only.
4. Rollback-readiness metadata capture/display as support-safe evidence.
5. Non-mutating signed `schedule_rollback_preview`.
6. A PureCleanse-only rollback-preview proof completed on 2026-09-30, ending with the pilot restored to `every_15_minutes`, rollback-preview disabled again, and `schedule_rollback` still unavailable.

The current dashboard may display support-safe rollback-readiness metadata and rollback-preview results as evidence. It must continue to render no rollback execution control and must dispatch no `schedule_rollback` action unless a later protocol, threat model, implementation plan, release, and live enablement gate explicitly approve it.

## Decision

Do not implement `schedule_rollback` as the next runtime slice.

The recommended next state is continued deferral of mutating rollback execution. The completed preview proof is useful evidence, but it does not by itself justify a new persistent schedule mutation action.

Any future `schedule_rollback` work must start as a fresh design slice, not as a direct implementation from the preview code.

## Rationale

`schedule_rollback` is materially higher risk than preview:

- it changes persistent client backup behavior;
- it depends on still-valid historical rollback metadata;
- it must revalidate current client schedule state immediately before mutation;
- stale metadata, changed current cadence, or unsupported local client state could otherwise cause the dashboard to restore the wrong cadence;
- it could be mistaken for a broad restore/undo feature unless the UI and audit trail stay very narrow;
- Schedule Apply remains disabled by default on clients and is intentionally per-client gated.

The dashboard already has useful operator evidence from rollback preview without adding another mutating action. Keeping rollback execution deferred preserves the principle that remote actions should advance only when the operational need clearly outweighs the added risk.

## Future Design Gate

A future `schedule_rollback` design may be considered only if all of the following are true:

- repeated operational evidence shows rollback execution would materially reduce risk or operator burden;
- the action remains limited to `alynt_scan_upload` cadence rollback only;
- the client already reports explicit `schedule_rollback_preview` support and a fresh successful preview exists;
- the preview result is fresh, still references the intended source `schedule_apply`, and still matches current client state;
- a separate client-local `schedule_rollback` opt-in exists and is disabled by default;
- the dashboard requires explicit administrator confirmation and explains the before/after cadence;
- the client revalidates schedule ID, current cadence/fingerprint, rollback cadence, metadata fingerprint, expiry, and capability version immediately before applying;
- the dashboard records a redacted audit trail with source apply action, source preview action, result code, requested actor, timestamps, and support-safe summary;
- protocol and threat-model docs are updated before code implementation;
- tests prove stale/current-state rejection, missing metadata rejection, unsupported schedule rejection, unsafe cadence rejection, rate limiting, idempotency, and no raw path/credential/Drime exposure.

## Non-Goals

This decision does not approve:

- schedule rollback execution;
- rollback controls in the dashboard UI;
- broad rollback-preview enablement;
- rollback for WPvivid, server-runner, Drime retention, cleanup, restore, backup creation, or arbitrary schedules;
- raw cron expressions, arbitrary intervals, option writes, shell commands, filesystem paths, Drime IDs, credentials, or dashboard-side Drime token storage;
- production or live-site enablement;
- client-side opt-in changes.

## Recommended Next Product Slice

Continue with non-destructive dashboard maintenance or observability unless a concrete operator need justifies a fresh rollback-execution design.

If rollback execution is later reconsidered, the next slice should be:

`V2.3 Schedule Rollback Runtime Design`

That slice should produce a dedicated design artifact and updated protocol/threat-model text before any implementation begins.
