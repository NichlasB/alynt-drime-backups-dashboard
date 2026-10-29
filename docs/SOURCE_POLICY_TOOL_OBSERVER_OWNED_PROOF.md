# Existing-Tool Bounded Observer/Termination Owned Proof — Blocked

## Authority, restore baseline and disposition — 2026-10-29

Explicitly approved local-only proof using existing bundled PowerShell and
owned disposable filesystem/stdio/process fixtures, after the
[socket-free foundation](SOURCE_POLICY_MINIMUM_HOST_ADMISSION_SAFETY_FOUNDATION.md).
Clean/synced dashboard restore baseline `911e9a35abdecbf3f285cb9236961ddf7b817563`.
This is the separately approved controlled R3 process/file proof, not another
completed task in the previous 1/8 safe-local batch.

**Physical verification failed and cleanup was confirmed.** The single ordered
attempt stopped at `positive-observer` with `tool-stderr-refused`, after 13
controller checks and before any completed proof group. No valid module terminal
was accepted; module counts/catalog/latency and observer feasibility are not
established. The child startup/guard/API cause cannot be inferred from the safe
parent error. Do not label this a module-limit, permission or provenance failure.

No automatic repeat, budget increase or acceptance/guard change follows a
refusal. The physical proof remains blocked even if ordinary repository CI is
green. The timeout and cancellation groups were not reached and receive no
physical credit. No candidate PHP/DLL, installation/download, WordPress/core,
authentication, database/SQL, site/live operation, actual host-trust designation,
production wiring, package/release/tag or deployment occurred. Site Operations
was consulted; no site is selected/touched, so no target/Novamira call applies.
Uploader/toolkit remain read-only; no whole-plugin review row is refreshed.

## Fixed source boundary and preflight

New development-only sources (not production bootstrap consumers):

- `source-policy-tool-observer-owned-proof.ps1`: explicit `-RunOwnedProof`, no
  extra arguments, Windows x64 only; fixed existing tool and ordered cases.
- `source-policy-tool-observer-worker.ps1`: fixed held/released worker, exact
  run/PID/start/source correlation and sole private ACK; no child creation.
- `source-policy-tool-observer-child.ps1`: fixed observation child, optional
  controller-selected hold for the later controlled timeout case. No launcher,
  native interop bridge, credentials, network or application interface.
- `source-policy-tool-observer-transport.ps1`: bounded private streams, exact
  line/summary checks, original Process-object identity and exact owned cleanup.

Fresh metadata confirmed configured PowerShell 7.6.5, .NET 10.0.11, x64 OS/tool,
and the same configured image/path and SHA256
`362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139`.
The image hash was checked again read-only after failure and remained identical.
This is existing-tool correlation, not independent publisher verification or a
Windows/CRT trust grant. No candidate runtime was substituted.

The controller requires the existing non-reparse ignored `build` parent, creates
one fresh GUID-named `build/adbtool-<run32>` root, copies/freeze-checks the two
small fixed child sources, and creates owned work/tmp directories and a canary.
No user-selected executable/source/command/target is accepted. Children use the
fixed bundled image, typed arguments, hidden shell-free startup, no profile and
a cleared, finite environment with owned scratch paths and telemetry/update
opt-outs. Those flags/source restrictions are not an OS-enforced network sandbox.

Original Process objects, handles, PID/start and source roles are retained and
registered before fallible post-start metadata reads. Only original objects may
be terminated; no name scan, rediscovered-PID cleanup, process-tree kill, service,
account or permission changes. The observer's exact target PID lookup is an
observation endpoint, not ownership authority: target start/main image/live
checks surround observation, while the controller retains and checks the
original held worker. No unrelated process inspection is requested. Fixed child
sources have no descendant-launch paths; no OS-enforced descendant containment
or exhaustive system-wide absence is claimed.

## Intended ordered cases and proof limits

The frozen ordered proof is:

1. Genuine held worker; normal observer ready and module result; clean observer
   exit/drain; exact ACK; released worker and clean worker exit/drain.
2. Only after that prerequisite: fresh held worker plus controlled held observer;
   reach the original observer deadline, then terminate/drain only those owned
   objects and confirm their absence. A held observer is a controlled blockage,
   not an induced native module-API hang.
3. Only after both: fresh held worker; cancel a bounded WaitForExitAsync wait,
   verify the original worker remains alive, then terminate/drain and reconcile.

All share a 25,000 ms monotonic runtime/cleanup ceiling, work below 20,000 ms,
observer startup/observation below 2,000 ms, and cleanup attempts within the same
remaining budget. No separate cleanup budget extends the original deadline.
Frame limit 512 bytes, stdout 16 KiB, stderr counted/discarded with overflow
sentinel 4097. Positive completion requires zero stderr, exact finite frames,
identity, clean exit and pipe drain. Failure cleanup can drain/discard error
output but cannot turn the refused proof into positive completion.

Module/main-image APIs are confined to the separate observer child. It is
written to refresh the exact held target twice, compare the complete metadata
rows, and bound count to 2–128, each ModuleMemorySize to 32 MiB and aggregate
mapped-image size to 256 MiB. These are **mapped-image metadata**, not file byte
sizes, file hashes or signature verification. A private CreateNew catalog is
bounded to 128 KiB; only its count/size/digest summary uses stdout. Expected
tool/kernel presence and two-snapshot consistency cannot establish an externally
reviewed tool module allowlist, transient load history or future candidate
dependency closure. No observed row would automatically become an allowlist.
No module catalog/result was accepted in this failed attempt.

The use of [Process.Modules](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.modules?view=net-10.0)
does not promise startup/transient completeness. Microsoft's
[Kill contract](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.kill?view=net-10.0)
requires independent exit observation and does not equate parent exit to all
descendant exit. The controller does not perform a synchronous module getter
outside the observer. Ordinary process start/identity and bounded owned-file
reads remain existing local APIs; this proof is not a universal hard real-time
guarantee that Windows can never block or refuse termination.

## Executed result, cleanup and diagnostic gap

Executed exactly once:
`scripts/manual/source-policy-tool-observer-owned-proof.ps1 -RunOwnedProof`.

Observed safe output, exit 1:

```text
tool-proof failure stage:positive-observer reason:tool-stderr-refused checks:13
tool-proof failure_cleanup_confirmed:1 fixtures_absent:1 owned_absence_confirmed:1
```

The genuine worker held handshake passed, then the observer process emitted
stderr. The transport deliberately retained counts, not raw error payloads. It
does not currently expose a validated child phase/reason; startup versus an
observer guard/API failure therefore remains unresolved. No claim is made that
the module getter was reached or that a catalog was successfully produced.

Cleanup retained the first failure, terminated only the still-live owned
objects, drained both original process pipes and confirmed both owned identities
exited before disposing them. All exact owned files/directories were removed
only after validating the absolute GUID root, entry allowlist and non-reparse/
empty-directory conditions; no recursive or broad delete. A read-only follow-up
found zero `build/adbtool-*` fixture directories. No recovery fixture remains.
The canary/source final readback stage was not reached: do not claim its checks
passed or invent all-host/application checksum evidence. Existing tracked
runtime/native/application files remain unchanged by Git comparison and source
scope; no application/database operation was run.

## Repository validation and publication

Before the physical attempt, all five PowerShell files (four harness sources
and the pure unit script) parsed successfully. The actual transport parser unit
script passed 32 assertions using positive and malformed records, with zero
process creation or fixtures. Three new PHP source-backed tests passed 110
assertions for fixed dispatch, closed scope, module-API isolation, identity
ordering, budgets and exact nonrecursive cleanup. They are static regressions,
not execution of the Windows proof on Linux CI.

Fresh ordinary repository QA on existing trusted PHP 8.5.1:

- Related family plus new source tests: 276 tests / 11,606 assertions, no
  failures/skips, including the existing thirteen in-memory killed controls.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,319 tests /
  20,814 assertions, no failures; two existing Sodium-unavailable signing tests
  skipped. These isolated checks do not replay the failed physical attempt.
- PHPCS 243 files; admin 23/23, no failures/skips; structure 149 runtime / 233
  PHP support / 223 test files / 220 class names and asset build passed.
- Strict Composer validation, Composer audit and npm moderate audit passed,
  zero known advisories/vulnerabilities; no dependency install/update.
- 139 owning local documentation targets, whitespace and final source/ownership/
  security/resource/error/compatibility/test-integrity review passed. All existing
  tracked non-documentation files remain identical to the restore baseline;
  only new development harness/tests are added, with no generated tracked delta.
  Uploader/toolkit remain clean/read-only at 3dfcb15/c067eb6. No new skip or
  assertion weakening, UI/UX workflow or historical whole-plugin review refresh.

Exact-head publication evidence follows after push and ordinary CI.
No runtime/native guard, ADBM0/ADBMP1/ADBC1/2 contract, host-admission helper,
source/artifact pin, dependency manifest, workflow, schema/version or production
wiring changes. No release/package/native workflow is dispatched. Ordinary CI
cannot close the failed physical verification.

Implementation-plan progress: a bounded owned-tool harness and parser/source
regressions exist; the approved physical prerequisite is **not complete**.
Previous safe-local progress remains 1/8, plus this separately approved failed
physical attempt; no failure/cleanup/evidence closeout is counted as product
feature completion.

Recommended next gated slice: **Harness-only Observer Phase/Reason Diagnostics
and One Fresh Owned Observer Proof**. Add only finite source/run/PID-bound safe
diagnostic phase/reason codes and source-backed/pure-parser regressions, then
one fresh ordered attempt with the same bundled image and fresh owned fixtures.
Keep zero-stderr positive acceptance, original guards, module/time/resource
bounds and exact cleanup unchanged. Never print raw exception messages, module
paths or streams; diagnostics cannot authorize a positive result. Stop again
on failed prerequisites. Candidate PHP, installation, WordPress/database/site,
actual host-trust designation and production operations remain disabled.
