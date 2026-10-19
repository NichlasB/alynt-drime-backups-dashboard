# V2.3 Closed Registration Native Admission and Locked Revalidation Design

Approved 2026-10-19, **repository-only/design-only**, R3 trust/concurrency design.
Owner: dashboard design; prospective implementation: companion uploader. Excluded
Git review bases: dashboard `a2621078d7181f4d576f2a01781a63e15758cf93`, uploader
`ace53355aabc1c530e200a62f5cb61e3b08bdd9d`. Review the complete design/plan delta
against these bases, not a documentation receipt tail. Versions stay 0.1.77/0.5.25.

Refines [registration and reserved intent](V2_3_CLOSED_REGISTRATION_RESERVED_INTENT_DESIGN.md)
and [native storage/participants](V2_3_NATIVE_SCAN_EFFECT_STORAGE_PARTICIPANT_DESIGN.md).
Uploader `docs/V2_3_REGISTRATION_NATIVE_READER_PROOF.md` owns the predecessor.

## Scope and decision

Map actual capture/construction, pure successor and revision-only native source;
define the missing trusted handoff and locked admission; review failure/race cases;
run unchanged-code regression checks; commit/push/verify exact-head CI. No runtime
or test changes, site/LocalWP/Novamira/SQL/browser/user/session/fixture access,
storage write, public endpoint, production loading, grant, activation, effect,
reservation execution, cancellation, rollback, package/release/tag/deploy. A site
target/Novamira check is not applicable: this task accesses repositories only.

Decision: a future **separate fixed private registration coordinator**, not a new
public consume method or extension of the schema-1 checkpoint. Its first native
write scope is only an existing owned schema-2 ledger's `register_closed`
bookkeeping transition. This design neither implements nor approves that write.
Reserve/observe native integration, row provisioning and production wiring remain
separate gates. Do not bundle them to obtain an apparent end-to-end success.

The existing reader's returned array is **non-authorizing candidate data**. Two
equal sweeps are not a snapshot; the genuine predecessor proof deliberately changes
an owned row after capture and still constructs a blueprint for the old frame.
It must not be passed to an independently callable writer as a capability. No
posted proposal, digest, nonce, UUID, provenance label, schema-8 null grant or
synthetic history can authorize storage or scheduled scanning.

## Source map and proof limits

### Approved owned native admission implementation/proof — 2026-10-19

Uploader `docs/V2_3_REGISTRATION_NATIVE_ADMISSION_PROOF.md` now owns the separate
dormant private native coordinator and explicitly selected Plugin Tester proof.
Exact local target/owned fixture creation/testing/cleanup and ledger-only native
writes approved. Genuine **2766 / 14**, eight assertion-killed/restored controls;
native checkpoint **740 / 13**, reader **700 / 11**, guard **266 / 11**, closed-BEGIN
**169 / 12** rerun green. Every run preserved **77** application checksums and
removed all three owned fixture tables. Source-scoped canonical reviews/QA/limits
recorded there; no production caller/public surface or effects enabled.

The prospective wording below preserves the original design baseline, not a claim
that this completed isolated milestone is still unimplemented. Actual schema-2
closed registration CAS/readback/acknowledged commit is proved; unknown protocol
simulations are not real lost-wire/crash proof. Auth revocation, ABA and production
participant limits below still apply. Next gated slice is **local-only V2.3 Guarded
Registration Receipt Observation and In-Flight Closure Design**, repository/design
only, before implementing the missing guarded observer/delayed-work contract.
Application state, wiring, grants/activation/execution/rollback stay untouched.

### Approved candidate plan / data handoff implementation — 2026-10-19

Uploader `docs/V2_3_REGISTRATION_ADMISSION_PLAN_FOUNDATION.md` now owns the dormant
immutable candidate/equality and consumed private DATA lifetime. New in-memory
**9 / 346**, related **262 / 3300**, six restored assertion-killed controls.
No actual auth/native handle/IO, genuine owner-origin or locking/commit proof;
existing reader/constructor/guards and production loading unchanged. The pure
factory is not the proposed native coordinator and its output cannot be imported
into the handoff. Next gated slice is **Plugin Tester local-only V2.3 Closed
Registration Native Admission and Owned Ledger Commit Proof**, requiring exact
owned-fixture/write approval, not authorized by the in-memory-only implementation.

All source paths in this document are uploader-relative; none are changed here.

| Source | Existing fact / consequence |
|---|---|
| `includes/class-registration-native-reader.php` | Private one-attempt twenty-read/two-sweep capture, role-only auth option fence, original physical auth frame, existing constructor; closes reader before nonce construction. Returns no captured frame or writer handle. Cannot be connected by merely calling a new consumer on its result. |
| `includes/trait-registration-construction.php` | Complete eligibility, ten genuine UUID roles minted once, full command/context and nonce formed outside locks, original issuance guards renewed. Its consumed latch cannot simply be cleared to allow a writer. |
| `includes/class-registration-request-contract.php` | Small issuance versus full command and observe purposes use separate actions; exact bounded forms. Parsing is not authorization. The full command omits most privately reserved identities. |
| `includes/trait-registration-eligibility.php` | Complete closed schema-8 chain/settings/all-ten data validation; schema-2 genesis/scope and empty pending slot; full-frame digest, no IO. Exact tuple equality can bind this pre-lock semantic decision to locked bytes. |
| `includes/class-closed-registration-contract.php`, `includes/trait-closed-registration-data.php`, `includes/trait-closed-registration-records.php`, `includes/trait-closed-registration-transitions.php` | Strict pure schema-2 context/receipt/successor validation. Current profile permits only empty -> one pending operation, not sixteen real operations or a terminal transition. Historical source labels remain data, not an auth seal. |
| `includes/class-scan-ledger-checkpoint.php`, `includes/trait-scan-ledger-checkpoint-storage.php`, `includes/trait-scan-ledger-checkpoint-transaction.php` | Separate schema-1 revision-only owner: fixed all-ten locking/CAS/readback/acknowledgement discipline, not schema-2 registration or its trusted handoff. Do not widen or call it as a generic writer. |
| `includes/trait-real-closed-writer-core-guard.php`, `includes/trait-native-observer-session.php`, `includes/trait-native-closed-begin-session.php` | Genuine core auth outside locks, original borrowed session facts and exclusive native lifetime. Shared guards/older owners remain unchanged; the new lane must explicitly retain the reader's protected bulk-autoload fence. |

Historical genuine evidence: native reader **700 checks / 11 groups**, construction
**851 / 10**, core guard **266 / 11**, schema-1 checkpoint **740 / 13**. Those are
previous owned-fixture runs, not rerun here and not schema-2 native admission,
cross-request handoff, physical registration commit or lost-wire/crash proof.

## Trusted same-stack handoff, not a bearer object

Future coordinator is test-loaded only, final, zero-IO/zero-argument construction,
noncloneable/nonserializable, without public operation/provider/target/actor/clock/
enablement/continuation ports. Private PHP visibility is not a sandbox against
hostile same-process PHP/reflection or privileged database mutation.

1. Consume the coordinator's attempt at entry, including malformed request,
   failed guard, reentry, exception or unsupported profile. Pin original method,
   actor/cookie, genuine core object/blog/tables, borrowed mysqli object and
   physical session facts **before auth callbacks**, original UTC observations
   and original monotonic start. Nothing resets these on a later phase.
2. Use only the fixed small `register_closed` issuance form. A proposed private
   owned-proof admission invocation must be explicitly selected by trusted test
   wiring; it must not silently turn today's inspection/construction operation
   into a write request. There is still no browser/public mutation operation.
   The issuance nonce shows the limited request purpose; the server-created full
   command nonce is binding/integrity context, **not a second user approval**.
3. Carry original facts and the complete captured ten-row frame as private owner
   state through capture, eligibility and construction. All identity generation
   remains genuine core, ten roles once, before locks; no remint/collision retry.
   Retain the original frame and exact constructed proposal, not a caller-created
   replacement or a hash-only substitute. The current returned array does not
   contain this owner state; future integration must implement that lifetime.
4. Internally form/reparse the full command form with its new nonce; exact-match
   typed envelope, action, nonce and all private proposal/actor/scope/identity
   fields to what this owner constructed. Renew original issuance guard and the
   separate full command guard, with original physical/session/request checks
   around callback phases. Never accept an issuance nonce in the command domain,
   reserve/observe nonce in the register domain, or arbitrary posted full form.
5. No export/import, global registry, cache, transient, persisted continuation,
   caller-selected closure or independently reusable token. The coordinator calls
   its fixed private next phase directly while owning the same stack. A pure
   plan may contain synthetic DATA for tests but can never create/adopt that
   trusted owner. Both parsed data and owner-origin continuity are required.

Proposed lifetime: `unattempted -> capture -> constructed -> prepared ->
prebegin_authorized -> begin_sent -> transaction_owned -> commit_sent -> committed`,
with definite `rolled_back` or terminal `poisoned/unavailable` exits. These are
private invocation states, not stored ledger phases. Once consumed stays consumed;
success, refusal and uncertainty never expose a continuation or second attempt.
Do not reuse the existing constructor's `active/consumed` flags as an excuse to
clear them: outer attempt ownership and internal phase ownership are distinct.

## Pre-lock preparation and authorization ordering

The local auth option fences must span **every** core callback phase of the new
lane, not just the first reader. Preserve genuine exact role-option fallback and
independent denial of other options-table queries; restore only installed callbacks
and the previous error handler in `finally`. No fabricated roles/capability map.
Bad issuance/capability/session must cause zero protected payload reads; bad full
command after capture must cause zero BEGIN/UPDATE. Do not reuse the old checkpoint
and assume its historical proof covers the reader-specific bulk-autoload fix.

Close the owned read-only capture connection before core nonce work. A future
writer is separately created and exclusively owned, never the borrowed `$wpdb`
authentication handle. Retain the supported single-site/native-cache/no-drop-in,
loopback MariaDB 10.6/mysqlnd/utf8mb4/64-bit and metadata profile; unsupported means
refusal, not privilege/configuration repair or a compatibility-floor change.

Prepare one successor using the existing strict pure registration contract on the
captured ledger and complete eligible frame. Pin the proposed `recorded_at` UTC
instant **outside locks**, after construction and before final pre-BEGIN guards;
it denotes preparation of this historical closed record, not database COMMIT time.
Validate original issue/deadline, current positive monotonic UTC, scope/genesis,
expected generation/revision, all IDs/collisions, closed/no-grant eligibility,
participant digest and actual encoded successor/reserves before any BEGIN.

No reparse, remint or successor regeneration under locks. If the original frame
is no longer current, refuse instead of rebasing. The future immutable pure plan
binds before tuples, proposal, successor and receipt; its hashes do not authorize IO.
The schema-2 record keeps only the existing bounded fields, never nonce/cookie/
session fingerprint/raw row/SQL handle. No schema bump is proposed by this design.

Create/configure/check the fresh writer outside locks, then renew original
issuance AND full command guards with original actor/cookie/request/physical facts,
complete plan equality and original time budgets immediately before BEGIN. Metadata
queries are not protected payload reads. No protected writer-side prefetch is
needed: the captured frame remains the only prepared before-image.

## Locked revalidation and the sole permitted future delta

The following is a proposed transaction, **not implemented or executed here**.

1. Latch `begin_sent` before native IO. Only acknowledged START plus exact owned
   transaction/session policy establishes `transaction_owned`. No adoption of
   ambient transactions or reconnect; preserve 2s row/3s metadata lock waits,
   native connect/read timeouts and verified supported durability settings.
2. Lock all ten existing rows in manifest order: safety, settings, connection,
   ledger, WPvivid snapshot, outbox snapshot, queue, active, uploaded, failed.
   SQL bounds lengths before fetching values, binds fixed names, detects missing/
   multiple rows and requires exact binary name. No callback-selected identifiers.
3. Revalidate supported schema/index/trigger/FK policy after the first safety lock
   acquires options-table MDL. Compare **each full tuple** against captured data:
   ID, binary name, binary autoload, octet-count wire string, exact value bytes,
   plus pinned blog/prefix/database/frame and owned physical/session facts.
   A matching ledger revision/hash alone is insufficient. Keep earlier locks held
   until the last comparison completes; only then may the successor be used.
4. Original UTC/deadline and monotonic acceptance checks bracket every blocking
   operation and precede UPDATE/COMMIT. The single original ten-second budget
   includes capture/construction, not a new ten seconds for the writer. If it
   leaves insufficient time, refuse; do not increase/reset it to obtain success.
   Blocking IO can exceed that acceptance budget; it is not a hard wall timeout.
5. Attempt at most one prepared ledger-only UPDATE with original option ID,
   exact binary name/autoload/old-value predicates. Require acknowledged execution
   and affected_rows=1. No INSERT/upsert/delete, generation/settings/safety write,
   row creation, snapshot/queue persistence or compensation. Successor increments
   only ledger revision, appends the one closed registered operation and existing
   `registered_closed` receipt; all effect slots remain reserved/unexecuted.
6. Read back all ten rows while locks remain owned. Ledger must exactly equal the
   prepared bytes, old ID/name/autoload and new length; the other nine tuples must
   be byte-for-byte unchanged. Verify owned transaction/policy/budgets again,
   latch `commit_sent` **before** IO, require COMMIT acknowledgement AND native idle
   policy. Idle by itself is never proof of acknowledged commit.
7. Close/release the owned handle before any WordPress callback/output. Renew
   original guards/request/borrowed physical facts/budgets before withholding or
   allowing a private positive historical decision. Do not publish raw receipts,
   blueprint/lookup IDs or prepared bytes in public support diagnostics.

Resource ceiling proposed for this combined path: **40 protected projections**,
20 capture + 10 locked compare + 10 locked readback; one BEGIN, one UPDATE attempt,
one COMMIT attempt, no retry. The old checkpoint's 30-read ceiling is unchanged;
do not import it or add another ten-row writer prefetch. Fixed manifest value caps
and total 6291456-byte frame cap stay unchanged; prepared successor remains within
131072 bytes including 16384 terminal + 6144 incoming headroom and existing per-
record/receipt/intent/node/depth bounds. Measure implementation memory separately:
several bounded frames/JSON copies do not imply a single-frame peak-memory cap.

Under locks allow only fixed native statements, primitive already-validated tuple/
session comparisons and non-callback clocks. No WordPress/cache/auth/nonce/UUID/
parser/producer/filter/log/output callbacks, user-provided callables or general
extension points. Exact equality binds the prior semantic validation to the now
locked state without running the semantic parsers inside the critical section.

## Authorization and concurrency claims that must remain limited

The all-ten barrier serializes this fixed options cohort, **not** WordPress user/
usermeta/role-session revocation. No core callbacks run while it owns those locks.
Fresh pre-BEGIN auth plus post-close auth therefore does not prove capability or
session validity at the exact COMMIT instant. Concurrent revocation after the last
pre-BEGIN check may allow a closed bookkeeping commit and suppress its outward
positive decision afterward. Do not describe post-commit checking as undoing it.
Any stronger authorization-linearization guarantee needs a separately designed,
approved participant/locking policy; it is not silently added here. This limitation
is unacceptable as an implicit basis for future active grants/effect execution.

Exact tuples detect current differences, not every intervening ABA/restore event.
Generation/genesis/history pinning is necessary but cannot prove protection from
privileged DB rewrites, same-byte rollback, external nonparticipating writers,
compromised PHP or production participant/cache compatibility. A stale frame with
unchanged restored bytes is not proof that nothing happened. This is an owned
closed-proof profile only, never a ready-to-enable production scanner.

## Failure, acknowledgement and observation-only follow-up

| Private outcome | Required treatment / forbidden inference |
|---|---|
| Refused before BEGIN | Consume owner, close owned resources, no UPDATE. No remint/retry on that invocation. |
| Definite owned precommit failure | Attempt only owned rollback; acknowledged rollback plus verified idle gives definite local rollback. No durable receipt claim or application cleanup. |
| Unknown BEGIN/rollback/COMMIT/close | Poison/consume, close only the owned handle; no reconnect, compensation, second transaction, ID regeneration or invented durable `unresolved` marker. |
| Acknowledged commit, later guard/budget/close failure | Historical commit may exist although outward result is unavailable. Never erase, repeat or recast it as rolled back. |
| Lost caller lookup context | Unavailable/manual investigation, not search-latest, reset or fresh-ID replacement. |

A future separately guarded observation uses a fresh read-purpose envelope and
all-ten native barrier, then interprets full retained history after release. Exact
registration receipt presence is historical closed bookkeeping only. Absence is
`not_observed_at_barrier`, never proof of cancellation, no effect, terminal state,
quiescence or permission to retry: a delayed earlier request might not yet hold
the barrier. Lookup cannot adopt a different enrollment/genesis/generation. This
design adds neither the observer nor a durable fence that closes in-flight work.

## Source-scoped design review and future discriminators

Focused design review resolved: returned array vs trusted owner; issuance vs full
nonce semantics; capture-to-lock staleness; all-ten/current-tuple binding; original
budget carryover/40-read accounting; prepared receipt timestamp; auth revocation
not serialized by option locks; unknown acknowledgement vs historical receipt.
No runtime defect fixed, canonical Feature/DS3/Database/07A/security workflow or
physical transaction proof claimed. Historical toolkit readiness rows unchanged;
UI/UX N/A. Full pre-release remains due for the next coherent release candidate.

Future proof obligations, not executed by this design:

- Pure positive: exact eligible captured frame/proposal produces the existing
  schema-2 registered successor/receipt, unchanged generation and all ten reserved
  identities, while every storage/grant/dispatch/execution flag remains false.
  Always-unavailable must fail that positive. Parser-valid input is not admission.
- Mutate each tuple field/role, actor, genesis, scope, generation, revision,
  deadline, proposal ID/context and purpose domain separately; stale locked data
  must refuse before UPDATE. No hash-only or ledger-only comparison may pass.
- In-memory behavioral controls remove each plan dependency/identity/time guard
  and must break an assertion. These tests cannot prove genuine core auth, trusted
  native origin, guard ordering, locking, durable receipts or database atomicity.
- Later separately approved genuine core/owned native proof: bad initial auth
  zero protected reads; bad final command zero BEGIN; subscriber/bulk-autoload and
  physical-session-swap denials; no callbacks while locks held; all-ten contention,
  stale post-capture data, actual CAS/readback/acknowledged commit and rollback.
  Verify 40-read/one-write/original-budget limits and untouched application state.
- Genuine positive must distinguish a committed registration from always-refuse,
  revision-only checkpoint or fabricated success; independent stored receipt and
  all nine other tuple checks, not mock counts alone. Guard-removal mutations must
  fail semantic assertions. Restore every counterfactual before final validation.
- Deterministic revocation/competing request/late barrier cases must preserve the
  claim limits above. Simulated missing acknowledgements are not actual lost-wire,
  crash/power-loss/durability or terminal-drain proof; those need their own plan.

## Validation, publication and next approval gate

Executed repository-only on 2026-10-19; these checks exercise unchanged code,
not the proposed admission protocol. No test added merely for this document.

- Uploader `php vendor/bin/phpunit --do-not-cache-result --filter
  'RegistrationNativeReaderBoundaryTest|RegistrationConstructionTest|RegistrationRequestContractTest|ClosedRegistration|FixedCoreRequest|ScanLedgerCheckpoint|ClosedScanEffectLedgerTest|ScanEffectManifestTest|ScanEffectFenceContractTest|ScanTargetCodecTest|ScanObservationContractTest|ScanNativeObserverBoundaryTest|RealClosedWriter|NativeClosedBeginBoundary|NativePairedBoundary'`:
  **253 tests / 2954 assertions**, no skips.
- Uploader `npm run lint`: **201 files**, passed; existing PHPCS/PHPCompatibility
  functionWhitelist comma-array deprecation banner remains, not introduced here.
  `npm run build`, `npm run verify:runner`, `npm run lint:runner` passed; generated
  runner/assets unchanged. `npm run test:dev-tools`: **3 tests**, passed.
- Uploader `npm audit --audit-level=moderate`,
  `php ../alynt-drime-backups-dashboard/composer.phar validate --strict` and
  `php ../alynt-drime-backups-dashboard/composer.phar audit`: passed, zero reported
  vulnerabilities/advisories.
- Dashboard `php vendor/bin/phpunit --do-not-cache-result`: **299 / 1423**, two
  existing Sodium-unavailable signing skips; none added or weakened. Full Linux
  CI must verify the signing cases, not treat these local skips as complete proof.
- Dashboard `npm run lint`: **138 files**, passed. `npm run check:structure`,
  `npm run build`, `php composer.phar validate --strict`, `php composer.phar audit`
  and `npm audit --audit-level=moderate`: passed; assets unchanged, zero reported
  vulnerabilities/advisories.
- Both `git diff --check` checks passed; all **14** cited uploader source paths
  and both predecessor links exist. Final diff review: dashboard three design/plan
  documents and uploader one plan only. No runtime/test/version/dependency/generated
  output or toolkit change. Toolkit remains clean at
  `fff4b05a82ec594fd4aacd9f01f702432c1cf719`; no historical readiness refresh.
- Restore workflow Option A/check: uploader clean/synced at its excluded base,
  existing external archive
  `C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-uploader/alynt-drime-backups-uploader-20260920-211159.zip`;
  no new archive/sidecar/backup/restore/checkpoint branch. Source protection only,
  not a database/site restore point.

Commit/push and exact-head CI for both owning repositories are required before
handoff; final commit IDs/run links are reported there without a receipt-only
documentation commit loop. No native/auth/session/SQL/physical transaction, new
mutation control or admission protocol test executed. In-memory regressions and
green CI cannot establish those unimplemented guarantees or release readiness.

Implementation-plan progress: the missing native admission boundary now has a
source-backed closed-only contract; real schema-2 native writes remain unimplemented.
The next meaningful item crosses a new implementation trust gate, not generic
maintenance. Recommended next gated slice: **local-only V2.3 Closed Registration
Admission Plan and One-Shot Handoff Safety Foundation**: dormant/test-loaded pure
plan/equality/lifetime contracts with in-memory fixtures and non-vacuous controls;
no actual authentication, storage/native handles, site/database/fixture operations,
public endpoints, production wiring, grants, activation or execution. Approve this
bounded contract implementation first; genuine native registration writes require
a separate exact owned-fixture approval after it passes.
