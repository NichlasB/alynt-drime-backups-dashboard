# Trusted Production Context and Lazy-Provider Safety Foundation

Current successor decision — 2026-11-05: [legacy production eligibility/join design](SOURCE_POLICY_LEGACY_PRODUCTION_ELIGIBILITY_DECISION.md)
is now recorded, not implemented. It explicitly separates this foundation's
already-active isolated owner validation from the future pre-connect/active join.
Next approval is Production-Bound Legacy Expectation and Two-Phase Capture/
Admission Join Safety Foundation, repository-only/isolated, with explicit trust-
proposal acceptance. No source, native/public provisioning or wiring change here;
earlier foundation proof remains source-bound and campaign10/10 unchanged.

## Authority, restore point and scope — 2026-11-05

Explicit approval covers repository-only implementation, isolated tests, review,
validation, commit/push and exact-head ordinary CI. This is the narrow approved
R3 trusted-composition/authorization boundary, not a general autonomous tier
increase. Clean/synced restore/review baseline
`c3e1f222c68b1bfaec13991b6bf6cd29a8d9b97b`, exact
[CI42042192518](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/42042192518)
reverified successful. Implements the bounded proposal in the
[cohort/eligibility decision](SOURCE_POLICY_PROFILE_COHORT_ELIGIBILITY_RECONCILIATION.md#selected-next-implementation-proposal-requiring-approval),
under the [trusted capture contract](SOURCE_POLICY_PRODUCTION_CONNECTION_READINESS_DESIGN.md#trusted-context-and-lazy-provider-contract--proposed-not-implemented).

Version0.1.77/schema8/PHP7.4/WordPress6.0 unchanged. No installed WordPress source,
configuration or runtime is loaded/read; no real authentication, credentials,
SQL, database, storage write, native execution, fixture account/permission, LocalWP,
live/SSH, client/Drime, schedule, activation/rollback, dependency installation,
package/release/deployment or production wiring. Site Operations consulted:
repository-only, not a registered site target; no target confirmation or Novamira
site operation is applicable/performed. Toolkit checklist is a separate docs-only
boundary; uploader untouched. Synthetic private passwords are test literals only.

## What is implemented, and what deliberately is not

| Responsibility | Actual source/behavior |
| --- | --- |
| Fixed declaration expectations | [Trusted_Source](../includes/class-source-policy-trusted-source.php) contains the previously pinned6.0/7.1.3/7.1.4 library maps, exact PHP metadata and legacy/current declaration filenames. Test parity against owning historical pin DATA prevents accidental self-learning/drift. No source file is executed or included by this catalogue. |
| Operation-local capture | [Trusted_Context](../includes/class-source-policy-trusted-context.php): private receiver binding, exact stock ambient/declaration identity, fixed source hashes and empty non-query observer policy BEFORE config/parser access; trusted constants/stock options table, full parsed/raw endpoint agreement, settings validation and same receiver/core/database/table. No secret-bearing public DTO or returned setup frame. |
| Lazy guarded dispatch | [Lazy_Provider](../includes/class-source-policy-lazy-provider.php): handle/read-free private construction; first authorized operation consumes before capture; fixed adapter/context only; no callable/provider/connection descriptor from request. Dispatches real existing view/conditional-save methods, not a manufactured successful status. |
| Operation lifetime | [Adapter operation trait](../includes/traits/trait-source-policy-adapter-operation.php) retains the existing private run/close lifecycle and optional context entry check. [Native_Adapter](../includes/class-source-policy-native-adapter.php) revalidates exact context at each existing profile boundary; historical NULL-context semantics remain. No raw receiver is returned. |
| Request admission | [Guarded_Entry](../includes/class-source-policy-guarded-entry.php) keeps capability → action-specific nonce → raw types/finite fields/revision → exact record → owner ordering. View has its own capability/type/record path. Canonical bounded positive IDs reject overflow, signs, padding and compounds before absint/unslash/record/capture. Failed record resolution returns unavailable without exposing diagnostics. |

The context/provider/adapter/connection/query-context/guarded-entry public create()
methods remain unavailable. All new construction/binding is private and exercised
only through explicit isolated fixture composition. **No native constructor,
bootstrap handoff or production mint is implemented/enabled here.** The foundation
captures and validates the operation-local relation to the already fixture-bound
fixed owner; it does not turn a caller array, receipt or catalogue match into a
connection grant. Runtime uses declaration Reflection only, not production
Reflection-based private-property/constructor injection copied from the harness.

Actual fixtures bind private source/driver API counterparts and modeled active
connection facts using Reflection, as existing isolated tests do. Those bindings
are disclosed test assumptions, not genuine-core/native/authentication proof.
There is no runtime test constant, artificial ready path, success shortcut,
public fixture setter or fallback enabling those test assumptions in production.

No bootstrap/plugin/admin/form/legacy-writer registration or loading changes.
Source_Policy::set_source_mode still uses the existing whole-map writer; the
known application concurrency fix is NOT delivered. Public bool behavior, option,
schema, nonce/action identities, chronological history and actual source labels
remain unchanged. Classifiers, polling and diagnostics stay ordinary non-native
reads. Native/default greeting/session/schema guards, no-reconnect behavior,
statement/IO/time budgets and Minimum_Lab_Scope remain byte-identical.

## Lifecycle, disclosure and compatibility limits

- Fresh → capturing → captured → closed, no reseal/replay. Provider consumes
  before the first fallible capture; same-provider callback reentry cannot start
  another operation or prematurely close the outer owner.
- The adapter compares context against its actual connection before storage and
  at later profile boundaries. Ambient/core/table/source/hook changes refuse or
  preserve post-submission uncertainty; no retry, rebase, reconnect or legacy
  whole-map fallback. Matching a current unknown hash is never origin authority.
- Terminal cleanup revokes before fallible close and drops frame/password,
  receiver, ambient, binding, policy, provider-owner/context and adapter-operation
  references. Existing final close is idempotent: one underlying close attempt,
  owned placeholder entry only, never ambient session/registry cleanup. Unknown
  closure is not success; a saved or already uncertain result stays uncertain.
  These are API-lifecycle assertions, not physical release of a database session.
- Serialization/restoration and cloning of context/provider are refused; public
  object properties expose no handles/settings. No raw exception/SQL/password is
  returned/logged. Filesystem/parser warnings become fixed refusals with the exact
  previous error handler restored. Clearing references is not PHP secure erasure
  or isolation from arbitrary malicious code/Reflection in the same process.
- Source reads are code-owned (at most12 known files, each at most512KiB); no
  request path, discovery, unbounded source traversal, callback autoload or unknown
  ArrayAccess. Revalidation deliberately rechecks instead of blessing changed
  state. This is bounded-byte trusted-filesystem consistency, not an OS-level IO
  deadline or distribution/loaded-object/ABI attestation. Future native/request
  qualification must assess actual cost within unchanged operation limits.
- Catalogue rows are NOT native/production admission. Empty non-query policy
  does not qualify arbitrary installed hooks, plugins, external caches or FPM.
  Core6.0's pinned parser returns a port string; only that exact version/type is
  normalized to an integer and checked against the full unchanged transport
  contract. No new transport, server, suffix or collation acceptance follows.

## Non-vacuous isolated validation and fixes

[Provider tests](../tests/SourcePolicyTrustedProviderTest.php) execute extension-
free PHP -n workers with synthetic WordPress/auth/config/driver APIs and RAM rows.
[Source tests](../tests/SourcePolicyTrustedSourceTest.php) check catalogue parity.
42 worker scenarios plus the catalogue test: **43 tests/185 assertions**, no skips.
Actual positive view/save/no-op and archived-parser-type paths drive Guarded_Entry
→ lazy capture → actual Native_Adapter, and preserve other data/autoload semantics.
An always-unavailable capture fails the positive, not merely negative expectations.

Refusals cover denied capability, separate nonce domain/expired/compound nonce,
compound/negative/padded/overflow/overlong IDs, field/revision bounds, missing or
wrong record/record exception, default fixture non-admission, wrong hash/parser
origin, unknown ambient/receiver/hook/ArrayAccess registry, parser warnings,
changed ambient/core/table/hook, missing private facts, stale revision, reentry, read/write/close failure,
serialization/restoration/clone and every public factory remaining closed.
Tests assert zero parser/capture/storage activity on early request failures,
no ambient close/reconnect, cleared private references and exact error-handler
restoration. All workers assert zero synthetic native allocate/connect/command
calls; modeled PHP owner queries mutate RAM only, not an actual SQL server.

[Semantic controls](../tests/node/source-policy-trusted-controls.test.mjs) create
one owned temporary SOURCE copy, assert the real positive, then assertion-kill
ELEVEN individual bypasses: always unavailable, capability, nonce, ID, record,
source digest, parser declaration, late context revalidation, registry shape,
single-use/reentry and error-handler restoration. Each source is restored and
compared before the next control; the exact owned temp tree is removed/absence
checked. No working-tree mutation, WordPress/native proof or security weakening.
Independent underlying profile guards remain defense in depth; tests do not
remove them to force an otherwise impossible foreign/native success.

Implementation findings fixed without changed acceptance:

| Finding | Fix and regression disposition |
| --- | --- |
| PHP integer overflow could emit a warning during ID coercion | Lexical bound against PHP_INT_MAX before any cast; site-overflow/negative/padded cases and ID bypass control. |
| Unknown registry could invoke ArrayAccess before source refusal | Array shape check before hook indexing; tripwire asserts0 calls and its removal is assertion-killed. |
| Source/parser diagnostics could escape or replace the caller's handler | Scoped warning-to-refusal conversion/finally restore; parser-warning case and handler-removal control. |
| Cleanup failure could downgrade existing uncertainty | Preserve exact safe write_uncertain and saved→uncertain; close-error/after-write cases assert codes and once-only close. |

The first isolated positive failures also exposed a fixture using the6.0 parser
DATA with a7.1.3 frame; fix selects the correct isolated core row, not a relaxed
runtime parser. Style fixes use PHPCBF on changed files only. No test was skipped,
guard removed, artificial success added or native attempt consumed to get green.

## Canonical source-scoped review checkpoint

FEATURE_LIGHT_REVIEW → FEATURE_BLOAT_AND_STRUCTURE_REVIEW (read-only Phase1, no
additional cleanup needed) → final FEATURE_SECURITY_REVIEW. Explicit baseline
c3e1f22 covers this whole coherent foundation, not a tail documentation commit.

Feature scope: dormant private capture/dispatch and its direct guarded adapter
callers. Files reviewed:13 source/test files plus owning records and minimum
unchanged bootstrap/profile/query-context consumers. Risk areas: Other/trusted
configuration, authorization, IO/error privacy; no registered UI/API/DB operation.
Issues found/fixed: the four implementation findings above; final unaddressed
defects0. Positive regression coverage exists at the isolated API/IO layer; actual
auth/storage/native/FPM coverage is explicitly pending. No physical Database
Review, whole-plugin07/07A/13/FULL completion or UI/UX review is claimed.

Canonical bloat helper, same explicit baseline, reports12 PHP files,0 oversized.
Final TOTALs: guarded-entry151, lazy-provider119, adapter274, trusted-context286
(277 before final security hardening), trusted-source64, adapter-operation62;
tests15/17 and support99/74/35/21.
CODE=1 output is the existing helper limitation, not a useful complexity count;
the helper omits .mjs, so the55-line Node control was separately counted/reviewed.
Project structure guard also passes stricter150-line test/support limits and the
runtime300-line cap. Classification: retain/consolidate the existing cohesive
lifecycle at the approved seam; no further cleanup/removal/split/defer action.
Reference search confirms explicit private test consumers and adapter trait
loading, no new hooks/filters/shortcodes/AJAX/REST/cron registrations or bootstrap
wiring. Immutable runtime pin duplication is intentional: runtime must not import
development-only harness files; parity tests enforce the common origin DATA.

## Publication, evidence and exact next gate

Final CI results and exact published head/run are recorded after completion in
the separately owned toolkit source-scoped checklist. No whole-plugin row is
refreshed. Local complete-suite discovery2709:23929 assertions, no failures,
two existing Sodium signer skips independently confirmed by5 signer tests/9
assertions. Initial discovery2704/23909 likewise passed with those two skips.
The final added missing-facts case and all new paths pass targeted43/185; full
local discovery was already running when that last case was added, so it is NOT
relabeled as the final head's test count. Exact-head CI must test final discovery.
All100 Node tests pass; the final eleven-control test is separately reverified.
Full PHPCS261 files, project structure, source-cohort/scope and whitespace pass.
This is isolated QA, not genuine WordPress/native or complete runtime readiness.

Final focused security gate: reviewed inputs/origins, no native mint or raw handle,
authorization/nonce/record-before-capture, fixed SQL/profile reuse, private settings
and cleanup/uncertainty/error disclosure. The four findings above have permanent
isolated regressions; no unresolved scoped security defect or missing feasible
regression handoff. True source/auth/native/FPM integration proof remains gated,
not inferred from these tests or current metadata. No release-readiness claim.

This source change affects Native_Adapter and adds a loaded lifecycle trait.
**All prior M/W/L native receipts remain historical, not current-cohort grants.**
The earlier private legacy success is retained, not erased; no new native run is
authorized. Freeze final shared eligibility/composition before one approved
affected-profile AND genuine-request qualification batch, not a rerun per commit.

Implementation-plan progress: dormant trusted capture/guarded dispatch now exists
and has positive isolated evidence; production/native construction remains absent
by scope. On exact-head publication/CI and owning evidence closeout, count ONE
approved foundation milestone, campaign10/10; static9 and16 consumed native
attempts unchanged. No extra credit for tests, retries, docs, cleanup or reviews.

**Recommended next gated slice: Repository-Only Legacy Production Greeting/Session
Eligibility Decision**, design-only. Decide a separate code-owned production
context policy for the exact legacy tuple/helper behavior without promoting
Minimum_Lab_Scope/job/image/fixture evidence, stripping prefixes, broadening
collations or raising support floors. That decision is required before dormant
native composition/final qualification and the single cohesive provider/form/
legacy-writer switch. No further generic infrastructure or current native/site
operation is recommended. Production wiring, activation and rollback stay disabled.
