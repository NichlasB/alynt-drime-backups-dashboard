# Read-Only Minimum-Profile Windows/VC Dependency and Observer Audit

## Authority, baseline and outcome — 2026-10-29

Approved non-secret, read-only existing-host metadata/source audit after the
[socket-free foundation](SOURCE_POLICY_MINIMUM_PREFLIGHT_SAFETY_FOUNDATION.md).
Clean/synced restore baseline `1efb9854b9f685bbf15ce29cd41d4dea27447389`.
Host discovery is R0; owning repository evidence is documentation only. No
artifact download, installation, candidate-runtime/module loading, signature/
catalog verification call, benign/native process experiment, authentication/SQL,
site inspection, fixtures, permission/credential/configuration/schedule change,
production wiring, release/package/tag or deploy. Existing trusted PowerShell
performs ordinary metadata reads; no inspected DLL export or proof method runs.
No WordPress/LocalWP/live target is selected; no Novamira/site operation applies.

**Audit complete; candidate loading remains blocked.** Required static files and
observer API surfaces exist, but bounded physical observation/cleanup and a
guaranteed-offline provenance-verification route are not established. Do not
convert hashes, owners, product strings or registry entries into verified trust.
No runtime/source/pin/admission change is made, and no automatic fallback runs.

## Exact host and tool observations

OS-derived directory APIs report Windows `C:\Windows`, system directory
`C:\Windows\system32`; directory metadata renders `C:\Windows\System32`.
Both inspected directory objects report Directory without ReparsePoint and owner
NT SERVICE\TrustedInstaller. This is not a complete ancestor/final-path/hardlink/
same-user-race proof. The synthetic helper's case-sensitive canonical path shape
is not silently changed to accommodate these spellings; a physical adapter still
needs an identity-bound path policy rather than blind capitalization.

64-bit OS and 64-bit tool process. CurrentVersion registry reports build 26200,
UBR 9711, DisplayVersion 25H2 and ProductName `Windows 10 Home`. These are literal
registry observations, not a corrected marketing-edition inference. File-resource
versions below differ from that host build; no corruption or compatibility verdict
or repair follows from that difference.

Existing tool: PowerShell Core 7.6.5, GitCommitId 7.6.5, .NET 10.0.11, under the
already configured bundled runtime. Microsoft.PowerShell.Security manifest
version 7.0.0.0 exports Get-AuthenticodeSignature and existing ACL/catalog commands.
Only manifest declarations were read; signatures were not checked or changed.
Selected tooling file correlations (not independently verified provenance):

| File | File version | SHA256 |
| --- | --- | --- |
| pwsh.exe | 7.6.5.500 | `362a356ce7f0940ec74f73a8fc2c990a2cc24a38a11c90bbd8eca947110ad139` |
| System.Management.Automation.dll | 7.6.5.500 | `2d8af026e589c5603629bfc3e7c57265b4167b12eca3ae549c6a6c3ca7e4967b` |
| Microsoft.PowerShell.Security.dll | 7.6.5.500 | `854ee267514c75eb61a84d00a5c8f28442f78143a39c14382f342b6b8adc6a96` |
| System.Diagnostics.Process.dll | 10.0.1126.37416 | `2b4871e02b366113c6ccf3b1436fadde5e69fc0a3e3ac421e2fa0f7af8c2bc3b` |

## Bounded installed-file metadata, not runtime compatibility

Read only the fixed 13 System32 files below: size, attributes, owner, version
resource, SHA256 and bounded DOS/PE header bytes. Per-file 32 MiB/aggregate 256 MiB
ceilings were checked; actual total 17,346,736 bytes. Every file is present,
non-reparse by its inspected attributes, with machine 0x8664/optional-header 0x20b
(x64/PE32+). No entry point, import resolver, DLL function or code image was run.
The metadata pass was rendered again after PowerShell's heterogeneous-table
formatter hid fields; this was another read, not a second physical experiment.

All file resources claim Microsoft Corporation. All owners are
NT SERVICE\TrustedInstaller except vcruntime140.dll, owned by NT AUTHORITY\SYSTEM.
These observations are neither signature verification nor a current-user effective
write-access audit. Hashes remain correlation values, not new allowlist/pin entries.

| System32 file | Bytes | File version | SHA256 |
| --- | ---: | --- | --- |
| advapi32.dll | 795272 | 10.0.26100.3624 | `b864e1468a01a44f1901e03c05c1029e71655dd9abddd6d840bcfff5e7227d44` |
| bcrypt.dll | 195128 | 10.0.26100.1 | `d0fc7863d7b034da33f34dc82b96a8604572598a217668a43fc9eca3a8e2a8e1` |
| crypt32.dll | 1587168 | 10.0.26100.9549 | `71da5f9806f7aaf9e081c1df28b13182d2643b7f3534173f868ba65287612bbb` |
| dnsapi.dll | 1445488 | 10.0.26100.9278 | `c7e1006f7bcbdff6d3668d10b9756a9734c53ccc47783f8908f68b60d0341bbb` |
| imagehlp.dll | 137832 | 10.0.26100.4202 | `58117bbc51c4a56b6c4789e190f33a540b7734e394ada9652e2832e3d2e16588` |
| iphlpapi.dll | 220528 | 10.0.26100.1 | `8521026d021cc5193a7cd7c0256bd14c7ab39738d22c019e6d544b37deee12c0` |
| kernel32.dll | 861992 | 10.0.26100.9549 | `e132eba4166256e2f414b91fe7d5386590bc5ea8626aee5af9c9738383cf2ae1` |
| ole32.dll | 1720576 | 10.0.26100.6725 | `1053ffb858cddbe29eaaa6a5b0836b88c7cd11fbd58605288600e507c06db443` |
| shell32.dll | 8101864 | 10.0.26100.9549 | `c55a5f3b6ef059871fe2a0c8d82f086e0998d1154023e3ecf49698b97f9341f6` |
| vcruntime140.dll | 123472 | 14.50.35719.0 | `184146852727a9db4eea06178716bec3cdbb1015c911f6b0f915b184ad7775b2` |
| ws2_32.dll | 553608 | 10.0.26100.1 | `b123b00c46c415d7baeaada58c29c3e6920895db836ee4c0cfee49d738af60ad` |
| ucrtbase.dll | 1399656 | 10.0.26100.9711 | `dedb7611e1abc1ff3852dcac3d4c93a048d729e25b061973b9969f22698f14ca` |
| apisetschema.dll | 204152 | 10.0.26100.9549 | `568c72f6216d83a716d6fa9bec59ae8d93e6728062d969ac362013f3910c0007` |

Both inspected native/WOW6432Node VisualStudio 14.0 VC\Runtimes\x64 registrations
report Installed 1, Version v14.50.35719.00, Major 14/Minor 50/Bld 35719/Rbld 0. This
agrees with the file version; installation provenance and actual PHP 7.4 ABI
behavior are still unverified. No PHP/LocalWP DLL copy or fallback is selected.

KnownDLLs was read only at its fixed Session Manager key, bounded to 256 values.
Matching value names include advapi32, `*kernel32`, ole32, SHELL32, WS2_32. No
value matched crypt32.dll or vcruntime140.dll in that subset. A missing sampled
registry entry does not prove a loader result or an unsafe path. SafeDllSearchMode
has no explicit value at the inspected key; no effective per-process policy is
inferred from absence. API-set names were not treated as literal DLL filenames,
and apisetschema.dll presence does not establish its complete mapping or closure.

## Offline-verification blocker and observer limits

The [public signature cmdlet](https://learn.microsoft.com/en-us/powershell/module/microsoft.powershell.security/get-authenticodesignature?view=powershell-7.6)
supports file/content inputs and catalog/embedded signatures, but exposes no
cache-only switch. Review of the matching-version
[PowerShell source](https://github.com/PowerShell/PowerShell/blob/v7.6.5/src/System.Management.Automation/security/Authenticode.cs)
shows a catalog-wrapper route and a WinVerifyTrust fallback. The latter's
WINTRUST_DATA initializer does not explicitly set cache-only provider flags.
Microsoft's [WINTRUST_DATA contract](https://learn.microsoft.com/en-us/windows/win32/api/wintrust/ns-wintrust-wintrust_data)
requires WTD_CACHE_ONLY_URL_RETRIEVAL to ensure code-signature verification does
not attempt network retrieval. No verification was invoked to test this limit.

Read-only metadata/IL inspection of the **already loaded** tool corroborates the
fallback structure, without invoking it: GetWinTrustData 261 IL bytes, zero-init,
field stores for cbStruct/UI/state/union/file-or-blob payloads, two native verify
call sites, no dwProvFlags store. GetSignature 33 bytes calls catalog and fallback;
catalog helper 278 bytes calls Microsoft.Security.Extensions.FileSignatureInfo.
One presentation-only Sort-Object typo was corrected and the metadata read
repeated; the empty first field display was not treated as evidence.

The existing catalog type resolves to getfilesiginforedistwrapper, assembly
version 10.0.0.0. Its public GetFromFileStream overload takes only FileStream
(184 IL bytes), with no public offline-policy parameter. Its complete no-network
behavior is not established here. This does not assert that every possible
Windows offline API is impossible; it means no admitted existing-tool route has
been demonstrated under this scope. No private verification method, native
interop bridge, Add-Type/compilation or extra helper is invoked/introduced.
The inspected Windows Kits registry contains no registered SDK root; that is
not an exhaustive filesystem inventory and does not authorize installing one.

Reflection confirms readable Process.Modules, ProcessStartInfo.ArgumentList/
Environment and Stopwatch.StartNew API surfaces. The module getter was not
invoked against a candidate/owned proof process; no held snapshot, bounded
observer latency, process cleanup, stream drain or absence proof is claimed.
API availability and file identity alone do not prove those behaviors. No full
base compiled-module list, DLL redirection/SxS resolution or transient load history
is admitted, and no synthetic unverified flag is changed.

## Disposition, validation and exact next boundary

The meaningful gate is provenance/trust policy, not a missing compiler. Do not
proceed to candidate loading or merely continue maintenance to hide the blocker.
The current design requires review when offline provenance cannot be established;
this audit follows that stop rule and does not weaken it.

Recommended next gated slice: **Design-only Existing-Host Trust Profile and
Bounded Observer Admission Decision**. I recommend evaluating a narrowly named,
operator-approved trust boundary for this controlled Windows/VC installation
instead of adding verification tooling. Explicitly distinguish operator trust
from cryptographically verified offline provenance, bind any proposed exception
to exact files/profile/evidence, and decide whether the existing no-load criteria
must change. This is a new approval boundary: no such trust designation, exception,
guard change or permission to load is granted/applied by this audit. Keep runtime,
network/site/database operations and production wiring disabled. Any later benign
process prerequisite and actual legacy-runtime proof remain separate approvals.

Fresh repository QA (ordinary isolated checks, not a physical proof):

- Minimum-profile family: 157 tests / 7,233 assertions, no failures/skips.
- Full Windows PHP 8.5.1 suite: 2,200 tests / 16,441 assertions, no failures;
  two existing Sodium-unavailable signing tests skipped. This is not an all-tests
  Windows pass or a physical PHP 7.4 compatibility result.
- PHPCS: 242 files; admin: 23/23, no failures/skips. Structure guard: 149 runtime,
  231 support, 219 test files and 216 class-name files; asset build passed.
- Strict Composer validation, Composer audit and npm moderate audit passed;
  zero known advisories/vulnerabilities. No dependency install/update occurred.
- Local documentation targets, whitespace and final scope/security/compatibility/
  error/evidence review passed. Only these owning records change; all non-doc
  tracked files remain identical to the restore baseline, with no generated delta.
  Uploader/toolkit remain clean/read-only at 3dfcb15/c067eb6.

Audit publication `0abe16a53738ff976a8a54a029b950c9e605013b` is pushed;
[CI 40929913571](https://github.com/NichlasB/alynt-drime-backups-dashboard/actions/runs/40929913571)
succeeded for that exact head. Actual PHP 7.4 log: 2,200 tests / 16,443 assertions;
PHP 8.3: 2,200 / 16,448, both without failures/skips. Admin 23/23 each, zero
failures/skips; manifest/audits/build/structure/PHPCS passed, zero known advisories/
vulnerabilities. Setup Node emitted its tool-level `url.parse()` deprecation
warning; no runtime/acceptance change is made to suppress it. This evidence-only
closeout receives its own pushed exact-head ordinary CI and clean/remote check at
handoff; no host/native proof or manual workflow is replayed.

The audit makes no runtime/native/whole-plugin release-readiness or Database Review claim.
Host state, code, source/artifact pins, dependencies, acceptance, schedules and
wiring remain unchanged. No new regression test is warranted for a metadata-only
audit; existing assertions and skips are unchanged.

Implementation-plan progress: actual existing dependency/tool metadata and the
offline/observer evidence gaps are established; the next meaningful step is the
gated host-trust/admission decision, not physical preflight.
