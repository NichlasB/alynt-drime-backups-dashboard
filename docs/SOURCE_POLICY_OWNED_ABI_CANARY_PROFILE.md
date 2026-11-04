# Owned Native ABI Canary Execution Profile

## One-time private startup diagnosis: logging configuration cause established — 2026-11-04

The user explicitly approves a dedicated private diagnostic repository, narrowly
bounded private error disclosure and ONE fresh owned reproduction/cleanup run.
This supersedes the proposed RSA-key setup as the immediate next action; it
does not authorize implementation of the logging repair or a second native run.
No risk-tier increase or privacy exception for public raw logs is inferred.

Public restore baseline **cce28f456874d557f19891bcde73b69701920a8e** is clean and
matches the freshly queried remote master before this evidence closeout.
The dedicated repository `NichlasB/alynt-dashboard-private-canary-20261104`
is API-verified private before publication, dispatch and closeout; only NichlasB
is listed as a collaborator/admin, with zero Actions secrets/variables. It is not
a fork/release/site, and public repository visibility/settings are unchanged.
Logs remain private to GitHub/authorized readers; no log/repository deletion or
secure-erasure claim is made. No ciphertext, key or crypto process is involved.

### Source review, meaningful tests and exact native binding

Private source head **ecd8e5ddecbf6b6f3aaded61dff1750d100c4bcb** is committed,
pushed and verified against its remote. SOURCE_PROVENANCE.json records the
original public head and LF-content SHA256 for every copied input. Eleven of
thirteen copied files are identical; only the workflow/controller differ.
AST comparison proves all fifteen unaffected pre-existing Owner methods are
unchanged, including run, prerequisites, build,
probe, profile readback and cleanup. The two existing-method changes add private
admission/capture, not an admission bypass. Two new private methods validate
the existing complete failure/source packets and publish only after primary
owned cleanup; original nonzero failure and all resource guards are preserved.

The workflow binds the exact private repository/master/head, owner actor,
attempt1, private event metadata and explicit default-off input. An actual
GitHub API privacy check precedes any native work; its read-only token is confined
to that step and not inherited by the minimal command environment. Only the
first amd64 builtin probe is selected; no derived/i386/x32 probe follows even
on success, and qualification stays false.

Local and exact private-run isolated tests pass **58 groups: 21 original,
15 startup,12 source-envelope and10 private-disclosure**, with no failures,
errors or skips; the first three suites report zero network calls. New tests
include actual admitted-constructor/fake-command and useful disclosure positives,
public/wrong-run/selector/hash/size/UTF8/quota/cleanup negatives, preserved original
refusal and no early output, one-probe/no-grant behavior, and three assertion-
killed authority/hash/cleanup bypass controls. These are synthetic API/IO tests,
not physical ABI qualification. An initial local sandbox temp-directory
permission failure is resolved by running the SAME unchanged tests with approved
filesystem access; no guard or test is relaxed.

[Private run41853335409](https://github.com/NichlasB/alynt-dashboard-private-canary-20261104/actions/runs/41853335409)
at that exact head, attempt1/job126655235505, is dispatched **15:00:52 UTC**.
The API privacy step and all isolated regressions pass. Native audit/probe runs
**15:01:03–15:01:09 UTC** and retains the original failed start/attach status.
The first amd64 builtin command returns CLI1, stdout0, complete stderr157 bytes.
The original and supplemental packets validate; the source envelope is unmatched.
The separately bounded private payload matches the same frame/binding/hash and
contains only a reviewed non-secret Docker logging-configuration error.

| Receipt | Verified value |
|---|---|
| Private source cohort | `ce22c0d8551bd3f23494b8006c0c9a7715fddf4a6d2bd8e4c6b1a7332239b05d` |
| Complete stderr SHA256 | `80a868096821299612792f8e0917bc2abf47d10457d368b829edc8e23bdf720e` |
| Primary cleanup | containers0,images0,stage_absent=true |
| Separate always-run cleanup | success15:01:10 UTC; containers0,images0,stage_absent=true |
| Observed output | primary50,447 bytes; independent41; native180 includes23 host +157 CLI error |
| Uploaded artifacts | API0 |
| Qualification/candidate/SQL/production wiring | all false |

The stderr length/hash are identical to the earlier public failures. This is
actual observed-byte correlation, not a reconstruction from an old hash.
The private host reports the same pinned Docker28.0.4/client commits,
runc1.5.2/libseccomp2.5.5 and kernel7.0.0-1017-azure. That agreement does NOT
make a private runner identical to the original public runner or transfer
physical qualification. The job conclusion remains **failure**; diagnostic
success and independent cleanup do not make the native check green.

### Established cause and smallest proposed correction

The existing controller explicitly requests local log driver, max-size16k and
max-file1, but omits compress. [Pinned Moby local.go](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/daemon/logger/local/local.go)
defines compression true by default and parses an explicit compress option.
[Pinned config.go](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/daemon/logger/local/config.go)
rejects compression with file count <=1. Its inspected raw SHA256 is
`720776aab42ed5542876ff8f05afe878ab737f3b45940c00dfdf440b7d0b8407`.
[Docker documentation](https://docs.docker.com/engine/logging/drivers/local/)
also documents compress=false and the enabled default. The reviewed private
error identifies this exact log-driver initialization conflict, not an
AppArmor/seccomp/ELF or dashboard SQL failure.

Recommend explicit **--log-opt compress=false**, retaining local/max-size16k/
max-file1, rather than increasing file count, removing logging or relaxing
security. The authoritative exact LogConfig readback, separate diagnostic
comparison and source-backed positive/negative tests must be updated together:
reject absent/true compression, extra log options, altered size/count and guard
bypasses. This is a proposed harness-only correction, NOT implemented or
physically verified here. Do not change daemon defaults or installed services.

Implementation-plan progress: startup cause is now evidence-backed; minimum
profile/ABI qualification and source-policy production wiring are still blocked.
Campaign product milestones **8/10**, static9 unchanged; twelve prior native
attempts plus this separately authorized **1/1 private diagnostic** are consumed
(**13 total**). No product milestone credit for diagnostics or evidence commits.
No candidate software, SQL, WordPress/site, installation, schedule, permission,
release/package/deployment or production operation occurred.

Evidence-only public closeout validation passes the existing isolated PHPUnit
bridge **3 tests/12 assertions** (21/15/12 original Python groups), verifies
all245 local file links in the three changed owning documents and passes Git
diff/whitespace review. Independent local hashing reproduces the private run's
actual nine-file source cohort. Only these three evidence/plan documents change;
uploader/toolkit and both public/private runtime source trees remain unchanged
during closeout. Ordinary exact-head CI must be verified after this docs-only
publication; it cannot turn the failed private native check into a success.

**Recommended next gated slice: Harness-Only Explicit Local-Log Compression
Compatibility Repair and One Fresh Owned ABI Qualification/Cleanup Proof.**
Approve the narrow controller/exact-readback/regression correction, ordinary
validation/publication/exact-head CI, then ONE original public-workflow attempt
after fresh prerequisites and cleanup verification. Keep raw disclosure disabled,
all containment/budgets/candidate/SQL/production boundaries intact; no further
RSA setup, catalog growth or unchanged retry is recommended. This approval
has not yet been granted.


## One-time disclosure/retention design: no capture admitted — 2026-11-03

The approved [decision](SOURCE_POLICY_ONE_TIME_DISCLOSURE_DECISION.md) records the
public-repository exposure boundary and selects conditional recipient encryption,
not plaintext logging, artifacts or more guessed source templates. Proposed
disclosure is complete bounded stderr only, offline owner review, no private key
in CI/agent, and separately approved exact-run log cleanup with explicit retention
limits. No implementation, cryptographic process, new read, key/permission/
retention change, log deletion or native attempt occurs. Key/tool/privacy/cleanup
and new-attempt admissions remain unresolved. Cause/ABI qualification, campaign8/10,
static9 and12 consumed attempts unchanged. Next action is owner-provided purpose-
specific RSA4096 public SPKI key/fingerprint approval; no private key or capture.

## Native source-envelope discrimination: unmatched and owned cleanup verified — 2026-11-03

The user approves one bounded proof at
**b0cb9b960f56a6b9b10a0fe4a0c77171e009e497**, not a new campaign or limit reset.
This is the exact narrowly scoped R3 owned-lab authority; no SQL, installed site,
candidate execution, installation, new protected read, raw disclosure, production,
release or profile/acceptance/budget change is authorized. The head is clean/
synced and [ordinary CI41726295847](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41726295847)
is reverified successful. Fresh isolated preflight passes3 PHP tests/12 assertions
(21 original,15 startup,12 source-envelope groups) before the single dispatch.

[Native run41726834197](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41726834197),
attempt1/job126233752569, runs its audit/proof step **22:00:15–22:00:16 UTC**;
job concludes failure at22:00:19. Fresh pinned engine/client28.0.4,
runc1.5.2/libseccomp2.5.5, compiler and kernel7.0.0-1017-azure prerequisites pass.
Created/pre-start containment and copied-executable hashes pass. The first amd64
builtin start/attach returns CLI1 with complete stdout0/stderr157 bytes, raising
the unchanged bounded-command-failed refusal. No accepted syscall observation,
derived-policy/i386/x32 container control or ABI qualification follows.

The real ADBOWNEDABISTARTFAIL1 and ADBOWNEDABISOURCEENV1 packets both pass strict
DATA validation against their actual operation binding. Independent read-only
hashing of the exact nine Git blobs reproduces the emitted source cohort. The
supplement's operation digest, cached five-recipe catalog digest and complete
stderr digest all correlate. Its status is **unmatched**, family **null**,
authority/qualification false. The original analysis remains daemon-envelope,
unclassified/unknown. This proves transmission of the actual bounded unmatched
observation, not a synthetic positive or successful classification/ABI control.
It rules out an exact match under the five frozen byte/owned-CID recipes only;
it does not establish which daemon/API/runtime path or cause occurred.

| Actual binding/fact | Value |
|---|---|
| Source cohort |`f84d53e800695448937b2587676b3a7fc8a7d7dcf17f8b984fea225c08ae083a`|
| Context hash |`b8a7fe87ad9ef26c058afab887ed6a772fd92b9771e06b9e8b81853a1bac4089`|
| Owned image |`sha256:05dc2db423cc5f69686cd9666ae6da5f13b12976d85288a4459d359564f0f408`|
| Owned CID |`b1e609baf4317994f2b9f370a7b0eae36bbe74cc51b3093369c74471ec9b1ce4`|
| Supplemental operation hash |`f148ee30749a476edc60acb2a5830c37deb362c19bdbf1f3c7d27fbcc580a5f1`|
| Catalog hash |`c420258fa529bab9bdeb2bc2ab701accba01d7303f7fe1233efab3a246be1551`|
| Complete stderr hash |`80a868096821299612792f8e0917bc2abf47d10457d368b829edc8e23bdf720e`|
| Canary/host/policy hashes |same three exact byte hashes as the preceding actual proof |
| Harmless host only |UID1001/NNP0/seccomp0;amd64/i386 getpid2472;x32 getpid-38 |
| Accounting |primary49,416;native180 (23 host +157 CLI stderr);independent cleanup41 |

The first existing owned cleanup readback is **created/Runningfalse/OOMfalse/
exit null**. This later snapshot is not proof of process non-execution or a
default-zero exit receipt. Primary cleanup and separate always-run cleanup both
report **containers0/images0/stage_absent=true**; independent cleanup succeeds
at22:00:16. Uploaded artifacts API reports0. No cleanup/diagnostic-unavailable
flag is emitted. Raw text remains unavailable; neither this unchanged digest/
length nor candidate hash guesses may reconstruct it. All SQL/candidate/
production/qualification flags remain false. The proof head has successful
ordinary CI and a **failed native check**; never call all checks green.

Implementation-plan progress: real source/catalog/stream correlation and the
unmatched outcome are verified, but cause/ABI coverage remain unestablished.
Campaign8/10 milestones,static9 unchanged. Original native10/10 plus first
additional1/1 and this additional1/1 are consumed (**12 total**). The explicit
unmatched stop condition is now reached: **no catalog growth or blind retry**.
Do not interpret a successful diagnostic DATA validation or verified cleanup as
a product milestone, native qualification success or permission to continue.

**Recommended next gated slice: Design-Only One-Time Human-Supervised Startup
Error Disclosure and Retention Decision**. The machine-only catalog has not
identified the cause. Evaluate the minimum human-readable evidence from the same
bounded, minimal-environment owned command buffer, with explicit privacy,
exposure/retention and exact cleanup controls. This is a new privacy/operational
decision, not more guessed templates. No raw capture, extra field/log/API read,
implementation or native attempt is authorized by the proposed design approval.
Closeout edits owning records only; validation, commit/push and ordinary exact-
head CI must complete before handoff. Uploader/toolkit and application state stay
untouched; no whole-plugin release-readiness evidence is refreshed.

## Source-envelope coverage foundation: no native execution — 2026-11-03

The approved [source-envelope foundation](SOURCE_POLICY_UNCLASSIFIED_STARTUP_DIAGNOSTIC_DESIGN.md#approved-isolated-safety-foundation-and-dormant-integration--2026-11-03)
implements the five frozen recipes, strict independent DATA binding and dormant
first-attempt integration. Twelve new isolated groups/five semantic controls pass,
with the original21/15 groups and unchanged parent parser/runner/cleanup guards.
No native C/workflow/cohort/SQL/candidate/profile/budget/production change or
historical-message reconstruction occurs. Cause/ABI coverage remain unknown;
campaign8/10,static9 and 11 consumed attempts are unchanged. Next gated slice is
one explicitly additional native source-envelope discrimination/cleanup proof
at the published head after CI, targeting actual stream/source correlation and
matched family versus unmatched—not a presumed fix. No allowance is granted here;
an unmatched outcome requires stopping further blind retries/catalog growth.

## Unclassified daemon coverage and privacy-safe recovery design — 2026-11-03

The approved [design](SOURCE_POLICY_UNCLASSIFIED_STARTUP_DIAGNOSTIC_DESIGN.md)
records pinned CLI/API error-source discovery and selects five exact envelope
recipes with enum-only, separately bound supplemental DATA. It preserves the
current unknown historical receipt and requires useful synthetic positives plus
source-backed guard controls before any future physical proposal. No implementation,
raw disclosure, new protected read, command, profile/acceptance/budget change or
native run occurs here. Cause/ABI coverage remain unestablished; campaign8/10,
static9 and 11 consumed attempts are unchanged. Next gated slice is the combined
repository-only safety foundation/dormant integration with isolated tests only,
not another native attempt or a renewal of the exhausted allowance.

## Additional authorized startup-diagnostic proof: unclassified daemon message — 2026-11-03

The user approves **one** additional bounded native qualification/cleanup proof
at **c2e2b4a4bdcb110cf148d934903d6f77d5faa58d**. This is narrowly scoped R3 owned
ephemeral-lab authority, not a risk-tier increase, new campaign or attempt reset.
The head is clean/synced and its [ordinary CI41716530416](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41716530416)
is reverified successful before dispatch. Fresh isolated preflight passes2 PHP
tests/8 assertions, covering the original21 and new15 Python groups. No installed
site, SQL, candidate execution, installation, production wiring or policy change
is authorized. Uploader/toolkit remain clean and untouched.

One dispatch produces [native run41719960658](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41719960658),
attempt1/job126209816181. Fresh audit and native step run **21:14:34–21:14:43 UTC**;
the job concludes failure at21:14:45. The pinned engine/client28.0.4, runc1.5.2,
libseccomp2.5.5, existing compiler and kernel7.0.0-1017-azure prerequisites pass.
Created/pre-start containment and copied-executable bytes pass before the first
amd64 builtin `docker start --attach` returns CLI1 with stdout0/stderr157 bytes.
No derived-policy/i386/x32 container control or accepted syscall observation runs.

The new **actual** ADBOWNEDABISTARTFAIL1 packet passes the current strict DATA
validator against its owner, image, CID, ABI/policy and context. Independent
read-only computation over the exact nine Git blobs matches the emitted source
cohort. Both streams are complete; stderr SHA256 is
`80a868096821299612792f8e0917bc2abf47d10457d368b829edc8e23bdf720e`.
Only the daemon envelope is recognized: analysis **unclassified**, layers
`[docker-daemon]`, topics`[unknown]`. No matching nested layer/topic, root cause or
specific daemon failure is established. Raw text remains withheld; do not derive
or reconstruct a message from the unchanged length/hash.

The first already-existing owned cleanup readback projects **created**, Running
false, OOMfalse and **exit_code=null**, with basis first-owned-cleanup-readback-
after-failure. This is later cleanup-time state, not proof that the process never
started or a default-zero process-exit receipt. CLI-spawned true confirms only the
CLI child. All diagnostic/proof authority, qualification, SQL, candidate and
production flags remain false; no ABI coverage is admitted.

| Actual binding | Value |
|---|---|
| Source cohort |`d4bc44507dd9bfab73aaf244ab419d7df55b660303340a19ee044a08e6136615`|
| Context hash |`54cf5fb2418233c258ca5a94a6ef60fe88e5c7a71c21ec4be53e158cf2188b7b`|
| Owned image |`sha256:0899c07412581321b952654839e032fa09690121acd10722ae57d2f7a22a5df6`|
| Owned CID |`a33fa78ba20332448ca0d65e2d43381f38cc2de6ccbed3d0d89a820af6c74544`|
| Canary SHA256 |`4e27a6aa75b0e47f5818e040f4f98b91632e0a603ca216af38cfcbd1c4b051ee`|
| Host SHA256 |`3a51b40bead7f3c2f9e87c13197e6b522caf5b124a16b5ec24e3998f3bbfc06a`|
| Derived policy SHA256 |`3277296f5e2fa40d0e53fe3d2925e390174f3fcfc9ec2adfba8fd63852984fe4`|
| Host only |UID1001/NNP0/seccomp0; amd64/i386 getpid2291; x32 getpid-38 |
| Accounting |primary49,036 bytes; native180 (23 host +157 CLI stderr); independent cleanup41 |

Primary cleanup and the separate always-run cleanup both report
**containers0/images0/stage_absent=true**; independent cleanup succeeds at21:14:43.
Actions artifacts API confirms0 uploaded artifacts. No cleanup issue or
diagnostic-unavailable flag is emitted. The actual diagnostic reuses the existing
cleanup read, with no new inspect/log/exec call. Native C, workflow, original
readback/acceptance, source cohort, quotas and SQL/candidate/production paths are
unchanged. The exact proof head has successful ordinary checks **and a failed
native check**; do not report all checks green or whole-plugin readiness.

Implementation-plan progress: native first-failure/cleanup diagnostics are now
observed, not merely modeled; actionable startup cause and ABI qualification
remain unresolved. Campaign **8/10 milestones,static9** unchanged. Original
native10/10 plus separately approved additional1/1 are consumed (11 total);
no second attempt, retry or source-policy widening is authorized.
**Recommended next gated slice: Repository-Only Unclassified Daemon Startup
Diagnostic Coverage and Privacy-Safe Recovery Design (No Native Rerun)**. Review
the finite catalog and minimum safe evidence needed for this actual unknown
envelope; do not hash-guess the message, log raw text, relax containment or retry
without new explicit authority. This closeout changes owning records only;
validation, commit/push and ordinary exact-head CI precede handoff.

## Startup evidence foundation: isolated implementation only — 2026-11-03

The approved [foundation and dormant integration](SOURCE_POLICY_OWNED_ABI_STARTUP_FAILURE_FOUNDATION.md)
adds a first-failure operation/source-bound enum-only diagnostic and optional state
from the existing first owned cleanup row. Fifteen isolated groups, the original
21 and six source-backed controls validate capture/privacy/budgets/rejection and
cleanup without engine execution. Workflow, original readback/acceptance, native C,
SQL/candidate and production paths remain unchanged. Historical startup cause is
still unknown; no new native or ABI result is claimed. Campaign8/10 milestones,
native10/10,static9 unchanged. The exact next gate is one newly explicitly
authorized bounded startup-diagnostic qualification/cleanup attempt; no dispatch
is authorized by this foundation or ordinary CI.
Implementation fbcba446884ab54c8505c45b4c4257bb8cf434e7 passes
[ordinary CI41716099147](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41716099147):
PHP7.4 2,611/23,468 assertions, PHP8.3 2,611/23,473; Node88/88 each, no
failures/skips and zero advisories. This is isolated source validation, not a new
physical attempt or successful native check.

## Startup-failure evidence/recovery design approved — 2026-11-03

The [repository-only design](SOURCE_POLICY_OWNED_ABI_STARTUP_FAILURE_DESIGN.md)
binds finite non-authoritative diagnostics to the actual fixed CLI operation,
preserves primary failure/budgets, and projects optional later state only from
an already-existing owned cleanup row. No implementation, new inspect/exec/log
read, raw-message retention, acceptance change or native attempt occurs here.
Read-only pinned CLI source confirms attach/inspect can precede daemon start;
status1 alone does not establish startup stage or container exit. Historical
stderr remains unavailable; no cause is reconstructed from its length/hash.
Campaign8/10 milestones,native10/10,static9 are not reset or incremented.
Next gated slice: the design's combined **repository-only safety foundation and
dormant harness integration**, isolated tests only and no native rerun. A later
physical proof still needs separately renewed bounded native authority.

## Final approved attempt: compatibility verified; startup fails; ceiling reached — 2026-11-03

Repair **9425c79110111d0243e727297c6e235bf1ba9333** has successful
[ordinary CI41700628672](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41700628672):
PHP7.4 **2,610/23,464 assertions**, PHP8.3 **2,610/23,469**, Node88/88 each;
no failures/skips and zero Composer/npm advisories. Local21 isolated groups,
bridge1/4 and full local2,610/23,462 pass (two existing local Sodium skips).

The one approved final [native run41701111079](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41701111079),
attempt1/job126144319258, fails **2026-11-03 19:12:53–19:12:55 UTC**. Both repaired
created/pre-start profile checks now pass in the real engine, as do source/state
and copied-executable hash readbacks. There is no profile mismatch diagnostic.
The first amd64 builtin control reaches **docker start --attach**, which returns
CLI status1 and157 stderr bytes, then raises bounded-command-failed. No container
syscall observation is admitted; no derived-policy/i386/x32 container control runs.

The stderr SHA256 is
`80a868096821299612792f8e0917bc2abf47d10457d368b829edc8e23bdf720e`.
Raw text was deliberately not retained by the existing bounded runner. The exact
daemon/runc/attach failure category therefore remains **unestablished**; do not
guess a missing dependency, exec format, AppArmor, seccomp or namespace cause.
No post-start state/exit/loaded-policy receipt was reached before cleanup, so do
not claim either successful startup or that no process could have started.
Native-output accounting180 includes23 harmless host bytes plus157 CLI stderr
bytes, **not180 bytes of container syscall evidence**.

| Actual binding/fact | Value |
|---|---|
| Source cohort |`999cda99026027fb9ca4ea733210847280702e6650031e0788560a79cf620e33`|
| Owned image |`sha256:87116c3e6c9f9bfbdd6ae826ae25125bdb88aacb11d0bd325528db78ca666585`|
| Canary/host/policy hashes |same exact three hashes as preceding attempts |
| Runtime/tools/kernel |same pinned reported versions/tool hashes/kernel/config as preceding attempt |
| Harmless host only |UID1001,NNP0,seccomp0;amd64/i386 getpid2287;x32 getpid-38 |
| Observed bytes |48,089 primary;41 independent cleanup |

Primary cleanup removes the proven owned container, imported image and staging
files and reports **containers0/images0/stage_absent=true**. The separate always-
run cleanup succeeds at19:12:55 UTC and independently confirms the same absence.
Direct owned command children return and are reaped; Actions artifacts API0,
no retained fixture files. No installation, candidate execution, SQL, installed-
site, release or production operation occurred. SQL guards/budgets, native C and
workflow are unchanged. Exact repair head has two successful ordinary checks,
one failed native check, legacy status contexts0. Do not call all checks green.

Implementation-plan progress: canonical namespace and explicit default-AppArmor
compatibility are physically verified through pre-start readback; startup/syscall
qualification is still blocked. Campaign remains **8/10 completed milestones,
native10/10,static9**. No milestone credit for repair/retry/docs. The authorized
native ceiling is exhausted; no rerun, changed workflow dispatch, alternative lab
or implicit reset is allowed. Only this owning evidence closeout and ordinary
exact-head publication verification continue under the current approval.

**Recommended next gated slice: Repository-Only Bounded Owned-Canary Startup-
Failure Evidence and Recovery Design (No Native Rerun).** Define finite, non-secret
operation/failure-category diagnostics that preserve the first rejection and
existing budgets, with source-backed synthetic regressions and no acceptance
changes. Do not infer the lost stderr or extend execution authority. Any later
physical attempt needs an explicit new campaign/attempt allowance after that
evidence contract is reviewed; it is not authorized by this recommendation.

## Approved canonical UTS / explicit default-AppArmor repair — 2026-11-03

User approval covers the identified fixed-profile compatibility repair and
**one** fresh owned proof, campaign native attempt10/10. Restore point:
clean/synced `42416634a87fe60c563f1ef4f80223102ac4a90b`, ordinary CI41684223198
success. Campaign8/10 milestones,native9/10,static9 before dispatch. No automatic
limit reset/extension, installation, candidate execution, SQL, site operation or
production wiring; SQL lab guards and all resource/output/deadline budgets stay.

Read the matching official Moby6430e49 source again: canonical HostConfig.UTSMode,
explicit AppArmor option parsing and conditional default OCI AppArmor selection.
Repair the two approved admission comparisons only: require the canonical
namespace key's exact empty/private value, and exactly NNP + selected seccomp +
`apparmor=docker-default`. The existing root AppArmorProfile requirement remains
strictly docker-default. Constructor now explicitly requests that existing
default; no custom/unconfined selection or host-policy management path is added.
Prerequisites additionally require engine-advertised AppArmor before owned work.
Missing support refuses; there is no fallback or permission increase.

New guard SHA256: `b4ae3f201336ee2b497251d352d3d35f2ad1e4ff1a1cebf0c687db1307889747`.
Tests reverse **only those two source edits** and require the preceding guard's
exact d2bf7e5 source hash, proving every other acceptance clause is preserved.
The non-authoritative diagnostics mirror the canonical key/options and continue
to preserve first exception/report, redaction and existing-budget accounting.
Native C bytes, syscall expectations, workflow and cleanup logic are unchanged.

The test-only `owned-abi-readback-scope.json` records a source-specified wire-format
subset, explicitly **not a captured inspect response or native proof**. It
replaces the wrong-case assumption, with positive explicit-default and rejected
implicit-default cases. Twenty-one isolated groups pass after a red run against
the old construction/guard: canonical key, missing/host/malformed namespace,
wrong-case fallback, empty/custom/unconfined AppArmor, missing/duplicate/extra
security options, both seccomp selectors, actual constructor argv and refusal of
unsupported AppArmor before further tools. Previous semantic controls remain.

Focused source review confirms no arbitrary profile/argv, security relaxation,
new dependency, credential/environment disclosure or broadened cleanup. This is
approved narrow R3 disposable CI-lab scope, not whole-plugin release readiness
or an installed-site/SQL proof. Validate/publish/verify ordinary exact-head CI
before the single ordered attempt; report real native outcomes and independent
cleanup without further dispatch once the10/10 ceiling is consumed.

Local checks: isolated21/21, PHP bridge1/4, full PHP2,610/23,462 with two existing
Sodium skips; Node88/88; PHPCS255 files; structure/build, Python syntax, strict
Composer validation, Composer/npm audits and diff checks pass, zero advisories.

## Fresh diagnostic attempt: readback-model blockers identified — 2026-11-03

Published diagnostics **c24226c1a598a3b0c209ddc726c91780f988c4a7** have successful
[ordinary CI41683281342](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41683281342):
PHP7.4 **2,610/23,464**, PHP8.3 **2,610/23,469**, Node88/88 each; no failures/skips,
zero Composer/npm advisories. The authoritative guard remains byte-identical.

The single approved fresh [native run41683627430](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41683627430),
attempt1/job126083471886, fails **2026-11-03 17:22:39–17:22:40 UTC** at
`amd64-builtin / created / container-command`. Its bounded first report contains
exactly **root.AppArmorProfile** and **HostConfig.UtsMode** as mismatches;
AppArmor category=empty,environment=empty,default_path_only=false,matched=false,
authority=false. All other compared fields match. The original exception is
preserved. No container start, network probe, derived-profile application or
container ABI qualification occurs; the host observer alone emits23 native bytes.

| Actual binding/fact | Value |
|---|---|
| Source cohort |`2565ee38d0155103e94dbea8b74a7a10ec139036cfa445a1735dc275de43d973`|
| Owned image |`sha256:0c3c2a972fa885dc34983f01397c9f8a0938a5a76d2cdb13f7b235339c6d9a31`|
| Compiled canary/host and policy |same exact three hashes as preceding attempt |
| Runtime/compiler/kernel |same pinned runtime/tool hashes and kernel/config as preceding attempt |
| Harmless host facts |UID1001,NNP0,seccomp0;amd64/i386 getpid2464;x32 getpid-38 |
| Total observed output |39,636 bytes; independent cleanup41 bytes |

Primary cleanup removes the owned created-but-never-started container, imported
image and staging files, reporting containers0/images0/stage_absent=true. The
separate always-run cleanup succeeds at17:22:41 UTC and independently confirms
the same absence. Actions artifacts API0; no retained fixture files; owned direct
command children returned and were reaped. No
installation, candidate execution, SQL, LocalWP/site or production action. No
second fresh attempt was dispatched under this approval. Exact diagnostic head
has two successful ordinary checks, one failed native check, legacy contexts0.

### Matching source interpretation, not a relaxed acceptance rule

Read-only review of official Moby at6430e49a55babd9b8f4d08e70ecb2b68900770fe finds:

- [HostConfig declares **UTSMode**](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/api/types/container/hostconfig.go),
  not UtsMode. The existing guard and synthetic fixture share a wrong-case key.
  The diagnostic identifies that lookup failure; it does not retain or prove
  the actual canonical UTSMode value. Do not call this a host-namespace violation.
- [parseSecurityOpt](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/daemon/daemon_unix.go)
  assigns an explicit AppArmor request when present. [WithApparmor](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/daemon/oci_linux.go)
  chooses the default for an empty, nonprivileged request at OCI construction.
  Empty request metadata before startup is therefore not proof of an unconfined
  running process. No process was started to establish loaded LSM enforcement.

The19 isolated groups prove diagnostic/guard agreement, not that Docker emits
the synthetic positive fixture. Real native evidence now exposes that shared
model assumption. Do not silently accept empty AppArmor or missing namespace
metadata, edit the pinned guard merely to go green, or infer all-ABI eligibility.

**Recommended next gated slice: Owned Canary Canonical UTS Readback and Explicit
Default-AppArmor Compatibility Repair, with One Fresh Qualification/Cleanup
Attempt.** Correct the canonical key and require its private value, explicitly
request the existing docker-default AppArmor profile, and match exactly the
three intended security options (NNP, selected seccomp, fixed default AppArmor).
Reject unconfined/custom/extra options, missing/host UTS and malformed data; use
source-backed genuine-format regressions, then ordinary exact-head CI, fresh
prerequisites and one approved attempt. This changes the fixed canary admission/
construction contract, beyond this guard-preserving diagnostics approval; it
does not authorize installation or changing a host policy. Preserve SQL lab
guards, budgets and disabled candidate/production paths. A fresh attempt would
consume native10/10; do not reset or extend the campaign limit implicitly.

Implementation-plan progress: missing-field observability is resolved, and two
concrete compatibility defects are identified. Physical qualification remains
incomplete; campaign **8/10 milestones,native9/10,static9**, with no retry/docs
product credit. Closeout changes only owning records; final docs exact-head CI
must pass before handoff, without another native operation.

## Approved guard-preserving diagnostics and one fresh attempt — 2026-11-03

The user approves recovery from the recorded pre-start refusal: add bounded
per-field diagnostics, validate/publish/exact-head CI, then **one** fresh owned
qualification/cleanup attempt. Restore point is clean/synced
`0528ffdc68a319bbd0c62b9805d102345d90b11b`, ordinary CI41676498861 success.
Campaign stays8/10 milestones,native8/10,static9 before dispatch; reserve native9/10
for this attempt. No installation, candidate execution, SQL, site operation or
production wiring, and no changes to existing SQL lab guards or budgets.

The authoritative `container_readback` function is unchanged, pinned by its
source SHA256 `d2bf7e5ac211e4a315dd28c3ccc88e367165e46ef8c2d462cdc99100a5235280`.
A separate pure diagnostic mirrors each field comparison without becoming an
admission predicate. The owner calls the original guard first at the three
existing checkpoints; only rejection captures one finite first-failure report,
then rethrows the **same** exception. Diagnostic failure/exhaustion cannot mask
the rejection, grant a start or replace the earlier report.

Only fixed mismatch names and finite shape categories are retained. No raw
inspect object, environment entry/name/value, security-policy bytes, labels or
arbitrary errors are emitted. Exact default-PATH-only and AppArmor empty/default/
other categories are diagnostic facts, not newly accepted profiles. Reports are
at most8KiB and charged against the unchanged body control512KiB/observation2MiB
ceilings; exhausted budget yields unavailable evidence and preserves failure.
Workflow/argv/native C sources, acceptance predicates, cleanup and SQL paths
remain unchanged; no speculative profile correction is included.

Five new isolated groups (19 total) establish positive/mutation agreement with
the pinned guard, malformed/redacted data, first-exception/first-report retention,
budget refusal, and inability of a forged matched report to override rejection.
Two additional source-backed controls discriminate diagnostic-field omission
and bypass of the original guard, without editing tracked source; the preceding
five controls remain. Initial tests fail for the absent APIs, then19/19 pass.
These are isolated contract/owner tests, not physical qualification or whole-plugin
release readiness. Focused source review checks data-only output, guard ordering,
exception identity, finite work/output and no new executable/cleanup dispatch.
Ordinary exact-head CI and fresh prerequisites are required before dispatch;
record actual mismatch/cleanup results below, never infer native success from CI.

Local validation for this repair: isolated19/19; bridge1/4; full PHP2,610/23,462
with the same two existing Sodium skips; Node88/88; PHPCS255 files; structure,
build, strict Composer manifest and Composer/npm audits pass, zero advisories.

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

## Actual one-attempt result: pre-start refusal, cleanup verified

Published implementation **47f05dca17f53af22509a23e013feb41acf9c3a4** has successful
[ordinary CI41675517649](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41675517649):
PHP7.4 **2,610/23,464 assertions**, PHP8.3 **2,610/23,469**; Node88/88 in each,
no failures/skips, zero Composer/npm advisories. Local final14-group bridge1/4
passes. Ordinary CI success is **not** native qualification success.

The single approved [native run41675986199](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41675986199),
attempt1/job126056604849, fails on **2026-11-03 16:36:55–16:36:57 UTC** at
`amd64-builtin / container-command`, the first created container's initial
pre-start readback. No start/attach call, container syscall observation, derived
profile application or negative control occurred. The actual failed field within
that compound predicate is not retained; do not guess AppArmor, environment,
command or another specific field, and do not widen a predicate without evidence.

Established facts before refusal:

| Fact | Actual evidence |
|---|---|
| Source cohort |`579900c4a695eed0af4d086ede615768fad43a492c94dd57c654219129d1ce31`|
| Engine/client |28.0.4 /6430e49;28.0.4 /b8034c0 |
| Runtime/library |runc1.5.2 /v1.5.2-0-g29dd3dc2;libseccomp2.5.5 |
| Existing compiler |`/usr/bin/x86_64-linux-gnu-gcc-13`; no installation/download |
| Owned canary ELF |`4e27a6aa75b0e47f5818e040f4f98b91632e0a603ca216af38cfcbd1c4b051ee`|
| Owned host ELF |`3a51b40bead7f3c2f9e87c13197e6b522caf5b124a16b5ec24e3998f3bbfc06a`|
| Imported owned image |`sha256:fef20cc8d81af34929c2236da07bf6a1b2cc290d8af980b6dc6bda79efa20ab1`|
| Derived DATA policy |`3277296f5e2fa40d0e53fe3d2925e390174f3fcfc9ec2adfba8fd63852984fe4`|
| Host observation only |UID1001,NNP0,seccomp0;amd64/i386 getpid2478;x32 getpid-38 |
| Kernel file |7.0.0-1017-azure;IA32=y/X32=n;same hash as prior audit;boot_correspondence=false |
| Observations/output |container observations0; native host output23bytes; total observed39,332bytes |

Compilation and strict ELF checks succeeded for both owned executables; the
owned image imported and passed image/config/layer readback. One container was
created but **never started**. The successful harmless host observer is not a
network/filter control. ENOSYS plus a config declaration still does not grant
complete-ABI non-callability, and no container qualification was obtained.

Primary cleanup reports **containers0,images0,stage_absent=true**. The separate
always-run process succeeds and independently reports the same absence; it
observes41 bounded bytes. Owned container/image/staging files were removed;
there are no retained fixtures or workflow artifacts (Actions artifacts API0).
No compiler cache/base-image/build-daemon resource was created. No candidate,
SQL, installation, installed site or production operation occurred. Existing SQL
workflow/controller and candidate policy API remain byte-for-byte at baseline.

The exact implementation head has two successful ordinary check runs and one
failed native `canary` check; legacy status contexts0. Do not report all checks
green or let a later docs-only CI success erase that native failure. No native
retry was run. Campaign remains **8/10 completed milestones,native8/10,static9**:
construction/preflight/import progress is meaningful, but failed qualification
does not earn an end-to-end product milestone.

**Recommended next gated slice: Harness-only Fixed Container-Profile Predicate
Diagnostics and One Fresh Owned ABI Qualification/Cleanup Attempt.** Add bounded
non-secret per-field mismatch categories that are proven equivalent to the
unchanged guard, with source-backed regressions; then fresh prerequisites and
one explicitly approved attempt. Do not relax fields, infer missing readback,
change SQL guards or count the retry as another milestone. The new attempt is
not included in this already-consumed single-attempt approval. Full all-ABI,
builtin-source/loaded-filter correspondence and candidate runtime admission
remain unestablished. Final docs-only exact-head CI/publication is required
before the consolidated handoff; no further execution is performed for docs.
