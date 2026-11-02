# Candidate-Bound ELF String-Table Cost and Reachability Admission Design

## Approved scope and baseline — 2026-11-02

User approves repository-only design through validation, commit, push and
exact-head ordinary CI. No parser, collector, graph, workflow, test expectation,
acceptance limit, candidate acquisition, extraction, image loading or execution
changes are authorized by this design. No SQL, LocalWP/live/installed application,
credential/permission, schedule, production wiring, package or release operation.
This is R1 documentation of a proposed sensitive admission policy, not permission
to implement that policy or increase the repository's autonomous risk tier.

Restore/review baseline is clean/synced **37a2067f1c93264f2fb9f2c9f44391444d8a128c**;
[exact CI41451237060](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41451237060)
is reverified successful. Campaign starts and remains **7/10 product milestones,
7/10 native attempts, six static acquisitions**. Design/evidence commits do not
earn qualification credit. Uploader and toolkit remain untouched.

## Evidence and actual ownership trace

The [owned diagnostic41450662313](SOURCE_POLICY_UBI_ELF_STATIC_ADMISSION.md#actual-string-table-diagnostic-and-cleanup-result)
observed DT_STRTAB and DT_STRSZ present, declared size **1,084,894 bytes** versus
the enforced **1,048,576-byte** cap for `usr/lib64/libmariadbd.so.19`. It failed
at layer6/cumulative archive entry3714, after six complete layer/model commits.
Cleanup completed, artifacts zero. The seventh layer/final namespace/selected
roots/provider graph were not qualified. This does not prove that the table is
otherwise valid, that the library is required or unused, or that the candidate
would pass any proposed alternative. No artifact bytes remain for this review.

Source boundaries examined at the baseline:

- [ELF parser](../scripts/manual/source_policy_ubi_elf_static.py): `parse` checks
  the extent cap, maps the complete table to one file-backed load segment, then
  creates `strings = data[mapped[0]:mapped[0]+tags[10]]`. For a bytearray body this
  copies the complete declared extent. `text` subsequently searches only a
  241-byte window per requested offset and validates at most 240 ASCII bytes.
  Its work counter charges a text request, not each searched/copied byte.
- [Collector](../scripts/manual/source_policy_ubi_alias_static.py): `collect`
  retains one eligible body at a time, parses every eligible ELF encountered,
  and aborts on the first parser refusal. Only complete compressed/diffID
  verification returns staged observations; it has no unavailable-object record.
- [Fixed entry](../scripts/ci/source-policy-legacy-ubi-static.py): constructs
  the candidate-bound model, charges returned parse work before each model
  commit, merges observations, finalizes all layers, resolves both fixed roots,
  then calls `graph`. The actual failure occurs before this rooted phase.
- [Graph/model](../scripts/manual/source_policy_virtual_closure.py): final-origin
  and snapshot checks, fixed providers/SONAME, alias bounds, work/role/edge limits
  and cycle refusal remain. No installed loader or host search participates.

Thus declared extent and bytes actually required for dependency-name access are
different cost quantities. That observation motivates a new bounded-access
profile; it is **not** authority to remove the old guard or reinterpret its
1MiB extent limit as if it had always meant only copied bytes.

## Decision

Prefer a **socket-free, default-off bounded requested-string access foundation**
first. Do not raise the existing cap to a value just above the measured object,
and do not introduce reachability-based suppression of parser refusals.
The existing strict parser/default consumer continues to refuse the same object.

A future explicitly selected profile may permit a declared table extent above
1MiB **only when the entire extent is uniquely file-backed inside the existing
bounded ELF body**, while prohibiting a whole-table copy and charging bounded
string operations. This is a declared-extent **acceptance-policy change**, even
though it does not increase the existing body or aggregate work ceilings. It
needs implementation approval and later separate fixed-owner integration and
fresh artifact qualification. The present design changes none of those paths.

Re-use the cohesive parser after explicit approval rather than fork its ELF
validation logic or rename/split unrelated classes. A pure typed opt-in or
dedicated private helper can serve synthetic tests, but existing calls and the
shared strict reader must retain their current defaults and output contract.
The fixed CI entry must not start passing an opt-in as part of the foundation.
No user-supplied callbacks, paths, provider search roots or IO-capable handles.

A future real consumer must still verify the existing exact manifest/config
pins and both fixed root content hashes; a test API, opt-in boolean or copied
receipt cannot attest that identity. Do not special-case the observed filename
or hardcode1,084,894 as a new threshold. Only a separately reviewed fixed owner
may select the new profile on the same pinned candidate after ordinary CI and
fresh qualification approval. Future changed source-cohort bytes stale affected
future receipts; this docs-only design does not alter or rerun old source facts.

## Proposed bounded-access contract (not implemented)

The ABI describes DT_STRTAB as an address and DT_STRSZ as its byte extent, with
DT_NEEDED/DT_SONAME values indexing strings in that table. This separates extent
validation from individual string access; it does not establish our candidate's
validity. [ELF dynamic entries](https://gabi.xinuos.com/elf/08-dynamic.html)

1. Preserve header/architecture, program-header, dynamic-segment, duplicate-tag,
   terminator, needed-name count and all existing interpreter/feature checks.
   DT_STRTAB/DT_STRSZ must be present where the parser requires string access.
   Reject zero extent, unsigned64 wrap, truncated or ambiguous mappings. Require
   a single complete **file-backed** mapping for the whole declared extent;
   memory-only tails are not file evidence. Use subtraction-based bounds before
   forming file offsets. The entire span must fit the actual body, already
   limited to 128MiB. Do not rely on section headers, host virtual memory or a
   mapping that covers only requested names.
   [File versus memory segment sizes](https://gabi.xinuos.com/elf/07-pheader.html)
2. Keep only an integer base/length view during parsing, not a copied table.
   The already owned body stays local; return no view, buffer, path or handle.
   Check the table's two boundary NUL bytes with bounded direct access in the
   new profile. This is not a scan or validation of all interior strings.
3. For each typed offset require `0 <= index < extent`, compute a search window
   no larger than `min(241, extent-index)`, and search the original body only
   within that window. Never clamp an invalid index into range, extend to EOF,
   decode a suffix of the entire table, or search until an unbounded terminator.
   A NUL beyond the declared table does not terminate an in-table string.
4. Copy/decode only the located payload, at most 240 bytes. Preserve ASCII,
   library grammar, order, duplicate-needed refusal and existing empty-string
   rules. Offsets into a suffix are legal; do not demand a preceding NUL or
   invent alignment requirements. The format allows repeated and unreferenced
   strings; this profile examines requested names, not every symbol name.
   [String-table indexing and boundaries](https://gabi.xinuos.com/elf/04-strtab.html)
5. Keep body SHA256/size and primitive observation binding unchanged. A digest
   of requested names is not a substitute for the full content origin. Parser
   output still cannot establish provider closure, ABI/symbol versions or
   execution permission. Any proposed access-cost receipt must be versioned
   DATA with a fixed schema, no raw strings, addresses or arbitrary errors.

Do not silently add fields to the exact existing graph-observation schema.
The pure foundation must keep semantic metadata and any access-profile cost
evidence distinct; the existing `work` field can report the fully charged count,
but a new receipt is not input authority for old consumers. Graph admission of
any new profile/receipt stays disconnected until separately reviewed integration.

The proposed profile proves a bounded subset of static name interpretation,
not full ELF conformance, relocation correctness or loader behavior. Existing
ADBUELFST1 receipts continue to describe the **strict** extent guard; they must
not be relabeled as successful bounded-access admission or silently change
their recorded limit. New refusal/receipt categories need explicit tests.

## Resource and accounting obligations

| Quantity | Enforced now | Proposed opt-in obligation |
|---|---|---|
| Whole ELF body | At most 128MiB | Unchanged; one already collected body |
| Declared string extent | At most 1MiB | Complete unique file span, at most body size; explicit new policy |
| Additional full-table copy | Up to 1MiB | Zero |
| Dynamic segment / program headers | 1MiB / 128 | Unchanged |
| Needed / SONAME / path requests | 64 / 1 / 2 | Unchanged; at most 67 string requests |
| Per-request search / payload | 241 / 240 bytes | Preserve and enforce before access |
| Observations / encoded metadata | 256 / 2MiB | Unchanged, including discarded/replaced bodies |
| Shared work / deadline | 2,097,152 / 300s | No reset, refund, new ceiling or bypass |
| Graph edges / resolution roles | 4,096 / 256 | Unchanged; fixed roots also consume roles |

Derived name-access upper bounds per object are **16,147 searched-window bytes**
and **16,080 payload bytes**, plus bounded ASCII checks and two endpoint reads.
These are conservative logical bounds, not measured native memory/timing proof.
They exclude the existing bounded body hash/stream cost and ELF table traversal,
which must remain under their own byte, work and deadline guards. The original
128MiB body allocation remains; removing a table copy is not an RSS guarantee.

A proposed local cost meter must accept only typed finite remaining units and
reserve each operation's worst-case window/copy/check cost **before** performing
it. Charge mapping tests, dynamic/header traversal and requested-string work;
do not charge one unit per string while ignoring its byte work. No callbacks or
independently reset per-name budgets. Failures retain bounded consumed-cost
facts and their first cause; diagnostics cannot grant a refund or readiness.

Future fixed-owner integration must reconcile parser consumption with the same
combined namespace/graph work authority before the next grant/commit. Current
collection returns successful work and the entry charges it after collection;
that existing arrangement is not proof of a new byte-level pre-admission meter.
Do not claim the pure foundation establishes owner-level aggregate accounting.
Synthetic sequential grants must prove no double counting, omitted charge or
refund; actual fixed-owner wiring requires its own review before qualification.
In particular, 256 times 16,147 exceeds the shared 2,097,152 work ceiling: an
otherwise structurally valid candidate may still refuse on work exhaustion.
Do not raise a ceiling merely to fit the sum or a measured job.

## Reachability decision and refusal scope

Keep the current eager observation and fail-first collector. Do **not** decide
from a filename that `libmariadbd.so.19` is optional. The interrupted run provides
neither selected-root dependencies nor a final provider map for that question.
The new string-access foundation is useful regardless of this object's future
reachability and does not require a quarantine architecture to test its cost.

Deferring unavailable-object failures would require new staged outcome types,
complete-layer/final-origin binding, shadowing/hardlink handling, cost accounting
for failed parses, and a final traversal that refuses if any required object is
missing or unavailable. Those are real changes to admission, not a harmless
exception handler. They are **deferred**, not reopened or authorized here. Never
drop a failed observation and let missing evidence imply an empty dependency set.
No partial namespace, copied receipt, synthetic unavailable owner or caller-
asserted reachability may authorize success. If bounded access still refuses a
real object later, inspect that evidence before selecting another policy change.

All original provider directory/SONAME/alias, graph-cycle, RPATH/RUNPATH and
unsupported-feature rules remain. Even a later complete DT_NEEDED graph cannot
prove NSS/FIPS/dlopen/plugins/shutdown closure, runtime grants or legacy M native
qualification. No host library/cache/environment fallback or image substitution.

## Required non-vacuous implementation evidence

Before any future consumer wiring, synthetic tests must discriminate:

- strict/default rejection versus explicit opt-in access to an actually mapped
  1,084,894-byte table; ordinary small and exact1MiB positives stay covered;
- maximum bounded body/extent arithmetic, uint64 overflow, EOF/memory-only and
  overlapping/ambiguous segment refusals without any out-of-body access;
- index zero, final-byte, suffix/repeated references, and a NUL only outside the
  declared extent; empty rules and exact240-byte positive/241-byte refusal;
- large unused interiors with access counters proving no full-table slice,
  allocation, decode or linear scan; endpoint and mapping checks still occur;
- exact67-request boundaries, byte/copy/check charges, typed remaining budget,
  deadline expiry and sequential exhaustion without reset/refund/double charge;
- malformed receipt/profile inputs and no view/handle/IO/process escape;
- unchanged SHA/size/origin/SONAME/search/cycle defenses and same first refusal
  if diagnostics fail; no artificial success or skip for an unavailable owner.

Mutation controls must kill removal of the opt-in, extent mapping, NUL/window,
pre-charge and origin guards at the layers that actually enforce them. Existing
twenty groups/eight controls are historical baseline coverage, not evidence for
the not-yet-written access profile. Isolated tests cannot establish actual
candidate compatibility, physical memory bounds, WordPress authentication,
database behavior, native cleanup or release readiness.

## Publication and exact next gate

This change contains documents only; all parser/collector/model/entry/workflow,
tests and dependencies must match the baseline byte-for-byte. Validate owning
links/diff boundaries and relevant existing isolated checks; publish one design
commit and verify its exact-head ordinary matrix. No manual qualification job.
Local/CI results are recorded only after execution. No historical whole-plugin
review rows or source-scoped native receipts are refreshed by this design.

Executed local validation: `php vendor/bin/phpunit --do-not-cache-result`
(with the existing Python runtime setting) passes **2,599 tests / 23,408
assertions**, no failures/errors, two existing Sodium-unavailable skips.
`npm run check:structure`, affected local-link checks and `git diff --check`
pass. A baseline diff excluding `docs/**` is empty: no code, tests, workflow,
pins, dependency or active acceptance changes. No new profile tests or candidate
evidence are claimed. Exact pushed-head CI is a publication gate; its actual
run identity/results belong to that commit's checks and the final handoff, not
an assumed future result in this design. No manual workflow is dispatched.

**Recommended next gated slice: Local-only Socket-Free Candidate-Bound ELF
String-Access and Cost Admission Safety Foundation.** Approve the pure, dormant
explicit-opt-in implementation and synthetic byte/budget tests through review,
validation, commit, push and exact-head CI. Keep strict defaults and all current
collector/fixed-entry/workflow wiring unchanged; no acquisition, extraction,
image loading, native execution, SQL, site operation or production wiring.
This is the narrow proposed declared-extent policy exception to implement and
test, not authority to change the active 1MiB guard or count a product milestone.

Implementation-plan progress: the cost/reachability decision is specified; real
legacy M admission remains blocked, campaign **7/10** milestones unchanged.
Proceeding to the foundation crosses from the approved design into new parser
policy implementation and requires approval. No other implementation slice or
generic cleanup queue is selected to fill the campaign ceiling.
