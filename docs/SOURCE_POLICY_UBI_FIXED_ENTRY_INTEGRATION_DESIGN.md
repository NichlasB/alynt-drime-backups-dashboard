# Fixed-Entry Bounded String-Access Admission and Static Qualification Integration Design

Current implementation: the [default-off admission/cleanup foundation](SOURCE_POLICY_UBI_FIXED_ENTRY_FOUNDATION.md)
implements this design with offline evidence only. Next gate is one separately
approved owned static qualification/cleanup proof, not candidate execution.
Earlier proposed-state/design evidence below remains historical.

## Approval, baseline and proof limits — 2026-11-02

User approves repository-only design, validation, commit, push and exact-head
ordinary CI. This is R1 documentation of a sensitive future integration, not
implementation or a risk-tier increase. No entry/workflow/profile behavior,
acquisition, extraction, image loading, candidate/native execution, SQL, installed
site, credentials/permissions, schedules, production wiring, package, tag, release
or deployment changes. Uploader/toolkit stay untouched. No manual job dispatch.

Restore/review baseline **3bac53d7e8c119ecbec59bb49018d49a47c600c6** is clean/synced;
[exact-head ordinary CI41489712767](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41489712767)
is reverified successful. The [fixed-owner foundation](SOURCE_POLICY_UBI_FIXED_OWNER_FOUNDATION.md)
has actual synthetic parser/collector/model composition, not genuine fixed-job
or candidate qualification. The latest [real diagnostic41450662313](SOURCE_POLICY_UBI_ELF_STATIC_ADMISSION.md#actual-string-table-diagnostic-and-cleanup-result)
still refuses the strict string-table extent after six layers. No final graph
or runtime compatibility is established. Campaign remains **7/10 milestones,
7/10 native attempts, six static acquisitions**; no design/retry/docs credit.

## Actual consumers and remaining integration gaps

Read-only scope covers the [fixed entry](../scripts/ci/source-policy-legacy-ubi-static.py),
[manual workflow](../.github/workflows/source-policy-legacy-ubi-static.yml),
[collector](../scripts/manual/source_policy_ubi_alias_static.py),
[model/owner](../scripts/manual/source_policy_virtual_closure.py),
[ELF parser/graph](../scripts/manual/source_policy_ubi_elf_static.py), acquisition/
strict-reader/provenance dependencies and their isolated tests. Current behavior:

- Three exact CLI modes pair with alias/ELF flags; old ELF selection is strict.
- `target` binds repository, master ref, workflow path, expected SHA, numeric
  run/attempt and absolute runner-temp root. Workflow separately checks Linux/
  x86_64 and uses a pinned checkout without persisted credentials.
- `proof` freezes participating sources and starts one300s deadline before
  metadata acquisition. After metadata admission it collects layer0, constructs
  a model and replay-charges successful parser work before each apply.
- Selected-root pin admission is separate from the generic graph. The dormant
  `_owned_graph` closes only model state and is not a substitute for those pins.
- Primary source readback and cleanup currently share one try block: a readback
  exception can skip primary cleanup. Constructor failure can also leave no
  `owner` handle although an owned root was partially created. Always cleanup
  is independent, but primary reporting must not infer absence from `owner=None`.
- `_owned_graph` loads its own ELF module; its exception class is not the entry's
  separately loaded ELF class. The current entry refusal tuple cannot simply be
  reused for this new graph path without losing a finite policy cause.

These are design findings and future acceptance requirements, not changes or
executed failures in this task. No historical qualification is relabeled.

## Selected policy: a separate explicit, default-off static mode

Do not reinterpret `elf_policy=true`, automatically retry a strict refusal with
bounded strings, infer selection from receipts, or change old defaults. Propose
one new boolean workflow input `bounded_access_policy`, default false, matching
`BOUNDED_ACCESS_POLICY` and the distinct fixed CLI
`--approved-bounded-elf-static-prerequisite`. These names are proposed, not added.

| Alias | ELF | Bounded access | Selected policy |
|---|---|---|---|
| false | false | false | Existing strict selected-origin scan |
| true | false | false | Existing alias/hardlink namespace scan |
| true | true | false | Existing strict1MiB ELF/provider scan |
| true | true | true | Proposed shared-owner bounded-string static scan |
| Any other combination | | | Refuse before root creation/network |

Accept only exact lowercase boolean strings at the environment boundary and one
matching CLI argument. Preserve existing `policy_flags` callers, tuple contracts
and independent negative cases, either through a compatible wrapper or a narrow
new fixed selector; do not silently change the meaning of old return values.
Cleanup remains a separately admitted operation and does not require work/profile
readiness. No automatic trigger, PR/fork execution, URL/image/root/pin override,
shell command input, callback/factory or extra workflow permission is selected.
Keep the runner,10-minute outer timeout, read-only permission and non-cancelling
concurrency group. One dispatch remains one explicitly approved static attempt.

## Genuine job, source cohort and authority construction

Retain all existing target/CLI/environment admission before owned creation or
network effects. Require the checkout HEAD to equal EXPECTED_SHA/GITHUB_SHA and
the fixed participating source paths to match that checkout without modifying
it; use fixed local read-only Git checks, not a network lookup or untrusted path.
This closes source/head binding instead of treating a caller's hash as authority.

The bounded mode's frozen source map comprises the entry, workflow, acquisition
module, strict archive reader, provenance module, collector, model/authority and
ELF module: the same eight participating paths currently frozen for ELF mode,
with their newly implemented bytes. Recompute the sorted canonical source-map
SHA256 from actual files; do not hard-code or reuse the old diagnostic cohort.
Verify the entire map again after the attempt. Any future additional participating
helper must enter this fixed map and its tests. Do not replace a file hash with a
documentation SHA or claim the map pins the runner/interpreter/stdlib build.

Preserve manifest/config hashes, all ten layer descriptors/sizes/diffIDs,
linux/amd64 metadata, declared environment/entrypoint/volume restrictions and
download identities. No hash substitution, reachable-object shortcut, provider
fallback, table-sized cap increase, lazy acceptance of an unavailable object,
extraction or execution follows from this profile.

After real metadata `admit`, before collecting any layer, construct exactly one
`alias.v._FixedWorkAuthority` with the admitted manifest/config/layer identities,
fresh source-map digest, verified head, source-derived run correlation
`SHA256(GITHUB_RUN_ID + ':' + GITHUB_RUN_ATTEMPT)[:32]`, literal profile
`bounded-strings-v1` and the original pre-acquisition deadline. These constructor
arguments remain DATA; reviewed fixed construction and actual job checks establish
their provenance. Never accept a serialized owner, balance or context from a report.

## Collection, model and final pin/graph ordering

1. Create the single deadline before metadata requests, as now. Preserve the
   aggregate2,097,152 work cap,256 ELF reservations,2MiB encoded observations,
   existing archive/download/entry/time limits and exact alias profile.
2. Pass the same exact loaded authority and source-derived layer ordinal to
   `collect` only in the new mode. It derives body context, issues/invokes/settles
   the real parser call and returns no provisional observations before full hashes.
3. After verified layer0 use `_owned_model` with its actual profile and same owner.
   Apply it once. **Do not call `tree.charge(collected['elf_work'])` in this mode**;
   that is already settled consumption. Preserve the old charge in old ELF mode.
4. Apply each subsequent verified layer in order, using authority time/ticks.
   Retain only compact `{metadata, elf, elf_bindings}` envelopes for `_owned_graph`,
   at most ten containers/256 observations under the same byte cap. Release full
   records/ordinals/member buffers after apply; no ten-layer archive-record journal.
5. Finalize all ten layers; resolve the two fixed WANTED roles using the bound
   model, then call the unchanged actual `elf.admit_selected(roles)` **before**
   `_owned_graph`. Require both pinned root hashes/canonical origins. Do not use
   generic graph success as root-pin acceptance or skip the matcher in tests.
6. Run the owner-bound graph on compact envelopes. Preserve immutable origin,
   snapshot, SONAME, same-family provider-alias, search/feature/cycle/edge rules.
   It may close model state. Do not subsequently resolve/inspect/charge the closed
   owner or manufacture a new model/budget to complete reporting.
7. Model closure is provisional. Final program success additionally needs exact
   selected profile, all layers/pins, unchanged source and confirmed primary cleanup;
   whole-job qualification additionally needs successful always reconciliation.

Report the access profile and bounded owner snapshot separately from scan byte
counters. `elf_work` is only a successful-parse summary, not a spendable balance;
known failed work and conservative burned work must remain distinguishable.
Do not import caller-tallied counts or use a status string to reopen a poisoned
owner. No new-mode success under the lesser `selected-origins-verified` status.
Even success is static DT_NEEDED closure only: runtime, relocations, NSS/FIPS,
dlopen/plugins, shutdown and legacy minimum-profile native qualification remain
unestablished. Candidate execution/database/image-loading/runtime grants stay false.

## Failure relay and cleanup must not depend on one another

The fixed helper must recognize its own exact loaded ELF refusal class and relay
its finite first cause into the collector/entry's known exception domain, with
validated bounded facts. Do not rely on class-name matching, arbitrary exception
duck typing or a different import's `isinstance`; do not replace a real graph
policy refusal with `unexpected-tool-failure`. Unexpected errors still fail closed.
Validate the fixed owner snapshot before reporting: exact keys/types, work<=cap,
counts<=256, observations<=2MiB, valid states and runtime_grant=false. Contradictory
or unavailable diagnostics cannot authorize success or replace the first cause.
Require verified_layers<=10, records<=settled<=grants<=256, grants-settled in
{0,1}, and work_used=parser_work+model_work+grants+settled+burned_work. Closed
model completion requires no unsettled grant. These scalar checks validate DATA
consistency only, not independent proof that the fixed invocation occurred.

Separate failure accounting, source readback, primary cleanup, reporting and
return decision. A source readback exception must mark source verification false
and still attempt exact cleanup; cleanup/reporting exceptions must not overwrite
the original parser/policy reason. Poison, outstanding allowance, exhausted work
or deadline cannot gate cleanup. Never call authority spending methods to clean.

Attempt the existing `cleanup(root)` against the already target-admitted root
even if Owned construction did not return a handle. Missing root can be confirmed
absent; partial marker/unknown membership must refuse and retain, not claim zero
artifacts because owner=None. Preserve root name/parent resolution, UID,0700 mode,
head/run owner marker, regular-file/single-link checks, thirteen-leaf membership
and non-recursive deletion. Do not add report files to the directory or broaden
the allowlist. Reporting uses bounded stdout only; tokens/URLs/raw bodies stay out.

Keep the existing independent `always()` cleanup step and its target binding.
A source mismatch or cleanup failure means failed qualification regardless of
model status. Inspect final workflow conclusion and actual cleanup output before
claiming success. A skipped reconciliation is not cleanup evidence. Unknown or
foreign ownership is a stop, not permission for forced removal or another attempt.

## Required future verification, not performed implementation evidence

- Keep actual parser/model synthetic positives and all predecessor strict/default
  refusals. Eagerly observed superseded/unreachable objects still consume budget.
- Exercise the real selector and target checks across every valid mode and hostile,
  stale-head/run/workflow/CLI combination; witness refusal before Client, Owned,
  layer opening or authority construction. Unit environment dictionaries are not
  genuine GitHub authentication or source attestation.
- Test the entry's fixed construction inputs/order, one original deadline, one
  owner, no parser replay charge, ten-layer compact-envelope bound and root matcher
  before graph closure. Detect reset, second owner, missing pin or stale source.
- Fault-inject source readback/partial construction/reporting/cleanup separately;
  require exact cleanup attempts after primary failure, first-cause retention,
  nonzero result, no forged completion and independent always-step evidence.
- Discriminate an actual helper-module ELF policy refusal and malformed accounting
  from unexpected tooling errors. Removing its relay validator must fail tests.
- Update existing whole-file "disconnected" fences only under implementation
  approval: preserve stronger witnesses that all old modes stay strict and the new
  mode cannot be selected without every gate. Do not merely delete those assertions.
- Assertion-killed controls must remove mode/head/source/pin/ordering/accounting/
  cleanup guards on otherwise admissible inputs. No guard can be "tested" only by
  a different earlier refusal. Do not weaken pins or add a real-entry test factory
  to obtain synthetic full-candidate success. Actual generic positives plus pin
  matcher DATA tests are distinct from a real fixed-entry qualification.

A later explicitly approved implementation is repository-only/offline-tested;
it does not include a manual dispatch. Only after its exact-head ordinary CI and
focused review may a separately approved, exact-head, one-owned-static-acquisition
and cleanup proof select the new mode. Record truthful failure if further parser,
provider, budget or cleanup constraints refuse; no unchanged blind retry. This
design spends neither a static acquisition nor a native attempt and authorizes no
candidate execution regardless of a future static result.

## Validation, plan progress and next approval

This change is documents only. Review local links, scope/diff and unchanged
non-document bytes; run existing isolated PHP validation and structure checks,
then commit/push and verify ordinary CI at the exact resulting head. Local and CI
counts must be executed/retrieved, never inferred from the baseline. No UI/UX,
Database Review, installed-site operation or whole-plugin release-readiness row
is refreshed. Final publication results belong to this change's checks/handoff.

Executed local `php vendor/bin/phpunit --do-not-cache-result` with the configured
isolated Python runtime passes **2,603 tests/23,433 assertions**, no failures or
errors, two existing Sodium-unavailable skips. `npm run check:structure`, affected
local-link checks and `git diff --check` pass. Every non-document byte matches
the restore baseline, including entry/workflow, parser/model/collector, tests,
runtime includes, generated assets and dependency manifests. Only this design,
the implementation-plan resume point and foundation pointer change. No new
implementation test, new qualification result or physical cleanup is claimed.

Implementation-plan progress: the concrete fixed-entry source/run/profile/pin/
cleanup ordering is now specified. It is not implemented or qualified. Campaign
remains7/10 milestones/native7/10/six static acquisitions; earlier gates are history.

**Recommended next gated slice: Repository-only Fixed-Entry Bounded-Access
Admission and Cleanup Integration Safety Foundation.** Approve the default-off
selector, fixed construction, compact-envelope/pin ordering, bounded refusal relay
and independent cleanup orchestration with offline regressions, validation,
commit/push and exact-head CI. Preserve existing modes and limits; no acquisition,
manual dispatch, candidate execution, installed-site or production change. A real
owned static proof remains a separate explicit approval after that foundation.
