# Candidate-Bound libgcc_s Provider-Alias Refusal Diagnostic Design

## Approved scope and evidence — 2026-11-02

User approves repository-only diagnostic design through validation, commit, push
and exact-head ordinary CI. R1 documents only; no implementation, admission/regex,
budget, profile/workflow, acquisition, extraction/loading/execution, SQL, installed
site, permission/credential, schedule, production wiring, package/release/tag or
deployment change. No manual dispatch or new risk-tier authority is included.
Uploader/toolkit remain untouched; no installed-site/Novamira task applies.

Restore/review baseline **fd69e839b1959b76c52e5bc76f72b5ceb28ea69c** is clean/synced;
[ordinary CI41511365215](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41511365215)
is reverified successful. The [actual static proof41510494382](SOURCE_POLICY_UBI_FIXED_ENTRY_FOUNDATION.md#actual-bounded-access-static-proof-and-cleanup-result)
at a718841 remains a failed qualification: all ten layers/model commits, then
`elf-provider-alias` for `libgcc_s.so.1`. Its thirteen-file/directory primary cleanup,
zero-file independent reconciliation and zero Actions artifacts are established.
The old layer6 string-table blocker was passed in that selected profile, not the
complete provider graph. No candidate bytes remain for another inspection.

The packet records requested name/current-object digest/root summaries but not
the alias target or failing predicate. The actual target remains **unknown**.
Do not infer a distribution naming scheme, missing library, malformed candidate,
unused dependency or permission to change policy. Campaign stays **7/10 product
milestones, 7/10 native attempts, seven static acquisitions**; no design credit.

## Actual boundary and selected capture point

The [provider](../scripts/manual/source_policy_ubi_elf_static.py) first validates
the requested library name, constructs `usr/lib64/<name>`, and inspects that node
in the finalized model. Only a symlink enters the alias conjunction:

1. target satisfies the existing `library` validator (including128-character cap);
2. target starts with `name.split('.so',1)[0]+'.so.'`;
3. target is not exactly the requested name;
4. `target[len(stem):]` matches the existing numeric/dotted-numeric suffix regex.

Only after that gate does the code inspect the target. It then retains the
regular-file/mode/combined-link limits and graph snapshot/origin/SONAME/search/
feature/cycle checks. All these requirements remain unchanged.

Select a bounded **post-refusal** capture beside this exact guard, using only the
already inspected immutable row, requested name and target. Do not move the guard,
precompute a different acceptance decision, normalize/strip/case-fold the target,
follow the refused target, run another inspection/resolve, parse another body or
perform host IO. The original conjunction and finite `elf-provider-alias` cause
must still decide admission. Invalid requested names and earlier model refusals
must not cause a protected node read merely to make a diagnostic.

The existing graph `elf_facts.path_sha256` identifies the requesting object, not
the provider alias. Keep those domains distinct. The new provider-path digest
must be computed from the actual constructed provider path; never relabel the
old current-object digest as target or alias evidence.

## Proposed primitive facts, not a receipt granting authority

Propose a separate `ADBUELFPA1` value, at most2KiB encoded, with exact keys/types:

| Group | Proposed fields and bounds |
|---|---|
| Discriminator | format=`ADBUELFPA1`, reason=`elf-provider-alias`, diagnostic_only=true, runtime_grant=false |
| Requested provider | requested_name accepted by the current library validator; provider_path_sha256 recomputed from `usr/lib64/` plus that name |
| Immutable alias origin | snapshot SHA256, alias_layer integer0..9, alias_entry integer0..65535, copied from the inspected symlink row |
| Exact target identity | target_utf8_bytes integer1..240, target_sha256 over the entire bounded UTF-8 literal; never a prefix hash |
| Optional target literal | target_literal only when independently accepted by the current library validator; otherwise null |
| Predicate decomposition | checks with exactly library_name_valid, same_family_prefix, not_self, numeric_suffix booleans; failed_predicates fixed-order nonempty subset of invalid-library, wrong-family, self-target, nonnumeric-suffix |

Shape/type and finite string/byte bounds must precede encoding, hashing or copying.
No arbitrary object coercion, error message, host filename, URL, token, content
buffer, stack trace or unbounded string enters the packet. Use exact existing
UTF-8 target bytes; never sanitize a different string and present it as observed.
A target rejected as a library name is withheld, not logged verbatim or clipped.
Validators accept exact primitive DATA from fixed calls, not arbitrary serialized
packets. Do not add a JSON importer to test duplicate keys that a dict cannot
retrospectively detect; serialized input itself must remain inadmissible.

After refusal, compute the four bounded boolean facts independently using the
same definitions as the unchanged conjunction. For numeric_suffix use the exact
`target[len(stem):]` slice even when the prefix check is false; do not silently
introduce a different basename or regex. These are a decomposition of the failed
conjunction, **not** a claim that short-circuit execution originally evaluated
all four expressions. The ordered failed list must match false booleans exactly.

If a literal is present, its current-library validation, encoded length and hash
must agree and all checks must be recomputed from that literal/request. Null
requires library_name_valid=false. For a withheld target, schema/list consistency
does not independently authenticate the remaining classification bits: they are
bounded source-produced diagnostic observations, not proof recoverable from a
hash or authorization to admit the target. Missing/contradictory/overflow values
are unavailable diagnostics, never an inferred valid target or used-zero success.

Capture at most one first fact, including an unavailable first capture, on the
original exception. Do not overwrite it with a later provider, root, retry or
relay. Deep-copy validated primitive output; an export mutation must not change
the original. Diagnostic/validator failure preserves the same original exception
and finite reason. No capture path can change queue/provider state or continue
past the rejected alias. Cleanup remains independent of diagnostics and poison.

## Relay, candidate context and compatibility

Use the existing fixed modules, not a new backend or arbitrary callback. Proposed
flow: producer validator in the ELF module; explicit field relay through
`_owned_graph`'s exact helper-module refusal catch; revalidation by the entry's
fixed ELF module; candidate-context validation in the existing provenance module.
The relay must handle separately loaded class identities without class-name
matching or losing the primary cause. A report is DATA, not an owner/work handle.

Keep `ADBUELF1` and `failure_packet`'s existing exact six-field schema unchanged.
Do not insert the new value into that packet and silently make old validators
reject it. Retain generic graph and string-table facts independently. Propose a
new first-only top-level provider-alias diagnostic field in the report, absent
when no such diagnostic applies; it cannot determine process success.

Publish real candidate context only from the currently admitted bounded static
mode and fixed call frame: exact head, fresh eight-path source-cohort digest,
run+attempt correlation, pinned manifest/config, literal bounded profile, complete
layer/namespace counters and the admitted compressed/diffID pair selected by the
alias's layer. The snapshot/origin come from the actual finalized model row, not
from JSON or an inferred target. Cross-check the new requested name against the
generic first graph fact where available. Inconsistent context withholds the
diagnostic and retains the refusal; it does not qualify a different candidate.
Before export, compare the fact's snapshot to the already finalized tree snapshot
held by the fixed entry; this must not cause another inspect/resolve after poison.

The complete candidate-context envelope has a fixed4KiB encoded ceiling; pure
facts retain2KiB. These are fixed additional failure-DATA bounds, not increases
to existing parser/model work,2MiB successful-observation storage, object/edge,
source, download, archive or time ceilings. One packet only: no per-provider
journal, body copy, artifact export or retained candidate file. Overflow remains
unavailable. Never consume a new model grant or restart the deadline for capture.

No CLI, workflow input/default, repository permission, pin, target path, source
selection, provider policy or successful output changes are needed. Modified
producer/relay/provenance bytes must enter the newly computed existing eight-path
source cohort; do not reuse the failed proof's digest or retrofit its old receipt.
No physical target can be reported as followed/verified from this alias refusal.

## Required non-vacuous implementation evidence

Future offline tests must use actual provider/model/parser code and discriminate
success from refusal. Preserve the existing positive same-directory numeric alias
graph and strict/provider/origin/security negatives. An always-unavailable
capture or a module with only hand-made packets is insufficient.

- Exercise positive actual same-family resolution and invalid requested names.
  Spy on inspections: a refused alias uses the already inspected row, with zero
  additional target/body/host reads, regardless of diagnostics malfunction.
- Isolate each predicate where possible: use a valid foreign stem of equal length
  with a numeric suffix; a same-family nonnumeric suffix; and a target longer
  than128 but within the model's240-byte path domain, with otherwise valid prefix/
  numeric checks. Do not let a later path-length refusal hide guard removal.
- A self-link is also stopped by the later regular-target check. Kill removal of
  the earlier not-self guard with **first reason and no-second-inspection**
  assertions, not a false claim that its removal necessarily yields graph success.
- Cover malformed types, booleans-as-integers, bounds, Unicode/unsafe targets,
  literal/hash/length mismatch, unknown/duplicate keys or failed clauses, empty/
  reordered lists, stale/mismatched snapshot/origin/context, overflow, duplicate
  capture and deep-copy isolation. Invalid literals must never appear in output.
- Force capture and both relay validators to fail independently; preserve the
  same first refusal, owner poisoning/accounting and exact cleanup attempt. Kill
  removal of capture, first-fact retention and relay/context validation with
  otherwise admissible witnesses, not an unrelated earlier rejection.
- Retain actual generic positives and all root-pin checks. No ROOT_HASHES override,
  artificial real-entry success path, weaker aliases or skipped provider may be
  introduced to make diagnostics pass. Stubbed context tests are not genuine job
  authentication, artifact acquisition or native qualification.

New isolated tests may create only owned synthetic byte/archive/model fixtures.
Do not run UI/UX, claim Database Review/physical transaction proof, refresh old
whole-plugin pre-release rows or reopen deferred native/observer work.

## Validation, progress and next approval

This design changes documents only. Review the complete scope, links and unchanged
non-document bytes; run existing isolated PHP validation and structure checks,
then commit/push and verify ordinary CI at the exact resulting head. Record only
executed results; publication CI cannot turn the preserved failed static run into
success. No implementation or new diagnostic fact/target observation is claimed.

Executed local `php vendor/bin/phpunit --do-not-cache-result` passes **2,604 tests /
23,438 assertions**, no failures/errors, two existing Sodium-unavailable skips.
`npm run check:structure`, affected local-link checks and `git diff --check` pass.
Every non-document byte matches the restore baseline, including admission,
diagnostic source, workflow, tests, runtime includes/assets and dependencies.
Only this design and its owning plan/foundation pointers change. Exact-head
ordinary CI counts/conclusion are retrieved from the published commit's checks
and final handoff; pending runs are not treated as successful.

Implementation-plan progress: the missing first-alias evidence and bounded relay/
context/test contracts are now specified; provider admission remains gated.
Campaign stays7/10 milestones/native7/10/seven static acquisitions. No design,
diagnostic or publication commit is counted as product-feature completion.

**Recommended next gated slice: Bounded Provider-Alias Diagnostic Safety
Foundation and One Owned Static Diagnostic/Cleanup Proof.** Approve the fixed
producer/validators/first-only relays with offline tests, focused review, local
validation, commit/push and exact-head ordinary CI, followed by exactly one fresh
static-only diagnostic run at that clean published head if checks pass. Keep the
alias conjunction, pins, limits, extraction/execution and production wiring
unchanged. The job must still fail on the original alias refusal; capture of a
valid diagnostic meets an evidence objective, not graph qualification. Require
primary/independent cleanup and no retained artifacts; no unchanged retry or
policy exception. This combined implementation/one-run authority is requested
for the next approval only and is **not** granted by the present design approval.
