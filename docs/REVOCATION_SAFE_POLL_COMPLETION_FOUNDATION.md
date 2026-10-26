# Revocation-Safe Poll Completion Safety Foundation — 2026-10-26

Current follow-up: the separately approved and exact-target-confirmed
[owned core/database proof](REVOCATION_SAFE_POLL_COMPLETION_OWNED_PROOF.md)
passed269/8, including real prepared/no-op/error/readback and ordered two-process
revocation cases; fixtures removed and77/77 application checksums unchanged.
The original source-only scope and proof gate below are historical, superseded
only for that narrow physical qualification. Multi-write atomicity/ABA limits
remain.05 stays pending for the separate Medium scheduled-retention error finding.

## Approval, baseline and scope

Approved repository-only R3 repair of the High canonical05 finding: a captured
poll could complete after `revoke_local()` and restore `enrollment_status=active`.
Baseline `d68db6247caa1992a43340e519440854e10c7468`, master, clean/synced;
predecessor CI40387153731 passed. That evidence does not qualify this new query.
Version0.1.77/schema8/minimum WP6.0/PHP7.4 remain unchanged. Uploader and toolkit
are not implementation targets. No site/SQL/authentication/lifecycle operations,
schema changes, database fixtures, account/credential/schedule changes, release or deploy.

External source-folder restore point (not a site backup or package):
`C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots\alynt-drime-backups-dashboard\alynt-drime-backups-dashboard-20261026-212727.zip`,
matching `.txt` sidecar, SHA256
`293CF3813F30544613BDE59267D808B609953C1A2400DA82B9EFCD35F21F4E67`.
Option B only; no checkpoint branch or restore operation.

## Plan and implemented contract

1. Pass the original captured site row from the poller into success **and** failure
   completion methods. Optional final context arguments preserve PHP call shape,
   but absent/invalid context fails closed; no legacy ID-only write is authorized.
2. Admit only a positive matching site ID, a captured active/awaiting-first-poll
   state and typed identity/credential fields. Success requires nonempty polling
   credentials. Failure may report missing credentials only when explicit null
   or empty captured fields still match; missing keys are not implicit nulls.
3. Fence the UPDATE itself by primary key, current pollable enrollment and exact
   captured public ID, key ID and encrypted polling secret. All values are
   prepared, identifiers come from fixed internal maps/storage naming, and
   strings compare byte-for-byte with BINARY, not a case-insensitive collation.
   A separate pre-read or transient lock is not treated as the write barrier.
4. Distinguish SQL failure (`false`) from stale/missing/changed admission
   (`WP_Error: poll_completion_stale`). Zero affected rows are not automatic
   success: a bounded primary-key SELECT must verify the same fence **and every
   intended persisted value**. Read failure remains storage failure. A genuine
   unchanged result succeeds, including repeat first-poll completion with the
   same credential generation. At most one UPDATE plus one zero-row readback.
5. Stop the caller on stale success before reporting success or reconciling
   remote-action payloads. Consolidate failure recording so WP_Error cannot be
   cast to true. Snapshot/transport/auth/payload failures retain their original
   error when recording succeeds; a failed recording carries its original code
   in the existing storage-error envelope, and stale admission stays explicit.

Existing key-storage, revocation, enrollment writes, authentication, transport,
retry cadence, transient locks, schema, indexes and public routes are unchanged.
The new safe operator message is translated. Its entry and three affected
poller references were projected from actual tool-only WP-CLI2.12.0 extraction,
using empty owned configuration, `--skip-packages`, and build/docs/scripts
exclusions. Unrelated historical POT drift is preserved, not silently refreshed.

## Source-scoped review checkpoint

Explicit coherent review baseline: `d68db6247caa1992a43340e519440854e10c7468`.
Scope: two production traits, their directly affected poller/repository callers,
three permanent test classes, two new support files, three adjusted poller test
helpers, the scoped POT projection and owning records. No whole-plugin review
row is refreshed by this checkpoint.

Canonical FEATURE_LIGHT_REVIEW: risk DB/polling-state/error contracts; no UI,
route, hook, request authorization, dependency or architecture addition. One
initial PHPDoc placement mistake and mechanical indentation/alignment findings
were fixed. No additional unresolved source defect. Bloat/structure workflow
unnecessary: one cohesive private write fence in the existing trait, shared
failure handling instead of duplicated branches, no new runtime file/loader,
and all structure limits pass. Backend-only; no UI/UX workflow. Database Review
and full release/security readiness remain pending.

Canonical07 source-scoped inventory, then07A implementation/review:

| Category | Scenario / disposition |
|---|---|
| Empty/deletion | Missing row cannot report success; no recreation/cascade. |
| Boundaries/corruption | Missing/malformed IDs, state, identity and credential fields reject before SQL; explicit nullable failure context supported. |
| Concurrency/permissions | Revocation or credential/identity change between capture and completion rejects at the modeled write; byte-only changes matter. |
| External failure | Real poller error paths propagate stale/storage/original failures without success reconciliation. No actual HTTP request. |
| WordPress variation | Prefix from storage helper; binary/null/affected-row semantics still require real wpdb/MariaDB qualification. PHP7.4/8.3 CI is separate from local8.5. |
| Recovery/retry | Active/first poll positives, repeated no-op positives, write/read errors, revocation between zero-write/readback, changed persisted state. |
| Resource/extremes | Fixed field maps and primary-key bounded one/two-query paths; no scans, loops over sites or native load experiment. |
| Conflicts/browser | No new identifiers, hooks, browser output or clients; not applicable to this backend differential. |

07A classification: useful source/API regression protection, not database or
physical-concurrency proof. The SQL double interprets the actual emitted fixed
predicates and assignments, fails on unknown syntax, and models case-insensitive
equality unless BINARY appears. It does not independently enforce the desired
policy, implement SQL escaping, or certify server/driver behavior. Real production
repository and poller methods run; only storage/HTTP/WordPress APIs are isolated.
No test of genuine authentication, hooks, transactions or storage is claimed.

RED on unchanged production:24 tests/39 assertions,23 behavioral failures,
including actual `revoke_local()` followed by success/failure completion accepting
the stale row. No syntax/bootstrap failure. Positive completion is tested too;
an always-denying implementation cannot satisfy the suite.

Seven temporary semantic controls were assertion-killed, then original source
SHA256s restored: remove current-enrollment predicate (2 failures), remove key-ID
fence (2), remove byte comparison (6), accept zero rows blindly (2), omit complete
intended-state readback (1), ignore stale success in the caller (1), remove the
primary-key fence (1). No source
bypass, artificial success path, skip or lowered assertion remains.

Final focused FEATURE_SECURITY_REVIEW (after fixes/validation): no additional
confirmed introduced vulnerability. Closed field maps/individually prepared
values are the only SQL concatenation; no request keys become identifiers.
No raw SQL, credentials, ciphertext or original response enters the new error.
No auth/nonce/route change, filesystem operation, debug bypass or dangerous API.
Regression handoff: the confirmed High defect now has permanent source/API
protection; physical SQL/CAS behavior remains the explicit gated handoff below.

## Validation and publication

Local PHP8.5.1: related61 tests/321 assertions, no skips; full363/1867 with the
same two pre-existing missing-Sodium signing skips, no failures/errors/warnings.
Forty-six permanent cases added; no old tests removed or weakened. Source
restoration hashes: runtime writes
`77C4C6514FE8B46BCDC498683586B32A6758CB7EC79594142E7A7E2932D16308`,
poller status check
`B5D8BAC77941D08379467B1168042201E079BB6CC71DF319C74D8E5C656BC330`.
Node16/16, no failures/skips; PHPCS144 files, structure136 runtime includes/194
support helpers/142 test files, deterministic asset build with no tracked output
change, strict Composer manifest validation, Composer/npm audits with zero
advisories, syntax and diff hygiene passed.

Published implementation head `281c94f802a6fe4749ad421200c10fdc96792256` passed
[exact-head CI40397479255](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40397479255).
Actual PHP7.4 and8.3 logs each report363 tests/1874 assertions, no skips/failures;
Node each16/16, fail0/skip0. Both matrix jobs passed strict Composer validation,
dependency audits (zero advisories), build, structure and PHPCS144. The Sodium
branches skipped by local PHP ran successfully there. Source-scoped foundation
publication is complete; this is not the physical qualification below.

## Proof limits and next gate

This is **not** an atomic multi-write poll transaction. A redacted snapshot is
still inserted before the fenced completion, and an event may be logged after
rejection. Revocation after an already accepted write can also occur before
reconciliation. No claim of zero post-revocation writes or full serialization
is made. The fence uses current enrollment/credential identity, not a new
monotonic generation: deliberately restoring identical credentials/state is not
distinguishable without a separately designed contract/schema decision.

No physical MariaDB/wpdb affected-row, collation, escaping, locking, concurrency,
recovery, installation or minimum-WordPress acceptance ran. Historical lifecycle
and schema proofs remain tied to their recorded source, not refreshed for this
new query. Canonical05 remains incomplete; the separate Medium ignored scheduled
retention-error finding remains open and is not bundled into this approval.

Recommended next gated slice: **Plugin Tester local-only Revocation-Safe Poll
Completion Conditional-Write, Zero-Row and Concurrent Revocation Owned Proof**.
Require separate approval and exact local target/owned disposable table fixture
confirmation, preserve existing application tables/options/users/cron and
production wiring, exercise real prepared SQL/no-op/error/credential-change
semantics and bounded ordered revocation/completion interleavings, then exact
cleanup. Do not deploy/package this foundation before the remaining release
review and physical qualification gates. Safe-local product progress is3/8
after this foundation was pushed and exact-head CI verified; organization
and separately approved native proofs do not inflate that count.
