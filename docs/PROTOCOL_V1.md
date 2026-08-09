# Alynt Drime Backups Dashboard Protocol v1

Status: approved Phase 3 baseline as of 2026-08-09. Implementation remains separately approval-gated.

This document freezes the first cross-plugin protocol between Alynt Drime Backups Dashboard and Alynt Drime Backups Uploader. Version 1 is read-only relative to client sites and Drime.

## Non-Negotiable Boundary

- The dashboard may enroll, poll, classify, and retain redacted status.
- The uploader may expose one authenticated, GET-only, read-only status endpoint after explicit administrator opt-in.
- Version 1 must not create backups, restore backups, delete backups, clean up files, change settings, rotate Drime credentials, expose Drime tokens, browse files, or run arbitrary commands.
- All credentials are placeholder-only in examples. Do not copy examples into fixtures with real secrets.

## Actors And Origins

| Actor | Responsibility |
| --- | --- |
| Dashboard administrator | Creates a pending dashboard site and generates a one-time pairing token. |
| Client-site administrator | Pastes the token into the uploader, reviews the dashboard origin, and explicitly opts in. |
| Dashboard plugin | Validates enrollment, stores dashboard-owned state, and polls the fixed client status route. |
| Uploader plugin | Stores a verifier for the polling credential and returns a redacted schema-1 status payload. |

Public HTTPS origins only are allowed in v1. Store canonical origins, not arbitrary endpoint URLs.

## Canonical Origin Rules

Both plugins must normalize origins before comparison or storage:

- scheme must be `https`;
- host must be lower-case IDNA ASCII where applicable;
- default HTTPS port `443` is omitted;
- path, query, fragment, and user info are not part of the origin;
- localhost, IP literals, nonstandard ports, private/reserved address ranges, and ambiguous DNS resolution fail closed.

The dashboard builds the client status endpoint from:

```text
{canonical_client_origin}/wp-json/alynt-drime-backups-uploader/v1/status
```

The uploader enrollment request goes to:

```text
{canonical_dashboard_origin}/wp-json/alynt-drime-backups-dashboard/v1/enroll
```

## Pairing Token

Dashboard displays the pairing token once:

```text
adb1.<base64url-json-payload>
```

Decoded payload shape:

```json
{
  "protocol_version": 1,
  "enrollment_id": "00000000-0000-4000-8000-000000000000",
  "dashboard_origin": "https://control.sitesmanage.com",
  "expected_client_origin": "https://client.example.com",
  "secret": "<one-time-256-bit-secret>",
  "expires_at": "2026-08-09T20:15:00Z"
}
```

Rules:

- `secret` must have at least 256 bits of entropy from `random_bytes()`.
- `expires_at` default is 15 minutes after issue.
- The dashboard stores only a one-way verifier for the secret.
- The token is consumed by one terminal enrollment attempt. Retrying with credential material requires a newly generated token.
- Pairing tokens and polling credentials must never appear in URLs, query strings, logs, diagnostics, emails, screenshots, or support exports.

## Enrollment Request

The uploader sends one enrollment request after administrator opt-in:

```http
POST /wp-json/alynt-drime-backups-dashboard/v1/enroll HTTP/1.1
Host: control.sitesmanage.com
Authorization: Bearer <one-time-pairing-secret>
Content-Type: application/json
Accept: application/json
Cache-Control: no-store
```

```json
{
  "protocol_version": 1,
  "enrollment_id": "00000000-0000-4000-8000-000000000000",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "home_url": "https://client.example.com",
  "status_endpoint": "https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status",
  "uploader_version": "0.0.0-example",
  "status_schema_version": 1
}
```

Dashboard validation:

- supported `protocol_version`;
- valid, unexpired, unused enrollment record;
- constant-time verifier comparison for the one-time secret;
- exact canonical-origin match for `home_url` and the dashboard-stored expected origin;
- fixed status endpoint path;
- valid `site_uuid`;
- supported uploader status schema;
- no redirects, unsafe URLs, or non-JSON responses during the first poll.

## Enrollment Response

On success, the dashboard creates a separate per-site polling credential and returns it once:

```http
HTTP/1.1 201 Created
Content-Type: application/json
Cache-Control: no-store
```

```json
{
  "protocol_version": 1,
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "polling_key_id": "pk_example_0000000000000000",
  "polling_secret": "<polling-256-bit-secret>",
  "polling_auth_scheme": "Bearer adb-poll-v1.<key_id>.<secret>",
  "first_poll_required": true
}
```

Storage rules:

- Dashboard stores the usable polling secret only through an authenticated-encryption credential vault.
- Client stores only the key ID, dashboard origin, credential verifier, paired time, and last authenticated-read time.
- Salt or key changes that make dashboard credentials undecryptable must fail closed and require re-pairing.
- Client revocation immediately rejects future polling requests.

Enrollment becomes active only after the dashboard completes one valid authenticated status poll for the same `site_uuid`.

## Polling Request

Dashboard polls the client status endpoint:

```http
GET /wp-json/alynt-drime-backups-uploader/v1/status HTTP/1.1
Host: client.example.com
Authorization: Bearer adb-poll-v1.pk_example_0000000000000000.<polling-secret>
Accept: application/json
Cache-Control: no-store
```

Transport requirements:

- use WordPress safe HTTP APIs;
- disable redirects;
- do not send dashboard cookies, WordPress nonces, or unrelated headers;
- use a short timeout;
- enforce a response-size limit and JSON depth limit;
- revalidate DNS/IP safety at request time;
- store sanitized error codes and summaries only.

## Status Response

The uploader returns status schema `1` from the existing uploader `docs/STATUS_PAYLOAD.md`, with path mode disabled:

```http
HTTP/1.1 200 OK
Content-Type: application/json
Cache-Control: no-store
```

```json
{
  "schema_version": 1,
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "plugin_version": "0.0.0-example",
  "queue_count": 0,
  "uploaded_count": 12,
  "failed_count": 0,
  "active_upload": false,
  "auto_scan_enabled": true,
  "server_cron_expected": false,
  "server_outbox_configured": true,
  "server_outbox_readable": true,
  "wpvivid_override_configured": false,
  "old_wpvivid_uploader_active": false,
  "wp_cron_disabled": false,
  "cron_status": "ok",
  "cron_reason": "Scheduled scans are available.",
  "warning_count": 0,
  "warnings": [],
  "last_runner": "wp_cron",
  "last_runner_at": 1786305600,
  "last_scheduled_scan_at": 1786305600,
  "last_wp_cli_scan_at": 0
}
```

Required dashboard checks:

- `schema_version` is supported;
- `site_uuid` matches the enrolled site;
- required fields are present and typed;
- path-mode fields such as `server_outbox_path` and `backup_path_override` are rejected for dashboard ingestion;
- warning records use stable codes and operator-safe messages;
- unknown additive fields are ignored unless explicitly allowlisted later.

Dashboard receive time is authoritative for `last_seen_at`. Client timestamps are only status evidence.

## Error Codes

All errors use compact stable codes and operator-safe summaries. Raw exception text, raw response bodies, stack traces, SQL, paths, credentials, authorization headers, cookies, nonces, and Drime data are forbidden.

| Code | Producer | Meaning | Dashboard state |
| --- | --- | --- | --- |
| `pairing_expired` | Dashboard | One-time pairing token expired. | Pending / attention |
| `pairing_used` | Dashboard | One-time pairing token was already consumed. | Pending / attention |
| `pairing_invalid` | Dashboard | Pairing verifier failed. | Pending / attention |
| `origin_mismatch` | Dashboard | Client origin does not match the pending record. | Pending / attention |
| `endpoint_invalid` | Dashboard | Status endpoint is not the fixed route. | Pending / attention |
| `protocol_unsupported` | Either | Protocol version is unsupported. | Incompatible |
| `auth_missing` | Uploader | Polling credential missing. | Needs attention |
| `auth_invalid` | Uploader | Polling credential invalid or revoked. | Needs attention |
| `rate_limited` | Uploader | Credential or origin/IP exceeded rate limits. | Needs attention |
| `schema_unsupported` | Dashboard | Status schema is unsupported. | Incompatible |
| `payload_invalid` | Dashboard | Required field missing, wrong type, or unsafe content. | Incompatible |
| `site_uuid_mismatch` | Dashboard | Response UUID does not match enrollment. | Incompatible |
| `destination_unsafe` | Dashboard | URL/DNS/IP safety check failed. | Needs attention |
| `transport_failed` | Dashboard | Network, TLS, DNS, timeout, or HTTP failure. | Not reporting / needs attention |
| `response_too_large` | Dashboard | Response exceeded size limit. | Incompatible |
| `json_invalid` | Dashboard | Response was not valid JSON. | Incompatible |

## Rate Limits

Uploader must rate limit the status route:

- per polling key ID;
- per observed origin/IP where available;
- bounded storage with automatic expiry;
- safe default failure as `429 rate_limited`;
- no lockout that requires remote dashboard action to recover.

Dashboard scheduled polling defaults:

- 15-minute poll interval;
- 60-minute stale threshold, never less than three intervals;
- per-site jitter;
- bounded due-site batch size;
- exponential backoff for repeated network/server failures, capped at 6 hours;
- authentication failures do not retry aggressively.

## Versioning And Compatibility

- Protocol version is `1`.
- Client status schema version is initially `1`.
- Unsupported protocol or schema versions must not be guessed.
- Additive payload fields are ignored until allowlisted.
- Breaking changes require a new protocol or schema version and migration notes in both repositories.
