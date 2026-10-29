# Minimum-Profile Selected PE Import Classification and Owned Proof

Forward progress: the approved [dependency/runtime-preflight integration design](SOURCE_POLICY_MINIMUM_RUNTIME_PREFLIGHT_INTEGRATION_DESIGN.md)
defines the next closed boundary without acquisition, profile inspection or
loading. Follow its ordered gates; this historical owned proof is not rerun or
upgraded to runtime/dependency closure evidence.

**Outcome: static acquisition, complete source identity, selected direct-import
classification and exclusive payload readback passed; exact cleanup confirmed.**
Dependency presence/transitive closure and candidate-runtime/native qualification
remain unproved. Nothing downloaded was installed or executed.

## Authority, restore baseline and boundaries — 2026-10-29

Explicit R3 approval of **Harness-only Selected PE Import Diagnostic and
Evidence-Based Dependency Classification with One Fresh Owned Acquisition/
Cleanup Proof**. Clean/synced restore baseline
`561184756a25f1fe62c696f736f2c7d42ee34d96`; follows the
[core ZIP mode proof](SOURCE_POLICY_MINIMUM_CORE_MODES_OWNED_PROOF.md) and the
[minimum artifact/runner design](SOURCE_POLICY_MINIMUM_ARTIFACT_RUNNER_DESIGN.md).
One bounded PHP-only diagnostic, cleanup, evidence-supported classification,
fresh prerequisites, then one complete acquisition/readback/cleanup attempt.
No automatic retry or further native experiment is authorized by this record.

No installation, downloaded PHP/DLL/core loading/execution, WordPress bootstrap,
LocalWP/site/database/live work, SQL, credentials, existing permissions/state,
global configuration/CA store, schedules, production/form wiring, native guards/
budgets, dependency installation/update, version/schema/support changes,
ADBC1/ADBC2, package/release/tag or deployment actions. Existing trusted
PHP8.5.1 is tooling only. Uploader/toolkit remain untouched. No whole-plugin
release readiness, Database Review, UI/UX or native qualification is refreshed.

## Diagnostic and actual dependency evidence

The new development-only `scripts/manual/source-policy-minimum-import-diagnostic.php`
has an exact CLI entry; including it for tests is inert. It uses the existing
fixed native-CA/TLS downloader and publisher ZIP SHA256, complete metadata/
inventory/link/resource/CRC checks, then the same bounded PE decoder for only
`php.exe`, `php7.dll`, `ext/php_mysqli.dll`. It emits validated names and hashes,
not source bytes, raw errors or arbitrary paths. At most64 imports/80 name bytes
per fixed role,16MiB per selected image,8192 inventory rows. No extraction,
admission, process launch, search path, module load or core download in diagnosis.
Warnings are fixed safe codes; exact owner cleanup is in finally; an uncertain
constructor/cleanup is not labeled confirmed. Diagnostic source/tool hashes are
compared after cleanup, including on refusal.

```text
php -d memory_limit=768M scripts/manual/source-policy-minimum-import-diagnostic.php --confirmed-owned-selected-imports
```

One invocation passed with status imports-observed-not-qualified, elapsed1,672ms,
owned child `alynt-minimum-39b67323dddca7983585979fd4c498a4`, diagnostic image
`7fce6843507ca7c60752029dd042d955ba5a880fcb43f84835841f90ffe6d808`.
Pinned PHP ZIP26,132,214 bytes/SHA256
`14ae3250d4447c8ccfc4c45a70d90adfbcd61e728d85f0be56a7ddf8f9c8aace`;
93 entries/70,083,558 expanded bytes decoded in memory, extraction=false.
Cleanup confirmed; an independent exact-path check found the child absent before
the full proof. No diagnostic artifact is retained or loaded.

| Fixed role | Direct imports | Actual selected SHA256 |
| --- | --- | --- |
| `php.exe` | 15 | `4b06073dd8230421ba701a677dd8b1aa23ed55706663d3fdf996702b557f83b2` |
| `php7.dll` | 21, including the previously rejected `imagehlp.dll` | `51a5c862da428391c4864d862ca4fffbc608362cd66ada757b84d991a65bd035` |
| `ext/php_mysqli.dll` | 6 | `1c69a4336a2a42fef76c0d840543b8cd0a1aae4eb6f9111dfb5f2e6ed6a46891` |

The observed union contains `php7.dll` from the publisher-pinned archive and22
external names. The external set is `advapi32.dll`, `bcrypt.dll`, `crypt32.dll`,
`dnsapi.dll`, `imagehlp.dll`, `iphlpapi.dll`, `kernel32.dll`, `ole32.dll`,
`shell32.dll`, `vcruntime140.dll`, `ws2_32.dll`, plus the11
`api-ms-win-crt-{convert,environment,filesystem,heap,locale,math,runtime,stdio,string,time,utility}-l1-1-0.dll`
contracts. This is a direct-import observation, not transitive dependency closure.

Microsoft's [Image Help Library](https://learn.microsoft.com/en-us/windows/win32/debug/image-help-library)
documents ImageHlp as OS-included, and the
[MapFileAndCheckSumA requirements](https://learn.microsoft.com/en-us/windows/win32/api/imagehlp/nf-imagehlp-mapfileandchecksuma)
name `Imagehlp.dll`. Together with the actual pinned-package observation, this
supports classifying the **exact** lowercased name as an external Windows
dependency. It does not establish an installed DLL's presence, signature, file
identity, bitness, exported symbols, loader search result or runtime compatibility.
No ImageHlp copy is searched for, fetched, redistributed, installed or loaded.

## Small repair and non-vacuous review

`source-policy-minimum-static.php` now shares fixed selected-role parsing and
direct-import classification with the diagnostic/tests. Selected inventory is
bounded/type-checked and duplicate/case-ambiguous fixed roles are refused. The
full static entry still checks the complete pinned core tree and12 independent
source SHA256 pins **before** selected binary parsing/classification.

Only `imagehlp.dll` is added to the prior external-name list. No wildcard,
alternate DLL, arbitrary directory search or automatic dependency admission is
added. The pre-existing CRT-name rule and bundled-payload hash behavior are
unchanged. Unknown/lookalike/unreviewed API imports still throw
unexpected-import-refused. All external names retain
presence-and-transitive-closure-unverified; dependency_closure_qualified and
runtime_loading remain false, and every parsed PE retains load_authorized=false.
The new observation/classification helpers cannot mint an authenticated archive
descriptor or runtime permission from synthetic input.

The exact-name synthetic positive was red with unexpected-import-refused before
the one-name repair and green afterward. Actual parser tests cover correct role/
hash/keys, missing/case/duplicate/type/wrong-EXE-DLL/malformed-image refusals;
classification tests cover bundled versus external CRT DATA, unknown/lookalike
names, unreviewed API families/versions and core-first refusal. Three in-memory
semantic controls are assertion-killed: removing the known-name entry, removing
unknown-import refusal, and removing the core-first guard. The unchanged baseline
passes; repository source hashes are unchanged after each control. The mutation
contract explicitly distinguishes a thrown refusal from silently omitted output.
These are isolated synthetic/source tests, not candidate-runtime/Windows loader,
WordPress authentication, database or physical transaction proof.

Source-scoped review covers callers, fixed inputs, parse/classification order,
all error/finally paths, bounded observations, trusted-image freeze, import-policy
delta, no-loader/no-network include behavior, PHP7.4 syntax/compatibility and
packaging exclusion. Production/bootstrap/native workers, TLS/downloader,
archive/core-mode/tree/PE guards, all artifact/source pins, filesystem ownership/
extraction and native acceptance are unchanged. No new production caller.

## Fresh complete proof and verification

Fresh related suite before the sole full attempt:
`php vendor/bin/phpunit --do-not-cache-result --filter SourcePolicyMinimum`:
107 tests/2,027 assertions, no failures/skips. Changed-source PHPCS/
PHPCompatibilityWP, structure and whitespace checks passed.

One full invocation after the diagnostic cleanup and reviewed one-name repair:

```text
php -d memory_limit=768M scripts/manual/source-policy-minimum-acquire.php --confirmed-owned-minimum-artifacts
```

Status static-verified, exit0, elapsed170,016ms, cleanup confirmed. Owned child
`alynt-minimum-396b233f814d920badf7a619f338d899`; trusted image SHA256
`3db6e1c747aea2cd7c3dfcd6f60d0a16a5a3e63a25ac42d97072b7010e59c16e`.
Existing PHP8.5.1/libzip1.11.2/cURL8.17.0/OpenSSL3.5.4; per-request Windows native
CA and child memory768MiB. No retry, fallback or budget enlargement.

- Authenticated Git manifest977,559 bytes,3,437 files/418 directories; unchanged
  root `a3bd7a4074a4c95c8af3b969346726273d68e167`. JSON correlation SHA256
  `b18d30863e7dfad681ae3d1c09d2dfa9af78c0ee5d176249c2f140940ca7b9c3`.
- PHP ZIP identity/inventory matched diagnosis:26,132,214 compressed bytes,
  93 entries/70,083,558 expanded bytes; unchanged publisher SHA256 above.
- Core ZIP32,963,838 bytes,3,856 entries/74,132,675 expanded bytes; correlation
  SHA256 `f94b2d521ea9c38d965d1adf8ead55a1b45dc171795bd550e94cd2ce99c7a437`.
  Complete decoded byte/blob/mode/tree checks and12 source SHA256 pins passed.
- All three selected x64/PE32+ roles returned the diagnostic hashes/import sets
  above. `php7.dll` is the single bundled direct import with its recorded SHA256;
  all22 external imports are explicitly unverified for presence/transitive closure.
  Each load_authorized=false; dependency_closure_qualified/runtime_loading=false.
- Exclusive regular-file extraction and exact byte readback completed3,523 files/
  144,216,233 bytes, payload correlation SHA256
  `afa240adbebe758cfb7cdb46cfc9ec967f3fc717baa3e1b4c747e4cf4e9e5dda`.
  No archive permissions applied, overwrite, payload execution, runtime/module
  load, installation or source bootstrap. The earlier inventory's extraction=false
  describes its non-writing decoding stage, not the later completed owned writes.

Exact marker, manifest, both ZIPs, all extracted payloads and their owned parents
were removed by the existing tracked ownership-checked cleanup. Independent
exact-path checks found both diagnostic and full-proof children absent. No
artifact is retained/recoverable; only non-secret evidence remains. Independent
source/tool hash recomputation after cleanup matches the full-proof image above.
No diagnostic/proof replay is permitted for documentation or CI closeout.

## Publication, implications and next boundary

Executed post-proof repository QA on existing tooling PHP8.5.1:

- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,150 tests/
  11,235 assertions, no failures; only the two existing missing-Sodium invalid-key
  and signature-roundtrip skips. No new skip or weakened existing test.
- Fresh related minimum suite107/2,027, no failures/skips, including the unchanged
  baseline and three assertion-killed in-memory controls; source hashes preserved.
- `npm run lint`:240 files pass, including explicit changed-source PHPCS/
  PHPCompatibilityWP; admin23/23, zero failures/skips. Structure149 runtime/
  229 support/216 tests/213 class names and asset build pass; no tracked generated
  delta or package build.
- Strict Composer validation, Composer audit and npm moderate audit pass,
  zero known advisories/vulnerabilities; no dependency install/update.
- 134 owning local documentation links, scoped consumer/security/error/compatibility/
  test-integrity review, whitespace and protected-source checks pass. Production,
  native workers/controllers, all pins, TLS/archive/mode/tree/PE/ownership helpers,
  dependency manifests/locks, workflows and assets are byte-identical to5611847.
  Only the scoped static helper, new diagnostic/tests and owning records change.
  Uploader/toolkit remain clean and untouched; both exact temporary children absent.

Implementation publication `f6da13f88ab06c105493c8ff063b8e806570c934` is pushed;
ordinary [CI40909253119](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40909253119)
succeeded for that exact head. Actual PHP7.4 log:2,150 tests/11,237 assertions;
PHP8.3:2,150/11,242, each with no failures/skips. Admin23/23 each, zero failures/
skips; manifest/audits/build/structure/PHPCS steps passed, zero known advisories/
vulnerabilities. This evidence-only closeout receives its own pushed exact-head
ordinary CI and clean/remote verification at handoff; no acquisition or native
workflow is dispatched or replayed for publication.

These checks do not establish a loadable runtime, actual
PHP7.4/mysqlnd/NTS/reporting-mode behavior, Windows DLL resolution/transitive
closure, genuine core library bootstrap, SQL/native acceptance or release readiness.
All changes are development-only; rollback is the scoped Git change, not a site
or database operation. No source-pin/dependency/compatibility contract changes.

Implementation-plan progress: the separate acquisition/static/readback gate in
the minimum artifact/runner design is now complete; actual runtime and native
qualification remain separately gated. Recommended next gated slice:
**Repository-only Minimum-Profile Dependency Closure and Fixed Runtime-Preflight
Integration Design**. Specify finite external DLL/search-path/source admission,
the owned fixed legacy runtime/module launch and bounded version/driver/mode/
stdio/cleanup checks, without executing or installing anything. Do not claim
closure from names/hashes alone; no arbitrary DLL search or installation fallback.
Keep artifact acquisition, runtime loading, authentication/SQL/storage/native
experiments and production wiring disabled in that design-only slice. The later
actual runtime preflight requires its own explicit approval, and any database
proof additionally requires the separate Site Operations target/fixture gate.
