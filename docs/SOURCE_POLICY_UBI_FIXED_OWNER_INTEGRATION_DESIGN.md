# Candidate-Bound Fixed-Owner String-Access Cost and Origin Integration Design

## Approved scope, baseline and evidence — 2026-11-02

User approves repository-only design through validation, commit, push and
exact-head ordinary CI. Documents only: no implementation, parser/collector/
model/entry/workflow behavior change, profile selection, acquisition, extraction,
image loading, native execution, SQL, installed site, production wiring,
permission/credential, schedule, packaging, tag, release or deployment operation.
This is R1 design of a sensitive future integration, not implementation authority
or a general autonomous risk-tier increase. Uploader/toolkit stay untouched.

Restore/review baseline is clean/synced **ac7979a0f3f564032e38f9731284da616304262c**;
[ordinary CI41473269073](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41473269073)
is reverified successful. The [dormant foundation](SOURCE_POLICY_UBI_STRING_ACCESS_FOUNDATION.md)
proves bounded string access against a supplied local DATA grant, not a genuine
fixed-owner allowance, complete-layer origin or candidate qualification. The
latest [real diagnostic41450662313](SOURCE_POLICY_UBI_ELF_STATIC_ADMISSION.md#actual-string-table-diagnostic-and-cleanup-result)
still fails the strict extent guard after six layers; cleanup completed, no
artifact remains. Reachability and eventual compatibility remain unknown.
Campaign remains **7/10 milestones, 7/10 native attempts, six static acquisitions**.
No product credit for this design or its evidence/publication commits.

## Actual gap and selected architecture

Today [collect](../scripts/manual/source_policy_ubi_alias_static.py) calls strict
`elf.parse(body, deadline)`, accumulates successful `elf_work` locally and returns
observations only after a complete diffID. The
[entry](../scripts/ci/source-policy-legacy-ubi-static.py) creates VirtualClosure
after collecting layer0 because its alias profile requires that verified layer;
it then charges parser work and applies the layer. The
[model](../scripts/manual/source_policy_virtual_closure.py) owns `_work` and
`_last`; parser failure costs are not a new shared pre-admission authority.
The [opt-in parser](../scripts/manual/source_policy_ubi_elf_static.py) can enforce
a local allowance, but its DATA receipt cannot issue that allowance or prove
which body/run invoked it. Simply forwarding its metadata would leave this gap.

**Select one source-owned work authority before the first collection**, then
share that same instance with the namespace model when it can be constructed.
The model must not be created early from an assumed alias receipt, and layer0
must not receive a fresh independent full budget. Do not create two authorities
and periodically reconcile snapshots, copy a private `_work` value into a new
model, or reset a counter when staged data is discarded.

A narrow private authority may be colocated with the model so the fixed entry,
collector and model use the exact same loaded type. No generic backend/factory,
arbitrary callback, duck-typed work sink or new WordPress service is selected.
Any future optional model/collector hook must default to the current behavior;
no existing caller silently gets new authority. The real fixed entry and manual
workflow stay disconnected until separately approved qualification integration.

Private Python objects are not cryptographic seals or OS permissions. Standalone
constructors and DATA receipts cannot attest a genuine CI run. Trust comes from
the reviewed fixed call graph and its existing repository/ref/head/runner gates,
source readbacks and actual candidate checks, not a class name or caller boolean.

## Fixed context and bootstrap ordering (proposed, not implemented)

1. Preserve existing target/CLI/environment admission before ownership/network
   work. Freeze the complete participating source cohort and one original300s
   deadline before acquisition, as now. No clock restart after metadata or layers.
2. Preserve manifest/config hash, descriptor, platform and pinned layer checks.
   Only after `admit` succeeds may the future fixed owner create the authority.
   Acquisition/hash/JSON/stream costs retain their existing byte/time limits;
   the2,097,152 modeled work domain is parser plus namespace/graph operations,
   not a new per-byte debit for every compressed byte or full-body hash.
3. Bind the authority to exact approved head/source-cohort digest, run/attempt
   correlation, manifest/config/layer identities, literal access-profile version
   and unchanged cap/deadline. Source-owned constants select the profile; no
   receipt, artifact config, URL, environment search or user-provided factory.
4. Collect layer0 with that authority while the model does not yet exist. Keep
   all candidate observations provisional. After its complete verification,
   preserve `approved_profile` checking the actual lib64 edge. Construct the
   model with that verified profile and the **same** authority, without resetting
   consumption or replay-charging already settled parser work.
5. Subsequent parser grants and model apply/finalize/inspect/resolve/graph debits
   use the same authority. The original isolated/default model remains strict;
   a future shared-authority branch must delegate every `_tick` to this authority
   rather than retain a second spendable balance or shadow clock.

Attach exactly one model, once, after the layer0 verification/profile gate.
Bind its actual instance and immutable identity/layer contract to this authority;
reject a second tree, clone/rebind, foreign identity or caller-provided restored
snapshot. Model debits must come through that bound instance. This is fixed-
source composition, not a claim that private object identity defeats arbitrary
Python code. Tests must witness construction/attachment once and kill a reset.

The importlib boundary matters: the entry uses the collector's loaded model
module/type for the authority and tree. Independently loaded lookalike classes
must not be accepted as an alternate sink. Parser module instances exchange
only validated primitive results; they receive no authority or IO-capable handle.
All participating bytes enter a new frozen source cohort; historical receipts
are not upgraded by a new profile/version marker.

## One outstanding grant and irreversible settlement

The authority has one nondecreasing charged total H, cap L=2,097,152, original
deadline, monotonic operation sequence and at most one outstanding parse grant.
Use an explicit state machine; no hidden resumable receipt cache or per-layer
budget. Select conservative modeled bookkeeping charges of one unit for issue
and one reserved unit for settlement, inside L, not a new ceiling.

| State | Permitted operation | Required outcome |
|---|---|---|
| Ready | Model debit | Check clock/remaining, debit once, remain Ready |
| Ready | Issue parse | Debit issue1, reserve settlement1, bind one grant/context |
| Parse outstanding | Invoke/settle that parser | No model debit, second grant or context switch |
| Parse outstanding | Valid successful receipt | Debit used u plus settlement1, close exactly once |
| Parse outstanding | Valid failed receipt | Same debit, then terminal poison; retain original refusal |
| Any active state | Missing/contradictory result or phase violation | Terminal poison; no continuation |
| Ready | Final completion | All layer/origin/graph/source/cleanup gates, no outstanding grant |
| Poisoned/complete | Grant, settle replay or further model use | Refuse; no reset/refund/retry |

For a ready balance H, issue is allowed only if L-H >=2. Debit one issue unit;
the parser receives R=L-H_old-2, leaving one settlement unit unavailable to it.
On a validated result with0 <=u <=R, H_new=H_old+1+u+1. The parser gets the same
original deadline and pre-charges within R. An exclusive outstanding grant
prevents another actor from spending its allowance during the call.

R is an allowance, not a debit of all R. Unused allowance was never spent, so
closing a successful grant is not refunding consumed work. Failure never allows
a retry within this authority, even if unused capacity remains. Missing or
malformed consumption must conservatively burn the outstanding R plus reserved
settlement unit and poison the attempt, not fabricate used0 or a successful
receipt. Report conservative burn separately from measured parser consumption.
No negative balance, deferred debt, quota increase, bookkeeper reset or duplicate
debit. If issue cannot reserve settlement, refuse before invoking the parser.

All model operations are pre-admitted against the same remaining balance; do
not keep the old `tree.charge(collected['elf_work'])` in the new branch, because
it would charge settled parser work twice. Old modes retain their existing path.
Parsing/layer failure leaves charged work spent. Copy-on-write namespace staging
does not roll back work, time, object reservations or hardlink effort. Abort the
whole new-mode attempt after any model error; no poisoned tree can later finalize.

## Call correlation, not receipt-based authority

Before invocation, the fixed owner derives one grant context from its own run
identity and the actual collector state: sequence, layer ordinal, expected
compressed/diffID identities, canonical record index, cumulative archive ordinal,
bounded canonical-path digest, complete observed member SHA256 and member size.
These facts are provisional body correlation, **not** a complete-layer receipt.
No body reread, extra file or buffer copy is needed; the collector already
hashes and length-checks the complete member before parsing it.

The owner calls the actual fixed parser once with explicit opt-in, R and the
original deadline, then settles that exact outstanding call synchronously.
An earlier wrapper or another object's equal-sized receipt cannot settle it.
Validate exact wrapper/schema/profile, complete status matching return/refusal,
receipt.granted==R, receipt.used==metadata.work on success, and metadata body
SHA256/size against the actual invocation. Costs are copied/validated through
the foundation validator, never accepted from a JSON report or caller tally.
No success metadata is available from a failed or unknown-consumption call.

Replayed/duplicate/out-of-order/foreign-owner settlement and source/profile/
deadline mismatch poison rather than silently no-op. A receipt alone remains
forgeable DATA: source-owned construction, one invocation and call-correlated
state establish the integration contract. Synthetic tests cannot convert that
into independent authentication or physical CI/environment attestation.

The current parser does **not** echo a grant sequence, layer or owner token.
Do not claim that it does. A future source-owned adapter must retain the issued
grant identity in its own synchronous call frame and pair that actual return or
exception with its own context. Never derive invocation identity from parser-
supplied path/run fields or accept an externally serialized completion as proof
that the call happened. Tests must witness one real parser invocation, not only
validate a wrapper assembled by a stub. Correlation tags remain DATA, not seals.

## Staging and final-origin admission

Settle resource consumption **before** waiting for the layer's remaining bytes
or complete diffID. Keep metadata/cost/context correlation private to staging.
Only complete compressed/diffID verification returns staged records; only the
correct ordered model apply commits that layer. Neither partial body hashing nor
a healthy cost receipt may create an observation in the final graph.

Index observations by layer/canonical record ordinal, not basename, path alone,
SONAME alone or digest alone. At finalization bind the selected node's immutable
content origin (including retained hardlink origins) to that exact body's SHA256,
size, eligible original path and same model snapshot. Overwrite/whiteout/opaque
changes must not reuse a superseded observation. All observed/replaced bodies
still consume object/work budget; discarded staging does not refund them.

Keep the existing256 observed-object and2MiB encoded-observation limits. The new
branch must include any retained cost/context envelopes in that same byte cap,
not hide unbounded provenance outside it. Keep at most256 settled object records;
use bounded sequence/digests, not an unbounded grant history. Hash/ordinal/origin
mismatch, missing metadata or unknown provider still refuses, with no fallback.
Do not defer an unavailable object's failure or infer it has no dependencies.

Final success requires all ten layers, final namespace, both fixed selected root
hashes, original provider/SONAME/alias/search/cycle rules, complete closed grant
state, unchanged source and exact cleanup. Cost readiness is not graph readiness.
Even a later successful job is **static DT_NEEDED closure only**; runtime grants,
relocations, NSS/FIPS/dlopen/plugin/shutdown closure and legacy M native proof
remain unestablished. No site/production permission follows.

## Error and cleanup contract

Preserve the first parser/namespace/policy exception and finite reason. If cost
validation/settlement/diagnostics also fail, poison/burn conservatively and retain
the original failure plus bounded accounting disposition; never replace it with
raw exceptions, used0, an unavailable-success owner or a clean success status.
Use primitive fixed-schema facts/digests only, no bodies, strings, addresses,
tokens, URLs or arbitrary messages. Diagnostic construction cannot authorize use.

Cleanup is outside the work ledger: exhaustion, open grants, poison, expired
deadline or reporting failure cannot block the existing exact owned-artifact
cleanup/finally and always reconciliation. Do not broaden the13-leaf membership,
UID/marker/head binding, deletion roots, permissions or failure criteria. Cleanup
completion does not clear poison or turn failed qualification into success.

## Required isolated implementation evidence

| Contract | Non-vacuous evidence required before real selection |
|---|---|
| Bootstrap | Parse layer0 before model construction, then attach the same authority without reset or duplicate debit |
| Exact balance | Issue/settle reservation, exact and one-short budgets, parser and model alternating to exhaustion |
| Failed work | Valid failure debit, unknown receipt burn, no refund on discarded/invalid layer or model failure |
| Exclusive call | Second grant/model debit while outstanding, replay, wrong ordinal/body/run/profile and settle-twice all refuse |
| Actual composition | Fixed parser invoked once, real synthetic byte results, actual model ticks, no fake ready/unavailable sink |
| Origin | Full-layer commit gate, tampered SHA/size/index/snapshot, overwrite/whiteout/hardlink retained-origin cases |
| Clock/receipts | One original deadline, typed grants/finite clocks, hostile DATA, bounded facts and same first refusal |
| Boundaries | Default mode remains strict; no real entry/workflow selection, network, acquisition, native or site operation |

Assertions must kill removal of exclusive-grant, pre-debit, settlement matching,
layer verification, no-reset/no-double-charge and final-origin guards. Witnesses
must fail if a model/parser action happens before admission, not only compare
fabricated counters. A normal positive synthetic parse/model/graph must also
pass; an owner that always refuses cannot satisfy the suite. Distinguish those
isolated API/archive tests from genuine CI target admission or native proof.

Synthetic positives exercise the actual generic private ledger, parser and
model/origin contract with synthetic archive hashes. They cannot satisfy or
replace the real entry's pinned-image/root-hash qualification using invented
bytes. Keep that entry's pin matcher mandatory and separately tested; do not
patch ROOT_HASHES, add a real-entry test-mode/factory bypass, or relabel private
contract composition as end-to-end candidate admission. This separation is a
proof limit, not permission to manufacture a success path for coverage.

## Publication and exact next gate

This design changes documents only. Verify every non-document byte matches the
baseline, links/diff boundaries and existing isolated validation; publish and
verify exact-head ordinary CI. No manual qualification workflow, no new attempt
and no refresh of whole-plugin pre-release/Database Review/native evidence.

Executed local checks: existing isolated `php vendor/bin/phpunit
--do-not-cache-result` with the configured Python setting passes **2,601 tests /
23,419 assertions**, no failures/errors, two existing Sodium-unavailable skips.
`npm run check:structure`, affected local-link checks and `git diff --check`
pass; every non-document byte matches the baseline. No new authority tests or
implementation results are claimed. Exact pushed-head CI remains a publication
gate; its actual identity/results are recorded by that commit's checks and the
final handoff rather than assumed in this design. No manual job is dispatched.

**Recommended next gated slice: Local-only Socket-Free Fixed-Owner String-Access
Cost and Origin Integration Safety Foundation.** Approve the private single
authority and dormant model/collector integration contract using synthetic bytes,
owned temporary archive fixtures and isolated tests through review, validation,
commit, push and exact-head CI. Existing entry/workflow calls and strict defaults
must remain disconnected from the new profile. No acquisition, extraction,
candidate execution, SQL, installed-site or production change. A later explicit
fixed-entry/manual-mode qualification approval is still required to select it
on the real pinned candidate; no new flag or native attempt is authorized here.

Implementation-plan progress: bootstrap, shared budget and immutable-origin
requirements are now specified, but no owner integration or real admission is
implemented by this design. Campaign stays **7/10 milestones / 7/10 native
attempts / six static acquisitions**. No generic cleanup or deferred product
work is selected to fill the ceiling; the next implementation is the stated gate.
