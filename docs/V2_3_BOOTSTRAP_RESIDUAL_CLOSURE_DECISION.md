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
