# M Genuine-Session Auth SQL Shape Compatibility Diagnosis and Harness Repair

Follow-on — 2026-11-06: separately approved [ONE requalification](SOURCE_POLICY_M_GENUINE_REQUEST_REQUALIFICATION_ATTEMPT.md)
at440430e/run42279779134 passed48231 checks/9 groups, all23 genuine-session cases
and strict cleanup/recovery. This does not identify the original uncaptured query.
M bounded request gate closes at the new cohort, not W/L/HTTP/target readiness.
Next gate: Repository-Only Current-Cohort W/L and Genuine-HTTP Qualification Closure
Design; no production wiring. Opportunities19, campaign10/10/static9 unchanged.

## Approval and coherent baseline — 2026-11-06

Explicit repository-only R3 diagnosis/guard-preserving harness repair, isolated
tests, focused review, validation, commit/push and ordinary exact-head CI. Clean/
synced review/restore baseline `d0a5c5330609ed321a977e7adaf2c03f6fc40d35`;
[ordinary CI42261912848](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/42261912848)
reverified completed/success. Toolkit baseline18a1679ad3124dd03e2106f8391b050bb2608ff4
clean; uploader3dfcb15b5cab8191df7d984251986ead140ded52 untouched. Version0.1.77,
schema8, PHP7.4/WordPress6.0 unchanged. Compare with the current
[implementation plan](IMPLEMENTATION_PLAN.md) and the
[failed one-attempt record](SOURCE_POLICY_M_GENUINE_REQUEST_QUALIFICATION_ATTEMPT.md).

No native rerun/dispatch, actual authentication/core bootstrap, SQL/database/schema,
owned native fixtures, installed/LocalWP/live/client/Drime, credentials/permissions/
schedules, installation, application wiring, grants/activation/rollback or package/
tag/release/deploy. Read-only static GitHub source text stayed in memory; no core
file was saved or loaded as executable code. Site Operations consulted; no site
target/Novamira operation applies. Consumed native opportunities18 and historical
campaign10/10/static9 unchanged; this approval grants no native allowance.

## Source-backed diagnosis and limits

The historical run's first-case auth failure identifies only the label
`unexpected auth SQL`; it retained no raw query. This repair does NOT retroactively
identify that exact statement or claim the failed run succeeded.

Static text was read from the SAME official commit
`cc101b64012b16d087780657a2b828ccd7794a63`. The existing wp-db.php pin was rechecked:
108975 bytes, SHA256
`f07bc6403ab6db30195cca8e47a384de11d582a66a64cb50449d4335edcc24cf`.
Session base7425 bytes, user-meta session implementation2990 bytes, meta.php62692
and user.php167611 bytes matched their existing approved auth pins; no pin/catalog/source selection
was changed or discovered dynamically for runtime admission.

The selected cold path is source-supported:

1. [WP_Session_Tokens::create/update](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/class-wp-session-tokens.php#L122)
   delegates the generated token/session to its manager.
2. [WP_User_Meta_Session_Tokens::update_session/update_sessions](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/class-wp-user-meta-session-tokens.php#L77)
   calls update_user_meta for the owned session_tokens key.
3. [update_metadata](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/meta.php#L180)
   reads existing metadata/IDs and takes add_metadata when that key is absent.
   Its default non-unique add branch does not require a new COUNT query allowance.
4. [wpdb::_insert_replace_helper/process_fields](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/wp-db.php#L2366)
   processes string charsets/lengths before issuing the INSERT; no fake warm cache
   or manually injected column metadata is selected by the proof.
5. [get_col_charset/get_table_charset](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/wp-db.php#L2887)
   populates a cold wpdb table/column cache with a SHOW FULL COLUMNS query. The
   selected session setup writes only the fixed owned usermeta table; the proof's
   fixed schema has four columns. Object-cache identity resets do not replace this
   normal wpdb metadata path. No charset filter shortcut is added.

The pinned update_user_meta wrapper directly delegates to update_metadata. The
selected ASCII session/field values use wpdb::strip_invalid_text's local ASCII
branch; no conversion-query fallback allowance is added. This is static branch
analysis of the selected fixtures, not runtime measurement or an arbitrary-content
claim. Unknown conversions and any other unselected SQL continue to refuse.

The prior observer has no SHOW shape at all. Thus a required cold-path metadata
read is independently confirmed missing and consistent with the failure location;
it is not a captured-query claim. No blanket schema-discovery allowance, option/
user-table metadata read, SQL rewrite or guess about other unknown statements.

## Narrow repair and preserved guards

[adbg_sql_class](../scripts/ci/source-policy-minimum-request-observer.php) accepts
exactly `SHOW FULL COLUMNS FROM ` followed by the backtick-quoted owned prefix plus
usermeta. Prefix still requires its exact source-owned format; the raw query must
match byte-for-byte, not merely a normalized SHOW prefix. No qualified database,
unquoted name, trailing whitespace/comment/statement, other table or altered form.
The returned class is session-schema-read, not a handle, SQL executor or grant.

The actual passive observer admits that class ONLY during setup, before final
authentication. It consumes the existing setup call counter with the same256-call
cap and returns identical SQL bytes. Unknown SQL still refuses; authentication
still cannot write metadata or discover schema. This does not add a query to any
application/production allowlist. The observer remains removed before guarded
entry; the runtime's two-entry query seal, guarded capability/nonce/type/record/
source/config order, no-reconnect rules and policy credits are unchanged.

No application includes, API/auth primitives, worker orchestration, schemas/grants,
fixture quotas, pins, source/runtime model, workflow flags, 20s body/25s worker/
10min job, per-native-operation or output/cleanup/staging bounds were modified.
The private manual mode is still separately default-off. No bypass via pre-primed
metadata, injected principal, reflected ready state, permissive fallback or retry.

## Discriminating isolated validation

The new [SQL-shape model](../tests/support/source-policy-minimum-request-sql-shape-model.php)
does not load WordPress, wpdb, mysqli or PDO-MySQL. It calls the ACTUAL passive
classifier/observer with source-derived SQL DATA and an in-memory delivery witness.
The cold trace covers metadata/cache-ID reads, required metadata discovery and
session INSERT. This is not genuine core execution, persisted-session success,
database dispatch or proof of the captured historical statement.

Before the repair, the new regression assertion-failed because the unchanged
classifier returned null for the required read. After repair the positive model
passes95 assertions, including exact setup/auth accounting and byte identity.
It refuses four other owned tables, ten malformed/foreign/qualified/suffixed/type
variants, final-auth discovery, unknown phase and budget overflow without delivery.
An ordinary known final-auth user read still passes.

[Node semantic controls](../tests/node/source-policy-minimum-request.test.mjs)
assertion-kill five valid source-copy mutations: removing the new recognition,
accepting broad SHOW prefixes, removing setup-only admission, removing the256 cap,
and changing returned query bytes. A syntax/launcher failure is not a valid kill.
Copies are restored/read back and their validated owned temporary directory is
removed. The new helper's declarations are inert under ordinary PHPUnit discovery,
covered by the extended definition-free test. No actual fixtures were created.

Focused request/distribution/production-route checks26/26 and the direct95-assertion
model passed locally, zero failures/skips. Related isolated PHP283 tests/1519
assertions and complete Node120/120 passed with0 failures/skips. PHPCS284/284,
new helper syntax, build and structure passed. Owning links/whitespace and ordinary
exact-head CI results are recorded in the toolkit only after actual completion.
Ordinary CI does not dispatch or establish a native proof.

## Source-scoped canonical review checkpoint

FEATURE_LIGHT_REVIEW covers the observer, Node tests, new inert model and direct
child/auth/fixture callers. Other risk: private proof SQL-shape/effect accounting;
no UI/REST/cron/application behavior. One source-confirmed compatibility omission
fixed with a pre-implementation regression; no remaining scoped quality issue or
manual design decision. Unknown historical query and physical behavior remain
proof limits, not evidence to weaken acceptance.

FEATURE_BLOAT_AND_STRUCTURE_REVIEW Phase1 uses explicitd0a5c53 above, the coherent
pre-repair boundary, not a verification tail. Canonical helper: two changed PHP
files,0 oversized; observer148 TOTAL and new model50 TOTAL, CODE=1 parser limitation
not used as code-LOC evidence. Both Consolidate/retain; the changed .mjs is inspected
separately (outside helper coverage). Direct callback/import/test references checked,
no removal/registration/split or Phase2 structure edit required. Repeat measurement
retains the same boundary. Historical pre-release02 deferrals remain untouched.

Final FEATURE_SECURITY_REVIEW after fixes: exact prefix/table/query bytes, setup
phase, paid bounded effects, no protected-policy discovery, no query rewrite/raw
payload retention, inert direct-execution model and owned-copy restoration. No
remaining confirmed scoped security defect/new07A handoff. No UI/UX workflow,
Database/physical-transaction proof or historical whole-plugin03/04/05/07/07A/13/
FULL readiness refresh. Full release readiness remains pending.

## Implementation-plan progress and next gate

A source-confirmed cold session-write metadata-read omission is repaired and
discriminated at isolated API/DATA layer. The historical native run is STILL
failed and genuine-session/late-source/timing/cleanup qualification remains
unproved. No native attempts were consumed by this repair and no production
availability, W/L, HTTP or installed-graph claim is made.

Recommended next gated slice: **ONE New-Head M Genuine-Session Request Owned
Requalification and Cleanup Attempt**, on this final reviewed/pushed repair head,
existing ephemeral CI lab only, with fresh source/runtime prerequisites, acquisition
and owned session/SQL/native fixtures/cleanup explicitly approved. Do not reuse
the failed1b28963 receipt/image or rerun its job. Stop on any further unknown shape,
unmet limits or cleanup failure; no automatic retry/source/budget expansion.
W/L and genuine HTTP/target evidence remain gates before ONE application switch.
