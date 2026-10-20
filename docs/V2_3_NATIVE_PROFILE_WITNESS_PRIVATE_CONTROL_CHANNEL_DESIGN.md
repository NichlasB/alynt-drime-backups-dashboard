# V2.3 Native-Profile Witness Selection and Private Control-Channel Integration Design

Successor2026-10-20: approved synthetic-only foundation implemented in uploader
`docs/V2_3_SOCKET_FREE_NATIVE_PROFILE_CONTROL_FOUNDATION.md`; old decoder, native
relay and production wiring unchanged.111 scoped/full226 Node tests, source-scoped
canonical reviews and related isolated checks; exact-head CI evidence in toolkit.
Next approval is **Windows Owned-Process Stdio Feasibility and Cleanup Proof**,
no WordPress/database/network/native sessions/suppression. The historical design
and native proof limits below remain unchanged; no IPC/native compatibility inferred.

2026-10-20. Approved R3 **repository-only, design-only**. Dashboard owns this design
and plan/parent pointers. Uploader is read-only at
`a78bc9c7e460f78d17dafb44e1608b8c074ca562`; dashboard starts
`8e2053f553aa3fc51958f5991bbfcbf2dcafd0ff`, toolkit
`8a984401609886fc45dd4c9819cc94945d500ef0`, all clean/synced.
No implementation, WordPress bootstrap/authentication/SQL, users/fixtures/storage
writes, listeners/sockets, native or pipe experiment, process-failure injection,
response suppression, production wiring, grants, observer execution, activation,
effects, rollback, package/release/tag/deploy. Versions0.5.25/0.1.77 unchanged.

## Coherent boundary and current evidence

Review excluded bases uploader `47f7e68127e2a47db9ad0b658e1739f72b9b62a5` and
dashboard `4f0750dfdb19f583d36f1278e89d7262b6574350`: the complete owned no-fault
relay feature and this successor design, not just its documentation tail. Direct
dependencies: uploader `docs/V2_3_OWNED_RELAY_NO_FAULT_PROOF.md`, selected synthetic
decoder/protocol, relay framing/server/supervisor/worker/cases, joined test wrapper,
native admission root/transaction/storage and native session/profile/cleanup.
Parent: [witness feasibility](V2_3_OWNED_NATIVE_UNCERTAINTY_WITNESS_FEASIBILITY_DESIGN.md).

Pristine dashboard Option B source-only snapshot before edits:
`alynt-drime-backups-dashboard-20261020-165309.zip` plus matching `.txt`,13138 files,
SHA256 `6C54599BB960AD67003D8654702A8CE5E6900CF93D72AB770F3850FDC70B98A0`, under
`C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/`.
No checkpoint/stash/rollback; not a site/database backup. Master/toolkit/Site
Operations and prospective Plugin Tester profile read. No site is operated on,
so no LocalWP target confirmation or NovaMira/SQL access is consumed/reused here.
Future native work needs a fresh exact target and owned scope confirmation.

Historical native225/2 proves three genuine role/session lifetimes, six complete/
equal directional streams and no-fault cleanup; direct266/11 and5756/27 protect
the original guards/join. These native results are **not rerun** here. They do not
prove selected COMMIT/OK correlation, native decoder compatibility, a live private
channel, zero delivery, uncertain outcome, safe retry or private qualification.

## Decision and explicitly unproven dependencies

Design a **test-only passive selector plus inherited-stdio rendezvous**. Do not
attach the old five-frame synthetic decoder to the real stream or broaden its
contract invisibly. A new socket-free helper may model the native-profile subset
and bounded control transcript; later transport integration needs separate proof.
No new runtime DATA schema, authority/provider interface, SQL parser, endpoint or
callback is proposed. Existing native runtime and no-fault proof stay unchanged.

Three gaps are explicit go/no-go gates, not assumptions disguised as completion:

| Gap | Required later evidence |
|---|---|
| Relay accepts more handshake variants than the synthetic decoder; exact negotiated capability mask/auth OK status were not retained by the no-fault receipt | A bounded, strict synthetic profile first; later no-fault native evidence must satisfy it without relaxing runtime checks or inventing OK packets. |
| Current worker returns role IDs only after completion and Node closes child stdin immediately | Incremental private messages and a pre-BEGIN exact writer/pair acknowledgement; post-hoc ID equality is insufficient. |
| Standard inherited streams exist, but bounded PHP read/write/EOF behavior on this Windows combination is not proved | Separate owned-process, no-WordPress/no-DB/no-socket pipe feasibility proof before any native channel integration. |

## Native-profile candidate: recognition, never delivery control

The prospective selector consumes complete bounded frames from the existing
transparent framing path, never TCP chunks interpreted as messages. It preserves
the2MiB frame/8MiB shared pending-frame/64MiB attempt/4096 pair-frame envelope and
additional bounded Node/TCP buffers. Handshake itself is capped256-byte greeting,
4096-byte hello and7-byte auth OK; no continuation or unknown extension fallback.

- Protocol10/MariaDB10.6, nonzero uint32 server thread, native-password plugin,
  server collation8 or45; distinguish six reserved bytes from MariaDB's extended
  capability word. Server advertisement is not negotiated client capability.
- Parse protocol41 client fields completely, including bounded20-byte opaque
  secure auth and exact trusted username/database where present. Never retain or
  publish auth bytes. Writer/capture require CONNECT_WITH_DB; the borrowed wpdb
  role may select DB later, but cannot be selected as writer. Runtime still
  independently validates its actual database, charset and physical session.
- Initial candidate client allowlist: required CONNECT_WITH_DB(for writer),
  PROTOCOL_41, TRANSACTIONS, SECURE_CONNECTION, PLUGIN_AUTH; optional LONG_PASSWORD,
  LONG_FLAG, LOCAL_FILES advertisement, MULTI_RESULTS, PS_MULTI_RESULTS and
  CONNECT_ATTRS. Attribute block: at most1024 bytes/16 pairs, bounded length encoding,
  no trailing bytes, discard immediately. Unknown bits, length-encoded auth,
  TLS/compression/zstd, multi-statements, expired-password, session tracking and
  deprecated-EOF modes cannot qualify. This allowlist is a **candidate**, not a
  claim about the unrecorded native mask; unsupported real negotiation is a gate.
- LOCAL_FILES advertisement does not permit a0xfb server file request; preserve
  the existing refusal. Native session flags/local-infile policy are not changed.
  No auth switch, certificate handling, downgrade or synthetic server greeting.
- Authentication candidate requires the canonical seven-byte zero-count/warnings
  OK with AUTOCOMMIT-only status2, expected sequence2. The old relay did not check
  that status; native conformance remains unproved. Do not broaden to unknown flags
  merely to obtain a positive proof.

After authentication, session setup, metadata SELECTs, native policy checks,
BEGIN, prepared projections/execute/close and readback must remain opaque and
byte-exact. `admission_transaction()` calls session/policy SELECTs **after** guards
7/8 and before BEGIN/COMMIT: the next frame after binding is not assumed COMMIT.

Only a complete client sequence0 COM_QUERY whose entire payload is byte-exact
`0x03 + COMMIT AND NO CHAIN NO RELEASE` on the selected live writer pair may be
the target. No substring/case-folding/whitespace normalization, parameter/row/
statement-body search, COM_STMT_EXECUTE interpretation or second target. Change-
user/reset/set-option/unknown commands invalidate this narrow observation. They
never grant a new session identity or restart the lifetime.

The witness relies on the reviewed unmodified owner/mysqlnd **synchronous** query
sequence and exclusive writer ownership, not a generic independent SQL exchange
parser. Target selection requires both directional assemblers at frame boundaries,
no pending earlier egress and no already-partial server response. Bind the original
request's egress attempt/completion to the first subsequent server frame, sequence1;
if response arrival races the write callback, retain only its bounded observation
until accounting resolves, without withholding its normal egress. Extra client
traffic, partial/extra/coalesced ambiguous responses or wrong sequence invalidate.
This source/trusted-local-server assumption must remain explicit in later proof.

The selected success response is exactly the same canonical seven-byte idle OK;
ERR/EOF/FE/result data/zero-leading rows, noncanonical lengths, warnings, extra
results/status/info/session bytes do not qualify. An observation is finalized only
after request/response forwarding accounting and a terminal no-fault receipt agree;
a first leading-zero byte or a stored ledger receipt is not sufficient.

In the next no-fault integration, observation failure only marks the **proof**
inconclusive: it must not change transport egress, close a live transaction,
quarantine a response, fabricate a native return or trigger rollback/retry. Existing
transport safety limits remain separate. There is no suppress/delay/drop method,
delivery instruction, fault toggle or grant in the proposed pure helper result.
Actual suppression remains a different future approval and implementation gate.

## Private channel and original-root binding

Choose child standard stdin(supervisor to worker) and stdout(worker to supervisor)
with stderr counted/discarded, not public HTTP/TCP control, tempfiles, command-line
or environment tokens, fd3 assumptions or Node's non-Node-unsupported IPC channel.
The Node parent must drain stdout incrementally while the child waits on stdin;
current collect-final-JSON behavior cannot be reused unchanged. Outer PHP owner
still receives only one redacted supervisor result. No arbitrary process/endpoint.

Proposed v1 wire is ASCII canonical JSON followed by LF: exact ordered keys/types,
no duplicate/unknown keys, whitespace variants, escapes, CR/NUL, Unicode, BOM or
trailing bytes. Validate bounded decoding and exact canonical re-encoding before
acceptance. Each line<=4096 bytes, at most8 lines/32KiB total across directions;
partial EOF/oversize/extra message/sequence gap permanently invalidates the attempt.
Integer thread IDs are positive uint32; nanosecond durations are canonical decimal
strings parsed with bounds, not floating-point timestamps. No raw auth, nonce,
candidate rows, command/reservation IDs or write-capable handles in messages.

| Direction/sequence | Proposed exact message purpose |
|---|---|
| Node1 BOOT | Fresh256-bit attempt token, v1 protocol, fixed observation-only intent. Token only in inherited pipe memory, never logs/argv/env. |
| PHP1 ROOT_REQUEST | Echo attempt for this single upcoming genuine root, after setup/login, before its actual private entry. |
| Node2 ROOT | Fresh domain-separated root challenge; supervisor samples its own monotonic T0 **before** writing this message. |
| PHP2 BIND | Echo attempt/root, actual borrowed auth/capture/writer thread IDs and locally checked remaining duration. Emitted only from fixed test callback7, before BEGIN/DML. |
| Node3 BOUND or REJECTED | One-shot acknowledgement of the exact original live writer pair; BOUND echoes writer and fresh opaque pair ticket. No storage/execution permission. |
| PHP3 FINAL | Bounded compiled-label proof summary tied to attempt/root/ticket; Node strips all private correlation values before operator/parent output. |

Exact ordered key schemas (the uppercase letters are private values, not wire keys):
BOOT `(v,kind,seq,attempt,intent)` with kind `boot`, intent `observe_only`;
ROOT_REQUEST `(v,kind,seq,attempt)` with kind `root_request`;
ROOT `(v,kind,seq,attempt,root)` with kind `root`;
BIND `(v,kind,seq,attempt,root,auth,capture,writer,remaining_ns)` with kind `bind`;
BOUND `(v,kind,seq,attempt,root,writer,pair)` with kind `bound`;
REJECTED `(v,kind,seq,attempt,root,reason)` with kind `rejected`, reason `unavailable`;
FINAL `(v,kind,seq,attempt,root,pair,passed,checks,groups)` with kind `final`.
All v values1; sequence numbers as above. Attempt/root/pair are distinct64-character
lowercase hex strings, generated only by the supervisor in the future real channel.
FINAL passed is boolean, checks integer1..8192, groups integer1..32; these are
test-summary assertions, not native outcome facts or proof by count alone.

Only that happy transcript can complete; errors terminate rather than retry,
resynchronize, accept another root, reuse IDs/tickets or fall back to post-hoc IDs.
The supervisor retains its unforgeable per-pair in-process identity and socket
objects; the uint32 thread alone is not a capability. Exclude borrowed/capture,
require a single live authenticated match, same negotiated profile and exact
three-role lifetime set at completion. Closed/replaced/duplicate/foreign pairs
cannot be rebound, even if a server later reuses a numeric thread ID.

The worker uses the existing test-only `nonce_life` wrapper, not new production
hooks. At verify7 it reflects the original root/writer once and asserts retained
origin/pin continuity, phase idle,20 protected projections and0 writes, borrowed
auth identity and capture closure. Read only native thread properties; add no SQL
or extra nonce verification. Binding inside guard7's callback is **not** evidence
guard7 has succeeded. Return its lifetime unchanged; genuine guards7/8, origin,
physical facts and budget checks still run normally before transaction entry.
At verify8 recheck the same root/pair ticket and local budget without channel I/O.
Never run channel callbacks while native row locks are held. Callback failures
must be rethrown by the test wrapper outside production's swallowing refusal
boundary, so an unavailable owner cannot produce a vacuous green proof.

## Clock and backpressure safety

Keep durations canonical0..10000000000ns and reject invalid/overflowing integer input
before arithmetic; modeled Node start must be0..(signed64 max minus10s).
Do not compare PHP hrtime absolute values with Node hrtime absolute values. Keep
PHP's actual original start/window untouched. Node sets its conservative local
deadline T0+10s from ROOT, which precedes the actual root call under the reviewed
wrapper, assuming normal same-host monotonic duration accuracy, not a shared epoch.
Never use BIND arrival+reported remaining as a refreshed deadline. BIND/BOUND both
require their own clock's remaining>=6s, monotonic non-regression and a single
<=1s rendezvous acceptance duration. A late ACK is rejected even if token-valid.

The1s check is an acceptance test **after I/O returns**, not a claim that blocking
PHP pipe I/O is cancelled within1s. A silent/stalled peer can require the existing
owned supervisor watchdog/EOF teardown; while waiting, the worker is still before
BEGIN and must never continue on timeout/partial/late messages. Watchdog/process
and cleanup failure paths require separate owned proof, not an in-memory claim.
No busy spin, assumptions about stream_select/nonblocking Windows pipes, arbitrary
kill, extended runtime budget or success inferred from elapsed time.

Initial binding6s is necessary but not sufficient for a future lost-ack experiment:
SQL between binding and COMMIT consumes the same window. Any later suppression
design must recheck conservative remaining>=6s **at the exact COMMIT boundary**;
if late, forward normally and classify the experiment ineligible, never introduce
a new fault after DML merely to salvage timing. This design implements neither
that check in transport nor response withholding.

## Staged acceptance and adversarial handoff

1. Next concrete foundation: **socket-free native-profile selection/control
   transcript helpers**, synthetic bytes/messages and virtual clocks only. Preserve
   old decoder tests; native compatibility is not inferred from synthetic positives.
2. Then separately approve Windows owned-process stdio feasibility, no WordPress,
   database, networking or native sessions. Prove partial read/write, flush, EOF,
   bounded output, stalled-peer/watchdog and owned process cleanup. If unavailable,
   report the exact transport blocker; do not silently add a control listener or fd.
3. Then separately approve exact Plugin Tester owned **observation-only** native
   integration. Require unchanged positive admission plus negative auth/target
   controls, original guard order/deadlines, truthful negotiation/selected OK,
   unchanged forwarding and application checksums/session/fixture cleanup. No
   response suppression. Only afterward evaluate a distinct lost-ack approval.

Required pure regression groups: valid native-shaped profiles and complete
positive transcript (always-unavailable fails); every optional capability/attribute
boundary and unsupported mode; fragmented/coalesced/truncated/oversize frames;
opaque intermediate/prepared/row/nonce-like COMMIT bytes versus exact target;
sequence/early-response/duplicate-target/race cases; wrong/reused attempt/root/
pair/role and closed lifetime; canonical JSON duplicate/type/length/overflow cases;
missing/late/extra ACK, original clock expiry/regression/exact6s and1s boundaries;
no deadline reset or raw/handle/authority export. Guard-removal controls must fail
assertions, be SHA-restored before any later integration, and not weaken fail-closed
behavior or create artificial runtime success paths.

## References, review and validation scope

Primary references checked2026-10-20: [MariaDB handshake](https://mariadb.com/docs/server/reference/clientserver-protocol/1-connecting/connection),
[COM_QUERY](https://mariadb.com/docs/server/reference/clientserver-protocol/2-text-protocol/com_query),
[OK/status](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/ok_packet),
[Node24 child stdio](https://nodejs.org/docs/latest-v24.x/api/child_process.html#optionsstdio),
[PHP standard streams](https://www.php.net/manual/en/wrappers.php.php), and
[PHP stream_select Windows caveat](https://www.php.net/manual/en/function.stream-select.php).
These establish protocol/API constraints, not Windows interoperability evidence.

Canonical Feature Light: three dashboard documents and minimum uploader source/
tests reviewed; risk Other/design only. Clarified broad relay versus strict witness,
post-binding traffic, guard7-before-verification, clock epochs/reset, ACK versus
authority, observation failures versus delivery faults and Windows I/O limits.
No runtime production defect or new implementation decision left implicit.
Bloat/Structure unnecessary: zero changed executable source, no split/cleanup.
UI/UX, formal DS3/07A/Database/whole-plugin readiness not run or refreshed; the
future regression matrix above is a handoff, not tests executed in this design.

Final focused Feature Security follows source/protocol crosscheck and local
regressions: trusted operator/fixed-child/exclusive-writer assumptions, private
tokens and lifetime/domain separation, no public control/grant, disjoint genuine
nonce domains, bounded parse/output/clock and no transaction-time callbacks.
No confirmed production security defect; proposed helper guards route to future
pure tests and actual pipe/native boundaries to their separately approved proofs.
Full pre-release suite still required for a coherent RC before packaging.

Executed repository-only validation: uploader related DATA/API
`php ./vendor/bin/phpunit --filter 'Registration(NativeFailure|NativeQualification|ClosedQualification|NativeOutcome|AdmissionOutcome|AdmissionJoin)' --do-not-cache-result`
passed173 tests/10171 assertions, no skips; `npm run test:dev-tools` passed115 tests,
no skips, with only the existing watcher's owned temporary filesystem fixtures;
WPCS235 pass with the existing PHPCompatibility functionWhitelist format
deprecation. These are isolated source/tooling tests, not a new native proof.
Dashboard full PHPUnit299/1423/two existing local Sodium skips, WPCS138,
`npm run check:structure` and build pass; generated assets unchanged. Three-document
relative links, source references and final Git diff checked. Uploader remains
unchanged/unrepublished. Exact dashboard pushed-head Linux CI and toolkit
publication/check-context evidence are recorded in its source-scoped checklist
entry, not inferred from predecessor CI. No new tests or mutation experiments.

Implementation-plan progress: native-profile/passive selection and private-root
rendezvous now have an explicit falsifiable contract, without premature native
authority or delivery control. **Recommended next gated slice: local-only V2.3
Socket-Free Native-Profile Selection and Control Transcript Safety Foundation**,
synthetic bytes/messages and virtual clocks only; keep actual IPC/networking,
authentication/storage/native execution, response suppression and wiring disabled.
