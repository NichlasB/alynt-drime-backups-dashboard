# Candidate-Bound Legacy Greeting and Core-Derived Session Admission Decision

## Authority and disposition — 2026-11-05

Owner approval covers this repository-only decision, isolated validation,
commit/push and ordinary exact-head CI. Clean/synced restore and review baseline:
`8dc7af09463da3286d9e6e3c2b707782883e6e60`, CI41997435879 reverified successful.
R1 documentation about a future R3 admission-policy exception, not authority to
implement it. No runtime/test/workflow/dependency bytes, default admission,
candidate pins, installed state or production wiring change. No candidate/core
runtime loading, SQL, fixture, engine, acquisition or native retry occurs.
Public primary-source text inspection is not native qualification.

**Decision: a single exact, private legacy-lab greeting AND helper/session policy
is justified for a separately approved repository-only foundation.** It is not
generic version normalization or production compatibility expansion. Preserve
the failed attempt, strict default refusal and physical schema requirements.
Implementation and another native opportunity remain separately gated.

## Observed failure versus source-derived expectation

[Run41996445940](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41996445940)
at52d881a passed control SQL identity10.6.23-MariaDB and the connection-free PHP
audit, then refused before owned metadata queries at the exact API guard.
The source/enum correlation establishes raw5.5.5-10.6.23-MariaDB, not an arbitrary
prefix hypothesis. Failure counter480/groups0 is NOT480 passed assertions or a
qualification result. The [owning result](SOURCE_POLICY_DISTRIBUTION_LAB_INTEGRATION.md#single-approved-native-attempt-legacy-greeting-refused-owned-cleanup-verified--2026-11-05)
preserves failed primary release and successful subsequent owned-inventory removal.

| Identity or state | Evidence / proposed expectation, never interchangeable |
| --- | --- |
| Engine/build | Existing fixed UBI manifest/config and trusted-distribution contract; not inferred from a version label |
| Control SQL VERSION() | Observed exact10.6.23-MariaDB before PHP audit; keep unchanged |
| Owned raw mysqli API | Observed exact5.5.5-10.6.23-MariaDB by failure/source correlation; current default refuses it |
| Genuine core version/capabilities | Source-derived5.5.5, utf8mb4 available, utf8mb4_520 unavailable for this client/greeting |
| Empty-collation helper result | Source-derived utf8mb4 /utf8mb4_unicode_ci, NOT a measured owned session from this attempt |
| Connection readbacks | Must later equal genuine helpers and exact private expectation; currently unqualified |
| Physical options schema | Keep utf8mb4_unicode_520_ci/InnoDB/four columns/three indexes/unique option_name; no DDL or reinterpretation |

### Exact primary-source trace

WordPress6.0 commitcc101b64012b16d087780657a2b828ccd7794a63, wp-db.php SHA256
`f07bc6403ab6db30195cca8e47a384de11d582a66a64cb50449d4335edcc24cf`
is rechecked from public source bytes against the [existing catalog](../scripts/manual/source-policy-supported-core-pins.php).
[Pinned wpdb](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-includes/wp-db.php):
db_server_info forwards mysqli information (3820–3827); db_version retains its
numeric prefix (3809–3810). has_cap at3754–3785 therefore sees5.5.5: collation,
set_charset and utf8mb4 pass with mysqlnd7.4.33, while utf8mb4_520 does not.
For single-site DB_CHARSET=utf8 and DB_COLLATE='', init_charset/determine_charset
(791–860) select utf8mb4_unicode_ci. set_charset (872–906) uses the genuine
driver charset setter and prepared SET NAMES/COLLATE. Do not override these
helpers, db_version, DB_COLLATE or raw server observation to obtain readiness.
An explicit _520_ci configuration is a different case, not the empty profile.

PHP7.4.33 tag resolves through005f0cbace2d58e74867606f6b9a335491566bb0 to source
commitdca8d5565b947270a29f232bc54efd9df3b92b94. Its [greeting reader](https://github.com/php/php-src/blob/dca8d5565b947270a29f232bc54efd9df3b92b94/ext/mysqlnd/mysqlnd_wireprotocol.c#L349)
copies the supplied version; [handshake command](https://github.com/php/php-src/blob/dca8d5565b947270a29f232bc54efd9df3b92b94/ext/mysqlnd/mysqlnd_commands.c#L650)
retains it, and [server-info accessor](https://github.com/php/php-src/blob/dca8d5565b947270a29f232bc54efd9df3b92b94/ext/mysqlnd/mysqlnd_connection.c#L1390)
returns that retained value. mysqli_api.c at1434–1451 forwards this information.
This explains why the protocol compatibility prefix can survive the API; it is
not a driver build attestation or permission to treat every such prefix as safe.

[MariaDB10.6.23 handshake source](https://github.com/MariaDB/server/blob/fe8047caf26d20e98ea7f6ec1dce3924e696703f/sql/sql_acl.cc#L13277)
adds its named replication-compatibility prefix unless a custom server version
is configured. This is explanatory source context, not provenance binding that
source to the image. Do not change server-version configuration. Actual exact
correspondence comes from this candidate's recorded API/control observations.

## Proposed finite contract, not an implemented grant

Reuse the [existing private lab-scope composition](../includes/class-source-policy-minimum-lab-scope.php)
and [fixed construction branch](../scripts/ci/source-policy-minimum-native-operation.php),
with one separately discriminated code-owned row for trusted-distribution-legacy-v1.
Do not introduce a generic profile registry, extra factory, normalization layer
or public success path. Existing newer-server trusted-lab-functional-v1 behavior
must remain byte-for-byte equivalent in its admission decisions.

The new row must bind ALL of the following, without mixing fields between rows:

- Existing exact distribution workflow/job/head/source-image/run correlation,
  attempt1 and explicit execution flag from the [entry/evidence contract](../scripts/ci/source-policy-minimum-distribution-contract.php).
  Twelve-field evidence is consistency DATA, not imported-receipt authority.
- Pinned genuine core6.0 commit and source cohort; PHP/mysqlnd7.4.33/Linux amd64;
  initial report mode0; runtime manifest08a1cb1f.../configde3b9412... and UBI
  server manifest14bab8cc.../config2a46b014..., full values in the [candidate record](SOURCE_POLICY_LEGACY_MINIMUM_CANDIDATE_DESIGN.md).
  Existing controller/preflight owns config/observed-runtime checks; do not claim
  those are new fields already present in the twelve-field scope evidence.
- Exact fresh No_Reconnect_Connection receiver and retained Query_Context,
  verified source files, original frozen frame and owned fixture identity:
  adb_minimum, fixed user **adbm**, run-derived adbm_<12hex>_options,
  TCP127.0.0.1:3306/socketnull. No password retained/hashed/exported in the scope.
- Exact raw API expectation **5.5.5-10.6.23-MariaDB only**, alongside the separate
  unchanged control SQL10.6.23-MariaDB gate. Neither unprefixed nor double-prefixed,
  suffix variants, other builds or prefix/version ranges enter this new row.
- Exact fixture settings DB_CHARSET=utf8 and DB_COLLATE='' for this new row;
  expected helper charsetutf8mb4 and connection collationutf8mb4_unicode_ci.
  No alternate configured collation is needed to qualify this physical case.

Selection must occur after fixed entry/source/candidate validation and before
owner construction; scope validity/frame/context must be checked before native
handle allocation (after the receiver object exists),
at bootstrap boundaries and during active admission. No caller parameter, request,
option, environment-only model claim, receipt parser or public method can select
the exception. Existing environment checks are necessary but are not a security
boundary against arbitrary trusted-runner code or reflection. Retain the accepted
trusted-host/distribution assurance limits, not a new hostile-runner claim.

The raw identity stays raw in qualification facts; never rewrite it to the SQL
string. The same bound receiver must determine initial expected greeting,
[session validation](../includes/class-source-policy-bootstrap-evidence.php),
final [native-profile admission](../includes/class-source-policy-native-profile.php)
and later per-operation checks. No bootstrap-only bypass or active fallback.
Closed/replaced receiver/context, changed frame/facts/helper state or wrong model
must refuse, prevent the next command and release the owned connection.

Keep null/unscoped/default expectations unchanged: Transport_Profile still
requires10.6.23-MariaDB for core6.0, default session still requires_520_ci, public
factories stay unavailable. Preserve source/handle/hook/cache/reentry ownership,
report OFF, no reconnect/reissue, exact byte predicates, eight setup steps,
20-second/16-credit initializer and every existing outer query/time/resource limit.
No additional probe, SQL catalog expansion, observer or budget increase is needed.

## Non-vacuous foundation and later physical proof

The next foundation must include actual construction/adapter-path isolated
positives, not an always-unavailable owner or synthetic ready/facts injection.
Extend existing fixtures deliberately: their current empty-collation double
special-cases the NEWER server and otherwise yields_520_ci; that is NOT the
pinned legacy core behavior and must not silently become native evidence.

Require discrimination for each model/candidate/source/job/head/run field;
old/new cross-row mix; absent/cloned/foreign context or receiver; altered frame;
wrong raw greeting before metadata; default refusal; helper/session disagreement;
each charset/collation readback; schema collation drift; and post-bootstrap
binding/helper/facts drift before further dispatch. Positive view/save/conflict
must preserve non-target entries. Keep legacy/newer strict regressions.
Guard-removal semantic controls must assertion-fail for required binding,
exact greeting, session agreement and schema/default separation; include an
always-unavailable control that kills the positive case. No new skips or weakened
assertions, JSON-extension assumption or fabricated success to improve coverage.

Isolated source/driver doubles can prove policy and ordering, not actual core,
authentication, collation/escape/SQL behavior or held-live release. After separately
approved foundation/publication, ONE newly approved exact-head physical attempt
would require fresh unchanged audit/startup/identity/containment prerequisites,
actual genuine helper/readbacks, the original nine groups/byte probe/non-empty
hooks/cache/failure/concurrency cases and independent all-server held-live plus
post-exit reconciliation and exact owned cleanup. Refusal remains failure; empty
recovery/removal alone never establishes successful functional release. Do not
dispatch or allocate under this design approval, reset limits or retry blindly.

## Qualification meaning, review and next gate

A future pass could close the candidate-bound **legacy engine functional lab**
subcase under the accepted trusted-distribution assurance. It would not validate
unchanged default admission for this prefixed profile, qualify arbitrary MariaDB,
waive M/W/L reconciliation or deliver the concurrent-save repair. Required
production-provider/form/default eligibility must be explicitly reconciled before
wiring; no all-unavailable release or old-writer fallback is acceptable. The
[delivery objective](SOURCE_POLICY_DELIVERY_QUALIFICATION_REPLAN.md) remains open.

Feature scope: repository-only joint admission decision. Files reviewed: five
changed Markdown records plus directly relevant unchanged guards/construction
and tests. Risk areas: future private DB/profile trust, no new runtime surface.
Light findings: one documentation ambiguity fixed (lab versus default eligibility),
zero remaining non-security issues; the policy implementation remains a manual
approval decision, not a defect silently fixed here.

Canonical Feature Light then final Feature Security review is source-scoped to
this decision and four directly owning resume records, baseline8dc7af0; inspected
callers/guards/tests are unchanged. Draft review corrected a potential transfer
of lab evidence to default/production eligibility by separating these claims.
No remaining confirmed defect in the documentation boundary; the proposed R3
exception still needs owner approval. Bloat/Structure is unnecessary: zero code
growth or duplicate implementation. UI/UX is inapplicable: no UI/copy change.
No whole-plugin03/04/05/07/07A/13/FULL completion or physical transaction proof.
Existing isolated tests and document link/scope validation are rerun; ordinary
exact-head CI and the scoped toolkit record supply publication evidence.

Local validation: existing focused PHPUnit **48 tests/260 assertions**, Node
**15/15**, no failures/skips; **306** local file links across the five records
resolve; configured PHPCS passes **257 files**. Git whitespace/scope review
passes. No tests were added or changed:
these are regressions of unchanged behavior, NOT tests of an implemented new row.
Final focused Security: no confirmed defect in this documentation-only boundary;
no new security regression handoff. Future policy protection is specified above,
not claimed already complete. Actual exact-head publication results belong in
the source-scoped toolkit evidence, without refreshing whole-plugin rows.

Implementation-plan progress: the observed greeting and source-derived session
contradictions now have one finite joint decision, not serial bypass proposals.
Campaign8/10 product milestones,static9 and15 consumed native attempts remain
unchanged. No owned resource is created by this design task.

**Recommended next gated slice: Repository-Only Candidate-Bound Legacy Greeting
and Genuine-Core Session Policy Safety Foundation.** Explicitly approve the one
private lab-only greeting/session exception above, isolated non-vacuous tests,
focused reviews, validation, commit/push and ordinary exact-head CI; keep default
production admission unchanged and native execution/production wiring disabled.
Do not include a native retry in that approval.
