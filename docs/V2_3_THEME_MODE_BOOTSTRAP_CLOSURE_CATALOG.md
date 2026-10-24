# V2.3 Theme-Mode Bootstrap Closure Catalog and Resource Review

Successor: [fixed projection/full-shutdown evidence design](V2_3_THEME_MODE_PROJECTION_SHUTDOWN_EVIDENCE_DESIGN.md).
The successor resolves specific callback/provider branches and specifies a
post-core candidate plus supervisor exit/EOF verdict;this catalog remains a
historical provisional review,not native or complete source qualification.

2026-10-24. Repository-only review, not a qualified bootstrap or permission to
implement/run one. Dashboard owns this record; uploader owns the dormant proof
scripts. No executable, fixture, actual option, account, permission, native,
authentication, schedule, production-wiring or compatibility change. Versions
remain dashboard0.1.77/uploader0.5.25; declared minimum WordPress6.0 unchanged.

Excluded Git review bases: dashboard `2cdc674a6b3ba0ee629bed18dc80271eb296eb0b`,
uploader `821211038e1b1104b8751b8d0bb39fad4ec63e5b`, toolkit
`b5c2281cbd3ecabbf2c23a257b31f780a9cc79bc`. Review changes: this catalog,
the [profile decision](V2_3_THEME_DEVELOPMENT_PROFILE_CLOSURE_DESIGN.md) pointer,
both implementation plans, and source-scoped toolkit evidence only. Preserve the
[P/N/D and zero-mutation-intent contract](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md).

## Outcome: useful finite catalog, full qualification still HOLD

The exact private7.1.2 absent-theme/theme-mode candidate has no NEW demonstrated
unavoidable writer in the paths traced below. That is not an exhaustive absence
proof and does not qualify implementation. The old default-mode NO-GO stands.
Two concrete readiness findings emerged: the candidate fixture consumes the
existing cleanup row ceiling, and the predecessor worker finalizes observation
before natural shutdown. Neither may be silently fixed by larger budgets or an
early-success receipt. Dynamic metadata/filter traversal and full resource bounds
remain open; do not relabel unreviewed calls as unreachable.

This review inventories source branches, proposed physical domains and resource
dependencies. It does NOT install a new allowlist or claim that all bootstrap
dependencies have been found. Native execution/storage/wiring remain disabled.

## Frozen candidate and evidence method

Retain the parent's proposed exact WP_DEVELOPMENT_MODE='theme', explicit debug/
log/display=false, WP_ADMIN=true, SHORTINIT=false, no installing/recovery/cache
drop-in, no plugins/MU/theme/language content and no caller-selectable profile.
No constants were actually changed. Cold genuine core object cache only; no
pre_option/pre_transient/default/cache substitutions or callback removal.

Public [7.1.2 tag source](https://github.com/WordPress/WordPress/tree/7.1.2) was
downloaded into memory, PHP members read as text, never extracted or executed.
1900 PHP members were available for discovery. Inspecting that archive is not
inspecting Plugin Tester or attesting its installed files. References below use
raw source line numbers. Hashes are UTF-8 public text, not application checksums.
The fixed repository worker and owned-proof supervisor were READ, not executed.

## Phase/effect crosswalk

P = proposed exact synthetic present value; N = exact genuine absent projection
lookup/default; D = denied/unknown/protected. C = genuine core-derived result,
not a replacement API value. A hook registration alone does not execute its body.

| Phase / pinned source | Actual path or prerequisite | Effect and disposition |
|---|---|---|
| wp-settings.php:136-180 | Genuine wpdb/cache/default filters, shutdown registration, installed check | siteurl(P); bulk options must be physically projected. Connection/session setup is distinct from counted application SELECT attempts. |
| wp-settings.php:182-485 | Core libraries, script-module/interactivity hooks, Embed/textdomain/AI factories | Includes/top-level constructors belong to closure. Embed constructor registers filters/shortcodes, not a render/HTTP request. AI adapter registration is not provider invocation. |
| wp-settings.php:496-630 | MU/plugins empty, taxonomy/post types, cookies/SSL, plugin loading | active_plugins(P) does NOT avoid earlier hack_file(N). Cookie/SSL URL dependencies retain siteurl; attachment label reads wp_attachment_pages_enabled(N), including pre-init registration. |
| taxonomy.php:25-107 | Initial category/tag types, repeated on init | category_base/tag_base(N) use core defaults. Built-in definitions here do not supply default_term; register_taxonomy:539-559 can write for OTHER definitions, not evidence that this branch writes. |
| wp-settings.php:652-738 | WP_Query, Rewrite, WP, Widget_Factory, Roles, theme/locale setup | Rewrite::init:1919-1945 reads permalink_structure(P). Roles uses fixed prefixed user_roles(P). Missing theme constructor returns an error object and caches it in genuine MEMORY, not a persistent theme cache. |
| theme.php:388-436/697-722 | Absent theme directory fails registration; zero/one root | Returns core '/themes' before stylesheet_root/template_root/theme_roots reads. Do not grant those names for a multi-root variant. Template paths still exist as STRINGS; not proof that theme loading loop is empty. |
| after_setup_theme, theme-templates.php:138-142 | wp_enable_block_templates checks missing theme/theme.json | Genuine false results; widgets-block-editor support is memory-only. No custom-header/background support from a nonexistent theme. Theme-mode path checks can repeat. |
| wp-settings.php:763, Site_Health constructor | maybe_create_scheduled_event executes during construction | cron(P) must already contain Site Health's exact future event; DISABLE_WP_CRON does not prevent registration writes. Admin enqueue callbacks are registered, not dispatched by wp-settings. |
| wp-settings.php:766, WP::init / user.php:3805-3853 | Current-user determination happens before init | Empty fixed cookie/request profile is NOT a genuine authenticated request proof. Do not transfer the anonymous branch to future HTTP/cookie/application-password paths or authorize secret reads. |
| init priority0, post.php:20-101/taxonomy.php | Built-in types registered again | Repeated option API calls need real notoptions/alloptions reuse accounting, not one query per source match. No main WP query/render is dispatched by this worker. |
| init priority1, widgets.php:1836-1886 | Default widgets plus widgets_init | link_manager_enabled(P='0') excludes Links; genuine absence is NOT equivalent because its default is true. Eighteen exact current-format settings below avoid first-run conversion/save/delete. |
| Widget_Factory::_register_widgets, WP_Widget:253-277/611-639 | Enumerates widget classes and settings | Generic display/form/update callbacks are registered, not called. Media/Text/Custom_HTML overrides add admin/preview hooks; no widget rendering is authorized. Numeric instances are absent from proposed settings. |
| init priority5, functions.php:4888-4893 | smilies_init | use_smilies(N) returns before building the translation regex. |
| init priority9/10, blocks/index.php:29-177 | Style handles, metadata collection, static/dynamic registrations | Admin predicate avoids style-directory glob/transient writer. blocks.php:375 calls wp_style_is BEFORE its own early return, so do not claim the styles singleton is never constructed. |
| blocks.php:169-230/267-325/520-821 | Metadata and script/module/style helpers, registry construction | 115 core/name literal entries in the210661-byte PHP metadata file; zero 'file:' occurrences. Handle fast paths avoid script/module file registration for those values. Variations/render PHP are deferred closures, NOT executed solely by registration. Metadata/settings filters and individually supplied arguments still need complete transitive enumeration. |
| init priority22, Block_Supports:77-80/169-190 | Per-block register_attribute callbacks | Fifteen literal registration entries in block-support files; this is a nested traversal, NOT one callback. Typography/position/duotone register attributes in memory; similarly named render helpers calling theme-JSON are not automatically executed. Remaining callbacks/metadata filters are an explicit frontier, not closed by this sample. |
| init patterns, class-wp-theme.php:1856-1898 | Missing theme/root/pattern directory plus exact theme mode | Avoids the prior persistent pattern writer. Existing theme + cached ARRAY can DELETE, including empty array; stay outside this profile. Core pattern registration and later rendering remain different operations. |
| init sitemaps, class-wp-sitemaps.php:65-104 | Rewrite registration precedes blog_public(N) check | False blog_public skips provider registration; no sitemap rendering/query follows merely from server initialization. |
| init15/20, connectors.php:216-276/780-891 | Connector metadata/settings registry then provider-key pass | Empty SDK provider registry must remain empty. hasProvider check precedes key-source/get_option access. Metadata registration alone is NOT permission to read environment/constant/API-key options. Full autoload/factory closure remains a frontier. |
| init theme switch, theme.php:3483-3520 | theme_switched(N) false | Avoids after_switch_theme, rewrite flush and update_option. A truthy synthetic value is not an acceptable convenience default. |
| init scheduling, update.php:1089-1101/functions.php:8551-8575 | Three update checks and two privacy jobs, plus constructor Site Health job | Exact six future cron tuples below. Missing tuples call scheduling writers even with spawning disabled. No admin_init/load-* route is dispatched here, so update-transient preseeding is not justified solely by hook registration. |
| wp_loaded, block-template.php:14-18/theme.php:2963-2993 | Template-loader filters, just-in-time header/background | No _wp-find-template request field and no custom theme support; do not dispatch frontend/admin rendering to obtain success. |
| shutdown, default-filters.php:443/cron.php:1020-1056 | Output-buffer flush plus deferred _wp_cron | _wp_cron returns before ready-job reads when DISABLE_WP_CRON=true. update.php:1137 adds destructive temp-backup shutdown work INSIDE a cron callback, not unconditionally at include time. No such callback is dispatched by this profile. |

Calendar is a useful reachability counterexample: blocks/calendar.php:87-96
registers metadata; :108-139 can query posts/update an option when its published-
posts helper actually runs. Render/delete/status-transition callers are not an
init registration call. Never grant posts or accept a writer just from grep.
Likewise script-loader.php:348-384 has nonce/protected preference operations;
do not infer they execute from metadata containing an existing script HANDLE,
or infer they are globally safe. Any newly reachable script initialization path
must be reviewed before extending this candidate.

## Finite proposed names: 29 P + 7 N, NOT an enacted/exhaustive policy

P retains the predecessor eight exact keys/values: siteurl/home fixed local
synthetic origin; blogname owned marker; active_plugins serialized empty array;
template/stylesheet='owned-no-theme'; WPLANG empty; exact owned-prefix user_roles
synthetic role map. Never broaden the prefixed role key to arbitrary site keys.

Add eighteen P settings, EACH the serialized current-format array containing
only `_multiwidget => 1`, no numeric instance or arbitrary class/object value:

```text
widget_pages             widget_calendar          widget_archives
widget_media_audio       widget_media_image       widget_media_gallery
widget_media_video       widget_meta              widget_search
widget_text              widget_categories        widget_recent-posts
widget_recent-comments   widget_rss               widget_tag_cloud
widget_nav_menu          widget_custom_html       widget_block
```

The hyphenated recent-posts/recent-comments names derive from their constructors,
NOT the legacy widget_recent_entries/widget_recent_comments aliases. WP_Widget
get_settings can DELETE legacy options or SAVE on missing/current-format failure.
Do not allow aliases or fake an option default to avoid those calls.

The remaining three P names: link_manager_enabled='0', permalink_structure='',
and cron. A genuine missing permalink returns boolean false, not an exact present
empty string; do not rely on coercion/diagnostic suppression for Rewrite::init.

N is exactly: hack_file, wp_attachment_pages_enabled, category_base, tag_base,
use_smilies, blog_public, theme_switched. These are proposed absent names to be
permitted for genuine empty SELECT results, NOT physical rows or fabricated
false values. Attachment label false takes the media-file label branch; adding
an unnecessary P row would break the row arithmetic below. P/N disjointness and
DB-collation alias denial still need physical proof, not just byte comparison.

cron must be current array format with version=2, exactly these six future jobs,
each correct recurrence/interval, empty args and genuine args-key derivation:

| Hook | Recurrence / interval seconds |
|---|---|
|wp_site_health_scheduled_check|weekly /604800|
|wp_privacy_delete_old_export_files|hourly /3600|
|wp_privacy_personal_data_cleanup_requests|daily /86400|
|wp_version_check|twicedaily /43200|
|wp_update_plugins|twicedaily /43200|
|wp_update_themes|twicedaily /43200|

Epoch selection, clock advance/refusal and malformed/stale cron controls must
be designed before any setup writes. A future timestamp alone is insufficient
without correct args shape. No actual schedule is read or changed in this review.

Everything else stays D until individually justified: actual settings/safety,
secrets/salts/cookies/session tokens, connector keys, legacy/theme-mod aliases,
theme-root/transient namespaces, posts/terms/comments/metadata, arbitrary SQL,
filesystem/routine/external handles. Some are excluded by branches above, others
remain unknown; D means deny, not a false claim of unreachability.

## Resource review: separate names, rows, queries, bytes and finalization

Source-scoped canonical DS3/06 Phase1 inventory, not a whole-plugin Performance
completion or native benchmark. Two repository executables are directly affected
consumers (unchanged); public core sources are a separate discovery corpus.
No runtime optimization/Phase2 implemented or required within this docs-only scope.

1. **High / memory-data bound, not a measured production regression:**
   projection-owned-proof.mjs:53-56/99. P29 plus two hidden settings/safety
   canaries plus proof_owner =32 owned options rows, exactly the existing cleanup
   preview ceiling. P30 gives33 and fails cleanup admission. N7 consumes NO rows.
   The proposed64-name/64KiB review ceiling is NOT a32-row override. No removal of
   canaries/marker, cleanup shortcut or budget increase is approved. Any newly
   necessary P row requires a new profile/resource decision before provisioning.
2. **High / incomplete query observation:** projection-core-bootstrap.php:39-51
   captures num_queries/restores diagnostic handler/closes wpdb before natural
   PHP shutdown. A current pre-shutdown receipt cannot cover late core callbacks,
   failures or reconnect attempts. No actual missed write was observed here; the
   predecessor stops earlier. Future design must keep fences alive through the
   genuine core shutdown callback and finalize/close AFTER it; premature, missing,
   duplicate, failed or timed-out finalization must never produce success.
3. **Medium / uncached traversal bound unearned:** theme-mode repeated theme.json
   path tests plus metadata/filter/support traversal. Static metadata210661 bytes
   is NOT PHP peak memory or the64KiB fixture-value budget. 115 literal names and
   15 support-registration rows are discovery counts, not a safe upper bound on
   all traversed calls, support rows or allocations. Do not remove core hooks,
   add persistent caches, render blocks or warm the object cache to improve cost.

For the LISTED name set only, proposed all-P-autoload hydration plus genuine
notoptions memoization gives8 application option SELECTs (1 bulk +7 unique absent
lookups); fallback gives9 (2 bulk +7). This is CONDITIONAL arithmetic, not an
executed count, conservative full-bootstrap upper bound or evidence of meeting
32 attempts. Multiple core calls to one N may share a genuine negative cache;
cache invalidation/unlisted/dynamic queries may invalidate that calculation.
Connection setup, capability negatives and supervisor SQL must be accounted in
their own epochs, never hidden inside this option-only subtotal.

Keep actual guards:32 attempted queries,4096 SQL bytes, existing statement/worker/
root/output/cleanup limits and HTTP1000/2000/4000/10000ms epochs. Supervisor source
currently gives child10s, SQL child15s, lock_wait_timeout2s/max_statement_time5s,
stdout262144/stderr32768 characters. Preserve the parent20s root epoch; do not
mislabel a per-child deadline as a proven total or confuse JS character counters
with byte limits. Serialized fixture bytes, total processes/includes/reads, PHP
peak memory, elapsed cold/fallback time and full shutdown totals are UNMEASURED.
No index/migration/background-job recommendation: no runtime schema/query changed.
No UI asset/browser DOM work: metadata registration is not browser asset loading.

## Review disposition and next boundary

Feature Light: four changed plugin Markdown files plus direct consumers above;
Other/design, R0 source analysis/R1 publication. Two high readiness findings and
one medium unearned bound recorded, not runtime defects silently patched. No
introduced in-scope documentation defect remains. Bloat/Structure unnecessary:
no executable growth/refactor/framework. UI/UX not applicable. DS3/06 stops at
source-scoped Phase1 with unresolved implementation/native obligations; do not
mark historical whole-plugin rows refreshed. FULL_PRE_RELEASE remains reserved
for a coherent release candidate; no Database/physical/authentication proof claim.

Before a harness revision, the exact remaining closure frontier is metadata/
settings filters and all register_attribute callbacks, AI autoload/factory/provider
emptiness, complete pre-init/include/locale/user and shutdown suffix accounting,
and physical projection/absence/denial plus cold/fallback resource evidence. The
table is not a certificate replacing those obligations. In particular no claim
that unknown provider/protected reads are acceptable if SQL later denies them.

Recommended next gated slice: **design-only V2.3 Fixed Theme-Mode Projection and
Full-Shutdown Evidence Design**. Resolve that finite frontier, exact physical
P/N/row/value and finalizer/negative-control contracts before any harness change.
Do not authorize another incremental one-key native attempt or raise budgets.
If closure needs a thirtieth P row or an unavoidable out-of-domain participant,
return to an explicit product/profile decision, not an automatic policy exception.

## Reproducible public identities

All below are7.1.2 UTF-8 text SHA256; wp-includes prefix except wp-settings.php.

| Source | SHA256 |
|---|---|
|wp-settings.php|2E3185EE44BCDD9D9C4BEF8CE4891827711DEC2B5D5D4C08C951738CCEF51F8D|
|default-filters.php|55BBF47FB835A0F64F28423D475B51F7FA72479F319FCA417B4F23990D44F897|
|option.php|FE3F0B7E115B2A50C31162ED6428ABE4088C080B0F71B9678D84806168FE90D0|
|widgets.php|2426468C359C2AAAB8DC047C9BF0B896ADB58C3AE26B8C3EDF29C8941176C1D6|
|class-wp-widget.php|E2B3E341D2E042E61320A8BC84C278570537DC42EBCFAA5517DD0A72E3C915B7|
|class-wp-widget-factory.php|56C58C1EC5BCCB265DE6FC0E9AF1DC9AF5497FE987AA94624C06C385F3295F9F|
|blocks/blocks-json.php|EE2602CEEC667357934D759561ABC54710E7C7CD9FC1031960C332EDDF322C41|
|class-wp-block-supports.php|F126A6CD4EA7758C2A5A9DF918804751513CA66C211E898703FA6802B825FA88|

No current site, ports, registry, WordPress runtime, SQL, accounts/fixtures,
native experiment, config/service or application permission was inspected/run.
No source-corpus file was written; the in-memory inspection process is closed
after review. No tests changed. Exact-head CI belongs to the scoped toolkit
closeout. Neither isolated tests nor CI establish the open native/physical claims.

Executed preservation checks:

- Uploader: `C:/php/php.exe vendor/bin/phpunit --do-not-cache-result --filter
  'ProjectionProofBoundaryTest|UploaderMemoryBudgetTest|BootstrapContainmentPolicyTest|ClosedEntryOwnerApiIoTest|RegistrationEntryIngressTest|ClosedEntryOwnedProofBoundaryTest'`:
  156 tests/1951 assertions, no failures/skips.
- Dashboard: `C:/php/php.exe vendor/bin/phpunit --do-not-cache-result --verbose`:
  299 tests/1423 assertions, no failures, two existing Sodium-unavailable skips
  (RemoteActionSignerTest and RemoteActionSignerFailureTest). Not all local tests
  passed without skips; CI's actual supported-runtime counts must be checked.
- Eight recorded public hashes matched the in-memory sources; transient catalog
  arithmetic checked29P/7N/36 unique names and32-versus33 row boundary. These are
  text/arithmetic checks, NOT executed core branches or mutation-killed guards.
- 41 local Markdown links checked across dashboard plan/design/catalog; whitespace
  and docs-only boundaries checked. No new permanent test coverage claimed.

Final canonical Feature Security review after validation: no introduced scoped
security defect; no confirmed defect needing a07A regression handoff. Input,
authorization/CSRF, SQL, output/file and secret boundaries unchanged; no new
grant, API/default override, handle or write-intent exception. Resource/frontier
findings above remain explicitly unqualified, not silently accepted. Restore
OptionA verified clean pushed uploader8212110 and existing pristine20261024-161220
archive; no new snapshot, checkpoint or restore operation. No native/site approval
was sought or exercised for this repository-only work.
