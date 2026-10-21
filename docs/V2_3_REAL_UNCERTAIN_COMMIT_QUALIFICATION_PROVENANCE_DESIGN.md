# V2.3 Real Uncertain-COMMIT Qualification Provenance and Observation Design

2026-10-20. Approved **repository-only, design-only**. No implementation or native
experiment, WordPress bootstrap/authentication, SQL/storage write, user/table
fixture, socket/listener/IPC/fault run, production wiring, grants, activation,
rollback, package/release/tag/deploy. Versions uploader0.5.25/dashboard0.1.77 unchanged.
Future requirements below authorize no execution. This is not a release candidate.

## Ownership and review boundary

Dashboard owns this design, its implementation-plan entry and the selected-loss
design successor pointer. Uploader is read-only at
`9d3c9d107331f5a7074cc58ac4d91ac6ab91267b`; dashboard starts
`4a84f89fbc28c9c4f7cab63a2d22e0e78c264a49`, toolkit
`1fd28842ee1e23789968cfb78cf47e232bc62b1b`, all clean/synced before work.
Excluded coherent review bases uploader
`174a6a97fe7be7d51c9b886517fb61fbbeb5ff50` (before native loss implementation)
and dashboard`776a4f19bad779816bb95450a8ac44da06d8f08f` (before governing loss
design). Scope is that proof/design boundary and directly read dependencies, not
every unrelated historical change or the latest verification-only commit.

Source-only dashboard snapshot before edits:
`alynt-drime-backups-dashboard-20261020-215606.zip`,13190 files, matching sidecar
under toolkit-snapshots, SHA256
`CC851DA17267BE52DA2C8664DA276317640B6B8C284200B344BAFABC42FDE887`.
Not a site/database backup; no stash/restore. Master/toolkit/repository instructions
and Site Operations/prospective Plugin Tester profile read. No site is operated on;
no target/Novamira/credential/service/database check is needed for this design.

Parents: [selected response-loss design](V2_3_SELECTED_COMMIT_RESPONSE_LOSS_WITNESS_DESIGN.md)
and [failure qualification design](V2_3_UNCERTAIN_NATIVE_OUTCOME_FAILURE_QUALIFICATION_DESIGN.md).
Uploader owner `docs/V2_3_SELECTED_RESPONSE_LOSS_OWNED_PROOF.md` records historical
no-fault242/2, selected170/2,11 withheld framed bytes/zero target response writes,
genuine guards266/11 and77-table preservation/cleanup. None is rerun here.

## What is established, and what is not

The previous final-class proof establishes a bounded real server-OK/client-refusal
event, original stored proposal/other-nine rows, guard order, erasure and independent
cleanup. It does **not** establish the actual private qualification invocation,
return shape or classified history. Spent=true is insufficient: outer finally
also sets that latch. Poisoned phase and returned=false are not classification.

| Unchanged source | Consequence |
|---|---|
| `trait-registration-admission-transaction.php` marks COMMIT sent before query; raw return/ack only recorded if query returns | A thrown API call leaves raw/ack unknown; server OK is independent external evidence, never an input to classification. |
| `trait-registration-native-outcome-session.php::finish()` detaches/closes owned writer, seals capture, poisons commit_sent | Observe qualification after real cleanup; do not require pre-cleanup phase or manufacture close acknowledgement. |
| `trait-registration-admission-root.php` narrow native catch joins the ordinary false branch | Retained false branch calls finish(), then qualifies once with auth/intent/physical null, returns false without guards9/10. |
| `trait-registration-admission-qualification.php` consumes before full original-pin checks, captures actual clocks and owner slots, calls closed_qualification_data | Exact call count and actual returned result require observation inside that private call, not a later reflected reclassification. |
| `trait-registration-sealed-outcome-data.php` validates seal/causal/raw consistency and derives history/current separately | A valid sent-COMMIT/no-idle history is expected uncertain; current acceptance is false when three guards are unperformed. Source expectation is not native proof. |
| Root finally clears capture/origin/IDs/writer/candidate/context before return | Post-return inspection cannot recover the erased result. Do not weaken this erasure for observability. |

Previous `RegistrationFailureApiHarness` privately aliases the actual qualifier
and observes its return, but doubles frame/auth/session/rows/native APIs. Its
source identity assertions prove isolated composition only. Existing genuine
postrelease qualification cases inspect acknowledged paths at nonce9/10; those
callbacks never occur on the selected failed-COMMIT path. Neither closes this gap.

## Decision: fixed proof-only source composition, no production seam

Select a separate final **test-only composition** using the actual unchanged
production root/join/construction/guard/transaction/storage/session/capture/DATA
traits. Alias the qualifier privately and wrap only that call to count it and
retain a bounded, non-authorizing projection of its actual return. Reuse the
existing isolated composition pattern, but do not reuse its API doubles as native
evidence. No source patch/eval/generation, subclassing the final owner, observer
hook, callback provider, public getter, ambient flag or production instrumentation.

This is deliberately **not the original final runtime class**. A future native run
may establish genuine WordPress/native provenance through unchanged source bodies
in the checked composition, not that the unmodified final class exported its
private return. Keep the earlier unchanged-final-class witness separate. Do not
merge two runs into a claim about one physical attempt or call this full behavioral
equivalence. A later requirement for direct final-class observability needs a new
design decision; it is not smuggled into this test harness.

The proof class has no production require/caller/route. Its only extra state is
private bounded observation bookkeeping, inaccessible to the reused source bodies.
It adds no externally supplied origin, row, handle, clock, native outcome, result
or operation selector. Existing ordinary request construction remains the only
entry, with genuine auth/current actor/separate nonce domains at a native handoff.

### Source and state equivalence gate

Before executing any future real request:

1. Compare the complete production method/signature/visibility/trait-resolution
   manifest, not a chosen subset that could miss a replaced guard or factory.
   Reused methods must have identical Reflection source file/start/end and body
   hashes. Include root, transaction/write, cleanup, qualification alias, origin,
   current guards, frame/profile/policy/session/budget and DATA methods.
2. Account explicitly for class-local factory/constructor/entry/pin bodies: exact
   canonical token-body equality and signatures/defaults where declarations live
   in the separate proof class. A different class name is recorded, not concealed.
   Only the fixed qualifier wrapper/alias and private proof projection are exceptions.
3. Compare original property/default/static/visibility/constant manifests; original
   slots must match, no undeclared/dynamic property or method-shadow fallback.
   Additional proof-only state must be fixed allowlisted and bounded. Preserve
   final/private construction and original public surface; no public run/state ports.
4. Refuse unknown/new/missing members, changed sources, unsupported PHP layout or
   alias conflicts before protected reads, native factories or fixture execution.
   Record source revision/digests and explicit exceptions; counts alone are insufficient.

Do not copy native method implementations into a parallel harness algorithm.
Do not transform production source on disk or accept token-normalization that
ignores executable literals/operators/control flow. If full safe equivalence
checking cannot be implemented, stop instead of weakening the manifest check.

## One passive wrapper, actual result and bounded receipt

The wrapper increments an entry count before calling the original private alias
exactly once with the root's unchanged pin and guards. It observes original
cleanup/seal/empty-slot and primitive capture facts without altering them; no
additional WordPress authentication, query, IO, provider or clock refresh occurs.
It must return the **exact actual result** unchanged; exceptions propagate unchanged.
Do not replay the alias to inspect it or compute a second classification from
saved DATA. Do not feed an external relay/audit receipt into the owner or wrapper.

Store only a fixed in-memory projection: entry/return counts (cap1), return kind
false/closed-array/exception, actual enum/boolean return fields, and finite
pre/post predicates for seal/empty slots, capture unchanged and unchanged source
IO counters. Never retain raw origin/proposal/IDs/clock/cookies/nonces/tokens/rows,
exception message, object, handle, callable or full capture/result graph. Preserve
all original result flags, including candidate_only=true and authority flags=false.
Unrecognized keys/types/outcomes mark the proof unavailable, never sanitize to success.

The observation validity flag is consumed before validation and cannot be rearmed.
Nested/repeated calls invalidate evidence, not the genuine result; no wrapper failure
may suppress original cleanup, replace the return or make runtime acceptance true.
Root finally erases every original slot as before. The proof-only projection may
survive only to the fixed worker's post-root assertions and private summary, then
is cleared before worker exit. No persistence/public/export/debug/serialization
surface; proof-only extraction is private reflection after root return.

No IPC/stdio write or external assertion callback runs inside qualification or
while native locks are held. Defer proof assertion failures to outside the root's
swallowing catches; retain a bounded failure latch rather than throwing a new
assertion that could bypass the original qualifier. Existing exact native/audit/
cleanup conjuncts must still pass independently. If observation fails, report
inconclusive with safe fixed metadata; never downgrade the required proof.

## Expected evidence and falsifiable controls

| Future case | Required observed result / safety condition |
|---|---|
| Valid no-fault source composition | Original admission succeeds, actual one qualifier call/return and committed history/current acceptance true after genuine ten guards. Reject an always-unavailable qualifier or always-empty observer. |
| Selected real response-loss composition | Same original root, pre8/post0, forty projections/one DML attempt, real refusal and cleanup. Exactly one actual qualifier return must be closed-array with history uncertain/current acceptance false and every authority/diagnostic/retry/storage/dispatch/execution flag false. |
| Client-release claim | Copy its actual boolean and correlate with actual raw close/ack/empty slots. Do not force true because external sessions disappeared; if false, report that limit, not released=true. |
| Malformed/stale/denied/wrong-domain intent | Existing early zero-protected-IO contract and zero qualifier entries. Distinct issuance/command nonce domains remain exact. |
| Invalid original pin/IDs/clock/seal/reentry/remaining slot | Isolated negative instrumentation must make qualification unavailable; never import repaired state or use these faults as genuine native events. |
| Throw before qualifier / within actual alias | Missing return or exception is not an uncertain-array observation. Preserve original refusal/erase, record incomplete evidence; no synthetic error-to-result conversion. |
| Late/repeated observation, false FINAL, missing cleanup/audit | Independently fail proof; no replay/receipt laundering or automatic second response loss. |

Socket-free isolated foundation must first verify full method/state manifest
parity, exact wrapper return/throw identity, noninterference and bounded projection.
Use existing actual source bodies with explicit API doubles, including positive
committed and uncertain paths; labels must say isolated, not native authentication.
Assertion-killed controls should cover skipped qualifier, always-false qualifier,
fabricated observer result, extra failure guard, changed original method/property,
lost erase, altered raw/ack and replay. Verify imports/semantics before accepting
an assertion kill; no syntax/bootstrap failure, loosened guard or new success path.

Only after separate native approval: freshly resolve/confirm Plugin Tester and
owned fixtures/listeners/processes/cleanup; bundled-PHP preflight, initial bundled
mysql, genuine guard controls and no-fault transparency/cleanup **before** one
fresh selected response-loss attempt. Native calls use real core/native factories,
not injected handles or API doubles; no new session policy/timeouts. Keep original
10s acceptance/6s reserve and bounded cleanup/output limits. Observation timing
overhead is not grounds to extend a deadline or retry an ineligible attempt.

Use a separate fixed private qualification-proof transcript/domain and compiled
entry, not new optional fields tolerated by strict existing v1/v2 FINAL grammars.
Bind root/attempt/pair/source manifest, genuine guard/call counts and minimal
result projection; never export secret/raw DATA. The future protocol needs its own
bounded isolated tests before actual IPC. A FINAL is still a claim requiring actual
worker exit/pipe/relay/session/lock/fixture/application reconciliation, not authority.

## Rejected alternatives and remaining limits

- No nonce9/10 or new WordPress hook on failure merely to inspect private state.
- No surviving runtime receipt/getter, relaxed erasure, persistent diagnostics,
  debugger dependency/install or public/private-result bridge to the dashboard.
- No reflected post-return qualifier replay, standalone synthetic classifier,
  source rewrite/eval, native return injection or server-OK-to-commit promotion.
- No claim that method identity proves original final-class execution, client
  acknowledgement, crash durability, current authorization, safe retry or rollback.

The result would be provenance of **the actual reused qualifier's return in an
instrumented native composition**, conditional on the declared local-source/server
trust model. It is not a cryptographic certificate against a malicious test runner.
The next meaningful work is this bounded proof support, not another outcome schema,
generic maintenance batch, release or activation decision.

## Focused reviews and validation

Canonical Feature Light: three dashboard design/plan/pointer files plus the direct
source/proof contracts above. Risk Other/design-only. Clarified erased-result versus
spent/phase inference, composition versus final-class identity, exception/return
transparency, manifest completeness, no failure callback and no external authority
feedback. No outstanding design inconsistency or production defect found.
Bloat/Structure unnecessary for no executable changes; no UI/UX workflow.
Formal DS3/07A/Database/full-plugin readiness not claimed by writing this test plan.
Future implementation needs source-scoped Light/Bloat/03/07/06/07A and final focused
Security; FULL_PRE_RELEASE remains for a coherent RC before packaging/publishing.

Final focused Feature Security after source/document crosschecks and local QA:
no new runtime port, raw private-state export, trust promotion, auth/nonce/erasure
weakening or hidden native approval. No confirmed scoped security defect; regression
handoffs are future foundation/native controls above, not completed runtime proof.
Exact executed validation/publication evidence is recorded in toolkit; historical
native and predecessor CI counts are not refreshed by this design.

Executed local validation: dashboard `php ./vendor/bin/phpunit --do-not-cache-result`
299 tests/1423 assertions/two known Windows Sodium skips; WPCS138, structure guard
and build pass. Uploader related isolated PHP174/10181 and full Node472 pass/no
skips, no native worker/WordPress/SQL invoked. Markdown relative links, source
ordering/literals and docs-only diff checked. No test or generated/runtime source
change; uploader stays byte-identical/unpublished. Dashboard exact-head CI/logs and
toolkit docs-only remote/check/status evidence are recorded separately at closeout.

Implementation-plan progress: selected a bounded source-preserving observation
route and explicit evidence level for the next uncertainty milestone.
**Recommended next gated slice: local-only V2.3 Test-Only Qualification Observation
and Source-Identity Safety Foundation**, isolated API tests only. Implement the
fixed proof composition/manifest, passive wrapper/projection and semantic controls,
without actual authentication, WordPress bootstrap, storage, networking/IPC/native
experiments or production wiring. No new production qualifier, schema or endpoint.

## Approved foundation successor — 2026-10-20

Approved verification repair retains complete manifest guards while canonicalizing
PHP-version ampersand token representation; bounded test-only memory configuration
and actual low-headroom refusal regressions leave the production guard unchanged.
Repaired isolated41/619 and affected78/818 pass; prior failed CI/local history and
full revalidation outcomes are retained in uploader owner/toolkit. No native proof.

Uploader owns `docs/V2_3_QUALIFICATION_OBSERVATION_SOURCE_IDENTITY_FOUNDATION.md`:
fixed final composition, complete production/proof/projection source pins, private
passive alias observation, sticky replay/reentry invalidation and exact result/throw
transparency. New37/607 isolated tests include actual committed/uncertain qualifier
returns over synthetic state; six counterfactual receipt oracle checks and67 missing
method/14 descriptor controls are not actual-source mutation or native evidence.
Early malformed root zero-entry/IO refusal is separate from positive private-alias
tests; no full authenticated/native request on the new class has run. Original source,
guards/clock/cleanup/erasure/wiring unchanged. Source-scoped canonical review and
validation/publication evidence in owner/toolkit, not whole-plugin readiness.

The next gate is **local-only V2.3 Fixed Qualification Observation Worker and Private
Transcript Safety Foundation**, isolated API/synthetic-message tests only: fixed
manifest-before-entry, private extraction/clear and separate bounded strict domain.
No actual authentication/storage/IPC/networking/native experiment or production
activation is authorized by that foundation. Genuine proof still needs its own
explicit Plugin Tester target/owned-scope approval; historical170/2 is not refreshed.

## Approved fixed worker/private transcript foundation — 2026-10-20

Uploader owner: `docs/V2_3_QUALIFICATION_WORKER_PRIVATE_TRANSCRIPT_FOUNDATION.md`.
Complete source gate now covers fixed worker and transcript in addition to the
unchanged production/composition/projection pins. Fixed final owner construction;
private extraction consumes and erases observer receipt before validation, checks
original slots without clearing them to manufacture evidence, then discards refs.
Private request entry unconditionally refuses without invoking the original root.

Separate `alynt.qualification-observation.isolated.v1` exact one-frame grammar,
4096 bytes/depth8, distinct64-hex attempt/root/pair labels, version/sequence1 and
fixed pins. Actual9-field receipt/13-field result copied; no external classification
input or authority. All root invocation/erasure/authentication/native/IPC proof
flags remain false. Parser acceptance is structural, not trusted source provenance,
OS EOF/exit, real clocks, guard counts or physical cleanup proof.

New31/7884 and related125/12735 isolated tests pass, including committed/uncertain
actual qualifier results on synthetic post-root state, missing/retained/forged
evidence, causal no-root entry and all truncations. Privileged fixture Reflection
and manual final-state mirroring are explicit limits, not genuine request proof.
Canonical scoped Light/Bloat/03/07/06/07A/final Security and full QA/publication
evidence in owner/toolkit. No dashboard runtime/contract change or new native run.

Implementation-plan progress: isolated extraction and separate transcript are
protected; real uncertain-request observation remains unproven.
**Recommended next gated slice: local-only V2.3 Fixed Qualification Worker
Request-Entry and Private Stdio Integration Design**, design-only. Define genuine
fixed entry/guards/original clocks, private handoff and independent cleanup/audit
conjuncts under a distinct native-proof domain; keep actual authentication,
WordPress/storage/native/IPC experiments and production wiring disabled. Do not
flip isolated false flags to native proof or relax existing strict v1/v2 grammars.

## Approved fixed request-entry/private stdio design — 2026-10-21

Owner: [fixed request-entry/private stdio design](V2_3_FIXED_QUALIFICATION_REQUEST_STDIO_DESIGN.md).
Separate fixed proof-only native worker selected; existing isolated worker stays
closed. One original retained root on its own source-pinned composition; private
late pair binding, actual qualifier observation and consumed extraction after
original finally, no external owner/proposal/result or artificial slot clearing.
Existing join runner cannot substitute a different owner. No native implementation
or experiment performed by this design; uploaderf339a459 remains byte-unchanged.

Reserve a distinct native-v1 no-fault six-frame domain with original budgets,
strict fields/caps and independent real EOF/exit/pipe/session/lock/fixture/
application reconciliation. Parsed FINAL/source claims are not authentication,
physical cleanup, final-class execution, durability or authority. Old protocols
and synthetic false proof flags unchanged. Canonical scoped Light/final Security
and fresh isolated125/12735/Node472 QA in owner; publication evidence in toolkit.

Implementation-plan progress: concrete fixed entry/late-pair/stdio design selected;
genuine observation remains unproven. **Recommended next gated slice: Plugin Tester
local-only V2.3 Fixed Qualification Worker Genuine Request and No-Fault Private
Stdio Integration Proof**, isolated implementation first, then exact-confirmed
owned fixtures/users/listeners/processes and cleanup. Preserve actual application
state and production wiring; no response suppression or selected attempt until
separate later approval and successful no-fault transparency/cleanup.
