# V2.3 Fixed Source-Image Ownership Feasibility Preflight

2026-10-26. Approved R3 local-only feasibility/owned-fixture scope; **Blocked
before fixture creation**, not a completed denial proof. Owner contract:
[source-ownership design](V2_3_SETUP_OBSERVATION_ADMISSION_SOURCE_OWNERSHIP_DESIGN.md).
Review bases: dashboard0a7e44899cee8c781c20e3cea1ea85e8d2a387ff and
uploader763a559d6ba0b23ca940696cb094b01cf79956fa. This change is documentation only.

Successor: [supervisor/cleanup handoff design](V2_3_ELEVATED_OWNED_PRINCIPAL_SUPERVISOR_DESIGN.md)
specifies the proposed trust boundary; it does not supply an elevated artifact,
execute a proof or change this dated preflight's Blocked outcome.

## Target and permitted discovery

The user freshly confirmed `plugin-tester`, `local-only`, LocalWP “Plugin Tester”,
`http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`.
Novamira HQ guide then complete inventory succeeded; the exact HTTP origin is a
connected WordPress profile. No doctor/ability/authentication/HTTP/SQL operation
was performed. Confirmation authorized the bounded filesystem/principal proof,
not WordPress bootstrap, database execution or production wiring.

## Observed facts and decision

Read-only preflight in the actual unsandboxed execution context returned:

|Check|Observed|What it does not establish|
|---|---|---|
|Windows identity effective Administrator / SYSTEM|Both false|No elevated provisioning/cleanup authority established|
|Available token privileges|No token assignment, impersonation, backup, restore, ownership or debug privilege listed|Not an audit of every possible delegated host right|
|PowerShell / account module|64-bit PowerShell7.6.5; `New-LocalUser` available|Command availability is not account-creation permission|
|Confirmed target|Directory exists; ordinary directory attributes, no reported link type|Not transitive path/ACL/loader or installed-source qualification|
|C: volume|Ready, NTFS|Filesystem format alone does not establish effective isolation|

The sandboxed attempt to query volume data through CIM failed with access
unavailable. It is not physical proof failure; the read-only unsandboxed
`System.IO.DriveInfo` check supplied the narrower format/readiness observation.
The Administrator/SYSTEM result was rechecked outside the sandbox, not inferred
from a sandbox-restricted token.

**NO-GO for this proof in the current execution context.** No reviewed elevated
provisioning/cleanup supervisor or separately owned consumer identity is available
in the approved implementation. Do not attempt account creation just to discover
whether it fails. No UAC launch, privilege adjustment, borrowed account/token or
existing ACL/account change was attempted. This does not prove that Windows or
this machine cannot support the candidate; it identifies a missing prerequisite.

Microsoft describes administrative local-account management in
[local accounts](https://learn.microsoft.com/en-us/windows/security/identity-protection/access-control/local-accounts).
[Restricted tokens](https://learn.microsoft.com/en-us/windows/win32/secauthz/restricted-tokens)
can support restricted processes without a new account, but are not an automatic
substitute for this design's independently owned image/consumer boundary. Their
effective rights, owner/parent/open-handle cases, process/desktop access and full
lifetime would require a separately reviewed containment design and real proof.
No restricted token or process was created here. Do not treat same-user hashes,
read-only attributes or synthetic positive tests as the missing denial proof.

## Proof and cleanup limits

- No disposable account, credential, principal, directory/image, permission,
  alias, open handle, listener, helper process or native test was created.
- No fixture cleanup was necessary; this is zero creation, **not cleanup proof**.
- No allowed-read/denied-write, rename/delete, owner/DACL, ancestor, reparse,
  hard-link, inherited-handle or continuous-lifetime assertion ran.
- No WordPress/bootstrap, database/session/authentication, installed PHP/core,
  application-state checksum, service, production ingress/wiring, activation or
  rollback qualification is claimed. No package/release/tag/deploy.
- No new test or executable path. Local preservation checks and exact-head CI
  are publication evidence only; they cannot turn this preflight into a pass.

All A1-A4 and native GO remain Blocked. Existing query/time/resource budgets and
dashboard0.1.77/uploader0.5.25 compatibility remain unchanged.

## Source-scoped review and next boundary

Canonical Feature Light and final focused Feature Security apply only to the
four changed Markdown files: this audit, both plan entries and the design's
successor pointer. Other/documentation; no executable security fix, regression
handoff, UI/UX or meaningful Bloat trigger. No DS3/Database/physical/FULL review
or historical whole-plugin readiness refresh is claimed.

Implementation-plan progress: A4 now has a concrete execution-authority blocker,
not another synthetic implementation task. The physical proof remains incomplete.
Recommended next gated slice: **repository-only Elevated Owned-Principal
Supervisor and Cleanup Handoff Design**. Define a small independently reviewed
provisioning/cleanup supervisor with exact owned-account/root limits, ephemeral
secret handling, crash recovery and denial-test identity verification before
asking a human to elevate only that supervisor. Do not run Codex, LocalWP,
WordPress or the eventual consumer with Administrator authority. Design approval
would not authorize UAC, account creation or a fresh physical proof.
