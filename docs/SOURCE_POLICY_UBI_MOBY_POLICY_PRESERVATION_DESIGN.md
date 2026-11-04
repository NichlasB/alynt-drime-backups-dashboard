# Candidate-Bound Moby Builtin-Profile Preservation / ABI Effect-Denial Design

Current evidence: the [public compression repair proof](SOURCE_POLICY_OWNED_ABI_CANARY_PROFILE.md#public-compression-repair-proof-passed-x32-coverage-remains-open--2026-11-04)
verifies genuine amd64/i386 builtin-positive/derived-denial controls and exact
cleanup. x32 ENOSYS/config-file facts do not satisfy the unchanged independent
non-callability requirement below; qualification/candidate admission remain false.
Next gate is repository-only x32 evidence sufficiency/candidate admission review,
not further native execution, acceptance relaxation or new attestation tooling.

## Authority, restore point and unchanged boundaries — 2026-11-03

User approves **design only**, repository validation, commit/push and exact-head
ordinary CI. R1 documentation about a future narrowly approved R3 policy boundary;
no global risk-tier increase. No implementation, generated profile, application,
acquisition/import, engine/host audit dispatch, native/canary/candidate execution,
SQL, site/LocalWP/live operation, installation, credential/permission change,
production wiring, packaging/release/tag/deploy is authorized here. Uploader and
toolkit remain untouched. No installed site is selected or inspected.

Restore point **36c93c557c81b5d45450e54a323c5941e18e5638** is clean/synced and
equal to remote master. Its [CI41633494136](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41633494136)
is reverified successful. Repository instructions, owning implementation plan,
[runtime design](SOURCE_POLICY_UBI_CONNECTION_FREE_RUNTIME_ADMISSION_DESIGN.md),
[dormant DATA foundation](SOURCE_POLICY_UBI_RUNTIME_CONTRACT_FOUNDATION.md) and
[actual engine audit](SOURCE_POLICY_UBI_ENGINE_FEASIBILITY_AUDIT.md) are the baseline.
Public immutable upstream bytes are inspected/hashed in memory; no source fixture
or runnable policy is written and no upstream/native program is run.

The historical audit observed Docker28.0.4/6430e49, builtin seccomp and overlay2
on Ubuntu24.04/amd64. It did not establish builtin filter contents. Required
candidate stays manifest14bab8cc.../config2a46b014... and version-only scope; exact
full pins remain in the owning runtime design. Existing `restrict_policy`,
NETWORK_CALLS, source cohorts, workflows and all legacy image/version/SQL/default
admission remain unchanged. Campaign **8/10 milestones,native7/10,static9** does
not increment for this design or its publication.

## Selected separate model, not a general profile parser

Propose a private, default-off **candidate-moby-28.0.4-effect-denial-v1** DATA
model. This is a proposed identifier, not an existing API. It accepts only the
exact candidate, reviewed engine/build/source cohort and one immutable baseline.
Do not widen the old tiny synthetic guard, accept user profiles, relax unknown
keys/actions/ABIs, add another runtime backend or reuse a copied audit packet as
live authority. A changed engine/client/source baseline needs a new review.

Moby commit **6430e49a55babd9b8f4d08e70ecb2b68900770fe** is the official v28.0.4
tag target, matching only the historical engine's reported short commit. Baseline
`profiles/seccomp/default.json` is exactly12,828 bytes, SHA256
`9c1025c88ccaa517b648da571961838744ea2137f176bfe6a48b21294cae9c76`.
Constructor/resolver hashes are already recorded in the engine audit. Additional
source pins read in this design are:

| Moby path at that full commit | Bytes | SHA256 |
|---|---:|---|
| profiles/seccomp/generate.go |569|`b062504acf993cf85953040cc4e5eb4829251c6c6383660dc6c1c0a62cb675c5`|
| profiles/seccomp/seccomp.go |4,449|`40d8214c6dfc7eb495753b437b6ca17b25da304c3b3fc3f1c933d15de067738e`|
| profiles/seccomp/kernel_linux.go |1,743|`4ad0494e6d12f1cf30af8a55b3bd4ccd0b4b5fc65902b8888be7e0ed0d04591e`|
| profiles/seccomp/seccomp_test.go |8,328|`a1ae21a8ccb727231012635de9e9de01d8349453dddf9d75ef2876c1401b4914`|
| daemon/seccomp_linux.go |1,921|`ef55499aaa843322028734c74604ecba3a68686436320c1ad4bef2bc6b5b0860`|
| daemon/oci_linux.go |33,896|`816b53630dca6536a0ea97a2e9156818ec2a855761c887e3b5372c06ca198b3a`|
| daemon/config/config.go |32,037|`57fcdcb6cbc0e3a1aa49b302e5a5ec7c612703c7154e9081b779ebc1ffe13d51`|
| vendor/github.com/opencontainers/runtime-spec/specs-go/config.go |39,855|`31dd6e0933c9e85c798319b307c8a2093c7866808f6f473f0cfa827a131b1b61`|

CLI28.0.4 tag target is **b8034c0ed70494a90c133461d145cd072d920d7c**. Its
`cli/command/container/opts.go` is43,737 bytes, SHA256
`5152629b6638c0a307da1c24a9758b443d8c66b1eee35d38ec77ed6265d29109`.
The actual client build was not captured by the old server-only audit. This is
a reviewed candidate client, **not observed client admission**. No entire-daemon,
CLI or dependency security audit is claimed by hashing these source files.

## Exact structural subtraction and non-weakening obligation

Raw-byte hash/length admission precedes bounded strict JSON parsing. Preserve
defaultAction=SCMP_ACT_ERRNO and defaultErrnoRet=1, the entire archMap, array
order, every retained rule, field/type/value, selector, argument index/value/
operator and explicit errno. Duplicate syscall names across differently guarded
rules are meaningful: never globally deduplicate, merge, sort or flatten them.
Reject duplicate JSON keys, non-finite numbers, bool/int substitution, unknown
metadata or a different raw baseline before producing a derived result.

Only operation: subtract the following fixed22 names from **ALLOW** name arrays,
wherever they occur. Do not remove any explicit-denial rule or its selectors.
Remove a whole ALLOW rule only if its name array becomes empty:

```text
socket socketpair socketcall connect bind listen accept accept4 shutdown
getsockname getpeername setsockopt getsockopt
send sendto sendmsg sendmmsg recv recvfrom recvmsg recvmmsg recvmmsg_time64
```

Read-only analysis of the actual pinned JSON establishes428 name occurrences
in31 rules. Exactly two original zero-based rules intersect this set: rule0
loses21 names and survives; rule2 contains only socket and is removed, including
its now-irrelevant argument predicate. Planned output therefore has30 rules and
406 occurrences. These are **expected deltas, not an executed derivation**.
All other original rules remain value/type/order-identical. Preserve in
particular clone3's ERRNO38 and CAP_SYS_ADMIN exclusion, not merely its name.
No io_uring_* name is ALLOW in this baseline; independently require that to remain
true. The proposed new set includes send/recv/time64 variants omitted by the old
synthetic set without changing that old contract.

Require an independent relational verifier, not only a hash or count check:

1. Re-admit the original raw baseline, context and exact source manifest.
2. Compare every top-level field except the syscall array exactly.
3. Walk original rules in order; derive the sole permissible retained-name
   subsequence and compare each resulting rule, including all other fields.
4. Require no extra/missing/reordered rules or names, the exact delta and no
   remaining ALLOW occurrence of a denied name under **any** condition.
5. Independently check all non-denied names retain their original occurrences,
   guards and actions; explicit denials/errno and architecture mappings persist.

Design inference: for the same source-resolved host/capability/kernel context,
non-network syscall decisions are unchanged, while removed networking allows
fall through to denial. No permission is added. This argument relies on exact
selector/resolver preservation and the reviewed ALLOW/ERRNO-only baseline; it is
not a general proof for arbitrary seccomp actions or opaque policy files. Never
introduce ALLOW-default, notification/listener, TRACE, LOG, new flags/capabilities,
unconfined mode or global daemon/host configuration as an implementation shortcut.

## Builtin correspondence: source facts versus runtime evidence

The pinned [generator](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/generate.go)
serializes DefaultProfile. The upstream
[equivalence test](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/seccomp_test.go)
compares architecture/action/rules, but does not by itself compare every field.
Future correspondence checks must also retain defaultErrnoRet and absence of
new flags/listeners; running or reading that upstream test is not binary attestation.

The pinned [resolver](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/seccomp_linux.go)
is shared by builtin construction and JSON loading. Its conditions use host Go
architecture, **bounding** capabilities and kernel major/minor; these are not
caller-selected syscall-ABI selectors. Preserve includes/excludes verbatim,
including all/any membership and version boundaries. Do not pre-evaluate them
in the serialized derived profile or replace source behavior with an approximate
Python resolver. Pure selector models are test evidence only.

Review of daemon WithCapabilities→WithSeccomp ordering and the
[builtin/custom branches](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/daemon/seccomp_linux.go)
defines the intended path. Future qualification must freshly bind engine **and
client** exact version/commit, builtin availability, local endpoint, nonprivileged
effective configuration, empty capability sets, no-new-privileges, namespaces,
kernel and runtime/libseccomp identities. No unsupported-kernel warning fallback
or daemon custom default may silently stand in for builtin.

The reviewed [CLI](https://github.com/docker/cli/blob/b8034c0ed70494a90c133461d145cd072d920d7c/cli/command/container/opts.go)
recognizes seccomp=builtin and compacts custom JSON before sending it. Proposed
baseline control explicitly selects builtin; derived control uses exactly one
owned profile, never unconfined. Future source/client admission must establish
these same paths before using those options. Distinguish original raw SHA,
canonical derived SHA and actual transmitted/read-back custom JSON SHA; never
compare a filesystem path or CLI echo with a policy-content hash.

Later stored HostConfig/SecurityOpt readback must match the exact owned request
and compact payload. That is **request/configuration evidence**, not proof of
resolved OCI rules or loaded BPF. The chosen trust model is the existing trusted
ephemeral engine plus reviewed source correspondence and independent controls;
it is not reproducible-build, measured-binary, daemon-integrity or loaded-object
attestation. All stronger claims remain false. If required correspondence or
runtime/ABI metadata cannot be established without new tools/permissions or
unsupported assumptions, refuse and report the concrete blocker.

## ABI coverage and non-vacuous future controls

ABI here means syscall calling conventions, not MariaDB/PHP/mysqlnd/genuine-core
transport compatibility. Required legacy row M remains unqualified.

Retain the complete original archMap, including other unchanged architecture
rows; this grants no support for executing those hosts. On admitted amd64, the
resolved mappings must remain x86_64, x86 and x32. Deny both direct calls and the
x86 socketcall multiplexer, plus applicable compat/time64 variants. Require an
explicit reviewed name→ABI/number coverage matrix tied to the actual runtime/
libseccomp translation identity, not just searches of JSON names.

Linux's [seccomp ABI caveats](https://man7.org/linux/man-pages/man2/seccomp.2.html)
explain that x32 shares an audit architecture with x86_64 and is distinguished by
a syscall-number bit; checking only arch is inadequate. Pre5.4 kernel ambiguity
also needs explicit rejection/handling. Proposed first native qualification
requires kernel>=5.4, without changing plugin PHP/WordPress compatibility. Do not
delete x32/x86 mappings because a host cannot execute a test binary.

All following controls are future, candidate-independent, source-pinned owned
fixtures in the separately authorized ephemeral lab, not executed in this design:

| Required evidence | Success condition / honest failure state |
|---|---|
| Canary health | Fixed harmless non-network action succeeds under both builtin and derived configurations; identity/argv/result are correlated. A crashed or unstarted canary is not denial. |
| Baseline networking positive | For each supported ABI, bounded loopback-only socket lifecycle succeeds under explicit builtin, with no publication/external destination, then descriptors close. No globally unconfined positive. |
| Derived enforcement | Same source/ABI inputs fail with the expected kernel denial and active canary continuation under the derived policy. Direct socket and x86 multiplex paths are distinct controls. |
| Direct bind/listen and related calls | Use separately source-validated raw-call vectors: builtin canary establishes the real lifecycle plus the expected invalid-FD result; derived control with the same invalid-FD arguments must yield policy denial instead. This tests entry filtering without inheriting a socket or opening a derived listener. Unknown ABI/error ordering is a gap, not an assumed pass. |
| ABI availability | A missing loader, bad fixture, CLI failure or ENOSYS alone is not proof an ABI is kernel-disabled. Unsupported/unobservable ABI remains unestablished. Independent qualified kernel/runtime evidence may establish non-callability, separately labeled, never a passed filter control. |
| Non-network preservation | Pinned rule/argument/selector/errno matrices remain identical; representative control results complement, not replace, structural preservation for all rules. |
| Ownership/release | Exact CIDs, run/head/source/attempt, image/config/profile hashes and bounded cleanup/independent absence checks. Never prune or remove pre-existing/shared cache. |

At least the native amd64 health/positive/denial chain must actually pass; a
collection of unavailable/expected-refusal controls cannot qualify anything.
ABI-complete evidence requires every supported mapped ABI covered and remaining
mapped ABI non-callability independently established. Otherwise leave coverage
unestablished and do not start the candidate. No inference that read/write/ioctl
on an inherited socket, all device access or every possible kernel effect is
eliminated: candidate FD/environment/mount/capability boundaries remain required.
Keep io_uring unavailable and no inherited ring descriptors. This is not a
general sandbox or protection from a compromised trusted engine/kernel.

No canary binary, compiler/image dependency or privileged evidence reader is
selected by implication here. Use only an independently reviewed bounded owned
fixture within existing authority; new installation, host privilege/security
changes or inability to observe required evidence is a stop, not a fallback.
Candidate version execution and SQL remain later separate gates even if every
harmless containment control eventually passes.

## Proposed DATA, bounds, tests and staged implementation

Propose a separate versioned private policy-DATA record (name ADBUMOBYPOLICY1,
not an existing schema). Exact primitive fields bind profile model, candidate
manifest/config, original source hashes, derived/transport hashes, fixed deny
set identity, retained-rule/delta facts and ABI coverage requirements. Run/head/
attempt/static+runtime-source/graph/snapshot context must be fresh and source-held
at an actual owner seam, never supplied as a ready/qualified boolean. Finite source
manifest must explicitly include every new transformer/verifier/serializer/
policy fixture and eventual controller/workflow. Do not silently reuse the old
ten-path cohort for future consumers or rewrite historical audit-source identity.

Accepted pure records always have execution/launch/runtime grants=false;
structural-preservation DATA, source correspondence, configuration readback and
kernel/ABI control evidence are separate states. A copied receipt is not a
capability. No operational owner, executor, workflow selection or source-static
export integration is activated by the next dormant foundation.

Proposed pure parser bounds: baseline<=16KiB (actual12,828), nesting<=16,
rules<=64 (actual31), name occurrences<=512 (actual428), derived serialization
and evidence each<=64KiB. Preserve duplicates where meaningful; reject unknown
structure or any expansion beyond the exact delta. Account source reads,
parsing/verifier traversal and retained bytes within existing1MiB control,
2MiB observation,2,097,152 work and2GiB shared logical-byte ceilings. No new
256MiB acquisition/300s allowance, additive phase budget or native-attempt reset.
All eventual commands/control output use existing job/deadline/cleanup reserves;
fail before starting if remaining budget cannot complete the ordered proof.

Next foundation must add discriminating pure tests before implementation:

- Positive exact-pinned-baseline DATA derivation and independent verification,
  with safe fixture provenance/licence attribution and no live downloads in tests;
  this does not make a synthetic profile or altered pin a genuine baseline.
  Preserve exact fixture bytes across Windows/Linux checkout with scoped fixture
  attributes or lossless encoding, never newline normalization or pin overrides.
- Raw/source/candidate/context tampering; wrong client/build; stale/copy frames;
  unknown keys/action/ABI, duplicate JSON keys/NaN/floats/bool coercion and overflow.
- Every protected rule/selector/argument/errno/arch/type/order mutation fails;
  global name deduplication and a permissive replacement baseline fail as well.
- Positive/negative selector boundary matrices exercise the reviewed host/caps/
  kernel semantics at DATA level without pretending to run Go or a kernel filter.
- x86 multiplexer, x32-number-bit, missing time64 names, lost explicit errno,
  omitted source binding and always-unavailable proof ownership are discriminated.
- Source-backed assertion-killed removals of pin, exact-delta, protected-field,
  ABI coverage and no-grant guards; positives kill an always-refusing transformer.

No implementation or new test is added by this design; these are acceptance
requirements for the next approved slice. No profile hash, native success,
ABI enforcement or source-to-binary attestation is manufactured here.

## Design validation, implementation-plan progress and next gate

Focused source-scoped review checks subtraction-only non-weakening, actual JSON/
Go/builtin/client distinctions, preservation of syscall occurrence semantics,
ABI caveats, non-vacuous controls, resources/ownership and evidence limits. It is
not canonical whole-plugin release-readiness, Database Review, UI/UX or a native
proof. Current validation/publication/CI results are recorded after execution;
restore-head CI above is not substituted for this design's checks.

Executed local validation: full isolated PHPUnit **2,608 tests/23,454 assertions**,
no failures/errors, two existing Sodium-unavailable skips; Node **82/82**, no
failures/skips. `npm run check:structure`, owning local-link checks and
`git diff --check` pass. Source/test/workflow/dependency/production bytes are
compared unchanged against the restore point; only this design and its owning
implementation-plan pointer differ. No tests are added, skipped or weakened;
no Go/engine/ABI canary or profile operation runs. Exact-head ordinary CI checks
and actual counts are retrieved after publication and reported at handoff.

Implementation-plan progress: the real-profile compatibility blocker now has a
finite preservation model and explicit source/transport/ABI proof obligations,
without activating it. Safe-local design is complete; implementation requires
the next scoped approval. Campaign remains8/10 milestones,native7/10,static9.

**Recommended next gated slice: Repository-only Socket-Free Candidate-Bound
Moby Policy Preservation and ABI Coverage DATA Safety Foundation.** Approve
bounded fixed-baseline derivation plus independent preservation verifier and
non-vacuous isolated tests through validation, commit/push and exact-head CI.
Keep old guards/workflows unchanged, all profile application, acquisition/import,
native/canary/candidate execution, SQL and production wiring disabled. Only after
that foundation is verified may the retained owned engine-import/containment
proof authority be revisited with fresh prerequisites; no candidate-execution
grant follows from successful DATA.
