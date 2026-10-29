# Minimum-Core/PHP7.4 Isolated Artifact and Fixed Runner Design

## Authority and decision — 2026-10-29

Approved repository-only design, based on clean/synced dashboard
`f26a1d474859f6f2968310ed005d64ea86c2c6c8`. No archive acquisition, extraction,
installation, executable loading, site inspection, SQL, fixture or native proof
is authorized or performed by this slice. Ordinary repository validation and
push-triggered CI remain in scope; no native/manual workflow is dispatched.
Runtime, public factories, legacy writer, support declarations, schema8,
version0.1.77, ADBC1/ADBC2 acceptance and production/form wiring remain unchanged.

Select one portable, disposable **Windows x64 NTS PHP/mysqlnd7.4.33 + genuine
core6.0** candidate, outside LocalWP. A later native attempt must separately
admit numeric loopback TCP and exact server `10.6.23-MariaDB` under the existing
transport/runtime predicates. No minimum socket row or server-suffix relaxation
is proposed. This is legacy compatibility qualification, not a recommendation
to deploy an unsupported PHP release. No compiler, SDK or Build Tools is needed
to consume the selected prebuilt ZIP; missing runtime prerequisites must stop
the process, not initiate installation.

## Source baseline and limits

Owning inputs: [qualification closure design](SOURCE_POLICY_SUPPORTED_PROFILE_QUALIFICATION_CLOSURE_DESIGN.md),
[supported-profile foundation](SOURCE_POLICY_SUPPORTED_PROFILE_FOUNDATION.md),
[current Windows proof](SOURCE_POLICY_WINDOWS_CURRENT_QUALIFICATION_OWNED_PROOF.md),
and [implementation plan](IMPLEMENTATION_PLAN.md). Source inputs inspected:

- `scripts/manual/source-policy-supported-core-pins.php` and
  `source-policy-supported-profile.php`: pure fixed source/profile DATA, not
  readiness, installation or qualification authority.
- `source-policy-proof-reflection.php`, `source-policy-ambient-proof-operation.php`,
  `source-policy-adapter-proof-cases.php`, `source-policy-adapter-proof-scope.php`
  and `source-policy-bootstrap-native-cases.php`: existing proof composition.
- `includes/class-source-policy-native-profile.php`,
  `class-source-policy-transport-profile.php` and
  `class-source-policy-bootstrap-evidence.php`: unchanged native admission rules.
- Existing Linux qualification escaping case, isolated profile/reflection tests,
  ordinary CI and packaging rules: directly affected future consumers.

The preceding Windows proof395/9 used core7.1.3/PHP8.5.12, not this candidate.
Its quoted/control/NUL marker coverage is not the complete four-byte escaping
matrix. Prior read-only installed-profile observations found no LocalWP7.4
runtime; they are historical evidence, not a fresh inventory or acquisition
grant. No artifact availability, DLL compatibility, environment admission,
minimum native result, whole bootstrap/authentication or release readiness is
established by this document or by PHP7.4 isolated CI.

## Fixed artifact identities and provenance

Public publisher metadata was read in memory; no binary/archive was fetched.

| Input | Fixed expected identity | Evidence and remaining obligation |
| --- | --- | --- |
| PHP Windows binary | `php-7.4.33-nts-Win32-vc15-x64.zip` | Official [release directory](https://downloads.php.net/~windows/releases/) and [publisher checksum manifest](https://downloads.php.net/~windows/releases/sha256sum.txt). |
| ZIP SHA256 | `14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace` | Expected publisher value, not a digest of downloaded bytes in this slice. |
| Core source | `WordPress/WordPress` commit `cc101b64012b16d087780657a2b828ccd7794a63` | [Immutable commit](https://github.com/WordPress/WordPress/commit/cc101b64012b16d087780657a2b828ccd7794a63), tree `a3bd7a4074a4c95c8af3b969346726273d68e167`; genuine version6.0 plus the existing11 independent SHA256 source pins. |
| Non-executed fixture DDL reference | `wp-admin/includes/schema.php` at that commit | SHA256 `788cb6b37c80d9eff4c15863d34fb8e2a2d30207c7a455575ca1ec4e49614a3a`; [immutable source](https://github.com/WordPress/WordPress/blob/cc101b64012b16d087780657a2b828ccd7794a63/wp-admin/includes/schema.php). Not an additional bootstrap grant. |

Future acquisition must use the exact HTTPS PHP URL
`https://downloads.php.net/~windows/releases/php-7.4.33-nts-Win32-vc15-x64.zip`
and the already reviewed expected digest, checked **before extraction/loading**.
No latest alias, alternate TS/x86/debug/source ZIP, mirror, silent checksum
refresh or archive-directory substitution. A fixed-host redirect policy must
reject other destinations. A digest-keyed cache still requires full byte
revalidation; absence/mismatch is refusal, not permission to choose another build.
The PHP source-tarball hashes from the release API are not Windows ZIP hashes.

Select the fixed core transport
`https://codeload.github.com/WordPress/WordPress/zip/cc101b64012b16d087780657a2b828ccd7794a63`,
with no redirects or alternate ref. Admit only its exact commit-named wrapper,
then reconstruct the complete Git tree from entry names, byte contents and
reviewed file modes; compare its root object ID with the pinned tree above.
Reject symlinks/gitlinks/unknown modes, missing/extra entries and any unsupported
tree representation rather than trusting a filename or API's recursive listing.
Independently recheck version6.0 and the existing11 per-file SHA256 source pins.
A generated source-archive SHA256 alone is correlation, not publisher provenance;
do not invent a published archive checksum. The future implementation must test
the exact tree reconstruction on synthetic fixtures before approval to fetch.
No moving branch/tag, checkout hooks, filters, submodules, LFS, installed-core
copy/relabel or production source is admitted. Core and schema sources are DATA
until a separately reviewed finite library include closure is allowed; never
load `wp-config.php`, `wp-load.php`, installers, themes or a full WordPress site.

The [official Windows guidance](https://www.php.net/~windows/) identifies VC15's
Visual C++ Redistributable prerequisite; current PHP8 build guidance is not proof
of PHP7.4 compatibility. [PHP EOL](https://www.php.net/eol.php) remains a known
constraint. No automatic redistributable download/install, PATH/registry/global
INI changes, LocalWP replacement, web routing or service restart is proposed.

## Owned provisioning and extraction contract — not implemented

Use a new controller-generated `alynt-minimum-<32hex>` child beneath a resolved
temporary directory, with exact run/ownership marker and separate runtime/source
children. Reject aliases, links/junctions/reparse points and pre-existing targets;
never accept caller-selected roots or clean a temp parent, repository or site.
Use ordinary owned fixture trust, not a claim of isolation against a compromised
same-user/operator. No new account, SAM, privilege or protected-identity design.

Design ceilings, **not measured archive facts**:64MiB per archive,8192 entries,
256MiB expanded PHP/128MiB core/384MiB combined,240-character normalized output
paths and10-minute outer provisioning lifetime. Exceeding a ceiling stops for
review; it must not enlarge native deadlines. Before writing any extracted entry,
validate the complete entry inventory: reject absolute/drive/UNC paths, traversal,
NUL, ADS/colon, reserved Windows names, trailing dots/spaces, case-colliding or
duplicate paths, links, unknown kinds, overwrites and count/size bombs. Resolve
every output beneath the exact new child; blind extraction followed by inspection
does not qualify. Partial failures retain only safe non-secret diagnostics and
perform exact ownership-checked cleanup, never broad recursive removal.

Static preflight freezes source/runtime/harness/controller bytes and the intended
launch profile: exact `php.exe`, `php7.dll`, `ext/php_mysqli.dll`, necessary runtime
dependencies and approved INI/environment paths. A recorded digest correlates
those bytes; self-recording does not establish their provenance. Reject unexpected
PE architecture/imports or external DLL lookup dependencies; unresolved required
runtime compatibility is a blocker. No secrets in artifact markers, source images
or logs. No dependency installation or optional extension discovery fallback.

## Fixed minimum runner boundary — future implementation

Use a separate small fixed minimum worker/controller, not a generalized executor
or a widened current-Windows8.5.12 switch. Consume only the controller-owned,
revalidated artifact descriptor; no arbitrary executable/core/table/SQL/callback
inputs, provider registry, user path override or write-capable returned handle.
Keep existing Windows/Linux image identities and historical proof receipts intact.
Shared pure source/byte DATA can be reused without conflating native qualification.

The minimum profile must check actual Windows/x64/NTS PHP7.4.33 and mysqlnd7.4.33,
genuine internal mysqli symbols, and exact owned module/source identities. Launch
with `-n`, the explicit owned mysqli module and fixed child-only configuration;
strip inherited prepend/append/PHPRC/INI-scan injection, without altering the
parent environment. Reporting mode must genuinely begin and remain **OFF0**;
do not toggle it to3 or manufacture expected facts. Keep the genuine core-owned
ambient boundary and source-bound observer; do not inject ready/facts/native
handles or call public factories into availability. Optional JSON is not assumed
under `-n`: use a bounded primitive transcript, not optional-extension loading or
object deserialization. Type/stage/count diagnostics must redact SQL, credentials,
raw stderr and connection strings and preserve the first failure through cleanup.

The shared reflection helper already conditionally uses `setAccessible` before
PHP8.1. Remaining direct private `bootstrap` invocation/property reads in proof
cases still need a **proof-only legacy-safe** boundary; current helper coverage
does not prove the complete worker runs on7.4. Test removal/bypass controls must
exercise actual required predicates, not accept an owner that always refuses.
No artificial positive path, suppressed guard or fallback source is permitted.

Minimum fixtures must use core6.0 autoload `yes/no` (`no` for saved policy), not
the current proof's hardcoded `off`. Future owned DDL is a reviewed transcription
of the pinned core6.0 options definition (four columns, primary option_id, unique
option_name, autoload index), with exact unchanged InnoDB/utf8mb4_unicode_520_ci
admission. Do not clone installed7.1.3 schema and label it6.0, execute schema.php,
repair application tables or copy application rows. Derive an owned table name
only after target confirmation; no database account/permission change is implied.

Retain CAS/noop/stale-before-already-satisfied, byte/length/autoload equality,
creation-contention/no-replay, callback/reentry, cache invalidation, lock failure,
direct errno and uncertainty/release cases. Each genuine positive needs paired
malformed/source/mode/transport refusals and non-vacuous guard-removal controls
in isolated tests. Record actual native commands/cost, not one API call=one query.
The complete escape probe must preserve quote, backslash, NUL, percent and UTF-8
four-byte globe bytes, with fixed quoted alias ``SELECT %s AS `escaped` `` (the
inner alias is SQL identifier quoting). Reference probe-expression SHA256 from
the existing Linux case: `dd87c5cfcb62310d9636bc9951f5abcc8dcdb60d14eb8b53518a21577bdd67b5`.
Do not normalize via JSON, substitute ASCII or remove core collation/query checks.
Missing mbstring/non-ASCII paths may introduce genuine extra core reads: explicitly
audit/pin the reachable closure and measure physical cost before admission, never
silently expand the include set or production budgets.

Preserve initializer20s/16 command-effect credits, connect2s/read3s/statement3s/lock1s,
body<20s and process25s. Bounded stdout/stderr, owned process termination and
cleanup must be proved socket-free before any connection. Independent all-server
live-held and post-exit session/transaction reconciliation must include NULL and
foreign database sessions, excluding only the exact observer; correlate run/image/
PID and held terminal state. Do not turn ADBC2 diagnostic counter divergence into
a release exception or reuse its8.5.12 profile as minimum evidence. Define any
minimum-specific receipt producer/validator together, without weakening ADBC1/2.

## Ordered admission gates and remaining work

1. **Next repository-only foundation:** implement only synthetic archive/PE/
   ownership/profile/transcript admission and fixed runner contracts, plus
   isolated API/IO tests. No downloads, extraction, child runtime loading,
   authentication, SQL or native execution. Keep production wiring closed.
2. **Separate acquisition approval:** exact owned artifact fetch/static safe
   extraction/tree verification and cleanup. The fixed source transport/tree
   validator must pass isolated review first. No PHP execution or installation.
3. **Separate runtime preflight approval:** owned legacy binary/module launch,
   version/driver/mode/stdio/cleanup checks only, with no authentication, SQL,
   storage fixtures or native database handle. Missing dependencies stop here.
4. **Separate native proof approval and exact target confirmation:** fresh Site
   Operations/Novamira checks, artifact revalidation, genuine pinned-library guards
   and no-fault/cleanup prerequisites, application inventory/checksum baseline,
   then one ordered bounded attempt in an empty owned minimum-schema fixture.
   Reconcile all-server held/post state, original checksums and exact fixture/
   process/artifact cleanup. No automatic retry, application/bootstrap/lifecycle/
   users/options/cron/permission changes or production integration is authorized.

This is a finite minimum-profile lane, not authority to reopen protected staging,
compiler installation or deferred feature execution. Actual acquisition/runtime/
native proof and remaining current-profile full-byte coverage are still separate
gates before provider/form integration. No whole-plugin Database Review, native
transaction proof, authentication or release-readiness credit from isolated tests.

## Source-scoped review, validation and publication

Design review covers expected-versus-observed identity, source provenance versus
correlation, pre-load extraction trust, DLL/search/config isolation, legacy API/
autoload differences, complete escaping/cost, non-vacuous tests, finite ownership/
cleanup and explicit permission boundaries. No UI/UX workflow or historical
whole-plugin checklist row is refreshed. Only this record, the plan resume entry
and current proof's forward pointer change. Executed repository QA and exact-head
ordinary CI evidence are recorded below after validation; native evidence is not
rerun for documentation publication.

Executed local repository QA (existing PHP8.5.1; no candidate runtime loaded):

- Targeted supported-profile/reflection/transport/bootstrap/context/adapter
  isolated family:276 tests/1,259 assertions, no failures/skips.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,043 tests/
  9,208 assertions, no failures; two existing missing-Sodium skips (invalid-key
  and signature roundtrip). Neither is minimum-runtime/native qualification.
- PHPCS229 files; admin interaction23/23, no skips; structure149 runtime/
  227 support/207 tests/204 class names; asset build:all pass.
- Composer strict manifest validation/audit and npm moderate audit pass, zero
  known advisories/vulnerabilities; no install/update or dependency delta.
- Local owning-record links, scoped diff and whitespace review pass. All tracked
  non-documentation paths remain identical to baselinef26a1d4; uploader/toolkit
  remain clean at3dfcb15/c067eb6 and are not modified by this slice.

Implementation-plan progress: the selected minimum artifact/runner design is
complete; no artifact has been provisioned or physically qualified. Recommended
next gated slice: **Repository-only Minimum-Profile Artifact Admission and Fixed
Runner Safety Foundation**, synthetic archive/PE/profile/transcript and isolated
API tests only, with downloads, runtime loading, storage/native execution and
production wiring disabled.

Design publication `0d743adcc8072cc6c2643027bd85dfd905e910fc` is pushed;
ordinary [CI40882215816](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40882215816)
succeeded at that exact head. Actual PHP7.4/8.3 logs each report2,043 tests/
9,215 assertions with no failures/skips; admin23/23 each, no failures/skips.
Manifest/dependency audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. Existing Node setup/cleanup DEP0040/DEP0169 warnings are nonfatal;
no dependency update is included. This evidence-only closeout receives separate
pushed exact-head ordinary CI and clean/remote verification at handoff, without
repeating site, artifact or native work. It does not qualify the proposed Windows
PHP7.4 executable, minimum-core library/native path or release candidate.
