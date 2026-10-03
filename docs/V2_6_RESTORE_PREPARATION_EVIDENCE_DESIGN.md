# V2.6 Restore Preparation Evidence Design

Status: first evidence/display line completed. The dashboard-side read-only evidence consumer, Site Detail display, compact Sites-row hints, Diagnostics summary row, and source-level Diagnostics aggregates are implemented, released, deployed, and post-release monitored through dashboard `0.1.55`. This document still does not approve restore preparation runtime actions, restore execution, backup deletion, Drime mutation, arbitrary filesystem browsing, dashboard-side Drime credentials, or production data changes.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- `docs/V2_4_CLEANUP_APPLY_DECISION.md`

## Decision

The next restore-adjacent direction should be **restore preparation evidence**, not restore execution.

Do not implement a restore action as the next slice. If restore work continues, start with a non-destructive evidence model that helps an operator answer:

- which backup sources appear restorable;
- whether required backup components are present according to client-owned evidence;
- whether checksums, sidecars, manifests, and source categories are reported as internally consistent;
- whether a candidate appears stale, incomplete, incompatible, or unverified;
- what manual runbook step would be needed before any restore could be attempted.

The dashboard should display support-safe readiness summaries only. It must not download, stage, unpack, overwrite, restore, delete, browse paths, request Drime credentials, or mutate production data.

## Rationale

Restore execution is the highest-risk V2 class because it can overwrite production data. The dashboard does not need restore execution to become more useful. A safer intermediate step is to expose bounded, redacted readiness evidence so operators can see whether a site has plausible restore candidates and where manual review is needed.

This design also keeps the dashboard honest: a backup can be "fresh" while still lacking enough verified restore evidence for a safe restore. Separating freshness from restore readiness avoids implying that monitoring success equals restoration confidence.

## Scope

Allowed planning scope:

- define support-safe restore-readiness summaries;
- define candidate evidence categories;
- define dashboard display concepts for Site Detail and Diagnostics;
- define forbidden fields and hard boundaries;
- define tests and future approval gates.

Out of scope:

- restore execution;
- restore staging on production;
- database imports;
- file extraction or overwrite;
- Drime object deletion, retention, or mutation;
- backup-set deletion;
- arbitrary filesystem browsing;
- dashboard-side Drime credentials;
- raw paths, filenames, Drime IDs, signed URLs, SQL, commands, or package internals;
- any live-site change.

## Proposed Evidence Model

If a later runtime slice is approved, the client may report an optional `restore_readiness` summary in the authenticated status payload.

Candidate support-safe fields:

```json
{
  "restore_readiness": {
    "schema_version": 1,
    "generated_at": "2026-10-02T12:00:00Z",
    "overall_state": "evidence_available",
    "candidates": [
      {
        "source": "server",
        "candidate_ref": "opaque-client-generated-reference",
        "latest_backup_finished_at": "2026-10-02T01:30:00Z",
        "component_state": "complete",
        "checksum_state": "verified",
        "manifest_state": "compatible",
        "sidecar_state": "present",
        "age_seconds": 37800,
        "warnings": []
      },
      {
        "source": "wpvivid",
        "candidate_ref": "opaque-client-generated-reference",
        "latest_backup_finished_at": "2026-09-28T04:00:00Z",
        "component_state": "unknown",
        "checksum_state": "not_reported",
        "manifest_state": "not_reported",
        "sidecar_state": "not_reported",
        "age_seconds": 388800,
        "warnings": [
          "restore_evidence_incomplete"
        ]
      }
    ]
  }
}
```

Rules:

- `candidate_ref` must be opaque and client-generated.
- The dashboard must not infer raw filenames, paths, package names, Drime object IDs, or signed URLs from a candidate reference.
- The dashboard must treat missing or unknown evidence as `not verified`, not as ready.
- The dashboard must not dispatch a restore action from this evidence.
- The client remains the only system that can inspect local/Drime state and summarize it.

## Candidate States

Recommended allowlisted values:

- `overall_state`: `not_reported`, `evidence_available`, `incomplete`, `stale`, `incompatible`, `unknown`;
- `component_state`: `complete`, `partial`, `missing`, `unknown`;
- `checksum_state`: `verified`, `failed`, `not_reported`, `unknown`;
- `manifest_state`: `compatible`, `incompatible`, `not_reported`, `unknown`;
- `sidecar_state`: `present`, `missing`, `not_reported`, `unknown`.

Any unrecognized value should sanitize to `unknown`.

## Dashboard UI Direction

Site Detail:

- add a restore-readiness panel only when the latest sanitized payload includes evidence;
- label the panel `Restore readiness evidence`;
- show source-level summaries such as `Server · evidence available · checksum verified · manifest compatible`;
- show missing evidence as `Not verified`;
- include explicit copy: `This is evidence only. The dashboard cannot restore this site.`;
- link operators to the manual restore runbook once one exists.

Sites list:

- do not add row-level restore controls;
- show a compact read-only `Restore evidence` hint when sanitized evidence is present;
- keep the hint support-safe and bounded to overall/source evidence states;
- do not expose candidate references, paths, filenames, package names, Drime identifiers, credentials, or restore controls.

Diagnostics/support copy:

- aggregate counts only, such as sites with evidence, missing evidence, incomplete evidence, and incompatible evidence;
- include an operator-facing overall summary row and source-level Server/WPvivid aggregate counts;
- do not include domains, paths, object IDs, filenames, or candidate refs in support copy unless a later privacy review explicitly approves a redacted export field.

## Safety Boundaries

The dashboard must never:

- run restore execution;
- download, unpack, stage, import, or overwrite backups;
- send paths, filenames, package names, Drime IDs, signed URLs, SQL, shell commands, or arbitrary criteria;
- store Drime credentials;
- browse a client filesystem;
- mutate production data;
- present evidence as a guarantee of restorable state.

The client must:

- own all path/object resolution internally;
- return only bounded, redacted, allowlisted states;
- fail closed when restore evidence is uncertain;
- never expose raw paths, filenames, package names, object IDs, credentials, or package internals.

## Future Test Plan

Dashboard tests:

- sanitizer accepts only allowlisted restore-readiness fields and states;
- unknown states become `unknown`;
- forbidden fields are dropped or rejected;
- Site Detail renders evidence-only copy and no restore button;
- Diagnostics aggregates remain support-safe;
- restore-readiness evidence does not affect backup freshness classification.

Uploader tests:

- restore-readiness summary is disabled or absent by default until explicitly implemented;
- raw paths, object IDs, package names, and credentials never appear in payloads;
- incomplete evidence is reported as incomplete or unknown, not ready;
- checksum/manifest/sidecar states are bounded allowlisted values.

Cross-plugin tests:

- v1-only clients remain monitored without restore-readiness UI;
- clients with partial evidence show `Not verified`;
- no restore action type is advertised, dispatched, or accepted.

## Approval Gates Before Runtime Work

Before implementation:

1. Update `docs/PROTOCOL_V2.md` with an additive status-payload-only `restore_readiness` shape.
2. Update `docs/THREAT_MODEL_V2.md` with restore-readiness evidence risks.
3. Confirm companion uploader target and restore-point recommendation before code edits.
4. Run applicable ds2 feature workflows for both repositories.
5. Run targeted ds3 pre-release reviews before any release.
6. Complete one local/disposable or explicitly approved low-risk proof.

Before restore execution:

- write and approve a separate restore runbook;
- complete staging/disposable restore drills;
- create fresh production restore points;
- require explicit high-friction production approval;
- keep restore execution disabled by default and unavailable from this evidence slice.

## Recommended Next Step

This planning direction was accepted, and the first implementation line is complete:

**Restore Readiness Evidence Consumer** — dashboard-side sanitization and display of optional `restore_readiness` summaries, with no client actions and no restore controls.

Completed follow-up visibility slices include compact Sites-row hints, Diagnostics summary wording, and source-level Diagnostics aggregate counts, all released and post-release monitored through dashboard `0.1.55`.

Do not proceed to restore execution from this design. Any next restore-adjacent work must start as a separate approved planning decision and must not add restore-preparation runtime actions, staging, download/import behavior, path browsing, Drime credentials, production mutation, or restore controls.
