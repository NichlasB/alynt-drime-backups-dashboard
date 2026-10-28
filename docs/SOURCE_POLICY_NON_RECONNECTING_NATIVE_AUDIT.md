# Fixed Non-Reconnecting Connection / Native Adapter Audit — Blocked

Later isolated implementation,2026-10-28:
[core placeholder/lifecycle foundation](SOURCE_POLICY_CORE_PLACEHOLDER_LIFECYCLE_FOUNDATION.md)
adds a closed private-context exception and RAM tests. This audit script and its
default no-context refusal remain unchanged/not rerun. Native qualification is
still blocked pending genuine-core context/cleanup and owned native evidence.

Later design-only decision,2026-10-28: the
[core placeholder/lifecycle design](SOURCE_POLICY_CORE_PLACEHOLDER_LIFECYCLE_DESIGN.md)
specifies a bounded exact-receiver exception and its acceptance/cleanup gates.
It does not change the runtime guard or rerun this probe. Native qualification
remains blocked pending isolated implementation and genuine owned evidence;
the original nine-check audit and preserved-state results remain historical.

## Authority, restore point and outcome — 2026-10-28

Approved R3 Plugin Tester local-only Fixed Non-Reconnecting Connection
Construction and Native Adapter Owned Qualification/Cleanup Proof, audit first.
Fresh exact target confirmation: `plugin-tester`, `local-only`, LocalWP
“Plugin Tester”, `http://plugin-tester.local`, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`, database port10004.
Clean/synced restore point: `93f391321506c04af32d6dce1bdf2f337d087776`.
Novamira HQ guide/complete inventory read; exact local URL present. Bundled
mysql.exe was used for initial SQL, not WordPress bootstrap or generic PHP.

**Audit blocked the native qualification before fixture creation.** The
[dormant foundation](SOURCE_POLICY_NON_RECONNECTING_INTEGRATION_FOUNDATION.md)
rejects every non-empty query-hook registry, including genuine core's own
placeholder-escape callback. A native positive adapter operation cannot be
qualified under that admission policy. This is an availability/compatibility
contract conflict, not evidence that an application write occurred or a need
to restart LocalWP. No fixture/native construction/failure experiment ran.

This checkpoint adds only a manual zero-SQL reproducer and owning records.
Runtime includes, loaders, factories, form/handler, tests, assets/catalog,
schema8/version0.1.77, dependencies and CI configuration stay byte-unchanged.
No actual authentication, application writes, WordPress lifecycle/bootstrap,
cron, cache backend, credentials, services, client/Drime, package/release/tag or
deployment changes. Uploader/toolkit remain read-only. No task-count inflation.

## Read-only installed/database audit

Resolved current LocalWP metadata: database10004/web10005, installed core7.1.3,
bundled PHP8.5.12 and MariaDB10.6.23. Database local; autocommit1, no transaction.
wp_options: InnoDB/utf8mb4_unicode_520_ci, expected four columns, full unique
option_name plus primary/autoload indexes. No db.php/object-cache.php/
advanced-cache.php drop-in. Zero pre-existing adbp_/adbn_ proof tables.

Captured all77 existing application table checksums and the table manifest
before the probe; independently reconciled afterward: all77 identical and
zero retained proof tables. No table was created, modified or dropped; no
application option/user/cron payload was read into the probe. Initial SQL was
metadata/checksum inspection only. No owned session termination or listeners.

## Genuine core preparation conflicts with the closed query policy

Source evidence in installed class-wpdb.php:

- `_real_escape()` calls `add_placeholder_escape()` at1288.
- `prepare()` returns `add_placeholder_escape()` at1759, including numeric-only
  preparation, which does not require a native driver/connection.
- `placeholder_escape()` at2413 registers `array($this,
  'remove_placeholder_escape')` on query at priority0 (line2428).
- `remove_placeholder_escape()` at2458 restores protected percent markers.

The foundation's Native_Profile::admit rejects a non-empty query callback list
even when its private descriptor fingerprint exactly matches. Preparing the
owner's observation also changes the fingerprint if admission happened first.
Thus merely moving construction or stamping a later fingerprint does not solve
both guards. Removing core's filter, bypassing admission or using unprepared SQL
would not be a valid native proof. No such workaround was applied.

Installed class-wpdb.php SHA256:
`E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD`.
Runtime source identities remain:

- Native_Profile: `3E07194882B0139B2105763FEE7BC52D30743A1BDC23D1DCB56FED9112DE4DAC`
- No_Reconnect_Connection: `1B444F72A517CF100165C34B3784CA806B80FE495E9AFBEDC12140BF96DC67A1`
- Native_Adapter: `FB691ECDA590CFE1ECDCE36A9DEF2CA79AA656FCE27A499F5D255E3A70ACD809`

## Permanent bounded read-only reproducer and proof limits

[Manual audit](../scripts/manual/source-policy-core-query-audit.php), fixed CLI
flag `--confirmed-plugin-tester-read-only-query-audit`, refuses an existing
ABSPATH and checks the audited core hash. Run in a fresh process:

```powershell
& 'C:\Users\Captain\AppData\Roaming\Local\lightning-services\php-8.5.12+0\bin\win64\php.exe' -n scripts/manual/source-policy-core-query-audit.php --confirmed-plugin-tester-read-only-query-audit
```

Actual result: **9 checks; zero native connections; zero SQL queries**. Uses
genuine core libraries/cache/hook registry and actual profile validator, no
wp-load.php/plugins/configuration/authentication or native wpdb constructor. Reflection
allocates only a handle-free genuine wpdb object. An exact numeric prepare
registers core's callback; matching descriptor DATA is rejected. Finally removes
only that exact private-process callback. The positive empty-query control
passes before preparation: this is not an always-unavailable-only test.

The descriptor is synthetic DATA solely to isolate the validator conflict, not
a qualified session/schema/source grant. No physical driver, authentication,
native mutation/concurrency/cache, minimum-core6.0 or minimum-PHP native proof
is claimed. Existing isolated CI does not execute this installed-core script.

An initial inline probe stopped at its positive control because the standalone
library process had not initialized core's extension-cache flag from null to
false. The permanent reproducer explicitly initializes its private stock-cache
context; no application setting, expected rejection or production guard changed.

One temporary query-refusal bypass assertion-killed the reproducer at its
matching-core-callback rejection check. Restored Native_Profile byte-exact to
the hash above; the restored9-check probe passes. This verifies the diagnostic
depends on the real guard and does not implement an admission exception.

## Validation and source-scoped review

Syntax and targeted PHPCS for the new manual audit pass. Full local PHP1212
tests/5363 assertions passes with only the two existing missing-Sodium signing
skips; Node16/16, PHPCS185 configured files, structure142 runtime/209 support/
174 test files, build and strict Composer manifest pass. Composer/npm audits:
zero advisories. Diff hygiene, scoped links, runtime/test/asset/config baseline
identity and non-target clean-state checks pass. Audit checkpoint
`7a9e6bf014240b8fc5f39eee9b44625a6d16d749` passed
[exact-head CI40657855729](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40657855729).
Actual PHP7.4/8.3 job logs each1212 tests/5370 assertions, no failures/skips;
Node16/16, all configured gates pass, audits report zero advisories. Hosted CI
does not run the installed-core probe or native proof. This successful isolated
publication verification does not remove the native qualification block.

Feature Light scope: one manual audit plus owning records and unchanged
prepare/profile caller context. Risk: backend compatibility and isolated
process/library context. One confirmed compatibility issue is flagged for a
contract decision; zero runtime fixes. No broad refactor/framework/dependency,
UI/UX workflow, unnecessary split or bloat review needed for this bounded tool.

Final focused Security scope: exact CLI/fixed path/source pin, no caller SQL or
credentials, no wpdb constructor/driver or application bootstrap, fixed JSON/
diagnostics, exact callback cleanup and unchanged runtime guards. No additional
security defect found in the audit tool. The compatibility issue remains open,
not a claimed completed feature or whole-plugin review. Historical toolkit
rows and whole07/07A/13/FULL remain unchanged; no Database Review/native credit.

## Implementation-plan progress and exact next gated slice

Native qualification is **blocked**, not completed. The audit found a concrete
core contract missing from the isolated preparation double; its earlier positive
RAM results remain valid only within their documented limits. The real
application lost-update race, native provider and production wiring remain open.

Recommended next gated slice: **repository-only Source-Policy Genuine-Core
Placeholder-Escape Admission and Connection-Lifecycle Compatibility Design**,
design-only. Define exact code-owned core callback/receiver/implementation
identity, preparation-before-fingerprint ordering, multiple owned/global core
connections and exact close-time callback ownership. Keep arbitrary query
transformers refused; do not remove core escaping, copy query or weaken the
non-reconnect/uncertainty boundary. Define non-vacuous genuine-core acceptance
before revising this explicit all-query-callbacks-closed policy.

After approved design and isolated safety implementation, resume the approved
owned proof with fresh readiness/baseline checks under its exact target/scope.
Minimum-core/current-profile native qualification remains required. Do not
connect a production factory/form or deploy on the strength of this audit.
