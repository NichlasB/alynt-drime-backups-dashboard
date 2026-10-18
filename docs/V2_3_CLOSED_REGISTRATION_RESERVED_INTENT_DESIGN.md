# V2.3 Closed Registration and Reserved Intent Native Integration Design

Approved 2026-10-18, **repository-only/design-only**, R3 trust/concurrency design,
through validation, commit, push and exact-head CI. Owner: dashboard design;
prospective implementation owner: companion uploader. Excluded Git review bases:
dashboard `262315397f98926c059e1fb591a6b574203f9ad7`, uploader
`f010f40c06a29d80fdf2a92c1d41aa62f4d6f250`. These cover the coherent design delta,
not a later verification-only commit. Versions remain 0.1.77 / 0.5.25.

Refines [native storage/participants](V2_3_NATIVE_SCAN_EFFECT_STORAGE_PARTICIPANT_DESIGN.md).
Uploader `docs/V2_3_NATIVE_SCAN_LEDGER_TRANSACTION_PROOF.md` owns the completed
revision-only checkpoint. Its genuine **740 / 13** and ten controls are historical
evidence, not registration, provenance, native reservation or recovery proof here.

## Approved scope and decision

Map existing source -> separate closed registration/provenance grammar -> define
fixed construction, transitions and observation-only recovery -> adversarial design
review -> unchanged-code regression/QA -> commit/push/exact-head CI. No new source,
tests, schema installation, option provisioning, LocalWP/site/Novamira/SQL/browser/
fixture/user/session access, grant, wiring, activation, cancellation, rollback,
package/release/tag/deploy. Novamira/site checks are not run: no site is accessed.
Restore workflow Option A/check confirmed clean/synced uploader at its base and
existing external September 20 archive; no archive/backup/restore/branch created.

Choose a **new closed-proof domain/version**, not an extension which turns the
schema-1 synthetic `scheduled` label into authority. Proposed schema **2**, domain
`scan_registration_closed_v2`, fixed entry point `closed_native_proof`, `closed`
only true; no active/grant variant. This is permission to design that contract,
not accept/install it. Real scheduled registration remains forbidden from schema
8/null grant, regardless of a valid new ledger, nonce, UUID, digest or admin actor.
Future active consent requires separately designed provenance/schema/participants.

First native follow-up is ledger-only: register closed bookkeeping, then reserve
the fixed WPvivid snapshot intent as data, then separately observe historical
receipts. Never apply its candidate, start a producer or return a dispatch handle.
Outbox/queue reservations retain fixed slots but executable transitions are deferred;
no public effect selector or generic registration/write/resolve callback port.

## Source-backed boundaries

### Approved dormant native read-to-construction integration — 2026-10-18

Uploader `docs/V2_3_REGISTRATION_NATIVE_READER_PROOF.md` owns genuine **700 / 11**
for private guarded native two-sweep capture -> complete existing constructor.
F1 borrowed-session adoption and F2 hidden protected bulk-autoload fetching during
core authorization fixed only within the new private lane; shared guard unchanged.
All **77** application checksums unchanged, owned fixtures removed; no public
endpoint, production require or native write. A deliberate post-sweep fixture change
proves captured bytes can be stale even when a non-authorizing blueprint is formed.
Next gated slice: **local-only V2.3 Closed Registration Native Admission and Locked
Revalidation Design**, design-only same-stack handoff/all-ten locked comparison and
ledger-only receipt. This is not approval to commit captured candidate data.

### Approved captured-frame construction proof — 2026-10-18

Uploader `docs/V2_3_REGISTRATION_CONSTRUCTION_OWNED_PROOF.md` now owns genuine
core construction/eligibility proof (**851 / 10**) on approved disposable Plugin
Tester users/storage, with all **77** application checksums unchanged and fixtures
removed. Synthetic stored schema-eight/schema-two history is not authenticated
prior admission; test capture is not the proposed fixed authorizing reader. No
runtime source, production wiring or native transaction/apply changed. Next gated
slice is **Plugin Tester local-only V2.3 Fixed Registration Native Reader and
Guarded Construction Integration**, private/read-only with public unavailable.

All `includes/` paths below are relative to the separate uploader repository.

| Inspected source | Current limit / design consequence |
|---|---|
| `includes/class-closed-scan-effect-ledger.php`, `includes/trait-closed-scan-effect-records.php` | Schema-1 canonical structural data, synthetic scheduled tag, no event provenance. Leave this codec/domain unchanged; never promote old history. |
| `includes/class-scan-ledger-checkpoint-plan.php`, `includes/class-scan-ledger-checkpoint-request.php` | Full-frame digest and separate nonce, revision-only successor, refuses all pending work. Not registration, identity construction or a durable command receipt. |
| `includes/class-scan-ledger-checkpoint.php`, `includes/trait-scan-ledger-checkpoint-storage.php`, `includes/trait-scan-ledger-checkpoint-transaction.php` | One-shot private owner, all-ten locks, ledger-only CAS/readback and commit-or-poison. Preserve its public API, selector ceiling and refusal semantics; new purpose needs a separate owner/codec. |
| `includes/class-scan-effect-manifest.php`, `includes/class-scan-observation-data.php` | Fixed ten tuples; current observation checks reference consistency, not full safety/enrollment eligibility. The new closed proof must explicitly validate schema-8 disabled state, not accept a minimal safety-shaped object. |
| `includes/class-real-closed-writer-model.php`, `includes/trait-real-closed-writer-validation.php`, `includes/class-real-closed-writer-data.php` | Strict schema 8 has disabled lifecycle/false consent/null authenticated grant, retained command identities and bounded chain. Pure validators establish consistency only; require exact version 8, no pending old writer and unchanged generation for this closed ledger protocol. |
| `includes/trait-real-closed-writer-core-guard.php`, `includes/trait-native-closed-begin-session.php`, `includes/trait-native-closed-begin-storage.php` | Genuine fresh core authorization, narrow native metadata/session policy, definite ownership/acknowledgement. Reuse unchanged definitions where appropriate, not injected truth predicates or borrowed transactions. |
| `includes/trait-native-receipt-resolution.php` | Old two-row receipt purposes/barrier cannot resolve this ledger's identities or domains. New observation must take all ten locks and cannot authorize replay from absence. |
| `includes/class-scan-target-codec.php` | Fixed bounded typed candidate preparation only; no serialized-object loading, producer/file discovery or arbitrary whole-target proposal port. Hashes/valid deltas are not effect permission. |

## Proposed exact closed records and limits

### Approved in-memory contract refinement — 2026-10-18

The subsequent approved Contract Safety Foundation is owned by uploader
`docs/V2_3_CLOSED_REGISTRATION_CONTRACT_FOUNDATION.md`, excluded uploader
`1c0dc5b80ffcfbc9b556c57d73d2d3cfa18348cb` and dashboard
`156e762f20f119f02a83866684c9835ddfcfefd1`. It implements data consistency only.
The exact operation below now retains `registration_dependency_digest` so the
original frame-label/context can be reconstructed; this fixes a missing data
dependency, not authenticated provenance. Pure scope additionally pins origin
creation time. Reservation uses original operation times and its once-reserved
command ID/revision, making the stored receipt reconstructable without storing a
second arbitrary envelope. Both supported transitions retain revision headroom.

Only zero/one pending operation is supported; no terminal protocol/import or
16-operation history is implemented. Registration conservatively reserves 1536
record bytes for the supported WPvivid intent hashes/bookkeeping receipt/syntax,
in addition to the existing root closure/incoming reserves. Worst scalar shapes
pass both pure transitions. Future terminal/three-effect capacity, actual full-frame
eligibility, authentication, UUID issuance, native transactions and genuine storage
proof remain unimplemented. No production loading or old schema changes.

New tests **11 / 178**, related **157 / 2093**, five assertion-killed/restored
controls. Owning record contains source-scoped light/final security and exact QA/CI.
Recommended next gated slice: **local-only V2.3 Closed Registration Fixed Request
Construction and Eligibility Safety Foundation**, dormant/test-only; new trust
boundary needs approval. Storage writes, wiring, grants and execution stay disabled.

Use the same fixed ledger option/ten-row manifest; no new table or added dependency.
Schema-2 root key order: `schema_version`, `domain`, `site_uuid`,
`enrollment_binding`, `authority_generation`, `ledger_revision`,
`participant_digest`, `closed`, `origin`, `operations`.
`origin` is exactly `{kind, ledger_id, created_at}`: fixed kind
`owned_closed_fixture_v2`, fresh canonical UUIDv4 ledger ID and positive UTC integer.
This genesis is provisioned only by a later separately approved owned-fixture
supervisor, never by ordinary construction/reads. Its label is not evidence that
an arbitrary stored row actually came from that supervisor.

No automatic schema-1 import, conversion or on-demand genesis. The existing
observer/checkpoint MUST reject schema 2, and the new owner MUST reject schema 1.
Mixed, missing, old or reset history is unsupported, never an empty ledger. This
closed profile is deliberately unsuitable for production provisioning or upgrade;
that lifecycle/restore/downgrade decision remains gated. Do not widen legacy codecs.

Operation key order: `operation_id`, `command`, `provenance`, `context_digest`,
`registration_dependency_digest`,
`entry_point`, `generation`, `participant_digest`, `issued_at`, `expires_at`,
`registration_revision`, `reservation_command_ids`, `phase`, `effects`,
`terminal_id`, `resolution_id`, `registration_receipt`, `reservation_receipt`,
`terminal`. `command` retains the exact original typed registration envelope,
excluding nonce. `provenance` has exact keys `source`, `actor_id`,
`issued_command_digest`, `recorded_at`; fixed source
`genuine_core_closed_registration_v2`. Actor is the fresh core local integer ID,
not a posted actor/role. This private historical label/digest is not an auth seal.
Never store cookies, tokens, nonce bytes, passwords, session-token fingerprints,
raw rows, paths, SQL handles or serialized continuations. No public record export.

`effects` and `reservation_command_ids` have exactly ordered roles
`wpvivid_snapshot`, `outbox_snapshot`, `queue_addition`. Each effect retains the
schema-1 slot vocabulary as new-domain data, not imported history: effect_id,
purpose, state, dependency_digest, before_hash, after_hash, result_revision,
receipt. Initially state `reserved`, all intent/result fields null. The first
supported reservation changes only WPvivid's state to `intent_reserved` and pins
the complete dependency digest plus strict candidate before/after hashes; its
effect result_revision/receipt stay null. Operation phase becomes `intent_reserved`.
It is NOT committed/no_change/cancelled effect evidence. Others stay reserved;
terminal stays null. No synthetic terminal or cancellation success path.
Consequently the first supported registration occupies the sole pending slot;
without a separately approved terminal protocol this profile cannot register a
second operation. Do not clear/reset the record to make another attempt possible.

Registration/reservation receipts have exact ordered keys `domain`, `purpose`,
`command_id`, `operation_id`, `context_digest`, `result_revision`, `recorded_at`,
`classification`. Domain is the new schema-2 domain. Purposes respectively
`register_closed` / `reserve_wpvivid_intent`; classifications respectively
`registered_closed` / `intent_recorded_closed`. Reservation receipt context binds
the original registration plus newly prepared dependency/candidate hashes. Its
result_revision is the new ledger revision, NOT an effect result revision.

Canonical duplicate-key-rejecting plain JSON, exact ordered fields and UTF-8;
no floats, coercion, objects, references, defaults or callbacks. Retain 131072-byte
root, depth <=15 and 4096 key/value-node ledger acceptance caps; shared decoder
allocation ceiling remains separately bounded, not falsely called 4096 nodes.
Header <=4096, each operation <=6144, each new bookkeeping receipt <=1024 bytes,
at most 16 lifetime operations and one pending operation. Actual supported count
may be lower when byte/node caps bind. No pruning/TTL/reset/identity reuse.

Registration must validate actual encoded successor AND its maximum future slot/
receipt shapes, leaving 16384 terminal/closure bytes plus the existing 6144 incoming
record headroom. Reservation cannot consume the terminal reserve. These are design
constraints, not a claim that the larger format already fits: the pure follow-up
must prove maximum-size positive/negative cases or refuse unsupported shapes.
Do not raise caps merely to make fixtures pass. Integer revisions/generations have
at least five increments of headroom below PHP_INT_MAX; check before arithmetic.
All bookkeeping increments only ledger_revision, never permission generation.

## Fixed trusted construction and intent domains

Approved construction foundation (2026-10-18), uploader owner
`docs/V2_3_REGISTRATION_CONSTRUCTION_FOUNDATION.md`, now implements strict bounded
four-purpose forms, fixed core guards, complete eligibility and PRIVATE once-only
registration blueprint construction. Public issue/consume remain unavailable:
there is no authoritative reader, caller-frame port or public nonce/UUID/blueprint
export. Reservation/receipt grammar is guarded, not a producer or native consume
path. New **10 / 261**, related **250 / 2936**, four restored behavioral controls
are pure/isolated API evidence, NOT genuine core/storage/physical transaction proof.
Next gated slice: **Plugin Tester local-only V2.3 Closed Registration Construction
and Eligibility Owned Proof**, with exact target/disposable fixture confirmation;
no application state, production wiring, grants, activation or execution changes.

Future final/private factory is no-IO at construction, dormant/test-loaded, no
bootstrap/admin/REST/AJAX/cron/CLI registration. No public provider, enablement,
actor, target, clock, source or write-handle port. Private PHP visibility is not
a sandbox against compromised same-process PHP or a privileged database writer.

1. Small exact POST issuance form: `purpose`, `confirm`, `nonce`; reserve issuance
   additionally identifies the existing operation UUID. Proposed action
   `alynt_drime_backups_scan_registration_issue_v2:{server_blog}:{purpose}`.
   Fixed purposes `register_closed` / `reserve_wpvivid_intent`. Validate request,
   genuine current local session and fresh site-scoped manage_options plus nonce
   BEFORE protected reads. Pin original actor/cookie/core frame/native auth facts.
2. Read the fixed ten existing bounded rows. Outside locks validate exact tuples,
   schema 2/genesis, complete schema-8 disabled/false-consent/null-grant/no-old-pending
   chain, site UUID and generation. Require lossless settings inspection with
   auto_scan_enabled=false in this initial closed proof. Never call default/UUID-
   generating getters. Connection bytes are a freshness dependency, not pairing
   or enrollment eligibility. Unsupported/missing data refuses, not repaired.
3. After fresh authorization, registration privately generates **ten UUIDv4s once**
   outside locks using genuine core construction: registration command, operation,
   three effect IDs, three reservation-command IDs, terminal and resolution IDs.
   Reject collisions with site/genesis IDs, all retained ledger IDs and schema-8
   reserved/history IDs. No collision retry, caller IDs or remint after uncertainty.
   Canonical UUID syntax alone never proves internal generation.
4. Build an immutable same-stack blueprint binding those IDs, original actor,
   site/enrollment/generation/genesis, fixed entry point/manifest and the complete
   observed frame fingerprint. Proposed domain-separated context encoding hashes
   an exact ordered structure containing these fields and the typed command
   WITHOUT its context_digest/nonce; assign context_digest afterward (no cycle).
   Check JSON encoding succeeds. Core nonce creation stays outside locks; then
   renew authorization against the exact pinned frame/actor/session again.
5. Exact command form vocabulary: `contract`, `purpose`, `command_id`, `site_uuid`,
   `enrollment_binding`, `expected_generation`, `expected_ledger_revision`,
   `context_digest`, `issued_at`, `expires_at`, `confirm`, `nonce`; reservation also
   `operation_id`. Proposed contract `scan_registration_request_v2`, confirmations
   `register` / `reserve`. Fresh separate actions
   `alynt_drime_backups_scan_registration_command_v2:` and
   `alynt_drime_backups_scan_reservation_command_v2:` hash every ordered typed field.
   Exact-match the private blueprint; posted hashes/IDs cannot reconstruct it.
   Full raw/once-unslashed encoded body INCLUDING nonce <=4096, each value <=2048;
   canonical decimal magnitude before int cast, lowercase UUID/digest grammar.
6. Blueprint is single-use, noncloneable/nonserializable and never a public token.
   In a future owned proof, trusted harness instrumentation retains the non-secret
   command/operation lookup IDs BEFORE the attempt. No actionable public issuance
   or cross-request reconstruction is delivered by this design. Lost lookup IDs
   mean unavailable/manual investigation, never search-latest, reset or new IDs.

Registration and reservation must remain separate explicitly invoked commands,
not automatic continuation after commit. Reserve derives its command/effect IDs
from the retained registration; only the original actor may reserve initially,
with a currently valid session/capability. A new session needs fresh issuance,
not restored serialized auth. A separately authorized current admin may observe
historical facts, but observation never delegates mutation or adopts ownership.

Reserve prepares a genuine bounded typed WPvivid snapshot candidate outside locks
using the fixed codec and private owned-proof proposal source, not posted raw target
bytes or production discovery. Bind all ten dependency tuples, before/after hashes,
original registration digest and fixed effect ID. It may reserve a changed candidate
without applying it; do not invent a no-op-only success path to inflate coverage.
Original operation issued_at/expires_at remain immutable (window <=300s). A fresh
nonce or new ten-second invocation budget cannot extend that mutation deadline.
Expired work can only be observed through a fresh read-purpose window, not resumed.

## Ledger-only transactions and observation-only recovery

Proposed separate native owner reuses supported core/session/metadata policy only.
Do not widen the old checkpoint or two-row writer/resolver. Prefetch/prepare before
locks; renew auth; owned acknowledged BEGIN; fixed all-ten FOR UPDATE order; metadata
recheck after safety MDL; exact full tuples/scope/deadline; one ledger-only binary
ID/name/autoload/old-value CAS; full locked readback; latch COMMIT-sent before IO;
acknowledged native idle policy and close/release BEFORE core callbacks/output.
At most 30 protected row reads, one UPDATE/transaction, original ten-second
acceptance budget, native timeouts and no retry; blocking IO can exceed acceptance.

| Command | Locked prerequisite | Sole ledger successor | Never inferred |
|---|---|---|---|
| Register | Exact genesis/scope, fresh intent, no pending operation, all capacities/IDs valid | Append one closed registered operation, reserve ALL IDs/slots, receipt and revision+1 | Scheduled permission, effect or current grant |
| Reserve WPvivid | Exact OWN registered operation/actor, its once-reserved command/slot, original deadline, newly bound complete frame/candidate | One intent slot plus reservation receipt and revision+1; other rows/records unchanged | Effect committed, no_change, dispatch or upload permission |
| Observe registration/reservation | Fresh read-purpose auth and supported all-ten barrier; complete retained chain interpreted outside locks | NO UPDATE and no new durable unresolved/terminal marker | Replay, cancellation, present target equality or quiescence |

Foreign pending work blocks registration. Reservation is allowed to address only
its exact existing operation; do not reuse a blanket pending_count=0 test, or
remove that guard globally. Repeated/expired mutation refuses, even if a receipt
exists: separately observe it, never report a duplicate as a fresh write success.

Definite precommit failure: acknowledged owned rollback, no receipt claim. Unknown
BEGIN/COMMIT/rollback/close: poison/consume owner, close only owned handle, no
reconnect, compensation, remint or second attempt. An uncertain caller does NOT
write `unresolved` to the ledger; persisted before/after state remains authoritative.
An acknowledged commit remains historical if post-commit auth/budget later refuses.

Fresh exact lookup form: `contract`, `purpose`, `command_id` (fresh lookup UUID),
`site_uuid`, `observed_command_id`, `issued_at`, `expires_at`, `confirm`, `nonce`.
Proposed contract `scan_registration_observation_v2`, purpose fixed
`observe_registration` / `observe_reservation`, confirm `observe`; independent
action `alynt_drime_backups_scan_registration_receipt_v2:` hashes every typed field.
Same grammar/size/window guards, no original mutation-envelope reuse. A fresh lookup
window may follow expired work but grants no new mutation deadline.

Observation uses an exclusively owned native all-ten locking barrier, no UPDATE,
same metadata/policy/timeout/acknowledgement discipline; capture bounded raw tuples,
release/close, then validate the full schema-2 chain and renew core auth. The earlier
two-sweep reader is insufficient. Check exact original command/context/domain/IDs/
receipt revision, not current ledger bytes alone or a matching hash. Private output
may distinguish registered_closed, intent_recorded_closed, superseded history,
not_observed_at_barrier, domain_conflict or unavailable. All public support flags
stay false; no raw rows, IDs, private fingerprints or write handle exported.
The private read blueprint pins current actor/site/enrollment/genesis/generation
and manifest independently of caller lookup IDs. A scope/genesis change refuses;
lookup cannot adopt history across reenrollment, restore or a replaced ledger.
Observed IDs select only bounded retained records after validation, never a SQL
identifier, arbitrary option, search-latest query or cross-site lookup.

The barrier waits behind a transaction already owning the shared lock; timeout
means unavailable. **Absence is not rollback, no-effect, retry or terminal proof**:
an earlier authorized request may not yet have acquired its lock. Even an expired
request/unchanged row cannot prove no delayed COMMIT was sent. Closing/cancelling
that possibility requires a future durable serialized fence, not this read-only
lookup. Receipt presence proves only its historical closed bookkeeping transition
within the proven native-writer/storage assumptions, never protection from database
tampering, restore/rollback or compromised PHP. No effect receipt is manufactured.

## Review decisions, evidence requirements and next gate

Focused source-backed design review resolved three key gaps: schema-1 synthetic
tags cannot become authenticated provenance; the reservation must recognize its
own pending operation without permitting foreign work; absence after a lock barrier
still cannot authorize retry. Version isolation, fresh purpose domains, complete
frame/actor binding, unchanged generation, capacity and poisoned-owner rules above
are normative proposals. No source defect fixed or canonical Feature/DS3/Database/
07A/security workflow completion claimed. Historical toolkit rows stay unchanged.
UI/UX N/A; full pre-release remains reserved for a later coherent release candidate.

| Future layer | Required discriminator, not claimed as executed here |
|---|---|
| Pure grammar/model | Positive register -> own reserve and unchanged other history/generation; foreign pending, legacy/mixed domains, malformed/overflow/collision/size/node/reserve rejection; absent/expired receipt never executable. Maximum successor shapes must actually fit. Always-unavailable plans must fail positives. |
| Guard-removal controls | Removing any auth phase, any dependency, purpose-domain separation, actor/ID/phase, reserve/capacity, exact CAS/readback or sent-before-IO latch must break a behavior assertion, not merely a mock call count. |
| Genuine core / owned native (separate approval) | Zero protected reads before auth; real role/session revocation; all-ten locks and stale dependencies; exact registration/reservation receipts; no callbacks under locks; pre/post-write failures, cleanup and independent unchanged application checksums. In-memory results are not this proof. |
| Unknown-outcome / later integration | Deterministic competing in-flight requests versus lookup; genuine dropped acknowledgement/crash testing needs its own isolated plan. Effect/cancel races, active consent, participant/cache/producer/lifecycle compatibility remain separate gates. |

Pure-model fixtures may supply canonical identities to test grammar and successor
behavior; that is not a public native construction seam or evidence of genuine
UUID generation, authenticated provenance or physical storage. Positive closed
bookkeeping assertions must coexist with all grant/execution flags staying false.

Implementation-plan progress: registration identity/provenance, separate reserved
intent and non-authorizing unknown-outcome observation are now concrete contracts;
native checkpoint evidence is not mistaken for their implementation. No site was
accessed and no runtime evidence rerun by this design-only slice.

Recommended next gated slice: **local-only V2.3 Closed Registration and Reserved
Intent Contract Safety Foundation**, dormant/test-loaded in-memory schema-2 codec,
bounded successor/reserve/lookup models and adversarial controls only. Keep actual
UUID/auth construction, storage/fixtures, production wiring, grants, activation,
effect execution, cancellation and rollback disabled. Approve this new data-contract
implementation scope; native owned-fixture proof follows only after its own gate.

## Validation and publication

Executed repository-only on 2026-10-18. These checks exercise unchanged
implementations, NOT this unimplemented schema-2/auth/native protocol. No new test
was added just to count a design document.

Uploader commands/results:

- `php vendor/bin/phpunit --do-not-cache-result --filter 'ScanLedgerCheckpoint|ClosedScanEffectLedgerTest|ScanEffectManifestTest|ScanEffectFenceContractTest|ScanTargetCodecTest|ScanObservationContractTest|ScanNativeObserverBoundaryTest|RealClosedWriter|NativeClosedBeginBoundary|NativePairedBoundary'`:
  **146 tests / 1915 assertions**, no skips.
- `npm run lint`: **192 files**, passed; existing PHPCS/PHPCompatibility
  functionWhitelist array-syntax deprecation banner remains, not a new finding.
- `npm run build`, `npm run verify:runner`, `npm run lint:runner`: passed;
  generated runner/assets unchanged.
- `npm run test:dev-tools`: **3 tests**, passed.
- `npm audit --audit-level=moderate`,
  `php ../alynt-drime-backups-dashboard/composer.phar validate --strict`,
  `php ../alynt-drime-backups-dashboard/composer.phar audit`: passed, zero reported
  vulnerabilities/advisories.

Dashboard commands/results:

- `php vendor/bin/phpunit --do-not-cache-result`: **299 tests / 1423 assertions**,
  two existing Sodium-unavailable signing skips in this local PHP runtime; no
  tests weakened or skipped by this change. Linux CI must verify the full suite.
- `npm run lint`: **138 files**, passed.
- `npm run check:structure`, `npm run build`: passed; assets unchanged.
- `php composer.phar validate --strict`, `php composer.phar audit`,
  `npm audit --audit-level=moderate`: passed, zero reported vulnerabilities/advisories.

Both `git diff --check` checks passed. All **17** referenced uploader source paths
and the owning design's predecessor link exist. Final scope is dashboard three
design/plan documents and uploader one plan document only; no dependency,
runtime/generated asset, test, version or toolkit checklist changes. No native,
authentication/session, SQL, physical transaction or new schema capacity proof
executed.

Publication receipts, exact pushed heads verified 2026-10-18:

- Dashboard design/plan `e999a2ace8e1f2cbea8c27d63256fa64a714b41a`:
  [CI 39159480398](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/39159480398)
  **success**, PHP 7.4 and 8.3 each **299 tests / 1430 assertions**, no skips;
  lint/structure/build/manifest/audits passed.
- Uploader plan `1c0dc5b80ffcfbc9b556c57d73d2d3cfa18348cb`:
  [Quality 39159488936](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/39159488936)
  **success**, PHP 7.4 and 8.3 each **1440 tests / 12865 assertions**, no skips;
  lint/proof-script syntax and Node dev-tool/build/runner/audit jobs passed.
- This receipt-only dashboard follow-up changes no reviewed contract or source.
  Its final exact-head CI is checked separately at handoff; the runs above are
  evidence for the named commits, not an assertion that a later commit ran them.
- Toolkit remains unchanged at `dc5bc89a507494217ee934150c98edcbdb01181c`;
  no canonical review/checklist or historical whole-plugin row refreshed.

The registration/reservation implementation remains the next approval boundary.
Green unchanged-code CI does not prove the proposed new protocol, capacity,
authentication, physical transactions or live release readiness.
