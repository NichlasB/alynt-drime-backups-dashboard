# V2.2 Remote Action History And Audit UI Hardening Plan

Status: implemented, released, and deployed to the dashboard host. This document does not approve broad client enablement, destructive actions, restore actions, schedule changes, cleanup/delete actions, or Drime credential storage in the dashboard.

Related artifacts:

- `docs/IMPLEMENTATION_PLAN.md`
- `docs/V2_REMOTE_ACTIONS_PLAN.md`
- `docs/V2_1_REQUEST_BACKUP_NOW_DESIGN.md`
- `docs/V2_1_SIGNED_DISPATCH_IMPLEMENTATION_PLAN.md`
- `docs/PROTOCOL_V2.md`
- `docs/THREAT_MODEL_V2.md`
- uploader `docs/STATUS_PAYLOAD.md`

## Current Baseline

V2.1 `scan_upload_now` is implemented, released, deployed to the dashboard host, and pilot-proven on `purecleanse.net`.

The pilot showed that the remote-action security and dispatch model works, but it also exposed the next operator-experience gap:

- dashboard action rows record request/dispatch/accepted/rate-limited states;
- the client status payload reports richer local worker outcomes through `remote_actions.last_action`;
- the dashboard does not yet reconcile that client-reported final evidence back into the durable dashboard action row in a way that supports strong filtering, review, stale-action detection, or support exports.

V2.2 should close that evidence gap before broader V2.1 enablement or any higher-risk V2.3+ control is considered.

Implementation note: the dashboard now adds action-table reconciliation fields, reconciles sanitized `remote_actions.last_action` evidence after successful status polls, marks unconfirmed accepted/running actions stale after bounded thresholds, distinguishes dashboard and client state in Site Detail action history, shows a compact latest-client-action hint on Sites rows, and adds support-safe aggregate action-history counts to Diagnostics support copy.

## Goal

Make V2 remote-action state visible, reviewable, reconciled, and support-safe.

The operator should be able to answer:

- Which sites have V2 actions enabled?
- Which action requests are pending, accepted, running, succeeded, failed, busy, rate-limited, stale, or dispatch-failed?
- Did the client report a final result for a dashboard action?
- Is a dashboard row stale because the client never confirmed progress?
- Which failures are operational client results versus dashboard dispatch/response failures?
- What redacted facts can be copied into support notes without exposing paths, package names, Drime identifiers, signatures, keys, tokens, raw response bodies, or credentials?

## Non-Goals

V2.2 must not add new remote powers.

Do not add:

- fresh WPvivid backup creation;
- fresh server-runner package creation;
- schedule management;
- cleanup or retention actions;
- delete backup actions;
- restore preparation or restore execution;
- dashboard-side Drime API credentials;
- filesystem browsing;
- arbitrary URL checks;
- arbitrary command execution;
- raw path, package-name, backup-ID, Drime-ID, signed-URL, credential, nonce, cookie, SQL, or raw response storage.

## Recommended Implementation Shape

### 1. Dashboard reconciliation service

Add a small dashboard-side reconciliation service that runs after a successful status poll and inspects sanitized `remote_actions.last_action`.

Responsibilities:

- accept only already-sanitized payload data from the existing status payload validator/capability sanitizer;
- match client `last_action.action_id` to a dashboard action record by the dashboard action public ID;
- verify the matched action belongs to the same dashboard site;
- update the dashboard action record when the client reports a newer terminal or progress state;
- store only bounded, redacted scalar evidence and allowlisted counts;
- ignore unknown action IDs without creating synthetic action rows in v2.2;
- never let action success override backup freshness status.

### 2. Minimal schema/data model extension

Prefer a small additive migration over overloading `result_summary` or raw context JSON.

Candidate fields for `{$wpdb->prefix}alynt_drime_dashboard_actions`:

- `client_state varchar(32) nullable`
- `client_result_code varchar(64) nullable`
- `client_result_summary text nullable`
- `client_counts_json longtext nullable`
- `client_updated_at datetime nullable`
- `reconciled_at datetime nullable`

Keep existing fields as the dashboard-owned request/dispatch record. Use the new fields for client-reported execution evidence.

Migration requirements:

- idempotent upgrade;
- no destructive table rewrite;
- covered by schema tests;
- uninstall cleanup continues to remove the action table;
- older rows render safely with empty client evidence.

### 3. Action state model

Keep dashboard-owned and client-owned evidence visually distinct:

- dashboard state: `queued_for_dispatch`, `dispatch_failed`, `accepted`, `rejected`, `unsupported`, `rate_limited`, `busy`;
- client execution state: `accepted`, `running`, `succeeded`, `failed`, `timed_out`, `rate_limited`, `busy`, `rejected`, `unsupported`;
- derived operator state: the safest single label for lists and filters.

Suggested derived precedence:

1. `dispatch_failed` when the dashboard never safely reached the client.
2. client terminal states when a matching client result exists.
3. stale accepted/running when no matching client update arrives within the expected window.
4. dashboard accepted/rate-limited/busy/rejected/unsupported when no newer client evidence exists.
5. queued/pending when dispatch has not happened yet.

### 4. Stale-action detection

Add deterministic stale detection for accepted/running actions.

Suggested initial thresholds:

- accepted but no client confirmation after 30 minutes: `stale`;
- running but unchanged after 60 minutes: `stale`;
- never mark backup freshness healthy because an action is accepted or running.

These thresholds should be constants or filters only after a later extensibility review. For V2.2, keep them internal and tested.

### 5. Admin UI hardening

Improve the existing Site Detail action history table:

- show dashboard request time;
- show dashboard dispatch/accepted state;
- show client-reported state when present;
- show result code and redacted summary;
- show allowlisted counts: found, queued, already known, upload attempted, failed;
- show retry-after for rate-limited actions;
- show stale marker and explanation;
- keep empty state clear.

Add a dashboard-level or Diagnostics-level aggregate:

- action-enabled site count;
- recent action count;
- pending/running/stale/failed/rate-limited count;
- latest action age;
- clients with capability but no dashboard signing key;
- sites where client and dashboard action evidence cannot be reconciled.

Keep Sites-list rows compact:

- latest request label;
- latest derived action state;
- short retry/stale note when relevant;
- no raw details in the row.

### 6. Filters and review affordances

If the existing Attention view is the lowest-friction place, add V2 action filters there rather than creating a new app-like screen.

Recommended filters:

- all recent actions;
- pending/running;
- failed/stale;
- rate-limited/busy;
- action-enabled sites;
- action-capable but not opted in.

Do not let action filters replace the backup-health Attention queue. Treat them as an operational evidence lens.

### 7. Support copy/export hardening

Extend support-safe diagnostics/export to include:

- aggregate action counts;
- per-site latest derived action state;
- action IDs as public UUIDs only;
- redacted result code/summary;
- allowlisted numeric counts;
- timestamps.

Continue excluding:

- signatures;
- private keys;
- public/private action opt-in tokens;
- polling credentials;
- authorization headers;
- raw request or response bodies;
- local paths;
- package names;
- Drime object IDs or URLs;
- SQL;
- cookies, nonces, salts, and credentials.

### 8. Uploader-side companion tightening

Keep uploader changes small unless dashboard reconciliation reveals a payload gap.

Likely uploader tasks:

- confirm `remote_actions.last_action` contains enough fields to reconcile final worker outcomes;
- add `requested_at` and `completed_at` only if they can be derived without exposing unsafe state;
- ensure counts remain allowlisted and numeric;
- ensure disabled/revoked action state remains visible enough for dashboard explanation;
- add focused tests for any new or clarified fields.

If the current payload is sufficient, V2.2 can be mostly dashboard-side.

## Acceptance Criteria

- Existing V1-only clients continue to render with no V2 controls.
- Existing V2.1-enabled clients continue to support only `scan_upload_now`.
- A dashboard action accepted by the client is reconciled with a matching client `last_action.action_id`.
- Client `succeeded`, `failed`, `rate_limited`, `busy`, `rejected`, `unsupported`, and `timed_out` states render distinctly and safely.
- Accepted/running actions become visibly stale when no fresh matching client status appears within the documented threshold.
- Action history distinguishes dashboard dispatch evidence from client execution evidence.
- Sites-list action summaries stay compact and do not crowd backup freshness evidence.
- Diagnostics/support export includes useful action counts and latest states without exposing forbidden data.
- No dashboard state changes backup freshness unless normal backup-source evidence changes.
- No new remote-action type is added.
- No dashboard Drime credential storage or direct Drime API access is added.

## Test Plan

Dashboard focused tests:

- migration adds client reconciliation fields idempotently;
- repository can find/update by public action ID and site ID;
- reconciliation ignores unknown action IDs;
- reconciliation refuses cross-site action IDs;
- reconciliation updates client state/result/counts with only allowlisted fields;
- reconciliation does not downgrade newer terminal dashboard evidence with older client evidence;
- stale-action classifier handles accepted/running thresholds deterministically;
- Site Detail renders dashboard state and client state separately;
- Sites-list renders a compact latest-action line;
- Diagnostics/support export includes counts but no forbidden fields;
- V1-only and V2-disabled payloads remain unchanged.

Uploader focused tests, only if uploader payload changes:

- `remote_actions.last_action` remains redacted;
- timestamps and counts are bounded and scalar;
- disabled/revoked capability summaries remain safe;
- forbidden keys/values are not emitted.

Workflow checks after implementation:

- dashboard focused RemoteAction tests;
- dashboard focused storage/schema tests;
- dashboard focused admin rendering/diagnostics tests;
- dashboard full `npm.cmd test`;
- dashboard `npm.cmd run lint`;
- uploader focused tests only if uploader code changes;
- ds2 feature security review;
- ds2 feature UI/UX implementation review;
- ds2 feature bloat/structure review if schema/service/UI changes are larger than expected.

Run ds3 pre-release only when preparing a release candidate.

## Rollout Guidance

V2.2 should be released before broader V2.1 enablement.

Recommended order:

1. Implement and test V2.2 locally.
2. Release dashboard first if the slice is dashboard-only.
3. Release uploader first only if the payload contract changes.
4. Deploy to `control-sitesmanage` after the normal live-site approval gate.
5. Verify existing `purecleanse.net` action history renders with reconciled client evidence.
6. Only then consider opting in another low-risk client.

## Approval Gate Before Code

Before code implementation, confirm:

- V2.2 is limited to action history, reconciliation, filters, diagnostics, support export, and stale-action evidence;
- no new remote action types are added;
- no backup creation, schedule management, cleanup, delete, or restore behavior is added;
- dashboard still receives no Drime credentials and performs no Drime API calls;
- any schema migration is additive and idempotent;
- live deployment remains a later separate approval gate.
