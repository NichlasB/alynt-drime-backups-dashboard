# V2.3 Registration Observation Native Barrier and Private Handoff Design

## Progress checkpoint — fixed native observer / owned proof, 2026-10-19

Uploader `docs/V2_3_REGISTRATION_OBSERVATION_NATIVE_BARRIER_PROOF.md` records the
approved implementation and Plugin Tester local-only evidence: **4247/9**, eleven
assertion-killed/exactly restored controls, all77 application checksums unchanged,
three owned tables removed every run. Fixed ten-row locking sweep, independent
genuine guards/physical identity, zero-DML status, acknowledged close and pending-
aware results now exist privately. Separate NOWAIT primitive proves actual locks;
synthetic history is not an uncertain-admission recovery origin. Production wiring,
grants/activation/effects/rollback remain disabled. Lower design/progress statements
that say unimplemented are historical, not the current barrier implementation state.

Next gated slice: **Plugin Tester local-only V2.3 Native Registration Observation
Contention and Failure Proof**, bounded disposable participating-writer, delayed-
commit/timeout/owned-session failure scope with deterministic coordination and
cleanup. Requires new explicit approval. No global quiescence, durable closure,
safe retry, lost-wire/crash or production-readiness conclusion follows yet.

## Progress checkpoint — owned genuine handoff auth/cleanup, 2026-10-19

Uploader `docs/V2_3_REGISTRATION_OBSERVATION_HANDOFF_AUTH_PROOF.md` now owns the
approved Plugin Tester local-only genuine-core candidate handoff proof: **2946/8**,
ten assertion-killed/exactly restored controls, all **77** application tables
unchanged and three owned fixture tables removed on every run. Current complete
history is synthetic candidate DATA; native observer transactions remain disabled.
No production wiring, admission writes, grants/activation/effects/rollback changed.

Next gated slice: **Plugin Tester local-only V2.3 Fixed Native Registration
Observation Barrier Foundation and Owned Zero-DML Proof**, fixed read-only all-ten
transaction/locking/close and owned fixtures/cleanup per this design, preserving
application state. Requires new explicit transaction-scope approval. No native
barrier, physical-session, uncertain-admission or durable ingress-closure proof
follows from genuine authentication alone. Lower checkpoints remain historical.

## Progress checkpoint — guarded private API integration, 2026-10-19

Uploader `docs/V2_3_REGISTRATION_OBSERVATION_GUARDED_HANDOFF_FOUNDATION.md` now owns
the approved isolated-API same-owner handoff integration; excluded uploader
`ba213031937078f226ed9d9e5b6efe6899d7fddb`, dashboard
`6deb33ef615a7f0cac0afa7adaad19936bfd18d7`. Own guarded construction facts staged
before origin cleanup, independent read guards renewed before claim/after finish,
original clocks and consuming construction-only cleanup preserved. New
**14 / 1701**, related **336 / 7087**, no skips; **10** assertion-killed/restored
controls. Canonical source-scoped Light/Bloat/final Security only. All results
remain candidate-only, no native/execution qualification or production wiring.

Next gated slice: **Plugin Tester local-only V2.3 Guarded Observation Private
Handoff Owned Authentication and Cleanup Proof**, exact owned users/ten-row
fixtures/testing/cleanup, genuine core boundary/cleanup, no observer transaction,
admission write or application-state change. This requires fresh site/fixture
approval; neither genuine authentication nor the native barrier is established by
API doubles. Lower checkpoints/design remain scoped historical records.

## Progress checkpoint — private handoff / candidate DATA, 2026-10-19

Approved in-memory foundation now implemented in uploader
`docs/V2_3_REGISTRATION_OBSERVATION_HANDOFF_FOUNDATION.md`; excluded uploader
`d6318fa547f77754976a0fe813b9d251a28d1576`, dashboard
`91c51c41bfac0a1739a5081525ca062d5e655826`. Bounded one-shot private DATA handoff
and complete pending-aware current-frame candidate classification: **23 / 1045**
new, related **322 / 5388**, no skips; **11** assertion-killed/exactly restored
controls. Canonical source-scoped Light/Bloat/final Security, not native/physical
proof or whole-plugin release readiness. Existing source/wiring unchanged.

No genuine constructor-to-handoff integration or native barrier implemented.
Next gated slice: **local-only V2.3 Guarded Observation Private Handoff Integration
Safety Foundation**, isolated API tests only, own original facts transferred
before cleanup without weakening the existing construction-only path. No actual
WordPress/native/storage operations or qualification, grants/activation/rollback.
The original design below still governs later genuine/native proof boundaries;
its original next-gate section is historical, not a claim that integration exists.

Approved 2026-10-19, R3 **repository-only/design-only**. Dashboard owns this
contract; uploader owns the dormant construction and future observer. Excluded
review baselines are uploader `e6b24f18a4b9060ce0362abf7e93a50e227d17b8` and
dashboard `d964231688d1deecc8c0132c81df0f78b935433c`. Review the complete delta
from these commits, not a later validation or documentation receipt. Versions
remain uploader 0.5.25 and dashboard 0.1.77.

Refines the [receipt observation / closure design](V2_3_REGISTRATION_RECEIPT_OBSERVATION_CLOSURE_DESIGN.md)
and [native admission design](V2_3_CLOSED_REGISTRATION_NATIVE_ADMISSION_DESIGN.md).
Uploader `docs/V2_3_REGISTRATION_OBSERVATION_AUTH_PROOF.md` records the preceding
owned origin/construction proof, not an observation transaction proof.

## Scope and implementation-plan decision

Specify the private transfer of original facts, independent read authorization
and the original observation deadline into a fixed all-ten native read barrier.
Resolve lifecycle, pending-history, result, uncertainty and evidence boundaries.
Validate unchanged repository code and publish the design/plan records only.

No source/test/schema/dependency/version changes, site access, LocalWP/WordPress
bootstrap, SQL, fixtures/users, storage writes, endpoints, production wiring,
grants, activation, effects, rollback, package, tag, release or deployment.
Site-target confirmation and Novamira discovery are not applicable: no site is
accessed. Previously approved owned proofs are not rerun under this approval.

Implementation-plan progress: the open observation milestone now has a specified
construction-to-barrier ownership contract and pending-aware acceptance criteria.
It still has **no native receipt observer**, durable ingress closure or runtime
activation. The next milestone is a new implementation gate, not maintenance.

## Source-backed findings and reuse limits

All paths in this section are uploader-relative; source is unchanged by this
design. These are design integration gaps, not claims of active production bugs.

| Existing source | Observed constraint | Design decision |
|---|---|---|
| `includes/class-registration-observation-construction.php` and `includes/trait-registration-observation-native-capture.php` | Both construction cleanup paths clear origin; the native wrapper returns a private blueprint only after inner cleanup. | Never import that returned blueprint as retained origin or barrier evidence. A later integrated owner must hand off privately before cleanup. |
| `includes/class-registration-observation-origin.php` and `includes/class-registration-observation-lifetime.php` | Bounded DATA matching and one-shot lifetime do not authenticate a caller or acquire locks. | Keep pure candidate classification separate from the native owner's evidence. |
| `includes/trait-registration-eligibility.php` | `eligible_registration()` requires zero pending operations. | Do not reuse it unchanged for observation; valid registered/reserved history must remain observable. |
| `includes/trait-registration-admission-candidate.php` and `includes/class-registration-admission-handoff.php` | Admission compares a complete original before-image and proposal. | Observation validates the current complete closed frame and original identity, not equality to the pre-write frame. |
| `includes/trait-registration-admission-transaction.php` and `includes/trait-registration-admission-storage.php` | Admission includes CAS DML/readback and a larger row budget. | Do not compose these transaction/storage traits into a non-writing observer. |
| `includes/trait-native-closed-begin-session.php` and `includes/trait-native-options-metadata.php` | Owned-session policy, metadata validation and sent-before-I/O uncertainty handling already exist. | Preserve those policies through fixed private read-only operations; never export a writer/SQL handle. |
| `includes/class-closed-registration-contract.php` and `includes/trait-closed-registration-records.php` | Strict schema-2 history supports registered and first reserved-intent phases, not fabricated terminal/superseded receipts. | Use complete canonical history plus exact original command/context/actor/operation matching. |

## 1. Same-owner construction and one-shot handoff

The future integrated path is a dedicated fixed private coordinator, not an
endpoint or caller-supplied continuation. It owns origin retention, read
construction, barrier phases and final cleanup within one invocation. An outer
attempt latch is consumed before guards; refusal, callback reentry or exception
cannot reset it. No `retain_origin=true` mode, public origin getter, serializable
capability, injected observer/provider or callback is permitted.

Existing construction-only paths must retain unconditional cleanup and their
current refusal semantics. Later approved integration may factor deterministic
private construction work so the integrated path can transfer its own facts
before its own cleanup. It must not call today's consuming constructor and then
reconstruct origin from the returned blueprint, raw receipt or a new capture.
It must not reset `observation_attempted` or weaken an existing `finally` block.

The logical transition is:

```text
own origin -> independent read guards / own lookup construction
           -> private handoff staged -> claim once BEFORE native I/O
           -> native barrier -> close -> parse / renew guards -> private result
           -> unconditional clear / consumed
```

Staging requires the exact original small form and own full read construction,
own retained origin, pinned installation/manifest, original clocks and retained
issued-identity set. The receiving phase claims once and removes the staged slot
before comparison or I/O. Only the current private stack retains necessary facts
until final validation; no later invocation can retrieve or revive them. A
callback that reenters while guards are running poisons the outer attempt.

Keep three categories separate:

- Nonce-free bounded origin: original typed `register_closed` command, historical
  actor/operation/context, exact installation, genesis scope and manifest digest.
  The existing 4096-byte, depth-6, 256-node primitive/unaliased limits remain.
- Private current-request facts: exact small/full forms and their independently
  derived actions, one fresh lookup ID and read interval. Each existing form's
  raw/once-unslashed bounds remain unchanged. These facts must never be logged,
  persisted, returned through a public DTO or placed in origin.
- Genuine coordinator state: core identity, current actor/cookie, borrowed auth
  physical-session identity, fences, owned native connection and phase latches.
  None is a field accepted by a pure DATA model as proof of authenticity.

A future pure handoff model may accept synthetic primitive labels to test exact
binding and consumption. It must say DATA-only and cannot return native evidence.
Any aggregate retained DATA encoding must additionally fit 16384 bytes, depth 8
and 512 key/value nodes; this does not relax any smaller existing sub-contract
limit. No objects/resources/references/floats, handles, callbacks, raw frames,
credentials, session tokens or mutation continuation belong in that model.
The native handle is exclusively coordinator-owned, never handed to it.

Origin must ultimately be retained before the original potentially uncertain
admission write. That real admission-to-observation lifecycle is **not wired by
the preceding construction proof or by this design**. A later owned observer
proof may use explicitly labeled fixtures; it cannot claim recovery from a real
uncertain write without separately proving that lifecycle. Lost origin/process
death means unavailable/manual investigation, never latest-receipt search,
reminting, enrollment reset, reconnect-and-retry or importing caller provenance.

## 2. Independent authorization and clocks

Only POST `observe_registration` / `confirm=observe` is supported in the first
lane. Reject `observe_reservation`, extra/malformed/aliased forms and a different
observed command. Preserve the two independent read nonce domains:

- Small intent issuance: `alynt_drime_backups_scan_registration_observe_issue_v2:`
  plus SHA-256 of the existing ordered blog/purpose/observed-command tuple.
- Full query: existing `alynt_drime_backups_scan_registration_receipt_v2:` binding
  the entire typed `scan_registration_observation_v2` envelope.

Do not substitute the mutation issuance or mutation command nonce. The current
authenticated observer may differ from the historical mutation actor; fresh
authentication does not transfer ownership of the mutation or change its facts.

The coordinator pins original core/blog/table/installation facts, current actor,
cookie and borrowed auth-session physical identity. Preserve the genuine-core
session/manage-options checks, role-only autoload bypass and independent exact
role-only options-query fence across **every callback phase**, including renewed
guards. No protected options payload reads before authorization. No caller
snapshot or minimal role lookup substitutes for those guards.

Authorize small intent before UUID issuance. Generate exactly one lookup UUID;
reject collision with all retained original/reserved/genesis identities and,
after capture, complete current history. Never retry generation on collision.
Construct and reparse the exact full envelope, bind both forms to original
observed ID/site, and renew both genuine nonce domains and physical auth identity
before BEGIN. Renew them again after native close before accepting any result.
No WordPress/auth/nonce/UUID/parser/provider callbacks while locks are held.

Latch the outer observation monotonic start and UTC before construction; retain
that same start through handoff, metadata, locking, close, parsing and final
guards. All observations must be nondecreasing and within the original strict
10-second acceptance budget. Inner construction clocks cannot renew that budget.
The fresh read query remains inside its own at-most-300-second interval and is
revalidated after close. Original mutation expiry may be in the past when
observing historical registration; it is never extended or used to permit replay.

## 3. Native barrier protocol (specified, not implemented)

Use a separately opened, exclusively owned connection to the pinned installation;
never the borrowed WordPress authentication connection or the uncertain writer's
connection. Preserve the existing supported 64-bit, single-site, mysqlnd,
utf8mb4/loopback MariaDB profile and refusal of unsupported drop-ins/cache/schema
shapes. Do not broaden the profile to gain a successful result.

The fixed protocol is:

1. Complete and renew authorization, physical identity and original budget.
2. Configure only the owned idle native session: READ COMMITTED, READ WRITE,
   2-second row-lock wait and 3-second metadata-lock wait, with the existing
   durability/role/session checks. Validate exact options table/index metadata,
   no triggers or inbound/outbound foreign keys, and complete metadata visibility.
3. Renew guards outside locks; latch `begin_sent` **before** sending START
   TRANSACTION READ WRITE. Only acknowledged begin with exact owned-session facts
   transitions to `transaction_owned`.
4. Read exactly one fixed ten-role sweep with bounded `SELECT ... FOR UPDATE`,
   safety first, manifest order. Hold every acquired lock until final release.
   After safety obtains metadata protection, recheck the exact metadata profile.
   Under locks do only fixed native queries, bounded tuple copying and primitive
   session/budget checks; parse full row codecs/history only after close.
5. Confirm all ten complete tuples, original budget and owned transaction state.
   Latch `commit_sent` before COMMIT AND NO CHAIN NO RELEASE. Require acknowledged
   idle state on the same owned physical session, then successful owned close.
6. Parse/validate the complete captured frame, match original facts, renew genuine
   guards and clocks outside locks, classify privately, clear everything.

READ WRITE is needed for locking reads; it grants **no application DML authority**.
There is no INSERT/UPDATE/DELETE/DDL, ledger/receipt/safety marker, cache publication,
generation bump, admission CAS or readback sweep. Reuse validated fixed native
policies, not the write-capable admission trait/handle or an arbitrary SQL port.

The observation payload budget is **10 reads**, not construction's two sweeps or
admission's up-to-40 reads. Preserve complete tuple identity/name/autoload/octet
checks, unique row IDs, binary fixed names, LIMIT 2, all existing role/parser caps,
ledger cap 131072 and aggregate frame cap 6291456. Scope/metadata/session queries
are separately fixed and bounded, not an excuse for extra payload sweeps,
discovery, pagination or retries. The 10-second budget includes their cost.
Native timeout settings bound waits, not all wall time: a blocking I/O may overrun;
such a result must be rejected, not described as hard-cancelled at 10 seconds.

The barrier establishes coexistence of the captured frame at the final locked
read for writers participating in the same lock protocol. READ COMMITTED alone,
two unlocked equal sweeps or a supplied manifest hash do not establish it.
Core authentication is not serialized by these options locks; no auth-at-COMMIT,
hostile-SQL exclusion, restore-ABA or global quiescence proof is claimed.

## 4. Pending-aware frame validation and result qualification

After native close, validate every manifest tuple and codec, disabled settings,
complete valid schema-8 closed provenance with null grant/no old writer pending,
exact original installation/genesis/authority scope and complete canonical schema-2
registration history. Preserve current alias/size/depth/canonicalization limits.
Perform the same connection, snapshots and queue/active/uploaded/failed cross-role
data checks as the existing complete-frame contract; do not invent a shorter
minimal safety-row acceptance path.

Do **not** require `eligible_registration()`'s zero registration-pending count.
Both valid `registered` and first `intent_reserved` history retain the original
registration receipt and must be accepted as candidates for that receipt. This
does not admit a schema-8 old writer pending, enable settings, execute a reserved
effect or support a reservation-observation request. No terminal, superseded or
closed-ingress positive exists in the current strict grammar.

Do **not** compare the current frame byte-for-byte with the original mutation's
pre-write frame or require its old ledger revision/dependency digest to remain
current. A successful registration necessarily changes that frame. Instead check
the current complete valid history and original command's full typed bytes,
context digest, historical actor and operation ID against retained origin. Never
learn the expected original context/actor from the receipt being inspected.

| Private result after all native and renewed-guard checks | Meaning |
|---|---|
| `registered_closed`, `barrier_verified=true` | Exact historical closed registration is retained in a valid current frame at the barrier, including supported reserved-intent history. Not effect completion or new write permission. |
| `not_observed_at_barrier`, `barrier_verified=true` | Exact original command absent from a valid complete frame at that point only. A delayed writer may commit after lock release. |
| `domain_conflict`, `barrier_verified=true` | Complete valid history contains the observed ID but conflicts with retained purpose/context/full command/historical actor/operation. Invalid history is not upgraded into conflict evidence. |
| `unavailable`, `barrier_verified=false` | Missing origin, invalid frame/scope/auth, unsupported profile, budget failure, reentry or any uncertain phase; no historical/absence claim. |

Existing pure origin/lookup classifications remain candidate DATA, always with
`barrier_verified=false`. Only the genuine fixed native owner may attach barrier
qualification, after its own acknowledged close and final guards. No function
accepting a caller boolean, serialized token, synthetic phase transcript or
native connection can turn candidate DATA into evidence. A future pure model's
valid candidate path must still return no native certificate.

All results remain private: no public support payload, raw rows/SQL/nonce/cookie,
actor/session token, native handle, auth capability or writer continuation.
Preserve `authenticated=false` for DATA result contracts; authentication belongs
to the genuine owner's control flow, not an exported DATA assertion. Closure,
retry, replay, drain, effect/rollback authority are false/absent in every result.
Even a fully verified absence cannot resolve an in-flight invocation's lifetime.

## 5. Failure, consumption and uncertainty

Allowed native phases are `ready -> begin_sent -> transaction_owned ->
commit_sent -> barrier_released -> closed`, followed by validation/result. Names
are logical design states, not an existing API. Every terminal path clears
origin, request facts, captured bytes and handle references; diagnostics are
redacted. A refused or poisoned owner cannot be reset, cloned into a continuation
or retried. No destructor-based fallback grants success.

- Before BEGIN: close owned resources, consume/refuse. No rollback on borrowed
  WordPress connections and no alteration of their session policy.
- Definitely owned pre-COMMIT transaction: permit fixed rollback of **this read
  transaction only**, with sent latch and acknowledged idle state, then close.
  It does not roll back an earlier admission writer; result is unavailable.
- Unknown BEGIN, COMMIT, rollback acknowledgement, physical identity or close:
  poison/consume, best-effort owned close, unavailable. Never reconnect, send
  speculative rollback to a different session, rerun a sweep or report absence.
- Post-close parsing/auth/time failure: unavailable even if the barrier was
  physically reached. No positive result survives failure of the final guards.
- Callback reentry, replaced global/core/auth facts, loss of either independent
  nonce guard or unexpected options query: fail closed before protected I/O, or
  invalidate the entire attempt if discovered in a renewed callback phase.

Durable ingress closure/draining remains a separately versioned writer/migration/
participant contract. Schema-2 `closed=true` is closed proof mode, not closed
registration ingress. This design does not establish safe retry, recover lost
origin, authorize cleanup or change schedules.

## 6. Required non-vacuous evidence and next gates

The next implementation must include meaningful valid **candidate** paths, not
an owner that always returns unavailable and passes only refusal tests. At the
DATA layer, valid pending/retained-reservation, absent and conflict fixtures must
classify distinctly while never claiming a native barrier. At the genuine layer,
owned fully valid authorized cases must reach a qualified result; fail-closed
profiles must not be weakened or given artificial success hooks to achieve that.

Required independent tests/control removals, staged by proof level:

| Proof level | Required checks | Does NOT establish |
|---|---|---|
| In-memory handoff/result foundation | Exact original forms/origin/start/identity binding; consume-before-refusal/reentry; alias/size/depth and stale/malformed intent; pending and retained-reservation positives; wrong historical actor/operation/context; expired mutation vs fresh query; no native certificate even with forged flags/transcripts. | Genuine core authentication, protected-read ordering, native locks/physical transactions. |
| Isolated API integration | Assert small guard before UUID, both nonce domains before protected I/O, original clock carried through handoff, independent post-close guards, no cleanup bypass or exported write handle; guard removals must fail semantic assertions. | Genuine WordPress users/session/authentication or real database behavior. |
| Separately approved owned WordPress/native proof | Genuine auth/role fence and physical-session pinning; ten locked rows in order, zero DML/DDL, metadata recheck, pending-aware classifications, exact cleanup/application-state reconciliation; each missing required guard rejected by assertion-killed controls. | Real uncertain admission recovery, multi-process serialization, lost-wire/crash safety unless independently exercised. |
| Separately approved contention/failure proof | Competing participating writers, delayed commit after absence, lock timeout, unknown acknowledgements/close; distinguish fault injection from actual lost-wire/crash evidence. | Global quiescence, durable ingress closure, hostile SQL, restore-ABA or rollback safety. |

For every guard-removal control assert the semantic failure (ordering, wrong
domain accepted, stale request, early protected read, state/handle leak), not
merely a source string or thrown exception caught by the owner. Restore exact
source bytes and rerun the real checks. In-memory transcripts and mocked SQL
calls cannot be labeled Database Review or physical transaction/zero-DML proof.

### Review disposition for this design

Resolved on source inspection: consumed-origin gap; inappropriate zero-pending
reuse; inappropriate old-before-image equality; accidental admission DML reuse;
deadline renewal at handoff; caller-minted barrier flags; and absence-as-closure.
These are specified constraints, **not implemented fixes or new runtime proofs**.
Review scope is this design and affected plan records at the stated baselines.
No canonical code-review/whole-plugin release-readiness row is refreshed by this
design checkpoint. FULL_PRE_RELEASE remains reserved for a coherent release
candidate before packaging/publishing work containing this feature.

### Validation record

Local unchanged-code validation on 2026-10-19:

- Uploader related observation/admission/closed-writer/native-boundary PHPUnit:
  **299 tests / 4343 assertions**, no skips; PHPCS **215 files**, build, runner
  verification/syntax and developer-tool **3 tests** pass. Existing PHPCS
  comma-separated `functionWhitelist` deprecation only, not a new finding.
- Dashboard full PHPUnit: **299 tests / 1423 assertions**, two existing local
  Sodium-signing skips; PHPCS **138 files**, structure and build pass. Linux CI
  must independently cover the Sodium cases rather than counting skips as proof.
- Both Composer strict validation/audits and npm moderate-threshold audits pass,
  no advisories. Generated assets/runner, manifests, lockfiles, source and tests
  unchanged; whitespace check passes. All **13** named source paths and **17**
  relative links in the dashboard design/plan records resolve; both excluded
  baselines exist and remote master heads match before publication.

Publication requires exact-pushed-head CI success in both repositories, not a
previous green run; report actual run IDs/results in the final handoff. These
regressions validate unchanged code, not the proposed observer. No owned site
proof, SQL, mutation-control experiment or native transaction was run here.

### Recommended next gated slice

**Local-only V2.3 Registration Observation Private Handoff and Barrier Result
Safety Foundation**: implement the bounded one-shot DATA handoff and pending-aware
complete-frame candidate/result contracts with in-memory tests, meaningful valid
cases and semantic guard-removal controls. Keep native certification impossible,
no actual WordPress/core/native connection or storage operations, no production
wiring, grants, activation or rollback. This advances the new ownership contract
without prematurely authorizing the genuine native observer; it requires a new
explicit implementation approval. Later genuine integration/owned lock proof and
admission-origin lifecycle remain separate gates.
