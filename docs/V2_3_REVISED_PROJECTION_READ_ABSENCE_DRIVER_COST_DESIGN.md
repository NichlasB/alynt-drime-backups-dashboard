# V2.3 Revised Projection Read/Absence and Driver Cost Admission Design

2026-10-25. Approved repository-only DESIGN. No implementation, actual worker,
storage, native execution or production wiring change. Uploader0.5.25 and
dashboard0.1.77 unchanged; no package/release/deploy. Risk R0 analysis/R1 records.

Excluded coherent review bases: dashboard004cdaab9a4557d19afcc59f0505b9739619579e;
uploader645de7a87e32c7a215af33ec86f7598559e18827; toolkit
c349b45c4db218751d59f64a289f4861bcb68cd3. Owner is this dashboard document;
uploader owns future test-only contracts and any separately approved native proof.
Parents: [residual decision](V2_3_BOOTSTRAP_RESIDUAL_CLOSURE_DECISION.md) and
[theme/shutdown design](V2_3_THEME_MODE_PROJECTION_SHUTDOWN_EVIDENCE_DESIGN.md);
companion uploader docs/V2_3_SIDEBAR_ABSENCE_MODEL_FOUNDATION.md.

## Decision

Retain native NO-GO. The new29P/8N model closes a catalog representation gap,
not a genuine missing-row, whole-request query/cost or pre-owner-read proof.
Separate five ledgers instead of treating one integer as all of them:

|Ledger|What it records|What it must not claim|
|---|---|---|
|Option API work|Each reached request/default/filter/cache traversal|One API call equals one SQL query|
|Projected data execution|Bulk/single-option attempts, successful results, rows and provenance|A filter visit or num_queries increment is a successful result|
|Distinct absence|Exact N name plus completed zero-row result, with backing-source identity|Eight counters, falsy value or hidden view row proves eight absences|
|Driver/session control|Connection/charset/database selection and direct session commands, by phase/handle|All such work goes through the query filter or SELECT-only grants prevent it|
|Whole-request resources|Cumulative bytes/allocations/inner work/time through engine/process/EOF|Serialized value bytes, included-file count or test memory limit is a native bound|

These are design concepts, not new exported APIs or a new receipt version. Current
v1/v2 model labels, twelve counters, helpers and native eight-key worker stay intact.

## Fixed option and absence domain

P remains the exact29 names/values from the compiled model. Hidden source rows
remain both protected canaries plus ownership marker:29+3=32. N is exactly:
hack_file, wp_attachment_pages_enabled, category_base, tag_base, use_smilies,
blog_public, theme_switched, sidebars_widgets. No wildcard, caller-selected name,
present thirtieth option, removed canary or pre_option/cache substitution.

The additional sidebar name is absent, not an artificial empty stored value.
Source get_option's empty-array default is acceptable to this widget edge, but
only after a permitted genuine zero-row result. Existing core defaults/filters
remain genuine and are separately covered by source closure; do not inject a
default or rewrite the query/result to manufacture that path.

Three distinct propositions are required for any later physical claim:

1. Exact owned backing source contains no N rows or collation-equivalent aliases.
   A view hiding an existing row does not establish absence. SQL comparison,
   case/trailing-space behavior, collation and bytes need actual owned evidence.
2. Each reached cold N read targets the fixed owned projection with the intended
   exact name, succeeds with zero rows and no driver/server error. A filtered-out,
   cancelled or failed query is not absence. An empty string, '0', false or NULL
   stored value is a present row, not a zero-row result.
3. Genuine cold nonpersistent cache then learns that absence. Repeated requests
   can hit notoptions; they do not create new distinct absence credit. No manual
   prefill, replacement cache or forged history may earn a cold-read claim.

The source option API's non-object get_row branch can also follow a failed read;
therefore observing its default/negative cache alone is insufficient. A future
physical observer must independently establish successful result status and
source identity. This design does not select/implement that observer or alter core.

New v2 synthetic transcript requires eight modeled N requests/results. That is
its explicit fixture contract, NOT proof that all eight API branches occur in a
natural bootstrap. A source-qualified path may reach a strict subset. Do not add
unreachable option calls to fill the model count, or silently reinterpret the
existing candidate as a distinct-name receipt. Any later composition needs a
separately versioned evidence contract and actual reached-name provenance.

## Conditional read arithmetic, not admission ceilings

Assume one stable core/cache, no retries/errors/short circuits/evictions, exactly
one bulk hydration of all29 P values, no other data reads and at most one first
cold miss per distinct reached N. Let D be the number of DISTINCT reached N names,
0..8. Let B be successful/empty bulk attempts on the selected core branch:

|Case|Bulk outcomes|Conditional option SELECT attempts|
|---|---|---|
|autoload|One populated P bulk result|1+D, at most9 for D=8|
|fallback|One empty autoload result then populated P fallback|2+D, at most10 for D=8|

This revises the prior conditional8/9 arithmetic by one miss. It is not a measured
total, a native9/10 cap or a reason to replace the original32-attempt fence.
Repeated category/tag API calls still traverse filters/cache even if their first
miss was cached. Missing any premise invalidates the arithmetic rather than
silently consuming a generic allowance. Excluded extra reads remain unresolved
source obligations, not presumed zero. Current model's17/17 example counters
are synthetic fixture values, not derived from this subtotal or installed evidence.

Track attempts separately from completed successful data executions and row
results; distinguish empty first bulk from one populated hydration. Never subtract
failed reads, replayed statements, setup work or late shutdown activity to make
a receipt fit. Unknown multiplicity or unknown error status fails admission.

## Driver/session phases and observability limits

Inspected public pinned WordPress7.1.2 source this task, ZIP read only in memory:
SHA2568FC96C59A78B7219E4A130222B7FADB51B03E503E8B0123BEAA7E28961C21CE2.
Raw member identities, not installed-file attestations:

|Member|Bytes|SHA256|
|---|---:|---|
|wp-includes/option.php|105246|FE3F0B7E115B2A50C31162ED6428ABE4088C080B0F71B9678D84806168FE90D0|
|wp-includes/class-wpdb.php|121437|E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD|

option163-218/600-648 establishes cache order, one-name SELECT, negative caching
and empty-bulk fallback. wpdb2214-2234 checks readiness before the query filter;
_do_query2347-2356 increments num_queries without a successful-result predicate.
query2267-2284 can replay _do_query after check_connection WITHOUT revisiting the
query filter. Therefore neither filter attempts nor num_queries can certify the
successful data ledger; one filter visit can cover multiple driver executions.

|Phase/family|Pinned source / effect|Future evidence obligation|
|---|---|---|
|Connect/handshake|Driver connection and server/session profile|Owned handle/identity; bounded connect and original deadline; not an option SELECT|
|Charset|wpdb916-937 may call mysqli_set_charset then direct SET NAMES, optional COLLATE|Separate API/protocol work from explicit SQL; exact admitted charset/collation branch and result|
|SQL mode|949-985 direct SELECT @@SESSION.sql_mode, optional SET SESSION sql_mode|Distinguish empty successful mode from error/no result; fixed source-derived filtering and result, no generic SET allowance|
|Database selection|db_connect2033-2046 calls set_charset, set_sql_mode and select|Exact owned schema/handle; driver operation not automatically a query-filter visit|
|Connection check/replay|2124-2138 direct DO1 and reconnect loop; query2267-2284 replay|Separate checks/connections/replays and every resulting setup effect; no undercount from a single filter visit|
|Proof setup/audit|Existing projection-proof-worker directly sets statement timeout, reads identity/grants before bootstrap|Separate verifier session from core session; one handle's limits/identity cannot attest another|
|Shutdown/close|Core/cache callbacks, driver close, engine destruction, process and both EOFs|Keep fences/ownership through the full suffix; early close/candidate/exit0 alone earns nothing|

These families are NOT an exhaustive driver/API inventory, a protocol allowlist
or permission to run them now. Additional metadata/escaping/version/connection
calls and their transitive effects remain source-audit obligations. mysqli API
calls may cause protocol work without a SQL string. Do not double-count a known
explicit statement while also ignoring unknown API work. No raw query/credential
payload is required in support-safe output; use fixed family/outcome identifiers.

Normal-path proof must refuse any failed/unknown/reconnected/replayed session
unless its entire path is independently source-qualified and separately admitted.
It may NOT disable genuine core reconnect/error behavior to force a pass. Original
deadline remains external and cumulative: connection timeout is not a refreshed
request budget. No new retry count, statement allowance or memory limit is assigned.

The current worker's query hook is useful for application intent but cannot cover
all direct commands. Source reasoning plus future owned session evidence must
close that gap without replacing genuine wpdb/cache, patching vendor core or
relaxing grants/guards. Physical observability feasibility remains unresolved;
no existing relay, query hook, SQL-mode check or session budget is promoted to
an all-command observer by this document.

## Admission gates and next implementation

Native readiness still requires ALL A1-A4: transitive include/callback/error graph,
object/resource/shutdown lifetime, cumulative cost/heap/time policy, installed
source/config/SAPI/extensions and owned immutable evidence. Each remains open.
The catalog model supplies neither driver observation nor those closures.

Next preferred slice: **local-only V2.3 Socket-Free Distinct Absence and Cost
Ledger Safety Foundation** in uploader. Only synthetic bounded primitive events,
fixed name/family enums and virtual clocks; no SQL parsing/execution, authentication,
storage, driver calls, native workers or production wiring. Minimal test-only
representation, not a generic tracing framework or new actual receipt. Design
implementation expectations:

- Track exact distinct N identity/result, separately from API/cache visit counts;
  require the compiled v2 catalog rather than a supplied arbitrary allowlist.
- Track B/D conditional arithmetic without returning a whole-request/native cap;
  driver/session/verifier namespaces cannot donate counts or substitute outcomes.
- Genuine unknown/error/reconnect/late effects cannot become successful absence
  or native evidence. Keep all authority outputs false even for model positives.
- Positive autoload/fallback with actual helper equality and reached-name subsets;
  duplicate-one-name-as-eight, alias/overlap/present-as-absent, failed-zero result,
  stale profile, wrong session, replay/missing family/deadline and late-effect
  adversarial cases must fail. Guard-removal controls must expose key refusals.

This implementation is NOT included in the present design approval. A future
native experiment, owned storage/privilege changes, instrumentation or admission
policy transition needs its own exact scope/target approval. Do not skip source
closure to request expanded physical proof merely because synthetic tests pass.

## Scoped reviews and validation

Canonical Feature Light: six Markdown files (owner, both plans, two parent
successor notes and companion foundation pointer), Other/design; unchanged
helpers/worker are direct contract references. Clarified two misleading possible
inferences in the draft: default/negative-cache is not successful absence, and
v2 eight-counter fixture is not natural distinct-name reachability. No executable
fix/security defect or new automated-test coverage claimed. No meaningful
Bloat/Structure/UI/UX trigger; no whole-plugin/DS3/Database/physical/FULL completion.

Final focused Feature Security: review after design/diff/validation; no new request,
SQL, handle, privilege, credential or authority path. No confirmed scoped security
defect or new security-fix regression handoff. Source-scoped evidence only; not
an audit certificate for unchanged runtime. Minimum versions/contracts unchanged.
Preservation tests, link/whitespace checks and exact-head CI/log results are
recorded in toolkit evidence. Existing restore OptionA verified clean uploader
645de7a and external20261024-161220 archive; no restore/checkpoint/archive created.
Static Site Operations/registry only; no LocalWP/site/Novamira/SQL/native access.

Implementation-plan progress: revised read/absence/driver admission obligations
specified; physical qualification remains Blocked. Recommended next action is
the socket-free test-only foundation above, with actual worker/storage/wiring
unchanged. No package, release, tag, deploy or real admission is authorized.
