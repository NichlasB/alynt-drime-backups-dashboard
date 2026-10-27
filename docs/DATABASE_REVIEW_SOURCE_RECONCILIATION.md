# Database Review — source inventory and finding reconciliation

## Scope and status — 2026-10-27

This records the already-authorized repository-only canonical05 reconciliation
after the separately approved schema/lifecycle, poll-completion and retention
proofs and latest-selection repair. It is not a new native experiment or a fresh
Phase1 editing pass. Original05 baseline: d68db6247caa1992a43340e519440854e10c7468;
current source:5db1040a5fd92cd94142db3a2b86ed4c519215e5. Files were inspected
read-only before this evidence record. No additional production change chosen.
Canonical source: toolkit ds3-pre-release/05-DATABASE_REVIEW_PROMPT.md, both phases.

Inventory:3 tables/66 columns/24 indexes;38 direct wpdb call sites, comprising
35 in includes and3 in uninstall.php (not38 includes-only).3 CREATE templates
and dbDelta's indirect schema queries are additional patterns, not separately
counted direct call sites. Counts are static source locations, not round trips.
Five owned persistent options, three transient families, zero owned post/user
meta keys. Core date_format/time_format reads and cron API storage are not owned
options. Manual proof/test/vendor SQL is not application runtime SQL.

Three confirmed findings: High stale poll-completion write fence, Medium
discarded retention results, Medium inconsistent current-snapshot identity.
All have approved source repairs; earlier physical receipts qualify their named
source images only. The current SELECT now has separately approved
[physical114/5 evidence](SNAPSHOT_LATEST_SELECTION_OWNED_PROOF.md); dated gate below
is historical, not renewed native authority. Whole05 tracking/FULL remains
**pending**, not silently Complete. No toolkit historical row
is refreshed. No new SQL-injection/schema/missing-index defect confirmed by this
source reconciliation; this is not a comprehensive final13 security judgment.

## Complete schema inventory

All names use `{$wpdb->prefix}alynt_drime_dashboard_` plus the suffix below.
Source: includes/class-storage.php:78 (sites),117 (snapshots),138 (actions).
Creation/upgrade: maybe_install(), dbDelta for each template, using
`$wpdb->get_charset_collate()`; tables/column maps are fixed plugin identifiers.
No SQL FOREIGN KEY, enum, database timestamp trigger or explicit ENGINE clause.
Schema defaults are declarations, not observed server configuration.

```sql
-- sites:26 columns /11 indexes
id bigint(20) unsigned NOT NULL AUTO_INCREMENT
public_id char(36) NOT NULL
site_uuid char(36) NULL
site_label varchar(191) NOT NULL DEFAULT ''
expected_origin varchar(255) NOT NULL DEFAULT ''
environment varchar(32) NOT NULL DEFAULT 'production'
enrollment_status varchar(32) NOT NULL DEFAULT 'pending'
pairing_secret_hash varchar(255) NULL
pairing_expires_at datetime NULL
polling_key_id varchar(64) NULL
polling_secret_ciphertext longtext NULL
action_key_id varchar(128) NULL
action_private_key_ciphertext longtext NULL
plugin_version varchar(64) NULL
payload_schema_version smallint(5) unsigned NULL
overall_status varchar(32) NOT NULL DEFAULT 'pending'
last_poll_attempt_at datetime NULL
last_seen_at datetime NULL
next_poll_at datetime NULL
consecutive_failures int(10) unsigned NOT NULL DEFAULT 0
last_error_code varchar(64) NULL
last_error_summary text NULL
paused_at datetime NULL
archived_at datetime NULL
created_at datetime NOT NULL
updated_at datetime NOT NULL
PRIMARY KEY(id)
UNIQUE KEY public_id(public_id)
UNIQUE KEY site_uuid(site_uuid)
KEY expected_origin(expected_origin(191))
KEY pending_origin(expected_origin(191),enrollment_status,pairing_expires_at)
KEY enrollment_status(enrollment_status)
KEY action_key_id(action_key_id)
KEY next_poll_at(next_poll_at)
KEY poll_due(enrollment_status,paused_at,next_poll_at,id)
KEY archived_at(archived_at)
KEY last_seen_at(last_seen_at)

-- snapshots:13 columns /6 indexes
id bigint(20) unsigned NOT NULL AUTO_INCREMENT
dashboard_site_id bigint(20) unsigned NOT NULL
schema_version smallint(5) unsigned NOT NULL DEFAULT 1
observed_at datetime NOT NULL
payload_fingerprint char(64) NOT NULL DEFAULT ''
overall_status varchar(32) NOT NULL DEFAULT ''
queue_count int(10) unsigned NOT NULL DEFAULT 0
uploaded_count int(10) unsigned NOT NULL DEFAULT 0
failed_count int(10) unsigned NOT NULL DEFAULT 0
active_upload tinyint(1) NOT NULL DEFAULT 0
warning_count int(10) unsigned NOT NULL DEFAULT 0
cron_status varchar(64) NOT NULL DEFAULT ''
payload_json longtext NOT NULL
PRIMARY KEY(id)
KEY site_observed(dashboard_site_id,observed_at)
KEY site_fingerprint(dashboard_site_id,payload_fingerprint)
KEY site_latest(dashboard_site_id,id)
KEY retention_cleanup(observed_at,id)
KEY overall_status(overall_status)

-- actions:27 columns /7 indexes
id bigint(20) unsigned NOT NULL AUTO_INCREMENT
public_id char(36) NOT NULL
dashboard_site_id bigint(20) unsigned NOT NULL
action_type varchar(64) NOT NULL DEFAULT ''
state varchar(32) NOT NULL DEFAULT 'queued_for_dispatch'
idempotency_key varchar(128) NOT NULL DEFAULT ''
action_key_id varchar(128) NOT NULL DEFAULT ''
requested_by bigint(20) unsigned NOT NULL DEFAULT 0
requested_at datetime NOT NULL
dispatched_at datetime NULL
accepted_at datetime NULL
completed_at datetime NULL
expires_at datetime NOT NULL
last_seen_at datetime NULL
retry_after_seconds int(10) unsigned NOT NULL DEFAULT 0
result_code varchar(64) NOT NULL DEFAULT ''
result_summary text NULL
client_state varchar(32) NULL
client_result_code varchar(64) NULL
client_result_summary text NULL
client_counts_json longtext NULL
client_updated_at datetime NULL
reconciled_at datetime NULL
request_fingerprint char(64) NOT NULL DEFAULT ''
redacted_context_json longtext NOT NULL
created_at datetime NOT NULL
updated_at datetime NOT NULL
PRIMARY KEY(id)
UNIQUE KEY public_id(public_id)
KEY site_requested(dashboard_site_id,requested_at)
KEY site_state_requested(dashboard_site_id,state,requested_at)
KEY state_expires(state,expires_at)
KEY idempotency_key(idempotency_key)
KEY completed_at(completed_at,id)
```

## Query inventory — all38 direct call sites

Paths below are relative to includes unless explicitly uninstall.php.
S=sites,N=snapshots,A=actions. `*` means full row; summary projections are fixed
source lists. API=wpdb insert/update with bound values/format handling (not raw
interpolated request SQL); P=prepare placeholders; F=closed internal fields with
individually prepared values; C=fixed query without dynamic values. None of the
queries below joins a core table. No join unless explicitly listed.

### Sites —15

| ID / source:line | Complete query pattern / WHERE | ORDER / LIMIT / preparation | Trigger |
|---|---|---|---|
| S1 traits/trait-site-repository-reads.php:84 | SELECT fixed non-secret summary FROM S WHERE1=1 [AND enrollment_status=%s] [AND archived_at IS NULL/IS NOT NULL] | site_label,expected_origin ASC; limit1..500/offset>=0; P | Admin lists/Diagnostics |
| S2 same:99 | SELECT * FROM S WHERE id=%d | PK one row, no explicit LIMIT; P | Admin/poll/dispatch site lookup |
| S3 same:126 | SELECT * FROM S WHERE enrollment_status IN(active,awaiting_first_poll) AND archived_at IS NULL AND paused_at IS NULL AND polling_key_id IS NOT NULL AND polling_secret_ciphertext IS NOT NULL AND(next_poll_at IS NULL OR next_poll_at<=%s) | COALESCE(next_poll_at,epoch),id ASC; limit1..50; P | Scheduled poll batch |
| S4 same:158 | SELECT * FROM S WHERE public_id=%s AND enrollment_status=pending | LIMIT1; P | Enrollment |
| S5 same:184 | SELECT * FROM S WHERE expected_origin=%s AND enrollment_status=pending AND pairing_expires_at>%s | LIMIT1; P | Pending-origin reuse |
| S6 traits/trait-site-repository-writes.php:33 | INSERT S(site_uuid,public_id,site_label,expected_origin,environment,enrollment_status,overall_status,pairing_secret_hash,pairing_expires_at,created_at,updated_at) | Single row; API formats | Create pending enrollment |
| S7 same:72 | UPDATE S SET enrollment_status=revoked,overall_status=pending,pairing/polling/action credentials=NULL,next_poll_at=NULL,updated_at WHERE id=%d | PK single row; API formats | Local revoke |
| S8 same:109 | UPDATE S SET site_uuid,status=awaiting_first_poll,overall_status=pending,pairing fields=NULL,poll credentials,plugin/payload versions,updated_at WHERE id=%d AND enrollment_status=pending | PK single row; API formats | Complete enrollment |
| S9 traits/trait-site-repository-local-state-writes.php:34 | UPDATE S SET archived_at=now,updated_at=now WHERE id=%d | PK single row; API formats | Local archive |
| S10 same:63 | UPDATE S SET archived_at=NULL,updated_at=now WHERE id=%d | PK single row; API formats | Local unarchive |
| S11 same:92 | UPDATE S SET paused_at=now,next_poll_at=NULL,updated_at=now WHERE id=%d | PK single row; API formats | Local pause |
| S12 same:122 | UPDATE S SET paused_at=NULL,next_poll_at=now,updated_at=now WHERE id=%d | PK single row; API formats | Local resume |
| S13 traits/trait-site-repository-runtime-writes.php:70 | UPDATE S SET action_key_id,action_private_key_ciphertext,updated_at WHERE id=%d | PK single row; API formats | Dashboard action-key setup |
| S14 same:147 | UPDATE S SET closed success/failure map WHERE id=%d AND BINARY public_id=%s AND polling key/cipher exact bytes or IS NULL AND BINARY enrollment_status IN(active,awaiting_first_poll) | PK single row; F | Poll completion |
| S15 same:159 | SELECT1 FROM S WHERE same S14 fence AND all intended completion values match byte-wise/NULL | LIMIT1; F | Zero-row completion confirmation |

S14 success map: enrollment_status,overall_status,plugin_version,last_poll_attempt_at,
last_seen_at,next_poll_at,consecutive_failures,last_error_code,last_error_summary,
updated_at. Failure map: overall_status,last_poll_attempt_at,next_poll_at,
last_error_code,last_error_summary,consecutive_failures,updated_at. S15 WHERE
adds those persisted columns; it is not just a row-existence test.

### Snapshots —6

| ID / source:line | Complete query pattern / WHERE / JOIN | ORDER / LIMIT / preparation | Trigger |
|---|---|---|---|
| N1 class-snapshot-repository.php:42 | INSERT N(all columns except auto id):site ID,version,server UTC observation,fingerprint,status,counters,flag,cron,JSON | Single row; API formats | Validated poll recording |
| N2 traits/trait-snapshot-repository-reads.php:31 | SELECT * FROM N WHERE dashboard_site_id=%d | id DESC LIMIT1; P | Detail/action capabilities |
| N3 same:67 | SELECT s1.* FROM N s1 INNER JOIN(SELECT dashboard_site_id,MAX(id) latest_id FROM N WHERE dashboard_site_id IN(%d...) GROUP BY dashboard_site_id)latest ON latest.latest_id=s1.id | At most one/requested site; no explicit LIMIT; P dynamic placeholder count | Fleet/Diagnostics |
| N4 same:111 | SELECT fixed summary excluding JSON/fingerprint FROM N WHERE dashboard_site_id=%d | observed_at,id DESC LIMIT1..50; P | Detail recent history |
| N5 same:140 | SELECT COUNT(*) FROM N WHERE dashboard_site_id=%d | Aggregate, no LIMIT; P | Local removal preview |
| N6 traits/trait-snapshot-repository-retention.php:36 | DELETE FROM N WHERE observed_at<%s AND id NOT IN(SELECT latest_id FROM(SELECT MAX(id) latest_id FROM N GROUP BY dashboard_site_id)latest_snapshots) | observed_at,id ASC LIMIT1..5000; P | Daily retention, default30days/500 |

### Actions —13

| ID / source:line | Complete query pattern / WHERE | ORDER / LIMIT / preparation | Trigger |
|---|---|---|---|
| A1 traits/trait-remote-action-repository-lookups.php:43 | SELECT * FROM A WHERE public_id=%s AND dashboard_site_id=%d | LIMIT1; P | Reconciliation/preview admission |
| A2 same:75 | SELECT * FROM A WHERE dashboard_site_id=%d | requested_at,id DESC LIMIT1; P | Detail/latest action |
| A3 same:109 | SELECT fixed safe history/context fields FROM A WHERE dashboard_site_id=%d | requested_at,id DESC LIMIT1..50; P | Detail action history |
| A4 same:148 | SELECT COUNT(*) FROM A WHERE dashboard_site_id=%d | Aggregate no LIMIT; P | Local removal preview |
| A5 same:176 | SELECT COUNT(*) FROM A WHERE dashboard_site_id=%d AND state IN(queued_for_dispatch,accepted,running) | Aggregate no LIMIT; P | Local removal eligibility |
| A6 same:202 | SELECT * FROM A WHERE id=%d | LIMIT1; P | Internal context merge |
| A7 traits/trait-remote-action-repository-state.php:68 | INSERT A(public_id,dashboard_site_id,action_type,state,idempotency_key,action_key_id,requested_by,requested_at,expires_at,retry_after_seconds,result_code,result_summary,request_fingerprint,redacted_context_json,created_at,updated_at) | Single row; API formats | Approved action orchestration |
| A8 same:139 | UPDATE A SET state,retry_after_seconds,result_code,result_summary,last_seen_at,updated_at[,accepted_at][,completed_at] WHERE id=%d | PK single row; API inferred formats | Dispatch/state result |
| A9 same:168 | UPDATE A SET dispatched_at,updated_at WHERE id=%d | PK single row; API inferred formats | Dispatch bookkeeping |
| A10 traits/trait-remote-action-repository-reconciliation.php:73 | UPDATE A SET state,client_state,client_result_code,client_result_summary,client_counts_json,client_updated_at,reconciled_at,last_seen_at,updated_at[,redacted_context_json][,accepted_at][,completed_at] WHERE id=%d | PK single row; API inferred formats | Sanitized client report |
| A11 same:107 | UPDATE A SET state=stale,result_code=client_confirmation_stale,result_summary,completed_at,updated_at WHERE dashboard_site_id=%d AND client_state IS NULL AND((state=accepted AND accepted_at IS NOT NULL AND accepted_at<%s) OR(state=running AND last_seen_at IS NOT NULL AND last_seen_at<%s)) | No LIMIT; site/state-scoped; P | Poll reconciliation maintenance |
| A12 traits/trait-remote-action-repository-summary.php:36 | SELECT COUNT(*),SUM(CASE...) for client_reconciled/stale/awaiting_confirmation/schedule_apply/rollback_preview/cleanup_preview/rollback_metadata_captured,MAX(updated_at) FROM A | Full-table aggregate no LIMIT; C static states/types/LIKE literal | Support diagnostics |
| A13 same:104 | DELETE FROM A WHERE completed_at IS NOT NULL AND completed_at<%s | completed_at,id ASC LIMIT1..5000; P | Daily retention default90days/500 |

### Schema and uninstall —4

| ID / source:line | Query pattern | Preparation/bound / trigger |
|---|---|---|
| M1 class-storage.php:189 | SHOW TABLES LIKE %s | P + esc_like fixed names; three-table install verification |
| U1 uninstall.php:30 | DELETE core options WHERE option_name LIKE %s OR option_name LIKE %s | P + esc_like fixed site-lock value/timeout prefixes; no LIMIT; uninstall only |
| U2 uninstall.php:41 | Same with fixed enrollment-failure transient value/timeout prefixes | P + esc_like; no LIMIT; uninstall only |
| U3 uninstall.php:66 | DROP TABLE IF EXISTS fixed owned table | Fixed three-name loop; purge-constant-only uninstall, no supplied values |

dbDelta receives the three full CREATE templates above. maybe_install also uses
dbDelta(query,false) to verify no remaining planned changes, not merely trust a
returned action string. WP option/transient/cron APIs may issue core-managed SQL
and use object caches; that is not invisible plugin-owned direct SQL.

## WordPress data inventory

All five owned option writes explicitly request autoload=false. Values are
strings/arrays, not arbitrary unserialize inputs. Defaults and use sites:

| Exact suffix after alynt_drime_backups_dashboard_ | Type / default | Source reads/writes and cleanup |
|---|---|---|
| schema_version | Scalar string; default''; current'8' | class-storage.php:180,199,233; uninstall.php:69 |
| source_policies | Sanitized site/source policy map; default[] | class-source-policy.php:130,143; uninstall.php:70 |
| diagnostics_settings | Array; enabled=false,minimum_level=warning,retention_days=14,max_events=200; clamps1..90days/10..1000events | diagnostics/traits/trait-event-log-settings.php:31,68,96; reporting.php:169; uninstall.php:71 |
| diagnostics_events | Redacted event ring; default[]; configured age/count bounds | diagnostics/traits/trait-event-log-storage.php:50,94; reporting.php:151,170; uninstall.php:72 |
| audit_events | Redacted operator audit ring; default[];90days/500events | diagnostics/traits/trait-event-log-storage.php:75,111; reporting.php:171; uninstall.php:73 |

`reporting.php` in that table abbreviates `trait-event-log-reporting.php` in the
same diagnostics/traits folder. Existing logging is read/filter/write bounded
option persistence, not a durable concurrent append-only audit ledger. Failed
option persistence is not silently promoted to successful durable logging.

| Transient after same prefix | Data / expiry | Source |
|---|---|---|
| poll_sites_lock | timestamp /600seconds | class-poller.php:29-31; traits/trait-poller-locks.php:29,33,43; scheduling.php:113; diagnostics scheduler:96; uninstall.php:25 |
| poll_site_lock_{absint(id)} | timestamp /600seconds | poller-locks.php:60,64,75; uninstall.php:28-36 |
| enroll_fail_{sha256(enrollment-id-or-fixed-missing-key)} | count array, capped10 /300seconds | class-enrollment-rest-controller.php:30-32; traits/trait-enrollment-rest-rate-limits.php:58,75,78,95; uninstall.php:39-47 |

Locks/rate limits use transient APIs and get-then-set; they are advisory, not
database-atomic admission fences. The S14 write fence must stand independently.
Core options date_format/time_format are read only in admin time formatters;
cron is owned by WordPress APIs for the two named poll/cleanup events. No plugin
post/user metadata or direct core post/user-table query was found.

## Relationships, lifecycle and deletion

- N.dashboard_site_id and A.dashboard_site_id refer logically to S.id. Reads
  filter/group by this identity (N2-N6,A1-A5,A11); no SQL parent join/FK/cascade.
  Repository callers validate the selected site; low-level storage is not an
  independent authorization boundary.
- A.requested_by records a WordPress user ID (0 allowed), not an enforced FK.
  User deletion does not cascade historical action records. No posts relationship.
- Pending enrollment originates in S6 after validated origin/token preparation;
  S8 consumes pending state and stores encrypted polling material. Site/action
  inserts set created_at/updated_at; mutable site/action paths set updated_at.
  Snapshots are immutable observations: validated/redacted JSON, encoding failure
  handled before INSERT, server UTC observation and SHA256 fingerprint. No SQL
  JSON constraint or content-dedup UNIQUE constraint is claimed.
- Revoke clears pairing/poll/action secrets and next poll; archive/pause are
  local state changes, not hard deletion of S/N/A. Existing local-removal UI is
  preview/eligibility, not a hard-delete apply path. History retention remains
  independent of revoked/archived parent visibility.
- N6 daily cleanup keeps one highest-ID row/site even if ancient; A13 keeps
  incomplete completed_at=NULL rows. These are explicit preservation policies,
  not missing cascade fixes to implement silently. No automatic expiry/purge of
  every row is promised; orphaned/manual-corruption repair is not this scope.
- includes/class-plugin.php:135 attempts both batches before fixed safe error
  logging. Log failure cannot recurse or turn SQL failure into success. Diagnostic
  logging remains opt-in/threshold-bound; audit ring separately bounded.
- Activation (class-activator.php:26) verifies schema before scheduling. Version
  mismatch runs maybe_upgrade(); schema8 is not an unverified install-success
  flag. Current marker is cleared before forced reinstall, table existence and
  dbDelta convergence verified, then marker persistence read back; safe error on
  failure. Core-backed failure/retry evidence belongs to its original receipt.
  No database downgrade/transactional DDL rollback mechanism is implemented.
- Deactivation only unschedules maintenance/global lock, preserving data.
  uninstall.php requires WP_UNINSTALL_PLUGIN and exact canonical folder name,
  clears two events and matching transient families, then **preserves** all three
  tables/five options unless the exact boolean purge constant is true. Purge then
  drops actions/snapshots/sites and deletes all five named options. All tables
  are covered; no unconditional purge introduced from generic prompt examples.
  Multisite/network activation remains undeclared and unqualified, not inferred.

## Schema/security/index analysis and approved dispositions

All primary IDs/relationships use unsigned bigint; versions/counts/flags have
appropriate declared numeric types, UTC dates use datetime, unbounded encrypted/
JSON data longtext, bounded names/keys varchar/char. Required fields/defaults,
nullable inapplicable lifecycle timestamps, fixed prefix/charset/dbDelta examined.
No new schema/type/index migration is justified from this source inventory.
No raw request identifier/value is interpolated: fixed table/column maps,
prepared scalar values, API writes and escaped LIKE prefixes. S14/S15 concatenate
only closed internal maps whose values are individually prepared. A12's LIKE
needle is a fixed literal, not user-controlled; U3 fixed identifiers require no
value placeholder. API inferred formats in A8-A10 are wpdb preparation, not
unprepared raw SQL. This does not prove genuine request authorization.

Index cross-reference: S id predicates10 call sites; enrollment_status7; public_id3;
poll credential predicates3 (including NULL due checks); archived_at2; remaining
expected_origin/pairing_expires_at/paused_at/next_poll_at each appear in the named
lookup/due variants above. N site equality/IN4 query sites plus retention grouping,
observed_at cleanup1, id cleanup1 and derived join1; A site predicates6, id4,
state2, public_id1, completed_at1, client_state/accepted_at/last_seen_at in A11.
S15 additionally predicates its fixed completion-map fields listed above.
Orders/joins are fully enumerated in the query tables; no hidden core join.

- PK/unique site lookups and pending_origin cover bounded enrollment shapes;
  poll_due matches status/pause/due access, but COALESCE ordering/OR may sort.
- site_latest supports N2/N3 highest-ID/site; site_observed supports history;
  retention_cleanup supplies time/id. site_fingerprint is declared but no current
  direct lookup filters by fingerprint; no speculative index removal chosen.
- Action site_requested/site_state_requested/state_expires/public_id/completed_at
  support the corresponding access patterns. Scalar counts still scan matches.
- S1 label/origin sorting, S3 COALESCE, N6 global derived MAX grouping, A11 scoped
  maintenance without LIMIT and A12 aggregate/JSON LIKE remain performance-watch
  shapes for06. No physical EXPLAIN/volume timing was run here; no index removal,
  speculative composite index or stronger bounded-cost claim is made. They are
  not new confirmed missing-index defects without workload/plan evidence.

Approved Phase2 decisions: no schema/index change; repair High poll completion
fence (REVOCATION_SAFE_POLL_COMPLETION_FOUNDATION.md and owned proof269/8), repair
Medium retention reporting (SCHEDULED_RETENTION_FAILURE_REPORTING_FOUNDATION.md,
RETENTION_CLEANUP_OWNED_PROOF.md214/9), repair Medium current identity
(SNAPSHOT_LATEST_SELECTION_CONSISTENCY_FOUNDATION.md). These are integrity/cleanup/
trust-input fixes, no migration required. Preserve existing API/retention/uninstall
contracts, avoid transactional/schema redesign. Source rollback is a reviewed
Git change; not a database rollback or permission to reintroduce known defects.

## Exact unfinished item and authority gate

Repository inventory/finding reconciliation is recorded; no further routine
maintenance is substituted for physical qualification. Recommend approval of
**Plugin Tester local-only Snapshot Latest-Selection Consistency and Capability-
Input Owned Proof**: owned disposable sites/snapshots fixtures and exact cleanup,
real prepared single/bulk/history SELECTs for reversed/tied/forward times, empty/
foreign sites and malformed payloads; isolated non-network capability consumers.
Preserve application state, history/retention SQL, schema and all guards. No
auth/user creation, installed lifecycle, actual remote request, schedule change,
retention DELETE, package/release/deploy or deferred V2.3 work. Fresh exact target
confirmation is required after scope approval. Prior214/9 does not qualify this
new SELECT. No such operation ran in this repository-only task.

After that proof, reconcile owning evidence and request separately scoped shared
toolkit publication if needed before canonical06. Do not mark05/FULL complete
from this source record alone. Product count5/8 after verified selector source
CI; this inventory/evidence closeout is not another invented product task.
