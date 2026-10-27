# Snapshot Freshness Timestamp Validation Safety Foundation

## Authority and source boundary — 2026-10-27

The user approved the repository-only Review07 timestamp-validation slice through
validation, commit, push and exact-head CI. Clean/synced baseline:
`0b39a28e10e6795a44de9b0c82e8c650b26649c1` (immutable source restore point).
R2 bounded DATA classification only. No storage/schema, scheduling, permission,
credential, transport, production wiring, client/site, native experiment, package,
version, tag, release or deployment change. Uploader and toolkit are unchanged.

## Scenario and proportionate handling

Review07 Phase1 identified a Medium freshness boundary: the classifier's first
parseable site/snapshot timestamp won, relative expressions were accepted by
strtotime(), and a future date had a negative age and therefore looked fresh.
This is source/isolated evidence, not an observed live incident. Likelihood is
rare under ordinary server-generated dates, but realistic under corrupt stored
data or clock rollback. Strategy: detect and classify as not_reporting, without
rewriting stored data, changing the one-hour threshold or adding a clock service.

Only `trait-status-classifier-helpers.php` changes at runtime. Its private
timestamp() is used only by this classifier's is_stale(); diagnostics and backup-
source date parsers are separate and unchanged. Direct consumers remain the
existing Poller, admin status context and Diagnostics classifier callers.

The closed freshness contract is:

- Every supplied site last_seen_at and snapshot observed_at/captured_at must be
  valid, no later than the supplied/default now, and at most3600 seconds old.
  A fresh site value cannot mask a stale/invalid/future snapshot, or vice versa.
- Null/empty fields may use the other timestamp. Both missing is not_reporting.
  Legacy captured_at is used only when observed_at is absent/null, preserving
  existing precedence; a bad observed_at is not rescued by a good captured_at.
- Positive integer Unix seconds and bounded decimal integer strings are accepted;
  strings must fit the platform integer range. Floats, exponents, booleans,
  compound values, relative dates, date-only strings and trailing data are not.
- Complete SQL/ISO calendar values use an anchored grammar, checkdate(), bounded
  time/offset fields and at most six fractional digits. Input is bounded to32
  bytes before regex/date parsing. SQL/no-offset values are explicitly UTC,
  independent of PHP's default timezone. Exceptions/range errors return invalid.
- Resolution remains whole Unix seconds; fractions are parsed, then truncated
  by getTimestamp(). There is no subsecond freshness or clock-skew guarantee.
  Any future whole second fails closed; no unapproved grace period is invented.

Valid fresh evidence still reaches working; stale/invalid evidence reuses the
existing not_reporting category/message. Pending/paused/schema/empty-payload
guards retain their ordering. No new UI strings, markup, assets or route/API
contracts. Tightened handling of corrupt/relative/future freshness DATA is
intentional; healthy SQL/ISO/Unix evidence and the exact3600-second boundary
retain positive controls. No persistent cache or source-policy work reopened.

## Isolated regression evidence and limits

New `StatusClassifierFreshnessTimestampTest` invokes the actual classifier with
healthy payloads and fixed clocks, not an unavailable-only substitute. Before
the runtime change, its first54 cases produced40 assertion failures plus one
PHP8.5 oversized float-string conversion error (54 tests/55 assertions). Failures
included fresh-site masking, future/relative values and timezone interpretation.

Final55 tests/58 assertions pass. Cases cover both timestamp positions, fresh/
stale/exact-threshold/future boundaries, SQL/ISO/offset/fractional/Unix positives,
missing/legacy precedence, bad types/length/time/date values and two non-UTC PHP
default timezones (restored in finally). An added calendar pair places
2023-02-31 at PHP's normalized March3 "now", contrasted with valid March3;
removing calendar validation would make that invalid value look healthy.
Future, all-candidate, calendar and UTC checks have distinct positive/negative
oracles. No production guard was mutated and no mutation-kill result is claimed.

Final local full495 tests/2995 assertions with the same two known missing-Sodium
skips; new55/58 and randomized related107/255 (seed20261027) pass without skips.
Node16/16 with zero failures/skips; PHPCS169 files, PHP syntax, structure, build,
strict Composer validation and npm/Composer audits pass (zero vulnerabilities/
advisories). Build output unchanged. Test files remain excluded from configured
PHPCS; syntax/execution, not a false whole-test lint claim, cover the new test.

These are isolated PHP/API-shim tests. No genuine WordPress authentication,
options, SQL, fixture users/tables, cron, native transactions, browser/site/load
or production clock experiment ran. Current source remains0.1.77, schema8.
Published implementation d32ec1522656a59aaa609aaf148b78f66767a099 passed
[exact-head CI40551305243](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40551305243):
PHP7.4/8.3 each495 tests/3002 assertions with no CI skips/failures, Node16/16
per job with zero skips/failures, and all configured validation/build/lint/audit
gates. Actual counts retrieved from both job logs; zero advisories/vulnerabilities.
Remote master matched the clean implementation head. This record-only closeout
does not change tested source or rerun native/WordPress operations.

## Focused reviews and remaining gates

Canonical Feature Light and final Feature Security reviews cover the changed
helper/test plus the classifier and direct callers. No additional defect found:
bounded type/length/calendar checks, no new IO, secret/output or authority path,
unchanged authorization/lifecycle guards, storage and wiring. Bloat/structure
escalation is unnecessary:
one cohesive private helper, runtime/test files within existing limits, no new
abstraction or split. UI/UX workflow is not triggered by this backend-only DATA
change; generated assets and user-facing strings are unchanged.

This resolves only the approved timestamp finding, not whole07/07A/13/FULL or
release readiness. The other Phase1 concerns remain unapproved: failed site-list
read versus genuine empty state; advisory poll lock ownership; concurrent whole-
option updates; compound request-ID coercion; duplicate pending-origin admission;
permissive malformed payload scalars. Logging and locks already have documented
best-effort/advisory limits, not newly promised durability/atomicity.

Recommended next gated slice: repository-only **Failed Site-List Read Availability
Safety Foundation**, preserving valid-empty/populated output, storage/schema,
authorization and wiring while surfacing unavailable reads without SQL details.
Proceed only with that separate approval.07A follows production-side07
dispositions; no site/native/concurrency/database proof is authorized here.
