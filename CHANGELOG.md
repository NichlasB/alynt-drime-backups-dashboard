# Changelog

All notable changes to this project will be documented in this file.

## Unreleased

No unreleased changes yet.

## 0.1.4 - 2026-08-13

### Fixed

- Prevented Sites-tab action buttons from overflowing the table on narrower desktop widths.
- Hid revoked duplicate Sites-tab rows when a healthy active enrollment exists for the same client origin, while preserving the stored revoked record for audit/history.

### Tests

- Added focused Sites-list coverage for superseded revoked duplicate filtering.

## 0.1.3 - 2026-08-13

### Fixed

- Clarified Sites-tab manual-check availability for pending, revoked, and missing-credential rows while preserving the read-only polling model.
- Allowed active Sites-list rows with redacted stored polling-secret evidence to show **Check Status Now** without exposing encrypted credential data.

### Tests

- Added focused admin rendering coverage for credential-aware manual-check and next-poll state copy.

## 0.1.2 - 2026-08-13

### Changed

- Expanded the Sites tab to show per-source backup freshness, current package counts, latest backup/package time, and latest upload time at a glance for reported Server and WPvivid backup evidence.
- Polished the narrow Sites table layout so the accessible table caption no longer appears as a cramped visual column in stacked mobile-style rows.

### Tests

- Added focused admin rendering coverage for compact Sites-row backup source evidence and detailed source timestamp output.

## 0.1.1 - 2026-08-12

### Added

- Added dashboard-side consumption of optional redacted `backup_sources` status evidence for validation, classification, admin display, aggregate diagnostics, and protocol documentation while preserving the read-only boundary.
- Added a pending-origin lookup index for enrollment completion queries.

### Changed

- Refreshed the dashboard translation template and release-readiness documentation for the current release candidate.

### Fixed

- Allowed exact same-origin dashboard self-polling when the site hostname resolves to loopback locally, while preserving private-address rejection for other client origins.
- Treated stale or unchanged enrollment completion and local revocation writes as failures instead of reporting success.
- Cleaned enrollment failure-rate-limit transients during uninstall.

### Tests

- Hardened adversarial coverage for stale enrollment and revoke writes, transport response-size limits, and backup-source payload bounds/status enums.

## 0.1.0 - 2026-08-11

### Added

- Created the separate Alynt Drime Backups Dashboard plugin scaffold.
- Added local dashboard storage schema hooks for sites and status snapshots.
- Added an admin page shell documenting the approved v1 read-only boundary.
- Added pairing token helper primitives and initial poller scaffolding, later expanded into scheduled read-only polling.
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
- Added optional structured diagnostics logging with disabled-by-default settings, redacted local event storage, recent event viewing/export, clear controls, and targeted enrollment/polling failure instrumentation.
- Added a WordPress-native responsive admin design with accessible status badges, site summaries, guided pairing, prioritized attention review, bounded recent snapshot history, safer local confirmation screens, and progressive copy/busy-state enhancements.
- Added hook, settings, FAQ, and developer documentation for the read-only dashboard boundary.
