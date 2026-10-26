# V2.3 Fixed Genuine Closed-Entry Request Owner and Bounded Raw Reader Integration Design (R2)

Design successor (2026-10-23):[buffered input / genuine HTTP integration](V2_3_BUFFERED_INPUT_GENUINE_HTTP_INTEGRATION_DESIGN.md)
separates forward-only buffered use, external profile qualification and genuine
same-source owner roots. Current reader/entry gate unchanged; no profile installed,
HTTP/WordPress work or ingress activation. Next gated slice:local-only Buffered
Closed-Entry Reader and Profile-Admission Boundary Safety Foundation (R2),isolated
API/IO only with entry_available=false. Original design below is historical;
production NO-GO,R3-R7 remain gated.

Owned proof successor (2026-10-23): uploader owns
docs/V2_3_CLOSED_ENTRY_GENUINE_AUTH_INPUT_PROFILE_OWNED_PROOF.md. Genuine core
private helpers pass17 cases/8963 checks;full cleanup/all77 application checksums
preserved. HTTP profile Blocked:observed seekable=true conflicts with the current
predicate;server hard IO bounds/genuine Tools HTTP context remain unproved.
No gate toggled/guard changed. Recommended next gated slice:design-only V2.3
Buffered Input Profile and Genuine Closed-Entry HTTP Integration Design (R2).
Production ingress/native execution/wiring stay disabled;R3-R7 remain gated.

Implementation successor (2026-10-23): uploader owns
docs/V2_3_FIXED_CLOSED_ENTRY_OWNER_READER_FOUNDATION.md. Fixed dormant private
POST/GET source, bounded original raw reader and isolated API/IO protocol tests;
three draft regressions fixed and thirteen semantic controls. No real auth,
WordPress/storage/native execution or public wiring. Actual entry remains false:
server/SAPI framing/buffering/IO and genuine authentication require separately
approved proof. Next gated slice:Plugin Tester local-only V2.3 Fixed Closed-Entry
Owner Genuine Authentication and Input-Profile Feasibility / Owned Cleanup Proof
(R2), exact target/owned user-fixture confirmation. Original design below remains
historical design-only evidence; R3-R7 and production NO-GO remain unchanged.

2026-10-23. Approved repository-only, **design only**. No executable, test,
configuration, WordPress, database, native experiment or site change. Storage,
native execution, public registration, production wiring, grants, activation and
rollback stay disabled. Versions remain dashboard 0.1.77 / uploader 0.5.25.
No package, release, tag or deploy. R2 denotes the product readiness gate, not
permission to raise the agent risk tier or implement genuine authentication now.

Parent: [entry surface/request policy](V2_3_REAL_AUTHENTICATED_CLOSED_ENTRY_REQUEST_POLICY_DESIGN.md).
Readiness: [R1-R7 gates](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md).
Uploader owns docs/V2_3_CLOSED_ENTRY_INGRESS_POLICY_FOUNDATION.md and all future
source. This design closes ownership/reader decisions; it does not repeat the
synthetic event model or promote it into an authorizing controller.

Explicit excluded Git review bases: dashboard
6ef2cde33b6c13bbc78498454c456b3997c70d2b, uploader
b79e4a89cd70ae6661336fd336b5d81a5f51f97c, toolkit
6a16b2abbcc5e2c07a82195fe23cda9fd0b3d77e. Scope is this coherent design plus
direct source dependencies, not the last verification commit or whole-plugin
release readiness. Historical native closures and proofs remain unchanged.

## Actual source constraints

Paths below are relative to the uploader repository; proposed behavior is not
an existing API or an implementation claim.

| Existing source | Consequence for the selected design |
|---|---|
| includes/class-registration-entry-ingress.php | Pure parser accepts supplied method/maps/bytes/completeness/override facts. It proves grammar, not that facts came from HTTP. Its exact action and original-slash return remain unchanged. |
| includes/class-registration-entry-policy.php | Forgeable synthetic events and virtual clock model only. Never call step/finish to decide real authorization, cleanup or native readiness. Its diagnostic DATA is not an HTTP receipt. |
| includes/trait-real-closed-writer-core-guard.php | same_request is explicitly POST-only. guard obtains actor/cookie at each invocation; an outer original identity pin is still required across the whole request. Do not weaken existing callers or treat one guard call as a durable certificate. |
| includes/trait-registration-observation-construction-window.php | Existing role-only/alloptions fences are POST-scoped and object-local. Preserve their behavior; do not call open_window for GET or reset/transfer its lifetime. |
| includes/class-registration-request-owner.php; includes/class-real-closed-writer-request-owner.php | Public issue/consume remain unavailable. Neither is the new HTTP root. Preserve their APIs and negative-only boundary. |
| includes/trait-registration-construction.php | Private construction can inspect protected state and generate IDs/full nonces after guards. The next owner/reader foundation must stop before calling it. |
| includes/class-registration-native-lifecycle.php; includes/trait-registration-native-lifecycle-root.php | Native entry remains false. Private lifecycle_root starts its own window; wrapping/calling it would not automatically inherit an earlier HTTP root deadline. No reflection or unavailable-bit toggle as production composition. |
| includes/class-admin-page.php | Existing Tools slug alynt-drime-backups-uploader requires manage_options. It is the future small-nonce issuer context, not a new remote issuer or implemented UI in this slice. |
| alynt-drime-backups-uploader.php; includes/class-plugin.php | No closed-entry family or reserved admin-post hook is installed. Keep it that way. |

## Fixed owner and lifetime

Select a dedicated dormant, final uploader-local owner, separate from the public
unavailable facades. Proposed name: Registration_Closed_Entry_Request_Owner with
the existing full uploader class prefix. This name is not an installed class.
Its construction takes zero arguments and performs no IO. Fixed private GET and
POST procedures acquire their own real inputs; no public method/body/frame,
stream/path, callback/provider, actor, clock, capability, nonce-action or enable
argument. Isolated test seams are not production ports.

The future registered transport is still only authenticated admin-post POST
action alynt_drime_backups_register_closed, no query string or nopriv companion.
Do not add its hook/loader, a REST/AJAX/CLI entry or a form in the next foundation.

Consume one **request-wide** attempt latch before dependencies, callbacks or
input IO, including malformed/refused paths. Creating another instance cannot
restart it. Reentry poisons the outer attempt; clear no latch in finally. GET
issuer and POST paths cannot be combined/restarted within that lifetime. This
is request-local consumption, not cross-HTTP replay protection or cancellation.
No clone, serialization, exported continuation or retained request owner.

Start the original real monotonic root before input acquisition. Validate UTC
independently, reject regression and keep the original 20s root / 10s initial
phase ceilings; a future read phase retains its own 10s ceiling under that root.
Auth, body reads and fresh nonces do not restart clocks. A PHP deadline check is
not a hard timeout for a blocking syscall. Unsupported blocking input refuses;
web-server/SAPI request-time limits must be proved separately, never changed here.

Privately pin the original core db object, blog, prefix/tables, current positive
actor ID and original bounded logged-in cookie/session. Do not serialize or log
them. Confirm original equality around every callback-bearing phase; never
re-pin to a newly valid actor/cookie/blog after a callback. Preserve single-site,
native cache false/null, exact core classes, no switched context/drop-ins/CLI/
SHORTINIT and supported installation checks from the existing fixed guards.
No hostile same-process PHP isolation is claimed.

## Earned raw ingress, not supplied completeness

The owner privately acquires actual method, CONTENT_TYPE, QUERY_STRING, GET,
POST, FILES and method-override metadata. POST remains original WP-slashed input.
Never use REQUEST or reconstructed http_build_query(POST) as raw evidence.
Refuse missing/wrong-type/ambiguous metadata; pin it and compare fresh bounded
input after callback-bearing authorization. Any disagreement poisons the attempt.
No second body read to repair changing input.

The fixed reader opens **php://input in rb mode only**, once. It accepts no path,
stream, offset, filter or callback, does not spill to temp/storage, seek/reopen,
decode or normalize. The owner, not a caller, derives completeness privately:

1. Before open, enforce exact POST and existing <=128-byte sole form content
   type; empty query/GET/FILES; absent method override, Content-Encoding and
   Transfer-Encoding sources. No forwarded-header authorization or URL import.
   Preserve the parser's <=16384 raw / <=4096 decoded-and-slashed JSON /
   <=2048 value ceilings and exact four-key grammar.
2. Initial supported HTTP profile requires a positive canonical decimal
   Content-Length <=16384. Treat it only as a consistency condition, never an
   allocation size, a substitute for EOF or permission to accept a capped prefix.
   Missing/ambiguous/noncanonical length refuses. This is a deliberately narrower
   supported profile, not a claim that every valid HTTP request needs that header.
3. Read at most 16385 bytes total, in chunks <=1024, with at most 17 fread calls
   and checks of the original deadline before/after IO. Each request is bounded
   by the remaining 16385 allowance. Positive short reads may continue within
   that fixed budget; exhausted budget without verified completion refuses.
4. A false read, warning/exception, timeout/error, empty non-EOF read or unknown
   stream/profile state refuses without retry. A short read is not completion.
   Require clean EOF evidence under the verified input profile; EOF alone is
   insufficient because PHP documents that it may also indicate an error.
   Receiving byte 16385 refuses, including when the declared length is smaller.
5. Require final bytes <=16384 and exact declared-length agreement, clean owned
   stream close and unchanged transport metadata. Unknown close/cleanup prevents
   success. Only then privately pass complete=true and empty overrides to the
   existing pure parser. Keep original three slashed domain strings; its existing
   issuance parser owns the one unslash. Erase raw bytes after the last needed
   comparison; never expose or retain them across requests.

PHP exposes an input stream, not the original network header multiset. A collapsed
SERVER map cannot prove duplicate/conflicting-header rejection or absence of
request smuggling. A reviewed exact server/SAPI profile must prove canonical
header/framing handling, body availability, request buffering, parser limits and
bounded IO. No such profile is newly approved here. No SAPI-name check, flag,
stubbed complete marker or successful CLI/memory-stream test waives that gate.
If the necessary evidence cannot be obtained, actual ingress remains unavailable.

Normative PHP references: [input wrapper](https://www.php.net/manual/en/wrappers.php.php),
[fread semantics](https://www.php.net/manual/en/function.fread.php),
[EOF/error distinction](https://www.php.net/manual/en/function.feof.php).
The one-open/read-call/byte budgets above are this design's restrictions, not
guarantees supplied by PHP. No input operation is executed in this design.

## Genuine guard ownership and separate GET issuer

Use fixed first-party guard procedures, not event tokens, caller booleans or
injected authorization. A later scoped implementation may share demonstrably
method-independent private logic, but must not change the existing POST-only
same_request/open_window guards or their source-sealed callers. No temporary
SERVER method rewrite, global method selector or broadened legacy guard.

POST order is strict: consume/start -> bounded transport/grammar -> pin original
core/actor/cookie -> fresh genuine authentication/current manage_options ->
existing blog-bound register_closed small nonce verification -> fresh genuine
authentication/current capability and original identity/transport/time equality.
Accept only nonce results 1 or 2, never nonce-as-auth. Missing/expired/stale or
wrong-domain nonce refuses before protected capture or UUID/full-nonce creation.

Core user/session/role reads needed for auth are distinct from the protected ten
option participants. Install owned role-only/alloptions/query fences before
callback-bearing auth work; allow only the fixed role read against the pinned
options table and required user/session reads, never protected captures/writes.
Restore the original temporary role view and only owned filters/error handlers
on every exit. A callback changing context or reentering prevents outer success.
Do not silently broaden SQL allowances to make an unsupported auth profile pass.
Existing cache refresh calls are auth coherence, not a new persistent write;
they require genuine owned integration proof later and do not run now.

GET issuance has its own exact GET/current Tools screen/context checks, no body
or native/protected-state path and no reuse of POST-only helpers. Verify the
fixed current Tools page/screen from the genuine admin context, not a posted or
caller-supplied screen ID;reject mutation/actor/nonce/redirect inputs rather than
using them as context. Existing unrelated Tools rendering is unchanged.
Pin/authenticate the same original logged-in session and manage_options before calling
Registration_Request_Contract::issue_action(server_blog,register_closed).
Only then may a future fixed issuer call wp_create_nonce for that action, followed
by fresh original-session/capability/context/time checks before any value escapes.
No guest issuance, read/full mutation nonce, UUID, protected frame or durable state.
The existing four small/full mutation/read domains remain separate and unchanged.
The later UI may expose only the four fixed wire fields, without referer/submit
imports; rendering/escaping/presentation is separately reviewed when implemented.

Core cookie/nonce functions can invoke hooks. Their return values do not remove
the need for equality/reentry checks. Preserve the existing explicit unexpired
cookie policy, not core POST grace as fresh authorization. References:
[cookie validation](https://developer.wordpress.org/reference/functions/wp_validate_auth_cookie/),
[nonce creation](https://developer.wordpress.org/reference/functions/wp_create_nonce/),
[nonce security](https://developer.wordpress.org/apis/security/nonces/).

## Closed downstream boundary and errors

The next dormant owner/reader foundation ends after the guarded private request
checkpoint. It must not call protected eligibility/construction, issue UUIDs/full
nonces, acquire native sessions or call a private lifecycle via reflection.
Public disposition stays redacted unavailable; positive tests must exercise the
actual private reader/guard/issuer decisions, not add a fake public success path.
An isolated synthetic small-nonce result is not genuine WP issuance proof.

A future same-stack native composition must explicitly inherit the original
root/identity and satisfy R3-R5 before connecting the owned three strings to any
protected frame or native transaction. Do not nest a fresh 20s lifecycle root or
turn native_entry_available on. Writer release/erasure, independent fresh read
authorization, one barrier and post-close interpretation remain required. R6
active effects and R7 rollback stay separately gated.

When a real emitter is approved, keep the parent fixed no-store <=1024 diagnostic
contract:405 method,400 malformed transport/grammar,403 failed current auth/intent,
503 unsupported profile/dependency/deadline/reentry/cleanup. Cleanup uncertainty
overrides success. Current dormant owner cannot return200 merely because auth
passed; native/barrier evidence is absent. No arbitrary errors, raw data, IDs,
nonce/cookie, SQL, filenames, handles, redirect, retry or persisted continuation.
The GET private issuer's future narrowly approved nonce output is not a POST
diagnostic result or a general token endpoint. No HTTP response is implemented.

## Required non-vacuous proof and gates

| Boundary | Acceptance at the correct layer |
|---|---|
| Fixed private reader | Actual first-party branch with bounded synthetic chunk/EOF/error/close doubles: valid multi-read body succeeds; 16385th byte, declared-length mismatch, short/non-EOF, false/error/timeout, empty stall, 17-call exhaustion and unknown close refuse. Assert sole fixed path/mode, no reopen/spill and original clock. Isolated IO is not SAPI proof. |
| Original slashed handoff | Exact parsed three strings, plus/backslash/Unicode/percent values survive one domain unslash; duplicates/aliases/mixed sources and changes during auth refuse before simulated protected-read/UUID/native counters. |
| Genuine guard call protocol | Exercise the actual fixed owner with isolated core API doubles: capability and original cookie/blog/frame equality around each small nonce; missing/reordered guards and wrong domains must be assertion-killed. Never substitute the DATA event model for the owner under test. |
| GET issuance | Positive actual private branch with fixed Tools context/auth/capability/action; wrong method/screen/guest/session, post-nonce context change or reentry yields no token. No protected capture/UUID/native call. Isolated API success is not genuine authentication proof. |
| Request-wide ownership | New instances cannot retry a consumed attempt; malformed first attempt consumes; callback reentry poisons outer work; exceptions/deadlines erase input/pins and restore only owned temporary state. Independent test processes preserve real request isolation. |
| Real HTTP/core behavior | Separately confirmed Plugin Tester target and owned user/fixture scope, genuine cookies/capabilities/nonces, no storage/native composition. Prove supported server/SAPI framing and parser limits or explicitly leave profile support blocked. No current fixture approval or R1 CLI proof certifies this. |
| Future protected/native wiring | Separate R3-R5 design/proof/public registration gates and full pre-release before publication; no imported model receipt or unavailable-bit switch. |

No new tests are manufactured for this documentation-only slice. Preservation
tests are not new reader/auth proofs. Do not claim Database Review, physical
transaction proof, installed HTTP support or whole-plugin readiness from them.

## Source-scoped review and publication record

Canonical Feature Light:Other/design risk, five docs (four dashboard,one uploader
plan),plus direct sources above. Four design hazards resolved:caller-supplied
completeness;short-read/EOF confusion;GET/POST method and original pin ownership;
request-wide latch/deadline reset. Zero executable fixes or remaining decisions
inside this design;actual supported-profile approval and R3-R7 remain gates.
Bloat/Structure unnecessary:no PHP/JS/CSS delta. UI/UX unnecessary:no rendered
surface. No formal DS3 review/Database/physical or full pre-release completion.

Final canonical focused Feature Security after document and preservation QA:
input provenance/bounds,identity/nonce order,callback poisoning,role-only fence,
cleanup/output and disabled wiring reviewed. No confirmed scoped security defect.
Future regressions are the layer-specific acceptance matrix,not claimed coverage.
Exact local validation and pushed-head CI evidence live in toolkit's new scoped
record;historical whole-plugin release-readiness rows remain unchanged.

Executed preservation QA: uploader isolated PHP17/1037 with no skips/failures,
WPCS250 pass (existing PHPCompatibility ruleset deprecation notice); dashboard
PHP299/1423 with two existing Windows Sodium skips,WPCS138/structure/build pass.
These are preservation checks,not new reader,HTTP or genuine authentication proof.
No new test,dependency,version,asset,loader,hook or source-seal change.

Companion uploader canonical source-only OptionB before its plan edit:12134 files
from the clean excluded baseline;alynt-drime-backups-uploader-20261023-132115.zip
and matching.txt under toolkit-snapshots/alynt-drime-backups-uploader;SHA256
752C5E1D4ABB8F2B457D867ED41A59BF8CC093081ED39B2D291F6F01E5B9D5F4.
No site/database backup,checkpoint branch or restore. Read-only OptionD closeout
and Markdown/source-reference checks recorded in toolkit evidence.

Implementation-plan progress:R2 fixed ownership/raw-reader integration decisions
are selected;genuine source/HTTP/auth proof is not complete. Production NO-GO;
R3-R7 remain gated,not waived by this design or the historical R1 local proof.

Recommended next gated slice:local-only V2.3 Fixed Closed-Entry Owner and Bounded
Reader Safety Foundation (R2),isolated API/IO tests only. Implement actual fixed
private acquisition/guard/issuer branches without production injection ports;
no real authentication,cache/storage changes,WordPress/native execution,public
hook/loader,wiring,grants,activation or rollback. Then obtain separately approved
genuine WordPress/server-profile evidence before considering actual HTTP support.

## Path-only navigation note — 2026-10-26

The uploader includes/class-admin-page.php citation above is historical. Its
current path is includes/admin/class-admin-page.php; see the
[organization map and proof limits](REPOSITORY_ORGANIZATION_MIGRATION.md).
This does not refer to dashboard admin, renew this design's acceptance, reopen
deferred V2.3 or authorize actual HTTP/native/wiring work. Original text is retained.
