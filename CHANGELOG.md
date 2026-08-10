# Changelog

## 0.1.0 - Unreleased

- Created the separate Alynt Drime Backups Dashboard plugin scaffold.
- Added local dashboard storage schema hooks for sites and status snapshots.
- Added an admin page shell documenting the approved v1 read-only boundary.
- Added pairing token helper primitives and a no-op poller placeholder.
- Added implementation plan, package metadata, and development tooling placeholders.
- Added draft v1 protocol and threat-model documents for the read-only dashboard/uploader boundary.
- Froze the Phase 3 protocol and threat-model documents as the approved implementation baseline.
- Added dashboard-side pending enrollment creation, protocol-v1 pairing-token generation, public-HTTPS origin validation, and local revocation scaffolding while keeping polling and REST enrollment disabled.
- Added credential-vault encryption/decryption and safe status-request preparation foundations without enabling REST enrollment or outbound polling.
- Added the protocol-v1 REST enrollment endpoint for authenticated client opt-in completion, encrypted polling-credential storage, and display-once credential return while still requiring a later first-poll activation slice.
- Added schema-1 status payload validation, first-poll activation, snapshot recording, and manual **Check Status Now** through the fixed read-only client status route.
- Added scheduled read-only polling with bounded batches, global/per-site locks, deterministic jitter, retry backoff, and 30-day snapshot retention cleanup.
- Added a redacted admin Diagnostics tab for scheduler state, retention defaults, polling counts, and recent safe poll outcomes.
- Added operator UI polish for Sites, Attention, Site Detail, latest redacted snapshot summaries, accessible status guidance, and support-copy diagnostics.
