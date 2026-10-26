# V2.3 Fixed Supervisor Windows Adapter and Protected Bootstrap Integration Design

2026-10-26. One repository-only design task selected under the autonomous safe-local
batch: R0 source analysis/R1 Markdown publication, not eight artificial milestones.
Excluded review bases dashboardd2833f90a4389b8cd603e30fd2ae8d37f67a1c79,
uploader35846f38e7742036e7baa8bf33f6d940ea0c2d5a and
toolkitbb41a14edfa243ce6e3183077c7ac3bb9f69b620.
Parents: [supervisor design](V2_3_ELEVATED_OWNED_PRINCIPAL_SUPERVISOR_DESIGN.md),
[read-only audit](V2_3_TRUSTED_SUPERVISOR_HANDOFF_READ_ONLY_AUDIT.md), and uploader
docs/V2_3_SUPERVISOR_JOURNAL_DATA_FOUNDATION.md. Their historical observations are
not refreshed. No site, host, installed toolchain, account or permission inspection.

Successor outcome: [approved staging/admission preflight audit](V2_3_PROTECTED_BOOTSTRAP_ADMISSION_FEASIBILITY_AUDIT.md)
found no qualified native toolchain/artifact/bootstrap in bounded discovery and
stopped before fixtures. Foundation/proof remain Blocked; no staging or native
execution is authorized by the audit outcome or by this design alone.

## Decision: preserve the missing native boundary, not a model bridge

The DATA owner is test-only bookkeeping. Its private symbols, sealed receipts,
positive probe declarations and recovery lineage cannot authenticate a Windows
identity, grant a privileged operation, or prove a durable journal. Do not import
it into an elevated executable or translate its receipt into native permission.
Keep all actual-authority fields false. Existing worker, storage, plugin hooks,
ingress, activation, rollback, schedules and production wiring stay unchanged.

The candidate is a separately reviewed fixed native executable plus a fixed
non-administrative canary probe, not Node/PHP/PowerShell running repository code
as administrator. This is a design selection, not a compiler/dependency addition,
available artifact, signature, installed implementation or native GO. Do not add
a general command runner, arbitrary API dispatch, caller-supplied callbacks,
caller-selected paths/accounts, automatic elevation or a privileged IPC service.

The first physical scope must be **protected staging plus read-only supervisor
admission only**. Account creation, logon, proof-root mutation, denial probe,
process launch and journal mutation are later gates. A staged artifact being
present cannot automatically enable those later modes.

## Trusted bootstrap and admission order

1. Before host work, separately approve the exact local target, new staging
   objects, human supervisor, permissions, retention and exact cleanup scope.
   The coding agent/toolchain stays unelevated. No password or raw SID in chat,
   command lines, environment, logs or tracked records; no automatic UAC handling.
2. A human-controlled trusted bootstrap reviews a finite artifact manifest from
   an independently trusted review/build handoff. A digest generated alongside
   mutable repository output is not that trust root. Record exact source/build/
   artifact/policy revisions and all loader/configuration dependencies privately;
   no download, dynamic compilation, repository import or script execution in the
   elevated path. No such bootstrap or protected executable exists in this task.
3. Create only the separately approved fresh protected staging child under the
   candidate machine-controlled parent, with the reviewed owner/DACL at creation.
   Collision refuses: no adoption or repair of an existing object or parent ACL.
   The human/bootstrap must establish protection before running staged code;
   its future self-check cannot retroactively secure its own loading.
4. A future read-only admission entry checks actual supervisor token/elevation,
   distinctness from initiator/consumer, exact protected image and dependency
   identity, ancestor/object/alias protection, effective rights, and original
   attempt/policy/epoch binding. Input fields remain bounded non-authoritative
   intent; none can select the identity to trust. Failure precedes any mutator.
5. Admission returns only a bounded non-secret refusal or read-only admission
   observation. It must never claim account/logon/probe/cleanup/source ownership
   qualification. No external receipt, including this document or DATA output,
   can authorize a future mutator or renew consent after the entry returns.

Program Files remains a parent candidate, not effective-access proof. The prior
human designation is neither fresh sign-in consent nor actual token attestation.
Same-identity filtered/elevated UAC tokens do not satisfy the distinct-role rule.
The trusted administrator/OS remain trusted; this is not an administrator-hostile
sandbox. Unknown trust, image or protection state refuses rather than installing
a service, changing host policy, or manufacturing a new administrator.

## Native call/result ownership ledger — unimplemented candidates

Each row requires a fixed typed wrapper, immediate documented error capture,
bounded output allocation, exact result ownership and one explicit close/free
path. No generic caller-driven API name/argument adapter. Public results contain
finite reason/count enums only; token/SID/path/handle records stay private.

|Boundary|Candidate and evidence required|Refusal / scope limit|
|---|---|---|
|Token admission|Owned token handle, actual user/elevation/group/privilege information; fixed query classes and capped buffer sizing|No environment username, account label, model symbol or child self-report attests identity; insufficient access/unknown size refuses|
|Image/root binding|Open exact reviewed objects; bind volume/file identity, owner/DACL, resolved ancestry, link/stream/member inventory and loader dependency closure for the whole lifetime|Path canonicalization, digest, one ACL read or one handle does not prove no aliases, old write handles, parent replacement or transitive loader escape|
|SAM creation, later only|Local-only NetUserAdd, fixed account policy, one generated candidate and ephemeral secret; retain call result plus independently resolved SID|Collision/policy failure does not trigger another identity or policy change; unknown post-call state quarantines|
|Probe launch, later only|CreateProcessWithLogonW remains a candidate, suspended fixed image and explicit environment/current directory, no profile or network-only logon flags|Private desktop, valid stdio and exact non-admin token/job ownership must qualify before resume; no alternate logon/API fallback|
|Containment, later only|Owned process/thread/job handles; confirm assignment and no escape, actual exit and private-channel EOF closure|PID reuse, nested-job incompatibility, failed assignment, inherited privileged handles or timeout refuses; forced exit never proves natural success|
|Account removal, later only|Resolve recorded local name to exact owned SID immediately before fixed NetUserDel; require no concurrent rename/reuse by trusted administrators|Name-based deletion is not an atomic SID-conditional operation; ambiguous ownership/concurrency quarantines, never prefix-sweeps|
|Root removal, later only|Revalidate journaled object/member identities and ancestry; fixed handle-owned deletion candidate, bounded exact member set|No recursive name/glob sweep, shared-parent deletion, unknown child adoption, link following, profile or registry cleanup|

Primary API references consulted on 2026-10-26 (contracts, not installed evidence):

- [GetTokenInformation](https://learn.microsoft.com/en-us/windows/win32/api/securitybaseapi/nf-securitybaseapi-gettokeninformation)
  requires appropriate token access and reports required output length. A later
  wrapper must cap sizing, reject drift and free its own buffers; no unbounded retry.
- [GetFileInformationByHandleEx](https://learn.microsoft.com/en-us/windows/win32/api/winbase/nf-winbase-getfileinformationbyhandleex)
  returns selected information for an opened file. Inference: this alone cannot
  establish the complete ownership/lifetime predicates in the ledger.
- [DLL search order](https://learn.microsoft.com/en-us/windows/win32/dlls/dynamic-link-library-search-order)
  includes dependency searching even when the first DLL used a full path. The
  candidate therefore needs transitive loader/configuration closure, not just an
  absolute executable name. No OS library inventory or signing policy was changed.
- [NetUserAdd](https://learn.microsoft.com/en-us/windows/win32/api/lmaccess/nf-lmaccess-netuseradd)
  has explicit local-server selection, policy/defaults and failure results.
  [NetUserDel](https://learn.microsoft.com/en-us/windows/win32/api/lmaccess/nf-lmaccess-netuserdel)
  accepts server/name, not an expected-SID compare-and-delete parameter. Inference:
  a journal lookup followed by deletion does not remove the concurrency gap.
- [CreateProcessWithLogonW](https://learn.microsoft.com/en-us/windows/win32/api/winbase/nf-winbase-createprocesswithlogonw)
  documents interactive logon permission, desktop/window-station permissions,
  unchecked standard-handle values and return before initialization completes.
  Its signature does not provide a bInheritHandles/explicit handle-list parameter.
  Do not infer safe cross-identity pipe inheritance from STARTUPINFO values.
  Logon flag0, fixed non-null absolute application, explicit environment/directory
  and a private desktop are requirements, not a tested launch recipe. The API is
  not available from LocalSystem in the documented modern profile; no SYSTEM
  fallback or automatic switch to a more privileged logon API is selected.
- [AssignProcessToJobObject](https://learn.microsoft.com/en-us/windows/win32/api/jobapi2/nf-jobapi2-assignprocesstojobobject)
  has access/session/nesting constraints and failure outcomes. Job assignment
  returning success is not proof of application shutdown, channel EOF or cleanup.

## Side-effect ordering, uncertainty and bounded termination

Only a later approved native implementation may create its protected journal,
before proof-root/account mutation. Its evidence schema must be distinct from
the in-memory fixture schema: exact private SID/object/process identities,
artifact/policy/attempt binding, ordered intent/result records and explicit retained
control artifacts. It must prove persistence/error handling, not replay JS symbols.
Intent flush before and result flush after a call is not atomicity with SAM/files.
Lost/partial results never license adoption, guessed cleanup or automatic retry.

Every future wrapper needs a finite operation/byte/allocation/watchdog policy and
owned completion observation. Neither the model2048-byte/24-event charge nor a
generic subprocess timeout supplies a native resource limit. Existing20s/query32/
SQL4096 and physical resource policies remain unchanged; no extra success epoch,
pause/reset or borrowed PHPUnit memory limit. Numeric native wrapper caps and
their enforcement remain unqualified and block execution until reviewed.

[CancelSynchronousIo](https://learn.microsoft.com/en-us/windows/win32/api/ioapiset/nf-ioapiset-cancelsynchronousio)
requests cancellation but does not wait for completion, which may still finish
normally. Inference: a watchdog/cancel return cannot prove that an OS/SAM side
effect stopped or rolled back. Do not terminate a shared thread or claim a generic
hard cancellation contract. Unobserved completion means bounded reporting plus
quarantine and human reconciliation, not a native success or cleanup pass.

Cleanup-only must be a separately consented private-journal entry, never a caller
path, DATA receipt or new proof. Contain and observe owned descendants/channels
before removing a verified account/root; disabling/deleting accounts does not
terminate existing tokens. Unknown in-flight calls, identity drift or incomplete
cleanup block successors. Retained staging/journal records are inventoried
separately; no zero-artifact claim and no deletion of a shared protected parent.

## Acceptance layers, review and next gate

Source-scoped Feature Light covers this owner, both plan entries and the DATA
owner's successor pointer: four Markdown files, Other/design. No code bloat/UI/UX
trigger, production code, new API contract, dependency, version or test change.
The design deliberately exposes launch-handle/cancellation/identity gaps instead
of claiming that the DATA foundation solved them. No executable regression fix
or physical result is claimed. Final focused Feature Security retains the
independent bootstrap, exact ownership, no fallback and no model-to-authority
promotion boundaries. No confirmed executable defect requires a new regression
handoff. Historical whole-plugin/DS3/Database/FULL rows are not refreshed; FULL
remains due before a coherent release candidate is packaged or published.

Validation is Markdown/diff/scope review plus preservation tests and exact-head
CI, not Windows auth, privilege, filesystem, storage or native proof. Carry the
unidentified first Windows full-suite assertion from the DATA task as unresolved;
two unchanged passing repeats and Linux CI do not diagnose it. No further repeat
loop, budget weakening or new physical claim is justified by this document.

Implementation-plan progress: concrete native ownership/launch/cancellation and
protected-bootstrap prerequisites are now explicit; A1-A4/native GO stay Blocked.
The remaining meaningful work crosses the permission/elevation/filesystem gate.
Do not manufacture further synthetic adapter receipts to avoid that decision.
Recommended next gated slice: **Plugin Tester local-only V2.3 Protected Bootstrap
Staging and Fixed Read-Only Supervisor Admission Foundation/Owned Cleanup Proof**.
It needs separate R3 approval for a reviewed fixed native admission artifact,
human-controlled distinct-supervisor handoff, exact new staging/permission objects,
read-only admission execution and owned cleanup/retention. Audit first; missing
trusted bootstrap, finite artifact/dependency closure or bounded native operations
must stop before staging/execution. Fresh exact target/fixture confirmation is
required. No SAM/account creation, consumer logon/probe, existing ACL/policy change,
WordPress/SQL, production wiring, activation or rollback is included in that slice.
