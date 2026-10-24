# V2.3 Pre-Owner Protected-Read Contract and Isolated Authentication Profile Design (R2)

2026-10-24. Approved design-only repository work. No executable/test/dependency,
WordPress, SQL, fixture, account/permission, configuration, service, HTTP or native
experiment change/run. Storage writes, admission, production wiring, feature
grants, activation and rollback remain disabled. Versions remain dashboard0.1.77
and uploader0.5.25. No package/release/tag/deploy.

Excluded coherent review bases:dashboard `0bedf240094ec04d01e845707f3a3618218f281f`,
uploader `20ee1c5c4f8e6a8bf8e26c29840527b4eeb0e714`, toolkit
`cc3e58e7c0f35b2b61208c1f28f574d2aa879ec2`.
Parent:[fixed profile/HTTP design](V2_3_FIXED_PROFILE_ADMISSION_GENUINE_HTTP_INTEGRATION_DESIGN.md).
Measured predecessor:uploader `docs/V2_3_PRE_OWNER_BOOTSTRAP_CONTAINMENT_OWNED_PROOF.md`.
Its208checks/two owned profiles and preservation results are historical evidence,
not rerun or reclassified by this design.

## Decision: ordinary routes remain unsupported; investigate a constrained test profile

Keep the strict protected-value boundary. The measured genuine7.1.2 bootstrap
tries bulk autoload options at its installation check before routing or auth.
Do not change that result by allowing incidental protected reads, declaring
autoloaded credentials harmless, setting an installation/SHORTINIT flag, raising
minimum versions, faking core functions/screens or relaxing the current proof fence.
Ordinary Tools/admin-post installations remain **Unsupported/Blocked** for positive
closed-entry admission. This is not a conclusion that rollback can never exist.

Select one next feasibility candidate: a **separately owned, read-only database
projection profile**, backed by populated disposable source tables and fixed SQL
views. A dedicated low-privilege invoker can read only those views, not their base
tables or application tables. A separate non-request definer has only the specific
owned source SELECT privileges needed by the views. No application database account,
stored secret, source data or existing wp-config is changed or reused in the worker.

This is a design selection for an isolated test candidate, NOT a supported
production profile, working SQL configuration or positive owner implementation.
It intentionally changes the test worker's database capability boundary and will
require new explicit owned account/permission/DDL approval before any proof.
Do not build more generic profile DATA seals as a substitute for that evidence.

## Protected-read contract: define the value boundary, not a misleading counter

Protected participants include `alynt_drime_backups_settings`,
`alynt_drime_backups_schedule_safety_state`, their payloads/aliases/serialized copies,
and protected ledger/safety/native participants outside the fixed auth bootstrap.
No protected value may enter the request's SQL results, object cache, globals,
callbacks, errors, receipts, logs or response before the existing authorization
and intent boundary. A filtered result after PHP has read the value is too late.
The closed entry still does not acquire protected capture or writer authority.

Required auth user/session/capability/role reads remain distinct, narrow sensitive
reads necessary for genuine core authentication; they are not permission to read
settings/safety data. They must have their own fixed participants, bounds and
genuine current-session/capability checks. A cookie, claimed actor, profile flag,
header, historical receipt or private class cannot authenticate the caller.

Application-value containment is not a claim about database engine page access:
the server may inspect underlying storage while evaluating a view or indexed read.
No claim of zero physical page IO, timing-channel isolation or hostile database
administrator containment. If the required contract instead forbids even server-
side evaluation of populated protected base storage, this candidate is incompatible
and must stop, not redefine a passing result. The old proof's0 executed data-query
result stays0; a future projection run must report its actual safe queries, not0.

The candidate is a different fixed test profile, not a changed allowance in
`bootstrap-containment-policy.php` or the owner. Bulk option reads are admissible
only from an independently proven safe projection: an arbitrary SELECT from an
owned table that still contains canaries remains forbidden. A sanitized snapshot
or empty shadow table alone is insufficient.

## Fixed database projection and ownership contract

For the first proof, use only disposable owned source tables and owned view names;
no views over existing application tables, no actual-user copying. Use exact random
ownership markers and collision previews before creation. The later operator must
fix concrete names/columns/key literals, not accept SQL, schema, profile or account
selectors from a request. Production identifiers are never substituted.

| Participant | Fixed projection and evidence obligation |
|---|---|
| Source options | Genuine option column layout, unique option_name, populated safe options plus both protected-name synthetic canaries; canaries never omitted from source to obtain success |
| Readable options view | Explicit option_id/option_name/option_value/autoload columns; positive key allowlist, no SELECT-star or broad negative-name-only predicate; source canary rows remain inaccessible |
| Source auth data | At most two owned fixture actors, owned sessions and role/capability metadata only; no actual application user/session access |
| Readable users/usermeta views | Explicit core-required columns, fixed owned actor IDs and permitted metadata keys, correct view-prefix role/capability keys; never arbitrary actual user rows |
| Excluded participants | No readable application base tables, protected source columns/rows, ledger/safety tables, routines, files or write-capable handle; direct qualified access and UNION bypass attempts must fail |

Initial options allowlist for bootstrap feasibility only:siteurl,home,blogname,
active_plugins,template,stylesheet,WPLANG and the exact view-prefix user_roles key.
At most8 unique options with individually bounded synthetic values and total64KiB
fixture payload;at most2 users/16 usermeta rows. Missing dependency or oversize is
Blocked, not automatic key discovery/copy or silent enlargement. This is not yet
a claim that these keys are sufficient for full Tools or admin-post routing.
Do not put settings/safety payloads in an allowed key, usermeta, plugin list,
theme, option alias or serialized wrapper. The owned safe-value manifest must be
reviewable independently of the exclusion predicate. Unknown source taint blocks.

Prefer simple explicit-column/predicate views with a fixed algorithm validated on
the actual server. Do not use a view LIMIT as the security/resource guarantee;
bound source rows/unique allowed keys and statement/process budgets separately.
Some views are writable:read-only behavior comes from effective invoker privileges,
not the word VIEW, a PHP readonly flag or WITH CHECK OPTION. No routines, user/session
variables, caller-defined functions, dynamic views, cross-schema indirection or
query-rewriting adapter. SQL SECURITY INVOKER cannot borrow unavailable base
privileges;the selected candidate requires a separately restricted definer.
[MariaDB CREATE VIEW](https://mariadb.com/docs/server/server-usage/views/create-view)
documents the privilege context;it does not establish our local configuration.

Use two fresh owned database principals, never existing identities:

- Definer:only SELECT on the exact owned source tables/columns, no application
  grants, roles, delegation or write privilege. Its authentication must be disabled
  or otherwise unreachable from the request, with version-specific behavior proved.
- Invoker:only SELECT on exact owned views, no global/schema/base-table privileges,
  inherited roles, proxy/delegation, write/DDL/FILE/EXECUTE/CREATE USER/GRANT OPTION.
  Verify actual effective identity/permissions, not just intended GRANT text.
- Supervisor:approved privileged provisioning/fixture mutation/cleanup only,
  never a request fallback. Keep its credentials and handles out of worker memory,
  environment, config, argv and private transcript. In particular, DO NOT inherit
  the predecessor proof's root MYSQL_PWD into a read-only projection worker.
- Worker:allowlisted environment and source/ini only, sole limited credential via
  bounded private process input, no credential-bearing files/logs/URLs or actual
  wp-config include. No parent root environment inheritance, shared browser jar,
  credential capability export or escalation after a refused query.

MariaDB has distinct global/schema/table privileges;effective grants and account
matching need actual version-specific proof. Use positive least privilege, not
newer DENY syntax unavailable on the predecessor's10.6 server. Source inspection
is not that proof. [MariaDB GRANT](https://mariadb.com/docs/server/reference/sql-statements/account-management-sql-statements/grant)

## Genuine bootstrap, cache and eventual authentication composition

Start a new single-request worker with genuine cold, nonpersistent core cache,
actual core/source/config identities and no existing content/drop-ins/plugins.
Point core's owned table prefix at the proven views using a separate fixed test
bootstrap;do not modify core, its queries or application wp-config. Observe real
query/result/cache provenance from the first data access. No preloaded alloptions,
pre_wp_load_alloptions short circuit or cache adapter may manufacture feasibility.
This avoids relying on the6.2-only filter but does not prove WP6.0 compatibility.

All autoload values on the populated source—including both canaries—must vary in
controls. Test genuine core's empty-autoload/all-options fallback through the view;
safe rows must still be visible,protected values absent. A missing required read or
attempted core write remains a refusal/Blocked dependency;never reconnect as root,
permit a write, alter core flags or expand grants to keep bootstrapping.

Only after this prerequisite is earned may separately approved genuine HTTP/auth
work compose it with the same fixed owner. Keep real core routing, screen, cookie
validation, session revocation, fresh capabilities, separate GET/POST guards,
blog-bound small-nonce domain and exact nonce verification returns1/2. Real owned
users must earn later positives;caller actor values and view receipts cannot.
No reflection/bind/eval or copied owner decision logic. The current owner has no
such profile bridge and `entry_available()` remains literally false.

Fresh role/session reads must reflect controlled source revocation, not frozen
authentication snapshots or a warm cache. Even future owned-user success is not
authority for actual site users, client cadence, grants, activation or rollback.
No design for promoting fixture credentials/data to production is supplied here.

Preserve original owner initial10s/root20s and outer HTTP1000/2000/4000/10000ms
connection offsets. Neither view provisioning, warm preflight, bootstrap nor owner
entry resets an active request epoch. SQL statement limits do not replace parent
worker/pipe/listener cleanup deadlines;no queue, retries or shared-service kills.
The next prerequisite proof needs no HTTP listener or response-loss experiment.

## Ordered next proof and honest stop conditions

1. Obtain fresh exact Plugin Tester local-only target confirmation plus explicit
   scope for owned tables/views and TWO disposable database principals, SELECT
   permission setup, private credentials/processes and previewed cleanup. No existing
   account/grant, application data, service or configuration mutation. Site Operations,
   Novamira and bundled-MySQL read-only preflight precede any new database operation.
2. Before creating anything, inventory application tables/checksums, existing owned
   collisions, exact account host identities and supported server/core runtimes.
   An unavailable WP6.0 runtime stays Blocked;no implicit install/download.
3. Seed bounded safe rows AND protected canaries, create fixed views/restricted
   principals and prove direct limited-connection safe-read positives. Prove base,
   qualified schema/UNION/routine/file paths and INSERT/UPDATE/DELETE/DDL/privilege
   escalation denied;all negative writes may target only owned disposable objects.
4. Disable the PHP query observer in a control:the DB projection must still prevent
   protected-value exposure. No observer-only safety claim. A deliberately unsafe view over the same
   canary source, read only by an OWNED control principal, must make the zero-value
   assertion fail. Never grant access to an actual application table for this control.
5. Attempt genuine cold bootstrap only after capability positives/negatives pass.
   Trace first safe query/cache hydration and actual blocked/completed stage. A
   bootstrap completion is not an authenticated Tools route or owner checkpoint.
   Keep owner unavailable;stop at any unsupported read/write or resource dependency.
6. Close/join exact owned processes/connections before previewing and removing
   owned views, then owned principals/privileges, then owned source tables after
   dependency checks. Verify all absent and application checksums/config/service
   identities preserved. Preview exact grants and ownership before DROP USER/VIEW;
   unknown ownership/partial cleanup is Blocked,not broad reset/cleanup permission.

Keep attempts,allowed safe results,prevented protected results,control-only canary
disclosures,cache hydration,auth and owner stages separate. Required guards must
be independently assertion-killed;an always-denying account or empty projection
cannot pass. Never label isolated policy/receipt tests Database Review, physical
transaction proof or genuine authentication. No normal-profile support conclusion
from a fixture-only projection, even if actual core bootstraps successfully.

## Review, publication and next gate

Canonical Feature Light scope:four plugin Markdown files plus directly read
owner/scope/auth and predecessor proof source;Other/design. No runtime/UI change,
no new tests or artificial success path. The review makes the engine-page/value
distinction,raw/root credential separation,effective privileges and non-vacuous
unsafe-view control explicit. Bloat/Structure and UI/UX N/A:no executable/rendered
delta. No formal DS3/Database/whole-plugin13/FULL_PRE_RELEASE completion.

Final canonical Feature Security after document/preservation validation checks
that ordinary profile remains unsupported,projection is a candidate not authority,
definer/invoker/supervisor secrets are separated,no read exception is applied,
actual auth/fences/budgets unchanged and new permissions/cleanup are separately
gated. No confirmed in-scope design security defect or security-fix regression
handoff remains;actual capability/environment evidence is still unearned.
Light review found/fixed one Low wording ambiguity:the DB must prevent protected-
value exposure with the observer removed,not merely "contain values". No remaining
in-scope defect/manual design decision;the next operational permission approval
below is a scope gate,not a hidden implementation completion claim.
Publication/exact-head CI and executed preservation results belong in toolkit's
new source-scoped record;historical readiness/208-check evidence stays historical.

Executed preservation validation:isolated uploader owner/ingress/proof-boundary
PHP149tests/1890assertions,OK,no failures/skips;dashboard299/1423,2 existing Windows
Sodium skips.35 local Markdown links across3 dashboard docs resolve;whitespace
checks pass. Executable/tests/assets/scripts unchanged against both excluded
bases. These are source-preservation checks,not a new WordPress/database/projection
or authentication proof. Canonical uploader source safety OptionA at clean20ee1c5
confirmed latest archive20261024-125512 and0 dirty/untracked files before editing;
no new archive,checkpoint,restore apply or site/database backup for this docs slice.

Implementation-plan progress:R2 now has an explicit strict value contract and
concrete isolated-profile investigation path,not more ordinary-route retries.
Recommended next gated slice: **Plugin Tester local-only V2.3 Owned Read-Only Core
Projection Feasibility and Privilege-Denial Proof (R2)**,including bounded disposable
source tables/views,TWO owned database principals,permission setup and exact cleanup.
Approve that operational/permission scope separately before implementing or running
it. Keep admission,wiring,feature grants,activation/rollback disabled;R3-R7 independent.
