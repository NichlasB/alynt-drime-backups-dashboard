# Socket-Free UBI Import and Launch DATA Safety Foundation

## Authority and restore point — 2026-11-03

User approves the repository-only dormant foundation following the
[runtime-admission design](SOURCE_POLICY_UBI_CONNECTION_FREE_RUNTIME_ADMISSION_DESIGN.md).
Narrow R3 trust-boundary implementation authority covers pure contracts, synthetic
byte fixtures/inert adapters, validation, commit/push and exact-head ordinary CI.
It does not enable acquisition, Docker/engine inspection or mutation, security
profile application, image loading, candidate/native execution, SQL, installed
sites, production wiring, package/release/tag/deploy or system changes.

Restore point **0c7a6111768ddbd05f201c00f7cde14eb6210ca7** is clean/synced and
reverified equal to remote master; its ordinary
[CI41623425082](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41623425082)
is successful. Uploader/toolkit are clean and untouched. No new workflow, caller,
runtime include, dependency, old static cleanup rule, source pin or existing
native/default image/version/session admission changes.

## Implemented boundaries

- [Import codec](../scripts/manual/source_policy_ubi_import_contract.py): proposed
  single-image/no-tags USTAR envelope, unchanged config bytes and ten ordered
  opaque layer payloads, digest-derived member paths, deterministic regular-file
  metadata and <=64KiB output chunks. Incremental bounded gzip handling rehashes
  compressed bytes and expanded diffIDs and checks lengths, order, EOF/trailing
  bytes. Duplicate/nonstandard JSON constants, malformed types/shape/metadata,
  excessive payloads and zero/invalid sink progress refuse. No rootfs extraction,
  disk archive or operational sink exists.
- The envelope is **provisional until the generator completes successfully**.
  Any hash/length/decompression failure invalidates all prior output. A generic
  synthetic payload can pass the codec without being an admitted candidate tar.
  A future consumer must not connect provisional bytes to an engine and equate
  partial import or a drained prefix with admission. Actual import/quarantine,
  engine-format/platform/content-ID and file readback remain unimplemented and
  unqualified. Original inner-tar/static validation is not replaced by gzip hashes.
- Shared DATA accounting charges metadata parsing, bounded work, decompression,
  archive copying and inert sink copying against the original ceilings: 2GiB
  expanded, 2,097,152 work units, 1MiB control and 2MiB observation; no fresh
  per-phase allowance. Virtual clocks enforce import120s, candidate15s and
  cleanup30s reserve within600s. Accounting poison preserves its first cause.
  These are synthetic clock/byte contracts, **not a real process supervisor**.
- [Runtime contracts](../scripts/manual/source_policy_ubi_runtime_contract.py):
  exact primitive candidate/run/head/static+runtime-source/snapshot/graph DATA
  context, fixed ten-layer compressed identity/size plan, finite explicit source
  cohort, nonroot direct version argv, exact resources/environment/read-only
  tmpfs/namespace/privilege fields and required future independent readbacks.
  Strict recursive type equality rejects `1`/`true`, floats and tuple/list
  substitution. Every accepted plan/frame explicitly has launch_authority=false.
- The runtime source cohort currently adds the two new consumers to the existing
  eight-path static cohort. Tests compare the duplicated candidate/layer pins and
  static path cohort to actual existing source. This is a pure digest-composition
  contract, **not actual immutable checkout/source collection**. Any later
  controller/workflow/profile/serializer requires explicit cohort extension and
  fresh hashing. Historical static-source identity remains distinct and unchanged.
- Restrictive policy **DATA** transformation accepts only a bounded simple
  x86_64 default-ERRNO allowlist, removes networking/multiplexer names, adds no
  allowance and rejects conditional/unknown shapes/actions/architectures. Actual
  Docker default profiles may be more complex and consequently refuse. No real
  default-profile pin, kernel semantics or supported ABI closure is established.
  Synthetic derived bytes/hashes always say kernel_verified=false; network:none
  alone remains insufficient. No profile file is written or filter applied.
- Full CID/name/labels/image/exact mount/state DATA comparisons model ownership
  correlation, not independent engine inspection or permission to remove an
  object. No image/cache deletion operation exists; pre-existing/shared content
  may never be inferred owned from these copied records.
- Bounded interleaved stdout/stderr accounting, exact proposed synthetic version
  literal, output hashes (no raw output), CID/content/containment/argv/context
  correlation, explicit exit/OOM/timeout and independent cleanup fields form a
  separate <=64KiB ADBUROBS1 DATA summary. Unknown output, nonzero exit, output/time
  overflow and uncertain/failed cleanup cannot become a valid DATA observation.
  First failure survives successful cleanup; cleanup does not mint success.
  The proposed literal has **not** been observed from this candidate. No SQL,
  legacy M/ABI, subsequent-execution or genuine native evidence is asserted.

The modules use only bounded pure standard-library operations. Inert byte sinks
and clocks are fixture inputs, not safe sandboxes for arbitrary Python callbacks.
Serialized frames can be copied/forged and are deliberately not capabilities.
There is no operational owner/factory, dynamic executor, public entry or ready
handle; no existing static/native workflow imports either module.

## Non-vacuous validation and focused review

[Isolated Python suite](../tests/support/source_policy_ubi_runtime_contract_test.py)
is invoked by the existing PHP subprocess pattern through
[SourcePolicyUbiRuntimeContractTest](../tests/SourcePolicyUbiRuntimeContractTest.php).
Thirteen groups cover successful byte-preserving envelopes/partial writes, exact
DATA identity/plan/subset/ownership/observation positives and independently changed
length/hash/order/EOF/metadata/type/source/profile/privilege/mount/output/timeout/
cleanup failures. Large compressible synthetic payloads exercise bounded decoder
drainage rather than only a one-buffer path. Bad metadata refuses before a
fixture stream can be read. Candidate pins are never overridden to make synthetic
config bytes pass candidate import identity.

Seven source-backed semantic removals are assertion-killed: compressed digest,
sink integer typing, candidate manifest, networking removal, exact launch plan,
ownership labels and primary cleanup requirement. Positive archive/plan/summary
checks ensure an always-refusing implementation fails. Mutation functions exist
only in isolated test namespaces, never source edits or deployed bypasses.

The first regression invocation failed because the modules were absent. Initial
tests then caught a late-bound chunk-fixture variable; independent strict-type
regressions caught bool/int and float/int equality and were repaired with bounded
exact-type traversal, not weaker assertions. Unknown JSON constants now refuse.
No existing test is removed, skipped or weakened.

Focused source-scoped error/security/resource/compatibility review covers prefix
invalidity, bounded decompression/backpressure, source and candidate separation,
type integrity, ownership, first-cause and independent cleanup. It does not stand
in for canonical whole-plugin pre-release/security/Database Review, kernel or
physical/native transaction evidence. PHP7.4/WordPress6 support and production
behavior are unchanged; backend-only work requires no UI/UX workflow.

Executed validation: direct isolated Python suite **13 groups,0 failures/errors**;
targeted UBI/predecessor PHPUnit **19 tests/108 assertions**; full isolated PHPUnit
**2,608 tests/23,454 assertions**, no failures/errors, two existing local
Sodium-unavailable skips. `php vendor/bin/phpcs`, Composer strict validation/audit,
`npm audit --audit-level=moderate` (zero vulnerabilities), `npm run test:admin`
(74/74, no skips), `npm run check:structure`, `npm run build`, local record-link
and diff checks pass. The focused suite is rerun after the final source edits;
no generated tracked artifact or dependency changes. Exact-head publication
checks/counts are retrieved after push and reported at handoff; restore-head
results above are not substituted for this implementation's CI.

## Implementation-plan progress and exact next gate

The designed import-envelope, launch/effect-plan and version-observation trust
boundaries now have executable, non-vacuous **synthetic** contracts. Source-held
same-attempt static closure export, operational import/readback/containment
supervision and actual engine/default-profile/kernel feasibility still have no
production wiring or execution authority. No candidate bytes/resources are
created or retained by this task. Campaign remains **8/10 milestones,7/10 native
attempts,nine static acquisitions**; this preparatory foundation is not a product
milestone or native attempt.

**Recommended next gated slice: Candidate-Bound UBI Owned Engine-Import and
Effect-Containment Feasibility / Cleanup Proof.** Explicitly approve audit-first
work in the trusted ephemeral CI lab: fresh same-attempt static prerequisites,
owned non-executing image import and stopped-container file/config readback,
exact engine/default-policy source qualification, separately bounded harmless
containment controls and exact owned cleanup. Keep candidate execution and SQL
disabled during this prerequisite slice. Unknown import format, non-weaker
policy/ABI semantics or ownership/cleanup must stop before progression; no
fallback pull, unconfined policy, extra permissions or host installation. Any
new native qualification must consume the existing campaign budget, not reset it.
Version-only candidate execution remains a later explicit gate after these
prerequisites, never an automatic grant from synthetic DATA.
