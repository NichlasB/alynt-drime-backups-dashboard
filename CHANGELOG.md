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
