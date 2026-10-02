# V2.4 Cleanup Apply Decision

Status: planning-only decision record. `cleanup_apply` is deferred and must not be implemented, released, deployed, enabled, or exposed in the dashboard without a later explicit high-risk approval gate.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_4_CLEANUP_RETENTION_DESIGN.md`
- `docs/V2_4_CLEANUP_PREVIEW_IMPLEMENTATION_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`

## Decision

Do not implement `cleanup_apply` as the next slice.

Keep V2.4 at non-mutating `cleanup_preview` for now. The dashboard and uploader may continue to report, display, dispatch, and audit preview-only cleanup evidence for explicitly opted-in clients, but no dashboard control should delete files, apply retention, mutate Drime, remove backup sets, browse arbitrary paths, or execute cleanup.

## Rationale

The cleanup-preview rollout successfully proved the safe visibility layer:

- dashboard `0.1.51` and uploader `0.5.24` shipped preview-only support;
- active dashboard clients report cleanup-preview capability for `safe_local_uploader_owned` / `uploader_temp_artifacts`;
- cleanup apply, Drime cleanup, backup deletion, restore actions, credential actions, path exposure, and arbitrary filesystem browsing remain unavailable;
- the first approved preview proof returned no eligible stale temp artifacts.

That is useful as observability, but it does not yet prove enough operational need to justify a destructive runtime action. Implementing cleanup apply now would add deletion risk, restore-point expectations, client-local opt-in complexity, stale-preview failure modes, and operator-confirmation UX without a demonstrated recurring cleanup problem.

## What Remains Allowed

Allowed without changing this decision:

- keep `cleanup_preview` support and UI as preview-only;
- improve preview-only wording, diagnostics, support copy, and action-history presentation;
- run explicitly approved non-mutating preview checks on selected clients;
- collect non-secret evidence that stale uploader-owned temp artifacts are recurring and material;
- update docs or tests that preserve the preview-only boundary.

## What Remains Blocked

Blocked until a later explicit approval gate:

- `cleanup_apply` action dispatch;
- any cleanup apply button or confirmation flow;
- deletion of local files, local records, Drime objects, backup sets, WPvivid backups, or server-runner packages;
- dashboard-provided paths, filenames, object IDs, retention rules, arbitrary age thresholds, or free-form delete criteria;
- dashboard-side Drime credentials;
- arbitrary filesystem browsing;
- combining cleanup apply with restore, backup-set deletion, or remote retention.

## Conditions To Reconsider

Reconsider `cleanup_apply` only if all of these become true:

1. Multiple preview-only observations show recurring, material, uploader-owned stale temp artifacts.
2. The artifacts are safe to remove without affecting active uploads, backup evidence, restore readiness, or support diagnostics.
3. The cleanup scope remains narrow: `safe_local_uploader_owned` and allowlisted categories only.
4. The client owns all path resolution and eligibility decisions internally.
5. Protocol and threat-model updates explicitly cover destructive local cleanup.
6. A restore-point/backout plan exists for any pilot site.
7. The dashboard applies only a fresh, matching preview fingerprint and cannot send paths or item lists.
8. Tests prove stale previews, unsafe local state, missing opt-in, unknown categories, duplicate apply, and concurrent apply fail closed.

## Recommended Next Slice

The next safest slice is not cleanup apply. Use one of these instead:

1. **Cleanup Preview Observability Polish** — improve preview-only history, diagnostics, and support copy if operators need clearer proof that cleanup apply is unavailable and unnecessary.
2. **Next V2 Planning Pass** — evaluate a different non-destructive readiness slice, such as restore-preparation evidence, without implementing restore execution.

Do not proceed to `cleanup_apply` implementation from the current evidence baseline.
