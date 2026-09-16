# Alynt Drime Backups Dashboard Protocol v2

Status: V2.1/V2.2 protocol baseline with implemented V2.3 schedule capability reporting, implemented non-mutating `schedule_preview`, and guarded `schedule_apply` for `alynt_scan_upload` cadence changes only. The action opt-in token foundation, dashboard signed dispatch, client action-intent endpoint, dashboard-side action-history reconciliation, preview-only schedule capability, schedule-preview action, and guarded Schedule Apply have been implemented, released, and deployed to the dashboard host. `schedule_apply` remains disabled by default on clients and requires separate local Schedule Apply opt-in before the dashboard can show apply controls.

This document defines the proposed cross-plugin protocol for the first remote-action slice between Alynt Drime Backups Dashboard and Alynt Drime Backups Uploader.

Implementation planning for signed dispatch is tracked in `docs/V2_1_SIGNED_DISPATCH_IMPLEMENTATION_PLAN.md`. The action-history/audit hardening slice is tracked in `docs/V2_2_REMOTE_ACTION_HISTORY_AUDIT_PLAN.md`. V2.3 schedule-management design is tracked in `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`, implemented schedule preview is tracked in `docs/V2_3_SCHEDULE_PREVIEW_IMPLEMENTATION_PLAN.md`, guarded schedule apply implementation is tracked in `docs/V2_3_SCHEDULE_APPLY_IMPLEMENTATION_PLAN.md`, and rollback-readiness metadata planning is tracked in `docs/V2_3_ROLLBACK_METADATA_CAPTURE_PLAN.md`.

Version 2 is additive to the version 1 read-only pairing and polling protocol. A site may remain fully valid as a v1-only monitored site without supporting this protocol.

## Non-Negotiable Boundary

- V1 pairing and status polling remain read-only.
- V2 remote actions require a separate client-side opt-in after v1 pairing.
- The dashboard must not store Drime API credentials.
- The dashboard must not send shell commands, filesystem paths, package names, raw backup IDs, Drime object IDs, signed URLs, or arbitrary settings payloads.
- The client uploader remains the only system that can execute backup-related work, and it uses its own local settings, credentials, locks, and policy.
- V2.1 initially allows only `scan_upload_now`.
- Fresh server-runner or WPvivid backup creation is not part of the initial V2.1 action unless a later client capability explicitly declares and safely implements it.
- V2.3 started with schedule capability reporting, preview-only display, and non-mutating `schedule_preview`. The current mutating V2.3 action is guarded `schedule_apply` for `alynt_scan_upload` cadence changes only. Applying a schedule requires a fresh successful preview, client-side revalidation, a separate local Schedule Apply opt-in, and release/deploy approval gates. Rollback metadata capture/readiness and rolling back schedule changes require later protocol updates and separate approval gates.

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
    },
    "schedule_management": {
      "protocol_version": 2,
      "capability_version": 1,
      "enabled": true,
      "preview_only": true,
      "apply_supported": false,
      "rollback_supported": false,
      "schedules": [
        {
          "schedule_id": "alynt_scan_upload",
          "label": "Alynt scan/upload",
          "owner": "alynt_uploader",
          "manageable": true,
          "current_cadence": "every_15_minutes",
          "current_next_run_at": "2026-09-11T17:45:00Z",
          "supported_cadences": ["every_15_minutes", "every_30_minutes", "hourly"],
          "minimum_interval_seconds": 900,
          "can_disable": false,
          "requires_high_friction_disable": true,
          "rollback_supported": false
        }
      ]
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

### Preview-Only Schedule Capability Reporting

V2.3 may add an optional `remote_actions.schedule_management` object to the authenticated status payload. This is capability reporting only unless a later approved action type is added.

Dashboard ingestion rules:

- Treat `schedule_management` as optional and backward-compatible.
- Treat clients that omit it as not supporting dashboard schedule management.
- Accept only documented scalar fields, bounded arrays, and allowlisted schedule objects.
- Accept only dashboard-recognized schedule IDs. The first planned IDs are `alynt_scan_upload` and, later only after separate client proof, `alynt_server_runner`.
- Accept only allowlisted owner labels such as `alynt_uploader` and `alynt_server_runner`.
- Accept only allowlisted cadence labels such as `every_15_minutes`, `every_30_minutes`, `hourly`, `daily`, and `weekly`.
- For preview-only clients, require `preview_only: true`, `apply_supported: false`, and `rollback_supported: false`. For apply-capable clients, require `apply_supported: true`, `rollback_supported: false`, `schedule_apply` in `allowed_actions`, and a fresh matching preview before any apply control is shown.
- Display capability as unavailable when Sodium, V2 action opt-in, or schedule capability reporting is unavailable.
- Reject or ignore any raw cron line, crontab fragment, WP-Cron array, raw WPvivid option, filesystem path, package name, Drime identifier, token, credential, shell command, SQL, signed URL, or arbitrary setting payload.

The preview-only slice may show current schedule posture and supported cadence choices, but it must not dispatch schedule mutation.

### Planned Schedule Preview Action

The next V2.3 slice may allow a signed `schedule_preview` action for `alynt_scan_upload` only. `schedule_preview` is non-mutating: it asks the client to validate a proposed cadence against local capability state and return a redacted before/after preview.

Planned request extension:

```json
{
  "action_type": "schedule_preview",
  "schedule_preview": {
    "schedule_id": "alynt_scan_upload",
    "proposed_cadence": "every_30_minutes",
    "capability_version": 1
  }
}
```

Rules:

- The dashboard may offer only client-declared schedule IDs and cadence labels.
- The dashboard must not send raw cron syntax, next-run timestamps, paths, commands, option names/values, package names, backup IDs, Drime IDs, credentials, disable flags, or arbitrary labels.
- The client must compute the preview from current local state.
- The client must reject unknown schedule IDs, unsupported cadences, free-form cron expressions, and unsafe local state.
- The response may include schedule ID, label, owner, current cadence, proposed cadence, current next run, proposed next-run estimate, `would_change`, warning codes, and support-safe result codes.
- The response must not include raw cron, raw crontab, raw WP-Cron arrays, raw WPvivid options, usernames, paths, package names, Drime IDs, credentials, or arbitrary client-local internals.
- `schedule_apply` is implemented only for the approved, guarded `alynt_scan_upload` cadence flow. `schedule_rollback` remains reserved and must be rejected until separately implemented and approved.

### Schedule Apply Action

The V2.3 Schedule Apply slice may allow a signed `schedule_apply` action for `alynt_scan_upload` only. `schedule_apply` is mutating: it asks the client to apply one already-previewed cadence change after revalidating the preview against current local schedule state.

Request extension:

```json
{
  "action_type": "schedule_apply",
  "schedule_apply": {
    "schedule_id": "alynt_scan_upload",
    "proposed_cadence": "every_30_minutes",
    "capability_version": 1,
    "preview_action_id": "00000000-0000-4000-8000-000000000001",
    "preview_fingerprint": "sha256-example-redacted-preview-fingerprint"
  }
}
```

Rules:

- The dashboard may dispatch `schedule_apply` only from a fresh successful `schedule_preview` result for the same site, schedule ID, proposed cadence, capability version, and current local schedule fingerprint.
- The dashboard must not send raw cron syntax, current next-run assumptions, paths, commands, option names/values, package names, backup IDs, Drime IDs, credentials, disable flags, or arbitrary labels.
- The client must revalidate the preview against current local state before applying.
- The client must reject unknown schedule IDs, unsupported cadences, missing/expired/stale preview references, free-form cron expressions, unsafe local state, and changes that would disable all backup production.
- The client must report rollback unavailable in this slice; `schedule_rollback` remains reserved until separately implemented and approved.
- The response may include schedule ID, label, owner, previous cadence, applied cadence, previous next run, new next run, rollback availability as false, empty rollback expiry, warning codes, support-safe result codes, and an additive `rollback_metadata` object.
- `rollback_metadata`, when present, is evidence-only. It may include `captured`, `available: false`, `reason`, source action IDs, previous/applied cadence labels, previous/applied next-run timestamps, before/after redacted schedule fingerprints, `captured_at`, and `expires_at`.
- The response must not include raw cron, raw crontab, raw WP-Cron arrays, raw WPvivid options, usernames, paths, package names, Drime IDs, credentials, or arbitrary client-local internals.

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
- schedule preview/apply/rollback payloads until V2.3 action types are separately approved;
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

## V2.3 Schedule Action Types

The V2.3 design reserves the following action names, but they are not active in the current protocol baseline:

- `schedule_preview`
- `schedule_apply`
- `schedule_rollback`

`schedule_preview` is implemented as a non-mutating V2.3 action. `schedule_apply` is implemented locally and unreleased for `alynt_scan_upload` cadence changes only. `schedule_rollback` remains reserved and must be rejected until separately implemented and approved.

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
| `schedule_capability_invalid` | Dashboard | Schedule capability payload was malformed, unsafe, or incompatible. |
| `schedule_management_unavailable` | Dashboard | Client does not currently expose supported schedule-management capability. |
| `action_dispatch_failed` | Dashboard | Dashboard could not send the signed intent safely. |
| `action_response_invalid` | Dashboard | Client response was malformed or unsafe. |
| `action_result_stale` | Dashboard | No fresh status confirmation arrived in the expected window. |

All error summaries must be operator-safe and must not include raw exception text, raw response bodies, paths, SQL, credentials, signatures, keys, Drime identifiers, package names, or stack traces.

## Compatibility

- V1-only clients remain valid and monitored.
- Dashboard must hide V2 action controls unless the client reports compatible capability.
- Client may support v2 status capability reporting before accepting any action.
- Client may support `remote_actions.schedule_management` preview-only reporting without accepting schedule mutation actions.
- Breaking changes require a new protocol version and explicit migration notes in both repositories.

## Implementation Gate

Do not implement this protocol until:

- `docs/THREAT_MODEL_V2.md` is approved;
- the V2.1 design direction is approved;
- the V2.3 preview-only schedule capability shape is approved before schedule-management implementation begins;
- both repositories have restore points or clean commit baselines;
- focused test plans exist for dashboard and uploader;
- the live rollout plan keeps all remote actions disabled until each client explicitly opts in.
