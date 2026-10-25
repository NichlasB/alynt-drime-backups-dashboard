# V2.3 Genuine wpdb Session/Setup Profile and Source-Rebaseline Design

2026-10-25. Approved DESIGN ONLY: R0 source analysis/R1 Markdown publication.
Dashboard0.1.77/uploader0.5.25 and minimum compatibility unchanged. Excluded
coherent review bases: dashboard98c8ba73e1414a0d5c8c5b6cc3a31982f86f7287,
uploader774e09fd096dc5f752dad3c4350f6d82fa62e8e9, toolkit
07061d540dbfc319af59c074a48e7457bcc6d90e. Parent:
[read-only audit](V2_3_PROJECTION_SESSION_PROFILE_READ_ONLY_AUDIT.md); prior
[driver/cost design](V2_3_REVISED_PROJECTION_READ_ABSENCE_DRIVER_COST_DESIGN.md).
Uploader owns any future separately approved test-only implementation.

## Decision and non-goals

Retain native NO-GO. Specify a new versioned genuine-wpdb session/setup candidate,
not a patch to `projection-session-profile.mjs`, `projection-zero-row-decoder.mjs`
or the current observer owner. All existing fixed synthetic contracts and tests
remain unchanged. Do not strip capability bits, insert a handshake schema, skip
setup, rewrite results/SQL, patch genuine core/driver/cache, or inject an old-profile
context to make the new path pass an incompatible decoder.

No SQL execution, actual authentication, credentials/grants, storage/fixtures,
PHP/WordPress native experiments, LocalWP inspection, processes/listeners, services,
production wiring, activation/rollback, package/release/tag/deploy. Public upstream
text was read in memory only. The prior audit is dated evidence, not renewed site
authorization or a fresh running-profile observation. No Novamira/site tool called.

## Separate identities and evidence levels

Future contracts must separate source catalog, session wire profile, setup family,
data-result grammar, ownership/lifetime and resource-policy identities. One label
or fingerprint must not stand in for all six. A source/member hash identifies
bytes; a thread identifies correlation; neither authenticates a peer or admits a
native action. Every event/credit joins one original attempt/pair/generation/
session/request context, with monotonic ticks and the unchanged external deadline.
Unknown/missing/mixed identities are refusal, never defaults or inferred success.

|Evidence level|What exists now|What remains unproved|
|---|---|---|
|Public reference bytes|Pinned wpdb7.1.3/PHP8.5.12 members below|Installed build/options, active extension/SAPI or full transitive closure|
|Audited installed member match|Prior audit matched one wpdb file to public7.1.3|Whole core/configuration tree, immutable ownership, later changes|
|Declared/configured package|Prior metadata/PE/config snapshots selected PHP8.5.12, MariaDB10.6.23|Running process, server greeting, negotiated flags/plugin/charset|
|Observed owned session|None supplied by this design|Authenticated limited identity/grants, topology, forwarding and result truth|
|Full native admission|None; all A1-A4 open|Source/lifetime/cost/heap/installed ownership and cleanup closure|

Do not discard useful old analysis merely because a version marker changed.
Recheck exact member equality plus predicates/transitive bindings; carry only the
bounded claims actually preserved. In particular, prior7.1.2 wpdb identity and the
new7.1.3 member have the same raw hash. This does not carry whole-archive, bootstrap,
shutdown or installed-tree completeness. Old A1-A4 were never closed to begin with.

## Finite source-rebaseline contract

This is a rebaseline DESIGN, not a claim that the whole source graph is now audited.
Keep a bounded manifest of exact reference/member identities and explicit edges:
unchanged/rechecked, changed/review-needed, added/removed/unresolved. Every carried
effect/read/absence/callback/lifetime/cost claim names its source and profile
predicates. New version labels alone cannot mark an old row current/Complete.

Rebaseline groups: core connection/charset/sql-mode/select/reconnect/query replay;
mysqli API entry/options and mysqlnd connect/auth/packet paths; genuine bootstrap
includes/callbacks/options/cache/locale/widgets/registries; natural shutdown and
engine/extension lifetimes; external immutable source/configuration ownership.
The seven references below seed only the connection/profile group, not all groups.
No copying trees, freezing ACLs, installing packages or changing source ownership
mechanisms is authorized. Before/after hashes alone do not prevent mid-run mutation.

Raw UTF-8 public bytes retrieved this task, without evaluation or disk extraction:

|Reference member|Bytes|SHA256|
|---|---:|---|
|WordPress7.1.3 wp-includes/class-wpdb.php|121437|E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD|
|PHP8.5.12 ext/mysqli/mysqli_api.c|55351|29E23960A109B5F8B3FB268F1F44AA9B400E5495DAC788981AA32978B0BD4941|
|PHP8.5.12 ext/mysqli/mysqli_nonapi.c|33465|7A60F688BCBC2BB3F4F0990D0C235A43707C3BA5F6B8A3B3FAE67A2B9B905C23|
|PHP8.5.12 ext/mysqlnd/mysqlnd_connection.c|69567|9A00E4689676D29BD3E69630AF50477B2309F6D685F87FC6F580FD745E450430|
|PHP8.5.12 ext/mysqlnd/mysqlnd_auth.c|47207|2E1E1C92227A8D451E7A05EA0290D4B0F8A56F1A171EAE3E2ECE2710AA96E67E|
|PHP8.5.12 ext/mysqlnd/mysqlnd_wireprotocol.c|94756|BC0121D607070261B0CA90C3DFD6D6A75972F57F2279F28B120E4780EA6B55E8|
|PHP8.5.12 ext/mysqlnd/mysqlnd_enum_n_def.h|20820|27689DC30FDD4FFA3AF386FD24EA83A5C63F677F2F1E1F4F5E8D12875C636585|

Primary sources: [wpdb](https://raw.githubusercontent.com/WordPress/WordPress/7.1.3/wp-includes/class-wpdb.php),
[mysqli entry](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqli/mysqli_api.c),
[mysqli connection](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqli/mysqli_nonapi.c),
[mysqlnd connection](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_connection.c),
[auth](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_auth.c),
[wire encoder](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_wireprotocol.c),
[definitions](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_enum_n_def.h).
Read methods/branch predicates, not web-rendered line numbers; raw file identities
are the reference. Binary source/build equivalence remains an independent gate.

## Genuine setup state and result requirements

Proposed profile-family name `wpdb-postconnect-setup.v1` is a design label, not a
new exported API, source certificate, supported wire mask or native receipt.
Selected schema must remain unestablished until its later selection completes.

|Stage/family|Required boundary|Refusal/unknown case|
|---|---|---|
|Initialize|One source-bound fresh nonpersistent core handle, original root/epoch|Existing/reused/unbound handle or unqualified persistence|
|Connect/handshake|Core supplies no database; preserve genuine driver negotiation|Old handshake-schema substitution, altered flags or unsupported profile|
|Authentication outcome|Exact supported response on the owned pair; no raw auth retention|Error/switch/unknown/extra exchange; thread or username alone is not authority|
|Charset setup|Source-qualified API work and each actually emitted explicit statement/result|Unbound API-to-wire mapping, failed/unknown outcome or hidden direct work|
|Mode read|Successful source-qualified one-value result and a fixed typed branch|Failed/no result, missing row, malformed/multiple rows or unexplained value|
|Mode branch|Successful source-empty branch has no mode-write; nonempty branch requires its exact filtered write/result|Treating failure as empty, skipping a required write or accepting generic SET|
|Database selection|Exact owned schema selection and completed non-error response on same session|Assuming schema from old hello/context, wrong schema or failed/partial selection|
|Selected data|Only after all selected setup obligations complete, separate request/result grammar|Early N credit, unknown metadata/profile, extra result, replay or alias|
|Terminal join|Keep source/observer ownership through natural shutdown/engine/process/both EOFs|Early close, marker/QUIT/exit0 alone or unobserved suffix|

The source sequence has explicit branches; do not turn this table into a universal
fixed command count. `wpdb` may return from mode handling on a failed read, or on
a successfully fetched PHP-empty first value. Those are NOT equivalent proof
states. A native candidate rejects failed/unknown results even if core continues.
Do not label a zero-row result, empty string, '0', NULL and error interchangeably;
only separately source-qualified value semantics can choose the empty branch.

Mode filtering depends on fixed source/hook/configuration predicates. An unresolved
filter or dynamic mode value cannot become a generic value/SQL allowance. The
next DATA model may use invented bounded branch enums; that is not a packet
decoder or real SQL-mode result proof. A future wire parser needs its own exact
grammar, metadata/row/status/warning/more-result and allocation bounds.

Capability advertisement is distinct from permitted command/result behavior.
The mysqli real-connect source adds MULTI_RESULTS and removes MULTI_STATEMENTS
from its connect flags; mysqlnd then combines defaults/options. This is not a
negotiated mask from the installed site or permission for multiple statements.
Define a finite source-qualified negotiation profile only after its required/
optional/forbidden bits and emitted shape are established; unknown stays closed.
Do not strip defaults, toggle server/driver features or reuse the old exact mask.
Plugin, auth-response length, greeting charset and client maximum remain unobserved.
Driver advertisement, server16M, old16KiB model and parser-allocation ceiling are
different domains; neither3GiB upstream default nor config256M is a native budget.

The old ordinary-EOF zero-row decoder also fixes the old capability context.
Do not pass a translated/forged old context to reuse it. A successor data-result
profile must independently bind its actual session/capabilities and supported
termination grammar; exact bounded field validation may be reused only through
a separately reviewed versioned composition. Current code remains unchanged.

## Accounting, lifetime and failure discipline

Keep source/API work, emitted protocol operations, successful results, distinct
absence/cache history, verifier activity and whole-request resources separate.
API-to-wire can be zero/one/many; unknown is not zero and an API call plus its
emitted statement must not be counted twice in one domain. Query-filter visits
do not attest direct charset/mode/select/probe operations or hidden query replay.
No new native query/byte/time/memory allowance follows from setup-family count.

New connection, reconnect, replay, probe or second handle poisons the proposed
normal-path candidate until separately source-qualified/admitted. Do not disable
genuine error/reconnect behavior. Keep original deadlines/cumulative budgets;
failed attempts and late shutdown work are not discarded or granted a new epoch.
Existing workers,32-query/4096-SQL-byte fences, catalog P29/N8, private byte-copy
and capture limits remain untouched; these are not whole-request admission proof.

## Next bounded implementation and non-vacuous tests

Recommended next gated slice: **local-only V2.3 Genuine wpdb Source-Profile Catalog
and Setup Admission DATA Safety Foundation** in uploader, in-memory only. Minimal
fixed primitive source catalog/evidence levels and ordered setup-family model;
no SQL/packet parsing, driver calls, actual authentication, source-tree mutation,
storage, native execution or observer/worker/production wiring. Not a generic
tracing framework or a native-profile implementation.

- Fixed catalog/lineage distinguishes old synthetic, new public-reference and
  prior-audited-member evidence. Unknown installed/build/observed-session/ownership
  states cannot be set complete by caller labels, hashes or guessed constants.
- Positive synthetic sequences for separately selected empty/nonempty mode paths
  must reach modeled completion through the actual helper. Their actual source/
  auth/native/forwarding/backing/resource/storage/wiring/execution claims stay false.
  An always-unavailable helper must fail positive tests; no artificial native path.
- Require later database selection, branch-specific mode write, separate family/
  API/result accounting, exact private contexts/original virtual epoch and sticky
  refusal. Old-profile/schema-in-hello, foreign/stale identities, missing/reordered/
  duplicate stages, failed-as-empty, unknown enums/getters, expired/replayed/extra
  activity and mixed-source evidence must refuse. No credentials or raw SQL output.
- Source-backed semantic controls must expose removed source/branch/order/result/
  epoch/refusal guards by assertion failures, not import errors. Synthetic fixtures
  do not establish genuine WordPress authentication, storage or physical proof.

After that separately approved foundation, reassess finite source-rebaseline
residuals before seeking actual profile/native authority. A1-A4, owned immutable
source mechanism, build/SAPI/profile compatibility, bounded buffers/resources,
genuine limited identity/grants and physical cleanup remain separate gates.

## Source-scoped review and publication

Canonical Feature Light: five Markdown files (owner, both plans, audit successor,
companion foundation pointer), Other/design; unchanged sources/helpers are only
contract references. Draft clarifications: no old-mask laundering and no failed-
mode-read-as-empty success. No executable defect/fix or new automated test claimed.
No meaningful Bloat/UI/UX trigger or whole-plugin DS3/Database/physical/FULL refresh.
Final focused Feature Security after validation: no new authority, credential,
SQL, endpoint or IO path; no confirmed scoped security-fix regression handoff.

Existing synthetic tests and exact-head CI are preservation/publication evidence,
not implementation or native proof. Actual counts/run links are source-scoped in
toolkit evidence. Restore OptionA checked clean uploader774e09f/existing external
20261024-161220 archive; no snapshot/checkpoint/restore created. No LocalWP source/
configuration inspection renewed by this repository-only design.

Implementation-plan progress: source-reuse rules and genuine post-connect setup/
result obligations now specified, while full rebaseline and native GO remain
Blocked. Next action is the in-memory DATA foundation above, not a native rerun.
