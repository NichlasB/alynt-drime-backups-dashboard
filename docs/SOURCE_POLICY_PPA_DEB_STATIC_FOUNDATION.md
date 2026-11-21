# Candidate-Bound PPA DEB Static Admission Foundation

## Authority, baseline and outcome boundary — 2026-11-21

User approved the foundation and ONE owned static proof, explicitly including
direct PPA trust and DATA-only inspection. Continue routine related work without
approval pauses; five rounds does not authorize five acquisitions, installation,
extraction or candidate execution. Narrow R3 acquisition/owned cleanup only, not
a general risk-tier increase. Review/restore baseline:
`e75cb8287c007bb623c4178d1080cf7a83e5f52a`, clean/synced master; preceding
CI44788684261 reverified actually successful. Toolkit baseline
`824d51efe8841e6692b7bbd6d7944f2e7da07597`; uploader untouched/out of scope.

The [candidate decision](SOURCE_POLICY_PHP_8512_DEB_CANDIDATE_DECISION.md) owns
the exact three package names, revision, origin, byte sizes and SHA256. They are
literal code pins, not latest selectors; metadata total4,146,302 bytes. Trust is
direct publisher/TLS plus frozen digest, NOT package signature verification,
independent build attestation or immutable availability. All three compressed
identities must match before any nested inspection. No fallback/repin/retry.

Manual workflow `source-policy-ppa-deb-static.yml` admits only NichlasB, this
repository, master, an exact40-character expected SHA matching the dispatch SHA,
first run attempt, Linux/x86_64 and checked-out source closure. Read-only token,
pinned checkout with credentials disabled; no install/cache/artifact upload.
Existing-tool synthetic preflight must pass before acquisition. No scheduled
trigger or public/plugin caller exists. CLI entry and cleanup repeat exact-job
admission. The ONE allowance is consumed on dispatch even if preflight refuses.

## Closed DATA and resource policy

- Ordinary AR only: exact magic, three ordered members, debian-binary2.0,
  control.tar and data.tar, optional gz/xz/zst codec. Fixed headers/decimal size,
  at most16MiB per member, exact newline odd-byte padding, no extra members,
  thin archives, external references or long-name tables.
- Tar ustar/GNU fixed header only, strict checksum/numeric/text/padding/end;
  regular files, zero-size directories and DATA-only link metadata. No PAX,
  devices, sparse extensions or implicit unsupported-format recovery.
- Candidate-specific virtual key policy strips ONE leading literal `./` and
  one directory slash; root`.`/`./` only zero-size directory. Then printable
  ASCII240/depth32/no absolute, drive, backslash, dot/parent/empty segments.
  No host path is formed from a member name. Canonical duplicates refuse;
  cross-package collisions refuse except repeated directories. Existing archive
  path acceptance remains unchanged. Link metadata is hashed, never resolved.
- Control is regular, at most64KiB, unique bounded fields, exact Package/Version/
  Architecture. Only bounded Depends metadata is emitted; scripts are streamed
  and hashed, not interpreted or echoed. Selected CLI/mysqlnd/mysqli paths must
  be actual unique regular ELF64/x86_64 members. The fixed reader records ELF
  hash/size/interpreter/NEEDED/search paths, never calls ELF/ldd/module loading.
  Versioned symbols, actual PHP/NTS/module API and complete closure are Unknown.
- One shared ledger across three files and all nested streams:192MiB charged
  acquisition, compressed rereads, headers/padding and decoded work;8192 entries;
 16MiB member;64KiB final diagnostics;600s job,540s work,30s cleanup. No reset per
  package/stream. Concurrent address-space partition40MiB parent +24MiB one
  decoder, total64MiB;decoder CPU60s and8MiB window ceiling unchanged.
- Bounded nonblocking stdin/stdout pipes feed slices of original DEBs directly
  to existing trusted decoders. No nested staging tar, extra producer process,
  extraction, thread pool or shell. Zstd frame checked first and -M8MB enforced;
  gzip uses its fixed dictionary; XZ uses conservative8MiB TOTAL decoder-memory
  limit, so an8MiB dictionary plus overhead can refuse. This is not a relaxed
  dictionary/window policy or a promise the package codec fits.
- Direct fixed HTTPS PPA paths only; proxies disabled, redirects refused,
  Content-Length/status/encoding/size/hash exact, bounded chunks/time/deadline.
  Only approved compressed DEBs and a head-bound ownership marker are written.
  Directory ownership/mode, finite allowed names, regular single-link files and
  exact marker are verified before exact unlink/rmdir; foreign resources refuse
  cleanup. Decoder kill/reap in finally, runner tracking retained/no credentials.
  Independent always-cleanup reconciles the same exact directory. Unobservable
  or failed cleanup is a blocker, never inferred zero from a failed command.

Receipt `static_inventory_complete:true` requires all three controls/streams,
clean decoder exits and all three roles. `qualified:false`, `extracted:false`,
`executed:false` remain mandatory: static inventory is NOT runtime/native/HTTP
qualification, dependency admission or authority to materialize a private prefix.
No worker/runtime/production code, dependencies, compatibility or guards changed.

## Source-scoped canonical checkpoint

Feature Light: six executable/workflow/test files plus this record and six direct
owning resumes; baseline above covers the coherent foundation, not a docs tail.
Risk: third-party DATA delivery, bounded file/process ownership and hostile input.
One error-recovery issue fixed: selector creation was outside the post-launch
cleanup guard. It is now covered by kill/reap/pipe-finalization, with an isolated
setup-failure regression; no broader policy or resource change was needed.
No UI/REST/AJAX/cron/DB changes. Positive tests require actual synthetic ELF roles
and matching controls; malformed data, missing roles, identity mismatches,
canonical collisions and shared budget exhaustion must refuse. Three semantic
controls are assertion-killed (virtual key, control identity, shared work ledger),
not import/type failures. Windows cleanup mode/uid is isolated API evidence,
not real Unix permissions; Linux real codec/preflight tests are separate.

Bloat/structure Phase1: explicit e75cb828 baseline helper reports one changed PHP
file20 TOTAL/1 CODE, limit300, zero oversized. Helper only measures PHP/JS/CSS,
NOT Python. Manual responsibility review covers DATA parser, bounded codec pipe,
fixed delivery/cleanup owner and synthetic fixture suite; no redundant fallback,
debug remnants, candidate runtime wiring or forced split identified. No edits
required in Phase2; no whole-plugin structure review claimed. Final security
scope checks exact actor/head/tuple, fixed TLS origin/no redirects, resource
accounting, no host-member paths, source loading, privacy-safe refusals, foreign
cleanup refusal and child reaping. No confirmed unresolved security defect;
existing new unit/controlled-codec regressions protect these boundaries.

Not a full release suite, Database Review, physical transaction/authentication
proof or whole-plugin readiness refresh. No UI workflow applies to DATA tooling.
Do not mark unrelated historical checklist rows complete from this checkpoint.

## Validation and ONE proof ledger

Local synthetic13 groups passed, network0; targeted bridge/archive5 tests52
assertions passed without failures/errors/skips. Ordinary Linux CI must also
run the fourteenth real-tool synthetic group (all codec pipes, preflight/caps and
reaping). Windows has no Linux codec execution; this is explicitly reported,
not a skipped candidate qualification. Full QA/publication results and the
actual one-shot receipt belong below and in the canonical toolkit closeout.

Local full PHP3075/25392 passed with two existing Sodium-unavailable skips;
first run failed an unchanged owned-Git fixture because Windows environment
restoration loses empty inherited GIT_CONFIG_VALUE entries. Exact stderr was
`missing config value GIT_CONFIG_VALUE_0`; plain isolated Git initialization
succeeded. Repeating unchanged offline tests with process-local GIT_CONFIG_COUNT=0
passed; no persisted Git/credential/system/test/acceptance change. Final targeted
5/52 revalidated the subsequent selector-reaping regression. Node295/295,no
failures/skips;PHPCS305/305,build,structure,Python AST and356 scoped local links/
whitespace passed. Build produced no tracked runtime changes. Real Linux codec
proof remains pending ordinary CI; no Linux result is inferred from Windows.

Status: implementation pending ordinary exact-head publication/CI; PPA proof
not yet dispatched. No DEB acquired/extracted/installed/executed in local tests.
Product milestones4/10 and native cumulative43 unchanged; earlier static archive
allowances remain consumed. New ONE PPA DATA allowance is separate,not native.

Next action within approval: publish/verify foundation exact-head ordinary CI,
then ONE fixed three-DEB DATA acquisition/inventory/cleanup dispatch. Stop on
unresolved verification/ownership or a need to alter caps/format/trust, materialize
members, install/load/execute candidates or begin native/SQL/site operations.
