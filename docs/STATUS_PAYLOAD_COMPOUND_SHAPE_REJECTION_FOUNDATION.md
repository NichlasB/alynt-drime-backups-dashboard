# Top-Level Status Payload Compound-Shape Rejection Safety Foundation

## Scope and authority — 2026-10-27

Separately approved repository-only R3 implementation of the21-slot contract in
[the disposition design](REVIEW07_CONCURRENCY_PAYLOAD_DISPOSITION_DESIGN.md).
Clean/synced dashboard master baseline and immutable source restore point:
`dc4db7c74958602cee0318dd4a08dfd22fa4d5a6`. Version0.1.77/schema8 unchanged.
Only the validator/common sanitizer trait, isolated regressions, scoped generated
POT content and owning records change. Uploader/toolkit remain read-only.

No actual WordPress/authentication, SQL, database/user/option/cron fixture,
credential, HTTP, native or concurrency operation. No production-wiring,
storage/schema, endpoint, execution, activation/rollback, dependency, asset,
version/package/release/tag/deploy change. A synthetic php://memory resource is
owned/closed inside one test; no filesystem/database resource is opened there.
No broadening of the autonomous R2 cap or deferred V2.3 authorization.

## Finding, handling and compatibility

Review07 Low: compound values in scalar health fields could reach integer/
boolean/string coercion, misrepresent status, warn or invoke direct-call object
conversion. No live incident, authentication bypass or execution grant is alleged.
JSON objects arrive as arrays under the existing transport's associative decode;
the boundary therefore cannot reject only PHP objects.

One private helper checks isset/non-null values with is_scalar against fixed
field-name lists. It does not sanitize, stringify, traverse or invoke methods on
rejected values. The existing recursive forbidden-key scan still runs first;
transport body/depth limits and the scan itself are unchanged, not requalified.

1. Unsupported schema shapes fail before absint with schema_unsupported.
2. Unsupported UUID shapes become the existing empty-invalid UUID path before
   string conversion, returning site_uuid_mismatch. Unsupported schema retains
   precedence over malformed UUID/later fields.
3. After valid schema and UUID, the other19 scalar fields are checked before
   their existing conversions. Arrays, objects and resources return payload_invalid
   with one generic translated message, no field contents or raw exception text.
   Capability/structured-extension delegation is not reached after rejection.
4. Scalar fields remain the designed21: schema/UUID/version; four counts, three
   timestamps, eight booleans and cron_status/cron_reason/last_runner strings.
   Missing/null defaults, scalar string/int/float/bool conversions, existing
   negative-count absint behavior and truthy nonempty string "false" are preserved.
   This is shape rejection, not a strict scalar grammar or enum/schema migration.
5. Unknown extension omission and all existing warnings, backup_sources,
   restore_readiness, remote_actions and cadence_authority handlers remain intact.
   No nested parser is tightened and no optional evidence grants execution.
   Malformed payloads follow the original poll-failure route, not healthy
   classification/snapshot/success/reconciliation. Storage failures and retry
   semantics of that route are unchanged, not newly qualified.

Direct-call non-JSON scalar extremes and nested-field conversion behavior are
not broadly redefined. Valid serialized observations retain their prior shape;
previously accepted malformed compound responses intentionally become errors.
Repository producer/consumer compatibility is not a deployed-client inventory.

## Non-vacuous validation

Initial unsupported PHPUnit --no-progress attempt stopped before running tests
and is not credited. Actual330-case red run against unchanged runtime:499
assertions,88 failures and42 conversion errors. Failures include capability
delegation tripwires, object-conversion tripwires and poller success rather than
required rejection. Some existing empty/invalid envelope cases already failed
closed. No semantic-mutation experiment is claimed; the pre-fix run establishes
that omitting the new shape boundary does not satisfy the regression contracts.

Final new331 tests/878 assertions, no failures/skips:

- 126 shape cases:21 fields x empty/nonempty arrays, associative JSON object,
  nested JSON object, direct stdClass and throwing stringable object. All assert
  the required WP_Error before capability delegation; PHP warnings/conversion
  failures cannot masquerade as correct rejection.
- One owned in-memory resource case iterates all21 slots with finally cleanup;
  three further controls preserve forbidden-key/schema/UUID error precedence.
- 152 scalar positive cases assert independently specified normalization outputs,
  including numeric strings/floats/negative counts and legacy boolean semantics;
  42 missing/null cases assert the original defaults or required-envelope errors.
- Exact baseline projection/unknown-structure omission and a combined positive
  retain all five supported structured extensions; dormant authority execution
  flag stays false. Existing richer extension suites remain unchanged.
- Four synthetic wire/poller rejection cases (schema/count/boolean/text) assert
  one fake HTTP call, failure metadata and zero successful snapshot, site-success
  or reconciliation calls. One successful-wire control still records/reconciles
  once with the exact prior payload. These are actual validator/transport/poller
  methods with fake DNS/HTTP/storage, not genuine endpoint, SQL, authentication,
  concurrency or remote-action acceptance.

Related randomized409/1095 (seed20261027) pass without skips; full local PHP8.5.1
996 tests/4293 assertions, two unchanged missing-Sodium signing skips, no failures.
Node16/16; PHPCS169 configured runtime/manual files, structure (136 runtime,
204 support,162 test files), build, strict Composer manifest and both dependency
audits pass with zero advisories. PHPCS excludes tests; PHP suites execute them.
An initial multiline-array style failure was repaired without changing behavior.
Assets unchanged. No new skip, reduced threshold or weakened existing assertion.

Commands: targeted three-class PHPUnit filter and full
`php vendor/bin/phpunit --do-not-cache-result`; related
`--filter 'StatusPayload|Poller|CadenceAuthorityEvidence' --order-by=random
--random-order-seed=20261027`; `npm run test:admin`, `lint`, `check:structure`,
`build`; `php composer.phar validate --strict`, `php composer.phar audit`,
`npm audit --audit-level=moderate`; changed-PHP syntax and `git diff --check`.

## Source-scoped review and translation evidence

Explicit review boundary: baseline above to this foundation, seven changed/new
PHP files (two runtime, three test classes, two support files), directly affected
poller status-check caller, existing extension/nonce-free payload fixtures and
the scoped catalog/owning records. Not a whole-plugin release review.

Feature Light: approved remote-input shape validation, no endpoint/state/storage
change. No further significant non-security issue found after lint repair.
Single bounded helper/list follows existing architecture, early returns and
WP_Error handling. No new runtime class/file/loader or meaningful bloat/splitting
escalation; all structure limits pass. A scalar field omission is exercised by
the independent per-field rejection matrix, not a test that reuses the runtime list.
No broad defensive refactor, resource-limit redesign or test-suite restructuring.

Backend-only change: no UI/UX workflow, browser, forms, JS or markup work. The
new support-safe translated error uses the existing error/log/notice path;
no raw values, SQL, paths, tokens or stack traces are emitted. No accessibility,
installed WordPress or screen-reader acceptance is claimed.

Final Feature Security: no further defect found in this scope. Fixed scalar
field lists and short-circuit checks precede coercion; forbidden-field and
enrollment UUID checks retain precedence and meaning. Expected identity/key,
authentication, nonces/capabilities, extension authorization, transport/SQL/file
boundaries are unchanged, not bypassed by this helper. No dangerous evaluation,
new logs, input-derived paths or sensitive output. Confirmed Low shape finding
now has permanent isolated actual-method regression coverage; whole07A and final13
remain pending. No claim of genuine auth/storage or native proof from doubles.

Source-only WP-CLI2.12.0 extraction used owned global config, --skip-packages and
a verified config-free temporary working directory. Initial include-only output
also found historical build copies; it was rejected, not projected. Qualified
repeat explicitly excluded build/docs/scripts and contained only current-source
references. Projection adds the one generated message and refreshes four affected
existing reference blocks, preserving prior message content/order/multiplicity
and unrelated catalog drift. Non-reference preservation plus exactly one message
was mechanically verified. Owned config/POT removed; changed-PHP syntax and final
diff/scope checks pass. Whole09 is not refreshed; no WordPress bootstrap.

Publication: exact-head CI pending. Local checks do not establish CI completion.

## Implementation-plan progress and next gate

Fourth separately approved Review07 finding handled, not an inflated autonomous
task batch. Canonical07 still has an unresolved source-policy save/conflict
decision; advisory polling locks, best-effort logs and concurrent pending-origin
admission retain their documented limits/dispositions. Whole07/07A/13/FULL remain
incomplete; toolkit untouched. No release/package or native acceptance inferred.

Recommended next gated slice: repository-only **Source-Policy Concurrent Save and
Conflict Contract Design**, design-only. Settle cross-site preservation, same-site
conflict, missing/no-op option behavior, option filters/hooks/cache coherence and
bounded retry/failure semantics before selecting a persistence mechanism. Keep
SQL/option writes, schema, actual WordPress/native execution, production wiring
and all site state unchanged. Do not implement raw-SQL CAS or reopen V2.3 here.
