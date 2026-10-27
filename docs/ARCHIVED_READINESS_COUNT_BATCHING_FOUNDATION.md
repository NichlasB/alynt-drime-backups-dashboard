# Archived Local-Removal Readiness Count Batching Safety Foundation

## Authority and scope — 2026-10-27

User approved this repository-only implementation through validation, commit,
push and exact-head CI, preserving exact counts, failure conventions and every
readiness rule. Clean/synced baseline751af4217fe647dc218131adc9ded62b08877ffc is
the scoped source restore point. R2 bounded query orchestration; no site/SQL
execution, schema/index/version0.1.77 change, native proof, application mutation,
credential/auth change, remote action, package/tag/release/deployment. Uploader
and shared toolkit remain unchanged. Production wiring is unchanged except the
approved Diagnostics read orchestration; no endpoint/action/lifecycle wiring.

Review boundary: that baseline through this foundation, including the two count
repositories, Diagnostics count caller/helpers, their existing test adapters and
new isolated regressions. No unrelated reorganization, class split or dependency.

## Strategy and preserved contracts

The confirmed06 opportunity issued three scalar counts per archived record:
snapshot rows, all action rows and non-terminal action rows. Diagnostics now
collects archived IDs before its metric loop and fetches three independent maps.
Each repository statement groups COUNT(*) by dashboard_site_id for at most50
deduplicated prepared IDs. No history-window limit or row truncation:20,000 rows
still means20,000, not10/50. Snapshot and action tables already have site-leading
indexes; no new index recommendation or physical query-plan claim is made.

`Snapshot_Repository::counts_by_site_ids()` preserves its scalar reader's signed
integer/zero ID semantics. `Remote_Action_Repository::counts_by_site_ids()` uses
the existing absolute/positive ID policy, with a fixed bool selecting total or
non-terminal counts. The latter retains exactly queued_for_dispatch, accepted
and running. Diagnostics resolves action-map keys by the same absolute policy;
snapshot-map keys retain the original integer. Missing groups are zero. These
are internal repository methods, not public routes or newly permitted actions.

Total actions and non-terminal actions are deliberately **not combined into one
SUM/count statement**: a failure in one must not erase the independent other
metric. Each failed grouped query falls back once per affected ID to its original
scalar reader. Later batches continue; no retry loop or persistent/request cache.
The scalar methods and their SQL remain unchanged. Complete scalar failure
retains the existing integer-zero convention, not a new fail-closed readiness
contract. This performance patch does not redesign that existing convention.

The error branch checks both a non-array result and nonempty wpdb last_error:
[WordPress get_results()](https://developer.wordpress.org/reference/classes/wpdb/get_results/)
can return an empty array on SQL failure as well as on a valid no-match read.
Valid empty results do not retry. A failed grouped query cannot silently become
zero when its legacy scalar query could still retrieve the count. Query errors
remain private; no SQL/error text enters support output.

For A ordinary positive archived IDs, healthy count statements fall from3A to
3*ceil(A/50):100 records300 ->6 (98% fewer). Worst-case all grouped failures add
at most3A scalar reads:306 at100 records, not an unbounded retry storm. These
are source/isolated call-budget observations, not physically measured round
trips or latency. Exact COUNT work still depends on retained matching rows.
Result memory is three small ID/count maps, not raw histories/action contexts.
No atomic cross-table point-in-time or concurrent-state consistency is claimed;
the old independent counts also did not provide one.

Only archived records request counts. Empty eligible sets access no repository;
empty direct repository requests need no database collaborator. Existing
repository availability/no-database guard is retained. The readiness-reason
method body is unchanged (source comparison returned true). It still checks
terminal/expired-pending state, the existing paired polling-credential presence
rule, action credentials, scheduled poll, paused state and non-terminal rows in
the original order. No stricter or weaker credential rule was introduced.
Site Detail previews, deletion handlers, their scalar reads, all mutation guards,
history chronology/current selection and retention SQL are untouched. No raw
payload/credential/context columns or persistent caches are added.

## Regression evidence and limits

New RepositoryCountBatchTest exercises actual repository methods over a wpdb
query-capture collaborator, not a SQL interpreter. It checks scoped COUNT/GROUP
predicates, exact non-terminal state set, no history limit, normalization,
deduplication, empty keys/inputs,1/50/51/100/500-site budgets, foreign-row exclusion,
failed-batch scalar recovery/continuation and independent zero conventions.
The collaborator explicitly models ARRAY_A empty-on-error plus last_error,
instead of assuming every database failure returns null.

New DiagnosticsArchivedCountBatchTest calls actual collect() with in-memory
repositories that reject scalar calls on the healthy path.100 archived records
produce200 snapshots/400 actions/10 non-terminal rows,90 ready/10 blocked, excluding
a visible record with1,000 fixture rows.13 readiness/presence/expiry cases assert
both the exact existing reason and full numeric aggregate. Empty/visible-only
inputs and signed/zero ID policies are covered. Existing local-removal, support,
admin scalar-preview and history tests/expectations remain unchanged.

Initial repository13 cases errored on the absent API; three of four Diagnostics
cases rejected the actual old scalar path (empty input passed). Before adding
the last_error guard, all three count-kind error cases failed meaningfully:
actual0 versus expected recovered8. They pass after the repair. Positive20,000,
fallback1..50, recovered8 and nonzero aggregate cases prevent an always-zero
implementation from satisfying the suite.

An optional temporary runtime predicate mutation was rejected by the safety gate
before application because it would change preserved readiness semantics. It was
not retried or bypassed; no runtime mutation-control success is claimed. Runtime
hashes were checked unchanged after the rejection. Isolated predicate/admission
assertions and the actual red-to-green error cases remain the evidence, not a
claim of physical state-membership or database-failure proof.

Final scoped hashes:
snapshot read trait74AA730BBFE0D0476BF934C509639394510E26BF6839550E496EDBB5355D862D;
action lookup traitCE502A6500313138A65EA4B40D165B1386D5BF9E4CDE8A6FD2672ED15C5824AA;
Diagnostics removal helper155ADF6996E0DDBBD0820714FA72D426989F84F9C0E564470872448D1197C811.

Full local PHPUnit432/2,858 with two known missing-Sodium skips; related randomized
seed20261027 suite23/668 no skips. New21 cases/648 assertions. Lint, structure,
Node16/16, build, strict Composer validation and npm/Composer audits passed;
zero vulnerabilities/advisories, generated assets unchanged. Published source
9168b9a965cf4c0be3cad9c7794c69cd2219c8c2 passed
[exact-head CI40523417104](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40523417104):
PHP7.4/8.3 each432 tests/2,865 assertions, no skips/failures; Node16/16 and all
configured gates. Counts/audit outcomes were retrieved from both job logs;
remote master matched the clean source head. This record-only closeout does not
change tested code; CI did not execute SQL/WordPress or the future native proof.
No test expectation was
lowered to obtain green; initial key-only/cipher-only fixture expectations were
aligned with the existing paired credential rule before green validation.

No actual WordPress/SQL/site operations or native rerun were performed. Prior
history proof remains dated evidence for its original scope; adding methods
changes the enclosing file hash and does not qualify these new grouped counts.
No whole03/04/05/06/07/07A/13/FULL, Database Review, transaction/atomicity, privilege,
browser or production-load completion is claimed. Rollback is source reversion;
there is no data/schema rollback. PHP7.4 and existing architecture are retained.

## Exact resume point

Product progress is now **7/8** after exact-head CI success. Canonical06
remains in progress; source-policy repeated sanitization is still open.
Recommended next gated slice: **Plugin Tester local-only Archived Readiness
Grouped Count and Scalar-Fallback Owned Proof**. Require separate approval and
fresh exact target/fixture confirmation for two schema-only snapshot/action
fixtures, real per-site/grouped count equality including all three states,
controlled grouped-read failures with scalar recovery/zero convention, actual
aggregate equality and exact cleanup/preservation witnesses. No real site rows,
users, lifecycle, cron, retention, action execution, wiring or deployment.
