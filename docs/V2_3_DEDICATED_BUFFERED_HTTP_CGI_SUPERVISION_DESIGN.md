# V2.3 Dedicated Buffered HTTP/CGI Supervision and Pre-Root Resource Design (R2)

2026-10-23. Autonomous batch task4/8. User requested the remaining five worthwhile
safe-local tasks;this selects a repository-only experimental candidate and its
isolated support,not permission to launch it. Excluded coherent bases:dashboard
0555bdefccbc658b1867556592ef97452bb76972,uploader
5f12f98630cc8cdf55aab77fa08bd3c6eaaf0af1,toolkit
0bce101fec200b7ccbb8761c4d495a2172ef5f85. Versions0.1.77/0.5.25 unchanged.

Parents:[buffered HTTP design](V2_3_BUFFERED_INPUT_GENUINE_HTTP_INTEGRATION_DESIGN.md),
[feasibility review](V2_3_BUFFERED_INPUT_PROFILE_FEASIBILITY_REVIEW.md);uploader
owns docs/V2_3_ISOLATED_CGI_TERMINATION_OWNED_PROOF.md. Existing ordinary LocalWP
and production profiles remain unqualified. No site inspection,HTTP/SQL request,
WordPress authentication/bootstrap,fixture/configuration write,native experiment,
service restart,permission/dependency change,package/release/tag/deploy in this task.
entry_available=false;production loader/ingress/grants/activation/rollback untouched.

## Decision and non-goals

Prepare only test-support for one fixed loopback HTTP-to-CGI fixture candidate.
Use Node's standard HTTP parser in a later separately approved operator,not a new
general HTTP parser/proxy or production server. Require one warm,independently
verified disposable CGI worker/console owned before accepting the one connection.
No request queue,retry,reuse,external URL,client routing,WordPress include or plugin
registration. Existing shared LocalWP workers cannot be termination targets.

Socket-free support uses primitive snapshots,synthetic Buffers and virtual clocks.
It cannot attest server framing,physical process identity,actual timing or cleanup.
Nothing here replaces the actual fixed owner/reader with a parallel DATA admission
model or changes its original10s initial/20s root deadlines. No receipt or public
write-capable handle is produced. Candidate helpers live under uploader scripts/
tests only and are not imported by plugin runtime or the old native operator.

## Fixed experimental ingress

Future standard-parser settings must bound complete wire headers4096 bytes,
16 fields,strict parser behavior and one request/connection. The repository header
policy is a second semantic gate on an exact primitive snapshot,not a wire parser.
Accept HTTP/1.1 only,GET/POST /probe.php,Host owned.invalid,Connection close,
one canonical Content-Length0..16384;POST requires exact form content type,GET
requires0 and no content type. Reject duplicate fields(case-insensitive),unknown
fields,controls/folding,query/absolute/encoded target aliases,TE,Trailer,Expect,
Upgrade,compression,Cookie/Authorization and all other methods. No ambiguous
length reconciliation,chunked decoding,header joining or URL/body normalization.
This intentionally narrow synthetic profile is not genuine Tools/admin-post support.

Node's rawHeaders preserves individual fields;merged headers cannot prove absence
of duplicates. message.complete is a parser fact needed after end,not a worker-exit
or authentication fact. A later operator must use actual parser events,not client
flags. [Node HTTP API](https://nodejs.org/docs/latest-v24.x/api/http.html)
Strictly refusing TE and duplicate CL avoids alternate length interpretation;
incomplete messages cannot become complete merely because their socket closed.
[HTTP/1.1 framing rules](https://www.rfc-editor.org/rfc/rfc9112.html#section-6.3)
These settings describe future integration obligations,not installed configuration.

Body collection:one bounded allocation<=16KiB,nonempty byte chunks copied without
decoding,linear total copying,no chunk list/spill/seek/reopen. EOF/end requires
exact declared length,complete=true,aborted=false,error=false. Error,extra bytes,
late/reentrant/repeated consumption or malformed facts permanently poison and
erase the buffer. A second request must never obtain a second CGI worker;close
the connection and cancel any unfinished fixture work. Do not claim that a later
pipelined request retroactively undoes an already valid first request.

## One original epoch, not renewed per-phase timeouts

E0 is the accepted-connection monotonic instant after warm-worker ownership is
established. Startup/preflight before E0 is separately bounded and is not silently
charged as already qualified HTTP work. Proposed strict absolute cutoffs:

| Phase | Latest allowed time relative to E0 |
|---|---|
| Headers accepted |strictly before1000ms|
| Exact complete body |strictly before2000ms|
| CGI handoff/work allowed |strictly before4000ms|
| All owned cleanup proven |strictly before10000ms|

4000ms reserves6000ms for fresh ownership inspection,child join,console/listener
absence inspection and client closure. All nested waits use the lesser of their
fixed cap and remaining original budget;zero budget refuses before an operation.
Clock regression,nonfinite/negative time or phase regression poisons the attempt;
elapsed offsets never reset E0. Cleanup remains possible after a work refusal but
never revives eligibility or extends the final cutoff. Future20s native-root work
is not authorized;there is no protected/native phase in this fixture candidate.

Current CGI helper's two5s inspections/2s join/1s client-close caps cannot simply
be added after a4s work timeout and called a10s guarantee. Future operational
integration must earn bounded remaining-budget inspections/joins with unchanged
identity guards,or reject this candidate. A timer checked after a blocking call,
client timeout,PHP fatal,server inactivity timeout or printed success is not
worker settlement. Event-loop/OS suspension and failed cleanup remain explicit
failure,not a hard-real-time claim or permission to kill an unverified process.

## Remaining safe-local implementation queue

5. Implement original-epoch/remaining-budget test-support with boundary,regression
   and refusal/cleanup virtual-clock tests. It must never own an actual timer/process.
6. Implement fixed header semantic policy with raw duplicate/control/type/length
   negatives and valid GET/POST positives;no HTTP listener/parser implementation.
7. Implement bounded body collector with byte conservation,exact EOF/failure,
   mutation isolation,one-shot erasure and deadline tests using memory only.
8. Compose the real policies/body collector with the existing FastCGI builder,
   prove original-budget/no-partial-forward/erasure/order through isolated tests,
   then source-scoped error/edge/adversarial review and final focused security.
Each is independently validated,committed,pushed and exact-head CI verified. They
are prerequisite support for one future operational proof,not five product gates
or a substitute for that proof. Do not launch an operator to reach the numeric target.

## Acceptance and operational gate

Permanent positives must reach complete synthetic handoff;always-unavailable or
empty-body implementations must fail. Guard-removal controls must fail assertions
after successful module import. Preserve raw octets/declared length and original
clock across all components;no protected data/native handle in an exported result.
CI stays socket-free;no native experiment,HTTP listener,WordPress or storage proof.

After these five tasks,the next recommended gated slice is **isolated disposable
fixed HTTP-to-CGI supervision no-fault/resource-limit and owned cleanup proof
(R2)**. Separately confirm exact owned configs/listeners/processes/files and cleanup,
preserving LocalWP/application state. Prove no-fault/refusal before controlled
slow/incomplete/disconnect cases. Actual wire/parser limits,original total epoch,
every worker/console/socket/listener/file and existing-service preservation must
be established. Unknown ownership or overrun is Blocked,no blind retry. Genuine
WordPress HTTP/auth/bootstrap/minimum-version and R3-R7 remain later gates.

## Source-scoped review

Canonical Feature Light:3 docs,new design plus two owning plan entries;Other/design.
Issues found1,auto-fixed1:explicitly separate old post-hoc CGI experiment accounting
from the proposed whole-candidate epoch and bounded cleanup reserve. Manual0.
No executable,public contract or dependency change;Bloat/Structure and UI/UX N/A.
Final focused Feature Security after document/preservation QA:fixed destination,
unknown-header rejection,no secrets,original budgets,ownership/refusal and disabled
wiring reviewed;no confirmed scoped security defect. This is not formal DS3,
Database Review or whole-plugin/FULL_PRE_RELEASE completion. Publication/actual
QA counts and links belong in the new toolkit source-scoped batch record.
