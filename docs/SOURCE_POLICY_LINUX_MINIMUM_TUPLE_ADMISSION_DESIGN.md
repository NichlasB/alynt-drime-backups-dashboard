# Linux Minimum-Lab Exact-Tuple Admission and Source-Ownership Integration Design

Proposed evidence-strategy revision: [trusted-lab qualification replan](SOURCE_POLICY_MINIMUM_TRUSTED_LAB_REPLAN.md).
Its reduced attestation/process/cache claims require explicit acceptance before
implementation. The private receiver/three-consumer integration below remains
unimplemented; expected DATA and a design approval mint no lab/runtime grant.

## Authority and decision — 2026-10-30

Approved repository-only design at clean/synced baseline
`026427bab5c0f8338096d50171094b43b23d470a`. The
[candidate feasibility design](SOURCE_POLICY_LINUX_MINIMUM_CANDIDATE_FEASIBILITY_DESIGN.md)
owns the immutable donor/core/server references and unmet artifact obligations.
This slice designs their lab-only admission; it implements or admits nothing.
Only this record, its plan entry and predecessor pointer change. Ordinary trusted
repository QA, commit/push and exact-head CI are authorized. No downloads,
provisioning, candidate/core execution, containers, workflow dispatch, SQL,
authentication, fixtures, site operations, credentials/permissions, packaging,
release/deploy or production/form wiring. Site Operations was consulted; no
site target or Novamira operation applies to repository-only design.

**Select a closed, receiver-bound lab scope, not a broader core-to-server map.**
The finite proposed row is Linux/amd64, PHP/mysqlnd 7.4.33, genuine core 6.0
commit `cc101b64012b16d087780657a2b828ccd7794a63` / tree
`a3bd7a4074a4c95c8af3b969346726273d68e167`, the candidate's exact runtime
platform-image and MariaDB image digests, full server
`10.11.18-MariaDB-ubu2204`, TCP `127.0.0.1:3306`, null socket, initial mode 0.
Future source DATA must copy the full immutable digest values from that owning
record, never resolve a mutable tag. Consistency, runtime admission and native
qualification remain distinct; the candidate is still payload-unverified.

Follow-up: the approved [socket-free DATA foundation](SOURCE_POLICY_LINUX_MINIMUM_TUPLE_FOUNDATION.md)
implements only the fixed descriptor/correlation layer with independent positive
and assertion-killed semantic tests. It does not implement the receiver scope,
change legacy admission or supply artifact/native qualification evidence.

For the first synthetic foundation, select a proposed DATA domain `ADBLMT1`
with exactly these 17 keys: `format`, `platform`, `arch`, `php`, `client`,
`core`, `commit`, `tree`, `runtime_image`, `server_image`, `server`, `transport`,
`host`, `port`, `socket`, `db_host`, `initial_mode`. Fixed values are the row
above (`Linux`, `x86_64`, `mysqlnd 7.4.33` for those corresponding fields), with
strict integer 3306/0, null socket and no coercion. Require the exact key set,
canonical source-defined ordering for hashing and no executable/path values.
This is an expected candidate descriptor, not a runtime-build attestation.

The proposed bounded ownership-correlation transcript has exactly `event`,
`head`, `source_image`, `run`, `pid`, `tuple_hash`: literal event `tuple-data`,
lowercase 40/64/32-character hex head/image/run, positive integer PID at most
2,147,483,647 and the canonical fixed tuple's SHA256. Compare every field with
the independent parent's expected DATA; reject wrong types/extra/missing keys.
Tests use synthetic identities, not claimed observations. A match returns only
consistency; no runtime/qualification/dispatch flag, scope object or grant is
minted. A caller matching its own expected hash proves correlation only. The
actual source-image byte verification and provenance remain separate later work.

Preserve `Transport_Profile::server('6.0') === '10.6.23-MariaDB'`, its 7.1.3 row,
the 7.1.4/full-build/socket row, existing three-argument profile semantics,
public factory refusals, PHP/core minimum declarations and ADBC1/ADBC2 rules.
No suffix stripping, core relabeling, version prefixes, Linux-OS-only exception
or support-matrix expansion. Only the fully bound lab receiver may eventually
use the new exact build expectation; ordinary core-6.0 admission still rejects it.

## Actual source trace and affected consumers

| Existing boundary | Present contract | Future lab obligation |
| --- | --- | --- |
| [`Bootstrap_Profile::settings/source`](../includes/class-source-policy-bootstrap-profile.php) | Exact 12-key settings, finite transport, source/reflection/cache/hooks/driver bindings; not a distribution grant. | Preserve all checks; bind scope to the same frame, audited source image/runtime observations and query owner before allocation. |
| [`Native_Bootstrap::bootstrap`](../includes/traits/trait-source-policy-native-bootstrap.php) | Null-default private frame; actual server checked before setup; final facts admission before active/ready. | Same immutable selection at both actual-server comparison and final admission; no bootstrap-only exception. |
| [`No_Reconnect_Connection::admit_profile`](../includes/class-source-policy-no-reconnect-connection.php) | Active/ready/report/context checks then retained facts admission, used by preparation/query wrappers. | Retain non-secret scope after bootstrap inputs clear; revalidate for each phase and revoke before close. |
| [`Native_Profile::admit`](../includes/class-source-policy-native-profile.php) | Exact 11-key facts, full core/server map, schema/cache/hooks/context; returns owner/autoload/states DATA. | Default remains legacy; only an independently checked exact receiver can select the fixed lab expectation. |
| [`Native_Adapter`](../includes/class-source-policy-native-adapter.php) | Closed provisioning; repeated connection admission around observation, write and callback phases. | Same adapter, SQL/revision/error/uncertainty and repeated guards; no alternate lab writer or ready-handle injection. |
| [`Query_Context`](../includes/class-source-policy-query-context.php) | Source-bound owner, sealed callbacks, owns/valid/finish, no public provider. | Exact same receiver/context instance for bootstrap, retained scope and subsequent admission; strings cannot confer ownership. |

The connected-server comparison, final bootstrap admission and per-query
admission are three separate consumers. Updating just one cannot suffice.
`profile()` exports qualification DATA; do not add scope objects, contexts,
credentials, paths or handles to its 11-key array. Adapter revision/conditional
save semantics remain unchanged. Scope is private receiver state, not a command
fact, caller-selected expected server, environment flag or option.

## Finite source ownership: three distinct layers

1. **Socket-free tuple DATA.** A fixed versioned row compares bounded exact
   scalar/array values with source-owned constants: candidate platform and
   database-image digests, PHP/client/build expectations, core/commit/tree,
   endpoint and mode. No callable, resource, executor, receiver, arbitrary path,
   alternative row, fallback or IO. It returns consistency DATA, never ready,
   qualified, grant or connection results. Binary/module hashes and OCI config
   remain unknown; do not invent them or compare caller-provided expected hashes
   as proof. A positive synthetic match is useful DATA coverage, not admission.
2. **Source/observation binding.** Later approved artifact audit/preflight must
   establish actual config/dependency and loaded-source identities. A versioned
   complete source image binds exact repository head, loaded includes/traits,
   genuine core dependencies, lab descriptor/pins, worker/controller, launch/
   preflight/cleanup sources and manual workflow/action pins. Require a finite
   allowlist, canonical ordering/root labels, path containment, no symlink escapes
   or extra includes and byte/hash checks before loading. Include PHP/mysqli and
   actual built-in mysqlnd identity, not a fabricated `mysqlnd.so`. Preserve the
   6.0 11-file pin set; Windows `php7.dll` is not Linux evidence.
3. **Private receiver scope.** Retain audited non-secret descriptor and exact
   receiver/query-context identity once before private bootstrap; null by
   default. No setter, public mint/factory, filter, request/environment selector,
   serialized receipt or DB option establishes it. A later exact owned proof
   entry may compose it privately only after parent audit/preflight passes,
   like its existing frame/query bindings. This privilege must not become
   application provisioning or enable public factories.

The [proof-reflection helper](../scripts/manual/source-policy-proof-reflection.php)
and [Linux worker](../scripts/ci/source-policy-linux-qualification.php) already
compose private inputs in a trusted proof. That is **not** tamper-proof PHP
capability security: arbitrary PHP/reflection in the process could also alter
private fields. Exact image/source/run receipts correlate, not authenticate or
sign, observations. They cannot protect against a malicious host/runner/plugin;
the approved lab host, Docker engine/kernel and distribution trust remain
prerequisites. Neither a hash/path nor `GITHUB_ACTIONS` alone establishes trust.

## Selected future integration contract — not implemented

- Add only a private nullable scope slot on the actual receiver. A private
  dispatcher returns the unchanged legacy server mapping for null. A non-null
  scope must pass the complete fixed lab binding; malformed/stale scopes refuse
  without fallback or source-fingerprint refresh. Do not infer scope from facts.
- Preserve `Native_Profile::admit`'s current three arguments/defaults. A future
  optional fourth **exact receiver** is the bounded internal lab interface,
  never an expected-server string, facts map, arbitrary provider or selector.
  Non-null wrong objects, subclasses, unbound and closed receivers refuse
  without invoking overridable collaborators. Legacy paths keep the current
  call shape; only a non-null private lab branch passes the receiver.
- If cross-class access is needed, one narrow final receiver consistency
  accessor takes no selector/expected tuple and returns only its fixed lab
  expectation or refusal. It validates retained scope, exact class/source,
  same context ownership/validity and immutable frame/facts bindings. It never
  exports scope/context/credentials/handles, opens a session or changes ready.
  Null scope cannot produce a lab expectation. This is DATA, not a public grant.
- Initial actual-server and final facts checks use the same selection;
  `mysqli_get_server_info` must match byte-for-byte. Later admission compares
  retained core/server/database/table/session/source/report bindings. The lab
  consistency check must support the same `connected_unqualified` transition
  for final bootstrap admission and active state for later calls; requiring
  already-active/ready before bootstrap completes would make success impossible.
- Successful bootstrap clears input frame/binding/password but retains this
  non-secret scope, not reconstructed exported facts. Failure/close revokes it
  before fallible/reentrant cleanup with qualification/report state. Replay,
  context/receiver substitution and post-close reuse permanently refuse.

No global profile registry, alternate wpdb subclass, duplicated adapter, dynamic
executor or factory framework. These are bounded planned internal API changes,
not approval to implement them now. The first foundation below is DATA only;
runtime integration waits for actual observations supporting source ownership.

## Path, resource and evidence boundaries

The [minimum pin helper](../scripts/manual/source-policy-supported-core-pins.php)
and [supported-profile helper](../scripts/manual/source-policy-supported-profile.php)
retain their Windows/runtime fields and historical receipts. A future Linux
helper may explicitly reuse only core-6.0 commit/source pins. Do not overwrite
the newer [Linux core pins](../scripts/ci/source-policy-linux-core-pins.json),
worker/preflight/terminal contract or turn them into a caller-configurable runner.
Their PHP 8.5.12/core 7.1.4/socket/two-shared-module assumptions and 282/9
historical proof do not qualify this minimum profile.

Development DATA/worker sources stay outside plugin bootstrap/runtime imports.
Eventual dormant `includes` code cannot depend on excluded `scripts/tests/docs`;
[`deploy.sh`](../deploy.sh) excludes those directories. No packaging/deployment
is run or changed. New executable sources would stale previous whole-source
proofs for that head; revalidate the appropriate cohort under later explicit
proof approval, not by relabeling old Windows/Linux or ordinary matrix CI.

Preserve the existing 20-second/16-credit bootstrap envelope, finite eight-step
setup, connect/read limits, no reconnect/reissue, exact observers, report-OFF/
ambient restoration, schema/charset/session, uncertainty and cleanup rules.
No extra query, introspection scan, retry, budget increase or pull follows here.
Full source-image validation belongs to bounded audit/preflight, not an
unbounded per-query filesystem traversal. Compare retained bindings without
silently resealing; before/after physical-proof source readbacks stay mandatory.
Fix finite path/byte/file limits before loading actual artifacts, not from
guessed payload contents. Candidate-128/tool-135 Windows contracts stay intact.

The predecessor's namespace, mounts, image-volume overrides, trust and owned
cleanup obligations remain open. No new artifact lookup or physical observation
was performed for this design; it is source analysis, not environment admission.

## Non-vacuous acceptance and next approval

The next foundation needs an accepted **synthetic DATA consistency** case plus
independent negatives; an always-unavailable owner does not prove this boundary.
Do not manufacture a native success path to obtain coverage.

- Independently change every fixed platform/architecture/PHP/client/core/commit/
  tree/image/server-suffix/endpoint/socket/report field. Reject missing/extra
  keys, wrong scalar types and prefix-equivalent values. DATA success must still
  entail zero IO, handles, grants, runtime qualification and production changes.
- Preserve every legacy positive/negative row, especially unscoped core 6.0 +
  10.11.18 rejection, wrong-core/socket rows and public unavailable factories.
- Future integration must cover all three admission sites, scope retention after
  bootstrap clearing, pre-dispatch guards, malformed non-null no-fallback,
  cross-owner/context/source substitution and revocation before reentrant or
  failing close. Never call hostile subclasses/callbacks or leak handles.
- Controlled semantic removal/bypass of each required guard must kill the
  appropriate assertion, not only change counters. Restore/verify exact bytes.
  No weaker assertions, fake provenance, ignored denial paths or global scope
  flags. API doubles are not genuine authentication, storage, physical release
  or transaction proof. Existing tests remain unmodified in this design slice.

**Recommended next gated slice: repository-only Socket-Free Linux Minimum-Lab
Tuple DATA and Source-Ownership Contract Safety Foundation**, synthetic fixtures/
isolated tests only. Implement the fixed row and bounded ownership-transcript
DATA with positive/negative and guard-kill tests; keep runtime receiver
integration, actual artifact/source qualification, workflows and execution
disabled. This implements the selected contract without pretending unknown
binary facts are observed. Artifact acquisition/static verification, genuine
connection-free preflight and native receiver/proof integration retain their
later bounded approvals; none follows automatically from this design.

Implementation-plan progress: resolves where/how the lab-only full-build
exception is scoped without changing legacy acceptance. Minimum native
qualification stays open, Windows observer work deferred and factories/wiring
disconnected. Historical count stays 1/8 plus separate proof work; no product
or native completion claim.

## Validation and publication

Only three owning docs change; uploader/toolkit stay clean/read-only at
`3dfcb15b5cab8191df7d984251986ead140ded52` /
`c067eb61883035a1764d830bccffc1e542d3e1dc`. Executed ordinary trusted repository
QA with existing PHP 8.5.1, not the candidate or genuine core/native environment:

- `php vendor/bin/phpunit --do-not-cache-result --filter
  'SourcePolicyTransportProfile|SourcePolicyNativeAdapterContract|SourcePolicyBootstrap|SourcePolicyNoReconnect|SourcePolicySupportedProfile|SourcePolicyLinux'`:
  372 tests / 1,727 assertions, no failures/skips. These existing isolated tests
  check current contracts; they do not implement/test the proposed new DATA row.
- `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,325 tests /
  21,007 assertions, no failures; two existing Sodium-unavailable signing skips.
- `npm run lint`: PHPCS 243 files. `npm run test:admin`: 23/23, no failures/skips.
  `npm run check:structure`: 149 runtime / 233 PHP support / 224 test files /
  221 class names. `npm run build`: passed, no tracked generated delta.
- Strict Composer validation, Composer audit and npm moderate audit passed;
  zero known dependency advisories/vulnerabilities. No dependency changes or
  candidate-image audit/clearance is implied.
- 165 owning local documentation targets and whitespace checks passed. Final
  source/provenance, compatibility, error/resource/cleanup and test-integrity
  review confirms all executable/test/profile/manifest/workflow files unchanged.
  A rejected patch anchor made no partial changes; it was corrected before QA.

Design `612b65ef39eb650a761206e4e2e5257ed10cf86e` is pushed;
[exact-head ordinary CI 41132623673](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41132623673)
succeeded. Actual PHP 7.4: 2,325 tests / 21,009 assertions; PHP 8.3: 2,325 /
21,014, no failures/skips on either. Admin 23/23 each, no failures/skips;
manifest, dependency audits, build, structure and PHPCS passed, zero known
dependency advisories/vulnerabilities. Existing Setup Node `punycode`/
`url.parse()` deprecation warnings remain tool-level warnings, not suppressed.

This docs-only evidence closeout receives its own pushed exact-head ordinary CI
and clean/remote verification at handoff. No ordinary test result establishes
new artifact, receiver scope or native admission.
No canonical whole-plugin review, physical proof or release readiness closeout;
no toolkit checklist or historical whole-plugin review row is refreshed.
