# Connected mysqlnd Counter Semantics and Release-Evidence Design

## Authority and baseline — 2026-10-28

Approved repository-only design through validation, commit, push and exact-head CI.
Clean/synced baseline: `260b90c94deb47640b1df70491ba9464a722321f`, successful
[CI40739552966](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40739552966).
R1 documentation of a future explicitly gated R3 diagnostic; autonomous R2 is not
raised. Version0.1.77/schema8/PHP7.4/WordPress6.0 remain unchanged.

Only this design, the implementation-plan checkpoint and owning audit's forward
record change. No runtime, manual harness, test, acceptance, dependency or CI edit.
No installed-site inspection, WordPress authentication/bootstrap, SQL, fixture,
native driver experiment, private process/IPC proof, configuration, credential,
permission, schedule, client/Drime/live, package/release/tag/deployment operation.
No LocalWP target resolution or Novamira site call is needed: the target of this
task is repository source and published upstream source, not Plugin Tester state.
Uploader/toolkit remain read-only. Historical review rows and task counts stay put.

Inputs: [original contract](SOURCE_POLICY_FAILED_CONNECT_CLEANUP_EVIDENCE_DESIGN.md),
[isolated foundation](SOURCE_POLICY_CLEANUP_EVIDENCE_FOUNDATION.md), and the
[bounded native diagnostic](SOURCE_POLICY_CLEANUP_NATIVE_OWNED_AUDIT.md#bounded-missing-table-terminal-evidence-diagnostic--2026-10-28).
Historical evidence is not rerun or promoted by this design.

## Decision

Keep the existing verifier and controller fail-closed, including both connected
counter refusals. Do not add a wrap/pconnect allowlist, remove assertions, repair
counter bytes, set ready/qualification, reconnect or treat a closed-object Error
as release. Separate four questions when designing the next diagnostic:

| Question | Required evidence | Not established by |
|---|---|---|
| What did mysqlnd report? | Exact named canonical-string observations, fresh process, fixed source/profile and stage | Casting, subtraction, normalization, manual labels or guessed counter values |
| Was the exact owned object released? | One literal-true PHP mysqli_close acknowledgement, exact non-ambient identity, no replay, terminal/result/callback cleanup where applicable, corroborated by live server observation | Zero counters, wrap, an unavailable owner, or a closed-object getter alone |
| Is server/process cleanup independently reconciled? | Quiescent all-server baseline, live-child post-close and separate post-exit observations, owned process exit/cleanup | DB-local filtering, cached observer DATA, or zero sessions only after killing the child |
| Is the owner qualified for connected work? | Existing unmodified case verifier, all ordered prerequisites, genuine positive setup/escaping/seal and independent release evidence | A completed raw diagnostic, source interpretation, or any combination of negative-case cleanup facts |

No existing packet acquires a new status. In particular the historical rejected
missing-table packet stays rejected; its stderr path did not perform an accepted
live-child hold/ACK. Historical post-exit quiescence and77 unchanged checksums
prove bounded cleanup/preservation, not completion of that missing live phase.

## Observed evidence versus interpretation

The bounded diagnostic recorded fresh baseline zero and identical bootstrap/after
vectors `1,0,10,6,6,0,0,0,0,1,18446744073709551615` in the original11-counter order.
Two expectations disagree: pconnect_success1 versus0 and active_connections
18446744073709551615 versus0. Recorded terminal predicates otherwise match the
negative case: unavailable, never active, closed, eight consumed steps, six
explicitly freed metadata results, no public query/roundtrip/seal. Allocation and
close-call flags remain source-path/latch evidence, not independent instrumentation.
The result establishes no positive session/schema qualification or internal errno.

The [PHP statistics manual](https://www.php.net/manual/en/mysqlnd.stats.php)
describes aggregate counters shared by mysqlnd consumers. Its nominal definitions
count successful persistent connections and current open connections respectively.
A client-global statistic is not an object ownership receipt; restricting the
worker to mysqli and a fresh process reduces contamination but does not prove that
every implementation counter has the intended lifetime semantics.
The [mysqli pooling manual](https://www.php.net/manual/en/mysqli.persistconns.php)
uses a p: host prefix to request a pooled connection. The local private frame
allows only literal loopback hosts and passes flags0. Those guards remain intact.

### Immutable upstream source inspection

Read-only GitHub API inspection resolved annotated tag php-8.5.12,
tag object `c96586077b050b362744ba295f97f6395f58a75a`, to commit
`136e049ac10b032c83018e65b63b6df858943750`. Content was decoded in memory only;
no files installed, compiled, executed or copied into the repository. The API
commit blobs, not mutable branch pages or cached tag-page line numbers, own the
following references. These are comparison-source hashes, **not** attestation of
LocalWP's executable/DLL build provenance.

| Upstream file at that commit | SHA256 |
|---|---|
| ext/mysqli/mysqli_api.c | 29E23960A109B5F8B3FB268F1F44AA9B400E5495DAC788981AA32978B0BD4941 |
| ext/mysqli/mysqli_nonapi.c | 7A60F688BCBC2BB3F4F0990D0C235A43707C3BA5F6B8A3B3FAE67A2B9B905C23 |
| ext/mysqlnd/mysqlnd_connection.c | 9A00E4689676D29BD3E69630AF50477B2309F6D685F87FC6F580FD745E450430 |
| ext/mysqlnd/mysqlnd_statistics.h | C6DBFB783C9C1971CF01F5F11ED70067F64111C2ABF0940696EB7240817684B1 |
| ext/mysqlnd/mysqlnd_statistics.c | 75890502F2900B6FD4FB9B03A836395BF69387545E4F26420CFF00D0AF3C38F6 |
| ext/mysqlnd/mysqlnd_structs.h | 9C4E5A5EF36DBC34D482A51F3FB47F5ACDCF449FDAAAC236A327DD0CEDD564B5 |
| ext/mysqlnd/mysqlnd_enum_n_def.h | 27689DC30FDD4FFA3AF386FD24EA83A5C63F677F2F1E1F4F5E8D12875C636585 |

1. [mysqli_api.c](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqli/mysqli_api.c#L974-L1013)
   initializes mysqlnd with persistent allocation enabled in php_mysqli_init.
   Its php_mysqli_close path separately checks the mysqli wrapper's persistent
   flag to select physical close or the pool. Distinguish allocator lifetime from
   a request to keep/reuse a pooled connection. mysqli_close at269..285 returns
   PHP true after wrapper cleanup; that is not a separately exported wire/server
   acknowledgement. Independent live reconciliation remains necessary.
2. [mysqli_nonapi.c](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqli/mysqli_nonapi.c#L144-L158)
   gates the wrapper's pooling flag on the p: path. An already initialized mysqlnd
   object is reused by real_connect; a missing one is allocated with the locally
   selected persistence flag at234..238. This supports an initialized-handle
   explanation for pconnect_success1 without demonstrating actual pooling.
3. [mysqlnd_connection.c](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqlnd/mysqlnd_connection.c)
   increments opened counters at736..741, including the persistent counter when
   its allocation flag is set. close at2045..2067 sends close, then destroys the
   wrapper; dtor at2029..2036 releases a reference. Last-reference cleanup at
   1147..1159 can send close again. send_close at1083..1087 decrements open counts
   for state>=READY; it sets QUIT_SENT after closing the stream.
4. [mysqlnd_enum_n_def.h](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqlnd/mysqlnd_enum_n_def.h#L371-L380)
   places QUIT_SENT6 above READY1. Together these paths permit repeated internal
   accounting for one public close, a source-level explanation consistent with
   the observed wrap. This is an inference about that source, not a trace of the
   bundled DLL, proof of a second PHP close, or permission to retry cleanup.
5. [mysqlnd_structs.h](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqlnd/mysqlnd_structs.h#L298-L305)
   stores unsigned64 counter values;
   [mysqlnd_statistics.h](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqlnd/mysqlnd_statistics.h#L29-L42)
   decrements by adding minus1 without saturation.
   [mysqlnd_statistics.c](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/ext/mysqlnd/mysqlnd_statistics.c#L196-L209)
   exports unsigned decimal strings. A wrapped value is consistent with those
   operations but is never interpreted as a real negative connection count.

The reviewed upstream source supplies plausible mechanisms for both observations.
It does not establish LocalWP's build identity, call/refcount trace, actual pooling,
absence of all leaks, or the exact redacted owner-refusal cause. Do not label this
a confirmed installed PHP defect, promise a version upgrade fixes it, rebuild PHP,
change ini/reporting modes or expand compatibility from this comparison.

## Repository ownership and unchanged consumers

- includes/class-source-policy-bootstrap-profile.php: finite loopback/settings,
  actual driver/declaration/observer guards; no native provider.
- includes/traits/trait-source-policy-native-bootstrap.php and
  includes/class-source-policy-no-reconnect-connection.php: exact owned allocation,
  bounded setup, once-only close, revocation-before-cleanup and independent callback
  cleanup; public construction/query/reconnect stay closed before qualification.
- scripts/manual/source-policy-cleanup-native-cases.php: named native counters,
  original owner result/terminal observations, no reflection-based ready/handle
  injection. No assumption that path flags instrument internal C calls.
- scripts/manual/source-policy-cleanup-evidence.php: strict30-field ADBC1 parsing,
  literal-zero baseline and unchanged preflight/failed/connected case predicates.
- scripts/manual/source-policy-cleanup-cohort.php, bridge.php and controller.ps1:
  rejected DATA cannot reach held/live/ACK/finish; unique ordered children, strict
  source/profile correlation and25s whole-cohort budget remain mandatory.
- scripts/manual/source-policy-cleanup-diagnostic.php and process.ps1: bounded
  numeric/enum diagnostic, error-channel rejection/redaction, exact owned cleanup.
  Diagnostics are not normal terminal receipts or producer authentication.
- tests/SourcePolicyCleanupDiagnosticTest.php, EvidenceTest/CohortTest/NativeGateTest
  and isolated support: preserve both anomaly refusals and unavailable-positive,
  wrong-context, retained-state and missing-live-observation failures.

The abbreviated bridge/controller/process/test names above are within their
respective source-policy-cleanup / SourcePolicyCleanup families, not new files.
No parser, manual command/mode or protocol version is introduced by this design.

## Next bounded diagnostic contract — proposed, not authorized here

Prefer one minimal raw connected reference over another full owner cohort or an
acceptance rewrite. It must use the same confirmed fixed profile and a fresh
private mysqli-only process, with no wp-load, wpdb owner, imported handle, p: host,
pool/reconnect, arbitrary endpoint, listeners or runtime service changes.

The separately approved slice may add only a private observational harness and
isolated regressions, then run **one** raw connected attempt after fresh audited
source/error/stdio prerequisites and exact target/owned-process confirmation.
Use no application tables or fixture writes: selecting the fixed database at
connect is sufficient; no candidate SQL/result is required. If implementation
cannot satisfy that finite profile, stop rather than add DDL/accounts/permissions.

Record original11 counters plus active_persistent_connections, explicit_close,
implicit_close and disconnect_close at four fixed stages: before allocation,
after initialization, after successful connect, after the one attempted close.
Keep exact canonical native strings, named mapping, source/ABI/run/stage binding
and before/after source equality. Do not infer omitted stages or collapse them
into a cached post-close snapshot. Zero baseline and no unexpected query,
reconnect/reuse/result effects are required; anomalies remain observations, not
alternative successful owner profiles.

Capture allocation identity directly; set the one-call latch immediately before
the only literal mysqli_close on that object in finally and record its literal
return/exception category. Do not query errno on invalid/closed objects. Closed-
object probe and native explicit_close counter remain supporting, not a primitive
invocation count or proof of wire-level closure. No handle/credential/SQL/message/
trace crosses the private channel. Never import it into the dormant owner.

A new distinct bounded diagnostic DATA frame, if needed, must not parse as ADBC1
or synthesize its ACK. Retain16KiB stdout/4KiB stderr, canonical scalar bounds,
redaction and fail-on-unexpected-stderr. Preserve2s connect/3s read and a single
25s parent deadline covering startup, snapshots, close, observer/hold/exit. A
timeout/crash/release failure means unconfirmed; no automatic retry or larger
budget. Parent checks are not proof of hard interruption of blocked native IO.

At a quiescent instance, use an independent approved bundled-client observer:
baseline all-server sessions/transactions0/0; after connected snapshot and before
release, exactly one candidate session/no transaction while that child is alive;
after acknowledged close, held-child all-server0/0; then exact private release
token, clean exit and separate post-exit0/0. Include DB=NULL/foreign databases,
exclude only the observer's own session, never read foreign SQL/user/host payloads
or kill foreign sessions. Any ambiguity stops the diagnostic. Forced owned process
recovery may establish cleanup, never substitute for the live close observation.
No application checksum preservation result may be claimed unless actually run.

The two holds need distinct fixed phase/run/source/child-bound tokens: permission
to perform the one already scheduled close after the connected observation, then
permission to exit after the live closed observation. Neither is an ADBC1 token or
native grant. Wrong phase, replay, EOF or deadline stops progress; finally still
attempts the exact owned close once if not already attempted. No subsequent native
API query may repair the recorded snapshot or discover the original error. Keep
the first three counter snapshots bounded in the connected DATA frame and the
fourth in the terminal frame; neither may be copied from a later stage.

This adds one raw reference, not a comparator session that changes the candidate's
client counters. A complete diagnostic reports independent release and counter
observations separately, even if they conflict. It is never connected owner
qualification, a saved/conflict result or authority for storage/form wiring.

## Non-vacuous checks and gates

Future isolated regressions must reject a dirty/malformed baseline, wrong stage/
case/source/PID, duplicate/missing frame, false/throw/missing/second close, ambient
or foreign identity, zero sessions only after exit, foreign/DB=NULL live session,
no connected stage, copied post-close snapshots, extra SQL/reconnect and exceeded
IO/time bounds. Always-unavailable and always-closed stubs must fail the connected
reference; error from startup cannot count as an assertion-killed control.
Existing observed pconnect/wrap rows must still fail the unchanged ADBC1 verifier
independently and together. No artificial owner success or altered thresholds.

Ordered decisions after this document:

1. Explicitly approve the bounded raw reference and owned cleanup proof described
   above, including harness-only implementation/isolated tests, not a full retry.
2. Reconcile actual four-stage observations with this upstream-source hypothesis.
   A mismatch or incomplete release stops further qualification. No unconditional
   cleanup grant based on version label, wrap or pconnect value is acceptable.
3. Only then propose an explicitly approved, source/profile-scoped manual evidence
   contract revision if warranted, with independent live release mandatory and a
   distinct counter-diagnostic classification. Keep current rules until that gate.
4. Any later fixed-seven native qualification requires fresh applicable source
   image/prerequisites/target authority and its real positive, terminal cleanup and
   live/post observations. Historical negative rows cannot complete that cohort.

No direct production remedy, native retry or release is recommended now. Avoid
another generic abstraction/model layer: the missing evidence is a minimal actual
connected close reference, not additional synthetic proof of native behavior.

## Source-scoped review and validation

Reviewed driver allocation versus pooling, close versus counter accounting,
revocation/ownership, stage provenance, all-server live/post ordering, diagnostic
privacy, unchanged positive/negative admission, original budgets and independent
release failures. This is design review, not canonical whole-plugin pre-release,
Database Review, physical transaction proof, native qualification or UI/UX review.
Source hashes and upstream reasoning cannot authenticate an installed executable.

Executed preservation validation: focused Cleanup/FailedConnectObservation
PHP130 tests/460 assertions; full local PHP8.5.1 suite1698/7526, no failures,
only the two existing missing-Sodium skips. Node16/16, PHPCS204 files,
structure148 runtime/220 support/189 test files/186 class names, build, strict
Composer manifest, Composer/npm zero-advisory audits all pass. Repository-only
source audit26 reports SQL0/site reads0/native connections0. Three design local
links, the historical heading anchor and14 mapped source consumers resolve.
Diff review confirms no non-document source/dependency/configuration or generated
asset delta; uploader/toolkit remain clean at unchanged heads
3dfcb15b5cab8191df7d984251986ead140ded52/
c067eb61883035a1764d830bccffc1e542d3e1dc.

Commands: php vendor/bin/phpunit --filter
'SourcePolicyCleanup|SourcePolicyFailedConnectObservation' --do-not-cache-result;
full PHPUnit --verbose --do-not-cache-result; npm lint/test:admin/check:structure/
build; php composer.phar validate --strict/audit; npm audit --audit-level=moderate;
source-policy-cleanup-source-audit.ps1 -RepositoryOnly; scoped link/anchor/path,
Git diff hygiene and baseline-preservation checks. No new tests or semantic
mutation experiment: documentation-only work preserves existing tests, which
exercise the unchanged implementation, not the proposed native diagnostic.
Design checkpoint `25682d638de6aa08ba23efa9918624810eb54b3d` passed
[exact-head CI40741054616](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40741054616).
Actual hosted PHP7.4/8.3 each1698 tests/7533 assertions, no failures/skips;
Node16/16 and all configured gates pass. This evidence-only closeout preserves
the tested implementation bytes and receives a separate exact-head check before
handoff. No native claim follows from local or hosted preservation QA.

Implementation-plan progress: the counter mismatch now has a pinned-source
interpretation and a minimal release-evidence decision path. Native connected/
positive qualification and actual source-policy lost-update repair remain open.
Recommended next gated slice: **Plugin Tester local-only Bounded Raw Connected
Counter/Single-Close Diagnostic and Owned Cleanup Proof**, one fresh raw attempt,
no application writes, existing acceptance/runtime/wiring unchanged. Approve it
to distinguish initialized-handle accounting from actual live release before any
owner acceptance revision or full qualification retry.
