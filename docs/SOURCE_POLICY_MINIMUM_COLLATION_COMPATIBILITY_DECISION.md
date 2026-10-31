# Minimum-Lab Genuine-Core Collation Compatibility Decision

Status: proposed acceptance revision, not approved or implemented. Production
wiring and native qualification remain disabled/unproved. This decision package
is prepared under the autonomous campaign's source-scoped design authority;
changing the acceptance criterion needs the campaign's explicit exception gate.

## Exact evidence boundary

Campaign baseline: `1b739b8389efbbed6fb928b653486a308af1d8de`. Native attempts
41238716042, 41239253831 and 41239642165 isolate the pre-probe server comparison.
The last establishes the exact raw PHP7.4.33/mysqlnd API identity
`5.5.5-10.11.18-MariaDB-ubu2204`; independent control SQL VERSION() remains
`10.11.18-MariaDB-ubu2204`. The source-backed correction is lab-only
`b16d70e72b59e487825b07b36722c8d112634eec`, ordinary CI 41239966226 successful.
No image/core/runtime source pin, physical version, default production row,
source/receiver binding, query budget or exact string guard was relaxed.

[Attempt 41240099125](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41240099125)
at that source passes fresh audit, exact server comparison and all eight
bootstrap steps / twelve effect credits, then refuses at line 153, the combined
session/schema validation boundary. Return remains fixed WP_Error, ready=false,
context/native closed, owned handle released. No completed nine-group body,
escape-byte round trip, final bound-profile publication or closed-proof terminal.
The failure packet does not export individual readback fields: it is not proof
that schema passed or that collation was the sole failing field.

External owned cleanup and always-recovery remove resources without ownership,
fixture-absence, all-server reconciliation or physical-removal issues. Failed
PHP exit keeps strict functional release incomplete. See the complete
[campaign ledger](SOURCE_POLICY_MINIMUM_TRUSTED_LAB_INTEGRATION.md#autonomous-campaign-authority-and-attempt-ledger--2026-10-31).

## Source-derived contradiction, separate from native field observation

Official WordPress 6.0 source commit
`cc101b64012b16d087780657a2b828ccd7794a63`, pinned wp-db.php SHA256
`f07bc6403ab6db30195cca8e47a384de11d582a66a64cb50449d4335edcc24cf`:

1. db_server_info returns the raw mysqli API string.
2. db_version keeps the numeric/dot prefix, yielding 5.5.5 for that string.
3. has_cap('utf8mb4') accepts this server/client combination, but
   has_cap('utf8mb4_520') requires >=5.6 and returns false.
4. For the existing DB_CHARSET=utf8 and empty DB_COLLATE, determine_charset
   selects utf8mb4 / utf8mb4_unicode_ci, not utf8mb4_unicode_520_ci.

The current [session validator](../includes/class-source-policy-bootstrap-evidence.php)
requires the latter exact helper collation and matching session readbacks.
That requirement and the pinned genuine helper behavior conflict. The source-
derived fixed API regression reproduces the former output and still requires
refusal at eight steps/twelve credits. It is not genuine core execution.
Existing positive model cases use their pre-existing explicit collation and
cannot substitute for qualification of this empty-collation native case.

## Preferred bounded revision requiring approval

Accept only the exact private minimum-lab profile's genuine helper-derived
connection collation `utf8mb4_unicode_ci`. Do not force DB_COLLATE, replace core
helpers, strip the server string, change pins or silently admit arbitrary
collations. Keep all three session character sets exactly utf8mb4 and require
the actual helper/session collation to agree with the code-owned profile.
The private source/run/receiver/context binding must choose the expectation;
no caller-selected descriptor, option, environment grant or generic fallback.
Default production profile behavior stays unchanged and unavailable.

Keep the physical table/schema requirement exactly utf8mb4_unicode_520_ci,
InnoDB, fixed four columns/three indexes/unique option_name. This is a connection
profile revision, not a schema/configuration rewrite or new production support
declaration. Preserve the 20-second/16-credit initializer and all outer limits.

The adapter's authoritative read and conditional UPDATE use exact BINARY
option_name, option_value and autoload predicates plus OCTET_LENGTH; inspect
[their actual SQL](../includes/class-source-policy-native-adapter.php) during
the focused review. These protections are reasons to evaluate this narrow
revision, not proof that every collation interaction is harmless. INSERT uses
the unchanged schema's unique index; real duplicate/race/byte behavior must be
requalified. Never infer success solely from row equality or a model.

## Required proof before closing the milestone

- Source-scoped admission/error/edge/security review of the exact policy delta.
- Positive bound-profile tests and negative wrong/unknown/unscoped profile,
  helper/session disagreement, charset/collation drift and schema tests; required
  guard bypasses must kill assertions. Existing strict default tests remain.
- Targeted local validation, commit/push and ordinary exact-head CI.
- Fresh existing trusted-lab audit and the complete unchanged native nine-group
  body, byte probe, mode0/once-only/no-reconnect behavior, and independent live/
  post-exit release plus exact owned cleanup. Do not add a broad SQL observer.
- Retain original core/runtime/image/configuration, support-coverage and trusted-
  host limits. No whole-plugin readiness, transaction-uncertainty, intended-site,
  minimum-database-version or production-wiring claim follows from this lab case.

No fifth native attempt or acceptance implementation is authorized by the
current campaign while this decision is pending. Recommended next action:
approve this exact lab-only collation acceptance revision and its bounded owned
requalification, then resume the campaign with four of ten attempts used.
