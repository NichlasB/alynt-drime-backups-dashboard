# Minimum-Profile Artifact Admission and Fixed Runner Safety Foundation

## Authority and restore baseline — 2026-10-29

Approved implementation of the next synthetic-only gate in the
[artifact/runner design](SOURCE_POLICY_MINIMUM_ARTIFACT_RUNNER_DESIGN.md).
Clean/synced restore baseline `9ae4bcd800733978e635ac258d7d2084d4f71b46`.
R2: bounded development-only pure code, no sensitive state or public contract.
No downloads, artifact extraction, candidate-runtime loading, LocalWP/site
inspection, authentication, SQL, storage fixtures or native proof. The only child
processes used by tests run existing PHP_BINARY with `-n` on synthetic DATA.
No accounts/permissions, credentials, schedules, dependencies, source manifests,
runtime/legacy writer, schema8, version0.1.77, production/form wiring, ADBC1/ADBC2,
release/package/tag or deployment changes. Uploader/toolkit are not edited.

## Implemented boundary and consumers

All new code resides in development-only `scripts/manual/`, with **tests as its
only callers**. Nothing is added to production bootstrap or existing native
workers/controllers. Existing qualified image inputs remain unchanged.

| Source | Responsibility | Explicit proof limit |
| --- | --- | --- |
| `source-policy-minimum-artifacts.php` | Fixed publisher/source identity DATA, bounded in-memory digest checks, hypothetical owned layout and complete typed inventory checks. | Not a downloader, ZIP decoder, path owner, filesystem probe or extraction permit. |
| `source-policy-minimum-tree.php` | Complete bounded Git blob/tree encoding, Git directory ordering/modes, fixed minimum tree and independent11 source pins plus schema reference. | No downloaded tree exists here; genuine fixed-tree positive is not claimed from tiny synthetic trees. |
| `source-policy-minimum-pe.php` | Bounded PE32+ x64 header, backed-section/import-name and executable/module-role decoding. | A deliberately narrow structural decoder, not loader equivalence, signature/NTS/dependency trust or real-binary compatibility. |
| `source-policy-minimum-runner.php` | Exact supplied minimum-profile DATA and sticky ordered `ADBM0` synthetic transcript. | No worker, launch command, external observation, authentication, native handle or authority token. |

Catalog values match the approved PHP7.4.33 NTS x64 ZIP checksum/core6.0
commit/tree. Layout derives a bounded `alynt-minimum-<run>` child from supplied
temporary-parent DATA, but always reports `filesystem_verified:false`. Inventory
allows only exact path/kind/size/compressed keys, php/core kinds, bounded integer
sizes/counts/root lengths and the exact core archive wrapper. It refuses unsafe
Windows/UNC/drive/traversal/ADS/control/Unicode/reserved/trailing-dot-space names,
case collisions (including implicit parents), duplicate paths, files used as
directories, link/reparse/unknown kinds and declared byte/count excesses.
No real filesystem facts can be established by caller-supplied metadata.

Tree input is bounded to8192 primitive files/128MiB/depth16; only100644/100755
modes are accepted. Duplicate/case-colliding/file-directory paths, links/gitlinks,
unknown modes and extra keys are refused. Recursive encoding is private to the
validated bounded tree; no public unbounded recursive entry point. Synthetic
known blob framing/empty tree/nested ordering/mode/content changes are exercised.
The fixed core checker independently checks the existing11 source SHA256s and
non-executed schema.php reference after complete root-tree comparison. No genuine
core image is fabricated to obtain a positive; complete real-tree admission still
requires the later owned artifact acquisition proof.

PE decoding accepts only three fixed role names, at most16MiB input/16 backed
nonoverlapping sections/64 imports/80-character DLL names. Bounds precede reads;
unknown machine/format, wrong EXE/DLL role, malformed/truncated RVA spans,
unsupported delay imports, unterminated/unsafe names and incomplete import tables
are refused. It does not parse every PE feature, resolve imports, certify arbitrary
sections, prove VC runtime presence or decide a real image is loadable. The fixed
future artifact adapter must separately qualify its ZIP/raw metadata handling,
complete required file set, static import/dependency policy, reparse/ownership and
immutable byte provenance. Parser output always has `load_authorized:false`.

Runner DATA requires exact Windows/PHP7.4.33/mysqlnd7.4.33/x64/NTS/report-OFF0/
core6.0/internal-mysqli/autoload-no fields with no casts/extra keys. The transcript
binds run/image, phase, exact LF framing and monotonic virtual milliseconds within
the separate10-minute provisioning ceiling. Wrong order/correlation/claim/type,
replay, time reversal/expiry or oversize frame permanently latches failure; raw
input is neither retained nor logged. The valid five-stage sequence can finish
`synthetic_complete:true`, while `execution:false` and `native_qualified:false`
remain unconditional. The transcript only checks claimed DATA; it cannot vouch
for preceding artifact checks, a real clock, held/live/post release or a process.
Original initializer/body/command/native process budgets are untouched.

## Non-vacuous tests and source-scoped review

Five new test classes and two small support fixtures exercise actual helpers.
The initial artifact test was red because the implementation was absent; the
completed suite includes valid positive DATA paths and paired hostile inputs.
Tests do not merely expect unavailable, inject a native success, switch report
mode, bypass a guard or obtain a write-capable handle.

Five **in-memory** guard-removal controls are assertion-killed by the same
positive/refusal contract: implicit-parent case collision, digest comparison,
PE machine, transcript correlation/framing and exact reporting mode. Baseline
uses real fixed sibling includes in existing PHP `-n`; mutants evaluate the same
source with one exact checked edit and fixed include-order substitution, never
rewrite repository files. Child contract verifies no mysqli/PDO-MySQL/JSON;
source SHA256s are compared before/after each control. This is isolated semantic
evidence, not real PHP7.4 Windows/environment or native mutation proof.

A token-level pure-call guard covers the four helper sources and rejects IO/
loader/launcher/eval/output call surfaces; fixed sibling DATA includes and private
bounded tree recursion are the only composition. No helper is wired to production
bootstrap. The final diff review also checks actual consumers, source-image
preservation, packaging exclusion, PHP7.4 compatibility, resource bounds and
non-authorizing result flags. No UI/UX, whole-plugin pre-release/Database Review,
physical transaction or genuine authentication evidence is claimed or refreshed.

## Validation and publication

Executed local validation on existing PHP8.5.1:

- `php vendor/bin/phpunit --do-not-cache-result --filter SourcePolicyMinimum`:
  60 tests/1,740 assertions, no failures/skips; five controls assertion-killed,
  baseline passed, source bytes unchanged. Baseline uses actual fixed includes.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`:2,103 tests/
  10,948 assertions, no failures; only the two existing missing-Sodium invalid-key/
  signature-roundtrip skips. No added skip or weakened existing assertion.
- `npm run lint`:233 files pass, including all four new helpers; explicit new
  helper PHPCS/PHPCompatibilityWP check also passes. Admin23/23/no skips.
- Structure149 runtime/229 support/212 tests/209 class names, build, Composer
  strict validation/audit and npm moderate audit pass; zero known advisories/
  vulnerabilities and no dependency install/update or generated tracked delta.
- Owning links, complete scoped diff, pure source-call and consumer review pass.
  Existing runtime/bootstrap/controllers/manifests/workflows/dependencies are
  byte-identical to9ae4bcd; uploader/toolkit are clean and untouched. Existing
  release workflow already excludes scripts/tests/docs; no package was built.

Publication `d3328d84310c514bc95462e99b34dec13082b00e` is pushed;
ordinary [CI40887183349](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40887183349)
succeeded for that exact head. Actual PHP7.4 log:2,103 tests/10,950 assertions;
PHP8.3:2,103/10,955, each without failures/skips. Admin23/23 on each job, no
failures/skips; manifest/audits/build/structure/PHPCS passed with zero known
advisories/vulnerabilities. Existing Node setup/cleanup DEP0040/DEP0169 warnings
were nonfatal, not dependency changes. The evidence-only closeout receives its
own pushed exact-head ordinary CI and clean/remote verification at handoff.
No native workflow is dispatched; ordinary repository CI does not admit the
proposed Windows executable or minimum-profile native path. No site, artifact
or native operation is repeated for evidence publication.

Implementation-plan progress: synthetic artifact/tree/PE/runner contracts are
implemented; actual acquisition, filesystem ownership/extraction, dependency
trust/runtime preflight and native qualification are still unproved and gated.
Recommended next gated slice: **Owned Minimum-Profile Artifact Acquisition,
Static Verification and Cleanup Proof**, including the fixed ZIP/filesystem
adapter implementation and synthetic regressions before one bounded acquisition.
Approve only exact portable artifacts/owned temporary files and cleanup; no
installation, candidate-runtime loading, site/SQL operations or production wiring.
Runtime/environment loading and any owned database proof remain later approvals.
