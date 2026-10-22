# V2.3 Exact-Wait Witness Window and First-Safety-Execute Coordination Design

Socket-free successor (2026-10-22):uploader owns
docs/V2_3_SOCKET_FREE_CACHE_AWARE_WAIT_WITNESS_FOUNDATION.md. Separate v2 synthetic
grammar/credit/virtual window;31 new tests/11 assertion-killed controls. Original
native source/seal unchanged;no actual registry/stdio/cache/wait proof. R1 physical
qualification BLOCKED. Next gated private stdio/relay integration and owned
no-fault cleanup proof requires fresh exact local target/fixture confirmation.

2026-10-22. Approved DESIGN ONLY. No implementation,native experiment,WordPress
bootstrap,SQL,fixture,listener/process,storage write or production-wiring change.
Versions remain dashboard0.1.77/uploader0.5.25. Grants,activation and rollback stay
disabled. Repository documentation/publication is the only mutation in scope.

## Explicit boundary and evidence ownership

Excluded bases:dashboard8ca9216f501202a359f6c9f4bd0c34009ca9bf78;
uploader29f63b2d16da2366187f0f7acc88edbc528f31c9;toolkit
bff5b516b7673a89b310c38d3abf844a2259811e. Coherent native feature boundary remains
be42b57fff92da1ef65aa1608be29ce4766eeb1e. Review this design,three related dashboard
plan/design records and uploader plan pointer,plus direct read-only dependencies.
This is not an exact-head refresh of historical whole-plugin readiness.

Historical native evidence belongs to uploader
docs/V2_3_BARRIER_PROGRESS_DIAGNOSTICS_OWNED_PROOF.md. Frozen66-file LF seal
2ac6e5fb44961d21ff4f04996e1f011b8aaa4ed8534746e0711220713c8066f4 is unchanged.
That ONE attempt passed genuine/no-fault/selected controls,but released failed:
monitor watch_missing/helper_wait_missing;BEGIN/first safety execute/server1205,
post-root rolled_back/one read after22 guards observed. Held NOT RUN;owned cleanup
and77-checksum preservation passed. These are HISTORICAL,not rerun in this design.
1205/one attempted read is not a matching wait or a successfully fetched row.

Uploader source anchors:

- scripts/tests/uncertain-barrier-native-{control,helpers,registry,statements}.mjs
- scripts/tests/uncertain-barrier-native-{helper,monitor-body,worker}.php
- scripts/tests/{native-qualification-selected-relay,selected-native-frames}.mjs
- includes/trait-registration-observation-{native-barrier,barrier-rows}.php
- includes/trait-native-closed-begin-session.php

## Findings: source facts versus unproven historical cause

**WW1 — cache-refresh starvation risk.** The pinned MariaDB10.6.23 cache requires
more than100ms since its last read before refresh;end-read advances that timestamp.
The existing monitor sleeps20ms between repeated reads. Thus a fast empty poll
can keep an earlier snapshot stale. This is a source-backed failure mechanism,
NOT proof that it caused the previous missing witness. Primary references:
[trx0i_s.cc](https://github.com/MariaDB/server/blob/mariadb-10.6.23/storage/innobase/trx/trx0i_s.cc#L900),
functions can_cache_be_updated/trx_i_s_possibly_fetch_data_into_cache/
trx_i_s_cache_end_read;[I_S fill path](https://github.com/MariaDB/server/blob/mariadb-10.6.23/storage/innobase/handler/i_s.cc#L159).

**WW2 — polling begins before execute.** helpers.bind sends watch after blocker
READY and starts monitoring BEFORE control sends bound_barrier. The worker still
has ACK processing,guard22,native session/policy/budget/zero-DML/BEGIN checks and
the first safety prepare before execute. The monitor's2.5s window therefore
includes pre-execute time. Current records do not measure this overlap.

**WW3 — a seen command is not delivery or a wait.** progress sees execute before
target.write;only the exact upstream write callback plus matching original
registry account credit establishes local forwarding. Neither establishes server
execution,cache freshness,wait identity or successful barrier. FINISH/cleanup and
the exact native terminal remain independent obligations.

The [MariaDB wait-table contract](https://mariadb.com/docs/server/reference/system-tables/information-schema/information-schema-tables/information-schema-innodb-tables/information-schema-innodb_lock_waits-table)
relates requesting/blocking transactions and lock IDs. Preserve the current exact
requester,blocker,owned table and option_name index predicate. Do not accept1205,
elapsed sleep,READY,empty query completion or a forwarded execute as a substitute.

## Preferred design: arm once,credit once,query with a quiet gap

This is a prospective harness-only design,not an implemented fix or permission to
run it. Keep original native owner,SQL,session policy,guards,nonce domains,root
return/throw and production entry closed. No native-owner callback or PHP method
override. Do not hold or suppress a barrier request/response to manufacture a wait.

| State | Required event | Result/next state |
|---|---|---|
| Closed | Fresh fixed source/context,distinct six roles,blocker READY | Start one monitor arm;no wait query yet |
| Armed | Monitor read-only setup and fixed prepared wait query complete | One exact ARMED receipt;original2.5s deadline already running |
| Awaiting execute | Original bound_barrier ACK completes within existing1s budget | Native guards/BEGIN/read continue unchanged;monitor does not poll early |
| Execute credited | First bound safety COM_STMT_EXECUTE fully written upstream AND original matching credit consumed | Emit one source/root/attempt/role-bound trigger;not a native success receipt |
| Quiet gap | Monotonic not-before threshold reached before original deadline | Permit one fixed wait query |
| Observing | Exact matching tuple before deadline | Existing wait witness;released branch may request blocker rollback |
| No match | Query completes without tuple and time remains | Next quiet gap,never an early repoll or deadline reset |
| Refused/expired | Wrong/replayed event,clock regression,partial write,scope/shape/error/cap/close failure | Consume attempt;no release-as-success;settle owned resources independently |

### Cache-aware eligibility,not a freshness claim

Propose a fixed125ms minimum quiet gap:larger than the source's strict100ms
threshold,while REDUCING polling frequency. Measure from the later of local
execute-trigger receipt and the end of this monitor's previous wait query. First
query also observes that gap. No wait-table reads during arming or the gap.
After an empty query,update last-query-end even if data was stale;never spin.
Sleep is only scheduling,not evidence. A late wakeup/query result cannot renew
or escape the fixed deadline. Other database readers can affect the shared cache;
this protocol cannot certify their absence or force refresh. Missing witness
still fails. Do not change DB isolation/config,flush caches or inspect broad rows.

Arm deadline is fixed once at the same post-prepare stage where the old monitor
sets its2.5s deadline. It includes trigger waiting and quiet gaps. Execute credit
MUST NOT start another2.5s window. Check time before/after every query and before
publishing WAITING. A slow native query can overrun an acceptance deadline;refuse
the result and preserve independent cleanup,do not claim server cancellation.
Keep125-query cap unchanged;125ms spacing permits fewer queries,not more. No
requirement to consume all125 queries. Parent root/read/ACK and containment limits
are independently enforced;never reset,convert or compare PHP/Node monotonic epochs
as if interchangeable. No caller-supplied clock in the native path.

### Exact execute credit and no transport interference

The future source-pinned statement classifier must recognize the first safety
statement under current barrier binding,matching prepare/execute ID,owned table,
role order,sequence and no prior execution. Metadata execute,writer execute,later
row,replay,foreign role/context or uncredited/partial write cannot trigger.
The relay forwards original bytes through its existing sole write site without
waiting for monitor acknowledgement. Only AFTER that callback succeeds does the
registry account the original fresh Symbol credit and resolve a private one-shot
notification to its parent owner. Request observation alone must not resolve it.

The parent retains this notifier before native execution. No polling loop over
transport state,new transport role,arbitrary callback,operator SQL or replacement
socket/handle. The native worker is not given a trigger/provider. Notification
failure must invalidate proof eligibility,not rewrite root results or re-send
bytes. Preserve original four relayed roles/eight streams and the separate two
DIRECT helpers. Writer-only11-byte loss eligibility/accounting stays unchanged.

## Private control successor and failure semantics

The next socket-free foundation must specify canonical byte fixtures under a
distinct fixed direct-control domain/revision,not accept new messages in the old
v1 parser. Retain exact source/attempt/root/six-role context,ASCII JSON/LF,keys,
types and single-consumption. ARMED says only monitor setup is ready,not lock/wait
or permission. Execute trigger is accepted only after ARMED and only once;early,
duplicate,foreign or expired triggers poison that candidate.

Successful monitor frames:HELLO→ARMED→WAITING→CLOSED,still within existing four
messages/helper. Blocker remains HELLO→READY→RELEASED→CLOSED. Failure uses bounded
REFUSED and exit;do not fabricate CLOSED/zero-DML success after an exception.
Parent arm/execute/finish commands remain inside existing4096-byte frame and
32768-byte aggregate traffic caps. Do not raise a cap to accommodate the protocol.
Keep worker's eight-frame protocol and original first-fragment ACK clocks unchanged.

Released accepts only actual matched wait,acknowledged blocker rollback and full
original successful barrier/qualification/drains/cleanup. Held retains matched
wait through the exact native timeout/refusal suffix;do not promote it to success.
No match/late trigger/cache uncertainty remains failure even if root or cleanup
otherwise succeeds. Failure/expiry wakes the retained parent waiter and prevents
new notifications. Settle blocker transaction,monitor statement/session,all pipes,
listeners and owned process tree through existing bounded cleanup. Cleanup is not
a successful wait receipt. No DROP until owned absence/locks/checksums permit it.

Budgets unchanged:root20s/read10s,selected writer4s,ACK1s,native connect3s/read5s,
row-lock2s/metadata-lock3s,monitor2.5s/125 queries,parent waiting3s/join3.25s,
worker24s/helper28s/outer90s,4096 frames/pair,2MiB packet/8MiB queue/64MiB attempt.
No retries,new factory/session,barrier suppression,clock reset,DDL/application
write,KILL,grants,credential repair,schedule action or production wiring.

## Required next proof layers,not acceptance claimed here

First implement ONLY a socket-free DATA scheduler/credit contract with synthetic
bytes and virtual clocks. It must earn non-vacuous tests:

- Positive arm→credited safety execute→125ms gap→matching synthetic tuple;
  model absence before100ms and visibility after a sufficient idle gap. Label
  the cache model synthetic,not a reproduction of MariaDB or the old failure.
- Legacy20ms repoll and exactly100ms boundary refused;no query/trigger/deadline
  bypass.125ms/2.5s boundaries,late return,query cap,clock regression and no busy
  loop. External-reader starvation remains an explicitly modelled refusal.
- Actual source-backed first safety prepare/execute/credit identity;every wrong
  role/statement/order/credit/partial write,replay,old domain,context and cap fails.
  Always-unavailable implementation must fail positive controls. Removed required
  guard/gap/once-only/credit checks must fail assertions,not just syntax/imports.
- Preserve unchanged result/throw,nonce/guard order,production source identities,
  limits and no write-capable handles. Do not create synthetic native success.

After separate approval:fixed private stdio/relay integration and owned no-fault
proof,then ONE ordered selected-loss/contention attempt with fresh prerequisites
and independent77-table/lock/session/process/fixture audits. Reconfirm exact
Plugin Tester local-only target and owned fixture scope at that future gate.
Neither integration nor another experiment is approved by this design.

## Source-scoped review and validation boundary

Feature Light:five docs-only artifacts,Other/design risk,no executable change.
Feature scope:exact-wait scheduling/execute coordination design and plan pointers.
Files reviewed:5. Risk areas touched:Other/design only. Issues found:0 new scoped
document defects;WW1-3 record existing source risks. Issues auto-fixed:0 executable
fixes. Issues requiring manual decision:0 within this approved design.
WW1-3 are documented with evidence/limits;design preserves fail-closed ownership,
budgets and source identity. No unresolved choice is delegated to an implementer:
use one arm deadline,post-write consumed credit and fixed125ms quiet gap.
Physical feasibility is still unverified,not an outstanding design option menu.
Bloat/UI/UX unnecessary:zero changed PHP/JS/CSS or UI surface. Formal Database
Review and FULL_PRE_RELEASE are not run or claimed. Final focused Security checks
proposed context/replay/ownership/byte/cap/cleanup boundaries;no new executable
surface or confirmed scoped security defect. Future regressions specified above,
not implemented by prose. This checkpoint does not replace the next RC suite.
Regression handoff:no confirmed new security defect requires new regression
coverage;the explicitly prospective socket-free controls remain required.

Local link/scope/integrity checks and exact-head CI publication evidence are
recorded in the toolkit source-scoped checklist. Historical native/runtime results
are not fresh verification. Clean reviewed Git baselines protect this bounded
docs-only addition;no site/database backup,restore or source archive was created.

Implementation-plan progress:R1 now has a version-pinned cache/coordination design;
physical exact-wait/released qualification remains Blocked,R2-R7 NO-GO.

**Recommended next gated slice:local-only V2.3 Socket-Free Cache-Aware Wait-Witness
Scheduling and Execute-Credit Safety Foundation.** Synthetic bytes/virtual clocks
only;keep real timers,IPC,networking,WordPress,storage,native execution and wiring
disabled. Obtain that bounded implementation approval before editing the harness.
