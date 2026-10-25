# V2.3 Pinned Bootstrap Reachability, Lifetime and Effect/Cost Audit

Successor correction: [residual decision, Task2](V2_3_BOOTSTRAP_RESIDUAL_CLOSURE_DECISION.md)
establishes a constructor read of sidebars_widgets outside29P/7N. The historical
"no newly required option established" finding below describes this audit's
earlier limited scope, not the successor result. No catalog/worker widening.

2026-10-25. Approved repository/public-source audit; R0 analysis, R1 Markdown
publication. Dashboard owns this record; uploader owns the unchanged worker,
operator and socket-free catalog/transcript. No PHP source was evaluated to
inspect WordPress metadata. No WordPress, SQL, authentication, fixtures, accounts,
permissions, storage writes, native experiment, wiring, grants, activation or
rollback. No version/dependency/protocol/test change, package, release or deploy.

Excluded review bases: dashboard `577ba07ad5207ed06cb0c1fa83747c77cf7c8f23`,
uploader `3ae8257f89c7730439352e4c9f7e99153da3fde7`, toolkit
`3ace56cc4cd2b878d116f245e5c3b0f897a2cb35`. Publication scope: this audit,
the [parent closure design](V2_3_BOOTSTRAP_INCLUDE_LIFETIME_RESOURCE_CLOSURE_DESIGN.md),
both implementation plans and uploader's foundation successor note; toolkit
source-scoped evidence separately. Historical whole-plugin readiness is unchanged.

## Verdict: useful source closure, not native admission

The audit establishes concrete immediate call/effect and finite-family cost
records below. It does NOT establish a complete root-to-exit call graph, every
instance lifetime, a conservative whole-request cost total or stable installed
source ownership. Those are explicit unresolved findings, not zero contributions.
The 29P/7N candidate remains **NO-GO for native qualification**. An audit with
open findings is not a source-complete certificate or permission for a new attempt.

No newly unavoidable writer or required thirtieth present option was established
in these inspected paths. This is not a global absence-of-writers claim. The
tentative script-registry/date-option concern was disproved for the inspected
metadata edge, rather than used to justify widening the profile. Do not patch
core, remove hooks, warm caches, fabricate a successful bootstrap or add another
availability seal to hide the remaining work.

## Fixed source and candidate predicates

The official [WordPress 7.1.2 ZIP](https://wordpress.org/wordpress-7.1.2.zip) was
downloaded into bounded memory, digest-checked and inspected as text, not
extracted/executed. SHA256
`8FC96C59A78B7219E4A130222B7FADB51B03E503E8B0123BEAA7E28961C21CE2`;
37225839 bytes. Parent inventory1512 PHP members/21407880 PHP bytes remains
discovery, not an include allowlist or memory estimate. Analysis streams were
disposed. A transient download timeout was retried; no source/site fallback ran.
ZIP line numbers below are not browser-rendered raw-page line positions.

The proposed profile remains single-site, empty request/cookies/PHP_AUTH,
WP_ADMIN=true, SHORTINIT=false, debug/log/display=false, cold genuine nonpersistent
core cache, absent owned themes/content/plugins/MU/drop-ins/languages, and proposed
WP_DEVELOPMENT_MODE='theme'. These are conditional source-analysis facts, not
newly observed Plugin Tester facts. The actual eight-key worker is unchanged.
P29/N7, 32 owned rows including both canaries and owner marker, all existing
query/time/output/cleanup budgets, compatibility and disabled wiring are unchanged.

Read [P/N/effect domains](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md)
and [theme predicates](V2_3_THEME_DEVELOPMENT_PROFILE_CLOSURE_DESIGN.md) with this
ledger. Each row closes only its stated edge. Unknown filters, callers, inherited
behavior, mutable source or later dispatch do not inherit that disposition.

## Immediate roots and lifetime ledger

Source member identities are in the final table. The phase is core-load unless
specified. Counts are per cold request under the fixed predicates, not cumulative
whole-request totals. Immediate constructor return never closes transitive
allocations, inherited destructors, captured callbacks or PHP resources.

| ID / caller and source | Target, predicate and immediate maximum | Disposition / retained lifetime and open edge |
|---|---|---|
| R1 settings468; Embed31-48 | One Embed; registers content filters, shortcode and edit-form actions | Immediate registration closed; rendering/autoembed/HTTP is not called by the constructor. Hook/global references retain the object; a later dispatch requires its own edge. |
| R2 settings479; textdomain registry79-81 | One registry, literal array state and upgrader invalidation registration | Immediate setup closed; locale lookup and language-file discovery are separate probe/cache edges, not an empty constructor certificate. |
| R3 settings484-485 | AI cache and event adapters, one each; parent fixed discovery/provider analysis retained | No new provider/key access established here. Autoload/adapter/interface lifetime closure remains transitive, not covered by two literal new sites. |
| R4 settings652; Query4103-4107 | One Query with default empty query string | Query dispatch branch closed-excluded at this constructor. Other query methods are not approved; global alias lifetime is retained. |
| R5 settings671; Rewrite2022-2024 ->1919-1946 | One Rewrite; P permalink_structure read, array resets/string tests | Immediate read/setup closed to P; this is not flush_rules or rewrite generation. Later sitemap rewrite registration is separate. |
| R6 settings680/766; WP690-692 | One WP, then init calls wp_get_current_user once at this root | Empty construction is not empty bootstrap. Genuine anonymous-user/authentication chain is retained as a separate unresolved full-call obligation; no user proof ran. |
| R7 settings689; Widget_Factory46-48/73-79/106-121 | One factory, widgets_init priority100; dynamic new once per registered widget class | Registration closed; dispatched subclasses/overrides and callbacks require W1 below. Factory/callback arrays retain instances. |
| R8 settings698; Roles86-92/302-321/333-390; Role46-49 | One Roles; single-site exact role-key P; candidate contains one role -> one Role with name/capability fields | Immediate Role field assignment closed; off-site multisite get_blog_option excluded. wp_roles_init dispatch is a distinct obligation, not ignored because its constructor returned. |
| R9 settings728; Locale130-133/146-254/391-397 | One Locale, fixed translation/string tables and five global table aliases | Local construction is finite, but translation/filter calls are transitive. It is not a raw-string-only constructor or a peak-memory certificate. |
| R10 settings737-738; Locale_Switcher48-64/224-232 | One switcher, determine_locale/get_available_languages, two locale filters | Empty switch stack makes filter_locale return input. Language discovery goes through l10n1552-1580 to textdomain registry; absent directory/config must eventually be attested, not presumed installed. |
| R11 settings763; Site_Health79-85/37-57/3343-3347 | Singleton once; ini_get and cron next-event check | Correct future weekly cron excludes schedule writer on this edge only. Constructor registers admin/cron handlers, not the health tests themselves. |

### Transitive families: included code is not executed behavior

| ID / source and phase | Closed observation and finite subtotal | Remaining condition / effect limit |
|---|---|---|
| W1 widgets1836-1886; Widget253-277/599-639; init1 -> widgets_init100 | Installed-site gate plus link_manager_enabled='0' yields18 default widget registrations. Base get_settings reads exact widget P; `_multiwidget=1` excludes missing-option save, legacy delete and old-format conversion; unsetting marker leaves empty instance settings, hence one generic registration on each base-method path | The18 base-method option API calls are NOT18 SQL queries. Subclass constructors/_register_one overrides and their callbacks still require complete transitive closure. Display/update/form callback registration is not execution. |
| W2 widgets2154-2168; widgets_init1 | Absent valid block theme excludes classic-sidebars theme-mod lookup at this edge | Do not remove this hook or force wp_is_block_theme; physical theme absence/source must be proved separately. |
| B1 blocks/index16-18/152-177 |86 literal dynamic includes, two special includes,27 static folder entries. Set join is exactly115 distinct folders and115 metadata names | All86 registration bodies plus legacy-widget/widget-group and separately typed video body inspected; each block registration calls the metadata API, not its render callback. Additional settings/meta hooks are not block registrations or DML by name alone. This is a finite family, not the complete include graph. |
| B2 metadata registry84-181; blocks520-608 | Priority9 registers collection, first get_metadata requires the210661-byte PHP manifest once in that collection, then retained metadata; per block18 property-map visits |115*18=2070 property visits IF that family completes. Source bytes are not decoded PHP heap bytes. Absent/tampered manifest, fallback JSON, realpath and filter effects must not be silently omitted from failure cases. |
| B3 blocks610-662/667-769 | Render/variation PHP paths become captured callbacks; three script fields, one module field and three style fields traversed per registration | Creating a closure is not requiring its captured template or invoking variations. Concrete overrides such as post-terms/template-part variations remain lazy until accessed. Whole-caller exclusion remains required. |
| B4 Block_Supports169-190; included block-support files |17 support registrations,15 with register_attribute, two without; parent15 callback-body review retained | Conditional B=115 gives1955 support-slot visits and1725 attribute callback invocations, NOT15 total calls. These are not includes and do not violate the synthetic1900 include counter by arithmetic. Final global B/caller multiplicity and callback inner work still need closure. |
| B5 blocks/index29-34; blocks360-388 | Admin separate-assets predicate excludes bulk CSS glob/transient branch and individual core-style registration after the relevant checks | Not all probes disappear: metadata realpath and earlier style-handle/registry checks remain. No generic asset/HTTP approval. |
| S1 sitemaps22-39; Sitemaps51-104 | init constructs Sitemaps plus Registry/Renderer/Index (four objects in this family); renderer computes URLs, then init registers rewrites before N blog_public disables providers | Disabled sitemaps does not mean zero allocations or no rewrite work. No sitemap XML render/provider enumeration is established; URL helpers/filter work still belongs to full totals. |
| I1 icons84-142; Icons_Registry63-198 | init0 collection then init10 reads10521-byte PHP icon manifest,88 literal filePath rows; register stores path/label/name/collection | File-path mode does NOT read88 SVGs or invoke the content sanitizer at registration. content mode/parser/network/render remains a different edge. One manifest read and88 registration visits are real resource costs. |
| F1 fonts263-294; Font_Collection53-75/84-104 | Default font collection retains remote JSON URL in src; constructor validates supplied data | URL text is not HTTP. load_from_json/load_from_url occurs in get_data, not eagerly at registration. Do not invoke it or cache/warm the collection for this proof. |
| H1 settings460-461; Script_Modules433-467; Interactivity403-407 | Two factory-owned objects precede after_setup_theme; add_hooks probes block-theme status and registers print/data filters | Printing/import-map/footer/admin hooks are not dispatched merely by wp-settings completion. Registration cannot substitute for auditing all later dispatch or retained resources. |
| T1 load1302-1311; default-filters443; functions5578-5582; cache342-344 | Shutdown dispatch -> output-buffer flush -> genuine cache close returns true; `_wp_cron` disabled branch retained | Flush iterates actual buffer depth and may invoke handlers. Cache-close no-op does not establish engine/process/pipe closure, source stability or absence of later destructors/autoload. |

### Rejected tentative finding: script date/time options

script-loader85-165/348-425/668-676 can read start_of_week/time_format/date_format/
timezone_string/gmt_offset after init if WP_Scripts is initialized. However the
inspected115-entry core metadata has no editorScript/script/viewScript file path;
only accordion/tabs have viewScriptModule, both literal module IDs. The helpers
return IDs/handles before their file-backed script registration path. Calendar
metadata953-1006 has no viewScript. Its render callback is merely stored.
Consequently that metadata chain does NOT establish WP_Scripts construction or
those option reads. They are not added to P or N; no source defect is asserted.
Other callers of WP_Scripts remain a full-graph obligation, not globally excluded.

## Remaining lifetime and cumulative admission findings

**A1 — High readiness gap, whole root/dispatch closure unresolved.** Root literal
new sites are not an allocation census. This audit expands real factories and
registries, but does not close every include/autoloader, hook-added callback,
translation/theme path, subclass override, authenticated successor or error path.
The31 member identity rows are not a claim to have reviewed every statement in
those files, much less every member of the archive. No unknown edge is admitted.

**A2 — High readiness gap, lifetime/teardown closure unresolved.** The parent's
twelve destructor-definition families retain explicit instance/caller obligations:
FTP sessions, GD/Imagick, HTMLToken stored callable/captures, Nyholm stream/wrapper,
PHPMailer SMTP, Requests Curl, three SimplePie classes and Sodium state. This
audit does not establish that all are instantiated OR all unreachable. Immediate
memory-only setup does not prove inherited/captured/resource behavior at final
reference release. Output-buffer callbacks, driver/PHP-extension resources and
engine teardown are separate frontiers; late shutdown hook/did_action/exit0 is
insufficient. Do not manually unset/GC/close early to manufacture completion.

**A3 — High readiness gap, cumulative resource admission unresolved.** Known
subtotals now include2070 property visits,1955 support-slot visits/1725 attribute
calls (conditional115-family completion),210661 metadata source bytes,10521 icon
manifest bytes and88 icon registrations, plus18 widget option calls. They are
neither exclusive alternatives nor whole-request totals. Core initial taxonomies/
post types, recursive labels/translations, file probes, repeated filters, error
paths, object/cache retention and driver/bootstrap/teardown costs are not zero.
Conditional8/9 option SELECT arithmetic and1779 P-value bytes exclude these costs.

There is still no approved native peak-memory cap or defensible complete peak
estimate; PHPUnit256M is not one. Preserve32 attempted app queries/4096 SQL bytes,
child10s/SQL child15s, lock2s/statement5s, parent20s request epoch and all HTTP/output
limits. No reset, raised threshold or core optimization is authorized. No new
numerical budget violation is asserted from unrelated units or unmeasured time.

**A4 — High admission gap, installed source/config ownership unresolved.** Public
archive/member hashes bind this analysis corpus, not an installed process over
its entire lifetime. PHP/SAPI/extensions, autoload/prepend/append/preload, source
mutability, request/constants/paths and genuine cold cache need independent fixed
ownership evidence. Before/after hashing alone does not prevent transient source
substitution. This task inspected no site, metadata, ports, services or database.

## Canonical review disposition and publication validation

Feature scope: five Markdown artifacts, directly affected unchanged worker/model
contracts. Files reviewed:5 changed Markdown files; source-member identities31
plus the explicitly described registration bodies/transitive excerpts above.
Risk:Other/design, not new UI/AJAX/REST/DB/cron/file runtime behavior. Confirmed
introduced code defects:0; auto-fixed code defects:0; admission findings:A1-A4.
Documentation corrections: discard the tentative script-option reachability
claim; independent source recount pins18 property mappings/2070 visits,not20/2300.
No bloat/structure or UI/UX workflow needed: no executable growth or UI changes.

Canonical06 Performance Phase1 source-scoped inventory completed BEFORE writing
this report. Files analyzed:31 identity-listed members with stated additional
body excerpts; not whole-plugin coverage. Confirmed performance defects:0;
unmeasured admission/resource gaps are not invented optimization regressions.
N+1/query/index findings:0 confirmed; assets/memory/eager-registry costs are in
B1-B5/I1/A3; JS/background/custom-table implementation is outside this source
audit; HTTP loading versus URL registration is distinguished in F1. No index,
cache, lazy-load, batching or background-job change recommended to core. Phase1
complete; Phase2 optimization NOT run or authorized, and prompt06 NOT Complete
for the whole plugin. A1-A4 must be resolved before any native GO.

Feature Light and final focused Feature Security preserve fail-closed boundaries:
no source hash or modeled candidate is authority, no new handles/endpoints/input,
no protected data in findings. No confirmed security defect requires a new07A
regression handoff. Not a Database Review, physical transaction proof, full DS3
suite or pre-release certification. FULL remains reserved for a coherent release.

Executed preservation checks: uploader related PHPUnit160 tests/2415 assertions;
Node transcript42/42; no failures/skips. Dashboard299/1423 with two existing local
Sodium signer skips. Tests unchanged; no physical/native proof implied. Restore
OptionA verified clean uploader3ae8257 and existing pristine20261024-161220 ZIP;
no new snapshot/checkpoint/restore. Markdown targets, docs-only diff and exact-head
CI/log evidence complete publication; details are in the source-scoped toolkit
record. Full Windows uploader PHP was not rerun. Toolkit has zero Actions; its
remote/check/status verification must not be called green CI.

Independent raw-member length/SHA256 comparison passed31/31; property-map recount
returned18. Local Markdown target validation passed86 references across all five
documents; whitespace checks passed. Existing PHPCS passed dashboard138 and
uploader255 files; uploader's pre-existing PHPCompatibility functionWhitelist
deprecation remains a tooling notice. Dashboard structure guard passed. These
checks preserve repository contracts, not actual WordPress/resource behavior.

Implementation-plan progress: R2 advances from seed counts to a populated
conditional reachability/lifetime/effect-cost ledger and explicit NO-GO findings;
source-complete/resource/native admission is still unestablished. This is not
permission to continue maintenance until a native attempt looks plausible.

Recommended next gated slice: **design-only V2.3 Bootstrap Closure Completion and
Resource Admission Decision**. Use A1-A4 to decide the finite remaining source/
lifetime closure deliverables, independently owned runtime-source assumptions
and an explicit native resource policy before proposing any revised harness or
physical proof. Do not implement a generic certifier or promote modeled_only.
Storage writes, native execution and production wiring remain disabled.

## Raw inspected member identities

SHA256 is of uncompressed raw member bytes, not evaluated PHP or installed files.
The archive pin identifies referenced excerpts beyond this deliberately bounded
identity table; it does not authorize arbitrary archive members.

| Archive-relative member | Bytes | SHA256 |
|---|---:|---|
|wp-settings.php|33152|2E3185EE44BCDD9D9C4BEF8CE4891827711DEC2B5D5D4C08C951738CCEF51F8D|
|wp-includes/class-wp-embed.php|15908|A8359656527313E546B0201AFFFB29FEE092813EBBF557CD3D75B0E0F1A3C18D|
|wp-includes/class-wp-query.php|164148|6DB1F0AA772680920C0203A87B2624F4F14E1E8969624DB5C12010BC816E040C|
|wp-includes/class-wp-rewrite.php|63693|E197E1F2A78B4B6FFFFD1687BDEDF0C254D2A8CF013F6D8C8E55C0AC53FACD3E|
|wp-includes/class-wp.php|26520|1C161A1BE1CD87EF14EB91D978E0A4737DA15750F2B47C260D4B9358BC37DE6E|
|wp-includes/class-wp-widget-factory.php|4113|56C58C1EC5BCCB265DE6FC0E9AF1DC9AF5497FE987AA94624C06C385F3295F9F|
|wp-includes/class-wp-widget.php|18452|E2B3E341D2E042E61320A8BC84C278570537DC42EBCFAA5517DD0A72E3C915B7|
|wp-includes/widgets.php|70866|2426468C359C2AAAB8DC047C9BF0B896ADB58C3AE26B8C3EDF29C8941176C1D6|
|wp-includes/class-wp-roles.php|9326|DF0040896B30F89BDC254F62857AC7508109062217EE746750B69F3B024549E1|
|wp-includes/class-wp-role.php|2523|9AB9AD4A29FA3BC9E03C4F9A61FC91B407DBECB3D1F6521F706DEACA5CA55B3E|
|wp-includes/class-wp-locale.php|16848|379D5EBC2A3434EC22459F5D9E032A1C1F2986F5485CF4EB4A8BEBF2F160D856|
|wp-includes/class-wp-locale-switcher.php|6776|C80DCE58AD081AD14C6C67EFA63A78C964D03B79866906E88241BA428301F668|
|wp-includes/class-wp-textdomain-registry.php|10481|C2C44CE46450364208968B731102497FA3DD3323EDFD0357BFDEABC98DC6B40E|
|wp-admin/includes/class-wp-site-health.php|131168|D7517A0DDAA8B59B15265FEBAFC6545357AB53E3F982C50C8296B37484AF654D|
|wp-includes/blocks/require-dynamic-blocks.php|5256|68E3243C0C4254B8B7573B6880A6F3306A41E2D2DE98429D47AF5EB967951636|
|wp-includes/blocks/require-static-blocks.php|535|0CB910F1DABDC5176CA332B0AEEAAC0B811F84F6017E3670EB724CB0C50D1A0C|
|wp-includes/blocks/blocks-json.php|210661|EE2602CEEC667357934D759561ABC54710E7C7CD9FC1031960C332EDDF322C41|
|wp-includes/blocks.php|124205|99DE93FA9AFBF4576D5A34D1E46365980D510DE59F565BECE9F4DB178625A460|
|wp-includes/class-wp-block-metadata-registry.php|11846|2B8319A1ECC6131AD7889FBFA5895DC533F08B46DFB4987D2930F219D207A9B0|
|wp-includes/class-wp-block-supports.php|7578|F126A6CD4EA7758C2A5A9DF918804751513CA66C211E898703FA6802B825FA88|
|wp-includes/icons.php|6334|B4B8125FD3FBC49F3736902C03DB5372C59E1CC89D0F28EC14BE541D2A653CBD|
|wp-includes/class-wp-icons-registry.php|9903|57507192149EC98E3825C55CD9A04E5664B58ED2E4290804E21AFDD5D277C833|
|wp-includes/assets/icon-library-manifest.php|10521|4226C76915F22DE22D432B8BEA17C02244D0F8991FD59DEF628D640C57B4FE2B|
|wp-includes/sitemaps/class-wp-sitemaps.php|7682|DD6ADACC4AC3290F865E037A486910C9E07E2B5A45D530861AAB53DD57AD4763|
|wp-includes/script-loader.php|163550|87F6567F6A96CB61B2D53EB6CAF533BB3458A0257CACCB96A8B459E60976C393|
|wp-includes/functions.wp-scripts.php|20492|7F1C8945F97711927196D6E01216443B43B70BD1530C5013D53F20238757CAD4|
|wp-includes/class-wp-script-modules.php|40743|834F2A092506EBF246132C4C5A5694BBA469410C0845DE3372C58BD9B09D80D1|
|wp-includes/interactivity-api/class-wp-interactivity-api.php|59251|881E9698FA7E0783C5CEE2C0D0BF1886CDF9CF5AF3259560A3EF2B90B40EFD0B|
|wp-includes/functions.php|295481|CAC8A17E293DF99F0683A88DEBD3BA5E5CCE274183A4E9FD3C9F18375747F41C|
|wp-includes/cache.php|13486|A219C2B57CC9990B560C6B497C877DB443B7E29239645A6A79B2D76839C46E19|
|wp-includes/load.php|56475|3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A|
