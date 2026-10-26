# Repository Organization Migration — 2026-10-26

## Project boundary and main-work hold

Approved D2/U1/U2/U3 path-only organization, after completed D1. Preserve global
symbols, source bytes, public contracts and load order; no architecture/logic
change, forwarding shim, dependency install, protected-source rebaseline, site,
SQL/native/lifecycle/cron operation, package/release/tag or deployment.
Toolkit stays read-only. Deferred V2.3 stays parked. This is scoped canonical02
organization, not whole-plugin02/FULL completion or product-feature progress.

Main-work checkpoint: pre-migration dashboard
`8aa20fc10afc9f9876c13a3d82c465d80708c7ea`, clean/synced, exact-head
[CI40410394509](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40410394509)
passed. Cumulative product progress remains4/8 throughout organization. Next
main-work gate remains **Plugin Tester local-only Retention Cleanup,
Latest-Snapshot Preservation and Failure-Reporting Owned Proof**; it is unapproved.
Do not execute that proof or substitute main work for unfinished organization.

## D1 historical closeout

Diagnostics/event-log15 moved in source `d68184498a2b1a6e50494489deb18899ee0a121b`,
docs `894f11cfb5ccc841e7c9f142ceb55ac610df0b12`, exact-head CI40383732313/
40384087611 passed. The existing owning D1 review/map remains historical evidence.

## D2 — Dashboard admin paths

Baseline `8aa20fc10afc9f9876c13a3d82c465d80708c7ea`. External source-folder restore
ZIP (not a site backup/package):
`C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots\alynt-drime-backups-dashboard\alynt-drime-backups-dashboard-20261026-232127.zip`,
matching .txt,6957 files, SHA256
`0ADD08690EF6FDA45C972E14A9170D77EF1BC64EB562CC003F9EC7599DDED56D`.
No restore applied/checkpoint branch created.

Exactly57 files moved, every raw SHA256 identical. Root loader plus11 direct
test/harness files received101 literal path substitutions only. All631 original
tracked paths accounted for,136 unique loader entries retain exact order,
69 affected PHP syntax checks pass, no stale executable old path or case collision.
No source-body, query, setting, capability, nonce, rendering, asset or fixture
assertion change. The full source-byte verifier rejects non-path code changes,
missing/unexpected mapped paths, loader-order changes and altered moved bodies.

Existing tool-only WP-CLI2.12.0 extraction ran before WordPress load, empty owned
configuration, --skip-packages, build/docs/scripts excluded. Baseline/current
full extractions each832 messages/header with identical non-location content;
all extracted locations differ only by this move map. Scoped tracked projection
updates708 active message blocks (no historical-only blocks), using actual moved
source references/lines and retaining unrelated metadata/missing-entry drift.
No message added/deleted or general catalog refresh.

Offline validation: full PHP375/1959 with the same two pre-existing missing-
Sodium signing skips as baseline; Node16/16 no failures/skips; PHPCS149,
structure136 runtime/195 support/144 test files, deterministic asset build,
strict Composer validation and Composer/npm audits (zero advisories) pass.
No tracked asset output changes. Static recursive rsync package rules still
include includes/admin and its traits; no ZIP created or installed.

Source commit `497ca137a0e4545edb0ede9ea4680130074030de` pushed; exact-head
[CI40412188412](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40412188412)
passed. Actual PHP7.4/8.3 logs each375 tests/1966 assertions, no skips/failures;
Node each16/16, fail0/skip0; all configured lint/build/structure/audit gates passed.
Current-state documentation reconciliation occurs only after that source CI.

### Exact D2 mapping

| Historical path | Current path |
|---|---|
| `includes/class-admin-page.php` | `includes/admin/class-admin-page.php` |
| `includes/traits/trait-admin-page-action-notices.php` | `includes/admin/traits/trait-admin-page-action-notices.php` |
| `includes/traits/trait-admin-page-actions.php` | `includes/admin/traits/trait-admin-page-actions.php` |
| `includes/traits/trait-admin-page-add-site.php` | `includes/admin/traits/trait-admin-page-add-site.php` |
| `includes/traits/trait-admin-page-archive-actions.php` | `includes/admin/traits/trait-admin-page-archive-actions.php` |
| `includes/traits/trait-admin-page-attention-recovery-history-helpers.php` | `includes/admin/traits/trait-admin-page-attention-recovery-history-helpers.php` |
| `includes/traits/trait-admin-page-attention.php` | `includes/admin/traits/trait-admin-page-attention.php` |
| `includes/traits/trait-admin-page-backup-source-compact-helpers.php` | `includes/admin/traits/trait-admin-page-backup-source-compact-helpers.php` |
| `includes/traits/trait-admin-page-backup-source-evidence-helpers.php` | `includes/admin/traits/trait-admin-page-backup-source-evidence-helpers.php` |
| `includes/traits/trait-admin-page-backup-source-evidence.php` | `includes/admin/traits/trait-admin-page-backup-source-evidence.php` |
| `includes/traits/trait-admin-page-backup-source-operator-helpers.php` | `includes/admin/traits/trait-admin-page-backup-source-operator-helpers.php` |
| `includes/traits/trait-admin-page-backup-source-policy-helpers.php` | `includes/admin/traits/trait-admin-page-backup-source-policy-helpers.php` |
| `includes/traits/trait-admin-page-backup-source-timestamp-helpers.php` | `includes/admin/traits/trait-admin-page-backup-source-timestamp-helpers.php` |
| `includes/traits/trait-admin-page-basic-detail-helpers.php` | `includes/admin/traits/trait-admin-page-basic-detail-helpers.php` |
| `includes/traits/trait-admin-page-cleanup-preview-helpers.php` | `includes/admin/traits/trait-admin-page-cleanup-preview-helpers.php` |
| `includes/traits/trait-admin-page-diagnostic-formatters.php` | `includes/admin/traits/trait-admin-page-diagnostic-formatters.php` |
| `includes/traits/trait-admin-page-diagnostics-audit-history.php` | `includes/admin/traits/trait-admin-page-diagnostics-audit-history.php` |
| `includes/traits/trait-admin-page-diagnostics-event-log.php` | `includes/admin/traits/trait-admin-page-diagnostics-event-log.php` |
| `includes/traits/trait-admin-page-diagnostics-local-actions.php` | `includes/admin/traits/trait-admin-page-diagnostics-local-actions.php` |
| `includes/traits/trait-admin-page-diagnostics-overview-helpers.php` | `includes/admin/traits/trait-admin-page-diagnostics-overview-helpers.php` |
| `includes/traits/trait-admin-page-diagnostics-overview.php` | `includes/admin/traits/trait-admin-page-diagnostics-overview.php` |
| `includes/traits/trait-admin-page-diagnostics-settings.php` | `includes/admin/traits/trait-admin-page-diagnostics-settings.php` |
| `includes/traits/trait-admin-page-diagnostics-support-output.php` | `includes/admin/traits/trait-admin-page-diagnostics-support-output.php` |
| `includes/traits/trait-admin-page-diagnostics-tables.php` | `includes/admin/traits/trait-admin-page-diagnostics-tables.php` |
| `includes/traits/trait-admin-page-diagnostics.php` | `includes/admin/traits/trait-admin-page-diagnostics.php` |
| `includes/traits/trait-admin-page-display-helpers.php` | `includes/admin/traits/trait-admin-page-display-helpers.php` |
| `includes/traits/trait-admin-page-formatters.php` | `includes/admin/traits/trait-admin-page-formatters.php` |
| `includes/traits/trait-admin-page-local-actions.php` | `includes/admin/traits/trait-admin-page-local-actions.php` |
| `includes/traits/trait-admin-page-polling-detail-helpers.php` | `includes/admin/traits/trait-admin-page-polling-detail-helpers.php` |
| `includes/traits/trait-admin-page-remote-action-history-cleanup-details.php` | `includes/admin/traits/trait-admin-page-remote-action-history-cleanup-details.php` |
| `includes/traits/trait-admin-page-remote-action-history-filters.php` | `includes/admin/traits/trait-admin-page-remote-action-history-filters.php` |
| `includes/traits/trait-admin-page-remote-action-history-helpers.php` | `includes/admin/traits/trait-admin-page-remote-action-history-helpers.php` |
| `includes/traits/trait-admin-page-remote-action-history-labels.php` | `includes/admin/traits/trait-admin-page-remote-action-history-labels.php` |
| `includes/traits/trait-admin-page-remote-action-history-schedule-details.php` | `includes/admin/traits/trait-admin-page-remote-action-history-schedule-details.php` |
| `includes/traits/trait-admin-page-remote-actions.php` | `includes/admin/traits/trait-admin-page-remote-actions.php` |
| `includes/traits/trait-admin-page-request-backup-detail-helpers.php` | `includes/admin/traits/trait-admin-page-request-backup-detail-helpers.php` |
| `includes/traits/trait-admin-page-restore-readiness-evidence.php` | `includes/admin/traits/trait-admin-page-restore-readiness-evidence.php` |
| `includes/traits/trait-admin-page-restore-readiness-labels.php` | `includes/admin/traits/trait-admin-page-restore-readiness-labels.php` |
| `includes/traits/trait-admin-page-schedule-label-helpers.php` | `includes/admin/traits/trait-admin-page-schedule-label-helpers.php` |
| `includes/traits/trait-admin-page-schedule-management-action-forms.php` | `includes/admin/traits/trait-admin-page-schedule-management-action-forms.php` |
| `includes/traits/trait-admin-page-schedule-management-form-helpers.php` | `includes/admin/traits/trait-admin-page-schedule-management-form-helpers.php` |
| `includes/traits/trait-admin-page-schedule-remote-actions.php` | `includes/admin/traits/trait-admin-page-schedule-remote-actions.php` |
| `includes/traits/trait-admin-page-schedule-rollback-preview-evidence.php` | `includes/admin/traits/trait-admin-page-schedule-rollback-preview-evidence.php` |
| `includes/traits/trait-admin-page-schedule-rollback-preview-helpers.php` | `includes/admin/traits/trait-admin-page-schedule-rollback-preview-helpers.php` |
| `includes/traits/trait-admin-page-schedule-rollback-preview-readiness.php` | `includes/admin/traits/trait-admin-page-schedule-rollback-preview-readiness.php` |
| `includes/traits/trait-admin-page-shell.php` | `includes/admin/traits/trait-admin-page-shell.php` |
| `includes/traits/trait-admin-page-site-detail-local-record-panels.php` | `includes/admin/traits/trait-admin-page-site-detail-local-record-panels.php` |
| `includes/traits/trait-admin-page-site-detail-local-removal-preview.php` | `includes/admin/traits/trait-admin-page-site-detail-local-removal-preview.php` |
| `includes/traits/trait-admin-page-site-detail.php` | `includes/admin/traits/trait-admin-page-site-detail.php` |
| `includes/traits/trait-admin-page-site-formatters.php` | `includes/admin/traits/trait-admin-page-site-formatters.php` |
| `includes/traits/trait-admin-page-sites-list-context.php` | `includes/admin/traits/trait-admin-page-sites-list-context.php` |
| `includes/traits/trait-admin-page-sites-list.php` | `includes/admin/traits/trait-admin-page-sites-list.php` |
| `includes/traits/trait-admin-page-sites-table.php` | `includes/admin/traits/trait-admin-page-sites-table.php` |
| `includes/traits/trait-admin-page-sites.php` | `includes/admin/traits/trait-admin-page-sites.php` |
| `includes/traits/trait-admin-page-status-formatters.php` | `includes/admin/traits/trait-admin-page-status-formatters.php` |
| `includes/traits/trait-admin-page-status-history-detail-helpers.php` | `includes/admin/traits/trait-admin-page-status-history-detail-helpers.php` |
| `includes/traits/trait-admin-page-time-formatters.php` | `includes/admin/traits/trait-admin-page-time-formatters.php` |

### Source-scoped reviews and proof limits

FEATURE_LIGHT_REVIEW: feature scope path-only admin organization;70 source/POT/
consumer paths reviewed against explicit baseline (57 byte-identical owners,
12 literal consumers,1 POT). Risk areas File Ops/loading/i18n; no changed UI or
request/data behavior. Issues found0, auto-fixed0, manual decisions0. Feature
passes light review; no significant introduced non-security issue. No new code
bloat/abstraction/file split or UI behavior, so no bloat or UI/UX workflow.

Final FEATURE_SECURITY_REVIEW after validation: feature passes security review;
no introduced issue found. Fixed internal paths remain beneath the approved repo,
no dynamic path/nonce/capability/escaping/redaction/secret or new API boundary.
Regression handoff: no confirmed security defect needs new regression coverage.
This is differential source/loading validation, not a whole-plugin security audit.

Existing API/render/permissions/failure fixtures and offline Node interactions
ran on the migrated tree. No genuine browser, installed WordPress lifecycle,
authentication, database/concurrency, deployed direct-include consumer or package
acceptance was rerun. Historical schema122/9, lifecycle264 and write-fence269/8
receipts remain tied to their recorded source images, not refreshed by hashes or
unit tests. Their relevant storage/write-fence bodies did not move; the root
bootstrap's path image changed. Future environment-backed qualification must
deliberately load the new recursive tree and distinguish its source identity.

Historical plan/proof source citations are preserved. This record's exact map
provides current navigation instead of rewriting old paths into fictitious new
acceptance. In particular, dashboard V2_3_FIXED_CLOSED_ENTRY_OWNER_RAW_READER_DESIGN
and V2_3_REAL_AUTHENTICATED_CLOSED_ENTRY_REQUEST_POLICY_DESIGN class-admin-page
citations are uploader history, not D2 rewrite targets. U2/U3 cross-plugin
current mappings will be reconciled after their source CI.

## Final cross-repository reconciliation

D2 final evidence head6dca720deb47ce28cec243387c0cd00030f37e94 passed exact-head
[CI40412497402](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40412497402):
PHP7.4/8.3 each375/1966, no skips/failures; Node16/16 and all configured gates.
Uploader U1/U2/U3 then completed in order; see its
[exact34-file maps, source CI receipts and scope limits](https://github.com/NichlasB/alynt-drime-backups-uploader/blob/3dfcb15b5cab8191df7d984251986ead140ded52/docs/REPOSITORY_ORGANIZATION_MIGRATION.md).

| Uploader batch | Source SHA | Successful exact-head Quality |
|---|---|---|
| U1 Drime11 | 21293008947760601172315a96ca6e97c35b4155 | [40412785811](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/40412785811) |
| U2 admin13 | 2fb348945667a5a588bfd9b8fa174d7143a0b027 | [40413498188](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/40413498188) |
| U3 producers10 | 24fed61e991fc37771fd7a10f368b741dda202ee | [40414117502](https://github.com/NichlasB/alynt-drime-backups-uploader/actions/runs/40414117502) |

Actual logs for every uploader source run: PHP7.4=2311/55847, PHP8.3=2311/55598,
no skips/failures; Node1987/1987 fail0/skip0 and all configured gates. Local
before/after every uploader batch focused76/452, full Node1987/1987, PHPCS255,
syntax/build/runner freshness/strict manifest/audits passed. Full Windows PHP
was not rerun or claimed; full PHP matrix evidence is exact-head CI, unfiltered.
Existing lint deprecation/Git newline notices remain disclosed. Uploader docs
closeout3dfcb15b5cab8191df7d984251986ead140ded52 changes only its owning map,
implementation-plan navigation and local checklist note, not historical rows.
Its final exact-head CI is independently verified at handoff, not inferred from U3.

Fresh cumulative recheck: all57 dashboard and34 uploader moves preserve original
raw SHA256s;136/125 ordered loader entries,69/35 affected PHP syntax checks, no
stale executable old path/case collision. No protected source/test/tooling/
manifest/CI/dependency/asset/runner change in uploader. Covered source-identity
positives and assertion-killed controls pass with no expected hash/grammar update.
Neither runtime depends on the other repository. Public namespaces/payloads,
credentials, schedules, schema/version and dormant/unavailable flags unchanged.

Final layouts match the complete audited map: dashboard includes root24,
traits40, diagnostics15, admin57; uploader flat219, Drime11, admin13, producers10.
All106 approved audit moves are implemented, including earlier D1;91 are this
continuation. Other root/assets/tests/proof-tool/docs/runner categories keep their
audited placement. No additional move is implied by the flat-file counts.

Current-path notes are appended to the four cross-plugin design records named
above; original source citations/proof text are not rewritten. Their uploader
admin/producer paths resolve through the uploader map, not dashboard D2. Both
implementation plans and owning evidence now distinguish completed organization
from pending main work. Shared toolkit checklist remains untouched.

Evidence disposition: historical runtime/native/lifecycle/package receipts remain
true only for their recorded source images; no whole-plugin02/03/04/05/09/13 or
FULL date/checkbox is refreshed. Before release, qualify the relevant new-tree
bootstrap/fixture-manifest/lifecycle/package boundaries under their own authority.
Source-only hash/load/discovery tests do not replace genuine WP/database/browser
or physical concurrency proof, and do not renew deferred native authority.
Ignored/deployed/third-party direct-include consumers remain unknown and untouched.

All approved source gates are complete; final docs-only publication gets its own
exact-head CI at handoff. Organization contributes zero product tasks:4/8 remains.
Return to the saved **Plugin Tester local-only Retention Cleanup, Latest-Snapshot
Preservation and Failure-Reporting Owned Proof** gate. It still needs separate
owned table/options approval and exact local-target confirmation. No main-work
proof, site operation, release or deployment runs under this organization scope.
