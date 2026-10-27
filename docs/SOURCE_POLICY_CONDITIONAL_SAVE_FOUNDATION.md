# Source-Policy Conditional Save and Conflict Safety Foundation

## Authority and immutable baseline — 2026-10-27

Separately approved repository-only R3 foundation through validation, commit,
push and exact-head CI. Clean/synced source restore point:
`2d41d6c72c9feaf5c30a30e987611ee64f11d7a7`.
Implements the in-memory stage of the [approved design](SOURCE_POLICY_CONCURRENT_SAVE_CONTRACT_DESIGN.md),
NOT its native adapter or production form integration. Version0.1.77/schema8,
dependencies, capabilities/nonces, endpoints, source-policy option name, actual
storage/cron, factory, form and legacy boolean setter are unchanged.

No WordPress/site/database operations, real options/users/fixtures, SQL, cache
flush, native concurrency, HTTP, credentials, schedules, release/package/tag or
deployment. One owned php://memory value is closed in a test finally block;
two exact source-extraction temporary files are removed. Uploader/toolkit stay
read-only. Prior8/8 ceiling is not inflated; V2.3/memoization remain parked.

## Implemented DATA contract, not native authority

New final Source_Policy_Save_Contract is definition-only in the loader, after
Source_Policy. No production factory constructs or delegates to it. It contains
no store, injected writer/callback, SQL, Options API fallback or native session.
It cannot select a live writer based on function_exists. The existing fixture
normalizer is always constructed with an explicit primitive map, so its option-
read branch is not selected. Genuine WordPress sanitizer filters are not
qualified by isolated shims; their arbitrary external side effects are not
claimed absent if trusted code later invokes this model inside WordPress.

- observe() checks literal presence, scalar metadata and primitive two-level,
  reference-free map shape before serialization/normalization. Objects/resources/
  reference graphs reject without invoking magic methods. Exact bytes must equal
  serialize(map), or absence must carry empty bytes/map/autoload. No deserialization
  is performed; the narrow PHPCS serialization suppression documents these checks.
- Private scalar/array state freezes the observation. A fixed domain and length-
  framed owner/presence/serialized/autoload values produce a lowercase64-hex
  consistency revision. Exact matches() also compares bytes and effective view.
  Same effective view does not erase raw-byte, owner or autoload differences.
  Missing/present-empty differ; M→N→M still matches, not a durable generation.
- plan() validates the expected revision before no-op, retains current source/
  mode/site normalization and alias merge behavior, preserves normalized non-target
  policies, and produces update/insert/noop DATA. Stale or malformed intent yields
  WP_Error rather than rebasing. It does not mutate memory or storage by itself.
- classify_result() distinguishes exact modeled0/1/false, unavailable/unknown,
  impossible counts and literal postcondition completion. Noop can be unchanged;
  conditional0 is conflict, definite error is storage_error and unconfirmed
  completion is write_uncertain. A native adapter must classify its actual
  acknowledgement first: raw wpdb false is not automatically definite non-commit.

All model owners/observations/plans/results are caller-constructible DATA, NOT
authenticated provenance, write-capable handles, storage receipts or grants.
Internal plans carry candidate policy maps and must never be exported as public
results. Native integration must construct/qualify its own fixed observation and
atomic predicate; it cannot adopt synthetic arrays or call classify_result(1)
as proof of a commit. The existing admin boolean entry rejects these result arrays.

## Actual guarded handler result mapping

The private source-policy action still calls the original boolean setter only
after its unchanged nonce/site-ID guard. It now explicitly handles WP_Error or
unknown types before assessing success, accepting ONLY literal true. Literal
false and true preserve their exact original result arrays/audit outcomes.
Known conflict/unavailable/invalid/storage_error/write_uncertain codes get fixed
translated messages; unknown errors/types become source_policy_save_failed.
No collaborator message, error data, raw map, revision or handle is reflected.

Conflict explains not saved and refresh/review; uncertainty says it may have
been saved and requires inspection before another change. Errors/unknown values
cannot produce success notices or succeeded audit outcomes. Audit failed means
the request did not complete successfully, not proof that an uncertain write did
not occur. No automatic retry, success-hook replay or compensation is introduced.

IMPORTANT: class-source-policy.php, class-plugin.php and the existing policy form
are byte-unchanged. Their pre-existing native behavior is not disabled, replaced
or invoked by this task. The new conditional native writer remains nonexistent/
disconnected; this foundation does NOT fix the live whole-option lost-update
race. Its scope is DATA semantics and safe result handling at the existing seam.

## Non-vacuous regression and semantic controls

Actual pre-fix handler run:14 tests/16 assertions,12 failures, no errors. Injected
WP_Error and truthy/unknown values failed required rejection; two literal-boolean
positive/negative controls already passed. No current production setter is
alleged to return WP_Error; this qualifies the result boundary before a new
conditional entry can do so, not an observed native failure incident.

Final new76 tests/234 assertions, no failures/skips:

- Handler14: six known/unknown error cases, six unexpected values (including the
  model's saved-array shape), literal true/false controls, exact one-call args,
  no private-message/data leakage and non-success audit outcomes. Conflict and
  uncertain copy are distinguished. Existing nonce/compound-ID regressions remain.
- Observation15: owner/presence/autoload/raw-byte binding, primitive immutability,
  field framing, malformed data, object magic-method tripwires, references/resource,
  JSON output with no public private-state fields and explicit ABA limitation.
- Plan16: positive update/noop/insert, alias normalization, non-target preservation,
  stale same-/different-site and first-creator interleavings, stale already-
  satisfied intent, invalid commands and fresh deliberate retries. RAM apply
  calls/mutations are counted; no automatic retry or upsert adoption.
- Outcome31: exact acknowledgement matrix, definite failure with no modeled
  mutation, and two committed-in-RAM but unconfirmed finishes. Uncertainty is not
  fabricated saved and stale intent cannot silently adopt that new state.

Two deliberate model controls were assertion-killed, then restored byte-exact:
comparison bypass ->4 tests/12 assertions/four failures; zero-row-as-saved ->
28 tests/51 assertions/two failures. Before/after model SHA256:
`9714AD82324DA32B7DA8A9D8A2667C66F6776B20ADC229292DC4B92E5EED8342`.
Final new76/234 passed after both restores. This is semantic RAM-model evidence,
NOT actual SQL CAS/unique-index/transaction, core-hook/cache or concurrency proof.

Related randomized238/569 (seed20261027) and full local1072/4527 pass; full suite
has the same two baseline missing-Sodium signing skips and no new skips/failures.
Node16/16, PHPCS170 configured runtime/manual files, structure137 runtime/205
support/166 test files, build, strict Composer manifest and both dependency audits
pass; zero advisories, assets unchanged. Initial new-model property docblocks
and primitive-serialization warning were resolved without weakening tests/rules.
PHPCS excludes tests; PHP suites execute them. All eight changed-PHP syntax
checks and final diff/native-disconnection/catalog checks pass. No installed-site test.

Commands: `php vendor/bin/phpunit --filter 'SourcePolicySave|SourcePolicyHandlerResult'
--do-not-cache-result`; related filter also includes SourcePolicyMemoizationDesign
and AdminPageCompoundRequestId with --order-by=random --random-order-seed=20261027;
full `php vendor/bin/phpunit --do-not-cache-result`; `npm run test:admin`, `lint`,
`check:structure`, `build`; `php composer.phar validate --strict`, Composer audit,
`npm audit --audit-level=moderate`; scoped syntax/diff/catalog verification.

## Source-scoped review checkpoint

Boundary: immutable baseline above to this foundation, eight changed/new PHP
files (loader, model, handler, four test classes, RAM support), directly affected
unchanged store/form/notice shell and owning records/catalog. Not whole-plugin
release readiness or permission to begin a native proof.

Feature Light: bounded DATA/admin-result risk; no further significant quality
issue after routine lint fixes. One cohesive model, no generic persistence
framework, class renaming or unnecessary file splitting; all limits pass.
Normalization is deliberately reused, not replaced by a raw direct-site lookup.
Bloat/structure escalation unnecessary. Existing production write/retry/cache
contracts are not silently upgraded; unresolved native compatibility stays gated.

Feature UI/UX Implementation: source-only error notices/copy, because this slice
changes handler feedback (not UI work for the dormant backend model). Existing
escaped dismissible notice-error/role=alert/assertive renderer is retained;
generic messages contain what happened and specific refresh/review guidance,
with explicit uncertainty. No new field, button, layout, async flow or discarded
visible user input; those sections are unchanged/not applicable. No browser or
screen-reader acceptance. No further scoped design-system issue found.

Final Feature Security: no further issue found in this scope. Native authority
is not manufactured by DATA factories; unknown/model results are not accepted
as admin success. Capability/nonce/ID ordering, normalizer/legacy option identity
and late escaped output remain. Reference/object rejection precedes serialization;
there is no unserialize, SQL, input-derived file path, native callback/adapter
or raw error logging. Permanent actual-handler and DATA regressions protect the
changed behavior; whole07A/final13 remain pending, not credited by this checkpoint.

Source-only WP-CLI2.12.0 with owned global config, --skip-packages, verified
config-free temp working directory and build/docs/scripts exclusions generated
four new message blocks. Only these are projected; existing source references
remain correct, unrelated catalog order/content/multiplicity/drift preserved.
No WordPress bootstrap or whole09 completion. The four complete blocks match
qualified extractor output; all prior catalog content/order was mechanically
verified unchanged after line-ending normalization. Exact owned config/POT removed.

Publication: implementation `57c9abbf42f2c6d1e0fbde7c34309619f3c155ef` passed
[exact-head CI40572272235](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40572272235).
Actual job logs: PHP7.4/8.3 each1072 tests/4534 assertions, no failures/skips;
Node16/16 and all configured gates pass, zero Composer/npm advisories.
These hosted isolated suites do not qualify native WordPress option writes.

## Implementation-plan progress and next gate

Approved in-memory foundation and safe handler boundary implemented/pushed/CI-verified;
whole-option persistence still requires genuine qualification/integration.
Whole07/07A/13/FULL remain incomplete; no new autonomous batch count, native
acceptance or toolkit checklist refresh. Earlier documented limits stay intact.

Recommended next gated slice: **Plugin Tester local-only Source-Policy Native
Option/Hook/Cache Feasibility and Owned Conditional-Write Proof**, audit first.
Require fresh exact target/owned-option confirmation and separate authority for
owned fixture writes/cleanup. Qualify strict first-create and byte-conditional
updates, acknowledgements, hooks/cache failure and bounded contenders; stop on
unsupported semantics. Preserve actual application options/users/cron and keep
production/form wiring disconnected. Do not deploy or touch a live site.
