# V2.3 Authoritative Raw Observation and Native Backend Admission Integration Design

Current follow-up 2026-10-18: separately approved dormant contracts, native
planning observation and native closed-begin safety-row foundation now exist.
Uploader `docs/V2_3_NATIVE_CLOSED_BEGIN_PROOF.md` owns the 169-check owned native
closed-begin proof, 122-check observer/266-check guard reruns and independent
preservation/cleanup audits; publication receipts are recorded after verification.
Legacy validator calls are prepared before BEGIN/final auth; exact validated
tuples are rechecked under locks. Public requests remain unavailable, with no
public write API, paired target write, continuation or receipt resolution.
Real COMMIT-ack wire loss is not inferred from the protocol simulations. The
latest implementation-plan heading supersedes historical recommendations below.

Date: 2026-10-18. Status: approved **design-only** boundary; no reader, connection,
backend, transaction or admission implementation is authorized by this record.
Risk: R3 authentication/storage/concurrency design, explicitly approved by the user.
Owner: dashboard planning; prospective runtime owner: companion uploader only.

## Scope, baseline and existing evidence

Source snapshots: dashboard `4d435f654dc581a3867aaa8e745685cdb8c5dd1a`, uploader
`c06f29aa094995eee1bd77ce832154f441a7f711`. Explicit feature bases (excluded) carried
forward are dashboard `e93b18791842ec36eee79bf68735a5ead71739ac` and uploader
`10f96b629bc509a6e0836b8ab7480a37cf80e122`, through those snapshots (included).
The source-scoped design review considers that coherent schema-8 data, primitive
serialized-target and fixed genuine-core request boundary, not merely the latest
proof-document delta. The earlier canonical review remains in uploader
`docs/V2_3_CLOSED_WRITER_SOURCE_REVIEW.md`; its executed results are not broadened here.

The uploader's `docs/V2_3_FIXED_CORE_GUARD_PROOF.md` owns the prior genuine-core
266-check result, cache compatibility mutation evidence and isolated cleanup audit.
That result proves the named authentication guard cases, **not** protected raw
observation, native backend construction, schema-8 admission or physical transactions.
The earlier native SQL admission proof uses the separate schema-7 fixture domain.

Source inspected at the uploader baseline:

| Source | Existing behavior and design consequence |
| --- | --- |
| `includes/class-real-closed-writer-request-owner.php` and request contract/core-guard trait | Zero-IO construction; fixed purpose/nonce domains; public issue/consume always unavailable. No authoritative reader or backend. Frame metadata is not a native-session certificate. |
| `includes/class-real-closed-writer-model.php`, data/validation/candidate helpers | Pure schema-6/8 data consistency and default-off simulations. Schema 7 never becomes a real-domain event. Context digest v1 binds semantic source and exact target hashes, not raw safety-row identity or native connection ownership. |
| `includes/class-serialized-settings-parser.php` and target helper | Bounded primitive byte parsing; only the literal root scan Boolean may change, preserving unrelated bytes. Pure data output is not permission. |
| `includes/class-schedule-safety-option-cas.php` | Existing raw option CAS uses borrowed `wpdb`, can create dedicated state and publishes cache invalidations. It is not the new paired native backend and must not be silently repurposed. |
| `includes/class-settings.php`, `includes/trait-settings-site-identity.php` | Normal settings/default/identity APIs are not a read-only raw-observation provider. No UUID/default creation through this path. |
| `scripts/tests/admission-proof-factory.php`, `admission-proof-transaction.php`, `closed-writer-proof-storage.php` | Developer-only owned fixtures, injectable fixture ports and simplified endpoint parsing. Useful evidence of those fixtures, not a production factory or real-domain admission certificate. |

Related controlling designs: [trusted real-domain construction](V2_3_TRUSTED_REAL_DOMAIN_CONSTRUCTION_DESIGN.md)
and [authenticated closed-writer admission](V2_3_AUTHENTICATED_CLOSED_WRITER_ADMISSION_DESIGN.md).
This record refines their raw/native ownership gap; it does not change existing
schemas, actions, digest versions, dashboard endpoints or runtime/public contracts.

## Decisions and non-goals

1. Keep the fixed request grammar and genuine core guard. No caller-selected
   authorizer, clock, UUID, table, DSN, reader, transaction callback or support flag.
2. Read the two existing fixed rows privately, with byte limits enforced before
   materializing values. Missing, malformed, oversized or incoherent means unavailable;
   never install, repair, migrate, default or create identity during observation.
3. Separate genuine WordPress authentication from privately owned storage authority.
   One private physical session owns **both** safety and settings operations. Do not
   borrow the application's `wpdb` handle for transaction control.
4. Raw observation is data, not admission. Only a later approved fixed owner/backend
   integration may create an unexportable, single-use continuation after confirmed
   durable closed begin and renewed authorization. No array or fixture port can mint it.
5. Unknown commit is unresolved, not a retry signal. Reconciliation is separately
   authorized observation with an in-flight transaction resolution barrier.

No current SQL, site/user/option/cron changes, native connection opening, fixture
creation, public success path, production loading/registration, dispatch, grant,
activation, schedule apply, rollback, package, release or deployment. No database
schema or plugin compatibility-floor change. A future connection capability profile
can refuse an environment without raising PHP 7.4 / WordPress 6.0 plugin minimums.
Trusted same-process PHP is an assumption: private/final ownership is an API boundary,
not isolation from malicious code with process or database credentials.

## Two connection roles, neither interchangeable

### Authentication connection

The existing guard performs real cookie/session, actor, capability and purpose-bound
nonce checks, including fresh cache eviction and post-nonce checks. Its role/session
reads are authorization work, not the protected settings/safety observation. Preserve
strict false/null built-in-cache support and every unsupported-frame refusal.

Before and after every future genuine-core guard, passively establish that the
native WordPress authentication connection is idle, autocommit is on, and it is
still the same handle, physical session, database and single-site scope. Do not
issue START, SET, COMMIT or ROLLBACK on that connection, replace global `wpdb`, or
repair ambient state.
An ambient transaction could expose stale role/session snapshots despite cache
eviction; unknown idle state, changed handle/session or a core reconnect denies
further protected observation/admission. These system-metadata checks may precede
authorization; protected application rows must not. This additional fence is a
**future requirement**, not part of the completed guard proof.

### Private storage session

After valid syntax and fresh authorization, a future fixed internal factory may
open a nonpersistent native `mysqli` session using only trusted installation
configuration. Never return or accept its handle. Do not adopt an existing session,
enable `p:` persistence, call `ping`, reconnect, fail over, enable multi-statements
or local-file loading, or use `wpdb::query` for its transaction lifecycle.

Rationale: WordPress's [connection check](https://developer.wordpress.org/reference/classes/wpdb/check_connection/)
can reconnect, and a new transaction can implicitly commit an existing one
([MariaDB transaction reference](https://mariadb.com/docs/server/reference/sql-statements/transactions/start-transaction)).
PHP's [ping API](https://www.php.net/manual/en/mysqli.ping.php) is not an ownership
certificate; [thread ID](https://www.php.net/manual/en/mysqli.thread-id.php) is only
one connection-lifetime fence, not proof of primary identity or authorization.
The private-session decision prevents this backend from committing unrelated
application work; it does not certify the separate authentication connection.

First **candidate proof profile**, not declared runtime support: MariaDB 10.6,
mysqlnd/native `wpdb`, a trusted direct primary endpoint, InnoDB, one blog and no
DB/object-cache drop-ins or external cache. Its adapter must verify idle state via
the documented read-only session `in_transaction` variable, autocommit, selected
database, charset and connection identity. Do not assume that variable exists on
MySQL or that a missing diagnostic row proves idleness. Unknown profiles refuse.

The later factory must resolve the exact installation transport without the
fixture's `explode(':', DB_HOST)` shortcut. Credentials remain private, transient
and never part of observations, receipts, diagnostics or logs. Preserve required
TLS verification/socket/charset behavior; no insecure downgrade or guessed fallback.
An unsupported TLS/socket/proxy/replica/HA configuration needs a separately proven
profile, not a caller assertion. Both connection roles must be established as the
same trusted primary/database; matching names or server IDs alone do not prove this.
No new database account, permission or credential setting is proposed.

## Bounded, coherent authoritative observation

Fixed table: validated single-site options identifier from the fixed frame. Fixed
names only: `alynt_drime_backups_schedule_safety_state` and
`alynt_drime_backups_settings`. All values use prepared parameters; identifiers are
fixed validated installation metadata, never form fields. No `get_option`, settings
getter, `maybe_unserialize`, PHP `unserialize`, option filters or UUID generation.

The future read primitive must use the unique option-name index and return at most
one exact-name row. Project positive row ID, exact bounded autoload/name metadata,
octet length, and a conditional raw value **only when** within the relevant cap.
Do not fetch an arbitrary LONGTEXT and then measure it in PHP. A collation-equivalent
but byte-different name, duplicate result, invalid row ID, truncated result or SQL
error refuses. Both rows require literal `no`, `off` or `auto-off`; no coercion.

Safety raw value is JSON, capped at 131072 bytes, with the existing strict schema,
plain-data/node/string/depth/history/receipt limits. Accept only supported closed
schema 6 or schema 8 as appropriate to the purpose, never schema 7. Reject duplicate
JSON member names rather than silently accepting a decoder's last value; a bounded
duplicate-key policy/proof is required before a real reader can be admitted.
Settings raw value is serialized primitive data, capped at 1048576 bytes with the
existing parser's 4096 key/value-node, depth-15 and collection bounds. Preserve
exact UTF-8 bytes, literal root Boolean, existing canonical UUID and unknown keys;
never reserialize the whole target. Compare identity to the valid safety scope.

Outside a transaction, use bounded reads in order safety A, settings A, safety B,
settings B. Require exact equality of each pair's row ID, raw bytes, autoload and
fixed scope/session. Re-run genuine authorization and idle/session fences afterward.
Refuse mixed observations; no read loop that silently moves to a newer request
context. Cooperating generation changes are detected; a nonparticipating ABA writer,
restore or malicious database administrator is **not** ruled out by repeat reads.
This is a planning observation; locked revalidation remains mandatory for admission.

Keep a private observation binding over both exact row tuples, scope, semantic
context and session lifetime. Use a separate internal version/domain and unambiguous
length-delimited encoding. Do **not** silently change existing context-digest v1 or
add a posted field. Every consume request forms a new private binding; issuance
does not transfer a physical session or handle. The original intent still binds
the existing semantic context, target hashes, identities and deadline. Compare the
consume-time exact tuple again under locks, including raw safety bytes even when
canonical semantic hashes happen to match. Raw values, target after-bytes, connection
facts and the binding are never public responses, serialized owners or debug output.

## Native transaction and private admission protocol

The following are **design states, not implemented APIs**:

`unopened -> idle -> begin-sent -> transaction-owned -> commit-sent -> confirmed-idle`

Any uncertain transition poisons the private session and ends its lifetime. No
reconnect or automatic restart. A private session is never adopted twice or passed
through a generic callable. Reentrancy, clone/serialization and cross-request reuse
must fail; exceptions/finally invalidate its continuation before closing only its
own resources. Do not set global mysqli reporting or other process-wide DB behavior.

Admission requires a proven transactional options table with normal full unique
option-name indexing and both existing rows. Verify engine/schema and absence of
side-effect triggers/foreign keys with sufficient metadata visibility; empty hidden
metadata is not a certificate. Revalidate relevant metadata under the table's
transactional metadata lock. No DDL, schema repair, automatic insertion, global SQL
settings, stored procedures or privilege changes. Concurrent administrator DDL,
restore, old binaries and nonparticipating writers remain explicit deployment gates.

Session policy is fixed and verified while idle: autocommit on, READ COMMITTED,
connect timeout 3s, read timeout 5s, row-lock wait 2s and metadata-lock wait 3s for the
candidate profile. Bound total operations by a 10s monotonic budget and the original
intent's at-most-300s wall-clock window, whichever expires first. A blocking call
can overrun an application budget; no hard cancellation guarantee is claimed.
Clock regression, expiry or a changed prepared completion timestamp denies without
refreshing or minting another identity. Profile syntax and timeout semantics need
later genuine driver/server proof, not a fake driver's acknowledgement.

1. Authenticate outside locks; pin original intent, identities and once-reserved
   completion ID. Validate scope/profile and private session idleness before START.
2. Lock safety first, settings second, on that same private physical session.
   Re-read bounded exact rows using locking reads, then validate raw tuple, semantic
   context, lifecycle, counters, receipt domain/identity and capacity. No WordPress
   authorization/option/cache hooks, network calls, logs or arbitrary callbacks while
   locks are held. Pure bounded decoding/encoding is allowed. Fresh core auth runs
   before locks and after release; revocation during the SQL critical section is
   not atomically excluded and must not be misrepresented as such.
3. Upgrade/closed begin may make at most three actual definite-conflict comparisons,
   with confirmed rollback and renewed guards between attempts, retaining original
   intent/deadline/IDs. Authorization calls are not the attempt counter. No retries
   for uncertainty. Schema-8 closed begin must commit durably and be read back before
   a renewed guard permits any private single-use continuation. It is not a grant.
4. On completion, one attempt only: lock/revalidate the **post-begin** closed state,
   expected pending closure, exact target bytes and original command chain. Never
   compare a pre-begin state hash to its legitimate successor. Apply the one-token
   settings byte patch and matching internal completion in the same transaction,
   with exact row-ID/autoload/binary-value predicates, expected affected-row counts,
   bounded raw readback and fixed prepared timestamp before commit. A zero-row
   update is not success. The existing already-false/no-pending no-op is separately
   proven and never used to excuse a failed pair write. Abandon is also one attempt.
5. Use explicit `COMMIT AND NO CHAIN NO RELEASE` (and the corresponding ROLLBACK
   form) for the verified profile; do not inherit configurable completion behavior
   ([MariaDB COMMIT](https://mariadb.com/docs/server/reference/sql-statements/transactions/commit)).
   Require acknowledgement, the same session and verified idle state. Post-release
   readback/authorization can withhold an outward result but cannot undo a confirmed
   commit or turn it into permission to repeat the target write.

Definite pre-commit failure rolls back the owned transaction or poisons/closes it
if rollback cannot be confirmed. A prior durable begin stays closed/pending when
the pair transaction fails. A lock timeout does not inherently roll back the whole
transaction ([InnoDB error handling](https://dev.mysql.com/doc/refman/8.4/en/innodb-error-handling.html));
the profile must prove its own server semantics and explicitly terminate the owned
transaction. Never continue after a failed statement or certify success from a
transaction-state flag alone. No compensation, automatic abandon or receipt pruning.

## Unknown outcomes and observation-only reconciliation

Once COMMIT has been sent, transport loss, timeout, exception or a missing response
may mean committed or not committed. Poison/close the private handle, invalidate
the continuation, preserve original IDs, and classify the result as unresolved.
Rollback/close after that point cannot prove the commit did not happen. Do not
reconnect, replay, allocate another command, clear pending or write the old target.

A later fresh genuine-core `receipt_read` intent has its own short lifetime and
nonce domain; old mutation expiry still denies mutation. Use a new verified private
session and acquire safety/settings locks in the same order as a **resolution
barrier** before interpreting receipt absence: an ordinary unlocked read can race
an earlier transaction still committing. Failure to obtain that barrier remains
unresolved. Reconciliation performs no target/state writes and terminates its
owned locking transaction explicitly before renewed authorization and output.

Validate retained command ID, digest, real domain and full closure/completion chain,
then separately observe current raw target facts. A positive retained receipt can
establish its historical event; subsequent legitimate target changes must not be
reported as the old result still being effective. Absence alone never proves
rollback/no effect, particularly with missing/pruned history, restore, inconsistent
state or unsupported storage. Never turn historical fixture receipts or a Boolean
backend return into real admission. Keep outputs support-safe and non-authorizing.

## Cache, effect and lifecycle gates remain closed

Raw storage is authoritative for every eventual participant. Invalidate the two
option keys plus `alloptions`/`notoptions` only after confirmed commit and outside
locks. A cache-delete false return can mean no entry; it does not justify replay.
Invalidation failure or stale repopulation is an observability/effective-state
problem, not SQL rollback or renewed permission to write. The public dormant owner
continues returning its exact unavailable shape until a separately approved
integration changes it with genuine proof.

Stored `auto_scan_enabled=false` is not proof that running scans, queues, uploads
or other writers stopped. Cron/producer/consumer enforcement, lifecycle admission,
all settings writers, restore/downgrade/uninstall, cache participation and old
binaries need their own coherent integration and acceptance. Dashboard stays
read-only relative to clients and Drime; there is no remote command surface here.

## Acceptance matrix for later approved work, not results of this design

| Proof layer | Required discriminating evidence |
| --- | --- |
| Pure contracts | Exact row metadata/bounds/duplicate JSON rejection, byte-preserving non-ASCII targets, mismatched/recreated rows, private binding versus unchanged context v1, legal/illegal session-state transitions. Synthetic native facts remain data, never certificates. |
| Guard ordering | Traps for protected reads before valid capability/session/nonce and after revocation/context loss; separate issue/read/mutation nonce domains; malformed/stale intent never opens storage. Removing or moving each required guard must fail a specific control. |
| Native/session refusal | Ambient auth transaction, reconnect/session swap, unknown profile, wrong endpoint/database/charset, persistent handle, nontransactional or unverifiable schema/trigger metadata. Denial must not commit/rollback the application session. |
| Owned integrated schema-8 SQL proof | Genuine core guard plus the fixed private factory, exact primitive settings bytes, closed begin and atomic pair; lock order, changed autoload/row IDs, concurrent writers, timeout/deadlock, failure after first update and no hook/callback under locks. Disposable owned scope only, with independent preservation/cleanup audits. |
| Commit uncertainty | Lost begin acknowledgement, lost COMMIT response, old transaction completing after first observation, resolution-barrier contention, absent/pruned/wrong-domain receipts and superseded targets. No retries or compensating writes; preserve unresolved states. |
| Encapsulation/mutations | No returned write-capable handles, private after-bytes or secrets; no public injectable success ports. A bypassed guard, omitted lock, unsafe reconnect, loosened equality or absent barrier must fail its meaningful test, not merely return expected unavailable. |

An owner returning unavailable for every request cannot by itself pass these gates.
Use positive internal decision/observation controls without creating artificial
public success. Distinguish a simulated SQL transcript from genuine authentication,
driver sessions and concurrent storage proof. Existing 266-check guard proof and
schema-7 fixture SQL remain separate; neither proves their proposed combination.
No Database Review, physical atomicity, power-loss durability or whole-plugin
release readiness is claimed by in-memory tests or this design.

## Focused design review and executed validation

Source-scoped architecture, authorization/privacy, concurrency/error handling,
resource bounds and lifecycle review found and resolved these **design gaps**:

- Shared `wpdb`/ambient transaction and reconnect ambiguity: private storage
  ownership plus passive idle/session fencing around genuine core authorization.
- Oversized/defaulted or mixed observations: indexed bounded projection, strict
  primitive/JSON policy, repeated exact tuples and locked revalidation.
- Pure semantic context mistaken for physical admission: separate private binding,
  no digest-v1 drift and no exportable capability/provider ports.
- Uncertain commit mistaken for failed write: poisoned session, stable identity,
  separately authorized locking reconciliation and no absence-based success claim.
- Cached/stored state mistaken for effective stop: explicit later writer/lifecycle
  gates; unsupported transport/server/schema behavior is refused, not inferred.

This is a focused design review, not a new canonical whole-plugin Feature/DS3,
Database Review, UI/UX pass or refresh of historical toolkit release-readiness rows.
The next coherent release candidate still requires the canonical full pre-release
workflow before packaging/publication. No toolkit checklist is changed by this
design-only checkpoint.

Executed repository-only validation:

| Command/check | Result |
| --- | --- |
| Dashboard `php vendor/bin/phpunit --do-not-cache-result` | 299 tests / 1423 assertions; two existing Windows skips, not complete platform coverage. |
| Uploader `php vendor/bin/phpunit --do-not-cache-result --filter 'FixedCoreRequest\|RealClosedWriter\|SerializedSettings'` | 210 tests / 1832 assertions, no skips; focused in-memory regressions, not a rerun of native proof or the full uploader suite. |
| Dashboard `npm run lint` | PHPCS 138 files passed. |
| Dashboard `npm run check:structure` and `npm run build` | Passed; no generated changes. |
| Dashboard `php composer.phar validate --strict`, `php composer.phar audit`, `npm audit --audit-level=moderate` | Valid manifest; zero advisories/vulnerabilities. |
| Relative design links, exact four-file docs-only scope and `git diff --check` | Passed; no uploader changes. |

Exact pushed-head CI/status is verified at publication and included in the handoff;
local results above are not a substitute for that gate.
No executable tests are added for an unimplemented design. Uploader remains
read-only; versions remain dashboard 0.1.77 / uploader 0.5.25. No site, SQL, native
connection, fixture, browser, release or deployment operation runs in this slice.

## Executed follow-up boundary — 2026-10-18

The approved contract foundation and then separately confirmed Plugin Tester
native planning-reader/session-ownership proof now exist. Uploader
`docs/V2_3_NATIVE_RAW_OBSERVATION_PROOF.md` owns the exact source baseline, 122-check
native proof, separate 266-check genuine-core guard rerun, fixture cleanup and
77-table preservation audit. This is only read-only planning observation, not
native transaction admission, receipt resolution, authenticated schema-8 provenance
or paired-write/concurrency proof. Those broader acceptance requirements above
remain unimplemented. See the latest implementation-plan heading for the next
Fixed Native Closed-Begin Transaction Safety Foundation and Owned Failure Proof
gate; owned safety-row SQL writes require separate explicit approval, and paired
target writes/application state/production wiring/execution remain excluded.

## Historical next gated slice at design completion

Recommend approving **local-only V2.3 Fixed Raw Observation and Native-Session
Admission Contract Safety Foundation**: implement the missing bounded exact-row/
coherence/private-binding and fail-closed session/admission lifecycle contracts as
one coherent dormant uploader foundation, with non-vacuous in-memory decisions and
mutation controls. Keep the fixed public owner unavailable, all definitions
test-loaded/unregistered, and actual reader/connection/SQL execution, fixtures,
production wiring, grants, activation and rollback disabled. Do not manufacture
native certificates from those simulations. A later separately approved fixed
native adapter and confirmed owned schema-8 integrated proof must establish the
physical facts before real admission can be considered.
