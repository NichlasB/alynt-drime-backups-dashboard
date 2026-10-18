# V2.3 Cache Convergence and Effective Scan-Stop Lifecycle Admission Integration Design

Approved **local-only, design-only**, 2026-10-18. R3 concurrency/permission design;
no runtime implementation, application/fixture/cache/SQL operation, grant,
activation, schedule apply/rollback, release, package or deployment is approved.
Owner: dashboard design; prospective runtime owner: companion uploader only.

## Baseline, scope and inherited limits

Explicit excluded review bases are dashboard
`3ab57b6fe74ca64eb185d813555a4ca28b036e2a` and uploader
`b253a60b94b9baa0fc0c38920c633b01b3907b79`, through this design/plan delta.
Read current sources rather than inferring integration from earlier proofs.
Uploader `docs/V2_3_NATIVE_PAIRED_COMPLETION_PROOF.md` owns the prior 335-check
owned pair/resolution result and its separate source-scoped review; those native
tests are **not rerun or enlarged** by this design. Synthetic genesis, COMMIT-ack
simulation, old-writer/restore, cache and effective-state limits remain intact.

Refines [raw/native admission](V2_3_RAW_OBSERVATION_NATIVE_ADMISSION_DESIGN.md),
[activation/cohabitation](V2_3_CADENCE_AUTHORITY_ACTIVATION_DESIGN.md) and
[trusted consent context](V2_3_TRUSTED_CONSENT_CONTEXT_DESIGN.md). The completed
schema-8 closed-only writer is not an active-mode grant. No existing schema,
purpose nonce, digest, version-1 evidence, public field or compatibility minimum
is changed or accepted differently here. Current production remains legacy.

## Preferred decision: raw authority, scoped stop, no cache-derived permission

Cache publication is compatibility/observability work after a confirmed commit,
not a grant, transaction receipt or synchronization barrier. Every future
participating scan-effect/settings writer must use fresh raw authority and a
shared serialized effect boundary. A cache hit, cache purge, two ordinary reads,
last-moment Boolean check, expired lease or missing cron event cannot establish
that boundary. Do not wire the existing private native owner directly to an
administrator handler or the scan dispatcher to bridge this gap.

Keep these facts separate; labels below are **design vocabulary**, not installed
status/API fields:

| Fact | Required evidence; what it does not prove |
|---|---|
| Stored closure/pair confirmed | Acknowledged owned transaction and exact receipt/readback. Does not prove publication, caller participation or stopped effects. |
| Cache publication attempted / unconfirmed | Fixed-scope post-commit invalidation observations. Does not prove another process discarded its arrays, nor undo a stored commit. |
| Participating admission closed | A durable closing generation ordered against every participating start/effect commit. No public true flag or current schema-8 row proves this today. |
| Draining / inspection required | Previously registered work or unknown outcome remains. No new affected effects admitted; elapsed time never clears it. |
| Scan effects quiescent at a verified barrier | Closed ingress plus authoritative accounted-for terminal work, no unresolved older effect transaction, and a proven participant set. Not cancellation of uploads, filesystem calls already running, backup producers or Drime effects. |

Preserve product scope: `auto_scan_enabled=false` in **legacy** mode disables
automatic scheduling, not existing separately authorized manual/CLI/remote scans.
It must not become an undocumented emergency stop. Under a future explicitly
consented client-owned mode, preparing/draining/disabled/uncertain authority
blocks new scan/enqueue admission across those entry points; active mode initially
permits only the due guarded scheduled path. Upload-next/abort remain subject to
their independent policy, not new permission from this design. Failed requeue
needs its own existing-queue policy; do not classify it as fresh scheduled discovery.

## Actual source and participation map

All paths in this table are relative to `alynt-drime-backups-uploader`. Current
behavior is source inspection, not a newly executed WordPress runtime test.

| Surface and exact source | Current behavior | Required integration boundary, not implemented |
|---|---|---|
| `includes/class-settings.php` (`get`, `update`, `is_persisted`, `site_uuid`) and `includes/trait-settings-site-identity.php` | Defaults merge with cached option data; update/UUID creation rewrite the whole option and seed the submitted array into cache. A get-based equality check is not independent raw confirmation. | No install/default/UUID helper in authoritative admission. Coordinate all whole-row writers, even unrelated setting changes; preserve exact target identity/bytes and verify durable outcomes independently of their cache. |
| `includes/class-schedule-safety-option-cas.php` and `includes/trait-option-storage.php` | Older safety/cron CAS invalidates selected keys after a successful swap. Shared array storage writes then seeds/verifies option cache. | Do not repurpose borrowed wpdb/CAS or cached array equality as native ownership/atomicity. Existing helper behavior is an integration risk, not fixed in this design. |
| `includes/class-native-closed-begin.php`, `includes/trait-native-paired-completion.php`, `includes/trait-native-receipt-resolution.php` | Dormant/test-loaded only; confirmed closed begin/pair and locking receipt observation; no publication or effect admission. | Keep original IDs/deadlines, no public write handles and no replay after uncertain COMMIT. Add neither production loading nor grants as a publication convenience. |
| `includes/class-cron.php` (`maybe_schedule`, `clear`, `scan`, `perform_scan`, `upload`, `apply_scan_cadence`) and `includes/class-plugin.php` | Production Cron receives no dispatcher. Init may register scan/upload events or clear both. The legacy scan body does not recheck the automatic-scan setting; upload is separate. Cadence apply edits only the scan event. | Guard actual callback entry, not just wake-up registration. Mode-aware scheduling must not fall back/recreate unsafe legacy work; explicitly preserve intended upload liveness and unrelated hooks. Never call `clear()` as proof of scan quiescence. |
| `includes/trait-plugin-admin-actions.php`, `includes/class-cli-command.php`, `includes/class-remote-action-worker.php` | Admin scan, CLI scan/run and accepted remote scan call the shared facade. CLI run can subsequently upload; remote scan can schedule upload. Acceptance/action lock is not current lifecycle authority. | Revalidate at execution, before scan side effects. A rejected scan must stop that combined CLI/remote workflow before its upload follow-up. Existing independent upload entry points remain distinct. |
| `includes/trait-plugin-scan-queue.php`, `includes/class-scanner.php`, `includes/class-wpvivid-producer.php`, `includes/trait-generic-outbox-producer-scan.php` | Facade scans before `Queue::add_many`; producers read files and persist stability snapshots **during** discovery. Discovery is not a pure no-effect preflight. | Register owned scan work before producer invocation. Account for snapshot persistence and already-running reads as well as final queue insertion; no callback/filesystem traversal while holding native SQL locks. |
| `includes/class-queue.php`, `includes/trait-plugin-failed-upload-actions.php` | Public add/add_many/prepend and admin failed requeue can mutate the queue independently. Ordinary read/modify/write plus package dedup is not closure fencing. | All participating queue additions/requeues need the correct policy and same effect-ordering protocol; guard the sink, not only facade callers. Preserve existing upload removals/progress semantics separately. |
| `includes/trait-dashboard-connection-state-transitions.php`, `includes/trait-dashboard-connection-enrollment.php`, `includes/class-dashboard-connection.php` | Revoke, remote opt-in/out, enrollment and ordinary authenticated-read timestamp updates copy/rewrite connection state and seed cache. | Semantic changes close admission before transition. Even timestamp-only writes require exact fresh row/CAS discipline against resurrection; semantic timestamp exclusion is not permission to overwrite stale flags. Do not change credentials/pairing here. |
| `includes/class-cadence-authority-entry-point-policy.php`, `includes/class-cadence-authority-scan-dispatcher.php`, `includes/class-cadence-authority-lifecycle-store.php` | Synthetic classifier, explicitly injected dispatcher and disabled lifecycle models are not production permission. A dispatch's own started work is different from pre-admission pending rejection. | Closed construction must distinguish pre-admission from owned-effect revalidation. No posted entry-point/owner/Boolean or synthetic grant becomes authority. Preserve all false support flags. |
| `includes/class-uploader.php`, `includes/trait-uploader-active-upload.php`, `includes/trait-uploader-retry-state.php`, `includes/trait-uploader-local-server-retention.php` | Upload lease, active multipart recovery, retries, registry/queue updates and optional retention are independent effects. Upload-next may invoke retention even for an empty queue. | Do not describe continued uploads as side-effect-free or cancel them via scan closure. No lease takeover, multipart abort, retry reset, cleanup or Drime change is authorized here. |
| `includes/class-activator.php`, `includes/class-deactivator.php`, `uninstall.php` | Activation may install defaults; deactivation clears hooks without killing running PHP; uninstall deletes a fixed list and omits the safety option. | No auto-consent on activation/missing state. Preserve uncertain records; no downgrade/reinstall/restore/uninstall safety inference or new deletion. Shipping active mode needs a separate lifecycle runbook/proof. |

This inventory covers discoverable first-party paths at the stated baseline, not
arbitrary third-party PHP, old deployed processes, database administrators or
external backup creators. Server runner/WPvivid backup creation is outside uploader
scan-stop scope; their production schedules are not owned by this protocol.

## Post-commit cache-publication protocol

The options API can return filter-supplied, defaulted, autoloaded or cached values,
not necessarily an independent database observation. Its cache paths include
alloptions/notoptions/per-option data. See the primary
[get_option source](https://developer.wordpress.org/reference/functions/get_option/).
Likewise, [update_option](https://developer.wordpress.org/reference/functions/update_option/)
does not provide an exact two-row native CAS or distinguish every no-change/error
case by its Boolean alone. The repository helpers' cache seeding makes raw
durable verification essential for this integration. These are design inferences,
not a claim that all existing settings saves fail.

Future fixed publication must:

1. Preserve original pinned blog/table and private command/commit classification.
   Release native locks and finish the owned transaction before any WordPress
   cache/hook/provider call. Never keep a connection open under callbacks as a
   publication lock or emit receipts/raw settings in debug output.
2. For a confirmed safety-only begin, invalidate its fixed safety option key plus
   alloptions/notoptions; for a confirmed pair, invalidate **both** fixed safety
   and settings keys plus alloptions/notoptions, in the original blog's options
   group. Do not flush all cache/groups, seed the old/proposed settings array,
   switch blogs to guess scope, or synthesize `updated_option` hooks. Other
   writers' cache/hook behavior requires its own compatibility review.
3. Attempt required invalidation for a confirmed commit even when renewed user
   authorization withholds an outward success. Cache removal grants no new user
   permission. If the original scope cannot safely be retained, do not act in
   another scope; leave publication unconfirmed and block any new authority claim.
4. Handle true/false/throw without assuming false proves either an outage or
   success. Built-in cache delete returns false when a key is absent, per
   [WP_Object_Cache::delete](https://developer.wordpress.org/reference/classes/wp_object_cache/delete/).
   Inspect only fixed-key observations inside the supported profile; no arbitrary
   callback may assert cross-process convergence. Re-read raw state if needed,
   not a copied cache array as a durable certificate.
5. If COMMIT was unacknowledged, preserve unresolved classification and original
   IDs. Any later fixed-key eviction is hygiene only, never no-effect evidence;
   separately authorized locked receipt resolution must precede claims about the
   historical operation. No re-execution/compensation because cache looks old.
6. Keep publication outcome separate from historical commit and effective stop.
   If a process dies after COMMIT but before eviction, cooperating effect writers
   still consult uncached authoritative fencing. A delete cannot revoke arrays
   already held by another worker or prevent stale repopulation.

Initial candidate remains built-in, nonpersistent, single-site cache with no
DB/object-cache drop-ins; preserve strict false/null compatibility from the prior
guard. External/distributed caches are unsupported until separately proved.
No durable cache-publication journal, new field, retry job or periodic reconciliation
is installed by this design; adding one requires explicit storage/scheduling scope.
Bound any future per-request eviction attempts; no retry loop waiting for all
processes to agree, no refresh of original mutation deadlines.

## Shared admission/effect ordering, not check-then-act

Choose a future **durable registration plus serialized effect-commit** protocol.
Do not hold row locks across a whole scan, arbitrary callback, file traversal,
authorization, WordPress option/filter API, network or Drime request. Do not rely
on a second read immediately before an uncoordinated `update_option`: closure
can win between that read and the write.

Conceptual ordering (not new methods/fields or executable authority):

1. Trusted construction selects the actual entry point. Authenticate the local
   command separately; background work relies on a separately proven durable
   grant, never an administrator cookie cached from an earlier request. Current
   closed-only schemas cannot supply such a grant: actual admission through the
   new authority lane remains unavailable, not existing legacy behavior. Never
   reinterpret `authenticated_grant=null` as implicit consent.
2. Before producer/snapshot work, atomically register one owned operation against
   supported current mode, permission generation, enrollment and immutable intent.
   Refuse unknown/pending foreign work, but recognize this operation's own claim
   during owned revalidation. Lease expiry does not erase started uncertainty.
3. Prepare bounded snapshot/queue proposals outside SQL locks without persisting
   them. Existing producers already persist snapshots: splitting preparation from
   persistence is a required future integration, not something already achieved
   by fencing only `queue_scan_candidates()`.
4. Serialize each affected persistence boundary against closure using the same
   raw safety generation and exact affected-row snapshot. Preferred physical
   direction: one exclusively owned native transaction locks safety first, then
   fixed affected rows in one globally defined order; revalidates exact prepared
   bytes/IDs/autoload and owned registration; writes state/effect/receipt together.
   Reuse safety-then-settings order whenever settings participates. Additional
   snapshot/queue rows, codecs, limits and combined lock order require explicit
   reviewed versioned contracts and owned proof; the current two-row backend is
   **not** approved to accept more option names or arbitrary SQL.
5. If an effect transaction wins before closure, its outcome belongs to registered
   draining work. If closure wins first, the old-generation effect commit fails
   with no affected persistence. Finish acknowledged transactions before outside
   callbacks/publication. Unknown COMMIT retains registration and blocks replay;
   duplicate signatures or an empty queue are not exactly-once proof.
6. Retire owned work only with confirmed matching terminal evidence. Closure
   prevents fresh registrations and stale-generation effects, but cannot instantly
   cancel code/reads already in flight. Claim quiescence only after accounting for
   all participating older work and a resolution barrier; crashes remain draining.

This is a future physical design requirement, not approval for queue/snapshot
schema changes, a writable provider, lock table, named global mutex or generic
transaction callback. It must also coexist with upload queue removals/progress;
do not replace the whole queue with an older prepared array and resurrect items
already uploaded. All affected readers/writers must participate before activation.

## Coordinated settings/connection writers and lifecycle

Every settings writer must reject a stale complete row even if its own requested
field is unrelated to scanning. The normal sanitizer currently rebuilds settings
from defaults; a future coordinated edit must explicitly preserve or intentionally
validate unknown fields rather than silently normalize the native one-token patch
into a whole-row rewrite. No sanitizer or writer is changed now.

Meaningful permission, scan setting, enrollment/key/origin or mode changes need
closed-first, generation-bound transition handling. Failed closure cannot be
reported as disabled. A subsequent write failure leaves a recorded closed/draining
transition, not automatic restore of the old grant. A newly enabled Boolean cannot
clear pending work or restore authority. Off/on/away-and-back requires monotonic
generation invalidation; a matching hash alone does not rule out ABA.

Ordinary polling timestamps remain excluded from semantic enrollment identity.
However, `record_authenticated_read()` and error bookkeeping currently rewrite a
copied connection option: their **physical** writes must be serialized/CAS-checked
or restricted to fresh nonsemantic fields so they cannot resurrect remote opt-in
after revocation. Do not couple a normal poll to fresh consent or change binding
version 1 just to solve a stale-copy race.

Preparing/draining/disabled authority cannot fall back to legacy. Never infer
never-activated or verified handoff from missing state, absent pending records,
an empty queue, a stopped cron event or valid synthetic closed provenance. A fresh
explicit handoff needs complete effect accounting, new generation and verified
legacy wake-up, not an automatic cleanup path. Unknown outcome resolution observes
and retains evidence; it does not grant retry, abandon or activation.

Old binaries, previously dispatched legacy callbacks and direct nonparticipants
cannot be fenced by a dormant new reader. Before actual activation, require a
separate maintenance/drain deployment plan establishing a coherent participant
set. Preserve uncertainty through deactivation; reinstall/default creation,
downgrade, backup restore and destructive uninstall require explicit lifecycle
approval/proof. No restore is considered safe merely because a receipt is valid.

## Required discriminating proof before runtime integration

| Scenario / test layer | Required behavior and counterfactual discriminator |
|---|---|
| Pure protocol model | Positive candidate ordering for effect-before-close, close-before-effect, exact own versus foreign work; all outputs non-authorizing. An always-unavailable implementation must fail positive model controls. Never fabricate real grants to get green. |
| Cache model then genuine core | Stale individual/alloptions/notoptions entries, missing keys/false delete, thrown provider, post-commit revocation, scope swap, crash before eviction, stale repopulation. Removing raw authority or calling cache hooks under locks must fail. Current-process eviction is not cross-process proof. |
| Writer race | Two settings edits; stale UUID/default save; poll-timestamp/error copy racing revoke/re-pair; rollback/zero affected rows. Omitting exact raw CAS or generation check must allow a demonstrated resurrection and fail the test. |
| Producer/queue sink | Closure before discovery, after registration, during file read, before snapshot persist and before queue persist; direct add/prepend/requeue and upload removals. Removing the sink fence must fail even when facade tests pass. No scan callback under native locks. |
| Entry-point integration | Real scheduled callback including already-dispatched wake-up, admin/CLI scan/run and accepted remote execution; rejected combined flow must not upload/schedule upload. Legacy manual semantics and independent upload policy remain unchanged. |
| Physical owned atomicity | Fresh separately confirmed disposable rows/users scope; competing native processes/lock barriers, first-write failure, stale prepared queue, unknown commit then locked resolution, terminal versus draining work. In-memory tests cannot certify this. |
| Lifecycle/release | No grant/activation on installation/status/default reads; nonparticipant/old binary/restore/DDL/cache profile refusal; explicit maintenance/drain/handoff, retained uncertainty and capacity exhaustion. No timeout-based assumption of quiescence or unsafe pruning. |

Retain current request/parser/storage caps and profile refusals. Any new queue/
snapshot bounds, receipt capacity and truncation policy must be explicit before
physical admission; refuse unsupported/oversized state, never truncate private
evidence or expose paths/package identifiers/raw options/owners/credentials.
No new public status contract or UI is finalized by the design vocabulary here.

## Focused design review and implementation-plan progress

Source-scoped architecture/authorization/error/concurrency/resource review resolves
these design gaps: cached equality versus durable confirmation; whole-option stale
resurrection (including timestamp writers); producer snapshot effects before queue;
check-then-act versus a serialized effect sink; stopped wake-ups versus in-flight
work; historical receipts versus quiescence; and independent upload/retention effects.
No confirmed runtime defect is fixed or new runtime proof claimed in this slice.

Only this design, the raw/native design cross-reference and both implementation
plans change. Versions, source, tests, assets, schemas and production constructors
remain unchanged. No site, SQL, fixture, cache operation or Novamira operation is
needed/performed. Existing tests check regression only, not the proposed protocol.
This is not a new canonical Feature/DS3 or Database Review; historical toolkit
whole-plugin rows stay unchanged. Full pre-release remains required for a future
coherent release candidate, before packaging/publishing.

Implementation-plan progress: the next cache/effective-stop integration decision
is mapped to actual participating writers/effect boundaries; active runtime remains
gated. Do not substitute maintenance/package churn for that permission decision.

Recommended next gated slice: **local-only V2.3 Scan-Effect Fencing and
Post-Commit Cache-Publication Contract Safety Foundation**. Implement only a
dormant, test-loaded bounded in-memory protocol/observation model, with explicit
non-authorizing positive/negative interleavings and guard-removal controls.
Keep actual readers/cache APIs/storage writes/fixtures/hooks/production wiring,
grants, activation and schedule rollback disabled. It must not mint a transferable
admission token or caller-selected write provider. Native multi-row sink and real
core/cache/worker proof are subsequent separately scoped approvals, not bundled.

## Validation and publication

### Subsequent approved contract foundation — 2026-10-18

The next in-memory milestone is now implemented in uploader; owning record:
`docs/V2_3_SCAN_EFFECT_CACHE_CONTRACT_FOUNDATION.md`. Two dormant test-loaded
models cover bounded synthetic scan-effect/closure ordering and separate fixed-scope
post-commit cache observations. They do not alter or prove the native/storage/cache/
worker requirements above. Production loading, schemas, readers, actual cache/SQL/
fixture operations, grants, activation and rollback remain unchanged/disabled.
The preferred next gate is design-only **V2.3 Native Scan-Effect Storage and
Participant Integration Design**, specifying physical row/codec/lock/capacity and
full writer participation contracts before additional runtime approval. Historical
validation below remains evidence of this earlier design, not the new models.

Executed repository-only validation on 2026-10-18:

| Command/check | Result and limits |
|---|---|
| Uploader `php vendor/bin/phpunit --do-not-cache-result --filter 'NativePairedBoundary\|NativeClosedBeginBoundary\|Cadence\|GuardedScan\|CronGuarded\|SettingsTest\|QueueTest\|SerializedSettings'` | **475 tests / 1963 assertions**, no skips; 58.730s. Existing isolated contracts only, not new effect/cache/WordPress proof. |
| Dashboard `php vendor/bin/phpunit --do-not-cache-result` | **299 / 1423**, two existing unrelated Windows skips; no no-skip local claim. |
| Dashboard `npm run lint`, `npm run check:structure`, `npm run build` | PHPCS **138 files**, structure and build pass. No generated differences. |
| Dashboard `php composer.phar validate --strict`, `php composer.phar audit`, `npm audit --audit-level=moderate` | Valid manifest, no advisories/vulnerabilities. No dependency changes. |
| Source/reference validation | **31** distinct quoted uploader PHP paths and **three** relative design links resolve; direct scan/queue/settings/connection call searches inspected. |
| Diff/ownership review | Only this design, dashboard implementation plan/raw-native cross-reference and uploader implementation plan. Source/tests/bootstrap/assets/version/schema untouched; `git diff --check` passes. |
| Safety checkpoint | Read-only uploader restore-point Option A/check: clean master at the stated base, existing external archive and Git checkpoint eligible. No archive, branch, backup or restore created. |

No new executable tests are appropriate for an unimplemented design. Existing
native proof results are historical context only. No WordPress bootstrap, cache
API, SQL, fixtures, LocalWP or live access occurred; therefore the Novamira site
operation path was not invoked. This approval does not renew prior fixture scope
for future effect storage operations. Exact pushed-head CI must pass before the
publication handoff; earlier-head results do not substitute for it.

Design/plan publication: dashboard master
`467e30881f6e88451f8eb1011b5f3c6a6a2810b6` passes exact-head
[CI 39145076026](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/39145076026),
PHP 7.4 and 8.3 each **299 tests / 1430 assertions**, no skips, plus coding
standards/structure/build/manifest/audits. Uploader companion-plan master
`7679bfbfaed2c7e5c947cf42cfe8c0670d6fdbc3` passes exact-head
[Quality 39145083300](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/39145083300),
both PHP versions and Node/build/audit; full-suite totals are verified from its
completed log at handoff. This is regression coverage of unchanged code, not
proof of the proposed protocol. The final documentation-only dashboard head is
independently checked after pushing this receipt and the legacy-lane wording
clarification; no earlier-head CI substitutes for that final check.
