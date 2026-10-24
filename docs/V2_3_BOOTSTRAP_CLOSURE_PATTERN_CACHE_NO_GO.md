# V2.3 Bootstrap Closure Review: Absent-Theme Pattern Cache NO-GO

2026-10-24. Repository-only source/effect and fixture-prerequisite review following
the [complete read/absence contract design](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md).
Outcome: **fixed absent-content/default-development-mode candidate NO-GO**.
The rejection witness is complete; the exhaustive transitive catalog is NOT.
Do not finish an unusable manifest or provision another experiment to rediscover it.

Excluded review bases: dashboard `bcf65ce09c0cf559a3c5f0d8719fb38774ef60da`,
uploader `33a983e8ca53cb0805a9da593f487f1f57f1816d`, toolkit
`2c030dca385aa4f9ea9c653aad6a3b8d659f8517`.
Changes are this record, its parent outcome pointer and both implementation plans.
Uploader0.5.25/dashboard0.1.77 unchanged. No executable, test, policy, dependency,
permission, fixture, configuration, site or production-wiring change.

## Fixed assumptions checked against repository source

Uploader `scripts/tests/projection-core-bootstrap.php` requires an absent owned
content path; it fixes content/plugin/mu-plugin/language directories beneath that
path, WP_ADMIN=true, SHORTINIT=false, WP_CACHE=false, DISABLE_WP_CRON=true and
fatal-error recovery disabled. It includes genuine wp-settings.php, not wp-config.
It does not define WP_DEVELOPMENT_MODE, set installation mode, alter theme hooks
or fabricate core defaults. `scripts/tests/projection-owned-proof.mjs:38,52`
fixes template/stylesheet to `owned-no-theme`. The proposed successor retains
those literals, cold core cache and no actual theme/content include.

This review considers a hypothetical later bootstrap after EARLIER read/widget/
cron prerequisites were satisfied. It does not claim the actual eight-key worker
reached this later failure: the historical worker still stops earlier. The query
observer, 32-query/4096-byte limits, eight-key policy and all production state are
unchanged. No worker was run here.

## Decisive source chain

All line numbers below refer to the exact public WordPress7.1.2 tag, one-based.
The linked raw bytes were fetched, hashed and read IN MEMORY, not saved as a
runtime or executed. No fresh LocalWP source comparison or installed-site
attestation is claimed.

| Edge | Source witness | Consequence under fixed assumptions |
|---|---|---|
| Default development mode | default-constants.php:87-89; load.php:322-374 | Unset WP_DEVELOPMENT_MODE becomes empty; theme-development mode is false. WP_DEBUG=false is not the theme-cache predicate. |
| Template globals | wp-settings.php:708-709; template.php:694-699 | Core assigns directory strings from get_stylesheet_directory/get_template_directory even though the owned content root is absent. |
| Theme list | load.php:1096-1124 | Outside installation/recovery, the template path is appended without checking directory existence. A nonexistent theme does NOT imply an empty returned list. |
| Init dispatch | wp-settings.php:779; block-patterns.php:414-478 | _register_theme_block_patterns is an init callback. Its empty-list guard does not exclude this profile; it constructs the theme and calls get_block_patterns. |
| Theme object | theme.php:117-133; class-wp-theme.php:299-330 | The absent fixture theme produces an error-bearing/nonexistent WP_Theme, not an exception or early return from the caller. |
| Cache-read refusal | class-wp-theme.php:2012-2022 | get_pattern_cache returns false immediately when the theme does not exist. A preseeded site transient is not consulted. |
| Missing directory | class-wp-theme.php:1856-1875 | can_use_cached is true; the missing patterns directory calls set_pattern_cache with an empty array before returning. |
| Persistent effect | class-wp-theme.php:2033-2064; option.php:2641-2687 | set_pattern_cache calls set_site_transient with a positive TTL. The no-external-cache/non-installing branch uses add/update_site_option for timeout/data. |

Relevant public sources:
[bootstrap](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-settings.php),
[template paths](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/template.php),
[theme list](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/load.php),
[pattern callback](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/block-patterns.php),
[theme cache](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/class-wp-theme.php),
[transient persistence](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/option.php).

This violates the parent's **zero mutation INTENTS** rule, regardless of whether
the database denies DML, a query guard throws earlier, or an identical stored value
causes a downstream update to short-circuit. The contract does not permit calling
a persistent writer merely because no committed row ultimately changes.

P/N value selection alone cannot solve this chain while the content path remains
absent and theme development stays off: the cache reader refuses all nonexistent
themes before checking their transient. Changing a TTL/value, adding another key,
warming notoptions, or ignoring failed writes does not establish write-free core.
No assertion of impossibility for every WordPress configuration follows.

## Catalog disposition: reject before pretending closure

| Frontier reviewed | Source-only result / remaining obligation |
|---|---|
| Initial/cold options, plugin discovery | Earlier siteurl/active_plugins/attachment/hack_file/rewrite findings retained. No actual policy enlargement. Exact P/N/default/budget closure not completed. |
| Widgets | widgets.php:1836-1886 instantiates concrete core widgets; factory constructor:46-48 adds widgets_init priority100, _register_widgets:106-120 calls each _register; base widget:253-254/611-635 reaches settings and initialization. Present current-format settings remain prerequisites, not absent-family success. |
| Theme discovery and pattern registration | Rejection chain above; no empty-theme-list shortcut, no seeded-cache escape under absent theme. This is a sufficient veto before a complete positive catalog. |
| Core block CSS cache | blocks/index.php:29-33/58-94 and script-loader.php:2704-2706: WP_ADMIN makes the asset predicate false before the transient path. Do not misclassify a write merely because grep finds one in the file. |
| Cron registration versus dispatch | Existing six-event minimum remains incomplete positive closure. cron.php:1020-1031 adds shutdown _wp_cron; :1048-1053 exits for DISABLE_WP_CRON before ready-job lookup. This excludes spawning, NOT init registration writes. |
| Connector initialization | ai-client.php:21-37 defaults AI support true; connectors.php:216-276/854-891 includes provider-dependent credential lookup paths. Provider availability and transitive SDK setup remain UNRESOLVED, not an asserted credential read. No actual credentials or environment were inspected; do not add credential keys to P/N. |
| Remaining init/module/default callbacks and shutdown | Blocks, metadata, sitemaps, icons/fonts, locale/current-user paths, added hooks and cache/cleanup suffix are NOT certified closed. One decisive veto obviates claiming or completing an executable candidate manifest in this review. |

The 64-name/64KiB REVIEW ceiling is not enacted. No conservative worst-case
query/byte/time total for a successful full bootstrap is earned; no estimate is
represented as measured execution. A future changed profile needs its OWN complete
catalog, including branches newly exposed by that change. No query, deadline,
credential or protected-read exception is recommended.

## Independent checks and proof limits

Nine in-memory source assertions passed: empty development-mode default,
unvalidated theme-list append, init callback registration, factory call to theme
patterns, nonexistent-theme cache-read refusal, missing-pattern-directory writer,
site-transient writer call, admin block-CSS early return, widget-factory priority100.
These check source predicates, not PHP execution, reachability under every earlier
dependency, adversarial runtime controls, physical DML or genuine authentication.
No new test suite or synthetic owner success was added.

No LocalWP/registry/port/MCP, WordPress bootstrap/auth/HTTP, SQL/database connection,
fixture/account/permission, listener/process, service/config/schedule or cleanup
operation took place. No current application checksums/native counts refreshed.
No SDK/provider discovery was executed. Public-source 404 discovery attempts were
excluded, not reused as another file's evidence. Historical proofs remain historical.
Actual storage writes, native execution, admission, wiring, grants, activation and
rollback stay disabled. No package/release/tag/deploy.

## Exact source identities

SHA256 over UTF-8 public tag text, preserving fetched line endings/trailing bytes;
these are public-source identities, NOT current LocalWP file attestation.

| File | SHA256 |
|---|---|
| wp-settings.php |2E3185EE44BCDD9D9C4BEF8CE4891827711DEC2B5D5D4C08C951738CCEF51F8D|
| wp-includes/default-constants.php |95C3A9AC07E13CED91017B059C3D38C1A3D827BA0293FF162DA83E3D9BEE4E72|
| wp-includes/load.php |3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A|
| wp-includes/template.php |63DB56469E69056315EB75D4D96DAAF2F5D40551595E5666A7CDB5BB71D36693|
| wp-includes/default-filters.php |55BBF47FB835A0F64F28423D475B51F7FA72479F319FCA417B4F23990D44F897|
| wp-includes/block-patterns.php |50D76E2BB2F0A917A0FC15E95C10B44AD435A9E83399BFD7ABE937CFBCF9D2C9|
| wp-includes/class-wp-theme.php |CBEF310938BA91E8E2055F296561AFCE199EAB2A9AA5154CE86D46F2C0CA6656|
| wp-includes/option.php |FE3F0B7E115B2A50C31162ED6428ABE4088C080B0F71B9678D84806168FE90D0|
| wp-includes/script-loader.php |87F6567F6A96CB61B2D53EB6CAF533BB3458A0257CACCB96A8B459E60976C393|
| wp-includes/widgets.php |2426468C359C2AAAB8DC047C9BF0B896ADB58C3AE26B8C3EDF29C8941176C1D6|
| wp-includes/class-wp-widget-factory.php |56C58C1EC5BCCB265DE6FC0E9AF1DC9AF5497FE987AA94624C06C385F3295F9F|
| wp-includes/ai-client.php |20DEA40CA6D32079B28E17C671ABF7943E472FF5410B7F04BEB23030348D350F|
| wp-includes/connectors.php |600040F85AC26D02FB709A31F24C583B9B9286A18B9E3DE005188F1F738EAB9F|
| wp-includes/theme.php |B32DD01A7DF72AD34FD6770ACE7DB730759FC0E5BD06B9ED82A3C7C92DA428E9|
| wp-includes/class-wp-widget.php |E2B3E341D2E042E61320A8BC84C278570537DC42EBCFAA5517DD0A72E3C915B7|
| wp-includes/blocks/index.php |0835D4481FBF05BF2AEB245C3AFCE5B542B346916821876085C3EEF85D511FC8|
| wp-includes/cron.php |DC27910A1AD004EB2AAABC19352246CC0B9E601BF2DA870E141646FD9DD95591|

## Source-scoped reviews and preservation validation

Canonical FEATURE_LIGHT_REVIEW_PROMPT.md scope:four changed Markdown files plus
the directly affected fixed projection worker/fixture and pinned public source
chain. Risk R1 publication of R0 analysis;Other/source review,not runtime DB/cron
modification. One feasibility/Edge Case finding Flagged for profile decision:
nonexistent theme does not imply empty valid-theme-list or skipped cache writer.
No automatic runtime fix. Earlier optional branches are not claimed fully closed.
UI/UX not applicable;Bloat/Structure unnecessary for this cohesive source record
and short plan pointers,with no executable framework/refactor. No formal DS3,
Database Review,physical transaction/FULL_PRE_RELEASE or historical readiness refresh.

Executed existing isolated preservation checks (not bootstrap qualification):

- Uploader: `C:/php/php.exe vendor/bin/phpunit --do-not-cache-result --filter
  'ProjectionProofBoundaryTest|UploaderMemoryBudgetTest|BootstrapContainmentPolicyTest|ClosedEntryOwnerApiIoTest|RegistrationEntryIngressTest|ClosedEntryOwnedProofBoundaryTest'`:
  156 tests/1951 assertions,zero failures/skips.
- Dashboard: `C:/php/php.exe vendor/bin/phpunit --do-not-cache-result`:
  299 tests/1423 assertions,two existing Windows Sodium skips.
- Local Markdown links,whitespace and docs-only diff checks;no new or changed
  tests. Nine public-source assertions are distinct from those PHP suites.
- Restore-point OptionA check:clean pushed uploader33a983e,existing pristine
  20261024-161220 archive;no new archive/checkpoint or source/site restore.

Final canonical FEATURE_SECURITY_REVIEW_PROMPT.md after validation:source-scoped
docs/direct callers;no introduced security defect or regression-fix handoff.
Protected read domains,positive projection,independent DB denial,query/write
fences,cold-cache evidence and zero-mutation-intent rule remain unchanged.
No credential/source secret or executable bypass added. The persistent-effect
witness is a candidate feasibility defect,not a production security regression.
Exact-head CI publication evidence belongs in the scoped toolkit closeout.

## Decision and exact next boundary

**Source review reached a decisive NO-GO; catalog/profile qualification Blocked.**
Do not propose another owned experiment with more option rows on this unchanged
profile. Completing its positive fixture list cannot remove this veto.

Recommended next gated slice: **design-only V2.3 Core-Supported Theme Development
Profile Decision and Bootstrap Closure Revision (R2)**. Evaluate one fixed,
private worker-only WP_DEVELOPMENT_MODE='theme' candidate against the full source/
effect graph, including this cache reader/writer, other theme-mode effects and
minimum-WordPress differences. Prefer that bounded design over permitting writes
or creating a theme filesystem merely to evade the finding. It is a proposal,
NOT an enacted constant, hook bypass or earned write-free profile. Do not use
'all', remove callbacks, fake caches/defaults, skip bootstrap/shutdown, change
site configuration, add files to LocalWP or execute native proof under this approval.

This changes the selected profile's semantic premise and needs an explicit design
decision before implementation. Even if that design qualifies, actual local
fixtures/accounts/permissions/native proof require a later separate exact-target
and owned-scope gate. No production support, authentication/HTTP readiness or
WordPress6.0 qualification follows from this7.1.2 static witness.
