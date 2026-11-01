# UBI Critical-Alias and Dependency-Closure Admission Design

Current resume: user approves [bounded hardlink content provenance](SOURCE_POLICY_UBI_HARDLINK_STATIC_ADMISSION.md)
for the separate fixed static mode, no extraction/execution or default relaxation.
Actual qualification follows fresh exact-head ordinary CI and one owned attempt;
earlier type exclusions/gates below describe their historical source.

Current outcome: [alias-static foundation/proof](SOURCE_POLICY_UBI_ALIAS_STATIC_ADMISSION.md)
is published/CI verified; actual archive refuses a hardlink before full layer
verification. No alias/namespace/runtime qualification follows. New boundary is
Candidate-Bound Static Hardlink DATA and Content-Provenance Policy Exception;
strict defaults/empty default aliases and no execution remain unchanged.

Current resume: user approves the [candidate-bound static exception](SOURCE_POLICY_UBI_ALIAS_STATIC_ADMISSION.md).
Only an explicitly selected static workflow mode consumes the model, after full
layer hashes, with the fixed lib64 edge and runtime_grantfalse. Shared strict
reader/defaults and unapproved-link refusal remain. Historical empty-consumer
and pending-policy statements below describe their original slices.

Current resume: [actual diagnostic41342892366](SOURCE_POLICY_LEGACY_UBI_FEASIBILITY_PROOF.md#campaign-diagnostic-actual-failed-entry--2026-11-01)
at8166015 establishes layer-0 symlink lib64-to-usr/lib64, not a fully verified
layer/final-rootfs origin or alias grant. Original refusal and empty real profile
remain. The next action requires an explicit Candidate-Bound UBI Alias-Aware
Static Admission Policy Exception; unchanged retries cannot close this policy gap.
Earlier resume statements below are historical.

Current resume: approved [synthetic-only foundation](SOURCE_POLICY_UBI_PROVENANCE_VIRTUAL_FOUNDATION.md)
implements observational first-failure correlation and an unconsumed dormant
model; original static refusal and real-alias/execute denial remain. Next gated
slice is Owned UBI Failed-Entry Provenance Diagnostic Acquisition and Cleanup
Proof, one unchanged-refusal static diagnostic only, not an alias acceptance run.

## Approved boundary and evidence — 2026-11-01

Repository-only design, validation, commit/push and exact-head ordinary CI are
approved. R1 documentation scope, not parser or runtime authority. Clean/synced
restore/coherent review baseline72d2ac0ed0d613a6ce313fed02e802d042a0a5f0;
ordinary CI41335410457 reverified successful. No fresh metadata/payload acquisition,
host rootfs extraction, candidate loading/execution, workflow dispatch, database/
site operations, installation or production-wiring changes. Uploader/toolkit stay
read-only. Existing failed proof and cleanup evidence remain historical facts.

[Static attempt41335270059](SOURCE_POLICY_LEGACY_UBI_FEASIBILITY_PROOF.md#exact-head-publication-and-single-owned-refusal)
verified fixed metadata and the first compressed layer, then refused at
critical-parent-not-directory. Zero complete layers/diffIDs, four owned files
removed, primary/always cleanup complete, no execution. The packet does not
identify the failed member/type/link target. A regular-file parent must continue
to refuse; do not infer that this was a legitimate lib64 symlink merely because
that shape is a useful synthetic test. Rootfs/dependency closure remains unknown.

## Decision: evidence first, dormant virtual model, no alias enabled

Preserve the shared reader's direct-directory and critical-linked-path guards.
Do not remove both as an implementation shortcut, normalize a caller path through
the host, or trust candidate contents to authorize themselves. Next implementation
is an observational failure packet plus a **separate dormant, IO-free virtual
namespace/closure model** tested on synthetic records. No production/shared-reader
consumer calls that model, and the real-candidate alias allowlist is initially
empty. It cannot change the result of the existing UBI static attempt.

A later separately approved diagnostic acquisition can establish the failed
entry's actual type/target. Only then consider one exact candidate/source-bound
alias profile and integration. If the observed entry is a regular file, special
type, escape, unresolved link or costs exceed limits, retain refusal. No automatic
new image, privileged helper, toolchain or generalized OCI extractor project.

## Failure packet contract, not a grant

Capture once immediately before the original critical-parent/linked-path throw;
rethrow the same refusal without continuing. Preserve original first cause if
cleanup also fails. A proposed versioned bounded packet contains:

- source head and reader/entry/workflow source-image digests, run correlation,
  fixed platform-manifest/config digest, layer ordinal and compressed digest;
- compressed verification true/false separately from full-layer diffID status
  (must be false if the scan stopped early), bounded entry ordinal;
- fixed failure code, member-type enum, affected selected-role enum, canonical
  member/link-target lengths and hashes, and path/target syntax categories;
- optionally literal member and link target **only if** already validated as
  printable ASCII, <=240 bytes, no controls/backslashes and inside the fixed
  candidate's library/executable path domain. Otherwise hash/category only.

At most one packet, <=2048 encoded ASCII bytes, no raw tar/PAX/body dump, arbitrary
exception, URL/token, host path or environment. Missing/malformed/oversized fields
cannot replace the failure with success. A hash is correlation, not authorization.
No ownership/cleanup predicate may depend on untrusted packet contents. Diagnostic
capture must leave read order, budgets, refusal precedence and effects unchanged.

## Dormant namespace model and provenance

Input is bounded primitive records supplied by a future source-owned collector,
never archive extraction or a filesystem handle. Preserve existing path grammar,
Unicode15.0 policy, exact bytes and normalization rules for archive entry names.
Keep archive keys separate from link-target syntax; a host Path.resolve/realpath,
os.stat/readlink, ldd, dynamic loader invocation or candidate helper is forbidden.

Each node records literal key/type/mode/UID/GID, layer+entry origin, content hash/
length where applicable, and literal link target. A content hash is admitted only
after its complete containing layer's compressed hash **and diffID** verify.
Unverified layer records remain quarantined DATA. Stage a whole layer, validate
duplicates and metadata, then atomically commit its virtual changes; failure
discards the staged layer. The model never returns partial roles as qualified.

Maintain a bounded virtual tree and origin graph, not copies of every file body.
If a critical body is needed later, re-read only its exact recorded layer/entry
under remaining budgets and verify its digest. No rootfs materialization or
candidate-controlled host filename. Original archive/type/sparse/PAX/whiteout/
expanded-byte guards remain; unmodeled semantics refuse.

The [OCI1.1.1 layer rules](https://github.com/opencontainers/image-spec/blob/v1.1.1/layer.md)
describe layered replacements and deletion markers, not mere tar extraction.
The proposed model applies explicit/opaque whiteouts to lower-layer nodes before
adding same-layer entries, independent of marker order. Markers never become
files. Replacements invalidate affected descendants/origins and prior resolution
results; directory metadata replacement must not erase children incorrectly.
No claim is made that the current selected-role reader implements a full OCI tree.
Hardlink-specific OCI behavior is outside the initial supported subset below.

## Finite alias resolution policy, pending real profile

Proposed resolver consumes only a finalized verified virtual snapshot and an
explicit source-owned profile of allowed alias edges. Initial real profile has
**zero edges**; synthetic profiles are test DATA only. Candidate text or an
untrusted receipt cannot mint/extend the profile. Exact image/config/source and
every expected alias key/type/target/origin must match the later approved profile.

For a selected executable/interpreter/dependency, walk virtual components from
image root. Every non-alias parent must be a verified directory. For an approved
symlink, resolve a relative target against its virtual parent; an approved
absolute target is rooted in the image, never the host. A separate bounded lexical
target parser may process dot/parent components but must refuse any root escape,
ambiguous/empty encoding, control/backslash/unsupported Unicode or oversized path.
Archive entry keys still reject dot/parent components. No drive/UNC/proc/sys/dev
or host-search fallback is introduced.

Follow only profile-listed symlinks, reject cycles/dangling targets and missing
parents, and require the terminal node to be a verified regular file. Record
every edge plus final content origin. Critical hardlinks, directory hardlinks,
cross-layer hardlink ambiguity and special types remain refused initially; do
not pretend a hardlink is a symlink or copy bytes under a new provenance label.
Late changes to any edge/ancestor/target invalidate all dependent roles. Resolution
memoization must be keyed to the finalized namespace/profile digest, not path alone.

Proposed tighter model caps within current global ceilings: <=65536 nodes/entries,
<=10 layers, <=16 link hops and32 path components per resolution, <=240-byte
canonical keys, <=256 selected ELF objects and4096 dependency edges. Charge every
lookup/hop/edge and re-read against one monotonic operation budget/deadline;
cycles/cap exhaustion refuse without recursive/unbounded retries. Measure model
memory/CPU with isolated worst-case fixtures before integration; these numbers
are proposed ceilings, not measured feasibility or permission to increase limits.
Keep existing256MiB payload/128MiB layer/1GiB layer-expanded/2GiB total-expanded/
300s acquisition and native resource ceilings. Stop if the model cannot fit them.

## Dependency closure is more than ELF paths

The current [elf reader](../scripts/manual/source_policy_linux_minimum_static.py)
reports interpreter/DT_NEEDED/search-path DATA and explicitly returns
dependency_closure_verifiedfalse. Alias resolution must not flip that flag.
For each selected ELF, bind exact bytes/type/architecture and interpreter origin,
then resolve each direct/transitive needed object through a finite, source-owned
search policy and the same verified snapshot. Unknown/slashed dependency names,
ambiguous SONAME/provider selection, unsupported RPATH/RUNPATH tokens, $ORIGIN,
cache/hwcaps behavior or object cycles refuse until modeled and tested explicitly.
Do not borrow the host loader/cache/libraries or run ldd/ldconfig to infer closure.

[GNU libc's linker guidance](https://sourceware.org/glibc/manual/2.43/html_node/Dynamic-Linker-Hardening.html)
explains why explicit dependency information and runtime loading features are
separate concerns. A DT_NEEDED graph alone cannot establish NSS, OpenSSL/FIPS
provider, plugin/dlopen or shutdown-loaded object closure. Retain those unknowns;
do not disable FIPS, change LD_* or GLIBC_TUNABLES, install libraries, rebuild the
candidate or claim complete dynamic closure to make version execution reachable.

The initial dormant result distinguishes `namespace-consistent`,
`selected-origins-verified`, `dt-needed-closure`, and `runtime-profile-unestablished`.
These are non-executable DATA, not interchangeable grants. Any later version-only
execution decision must state its finite invocation/config/environment and actual
closure/trusted-engine assumptions separately. Current workflow remains static-only.

## Consumers, non-vacuous proof and rollout order

- Shared reader, [fixed UBI entry](../scripts/ci/source-policy-legacy-ubi-static.py)
  and [manual workflow](../.github/workflows/source-policy-legacy-ubi-static.yml):
  no behavior changes now. Future diagnostics preserve original rejection, frozen
  source cohort and exact owner-marker/file/UID cleanup; new hashes stale only
  affected source-image receipts, not historical evidence.
- Tests: preserve current metadata/direct-directory positives and linked-parent
  negatives. Add complete direct and approved synthetic-alias positives, and
  independent wrong edge/type/origin/profile/root/cycle/dangling/whiteout/late-
  overwrite/hash/diffID/budget negatives. Reorder same-layer whiteout fixtures to
  verify equivalent outcomes. Removing authorization, containment, provenance,
  invalidation or bounds must kill assertions, not trigger unrelated startup errors.
- Diagnosis: synthetic expected member/target/hash matches actual capture while
  the original exception still propagates. Capture failure/overflow cannot erase
  cause or authorize continuation. Socket/process/file-operation sentinels verify
  the pure model does no IO; no artificial success path or relaxed source guard.
- Publication: implement only the repository-only dormant foundation next, run
  focused plus full QA, commit/push/exact-head CI. Later separately approve one
  owned diagnostic acquisition with unchanged refusal/cleanup and no execution.
  Review actual entry evidence before enabling even one real alias. Full artifact
  closure and runtime feasibility remain later gates, not automatic task chaining.

Recommended next gated slice: **Repository-Only UBI Failed-Entry Provenance and
Dormant Virtual-Closure Safety Foundation**, using synthetic records/API tests
only. Includes observational first-failure capture but no changed acceptance,
real alias activation, acquisition, workflow dispatch, runtime loading/execution,
initialization/SQL or production wiring. This is the approved design's bounded
implementation proposal, not a general filesystem rewrite.

Implementation-plan progress: this design resolves the critical-alias policy
boundary without claiming a real alias or full dependency closure. On publication/
exact-head CI it is campaign5/10 planning/qualification milestones; native attempts
unchanged7/10 and physical legacy M feasibility still blocked. No whole-plugin
release-readiness/Database Review, support change, package or deployment follows.

## Source-scoped review and validation

Review at72d2ac0 through this design separates observed failure from guessed link
identity, verified layer origins from quarantined metadata, virtual alias modeling
from actual admission, and static ELF edges from runtime-loaded object closure.
The proposed OCI subset is deliberately narrower than a general extractor;
unmodeled types/loader features remain refusals, not implicit compatibility.
No executable/shared-reader/test/workflow/dependency bytes change, so physical
proof source images and their failures are not relabeled. Historical whole-plugin
readiness rows remain untouched; no UI/UX or Database Review runs here.

Fresh local PHP8.5.1 full PHPUnit --do-not-cache-result:2591 tests/23359 assertions,
no failures and two existing missing-Sodium skips. PHPCS255 files, Node74/74/no
skips, structure, strict Composer validation, local link resolution and
git diff --check pass. No new tests are warranted for documentation-only policy;
existing regression success does not prove a virtual resolver or UBI closure.
Publication37a1dc6a47008206bc0eb3ac879f2354e0f15001 is pushed; exact-head
[ordinary CI41336660618](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41336660618)
succeeds. Actual logs PHP7.4:2591 tests/23361 assertions; PHP8.3:2591/23366;
Node74/74 on both, no failures/skips. Manifest/audits/build/structure/PHPCS pass.
This scoped evidence-only closeout receives its own pushed exact-head CI at
handoff without acquisition, new native attempt or changed executable behavior.
