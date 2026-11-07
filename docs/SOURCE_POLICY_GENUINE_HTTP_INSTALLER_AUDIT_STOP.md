# Genuine-HTTP Installer Containment — Audit Stop

## Approved scope and outcome — 2026-11-07 UTC

The user approved Genuine-HTTP Owned Bootstrap/Request IO Integration and ONE
Ephemeral-CI Qualification/Cleanup Attempt, audit-first with fresh prerequisites
and exact owned cleanup. The [selected design](SOURCE_POLICY_GENUINE_HTTP_OWNED_STORAGE_DESIGN.md)
requires refusal on incompatible source/stock-hook/bootstrap findings. This
source audit found an unresolved installer effect before dispatch. **Native
integration and execution are held; the approved ONE attempt is unused.**

Clean/synced dashboard review baseline: `c9f055edf2314c26b7c355daef96ba9767cb2363`;
[ordinary CI42400103861](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/42400103861)
reverified actually completed/success. Toolkit baseline
`68c4242e5b700eb25b15de45c373d0f4c65b2a7b`; uploader remains clean/untouched at
`3dfcb15b5cab8191df7d984251986ead140ded52`. Public compatibility, dependencies,
runtime sources, workflows, source identities and acceptance criteria unchanged.

## Pinned-source evidence, not an observed network experiment

Read public immutable WordPress source through the GitHub Contents API into
bounded memory only; no core download to disk, loading, installation or execution.
All three files are at `db96f5eaa77a8d3e8eec8923a518fa2326f0d46a`:

| Source | Bytes | SHA256 | Relevant evidence |
| --- | ---: | --- | --- |
| `wp-admin/includes/schema.php` | 45583 | `a446ddd76ce401fc8173bfee4e79a98f8c3567ed3ba7560c2f167ca85e6976b0` | Default single-site permalink structure is empty, line443 |
| `wp-admin/includes/upgrade.php` | 116674 | `0d1d0e6e9c135452641b5a518ba41f6907e67ac668d27d5e38690f53e846527e` | Installer calls permalink probing, line137; implementation496–557 |
| `wp-includes/class-wp-http.php` | 41737 | `6497daba5828091fa173de7fa04c7d86e2b59e6998a364c5acad2b448c899e71` | External-block policy exempts own-site/localhost, lines895–943 |

Primary source anchors:
[schema443](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-admin/includes/schema.php#L443),
[installer496](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-admin/includes/upgrade.php#L496),
[HTTP895](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/class-wp-http.php#L895).

With fresh default storage, `wp_install_maybe_enable_pretty_permalinks()` does
not take its nonempty-option early return. It iterates two candidate structures,
calls `flush_rules(true)` at524 and issues `wp_remote_get()` for a new site's
first-post URL at541 with a five-second timeout. A failed candidate can lead to
the second probe; the source may reset plain permalinks afterward. Hard rewrite
flushing also needs an explicit generated-file policy; this audit does NOT claim
an actual `.htaccess`/web.config write occurred or must occur on this profile.

`WP_HTTP_BLOCK_EXTERNAL` does not alone contain these same-site requests:
`block_request()` permits localhost/own-site unless `block_local_requests`
changes its default false result. This branch precedes `WP_ACCESSIBLE_HOSTS`;
an empty accessible-hosts list is not a fix. The selected fixture recipe covers
installer mail but has not selected an owned local-probe short-circuit/local-
request containment contract. These installer calls fall outside its finite
login/bridge request model and no-unplanned-HTTP setup assumptions. Do not assume
that an absent planned server implies a harmless failure, no other listener or
zero network effects. **No traffic was generated or observed in this audit.**

This is a prospective lab containment/specification gap, not evidence of a
production exploit or proof that genuine HTTP qualification is impossible.
Initial stock-filter/theme inspection did not establish a separate direct
native-hook contradiction; it also did not prove the complete effective graph.

## No native attempt or retained implementation

No minimum-lab dispatch, fresh native prerequisites, HTTP request/listener,
WordPress bootstrap/authentication, SQL/storage operation, fixture/account/
permission write or native qualification occurred. No LocalWP/live/client/Drime
operation, system installation, production wiring, grant, activation, rollback,
package, tag, release or deployment. No lab resources were created to clean up.

During source audit, five new untracked helper drafts were started provisionally:
`scripts/ci/source-policy-http-owned-wire.mjs`,
`scripts/ci/source-policy-http-owned-source.mjs`,
`scripts/ci/source-policy-http-owned-database.mjs`,
`scripts/ci/source-policy-http-owned-observer.php`, and
`tests/node/source-policy-http-owned-wire.test.mjs`.
Only their two isolated transport-IO-double tests ran (2/2); no sockets/core/SQL.
The helpers were incomplete, unpublished and never wired into any workflow.
After the finding, all five archive entries were verified and exactly these
proven-new drafts were removed. No existing user file was deleted. Do not reuse
this archive as qualified implementation or a clean restore baseline.

Canonical Option B source restore, before drafts: archive
`C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/alynt-drime-backups-dashboard-20261107-194219.zip`
and matching `.txt` receipt;11205 files;clean baseline above;SHA256 verified
`8a3530b36c1f6e2cb251113e2ec86e36863a8f3c11bbbb0da0b64cbe723304d7`.
Recoverable dirty draft archive:
`C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/alynt-drime-backups-dashboard-20261107-200218.zip`
and matching `.txt`;11210 files;SHA256 verified
`15998e87d1b54964a7e2c4f7cb4907ecfdb699cf5063ab085f7ba8aad8797e8f`.
Neither is a release package or database/site backup; no rollback was performed.

## Canonical focused reviews and regression handoff

Feature scope: source-audit stop and directly owning resume records only.
Files reviewed: seven final Markdown files; three critical pinned core files
above plus directly inspected installer/bootstrap/filter/theme call sites.
Risk areas touched: Other (prospective bootstrap HTTP/file effects); no runtime
surface changed. Issues found:1. Issues auto-fixed:0 runtime defects; stale
current resume pointers corrected. Issues requiring manual decision:1.

`FEATURE_LIGHT_REVIEW_PROMPT.md`: Edge Case/WP Best Practices;
`wp-admin/includes/upgrade.php:541` and `class-wp-http.php:909` invalidate the
assumed installer containment. ACTION: flagged, native dispatch stopped.
No UI/UX workflow applies. Bloat/Structure review unnecessary: final change is
Markdown-only, with provisional helpers removed and no new architecture,
duplicate runtime logic, file split or retained troubleshooting path.

Final `FEATURE_SECURITY_REVIEW_PROMPT.md`: Medium, prospective lab containment
finding remains **OPEN/BLOCKED**, not a green feature-security gate. No new input,
output, SQL, authorization, nonce or public endpoint implementation remains.
Fix disposition: a reviewed fixture containment contract is needed before code;
do not silently strip stock callbacks, fake probe success/authentication, preset
permalinks to bypass installation or weaken acceptance/budgets.
Regression disposition: existing isolated bridge/DATA tests do not protect
installer HTTP/rewrite effects. Practical missing coverage is a candidate for
`07A-ADVERSARIAL_TEST_SUITE_REVIEW_PROMPT.md`: isolated request-policy/callback
tests plus eventual core-backed installer integration, fixed expected probe
failure, unknown URL/method/phase refusal, hard-rewrite effect bounds and an
assertion-killed removal/bypass control for the selected blocker. No claim of
genuine auth, storage, Database Review, physical containment or whole-plugin
pre-release readiness follows from in-memory/IO doubles or this source audit.

## Validation, publication and exact resume

Documentation-only closeout runs the existing focused HTTP isolated tests,
relative local-link checks and whitespace review, then ordinary exact-head CI.
Fresh local results: `node --test tests/node/source-policy-http-*.test.mjs`
passed37/37,zero failures/skips;seven changed Markdown files have392 relative
local links,all resolving;`git diff --check` passed. No test/source/workflow/
dependency file remains changed. These are isolated preparation checks only.
Actual publication/run/count evidence is owned by the toolkit's source-scoped
audit-stop entry; neither prior CI nor successful isolated tests count as the
missing native proof. No full release-readiness row is refreshed here.

Implementation-plan progress: the pending genuine-HTTP milestone has reached
an explicit fixture-effect policy decision, not completed integration. Campaign
remains **4/10 product milestones,8/10 native attempts,cumulative31**. The ONE
newly approved attempt was not consumed. Do not count this audit/docs closeout,
drafts or cleanup as another product milestone, or reopen the W/L/M cohort.

Recommended next gated slice: **Repository-Only Core-Backed Installer Local-HTTP
and Rewrite Containment Design**, design-only. Select the smallest source-owned,
core-supported setup policy: examine a fixed expected local-probe failure
short-circuit, fail closed on unknown calls, and decide hard-rewrite generated-
file handling explicitly. Preserve genuine core installation/authentication,
stock graph/guards, budgets, source identities and disabled production wiring.
Do not implement or dispatch the held attempt until that new decision is approved.
