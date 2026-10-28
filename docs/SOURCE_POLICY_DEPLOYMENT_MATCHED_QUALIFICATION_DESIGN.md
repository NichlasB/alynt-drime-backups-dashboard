# Source-Policy Deployment-Matched Linux/Socket/Charset Qualification Design

## Current implementation checkpoint — 2026-10-28

The separately approved [fixed transport/charset foundation](SOURCE_POLICY_FIXED_TRANSPORT_CHARSET_FOUNDATION.md)
implements gate1 below using isolated API/IO tests only. Original design outcomes
remain historical; runtime/base-composition changes invalidate old native images
for current-source qualification. No native/provider/production grant follows.
The next approval is the off-production Linux environment preflight in gate2,
not another cleanup slice or a production connection attempt.

## Authority and baseline — 2026-10-28

Approved repository-only design through validation, commit, push and exact-head
CI. Clean/synced baseline `8daa4e7bb8fb7737e5b6ec70483e96d53f950c7c`.
R1 documentation for future separately approved R3 transport/session boundaries;
autonomous R2 is not raised. Version0.1.77/schema8/PHP7.4/WordPress6.0 unchanged.
Only this design and owning plan/forward records change. No runtime, tests,
manual images, dependencies, CI, assets, package, release, tag or deployment.
No site/SSH/LocalWP inspection, authentication, bootstrap, SQL/database/storage,
native experiment, fixture, account/permission/credential/service/schedule or
client/Drime action. Uploader/toolkit and local rollout tracker stay read-only.

Inputs: [production readiness](SOURCE_POLICY_PRODUCTION_CONNECTION_READINESS_DESIGN.md),
[actual ambient/report-OFF proof](SOURCE_POLICY_AMBIENT_REPORT_OFF_OWNED_PROOF.md),
[initial private bootstrap contract](SOURCE_POLICY_NATIVE_BOOTSTRAP_DESIGN.md),
[dormant adapter foundation](SOURCE_POLICY_NON_RECONNECTING_INTEGRATION_FOUNDATION.md).
Historical proof counts/images and whole-plugin review/task totals are preserved.

## Prior approved audit: observations are not a runtime grant

The immediately preceding separately approved/confirmed control-sitesmanage
live-only audit was static, non-secret and read-only. These are its recorded
findings, not a fresh site audit in this design. No source/configuration was
executed; no authentication probe, SQL, database connection or write ran. The
no-writes instruction also left the rollout tracker/repositories unchanged.

| Prior observation | Meaning and limit |
|---|---|
| Site root /var/www/control.sitesmanage.com/htdocs, static WP7.1.4, dashboard files0.1.77 | Installed-file metadata, not current plugin activation or runtime state |
| Site-specific Nginx socket route and pool point to PHP8.5; FPM binary8.5.12, Linux x86_64 | Configured web runtime; default CLI8.2.35 is a different binary/profile, not the qualification target |
| FPM ini enables mysqlnd/mysqli, empty auto-prepend/append | Static ini/pool facts, not proof of actual request extension state or callback closure |
| DB_HOST literal uses localhost with an explicit Unix socket; DB_CHARSET=utf8, DB_COLLATE empty | Non-secret config classification; no socket path/credentials are copied into this design or fixtures; negotiated state is unknown |
| Installed server binary reports MariaDB10.11.18 | No connected-server identity/version, SQL modes, schema, limits or autocommit was read |
| Eleven sampled core-library hashes match the earlier proof pins | Specific sampled bytes match despite WP version metadata; not complete distribution trust or a7.1.4/native eligibility grant |
| Default-content db.php/object-cache.php/advanced-cache.php/sunrise.php absent, WP_CACHE=false in main config | Does not establish actual core cache posture, absence of runtime filters, active plugin graph or multisite state |
| Main config has two site-local companions plus wp-settings include | Companions have no relevant allowlisted defines or further includes; none was executed; dynamic state is still unknown |
| One top-level MU source, wp-cli-login-server.php | Presence only; neither its activation effects nor any authenticated behavior was probed |
| Native adapter/owned-connection sources absent from deployed0.1.77 | This development work is not installed or wired on production |

The11 matched wp-includes files are class-wpdb.php, load.php, plugin.php,
class-wp-hook.php, class-wp-filter-sentinel.php, class-wp-error.php, cache.php,
class-wp-object-cache.php, formatting.php, functions.php and option.php. Exact
prior pins remain in the owning proof sources; current hashes never authorize
unknown files/callbacks by matching themselves.

## Decision: preserve core behavior; qualify the missing environment off production

Do not enable the provider, change wp-config, force TCP, convert table data,
replace core helpers, or broaden the current version/server guards to make this
audit look compatible. The actual deployed connection is unqualified. Keep the
legacy application behavior unchanged until the cohesive migration is separately
approved; no unavailable-everywhere replacement or unsafe fallback is selected.

The selected next foundation is a small extension of the existing private fixed
initializer/profile/evidence responsibilities, using isolated API/IO tests only.
It prepares one Linux/mysqlnd/Unix-socket candidate and core-derived charset
evidence. No generic transport/backend framework, connection broker, persistent
ledger, consent switch, public descriptor/factory, raw-SQL hook or new settings UI.
Native execution and public create()/production wiring remain closed throughout.

Current source seams were re-read:

- Bootstrap_Profile::settings accepts only core6.0/7.1.3, numeric loopback plus
  integer port, configured utf8mb4 and limited collate values matching constants.
- Native_Bootstrap constructs a host:port string and connects with socket=null,
  flags0; it and Native_Profile both require10.6.23-MariaDB.
- Genuine init/determine/set charset helpers already run. Bootstrap_Evidence
  separately enforces session utf8mb4/unicode_520 and exact table/index shape;
  the private setup has ordered steps,20s/16 command credits and2s/3s IO options.
- Windows manual sources/controller are separately pinned to their confirmed
  native runtime/loopback profile. They are evidence, not a portable provider.
- Actual factories remain unavailable; Source_Policy still has the old whole-map
  application writer. No transport design alone fixes that application race.

## Fixed trusted transport contract — proposed, not implemented

Keep immutable core configuration separate from observed own-session facts. The
future private frame has an explicit code-owned transport discriminator and
mutually exclusive endpoint fields; names here describe a contract, not an API:

| Selected profile | Native call arguments | Refusals |
|---|---|---|
| Existing loopback TCP | Frozen numeric host, explicit integer port, socket=null, flags0 | No hostname/default-port inference or automatic fallback |
| New Linux Unix socket | host=localhost, port=null, exact trusted absolute socket path, flags0 | No TCP conversion, default socket, remote endpoint, Windows pipe or p: persistence |

Use the known genuine core parser only after exact class/method/source binding;
validate its full output against the selected supported shape. The raw trusted
DB_HOST snapshot and parsed tuple must agree; malformed/ambiguous/trailing input
cannot acquire authority from a parser's partial match. The
[core parser](https://developer.wordpress.org/reference/classes/wpdb/parse_db_host/)
separates host/port/socket; [mysqli real_connect](https://www.php.net/manual/en/mysqli.real-connect.php)
accepts them separately. These primary API facts do not prove native transport
selection, timeout behavior or compatibility.

For the initial socket candidate, require Linux, a non-empty absolute path bounded
to96 ASCII bytes with no NUL/control bytes, no parent traversal or URI/drive form,
and no supplied port. That is a deliberately narrow candidate policy, not a claim
about every platform's maximum socket length. The actual prior audit did not
qualify its path bytes against this new policy. Do not truncate, concatenate an
arbitrary URI, resolve/rewrite a socket path as if it were a source filename,
search alternate sockets or grant access from file existence/stat alone. A later
owned environment uses its own confirmed path, never a copied production path.

Preserve actual core object initialization under final db_connect refusal; give
it the intact trusted core endpoint representation, not host . ':' . port for a
socket. The separately named private initializer still allocates exactly its own
single handle, never the ambient handle, and uses the fixed parsed tuple once.
No parent connector, reconnect/check_connection, retry, endpoint repair, SSL/
flags downgrade, init command, local infile, multi-statements or persistent pool.

Capture only from trusted core composition after existing authorization/intent/
record gates, never POST/options/fixture environment input in production. Secrets
stay private/transient and out of descriptors, revisions, logs and source images.
Report mode is shared context: preserve actual core-selected OFF, never toggle/
restore it. The qualified Windows strict/OFF rows remain distinct and unchanged.
The current runtime descriptor has no reporting-posture field; its native proof
checks do not implement the proposed production capture/recheck guard. The next
isolated construction must bind the expected posture from fixed trusted driver
observation and reject changes before allocation/dispatch, with post-submission
changes uncertain. No test-supplied mode value alone can authorize a session.

## Configured charset, negotiated session and stored schema are different facts

Select configured utf8 or utf8mb4 with empty or already-qualified collation for
the next candidate, rather than relabeling utf8 as utf8mb4 before validation.
Snapshot actual DB_CHARSET/DB_COLLATE unchanged. After the sole successful own
connect, invoke the audited genuine core helpers and compare their computed
charset/collation with all actual client/connection/result/collation readbacks.
The [core determine_charset source](https://developer.wordpress.org/reference/classes/wpdb/determine_charset/)
can promote utf8 to utf8mb4 and select a supported collation on a connected
handle. That is not evidence that production negotiated those values.

The next isolated foundation must positively reach the real orchestration for
configured utf8 -> helper-computed utf8mb4 -> matching session readback, retaining
the current qualified utf8mb4/unicode_520 session requirement. No rewritten
constant, copied core algorithm, guessed helper receipt or arbitrary collation
allowlist. Wrong helper result/native errno/readback remains unavailable. Prove
4-byte/quotes/backslashes/percent escaping later on the actual admitted owner,
not just a fixture serializer. Preserve bounded mode tokens and immediate error
checks before later commands can clear a native failure.

Configured utf8 does NOT establish table charset/collation/index semantics.
The real production options schema was not queried. Keep exact InnoDB/four-column/
full unique-name identity/autoload and existing unicode_520 table acceptance in
this first extension; do not remove schema checks, convert tables or broaden
column/index/collation shapes speculatively. If later approved metadata/owned
proof finds a different shape, stop for that specific compatibility decision.
Preserve binary/length CAS predicates, bytes/autoload/non-target data and revision
semantics; no DDL/migration or charset-based optimistic match.

## Version, server and release evidence policy

Prepare an explicit candidate row for WP7.1.4/PHP8.5.12/Linux/mysqlnd/MariaDB10.11
family, not a wildcard active grant. Separately audited source closure and later
connected-server build evidence must define the exact supported server row; an
installed10.11.18 executable is not that receipt. Do not accept version prefixes,
all MariaDB/MySQL servers, an arbitrary current hash or a caller profile ID.
Preserve existing10.6.23 behavior. Any later private-frame change must explicitly
migrate isolated fixtures/composition, not silently coerce an old shape. Changed
runtime/harness bytes stale the associated native image: fresh approved native
regression is required, including the preserved Windows/TCP lane. Old receipts
remain historical and cannot be recast as current-source qualification.

No setup-catalog/credit/elapsed increase is selected. Keep16 fixed command effects,
20s bootstrap,2s connect/3s read and fixed owned-session statement3s/lock1s limits,
ready-last, ordered step/latches, no ordinary dispatch before admission and
independent partial driver/result/callback cleanup. Later actual Linux/socket
tests must establish their real effects/readbacks/elapsed behavior; unsupported
options or extra work refuse, not silently fall back. A synchronous timeout is
not claimed to be an interruptible hard deadline.

ADBC1/ADBC2 and Windows receipts stay unchanged. A later Linux manual harness
needs its own exact image/process/run-bound evidence and independently observed
all-server live/post release, including NULL/foreign DBs and transactions, not
counter-wrap allowances learned from Windows or query-filter counters alone.
Do not build another relay, response-loss experiment, process-isolation toolchain
or general supervision service for this normal connection-compatibility task.

### Proportionate owned Linux lab candidate

The existing ci.yml uses ubuntu-24.04 with PHP7.4/8.3 isolated tests, not a native
MariaDB/socket proof. Prefer assessing an opt-in, dedicated ephemeral job on that
runner family before asking for a new workstation toolchain or touching any live
database. A future job would need separately approved PHP8.5.12/core/server image
acquisition and exact pins, private owned socket/storage, fixture-only credentials,
bounded process/SQL effects and unconditional exact cleanup. Nothing is installed,
downloaded, added to CI or dispatched by this design. Existing CI cannot be
credited as that environment's preflight or database proof.

[GitHub's service-container guidance](https://docs.github.com/en/actions/use-cases-and-examples/using-containerized-services/about-service-containers)
describes Linux runner requirements and job-scoped service lifetime; it does not
establish that our required versions/socket/cleanup tests work. Resolve those in
the explicit environment preflight before fixtures. Keep native jobs opt-in and
isolated from untrusted pull requests, permissions contents:read, no production
secrets, shared application database, privileged host mount or production network
target. If that candidate cannot meet the finite profile/bounds, stop for an
explicit owned-environment decision rather than installing Docker/WSL/compilers
or inventing a new supervisor. Minimum-profile native work remains distinct.

## Ordered qualification gates and non-vacuous acceptance

1. **Repository-only Source-Policy Fixed Transport and Core-Derived Charset
   Safety Foundation**: the preferred next separately approved slice. Extend
   private frame/profile/orchestration/evidence coherently with isolated API/IO
   tests only. Public factories, storage/native execution and wiring stay closed.
   Exercise actual mysqli argument positions and genuine-helper invocation seams,
   not only DTO validators. Existing Windows/TCP positives must still pass.
2. Separately approve preflight of an explicitly owned non-production Linux
   environment, preferably the bounded ephemeral CI candidate above. Resolve
   PHP8.5.12/mysqlnd, confirmed private socket,
   MariaDB10.11 build, pinned core files, principal/permissions and observer/client
   tools before fixtures. The environment is not assumed available on Windows;
   do not inspect production again, install/download Docker/WSL/compilers/PHP/DB,
   create accounts, change services or acquire credentials on design authority.
   Missing prerequisites are an exact blocker, not permission to improvise.
3. Under exact separate environment/fixture approval, run one bounded owned native
   qualification with fresh source/guard/no-fault prerequisites: actual bootstrap,
   session/schema/escaping, saved/noop/stale/creation/CAS, real1062/1064/1205/error/
   uncertainty, non-empty known hooks/core cache, ambient preservation and release.
   Independent baseline/cleanup covers all pre-existing application state; no
   production database, configuration copy, backup/restore or installed lifecycle.
4. Genuine minimum WP6.0/PHP7.4 source/runtime qualification remains its own row.
   No support-floor bump, dropped edit feature or presumption that new-current
   proof covers the minimum. Complete minimum/intended-profile gates before
   code-owned provider and cohesive authorized form/legacy migration. Real
   application hook/cache/authentication evidence remains separately required.
5. Canonical scoped07/07A/security and applicable Database/UI/i18n reviews, then
   FULL_PRE_RELEASE on the coherent release candidate before package/publish;
   separate backup/deployment approval for any live install. No historical
   whole-plugin readiness row is refreshed by this design or isolated suite.

Required adversarial controls: wrong socket tuple/port/platform/source/core,
NUL/oversize/persistent/remote/partial-parse path, bad charset/collate or changed
trusted context must refuse before allocation/protected reads. Positive source/
frame admission alone is never a native grant. Add active-state positives under
isolated IO, then remove/bypass each required transport/charset/readback/latch/
source guard and require assertion failure, restoring exact hashes afterward.
No startup failure counted as a killed guard and no unavailable-only acceptance.
Changed hooks/report/handle identity after submission stays uncertain; no retry,
reseal, manual READY/facts stamping or public handle/SQL return. Later native
controls require their own approval and cannot be claimed from isolated tests.

## Scope review, validation and outcome

Source-scoped design review covers trusted input vs observed evidence, transport
argument ambiguity, socket/source path distinction, raw vs negotiated charset,
unqueried schema/server assumptions, ownership/cleanup/bounds and supported-profile
regression. Source equality does not remove environmental qualification. No UI
workflow, Database Review, native success or production release readiness claimed.
Actual local validation on PHP8.5.1:

- Related ambient observer/receipt, native adapter, guarded entry, bootstrap and
  core-context suites343 tests/1,502 assertions, no failures/skips.
- Full PHPUnit --verbose --do-not-cache-result:1,885 tests/8,212 assertions,
  two existing Sodium-dependent signing skips, no failures. These isolated
  suites preserve current contracts; they do not execute the proposed support.
- npm lint (PHPCS220 files), test:admin16/16, check:structure (148 runtime,
  226 support,199 test files/196 class names) and build pass; no tracked asset delta.
- Strict Composer manifest validation and Composer/npm moderate-threshold audits
  pass, zero known advisories/vulnerabilities.
- Four local input links resolve; diff hygiene and byte identity against the
  baseline for runtime/scripts/tests/assets/CI/bootstrap/dependency files pass.
  Uploader/toolkit remain clean at unchanged heads; no rollout tracker write.

No tests, guard mutations or runtime changes were added for this docs-only task.
Public primary sources were consulted, not installed/site profiles. Design
`febfd585f02e1cc641d711565c10197d9a3ac806` is pushed and passed
[exact-head CI40775446488](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40775446488).
Actual PHP7.4/8.3 logs each1,885 tests/8,219 assertions, no failures/skips;
Node16/16 on both/all configured gates pass. Both check-run heads match. This
evidence-only closeout preserves non-document bytes and receives separate pushed
exact-head CI before handoff. Green CI does not qualify the candidate native row.

Implementation-plan progress: converts the approved static deployment audit into
a finite off-production compatibility/qualification plan. Production connection
and application lost-update repair remain gated; no historical task-count credit.
Recommended next action: approve the repository-only Fixed Transport and
Core-Derived Charset Safety Foundation above, using isolated API/IO tests only.
