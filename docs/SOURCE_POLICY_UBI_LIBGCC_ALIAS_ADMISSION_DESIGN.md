# Candidate-Bound libgcc_s Versioned Provider-Alias Admission Design

## Approved scope and restore point — 2026-11-02

User approves repository-only **design** through validation, commit, push and
exact-head ordinary CI. R1 documents only; this does not approve implementation
of a policy exception, a workflow/CLI/default change, acquisition, extraction,
candidate loading/execution, SQL, installed-site/LocalWP, account/permission,
credential, schedule, production wiring, package/release/tag or deployment.
Uploader/toolkit remain untouched. No site operation or target confirmation is
needed for this repository-only scope. Restore/review baseline is clean/synced
**8026dcfd82f9fec156de480bb54aaaa564482195**, equal to remote master; ordinary
[CI41521505570](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41521505570)
is reverified successful. Governing repository instructions and actual provider,
collector, owner/model, fixed entry, workflow and tests were inspected.

## Established evidence and limits

The [one completed diagnostic run41521163038](SOURCE_POLICY_UBI_PROVIDER_ALIAS_DIAGNOSTIC_FOUNDATION.md#actual-single-static-diagnostic-and-cleanup-result)
at6bd8444 verified all ten pinned layers and model commits, then **failed** on
`elf-provider-alias`. It observed exactly:

| Domain | Fixed observed value |
|---|---|
| Manifest | `14bab8cc945c08109a2175e844d46f4eaff1fbf4f59e95dcf2f9cac7961510e1` |
| Config | `2a46b014d1a6182f9f8240db6bae96566cdb11178f878ffe794f15095b745f35` |
| Requested name / alias path | `libgcc_s.so.1` / `usr/lib64/libgcc_s.so.1` |
| Exact target basename / derived path | `libgcc_s-11-20240719.so.1` / `usr/lib64/libgcc_s-11-20240719.so.1` |
| Target-string UTF-8 size / SHA256 | 25 / `d359192c4c77928fa9d0d5be9e87f9a95cad61eaa2081a46213ae80d18bb2ec9` |
| Alias layer / archive entry | 0 /1700, zero-based model entry |
| Alias layer compressed SHA256 | `2920d84eafa0cf94806ab58f0a2124f7b2d35bcbb06fc89a9106dcc28efe397a` |
| Alias layer diffID | `004d2c90a65694c2830b06fddc1047d40063c6cb36fb31a5a3edfce9435326c6` |
| Existing diagnostic predicate results | library=true, prefix=false, not-self=true, numeric-suffix=false |

Primary13-file/directory cleanup and independent zero-file reconciliation pass;
Actions artifacts are zero. No bytes remain for inspection. The hash above is
the **alias target string**, not target-file contents. The prior generic packet's
current-object digest is neither of those domains. Actual target file SHA/size,
final content origin, SONAME, target object policy and dependency closure were
not established by this refusal. Do not invent them or infer ABI compatibility.
`wrong-family` denotes the current lexical prefix rule, not a foreign ABI finding.

Campaign remains **7/10 product milestones, 7/10 native attempts, eight static
acquisitions**. This design earns no new qualification or milestone credit.

## Selected policy decision

Recommend a **separate default-off, exact-candidate static alias profile**, not a
general library-regex change or distribution-wide filename rule. A proposed
literal profile identifier is `candidate-libgcc-alias-v1`; it is a design name,
not an existing API, workflow input or execution authority. Keep the existing
`bounded-strings-v1` parser/work profile independent and unchanged.

Retain the current strict numeric alias conjunction as the default. In the new
profile only, an exact source-held match of the table above may provide a second
admission branch for this single alias. It substitutes only for the prefix and
numeric-suffix restrictions for that tuple. Existing requested/target `library`
validation, non-self rule, same-directory path construction and all subsequent
file/origin/graph checks remain mandatory. Never accept a generic `libgcc_s-*`
glob, arbitrary version/date, suffix family, caller-supplied allowlist or digest.
Do not skip this dependency, turn unknown metadata into success or raise limits.

Changing the pinned image, any fixed tuple member, alias layer/entry or filename
requires a new evidence-backed policy decision. Verified later layers may not
replace the alias with a different origin, even if its target text is unchanged.
The target file itself must retain the existing verified final content-origin
rules; its unknown entry/hash is not fabricated as a new constant in this design.
Pinned complete image contents plus fresh retained origin verification, not the
string hash alone, authenticate whatever target file is eventually observed.

## Construction, ownership and ordering contract

The eventual fixed bounded entry must explicitly select the new mode. Existing
CLI combinations/workflow inputs/defaults must not implicitly acquire it because
`alias_policy`, ELF or bounded access is already true. A later separately approved
entry/workflow integration must add an independent default-false selection,
require all current prerequisites, reject invalid combinations, freeze the same
complete source closure and admit the exact approved clean head. That integration
and any actual dispatch are **not** part of this design or the next dormant
foundation. Do not modify governing rules or use an ambient environment escape.

The future real sequence is:

1. Genuine fixed job/head/source and pinned manifest/config/full layer-plan
   admission; one existing authority, budget and deadline; all ten compressed/
   diffID verifications and namespace commits; finalization.
2. Resolve the selected server/loader and run the unchanged `admit_selected`
   root-pin matcher **before** constructing an enabled alias policy. A caller
   boolean or exported roots packet is not a substitute for this source ordering.
3. The fixed `_owned_graph` helper admits the actual bound owner/model and all
   retained ELF origin bindings. Construct the private per-call profile value in
   the **same loaded ELF module** that consumes it, avoiding class-identity
   mismatch across independently loaded modules. Do not introduce arbitrary
   callbacks, new backend imports or a public factory for caller-selected policy.
4. The graph/provider seam uses that source-held value and actual inspected row.
   Generic/default calls without it retain the old rule. Validate a supplied
   profile/context before protected reads; malformed/unknown/stale opt-in is a
   refusal, not fallback to a permissive path. Reject poisoned/foreign/unfinalized
   ownership before target lookup. Types and object identity are source-level
   controls, not a claim that Python objects form a hostile-process sandbox.
5. Compute the strict alias decision. Only if it is false, consider the bounded
   exact exception predicate on the already inspected alias. Both branches are
   decided **before** throwing the terminal refusal. Never catch a poisoned graph
   and resume it under different policy, nor restart its deadline or accounting.
   If neither admits, preserve the existing first refusal/diagnostic behavior.
6. Only after alias admission inspect the single derived target path and continue
   the existing descriptor/SONAME/object/closure processing. No additional alias
   traversal or target search; a target symlink remains rejected as non-regular.
   One admitted alias contributes its normal edge to the existing graph digest.

The private profile must bind the actual finalized tree and both directions of
owner/model identity, pinned manifest/config, complete fixed ten-layer plan,
fresh eight-source cohort, genuine head/run correlation, current snapshot and
the alias's verified layer pair/origin. It contains one fixed policy row, not an
extensible journal/registry. Source bytes and run correlation must be freshly
constructed, not copied from `ADBUPA1` diagnostics or user JSON. A pure matcher
test may exercise a primitive DATA representation but cannot confer real entry
authority. A private profile's exported description is diagnostic DATA only.
Require exact primitive shapes/types, bounded fixed strings and integers rather
than truthy coercion; reject unknown fields, serialized JSON, mutable caller lists
or arbitrary objects. Bound values before hashing/copying. The single fixed row
and current context cannot expand existing source/observation storage ceilings.

The old snapshot/source digest is **not** a reusable candidate constant:
`VirtualClosure.finalize` hashes the source-bearing identity as well as namespace
contents. Future changes in the frozen source cohort alter the snapshot even for
the same image. Bind against the actual current finalized snapshot and fresh cohort;
never disable equality checks or accept the previous job's snapshot to pass.

## Downstream checks that must not be bypassed

The current [provider/graph](../scripts/manual/source_policy_ubi_elf_static.py)
already returns the canonical regular target path. Graph closure resolves that
path, not the refused alias path, so no new general `VirtualClosure._aliases`
entry or permissive namespace-resolver change is needed. The existing `lib64`
root alias, hardlink policy and separate content-origin protections stay intact.

The target must be a regular-file model view, without setuid/setgid, inside the
existing combined16-link ceiling. Existing permitted hardlink-backed regular
views are **not** newly authorized or exempted: retain owner `check_node`,
`bound_metadata` content-origin path/layer/entry/SHA/size and observation binding,
including any already permitted chain. No missing observation or link may be
treated as the requested file. Missing node, wrong type, altered origin or stale
snapshot refuses normally, even after an exact alias-policy match.

Keep the graph's same-snapshot checks; target ELF64/x86_64 metadata admission;
ET_DYN/type3 and **SONAME equal to `libgcc_s.so.1`**, not the versioned filename;
unsupported-feature/search-path/interpreter policy; provider mapping/content
SHA; required-object closure; cycle detection; and object256/edge4096 limits.
Do not relabel basename equality as SONAME or silently drop another provider.
Successful static closure still leaves relocations, symbol/version/ABI behavior
and native compatibility unproven and runtime_grant=false.

No new body read/parse is needed merely to match an alias: the collector already
uses the current eligible library-name domain, which accepts this basename. Any
eventual target metadata must come from the existing verified retained parser/
owner binding, never host IO or a second unaccounted scan. Keep all current
download/archive/model/storage/parser/time ceilings; charge bounded comparisons
and target inspection through the existing accounting where applicable. Refuse
budget exhaustion; do not reuse the prior597,888 work figure as future evidence.

## Failure DATA and compatibility

Keep existing exact `ADBUELF1`, `ADBUELFPA1` and `ADBUPA1` schemas and first-only
unavailable semantics. A rejected alias still reports the actual failed strict
clauses; an accepted exception must not invent a refusal or change the strict
predicate facts to true. Later missing-target/SONAME/graph failures remain those
actual failures, not retrofitted alias success or a stale previous diagnostic.
No arbitrary raw message, pathname, secret, content buffer or retained candidate
artifact is introduced. Use at most one small versioned policy summary if later
reporting needs it; any such summary must have exact fields and a fixed ceiling,
not enlarge old packets. This design does not claim that summary is implemented.

Actual code changes belong in a later explicitly approved narrow R3 foundation.
Keep PHP7.4/WordPress6 compatibility, runtime plugin code, production factories,
authentication, storage, schema and schedules unchanged. No UI/UX workflow,
Database Review, full release-readiness refresh or runtime proof follows from
this source-scoped design. Full pre-release review remains a separate gate.

## Non-vacuous implementation validation required next

- Preserve actual strict numeric-provider and generic parser/graph positives,
  plus default rejection of the observed versioned target. Add an explicit pure
  policy positive and actual provider/model positive for this exact tuple, clearly
  labelled synthetic DATA, never hash-authenticated candidate qualification.
- Flip each exact tuple field independently: manifest/config/layer pair, alias
  ordinal/index, requested name, target text/length/hash; include same-stem other
  dates, similarly named foreign libraries, traversal/absolute/Unicode/oversize,
  self-target, second alias, missing/unknown opt-in and booleans-as-integers.
  Default off and invalid opt-in must never inspect the refused target.
- Bind positive DATA to one actual finalized model/owner; alter head/run/cohort,
  snapshot, model identity, finalization, layer completeness, observation origin
  and poison state. Each independently fails without borrowing a different
  earlier rejection as evidence. Export mutation cannot enable policy.
- After an alias match, independently demonstrate missing/non-regular/privileged
  target, disallowed chain, altered SHA/size/origin, missing observation, wrong
  SONAME/type, search/features/interpreter, cycle and resource-budget failures.
  Keep the original selected-root-pin negatives. No `ROOT_HASHES` overrides,
  artificial genuine-entry success, parser bypass or fake verified receipt.
- Kill source-backed removals of default-off/tuple/ownership/origin/SONAME and
  pre-target-read guards with discriminating positive/negative witnesses. An
  always-refusing matcher cannot pass. Separate pure/API tests from real archive
  verification, genuine Actions admission and physical cleanup proof.
- Exercise actual helper module identities and error relays; preserve first
  primary failure, poisoning and independent cleanup on malformed profile or
  diagnostic/reporting failure. Do not convert failed static jobs into success.

The foundation should be a dormant pure policy matcher and guarded provider/
graph seam with isolated tests, no fixed entry/workflow selection or acquisition.
An ordinary green CI proves that foundation only. A later separately approved
fixed-entry integration must prove genuine root-pin-before-policy ordering,
source/run/profile binding and default-off selectors before any new static run.
Any newly observed blocker must be evaluated on its evidence, not pre-approved
away by this document. No candidate bytes are acquired or native attempts
consumed here.

## Design validation and exact next gate

This design and its owning implementation-plan pointer are the only intended
changes. Validate local links, diff and document-only scope; run existing full
isolated PHPUnit and structure validation; commit/push and verify ordinary CI at
the exact resulting head. Record actual executed results before publication;
pending CI is not completion and does not supersede the preserved static failure.

Executed local `php vendor/bin/phpunit --do-not-cache-result` passes **2,605
tests /23,442 assertions**, no failures/errors, two existing Sodium-unavailable
skips. `npm run check:structure`, owning local-link and `git diff --check` checks
pass. Scope review confirms all non-document bytes identical to8026dcf, including
policy/diagnostics, parser/model, entry/workflow, tests, runtime and dependencies.
No tests are added or altered by this design. Uploader/toolkit remain clean and
untouched. Exact resulting-head ordinary CI counts/conclusion are retrieved from
publication checks and reported at handoff, not inferred from restore-head CI.

Implementation-plan progress: the exact alias exception, provenance/ordering
requirements and remaining target/graph proof limits are specified; **admission
is still disabled**. Campaign7/10 milestones/native7/10/eight static acquisitions
unchanged; design is not feature completion.

**Recommended next gated slice: Repository-only Candidate-Bound libgcc_s
Provider-Alias Policy Safety Foundation.** Approve the dormant exact-tuple policy
and guarded static provider/graph seam, meaningful isolated tests, validation,
commit/push and exact-head ordinary CI. This is explicit narrow policy-exception
implementation authority, not broader regex/compatibility relaxation. Keep actual
fixed entry/workflow selection, acquisition, extraction, candidate execution and
production wiring unchanged/disabled. No new static/native run is included.
