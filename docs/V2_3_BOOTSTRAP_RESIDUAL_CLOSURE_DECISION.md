# V2.3 Bootstrap Closure Completion and Resource Admission Decision

2026-10-25. Autonomous safe-local batch; dashboard owns this source/design record.
Risk R0 analysis/R1 documentation. Base dashboard
`96cdd00774c4e5c513ac1e80788b1dc26ef48cd2`; companion uploader
`af2cf8e93357aad1df17743cec61150eb94b6d5d` is read-only context, not an edit target.
This continues [A1-A4 of the pinned audit](V2_3_PINNED_BOOTSTRAP_REACHABILITY_LIFETIME_COST_AUDIT.md),
not eight new runtime features. No application storage, actual authentication,
native execution, services, privileges, wiring, activation or rollback changes.
No package, release or deployment. Versions/compatibility/guards remain unchanged.

## Task 1 — Admission decision and finite residual work

Decision: retain NO-GO, complete the finite source residuals first, and separate
source complexity from enforceable runtime budgets. Do not choose an arbitrary
memory number from PHPUnit or infer peak memory from source bytes. Existing
32-query/4096-SQL-byte and time/output/cleanup ceilings remain fixed. Unassigned
native memory policy means no admission; it is not an infinite allowance.

The following seven deliverables address actual open ledger edges, not cleanup:

| Task | Residual | Required source/design result |
|---|---|---|
|2|Widget subclasses and inherited registration|Separate base reads, constructor/registration overrides and deferred admin/render/update callbacks; identify writer predicates|
|3|Anonymous-user bootstrap|Trace empty-cookie/request identity construction to roles/meta/capability callbacks; distinguish it from genuine authenticated request proof|
|4|Translation registry|Account for locale discovery, cache/probes and lazy textdomain loading under exact absent-language predicates|
|5|Core registry dispatch|Record actual dispatched phases, nested registry work and deferred render/REST/admin callbacks without treating registrations as invocations|
|6|Full shutdown/lifetimes|Resolve the natural shutdown suffix and concrete retained callbacks/resources; preserve unresolved destructor reachability rather than guessing|
|7|Query/cache accounting|Separate driver/setup statements, attempted application queries, cache hits, misses and refused effects; no subtotal-as-total claim|
|8|Admission reconciliation|Reconcile newly closed edges and residual blockers; define one exact next meaningful boundary, not an automatic native attempt|

For each deliverable, source inspection is text-only against the official7.1.2
ZIP pinned by SHA256
`8FC96C59A78B7219E4A130222B7FADB51B03E503E8B0123BEAA7E28961C21CE2`.
No PHP metadata evaluation, core callback invocation, generated parser/certifier
or test that fabricates source-complete/native success is introduced.

Resource admission must eventually combine: finite source operation/retention
accounting; stable owned source/configuration; an explicitly approved native
memory policy enforced before bootstrap; and independently approved physical
measurements/cleanup under unchanged original deadlines. A slow/refused run may
demonstrate bounded refusal, not successful qualification. Measuring one trace
cannot close unobserved branches or substitute for source provenance.

Source ownership cannot be established by before/after checksums alone. A future
design must select an owned stable tree/configuration mechanism and its exact
mutation/cleanup authority before copying files, changing ACLs or running PHP.
This document authorizes none of those operations, nor any automatic transition
from repository evidence to Plugin Tester. No secret-bearing configuration is read.

Validation/review for Task1: canonical source-scoped Feature Light/focused
Security on this record and plan pointer; no new code/security defect, UI/bloat
trigger, Database/physical proof or whole-plugin readiness claim. Targeted local
Markdown/whitespace and existing dashboard PHPUnit are publication checks only.
Exact-head CI outcomes belong in the source-scoped toolkit batch evidence.

## Task 2 — Widget overrides: confirmed missing sidebar dependency

Fresh pinned source: widgets/class-wp-widget-recent-comments.php24-37,
6918 raw bytes, SHA256
`80A93831407C18C3DE00BBFABC4745499CD1ACE7B7F19D55C07C239F5EEC511F`.
Caller: wp_widgets_init constructs Recent Comments before widgets_init100 calls
the factory's base registration. Constructor34 invokes is_active_widget BEFORE
the right operand is_customize_preview, regardless of an empty widget instance.
widgets.php916-938 immediately calls wp_get_sidebars_widgets;1021-1054 selects
get_option('sidebars_widgets', array()) on the WP_ADMIN=true branch at1039.

This option is in neither P29 nor N7. A missing row still requires an absence
read; a default empty array is not permission to request that name. `_multiwidget`
does not protect this constructor path. It is therefore a confirmed source-level
counterexample to complete 29P/7N closure, not the tentative script-path concern
that the parent audit correctly rejected. No WordPress or SQL execution ran.
Do not claim this is necessarily the first refusal: another earlier unclosed edge
could fail first. No unavoidable writer is established by this particular read.

Other inspected constructors and the media/text/custom-HTML `_register_one`
overrides register admin/footer/preview callbacks rather than enqueue/render them
immediately. Media subclasses merge translated labels and compute admin URLs;
Recent Posts/Comments retain legacy option names, but base migration/delete still
requires the missing/old-format predicate. WP_Widget::is_preview339-342 consults
the actual customizer global; it is not forced false by this audit. The constructor
URL/translation helper effects remain transitive obligations, not literal-only work.

Decision: retain native NO-GO and unchanged catalog/worker. A future exact absent-
sidebar proposal would need a new versioned N domain and source-backed positive/
negative model coverage; adding a present thirtieth row would exceed the current
32-row fixture budget with both canaries/owner retained. Neither is applied here.
Remaining source audits may proceed independently; they do not authorize setup,
profile widening, hook removal, replacing the option API or suppressing reads.

Validation: inspected eager call ordering and both admin/front-end option branches
as text against the archive pin; this is source evidence, not genuine widget or
database proof. Scoped Light/focused Security: no new executable/security defect;
one confirmed admission dependency recorded without weakening its guard.

## Task 3 — Anonymous identity: early guards versus authenticated proof

Pinned user.php3805-3854 dispatches determine_current_user with false before
wp_set_current_user(0). Default-filters520-522 registers the three genuine
validators: auth cookie, logged-in cookie and application password. Empty cookies
make pluggable.php1013-1049 return false at1035 before cookie-field parsing;
wp_validate_auth_cookie782-795 dispatches auth_cookie_malformed and returns before
user lookup/session-token verification. That action remains part of the callback
graph, not an omitted zero-cost step. Logged-in validation user.php589-599 also
returns early for the admin/empty-cookie predicate.

Application-password validation user.php521-543 checks availability BEFORE its
missing PHP_AUTH_USER/PW guard. Availability/environment/filter work therefore
cannot be erased by saying credentials are absent. Authentication at536 is
excluded only under the exact missing-server-fields predicate; no validator is
replaced, disabled or invoked by this analysis.

wp_set_current_user27-49 constructs WP_User(0,'') and then setup_userdata and
set_current_user. WP_User131-170 takes get_data_by('login','');203-226 rejects the
empty value BEFORE cache/SQL lookup and does not call init/for_site on that false
result. This closes that direct empty-user lookup edge, not every subsequent
capability or user-metadata caller. kses_init2606-2612 removes filters, tests
current_user_can('unfiltered_html') and may add filters; it is not a no-op or a
role assignment. The footnotes/capability action suffix remains separately scoped.

Result: several direct credential/user-lookup branches are source-excluded for
the empty profile, but all validator filters/action/capability helper effects and
installed empty inputs still need closure/attestation. Neither anonymous WP_User0
nor isolated tests establish genuine authenticated request success, nonce-domain
separation or absence of every protected read before authorization. No fixture
users, cookies, passwords, actual options or native transaction were touched.

Validation: inspected actual early-return ordering and the get_data_by empty guard
as text; no fake current user, mocked authentication result or new success path.
Scoped Light/focused Security retains A1/A4 and the independent Task2 blocker.

## Task 4 — Translation discovery and missing-file load boundary

Pinned l10n.php30-80 reads WPLANG through the single-site option branch, then
chooses en_US for an empty locale. determine_locale124-177 dispatches pre/final
filters and chooses get_user_locale in admin;94-111 may access the current user's
locale before falling back. It is not a constant en_US function or free of the
anonymous/capability/configuration obligations in Task3.

Textdomain registry180-220 normalizes the path, calls its pre-lookup filter,
checks translation_files object cache, then on a cold miss performs TWO globs
(*.mo and *.l10n.php) and caches the resulting array. This is not a transient or
option-table writer under genuine nonpersistent cache. A cache hit still incurs
the filter/cache lookup; a repeated path need not repeat the two globs. Different
normalized paths have different cache keys. Absent files mean zero matched entries,
not zero probes, cache allocation or filter work. No short-circuit filter is added.

load_default_textdomain952-975 unloads the domain and attempts both locale and
admin-locale loads under admin=true. load_textdomain790-845 initializes/sets the
translation controller and tries PHP/MO alternatives through filtered paths.
Controller load_file101-125 calls realpath and refuses false BEFORE constructing
WP_Translation_File. Thus stable absent paths exclude translation-file creation
on that edge, but not controller allocation/filter/probe work. Missing-file refusal
cannot be claimed if source/config or filters redirect a path during execution.

The JIT default-domain guard l10n1423-1431 returns before plugin/theme path
discovery for 'default'; it does not suppress explicit load_default_textdomain.
Other domains/custom registered paths and language-directory aliases remain
separate cases. No translation PHP file was evaluated or installed file inspected.

Result: the missing-file creation boundary and cold per-path two-glob cost are
source-resolved, conditionally; total locale/filter/path multiplicity and physical
absence/source ownership remain open. A1/A3/A4 narrow but are not closed. Scoped
Light/focused Security finds no introduced code defect; native/catalog stays NO-GO.

## Task 5 — Phase dispatch, repeated registries and lazy REST

Pinned settings565-566 calls create_initial_taxonomies/post_types before
plugins_loaded630; then sanitize_comment_cookies643, setup_theme705,
after_setup_theme757, WP::init766, init779 and wp_loaded801. The no-content
predicate excludes plugin/theme loop bodies, not the unconditional phase actions.
Default-filters591/683 also registers those two create_initial functions at init0.
Therefore each has TWO direct invocations on the normal no-locale-switch path,
not one because its source file was included once. A change_locale action could
add more; it requires a separate reachable dispatch, not an automatic third call.

post.php20 begins reset_default_labels and registration without an early
already-initialized return. taxonomy.php25-61 resets labels; before init it uses
false rewrites, inside init it computes rewrites and repeatedly requests category_base/
tag_base. N misses may become cache hits, but the option APIs/filters still run.
For absent category_base/tag_base each expression calls get_option twice (the
truthy ternary arm is excluded); this is four API calls in that rewrite setup,
not four new SQL queries. Allocations/replacements and label/filter traversals
must be counted separately from final registry size or distinct hook identities.

Important naming distinction: rest_api_init() (rest-api208-230), hooked to init,
registers rewrites/query var. It does NOT fire the 'rest_api_init' action. The
latter is dispatched in rest_get_server629-657 AFTER its filtered server-class
factory. Hence registering default REST routes/settings callbacks does not
establish that they or controllers execute during this root. Conversely, an
unreviewed rest_get_server caller cannot be excluded by this name distinction.

plugin.php488-525 increments action counts BEFORE dispatch, invokes 'all' handlers
if present, then the hook object. Hook328-365/375-382 traverses callback priorities
and invokes exact accepted arguments. add_filter91-117 indexes by callback identity
within priority and can resort active iterations. Re-registering an identical key
may replace it; a new object callback is not necessarily that same key. No generic
one-visit or fixed-static-list bound follows from a visited-file set.

Result: repeated registry invocation and lazy REST dispatch are source-resolved
edges; a full per-phase callback/SCC/inner-work bound remains open. Source review
does not remove callbacks or change core registration order to shrink the proof.
Scoped Light/focused Security: no introduced executable issue or fabricated whole-
graph completion. Task2's missing sidebar dependency remains an independent NO-GO.

## Task 6 — Shutdown suffix and a concrete captured destructor target

Pinned settings166 registers shutdown_action_hook. load1302-1311 dispatches
shutdown before wp_cache_close; genuine cache342-344 returns true. The registered
buffer flusher functions5578-5582 captures ob_get_level then calls ob_end_flush
once per captured level. Buffer count/handlers and their retained references must
be part of source/config admission; cache close does not flush/clear all objects.

cron1020-1031 may register _wp_cron on shutdown rather than run jobs immediately;
1048-1056 returns for DISABLE_WP_CRON before ready-job lookup. This closes THAT
disabled path, not other shutdown callbacks, a custom cache close or engine work.
wpdb4049-4062 closes an actual mysqli handle and clears readiness on success.
It is not equivalent to core cache close, nor may the old worker's early close
be reused as proof that later callbacks remain inside the active query fence.

The HTML-token lifetime obligation is now more concrete: processor438-440 stores
a closure calling parent::release_bookmark;1072-1076 passes it to token creation.
Token99-115 retains and invokes that callable in its destructor. The inherited
tag-processor1393-1411 release body is a bookmark-table mutation, not a generic
external callback. This resolves this callback origin/target, NOT whether a parser
instance is reachable from bootstrap or its token/capture/cycle peak. Other token
construction sites may supply null/different origins and need their own record.
Nyholm Stream75-97 closes a retained resource via fclose then detach; an unknown
wrapper is not assumed to be a memory stream. No destructors were executed here.

The [PHP destructor rules](https://www.php.net/manual/en/language.oop5.decon.php)
and [shutdown rules](https://www.php.net/manual/en/function.register-shutdown-function.php)
require retaining a separate engine/process suffix: callbacks may append shutdown
work; exit can prevent later callbacks; max_execution_time is not a supervisor
deadline. Last-reference destruction is not tied to one chosen final marker.
The future parent must still earn normal core/cache return, sticky post-candidate
fences, process closure and both EOFs under the original epoch, plus owned cleanup.

Result: one captured destructor target and disabled-cron ordering are resolved;
full instance/resource reachability and installed buffer/source predicates remain
open. No forced GC/unset, callback removal, source hash certificate, early handle
close or exit0-only success. Scoped Light/focused Security retains native NO-GO.

## Task 7 — Driver control, attempted queries and genuine cache accounting

Pinned wpdb2214-2234 checks readiness before the query filter, then filters SQL
before execution. A guard that counts filter visits measures those attempted
application calls, including refused ones; it is not an all-driver-statement
counter. _do_query2347-2356 increments num_queries after its optional mysqli_query
call, even without a success predicate. That property is not a successful-result,
transaction-completion or physical zero-DML certificate, regardless of a receipt
label such as completed_data_queries. Existing receipt/protocol is not changed.

Direct control paths bypass that filter/counter: set_charset916-937 may call
mysqli_set_charset plus SET NAMES; set_sql_mode949-985 directly selects session
sql_mode and conditionally sets it (empty/error result returns before SET);
db_connect2033-2044 calls both and selects the database. Connection checking also
has a direct DO1 probe. These are session/driver effects, not P/N option reads or
application-row DML. They require their own finite control-statement/connection/
retry accounting and original deadline, not an ad-hoc SELECT/SET wildcard or
claim that read-only account privileges prohibit every session statement.

option.php163-217 prioritizes alloptions, then notoptions, then options cache,
then one missing-name SELECT and notoptions insertion. Repeated absent-name calls
therefore still execute API/filter/cache work without necessarily repeating SQL.
wp_load_alloptions600-645 can do autoload SELECT then fallback all-option SELECT
before hydration; a bulk result, single-key result and genuine absence remain
different evidence. No cached default may substitute for an unapproved name.

Consequences for Task2: 'sidebars_widgets' remains an attempted out-of-domain
read even when its eventual result would be absent/default empty. A future exact
N8 model would add no present source row, so29P+two canaries+owner remains32, but
its cold first miss and all repeated API work must be budgeted. Existing conditional
8/9 option-only SQL arithmetic is no longer sufficient for that revised proposal;
no complete replacement total or budget increase is asserted here.

Result: query/driver/cache observability limits are now concrete, not a completed
whole-request cost. No SQL, connection, privilege or source-operator change ran.
Scoped Light/focused Security: no new executable issue or broadened SQL authority.

Successor (2026-10-25): [revised read/absence and driver admission design](V2_3_REVISED_PROJECTION_READ_ABSENCE_DRIVER_COST_DESIGN.md)
separates successful distinct-name absence from default/cache/counter evidence and
driver replay from query-filter visits. Design-only; actual worker remains unchanged.

## Task 8 — Versioned sidebar-absence proposal and admission reconciliation

Preferred next repository implementation: a NEW test-only versioned29P/8N
catalog domain adding exactly sidebars_widgets as genuine absence, with the old
29P/7N model/identity explicitly distinguished. Do not silently change v1's label
digest, expected count or acceptance assertion. The current compiled catalog,
native eight-key worker, SQL observer and all production contracts stay untouched
in this design batch. No source-complete/native certificate is issued.

Why absence rather than a new present row: wp_get_sidebars_widgets1039 explicitly
accepts its empty-array default and the inspected is_active_widget loop then has
no entries. A positive sidebars option is not required by THIS edge. N adds no
present source row, preserving29P+two canaries+owner=32. Actual absent backing rows,
binary/collation behavior and the permitted absence query still require later
owned physical proof; filtering a present value out of a view does not establish
backing-store absence. No fake empty return, pre-option cache priming, widget/hook
removal, canary trimming or broader read wildcard is an acceptable substitute.

Required model tests for that next slice: a positive exact29P/8N literal fixture;
deleting/aliasing/duplicating/changing the new name fails; stale29P/7N evidence
cannot masquerade as the new profile; a present sidebar value cannot satisfy
absence; names outside the exact domain refuse. Keep negative native-readiness
tests and the honest modeled_only result. Tests must exercise actual helpers and
fail when the new name/version/absence guard is bypassed, not merely expect every
input unavailable. This proposal does not prescribe a generic parser/framework.

The revised cost worksheet must add the newly permitted cold miss to option-only
subtotals while retaining all repeated API/filter work and direct driver setup.
It must still refuse if total bounds or native memory policy remain unknown;
neither a synthetic trace nor the source below supplies those physical limits.

| Parent finding | New result | Still not established |
|---|---|---|
|A1 source/calls|Concrete eager sidebar counterexample; anonymous early guards; lazy REST distinction; repeated registry calls|Complete transitive callback/include/autoload/error closure and revised-domain compatibility|
|A2 lifetime|One HTML-token callback origin/target; actual buffer/cache/driver suffix distinguished|All instantiated/inherited/captured/native resources, stable source/buffers and full engine/process closure|
|A3 resources|Two globs per cold path; repeated API versus SQL; driver statements versus query-filter/num_queries|Whole-request query/inner-work/heap/time totals and approved native memory policy|
|A4 installed ownership|Public source pin and raw member records only|Installed tree/config/SAPI/extensions/empty request/cold cache/absence and anti-substitution ownership|

Implementation-plan progress: eight distinct source/design deliverables address
the existing R2 residuals; no claim of eight runtime features or complete bootstrap
qualification. The independent source lanes are narrowed, not exhausted; native
admission remains NO-GO. Recommended next action: implement the test-only versioned
29P/8N sidebar-absence catalog and adversarial regressions in its owning uploader
repository, without changing the actual worker or production wiring. Native,
storage/fixture/privilege and deployment transitions require separate authority.

Batch review: canonical source-scoped Feature Light and final focused Feature
Security cover this cohesive record, plan and parent correction against the
96cdd00 base. No executable/security fix or new regression test claimed; source
counterexample and honest readiness limits are documentation outcomes. No bloat/
UI/UX trigger, Database Review, physical proof or whole-plugin pre-release refresh.
All eight slices execute existing dashboard PHPUnit plus whitespace checks;
Markdown targets checked for each later slice and final batch. PHPCS138/structure
passed once for the final batch. Local299/1423 includes two existing Sodium skips;
exact-head PHP7.4/8.3 CI results/log counts belong in toolkit source-scoped evidence.
No uploader edit/native run or package/release/deploy. Compatibility unchanged.

### Additional raw source identities inspected in this batch

Archive SHA256 is pinned above; these hashes are raw uncompressed member bytes,
not installed-source attestations or a claim that every statement was reviewed.

| Member | Bytes | SHA256 |
|---|---:|---|
|wp-includes/widgets/class-wp-widget-recent-comments.php|6918|80A93831407C18C3DE00BBFABC4745499CD1ACE7B7F19D55C07C239F5EEC511F|
|wp-includes/user.php|180301|C837321FBF722F89A080324F3C742B0C44E5853C9BD6D305CB894B5F664FC6CC|
|wp-includes/pluggable.php|127919|3A2482A65B50D62EBAE75A728BE98B78985D3A3F5A3E007CB2D0C1338BF05777|
|wp-includes/class-wp-user.php|23189|A6330B51EC04DBFE0B968A1130148AEDB63E28B64FC533F24C1DA563E11D5FE2|
|wp-includes/l10n.php|71415|43C9AF6B687EE3EB92608FEA4697EBE7B62B3D45490A7657EFCE71033AC82ED7|
|wp-includes/l10n/class-wp-translation-controller.php|12971|E8A2D90C35C23780EF1A7DE8E2995D625B7B6D20D9543438056BE52FD4ABFBEC|
|wp-includes/post.php|305215|522AE4AD3DD9E367840CD235CDE9FE6FF01AE1E4DD96906C7B5DC0EDBE81A56B|
|wp-includes/taxonomy.php|178307|281F7AA456CB28BD09B1476B61EE7D9614DB78DE013F31667141A42C07E31C7B|
|wp-includes/rest-api.php|102951|9C4AF1341E37DE253323CF7C28DA1424F41DECC2154904E2B71A4E71C9CDD70C|
|wp-includes/class-wp-hook.php|17584|B839C0E5672246BCA8DB1AB781EC8835F7732F253C375A237CBF6EC536E8D12E|
|wp-includes/plugin.php|37123|D70B9E18D34AB3FE46E15548A5E8E089EA5920832F06A4D89A9D64B2F7938B0D|
|wp-includes/class-wpdb.php|121437|E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD|
|wp-includes/html-api/class-wp-html-processor.php|213919|A5C63913428050F39F3BED0461F90D13E214495F7E68F17E349C5C52D4084C6E|
|wp-includes/html-api/class-wp-html-tag-processor.php|178828|E2D3C1511D02A60637AD94B9A2D6A76DA234D55F4F86C9D1A6BF016A26C69BD6|
