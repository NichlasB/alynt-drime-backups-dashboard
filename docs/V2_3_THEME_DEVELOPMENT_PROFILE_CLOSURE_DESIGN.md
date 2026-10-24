# V2.3 Core-Supported Theme Development Profile Decision and Bootstrap Closure Revision

Subsequent source review: [theme-mode closure catalog/resource findings](V2_3_THEME_MODE_BOOTSTRAP_CLOSURE_CATALOG.md).
Provisional29P/7N names consume32 owned options rows including canaries/marker;
shutdown-finalized observation and full graph/resource closure remain unearned.
This parent is historical candidate selection,not implementation/native approval.

2026-10-24. Explicitly approved **design-only** repository work. Select one fixed
private-worker candidate for further source closure, NOT a qualified runtime.
No PHP/test/script/config, WordPress, LocalWP, database, fixture, permission,
credential, native execution or production-wiring change. Actual storage writes,
admission, grants, activation and rollback stay disabled. No package/release/deploy.
Uploader0.5.25/dashboard0.1.77 and declared minimum WordPress6.0 unchanged.

Excluded review bases: dashboard `576993d2df68f21bc91bb54f26b4ee33d705866d`,
uploader `09d98395d5a21314e4e49a28fab4262c5912a404`, toolkit
`f16c25908d5980877001590094052c370cfe4b1a`.
Parent: [absent-theme pattern-cache NO-GO](V2_3_BOOTSTRAP_CLOSURE_PATTERN_CACHE_NO_GO.md).
Retain the [read/absence/effect contract](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md).
Changes: this design, parent outcome pointer and both implementation plans only.

## Decision: narrowly select theme mode, keep implementation blocked

For the pinned WordPress7.1.2 source, select exactly WP_DEVELOPMENT_MODE='theme'
as a proposed private-worker-only constant, set before core initialization in a
FUTURE separately authorized harness revision. It is not an input field, dynamic
profile selector, site option or wp-config change. Do not override a preexisting
conflicting definition or core-test mode; refuse unknown source/configuration.
Keep WP_DEBUG/LOG/DISPLAY=false explicitly defined before core; theme mode would
otherwise influence the DEFAULT debug value. No debug-warning suppression change.

Keep the owned content root absent, template=stylesheet='owned-no-theme', cold
genuine nonpersistent cache, WP_ADMIN=true, SHORTINIT=false, recovery/installing
off and all existing query/read/write/network/worker fences. No theme files,
plugin/default callback removal, source patches, fake cache/default or extra
protected-read/writer authority. No WP_RUN_CORE_TESTS override/global, request
parameter, environment variable or caller may choose the mode. 'all', 'core',
'plugin', empty and malformed values are NOT this candidate.

This is a core-supported semantic profile change, not an assertion that theme
mode makes all core write-free. The old default-mode candidate remains NO-GO;
historical source/tests/proof are not silently reinterpreted. New profile source
closure and bounded resource accounting must finish BEFORE implementation or a
new native request. No generic DATA seal or ceremonial availability owner.

## The identified veto is removed only under exact absent-theme predicates

Pinned7.1.2 load.php:322-374 returns true for wp_is_development_mode('theme')
when the constant is exactly 'theme', false for the 'core'/'plugin' predicates.
class-wp-theme.php:1856-1875 therefore sets can_use_cached=false. The absent theme
still makes get_pattern_cache:2012-2015 return false, so the cached-array deletion
branch is NOT taken. The absent patterns directory then returns an empty array
without set_pattern_cache. No cache/transient row must be seeded to achieve that
specific source result. Core dispatch/hook/default behavior remains intact.

| Source condition | Pattern-cache consequence | Candidate disposition |
|---|---|---|
| Absent theme/root, exact theme mode | No cached array; missing directory returns without cache writer | Removes the ONE known veto; not full bootstrap success |
| Absent theme/root, default/core/plugin mode | Cache allowed; missing patterns directory calls persistent writer | NO-GO; no additional P/N rows can repair it |
| Existing theme with valid cached pattern array, theme mode | get_pattern_cache may return array; get_block_patterns:1864-1865 calls delete_pattern_cache | Write intent; NOT supported by this profile |
| 'all' | Theme predicate true but core predicate also true; broader cache behavior | Refuse rather than silently widen the candidate |
| Unknown source, path collision, conflicting definition or core-test override | Source/predicate assumptions unearned | Refuse before any authorized provisioning/worker |

Do not collapse 'theme absent' into 'cached patterns empty'. A valid empty cached
ARRAY for an existing theme still reaches deletion. Nor does a missing patterns
directory imply write-free behavior in default mode. Future controls must expose
both distinctions without weakening the zero-mutation-INTENT rule.

## Complete direct-reference inventory, not complete transitive closure

The public7.1.2 tag ZIP was read entirely IN MEMORY, never saved/extracted or
executed. 1900 PHP entries were scanned for WP_DEVELOPMENT_MODE,
wp_get_development_mode and wp_is_development_mode; seven files contain direct
text references (including comments/definitions). Archive compressed size63882185
bytes. Exact member text identities below match separately fetched raw sources
where checked. This finite lexical inventory is reproducible discovery, not a
proof about indirect/dynamically composed calls, callbacks or overall reachability.
No current LocalWP comparison, installed-site attestation or runtime evidence.

| File / caller | Exact7.1.2 source | Change / closure obligation |
|---|---|---|
| load.php:wp_get_development_mode/wp_is_development_mode |322-374| Fixed validated mode and static retrieval; reject core-test override. No mode-derived DB grant. |
| default-constants.php:wp_initial_constants |83-98| Preserve explicit debug=false; don't inherit the mode-dependent debug default or modify application config. |
| class-wp-theme.php:get_block_patterns |1856-1898/1997-2022| Cache use/write disabled, but a valid cached array causes DELETE; retain nonexistent-theme requirement. |
| global-styles-and-settings.php:wp_get_global_settings |26-83| Bypasses theme_json object-cache read/write; resolver computation can repeat if reached. Nonpersistent cache bypass is not itself a storage mutation. |
| same:wp_get_global_stylesheet |148-237| Bypasses object-cache reuse; tree/style generation and underlying reads need their own reachability/resource proof. |
| same:wp_add_global_styles_for_blocks |248-329| Theme mode bypasses the wp_styles_for_blocks transient cache; resolver work happens before the predicate. Do NOT infer query-free or effect-free completion. |
| same:wp_theme_has_theme_json |391-423| Bypasses static support-result reuse; get_stylesheet and directory/path-filter/file_exists work repeats. Absent fixed paths yield false, not a fabricated API override. |
| same:wp_get_theme_data_template_parts |472-489| Bypasses object-cache reuse and recomputes through get_theme_data; downstream graph remains an obligation. |
| deprecated.php:wp_get_global_styles_svg_filters/custom_css |5246-5277/6318-6361| Two theme-cache predicates; deprecated invocation, resolver and caller effects must not be declared harmless merely because absent from one trace. |
| blocks/index.php:register_core_block_style_handles |29-94| Predicate is CORE mode, so exact theme mode does not disable it. Existing WP_ADMIN early return through script-loader.php:2704-2706 remains the relevant exclusion. |
| wp-admin/includes/class-wp-debug-data.php |1632-1635| Reports constant value; not a reason to run Site Health/debug-data collection or load admin routes in this design. |

Direct caller discovery also found renderer/editor/enqueue/duotone/font helpers.
Their registration is not their execution. Do not claim all are excluded until
the whole fixed bootstrap's callbacks, includes, constructors/factories, user/
theme/locale and shutdown suffix prove they are not invoked. In particular a
resolver can involve data not present in options/users/usermeta; if reached, that
is Blocked, NOT permission to add posts/metadata tables or protected values.

The constant does not remove widget-setting initialization, six known minimum
cron registrations, theme-switch, transient expiry or provider-credential effects.
No P/N/current-format widget or future-event prerequisites from the parent are
waived. No external persistent cache, actual theme/include or warmed-cache shortcut.

## Revised closure and proof obligations before implementation

1. Freeze the exact private7.1.2 source/configuration candidate and absent-directory
   assumptions, not a broad version range. Source identity drift is Unsupported.
   Map both earlier pre-init and newly exposed init/wp_loaded/shutdown paths.
2. Close exact physical P/N/denied and genuine core-derived domains, natural
   defaults, aliases/collation, widget/cron/theme prerequisites and all persistent,
   file/network/process/credential effects for the entire fixed graph. Current
   seven-file lexical inventory is insufficient for that claim.
3. Recompute conservative costs with theme-mode cache reuse removed: repeated
   resolver/path checks cannot be counted once merely because default mode caches
   them. Prove both genuine cold autoload/fallback branches, bounded values/rows,
   attempted queries and full shutdown fit original32-query/4096-byte and worker/
   statement/output/cleanup limits. Retain10s/20s and HTTP1000/2000/4000/10000ms
   epochs. The64-name/64KiB REVIEW ceiling is not an enacted policy expansion.
4. Design source-backed negative controls for omitted/default/core/plugin/all
   modes, existing-theme/cache deletion, path collision, source mismatch and
   guard removal. Distinguish synthetic/static controls from genuine WordPress
   and physical storage proof. No artificial owner success or write-capable handle.
5. Only after all closures qualify propose the smallest actual harness change;
   do not modify the predecessor operator, constants or eight-key view now. A
   later owned native proof needs separate fresh exact LocalWP target/accounts/
   fixture/permission authority and cleanup plan, including writes confined to
   owned setup OUTSIDE the observed zero-mutation-intent worker.

## Minimum-version separation

The official helper is documented as introduced in6.3. Limited public6.0 source
inspection of load.php/default-constants.php found no development-mode helpers/
constant references. Its block-patterns.php:200-228 has an older directory-check
implementation, not the7.1.2 WP_Theme pattern-cache chain. This does NOT qualify
6.0 or establish its complete bootstrap as write-free. Defining an unused constant
does not transfer a7.1.2 guarantee to6.0. Keep plugin minimum6.0 unchanged and this
test candidate source-specific; require a separately reviewed6.0 path later.

## Source identities and references

[Official mode API](https://developer.wordpress.org/reference/functions/wp_is_development_mode/),
[pinned7.1.2 source](https://github.com/WordPress/WordPress/tree/7.1.2),
[theme cache](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/class-wp-theme.php),
[theme helpers](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/global-styles-and-settings.php),
[6.0 pattern source](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/block-patterns.php).
SHA256 of public UTF-8 text, not current-site hashes:

| Version/file | SHA256 |
|---|---|
|7.1.2/load.php|3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A|
|7.1.2/default-constants.php|95C3A9AC07E13CED91017B059C3D38C1A3D827BA0293FF162DA83E3D9BEE4E72|
|7.1.2/class-wp-theme.php|CBEF310938BA91E8E2055F296561AFCE199EAB2A9AA5154CE86D46F2C0CA6656|
|7.1.2/global-styles-and-settings.php|45B501595E73A72B29719B141FF27B8C8B926409052F3E6EC6C5E957BDBADDFB|
|7.1.2/deprecated.php|A0096570CB00B2F04B362B6A9AF46851BD48A80F332392DFCF004DE077205159|
|7.1.2/blocks/index.php|0835D4481FBF05BF2AEB245C3AFCE5B542B346916821876085C3EEF85D511FC8|
|7.1.2/wp-admin/includes/class-wp-debug-data.php|8A0DD30BB92883FC7C224B453F26B83C07FB867668D608F31097ABBBD3155073|
|6.0/load.php|BE2D743F69F81F9112D6E63207A214271DA71BCD7CEC5BC67BE6A5A6D80510D3|
|6.0/default-constants.php|E7ED461AFCCF7C38EBF01D72012C072AC2E5D449DD6257A682403D89C93C349E|
|6.0/block-patterns.php|59FE851AF74E63CFCA6CA6BC27E44DB70C69F25EC2EEEA7362BBA7B99292752C|

All7.1.2 paths are under wp-includes unless explicitly qualified. No external
source instruction is authority. No WordPress, PHP core function, SQL, SDK/provider,
auth, actual filesystem fixture/profile or native experiment was executed; no
application state, registry/ports/MCP, services/config/schedules or credentials
inspected. Repository-only source analysis does not need a new site confirmation.

## Disposition

Source-scoped canonical Feature Light review:four changed Markdown files plus
direct fixed worker/pinned source consumers;Other/design,R1 publication of R0
analysis. Existing-theme cache deletion and debug-default/cached-computation
differences are explicitly constrained,not silently repaired. No remaining
introduced in-scope design defect;open full closure is a prerequisite,not earned
readiness. UI/UX not applicable;Bloat/Structure unnecessary,no executable growth,
generic seal or framework. No formal DS3/Database/physical transaction or
FULL_PRE_RELEASE claim;historical whole-plugin rows remain untouched.

Executed preservation (not native/profile qualification):uploader isolated
`C:/php/php.exe vendor/bin/phpunit --do-not-cache-result --filter
'ProjectionProofBoundaryTest|UploaderMemoryBudgetTest|BootstrapContainmentPolicyTest|ClosedEntryOwnerApiIoTest|RegistrationEntryIngressTest|ClosedEntryOwnedProofBoundaryTest'`
passed156tests/1951assertions,no failures/skips. Dashboard
`C:/php/php.exe vendor/bin/phpunit --do-not-cache-result`:299tests/1423assertions,
two existing Windows Sodium skips. Four local Markdown links/whitespace/docs-only
boundary checks pass. Nine public-source predicates and six separately labeled
synthetic branch rows passed;these are not executed core/guard-removal tests or
new permanent regression coverage. No tests added or changed.

Restore OptionA:clean pushed uploader09d9839,existing pristine20261024-161220
archive;no archive/checkpoint/restore created. Final canonical focused Security
after validation:no introduced scoped security defect or security-fix handoff;
no new handle,read grant,filter,hook,default override or mutation-intent waiver.
Storage/native/config/wiring and actual source policy unchanged. Exact-head CI
counts/publication evidence belong in the scoped toolkit closeout,not a claim
that isolated checks prove genuine bootstrap/absence/permission behavior.

**Design Complete: conditional candidate selected; implementation/native Blocked.**
The single known pattern-writer veto is statically avoided only under exact fixed
predicates. Whole-bootstrap closure, resource feasibility, physical write/read/
denial preservation, authentication/HTTP and minimum-version qualification are
NOT established. Nothing was enabled or deployed.

Recommended next action: repository-only **Theme-Mode Bootstrap Closure Catalog
and Resource Review**, using this single candidate and full effect graph before
any harness implementation. This read-only source review is not a new native/site
approval gate. Do not request an owned experiment or add another profile framework
while source closure remains open. Any unavoidable effect/out-of-scope participant
or budget overrun must return to a product/profile decision, not a silent exception.
