# Socket-Free Explicit Host-Trust and Observer-Admission Safety Foundation

Forward progress: the separately approved [owned-tool physical attempt](SOURCE_POLICY_TOOL_OBSERVER_OWNED_PROOF.md)
failed in the positive observer stage and cleaned both owned processes/fixtures.
It does not upgrade this synthetic foundation to physical feasibility; follow
the recorded diagnostic/fresh-attempt approval gate. Historical results remain.

## Authority, baseline and implementation-plan position — 2026-10-29

Selected under the autonomous safe-local batch, at clean/synced dashboard restore
baseline `0f5858eb765d4b951a8843b936b6a88b8966ad47`. This is one coherent R2
development-only implementation of the next item in the
[host-trust/observer design](SOURCE_POLICY_MINIMUM_HOST_TRUST_OBSERVER_DESIGN.md),
not separate task credit for tests, review, documentation or CI closeout.
The batch ceiling is eight worthwhile completed tasks, not a reason to invent
maintenance work after the next meaningful item crosses a physical proof gate.

Only new pure helper/tests and owning records change. No actual host-trust
designation or consent acquisition, installed-profile/source discovery,
artifact acquisition/loading, process/observer adapter, native experiment,
authentication, SQL, WordPress bootstrap, site operation, permission/credential/
schedule change, installation, package/release/tag/deploy or production wiring.
Existing trusted PHP children exercise supplied synthetic code/DATA only; they
are ordinary isolated QA, not the proposed Windows observer/process proof.
Site Operations was consulted; no site is selected/touched, so no target or
Novamira operation is appropriate. Uploader/toolkit remain read-only.

## One self-contained, non-authorizing DATA owner

`scripts/manual/source-policy-minimum-host-admission.php` implements one cohesive
final class, with no includes/dependencies and tests as its only consumers.
It does not alter the existing Launch DATA or ADBMP1 helpers. It returns no
executable path, Process/storage handle, launch command or genuine approval.

Constructor inputs bind synthetic run32hex, combined fixed worker/observer source
digest, expected/observed primitive profile, exact decision claims, two identity
claims and expected existing-tool module rows. Invalid construction throws only
fixed safe exceptions. Matching supplied expected/observed/decision data can be
forged by a caller: positive consistency is intentionally **not authorization**.

- The exact 18-field profile binds Windows root/canonical System32 relation,
  build/UBR, separate x64 claims, Windows identity/file-manifest digests, installed
  CRT/version/identity/digest claims and bundled-tool root/version/identity/image/
  closure claims. Cardinality/type/length checks precede key traversal/hashing.
  Portable path DATA rejects traversal, aliases, ADS, empty/reserved/trailing-dot
  components and a tool root inside Windows. This is not realpath, reparse,
  final-file identity or publisher verification. Fictional test build 10.0.12345
  is not this host's observed build or a new installed-profile pin.
- Decision DATA has one exact policy `existing-host-operator-trust-v1`, one scope
  `minimum-preflight-observer`, same run/profile/source and explicit
  `operator-trust-not-signature` basis. Claimed signature/offline verification
  must be false. Missing/extra/relabelled/stale-context fields refuse. No secret,
  reusable grant, persistent replay ledger or human-approval check is introduced.
  A fresh caller can recreate matching claims; this is not cross-process replay
  prevention or authenticated consent.
- Worker/observer object labels, integer PID, canonical bounded start tick,
  fixed tool-image digest and separate source digests are exact supplied claims.
  Distinct PID/object/source roles and combined source correlation are required.
  They are not retained OS Process objects and cannot prove ownership/PID reuse.
- Module DATA is independently dense/bounded/typed before canonical comparison:
  2–128 rows, 32 MiB/file, 256 MiB aggregate, exact role/path/name/hash/size fields,
  no duplicates or application-PHP roles. Require pwsh.exe in the tool role and
  kernel32.dll in the Windows role; any supplied CRT row must have the separate
  exact Windows/CRT correlation. Tool and System32 path roles cannot substitute
  for each other. Matching self-reported digests still do not establish a genuine
  complete module set, its publisher or a safe physical load path.

## Ordered channels, virtual deadlines and cleanup

The state sequence is deliberately separate from ADBMP1; there is no new wire
parser, socket, command, callback or actual process protocol:

1. Worker held claim.
2. Controller observer-started claim.
3. Controller complete expected/observed module DATA comparison.
4. Controller observer zero-exit, pipe-drain and owned-absence claims.
5. Controller release claim.
6. Worker released claim.
7. Controller worker zero-exit, pipe-drain and owned-absence claims.
8. Complete controller cleanup DATA: both owned identities absent, pipes drained,
   fixtures absent and unchanged state.

Separate methods model source/channel ownership but cannot authenticate callers.
Every event binds exact identity and order; early/reordered/replayed/extra events
or arbitrary cancellation/disposal/EOF/parent-exit/stderr labels refuse. Empty,
partial, changed or invalid module rows cannot satisfy expected evidence.

Virtual time is integer, nonnegative and monotonic. Work must remain below
20,000 ms, observer module/exit claims below 2,000 ms from observer start, and
all cleanup below the shared 25,000 ms ceiling. Cleanup reserve is not a larger
native budget or an OS termination guarantee. No clock, thread or stream runs.

First refusal is sticky with a fixed safe reason; later valid events cannot
reopen completion. Exact cleanup DATA may still reconcile after refusal within
the shared bound, exposing only `cleanup_consistent=true`, never successful
completion or physical absence. Completion requires all eight normal stages;
subsequent invalid input invalidates completion. Snapshot is a fixed primitive
summary, without raw input, module paths, source, errors or handles.
Execution, load authority, human approval verification, platform verification,
signature/offline verification and native qualification remain false everywhere.

## Non-vacuous tests and source-scoped review

The initial positive test was red because the helper did not exist. Final tests
require positive consistency and ordered completion, not only refusal. They
exercise every profile dimension, malformed mirrored profiles, decision domains,
run/source/image/object/PID/start correlation, every stage's missing/reordered
event, identity-before-payload guard ordering, strict deadlines/types, cancel/
EOF/exit/drain distinctions and complete bounded cleanup after success/refusal.

Matching forged module digests are tested independently of structural/role/size/
count/aggregate guards, with positive exact-ceiling cases. This avoids vacuous
tests that pass solely because a changed digest rejects the input. Test review
corrected an initial expectation that reconciled cleanup after refusal must
also return false: the design permits cleanup consistency while completion
stays refused. No authority, completion or absence guard was relaxed.

Thirteen in-memory semantic controls must be assertion-killed: profile equality,
decision equality, image/source binding, module path/equality, observer identity,
global/observer deadlines, premature release/completion, sticky refusal and final
absence. Each has exactly one source replacement; invalid mutation setup is a
test failure, not a killed control. One formatter-sensitive cleanup locator was
corrected before the final controls passed. Source SHA256 remains unchanged after
each child. Existing trusted PHP runs with `-n`, JSON disabled and no mysqli/
PDO-MySQL, never candidate artifacts or native database sessions.

The token-level closed-call guard forbids IO/launcher/discovery calls, includes,
globals/request/environment data, shell backticks, output, dynamic functions/
classes/receivers and arbitrary constructors. Only self methods, fixed pure
primitives and safe InvalidArgumentException construction are admitted. This
protects current source composition; it is not a formal PHP or OS sandbox.
Source-scoped review covers bounded types/resources, exact roles and digest
correlation versus trust, error privacy, channels/identity/order, deadlines,
cleanup/refusal, compatibility, actual callers and test integrity. No UI/UX,
physical authentication, Database Review or whole-plugin readiness is claimed.

## Validation, publication and batch boundary

Fresh final repository QA on existing trusted PHP 8.5.1:

- Related family: `php vendor/bin/phpunit --do-not-cache-result --filter
  SourcePolicyMinimum`: 273 tests / 11,496 assertions, no failures/skips. This
  includes the new 116 tests and thirteen killed controls; no native proof ran.
- Full `php vendor/bin/phpunit --verbose --do-not-cache-result`: 2,316 tests /
  20,704 assertions, no failures; two existing Sodium-unavailable signing tests
  skipped. This is not an all-tests Windows pass or physical legacy-runtime proof.
  Final targeted/full QA was repeated after adding pre-traversal cardinality
  guards; earlier-source results are not substituted for final-source evidence.
- PHPCS 243 files passed, with the final changed helper rechecked separately;
  PHPCompatibilityWP remains configured for PHP 7.4. Admin 23/23, no failures/
  skips; structure 149 runtime / 233 support / 222 test files / 219 class names
  and asset build passed. No generated tracked delta.
- Strict Composer validation, Composer audit and npm moderate audit passed,
  zero known advisories/vulnerabilities; no dependency install/update.
- 139 owning local documentation targets, whitespace and final source/caller/
  security/compatibility/resource/error/test review passed. Existing tracked
  non-documentation files remain identical to the restore baseline; only the new
  development helper/tests are added. Uploader/toolkit remain clean/read-only at
  3dfcb15/c067eb6. No assertion weakened, new skip or whole-plugin review refresh.

Implementation publication `0fbb67abad5828cc87aac0ba7b495bc2147bb301` is pushed;
[CI 40940936318](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40940936318)
succeeded for that exact head. Actual PHP 7.4 log: 2,316 tests / 20,706 assertions;
PHP 8.3: 2,316 / 20,711, both without failures/skips. Admin 23/23 each, zero
failures/skips; manifest/audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. Setup Node emitted its tool-level `punycode`/`url.parse()`
deprecation warnings; no test/acceptance change suppresses them. This evidence-only
closeout receives its own pushed exact-head ordinary CI and clean/remote check
at handoff. No manual/native workflow or physical proof is dispatched/replayed.
The unchanged release configuration excludes scripts/tests/docs; this is static
configuration review, not a package audit or permission to create a release.

Version
0.1.77/schema 8, runtime/bootstrap code, native workers/controllers, ADBM0/ADBMP1/
ADBC1/2 acceptance, existing source/artifact pins, dependency manifests/locks,
workflows and production wiring remain unchanged. No release is warranted for
this development-only foundation.

Implementation-plan progress: the pure trust/observer consistency foundation is
implemented; actual trust, physical process/observer feasibility and candidate
runtime/native qualification remain unproved. **1/8 worthwhile safe-local tasks
completed** through validation, commit/push and exact-head CI; tests, source review
and evidence closeout are not separately counted. The current implementation-plan
safe-local lane is exhausted at the next separately gated physical proof. No
filler maintenance, release work or deferred feature is opened to reach eight.

Recommended next gated slice: **Local-only Existing-Tool Bounded Observer/
Termination and Owned Cleanup Proof**, using only the existing bundled x64
PowerShell image, frozen minimal sources and exact owned filesystem/stdio/
process fixtures and cleanup. Require positive held observation and bounded
failure/termination/drain/owned-absence evidence. Keep candidate PHP/DLL loading,
downloads/installation, site/WordPress/database operations, actual host-trust
designation, native qualification and production wiring disabled. This batch
does not authorize those new physical fixture/process experiments.
