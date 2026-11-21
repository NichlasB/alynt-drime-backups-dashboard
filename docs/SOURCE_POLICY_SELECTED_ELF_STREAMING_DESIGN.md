# Selected-ELF Range Inventory Feasibility and Memory-Proportionality Design

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
