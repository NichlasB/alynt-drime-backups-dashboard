# Operator UI Reliability Batch — 2026-10-26

Baseline: `a9b01f9d443d6a00f7d6e079a34bc914f186fbf5`, dashboard 0.1.77.
R2 display/interaction-only work under the autonomous eight-task request.
No uploader, WordPress, database, polling, permissions, credentials, client actions,
release, package or deployment changes. V2.3 research remains deferred.

## Source-scoped inventory and plan

Canonical 03 Error Handling Phase 1 inspected the support-output, basic-detail,
sites-table and time-formatters traits plus admin JS (five files). Eight medium
UI reliability issues, no critical/high issues in this scope: misleading export
on encoding failure; unreadable snapshot zero counts; loose timestamp parsing;
cancelled/restored form busy state; clipboard focus loss; download resource
cleanup; endpoint preview accepting rejected origins; missing-target controls.
No new destructive confirmations, async network requests or permission changes.
This is not a whole-plugin inventory or release-readiness rerun.

Phase 2 is authorized by the batch request. Recovery is local feedback, honest
unavailable states and restoration of owned transient DOM state, not retries of
server actions. No logging of support content, tokens or raw errors.

## Tasks

1. Support export: genuine JSON encoding failure (INF) first reproduced the
   misleading `{}` export. Failure now renders only the actionable refresh notice;
   valid JSON retains escaped textarea/copy/download. Targeted render regressions.
2. Snapshot unreadability: pending.
3. Absolute timestamp display: pending.
4. Form processing lifecycle: pending.
5. Clipboard focus recovery: pending.
6. Download resource lifecycle: pending.
7. Endpoint preview consistency: pending.
8. Missing-target enhancement: pending.

Each task requires local validation, its own commit/push and exact-head CI before
counting complete. Run links are recorded after verification. Final source-scoped
Light/UI/Error Handling/Security review and browser component evidence will
distinguish DOM/API doubles from real WordPress acceptance. No database proof or
whole-plugin review rows are refreshed by this batch.
