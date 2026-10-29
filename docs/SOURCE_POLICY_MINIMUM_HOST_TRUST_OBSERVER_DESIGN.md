# Existing-Host Trust Profile and Bounded Observer Admission Decision

Forward progress: the [socket-free safety foundation](SOURCE_POLICY_MINIMUM_HOST_ADMISSION_SAFETY_FOUNDATION.md)
implements only supplied-DATA consistency and virtual-clock observer/cleanup
guards. No actual trust designation, physical proof, runtime loading or wiring
is enabled; follow its next gated existing-tool proof rather than replaying this
design or historical audit as execution authority.

## Authority, decision and restore baseline — 2026-10-29

Approved **design only**, at clean/synced dashboard baseline
`7754cfb211688cdb8e589117343f0b0eea4fdc01`. R2 repository documentation of a
later explicitly approved host/process trust boundary. Ordinary repository QA,
commit/push and exact-head CI are in scope; implementation and host experiments
are not. Only this record, the owning plan and predecessor forward pointers change.
Uploader/toolkit remain read-only. No AI Workflows checklist row is refreshed.

Choose a **narrow, explicitly operator-approved existing-host trust assumption**
as the proposed alternative to independently verified offline provenance for
this controlled Windows/installed-VC profile. Do not add a compiler, SDK, trust
helper, verifier installation, certificate exception or global policy change.
This decision defines the alternative; it does **not** designate this host as
trusted, change an executable guard or authorize any process/runtime launch.

The operator would be accepting an unverified origin/servicing assumption, not
receiving an equivalent cryptographic guarantee. If that residual risk is not
explicitly accepted for a future exact physical scope, stop before loading.
Do not silently substitute this policy when signature verification is unavailable.
The existing no-load rule remains effective until that separate gate is passed.

No download, installation, installed-profile refresh, signature/catalog call,
candidate-runtime/DLL loading, benign/native process experiment, authentication,
SQL, WordPress bootstrap, site operation, storage/permission/credential/schedule
change, production wiring, release/package/tag or deployment occurs here.
Existing trusted PHP/Node repository QA is not candidate-runtime execution.
No site target is selected; no Site Operations confirmation or Novamira call
is appropriate for this repository-only design.

## Evidence and exact change to the proposed policy

The [read-only audit](SOURCE_POLICY_MINIMUM_WINDOWS_DEPENDENCY_OBSERVER_AUDIT.md)
establishes metadata for build 26200/UBR 9711, x64, the 13 named System32 files,
installed VC 14.50.35719.0 registrations and the existing PowerShell 7.6.5/.NET
10.0.11 toolchain. Those historical observations are not fresh identity checks,
runtime compatibility, authenticated publisher provenance or a launch receipt.
The audit did not establish a guaranteed-offline verification route. Microsoft's
[WINTRUST_DATA contract](https://learn.microsoft.com/en-us/windows/win32/api/wintrust/ns-wintrust-wintrust_data)
requires an explicit cache-only flag for no-network code-signature verification;
this design neither invokes nor reimplements that API.

The [preflight design](SOURCE_POLICY_MINIMUM_RUNTIME_PREFLIGHT_INTEGRATION_DESIGN.md)
already separates publisher artifacts, Windows platform behavior and installed
CRT. Its unavailable-provenance stop now leads to this **proposed** operator
decision, not a permissive fallback. Keep the following evidence classes distinct:

| Class | Future admission basis | What it never proves |
| --- | --- | --- |
| PHP/core publisher artifacts | Unchanged pinned acquisition, complete source/tree/static checks and fresh owned readback. | Installed Windows/CRT provenance, process behavior or database qualification. |
| Windows servicing/loader boundary | Separate explicit operator acceptance of the exact controlled host profile; fresh bounded identity/correlation checks. | Independent offline signature verification, uncompromised OS or hostile-administrator isolation. |
| Existing installed VC runtime | Its own explicit operator acceptance, exact System32 file/version/hash/architecture and registration correlation. | A Microsoft publisher signature or PHP ABI compatibility merely from version agreement. |
| Existing trusted observer/toolchain | Named existing tool image/source identities and separately proved owned-process behavior. | Authenticity from a self-reported path/hash, or safe termination from API presence. |

Hashes detect changes relative to a reviewed snapshot; they do not establish who
published it. CompanyName/owner/registry metadata remains a claim/correlation.
Never emit `signature_verified`, `offline_provenance_verified` or equivalent as
true for operator trust. Do not mutate historical `platform_verified=false`,
`dependency_closure_qualified=false`, `load_authorized=false`, ADBM0 or ADBC1/2
records. ADBMP1 remains a non-authorizing consistency protocol.

## Minimal proposed host-profile contract

Use one fixed policy version and one finite profile for this existing Windows
installation, not a machine inventory service or user-selectable executable API.
The future owner must bind the explicit approval to the audited record, exact
repository/controller sources, intended proof scope and fresh run. A serialized
boolean or digest supplied by a worker/caller cannot establish human approval.
No reusable approval token, secret, trust store or durable launch capability is
introduced. Any future physical approval must name the operator-trust assumption
plainly, in addition to the exact fixture/process scope.

Required profile dimensions, rechecked before a later approved launch:

- Native x64 Windows root and system-directory identities derived from trusted
  APIs, exact build/UBR and tool versions/images. Treat the audit's ProductName
  literally, not as an inferred edition or supported-profile change.
- Separate Windows, installed-CRT and trusted-tool roles; fixed file names,
  architecture, bounded size, exact path identity and reviewed SHA256/version
  correlations. The audit table is a historical comparison reference, not a new
  publisher allowlist. A Windows update, CRT/tool update, file/identity change,
  missing field or contradictory observation expires this profile; no automatic
  repinning, download, downgrade, repair or "newer is safe" rule.
- No source of authority from PATH, caller SystemRoot, a user DLL directory,
  basename alone, a `reviewed-platform-profile` string or registry absence.
  Reject aliases, ADS, traversal, reparse/redirection or ambiguous path identity.
  The observed `system32`/`System32` spelling needs a reviewed identity-bound
  representation, not blind lowercasing/capitalization to satisfy a pure helper.
- Initially only the exact reviewed System32 dependency locations; no blanket
  Windows-prefix or WinSxS-prefix admission. Additional platform modules/locations
  require finite source/profile review before candidate loading, not acceptance
  because a first launched child happened to load them. API-set names remain
  contracts, not guessed literal DLL paths. Windows loader internals remain within
  the stated operator platform assumption, not recursively reimplemented.
- Preserve the three authenticated application roles, complete artifact checks,
  frozen worker/producer sources, empty owned working directory and exact clean
  environment from the preceding design. Trust in Windows does not admit any
  application DLL from Windows, LocalWP, current PHP, CWD or an arbitrary directory.

Fresh checks are detection in a controlled owned-fixture model, not immutable
filesystem proof. This policy explicitly does not defend against compromised
Windows, an administrator, a malicious/racing same-user process, substituted
trusted tooling or transient loads unseen by a held snapshot. Do not resurrect
protected staging, SAM/accounts, elevation or native compiler work to overclaim
those properties. If the required identity checks cannot be obtained safely with
existing tools, refuse and report that exact limitation.

## Bounded observer admission, before the candidate

Metadata availability is insufficient. Microsoft documents
[Process.Modules](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.modules?view=net-10.0)
as a refreshed collection; it may be empty during startup and may throw. Plan an
exact owned ready/held handshake, then refresh and enumerate only that owned
process. No WaitForInputIdle assumption for a console worker; no unrelated
process scan or transient-load-completeness claim.

The later private adapter must isolate potentially blocking module/file reads
in a separately owned observer process. A timer around a synchronous getter,
abandoned thread/task or cancelled wait is not bounded cleanup.
[WaitForExitAsync cancellation](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.waitforexitasync?view=net-10.0)
cancels the wait operation, not proof of child termination. Microsoft documents
[Kill](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.kill?view=net-10.0)
as asynchronous and warns that parent exit does not prove descendant exit.
These are API limits, not measured behavior of this host.

Keep the existing shared 25,000 ms monotonic ceiling, at most one fixed worker
and one observer child concurrently, 128 module rows, 32 MiB/file and 256 MiB
aggregate. Plan an observer deadline of at most 2,000 ms, within the remaining
work budget; stop new work at 20,000 ms to reserve at most 5,000 ms for cleanup
inside the same ceiling. These are proposed limits, not fresh performance facts
or a guarantee that Windows can always terminate a process. Do not expand a
budget after a failed physical attempt.

Bind each original Process object/handle, PID/start identity, executable and
source to the same run before observation, acknowledgement or termination. Do
not reopen a reused PID and infer ownership. Use hidden, shell-free fixed typed
arguments, no profile startup, fixed audited code and a clean environment. The
worker and observer must not spawn descendants. Unexpected descendants or
uncertain ownership refuse; never broad-kill by process name or guessed tree.

For the existing-tool prerequisite, choose the already configured bundled x64
PowerShell image for both fixed worker and observer roles, with separately frozen
minimal sources. Do not substitute another shell, installed PHP or an arbitrary
executable. Its own reviewed tool-module closure is separate from the candidate's
Windows/VC/application closure; a PowerShell-specific DLL path can never become
an allowed candidate dependency. This specifies a later proof, not a launch now.

Require finite canonical rows, complete per-stage expected/observed comparison,
fresh file correlations and a held process throughout the observation window.
Empty/partial/duplicate/extra rows, changed files, inaccessible properties, stale
identity, malformed output, stderr, timeout or early exit permanently refuse.
Do not teach the expected set from the first observed candidate or relax a module
check to make it pass. The compiled PHP base-extension manifest is also a source/
profile prerequisite, not a set learned and blessed during the first launch.

The controller owns ACK/release, exit, pipe drain and cleanup evidence. After any
failure, acknowledge nothing further, attempt bounded cleanup only on the exact
owned objects, drain/dispose owned streams and verify owned identities exited.
Disposal/cancel/EOF/zero exit alone is not absence evidence. If an observer cannot
be terminated/reconciled within the bound, retain only exact owned recovery
metadata, report failure and stop; do not delete a possibly in-use fixture,
retry automatically, kill unrelated processes or claim cleanup succeeded.
Do not log raw streams, environment, credentials or whole module paths in public
summaries. Private reviewed evidence may retain only bounded non-secret facts.

## Source ownership, proof gates and non-vacuous tests

No source changes in this slice. `source-policy-minimum-launch-data.php` and
`source-policy-minimum-preflight-transcript.php` remain supplied-DATA models;
their tests are their only consumers. Preserve exact current labels/shapes and
all false authority flags. Do not overload `reviewed-platform-profile` to mean
authenticated operator consent or change ADBMP1 to accept a human approval claim.

The next implementation should be one small development-only **pure DATA**
host-policy/observer-admission helper and its isolated tests, not a launcher,
host-discovery adapter or unavailable factory hiding a success path. It may
distinguish consistent operator-trust claims from cryptographic claims and model
ordered deadlines/identity/cleanup. It must return no executable path, process or
write-capable handle and never authorize a load. No IO, callback, reflection,
installed-profile discovery or genuine approval acquisition belongs in it.
Use the predecessor records as references rather than duplicating host tables.

Required later tests include a genuine positive DATA-consistency sequence plus
paired negatives for missing/replayed/wrong-scope approval claims, false signature
claims, host/CRT/tool/source changes, path-case ambiguity, wrong PID/start/object
binding, partial/stale modules, timeout, cancelled wait without exit, missing
drain/absence and sticky refusal. Assert that all authority flags stay false on
both positive and negative sequences. Assertion-killed controls must demonstrate
that removing a required decision/identity/deadline/cleanup guard breaks tests;
an owner that always returns unavailable must not satisfy the positive contract.
This remains synthetic evidence, never human approval or physical process proof.

Ordered gates, each requiring its own appropriate authority:

1. This design and routine QA: no trust designation or execution.
2. Socket-free host-trust/observer-admission foundation: isolated DATA/virtual
   clocks only, current physical/runtime/native boundaries still closed.
3. Separately approved bounded owned **existing-tool** observer/termination proof:
   trusted tool children and owned filesystem/stdio fixtures only, no candidate
   PHP, WordPress, database, download or installation. Require positive held
   observation plus controlled timeout/cleanup proof, not just API metadata.
4. Fresh exact host/trust acceptance, complete expected source/module profile,
   owned authenticated artifacts and separate approval for the two-stage legacy
   connection-free preflight. No SQL or native adapter construction; prove both
   held stage observations and final cleanup before claiming that narrow profile.
5. Native/database qualification is a later separate approved scope. Preflight
   success grants no connection, write, production wiring, release or deployment.

No step can replay a historical receipt or synthetic digest as physical evidence.
No installation/native bridge fallback or repeated experiments are authorized
by a refusal. Full pre-release review remains reserved for a coherent release
candidate; this design is not whole-plugin readiness or Database Review credit.

## Validation, publication and next boundary

Fresh ordinary repository QA on existing trusted PHP 8.5.1:

- `php vendor/bin/phpunit --do-not-cache-result --filter SourcePolicyMinimum`:
  157 tests / 7,233 assertions, no failures/skips. Existing synthetic guards and
  positive/hostile sequences ran; no new helper or behavioral test is claimed.
- `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,200 tests / 16,441
  assertions, no failures; two existing Sodium-unavailable signing tests skipped.
  This is not an all-tests Windows pass or candidate PHP 7.4 runtime proof.
- `npm run lint`: 242 files pass. Admin 23/23, no failures/skips; structure 149
  runtime / 231 support / 219 test files / 216 class names and asset build pass.
- Strict Composer validation, Composer audit and npm moderate audit pass, zero
  known advisories/vulnerabilities; no dependency install/update.
- 143 owning local documentation targets, whitespace and source-scoped design
  review pass. Review covers explicit authority versus claims, version/path/role
  drift, stale/partial evidence, synchronous-call/timeout and cleanup limits,
  compatibility, errors/resources and test integrity. No host API is invoked to
  substantiate the design; public API contracts are not physical evidence.
- All tracked non-documentation files remain identical to the restore baseline;
  no generated tracked delta. Uploader/toolkit stay clean/read-only at
  3dfcb15/c067eb6. No new test skip, assertion weakening or historical checklist
  refresh; UI/UX and whole-plugin pre-release workflows do not apply to this slice.

Design publication `868e0cc7970274487814b89a34b3b002482b44eb` is pushed;
[CI 40936999758](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40936999758)
succeeded for that exact head. Actual PHP 7.4 log: 2,200 tests / 16,443 assertions;
PHP 8.3: 2,200 / 16,448, both without failures/skips. Admin 23/23 each, zero
failures/skips; manifest/audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. Setup Node emitted tool-level `punycode`/`url.parse()` deprecation
warnings; no source or acceptance rule is changed to suppress them. This
evidence-only closeout receives its own pushed exact-head ordinary CI and
clean/remote verification at handoff; no manual/native workflow is dispatched.
No behavioral regression is implemented or claimed by a design-only document.
Compatibility, schema, source/artifact pins, runtime/native workers, acceptance
rules, actual guards and production wiring remain unchanged. Only the proposed
future operator-trust decision differs; it must be explicit and separately gated.

Implementation-plan progress: the provenance gap has an explicit proposed policy
resolution and the observer has finite admission/cleanup requirements. Physical
trust, process feasibility, runtime loading and native qualification remain open.

Recommended next gated slice: **Local-only Socket-Free Explicit Host-Trust and
Observer-Admission Safety Foundation**. Implement only the pure DATA/virtual-clock
contract and non-vacuous isolated tests through validation, commit, push and CI.
Keep actual trust designation, discovery, process/observer IO, runtime loading,
installation, native execution and production wiring disabled. Do not proceed
to physical proof or treat this design approval as acceptance of the host risk.
