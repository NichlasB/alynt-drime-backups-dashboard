# V2.3 Fixed Genuine Setup Observation Admission and Source-Ownership Closure Design

2026-10-25. Approved repository-only DESIGN: R0 analysis/R1 Markdown publication.
Excluded coherent bases dashboardda1b9c3cb30eae72575beb62314dade7f05391f7,
uploadera2012306a8ce0dad565571cd2a6170ec21e294dc,
toolkit9d5b376aa5fc6fab3455fd449673fb8299722c3d. Companion owns
`docs/V2_3_WPDB_SETUP_WIRE_FOUNDATION.md` and its unchanged synthetic helpers.
Parents: [genuine setup rebaseline](V2_3_GENUINE_WPDB_SESSION_SETUP_REBASELINE_DESIGN.md),
[A1-A4 source audit](V2_3_PINNED_BOOTSTRAP_REACHABILITY_LIFETIME_COST_AUDIT.md) and
[closure decision](V2_3_BOOTSTRAP_RESIDUAL_CLOSURE_DECISION.md).

## Decision and limits

Retain native NO-GO. Select a **separately owned, sealed disposable execution
image with a lifetime-bound source lease** as the candidate to investigate, not
an available implementation or proven Windows isolation mechanism. Do not run
genuine bootstrap/session observation until that mechanism and the remaining
source/profile/resource obligations qualify under separate approved proofs.
No source image, account, permission, lease, observer, endpoint or receipt is
created by this design. Public/member hashes and synthetic outcomes are evidence
inputs, not the authority that can open an admission gate.

No actual authentication, SQL/storage, WordPress/LocalWP/native execution, fixture
creation/cleanup, filesystem permission/credential changes, services/listeners,
production wiring, grants, activation/rollback, package/release/tag/deploy.
Versions stay dashboard0.1.77/uploader0.5.25, PHP7.4/WP6.0 unchanged. The dated
Plugin Tester audit is context only; no fresh site inspection or Novamira call.

## Keep evidence domains separate

|Domain|Required evidence before actual admission|Not sufficient|
|---|---|---|
|Source closure|Finite exact graph plus predicates for include/autoload/hooks/configuration, effects and all reachable suffixes|One wpdb hash, public archive or version marker|
|Source ownership|Effective protection of every loadable object/configuration and loader path for the whole owned lifetime|Before/after checksums, a writable copy or a caller's lease label|
|Session/profile|Fresh nonpersistent owned pair, real driver/build/options and negotiated grammar, independent limited identity/grants evidence|Thread ID, username, guessed flags or synthetic profile|
|Setup/results|Bound request/result lineage, source-qualified charset multiplicity, typed mode/filter branch, later exact schema result|A bare OK, failed-as-empty read or invented metadata/value|
|Lifetime/resources|Original request epoch, complete command accounting and natural shutdown/engine/process/both EOFs with bounded cleanup|One command finish, early close, exit0, snapshot counters or PHPUnit heap|

All five domains join the same attempt/pair/generation/request/session, with a
fresh command identity, source revision, execution-image identity, lease generation
and fixed profile/result/resource policy revisions. No cross-attempt borrowing,
automatic fallback, epoch renewal or caller-selected parser/provider. Source
closure and source ownership are different obligations: freezing unreviewed code
does not audit it, and reviewing bytes does not freeze what the loader executes.

## Candidate image/lease ownership contract

The trusted staging/supervision principal and the candidate execution principal
must have genuinely different effective mutation authority. The worker and any
other in-scope application writer must be unable to modify/replace/rename/delete
image members, mutate configuration/permissions/ownership, or substitute any
ancestor/path/loader alias. A writable ancestor, reparse/hard-link escape, open
write handle, inherited handle or writable dependency outside the image fails
the candidate. A process-local mutex or a cooperative writer promise is not an
OS source boundary. A worker running with the staging owner's authority fails.

Windows access depends on effective tokens/security descriptors; parent deletion
and DACL/owner rights require separate consideration. See Microsoft's
[file security](https://learn.microsoft.com/en-us/windows/win32/fileio/file-security-and-access-rights),
[standard rights](https://learn.microsoft.com/en-us/windows/win32/secauthz/standard-access-rights) and
[file attributes](https://learn.microsoft.com/en-us/windows/win32/fileio/file-attribute-constants).
A read-only attribute is not this ownership mechanism. These references motivate
the candidate requirements; they do not prove a working LocalWP/Windows profile.

The trusted host/administrator is outside the protection claim; arbitrary code
sharing that authority is not treated as an untrusted isolated worker. If this
separation cannot be enforced on the actual host, refuse this candidate rather
than relabel the existing same-user LocalWP tree as immutable. Do not change
existing accounts/ACLs, elevate the worker or lower the threat model to pass.

The finite image manifest must cover genuine core and reviewed plugin/bootstrap
sources, PHP executable/SAPI and loaded native dependencies, loader/search paths,
fixed generated non-secret configuration, prepend/append/preload/autoload inputs,
drop-ins, theme/language and conditional code paths. External reads, cache/request
predicates and absence domains retain explicit source/effect obligations; copying
an image does not authorize them. Do not copy live credentials/configuration or
silently replace genuine functions/hooks with stubs to obtain a pass.

Build only in a future approved owned staging area, verify exact bytes/objects
and resolved paths, seal before admission, then bind the observed process/loader
to those protected objects. No writable alternate source, stale preloaded/opcache
artifact or library search fallback may escape that binding. Existing package/PE
metadata alone does not establish loaded build equivalence. The trusted owner must
not reopen image writes during the lease; unknown effective rights/loaders refuse.

## Planned admission/lifetime sequence — no executable API

1. Resolve fresh exact local target and approved owned proof scope. Establish
   independent supervisor ownership, original deadline and cleanup authority.
2. Require a complete reviewed manifest and qualified sealed-image denial proof.
   Acquire a fresh non-transferable lease before any genuine bootstrap read.
3. Bind source/profile/resource revisions and child identity while all native
   actions remain closed. A digest or frozen JS object cannot manufacture a lease.
4. Only a separately approved observation proof may start one genuine fresh
   nonpersistent session with independently verified limited rights. Retain the
   genuine no-schema connect/setup/later selection sequence and driver defaults.
5. Observe exact direction/sequence/command/result on the owned pair, with
   bounded detached bytes before any result is credited. Track source/API work,
   emitted operations, results, verifier work and whole-request resources apart.
6. Keep image/process/observer ownership through natural shutdown, engine/native
   teardown, process exit and both EOFs. No early close/GC/forced teardown as a
   successful suffix. Release the lease only after confirmed bounded cleanup.

This sequence is a design obligation, not a universal command count or a native
grant. Existing32 attempted application queries/4096 SQL bytes, original parent20s
and existing child/SQL/lock/statement/HTTP/output limits remain unchanged. There
is still no approved native peak-memory policy. No new number is inferred from
model16KiB, configured256M, driver packet maximum or source bytes.

Every loss of source lease, replacement identity/path, unqualified profile,
deadline regression/expiry, unexpected read/result, replay/reconnect/probe/second
handle or incomplete suffix is sticky refusal. Failed work remains cumulative;
no automatic retry/replacement, implicit source recertification or fresh budget.
Contain/stop only owned resources under future approved cleanup authority. If
cleanup cannot establish their terminal state, quarantine non-secret ownership
records and report the blocker; do not delete unrelated state or admit a successor.

## Genuine profile admission is not synthetic-profile translation

`wpdb-setup-wire.synthetic.v1` supports two fixed invented command families and
ordinary-EOF fixture metadata. Its `schema-selected`/mode outcomes, copy counters
and private symbols do not establish genuine negotiation or full setup completion.
Keep the helper, DATA model, old observer/zero-row decoder and actual worker
unchanged. Do not pass forged old flags/context, strip negotiated bits, disable
driver features or alter genuine setup to fit the fixed reader.

A future candidate must independently qualify handshake/auth outcome and privacy,
real supported flags/charset/plugin/result terminators, charset API-to-wire0/1/many,
successful one-value mode semantics/filter predicates and later schema selection.
Unknown capabilities/metadata/warnings/more-results/dynamic values or source hooks
remain unsupported. A profile-specific successor parser needs its own source-
scoped review before use; this design neither chooses a real mask nor creates one.
Retain no raw auth packets/credentials/cookies, private request bodies or mode/SQL
values in public evidence. Publish only bounded non-secret revisions, counts,
typed outcomes and cleanup facts; private correlation is not authentication.

## Closure ledger and proof obligations

|Open item|Design result here|Still required; not closed by this document|
|---|---|---|
|A1 source/effects|Exact graph and fixed profile predicates are prerequisites|Complete current source/callback/include/error/replay rebaseline|
|A2 lifetime|Image lease spans loader, command and full terminal suffix|Concrete object/capture/native-resource and full process/EOF proof|
|A3 resources|No subtotal promotion or new budget; original epoch retained|Whole-request cost/heap policy and independently approved enforcement/proof|
|A4 ownership|Select separate-principal sealed image/lease candidate|Effective rights/path/loader anti-substitution and actual installed/build binding|

Future pure tests may prove finite record validation/state transitions only.
Require non-vacuous model-positive sequences with all actual source/auth/native/
forwarding/backing/resource/storage/wiring/execution claims false. Exercise stale/
mixed manifests/leases/profiles, duplicate/replayed commands, accessor/revoked-
proxy inputs, expiry/renewal attempts and cleanup uncertainty. Semantic guard
removals must cause assertion failures; no artificial native-success path.

Physical ownership proof must separately demonstrate allowed reads and denied
mutation/replacement/permission/alias attempts under the actual consumer identity,
including open-handle and parent-directory cases, retained through the required
lifetime. It needs owned disposable fixtures, previewed exact cleanup and distinct
privilege authority. In-memory tests, source hashes or OS documentation cannot
satisfy it. A later genuine session proof still needs its own target/fixture/
authentication/storage approval; a filesystem denial proof grants none.

## Review, publication and exact next gate

Canonical source-scoped Feature Light: four Markdown files (owner, both plans,
companion successor pointer), Other/design. Draft clarified the distinct source
closure/lease obligations and rejected same-principal/read-only-attribute shortcuts.
No executable defect/fix or new test claimed. No meaningful Bloat/UI/UX trigger.
Final focused Feature Security: no new auth/storage/IO/credential/permission path
or confirmed security-fix handoff. Historical whole-plugin readiness/DS3/Database/
physical/FULL rows are not refreshed. FULL remains due for a coherent release RC.

Existing synthetic tests and exact-head CI are preservation/publication checks,
not implementation or source-ownership proof. Restore OptionA checked cleana201230
and existing external20261024-161220 archive; no new backup/checkpoint/restore.
Actual counts/run links are recorded source-scoped in toolkit evidence.

Implementation-plan progress: real observation prerequisites and a specific
source-ownership candidate are now defined; all A1-A4/native GO remain Blocked.
Recommended next gated slice: **Plugin Tester local-only Fixed Source-Image
Ownership Feasibility and Denial Proof**, first resolving whether separate trusted
staging/consumer authority is supportable, then only under explicit R3 approval
using owned disposable filesystem/principal/permission fixtures and exact cleanup.
No WordPress bootstrap, database, actual setup session or production wiring in
that first proof. It must stop if the host cannot enforce the proposed boundary;
do not substitute more synthetic maintenance for the next physical ownership gate.
