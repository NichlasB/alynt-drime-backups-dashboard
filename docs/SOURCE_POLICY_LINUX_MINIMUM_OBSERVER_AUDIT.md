# Linux Minimum-Lab Existing-Authority Host-Observer Feasibility Audit

## Approved authority and checkpoint — 2026-10-31

Clean/synced restore baseline `4d6a917b722d413e1269913eee3177b5c98677bc`.
User approves the [design's next gated audit](SOURCE_POLICY_LINUX_MINIMUM_PREFLIGHT_ADMISSION_DESIGN.md#host-observer-feasibility-is-the-next-gate),
including the bounded repository harness/tests, validation, commit/push, exact-
head ordinary CI and one physical manual audit on an ephemeral Linux CI host.
Resolved target: `NichlasB/alynt-drime-backups-dashboard`, master, GitHub-hosted
`ubuntu-24.04` Linux/x86_64, new manual-only observer workflow. Execution head
will be the exact published implementation commit after ordinary CI succeeds;
the dispatch input must equal both that SHA and the runner checkout SHA.

Existing host Python/tools only. No candidate image/artifact acquisition,
Docker operation, candidate/runtime PHP loading or execution, WordPress/core
bootstrap, database/authentication/SQL, installed site, secret/credential,
permission/capability/principal change, elevation, installation, package/release,
deployment or production wiring. No LocalWP/live target or Novamira operation
applies. Uploader/toolkit stay read-only; no whole-plugin readiness row changes.
Only owned run-specific temporary filesystem fixtures and one harmless child
may be created. Initial private object modes are creation defaults, not chmod
or changes to any existing object's permissions.

## Fixed harness and bounds

- [Workflow](../.github/workflows/source-policy-linux-observer-audit.yml): manual
  dispatch only, master/exact-head/repository gates, contents read, pinned source
  checkout without persisted credentials, existing `/usr/bin/python3 -I -S -B`,
  no setup/install action, three-minute job deadline and always cleanup. Neither
  ordinary pushes nor ordinary CI can invoke the physical audit body.
- [Supervisor](../scripts/ci/source-policy-linux-observer-audit.py): verifies
  checkout/head, isolated existing Linux tool and absence of set-ID/file
  capabilities on it. Freezes supervisor/workflow/executable hashes; binds actual
  child PID/parent/start identity through a held proc-directory FD. Records
  current/child UID/GID, effective capability mask and NoNewPrivs; no authority
  is changed. The harmless child receives only `LANG`, no inherited secrets.
- One fixed child maps a parent-created 4,096-byte regular file read-only,
  signals readiness and waits at most 20 seconds for its fixed release byte.
  It never executes fixture bytes, connects, writes files or starts descendants.
  Parent uses device/inode, never a child/map pathname as a host filename.
  It attempts the known fixture's kernel `map_files` object first, then all
  unique regular mapped objects only if that succeeds. No alternate path read
  can turn a denied kernel-object read into availability.
- 32 KiB maps payload / 256 rows / 64 unique files, one payload read plus a
  one-byte EOF completeness check (never stitched snapshots). Two fixed
  observations; each object at most 128 MiB, at most 256 MiB hashing per phase,
  1 MiB hash reads. Parent deadline 25 seconds; bounded two-second release/
  TERM/KILL waits and outer 45-second timeout. No limit or privilege fallback.
- Run-ID/attempt-owned child of the exact runner temp directory, exclusive
  owner/child receipts and one `mapped.bin` only. Cleanup validates private root,
  source/head/UID ownership and every flat child before deleting exact files
  and empty root. Unknown content/identity retains and reports failure, not a
  recursive delete. Main supervisor signals only its own `Popen` child; the
  independent cleanup entry only waits for a recorded PID/start identity and
  never signals a PID obtained from a file. Source-bound launch-gap uncertainty
  is unconfirmed cleanup, not invented process-release evidence.

`available` requires complete bounded kernel-object reads at both barriers and
stable backing-file cohort, unchanged authority/source and confirmed child/
stage cleanup. `unavailable` records actual access denial with fixed operation,
errno and safe counts; no path/stream/error-text dumping. Either can represent a
successfully **completed audit** after cleanup. Exit 0/green audit job therefore
does not mean observer availability or candidate qualification. Unexpected
failures or incomplete cleanup/source readback exit nonzero. Every report keeps
`candidate_qualification=not-established`. No audit outcome authorizes candidate
loading, container observation, native admission or production wiring.

## Repository-only tests and pre-dispatch state

[PHP source/entry gates](../tests/SourcePolicyLinuxObserverAuditTest.php) and
[pure Python fixtures/mocks](../tests/support/source_policy_linux_observer_test.py)
do not run the owned Linux body locally. Cover positive stat/maps/authority and
cleanup membership, strict bounds/overlap/identity, complete read/EOF, actual
mocked backing-object hash success, distinct denied-object facts, fixed child
read-only protocol and inactive/foreign invocation refusal. The test API import
and suite block network/process creation; proc/file-object reads are mocks.

Seven trusted in-memory source controls are assertion-killed, not counted as
syntax/import/harness errors: maps byte/row/overlap bounds, cleanup membership,
PID, truthful audit-completion criteria and always-unavailable observation.
Disk source remains unchanged by controls. Red baseline failed because the
new harness/workflow did not exist. Initial redundant negative fixtures were
strengthened to isolate each removed bound; Linux constants in Windows mocks
are test-only, not weakened live guards. Child protocol constants and syntax
are checked without executing it on Windows.

Narrow local result: 6 PHP tests / 51 assertions; 10 Python groups, no failures/
skips. Adjacent entry/qualification contracts: 39 tests / 438 assertions, no
failures/skips. Full local PHP 8.5.1: 2,446 tests / 23,000 assertions, no failures,
two existing Sodium signing skips. PHPCS 244 files; admin 23/23 with no failures/
skips; structure 149 runtime / 235 PHP support / 228 test files / 225 classes;
build passed without tracked asset changes. Strict Composer validation,
Composer audit and npm moderate audit passed, zero known dependency advisories/
vulnerabilities. Whitespace and 170 owning local documentation targets passed.
Final scoped source/security/error/resource/ownership/cleanup/test-integrity
review preserves all runtime, artifact pins/reader, existing tuple/profile,
ADBC1/ADBC2, dependency and existing workflow sources. Uploader/toolkit remain
clean/read-only. No Windows physical audit, site proof or native run occurred.

## Published implementation and single physical result — 2026-10-31

Implementation `29195b02ee01b6e27eab376cf0235e5b5224d424` is pushed;
[ordinary exact-head CI 41197313747](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41197313747)
passed before dispatch. Actual PHP 7.4: 2,446 tests / 23,002 assertions;
PHP 8.3: 2,446 / 23,007, no failures/skips. Admin 23/23 per matrix, no
failures/skips; other configured lint/build/structure/manifest/audit checks passed.
These isolated/source tests did not establish physical observer feasibility.

Exactly one [manual audit 41197435627, attempt 1](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41197435627)
ran at that same implementation head on GitHub-hosted Ubuntu 24.04.5 x86_64,
runner image `20261025.353.1`, existing Python 3.12.3. Audit step failed:
`ADBLHO1`, `status=refused`, `reason=proc-read-incomplete`,
`phase=mapped-file-observation`. The report has no completed startup/final
snapshot or access-denial facts. The source reason is emitted only when the
one-byte EOF check after a bounded proc payload read returns another byte.
At this phase, the first snapshot reads process stat and maps; the current
report does not distinguish which read produced the reason or its payload size.
Do not infer truncation beyond 32 KiB, a particular kernel short-read behavior,
or a `map_files` capability denial from this result. Neither availability nor
unavailability of the required kernel-object observer has been established.

Observed parent/child UID/GID 1001/1001, effective capabilities
`0000000000000000`, NoNewPrivs 0; authority readback unchanged. Child PID 2395 /
start 15498 was reaped, supervisor reported `cleanup=confirmed`; the independent
always-cleanup process reported `owned_stage_absent=true` for exact stage
`adb-linux-observer-41197435627-1`. Its disposable files/root were removed and
cannot be recovered. Both reports bind source image
`ee3815418042444bdce03f2092f89231e00612bff6fed992660ee091db82d78c`;
supervisor reports source unchanged. No candidate images/execution, database or
privilege changes occurred. `candidate_qualification=not-established` remains.
Stage absence is an independent filesystem readback; it is not a second
independent post-exit process census or whole-host teardown attestation.

No harness correction, raised limit, privilege fallback or second physical
attempt follows this failed verification. Evidence closeout changes only this
record and current plan/design resume references; executable sources remain at
the attempted cohort. Ordinary CI on that docs-only closeout must not be
reported as recovery of the failed physical workflow.

Docs-only closeout checks: adjacent 39 tests / 438 assertions passed with no
failures/skips; whitespace and 171 owning local documentation targets passed.
Only three owning records change. Uploader/toolkit remain clean at original
`3dfcb15` / `c067eb6`; no executable or test repair is included. Exact-head
ordinary CI and remote/clean verification are required at final handoff.

Implementation-plan progress: observer feasibility remains unresolved; the
safe-local runtime/DATA lane is blocked, not a new maintenance queue.

Recommended next gated slice: **Harness-Only Linux Proc-Read Completeness
Diagnostics and One Fresh Existing-Authority Harmless-Child Audit**. Approve
fixed proc-role/byte-count diagnostics, source-backed isolated regressions,
fresh ordinary CI and one new bounded owned audit, with the existing single-
payload/EOF rule, budgets, kernel-object access guards and cleanup unchanged.
First distinguish stat/maps and actual read shape; do not silently assemble
multiple snapshots, enlarge limits, assert missing capabilities or change the
observer trust model. Any required read-policy change needs a separate explicit
decision after those facts. No candidate loading, site/database work or wiring.
