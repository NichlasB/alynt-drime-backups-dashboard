# Genuine-HTTP Pre-Router Resource Admission Audit Stop

## Authority and checkpoint — 2026-11-08 UTC

The user approved Genuine-HTTP Owned Bootstrap/Request IO Integration with
Installer Containment and ONE Ephemeral-CI Qualification/Cleanup Attempt,
audit-first with fresh prerequisites and exact owned cleanup. This records the
source-audit stop BEFORE implementation or native execution, not an unsuccessful
native attempt or a revocation of that approval.

Dashboard baseline `281916314260f60d86afb8f56d4118a2093df7d6` was clean and
equal to remote master; [ordinary CI42414815686](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/42414815686)
was reverified completed/success for both PHP jobs. Toolkit baseline
`6d4294b54b0d5dc8e6ef9630981c9779c6750287` and untouched uploader
`3dfcb15b5cab8191df7d984251986ead140ded52` were clean and remote-equal.
Only this record and six directly owning plan/resume records change. Git retains
the full pre-edit baseline; no executable source, test, workflow, dependency,
artifact pin, acceptance rule or production-wiring change.

Campaign remains4/10 completed product milestones,8/10 native attempts,
cumulative31. ONE approved attempt remains unused. No prerequisites/native
dispatch, HTTP listener/request, core loading, authentication, SQL, fixtures,
LocalWP/live operation or cleanup experiment ran. No newly owned lab resources
exist to clean up. The earlier recoverable draft archive was inspected in memory
only; no archived helper was extracted, restored or used as qualified code.

## Source evidence and exact scope

Public immutable source reads were bounded to524288 decoded bytes per file,
in memory only. PHP tag `php-8.5.12`, annotated tag object
`c96586077b050b362744ba295f97f6395f58a75a`, resolves to commit
`136e049ac10b032c83018e65b63b6df858943750`.

- [cli-server body parser](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/sapi/cli/php_cli_server.c#L1803):
  lines1803–1813 allocate the first body buffer with
  `pemalloc(parser->content_length, 1)`, then grow/copy received body bytes.
  File86897 bytes, SHA256
  `d25561716a036bbbd3d31307d4fb08fcba6d3977878fc97761b5989e25f82eb2`.
- The [request dispatch path](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/sapi/cli/php_cli_server.c#L2655)
  reads the request before dispatch; lines2321–2329 perform request startup and
  router dispatch later. A router-side body cap therefore cannot establish a
  cap on this earlier C allocation.
- [Persistent allocation macros](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/Zend/zend_alloc.h#L205)
  select `__zend_malloc`/`__zend_realloc` for persistent=true; header25024 bytes,
  SHA256 `8638c5b8f53631211674261183faf5311c8172ef51fc23a62ad398e3f2ca6b97`.
  [Allocator implementation](https://github.com/php/php-src/blob/136e049ac10b032c83018e65b63b6df858943750/Zend/zend_alloc.c#L3945)
  lines3945–3951 call C `malloc(len)`. PHP/userland memory settings alone must
  not be presented as independent containment of this pre-router path.

The selected [owned HTTP profile](SOURCE_POLICY_GENUINE_HTTP_OWNED_STORAGE_DESIGN.md)
uses PHP8.5.12 cli-server, a private bounded parent,16KiB body/32KiB headers and
64MiB PHP/WordPress limits. It has not explicitly settled whether input bounds
mean producer-emitted/userland-consumed input or a pre-router server-ingress
allocation guarantee. That assurance distinction requires a decision before the
physical qualification is represented as satisfying the profile.

This is NOT evidence of an observed oversized request, OOM, attack, plugin
vulnerability, compiled-binary attestation or inevitable failure of a valid
bounded private request. No claim is made that every lower-level parser limit
or header-allocation path has been audited. Bounded producer input can still be
suitable for functional HTTP qualification; it is not interchangeable with
adversarial pre-router ingress/allocation containment.

Pinned WordPress7.1.4 source at
`db96f5eaa77a8d3e8eec8923a518fa2326f0d46a` was also inspected: genuine
WP_Hook callback metadata matches the dormant helper's expected shape, the
stock default theme agrees with the selected fresh-install theme, and the
bounded default-filter scan did not identify a direct required-native-hook
conflict. These limited source findings do not establish the complete loaded
graph, genuine authentication, installer containment, native compatibility,
full-shutdown effects or HTTP proof. Existing API doubles remain isolated tests.

## Focused reviews and disposition

Canonical Feature Light scope: seven changed Markdown records and their directly
referenced profile/helper/source call sites, read-only. Risk: Other, future
HTTP/resource qualification. One resource-assurance issue flagged; zero runtime
fixes; one explicit admission decision outstanding. Direct resume records now
point here, so prior execution recommendations are historical approved steps,
not permission to bypass the newly identified decision. No broader pre-release
review is claimed. Bloat/Structure unnecessary: no code, duplicate implementation,
file split or new architecture. UI/UX not applicable to this backend audit.

Final focused Feature Security disposition: no executable change or confirmed
plugin defect; the unproved pre-router resource assurance remains BLOCKED rather
than being reported as fixed or runtime-security verified. Existing isolated
HTTP tests cannot prove this property. Regression disposition: resource/runtime
qualification would be the proper layer if that guarantee is selected; it is
not practical to infer C-ingress containment from API doubles. Do not add an
artificial success path or weaken the current guards to make coverage green.
Historical whole-plugin security/Database/pre-release rows remain untouched.

## Exact next gated slice

**Repository-Only Private-Producer HTTP Envelope and Pre-Router Resource
Admission Decision**, design-only. Preferred proportionate direction to evaluate:
retain cli-server for private functional HTTP qualification with explicit
producer-emitted/userland-consumed bounds, while excluding an unproved
pre-router allocation-containment claim. Preserve current numerical budgets,
request/auth/native guards and production isolation; do not silently redefine
acceptance or introduce a proxy, new toolchain or generic attestation project.

This is an assurance-scope decision, not routine harness repair. Approval is
needed for that design decision before implementation or consumption of the held
ONE attempt. The decision itself authorizes no acquisition, runtime loading,
native experiment, storage writes, wiring, grants, activation or rollback.
Implementation-plan progress: pending genuine-HTTP qualification now has a
specific source-backed resource-admission gate; no product milestone completed.

Fresh local validation: `node --test tests/node/source-policy-http-*.test.mjs`
with explicit existing PHP/Python paths passed47/47, failures/skips0. These are
isolated API/IO/DATA/semantic-control tests, not genuine HTTP/native proof.
All412 local Markdown links across the seven changed records resolve;
`git diff --check` passes. Final light/security diff review retains the single
unresolved admission decision and introduces no executable/public surface.
Exact-head publication evidence is recorded after actual completion in the
toolkit's source-scoped entry; ordinary CI is not native proof.
