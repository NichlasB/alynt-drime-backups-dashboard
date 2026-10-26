# V2.3 Real Authenticated Closed Entry Surface and Request Policy Design (R2)

2026-10-23 successor: [fixed owner/raw-reader integration design](V2_3_FIXED_CLOSED_ENTRY_OWNER_RAW_READER_DESIGN.md)
selects earned transport facts,request-wide original identity/lifetime and a
separate GET issuer. Documentation only;no genuine auth/IO or public wiring.
Current next gated slice:local-only V2.3 Fixed Closed-Entry Owner and Bounded
Reader Safety Foundation (R2),isolated API/IO tests only. Earlier next-step text
below is historical;storage/native execution,wiring,activation/rollback stay off.

Successor implementation record (2026-10-22):uploader owns
docs/V2_3_CLOSED_ENTRY_INGRESS_POLICY_FOUNDATION.md. Strict dormant ingress and
fixed policy DATA are now implemented with synthetic bytes/isolated APIs only.
This does not establish genuine authentication,actual HTTP framing,nonce issuance
or native/public wiring. The design below remains the prospective full contract;
its original validation is historical. Current next gated slice:local-only V2.3
Fixed Genuine Closed-Entry Request Owner and Bounded Raw Reader Integration
Design (R2),design-only;no storage/native execution or production wiring.

2026-10-22. Approved repository-only,DESIGN ONLY. No executable/test/config,
WordPress/user/database,fixture,listener/process,native experiment or site change.
Storage writes,public registration,wiring,grants,activation and rollback remain
disabled. Versions remain dashboard0.1.77/uploader0.5.25. No release/package/tag/
deploy. R2 here is the readiness-gate label,not a claim to raise agent risk tiers.

## Decision and ownership

Select one future **uploader-local authenticated admin-post POST** entry,not a
dashboard remote action,REST endpoint,AJAX command,CLI,cron or public callback.
Reserve action `alynt_drime_backups_register_closed` and future hook
`admin_post_alynt_drime_backups_register_closed`;no nopriv companion. This name is
proposed only:do not register it,include the dormant family or turn an unavailable
facade/native entry on. Hook dispatch is not the authorization decision.

The future operation is one closed-ledger registration with at most one freshly
authorized same-request diagnostic observation. It cannot reserve WPvivid intent,
scan,enqueue,upload,grant active cadence,apply/rollback a schedule,repair storage,
pair a site or contact Drime. Existing registration grammar's other purposes do
not expand this entry's allowlist. Preserve legacy callers and behavior.

WordPress documents authenticated admin-post hooks as accepting GET or POST;
therefore this selected boundary must explicitly enforce POST. WordPress nonces
are reusable CSRF intent tokens,not authentication,capability or replay locks.
Sources:[admin-post](https://developer.wordpress.org/reference/hooks/admin_post_action/),
[nonce security](https://developer.wordpress.org/apis/security/nonces/).

Dashboard owns this cross-plugin design and readiness policy;uploader owns all
future source/guards/storage. Explicit excluded review bases:
dashboard961af29f807265337f4d99f9d42548e63ac7c1f5,
uploader650872be7d4e1eb5106cd57158de125227f56e0a,
toolkitdcd228481b2857ee55f98b72d1f3ae67130c903b. Scope is this coherent R2
decision plus directly inspected dependencies,not a whole-plugin review.

Parent:[production-wiring readiness](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md).
Uploader docs/V2_3_BLOCKER_ROLLBACK_ACK_CORRECTION_OWNED_PROOF.md owns R1's accepted
fixed-local joined proof;it does not prove this new HTTP ingress,other hosts or
production readiness. That proof is historical,not rerun in this design.

## Actual source versus proposed entry

Uploader references are existing sources,not new APIs:

| Source | Current fact / design consequence |
|---|---|
| alynt-drime-backups-uploader.php;includes/class-plugin.php | Runtime loader/constructors do not install this registration family. No new include or hook in this slice. |
| includes/class-admin-page.php | Existing uploader Tools screen uses manage_options. Future local nonce issuer belongs here,not dashboard enrollment/polling. No UI change now. |
| includes/class-registration-request-owner.php;includes/class-real-closed-writer-request-owner.php | Public issue/consume remain redacted unavailable. Do not treat their negative-only result as completed positive R2 integration. |
| includes/class-registration-request-contract.php | Small issuance form and full typed envelope are separate. Flat string/reference/UTF-8/key/value/JSON caps and one unslash already exist. Other purposes must be rejected by the new entry. |
| includes/trait-real-closed-writer-core-guard.php | Fixed genuine cookie/session/actor/manage_options guards around nonce;single-site/native cache states false or null,exact core classes/prefix/no switched context. POST-only same_request must not be reused as a GET issuer guard. |
| includes/trait-registration-construction.php;includes/trait-registration-eligibility.php | Guards before eligibility/UUID issuance;ten participants,schema8,disabled settings/no pending writer or ledger command,exact context and one proposal. No supplied proposal/context import. |
| includes/class-registration-observation-request.php | Small read nonce binds original observed command;full read identity/domain separate. Parsing is not genuine authorization. |
| includes/class-registration-native-lifecycle.php;includes/trait-registration-native-lifecycle-methods.php | Final native_entry_available remains false;fixed composition is not a registered controller. Do not toggle this method as an activation mechanism. |
| includes/trait-registration-native-lifecycle-root.php;includes/trait-registration-native-lifecycle-read.php | Original once-only origin before possible DML,owned release/writer erasure,fresh read guards,one barrier,post-close interpretation and final erasure. Preserve its clocks/results/private ownership. |
| includes/class-registration-native-admission.php;includes/class-registration-lifecycle-request-owner.php | Private standalone/preparation paths are not interchangeable HTTP roots. No reflection-based production calls,callback/provider/handle import or synthetic provenance. |

No hostile same-process PHP isolation is claimed. Approved fixture/relay workers
are evidence infrastructure,never production controllers or native factories.

## Fixed transport contract (prospective)

Future URL is this installation's generated admin-post.php,with no query string.
Only exact POST and application/x-www-form-urlencoded (optional sole UTF-8 charset)
are supported. Reject multipart/JSON/files,method overrides and missing/ambiguous
content type. No CORS/API-key/application-password/polling-token authorization
alternative is designed. Logged-in cookie session and current local capability
are mandatory;unsupported authentication/profile refuses without fallback.
Generate the destination from trusted installation admin configuration,never a
posted URL or forwarded Host/Origin header. Such headers are not actor identity.
An approved future live target must use its reviewed HTTPS/admin-cookie policy;
plain HTTP for an explicitly confirmed local fixture does not approve production
transport. No cross-origin request or redirect is introduced by this design.

Exactly four wire keys,case-sensitive and unordered:

| Key | Required value |
|---|---|
| action | alynt_drime_backups_register_closed |
| purpose | register_closed |
| confirm | register |
| nonce | Nonempty bounded original small issuance nonce |

No actor/blog/site UUID,generation,revision,context digest,command/operation ID,
clock,full envelope,read command,origin,callback,enable flag or redirect target
may be supplied. Remove only the verified action key before calling the existing
issuance parser with the remaining ORIGINAL WordPress-slashed strings.

Raw ingress must be inspected once with a bounded read (at most16385 bytes to
detect excess over a16384-byte encoded transport cap);Content-Length is not
authoritative. Require complete body/no parse truncation. Enforce4096-byte
aggregate decoded/JSON body including transport overhead,then all existing
domain4096/key/value2048/UTF-8/reference bounds. This new stricter transport
ceiling does not widen the domain/native limits. If original raw bytes cannot
be obtained safely,refuse;do not fall back to trusting PHP's collapsed form.

Validate percent escapes and URL decoding exactly once;reject duplicate decoded
keys,arrays/bracket names,empty or numeric keys,dot/space aliases,NUL/control
characters,invalid UTF-8 and unknown fields. Literal ampersand separators only;
no semicolon parser fallback. Compare unique decoded raw fields to a separately
once-unslashed inspection of $_POST;reject disagreements or non-string values.
Reject nonempty $_GET/query string,files and mixed-source action/nonce. $_REQUEST
is never the source of a domain argument even though WordPress uses action to
select a hook. The hook name is not proof of the posted action or actual method.

Do NOT feed an already-unslashed copy into the existing parser:that parser owns
one stripslashes pass. Raw decoding versus WordPress unslashing are distinct
layers. Do not normalize invalid input with sanitize_text_field,absint,default
values or double decoding and then accept it. The future issuer must produce
only these keys (no default _wp_http_referer or named submit field).

## Issuer and authorization ordering

The future small nonce issuer is restricted to the uploader's existing local
Tools screen on exact GET,after fresh genuine logged-in cookie/session/current
manage_options checks for the current supported installation. It performs no
safety/settings/ledger capture,UUID generation,provisioning or native session.
Issue only the existing blog-bound register_closed small nonce;no full mutation
or read token is exposed by the page. No guest nonce or on-demand remote issuer.
This requires a distinct fixed read-only GET issuer policy—not weakening the
existing POST-only guard or changing $_SERVER to make it pass. The policy must
pin the same actor/blog/cookie and restore temporary core/cache views on failure.
Core authentication/role reads are permitted auth work,not protected option-frame
capture. No new screen/form/rendered message is implemented by this design.

Future POST owner order is fixed:

1. Consume request/handler latch even on refusal;reject method,wire shape,bounds,
   ambiguity or reentry before protected reads or UUIDs. Capture only bounded
   transport and non-secret core installation metadata required for guards.
2. Pin original current blog/prefix/core db object/local actor/cookie/session.
   Require fresh genuine authentication,current manage_options,original small
   nonce domain and fresh authentication again. Role-name checks,hook dispatch,
   nonce verification alone and remote pairing/opt-in do not authorize.
3. Only after successful authorization may the fixed owner acquire/validate the
   protected ten-row capture and construct its own proposal/full nonce. Recheck
   original actor/context/intent after callbacks/capture and before native setup,
   immediately pre-BEGIN and at each existing release/read boundary. Missing
   R3/R4/R5 prerequisites remains unavailable;never create/adopt/repair state.
4. Use one same-stack closed lifecycle,not issue in one HTTP request and consume
   an exported blueprint in another. Retain own minimal origin/complete collision
   set before possible DML;one existing ledger CAS maximum,no borrowed handle.
   A future approved production composition must be explicitly designed/proved;
   changing the current unavailable bit or exposing a private method is forbidden.
5. After actual writer release and erasure,independently renew current genuine
   actor/cookie/capability/blog/physical/intent guards before any read nonce/UUID
   or protected read. Only own committed/uncertain history with owned release
   may be considered for one fresh diagnostic observation. Preserve false native
   results;unknown release,not-written,rolled-back or expired paths never qualify.
6. Keep separate small/full read guards at construction,claim,post-setup/pre-BEGIN
   and after acknowledged barrier close. No WordPress callback while locking
   storage. Interpret only after release and fresh guards;finally consume/erase
   all origin,proposal,nonce,IDs,handles and temporary process-local state.

The four existing nonce domains are reused exactly,not recomputed from posted
actions or substituted with wp_rest/settings nonces:

| Intent | Existing action builder/domain |
|---|---|
| Small mutation | Registration_Request_Contract::issue_action(server_blog,register_closed):alynt_drime_backups_scan_registration_issue_v2:blog:register_closed |
| Full mutation (private) | Registration_Request_Contract::parse:alynt_drime_backups_scan_registration_command_v2:hash(ordered typed envelope) |
| Small read (private) | Registration_Observation_Request::issue_action:alynt_drime_backups_scan_registration_observe_issue_v2:hash([blog,observe_registration,original command]) |
| Full read (private) | Registration_Request_Contract::parse:alynt_drime_backups_scan_registration_receipt_v2:hash(ordered typed read envelope) |

Preserve core nonce returns1/2 acceptance plus original UTC expiry/nondecreasing
clock checks. Full envelope lifetime<=300s,admission10s/read10s within original20s
root;no reset by fresh authentication/nonce/HTTP retry. Existing parser/native
caps unchanged. A new lookup ID must differ from every retained/generated ID.

## Replay,outputs and prerequisite gates

One-shot within one request is NOT cross-HTTP exactly-once. A replayed small
nonce may still be valid and a fresh POST could generate another command. R2 must
not promise deduplication from nonce,transient,button disable or fresh UUIDs.
Existing pending/CAS checks protect only their actual scope. R3/R5 must settle
repeat/concurrent POST eligibility,closed-ledger capacity and uncertain-history
handling before any public wiring. No automatic retry,resend,durable continuation,
out-of-band lookup or reconstruction from an HTTP response is permitted.

Prospective responses are bounded no-store JSON,not a redirect carrying state:
405 non-POST,400 malformed,403 failed current authorization,503 unavailable
dependency/native/cleanup/deadline. After verified barrier and renewed auth,200
may carry only a fixed diagnostic outcome closed_receipt_observed or
not_observed_at_barrier plus execution_supported=false,dispatch_supported=false,
retry_supported=false. HTTP200 is not current registration/commit authority.
Failed/held barrier remains unavailable,not absent or success. No public
registered=true,raw command/origin,nonce,IDs,SQL,thread/path,receipt,exception or
capability. Serialize only after all resources/slots settle;missing cleanup
overrides a success-shaped value. Fixed envelope<=1024 bytes;no new persistence,
result polling,logs/transients/user-meta or unbounded errors. Future human-facing
UI/copy and presentation require their own review when actually implemented.

R3 must define existing-row provisioning/version/metadata/autoload/capacity and
direct-SQL/core-cache coherence;R4 must approve/prove supported native target;
R5 must define revoke/re-pair/settings/session/capability/lifecycle/downgrade/
uninstall interactions. None is a caller boolean or waived by this R2 design.
R6 active authority/effects and R7 rollback stay separate. Until approved source,
tests,operational proof,full pre-release and deployment gates are satisfied,the
runtime loader/hooks and native_entry_available=false remain unchanged.

## Non-vacuous acceptance for the next dormant foundation

| Behavior | Required evidence / correct layer |
|---|---|
| Unique exact valid encoded form | Pure parser returns exactly the original three domain strings;always-unavailable fails this positive test. No nonce/auth/storage claim from parsing. |
| Duplicate/aliased/array/mixed-source key,invalid escape/UTF-8,wrong content type/method,over-cap/truncated body | Reject before simulated protected-read/UUID/dispatch counters;test exact boundaries and source disagreement,including backslashes/one unslash. |
| Current actor/cookie/blog/capability or nonce-domain guard missing/reordered | Isolated fixed-policy API tests assert guard order and no protected reads/UUIDs;assertion-killed semantic controls,not merely expected unavailable. Distinguish auth-user reads from protected frame reads. |
| Fresh GET issuer vs POST owner | Positive separate method policies;no changing the method to reuse POST guard. No protected state or generated IDs at nonce issuance. Genuine core proof is later,not mocked authentication evidence. |
| Changed context/callback reentry/expired clock | Consume and erase;no second dispatch/read or write-capable handle. Keep original source identity/results/clocks. |
| Committed/uncertain/refused/cleanup-unknown fake downstream | Typed diagnostic-only projection;no false-to-success coercion,old receipt adoption or permission flags. API doubles are not native proof. |
| Real HTTP/core/auth/storage/public wiring | Separately approved genuine WordPress/owned proof and R3-R5 work,then approved registration/release gates. Existing CLI R1 receipts cannot certify HTTP dispatch. |

Do not fabricate production success,broaden public facade APIs,add a generic
controller framework or begin native work to fill this design's test matrix.

## Source-scoped review and validation record

Canonical Feature Light:four docs (three dashboard,one uploader plan),plus direct
read-only source above. Risk Other/design(authentication/HTTP/storage discussed,
not changed). Issues found3 design ambiguities resolved:dispatch not POST/auth,
raw duplicate/slash handling,and nonce/latch replay limits. Auto-fixed executable
issues0;remaining manual decisions0 within R2 design. Future R3-R7 approvals are
explicit gates,not current design defects. No new tests manufactured for prose.
Bloat/Structure unnecessary:zero PHP/JS/CSS changes;UI/UX unnecessary:zero rendered
UI/copy changes. No formal Database/physical review or FULL_PRE_RELEASE claimed.

Final canonical focused Feature Security after documentation/QA review checks
input/source/nonce/actor ordering,SQL ownership,private data/handle projection,
replay/cleanup and disabled registration. No confirmed scoped security defect;
regression handoff is the prospective matrix above,not claimed installed coverage.
Exact local QA/published-head CI outcomes live in toolkit source-scoped evidence;
historical whole-plugin release rows remain unchanged. Full pre-release is still
required at the next coherent RC before packaging/publishing.
Executed preservation QA:dashboard PHP299/1423,two existing Windows Sodium skips,
WPCS138/structure/build pass;uploader isolated contract/lifecycle PHP81/3590,no
failures/skips.64 selected local Markdown links and15 direct uploader include
references resolve;whitespace check clean,generated assets and executable sources
unchanged. These are preservation checks,not new HTTP/authentication proof.

Repository-only design uses clean Git baselines;no site target confirmation,
Novamira call,WordPress,SQL or runtime proof is applicable. Required companion
uploader source-only OptionB archive before its plan edit:
alynt-drime-backups-uploader-20261022-221611.zip and matching.txt under uploader's
toolkit-snapshots folder;12111 files,SHA256
F34B43EBBBCFA2BE9F3095399476C98EBF06A55F44E4F665D090549412042A2E.
No checkpoint branch,restore or site/database backup. Historical R1 remains fixed.

Implementation-plan progress:R2's entry/method/body/issuer/guard/response policy
is now selected,not implemented;production NO-GO,R3-R7 still gated.

**Recommended next gated slice:local-only V2.3 Closed Entry Ingress and Request-
Policy Safety Foundation (R2).** Implement only the dormant fixed ingress parser
and isolated policy/result boundaries with synthetic bytes/API tests;no actual
authentication,WordPress/storage/native execution,hook/loader registration,
public wiring,grants,activation or rollback. Positive parser/policy tests and
removed-guard controls must earn their place;do not add an always-false wrapper.

## Path-only navigation note — 2026-10-26

The uploader includes/class-admin-page.php citation above is historical. Its
current path is includes/admin/class-admin-page.php; see the
[organization map and proof limits](REPOSITORY_ORGANIZATION_MIGRATION.md).
This is not dashboard admin or renewed HTTP/authentication/native qualification.
Original design text and deferred V2.3/wiring boundaries remain unchanged.
