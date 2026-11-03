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

Seven Node groups use the actual collector/command boundary with inert fixtures:
positive projection, admission-before-read, source/endpoint ordering, malformed
or unknown profiles, fixed bounded readonly argv/environment, source/workflow
exclusions and two source-backed assertion-killed job/endpoint guard removals.
Stubbed fixtures prove those logic contracts, not actual engine/default-policy
enforcement. Exact-head ordinary CI must succeed before manual metadata dispatch.
Actual job/source results and continuation or blocked gate follow below after
execution; historical CI is not substituted for current verification.

Executed local validation:7 new Node groups (two semantic controls), full Node
81/81 with no skips; full isolated PHP2,608/23,454 with two existing local Sodium
skips and no failures/errors. PHPCS, structure, asset build, Composer strict
validation/audit, npm audit (zero vulnerabilities), owning local links and diff
checks pass. Source-scoped review confirms fixed read-only argv, bounded private
child environment, no raw metadata/exception disclosure and no existing static/
native workflow or production change. No whole-plugin release-readiness,
Database Review, actual import or kernel-enforcement claim is refreshed.
