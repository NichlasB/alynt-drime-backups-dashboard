# Alynt Drime Backups Dashboard Implementation Plan

This document is the implementation plan for the separate **Alynt Drime Backups Dashboard** WordPress plugin.

This is now the canonical implementation plan for the dashboard repository. The earlier uploader-side copy remains historical context only and does not make the dashboard part of Alynt Drime Backups Uploader.

Phase 3 protocol details are tracked in `docs/PROTOCOL_V1.md` and `docs/THREAT_MODEL_V1.md`.

## Current State And Safety Boundary

- Planning status: approved for local scaffold.
- Dashboard repository: created locally at `C:\Development\WordPress\Plugins\alynt-drime-backups-dashboard`.
- Dashboard plugin files: scaffolded.
- Uploader dashboard endpoint: not implemented.
- Eventual host: `control-sitesmanage live-only`.
- Live changes in this planning pass: none.
- Version 1 is read-only relative to client sites and Drime. It may create and update its own dashboard registry, polling credentials, status history, and schedules, but it must not change client settings, create or delete backups, restore data, clean up files, or mutate Drime.

The repository path and package identity below were explicitly confirmed before scaffolding. Broad feature implementation should still begin with a fresh restore point or an equivalent baseline snapshot.

## Repository And Package Identity Confirmation Gate

The proposed local path was checked on 2026-08-09 and does not currently exist.

| Identity | Recommended value | State |
| --- | --- | --- |
| Plugin name | `Alynt Drime Backups Dashboard` | Confirmed project decision |
| Local repository path | `C:\Development\WordPress\Plugins\alynt-drime-backups-dashboard` | Available; approval required |
| Repository name | `alynt-drime-backups-dashboard` | Approval required |
| Intended GitHub repository | `NichlasB/alynt-drime-backups-dashboard` | Recommended; create/verify before updater setup |
| Installed folder / plugin slug | `alynt-drime-backups-dashboard` | Approval required |
| Main plugin file | `alynt-drime-backups-dashboard.php` | Approval required |
| Initial version | `0.1.0` | Recommended |
| Text domain | `alynt-drime-backups-dashboard` | Recommended |
| Composer package | `alynt/alynt-drime-backups-dashboard` | Recommended |
| PHP package docblock | `Alynt_Drime_Backups_Dashboard` | Recommended |
| PHP namespace | None in v1; use the sibling-compatible class prefix `Alynt_Drime_Backups_Dashboard_` | Recommended |
| Function/option/hook prefix | `alynt_drime_backups_dashboard_` | Recommended |
| Constant prefix | `ALYNT_DRIME_BACKUPS_DASHBOARD_` | Recommended |
| Dashboard REST namespace | `alynt-drime-backups-dashboard/v1` | Recommended |
| Client status REST namespace | `alynt-drime-backups-uploader/v1` | Recommended |
| Minimum WordPress | `6.0` | Match uploader baseline |
| Minimum PHP | `7.4` | Match uploader baseline |
| Distribution | Owner-managed GitHub release ZIP through Alynt Plugin Updater | Recommended |
| Updater header | `GitHub Plugin URI: NichlasB/alynt-drime-backups-dashboard` | Add only after repository identity exists |

Do not initialize a folder, Git repository, GitHub repository, plugin header, package manifest, or updater workflow until the user approves this identity table. The GitHub owner/repository must be verified after creation rather than inferred from the plugin name.

## Purpose

Provide one WordPress control-center screen that shows whether enrolled sites running Alynt Drime Backups Uploader are reporting healthy, redacted backup-upload status.

Version 1 should answer:

- Which sites are paired and reporting?
- Which sites are healthy, need attention, are not reporting, or have an incompatible payload?
- Which uploader version and status schema is each site using?
- Which sites have failed, queued, or active uploads?
- Which sites report source or cron warnings?
- When did the dashboard last successfully receive status for each site?

## Version 1 Non-Goals

Do not add any dashboard-to-client or dashboard-to-Drime mutation:

- no remote backup execution;
- no remote restore or restore preparation;
- no remote deletion or retention cleanup;
- no remote settings changes;
- no remote credential changes;
- no remote local-file cleanup;
- no filesystem browsing or path-mode payloads;
- no Drime API token collection, storage, or forwarding;
- no arbitrary URL health checks;
- no email, Slack, or other notification channels in the initial v1 baseline;
- no multi-tenant customer portal or non-administrator frontend.

Local dashboard actions needed to manage enrollment, polling, and retained status history are in scope. They must remain capability- and nonce-protected.

## Eventual Host And Live-Site Gate

The approved eventual target profile is:

| Field | Value |
| --- | --- |
| Site key | `control-sitesmanage` |
| Mode | `live-only` |
| Live site | `https://control.sitesmanage.com` |
| SSH alias | `admin` |
| WordPress path | `/var/www/control.sitesmanage.com/htdocs` |
| Deployment method | `scp` |

This target is recorded for architecture and rollout planning only. Do not inspect, upload, install, activate, configure, migrate, schedule, or pair anything on the live site during repository implementation.

Before the first live operation, present the exact profile above again and obtain explicit approval for the exact operation. Plugin upload, activation, database-table creation, schedule activation, and first client enrollment are separate observable live changes and must be included in that approval scope.

## Ownership Boundary

### Dashboard plugin owns

- dashboard-side site registry;
- pending enrollment records and one-time token lifecycle;
- encrypted outbound polling credentials;
- URL validation and safe HTTP transport;
- manual and scheduled read-only polls;
- latest status and retained snapshots;
- health classification and stale-site detection;
- WordPress administrator screens;
- local revocation, pause, and removal of dashboard records;
- dashboard diagnostics that never expose credentials or raw sensitive responses.

### Uploader plugin owns

- administrator opt-in on each client site;
- pairing-token entry and destination confirmation;
- a fixed read-only status endpoint, disabled by default;
- polling-credential verification and revocation;
- rate limiting;
- construction of the existing redacted status payload with path mode disabled;
- proof that no secret, filesystem path, request body, signed URL, cookie, nonce, salt, database credential, or package content enters the external payload.

The uploader change is a separate feature slice in the existing uploader repository. It needs its own plan, restore point, tests, version bump, feature reviews, and release. Do not copy uploader internals into the dashboard repository.

## Version 1 User Stories

1. As an administrator, I can register a site label, expected HTTPS URL, and environment, then generate a short-lived one-time pairing token.
2. As a client-site administrator, I can paste that token into Alynt Drime Backups Uploader, see which dashboard origin will receive the enrollment, explicitly opt in, and revoke the connection later.
3. As a dashboard administrator, I can confirm the first authenticated read-only status check before enrollment becomes active.
4. As a dashboard administrator, I can see all enrolled sites in a scannable list with a plain-language status.
5. As a dashboard administrator, I can open a site detail view for the latest redacted payload and recent status history.
6. As a dashboard administrator, I can run **Check Status Now** without triggering any backup or changing the client.
7. As a dashboard administrator, I can see when a site has stopped reporting or has an authentication, network, schema, or payload error.
8. As a dashboard administrator, I can pause polling or revoke/remove a local dashboard registration without sending a remote action.

## Pairing And Authentication Contract

### Token format and lifecycle

- Generate secrets with `random_bytes()` and at least 256 bits of entropy.
- Present one opaque, versioned token such as `adb1.<base64url-payload>` for the operator to paste into the client plugin.
- The encoded payload may carry the dashboard HTTPS origin, a random enrollment identifier, and the one-time secret. Encoding is transport, not encryption.
- Never put a pairing or polling secret in a URL, query string, browser history, diagnostic event, email, screenshot fixture, or support export.
- Store only a one-way verifier for the pending one-time secret.
- Default expiration: 15 minutes.
- Token use: single successful enrollment only. Expire it immediately on success and allow the dashboard administrator to revoke it before use.
- The dashboard must require the expected client HTTPS origin before token generation. The completing client must match that origin after canonical normalization.

### Enrollment handshake

1. Dashboard administrator creates a pending site with label, expected HTTPS origin, and environment.
2. Dashboard creates and displays the one-time pairing token once.
3. Client administrator pastes the token into the uploader, reviews the dashboard origin, checks an explicit opt-in control, and confirms pairing.
4. The uploader sends a small HTTPS POST to the fixed dashboard enrollment endpoint. It includes the one-time credential, `site_uuid`, normalized home URL, uploader version, status schema version, and its fixed status endpoint URL. It does not include the Drime token or the full status payload.
5. The dashboard validates token expiry and single use, exact expected client origin, UUID format, fixed endpoint path, and supported protocol version.
6. The dashboard creates a separate 256-bit, per-site, revocable polling credential. It stores the usable credential only through an authenticated-encryption credential vault and returns it once to the uploader.
7. The uploader stores only the credential identifier and a one-way verifier needed to authenticate future reads.
8. The dashboard performs the first authenticated GET against the fixed client status endpoint. Enrollment becomes active only if the response is valid, redacted, schema-compatible, and carries the same `site_uuid`.
9. The one-time pairing token is consumed whether the final activation succeeds or reaches a terminal failure. A retry that needs new credential material requires a newly generated token.

### Polling credential

- Scope: read-only status endpoint for one site UUID.
- Transport: HTTPS `Authorization` header; never query parameters.
- Dashboard storage: authenticated encryption at rest, isolated behind one credential-vault class. Derive the local encryption key from WordPress secret material with a documented KDF; never store the derived key in the database.
- Client storage: one-way credential verifier, credential identifier, dashboard identifier/origin, paired time, and last authenticated-read time.
- Rotation: re-pair or use a dedicated local credential-rotation flow that does not add remote control.
- Salt/key changes that make a dashboard credential undecryptable must fail closed and require re-pairing.
- Client revocation must immediately reject subsequent requests.

Version 1 uses a narrowly scoped bearer-style polling credential over HTTPS plus strict rate limiting. Do not design a generic remote command authentication framework.

## Client Status Endpoint Contract

Recommended fixed route:

```text
GET /wp-json/alynt-drime-backups-uploader/v1/status
```

Requirements:

- endpoint registration may exist after the uploader update, but access remains disabled until explicit client-site pairing;
- allow `GET` only;
- require the per-site polling credential;
- return the result of the existing health summary with `include_paths` set to `false`;
- return current status schema `1` initially;
- add response headers that discourage storage by intermediate caches;
- use compact, stable error codes without raw exception text;
- apply per-credential and per-origin/IP throttling with bounded storage;
- never return a WordPress nonce as authentication;
- never return WordPress user, plugin setting, path, log, package, or Drime response data outside the documented redacted contract;
- do not register POST, PUT, PATCH, or DELETE command routes in v1.

The dashboard must accept only documented schema-1 fields. Unknown additive fields may be retained only after a redaction-safe allowlist decision; the initial implementation should ignore them. Missing required fields or unsupported schema versions produce an `Incompatible` state rather than best-effort guessing.

## Safe Outbound Polling And SSRF Controls

The dashboard is an authenticated HTTP client and therefore needs an explicit server-side request-forgery boundary.

- Accept public HTTPS origins only in v1.
- Do not accept URL user info, fragments, IP-literal hosts, localhost names, nonstandard ports, or private, loopback, link-local, multicast, carrier-grade NAT, documentation, or reserved address ranges.
- Store a canonical origin, not an arbitrary full endpoint URL.
- Build the endpoint from the canonical origin plus the fixed uploader route.
- Validate the destination at registration, pairing completion, and every poll.
- Use WordPress safe HTTP APIs with unsafe-URL rejection enabled.
- Disable redirects; never forward the authorization header to a redirected origin.
- Send no WordPress cookies or dashboard credentials other than the site-scoped polling credential.
- Set a short timeout, a strict response-size limit, a JSON content expectation, and a conservative JSON-depth limit.
- Revalidate DNS/IP safety at request time and fail closed when resolution is ambiguous.
- Store a sanitized error code and operator-safe summary, not raw response bodies or transport traces.

## Status Payload And Health Classification

The initial consumer contract is uploader status schema `1` from `docs/STATUS_PAYLOAD.md`.

Use dashboard receive time as the authoritative `last_seen_at`. Preserve client timestamps only as status evidence; do not let clock drift determine freshness.

Classification precedence:

1. `Pending` — enrollment has not completed its first valid poll.
2. `Paused` — polling was paused locally by an administrator.
3. `Incompatible` — authentication succeeded but the payload schema or required fields are unsupported.
4. `Not reporting` — no successful poll exists within the stale threshold.
5. `Needs attention` — the latest fresh payload has failed uploads, warnings, an unreadable configured outbox, or a cron status that requires attention.
6. `Not configured` — only when the payload contract can positively prove that no supported source is configured. Do not infer this from missing optional fields.
7. `Working` — the latest payload is fresh and no attention condition applies.

Queue count alone is not automatically a failure. Add an attention condition only when successive snapshots prove that a queue is not draining for a documented threshold. An active upload is informational unless it remains unchanged beyond a documented threshold.

Schema 1 does not prove that default-path WPvivid backups were recently observed. The dashboard must not label them as recently observed unless a later uploader contract adds a redacted field and tests for it.

## Data Model

Use custom tables from the first implementation. Repeated polling history and indexed stale/attention queries are a better fit than autoloaded options.

Recommended tables:

```text
{$wpdb->prefix}alynt_drime_dashboard_sites
- id bigint unsigned primary key
- public_id char(36) unique
- site_uuid char(36) nullable unique
- site_label varchar(191)
- expected_origin varchar(255)
- environment varchar(32)
- enrollment_status varchar(32)
- pairing_secret_hash varchar(255) nullable
- pairing_expires_at datetime nullable
- polling_key_id varchar(64) nullable
- polling_secret_ciphertext longtext nullable
- plugin_version varchar(64) nullable
- payload_schema_version smallint unsigned nullable
- overall_status varchar(32)
- latest_payload_json longtext nullable
- last_poll_attempt_at datetime nullable
- last_seen_at datetime nullable
- next_poll_at datetime nullable
- consecutive_failures int unsigned
- last_error_code varchar(64) nullable
- last_error_summary text nullable
- created_at datetime
- updated_at datetime
```

```text
{$wpdb->prefix}alynt_drime_dashboard_snapshots
- id bigint unsigned primary key
- dashboard_site_id bigint unsigned
- observed_at datetime
- payload_fingerprint char(64)
- overall_status varchar(32)
- queue_count int unsigned
- uploaded_count int unsigned
- failed_count int unsigned
- active_upload tinyint(1)
- warning_count int unsigned
- cron_status varchar(64)
- payload_json longtext
```

Required indexes include sites by `site_uuid`, `enrollment_status`, `next_poll_at`, and `last_seen_at`, plus snapshots by `(dashboard_site_id, observed_at)` and `(dashboard_site_id, payload_fingerprint)`.

Database migrations must be versioned, idempotent, covered by tests, and run through WordPress activation/upgrade code. Deactivation unschedules polling but keeps data. Uninstall removes plugin-owned schedules, options, tables, pairing verifiers, and encrypted credentials.

## Polling, History, And Retention

- Default poll interval: 15 minutes.
- Default stale threshold: 60 minutes; it must never be less than three polling intervals.
- Add per-site jitter so all sites are not requested at the same second.
- Use a global scheduler lock plus a per-site poll lock with bounded expiry.
- Process a bounded number of due sites per cron run.
- Manual **Check Status Now** uses the same transport and validation pipeline as scheduled polling.
- Back off repeated network or server failures exponentially, capped at 6 hours, while preserving the visible stale state.
- Authentication failures do not retry aggressively; mark attention and require credential review or re-pairing.
- Store a snapshot on meaningful payload/status change and at most one unchanged heartbeat snapshot per hour.
- Default snapshot retention: 30 days.
- Run bounded daily cleanup in batches and never remove the latest snapshot for an enrolled site.
- Avoid overlapping polls and make late WP-Cron execution visible in dashboard diagnostics.

## WordPress Admin UI

Use server-rendered, WordPress-native admin screens first. Do not add a JavaScript framework for v1.

### Sites

Use `WP_List_Table` conventions with:

- site label and origin;
- environment;
- overall status;
- last report time;
- uploader version;
- queue, failed, and warning counts;
- cron health;
- row links for **View**, **Check Status Now**, and local **Pause/Resume**.

Include explicit empty, loading, success, error, and incompatible states. Status must use text and icons in addition to color.

### Add Site / Pairing

- collect label, expected HTTPS origin, and environment;
- generate one one-time token;
- show its expiry and explain that it is displayed once;
- never redisplay the token from stored data;
- allow local cancellation before it is used;
- clearly distinguish the pairing token from the Drime API token.

### Site Detail

- latest plain-language health summary;
- latest validated redacted fields;
- recent status timeline;
- sanitized polling failures;
- enrollment and credential state without displaying a secret;
- local actions for check, pause/resume, revoke, and remove.

### Attention Queue

Provide a filtered Sites view rather than a separate custom application. Include not-reporting, incompatible, authentication-failed, failed-upload, warning, unreadable-outbox, and cron-attention states.

All screens require a dedicated administrator capability, with `manage_options` as the initial mapping. Every state-changing local action requires a nonce and capability check. All text is translatable with text domain `alynt-drime-backups-dashboard`.

## Diagnostics And Privacy

- Log event codes, site public IDs, timing, HTTP status class, and sanitized summaries only.
- Never log or display pairing tokens, polling credentials, authorization headers, raw response bodies, local paths, Drime identifiers that are not in the approved payload, cookies, nonces, salts, or database credentials.
- Provide a redacted diagnostics summary and health checks for scheduler state, table schema, credential decryptability, and recent poll outcomes.
- Treat a remote error body as untrusted input and never persist it verbatim.
- Avoid analytics and external telemetry in v1.

## Tooling, Tests, And Release Strategy

### Initial repository baseline

- install project guardrails after creating the empty plugin folder;
- use a minimal class-based WordPress structure with prefixed classes;
- add Composer development dependencies for WPCS/PHPCS and PHPUnit-compatible unit tests;
- use Brain Monkey or the established sibling pattern for isolated WordPress behavior tests;
- add npm only for command orchestration or a real asset build need;
- do not add runtime Composer dependencies unless implementation proves they are necessary;
- add baseline diagnostics before broad feature work;
- add Alynt Plugin Updater compatibility only after the GitHub repository identity is verified.

### Dashboard test minimums

- activation and idempotent schema migration;
- token entropy, expiry, one-time use, hashing, and redaction;
- credential-vault round trip and fail-closed behavior;
- canonical URL normalization and blocked SSRF targets;
- fixed endpoint construction, redirects disabled, timeouts, and response-size limits;
- authentication, HTTP, malformed JSON, invalid field, and unsupported-schema failures;
- site UUID match enforcement;
- health-classification precedence;
- scheduler locking, batching, jitter, backoff, and stale thresholds;
- snapshot deduplication and retention;
- capability, nonce, escaping, and uninstall cleanup behavior;
- proof that secrets and raw remote bodies do not enter diagnostics.

### Uploader test minimums

- endpoint inaccessible before explicit opt-in/pairing;
- administrator-only pairing and revocation controls;
- token parsing and dashboard-origin validation;
- credential verifier storage without recoverable raw polling secret;
- missing, malformed, wrong, expired/revoked, and rate-limited credential handling;
- GET-only endpoint behavior;
- exact schema-1 redacted payload and `include_paths=false` enforcement;
- regression proof against secret- and path-like keys/values;
- cache-control behavior;
- absence of command or mutation routes.

### Distribution

- expected release tags: `vX.Y.Z`;
- expected release asset: `alynt-drime-backups-dashboard-X.Y.Z.zip`;
- ZIP top-level folder: `alynt-drime-backups-dashboard/`;
- GitHub release workflow must build from the release tag, exclude development/local files, and upload an idempotently named asset;
- configuration, ZIP validation, publication, updater runtime testing, and live deployment remain separate approval-gated workflows.

## Implementation Phases And Gates

### Phase 0 — Approve identity and protocol plan

- approve the repository/package identity table;
- approve custom tables, 15-minute polling, 60-minute stale threshold, and 30-day history defaults;
- approve the pairing/authentication shape and strict public-HTTPS-only v1 boundary;
- confirm the current uploader documentation baseline is safely committed or captured before uploader feature work.

Exit: explicit approval; no scaffold yet.

### Phase 1 — Create repository and safety baseline

- create the approved folder and Git repository;
- install project guardrails;
- add the accepted implementation plan;
- scaffold only the plugin header, loader, activation/deactivation/uninstall boundaries, and minimal test/lint commands;
- verify identity values are consistent;
- create a baseline Git commit.

Exit: minimal plugin activates in an isolated test environment and has no live deployment.

### Phase 2 — Dashboard storage and shell

- implement versioned custom-table migrations;
- implement site repository and snapshot repository;
- build Sites, Add Site, Site Detail, and Attention filter shells with fixture data;
- implement status classifier independently of HTTP transport.

Exit: UI and storage tests pass without a client endpoint.

### Phase 3 — Freeze the cross-plugin protocol

- convert the pairing, authentication, URL safety, rate-limit, payload, and error-code sections into a versioned protocol document;
- define exact request/response examples with placeholder credentials only;
- complete focused threat modeling for secret theft, replay, brute force, SSRF, DNS rebinding, redirect leakage, payload abuse, logging leakage, and compromised client behavior.

Exit: protocol is approved before either repository implements both sides.

Draft artifacts:

- `docs/PROTOCOL_V1.md`
- `docs/THREAT_MODEL_V1.md`

### Phase 4 — Add uploader opt-in and read-only endpoint

- create an external restore point for the uploader repository before broad edits;
- resolve the existing dirty documentation state without discarding user work;
- implement client pairing UI, credential verifier, revocation, rate limiting, and GET-only redacted endpoint;
- run uploader build, lint, tests, feature reviews, UI/UX review, security review, and documentation sync;
- release the uploader update separately before dashboard enrollment relies on it.

Exit: endpoint is disabled by default, authenticated when paired, read-only, redacted, released, and proven in an isolated environment.

### Phase 5 — Dashboard enrollment and manual polling

- implement pending enrollment and one-time token flow;
- implement credential vault and safe HTTP transport;
- implement first-poll activation and **Check Status Now**;
- test with two disposable WordPress environments over HTTPS or an equivalent isolated integration harness.

Exit: end-to-end pairing and manual status polling pass without scheduled polling or live-site work.

### Phase 6 — Scheduled polling and history

- implement schedule, jitter, locks, batching, backoff, snapshots, stale detection, and retention cleanup;
- add scheduler diagnostics and time-control tests.

Exit: deterministic automated tests and an isolated multi-site soak test pass.

### Phase 7 — Operator UI and observability completion

- finish Sites, Site Detail, and Attention views;
- add accessible states and plain-language error guidance;
- complete redacted diagnostics and support output;
- run feature light, bloat/structure, UI/UX, security, and documentation-sync workflows.

Exit: v1 feature scope is complete and no remote action exists.

### Phase 8 — Pre-release and local/staging acceptance

- run the toolkit full pre-release workflow at `C:\Users\Captain\Documents\AI Workflows\Toolkits\wp-plugin-toolkit\d4-prompts\ds3-pre-release\FULL_PRE_RELEASE_WORKFLOW_PROMPT.md` using `@FULL_PRE_RELEASE_WORKFLOW_PROMPT.md run`;
- create and inspect the release ZIP;
- test clean activation, upgrade, deactivation, reactivation, and uninstall in disposable WordPress environments;
- test native Alynt Plugin Updater behavior after a release candidate exists;
- verify the dashboard cannot pair arbitrary private-network targets and cannot expose credentials.

Exit: release candidate accepted; still no `control-sitesmanage` change.

### Phase 9 — Approval-gated live rollout

- reconfirm `control-sitesmanage live-only` target details;
- request explicit approval for the exact live upload/install/activate/migrate/schedule scope;
- take a site/database backup or host-level restore point appropriate for the live target before activation;
- deploy one reviewed release artifact;
- verify activation, tables, scheduler, admin access, and diagnostics;
- pair one low-risk known client first and observe before expanding.

Exit: live rollout verified and separately documented.

## Restore-Point Recommendation

No restore point is needed merely to review this planning document, and none was created in this pass.

Before edit-heavy work:

1. Run `@RESTORE_POINT_PROMPT.md run` against `C:\Development\WordPress\Plugins\alynt-drime-backups-uploader` before implementing the client endpoint or pairing UI. The default external snapshot root is `C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots`.
2. Confirm the uploader documentation baseline and preserve any later uncommitted changes before the implementation branch begins.
3. In the new dashboard repository, create a clean baseline commit after minimal scaffolding, then create an external restore point before the first broad storage, enrollment, or polling implementation batch.
4. Before activation on `control.sitesmanage.com`, use an appropriate live-site and production-database backup/restore point in addition to repository snapshots. A source restore point does not protect WordPress runtime data.

## Version 1 Acceptance Criteria

Version 1 is complete only when:

- identity values and release packaging are consistent;
- enrollment is explicit, one-time, expiring, and revocable;
- the client endpoint is disabled before opt-in and accepts GET only;
- the dashboard can poll only a predeclared public HTTPS origin through a fixed route;
- polling credentials are site-scoped, protected at rest, never logged, and revocable;
- the external payload matches the redacted schema contract and contains no path or secret material;
- malformed, oversized, redirected, unsafe-destination, authentication, rate-limit, and incompatible-schema cases fail safely;
- scheduled and manual polling share one validated transport path;
- stale, attention, incompatible, pending, paused, and working states are deterministic and tested;
- status history is bounded and cleanup is tested;
- the UI is accessible, translatable, and understandable without reading logs;
- dashboard, uploader, isolated integration, release-package, and updater tests pass;
- no remote action route, Drime credential field, or client mutation exists;
- no live deployment occurs without a new explicit `control-sitesmanage live-only` confirmation.

## Decisions Required Before Scaffolding

Approve or revise:

1. the exact repository/package identity table;
2. custom tables from v1;
3. the 15-minute poll, 60-minute stale, and 30-day snapshot-retention defaults;
4. public HTTPS client origins only in v1;
5. the one-time enrollment plus separate revocable polling credential model;
6. GitHub/Alynt Plugin Updater distribution after repository creation.

Until these decisions are confirmed, the next state remains **Phase 0 — Intake**, and scaffolding must not start.
