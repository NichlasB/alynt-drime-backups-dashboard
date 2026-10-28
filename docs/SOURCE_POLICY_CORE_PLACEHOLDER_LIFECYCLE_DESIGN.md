# Source-Policy Genuine-Core Placeholder-Escape Admission and Connection-Lifecycle Compatibility Design

## Authority and immutable baseline — 2026-10-28

Separately approved repository-only design through validation, commit, push and
exact-head CI. Clean/synced restore point:
`756298a1aa9d631effceb2b66a63f09bdfb9810c`. R1 documentation for a future R3
SQL/callback ownership boundary; maximum autonomous R2 authority is not raised.
Version0.1.77/schema8 and declared PHP7.4/WordPress6.0 remain unchanged.

Only this design and owning plan/review/audit references change. No runtime,
test, manual script, asset/catalog, dependency, CI or packaging change. No
LocalWP/site inspection, core-library probe, authentication, SQL/storage writes,
native connection/execution, fixture, credential, schedule, service, client/
Drime, release/tag/package or deployment operation. Public primary sources were
read online; prior installed-profile evidence is historical, not rerun.
Uploader/toolkit remain read-only; V2.3/memoization stay deferred, no batch-count
inflation. The production lost-update race and native proof remain open.

## Evidence and the precise gap

The [blocked native audit](SOURCE_POLICY_NON_RECONNECTING_NATIVE_AUDIT.md)
recorded nine zero-SQL checks: genuine prepare registers core's query callback,
which the [dormant profile](../includes/class-source-policy-native-profile.php)
rejects even with a matching fingerprint. Its installed7.1.3 source hash and
77-table preservation evidence remain historical. Existing RAM preparation
returns an opaque token without installing that callback; it cannot qualify
genuine preparation or a native provider.

[Core placeholder_escape](https://developer.wordpress.org/reference/classes/wpdb/placeholder_escape/)
registers its receiver's remove_placeholder_escape filter at priority0.
[Core removal](https://developer.wordpress.org/reference/classes/wpdb/remove_placeholder_escape/)
calls placeholder_escape again, then restores percent markers. Consequently,
checking only the callback's name misses a virtual method dependency.
[Core close](https://developer.wordpress.org/reference/classes/wpdb/close/)
closes the driver and changes connection flags; the shown implementation does
not remove that query callback. An absent driver also returns false, so repeated
core close calls are not independently an idempotent lifecycle contract.

The [pinned6.0 wp-db.php](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/wp-db.php)
also contains this preparation/callback mechanism and the same cleanup concern;
this is source compatibility evidence, not minimum-core native qualification.
Current online reference pages are not a source pin for the installed profile.

Selected change to the earlier [integration design](SOURCE_POLICY_NATIVE_ADAPTER_INTEGRATION_DESIGN.md):
permit only proven core placeholder mechanics for exact internally owned
receivers. Arbitrary query transformation remains unsupported. This document
does not itself relax the existing runtime refusal or complete native eligibility.

## Fixed receiver and executable-code contract

Use one private operation context, not a public callback/connection allowlist:

- Exactly one fixed operation-owned non-reconnecting connection. It cannot
  alias global wpdb. Its private provider, not request DATA, establishes identity.
  It is fresh/single-use; a reused or previously closed receiver is not readmitted.
- At most one already-qualified ambient global wpdb receiver, snapshotted by
  exact object identity. The initial policy accepts stock core wpdb, not an
  arbitrary subclass/drop-in/router. It is never the owned mutation connection.
- At most two query entries total: the owned callback and the ambient callback
  if already present. Only integer priority0, accepted_args1, canonical two-slot
  object/method callback and exact remove_placeholder_escape spelling qualify.
  Preserve their recorded order; validate actual entries, not just registry keys.
- No third receiver, extra priority/duplicate entry, string/static lookalike,
  wrapper closure, invokable object, extra arguments or matching-name subclass
  exception. Unknown callbacks reject without invocation, even if fingerprinted.

Do not discover trusted receivers by scanning callbacks and then adopting them.
Bind them first from fixed internal composition and trusted core context. Strong
references plus === identity prevent object-ID reuse from becoming admission.
If the ambient global identity changes, reject rather than resnapshotting it.
No version string, hash supplied by a caller, class-name suffix or instanceof
test alone establishes executable provenance.

Before invoking preparation/placeholder APIs, verify already-loaded concrete
classes and relevant methods against the qualified core/plugin source profile.
The callback and placeholder_escape/add_placeholder_escape/prepare/_real_escape
chain must use audited core bodies, not subclass overrides, traits, __call or
delegation to unknown methods. Include any profile-specific transitive method
used by the fixed SQL templates. No callable probe may invoke magic/autoload.
The fixed query/execute/non-reconnect/close boundaries also need code identity.
Production must not accept a test subclass merely because it has a ready flag
or synthetic qualification DATA. Isolated fixtures are explicitly not providers.

Prefer inherited genuine core preparation and small final owned lifecycle
boundaries; do not copy core query, call private executor methods or build a
parallel escape implementation. Qualify core6.0 and the recorded7.1.3 profile
separately before use. Unknown versions/source/method layouts are unavailable;
do not silently raise minimum versions or change the ordinary classifier path.

## Preparation, fingerprint and dispatch ordering

1. Keep public capability/nonce/raw-intent/site guards ahead of protected owner
   reads and any native handle construction. Factories remain closed until the
   separate implementation/proof/integration gates are satisfied.
2. Check fixed receiver/source identities and the existing bounded query registry
   structurally, without executing any unknown callback. Snapshot the ambient
   callback's presence and identity; do not proactively prepare on global wpdb.
3. On the verified owned object, call genuine placeholder_escape to establish
   its exact core callback before sealing the operation fingerprint. This is a
   no-SQL preparation step, not permission to open a session or qualify metadata.
   Capture ownership before registration so partial setup can be cleaned.
4. Validate the resulting full query pipeline and seal its fingerprint together
   with the other existing relevant-hook/profile facts. Future bounded native
   setup must keep this pipeline invariant; native/schema/cache eligibility
   still requires its own genuine evidence before a protected observation.
5. For every fixed SELECT/INSERT/CAS and bounded duplicate observation: validate
   before prepare, call genuine prepare, then validate again before execution.
   The current inline prepare-then-execute observation lacks this second boundary
   and must be changed deliberately. Never stamp a fresh fingerprint to accept
   a change made during preparation. Do not replay preparation/callbacks to heal.
6. Retain checks across sanitation, prewrite, cache and success-callback phases.
   Mark mutation submission immediately before the first possible driver attempt,
   after preparation and its admission check. Preserve uncertainty after that
   point; no reconnect, second physical mutation, retry/rebase or compensation.

No temporary removal/reinsertion of core query filters around an execute call.
Let the exact admitted core pipeline restore placeholders through ordinary core
execution. Do not manually replace percent markers, bypass prepare or substitute
an identity query function. This is a narrow core transformation, not an identity
filter: genuine percent markers must change while value semantics stay exact.
Treat priority0 as a real registration, not false:
has_filter results require strict false comparison.

Do not assume generated tokens are equal across receivers or PHP versions.
Validate percent/quote/backslash semantics through the finite complete pipeline,
including the optional ambient receiver, on each supported qualification profile.
Tokens are internal implementation DATA: never expose/log/persist them or turn
them into authentication, intent or mutation receipts.

If the absent ambient callback appears during an operation, or another owned
connection registers a callback, admission changes and closes. Do not expand
the receiver set dynamically. Sequential owners may proceed after exact cleanup;
simultaneous second owners remain unsupported rather than creating a general
connection registry. A native proof may use the qualified ambient connection as
the independent contender without admitting a third receiver. Reentry still
rejects before another protected read or outer-session cleanup.

## Owned cleanup and result semantics

The private lifecycle distinguishes never-opened, active, closing and confirmed
closed states. Track an owned callback registration independently of driver
creation: partial setup with no socket must still remove that callback. This
state is process-local bookkeeping, not a durable grant or recovery ledger.

On every terminal path, block further prepare/dispatch, attempt closure of only
the owned native handle, and independently attempt exact owned callback removal
in finally paths. Neither failure may prevent attempting the other cleanup.
Use the original receiver/method/priority registration, verify it still names
that entry, and verify absence with metadata afterward. Never call placeholder
APIs after removal to verify cleanup: they can register the callback again.

Never remove the ambient callback, remove_all_filters, restore an entire saved
registry, or close global wpdb. A moved/replaced/malformed entry is not permission
to delete unrelated callbacks or repair shared state. Leave such changes alone
and report unconfirmed cleanup. Do not retain the owned receiver in a new global
cache/pool after success. Core's own static token is not reset or exported.

Repeated cleanup is a no-op success only after the private owner has positively
confirmed both driver closure (or proven never opened) and owned callback absence.
Do not convert a first unexplained core close=false into success. Unknown native
closure stays unknown; never reconnect to verify it. Cleanup failure before
submission maps to unavailable; after submission maps to write_uncertain even
if one row was acknowledged. No automatic undo or claimed rollback of a saved row.

One coherent cleanup boundary must serve view, conditional save and the future
legacy bool bridge. Keep existing literal saved/unchanged/error projection and
private DTO rules. This exception grants no broader hooks, raw handles, shared
cache support, schema repair, query logging or physical atomicity guarantee.
Trusted PHP callbacks are not sandboxed; this contract does not resist arbitrary
code execution or make their side effects transactional.

## Required non-vacuous acceptance and proof layers

Next isolated implementation must improve the preparation double, not weaken
tests to preserve an always-unavailable owner. Model core registration, priority0,
percent restoration and exact close-time removal. Execute the actual dormant
owner/profile logic through positive view/insert/update/no-op/legacy results,
non-empty supported observers and unchanged CAS/read/uncertainty budgets.

Required rejection/ownership cases:

- Same callback name on wrong receiver/source, overridden placeholder dependency,
  wrong priority/arguments, malformed registry, third receiver, identity swap,
  closed/stale owner, unknown callback and captured matching-but-untrusted facts.
- Changes during warm-up/prepare and before every dispatch: unknown callback
  must not run and no protected query/mutation may follow failed admission.
  Cover own/ambient aliases, absent-then-added ambient callback and reentry.
- Sequential owners do not accumulate query callbacks; a second simultaneous
  owner rejects without deleting either the first owner's or ambient callback.
- Constructor/setup/prepare/observer/close/remove failures exercise independent
  cleanup. Verify external counters and registry/native state after returned
  errors so caught exceptions cannot hide unsafe calls or false success.
- Guard removal controls for receiver identity, method provenance, post-prepare
  recheck and exact cleanup must kill assertions, then restore source hashes.
  Existing arbitrary-query rejection tests remain protecting that domain; add
  distinct genuine-core-mechanics positives rather than deleting those tests.

Isolated provenance substitutes only simulate executable identity, never qualify
real core. A later separately authorized no-SQL genuine-core layer must verify
real preparation/registry/marker behavior on pinned6.0 and intended current
sources. The existing audit script records the old refusal; retain its historical
meaning or replace it with an explicitly versioned successor after acceptance,
not an unlabelled inverted expectation.

Then resume the approved native owned proof with fresh exact target/readiness/
preservation baselines. It must exercise the actual fixed construction, bounded
session/profile, adapter, core hook/cache behavior and non-reconnect failure
boundary with exact cleanup. Minimum-core/PHP matrix, installed callback graph
and native driver behavior cannot be inferred from source review or hosted RAM
CI. Final production factory/form wiring and release/deploy remain separate gates.

## Validation, plan progress and exact next gate

Source-scoped contract review addresses the recorded core-callback incompatibility
without claiming an implemented exception. Current runtime remains deliberately
closed; no native audit/probe was rerun. Existing isolated related suite378
tests/1405 assertions passes, randomized seed20261028, no failures/skips;
structure142 runtime/209 support/174 test files passes. Seven scoped relative
links resolve; diff hygiene, non-document baseline identity and non-target
clean-state checks pass. This validates documentation consistency/unchanged RAM
contracts, not the proposed callback exception. Exact-head CI is pending.

Focused contract review: immutable receiver binding, bounded exact registry,
transitive method provenance, priority0, no fingerprint refresh, post-prepare
guard, independent owned-only cleanup and failure uncertainty checked against
the actual dormant callers. No further design inconsistency identified. No
UI/UX workflow applies: no rendered output, notice, form, assets or public wiring
changed. This is not a canonical whole-plugin security/release-readiness rerun.

Implementation-plan progress: the next admission/ordering/cleanup contract is
specified; the native qualification block and actual lost-update repair remain
open until implementation and genuine evidence. No feature/native completion
or historical whole07/07A/13/FULL/toolkit readiness row is refreshed.

Recommended next gated slice: **repository-only Source-Policy Fixed Core
Placeholder-Escape Admission and Owned Lifecycle Safety Foundation**, isolated
API/driver/IO tests only. Implement the bounded receiver/provenance/post-prepare/
cleanup contract while all public factories, actual authentication/storage,
native execution and production wiring remain disabled. That approval is not
provided by this design-only slice. No native qualification or deployment now.
