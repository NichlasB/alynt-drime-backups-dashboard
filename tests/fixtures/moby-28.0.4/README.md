# Immutable policy DATA fixture

`default.json.b64` losslessly encodes the unmodified 12,828-byte Moby policy at
commit `6430e49a55babd9b8f4d08e70ecb2b68900770fe`
([official source](https://github.com/moby/moby/blob/6430e49a55babd9b8f4d08e70ecb2b68900770fe/profiles/seccomp/default.json)).

Decoded SHA256: `9c1025c88ccaa517b648da571961838744ea2137f176bfe6a48b21294cae9c76`.
The original has no final newline. Base64 preserves the exact octets across
Windows/Linux checkouts; tests do not normalize or override the decoded pin.
No live test download, Go execution, filter application or runtime admission.

Upstream Apache-2.0 LICENSE and NOTICE are retained unmodified alongside the
fixture. This is test-only source DATA, not a runtime dependency or plugin asset.
The existing tests exclusion from release packaging remains unchanged.

`owned-abi-readback-scope.json` is a **source-schema subset, not a captured native
inspect response**. It records the canonical `HostConfig.UTSMode` spelling from
the same commit's `api/types/container/hostconfig.go`, and explicit versus implicit
AppArmor request forms reviewed in `daemon/daemon_unix.go` and `daemon/oci_linux.go`.
It prevents the test double from inventing a differently cased namespace key.
The explicit case includes exactly NNP, selected seccomp and existing
`apparmor=docker-default`; the implicit case is deliberately rejected by this
owned-canary contract. These cases do not establish actual kernel/LSM enforcement.
