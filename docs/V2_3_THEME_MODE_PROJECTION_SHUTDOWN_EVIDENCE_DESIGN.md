# V2.3 Fixed Theme-Mode Projection and Full-Shutdown Evidence Design

2026-10-24. Explicitly approved DESIGN ONLY. Dashboard owns this design; uploader
owns the unchanged private proof scripts. R0 source/design analysis, R1 Markdown
publication. No executable/test/config/policy/dependency/version change, actual
authentication, storage write, native execution, production wiring, grants,
activation or rollback. No WordPress/site/SQL/fixture/account/permission operation,
package, release or deployment. Uploader0.5.25/dashboard0.1.77 and minimumWP6.0
remain unchanged. This is NOT implementation or native-proof approval.

Excluded review bases: dashboard `d79946f4fe540d178cb9086753c236b7e153eb75`,
uploader `4d8cecf5ce98080e2f26d86dbd9b4c17700e7fa6`, toolkit
`b7b5ccd4aa92534f800292a389c31dd0d702e7dd`.
Parent: [catalog/resource review](V2_3_THEME_MODE_BOOTSTRAP_CLOSURE_CATALOG.md).
Retain the [read/absence/effect contract](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md)
and [exact theme-mode profile](V2_3_THEME_DEVELOPMENT_PROFILE_CLOSURE_DESIGN.md).
Changed scope: this design, catalog successor pointer and both plans only.

## Decision

Design one fixed, test-only projection catalog and a supervisor-accepted shutdown
transcript. Do not expand the actual eight-key worker or install a new availability
owner, generic DATA seal, public endpoint or profile-selection framework.
The candidate remains pinned7.1.2, absent owned content/theme/language/plugins,
WP_DEVELOPMENT_MODE='theme', debug/log/display=false, genuine cold nonpersistent
core cache, WP_ADMIN=true, SHORTINIT=false and unchanged strict fences.

The catalog is a candidate, not a certificate of complete core reachability.
Some formerly open branches are closed below by source reasoning. Full includes/
object-lifetime/resource and physical qualification remain prerequisites to a
native request. A pure contract implementation may proceed separately without
pretending to establish those prerequisites. Never repair an unknown read/writer
by returning fake defaults, warming cache, deleting hooks or raising limits.

## Source-frontier resolution

Public7.1.2 ZIP was read in memory, never extracted/executed. All1900 PHP members
were available for discovery, not all individually proved safe. The following
are source conclusions under the fixed candidate, NOT executed WordPress tests.

| Frontier | Reviewed source and outcome | Bound / refusal |
|---|---|---|
| Fifteen register_attribute callbacks | align, anchor, aria-label, background, border, colors, custom-classname, custom-css, dimensions, layout, position, shadow, spacing, typography, WP_Duotone::register_duotone_support | All inspected bodies modify attributes/inspect support arrays. block_has_support:2734-2749 and wp_has_border_feature_support:153-164 are array/type operations, not theme-JSON/DB readers. Do not invoke their apply/render siblings. |
| Metadata/property transformations | Three literal registration sites, including multiline search: wp_migrate_old_typography_shape (blocks.php:2761-2801), WP_Duotone::migrate_experimental_duotone_support_flag (:1316-1324), wp_mark_auto_generate_control_attributes (auto-register.php:30-61) | Support/attribute transformations only; none changes script/module file paths. Old typography can issue a diagnostic; no suppression change is allowed. Unknown dynamically registered callbacks are Unsupported, not silently ignored. |
| Block construction | WP_Block_Type constructor:347-351/set_props:543-578 and registry::register:48-103 | Registry construction does not render or demand lazy variations. The reviewed property filter adds attribute metadata. Retain the parent's handle fast paths; file:/render/variation callbacks are not blanket-approved by a name count. |
| AI autoload | php-ai-client/autoload.php:13-45 maps two fixed namespaces to library files. DiscoveryStrategy::init:29-35 prepends a strategy, not a client request. Cache/event adapters have no constructors calling their operation methods. | AiClient::setCache/setEventDispatcher merely assign statics; defaultRegistry:107-112 constructs ProviderRegistry. No implicit provider registration was found in these paths. |
| Provider/credential branch | ProviderRegistry starts with empty private maps, no constructor/parent constructor; getRegisteredProviderIds:116-118/hasProvider:128-130 and lookup helpers:488-502 inspect maps. Whole-PHP literal registerProvider search found its declaration, not an external invocation. connectors.php:854-884 checks hasProvider BEFORE key-source/get_option. | With absent plugins/MU/theme code and no injected registry, credential loop skips. A lexical search alone is not authority over generated calls; unknown code/source/factory or nonempty provider registry invalidates the candidate BEFORE credential access. No key/env/secret option is added to P/N. |
| Early user/locale | determine_locale uses get_user_locale in admin; fixed empty cookie/auth request leads to anonymous core user. wp_parse_auth_cookie:1033-1038 returns on empty cookie; user.php:521-534 returns before application-password authentication if auth fields absent; WP_User::get_data_by:217-225 rejects empty login/ID before SELECT. WPLANG stays empty, absent language tree prevents translation-file inclusion. | Does not prove authenticated HTTP, capability/nonce or storage behavior. Any cookie, PHP_AUTH field, customizer/preview/scrape/install/recovery input is a profile mismatch, not a route to more privileges. |
| PHP shutdown registration | wp-settings.php:166 registers shutdown_action_hook. Disabled fatal handler returns before registration (error-protection.php:81-95); absent scrape fields return before transient lookup/registration (load.php:1846-1882). Four lexical sites include one comment; three executable core registration sites identified. | Under these predicates the expected core PHP callback is shutdown_action_hook. A late hook marker is insufficient: load.php:1302-1311 invokes shutdown ACTION and THEN wp_cache_close; genuine cache.php:342-344 returns true. |

All fifteen callbacks were read, not just three representative examples. This
closes those specific effect questions, NOT a worst-case traversal certificate.
The fifteen-by-registered-block traversal still needs finite counts and actual
resource evidence. Source discovery found12 files defining __destruct: FTP/image
editors, HTML token, AI stream, PHPMailer, Requests transport, three SimplePie
classes and Sodium state. Definition is NOT instantiation; no general destructor-
free claim follows. Future complete include/object manifest must classify each
reachable instance, callback and lifetime, including non-PHP metadata/resources.
Unknown late PHP registration/destructor paths keep native admission closed.

## Fixed projection and fixture contract (not installed)

Adopt the parent's exact29 P names and7 N names without adding aliases or
namespaces. P is a compiled literal catalog, not supplied by a request/config
array. The only substitutions are the existing owned12-hex prefix and24-hex
non-secret ownership marker; source/profile identity and mode are not caller-
selectable. The supervisor chooses only the existing autoload/fallback cases.

P values: existing eight synthetic values, eighteen arrays containing ONLY
_multiwidget=>1, link_manager_enabled='0', permalink_structure='', and cron.
Keep widget_recent-posts/widget_recent-comments hyphenated; no legacy fallbacks,
numeric widget instances, objects or arbitrary serialized payloads. Prefix user
roles remains the existing synthetic administrator/read/manage_options map.
No real users/options/settings or secret-bearing values may populate this catalog.

N remains hack_file, wp_attachment_pages_enabled, category_base, tag_base,
use_smilies, blog_public, theme_switched. No physical N row, cache injection or
pre_option/default substitution. The worker can request an exact N SELECT; only
the genuine projected empty result/default establishes absence. A request counter
is not proof of that result. Future isolated physical checks must prove it.

The options source table must have29 P rows +two protected-name canaries with
synthetic values +proof_owner =32. Keep all three hidden rows and the existing
32-row cleanup preview ceiling. Existing two actors/four metadata rows remain
unchanged, not expanded to the broader review ceiling. A necessary thirtieth P
name is a profile/resource decision BEFORE provisioning;64 review names/64KiB
is not authority to create33 rows or permit more application queries.

Future native view predicate must be byte-exact for canonical P identifiers,
including prefix; reject case/trailing-space/collation aliases, not just compare
PHP arrays. Design a binary identifier comparison and audit actual collation and
genuine core SELECT behavior before use; do not implement schema/permission/view
changes now. P/N/D validation must precede setup/account creation. Invoker only
receives read-only projected views, never source tables/unsafe view/definer/root
credentials or file/routine/DDL/DML privileges. No writer-capable handle may cross
the boundary. No query rewriting or broad wildcard read permission is allowed.

For negative physical alias/N controls, use separately owned bounded fixtures;
replace a P row if necessary, never append a33rd row. Such a partial fixture is
NOT a positive complete-bootstrap fixture. An expected refusal/cleanup result
cannot count as successful bootstrap. Actual accounts/fixtures/permissions and
cleanup still require a separate confirmed R3 local-only scope.

cron uses one ten-digit timestamp T, six hooks/recurrences/intervals from the
parent, empty args, args key md5(serialize([])), and version=2. Proposed native
epoch rule: capture wall-clock W0 with monotonic root start M0, T=floor(W0)+86400;
reject inconsistent clock samples or elapsed root budget before starting. The
positive transcript must end within original deadlines, with all events still
future; time rollover, stale T, malformed args/hash/interval/version are refusals,
not permission to schedule or refresh anything. No actual cron is read/changed.

Executed synthetic PHP primitives only, virtual T=1800000000:29P/7N disjoint,
32 total options rows; sum of serialized P VALUE bytes1779, cron979, each widget30,
empty-args key40cd750bba9870f18aada2478b24840a. Fixed marker24 characters and the
existing URL/roles account for these exact values. Keys/SQL/encoding/object
overhead are not included. This is not measured PHP memory, a DB row/result proof,
source-complete query bound, or native clock test. No WordPress source executed.

## Shutdown evidence lifecycle

Use a private test transcript, not an application API or consent/admission grant.
States are monotonic; any failure is terminal and sticky:

```text
UNSTARTED -> FENCED -> CORE_RETURNED -> CORE_SHUTDOWN_RETURNED
          -> CANDIDATE_EMITTED -> PROCESS_CLOSED -> SUPERVISOR_ACCEPTED
any failed/missing/repeated/out-of-order condition -> REFUSED
```

1. Before core, establish the original query/HTTP/diagnostic/include/source/
   output/deadline fences and a failure-only early-exit sentinel. It must never
   emit success or treat registration as execution. Freeze the exact empty
   request/cookie/auth environment, source identity and initial cache provenance.
2. Invoke unmodified core once. CORE_RETURNED means genuine wp-settings returned
   normally through wp_loaded, not an exception converted to successful return.
   A blocked dependency is refusal. Register a separate PHP finalizer ONLY after
   normal core return; PHP registration order then places it after the existing
   core callback, not merely last within WordPress's shutdown action.
3. Let natural PHP shutdown invoke core exactly once; never manually dispatch
   shutdown, wrap/replace core's function, remove callbacks or invoke cache-close
   ahead of it. Expected hook observations plus source-closed normal return from
   shutdown_action_hook establish CORE_SHUTDOWN_RETURNED, including cache close.
   did_action('shutdown') alone only records entry, NOT completion.
4. The late finalizer verifies all prior state, sticky failures, exact expected
   source/include/callback/result domains, actual cache P provenance and counters.
   It emits ONE bounded candidate frame, NOT a standalone passed:true receipt.
   Keep query/HTTP/diagnostic fences active through process teardown: do not
   restore the handler or close wpdb early. No later query is permitted, even an
   otherwise allowed P/N SELECT, because it would invalidate frozen counters.
5. Any after-candidate query/HTTP/unsuppressed diagnostic/extra output must invalidate the
   transcript and refuse, including caught exceptions. The guard must latch the
   violation before throwing and append at most one bounded non-secret failure
   frame if a candidate was already emitted. No return path may erase the latch.
   Future implementation must test swallowed exceptions, not rely on exit code.
6. Parent waits for the owned process AND both pipe EOF/close events under the
   existing deadlines. Require exit0, no signal/timeout/unsettled process, empty
   stderr, exactly one valid candidate frame and no extra bytes/failure frames.
   Exit and EOF may be observed in either order; neither event alone suffices.
   Only then consider acceptance, after separately authorized session closure,
   state/source/permission preservation and owned cleanup proof. Child evidence
   cannot self-authorize this supervisor verdict or compensate for failed cleanup.

Proposed frame is a fixed-position JSON ARRAY with an exact tag/version, root
correlation ID, autoload/fallback case, fixed profile digest, terminal phase and
bounded integer counters. No extensible property object/duplicate-key ambiguity,
raw SQL, protected payload, credentials, cookies, paths or exception text. Use
ASCII-only canonical fields, one LF and <=4096 bytes/frame; failure frame<=256.
These are tighter proposed private protocol bounds, NOT enacted output policy.
Original stdout262144/stderr32768 character limits remain; ASCII permits an honest
byte comparison for protocol frames, not for arbitrary output, which is refused.
Unknown/extra fields, noncanonical numbers, non-ASCII, duplicate/reordered/partial
frames, wrong source/root/mode/version and post-candidate activity all refuse.

PHP can append shutdown callbacks while shutdown runs; exit can skip later ones,
and process termination may skip shutdown entirely. A late finalizer is therefore
only a candidate, not a universal end-of-runtime observer. Unknown late callback/
destructor registration fails source qualification. Parent process/pipe closure
is mandatory; exit0 without the full transcript is refusal. Keep engine teardown
ownership until zero owned sessions can be proved later; no reconnect/cleanup
using a child root/definer handle. PHP max_execution_time is not the supervisor
deadline. See [PHP shutdown semantics](https://www.php.net/manual/en/function.register-shutdown-function.php).

## Resource and test contracts before native admission

Preserve32 attempted application queries/4096 SQL bytes, child10s, SQL child15s,
lock-wait2s/statement5s, parent20s request/root epoch and HTTP1000/2000/4000/10000ms
epochs. These belong to distinct paths; do not claim the entire multi-step setup/
cleanup operator already fits20s. Deadlines must not reset at shutdown entry or
candidate emission. No process/socket/relay/fault-injection experiment approved.

Keep separate counters for attempted application SQL, completed data queries,
bulk hydration, exact N requests/results, source/includes, unexpected intents and
shutdown progression. wpdb connection/session initialization can call lower-level
driver paths outside the query filter; catalog those explicitly. A zero-DML
listener count does not establish zero writer-API INTENTS: cache-equal updates
may avoid SQL. Source closure, physical read-only denial and actual observations
are independent evidence. The parent's8/9 option-only arithmetic stays conditional,
not a conservative total or permission to ignore an unlisted/direct-driver call.

Required next contract tests (no native execution):

| Test layer | Positive evidence | Negative / non-vacuity requirement |
|---|---|---|
| In-memory fixed catalog | Exactly29P/7N/32 rows, primitive canonical values, real PHP serialization/args hash and virtual future epoch | Added/aliased/legacy/missing P, P/N overlap, N physical row,33 rows, stale/malformed cron, objects/oversized values/source mismatch refuse. Do not relax required guards to make a model pass. |
| Isolated transcript/state model | Real modeled normal return -> core shutdown+cache-close -> candidate -> process/EOF -> parent acceptance | Early/duplicate/missing/out-of-order lifecycle states, caught late violation, error-after-candidate, exit0 with partial evidence, extra output, wrong root/source, EOF without process closure and timeout refuse. Both legal exit/EOF event orders must pass only after their joint completion. Remove/bypass each mandatory predicate and require its relevant negative to expose the defect. |
| Source-scoped preservation | Existing executable/entry guards unchanged; exact callback/reader source identities recorded | Tests must distinguish registration from execution and query requests from returned absence. An always-unavailable implementation cannot pass because a valid positive modeled trace is required. |

Do not create an artificial CORE_RETURNED/native success path or substitute a
synthetic model for genuine core. The next implementation should be small,
socket-free test support near the existing proof scripts, not a new production
policy/service framework. Tests must exercise the helper actually proposed for
later composition, not only a duplicate expected-value formula.

Native readiness additionally requires complete include/metadata/object-lifetime
closure (including reachable destructors), conservative full traversal/query/
memory/time accounting, exact current source/config match, physical binary/
collation/absence/privilege proofs, full shutdown observation, state preservation
and bounded owned cleanup. None is established by this design or existing CI.
Authenticated HTTP/input profiles and WordPress6.0 qualification stay separate.

## Disposition

**Design Complete; execution/native admission remains Blocked.** Known metadata,
attribute, provider and shutdown-order questions have concrete source-based
answers; no exhaustive source/runtime qualification is asserted. No policy/guard/
source minimum or actual profile changed. Unknown closure still fails closed.

Recommended next gated slice: **local-only V2.3 Fixed Projection Catalog and
Shutdown Transcript Safety Foundation**, using in-memory values/virtual clocks
and isolated API tests only. Keep actual authentication/storage/native execution,
production wiring, grants, activation and rollback disabled. No R3 site authority
or new owned proof follows from that foundation.

## Public source identities

Pinned7.1.2 UTF-8 text SHA256, all paths under wp-includes. These identify the
reviewed subset, NOT an installed-site or complete PHP/non-PHP source manifest.

| Source | SHA256 |
|---|---|
|php-ai-client/autoload.php|7FE20D44FD6013FD202CBE1EDBD6EC489BD613E61C5511EAA74B45901DAC69A5|
|php-ai-client/src/AiClient.php|8AC30C972F5408E8C9D8859AC76132F052586066199448EB239CD31613D865D7|
|php-ai-client/src/Providers/ProviderRegistry.php|56251F9168D805BFDDE67F58C9D4F59EC98D151442B9CE154B80F056DCF94ED3|
|php-ai-client/src/Providers/Http/Abstracts/AbstractClientDiscoveryStrategy.php|4C3F5FB5C5F5D69CC75ED64474F2579E0B723E580D6C17C5FC430A7A6236BBC0|
|block-supports/auto-register.php|49F792670C6ED62E3CD1E76744097ECF05BB438FFE4795E27E040822DEABB65B|
|class-wp-duotone.php|8235829F42D49562BF100E05489E4883C95FC4D353F66B0F5C5CBEFF81CCDC8D|
|load.php|3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A|
|cache.php|A219C2B57CC9990B560C6B497C877DB443B7E29239645A6A79B2D76839C46E19|

The15 block-support file paths are the fifteen callback family names in the first
table (duotone uses its support file). Sort paths ordinally, concatenate each
full wp-includes/block-supports/path +space+ uppercase text SHA256, join with LF,
no trailing LF: combined digest
EB45B6120828244D0473EDA806DA6432F2C84C63FA11CEBADD26EED5F9769142.
This records all inspected bodies' containing files without copying core code.

## Executed validation and focused reviews

Canonical Feature Light:four changed Markdown files plus direct unchanged proof
consumers,Other/design. One Edge Case issue auto-fixed before publication: an
initial EOF-before-exit rejection would reject a legal transport order. Revised
design and two positive modeled orders require joint completion,not fixed event
order. No introduced in-scope documentation issue remains;whole source/native
qualification still Blocked. Bloat/Structure and UI/UX unnecessary:no executable
growth,framework,UI or public behavior. No DS3/Database/whole-plugin/FULL_PRE_RELEASE
completion or historical readiness refresh claimed.

Preservation commands executed this task:

- Uploader `C:/php/php.exe vendor/bin/phpunit --do-not-cache-result --filter
  'ProjectionProofBoundaryTest|UploaderMemoryBudgetTest|BootstrapContainmentPolicyTest|ClosedEntryOwnerApiIoTest|RegistrationEntryIngressTest|ClosedEntryOwnedProofBoundaryTest'`:
  156 tests/1951 assertions,no failures/skips.
- Dashboard `C:/php/php.exe vendor/bin/phpunit --do-not-cache-result --verbose`:
  299 tests/1423 assertions,no failures;two existing Sodium-unavailable signer
  skips. Not equivalent to all local tests passing without skips.
- Transient design model:two positive legal exit/EOF orders and13 negative
  traces/predicates passed. Synthetic PHP serialization/row/value arithmetic
  passed. These do NOT execute the proposed implementation,remove real guards,
  exercise native WordPress or create permanent regression coverage.
- Eight new public identities and combined15-support-file digest matched the
  in-memory sources. Source ZIP/memory/process disposed/exited. No source files
  extracted/saved;no current site/ports/registry/service/SQL/auth/native operation.
- 43 local Markdown targets across plan/catalog/design checked;docs-only and
  whitespace checks pass. No tests changed or thresholds/skips weakened.

Final canonical focused Security after validation:no introduced scoped defect
or confirmed security-fix regression handoff. Projection authority,secret/output,
query-intent,late-failure and parent/child acceptance boundaries remain explicit;
fixed digest/correlation is not authorization. No executable view/grant/handler/
hook/budget change. Restore OptionA verified clean pushed uploader4d8cecf and
existing pristine20261024-161220 archive;no archive/checkpoint/restore created.
Exact-head publication CI/log evidence belongs to the source-scoped toolkit
record. Green isolated/CI results do not establish any pending native claims.
