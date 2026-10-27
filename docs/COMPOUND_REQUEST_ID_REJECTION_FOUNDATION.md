# Compound Request-ID Rejection Safety Foundation

## Scope and authority — 2026-10-27

Separately approved repository-only R3 foundation through validation, commit,
push and exact-head CI. Clean/synced baseline and immutable source restore point:
`5d186185b85e97a216bdfda684803829f04b0149`. Only the existing POST site-action
input boundary is changed. No actual WordPress authentication, SQL, fixtures,
users, credentials, scheduling, transport, native experiment, storage/schema,
production wiring, version/package/release/tag or deployment operation. Uploader
and toolkit are not targets. This approval does not raise the autonomous R2 cap.

## Finding and preserved contract

Review07 Medium: a nonempty compound POST `dashboard_site_id` could become1
through integer coercion and reach a protected collaborator for an unintended
record. This is not evidence of a capability/nonce bypass or a live incident.

One private `verify_site_action_request()` helper first calls the unchanged
action-specific nonce verifier, then inspects only the raw ID type. Arrays,
objects, booleans and floats return the existing translated `site_not_found`
error before traversal, unslashing, integer conversion, lookup, token generation,
polling, audit, policy or dispatch. No raw submitted value is reflected or logged.
The narrow PHPCS suppression documents why structural inspection must precede
unslashing rather than recursively processing an already unsupported value.

Eleven guard call sites cover13 existing routes: revoke, pause/resume polling,
status check, source policy, archive/unarchive, action opt-in token, backup-now,
cleanup preview, schedule preview/apply and rollback preview. The public shell's
`manage_options` gate, action-specific nonce domains, existing confirmation and
business guards remain unchanged. Accepted string/integer IDs retain their
existing `wp_unslash()`/`absint()` normalization; missing/null retain the legacy
zero path. This is not stricter canonical numeric-string validation. Non-site
forms remain nonce-only; GET IDs, action selector, nonce shape, schedule IDs and
other request fields are outside this finding's scope.

## Non-vacuous regression evidence

Initial156-case run against unchanged runtime:226 assertions,60 failures and13
object-conversion errors. Protected-collaborator tripwires were reached for
malformed IDs; other cases failed the required error contract. Some existing
empty-ID paths already rejected input. These are red-to-green regressions, not
semantic-mutation or physical-operation proof.

Final new157/314, no failures/skips:

- 78 cases:13 routes x six unsupported values (empty/nonempty/nested arrays,
  boolean, float, object), asserting generic rejection and no collaborator call.
- 39 positive controls:13 routes x string/integer/zero-padded IDs. A sentinel
  exception proves the original first delegate receives42. It does not execute
  the operation or pretend successful persistence/dispatch. Existing positive
  action tests complement these entry controls.
- 39 nonce-precedence controls: missing, expired and another real action domain
  fail with `dashboard_session_expired` before malformed-ID rejection/delegation.
- One non-site pending-creation control with an unused compound ID retains the
  original isolated fixture result and exactly one manager call.

Related handler selection randomized with seed20261027:171/399, no skips/fails.
Full local PHP8.5.1:665 tests/3415 assertions, two pre-existing missing-Sodium
signing skips, no failures. Node16/16, PHPCS169 configured runtime/manual files,
structure (136 runtime/202 support/159 test files), build, strict Composer
manifest, Composer/npm audits and diff hygiene pass; zero advisories, assets
unchanged. PHPCS configuration excludes tests; isolated tests do run them.
Commands: `php vendor/bin/phpunit --filter AdminPageCompoundRequestIdTest
--do-not-cache-result`; related six-class filter with `--order-by=random
--random-order-seed=20261027`; full `php vendor/bin/phpunit --do-not-cache-result`;
`npm run test:admin`, `lint`, `check:structure`, `build`;
`php composer.phar validate --strict`, `php composer.phar audit`,
`npm audit --audit-level=moderate`, `git diff --check`.

Source-only WP-CLI2.12.0 `i18n make-pot` with `--skip-packages`, an owned temporary
global config and verified config-free working directory extracted only the
changed common helper. Its generated reference at line127 was projected into the
existing message block. No new message, catalog reordering/deduplication or
unrelated reference refresh; historical whole09 drift remains outside scope.
Owned temporary config/POT removed. No WordPress bootstrap or translation-runtime
acceptance claimed.

## Source-scoped canonical review

Baseline-to-foundation review: eight changed PHP files (five runtime traits,
test class and two support files), plus directly affected shell, notices and
test nonce harness. Owning records and scoped catalog reference also inspected.

Feature Light: scope is admin action ingress/feedback, not new endpoints, storage
or transport; no significant further issue found or auto-fixed. Single early-
return helper avoids duplicated checks and adds no runtime abstraction/file.
No meaningful bloat/structure escalation: all enforced file limits pass.
Error, valid-ID, non-site-form and nonce-order tests protect the bounded change;
concurrency/retry semantics of downstream actions are unchanged, not newly proven.

Feature UI/UX Implementation: source-scoped notices/copy only. Existing error
renderer escapes text into a dismissible `notice-error`, `role=alert` and
assertive live region. Generic existing not-found text intentionally exposes no
submitted type/value and does not preserve/re-emit a tampered hidden record ID.
No new visible form/input/async/loading/buttons/markup/CSS/focus behavior; those
guide sections are unchanged/not applicable, not browser-qualified. No new
design-system issue found. Whole-page accessibility/browser acceptance remains
pending; this is not a whole-plugin UI review.

Final Feature Security: no further issue found in this scope. Capability gate
precedes private routing; nonce verification remains separate from authorization,
with separate domains; unsupported types cannot reach protected collaborators.
Accepted input retains existing sanitation and later business checks. No SQL,
file operation, endpoint, output surface, dangerous evaluation or secret logging
added. Regression handoff: confirmed malformed-ID finding now has permanent
isolated actual-handler coverage; whole07A still pending. Nonce/capability shims
are not genuine WordPress authentication, and sentinel entry is not a successful
credential, remote, schedule or database operation. No native evidence refreshed.

Publication verification: pending exact-head CI; do not infer completion from
local validation. Owning records will capture actual run/log counts.

## Implementation-plan progress and next gate

This resolves the approved compound-ID finding, the third separately approved
Review07 disposition after timestamp and failed-list handling. Prior autonomous
8/8 ceiling is not inflated; this is one R3-approved foundation, not eight tasks.
Whole07/07A/13/FULL remain incomplete. Advisory polling locks, whole-option
overlap, concurrent duplicate pending records and malformed status-payload scalar
contracts remain explicit concerns, not quietly repaired or physically accepted.

Recommended next gated slice: repository-only **Review07 Remaining Concurrency
and Payload-Compatibility Disposition Design**, design-only. Classify the four
remaining concerns against existing documented guarantees and choose the next
necessary fix/acceptance boundary before tightening client contracts or adding
persistence/locking complexity. Keep storage, native execution, public contracts,
production wiring and all site state unchanged. Do not reopen deferred V2.3.
