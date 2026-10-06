# Local Retained Record Removal Design

This document plans a possible future dashboard-local permanent removal flow for retained records. It does not implement the flow, change schema, delete data, contact client sites, change credentials, or alter any live-site behavior.

## Purpose

The dashboard currently keeps revoked and expired-pending records for audit/history. The archive/unarchive flow reduces operational clutter while preserving records, snapshots, action history, and support evidence. A later permanent removal control may be useful for clearly abandoned local records, but it is inherently destructive because it can remove dashboard-owned history and encrypted credential remnants if eligibility is wrong.

The recommended direction is to treat permanent removal as a separate high-friction local maintenance action, not as a normal Sites-row control.

## Current Decision

As of 2026-10-06, do not treat permanent local removal as the default next implementation slice. Archive/unarchive plus display-only removal-readiness evidence is the safer operational baseline because it keeps old records out of normal monitoring views while preserving dashboard-owned audit/history.

Re-open implementation planning only if there is a concrete operational reason, such as:

- archived retained records are creating real operator clutter even in archived views;
- dashboard-owned retained data volume creates measurable database or performance pressure;
- clearly abandoned test/duplicate records need a formal data-minimization path;
- compliance or retention policy requires a deliberate dashboard-local purge workflow.

Until one of those conditions is met, keep permanent removal deferred. Future implementation remains feasible, but it must enter through the approval gate below because it would introduce a destructive dashboard-local database action.

## Scope

In scope for a future implementation:

- dashboard-local records only;
- records that are already non-polling and archived;
- support-safe preview of what would be removed;
- deletion of the eligible site row and dashboard-owned dependent rows only after an explicit confirmation step;
- operator audit entry with redacted counts and reason code;
- tests proving active, polling, credentialed, paused, awaiting-first-poll, and unarchived records cannot be removed.

Out of scope:

- live-site actions;
- client-site mutation;
- Drime mutation;
- backup creation;
- restore;
- cleanup/delete apply on client sites;
- schedule apply or rollback;
- credential rotation;
- automatic removal;
- bulk removal;
- removal by domain alone;
- hiding failed eligibility checks.

## Recommended UX

Do not add a permanent-remove button to the default Sites table.

Add a future control only in the archived-records view and/or archived Site Detail screen after these conditions are true:

- the record is archived;
- the record is locally revoked or an expired pending record;
- no polling credentials or action signing credentials are present;
- the record is not paused active monitoring;
- the record is not awaiting first poll;
- no non-terminal remote action history exists for that record.

The first click should generate a local preview, not delete anything. The preview should show support-safe counts:

- one site record;
- number of retained snapshots;
- number of remote-action history rows;
- whether encrypted polling credentials are absent;
- whether action signing credentials are absent;
- latest snapshot timestamp, if any;
- latest action timestamp, if any;
- an eligibility verdict and reason.

The confirmation should require a typed phrase such as `REMOVE LOCAL RECORD` and a fresh nonce. The confirmation copy should say this removes dashboard-owned monitoring history only and does not contact the client site or Drime.

## Data Boundary

A future implementation may delete only dashboard-owned rows:

- `wp_alynt_drime_dashboard_sites` row for the selected dashboard site ID;
- `wp_alynt_drime_dashboard_snapshots` rows where `dashboard_site_id` matches;
- `wp_alynt_drime_dashboard_actions` rows where `dashboard_site_id` matches.

It must not delete:

- dashboard-wide options;
- diagnostics event history unrelated to the selected dashboard site;
- source policy options unless a precise site-scoped cleanup is implemented and tested;
- plugin files;
- client-site data;
- Drime data.

## Eligibility Rules

The future action should fail closed unless all conditions are true:

- current user has the dashboard admin capability;
- request has a valid nonce for the preview or confirm step;
- site ID resolves to exactly one dashboard-owned row;
- `archived_at` is not empty;
- `enrollment_status` is `revoked`, or `enrollment_status` is `pending` with an expired `pairing_expires_at`;
- `polling_key_id` and `polling_secret_ciphertext` are empty;
- `action_key_id` and `action_private_key_ciphertext` are empty;
- `next_poll_at` is empty;
- `paused_at` is empty;
- no action rows for the site are in non-terminal states such as queued, accepted, running, or awaiting confirmation.

Do not infer eligibility from UI visibility alone. Re-read the database state at confirmation time.

## Audit And Support Copy

Record an always-on local audit event for preview and confirm attempts. The audit context should include:

- dashboard site ID;
- public ID when available;
- action name;
- outcome;
- eligibility reason code;
- removed row counts after confirmation.

The audit context must not include pairing tokens, polling secrets, action private keys, raw payload JSON, authorization headers, filesystem paths, Drime credentials, or raw client error bodies.

Diagnostics/support copy may add aggregate counts for removed-record audit outcomes, but it should not list removed domains or labels.

## Implementation Plan

1. Add repository preview helpers for removal eligibility and dependent-row counts.
2. Add admin rendering for archived-record removal preview only.
3. Add tests proving active/polling/credentialed/unarchived records are ineligible.
4. Add confirmation handling in a separate commit after preview tests pass.
5. Delete dependent rows in bounded, explicit repository methods with prepared SQL and tight `dashboard_site_id` predicates.
6. Record redacted audit outcomes for preview and confirm attempts.
7. Update Diagnostics/support copy only if aggregate audit counts are useful.

## Required Tests

- archive-only UI renders no removal control for default active Sites rows;
- revoked archived record with no credentials can produce an eligible preview;
- expired pending archived record can produce an eligible preview;
- active, awaiting-first-poll, paused, unarchived, credentialed, or non-terminal-action records are rejected;
- confirmation re-checks eligibility after preview;
- confirmation deletes only the selected dashboard-owned site, snapshot, and action rows;
- audit events are redacted and bounded;
- uninstall behavior remains unchanged.

## Approval Gate

Implementation is not approved by this document.

Before code work, require explicit approval because the implementation would introduce a destructive dashboard-local database action. Before release or live deployment, require the normal release/deploy gate and a fresh `control-sitesmanage` restore point.
