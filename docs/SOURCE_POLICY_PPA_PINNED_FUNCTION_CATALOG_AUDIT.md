# PPA Pinned-Core Function-Use and Catalog Provenance Audit

## Approved boundary and baseline — 2026-11-24

Repository-only,read-only pinned-source analysis;publication changes documents only.
Dashboard baseline aedee949327e3ecef36640640878170890701639 was clean,synced and
remote/ordinary [45445978421](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/45445978421)
reverified completed/success. Toolkit baseline8f34f56815025696b3c8a792b246e0e46b7d501f
was clean/synced. R0 source audit/R1 evidence publication,no implementation change.
The [dormant foundation](SOURCE_POLICY_PPA_LAUNCH_DATA_FOUNDATION.md) is the review
boundary,not a new runtime qualification. Native46/product4/10 and allowances unchanged.

No archive/package acquisition,checkout/materialization,candidate/module/core
execution,bootstrap,authentication,SQL/HTTP/native proof,LocalWP/site operation,
new tooling,guard/budget/pin/compatibility change or production wiring. Existing
source/tests/workflows/dependencies remain unchanged. GitHub source API reads are
not artifact acquisition or runtime observation. No lab resources were created.

## Source coverage and reproducible provenance

Exact [WordPress tree db96f5eaa77a8d3e8eec8923a518fa2326f0d46a](https://github.com/WordPress/WordPress/tree/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a),
the existing7.1.4 pin,returned5,650 entries,truncated=false. Enumerated ALL1,900
case-sensitive `.php`/`.inc` blobs across root,admin,core,themes and bundled vendors:
22,748,079 bytes,largest357,678. Read in memory with existing gh/PowerShell,50-blob
GraphQL batches;no source files saved or evaluated. Each blob's byte size and Git
SHA1(`blob <length>\0`+bytes) matched the exact tree before SHA256 indexing.
Analysis bounds do not expand any qualification/runtime resource budget.

wp-includes/script-loader.php did not round-trip the GraphQL text response. The
initial scan refused;the completed scan used REST Git-blob base64 bytes for that
member and verified size/Git identity too. A local scan-counter naming collision
also stopped an intermediate read;neither incomplete pass is counted as coverage.
No identity check was bypassed. ASCII identifier indexing uses decoded text,not
a PHP parser or a proof about non-text/dynamic names.

Completed manifest SHA256:
`3b0b19969bfaf09c13eb89c1cf8a335eee2c3c984012e15cfb783a9f983725b8`.
Reproduce with ordinal path sorting;each UTF-8 row is
`path<TAB>git_blob_oid<TAB>decimal_byte_size<TAB>raw_byte_sha256<LF>`.
Manifest covers all1,900 members,not only matches. No generated manifest/source
copy is introduced by this audit.

Case-insensitive lexical search `\b(?:ctype_digit|iconv|mb_strlen|mb_substr)\s*\(`
found55 matching LINES in20 files,including comments,strings and declarations;
55 is NOT a call count. Relevant definitions,guards and callers were manually
inspected. Variable calls,quoted callbacks,autoloading,hooks,other functions and
all possible inputs are not resolved by this search. No universal core/vendor
function closure or absence-of-dynamic-use claim follows.

| Exact pinned member | Matching lines,including non-call matches |
|---|---|
| wp-admin/includes/post.php | 1613,1614 |
| wp-includes/ID3/getid3.lib.php | 1268,1270,1279,1285,1307,1619,1622,1639,1807 |
| wp-includes/ID3/getid3.php | 85,753,755,757 (documentation/error strings) |
| wp-includes/ID3/module.tag.id3v1.php | 86,92 |
| wp-includes/PHPMailer/PHPMailer.php | 3856,3896,3909 |
| wp-includes/SimplePie/src/Misc.php | 370 |
| wp-includes/class-json.php | 950,973 |
| wp-includes/class-wp-recovery-mode-cookie-service.php | 94 |
| wp-includes/class-wpdb.php | 3701,3702 |
| wp-includes/comment.php | 1322,1334 |
| wp-includes/compat-utf8.php | 320,367 (documentation) |
| wp-includes/compat.php | 258,272,278,305,343,354,360 (includes definitions) |
| wp-includes/formatting.php | 5474 |
| wp-includes/functions.php | 6393 |
| wp-includes/link-template.php | 4588,4590,4591 |
| wp-includes/pomo/streams.php | 95,107 |
| wp-includes/rest-api.php | 2735,2751 |
| wp-includes/sodium_compat/src/Core/Util.php | 795,851 |
| wp-includes/user.php | 2321,2349,2366,2376,2377,2418 |
| wp-mail.php | 208 |

## Findings and admission consequences

### F1 — Pin bytes and phase-specific dependencies,not supplied assertions

For explicit UTF-8 mb_strlen/mb_substr,compat.php provides the conditional wrappers,
_mb_strlen,_mb_substr and _is_utf8_charset;compat-utf8.php provides codepoint_count/
codepoint_span/_wp_scan_utf8. Native helper requirements include is_string,
strcasecmp,strlen,substr,is_null,max,min,strspn,ord. Comments/control keywords in
a lexical token list are not dependencies. No iconv is needed for this fallback.

[wp-settings.php](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-settings.php#L34)
loads version,compat-utf8,compat,load at34–37,before wpdb.
[compat.php](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/compat.php#L294)
uses get_option('blog_charset') for null encoding at300/376. The two-file explicit-
UTF-8 helper closure is NOT the complete omitted-encoding effect closure:option/
cache/filter dependencies remain separate. Core user creation,REST string-length
checks and other listed callers omit encoding. No zero-extra-read/query/cost claim.

[wpdb](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/class-wpdb.php#L3676)
uses explicit UTF-8 at3701/3702 after its3677 availability guard. Absent native
mbstring does not necessarily force DB conversion after stock compat has loaded.
Library-only W/L bootstrap does not load stock wp-settings/compat;its role/phase
cannot inherit HTTP post-core claims. Its fixed user fixtures use wpdb inserts,
not wp_insert_user;loaded-but-uninvoked user.php bodies are not observed failures.

Current coreSourceData accepts two caller-provided hex digests;the catalog digest
hashes those rows. That proves DATA consistency,not their truth or consumer review.
The eleven-file Linux core-pin manifest lacks both compat members. A real catalog
must bind audited bytes/commit,role,phase,consumer and branch conditions,not merely
a supplied 'source-reviewed' string. Existing validators remain dormant/unchanged.

### F2 — ctype_digit is conditional CORE use,not globally safely absent

[Recovery cookie validation](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/class-wp-recovery-mode-cookie-service.php#L76)
calls ctype_digit at94 without a function_exists fallback,after a four-part parse
at153–161. WP_Recovery_Mode::initialize checks a non-empty recovery cookie at110;
handle_cookie invokes validation at271. Stock wp-settings573–575 initializes
recovery when single-site/fatal-error handling permits. Absence can fail for that
input branch;no recovery-cookie experiment was performed/authorized. This does
not claim that existing fixed HTTP cases hit that branch. ID3 has direct uses too.

The candidate's observed absence cannot become a universal 'gated/safe' label.
Future DATA must distinguish required,known guarded,condition-specific excluded
and UNKNOWN;unestablished exclusion refuses native admission. Do not disable
recovery,replace the function,add a module or weaken request/SQL guards to pass.

### F3 — Admit algorithm domains,not only function presence

[pluggable.php](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/pluggable.php#L2719)
wp_hash defaults to HMAC-MD5;cookie generation/validation use it at863/970,then
HMAC-SHA256 at865/972. Nonces use wp_hash at2498/2504/2548. Default password creation
uses HMAC-SHA384 plus bcrypt at2813–2816;check/rehash uses password_verify and
password_needs_rehash. Session tokens use hash('sha256') at
class-wp-session-tokens.php:71. Legacy password/PRNG branches contain md5,sha1,
crypt/random_int;loaded definitions do not prove every branch is exercised.

The foundation's sha256/sha384/bcrypt booleans do not explicitly require HMAC-MD5
or separate hash_algos from hash_hmac_algos membership. Candidate-specific auth
admission must include actual required domains and role/phase;the ten connection-
free checks alone do not prove them. password_algos is a capability check,not a
required runtime call just because a docblock names it. No new algorithm runtime
observation is claimed here.

### F4 — Guarded vendor uses are not universal reachability proof

wp-mail.php:207 guards iconv;SimplePie Misc:318 guards its370 helper;getID3:1269
guards1270,but conversion may fail without a supported route. PHPMailer uses
mb_*;POMO/sodium byte helpers have overload conditions. None proves compatibility
of this minimal module set with every bundled library.

The fixed owner's pre_wp_mail returns before mailer work in pluggable233–237;
pre_http_request returns before Requests::request in class-wp-http277–281/416.
Containment registers those callbacks before stock bootstrap. This is evidence
for those guarded paths,not proof no earlier/different vendor consumer,dynamic
hook,late include or failure/shutdown path can run. No UI,mail/feed/media operation
or pre-router containment claim is added.

## Byte-verified anchors (SHA256,not runtime observations)

| wp-includes/ member unless stated | Bytes | SHA256 |
|---|---:|---|
| compat.php | 22479 | 6687dd4bf958e127a9d9df3fcc70545d8a0bea2d94410dc2a38b6ed2ee7df58b |
| compat-utf8.php | 19386 | 7a922c1d15f8d1464c18d005d6e094126c4c82a85f672b1b9d8616db67f700ea |
| class-wp-recovery-mode-cookie-service.php | 6877 | 0290d5e6b418968e1bc03b2618593a1822e148a6e19e4b7accd35aefd58f6b59 |
| class-wp-recovery-mode.php | 11460 | cbb58ae15fd0cd848408313f2aab7eb05c94b0d1a983dcf6fe9f9e4bc76d4dbc |
| pluggable.php | 127919 | 3a2482a65b50d62ebae75a728be98b78985d3a3f5a3e007cb2d0c1338bf05777 |
| class-wp-session-tokens.php | 7319 | 0708de7171949675139eda1e476935763f65e8a3a0cf40fc534dfffa00fb04cb |
| class-wpdb.php | 121437 | e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd |
| option.php | 105246 | fe3f0b7e115b2a50c31162ed6428abe4088c080b0f71b9678d84806168fe90d0 |
| root wp-settings.php | 33152 | 2e3185ee44bcdd9d9c4bef8ce4891827711dec2b5d5d4c08c951738ccef51f8d |

These anchors are findings,NOT an active allowlist or a complete source manifest.
version.php SHA2567163a7bfaf1fbac5024c0382931f17f9993076e15ff245e93254f009e0e34cc5
declares json/hash as minimum extensions,not a complete function-use contract.
Existing PPA package/payload pins remain unchanged.

## Outcome,validation and next gate

Audit COMPLETE with confirmed catalog gaps;runtime function-use closure NOT closed.
Byte verification/manual source review does not prove actual fallback behavior,
authentication,transactions,resource fit,physical ownership/exec,transitive closure
or production readiness. All native/HTTP/runtime/production grants remain false.
No full-plugin review row refreshed. Local validation:422 documentation links
resolve;git diff --check and source/tests/workflows/dependencies unchanged checks
pass;complete scoped diff reviewed. No local PHP/core/native suite was run for
this documentation-only audit. Actual ordinary exact-head CI
belongs in the toolkit scoped record,not inferred from the baseline run.

Implementation-plan progress:exact source provenance,conditional fallback/ctype
paths and concrete HMAC-MD5 requirements replace unreviewed assumptions. This is
not a new native/product milestone. No native/artifact allowance consumed.

Exact recommended next gated slice:Repository-Only PPA Source-Bound Function
Catalog and Algorithm Admission Safety Foundation. Fixed source anchors,role/
phase/condition-aware UNKNOWN refusal and explicit algorithm domains,isolated DATA
tests and assertion-killed missing-guard controls only. No source provider,core
loading,shim/module addition,artifact/native proof or wiring. Real consumer
exclusion/lifetime/exec/combined resource proof stays separately gated.
