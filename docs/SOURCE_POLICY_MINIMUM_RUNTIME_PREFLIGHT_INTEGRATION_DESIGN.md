# Minimum-Profile Dependency Closure and Fixed Runtime-Preflight Integration Design

Forward progress: the approved [socket-free safety foundation](SOURCE_POLICY_MINIMUM_PREFLIGHT_SAFETY_FOUNDATION.md)
implements the synthetic gate only, without host discovery, acquisition or loading.
Follow its read-only dependency/observer audit gate; this design and historical
proofs are not upgraded to physical preflight or native qualification.

## Authority, decision and restore baseline — 2026-10-29

Approved repository-only design at clean/synced dashboard
`9ea299d75c4d0bc873d04eb82af936ed0e1266b6`. R2 documentation of a later R3
artifact/loader boundary, not permission to cross it. Only this record,
[implementation plan](IMPLEMENTATION_PLAN.md) and the preceding
[static acquisition proof's](SOURCE_POLICY_MINIMUM_IMPORT_CLASSIFICATION_OWNED_PROOF.md)
forward pointer change. Ordinary repository QA, commit/push and exact-head CI
are authorized; no manual/native workflow is dispatched.

Choose a **finite application closure plus an explicit trusted Windows platform
boundary**, followed by a separately approved, connection-free legacy runtime
preflight. This is not a custom Windows loader, system-wide DLL inventory or
proof of every transitive Windows implementation dependency. No compiler/SDK/
Build Tools, new account, protected-staging scheme, privilege change, injection,
service or dependency installation. Missing evidence is refusal, not a fallback.

This slice performs no downloads, artifact extraction, candidate-runtime/module
loading, installed dependency/profile inspection, process/module experiment,
authentication, SQL, WordPress/core bootstrap, fixtures or site operations.
Site Operations was consulted; no site target is selected or touched, so target
confirmation/Novamira operations are not invoked. No production wiring, public
factory, native worker/controller, schema8/version0.1.77, support declarations,
source/artifact pins, TLS rules, ADBC1/ADBC2 or native deadlines change.
Uploader/toolkit remain read-only. No release/package/tag/deploy or checklist
readiness claim; PHP7.4 remains legacy compatibility evidence, not a deployment
recommendation.

## Source boundary and established versus missing evidence

The [original artifact/runner design](SOURCE_POLICY_MINIMUM_ARTIFACT_RUNNER_DESIGN.md)
separates acquisition, runtime preflight and database/native qualification.
The preceding approved proof completed only acquisition/static/readback:
3,523 files/144,216,233 bytes, complete pinned core tree/12 source SHA256 pins,
three selected PE roles, direct-import classification and exact cleanup. Its
artifacts were deleted; its hashes/receipt cannot authorize a later launch.
No acquisition or physical proof is repeated here.

Inspected current consumers and gaps:

| Existing source | Retain / integration limit |
| --- | --- |
| `scripts/manual/source-policy-minimum-{artifacts,archive,core-modes,tree,pe,static,owned,acquire}.php` | Preserve fixed publisher/core identity, complete inventories, modes/blobs/CRC/tree, exclusive readback and tracked cleanup. Acquisition currently ends in cleanup and grants no launch handle; do not turn its result array into a retained-artifact capability. |
| `source-policy-minimum-runner.php` and `SourcePolicyMinimumRunnerTest.php` | `ADBM0` is ordered synthetic no-extraction/no-execution DATA. Do not repurpose its completion as a real preflight receipt. |
| `source-policy-supported-profile.php`, `source-policy-supported-core-pins.php` | Keep the exact6.0/7.4.33/OFF0 candidate and source pins; neither observes a runtime. Core source identity is not core execution. |
| `source-policy-cleanup-process.ps1` | Existing bounded pipes/process-handle patterns are useful, but its launcher inherits environment and its stderr mapping is database-specific. It is not a ready minimum-preflight launcher; leave it and its historical images unchanged. |
| `source-policy-ambient-proof-controller.ps1`, `source-policy-proof-lock.php` | Existing LocalWP8.5.12 credentials/SQL/module composition is out of scope and must not be reused as a7.4 preflight. No service discovery or database child here. |
| `includes/class-source-policy-native-profile.php` | Existing provider/session/schema admission remains disconnected; no preflight record becomes native provider facts or a write-capable handle. |

Current exact application closure is `php.exe`, `php7.dll`,
`ext/php_mysqli.dll`, authenticated by the unchanged publisher ZIP. The observed
42 direct edges (15/21/6 per role) reference one bundled name, `php7.dll`, and22
external names recorded in the owning proof. The existing name policy remains
classification only: all externals are presence-and-transitive-closure-unverified,
dependency_closure_qualified=false and load_authorized=false. Do not flip those
historical/helper flags merely because a later scoped preflight succeeds.

## Dependency and search-path admission before any launch

Separate three classes, with no arbitrary name/path overrides:

1. **Publisher-owned application files:** freshly validate both artifacts and
   complete core/source identity, then derive only the three fixed load roles
   above. Freeze their verified bytes, paths and fixed worker/controller source.
   Stage a minimal launch child containing only those three load roles plus
   separately hashed fixed worker DATA/source, and an empty owned working
   directory. Reject extra `.dll`, `.exe`, `.local`, INI, external manifest or
   unexpected entry in these launch/search directories. Do not place optional
   archive extensions/libraries in DLL search locations. Full archive validation
   still precedes subset staging; subset selection is not a replacement ZIP pin.
2. **Windows/API-set dependencies:** trust only a separately reviewed exact x64
   Windows platform profile, not the caller's `SystemRoot`, PATH or arbitrary
   directories. Windows/API-set/KnownDLL/SxS handling belongs to that platform
   trust boundary. API-set contract names are not assumed to be literal disk
   files. System implementation internals are not recursively reimplemented by
   this plugin. Actual OS-root/build/architecture, directory identities and the
   source of module observations must be established in the later read-only
   profile audit, not populated from this document or the22 names alone.
3. **External VC runtime:** `vcruntime140.dll` needs an explicit installed-runtime
   provenance/path/architecture decision, separate from PHP's ZIP provenance.
   Accept only an existing Microsoft runtime under the reviewed platform profile;
   no copy from current PHP/LocalWP/PATH, download, install or version guessing.
   A computed SHA256/version/path is correlation, not publisher provenance.
   The later audit must identify supported non-loading provenance evidence
   (including catalog versus embedded-signature limitations) and its bounded,
   no-network verification method. If unavailable/ambiguous, stop before launch
   for that exact trust decision; do not add a compiler/helper or silently accept.

Microsoft's [DLL search-order documentation](https://learn.microsoft.com/en-us/windows/win32/dlls/dynamic-link-library-search-order)
describes redirection, API sets, SxS, loaded modules and KnownDLLs; dependent
libraries can still be resolved by name when a top-level path is absolute.
Accordingly the design does not treat three file hashes as loader closure.
[SetDefaultDllDirectories](https://learn.microsoft.com/en-us/windows/win32/api/libloaderapi/nf-libloaderapi-setdefaultdlldirectories)
is process-local; setting it in a parent, or after PHP startup, does not prove
the child's initial imports were safely resolved. No such API change is proposed.

Before launch, require exact profile provenance for every non-platform dependency,
all relevant search locations accounted for and no untrusted/user search entry.
The platform boundary explicitly trusts Windows servicing/loader behavior; it
does not defend against compromised Windows, an administrator, or a racing
same-user attacker. Fresh path/identity/hash checks are detection in the owned
fixture model, not an immutable-filesystem/sandbox proof. Unknown imports,
redirection or unverifiable dependency search stop; never attempt a load just
to discover whether an untrusted DLL works.

After a later approved launch, observe the exact owned child's loaded modules
while it is held. [.NET Process.Modules](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.modules)
is a possible existing-tool observation surface, not proof of transient startup
or all future/dynamic loads. Bind process object/PID/start identity and executable to the same child;
require the three expected application roles appropriate to its stage, exact
application hashes/paths, and all other observed modules within the reviewed
Windows/VC trust profile. Reject application modules from any other directory,
unknown non-platform dependencies, basename ambiguity, inaccessible/partial
observations or changed files. Do not label a path prefix or a signature string
alone as provenance. No inspection or termination of unrelated processes.

## Fixed connection-free preflight, not implemented by this design

The later adapter must use its own private fixed composition, not change the
existing acquisition CLI's cleanup behavior or broaden current native workers.
Only one controller-owned freshly revalidated child can supply artifact paths;
no public executable, worker, extension, DLL, command, SQL or callback argument.
No durable launch grant is serialized or returned. The historical source hashes
are comparison references; each later approved acquisition must revalidate them.

Use existing trusted x64 tooling, no shell or profile startup, hidden window,
typed argument list, exact executable/worker paths and empty owned CWD. Create a
fresh allowlisted child environment, never inherit credentials/PHPRC/
PHP_INI_SCAN_DIR/preload/agent variables or user PATH. Required Windows system
values must derive from the admitted host profile, not untrusted environment;
PATH is at most its exact reviewed system directory, with no empty/relative
component. No global environment, registry, DLL-search or INI changes. Do not
copy the old launcher's inherited-environment behavior under a new function name.

Two ordered fixed stages share one25s monotonic runtime-preflight deadline:

- **Base:** run only the verified CLI/core runtime with `-n` and the fixed small
  worker. Check Windows, CLI, PHP_VERSION exactly7.4.33, PHP_INT_SIZE8, PHP_ZTS
  false, no loaded/scanned INI, no prepend/append source, no optional/Zend
  extensions and exact worker/fixed-pure-producer include closure (at most two
  frozen source files, no autoload). Require mysqli absent. The compiled-in base
  module set must be fixed by the source/profile audit, not guessed from another
  installed PHP or accepted merely because the requested optional module is absent.
- **Mysqli:** fresh child, same profile plus only the absolute owned
  `ext/php_mysqli.dll` and fixed extension directory. Check internal mysqli
  symbols/class origins, raw client info exactly `mysqlnd 7.4.33`, and
  `mysqli_driver::report_mode` exactly integer0 before and after observations.
  Never call mysqli_report or write the property to make it pass. Reject an
  unexpected module set; raw mysqlnd text is compared exactly before deriving
  canonical version DATA. No JSON/mbstring/OpenSSL/FFI extension loading fallback.

The [PHP CLI options](https://www.php.net/manual/en/features.commandline.options.php)
define `-n`/`-d`; neither is an OS loader sandbox. Expected source semantics were
read in memory from PHP7.4.33's
[mysqli globals](https://github.com/php/php-src/blob/php-7.4.33/ext/mysqli/mysqli.c),
[driver properties](https://github.com/php/php-src/blob/php-7.4.33/ext/mysqli/mysqli_driver.c)
and [mysqlnd version](https://github.com/php/php-src/blob/php-7.4.33/ext/mysqlnd/mysqlnd.h):
the report-mode default is0 and raw client-version prefix is `mysqlnd ` plus
PHP_VERSION. This is expected source behavior, **not observed candidate behavior**.

A read-only mysqli_driver object is not a connection. Forbid `new mysqli`,
mysqli_init, connect/real_connect/persistent-connect, query, native owner/factory
construction, credential inputs, host/port/service probes and all SQL. No core
library include or WordPress bootstrap; core6.0 comes only from the fresh pinned
source receipt, never from invented runtime output. File writes are confined to
owned provisioning/controller fixtures; worker observations do not write state.
This is audited finite code, not proof of OS-enforced network isolation.

## Receipt, ownership, budgets and failure rules

Define a separate versioned **ADBMP1** private primitive transcript in the next
foundation; do not alter ADBM0 or ADBC1/2. Bind a controller-generated run32hex,
fresh source/artifact/profile digest64hex, exact child PID/start identity, fixed
stage and monotonically ordered observations. No caller-supplied readiness or
native handles. Planned per-stage order: startup identity, profile facts, held,
controller module observation, exact private release, clean exit and post-exit
file/process cleanup. Child claims and independent controller observations must
remain distinguishable. Correlation is not authenticity or an execution permit.

Planned exact LF envelope:
`ADBMP1|<run32>|<image64>|<base-or-mysqli>|<pid-decimal>|<sequence>|<kind>|<fixed-fields>\n`.
The next foundation must define exact field arity/types for each finite kind;
no serialized objects, JSON dependency or arbitrary payload is admitted.
Worker kinds are start/profile/held/released; controller evidence kinds are
modules/exit/cleanup, kept on separate trusted inputs. An exit claim arriving on
the worker pipe cannot satisfy controller exit evidence. The sole stdin response
is a fixed correlated `ADBMP1-ACK` frame for the current held stage; no command
or source input. Canonical integers reject coercion/leading signs/extra fields;
the controller's real monotonic deadline governs regardless of claimed child time.

Proposed ceilings (not measured runtime facts): max512 bytes/LF frame,16KiB total
stdout,4KiB stderr discard/count ceiling,64 fixed frames,128 observed modules,
32MiB per observed file/256MiB aggregate read evidence. Use at most two concurrent
owned processes (worker plus trusted observer), two worker stages and at most
one observer per stage. Any potentially blocking inspection must itself have
bounded execution/owned termination; elapsed-time checks around an unbounded
synchronous call do not establish a deadline. Unsupported observer capability
refuses, without new dependencies/elevation or budget expansion.

Any stderr, malformed/extra/reordered/stale/partial frame, identity mismatch,
timeout, early exit, mode/INI/module/provenance mismatch, file change or cleanup
uncertainty permanently refuses the run. Emit only safe stage/reason/counts;
never raw stderr, environment, paths/SQL/credentials. EOF is not a terminal; a
held frame alone is not success. Require exact clean exit, drained pipes,
independent post-exit absence and unchanged source/artifact observations before
marking the scoped preflight complete. Do not upgrade historical unverified flags.

Keep acquisition's600s lifetime and archive/path/byte/count limits separate from
runtime's25s total; neither enlarges native initializer20s/16credits, connect2s/
read3s/statement3s/lock1s/body<20s/process25s. Exact Process-object cleanup is
always attempted in finally after a failure/expiry: close private input, bounded
250ms exit wait, kill only that still-owned process if needed, bounded1000ms reap.
First failure and cleanup uncertainty are reported separately; failed cleanup
prevents success and may retain only the exact manifest for recovery. Never
delete while owned processes remain live or recurse over a temp/site root.

The future composed owner must retain its tracked filesystem manifest through
both stages, stop/reap its workers/observers, verify unchanged inputs, then remove
all exact owned payloads/parents and independently confirm absence. No reuse of
the deleted prior proof child. Scope remains ordinary same-user fixture trust,
not a security sandbox or adversarial Windows reparse/transaction proof.

## Ordered validation and approval gates

1. **Next repository-only foundation:** socket-free dependency/launch-admission
   DATA and `ADBMP1` producer/validator contracts,
   synthetic path/module/profile/clock/stream fixtures only. Actual IO, networking,
   process launch, installed-profile discovery, runtime loading and wiring remain
   disabled. Positive DATA paths must pass but never mint real load authority.
   Proposed cohesive sources: `scripts/manual/source-policy-minimum-launch-data.php`
   for fixed dependency/path/profile consistency, and
   `scripts/manual/source-policy-minimum-preflight-transcript.php` for the versioned
   producer/validator state machine. These paths are proposals, not implemented
   files. No real launcher/owner is hidden in a DATA helper; all complete snapshots
   keep execution/native_qualified=false. A sealed physical adapter remains later.
2. **Separate read-only host dependency/profile audit:** resolve exact trusted
   Windows/VC runtime/search/provenance and existing observer capabilities without
   loading artifacts, installation or site/database inspection. Record unknowns;
   no source pin, OS trust designation or dependency fallback is silently invented.
3. **Separate owned process/stdio/cleanup prerequisite:** existing trusted benign
   fixed child only, proving the new environment/argument/timeout/observation
   composition before downloaded binaries are allowed. Synthetic clocks do not
   prove physical process termination or Windows stream behavior.
4. **Separate artifact/runtime-preflight approval:** fresh acquisition/static
   prerequisites and one ordered base/mysqli held/module/exit/cleanup proof,
   no authentication, connection handle, SQL, core bootstrap or installation.
   A scoped passing receipt still cannot admit production/provider/native work.
5. **Separate minimum database/native proof:** existing Site Operations/Novamira,
   exact target/owned fixture confirmation and fresh physical prerequisites. No
   use of this design to bypass that gate or repair application state.

Required synthetic cases: real positive dependency/launch DATA; wrong/missing/
extra role, DLL lookalike, unreviewed API contract, unsafe path/case/link alias,
PATH/INI injection, provenance asserted only by hash, fake internal symbol,
PHP/mysqlnd/version/bitness/ZTS/mode mismatch; unknown/partial/changed module
observations; wrong run/image/PID/stage, replay/CRLF/extra/overlong/partial output,
time reversal/expiry, stderr, early exit and cleanup uncertainty. Kill removal of
each required gate with assertion controls; an always-unavailable owner must not
pass a suite of expected refusals. In-memory controls and stubbed platform facts
never count as physical loading, signature/catalog, process or database evidence.

## Review, verification and remaining limits

Source-scoped design review covers provenance versus correlation, startup versus
post-startup search, platform delegation, fixed private composition, changed
consumer ownership, backward transcript compatibility, legacy API expectations,
all refusal/cleanup paths, bounded costs and non-vacuous tests. No architectural
rewrite or generic executable/provider registry is introduced. Existing tested
runtime and native acceptance remain unchanged; only documentation rollback is
relevant here. No UI/UX or whole-plugin release/Database Review credit.

Executed repository QA on existing tooling PHP8.5.1 (no candidate runtime loaded):

- `php vendor/bin/phpunit --do-not-cache-result --filter SourcePolicyMinimum`:
  107 tests/2,027 assertions, no failures/skips. Existing synthetic admission,
  parser/order/refusal and assertion-killed controls ran; no new implementation
  or test is claimed by a design-only document.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,150 tests/
  11,235 assertions, no failures; only the two existing missing-Sodium invalid-key
  and signature-roundtrip skips. Routine isolated QA uses existing trusted tooling,
  not the proposed worker/controller, acquired artifacts or site/database work.
- PHPCS240 files, admin23/23/zero skips, structure149 runtime/229 support/
  216 test files/213 class names, asset build and strict Composer validation pass.
  Composer/npm moderate audits pass, zero known advisories/vulnerabilities;
  no dependency install/update or generated tracked delta.
- 137 owning local links, complete documentation diff/source-scoped design review
  and whitespace checks pass. All tracked non-documentation files remain identical
  to9ea299d. Uploader/toolkit are clean and untouched at3dfcb15/c067eb6.

Design publication `5c31c430373413b9b0e9b263f616fd36bfa6185c` is pushed;
ordinary [CI40916766096](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40916766096)
succeeded for that exact head. Actual PHP7.4 log:2,150 tests/11,237 assertions;
PHP8.3:2,150/11,242, both without failures/skips. Admin23/23 each, zero failures/
skips; manifest/audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. This evidence-only closeout receives its own pushed exact-head
ordinary CI and clean/remote verification at handoff, without dispatching a
manual/native workflow, redoing acquisition or repeating any physical proof.
No installed profile, downloaded code, subprocess loader, signature/catalog or
native/database evidence is produced by this design.

Implementation-plan progress: dependency/platform trust, fixed preflight ownership,
versioned evidence and ordered proof gates are specified; runtime/native lane is
still closed. Recommended next gated slice: **Local-only Socket-Free Dependency/
Launch Admission and Runtime-Preflight Transcript Safety Foundation**, synthetic
DATA/virtual clocks/isolated tests only; no installed-profile inspection, filesystem/
network/process IO, downloaded runtime/module loading or production wiring.
