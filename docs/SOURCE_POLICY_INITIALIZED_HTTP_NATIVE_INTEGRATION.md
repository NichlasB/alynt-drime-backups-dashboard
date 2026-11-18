# Initialized-Fixture Native Loader/Controller Integration

## Repository-only query-refusal diagnosis and metadata repair — 2026-11-18

User approved the prior recommended Repository-Only Initializer Query-Refusal
Source Diagnosis and Guard-Preserving Repair. Scope:R2 source/isolated tests,
reviews,publication/exact-head ordinary CI;NO new native attempt,core bootstrap,
SQL/site/LocalWP/fixture operation or added diagnostics. Clean/synced review and
restore baseline `d8c70438c93eb9028775b92641efbc3d18004805`;
CI44410655884 reverified actually successful. Toolkit baseline9ce52c15ac41c67edadda6c3502d11a5d554c74c.

Bounded three-branch review:query registry permits only the exact observer and
pinned wpdb placeholder restorer;the shared clock retains the original parent
origin/deadline and monotonic/time/SQL credit. Neither is bypassed or changed.
The SQL branch has a confirmed source-compatibility omission. Exact pinned core
db96f5eaa77a8d3e8eec8923a518fa2326f0d46a class-wpdb.php was read in memory,
not loaded;SHA256 e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd
matches the existing pin. [Pinned source](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/class-wpdb.php#L3305):
update2680 → process_fields2812 → process_field_charsets2934 → get_col_charset3375
→ uncached get_table_charset3276 → line3305 SHOW FULL COLUMNS FROM the table.
The existing options-only initializer grammar rejected that read-only metadata
shape,even though its ordinary option UPDATE/INSERT shapes were admitted.

Repair:add precisely `SHOW FULL COLUMNS FROM <derived owned options table>` to
the initializer grammar after the existing bounded tokenizer. This IS a narrow
finite grammar addition,not a claim that acceptance bytes are unchanged. It
preserves the existing options-only/no protected-row read/no DDL or foreign-table
policy. Metadata returns column definitions,not option values. No LIKE/WHERE,
database-qualified name,other table,generic SHOW/DESCRIBE or additional statement
is admitted. Every metadata query consumes the same original SQL/time credits;
the real native registry/refusal/shutdown/anchor/release path stays unchanged.
Measured HTTP SQL observer remains byte-identical. No callback/provider/handle,
new seed list,query rewrite,resource increase or production wiring added.

Red-to-green:the exact source-shaped quoted-table metadata fixture failed before
repair with initialization-sql (not syntax/tooling);afterwards metadata then an
ordinary cron UPDATE return same bytes and finish with exactly2 charged queries.
Nine hostile metadata/protected-write variants refuse and poison the guard;
one remaining credit cannot buy another query and original deadline still expires.
Two new RAM-only controls are behaviorally killed:remove metadata availability,
or replace exact-table equality with a broad prefix match. Existing seven guard
controls and native approval/clock/registry/non-catchable refusal oracles retained.
These are synthetic SQL/isolated APIs/virtual clocks,not genuine core or DB proof.

Canonical Feature Light SOURCE-SCOPED:three code/test files plus four owning
resumes,this record and direct unchanged initializer/clock/registry/controller/
observer callers. One compatibility finding fixed;no remaining scoped quality
finding/manual decision. Five-line cohesive guard addition with reused helpers;
no meaningful bloat/structure trigger,no UI/UX (backend-only). Final Feature
Security SOURCE-SCOPED:exact typed table binding,pre-query charging,protected
reads/writes/DDL/foreign effects and original fail-closed terminal preserved;
no confirmed security defect or07A handoff. No whole-plugin review/readiness row
or historical refusal is refreshed. Local validation:focused native-model11/11,
guard5/5 (nine semantic controls),HTTP140/140,canonical Node295/295,no failures/
skips. Full PHP3070/25340,0 errors/failures,two existing Sodium-unavailable skips
confirmed separately by signer verbose5/9. PHPCS305/305,PHP/Node syntax,
structure/build,334 local links and whitespace pass. No new native attempt.
Ordinary exact-head CI evidence belongs in the canonical toolkit closeout after
actual completion;do not infer native success from ordinary CI.

Native causal limit:run44410105113 did NOT export the failing registry/clock/SQL
condition or query. This source-backed incompatibility is fixed,but it is NOT
established as that run's actual cause and may not be its earliest refusal.
No completed initializer/shutdown/full-baseline/HTTP evidence is added;other
finite grammar incompatibilities remain possible. ONE1/1 exhausted,cumulative42,
milestones4/10 unchanged. Stop before native execution. Recommended next gated
slice:ONE New-Head Initialized-Fixture HTTP Owned Requalification and Cleanup
Attempt,with fresh existing prerequisites and exact owned cleanup. Separate
explicit authority required;do not add diagnostics or auto-retry on failure.

## Approval, source boundary and allowance — 2026-11-18

User approved native loader/controller integration plus **ONE** owned ephemeral-
CI qualification/cleanup attempt,fresh prerequisites,unchanged guards/budgets and
production wiring disabled. This is a scoped R3 lab exception,not a general risk-
tier increase or LocalWP/live/client/Drime operation. No registered site target,
registered-site bootstrap,SSH or Novamira operation applies. No backup/restore,existing
application/account/permission/credential/schedule change,package/tag/release/deploy.

Clean/synced coherent review/restore baseline:
`fef8b5f5bf419aed2f66c120be0058c2bd2f7a2c`;ordinary CI44403487577 reverified
actually successful. Toolkit baseline`f5e44544bc1e109299eb5a7dc39abbace6534482`;
uploader3dfcb15 unchanged/outside scope. Version0.1.77/schema8/PHP7.4/WordPress6.0,
core7.1.4/runtime/theme/server/source pins and public/storage contracts unchanged.
Original recovery3/3 remains exhausted,cumulative41,product milestones4/10.
New separately approved initialized-v2 ONE allowance:1/1 CONSUMED at run44410105113;
cumulative42,product milestones4/10 unchanged. No second native attempt authorized.
No repeat-until-quiet initialization,new seed list,diagnostic layer or extra run.

## Fixed native path, not the isolated preparation adapter

The existing manual Linux qualification workflow adds `execute_initialized_http`,
default false. It requires genuine-request and genuine-HTTP approvals,exact master
head,owner actor,run_attempt1 and no setup-diagnostic mixing. Existing v1 remains
the default;its profile validator does not accept v2. Native controller admission
runs before owned resource creation. Ordinary push CI never runs qualification.

The native initializer is a distinct fixed CLI child/argument under the existing
Linux/PHP8.5.12/mysqlnd/NTS64/no-INI runtime gate. It receives one bounded private
stdin ticket bound to the same run/head/image/root-owner/manifest,remaining SQL
credit and original shared setup clock. Installer and initializer both check the
same60s setup deadline before each observed query;their combined WordPress query
credit stays1024. Original600s job,60s HTTP,5s request/startup,30s cleanup reserve,
64 exchanges,64MiB and all channel/source/DB limits stay unchanged.

After the installer exits,the parent requires zero all-server sessions/trx,
DDL revoked/read back,exact three users/roles and non-autoloaded protected rows.
It snapshots protected policy/sentinel/config/roles,users/usermeta/inert records
and schema metadata—not all options. Schema projection deliberately excludes
allocator counters,which ordinary allowed option INSERTs can advance without DDL.
No cron/widget/transient namespace is excluded from the later full baseline.

ONE fresh normal CLI wp-load/wp-settings runs without preexisting ABSPATH,
WP_INSTALLING/SHORTINIT override,login/cookie mint,manual user selection,owner/
provider/native writer or plugin lifecycle. The existing finite initializer SQL
guard does not inherit the installer's broad setup bypass. The actual query-hook
map permits only its priority1 observer and the pinned genuine wpdb priority0
placeholder restorer;late rewrites or foreign origins refuse. Network/mail/local/
hard-rewrite attempts terminate;protected cache/autoload and owner source remain
absent. Native refusal exits1,so catching a failed guard cannot restore success.

The final sidecar is registered AFTER real stock shutdown_action_hook,requires
shutdown exactly once and actual source/runtime/cache/principal checks,and keeps
observers armed. A late query after sealing terminates even if a sidecar exists.
The parent requires exit0/no signal/no stderr,then independent all-server release,
unchanged source and exact before/after protected anchors. Only that independent
comparison supplies `anchors_unchanged`;the child cannot assert it for the parent.

Only then freeze the original full non-target invariant and enter strict measured
HTTP:stock login/cookies/POST,all25 exchanges/13 cases,original positive saved/noop/
stale and denial/read/write/result checks,EOF,live server identity,release and exact
cleanup. Versioned private context/request sidecars and DATA acceptance prevent
v1 receipts substituting for v2. No request write exception or policy relaxation.
Recovery recognizes the one fixed initializer argv/PID/start/executable/owner tuple;
it never gains a generic command or unknown-process cleanup capability.

## Source-scoped review and validation

Canonical Feature Light:bounded CI helper/workflow/model/test changes plus direct
worker/router/source/database/clock/recovery callers. No installed runtime include
or dependency change. Existing v1 case/limit/observer controls remain. A proposed
test edit dropping native files from coverage was rejected by automated review;
it was NOT applied. The accepted alternative retains every file and SQL hash and
adds actual default-off/actor/head/attempt/private-role and v1/v2 binding oracles.
No remaining scoped quality finding or manual policy decision before this run.

Canonical Bloat/Structure Phase1:explicit baseline above;five PHP files,0 over300
TOTAL lines (68,40,165,123,130). Canonical CODE output was degenerate1 per file
under this PowerShell host and is not relied on. The helper excludes .mjs;its
physical-line function additionally measured the nine changed modules/tests at
20–197 TOTAL,all below250. Source/callback references checked;no stale/dead/
diagnostic fallback to remove or useful split. Classification:Consolidate
without edits;no Phase2 cleanup needed,no unrelated toolkit fix or forced split.
This is not a whole-plugin file-structure review. Backend-only;no UI/UX workflow.

Final Feature Security SOURCE-SCOPED:closed job/model/ticket/role/source/storage
admission,per-query shared credits,strict initializer effects and exact anchors,
unchanged capability/nonce/type/record/revision/request rules,non-catchable late
refusal and owned-only cleanup. No new public grant,handle return or endpoint.
No remaining scoped confirmed defect/07A handoff. Physical effects are NOT proven
by isolated tests;the approved native attempt is the remaining runtime gate.
Whole-plugin release readiness and historical review rows are not refreshed.

New isolated tests cover default-off/closed native selection,missing/stale
initialization,all13/25 v2 DATA positives and v1 refusal,independent anchors,
shared PHP clock/native-role checks,closed query registry,fixed recovery argv
and non-catchable query refusal before/after sealing. Three source controls are
behaviorally killed (native approval,deadline,exit),not credited for parse errors.
Local full Node294/294 and PHP3070/25340 passed,0 errors/failures;two existing
Sodium-unavailable local skips confirmed by signer verbose5/9. PHPCS305/305,
structure/build and321 relative links passed. No tests/skips/timeouts or limits
were weakened. Ordinary exact-head CI and the ONE native outcome are recorded
after actual completion. No genuine native success or retry credit is inferred.

## Actual publication, ONE outcome and exact cleanup — 2026-11-18

Published integration `dab8026a4808b35c7c3609401f342e8c6b349973` on master.
[Ordinary exact-head CI44409706066](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/44409706066)
ACTUALLY completed/success,both matching-head checks. PHP7.4 3070/25342 and
PHP8.3 3070/25347,plain OK/no skips;Node294/294 each,failures/skips0;PHPCS305/305,
structure/build pass,Composer/npm audits0. Legacy status contexts0 is not failure.
Clean local/remote exact head verified before dispatch. Installed runtime,
dependencies,source pins,assets and production wiring unchanged.

[ONE native run44410105113](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/44410105113)
at the SAME integration head ACTUALLY completed/failure. Fresh unchanged
environment prerequisite passed;native audit29/zero execution then291 checks/9
groups passed with independent live/post-exit zero. Fresh genuine-session source
audit211/zero execution then23 cases/12006 checks/9 groups passed,independent
live/post-exit zero. Each prerequisite's owned cleanup/reconciliation passed.

Installer and parent pre-initializer admission reached the fresh initializer.
The initializer refused at core-bootstrap with exit1/no signal,stdout0/stderr194.
Existing bounded packet:RuntimeException,proof line24,assertion
`c7e681bc049d4e561fc6c944e7a797fbd2b0aa5cbbaeb2337f8d3632c81c6478`.
Source hash comparison identifies the fixed `initializer-query-refused` label,
not a specific query or core caller. The catch covers hook-registry validation,
shared-clock evaluation and query-guard filtering;which condition failed and
whether any SQL was denied remain Unknown. Line24 is the synthetic refusal
emitter,not an upstream core location. No initializer-completion/shutdown receipt,
post-initializer anchor comparison,full baseline or HTTP case is proven.
Do not confuse this refusal with the historical v1 request write-scope cause.

Exact `OWNED_HTTP_CLEANUP exact-owned-zero`;always recovery reports absent;
final native reconciliation containers0/directories0/tmpfs released/
reconciliation_failure0 and preflight containers0/private_directory0/core_checkout0.
Both always steps completed/success. The generic summary "refused or cleanup
unknown" does not override those explicit cleanup observations. No owned
resource remains reported;no retry,site operation or application change ran.

Implementation/publication/reviews are complete;native HTTP qualification remains
BLOCKED,not complete. The ONE allowance is exhausted,cumulative42,milestones4/10.
Recommended next gated slice:Repository-Only Initializer Query-Refusal Source
Diagnosis and Guard-Preserving Repair. Bound the investigation to the three
existing catch branches and source-backed isolated reproductions;identify a
concrete correction or recommend reassessment. No new diagnostics/native retry,
acceptance relaxation,budget/source-pin or production-wiring change is implied.

## Proof limits

The intended qualification is functional private HTTP against an initialized disposable fixture,
not cold-after-install immutability,FPM/third-party/installed-graph eligibility,
pre-router containment,hostile-runner resistance or the still-unwired real form.
The actual lost-update application switch remains disabled and separately gated.
Existing native cohorts remain on their original source/assurance boundaries.
