# V2.3 Owned Native Uncertainty Witness Feasibility Design

2026-10-20. Approved R3 **repository-only, design-only**. No code implementation,
WordPress/LocalWP bootstrap, authentication, SQL, users, fixtures, storage writes,
listeners, relay, native experiment, production wiring, grant, observer execution,
activation, effects or rollback. No package/release/tag/deploy. Uploader0.5.25 and
dashboard0.1.77 unchanged. Future experiments below require separate approval.

## Ownership, coherent boundary and protection

Dashboard owns this design and two plan/parent-design pointers. Uploader remains
read-only at `ae8c0f4924eafd40fad1963b42596274615da09d`; dashboard starts
`89108c90a50d9d6649ad95cd81c1de8be85fb860`, toolkit
`eb2937954519a125961933544c93667c60315727`, all clean/synced. Reuse explicit
excluded bases uploader `95360a869f80f8821f49d2fa0b9afbac54592173` and dashboard
`54889b58fc4557b58aa6215c974d83a46bb68aed`: original failure design, closed source
bridge, owned proof and this successor, not a documentation-only tail. Earlier
physical-profile/session/DATA dependencies are read directly.

Parents: [failure qualification design](V2_3_UNCERTAIN_NATIVE_OUTCOME_FAILURE_QUALIFICATION_DESIGN.md)
and uploader `docs/V2_3_NATIVE_FAILURE_CLOSED_QUALIFICATION_OWNED_PROOF.md`.
Historical native5756/27, outcome2028/9, guard266/11 and77 unchanged application
checksums are not rerun or enlarged here. Those prove bounded SQL failure/cleanup,
not lost acknowledgements or private failure qualification results.

Pristine dashboard Option B source-only snapshot before edits:
`alynt-drime-backups-dashboard-20261020-153557.zip` and matching `.txt`,13121 files,
SHA256 `43EFC72D583BFDD61762397F4E51D1B6AE35BECCA2714696B9253C0888BDE828`,
under `C:/Users/Captain/Documents/AI Workflows/Toolkits/toolkit-snapshots/alynt-drime-backups-dashboard/`.
No checkpoint branch/stash/rollback; not a site/database backup. Site Operations
and prospective Plugin Tester profile read; no site operation or NovaMira access
is needed for this repository-only design. A later target gate is not satisfied
by a prior task's Plugin Tester confirmation.

## Decision: conditional feasibility, not an experiment-ready proof

Select one candidate: a **test-only bounded loopback byte relay**, controlled by an
independent supervisor, that withholds one real server COMMIT response from the
exact owned writer. No runtime provider or failure switch. This is a source/design
inference, not a claim that the current Windows/mysqlnd environment supports it.
Current proof scripts are not relay-ready and must not be silently repurposed.

| Source constraint | Consequence for the candidate |
|---|---|
| `trait-native-observer-session.php::profile/open_reader/native_idle` accepts a syntactic loopback DB_HOST port; compares borrowed/owned actual server db/host/port/id/version/read-only facts and distinct native thread IDs | A relay listener port need not equal the server's reported port. Preserving genuine handshake and metadata may satisfy existing checks without weakening them; this still needs a later positive native control. |
| `trait-native-closed-begin-session.php` configures only new owned connections; pins physical identity and READ-COMMITTED, lock2/metadata3, flush1 policy | Relay cannot spoof metadata, adopt a caller handle, reconnect or change policy. Auth/capture/writer remain distinct native mysqli objects and server sessions. |
| `scripts/tests/fixed-core-proof-worker.php` validates exact LocalWP backend port and defines DB_HOST from it | Existing worker stays unchanged. A separately approved test-only supervisor must distinguish independently verified backend target from its exclusively owned ephemeral listener; no actual wp-config/sites.json/route/firewall change. |
| `trait-registration-admission-transaction.php` calls exact `COMMIT AND NO CHAIN NO RELEASE` after all-ten readback; marks send before native query | The experiment targets that literal command only, not a generic UPDATE/execute failure. Sending bytes is not evidence the server completed COMMIT. |
| `trait-registration-admission-root.php` catches native failure, explicitly cleans/qualifies closed, returns false and erases private DATA | External witness never enters runtime qualification. Native boolean/refusal/final spent latch cannot expose or certify the private result/count. |
| Native cleanup rolls back only definite pre-COMMIT ownership | Unknown COMMIT must not acquire a retry, reconnect, readback, repair or compensating rollback in the owner. Positive client close would still not prove server quiescence. |

Current port10004 is historical target context, not an experiment parameter to
reuse blindly. Future backend metadata/siteurl/marker checks must resolve afresh.
All three isolated core connections would traverse the new listener; only the
independently bound owned writer may be faulted. Borrowed auth is never disrupted.

## Minimum witness: observed server success, not invented acknowledgement

MariaDB specifies packet framing and connection IDs in its handshake, a COM_QUERY
command containing SQL, and a server OK response for successful command completion.
These establish a candidate observation grammar, not delivery to PHP or durability
beyond the server's own acknowledgement. Official references checked2026-10-20:

- [Packet framing](https://mariadb.com/docs/server/reference/clientserver-protocol/0-packet)
  — length/sequence framing; TCP chunks are not protocol packet boundaries.
- [Connection handshake](https://mariadb.com/docs/server/reference/clientserver-protocol/1-connecting/connection)
  — connection ID, capability negotiation and authentication exchanges.
- [COM_QUERY](https://mariadb.com/docs/server/reference/clientserver-protocol/2-text-protocol/com_query)
  — distinguish exact text command from binary prepared-statement traffic.
- [OK packet](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/ok_packet)
  — decode negotiated shape/status, never treat a leading zero alone as success.
- [mysqli options](https://www.php.net/manual/en/mysqli.options.php)
  — command-result timeout is separate from connection timeout; existing source
  sets connect3/read5. Timeout/refusal is not cancellation or a new acceptance window.

A future successful experiment must establish all of these independently:

1. Supervisor exclusively owns listener/process/socket identities and a fresh
   attempt. Each upstream/downstream pair is bound for its entire lifetime to its
   genuine handshake connection ID, not an accept ordinal or reusable ID alone.
2. Existing test-only pre-BEGIN nonce callback observes the actual owned writer's
   thread ID and private original attempt context. Supervisor matches exactly one
   live pair, excludes auth and capture IDs, then arms it once over an out-of-band
   owned control channel. No new runtime callback/provider or protected read.
3. Relay forwards the original exact COMMIT request to that pair's upstream with
   no query rewriting/injection. Complete request framing and byte counts are
   tracked separately from server execution. No pipelining or multi-statements.
4. Relay receives the complete genuine response for that command. Only a supported
   well-formed server OK with expected sequence, idle transaction status and no
   additional-results ambiguity satisfies the server-success witness. ERR, result
   data beginning with zero, partial/unknown packet or EOF before OK cannot qualify.
5. Before any target response byte reaches the downstream socket, relay quarantines
   the response and keeps downstream delivery at exactly zero for that command.
   Neither that response nor a late buffered copy may subsequently drain to PHP.
   A write callback, TCP acknowledgement or observed packet is not PHP API receipt.
   Account for every real socket-write attempt/completion, not a decoder decision
   flag; owner refusal alone cannot prove zero bytes. The reviewed exclusive egress
   path and independent supervisor accounting are explicit witness trust assumptions.
6. Unmodified owner experiences its real native timeout/failure, returns false,
   emits no new current failure guards and disposes its owned slots. The relay
   never supplies a successful native return or a fabricated acknowledgement.
7. Independent direct-backend audit checks the exact owned ledger proposal and
   unrelated owned rows after the response/owner return, with source-derived IDs
   retained only inside the test supervisor. Verify owned sockets/session release
   and lock availability before fixture cleanup. Application inventory/checksums,
   actual user/options digests and fixture absence reconcile independently.

If all hold, the limited claim is **this experiment's server acknowledged COMMIT,
the relay withheld that response, and the client owner refused**. It is not power-
loss durability, global server quiescence, safe retry, current authorization or an
observer grant. An independent stored receipt alone is weaker and cannot replace
the original request/response witness. Never feed audit results back into runtime.

## Narrow transport and privacy envelope

The future relay must bind exclusively to 127.0.0.1 on an OS-assigned private port,
never reuse10004, accept arbitrary upstreams or modify global networking/services.
Use a separately validated fixed backend from the confirmed LocalWP profile.
Do not add TLS termination, install certificates, downgrade encryption/compression,
change users/passwords or capture application traffic. Reject unsupported negotiated
TLS/compression/authentication/protocol forms before arming; report infeasibility
for that environment rather than altering the server or weakening runtime checks.

Pass-through traffic includes authentication and potentially private fixture data.
No packet capture file, raw SQL/authentication/cookie/nonce/payload logging, trace
dump or new public lookup IDs. Opaque authentication buffers must be bounded and
discarded promptly; no claim of cryptographically guaranteed managed-memory erase.
Decode only needed fixed fields, redact diagnostics, and keep transient identity
binding private. Published receipts use compiled labels/booleans/counts, not raw
protocol bytes or connection IDs. A relay cannot prove its own honesty: trust its
reviewed test code, exact source hash and independent supervisor accounting; this
is a controlled witness, not cryptographic remote attestation.
Future arming uses bounded private inherited pipes and an ephemeral attempt token,
not a public control listener, command-line secret or request-supplied thread ID.
The socket-free decoder cannot itself prove the future control channel's provenance.

Proposed proof-only limits, to validate before any future native run: four live
pairs/eight accepted lifetimes per attempt; two MiB per protocol frame, eight MiB
aggregate queued bytes,64 MiB forwarded bytes per attempt and4096 frames per pair.
Existing manifest values cap at one MiB/row and six MiB aggregate; these transport
limits are deliberately narrower experimental support, not new plugin limits.
Reject oversize/continuation at0xffffff, unsupported length encoding, sequence
ambiguity, unbounded handshake or buffer growth; never truncate/favorably normalize.
Support fragmentation/coalescing and byte-exact forwarding in the eventual relay.
No broadened SQL parser or general-purpose proxy feature is needed.

Keep original runtime <10s acceptance and existing connect/read/session limits.
Arming must leave at least six seconds of the original root window for the existing
five-second native read timeout; otherwise abort that experiment before BEGIN/DML.
No reset or extension of the runtime clock. Proposed supervisor wall bound30s and
one attempt only; delayed/missing upstream OK is an inconclusive witness, not proof.
After owner return, cleanup closes only supervisor-owned sockets/processes; any
unresolved session/lock or uncertain fixture ownership blocks DROP and requires
an exact recovery report. No arbitrary process/server/borrowed-auth termination.

## Rejected shortcuts and staged acceptance

| Candidate/observation | Decision |
|---|---|
| Ordinary lock timeout or closed owned client | Useful existing failure proof, not a lost COMMIT response. |
| Throw at a source API seam or reflected native map | Isolated simulation only; do not relabel native provenance. |
| Drop connection before a server response, processlist disappearance or elapsed time | Does not establish server completion; not the selected first witness. |
| Shared database restart, OS-wide packet drop, arbitrary KILL, borrowed auth termination | Reject: broad effects and wrong ownership boundary. |
| Temporarily return true, invent OK/idle/ack, relax native metadata checks | Reject: artificial success/changed subject, not evidence. |
| Relay receives real OK but leaks any response bytes or cannot bind writer lifetime | Failed witness; no retry to salvage the same attempt. |

First implement only a socket-free test helper: bounded protocol framing/handshake/
exact-COMMIT/OK decoder with explicit states and an opaque per-attempt identity.
Use existing uploader Node development tooling; tests supply synthetic byte chunks,
not file transcripts from a database. No net/SQL/WordPress, fixture, credentials,
process launch or relay implementation. It is not another runtime outcome DTO,
classifier, authority layer, schema or production dependency. In-memory recognized
protocol frames do not prove forwarding, zero delivery or real server behavior.

Require positive known-valid handshake/COMMIT/OK and real non-authorizing controls,
then split/coalesced/truncated/oversize frames, sequence gaps/duplicates, wrong
direction/lifetime/thread, pre-auth/early/repeated arm, wrong command/nonce-like
bytes, ERR/EOF/row-leading-zero/unsupported OK flags, and byte/count/deadline caps.
Tests must fail if identity, framing, exact command or full OK validation is removed;
an always-unavailable decoder must fail its positive control. No fabricated success
enters production. Permanent adversarial tests and source restoration evidence
precede a separate relay/supervisor implementation gate.

Only after that decoder is reviewed and green should a separately approved native
harness integrate exclusive listener accounting, no-fault byte-exact positive
admission, refusal/target-isolation controls and finally the single lost-COMMIT-OK
experiment. Exact Plugin Tester target/owned fixture/listener/socket cleanup and
application audit approval is mandatory then. Stop if truthful feasibility fails;
do not expand the experiment or weaken the closed owner to obtain a green result.
BEGIN ambiguity, unknown execute/rollback, ack-with-failed-idle and close uncertainty
remain separate unsupported experiments, not implied by this selected COMMIT case.

## Source-scoped review and verification record

Canonical Feature Light covers three dashboard documents and directly affected
uploader source/test/parent contracts. Other/backend design only; no executable
change. Clarified server-port versus listener-port identity, current supervisor
incompatibility, exact owned-writer selection, delivery versus API receipt,
resource/privacy constraints and private-result observation limits. No open
implementation decision is concealed as proof. Bloat/Structure unnecessary:
zero changed PHP/JS/CSS. No UI/UX or formal DS3/Database/whole-plugin refresh.

Final focused Feature Security follows source/protocol crosschecks and local
regressions: fixed target ownership, unchanged authentication/independent nonce
domains/physical checks, non-transferable witness, no fabricated success/secret
logging, bounded parsing/relay proposal and explicit approval separation reviewed.
No confirmed runtime security defect or regression handoff; prospective parser
adversarial cases above belong to its separately approved foundation. Official
protocol docs are not empirical proof of this candidate. Full pre-release remains
required for the next coherent RC before packaging/publishing.

Executed local regression checks: uploader related DATA/API173 tests/10171 assertions,
no skips, WPCS235 pass (existing PHPCompatibility functionWhitelist deprecation
only). Dashboard299/1423 with two existing local Sodium skips, WPCS138, structure
and build pass; generated assets unchanged. Markdown links/source references and
documentation-only Git diff checked. No native proof, packet replay from a real
connection, SQL or site check ran. Exact dashboard pushed-head Linux CI and toolkit
publication evidence are tracked in the source-scoped checklist entry, not inferred
from earlier CI. Uploader remains byte-identical/clean and is not republished.

Implementation-plan progress: a credible but unproven lost-COMMIT witness route
and its falsifiable go/no-go gates are specified; native evidence is not claimed.
**Recommended next gated slice: local-only V2.3 Socket-Free Commit Witness Decoder
Safety Foundation**, test-only synthetic byte fixtures, validation/commit/push/CI.
Keep actual authentication/storage/listeners/native experiments/production wiring,
grants/observer execution/activation/rollback disabled. No further design-only
maintenance or outcome-DATA layer is needed before this concrete test foundation.
