# V2.3 Same-Root Fixed Native Boundary Integration Design

Successor owned no-fault evidence: uploader
docs/V2_3_SAME_ROOT_NATIVE_ADAPTER_OWNED_PROOF.md (2026-10-21), approved exact
Plugin Tester target/fixtures. New fixed proof composition, genuine guards and
one real fixture-ledger CAS/barrier/cleanup;2214 checks/15 groups,77 application
checksums unchanged. The final unavailable entry/production wiring stay closed.
Real uncertain-COMMIT and physical-failure proof remain separate approval gates.

Approved isolated implementation successor: uploader
docs/V2_3_SAME_ROOT_NATIVE_BOUNDARY_ADAPTER_FOUNDATION.md. Fixed native primitive
composition and phase/guard/release integration now implemented behind a literal
unavailable entry, with extension-free API tests only; source-pinned predecessors
and production wiring unchanged. Next separate gate is the Plugin Tester local-
only Same-Root Native Adapter Genuine Guard and No-Fault Owned Cleanup Proof,
with exact target and disposable users/storage/one-ledger-CAS/cleanup approval.
No actual native or WordPress proof is supplied by the API tests. The approved
design and its historical verification below remain unchanged.

2026-10-21. Approved repository-only/design-only. Dashboard owns this record;
uploader0.5.25 is inspected read-only, dashboard0.1.77 unchanged. R1 document
change describing a future R3 authentication/storage integration, not approval
to implement or execute it. No WordPress bootstrap, users, SQL/storage writes,
native sessions/experiments, fixtures, listeners, suppression or site operations.
Production wiring, grants, activation and rollback remain disabled. No package,
version bump, release, tag or deployment.

## Decision, ownership and review boundary

**Keep production wiring NO-GO.** Reuse the fixed native primitives under one
new dormant same-root composition, with separate capture/admission/barrier
resource lifetimes. Do not fill six stubs and assume the existing candidate-only
root has become a native integration. Two joins need explicit implementation:
fresh mutation checks after writer setup and before BEGIN; acknowledged barrier
close before DATA parsing and fresh post-close read checks.

Parent: [production-wiring readiness](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md).
Uploader owner docs/V2_3_SAME_ROOT_LIFECYCLE_INTEGRATION_FOUNDATION.md records the
completed isolated coordinator, not native proof. The original lifecycle design
is [same-request lifecycle](V2_3_REGISTRATION_ADMISSION_OBSERVATION_LIFECYCLE_DESIGN.md).

Starting clean/synced heads: uploader b87a0a6deca21f647ed1f75e6b093cdb68299114,
dashboard c15270942b0e21be021f06941bc5c48607328e96; toolkit
518863cd9f84afbca5a6ff72c9073350f809538b. Explicit excluded coherent review bases:
uploader3cd9ed9786e578a666ed8814ba724092e99079c9 through b87a0a6; dashboard
9388869c1bea20d7d4cdd974fdd80172c45244f3 through this design. Older native
helpers below are inspected direct dependencies, not a whole-history audit.
Three dashboard documents only; no uploader implementation, test or proof pin
is changed or adopted as proof of a new composition.

OptionB source-only restore point, no local checkpoint branch:
Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/
alynt-drime-backups-dashboard-20261021-170909.zip and matching .txt;
13253 files; SHA256
EC54181AE3B15B4ABE054082615BC80826635F64EC90B31FFAF743BB1E8F6D38.
Not a site/database backup. No site target, credentials, LocalWP service discovery
or Novamira operation is applicable to this repository-only task.

## Six logical boundaries: actual source and selected integration

Uploader paths are source references, not installed public APIs. Names below are
the current unavailable methods in includes/class-registration-lifecycle-integration.php.
Their signatures are not a sufficient native contract and are not frozen here.

| Boundary | Existing primitive / required fixed ownership |
|---|---|
| lifecycle_profile | includes/trait-native-observer-session.php profile/native_idle. Check only supported configuration and borrowed authentication-session metadata; pin the original core/handle/facts. No owned connection, protected option projection, repair or reconnect before authorization. |
| lifecycle_physical | Same trait auth_same. Recheck original wpdb object, exact dbh identity and physical facts across both phases; never re-pin after reconnect, switch-blog, callback or release. Borrowed handle is never configured, closed or exported. |
| lifecycle_capture | includes/trait-registration-admission-transaction.php admission_capture with includes/trait-registration-admission-storage.php admission_row and includes/trait-native-options-metadata.php metadata. Open one owned read-only session only after original small mutation authorization; at most20 fixed projections/two equal sweeps. Detach/close successfully before returning its private frame to construction callbacks. Equal sweeps are not an atomic snapshot. |
| lifecycle_attempt | Same transaction/storage helpers plus includes/trait-native-closed-begin-session.php and includes/trait-registration-native-outcome-session.php. Own one fresh writer, fixed metadata/policy, original candidate/pin and raw outcome capture. Separate idle setup from final root-owned guards, then once-only locked transaction; at most40 admission projections total and one existing-ledger UPDATE. No nested admission owner or imported historical outcome. |
| lifecycle_release | Registration outcome-session finish semantics plus includes/trait-registration-native-outcome-capture.php and includes/trait-registration-admission-qualification.php. Seal actual sent/ack/detach/close facts once; require confirmed close and empty owned handle/physical slots before qualification. A later finally call is a non-IO terminal check, not another close, outcome capture, rollback or factory invocation. |
| lifecycle_read | Fixed native primitives from includes/trait-registration-observation-native-barrier.php and includes/trait-registration-observation-barrier-rows.php. One fresh owned locking session, original request's freshly built read envelope, exact metadata/zero-DML/acknowledged close before frame delivery. Do not call standalone observe_native or seed its capture/origin latches. No caller certificate can make the frame native-qualified. |

Use explicit private trait composition/aliases, not provider interfaces, enabled
switches, arbitrary SQL callbacks, caller-selected endpoints, injected handles or
reflection as a runtime bridge. Preserve the existing candidate-only final class,
original native admission/observer owners, worker/relay and their historical source
identities. A new dormant native composition owns the new integration and proof
manifest. Shared primitives may be reused unchanged; if a primitive truly needs
change, its old proof is not silently refreshed by the new isolated tests.

Resolve method/property conflicts explicitly: DATA finish is not native cleanup;
admission outcome cleanup is not barrier cleanup; reader/physical/phase slots and
bounded counters have one current phase owner. Do not combine traits by accident
or carry a writer outcome session into the observer. The current placeholder
slots do not prove real handles are releasable.

## Fixed same-root order and narrow native profile

1. Consume root once, install only owned role/query fences, pin original POST,
   core, actor/cookie and original monotonic/UTC clock. Profile metadata is allowed
   before the first intent guard; protected data reads and owned factories are not.
2. Fresh small mutation guard and original-request/physical continuity precede
   owned capture. Close capture before construction/nonce callbacks. Reuse actual
   candidate construction, complete historical plus ten reserved identities and
   own minimal origin retention before any possible ledger DML.
3. Prepare one owned idle writer and metadata outside locks. Retain the original
   full mutation nonce/action only in the root's private stack until the final
   checks. Repeat small AND full mutation authorization, same original actor,
   cookie/core/physical identity, deadline and exact full origin/proposal pin
   after setup and immediately before BEGIN. Never reconstruct a full form from
   a returned result, reset the root or accept a caller guard boolean.
4. Native-only critical section: fixed safety-first ten-role comparison, exact
   metadata after safety lock, single ledger CAS, ten-role readback, COMMIT. Mark
   send/reservation before IO; preserve raw true/false/throw and affected-row facts.
   No WordPress/authentication/nonce/parser/extension callback under row locks.
5. Release admission ownership, then use the unchanged sealed qualifier. Only
   committed/uncertain historical outcome plus positive client release can reach
   fresh diagnostic admission. Unperformed current guards remain null, not true.
   Erase full frames/proposal/replacement/context, stack full pin/forms/nonces,
   raw outcome/seal before read-auth callbacks. Retain only own bounded minimal
   origin/complete identity set and inert private cleanup-completion state.
6. Freshly authorize original mutation intent, then independent small/full read
   nonce domains and different lookup identity. Reuse actual stage/claim/finish
   DATA validators. No callbacks may replace original actor, installation,
   physical facts, minimal origin or identities. At most one diagnostic attempt.
7. After claim, prepare a NEW idle barrier session with its own phase/counters.
   Repeat both read guards/physical checks after setup/metadata and immediately
   before BEGIN. Transfer only the own constructed typed read envelope internally
   to the fixed native barrier; the current lifecycle_read(core) alone omits the
   envelope/deadline required by barrier_capture. No new public parameter exists.
8. Complete native barrier and confirmed client close, then parse its private
   frame through consumed DATA finish, renew both read domains/original continuity
   after close and check original budgets. Only this same source-owned native
   path may qualify the existing result; any cleanup/reentry failure overrides it.
9. Finally settles each owned lifetime at most once, erases primitive DATA/frames/
   session slots, restores exact owned filters and prior handler. No re-open,
   resend, read retry or after-return continuation, including exceptional exits.

Native compatibility is deliberately narrow, not the plugin minimum:64-bit PHP,
mysqli/mysqlnd, no custom client flags, utf8mb4/empty DB_COLLATE, exact loopback
127.0.0.1:port, bounded ASCII DB name and MariaDB10.6.x. Borrowed session must be
idle/autocommit, same physical identity/server/database, non-read-only server.
Owned sessions are distinct nonpersistent connections, local infile disabled,
connect3s/read5s. Writer/barrier policy remains READ COMMITTED, lock waits2s/3s,
no active role, flush-at-commit1. Exact normal InnoDB options schema/indexes,
metadata visibility and no trigger/inbound/outbound FK requirements remain.
Do not grant privileges or broaden unsupported profiles to make a proof pass.

Admission10s and read10s inside ORIGINAL20s, nonregressing UTC and intent expiry
remain acceptance bounds. Native blocking calls may overrun; this is not hard
cancellation. No timeout, row cap, parser bound, event cap or budget increase.
The ten compiled participants remain owned by includes/class-scan-effect-manifest.php;
no table/name/value is selected by a request. Missing/duplicate/malformed/oversized/
autoloaded participants refuse; no insert, schema setup or cache-based authority.

## Release, observation qualification and uncertainty

Keep three cleanup receipts PRIVATE and phase-specific (capture, admission,
barrier); these are inert owner state, not transferable DATA or durable records.
Consume/detach each owned handle before its close attempt. Record confirmation
only from its actual raw result; false/throw/unknown is permanently unavailable.
Final cleanup may inspect terminal receipts/empty slots without IO. Admission
outcome erasure must not cause final release to start or invalidate a different
barrier lifetime; never reuse cleared capture state as permission to clean again.
Known transaction ownership can roll back once using existing policy. Unknown
BEGIN/COMMIT/rollback poisons the session: detach/close, never retry/repair/query
for a favorable history. Every statement/result is also bounded and closed.

Selected barrier is a zero-DML **READ WRITE locking transaction**, not SQL
TRANSACTION READ ONLY. Require ten safety-first projections, two exact metadata
checks, two native zero-DML inspections of all15 fixed counters, acknowledged
COMMIT, matching idle session and confirmed close before accepting any frame.
The shared close_barrier implementation returns void/phase state; adapter must
derive its own strict release confirmation from actual cleanup, never assume
void or a detached null slot means close succeeded. No parsing/auth callbacks
until that release. Counter/resource failure or lock timeout returns unavailable.

Only after that native pipeline and fresh post-close read checks may existing
candidate result become barrier_verified=true/candidate_only=false, with candidate
absence renamed not_observed_at_barrier as in the standalone native observer.
Synthetic candidate results remain candidate-only. No supplied frame, boolean,
classification or previous owner's phase can trigger this promotion. All other
authority/closure/retry/storage/dispatch/execution flags stay false; historical
closed receipt is not permission. Client close does not certify server quiescence;
barrier absence is not cancellation of a paused/nonparticipating writer or safe
retry. No status, cron, remote, AJAX, REST, admin or CLI production entry is added.

## Non-vacuous acceptance and subsequent gates

These are future obligations, NOT proof performed by this design's checks.

| Obligation | Required layer / observable failure control |
|---|---|
| Fixed new composition actually joins six boundaries | Isolated positive committed AND uncertain paths using unchanged orchestration/validators/qualifier; doubles only at owned native API edges. Always-unavailable owner must fail the positive assertion. Distinguish actual final owner from test composition. |
| Authorization/setup/lock ordering | Exact four nonce domains and explicit phase counters; deny each guard individually. Remove post-setup mutation/read check, pre-capture guard or post-close check: assertion-killed controls, not merely expected-false tests. No protected read/UUID before authorization. |
| No native qualification from candidate or poisoned cleanup | Remove close/ack/metadata/zero-DML gate and assert the positive/control fails. Unknown release, duplicate IO, cross-phase receipt/slot reuse, source/identity mismatch, reentry and expiry consume/refuse. No write-capable handle/export. |
| Actual WordPress request and no-fault native join | Separately approved exact Plugin Tester target and owned users/fixtures; real guards/nonce domains, one ledger CAS, other9 fixed rows unchanged,50 total protected projections max (40 admission+10 barrier), actual independent handles/cleanup. Baseline existing application state and verify preservation/owned removal. |
| Real uncertainty joined to fresh observation | Later separately approved selected-loss experiment, after fresh genuine guard/no-fault transparency and cleanup prerequisites on the NEW source-pinned composition. One bounded attempt, no wider limits; real false/unknown outcome plus actual barrier/timeout behavior, not synthetic labels. |
| Physical failure/contention/cleanup | Approved owned native proof: partial setup/BEGIN/DML/COMMIT/close failures, lock contention and bounded settlement. Isolated doubles/virtual clocks cannot prove transaction, server cancellation, crash behavior or actual storage preservation. |

Do not run legacy owned proofs as though they validate new source. Record exact
new class/traits/primitives/worker identity before native proof, retain failures,
and verify cleanup before publication. Existing source guard-control evidence
remains historical. No Database Review or physical atomicity claim from API tests.
Readiness gates R2-R7 in the parent (entry, provisioning/cache, supported target,
lifecycle, grants/effects, rollback) remain open after an R1 native proof.

**Recommended next gated slice: local-only V2.3 Same-Root Fixed Native Boundary
Adapter Safety Foundation**, dormant implementation with isolated API tests only.
Implement the selected phase ownership, post-setup guard joins and native release/
result qualification using fixed primitives, not another DATA classifier; keep
actual authentication/storage/native execution and production wiring disabled.
Explicit approval is needed for that new native/authentication composition.
Its subsequent genuine/physical proof requires separate exact disposable target
and fixture approval. No generic maintenance or packaging detour is recommended.

## Source-scoped review and verification

Canonical Feature Light and final Feature Security reviews cover this design,
plan milestone and parent successor pointer plus the direct sources above. No
whole-plugin or historical release-readiness row is refreshed. Bloat/Structure
and UI/UX are unnecessary: zero changed PHP/JS/CSS or UI. Formal DS3/native/
Database reviews and FULL_PRE_RELEASE are not claimed; full suite is reserved
for the next coherent RC before packaging/publishing.

Canonical FEATURE_LIGHT_REVIEW_PROMPT.md outcome:
Feature scope: same-root native boundary integration design. Files reviewed:3
changed dashboard documents plus the named direct uploader dependencies. Risk
areas touched:Other/design. Issues found0; auto-fixed0; manual decisions0 within
the approved design scope. Future implementation/proof approvals are gates, not
unresolved defects silently marked fixed.

| Light category | Source-scoped outcome |
|---|---|
| 1 Scope/risk | Documentation-only; native/authentication semantics specified, not installed. No source, public surface or compatibility floor changes. |
| 2 Maintainability | Reuse fixed primitives; explicit trait conflicts/phase ownership, no nested consuming roots, provider framework or duplicate classifier. |
| 3 Error/recovery | Strict raw acknowledgements/cleanup, poisoned unknown commands, no resend/repair; client close distinct from server quiescence. |
| 4 UX/accessibility | N/A: no UI, output/form/notice or runtime string change. No UI/UX workflow. |
| 5 WordPress | Fresh independent nonce/capability/session domains and fences; no callbacks under locks; borrowed connection untouched. No hook/API changes. |
| 6 Edge/integrity | Unsupported profile, stale/malformed intent, identity swaps, reentry, cleanup failures and cross-phase receipt reuse refuse; proof limits explicit. |
| 7 Performance/data | Existing timeouts, deadlines, row/byte/event caps preserved; at most50 protected projections and one ledger CAS in future root, no setup/migration/cache authority. |
| 8 Documentation | New owner, plan milestone and parent pointer agree; actual closed stubs distinguished from proposed native behavior. No README/changelog/version behavior change. |
| 9 Escalation | No changed executable code, so no bloat split, UI, formal DS3 Database/physical proof or release-readiness claim. Full pre-release at coherent RC remains required. |

Executed preservation validation on2026-10-21:

- Dashboard php ./vendor/bin/phpunit --do-not-cache-result:299 tests/1423
  assertions, two existing Windows Sodium skips, no failures/errors.
- Dashboard php ./vendor/bin/phpcs:138 files passed; npm run check:structure
  and npm run build passed. No generated assets or runtime file changed.
- Read-only uploader php ./vendor/bin/phpunit --do-not-cache-result --filter
  'SameRootLifecycle|RegistrationNativeQualification|RegistrationNativeAdmissionBoundary|RegistrationObservationNativeBarrierBoundary|RegistrationAdmissionJoin':
  125 tests/8528 assertions, zero skips/failures. Existing isolated API/DATA/
  source-contract checks only; no real authentication, native IO or transaction.
- All13 explicitly named uploader source references and27 local Markdown links
  across the three documents resolve. Final whitespace/scope/Git checks are
  publication gates; uploader must remain unchanged at its starting head.

Final canonical FEATURE_SECURITY_REVIEW_PROMPT.md after local checks and source/
diff inspection: input/output/SQL/file-operation code unchanged; no new input,
route, SQL port, serializer, secret or handle export. Future guards, separate nonce
domains, private provenance, phase release and fail-closed result promotion are
specified, not claimed operational. No confirmed scoped security defect found;
no security regression handoff required for this docs-only change. Future
guard-removal/native controls are requirements, not tests executed by this task.

Exact-head dashboard CI and private toolkit publication evidence are recorded in
the source-scoped toolkit checklist closeout. No new uploader commit or CI run
is needed for its read-only source inspection. None of these preservation checks
authorizes native execution or establishes a native join.
