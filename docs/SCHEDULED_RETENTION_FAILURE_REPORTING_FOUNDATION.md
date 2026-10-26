# Scheduled Retention Failure Reporting Safety Foundation — 2026-10-26

## Approval, baseline and scope

Approved repository-only repair of the remaining Medium canonical05 finding:
the scheduled retention callback discarded both repositories' `WP_Error` results.
Coherent baseline `41c1fcf8ac3b619392a70585d45be801947e50cb`, master, clean/synced;
predecessor [CI40403355215](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40403355215)
passed. Version0.1.77/schema8/minimum WP6.0/PHP7.4 remain unchanged. No SQL,
cleanup execution, site/WordPress/authentication/native operation, fixture
database, retention/schedule/schema change, package/release/tag or deployment.
Uploader/toolkit and historical whole-plugin review rows are untouched.

External source-folder restore ZIP (not a site backup/package):
`C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots\alynt-drime-backups-dashboard\alynt-drime-backups-dashboard-20261026-230005.zip`,
matching `.txt`, SHA256
`720C3D7FC6633700C3899077689B7F29E5BB9A216B3F6F7ED7F0DE3974333CE0`.
No restore operation ran.

## Plan and implemented contract

1. Retain the existing shared event logger in the plugin owner; no new service,
   constructor argument, loader, dependency or public hook.
2. Attempt both original cleanup calls exactly once, with no arguments, before
   reporting. Their original SQL, batch/retention defaults and return contracts
   are unchanged. The scheduled callback still returns void.
3. Report each `WP_Error` as an `error`/`storage` diagnostic with a fixed internal
   repository-specific code and existing translated safe message. Never forward
   returned error codes, messages, data, SQL, table names or credentials.
   Successful counts, including zero, produce no diagnostic event.
4. Respect existing opt-in and severity threshold. Logging remains disabled by
   default; an error below the configured threshold is also intentionally quiet.
   This is not always-on audit reporting or a new notice. Existing bounded,
   non-autoloaded diagnostic storage is reused, not reconfigured.
5. A logger `false` result causes no retry/fallback, duplicate cleanup or change
   in cleanup order. Durable reporting cannot be guaranteed if diagnostic
   storage itself fails. Unexpected exceptions are not swallowed or modeled as
   successful cleanup; repositories retain their `int|WP_Error` contract.

## Isolated regression evidence

The actual plugin constructor and callback run with the real event logger and
existing option API shims. Only destructive repositories are replaced with
capture doubles. A throwing `wpdb` sentinel rejects unexpected SQL calls;
schema8 is seeded in memory so the constructor does not attempt installation.
Fixture globals are restored after every case. Hook registration uses shims:
this is not genuine WordPress authentication, cron dispatch or storage proof.

Nine permanent cases cover positive/zero counts, each repository failure and
both failures, exact safe fields/empty context/non-autoload storage, diagnostics
disabled/critical-only policies, logger refusal and healthy-run history without
success noise. The constructor path protects real member/logger wiring, not
merely reflection-injected behavior. No old test or assertion was weakened.

Clean RED matrix on unchanged runtime:5 tests/26 assertions,3 behavioral
failures and no errors. Both healthy cases already passed. GREEN new scope:
9 tests/67 assertions, no failures/skips. Three temporary semantic controls were
assertion-killed: omit the error guard (4 failures), omit the second cleanup
call (5), forward raw error text (3). Source SHA256 was restored exactly to
`BE7572202B74855CED922B032B1DD9348947874754BD27D54331C92DFDF9AB31`;
the9/67 suite passed again. No deliberate fault remains.

## Canonical source-scoped review checkpoint

Baseline: `41c1fcf8ac3b619392a70585d45be801947e50cb`. Scope: plugin owner,
new test/support files, two POT references and owning records, plus directly
affected repository retention and diagnostic logger/settings/storage/redactor
contracts. This is a focused differential, not a refreshed whole-plugin05/FULL
or historical lifecycle acceptance result.

FEATURE_LIGHT_REVIEW:

- Feature scope: scheduled retention failure reporting only.
- Files reviewed:10 source/test/POT files, plus owning records and existing API shims.
- Risk areas touched: Cron callback, DB error contract, opt-in diagnostic storage.
- Issues found:0 significant unresolved non-security issues.
- Issues auto-fixed:0 review defects; PHPCBF separately corrected one array layout.
- Issues requiring manual decision:0 within this approved foundation.

Feature passes light review. No significant non-security issues found in changed
files. Both batches precede logging; failure handling has a fixed two-result
bound and at most two existing capped diagnostic writes. No request input,
UI/form/HTML/route, new setting or arbitrary identifier is introduced. Existing
translated messages are reused. Bloat/structure workflow is unnecessary: one
cohesive owner method and one shared member, no runtime file split or new
abstraction; all size limits pass. Backend diagnostics only: no UI/UX workflow.

Final FEATURE_SECURITY_REVIEW after fixes/validation: feature passes security
review; no confirmed introduced security issue. No raw error message/data enters
the event, no arbitrary SQL/identifier/request input is added, and diagnostic
opt-in/threshold/autoload bounds remain intact. Authorization, nonce domains,
REST, filesystem and credential boundaries are unchanged. Regression handoff:
no confirmed security defect requires additional coverage; the intentional
private-error forwarding control is rejected by permanent API-level assertions.
This is not physical DB/transaction, installed-site or final release security proof.

## Validation and publication

Local PHP8.5.1 full375 tests/1959 assertions, no failures/errors, same two
pre-existing missing-Sodium signing skips; focused9/67 has no skips. Baseline
full366/1892 had those same two skips. Node16/16, PHPCS149 files,
structure136 runtime includes/195 support helpers/144 test files, asset build,
strict Composer validation, Composer/npm audits (zero advisories) and diff
hygiene passed. No tracked asset changed.

Tool-only WP-CLI2.12.0 extraction ran before WordPress load with an owned empty
configuration and `--skip-packages`; build/docs/scripts were excluded. Only two
actual new caller references were projected into the existing POT entries.
Validator confirmed831 baseline/tracked entries including header,832 in full
extraction; unrelated historical missing-entry/reference drift is preserved.
No new message or broad catalog regeneration is bundled.

Publication and exact-head CI are pending; count this as the fourth of eight
safe-local product tasks only after push and successful exact-head verification.

## Proof limits and recommended next gate

The Medium source defect is repaired, not physically qualified here. No real
DELETE, batch bound, latest-snapshot preservation, incomplete/recent-action
preservation, genuine option failure or scheduled cron execution was exercised.
Historical lifecycle/schema proofs retain their recorded source scope; the
modified plugin owner does not silently refresh their acceptance. The High
poll-completion physical proof remains separately recorded, not rerun.
Canonical05 and full release readiness remain pending, deferred V2.3 stays parked.

Recommended next gated slice: **Plugin Tester local-only Retention Cleanup,
Latest-Snapshot Preservation and Failure-Reporting Owned Proof**. Approve only
owned disposable table/options fixtures, real bounded cleanup/error reporting
tests and exact cleanup, then confirm the exact local target. Preserve existing
application tables/options/users/cron and wiring; no policy/schema/schedule
change or application cleanup. This qualifies the still-unproved destructive
retention boundaries before closing Database Review; it is not another generic
maintenance slice or authorization to run cleanup now.
