# Candidate-Bound Selected-ELF and Static Dependency-Closure Policy

## Approval, baseline and plan — 2026-11-02

User approves implementation, validation, commit/push/exact-head CI and **one
fresh static proof**, keeping extraction/execution disabled. Narrow R3 untrusted
binary/archive/remote-trust/owned-artifact scope, not a global risk-tier increase.
Restore/coherent review baseline is clean/synced
abe7be4db797d7d08f8d40404a9573321e4d0c1a; ordinary CI41351325326 reverified
successful. Prior [hardlink proof](SOURCE_POLICY_UBI_HARDLINK_STATIC_ADMISSION.md#exact-head-ci-and-successful-owned-static-proof)
qualified ten layer hashes/namespace commits and two content origins, not ELF or
runtime behavior. Campaign starts7/10 completed milestones,7/10 native attempts,
four static acquisitions; no counter reset or extra credit for evidence commits.

Plan: observe bounded eligible ELF bytes during the existing no-extraction
stream; quarantine metadata until the whole layer verifies; bind reached objects
to immutable final origins; apply one source-owned provider/graph policy; add
real-byte/model positive/refusal/guard controls; review/full QA/publication/CI;
then one exact-head static acquisition and exact primary/always cleanup. No
native version/server, SQL/authentication, LocalWP/live/application state,
production wiring, dependency/image substitution, installation or release work.

## Bounded byte parser, observation is not admission

The new isolated module reads ELF64 little-endian/x86-64 ET_EXEC/ET_DYN DATA:
header/program-table sizes/counts and file ranges; load alignment/order/memory
shape; one mapped dynamic segment and unique interpreter; bounded string-table
mapping/indices/termination; DT_NEEDED, DT_SONAME and search-path metadata.
It does not map/load objects, invoke a linker, inspect host libraries or execute
candidate code. Original shared strict reader/parser bytes are unchanged.

Format references are [ELF header](https://gabi.xinuos.com/elf/02-eheader.html),
[program headers](https://gabi.xinuos.com/elf/07-pheader.html) and
[dynamic linking](https://gabi.xinuos.com/elf/08-dynamic.html), not runtime proof.
The Linux Foundation mirror denied access; no claim is based on its blocked page.
[glibc2.34 constants](https://github.com/bminor/glibc/blob/glibc-2.34/elf/elf.h)
identify filter/auxiliary/audit/config and relevant origin/search flags. Their
presence is bounded enum DATA; **reached-object policy rejects them**. Merely
observing an unneeded library does not authorize those features. Structural
malformations still refuse at parsing. This is a specified subset, not complete
ABI, relocation/symbol/version or dynamic-loader validation.

The collector's new exact-boolean `elf_policy=False` is default-off. Eligible
regular bodies are the two fixed selected paths or direct `usr/lib64` library
names under a fixed ASCII SONAME grammar. It hashes all bodies as before, retains
only one eligible ELF body at a time, parses it in memory, then drops its bytes.
No ELF body file, rootfs extraction or hardlink body read is added. Metadata is
indexed by layer/canonical-record ordinal only after complete compressed/diffID
verification; no partially scanned layer can supply graph observations.

Limits:128MiB/member body,128 program headers,1MiB dynamic/string tables,
240-character strings,64 unique needed names/object,256 observed objects,
2MiB aggregate encoded observations. Parsed program/dynamic/string operations
have a cumulative2097152 work ceiling and are charged into the existing model
work budget before namespace commit. Graph/model operations share the same
budget/300s deadline; existing byte/entry/node/layer/hop/role limits remain.
No original ceiling is increased. These logical bounds/isolated boundary tests
are not independent native RSS or worst-case timing certification.

## Source-owned provider policy and immutable origin binding

New model inspection is finalized-only, directory-parent checked and returns
deep primitive copies, not file/database/write-capable handles. Explicit work
charging accepts bounded integer DATA only. No public endpoint or pluggable
backend is introduced; the fixed source-owned entry constructs the actual model.
Standalone primitive APIs are not independent authentication/attestation.

The real entry rechecks the fixed selected server/loader content hashes from the
previous pinned-image proof. Graph inspection uses the new run's verified model,
never a copied log receipt as authority. Each observation must match the final
node or retained hardlink content origin's exact layer/index/SHA256/size; the
original body path must be eligible. Both roots and every provider bind the same
immutable snapshot. Graph-local memoization cannot cross a snapshot/run.

Provider search has exactly one source-owned directory, `usr/lib64`, and a
bounded literal library name. No host/default-loader/cache/hwcaps/CWD/environment
search or directory fallback. A direct regular file, including an already
verified hardlink content origin, is allowed. Alternatively **one same-directory
symlink to a numeric version in the same library filename family** may name a
regular target. Absolute/traversal/foreign-family/chained/dangling/special/set-ID
targets refuse; provider alias plus retained hardlink history stays within16
edges. This separate provider rule does not populate default/global/critical
namespace alias profiles. The target's parsed DT_SONAME must match the requested
DT_NEEDED name exactly, and providers must be ET_DYN.

The selected server requires exactly the fixed `/lib64/ld-linux-x86-64.so.2`
interpreter; the selected loader has no interpreter of its own. Other reached
objects may have none or that exact interpreter. Its graph edge is explicitly
mapped to the verified canonical loader origin, not normalized through the host.
Any RPATH/RUNPATH tag, including empty or literal default-looking paths, refuses.
Filter/audit/config/origin/search features refuse on reached objects. Unknown
provider identities, cycles, unavailable observations and budget exhaustion
refuse without fallback or a manufactured success path.

Existing model closure revalidates object hashes, providers, graph edges/cycles
and its global256-resolution/4096-edge limits; root resolutions already consume
that same role budget. A success is labeled **static DT_NEEDED closure only**,
with graph/source/snapshot correlations, actual root summaries and provider-alias
count. `runtime_grantfalse`, runtime_profileunestablished and relocations_verifiedfalse
remain explicit. Parser dependency_closure_verifiedfalse is not converted into
runtime permission. NSS/FIPS/dlopen/plugins/shutdown-loaded objects, actual ABI/
symbol-version compatibility and required legacy M core/PHP/database execution
are still unproved even if this static graph succeeds.

## Explicit workflow, diagnostics and proof limits

The manual exact-head workflow adds `elf_policy`, defaultfalse, valid only with
alias_policytrue and its own literal CLI flag. Pure typed mode admission validates
all three combinations and environment/flag agreement before proof ownership or
network work. Repository/ref/run/head/OS gates, permissions, source readbacks,
concurrency, deadlines and exact13-leaf owner-marker/UID cleanup remain. Only the
ELF mode includes the new module in its frozen source cohort. New bytes stale
affected future receipts; historical static/native records are not relabeled.

ADBUELF1 failure facts cap32768 ASCII bytes: fixed stage, hashed object path,
validated library name and root summaries, search-path hashes/counts and known
feature enums. No bodies, raw RPATHs, arbitrary errors, URLs/tokens or host paths.
First facts are retained; oversize withholds detail. Diagnostic malfunction
rethrows the same original refusal. Failure cannot leave a prior selected-origin
status masquerading as graph success; exit success requires the corresponding
completed static status plus unchanged source and exact cleanup.

## Non-vacuous validation and source-scoped review

Initial new test fails on the absent implementation. Actual synthetic ELF bytes
then flow through real gzip/tar hashing, metadata parsing, verified layer/model
construction and graph validation. Fourteen groups cover positive graphs,
same-directory provider aliases and retained hardlink origins; header/segment/
dynamic/string/interpreter malformations and duplicates; wrong hash/size/SONAME,
missing providers, search features and cycles; exact4096-edge positive/overflow;
object/metadata/work/deadline boundaries; finalized inspection/copy isolation;
hostile primitive shapes; and no host IO/linker/process/handle observations.

Five assertion-killed controls cover typed CLI mode admission, content binding,
search-path policy, provider-family and SONAME policy. Hash/search checks have
independent downstream defenses: their controls are honestly scoped to the
actual first validation functions; family/SONAME controls kill whole-graph
contracts. Removing only a first guard does not falsely count as a whole-owner
bypass when the model still refuses. No native fake-ready owner or relaxed test.

One raw regex string initially emitted repeated SyntaxWarning output on import,
filling the old separate stderr pipe. Exact owned test children8524/9428 (parents
40068/21460) were identified and stopped; no foreign/site process was touched.
Direct execution identified the real warning; the regex literal was corrected,
not its accepted language, and the new wrapper drains one merged channel with
bounded65536-byte retained diagnostics and strict JSON/exit validation. No warning
is suppressed. Fresh direct `-W error::SyntaxWarning` passes all14 groups.
These local diagnostics/repairs are not artifact/native attempts or milestones.

Fresh related PHPUnit12 tests/73 assertions passes. Full local PHP8.5.1:
2599 tests/23407 assertions, no failures/errors, two existing Sodium-unavailable
skips (signer round-trip and forced signing-failure coverage). All14 new Python
groups pass without skips, including the final duplicate-tag/interpreter cases.
PHPCS255 files, Node74/74/no skips, structure, build, Composer strict validation,
Composer audit and npm audit pass; both audits report zero vulnerabilities.
The prior full-suite output became unobservable after context truncation; its
process was confirmed ended before one fresh observable validation, not a parallel
duplicate or inferred success. Publication CI and the one actual owned artifact
proof are still pending. Source-scoped review fromabe7be4
covers parser/policy, collector, model inspection, fixed entry/workflow and their
direct callers/tests/records. No runtime PHP/bootstrap, uploader/toolkit, schema,
credential/permission, dependency pin, production form/provider or release byte
changes. No whole-plugin review/readiness or Database Review is refreshed.

Implementation-plan progress: selected ELF/static-provider preparation advances,
but campaign remains7/10 milestones/native attempts7/10 until actual scoped
validation/publication/proof/evidence completion. One new static acquisition is
authorized here; no repeated proof, counter reset or extra credit for retries,
cleanup, tests or evidence commits. Extraction and execution remain disabled.
