# V2.3 Guarded Registration Receipt Observation and In-Flight Closure Design

Approved 2026-10-19, R3 **repository-only/design-only**. Dashboard owns this
contract; companion uploader would implement it. Excluded review bases: dashboard
`7165b9df616ee2ee33f64ace5b90bc79797b86f6`, uploader
`df89d677db990fc909f2e53bf4d67876afb31060`. Review the complete design/plan delta,
not a later documentation receipt. Versions remain dashboard 0.1.77/uploader 0.5.25.

Refines [native admission](V2_3_CLOSED_REGISTRATION_NATIVE_ADMISSION_DESIGN.md),
[registration/reserved intent](V2_3_CLOSED_REGISTRATION_RESERVED_INTENT_DESIGN.md)
and [storage/participants](V2_3_NATIVE_SCAN_EFFECT_STORAGE_PARTICIPANT_DESIGN.md).
Uploader `docs/V2_3_REGISTRATION_NATIVE_ADMISSION_PROOF.md` owns the predecessor.

## Scope and decisions

Map source, define trusted lookup origin/fresh observation construction, a fixed
all-ten non-writing barrier, result semantics and a separately versioned future
registration-ingress closure protocol; review races/failure cases; validate
unchanged code, commit/push and verify exact-head CI. **No implementation**,
runtime/test/schema/version/dependency changes, site/LocalWP/Novamira/SQL/browser/
fixtures/users/sessions, actual storage operations, endpoints, production wiring,
grants/activation, reservation/effect execution, rollback, package/release/deploy.
No site target or Novamira call is needed because no site is accessed.

Decision: implement observation before any closure writer. A fresh authenticated
read may establish exact historical closed registration, or only that the command
was not observed at its barrier. It cannot drain or close another invocation.
Designing a durable ingress fence does not implement or approve that write.
Schema-2 `closed=true` means **closed proof mode**, NOT closed registration ingress:
the current owner still permits the first registration in that mode.

## Source-backed boundary

### Approved origin/result DATA foundation — 2026-10-19

Uploader `docs/V2_3_REGISTRATION_OBSERVATION_FOUNDATION.md` owns the dormant pure
origin/read-grammar/result and simulated one-shot lifetime implementation. New
**15 / 285**, related **280 / 3654**, no skips; ten assertion-killed/exactly restored
controls. Existing request/history/codecs/guards/native owners/production wiring
unchanged. No actual authentication, UUID issuance, native handle/storage/site/
fixture, closure migration/writes, grants/activation/execution/rollback operation.
The origin remains synthetic DATA, not genuine provenance or a barrier certificate.

The prospective protocol below remains normative for later genuine integration;
this milestone does not implement its private origin retention seam or native
observer. Next gated slice: **local-only V2.3 Observation Origin Retention and
Guarded Request Construction Safety Foundation**, dormant isolated-API integration
before separately approved genuine core/native owned-fixture proof.

All paths in this table are uploader-relative and remain unchanged here.

| Source | Existing fact / consequence for this design |
|---|---|
| `includes/class-registration-native-admission.php` | Private same-stack genuine construction and owned schema-2 ledger-only commit; outer once-attempted latch. Clears private context on exit and exposes no durable recovery context or public continuation. |
| `includes/trait-registration-admission-transaction.php`, `includes/trait-registration-admission-storage.php` | All ten locks, complete tuple equality, exact binary ledger CAS/readback and pre-IO sent latches. A captured array or matching revision cannot substitute for this ordering. |
| `includes/class-registration-request-contract.php` | Pure full observation envelope/receipt action already exists; small issuance and issue_action currently accept mutation purposes ONLY. Neither is genuine observation issuance or origin provenance. |
| `includes/class-closed-registration-contract.php` | Pure lookup validates schema-2 history and expected context; returns registered_closed / intent_recorded_closed / domain_conflict / not_observed_in_candidate. No native barrier, terminal history, closure or authority. |
| `includes/trait-closed-registration-records.php` | Full canonical receipt/context/operation chain; only registered and intent_reserved phases, no terminal/cancel/drain representation. |
| `includes/trait-registration-eligibility.php` | Registration requires zero pending operations. Do NOT reuse that admission predicate for observation: it would reject precisely the retained pending registration being sought. |
| `includes/trait-real-closed-writer-core-guard.php` | Genuine local session/manage_options/nonce and original request/window checks outside locks; not authorization valid at the exact COMMIT instant. |
| `includes/trait-native-options-metadata.php`, `includes/trait-native-closed-begin-session.php` | Conservative metadata, physical owned session, verified acknowledgements and rollback-or-poison policy; not a general storage/provider port. |
| `includes/trait-native-receipt-resolution.php` | Older schema-8/two-row receipt resolver. Its result, loading and missing registration-specific autoload fences must not be silently reused as a schema-2/all-ten observer. |

Historical predecessor proof: genuine admission **2766 checks / 14 groups**, eight
restored semantic controls; all 77 application checksums unchanged and fixtures
removed. Those runs are NOT rerun here, and do not prove a registration observer,
retained cross-request origin, closure, real lost-wire/crash or production stop.

## Trusted lookup origin, not an imported recovery capability

Future closed proof needs a private origin retained **before** the original write
attempt can become uncertain. Normative bounded data shape:

- domain `registration_observation_origin_v1`;
- installation: exact blog ID, prefix and database name;
- original scope: site UUID, enrollment binding, authority generation, ledger
  genesis ID and creation time, fixed manifest digest;
- exact original typed registration command, original actor ID, operation ID and
  expected context digest (which must equal that command's context digest).

Primitive/unaliased exact keys only, no objects/resources/closures, nonce/cookie/
session/credentials, raw row payload or native handle. Proposed total encoded cap
4096 bytes, depth six and 256 key/value nodes; canonical numbers/UUIDs/digests and
complete internal binding required. These are proposed new DATA bounds, not a
relaxation of existing request/ledger/parser limits or measured peak memory.

A pure origin object can validate synthetic data but **cannot authenticate who
created it**. A later genuine lifecycle owner must retain its own origin directly
from private successful construction before dispatch, never adopt a posted array,
caller-created origin object, arbitrary provider or current ledger as its origin.
Existing admission does not provide this retention seam; changing it is a separate
approved integration milestone. A poisoned writer is never reopened for observation.
Trusted proof instrumentation may inspect retained non-authorizing data, not inject
an authorizing replacement or publicly export a writer handle.

Initial future proof is process-local only: a separately guarded observer after
the attempted mutation has released/closed its handle. Cross-request persistence,
browser recovery links and automatic registries are NOT supplied by this design.
If the process or exact origin is lost, return unavailable/manual investigation;
do not reconstruct origin from a receipt, search latest, guess IDs, re-enroll,
reset the ledger or mint a replacement mutation. Origin binds the original actor
as historical data; a currently authorized different local admin may observe,
but cannot thereby adopt mutation ownership or reserve/execute the operation.

## Fresh observation construction and authorization

Future private, test-loaded final observer: zero-argument/zero-IO creation, one
attempt consumed before malformed input/guard failure/reentry, no public operation,
provider/clock/target/enablement/handle port. Production remains unaware of it.

1. Exact original POST method and a small read-issuance intent containing only
   `purpose=observe_registration`, `confirm=observe`, `observed_command_id`, `nonce`.
   Select the already retained trusted origin internally by that exact ID; the
   submitted ID never provides scope, authentication or a SQL selector. Unknown
   or missing origin refuses, without protected reads or search.
2. Proposed NEW issuance action:
   `alynt_drime_backups_scan_registration_observe_issue_v2:` + SHA-256 of an exact
   ordered tuple `[server_blog, purpose, observed_command_id]`. Keep it separate
   from existing mutation issuance AND full receipt actions. Current grammar does
   not implement this purpose; do not repurpose register_closed or bypass issuance
   verification just because the request is logically read-only.
3. Pin original genuine core installation, actor/cookie/borrowed physical session,
   UTC and monotonic start before auth callbacks. Install role-only autoload bypass
   and independent exact-role-query fence across ALL callback phases; restore only
   own callbacks/error handler. Genuine current session/manage_options plus initial
   issuance nonce before ANY protected payload. Unsupported profile refuses.
4. Outside locks, generate one fresh genuine UUID for this lookup and a fresh
   <=300s read window. Full form already specified by Request_Contract:
   `contract=scan_registration_observation_v2`, `purpose=observe_registration`,
   fresh `command_id`, original `site_uuid`, original `observed_command_id`,
   `issued_at`, `expires_at`, `confirm=observe`, `nonce`. Raw and once-unslashed
   encoded bodies <=4096, each key/value <=2048, exact canonical grammar. Reject
   lookup ID equal to any origin identity; after capture reject retained identity
   collisions too, never remint/retry to make a collision pass.
5. Use existing full receipt action prefix
   `alynt_drime_backups_scan_registration_receipt_v2:` hashing every ordered typed
   envelope field. Genuine nonce creation and all reparse/exact-envelope checks
   outside locks. Renew independent issuance AND full receipt guards, original
   actor/cookie/request/physical facts and original budgets before BEGIN and after
   owned close. Server-created nonce is binding context, not a new user approval.

No original mutation nonce/envelope is reused. Its expiry may be past; a fresh
read window neither extends that mutation deadline nor resumes it. Existing pure
grammar also knows observe_reservation, but the first genuine observer will reject
that purpose until its own trusted reservation-origin integration is approved.

## Fixed native observation barrier and validation

No prefetch/prepare of an empty-registration candidate. Trusted retained origin
supplies expected scope independently; current rows cannot select a new genesis.
Use a separately and exclusively owned supported native connection, never borrowed
wpdb or the uncertain writer. Metadata-only/session policy reuse must NOT import
admission_write, safety/settings writers or any generic SQL capability.

Future transaction sequence: original guards -> metadata -> guards -> latch
BEGIN-sent -> acknowledged READ WRITE transaction -> ten fixed bounded FOR UPDATE
reads in manifest order -> metadata recheck after safety MDL -> verified session/
budget -> latch COMMIT-sent -> acknowledged COMMIT/idle -> close owned handle.
READ WRITE is a native locking requirement here, not permission for DML. There
must be **zero INSERT/UPDATE/DELETE/DDL**, no generation/receipt/cache publication
write and no durable unresolved marker. Capture ID/binary name/binary autoload/
octet wire string/exact value for every role, rejecting missing/duplicate/over-cap
rows without provisioning. All locks remain held through the last read; all ten
rows therefore coexist at a barrier point for participating writers, not forever.

No WordPress/auth/nonce/UUID/parser/producer/output callback under locks. Only
after acknowledged release AND close: validate fixed manifest/full tuple shapes,
nonautoload rows, complete schema-8 disabled/null-grant chain, settings disabled,
connection/snapshot/queue codecs, full strict schema-2 ledger/receipt history and
exact original scope. Permit the valid retained schema-2 pending operation rather
than applying registration's empty-slot rule. Any unsupported active state, old
writer pending, malformed history or changed site/enrollment/generation/genesis
is unavailable, not adopted or repaired.

Require exact original command/context/operation binding for a matching receipt;
the expected context is private origin data, NOT a value learned from that same
receipt. Validate original full history, not merely a receipt-shaped fragment/hash.
Preserved registration receipt in a valid later intent_reserved record remains
historical registered_closed, never proof that current target bytes match or
effect work completed. Current codec has no superseded/terminal phase; do not
fabricate such a positive to satisfy a test. Renew genuine guards and original
budget before returning any private redacted result; clear all origin/row/handle
references on every exit. No raw rows, IDs, fingerprints, nonce or receipt export.

Proposed ceiling: **ten protected projections**, one BEGIN/COMMIT, zero DML/retry;
original ten-second acceptance budget includes construction, locks and post-close
validation. Existing 2s row / 3s MDL waits and native IO limits retained. Blocking
IO can exceed acceptance; no hard cancellation guarantee. Manifest per-row and
6291456-byte frame cap, 131072-byte ledger and existing parser/history reserves
unchanged. Auth/metadata/session queries are separately bounded, not included in
the ten protected projections. Multiple bounded copies are not a peak-memory claim.

## Results, uncertainty and the closure gap

| Private result | Meaning / limits |
|---|---|
| registered_closed + barrier_verified | Exact original registration receipt in fully valid retained history captured under the acknowledged all-ten barrier. Historical closed bookkeeping only; authenticated=false/support flags remain false, not a current grant, target equality or effect completion. |
| not_observed_at_barrier | Pure not_observed_in_candidate elevated ONLY after this genuine barrier, exact scope/full validation and renewed auth. No rollback/no-effect/retry/cancellation/quiescence implication. |
| domain_conflict | Fully validated matching command ID in incompatible purpose/context/operation; never a receipt for the requested origin, never permission to search or retry. |
| unavailable | Missing origin, bad auth/intent, scope/profile/cap/history conflict, timeout, unknown ack/close or expired read budget. No outcome inferred and no replay. |

Pure DATA results must keep **not_observed_in_candidate**, barrier_verified=false,
closure_proven=false and retry_supported=false. Only the future genuine owner may
apply the native barrier qualifier, never a caller-supplied flag. This is private
evidence, not public execution authority; all public storage/dispatch/execution
flags remain false. Closure_proven stays false for every observation outcome.

| Ordering | Required outcome |
|---|---|
| Earlier writer owns the common lock first | Observer waits; after committed registration it may observe the receipt, or times out/unavailable. |
| Observer owns barrier before an earlier authorized request acquires any lock | It may report not_observed_at_barrier; after release the delayed writer can still commit. |
| Future durable ingress fence wins the same common locks first | Participating old prepared registration must fail full tuple/version/epoch check before UPDATE. Observation alone never supplies this fence. |
| Registration wins before a prepared future closure | Closure's stale full-frame comparison refuses, preserves registration history and makes NO closure claim. Fresh explicit closure preparation is distinct from replay of an uncertain command. |

Definite read-transaction failure may roll back only its owned session; unknown
BEGIN/COMMIT/rollback/close consumes/poisons and closes, with no reconnect/retry.
Successful read COMMIT is an acknowledgement of the barrier transaction, not a
mutation receipt. A post-close auth failure withholds classification; it does not
undo anything in the earlier writer. Expiry, cron cancellation, empty pending
counts, cache eviction, repeated absence or process-local latches cannot close an
invocation that has not reached its common lock. Core revocation is not serialized
by options locks; no exact-COMMIT authorization or hostile restore/ABA guarantee.

## Future durable registration-ingress closure contract (NOT implemented)

Choose a separately versioned closed-ledger successor, not an extra tolerated field
in schema 2 and not reinterpretation of closed=true/null grant. Proposed future
closure profile requires an explicit admission_closed=true, monotone admission
epoch, retained original scope/history and exact closure command/context/receipt.
Its final wire grammar, capacities and explicit schema-2 bridge require a separate
closed-codec/migration approval before implementation; no implicit read migration.
Older strict schema-2 writers must reject the new domain; prepared old writers
must fail exact ledger tuple equality. No mixed-version fallback or grant promotion.

Normative write protocol for that later milestone:

1. Separately authenticated explicit closure command with its own once-generated
   IDs/window/purpose; outside-lock full validation and exact closed successor.
   Closure is a mutation, never hidden inside receipt observation or auto-timeout.
2. Same supported owned session, all-ten lock order and metadata policy as EVERY
   affected registration path. Compare complete captured tuples before one exact
   ledger CAS; preserve every existing registration/reservation/history field.
   Reserve enough bounded closure bytes up front; capacity/overflow refuses,
   never pruning/resetting unresolved data. No new table/default row or repair.
3. Atomic acknowledged closed-ingress/version/epoch plus closure receipt/readback
   is the linearization point. No safety permission-generation change for this
   registration-only fence, no cancellation/effect receipt or terminal promotion.
   A later global scan/authority closure still needs the separately designed paired
   safety-generation/ledger transaction and complete participant/worker accounting.
4. Future participating registrations and reservations must check the new closed
   gate/epoch while holding that barrier, not just during capture, and must never
   rebase/remint onto it. No reopen or fresh consent is defined here. Existing
   pending operations remain unresolved, not cancelled; effects stay disabled.
5. Unknown closure acknowledgement => unavailable; only a separately guarded exact
   closure-receipt observation may confirm historical ingress closure. Absence
   still cannot authorize another close/reset. No automatic retry or compensation.

Even an acknowledged fence proves only **registration ingress closed under proven
participant assumptions**. It is not full process drain, no file/network/upload
activity, all-effects terminal, cancellation, safe uninstallation/downgrade or
production quiescence. Every relevant writer, old binary, ambient worker and
whole-row save must be accounted for; arbitrary privileged SQL/restore cannot be
fenced by a PHP convention. No production activation while any participant is
uncoordinated. This is the explicit closure boundary, not a claim it already exists.

## Source-scoped design review and future acceptance

Resolved gaps: current observation grammar lacks read issuance; submitted IDs lack
trusted origin/genesis; immutable pure origin is not native provenance; old pending
guard would make positives vacuous; two-row resolver/two sweeps are not this barrier;
schema-2 closed is not ingress closure; missing receipt cannot close delayed work.
No runtime defect fixed or canonical Feature/DS3/Database/07A/security workflow
completion claimed by a design document. Toolkit/historical readiness rows remain
unchanged. UI/UX N/A backend-only. Full pre-release still required for a coherent RC.

| Later layer | Required discriminator, not executed here |
|---|---|
| Pure origin/query/result foundation | Positive exact pending registration lookup, including after mutation expiry and valid retained reservation; complete scope/command/context/operation binding, separate read issuance/action domains, exact caps/reference/identity/stale read rejection. Pure absence never barrier/closure/retry. Always-unavailable must fail positives. |
| One-shot dormant lifecycle | Missing/imported/stale origin or replacement actor/request refuses; attempt consumed on every exit; no writable continuation or public bootstrap. Removing a scope/context/lifetime/nonce-domain guard must break a semantic assertion. |
| Genuine core/native proof, later exact fixture approval | Bad issuance/auth zero protected reads; independent full read nonce before BEGIN; bulk-autoload/physical-swap/revocation checks, real all-ten NOWAIT/contention and zero DML; exact receipt and nine unchanged tuples, no core callbacks under locks, untouched application-table audits and all owned cleanup. |
| Competing-process proof | Real writer-first receipt and observer-first absence followed by delayed commit; never fake these with an in-memory schedule. Actual dropped-ack/crash recovery needs its own controlled plan; protocol phase simulations alone do not prove it. |
| Durable ingress fence, separately approved | Both closure-first/stale-old-writer and writer-first/stale-closure orderings on physical storage, exact closed receipt/epoch/version checks, capacity/rollback/unknown outcomes, no history loss or terminal inference; old/nonparticipating writers explicitly bounded. |

## Validation and publication

Executed repository-only on 2026-10-19. These checks exercise unchanged code,
not this proposed observer/closure. No tests added just to count a design document;
historical genuine proof totals stay historical.

- Uploader `php vendor/bin/phpunit --do-not-cache-result --filter
  'RegistrationAdmission|RegistrationNativeAdmissionBoundary|RegistrationNativeReaderBoundaryTest|RegistrationConstructionTest|RegistrationRequestContractTest|ClosedRegistration|FixedCoreRequest|ScanLedgerCheckpoint|ClosedScanEffectLedgerTest|ScanEffectManifestTest|ScanEffectFenceContractTest|ScanTargetCodecTest|ScanObservationContractTest|ScanNativeObserverBoundaryTest|RealClosedWriter|NativeClosedBeginBoundary|NativePairedBoundary'`:
  **265 tests / 3369 assertions**, no skips. Existing pure/API boundary regressions,
  not a new genuine native observer or closure proof.
- Uploader `npm run lint`: **208 PHP files**, passed; existing PHPCompatibility
  comma-array functionWhitelist deprecation only. `npm run build`,
  `npm run verify:runner`, `npm run lint:runner`, `npm run test:dev-tools`
  (**3 tests**) passed; generated runner/assets unchanged.
- Uploader `php ../alynt-drime-backups-dashboard/composer.phar validate --strict`,
  `php ../alynt-drime-backups-dashboard/composer.phar audit`,
  `npm audit --audit-level=moderate`: passed, zero reported advisories/vulnerabilities.
- Dashboard `php vendor/bin/phpunit --do-not-cache-result`: **299 / 1423**, two
  existing local Sodium signing skips. Full Linux CI must cover them. `npm run
  lint` **138 files**, `npm run check:structure`, `npm run build`,
  `php composer.phar validate --strict`, `php composer.phar audit` and
  `npm audit --audit-level=moderate`: passed; assets unchanged, no advisories.
- Both whitespace checks pass; all **11 distinct** quoted source references and
  **three** relative design links resolve. Final scope: dashboard this design,
  predecessor cross-reference and plan; uploader plan only. No runtime/test/schema/
  dependency/version/generated output or toolkit checklist change. No SQL/core
  bootstrap, physical transaction, fixture or new mutation control executed.

Commit/push/exact-head full PHP 7.4/8.3 CI and clean synchronization required for
both owners; final commit IDs/run links reported at handoff without documentation-
only receipt loops. Green unchanged-code CI cannot prove the proposed contract,
native locking/zero-DML, actual authentication, closure or production readiness.

## Implementation-plan progress and next gate

Progress: trusted origin/read-purpose/native-barrier semantics and the exact
observation-versus-durable-ingress-closure boundary are now specified. The next
meaningful milestone is executable dormant DATA contracts, not more maintenance
or immediate closure/activation. No remaining ambiguity is treated as authorization.

Recommended next gated slice: **local-only V2.3 Registration Observation Origin
and Result Safety Foundation**, repository-only, dormant in-memory tests. Implement
bounded immutable synthetic origin, read-issuance/query binding and non-authorizing
result/lifetime contracts with positive/adversarial controls; reuse strict existing
schema-2 history lookup without weakening pending/authorization guards globally.
Keep actual authentication/UUID issuance/native handles/storage/site/fixtures,
production wiring, closure migration/writes, grants/activation/effects/rollback
disabled. Approve that new trust-data contract before genuine lifecycle integration.
