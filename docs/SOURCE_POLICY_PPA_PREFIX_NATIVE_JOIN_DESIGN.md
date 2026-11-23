# Fixed Prefix Native Storage and Source-Stream Join Integration Design

## Authority,baseline and decision — 2026-11-23

Explicit DESIGN-ONLY approval through validation,commit,push and ordinary exact-
head CI. R0 source/primary-documentation review,R1 design publication. Baseline
3c25d66b40dd5abf5101cad00970e47620d02d43 clean/synced master;
[ordinary45232186492](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/45232186492)
freshly reverified completed/success. Toolkitaea3cd2089440570aa0633b2319f2677a0476acb
clean/synced;uploader remains unchanged. Review boundary:this design and directly
owning resume records,not an implementation/proof cohort or latest docs tail.

Conditional GO for a later dormant fixed adapter/coordinator foundation with
isolated API tests. NO GO for actual storage,acquisition,materialization,native
experiments,candidate/module loading/execution,SQL/sites/LocalWP,permissions or
configuration changes,installation,production wiring,grants,activation,rollback,
packaging/release/deploy. No new tooling,trust/compatibility exception or budget.
Historical DATA3/3,new1/1,original/fresh audits each1/1,native43/product4/10 remain.

Owning [output foundation](SOURCE_POLICY_PPA_PREFIX_OUTPUT_FOUNDATION.md) remains
closed at _DisabledStorage.profile,with no syscall methods or active factory.
[Source leases/replay](SOURCE_POLICY_DESCRIPTOR_SOURCE_QUALIFICATION.md) remain
read-only. This design does not alter their code,receipts or existing callers.
[Dependency/prefix design](SOURCE_POLICY_PPA_DEPENDENCY_PREFIX_ADMISSION_DESIGN.md)
retains the three exact payload identities and four-name namespace. Successful
[provider audit](SOURCE_POLICY_PPA_PROVIDER_PROFILE_AUDIT.md) is direct availability
only;PHP/API/transitive/version/loaded-path compatibility remains unproved.

## Actual gaps;keep the integration small

_OutputOwner currently consumes a modeled claim/create/mode/read/remove contract.
Its claim returns a token,but real mkdir does not return a directory FD. Its
writer/readback methods cannot establish registration,signal/close or filesystem
behavior by themselves. It also has NO source-stream join:its fixed preparation
dictionary explicitly says source_joined/native_enabled/materialized FALSE.

_LeasePreparation._checked_scan already hashes and checks each source before/after
each control/data scan. _Transcript.facts binds expanded size/hash,count and row
ledger only after exhaustion. _LeasePreparation.run closes collectors/sources
before _commit;the returned JSON-compatible DATA is not a live source capability.
Reusing its closed cursors or accepting that DATA as caller-supplied authority is
therefore incorrect. A new fixed owner must reopen/re-admit sources under the SAME
original job/ledger,not turn the read-only API into a writable callback framework.

Proposed implementation surface:one private native output adapter and one private
join owner,using the existing strict walker,codec,source leases and output policy.
No general extractor,public plugin endpoint,provider registry,background service,
new observer/key/attestation tool or second runtime-selection architecture.

## Fixed context and construction

Only the existing trusted manual owner may later derive context behind target()/
marker()/exact-job checks. Bind exact head,first attempt,run ID,original Budget/
scratch objects,three D.PINS,selected catalog and admitted parent identity. The
source and output sibling names are respectively adb-ppa-deb-<same-run-id> and
adb-ppa-runtime-<same-run-id>. Retain independent canonical <=256-byte markers
for each exact root;do not enlarge them into durable manifests or authority tokens.

No caller root,catalog,storage provider,receipt,boolean,environment-selected policy
or raw FD. The future adapter opens/captures the same admitted parent identity as
the source owner and binds root-relative operations to its directory FDs. Check
UID==EUID,main-thread/signal and actual dir_fd/no-follow/noninheritance support
before construction. Unsupported profile refuses;no host repair or umask/global
permission change. Unexpected created modes refuse,never auto-chmod into admission.

Native activation remains unavailable in ordinary tests/controllers/production.
Later isolated tests replace the PRIVATE syscall boundary before calling dormant
methods;there is no production fixture/success switch. A future separately approved
manual physical entry must bind the concrete adapter/context itself,not enable an
arbitrary caller-selected _STORAGE or treat a mocked policy seal as a native grant.

## Claim/acquisition ledger;not a filesystem transaction

Use small preallocated private resource records,not a new persistence framework.
Record each syscall's acquisition intent before it can allocate a resource;
register returned FD and then validated identity before restoring the existing
bounded signal mask. Backend ownership persists even if its caller has not yet
received a token. No post-open allocation may leave an unregistered FD.

| Claim state | Allowed outcome/recovery |
| --- | --- |
| Not started / definitive exclusive-create refusal | No new root owned;close known parent handles. Existing names are foreign,never adopted/deleted. |
| mkdir returned / root FD not captured | Creation may have occurred but current pathname identity is not established. Refuse and report retained/Unknown;no path-only rmdir recovery. |
| Root FD captured and parent/name/type/UID/mode bound | Track its dev/inode privately. Only the exact owned namespace can be used or cleaned. |
| FD acquired / validation or restore interrupted | Retire only registered resources;unresolved acquisition/close becomes sticky Unknown,no grant. |
| Closed/removed and independently reconciled | Eligible for cleanup-complete reporting,not execution authority. |

[mkdir semantics](https://man7.org/linux/man-pages/man2/mkdir.2.html) provide a
success/failure result and mode subject to creation policy,not an atomically
returned opened-directory capability. Do not describe mask bookkeeping as an
atomic mkdir/open transaction or protection from SIGKILL,power loss or hostile
same-UID/root interference. Retain the existing trusted-parent/no-concurrent-
writer model;stronger adversarial ownership would require an explicit decision.

The current _OutputOwner constructor's conservative Unknown on an unresolved claim
stays fail-closed. Any later refinement distinguishing definitive non-acquisition
from partial acquisition must consume a private backend state,not inspect a path
and guess. Marker/JSON/path/UID equality alone never recovers lost ownership.

## Native storage obligations

[Directory-relative Python APIs](https://docs.python.org/3.12/library/os.html#files-and-directories)
must be capability-checked against the actual existing lab runtime,not assumed
from documentation. Proposed operations,not APIs implemented or tested here:

- Parent/root opens:read-only directory handles,no-follow,close-on-exec;stat/fstat
  binding and expected owner/mode. Child directory times may legitimately change;
  do not freeze parent mtime/ctime across owned sibling creation.
- Payload/marker creation:fixed flat leaves only,0600,O_WRONLY|O_CREAT|O_EXCL|
  O_NOFOLLOW|O_CLOEXEC|O_NONBLOCK. No truncation,append,archive permissions,symlink/
  hardlink creation,devices,INI,extra modules or maintainer scripts. Validate access,
  regular type,single link,owner,identity and size before treating a handle as ready.
- Writes:bounded positive progress;charge actual work on the original ledger.
  No whole CLI buffer. A failed/uncertain write poisons the owner until cleanup.
- Mode transition:the existing all-entry pre-mode check remains mandatory. The
  native method must ALSO reopen read-only,no-follow and bind that FD to captured
  identity/type/owner/nlink/size/0600 immediately before fchmod on THAT FD to0400.
  Recheck afterward and retire the temporary handle before readback. Never chmod
  an archive/caller pathname or set0500/execute permission in materialization proof.
- Readback:independent read-only handles,EOF probe,full fixed hashes and final
  namespace/metadata,not bytes retained from the write. Preserve pre-reservation/
  release ordering,writer-close checks and no accessible write handles at seal.
- Deletion:only fixed captured leaves beneath captured root/parent FDs,exact
  membership and ownership checks,then unlink/rmdir and absence checks. No recursive
  remove,shared-cache prune,installed PHP removal or adoption of preexisting roots.

[Open flags](https://man7.org/linux/man-pages/man2/open.2.html) do not make a whole
pathname traversal safe merely by setting O_NOFOLLOW on its final component.
Captured directory context remains essential. Descriptor validation plus chmod/
unlink is not an adversarial compare-and-swap namespace transaction. Where the
trusted no-concurrent-writer premise cannot be established,refuse rather than
claiming portable race immunity or adding unapproved kernel/tool dependencies.

[close failure semantics](https://man7.org/linux/man-pages/man2/close.2.html) make
retrying a possibly reused descriptor unsafe. Mark retirement before the call;
never retry an ambiguous close,including from repeated cleanup. Distinguish
source,writer,mode,readback,iterator and parent/root records;do not convert a late
close failure into an empty-resource assertion. Successful unlink alone is also
not proof that all open references are gone;see
[unlink semantics](https://man7.org/linux/man-pages/man2/unlink.2.html).

## Same-owner source-stream join

1. After the already required exact-job/preflight/pin/acquisition/transport-release
   stages,a private fixed preparation owner creates a bounded IN-PROCESS summary
   of first-pass source identities,selected catalog and primary control/data stream
   facts. Retain only package/category,expanded size/hash,entry count and row-ledger
   hash,not stream/member bodies. Reuse _LeasePreparation scan checks;no generic
   caller visitor. Publish nothing until its existing final source seal/close succeeds.
2. Reopen fresh read-only leases through the SAME fixed source factory,parent/run/
   head and ORIGINAL Budget/scratch. Re-admit all three pins BEFORE parser/output
   work. The prior summary is code-owned state,not parsed JSON from a caller or
   historical GitHub receipt. Reject changed roots,pins,namespace or close uncertainty.
3. Construct a provisional output owner only after those admissions. Rewalk ALL
   three packages'control AND data streams with D.walk_tar/D.Slice/the fixed codec.
   Before/after EACH stream retain source identity/full namespace checks. Validate
   every path/trailer/EOF/control,including common and nonselected files. Only exact
   selected regular role/package/member/size events call begin/feed/finish;all other
   files are scanned but never written. No per-package result or early usable prefix.
4. Compare second-pass expanded size/hash/count/row-ledger with the private first-
   pass summary,and member hashes with the fixed output catalog. Preserve duplicate/
   collision policy;do not register replay entries as a new primary namespace or
   reset global inventory/entry/work counters. Delayed package/common/trailer failure
   invalidates already written PROVISIONAL payloads and invokes owned cleanup.
5. Exhaust compressed input,tar padding/trailer and physical EOF;require successful
   decoder exit,closed pipes/selectors and all three final source identities. Close
   all source cursors/leases and decoder resources SUCCESSFULLY before output seal.
   A finally-return or earlier computed result cannot bypass close/restore failure.
6. Only then run output mode/readback/final namespace/release barriers. The current
   policy dictionary remains preparation DATA with false grants;do not flip its
   flags as a shortcut. Future joined completion must originate inside this SAME
   fixed owner after all stages,with no public path/FD or execution token.

These are proposed seams. Existing _LeasePreparation returns and callers stay
unchanged;its closed cursors are not reused. A fixed subclass/private capture at
_checked_scan may retain the bounded primary summary without changing ordinary
read-only proof behavior. No read-only cursor gains write/fileno/reopen methods.

## Budget,retirement and independent closeout

Retain40MiB parent/24MiB child,8MiB codec window,1MiB raw counter,192MiB aggregate,
8192 entries,16MiB member,64KiB chunk/control/output,540s work/30s cleanup/600s job.
No new Budget or reset work deadline at second pass,root claim,readback or cleanup. Required
resource registration,marker/output bytes and readback/EOF work must be counted.
Reuse original scratch,including overlapping source/codec/output chunks;drop raw
references before releasing reservation. Counter evidence is not full object/RSS.

Known prior work107599159 leaves93727433 bytes. Selected outputs12672176 imply
write+readback25344352,leaving68383081 BEFORE second-pass source hashes,compressed/
expanded streams,AR/tar framing,markers and observation. Rehashing all sources once
costs4146302;two such seals alone cost8292604. These are planning lower bounds,
NOT demonstrated combined fit. Synthetic traces must account for every selected
code path;later real cap-enforced qualification may refuse. Never drop a hash,
reuse old credit,increase caps or claim universal regular-file IO latency bounds.

Normal/failure finally cleanup must retire decoder/process/file/iterator resources
in a fixed bounded order before removal and close directory handles afterward.
Cleanup uses its existing30s wall allowance,not renewed qualification time;recheck
the ORIGINAL work deadline after cleanup before reporting proof success. Any
uncertain child/FD/identity/removal yields Unknown,not zero;count categories separately.
Start that cleanup deadline once in the fixed owner and keep helper waits within
the remaining allowance. Do not stack fresh30s source/output/decoder cleanup
windows or reset the shared alarm inside a helper. Existing ordinary source-proof
callers retain their contract;any join-only deadline plumbing needs isolated tests.
Work-budget refusal must not suppress retirement of already proven owned resources;
continue only bounded cleanup,keep qualification failed and never treat cleanup as
new work credit or revive the expired original result.

No new durable recovery ledger or supervisor is required by this design. If the
in-process private ownership ledger is lost in a killed/crashed process,the later
always step may only reconcile bounded presence/absence. It MUST NOT delete a
runtime root from marker/path checks alone or call runner teardown owned cleanup.
Presence/uncertainty stops another attempt and requires explicit bounded recovery
authority/evidence. Initial physical scope must not pretend to prove forced crash/
SIGKILL recovery;that is a separate decision if genuinely needed. Preserve the
existing SOURCE-root cleanup scope without extending it to a sibling runtime root.

## Required tests and next approval

Later isolated implementation tests must have a real modeled positive through
first scan,second scan,source/decoder close,output seal and cleanup. Assertion-kill
always-unavailable,missing per-stream/final hashes,late common/trailer failure,
forged summary,wrong head/root,close-before-registration/unmask,pre-mode identity,
source-close bypass,readback/EOF/namespace/resource and false-cleanup guards.
Test short/failed writes,zero/boolean progress,FD0,partial acquisition,foreign/
symlink/hardlink/mode/identity changes,mask/close uncertainty and residue. No fake
success switch;model tests do not establish native effects,physical atomicity or fit.

Next recommended gated slice:Repository-Only Fixed Native Prefix Storage and
Source-Stream Join Safety Foundation,isolated syscall/API tests ONLY,with native
entry/factory selection and actual materialization/execution STILL DISABLED.
Implement these fixed contracts without activating a manual entry or acquiring
artifacts. Review before requesting ONE explicitly scoped owned ephemeral-CI
materialization/cleanup qualification and any fresh DATA acquisition allowance.

A later physical receipt must truthfully distinguish three selected payloads
actually materialized (then cleaned) from prepared-only RAM policy;do not retain
an extracted/materialized:false claim after writing real files. Use a new scoped
receipt,qualified/executed/dependency_closure_verified false and versioned symbols
Unknown. No path survives a cleanup-only proof and no PHP/HTTP/SQL/runtime grant
follows. Candidate startup remains a separate approval after materialization fit.

## Source-scoped review and validation

Canonical Feature Light:design plus direct records/unchanged storage,source,
walker/codec/replay consumers. Reconciled gaps:mkdir/open versus ownership;adapter
registration before caller return;closed-source DATA versus private origin;full
second-pass consumption;pre-mode versus native-FD checks;unlink versus FD release;
marker-only crash recovery versus Unknown;lower bounds versus actual fit. No
executable defect or physical proof claimed fixed. Docs-only:no bloat split/UI.
Final focused Feature Security SOURCE-SCOPED:least access,fixed source/output
provenance,default-off selection,false grants,finite cleanup/privacy/accounting;
no unresolved contradiction in this design scope. No historical whole-plugin,
Database/transaction or release-readiness review is refreshed.

Targeted unchanged isolated tests,arithmetic,links/diff and actual ordinary exact-
head CI publication evidence are recorded in the owning toolkit closeout. Source,
tests,workflows,dependencies,pins,default disabled entry and physical-qualified
cohort remain unchanged. This design adds no native/static attempt or product
milestone;it establishes the contract for the next dormant integration foundation.

Actual local validation this task:unchanged targeted PHP5 tests/37 assertions plain
OK (including24 RAM output groups/five assertion-killed controls);source API32/
controller15 groups each0 failures/errors/skips. Catalog/source-size arithmetic
above recomputed in memory;406 local document links and diff whitespace passed.
No local full suite,physical syscall probe,codec preflight,artifact/native/manual
qualification or site operation was run for this design task. Ordinary exact-head
CI is publication evidence,not design implementation or physical qualification.
