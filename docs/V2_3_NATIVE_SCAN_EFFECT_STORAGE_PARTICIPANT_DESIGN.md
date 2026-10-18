# V2.3 Native Scan-Effect Storage and Participant Integration Design

Approved **repository-only, design-only**, 2026-10-18; R3 concurrency/permission
design. Owner: dashboard design, prospective implementation owner: uploader.
Excluded review bases: dashboard `12792bdc8fd3974916a609a100f3ba426a2c574f`,
uploader `63cfeefd988f37923527410f08d32e7d99d06276`. Scope is this coherent design
and directly related plan/cross-reference records, not merely a later receipt.

Refines [cache/scan-stop admission](V2_3_CACHE_CONVERGENCE_SCAN_STOP_ADMISSION_DESIGN.md).
Uploader `docs/V2_3_SCAN_EFFECT_CACHE_CONTRACT_FOUNDATION.md` owns the preceding
pure models; their positive candidates and eight counterfactuals are historical
evidence, not native authority or proof of the storage proposed here. No actual
reader/cache/SQL/fixture/site operation, schema migration, production wiring,
grant, activation, rollback, release/package/tag/deploy is authorized by this design.

## Decision and trust boundary

Prefer a **separate fixed scan-effect ledger option** plus the existing fixed
options, on the same pinned core InnoDB options table. No new table, global mutex,
generic SQL/callback provider or caller-selected target. The ledger name and all
new fields/caps below are **proposals, not installed contracts**. Current schema-8
safety remains closed-only with `authenticated_grant=null`; no current schema can
admit new real scheduled work. A future active grant and its versioned migration
remain separately gated. Synthetic ledger data never fills that gap.

The existing `includes/class-native-closed-begin.php` owner has a private two-row
selector, eight-read budget, fixed safety/settings CAS and private continuations.
Do not widen that Boolean selector, reuse its receipts for scan effects, expose a
public write handle or reinterpret its begin/complete/receipt purposes. A later
native implementation needs a separate fixed owner, reviewed profile and proof.

Current pure `snapshot`/`queue` revision counters abstract exact row/CAS ordering;
they are **not** stored versions, a payload codec or trusted participant discovery.
Real participation must include every writer of shared rows, not only operations
which require the future scan grant. Independent upload policy remains independent.

## Fixed manifest and global lock order

All source paths below are uploader-relative. Actual constants were inspected.
First native proof should use one immutable ten-row manifest and lock **all ten in
this order** for every participating storage transaction. This deliberately favors
auditable ordering over premature subset/concurrency optimization. Future smaller
manifests require separate review; no caller may choose rows or lock order.

| Order / role | Fixed option name | Proposed maximum value bytes | Current source / future obligation |
|---|---|---:|---|
| 1 safety | `alynt_drime_backups_schedule_safety_state` | 131072 | `includes/trait-native-closed-begin-storage.php`; raw generation/closed transition anchor |
| 2 settings | `alynt_drime_backups_settings` | 1048576 | `includes/class-settings.php`; exact bytes, no defaults/UUID creation in admission |
| 3 connection | `alynt_drime_backups_dashboard_connection` | 131072 | `includes/class-dashboard-connection.php`; raw enrollment/semantic binding, private credentials never emitted |
| 4 ledger (new proposal) | `alynt_drime_backups_scan_effect_state` | 131072 | New domain only; durable registrations, intent/receipt slots and closed drain accounting |
| 5 WPvivid snapshot | `alynt_drime_backups_file_snapshots` | 524288 | `includes/class-wpvivid-producer.php`; split discovery from snapshot persistence |
| 6 outbox snapshot | `alynt_drime_backups_outbox_file_snapshots` | 524288 | `includes/class-generic-outbox-producer.php`, `includes/trait-generic-outbox-producer-scan.php` |
| 7 queue | `alynt_drime_backups_upload_queue` | 1048576 | `includes/class-queue.php`; add/add_many/prepend AND remove/attempt writers participate |
| 8 active upload | `alynt_drime_backups_active_upload` | 262144 | `includes/class-queue.php`, `includes/trait-uploader-active-upload.php`; independent upload state, no cancellation inferred |
| 9 uploaded registry | `alynt_drime_backups_uploaded_files` | 1048576 | `includes/class-backup-registry.php`; fresh duplicate/removal dependency |
| 10 failed registry | `alynt_drime_backups_failed_uploads` | 1048576 | Same registry plus `includes/trait-plugin-failed-upload-actions.php`; requeue/failure dependencies |

These bounds are a conservative **initial proof-profile proposal**, not measured
production suitability or new limits on legacy behavior. Every required row must
already exist exactly once with a pinned option ID, exact ASCII name and supported
non-autoload marker (`no`, `off`, `auto-off`). Missing/duplicate/wrong-scope or
oversized rows refuse admission, never become an empty/default row. Creation of
missing rows, stable empty active-state representation, closed ledger provisioning
and any migration away from delete-on-empty need separately approved closed-first
bootstrap/lifecycle work. Ordinary status, cron, construction and reads create nothing.

Preserve current strict single-site/builtin-cache/no-drop-in/native-session and
metadata refusals; do not grant database privileges to satisfy a profile. Require
the fixed full unique option-name index, InnoDB, no unsupported triggers/FKs and
schema/metadata revalidation after the first safety lock acquires table MDL.
Identifiers come only from a pinned genuine-core frame. Project byte lengths and
caps before fetching values; exact binary ID/name/autoload/value comparison follows.
Never silently truncate, normalize or broaden the existing native profile.

## Proposed closed ledger / receipt grammar and capacity

Proposed domain `scan_effect_storage_closed_v1`, schema 1, strict duplicate-key-
rejecting canonical JSON; maximum 131072 bytes, depth 16, 4096 scalar/key/value
nodes. Exact root vocabulary: `schema_version`, `domain`, `site_uuid`,
`enrollment_binding`, `authority_generation`, `ledger_revision`,
`participant_digest`, `closed`, `operations`. `closed` is **only true** in the
first closed codec; no grant/default/active variant. No migration to this format
or change of existing safety schema is implemented by the design.

Existing site UUID, enrollment digest and lifecycle permission generation are
bound, never generated from cached defaults. Canonical integer/range checks must
preserve existing generation semantics and platform overflow refusals; generation
values never pass through floats. `ledger_revision` is separate from the authority
generation: storage bookkeeping must not invent consent or increment the existing
permission generation for ordinary progress. Refuse overflow before arithmetic.
An absent ledger is unsupported, not never-activated/closed/quiescent evidence.

At most **16 lifetime operation records**, one nonterminal operation at a time.
An operation record is at most **6144 encoded bytes** and pins its UUID, original
command/owner context digest, scope/enrollment/generation, trusted entry-point tag,
participant-manifest digest, phase, issued/deadline facts, fixed effect slots and
terminal record. Synthetic 64-hex IDs in the earlier model are not promoted into
native identities; actual fresh internal UUID construction is a later trusted port.
No posted owner/entry-point/participant flag or copied cookie grants admission.

Proposed exact operation keys: `operation_id`, `command_id`, `context_digest`,
`entry_point`, `generation`, `participant_digest`, `issued_at`, `expires_at`,
`phase`, `effects`, `terminal_id`, `resolution_id`, `terminal`. Site/enrollment scope is inherited
from the root and included in context digests, never inferred from a caller.
`phase` is `registered`, `intent_reserved`, `draining`, `unresolved` or `terminal`;
these are closed proof-data states, not an active authority flag. `entry_point`
initially accepts only the internally selected scheduled domain for synthetic
records. Reject actual admission regardless of a structurally valid record.

`effects` has exactly `wpvivid_snapshot`, `outbox_snapshot`, `queue_addition` slots.
Each slot has `effect_id`, `purpose`, `state`, `dependency_digest`, `before_hash`,
`after_hash`, `result_revision`, `receipt`. Unprepared slots have exact null
intent/receipt fields; reserved and terminal slot consistency is validated, not
filled by defaults. Slot states: `reserved`, `intent_reserved`, `committed`,
`cancelled`, `unresolved`. A terminal receipt has exact keys `domain`, `purpose`,
`operation_id`, `effect_id`, `context_digest`, `dependency_digest`, `before_hash`,
`after_hash`, `result_revision`, `classification`; its classification is one of
`committed`, `no_change`, `cancelled`. An uncertain local acknowledgement is not
an invented durable committed/cancelled receipt. `no_change` requires equal target
hashes; `committed` requires distinct hashes. Cancellation does not claim an effect:
its before/after hashes are null, with a fresh dependency digest and fixed purpose.
Operation `terminal` is null or a fixed record with exact keys `domain`, `purpose`,
`terminal_id`, `operation_id`, `context_digest`, `result_revision`, `effect_receipts`.
The last field maps the three fixed slots to their terminal receipt digests; reject
terminal while any slot is reserved/unresolved. The once-reserved `resolution_id`
is a separate identity for future authenticated whole-operation cancellation, not
a retry ID or permission. Every receipt revision is bounded by the strictly
monotonic root revision; any update requires a new root revision without overflow.
All UUIDs are canonical existing lowercase UUIDs, all digests lowercase SHA-256,
all revisions/times strictly typed bounded integers. Null is permitted only in
explicit unprepared fields or cancellation hashes, not to infer a missing owner,
epoch or terminal fact.

Reserve three fixed effect identities/slots (WPvivid snapshot, outbox snapshot,
queue addition), one terminal identity and a cancellation/resolution slot at
registration. Each effect pins its original purpose/ID, ordered dependency tuple
digests, before/after target digests and resulting ledger revision. Receipts record
only fixed domain/type/IDs/digests/generations/classification, never raw options,
paths, package records, cookies, tokens, connection credentials or SQL handles.
Private digests are not public diagnostics or proof of who produced the data.

Reserve **16384 bytes for closed-first terminal/resolution/closure accounting**;
new registration must fit actual encoded successor plus worst-case reserved slots,
not merely pass an item count. Root envelope must fit 4096 bytes; the per-record,
total/node/depth and reserve bounds all apply simultaneously. A close/cancel must
not fail merely because ordinary history used its reserved capacity. Bounds and
maximum successors need discriminating tests before native code. No automatic TTL,
history pruning, identity reuse, truncation or reset. At capacity refuse new work;
safe archival/long-running production capacity is a separate lifecycle decision.

The closed format may represent synthetic reserved/terminal slots for pure proof;
it cannot admit real scan registrations. An active format requires explicit new
provenance/version/consent approval, not toggling `closed` or reusing schema-8 null
grant. Closed codec validation is not authenticated event or migration proof.

## Target codecs and exact mutation rules

Keep `includes/class-serialized-settings-parser.php` and
`includes/class-serialized-settings-target.php` unchanged. Their current one-token
disable codec, UTF-8/plain-data subset, 1 MiB/4096-node/depth limits do not establish
a generic queue/snapshot parser. Prefer new **fixed-role byte-span codecs**, no PHP
`unserialize`, objects, references, autoloading or callbacks; reject unsupported
syntax, duplicate/coercion-colliding keys, trailing bytes and lossy numbers.

Proposed snapshot/queue/registry/connection codecs accept bounded plain serialized
arrays/scalars with exact string-byte lengths and validated UTF-8, depth at most 15,
4096 nodes per row, and role-specific byte caps above. Snapshot maps have at most
512 exact lowercase SHA-256 keys and nonnegative bounded integer `size`/`mtime`
values. Queue/registry maps have at most 128 records; a queue key must equal its
record signature. New queue proposals add at most 64 items / 262144 bytes per scan.
All applicable limits must pass; record caps do not override node/byte limits.
No existing application row is silently filtered or repaired to fit this subset.

Preserve untouched raw entry spans, key order and supported unknown fields; changes
are explicit typed deltas against a fully validated current root. Encode new
entries/count tokens deterministically, then reparse and verify only the intended
delta. Queue add/prepend uses fresh queue/active/uploaded/failed dependencies and
the existing signature/path/WPvivid duplicate semantics from
`includes/trait-queue-duplicate-index.php`; do not replace them with hash-only dedup.
No caller-supplied whole queue, arbitrary field/option writer or proposed cache array.
The initial codec's queue verbs are fixed `append_new`, `prepend_requeue`,
`remove_existing`, `set_attempts_existing`; they do not change producer identity,
path or signature on an existing entry. Snapshot verb is fixed `replace_snapshot`
for its one producer role. Initial closed codec tests validate these deltas as
data only; they never make requeue, upload progress or snapshot persistence legal.
Snapshot replacements are confined to their producer role and exact before tuple;
removing old snapshot entries does not permit file deletion or registry cleanup.

Settings changes preserve current site identity and unrelated bytes; connection
timestamp/error deltas preserve fresh current semantic flags and credentials.
Even nonsemantic writes require exact current CAS. Enrollment binding version 1
and existing nonce domains do not change merely to solve stale-copy races.
Raw connection data and target spans remain private throughout parsing/review.

## Native transaction and unknown-outcome protocol (future, not implemented)

1. Trusted construction selects one fixed purpose and genuine entry point, checks
   authentication/authorization/intent before protected reads, pins frame/profile/
   original budget, and creates a private single-use owner. Request-purpose and
   receipt-purpose nonces remain separate; scheduled authority needs its separately
   proven durable grant. Current closed-only state still refuses that admission.
2. Outside locks, prefetch the fixed bounded rows, validate codecs/semantics, prepare
   exact before/after deltas, once-reserved identities and receipt bytes. Producer
   file/sidecar work is outside transactions and cannot persist snapshots/queue yet.
   Check scope/current authorization again before opening the critical section.
3. On the exclusively owned native session, start one transaction, lock all ten
   rows in manifest order, recheck pinned metadata/session/tuple bytes and generation,
   matching owned registration and purpose-specific allowed phase. Compare **every
   dependency**, including uploaded/failed/active rows; equality of the queue alone
   is insufficient. No core auth/filter/option/cache/producer/file/network callbacks
   under locks. Budget remains the original acceptance budget, not a renewed lease.
4. Persist only the fixed effect target plus its ledger receipt/phase in the same
   transaction, with binary exact CAS, expected affected rows and locked readback.
   A no-op has an explicit ledger receipt; zero affected rows is not success by
   default. Closure must update safety generation/closed ledger accounting together
   under the same manifest, not use the old two-row owner alongside unfenced effects.
5. Latch COMMIT-sent before I/O. Release/close the owned session before core calls
   or publication. Acknowledged commit plus exact locked readback establishes the
   historical result even if later reads differ; a fresh authorization failure can
   still withhold outward success. Unknown acknowledgement leaves the original durable registration/
   reserved effect identity unresolved; no reconnect/replay, compensation, timeout
   clearing, regenerated ID or legacy fallback. Cache eviction remains hygiene only.
6. Fresh separately authorized locked resolution observes the same fixed receipt
   domain/identities/dependencies. An exact receipt establishes historical outcome,
   not present permission or current bytes. Absence by itself is **not** a retry or
   quiescence certificate. A future cancellation must atomically close/cancel the
   reserved slot under this barrier so late effect attempts fail CAS/phase checks;
   effect-wins must yield its receipt, cancel-wins must prevent the effect. This
   operation needs separate native competing-process proof and trusted authorization.
   No cancellation or write-capable resolution is provided by this design.

Registration, original intent reservation and each intent-before-attempt transition
must be durable before producer/effect work. Receipts and target effects are atomic;
a socket disconnect cannot silently forget which effect may have been attempted.
Terminal accounting requires all fixed slots confirmed terminal or safely fenced/
resolved. A producer error does not erase earlier committed snapshots. Closing
ingress stops new affected writes but not file reads already running; no effective
quiescence claim until authoritative terminal accounting and verified participants.

Proposed first-profile invocation ceilings: four ten-row passes (prefetch, locked
comparison, locked readback, post-commit observation), at most **40 protected row
reads**, one owned transaction and no automatic retry. Each pass is capped at **6
MiB** aggregate raw bytes; retain at most one parsed row/tree at a time, with private
before/after raw buffers bounded separately. This is not the existing owner's
eight-read budget, a measured memory guarantee or hard cancellation of blocking I/O.
Each separately constructed one-shot owner starts its ten-second acceptance budget
before guards/reads. That budget never extends the original operation/effect intent
deadline; no refresh of issued/expiry fields to accommodate a long scan. Discovery
runs outside locks with bounded preparation; an expired effect refuses persistence
and needs separately authorized observation/cancellation, not re-admission or replay.

## Participant map and independent upload coexistence

| Actual surface | Required future participation, not changed here |
|---|---|
| `includes/class-cron.php`, `includes/class-plugin.php` | Guard actual callback, including already-dispatched wake-ups. Production currently injects no authority dispatcher. No schedule clearing/recreation as a stop certificate. |
| `includes/trait-plugin-admin-actions.php`, `includes/class-cli-command.php`, `includes/class-remote-action-worker.php` | Shared admission plus sink guards; rejected combined CLI run/remote scan must not proceed into upload/schedule-upload. Legacy manual semantics remain intact outside future explicitly consented mode. |
| `includes/class-scanner.php`, both producer sources above, `includes/trait-plugin-scan-queue.php` | Register before producers; split bounded discovery/preparation from snapshot persistence; separately commit each producer snapshot and final queue delta, retaining earlier receipts if later work fails. Unknown/custom producer cannot claim manifest participation. |
| `includes/class-queue.php`, `includes/trait-plugin-failed-upload-actions.php` | Direct add/add_many/prepend and retry/requeue cannot bypass sink policy. Failed requeue is an existing-queue workflow with separately authorized intent, not fresh scheduled discovery. |
| `includes/class-settings.php`, `includes/trait-settings-site-identity.php`, connection state/enrollment traits | All whole-row writers, UUID/default saves and timestamp/error bookkeeping must participate or be refused in active authority mode. Semantic changes close first and never restore a stale grant after a later settings-write failure. |
| `includes/trait-uploader-failure-handling.php`, `includes/trait-uploader-retry-state.php`, active/multipart traits, `includes/class-backup-registry.php` | Queue removal/attempts and uploaded/failed/active bookkeeping use fresh deltas and ordered CAS. Their upload permission remains independent: closure is not permission to abort/reset/retry or suppress already-authorized upload progress. |
| `includes/class-activator.php`, `includes/class-deactivator.php`, `uninstall.php` | No auto-grant/reset/default repair; deactivation does not kill old workers. Preserve unresolved evidence; uninstall/downgrade/restore require explicit lifecycle runbook and approval. |

Current upload completion in `includes/trait-uploader-failure-handling.php` marks
uploaded, clears failed, removes queue and clears active in separate helper writes.
Future local bookkeeping should have one fixed atomic completion intent over those
dependencies; **do not** retry the Drime upload because bookkeeping is uncertain.
Multipart/network/file/retention side effects stay outside the SQL transaction and
outside scan-stop guarantees. Independent uploaded-location/retention writers must
join if they alter a dependency row, even when they do not need a scan grant.
The registry's separate Drime-location option is not added to this manifest unless
a later proven dependency requires a separately reviewed fixed expansion.

Before active deployment, require a coherent participant release, closed bootstrap,
maintenance/drain plan for old binaries/already-running legacy callbacks and verified
worker accounting. A manifest digest or new version alone cannot prove all processes
participate. No production activation while any first-party writer is uncoordinated,
required row absent or capacity/profile refusal unresolved. No automatic fallback
from preparing/draining/uncertain to legacy. No destructive cleanup to make room.

## Acceptance and next gated implementation

| Layer / discriminator | Required before runtime claims |
|---|---|
| Pure closed ledger/codecs | Positive bounded closed records and exact typed deltas; malformed/duplicate/object/reference/UTF-8/overflow/limit rejection; unrelated bytes preserved; reserve counts/bytes cannot be consumed by registration; every output non-authorizing. Always-unavailable codecs must fail positive tests. |
| Fixed manifest / policy model | Wrong role/name/order/scope/identity and stale dependency reject; same original own registration differs from foreign pending work. Reject all real admission from schema 8/null grant. Removing one dependency/CAS/phase/reserve guard must break a targeted test. |
| Owned native fixtures (separate approval) | Ten exact disposable rows only; real lock barriers, first-write failure/rollback, unknown commit and effect-versus-cancel ordering, ID/autoload/byte/metadata conflict, no callback under locks, unchanged application tables. Pure data is not Database Review or physical proof. |
| Genuine producer/queue/core integration (later gate) | Snapshot/queue closure races, direct sink bypass, upload removal/attempt and registry races, timestamp-vs-revoke, auth revocation, original-scope cache publication after release; no real Drime/client actions from fixtures. |
| Lifecycle/release (later gate) | Explicit closed provisioning, stable empty rows, capacity/archival, participant drain, nonparticipant/restore/downgrade refusals and fresh consent format; full pre-release before package/publication, separate live backup/deploy approval. |

Focused source-scoped architecture/security/error/concurrency/resource review
selected the fixed manifest, separate closed ledger domain, exact delta codecs,
terminal capacity reserve and all-writer participation above. No runtime defect is
fixed, new schema installed or canonical Feature/DS3/Database Review completion
claimed by this design-only slice. Historical toolkit rows stay unchanged; no UI/UX
workflow applies. Validation below checks unchanged-code regression, not this design.

Implementation-plan progress: native row ownership, bounds, lock ordering and
participant obligations are now concrete; actual native effects/consent remain gated.

Recommended next gated slice: **local-only V2.3 Closed Scan-Effect Ledger and Fixed
Target Codec Safety Foundation**. Implement the closed-only ledger/reserve grammar,
fixed manifest and snapshot/queue typed-delta byte codecs as dormant test-loaded
in-memory components, with non-vacuous positive cases and guard-removal controls.
Keep current owners/parsers/schema-8 meaning unchanged; no actual readers/cache/
storage/fixtures, production wiring, grants, activation, cancellation or rollback.
Owned native proof and real participant changes require subsequent explicit scope.

## Subsequent closed data foundation — 2026-10-18

The approved next milestone is implemented in companion uploader
`docs/V2_3_CLOSED_SCAN_EFFECT_CODEC_FOUNDATION.md` against excluded uploader
`a73f558969af5c397862b03ae38cbb00fbab4fd7` and dashboard
`d2482d07fd0060ec5d2fd9fc3a81266e032e0312`. Fixed manifest, closed ledger/reserve
grammar and snapshot/queue typed byte-span deltas are dormant/test-loaded pure
components, not the native owner or real participants proposed above. The owning
record defines exact closed context encoding and F1/F2 regression fixes, focused
canonical reviews, resource/proof limits and exact-head CI. No schema-8, parser,
native two-row owner, production wiring or application state change.

Pure **23 / 259** and related **103 / 1231**, no skips, plus nine assertion-killed/
restored controls do not prove native locking, commit, core authorization, cache
convergence or physical stop. No actual reader/cache/SQL/fixture/site operation.
Historical design validation below stays historical. Recommended next gated slice:
**Plugin Tester local-only V2.3 Fixed Scan-Effect Manifest and Closed Target
Observation Proof**, dormant fixed native read-only observer with separately
approved owned disposable ten-row fixtures/cleanup, existing application state and
production wiring untouched, grants/activation/cancellation/rollback disabled.

## Subsequent native observation proof — 2026-10-18

Uploader `docs/V2_3_NATIVE_SCAN_OBSERVATION_PROOF.md` owns the approved Plugin Tester
local-only fixed ten-row observer and three-table owned fixture proof. Excluded
uploader `08a47072e934b28f0fae0eb996e6648d1b3270df`, dashboard
`e867c6d256938d4c20a2f4885904fc9c566c1f34`. Genuine guarded private observation
**245 / 11 groups**, eight valid restored controls, unchanged guard **266 / 11**
and prior observer **122 / 8** rerun. Every run removed owned fixtures and preserved
all **77 application-table checksums**. Public support flags remain false; no
production loading, actual application state or schema-8/grant meaning change.
These are native read/profile/nonce/refusal results, not locking, atomic snapshot,
CAS/commit, complete runtime eligibility, cache convergence or physical scan-stop.

Implementation-plan progress: the initial observation boundary now has genuine
evidence. Recommended next gated slice: **Plugin Tester local-only V2.3 Closed
Scan-Effect Ledger Transaction Foundation and Owned Failure Proof**, fixed ten-row
locks and closed bounded ledger successor/CAS/readback/acknowledged commit with
owned ledger-fixture writes only. Separate approval; actual snapshot/queue effects,
application state, wiring, grants, activation, cancellation and rollback disabled.
Historical design and preceding pure-foundation evidence remains historical.

## Validation and publication

Executed repository-only checks on 2026-10-18:

| Check | Result / limits |
|---|---|
| Uploader targeted PHPUnit command below | **116 tests / 1210 assertions**, no skips, 1.429s. Existing isolated tests, not new native/WordPress/cache/participant proof. |
| Dashboard `php vendor/bin/phpunit --do-not-cache-result` | **299 / 1423**, two existing unrelated Windows skips; no local no-skip claim. |
| Dashboard `npm run lint`, `npm run check:structure`, `npm run build` | WPCS **138 files**, structure/build pass; no generated change. |
| Dashboard `php composer.phar validate --strict`, `php composer.phar audit`, `npm audit --audit-level=moderate` | Valid manifest and no advisories/vulnerabilities; dependencies unchanged. |
| Reference/diff review | **26** distinct quoted uploader source paths and the relative predecessor design link resolve. Row names checked against actual constants/native selectors. Only owning design, predecessor cross-reference and two plans change; no source/tests/bootstrap/assets/schema/version changes. |
| Source safety | Uploader restore workflow Option A/check: clean/synced master at excluded base, checkpoint eligible, existing external September 20 archive. No backup/restore/archive/branch created. |

Uploader targeted command:
`php vendor/bin/phpunit --do-not-cache-result --filter 'ScanEffectFenceContractTest|CachePublicationContractTest|QueueTest|ScannerTest|SettingsTest|SerializedSettings|CadenceAuthorityEntryPointPolicy|NativePairedBoundary|NativeClosedBeginBoundary'`.

No new runtime tests are appropriate for an unimplemented design. Existing native
physical proof totals are historical, not rerun. No site, Novamira, SQL, fixture,
actual cache or application state operation occurred. Exact pushed-head CI for
both repositories must pass before completion; earlier-head results do not replace
the final-head checks. Design review clarified cancellation versus effect receipt,
historical commit versus later bytes/outward authorization, and original intent
deadline versus per-invocation acceptance budget; no implementation was changed.

Design/plan head `ba453c1b1e30347a051dcd7cf38da668677755bb` passes exact-head
[dashboard CI 39147920829](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/39147920829):
PHP 7.4/8.3 each **299 tests / 1430 assertions**, no skips, plus lint/structure/
build/manifest/audits. Companion plan head
`a73f558969af5c397862b03ae38cbb00fbab4fd7` passes exact-head
[uploader Quality 39147921360](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/39147921360):
PHP 7.4/8.3 each **1403 / 12481**, no skips, plus Node/build/audit. These are
unchanged-code regression results, not execution/proof of proposed native contracts.
This documentation-only receipt/Markdown-table follow-up is independently pushed
and its final exact-head CI verified at handoff; previous-head CI is not substituted.
