# V2.3 Core Projection Pre-Provisioning Dependency Audit

Successor: [complete read/absence and write-free feasibility design](V2_3_COMPLETE_BOOTSTRAP_READ_ABSENCE_WRITE_FREE_DESIGN.md).
Its public-source comparison corroborates the12 recorded identities; it does not
rerun this audit/proof or turn the rejected nine-option candidate into a GO.

2026-10-24. **Audit complete; finite nine-option candidate NO-GO. Native owned
qualification / cleanup proof Blocked, not executed.** The approved audit-first
condition prevented provisioning; this is not a passing bootstrap/native proof.

User approved Plugin Tester local-only, disposable accounts/views/fixtures and
cleanup only if the finite candidate qualified. Fresh resolved target confirmed:
LocalWP Plugin Tester, `http://plugin-tester.local`, WordPress at
`C:/Users/Captain/Local Sites/plugin-tester/app/public`, DB10004. Existing state,
admission, production wiring, feature grants, activation and rollback untouched.
No release, package, tag, deployment, service restart or configuration change.

Excluded coherent publication bases: dashboard
`ff1cea32d1c0f9ade15fbe63f1229e57a3610331`, uploader
`1da8ac7d9ea33c5b8b8a6fe9126fea205e876e0e`, toolkit
`d041605676519671a1aeed176709532103127a12`.
Parent: [finite candidate design](V2_3_FIXED_CORE_PROJECTION_DEPENDENCY_MANIFEST_DESIGN.md).
Predecessor: uploader `docs/V2_3_READ_ONLY_CORE_PROJECTION_OWNED_PROOF.md`;
its four groups/994 checks and77 application checksums are historical, not rerun.

## Method and readiness

Read the actual local core sources without including/executing them. The local
version file reports7.1.2. Trace concrete bootstrap calls and option cache/DB
fallbacks against the existing worker's eight keys plus the proposed ninth.
SHA256 identities below pin the inspected bytes; no upstream checksum comparison,
full core certification or exhaustive dependency inventory is claimed.

Novamira guide and complete inventory identify the exact local profile. Doctor
reports expired OAuth/usage_error checks; discovery reports provider_error/possible
CLI incompatibility. No credential reconnection or CLI repair attempted. Metadata
resolves MariaDB10.6.23/PHP8.5.1,DB10004/web10005. Direct bounded TCP checks still
found both ports unavailable after user confirmation. This does not prevent a
filesystem source audit and is NOT the reason the candidate fails.

Site Operations/registry, LocalWP database and runtime-testing workflows read.
No SQL, bundled-MySQL connection, WordPress bootstrap, HTTP route probe, fixture,
account/permission/DDL, native worker or cleanup command was run. The mandatory
bundled-MySQL preflight would precede any later DB operation, not a source audit.
No actual wp-config contents, application options, users or stored secrets read.

## Concrete dependency witnesses

Line numbers refer to the pinned local core files, relative to the confirmed root.
These witnesses disprove the candidate; they are NOT an exhaustive manifest.

| Stage | Actual source trace | Consequence under the unchanged candidate |
|---|---|---|
| Recorded ninth-key read | wp-settings.php:566 calls create_initial_post_types; post.php:101 reads wp_attachment_pages_enabled to select an attachment label | Either0/1 changes a label, not the subsequent bootstrap dependencies; it does not itself write |
| Additional read despite empty plugin list | wp-settings.php:582 calls wp_get_active_and_valid_plugins; load.php:1015 reads active_plugins, then1018 reads hack_file BEFORE the empty-list return at1023 | Empty active_plugins and absent my-hacks.php do not avoid the option read; hack_file is outside the finite manifest |
| Genuine cold miss must query | option.php:164 onward loads alloptions, checks notoptions/options, then206 issues the fixed option-value SELECT on a miss | Omitting hack_file from the view does not make its lookup query-free; absence/default evidence is a separate contract, not authorization to widen the guard |
| Later independent read | wp-settings.php:671 constructs WP_Rewrite; class-wp-rewrite.php:2022 constructor calls init;1923 reads permalink_structure | A second out-of-manifest read exists even if an earlier blocker were resolved; no claim this later stage was executed |
| Registration before init | wp-settings.php:763 constructs Site Health; class-wp-site-health.php:38 calls maybe_create_scheduled_event;3344 checks the weekly event | If no event exists in the owned profile, it calls wp_schedule_event; this is registration, not cron execution |
| Registration on init | default-filters.php:459/461 register privacy scheduling callbacks; functions.php:8551/8568 check/schedule missing events in non-installing mode | Missing-event branches can lead to writes; setting installation flags or removing core hooks to avoid them is not authorized |

The predecessor worker deliberately starts a genuine empty nonpersistent cache,
loads no application content/drop-ins, and forbids query/default rewriting.
Its exact-key query guard rejects the additional lookup BEFORE DB execution.
The source order is decisive: PHP evaluates get_option('hack_file') before testing
file existence and before returning for an empty active-plugin array. A denied
lookup, populated notoptions cache or fabricated default is not earned completion.
No default-filters.php shortcut for hack_file/permalink_structure was found in the
inspected core default-filter registration source; no application plugins run in
the fixed candidate. No runtime order/first-next-error claim is made from this audit.

Cron is also outside the nine-option manifest. cron.php:1262 reads it; missing or
non-array data becomes an empty schedule. wp_next_scheduled at859 delegates to the
scheduled-event lookup. The reviewed missing-event branches reach wp_schedule_event
at252, then _set_cron_array at330/1297 and update_option('cron',...) at1304.
These are CONDITIONAL source paths, not observed writes. A denied cron read would
stop earlier under the current guard. No event, schedule or application DB state
was inspected/changed. DISABLE_WP_CRON governs spawning in cron.php:1051, not the
Site Health constructor or the two privacy registration callbacks shown above.
Do not claim it removes all bootstrap write attempts.

## Decision: stop before provisioning, not another one-key experiment

The ninth option alone cannot support the stated full cold-bootstrap goal under
the fixed contract. No successor manifest/worker/view/credential change was made.
No disposable accounts, tables or views exist from this task; no cleanup was
needed or attempted. No new77-table/permission checksum, physical DB containment,
actual authentication, HTTP/owner admission or minimum-WP6.0 proof is earned.

Do not add hack_file/permalink_structure/cron, allow arbitrary negative lookups,
preload absent-option caches, copy existing application values, preseed schedules,
remove core hooks, suppress diagnostics or permit request writes under this
approval. Whether any bounded absence/read model and separately owned fixture
preconditions can avoid ALL writes needs a new whole-bootstrap feasibility
decision. This audit does not prove that a read-only profile can never work.

## Inspected source identities

| Core-relative file | SHA256 |
|---|---|
| wp-settings.php |2E3185EE44BCDD9D9C4BEF8CE4891827711DEC2B5D5D4C08C951738CCEF51F8D|
| wp-includes/version.php |C822935799E6F3B83129882554CCFF48596CF98B4A5EB82B83D82FE15455894E|
| wp-includes/post.php |522AE4AD3DD9E367840CD235CDE9FE6FF01AE1E4DD96906C7B5DC0EDBE81A56B|
| wp-includes/load.php |3EFD0934BD8A454A8D7076FBBD21504EA097A753125A48E64B8A1DAD380D357A|
| wp-includes/option.php |FE3F0B7E115B2A50C31162ED6428ABE4088C080B0F71B9678D84806168FE90D0|
| wp-includes/default-filters.php |55BBF47FB835A0F64F28423D475B51F7FA72479F319FCA417B4F23990D44F897|
| wp-includes/class-wp-rewrite.php |E197E1F2A78B4B6FFFFD1687BDEDF0C254D2A8CF013F6D8C8E55C0AC53FACD3E|
| wp-includes/default-constants.php |95C3A9AC07E13CED91017B059C3D38C1A3D827BA0293FF162DA83E3D9BEE4E72|
| wp-includes/functions.php |CAC8A17E293DF99F0683A88DEBD3BA5E5CCE274183A4E9FD3C9F18375747F41C|
| wp-includes/user.php |C837321FBF722F89A080324F3C742B0C44E5853C9BD6D305CB894B5F664FC6CC|
| wp-includes/cron.php |DC27910A1AD004EB2AAABC19352246CC0B9E601BF2DA870E141646FD9DD95591|
| wp-admin/includes/class-wp-site-health.php |D7517A0DDAA8B59B15265FEBAFC6545357AB53E3F982C50C8296B37484AF654D|

## Review / publication scope

Four changed Markdown files: this record, parent outcome pointer, both plans.
Canonical Feature Light scope: Other/source audit, plus actual fixed proof sources
and pinned core callers. Findings: two out-of-manifest read witnesses and one
conditional schedule-write category; all Flagged/Blocked, not auto-fixed. No
executable delta, bloat/framework or UI; Bloat/Structure and UI/UX unnecessary.
No formal DS3/Database/FULL_PRE_RELEASE completion or historical readiness refresh.
Executed preservation: uploader isolated156tests/1951assertions, no failures/skips;
dashboard299/1423, two existing Windows Sodium skips;35 local Markdown links and
whitespace checks pass. All12 pinned core hashes rechecked unchanged. Executable,
test, script, configuration and asset trees in both plugins remain unchanged.
This is not a fresh application/permission-table checksum or native cleanup proof.
Final canonical Feature Security after validation preserves stop-before-provisioning,
separates conditional/static paths from executed writes and leaves actual guards,
credentials and wiring intact. No introduced scoped security defect or security-fix
regression handoff; feasibility findings remain Blocked, not fixed/waived. Actual
read/absence/write-path regression qualification belongs to the future approved
design/proof, not artificial isolated success tests. Exact-head CI and source-scoped
checklist disposition belong in the toolkit; no test substitutes for the native proof.
Canonical OptionA source check confirmed clean uploader base and existing archive;
no new archive/checkpoint, source restore or site/database backup was performed.

Implementation-plan progress: R2's finite candidate is rejected before risky work;
native qualification remains Blocked. Recommended next gated slice: **design-only
V2.3 Complete Bootstrap Read/Absence Contract and Write-Free Profile Feasibility
Design**, resolving the full source dependency graph and conditional registration
writes together before another owned experiment. Keep storage writes, native
execution, production wiring, feature grants, activation and rollback disabled.
