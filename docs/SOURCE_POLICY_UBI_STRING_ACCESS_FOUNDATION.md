# Socket-Free Candidate-Bound ELF String-Access and Cost Admission Foundation

## Approval and bounded plan — 2026-11-02

User approves the [design's exact next foundation](SOURCE_POLICY_UBI_STRING_ACCESS_ADMISSION_DESIGN.md)
through implementation, synthetic validation, review, commit, push and exact-head
ordinary CI. This is the narrow R3 untrusted-binary admission-policy foundation,
not a general risk-tier increase or permission to acquire/load/execute artifacts.
No collector/fixed-entry/workflow, graph/provider, strict shared reader, runtime
PHP, storage, site, credential/permission, schedule, release or production change.

Restore/review baseline is clean/synced **d4c93fcc61d6ca7d1c6a4bd1d3d727115e2ccb09**;
ordinary CI41459169859 is reverified successful. Campaign remains **7/10 product
milestones / 7/10 native attempts / six static acquisitions**. A dormant safety
foundation is not another completed qualification milestone.

Plan: add red synthetic contracts; re-use the parser under an explicit default-
off string-access profile and typed remaining-work grant; validate the complete
file-backed extent without a table copy; pre-charge bounded name access and
preserve strict defaults/first failures; keep metadata distinct from a versioned
cost wrapper; exercise boundaries, no-effect and mutation controls; full local
QA, scoped diff review, publication and exact-head CI. No manual workflow dispatch.
Pure work grants/receipts cannot attest candidate identity or establish fixed-
owner aggregate accounting; later integration still needs its own approval.

## Implemented dormant API and unchanged strict owner

`parse(data, deadline=None, *, bounded_strings=False, remaining_work=None)`
reuses one private parsing core. Default calls still use the original1MiB extent
guard, whole-table copy, refusal diagnostics and metadata/work semantics. Existing
collector `elf.parse(body, budget['deadline'])`, fixed CI entry, workflow, graph,
shared reader, provider/alias rules, pins and runtime PHP are byte-identical to
the baseline. No consumer passes the new keyword and no manual job is dispatched.

The explicit opt-in requires an exact boolean and an exact integer work grant
between0 and2,097,152. Non-finite/hostile deadline/grant/profile values are refused
without conversion magic. No paths, callbacks, loaders, IO handles or alternate
providers enter this API. The opt-in may accept an extent above1MiB only when its
entire span is uniquely file-backed inside the existing at-most128MiB body.
Unsigned address wrap, zero extent, EOF/memory-only or ambiguous mappings refuse.
The new profile checks both boundary NUL bytes, then holds only base/length ints.

Each requested index must lie within the extent. A search reserves at most
`min(241, extent-index)` bytes before calling the exact buffer's bounded `find`.
Only the located at-most240-byte payload is copied, checked and decoded. Suffix
references and existing empty/name/duplicate rules remain. There is no full-table
or suffix allocation, table-wide decoding or new scan of unused string contents;
the pre-existing complete-body SHA256 computation remains and is not omitted.
Existing128-header/1MiB-dynamic/64-needed/two-path/256-object/4096-edge limits stay.
The1MiB cap remains active in every actual collector path; this new declared-
extent acceptance policy exists only in the disconnected explicit opt-in.

## Cost admission, failure receipts and authority limits

One private meter pre-admits structural, mapping, search, copy, ASCII-check,
decode and boundary costs against the supplied grant before those operations.
Copy/check/decode are reserved atomically, so a denied final byte cannot leave
an unreported half-reservation. Interpreter access and bounded name validation
also reserve structural cost; header/dynamic/mapping traversals are accounted.
The meter has a maximum67 name requests, the same2,097,152 work ceiling and
deadline checks, including a final check before reporting completion. No per-
request resets/refunds or additional ceiling is introduced. This is a defined
logical operation/byte reservation model, not exact CPU instruction accounting.

Success returns an **ADBUELFSA1 wrapper**, not an old graph observation:
semantic metadata (with fully charged `work`), separate ADBUELFCOST1 reservation
facts, and runtime_grantfalse. The fixed-schema cost validator requires typed,
bounded scalar fields; counters sum exactly to consumed units, consumption does
not exceed the grant, and request/span/byte relationships must agree. Reserved
window/copy/check/decode bytes are conservative allowances, not physical read
telemetry. Every receipt is copied as primitive DATA, never a live meter/buffer.
Unknown keys, malformed totals, wrong flags or hostile types are withheld.

On refusal, bounded incomplete cost facts are attached without replacing the
first error. A diagnostic failure retains that same exception with unavailable
facts rather than logging arbitrary errors. Strict ADBUELFST1 diagnostics and
their original limit are unchanged. A new wrapper is rejected by the existing
exact graph-metadata schema; test code deliberately inspecting its metadata does
not establish a real consumer integration or independently trusted provenance.

The module and private helpers are **not** cryptographic seals, authentication
boundaries or write capabilities. A caller can invent a DATA grant or edit a
DATA receipt. Only later fixed-owner admission can supply the real remaining
aggregate allowance, bind source/profile and immutable content origins, debit
success/failure without refund, and reconcile namespace/graph costs. This work
does not implement or claim that owner-level authority. No real candidate has
been accepted, no actual global budget reconciled, and the library's rooted
reachability remains unknown. Extraction/execution/runtime grants stay disabled.

## Non-vacuous synthetic validation and review

All twelve new groups initially error because the profile/meter/receipt API is
absent. With implementation they pass, as do all twenty predecessor ELF groups
under `-W error::SyntaxWarning`. Related PHP wrappers/static contracts pass
12 tests/73 assertions before the final full QA run. Tests cover:

- actual mapped1,084,894-byte positive opt-in and default strict refusal;
  small/exact1MiB semantic equivalence apart from deliberately charged work;
- full128MiB body/span positive and one-byte body overflow refusal, plus uint64,
  EOF/memory-only/ambiguous mappings and boundary NUL failures;
- empty/last/invalid offsets, suffixes, exact240-byte positive/241-byte refusal,
  64/65-needed and duplicate/name/search/unsupported-feature defenses;
- all67 requests and the68th refusal, exact/one-short and sequential grants,
  virtual-clock expiry after partial progress, no refund/reset assumptions;
- malformed profile/receipt/grant inputs, cost-copy isolation and no handle,
  host IO, networking or process calls during the actual parsing/receipt path;
- same first refusal when cost diagnostics fail, and original content-origin
  rejection rather than trusting a cost receipt or unavailable owner.

An isolated `tracemalloc` regression on a6MiB unused table requires under128KiB
of traced temporary Python allocations; a full-table/suffix copy would fail it.
It excludes the already allocated body and does **not** certify native RSS,
allocator internals, wall time or worst-case loader/CPU behavior. Baseline graph
fixtures use existing owned synthetic gzip/temp-file helpers; the no-IO claim
is scoped to the actual opted-in parser/receipt calls, not source loading or all
test-fixture setup. No candidate archive, WordPress, database or installed state.

Seven assertion-killed controls cover actual default enablement, complete-extent
mapping, pre-charge, window reservation, actual find-bound bypass, first NUL-refusal cause, and origin
binding. The default/mapping controls discriminate actual parser acceptance;
window/pre-charge/origin controls are scoped to their actual enforcing functions.
The NUL control kills loss of the first refusal even though an independent
negative-cost defense still refuses; this is not misreported as a whole-owner
bypass. Controls modify trusted test code in memory only, never source files or
candidate code. No skips, artificial success paths, relaxed thresholds or fake
runtime-ready owner are introduced. Existing downstream defenses remain.

Test-only in-memory AST witnesses instrument the exact bounded-name helper,
delegate to the real buffer `find`/slice operations, and record their actual
arguments. They verify no find before its grant, no payload slice before the
atomic copy/check/decode reservation, and a241-byte search even when the first
NUL is much farther away. The find-bound control removes that actual argument
bound and is killed by its observed call, despite independent length defenses.
No witness callback or alternate buffer type is added to the production API.

Source-scoped review fromd4c93fc covers the shared-core refactor, default/opt-in
selection, bounds/byte-copy ordering, grant/receipt invariants, diagnostic errors,
test integrity and all directly owning records. No public production/API,
credential/data/schema, dependency/compatibility, remote/schedule, release or
whole-plugin readiness evidence is changed. This is not native/Database Review.

Final local validation after the witness additions: `php vendor/bin/phpunit
--do-not-cache-result` with the existing Python runtime setting passes **2,601
tests / 23,419 assertions**, no failures/errors, two existing Sodium-unavailable
skips. PHPCS255 files, Node74/74/no skips, structure, build, Composer strict
validation, Composer audit and npm audit pass; both audits report zero
vulnerabilities. Affected local links and `git diff --check` pass. Explicit
baseline comparisons confirm real consumer/CI/graph/runtime/dependency bytes
are unchanged. No manual proof is performed or inferred from this QA.

Exact pushed-head ordinary CI is the publication gate; actual run identity and
matrix results belong to that commit's checks and the final handoff, not an
assumed future result here. No toolkit/uploader change, release or deployment.

## Exact next gate

**Recommended next gated slice: Repository-only Candidate-Bound Fixed-Owner
String-Access Cost and Origin Integration Design.** Approve design-only closure
of the one source-owned remaining-work grant, source/profile identity, complete-
layer/final-origin correlation, failed-cost debit and combined model/graph
accounting before an actual collector may select the profile. Keep current
collector/entry/workflow behavior unchanged, with no acquisition, extraction,
native execution, site work or production wiring. This is the authority gap
left deliberately unproved by pure synthetic work, not a request to run a job.

Implementation-plan progress: bounded string access now has a dormant tested
foundation, but legacy M actual admission/native qualification remains blocked.
Campaign remains7/10 milestones/native7/10/six static acquisitions. No credit
for this intermediate foundation, tests or evidence closeout. Historical failed
receipts retain their original source; new module bytes require fresh affected
cohort evidence after separately approved real integration, not a copied result.
