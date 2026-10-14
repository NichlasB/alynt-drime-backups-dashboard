# V2.3 Authenticated Local Consent and Durable Admission-Closure Design

Approved local-only, documentation-only on 2026-10-14. Source checkpoints: dashboard `a53fc5013854dd52d11a04a48685b08ad17ab41d`, uploader `a6fa93786435c27a8c2b06904ae51fdbfa0b6cde`. This advances [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md) and the command/provenance gate in [the trusted-context design](V2_3_TRUSTED_CONSENT_CONTEXT_DESIGN.md). Production wiring, actual permission controls, storage/schema writes, activation and rollback execution are unchanged. Candidate contracts below are not installed APIs, accepted request fields or public evidence.

## Preferred Decision And Non-Goals

Design a client-owned, purpose-specific consent ledger and command boundary, not a dashboard-issued permission. A future authenticated grant may authorize preparation only; it must not enable authority, infer activation/handoff history, dispatch a scan or enable rollback. Keep the observer and all execution switches dormant until independently reviewed authorization, storage, writer and effect integrations have passed their own proofs.

Durable closure is the authoritative safety transition: clear consent and close new admissions in the same exact-byte CAS as the permission-generation/revision increment and command receipt. Separate settings/enrollment mutations follow closure, never precede it as a purported substitute. A persisted grant and a successfully closed admission are different outcomes from a submitted command or an apparently disabled setting.

Do not add a new token/HMAC secret, capability, role, remote action, REST route, CLI bypass, global options/cron filter, status extension or package in this design. The initial candidate remains single-site and bound to the existing paired/remote-opt-in identity; unpaired or multisite authority needs a separate trust decision.

## Source-Backed Gaps

Uploader paths are relative to the separate `alynt-drime-backups-uploader` repository.

| Source | Current contract; design consequence |
| --- | --- |
| `includes/trait-plugin-admin-actions.php` | Existing local handlers check `manage_options` and action nonce. No cadence-consent handler exists; reuse the pattern only through a separately reviewed real request boundary. |
| `includes/class-cadence-authority-lifecycle-store.php` | Schema 3 records synthetic consent and generation; every authority is disabled. `record_consent()` authenticates no administrator and cannot establish provenance. Failed writes may retain prior consent. |
| `includes/trait-cadence-authority-lifecycle-preparation.php` | Explicit preparation preserves protected journals, rejects unfinished migration and retains unknown history. It does not certify never-activated or verified handoff. |
| `includes/class-schedule-safety-store.php`, `includes/class-schedule-safety-option-cas.php` | Strict bounded authoritative state reads and three-attempt byte CAS; protected state is not a transaction with settings, enrollment, cron or queue effects. Existing backend allowlist is state/cron only. |
| `includes/class-cadence-authority-context-observer.php`, `includes/trait-cadence-authority-context-facts.php` | Test-loaded strict fact reader; no real provider or authenticated provenance. All current schema-3 observations remain non-admitting. Two observations are not global-writer fencing. |
| `includes/class-settings.php` | Defaulted getter and unconditional separate-option update; identity helper may create UUID. Do not use these as authoritative permission facts or initialize identity during observation. |
| `includes/trait-dashboard-connection-state-transitions.php` | Revocation/remote-disable and authenticated-read timestamps rewrite copied whole options. A stale timestamp writer can overwrite semantic permission changes even though its timestamp is not a binding input. |
| `includes/trait-dashboard-connection-enrollment.php`, `includes/trait-cadence-authority-evidence-context.php` | Pairing/key/origin changes and remote opt-in are separate from cadence consent. Preserve version-1 binding semantics; never inherit an old grant across re-enrollment. |
| `includes/class-cadence-authority-entry-point-policy.php` | Private pre-admission classifier always returns dispatch/execution false. A projection is neither authenticated provenance nor an owned-work permit. |
| `includes/class-cadence-authority-scan-dispatcher.php`, `includes/class-cadence-authority-store.php` | Durable ownership/start and bounded context checks exist in dormant form. Current two-field context callback is not an epoch-aware closure fence and must not be replaced with the pre-admission pending rejection. |
| `includes/class-cron.php`, `includes/class-plugin.php` | Production construction retains legacy behavior; no observer/ledger admission integration is installed. |

Historical uploader `docs/V2_3_LIFECYCLE_ISOLATED_STORAGE_PROOF.md` demonstrated isolated CAS ordering, failed-write retention and interrupted acknowledgement. It did not authenticate a user, coordinate independent writers or prove effects cannot happen after revocation. That proof is not rerun here.

## Future Authenticated Local Command

The future handler must run on the client-local administrator request surface, not dashboard credentials or a serialized projection. Require authenticated WordPress user context, current `manage_options` for the current site, POST-only explicit purpose/action intent and an action-specific nonce before sensitive observation or mutation. No guest, GET, remote signature, pairing token, Schedule Apply permission or rollback-preview opt-in substitutes for this authorization. WordPress nonces provide CSRF/request-intent protection but are neither authorization nor one-use replay protection; see the [official nonce guidance](https://developer.wordpress.org/apis/security/nonces/).

Treat submitted command ID, expected permission generation and intent deadline as untrusted concurrency inputs, not permission. Strictly validate exact fields, bounds and purpose; reject additional fields, truthy booleans, actor/issuer assertions, posted current-binding/clock and counter overflow. Derive site/enrollment identity and UTC time through closed authoritative readers without defaults, repair, UUID generation or cached permission assumptions. Bind the nonce/action context to the command identity and expected generation; recheck authorization and current semantic context at the write boundary. User/capability changes immediately after the last check remain a documented request-authorization race, not a solved distributed transaction.

Candidate command purposes are preparing grant, revoke and context closure. A grant needs explicit affirmative intent, a present validated dormant ledger and a still-current binding/generation; a revoke closes the stored grant without requiring the old remote connection to remain paired. A local-authorized closure must still identify and validate the current site's ledger and expected generation. Rebinding first closes the old grant; the new identity receives no inherited consent. Missing/corrupt/unsupported ledger rejects without creating or repairing it.

Use one bounded command identity and canonical digest over purpose, validated site scope, binding expectation, submitted expected generation and a short intent deadline. Maximum candidate grant-intent window is 300 seconds; it is not grant lifetime or a worker lease. The deadline must originate from and be bound to the reviewed server-issued intent/nonce context; merely accepting a posted time within now plus 300 seconds does not prove intent issuance. Never accept a client-supplied digest as proof. Authenticate before receipt lookup, so replay cannot expose private ledger information. Do not silently replace a stale submitted generation with the newest epoch and retry. Grant rejects unfinished/draining work; authenticated closure must not depend on grant eligibility, remote pairing or a usable grant-intent window.

The initial grant is site-owned, not a logged-in session lease. Logout or nonce expiry does not retroactively revoke a committed preparing grant. Automatic revocation on administrator-role deletion, ownership transfer or reinstall is not implemented or implied; its writer/lifecycle policy must be reviewed before activation. Workers would consult durable site consent plus fresh ownership/context fences, not an administrator cookie. No retained nonce, cookie, token, verifier, key, raw request or unnecessary personal identifier is needed in the consent receipt. Provenance records the reviewed local command path and scope, not personal audit attribution or cryptographic protection against same-privilege plugin/DB writers.

## Candidate Versioned Ledger And Migration

Prefer a separately reviewed successor schema in the same dedicated non-autoloaded safety option, atomically coupling permission and receipts rather than creating another independently writable grant option. Do not assign a deployed version or broaden the CAS backend allowlist in this design. Proposed internal additions must have an exact bounded validator; no public/wire fields are finalized here.

| Candidate record | Required meaning and limits |
| --- | --- |
| Consent provenance | Versioned purpose/path marker, validated site/enrollment scope, command ID/digest, granted permission generation and server UTC commit time. Its trust comes from the closed authenticated writer, not the marker string or a caller-provided boolean. |
| Admission state | Explicit closed/preparing only in the next dormant implementation. Consent grant leaves authority disabled; no active phase or positive execution projection. Closed state carries current permission generation/revision and reason, independent of connection flags. |
| Last transition receipt | Exact command identity/digest, purpose, expected/result generation, outcome and server time in the same CAS as the transition. Always reserve space for this receipt so history capacity cannot prevent closure. |
| Bounded terminal history | At most 50 compact terminal receipts within the existing 128 KiB/depth-16 state limits. History capacity must reject new grants rather than evict protected grant/closure evidence; closure may omit adding optional history, never its last receipt. |
| Existing protected state | Preserve cadence/due, binding and existing terminal/unfinished journals and dispatch ownership. Closing admissions does not delete, expire, complete or rebind uncertain work. |

Reserve encoded-byte capacity and monotonic-counter headroom for a closure before accepting preparation/migration or a new grant; a numeric receipt-count limit alone is insufficient. Reject an oversized successor migration rather than consume that reserve. A future active ledger must close and retire safely before counter exhaustion, never grant at the last usable generation or reset counters to free space. Unknown physical storage failure remains outside any capacity reservation guarantee. Exact field sizes and reserve thresholds must be settled and adversarially tested in the next implementation, not guessed into the current schema.

Candidate migration remains explicit, never a getter side effect. Preserve original journals/receipts and unknown history, initialize authenticated provenance absent and admission closed, and increment version/generation only through a reviewed conditional transition. Schema-3 synthetic `consent: true` must become untrusted/closed, never an authenticated grant. Reject unsupported/corrupt state, overflow or migration with unfinished work unless a separately proved lossless draining transition is approved. No migration happens here.

An absent/new row, clean journal, epoch zero, `new_model` or `unknown` label cannot establish never-activated/handoff history. No receipt added by this ledger resolves that history gap. Downgrade/reinstall/uninstall, per-blog routing and verified legacy handoff remain separate gates; an old binary must not silently resume legacy scanning over future authority state.

## Command Replay And Ambiguous Acknowledgement

| Observed case | Candidate required result |
| --- | --- |
| Fresh authorized grant, unchanged context/expected epoch | One CAS records preparing consent/provenance and receipt, advances generation/revision and leaves authority disabled. No callback, queue or schedule write. |
| Same ID and identical canonical digest already committed | Observe receipt only; no write, new lease, epoch advance, refreshed deadline or regrant. Report whether that receipt is still current or superseded. |
| Same retained ID but different digest/purpose/site expectation | Reject conflict; do not reinterpret the original command or reveal its raw fields. |
| Old grant retried after revoke/context closure | Superseded receipt or stale-generation rejection; never permission. Even if terminal history is later pruned, monotonic expected-generation fencing prevents regrant. No generation reset is permitted. |
| Concurrent commands at the same expected epoch | At most one state-changing CAS wins; loser rereads only to classify the original request, not to adopt the winner's new epoch. |
| Process ends after CAS commit but before acknowledgement | Fresh authoritative receipt/state observation can identify the original committed or superseded outcome. Do not issue a new command or replay effects automatically. |
| Write/read/connection failure with unknown outcome | Report unresolved outcome, not success or no-write assurance. Stop dependent mutations; a later explicit read may reconcile the same identity. Missing receipt alone cannot prove no commit if observation itself is unavailable. |

Keep transitions pure inside the bounded CAS retry loop: no nonce generation, logging, external callback or settings write inside the transformation. Revalidate exact binding/expected epoch and capacity on every attempt. A maximum three attempts is a contention bound, not permission to retry a new command after timeout. Full receipt/outcome codes and retention policy need implementation review and tests; current observer reason codes remain unchanged.

## Durable Closure Ordering And Partial Failure

The following is the preferred future ordering for authenticated revoke, remote-opt-out, identity/key/origin replacement and meaningful automatic-scan permission changes. No existing writer is altered here.

1. Validate the local caller/writer purpose and authoritative ledger/site scope. Prepare the intended semantic change without applying it.
2. CAS admission closed and consent absent, advance permission generation and schedule revision, and persist the exact closure receipt. Retain unfinished work under its original owner/binding; phase remains draining when uncertain work exists, otherwise disabled. A repeated already-committed closure is observation only.
3. After confirmed closure, apply the separately authorized settings/enrollment change through a reviewed conditional writer. Closure is never rolled back if that second write fails.
4. Reobserve actual semantic state and record the bounded transition result. A new explicit grant at the new epoch/context is required before any later authority preparation. Nothing automatically enables authority or resumes legacy scanning.

**If step 2 fails or cannot be reconciled, durable revocation is not confirmed and the previous grant may remain.** Do not show “disabled”, perform the dependent change, clear journals or tell another process that a transient in-memory error revoked its grant. If closure committed but the secondary option write failed, admissions remain closed; report partial completion, preserve uncertainty and require explicit reconciliation without reopening.

A broken/unwritable database cannot guarantee immediate durable revocation through that same database. Future admission/effect readers must reject unreadable state, but other processes may still read an old committed grant. Existing emergency settings are unchanged and are not proof of a new cross-process circuit breaker. Active-mode shipping is blocked until the supported failure model, freshness/epoch fences and operator response are proved; any independent emergency stop mechanism is a separate design/approval, not an invented fallback here.

## Independent Writer And Effect Fences

All participating semantic settings/enrollment writers need the closure protocol before activation. Timestamp-only writers need narrow conditional updates or an equivalent reviewed coordination mechanism that cannot restore a stale whole-copy semantic flag. Excluding polling timestamps from the binding is correct, but does not make whole-option replacement safe. A resurrected remote/scan flag alone must never recreate ledger consent; a fresh authenticated grant is required. Semantic off/on or key change-away-and-back must advance epochs, even if the final binding hash matches its initial value.

Do not install a global `update_option`/cron interception filter or assume separately written options are a single transaction. Arbitrary same-privilege code/direct DB writers, unsupported cache backends and old binaries are outside a cooperative writer proof. Before active integration, inventory and fence scheduled/manual/admin/CLI/remote/direct scan and raw queue entry points; blocked scans must not indirectly trigger scan-derived uploads. Independently authorized existing upload/abort/retention work remains a different purpose and is not cancelled by this ledger.

Future claim/start/effect checks must bind the authoritative consent epoch, identity, revision, owner and intended operation. Closure winning before claim/start blocks new work; claim winning first still needs a fresh post-claim check. Already-started work may have effects before it notices closure; retain recovery evidence instead of claiming instant cancellation, exactly-once queue writes or “nothing happened”. A check immediately before an effect still leaves a check-to-effect race unless the effect itself has a proved fence. No CAS over one option atomically controls Drime, queue records or another process's callback.

Owned-dispatch revalidation must recognize its own pending owner rather than reuse pre-admission's uncertainty rejection. The existing two-field dispatcher reader and 16-field entry-point projection are different contracts; neither is upgraded by this design. Failed completion, expired ownership, exception or changed context preserves uncertainty and prevents automatic replacement/replay. Rollback stays reserved and rejected independently of any grant.

## Required Future Proof And Review Sequence

| Layer | Required evidence before claiming that layer complete |
| --- | --- |
| Dormant ledger/intent fixture | Strict version/purpose/types/size/clock/counter validation; synthetic consent not promoted; immutable duplicate/superseded receipt; grant/revoke/rebind ordering; closure at capacity; failed/ambiguous writes and protected pending state. In-memory evidence proves only the model. |
| Real local authorization | Actual WordPress handler rejects guest/no-capability/GET/wrong nonce/other purpose/forged actor or binding/stale intent and authorizes each receipt read. No mock-only authenticated-consent claim. |
| Isolated SQL/process proof | Explicit disposable-site approval; migration, competing exact-epoch commands, closure-before-secondary-write, failed SQL, commit-without-acknowledgement, cache freshness and receipt retention. Actual options/cron/activation unchanged. |
| Writers/owned effects/lifecycle | Stale timestamp copy, off/on ABA, all entry points, before/after start/effect closure, partial secondary writes, role/ownership policy, deactivate/reinstall/downgrade/handoff and supported cache/site scope. No broad production wiring until reviewed. |
| Release/activation | Applicable feature and full pre-release/lifecycle review, package audit and separately approved activation/deployment/pilot with restore points. Foundation tests are not active-feature clearance. |

Focused architecture, authorization/purpose separation, persistence/idempotency, privacy, concurrency and lifecycle reviews are appropriate now. This document does not claim execution of the full feature/pre-release workflow suite. No new executable tests are justified for unimplemented candidate semantics; validate source links and unchanged regressions, then implement adversarial tests in the separately approved next slice.

## Validation, Ownership And Exact Next Gate

Only dashboard documentation changes. Uploader remains read-only at the source checkpoint above; no site, LocalWP, Novamira, browser, SQL, credentials, options, runtime providers, permission handlers, protocol or versions change. No source restore archive, site backup, release or deployment is needed for this documentation-only slice. Dashboard remains `0.1.77`, uploader `0.5.25`. Validation/publication receipts are recorded below only after execution.

Executed on 2026-10-14: uploader `php vendor/bin/phpunit --do-not-cache-result --filter 'CadenceContext|CadenceLifecycle|CadenceAuthority|GuardedScan|CronGuarded'` passed **387 tests / 1538 assertions**, no skips. Dashboard `php vendor/bin/phpunit --do-not-cache-result` passed **299 tests / 1423 assertions**, two existing Windows skips. Dashboard `php vendor/bin/phpcs` passed 138 files; `npm run check:structure`, `npm run build`, `npm audit --audit-level=moderate` and `php composer.phar audit` passed, both audits with zero vulnerabilities. Source-reference checks resolved all 16 quoted uploader PHP paths and five relative document links across the three changed documents. Diff checks passed; no runtime, test/assertion/skip, dependency, version or generated asset changed. These regressions protect unchanged code; they do not execute the proposed authenticated handler, ledger, migration or closure. No full pre-release workflow or real authorization/SQL/effect proof is claimed. Exact-commit CI remains the publication gate.

**Recommended next gated slice: local-only V2.3 Consent Ledger and Command Receipt Safety Foundation.** Implement the bounded successor-ledger validator, explicit dormant migration and pure grant/closure/duplicate/superseded command transitions against in-memory CAS fixtures, with test-only injected command context that is explicitly not real administrator authentication. Preserve journals/due/history and reserve closure receipt capacity. Keep definitions outside production bootstrap; no real local permission handler/provider, site/SQL writes, activation, scheduler/effect integration or rollback. This is a new permission/storage interpretation gate requiring approval; the real authenticated handler and SQL/writer/effect proofs follow separately. Do not package dormant models or continue maintenance to avoid that decision.

## Publication Receipt

Design commit `0e5165579673a5f2954b8c57541ede3d6c7a1451` is pushed to `origin/master`; exact-commit [CI 38586056884](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/38586056884) passed PHP 7.4/8.3, each **299 tests / 1430 assertions**, no skips, plus Composer validation/audit, npm audit, build, structure and PHPCS. Three dashboard documents changed; source review found no runtime/wire/schema/permission or site mutation. Uploader remains clean/synced at `a6fa93786435c27a8c2b06904ae51fdbfa0b6cde`. The approved design milestone is complete, not authenticated permission or closure implementation. The receipt-only follow-up requires final exact-head CI verification before handoff.

## Dormant Ledger Foundation Follow-Up (2026-10-14)

The separately approved fixture-only ledger implements strict schema-4 migration and purpose-bound receipts with non-replaying duplicates/superseded grants, preserved journals/due/history and byte/counter/history capacity for closure. Its source marker is `fixture_intent_v1`, not authenticated provenance; no real permission handler/provider, production include, actual schema/options/SQL or activation is installed. Uploader `docs/V2_3_CONSENT_LEDGER_SAFETY_FOUNDATION.md` records 71 focused tests / 775 assertions and proof limits. The next meaningful gate is **local-only V2.3 Consent Ledger Isolated Storage and Admission-Closure Proof**, with owned disposable Plugin Tester storage and actual options/cron/activation unchanged; real authorization and writer/effect integration follow separately.
