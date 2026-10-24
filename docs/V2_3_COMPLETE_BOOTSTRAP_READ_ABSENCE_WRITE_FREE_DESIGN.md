# V2.3 Complete Bootstrap Read/Absence Contract and Write-Free Profile Feasibility Design

Outcome: the [source/effect closure review](V2_3_BOOTSTRAP_CLOSURE_PATTERN_CACHE_NO_GO.md)
finds a decisive pattern-cache writer in the fixed absent-content/default-mode
profile. P/N preseeding cannot remove that veto; candidate NO-GO, exhaustive
catalog/native qualification not completed. The design below is historical
conditional specification, not a GO for an owned experiment.

2026-10-24. Approved **design-only** repository work. No executable, test,
dependency, native experiment, authentication, SQL, account/permission, fixture,
WordPress, LocalWP, credential, schedule, service or production-wiring operation.
Storage writes, admission, feature grants, activation and rollback remain disabled.
Uploader0.5.25/dashboard0.1.77 unchanged; no package/release/tag/deploy.

Excluded review bases: dashboard `d31919c753f769f54428e076e36894677b34bd31`,
uploader `bf63cfd77d9c4404ee3dbf361b56c906ca538dbe`, toolkit
`46ea747ad16195536c426d726561b70161b63086`.
Parent: [pre-provisioning audit](V2_3_CORE_PROJECTION_PREPROVISION_DEPENDENCY_AUDIT.md).
Earlier [nine-key design](V2_3_FIXED_CORE_PROJECTION_DEPENDENCY_MANIFEST_DESIGN.md)
remains a rejected candidate, not silently enlarged or implemented here.

## Decision: conditional fixture feasibility, not a qualified profile

Retain strict protected-value containment, real core behavior and Unsupported
ordinary Tools/admin-post profiles. Replace the proposed single list of readable
keys with a design for a **closed read/effect catalog** covering the whole fixed
bootstrap through wp_loaded and owned worker shutdown. The contract is complete
as a specification; the exact transitive source catalog is NOT yet closed.

A preconditioned, disposable read-only fixture profile may be feasible: genuine
core can observe already-initialized synthetic state instead of trying to create
or migrate it. This is a conditional hypothesis, not a GO for provisioning, runtime
success, genuine authentication or production support. No native successor is
recommended until the source-catalog gate below is closed. Do not substitute more
generic profile DATA seals, a larger wildcard allowlist or one-key native retries.

The prior twelve recorded local-source hashes were compared IN MEMORY with the
public WordPress7.1.2 tag; all twelve exactly match. This strengthens the recorded
source provenance, not current LocalWP attestation. No LocalWP file, registry,
port, DB, HTTP route or MCP was inspected in this design. Public source was fetched
as bytes, hashed/read, never saved as a runtime, included or executed. No6.0 source
or runtime equivalence is earned by a7.1.2 match.

## Closed reads: present, absent and denied are different outcomes

Use two finite, source-owned sets for the later candidate:

- **P (present):** exact permitted option names, expected fixture values/types,
  serialization shape, byte/row bounds and provenance. Positive readable view
  rows are EXACTLY P, not just a matching count.
- **N (absent):** exact permitted lookup names whose absence FROM THE REQUEST
  PROJECTION and genuine core default path are reviewed. N contains no readable
  value. A core SELECT must return zero rows before natural notoptions caching;
  no pre_option/default override, fabricated cache entry or query rewrite.
- **D (denied):** everything else, including protected settings/safety/ledger
  participants, aliases and unknown/dynamic names. Reject before dispatch. A
  missing catalog entry is a Blocked dependency, not an exploratory read.

P and N must be disjoint under both exact bytes AND actual database comparison
semantics. Reject duplicate/case/collation/prefix aliases, not merely duplicate
array keys. Preserve source spelling (including WPLANG); no caller normalization
may turn a denied name into an allowed one. Actual DB collation/row behavior needs
later owned proof; a string-only test cannot establish it.

P/N/D classify physical projection reads, not every logical Options API return.
The closure also records **C (core-derived/no-DB results)** from exact, unmodified
core callbacks, with their own source identity, result domain, transitive reads
and effects. For example, default-filters.php has a genuine pre_option callback
for embed_autourls and a computed gmt_offset callback. Do not force an artificial
SQL lookup or mistake a legitimate core result for earned N absence. C grants no
extra read/write authority and permits no new filter installed by us. Unknown
callbacks/derived names still block closure. Genuine post-miss default callbacks
(such as link_manager_enabled) belong in N's reviewed downstream-effect trace,
not an assumed false default. Raw P rows and effective core-return values must
be tracked separately. No callback may manufacture an auth/profile success.

The view remains a positive projection over owned synthetic source. N is not a
negative-name exclusion predicate or permission to read application tables.
An approved N name must stay absent even if an adversarial OWNED source row is
introduced in a labeled control. That proves projection absence, not absence from
an actual site's storage. No production semantics or credential migration follows.

Each N entry needs its exact source caller, default type/value, wrappers/aliases,
subsequent branch and effects. Missing widget settings, expired transient data,
or a missing cron array are NOT safe absence entries merely because get_option
can return false. If the default can initialize/migrate/delete/persist state,
classify it as a P precondition or Unsupported; do not authorize the write.
[Options API behavior](https://developer.wordpress.org/reference/functions/get_option/)
supports the lookup/default distinction, not completeness of our candidate.

## Complete effect and source-closure gate

The catalog must cover included module top-level statements, constructed objects,
exact class factories, core default hooks and their dynamically registered hooks
through plugins_loaded, setup_theme, after_setup_theme, init, wp_loaded and
shutdown. A grep of literal get_option calls is discovery, NOT a closure proof.
Resolve each reachable branch from fixed fixture/config inputs; unresolved dynamic
dispatch, include path, key or side effect is Blocked before provisioning.

Each entry records: exact source tag/file/SHA256/line/caller and phase; physical
participant/query shape; P/N/default domain; branch predicate; cache provenance;
maximum calls/rows/bytes; potential writes/network/file/process effects; fixture
precondition and negative control. Record why excluded branches are unreachable,
not just that a successful trace did not encounter them. Shared hook/global state
and callback additions must be in the closure. Do not copy owner decision logic.

| Phase / frontier | Required closure obligation |
|---|---|
| Core requirements, DB setup, cold cache, installation check | Genuine fixed configuration/source; allow only separately classified connection/session setup; no installation/SHORTINIT bypass |
| Initial taxonomies/post types and module globals | Exact attachment-option branches and all constructor reads; no source-function inventory masquerading as reachability |
| Plugin discovery / plugins_loaded | Empty plugin list still reads hack_file; include widget/embed/default callbacks; no actual content/drop-ins/plugins |
| Rewrite, roles, theme setup / locale | Present permalink string, fixed owned role key, exact theme/locale/transient and user/session paths; no actual-site values or uncontrolled directories |
| Site Health / init registrations | All scheduler, widget, theme migration, metadata, REST, connector, sitemap, font/icon/block callbacks and added callbacks; registration is not execution |
| wp_loaded / shutdown / cleanup | Late handlers and persistent-cache/file/network effects; complete bootstrap does not skip the shutdown suffix |

This table defines the review frontier, NOT a claim that every row is resolved.
Exact option/type/effect lists remain a source-catalog deliverable. No existing
8-key script, nine-key proposal, actual view, query filter or grant is changed.

## Concrete write-free fixture prerequisites to evaluate together

The following are source-backed requirements for a FUTURE separately approved
owned fixture profile, not permission to seed anything now and not a sufficient
complete manifest. Values are synthetic and owned; never copy wp_options, real
users, sessions, roles, themes, transients or schedules into a shadow profile.

| Family | Required candidate state / refusal |
|---|---|
| hack_file | Candidate N: genuine zero-row lookup/default false; absence must not load my-hacks.php; no fake return/filter |
| permalink_structure | Candidate P: reviewed string (possibly empty), never an untyped false causing hidden diagnostics |
| wp_attachment_pages_enabled | P exact string0/string1; retain both controlled label branches, no unrelated dependency exemption |
| Cron registrations | P array with version2 and the exact already-present event tuples required by the closed callback graph; no request-time scheduling or cron-array upgrade |
| Widget settings | P exact option_name for each concretely registered widget, current-format array with _multiwidget; absent/legacy alternatives are refusal controls, not N shortcuts |
| Theme switching / sidebar migrations | Reviewed no-pending-switch state and current-format owned settings where required; any switch/mapping/flush/update path is Unsupported until resolved |
| Transients / caches | Exact physical data/timeout participants and nonexpired domains where used; absence/expiry may initialize or delete, so require caller/effect review; no external persistent cache |
| Authentication-related metadata | Keep at most two synthetic actors / sixteen usermeta rows and exact owned key domains; no authentication or positive owner admission inferred from readability |

### Cron: pre-existing fixture state is not schedule execution

The recorded audit identifies Site Health weekly plus privacy hourly/daily
registration paths. Public7.1.2 update.php:1089-1100/1210 adds init registrations
for wp_version_check, wp_update_plugins and wp_update_themes (twicedaily).
These six named events are a MINIMUM known prerequisite, not an exhaustive list.
[Core scheduling](https://developer.wordpress.org/reference/functions/wp_schedule_event/)
can persist the cron array; DISABLE_WP_CRON is not a registration-wide veto.

A later owned fixture could contain reviewed event tuples (exact hook, argument
hash, args, recurrence, interval, timestamp) before the worker starts. Their times
must lie beyond the unchanged request horizon under the fixed trusted clock.
Version2 avoids the legacy-array upgrade path. Validate absence of extra events
and exact argument semantics; no wildcard hook names or arbitrary timestamps.
The worker cannot repair missing/stale/malformed tuples. Removal of each required
tuple must expose the genuine initialization path and FAIL before persistence.
No core event is executed or actual WordPress cron option/schedule changed.

### Widgets: absent is a write trigger, not an empty-state success

Public7.1.2 class-wp-widget.php:611-635 saves defaults when settings are false,
may delete a legacy alternative, and converts nonempty legacy settings. Thus an
always-absent widget_* family is not write-free. Exact option names must come from
the reachable fixed constructors/factory, not a wildcard or unverified naming
formula. A reviewed current-format empty-instance array can be a P precondition;
this is not actual widget behavior/runtime proof. Keep core hooks intact.

### Other effects are independent vetoes

Public theme.php:3483-3519 can flush/update state after a pending theme switch.
Its transient/root paths and widget sidebars need independent closure review;
finding an update function in a file does not prove it is reachable. Conversely,
not seeing it in one trace does not prove it cannot execute. Type warnings, JSON/
serialization failures, expiry and migrations are failures, never suppressed.
Object-bearing fixture serialization is Unsupported; allow reviewed arrays/scalars
only. Any required new table, routine, file writer, shared process, network call,
external cache or privileged handle exceeds the fixed options/users/usermeta
candidate and must stop for a new decision.

## Independent containment, bounded resources and evidence

Retain the two-principal model: locked source-SELECT-only definer; request invoker
SELECT on exact owned views only, no base/application grants, inherited privilege,
FILE/EXECUTE/DML/DDL/GRANT or root fallback. Root/definer credentials/handles remain
outside worker memory, environment, argv/config and transcripts. No authority
follows from P/N membership, profile identity or receipt.

Proposed REVIEW ceiling:64 unique names across P union N, exact fixed manifest,
aggregate encoded fixture values/keys at most64KiB; no arbitrary runtime choice of
64 keys. The catalog must justify its actual count and each bound. This is NOT an
enacted increase of the existing eight-key policy. Preserve32 query attempts,
4096-byte query maximum, existing statement/worker/output/pipe cleanup limits and
owner initial10s/root20s/HTTP1000/2000/4000/10000ms epochs. Prove the conservative
worst-case query count fits BOTH autoload/fallback branches before provisioning;
capacity overrun is NO-GO, not permission to raise a deadline/cap or warm the cache.

Each worker starts genuine empty nonpersistent core cache. Capability controls
use separate connections/workers; never import their values into the core cache.
Track attempted versus completed safe reads, P values, genuine N zero-row results,
natural cache hydration, mutation INTENTS and attempted persistent/network/file
effects. A successful request must have zero mutation intents, not merely zero
committed changes because the invoker denied them. Natural in-process cache writes
are not persistent storage writes. Fixed connection charset/session-budget setup
is separately classified and bounded; it is not an arbitrary SET allowance.

Required eventual evidence: observer-free DB safe-read/denial positives; unsafe-view
canary assertion kill and verified revocation before real core; fresh cold
autoload/fallback branches; exact P/N/default outcomes; every required precondition
independently removed/corrupted as a fail-closed control; query/write-fence bypass
controls assertion-killed on owned data; actual completion through the shutdown
suffix; independently verified owned cleanup and application/permission/source
preservation. Static tests, denial-only owners and synthetic traces cannot earn
this evidence. No physical page/timing or hostile-DB-admin containment claim.

## Feasibility disposition and ordered next step

1. **Design Complete; existing candidate still NO-GO.** Ordinary profiles,
   production ingress, actual user grants and bootstrap/authentication readiness
   remain Unsupported/Blocked. The proposed preconditioned fixture is conditional.
2. **Next: one source-complete closure catalog / fixture-prerequisite review.**
   Use pinned public core sources and recorded identities, without WordPress/SQL,
   native experiments or fixture writes. Resolve the entire phase/effect frontier,
   exact P/N entries, aliases/default types, event/widget/transient prerequisites,
   byte/query costs and incompatible paths together. No new DATA sealing framework.
3. **Only after that catalog is closed** propose a separately approved owned
   profile/harness/proof scope with fresh exact LocalWP confirmation, DB-principal/
   permission/fixture setup authority, audit-first preflight and exact cleanup.
   Unknown source/dispatch/effect or an insufficient catalog stops before creation.

Do not repeat the earlier native experiment with an extra key. If source closure
shows an unavoidable persistent effect under every allowed fixture state, reject
this candidate and return to a product/architecture decision, not a silent
protected-read or write exception. Conditional fixture feasibility is not a claim
that production support is possible or impossible.

## Public source evidence and review limits

Public tag: [WordPress7.1.2 source](https://github.com/WordPress/WordPress/tree/7.1.2). The12
recorded hashes in the parent audit all matched exact public bytes this turn.
Additional public source identities (not newly compared against LocalWP):

| File | SHA256 |
|---|---|
| wp-includes/theme.php |B32DD01A7DF72AD34FD6770ACE7DB730759FC0E5BD06B9ED82A3C7C92DA428E9|
| wp-includes/widgets.php |2426468C359C2AAAB8DC047C9BF0B896ADB58C3AE26B8C3EDF29C8941176C1D6|
| wp-includes/class-wp-widget.php |E2B3E341D2E042E61320A8BC84C278570537DC42EBCFAA5517DD0A72E3C915B7|
| wp-includes/update.php |B8F3C41257C116DF55A338B519DFE53FF3082F76D2D1FEA4A56C7AABB6CF4823|
| wp-includes/class-wp-locale-switcher.php |C80DCE58AD081AD14C6C67EFA63A78C964D03B79866906E88241BA428301F668|
| wp-includes/default-filters.php |55BBF47FB835A0F64F28423D475B51F7FA72479F319FCA417B4F23990D44F897|

Canonical Feature Light/final Security scope: four changed Markdown files (this
design, audit successor pointer, both plans), direct fixed proof source and cited
public core branches. Other/design; no executable/rendered delta, generic bloat
or new success-path test. Bloat/Structure and UI/UX unnecessary. No DS3/Database/
physical transaction/FULL_PRE_RELEASE or refreshed historical readiness claim.
Executed preservation: uploader isolated156tests/1951assertions, no failures/skips;
dashboard299/1423 with two existing Windows Sodium skips;37 local Markdown links,
whitespace and no-executable-delta checks pass. No new tests or local full uploader
PHP/7.4 runtime claim. OptionA source safety check: clean pushed uploader base and
existing pristine archive, no new archive/checkpoint/source or site restore.
Canonical Light found/fixed one Edge Case design ambiguity: core-derived/pre-option
results must be separate from physical P/N lookup evidence. No remaining in-scope
design finding; the unresolved closure catalog is an explicit prerequisite, not
an implied completed artifact. Final canonical Security after validation checks
unchanged actual policy, exact C source/default semantics, protected/alias denial,
no mutation-intent waiver, no new handles/filters/hooks and separately gated fixture
writes. No confirmed introduced security defect or security-fix regression handoff.
Exact-head CI belongs in the source-scoped toolkit closeout. Native proof, complete
source catalog and6.0 qualification remain unearned. All actual policy, storage,
execution and wiring remain unchanged.
