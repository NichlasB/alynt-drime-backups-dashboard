# Schema Upgrade Failure/Retry Core-Backed Owned Proof — 2026-10-26

## Authority and target

Explicit approval and fresh resolved-target confirmation: `plugin-tester`,
`local-only`, LocalWP “Plugin Tester”, `http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`, database `local` on
127.0.0.1:10004. Novamira HQ guide/complete inventory confirmed the exact URL.
The direct bundled MariaDB client was used first, before any PHP database access.
Initial sandbox connection denial was resolved through the authorized elevated
read-only client invocation, not credential/service/configuration changes.

Repository baseline `9ab20cba47efa5c88bbe97e414661b6ed2a58d61`, master clean.
Production PHP, version0.1.77, schema8, contracts and production wiring unchanged.
No uploader changes, deployment, release/tag/package, installer, account, grant,
user, endpoint or schedule operation. Historical Windows/V2.3 research stays parked.

## Composition and ownership

The explicit manual CLI entry is `scripts/manual/schema-upgrade-owned-proof.php`.
It requires the exact confirmation flag, a fresh `adbpf_<12hex>_` namespace and
in-process MYSQL_PWD; the credential is not recorded or passed as an argument.
The installed library loader composes genuine wpdb, dbDelta, get/update_option,
core cache and hooks without wp-config/wp-load/wp-settings, installed plugins,
active theme, external cache drop-ins or application request/shutdown execution.
Core admin include dependencies register hooks but no admin lifecycle action runs.
Translations use core NOOP_Translations in this private process, not real locale
or authentication proof. No WordPress functions are replaced by test doubles.

Only four absent-before-start owned names are admitted: fixture options plus the
three real schema declarations under that fresh prefix. Actual schema marker
reads/writes use genuine core APIs against the fixture options table, never
wp_options. The fixed-SQL scope assertion rejects application/non-owned targets;
it is not a general SQL firewall for untrusted statements. There are no DB account
or privilege changes. Session statement/lock limits are3 seconds; CLI limit25.

Cleanup is armed only after all manifest collisions are excluded, previews owned
row counts, drops only the exact four names and verifies absence. Catch/shutdown
fallbacks cover ordinary PHP failures, not machine power loss or forced termination.
An external bundled-client final inventory/checksum reconciliation is independent
of the harness. No broad wildcard DROP or purge; all existing tables stay targets
of read-only checksum checks only.

## Actual result

Final post-format/source-review run: **122 checks /9 groups**, seven injected
DDL failures and seven actual MariaDB errors witnessed,424 query-hook observations:

- genuine new installation, physical completion marker and core convergence;
- actual failed CREATE for each of three owned tables, older marker retained,
  then successful genuine retry after removing the fault;
- actual failed ALTER for each of three incomplete owned tables, table presence
  alone insufficient, older marker retained, then successful retry/convergence;
- forced-current reinstall invalidates its marker before failed ALTER, retains
  an empty retry marker and succeeds on retry;
- current-version startup performs no schema queries or writes (core option
  reads may still occur), and actual stored autoload is disabled.

Faults modify only matching owned DDL through the core query filter into invalid
SQL; MariaDB rejects that SQL. dbDelta itself, its planned-change output and
metadata executor are genuine. This is not a mocked false return or lost-wire/
transaction/permission/contention experiment. Success cases are required, so an
always-failing owner cannot pass. All four owned tables removed. Independent
before/final checksums and inventories: **77/77 existing tables unchanged**, zero
retained fixtures from any attempt. Existing options/users/cron/application data
therefore match the baseline; no claim of protection against unrelated concurrent
traffic or every possible database failure.

Profile: installed WordPress7.1.3, bundled PHP8.5.12, MariaDB10.6.23.
Installed source SHA256:

| Core source | SHA256 |
|---|---|
| wp-admin/includes/upgrade.php | 0d1d0e6e9c135452641b5a518ba41f6907e67ac668d27d5e38690f53e846527e |
| wp-includes/class-wpdb.php | e15403e90032dd0508811301505e491d4212de5152581a98f51b744a1a3903bd |
| wp-includes/option.php | fe3f0b7e115b2a50c31162ed6428abe4088c080b0f71b9678d84806168fe90d0 |

## Harness corrections, not hidden successful runs

Read-only composition preflight first needed genuine theme API and Walker
dependencies; these were loaded, not stubbed. No fixture attempt ran until that
preflight passed. First fixture attempt stopped at the scope assertion because
core's upsert column UPDATE was misclassified as a table; cleanup independently
confirmed. Added permanent isolated3/14 regression coverage, preserving rejection
of non-owned targets. Second attempt reached forced-current failure but used
get_var, which maps a real empty string to null. Replaced only the witness reader
with get_row, as core options does. A cached-result diagnostic's get_row(null)
was invalid and is not proof; actual SQL row reads establish the empty marker.
Third attempt passed forced invalidation/retry and then rejected legitimate core
option reads in an empty alloptions fixture. Corrected the assertion to its actual
contract: bounded option-only SELECTs, zero schema metadata/DDL/writes. No fake
autoload record or weakened production guard was added. Fourth run passed122/9;
after scoped PHPCS formatting/comments and local fault-mode variable rename,
the final fresh run again passed122/9. All earlier owned namespaces cleaned.

## Validation and proof limits

Full local PHP315/1597, two pre-existing Windows Sodium skips; isolated SQL scope
3/14 and related schema foundation6/84. Node16/16, no failures/skips. Syntax on
all three manual files; build/structure and PHPCS (including manual files) pass.
Manual-only SQL lint exceptions document intentional owned physical DDL and fixed
identifiers on WP6.0 (no %i); no production ruleset exclusion was added. Scripts
are excluded by the existing release workflow and never automatically run in CI.
Exact-head publication/CI evidence belongs to the toolkit checklist.

This closes the targeted schema failure/retry uncertainty in FULL03, alongside
the existing isolated caller tests and repaired export failure. It does not
complete Database Review, full installed activation/deactivation/uninstall,
minimum-WP/version matrix, authentication, browser, concurrency, atomic DDL,
historical false-current repair, or final Security/FULL release readiness.
Resume the ordered repository-only reviews at04; any further site/lifecycle
operation needs its own explicit fixture scope, not inferred authority.
