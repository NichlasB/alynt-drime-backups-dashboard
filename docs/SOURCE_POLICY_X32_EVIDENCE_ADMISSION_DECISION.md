# x32 Evidence Sufficiency and Candidate Admission Decision

## Scope and decision — 2026-11-04

Approved repository-only design/review, relevant isolated validation, commit/push
and ordinary exact-head CI. Restore point **db033e8c4f32f5a345cf10e6978421bffebf33d2**
is clean, freshly matches remote master and has successful
[CI41856653056](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41856653056).
No native experiment, manual workflow dispatch, runner/site inspection, syscall,
Docker/candidate/SQL operation, source/acceptance-rule change, new attestation
tooling, installation, permission/credential/schedule change, package or release.
Public upstream sources and existing GitHub run metadata are read-only inputs;
no source archive, binary, installed-site file or runtime artifact is acquired.

**Decision: retain x32-unestablished and candidate admission CLOSED.** Current
evidence cannot be promoted to complete non-callability or filter enforcement.
Select one small, separately approved **existing-runner read-only kernel-evidence
feasibility audit** as the next step, not another syscall experiment or a new
attestation project. Audit availability/sufficiency is unknown; nothing here
implements or authorizes that audit or a later candidate launch.

## Existing proof and assurance boundary

The [actual source-bound proof](SOURCE_POLICY_OWNED_ABI_CANARY_PROFILE.md#public-compression-repair-proof-passed-x32-coverage-remains-open--2026-11-04)
is head592fe457fe7a9e428e13f8fd7f04483a7a8e7f1e/run41856008408, attempt1,
source0a67b48372a2e4ed7c41cb0c76bf7b8bf4a97bcd2ccaa100b3187285c0cb48bd.
The immutable run is rechecked successful without rerunning it. amd6418 and
i38638 routes each have genuine builtin-positive/derived-denial controls;
both cleanups report zero containers/images/staging, artifacts0. x32 builtin
returns ENOSYS; no x32 derived control runs, and qualification=false.

| Evidence | What it establishes | What it does NOT establish |
|---|---|---|
| Host x32 getpid and container x32 getpid/network probes return -38 | Those exact operations returned ENOSYS in their observed contexts | All x32 entry paths are impossible, or a successful x32 seccomp denial control |
| /boot/config-7.0.0-1017-azure, hash59b49d17..., declares X32=n | Complete admitted on-disk file/config observation | That the running kernel was built from that file; receipt explicitly says boot_correspondence=false |
| Observed kernel release7.0.0-1017-azure and runtime/libseccomp identities | Reported release and trusted tool/runtime version correspondence | Exact Azure source/build provenance or active per-ABI enforcement by name alone |
| Fixed C routes and unchanged JSON archMap/name subtraction | Source and structural correspondence, with useful actual amd64/i386 controls | Physical x32 filter coverage or a runtime grant |
| Successful workflow and cleanup | The available-control job completed and owned resources were removed | Overall qualification=true or permission to start the candidate |

The canary issues x32-tagged syscalls from the SAME amd64 executable. Therefore
rejecting an x32 ELF loader or saying the candidate is an amd64 ELF is not a
closure argument. x32 here is a possible syscall calling convention, not a new
promise to support x32 WordPress/PHP. Keep every original ABI mapping/deny rule.

The [accepted trusted-lab model](SOURCE_POLICY_MINIMUM_TRUSTED_LAB_REPLAN.md)
trusts CI/kernel/engine/distribution behavior; it is not adversarial-host or
measured-binary attestation. The
[complete-ABI obligation](SOURCE_POLICY_UBI_MOBY_POLICY_PRESERVATION_DESIGN.md#abi-coverage-and-non-vacuous-future-controls)
still requires real controls for callable mapped ABIs and independently qualified
non-callability evidence for the others. Independent means independent of the
canary's unavailable result, not proof against the already trusted host lying.
This review does NOT replace that obligation with ENOSYS, a version string,
a config-file name, a copied receipt or a default-ready boolean.

## Pinned upstream findings; not Azure build attestation

Public Linux tagv7.0 resolves through annotated tag3131ff5a117498bb4b9db3a238bb311cbf8383ce
to commit **028ef9c96e96197026887c0f092424679298aae8**. Five exact files
are read and SHA256-hashed in memory; no upstream program is executed:

| Upstream source at that commit | Bytes | SHA256 |
|---|---:|---|
| [arch/x86/entry/syscall_64.c](https://github.com/torvalds/linux/blob/028ef9c96e96197026887c0f092424679298aae8/arch/x86/entry/syscall_64.c) | 3787 | `b97e396c4816ed2b8256957861bc976eb25c4e6953f66c97af7bd1383c2b612b` |
| [kernel/configs.c](https://github.com/torvalds/linux/blob/028ef9c96e96197026887c0f092424679298aae8/kernel/configs.c) | 2054 | `c355fb8c6968035fe662aa6105d4cbfb68ff39528b2b1a6b8178c13d5b38bd8a` |
| [kernel/Makefile](https://github.com/torvalds/linux/blob/028ef9c96e96197026887c0f092424679298aae8/kernel/Makefile) | 7077 | `18ab5bdeb31f60dd99554e807be2042b2733451d3bd10b74c1e7ea8603c5eeda` |
| [init/Kconfig](https://github.com/torvalds/linux/blob/028ef9c96e96197026887c0f092424679298aae8/init/Kconfig) | 73556 | `84af04936ab66eca0a47c59b56cacbf4ff4bf19a6de7f40a9d64dbbaac744c8d` |
| [arch/x86/Kconfig](https://github.com/torvalds/linux/blob/028ef9c96e96197026887c0f092424679298aae8/arch/x86/Kconfig) | 107934 | `9d9b3343a356487b1e0f68cb0f4b89e95d76b4c6a6fcdfafcb1a65e3ecdf7cf1` |

- syscall_64.c compiles the x32 handler conditionally and its dispatch checks
  CONFIG_X86_X32_ABI. Together with the reviewed number-bit/range behavior this
  explains why qualified evidence of a compiled-out x32 path can be relevant.
  This upstream tag is NOT proof of the running Azure vendor build/patch set.
- init/Kconfig makes IKCONFIG tristate and IKCONFIG_PROC its proc access option.
  kernel/configs.c exports embedded gzip configuration through /proc/config.gz.
  kernel/Makefile connects that payload/configs.o to the build configuration.
  Thus the proc interface is a candidate existing evidence source, not an
  assertion that this runner exposes it or that every such file is built in.
- IKCONFIG=m can expose a module's embedded payload. A present proc filename,
  successful gzip decoding or self-reported X32=n is not enough. Require a
  qualified proc origin and built-in IKCONFIG=y/IKCONFIG_PROC=y for this proposed
  minimal route; module/unknown/mismatched origins remain insufficient.
- The current owner records a syntactically bounded release string; that regex
  is not a reviewed kernel-family/minimum-version admission check. The actual
  observed7.0 release is not changed, but any future evidence consumer must
  independently admit its reviewed source family/build semantics. No new
  runtime guard or inference is silently added by this document.

## Smallest selected next step; no grant from an availability audit

Recommend ONE bounded read-only audit on the same existing trusted
ubuntu-24.04/x86_64 GitHub-hosted profile, with fresh exact-head/run/attempt/source
binding, no installs, sudo, module loading, kernel changes or runner hopping.

The audit should determine whether already exposed /proc/config.gz is usable:
check its genuine proc origin without bind-file/symlink substitution, read only
that exact file in bounded memory and emit only presence/origin/validation
categories, byte counts/hashes and the allowlisted IKCONFIG/IKCONFIG_PROC/
X86_X32_ABI declarations. If mount provenance must be inspected, read only
bounded /proc/self/mountinfo; never publish its raw paths/content.
No environment, command line, credential, daemon log, arbitrary path or recursive
filesystem search is in scope. Use existing Python/standard libraries only;
do not build a generic host-attestation collector or add dependencies.

Proposed standalone caps: compressed input<=64KiB, expanded config<=256KiB,
optional mount metadata<=64KiB; require complete EOF, one valid gzip member/
CRC, no trailing data, unique exact declarations and existing absolute time/
memory/output/cleanup reserves. Charge all reads/decoding to the remaining
global budgets; these caps are NOT additive new allowances. Missing, inaccessible,
oversized, partial, duplicate, module-backed or unqualified-source evidence is
an honest unavailable/insufficient result, never permission to load a module
or pick another file. Emit no raw compressed/decompressed configuration or
mount dump, and create no artifacts. This is a proposed finite audit scope,
not implemented reader code or evidence that all provenance checks are feasible.

An audit positive means only that a candidate evidence channel is available
on THAT fresh host. It still requires source/build semantic correspondence
under the existing trusted-host contract and a separately approved same-job
integration before it could qualify non-callability. A fresh
[GitHub-hosted job uses a fresh runner instance](https://docs.github.com/en/actions/how-tos/write-workflows/choose-where-workflows-run/choose-the-runner-for-a-job);
an audit on another job cannot retroactively qualify run41856008408. Later
admission would need fresh bound amd64/i386 controls and contemporaneous
qualified x32 evidence in one owner/job, with shared budgets and unchanged
complete-ABI criteria. No old receipt is promoted by this design.

If the finite existing-interface audit is unavailable or insufficient, STOP
this lane and report the concrete evidence gap. Do not escalate into kernel
rebuilds, privileged/BPF observers, arbitrary syscalls, installations, alternate
runners, security-policy changes or repeated unavailable probes. That would
require a new explicit product/operations decision, not routine continuation.

Even a future legitimate x32 closure would not authorize candidate version
execution, legacy minimum SQL qualification, production connection/form wiring,
release or deployment. Those existing gates remain separate.

## Validation, progress and next gate

This decision and directly owning resume records are the only changes.
No scripts/tests/workflow, source identities, qualification flags, accepted
architecture mappings or runtime behavior change. Existing isolated checks,
local document links, differential scope review and ordinary exact-head CI
validate publication only, not proc availability, kernel provenance or x32
non-callability. No whole-plugin review/release-readiness row is refreshed.

Local publication validation passes the existing isolated PHPUnit bridge
**3 tests/12 assertions** (23/15/12 Python groups), verifies259 local file links
across the five changed documents and passes differential/whitespace review.
Required evidence/limit wording is checked separately, not counted as new tests
or kernel proof. Ordinary exact-head CI must be verified after publication.

Implementation-plan progress: the evidence gap and smallest bounded feasibility
step are now explicit; candidate admission remains closed. Campaign milestones
**8/10**, static9 and **14 consumed native attempts** are unchanged, with ZERO
new native attempts/physical evidence in this task. Uploader/toolkit and all
installed/live state remain untouched.

**Recommended next gated slice: One Existing-Runner Read-Only x32 Kernel-Evidence
Feasibility Audit**, including only its bounded existing-tool diagnostic setup,
isolated regressions, validation/publication/CI and one read-only runner job.
No canary/candidate execution, Docker/SQL/site operation, module load, acceptance
change or new attestation tooling. Ask for that exact protected-read/runner-job
authority; do not execute it under this repository-only design approval.
