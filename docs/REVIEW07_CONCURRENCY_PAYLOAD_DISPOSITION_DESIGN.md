# Review07 Remaining Concurrency and Payload-Compatibility Disposition Design

## Authority, baseline and non-goals — 2026-10-27

User approved this repository-only design/disposition through validation, commit,
push and exact-head CI. Dashboard master baseline/restore point:
`cf72f039f7f48535c729735b6358252ded7306ab`, clean and matched remote master.
Version0.1.77, schema8. Companion source inspected read-only at uploader
`3dfcb15b5cab8191df7d984251986ead140ded52`; toolkit remains unchanged.

Only this record and the owning implementation/review records change. No runtime,
tests, source-identity manifests, protocol version, assets, dependencies, storage,
locks, credentials, pairing, scheduling, SQL, WordPress bootstrap/authentication,
fixtures, native/HTTP experiment, production wiring, package/release/tag/deploy.
R1 document publication about R3 boundaries; no R3 implementation authority is
inferred. Prior autonomous8/8 ceiling stays8/8; this is a separately approved
design, not another batch or product-feature completion. Deferred V2.3 stays parked.

## Review scope and decision summary

This applies canonical07's likelihood/impact/strategy and regression-handoff
criteria to the four still-open Phase1 concerns. It is not a new whole-plugin
Phase1 audit, production-side fix, complete07/07A review or release-readiness signoff.
Three earlier findings have separately approved foundations: timestamp validity,
failed-list availability and compound POST IDs. Historical review rows stay dated.

| Concern | Current severity / likelihood | Design disposition | Residual status |
|---|---|---|---|
| Poll lock admission/release ownership | Medium / rare overlap, load-dependent | Retain documented advisory semantics; defer stronger locking | Not fixed; no exactly-once/ownership guarantee |
| Whole-option policy/log overlap | Medium / occasional with overlapping admins/workers | Split policies from best-effort logs: policy conflict handling remains required design work; retain bounded log limitation | Policy loss unresolved; logs not a durable ledger |
| Duplicate active pending origins | Low / rare concurrent submission | Retain serial guard, expiry and local recovery; defer serialization/schema change | Not concurrent uniqueness or pairing acceptance |
| Malformed top-level payload scalars | Low / occasional malformed client response | Prevent compound-to-scalar conversion in a narrow next foundation; preserve legacy scalar compatibility | Implementation and acceptance still gated |

Likelihood is qualitative source-derived assessment, not measured incident
frequency. The schedules below are hypothetical interleavings, not concurrent
test results. Retaining an existing limit is not proof that it cannot occur, a
new security guarantee, or unconditional approval to release with it. The policy
decision remains explicitly unresolved; review07 cannot silently close around it.

## 1. Advisory polling locks — retain documented limitation

Source: [lock helpers](../includes/traits/trait-poller-locks.php), acquire/release
at24/41/53/73; [coordinator](../includes/class-poller.php), TTL600seconds at31,
batch/check entry134/188; [existing database-review limit](DATABASE_REVIEW_SOURCE_RECONCILIATION.md).

Trigger: A and B both read no transient before either sets it, so both can enter.
After expiry or replacement, A's unconditional delete can remove B's newer lock.
Timestamp values are not ownership tokens; missing transient APIs admit work.
Global cron and per-site manual/cron coordination are separate advisory layers.
TTL does not bound total batch duration;10-second per-request HTTP timeout and
bounded batches do not constitute a global concurrency limit.

Consequence: duplicate read-only HTTP/status observations, extra local snapshots,
last-completer retry/status metadata, misleading "already running" assumptions.
No client backup/settings/Drime mutation is introduced by polling. The separately
qualified revocation-safe final site write remains independent; it does not make
snapshot-plus-site completion atomic or promise chronological completion order.

Strategy: Accept the already documented advisory property for current design;
do not sell it as atomic overload prevention. Defer replacing it with a durable
mutex/lease without an independently justified requirement. A token alone fixes
neither atomic acquisition nor atomic owner-checked release. A future design
must settle cache vs database ownership, expiry/restart behavior, cache coherence,
bounded admission and release, compatibility, and actual failure/contender proof.

Regression handoff: current scheduled-poll tests protect batch limits and serial
success; transient shims use process-local arrays and discard TTL. They cannot
prove contention, expiry or object-cache semantics.07A candidate: isolated
interleaving/owner-expiry characterization without claiming an exclusive lease.
Any stronger guarantee needs separately approved WordPress/database/cache and
two-worker proof, bounded resources and owned cleanup; none runs here.

## 2. Whole-option overlap — policy is not merely a log

Source: [source policy](../includes/class-source-policy.php), set90, all138,
whole-map update130; [event storage](../includes/diagnostics/traits/trait-event-log-storage.php),
diagnostic append39/update50, audit append65/update75 and per-instance read caches.

Trigger: A and B read mapM. A changes site7, B changes site8; A writesM+A then B
writesM+B, losing A despite different records. Repeating read/merge/write or
checking a posted revision before ordinary update_option still has a race.
Log instances similarly append/filter/cap their own snapshots and replace the
whole option; overlapping append or clear/append can lose or reintroduce events.

Policy consequence: an operator's saved required/optional mode can be undone.
Losing a change back to required can leave optional classification in effect;
this is not a harmless display-only last-write conflict. It does not grant
client execution, but can hide a monitoring expectation. Serial sanitizer and
fresh-read behavior do not provide persistence isolation.

Policy strategy: do NOT accept silently as "best effort" and do not mark fixed.
A follow-on R3 save/conflict contract must preserve unrelated-site updates or
return an explicit retryable conflict without pretending success. Same-record
overlap needs an explicit winner/conflict policy. Preserve existing option name,
required default, alias normalization and non-autoload behavior unless a separate
migration is approved. Do not select raw-SQL CAS here: serialized values, genuine
WordPress option filters, missing-option creation, object-cache invalidation,
hooks, no-op writes, retry bounds and physical affected-row semantics must be
settled first. Per-site options would require migration; an advisory transient
around read/write would not establish the desired property.

Logging strategy: retain bounded best-effort operational/audit history, not a
durable or append-only security ledger. Existing redaction, retention, option
separation and failure returns stay intact. Do not couple this to policy locking
or invent a new log table/transaction solely for review closure. Durable audit
requirements would reopen an explicit product/storage decision.

Regression handoff: existing policy normalization/classifier and event retention/
clear/separate-audit tests remain useful serial controls, not lost-update proof.
Policy conflict behavior is a07A candidate after contract approval: distinguish
same-key conflict, different-key preservation, missing option, no-op and storage
failure, with positive successful-save controls. In-memory callback ordering can
test a contract but not genuine option/cache concurrency. Native proof separately
gated; no option write, cache flush or SQL runs here.

## 3. Concurrent duplicate pending origin — defer serialization

Source: [enrollment manager](../includes/class-enrollment-manager.php), active
pending pre-read91 then token construction/insert97–106; [repository lookup](../includes/traits/trait-site-repository-reads.php),
get_active_pending_by_expected_origin198; [schema](../includes/class-storage.php),
unique public_id/site_uuid107–108, nonunique origin/pending indexes109–110.

Trigger: two separately authorized requests both observe no non-expired pending
record and insert different public IDs for the same normalized origin. Schema
uniqueness of public ID does not enforce active-origin uniqueness. NULL pending
site_uuid and later enrollment identity checks are different boundaries.

Consequence: multiple display-once tokens/local pending rows and confusing
operator choice. No evidence here of credential bypass or two successful genuine
enrollments; do not infer either safety or exploitation from this pre-read race.

Strategy: retain serial duplicate rejection,15-minute token lifetime and explicit
local revoke/expiry recovery. Defer atomic pending-origin admission: adding a
blanket unique origin index would affect historical/revoked/expired rows and
valid future re-pairing. A time-sensitive "one active pending" rule cannot be
implemented honestly by a plain permanent UNIQUE index. No credential reuse,
automatic token recovery, overwrite or duplicate cleanup is authorized.

Regression handoff: EnrollmentManagerTest already covers serial duplicate reject,
positive token/verifier-only storage and insert failure. It is not concurrent
admission proof.07A should preserve these controls and label the missing race
guarantee. Future serialization requires explicit enrollment/credential/schema
design approval and exact owned-fixture concurrency/cleanup authority.

## 4. Payload compatibility — selected next bounded implementation

Source: [validator](../includes/class-status-payload-validator.php), validate94,
schema99, UUID103, allowlist109; [scalar helpers](../includes/traits/trait-status-payload-validator-sanitizers.php),
non_negative_int/bool_field and string casts. [Transport](../includes/class-safe-transport.php)
limits body to1MiB and json_decode depth64, with array decoding. JSON objects in
scalar slots therefore arrive as PHP arrays; rejecting only PHP objects misses
the real wire case. Direct isolated calls may also supply PHP objects/resources.

Nonempty arrays can normalize to1 through absint and totrue through !empty;
string casts can emit warnings or execute a stringable object's method in direct
calls. Existing forbidden-key and enrolled UUID checks remain important, but
they do not validate the shapes of every top-level health field. Incorrect
health scalars can corrupt observations without granting client execution.

Compatibility evidence: read-only uploader3dfcb15 sources
`includes/class-health-summary.php` status() and
`includes/class-dashboard-status-rest-controller.php` handle_status_request(),
plus uploader `docs/STATUS_PAYLOAD.md`, produce/document scalar schema1 fields
and distinct optional structured extensions. This is repository evidence, not
an inventory of deployed versions or genuine endpoint acceptance.

Selected contract for the next separately approved foundation:

1. Add shape rejection only, not a new canonical scalar grammar. Preserve current
   string/int/float/bool conversions and missing/null handling; document that
   legacy nonempty string "false" remains truthy. Strict booleans/numbers/enums,
   negative-count semantics and global size/type redesign are separate decisions.
2. Cover21 top-level scalar slots: schema_version/site_uuid/plugin_version;
   queue_count/uploaded_count/failed_count/warning_count; active_upload,
   auto_scan_enabled,server_cron_expected,server_outbox_configured,
   server_outbox_readable,wpvivid_override_configured,old_wpvivid_uploader_active,
   wp_cron_disabled; cron_status/cron_reason/last_runner;
   last_runner_at/last_scheduled_scan_at/last_wp_cli_scan_at.
3. Preserve forbidden-key rejection first. Guard schema shape before its integer
   conversion, retaining schema_unsupported; guard UUID shape before string cast,
   retaining site_uuid_mismatch. After valid schema/UUID, reject unsupported shapes
   in the other19 slots as payload_invalid before their conversion. Unsupported
   schema/UUID errors retain precedence over malformed later fields. No raw value,
   field contents or exception text is logged/reflected. Do not recursively
   sanitize/traverse an already rejected scalar-slot compound value.
4. Preserve unknown-field omission and existing warnings/backup_sources/
   restore_readiness/remote_actions/cadence_authority structured handlers. No
   extension enabling, authority change, stricter nested parser, new endpoint,
   schema bump or alteration of valid serialized snapshots. Malformed responses
   use the existing poll-failure path rather than a successful healthy snapshot.
5. Permanent isolated tests must cover every scalar slot, empty/nonempty/nested
   arrays, decoded JSON objects, direct objects and no object conversion. Positive
   scalar/legacy missing/null and structured-extension controls prevent an
   always-reject validator from passing. Preserve exact earlier error precedence.
   Test actual validator and poller rejection routing with zero successful
   snapshot/delegate calls, not a duplicate validator implementation. Guard-removal
   counterexamples must fail meaningfully. No genuine auth/SQL/network proof is
   inferred from those tests. Existing caller behavior is not remote acceptance.

This is the preferred next implementation because it removes misleading
compound coercion without migrations, lock services or broad legacy scalar
tightening. It is lower severity than policy overlap, but the bounded change is
source-justified now; persistence concurrency needs its own unresolved design.
Neither decision permits packaging or declaring release readiness prematurely.

## Validation, publication and review limits

Source read/trace and hypothetical interleavings only; no new tests or runtime
experiments were added to manufacture progress. Existing related isolated suites
ran randomized, seed20261027:49 tests/245 assertions, no failures/skips.
`npm run check:structure` passed (136 runtime/202 support/159 test files).
All11 relative documentation links resolve; diff hygiene and the baseline-to-head
non-document source identity check pass. All21 scalar names were checked against
the actual validator allowlist; no runtime assertion or new test was substituted.
Published design `2f21d61cdc4f993c51e5f162ac8ed853975f825f` passed
[exact-head CI40559028471](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40559028471):
PHP7.4/8.3 each665 tests/3422 assertions, no failures/skips; Node16/16 per job,
all configured build/structure/lint/manifest checks and both audits passed,
zero dependency advisories/vulnerabilities. Actual counts retrieved from both
job logs; remote master matched the clean design head. These unchanged-code
controls do not prove any proposed behavior. No whole-plugin checklist row is
refreshed; toolkit stays read-only while canonical07 still has unresolved work.

## Implementation-plan progress and next gate

All four remaining concerns now have explicit source-backed design dispositions,
but none is represented as a new runtime fix or physical concurrency proof.
Policy conflict handling and payload implementation remain open; advisory locks,
best-effort logs and duplicate pending admission are documented/deferred limits.
Whole07/07A/13/FULL remain incomplete.

Recommended next gated slice: repository-only **Top-Level Status Payload
Compound-Shape Rejection Safety Foundation**, implementing only the21-slot
contract above with isolated tests, validation, commit, push and exact-head CI.
Approval must preserve scalar compatibility, structured extensions, authorization,
storage, native execution, production wiring and all site state. After that,
resolve the source-policy concurrent-save contract before canonical07 closeout;
do not silently substitute maintenance or reopen V2.3.
