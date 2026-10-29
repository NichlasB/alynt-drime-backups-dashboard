# Observer Phase/Reason Diagnostics and One Fresh Owned Proof

Forward decision: the approved [module-ceiling feasibility design](SOURCE_POLICY_TOOL_MODULE_CEILING_FEASIBILITY_DESIGN.md)
retains both 128-module limits and specifies a separately gated non-authorizing
exact-count calibration. It does not rerun, complete or alter this failed proof.

## Authority, baseline and disposition — 2026-10-29

Explicitly approved harness-only diagnostics, regression validation and **one**
fresh owned-tool attempt after the [initial failed proof](SOURCE_POLICY_TOOL_OBSERVER_OWNED_PROOF.md).
Clean/synced dashboard restore baseline `f7872918649d23938b31490725ff3d24cf26365d`.
The existing controlled R3 process/fixture scope is unchanged: bundled PowerShell,
fixed sources and exact owned filesystem/stdio/process cleanup only. No candidate
PHP/DLL, download/installation, WordPress/bootstrap, authentication/database/SQL,
site/live/production operation, actual host-trust designation, permission/schedule
change, package/release/tag/deploy or production wiring. Site Operations was
consulted; no site target or Novamira operation applies. Uploader/toolkit remain
read-only. Ordinary trusted repository QA is separate from the physical proof.

**Diagnostics implemented; the fresh physical attempt failed closed.** A fully
validated child diagnostic identified the first snapshot's module-count guard:

```text
tool-proof failure stage:positive-observer reason:tool-stderr-refused checks:13
tool-proof observer_phase:snapshot-one observer_reason:module-count rows:129 row:0 mask:0
tool-proof failure_cleanup_confirmed:1 fixtures_absent:1 owned_absence_confirmed:1
```

`rows:129` is the fixed overflow sentinel: the collection exceeded 128, **not**
an exact inventory of 129 modules. `row:0`/`mask:0` place the refusal before
per-module row traversal. The target identity and first module-collection guard
were reached; a complete accepted snapshot/catalog, positive observer group and
physical feasibility are still not proved. Later timeout/cancellation groups
were not run. The initial attempt remains historically unclassified; this new
attempt does not retroactively authenticate its cause.

No second fresh attempt, larger ceiling, filtered/truncated module set, weaker
guard or successful-proof claim follows. The current tool/ceiling combination
requires a design decision, not another automatic retry.

## Finite diagnostics without a new acceptance path

Only observer diagnostic instrumentation, its private transport/error display,
actual pure-parser tests, source-backed regressions and owning records change.
Worker behavior, launch/environment/identity/ACK checks, positive zero-stderr
acceptance, time/resource bounds and cleanup remain unchanged.

The child emits the exact ASCII/LF error envelope only after it has established
its own source/run/start identity and exact owned target parameters:

```text
ADBTD1|run32|observer|pid|tick|source64|target-pid|target-tick|phase|reason|rows|index|mask\n
```

Scope/self failure before that binding retains the prior fixed refusal, not a
made-up authenticated diagnostic. Phases and reasons are finite source-owned
enums. The decoder requires the original observer role, run, PID/start, source
hash and target PID/start, exact 13-field shape, canonical bounded integers,
allowed phase/reason combinations and consistent guard-mask bits. Rows cap at
129, row index at 128, mask at 15. The mask identifies failed name/path/size/
duplicate checks without printing their values. Metadata checks retain their
original short-circuit ordering; instrumentation does not access later metadata
checks after an earlier guard would have refused.

The transport counts/discards stderr as before, with a private ASCII prefix
bounded to 512 bytes only for validation. A safe fact can be retained only at
stderr **EOF**, when the entire stream is exactly one bounded correlated record.
No partial-prefix credit: extra bytes/lines, unknown fields/enums, malformed or
non-ASCII/control/CRLF input, wrong identity, overflow, truncation or replay leave
no diagnostic fact. The raw prefix is cleared at EOF or invalidation; only finite
phase/reason/count/mask fields can reach the public failure summary. No exception
message/type, path, module name, raw stream or environment is printed.

Any stderr still refuses `Read-ToolLine`; clean completion still requires zero
stderr. Failure cleanup may drain/discard it but cannot turn an error fact into
positive completion. Diagnostics are reported only in the controller's failure
branch after bounded owned reconciliation; they grant no capability or authority.

Unchanged physical ceilings: shared 25,000 ms runtime/cleanup, work below 20,000 ms,
observer below 2,000 ms, 128 modules, 32 MiB mapped-image size per module / 256 MiB
aggregate, private catalog 128 KiB, stdout 16 KiB, frame 512 bytes, stderr overflow
sentinel 4097. Mapped-image sizes remain distinct from file sizes/hashes. No
candidate module/profile limit or ADBM0/ADBMP1/ADBC1/2 record is changed.

## Non-vacuous parser/source validation before the attempt

The new positive decoder test was first red because the diagnostic function did
not exist. The final actual PowerShell parser suite passed **417 assertions and
six assertion-killed in-memory controls**, with zero physical processes/fixtures.
It covers positive decoded facts, every correlation field, finite phase/reason/
mask/count domains, complete versus fragmented/partial input, EOF, extra output,
overflow, non-ASCII, replay and immutable repository source bytes.

Controls remove prefix correlation, widen phase membership, remove row/type/mask
guards, and replace the decoder with always-unavailable. The real contract must
fail for each, including the always-unavailable positive case. Review found that
an unknown-phase/module-reason negative was redundantly rejected by a separate
compatibility guard; an unknown-phase/API-reason case was added to independently
kill phase widening. No guard or assertion was weakened. Original function
definitions are restored after each in-memory control; source SHA256 is checked.

All five PowerShell sources parsed successfully. Four PHP source-backed tests
passed 144 assertions for fixed scope, unchanged limits/cleanup, module-API
isolation, diagnostic correlation/bounds/EOF and unchanged stderr refusal.
Those are static regressions on Linux CI, not Windows process-proof execution.

## Fresh physical attempt and cleanup

Exactly one new invocation of the fixed owned-proof controller ran after the
above prerequisites, using a fresh GUID root and source hashes. It stopped on
the positive observer's first collection count. Only the genuine held worker
and observer were started; none of the subsequent groups was attempted.

Both original owned process identities and pipes were reconciled, with only
exact owned-object termination where needed. The validated absolute GUID root,
allowlisted files and empty non-reparse scratch directories were removed
nonrecursively. Follow-up read-only inventory confirmed zero owned fixture
directories; no retained cleanup target remains. Configured bundled image SHA256
was checked read-only afterward and remained
`362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139`.
No canary/source final-readback or full observer success is credited: that stage
was not reached. No all-host/application/database checksum claim is invented.

## Repository validation, publication and exact next boundary

Fresh ordinary repository QA on existing trusted PHP 8.5.1:

- Related family/source tests: 277 tests / 11,640 assertions, no failures/skips,
  including the unchanged thirteen existing in-memory host controls.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,320 tests /
  20,848 assertions, no failures; two existing Sodium-unavailable signing tests
  skipped. No physical proof is replayed by these isolated checks.
- PHPCS 243 files; admin 23/23, no failures/skips; structure 149 runtime / 233
  PHP support / 223 test files / 220 class names and asset build passed.
- Strict Composer validation, Composer audit and npm moderate audit passed,
  zero known advisories/vulnerabilities; no dependency install/update.
- 140 owning local documentation targets, whitespace and final source/caller/
  scope/security/resource/error/compatibility/test-integrity review passed.
  Changed executable code is limited to the three harness diagnostic sources;
  worker, old native/runtime helpers, limits and cleanup are unchanged. No
  generated tracked delta. Uploader/toolkit stay clean/read-only at
  3dfcb15/c067eb6. No new skip, assertion weakening or whole-plugin review refresh.

Diagnostic publication `bc760e878d07ff997ea8da74b7d5b6e10f35aa3c` is pushed;
[CI 40953758495](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40953758495)
succeeded for that exact head. Actual PHP 7.4 log: 2,320 tests / 20,850 assertions;
PHP 8.3: 2,320 / 20,855, both without failures/skips. Admin 23/23 each, zero
failures/skips; manifest/audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. Setup Node emitted tool-level `punycode`/`url.parse()` deprecation
warnings; no acceptance change suppresses them. This evidence-only closeout
receives its own pushed exact-head ordinary CI and clean/remote check at handoff.
No owned-process proof is replayed for publication. Green CI does not close the
physical module-count refusal or authorize a ceiling change.
No runtime/bootstrap/native worker, existing pure host-admission helper,
source/artifact pin, schema/version, manifest/lock, workflow or production wiring
is changed. Ordinary green CI does not close the failed physical verification.
No whole-plugin readiness, Database Review, UI/UX or release workflow is claimed.

Implementation-plan progress: the diagnostic boundary is now independently
tested and the current tool's module-count incompatibility is identified; the
physical observer prerequisite remains blocked. Previous safe-local progress
stays 1/8, plus separately approved proof/diagnostic work, not fabricated product
completion credit.

Recommended next gated slice: **Design-only Existing-Tool Module-Ceiling and
Observer Feasibility Decision**. Review whether a separately bounded existing-
PowerShell observation profile is proportionate before any further experiment;
keep the candidate's 128-module boundary and all current executable guards
unchanged. Do not select a new numeric ceiling from the overflow sentinel,
silently filter rows, add another runtime/compiler, or retry. This design must
explicitly resolve the tool-only boundary and its evidence limits without
installation, runtime loading, host/site/database operations or production wiring.
