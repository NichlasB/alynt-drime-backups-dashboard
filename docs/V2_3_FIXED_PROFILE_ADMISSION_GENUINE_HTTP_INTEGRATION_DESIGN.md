# V2.3 Fixed Profile Admission and Genuine Closed-Entry HTTP Integration Design (R2)

2026-10-24 successor decision:the owned pre-owner proof found an installation-check
bulk-read dependency before routing. The [read contract/isolated profile design](V2_3_PRE_OWNER_READ_CONTRACT_ISOLATED_AUTH_PROFILE_DESIGN.md)
keeps ordinary routes unsupported and selects a separately gated read-only database
projection investigation. This does not change the historical design/proof result,
enable admission or authorize account/permission/native operations.

2026-10-24. Approved design-only repository work. No executable, dependency,
server/PHP configuration, fixture, WordPress, database, HTTP or native experiment
change/run. Production wiring, admission, grants, activation and rollback remain
disabled; `entry_available()` remains literally false. Versions remain dashboard
0.1.77 and uploader 0.5.25. No package, release, tag or deployment.

Explicit excluded coherent bases: dashboard
`b3acb6af7291805977e8fe36d6e44be2fb1ea2f2`, uploader
`f5b0b9b1a41871a802175ac803241a60bd91c20f`, toolkit
`c75ad4859a21865c4e697e20ca97ffdca6820b13`.

Parents: [buffered input/genuine HTTP design](V2_3_BUFFERED_INPUT_GENUINE_HTTP_INTEGRATION_DESIGN.md),
[disposable supervision design](V2_3_DEDICATED_BUFFERED_HTTP_CGI_SUPERVISION_DESIGN.md),
[R1-R7 readiness](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md).
Uploader owns `docs/V2_3_HTTP_CGI_OWNED_INTEGRATION_PROOF.md` and the dormant
`class-registration-closed-entry-request-owner.php` with its reader, transport,
scope and GET-auth traits. Historical evidence stays historical, not refreshed.

## Decision: earn bootstrap containment before changing admission

Continue evaluating only a separately owned, fixed HTTP/CGI fixture application,
not ordinary LocalWP, its shared workers, or a production profile. The completed
12-case standalone proof establishes useful framing/resource/settlement evidence
for `/probe.php`, not an installed authenticated WordPress route. Its measured
connection-through-cleanup range was 3554..6154ms; no new measurement is made here.

The next meaningful gate is an **owned pre-owner bootstrap/read-containment and
route-feasibility proof**, not another generic DATA eligibility model or flipping
the unavailable bit. If core cannot reach a genuine route without a forbidden
read, report the profile Blocked. Do not broaden the fence, fake a core screen,
rewrite SQL, raise minimum versions or change protected-read requirements to pass.

## Source-backed gaps and required resolution

| Boundary | Current evidence / mismatch | Required before positive admission |
|---|---|---|
| Route identity | Experimental parser/encoder fix `/probe.php`, empty query and fixed script; no real admin routing | Exact owned `wp-admin/tools.php?page=alynt-drime-backups-uploader` GET and `wp-admin/admin-post.php` POST, no aliases, rewrites or arbitrary script/path selector |
| GET framing | Probe requires Content-Length zero; actual `get_transport()` requires CONTENT_LENGTH and CONTENT_TYPE keys absent | Prove real wire absence survives the exact FastCGI/SAPI chain; missing, empty and zero are different. Never unset contradictory SERVER keys in a harness to obtain success |
| POST framing | Probe accepts synthetic form bytes, not WordPress parsing/slashing | Genuine four-key form, canonical nonzero length <=16384, unchanged raw-to-original-slashed comparison, no reconstruction from POST |
| Authentication headers | Probe rejects every Cookie/Authorization field | Separately review a bounded, fixed fixture-cookie mapping and duplicates/aliases; do not enable general header forwarding or accept an actor/header as authorization |
| Configuration/source | Marked fixture and owned effective ini are known; genuine core, content, routes and cookie settings differ | Bind exact core/owner/server/route source, effective ini and early bootstrap configuration; drift invalidates evidence |
| Bootstrap reads | Owner `open_fences()` runs after transport acquisition and original pin; earlier WordPress work is outside it | Guard/audit before core database/cache use, not merely before the owner callback; separate attempted, prevented and completed protected reads |
| Cache/projection | Historical core helper allows SELECT from owned tables; SQL name matching misses bulk option reads | Cold genuine core cache and table/projection-aware denial, plus protected canaries and fallback coverage; sanitized shadow tables alone are insufficient |
| Response/routing | Standalone proof uses private parent completion and closes the HTTP client | Real route execution and bounded no-store dispositions need separate proof; a parent success receipt is not an HTTP success or an auth result |

Node `rawHeaders` retains separate fields, unlike merged headers; that does not
certify every wire-level normalization or obsolete folding case. The fixed-chain
proof must cover those cases explicitly rather than infer them from semantic
snapshot tests. [Node HTTP rawHeaders](https://nodejs.org/docs/latest-v24.x/api/http.html#messagerawheaders)

## Fixed admission contract: necessary facts are not transferable authority

No public profile argument, environment/constant enable switch, request header,
SAPI name, caller boolean, diagnostic receipt, path resemblance or historical CI
result can enable the owner. Do not introduce a public provider, stream handle,
native handle or alternate controller as a substitute for the real fixed owner.

External deployment assurance and observable request facts are separate:

- The trusted supervisor must independently establish the exact owned chain,
  immutable source/configuration, one worker/connection and bounded settlement.
  A PHP method cannot independently infer upstream enforcement from SERVER data.
- A later approved private admission procedure must check its fixed supported
  context and every locally observable invariant, then still run the existing
  real authentication, capability, original identity, intent and lifetime guards.
  No automatic support for an ordinary installation follows from fixture success.
- If the binding from external assurance to the exact running request is absent,
  ambiguous or drifted, refuse. A new DATA seal or private method name does not
  solve this deployment boundary; private PHP is not isolation from hostile PHP.
- This design does not supply a positive implementation of that binding. Such a
  source integration is gated on the feasibility evidence below, with an explicit
  reviewable trust contract. `claim()` must still consume before dependencies/IO;
  admission work must not create an unbounded phase before `start()` or reset it.

## Bootstrap containment, including WordPress 6.0

WordPress 6.0 starts its database and object cache and checks installation before
must-use/ordinary plugins load. A plugin callback cannot retroactively contain
those earlier reads. Its options implementation loads autoloaded rows and falls
back to all rows. [6.0 bootstrap](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-settings.php),
[6.0 options](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/option.php)

The `pre_wp_load_alloptions` short circuit dates from 6.2. The existing owner fence
therefore does not alone establish the declared 6.0 minimum, nor pre-owner safety
on newer WordPress. Keep PHP7.4/WP6.0 contracts unchanged; source inspection is
not a minimum-version runtime matrix. [Core option reference](https://developer.wordpress.org/reference/functions/wp_load_alloptions/)

Select this bounded feasibility approach for a later separately approved proof:

1. Use an isolated owned bootstrap before `wp-settings.php`, never the existing
   site's wp-config/content. Reuse genuine core hook APIs where possible, as the
   earlier owned core bootstrap does; no core function replacements, SHORTINIT
   success shortcut, SQL rewriting, copied auth/controller, or new generic cache
   framework. Verify source identity, absent unexpected drop-ins and fixed config.
2. Install observation/denial before the first core data query. Bootstrap setup
   and canary seeding are separate parent-owned fixture phases, not permission
   for writes during a request. An owner-only fence is too late. Query-hook
   coverage must be verified against actual dispatch; raw DB bypasses, unknown
   callbacks or unobserved IO block the claim. Do not claim hostile-PHP isolation.
3. Define exact permitted core/bootstrap and auth fixture participants before
   running: owned tables, safe projections/keys and read-only shapes. Reject
   unknown tables, writes and bulk options projections that could include
   protected participants even when the SQL contains no protected option name.
   No allow-all-SELECT or fallback to the application prefix. Do not implement
   an opaque query-policy interpreter merely for this experiment.
4. Seed owned shadow canaries for protected settings and safety participants,
   including autoload and all-options-fallback exposure, with synthetic values
   only. A string-search counter initialized to zero is not a read audit. Require
   non-vacuous safe-read controls and guard-removal controls that would detect
   canary access, never actual application secrets. Record attempts and denials
   separately; zero completed reads after denial is not a successful route.
5. Use a new single-request worker with genuine nonpersistent core cache, verified
   cold before any option read. No persistent/shared cache, preloaded sanitized
   alloptions or fake cache hit may hide a forbidden query. Trace cache hydration
   and value-source provenance as well as SQL. If cold-cache/dispatch coverage
   cannot be established without a new adapter/drop-in, stop for that design
   boundary rather than silently replacing the cache or claiming zero reads.
6. Trace bootstrap, routing, pre-auth and owner phases separately. Any protected
   read before authorization is a failed containment claim, including bootstrap
   reads. If strict denial prevents installation checks or core route dispatch,
   preserve that exact support-safe result and mark the profile Blocked. No
   ordinary-profile certification from an empty/sanitized shadow application.

This chooses an investigation boundary, not an invented working bootstrap fence.
The historical `fixed-core-proof-storage.php` name check/owned-table SELECT policy
remains useful for its narrower isolation proof; it is not the above full pre-owner
protected-read proof. No historical counts or runtime records are rewritten here.

## Genuine owner composition after the feasibility gate

A later source integration must keep the same canonical owner decisions in
isolated and genuine HTTP tests. Existing `issue_get()`/`inspect_post()` are
private, so a real transport needs a separately reviewed fixed owner-internal
bridge; do not call them by reflection/bind/eval, copy their logic, or let the
harness pass a screen, actor, nonce, stream or context. No bridge is added now.
Fixture bootstrap owns transport registration only; actual owner guards authorize.

- GET must traverse real Tools menu/screen routing for the exact slug, then
  fresh genuine session/capability checks on both sides of small nonce issuance.
  No set_current_screen shortcut, nopriv, REST, AJAX or CLI issuer. Obtain only
  the existing blog-bound `register_closed` small-nonce domain, not a full command.
- POST must traverse the reserved authenticated admin-post action, still reject
  non-POST/overrides, exact raw/query/form violations and stale or wrong-domain
  intent. Core admin-post dispatch is not a method/capability/nonce validator.
  [6.0 admin-post source](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-admin/admin-post.php)
- Keep order: consume/original clock -> earned transport/raw bytes -> pin once ->
  owned fences -> genuine fresh auth/capability -> small-nonce verification result
  exactly integer1 or2 -> original
  scope and fresh auth/capability -> private checkpoint -> owned cleanup/erasure.
  Preserve separate GET and POST guards; no repinning a changed valid actor.
- Separate HTTP requests require private fixture session/salt continuity, not
  authorization continuity. Use owned users/sessions only and keep cookies,
  nonce values and salts out of URL, argv, files, logs and existing browser jars.
  No public receipt/continuation or write-capable handle. A nonce/request latch
  is neither deduplication nor client-owned cadence/rollback authority.
- GET issuance/private checkpoint observation must come from actual reviewed
  source, not a test-created value. Later transport response/observer design must
  be explicit. Public POST remains redacted unavailable, with bounded no-store
  disposition; no protected capture, UUID/full nonce, ledger/native session or
  downstream lifecycle call. An unexpected exception is not a qualified refusal.

## Timing, resources and failure settlement

Keep standalone E0/header/body/work/cleanup offsets1000/2000/4000/10000ms and the
separately bounded warm-worker preflight unchanged until a separately approved
profile design earns any change. Keep owner initial10s/original20s unchanged.
Neither PHP bootstrap nor owner entry starts a fresh external budget; the outer
supervisor can conservatively refuse before the owner's own limit. No protected
native stage or new20s execution window is authorized by this design.

A real cookie/header/response profile must fit an explicit bounded envelope;
the current probe's4KiB total headers cannot be combined with a4KiB cookie plus
overhead by assertion. Choose smaller owned fixture inputs or gate a new bound;
do not silently raise limits or forward arbitrary headers. Preserve raw octets,
missing-versus-empty parameters, single original epoch, no-spill application
reader, and fresh remaining credit before every nested wait/cleanup operation.
No queue, retry, shared-worker termination or process-name kill. Unknown ownership,
budget expiry or unverified worker/console/socket/listener/file cleanup is Blocked.

## Acceptance and staged next action

| Layer | Required non-vacuous evidence | Not established by |
|---|---|---|
| Bootstrap feasibility | Allowed owned safe read reaches real core; protected canary/bulk/fallback reads are detected/prevented from first query; cold-cache provenance and exact route reach/refusal distinguished | Always-deny fence, empty tables, SQL substring alone, owner-only trace |
| Wire/profile parity | Real GET absent headers, cookie mapping, exact query/POST/slashing; duplicates, folding/controls,aliases,extra methods,slow/truncated/excess bodies and concurrent/reused connections refuse | `/probe.php` success or merged SERVER snapshot |
| Genuine owner HTTP | Same reviewed source with real core route/screen/session/capability/nonce, valid late-boundary positives and changed/revoked/expired/reentered negatives; required guard removals assertion-fail | Isolated API positive or unavailable public response alone |
| Settlement/preservation | Original-budget worker/console/listener/socket/file settlement, owned row/user/session inventory and previewed cleanup, independent existing application preservation | Client close, PHP fatal, parent exit0 or sample timing alone |

**Recommended next gated slice: Plugin Tester local-only V2.3 Pre-Owner Bootstrap
Read-Containment and Route Feasibility Owned Proof (R2).** This requires fresh
exact target confirmation, Novamira/Site Operations and LocalWP DB preflight,
explicit owned disposable storage/canaries/users/configuration/listener/process
creation and cleanup scope before any experiment. Confirm available genuine
core/version fixtures first; missing WP6.0 leaves that lane Blocked, not waived.
Start with safe controls and strict containment, not admission enablement. Stop
at the first unsupported bootstrap dependency; a stopped core route is a useful
feasibility finding, not completion of genuine HTTP owner qualification.

This approval does not authorize that proof. Only after feasibility and remaining
profile gaps are resolved may a new separately approved source integration change
private admission or add a fixed genuine route bridge. Production loading stays
off; R3-R7, release/deploy, activation and rollback remain independent gates.

## Review and validation scope

Canonical Feature Light:five documentation files plus directly read sources;
Other/design, no UI or runtime delta. Review made the bootstrap-first ordering,
bulk-read/canary coverage, GET header incompatibility and header-envelope budget
explicit. One Low wording issue fixed:nonce verification result1/2 is distinct
from the actual issued small nonce string. No code defect or manual decision
remains in this design scope. No additional code refactor/test manufacture is justified. Bloat/Structure
and UI/UX N/A (no changed executable/rendered interface). No formal DS3/Database/
whole-plugin13/FULL_PRE_RELEASE completion; the full suite remains a later RC gate.

Final focused Feature Security after document validation inspects admission trust,
pre-owner protected/cache reads, unchanged genuine auth and nonce domains, original
budgets, secrets, fixed routes and honest negative/cleanup outcomes. Remaining
runtime obligations above are gated, not claimed fixed by prose or isolated QA.
Result: no remaining confirmed security defect in the scoped design; no executable
security fix or new defect regression handoff. This is not whole-plugin security
certification or evidence that the proposed bootstrap fence works in core.
Publication, actual preservation-test counts and exact-head CI belong in the new
toolkit source-scoped evidence record. This design creates no new runtime proof.

Executed local preservation validation: existing isolated owner/ingress/boundary
PHP tests147/1865,OK,no failures/skips;dashboard299/1423 with2 existing Windows
Sodium skips;88 local Markdown links across the five changed documents resolve.
Whitespace checks pass;executable/test/assets/runner diffs against both excluded
bases are empty and the literal disabled-admission guard remains unchanged.
No tests were added or weakened. These are preservation checks,not genuine
authentication,bootstrap,profile,network or physical storage/transaction proof.
