# V2.3 Cadence Authority Evidence and Eligibility Integration

Approved 2026-10-13, local repository work only. Baselines: dashboard `eb5b15d`, uploader `b48ff5e`. R3 trust/protocol boundary; no production activation, release or deployment.

## Reviewed Contract

Use a separate optional `cadence_authority` status namespace with `contract_version: 1`, fixed `mode: client_owned_v1`, enrolled `site_uuid`, public `key_id`, integer `authority_revision`, supported `current_cadence`, UTC integer `next_due_at`, opaque 64-hex `authority_fingerprint`, `model_enabled` boolean, `pending` enum (`none`, `claimed`, `started`, `recovery_required`), integer `observed_at`/`expires_at` (five-minute maximum), and literal-false `dispatch_supported`/`execution_supported`. This is model evidence, not a remote-action capability. Unknown versions, fields, mismatched UUID/key context, malformed types or enabled execution flags are omitted without changing legacy health classification. Existing forbidden-field rejection still applies.

The uploader reads its own strict model and local pairing context. A domain-separated internal binding covers UUID, dashboard/client origins, enrollment public ID, pairing time, polling key/verifier, action key/public key and action opt-in time. Neither the binding nor credentials, journal, lease or raw state are exported. Dashboard expectations come from the enrolled site row, not the returned key ID.

Native model commits add `authority_evidence_version: 1` to the succeeded journal inside the existing single CAS. It is accepted only on valid succeeded model records; caller-supplied reservation evidence cannot include it. Schema-1 migration retains unmarked records and never adds provenance. Older strict readers fail closed on the additive record field. No new option/table, production migration or bootstrap write occurs. The marker proves which local model transformation produced the record, **not authenticated remote action execution**.

An explicitly called read-only inspector locates one native succeeded record by `source_change_action_id`, derives its rollback cadence locally, checks current enrollment, existing local rollback-preview opt-in, enabled authority, no unfinished work, unconsumed source, matching current revision/fingerprint and bounded freshness. IDs must be distinct; intent expires within five minutes. Its version-1 `model_change_preview` projection adds source/preview IDs, rollback cadence, source creation/expiry, preview creation/expiry, intent expiry and canonical `preview_fingerprint`. Source evidence expires at completion plus one hour; preview expires at the earliest of source expiry, creation plus fifteen minutes and intent expiry. All integer timestamps are UTC. `safety_checks_passed` may be true, but `eligible`, `dispatch_supported` and `execution_supported` are always false. No signed intent is authenticated or granted by this method.

The inspector re-reads model and pairing before returning positive evidence; a changed observation fails closed. This is not an atomic authorization promise: a future reservation/commit must re-evaluate all guards inside its mutation boundary, authenticate the actual request and establish a separately approved runtime permission. Status/preview hashes are consistency checks, not signatures. Dashboard preview validation requires fresh caller-provided current authority evidence and expected source/preview/intent identifiers; cached payloads are not authorization.

## Compatibility And Dormancy

No existing action field, cadence-plus-cron fingerprint, preview result, route, allowlist, local setting, permission, hook or schedule changes meaning. The uploader Health Summary accepts optional explicit producer injection; its production factory is unchanged, so deployed/default behavior performs no new model reads and emits no authority extension. The dashboard only retains a strictly valid extension if received with the persisted enrolled action-key context. No authority UI, dispatch or execution consumer is added.

Fixtures in both repositories are identical synthetic public projections and require no LocalWP/production database. These fixtures prove producer/consumer compatibility, not HTTPS authentication, actual opt-in, real signed-action provenance, worker effects or crash recovery.

## Threat Review

- Reject migrated/legacy, failed, expired, consumed, stale-revision or stale-fingerprint sources; never infer native provenance from matching legacy hashes.
- Fail closed on revocation, key rotation/re-pairing, disabled opt-in/model, unsupported version, uncertainty, future clocks, duplicate identifiers and changed observations.
- Strict scalar/enum/field allowlists exclude nested private state and secret-bearing fields; errors disclose stable codes only.
- Keep new evidence outside `remote_actions` so existing schedule controls cannot gain authority/execution permission from it.
- Do not trust self-reported public keys, fingerprints or safety results as permission. A compromised client can lie about health; evidence does not make the dashboard a privileged executor.

## Verification And Next Gate

Implemented the strict uploader producer/inspector, native journal provenance, explicit Health Summary injection seam, dashboard consumer and persisted-key polling integration. Reviewed diffs contain no production factory injection, new action/route/control, opt-in, hook, database schema or scheduler/worker change. No site, database, actual schedule, backup, Drime operation or production credential was used.

Executed verification:

- Dashboard `php vendor/bin/phpunit --do-not-cache-result`: 299 tests / 1423 assertions, two existing skips. `php vendor/bin/phpcs`: 138 files passed. `npm run check:structure`, `npm run build`, `npm audit --audit-level=moderate`, `php ./composer.phar audit`, and `git diff --check`: passed. No generated asset changes.
- Uploader full `php vendor/bin/phpunit --do-not-cache-result`: 386 tests / 2955 assertions, four existing skips. The last two test-only observation/provenance cases were added during that run; the final `--filter CadenceAuthorityEvidenceTest` rerun passed 33 tests / 166 assertions. Earlier combined authority/health coverage passed 73 tests / 780 assertions. Exact-commit CI must run the final 388-test tree, including PHP 7.4/8.3 compatibility.
- Uploader `php vendor/bin/phpcs`: 126 files passed. Build, runner verification/syntax checks and three dev-tool tests passed; generated files unchanged. npm audit: zero vulnerabilities. Composer audit using the dashboard's available `composer.phar` from uploader cwd: no advisories. Existing PHPCompatibility PHPCS deprecation remains visible; no exceptions/skips were added.
- Shared synthetic fixture SHA256 in both repositories: `e09d2d5cb96c504aad157572cd187a055f32c02e6be1fcebe4d046fd8789a870`. Actual producer projections match this golden fixture; the dashboard accepts the identical public projections and rejects altered/unsafe contracts. This is unit/contract proof, not authenticated runtime or crash-boundary proof.

Both clean committed source baselines were checked; the toolkit uploader restore-point check passed before edits. No new external archive or WordPress/database backup was made. Commit/push and exact-commit CI are the final publication gates. Versions remain dashboard `0.1.77` and uploader `0.5.25`; a package/release is not warranted for this dormant foundation.

Recommended next gated slice: **V2.3 Cadence Authority Guarded Scan Callback Integration**, local-only source and disposable-fixture proof, with real authority opt-in/activation and rollback execution still disabled. This must explicitly approve runtime dispatch-to-worker ownership and start/side-effect/completion crash-boundary work; the evidence layer alone does not solve that boundary.

## Publication Receipt

Dashboard implementation `c3270b53b7729c983567fd825b74e51963101ccc` is pushed to `origin/master`; exact-commit [CI 38456988852](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/38456988852) passed PHP 7.4/8.3, with 299 tests / 1430 assertions in each job, plus lint, structure, builds and audits. Uploader implementation `97c6c04a93274630b8de3adb736737b4b5a27c0f` passed [Quality 38456976342](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/38456976342), PHP 7.4/8.3 each executing the final 388 tests / 3015 assertions, plus Node build/audit. Neither CI suite skipped tests; existing platform-dependent Windows skips explain local count differences. Both worktrees were clean/synced when checked. This documentation-only receipt records completion, not new runtime authority or permission. The implementation-plan evidence item is complete and the next meaningful work crosses the guarded runtime-integration gate; do not create maintenance churn or a package to avoid that decision.
