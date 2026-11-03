# UBI Engine Import / Containment Feasibility Audit

## Current authority and restore point — 2026-11-03

User approves audit-first **Candidate-Bound UBI Owned Engine-Import and
Effect-Containment Feasibility / Cleanup Proof** in the trusted ephemeral CI lab.
This narrow R3 scope permits the described non-executing owned import/readback
and bounded harmless containment controls only after their prerequisites qualify.
It never permits candidate execution, SQL, LocalWP/live/production operations,
global security changes, installed dependencies or production wiring.

Restore point **f9ff1f228bdf89aad39c1ee2aefacd8ed4ed3b4a** is clean/synced, equal
to remote master; [ordinary CI41630733031](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41630733031)
is reverified successful. Read repository governing instructions, owning plan,
[design](SOURCE_POLICY_UBI_CONNECTION_FREE_RUNTIME_ADMISSION_DESIGN.md),
[dormant foundation](SOURCE_POLICY_UBI_RUNTIME_CONTRACT_FOUNDATION.md), existing
static workflow/owner closeout and existing native controller source. Site
Operations is read for boundary classification: no installed site is involved,
so neither a site target nor Novamira/site inspection is used. Uploader/toolkit
and all existing static/native workflows remain untouched.

## Audit-first sequence and actual source constraint

Do not acquire/import candidate layers or create/start a container merely to
discover that containment admission is unavailable. First establish the actual
ephemeral host engine version/build, local endpoint, storage driver and builtin
seccomp posture. Then map that observed build to immutable official source and
determine whether the proposed non-weaker policy can be qualified. Unknown
semantics fail closed before physical import/containment qualification.

The existing pure `restrict_policy` deliberately admits only three top-level
fields: defaultAction, architectures and syscalls; each rule has only names and
ALLOW action. The official Moby28.0.4
[default JSON](https://github.com/moby/moby/blob/v28.0.4/profiles/seccomp/default.json)
instead has defaultErrnoRet and archMap (amd64 also selects x86/x32), conditional
includes/excludes, argument filters and explicit errno rules. Its
[setupSeccomp](https://github.com/moby/moby/blob/v28.0.4/profiles/seccomp/seccomp_linux.go)
resolves architectures/capabilities/kernel selectors; builtin policy comes from
[DefaultProfile](https://github.com/moby/moby/blob/v28.0.4/profiles/seccomp/default_linux.go),
not a promise that an arbitrary copied JSON file equals the running daemon.
The current synthetic transformer must reject this shape, not discard conditions,
flatten arguments or replace the real policy with its toy positive fixture.
Public tag source is preliminary reference, not yet a pin to an observed daemon.

The Docker [load implementation](https://github.com/moby/moby/blob/v28.0.4/image/tarexport/load.go)
supports original-config creation and ordered diffID checks for its manifest
format, but that is not actual same-attempt import/readback evidence. The daemon
also stages archive data internally: stream transport is not proof of zero engine
disk materialization. No import-format compatibility or resource/cleanup result
is inferred from reading that source.

## Fixed read-only audit implementation

The separate default-inert manual
[workflow](../.github/workflows/source-policy-ubi-engine-audit.yml) and
[collector](../scripts/ci/source-policy-ubi-engine-audit.mjs) permit only:

- Git exact-head/scoped immutable-source readbacks;
- `docker context inspect` for the fixed local Unix endpoint;
- `docker version` and `docker info`, projected into a <=64KiB finite metadata
  summary without raw hostnames, proxy values, environment or error payloads.

Fixed job admission precedes reads: repository/master/workflow/manual head,
github-hosted Linux/amd64 and no Docker environment overrides. Immutable source
cohort is the collector, its workflow and the existing dormant runtime contract;
before/after hashes and Git readback are required. No generic command/argument
input, image acquisition/import/save, container command, artifact upload, custom
profile write/application or subprocess candidate is registered. Commands have
closed stdin, no shell, a finite environment without inherited secrets,10s/1MiB
bounds. Workflow timeout2min, contents:read, pinned checkout and shared existing
feasibility concurrency. Unknown metadata refuses with finite non-secret reasons.

The audit emits default_policy_source_established=false, qualification=false,
runtime_grant=false, image_loading=false and candidate_execution=false. It creates
no engine/storage fixtures, so there is no fixture cleanup operation to invent.
It is **metadata discovery**, not an ordered candidate/native qualification;
campaign native7/10 and static acquisitions9 are unchanged until an actual
qualification begins. No completed product milestone is credited for this audit.

Eight Node groups use the actual collector/command boundary with inert fixtures:
positive projection, admission-before-read, source/endpoint ordering, malformed
or unknown profiles, fixed bounded readonly argv/environment, source/workflow
exclusions, supported workflow-context placement and two source-backed
assertion-killed job/endpoint guard removals.
Stubbed fixtures prove those logic contracts, not actual engine/default-policy
enforcement. Exact-head ordinary CI must succeed before manual metadata dispatch.
Actual job/source results and continuation or blocked gate follow below after
execution; historical CI is not substituted for current verification.

Executed local validation:8 new Node groups (two semantic controls), full Node
82/82 with no skips; full isolated PHP2,608/23,454 with two existing local Sodium
skips and no failures/errors. PHPCS, structure, asset build, Composer strict
validation/audit, npm audit (zero vulnerabilities), owning local links and diff
checks pass. Source-scoped review confirms fixed read-only argv, bounded private
child environment, no raw metadata/exception disclosure and no existing static/
native workflow or production change. No whole-plugin release-readiness,
Database Review, actual import or kernel-enforcement claim is refreshed.

Publication recovery cycle1: initial head dfc5b8a registered a workflow parse
failure [41632527238](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41632527238)
with zero jobs, not a native attempt. Source audit against GitHub's official
[context availability table](https://docs.github.com/en/actions/reference/workflows-and-actions/contexts#context-availability)
identifies that runner is unavailable in job-level env and supported in step env.
A red placement regression is added before moving the same github-hosted
identity expression to the audit step; the guard is preserved, not removed.
Fresh ordinary exact-head CI is required before any dispatch. No native/resource
operation or failed qualification has happened during this workflow repair.

## Actual exact-head audit and immutable-source comparison

Recovered implementation head **6ea18d483746f4694e0d447c0fb7a349491307d0**:
[ordinary CI41632777798](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41632777798)
succeeds. Actual logs show PHP7.4 **2,608/23,456 assertions**, PHP8.3
**2,608/23,461**; Node82/82 in each, no failures/skips, zero Composer/npm
vulnerabilities. The earlier invalid workflow never started a job; it is retained
as recovery evidence, not erased or represented as green.

The separately dispatched exact-head read-only
[audit41633106695](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41633106695),
attempt1/job125907401869, succeeds **2026-11-03 12:07:48–12:07:59 UTC**.
Actual output (not fixture values):

| Observed field | Value |
|---|---|
| Runner image | ubuntu-24.04 /20261025.353.1 |
| Engine/API | Docker28.0.4 /1.48 |
| Reported engine commit | `6430e49` |
| OS/architecture/storage | linux /amd64 /overlay2 |
| Security options | apparmor; seccomp profile=builtin; cgroupns |
| Kernel | `7.0.0-1017-azure` |
| Exact three-file audit source SHA256 | `70f0cd0062c207c8674e6c62699ea00bc8ba3539f30051ee3d7da8e7c02a22f8` |
| Resources/artifacts | resources_created0; workflow artifacts API0 |

Image loading, candidate execution, database operations, qualification and
runtime grant are false. `default_policy_source_established=false` remains
truthful: engine/build metadata and public source mapping do not attest the
effective builtin filter or a valid derived container profile.

Official annotated v28.0.4 tag dereferences to full Moby commit
**6430e49a55babd9b8f4d08e70ecb2b68900770fe**, matching the observed short commit.
Read-only GitHub source API returns the following immutable bytes, hashed in
memory (no downloaded profile/source file, runtime load or policy application):

| Official source at that commit | Bytes | SHA256 |
|---|---:|---|
| [profiles/seccomp/default.json](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/default.json) |12,828|`9c1025c88ccaa517b648da571961838744ea2137f176bfe6a48b21294cae9c76`|
| [profiles/seccomp/default_linux.go](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/default_linux.go) |15,905|`4529ff8a038f4365ba865d30be2468f6c64b80c832567d978e91906999cc632b`|
| [profiles/seccomp/seccomp_linux.go](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/seccomp_linux.go) |4,742|`b95bbcb005cd7f049b033e4f64d91a7f35c2faf707198bf2218075298d5c465e`|
| [image/tarexport/load.go](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/image/tarexport/load.go) |11,786|`0eb425cc99c5bf76dac764cfd51892f93a8817751f463f4d973b4c983208421e`|

The actual pinned JSON has31 rules,8 argument-filter rules,22 includes rules and3
excludes rules, ALLOW and ERRNO actions, and amd64→x86/x32 mappings. Feeding these
exact source bytes to the **unchanged actual pure `restrict_policy`** yields
`Refusal('policy-shape')`. This is an executed compatibility comparison, not an
invented failure or an engine filter proof. A synthetic tiny allowlist cannot
substitute for it. The Go builtin constructor/resolver also needs preservation
and source/profile correspondence, not only a version-string match.

## Outcome, proof limits and next genuine gate

**Read-only prerequisite audit complete; approved physical import/containment
proof blocked before resource creation.** No acquisition, second pull, static
rerun, Docker import/create/start, containment canary, profile application, fixture,
SQL or candidate execution occurred. No owned resources require deletion; this
is not a physical cleanup/containment claim. Artifact count0 is independently
read from the Actions API. Existing guards, budgets, pins and wiring are intact.

Do not repair this by broadening the old simple-profile guard, flattening real
rules, dropping architectures/conditions, applying an unconfined/default-ALLOW
profile or invoking only network:none. Such a real policy-admission decision is
not an ordinary harness correction. The approved design explicitly requires a
stop when non-weaker default-profile/ABI semantics cannot yet be established.

**Recommended next gated slice: Repository-only Candidate-Bound Moby
Builtin-Profile Preservation and ABI-Complete Effect-Denial Design.** Approve
design-only work for a source-pinned, demonstrably non-weaker derivation that
preserves actual conditional/argument/errno rules and all admitted ABIs, or
rejects unsupported semantics. Define source-to-builtin correspondence and
positive/denial control prerequisites before implementing or applying a real
profile. Keep acquisition, import, native/canary/candidate execution, SQL and
production wiring disabled during that design. After separately approved
implementation, return to the retained owned import/containment proof authority;
do not infer fresh static or engine evidence from this historical audit.

Implementation-plan progress: a real source/profile compatibility blocker is now
established before spending candidate resources. Campaign remains **8/10
milestones,native7/10,static acquisitions9**; no metadata/repair/docs milestone
credit. This approval has not completed actual import or kernel qualification.
Docs-only closeout preserves the three audited source files; final publication
head, actual CI checks/counts and clean/remote state are verified at handoff.
