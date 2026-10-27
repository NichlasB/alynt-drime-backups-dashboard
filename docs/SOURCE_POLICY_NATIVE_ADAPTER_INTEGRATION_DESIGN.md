# Source-Policy Native Adapter Compatibility and Guarded Form Integration Design

## Authority and immutable baseline — 2026-10-27

Separately approved repository-only design through validation, commit, push and
exact-head CI. Clean/synced restore point:
`74885c601d2b1de5f28db66a24472e1ecfccab23`. R1 documentation about a future R3
concurrency/public-method integration; autonomous R2 authority is not expanded.
Version0.1.77/schema8 and declared PHP7.4/WordPress6.0 compatibility stay unchanged.

Only this design and owning plan/review/evidence-limit records change. No runtime,
test, manual proof script, package configuration, catalog, asset or dependency
change; no actual option/SQL/schema/cache write, authentication, LocalWP/site
inspection, bootstrap, fixture, native experiment, service, schedule, credential,
client/Drime, release/package/tag or deploy operation. Public primary sources were
read online; no site was contacted. Uploader/toolkit stay read-only. Prior8/8 is
not inflated; V2.3/memoization remain deferred. This does NOT fix the live race.

Inputs: [save/conflict contract](SOURCE_POLICY_CONCURRENT_SAVE_CONTRACT_DESIGN.md),
[DATA/result foundation](SOURCE_POLICY_CONDITIONAL_SAVE_FOUNDATION.md), and
[owned native evidence](SOURCE_POLICY_NATIVE_OWNED_PROOF.md). Historical proof
counts remain historical, not rerun or broadened by this design.

## Actual integration surface and additional constraint

The production [Source_Policy](../includes/class-source-policy.php) still replaces
the whole option from a fresh get_option view; the [factory](../includes/class-plugin.php)
and Admin_Page fallback construct that same facade. Repository PHP call-site
search found only the [local admin handler](../includes/admin/traits/trait-admin-page-local-actions.php)
calling set_source_mode outside tests. External PHP consumers cannot be ruled out.
The boolean public signature must therefore remain boolean, not WP_Error/array.

The [form](../includes/admin/traits/trait-admin-page-basic-detail-helpers.php)
derives its toggle from an ordinary read and has no revision. The public
[shell](../includes/admin/traits/trait-admin-page-shell.php) checks manage_options
before POST processing; the [action helper](../includes/admin/traits/trait-admin-page-actions.php)
checks the action nonce before uncoerced site-ID type. The handler now rejects
errors/unknown results instead of truthiness, but still calls the legacy setter.
Its existing literal-boolean controls must remain until deliberate integration.

Newly explicit driver limitation: [wpdb::query source](https://developer.wordpress.org/reference/classes/wpdb/query/)
can reconnect and reissue a query; the [pinned6.0 implementation](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/wp-db.php)
also has this path. Therefore one query-filter observation is not one physical
attempt under connection loss. Inference for our fixture candidate: calling
ordinary wpdb::query does not establish the required no-replay boundary. Prior
syntax/lock-timeout/CAS/hook successes remain valid, but no lost-connection or
driver-replay qualification was performed. The fixture writer is NOT a drop-in
production adapter. No source repair or native loss experiment runs here.

## Selected compatibility policy, not a new minimum version

| Boundary | Evidence now | Admission required before future wiring |
|---|---|---|
| PHP7.4 / WP6.0 minimum | Plugin contract; isolated CI is not native core proof | Genuine minimum-core option/hook/cache/driver qualification, not a version-number assertion |
| Recorded WP7.1.3 / PHP8.5.12 / MariaDB10.6.23 | Owned clone, core libraries, nine groups; no application bootstrap | Requalify the actual fixed adapter, non-reconnect connection and eligible callback profile |
| Other core/DB versions or patches | Unqualified | Explicit source/capability evidence plus relevant native proof; never infer support from one API existing |
| Process-local stock core cache | Owned evidence | Fixed core cache identity, relevant hook admission and verified local invalidation |
| External/persistent cache, db drop-in, replicas/router, multisite | Unqualified | Unavailable for this initial conditional port; no fallback writer or silent support expansion |

Published [WordPress6.0 option source](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/option.php)
uses no for explicit non-autoload; the recorded modern proof uses off. Logical
false is retained, but physical encoding/cache/autoload-value membership must be
chosen by the qualified core profile. Do not copy the fixture's hard-coded off
or modern helper requirement into a supposed WordPress6.0-compatible adapter.

Keep ordinary fresh classifier/read behavior on existing supported installs.
For a future unsupported conditional entry, show unavailable and do not issue a
revision or mutate. The legacy wrapper must return false, never call the old
unconditional writer. This is NOT permission to silently remove an existing
save feature on a declared supported/deployed stack: minimum-core and intended
deployment-profile acceptance are release/integration gates. If they cannot be
qualified, stop for a compatibility decision, not a minimum-version bump,
per-site migration or unsafe fallback. Unknown new profiles remain explicit.

## Fixed native owner and non-reconnecting connection

Select one plugin-specific owner behind the existing Source_Policy facade; no
generic storage framework, persisted capability ledger or administrator bypass.
Keep its SQL/schema/error helpers cohesive and within existing structure guards.
Do not load scripts/manual into runtime or expose their prefix/CLI/control flag.

1. Derive the single-site database/table/option domain from trusted core context.
   Public commands cannot supply a connection, table, prefix, owner label,
   callback, raw serialized map, observation or precomputed plan. Reconstruct
   observation/planning inside this fixed owner. DATA factories are not grants.
2. Select a private dedicated wpdb-derived connection whose check_connection
   override always returns false after a connection failure, with core-compatible
   signature and no sleep, reconnect, bail or mutation replay. Keep normal core
   preparation/charset processing; do not copy query(), call private core methods
   or alter global $wpdb. Constructor/initial connection behavior must also be
   bounded and error-safe before qualification; setting reconnect_retries=0 alone
   is not the selected guard. This design does not claim the wrapper already exists.
3. Obtain connection settings only from the existing trusted core configuration,
   never a POST value or separately persisted credentials. Matching database,
   socket/TLS/flags/charset, routing and isolation must be established, not guessed
   from the LocalWP fixture. An unsupported custom connection profile is unavailable.
4. Apply qualified limits only to that owned session, never the application's
   global session or service. Start with the proved MariaDB statement3s/lock1s
   limits as qualification targets, not a portable MySQL guarantee. No default
   unbounded fallback; close the owned connection in every terminal path.
5. Admit the fixed schema/unique key/transactional autocommit profile using bounded
   indexed metadata, not full option scans. Do not run schema DDL to repair it.
   Keep profile checks process-local, not a new persistent option or cache grant.
6. Reuse the one raw-row admission, at most one conditional write and at most one
   bounded ambiguity read from the prior design. No polling, automatic retry,
   upsert/replace/ignore, callback replay, compensation or hidden rebase. Bind the
   indexed option identity and exact binary/length-sensitive bytes/autoload.
   Keep stale-before-noop and absent/present-empty semantics. ABA is not solved.

Record submission before calling the driver. Definite syntax/qualified lock
failures are not all duplicate errors; transport loss/exception after submission
is uncertain unless definite non-application is independently established.
If the owned connection is lost, do not open another connection to classify and
then retry; return uncertainty. A later read cannot prove that this command,
rather than a contender, committed an equivalent value. Never fabricate saved
from a matching follow-up row, false, zero or an impossible multirow count.

## Hook, decoder and cache admission

An empty plugin bootstrap in the prior proof does not qualify installed callbacks.
Eligibility covers option read/default/projection hooks, sanitize_key, option
sanitation, pre-update filters, before/after add/update observers, relevant cache
filters and query transformation. Unknown relevant callbacks are unavailable
unless explicitly qualified by code-owned profile evidence; do not execute an
unknown callback to discover whether it is safe. No user-provided allowlist or
global remove_all_filters workaround. Non-empty positive observer controls are
required so rejection-only implementations cannot pass.

For supported callbacks, preserve branch-specific order/arguments and once-only
semantics. Identity/value-preserving sanitation and pre-update callbacks may run;
a changed effective candidate is unavailable before mutation, not permission to
drop unrelated policies. Reject reentry before any second protected read.
Prewrite callbacks may cause a contender: CAS must still detect it. Detect
relevant admission/profile changes across callbacks before submission; after a
commit, failed/unconfirmed cache or hook completion is uncertain, not denial or
rollback. Trusted third-party PHP side effects are not sandboxed/transactional.

Primitive stored data must be bounded before decode and reject objects,
references, malformed/count/depth/size abuse without magic methods. The fixture's
16KiB/depth8 limits are conservative qualification targets, not permission to
truncate a larger real option. Unsupported storage returns unavailable/invalid;
no fallback empty writable map or conversion of a filtered value into raw truth.
Validate any chosen decoder/resource limit at its real layer before connection.

After an acknowledged mutation, invalidate only the relevant individual option,
notoptions and alloptions core keys through qualified APIs. Do not seed a stale
map, read-modify-write an old shared cache snapshot, or flush the global cache.
Confirm process-local completion; preserve uncertainty for failed handling.
Reentrant or in-flight readers can still repopulate caches. This is not global
linearizable classifier reads; do not change classification or introduce
memoization to conceal that limit. External cache admission remains closed.

## Guarded form and result contract

Proposed names below are future interfaces, not implemented/public APIs today.
Keep one authorized form-view entry and one explicit conditional command. A
bounded form view returns only current mode and lowercase64-hex revision from
the SAME owned observation. It never returns raw bytes, other sites' maps,
database identity or write handles. Ordinary classifier reads remain separate;
do not combine their possibly filtered label with this authoritative revision.

GET/render: public manage_options gate first, normalize/resolve the current site,
then request the view for fixed wpvivid. Retain the existing eligibility to show
the WPvivid panel. If unavailable, retain readable context with an explicit
editing-unavailable explanation and no usable revision/submit action. A cached
mode must not be presented as an authoritative editable value. Do not open the
owned write-capable connection at plugin init, polling or ordinary read time.

POST: manage_options -> existing action-specific nonce -> raw field types ->
existing site-ID normalization/record resolution -> source/mode allowlists and
exact revision shape -> profile admission/owned observation -> compare ->
conditional mutation -> cache/hooks -> safe result/audit. Invalid compounds,
missing/wrong nonce and malformed revision must cause zero owner reads/writes.
Keep the current nonce domain; a revision is consistency DATA, not authentication,
a nonce, a secret or a replay lock. Do not trim/lowercase a malformed revision.
HTTP mode accepts required/external_optional explicitly; do not convert an
unknown posted mode into a required command. Preserve documented legacy scalar
normalization separately rather than silently changing its three-argument API.

Select an explicit future save_source_mode_if_current entry returning only
literal saved or unchanged strings, or WP_Error. These are ordinary result DATA,
not native receipts. The owner must derive them from its actual admitted operation;
callers cannot submit them or use model classify_result as evidence of mutation.
The handler accepts only these exact strings from that entry, tests is_wp_error
first and rejects booleans, arrays (including the model's status array), objects
and other strings. Do not make the existing boolean handler accept these values
piecemeal before its actual call is deliberately switched.

Keep set_source_mode(site,source,mode): bool for compatibility. Route it through
the SAME private owner/planner/writer using one fresh internal observation, not
observe-then-call-another-entry-that-reads-again. It maps saved/unchanged to true
and all other outcomes to false. It supplies no stale-form protection to an
external caller lacking an expected revision, but never bypasses CAS or retries.
Explicit array fixtures must not acquire a live writer via function_exists.

Keep the existing safe error-code mapping and escaped notice renderer. Saved is
success; unchanged reports no change without write/success-hook replay. Conflict
says not saved and asks for refresh/review; uncertainty says it may have saved
and asks to inspect before another change. Preserve attempted choice only as
non-authoritative feedback, never an auto-resubmission. Audit completed outcomes
as succeeded and errors as failed, with only allowlisted site/source/mode context;
failed is not proof of no commit. No raw rows, maps, SQL, handles or revision in
public errors/audits. Retain busy/focus/live-region behavior and a single notice.
Future UI implementation requires focused UI/UX/i18n tests, not claimed here.

## Cohesive implementation and acceptance gates

1. Next repository-only implementation: fixed non-reconnecting adapter/admission
   and facade/form/handler contract safety foundation, isolated API/driver/IO
   tests only. Keep the production factory/legacy default construction and actual
   WordPress/native execution disconnected. Use explicit test seams that cannot
   select a live store from function existence; no always-unavailable fake success.
2. Separately approved owned proof of THAT adapter against minimum-core and the
   intended deployment profile, including hooks/cache and driver failures. Any
   site/fixture/native failure experiment requires fresh scope/target authority;
   this document authorizes none. No relay/Windows toolchain architecture is
   selected or necessary merely to record a conservative uncertain result.
3. Separately approve final production connection only after the matrix qualifies.
   Switch factory, conditional form/handler and retained legacy wrapper as one
   coherent migration-free integration; no temporary parallel unconditional
   writer. Validate genuine authorization/nonce/render/save and native contention
   with owned fixtures, plus full release reviews before packaging. Live deploy
   still needs its own current backup/approval; no automatic deployment.

Required regressions: positive saved/unchanged and non-empty hook controls;
capability/nonce/type/revision ordering with zero protected calls; one-observation
view/revision; same-/cross-site and creator CAS; stale already-satisfied input;
alias preservation; primitive hostile storage/resource limits; exact false/0/1/
impossible acknowledgements; callback/profile changes and reentry; definite vs
uncertain failure, no reconnect/second physical mutation on simulated disconnect;
no-op and failed CAS have no success hooks; private cache faults; strict handler
result types and legacy bool compatibility. Predicate/reconnect/guard removal
must kill assertions. Isolated tests are not genuine authentication, DB/cache
proof or release readiness. Do not weaken supported contracts to fill a batch.

## This turn's validation, progress and next gate

No code/test change or native rerun. Existing isolated related255 tests/592
assertions randomized seed20261027 pass; structure137 runtime/205 support/167
test files passes. Primary-source links and actual callers were inspected.
All nine relative links resolve; diff hygiene and non-document baseline identity
checks pass. Source-scoped contract review kept minimum-profile admission closed,
driver replay explicit, raw DATA separate from native outcomes, authorization
before owner access and legacy/native mutations on one future path. No further
design inconsistency found. This is documentation review, not canonical whole-
plugin UI/UX/security completion or evidence of an implemented connection.
Publication: design `81719db9896b52e1621f94b0bc9b9e12bad60413` passed
[exact-head CI40579269577](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40579269577).
Actual job logs retrieved directly: PHP7.4/8.3 each1089 tests/4557 assertions,
no failures/skips; Node16/16 per job; all configured gates pass and both audits
report zero advisories. These isolated hosted suites do not execute the proposed
adapter, a native/site proof or genuine authentication.
Whole07/07A/13/FULL remain incomplete; historical checklist rows stay untouched.

Implementation-plan progress: compatibility, driver replay, unsupported profiles,
fixed ownership and same-observation form/legacy integration now have explicit
contracts and acceptance gates. The actual lost-update issue remains open.

Recommended next gated slice: **repository-only Source-Policy Non-Reconnecting
Native Adapter and Guarded Integration Safety Foundation**, implementing the
bounded adapter/admission plus facade/form/handler contracts using isolated tests
only. Keep actual authentication, storage/native execution, production factory
wiring and live/site state disabled/unchanged until separately qualified.
