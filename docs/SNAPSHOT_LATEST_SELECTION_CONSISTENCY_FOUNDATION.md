# Snapshot Latest-Selection Consistency Safety Foundation

## Authority, scope and baseline — 2026-10-27

The user approved repository-only implementation, validation, commit, push and
exact-head CI, preserving chronological history, retention SQL, schema and guards.
Target is this dashboard repository, not Plugin Tester or any live site. Baseline
`bdde4daf37ec65fe34ad89117c9df3d6b07be7ec` was clean and matched remote master.
Risk is the explicitly approved R3 capability trust-input policy correction,
not permission to run an action, change a site or raise autonomous authority.
Uploader/shared toolkit remain read-only. No package/release/tag/deployment.

External full-folder OptionB restore ZIP/sidecar:
`C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots\alynt-drime-backups-dashboard\alynt-drime-backups-dashboard-20261027-095756.zip`
and matching `.txt`;7028 files, baseline clean. SHA256:
`B718CE37EA410E1C4337B8E08B3375841817E77BA4EADE7049B3156705780C40`.
This is source-folder safety, not a site backup, installable package or restore.
No checkpoint branch or tag was created.

## Confirmed defect and approved decision

`includes/traits/trait-snapshot-repository-reads.php::latest_for_site()` formerly
selected observed_at DESC/id DESC. Fleet `latest_by_site_ids()` and retention
protection select MAX(id). With one site's id100 observed August2 and id101
observed August1, the detail/action reader chose100 and fleet chose101. With both
older than cutoff, retention protected101 and could remove100. Timestamp
regression is an example trigger; no actual site incident was alleged.

Current means **highest stored ID within the selected site**, matching the
existing fleet/retention policy. It does not mean largest timestamp, client-side
event chronology, transaction commit order or atomic cross-request observation.
The runtime diff is only the current-reader docblock and ORDER BY id DESC.
Its fixed table, prepared integer site predicate, LIMIT1, return/null behavior
and JSON decoding are unchanged. `recent_for_site()` still uses observed_at
DESC/id DESC and summary fields; retention SQL and all schema/indexes unchanged.
Existing `site_latest (dashboard_site_id,id)` supports the selected key shape;
no new physical EXPLAIN/load/performance evidence is claimed.

Direct consumers reviewed: Site Detail, Sites list, Diagnostics/support and
`trait-remote-action-dispatcher-intent.php::latest_capabilities()`, including its
scan/schedule-preview/apply/rollback-preview/cleanup-preview call sites.
No consumer, public endpoint, auth/nonce/key/capability/origin/signature/transport
guard, action type, schedule, version0.1.77 or schema8 was changed.

## Regression plan and actual red/green evidence

Tests call actual repository methods; capability cases call the actual public
dispatcher. The one small query-sensitive wpdb row model recognizes the narrow
existing SQL selectors, binds site values and models both old/new orders. It is
not a SQL parser, database engine, wpdb integration or transaction proof. Existing
crypto, DNS, HTTP and write doubles are reused; no actual network/storage.

| Test group | Protected behavior | Evidence quality / limit |
|---|---|---|
| SnapshotLatestSelectionTest three timestamp cases | Regressed/tied/advanced timestamps select101; equality with fleet, decoded payload, unrelated site999 excluded, integer binding and LIMIT1 | Strong isolated selector contract; SQL grammar asserted, not physically executed |
| Missing site / zero / bad JSON | Null for absent selected site, no foreign-row fallback, malformed JSON becomes empty DATA | Isolated existing return/decoder contract, not an authorization check |
| Chronological history | Current101 versus history100/101; selected site, bound limit, payload omitted | Existing history behavior preserved, no browser rendering claim |
| RemoteActionSnapshotSelectionTest six transitions | New disabled/no-actions/bad/scalar report blocks; rotated key blocks; new enabled report can pass existing guards | Actual dispatcher + repository; fake HTTP invoked once only on accepted positive, zero fake writes/HTTP on rejection |

Before runtime repair, the initial8 new cases produced7 failures/15 assertions:
old enabled reports were accepted despite newer disabled/rotated reports; old
disabled reports blocked newer enabled ones; current/history counterexamples and
query-policy assertions failed. This was behavioral red evidence, not a syntax,
fixture or bootstrap error. After the repair and three corruption/no-actions
cases were added, new11 cases plus existing3 snapshot cases pass14/71.
No old test or expectation was removed or weakened. Existing history/retention
tests remain, protecting distinct contracts; no consolidation justified.

Two bounded socket-free source controls:

- Bind the current query to foreign site7:1 test/1 assertion fails (999 !=101).
- Remove LIMIT1:1 test/3 assertions fails the required bounded-query assertion.

Both controls were removed. Exact runtime-file SHA256 before/after controls:
`422BFFD4A0A0E146BE4268B1CE5BBBD6493442A6A6DD4F38D4F4011B85B99A37`.
Randomized focused rerun seed20261027:14/71 green, no skips/errors/warnings.
Original temporal-order red evidence also rejects reversal to the old selector;
positive repository and accepted dispatcher cases prevent always-null/always-deny
implementations passing. Controls are isolated contract evidence, not native
failure/concurrency/authorization proof.

## Canonical source-scoped reviews

Explicit Git review baseline: `bdde4daf37ec65fe34ad89117c9df3d6b07be7ec` through
this foundation, not the previous proof or its last documentation commit alone.
Changed-code scope: one runtime trait, two test classes, one test-support double;
owning records plus the direct callers listed above. Toolkit canonical prompts
were read; no shared checklist or historical whole-plugin row is refreshed.

FEATURE_LIGHT_REVIEW: four code files reviewed, DB/remote-trust input touched.
One Medium selector inconsistency fixed under the approved decision; no other
significant non-security issue or new manual decision. Naming/global-class
architecture preserved, no dependency or UI/string change. Empty/JSON behavior
unchanged; one bounded query, no loop/new cache/storage. No bloat follow-up needed:
one109-line cohesive shared row model and two small focused test classes; no new
runtime abstraction or mechanical source split. No UI/UX workflow applicable.

Scoped07 Edge Cases review preceded scoped07A test-quality reconciliation:

- Medium confirmed date-regression/current-selection candidate: rare clock/order
  divergence, potentially stale capability input; prevent with highest-ID policy.
  Permanent isolated cases listed above; real query/installed/runtime acceptance
  remains outside this approval.
- Empty/zero/foreign site, ties/forward time and malformed/scalar payload cases:
  existing null/empty/fail-closed guards retained, explicit tests now protect them.
- Concurrent insert/delete is not made atomic; MAX(id) is not a transaction-order
  guarantee. No claim of permission/auth/request routing, browser, core-API,
  multisite, native resource or physical lifecycle qualification. Those broader
  categories are outside this source scope, not blanket N/A release acceptance.
-07A finding SS1 (missing cross-selector regression) repaired; row model is useful
  but limited, outcome+query assertions and red/control evidence credible at the
  isolated layer. No test UUT mocked, no false SQL/WordPress proof, no coverage-
  percentage claim, no framework/CI exclusions or added skips. Known Sodium skips
  are unrelated environment limits. No redundant test removed. Native handoff
  stays separate; full whole-plugin07/07A/05/13/FULL remain pending.

Final FEATURE_SECURITY_REVIEW after validation: one Medium stale trust-input
consistency defect repaired, isolated regression disposition described above.
Prepared values/site ownership, fixed table/column/order/limit, payload decoding
and unchanged downstream authorization/key/signing checks reviewed. No new
request input/output/file/nonce/auth surface or sensitive persistence; no new
security issue found in this scoped diff. Genuine request authentication and
physical SQL are not established by these tests. Full final13 is still required
for the next coherent release candidate; no release readiness inferred here.

## Local validation and publication

- `php ./vendor/bin/phpunit --verbose`:388 tests/2039 assertions, no failures/
  errors/warnings/risky tests; same two pre-existing unavailable-Sodium skips.
  PHPUnit9.6.35/PHP8.5.1; initial unsupported `--display-skipped` invocation ran
  no full suite and was replaced by the supported verbose command, not hidden.
- Focused selection/capability/history/retention:14/71, no skips; same randomized
  result. Syntax checks passed for all four changed/new PHP files.
- `npm run lint`:155 runtime/manual PHP files passed. Tests are intentionally
  excluded by existing PHPCS configuration; do not mislabel this as test PHPCS.
- `npm run test:admin`:16/16, no failures/skips. Build passed, no tracked assets
  changed. Structure:136 runtime,196 support,147 test files;145 class names pass.
- Strict Composer manifest, Composer audit and npm moderate audit passed,
  zero vulnerability advisories; diff check passed. No dependency/config change.

Publication and exact-head CI pending. No site/SQL/WordPress operation, auth,
native proof, retention execution, package, release, tag or deployment ran.

## Resume and proof limits

This fixes the repository selection policy, not all of Database Review. Continue
the already-authorized canonical05 repository-only inventory/finding reconciliation
before06. Keep historical schema/lifecycle/poll/retention receipts at their actual
source heads; the previous214/9 physical retention proof does not qualify the new
single-site SELECT or installed capability path. Any needed owned database/runtime
qualification requires separate scope approval and exact target confirmation.
Shared toolkit evidence publication remains a separate approval boundary.
Product progress becomes5/8 only after push/exact-head CI; do not count restore
ZIPs, evidence docs, organization or previously separate native proofs as tasks.
