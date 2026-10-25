# V2.3 Driver/Session Observation Feasibility and Provenance Integration Design

Successor implemented2026-10-25: uploader owns
docs/V2_3_PROJECTION_ZERO_ROW_PROVENANCE_FOUNDATION.md. Separate test-only
socket-free selected request/zero-row decoder and private synthetic provenance;
this design's native limits/NO-GO remain. Next gated design: Fixed Projection
Observer Adapter and Session Provenance Integration Design. Actual worker/storage/
native execution/production wiring unchanged; historical design record follows.

2026-10-25. Approved repository-only DESIGN, R0 analysis/R1 publication. No actual
worker, storage, native execution or production wiring change. Versions remain
dashboard0.1.77/uploader0.5.25; no package/release/tag/deploy or real admission.
Excluded coherent review bases: dashboardad4e780163f6c7e8b92fe627ee005b707eb5c4dd;
uploaderf8ef7e81a48b2c901224ba9eff34202276bf5b2b; toolkit
12c824a7e52b54db3571ab319ef0d8be860cff16. Owner is this dashboard document.
Parent: [revised admission design](V2_3_REVISED_PROJECTION_READ_ABSENCE_DRIVER_COST_DESIGN.md).
Uploader owns docs/V2_3_DISTINCT_ABSENCE_COST_LEDGER_FOUNDATION.md and test helpers.

## Decision and feasibility boundary

An external passive classic-protocol observation lane is conditionally feasible
as a repository-only synthetic candidate. The EXISTING relay/witness is NOT a
qualified projection read/result observer. Actual native feasibility remains
unproven and NO-GO. Do not compose the supplied core/verifier labels or outcome'ok'
with a native receipt as though those labels establish source/handle/result truth.

Prefer a new small socket-free zero-row response/provenance foundation before
any transport or worker integration. Reuse inspected framing/context patterns,
not old success claims, root-role bindings, decoder limits or COMMIT semantics.
Do not replace genuine wpdb/cache with a subclass/drop-in, patch vendor core,
monkeypatch driver APIs, rewrite SQL/results or change callbacks to manufacture
observability. No server log, plugin, privilege, TLS or instrumentation change
is authorized by this design.

## What the inspected repository actually establishes

|Existing source|Useful pattern|Not established for projection|
|---|---|---|
|projection-core-bootstrap.php|Fixed original eight-key query fence and genuine cold cache checks|Direct driver calls, successful result status,29P/8N or full teardown observation; it closes wpdb after its current bootstrap receipt|
|relay-no-fault-frames.mjs|Bounded classic frame reassembly/hash accounting and narrow handshake gate|Command/result-set semantics, every connection or installed endpoint ownership|
|relay-no-fault-server.mjs|Owned loopback pairs, per-frame symbols, forwarding callback/account and stream closure|Current success expects3..8 pairs; not the desired projection topology or role proof|
|observation-passive-sink.mjs|Exception/refusal latch; observer returns do not control forwarding|A security membrane for arbitrary/untrusted observers or immutable Buffer access|
|observation-native-registry.mjs|Private pair symbols, unique greeting threads, boundaries/credits and role lifetimes|It binds three root/local auth/capture/writer roles and a selected COMMIT/OK/drain, not limited-account projection sessions|
|native-witness-profile.mjs / commit-witness-decoder.mjs|Fixed handshake subset and context-bound selected-byte grammar|General SELECT completion, zero rows, option names, backing absence or resource closure|
|distinct-absence-cost-ledger.php|Non-vacuous typed event/accounting model with all native claims false|An actual byte producer, authentication/handle seal or source/physical observation|

In particular, accepting a command opcode before the selected COMMIT is not
validation of its SQL or result. The registry's accepted writer opcodes include
database/query/ping/prepared-command families; that is not a new read allowlist.
The generic gate rejects server0xfb, including ambiguous NULL-leading rows;
that conservative old limitation is not a general result parser. Do not relax it
to make a new proof pass. New contracts must remain separate from old native
manifests, selected-loss experiments and their source identities.

The transport passes mutable frame Buffers to its fixed observer before write.
Ignoring observer return values/catching exceptions does not by itself prevent
byte mutation; stream hashes can detect mismatch after forwarding. This is a
trust-boundary inference from inspected code, not a new production vulnerability
claim. A future adapter must remain fixed/source-bound and read-only over bytes,
with bounded isolated copies or equivalent proven separation and independent
transport equality. No arbitrary caller callback, buffer escape or dynamic policy.
Any additional copy/allocation belongs in its cumulative resource accounting.

## Provenance chain required for future native evidence

1. **Source/profile root:** independently fixed worker/core/observer versions,
   installed configuration and supported driver/capabilities. A model-label hash
   or self-reported source field cannot attest installation. A1-A4 stay open.
2. **Owned transport lifetime:** parent-owned attempt identity, private pair
   identity/generation and exact endpoint topology. Greeting thread IDs are useful
   correlation, not authentication or proof of global process connection coverage.
   Extra/reused/reconnected/unbound pairs refuse normal-path qualification.
3. **Limited session identity:** bind actual handshake username/auth outcome and
   selected owned schema to that pair, then reconcile with independently scoped
   owned identity/grant evidence. Do not reuse the root/local registry or infer
   core identity from a verifier handle. Do not expose credentials/auth bytes.
4. **Command association:** exact generation/direction/sequence and one outstanding
   request; source-approved SQL/name/schema or fixed control family, not a generic
   SELECT/SET prefix. Bind request ingress AND completed forwarding credit; a
   parse callback or PHP query-filter visit alone is not execution/delivery proof.
5. **Result completion:** bind response metadata, row count and final non-error
   termination to that request/pair/capability profile. Refused/partial/error/
   additional-result responses cannot become successful empty results.
6. **Backing and cache relation:** wire zero rows proves only that response on
   that projection. Separately establish genuine owned backing absence and
   collation/alias safety, then earned genuine cache history. A hidden view row,
   an empty stored value or repeated same-name result is not distinct absence.
7. **Lifetime join:** keep source/observer/query fences through core/cache shutdown,
   driver/engine teardown, process exit and both EOFs, with original deadline and
   owned cleanup. No candidate marker, QUIT, pair-close or exit0 alone earns GO.

A future bridge must derive ledger events from independently bound evidence;
it must not merely validate an event object supplied by the same worker that
asserts success. Wire observation cannot see cache/API calls or driver API-local
work directly. Join separately source-qualified API/cache evidence with commands
without double-counting; missing domains remain unknown, not zero. Observing one
relay does not prove there are no bypass connections. No routing/DB_HOST change,
socket, SQL, source or privileged inspection occurs in the present task.

## Narrow response grammar, not COMMIT OK reuse

MariaDB distinguishes COM_QUERY's error, non-result OK, local-file request and
result-set responses. Thus an OK alone cannot certify SELECT zero rows.
[COM_QUERY reference](https://mariadb.com/docs/server/reference/clientserver-protocol/2-text-protocol/com_query).
Database selection is a separate COM_INIT_DB command with its own outcome;
handshake schema and later selection must not be conflated.
[COM_INIT_DB reference](https://mariadb.com/docs/server/reference/clientserver-protocol/2-text-protocol/com_init_db).

A text result carries column metadata and rows, then an EOF/OK terminator according
to negotiated capabilities; it can instead terminate with error. A0xfe byte is
not sufficient to classify a packet without context and length.
[Result-set reference](https://mariadb.com/docs/server/reference/clientserver-protocol/4-server-response-packets/result-set-packets).
These public protocol references were checked, not an installed MariaDB profile
or native capture. No assumption of MySQL/MariaDB version interchangeability.

First candidate: a separately versioned one-column text SELECT zero-row grammar
under an explicit ordinary-EOF profile. Require canonical bounded column-count,
one complete expected column definition, metadata EOF and immediate final EOF
with no rows/errors/warnings/more-results, contiguous sequences and exact request
association. An empty string, NULL or '0' row is still a row and must refuse.
Unsupported EOF-deprecation, cached/extended metadata, encryption/compression,
prepared/binary/multiple results, local-file/auth-switch/unknown states refuse;
do not toggle genuine client/server features to fit the parser. Exact protocol
flag/metadata byte fixtures must be pinned in the next synthetic implementation.

Do not claim that this decoder covers bulk two-column29-row hydration, variable
SQL-mode result values, all session setup or reconnect/replay. Those need their
own fixed source/response grammars and feasibility decisions. In particular,
mysqli API intent and emitted command are distinct cost domains; no complete
mapping is inferred from command names or the illustrative six-event ledger.

## Small next implementation and proof obligations

Recommended next slice: **local-only V2.3 Socket-Free Projection Zero-Row Response
and Provenance Safety Foundation** in uploader. Synthetic bytes, primitive context
and virtual clocks only. No transport import/socket/process/auth/storage/native
operation or actual worker/ledger bridge. New test-only code and permanent tests
may use existing decoder conventions without modifying old native source groups.

- Fixed factory/context with private synthetic attempt/pair/generation identities,
  selected schema/name/request identity and original epoch. Reject global/reused
  tokens, stale generations, unknown enums and untrusted getter/callback contexts.
  Bind the fixed v2 N-name domain/identity and owned prefix derived from the fixed
  seed; P names, aliases, free SQL and caller-supplied allowlists cannot qualify.
- Bounded header-first parsing before payload allocation. Proposed fixture-only
  ceilings:4KiB per frame,16KiB total/eight frames per selected exchange; no change
  to current transport/worker budgets or claim that those limits fit native input.
  Exceeding any bound latches refusal; fragments do not earn result credit.
- Positive full zero-row exchange and all split boundaries, plus row-bearing
  controls. Errors after metadata, wrong count/column/profile/schema/name/session,
  malformed length encoding, reordered/repeated packets, truncated EOF, extra
  results, stale/replayed request, expiry and post-match activity must refuse.
- Source-backed guard controls must expose broken association/result/length/
  deadline/refusal guards. Do not introduce a fake native success path merely
  for coverage. All native/auth/backing-absence/forwarding/delivery/resource/
  storage/wiring/execution claims remain false, even on synthetic positives.

This next foundation is NOT implemented by this design approval. Actual profile
selection, pair routing, supported driver proof, observer attachment, buffers,
owned fixtures/accounts/grants and native transparency/cleanup need separately
approved scopes/target confirmation. No response suppression or fault injection
is recommended. Native readiness still requires all A1-A4, not decoder success.

## Scoped review and verification record

Canonical Feature Light: five Markdown files (owner, both plans, parent design,
companion foundation pointer), Other/design; inspected unchanged helpers/relay/
worker are contract references, not modified runtime. Two central clarifications:
the selected witness does not parse result sets, and passive exceptions/return
isolation is not arbitrary observer byte immutability. No executable fix or new
automated-test coverage claimed. No meaningful Bloat/UI/UX trigger; no whole-
plugin DS3/Database/physical/FULL refresh. Historical evidence is not overwritten.

Final focused Feature Security after diff/preservation validation: no introduced
scope defect or security-fix regression handoff. Fixed trust/provenance boundaries,
no new credentials/endpoints/SQL/handles or authority path; plaintext auth/raw
result bytes must never enter support logs. Existing trusted observer mechanics
are not reclassified as an untrusted public API. Installed/version/cost/physical
limits remain unresolved and explicitly block native GO.

Exact executed checks/CI/log counts are recorded source-scoped in toolkit evidence.
Restore OptionA verified clean uploaderf8ef7e8 and existing external20261024-161220
archive, no snapshot/checkpoint/restore created. Static Site Operations/registry
only; no site/LocalWP/Novamira/native/SQL inspection or operation. No release.

Implementation-plan progress: observer feasibility decomposed and existing reuse
limits established; actual observation/provenance integration remains Blocked.
Recommended next action is the socket-free zero-row/provenance foundation above,
keeping actual worker, storage, native execution and production wiring unchanged.
