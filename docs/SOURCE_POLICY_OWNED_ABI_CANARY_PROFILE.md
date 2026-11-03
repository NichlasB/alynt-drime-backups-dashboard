# Owned Native ABI Canary Execution Profile

## Approved boundary and plan — 2026-11-03

User approval selects a **separate**, default-off ephemeral GitHub lab profile,
not a new SQL-lab role. Restore point: clean/synced master
`b9909735b7d583770b564efcce54afb415ad39d7`, ordinary CI41647139839 success.
Campaign baseline remains8/10 milestones,native7/10,static9. Reserve native
attempt8/10 for the single ordered dispatch; do not count scaffolding/retries/docs.
No LocalWP/site target, installation, downloads, candidate acquisition/extraction/
execution, SQL, production wiring, grants, activation or rollback is authorized.
This is narrowly approved R3 owned lab work, not a general risk-tier increase.

Sequence: fixed DATA contracts and non-vacuous isolated regressions; source review;
existing compiler/engine/runtime feasibility; source-pinned ordinary CI; one
ordered native attempt; primary plus independent cleanup; actual evidence and
exact-head publication verification. No unchanged native rerun on failure.

## Closed sources, image and operations

A separate manual workflow accepts exact master SHA and a typed default-false
enable switch. Contents:read, pinned checkout, hosted Ubuntu24.04, no credentials
in child environments, ten-minute job and existing shared lab concurrency remain.
The controller admits exact event/repository/ref/workflow/head/run/attempt and
rejects Docker overrides. It freezes its finite source cohort before any owned
work and verifies it again before execution/closeout. Existing SQL workflow,
images/version guards and candidate-frame APIs remain byte-for-byte unchanged.

Audit existing `/usr/bin/cc`, Docker28.0.4/client and runc1.5.2/libseccomp2.5.5 first.
Missing/incompatible tools refuse: never install or substitute a downloaded tool.
Compile two bounded static, no-libc, non-PIE owned executables with fixed flags.
ELF readback must reject interpreter/dynamic segments and oversized files.
The host availability executable contains **only** harmless identity/getpid and
PR_GET_SECCOMP reads; it has no networking implementation or argument dispatch.
The container executable alone has closed ABI selectors and fixed syscall probes.
Neither executes shell, opens files, loads libraries, forks or accepts stdin.

Construct one tiny, untagged, single-owned-file image archive without a base image,
Dockerfile/build daemon or image pull. Bind config/layer/image/source/head/run/
attempt hashes and labels. Refuse a pre-existing image ID before load; record
creation intent before fallible import. Inspect exact image configuration before
container creation, then source-held labels, CID, command, security/resource/
namespace settings and copied executable bytes before every start.

Each container is numeric nonroot65532, root read-only, network:none, private
PID/IPC, cap-drop ALL, no-new-privileges, no devices/binds/volumes/tmpfs/ports/TTY/
stdin/restarts/healthcheck. Memory512MiB, one CPU, PIDs64, nofile64/core0, logs16KiB.
Commands/time/output are bounded; fifteen seconds per control, shared540-second
body deadline plus cleanup reserve, control1MiB/observation2MiB and aggregate
native output16KiB. No arbitrary command/image/profile/path is admitted.

## Policy and native evidence

Canary context is a distinct format/purpose, with actual source/run identity and
no candidate graph/snapshot/manifest placeholders. Derive from the same pinned
Moby28.0.4 raw JSON by removing only the22 networking ALLOW names. Independently
verify every protected field/rule/name order and exact31/428→30/406 delta before
writing a private profile. Candidate APIs retain their old strict frame checks.
Explicit `seccomp=builtin` is the positive control; only the exact derived bytes
are the negative control. Inspect request transport hashes separately from actual
syscall observations. Never call config readback loaded-BPF attestation.

Pinned libseccomp2.5.5 [number table](https://github.com/seccomp/libseccomp/blob/f0b04ab0b4fc0bc2cde6da1f407b4a487fe6d78f/src/syscalls.csv)
and [x86 translation](https://github.com/seccomp/libseccomp/blob/f0b04ab0b4fc0bc2cde6da1f407b4a487fe6d78f/src/arch-x86.c)
define direct amd64, int80/i386, x32-bit and legacy socketcall routes. PNR names
are not invented direct syscalls. Static low addresses make compat pointers
valid; descriptor arguments are signed correctly. Native positives require real
getpid/UID/NNP/seccomp state, successful private AF_INET socket creation/close,
and syscall-specific error controls, followed by continued execution. The
negative requires EPERM for every selected networking route, not unavailable.
Legacy socketcall covers its fixed20 suboperations, including send/recv aliases.
Invalid-descriptor probes cannot transmit data or contact any destination.

All-unavailable results fail qualification. Unsupported ABI observations remain
separate: host getpid calls and a same-release config file are corroborating
facts, **not boot attestation or an automatic complete-ABI grant**. If x32 cannot
be established, report available-ABI controls and the exact residual gap. Never
weaken the old all-ABI acceptance obligation to make this proof green. No
candidate runtime eligibility, builtin binary correspondence or SQL result is
inferred from owned canaries.

## Ownership, failure and proof limits

Deterministic run/head/attempt/source-bound owner labels and a private regular
stage marker precede import/create. Full IDs and labels/image/config are freshly
checked before start/stop/remove; no broad prune, force removal, shared image
deletion or foreign enumeration output. Cleanup preserves the first failure,
stops only owned running containers, removes owned exited containers, removes
only the freshly imported owned untagged image, then the exact private stage.
An always-run second process independently reconciles labeled resources and stage
absence; uncertainty fails rather than claiming cleanup. No build cache exists.

Isolated tests must reject missing guards, altered profiles/commands/ownership,
malformed/unavailable/replayed observations and false ABI completion. They prove
contracts only, not native kernel enforcement. Source-scoped error/security/
resource review and full ordinary CI precede dispatch. This is not whole-plugin
release readiness, a Database Review, candidate proof or a production change.

## Pre-dispatch validation and focused source review

Fourteen isolated groups pass, including real pinned-policy positives, exact
direct/multiplexed number-table checks, static image/ELF and containment mutation
matrices, actual owner copy-before-start ordering and foreign-owner cleanup
refusal. Five source-backed semantic controls remove real raw-pin, ELF-loader,
network-namespace, continuation and executable-readback guards in private test
namespaces; each is assertion-killed. No tracked source is mutated, and no
compiler/engine/native body is invoked by these tests. The modeled positive
owner must actually reach its modeled start; unavailable-only evidence fails.

Focused source-scoped review covers error preservation, fallible load/create
ownership intent, source/path closure, private child environments, descriptor/
stdio bounds, cleanup reserve, no broad removals and no SQL/candidate dispatch.
Corrections before dispatch: decode the line-wrapped inert base64 fixture
losslessly; independently reserve bounded cleanup output/time after body failure;
require private cgroup/UTS/user/PID/IPC, default AppArmor and runc readbacks; disable
the compiler red zone around the compat int80 wrapper's saved frame pointer.
Image inventories use documented full-ID enumeration and **--all**, including
untagged/intermediate images, for both pre-import absence and labeled cleanup;
a dedicated regression prevents false absence from a filtered-out owned image.
These are in-scope profile/harness fixes, not relaxed runtime acceptance.

Local targeted PHP21/116, full PHP2,610/23,462 (two existing Sodium skips), Node88/88,
PHPCS255 files, structure, build, Composer strict validation/audit and npm audit
pass. The expanded14-group/five-control suite is rerun after final guard changes;
exact-head CI must verify the final complete source before any dispatch.
These focused engineering reviews do not refresh canonical whole-plugin toolkit
rows, historical release readiness or the postponed full pre-release suite.
