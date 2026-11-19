# PHP 8.5.12 Archived-Build Provisioning and Trust Decision

## Scope and decision — 2026-11-19

User approved this repository-only design following the
[exact-patch diagnosis](SOURCE_POLICY_INITIALIZED_HTTP_NATIVE_INTEGRATION.md).
R0 read-only supplier metadata/source assessment and R1 design publication only.
No runtime/archive download,extraction,installation,execution,workflow dispatch,
WordPress/SQL/fixtures,LocalWP/live/client/Drime or production wiring. No general
trust/risk-tier increase,artifact admission exception or native allowance.
Clean/synced source/review/restore baseline
`46c5addbf469ffc2c76e5651cdfe48724baecb7c`,ordinary CI44563550730 reverified
completed/success. Toolkit baseline`fdbffb878ef7f8fce4ae4a81a1764290831c8865`;
uploader remains outside scope. Version0.1.77/schema8/PHP7.4/WordPress6.0 unchanged.

Decision:the EXISTING supplier retains a concrete exact-name candidate. Recommend
that candidate for a separately approved acquisition/static-inventory/owned-cleanup
proof ONLY. Do not mark it provisionable or runnable from metadata. Keep all
PHP/mysqlnd/core/server pins,checks,resource budgets and disabled wiring intact.
No new supplier,compiler,build framework,package installation or cohort rebaseline.

## Exact observed candidate, not verified archive bytes

Public GitHub API metadata was read without requesting an octet-stream asset.
The matching object was independently re-read by its asset ID,with same fields.

| Field | Observed value |
| --- | --- |
| Supplier | shivammathur/php-ubuntu,already used downstream by pinned setup-php |
| Release | builds,ID39619287;immutable:false |
| Asset ID | 747285397 |
| Name | php_8.5.12-nts+ubuntu24.04.tar.zst |
| Size | 41,599,355 bytes |
| SHA-256 from API | 6eec9def9f436e192051dda270ed531be5e4dabb87ad3bcbe5c8d24fad6ccab5 |
| Created/updated | 2026-11-14 11:31:24/11:31:26 UTC |
| State/uploader | uploaded/github-actions[bot] |

[Release](https://github.com/shivammathur/php-ubuntu/releases/tag/builds),
[asset metadata](https://api.github.com/repos/shivammathur/php-ubuntu/releases/assets/747285397).
The un-suffixed architecture follows supplier naming for the x86_64 candidate;
actual ELF architecture,NTS,PHP8.5.12 and module versions are NOT verified.
ARM64,ZTS,debug-symbol and floating8.5 archives are different objects,not fallbacks.
The release/filename can be replaced:asset ID/name/size/API digest are observations,
not immutable retention or proof of binary content. A future mismatch/deletion
must refuse,not refresh the pin from a later API response.

## Source evidence and trust limits

Supplier source was read at fixed commit
`8369c135cf7be6740c474d31c78204d94b6bff74`,not executed. This is a review reference,
NOT evidence that this commit built the November14 archive.

- [package-php.sh](https://github.com/shivammathur/php-ubuntu/blob/8369c135cf7be6740c474d31c78204d94b6bff74/scripts/package-php.sh)
  derives SEMVER from php-config,creates minor and full-semver archive names from
  the same bytes,and packages changed filesystem/required-package files. That
  explains retention of exact-name candidates;it does not attest this asset.
- [release.sh](https://github.com/shivammathur/php-ubuntu/blob/8369c135cf7be6740c474d31c78204d94b6bff74/scripts/release.sh)
  uploads assets with --clobber. Filename/version alone is not a content pin.
- [install.sh](https://github.com/shivammathur/php-ubuntu/blob/8369c135cf7be6740c474d31c78204d94b6bff74/scripts/install.sh)
  downloads with mirrors,extracts at /,merges package metadata,changes alternatives,
  may start FPM and repairs packages. Do NOT invoke it or treat it as private
  provisioning/cleanup. Passing full-semver does not make these effects acceptable.
- Packaging/source requirements enumerate broad libraries/system files. An archive
  is not necessarily a relocatable CLI kit or a complete stable dependency closure.

Retain the existing trusted-distributor assurance limit:GitHub publisher metadata
and its digest are not an independent signature,build attestation or proof that
the supplier cannot replace content. Pinning a digest prevents silent byte drift
after selection;it does not prove behavior. No new attestation/key-management
tooling,policy bypass or hostile-supplier guarantee is introduced.

## Smallest proposed admission/provisioning sequence

1. Separately approve ONE owned ephemeral-CI archive acquisition/static inventory
   and exact cleanup;NO native qualification,installation or runtime loading.
   Fetch only this fixed public asset over TLS,with bounded redirects restricted
   to GitHub's actual asset-delivery hosts. Do not log signed redirect URLs,headers
   or tokens. No mirror,latest selector,version rewrite or automatic retry.
2. Before parsing/decompression,require exact41,599,355 bytes AND the pinned
   SHA256 above. File must be newly created in a private owned directory,not a
   symlink or preexisting file. Hash mismatch,extra/truncated bytes or unknown
   ownership refuses. A metadata digest is not an acquisition verification receipt.
3. Use existing trusted runner tools only,checked before acquisition;no dependency
   install if unavailable. Prefer existing bounded archive/ELF utilities where
   applicable,but do not inherit legacy-UBI candidate-specific exceptions or claim
   the current gzip/layer rules already support Zstandard. No new general framework.
   The reviewed runner's Zstandard decoder availability/caps remain Unknown.
4. Inventory the archive as DATA without materializing its paths or invoking its
   scripts/binaries. Bound decompression window/memory,expanded work,entry/path/
   header/member counts,depth,time and output. Reuse the current limits as ceilings:
   job600s,cleanup30s,memory64MiB,entries8192,aggregate192MiB,member16MiB,
   diagnostic64KiB. Streaming work counts even for skipped members;compressed
   archive bytes also count toward aggregate staging/work. Do not increase limits
   to fit a large system cache. Decoder/tool semantics and cumulative accounting
   must be established before this future proof,not silently assumed.
5. Reject traversal,absolute/drive/NUL paths,duplicate/ambiguous names and devices;
   report link/PAX/type metadata without creating links. Do not automatically admit
   a hardlink/symlink-target policy or resolve a selected executable through an
   unapproved type. Selected unresolved roles mean NOT qualified,not a fallback.
   No extraction into /,package DB,alternatives,services or installed/site state.
6. Locate CLI,mysqlnd,mysqli and their ELF/interpreter/DT_NEEDED/RPATH/versioned-
   symbol requirements with bounded static readers;compute member hashes and
   exact byte/path/type identities. Static analysis must not use ldd,php --version,
   php -m,dlopen or any artifact execution. Host libraries are unresolved unless
   their exact allowed origin/identity is independently established. Preserve
   existing no-host-INI/no-PDO/NTS/word-size and client-version runtime gates.
7. Return a finite inventory,resource totals and Unknowns,then verify exact owned
   cleanup even on refusal. Missing dependencies,unsupported encoding/type,
   nonrelocatable paths,cap overrun or missing decoder stops. No acquisition retry
   or automatic budget/dependency/policy exception. No runtime evidence credited.
8. ONLY after static qualification,design a concrete private prefix and immutable
   PHP/module/library manifest under the SAME job. Then separately approve any
   extraction/loader/runtime provisioning proof;prove no global overwrite,ambient
   module substitution,INI loading or unexpected library fallback. Existing bare
   php/extension=mysqlnd/extension=mysqli launch paths must NOT silently bind to
   mixed8.5.13 host components. No implementation or execution grant here.
9. A later native/HTTP attempt needs explicit new allowance,fresh existing
   prerequisites and unchanged guards/budgets. This design does not repair or
   refresh earlier failed native results or permit further attempts.

No claim is made that the candidate fits these ceilings,contains the selected
regular files,can relocate,has all dependencies,or matches a prior observed PHP
binary hash. Prior successful8.5.12 hashes are evidence for those runs,not archive
content or provenance proof. The archive's static qualification remains open.

## Source-scoped review, validation and resume

Canonical Feature Light SOURCE-SCOPED:design plus owning resumes/direct unchanged
profile,preflight,launch/recovery and supplier callers. Found the stock-installer
effect/relocation gap;design explicitly excludes that path and separates candidate
selection/static admission from provisioning/native authority. No remaining
design ambiguity requiring an implementation exception. No code/bloat/UI review
trigger,whole-plugin review or Database Review completion.
Final Feature Security SOURCE-SCOPED:fixed candidate/size/digest,closed delivery,
type/path/resource gates,DATA-only inspection,protected installed state and owned
cleanup;no new public/auth/SQL surface or confirmed code defect. Acquisition/parser/
ELF/closure/cleanup failure regressions belong to the next implemented harness,
not claimed from this document. No receipt is synthesized from unavailable facts.
Validation executed:exact candidate metadata/design identity comparison,345 local
links/whitespace,existing PHP profile/identity/qualification57 tests/451 assertions
and isolated HTTP140/140 all passed,no errors/failures/skips. No full local suite/
build or artifact/native test claimed. Actual ordinary exact-head publication CI
belongs in the canonical toolkit closeout;it cannot establish runtime admission.

Current implementation plan advances an actionable same-supplier candidate and
staged trust decision,NOT a completed HTTP/product milestone. Milestones4/10,
cumulative43 and exhausted native allowances unchanged;production wiring disabled.
Recommended next gated slice:ONE Owned PHP8.5.12 Archive Acquisition,Static
Inventory and Cleanup Proof at the pinned candidate above,after existing-tool/
resource-admission preflight. No filesystem extraction,installation,runtime/module
loading,native/SQL/site operation or production wiring in that proof.
