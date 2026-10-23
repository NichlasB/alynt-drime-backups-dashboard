# V2.3 Buffered Input Profile and Genuine Closed-Entry HTTP Integration Design (R2)

Gate B read-only analysis successor (2026-10-23):[profile feasibility review](V2_3_BUFFERED_INPUT_PROFILE_FEASIBILITY_REVIEW.md).
Confirmed config/process discovery and upstream timer semantics reviewed;no
HTTP/SQL/fixture/configuration mutation. Qualification remains Blocked on concrete
isolated disposable CGI-profile bounded termination/owned cleanup experiments,
not approval for more read-only analysis. Current service/application state and
entry_available=false unchanged;no production/native wiring.

Gate A successor (2026-10-23):uploader owns
docs/V2_3_BUFFERED_CLOSED_ENTRY_READER_FOUNDATION.md. Strict buffered candidate in
the existing private reader with isolated regressions;entry_available=false and
production/native wiring unchanged. Historical design below remains design-only.
Next meaningful gate B:Plugin Tester local-only Buffered Input Profile Qualification
Feasibility and Owned Cleanup Proof (R2),hard IO/worker settlement feasibility
first,not more benign probes or source churn. Exact operational scope separately
approved;no supported profile or positive genuine HTTP owner root claimed.

2026-10-23. Approved **design-only**, repository documentation. No executable,
test, server/PHP configuration, WordPress, SQL, fixture, HTTP request or native
experiment change/run. Storage writes, production ingress/loader/hooks, native
execution, grants, activation and rollback stay disabled. Dashboard0.1.77 and
uploader0.5.25 unchanged; no package/release/tag/deploy. Product gate R2 does not
raise agent permissions. This is not a supported-profile installation decision.

Explicit excluded coherent review bases: dashboard
f7a629623424b0ea673052f84541425a6afdb432, uploader
d47a689f7987759e49468456584b05992cb13915, toolkit
48655e3ea671f133eb98a21f6c9c2f23ba8fff68. Scope: this design, two implementation
plans and two direct dashboard design/readiness pointers; direct uploader owner,
reader, transport, scope, GET/core guards, ingress and proof dependencies read
only. Historical proof/release-readiness records are not refreshed.

Parents: [fixed owner/raw-reader design](V2_3_FIXED_CLOSED_ENTRY_OWNER_RAW_READER_DESIGN.md),
[request policy](V2_3_REAL_AUTHENTICATED_CLOSED_ENTRY_REQUEST_POLICY_DESIGN.md),
[R1-R7 readiness](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md).
Uploader owns docs/V2_3_CLOSED_ENTRY_GENUINE_AUTH_INPUT_PROFILE_OWNED_PROOF.md.
Its17 genuine-core helper cases/8963 checks and cleanup are historical evidence,
not integrated HTTP owner proof. Its four input-only HTTP samples establish
seekable=true on that measured profile, not clean framing/hard IO guarantees.

## Decisions and current source boundary

Select a **buffered, forward-only-use candidate**, not a nonseekable-only rule.
A stream's capability to seek is different from the reader performing a seek.
Changing seekable=false to true alone cannot admit a server profile. Current
includes/trait-registration-closed-entry-reader.php stays unchanged and refuses
the observed profile; entry_available() in the fixed request owner stays false.
No current profile is certified, including LocalWP cgi-fcgi.

Separate three propositions: (1) bounded reader algorithm accepts complete
synthetic input; (2) a particular deployment supplies trustworthy bounded input;
(3) the unchanged-source owner performs genuine HTTP/core authorization. None
implies either of the others. A pure DATA receipt, SAPI string, caller boolean,
environment enable switch or test supervisor success cannot authorize ingress.

## Buffered candidate: precise reader contract

Future implementation belongs in the existing private reader, not a parallel
parser/controller or generic stream-provider framework. It remains dormant:

- Fixed php://input, rb, one open; no seek/rewind/reopen, stream filter, caller
  path/handle, temp spill, read-all convenience API or transport reconstruction.
  No fseek/rewind probe to discover support. PHP may internally buffer/spill;
  application no-spill is not a claim about server/SAPI storage behavior.
- Keep <=1024 bytes/read, <=17 reads, <=16385 total, <=16384 accepted bytes,
  original10s initial/20s root checks, exact canonical declared length, clean
  EOF/error/owned close requirements and unchanged transport snapshots. Byte
  16385, short non-EOF, exhausted calls, empty stall, false/warning/exception,
  timeout, length mismatch, metadata drift or unknown cleanup still refuses.
- For the buffered candidate only, require exact PHP/Input/rb/php://input,
  blocked=true, timed_out=false, unread_bytes=0, boolean EOF matching feof,
  **seekable=true** consistently before/after each read. Missing/wrong-type or
  changed capability refuses. Do not accept either boolean indiscriminately;
  a future nonseekable candidate requires its own reviewed profile evidence.
- These facts are necessary consistency checks, not provenance or proof that
  EOF is error-free. Actual completion still requires independently qualified
  server/SAPI semantics. Keep raw bytes private until the existing ingress
  comparison and discard afterward. Preserve original three WP-slashed strings
  and existing one domain unslash; never regenerate raw bytes from POST.

PHP describes php://input as raw read-only input and allows repeated opens.
One-open/forward-only use is our stricter policy, not a PHP guarantee. [PHP input wrapper](https://www.php.net/manual/en/wrappers.php.php)

## Profile qualification: required evidence, not a runtime guess

Qualification is a separately approved operational task. Bind evidence to exact
server/proxy chain, PHP build/SAPI, effective configuration including inherited
locations/auto-prepend, route/body parser and immutable source revision. Record
non-secret identity/digests privately or redacted; configuration drift invalidates
qualification. Deployment assurance is operational trust, not isolation against
hostile same-process PHP. No public profile argument, header or diagnostic receipt.

| Obligation | Required evidence / refusal boundary |
|---|---|
| Network framing | Exact front-door chain rejects conflicting/duplicate length, length plus transfer encoding, unsupported encoding/override aliases and ambiguous parsing before this route. Collapsed SERVER data cannot prove original headers. Document any duplicate normalization policy explicitly; do not infer it from valid requests. |
| Complete bounded body | Request buffering before PHP consumption, fixed body limit, exact declared/received bytes and truncation/error behavior across proxy, FastCGI and SAPI. Preserve missing/noncanonical length refusal and raw-to-WP-POST equality. No multipart, chunked/encoded fallback or normalized substitute. |
| Pre-root resources | Header/body byte, connection/concurrency and absolute receive-lifetime ceilings enforced outside PHP. Bound any SAPI parsing/spill before the owner starts; a16KiB owner limit cannot undo prior buffering of a1000M upload. |
| Owner IO | Establish a bounded one-open/read/close operation total inside the original10s initial/20s root, including failure/cleanup. A timer checked around a blocking syscall cannot interrupt it. Benchmark samples, client timeout, EOF, max_execution_time and a proxy closing its socket do not establish worker settlement or hard IO limits. |
| Failure settlement | External enforcement must settle the owned request worker within its original deadline, not merely disconnect the client. No protected/native stage can start on uncertain input/cleanup. If the chosen platform cannot establish this, reject that profile rather than relaxing the budget. |
| GET / POST parity | Both exact routes use the qualified configuration and parser; test GET header representation and POST form slashing. An absent header and an empty FastCGI parameter are not silently equated. |

nginx request buffering reads the client body before forwarding, but that alone
does not prove PHP's complete buffered-read contract or a hard execution bound.
Its FastCGI read/send timeouts are inactivity limits, not total request/worker
deadlines. [nginx FastCGI buffering/timeouts](https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_request_buffering)
The body timeout likewise measures gaps between reads, not total upload duration.
[nginx client body timeout](https://nginx.org/en/docs/http/ngx_http_core_module.html#client_body_timeout)

**Selected fallback is refusal, not a new proxy/watchdog implementation.** Existing
LocalWP limits in the owning proof do not meet these evidence obligations. No
server tuning, slow-client/framing experiment, listener/process or fixture work
is authorized here. If operational feasibility requires an owned isolated server
or changed site configuration, request that exact new scope before proceeding;
never change the existing Plugin Tester installation implicitly.

## Genuine HTTP composition without a test-only success shortcut

Retain the single actual fixed owner's GET/POST decision code. Isolated API/IO
tests may use their explicitly labeled doubles/source instrumentation, but they
are not genuine HTTP evidence. The later genuine proof must load the exact
reviewed unmodified source: no eval/rewrite, unavailable-bit/reflection toggle,
injected actor/cookie/capability/nonce/profile, manually assigned WP_Screen,
temporary method rewrite, copied controller or fake public success result.

Current entry_available=false makes a positive actual owner HTTP root impossible.
Do not hide this by calling helpers and relabeling the result. Only **after**
profile qualification may a separately approved source integration replace that
placeholder with a fixed private profile-admission procedure. Its review must
specify observable invariants versus externally trusted deployment facts; no
query/header/constant/argument can turn it on. No such procedure is implemented
or approved for installation now. Default unsupported/refusal remains mandatory.

Future owned proof bootstrap may install temporary routes only in a separately
confirmed isolated fixture application, never the existing site's plugin loader,
Tools page, user roles or options. It must drive genuine core routing/authentication
against owned shadow tables with all incidental writes confined there. Same
canonical owner file and exact source hashes in isolated and genuine tests;
bootstrap owns transport registration only, not authorization. No native handoff.

Account for bootstrap **before** the owner: its role-only/alloptions fence is not
retroactive. Core can load/cache autoloaded options, with an all-options fallback;
therefore a Tools HTTP success on sanitized shadow tables cannot certify absence
of protected reads in an ordinary production bootstrap. Trace bootstrap and
owner phases separately, including caches, against explicit protected participant
names in the later owned proof. Any protected pre-auth read blocks that claim;
do not redefine it as harmless merely because it occurred before the owner.
[WordPress alloptions behavior](https://developer.wordpress.org/reference/functions/wp_load_alloptions/)
The documented pre_wp_load_alloptions short circuit starts in WP6.2, so it cannot
by itself establish declared WP6.0 compatibility. Keep minimum-version genuine
bootstrap containment and any earlier fence placement as a separate unresolved
production-readiness obligation; no autoload/schema/configuration change here.

1. Genuine Tools GET traverses core admin menu/screen routing for the fixed
   uploader slug, not set_current_screen supplied by the harness. Exact GET
   query/page, empty body/maps, current genuine screen and manage_options checked
   by the owner before small nonce issuance and again after callback-bearing
   work. No REST/AJAX/CLI/nopriv issuer. No success claim if core bootstrap
   requires unapproved application writes or a broadened SQL fence.
2. POST traverses genuine admin-post for only the reserved authenticated action,
   empty query, exact four-key bounded form. admin-post dispatch is not a POST
   validator: the actual owner must reject other methods and all aliases. Its
   hook also does not substitute for fresh capability/session/nonce guards.
   [WordPress authenticated admin-post hook](https://developer.wordpress.org/reference/hooks/admin_post_action/)
3. Across separate GET/POST requests use only owned fixture users/sessions and
   consistent private fixture salts through an owned worker/bootstrap lifetime.
   Keep secrets in private memory/owned process channels, not URL, CLI arguments,
   checked-in files, diagnostic output or browser's existing cookie jar. Fixture
   data may persist only for the bounded approved proof and exact cleanup.
   GET success does not carry authorization to POST: original identity is pinned
   afresh per request, then never repinned within it. Nonce is not deduplication.
4. POST order remains consume/start -> real bounded transport/body -> pin ->
   owned role-only/auth-read fences -> genuine auth/current capability -> exact
   blog/register_closed small nonce (1 or2 only) -> genuine fresh auth/capability
   and original transport/identity/time -> private checkpoint -> owned cleanup.
   No protected option capture, UUID/full nonce, storage handle/native session
   or lifecycle call. Existing POST-only guards and nonce domains stay separate.
5. Future proof records positive private checkpoint/GET issuance only inside the
   owned supervisor, with synthetic form comparison; public POST disposition
   remains redacted unavailable, never200 just because authorization passed.
   No receipt/capability/continuation escapes. Parent fixed no-store <=1024
   diagnostics remain405/400/403/503 as applicable; no new emitter/UI now.

## Acceptance and honest proof layers

| Layer | Non-vacuous required acceptance |
|---|---|
| Isolated buffered reader | Positive small/multi-read/16384 with exact slashed handoff;16385,length mismatch,false/warning/stall,EOF error,metadata drift,clock/reentry and close failure refuse. Spy one exact open,zero seek/reopen/spill. Counterfactual metadata/bounds/cleanup removals must cause assertion failures, not parse errors. |
| Isolated fixed owner | Preserve positive GET/POST call order; remove first/second auth, current capability, original pin, nonce domain, transport equality or cleanup guard and require assertion-killed controls. No protected reads/UUID/native calls before or after authorization; absence of a public write-capable handle asserted. |
| Server/SAPI qualification | Actual framing/limits/buffering/error/worker-settlement evidence on the exact chain; benign complete samples alone are insufficient. Resource/adversarial cases require bounded approved operational scope, not CI sockets or unbounded load. |
| Genuine HTTP/core owner | Real Tools GET and admin-post POST, genuine core origin/cookies/sessions/capabilities/nonces, valid late-boundary positive controls plus revoked/changed actor/session/role/context, stale/wrong-domain/malformed input, double attempt/reentry. Trace bootstrap/protected reads separately. Unexpected exceptions are not successful qualified refusals. Source unchanged, no policy bypass; raw profile and route support independently earned. |
| Owned cleanup | Before/after application inventory/checksums, exact owned row/file/user/session/listener/process accounting and previewed cleanup; no unknown workers or fixtures. Request failure cannot be success merely because supervisor exits0. Missing evidence leaves proof Blocked. |

Preservation suites are not new profile/authentication or physical transaction
proof. No Database Review, minimum-version genuine runtime matrix, whole-plugin
security/readiness or full pre-release claim. No new tests manufactured for this
documentation slice; acceptance above is future work, not existing coverage.

## Ordered gates and exact next slice

A. **Local-only Buffered Closed-Entry Reader and Profile-Admission Boundary Safety
Foundation (R2)**: implement the candidate metadata/forward-use invariant in the
existing private reader and isolated API/IO regressions/counterfactual controls.
Keep entry_available literally false and production loading unchanged. No actual
auth,storage,network/WordPress/native/configuration work; do not create a DATA
model as a substitute or add an operational profile selector. This is the next
recommended approval, not automatically included in this design-only approval.

B. Separately approve exact owned input-profile feasibility/qualification scope
on a resolved target, before actual HTTP owner admission. If hard IO/worker
settlement cannot be proved without new configuration/process scope, stop and
name that boundary. Do not repeatedly run unchanged benign measurements.

C. Only with B satisfied, separately approve fixed profile-admission source
integration and owned genuine HTTP routing/authentication/cleanup proof. Neither
A nor historical helper authentication permits turning entry_available on.

D. R3-R5 protected/native original-root composition, release/erasure/fresh-read
authorization/barrier and later R6 effects/R7 rollback remain distinct gates.
Production registration/deployment requires explicit approval and full canonical
pre-release of a coherent RC; no client/dashboard remote mutation is introduced.

Implementation-plan progress: R2 buffered-profile and genuine HTTP ownership
decisions advance; operational profile qualification and actual HTTP owner roots
remain Blocked, production NO-GO. No further implementation in this design task.

## Source-scoped review / validation record

Canonical FEATURE_LIGHT_REVIEW_PROMPT: five documentation files plus direct
sources; Other/design risk. Reader capability versus operation, upstream versus
root deadlines, unsupported gate versus genuine root testing, and CLI versus
Tools HTTP context are explicitly separated. Zero executable fixes; no remaining
in-scope document decision. Review added the pre-owner bootstrap/alloptions and
WP6.0 compatibility obligation instead of attributing retroactive protection to
owner fences. That production-readiness gap stays open, not a claimed fix.
Bloat/Structure N/A:zero PHP/JS/CSS delta. UI/UX N/A:
no implemented form/rendering/emitter or user-facing copy change. No formal DS3
or whole-plugin row refreshed; FULL_PRE_RELEASE remains a later RC requirement.

Final canonical FEATURE_SECURITY_REVIEW follows preservation QA/diff review:
profile trust/framing, fixed method/raw bounds, auth/capability/nonce order,
original pin/deadline, private handles, cleanup and disabled wiring inspected.
No confirmed new scoped security defect; future runtime acceptance is explicitly
unproved, not waived. No new defect regression handoff; acceptance matrix names
future test layers. Actual QA/CI counts and publication links belong in toolkit's
new source-scoped closeout, not as invented evidence in this design.

Source-only canonical OptionB before uploader plan edit:12193 files at its clean
excluded baseline; toolkit-snapshots/alynt-drime-backups-uploader/
alynt-drime-backups-uploader-20261023-210954.zip and matching.txt, SHA256
7B1F70865E64B5610DA749EF8A89BB1F10B81B8D50371A853BA27005851A0AFF.
No site/database backup, checkpoint branch or rollback. Source OptionD is preview
only; no restore apply. Exact-head CI verifies publication, not HTTP support.

Recommended next gated slice: **local-only V2.3 Buffered Closed-Entry Reader and
Profile-Admission Boundary Safety Foundation (R2)**, isolated API/IO tests only,
keeping actual authentication,storage,native execution, production ingress and
wiring disabled and entry_available=false.
