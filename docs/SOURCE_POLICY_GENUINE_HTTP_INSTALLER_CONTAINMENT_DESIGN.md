# Core-Backed Installer Local-HTTP and Rewrite Containment Design

Current implementation — 2026-11-07:[approved dormant foundation/preparation](SOURCE_POLICY_GENUINE_HTTP_INSTALLER_FOUNDATION.md)
implements the fixed callbacks/sidecar and isolated supervisor composition only.
No actual core/auth/SQL/HTTP/native execution. Earlier foundation request is
fulfilled;next gated slice:Genuine-HTTP Owned Bootstrap/Request IO Integration
with Installer Containment and ONE Ephemeral-CI Qualification/Cleanup Attempt,
audit-first/fresh prerequisites/exact cleanup. Physical containment is still
unproved;ONE held attempt unused,production wiring disabled.

## Authority and baseline — 2026-11-07 UTC

Approved repository-only DESIGN through validation, commit, push and ordinary
exact-head CI. R1 documentation about a future R3 owned HTTP qualification; no
operative containment code or execution authority. The [preceding audit stop](SOURCE_POLICY_GENUINE_HTTP_INSTALLER_AUDIT_STOP.md)
remains valid historical evidence, not a failed native attempt.

Review/restore baseline `5c77e7e660122033d16a376c8be6c58361eebec1`, clean/synced;
[ordinary CI42404782830](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/42404782830)
reverified ACTUALLY completed/success. Toolkit baseline
`b78b5003d98e67e6888954955fff7de3052a6c72`; uploader unchanged at
`3dfcb15b5cab8191df7d984251986ead140ded52`. The immutable clean Git baseline
is sufficient for this Markdown-only design; no new source/site/database archive
or rollback. Version0.1.77/schema8/PHP7.4/core6.0 minima remain unchanged.

No actual HTTP request/server, core loading/authentication, SQL/storage/native
experiment, fixture/account/permission/cron write, native workflow dispatch,
acquisition to disk, runtime installation or production wiring. No LocalWP,
installed/live/client/Drime operation; Site Operations target confirmation and
Novamira check are N/A for this repository-only task, not waived for future site
work. Existing isolated test children may run without loading genuine core.

## Pinned-source basis

Bounded GitHub Contents API reads into memory only, at WordPress commit
`db96f5eaa77a8d3e8eec8923a518fa2326f0d46a`; no source execution/download to disk.
The earlier audit records schema default empty permalinks and the same-site
exception in external-only HTTP blocking. Fresh relevant source:

| Source | Bytes | SHA256 | Decision evidence |
| --- | ---: | --- | --- |
| `wp-admin/includes/upgrade.php` | 116674 | `0d1d0e6e9c135452641b5a518ba41f6907e67ac668d27d5e38690f53e846527e` | `wp_install`137/139; default post209–257; permalink loop496–557 |
| `wp-includes/class-wp-http.php` | 41737 | `6497daba5828091fa173de7fa04c7d86e2b59e6998a364c5acad2b448c899e71` | `pre_http_request`277–280 returns before URL/transport/streaming work; local block909–919 |
| `wp-includes/class-wp-rewrite.php` | 63693 | `e197e1f2a78b4b6ffffd1687bdedf0c254d2a8cf013f6d8c8e55c0ac53facd3e` | refresh1509–1523; hard-flush filter1899 before file writers1902–1906 |
| `wp-settings.php` | 33152 | `2e3185ee44bcdd9d9c4bef8ce4891827711dec2b5d5d4c08c951738ccef51f8d` | plugin API require_once53; WP_Error121; complete stock graph retained |
| `wp-includes/plugin.php` | 37123 | `d70b9e18d34ab3fe46e15548a5e8e089ea5920832f06a4d89a9d64b2f7938b0d` | dependencies25–26; initializes genuine hook globals, no substitute hook API |
| `wp-includes/http.php` | 28961 | `ba3a08b53e117fd53eeccc8b972744ee3e87eae9d8d8125ff62939f28cd3e773` | error response produces empty header at264–269, not pretty-permalink success |
| `wp-includes/link-template.php` | 160143 | `3e79919111e913b311343c498f30f0cb08b820ff80fd6005218d9e17a258867c` | get_permalink uses actual post/date/slug and selected structure |

Immutable primary anchors:
[installer](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-admin/includes/upgrade.php#L137),
[HTTP short-circuit](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/class-wp-http.php#L277),
[rewrite gate](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/class-wp-rewrite.php#L1899),
[early plugin API](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-settings.php#L53),
[error header](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/http.php#L264).

Important correction to a three-flush interpretation: two candidate flushes
plus fallback flush inside the permalink routine are followed by the fourth
`flush_rewrite_rules()` in `wp_install`. No native count has been measured.
The proposed exact four-count oracle applies to this fixed fresh installation,
not every possible stock bootstrap or an arbitrary WordPress version.

## Selected finite setup policy

Amend the [owned HTTP recipe](SOURCE_POLICY_GENUINE_HTTP_OWNED_STORAGE_DESIGN.md),
not production policy: run genuine installation, deliberately deny its two
permalink probes through the supported HTTP short-circuit, and deny all hard
rewrite-file flushes through the supported hard-flush filter. Let genuine core
choose its plain-permalink fallback. Do not preseed/override the permalink
option to skip the loop, replace core functions, fake a successful HTTP response,
strip stock callbacks or change authentication. This does not qualify pretty
permalinks, rewrite-server behavior or a production/FPM/installed-plugin graph.

### Before complete bootstrap

The future private run-owned config fixes owned DB/prefix, root origin
`http://127.0.0.1:18777`, en_US single-site locale and owned content/theme state
before any load. Require the exact pinned `wp-includes/plugin.php` once, register
only fixed source-owned containment callbacks via its genuine API, then enter
unchanged `wp-settings.php`. Its require_once preserves those registrations.
Do not hand-edit `$wp_filter`, preload another hook implementation, add an MU
plugin/dropin, activate the dashboard or use SHORTINIT. Plugin API dependencies
remain in the complete pinned core manifest; no existing native pin cap expands.
Callback definitions are new proof-only manifest entries, not runtime grants.

Hook presence/source identity/priority/accepted-argument checks precede further
bootstrap effects and recur after stock loading and before each guarded entry.
Fixed priority10/accepted args: `pre_http_request`:3,
`flush_rewrite_rules_hard`:1, `block_local_requests`:1, existing `pre_wp_mail`:2.
Foreign callbacks/preemptive responses, missing/replaced callbacks, unknown
registry metadata or an unexpected source graph refuse; do not remove them or
learn an allowlist. The four hook names do not overlap the current native
`hook_names()` strict policy; that observation is not complete graph admission.
All existing native declaration/query/capture guards remain unchanged.

Each containment callback also checks its complete current hook registry before
returning. A later-priority callback could otherwise replace WP_Error/false and
undo containment before the parent sees a sticky failure. If any foreign entry,
priority/identity drift or unreadable registry is present, abort immediately
with a fixed safe exception before later callbacks/transport/file work; merely
recording a violation and returning is insufficient. Exact owned callbacks are
never removed to make a registry match. This is not a sandbox against arbitrary
hostile code modifying the process outside the pinned, qualified graph.

### HTTP-API denial, not simulated success

`pre_http_request` always returns a real fixed-code `WP_Error`, never false,
HTTP200, headers, cookies or response content. A non-false incoming preemption is
itself a sticky violation, not something to trust or pass through. If the genuine
error class is unavailable or callback setup fails, terminate safely before
continuing bootstrap; no false-return fallback. Callback code does not perform
HTTP, SQL, auth selection, filesystem writes or raw-URL diagnostics.

Only while the private CLI setup owner is inside the one genuine `wp_install`
call may two candidate GETs be classified as expected denied installer probes.
The stage is source-owned state, never a request/environment selector supplied
by the HTTP client. No proof server has started. Validate typed parsed arguments
against the pinned defaults: GET/timeout5/blocking true, empty headers/cookies,
null body/filename, stream false, compress false and no unknown key/value drift.
Default redirection5/_redirection5 is DATA only: no transport or redirect occurs.
The actual finite default-key map must be source-reviewed in implementation,
not expanded from whatever a failing request happens to contain.

Candidate1 is the exact owned origin plus `/YYYY/MM/DD/hello-world/`;
candidate2 is the same date/slug with `/index.php` before that path. At most two
private URLs, each <=256 bytes, are retained transiently for verification; no
query, fragment, credentials, alternate host/port/scheme, encoding normalization,
redirect or arbitrary path. After core returns, bind both exact observed strings
to the actual owned default post's valid post_date/post_name, fixed locale and
origin. Derive the expectation from independently read owned post fields and
pinned structure tokens, not from the observed URL or current wall clock. This
handles a date-boundary crossing without accepting a forged date. Missing post,
wrong order, missing/extra probe or mismatch refuses; do not repair stored data.

All other core HTTP-API calls in setup/bootstrap/auth/request/shutdown are denied
with the same safe error and set an irreversible unknown-effect flag. Never
classify an unexpected request as harmless just because it was blocked. No next
request/success publication after a violation. Parent-issued finite genuine
login/bridge HTTP exchanges are a different path; this filter neither answers
them nor mints a session. Keep `WP_HTTP_BLOCK_EXTERNAL`,
`DISABLE_WP_CRON` and no accessible-host exemption; fixed `block_local_requests`
true is defense in depth if the primary blocker is absent, not its substitute.

These are cooperative WordPress HTTP-API controls. They do NOT independently
attest absence of raw sockets, another extension's egress or kernel-wide effects.
No new firewall/seccomp/network architecture, canary or system installation.

### Hard rewrite-file denial and normal option effects

`flush_rewrite_rules_hard` always returns strict false. During the one installation
stage, account for exactly four calls with incoming true; any other stage/value/
count is a sticky refusal, still returning false. In particular, both fallback
flushes must remain covered. Do not enable Apache/IIS detection, create a rewrite
file, patch core or override `save_mod_rewrite_rules`/IIS functions to succeed.

Core refreshes `rewrite_rules` BEFORE consulting the hard-flush filter. Normal
bounded core option/cron writes remain owned setup effects, not zero-DML proof.
Observe them under the existing1024 setup SQL ceiling; do not delete cron events
or add an unbounded owned-writes exception. After installation verify plain
`permalink_structure`, expected owned option/schema state and normal installing-
mode exit. Do not use a cached/fabricated option result as physical readback.

Before/after regular-file manifests must agree for core/config/content/theme,
including absence of `.htaccess`/`web.config` at all relevant owned roots. No
generated rewrite-file allowlist is selected. New/changed/link/special files or
unknown write destinations refuse even if the hard-flush count looks correct.
Read-only source staging may supplement the callback but is not a claim of an
enforced host filesystem sandbox. Cleanup removes only exact owned resources.

The already selected count-only `pre_wp_mail` remains: non-null false prevents
transport, expected installer notification counted within mail<=1, unknown mail
is a violation. No recipient/message/password retention, fake delivery success
or override of wp_mail. Core HTTP/mail/rewrite blockers remain installed through
each later request and normal shutdown; with installing mode absent, expected
counts are zero. Do not temporarily remove containment to pass owner admission.

## Ordered evidence and unchanged limits

Future order: exact source/owned-root/config/callback checks -> complete stock
installing bootstrap -> one genuine installation -> owned readback and file/
hook verification -> normal setup exit/release -> start fixed HTTP server ->
fresh genuine login/view/save/no-op/conflict/denial matrix -> request EOF/shutdown/
independent release reconciliation -> exact owned cleanup. There is no public
installer route, late table/prefix swap, CLI cookie surrogate or refreshed timer.

Add a finite setup-containment sidecar to the future proof, separately validated
with exact run/head/image/stage identity: two expected denied HTTP calls, zero
unexpected/foreign preemptions, four suppressed hard flushes, expected mail<=1,
verified owned post/plain options, file-manifest equality and sticky violation
false. Later stages require zero expected installer events and no unknown
effects. Missing/forged/all-zero sidecars fail; a callback invocation count alone
does not prove transport never ran. Required control tests must cover that path.
Do not overload the existing13-case result schema or claim a sidecar is a grant.

Keep existing `ledgerMatches` schema and outbound0: two denied BEFORE-transport
attempts are separately counted, not actual outbound traffic secretly subtracted
from a ledger. Parent transactions stay max64 including login/controls; core
denied calls are not completed parent requests or extra case credit. Original
setup60s, whole HTTP60s, transaction5s, cleanup30s, job600s,64MiB, setup SQL1024,
bootstrap/auth SQL256 each and response/diagnostic/source envelopes unchanged.
No polling, response-loss experiment, native retry, learned pins or budget rise.
If implementation cannot meet this finite recipe, stop at the actual finding.

## Required non-vacuous validation and limits

Next isolated foundation must test actual fixed callback/validator algorithms
with explicit API/IO doubles: genuine error identity versus false/response-array
masquerade; source-owned stage/order; independent post-date URL binding; strict
argument/default-map shape; two probes/four flushes; foreign preemption; missing,
duplicate, overflow and post-install events; secret-free bounded diagnostics;
sticky failures; immutable clocks/SQL/source/cleanup limits. Require valid complete
transcript availability, not an oracle that passes only unavailable outcomes.

Assertion-killed removal/bypass controls must independently fail when the primary
HTTP blocker returns false, the hard-flush gate returns true, unknown-event
failure is ignored, the fourth flush is omitted or identity/post binding is
removed. Assert the actual primary callback's WP_Error return independently of
secondary local blocking, so defense in depth cannot mask its removal; separately
assert no fake transport entry on the selected short-circuit path. A rewrite
mutation must fail the strict-false assertion and modeled fake-file-writer
boundary; do not touch actual files/core. Add a later-priority override control
that must abort before its callback can run. Parse/import errors are
not semantic kills. These controls are planned, NOT run by this design.

Eventual separately approved native proof must demonstrate real stock fallback,
actual two/four counts, physical owned-state/file equality, real auth/guarded
operations and strict cleanup. In-memory transcript equality or ordinary CI does
not establish HTTP/native containment. Prior audit's07A regression handoff remains
open pending those tests/proof; no whole-plugin Database/security/readiness row
is refreshed. Historical W/L/M receipts retain their original narrower scope.

## Focused canonical review and delivery

Feature scope: finite fixture containment DESIGN and six direct owning/resume
records; seven Markdown files, plus the referenced immutable core/native-policy/
HTTP DATA call sites read-only. Risk areas:Other, future HTTP/file/DB effects.
Canonical Feature Light:five specification issues found and corrected here:
fourth flush rather than three; install-before-hook timing gap closed by genuine
plugin API early registration; false zero-SQL interpretation of soft rewrite
refresh explicitly rejected; late filter override requires immediate abort;
primary-blocker mutation cannot rely on secondary defense failing. Issues
auto-fixed:5 design clarifications; manual
decisions outstanding:0 within this approved design. Runtime fixes:0.

Bloat/Structure unnecessary:Markdown-only, no executable helper/architecture,
duplicate runtime or file split. No UI/UX workflow. Final focused Security checks
the complete changed design after validation: unknown/preempted/malformed traffic
never becomes success; callbacks cannot grant auth/handles; phase/date/count/
source binding and secret-free outputs are explicit; HTTP and rewrite denials
stay active through shutdown. No additional unresolved design contradiction
identified; the actual containment defect remains IMPLEMENTATION/PROOF PENDING,
not a green runtime-security gate. Regression disposition:practical planned API/
IO and core-backed layers above;no existing test is misreported as protection.

Actual local validation/publication evidence is appended after running checks
and recorded in the toolkit's source-scoped section, not inferred from this
proposal. Fresh local `node --test tests/node/source-policy-http-*.test.mjs`:
37/37,failures/skips0. All397 relative local links across these seven changed
Markdown files resolve;`git diff --check` passes. Existing isolated controls
remain narrower than the planned containment tests. Final diff has no source,
test,workflow,dependency,pin or generated artifact changes.
No new native attempt or completed product milestone. Implementation-
plan progress:resolves the specific installer containment decision sufficiently
to prepare dormant code,not genuine-HTTP qualification. Campaign4/10 milestones,
8/10 native attempts,cumulative31 unchanged;ONE held attempt remains unused.

Recommended next gated slice:**Repository-Only Core-Backed Installer Containment
Safety Foundation and Owned-Integration Preparation**,actual fixed callback/
sidecar algorithms and isolated API/IO tests only, including meaningful mutation
controls. Keep acquisition/core loading/auth/SQL/storage/listeners/native dispatch
and production wiring disabled. Fresh prerequisites and revised exact-head
native execution approval remain required before the held ONE attempt;this
design alone authorizes neither its implementation nor execution.
