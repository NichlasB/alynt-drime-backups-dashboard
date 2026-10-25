# V2.3 Fixed Projection Session/Profile Read-Only Feasibility Audit

2026-10-25. Complete for the approved read-only audit scope; native qualification
remains NO-GO. R0 inspection/R1 documentation publication only. No parser repair
or new implementation milestone. Versions dashboard0.1.77/uploader0.5.25 unchanged.
Excluded coherent review bases: dashboardaae8058553fa4bd2a0de4e27e5aae6c832e96913,
uploader142b5b15991d63ac3b2c3973685e959d1c28e063, toolkit
39d9f8164579d32aefbc791abf820e83c8824005.
Parent: [observer design](V2_3_PROJECTION_OBSERVER_ADAPTER_DESIGN.md); companion
uploader docs/V2_3_PROJECTION_OBSERVER_FOUNDATION.md remains a synthetic candidate.

## Confirmed target and execution boundary

User confirmed site key plugin-tester, mode local-only, LocalWP “Plugin Tester”,
http://plugin-tester.local, WordPress at
`C:\Users\Captain\Local Sites\plugin-tester\app\public`. Novamira HQ guide and
complete inventory read; exact local profile listed. No doctor/discover/WordPress
ability, CLI bootstrap, PHP/runtime probe, database connection, SQL, authentication
attempt, HTTP site probe, fixture/account/grant write, listener/process experiment,
service restart/configuration, native execution or production wiring change.
No backup/restore or release/package/tag/deploy. Repository evidence publication
is separate from the read-only LocalWP inspection and changes Markdown only.

Inspected non-secret source and allowlisted configuration values; no credentials,
cookies, salts, account secrets or raw authentication bytes emitted/recorded.
Public upstream source retrieval is not a request to the LocalWP website.

## Observed installed/configured facts, not running-session evidence

- Installed `wp-includes/version.php` declares WordPress7.1.3 (not the7.1.2 model
  source pin). SHA2567513DACF90ADE66347561FFC2E79D27E74B6A3F05D869BD455144ED7CD378A04.
- Installed `class-wpdb.php` SHA256
  E15403E90032DD0508811301505E491D4212DE5152581A98F51B744A1A3903BD matches the
  retrieved public7.1.3 source bytes. This does NOT verify the whole core tree,
  installed plugin execution, extension loading or a binary reproducible build.
- Fresh selected LocalWP metadata and rendered extension_dir point to PHP8.5.12;
  php-cgi.exe/php8.dll/php_mysqli.dll file/product metadata each8.5.12. Earlier
  pre-confirmation discovery reported8.5.1; do not treat that as current evidence
  or attribute the version change to this audit. No process was queried/executed.
- LocalWP metadata declares MariaDB10.6.23/nginx1.26.1. This does not prove the
  running server version, current process ownership, handshake or capabilities.
- Rendered php.ini enables php_mysqli.dll/php_pdo_mysql.dll, default port10004,
  default socket empty; memory_limit256M/max_execution_time1200/output_buffering4096.
  These are configuration snapshots, not effective runtime values, a new resource
  admission policy or proof of whole-request resource closure.
- Rendered MariaDB configuration specifies loopback/port10004/max_allowed_packet16M.
  Its packet limit is NOT the client's handshake maximum or the decoder's cap.
- Allowlisted direct wp-config definitions: DB_CHARSET='utf8', DB_COLLATE=''. No
  direct MYSQL_CLIENT_FLAGS definition found; indirect/conditional definitions
  and effective values are unknown because configuration was not executed.
- db.php/object-cache.php/advanced-cache.php/db-error.php absent at the four fixed
  wp-content paths. No claim about every include, dynamic replacement or runtime
  global object follows from these file-presence checks.

## Decisive source counterexample and additional gaps

|Candidate requirement|Audited evidence|Decision|
|---|---|---|
|Database local in handshake, exact client mask0x00088209|Installed wpdb1990/1993 passes null as the database argument;2045 selects dbname after connection|Current profile cannot represent the genuine core wpdb connection path unchanged|
|Immediate selected request after three handshake packets|wpdb2040-2045 performs charset setup, SQL-mode handling and later select;916/949/1192 contain corresponding API/statement paths|No complete source-qualified setup grammar in current observer|
|Exact16KiB client handshake maximum|Synthetic fixture only; upstream mysqlnd initializes a different default and serializes session options|Do not equate candidate cap, server16M and actual client advertisement|
|Exact capabilities/charset/plugin/20-byte response/server greeting|Static configuration/package names do not establish negotiated bytes; PHP source adds driver defaults/options|Unobserved; no invented native match or blanket relaxation|
|Pinned7.1.2 bootstrap/profile/resource closure|Installed version marker7.1.3 and selected PHP8.5.12|Broader source/profile pin must be rebaselined, not silently refreshed|

The connection-order finding is direct installed-source evidence, NOT an observed
wire trace or failed authentication. The narrow public7.1.3
[wpdb source](https://raw.githubusercontent.com/WordPress/WordPress/7.1.3/wp-includes/class-wpdb.php)
matches the installed file. A successful genuine-core path invokes charset/API
and SQL-mode setup before later database selection. Reconnect/probe paths also
exist; no assertion of complete command coverage comes from a query filter.

Conditional upstream driver reasoning only: PHP8.5.12
[mysqlnd connection](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_connection.c)
adds CONNECT_WITH_DB when a nonempty database is supplied and merges driver/
option flags. Its [definitions](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_enum_n_def.h)
include capabilities absent from the synthetic exact mask, and a default assembled
packet constant3GiB. [Authentication serialization](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_auth.c)
uses session max-packet/charset settings; the [wire encoder](https://raw.githubusercontent.com/php/php-src/php-8.5.12/ext/mysqlnd/mysqlnd_wireprotocol.c)
omits a database string when none is supplied. These references do not attest the
installed binary's build/options or prove the active driver/session. No runtime
packet values, authenticated identity or grants have been established here.

Application DB_CHARSET='utf8' is not itself a wire-charset counterexample:
installed wpdb881+ may transform utf8 to utf8mb4 and choose collation based on
the connected handle/capabilities. Neither initial greeting nor effective charset
can be inferred from that one define. Do not force settings to make the model fit.

## Outcome, review and next gate

The audit advances the implementation plan from generic uncertainty to a concrete
source-path incompatibility plus current configured-version evidence. It is NOT
an unexplained synthetic-test failure or a production vulnerability. Preserve the
old profile as a fixed synthetic contract; do not weaken its mask, inject fake
success, patch core/driver, force schema selection, suppress setup statements or
rerun a native proof under an unsupported profile. All A1-A4 remain open.

Canonical source-scoped Feature Light: four changed/new plugin Markdown records,
Other/audit; no executable/UI/endpoint/storage change, no significant scoped
record defect. No Bloat/UI/UX or whole-plugin DS3/Database/physical/FULL refresh.
Final focused Feature Security after scope/evidence validation: no introduced
authority/credential path or secret exposure, no security-fix regression handoff.
Documentation/publication checks and exact-head CI evidence live source-scoped
in toolkit records; synthetic tests/CI do not constitute native audit evidence.

Recommended next gated slice: **design-only V2.3 Genuine wpdb Session/Setup
Profile and Source-Rebaseline Design**. Specify versioned core/driver/source
ownership, post-auth database selection, setup/response grammars and explicit
unknowns before implementing a successor or planning any owned native proof.
Keep SQL, actual authentication, storage, fixtures, native execution, grants,
activation, rollback and production wiring disabled.
