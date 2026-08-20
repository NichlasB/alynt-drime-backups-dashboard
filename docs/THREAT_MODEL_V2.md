# Alynt Drime Backups Dashboard Threat Model v2

Status: draft planning baseline for V2.1 design review. This document does not approve runtime implementation, release, deployment, or live-site enablement.

Scope: V2.1 `scan_upload_now` remote-action planning for Alynt Drime Backups Dashboard and Alynt Drime Backups Uploader.

This model is additive to `docs/THREAT_MODEL_V1.md`. V1 read-only pairing and status polling remain in force.

## Assets

| Asset | Protection goal |
| --- | --- |
| Action private signing key | Dashboard-only, encrypted at rest, never displayed, logged, exported, or sent to clients. |
| Action public key | Client-side verifier only, bound to one v1 pairing, revocable, not accepted across sites. |
| Action opt-in token | Short-lived, single setup flow, no private key or Drime credential. |
| Action intent body | Site-scoped, action-scoped, signed, short-lived, idempotent, no arbitrary inputs. |
| Client backup execution state | Protected from duplicate work, overload, replay, and unsafe action expansion. |
| Action history | Durable enough for support, redacted enough for screenshots and export. |
| Drime credentials | Never stored, requested, displayed, signed, or transported by the dashboard. |
| Client backup evidence | Reported as redacted counts and states, not paths, package names, object IDs, or signed URLs. |

## Trust Boundaries

- Dashboard administrators are trusted only after WordPress capability and nonce checks.
- Client-site administrators must explicitly opt in to remote actions after v1 pairing.
- V1 polling credentials prove only read access and must not authorize V2 actions.
- V2 signed intents are remote untrusted input until client signature, freshness, idempotency, pairing, action allowlist, and local policy checks pass.
- Client action-result payloads are untrusted remote data until dashboard validation and redaction checks pass.
- WordPress options, custom tables, diagnostics, browser screens, and support exports are not safe places for secrets.

## Threats And Required Controls

| Threat | Scenario | Required controls | Verification target |
| --- | --- | --- | --- |
| V1 credential privilege escalation | A read-only polling secret is used to run remote actions. | Separate V2 opt-in, separate signing key, action endpoint rejects polling auth as authorization. | Client auth tests. |
| Action private key theft | Private signing key leaks from database, logs, export, or UI. | Credential-vault storage; never display after creation; redaction tests; support export denylist. | Dashboard storage and export tests. |
| Opt-in confusion | Client admin thinks read-only monitoring opt-in also enables remote actions. | Separate V2 token, separate checkbox, plain action list, disabled by default. | Client UI/UX and state tests. |
| Cross-site confused deputy | Dashboard action intended for one site is accepted by another paired site. | Bind action key, dashboard site public ID, site UUID, expected client origin, and route. | Signature and mismatch tests. |
| Replay | Captured action request is resent later. | Five-minute expiry, signed timestamp, idempotency key retention, conflicting replay rejection. | Replay/idempotency tests. |
| Duplicate heavy work | Operator double-clicks or two requests overlap. | Dashboard dispatch lock; client one-running-action lock; duplicate idempotency returns prior state. | Lock and duplicate-submit tests. |
| Resource exhaustion | Remote action causes repeated scans/uploads across many sites. | Per-site minimum interval, bounded worker, queue limits, upload locks, no automatic retry of accepted actions. | Rate-limit and soak tests. |
| Action type expansion | `scan_upload_now` grows into arbitrary backup creation or command execution. | Stable action allowlist; reject unknown fields; no command strings; separate review for `server_backup_now` or `wpvivid_backup_now`. | Payload validation and code review gate. |
| Path or package disclosure | Action result exposes local paths, filenames, package names, backup IDs, or Drime IDs. | Redacted counts only; forbidden-key and forbidden-value checks; no raw result bodies. | Result validation and redaction tests. |
| Drime credential centralization | Dashboard asks for or stores Drime token to perform backup work centrally. | Architecture rule: client executes with local credentials; dashboard never has Drime token fields. | Static review and settings/export tests. |
| SSRF or arbitrary URL | Action payload includes a URL or endpoint for client/dashboard to call. | No arbitrary URL fields in V2.1; fixed action route built from v1 canonical origin. | Payload schema tests. |
| Signature canonicalization bug | Dashboard signs one body but client verifies another representation. | Deterministic JSON body, signed body hash, method, route, origin, signed-at. | Cross-plugin canonical fixture tests. |
| Stale accepted action ambiguity | Client accepts action but status never confirms completion. | Dashboard stale-action timeout; normal polling reconciliation; visible stale state. | State transition tests. |
| Compromised client lies | Client reports success without actually uploading. | Treat action result as operational claim only; backup freshness remains based on normal redacted source evidence. | Classifier and UI tests. |
| Compromised dashboard overloads clients | Dashboard sends valid but excessive requests. | Client-side opt-in, rate limit, one-running-action lock, local disable/revoke. | Client policy tests. |
| Rollback vulnerability | Old plugin version accepts action with weaker validation. | Capability version reporting; action controls hidden for incompatible clients; release order uploader first. | Compatibility tests. |
| Logging leakage | Raw request, signature, key, response body, path, or package data enters logs. | Stable codes, operator-safe summaries, redactor denylist, no raw body persistence. | Diagnostics and audit tests. |

## Abuse Cases Explicitly Out Of Scope For V2.1

These must remain impossible in V2.1 code and UI:

- arbitrary command execution;
- forcing WPvivid to create a fresh backup;
- forcing a server runner to create a fresh package;
- restore preparation or restore execution;
- backup deletion;
- retention cleanup;
- local file cleanup;
- schedule changes;
- settings changes;
- Drime credential updates;
- dashboard-side Drime API use;
- arbitrary URL checks;
- filesystem browsing;
- accepting paths, package names, backup IDs, or Drime object IDs from the dashboard.

## Required Controls By Component

### Dashboard

- Generate one action key pair per enrolled site.
- Store the private key only with the credential vault.
- Display action controls only when the latest status payload declares V2 capability and local client opt-in.
- Require `manage_options` or the plugin capability plus nonce for every operator request.
- Record action state transitions in a dedicated action table.
- Sign only canonical allowlisted action bodies.
- Use one dispatch lock per site.
- Run a normal status poll after dispatch when safe.
- Mark accepted/running actions stale when no fresh client status confirms progress.
- Keep support export redacted and aggregate-first.

### Uploader

- Keep remote actions disabled by default.
- Require existing v1 pairing before accepting V2 opt-in.
- Store only the dashboard action public key and local action policy.
- Verify signature, key ID, timestamp, body hash, route, origin, site UUID, dashboard site public ID, action type, expiry, and idempotency key before work is queued.
- Keep idempotency records for at least 24 hours.
- Enforce one running action per site.
- Enforce default 60-minute minimum interval for `scan_upload_now`.
- Run work in a local worker rather than a long REST request.
- Use existing scanner, queue, registry, and upload worker behavior.
- Report only redacted counts and stable state/result codes.
- Allow the local admin to disable or revoke remote actions without contacting the dashboard.

## Fail-Closed Rules

The client must reject the action intent when:

- remote actions are not opted in;
- Sodium signing support is unavailable;
- action key ID is missing, unknown, or revoked;
- signature verification fails;
- route, method, origin, body hash, timestamp, site UUID, or dashboard site public ID does not match;
- request is expired or too far in the future;
- idempotency key is missing or conflicts with prior input;
- action type is unknown or not allowlisted;
- local locks or rate limits block execution;
- required local state cannot be persisted.

The dashboard must reject or hide action controls when:

- latest client status lacks V2 capability;
- capability does not include `scan_upload_now`;
- client reports remote actions disabled;
- dashboard cannot decrypt the action private key;
- client response is redirected, oversized, malformed, unsafe, or not JSON;
- action result contains forbidden fields or unsafe values.

## Verification Minimums

Dashboard:

- key generation and credential-vault round trip;
- private key never appears in diagnostics, support export, action table context, or rendered UI;
- action controls hidden for v1-only clients;
- capability parsing rejects unsafe keys and values;
- nonce/capability enforcement;
- deterministic signing fixtures;
- state transitions for accepted, rejected, busy, rate-limited, failed, timed out, stale;
- support export redacts signatures, keys, paths, package names, Drime identifiers, and raw responses.

Uploader:

- remote actions disabled before explicit opt-in;
- v1 polling credential does not authorize action endpoint;
- opt-in token validation binds dashboard origin, expected client origin, dashboard site public ID, and site UUID;
- invalid signatures, expired intents, future timestamps, wrong route, wrong body hash, wrong site UUID, wrong dashboard site public ID, and unknown action type fail closed;
- duplicate idempotency returns prior state without rerun;
- `scan_upload_now` uses existing scan/queue/upload scheduling and accepts no arbitrary paths;
- lock/rate-limit behavior is deterministic;
- status reports redacted action summaries only.

Cross-plugin:

- v1-only site remains monitored and action controls stay hidden;
- one action-enabled disposable site accepts a single `scan_upload_now`;
- dashboard records accepted/running/final state;
- backup freshness changes only after normal status evidence changes;
- client-side disable/revoke immediately prevents future actions.

## Release And Rollout Constraints

- Uploader must release first, with remote actions disabled by default.
- Dashboard release may include hidden controls, but controls must remain unavailable until client capability is visible.
- Live rollout must start with one low-risk client.
- No production client should opt in until release packages, updater behavior, tests, and rollback guidance are verified.
- No destructive or persistent action slice may proceed until V2.1 and action history hardening are proven.

## Approval Gate

Before any V2 runtime implementation:

- approve `docs/PROTOCOL_V2.md`;
- approve this threat model;
- approve `scan_upload_now` as the first V2.1 action;
- create or confirm restore points for dashboard and uploader repositories;
- create focused implementation tickets/checklists for both repositories;
- keep live `control-sitesmanage` unchanged until a separate release/deploy approval gate.
