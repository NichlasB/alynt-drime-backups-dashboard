# V2.3 Buffered Input Profile Qualification Feasibility Review (R2)

2026-10-23. Safe-local batch task2: complete the read-only feasibility analysis
before requesting any operational changes. User separately confirmed plugin-tester,
local-only,LocalWP “Plugin Tester”,http://plugin-tester.local,
C:/Users/Captain/Local Sites/plugin-tester/app/public for read-only inspection.
No SQL,HTTP probes,WordPress/bootstrap/authentication,fixture writes,configuration
changes,restarts,termination experiment,native feature execution or live work.
Only repository documentation is changed/published. Versions0.1.77/0.5.25,
entry_available=false,production ingress/wiring/grants/activation/rollback unchanged.

Excluded coherent review bases:dashboard3a88efa8a1d3fadfcbd857b27d5378977576dc74,
uploaderc10cc8ae3b0408c8f224fcaf3b84dc8ab53f5ef2,
toolkit3992067a93272ade2dd59eebfaffdcb4303980f6.
Parents:[buffered input/HTTP design](V2_3_BUFFERED_INPUT_GENUINE_HTTP_INTEGRATION_DESIGN.md),
[readiness](V2_3_CLOSED_ADMISSION_OBSERVATION_PRODUCTION_WIRING_READINESS_DESIGN.md).
Uploader owns docs/V2_3_BUFFERED_CLOSED_ENTRY_READER_FOUNDATION.md.

## Result: analysis complete, operational qualification blocked

The existing profile has **not** been qualified. No additional benign body
sample or socket-free model can close its worker-settlement/framing/resource
obligations. This is not a finding that buffered HTTP is impossible or should
never be implemented. It identifies the concrete experiment/configuration
boundary needed next; read-only analysis itself did not need approval to proceed
through the repository workflow. Site target confirmation was obtained under
the user's Site Operations instructions,not treated as approval for mutations.

## Fresh non-secret discovery and inspection

Exact LocalWP sites.json record resolves Plugin Tester to the same local root,
nginx1.26.1 HTTP10005,PHP8.5.1 CGI10002/10003,database10004. Novamira HQ guide and
complete inventory available;exact http://plugin-tester.local profile present.
No WordPress Ability,doctor/site HTTP request,reconnect or credential change.
Filesystem/process inspection is the direct source for generated server config,
not a reason to invoke a broader WordPress API. Registry stays local/untracked.

Generated configuration under Local/run/XPa4sCxmk/conf inspected read-only:

| Observation | Consequence |
|---|---|
| nginx global client_max_body_size1000M | The private reader's16KiB cap does not bound earlier network/server buffering or SAPI parsing. |
| Global fastcgi_read_timeout1800s; PHP location overrides1200s | Neither establishes a10s initial/20s owner worker-lifetime bound. Effective location precedence matters,not just the first matching directive. |
| No explicit request-buffering/body/header timeout or connection/rate limits in inspected .conf include tree | Do not claim they are disabled:upstream defaults and an outer router still matter. No absolute whole-request/worker bound is established by absence or defaults. |
| PHP max_execution_time1200,max_input_time600,post_max_size1000M,max_input_vars4000,memory_limit256M,default_socket_timeout60 | Broad limits are not the required bound. Socket timeout is not a blanket interrupt contract for php://input. |
| output_buffering4096,request_orderGP,Local auto-prepend configured | Configuration identity matters;no auto-prepend/bootstrap or response behavior is executed/proved here. |
| Two php-cgi.exe listeners,one nginx.exe listener;database listener present | Ports establish running services only,not input/authentication or transaction proof. No connection to the database. |
| Bundled php-cgi -n -v reports8.5.1,cgi-fcgi,NTS,Visual C++2022 x64;nginx -v reports1.26.1 | Binary identity,not active request ini values or genuine WP/minimum-version acceptance. These version-only commands load no WordPress. |
| Generated FPM configuration also exists | A template is not an active FPM worker. The listeners are PHP-CGI;do not attribute FPM's termination directive to them. |

Read-only process metadata confirms expected bundled CGI binaries and explicit
configuration arguments naming the resolved configuration directory. Initial
literal backslash/filename matching returned false;normalizing slash direction
and checking the actual directory resolves it. No command line or environment
contents are published. This is not a request-time phpinfo/loaded-ini attestation:
scanned ini/environment/in-memory reload state and the outer Local router are
not fully established. Do not label generated file values as certified effective
end-to-end behavior. No service/configuration was changed to obtain the result.

## Timer semantics that the next proof must distinguish

1. The actual owner checks hrtime/time around IO and refuses when the original
   initial window expires. That is a decision bound after control returns,not
   preemption of a blocked fopen/fread/fclose. The existing probe's3s client
   request.destroy timeout likewise proves neither CGI exit nor owned cleanup.
2. PHP timeout behavior is build/platform-specific. Windows measures real time;
   current PHP documentation also describes elapsed per-thread timers for builds
   with zend-max-execution-timers (default for ZTS since8.3),unlike ordinary
   CPU-time accounting. Therefore “PHP never counts blocking IO” is too broad.
   [PHP runtime timer configuration](https://www.php.net/manual/en/info.configuration.php#ini.max-execution-time)
3. That nuance does not certify the observed NTS CGI profile or change1200s into
   the original10s bound. set_time_limit restarts its counter;installing it as a
   retry/per-phase reset would violate original-root accounting. A fatal timeout
   is not proof that normal finally/role/filter/stream cleanup completed.
   [PHP set_time_limit](https://www.php.net/manual/en/function.set-time-limit.php)
4. max_input_time concerns input parsing before execution. It is separate from
   body reception before PHP and from the later owner's clock;neither can be
   silently subtracted/restarted to manufacture full-chain admission.
5. nginx request buffering can finish reading the body before forwarding;its
   read/send timeouts concern inactivity between operations,not whole request
   transmission or process settlement. They do not identify or authorize killing
   either existing shared CGI worker. [nginx FastCGI](https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html#fastcgi_request_buffering)
6. FPM request_terminate_timeout is a different worker-kill mechanism. Its
   existence is useful architecture evidence,not a setting applicable to the
   confirmed CGI listeners or permission to migrate/change services.
   [PHP-FPM configuration](https://www.php.net/manual/en/install.fpm.configuration.php)

The profile design's numerical budgets and default refusal remain unchanged.
No claimed universal hard-real-time OS guarantee;later evidence must state timer
origin,permitted scheduling/cleanup accounting and observed worker settlement.
Any change to the specified operational guarantee needs an explicit design
decision,not a looser test assertion or unavailable-bit change.

## Concrete next operational scope

Recommend an **isolated disposable CGI-profile bounded-termination and cleanup
proof**,not tuning the current Plugin Tester workers. It requires explicit
permission for owned temporary configuration/listeners/processes and controlled
timeout/termination tests. Keep the existing LocalWP configuration/services,
application data,users/options/cron and production loading untouched. No database
or WordPress is needed for this first resource-profile experiment;genuine HTTP
owner authentication/fixture storage remains a later,independent scope.

Before launching it:resolve exact owned paths/ports/process ancestry;reuse only
reviewed ownership/cleanup support;bound requests/output/lifetimes and record the
original epoch. Prove no-fault transparency and a refusal path first. Then prove
client disconnect and worker exit separately,with exact owned PID/socket/fixture
settlement. Never terminate an existing/shared CGI/nginx process to test a timer.
Unknown ownership/cleanup is a failed experiment,no blind retry or apply.

Even a successful isolated worker experiment does not qualify the ordinary
Plugin Tester or production chain. It supplies feasibility evidence for an exact
owned candidate;body framing,pre-root ceilings,configuration drift and genuine
Tools/POST/bootstrap read containment still need their correct proof layers.
No new watchdog/proxy,profile selector or automatic runtime fallback is installed.

## Source-scoped review and validation

Canonical Feature Light and final focused Feature Security:five documentation
files plus directly read owner/reader/probe/configuration evidence. No executable,
test,dependency,contract or version delta. Timer generalization and generated
versus effective-profile evidence limits clarified;no new scoped security defect.
Bloat/Structure and UI/UX N/A:no PHP/JS/CSS or implemented UI change. No Database,
physical transaction,HTTP qualification,whole-plugin readiness or full-pre-release
refresh. Historical proof and release rows remain historical. Preservation QA,
exact-head CI and source-only restore-point closeout are recorded in toolkit.

Implementation-plan progress:read-only R2 gate B feasibility analysis is complete;
operational profile qualification is Blocked on the concrete isolated configuration/
controlled-termination experiment,not another read-only planning approval.
Safe-local batch total becomes2/8 only after publication verification;do not count
unexecuted experiments or add maintenance churn to reach eight. Production NO-GO,
native execution/entry/wiring disabled,R3-R7 remain gated.
