# V2.3 Elevated Owned-Principal Supervisor and Cleanup Handoff Design

2026-10-26. Approved repository-only DESIGN; R0 analysis/R1 Markdown publication.
Explicit review bases dashboard631a9ae1ca0e0bc9044dd84f764785fc802633c1,
uploaderf9ec6fae15f1d92b9aa5d5c7f92aa47566da16f5,
toolkit61d58bfd8373104281ccdf93348477d76c12abc8. Parents:
[blocked feasibility preflight](V2_3_SOURCE_IMAGE_OWNERSHIP_FEASIBILITY_AUDIT.md)
and [source-ownership contract](V2_3_SETUP_OBSERVATION_ADMISSION_SOURCE_OWNERSHIP_DESIGN.md).

## Decision and authority split

Specify a **fixed local-only supervisor plus a fixed non-administrative denial
probe**, not a general elevated shell or available implementation. Only a human-
reviewed, protected supervisor artifact may eventually be elevated. Codex,
LocalWP, WordPress, the repository toolchain and consumer remain unelevated.
This design does not resolve the current process's authority blocker or qualify
an installed source image. No executable, API adapter, account, privilege, ACL,
fixture, UAC prompt, process, listener or recovery task is created or run here.

|Role|Proposed authority|Must not receive|
|---|---|---|
|Repository initiator|Prepare bounded non-authoritative intent and display results|Administrator token, password, generic privileged command channel|
|Trusted human/bootstrap|Review exact artifact/policy, protect it and consent to one attempt|Implicit authorization from a digest or chat receipt alone|
|Fixed elevated supervisor|Provision/disable/delete one owned local consumer and one exact proof root; maintain its protected handoff journal|Caller-selected users, roots, commands, modules or remote target|
|Fixed consumer probe|Read canaries and attempt a fixed finite denial matrix inside the owned root|Supervisor token/handles, source writes, profile loading or privileged group membership|
|Cleanup-only recovery|Reconcile one protected prior journal and owned objects|New proof, changed policy, account adoption, prefix sweep or successor attempt|

The trusted host/administrator remains outside the source-isolation threat
model; untrusted code sharing that authority is not protected by this candidate.
The initiator and consumer are not trusted to choose privileged operations or
attest their own identity. A standard consumer account is not a general host or
network sandbox: the fixed probe must contain no unrelated operations, and this
proof must not be reused as permission to run arbitrary untrusted application code.

UAC is a human consent mechanism here, not the source-isolation boundary. Microsoft
classifies it as defense in depth in its
[Windows servicing criteria](https://www.microsoft.com/en-us/msrc/windows-security-servicing-criteria).
Design requirement: a filtered/elevated pair of the same administrator identity
does not establish the proposed untrusted/trusted user boundary. The trusted
bootstrap/supervisor identity must be distinct from both initiator and consumer,
with independently qualified process/token/image access protection, or this
candidate refuses. No persistent administrator account or host-policy change may
be created to satisfy it. Availability of a suitable human-controlled trusted
identity is unresolved; do not solicit its credentials in chat or silently lower
the threat model when unavailable.

## Trusted handoff before any elevation

Do not elevate a script/executable or import a module directly from the mutable
repository, user temp, LocalWP tree or a caller-controlled path. Hashing a mutable
file and reopening it later is insufficient. A trusted human/bootstrap must bind
the independently reviewed artifact and all loader/configuration dependencies to
an admin-controlled protected staging image before executing elevated code.
That bootstrap is an explicit trust prerequisite, not self-certification by the
as-yet-untrusted supervisor. No signing key, installer or such image exists here.
The protected handoff area must also provide one fixed supervisor-only journal
location, distinct from the disposable proof root. Its setup and retention are
part of that future human-reviewed bootstrap scope, not caller-supplied storage.

The future review must freeze the exact bootstrap procedure, source/artifact and
policy revisions, bytes/object identity, dependency/search configuration and
effective owner/ancestor rights. No dynamic compile, PATH search, repository
imports, profile, autoloaded user module, shell interpolation or downloaded code
inside the elevated path. Do not install a service, scheduled task or policy
exception. If protected staging cannot be established without changing existing
objects' ACLs, stop and seek a new scoped decision rather than repair the host.

A human's future consent must display the fixed local target/mode, exact artifact
and policy revisions, generated attempt and ownership scope, denial cases, limits,
cleanup/recovery consequences and explicit no-WordPress/no-database boundary.
Cancellation/refusal occurs before side effects. No automated UAC acceptance,
credential solicitation in chat, implicit trust in an approval JSON or inherited
elevation. Do not launch the whole coding agent as Administrator.

## Finite input and ownership contract

The supervisor owns generation and validation of the local attempt ID, account
name, SID, root and object IDs. Non-authoritative intent cannot name an account,
SID, filesystem path, executable, DLL, server, command, credentials or arbitrary
operation. The implementation must freeze a closed versioned DATA schema with
explicit byte/count limits before any OS adapter exists; malformed, oversized,
duplicate, stale, replayed or unknown fields fail before privileged discovery.
Model acceptance means only syntactically admissible intent, never execution consent.

Use one newly generated local standard account; no existing account is adopted.
Its name is ASCII from a fixed prefix/encoding and within Windows'20-character
limit. Collision refuses without takeover, wildcard cleanup or automatic retry.
Use local SAM only, never a remote/domain server. Preserve existing users,
groups, LSA/logon policy and services; do not add administrator/service/batch/
remote rights or weaken password policy to get a test running. Validate the
actual created SID, non-admin token and effective groups/privileges independently.
[NetUserAdd](https://learn.microsoft.com/en-us/windows/win32/api/lmaccess/nf-lmaccess-netuseradd)
documents creation authority, name constraints and policy failure outcomes; its
defaults are not a substitute for the explicit reviewed account policy.

A cryptographically generated ephemeral password is confined to owned supervisor
memory and the fixed local logon API. No command line, environment, IPC message,
journal, stdout, transcript, repository or request body may carry it. Account
creation/logon use no supplied user secret. Zero owned buffers when no longer
needed; do not claim this erases OS/API copies, paging or crash dumps. Unknown
credential exposure is a failed attempt requiring exact owned-account containment,
not a request to disable host security or dump policy.

The root must be newly created under a separately approved, machine-controlled
parent, not beneath the writable Captain profile or WordPress tree. A proposed
ProgramData location is only a candidate: actual ancestor/delete-child rights,
resolved volumes/objects and absence of reparse/mount/alias escape must qualify.
Create protected security descriptors at creation, not after an exposure window.
Use an effective admin-controlled owner, not the unelevated initiator or consumer;
prove owner/DACL rights instead of assuming an Administrators label is sufficient.
No existing ancestor ACL is changed. If the actual parent is unsuitable, refuse.

Stage only finite non-secret canaries and the reviewed fixed probe dependencies.
No genuine core/configuration/credentials are copied in this first denial proof.
Keep sealed image and mutable test controls in distinct fixed subtrees of the
single disposable proof root; the private journal stays in the protected handoff
area. A consumer-writable control must not grant write/delete-child/owner authority
on the root, image or journal. Its exact
allowed mutations are test controls only, not exceptions to image protection.
Image members and every loader/configuration path must be non-writable by both
consumer and initiator; enumerate and reject extra links/streams, mutable aliases,
preexisting write handles and out-of-image dependencies. Open/read/execute is the
only intended consumer image access; no create, overwrite, append, delete,
rename, parent delete-child, owner/DACL change or replacement authority.
See [file security](https://learn.microsoft.com/en-us/windows/win32/fileio/file-security-and-access-rights)
and [standard rights](https://learn.microsoft.com/en-us/windows/win32/secauthz/standard-access-rights).

## Fixed probe launch and evidence boundary

Launch only the frozen absolute probe image with a fixed argument grammar,
explicit minimal environment and working directory. No interpreter/provider or
command chosen by the caller. A candidate is local `CreateProcessWithLogonW`
with no profile load and **never LOGON_NETCREDENTIALS_ONLY**, which would retain
the caller's local token. Its local-logon and window-station/desktop requirements
must qualify without changing existing desktop ACLs or logon policy; only newly
owned private desktop/window-station objects may be considered. See
[process/logon contract](https://learn.microsoft.com/en-us/windows/win32/api/winbase/nf-winbase-createprocesswithlogonw).
No fallback to supervisor identity, default shared desktop or another logon API.

The implementation must independently bind the created process handle/creation
identity and actual token SID to the recorded account before releasing a suspended
probe. Job containment/no-breakaway, handle/pipe ownership and descendant closure
must be established first or the still-suspended owned child is terminated as
failure. No privileged inherited handles or caller-chosen PID termination. A
returning launch call, claimed PID/SID, child message or exit0 is not identity proof.
[Job objects](https://learn.microsoft.com/en-us/windows/win32/procthread/job-objects)
can kill contained children on final handle close; they do not clean up accounts,
files, permissions or prove natural completion. No API feasibility is asserted.

The later probe matrix must demonstrate allowed reads under the actual consumer
and initiator contexts, then denial of writes/replacement/deletion, owner/DACL,
ancestor and alias/open-handle escape. Distinguish access-denied from missing
path, malformed operation or nonexecuted case. Include non-vacuous controls on a
separate owned mutable canary, so a probe that simply reports every denial fails.
Never deliberately hand the consumer a privileged write handle; reject such a
handoff before execution and verify no inherited supervisor authority.
Keep the source-image lease through natural probe shutdown, engine/process exit,
both pipe EOFs and owned cleanup. Forced termination is containment, not a pass.

## Journal, failure and exact cleanup

Use a bounded protected local journal in the already established handoff area,
created before the first account/proof-root mutation, with
closed policy/artifact/attempt revisions, ordered intent/result states and exact
account SID/name, object/volume IDs, process creation identity and cleanup facts.
Never persist the password or privileged handles. Flush intent before and result
after each side effect; journal durability is not atomicity with SAM/filesystem.
Creation interrupted before SID/object capture remains ambiguous: no automatic
account adoption or deletion by prefix/comment/nonce alone. Retain a non-secret
quarantine record for human reconciliation; no successor proof until resolved.

On failure stop proof admission, contain only owned processes and close the
owned private channels. Disable the verified owned account when needed to prevent
new logons, but never treat disabling/deleting it as terminating existing tokens.
Verify owned process/descendant exit and EOFs before account removal. Resolve the
exact local name back to the expected SID immediately before any account mutation;
name/SID drift, unexpected concurrent administrator action or uncertain creation
requires quarantine, not a second lookup that silently adopts a different object.
[NetUserDel](https://learn.microsoft.com/en-us/windows/win32/api/lmaccess/nf-lmaccess-netuserdel)
is name-based, not an atomic SID-conditional delete. Host administrators must not
concurrently rename/reuse the proof account; unknown concurrency refuses cleanup.

Delete only the journaled owned root/members after handle/object identity and
ancestry verification, without following links or recursive name/glob sweeps.
Unrecognized children, aliases, account metadata or identity changes are blockers.
Do not remove user profiles, registry trees, existing accounts, shared directories
or inherited host settings. The no-profile launch claim needs actual verification,
not broader cleanup authority if an unexpected profile appears.

Never promise rollback of SAM side effects on crash or rely on a finally block
alone. Cleanup-only recovery uses protected journals and the same fixed policy,
requires explicit human handoff, cannot renew/launch a proof, and is itself bounded.
Unknown leftovers are reported with private exact ownership records, not hidden by
log deletion. Publish only non-secret attempt/outcome/counts and cleanup status;
keep raw SIDs/names/paths private to the human cleanup record.
Explicitly inventory any retained approved handoff/journal records separately
from deleted account/probe-root fixtures; never claim zero artifacts while those
records remain. Their later removal needs exact ownership and retention authority,
not deletion of the shared protected parent.

Freeze finite control-plane watchdog/output/journal bounds during the DATA and
adapter review before physical execution. A blocked/uncancellable OS call cannot
be relabeled bounded success. Human consent occurs before a new execution epoch;
provisioning or recovery never pauses/resets an admitted proof's original epoch.
Existing20s/query32/SQL4096 and other proof limits remain unchanged; no native
heap allowance or extra success budget is introduced. Cleanup timeout means
incomplete/quarantined, not automatic retry or a cleanup pass.

## Review, acceptance layers and next gate

Canonical source-scoped Feature Light: four Markdown files (owner, both plans,
preflight successor pointer), Other/design. Draft explicitly separates trusted
bootstrap from mutable source, creation intent from completed ownership and
account deletion from token/process closure; mutable positive controls are kept
outside the sealed image with no ancestor authority. Review corrected journal
placement so intent can precede proof-root creation and retained control records
cannot be hidden by a zero-artifact claim. No executable defect/fix or test
claimed. No meaningful Bloat/UI/UX trigger. Final focused Feature Security retains
all operational gates; final clarification rejects same-identity UAC separation
as the trust boundary. No new executable path or confirmed regression handoff.
Historical whole-plugin/DS3/Database/physical/FULL rows are not refreshed.

Pure DATA tests may validate closed bounded intents/journal transitions and exact
ownership/refusal bookkeeping only. Require useful positive transitions with all
actual elevation/account/file/process/cleanup/wiring authority false, and semantic
guard controls that fail if mandatory checks are removed. They cannot establish
protected staging, effective ACLs, account/logon/job support or physical cleanup.
Those still require independently reviewed adapters and fresh exact local target/
fixture/elevation approval; this design grants none. All A1-A4/native GO stay Blocked.

Implementation-plan progress: the missing elevated provisioning/cleanup boundary
now has an explicit non-general-purpose handoff and failure contract. Next gated
slice: **local-only V2.3 Supervisor Intent and Cleanup-Journal DATA Safety
Foundation**, in-memory only. Freeze finite schemas/bounds, one-attempt lineage,
ambiguous-creation/quarantine and cleanup-only refusal rules; no Windows imports,
OS/IO adapters, elevation, authentication, accounts, storage, processes or wiring.
