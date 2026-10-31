# Unicode Archive-Metadata Path Policy and Critical-Link Containment Design

## Authority, restore point and decision — 2026-10-31

Approved repository-only design at clean/synced baseline
`febe6db16386a40bab25d71afd589b6269f49c17`. Documentation, ordinary trusted
repository validation, commit/push and exact-head CI only. No parser/test-source
implementation, artifact acquisition, extraction, candidate loading/execution,
Docker operation, WordPress/site/authentication/SQL or operational storage-fixture
operation, permission change, package/release/deploy or production-wiring change.
Existing isolated offline test temporary files/cleanup are ordinary QA, not a
new operational fixture proof. No site target is
selected; LocalWP confirmation/Novamira operations do not apply. Uploader and
toolkit stay read-only; no toolkit or whole-plugin readiness rows are refreshed.

**Select a separate tar-only, bounded literal Unicode virtual-name policy.
Keep the existing ASCII `name()` contract for core/Git/ZIP consumers. Never
normalize, case-fold, resolve, host-join or follow an archive name/link.** This
design did not implement that selection or authorize another acquisition.
The separately approved [synthetic foundation](#approved-synthetic-foundation--2026-10-31)
below implements it without renewing physical acquisition authority.

The [owning artifact proof](SOURCE_POLICY_LINUX_MINIMUM_ARTIFACT_PROOF.md#approved-bounded-refusal-diagnostics--2026-10-31)
records the consumed attempt, specific ASCII refusal and confirmed cleanup.
The actual symlink name/target was not retained. Ordinal 337 is U+0151, category
`Ll` in the existing analysis tool's Unicode database; its 58-character name
does not establish the full name's future eligibility, target or role relevance.
Historical partial byte evidence is not complete artifact/native qualification.

## Source map and findings

Baseline source: [static reader](../scripts/manual/source_policy_linux_minimum_static.py),
[acquisition owner](../scripts/manual/source-policy-linux-minimum-acquire.py),
[offline tests](../tests/support/source_policy_linux_minimum_static_test.py),
[PHP test entry](../tests/SourcePolicyLinuxMinimumArtifactTest.php).

| Consumer | Current responsibility | Selected boundary |
| --- | --- | --- |
| `scan_layer()` | Effective tar names, per-layer duplicate keys, critical ancestors/roles and whiteouts | Future private tar-only validator; all existing type, link, read/hash and resource guards remain |
| `name()`, `tree_oid()`, `core_modes()`, `core_zip()` | ASCII core names and ASCII Git tree sorting/encoding; complete pinned tree/ZIP/pins | Byte-unchanged ASCII contract; no Unicode propagation into core or Git hashing |
| Acquisition `CATALOG` / `Owned` / report | Fixed ASCII roles; digest-named flat owned files; bounded facts | No role expansion, archive-to-host mapping, new effect authority or raw-name logging |

Finding 1: replacing the shared ASCII guard would also admit names that later
reach explicit ASCII encoding in `tree_oid()`. Separate the consumers instead
of changing the core tree algorithm or pins to accommodate a tar-layer name.

Finding 2: current `tarfile.open(..., mode='r|')` relies on decoding defaults.
The existing bundled analysis Python reports 3.12.14, tar encoding `utf-8`,
Unicode database `15.0.0`. Future tar-only integration must state UTF-8 and
`surrogateescape` explicitly, then refuse surrogate-containing effective names;
never replace undecodable bytes or guess a different locale. Python documents
effective string names, encoding/error controls and PAX UTF-8 metadata.
[Python tarfile documentation](https://docs.python.org/3.12/library/tarfile.html#unicode-issues)
supports that decoder distinction, not a claim of original-header round-trip
identity. Installed `_proc_gnulong()` and `_proc_pax()` were inspected read-only.

Finding 3: the current whiteout reducer mutates the selected-role map in stream
order. A marker after a same-layer role can conservatively remove that role;
this is not general OCI changeset application. OCI whiteouts target lower
layers and opaque markers are order-independent for same-layer additions.
[OCI layer specification](https://github.com/opencontainers/image-spec/blob/main/layer.md#whiteouts)
also prohibits duplicate path entries. Do not silently turn this Unicode slice
into an overlay engine or claim OCI/rootfs reconstruction. Preserve conservative
refusal/claim limits; physical acquisition remains blocked regardless of CI.

## Selected tar-only contract (design baseline)

1. Consume only the decoded effective `TarInfo.name` string. Preserve existing
   pre-validation handling: strip exactly one leading `./`; trim trailing `/`
   only on directory entries; ignore only the existing empty/`.` root-directory
   case. No other replacement, whitespace stripping, percent decoding or path
   resolution. GNU/PAX and ordinary headers must converge on the same validator.
2. Require a non-empty string of at most 240 code points **and** at most 240
   strict UTF-8 bytes after that handling. Bound character count before encoding
   (at most 960 transient encoded bytes); refuse encoding errors. Keep 32 literal
   `/`-separated segments, no empty/`.`/`..` segment, no initial `/` and no ASCII
   backslash. This does not increase the old ASCII byte ceiling.
3. Require source-owned Unicode database version `15.0.0` before tar-name
   admission, using the already trusted stdlib; no caller override. Mismatch
   is a fixed refusal, never a download/skip/fallback.
   Refuse categories `Cc`, `Cf`, `Cs`, `Co`, `Cn`, `Zl`, `Zp`. This rejects
   controls, format/bidi controls, surrogates, private-use, unassigned/noncharacter
   code points and line/paragraph separators. It is a conservative policy, not
   support for every valid POSIX byte name or every invisible Unicode character.
   Retain other assigned categories literally, including combining marks and
   space separators. No claim that category admission makes a name display-safe.
4. Do not apply NFC/NFD/NFKC/NFKD, transliteration, case folding, platform
   separators, `normpath()` or `resolve()`. The key is the effective code-point
   sequence after step 1, not its visual appearance or original header bytes.
   NFC/NFD and case variants remain distinct; slash/dot lookalikes are ordinary
   non-ASCII characters, never structural delimiters. Duplicate effective keys
   within one layer refuse, including distinct header forms decoding to the
   same key. No lossy identity/hash or truncated key can select a role.
5. Keep all wanted roles fixed ASCII literals. All prefix tests retain explicit
   `key + '/'` boundaries; a lookalike is not `usr`, `php` or `mysqli.so`.
   Non-directory critical ancestors refuse before any selected content read;
   symlinks/hardlinks at a role/ancestor refuse independently of target text and
   entry order. Unrelated links are metadata only: never call `extractfile()`
   for them or dereference their targets. Only an actual regular wanted member
   can reach bounded in-memory ELF inspection, never a same-looking name.
6. Keep exact ASCII `.wh.` / `.wh..wh..opq` recognition and zero-sized regular
   marker requirement. Apply the same literal validation to a derived ordinary
   deletion target, rejecting empty/`.`/`..` basenames; the opaque root target
   is the one explicit empty-target exception. No Unicode whiteout lookalike
   may revoke a role. Preserve the current conservative reducer, not same-layer
   restoration or a new overlay model. A marker after a current-layer role
   remains conservative loss of that evidence; do not reorder markers to force
   success. A later genuine regular replacement may establish new role evidence,
   but none of these cases proves general OCI/rootfs conformance.
7. Existing tar size/type/sparse/PAX/read/deadline/entry/diff-ID checks and final
   exact wanted-role set remain mandatory. Unknown special types still refuse.
   Name validation occurs after existing parser resource guards, never in place
   of them; internal extension-header parsing is not made allocation-free by
   the new string guard. No parser, layer, member, staged-file or time cap rises.
8. Retain bounded diagnostic facts, no raw names/targets, stack/stream dumps or
   archive-derived filesystem paths; preserve exact owned-root cleanup reporting.
   Add fixed predicate codes only as needed for UTF-8 byte,
   category and Unicode-version refusal; current character/ordinal/prefix-hash
   facts must remain truthfully labeled. Record Unicode database version with
   trusted analysis-tool facts for a future separately approved attempt.

Python's [unicodedata documentation](https://docs.python.org/3.12/library/unicodedata.html)
identifies its 15.0.0 database, categories and normalization API. The versioned
[Unicode 15.0 property definitions](https://www.unicode.org/reports/tr44/tr44-30.html#General_Category_Values)
anchor the proposed category policy. These are design inputs, not category-
predicate implementation or new candidate-runtime/dependency requirements.

## Required non-vacuous implementation evidence

The next implementation may use synthetic bytes/`BytesIO`, virtual clocks and
isolated API tests only; no downloaded/candidate archives, network or
site/database operations.
Required cases, not evidence already obtained by this design:

- Positive ASCII and U+0151 tar names, an unrelated Unicode symlink plus a real
  regular wanted member, full synthetic diff-ID and exact role-set success.
  An always-refusing validator must fail. The historical actual name is unknown;
  use independently constructed examples, never fabricate a successful rerun.
- UTF-8 exactly 240 bytes and 241 bytes with fewer than 240 code points;
  character/depth boundaries; invalid UTF-8/surrogate escapes, control/format/
  private/unassigned/separator categories and Unicode-version mismatch. Valid
  printable ASCII remains compatible; unsafe ASCII still refuses.
- Ordinary/GNU/PAX name routes; PAX overrides and binary/invalid name decoding;
  one-leading-`./` alias duplicates, directory trailing-slash duplicates and
  literal NFC/NFD/case/lookalike distinctions without normalization. Malformed
  metadata and oversized/deadline cases must retain existing refusal guards.
- Exact/ancestor symlink and hardlink cases before/after a selected regular
  file, non-directory ancestors and Unicode lookalikes that cannot satisfy or
  revoke ASCII roles. Spy on content-read/ELF boundaries: forbidden reads zero.
  Unrelated links with absolute/traversal target text remain inert metadata,
  not an attestation that their targets are safe to extract.
- Exact/opaque/Unicode whiteouts, malformed derived targets, prefix collisions
  such as `php-extra`, root opacity and conservative same-layer ordering cases.
  No discarded role can be silently reported present or claimed as full rootfs.
- Preserve ASCII `name()` refusal, Git sorting/OIDs, complete-core ZIP and all
  11 existing pins; no Unicode relaxation may leak to those consumers. Existing
  PAX and regular-file positives must continue to run without skipping guards.
- In-memory semantic controls must kill Unicode rejection/acceptance, byte
  bound, duplicate, traversal, critical-read/link, whiteout and consumer-split
  regressions. Where redundant guards also refuse, check the required guard
  order/zero-read trace instead of claiming a generic refusal proves each guard.
  Restore no source files through destructive Git commands; no native controls.

## Validation, publication and exact resume boundary

Design review checked shared consumers, decode provenance, Unicode identity,
whiteout/critical-link containment, resource limits and non-vacuous proof needs.
Only this design, the implementation-plan resume record and the owning artifact
proof's current recommendation change. Historical refusal/cleanup/CI evidence
remains historical. No source, test, runtime, ELF-string policy, core/artifact
pin, dependency or CI-workflow change. This is a docs-only review; the proposed
acceptance change remains separately approval-required.

Executed locally with existing trusted PHP 8.5.1 / analysis Python 3.12.14:
`php vendor/bin/phpunit --do-not-cache-result --filter
'SourcePolicyLinuxMinimumArtifactTest|SourcePolicyLinuxMinimumTupleTest|SourcePolicyLinuxMinimumIsolationTest'`
passed 114 tests / 1,933 assertions, no failures/skips, including the existing
14 offline Python groups (network blocked). These check unchanged contracts,
not implementation of this proposal. `npm run check:structure` passed (149
runtime / 235 PHP support / 227 test files / 224 classes); strict Composer
manifest validation and Composer/npm moderate audits passed, zero known
advisories/vulnerabilities. Whitespace and all 160 owning local documentation
targets passed. Pre-publication remote master matched the baseline;
uploader/toolkit remain clean at `3dfcb15` / `c067eb6`.

Design `0a6ae7e5a9fd3830d7302fe43b93131ebcd46bb6` is pushed;
[exact-head ordinary CI 41189182921](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41189182921)
succeeded. Actual PHP 7.4: 2,440 tests / 22,951 assertions; PHP 8.3: 2,440 /
22,956, no failures/skips. Admin 23/23 per matrix, no failures/skips; build,
structure, PHPCS, manifest and dependency audits passed, zero known dependency
advisories/vulnerabilities. Existing Setup Node `punycode` / `url.parse()`
deprecation warnings remain visible tool-level warnings. The docs-only evidence
closeout is pushed and independently exact-head CI/remote/clean checked at
handoff. No physical proof or whole-plugin readiness is renewed; ordinary CI
does not implement/test the new policy or supersede the artifact refusal.

Implementation-plan progress: a finite tar-only policy and implementation proof
contract are selected; actual parser behaviour and artifact admission remain
unchanged/refused. No feature/native completion or autonomous task inflation.

Design-time next gated slice (subsequently approved below): **Local-Only Socket-Free Tar Unicode Virtual-Name
and Critical-Link Containment Safety Foundation**, synthetic/isolated tests only,
implementing this consumer split and bounded policy. It requires approval before
changing archive acceptance. No fresh payload attempt, extraction, candidate/native
execution or wiring follows that approval; any physical retry is separately gated.

## Approved synthetic foundation — 2026-10-31

Clean/synced restore baseline `8a45476cdf85b3b8447a4090645ebf23fde9711d`.
User approves the tar-only policy/containment foundation through local validation,
commit/push and exact-head CI using isolated/synthetic tests only. The change
is confined to development analysis helpers/tests and owning records. No
downloaded/candidate archives, acquisition attempt, host extraction, image load,
candidate/native execution, site/database fixture, installation, release/package
or production-wiring operation. No site target/Novamira operation applies.

Implemented the selected separate `tar_name()` guard: owned Unicode 15.0.0,
strict UTF-8, at most 240 code points and 240 bytes, category and literal
structure limits. Explicit tar decoding uses UTF-8/surrogateescape; escaped
invalid bytes refuse rather than being replaced. Core `name()`, Git/ZIP/tree
functions, ELF string policy, fixed roles and all 11 core pins remain unchanged.
Only derived ordinary whiteout target validation is added to the existing
conservative reducer; no reordering or general overlay semantics. Future
acquisition reports Unicode database version, but no acquisition was run here.

The new [Unicode tests](../tests/support/source_policy_tar_unicode_test.py)
join the existing blocked-network offline runner. They establish real synthetic
regular-member reads/diff-ID/role positives alongside inert Unicode links,
USTAR/GNU long-name/PAX and invalid-binary name cases, literal identity/duplicate
checks, byte/category/version boundaries, critical guard ordering and zero
forbidden content reads, whiteouts/resource limits and ASCII consumer isolation.
Historical U+0151 ASCII refusal facts are still tested against `name()`; tar's
rejection analogue now uses a prohibited format control. That intentional
policy change is paired with positive Unicode tar evidence, not a weakened
always-refusing test or an invented successful real archive scan.

All 23 in-memory source-image controls are assertion-killed; none counts an
import/harness error as a successful kill. They cover accepting/refusing all,
character/byte/category/version/structure guards, duplicates, critical-read/link
guards, whiteouts, consumer separation, unwanted normalization, selected reads,
diff IDs and decoder settings. Redundant guards use exact failure/order traces;
these are not 23 independent exploit or native proofs. The actual source file
SHA256 is checked unchanged after controls; no source mutation is written to
disk. Current helper SHA256:
`392d90edc29dc3044d3ab0f7d0a3c50412bdfacab737ae4fa3dab3dc19ea1eb5`.

Red regressions established missing tar-only/Unicode acceptance before the
implementation. Their larger report exposed a Windows anonymous-pipe stall:
only verified owned Python child 61408 of test PHP 81672 was stopped, now absent.
Bounded ASCII failure output (3,000 bytes plus newline) fixes that test-runner
reporting issue without dropping tests, skips, counts or guard assertions.
One ambiguous control selector was made unique; no runtime acceptance was
changed to obtain green controls. Final narrow run: 2 PHP tests / 12 assertions,
23 offline Python groups / 23 assertion-killed controls, zero failures/skips/
network calls. Existing isolated test-owned temporary files use exact cleanup;
no operational fixture/process or candidate data is retained.

Final trusted local QA: related DATA/artifact 114 tests / 1,933 assertions,
no failures/skips; full PHP 8.5.1 suite 2,440 / 22,949, no failures and two
existing Sodium-unavailable signing skips. PHPCS 244 files; admin 23/23, no
failures/skips; structure 149 runtime / 235 PHP support / 227 test files /
224 class names; asset build passed without a tracked generated delta. Strict
Composer validation, Composer audit and npm moderate audit passed, zero known
dependency advisories/vulnerabilities. Whitespace and 163 owning local document
targets passed. Final scoped source/security/error/resource/compatibility/
test-integrity review confirmed the original ASCII functions, pins, runtime,
tuple contracts, workflows and dependencies unchanged; no further finding was
hidden by weakening guards. This is not full rootfs/OCI or physical qualification.
Uploader/toolkit stay clean/read-only at their original heads; no historical
physical evidence or whole-plugin review/readiness row is refreshed.
Foundation `965b3ca0f810f42431bb9d30d56b946a6727412d` is pushed;
[exact-head ordinary CI 41190019321](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41190019321)
passed. Actual PHP 7.4: 2,440 tests / 22,951 assertions; PHP 8.3: 2,440 /
22,956, no failures/skips. The strict offline report verifies 23 groups and
23 assertion-killed controls on each matrix, not candidate execution. Admin
23/23 per matrix with zero failures/skips; build, structure, PHPCS, manifest
and audits passed, zero known dependency advisories/vulnerabilities. Existing
Setup Node `punycode` / `url.parse()` warnings remain visible tool-level
deprecations. This docs-only closeout receives its own pushed exact-head CI
and clean/remote verification at handoff. Ordinary CI cannot clear the physical
gate; no payload attempt follows this publication.

Implementation-plan progress: the selected tar-only policy now has synthetic
implementation/guard evidence. The last actual artifact attempt is still refused;
unknown name/target, later layers, full diff IDs, core bytes and ELF/native
qualification are not inferred from these tests.

Foundation-time next gated slice (subsequently approved): **Linux Minimum-Lab Unicode-Policy Static
Acquisition and Owned Cleanup Proof**. Approve one fresh bounded digest-pinned
static attempt with fresh offline prerequisites, the same pins and all original
payload/parser/time/cleanup ceilings. No normalization, larger limits, fallback,
second attempt, extraction or candidate/native execution; production wiring
remains disabled. Current synthetic approval does not authorize that retrieval.

Follow-up: the separately approved [one static acquisition attempt](SOURCE_POLICY_LINUX_MINIMUM_ARTIFACT_PROOF.md#approved-unicode-policy-static-attempt--2026-10-31)
passed all 25 layer diff IDs, complete pinned core bytes and selected ELF checks,
then removed all 30 owned files/root with independent absence confirmation.
This actual later evidence clears the static refusal, not dependency closure or
runtime/native qualification. The owning proof now carries the current gated
dependency-closure/preflight design recommendation; no source changed here.
