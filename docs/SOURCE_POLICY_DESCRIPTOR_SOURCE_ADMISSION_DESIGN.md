# Fixed Read-Only Descriptor Source Admission and Replay Owner Integration Design

## Authority and baseline — 2026-11-22

Explicit approval is DESIGN ONLY,including validation,commit,push and exact-head
ordinary CI. R0 source/primary-documentation audit and R1 design publication.
No source/test/workflow implementation,artifact acquisition,manual/native proof,
candidate execution,fixture creation,permissions/storage/site operations or wiring.
Existing3/3 allowance stays exhausted;native43/product4/10 unchanged. This is not
a new product milestone,physical qualification,release candidate or deployment.

Dashboard2ca40e86741148472cef3024fd3500ef20d477c0,clean/synced master;ordinary
44956412657 freshly reverified completed/success. Toolkit677b7acc15dc86175cab5112d29bf28834d8e0ef
clean/synced main;uploader3dfcb15b5cab8191df7d984251986ead140ded52 unchanged.
This delta follows the coherent [shared-walker/replay preparation](SOURCE_POLICY_SELECTED_ELF_STREAMING_DESIGN.md),
not a claim that a documentation-only baseline covers all prior source changes.

| Frozen source | Git blob OID |
| --- | --- |
| scripts/ci/source-policy-ppa-deb-static.py | 1e668897551369278316037916bc85da682d0570 |
| scripts/ci/source_policy_deb_replay.py | 0c547643ecb7ea5f29e5bc59969f78e8a2bd7279 |
| scripts/ci/source_policy_deb_data.py | cf0bb99c8ab1ad8e70e09e88d465df607d5b5855 |
| scripts/ci/source_policy_deb_stream.py | 56ad8360d19b04b334ead58e2b95215aa6b2ba29 |
| scripts/ci/source_policy_elf_ranges.py | c90abfa70a22b077558d517dc7d6581478b5a351 |
| .github/workflows/source-policy-ppa-deb-static.yml | 8a9175f0614025f5cd2304fa6ecf2c0f4b47a980 |

## Decision and assurance boundary

Conditional GO for a dormant fixed source-set/descriptor-lease foundation,with
isolated syscall/API tests first. NO GO yet for real opens,descriptor/process
qualification,a new static attempt,manual-entry selection or production wiring.
Reuse the existing owner/job policy,strict walker,decoder and ELF collector;do
not create a new runner,attestation service,toolchain,trust publisher or sandbox.

Current prepare accepts immutable packet bytes and explicitly does not qualify
on-disk ownership. Current manual inspect reopens each package with Path.open,
and current cleanup validates owned paths before unlinking. Neither is proof of
the proposed anchored descriptor lifecycle. No existing code defect is silently
fixed by this document;these are requirements for the next implementation.

Trusted model:one admitted ephemeral Linux CI job,its already-owned private
directory,and no concurrent owned writer after acquisition/transport release.
The fixed publisher/catalog/trust contract stays unchanged. The design detects
observed identity/name/metadata/content changes;it is NOT an immutable filesystem
snapshot,transaction,host-root/same-UID adversary defense or mount attestation.
Read-only access does not make the backing file immutable. Missing capabilities,
unqualified storage assumptions or observed changes REFUSE,not repair the host.

## Fixed construction and root admission

Future construction belongs behind the EXISTING manual owner's target/marker
policy,not a public factory accepting a root,descriptor,pins,reader callback or
verified flag. Derive only the existing RUNNER_TEMP root/run-id and exact three
filenames plus owner.json after exact job/head/ref/actor/first-attempt admission.
Reuse target()/marker() once;do not copy their policy into a second entry point.
Acquisition must already be finished,all output writers closed,and transport
release established. The factory independently verifies bytes;it does not trust
download receipts as authority. No creation/chmod/chown/credential repair here.

Require the existing single-main-thread Linux proof profile,matching real/effective
UID (consistent with current cleanup),and available dir_fd,pread,fstat,lseek,
non-inheritance,local signal-mask support and required flags. No Windows/LocalWP fallback or WordPress/PHP
compatibility change. Current runner parent mode/capabilities are Unknown until
later owned qualification;the following is a proposed closed admission contract,
not a claim that a host audit was run.

1. Check the existing absolute canonical,non-symlink trusted parent path. Open
   its directory with O_RDONLY|O_DIRECTORY|O_NOFOLLOW|O_CLOEXEC;compare the opened
   directory identity with fresh non-following path metadata. Require current
   effective UID ownership and no group/world write. Reject mismatches;no mode
   changes. Trusted ancestor ownership is a harness assumption,not established
   by realpath/lstat or O_NOFOLLOW on the final component alone.
2. Open only the derived flat root basename relative to that pinned parent FD,
   with the same directory flags. Require directory,current UID,exact0700 mode,
   stable dev/inode/mode/uid/gid/nlink/mtime_ns/ctime_ns and a matching parent-
   relative name binding. Do not include read-sensitive atime in the snapshot.
3. Enumerate root entries through a context-managed scandir(root_fd),consume at
   most5 entries and reject the fifth/unexpected name. Explicitly rewind the
   private root directory cursor to0 with checked lseek BEFORE each iterator;
   refuse unsupported/failed rewind. Do not rely on hidden iterator offset reset
   or allow concurrent iterators sharing that cursor. Require EXACT membership
   owner.json plus the3 frozen package names;no listdir/unbounded accumulation.
   Use fresh non-following dir_fd stat,not cached DirEntry.stat as a seal.
4. Open marker and packages relative to the SAME root FD,with O_RDONLY,
   O_NOFOLLOW,O_CLOEXEC and O_NONBLOCK. Never O_CREAT/O_TRUNC/O_APPEND/O_RDWR/
   O_WRONLY,descriptor reopening via /proc,or archive-derived host paths.
   Pre-open non-following metadata and post-open fstat must agree;after open,
   record ownership immediately so later failure cannot leak the new handle.
5. Require regular files,current UID,nlink1,no executable bits and no group/
   world write. Sources have exact catalog size;marker has expected length<=256
   and exact original marker bytes. Do not require0400 or mutate acquisition's
   valid0600/0644 outputs. Read nothing before type/ownership/shape admission.
6. Check actual access mode through F_GETFL & O_ACCMODE == O_RDONLY and confirm
   non-inheritance. Do not test read-only with flags & O_RDONLY (zero-valued
   access mode is not a set-bit test). No arbitrary supplied handle/fileno output,
   write API,duplication or caller-controlled seek/read implementation escapes.

Selected OS semantics are grounded in [Linux open/openat](https://man7.org/linux/man-pages/man2/open.2.html):
O_NOFOLLOW protects the basename,not earlier components;O_NONBLOCK is not a hard
regular-file IO timeout. Anchoring/trusted ancestors and existing alarm/job
limits remain separate obligations. No universal kernel-latency bound is claimed.
[Python os documentation](https://docs.python.org/3.12/library/os.html#os.open)
documents dir_fd/non-inheritable descriptors and bounded-iterator facilities.

## Same-lease reads and observation checkpoints

Each admitted package retains ONE read-only FD until the source-set teardown.
A private exact-origin cursor exposes only bounded read/seek/tell to existing
Slice/Decoder. Logical seeks do not open another path or duplicate a descriptor.
Use bounded pread calls with a private logical offset;accept short reads with
checked progress,refuse premature EOF,overruns,wrong types and use after close.
Do not wrap a complete file in bytes/BytesIO/mmap or stage selected members.
[pread semantics](https://man7.org/linux/man-pages/man2/pread.2.html) permit reads
at an explicit offset without changing the descriptor's shared file position.

Source fingerprint:dev,ino,mode,uid,gid,nlink,size,mtime_ns,ctime_ns. Compare each
admitted FD with its original snapshot AND fresh root-relative non-following
name metadata. Replaced same-byte files,unlink/rename,hard links,ownership/mode/
size/time changes or missing names refuse. Exclude atime because reads may change
it. Root binding/fingerprint and bounded membership/marker checks surround every
stream scan and final seal. Parent dev/inode/mode/owner binding stays stable;
unrelated parent-directory child activity is not a whole-parent timestamp freeze.
Check root/marker metadata and source name binding before/after each source hash
and before each AR index. These are bounded metadata checks,not additional full
marker-body/membership scans beyond the stage boundaries specified below.

All3 full source hashes MUST pass before ANY AR/tar/ELF/decoder parsing. For each
package,parse its AR index once under stable fingerprint checks. Before AND after
each control/data/replay scan,rehash that package through its SAME FD,with fresh
pre/post fingerprints and name checks. Observe real EOF one byte beyond the
expected size during each source hash;unexpected bytes are charged then refused.
After every package,retain facts privately. Before final teardown,rehash all3
sources again and revalidate root/marker/membership. No in-memory ready credit.

These checks authenticate the bytes actually observed,not a simultaneous snapshot.
Private-root/no-writer discipline remains necessary;hash/stat loops cannot turn
hostile same-UID mutation into a proved atomic file transaction. A failed check
invalidates the entire source set;no reopen-and-resume or reuse of old facts.

## Replay owner and publication order

Keep public immutable-byte prepare unchanged as a regression surface. Future
shared replay logic may take ONLY an internally constructed exact source-set
lease,not arbitrary readers or public source_mode/pins/verified/closed parameters.
Refactor private package/source access,not policy:one walker,one original Budget,
one shared scratch pool,the same fixed AR slice/codec and at most3 data passes.
All original member/whole-stream hash,length,entry/ledger,EOF/decoder and role
barriers stay active. Canonical inventory/path/type registration is primary-only.

Proposed lifecycle (not an existing API):

NEW -> JOB/ROOT ADMITTED -> ALL3 LEASED/HASHED -> PRIVATE SCANS/REPLAYS
-> FINAL NAMESPACE/ALL3 HASH SEAL -> DECODERS REAPED -> LEASES CLOSED
-> ORIGINAL ROLE/SCRATCH/OUTPUT/DEADLINE GATE -> PUBLISH PREPARATION FACTS.

Any failure -> TERMINAL INVALID -> CLOSE ALL PROVEN OWNED RESOURCES -> NO FACTS.
No caller sees provisional metadata. In particular,do not simply reuse current
run's return-inside-finally structure and assume a later close cannot fail.
Zero descriptor leases/iterators/decoders and zero retained raw reservations are
independent publication gates,not supplied booleans. Facts remain preparation-only
until a separately approved actual DATA qualification establishes the integration.
No qualification,execution,versioned-symbol,HTTP/auth/SQL or production grant.

The existing decoder only receives pipe bytes,never source/root/marker FDs.
Make close_fds=True and empty pass_fds explicit at its fixed Popen boundary when
integration is approved;all source FDs are non-inheritable. No source FD export,
new shell command or descriptor registry visible to requests. This follows
[Python subprocess descriptor rules](https://docs.python.org/3.12/library/subprocess.html#subprocess.Popen),
not a claim that fork/exec inheritance was tested in this design slice.

## Teardown,cleanup and failure accounting

Open/register and close/retire are not atomic Python operations. For this fixed
owner,defer existing SIGALRM/SIGINT delivery with pthread_sigmask ONLY across one
resource acquisition/close and its ownership bookkeeping,including iterator
acquisition. Capture/restore the exact previous mask in finally;registration or
retirement must finish before unmasking can raise. No new handler/thread,global
policy change,timer reset or work credit. Never start a decoder while masked;
the monotonic deadline continues and is checked immediately after restoration.
Unsupported signal profile or mask-restoration failure suppresses publication
and leaves cleanup Unknown. This does not prove a kernel-blocking-call time bound;
the existing job timeout remains a separate limit. Externally killed work is not
reported as successful cleanup. The exception/delivery behavior is documented in
[Python signal rules](https://docs.python.org/3.12/library/signal.html#execution-of-python-signal-handlers).

Track successful opens immediately,including partial admission. Only trusted
syscall-returned exact nonnegative FD integers establish a close obligation;
never accept a caller's integer/Boolean or assume valid FDs start above2.
Invalidate cursors first,reap every owned decoder,close each iterator explicitly,
then close package/marker/root/parent leases in reverse acquisition order. Do not
close inherited/foreign handles or scan/kill arbitrary processes. Idempotence
means no second close of a retired descriptor number,not blind retry.

Check each close outcome (normal Python close returns None,not a numeric success
flag);on error attempt the OTHER owned handles within the remaining cleanup
deadline,retain terminal cleanup failure and suppress publication. Never retry a failed
close on the same number:it may have been reused. This is the selected Linux-only
policy supported by [Linux close guidance](https://man7.org/linux/man-pages/man2/close.2.html).
Unknown cleanup is not zero. Diagnostics use finite stage/reason/cleanup states,
not raw FDs,paths,exceptions,marker bytes,environment or content dumps.

Descriptor teardown precedes owned compressed-file cleanup. The existing cleanup
function and workflow always-step are NOT changed by this design. Future wiring
must preserve their exact ownership gate and distinguish file-count0 from proven
descriptor/child cleanup. A failed teardown never becomes qualified because an
always-step removed files. Unresolved ownership/reaping/close concerns require
reconciliation before another attempt;no automatic retry or widening delete.
Existing540s work/30s cleanup/600s job boundaries remain;cleanup time is not extra
qualification time,and deadline-expired work cannot publish after cleanup.

## Fixed resource arithmetic and limits

Additional tight design ceilings:6 long-lived source-set FDs (parent,root,marker,
3 packages),plus ONE transient scandir-owned duplicate;total7 source-set FDs,
separate from the existing one decoder's pipes/selector/runtime descriptors.
The CPython3.12 [scandir implementation](https://github.com/python/cpython/blob/3.12/Modules/posixmodule.c)
duplicates the supplied directory FD before fdopendir;close that iterator before
opening another. This is a source-derived planning bound,not measured runtime FD
or memory evidence;later qualification must verify the admitted runtime behavior.

No whole acquired packet bytes retained. Reserve all planned raw IO/capture/
marker/output buffers BEFORE allocation/read;release scratch reservations only
when no longer retained. Keep1MiB combined raw ceiling and original40+24/64MiB
process partition,8MiB codec bounds,192MiB work,8192 entries,16MiB selected member
and64KiB chunk/control/output caps. Python objects,allocator/import state and
directory/codec internals are outside raw-counter evidence;actual fit Unknown.
Prior conservative raw subtotal436538 + marker256/extra-EOF-byte1 =436795 bytes,
NOT a high-water measurement or complete object-memory model.

Let P_i be frozen source size,r_i<=2 be data replays,B=sum(P_i)=4146302.
There are2+r_i stream scans per package (control plus data/replays). Initial and
final all-source hashing plus pre/post scan hashes cost
2B + sum(2*(2+r_i)*P_i). Worst r=(2,0,2):26 full hashes,38060532 source bytes,
10 stream scans. Full root/marker membership checks at admission,pre/post each scan
and final seal total at most22;successful marker reads<=5632 bytes. Extra EOF
reads observe0 bytes on success;failure bytes still consume work then refuse.
AR/header reads,codec peeks,compressed/decoded data,all replay entries and marker
bytes are ADDITIONAL charged work;metadata calls check the same deadline.
Decoded sizes/actual interpreter/object residency remain Unknown. Rehashing is
not free and may resource-refuse an otherwise valid package. No budget increase,
per-pass reset,skipped validation or new success claim is authorized.

## Required implementation evidence and ordered gates

1. Next gated slice:Repository-Only Fixed Descriptor Lease and Replay Source-Set
   Safety Foundation. Dormant code with isolated syscall/API DATA tests ONLY;
   no real opens,filesystem fixtures,native/manual dispatch,acquisition or wiring.
   Scope includes dormant construction in the existing owner,one cohesive source
   helper if justified,private replay integration and explicit decoder FD policy.
   No copied target policy or general public reader/callback interface.
2. Later NEW explicit owned ephemeral-CI qualification scope must cover actual
   descriptor/namespace/permissions/inheritance/cleanup fixtures and any fresh
   DATA acquisition/static attempt. Existing3/3 remains consumed. Bind EVERY
   loaded source path to exact-head closure before import/effects. Current
   workflow/M/D/C/archive-static/shared-manual-static paths remain required;
   add replay,ELF-range and any actual new lease helper paths before loading them.
   No manual mode switch until separately approved;no production wiring.

Permanent isolated tests must have non-vacuous successful admission/reads/replay/
close ordering and exact expected facts;an always-unavailable owner cannot pass.
Independent guards/control witnesses:wrong job/root/marker/membership;missing
flags;O_RDONLY=0 access-mode logic;symlink at parent/root/file;FIFO/directory/
device/hardlink;wrong UID/mode/size;path replacement with identical bytes;rename/
unlink;fingerprint changes;partial reads/early EOF/extra byte;closed/stale/wrong
lease;no read before admission;no write-capable/foreign/inheritable handle;
partial-open and fstat/hash failures;iterator errors;pending owned alarm/interrupt
at acquisition/registration/close/retirement and exact mask restoration;decoder launch/close/reap
failure;close EINTR/EBADF/other error with no retry/double-close;final-hash change;
deadline/work/scratch/entry exhaustion;false early result or cleanup credit.
Guard removals must fail behavior assertions,not imports or fixture setup.

Use in-memory syscall fakes with bounded virtual clocks for the first foundation.
Those tests do NOT prove real permission denial,openat/no-follow behavior,FD
reuse/inheritance,reaping,kernel IO timing or physical memory. Later owned proof
must close those gaps under the existing trusted lab profile without new tools,
permissions/installations,host attestation or weakened guards. Unavailable parent
profile/capabilities are a refusal,not a prompt to repair or repin the host.

## Source-scoped review and current evidence

Canonical Feature Light:9 design/resume documents plus unchanged direct sources.
Six design assumptions corrected:basename-only no-follow vs ancestor trust;
read-only/observation vs immutability;teardown before publication/no close retry;
rehash/iterator resources vs free work;repeated directory iteration vs implicit
offset reset;asynchronous interruption vs ownership bookkeeping. No executable
defect claimed fixed.
No bloat/UI workflow needed for this docs-only contract. Final Feature Security
is SOURCE-SCOPED design review:closed construction,least access,identity/provenance,
publication/cleanup order and explicit assurance limits;no unresolved finding
remains in this design scope. This is not implemented or physically qualified
behavior;implementation and physical proof remain required.

Executed unchanged pure synthetic bridges:2 PHP tests/10 assertions,plain OK,
no skips (range collector21 groups,replay preparation18 Windows groups). Checked
resource arithmetic in memory only. No source,test,workflow,pin or dependency
changes. All417 local document paths and git diff --check passed. Exact publication
CI/evidence belongs in the toolkit scoped closeout.
No refreshed whole-plugin DS3/Database Review/runtime/release-readiness rows.
