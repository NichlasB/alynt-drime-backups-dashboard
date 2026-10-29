# Minimum-Core/PHP 7.4 and Current-Source Windows/TCP Qualification Design

Subsequent approved gate1 implementation/evidence and current resume point:
[Supported-Profile Source Rebaseline and Isolated Harness Safety Foundation](SOURCE_POLICY_SUPPORTED_PROFILE_FOUNDATION.md).
The historical design/QA evidence below remains scoped to its original heads;
new proof-source images have no transferred native qualification.

## Authority, baseline and decision — 2026-10-29

Approved repository-only design, R1 documentation work. Clean/synced restore and
review baseline: `9535e51128117ce605f6f011b862969b902f09d5`. This design closes the
planning gap after the [Linux owned qualification](SOURCE_POLICY_LINUX_NATIVE_QUALIFICATION_OWNED_PROOF.md),
not the remaining native evidence gaps. No native execution, installed-source
inspection, SQL, authentication, fixtures, accounts, permissions, workflow
dispatch, dependencies, runtime/acceptance changes, production wiring or release.
Uploader/toolkit remain untouched. Version0.1.77/schema8/PHP7.4/core6.0 support
declarations remain unchanged. No additional historical safe-local task credit.

Decision: retain **two separate qualification lanes**. First prepare one bounded,
repository-only source-rebaseline/isolated-harness foundation, then separately
authorize audit-first owned qualification for each lane. Do not rerun the old
Windows controller unchanged or transfer Linux success to either lane. If an
exact candidate is unavailable, stop for an explicit candidate/design decision;
do not install/alter LocalWP, lower guards or raise declared minimum versions.

## Evidence boundary and finite candidates

| Lane | Candidate, not environment authority | Evidence at this baseline |
|---|---|---|
| L: pinned Linux | PHP/mysqlnd8.5.12, core7.1.4, Linux Unix socket, exact `10.11.18-MariaDB-ubu2204` | Native282/9 atb0717fc, [40864997754](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40864997754); exact image/cleanup only |
| W: fresh Windows/TCP | PHP/mysqlnd8.5.12 candidate, genuine core7.1.3, numeric loopback TCP, exact `10.6.23-MariaDB` | Historical [strict211/8](SOURCE_POLICY_FIXED_ADAPTER_NATIVE_OWNED_PROOF.md) and [ambient OFF367/9](SOURCE_POLICY_AMBIENT_REPORT_OFF_OWNED_PROOF.md); **not** current-source qualification |
| M: genuine minimum | PHP7.4.33/mysqlnd candidate, genuine core6.0, numeric loopback TCP, exact `10.6.23-MariaDB` | Isolated PHP7.4 CI only; genuine minimum-core/native proof remains absent |

These are selected compatibility cases, not all PHP/core/server permutations or
production grants. TCP permits core6.0/7.1.3 on numeric127.0.0.1 or::1; only
Linux/core7.1.4 permits the socket tuple. Read
[`Transport_Profile`](../includes/class-source-policy-transport-profile.php) and
[`Bootstrap_Profile`](../includes/class-source-policy-bootstrap-profile.php):
12 exact settings keys, no discovery/fallback/prefix match. Do not relabel7.1.4
as7.1.3, strip a MariaDB distribution suffix or add minimum-core socket support.
The TCP predicate itself does not constrain OS; a Linux minimum lab is possible
only after its complete exact server/build/transport candidate qualifies.

PHP7.4.33 is the final unsupported7.4 release, selected for an isolated legacy
compatibility experiment, **not** a secure deployment recommendation. Current
binary availability, architecture, runtime dependencies, image/digests and owned
server availability remain unknown; no installation is authorized. An arbitrary
MariaDB container must not be assumed to expose the exact unsuffixed server row.

## Source ownership and real compatibility deltas

Official tag refs resolved read-only through GitHub on2026-10-29:

- Core6.0: `cc101b64012b16d087780657a2b828ccd7794a63`;
  genuine [`wp-db.php`](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/wp-db.php)
  declares `wpdb`; [`functions.php`](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/functions.php)
  declares `absint` and includes `option.php`.
- Core7.1.3: `fc9832bef919c2a08541248db23af90d14ffb082`;
  [`version.php`](https://github.com/WordPress/WordPress/blob/fc9832bef919c2a08541248db23af90d14ffb082/wp-includes/version.php)
  states7.1.3; [`load.php`](https://github.com/WordPress/WordPress/blob/fc9832bef919c2a08541248db23af90d14ffb082/wp-includes/load.php)
  declares `absint`. Bind the complete selected distribution, not just shared
  file hashes or a private version label. API reads did not execute core.

The old Windows
[`bootstrap proof`](../scripts/manual/source-policy-bootstrap-owned-proof.php)
already has the12-key transport frame; it is **not** missing transport/socket.
Its parser binding still names `functions.php` despite its7.1.3 frame, and its
fixed pins/installed-path assumptions are not a new authenticated source image.
Audit the intended full distribution before replacing pins; never rehash an
unknown installed tree to grant itself provenance. Preserve old receipts.

Required source-only rebaseline inventory:

- Bootstrap/adapter/ambient workers and their controllers, proof cases, cleanup
  process and source-image gates: explicit fixed lane composition, never a new
  provider/backend registry or caller-selected native executor. Follow direct
  includes and reflected declarations, including cache/load/parser/hooks,
  formatting/option helpers and every actual transitive dependency. Unknown
  dependency or different method origin refuses before allocation.
- Minimum `wpdb` file differs from new-core `class-wpdb.php`; cache/parser and
  option/serialization/collation/helper closure must be independently audited.
  The native profile already selects minimum autoload `no`/states yes,no versus
  newer off/expanded states; prove those exact branches, not copied expectations.
- Test-only constructor/property reflection must work below8.1: conditional
  `setAccessible(true)` for private/protected members on older PHP only. Never
  inject a ready/native handle, active flag or fabricated successful evidence.
- Existing adapter preflight requires reporting3 and freezes `php8.dll`; neither
  is a PHP7.4 contract. Observe the genuine initial mode: PHP before8.1 defaults
  to OFF, later PHP to ERROR|STRICT. Preserve actual mode across the fixed owner;
  ambient OFF must come from genuine core connection behavior, never a harness
  report toggle. Core6.0 source explicitly selects OFF on `db_connect`.
- Freeze correct PHP executable/modules, bitness/thread-safety/mysqlnd/config,
  core commit/version/closure, runtime and harness/controller sources, approved
  server identity and exact transport. Resolve PHP7 versus PHP8 module names;
  do not copy DLL assumptions or require optional JSON under `php -n`. Keep
  test-child transport PHP7.4-compatible and bound output/serialization.

Primary language references: [reflection properties](https://www.php.net/manual/en/reflectionproperty.setaccessible.php),
[reflection methods](https://www.php.net/manual/en/reflectionmethod.setaccessible.php),
[mysqli reporting defaults](https://www.php.net/manual/en/mysqli-driver.report-mode.php)
and [unsupported PHP branches](https://www.php.net/eol.php).
These explain design differences, not availability or executed qualification.

## Required non-vacuous proof and resource contract

Repository-only foundation tests must prove exact source/tuple rejection before
allocation, mismatched core/parser/module/mode refusal, legacy reflection and
serialization behavior, lane separation, and closed public factories. Positive
controls must reach the intended modeled guard/operation; an owner that always
returns unavailable fails. Removing a required source/mode/transport/reconnect
guard must kill assertions, not merely trigger an earlier startup failure.
Synthetic controls do not establish genuine authentication or native behavior.

Before each separately approved native attempt, freeze/audit a complete fresh
source image and pass same-run handle-free prerequisites. W must exercise genuine
core-created ambient OFF plus current owner; M must exercise genuine minimum
core/PHP7.4, native owner and relevant option/cache APIs. Preserve any naturally
eligible strict-mode case without manufacturing mode3 on7.4. No automatic retry.

Require actual successful prepare/execute/CAS/no-op/stale/same- and cross-site
conflicts/removal/creation race, exact binary length and autoload predicates,
duplicate/missing-table/lock-timeout/error classification, callback reentry,
once-only hooks/cache invalidation, uncertainty and no replay. Reuse audited
cases only where genuine version semantics agree; classify missing evidence,
do not change assertions to match a broken implementation. Full WordPress
authentication/bootstrap and guarded form integration remain separate gates.

Keep the complete quote/backslash/NUL/percent/four-byte escaping probe and fixed
quoted `escaped` alias. One admitted core execute call need not equal one
physical SQL statement: genuine non-ASCII collation validation may add driver
work when mbstring is absent. Record truthful physical counters and resource
cost; never replace bytes with ASCII, disable `check_current_query`, drop guards
or equate diagnostics to acceptance. If the fixed budgets cannot hold, stop.

Initializer20s/16credits/connect2s/read3s/statement3s/lock1s, body<20s and child25s
remain unchanged, as do parser limits and ADBC1/ADBC2 acceptance. Any outer
supervisor must bound startup, live hold, acknowledgement, termination and exact
owned cleanup without widening these budgets. Source hashes correlate a
qualified image; they are not authority supplied by the observed process.

Native scope must be disposable, owned and least-privileged, never existing
application tables/options/users/cron. Do not inherit old root/port/path approval
or copy live credentials/data into a lab. Independently reconcile **all-server**
sessions (including NULL/foreign database) and transactions while the correlated
closed child is held, acknowledge the exact run/image/PID, then reconcile again
after exit. Exclude only the exact observer, not sessions selected by schema.
Verify owned schema/account/listener/process/temp-resource removal, and preserve
existing state. Failed/unknown cleanup blocks further attempts, not a success.

## Ordered approval gates and return to product work

1. **Recommended next gated slice: Repository-only Minimum-Core/PHP7.4 and
   Windows/TCP Source-Rebaseline and Isolated Harness Safety Foundation.**
   Implement fixed per-core source manifests/bindings and PHP-version-safe test
   composition with isolated/synthetic tests only; inventory all consumers and
   assert unchanged guards/budgets/public factories. No installed-source access,
   binaries/SDK installation, native sessions, target metadata, fixture writes,
   new native workflow dispatch or production wiring. Unresolved source closure
   stops this slice rather than weakening admission.
2. Separately authorize audit-first exact environment/source/runtime feasibility
   for W and M. Select owned targets and immutable artifacts; unknown server wire
   suffix/build/driver/closure refuses. Site-backed inspection requires current
   Site Operations/Novamira and exact target confirmation. If prerequisites need
   accounts/permissions/installations, obtain that exact authority first. Prefer
   isolated source images, not changing installed LocalWP core/runtime to fit.
3. Separately authorize each bounded owned proof/cleanup with exact target and
   fixture confirmation, fresh prerequisites and one attempt. Evidence identifies
   implementation head, complete image, versions/transport/mode, actual checks,
   failures/skips, independent live/post-exit cleanup and ordinary exact-head CI.
   A future Windows7.4 run may cover both axes only if both exact contracts are
   genuinely exercised; shared OS/name alone does not discharge either lane.
4. Only after supported-profile gaps close, return to the
   [readiness design](SOURCE_POLICY_PRODUCTION_CONNECTION_READINESS_DESIGN.md):
   trusted-context/lazy-provider foundation and owned proof, then separately
   approved coherent guarded form/facade/legacy integration. Current application
   facade still uses the old whole-option writer; native algorithm success is
   **not** deployed lost-update repair. Public `create()` remains unavailable.
   Finish applicable canonical reviews/FULL_PRE_RELEASE before any coherent
   package; release, backup and live deployment retain separate gates.

## Review, validation and publication

Source-scoped design review maps provenance, minimum/core branches, mode/transport
separation, real positive/negative controls, costs and independent cleanup. No
new runtime behavior, tests or semantic controls are introduced by this design.
No UI/UX workflow, native/Database proof, physical transaction evidence or
whole-plugin release-readiness refresh is claimed. Historical failures/successes
and toolkit checklist rows are unchanged. Local QA and exact-head publication
results are recorded below after execution; they cannot qualify W or M.

Implementation-plan progress: the remaining supported-profile evidence now has
finite contracts and ordered approval gates; actual qualification is still
gated, before trusted provider/form wiring. Recommended next action: approve
gate1 above, not another native attempt or production activation.

### Executed repository-only QA

- Focused TransportProfile/TransportFoundation/BootstrapFoundation/
  CoreContextProofGate/NativeAdapterContract/GuardedEntryFoundation isolated
  tests:311 tests/1,334 assertions, no failures or skips.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result` on PHP8.5.1:
  2,033 tests/9,038 assertions, no failures; two existing missing-Sodium skips
  (invalid-private-key and signature roundtrip). No native or site proof ran.
- `npm run lint`:226 files, pass; `npm run test:admin`:23/23/no skips;
  `npm run check:structure`:149 runtime/227 support/205 tests/202 class names,
  pass; `npm run build`:pass/no tracked generated delta.
- `php composer.phar validate --strict`, `php composer.phar audit` and
  `npm audit --audit-level=moderate` pass; no reported advisories/vulnerabilities.
- Nine scoped local document links resolve; full design/diff reviewed and
  `git diff --check` passes. Runtime/scripts/tests/assets/CI/languages/bootstrap/
  dependency files are byte-identical to9535e51. Uploader3dfcb15/toolkitc067eb6
  remain clean and unchanged. Only this design, its implementation-plan entry
  and the Linux owner's forward pointer change. No whole-plugin rows refreshed.

### Publication evidence

Design commit `d154d1ff66d6478bc3001325693cf128c4fa0ec2` is pushed;
ordinary push [CI40868990970](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40868990970)
completed successfully on that exact head. Actual logs: PHP7.4 and8.3 each
2,033 tests/9,045 assertions, no failures/skips; admin23/23 on each job, no
failures/skips. Manifest, audits, build, structure and PHPCS passed, zero known
dependency advisories/vulnerabilities. Node setup emitted DEP0169 (`url.parse`)
deprecation notices; no check failed and no dependency change was made.
This evidence-only closeout receives its own pushed exact-head ordinary CI and
remote verification at handoff. No native workflow was dispatched. PHP7.4
isolated CI does not establish genuine core6.0/native compatibility.
