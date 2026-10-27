# Failed Site-List Read Availability Safety Foundation

## Scope and authority — 2026-10-27

User approved this repository-only Review07 slice through validation, commit,
push and exact-head CI. Clean/synced baseline and source restore point:
`2b13143626693ad114b8976359c3864528c851b7`. R2 read-result/presentation handling;
no real SQL, WordPress/site operations, fixtures, schema, write queries, cron,
credentials, authorization, remote transport, production wiring, package,
version, release, tag or deploy change. Uploader/toolkit remain untouched.

## Scenario and handling contract

Medium, occasional read-failure scenario: ARRAY_A failure can look like an empty
array. Sites previously rendered "No Client Sites Are Paired Yet", archived
records appeared absent, and Attention could report zero. This is source/isolated
evidence, not a new live incident or physical failure proof. Detect and show
unavailable, without SQL/error/secret text or a retry loop.

- Site_Repository::all() retains its array interface and original prepared SELECT,
  field projection, ordering, filters and100/500 limits. Immediately after the
  query it captures non-array result or nonempty last_error as a boolean. Failed
  non-array results normalize to[]; successful arrays retain their content.
- last_list_read_failed() describes the most recent all() call on that instance,
  not every database read. Unrelated wpdb operations cannot overwrite it. Each
  new all() call replaces the flag; callers inspect it before another list read.
- Visible/archived admin contexts cache their own available flag. Failed lists
  suppress downstream snapshot queries, zero metrics and successful empty
  guidance. Unavailable Attention count is null, with no navigation badge.
  Successful empty lists retain original guidance and a genuine zero badge.
- Failed archived counts do not hide valid visible rows or corrupt their cached
  Attention count: their link omits the number and explains unavailability.
  Successful archived views reuse the loaded count. No additional query/retry
  loop; a fresh page/context can recover.
- Persistent inline error content uses the existing section structure, escaped
  translatable heading/body and role=alert, with reload guidance. It is a section
  read state, not a dismissible action outcome. No JS/new action/forms/CSS or
  permission change; existing positive row rendering remains intact.

This is not a dashboard-wide database-health API. Diagnostics/support consumers
that directly use the legacy array result, snapshot/action/count read failures,
and arbitrary corrupt row shapes are not newly availability-qualified. Internal
context counters are meaningful only when available=true. No new authorization,
durability, atomicity, SQL-parser or native error-handling proof is claimed.

## Validation and focused review

Initial tests lacked the isolated wp_parse_args shim: five errors/zero assertions,
not credited. After adding an array-only adapter, unchanged runtime failed four
meaningful assertions in five tests/eight assertions: visible, archived and
Attention false-empty output, plus cached failure becoming zero. Populated render
controls then exposed missing unrelated row helpers; only an explicit allowlist
of those hints/forms is stubbed, and unexpected dependencies throw. No runtime
success path or existing assertion was weakened.

Final new13 tests/106 assertions use the actual repository/query templates and
Sites/Attention/navigation traits with a socket-free wpdb collaborator. They
cover successful empty/populated,[]+error,null/false and populated+error, captured
flags despite unrelated errors, same-instance recovery, independent cached views,
no downstream snapshot reads after failure, unknown counts, genuine zero/positive
counts and new-request recovery. Targeted19/125 and related randomized25/154
(seed20261027) pass without skips. API-shim/render tests, not genuine wpdb,
authentication, browser, installed-site or physical database proof. Classifier
output and unrelated row widgets remain explicit collaborators, not acceptance.

Full local508/3101 with the same two known missing-Sodium skips; Node16/16 with
no failures/skips, PHPCS169 runtime/manual files, structure/build, strict Composer
manifest and both dependency audits pass; zero advisories, assets unchanged.
Configured PHPCS excludes tests. Source-scoped canonical Feature Light, UI/UX and
final Feature Security found no further defect: contextual text/heading/alert/
reload guidance, no color-only state, no raw error output or new write/authority
path. No browser/screen-reader acceptance. No bloat/splitting escalation needed.

Source-only bundled WP-CLI2.12.0 make-pot ran from a verified config-free temp
directory with owned global config and --skip-packages; no WordPress bootstrap.
Initial unsupported --config failed before extraction. Qualified extraction
excluded build/docs/scripts. Guarded mechanical projection added only four
introduced messages and refreshed20 affected reference blocks, preserving all831
original blocks/order/non-location content and their two duplicate identities;
835 final blocks. Unrelated missing-message/duplicate drift is not silently fixed
or marked whole09 Complete. Both exact owned temporary config/projection files
were removed. Source extraction is not language/runtime acceptance.

Publication/exact-head CI pending; local QA is not a substitute for CI.

## Implementation-plan progress and next gate

Only this approved failed-list finding is resolved after the timestamp foundation.
Whole07/07A/13/FULL remain incomplete; prior8/8 ceiling is not inflated by this
separately approved slice. Advisory locks, whole-option overlap, compound request
IDs, concurrent duplicate pending records and payload scalar contracts remain
unapproved. Recommended next gated slice: repository-only **Compound Request-ID
Rejection Safety Foundation**, with isolated authorized-handler tests rejecting
compound IDs before repository calls while preserving valid IDs, capability/nonce
checks, storage, scheduling, production wiring and all site state.
