# V2.1 Request Backup Now Design

Status: V2.1 design baseline. The local action opt-in token foundation has been implemented, but signed dispatch, the client action-intent endpoint, release, deployment, and live-site enablement remain unapproved and unimplemented.

Related planning:

- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/IMPLEMENTATION_PLAN.md`
- `docs/PROTOCOL_V1.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- `docs/V2_1_SIGNED_DISPATCH_IMPLEMENTATION_PLAN.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Boundary

Version 1 remains read-only. V2.1 is the first possible remote-action slice, and it must be implemented as a separate opt-in action model rather than an expansion of the v1 polling credential.

The dashboard still must not store Drime API credentials, browse client files, send shell commands, change schedules, delete backups, clean up retention, restore data, or change uploader settings.

The client uploader remains the execution owner. The dashboard may request an allowlisted action; the client decides whether it is opted in, currently capable, rate-limited, already busy, or safe to run.

## Product Goal

Give an operator a safe button for an enrolled site:

```text
Request Backup Now
```

The operator outcome is not "the dashboard ran a backup." The outcome is:

1. the dashboard recorded a signed request;
2. the client accepted or rejected it under local policy;
3. the client performed an allowlisted local workflow when accepted;
4. the dashboard observed the result through redacted action history and normal polling.

## Initial Action Recommendation

Start with `scan_upload_now` as the first V2.1 implementation target.

Why:

- it uses the uploader's existing scanner, queue, registry, and upload worker behavior;
- it does not need Drime credentials on the dashboard;
- it does not require generic shell execution;
- it can find and upload already-created server-runner or WPvivid packages;
- it is substantially lower risk than forcing WPvivid or a server backup runner to create a new backup.

Defer these action types until separate capability proofs exist:

- `server_backup_now`: only when the client can declare a safe local trigger for a configured server-runner package producer.
- `wpvivid_backup_now`: only when the client can declare a stable, tested WPvivid local API/cron trigger and can prevent duplicate heavy jobs.

The UI can still use the operator label "Request Backup Now" if the detail text says exactly what the current client capability can do. For initial clients, the detail should say "Scan for ready backup packages and upload anything eligible now."

## Operator Flow

1. Dashboard Sites row or Site Detail shows `Request Backup Now` only when the site reports V2.1 remote-action capability and client-side opt-in is active.
2. Operator opens a confirmation panel.
3. Panel shows the declared client capability, last accepted action, current queue/active upload state, expected effect, and rate-limit note.
4. Operator confirms.
5. Dashboard creates a local action record with `queued_for_dispatch`.
6. Dashboard sends one signed intent to the client action endpoint.
7. Client returns `accepted`, `rejected`, `rate_limited`, `busy`, or `unsupported`.
8. Dashboard records that response and immediately runs a normal read-only status poll if safe.
9. Client reports action progress/results through redacted action history in later status payloads.
10. Dashboard displays action state in Sites, Site Detail, Attention, Diagnostics, and support export.

## Dashboard UI

### Sites Tab

Add a compact action affordance only for capable sites:

- `View`
- `Check Now`
- `Request Backup Now`

For sites without V2.1 support, show disabled copy rather than a button:

```text
Backup request unavailable until the client opts in to V2.1 actions.
```

The Sites row should also show a compact latest action line when present:

```text
Backup request: accepted 4 minutes ago
Backup request: running
Backup request: failed - client busy
```

Do not let action state replace backup freshness evidence. Freshness still comes from status payload backup-source evidence.

### Site Detail

Add a "Remote Action Requests" panel below current read-only status controls:

- capability summary;
- action opt-in state;
- allowed action types;
- last request state;
- last result summary;
- recent action history table;
- confirmation form for `Request Backup Now`.

Confirmation copy:

```text
This asks the client site to scan for ready backup packages and upload eligible items using its own configured Drime settings. It does not restore, delete, clean up, change schedules, or reveal Drime credentials.
```

### Attention Tab

Do not mark a site as healthy merely because an action was requested. Use action state as context:

- `needs_attention` with a failed recent request stays attention;
- `needs_attention` with an accepted/running request can show "request in progress";
- stale source evidence remains stale until the normal status payload proves fresh upload evidence.

### Diagnostics And Support Export

Include redacted aggregate counts:

- action-enabled site count;
- pending/running/failed action count;
- latest action age;
- stale action count;
- rate-limited count.

Never include request signatures, private keys, raw client responses, raw paths, Drime IDs, signed URLs, package names, or credentials.

## Protocol Shape

Protocol details are drafted in `docs/PROTOCOL_V2.md`. That document should be approved before implementation. It should not mutate `PROTOCOL_V1.md` beyond cross-references.

Recommended client route:

```text
POST /wp-json/alynt-drime-backups-uploader/v2/action-intents
```

Recommended status/result reporting:

- Add optional, redacted `remote_actions` summary to the authenticated status payload.
- Keep older schema-1 clients compatible by treating this as absent when missing.
- Reject action-result fields that contain path-, secret-, credential-, package-, signed-URL-, or Drime-identifier-like keys.

Recommended intent body:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "dashboard_site_public_id": "22222222-2222-4222-8222-222222222222",
  "site_uuid": "11111111-1111-4111-8111-111111111111",
  "action_type": "scan_upload_now",
  "requested_at": "2026-08-20T10:00:00Z",
  "expires_at": "2026-08-20T10:05:00Z",
  "idempotency_key": "adb-act-..."
}
```

Recommended response:

```json
{
  "protocol_version": 2,
  "action_id": "00000000-0000-4000-8000-000000000000",
  "state": "accepted",
  "result_summary": "Scan/upload request accepted for local processing.",
  "retry_after_seconds": 0
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

## Authorization Design

Use a separate remote-action opt-in and signing model. Do not reuse the v1 read-only polling credential as a remote-action credential.

Preferred signing model:

1. Dashboard creates a per-site action signing key pair.
2. Dashboard stores the private key through the existing credential-vault pattern.
3. Client stores only the dashboard action public key, key ID, allowed action types, and opt-in timestamp.
4. Dashboard signs each intent body with the per-site private key.
5. Client verifies key ID, signature, origin binding, site UUID, dashboard site public ID, expiry, action allowlist, idempotency key, and local opt-in state.

Use Ed25519 through PHP Sodium when available. If Sodium is unavailable, the client should report remote actions unsupported and keep v1 read-only polling working.

Replay and load controls:

- action expiry defaults to 5 minutes;
- idempotency keys retained on the client for at least 24 hours;
- one running remote action per site;
- default minimum interval for `scan_upload_now`: 60 minutes per site;
- dashboard-side dispatch lock per site;
- client-side execution lock separate from upload locks;
- no automatic retry of accepted actions unless the client reports a retryable state.

## Client Execution Design

For initial `scan_upload_now`, the client should:

1. validate the signed intent;
2. record a local redacted action audit entry;
3. schedule or enqueue a local action worker rather than doing long work in the REST request;
4. run the existing `scan_and_queue()` workflow;
5. trigger or schedule the existing upload worker for eligible queued items;
6. record counts only: found, queued, already_known, upload_started, skipped, failed;
7. expose progress/result through redacted status summaries.

The action must not:

- call arbitrary shell commands;
- browse arbitrary folders;
- accept raw paths or package names from the dashboard;
- modify Drime destination settings;
- clear failed uploads;
- clean local files;
- delete remote files;
- run WPvivid backup creation unless a later declared capability explicitly supports it.

## Data Storage

Dashboard should add a custom action table rather than overloading the snapshot table:

```text
{$wpdb->prefix}alynt_drime_dashboard_actions
- id bigint unsigned primary key
- public_id char(36) unique
- dashboard_site_id bigint unsigned
- action_type varchar(64)
- state varchar(32)
- idempotency_key varchar(128)
- action_key_id varchar(128)
- requested_by bigint unsigned
- requested_at datetime
- dispatched_at datetime nullable
- accepted_at datetime nullable
- completed_at datetime nullable
- expires_at datetime
- last_seen_at datetime nullable
- retry_after_seconds int unsigned
- result_code varchar(64)
- result_summary text
- request_fingerprint char(64)
- redacted_context_json longtext
```

Recommended indexes:

- `(dashboard_site_id, requested_at)`
- `(dashboard_site_id, state, requested_at)`
- `(state, expires_at)`
- `(idempotency_key)`

Dashboard site records may also need non-secret capability metadata:

- `remote_actions_enabled`
- `remote_actions_capabilities_json`
- `remote_actions_last_seen_at`
- `remote_action_key_id`
- encrypted action private key ciphertext using credential-vault context

Client storage should remain local to the uploader plugin:

- remote action opt-in flag;
- action public key and key ID;
- allowed action types;
- idempotency records;
- local action queue/state;
- redacted action audit summaries.

## Tests

Dashboard tests:

- V2.1 controls hidden for v1-only clients.
- Capability allowlist renders the correct available/unavailable UI.
- Confirm form requires capability and nonce.
- Action records redact context and never store secrets.
- Signing input canonicalization is deterministic.
- Dispatch failures, rejected actions, rate limits, busy state, timeouts, and stale accepted actions render safely.
- Support export includes counts but not signatures, keys, raw response bodies, paths, package names, or Drime identifiers.

Uploader tests:

- Remote actions disabled by default.
- Separate opt-in required even when v1 polling is paired.
- Invalid signature, expired intent, wrong site UUID, wrong dashboard site public ID, unknown action type, replayed idempotency key, and unsupported key all fail closed.
- `scan_upload_now` calls the existing scan/queue path and does not run arbitrary commands.
- Concurrent action lock prevents duplicate work.
- Rate limit returns stable redacted state.
- Status payload reports redacted recent action summaries only.

Cross-plugin acceptance:

- v1-only enrolled site remains read-only and shows no action button.
- action-enabled disposable site accepts one `scan_upload_now` request.
- dashboard observes accepted/running/succeeded or failed state without raw secrets.
- backup freshness changes only after normal status evidence shows a new upload.
- disabling action opt-in on client immediately makes future requests fail closed.

## Implementation Order

1. Approve `docs/PROTOCOL_V2.md` and `docs/THREAT_MODEL_V2.md`.
2. Add dashboard action storage, signing service, capability model, and redacted action history display using fixtures.
3. Add uploader remote-action opt-in shell, public-key storage, signature verification, idempotency, rate limits, and local action audit storage.
4. Add uploader `scan_upload_now` worker using existing scanner/queue/upload scheduling.
5. Add dashboard dispatch flow and normal polling reconciliation.
6. Add UI affordances on Sites and Site Detail.
7. Run focused feature workflow checks in both repositories.
8. Release uploader first with remote actions disabled by default.
9. Release dashboard controls second, still hidden unless clients explicitly opt in.
10. Enable on one low-risk client and observe before broader rollout.

## Approval Gate

Before any implementation code:

- approve this design direction;
- approve that initial V2.1 means `scan_upload_now`, not forced WPvivid or server backup creation;
- approve `PROTOCOL_V2.md` and `THREAT_MODEL_V2.md`;
- create or confirm restore points for both repositories before edit-heavy work;
- keep live `control-sitesmanage` unchanged until a separate release/deploy approval gate.
