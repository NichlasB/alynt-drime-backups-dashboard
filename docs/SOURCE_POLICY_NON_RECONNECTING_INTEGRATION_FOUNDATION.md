# Source-Policy Non-Reconnecting Native Adapter and Guarded Integration Safety Foundation

## Authority and baseline — 2026-10-27

Separately approved repository-only R3 foundation, isolated tests only, through
validation/commit/push/exact-head CI. Clean/synced restore point:
`a7e41ab44c740f63c1eb4ff255bdf55cc1eaf914`. Implements the dormant contract stage
of the [approved design](SOURCE_POLICY_NATIVE_ADAPTER_INTEGRATION_DESIGN.md), not
production/native construction or final form wiring. Maximum autonomous R2 is
not raised. Version0.1.77/schema8 and declared PHP7.4/WP6.0 remain unchanged.

No WordPress/LocalWP/site inspection, actual authentication/options/SQL/cache/
users/cron/schema, fixtures, native driver/connection, HTTP, service, credential,
client/Drime, package/release/tag/deploy operation. PHPUnit owns only local test
processes and in-memory API/driver/cache doubles. No native mysqli object/socket
is created by the tests. Uploader/toolkit remain read-only; prior8/8 not inflated.

Production loader, Source_Policy facade, Plugin/Admin_Page construction, actual
form/handler/notice code, classifier, manual native proof scripts, assets/catalog,
CI/dependencies and persisted identities are byte-unchanged. The real application's
legacy whole-option lost-update race is still open. No site target is inferred
from historical approvals and no native proof was rerun.

## Implemented dormant boundaries

Five cohesive definitions, explicitly required only by isolated tests:

- No_Reconnect_Connection: conditional wpdb-derived definition without a parent
  constructor call. Construction is non-public; create() returns unavailable.
  Initial db_connect and check_connection are final refusals: no reconnect,
  reissue, sleep or bail. final query/execute preserve one inherited query call
  rather than copying core's executor, and prevent a subclass executor override.
  Raw print_error is refused; only fixed result/error DATA escapes. Unknown
  acknowledgement errors mark the session unusable; no follow-up on a lost link.
- Native_Profile: validates a private provider descriptor and bounded callback
  metadata without invoking/autoloading callbacks. It models only core6.0/no and
  recorded7.1.3/off branches on the stated MariaDB/schema/single-site/autocommit/
  bounded-session/core-cache descriptor. Actual core cache identity/extension flag
  and hook fingerprint must remain consistent. Query callbacks are always closed
  in this initial port, even with a matching fingerprint; SQL-transform support
  is not fabricated. This is DATA consistency, not native qualification.
- Stored_Map: caps raw bytes16KiB, uses allowed_classes=false/depth3 decoding,
  restores the prior warning handler in finally, then applies the existing
  primitive/reference checks and exact re-encoding. Bad row shape, objects,
  references, malformed/count/depth/size/trailing bytes and unsupported autoload
  reject. No get_option/empty writable fallback or magic-method-enabled decode.
- Native_Adapter: private construction/create() closed; one privately held owned
  session per operation. It checks command/revision/profile before protected
  read, reconstructs the observation/plan internally, and checks the SELECT
  acknowledgement/row count before recognizing absence. View returns only mode
  and revision from that same raw observation, never storage/connection handles.
  One strict INSERT or binary/length-sensitive conditional UPDATE; no upsert,
  retry, rebase or compensation. Stale expected state is conflict before no-op.
  Legacy-shaped bool bridge uses this same one-read writer, not an old setter.
- Guarded_Entry: private construction/create() closed; no registration/global POST
  reader. Capability -> existing action nonce -> raw types/allowlists/revision ->
  exact matching record lookup -> owner. Invalid intent causes zero record/owner calls. View
  exposes only the exact two-field DTO. Submit accepts literal saved/unchanged,
  rejects booleans/model arrays/objects/unknown strings and projects safe error
  codes without collaborator messages/data. No actual public handler is switched.

Profile consistency is rechecked between sanitation, specific/generic pre-update,
view projection, prewrite, cache completion and specific/generic success phases.
A new callback is not executed merely to discover whether it is safe. Before
submission, changed admission rejects; after submission/completion failure it
is uncertain. Reentry rejects before a second protected read and cannot close
the outer session. Only relevant private core cache keys are invalidated; no
stale-map seeding, global flush or shared-cache linearizability claim.

Return conventions: explicit owner returns saved/unchanged strings or WP_Error;
unknown/false/impossible acknowledgements cannot become success. Definite
qualified syntax/lock errors remain storage_error, not generic duplicate. A
strict insert1062 may use one bounded observation; it does not adopt a winner
or retry. Exceptions/unknown completion after submission, cache/hook failures
and unconfirmed close are uncertain. Every terminal operation closes only its
owned session; reentry leaves that ownership with the outer operation.

### Construction and proof limits are intentional

All three public create() methods return unavailable without authentication,
profile discovery, native IO or collaborators. No code provisions a real owned
session or private qualification descriptor. Bounded initial connection creation,
TLS/socket/flags/routing equivalence, real metadata admission and genuine minimum-
core/current-profile behavior are NOT implemented or qualified by these tests.
The non-reconnect subclass is not yet a working production connection factory.

Tests define a small fake wpdb in fresh PHP processes. It models core's reissue
branch and prepared SELECT/INSERT/CAS against RAM; it is not a copied genuine
core driver or SQL parser. Reflection provisions the actual closed adapter's
private fields with fixture connection/profile DATA solely in tests. This is
not a public activation seam or native provenance. A future provider may not
stamp current arbitrary facts/fingerprints as trusted merely because they match.
It must establish the design's actual core/schema/hook/cache/session conditions.

Positive tests run the actual adapter/guard algorithms, not an owner that only
returns unavailable. Default factory refusal is tested separately. No false
minimum-version, genuine authentication, physical transaction/cache/SQL-concurrency,
Database Review or whole-plugin release-readiness credit is taken.

## Regression evidence and assertion-killed controls

New isolated suite:123 tests/813 assertions, no failures/skips. Coverage includes
both modeled core/autoload branches, positive view/update/insert/no-op/legacy
bridge, exact non-target preservation and one-read budgets; stale already-
satisfied/contender/creator/value/autoload comparisons; malformed SELECT and
write acknowledgements; native-error DATA vs unknown completion, failed read not
absence, cache/hook/close faults and retained RAM mutation after uncertainty.

Guard tests cover capability/nonce/type/revision/record ordering, zero protected
calls on rejection, wrong nonce domain/invalid payload, strict outcome types and
error privacy. Hook tests include non-empty positive observer controls, reentry,
profile changes during reads/callbacks/cache and unknown callback refusal. Decoder
tests check primitive alias/absence semantics, hostile encoding/count/depth/size,
no object magic and exact error-handler restoration.

Final scoped review caught permissive record resolution in the new dormant entry:
an empty/malformed/wrong-site array was treated as an existing site. Seven new
cases failed7/7 assertions before the correction and pass7/49 afterward, checking
both view and command with zero owner calls. Resolution now requires a scalar
integer/string record ID exactly matching the normalized admitted site. This was
an unconnected foundation defect, not evidence of a changed live-site behavior.

Five temporary semantic controls failed assertions and were restored byte-exact:

| Control | Executed tests/assertions | Expected assertion failures |
|---|---|---|
| Re-enable core reconnect/reissue |3/22|2|
| Bypass submit capability guard |1/2|1|
| Remove between-phase profile checks |6/19|5|
| Enable object classes in decode |1/3|1|
| Remove value byte equality |6/38|2|

External invocation counters are asserted after returned errors, so caught
callback/magic exceptions cannot make a bypass pass vacuously. The reissue control
also demonstrates the dangerous case: a RAM mutation followed by replay can
become conflict instead of the required uncertain outcome. No control touches
native state and no mutant is committed.

Restored SHA256 values:

- No_Reconnect_Connection:1B444F72A517CF100165C34B3784CA806B80FE495E9AFBEDC12140BF96DC67A1
- Guarded_Entry:39F3C148C762927B1B96EC388AD7BF0D42B9712539244ABC6224ECB0873B9FBC
- Native_Adapter:FB691ECDA590CFE1ECDCE36A9DEF2CA79AA656FCE27A499F5D255E3A70ACD809
- Stored_Map:D76EB314D43C5D19C79BC1A88D0065F3D941014C3A9D61275DF515D2BB324B2A

Capability control was repeated after the record correction:1/2, one expected
assertion failure, then exact restoration to the final hash above.

Restored related randomized378/1405 (seed20261027) and full local1212/5363 pass;
full suite has only the two baseline missing-Sodium signing skips. Node16/16,
PHPCS184 configured files, structure142 runtime/209 support/174 test files,
build, strict Composer manifest and Composer/npm audits pass, zero advisories.
No assets changed. PHPCS excludes tests; the PHP suites execute them. All16 new
PHP syntax checks pass; diff, links and production-disconnection checks pass.
Initial test discovery and PHP8.5 reflection deprecations
were fixed locally without changing the canonical test config or adding skips.

Commands: new filter SourcePolicy(NoReconnectFoundation|NativeAdapter|
GuardedEntryFoundation|StoredMapFoundation); related filter SourcePolicy|
AdminPageCompoundRequestId with --order-by=random --random-order-seed=20261027;
full `php vendor/bin/phpunit --do-not-cache-result`; npm test:admin/lint/
check:structure/build; `php composer.phar validate --strict`, Composer audit,
`npm audit --audit-level=moderate`; explicit syntax/diff/source-identity checks.

## Source-scoped reviews, not whole-plugin completion

Feature Light:16 new PHP files plus directly affected unchanged facade/model/
admin/loader context. Risks: dormant DB/request/IO contracts, not a registered UI
or live effect. Callback-phase/profile checks were tightened and assertion-
protected; final record-resolution issue corrected with red/green evidence above.
No generic storage
framework, fallback writer, new credential setting, persisted schema or dependency.
No UI/UX workflow: no form/rendered output/user-facing string/notice/assets or
actual request wiring changed. Existing output/error mapping remains unchanged.

Feature Bloat/Structure read-only discovery: canonical PowerShell report with
explicit baseline a7e41ab44c740f63c1eb4ff255bdf55cc1eaf914 (master feature boundary,
including all untracked additions),16 changed files, zero oversized. Runtime
TOTAL counts: Guarded_Entry131, Native_Adapter298, Native_Profile110,
No_Reconnect_Connection124, Stored_Map56. Project check counts one trailing empty
line differently (largest299); both are under300. Other helper/test TOTALs:
28/42/40/60 and75/45/65/62/56/41/43, each under the project's stricter150 limits.
The canonical PowerShell CODE metric reports1 for every file (its negative split
limit collapses input in this host); it is unusable and is not claimed as a true
code count. TOTAL thresholds and changed-file boundary were independently sane.
Toolkit is read-only; no tooling repair or full bloat workflow completion claimed.

Per-file disposition for all16: Consolidate/no-op retain (the workflow has no
Keep bucket); no confirmed duplicate/dead/troubleshooting code to consolidate.
Direct references are confined to isolated support/tests; hooks/filters/REST/
AJAX/cron/shortcodes/string callbacks and production loading were searched. These
unconnected definitions are intentional foundation scope, not dead-code removal
candidates. No feature-stage bloat or structure issues found in changed files;
zero cleanup/split/defer items, no Phase2 edits required. No arbitrary split of
the cohesive adapter at the line limit. Whole pre-release02 remains its own gate.

Final Feature Security: exact command types and nonce/capability ordering,
private closed construction, prepared fixed-domain SQL, driver replay refusal,
raw-error suppression, bounded no-class decode, callback consistency, private
DTOs and no untrusted native admission reviewed. Record identity finding fixed
and revalidated; no remaining scoped security finding. All confirmed safety
concerns have permanent isolated tests and five
assertion-killed controls; genuine native/auth/cache evidence remains gated.
Whole07/07A/13/FULL and historical toolkit checklist rows remain incomplete/
unchanged. No security claim turns fixture DATA into native authorization.

Publication: implementation `84efe8d40262af36a1b4e97d06936a816d6cfcb6` passed
[exact-head CI40587584628](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40587584628).
Actual job logs122483046355 (PHP7.4) and122483046017 (PHP8.3) each report
1212 tests/5370 assertions, no failures/skips; Node16/16, all configured gates
pass and Composer/npm audits report zero advisories. Hosted isolated tests do
not establish genuine core/authentication/cache/driver or native qualification.

## Implementation-plan progress and exact next gate

Approved dormant adapter/guard algorithms now exist with positive and negative
isolated evidence. Production initialization, facade/form/handler connection and
the real lost-update issue remain unchanged. Initial connection provisioning and
real profile qualification are deliberately closed, not silently implemented.

Recommended next gated slice: **Plugin Tester local-only Fixed Non-Reconnecting
Connection Construction and Native Adapter Owned Qualification/Cleanup Proof**,
audit first, including genuine minimum-core/current-profile qualification before
any production use. It needs separately approved bounded native construction and
owned disposable fixtures/cleanup plus fresh exact target/scope confirmation.
Preserve all existing application state and keep production factory/form wiring
disconnected. No live deployment or unconditional fallback is recommended.
