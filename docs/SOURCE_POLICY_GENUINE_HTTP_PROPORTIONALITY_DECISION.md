# Genuine-HTTP Proof Proportionality and Acceptance-Contract Review

## Status, authority and immutable review boundary — 2026-11-18

**Design review complete; the replacement below is a recommendation, not an
activated acceptance policy or native execution grant.** The existing HTTP v1
predicate, workflow, source pins, tests and production wiring remain unchanged.
User approved repository-only design, validation, commit/push and exact-head CI;
no native attempts, added diagnostics or production wiring changes.

R1 documentation about a future R3 owned-fixture proof. Coherent source/review
and restore baseline:9ce9ce4aaa3d597997a52ccb524c4242775b36f5,clean/local-remote equal;
[ordinary CI44382824492](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/44382824492)
was successful. Toolkit baseline3da5bbc22ed1f698fab362c7d91d3de27e99d141,clean/synced.
Uploader3dfcb15b5cab8191df7d984251986ead140ded52 remains outside this workstream.
Version0.1.77/schema8/PHP7.4/WordPress6.0 and public/storage contracts do not change.

No WordPress/core authentication or bootstrap,SQL/schema/fixture operations,
LocalWP/installed/live/client/Drime/SSH inspection,listener/native process,
acquisition/installation,manual dispatch/rerun,credentials/schedules,package/tag/
release/deploy or diagnostic extension. Existing isolated API/DATA tests and
ordinary publication CI are validation,not native qualification. Site Operations
was consulted;no registered site target or Novamira operation applies.

## Finding: the feature is narrower than cold whole-core immutability

The [concurrent-save design](SOURCE_POLICY_CONCURRENT_SAVE_CONTRACT_DESIGN.md)
addresses a dashboard-local lost update in one monitoring-policy map. The
[current writer](../includes/class-source-policy.php) still reads and replaces
the whole map;the [current form](../includes/admin/traits/trait-admin-page-basic-detail-helpers.php)
still has no revision,and the [handler](../includes/admin/traits/trait-admin-page-local-actions.php)
still calls that legacy setter. This review neither delivers nor wires the fix.

The actual requirements remain:authorized same-observation mode/revision view;
capability,action/user/session nonce,types,canonical site record and revision
checks before protected owner/config/policy/native work;one conditional mutation;
saved/no-op/stale-conflict results;unrelated policy preservation;truthful uncertain
outcomes without retry;safe notices/audit and legacy compatibility at the later
cohesive application switch. A revision is not authorization or a nonce.
Normal core configuration/ambient authentication reads are not protected owner
capture;SQL counts alone cannot establish absence of PHP configuration reads.

There is a real HTTP gap:the retained23-case genuine-session proof does not show
stock password login,cookie/header/POST ingestion,normal full bootstrap or the
actual application form. Do not remove that gap or relabel CLI evidence as HTTP.
The private bridge also does not demonstrate the still-unwired application UI.

| Evidence or rule | What it actually establishes |
| --- | --- |
| [Original owned HTTP design](SOURCE_POLICY_GENUINE_HTTP_OWNED_STORAGE_DESIGN.md#bootstrap-owned-storage-and-effect-accounting) | Explicitly says complete-core bootstrap/login/session writes need not be zero;they require owned storage,separate bounded accounting and deterministic fixture preparation. Unknown/unrelated effects still block. |
| [Functional-only envelope decision](SOURCE_POLICY_PRIVATE_HTTP_ENVELOPE_ADMISSION_DECISION.md) | Genuine private HTTP flow within fixed limits;not pre-router containment,installed FPM eligibility or production readiness. |
| [Current SQL observer](../scripts/ci/source-policy-http-owned-observer.php) | After setup rejects all request writes except finite login session-token shapes,including ordinary core option writes. This is deliberately fail-closed,but stricter than the product-specific authorization requirement. |
| [Current controller](../scripts/ci/source-policy-http-owned-controller.mjs) | Seals all non-policy option/usermeta/table state before the first ordinary request and compares it throughout the HTTP phase. Changing only the SQL classifier would not solve that stronger invariant. |
| [Actual final recovery](SOURCE_POLICY_GENUINE_HTTP_INTEGRATION_IN_PROGRESS.md#bounded-recovery-exhausted-http-remains-unqualified--2026-11-18) | Fresh native291/9 and genuine23/12006/9 prerequisites,prepared setup and compact baseline passed;first HTTP bootstrap still refused at generic add_option1142. Remaining option/upstream caller Unknown;HTTP unqualified,exact cleanup verified. |

Conclusion:repeatedly enumerating first-run core writes is a poor default route
to the functional setting-save proof. The failed v1 results are valid refusals,
not evidence the product guard is wrong or permission to bypass it. The review
does not identify the remaining writer and does not claim a guaranteed fix.

## Selected recommendation: initialized-fixture HTTP, not request exceptions

Propose a distinct **owned-http-l714-initialized-v2** contract (a proposed name,
not an existing runtime constant). Prepare the disposable installation with
ONE genuine post-install normal bootstrap in a fresh owned CLI child,then run
the SAME strict measured HTTP phase. This is ordinary fixture initialization,
not an HTTP case,a login surrogate,a trusted user or a new generic lab.

This deliberately narrows the claim from cold-after-install whole-core
immutability to functional HTTP against an explicitly initialized fixture.
It does NOT narrow the measured13-case authorization/save/denial obligations,
relax request SQL admission,exclude maintenance rows from comparisons or advance
directly to production wiring. Admission of this revised fixture contract needs
explicit approval before implementation;v1 is unchanged in this design.
Any future v2 receipt must bind its explicit initialization role,versioned profile,
exact source/job and shared credit;v1 setup or HTTP DATA cannot substitute for it.
Counts/role labels alone never establish that the initializer actually ran.

Preferred finite sequence:

1. Stage the SAME pinned core/theme/runtime,private configuration,empty plugin/
   mu-plugin graph,owned database/prefix and before-bootstrap source identity.
   Perform the existing owned core installation/seed and full shutdown/release.
   Verify only the expected three fixture users/roles,policy/sentinel bytes,
   inert site record,theme/plugin state and exact schema. No application data.
2. Revoke installer DDL privileges before initialization. Start exactly ONE fresh
   CLI initializer on those same owned fixtures. Before normal wp-load require
   no prior ABSPATH/bootstrap and no WP_INSTALLING or SHORTINIT override. Run
   complete stock wp-load/wp-settings with the pinned theme/default callbacks;
   after completion require wp_installing() false. Do not call wp_install,
   activate a plugin,strip hooks,manually select a user or invoke the guarded
   entry/provider/native writer. No loaded PHP globals/auth/cache state survive
   into the later HTTP process.
3. Treat this as an explicit fixture-initialization role,not a generic setup
   flag reused by requests. Bound any normal core fixture effects to existing
   owned core tables and expected SQL forms;no DDL,foreign table/database,
   arbitrary SQL command or request-selected destination. Enforce zero protected
   policy reads/writes and zero owner allocation even during initialization;
   the existing broad installer setup bypass is NOT sufficient for this role.
   Keep protected fixture rows non-autoloaded and prove they do not enter cache.
   Parent fixture readback is not an authorization grant to child code.
4. Retain existing network/mail/hard-rewrite/file containment and exact source
   checks. Unknown outbound/mail/file/schema/source effects fail,not a learned
   exception. After real stock shutdown verify child exit,independent all-server
   sessions/trx zero and the unchanged protected policy/sentinel/users/roles/site/
   schema/config/source anchors. Initializer must not mint a session or leave a
   logged-in fixture principal. Do not convert unobserved effects into zeroes.
5. Only THEN freeze the full non-target invariant,including ALL core options,
   cron,widgets,transients and non-session usermeta. Do not omit a namespace or
   reset the baseline between measured requests. Start the one existing owned
   cli-server with the same immutable config/database/prefix/source image.
6. Run all25 exchanges/13 logical cases with stock wp-login GET/POST,genuine
   cookies and normal WordPress user determination/capability/nonce validation.
   View/save/no-op/stale and every denial must meet the original positive/result/
   record/native/policy observations. No bootstrap-created trusted ID,cookie
   mint,CLI HTTP credit,model acknowledgement,legacy-writer fallback or
   always-unavailable owner. Unknown measured writes still refuse.
7. Preserve full EOF,shutdown receipts,exact non-target comparisons,live owned
   server identity,independent all-server release and exact final cleanup.
   Failure preserves its original result. No uncertain replay or reinitialization
   to make a later failing case appear clean.

Why CLI initialization is proportionate:all retained final failures occur inside
core-bootstrap before wp-login or guarded-entry dispatch. A fresh normal full-core
bootstrap prepares real core state without a second HTTP server/warm-up route,
per-option fake values,proxy,principal-isolation redesign or diagnostic project.
CLI is ONLY setup:it cannot certify that HTTP-specific or authenticated branches
will remain quiet. That feasibility remains Unknown until a separately approved
native v2 proof;an unexpected measured effect still stops qualification.

## Budgets and anti-loop conditions are part of the proposal

Keep every value from the existing [profile limits](../scripts/ci/source-policy-http-profile.mjs):
600000ms original job,60000ms TOTAL setup,60000ms HTTP,30000ms cleanup reserve,
5000ms HTTP startup/transaction,1024 TOTAL setup SQL,256 bootstrap/256 auth SQL per
request,64 maximum HTTP exchanges,64MiB PHP/WP settings,existing source/body/
header/response/diagnostic/command buffers and DB resource bounds. No new
diagnostic fields,stack captures,raw option/SQL/cookie logs or attestation tooling.

Installer plus initializer share the SAME setup clock and1024 SQL allowance;
each child's zero-based counter is NOT a fresh allowance. Future implementation
must enforce remaining shared credit before each query and propagate only
code-owned bounded credit under the existing private job identity;checking the
sum after excess writes is not enough. Deadlines/reserves cannot restart at the
child boundary. At most one owned setup child at a time and one HTTP startup;
no additional HTTP warm-up exchanges or child retries are selected.

No repeat-until-quiet loop,second initializer,automatic additional seed list,
profile learning,budget increase or exception for the Unknown remaining option.
If one normal initialization does not fit the original bounds,or strict HTTP
still refuses,record that result and STOP for a product/proof decision. Do not
automatically extend the exhausted3/3 allowance. No attempt is granted here.

## Alternatives rejected and retained gates

- Reject blanket owned-option writes or transient-prefix exclusions during
  measured requests:they would weaken the current observation perimeter and
  could hide unrelated/owner effects. Also reject merely disabling the observer;
  the full-state invariant would still differ and protective coverage would fall.
- Reject indefinite hand-seeding/diagnostics to discover one more key. Known
  setup helpers remain valid historical code,but cannot establish bootstrap
  completeness. Do not remove them as unrelated cleanup in this design.
- Reject declaring CLI-only success sufficient or moving the HTTP gate after
  an unconditional public factory switch. Actual HTTP and later installed-graph
  eligibility remain requirements before the separately approved cohesive
  provider/view/revision/form/POST/result/audit/legacy-writer integration.

No FPM/third-party graph,public deployment,pre-router memory isolation,hostile
runner resistance,Database Review or whole-plugin release readiness is claimed.
Retain minimum-version/source/ABI assurance limits and existing M/W/L receipts
on their original cohorts. This documentation-only review neither refreshes nor
stales executable source identities. The current3/3 allowance stays exhausted,
cumulative41 and product milestones4/10 unchanged;design is not another product
milestone or a delivered lost-update fix.

## Focused review, validation and exact next gated slice

Canonical Feature Light SOURCE-SCOPED:the decision and owning Markdown resumes,
plus unchanged writer/form/handler,guarded entry/request composition,HTTP
observer/controller/router/profile/contract and retained proof records. Corrected
three specification traps during review:CLI initialization is not HTTP proof;
two setup children must not reset budgets;initializer must not inherit installer's
protected-policy bypass. No remaining scoped quality issue or unrecorded runtime
fix. No UI change/bloat framework;UI and structural workflows unnecessary.

Final Feature Security SOURCE-SCOPED retains capability/nonce/type/record/revision
ordering,no protected pre-authorization effects,no handles/grants,source/route/
profile restrictions,strict measured writes and full non-target/cleanup checks.
No executable defect fixed or acceptance change enacted. Regression handoff for
the future foundation:required initializer invocation/positive availability;
wrong role/head/source/mode/protected read/write/owner allocation refusals;
shared-credit/deadline/second-child/skip-initialization controls;unaltered measured
SQL/invariant negatives and all13 positives/denials. Removing any required guard
must kill an assertion rather than syntax or an always-unavailable fake result.
Existing tests do not cover the unimplemented initializer or genuine v2 effects.

Actual existing isolated validation in this review:

- `node --test tests/node/source-policy-http-*.test.mjs`:101/101,no failures/skips.
- `php vendor/bin/phpunit --filter 'SourcePolicy(GuardedEntryFoundation|RequestComposition|TrustedProvider|NativeAdapterContract|NativeAdapterConcurrency|StoredMapFoundation)Test' --do-not-cache-result`:183 tests/1039 assertions,plain OK,no failures/errors/skips.
- All381 relative file links across these six changed Markdown records resolve;
  `git diff --check` passes. Executable/test/workflow/dependency trees remain
  byte-identical to9ce9ce4. No test/control or diagnostic was added.

Publication exact-head CI belongs in the toolkit source-scoped record after
actual completion. It validates the same unchanged executable tree,not native
HTTP or initialization feasibility. No full local suite/build/native run claimed.

**Recommended next gated slice:**explicitly approve the initialized-fixture v2
contract above and the **Repository-Only Post-Install Bootstrap Fixture
Initialization Safety Foundation**,using isolated API/DATA/virtual-clock tests
only. Keep all native execution,actual authentication/SQL/fixtures,manual
dispatch,diagnostics and production wiring disabled. This is a real fixture/
acceptance-claim admission,not another approval of this completed review.
Native integration/one attempt and production/application switching require
separate later authority;no implicit allowance reset or public activation.
