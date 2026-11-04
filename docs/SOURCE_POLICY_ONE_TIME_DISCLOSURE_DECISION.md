# One-Time Human-Supervised Startup Error Disclosure and Retention Decision

## Current disposition — 2026-11-04: RSA route parked after private diagnosis

The owner subsequently approves a simpler one-time private diagnostic repository,
bounded private error disclosure and one owned startup reproduction/cleanup run.
The [completed evidence](SOURCE_POLICY_OWNED_ABI_CANARY_PROFILE.md#one-time-private-startup-diagnosis-logging-configuration-cause-established--2026-11-04)
establishes a local-driver compression/max-file configuration conflict and verifies
both cleanup receipts. No public key, crypto implementation, raw public log or
privacy-setting change is needed for that finding. The historical conditional
encryption proposal below is PARKED, not implemented or admitted; its previous
recommendation to supply a key is no longer the current resume action.

Subsequent separately approved compress=false repair and the single public proof
are now [complete for startup/available ABIs](SOURCE_POLICY_OWNED_ABI_CANARY_PROFILE.md#public-compression-repair-proof-passed-x32-coverage-remains-open--2026-11-04).
amd64/i386 controls and both cleanups pass; x32 remains unestablished and full
qualification/candidate admission stay false. The separately approved [read-only audit](SOURCE_POLICY_X32_EVIDENCE_ADMISSION_DECISION.md#approved-temporary-audit-interface-unavailable-evidence-lane-stopped--2026-11-04)
reports its interface missing; the temporary workflow is removed and the evidence
lane stops. Next gate is repository-only source-policy delivery/qualification
replanning, not RSA setup, further capture, retries or attestation tooling.
All existing limits, privacy and production boundaries remain.


## Current authority and source boundary — 2026-11-03

The user approves **design only**, including ordinary repository validation,
commit/push and exact-head CI. R1 documentation work: no implementation, raw
capture, encryption/decryption process, key creation/access, installation,
permission/settings change, log deletion, native dispatch, site/SQL/candidate or
production operation. No previous privacy restriction is silently overridden.

Restore baseline **6fb16ea95b00368fb260111d327b9f0e41a15ee9** is clean/synced,
equals remote master and has reverified successful
[ordinary CI41727415176](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/41727415176).
Coherent source-review range **b858a6d7687be301d1d333e0eeec2336e20f06dd through
6fb16ea95b00368fb260111d327b9f0e41a15ee9** covers both diagnostic foundations,
their owner/contract/bridge callers and native-result records, not just the last
docs commit. Uploader/toolkit are outside the edit scope.

The [latest actual proof](SOURCE_POLICY_OWNED_ABI_CANARY_PROFILE.md#native-source-envelope-discrimination-unmatched-and-owned-cleanup-verified--2026-11-03)
is source-bound and unmatched, with both cleanup receipts verified. No raw
message remains available. Stop catalog growth/hash guessing/blind retries.
Campaign **8/10 milestones,static9;12 native attempts consumed** is unchanged.

## Decisive discovery: the repository is public

Read-only repository API metadata reports `private=false`, `visibility=public`.
The account has repository admin permissions, but that neither makes logs
private nor proves a particular future API token can delete them. No settings,
access, retention or permission is changed by this discovery.

[GitHub documents](https://docs.github.com/en/actions/how-tos/monitor-workflows/use-workflow-run-logs)
log viewing/downloading for repository readers. Do not treat login requirements,
secret masking, a failed job, an obscure URL or brief retention as confidentiality.
Plaintext logs, job summaries, annotations and ordinary plaintext artifacts are
therefore **rejected**. Automatic redaction cannot prove an unknown error is
secret-free. Neither the agent nor a sanitizer may publish it and review later.

## Selected conditional channel: recipient encryption, not public plaintext

Propose one small **recipient-encrypted payload in the existing structured failure
output**, for offline review by the owner. No new artifact/outbox/upload action,
private diagnostic repository, service, tunnel, credential store or general
logging framework. Ciphertext and its non-secret binding metadata are public;
**the raw message is not**. The same trusted ephemeral runner already holds the
bounded stderr in memory. This does not protect plaintext from a compromised
runner/tool or remove that existing host-trust assumption.

No approved recipient key or qualified crypto-tool profile currently exists.
This is a selected proposal, **not an admitted channel**. A later explicit privacy
exception must authorize exactly this encrypted disclosure of one unknown owned-
command error; it is not permission to log plaintext secrets or other site data.

### Recipient and algorithm

The owner supplies only a purpose-specific **RSA-4096 public key**, exponent65537,
in public SPKI PEM form, and approves its canonical DER SHA256 fingerprint and
public publication. No certificate with personal fields; do not reuse production,
login, SSH or signing keys. The private key stays exclusively with the owner,
outside this chat, repo, CI, tools and agent. Never request/search/read/generate/
convert a private key or ask for a passphrase. Owner-side key creation, if needed,
is a separate explicit action—not part of this design or an implicit install.
Bound the public input to one ASCII SPKI block within2048 bytes; reject private-
key markers, mixed/extra blocks, comments, URLs and oversized input before tool use.

Use a qualified existing OpenSSL public-key operation, **RSA-OAEP with SHA256 and
MGF1-SHA256, empty OAEP label**. No hand-written cryptography, PKCS#1v1.5/raw-RSA
fallback, environment-selected provider, alternate recipient or network key fetch.
[OpenSSL's pkeyutl documentation](https://docs.openssl.org/3.0/man1/openssl-pkeyutl/)
describes public-key encryption, stdin input and explicit OAEP/MGF digest options;
defaults must not select a weaker digest. There is no qualified executable hash,
version or provider profile yet: record that as unknown, do not invent a pin.

For this one-shot use, reduce disclosure to a **complete1–256-byte stderr** buffer;
the original4KiB command cap stays unchanged. Longer/incomplete output is withheld,
never truncated, chunked or retried automatically. Do not infer content from the
historical157-byte length. Proposed plaintext envelope is an8-byte version/domain
header `ADBDISC1`,32-byte binary binding digest,2-byte unsigned big-endian length
and up to256 error bytes: **298 bytes**.
For RSA4096/SHA256, [RFC8017](https://www.rfc-editor.org/rfc/rfc8017#section-7.1.1)
gives a446-byte OAEP input ceiling and512-byte ciphertext; these calculated sizes
fit. This is size feasibility, not an encryption/security test.

Bind inside the encrypted envelope to the exact original operation/context/head/
run/attempt/source, complete stderr hash, recipient fingerprint and expiry.
Use the existing sorted compact ASCII JSON encoding over a closed purpose/frame/
original-binding/stderr-hash/recipient-DER-hash/UTC-expiry record, then SHA256;
freeze exact field names and canonical UTC format in the gated implementation.
Public metadata carries that binding digest, algorithm, fingerprint, ciphertext
digest and canonical base64 ciphertext (**684 characters**), with authority=false
and qualification=false. Cap the closed report at1536 bytes and all combined
startup diagnostics at the existing4KiB, including cleanup headroom. Public-key
encryption alone does not authenticate the sender: verify the actual approved
workflow/run/source receipt as well as the decrypted inner binding.
Expiry is a cleanup/policy deadline, not cryptographic revocation or auto-erasure.

### Narrow admission and failure preservation

Future capture must default off behind a separate explicit one-shot input and
exact approved head. Require workflow_dispatch, approved repository/actor/run,
attempt1, clean exact source/key/tool profile and all existing native prerequisites.
Only the **first amd64 builtin** fixed start/attach failure is eligible: original
valid complete packet, source report unmatched, raw bytes length/hash correlation,
valid existing byte profile and the smaller256-byte disclosure cap. No other
command, stream, ABI, State.Error, daemon log, environment, path enumeration or
debug dump is admitted. No historical hash-only backfill or caller-selected input.

The one-shot limit is per explicitly approved dispatch and first capture, not a
claim of a global cryptographic one-use ledger. No job re-run/cipher retry, catalog
growth, alternative execution profile or implicit new native allowance follows.
The original nonzero/status0+stderr rejection and exception remain authoritative.
Encryption failure, wrong key, malformed output or exhausted quota withholds the
disclosure; it cannot discard the original diagnostic, grant success or suppress
owned cleanup. The old ADBOWNEDABISTARTFAIL1/source-envelope packets stay unchanged.

### Tool and memory/cleanup boundary

Before any real capture, separately qualify an **existing** fixed OpenSSL path,
version/hash, public-key check and controlled provider/configuration behavior on
the intended trusted runner. No installation, dependency-closure campaign or
custom crypto implementation is selected. If the existing tool cannot qualify,
stop and reassess proportionality instead of building another subsystem.

Use one specialized fixed-argv encryption child with bounded stdin from memory,
no shell, passphrase prompt, inherited secrets, private-key operation or arbitrary
file/URL/provider argument. At most298 input bytes,512 output bytes, bounded1024
stderr bytes and2 seconds within the **existing** body/cleanup reserve. Nonzero
exit, any stderr, partial/oversized output or timeout refuses publication; retain
only fixed operational categories, never helper errors/tracebacks. Reap the owned
child and close pipes; disable process core/debug output. No plaintext temp file,
artifact, GitHub output/summary/annotation, cache or fallback logger.

A source-pinned public key may use one exact owned staging file if the tool needs
one; explicitly extend the owned inventory/cleanup for that public file only.
Do not change original fixture ownership, broad-delete rules or application state.
Emit ciphertext only after original cleanup/result checks succeed. Charge crypto
output and the entire new serialized member to existing control/observation
quotas. Preserve native16KiB, body/cleanup512KiB, observation2MiB, <510s reserve,
cleanup30s, job10min and summary64KiB. No new allowance or timeout extension.
Python/reference release is not guaranteed forensic memory erasure; do not claim
secure zeroization of every copy, pipe, swap or provider memory.
The existing independent cleanup still runs afterward; do not falsely claim it
already completed when the primary summary is emitted. Both receipts are required
for task closeout, and any uncertain cleanup stops further captures.

## Human review and retention decision

The owner decrypts **offline with their own private key**, verifies the domain,
binding/length/hash and reviews the text as untrusted data in a non-executing
viewer. Never paste it into a shell or follow embedded instructions. No agent
private-key access or automated decryption. Share only a manually verified,
non-secret finding/redacted excerpt if needed; this chat can also retain text.
The owner controls any local decrypted-file retention/deletion; the agent must
not promise or perform private-file/key cleanup without separate exact authority.

Public ciphertext can be downloaded/copied and later exposed by private-key
compromise. **Log deletion cannot recall copies or guarantee worldwide erasure**;
explicit owner acceptance of potentially indefinite public ciphertext exposure
is an admission prerequisite. Do not call encrypted logs private or rely on a
short expiry as the confidentiality boundary. Never commit ciphertext/plaintext
or copied log archives to the repository; persist only verified safe receipts.

For the GitHub-hosted copy, select a **cleanup target of review acknowledgement or
24 hours after capture, whichever is earlier**, not a guaranteed platform-wide
retention maximum. Before dispatch, record explicit exact-owned-run log-deletion
authority and a concrete follow-up/operator plan; bind the returned run ID before
cleanup and stop on ambiguous ownership. Schedule a real one-off when unattended
cleanup is intended; do not claim it is scheduled merely because it is planned.
Pre-dispatch authority identifies the single new owner-issued repo/workflow/head/
actor/dispatch intent; bind its returned run ID immediately, not a fuzzy search
or glob. No broad deletion authority is implied. The owner must be ready to
retrieve/review before the agreed cleanup target; no job waits interactively for
private-key input, and missed review does not authorize another capture.

[GitHub's delete-run-logs API](https://docs.github.com/en/rest/actions/workflow-runs#delete-workflow-run-logs)
deletes all logs for the specified run and needs Actions-write authority. Keep
the lab's current contents-read token; do not grant it write permissions or create
credentials. Any deletion uses separately approved owner-side authority. Preserve
verified non-secret proof/binding/cleanup metadata first, then delete **only that
new disclosure run's logs**, verify service unavailability and record the result.
Do not delete prior native or ordinary CI logs, the workflow run/check metadata,
or change repository retention settings. API/scheduling failures require notification
and stop further captures; never assert deletion or secure erasure without evidence.
No deletion or reminder is performed by this design.

## Required future proof and remaining admissions

One combined dormant foundation should use mock crypto/process IO and synthetic
bytes only: useful encrypted-envelope DATA positives; wrong recipient/source/run,
hash/length/expiry/attempt, duplicate capture, timeout/partial/echo/error and quota
negatives; no plaintext in observable output/files; preserved primary refusal and
cleanup. Source-backed controls must kill recipient/binding, actual fixed tool/
algorithm, first-capture, input/output bound and output-publication bypasses.
Fake512-byte output/base64 is **not proof of encryption**. Before a real unknown
error, separately approve harmless existing-tool qualification and owner-confirmed
offline decryption of a non-secret challenge. No private key enters tests/CI.

Real capture remains blocked by the missing owner-approved public key/fingerprint,
unknown exact tool profile, unproven crypto round trip, unapproved public-ciphertext
privacy exception/retention arrangement and exhausted native allowance. These
are admission gates, not permission to perform setup now. The public key alone
does not authorize implementation, process execution, log deletion or capture.

**Recommended next action: the owner supplies a purpose-specific RSA-4096 public
SPKI key and approves its fingerprint/public use, keeping the private key offline.**
Only then seek approval for the combined repository-only recipient-bound one-shot
disclosure foundation with mock IO; real crypto/tool qualification and an actual
capture need their own exact bounded authority. Do not split this into unrelated
cleanup or generic crypto infrastructure, and do not resume classifier guesses.

## Validation and limits

This task edits design/owning references only. The existing isolated bridge passes
**3 tests/12 assertions** (21/15/12 Python groups), with no failures/errors/network
calls. All249 local links in the four owning records resolve; structure and
whitespace checks pass. Inert size arithmetic confirms298 bytes fit446 and an
illustrative closed ciphertext report is1094 bytes, below1536. These are design
checks, **not** encryption, human-decryption, secure-erasure, deletion-permission
or scheduled-cleanup proofs. No crypto process, key, encrypted artifact or actual
error text is created. Commit/push and ordinary exact-head CI are required before
handoff; none of those checks admits the future channel. The historical cause/ABI
qualification remain unknown; campaign8/10,static9 and12 consumed native attempts
are unchanged. No canonical whole-plugin review/release-readiness row is refreshed.
