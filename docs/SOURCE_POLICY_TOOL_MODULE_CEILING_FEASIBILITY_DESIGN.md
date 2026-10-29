# Existing-Tool Module-Ceiling and Observer Feasibility Decision

## Authority, baseline and decision — 2026-10-29

Approved **design only**, at clean/synced dashboard baseline
`2cb1b22d210a5c09e2e11c22c2508dcbcff8b36d`. This record supersedes the next-step
decision in the [diagnostic proof](SOURCE_POLICY_TOOL_OBSERVER_DIAGNOSTICS_OWNED_PROOF.md),
not its historical observations. Uploader and toolkit remain read-only.

**Do not increase either module ceiling.** Keep the candidate's 128-module limit,
the existing PowerShell full-snapshot limit of 128, all executable guards and
production wiring unchanged. The next useful prerequisite is a separately
approved, non-authorizing **count-only existing-tool calibration**, not another
full proof or a guessed ceiling of 256/512/1024. No implementation, physical
calibration, host/profile reinspection or native experiment is authorized here.

No candidate PHP/DLL loading, installation/download, actual host-trust decision,
permissions, WordPress/bootstrap, authentication, SQL/database, site operation,
schedule change, package/release/tag/deploy, grants, activation or rollback.
Ordinary trusted repository QA is distinct from candidate/native execution.

## Evidence and proportionate boundary

The one fresh attempt reached `snapshot-one/module-count`, with `rows:129`,
`row:0`, `mask:0`, and confirmed owned cleanup. The first value is an overflow
sentinel: actual module count is **greater than 128**, not proven to be 129.
There is no accepted full catalog, positive group, timeout/cancellation proof,
exact module count or measured cost of admitting a larger inventory. Final
canary/source readbacks were not reached; no application/database checksum proof
follows. Do not retroactively classify the initial pre-diagnostic failure.

The [Process.Modules API](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.modules?view=net-10.0)
returns a module collection and can fail or observe an incomplete startup state.
The inherited [collection Count](https://learn.microsoft.com/en-us/dotnet/api/system.collections.readonlycollectionbase.count?view=net-10.0)
is an Int32 scalar; its O(1) retrieval does **not** make acquisition of
`Process.Modules` O(1). The getter may enumerate/materialize native collection
data before Count is read. Neither API establishes bounded native allocation,
getter latency, hard cancellation or an OS sandbox. Isolation and owned-process
termination remain necessary; a deadline is a refusal/cleanup policy, not an
in-process interrupt guarantee.

Count-only calibration is proportionate because it avoids per-row traversal,
path/name inspection, catalog allocation and mapped-size aggregation while
answering the missing numerical prerequisite. It does not establish feasibility
of those omitted operations, complete module identity, signatures, file hashes,
DLL trust or a safe future module budget. A successful count still requires a
subsequent explicit measured-budget/proportionality decision before any larger
tool-only snapshot. Candidate admission remains independent and unchanged.

| Evidence lane | Current boundary | Count-only effect |
| --- | --- | --- |
| Candidate DATA/admission and ADBMP1 transcript | At most 128 modules; non-authorizing | None |
| Existing-tool full snapshot / ADBT1 | Complete, two-snapshot inventory; at most 128 | None; refusal remains refusal |
| ADBTD1 error fact | Rows 129 is overflow; row index at most 128 | Never reinterpret as an exact count |
| Proposed ADBTC1 diagnostic receipt | Exact bounded scalar only | No snapshot, observer or execution admission |

## Fixed future calibration contract — reserved, not implemented

Use a distinct fixed controller entry, such as `-RunOwnedCountCalibration`,
mutually exclusive with the full proof. No arbitrary image, command, PID,
module cap or executable mode is accepted. No automatic count fallback on
full-proof refusal and no repeat attempt without approval. Reuse only the
minimal existing owned private transport/fixture primitives; do not introduce
a generic launcher, new runtime/compiler or parallel admission framework.

One exact held existing-tool worker and one isolated observer, at most two
children, remain bound to the original controller-owned Process objects,
handles, PID/start times, run, fixed image fingerprint and frozen script sources.
Observer PID lookup is observation only: verify target start/image/liveness
before and after acquiring the collection; never use rediscovered identities
as kill authority or inspect unrelated processes. No candidate runtime is loaded.

Acquire one collection and read its Count, without a module-row loop, catalog,
path/name/hash scan, filtered/truncated enumeration or per-count allocation.
Reserve a distinct exact ten-field ASCII/LF frame, at most 512 bytes:

```text
ADBTC1|run32|count-only|observer-pid|observer-start|source64|worker-pid|worker-start|count|not-admission\n
```

Bind all identities to original owned expectations and source-image checks;
the private receipt alone is not proof against a hostile host. Count uses a
canonical positive decimal Int32, range 2..2147483647, at most ten digits, no
leading zeros, sign, whitespace, overflow or saturation. This range bounds
scalar representation only: **it is not a module-processing budget**. In
particular, accepting diagnostic 129 or Int32-max DATA must never authorize
enumerating that many rows. Reject missing/extra fields, wrong purpose/tag,
correlation, record order, duplicate records, CRLF, trailing bytes or partial EOF.
No raw paths/module names go into the receipt or retained evidence.

Zero-exit, zero-stderr, bounded drains and exact cleanup remain required; a
receipt is provisional until those complete. Decode separately from ADBT1,
ADBTD1 and ADBMP1. Never widen their parsers or manufacture an all-stages terminal
receipt. Successful calibration may report `count_only_observed` and its owned
transport/cleanup facts only. `full_snapshot`, `observer_qualified`,
`load_authorized` and `runtime/native_qualified` remain false. No allowlist,
host-admission claim, historical receipt or source pin is refreshed from it.

## Resources, failure and cleanup

Preserve all current full-proof limits: 128 rows, 32 MiB mapped size per row,
256 MiB aggregate, 128 KiB catalog, 16 KiB stdout, 512-byte frame, 4097-byte
stderr overflow sentinel, two-second observer, 20-second work and 25-second
global envelope. Count-only creates no catalog and retains existing stream/time
bounds and cleanup reserve; no extra retries, expanded deadlines or buffers.
Mapped-image metadata must not be called verified DLL file bytes or signatures.

Future ordered calibration: establish fresh fixed source/ownership prerequisites;
hold one owned worker; run one isolated count observer; validate receipt and
clean observer exit/drain; acknowledge/release the original worker; verify its
exit/drain; check source/canary readbacks; perform exact allowlisted nonrecursive
fixture cleanup and reconcile owned absence. Cleanup uses original objects,
not process-name/tree kills. Existing no-reparse/path/foreign-file safeguards
remain. Do not claim all-host absence or unexecuted timeout/cancellation groups.

Unavailable/stale collection, count 0/1, malformed scalar, identity change,
timeout, stderr, unexpected IO or uncertain cleanup means refusal, not a larger
budget. Record only finite non-secret stage/reason facts. No fallback, truncation,
retry, guard bypass or unresolved process/fixture deletion. If one bounded
calibration cannot qualify within the existing envelope, stop and reassess this
Windows route rather than grow platform machinery. Even if it passes, pause for
an explicit exact-count/cost and proportionality decision; no automatic ratchet.

## Required later tests and proof limits

The future implementation must exercise an actual positive exact-count decoder,
including 128/129/Int32-max as **non-authorizing diagnostic DATA**, so an owner
that always returns unavailable cannot pass. Cover wrong mode/identity/source,
range/type/overflow/leading-zero errors, stale target, EOF/order/extra records,
nonzero exit/stderr, deadline, readback and cleanup failures. Assert separation
from all existing snapshot/admission parsers and false authorization flags.
Use semantic mutation controls that remove required correlation/purpose/range
guards and confirm assertion failures; preserve original sources after controls.
Source-backed checks must forbid per-count iteration/allocation and protect all
existing module caps, identity/ownership checks, deadlines and production wiring.

Synthetic parser tests prove grammar/guard behavior only. The separately approved
owned calibration must obtain the exact count from the real getter, correlate
it with the held target, reconcile cleanup, and report actual timing/limits.
It still does not qualify a full snapshot, hostile-host containment, candidate
runtime, native database connection, physical transactions or release readiness.
No whole-plugin review row or batch count is advanced by calling this design a proof.

## Validation and publication

Executed repository-only QA with trusted `C:\php\php.exe` (8.5.1), not the
candidate runtime:

- `php vendor/bin/phpunit --do-not-cache-result --filter
  'SourcePolicyMinimum|SourcePolicyToolObserver'`: 277 tests / 11,640 assertions,
  no failures/skips.
- `tests/support/source-policy-tool-observer-unit.ps1`: 417 checks and six
  assertion-killed in-memory controls, zero proof processes/fixtures.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,320 tests /
  20,848 assertions, no failures; two existing Sodium-unavailable signing skips.
- `npm run lint`: PHPCS 243 files; `npm run test:admin`: 23/23, no failures/skips;
  `npm run check:structure`: 149 runtime / 233 PHP support / 223 test files /
  220 class names; `npm run build`: passed, no tracked generated delta.
- Strict Composer validation, Composer audit and npm moderate audit passed,
  zero known advisories/vulnerabilities; no install or dependency changes.
- 141 local owning-document targets, whitespace and final scope/resource/error/
  compatibility/test-integrity review passed. The four tool harness scripts,
  three minimum admission/launch/transcript helpers and all tests remain byte-
  unchanged against baseline; only this design, plan and predecessor pointer
  change. Uploader/toolkit remain clean/read-only at 3dfcb15/c067eb6.

Exact-head publication verification follows the design commit. No physical
attempt, current host/profile reinspection, site or native operation runs here.
No historical failure is relabeled, whole-plugin review refreshed, or release
readiness claimed by these isolated checks.

Implementation-plan progress: the tool/candidate evidence boundary and the
missing exact-count prerequisite are now explicit; physical observer
qualification remains blocked. Previous cumulative safe-local progress stays
1/8 plus separately approved proof/diagnostic work; this is not product completion.

Recommended next gated slice: **Local-only Count-Only Existing-Tool Module
Calibration and Owned Cleanup Proof**, one separately approved bounded attempt
using bundled PowerShell and owned fixtures/processes only, with non-authorizing
receipt tests first. No new numeric ceiling or candidate/production execution.
