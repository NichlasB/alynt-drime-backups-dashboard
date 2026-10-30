# Measured Tool-Only Module Budget and Full-Snapshot Feasibility Decision

## Authority and disposition — 2026-10-30

Approved **design only**, at clean/synced dashboard restore baseline
`28e5cc2125ecb480fb9948d0695ca5292e5d47ca`. The owning predecessor is the
[count-only calibration](SOURCE_POLICY_TOOL_COUNT_CALIBRATION_OWNED_PROOF.md):
one held worker yielded 135 modules, 28 checks and 916 ms through owned closure/
cleanup. That is historical exact-count evidence, not a full catalog or timing
model for per-row reads. No source, tests, executable limit or proof is changed
or rerun here. Uploader/toolkit stay read-only. This is repository documentation,
not a new site operation, host-profile audit or runtime/fixture experiment.

**Select a fixed 135-row tool-only evaluation profile for a separately approved
full-snapshot attempt.** This is the smallest ceiling admitting the observed
count: seven rows / 5.46875% above 128, with zero speculative headroom. It is a
finite experimental refusal boundary, not a measured safe performance envelope,
an expected module allowlist, authorization to run, or a new production contract.
Count variation above 135 must refuse; do not select 136/256/512 after failure.

The candidate remains at **128**. All executable limits, including the current
tool cap of 128, stay unchanged now. All three existing minimum host-admission,
launch-DATA and ADBMP1 transcript helpers remain unchanged, including their
current tool-DATA limit of 128. No metadata receipt is fed into those helpers.
This decision supersedes only the proposed tool-row budget in the earlier
[observer design](SOURCE_POLICY_MINIMUM_HOST_TRUST_OBSERVER_DESIGN.md) for the
future versioned experiment; it does not relabel historical refusals or qualify
their old source/profile. No candidate PHP/DLL, installation/download, trust/
permission decision, WordPress/authentication/SQL/database, site/live state,
schedule, grants, activation, rollback, package/release/tag/deploy or wiring.

## Why one bounded evaluation is proportionate

An observed count of 135 is now available; repeating count calibration adds no
current value. Seven additional bounded metadata rows do not require another
runtime, native compiler, principal, protected-staging system, installer or
generic framework. Reuse the audited existing tool's fixed owned harness and
ordered observer/termination proof, with explicit new profile semantics and
isolated tests first. Do not learn or bless dependencies from the first catalog.

The [Modules API](https://learn.microsoft.com/en-us/dotnet/api/system.diagnostics.process.modules?view=net-10.0)
provides refreshed process-module information, can throw and may be empty during
startup. The getter can materialize data before a row guard runs. Count-only
916 ms is not a full-snapshot benchmark, two-second headroom calculation,
worst-case latency or native allocation bound. Two matching held metadata
snapshots cannot exclude transient loads or a malicious same-user/admin host.

If the new fixed attempt cannot pass with the other existing limits intact,
stop after owned cleanup and report the exact stage. Reassess/retire this route
instead of automatically increasing row, size, path, catalog or time budgets.
No additional calibration, retry, filtered set or staged "partial success" may
stand in for the complete ordered result.

## Fixed resource envelope — proposed, not implemented

| Dimension | Selected future tool profile | Candidate/current executable effect |
| --- | --- | --- |
| Rows | 2..135 per complete snapshot; no dynamic cap | None; candidate/current tool stay 128 |
| Metadata sizes | 32 MiB per mapped image, 256 MiB aggregate | Unchanged; not verified DLL file sizes/hashes |
| Names/paths | Existing 100-character name / 240-character path guards | No relaxation |
| Catalog | At most 131,072 serialized UTF-8 bytes per snapshot | No larger file or catalog |
| Child streams | 512-byte frame, 16 KiB stdout, 4097-byte stderr overflow sentinel | No relaxation; positive stderr remains zero |
| Time | Observer at most 2 s; work 20 s; shared total 25 s including cleanup | No additive budgets or retries |
| Ownership | At most two concurrent children, five launches across three ordered groups | Original objects/handles; no arbitrary processes |

For a full snapshot, count-check immediately after collection acquisition and
before row metadata; also cap actual visited rows before reading each row and
require enumerated unique rows to equal the advertised count. Reject count/
enumerator mismatch rather than trust Count to bound a substituted iterator.
Two complete snapshots inspect at most 270 rows, with unchanged ordinal-
ignore-case uniqueness, required modules and exact complete-text comparison.
[SortedDictionary](https://learn.microsoft.com/en-us/dotnet/api/system.collections.generic.sorteddictionary-2?view=net-10.0)
keeps the existing ordered-map approach; do not add sorting/index frameworks.

The row ceiling alone does not bound catalog acceptance. A conservative source-
level estimate is 100 ASCII name bytes + 1,920 path-hex bytes (240 UTF-16 units,
conservatively four UTF-8 bytes/unit, then hex) + eight size digits + two
separators + LF = 2,031 serialized bytes/row. At 135 rows this is 274,185 bytes,
greater than the unchanged 131,072-byte cap. This is arithmetic, not observed
paths, allocated heap or a reason to enlarge the catalog.

Require incremental exact UTF-8 accounting including every LF, before retaining
each serialized row in the dictionary: reject if its byte count exceeds the
remaining 131,072-byte budget. Retain the final exact byte-count check before
CreateNew/write. At most one bounded row is prepared before admission; two
accepted serialized catalogs total at most 262,144 bytes. Managed strings,
dictionary/object overhead, encoding temporaries and the native Modules getter
are not included in that payload bound; do not call it a hard process-memory
bound. Preserve observer isolation and original-object cleanup on deadline.

## Private profile version and path ownership

Reserve the fixed label `tool-snapshot-135-v1`, observer readiness/summary tag
`ADBTS1`, and diagnostic tag `ADBTSD1`. Version the full observer side in the
existing manual harness, not a parallel launcher or arbitrary profile/cap API.
The future full controller explicitly expects that profile; no legacy-summary
fallback or environment/CLI-supplied row budget. The fixed worker's ADBT1 held/
release/ACK handshake is unchanged and is not a full-snapshot receipt.

The new observer summary retains the existing exact 14-field shape and binding:

```text
ADBTS1|run32|observer|pid|start|source64|modules|worker-pid|worker-start|count|mapped-total|mapped-max|catalog-sha64|stable\n
```

Only the versioned full summary admits 2..135 with all size/digest/shape checks.
The matching versioned error fact retains the existing 13-field shape, finite
phase/reason/mask grammar and EOF-only extraction: rows at most 136 (overflow
sentinel, not an exact count), row index at most 135. Enumeration overflow
refuses before extra metadata reads; report module-count with index zero and
the overflow sentinel. Do not reuse old rows:129 semantics to mean 135/136.
Any stderr still forbids positive completion; diagnostics are never admission.

Affected future consumers: full observer child, summary/diagnostic functions
and EOF transport decoder, controller observer-readiness expectation and owned
source cohort, pure PowerShell regressions and PHP source contracts. Explicitly
replace the old full-summary/error contract, with no acceptance fallback for
ADBT1 module summaries or ADBTD1 facts. Preserve their rejection tests under the
new profile rather than silently accepting two ceilings. ADBTC1 count grammar
and candidate ADBMP1 remain separate and unchanged.

The count controller reuses the transport. Changing it invalidates reuse of the
old native count proof as evidence for the new source cohort; preserve that
proof as historical and rerun its pure parser/terminal/source regressions for
compatibility. Do not rerun native calibration merely to freshen a record.
Update the count source test's old full-observer 128 assertion to the explicit
new versioned tool contract; strengthen separate exact candidate-128 protection,
never replace it with a generic occurrence of "128". No changes to the three
minimum PHP helpers, bootstrap, runtime includes, pins, lockfiles or workflows.

## Required validation and one separately approved ordered attempt

Before any future launch, non-vacuous actual pure tests must accept complete
bounded synthetic summaries at 128/129/135 and reject 136, zero/one, overflow,
wrong profile/purpose/source/run/PID/start, legacy/count/candidate frames, stale/
partial/duplicate/trailing records, nonzero exit/stderr, wrong catalog digest,
enumerator mismatch, duplicate names, malformed paths, per-image/aggregate
overflow and changed held snapshots. Test exact catalog boundary/one byte over
with LF and non-ASCII paths, and refusal before retaining an over-budget row.
Test code that actually performs row accounting; static substring tests alone
are insufficient. Mutate required bounds/bindings/cleanup and an always-
unavailable owner in memory, requiring assertion failures and source restoration.
Re-run the existing count and full-observer suites; no guard removal for green CI.

Next approval must explicitly cover implementation/tests and **one** owned
three-group proof: (1) fresh pinned source/image and held target, two complete
stable catalogs, digest/bytes validation, clean observer exit/drain, exact worker
release/exit/drain; (2) fresh held observer timeout followed by bounded owned
termination/drain; (3) fresh held worker wait cancellation demonstrably not exit,
then termination/drain. Stop on the first refusal. At most two children coexist;
all three groups share the unchanged work/global envelope, not one budget each.
After all groups, verify every original identity exited, source/copy/image and
canary readbacks, exact nonrecursive no-reparse fixture cleanup and owned absence.
No broad PID scan, guessed descendants, foreign cleanup or incidental guard repair.

Only all groups and closure may report tool-observer process-proof completion.
A full metadata catalog is still not a DLL-content/signature/trust manifest or
real host-admission provenance. Existing host-DATA accepts at most 128 and uses
file-hash/path contracts this metadata proof does not establish: that downstream
integration remains a separate gated decision even after a successful tool
proof. Candidate execution, runtime/native/database qualification and production
wiring remain disabled. No UI/UX, Database Review, release readiness or product-
feature completion is inferred from isolated tests or ordinary CI.

## Validation and publication

Executed ordinary repository-only QA with trusted PHP 8.5.1, not the candidate:

- `php vendor/bin/phpunit --do-not-cache-result --filter
  'SourcePolicyToolCountCalibrationTest|SourcePolicyMinimum|SourcePolicyToolObserver'`:
  281 tests / 11,775 assertions, no failures/skips.
- Existing pure count and observer PowerShell suites: 339 checks / ten killed
  controls and 417 checks / six killed controls respectively; source restored,
  zero proof processes/fixtures. No new hypothetical profile is tested as implemented.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,324 tests /
  20,983 assertions, no failures; two existing Sodium-unavailable signing skips.
- PHPCS 243 files; admin 23/23, no failures/skips; structure 149 runtime / 233
  PHP support / 224 test files / 221 class names; asset build passed, no tracked
  generated delta. Strict Composer validation and Composer/npm moderate audits
  passed, zero known advisories/vulnerabilities; no installation/dependency changes.
- 144 owning local documentation targets, whitespace and scope/resource/error/
  compatibility/provenance/test-integrity review passed. Only this design, plan
  and predecessor pointer change; every executable/test/guard/manifest/workflow
  remains unchanged against baseline. Uploader/toolkit remain clean/read-only
  at 3dfcb15/c067eb6. No new whole-plugin review/readiness credit.

Design publication `b2740a55fc9bc3268b50eb9616e5c37fcd023595` is pushed;
[exact-head CI 41118340191](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41118340191)
succeeded. Actual PHP 7.4: 2,324 tests / 20,985 assertions; PHP 8.3: 2,324 /
20,990, both without failures/skips. Admin 23/23 each, no failures/skips;
manifest, structure, build, PHPCS and audits passed, zero known advisories/
vulnerabilities. Existing Setup Node `punycode`/`url.parse()` deprecation warnings
remain tool-level warnings; no runtime/acceptance change suppresses them.

This docs-only evidence closeout receives its own pushed exact-head CI and
clean/remote verification at handoff. No physical/source-profile/host experiment
runs in this design task, and no historical evidence is refreshed. Ordinary
green CI does not validate or authorize the proposed 135-row physical profile.

Implementation-plan progress: selects a finite tool-only evaluation budget and
complete cost/guard/receipt obligations; full observer feasibility remains
unproved. Previous cumulative count stays 1/8 plus separately approved proof/
diagnostic work, not a completed autonomous eight-task batch.

Recommended next gated slice: **Local-only Fixed 135-Module Tool Snapshot Profile
and One Ordered Observer/Termination Owned Cleanup Proof**. Approve the explicit
tool-only profile implementation, isolated regressions and single owned proof
scope, preserving candidate 128 and every other existing resource/ownership
guard. This design alone does not authorize that execution or any production path.
