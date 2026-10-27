# Retention Cleanup, Latest-Snapshot Preservation and Failure-Reporting Owned Proof — 2026-10-27

## Authority, baseline and non-goals

User approved the proof and freshly confirmed `plugin-tester`, `local-only`,
LocalWP “Plugin Tester”, http://plugin-tester.local, WordPress at
`C:/Users/Captain/Local Sites/plugin-tester/app/public`, database local on
127.0.0.1:10004. Novamira HQ guide/complete inventory resolved that exact profile;
no WordPress Ability ran because isolated core/owned SQL is the appropriate layer.
No live target is configured for this profile.

Clean/synced source baseline `360961447da75b775d6f1954252d08188546c95a`, master.
All prior organization is closed. This adds only explicitly gated manual proof
code, socket-free manifest tests and owning evidence. Production PHP/root loader,
schema8/version0.1.77, installed lifecycle, application options/users/cron,
credentials, schedules, client/Drime and wiring remain unchanged. No application
cleanup, actual polling, backup/restore, migration, native V2.3 experiment,
package/release/tag/deploy or service/configuration change.

External source-folder restore point, not a site backup/package:
`C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/alynt-drime-backups-dashboard-20261027-085022.zip`,
matching .txt sidecar,6997 files, SHA256
`FF6DB31FC2A46028DEAEABD8810A514BF8EA8F869031096C47EF027182033A3C`.
No source restore or checkpoint branch applied.

## Genuine core/storage and containment

LocalWP's bundled MariaDB10.6.23 client performed the initial read-only database/
port/version check, complete77-table inventory and77 physical EXTENDED checksums
before PHP database access or fixture creation. The final independent bundled-
client inventory/checksums match all77 originals with no extra tables.

Installed WordPress7.1.3 core libraries and bundled PHP8.5.12 execute the current
root loader, constructor, repositories, callback, hook API and diagnostic option
APIs unchanged. No alternate cleanup implementation, fake wpdb, injected logger
or WordPress function shim supplies results. The site php.ini preflight found
mysqli/mbstring available but an unrelated missing Imagick startup DLL. The proof
uses `-n` with explicit mysqli/mbstring and argument-hidden exceptions: a minimal
private CLI profile, not the site/FPM profile. No configuration was edited or
error globally suppressed. Expected server errors use wpdb's normal false/error
contract with printing disabled and outcomes asserted.

No wp-config/wp-load/wp-settings, installed plugins/themes/drop-ins, application
authentication, requests or application shutdown hooks are bootstrapped. Genuine
private core memory cache/POMO catalogs supply UTF-8/empty translations. Fixed
URL constants are private loader support, not HTTP calls. Private memory hook
registration/dispatch is genuine core, not an actual scheduled wp-cron request.

CLI entry requires the exact confirmation flag, `adbrt_<12hex>_` namespace,
closed preflight/run modes and inherited MYSQL_PWD (never persisted or passed
in argv). The flag is not human approval; Site Operations remains mandatory.
Three absent-before-start tables are schema-only clones of wp_options and the
two existing snapshot/action tables; no application row is copied. The clone
statements read only schema metadata of those fixed sources. All ordinary reads,
writes, failure injection and DROP target the exact owned tables. Private session
statement/lock budgets are bounded; no global database setting or account change.

Generated-SQL manifest checks fixed clone/metadata/DROP forms and ordinary owned
targets; rejects application/mixed targets, multi-statement/comment escalation,
multi-target mutation, GRANT, TRUNCATE and global settings. Quoted values are
masked only for lexical inspection so serialized fixture strings are not confused
with targets. This is a first-party assertion, **not a general SQL firewall or
database privilege boundary**; the connection is not a new restricted account.

Maximum fixture rows are5003 per data table, bulk INSERTs at most1000 rows.
Cleanup previews every created table, refuses unexpected counts over6005,
removes only creation-acknowledged manifest members, and verifies absence with
successful metadata reads. Shutdown/finally cleanup is idempotent; independent
client reconciliation is still required. No concurrent-process proof is claimed.

## Physical results

Fresh preflight:9 checks/3 owned absence reads, zero fixtures created. Successful
run: **214 checks / nine groups**,344 observed owned queries,0.877 seconds;
three tables removed and all77 original table checksums unchanged.

| Group | Actual evidence |
|---|---|
| Ownership/core/schema | Exact absent namespace, genuine root/core and InnoDB schema-only clones. |
| Snapshot retention | Oldest eligible rows/batch ordering; highest ID per site survives, including backdated newest ID and a sole ancient row; repeat zero and retained payload bytes. |
| Action retention | Old completed rows removed by completed_at/id; incomplete and recent rows survive despite ancient requested_at; repeat zero. |
| Strict cutoff/lower clamps | Observer seeds exact original prepared-cutoff and one-second-earlier owned rows before the original DELETE; only earlier row removed; lower day/batch clamp1 observed in actual SQL. No virtual PHP clock or replacement query. |
| Upper limits |5002 old rows per kind, snapshot latest sentinel; exactly5000 deleted, exact remainder preserved,365-day clamp observed in real prepared SQL. |
| Current callback/defaults | Actual constructor and private core cleanup hook; each original batch once, default500/30-day snapshot and500/90-day action limits, no success event. |
| Physical errors/recovery | Each and both DELETEs receive an intentional server syntax fault, not a fabricated WP_Error; other batch still runs. Safe exact events persist through genuine options; non-autoload buffer, empty contexts, no raw fault/table data; healthy recovery keeps history without noise. |
| Diagnostic policy | Disabled/critical-only settings suppress reporting after real errors and leave physical fixture option rows unchanged. No automatic opt-in. |
| Option failure | Intentional server fault in the genuine event write; both deletes precede one logging attempt per error, no recursive retry/fallback or fabricated persistence; unrelated owned canary survives. |

Latest means MAX(id), not maximum observed_at. Old incomplete action preservation
is the existing policy, not a new purge decision. Reporting remains opt-in;
durable events cannot be guaranteed while option storage itself fails.

## Harness correction and non-vacuous regression protection

The initial owned attempt stopped at wpdb's backtick-quoted SHOW FULL COLUMNS
metadata form, before the first snapshot INSERT. The narrow manifest admitted
only the unquoted form. All fixtures were removed and the independent77-table
inventory/checksums matched before proceeding. Only that exact owned metadata
variant was added; application metadata remains rejected. A fresh namespace and
fresh zero-fixture preflight preceded the successful full run. No production
code, guard expectation or assertion was relaxed.

Permanent socket-free manifest scope:2 tests/22 assertions, positive nested
retention/quoted data/schema metadata plus application/mixed/escalation negatives.
Two temporary guard-removal controls were assertion-killed: omit owned-prefix
validation (1 failure), omit target membership (1). These are manifest test
controls, not native/transaction controls. Source SHA256 restored exactly to
`7E7F618FE587BE646E2F44F65CDF23144143444CB546DF2FACE27BDA0A78B881`;
2/22 passed again. Missing CLI authorization flag refuses before core/connection.
Positive deletion and positive physical logging mean an always-unavailable
owner/logger cannot satisfy this proof. Existing9/67 reporting regressions remain.

## Focused reviews and validation

Explicit baseline360961447da75b775d6f1954252d08188546c95a. Scope: six manual
proof files, one permanent test, directly affected current runtime contracts and
owning evidence. Canonical FEATURE_LIGHT_REVIEW: risk owned DB/file/core-loading;
one harness metadata compatibility issue found/fixed, zero unresolved/manual
decisions. Mechanical PHPCBF/Yoda/name fixes are separate. Feature passes light
review; no significant unresolved non-security issue. No new runtime architecture
or duplicated production cleanup; cohesive manual entry/core/manifest/fixture/
batch/reporting responsibilities. Further bloat splitting unnecessary; no UI/UX.

Final FEATURE_SECURITY_REVIEW after validation: no introduced persistent security
issue found. Human/CLI/namespace boundaries, exact owned identifiers and prepared
values, inherited non-emitted credentials, suppressed raw SQL printing and exact
cleanup remain intact. Existing route/auth/nonce/privacy contracts untouched.
Regression handoff: metadata compatibility and scope gates have permanent API-
level tests; no confirmed security defect requires a new coverage candidate.
Source-scoped review only, not whole-plugin13 or release security acceptance.

Local baseline375/1959 and final377/1981 retain the same two missing-Sodium
signing skips, no failures/errors. New2/22 has no skips. Node16/16, PHPCS155,
structure136 runtime/195 support/145 test files, build, strict Composer manifest,
Composer/npm audits (zero advisories), syntax and diff checks passed. No tracked
asset changed. Native proof is deliberately outside CI/application/package paths;
CI executes only isolated tests/lint/Node/build, never this local SQL command.
Published proof head `cde65b8f46d766000fc0867a79c0c3651eae2900` passed
[exact-head CI40475267406](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40475267406).
Actual logs: PHP7.4/8.3 each377 tests/1988 assertions, no skips/failures;
Node16/16, fail0/skip0, all configured gates successful. CI does not rerun SQL.
This evidence-only follow-up has its own exact-head verification at handoff.

## Proof limits and resume

This is genuine owned MariaDB/wpdb/option persistence and private core callback
proof, not production cleanup, full cron scheduling, installed WordPress request,
authentication/browser/multisite/minimum-WP acceptance, concurrent insert/delete,
transaction atomicity or proof of every retention volume. It does not refresh
historical whole-plugin/lifecycle/native/package rows. Existing V2.3 stays parked.
Shared toolkit and uploader are not modified. No whole canonical05/FULL completion
is inferred solely from this scoped proof.

Publication is verified. The existing **canonical05 repository-only
inventory/finding reconciliation** resumed before advancing to06. The High poll fence has
its separate269/8 receipt; the Medium retention finding now has this narrow physical
qualification. Complete/check the canonical inventory rather than inventing more
retention tests or another native experiment. Any new schema/policy/site/publication
boundary still needs its own approval. Product progress stays4/8: this separately
approved native proof and its evidence closeout are not extra safe-local tasks.

### Reconciliation found a new policy gate

Read-only source inspection at the published head found inconsistent definitions
of current snapshot: `traits/trait-snapshot-repository-reads.php:34` orders the
single-site lookup by observed_at/id, while its fleet lookup (line71) and
`traits/trait-snapshot-repository-retention.php:42` choose MAX(id). Paths are
relative to includes/. Existing `tests/SnapshotRepositoryTest.php:43,84`
independently assert these different selectors, not their agreement.
Site Detail consumes the former (`admin/traits/trait-admin-page-site-detail.php:28`);
fleet/Diagnostics consume the latter. The former also supplies remote-action
capabilities (`traits/trait-remote-action-dispatcher-intent.php:32`).

Source-backed pure row model (no SQL/WordPress): for one site, id100 observed
2026-08-02 and id101 observed2026-08-01, detail selects100, fleet101. With cutoff
2026-09-27 both are old: existing retention deletes100 and protects101. Detail
then changes to101 without a new observation. This requires out-of-order stored
timestamps, e.g. clock regression; no such condition was alleged on a real site.
The physical proof correctly qualified MAX(id) preservation, not agreement with
every consumer. No database experiment or security bypass was demonstrated.

Medium correctness/trust-input consistency finding remains open. No selector,
capability guard, runtime query or test expectation was changed. Canonical05 is
not closed; its complete inventory/evidence reconciliation remains unfinished.
Recommend separately approving **repository-only Snapshot Latest-Selection
Consistency Safety Foundation**: use highest ID for single-site current lookup,
matching fleet/retention; preserve chronological history, retention SQL, schema,
guards and remote execution. Add isolated out-of-order/tie/empty/site-isolation
and capability-consumer regressions, then scoped reviews/local QA/push/exact-head
CI. Any genuine database qualification is a separate gate. The current approval
does not authorize this changed remote-action trust input.
