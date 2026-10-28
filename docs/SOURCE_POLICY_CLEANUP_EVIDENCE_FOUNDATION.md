# Terminal Qualification Revocation and Per-Case Cleanup Evidence Foundation

## Authority and baseline — 2026-10-28

Approved repository-only terminal qualification repair, then completion of the
previously blocked [cleanup foundation](SOURCE_POLICY_FAILED_CONNECT_CLEANUP_EVIDENCE_DESIGN.md).
Clean/synced restore point: d156b104a3797b67a54d9f58814119ccddd6573a.
R2 bounded terminal-DATA repair plus dormant/test-only contracts; no new native
authority. Version0.1.77/schema8/PHP7.4/WordPress6.0 remain unchanged.

The previous approval excluded runtime changes. Its real positive regression
exposed retained qualification DATA after close and stopped publication. Subsequent
explicit approval authorizes the one-line repair below; the earlier design's
unchanged-close statement is a historical scope record, not this newly approved
delta. Resume the partial work rather than substituting unavailable-only tests.

No WordPress/LocalWP/site inspection, authentication, SQL/database/fixture work,
native sessions or real hold/IPC experiment, service/credential/permission/schedule
change, production/form wiring, package/release/tag/deploy operation. Uploader and
toolkit remain untouched. No historical whole-plugin readiness row, native proof
result or cumulative product-task count is refreshed.

## Implementation and source ownership

- includes/class-source-policy-no-reconnect-connection.php: close clears private
  qualification immediately after its once-only latch, before fallible/reentrant
  cleanup. Active facts remain visible before close; terminal public/private facts
  are absent even if cleanup fails. Cached close truth, independent native/callback
  cleanup, ambient protection, dispatch and reconnect guards are unchanged.
  Previously copied scalar facts are not capabilities.
- scripts/manual/source-policy-cleanup-evidence.php: dormant strict30-field ASCII
  parser and case-specific verifier. Dirty baselines, malformed correlation/counters,
  absent release/identity/terminal/result/callback evidence and unavailable positives
  fail. Only the selected failed-connect DATA row accepts exact string
  18446744073709551615; successful/preflight after-counts stay literal0. No unsigned
  conversion, wrap-only grant or alteration of current proof assertions.
- scripts/manual/source-policy-cleanup-cohort.php: virtual-IO/clock state machine
  for seven fixed ordered cases. Unique child correlations, one terminal record,
  live all-session observation, private ACK, clean exit and separate post-exit
  observation are mandatory. Invalid/foreign/DB-local-only or DB=NULL-session
  activity DATA permanently stops the cohort. No retry, launcher or native backend.
- scripts/manual/source-policy-failed-connect-observation.php: isolated API seam,
  no shipped native implementation. Capture exact expected1049 before any invalid
  failed-object getter; retain fixed primary/cleanup stages, release the exact owned
  object once, require literaltrue ownership/release, cache scalar reports and
  redact collaborator messages. False, wrong exception/code, identity loss and
  primary/cleanup failures stay unconfirmed. The method itself refuses outside
  explicit isolation or with native extensions.

The protocol and sole observer intentionally share one cohesive manual file
(one scoped multiple-structure style exclusion). No general connector/provider,
production include, factory, handler or dependency is added. Root, existing manual
proof/acceptance, bootstrap implementation, schema/version, dependencies and CI
configuration remain unchanged from baseline.

The virtual reader rejects before appending beyond16KiB/case and128KiB/cohort.
Seven fixed cases already make the latter redundant defense-in-depth. Any nonempty
stderr stops immediately with no retained text (clipped count up to4097), stricter
than tolerating4KiB/32KiB error text. One monotonic25s virtual deadline covers the
whole cohort without per-case reset. Row elapsed DATA is bounded20s; bootstrap
commands stay capped16 with the positive round-trip separately accounted as one
post-bootstrap query/result/free.

## Tests and fixes

Three new suites use actual dormant owner bootstrap/close execution with existing
extension-free core/driver doubles and scalar/virtual pipes/clocks. The positive
reaches active setup, checked prepare, RAM string round-trip, changed-seal refusal
and close; it requires qualification absence at cleanup entry, not only afterward.
Existing bootstrap cases now assert active facts before close and absent terminal
facts on success and cleanup faults.

Fixture repairs: renamed a PHPUnit helper collision; cache seven rows only after
the complete build succeeds; register all/mode proof callbacks with keyed
add_filter rather than the shim's incompatible numeric callback constructor.
Remove only those exact fixture-owned callbacks. Shared shims/runtime registrations
are not muted or repaired to manufacture success. Expected run/source contexts are
themselves shape-validated; invalid constructor objects reject before retention or
coercion. Conditional fake exception declaration cannot shadow a real native
exception during ordinary test discovery.

Observed red-to-green: existing positive isolated bootstrap worker failed its
terminal-null assertion before repair, then passed181 checks. New focused suites
pass107 tests/331 assertions. Raw cases cover strict ownership/release booleans,
allocation/connect/cleanup stages, paired failures, reentry/repeat, getter avoidance
and redaction, without actual native IO.

Eight temporary semantic controls each exited1 through assertion failures, not
startup errors, then restored the exact changed source SHA256:

| Deliberate bypass | Assertion evidence |
|---|---|
| Retain terminal qualification | Existing positive worker rejects non-null terminal facts |
| Omit release acknowledgement | Three scalar cases fail |
| Omit source equality | Source-mismatch case fails |
| Ignore post-close active counter | Four cases fail, including successful-case wrap |
| Ignore live/post session count | Live and post foreign-session cases fail |
| Increase stdout case cap | Oversized partial-output assertion fails |
| Bypass consumed latch | Raw fixture detects repeated allocation/effects |
| Read errno after failed connect | Getter returns plausible1049 but call-count assertion fails |

Restored SHA256 hashes:

- owner: 0BD601B27F587E42AC2D621D5961DC5F7B4662CDF8FED57EFD4DDEB5A7FBB0B3
- verifier: 15E8137DDEBED213D2ECF4AD68D835AF7072A61F972CF8D1BDC97A5C160BC47A
- cohort: 7226D08213277EF223E2D90788B846332BE9E603EA3B7B2D887A4564B2A9F957
- observation: F043D022C9B1C38EA8D756EE963DA53386DD97205D8BF19A4274CA0CEB9917D5

Source-scoped review checked cleanup ordering, no repeat effects, independent
release, unchanged ambient/no-reconnect admission, strict parsing/correlation,
failed/connected counter separation, live/post/all-session ordering, bounds and
error redaction. No additional identified source issue remains; actual integration
evidence is gated below. Not a full canonical pre-release, Database or UI/UX review.

## Validation and publication

Final local PHP8.5.1 full suite:1675 tests/7397 assertions, no failures; only the
two existing missing-Sodium skips. New focused107/331 passes. Related randomized
run836/3419 (seed20261028, before the last five strict-boolean/paired-failure cases)
also passed; the final full suite includes those five. Node16/16, PHPCS200 files,
structure148 runtime/219 support/187 tests/184 class names, build, strict Composer
manifest, Composer/npm zero-advisory audits, eleven scoped PHP syntax checks,
scoped Markdown targets/diff hygiene/baseline preservation all pass. Original
positive isolated worker passes181 checks. No generated build output changed.
Exact-head CI publication closeout pending; no hosted result is claimed yet.
Commands: focused Cleanup/FailedConnectObservation PHPUnit; randomized
SourcePolicy|AdminPageCompoundRequestId seed20261028; full PHPUnit --verbose
--do-not-cache-result; npm lint/test:admin/check:structure/build; strict Composer
validate/audit; npm audit --audit-level=moderate; scoped syntax/link/diff and
baseline-preservation checks. No new skip, lowered threshold or dependency change.

## Proof limits and next gate

RAM escaping/results and projected counters are model evidence, not actual mysqlnd
accounting, genuine WordPress/storage proof, native result freeing or source
authentication. Correlated pid/source/profile DATA authenticates no process or
producer. Virtual observer facts establish no server privileges/all-session
visibility, real hold/ACK protocol, bounded OS pipe reads, process cleanup feasibility
or hard interruption of blocked synchronous IO. No native adapter/controller is
shipped or connected. Hosted PHP7.4/8.3 QA cannot qualify the pinned Windows profile
or other hosting runtimes.

Runtime source hash changed: historical native evidence does not transfer to this
head. Fresh source/profile/error prerequisites and all applicable positive/negative
native checks are required next. Historical failed-connect comparison and77-table
preservation were not rerun. Native qualification and lost-update repair stay open.

Recommended next gated slice: **Plugin Tester local-only Failed-Connect Per-Case
Controller/Live-Child Reconciliation Integration and Owned Cleanup Proof**.
Audit first; only qualified owned disposable storage/process/stdio fixtures,
failure-aware raw observation and the fixed seven-case controller. Require fresh
error/source prerequisites, live and post-exit all-server session/transaction
reconciliation including DB=NULL, exact target/fixture confirmation and cleanup.
This separately approved native slice must not change production wiring or weaken
existing acceptance, budgets or independent release evidence. Stop if the fixed
profile/hold cannot meet the envelope. No such operation is authorized here.
