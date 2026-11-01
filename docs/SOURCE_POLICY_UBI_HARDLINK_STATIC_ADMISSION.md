# Candidate-Bound Static Hardlink DATA and Content Provenance

## Approval, baseline and plan — 2026-11-01

User explicitly approves implementation, validation, commit/push/exact-head CI
and **one owned static requalification**, with extraction/execution disabled,
following the [alias-static hardlink refusal](SOURCE_POLICY_UBI_ALIAS_STATIC_ADMISSION.md#exact-head-implementation-ci-and-one-owned-refusal).
Narrow R3 archive/remote-trust/owned-file authority, not a global tier increase.
Restore/coherent review baseline is clean/synced
1ad583a53fe277322e8f0e69c6c45eba22f99a09, ordinary CI41348380861 reverified
successful. Campaign counters start6/10 completed milestones,7/10 native attempts;
static acquisitions are tracked separately and are not counted as native proof.

Plan: collect canonical hardlink DATA without reading a link body or creating
filesystem links; add explicit opt-in regular-file content-origin handling to
the virtual model; preserve complete-layer atomic verification and strict
defaults; test ordering/invalidation/negative/guard-removal contracts; full QA,
publish and verify ordinary CI; then one exact-head owned static attempt with
mandatory primary/always cleanup. No unapproved type/alias expansion or repeated
attempt follows a refusal. No native server, SQL, LocalWP/live state, production
wiring, dependency/image substitution, installation, package/release/deployment.

## Deliberately narrower than general hardlink/OCI support

[OCI1.1.1](https://github.com/opencontainers/image-spec/blob/v1.1.1/layer.md#hardlinks)
distinguishes shared file identity from a symbolic link and describes replacement
and whiteout effects. This policy implements only a bounded regular-file DATA
subset, not host inode/filesystem behavior or a complete general OCI extractor.
The GNU tar format reference was unavailable during this source review; no
additional compatibility claim is inferred. Unknown/ambiguous cases refuse.

The separate collector admits tar hardlink type as a zero-body primitive record.
Its target is archive-root-relative canonical grammar, optionally removing one
leading `./`, with no absolute/parent/empty/backslash/drive-like syntax. Only
ordinary file bodies are streamed into hashes; `extractfile` is never called on
a hardlink. All existing compressed/diffID/type/PAX/sparse/entry/byte/deadline
checks remain, and no staged record escapes before whole-layer verification.

`VirtualClosure` adds exact-boolean `hardlinks=False`, default unchanged. Only
the fixed candidate alias-static entry passes `hardlinks=True`, after original
manifest/config/layer pin admission; the shared strict reader stays byte-identical.
Default model and all runtime consumers remain without hardlink authority.

With opt-in enabled, whiteouts first affect only the lower verified namespace;
non-marker records then apply in **archive order**, not lexical/parents-first
order, to a private copy. A hardlink must name an already-present regular content
node through actual directory parents, not a symlink, directory, missing/forward,
self, special-root or host target. A target already present in a prior layer is
eligible even if that pathname is replaced later in the current layer: the link
captures the earlier content origin. A future-only target is not eligible.
The destination cannot be the target's ancestor: replacing that directory would
remove the very source pathname needed to create the proposed link. Source review
found this corner case; its added regression first failed with no refusal, then
passed with the explicit pre-binding guard. No native attempt was used to find it.

Mode/UID/GID must agree exactly with the target; set-ID hardlink targets/headers
refuse. Supplied hashes/bodies/provenance fields on hardlink input are rejected.
The private materialized DATA node retains the verified file hash/size and original
layer/canonical-record index/path, plus bounded source link history. This is not
symlink redirection or a fabricated new body. Later unlink/whiteout/replacement
of the original pathname does not silently rebind or destroy retained content
identity of another link. Removing the link name itself still removes that name.
Unsupported inode mutation/metadata equivalence is not modeled or claimed.

Only already-resolved bounded chains are accepted, at most16 edges. Self/cyclic
path history and forward references refuse. Selected symlink-plus-hardlink
history shares the original16-edge maximum. Every lookup/history-copy/replacement
is charged to the existing2097152 work limit/300000ms virtual window,65536
entries/nodes/10 layers and current collector resource ceilings; none is raised.
An additional cumulative65536 history-edge-copy cap bounds provenance storage
even when many names share16-edge histories; failed layers/replacements do not
refund it. Exact-bound positive/overflow/atomic-retention tests and a removal
control cover this tighter cap. This is not a native RSS/worst-case timing proof.
Layer failure commits no namespace state. Model policy and provenance bind the
immutable snapshot digest. Only the source-owned fixed entry selects the real
candidate policy and supplies collector-verified records; the standalone primitive
model API is not independent authentication or attestation of supplied DATA.

Selected hardlink receipts expose content SHA256/size/layer/canonical index and
path hashes, not raw arbitrary archive target names. Returned provenance is a
fresh primitive copy. A failed binding may attach fixed hash/layer/index/type
and metadata-equality booleans, never raw names or metadata values. Capture
failure preserves the same original refusal object and cannot authorize a
commit or erase its cause. These are DATA correlations from verified streams, not
independent attestation, physical inode proof, ELF/runtime closure or execution
grants. `runtime_grantfalse` and no extraction/image loading/SQL remain explicit.

## Non-vacuous tests and review

New regression initially fails on missing constructor policy and unsupported
collector type. Its initial ten-failure trace blocked a PHP/Python stderr pipe;
only the exact owned synthetic child PID7008/parent13324 was identified/terminated,
then a bounded3000-byte failure diagnostic rerun completed with the real red
errors. No site/native service was stopped or foreign cleanup performed. This
test-only diagnosis is not a qualification attempt or milestone.

Ten new synthetic groups cover actual gzip/tar collection/no hardlink body read;
positive content hash/origin/default denial/no grant; order-sensitive prior
content versus later source replacement; source deletion/directory replacement/
whiteout and same-layer replacement; missing/forward/self/directory/symlink/
non-directory-parent/special-root/path refusals; metadata/set-ID/forged-origin
atomic failure; chain/combined caps; full-layer hash/work gates; no native
link/symlink calls; hostile policy/target types and returned-copy isolation.
Five new in-memory controls are assertion-killed: metadata-guard removal,
hardlink-chain cap, combined-link cap, cumulative-history cap and lexical reordering of the archive-order
pass. Observation failure rethrows the exact original error. Original contracts pass first;
no startup-error kill, native fake-ready path or weakened assertion is counted.

One older blanket hardlink collector negative changes deliberately under this
approval: traversal still refuses at collection; canonical missing-target DATA
may be quarantined, but its opt-in model application must refuse. Its negative
assertion remains at the newly authorized boundary; no test is deleted/skipped.
Existing strict-reader, empty default aliases, whiteout/model/provenance and
no-execution source contracts remain. Fresh focused PHPUnit10 tests/60 assertions
passes; full QA/publication/actual owned evidence is recorded after execution.

Source-scoped review from1ad583a covers collector/model/fixed entry and directly
affected tests/records. No shared strict reader, runtime PHP/bootstrap, dependency
pin, workflow permission/cleanup, account/schema/site or sibling repository change.
New model/collector/entry bytes stale affected future static source cohorts only;
historical physical receipts remain limited to their original exact source.
No whole-plugin pre-release/Database Review row is refreshed.

Final local PHP8.5.1 full PHPUnit `--do-not-cache-result`:2597 tests/23394
assertions, no failures, two existing missing-Sodium skips. Fresh related suite
10 tests/60 assertions passes. PHPCS255 files, Node74/74 with no skips,
structure/build, strict Composer validation and Composer/npm audits pass; zero
known project advisories/vulnerabilities. Owning local links and diff checks
pass; generated build has no tracked delta. Strict reader/runtime/dependency/
workflow/cleanup paths remain byte-identical to baseline. Final source review
includes the descendant-source fix and tighter cumulative history cap. These
are isolated tests/controls, not native filesystem/inode or candidate proof.
Publication/ordinary exact-head CI must pass before the single owned attempt.

Implementation-plan progress: approved content-origin foundation advances the
blocked legacy M static prerequisite, not native qualification. Counters remain
6/10 milestones and7/10 native attempts pending actual validation/publication/
qualification/evidence. Retrying, diagnostics, cleanup and docs commits do not
earn separate milestone credit; one static requalification is authorized here.
