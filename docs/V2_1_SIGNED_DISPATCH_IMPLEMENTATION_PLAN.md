# V2.1 Signed Request Backup Now Dispatch Implementation Plan

Status: local implementation planning. This document does not approve release, deployment, live-site enablement, broad rollout, destructive actions, restore actions, schedule changes, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_1_REQUEST_BACKUP_NOW_DESIGN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Current Baseline

The following V2.1 foundations are already implemented locally:

- dashboard action table and dashboard-local action repository;
- dashboard redacted action-history display;
- dashboard Ed25519 signer and deterministic signing-input helper;
- dashboard-generated display-once `adb2a` action opt-in token;
- dashboard encrypted per-site action private-key storage;
- uploader `adb2a` parser and explicit client-side V2 action opt-in;
- uploader redacted `remote_actions` capability summary with `enabled`, `key_id`, `allowed_actions`, `sodium_available`, `min_interval_seconds`, and `one_running_action_per_site`;
- local dashboard action dispatch endpoint client has been implemented in the dashboard, and the paired uploader action-intent endpoint has been implemented locally;
- no remote backup execution.

Recent local baseline commits:

- dashboard: `635f2b6 feat: add v2 action opt-in token flow`
- uploader: `63d3b8c feat: accept v2 action opt-in tokens`

## Non-Negotiable Boundary

V2.1 is still limited to `scan_upload_now`.

The dashboard may ask an opted-in client to scan for ready backup packages and upload eligible queued items using the client's existing uploader settings and Drime credentials.

The dashboard must not:

- store, request, or display Drime API credentials;
- send shell commands;
- send filesystem paths, package names, backup IDs, Drime object IDs, signed URLs, arbitrary URLs, or settings payloads;
- trigger WPvivid or server-runner backup creation;
- delete, clean up, restore, or change schedules/settings;
- retry accepted work automatically.

The client uploader remains the execution owner and must be able to reject the request under local policy.

## Recommended Slice Shape

Implement this as two local commits, then run focused feature workflows before any release planning:

1. **Uploader receive/verify/enqueue slice** - completed locally: disabled-by-default V2 action-intent endpoint and local action audit/idempotency store.
2. **Dashboard sign/dispatch/reconcile slice** - completed locally: operator action, signed POST dispatch, state recording, safe response handling, and optional immediate read-only poll after client acceptance.

Release order, later and separately approved:

1. Release uploader first with the endpoint inert unless a client has already completed V2 action opt-in.
2. Release dashboard second with controls shown only for clients reporting compatible V2 capability.
3. Enable on one low-risk client only after explicit live approval.

## Uploader Implementation Plan

### New REST Endpoint

Add:

```text
POST /wp-json/alynt-drime-backups-uploader/v2/action-intents
```

Recommended class:

```text
includes/class-dashboard-action-intents-rest-controller.php
```

The route should be registered publicly at the WordPress REST layer, but the handler must fail closed unless all V2 action checks pass. This mirrors signed webhook-style endpoints: WordPress user auth is not required, but the action signature and local opt-in are required.

The endpoint must:

- accept `POST` only;
- return `Cache-Control: no-store`;
- reject before parsing expensive work when `remote_actions_enabled` is false;
- reject when Sodium verification is unavailable;
- reject missing or malformed action headers;
- parse a bounded JSON body only;
- never expose raw exception text or request body contents in responses, logs, status payloads, or support exports.

### Required Headers

```http
X-Adbd-Action-Key-Id: <stored action key id>
X-Adbd-Action-Signature: <base64url-ed25519-signature>
X-Adbd-Action-Signed-At: <iso8601 timestamp>
Content-Type: application/json
Accept: application/json
Cache-Control: no-store
```

### Intent Body

Accept only:

```json
{
  "protocol_version": 2,
  "action_id": "uuid",
  "dashboard_site_public_id": "uuid",
  "site_uuid": "uuid",
  "action_type": "scan_upload_now",
  "requested_at": "iso8601",
  "expires_at": "iso8601",
  "idempotency_key": "bounded identifier"
}
```

Reject any additional body keys.

### Signature Verification

Add uploader-side verification helpers that intentionally match the dashboard signer:

```text
ADB-ACTION-V2
POST
/wp-json/alynt-drime-backups-uploader/v2/action-intents
<canonical-client-origin>
<sha256-hex-of-canonical-json-body>
<signed-at-iso8601>
```

Verification must check:

- stored `action_key_id` matches `X-Adbd-Action-Key-Id`;
- `action_public_key` verifies the signature;
- signed route and method are exact;
- body fingerprint is exact;
- signed timestamp is within the allowed freshness window;
- body `expires_at` is still in the future;
- body `dashboard_site_public_id` matches the existing V1 pairing;
- body `site_uuid` matches local site UUID;
- body `action_type` is exactly `scan_upload_now`;
- body `idempotency_key` is valid and retained.

### Local Action Store

Use a bounded, non-autoloaded option for the first uploader-side implementation, because the uploader already stores bounded queue/registry/audit-like structures this way and V2.1 has only one action type.

Recommended option:

```text
alynt_drime_backups_remote_action_state
```

Recommended contents:

- recent action records, bounded to 50 entries;
- idempotency keys retained for at least 24 hours;
- latest action summary;
- running action lock owner and expiry;
- last accepted timestamp per action type for rate limiting.

Do not store:

- request signature;
- raw request body;
- raw response body;
- private keys;
- Drime credentials;
- paths;
- package names;
- Drime IDs;
- signed URLs.

### State Machine

Allowed local states:

- `accepted`
- `rejected`
- `unsupported`
- `rate_limited`
- `busy`
- `running`
- `succeeded`
- `failed`
- `timed_out`

The endpoint should normally return `202 accepted` and state `accepted` after it records the action and schedules work.

### Worker Behavior

Do not perform the scan/upload workflow in the REST request.

Add one local worker hook, for example:

```text
alynt_drime_backups_remote_action_event
```

The worker should:

1. acquire a bounded remote-action lock;
2. mark the action `running`;
3. record manual scan evidence through the existing cron-health mechanism;
4. call the existing `scan_and_queue()` workflow;
5. schedule the existing upload event shortly after queueing, instead of running long uploads inside the REST request;
6. store only redacted counts such as `found`, `queued`, `already_known`, `upload_attempted`, and `failed`;
7. mark the action `succeeded` or `failed` with a stable result code and operator-safe summary.

The worker must not:

- run arbitrary shell commands;
- trigger WPvivid backup creation;
- trigger server-runner backup creation;
- clear failed uploads;
- delete local or remote files;
- mutate uploader settings;
- expose raw file paths or package names.

### Capability Reporting

Extend `remote_action_summary()` to include `last_action` after the local action store exists.

The summary must remain redacted and compatible with existing schema version `1`.

## Dashboard Implementation Plan

### Dispatch Service

Recommended new class:

```text
includes/class-remote-action-dispatcher.php
```

Responsibilities:

- confirm the site is active and has V1 polling credentials;
- confirm the latest sanitized status payload reports V2 capability for `scan_upload_now`;
- confirm the dashboard has encrypted action private-key storage for the same action key ID;
- create or reserve a dashboard action record;
- build deterministic intent JSON;
- decrypt the per-site action private key using credential-vault context `action:{site_public_id}`;
- sign the canonical request;
- dispatch one POST to the fixed client route through the existing safe-transport model;
- sanitize the response and mark the dashboard action state.

### Dashboard Intent Body

The dashboard action ID should be the dashboard action row public UUID, not a separate generated identifier.

The dashboard should create an idempotency key that is:

- unique per dashboard action;
- bounded;
- not derived from secrets;
- safe to store and display only as a redacted identifier if needed.

### Safe Transport

Build the client URL from the enrolled canonical origin:

```text
{expected_origin}/wp-json/alynt-drime-backups-uploader/v2/action-intents
```

Reuse the existing public-HTTPS and unsafe-destination protections. Disable redirects, set short timeouts, set response-size limits, send no cookies, and never log raw response bodies.

### Admin UI

Keep `Request Backup Now` hidden or disabled unless:

- latest status payload reports V2 protocol `2`;
- remote actions are `enabled`;
- `allowed_actions` includes `scan_upload_now`;
- `sodium_available` is true;
- the dashboard has an action private key for the reported key ID;
- no in-flight dashboard action exists for that site.

Add a confirmation form on Site Detail first. The Sites-list row button can come after the confirmation flow is proven.

Confirmation copy:

```text
This asks the client site to scan for ready backup packages and upload eligible items using its own configured Drime settings. It does not restore, delete, clean up, change schedules, or reveal Drime credentials.
```

### Response Handling

Accept only:

- `protocol_version: 2`
- matching `action_id`
- allowed state
- bounded `result_code`
- bounded `result_summary`
- non-negative `retry_after_seconds`

Reject response payloads containing forbidden keys or suspicious sensitive-looking values.

After a client returns `accepted`, the dashboard may run one normal read-only status poll if the site's poll lock allows it. Backup freshness should still be determined only by normal status payload evidence, not by action acceptance.

## Test Plan

### Uploader Focused Tests

- endpoint returns fail-closed when V2 opt-in is disabled;
- missing headers fail closed;
- invalid key ID fails closed;
- invalid signature fails closed;
- expired signed timestamp fails closed;
- expired body fails closed;
- mismatched dashboard site public ID fails closed;
- mismatched site UUID fails closed;
- unknown action type fails closed;
- duplicate idempotency key returns original state without duplicate work;
- rate limit returns `rate_limited`;
- busy lock returns `busy`;
- accepted action records bounded redacted state and schedules worker;
- worker calls `scan_and_queue()` and schedules upload event without exposing paths/package names;
- status payload includes safe `remote_actions.last_action`.

### Dashboard Focused Tests

- request button hidden for V1-only clients;
- request button hidden when client reports V2 disabled;
- request button hidden when dashboard lacks matching action private key;
- confirmation action requires nonce/capability;
- intent JSON canonicalization is deterministic;
- signing headers match `PROTOCOL_V2.md`;
- dispatch uses fixed route and safe transport;
- dispatch failure marks `dispatch_failed`;
- malformed client response marks `action_response_invalid`;
- accepted response marks `accepted`;
- response sanitizer rejects forbidden fields;
- action history and support export never include signatures, private keys, raw response bodies, paths, package names, Drime IDs, signed URLs, or credentials.

### Workflow Checks

After implementation, run at minimum:

- dashboard focused PHPUnit tests for remote action signer/repository/dispatcher/admin rendering;
- dashboard full `npm.cmd test`;
- dashboard `npm.cmd run lint`;
- uploader focused PHPUnit tests for dashboard connection/action endpoint/action store/status payload;
- uploader full `npm.cmd test`;
- uploader `npm.cmd run lint`;
- ds2 `FEATURE_LIGHT_REVIEW_PROMPT.md`;
- ds2 `FEATURE_UI_UX_IMPLEMENTATION_PROMPT.md`;
- ds2 `FEATURE_SECURITY_REVIEW_PROMPT.md`;
- ds2 `FEATURE_BLOAT_AND_STRUCTURE_REVIEW_PROMPT.md` if the endpoint/storage code grows beyond the planned classes.

Run ds3 pre-release only when a release candidate is being prepared.

## Approval Gate Before Endpoint Code

Before writing endpoint/dispatch implementation code, confirm:

- initial action remains only `scan_upload_now`;
- uploader endpoint may be registered but fail closed unless V1 pairing and V2 action opt-in are active;
- the client may schedule existing local scan/upload workers but must not trigger backup creation;
- dashboard will still not receive Drime credentials, local paths, package names, Drime IDs, signed URLs, or raw responses;
- implementation remains local-only until a separate release/deploy approval gate.
