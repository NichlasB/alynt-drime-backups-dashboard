# PPA Fixed HTTP Consumer-Condition and Encoding-Effect Closure Design

## Approved scope and baseline — 2026-11-24

User approved the recommended repository-only DESIGN through validation,commit,
push and exact-head CI. Publication/source baseline6bb399a8969f3c2c57febf2f2ec40d08b741a0a6
was clean/synced/remote verified;ordinary
[45468742330](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/45468742330)
freshly reverified completed/success. Toolkit7f1d9b02f901656db729438e2f50aaab992edbe0
clean/synced/remote verified. Owning [DATA foundation](SOURCE_POLICY_PPA_SOURCE_CATALOG_DATA_FOUNDATION.md)
and [pinned-source audit](SOURCE_POLICY_PPA_PINNED_FUNCTION_CATALOG_AUDIT.md) define
the feature boundary. R0 analysis/R1 design publication,not runtime implementation.

Only documents change. No package acquisition/materialization,candidate PHP/core/module
loading,WordPress bootstrap,authentication,HTTP/SQL/native proof,LocalWP/live/client/
Drime,fixture/account/credential/schedule/system change,new tooling or wiring.
Existing DATA source/tests,worker/controller/guards,workflows,pins,dependencies and
limits stay unchanged. Native46/product4/10 and all historical allowances retained.

## Decision: condition closure is narrower than universal compatibility

The fixed private producer supports a SOURCE-LEVEL recovery-cookie exclusion
argument,but the current receiver does not enforce that argument before bootstrap.
Default-encoding mb_* is an ordinary option EFFECT,not an excluded/zero-read path.
Both need explicit phase/source/state conditions before any future physical owner
can use them. No current UNKNOWN entry is relabeled by this document.

| Boundary | Confirmed source fact | Required future condition / current limit |
|---|---|---|
| Installer CLI | Fresh fixed CLI entry and generated private config | Empty request/cookie inputs checked BEFORE stock bootstrap,not inferred from a later receipt |
| Initializer CLI | No-principal/empty-cookie assertion currently AFTER bootstrap | Pre-bootstrap input proof plus preserved post/shutdown assertions |
| HTTP producer | Five owned jars,finite cookie-name allowlist,25 fixed exchanges | Exact producer/source identity;no raw caller packet/jar substitution |
| HTTP receiver | Route/challenge/body/header checks precede core;cookie condition is absent | Refuse unexpected recovery/cookie/query conditions before core;do not delete or normalize them |
| Omitted encoding | Stock compat consults get_option('blog_charset') | Admit/check its genuine effect and charset state under existing guards/credits;never claim no reads |

Functional HTTP ONLY:the existing13 cases/25 exchanges,stock auth/nonces and full
shutdown remain. No pre-router containment,universal vendor/ABI/function closure,
transitive dependency/loaded-object attestation or production-readiness claim.

## Recovery-cookie and link path: source argument and missing receiver guard

At the exact7.1.4 core pin db96f5eaa77a8d3e8eec8923a518fa2326f0d46a,
[default-constants.php:336–340](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/default-constants.php#L336)
defines RECOVERY_MODE_COOKIE as wordpress_rec_ plus COOKIEHASH unless overridden.
The existing privateConfig source defines neither that constant nor an alternate
recovery session. Do not add an override or disable core recovery/fatal handlers.

OwnedCookieJar starts empty;accept() only admits wordpress_test_cookie,normal/
secure/logged-in auth cookies with32-hex suffixes,and wp-settings IDs1–3. The
wordpress_rec_ name is outside that grammar. Values cannot inject cookie separators
or CR/LF. Controller main owns the five jars and selects every request through
HTTP_FLOW → ownedRequestPacket → sendOwnedPacket;no browser/global cookie jar.
The fixed login target has no query;the bridge has either none or only
control=wrong-action. The pinned recovery-link handler requires GET action,
rm_token and rm_key together (link-service.php:73–79),not the existing POST nonce
or compound-site fixtures. These facts support the prescribed producer path only.

sendOwnedPacket checks framing/header names but DOES NOT validate arbitrary Cookie
values against the jar grammar;an independently fabricated packet is not covered.
The router obtains PHP headers/input and checks adbh_route BEFORE adbh_bootstrap,
but its metadata omits cookies. A valid path/challenge does not prove cookie
absence. Initializer's post-bootstrap empty-cookie check is too late to establish
pre-bootstrap exclusion. Do not turn these omissions into success via a supplied
cookie_absent=true label or a post-effect observation.

Future minimal predicate:fixed source-bound role/phase,route and exact parsed
cookie-name/query-name projection before stock bootstrap;reject unexpected names,
aliases/compound forms or recovery-link fields rather than unset them. Bind the
raw fixed-producer envelope and parsed projection,not a general-purpose PHP cookie
parser or new HTTP stack. Do not retain/export cookie values,credentials or nonces.
CLI roles must reject nonempty/unexpected request state before core. Preserve all
later source/no-principal/shutdown checks. This is a post-router functional guard,
not a claim about PHP's pre-router allocation or arbitrary outside traffic.

## Omitted encoding: admit the stock read,do not rewrite it

[compat.php:300/376](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-includes/compat.php#L294)
uses get_option('blog_charset') for null encoding. _mb_strlen/_mb_substr then use
the UTF-8 scanner or stock byte behavior. wpdb's explicit UTF-8 calls remain the
separate already-modeled no-default-option subset;do not force every caller into
it,add a shim/extension,replace core functions or rewrite arguments.

The installer calls wp_install with fixed owned users/password. Pinned
[upgrade.php:79–80/121](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-admin/includes/upgrade.php#L79)
populates options/roles before creating the supplied-password user. Pinned
[schema.php:446](https://github.com/WordPress/WordPress/blob/db96f5eaa77a8d3e8eec8923a518fa2326f0d46a/wp-admin/includes/schema.php#L446)
defaults blog_charset to UTF-8;the595–616 insertion loop skips existing keys and
assigns ordinary defaults autoload=on. Thus an owned FRESH successful install
supports this default;it is not a proof of arbitrary existing storage or later
state. Do not assume UTF-8 before population,or silently repair a different value.

get_option first runs pre_option_blog_charset/pre_option filters,then (outside
installation) alloptions/notoptions/options cache or an exact option SELECT;
installation can SELECT directly. default_option_blog_charset/option_blog_charset
and alloptions filters also affect the result. A cached value is not zero effect
or authorization. Each actual SQL command stays in the original setup/bootstrap/
auth or initializer credit/deadline domain. No exemption,SQL normalization,new
write grant or resource increase. Existing protected rows remain non-autoloaded;
an option read cannot become a protected policy read before authorization.

Concrete state gap:initializationAnchorSql protects source-policy/sentinel and
identity/config keys,but NOT blog_charset. The initializer permits ordinary
option effects and freezes the full invariant only AFTER initialization. That
full baseline detects later changes,not a charset change during initialization.
The design therefore requires an independent exact charset/autoload projection
before/after initialization AND binding into the full subsequent invariant.
Prefer the existing bounded anchor/reconciliation channel,not a new diagnostic
stream. No query is executed or changed now;future actual reads must fit unchanged
budgets and be separately approved. Unknown,missing,duplicate,non-UTF-8 or drifted
state refuses;never reset the row to make the proof pass.

Filter/cached effective value must agree with the admitted stock/default source
and owned fixture state. No exclusion of foreign/dynamic option callbacks has
been proved here. A future owner must establish source-bound hook/value conditions
before the first relevant call,not invoke a callback to inspect it or infer an
empty registry after effects. Do not publish cookies or option payloads as evidence.

## Provenance and finite DATA successor contract

The existing nine catalog anchors/manifest and all algorithm domains remain.
Future metadata must additionally bind the actual producer/router/CLI/anchor
consumer sources and relevant core defaults/install/link definitions. Exact Git
blob bytes were read in memory,size/Git SHA1 verified before SHA256;no source copy
or runtime load. The following are audit anchors,NOT a newly active allowlist.

Core rows at db96f5eaa77a8d3e8eec8923a518fa2326f0d46a:

| Member | Bytes | SHA256 |
|---|---:|---|
| wp-includes/default-constants.php | 11365 | 95c3a9ac07e13ced91017b059c3d38c1a3d827ba0293ff162da83e3d9bee4e72 |
| wp-admin/includes/upgrade.php | 116674 | 0d1d0e6e9c135452641b5a518ba41f6907e67ac668d27d5e38690f53e846527e |
| wp-admin/includes/schema.php | 45583 | a446ddd76ce401fc8173bfee4e79a98f8c3567ed3ba7560c2f167ca85e6976b0 |
| wp-includes/class-wp-recovery-mode-link-service.php | 3523 | 999f257e2e696474340758028f7dd8adf58e28fd36e7e0f8c84e7ea63eb2e740 |

Dashboard rows at6bb399a8969f3c2c57febf2f2ec40d08b741a0a6 (scripts/ci/ prefix):

| Member | Bytes | SHA256 |
|---|---:|---|
| source-policy-http-owned-wire.mjs | 7684 | a0207c24bb97682a06c5feea81c95136bdf8cfea6d8c63c8956dd7793530e9d0 |
| source-policy-http-owned-controller.mjs | 26303 | fb84996a64e7669de601cab41e73d1274843e3f88dd015264441a67c3ac14a36 |
| source-policy-http-owned-router.php | 6643 | 3a518db828113d690e391ecf4ca511e5da54b9ebab1e35261243a47c6bdde4bb |
| source-policy-http-owned-worker.php | 6422 | 6af2a8c2c32ce6773e4636cf9e6be61068b33f4860d662f3117e766c5ec31780 |
| source-policy-http-owned-setup.php | 7516 | 54ff334ad68bc6af7b8edbe707d51432e3eb43506dfe8bb887f63148f9772834 |
| source-policy-http-initialized-native.php | 8484 | 580ff710cc0243aaa777fbd08b19797bc3d2c2b6fc6bfc49f9bb7acd715c3d11 |
| source-policy-http-supervisor.mjs | 4366 | 52be25b6daba19d101eac7c0a481e1999038ce8f205160dfc14671ef76f10096 |
| source-policy-http-contract.mjs | 6141 | 1fc29c673ff1dc0d38fd7d4351f346d212ac38c1f956596d632ffa221b92661e |
| source-policy-http-initialization.php | 8241 | 7f7b22eca5c6ef061572b6cc1cc8451d634535a55eb68182327a6e17b7335ba4 |
| source-policy-http-initialized-evidence.mjs | 2601 | ce8f6b84af3843f617f0b7eb4e4cfb4e17ef6b7eebd5f02f8933fe123e67b21d |
| source-policy-http-owned-source.mjs | 6377 | 8d3f73d868138ce3599de5bf3bbbf908e766535543e76110c1f2c282c1521914 |

NEXT foundation is limited to finite source/role/phase/name-list and charset/effect
DATA predicates. Separate producer-consistent,pre-bootstrap-checked and effective-
charset-state claims;none is physical attestation. No arbitrary reviewed/excluded
flag,callable,source selector,raw cookie/body or storage handle. Keep all native,
runtime/HTTP/source-observed/consumer-closure/production flags FALSE. A matching
isolated condition packet cannot mutate the current catalog or enable launch.

## Residual blockers,validation and next approval boundary

This design does NOT clear other-core-vendor-uses UNKNOWN,prove the option-hook
registry,actual charset state,source/exec lifetime,aggregate resource fit or the
fixed PPA HTTP cohort. The receiver/state gaps above are concrete requirements,
not permission for a native retry or global 'all vendors safe' claim. Any later
consumer-status transition must name its exact scope and evidence;unresolved use
still refuses overall HTTP completion. No allowance or historical counter resets.

Required future isolated positives:fixed producer/role request projection and
explicit UTF-8/default-charset effect component DATA. Negatives:recovery/aliased/
compound cookies,wrong phase or late check,unexpected recovery query fields,
unbound source/head/manifest,charset missing/drift/filter disagreement,protected
cache inclusion,credit expiry/exhaustion,getters and forged closure flags. Remove
each admission guard and require an assertion-killed oracle;an always-unavailable
component must fail a genuine positive. Do not add artificial whole-HTTP success.

Actual current validation:nine existing socket-free catalog tests pass0 failures/
skips,including eleven source-backed controls. They protect the UNCHANGED current
UNKNOWN refusal,not an implemented new predicate or WordPress/storage proof.
All427 owning-document links resolve;whitespace and source-unchanged/full diff checks pass;
ordinary exact-head CI counts belong in the toolkit scoped evidence. No full local
PHP suite,UI/UX,Database/transaction or whole-plugin release review is claimed.

Implementation-plan progress:separates provable producer exclusions from missing
receiver enforcement,and required charset effects from the missing initializer
anchor. Exact recommended next gated slice:Repository-Only PPA Fixed-Producer
Cookie and Charset-Effect Condition DATA Safety Foundation,isolated DATA only.
No worker/SQL/HTTP/fixture implementation,source provider,native proof or wiring;
remaining UNKNOWN and physical integration stay separately gated.
