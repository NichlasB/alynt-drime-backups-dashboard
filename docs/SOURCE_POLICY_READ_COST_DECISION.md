# Source-Policy Read-Cost and Freshness Optimization Decision

## Scope and authority — 2026-10-27

This is the final worthwhile repository-only design/disposition task under the
standing safe-local ceiling of8, following successful archived-count owned proof
and exact-head CI. Baseline5980b298ae4bff3b92e4c07ce85693dd1c809374 was clean and
synced. R1 design/test work only: no production implementation, option API
operations, WordPress/native execution, credentials, site or wiring changes.
It advances the remaining canonical06 inventory item rather than generic cleanup.
The separate native proof is not counted as a product task.

## Observed cost and existing contract

`Source_Policy::all_policies()` reads get_option() and sanitizes the entire stored
map on every storage-mode call. Multiple classifier/render callers can repeat
that work. Phase1's synthetic100-entry/100-lookup probe observed100 get_option
calls and20,100 sanitize_key calls. This establishes repeated processing, not
100 physical database queries, actual fleet size or production CPU/latency.
WordPress option caching may already avoid SQL. No material production slowdown,
time threshold, controlled production profile or release blocker was established.

The constructor's private policies override is already a sanitized in-memory
snapshot for fixtures. Storage mode intentionally does not populate that field;
it observes the option on every read. set_source_mode() returns true for an
unchanged sanitized value; otherwise it returns update_option()'s result and
does not optimistically replace the override. These are existing semantics, not
permission to run writes or silently change persistence-failure behavior.

The map sanitizer normalizes signed/alias site keys and source/mode strings,
merging eligible external_optional entries; a direct raw site-key lookup is not
equivalent for noncanonical maps. required remains the default for unsupported
sources/absent policy. Policies affect dashboard classification/display only,
not client WPvivid settings, backups, Drime access or action permission.

## Decision: defer memoization; retain current runtime behavior

Do not add persistent/transient/static/request memoization or substitute direct
raw-key lookup in this candidate. Two confirmed query-multiplication findings
have been repaired and physically qualified. This smaller CPU opportunity has
no measured user-impact justification for expanding policy freshness semantics.
Record it as an intentional optimization deferral, not an implemented fix, a
current vulnerability, an exhausted product backlog or a newly required cache.
Reopen only with new profiling evidence or an explicit user-requested contract.

Rejected shortcuts and concrete reasons:

1. Populate the existing policies field after a storage read: this activates the
   fixture-override short circuit and suppresses later get_option observations;
   existing setters do not update it after storage writes. Freshness changes.
2. Keep raw_map plus sanitized_map and reuse when raw arrays are strictly equal:
   references can mutate both raw arrays, while cached sanitized output stays
   old. Same-identity mutable Stringable objects present the same hazard. A
   reference-free plain-value copy is a necessary contrasting control, not a
   general cache-safety proof.
3. Replace whole-map normalization with raw_map[site_id]: signed/alias site keys
   and normalized source keys can contribute an existing optional policy even
   where that direct key says required. Existing merge behavior would change.
4. Treat sanitization as a pure function of raw strings: genuine
   [WordPress sanitize_key()](https://developer.wordpress.org/reference/functions/sanitize_key/)
   applies a filter. Skipping calls can alter callback results/observations,
   global hook counters and all-hook observers even without changing raw data.
   No sanitizer-hook compatibility contract is newly granted by this decision.

Any future proposal needs explicit freshness/observer scope, a stable immutable
input strategy, invalidation on successful/failed/no-op writes and external
same-request updates, constructor-override preservation, legacy normalization
equivalence and filter-aware tests. Do not add complex guards, copies, hashes,
dependencies or a source-policy framework merely to optimize an unprofiled cost.
Benchmark the whole strategy, including comparison/copy/observer overhead, before
claiming improvement. No speculative design is production-wired here.

## Isolated evidence and proof limits

SourcePolicyMemoizationDesignTest passes5 tests/21 assertions. Three immutable-
snapshot counterexamples use referenced modes, referenced site maps and mutable
Stringable objects. The control uses reference-free value copies; the final case
uses the actual sanitizer/source_mode with a signed alias and uppercase strings
to reject direct-key equivalence. Positive optional values and changed required
results prevent always-empty/always-optional behavior satisfying the examples.

Tests invoke the actual existing constructor-override sanitizer with PHP DATA,
not actual get_option/update_option, storage reads, WordPress filter dispatch,
authentication, concurrency or a cache implementation. References/objects are
counterexample inputs, not an allegation that current stored site data contains
them. The filter point is primary-source review, not a genuine-hook test claim.
No existing expectation, runtime guard or source-policy code was changed.
Source-policy file SHA256 remains
E90BCCA58F3A87E98348A0E650D0E0548A0933E710D37218AA7D5AA0A0A0B2D1.

Local full440 tests/2,937 assertions with two known missing-Sodium skips; new5/21
and related randomized seed20261027 suite7/23 pass with no skips. Lint/structure
pass; all runtime/native-proof/generated files remain unchanged. Published design
b5af59366241e6b13c42a9720a8aa65297e5d4d0 passed
[exact-head CI40534457864](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40534457864):
PHP7.4/8.3 each440/2944, no skips/failures; Node16/16 and all configured gates,
zero audit advisories/vulnerabilities. Actual counts retrieved from both job logs;
remote master matched the clean design head. This completes the cumulative
safe-local ceiling **8/8**; the eighth task is design/disposition, not a shipped
runtime cache or the separately approved physical proof. This record-only
closeout changes no tested source; CI does not rerun native/WordPress operations.

## Implementation-plan reconciliation and next gate

Canonical06 now has explicit dispositions for all three confirmed Medium items:
history batching repaired/owned-qualified; archived counts repaired/owned-
qualified; repeated policy sanitization intentionally deferred for the reasons
above. SQL scan/retention/polling cost concerns remain profiling watch points,
not invented missing-index defects. No production-load or broad release-readiness
claim is made. The owning toolkit's canonical checklist is still unchanged;
this design does not mark06 or historical whole-plugin rows Complete.

After the safe-local8/8 ceiling is verified, recommended next gated action:
approve **toolkit-only PRE_RELEASE_CHECKLIST.md reconciliation of current06
evidence**, including both physical proof limits, source-policy deferral and
source-version limits on earlier evidence, preserving historical rows and the
separate toolkit Git boundary. Resolve the exact canonical resume there before
07 Edge Cases Phase1. No packaging/release/deployment or deferred V2.3 reopening.
