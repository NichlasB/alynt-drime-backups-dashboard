# Alynt Drime Backups Dashboard Threat Model v1

Status: approved Phase 3 baseline as of 2026-08-09. Implementation remains separately approval-gated.

Scope: v1 pairing, opt-in, authenticated dashboard polling, status ingestion, status classification, storage, diagnostics, and operator UI. This model does not authorize remote actions, live deployment, or uploader implementation work.

## Assets

| Asset | Protection goal |
| --- | --- |
| Pairing token secret | One-time use, short lifetime, no persistence as plaintext, no logging. |
| Polling credential | Site scoped, revocable, protected at rest on the dashboard, verifier-only on the client. |
| Client status payload | Redacted, schema validated, no paths or secrets, bounded retention. |
| Dashboard site registry | Administrator-only access, accurate origin binding, safe cleanup. |
| Operator diagnostics | Useful without exposing credentials, raw responses, paths, SQL, or private records. |
| Client site availability | Polling and rate limits must not create avoidable load or lockouts. |

## Trust Boundaries

- Dashboard administrator input is trusted only after capability, nonce, validation, and normalization.
- Client-site administrator opt-in is required before the uploader exposes status externally.
- Client status responses are remote untrusted input even after authentication.
- DNS and HTTPS transport are untrusted until safety checks complete at request time.
- WordPress options, custom tables, logs, and diagnostics are not safe places for plaintext secrets.

## Threats And Required Controls

| Threat | Scenario | Required controls | Verification target |
| --- | --- | --- | --- |
| Pairing token theft | Token copied from screen, history, logs, screenshots, or support output. | Display once; 15-minute expiry; one-time terminal consumption; store only verifier; redact from logs/diagnostics. | Token lifecycle tests and redaction tests. |
| Pairing replay | An attacker reuses a valid or previously used token. | Consumed state; expiry check; constant-time verifier comparison; exact expected-origin binding; new token required for retry. | Enrollment integration tests. |
| Brute force | Repeated pairing or polling guesses. | 256-bit secrets; rate limiting; bounded failure counters; generic errors; no verifier oracle. | Auth failure and rate-limit tests. |
| Credential leakage | Polling credential appears in URLs, request logs, diagnostics, or database plaintext. | Authorization header only; no query credentials; encrypted dashboard storage; client verifier-only storage; diagnostics redaction. | Secret scan, diagnostics tests, storage review. |
| SSRF | Dashboard is tricked into polling internal, localhost, metadata, or private-network targets. | Public HTTPS origins only; canonical origin storage; fixed endpoint construction; reject IP literals/private/reserved ranges; DNS/IP revalidation at every poll. | URL normalization and blocked-destination tests. |
| DNS rebinding | Safe host later resolves to unsafe IP. | Re-resolve and revalidate at poll time; fail closed on ambiguity; disable redirects. | Poll transport tests with resolver fixtures. |
| Redirect leakage | Authorization header sent to redirected or attacker-controlled origin. | Disable redirects; never forward authorization to redirects; mark sanitized transport error. | HTTP transport tests. |
| Payload abuse | Client returns huge, malformed, recursive, hostile, or unexpected JSON. | Response-size limit; JSON depth limit; strict schema/types; ignore unknown fields; reject path-mode fields. | Payload validation tests. |
| Schema confusion | Dashboard guesses from unsupported or incomplete payloads. | Supported schema allowlist; required-field validation; unsupported/missing fields become incompatible. | Compatibility tests. |
| Site impersonation | One client returns another site's UUID or endpoint. | Match enrolled `site_uuid`; bind credential to one site; bind endpoint to canonical origin. | UUID mismatch tests. |
| Logging leakage | Raw errors, response bodies, headers, paths, or SQL enter logs/diagnostics. | Stable error codes; operator-safe summaries; explicit redaction; no raw remote body persistence. | Redaction regression tests. |
| Compromised client | A paired client lies, sends warnings with sensitive content, or tries to influence UI. | Treat payload as untrusted; sanitize and escape all output; allowlist warning fields; classify conservatively; no remote actions exist. | Render escaping and payload validation tests. |
| Dashboard compromise blast radius | A dashboard credential is stolen or decrypted. | Credential is read-only and site-scoped; client can revoke; no command routes; no Drime token in dashboard. | Scope review and revocation tests. |
| Scheduler overload | Polling too many clients harms dashboard or clients. | Jitter; bounded batches; locks; backoff; 15-minute default interval; 60-minute stale threshold. | Scheduler and lock tests. |
| Retention/privacy drift | Status history accumulates indefinitely or includes sensitive accidental fields. | 30-day retention; bounded cleanup; latest snapshot preserved; payload allowlist; path/secret rejection. | Retention and payload filtering tests. |

## Abuse Cases Explicitly Out Of Scope For v1

These must remain impossible in code and UI:

- remote backup creation;
- remote restore or restore preparation;
- remote delete, retention cleanup, or local file cleanup;
- remote settings changes;
- Drime credential collection, viewing, rotation, or forwarding;
- arbitrary URL health checks;
- arbitrary command routes;
- unauthenticated dashboard status ingestion.

## Fail-Closed Rules

The dashboard must not activate or continue normal polling when:

- a destination fails URL/DNS/IP safety checks;
- a polling credential cannot be decrypted;
- the client redirects;
- the response exceeds the size limit;
- JSON cannot be parsed safely;
- schema version is unsupported;
- required fields are absent or incorrectly typed;
- `site_uuid` does not match the enrolled site.

The visible result should be a stable status such as `Incompatible`, `Not reporting`, or `Needs attention` with a sanitized error code.

## Required Approval Gates

- Approve this protocol/threat model before implementing both sides.
- Create a fresh uploader restore point before editing the uploader pairing UI or endpoint.
- Obtain explicit live-site approval before any `control-sitesmanage live-only` upload, activation, migration, schedule, or enrollment.
