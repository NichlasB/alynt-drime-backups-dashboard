# V2.3 Uncertain Admission Origin Retention and Observation Lifecycle Design

## Concrete native join/outcome refinement — 2026-10-19

The approved design-only [fixed native join/outcome contract](V2_3_NATIVE_ADMISSION_JOIN_OUTCOME_DESIGN.md)
maps original-before-DML retention into narrow native helper reuse, separate send/
ack/idle/historical/cleanup/current-auth facts and conservative diagnostic admission.
It preserves this parent's one-root/read policy and historical records; it does not
enable transactions or claim real uncertain-admission provenance. Current next gate
is in-memory-only Admission Outcome and Release DATA Safety Foundation, not another
constructor or a resumed preparation boolean. All implementation/proof gates below
remain scoped to their original checkpoints, not blanket permission to execute.

## Successor DATA checkpoint — 2026-10-19

The separately approved in-memory foundation is owned by uploader
`docs/V2_3_REGISTRATION_ADMISSION_LIFECYCLE_FOUNDATION.md`: bounded one-shot origin/
IDs, modeled sent/ack ordering, uncertainty/loss and original clocks,22/2408 new
tests and eight assertion-killed/exactly restored controls. This advances only the
DATA gate described below; it does not establish genuine origin/auth/native
release or enable storage/wiring/activation/rollback. The original design and
historical validation below remain unchanged. Current next approval is the fixed
admission-origin retention and guarded lifecycle owner safety foundation, isolated
API tests only; owned native execution/proof is a later separate gate.

## Original design checkpoint

2026-10-19. Approved R3 **repository-only, design-only** checkpoint. Storage
writes, WordPress/user/database operations, fixtures, production wiring, grants,
activation, dispatch and schedule rollback remain disabled/unchanged. This design
does not authorize its implementation, native integration or an endpoint.

## Ownership and coherent source baseline

Dashboard owns this cross-plugin lifecycle design and implementation-plan record.
Uploader owns the dormant construction/admission/observation source and proofs.
Excluded review baselines (not documentation-tail commits):

- Uploader `ca20a8b639d18e966ec17eaecda99b672f014e05`.
- Dashboard `f436bd83dc9d817331dd41f321549d04a984389f`.

Scope: existing native admission owner, candidate/storage/transaction traits,
genuine core guard, registration constructor/eligibility, observation origin/
request/construction/window, native capture, private handoff, native barrier/
session/metadata and directly affected boundary tests. No source/tests change.
Versions stay uploader0.5.25/dashboard0.1.77. Predecessors: uploader
`docs/V2_3_REGISTRATION_NATIVE_ADMISSION_PROOF.md`,
`docs/V2_3_REGISTRATION_OBSERVATION_NATIVE_BARRIER_PROOF.md`,
`docs/V2_3_REGISTRATION_OBSERVATION_CONTENTION_PROOF.md`; dashboard
[private handoff design](V2_3_REGISTRATION_OBSERVATION_NATIVE_HANDOFF_DESIGN.md).
Contention's 611 checks/9 groups establish physical fixture behavior, not genuine
uncertain-admission provenance or a production recovery lifecycle.

## Confirmed gap in current source

Uploader `includes/class-registration-native-admission.php::admit()` captures an
authenticated frame, constructs one proposal, attempts one ledger CAS and returns
a boolean. Finally removes the proposal/context/frame. False can follow an
acknowledged historical commit if final authentication or the original deadline
fails. False therefore does not mean no write occurred.

`trait-registration-admission-transaction.php` sets `begin_sent`/`commit_sent`
before I/O. `trait-registration-admission-storage.php::admission_write()` increments
its write counter before execute, but exports no trusted lifecycle outcome.
Neither the boolean nor a later row read is a provenance/uncertainty certificate.

Separately, `class-registration-observation-construction.php` and its native
capture trait construct and retain their own origin, without admission. Their
attempt/capture latches cannot establish provenance from a genuine admission.
Two constructors generate different IDs. Calling consuming `admit()` and then
rebuilding from a receipt, exported proposal or caller origin is rejected.

## Selected architecture: one fixed private lifecycle root

Future integration requires one non-public, non-extensible owner of this sequence.
No callback/provider/continuation, origin-import API, reconnect/retry port, public
endpoint or write-capable handle may escape it. Existing standalone facades,
boolean contracts and production loading remain unchanged.

| State | Required evidence / permitted next transition |
| --- | --- |
| unopened | Consume root attempt before validation; pin actual POST/core/actor/cookie/physical identity and original clock. |
| captured | Genuine guards before protected reads; bounded two-sweep native capture, then close owned capture connection. |
| constructed | Exactly one constructor generates the command and all ten fresh IDs; validate full command/candidate/frame. |
| origin_retained | Privately retain minimal original provenance and complete collision set **before DML can be sent**; reject overflow now. |
| admission_attempted | At most one guarded native CAS transaction; preserve sent-before-I/O phases, never infer no-write from false. |
| writer_released | Finish/detach owned writer; erase full writer frame/proposal/nonce; no observer reuse of handles. |
| observation_admitted | Independently authorize fresh read-purpose construction under the fixed policy below. |
| observation_consumed | At most one private handoff/native zero-DML barrier; consume before comparison and erase on every exit. |
| closed | No second admission/observation, reissue, origin reuse or recovery capability. |

Failures may jump only to consumed/closed cleanup. A DATA state model cannot
certify genuine authentication, physical release or native success. The fixed
root, not arbitrary state setters, must establish those facts. Do not blindly
compose the current consuming wrappers or merge their overlapping private state;
writer/observer phases and handles need distinct ownership. Reuse narrow private
operations only after a separately approved guarded integration review.

### Original provenance and bounds

Retain existing `registration_observation_origin_v1` DATA: exact installation
(blog ID/prefix/database), ordered scope (site UUID, enrollment binding, authority
generation, ledger ID, origin creation time), manifest digest, own original typed
mutation, historical actor, operation ID and context digest. Derive it only from
this root's own accepted construction. Never learn expected actor/context from a
receipt. Historical actor does not replace current authorization.

Keep the complete inherited collision set plus all ten generated IDs; the fresh
read lookup ID must collide with none. Preserve current bounds: origin4096 encoded
bytes/depth6/256 key-value nodes; unique UUID list4..128; aggregate handoff16384
bytes/depth8/512 nodes. Smaller parser limits still apply. Refuse references,
aliases, malformed/non-primitive data, changed tuple order, scope/manifest/genesis
mismatch or overflow; never truncate evidence to fit.

Full before/after ledger bytes, native sessions, handles and mutation forms are
writer-local transient data, not retained origin. Erase them after release.
Nonces may appear only in bounded private request/handoff state, never in durable
origin, logs or results. No options/transients/user-meta/schema are proposed.

### Admission outcomes and cleanup

Specify a future private `dml_sent` latch immediately before execute; do not derive
it from an exception, affected-row count or final boolean. Preserve sent-before-I/O
BEGIN/COMMIT/rollback phases and one-CAS limits. No unknown statement is resent.

| Historical evidence | Required interpretation |
| --- | --- |
| No DML sent; owned rollback acknowledged | No admission write by this attempt; diagnostic unnecessary. Not global retry authorization. |
| DML sent; rollback acknowledged | That owned transaction did not commit; no reusable capability. |
| COMMIT acknowledged and session idle | Historical commit only; final auth/deadline can still make public admission false. |
| DML/COMMIT/rollback acknowledgement uncertain | Unknown outcome; never absent, closed or retry-safe from boolean/phase alone. |
| Required guard or owned release cannot be established | Unavailable/manual reconciliation; erase origin, do not observe. |

Client-side close is not server-quiescence evidence or durable ingress closure:
the writer may still finish later. Observer never rolls back/reuses/reconnects
that writer or closes the borrowed WordPress auth connection. Unknown cleanup
cannot be repaired by exporting a handle or attempting admission again.

Only an uncertain attempt, or a historically committed attempt refused by a
later guard, is a potential diagnostic case. Ordinary successful admission needs
no observation. A failed guard still bars immediate observation; there is no
fresh-login fallback. Even for a potential case, all read gates must pass in the
original request or the origin is erased and manual reconciliation is required.

## Independent observation admission

Standalone observation needs a small read-issuance nonce bound to the observed
command ID. Admission creates that ID internally; the incoming form cannot know
it. The standalone consuming wrapper is **not** directly an admission successor.
Do not set `capture_attempted` or create another proposal to make it qualify.

Selected future first lane is a fixed **same-request private diagnostic policy**:
after owned writer release, the root may construct a read-only diagnostic of its
own retained command, under unchanged actual actor/cookie/core/physical identity.
This is proposed policy requiring separate guarded implementation approval, not
evidence of a second user-posted read form or durable recovery consent.

1. Revalidate actual current auth/capability, original registration issuance
   intent, original POST identity and remaining deadlines. No protected observation
   reads or fresh read UUID before authorization.
2. Under that fixed private policy, genuinely create the distinct small read
   nonce with `Registration_Observation_Request::issue_action()` using only the
   retained ID. Pin genuine core/actor/cookie/physical identity before/after
   creation. Creating a nonce is not itself authorization.
3. Genuinely verify that small read nonce under its own domain **before** one fresh
   lookup UUID. Mutation nonce validity is intent continuity only; it must never
   substitute for small/full read nonce verification. Preserve exact form bounds.
4. Construct one full `scan_registration_observation_v2` query; create/verify its
   separate full read nonce. Renew both read guards at private claim, pre-BEGIN
   and post-close gates, preserving original origin/lookup bindings.
5. Close observer before interpreting history; renew auth/clocks after close.
   Every failure consumes the attempt and erases provenance.

No caller ID/actor/context/action/nonce/callback may replace these facts. Missing
genuine issuance, revocation, stale intent or identity/physical swap means
unavailable, not simulated success. Standalone behavior stays unchanged. New
HTTP request/login, durable token or cross-request continuation is a separate gate.

## Deadlines, results and origin loss

Existing writer and observer each enforce ten-second monotonic acceptance windows
plus UTC query limits. Proposed integrated policy: root cap20 seconds from before
initial capture; writer's original10 seconds unchanged; observer's own10 seconds
starts once before read issuance and is also bounded by root remaining time.
These are **design limits, not implemented runtime guarantees**. Never reset the
root via `open_window()`, reset writer time for observation, accept backwards
clocks or renew expired mutation intent. Fresh read expiry remains at most300
seconds with existing origin temporal checks; it cannot extend acceptance time.
Native I/O may overrun remaining time. Reject late results; budget rejection is
not hard cancellation or server-quiescence proof. No background retry.
The proposed20-second ceiling bounds two sequential existing10-second phases;
it is not permission for either phase to borrow or restart the other's budget.

Validate the complete current pending-aware ten-row frame, not admission-only
no-pending eligibility, unchanged pre-write bytes or a receipt alone. Match exact
original command/context/historical actor/operation. Conflicts stay conflicts.
Acknowledged absence means only `not_observed_at_barrier`, even if a writer commits
before return. Presence is historical matching receipt evidence, not permission.

Preserve non-authorizing flags: DATA cannot become an authenticated/native barrier
or closure/retry/storage/dispatch/execution certificate. Native `barrier_verified`
requires genuine zero-DML barrier and release; closure/retry/authority flags stay
false. Return no origin/proposal/nonce/raw ledger/handle.

Origin is process-local and one-shot. Death/loss/expiry/interruption means
unavailable/manual reconciliation even if SQL committed. Never reconstruct from
receipt/latest ID/new constructor/dashboard snapshot/current actor. Durable
ingress closure and post-crash recovery remain separately unapproved boundaries.

## Required falsifiable evidence before integration

Next implement only bounded in-memory lifecycle DATA after fresh approval. Include
meaningful valid synthetic transitions and refusals: always-unavailable is not
positive evidence. Synthetic phase inputs are untrusted/non-authorizing and must
not become factory credentials. Do not add artificial runtime success paths.

Later tests/semantic controls must independently kill these bypasses:

- Retain after DML, generate a second proposal, drop reserved IDs, import
  receipt-derived actor/context or accept aliasing/overflow.
- Treat false as no-write, skip sent-before-I/O, reset deadlines, allow time
  regression or use fresh query expiry as acceptance time.
- Skip auth before protected reads, exchange nonce domains, bypass genuine
  issuance, accept stale/malformed intent or swap actor/cookie/physical identity.
  Independently count protected reads and verify guard order.
- Skip writer release, borrow/export handles, omit consume/erase on a failure,
  reuse consumed origin or promote pure DATA to native success.
- Validate only a receipt, infer durable closure from absence or enable retry/
  dispatch from historical presence.

Record assertion-killed controls and exact restoration. Isolated/stubbed API tests
do not prove actual WordPress auth, SQL ordering or physical release. A separately
approved owned-fixture proof must exercise **the actual single admission-to-read
root**, not independently generated history: commit/rollback, auth failure after
commit, timeout/loss and late completion with application-state/cleanup evidence.
Unreaped COMMIT ack, injected errors and owned disconnect remain distinct from
real wire-ack loss, server crash, durable closure and safe-retry proof.

## Review, validation and next gate

Source-backed design review covers phase/error ordering, separate nonce domains,
pre-read authorization, bounded retention and cleanup/no-handle-export. Historical
proofs are inputs, not implementation of this design. No Database Review, new
physical transaction proof, UI/UX review or whole-plugin readiness refresh claimed.
Canonical full pre-release remains required at the next coherent release candidate
before packaging/publishing code containing this work.

Local validation: uploader related346 tests/7210 assertions, no skips; dashboard
299/1423, two existing Sodium skips; uploader PHPCS222 files and dashboard138
files pass (existing functionWhitelist deprecation); dashboard structure passes.
These unchanged suites are regression checks, not execution of the new design.
Source method/flag references and linked owning records checked; docs-only diffs
and whitespace checks pass. Exact-head CI is reported at publication handoff;
no future result assumed here. Source/tests/manifests/versions remain unchanged
against the explicit baselines. No toolkit readiness row is refreshed.
External source restore ZIP (not site/database backup):
`alynt-drime-backups-uploader-20261019-183749.zip`,10,686 files, SHA256
`5C2115A479A4A64A25A02F815E26E44F1B0E8500D51E170C5D879FFA362D184C`.

Implementation-plan progress: missing genuine admission-origin lifecycle now has
a bounded design and explicit proof gates; none enabled/implemented.
**Recommended next gated slice: local-only V2.3 Admission-Origin Lifecycle DATA
Safety Foundation**, in-memory only, one-shot bounded retention/phase/loss/result
contracts with non-vacuous controls. No WordPress/auth/storage/native execution or
wiring. New explicit approval required. Guarded composition, owned admission-write
proof, durable closure and rollout remain later gates.

## Guarded lifecycle owner owned-auth checkpoint — 2026-10-19

Uploader `docs/V2_3_REGISTRATION_LIFECYCLE_OWNER_AUTH_PROOF.md` records the separately
approved exact-target Plugin Tester proof: genuine core4564/7, eight assertion-killed/
SHA256-restored controls, all77 application checksums unchanged and owned fixtures
removed. Fixed original actor/proposal identities/clock survive the six independent
preparation guards then clear; no native transaction or production wiring enabled.
This complements isolated API tests, not native capture/admission/release proof.

The next gated design is **Fixed Native Admission Join and Lifecycle Outcome
Integration**: map the existing standalone native transaction into the original
fixed root before its cleanup, without constructing a second proposal or treating
its boolean as no-write/uncertainty evidence. Preserve original-before-DML facts,
release and one-shot loss/transfer semantics from this design. Design-only approval
does not authorize storage writes, fixtures, native execution or production wiring.
