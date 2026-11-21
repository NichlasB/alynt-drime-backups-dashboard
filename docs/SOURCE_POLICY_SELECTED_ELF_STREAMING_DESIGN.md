# Selected-ELF Range Inventory Feasibility and Memory-Proportionality Design

## Shared walker and dormant replay preparation implemented — 2026-11-21

Explicit follow-up approval covers extracting the shared tar walker in
source_policy_deb_data.py plus the dormant replay/commit-barrier coordinator,
synthetic tests,validation,commit,push and exact-head CI;legacy behavior and
disabled production wiring preserved. The preceding hour's attempted edit was
permission-rejected before application;no workaround or source change occurred
until this explicit approval. R2 bounded repository preparation,not artifact/
native/site operations. Restore/review baseline30ca1dcedb788204c7a9a61eeea6d81dee117a67
clean/synced master,ordinary44901933213 success;toolkit141d5546b98972b659cca78033f7f51352326364.
Review boundary is this complete shared-parser/coordinator/test delta,not merely
the later evidence commit. Product4/10/native43 and exhausted3/3 unchanged.

### Shared policy and separate preparation surface

- D.walk_tar is now the ONE strict parser used by legacy tar_inventory and the
  dormant coordinator. Header/start/chunk/end events are internal progress,NOT
  complete-scan receipts. Exhausting tar terminators/trailer/EOF remains required.
  Original header/type/name/duplicate/size/padding/link/control/role policies and
  cross-package guard ordering remain;legacy selected-buffer allocation,S.elf,
  canonical digest and return shape remain unchanged. The old manual runner
  still imports tar_inventory,not the new coordinator or range collector.
- New scripts/ci/source_policy_deb_replay.py has NO CLI,acquisition,file-open,
  installation or production caller. prepare accepts only an exact tuple of
  three exact immutable bytes objects;no caller pins,readers,callbacks or ready
  flags. All fixed catalog sizes/SHA256 pass before ANY nested parser. Tests
  patch synthetic catalog bytes only inside isolated fixtures,not via an API.
- A private read-only cursor borrows immutable input without whole-input copying.
  This is a preparation adapter,NOT an on-disk ownership/stability factory or a
  proposed whole-artifact storage model for the eventual low-memory proof owner.
  The retained caller inputs are outside scratch accounting and do NOT prove fit.
- All scans use the same walker and original Budget. Whole-source rehashes,
  compressed/decoded bytes,skipped data and replayed headers/entries are charged;
  no per-pass credit/reset. Canonical paths/types/inventory register PRIMARY only.
  Replay stream length/SHA256,entry count and normalized ledger must all match.
  Selected members additionally obey the unchanged Collector's full-body hashes.
- At most one package/two selected roles is active and at most3 data scans occur.
  Finished collectors are not re-fed while a sibling still needs another phase.
  Same original AR slice/codec per replay;control is primary-only. Return waits
  for full trailer/EOF,slice consumption,clean decoder exit and owned reaping,
  matching replay transcripts,all packages/all3 roles and zero retained scratch.
  Private provisional metadata never reaches a caller on failure.
- Collector and control buffers reserve the shared1MiB scratch counter BEFORE
  allocation,release only their own reservation on success/failure,and never
  refund work credit. Existing64KiB chunks/control/output,192MiB work,8192 entries,
  540s work and original decoder limits remain. Input storage,IO/JSON temporaries,
  Python object/import/allocator costs and full process memory are NOT measured
  by this counter;no physical-fit or all-process accounting claim.
- Returned source_profile is immutable-input-bytes-only,prepared_only:true.
  static_inventory_complete/qualified/executed/extracted/dependency closure all
  remain FALSE. No real artifact,descriptor ownership,40/24MiB residency,HTTP,
  SQL,authentication,versioned closure or runtime qualification follows.

### Actual scoped tests and reviews

Before extraction,captured a legacy mixed regular/directory/link witness:
4 entries,4096 charged bytes,types30:2/32:1/35:1,inventory SHA256
15ba01c96c2fefea93608d4ea28623bb35f13292d281d6f0ad18ed64565b09af.
Permanent assertions preserve that exact baseline and cross-package guard order.
Preparation also matches legacy full role facts,primary inventory and types with
nonempty ordered needed/search paths. The sample has7 primary entries and15
scanned entries,passes3/1/3;an independently calculated byte ledger includes all
whole-source rehashes and replayed streams,not just a permissive upper bound.

Local new Python corpus18 groups,0 failures/errors/skips;related PHP bridges9
tests/74 assertions,plain OK/no skips. Seven restored semantic controls are
assertion-killed (not setup errors):whole-stream identity,all-pin admission,EOF
ordering,decoder release,required role set,work/deadline and entry budgets.
Malformed tar/checksum/padding/trailer/type/path/duplicate/selected-link inputs,
source recheck refusal,unselected replay mutation,partial-event termination,
role/path collisions,allocation/parse cleanup,three-pass limit and64KiB output
refusal covered. Input type/subclass/conversion-hook and cursor boundaries covered.
Windows gzip close/exit tests are explicitly stubbed API evidence,NOT native
decoder proof. Ordinary Linux CI adds one synthetic existing-tool gzip/xz/zstd
pipe/reaping group (19 total),not candidate execution or a physical-fit proof.

Canonical Feature Light SOURCE-SCOPED:4 source/test files plus8 owning records,
unchanged manual runner/codec/oracle/workflow consumers. One resource finding:
control buffer was bounded but not reserved in shared scratch;the new regression
first failed with a missing-reservation assertion,then passed after reservation/
finally-release correction. One initial identity control replaced the parser
with a Mock and was not assertion-killed;changed to a wrapping spy,not a weaker
gate or artificial success path. No unresolved non-security issue. Bloat/UI
workflows unnecessary:cohesive common parser/coordinator/corpus/small bridge,
no new UI,dependency,tooling framework or arbitrary file splitting.
Final Feature Security SOURCE-SCOPED:typed closed inputs,pre-parser identity,
unchanged path/type policy,whole-stream/member consistency,publication order,
budgets and owned cleanup checked;no confirmed security defect remains.
No whole-plugin DS3/Database Review/physical runtime/release-readiness refresh.

Local full PHP3077 tests/25402 assertions,0 failures/errors,2 independently
confirmed pre-existing Sodium-unavailable Windows signer skips. Final related
bridges9/74,no skips,also cover the last decoder-close failure/outer cleanup
strengthening. Node295/295,no failures/skips;PHPCS305/305,PHP syntax,structure,
in-memory Python compile,diff checks and408 local document paths passed.
Actual exact-head full CI evidence belongs in the scoped toolkit closeout.
Existing source-bound native/static evidence remains HISTORICAL after the shared
parser source change;ordinary tests do not requalify those old physical receipts.
The manual workflow already binds D's path;R/E remain absent because not loaded.
Before any future caller/dispatch,the exact source closure must include R/E.
Next gated:Repository-Only Fixed Read-Only Descriptor Source Admission and Replay
Owner Integration Design. Establish the actual descriptor/root/identity/cleanup
contract and aggregate memory plan before implementing a real-source factory or
requesting any NEW static attempt. Existing3/3 remains exhausted,wiring disabled.

## Approved socket-free collector foundation implemented — 2026-11-21

User explicitly approved Repository-Only Socket-Free Selected-ELF Range Collector
and Oracle-Parity Safety Foundation through validation/commit/push/exact-head CI,
synthetic DATA only. R2 bounded parser code; no artifact acquisition/execution,
owned replay/IO integration,site/database/native operations or production wiring.
Restore/review baseline8b42cc098832ac4fbc99221f28eb0beb7a5c241c,clean/synced master,
ordinary44898334775 success;toolkit031b09315ad367dc11d0e978d832af532bc40e75.
Review boundary is this complete new primitive/test delta against8b42cc0,with the
unchanged shared oracle and DEB owner/decoder/manifest consumers inspected,not
just a later documentation commit. Historical design and frozen source IDs below
remain unchanged;source counters3/3 exhausted/native43/product4/10 not reset.

### Dormant API and proof boundary

- New scripts/ci/source_policy_elf_ranges.py imports only hashlib/struct; no IO,
  callbacks,code loader,source paths,network,archive materialization or execution.
  Existing DEB runner/walker/codec/workflow/root plugin do NOT import it. Their
  source-identity closure is unchanged because this helper is not loaded there.
- Collector(size,budget=None) accepts exact bounded integers and an optional
  exact Budget. feed(offset,chunk) accepts only contiguous exact ints/bytes,
  nonempty chunks<=65536 and a member<=16MiB. No supplied hashes/range plans/
  readiness flags;invalid input or parse/scan failure is terminal.
- finish_scan() advances only primary -> metadata -> strings -> done,skipping
  empty phases where the actual plan permits. Each scan must consume the ENTIRE
  member;every replay SHA256 must match the primary,including unselected bytes.
  No fourth scan/retry on the same collector. result() before done,or input after
  done,refuses terminally. Returned lists are fresh copies of private tuples.
- Primary retains64 bytes plus<=7168 program-table bytes. Metadata retains only
  bounded interpreter spans plus a16-byte dynamic carry,with capped offsets/tags.
  Strings retain at most66 windows of<=241 bytes,in semantic rather than physical
  order. Hashes cover all fed bytes. No whole-member buffer or fabricated ELF.
- One shared Budget accounts owned retained bytearrays under1MiB and cumulative
  fed bytes under192MiB;reserve precedes allocation and cancellation/failure
  releases only that collector's reservations. Work credit is never returned.
  Caller chunks are separately bounded64KiB;Python object/import/allocator costs
  are NOT measured by this accounting. A later fixed owner must share the Budget
  across its collectors AND retain the original global compressed/decoded-read,
  entry/deadline/process/decoder limits. This counter is not their substitute.
- Result matches S.elf's member-level keys,including ordered duplicates,full body
  hash/size and false executed/dependency_closure_verified. The later unchanged
  DEB owner adds versioned_symbols:Unknown;this primitive grants no artifact,
  package/tar/decoder/source provenance,inventory completion or runtime admission.

### Source-scoped checks and reviews

Permanent Python suite:21 groups,0 failures/errors/skips. Exact differential
positives include nonempty needed/search paths/interpreters and full hashes;
zero/late/overlapping/max128 PHT;128 interpreter spans;first NULL;unused large
STRSZ;duplicate/order behavior;empty/240/241-char strings;64/65 needed and2/3
paths;all segment extents;replay tail mutation;partial scans/results;wrong types,
subclasses and hostile conversion objects;shared reservation ownership;allocation
failure/cancellation;max16MiB synthetic body;80 seeded positive/mutated pairs.
Chunk partitions1/7/63/64/511/512/4093/65536. Seven isolated,restored semantic
controls assertion-kill replay identity,full-scan completion,ordering,scratch,
work,result-availability and segment-bound bypasses,with zero setup errors.
No source mutation files,network or candidate bytes used by this suite.

Initial new-suite run had three fixture-construction errors (offset overlap,
string index and header overlap) plus one non-discriminating control;corrected
the fixture/control,not the unchanged oracle or accepted policy. This is not
red-to-green evidence of a previously deployed production defect. Permanent
guards are instead supported by the seven assertion-killed counterfactuals.

Canonical Feature Light SOURCE-SCOPED:3 new source/test files plus owning records
and unchanged direct consumers. One minor transient-copy concern was removed by
decoding bounded memoryviews rather than slicing bytearrays;no contract changes.
No unresolved non-security issue. Bloat workflow unnecessary:one cohesive dormant
phase machine,one synthetic corpus and one small PHP bridge;no unrelated split,
debug layer,outsized PHP/JS/CSS growth or UI. Resource/edge/adversarial inspection is
focused engineering review,NOT a refreshed whole-plugin DS3 06/07/07A result.
Final Feature Security SOURCE-SCOPED:exact inputs,pre-allocation bounds,all file
extents,hash/phase/commit barriers,terminal refusal and reservation ownership
checked;no confirmed security defect remains. No WordPress auth/storage/runtime,
Database Review,physical memory fit or full release readiness claimed.

Existing-reader bridges plus new bridge:8 PHP tests/69 assertions,plain OK,
no skips. Local full PHP:3076 tests/25397 assertions,0 failures/errors,2 existing
Sodium-unavailable skips (RemoteActionSigner round-trip/invalid-private-key),
confirmed separately;not a no-skip full-local claim. Local Node295/295,no skips/
failures;PHPCS305/305,PHP syntax/structure/in-memory Python compile/diff checks
passed;401 local document paths resolved. Actual exact-head CI counts belong in
the canonical toolkit closeout,not inferred from synthetic group counts.
Next gated:Repository-Only Selected-ELF Replay and Commit-Barrier Integration
Preparation,isolated IO/synthetic codec fixtures only. Existing3/3 stays consumed;
actual acquisition/static resource qualification still needs fresh explicit
authority AFTER integration and ordinary gates. Production wiring remains off.

## Authority, baseline and decision — 2026-11-21

User requested another hour after the exhausted DATA campaign. This continuation
is the recommended repository-only DESIGN slice,10:56:36–11:56:36 UTC
(11:56:36–12:56:36 Paris),including validation,commit,push and exact-head CI.
No renewed acquisition allowance:previous campaign3/3 and all older allowances
remain consumed;native43/product4/10 unchanged. No implementation,artifact
download,manual dispatch,OS/native experiment,materialization,installation,
candidate/PHP/module/SQL/site execution,production wiring or limit increase.
R0 source/primary-documentation review and R1 design/evidence publication only.

Restore/freeze baseline90934a0ee8ef1a84a6f6b69092dcc565b1bf627b,clean/synced
master;ordinary44895522138 freshly reverified completed/success. Toolkit
44454b7d9cc2c72a30bceb22b95ff2e488180b5a clean/synced;uploader unchanged.
This is a new design delta,NOT a claim that the last docs commit represents the
whole implementation boundary. Existing implementation spans51b615c..f55725f;
[actual three-run failure/cleanup evidence](SOURCE_POLICY_PPA_DEB_STATIC_FOUNDATION.md)
remains authoritative. All three pinned byte identities passed each attempt;
the last still had tar-inventory MemoryError with transport_released:true.
Exact failed allocation,decoded extents and total baseline virtual memory remain
Unknown. Do not retroactively claim a particular allocation caused the failures.

Canonical Git blob identities at the freeze (not Windows newline-dependent hashes):

| Source | Blob OID |
| --- | --- |
| scripts/manual/source_policy_linux_minimum_static.py | ed76a7653c4dd040ab3ae4462f6b7e94ccd90f67 |
| scripts/ci/source_policy_deb_data.py | 57749747baaa8bf63b2ec14b15b085fca1a1483a |
| scripts/ci/source_policy_deb_stream.py | 56ad8360d19b04b334ead58e2b95215aa6b2ba29 |
| scripts/ci/source-policy-ppa-deb-static.py | 1e668897551369278316037916bc85da682d0570 |

Decision:conditional GO for a socket-free selected-ELF range-collector foundation,
using the unchanged S.elf(bytes) function as a differential oracle. Keep that
shared reader and its legacy/archive consumers unchanged. Do NOT yet claim GO
for owned replay integration,actual resource fit or another candidate proof.
Prefer bounded metadata retention plus a finite replay schedule over a new OS
supervisor,toolchain,trust framework or additional failure-diagnostic layers.

## Proportionality and alternatives

Current tar_inventory allocates up to16MiB for one selected member,then calls
S.elf on its complete bytes. An exact buffer removes growth peaks,but does not
bound process baseline plus body below40MiB. The three failures do not prove
which of those allocations exhausted address space. A smaller byte-retention
algorithm is justified;another unchanged attempt or generic GC tweak is not.

- Raising64MiB,redistributing40+24,relaxing8MiB windows or dropping validation is
  outside this design. Existing limits are requirements,not failed-test knobs.
- mmap is not an address-space escape:Linux RLIMIT_AS covers brk/mmap/mremap.
  Anonymous mapping still reserves virtual memory;temporary/memfd/tmpfs member
  staging would also cross the current no-materialization boundary.
  [Linux resource-limit semantics](https://man7.org/linux/man-pages/man2/getrlimit.2.html),
  [Python mapping semantics](https://docs.python.org/3.12/library/mmap.html).
- New readelf/objdump processes,installations,raw ELF staging or candidate
  execution introduce unnecessary consumers/boundaries. Do not run them here.
- A compact rewritten/synthetic ELF passed to the old validator would validate
  manufactured offsets/bytes. Reject that shortcut,as well as hash-only success.
- Self-exec/process-phase handoff could discard allocator state,but adds a new
  ownership/deadline/credit protocol and still does not remove whole-body memory.
  Defer it;no new helper-process or handoff architecture is selected.
- Refactoring the shared S.elf now would widen effects into OCI/archive readers
  and source-identity manifests. Keep it unchanged as an oracle;limit the future
  new adapter to this DEB path,with deliberate differential coverage against it.

## Bounded read plan and commit barriers

All three original DEB sizes/SHA256 MUST pass before nested inspection,as now.
Keep exact package/role/path/control/AR/tar/codec/ownership policies unchanged.
Selected-member hashes are observed full-body identities,not separately pinned
expected hashes. Reading a header to build a provisional plan is NOT admission.
Do not publish any role or complete-inventory result until all required original
bytes,member framing,stream terminators,decoder exits and replay identities pass.

At most one package is active;at most two selected roles exist in a package.
Proposed pure collector consumes exact bytes in contiguous member-relative
offset order,at most65536 per chunk. No coercion/getter/callback/IO/loader from
input,raw buffer export or caller-supplied ready/verified facts. It computes
full-body SHA256/length itself;partial/duplicated/skipped input cannot finish.
Retain only validated planned ranges,not a bytearray sized to the whole member.

| Scan | Retained DATA | Required before moving on |
| --- | --- | --- |
| Primary | First64 ELF bytes and bounded program-header table;full-body hash | Original strict stream/framing and clean decoder exit;private immutable member/source transcript |
| Replay1,if needed | Dynamic16-byte entries and all bounded interpreter spans | Same source,full stream/member hashes,strict framing and clean decoder exit |
| Replay2,if needed | At most241 bytes for each needed/search-path string reference | Same commitments;all oracle checks;complete plan before result publication |

The header supplies the PHT offset/count before the rest of the member is dropped.
Keep its64 bytes so a PHT overlapping the header remains representable;late PHTs
are collected at their declared offsets. PHT/data bounds are checked without
integer wrap. Metadata learned after an earlier region has passed requires a
replay,not omitted validation or speculative whole-body buffering.

Worst case is THREE complete data scans per selected package:one primary and
two replays. No unbounded retry,per-range decompression or per-string rescan.
Common has no selected ELF role and needs no ELF replay;control streams remain
primary-only. Empty phases may be skipped only when the checked plan proves
there are no required spans. A future optional scan-fusion optimization must use
the same validator/commit barriers;it is not required for the first foundation.

Every replay uses the SAME validated read-only owned source descriptor/identity,
fixed AR slice and codec. Recheck fixed full-DEB size/hash before replay,charge
those reads,and refuse changed inode/type/owner/size observations. This retains
the trusted private-job/no-external-writer model;not hostile-host attestation.
Full decoded-stream SHA256 plus each selected full-body hash must agree with the
primary scan before metadata can be committed. Mutations or incomplete scans
refuse,even if a captured metadata window looks valid.

Use one common strict tar walker for primary and replay,not a permissive second
parser. All paths/types/numeric/text/checksum/duplicate/padding/trailer/EOF and
decoder-exit checks remain active. Replays do not re-register canonical paths,
roles or the inventory digest;they verify the immutable primary transcript.
They DO charge all headers,entries,compressed/decoded reads and skipped payloads
to the SAME Budget. Cross-package collision/duplicate-role checks remain primary
requirements,and global all-package success still gates final publication.

## Preserve the existing ELF subset exactly

This is the current reader's subset,not a new claim of complete ELF validation.
Keep returned machine/bits/type/interpreter/ordered needed/search_paths/full-body
sha256/bytes and false executed/dependency_closure_verified semantics. Retain
versioned_symbols:Unknown and no PHP/NTS/API/runtime/relocation/closure grant.

- First7 bytes,minimum64,kind2/3,machine62,width56,count<=128 and complete PHT
  extent are identical checks. Do not add OSABI/section-table/alignment policy.
- Validate EVERY segment's file extent,including unknown types. Preserve file
  size rather than substituting p_memsz;ignore fields the oracle ignores.
- Preserve all PT_LOAD entries,including duplicates,for exact-one string-table
  mapping. Coalescing identical mappings must not turn ambiguity into acceptance.
- PT_DYNAMIC is unique,length<=1MiB,multiple16;first DT_NULL ends interpretation.
  Zero-length dynamic is rejected. Later bytes still belong to the body hash.
- All PT_INTERP spans must validate (1..240,last byteNUL,ASCII). The last in PHT
  order wins,NOT last physical file order. Do not silently reject ASCII controls
  currently accepted there;future runtime-path admission is a separate boundary.
- Preserve DT_NEEDED order/duplicates,max64,and RPATH/RUNPATH order,max2.
  Bound retained offsets/counters even when an over-count will eventually refuse.
- Duplicate DT_STRTAB/DT_STRSZ tags refuse even when no strings are referenced.
  Conversely,unused large STRSZ alone is not newly rejected:the <=1MiB string
  table/mapping requirements are conditional on needed/path references,as now.
- Referenced strings require index<table size,firstNUL within241 bytes and
  printable ASCII. Preserve240/241-character boundary behavior and exact-one
  file-backed mapping,including empty-string acceptance where the oracle permits
  it. Capture min(241,remaining table bytes),not the whole table.
- A replay may collect spans in physical order,but must reconstruct results in
  original PHT/dynamic order. No sorting/deduplication of semantic result lists.

Accepted DATA/structured-result equivalence is mandatory. Single-fault refusal
checks retain their current categories;combined-malformation refusal precedence
may differ only where no security ordering or existing test contract is weakened.
Do not edit the old oracle or expected positives merely to make parity tests pass.

## Resource feasibility,not a physical-fit claim

Unchanged600s job/540s work/30s cleanup,64MiB concurrent40+24 partition,8MiB
decoder ceiling,192MiB work,8192 entries,16MiB member,64KiB diagnostics. Keep
conservative XZ8MiB total decoder bound. One decoder at a time;no third process.
Transport release before inspection and exact existing cleanup remain required.

Illustrative conservative raw reservation subtotal for TWO active roles:
2*(64+128*56+128*240+66*241)+2*16+3*65536+65536+65536+8+60+2*512+18
=436538 bytes. This over-counts simultaneous phase buffers intentionally and
includes dynamic carries,three IO chunks,control/diagnostic bounds and small
framing buffers. It is NOT a measured implementation high-water mark;the remaining
space below1MiB must cover other retained raw scratch/canonical/digest bookkeeping.
Dynamic entries can be consumed with a16-byte carry rather than a1MiB copy.
Plan-derived offsets/counters/segments have independent finite count bounds.

Propose an ADDITIONAL tighter1MiB cap on simultaneously retained raw scratch
buffers,reserved before allocation/released between phases. It is NOT a raised
process budget or a measurement of Python object/import/allocator/registry cost.
Full process address-space enforcement remains40MiB and physical fit Unknown.
No resource guarantee follows from these arithmetic bounds alone.

RAM reduction trades for extra work. With B=fixed4146302 bytes,U=all primary
decoded bytes,R=compressed selected-package slices,V=their decoded data bytes,
and P=selected-package whole-DEB bytes,a conservative cost includes B+primary
compressed reads+U+up to2*(P+R+V),plus AR/frame/marker reads. U/R/V are NOT known
from the failed receipts. Actual reads and all replayed entries are charged;
no per-pass/package/role resets or credits for ignored output. A valid input may
resource-refuse because replay work does not fit192MiB;do not claim universal
resource parity or increase the cap. Decoder/header minimum-cost checks may
refuse before an unaffordable replay. Budget/EOF failure never means success.

## Executed source probes and required future tests

Ran16 in-memory synthetic cases through the UNCHANGED existing S.elf:9 accepted,
7 refused,all expected outcomes asserted;full hashes/false execution checked on
positives. No files,artifact/network/native-candidate operations or memory-limit
experiments. These are spot probes of the oracle,not tests of an implemented
range reader or evidence that a package fits.

| Accepted (9) | Refused (7) |
| --- | --- |
| zero PHT;last interpreter in PHT order;ASCII-control interpreter;unused large STRSZ;first NULL;duplicate ordered needed;240-char string;late PHT/earlier dynamic+strings;PHT overlaps header | zero dynamic;duplicate dynamic;duplicate STRSZ;ambiguous identical LOAD mappings;241-char string;unknown-segment out-of-bounds;65 needed |

Future permanent differential corpus must include these witnesses plus truncated
headers/tables,integer extremes,all segment bounds,missing/invalid dynamic tags,
64/65 needed,2/3 paths,non-ASCII/NUL/string-index variants and chunk partitions
1/7/63/64/511/512/4093/65536. Positive nonempty dependencies/interpreters/actual
hashes must make an always-unavailable or manufactured-success parser fail.
Semantic controls must assertion-kill removed range/hash/ordering/budget/commit
guards,not fail on imports or test setup. Preserve all old regression expectations.

Replay integration needs independent controls for mutated sources,changed order/
type/size/role,missing/skipped payload bytes,padding/trailer/decoder failure,
unreaped child,stale/incomplete phase,global bytes/entries/deadline exhaustion,
fourth-scan attempts,raw-scratch release and exact owned cleanup. Source-identity
workflow lists must cover every loaded new helper before any future dispatch.
No raw plans or caller-supplied hashes can grant availability or bypass the owner.

## Ordered gates and scoped reviews

1. Next gated slice:Repository-Only Socket-Free Selected-ELF Range Collector and
   Oracle-Parity Safety Foundation. Pure DATA implementation/synthetic tests only,
   unchanged shared oracle;validate,review,commit,push and exact-head ordinary CI.
2. Later separately scoped owned replay/commit integration preparation:common
   walker,source/stream provenance,shared budgets and isolated IO/synthetic codec
   tests. No public artifact acquisition,site/database or candidate execution.
3. Only AFTER source/ordinary evidence passes,request a NEW explicit ephemeral
   static-proof allowance. Existing3/3 is still exhausted. No proposal here grants
   acquisition,materialization,installation,runtime loading or production wiring.

Canonical Feature Light SOURCE-SCOPED:design plus seven owning resumes and direct
unchanged oracle/walker/decoder/owner/manifest consumers. Corrected one-pass
ordering assumption,metadata-result ordering and resource-bound/fit equivalence;
three design issues reconciled,no executable defect claimed fixed. Bloat/structure
workflow unnecessary for docs-only delta;no changed code or file splitting.
Final Feature Security SOURCE-SCOPED:artifact hash gate,private bounded DATA,
full-stream replay provenance,unchanged acceptance/limits,commit ordering and
ownership/cleanup requirements explicit. No new security defect claimed fixed in
code;future implementation/physical evidence still required. No UI,Database
Review,transaction/authentication proof or whole-plugin release-readiness refresh.

Publication evidence belongs in the canonical toolkit closeout. Code/tests/
workflows/dependencies and all old proof counters remain unchanged in this slice.
Local existing offline bridges:7 tests/64 assertions,plain OK,no skips,covering
LinuxMinimumArtifact/PpaDebStatic/PhpArchiveStatic. All394 local Markdown links
across these8 documents resolved;reservation arithmetic and git diff --check
passed. These are existing-reader/design checks,not a new range-reader proof.
Plan advancement is a bounded implementable design,NOT resource/native/HTTP
qualification or another product milestone;product4/10/native43 remain.
