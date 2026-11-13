# Private-Producer HTTP Envelope and Pre-Router Resource Admission Decision

## Approved decision — 2026-11-13

The user explicitly selected **functional HTTP qualification only, with no
pre-router containment claim**, preserving budgets and guards. This resolves the
assurance-scope question in the [source audit](SOURCE_POLICY_GENUINE_HTTP_PREROUTER_AUDIT_STOP.md);
it does not repair or remove PHP's pre-router buffering. The approved boundary
is the existing private owned CI client/server, not a public ingress service.

Repository-only R1 design/documentation about future R3 qualification. Baseline
`8c60fc760b6c9c990629e07e8093b4c6b3f67f8b` was clean and remote-equal;
[CI42478938136](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/42478938136)
reverified actually completed/success. Toolkit baseline
`36e4373d90cbeac867145500208fcc4bf9e4dc52` clean/remote-equal; uploader remains
untouched at `3dfcb15b5cab8191df7d984251986ead140ded52`. Git preserves the full
pre-edit records. No code, tests, workflow, source pins, dependencies, public
contracts or production wiring change. No new runtime/server/toolchain selected.

## Selected assurance and explicit nonclaims

Keep `owned-http-l714-v1`, the same pinned core/theme/runtime, fixed loopback
origin and single owned worker. The future proof must establish genuine stock
HTTP login/session/nonce flow and the unchanged guarded view/save/no-op/conflict
and denial behavior, with physical owned-storage/release/cleanup evidence.
Neither successful HTTP status nor an always-unavailable owner qualifies.

The trusted finite producer must bound bytes BEFORE transmission. The router
must independently validate the observed request before core/owner dispatch.
These are separate checks: model-supplied counts, challenges, callback labels,
loopback binding or PHP INI readback alone cannot prove actual wire behavior,
principal isolation or physical resource containment.

Explicitly NOT claimed: hostile-client resistance at C ingress, a pre-router
allocation/RSS ceiling, absence of C buffering, kernel-wide memory/egress
isolation, protection against a malicious co-resident runner process, public
production-server suitability, installed-site eligibility or production readiness.
The earlier immutable-source finding remains valid; no new native observation
or compiled-binary attestation is asserted by this decision.

## Budget interpretation — values and guards unchanged

| Existing ceiling | Meaning for this functional profile |
| --- | --- |
| Request body16384 bytes / headers32768 bytes | Actual private producer-emitted encoded body/header bytes and independently checked router input; not a pre-router C-allocation bound. Responses retain the same32768-byte header ceiling. |
| Response65536 bytes / retained diagnostics65536 bytes | Incrementally bounded receiver output and aggregate private-safe retained diagnostics; raw cookies, credentials, nonces, SQL or PHP diagnostics never become evidence. |
| Memory67108864 bytes | Unchanged effective PHP/WP memory settings; not a64MiB whole-process RSS or persistent-C-allocation attestation. Unknown effective-setting drift still refuses. |
| Job600000ms / setup60000ms / HTTP60000ms / cleanup30000ms | Original monotonic phase/job deadlines and cleanup reserve, never refreshed on retry or release. |
| Startup/transaction5000ms / maximum64 transactions | Original shared request/reconciliation credit and bounded startup; existing25 exchanges/13 logical cases, no extra native credit. |
| Setup SQL1024 / per-request bootstrap256 and auth256 | Separate bounded owned effect ledgers, not blanket permission for unknown writes. Existing native SQL/session/initialization limits unchanged. |
| Source8192 files / aggregate201326592 bytes / per-file16777216 bytes | Existing exact complete-source staging ceilings and manifest validation; no new pins, paths or learned acceptance. |

Values are read from unchanged [profile DATA](../scripts/ci/source-policy-http-profile.mjs)
and [lifecycle guards](../scripts/ci/source-policy-http-lifecycle.mjs). The
[request/response contract](../scripts/ci/source-policy-http-contract.mjs) and
[supervisor](../scripts/ci/source-policy-http-supervisor.mjs) remain unchanged.
This table is an approved assurance clarification, not measured throughput or
permission to increase limits when genuine bootstrap does not fit them.

## Future integration admission obligations

1. Build only fixed finite requests from bounded fixture fields/private cookie
   jars. Bound construction as well as final buffers; account for encoding
   expansion and every emitted header, including cookies/framing. Independently
   derive actual byte counts from final buffers, compare with the unchanged
   route contract, then send those same buffers. Never trust supplied lengths or
   let the client silently append unaccounted headers/redirects/retries.
2. Keep exact Host/origin/method/path/challenge/content type and framing rules.
   POST Content-Length equals the actual encoded body byte count; no transfer
   encoding, ambiguous/duplicate framing or alternate route delegation. Preserve
   malformed form-type denial cases; do not repair intent/nonce/revision DATA.
3. Router-side bounded reads and validation still gate dispatch; they are defense
   in depth, not retroactive allocation prevention. Unknown/mismatched/truncated
   observations stop. Do not send oversized hostile-wire experiments to claim
   this functional profile proves a guarantee explicitly excluded above.
4. Pre-send refusals must produce zero network sends in isolated IO tests.
   Required byte/framing/call-site guards need non-vacuous controls. Actual
   qualification must bind observations to the same emitted buffers and owned
   server, not reuse a synthetic transcript as physical proof. Keep the positive
   save/unchanged/stale and genuine wrong-action/user/session oracles intact.
5. Preserve installer hook/HTTP-denial/soft-flush containment, exact source and
   genuine guard provenance, fixed secrets-only-private handling, full-response
   EOF, live all-server session/transaction reconciliation and exact owned cleanup.
   Retain original error stage if cleanup also fails. Ownership/cleanup unknown
   stops; do not kill a foreign listener or replay an uncertain write.

No new hostile-client harness, proxy, FPM server, dependency, firewall policy,
attestation tooling or system installation is needed for the selected claim.
If the fixed private-producer approach cannot satisfy its unchanged limits,
report the exact evidence; this decision does not authorize an alternative.

## Focused review, proof limits and resume

Canonical source-scoped Feature Light: eight changed Markdown records and the
directly referenced immutable budget/request/lifecycle/supervisor call sites.
Risk: Other, future HTTP/resource effects. Prior assurance ambiguity resolved by
explicit user choice; zero runtime fixes. Two specification traps clarified:
final-size validation must also bound construction, and library-added bytes must
not escape wire accounting. No outstanding decision within this selected scope.
Bloat/Structure unnecessary: documentation only, no runtime split/architecture or
duplicate implementation. No UI/UX workflow or whole-plugin readiness refresh.

Final focused Security: the design retains independent authorization/nonce/
source/framing/cleanup requirements and limits its claim explicitly; no new
executable defect or secret-bearing output introduced. Regression handoff:
pre-send/call-site controls and actual HTTP observations are future integration
obligations, not implemented coverage. Existing isolated tests are not genuine
authentication/storage/HTTP proof or Database Review. Ordinary CI cannot prove
pre-router containment, which this profile intentionally does not claim.

Next meaningful work: **Genuine-HTTP Owned Bootstrap/Request IO Integration with
Installer Containment and Private-Producer Envelope**, audit-first, followed by
fresh prerequisites and the held ONE exact-head ephemeral-CI qualification/
cleanup attempt within existing authority and remaining campaign limits. This
repository-only slice itself performs no acquisition, native dispatch, actual
authentication/storage/SQL/fixtures or production operation. The assurance gate
is resolved; do not request the same design approval again. A genuinely new
boundary or contradictory source/runtime evidence must still stop execution.

Implementation-plan progress: unblocks the specific functional-only resource
admission decision; implementation and physical HTTP proof remain pending.
Campaign4/10 milestones,8/10 native attempts,cumulative31 unchanged. ONE held
attempt unused; documentation/decision closeout is not a new product milestone.
Fresh local `node --test tests/node/source-policy-http-*.test.mjs` passed47/47,
failures/skips0, with the explicit existing PHP/Python paths. All423 relative
local links across the eight changed Markdown records resolve; whitespace checks
pass. Final source-scoped light/security review found no remaining contradiction
within the selected functional-only assurance. No executable files changed.
Exact-head publication evidence follows in the owning toolkit's source-scoped
record; no earlier whole-plugin result is refreshed.
