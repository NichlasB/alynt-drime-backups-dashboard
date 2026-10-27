# Diagnostics Batched Per-Site History Safety Foundation

## Authority and review boundary — 2026-10-27

The user approved the recommended repository-only Performance Review Phase 2
history slice through implementation, validation, commit, push and exact-head CI.
Baseline `f1dfccac8b1978779aa3502877fde3778991e19d` was clean and matched remote
master; that immutable commit is the scoped source restore point. No site backup,
site operation, SQL execution, schema change, package, tag, release or deployment
was authorized or performed. Uploader and shared toolkit remain unchanged.
This is bounded R2 read-only query orchestration: no auth, capability, privacy
projection, current-snapshot policy, retention or remote-action contract changes.
Physical qualification remains separately gated rather than inferred from mocks.

## Phase 1 inventory and approved disposition

The canonical 06 inventory scanned 138 runtime PHP files and 15 source assets,
then traced repository, Diagnostics, policy, rendering and polling hot paths.
Three Medium opportunities were established; no Critical/High defect or measured
production latency was established:

1. Diagnostics `attention_history_diagnostics()` issued one bounded history read
   per eligible non-archived site. Approved and addressed by this foundation.
2. Archived readiness issues three COUNT reads per archived site. Unchanged;
   exact readiness/count/failure semantics need a separate scoped decision.
3. Source Policy repeatedly sanitizes the entire stored map: a synthetic
   100-entry/100-lookup probe made 100 get_option calls and 20,100 sanitize_key
   calls. Core option caching may avoid SQL but not that repeated CPU work.
   Unchanged; freshness/invalidation must be designed before memoization.

The read-only inventory used isolated collaborators, not WordPress or SQL.
Aggregate action scans, snapshot retention derivation and sequential polling
remain profiling watch points, not confirmed missing-index defects. Page-gated
assets are minified (JS 3,614 bytes/CSS 12,961 bytes); no new actionable memory,
JS, eager-construction, HTTP or schema/index issue was established. No invented
EXPLAIN, timing, fleet size or load evidence. Canonical 06 remains in progress;
this one repaired item does not close the other findings or whole-plugin review.

## Strategy, semantics and trade-offs

`Snapshot_Repository::recent_by_site_ids()` normalizes and deduplicates IDs like
the existing bulk current reader, returning arrays keyed by requested site ID.
Empty/zero-only inputs perform no read. Each statement admits at most 50 sites
and at most 50 summary rows per site; Diagnostics continues to request 10.

For multiple sites, independently parenthesized SELECT branches each retain the
same summary projection, integer site predicate, observed_at DESC/id DESC and
prepared LIMIT as `recent_for_site()`. UNION ALL combines branches without
deduplication. A final site/observed_at/id ORDER BY is necessary: UNION does not
promise branch output order. The one-site tail uses the ordinary existing query
shape, not a second ORDER BY outside a parenthesized standalone SELECT. No window
functions, CTEs, correlated history-rank scans, schema/index changes or SQL writes.
Syntax rationale: [MySQL 5.7 UNION reference](https://docs.oracle.com/cd/E17952_01/mysql-5.7-en/union.html).
This reference is not physical compatibility or query-plan proof.

Returned rows may populate only their current batch's requested IDs. Diagnostics
filters archived/zero-ID sites before requesting histories and runs unchanged
aggregate logic over each site's bounded array. Single-site history, latest
highest-ID selection, retention SQL, archived readiness, guards and support output
contracts remain unchanged. No persistent/request cache or invalidation policy.

For N eligible unique sites, history round trips become ceil(N/50), rather than
N: 100 -> 2 (98 fewer/98%); 500 -> 10. SQL still performs N independently bounded
site lookups: no claim of eliminating that database work or measured speedup.
Diagnostics normally lists 100 sites; the list limit is not changed here. At 10
rows/site, one batch returns at most 500 rows; the public method cap permits
2,500 rows/batch. The complete result still holds up to N times the per-site
limit: bounded SQL batches trade modest aggregate memory for fewer round trips,
not streaming or a hard byte-size guarantee. No lower latency/MB number claimed.

Failed/null result batches leave their requested histories empty, matching the
old empty-history failure convention. Later batches are still attempted; there
is no per-site retry, stale cache or fallback to current snapshots. One failed
statement now affects up to 50 histories instead of one. As before, these
aggregate metrics do not independently distinguish storage failure from no
retained history. That observability limitation is recorded, not silently fixed
by changing output contracts in a performance patch.

## Regression and source-control evidence

New repository tests call the actual method with a query-capture wpdb double,
not a SQL interpreter. They assert projection, per-branch predicates/order/limit,
UNION ALL, final ordering, independent result grouping, normalization/deduplication,
1/50/51/100/500-site query budgets, 1..50 limit clamp, no empty-input reads,
batch failure continuation and rejection of rows belonging to another batch.
Chronological/tie fixtures are supplied ordered responses: their grouping is
tested, but actual SQL sorting/selection is explicitly not proved here.

New Diagnostics tests exercise actual collect(), with a repository double that
throws on the old single-site path. Positive 100-site recovery/transition counts,
mixed archived/zero/sparse/empty cases and separate ten-row windows cannot pass
with an always-empty history implementation. Existing aggregate/support tests
and all current-selection/retention regressions are retained unchanged. Only the
shared fixture history adapter gained the corresponding in-memory bulk method.

Before implementation: 14 repository cases errored on the absent method; two
Diagnostics cases rejected the actual old per-site path (one empty case passed).
This establishes missing functionality/caller regressions, not SQL execution.
The final focused randomized rerun seed20261027 passed 22 tests/135 assertions,
including 18 new tests/110 assertions, no skips/errors/warnings.

Three temporary isolated controls were assertion-killed and removed:

- Bind 5,000 instead of the requested 10: five query-budget cases failed, 25
  assertions. Initial attempts exposed a test-only missing ARRAY_A dependency;
  the new capture helper now defines that standard shim independently, and
  controls were rerun to assertion failures, not credited for bootstrap errors.
- Reverse the final tie-breaker to id ASC: one test failed after seven assertions.
- Bypass archived exclusion: one mixed-eligibility test failed at its first
  request-admission assertion.

Restored SHA256s:
`trait-snapshot-repository-reads.php`:
`475F8D266FD342E7095EB0664B759D25FAEE1D9E26A631FC7E469B8889622334`;
`trait-diagnostics-attention-history-metrics.php`:
`44CE87DB763A46EE99789573658319C9723795DD24C64020BF673B9E8A4F1EA0`.
Standalone new test files also run independently of full-suite load order.

## Validation, publication and limits

Local restored-source QA: full PHPUnit408 tests/2,174 assertions, two known
missing-Sodium skips; related randomized seed20261027 suite33/193, no skips.
Standalone new repository14/93; new Diagnostics4/17 (included in related/full).
`php ./vendor/bin/phpcs`, `npm run check:structure`, Node16/16 via
`npm run test:admin`, `npm run build`, strict Composer validation, npm audit and
Composer audit passed; audits report zero vulnerabilities/advisories.
Published source `4b17349b757bb38fc2c0061fd21cb54862fe3349` passed
[exact-head CI40488402447](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40488402447):
PHP7.4/8.3 each408 tests/2,181 assertions, no skips/failures; Node16/16 with no
skips/failures; all configured lint/build/structure/manifest/audit gates passed.
Actual counts were retrieved from both job logs, not inferred from local results.
Remote master matched the clean source head. Product progress advances5/8 ->6/8
for this one completed repository task; controls/tests/docs are not extra tasks.
This documentation-only closeout does not change the tested runtime or tests.
The build initially hit filesystem sandbox denial, then passed with
scoped repository access; generated assets are unchanged. No dependencies added.

Manual diff review covers trusted table construction, prepared integer values,
bounded predicates, summary-only projection, eligibility before reads, no writes
or remote calls, no cache/freshness weakening and PHP7.4-compatible syntax.
No canonical 03/04/05/07/07A/13/FULL completion or historical checklist refresh is
claimed. CI exercises isolated PHP/Node and static/build gates, not WordPress,
MariaDB/MySQL grammar, real query plans, concurrency, privileges or physical load.
Schema8/version0.1.77 and production wiring remain unchanged. Rollback is source
reversion of this bounded foundation; no data or schema rollback required.

## Exact resume gate

The following native gate was subsequently approved and completed; see the
[owned physical proof and current resume point](DIAGNOSTICS_BATCHED_HISTORY_OWNED_PROOF.md).
Historical requested slice: **Plugin Tester local-only Diagnostics Batched
History Chronology and Aggregation Owned Proof**. Require separate approval and
fresh exact target/owned-fixture confirmation before actual WordPress/database
operations, disposable snapshot fixtures and exact cleanup. Qualify real prepared
single/multi/tail queries, independent 10-row limits, timestamp regression/ties,
site separation, empty/failure behavior and actual aggregate equality. Preserve
all existing application state; no auth/users/lifecycle/cron/retention execution,
remote calls, production wiring, package or deploy. The remaining performance
findings are recorded, not bundled into this authorization or deferred V2.3 work.
