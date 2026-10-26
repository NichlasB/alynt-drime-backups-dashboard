# V2.3 Product/Threat-Model Rebaseline and Implementation-Plan Reset

2026-10-26. User-approved repository-only decision following the proportionality
review. R0 analysis/R1 Markdown publication; no implementation, installation or
site work. Excluded review bases dashboard0acb35a58fdbebe8f0eff94fb29f954833b6fd0f,
uploaderad2bb66f8ed9c4fd49fa2f420b34cb806f1a5ed0,
toolkitc5e67338f6ff042033da8b1db788fd9bb2f35c17.

## Current decision and routing precedence

**Defer schedule rollback execution and park the Windows-native proof branch.**
Do not install/resume MSVC/SDK setup, build/run a supervisor, stage protected
objects, alter accounts/permissions or open another synthetic/native proof slice
as the automatic next step. Prior operational approvals are not instructions to
resume that branch after this reset. Reopening it needs a fresh explicit decision
with purpose, supported environment, cost, acceptance criteria and stop conditions.

This record and the current entries at the top of both implementation plans
supersede dated *next-action routing* toward source-image leases, Windows
supervisors, protected bootstrap, toolchain provisioning and their DATA/native
follow-ups. They do not overwrite historical evidence or relax existing code,
tests, unavailable facades, profile refusals, protocol or operational gates.
Historical documents remain available for technical reference, not an automatic
task queue. Do not manufacture cleanup/model/docs tasks to fill a numeric batch.

Current shipped monitoring, guarded Schedule Apply and preview behaviour are
unchanged. `schedule_rollback` stays reserved/rejected/unadvertised; no execution
opt-in, control, hook, loader or native admission flag is enabled. Existing
cadence authority/closed-registration families remain dormant. Versions remain
dashboard0.1.77/uploader0.5.25, PHP7.4/WP6.0 minimum unchanged. No package/release/
tag/deployment or claim that the current repositories are release-ready.

## Product boundary and cost/benefit

The [original runtime design](V2_3_SCHEDULE_ROLLBACK_RUNTIME_DESIGN.md) concerns
one source-linked undo of a successful `alynt_scan_upload` cadence change to a
previous client-owned supported cadence. It is not backup restoration, undoing
uploads, arbitrary scheduling or settings recovery. That design expressly says
the feature is optional and records guarded reverse preview/apply recovery in
the historical pilot; it does not establish repeated operational demand.
The [decision record](V2_3_SCHEDULE_ROLLBACK_DECISION.md) already requires evidence
that a new mutation action materially reduces operator burden or risk.

The reviewed records do not establish that product prerequisite. Default to
continued deferral, not another implementation milestone. The existing recovery
path is a recorded operational alternative, not a newly certified atomic/crash-
safe primitive. Its known writer/persistence limits must remain visible.

The later branch expanded into complete genuine WordPress bootstrap read/effect
closure, source immutability through process teardown, private native sessions,
whole-request resources and separate Windows principal/ACL/bootstrap ownership.
The [source-ownership design](V2_3_SETUP_OBSERVATION_ADMISSION_SOURCE_OWNERSHIP_DESIGN.md)
and [latest feasibility audit](V2_3_PROTECTED_BOOTSTRAP_ADMISSION_FEASIBILITY_AUDIT.md)
show that a compiler would resolve tool availability only. It would not complete
the other qualification obligations or certify a deployed WordPress environment.

Judgment: that high-assurance lab project is disproportionate to the currently
demonstrated product benefit. A Windows helper is neither a WordPress runtime
dependency nor a general prerequisite for maintaining/releasing unrelated plugin
features. Keep its research evidence; remove it from the default product path.

## Security boundary retained; future simplification is not a bypass

The [V2 threat model](THREAT_MODEL_V2.md) remains the current product baseline.
Its rollback candidate does not claim protection against a fully compromised
client. Normal future WordPress integration would explicitly trust the supported
installed core/plugin code and host/database administration; it must not claim
an OS sandbox, immutable-source lease or hostile same-process PHP isolation.
This is a planning direction for a separately reviewed future design, not a new
supported request profile or permission to execute an existing dormant entry.

Core bootstrap/authentication reads and plugin-owned protected-state capture
must be distinguished. A conventional admin request is not automatically a
zero-read/write-free bootstrap. A future design must inventory relevant plugin
load-time reads/effects and protect feature-owned state at the correct boundary;
it cannot silently authorize early protected capture or label an old blocked
projection/profile supported. Existing R2/full-bootstrap containment contracts
and guards stay unchanged. Replacing their qualification contract, if justified,
requires an explicit versioned/reviewed boundary and genuine integration proof.

Retain genuine authentication, current capabilities, CSRF intent, exact action/
site/enrollment binding, freshness and revocation, strict schemas and redaction.
Nonces are not authorization or durable replay protection. Preserve separate
intent domains, durable single-use claims, exact/conflicting duplicate handling,
bounded work, uncertain-outcome closure and no blind retry. No guard, source pin,
test assertion, bound or unavailable bit may be removed merely to obtain green.

The recorded WP-Cron nonparticipating-writer counterexample remains material.
Using ordinary scheduling APIs or adding an action mutex does not prove safe
coordination. Preserve the client-owned authority candidate as research, not an
automatic migration decision. Any future design must establish its actual writer,
storage/cache, crash/restart and unrelated-event semantics or remain deferred.
No new schedule/SQL/schema/credential authority follows from this rebaseline.

## Evidence disposition — no historical readiness promotion

|Area|Disposition now|Not established or authorized|
|---|---|---|
|Parser, authorization-order and provenance tests|Retain source and meaningful positive/negative controls|Mocked APIs do not become genuine auth or production proof|
|Owned storage/concurrency/uncertain-COMMIT proofs|Retain exact environment/fixture/attempt limits and discovered failures|No portability, all-crash-boundary or exactly-once external-effect guarantee|
|WP-Cron overwrite / persistence limitations|Retain as real design blockers requiring resolution if runtime is reopened|No global interceptor, unprotected overwrite or silent weaker success criterion|
|A1 source/effects and A2 lifetime|Unproven/Blocked for the old native profile; research deferred|No full-bootstrap/source-closure or natural terminal-suffix pass|
|A3 resources and A4 ownership|Unproven/Blocked for the old native profile; research deferred|No approved whole-process heap/OS ownership/loader lease qualification|
|Windows supervisor/bootstrap/toolchain|Deferred, outside default product queue|No install/build/elevation/account/ACL/owned-native experiment now|
|Dormant closed entry / active cadence / rollback|Unavailable or disabled as before|No hook/profile activation, grant, dispatch or rollback execution|
|Historical whole-plugin reviews and release gates|Preserve dates/status/evidence|No Database/FULL/release-readiness refresh from this planning record|

The previously unexplained Windows full Node assertion remains unresolved; two
unchanged passing repeats and Linux CI did not diagnose it. Preserve that limit.
Do not remove prior helpers/tests, uninstall tools or delete downloaded/temp
installer artifacts under this documentation scope. The earlier installer attempt
was reported cancelled before setup; no fresh installed-state audit is performed
or inferred here. No cleanup or zero-artifact claim is made.

## Bounded route only if the product need is established

Before reopening rollback, document a concrete operator incident/workflow,
frequency or expected burden, why guarded preview/reverse apply is insufficient,
and measurable acceptance criteria for the narrow cadence-only action. Do not
collect secrets or mutate a site to manufacture that evidence.

If justified, propose **one coherent WordPress-native rollback design**, mapping
each original safety requirement to retained evidence, a genuine missing test,
or an explicit unproven limitation. State supported core/PHP/database/cache
environments, ordinary bootstrap trust, real atomicity/recovery constraints,
resource guarantees and exclusions. No implicit production dependency on Windows
or a custom elevated supervisor. Reject the approach if safe writer/recovery
semantics still require disproportionate redesign; deferral is a valid outcome.

Only after that design is separately approved may implementation/owned integration
proof be considered, with exact target/write/cleanup confirmation. Protocol,
migration/authority activation, release, deployment and live enablement remain
separate gates. No estimate of remaining runtime tasks is earned by parked models.

**Recommended next action: retain rollback deferral; reopen its product decision
only when a concrete operator need demonstrates benefit beyond the current
guarded recovery flow.** There is no automatic next Windows or model milestone.

## Source-scoped review and publication limits

Canonical Feature Light and final focused Feature Security cover this decision,
both plan entries, the product/runtime/threat-model pointers and the latest audit
routing banner: seven Markdown files, Other/planning. Scope is one rebaseline,
not a whole-plugin audit. No code/test/dependency/public wire contract or rendered
UI changed, no confirmed executable defect/regression-fix claim. No Bloat/UI/UX
trigger or new tests for prose. Light review clarified qualification-contract
wording so dormant R2 is not described as a shipped public guarantee; no code fix.
Reviews preserve product authorization and old
proof refusals separately; no blocked result is converted to success. Publication
QA/CI is preservation evidence only, recorded source-scoped in toolkit evidence.
FULL remains required for a coherent release candidate before packaging/publishing.
