# PPA Private Condition Projection and Charset-Anchor Integration Design

## Approval, exact baseline and unchanged authority — 2026-11-24

Approved repository-only DESIGN through validation, commit, push and exact-head
ordinary CI. Dashboard baseline f04186bf7d0f42c10cfdb0a1fa2b4fef2340d37c was clean,
synced and remote verified; [45476953844](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/45476953844)
freshly reverified completed/success. Toolkit baseline
0dbd377c12eea09e8168ab1b711df6f7619e2369 clean/synced/remote verified. R0 source
analysis/R1 documentation publication. Review boundary: the [condition DATA
foundation](SOURCE_POLICY_PPA_HTTP_CONDITION_DATA_FOUNDATION.md), its directly
related private owners and [consumer design](SOURCE_POLICY_PPA_HTTP_CONSUMER_CONDITION_DESIGN.md),
not a new whole-plugin or release review. The coherent preceding code boundary
remains 6bb399a8969f3c2c57febf2f2ec40d08b741a0a6 through f04186b; current patch is
documents only. No existing source, tests, catalog, worker, SQL, workflow, pins,
dependency, acceptance rule or budget changes.

No acquisition, materialization, candidate/module/core loading, WordPress bootstrap,
authentication, HTTP/SQL/native/LocalWP/site operation, fixture/account/credential/
schedule/system change, new diagnostic stream or production wiring. Native46 and
product4/10, all historical allowances and actual qualification limits retained.
No new physical milestone, resources or cleanup experiment. GitHub source API
reads below are bounded source inspection in memory, not runtime acquisition.

## Decision: preserve stock effects; v1 is not a native integration profile

The existing v1 requires seven EMPTY hook projections before/at/after a modeled
option call. Stock pinned core registers TWO option_blog_charset callbacks.
Therefore v1 remains useful finite DATA/refusal coverage but CANNOT qualify the
post-default-filters stock path as written. Do not remove callbacks, weaken its
tests, submit an empty snapshot, or set observed/consumer/native grants to pass.

The minimal successor must bind an exact stock callback profile, plus all-hook and
autoload-selector conditions, and independently join the owned charset row to the
existing anchor channel. It must distinguish source-derived effects from observed
ones. This design does not implement that successor, prove hook-lifetime closure,
or authorize native qualification. Existing post-core UNKNOWN continues to refuse.

## Source findings and reproducible provenance

At core commit db96f5eaa77a8d3e8eec8923a518fa2326f0d46a, eight exact members were
read through GitHub contents API; base64 bytes, reported length and Git blob SHA1
(`blob <length>\0` plus bytes) checked before SHA256. No source copy saved or run.
One initial class-hook line-display loop failed on PowerShell range typing; a
corrected bounded read completed. That display failure is not accepted coverage.

| Pinned core member | Bytes | SHA256 |
|---|---:|---|
| wp-includes/option.php | 105246 | fe3f0b7e115b2a50c31162ed6428abe4088c080b0f71b9678d84806168fe90d0 |
| wp-includes/default-filters.php | 39500 | 55bbf47fb835a0f64f28423d475b51f7fa72479f319fca417b4f23990d44f897 |
| wp-settings.php | 33152 | 2e3185ee44bcdd9d9c4bef8ce4891827711dec2b5d5d4c08c951738ccef51f8d |
| wp-includes/plugin.php | 37123 | d70b9e18d34ab3fe46e15548a5e8e089ea5920832f06a4d89a9d64b2f7938b0d |
| wp-includes/class-wp-hook.php | 17584 | b839c0e5672246bca8db1ab781ec8835f7732f253c375a237cbf6ec536e8d12e |
| wp-includes/class-wp-filter-sentinel.php | 760 | 68c9c675d3805a343835728d98a0efedf0df9833cb904bebf909c7e0fda5c61c |
| wp-includes/formatting.php | 357678 | 350f6b32303915df970bb0e3ea360fcd54bb0b8a7ecc3d504c3d860ab9468d32 |
| wp-includes/functions.php | 295481 | cac8a17e293df99f0683a88debd3ba5e5cce274183a4e9fd3c9f18375747f41c |

[default-filters.php:301–302](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/default-filters.php#L301)
registers _wp_specialchars then _canonical_charset, default priority10/one accepted
argument. wp-settings.php:153–154 loads it during stock bootstrap. For the EXACT
scalar UTF-8 value, formatting.php:945–955 returns before escaping/recursive charset
lookup because no special characters occur; functions.php:7715–7716/7731–7734
passes the non-null value through is_utf8_charset/_is_utf8_charset and returns
UTF-8. This narrow source argument is not a callback execution or universal input
proof; other values refuse, not normalize. Existing compat source binding remains
required. The new members are audit anchors, not edits to the active v1 catalog.

[plugin.php:183–189](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/plugin.php#L183)
dispatches the global all hook BEFORE the selected hook. option.php:600–660 invokes
the alloptions filters; 3259–3274 invokes wp_autoload_values_to_autoload before its
autoload query. The old seven-slot list is not a complete effect boundary. An
empty selected slot cannot exclude either extra path or later registration.

## Fixed owners and proposed handoffs (not implemented)

| Owner / existing source | Required future boundary | Evidence limit / refusal |
|---|---|---|
| Controller main, privateConfig, HTTP_FLOW | Own identity, exact role/exchange, original clock/credit and immutable packet before send | No arbitrary supplied packet/provider; five private jars are not independent receiver proof |
| OwnedCookieJar / ownedRequestPacket / sendOwnedPacket | Derive names from the SAME bounded header slice actually sent; bind flow, jar, method and target | Current send validates framing, not cookie grammar; duplicates across paths refuse, never collapse |
| owned-setup.php before adbh_bootstrap('setup') | Check empty CLI request/cookie state before core; retain fixed server fields | Installer charset existence is only after installation; earlier stock uses stay UNKNOWN |
| initialized-native.php before core/wp-load.php | Check empty CLI inputs before stock bootstrap, using the already bound private ticket | Current no-principal/empty-cookie ready check is after bootstrap and insufficient alone |
| owned-router.php after adbh_route, before adbh_bootstrap | Join exact received cookie/query/body-name shapes to the fixed exchange | Current meta omits cookies; post-core receipt cannot retroactively authorize bootstrap |
| owned-worker.php / initializer early plugin API | Validate exact source-bound hook shape before relevant effects and preserve existing observers | A later empty snapshot is not lifetime proof; no callback invoked merely to inspect it |
| Controller initializationAnchorSql / invariant | Independently validate charset row at quiescent before/after boundaries and bind baseline | Current initializer anchor omits blog_charset; later full invariant does not close that interval |
| Existing post-stock-shutdown receipt and parent release | Join terminal condition summary to independently observed anchors and exact exchange | Early body/receipt or child-supplied anchors alone cannot complete qualification |

Before each proposed receiver handoff, identity must join run/head/image, exact
profile/core/catalog, source manifests, role and ordered exchange. The existing
24-member catalog/digest and 25-exchange flow remain unchanged now. A future
versioned catalog must retain/rebind old members, add the audited hook/callback
sources and any actual changed owner bytes; never silently bless new source with
the old digest. Workspace Node members and staged proof PHP members stay distinct.

Producer name metadata is not an external capability or an alternative request.
Derive it inside the owner from its finite packet/serializer; never export raw
cookies, values, passwords, nonce/revision payloads or their value hashes. A future
receiver examines the already bounded fixed header and parsed arrays without
normalizing/unsetting inputs, decoding an alternate cookie language, or calling a
callback. Require canonical distinct cookie names and agreement with actual PHP
keys; reject aliases, recovery names/fields, encoded/compound cookie spellings,
duplicates or malformed types. Exact target/control value remains checked, not
merely the query-name list. Preserve compound nonce/site BODY denial fixtures as
arrays: they must still reach the original authorization/intention rejection.
This is functional post-router checking only, never pre-router containment.

## Hook/cache/effect admission and the first-use problem

The proposed stock-hook profile records exact name, priority, order, callable
identity, accepted-argument count and defining source. option_blog_charset has
exactly the two functions above at10/1 in insertion order; other selected slots,
the all slot and wp_autoload_values_to_autoload must be explicitly covered, not
omitted or assumed empty. Missing/extra/foreign/object/closure callbacks, changed
priority/arity/order or unbound definitions refuse. Never invoke them for probing,
reflect or serialize arbitrary objects, traverse unrelated registries, or remove
stock handlers. Direct WP_Hook callback metadata inspection requires the pinned
class and bounded plain metadata; caller-provided JSON is still only DATA.

A snapshot before wp-load is too early to contain default registrations. A check
after wp-load is too late for bootstrap calls. A query hook cannot see cache-hit
option effects. Adding a terminal option filter to observe values would itself
change the callback profile and would not prove the pre-filter state. Consequently
NO call-boundary observation is claimed or fabricated. A future owner must first
establish a finite source-bound registration/mutation interval covering each first
use, or explicitly return unavailable. No core wrapper, function replacement,
extra observer callback or new diagnostic stream is authorized by this design.

Cache projection must account for alloptions['blog_charset'], the direct options
entry and notoptions membership, including malformed/missing/conflicting entries.
Do not call get_option/wp_load_alloptions to obtain supposedly passive evidence:
that performs the very filters/cache/SQL effects being qualified. Only a separately
source-audited, bounded, read-only projection of the fixed in-process cache could
supply state; no arbitrary cache object/provider or entire-cache export. Protected
policy/sentinel values stay unread/unexported, and their pre-auth cache absence
guards remain. Such a physical cache reader does not exist in v1.

For an admitted literal row/cache and exact stock callbacks, an effective UTF-8
result may be SOURCE-DERIVED; it is not effective_charset_observed. Every omitted-
encoding get_option remains a required stock effect, even for zero SQL. Installer
pre-population calls, unknown callback/lifetime/cache states, other vendor paths,
all-hook behavior and aggregate resource fit remain UNKNOWN blockers. No global
consumer-closure or compatibility grant follows from the two stock callbacks.

## Charset-anchor join and resource contract

Use the existing independent parent observation channel, not child get_option or
a second diagnostics channel. After successful installer exit/reconciliation and
BEFORE initializer launch, a future fixed anchor projection must validate exactly
one binary blog_charset name, exact UTF-8 bytes and autoload=on, with bounded
cardinality and row identity. Missing-both-sides or duplicate rows must refuse even
if digest comparison happens to match. Do not repair the row or cache to pass.

Extend the existing initialization anchor's protected-key selection with that
same row; preserve all current anchors, users/usermeta/sites checksums and schema/
index checks. Compare after initializer stock shutdown, exit0, source checks and
all-server release, before freezing the full invariant. The full non-target
invariant ALREADY covers blog_charset after initialization. Its opaque digest
does not establish exact value/cardinality; independently checked row identity
must link to that SAME baseline and before/after each request, not a new baseline.

The v1 JSON charset_anchor and current SQL-framed anchor/full-invariant digest use
different encodings; never compare or relabel them as identical. A future versioned
canonical projection must bind exact row fields, parent observation phase and
existing opaque digests explicitly. Preserve old rows and deterministic binary
framing/order. Sequential reads are not a transaction or general atomic snapshot:
only independently established quiescent owned-child/session boundaries support
the intended join. Source/row drift, concurrent writer or uncertain release closes
the owner and prevents another step; no replay, reset or borrowed credits.

Prefer adding bounded fixed fields to existing parent batches rather than new
round trips. That is a design preference, NOT measured fit or a SQL exemption.
Parent commands still fit 65536-byte statement/output limits and remaining original
phase/transaction deadlines; child SQL remains setup1024/bootstrap256/auth256 with
no reset between installer/initializer setup. Job600000ms, setup/HTTP60000ms,
transaction5000ms, cleanup30000ms; existing receipt8192/diagnostic65536 caps and
all other limits unchanged. All added projection/validation work must be costed
before execution; unavailable fit blocks, never expands a budget. No query changes
or execution occur in this slice.

## Validation, review and next gate

Canonical Feature Light and final Feature Security are SOURCE-SCOPED to these
six changed documents and the directly inspected dormant/private/core sources.
Feature scope: private condition/anchor integration DESIGN. Files reviewed:6
changed documents plus read-only direct sources. Risk areas touched: documentation
of proposed input/hook/cache/DB boundaries only. Issues found:2 documentation
assumptions; issues auto-fixed:2; issues requiring manual decision:0 within this
design. Physical feasibility remains gated, not silently decided.
Light review corrected the assumed empty-hook profile and distinguished DATA,
source-derived effect, actual observation and SQL/JSON anchor domains. No runtime
security fix or test behavior change. Final scoped security review finds no
unresolved confirmed defect introduced by this document-only patch; identified
integration gaps remain explicit refusal requirements, not existing runtime grants.
No confirmed new executable security defect needs a 07A handoff here. No UI/UX or
bloat workflow applies to documentation-only changes; no Database/physical-
transaction or historical whole-plugin release-readiness row is refreshed.

Actual local validation: unchanged condition/catalog isolated tests21/21 pass,
0 failures/skips, including18+11 assertion-killed semantic controls. All437 local
links across the six documents resolve; structure, whitespace, complete scoped
diff and non-document invariance checks pass. These protect finite DATA and
UNKNOWN refusal, not the proposed physical readers. No full local PHP/core/native
suite or runtime observation claimed. Publication CI belongs in scoped toolkit
evidence; baseline CI is not substituted for the new exact head.
Future successor tests must have genuine positive stock-profile DATA, an
always-unavailable negative control, assertion-killed removal/bypass controls,
stock/extra/all/autoload callback drift, cache/notoptions conflicts, early/late
projection, missing/duplicate charset rows, mismatched digest domains, partial
release, expiry/credit exhaustion and no write/launch handles. No artificial HTTP
success or weakened v1 refusal to manufacture coverage.

Implementation-plan progress: concrete private handoffs and an independent anchor
join are specified; source review disproves the empty-hook native assumption and
exposes first-use/lifetime and cache-projection limits before implementation.
HTTP qualification and production eligibility remain disabled/unproved.

Recommended next gated slice: Repository-Only PPA Stock Charset Hook and
Anchor-Projection DATA Safety Foundation. Implement only versioned source-bound
stock-hook/cache/anchor DATA distinctions and isolated regressions, retaining v1
refusal and explicit UNKNOWN lifetime/native eligibility. No physical readers,
worker/query/HTTP integration, fixture/native attempt or production wiring. Actual
registration-lifetime closure and passive cache feasibility must be established
before any later physical integration, not inferred from synthetic positives.
