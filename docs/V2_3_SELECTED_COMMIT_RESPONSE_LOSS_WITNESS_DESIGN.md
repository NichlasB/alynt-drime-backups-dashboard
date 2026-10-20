# V2.3 Selected COMMIT Response-Loss and Uncertainty Witness Design

2026-10-20. Approved R3 **repository-only, design-only**. No native experiment,
WordPress bootstrap/authentication/SQL, storage write, fixture/user/table, listener,
socket/stdio/process-failure run, suppression, production wiring, grants, activation,
effects/rollback, package/release/tag/deployment. Uploader0.5.25/dashboard0.1.77
unchanged. Future-tense requirements below are not permission to implement/run them.

## Ownership, boundary and evidence

Dashboard owns this design, implementation-plan entry and feasibility-parent pointer.
Starts dashboard`776a4f19bad779816bb95450a8ac44da06d8f08f`; uploader read-only at
`665ec48ba88095307e8256a549823f04c4c48e44`; toolkit starts`6b4e135`, all clean/synced.
Reuse excluded coherent review bases uploader
`f11ba9c7dfa1c2d7cb0f94b03ce2c954863e045f` and dashboard
`d222004dc73eb16320b9e203371be4207f3adbbf`: complete native-observation attempt,
corrective drain design/foundation, passing hardening proof and this successor,
not a documentation-only tail. Earlier failure qualification dependencies read
directly; no claim to review every unrelated file in those ranges.

Source-only restore point before editing: dashboard
`alynt-drime-backups-dashboard-20261020-195402.zip` and matching sidecar under
toolkit-snapshots,13173 files,
SHA256`C70A24B20CCAD0A3DC64BE6FEB3303DCBCC8A1F6873C016BF66BCE36FDFD3D1C`.
Not a site/database backup; no stash/checkpoint/restore. Master/toolkit/repository/
Site Operations and prospective Plugin Tester profile read. No site is operated
on; no target/Novamira/service/SQL check or credential access/reconnection is needed.
Future native approval must freshly resolve and confirm its exact local target.

Parents: [uncertainty feasibility](V2_3_OWNED_NATIVE_UNCERTAINTY_WITNESS_FEASIBILITY_DESIGN.md),
[native-profile/private channel](V2_3_NATIVE_PROFILE_WITNESS_PRIVATE_CONTROL_CHANNEL_DESIGN.md),
[post-COMMIT validation drain](V2_3_POST_COMMIT_VALIDATION_DRAIN_DESIGN.md).
Uploader owns `docs/V2_3_NATIVE_OBSERVATION_STDIO_HARDENING_PROOF.md`:
historical242 native checks/2 groups,141 stdio/15,266 genuine guards/11,25 API/pure
tests/full331 Node, three roles/six equal streams and77 unchanged application
checksums. None is rerun here; none proves response loss or private uncertainty DATA.

## Decision: separate owned-pair cut, never weaken passive success

Select one first candidate: **after receiving the exact selected COMMIT's complete
canonical server OK, discard that one response before any downstream write and
close only the same owned writer's two relay sockets**. No held-reply sleep,
delayed replay, fabricated OK/error/return, OS-wide drop, server restart, SQL KILL,
reconnect, retry or compensating rollback. Authentication/capture pairs are never
targets. This is a controlled response-loss/disconnect experiment, **not** proof
of the normal five-second native read timeout. API failure must arise naturally
from the real connection loss, not a PHP stub/exception injected at a query seam.

This refines the older feasibility proposal's withholding route: a timed open
quarantine is not selected for the first experiment. Existing connect3/read5,
native lock2/metadata3 and original10s acceptance policy remain unchanged. Keep the
existing conservative six-second eligibility threshold even though the selected
cut does not intentionally wait five seconds; it is not a deadline extension.

Implement only in a separately approved fixed test harness later. Existing
`openNoFaultRelay`, passive registry, v1 private channel and successful drain proof
must retain their present contracts/tests. A new loss receipt must not pass the
existing six-stream equality/forwarded-OK/drain/QUIT success predicate by ignoring
its failed assertions. No production feature flag, endpoint, provider, hook or
request-supplied fault selector, SQL/target/handle import or runtime dependency.

## Preserve source truth and distinguish evidence layers

| Actual unchanged source | Consequence for this candidate |
|---|---|
| `trait-registration-admission-transaction.php` marks `commit_sent`, calls literal `COMMIT AND NO CHAIN NO RELEASE`, records acknowledgement only on a bool return, then checks `session(false)` | Select this command once, never a prepared payload/row substring. A real thrown query leaves raw/ack unknown, not a definite server rejection. |
| `trait-registration-native-outcome-session.php::finish()` rolls back only `transaction_owned`; `commit_sent` is poisoned and writer slots detached/closed | Never force rollback or manufacture close success to make the loss test green. Local close is not independent server-session absence. |
| `trait-registration-admission-root.php` narrow native failure bridge cleans/qualifies closed, returns false, runs no fresh failure guards, then erases origin/capture | Expect eight pre-failure genuine guard verifications, no guards9/10, original pin/root,40 protected projections/one DML attempt. These counts alone do not prove native COMMIT or private qualification. |
| Sealed DATA/history treats unacknowledged sent DML/COMMIT as uncertain; final root erases its private result | Source inference only. Final spent=true or returned=false does not prove qualification invocation, actual private classified result, current acceptance or diagnostic authority. |

Keep external server-OK witness, downstream-byte accounting, client API refusal,
independent stored-proposal audit and owned cleanup as **separate conjuncts**.
Never pass relay/audit receipts back into the runtime qualifier or create a new
native outcome DTO/provider merely to expose the private result. The first proof
may establish server acknowledgement plus client refusal, not real private failure-
qualification DATA. The latter remains a distinct future provenance decision.

MariaDB describes OK as command completion, not proof that PHP received it or a
power-loss durability certificate. Only the reviewed exclusive-writer/source and
trusted local-server assumptions associate this exact response with this COMMIT.
[MariaDB OK/status specification](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/ok_packet).
The PHP API separately specifies result versus connection timeout; neither means
server cancellation. [mysqli options](https://www.php.net/manual/en/mysqli.options.php).

## Eligibility, irreversible cut and receipt states

Use new parent-owned attempt/root/pair/frame identities, exact three authenticated
native profiles and the existing genuine callback7/8 pre-BEGIN pin checks. Binding
must still have capture closed, auth/writer alive,20 protected reads/zero writes,
at least6s original remaining and at most1s own-clock rendezvous. A BOUND token
is not permission and does not establish that the following nonce guard succeeded.
No new callbacks/control roundtrips while row locks are held.

| Proposed state | Required next event / disposition |
|---|---|
| Bound candidate | Normal transparent egress on all pairs; unsupported/lost binding becomes ineligible. |
| Exact target request | Same live writer, sequence0 COM_QUERY with entire exact literal; clean opposite/parser boundaries, no prior pending egress. Require original remaining>=6s here. Forward original bytes normally once. |
| Request credited | Exact parent frame write callback completed; no generic counter or later frame can credit it. No server execution claim yet. |
| Full selected response | First server sequence1 is exactly11 framed bytes: four-byte header plus seven-byte `00 00 00 02 00 00 00` OK. No extra/coalesced/partial next frame, ERR, EOF, info, warnings, extra results or unsupported negotiation. |
| Eligible linearization | Before the sole downstream write path, recheck same live pair/root, remaining>=6s, request credit resolved, no target write attempt/queued target prefix, no failure/second target. Atomically consume the one-shot loss choice. |
| Loss committed | Record withheld11/application-reply-write-attempts0/completions0; never queue the target response, then close only this owned writer pair. A later refusal/throw/expiry cannot release withheld bytes or re-arm. |
| Candidate finalized | Genuine client refusal/erasure report, worker close/pipe EOF, exact loss accounting and all independent cleanup/audit checks agree. Otherwise inconclusive/failed, never favorable by default. |

If any gate fails **before** loss is committed, forward normally using the unchanged
transparent path and permanently label this attempt ineligible. Do not wait for
an unresolved request callback merely to become eligible; if full OK races that
callback, pass it through and do not fault. No late callback can arm retrospectively.
At complete-OK eligibility, client must still be live; an OK seen after client
failure is not evidence this harness caused its refusal. Parse fragmentation/
coalescence by packet state, never treat TCP chunk boundaries as protocol framing.
Normal forwarding still obeys existing transport safety refusals; ineligible does
not authorize forwarding an unsafe/malformed packet the no-fault relay rejects.

After the irreversible cut, fail-closed means **no replay/no late forwarding**,
not the passive membrane's fail-open egress rule. Exceptions invalidate the loss
proof and trigger owned cleanup; they cannot accidentally fall through to a normal
write. Keep this mutating test controller separate from the no-authority observer.
Any actual response write attempt, even one with an error callback/zero reported
completion, permanently disqualifies zero-write evidence. Never infer zero payload
from `bytesWritten`, socket destruction or an empty callback counter alone.
Never call `end(response)` or `destroySoon()` to dispose of a selected reply: no
flush/graceful-close path may enqueue or drain the withheld application bytes.
Use compiled external outcomes `ineligible` (no deliberate cut), `inconclusive`
(cut occurred but a required fact is missing/contradictory), or `witness_complete`
(all independent facts verified). Operational failures remain failures even if a
limited historical fact survives. Pure tests may report only a simulated match;
none of these labels authorizes retry, private qualification or runtime execution.

## Accounting, resources and private control

Instrument the sole socket-write site and bind every observed/attempted/completed
frame to its original pair/direction/private credit. Require exactly one withheld
response,11 bytes including header; all other received application bytes must have
matching successful egress with original order/hash and no unaccounted queue.
Other five directional streams retain complete/equal pass-through; selected
server→writer stream is equal only for its prefix, with precisely11 withheld bytes
as the declared exception. No general rule that expected loss excuses unrelated
inequality. At final: ingress = completed egress + exact withheld, pending0,
unexplained bytes0. TCP FIN/RST control traffic is not a fabricated SQL response;
the claim is no target application-reply write, not zero network traffic or proof
of every mysqlnd internal receive state.

Extra server bytes, a second response, an unforwarded cleanup QUIT, unexpected
client command or any additional discarded byte makes this first narrow proof
inconclusive. Do not silently broaden its exception allowance; a truthful native
incompatibility is a go/no-go gate. Normal native close may omit QUIT after a lost
connection; unlike successful drain, absence of writer QUIT is not proof of failure
or cleanup. Independent session/lock checks remain mandatory either way.

Retain whole-attempt2MiB frames/shared8MiB queued/64MiB bytes/4096 pair frames,
three exact role lifetimes within existing four-live/eight-accepted envelope.
Selected response payload exactly7 bytes; validate header before any selected-body
allocation. Retain at most its11 owned bytes until disposal, no raw capture/logs.
Destroy/zero only owned copies, never mutate borrowed buffers awaiting normal egress.
No TLS/compression/session-tracking/metadata negotiation changes to fit the parser.

Keep v1 `observe_only` transcript unchanged. Proposed separate **v2** fixed private
BOOT intent `selected_commit_response_loss`, original ROOT/BIND/BOUND schemas with
v2, disjoint256-bit attempt/root/pair tokens, canonical ASCII JSON/LF/exact keys.
Each line<=4096 bytes,<=8 lines and32KiB combined; nonce/auth/SQL/row/handles never
enter messages. No fault selector/token in argv/environment/HTTP/tempfiles.
Proposed PHP FINAL exact ordered keys:
`v,kind,seq,attempt,root,pair,case,checks,groups,returned_false,pre_guards,post_guards,root_erased`.
Require v2/kind final/seq3, case `selected_commit_response_loss`, checks1..8192,
groups1..32, returned_false/root_erased true, pre_guards8/post_guards0. These are
untrusted-to-parent test assertions, **not** native receipt or authorization. Parent
joins its own witness/egress facts and outer audit; worker exit0 means test checks
completed, never that native admission accepted. Missing/forged/duplicate/early/
late/trailing FINAL cannot finalize. No new phase after failure to fetch a qualifier.

PHP root's original10s and conservative Node T0-before-ROOT10s remain independent
clock epochs, monotonic, never reset. Recheck eligibility>=6s both at exact request
and complete response. Original5s native read timeout and existing6s socket idle
policy are not enlarged; no timer is used as evidence of server completion.
Node write completion is not PHP API receipt; socket timeout alone does not close
a connection, and server.close alone leaves existing connections. Future cleanup
must observe actual lifetime events, not calls alone.
[Node24.12 net lifecycle/write contracts](https://nodejs.org/download/release/v24.12.0/docs/api/net.html).

## Owned audit, cleanup and failure acceptance

Future proof begins with a fresh exact LocalWP confirmation, metadata-resolved
bundled PHP stdio preflight, bundled mysql initial siteurl/inventory/checksum audit,
no pre-existing guard fixtures, and only freshly marker-owned fixture tables/users.
Re-run positive no-fault observation and real authorization controls at their real
layers before a separately approved **single** loss attempt. No automatic retry.

Retain original proposal/all-ten IDs privately at the already existing pre-BEGIN
test seam, never manufacture them afterward. Client must genuinely refuse, retain
no native handle/origin/capture, add no new guards/readback/retry/rollback after
unknown COMMIT, preserve borrowed auth identity/session, and keep public operations
unavailable. Assertions inside native callback wrappers must escape outer swallowing
refusal handling; always-unavailable must fail the required no-fault positive.

Outer owner requires actual worker exit/close, EOF/closed pipes/no unexplained
stderr, listener/pair cleanup, independently absent exact server sessions and
all-ten owned locks available before DROP. Under existing24s watchdog/28s parent
cleanup policy, unresolved close/kill/pipes/pumps must return a bounded unresolved
receipt to the outer owner; do not hang indefinitely in `server.close` or pipe
drain. Existing90s outer fail-safe and3s independent session wait are backstops,
not extended runtime acceptance. No proof of hard OS kill/crash guarantees.

Existing native supervisor/outer owner are **not loss-ready**: they require positive
v1 FINAL/drain/QUIT/stream equality, relay close awaits jobs, and the outer PHP wait
then pipe drain can block if ownership is unresolved. A future dedicated loss
supervisor must prove bounded unresolved handoff/process-tree ownership and exact
cleanup-safe admission; do not simply replace `passed` with its negation or reuse
an unbounded path. If unavailable, stop before native loss experimentation.

Independent direct-backend audit, after client release/session absence, must find
the exact original owned ledger proposal and all nine other role rows unchanged;
check locks with bounded owned-only probes. This supplements, never substitutes
for, real selected OK/zero-write evidence. Never repair/reset ledger before audit.
Then exact owned row previews/DROP/absence, actual users/options digests and full
application inventory/checksums reconcile. No writes to actual application state.
If session/lock/process/fixture identity is unresolved, retain only exact owned
fixtures and report blocker; no arbitrary PID/server-thread kill, broad DROP or
application repair. A checksum difference fails proof even if cleanup succeeds.

## Falsifiability, staged implementation and review

Next implement a **socket-free test-only loss eligibility/accounting state machine**
with synthetic bytes, role/frame identities and virtual clocks only, not a runtime
outcome/authority layer or a callable socket instruction. Reuse strict profile and
exact target decoder contracts; keep old passive helper/tests untouched. Results
explicitly deny native/zero-delivery/auth/storage/dispatch/execution proof. This
scope includes the candidate receipt and v2 transcript tests, not IPC or a relay.

Required tests: non-vacuous matched witness and simulated one-shot disposition;
ordinary pass-through/ineligible controls; wrong root/pair/role/credit/sequence;
opaque COMMIT-looking rows; partial/coalesced/extra packets and every unsupported
OK form; exact6s boundaries at both decisions/no reset/late callbacks; target write
attempt before completion and after failure; one-byte leakage/error callback,
wrong-frame credit/aggregate imbalance/extra discard; pre-cut throw forwards normally
but post-cut failure can never replay; missing/early/stale/zero FINAL and every
missing cleanup/audit conjunct. Positive control must fail an always-unavailable
controller. Guard-removal controls must kill meaningful assertions without syntax
or environment failures; restore/hash-check disk source if any file is changed.

Then separately approve exact Plugin Tester **Selected COMMIT Response-Loss and
Owned Cleanup Proof**, including the single response discard and only owned writer
pair close, fixed harness/isolated fixtures/bounded failure controls/audits/cleanup.
First prove no-fault transparency, failure isolation and bounded unresolved handoff
before inducing loss; stop on incompatibility. No intermediary maintenance-only
queue, runtime DTO churn or artificial success to manufacture progress.

Canonical Feature Light: three changed design/plan/parent docs, direct source and
proof contracts above. Risk Other/design only. Clarified incompatible success/loss
predicates, request-callback eligibility race, pre-cut versus post-cut errors,
exact byte conservation, private-result observability and bounded cleanup handoff.
No remaining implicit product choice in this narrow candidate. Bloat/Structure not
needed for zero executable changes; no UI/UX workflow. Formal07/07A/06/Database/
whole-plugin reviews are future handoffs, not completions by writing this matrix.

Final focused Feature Security after source/reference/document crosschecks and
local QA: same real authorization/separate nonce domains, no failure guard refresh,
single exact owned target, distinct nonauthoritative external evidence, private
bounded tokens/resources, no replay/handle/grant, and owned-only cleanup reviewed.
No confirmed new production security defect. Later implementation needs its own
reviews/controls; FULL_PRE_RELEASE remains for coherent RC before packaging.
Executed local QA: dashboard PHPUnit299 tests/1423 assertions/two known Windows
Sodium skips; WPCS138, structure and build pass. Related uploader isolated API/
DATA173/10171 and full Node331 pass/no skips. Markdown relative links, source literal/
ordering and docs-only diff checked; generated/runtime/test source unchanged.
Exact-head dashboard CI/log counts recorded in source-scoped toolkit evidence,
never inferred from predecessor CI; not native verification. Uploader stays
byte-identical/unpublished in this task.
Existing Node watcher tests use their normal disposable tooling file/process
fixtures; they are not WordPress/storage/native witness experiments.

Implementation-plan progress: selected loss intervention and its honest witness/
cleanup limits specified; next concrete gated work is the bounded pure foundation.
**Recommended next gated slice: local-only V2.3 Socket-Free Selected Response-Loss
Eligibility and Accounting Safety Foundation**, synthetic bytes/messages and virtual
clocks only, through validation/commit/push/CI. Keep networking/IPC/WordPress/native
execution/storage writes/actual suppression/production wiring/grants/rollback disabled.
