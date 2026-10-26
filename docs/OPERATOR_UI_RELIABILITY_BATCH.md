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
   Exact-head CI: [40347722572](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40347722572), successful for `8244141ac2aa091e750ee440c482a989658c8c98`.
2. Snapshot unreadability: both consumers now label empty/unreadable payloads
   unavailable; raw/pre-decoded genuine zero reports remain visible. Real decoder
   and consumers tested; unrelated labels/action forms stubbed, no repository use.
   Exact-head CI: [40348087155](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40348087155), successful for `3d748b37a019e9337df26eda45c74f5981c1ce79`.
3. Absolute timestamp display: relative expressions, incomplete/impossible dates
   now display unavailable, not fabricated freshness. SQL UTC/ISO offsets and
   fractions retain the actual instant. Private display-only parser, no stored
   date/protocol validation changes; failing `tomorrow` regression reproduced.
   Exact-head CI: [40348319029](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40348319029), successful for `11caad153f3b236c087fa8c501bd47ed2957f042`.
4. Form processing lifecycle: regressions first failed for cancellation, BFCache
   restoration and untouched disabled controls. Restore only this script's busy
   state on cancelled submit or persisted pageshow; normal navigation stays busy.
   Dependency-free Node tests execute actual JS against DOM/API doubles and now
   run in CI; this does not establish WordPress submission/authentication proof.
   Actual built-script Chromium component check passed for a cancelled submission.
   Playwright MCP was profile-locked; used an independent headless process with
   the existing installed Chromium 1223, closed in finally. No profile/service
   changes, downloads, network or WordPress. Initial runner URL/executable-path
   mismatches were corrected explicitly, not claimed as browser test passes.
   Exact-head CI: [40348978910](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40348978910), successful for `eca3e37e570767b3b7cf02bd3ad8ee41b02a6437`.
5. Clipboard focus recovery: successful fallback restores the keyboard trigger;
   failure keeps selected manual-copy text, and async completion cannot steal
   focus from another control. Actual-source tests retain native value delivery.
   Built-script Chromium keyboard/DOM focus check passed, clipboard API stubbed;
   not native OS clipboard or WordPress proof.
6. Download resource lifecycle: pending.
7. Endpoint preview consistency: pending.
8. Missing-target enhancement: pending.

Each task requires local validation, its own commit/push and exact-head CI before
counting complete. Run links are recorded after verification. Final source-scoped
Light/UI/Error Handling/Security review and browser component evidence will
distinguish DOM/API doubles from real WordPress acceptance. No database proof or
whole-plugin review rows are refreshed by this batch.
