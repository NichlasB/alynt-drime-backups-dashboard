# Hook-Silent Source-Policy Refusal and Error-Classification Safety Foundation

## Authority and scope — 2026-10-28

Approved repository-only R3 repair of the
[genuine error-hook blocker](SOURCE_POLICY_NATIVE_BOOTSTRAP_OWNED_AUDIT.md),
through isolated API validation, commit, push and exact-head CI. Clean/synced
restore point and coherent review baseline:
`228c3e146600371124245868cfba857843ec3fb4`. Autonomous R2 is not raised.
Version0.1.77/schema8 and declared PHP7.4/WordPress6.0 remain unchanged.

No LocalWP/installed-site inspection or genuine-library execution, authentication,
native extension/session/SQL/storage/fixture, credential/permission/service/cron,
client/Drime/live, package/release/tag/deploy operation. No native proof or prior
25-check genuine diagnostic rerun. Uploader/toolkit remain read-only; historical
whole-plugin readiness rows and prior task counts are not refreshed.

## Implemented error boundary

Source_Policy_Error constructs an ordinary empty WP_Error, then fills its
public errors DATA with one allowlisted code and a fixed message. It does not
call add/add_data, translation/filter/action APIs, reflection, unserialize or a
collaborator. No arbitrary message/data/handle is accepted. Unknown/non-string
codes fall back to unavailable without coercion. The required WP_Error class
must already be loaded; absence throws a fixed exception before any autoloader.
The helper returns error DATA only, never authentication or native eligibility.

Owned classification now uses the core instance-type predicate without its
observer dispatch. The seven existing outcome codes, empty data, ordinary
WP_Error type, fixed diagnostic wording and saved/unchanged/uncertainty
distinctions are preserved. Empty errors and subclasses remain errors by type;
classification invokes no getters, magic methods, callables or autoloaders.
Guarded entry projects codes only from the exact WP_Error class; unfamiliar
subclasses map to save_failed without invoking overridable getters. Messages,
data and handles from collaborators are still stripped.

All source-policy refusal/classification call sites in the closed connection,
bootstrap/profile/context, stored map, adapter, guarded entry and DATA contract
use the boundary. Existing source/nonce/capability/revision/record/query/cleanup
ordering, SQL templates, mutation/replay budgets, connect/reconnect refusals,
state latches, private provider absence and public factories remain intact.
No shared hook is disabled, removed, muted or resealed. External WordPress
WP_Error construction and is_wp_error retain their normal observer behavior.

Internal Save_Contract diagnostic text is now fixed, unfiltered English DATA;
it is no longer translated while constructing a protected refusal. This is a
deliberate effect-boundary change, not a claim that filtered translations are
byte-compatible. Existing real UI/handler messages and translations are
unchanged; future presentation must translate at its authorized outer boundary.
The root loader is byte-unchanged. Its existing DATA-class include now also
loads the new pure helper definition, not a writer/provider/request handler.

The [WordPress6.0 error class](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/class-wp-error.php)
documents public error maps and an empty constructor that returns before add().
Its [type predicate](https://raw.githubusercontent.com/WordPress/WordPress/6.0/wp-includes/load.php)
separates the instance check from the observer action. These source checks
support the selected API shape; they are not installed-source authentication,
genuine minimum-core execution or native-matrix qualification.

## Non-vacuous isolated evidence

The minimal shared error shim now has core-shaped public error/data maps.
In explicit ADB_POLICY_ISOLATED processes it models wp_error_added and
is_wp_error_instance; the translation shim models generic/domain gettext
dispatch with original source arguments. Ordinary non-isolated unit shims keep
their hook-free test convention. These remain partial substitutes, not genuine
WordPress authentication/error/source proof.

Original red:7 tests/9 assertions,6 assertion failures exposing real error-hook
effects; the positive core-event control passed. A further review regression
failed1/1 when a foreign error getter dispatched an observer. Final new suite:
**83 tests/676 assertions**, no failures/skips. Coverage includes:

- Raw core-model construction, classification and translation actually dispatch
  their respective observers; an always-unavailable or globally muted model
  cannot pass the positive controls.
- Six refusal operations across all/direct error/translation domains and
  observing/throwing/reentering callbacks: no observer call, preserved registry,
  exact refusal code/type, no native handle or physical dispatch.
- All closed factories, capability/nonce/record ordering, collaborator-code
  redaction, absent-class/no-autoload and non-coercing type/code inputs.
- Actual dormant RAM owner save success plus definite read/write failures,
  lost acknowledgement and retained-cache uncertainty; no reconnect. The cache
  fault includes an actual stale RAM entry, not merely a false delete result.
- External is_wp_error still dispatches its observer after the owned boundary.
  Unknown subclass getter methods are not executed during projection.

All138 existing extension-free bootstrap worker scenarios additionally install
throwing error/translation-hook tripwires. Their observation code uses direct
type assertions, not effectful predicates. Positive orchestration, partial
cleanup, guarded dispatch and old error outcomes remain covered. No native
handle/ready/active/facts injection is added by this repair.

The two manual proof sources were updated, **not executed**: native-case
observations use direct type checks; the genuine error audit now expects core
controls to emit events and owned refusals to stay quiet. Their prior native
failure/25-check diagnostic records remain historical and require fresh
revalidation; changing expected observations is not a passed native proof.

## Final semantic controls

Seven temporary controls all produced assertion failures, then restored exact
SHA256 hashes before clean final QA. Throwing-observer variants also produced
the expected callback exceptions; none was merely a syntax/startup failure.

| Removed boundary | Tests/assertions | Failures/errors |
|---|---|---|
| Coded WP_Error constructor |72/300|24/12|
| Effectful shared classification |1/37|1/0|
| Effectful direct query caller predicate |72/520|4/2|
| Translated internal diagnostic, message otherwise unchanged |1/37|1/0|
| Missing-class no-autoload guard |1/2|1/0|
| Collaborator error redaction |1/23|1/0|
| Exact-class getter admission |1/1|1/0|

Restored SHA256:

- Source_Policy_Error: `74CC1F2BCADB8E8AFF7840BC0D174D46F04ECA9A7685E6F5F87D9586F7ED0A0F`
- No_Reconnect_Connection: `9634BDE532F9247493534E3FD5D6C9B6EC2EC3B8FE75B379F04F6CB729C22BA6`
- Save_Contract: `6F62D1E4210C131450E427EF0B52E4EFFF6824FB702C11E1E4F52440A210B894`
- Guarded_Entry: `392C2AFEF7D4ABCB1AE2CACF917264E5E8C1F257989E261CB72934C77B627DC5`

Projection controls were repeated after the final exact-class review fix;
only the final hashes/counts above are the final-source evidence. Earlier
bootstrap/native controls are not relabeled as coverage of these error effects.

## Validation and source-scoped reviews

Final related randomized **675/3015**, seed20261028, no failures/skips. Full local
**1509/6973**, only the two baseline missing-Sodium signing skips; Node16/16,
PHPCS195 files, structure148 runtime/215 support/183 tests/180 test class names,
build and strict Composer manifest pass. Composer/npm audits: zero advisories.
All19 affected PHP syntax checks, diff hygiene and scoped links pass.
Commands: focused SourcePolicySilentRefusalTest and existing bootstrap suite;
related SourcePolicy|AdminPageCompoundRequestId randomized seed20261028;
full php vendor/bin/phpunit --verbose --do-not-cache-result; npm lint/test:admin/
check:structure/build; Composer validate --strict/audit; npm audit
--audit-level=moderate; explicit php -l and source/load/registration checks.

Source-scoped Feature Light:19 PHP files plus owning records and unchanged
root/UI callers. No UI/AJAX/REST/cron/native execution change. Fixed the assigned
error effects and a review-found virtual collaborator-getter callback path;
corrected the retaining-cache fixture to exercise a real stale entry. No
remaining significant scoped quality finding; no UI/UX workflow applies.

Canonical Bloat/Structure Phase1 read-only measurement uses the explicit review
baseline above, including additions, not a tail verification commit.19 files,
zero oversized, no removal/split/defer candidates or Phase2 edits. All files
classified Consolidate/no-op retain; direct/string references and WordPress
hook/filter/shortcode/REST/AJAX/cron registrations checked. Exact TOTALs:

| Group/file |TOTAL|Limit|
|---|---:|---:|
| includes/bootstrap-profile |132|300|
| includes/error |49|300|
| includes/guarded-entry |133|300|
| includes/native-adapter |299|300|
| includes/native-profile |129|300|
| includes/no-reconnect-connection |221|300|
| includes/query-context |286|300|
| includes/save-contract |220|300|
| includes/stored-map |57|300|
| traits/native-bootstrap |218|300|
| manual/bootstrap-error-audit |93|300|
| manual/bootstrap-native-cases |129|300|
| tests/SourcePolicySilentRefusalTest |106|300|
| support/bootstrap-cases |86|300|
| support/bootstrap-driver |54|300|
| support/bootstrap-worker |45|300|
| support/wordpress-shims-error-class |71|300|
| support/wordpress-shims-errors |23|300|
| support/wordpress-shims-i18n |39|300|

Prefixes remain class-source-policy-/trait-source-policy-/source-policy- as
applicable. The canonical CODE metric reports1 for every file on this host and
is not usable; TOTAL measurements are usable. The project guard counts a trailing
empty line, with Native_Adapter exactly300, not over its limit. No artificial
split, toolkit/helper repair or historical whole-plugin02 refresh.

Final focused Feature Security: bounded codes/message/data, trusted loaded-core
class prerequisite, no autoload/coercion/virtual getter, silent owned construction/
classification, unchanged authorization/storage/cleanup gates, preserved
uncertainty and external observers reviewed. Original error effects and the
review-added Medium getter issue have permanent isolated regressions and
assertion controls. No remaining identified scoped defect; genuine-source/
native verification remains a gate, not inferred from the model. Historical
whole07/07A/13/FULL and toolkit checklist rows remain untouched under this
repository-only scope. No release-readiness completion is claimed.

Implementation `7859010732155a185d569481c4041a5362377b0e` passed
[exact-head CI40696075408](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40696075408).
Actual PHP7.4/8.3 logs each1509 tests/6980 assertions, no failures/skips;
Node16/16 and all configured gates pass, zero Composer/npm advisories.
Hosted QA executes the isolated foundation and early manual-entry refusals,
not installed WordPress, genuine refusal verification or native qualification.

## Implementation-plan progress and next gate

The owned refusal/classification algorithm and non-vacuous model now cover the
observed error-hook gap. The old native proof is still blocked/pending genuine
revalidation; its failure is not retroactively converted to success. Actual
lost-update repair, configuration/minimum-core/native matrix and production
integration remain open.

Recommended next gated slice: **fresh Plugin Tester local-only Bounded Initial
Native Construction and Pre-Qualification Bootstrap Owned Qualification/Cleanup
Proof**, audit first with exact target/fixture confirmation. First establish
genuine hook-silent refusal code/message/data and unchanged core control events;
only then run one fresh ordered owned proof and cleanup if prerequisites pass.
Preserve existing state and disabled public factories/production wiring. No new
native attempt or genuine-library operation is authorized by this foundation.

## Repository-only manual evidence readiness — 2026-10-28

R1 autonomous batch follow-up from clean/synced
`8e6f98b9ad3bdb86e7b1c415773a7e8f1518c47a`. Reviewing the next gate's prepared
audit found that its error-result check accepted any WP_Error. An always-empty
refusal could therefore pass that check despite the required code contract.
The manual-only pure evidence checker now requires the exact ordinary class,
one unavailable code/empty-message public map, empty public data map and exact
core getter projections. Malformed maps reject before getters; subclasses never
reach virtual getters. Classification/query controls require literal true/false;
unknown case names reject. Existing source pins, invocation/driver gates,
positive core hook transcripts and handle-free cleanup assertions are unchanged.
No runtime/loader, authentication, schema, SQL, native or production-wiring change.

New isolated suite48 tests/49 assertions: valid evidence, empty/wrong/extra code,
nonempty message/data, malformed map, foreign subclass and wrong result types,
plus source wiring. The original type-only predicate failed28/47 assertions.
Temporarily bypassing the checker in the manual audit failed1/2 assertions;
restored audit SHA256
`ADA030660B48D3D57DA97C8B23522B97A67B826A7BCA27ABB54BAF344A9E6159`.
Restored focused suite plus existing early-entry gates54/73, randomized related
723/3064 (seed20261028), full local1557/7022 with only two existing missing-Sodium
skips. Node16/16; PHPCS196 files; structure148 runtime/215 support/184 tests/181
class names; build; strict Composer validation; Composer/npm audits zero
advisories; three syntax checks and diff hygiene pass. Commands match the
preceding validation section, with the new EvidenceTest and existing
OwnedProofGateTest focused filters. Source-scoped review confirms bounded pure
DATA checks, no extra production dependency or changed observer/authorization
semantics. Publication/exact-head CI remains pending.

These are model and source-wiring checks, not genuine WP_Error execution,
installed-source authentication or native success. The genuine manual audit
was not run; no site/SQL/fixture/driver operation occurred. Historical native
failure and whole-plugin/toolkit readiness remain untouched. One worthwhile
safe-local task advances audit acceptance; the next meaningful item still
crosses the separately approved-and-confirmed genuine/native proof boundary
named above. No unrelated cleanup is selected merely to fill eight tasks.
