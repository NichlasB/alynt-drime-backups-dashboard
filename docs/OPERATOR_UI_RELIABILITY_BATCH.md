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
   Exact-head CI: [40349182096](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40349182096), successful for `d640940430287b0f4ccc05f7d8fb1703862e6b86`.
6. Download resource lifecycle: failing tests reproduced immediate URL revocation
   and leaked URL/link on construction/click errors. Finally removes only the
   owned link and delays owned URL release for browser consumption on all paths.
   Content/type/filename are unchanged. No automatic retry or persistent storage.
   Independent Chromium received the synthetic blob download, suggested filename
   matched, download failure was null and owned anchor was removed; browser closed.
   Exact-head CI: [40349404015](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40349404015), successful for `4ea568247a51125e9d08d2a2ffb1f05c02643bc1`.
7. Endpoint preview consistency: failing tests reproduced credential/path stripping
   and trailing-dot mismatch. Preview now requires a public-host-shaped HTTPS
   origin with only optional root slash/default port. It never rewrites submitted
   input, resolves DNS or replaces server validation. Server validator unchanged.
   Exact-head CI: [40349591018](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40349591018), successful for `1ead68ef77b2caf67a84ceabe648b3dc29d4fc13`.
8. Missing-target enhancement: failing tests reproduced dead controls and silently
   missing targets. Reveal/bind only available string-valued fields and initially
   enabled controls; reject disabled/repeated copy events. A disappearing field
   uses the translated wrapper recovery message, never a misleading copy success.

Each task requires local validation, its own commit/push and exact-head CI before
counting complete. Run links are recorded after verification. Final source-scoped
Light/UI/Error Handling/Security review and browser component evidence will
distinguish DOM/API doubles from real WordPress acceptance. No database proof or
whole-plugin review rows are refreshed by this batch.

## Final source-scoped reviews and local validation

Feature scope: six runtime UI files and directly affected tests/build/CI/records
(20 files total). UI areas: admin evidence/support output, forms, notices, JS
interactions and copy; no AJAX, REST, database, cron or remote implementation.
Canonical Light → UI/UX + 03 Error Handling Phase 2 → final focused Security.
Eight medium issues identified/auto-fixed, no manual design decisions or confirmed
security defects in the changed paths. No new public contract or dependency.

Baseline inventory (method/operation; original behavior), all Medium:

| ID | Source | Original operation / impact | Recovery |
|---|---|---|---|
| 1 | diagnostics-support-output:render_support_copy_output | `false ? '{}' : $encoded` offered false export | Refresh notice, no controls |
| 2 | basic-detail-helpers:render_latest_snapshot_summary; sites-table:render_sites_table | `$snapshot` alone allowed empty decoded counts | Explicit unavailable evidence |
| 3 | time-formatters:time_html/datetime_label | Loose DateTime accepted relative/overflow values | Unavailable, absolute positives retained |
| 4 | admin/index.js:form submit | Busy labels/disabled state never restored | Cancel/persisted pageshow restoration |
| 5 | admin/index.js:fallbackCopy | Focus remained in selected textarea after success | Restore keyboard trigger, preserve failure selection |
| 6 | admin/index.js:download click | Immediate revoke; errors leaked link/URL | Owned finally cleanup, delayed release |
| 7 | admin/index.js:updateEndpointPreview | URL normalization silently stripped rejected parts | Display-only origin-shape hint |
| 8 | admin/index.js:copy/download binding | Reveal before lookup, missing target silently returned | Hide unusable controls, translated recovery |

03 summary: five original files scanned; one silent missing-target path, one
loading-state recovery gap, two misleading evidence/empty states, one preview
validation-feedback gap, and three export/focus/resource reliability gaps. Total
eight; no new network, permission or destructive confirmation issues in scope.

Light/structure: cohesive helpers, no new runtime files or abstraction framework;
no meaningful bloat/duplication requiring the Bloat workflow. Existing structural
guard passes. UI review keeps native classes, one primary action per existing
region, translated/escaped messages and polite live regions. Persistent encoding
failure stays non-dismissible until refresh (existing inline failure pattern).
Focus restoration does not steal focus after async work. Form state restores only
owned mutations, not arbitrary disabled controls. No client validation authority
is added by the preview. No whole-dashboard accessibility/visual claim.

Security gate: unchanged capability/nonce checks, fixed target-ID lookup, existing
clipboard/download contents and fixed filename; textContent, escaped PHP output,
no dynamic HTML or remote requests. No confirmed security defect requiring a new
07A handoff. No storage/schema/file upload or permission changes.

Local final checks: PHP 304 tests / 1489 assertions, two pre-existing Sodium skips;
Node 16/16, no skips/failures; PHPCS, build, structure and diff checks passed;
npm/Composer audits reported zero vulnerabilities/advisories. Generated dist is
ignored and rebuilt rather than manually edited. Chromium component smoke has
11 assertions, network aborted, isolated process closed; real DOM/focus/input
and synthetic blob download, clipboard API stubbed. Not real WordPress,
authentication, OS clipboard, actual BFCache navigation, database, physical
transactions, installed-site acceptance, package or deployment proof.

All eight commits require exact-head CI success; the toolkit source-scoped record
owns final run links/counts after the last push. Implementation-plan progress:
operator monitoring/support reliability advanced; V2.3 deferral unchanged.
Recommended next action after eight verified tasks: run the canonical full
pre-release workflow on this coherent UI reliability candidate before packaging
or publishing. No live deployment approval is inferred.
