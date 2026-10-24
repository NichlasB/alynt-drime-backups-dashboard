# V2.3 Complete Bootstrap Include, Object-Lifetime and Resource Closure Design

2026-10-24. Approved DESIGN ONLY. R0 public-source/design analysis, R1 Markdown
publication. Dashboard owns this design; uploader owns the unchanged proof and
socket-free helpers. No executable/test/config/protocol/dependency/version change,
actual authentication, native experiment, WordPress, application storage write,
SQL, fixtures/accounts/permissions, production wiring, grants, activation or
rollback. No package/release/deploy. Uploader0.5.25/dashboard0.1.77 and declared
minimumWP6.0 unchanged; pinned7.1.2 analysis does not qualify minimumWP6.0.

Excluded coherent review bases: dashboard `d2d7ff670c41e5301f24d32a46ee19fa14aed5dd`,
uploader `c2395f6a165a4819953000c45a8aa4c4f6bc3ec1`, toolkit
`8652be059903ae9813d90527f69df7101594c42c`. Scope: this design, both plans,
two parent dashboard source/design notes and uploader foundation successor note.
Parent: [projection/shutdown design](V2_3_THEME_MODE_PROJECTION_SHUTDOWN_EVIDENCE_DESIGN.md).
Retain [read/absence/effect domains](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md)
and [exact theme-mode predicates](V2_3_THEME_DEVELOPMENT_PROFILE_CLOSURE_DESIGN.md).
Uploader foundation: docs/V2_3_FIXED_PROJECTION_SHUTDOWN_FOUNDATION.md.

## Decision and evidence status

Design a finite, source-scoped closure review and cost ledger BEFORE revising the
actual eight-key worker. Do not turn the whole WordPress archive into an include
allowlist, introduce another availability owner/DATA seal, or admit a callback
because its file hash is known. No new runtime service, parser, profile selector
or grant token is implemented. Unknown edges, effects or multiplicities remain
unresolved; a complete design is not a completed source audit or native GO.

Four independent obligations: exact source/resource membership; reachable calls,
callbacks and branches; constructed objects/resources through their full lifetime;
and finite cumulative cost under existing limits. Passing one never fills the
others. A matched observed trace is only supporting evidence, not proof that all
unobserved paths are excluded. No native candidate is authorized by this document.

## Fresh public inventory and correction

The official [7.1.2 archive](https://wordpress.org/wordpress-7.1.2.zip) was read
in memory as text only, never extracted or executed. Archive bytes37225839,
SHA256 `8FC96C59A78B7219E4A130222B7FADB51B03E503E8B0123BEAA7E28961C21CE2`.
It contains4250 entries:3782 files and468 directories;1512 PHP files totaling
21407880 uncompressed bytes and2270 non-PHP files. These are discovery facts,
NOT candidate reachability or PHP memory measurements. Key parent hashes for
wp-settings/default-filters/blocks-json/Block_Supports/load still match.

Correction: previous records'1900 PHP-member discovery count is superseded by
this measured1512. No explanation of the prior counting error is established.
The existing synthetic transcript includes<=1900 domain remains UNCHANGED and
is not evidence of a source-complete count or an enacted native limit. Do not
silently change its protocol or reinterpret its literal-label profile digest as
an exact source catalog. Historical whole-plugin readiness rows are untouched.

Source text uses ZIP line numbers, not browser-rendered line positions. Fresh
analysis read a bounded public corpus (64MiB download/2MiB per PHP member/128MiB
aggregate analysis ceilings); these are analysis-tool limits, not new worker
budgets. All analysis streams/process memory were disposed, no corpus saved.

## Closure records and fail-closed review rules

Use ordinary source-scoped audit records alongside existing proof scripts, not
an extensible production schema. Each record has an exact source path/digest,
line/symbol, caller, phase, predicate, finite target set, effect domain, maximum
multiplicity and evidence reference. Disposition is closed-safe, closed-excluded
or unresolved. An unsupported expression/unknown predicate is unresolved, never
false/default/empty. No caller-provided catalog, root or callback list is trusted.

| Record family | Required distinction | Refusal / open obligation |
|---|---|---|
| Source artifact | Exact archive-relative path, raw bytes/hash/size; PHP code versus data/resource versus directory | Duplicate/case/canonical-path aliases, traversal, external stream wrappers, missing or changed member; do not normalize away different bytes |
| Include/read edge | Caller and literal or finite derived targets, branch predicate, require/include versus data read/probe, invocation count | Unknown dynamic target, mutable symlink/root/TOCTOU or unaccounted reader remains unresolved; file membership is not call permission |
| Call/hook edge | Registration and dispatch separately; exact callback/method/class, priority/order, args, filters and registry changes | Registered render/admin/REST/cron callbacks are not automatically executed; unknown or late registration cannot inherit file approval |
| Object/resource lifetime | Exact allocation/factory/inheritance site, constructor, retained references/closure captures, destructor/callback/stream close, possible phase of release | Declared class is not an instance; inherited destructor, captured callable, cycle/GC or resource wrapper needs transitive classification |
| Effect/cost edge | P/N/D reads/results, writer API intents, lower-level SQL, HTTP/IO/diagnostics/output plus bounded work/allocations | Zero SQL is not zero writer intent; absence requests are not empty results; unknown or unbounded contribution prevents native readiness |

A future audit expands a worklist from the fixed worker bootstrap and genuine
wp-settings root through top-level statements, constructors/factories, included
PHP/data, inherited methods, hook dispatch, autoloaders and teardown. Review
tokens/control flow, not grep alone; comments/declarations are discovery leads.
Do not execute PHP to evaluate a metadata array. Any future extraction/parser
must have its own bounded grammar/tests and reject unsupported syntax; manual
source review may resolve finite literal data without a new parser framework.

Every conditional edge needs its source predicate AND frozen candidate fact.
An absence predicate in public source is not proof that Plugin Tester has that
absence. Distinguish static source resolution from future physical evidence.
Cycles need a finite phase/iteration argument, not automatic rejection or a
visited-file shortcut. Repeated execution of one included file/callback still
costs work. No unknown branch is closed because one positive trace missed it.

Case-specific checkpoint sets cover pre-core fenced entry, normal wp_loaded
return, core shutdown/cache-close return, candidate and process/pipe closure.
Classify the full suffix, including any later autoload or destructor. Per-case
expected include SETS are supplementary, not an invocation counter:
[get_included_files](https://www.php.net/manual/en/function.get-included-files.php)
reports a repeatedly included file once. Non-PHP reads/probes and native/internal
classes/resources are separate records, not invisible zero-cost entries.

Future runtime-source preflight must cover worker/harness bytes, PHP/SAPI and
extensions, autoload/include path and auto-prepend/append/preload configuration,
fixed request/constants and absent content/drop-ins/language paths. The candidate
does not source wp-config.php or expose a root/definer/writer handle. Before/after
hashes alone cannot rule out transient source substitution; require independently
owned stable source provenance or an explicitly reviewed stronger mechanism before
native admission. No copy, locking configuration or source-tree mutation approved.

## Concrete source seeds and lifetime frontier

Fresh wp-settings text has eleven literal `new` sites: Embed468, textdomain
registry479, AI cache/event adapters484-485, Query652, Rewrite671, WP680,
Widget_Factory689, Roles698, Locale728 and Locale_Switcher737. This is a seed
inventory, NOT all allocations: singleton/factory calls, widget construction,
registry rows, closures and transitive methods also enter the worklist. Definitions
of recovery/REST/image/feed classes alone do not make their instances reachable.

Blocks/index.php:16-18 includes legacy-widget, widget-group and the dynamic list;
153-158 requires the static list and registers each entry. Metadata collection,
registry filters and the15 support callbacks therefore need target/multiplicity
records, including PHP metadata, JSON, file-existence checks, optional asset and
render/variation paths. Register_attributes:169-190 nests registered block types
and support rows;15 is NOT its total invocation bound. Exact final registry sizes
and every caller remain to be audited, not replaced by115 literal-name arithmetic.

One concrete excluded effect branch remains source-backed: block-style handles
return before their glob/transient path when separate assets are false;
[script-loader's admin predicate](https://raw.githubusercontent.com/WordPress/WordPress/7.1.2/wp-includes/script-loader.php)
returns false before its filter. Under fixed WP_ADMIN=true this excludes THAT
path, not all asset initialization or every writer in the file. Preserve genuine
core branching; never remove hooks or force a filter result to manufacture closure.

Twelve destructor-definition files were rediscovered across FTP filesystem,
GD/Imagick, HTML token, Nyholm stream, PHPMailer, Requests Curl, three SimplePie
classes and Sodium Poly1305 state. Full per-instance reachability remains OPEN.
Fresh body checks illustrate why filename/definition counts cannot close it:

| Body / exact ZIP lines | Observed source effect | Required lifetime evidence |
|---|---|---|
| HTML Token112-115; constructor99-104 | Invokes stored on_destroy callable with bookmark name | Exact callback origin/target/captures and reachability; not a generic harmless parser destructor |
| Nyholm Stream75-96 | Destructor calls close; resource fclose then detach | Exact stream/wrapper ownership and close effects; no assumption that all streams are memory-only |
| Imagick46-51 | Calls image clear/destroy on an Imagick instance | Constructor/factory reachability and native resource ownership |
| PHPMailer857-861 | Calls smtpClose | Trace its target and transport state before claiming no late network/IO |
| Requests Curl127-131 | Conditional curl_close of retained resource | Exact transport instance/resource and PHP-version semantics |

PHP [destruction semantics](https://www.php.net/manual/en/language.oop5.decon.php)
permit last-reference release before shutdown and non-fixed destructor order
during shutdown. Parent/inherited destructors, closures, cycles and callbacks must
be classified; no manual unset/GC/destructor invocation to make a proof finish
early. A returned constructor or class declaration is not lifetime completion.
Unresolved lifetime/IO effects prevent qualification even if no DB call appears.

## Resource closure ledger

For EACH autoload/fallback case, partition pre-core/core-load/pre-init/init/
wp_loaded/core-shutdown/engine-teardown work. Record per-edge maximum visits,
attempted/completed SQL, bulk hydration and P/N results, data bytes, include/data
read/probe operations, registry/metadata/support traversals, live object/resource
peaks, output and elapsed budgets. Add repeated/cyclic visits, hook-added work,
exceptions and refused-attempt costs. Excluded edges contribute zero only with
closed predicates; unknown is not zero. Do not sum exclusive alternatives as if
both run, or omit sequential phases as if they were alternatives.

Required invariant: bounded static case totals precede any future measurement.
For instance support dispatch <= B*S only after exact registered block count B,
support traversal count S and per-callback inner work are closed. This is a cost
worksheet, NOT a new arbitrary-loop allowance. File bytes, decoded PHP arrays,
retained objects and live process memory are distinct; do not infer one from the
other. The existing1779 P-value bytes/979 cron bytes exclude all such overhead.

Preserve32 attempted application queries/4096 SQL bytes, child10s, SQL child15s,
lock-wait2s/statement5s, parent20s request/root epoch and HTTP1000/2000/4000/10000ms
epochs. stdout262144/stderr32768 are existing JS character guards; the modeled
ASCII frame4096-byte bound does not redefine them. No resetting at phase/candidate,
raising thresholds, warming caches, changing core callbacks or trimming canaries.
The full supervisor setup/cleanup sequence is NOT proved to fit a20s request cap.
No approved native peak-memory cap/total has been established by this analysis;
the PHPUnit256M test cap must not be imported as a production/native budget.

Option-only8/9 SELECT arithmetic remains conditional, not a full bound. Direct
wpdb/driver initialization, repeated queries, registry reads and teardown must be
separately classified; attempted writes still violate intent even if read-only
privileges deny them or cache equality avoids DML. All post-candidate effects are
forbidden, including otherwise allowed P/N queries, because totals are frozen.
Keep the existing29P/7N/32-row boundary; any thirtieth P name, changed effect or
unbounded contribution goes to an explicit profile/resource decision BEFORE setup.

## Future proof handoff and non-vacuity requirements

After the source audit closes, a separately approved owned physical/native proof
must establish exact installed/source/config match, binary/collation P/N views,
read-only privileges, genuine cold cases, normal core return, natural shutdown
including cache close, then process/pipe closure, state preservation, zero owned
sessions/resources and bounded cleanup. No such proof runs today. PHP's
[shutdown function rules](https://www.php.net/manual/en/function.register-shutdown-function.php)
do not make max_execution_time a supervisor deadline. Unknown late callbacks or
destructors keep the gate closed; finalizer registration/did_action/exit0 alone
cannot prove complete teardown. Existing fences must remain effective to the end.

The current modeled_only v1 candidate and literal-label digest cannot be promoted
into native certification by changing an expected boolean. Preserve those APIs.
Any future real evidence composition/native protocol requires explicit design
and version/domain separation; no new frame fields or writer handles added here.
No protected payload, SQL, cookies, credentials or arbitrary exception/path text
belongs in a receipt or source ledger; record non-secret classifications/digests.

Future permanent tests must consume the actual proposed audit artifacts/helper:
positive closed SMALL synthetic graph, real graph still unresolved; missing/
extra/aliased/tampered artifact, unknown dynamic include/callback, inherited or
captured destructor target, repeated-include undercount, unbounded SCC, omitted
cost, late allocation/IO/query and wrong-case/phase/source all refuse. Removing
a required predicate must admit the corresponding bad case and kill its negative
test; an always-unavailable owner is not useful positive proof. No fake core
return, arbitrary evaluation, mocked SQL absence or synthetic physical certificate.

## Validation, review disposition and next boundary

Canonical source-scoped Feature Light: six changed Markdown files plus unchanged
direct helpers/native consumers. Other/design risk, no UI/endpoints/schema. One
documentation finding corrected with superseding notes:1512 measured PHP files,
not1900. No unresolved introduced documentation issue. Bloat/UI/UX unnecessary,
no executable growth. This is not a new DS3/Database/whole-plugin or FULL review;
historical release readiness remains unchanged. Final focused Feature Security:
no introduced scoped defect or confirmed security-fix regression handoff; source
identity, lifetime and observed execution are independent gates, never authority.

Executed this task: uploader isolated related160/2415 and Node transcript42/42,
no failures/skips; dashboard299/1423 with two existing local Sodium signer skips.
No tests changed or thresholds weakened. Fresh archive/version/subset identity
and source-text checks above are discovery/source evidence, NOT core execution.
Markdown references/whitespace/docs-only preservation and exact-head CI/logs are
the publication gate; full CI details belong to the source-scoped toolkit record.
Restore OptionA verified clean uploaderc2395f6/pristine20261024-161220 archive;
no new snapshot/checkpoint/restore. Required static workflow/registry read only;
no site/ports/metadata/services/MCP operations and no application state touched.

**Design Complete; complete source audit/resource/native qualification Blocked.**
Implementation-plan progress: R2 now has explicit artifact/edge/lifetime/cost
closure obligations and a corrected discovery baseline, not a bootstrap GO.
Recommended next gated slice: **repository-only V2.3 Pinned Bootstrap Reachability,
Lifetime and Effect/Cost Audit**, filling this finite ledger from pinned public
source BEFORE any native harness revision. Keep native experiments, storage writes
and production wiring disabled; do not substitute another one-key native attempt.

## Newly inspected public text identities

These UTF-8 text SHA256 values identify inspected subsets, not installed files or
the complete future raw-member catalog. Full archive identity is recorded above.

| Source under wp-includes | SHA256 |
|---|---|
|blocks/index.php|0835D4481FBF05BF2AEB245C3AFCE5B542B346916821876085C3EEF85D511FC8|
|html-api/class-wp-html-token.php|1B9BAC66C6DE18CAA785A62EA5623332A12441990E476B46D2FE7052B114D8CD|
|php-ai-client/third-party/Nyholm/Psr7/Stream.php|9ED57840D10BEED9647646EECF006CDE645FB63CE227471F801E626488BE0C1F|
|class-wp-image-editor-imagick.php|35C0B623CB11E7AD78D0239C8345FCB748875789E8146FE34B83B208079E291E|
|PHPMailer/PHPMailer.php|45599A196AE7944EE2DCD4F3D3DA0AC4243513D346B2D77BBD15DBD0C37F7064|
|Requests/src/Transport/Curl.php|2D10F26C919BA6D1B20B6A3FBFAA81DB4EF2B44871FE46392BE00B70793F7997|
