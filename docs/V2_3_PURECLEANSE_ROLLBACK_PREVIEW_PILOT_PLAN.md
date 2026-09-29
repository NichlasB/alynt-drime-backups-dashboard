# V2.3 PureCleanse Rollback Preview Pilot Plan

Status: planning-only. Do not run this pilot until the live approval gate is satisfied.

This plan covers the smallest safe proof for the V2.3 schedule rollback-preview feature. The pilot proves that the dashboard can request a non-mutating rollback preview from one explicitly opted-in client site while preserving the existing read-only/default-hidden safety model.

## Target

- Dashboard host: `control-sitesmanage live-only`
- Dashboard plugin: Alynt Drime Backups Dashboard
- Dashboard record: PureCleanse, site ID 5
- Client site: `https://purecleanse.net`
- Client uploader version observed during preflight: `0.5.22`
- Current intended scan/upload cadence: every 15 minutes
- Current dashboard state before opt-in: schedule apply is available; rollback preview is hidden because the latest client report does not advertise rollback-preview support.

## Safety boundaries

- Do not enable or execute schedule rollback.
- Do not add restore, delete, cleanup, credential, Drime API, arbitrary command, filesystem browsing, or settings-write actions.
- Do not change the dashboard pairing/security model.
- Do not grant the dashboard Drime API credentials.
- Keep rollback-preview disabled by default and visible only when the client explicitly advertises support.
- Treat rollback-preview as a non-mutating action. It may inspect rollback metadata and report what would happen, but it must not change the active schedule.
- Any temporary cadence change must be returned to the intended cadence before closing the pilot.

## Required live approval gate

Before any live client opt-in, remote action dispatch, or schedule mutation, confirm all of the following:

1. A fresh PureCleanse restore point exists.
2. A fresh `control-sitesmanage` restore point exists, or the user explicitly accepts relying on the existing dashboard backup posture for this non-schema pilot.
3. The user approves enabling rollback-preview support on PureCleanse only.
4. The user approves a short temporary cadence proof on PureCleanse, expected to be `every_15_minutes -> every_30_minutes -> every_15_minutes`.
5. The user approves dispatching the non-mutating rollback-preview action while the fresh apply rollback metadata is still valid.

## Preflight checks

Run these before enabling the pilot:

- Confirm dashboard plugin is active and healthy on `control-sitesmanage`.
- Confirm PureCleanse is active, paired, working, and not in attention.
- Confirm PureCleanse queue count is 0 and failed count is 0.
- Confirm PureCleanse current cadence is every 15 minutes.
- Confirm the client advertises schedule management support and schedule apply support.
- Confirm `schedule_rollback` is not advertised.
- Confirm rollback-preview is still hidden before explicit opt-in.

## Pilot flow

1. Enable the client-local rollback-preview policy on PureCleanse only. Do not enable rollback execution.
2. Run a dashboard manual poll for PureCleanse.
3. Verify the latest redacted status advertises rollback-preview support and still does not advertise rollback execution.
4. Generate a schedule preview for temporary cadence `every_30_minutes`.
5. Poll until the preview result is visible and valid.
6. Apply the preview to temporarily change PureCleanse from `every_15_minutes` to `every_30_minutes`.
7. Poll until the apply action succeeds and fresh rollback metadata is available.
8. Dispatch `schedule_rollback_preview` for the latest successful apply while rollback metadata is still valid.
9. Poll until rollback-preview completes.
10. Verify the rollback-preview result says what would be restored, but the active schedule remains unchanged.
11. Generate and apply the normal guarded schedule flow back to `every_15_minutes`.
12. Poll until PureCleanse reports `every_15_minutes`, working status, queue count 0, and failed count 0.
13. Disable the client-local rollback-preview policy again unless the user explicitly wants the pilot capability to remain available.
14. Poll once more and confirm rollback-preview is hidden again while schedule apply remains available.
15. Update the ignored local dashboard rollout tracker with non-secret findings.

## Abort criteria

Stop the pilot and report the exact state if any of these occur:

- PureCleanse is not working, not paired, or enters attention for a new reason.
- Queue count or failed count is non-zero before the pilot.
- Schedule preview/apply support disappears.
- The client advertises `schedule_rollback` execution.
- Rollback-preview support appears without explicit client opt-in.
- The guarded apply creates no fresh rollback metadata.
- Rollback metadata expires before the preview is dispatched.
- The rollback-preview result implies that a schedule changed.
- The final return to `every_15_minutes` cannot be verified.

## Acceptance criteria

- Rollback-preview remains unavailable by default.
- After PureCleanse-only opt-in, the dashboard shows rollback-preview capability for PureCleanse only.
- Rollback-preview completes as a non-mutating action using fresh rollback metadata.
- No schedule rollback execution is exposed or accepted.
- PureCleanse is returned to every 15 minutes.
- PureCleanse remains working with queue count 0 and failed count 0.
- The dashboard and rollout tracker record the pilot result without storing secrets.

## Recommendation

Use PureCleanse as the pilot site because it is already the only observed apply-capable site, is active on dashboard site ID 5, and reports uploader `0.5.22`, which includes the shortened schedule-management retry window needed for a valid preview/apply/rollback-preview sequence.

Do not run the pilot until the required live approval gate is complete. The next safest step is to ask the user to create or confirm the PureCleanse restore point, then approve the short `every_15_minutes -> every_30_minutes -> every_15_minutes` pilot window.
