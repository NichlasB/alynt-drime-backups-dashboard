# Source-Policy Native Option/Hook/Cache Feasibility and Owned Conditional-Write Proof

## Authority, baseline and limits — 2026-10-27

Separately approved R3 manual proof, with fresh confirmation of `plugin-tester`,
`local-only`, LocalWP “Plugin Tester”, `http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`. Clean/synced repository
restore point: `6f141b9455e26212f4e29ff628c8ab957c7bab26`.
Continues the [DATA foundation](SOURCE_POLICY_CONDITIONAL_SAVE_FOUNDATION.md) and
[save/conflict design](SOURCE_POLICY_CONCURRENT_SAVE_CONTRACT_DESIGN.md).

Only manual proof/support scripts, isolated regression tests and owning records
change. Version0.1.77/schema8, all runtime includes/bootstrap/factory/form/legacy
setter, catalog/assets and dependencies are unchanged. No application option,
user, cron, authentication, HTTP, WordPress bootstrap, plugin lifecycle, client,
Drime, live-site, credential, service, release/package/tag or deployment change.
Uploader/toolkit remain read-only. Prior autonomous8/8 is not inflated.

The native writer is a fixed **fixture-only candidate in scripts/manual**, not a
production adapter/API or proof that the existing whole-option race is fixed.
No runtime loader references it. Synthetic DATA remains caller-constructible,
not authenticated provenance or a write-capable application handle.

Later source-scoped design limit,2026-10-27: the
[adapter integration design](SOURCE_POLICY_NATIVE_ADAPTER_INTEGRATION_DESIGN.md)
identifies core wpdb query reconnect/reissue behavior. This proof did not test
connection loss. Its no-retry statements describe candidate-level control flow,
not a qualified absence of core/driver replay;236 query-filter observations are
not a physical-attempt bound under disconnect. Preserve the actual passed CAS/
syntax/lock/hook/cache evidence, but do not promote this candidate unchanged.

## Audit first and exact qualified profile

Novamira HQ guide and complete inventory were read; exact local profile exists.
Used registry/LocalWP metadata, then bundled mysql.exe for initial read-only
SQL. An initial sandbox-denied10013 was distinguished from stopped services;
confirmed external-sandbox preflight found both10004/10005 listening. No restart.

Observed installed core7.1.3, bundled PHP8.5.12 and MariaDB10.6.23, database local,
autocommit enabled. wp_options is InnoDB/utf8mb4_unicode_520_ci, four expected
columns, full unique option_name plus primary/autoload indexes. No db.php,
object-cache.php or advanced-cache.php drop-in was present. No application
options were read into the proof; CREATE TABLE LIKE copies schema, not data.

Source-inspected core Options API: update_option is not compare-and-save;
add_option still uses an overwriting upsert; get_option uses alloptions,
notoptions and individual caches and may expose filtered projections. Explicit
autoload=false maps to off in this profile. Neither ordinary API is silently
credited as the atomic primitive. Installed core libraries execute directly,
with genuine WP_Hook/wpdb/core process-local cache, not wp-load.php/plugins or
the application hook graph. Initial zero-fixture preflight passed3 checks/one
metadata query (before later session-ID evidence was added).

Recorded source SHA256 identities (post-proof installed files, not a universal
version pin or externally authenticated distribution):

| File under wp-includes | SHA256 |
|---|---|
| option.php | FE3F0B7E115B2A50C31162ED6428ABE4088C080B0F71B9678D84806168FE90D0 |
| class-wpdb.php | E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD |
| cache.php | A219C2B57CC9990B560C6B497C877DB443B7E29239645A6A79B2D76839C46E19 |
| class-wp-object-cache.php | 1857B774BD9CFBAFBD50A1B1424F7DF637D202CF3F2253CDA9798D4AD56F3CF0 |
| formatting.php | 350F6B32303915DF970BB0E3EA360FCD54BB0B8A7ECC3D504C3D860AB9468D32 |

## Fixture-only native contract and genuine evidence

Final exact clone `adbp_d487c65e309a_options`, prefix gated by the manual CLI
entry. All statement targets are fixed/owned except the schema-only source.
The generated-SQL scope helper is not a parser/firewall for arbitrary SQL or
third-party callbacks. Native connection/session setup necessarily precedes its
query filter; it contains no application DML. No request-selected target exists.

The candidate takes one bounded authoritative raw observation (16KiB maximum),
uses allowed_classes=false/depth8 decoding and primitive DATA validation, then
checks expected revision before no-op. Unknown serialization/autoload profiles
reject. It uses one prepared UPDATE binding indexed option identity plus binary
identity/value/autoload and value/autoload lengths, or one strict INSERT under
the real unique key. There is no upsert, INSERT IGNORE, unconditional fallback,
retry/rebase, global lock, persisted revision or per-site migration.

Native duplicate1062 on that strict insert receives at most one bounded
follow-up observation; it is not treated as generic success. Qualified syntax/
lock failures are definite errors; unknown acknowledgements remain uncertain.
The attempt boundary is marked before query execution, so an exception after
submission cannot become a claimed no-write rejection. No transport-loss native
experiment was performed, nor is general driver error exhaustiveness claimed.

Core sanitation/pre-update hooks run once in their supported branch. A changed
candidate/filter projection is unavailable rather than permission to replace
non-target data. Read-projection/sanitize_key filters reject before protected
read; owner reentry rejects before a second read. Prewrite observers precede CAS;
success hooks run only after acknowledged mutation/private-cache invalidation.
Callbacks cannot make their arbitrary side effects transactional.

Cache handling deletes only relevant option/alloptions/notoptions keys in the
private core cache and confirms local absence; it never seeds a stale shared
map or globally flushes. Genuine Options API reads verify visible current
values after mutation, including legacy/autoload transitions and negative cache.
An explicit retaining-cache fault double proves uncertain completion, **not**
real external-cache failure or global cross-process linearizable reads.

Final restored native proof: **241 checks /9 groups,236 parent+peer query-filter
observations,1.768 seconds**, excluding bounded session setup and five child
statements. Groups:

1. Positive updates from off/no/yes/on/auto/auto-on/auto-off; exact non-target
   preservation; genuine cache reads; stale same-/different-site and stale
   already-satisfied conflicts; positive fresh no-op. Normal saves assert exactly
   one admission read plus one mutation; stale command asserts one read only.
2. Genuine strict creation, negative-cache invalidation, missing+required no-op,
   first-creator conflict and absent versus present-empty distinction.
3. Historical primitive site/source aliases normalize without dropping effective
   non-target policy when the native row is saved.
4. Malformed serialized bytes, object encoding and oversized storage reject
   without mutation; no magic-method-enabled object decoding.
5. Non-empty trace/argument/order equality against genuine add_option for fresh
   creation and update_option for update/no-op, with identical input maps.
6. Transform rejection, read-filter rejection before raw read, reentry refusal
   while the outer operation succeeds, and real post-commit observer exception
   with committed row retained and no compensation.
7. Genuine invalid-SQL failure is storage_error, not duplicate/conflict; injected
   cache retention yields write_uncertain while the actual row remains committed.
8. Two distinct genuine connections change state between admission/CAS: same-site,
   different-site, competing creator, case-only/trailing-space autoload and value
   byte changes. Every losing candidate conflicts and leaves winner bytes intact.
9. A private owned child locks the fixture row. The candidate hits the real
   one-second server lock-wait timeout, makes no change, then the child acknowledges
   rollback and exits. Session limits max_statement_time3/lock waits1, finite
   startup/release/reap deadlines and whole-proof30-second bound are asserted.

This is controlled interleaving/one real lock wait, not a production load test,
general SQL transaction proof or an application-authentication/HTTP acceptance.

## Negative controls, recovery and preservation

Two intentional native predicate controls failed assertions, then were restored
byte-exact: value predicate bypass killed at different-site interleaving;
autoload predicate bypass killed at case-only autoload interleaving. No mutant
was committed. SQL helper before/after SHA256:
`06F15190C9B1EDFBECDBDD0F7C4197A0520216A964D95BBC8410746DA061D50D`.
The unmodified final proof passed after restoration.

Initial harness issues were corrected without relaxing candidate semantics:
core's reference upsert was initially rejected; it now has a separately gated
exact reference-only window, with default candidate mode still rejecting it.
The creation reference initially used a different input map; identical-input
parity fixed the test setup, not a relaxed expected trace. Early stopped attempts
and negative controls removed their clones through finally cleanup. Routine
PHPCS formatting/signature warnings were fixed, not excluded globally.

Independent final mysql metadata confirmed zero owned tables, zero exact owned
sessions39/40/41 and transactions; both owned PIDs58344/28008 were absent, with
zero owned temporary directories. All77 existing application table checksums
and the exact table manifest matched the pre-fixture baseline, including
wp_options/users/cron storage. Runtime/assets/catalog byte identity also matched
the immutable repository baseline. No existing application state changed.

## Local validation and source-scoped reviews

New isolated SQL-scope suite17 tests/23 assertions (no native execution): exact
byte predicate, identifier/target rejection, forbidden statement kinds and
reference-upsert admission only in the explicit control window. Existing model/
handler suite remains76/234. Related randomized255/592 seed20261027 pass.
Full local1089/4550 has only the same two known missing-Sodium signing skips,
no failures/new skips. Node16/16; PHPCS179 configured files; structure137 runtime,
205 support,167 test files/165 class names; build, strict Composer manifest,
Composer/npm audits pass with zero advisories; no built-asset changes.

Canonical Feature Light: nine new manual scripts, one isolated test and owning
records, plus unchanged DATA/legacy store as direct context. Bounded proof-only
responsibilities; genuine/synthetic evidence explicitly separated; no runtime
architecture/framework/dependency change or unnecessary file splitting. No
further significant quality issue after the recorded harness fixes. No UI/UX
workflow: no UI, notices, public strings, endpoints, request or asset changes.

Final focused Feature Security: exact CLI/owned-prefix/private target gates,
prepared values, fixed generated identifiers, bounded decoding and process
ownership/cleanup reviewed. No unsafe callback/magic-method grant, raw SQL/error
logging, arbitrary target/command, global cache flush or application fallback.
Reference upsert is rejected in default candidate mode; native semantic controls
protect value/autoload predicates. No further scoped security finding. Existing
whole07/07A/13/FULL and release readiness remain incomplete; no toolkit checklist
or historical whole-plugin row is refreshed by this manual proof.

Publication: implementation `5dee54486b6ff7684f9eed3cea762c77456e479c` passed
[exact-head CI40577776136](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40577776136).
Actual job logs: PHP7.4/8.3 each1089 tests/4557 assertions, no failures/skips;
Node16/16 per job, all configured gates and both dependency audits pass with
zero advisories. CI executes isolated regressions/lint, **not** this manual
Plugin Tester proof or an application/native integration rerun.

## Implementation-plan progress and next gate

The owned profile now supplies genuine conditional-write/hook/cache evidence,
not just RAM assertions. The real application still uses its unchanged legacy
setter/form, so the lost-update issue is **not repaired in production code**.
WordPress6.0/other core versions, persistent/external caches, replicas, multisite
and the installed application's live callback graph remain unqualified. A
bootstrap-free library proof cannot establish their support or authentication.

Recommended next gated slice: **repository-only Source-Policy Native Adapter
Compatibility and Guarded Form Integration Design**. Define the supported
core/hook/cache profile and unsupported-profile behavior before designing the
single authoritative native path for the form/handler/legacy wrapper. Keep that
slice design-only, with actual writes, site operations and production wiring
disabled. Do not silently connect this fixture candidate or retain an unsafe
parallel legacy fallback; live deployment remains separately gated.
