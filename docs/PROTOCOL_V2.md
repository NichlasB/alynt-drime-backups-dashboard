# Alynt Drime Backups Dashboard Protocol v2

Status: V2.1 protocol baseline. The local action opt-in token foundation has been implemented, but signed action dispatch, the client action-intent endpoint, release, deployment, and live-site enablement remain unapproved and unimplemented.

This document defines the proposed cross-plugin protocol for the first remote-action slice between Alynt Drime Backups Dashboard and Alynt Drime Backups Uploader.

Implementation planning for the next signed dispatch slice is tracked in `docs/V2_1_SIGNED_DISPATCH_IMPLEMENTATION_PLAN.md`.

Version 2 is additive to the version 1 read-only pairing and polling protocol. A site may remain fully valid as a v1-only monitored site without supporting this protocol.

## Non-Negotiable Boundary

- V1 pairing and status polling remain read-only.
- V2 remote actions require a separate client-side opt-in after v1 pairing.
- The dashboard must not store Drime API credentials.
- The dashboard must not send shell commands, filesystem paths, package names, raw backup IDs, Drime object IDs, signed URLs, or arbitrary settings payloads.
- The client uploader remains the only system that can execute backup-related work, and it uses its own local settings, credentials, locks, and policy.
- V2.1 initially allows only `scan_upload_now`.
- Fresh server-runner or WPvivid backup creation is not part of the initial V2.1 action unless a later client capability explicitly declares and safely implements it.

## Actors And Responsibilities

| Actor | Responsibility |
| --- | --- |
| Dashboard administrator | Requests a backup-related action for an enrolled site and reviews action history. |
| Client-site administrator | Explicitly opts in to remote actions and chooses allowed action types on the client site. |
| Dashboard plugin | Creates per-site signed action intents, stores local action records, dispatches intents, and polls status for redacted results. |
| Uploader plugin | Verifies signed intents, enforces local opt-in, idempotency, locks and rate limits, executes allowed local workflows, and reports redacted action summaries. |

## Version Relationship

Protocol v2 depends on a valid v1 enrollment for the same client origin and `site_uuid`.

The v1 polling credential is still used only for read-only status polling. It must not be treated as authorization to run remote actions.

V2 introduces a separate action signing key and a separate client opt-in state.

## Action Capability Discovery

The authenticated v1 status payload may include an optional redacted `remote_actions` object. It is absent for v1-only clients.

Recommended shape:

```json
{
  "remote_actions": {
    "protocol_version": 2,
    "enabled": true,
    "key_id": "ak_example_0000000000000000",
    "allowed_actions": ["scan_upload_now"],
    "sodium_available": true,
    "min_interval_seconds": 3600,
    "one_running_action_per_site": true,
    "last_action": {
      "action_id": "00000000-0000-4000-8000-000000000000",
      "action_type": "scan_upload_now",
      "state": "succeeded",
      "requested_at": "2026-08-20T10:00:00Z",
      "completed_at": "2026-08-20T10:03:00Z",
      "result_code": "scan_upload_completed",
      "result_summary": "Scan/upload request completed.",
      "counts": {
        "found": 2,
        "queued": 1,
        "already_known": 1,
        "upload_attempted": 1,
        "failed": 0
      }
    }
  }
}
```

Dashboard ingestion rules:

- Treat `remote_actions` as optional and backward-compatible.
- Accept only documented scalar fields and bounded string arrays.
- Accept only documented action types and states.
- Reject any nested key or value that looks like a path, credential, token, raw package name, raw Drime identifier, signed URL, raw response body, SQL, salt, cookie, nonce, or command.
- Do not infer backup freshness from action state. Freshness still comes from backup-source evidence in the normal status payload.

## Client Action Opt-In

Remote actions remain disabled until the client administrator explicitly opts in.

Recommended flow:

1. Dashboard administrator opens the enrolled site detail screen.
2. Dashboard creates a per-site action signing key pair.
3. Dashboard stores the private key through the credential vault with a site-specific action context.
4. Dashboard displays an action opt-in token once.
5. Client administrator pastes the token into the uploader dashboard-connection screen.
6. Client validates that the token matches the existing v1 dashboard origin, expected client origin, dashboard site public ID, and site UUID.
7. Client administrator confirms a separate remote-action opt-in checkbox.
8. Client stores the dashboard action public key, key ID, allowed actions, opt-in timestamp, and local policy settings.
9. Client reports capability through the optional `remote_actions` status summary.

Recommended opt-in token prefix:

```text
adb2a.<base64url-json-payload>
```

Decoded payload shape:

```json
{
  "protocol_version": 2,
  "purpose": "remote_action_opt_in",
  "dashboard_origin": "https://control.sitesmanage.com",
  "expected_client_origin": "https://client.example.com",
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "action_key_id": "ak_example_0000000000000000",
  "action_public_key": "<base64url-ed25519-public-key>",
  "allowed_actions": ["scan_upload_now"],
  "expires_at": "2026-08-20T10:15:00Z"
}
```

The action public key is not a secret, but the token should still expire quickly, be shown once, and be omitted from logs, diagnostics, screenshots, and support exports.

## Signing

Use Ed25519 through PHP Sodium.

If Sodium is unavailable on either side:

- dashboard must not enable V2 action dispatch for that site;
- client must report `sodium_available: false`;
- v1 read-only monitoring must continue to work.

Recommended request headers:

```http
X-Adbd-Action-Key-Id: ak_example_0000000000000000
X-Adbd-Action-Signature: <base64url-ed25519-signature>
X-Adbd-Action-Signed-At: 2026-08-20T10:00:00Z
Content-Type: application/json
Accept: application/json
Cache-Control: no-store
```

Canonical signing input:

```text
ADB-ACTION-V2
POST
/wp-json/alynt-drime-backups-uploader/v2/action-intents
<canonical-client-origin>
<sha256-hex-of-json-body>
<signed-at-iso8601>
```

Rules:

- JSON body encoding must be deterministic for signing.
- Request expiry defaults to 5 minutes.
- Client must reject signatures for the wrong key ID, route, method, origin, body fingerprint, timestamp, `site_uuid`, or dashboard site public ID.
- Client must use constant-time comparison for fixed-length hashes and identifiers where available.

## Action Intent Endpoint

Recommended route:

```text
POST /wp-json/alynt-drime-backups-uploader/v2/action-intents
```

Initial allowed request body:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "action_type": "scan_upload_now",
  "requested_at": "2026-08-20T10:00:00Z",
  "expires_at": "2026-08-20T10:05:00Z",
  "idempotency_key": "adb-act-example-0000000000000000"
}
```

Forbidden request fields:

- command strings;
- paths;
- package names;
- backup set IDs;
- Drime object IDs;
- Drime credentials;
- retention/delete scopes;
- schedule changes;
- restore targets;
- arbitrary URLs.

## Initial Action Type

`scan_upload_now`

Meaning:

- Client scans existing configured producers for ready backup packages.
- Client queues eligible packages that are not already uploaded or failed under existing local registry rules.
- Client triggers or schedules the existing upload worker for eligible queued work.
- Client records redacted counts and action state.

Not included:

- forcing WPvivid to create a backup;
- forcing a server backup runner to create a package;
- changing schedules;
- cleaning local files;
- deleting remote files;
- restoring backups;
- changing Drime settings or credentials.

## Action Response

Recommended success response:

```http
HTTP/1.1 202 Accepted
Content-Type: application/json
Cache-Control: no-store
```

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "state": "accepted",
  "result_code": "accepted",
  "result_summary": "Scan/upload request accepted for local processing.",
  "retry_after_seconds": 0
}
```

Recommended terminal rejection response:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "state": "rate_limited",
  "result_code": "action_rate_limited",
  "result_summary": "A recent backup request is still inside the local minimum interval.",
  "retry_after_seconds": 1800
}
```

Allowed states:

- `queued_for_dispatch`
- `dispatch_failed`
- `accepted`
- `rejected`
- `unsupported`
- `rate_limited`
- `busy`
- `running`
- `succeeded`
- `failed`
- `timed_out`
- `stale`

## Idempotency And Rate Limits

Client requirements:

- retain idempotency keys for at least 24 hours;
- duplicate idempotency key must return the original action state rather than running work again;
- allow at most one running remote action per site;
- default minimum interval for `scan_upload_now`: 60 minutes;
- local administrator may disable remote actions at any time;
- disabled or revoked action state must fail closed.

Dashboard requirements:

- one dispatch lock per site;
- one in-flight action per site until terminal or stale;
- no automatic retry for accepted actions;
- stale accepted/running action threshold must be visible to operators.

## Error Codes

| Code | Producer | Meaning |
| --- | --- | --- |
| `action_protocol_unsupported` | Client | Protocol version is unsupported. |
| `action_not_enabled` | Client | Remote actions are not opted in locally. |
| `action_key_unknown` | Client | Action key ID is unknown or revoked. |
| `action_signature_invalid` | Client | Signature verification failed. |
| `action_expired` | Client | Request was outside the accepted freshness window. |
| `action_replay` | Client | Idempotency key was already seen for conflicting input. |
| `action_site_mismatch` | Client | Site UUID or dashboard site public ID does not match pairing. |
| `action_unsupported` | Client | Action type is not allowlisted or not implemented. |
| `action_busy` | Client | A backup/upload/action lock is already active. |
| `action_rate_limited` | Client | Minimum interval has not elapsed. |
| `action_queue_failed` | Client | Client could not record or queue local work. |
| `action_dispatch_failed` | Dashboard | Dashboard could not send the signed intent safely. |
| `action_response_invalid` | Dashboard | Client response was malformed or unsafe. |
| `action_result_stale` | Dashboard | No fresh status confirmation arrived in the expected window. |

All error summaries must be operator-safe and must not include raw exception text, raw response bodies, paths, SQL, credentials, signatures, keys, Drime identifiers, package names, or stack traces.

## Compatibility

- V1-only clients remain valid and monitored.
- Dashboard must hide V2 action controls unless the client reports compatible capability.
- Client may support v2 status capability reporting before accepting any action.
- Breaking changes require a new protocol version and explicit migration notes in both repositories.

## Implementation Gate

Do not implement this protocol until:

- `docs/THREAT_MODEL_V2.md` is approved;
- the V2.1 design direction is approved;
- both repositories have restore points or clean commit baselines;
- focused test plans exist for dashboard and uploader;
- the live rollout plan keeps all remote actions disabled until each client explicitly opts in.
