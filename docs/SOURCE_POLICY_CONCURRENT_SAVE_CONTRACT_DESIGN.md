# Source-Policy Concurrent Save and Conflict Contract Design

## Authority and baseline — 2026-10-27

Separately approved repository-only design through validation, commit, push and
exact-head CI. Dashboard master baseline/restore point:
`18a04e4e9d7bdd2d852cadae70f0b214884f4325`, clean and matched remote master.
Version0.1.77/schema8 unchanged. Only this design and owning plan/review records
change. R1 document publication about a future R3 persistence boundary; no runtime,
test, option/SQL/schema, cache, WordPress authentication/bootstrap, fixture, HTTP,
native/concurrency experiment, credential, schedule, production-wiring, package,
release/tag/deployment operation. Uploader/toolkit remain unchanged/read-only.
Prior8/8 ceiling is not inflated; deferred V2.3 and memoization stay parked.

## Source evidence and actual gap

- [Source_Policy](../includes/class-source-policy.php): set_source_mode90 reads
  and normalizes the entire option map, changes one site/source, then replaces
  the option at130. all_policies138 preserves fresh get_option observations and
  existing normalization. Required means no stored optional override; only the
  wpvivid source and required/external_optional modes are supported.
- [Admin handler](../includes/admin/traits/trait-admin-page-local-actions.php):
  handle_update_source_policy_action190 retains the action-specific nonce/type
  guard, but line200 combines site validity and the boolean setter with &&.
  A future WP_Error return would be truthy there: it must NOT be introduced
  before explicit result handling and audit mapping are implemented.
- [Policy form](../includes/admin/traits/trait-admin-page-basic-detail-helpers.php):
  render_source_policy_panel90 displays/toggles mode and posts site/source/mode
  with its nonce, but carries no observation/version. [Notice renderer](../includes/admin/traits/trait-admin-page-action-notices.php)
  currently accepts boolean success. The public shell's manage_options boundary
  and all existing nonce/site-ID checks must remain before sensitive work.
- [Settings](SETTINGS.md) and uninstall.php retain the one existing
  alynt_drime_backups_dashboard_source_policies option; no per-site option
  registry, table, envelope migration or new persisted revision is selected.

Source-derived counterexample, NOT a concurrent experiment: A/B read mapM;
A writesM+A(site7); B writesM+B(site8), undoing A. If A changed optional back to
required, its loss can leave optional classification in effect. No client
execution is granted, but the monitoring expectation is material. Ordinary
normalization, fresh reads and successful individual updates do not prevent it.

## Verified WordPress constraints, not installed-site evidence

Core update_option reads/filters/serializes before an update whose predicate
matches the option name, not the previously observed value. Its boolean false
also covers unchanged values. It is not the selected compare-and-save primitive.
See [official update_option reference](https://developer.wordpress.org/reference/functions/update_option/).

The published WordPress6.0 source and current add_option reference contain an
upsert after preliminary existence checks. Inference: add_option alone cannot
be credited as an atomic insert-if-absent under two stale absence observations.
See [pinned6.0 core](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/option.php)
and [current add_option source](https://developer.wordpress.org/reference/functions/add_option/).

get_option can return filtered/default/cached values rather than an authoritative
raw row. A saved form revision checked before an unconditional update is still
a race. Cache deletion alone is not a write barrier.
See [official get_option reference](https://developer.wordpress.org/reference/functions/get_option/).
These source/documentation checks were read-only. They do not qualify an
installed WordPress version, database driver, cache backend or extension profile.

## Selected contract: observed-map conditional save, no hidden retry

Retain one map and choose conservative conflicts, not automatic rebasing,
per-site migration, an advisory transient mutex or last-writer-wins replacement.
The next foundation models the contract in memory only; native implementation
and production connection remain separately gated.

### Observation and command

1. A fixed owner obtains one authoritative observation for this one logical
   option: existence, exact serialized bytes and autoload state, plus its
   normalized effective map. Bind the owner to the current single-site storage
   domain, not a request-supplied table, option name, callback or connection.
2. A domain-separated SHA256 consistency revision frames owner identity,
   existence and byte lengths/values for serialized data and autoload. Compare
   bytes, not SQL collation or a hash of only the sanitized map. Missing and
   present-empty differ. Never post/export raw bytes, entire maps or handles.
   The64-hex revision is not a secret, authorization, CSRF nonce or replay lock.
3. Future form rendering must derive its displayed mode and revision from the
   SAME observation after authorization. Do not pair an authoritative revision
   with a differently filtered/stale display value. No such form is added here.
4. Future guarded command accepts site/source/desired mode and expected revision.
   Keep capability, action nonce and compound-ID rejection first. Require exact
   revision shape; stale/missing revision fails before mutation. Retain existing
   source/mode/site normalization and required defaults, including alias merge
   semantics; never replace whole-map normalization with direct raw-site lookup.
5. Reject unavailable/invalid storage or an unqualified filtered view rather
   than treating it as an empty writable map. No object conversion or arbitrary
   deserialization may manufacture a trusted writable observation. Historical
   read fallback is not permission to overwrite malformed option storage.

The revision is state-equivalence, not a durable generation: M→N→M may match
again. Detecting every intervening edit/replay requires new persisted versioning
and is NOT promised by this migration-free contract.

### Atomic mutation boundary and budgets

For a qualified native port, select a single conditional existing-row update
whose predicate includes the fixed option identity and exact observed bytes/
autoload. Apply binary/length-sensitive comparison; case-insensitive/trailing-
space SQL comparison is not byte equality. No compare-then-update_option path.
Preserve normalized non-target policies when constructing the replacement.
Any interleaving change yields conflict, including a change on another site.

For observed absence, use one strict insert-if-absent under the qualified unique
option-name constraint, never an overwriting upsert/replace. A concurrent creator
wins; this command conflicts without modifying that row. Database errors are not
suppressed or all labeled duplicates. Do not use INSERT IGNORE as a catch-all.
Keep the desired logical non-autoload policy; physical autoload representations
must be core-version-qualified, not guessed from a fixture.

Budget: one authoritative admission read, at most one conditional mutation,
zero automatic retry/rebase, at most one bounded follow-up read to classify an
ambiguous result. No scan over options, global lock, sleep loop, new revision
option, callback retry or compensation. Deadline/statement limits must be
qualified in the native proof, not fabricated here. Existing map-size and cache
performance are not newly load-qualified; this is not memoization work.

| Outcome | Required meaning / handling |
|---|---|
| saved | Exactly one qualified conditional mutation acknowledged, then required post-write/cache handling completed; map was current at that boundary, not forever |
| unchanged | Revision matches an authoritative observation and the desired effective map is already equal; no write or success-hook replay; missing+required may remain absent |
| conflict | Revision mismatch, lost conditional update or competing first creator; zero retry, no success notice/audit; refresh/review current state before resubmitting |
| unavailable / invalid | No trusted observation, unsupported filters/profile, malformed revision/map or unsupported native/cache capability; no fallback unconditional write |
| storage_error | Definite read/statement failure, generic support-safe error, never coerced to success |
| write_uncertain | Acknowledgement, post-commit cache/hook completion or connection outcome is unknown; mutation MAY have happened; no automatic retry or fictional rollback |

Check expected revision BEFORE deciding unchanged: a stale duplicate that happens
to desire the latest value remains a conflict, not a fabricated idempotent save.
Zero affected rows cannot mean success; false is not zero. An impossible multirow
result is an invariant failure, not permission to continue. No-op success is
linearized at its observed read, not an exclusive future-state guarantee.

Same-site overlap: first valid conditional change wins; stale contender conflicts.
Different-site overlap: loser conflicts too, preserving the winner's entire map;
automatic cross-site merging is intentionally deferred. Sequential fresh commands
can succeed. This is proportionate for the existing single-setting admin surface.

## WordPress compatibility and cache closure — mandatory before native wiring

This is a plugin-owned conditional operation, not a generic replacement for the
Options API. A raw SQL predicate alone does NOT satisfy the compatibility gate.

- Separate the authoritative raw observation from get_option/filter projections.
  Preserve supported core sanitation/pre-update/add and observer-hook semantics
  for this owned option, including argument/order/once-only success behavior.
  Do not silently skip hooks, claim an update_option call is conditional, or
  fire post-success hooks for a lost CAS. Callback failures after a commit are
  uncertain, not a rollback. Guard owner reentry; do not run callbacks again as
  an invisible retry. Arbitrary callback side effects are not made transactional.
- Value-transforming filters must not replace unrelated policies or detach the
  form's displayed view from its raw observation. If parity/ownership cannot be
  established for a profile, the native port remains unavailable. An empty hook
  fixture does not establish general compatibility; no unapproved hook contract
  break, global hook removal or permissive fallback is authorized.
- Treat core option, notoptions and alloptions caches explicitly. Clear only
  relevant core cache entries through qualified APIs after acknowledged mutation;
  never seed a potentially stale map after a newer writer, write a stale shared
  cache-map snapshot, or flush the entire object cache. Failure/unknown cache
  completion must not be reported as ordinary saved. Cache adapter limitations
  are explicit; conditional writes must never trust a cached stale map.
- In-flight old readers can repopulate caches; invalidation is not proof of
  globally linearizable get_option/classifier reads across processes/backends.
  Preserve ordinary read behavior and the no-memoization decision. The new
  observation/form path must remain authoritative; stronger display-freshness
  promises require separate evidence, not repeated get_option calls.
- Existing/absent rows, non-autoload and legacy autoload states, affected-row
  conventions, serialized alias maps and filters must be qualified against
  genuine core/driver/cache behavior before connection. No arbitrary-version,
  replica, external-cache or multisite support is inferred from this design.

Other plugins, direct SQL, purge/uninstall or administrative tools writing the
option outside this owner are not coordinated. The conditional operation will
not overwrite a changed observation; it cannot prevent a later external writer
from overwriting it. Do not market this as universal option transactionality.

## API, handler and user-result closure

Preserve the existing public three-argument boolean set_source_mode contract;
do not start returning WP_Error to its current && consumer. A future explicit
conditional-save entry may return a typed result/WP_Error. Its handler must test
is_wp_error first, then accept only literal success/known outcomes, record a
non-success audit for conflict/error/uncertainty and emit the safe corresponding
notice. It must never boolean-cast an error, unknown array/object or false into
success, or include raw storage/revision-map material in logs.

Before native connection, all internal mutations (including any retained legacy
boolean wrapper) must use the SAME conditional writer, not fall through to the
old unconditional replacement. A legacy wrapper may observe current state and
map conflict/error to false; it does not gain stale-form detection without an
explicit rendered revision. Document that compatibility limit. No new form,
conditional entry, wrapper behavior or production writer is implemented now.

Conflict copy should say the monitoring policy changed, this submission was not
saved, and to refresh/review before trying again. Uncertainty copy must acknowledge
that saving may have occurred and require inspection, not encourage blind retry.
Keep attempted choice as non-authoritative feedback while showing actual current
mode after refresh. Existing capability, nonce, escaping, busy controls and
single error notice remain; future UI changes require their focused source/UX
review and tests. This design does not claim browser/accessibility acceptance.

## Ordered implementation and proof gates

1. Next recommended repository-only foundation: implement the data-only observed
   map/revision/result contract and explicit error-safe handler result mapping
   with in-memory fixtures. Keep native persistence, new form wiring and actual
   option writes disconnected. Tests must not choose a live writer based on
   function_exists or treat the existing constructor override as a native store.
2. After that passes, separately approve a bounded Plugin Tester owned-option
   conditional-write/core-hook/cache qualification and native integration proof.
   Require fresh exact target/fixture confirmation, read-only preflight, owned
   fixtures only, bounded contenders and exact cleanup. Preserve real application
   options/users/cron and wiring until qualification succeeds. This is not
   authorized here; unsupported profile semantics must block connection.
3. Only then may an explicitly approved repository integration connect revised
   form/handler/legacy wrapper to the qualified native port. No unsafe parallel
   fallback. Live deployment remains a separate backup/approval boundary.

Do not create extra architecture slices to fill a batch. A failed compatibility
proof is a blocker/decision, not permission to weaken guards or introduce a
per-site migration, durable ledger or V2.3 work without approval.

## Regression handoff and current validation limits

Future permanent tests: different-/same-site contention, stale already-satisfied
intent, missing versus present-empty, no-op, malformed storage/revision, alias
normalization, first-creator race, SQL false/zero/one/impossible result, definite
failure/unknown acknowledgement, filters/reentry, cache failure and WP_Error
truthiness. Include positive save/no-op controls and exact unchanged non-target
maps. Removing the atomic predicate or the handler error branch must fail tests;
a refused/unavailable-only owner is not proof. In-memory ordering is not native
concurrency, option/cache/hook acceptance or a Database Review.

This turn added no code/tests or artificial success path. Existing related
isolated policy/classifier/request-boundary controls ran randomized seed20261027:
170 tests/351 assertions, no failures/skips. Structure passed (136 runtime,
204 support,162 test files). These controls do not prove the proposed writer.
All five relative links resolve; four primary-source web links were inspected.
Diff hygiene and unchanged non-document source identity pass. Published design
`ff9b68c77ead7e954560a91eaf02d1c7a83c8f9f` passed
[exact-head CI40568429183](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40568429183):
PHP7.4/8.3 each996 tests/4300 assertions without failures/skips; Node16/16 per
job, all configured build/structure/lint/manifest gates and both audits pass,
zero dependency advisories/vulnerabilities. Actual counts retrieved from both
job logs; clean remote master matched the design head. This evidence-only
closeout changes no tested source and does not qualify any proposed behavior.
No whole-plugin checklist refresh;
toolkit stays read-only. Whole07/07A/13/FULL remain incomplete.

## Implementation-plan progress and next gate

The remaining policy lost-update concern now has an explicit save/conflict
contract and compatibility/proof gates; it is NOT repaired by this document.
Payload guards and previous foundations retain their evidence, advisory locks/
best-effort logs/duplicate pending admission retain documented limits. No new
autonomous task count or release-readiness completion is inferred.

Recommended next gated slice: repository-only **Source-Policy Conditional Save
and Conflict Safety Foundation**, with in-memory observation/result tests and
explicit error-safe handler mapping only. Keep native writer, option/SQL writes,
new form wiring, production wiring and all site state disabled/unchanged.
