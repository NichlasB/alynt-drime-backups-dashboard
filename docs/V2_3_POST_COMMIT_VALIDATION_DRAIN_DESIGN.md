# V2.3 Post-COMMIT Session-Validation Drain and Observation Finalization Design

Successor foundation2026-10-20: uploader
`docs/V2_3_SOCKET_FREE_POST_COMMIT_DRAIN_FOUNDATION.md` implements the bounded pure
model/virtual-clock tests,69 targeted/full306 Node and eight restored semantic
controls. No actual IPC/native/storage/suppression/wiring change or repeat proof;
the blocked native draft stays preserved/unpublished. Next gate is passive native
adapter hardening plus a freshly approved exact Plugin Tester owned cleanup proof.

2026-10-20. Approved **local-only, design-only** successor. Documentation only;
no native/pipe experiment, WordPress bootstrap/authentication/SQL, fixture, storage
write, listener, process-failure test, suppression, production wiring, grant,
activation, effect, rollback, package, release, tag or deployment. Versions uploader
0.5.25/dashboard0.1.77 unchanged. Git publication/CI is repository work, not a native
experiment. Prior fixture approval is not reused for another experiment.

## Ownership, evidence and review boundary

Dashboard owns this design and the plan/parent pointers. Starting dashboard head
`d222004dc73eb16320b9e203371be4207f3adbbf` is clean/synced. Uploader remains at
`f11ba9c7dfa1c2d7cb0f94b03ce2c954863e045f` with the known **uncommitted, blocked**
observation attempt; its executable files and local blocker record are read-only
in this task. Toolkit starts`0d47b4a1ba236e4f4c0d07bc1d0dbac59be55a71` with the
known pending source-scoped blocker entry; preserve it and its historical rows.

Explicit excluded review bases: uploader
`47f7e68127e2a47db9ad0b658e1739f72b9b62a5` and dashboard
`4f0750dfdb19f583d36f1278e89d7262b6574350`. These cover the coherent no-fault relay,
native-profile/control design, synthetic foundation and stdio work, not just a
documentation tail. Review is source-scoped to the original native admission
root/transaction/session-policy/cleanup, affected witness/framer/control/proof
owners and these three dashboard docs, not every intervening unrelated change.
The uploader draft is outside Git history: correlate the failed attempt separately
with local `docs/V2_3_NATIVE_OBSERVATION_STDIO_BLOCKED_PROOF.md` and draft hashes;
never imply a commit or CI run covers it. Draft registry SHA256
`15E18926A3B54542590BB7F87196FA8320E4EBB2A069FFF97FBB36606AD97BB9`.

Fresh dashboard source-only Option B archive/sidecar before edits:
`alynt-drime-backups-dashboard-20261020-183941.zip` and matching `.txt`,13156 files,
SHA256 `E70444687ADBA6A46B44ED7BA4F428304B80AF060465158D8AEA19E5C78D70BE`, under
`C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/`.
No checkpoint/stash/restore; not a site/database backup. Master, toolkit, repository
and Site Operations/profile guidance read. No site is operated on: no fresh site
confirmation, Novamira/SQL/service check or credential reconnection is needed here.

Parent: [native-profile/private-channel design](V2_3_NATIVE_PROFILE_WITNESS_PRIVATE_CONTROL_CHANNEL_DESIGN.md).
This explicitly **supersedes only its no-extra-writer-traffic-after-selected-OK
completion rule** with the narrow candidate below. Exact selection, role ownership,
pre-BEGIN nonce/root binding, clock limits and delivery guarantees are not relaxed.
Old synthetic helpers/tests remain unchanged until a separately approved foundation.

Historical failed proof, not rerun: bundled-PHP stdio141/15 passed; genuine worker
242 checks/2 groups and original-root/pair exchange completed; three strict profiles,
literal COMMIT/canonical idle OK and egress callbacks observed. Six complete/equal
streams/256967 bytes/three QUITs, owned cleanup and77 unchanged application checksums.
Overall native proof **failed** with `writer_command`. That coarse label does not
identify a raw failing packet. Source inspection independently establishes that
normal completion necessarily sends further SELECTs. No native wire shape or
postamble compatibility is proved by this design or by that partial observation.

## Decision: retain target facts, validate a separate closed postamble

Do not remove or bypass `admission_transaction()`'s post-COMMIT `session(false)`.
It checks the same physical session/idle state and then `policy()`; those checks
precede `commit_idle`/`committed`. They are not a second mutation. On the successful
path, `finish()` closes the owned writer without sending a rollback. Borrowed-auth
revalidation and genuine nonce guards9/10 follow on their own role, not this writer.

Choose a **fixed two-query, one-row-each postamble recognizer**, not arbitrary SQL,
generic result-set interpretation, an inactivity timer, a callback-supplied success
bit or an early finish at COMMIT OK. Preserve the exact selected request/OK and
their per-frame forwarding evidence immutably. A later invalid drain makes overall
proof fail; it cannot rewrite that observation into an uncertain/failed native
commit or infer current acceptance. Native policy values remain checked by the
unchanged real owner. The observer never authorizes, retries, repairs or executes.

The proposed successful writer sequence is:

| Phase | Required observation before advancing |
|---|---|
| Selected COMMIT | Existing exact sequence0 COM_QUERY, same pinned live writer, clean pre-target boundaries. |
| Selected OK | First response sequence1, exact seven-byte idle OK; request/response egress credits retained separately. |
| Session drain | Exact Q_SESSION below, one complete10-column/one-row result. |
| Policy drain | Exact Q_POLICY below, one complete5-column/one-row result. |
| Writer close | Exactly one sequence0 COM_QUIT, forwarded; clean stream/socket closure on the original pair. |
| Final reconciliation | Private final, genuine positive worker, role lifetimes, all forwarding/cleanup and unchanged application state agree. |

There is no fallback path that skips either SELECT or accepts a partial drain.
If a genuine session check fails or short-circuits before policy, normal runtime
refusal/cleanup proceeds but the **positive no-fault proof cannot pass**.

## Exact command and source contract

Fixed byte comparisons include the COM_QUERY0x03 prefix and entire ASCII payload;
sequence0 each time. No trimming, substring/case folding, SQL rewriting, prepared
statement interpretation, caller-provided SQL or command allowlist after the OK.
Q_SESSION is the literal query in `trait-native-closed-begin-session.php::session()`:

```sql
SELECT CAST(CONNECTION_ID() AS CHAR),DATABASE(),@@hostname,CAST(@@port AS CHAR),CAST(@@server_id AS CHAR),VERSION(),CAST(@@autocommit+0 AS CHAR),CAST(@@in_transaction+0 AS CHAR),CAST(@@read_only+0 AS CHAR),CAST(@@tx_read_only+0 AS CHAR)
```

Q_POLICY is the literal query in its `policy()`:

```sql
SELECT @@tx_isolation,CAST(@@innodb_lock_wait_timeout AS CHAR),CAST(@@lock_wait_timeout AS CHAR),CURRENT_ROLE(),CAST(@@innodb_flush_log_at_trx_commit AS CHAR)
```

Future source-contract tests must compare the two compiled fixture strings against
these fixed source calls and their ordering after the literal COMMIT. Do not read
new SQL from production at runtime to expand the recognizer automatically. Source
drift fails the test and requires review. Second COMMIT/BEGIN/DML, SET, prepared
commands, reset/change-user, swapped/repeated SELECTs, third query, early QUIT or
anything after QUIT permanently invalidates this observation attempt.

## Candidate result grammar and resource bounds

Keep the strict previously designed MariaDB10.6/native-password profile, with no
compression/TLS/session-track/deprecated-EOF/metadata-cache/extended-metadata mode
in this candidate. Unsupported actual negotiation or result status is a later
native go/no-go gate, never grounds to broaden the parser just for a passing run.
This profile and exact packet forms below are **proposed, not native-verified**.

For each query, sequence numbers restart at1. Proposed classic text result:
one-byte column count10 or5, exactly that many structurally valid column definitions,
metadata EOF, exactly one text row, final EOF. Thus14 and9 server packets respectively;
no sequence wrap, missing/extra row, standalone OK/ERR, extra result or trailing
payload. Proposed EOF is exactly five bytes `fe 00 00 02 00` (zero warnings, idle
AUTOCOMMIT status only), distinct from the selected seven-byte COMMIT OK. Do not
classify a packet by its first byte alone. Normal text framing and EOF distinctions
are described by [MariaDB result sets](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/result-set-packets)
and [EOF fields](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/eof_packet).

Within each column definition, bounded-skip the six length-encoded strings,
require catalog `def`, then fixed-field-length12 and exactly12 remaining bytes;
reject extended/trailing data. A row must consume exactly10 or5 bounded encoded
fields and the entire payload. Do not store, decode to logs, hash or publish field
contents. For this source-shaped candidate, the session row has no NULL fields;
policy's fourth field alone is the NULL marker. Other lengths/values are opaque
to the observer, not substitutes for the owner's actual session/policy assertions.
The [text row format](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/resultset-row)
and [length encodings](https://mariadb.com/docs/server/reference/clientserver-protocol/protocol-data-types)
are context for this restricted structural scan, not native execution evidence.

Candidate tighter postamble envelope:4096 payload bytes/frame,1024 bytes per
encoded string,32768 total bytes including headers across both directions,
exactly23 server frames and three client frames (two queries/QUIT). Accept canonical
single-byte lengths0..250 or0xfc/two-byte lengths251..1024 only; reject0xfd/0xfe,
truncation, arithmetic overflow, nonminimal encodings and illegal NULL positions.
Enforce limits from headers before allocation; no packet continuation. These caps
fit the fixed small source queries; exceeding them fails observation, not transport.
Whole-attempt2MiB frame/8MiB queued/64MiB total/4096 pair-frame caps remain in force;
this design never raises them. Zero retained row/auth buffers on refusal/finalization.

## Forwarding, race handling and finalization

The passive observer receives bounded frame views and owned in-process pair/frame
identities, never sockets, send/close callbacks, write-capable handles or delivery
instructions. A false return, malformed input, expired budget **or observer throw**
latches an inconclusive proof while ordinary egress continues unchanged. Catch
observer failures outside the transport error path. Never mutate/zero a borrowed
frame before normal forwarding/hash accounting; clear only observer-owned buffers.
Existing independent transport safety limits still apply. No new drop, delay,
quarantine, injected response, forced disconnect or suppression toggle.

Record each exact frame's observation, egress attempt and completion once, using
parent-owned identity, not a generic pending counter or a later unrelated callback.
Require same pair/direction/frame for completion; duplicate/foreign/missing credits
fail. Observation order can advance before a write callback resolves: a server
response may precede its request's callback, or the next synchronous SELECT may
arrive before the previous response callback. Preserve the bounded logical stage
and outstanding credits without waiting to forward. At most one unresolved frame
per direction on this writer under the serial pumps; unexpected pipelining fails
observation, not egress. Test both callback orders explicitly. No delayed callback
may turn an incomplete/failed stage green or account for the wrong selected frame.

Both assemblers must be boundary-complete at command transitions, with no unmatched
prior response. A completed prior frame whose callback is pending is not a partial
packet. Coalescing/fragmentation is parsed by frame state, never TCP chunk boundaries.
When the final EOF is observed, all of its bytes must still be forwarded normally;
QUIT is eligible only after both exact results were observed and never substitutes
for their egress completion. Clean closure requires complete parser boundaries,
all exact credits resolved and no queued writer bytes. Early half-close/reset,
incomplete/extra frame or missing QUIT cannot certify success.

Keep PHP's actual original10s root and Node's conservative pre-ROOT10s deadline;
do not compare their absolute clock epochs. No drain/OK/FINAL/cleanup event resets
the clock. Binding still requires>=6s remaining and<=1s rendezvous acceptance on
each own clock. The logical drain and correlated FINAL must be observed within
the original window, with monotonic non-regression. Cleanup may finish afterward
under its separate existing bound; late evidence cannot resurrect expired logical
completion. Capture closure/auth liveness at bind, writer closure before borrowed
auth closure and the exact three original pair lifetimes remain mandatory.

Private FINAL remains the canonical three-message-per-direction protocol; it is
an untrusted-to-the-parent **claim**, never terminal proof. Retain it provisionally
if worker cleanup/normal auth QUIT has not completed. Final success additionally
requires exit0, EOF/closed pipes/no unexpected stderr, positive genuine admission
and guard order, observed drain/QUIT, complete/equal transparent streams, no live
listener/socket/session, exact fixture cleanup and unchanged application checksums.
These are conjunctive; zero checks, an always-unavailable owner or merely finding
a ledger row cannot pass. Redacted output may report compiled booleans/counts and
failure labels only; no tokens, SQL, row/nonce/auth bytes or raw handle/session IDs.

## Regression handoff and staged approval

Next foundation is **socket-free, synthetic bytes and virtual clocks only**. Add
a distinct fixed-drain recognizer and explicit native-profile successor mode/helper;
do not invisibly broaden the old five-frame decoder or rewrite strict old tests.
Shared frame helpers may be reused without a public runtime API/dependency.

Required positive: exact COMMIT/OK, both source-shaped results, QUIT/clean close,
all frame credits and final positive receipt. Exercise fragmentation/coalescence,
both write-callback races, opaque rows containing COMMIT-looking text, policy NULL,
exact resource/clock boundaries and separately preserved historical target facts.
Required negatives: unavailable owner; missing/swapped/duplicate/third query;
second COMMIT/mutation; bad sequence/column count/definition/row cardinality;
wrong EOF length/status/warnings; ERR/OK-as-row/more-results/metadata modes;
illegal NULL/length encoding/caps/trailing bytes; premature QUIT/close/extra packet;
wrong/reused pair/frame credit; observer throw that must not alter egress; expired
root, refreshed clock or forged/early FINAL. Distinguish parser tests from genuine
auth, SQL/storage, independent session absence and physical forwarding proof.

After implementation, run canonical Feature Light, Bloat/Structure when needed,
07 then07A (with assertion-killed/SHA-restored guard controls), deeper06 for the new
bounded traversal if necessary, final Feature Security. Before another native run,
review/harden the existing uncommitted integration separately: observer throws must
not reach transport teardown, exact selected-frame accounting must survive races,
all failure paths must reconcile owned process/listener/session cleanup. This design
does not certify that draft or publish it. Then separately approve the exact Plugin
Tester observation-only fixture/listener/process scope and rerun the whole positive
proof/negative controls/cleanup. No lost-ack/suppression approval is implied.

## Focused design review and validation scope

Canonical Feature Light: three dashboard documents plus the minimum original
runtime and affected helper/draft call sites above; risk Other/protocol design,
no UI/public/database contract change. Clarified the source-required two-query
postamble, status versus row context, frame-credit races, exception isolation and
FINAL versus terminal cleanup. No production defect fix or silent parser relaxation.
Bloat/Structure unnecessary for zero changed executable files; no artificial split.
07/07A/06 handoff above is future work, not formal workflow completion here.

Final canonical Feature Security after source/protocol/diff crosscheck: same
original authentication/nonce/role boundaries and private stdio lifetime, bounded
non-authoritative parsing and redaction, exact command/result pairing, no transport
capability or privilege export. No confirmed new production security defect in this
docs-only change; proposed guards require future permanent tests/native proof at
their proper layer. Existing blocked draft remains unapproved for publication.
No UI/UX, Database Review, whole-plugin readiness or historical row refresh; full
pre-release suite remains required for a coherent RC before package/publication.

Local repository-only QA executed: dashboard PHPUnit299 tests/1423 assertions,
two existing Windows Sodium skips; PHPCS138 files, structure guard and build pass.
Generated assets/runtime/test source unchanged; document links, literal SQL/source
ordering and diff/ownership checked. Exact-head dashboard CI publication evidence
is recorded in the source-scoped toolkit checklist entry. These validate unchanged repository code/
docs, not the proposed parser or the uncommitted uploader draft. No new tests,
mutation controls, native authentication/storage/transaction or delivery result.

Implementation-plan progress: the source/contract mismatch now has an explicit
bounded design; the native observation milestone remains blocked pending tests
and fresh proof, not falsely completed by this document.
**Recommended next gated slice: local-only V2.3 Socket-Free Post-COMMIT Validation
Drain and Observation Finalization Safety Foundation**, synthetic bytes/virtual
clocks only; keep actual IPC/network/authentication/storage/native execution,
response suppression and production wiring disabled.
