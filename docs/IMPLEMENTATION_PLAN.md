# Alynt Drime Backups Dashboard Implementation Plan

This document is the implementation plan for the separate **Alynt Drime Backups Dashboard** WordPress plugin.

This is now the canonical implementation plan for the dashboard repository. The earlier uploader-side copy remains historical context only and does not make the dashboard part of Alynt Drime Backups Uploader.

Phase 3 protocol details are tracked in `docs/PROTOCOL_V1.md` and `docs/THREAT_MODEL_V1.md`. V2.1 action-request protocol details are tracked in `docs/PROTOCOL_V2.md` and `docs/THREAT_MODEL_V2.md`.

Future remote-operation planning is tracked separately in `docs/V2_REMOTE_ACTIONS_PLAN.md`. The first V2.1 design artifact is tracked in `docs/V2_1_REQUEST_BACKUP_NOW_DESIGN.md`, signed dispatch implementation planning is tracked in `docs/V2_1_SIGNED_DISPATCH_IMPLEMENTATION_PLAN.md`, V2.2 action-history/audit hardening is tracked in `docs/V2_2_REMOTE_ACTION_HISTORY_AUDIT_PLAN.md`, V2.3 schedule-management design is tracked in `docs/V2_3_SCHEDULE_MANAGEMENT_DESIGN.md`, V2.3 preview-only capability implementation planning is tracked in `docs/V2_3_PREVIEW_ONLY_IMPLEMENTATION_PLAN.md`, the non-mutating V2.3 schedule-preview action implementation is tracked in `docs/V2_3_SCHEDULE_PREVIEW_IMPLEMENTATION_PLAN.md`, guarded V2.3 schedule-apply implementation is tracked in `docs/V2_3_SCHEDULE_APPLY_IMPLEMENTATION_PLAN.md`, rollback-readiness metadata capture is tracked in `docs/V2_3_ROLLBACK_METADATA_CAPTURE_PLAN.md`, the planning-only schedule rollback readiness gate is tracked in `docs/V2_3_SCHEDULE_ROLLBACK_READINESS_PLAN.md`, the non-mutating rollback-preview design is tracked in `docs/V2_3_SCHEDULE_ROLLBACK_PREVIEW_DESIGN.md`, the planning-only schedule rollback execution decision is tracked in `docs/V2_3_SCHEDULE_ROLLBACK_DECISION.md`, the design-only V2.4 cleanup/retention decision record is tracked in `docs/V2_4_CLEANUP_RETENTION_DESIGN.md`, the V2.4 cleanup-preview implementation plan is tracked in `docs/V2_4_CLEANUP_PREVIEW_IMPLEMENTATION_PLAN.md`, the V2.4 cleanup-apply decision is tracked in `docs/V2_4_CLEANUP_APPLY_DECISION.md`, and the V2.6 restore-preparation evidence design is tracked in `docs/V2_6_RESTORE_PREPARATION_EVIDENCE_DESIGN.md`. Dashboard-local retained-record removal planning is tracked in `docs/LOCAL_RECORD_REMOVAL_DESIGN.md`. These documents do not change the v1 read-only contract; they exist to keep backup execution, restore, cleanup, settings mutation, credential rotation, permanent dashboard-record removal, and other remote-control or destructive concepts out of the v1 acceptance boundary until a separate protocol, design, and approval gate are satisfied.

## Current State And Safety Boundary

- Planning status: v1 read-only dashboard is implemented, released, and deployed for operational monitoring. V2.1 Request Backup Now, V2.2 dashboard-side action-history reconciliation/audit hardening, V2.3 schedule visibility/preview/apply for the Alynt scan/upload cadence, dashboard-side non-mutating Schedule Rollback Preview controls, V2.4 non-mutating Cleanup Preview controls, and the V2.6 dashboard-side Restore Readiness Evidence Consumer have been implemented, released, and deployed to the dashboard host. V2.4 latest cleanup-preview evidence display is released and deployed through dashboard `0.1.57`; Site Detail now shows the latest sanitized preview summary when reported while preserving the preview-only cleanup boundary. V2.6 sanitizes and displays optional read-only `restore_readiness` status evidence without restore controls; the Site Detail panel, compact Sites-row hints, Diagnostics summary row, and source-level Server/WPvivid aggregate counts are released and deployed through dashboard `0.1.55` and retained in later release lines. Diagnostics Support Summary download/export is released and deployed through dashboard `0.1.58`; post-release monitoring on `control-sitesmanage` confirmed dashboard `0.1.58`, healthy scheduled polling, 14 active Working records, 0 active Attention records, no polling failures, and a successful browser-side redacted support-summary download. Dashboard `0.1.60` was released and deployed on `control-sitesmanage`; post-deploy scheduled-poll verification confirmed the poll cron advanced normally, 14 active Working records, 0 active Needs Attention records, 0 active consecutive-failure records, and fresh snapshots after the normal polling window. Dashboard `0.1.61` was released and deployed on `control-sitesmanage`; post-update scheduled-poll verification confirmed the live plugin remained active at `0.1.61`, the dashboard polling cron advanced normally, all 14 active dashboard records were Working, zero active records needed attention, zero active records had consecutive polling failures, and fresh snapshots were recorded after the normal polling window. Dashboard `0.1.62` is released and deployed on `control-sitesmanage`; post-deploy scheduled-poll verification confirmed the live plugin remained active at `0.1.62`, the dashboard polling cron advanced normally, all 14 active dashboard records were Working, zero active records had consecutive polling failures, and fresh snapshots were recorded after the normal polling window. The companion uploader producer was released and rolled out through uploader `0.5.25`; dashboard scheduled polling now receives sanitized `restore_readiness` evidence from the active tracked client set. The restore-readiness visibility polish line is complete for the current evidence model; any next restore-adjacent work must be a separate approved planning slice and must not add restore actions, restore-preparation runtime behavior, or imply restore guarantees. V2.1 has been broadly proven across active enrolled client rows; dashboard-host self-action proof required a same-origin safety patch because managed-host DNS can resolve the dashboard's own public hostname to loopback/private addresses from the dashboard server. Cleanup Preview has been proven as preview-only across the active dashboard client set with no cleanup apply, Drime mutation, backup deletion, restore, arbitrary filesystem browsing, dashboard Drime credentials, or path exposure.
- Dashboard repository: created locally at `C:\Development\WordPress\Plugins\alynt-drime-backups-dashboard`.
- Dashboard plugin files: implemented and released through GitHub release assets.
- Dashboard pending-enrollment token generation: implemented.
- Uploader dashboard endpoint: implemented in the companion uploader plugin and enabled only after explicit client-site opt-in.
- Current host: `control-sitesmanage live-only`.
- Live rollout state: dashboard `0.1.65` is deployed to `https://control.sitesmanage.com` after explicit approval and a fresh restore point. Dashboard `0.1.66`, `0.1.67`, `0.1.68`, `0.1.69`, `0.1.70`, `0.1.71`, and `0.1.72` are published GitHub release assets but have not been deployed; updater acceptance, live deployment, and post-deploy monitoring remain separate approval-gated workflows.
- Version 1 is read-only relative to client sites and Drime. It may create and update its own dashboard registry, polling credentials, status history, and schedules, but it must not change client settings, create or delete backups, restore data, clean up files, or mutate Drime.
- Dashboard-local operator action history is allowed in v1 because it records only dashboard-owned actions and redacted context. It does not grant remote-action capability.
- V2.1 Request Backup Now has an opt-in token foundation, signed dashboard dispatch, and client action-intent endpoint implemented and released. The first action remains `scan_upload_now`, meaning the client scans for ready backup packages and uploads eligible items using its own existing settings. Fresh WPvivid or server-runner backup creation remains deferred until a client declares and proves a separate safe local capability.
- V2.2 remote-action history/audit hardening is implemented, released, and deployed. It hardens dashboard/client reconciliation, stale-action evidence, Site Detail action history, compact Sites-row action hints, Diagnostics aggregates, and support-safe export fields before any V2.3+ higher-risk action class.
- V2.3 schedule management remains a higher-risk gated phase because it can change persistent client backup behavior. The preview-only `alynt_scan_upload` schedule capability slice and non-mutating signed `schedule_preview` action are implemented, released, and deployed through dashboard `0.1.22` and uploader `0.5.18`. The guarded `schedule_apply` slice for `alynt_scan_upload` cadence changes only is implemented, released, and deployed through dashboard `0.1.25` and uploader `0.5.19`, with the client-side Schedule Apply policy still disabled by default and enabled only per explicitly approved client site. Display-only Sites-row schedule hints distinguish preview-only and apply-gated clients without adding row-level controls. Rollback-readiness metadata capture/display is implemented and proven on the `purecleanse.net` pilot as evidence-only. Dashboard-side non-mutating `schedule_rollback_preview` dispatch/UI controls, audit labels, action-history summaries, and Diagnostics support aggregates are released and deployed through dashboard `0.1.43`, but remain hidden unless a latest client capability report explicitly advertises rollback-preview support. One PureCleanse rollback-preview pilot was completed on 2026-09-30 with temporary client-local opt-in, a guarded `every_15_minutes -> every_30_minutes` apply, non-mutating rollback preview evidence, restoration to `every_15_minutes`, and opt-in disabled again. Broad rollback-preview enablement and any mutating `schedule_rollback` runtime behavior remain unavailable without separate approval gates.
- A follow-up dashboard self-action safety patch allows exact same-origin V2.1 action dispatch when the enrolled client origin equals the dashboard's own normalized public HTTPS origin and managed-host DNS resolves that origin to loopback/private addresses. Public-IP enforcement remains required for every non-same-origin client action destination.

## Dashboard 0.1.72 Release Record

Dashboard `0.1.72` was released as a tiny Diagnostics Support Copy clarity patch. It adds display-only Support Copy help text explaining that remote-action boundary evidence is informational only, does not grant new client actions, and does not store Drime API credentials in the dashboard. It does not introduce protocol, database, remote-action capability, action dispatch, backup, restore, cleanup/delete, schedule apply/rollback, credential, Drime, live-site, or deployment behavior changes.

Commit scope since `0.1.71`:

- `649ce1a` — record dashboard release `0.1.71`;
- `6ebea0a` — add display-only Diagnostics Support Copy boundary explanation;
- `ef1c5df` — dashboard `0.1.72` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.72` in the plugin header, version constant, package metadata, readme stable tag, readme changelog, and translation template metadata;
- GitHub tag and release: `v0.1.72`;
- release asset: `alynt-drime-backups-dashboard-0.1.72.zip`;
- release asset SHA256: `642794982541b7cbd1d2783a602883f3c269e98b2087e7c502c6c66612869ef8`;
- CI passed for release-prep commit `ef1c5df` in run `37845118461`;
- Build Release run `37845280653` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.72`, and packaged PHP syntax passed for 136 PHP files.
- no protocol behavior, database schema, remote-action capability, action dispatch behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this patch line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.71 Release Record

Dashboard `0.1.71` was released as a small Diagnostics clarity and test-support maintenance patch. It clarifies remote-action capability gating in the Diagnostics Runtime panel, adds support-safe boundary evidence to the Diagnostics Support Summary export, and splits rollback-preview capability fixture helpers without introducing protocol, database, remote-action capability, action dispatch, backup, restore, cleanup/delete, credential, Drime, live-site, or deployment behavior changes.

Commit scope since `0.1.70`:

- `e174581` — record dashboard release `0.1.70`;
- `697b129` — split rollback-preview capability fixture helpers into a focused test-support trait;
- `9dec870` — add display-only Diagnostics Runtime capability-gate copy;
- `7b9f59d` — add support-safe remote-action boundary evidence to the Diagnostics Support Summary export;
- `c0bd7ae` — dashboard `0.1.71` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.71` in the plugin header, version constant, package metadata, readme stable tag, readme changelog, and translation template metadata;
- GitHub tag and release: `v0.1.71`;
- release asset: `alynt-drime-backups-dashboard-0.1.71.zip`;
- release asset SHA256: `1dfa47a0d1ffdcd1ef19a90f3bfaf4fc0d05b6095dee382a2a8bfdc6c019a548`;
- CI passed for release-prep commit `c0bd7ae` in run `37843574378`;
- Build Release run `37843740431` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.71`, and packaged PHP syntax passed for 136 PHP files.
- no protocol behavior, database schema, remote-action capability, action dispatch behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this patch line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.70 Release Record

Dashboard `0.1.70` was released as a small Diagnostics UI visibility and test-support maintenance patch. It adds display-only Diagnostics Runtime boundary copy and splits remote-action capability fixture helpers without introducing protocol, database, remote-action capability, backup, restore, cleanup/delete, credential, Drime, live-site, or deployment behavior changes.

Commit scope since `0.1.69`:

- `f18e429` — record dashboard release `0.1.69`;
- `6a8c54e` — split remote-action capability cleanup and schedule fixture helpers into focused test-support traits;
- `71341ab` — add display-only Diagnostics Runtime remote-action boundary copy;
- `e5e119f` — dashboard `0.1.70` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.70` in the plugin header, version constant, package metadata, readme stable tag, readme changelog, and translation template metadata;
- GitHub tag and release: `v0.1.70`;
- release asset: `alynt-drime-backups-dashboard-0.1.70.zip`;
- release asset SHA256: `3b38f7cd212e64fc83ccc74a104d9c4f100579386470852a77cce02749cdd9e2`;
- CI passed for release-prep commit `e5e119f` in run `37837004764`;
- Build Release run `37837228083` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.70`, and packaged PHP syntax passed for 136 PHP files.
- no protocol behavior, database schema, remote-action capability, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this patch line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.69 Release Record

Dashboard `0.1.69` was released as a maintenance-only test-support fixture cleanup patch. It does not introduce runtime behavior, UI output, protocol, database schema, remote-action permission, backup, restore, cleanup/delete, credential, Drime, live-site, or deployment behavior changes.

Commit scope since `0.1.68`:

- `398160d` — record dashboard release `0.1.68`;
- `d76c36c` — split cleanup diagnostics fixtures;
- `284a1d3` — split local removal diagnostics fixtures;
- `3e3c756` — split remote-action capability fixtures;
- `d917267` — split cleanup capability fixtures;
- `ef7effe` — split rollback capability fixtures;
- `dd1859d` — split schedule apply capability fixtures;
- `39e1548` — split schedule lookup repository fixtures;
- `c013b2e` — split client reconciliation fixtures;
- `abf5dc5` — split support summary fixtures;
- `497f282` — split schedule result fixtures;
- `35bb058` — dashboard `0.1.69` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.69` in the plugin header, version constant, package metadata, readme stable tag, readme changelog, and translation template metadata;
- GitHub tag and release: `v0.1.69`;
- release asset: `alynt-drime-backups-dashboard-0.1.69.zip`;
- release asset SHA256: `a854f2c46f5a46ed80e57984bbe5765e847d00d450b8b26acf5bd87f5e126a45`;
- CI passed for release-prep commit `35bb058` in run `37831189593`;
- Build Release run `37831352377` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.69`, and packaged PHP syntax passed for 136 PHP files.
- no production UI output, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this maintenance line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.68 Release Record

Dashboard `0.1.68` was released as a maintenance-only diagnostics test-support fixture cleanup patch. It does not introduce runtime behavior, UI output, protocol, database schema, remote-action permission, backup, restore, cleanup/delete, credential, Drime, live-site, or deployment behavior changes.

Commit scope since `0.1.67`:

- `8d486e5` — record dashboard release `0.1.67`;
- `8591009` — split diagnostics overview count fixtures;
- `5c6222b` — split restore-readiness diagnostics fixtures;
- `2bfeee8` — split schedule diagnostics fixtures;
- `d3a0f7e` — dashboard `0.1.68` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.68` in the plugin header, version constant, package metadata, readme stable tag, readme changelog, and translation template metadata;
- GitHub tag and release: `v0.1.68`;
- release asset: `alynt-drime-backups-dashboard-0.1.68.zip`;
- release asset SHA256: `1646c036d2a4f32db93c80e523e972cb6b90053ee82de83d9d6d1733b3ec8bfc`;
- CI passed for release-prep commit `d3a0f7e` in run `37816058264`;
- Build Release run `37816089948` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.68`, and packaged PHP syntax passed for 136 PHP files.
- no production UI output, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this maintenance line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.67 Release Record

Dashboard `0.1.67` was released as a maintenance-only test-support structure patch. It does not introduce runtime behavior, UI output, protocol, database schema, remote-action permission, backup, restore, cleanup/delete, credential, Drime, live-site, or deployment behavior changes.

Commit scope since `0.1.66`:

- `1cfeab0` — record dashboard release `0.1.66`;
- `f0d736d` — split diagnostics overview service stub;
- `bc72d4f` — split diagnostics overview harness;
- `4743afc` — split enrollment transient shims;
- `d0e9bd0` — split admin action handler harness;
- `254f545` — split action audit WordPress shims;
- `36305cd` — split diagnostics support harness;
- `8ed3464` — split dispatcher snapshot fixtures;
- `9b0570e` — split schedule rendering fixtures;
- `99f1178` — split poller payload fixtures;
- `3006354` — split reconciler action repository double;
- `c6b65ef` — split dispatcher schedule lookups;
- `b46697b` — split schedule management payload fixtures;
- `63ef8c9` — split polling rendering helper stubs;
- `74036e1` — split diagnostics snapshot repository;
- `0bda646` — split enrollment request fixtures;
- `1d6f67d` — split action audit doubles;
- `777a297` — split uninstall `wpdb` double;
- `2fe4b53` — dashboard `0.1.67` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.67` in the plugin header, version constant, package metadata, readme stable tag, readme changelog, and translation template metadata;
- GitHub tag and release: `v0.1.67`;
- release asset: `alynt-drime-backups-dashboard-0.1.67.zip`;
- release asset SHA256: `847525e52cb36b2ded6f8b9b15709c70dfeb9f8f4d52d34022590b34c68b2578`;
- CI passed for release-prep commit `2fe4b53` in run `37793976086`;
- Build Release run `37794005085` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.67`, and packaged PHP syntax passed for 136 PHP files.
- no production UI output, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this maintenance line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.66 Release Record

Dashboard `0.1.66` was released as a maintenance-only docs/test-support structure patch. It does not introduce runtime behavior, UI output, protocol, database schema, remote-action permission, backup, restore, cleanup/delete, credential, Drime, live-site, or deployment behavior changes.

Commit scope since deployed `0.1.65`:

- `72f13b0` — record dashboard release `0.1.65`;
- `ea65f43` — split Sites-list test doubles;
- `9d34797` — sync dashboard `0.1.65` deployment state;
- `79449c6` — split site repository fake `wpdb` support;
- `df63628` — split admin-action WordPress shims;
- `e9f2c3d` — split polling-state remote-action render helpers;
- `a55c721` — split diagnostics remote-action repository double;
- `98b2d3b` — split WordPress formatting shims;
- `8a4112d` — split poller repository write helpers;
- `12adcbe` — split remote-action fake `wpdb` query helpers;
- `66883cb` — split WordPress admin shims.
- `c300e40` — plan dashboard `0.1.66` patch release;
- `bac2b26` — dashboard `0.1.66` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.66` in the plugin header, version constant, package metadata, readme stable tag, changelog, and translation template metadata;
- GitHub tag and release: `v0.1.66`;
- release asset: `alynt-drime-backups-dashboard-0.1.66.zip`;
- release asset SHA256: `8d1ab2a193a698a48ae30d7b2881ef710cab4e0691d6718c2ca7df6defa373af`;
- CI passed for release-prep commit `bac2b26`;
- Build Release run `37626551416` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.66`, and packaged PHP syntax passed for 136 PHP files.
- no production UI output, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this maintenance line;
- updater acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.65 Release Record

Dashboard `0.1.65` was released as a maintenance-only structure/test-support patch:

- `7fa308b` — Site Detail local-removal preview helper split;
- `f1634d2` — polling-state local-record rendering method split;
- `02988c5` — local-removal diagnostics coverage split;
- `3a70ea6` — schedule-management fake action repository split;
- `510caa4` — dashboard `0.1.65` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.65` in the plugin header, version constant, package metadata, readme stable tag, changelog, and translation template metadata;
- GitHub tag and release: `v0.1.65`;
- release asset: `alynt-drime-backups-dashboard-0.1.65.zip`;
- release asset SHA256: `aa2681d5e110367145ec312eb97e608574ef115f4e9e5dce9e91798b6c4539d6`;
- CI passed for release-prep commit `510caa4`;
- Build Release run `37590368667` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 141 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.65`, and packaged PHP syntax passed for 136 PHP files.
- no production UI output, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior is introduced by this maintenance line;
- `control-sitesmanage` deployment was completed after explicit approval and a fresh restore point; live verification confirmed the active plugin remained `0.1.65`, updater status reported no newer dashboard update, the dashboard polling cron advanced normally, and support-safe aggregate health remained stable.

## Dashboard 0.1.64 Release Record

Dashboard `0.1.64` was released as a maintenance-only docs/test-support patch:

- `0d75607` — remote-action repository client-report fixture helper split;
- `c1f9f4c` — Site Detail local-removal preview rendering test split;
- `fffedd1` — Diagnostics overview aggregate fixture helper split;
- `b7af6b8` — local-removal readiness implementation-plan reconciliation;
- `4073f2d` — dashboard `0.1.64` version and release metadata update.

Release evidence:

- release candidate version metadata is `0.1.64` in the plugin header, version constant, package metadata, readme stable tag, and translation template metadata;
- GitHub tag and release: `v0.1.64`;
- release asset: `alynt-drime-backups-dashboard-0.1.64.zip`;
- release asset SHA256: `2810565fe12556e6f38118b43359f1ae8e5f03a1357ce20a46a50355e734b622`;
- CI passed for release-prep commit `4073f2d`;
- Build Release run `37532929329` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, 140 runtime files, no excluded source/dev folders, expected runtime metadata at `0.1.64`, and packaged PHP syntax passed for 135 PHP files.
- no production runtime PHP logic, UI behavior, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, release publication, or deployment behavior is introduced by this maintenance line;
- updater runtime acceptance, `control-sitesmanage` deployment, and post-deploy scheduled-poll monitoring remain separate approval-gated workflows.

## Dashboard 0.1.61 Release Record

Dashboard `0.1.61` was released and deployed as a maintenance-only patch:

- `01ba9b0` — remote-action repository summary docblock cleanup;
- `11346f7` — implementation-plan rollout-state synchronization for deployed dashboard `0.1.60`;
- `6eccf02` — enrollment REST repository test double split;
- `07b4063` — remote-action repository fake `wpdb` test double split;
- `de13286` — Unreleased changelog coverage for the post-`0.1.60` local maintenance line.
- `f5e0f9c` — dashboard `0.1.61` release planning record;
- `6bd88d1` — dashboard `0.1.61` version and release-preparation update;
- `533ae41` — dashboard `0.1.61` release documentation record.

Release and deployment evidence:

- GitHub tag and release: `v0.1.61`;
- release asset: `alynt-drime-backups-dashboard-0.1.61.zip`;
- release asset SHA256: `ac6a9310fa2a21d38d098c2d5a57dde2bc383ecf777b84f7797d5104ad2daa12`;
- CI and release build passed before deployment;
- `control-sitesmanage` was updated after explicit approval and a fresh restore point;
- post-update scheduled-poll verification on 2026-10-06 confirmed plugin `0.1.61`, update status `none`, the polling cron advanced, 14 active Working records, 0 active Needs Attention records, 0 active consecutive-failure records, and fresh post-window snapshots.

No further `0.1.61` release/deploy action is pending. The next work should be a new local-only planning or implementation slice unless a separate live-site, release, deployment, database, backup/restore, credential, pairing, schedule-apply/rollback, or other remote-action gate is explicitly opened.

## Dashboard 0.1.62 Release Record

Dashboard `0.1.62` was released and deployed as a display/readiness-only patch:

- `deac3d1` — local retained-record removal design record;
- `a3573ea` — Site Detail local removal preview evidence for archived retained records;
- `7aa27c6` — Diagnostics local removal-readiness aggregate evidence;
- `1534231` — compact archived-row local removal-readiness hints;
- `996c658` — dashboard `0.1.62` version and release-preparation update.

Release evidence:

- GitHub tag and release: `v0.1.62`;
- release asset: `alynt-drime-backups-dashboard-0.1.62.zip`;
- release asset SHA256: `515c368d4d16a353956818a4d18e7e99ab4a05abb9508590d9d29a9939d569bf`;
- CI passed for release-prep commit `996c658`;
- Build Release run `37510850974` passed and uploaded the release asset;
- ZIP audit confirmed one top-level plugin folder, no excluded source/dev folders, and expected runtime metadata at `0.1.62`.
- `control-sitesmanage` was updated after explicit approval and a fresh restore point;
- post-deploy scheduled-poll verification on 2026-10-06 confirmed plugin `0.1.62`, update status `none`, the polling cron advanced beyond the observed deployment window, 14 active Working records, 0 active consecutive-failure records, and fresh post-window snapshots for the active dashboard fleet.

Release boundary:

- no permanent remove/delete control;
- no confirmation form or POST handler;
- no dashboard database write beyond existing release metadata files;
- no remote action, client-site mutation, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or deployment behavior.

No further `0.1.62` release/deploy action is pending. The next work should be a new local-only planning or implementation slice unless a separate live-site, release, deployment, database, backup/restore, credential, pairing, schedule-apply/rollback, permanent local-record removal, or other remote-action/destructive gate is explicitly opened.

## Toolkit Workflow Gates For Future V2 Schedule Slices

Future schedule-management work must use the `wp-plugin-toolkit` routing rather than ad-hoc implementation. Treat each new schedule slice as Phase 4 feature work until it becomes a release candidate.

Required gates:

1. Refresh target-plugin context with the toolkit router, target repo status, current implementation plan, protocol docs, threat model, changelog, test/build tooling, and relevant handoff/context files.
2. Recommend or create a restore point before edit-heavy work.
3. After implementation, run the ds2 feature reviews that apply:
   - `d4-prompts/ds2-feature/FEATURE_LIGHT_REVIEW_PROMPT.md`;
   - `d4-prompts/ds2-feature/FEATURE_BLOAT_AND_STRUCTURE_REVIEW_PROMPT.md` when PHP/JS/CSS structure changed;
   - `d4-prompts/ds2-feature/FEATURE_UI_UX_IMPLEMENTATION_PROMPT.md` for admin UI, forms, AJAX states, or user-facing copy;
   - `d4-prompts/ds2-feature/FEATURE_SECURITY_REVIEW_PROMPT.md`.
4. Before release/deploy, run `d4-prompts/ds3-pre-release/FULL_PRE_RELEASE_WORKFLOW_PROMPT.md` or an explicitly justified targeted ds3 subset. For schedule-management work, the default targeted subset should include code cleanup, error handling, WordPress best practices, performance, edge cases, adversarial test-suite review, i18n, accessibility, code quality, documentation, and security audit.
5. Add database and uninstall reviews when the slice changes schema, custom tables, option lifecycle, retention, uninstall behavior, or stored action payload shape.
6. Do not proceed from preview to apply/rollback without a new protocol/threat-model update, explicit user approval, tests proving no unsafe fallback, and a separate release/deploy gate.
7. Treat rollback metadata capture as a separate stabilization/readiness slice from rollback execution. Capturing support-safe metadata may be designed before a `schedule_rollback` action exists, but `schedule_rollback` must remain unavailable until a later protocol/threat-model and release gate are approved.
8. Treat `schedule_rollback_preview` as the next possible runtime design step before any mutating rollback action. Do not implement `schedule_rollback` directly from metadata capture without a non-mutating preview design, updated protocol/threat model, explicit user approval, tests proving stale/current-state rejection, and a separate release/deploy gate.

### File Structure Baseline Checkpoint

Implementation status: completed locally as a documentation checkpoint and refreshed after dashboard `0.1.61`. A source-size scan excluding generated `build/` release artifacts and `vendor/` dependencies shows the current dashboard plugin source and test-support files are below the earlier large-file risk threshold; the largest current production PHP file is `includes/class-credential-vault.php` at roughly 258 lines, and the largest current test/support PHP files are under 200 lines. Historical files under `build/` may still contain older oversized release-stage copies and should not drive new refactors.

The next structure pass should therefore avoid speculative runtime refactors merely to reduce line counts. Prefer small, evidence-backed slices where a clean seam exists, especially test-support/docs-only cleanup or a focused ds2/ds3 file-structure review before touching security-sensitive runtime code such as credential storage, safe transport, enrollment, dispatcher signing, or schedule/rollback behavior. Files near the top of the current size list should be refactored only when a behavior slice exposes a real maintenance, testing, or review problem.

Acceptance criteria:

- current source-size evidence is recorded in the implementation plan;
- generated release-stage artifacts are not treated as current source bloat;
- no runtime PHP, UI output, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state changes are introduced.

### Diagnostics Remote-Action Capability Gate Copy Slice

The Diagnostics Runtime panel already exposes support-safe installed version, protocol/schema identity, and a remote-action boundary note. A small product-readability improvement is to make the existing capability-gated UI model explicit in the same panel, so operators can distinguish "remote actions are not globally available" from "controls appear only after a latest client report advertises a specific supported capability."

Implementation status: implemented locally as a display-only Diagnostics copy slice. The Dashboard Runtime panel now includes a support-safe `Capability gate` row explaining that remote-action controls appear only when the latest client report advertises the specific supported action capability. This does not change capability detection, action dispatch, protocol behavior, database schema, UI control availability, remote-action permissions, backups, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, live-site behavior, or client-site behavior.

Acceptance criteria:

- Diagnostics Runtime rendering tests cover the new capability-gate row.
- Translation template includes the new display-only strings.
- Focused Diagnostics rendering tests, full tests, lint, build, and whitespace checks pass.
- No protocol behavior, database writes, action capability, action dispatch behavior, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Diagnostics Support Remote-Action Boundary Evidence Slice

The Diagnostics Support Summary already exports support-safe aggregate remote-action counts. A small follow-up product-readability improvement is to include machine-readable boundary evidence in that aggregate section so support reviews can confirm that dashboard remote actions remain signed, explicitly opted in, capability-gated by the latest client report, and independent of Drime API credentials.

Implementation status: implemented locally as a support-safe export metadata slice. The `actions` section now includes a static `boundary` object with booleans for signed-client-actions-only, explicit client opt-in, latest client capability gating, and dashboard Drime credential storage. This does not include site labels, domains, paths, raw payloads, action contexts, credentials, tokens, Drime identifiers, protocol changes, capability changes, dispatch changes, database schema changes, live-site changes, or client-site changes.

Acceptance criteria:

- Support-summary action aggregate tests cover the new boundary evidence.
- Full tests, lint, build, and whitespace checks pass.
- No UI strings, translation changes, protocol behavior, database writes, action capability, action dispatch behavior, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Diagnostics Support Copy Boundary Explanation Slice

The Diagnostics Support Summary export now includes remote-action boundary evidence. A small UI follow-up is to explain beside the copy/download controls that this evidence is informational only and does not grant new client actions or store Drime API credentials in the dashboard.

Implementation status: implemented locally as display-only help text in the Support Copy panel. The copy clarifies the support-summary boundary evidence while preserving the existing redacted export, action capability gates, protocol behavior, database schema, dispatch behavior, credential handling, Drime behavior, release behavior, deployment state, live-site behavior, and client-site behavior.

Acceptance criteria:

- Support Copy rendering tests cover the new boundary-explanation text.
- Translation template includes the new display-only string.
- Focused Support Copy rendering tests, full tests, lint, build, and whitespace checks pass.
- No protocol behavior, database writes, action capability, action dispatch behavior, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Diagnostics Audit History Helper Structure Slice

The diagnostics event-log renderer also owned local operator audit-history rendering and label helpers. The next safe structure-only cleanup is to keep the structured event-log export/table renderer focused while moving audit-history rendering helpers into a dedicated trait.

Implementation status: implemented locally as a structure-only split. Operator audit-history diagnostics rendering, audit table rendering, and audit actor/action label helpers now live in `includes/traits/trait-admin-page-diagnostics-audit-history.php`. The existing diagnostics event-log trait composes the new helper trait and retains structured event-log summary/export/table rendering. No UI text, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- the changed/new PHP files pass syntax checks;
- focused diagnostics rendering tests, full tests, lint, build, and whitespace checks pass;
- the slice remains structure-only and does not alter UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Remote Action Repository Summary Docblock Cleanup Slice

The current source-size baseline does not justify broad runtime refactors. A tiny safe code-quality cleanup was still available in the remote-action repository summary trait: its class-level docblock had malformed spacing from earlier structure splits.

Implementation status: implemented locally as a docblock/formatting-only cleanup. `includes/traits/trait-remote-action-repository-summary.php` now has a normal trait description docblock and removes extra blank lines before the first method. No runtime PHP logic, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- the changed PHP file passes syntax checks;
- full tests, lint, build, and whitespace checks pass;
- the slice remains formatting-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Remote Action Repository Context Structure Slice

The remote-action repository has accumulated several support-safe context sanitizers as Request Backup Now, Schedule Preview, Schedule Apply, Schedule Rollback Preview, and Cleanup Preview were added. The next safe structure-only cleanup is to separate remote-action context sanitization from context merge/redaction helpers without changing storage shape, dispatch behavior, protocol behavior, database schema, UI output, or live-site state.

Implementation status: implemented locally as a structure-only split. Remote-action context sanitizers now live in a dedicated repository context-sanitizers trait while the original context trait retains merge/redaction/count/timestamp helpers. No release, deployment, push, protocol change, database change, UI change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Implementation target:

- move schedule-preview, schedule-apply, rollback-metadata, rollback-preview, and cleanup-preview context sanitizers into a dedicated repository context-sanitizers trait;
- leave redacted-context handling, client-action context merging, client-action count sanitization, and client-action timestamp selection in the existing context trait;
- preserve the existing repository public surface, stored JSON keys, redaction policy, allowlisted context fields, preview-only cleanup boundary, rollback-preview non-mutating boundary, and fail-closed sanitizer defaults;
- keep the slice local-only until separately approved for push/release.

Acceptance criteria:

- targeted remote-action repository tests pass with unchanged behavior;
- PHP syntax, lint, build, and whitespace checks pass;
- no new UI strings, translation changes, protocol changes, database writes, remote-action permissions, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Remote Action Capabilities Support Structure Slice

The remote-action capability sanitizer accumulated both payload sanitization and public support-detection helpers as Request Backup Now, Schedule Preview, Schedule Apply, Schedule Rollback Preview, and Cleanup Preview were added. The next safe structure-only cleanup is to separate support-detection helpers from the public sanitizer trait without changing sanitized output, support decisions, dispatch behavior, protocol behavior, database schema, UI output, or live-site state.

Implementation status: implemented locally as a structure-only split. Remote-action support detection for `scan_upload_now`, schedule-management preview, schedule preview, schedule apply, and schedule rollback preview now lives in a dedicated capabilities support trait. The public sanitizer trait retains payload sanitization and action/state sanitizers, and the capability class composes the new support trait alongside the existing sanitizer traits. No release, deployment, push, protocol change, database change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- focused remote-action capability tests pass with unchanged support decisions;
- PHP syntax, lint, build, and whitespace checks pass;
- no new UI strings, translation changes, protocol changes, database writes, remote-action permissions, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Remote Action Capabilities Schedule Result Structure Slice

The remote-action capability action-data trait accumulated both action-history summary sanitization and schedule preview/apply/rollback-preview result sanitizers. The next safe structure-only cleanup is to separate schedule result sanitizers from the action-history summary sanitizer without changing sanitized output, support decisions, dispatch behavior, protocol behavior, database schema, UI output, or live-site state.

Implementation status: implemented locally as a structure-only split. Schedule preview, schedule apply, evidence-only rollback metadata, schedule rollback preview, and schedule warning sanitizers now live in a dedicated capabilities schedule-results trait. The action-data trait retains forbidden-field scanning, allowed-action sanitization, and last-action summary assembly. No release, deployment, push, protocol change, database change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- focused remote-action capability tests pass with unchanged schedule result sanitization;
- PHP syntax, lint, build, and whitespace checks pass;
- no new UI strings, translation changes, protocol changes, database writes, remote-action permissions, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Schedule Rollback Preview Admin Helper Structure Slice

The admin Schedule Rollback Preview helper remains bounded and non-mutating, but the form-rendering trait accumulated readiness state, action-history lookup, and operator-message branching. The next safe structure-only cleanup is to separate rollback-preview readiness/selection helpers from the form renderer without changing UI output, capability checks, dispatch behavior, stored action history, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Rollback-preview readiness labels, readiness markup, structured readiness state, typed readiness results, and latest successful Schedule Apply selection now live in a dedicated admin readiness trait. The existing rollback-preview helper keeps the guarded form renderer and composes the evidence/readiness helpers. No release, deployment, push, protocol change, database change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- schedule-management and admin rendering tests pass with unchanged rollback-preview form/readiness behavior;
- PHP syntax, lint, build, and whitespace checks pass;
- no new UI strings, translation changes, protocol changes, database writes, remote-action permissions, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Schedule Management Action Form Helper Structure Slice

The schedule-management panel renderer accumulated the non-mutating preview form, guarded apply form, and latest-preview lookup helpers alongside panel and compact row-hint rendering. The next safe structure-only cleanup is to separate preview/apply form rendering into a dedicated helper trait without changing UI output, capability checks, dispatch behavior, stored action history, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Schedule preview form rendering, guarded schedule apply form rendering, and latest successful schedule-preview lookup now live in `includes/traits/trait-admin-page-schedule-management-action-forms.php`. The existing schedule-management form helper keeps the panel renderer and compact Sites-row hint and composes the new action-form trait plus rollback-preview helpers. No release, deployment, push, protocol change, database change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- schedule-management rendering tests pass with unchanged preview/apply form behavior;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Diagnostics Overview Admin Helper Structure Slice

The Diagnostics overview renderer has accumulated support-safe summary labels, runtime identity rendering, and cache-busted refresh helpers alongside the main screen shell. The next safe structure-only cleanup is to separate those helpers from the main overview renderer without changing Diagnostics output, support-copy behavior, scheduler diagnostics, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Restore-readiness labels, source-count formatting, runtime identity rendering, attention-history labels, and Diagnostics freshness/refresh URL helpers now live in a dedicated Diagnostics overview helpers trait. The original overview trait keeps the redacted Diagnostics screen shell and composes the helper trait. No release, deployment, push, protocol change, database change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- diagnostics rendering tests pass with unchanged output;
- PHP syntax, lint, build, and whitespace checks pass;
- no new UI strings, translation changes, protocol changes, database writes, remote-action permissions, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Diagnostics Restore Readiness Metrics Structure Slice

The diagnostics backup-source metrics trait accumulated restore-readiness aggregate counting after restore-readiness evidence was added. The next safe structure-only cleanup is to move restore-readiness aggregate counting into a dedicated diagnostics metrics trait without changing collected counts, support-copy JSON shape, Diagnostics output, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Restore-readiness site/source candidate aggregation now lives in a dedicated diagnostics restore-readiness metrics trait, composed by the existing site metrics trait alongside backup-source, cleanup-preview, schedule-management, and attention-history metrics. No release, deployment, push, protocol change, database change, UI copy change, support-copy shape change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- diagnostics aggregate tests pass with unchanged restore-readiness counts;
- PHP syntax, lint, build, and whitespace checks pass;
- no new UI strings, translation changes, protocol changes, database writes, remote-action permissions, backup creation, restore, cleanup/delete, credential handling, Drime behavior, deployment, or live-site change is introduced.

### Restore Readiness Evidence Label Helper Structure Slice

The restore-readiness evidence renderer accumulated panel rendering, Sites-row hint rendering, warning rendering, labels, and tone helpers after the V2.6 evidence consumer shipped. The next safe structure-only cleanup is to separate restore-readiness label/tone helpers from the evidence renderer without changing UI output, support-safe copy, status payload assumptions, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Restore-readiness source labels, state labels, overall row-hint tone, candidate tone, and candidate summary helpers now live in `includes/traits/trait-admin-page-restore-readiness-labels.php`. The existing evidence renderer composes the new helper trait and keeps panel, row-hint, payload extraction, and warning rendering. No release, deployment, push, protocol change, database change, UI copy change, support-copy shape change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- restore-readiness evidence rendering tests pass with unchanged output expectations;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Sites List Context Helper Structure Slice

The Sites list renderer accumulated request-local status context building, duplicate revoked-row filtering, Attention count calculation, and archived-record visibility helpers alongside the visible screen shell. The next safe structure-only cleanup is to separate Sites-list context helpers from the renderer without changing Sites tab output, status classification, archived-record behavior, duplicate-row filtering, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Sites-list context building, superseded revoked-row filtering, expected-origin normalization, Attention count lookup, and archived visibility detection now live in `includes/traits/trait-admin-page-sites-list-context.php`. The existing Sites-list renderer composes the new context trait and keeps the screen shell, intro copy, summary/table rendering, and archived-record toggle. No release, deployment, push, protocol change, database change, UI copy change, support-copy shape change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- Sites-list context tests pass with unchanged visible/archived context and Attention count behavior;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Site Repository Local State Write Structure Slice

The site repository write trait accumulated pending-site creation, local revocation, dashboard-local archive/unarchive writes, dashboard-local polling pause/resume writes, and enrollment completion. The next safe structure-only cleanup is to separate local archive and polling-state writes into a dedicated trait without changing SQL data shape, changed-row semantics, enrollment behavior, polling behavior, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Dashboard-local `archive_local`, `unarchive_local`, `pause_polling`, and `resume_polling` writes now live in `includes/traits/trait-site-repository-local-state-writes.php`. The existing site repository write trait composes the new local-state write trait and keeps pending creation, local revocation, and enrollment completion. No release, deployment, push, protocol change, database schema change, SQL behavior change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- site repository and dashboard local-action tests pass with unchanged archive/polling state behavior;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, SQL behavior, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Attention Recovery History Helper Structure Slice

The Site Detail status-history helper trait accumulated both recent snapshot-history rendering and the Attention/Recovery transition panel. The next safe structure-only cleanup is to separate Attention/Recovery transition rendering and transition classification into a dedicated helper trait without changing Site Detail output, history semantics, status labels, retained snapshot assumptions, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Attention/Recovery transition rendering, transition collection, and transition type classification now live in `includes/traits/trait-admin-page-attention-recovery-history-helpers.php`. The existing status-history detail helper composes the new trait and retains recent snapshot-history rendering, status label fallback, compact evidence text, and fixture-status guide rendering. No release, deployment, push, protocol change, database schema change, SQL behavior change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- attention/recovery history rendering tests pass with unchanged transition output and bounded latest-transition behavior;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Remote Action History Label Helper Structure Slice

The remote-action history helper accumulated table rendering, details disclosure rendering, action labels, state labels, client-report labels, result labels, and last-action extraction used by adjacent detail panels. The next safe structure-only cleanup is to separate label/result helpers from the history table renderer without changing action-history output, stored action data, result summaries, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Remote-action action labels, state labels, latest-client-action extraction, client-report labels, and result labels now live in `includes/traits/trait-admin-page-remote-action-history-labels.php`. The existing remote-action history helper composes the new label trait and retains table rendering, filters, details disclosure, summary detail selection, and compact action-count details. No release, deployment, push, protocol change, database schema change, SQL behavior change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- remote-action history and request-backup rendering tests pass with unchanged labels, client-report text, result summaries, and details output;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Remote Action Repository Schedule Apply Lookup Structure Slice

The remote-action repository lookup trait accumulated general row lookups, recent-history queries, redacted-context decoding, and the guarded schedule-apply preview freshness validator. The next safe structure-only cleanup is to separate the schedule-apply preview lookup validator from general action-row queries without changing validation semantics, WP_Error codes/messages, stored action data, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. The guarded `fresh_schedule_preview_for_apply()` validator now lives in `includes/traits/trait-remote-action-repository-schedule-apply-lookups.php`. The existing lookup trait composes the new schedule-apply lookup trait and retains public-ID lookup, latest/recent history queries, internal row lookup, and redacted-context decoding. No release, deployment, push, protocol change, database schema change, SQL behavior change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- remote-action repository schedule lookup and schedule dispatch/apply tests pass with unchanged preview freshness and apply-guard behavior;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, SQL behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Schedule Remote Action Handler Structure Slice

The admin remote-action handler trait accumulated Request Backup Now, Cleanup Preview, action opt-in token generation, schedule preview, schedule apply, and schedule rollback preview POST handlers. The next safe structure-only cleanup is to separate schedule-related POST handlers from the general remote-action handler without changing nonce checks, POST sanitization, audit logging, dispatch calls, confirmation requirements, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Schedule Preview, Schedule Apply, and Schedule Rollback Preview admin POST handlers now live in `includes/traits/trait-admin-page-schedule-remote-actions.php`. The existing remote-action handler composes the new schedule trait and retains action opt-in token generation, Request Backup Now, Cleanup Preview, and post-dispatch read-only polling decisions. No release, deployment, push, protocol change, database schema change, SQL behavior change, UI copy change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- admin remote-action tests and schedule dispatch/apply tests pass with unchanged nonce, audit, dispatch, confirmation, and post-dispatch polling behavior;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Diagnostics Support Section Helper Structure Slice

The diagnostics support trait accumulated top-level support-copy assembly plus section-specific reducers for diagnostic summary labels, remote-action aggregates, logging aggregates, and recent safe outcomes. The next safe structure-only cleanup is to separate those support-copy section reducers into a dedicated helper trait without changing support-copy JSON shape, redaction boundaries, diagnostic counts, protocol behavior, database schema, or live-site state.

Implementation status: implemented locally as a structure-only split. Diagnostic summary labels, remote-action support aggregates, logging support aggregates, and recent support-safe outcome reducers now live in `includes/traits/trait-diagnostics-support-sections.php`. The existing diagnostics support trait composes the new section helper trait and retains public support-summary entrypoints and top-level support-summary assembly. No release, deployment, push, protocol change, database schema change, SQL behavior change, UI copy change, support-copy shape change, remote-action permission change, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or live-site change was introduced by this slice.

Acceptance criteria:

- diagnostics support-summary tests pass with unchanged support-copy JSON fields, redaction boundaries, logging aggregates, action aggregates, and recent safe outcomes;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains structure-only and does not alter runtime behavior, support-copy JSON shape, UI copy, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Diagnostics Support Summary Export UI Slice

The Diagnostics Support Copy panel already renders a redacted JSON summary and clipboard helper for support handoff. The next safe operator-facing polish is to add a client-side download/export control that saves the already-rendered redacted summary as a `.json` file, without adding a server endpoint, changing support-copy shape, storing data, or exposing any non-redacted values.

Implementation status: implemented, released, deployed, and post-release monitored through dashboard `0.1.58`. Diagnostics now renders a progressive-enhancement `Download Support Summary` button next to the existing copy action. The browser generates the downloaded `.json` file entirely from the already-rendered redacted support-copy textarea and announces the result through the existing live status region. No server endpoint, support-copy JSON shape change, database write, protocol change, remote-action permission, backup creation, restore, cleanup/delete, credential handling, Drime behavior, raw payload exposure, or client-site mutation was introduced.

Implementation target:

- add a secondary `Download Support Summary` control next to the existing copy action;
- keep the control hidden until dashboard JavaScript is available, matching the existing copy-button progressive-enhancement pattern;
- generate the downloaded file entirely from the existing redacted textarea content in the browser;
- announce download success/failure through the existing support-copy live status region;
- preserve the existing redaction boundary: no client domains, site labels, pairing tokens, polling secrets, authorization headers, raw payloads, raw response bodies, Drime credentials, or remote command output.

Acceptance criteria:

- Diagnostics renders both copy and download controls for the redacted support summary;
- JavaScript-only download uses the existing redacted textarea content and a stable support-summary filename;
- failures are announced with operator-friendly fallback copy;
- targeted Diagnostics rendering tests cover the new controls;
- lint, build, tests, and whitespace checks pass;
- no live-site change, release, deployment, protocol change, database write, remote-action permission, backup creation, restore, cleanup/delete, credential handling, Drime behavior, or raw payload exposure is introduced.

### Admin Polling-State Rendering Test Harness Structure Slice

After the production-file cleanup pass, the largest remaining structure pressure is in PHPUnit rendering tests rather than runtime dashboard code. The `AdminPagePollingStateRenderingTest` file includes many intentionally broad admin-rendering assertions plus reusable harness and repository test-double classes. The next safe test-only cleanup is to move those reusable support classes into a dedicated test support file without changing assertions, fixtures, production code, protocol behavior, database schema, UI output, or live-site state.

Implementation status: implemented locally as a test-only split. The polling-state rendering harness and remote-action repository test double now live in `tests/support/admin-page-polling-state-rendering-test-harness.php`, and `tests/AdminPagePollingStateRenderingTest.php` requires that support file while keeping the existing test methods and fixtures. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `AdminPagePollingStateRenderingTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Admin Polling-State Rendering Test Class Split Slice

The initial polling-state rendering harness split left `AdminPagePollingStateRenderingTest` as a broad assertion file covering polling controls, site-detail local record guidance, request-backup UI, remote-action history, schedule rollback-preview, and cleanup-preview rendering. The next safe test-only cleanup is to split those assertions into focused rendering test classes while preserving every existing assertion, fixture value, production helper call, UI string expectation, protocol boundary, and live-site behavior.

Implementation status: implemented locally as a test-only split. Manual-check and next-poll row state coverage remains in `tests/AdminPagePollingStateRenderingTest.php`. Dashboard-local polling pause/resume controls moved to `tests/AdminPagePollingControlsRenderingTest.php`. Site-detail local record and attention/recovery rendering moved to `tests/AdminPageSiteDetailLocalRecordRenderingTest.php`. V2 request-backup rendering moved to `tests/AdminPageRequestBackupRenderingTest.php`. Remote-action history details and filters moved to dedicated remote-action history test classes. Schedule rollback-preview and cleanup-preview rendering moved to `tests/AdminPageScheduleCleanupRenderingTest.php`, with shared remote-action fixtures in `tests/support/admin-page-remote-action-rendering-fixtures.php` and shared bootstrap loading in `tests/support/admin-page-polling-state-rendering-bootstrap.php`.

Acceptance criteria:

- focused split rendering coverage passes with the same assertions;
- no split test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Status Classifier Test Class Split Slice

`StatusClassifierTest` remained the largest PHPUnit file after the admin rendering cleanup. It mixed baseline dashboard classification, generic backup-source evidence handling, WPvivid freshness policy windows, and WPvivid source-policy/no-evidence behavior in one file. The next safe test-only cleanup is to split those assertions into focused classifier test classes while preserving existing fixture builders, expected categories, messages, payloads, source-policy behavior, production classifier code, protocol behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Baseline snapshot/status rules remain in `tests/StatusClassifierTest.php`. Generic backup-source evidence coverage moved to `tests/StatusClassifierSourceEvidenceTest.php`. WPvivid freshness-window coverage moved to `tests/StatusClassifierWpvividFreshnessTest.php`. WPvivid upload-evidence/source-policy coverage moved to `tests/StatusClassifierWpvividPolicyTest.php`. Shared classifier dependencies now load through `tests/support/status-classifier-test-bootstrap.php`, while the existing fixture trait remains in `tests/support/status-classifier-test-fixtures.php`.

### Status Classifier Backup Source Fixture Split Slice

Implementation status: implemented locally as a test-support-only split. The reusable backup-source source-summary fixture builder now lives in `tests/support/status-classifier-backup-source-fixtures.php`. The existing status-classifier fixture trait composes the new backup-source fixture trait and retains active-site, snapshot, and healthy-payload builders. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, classifier behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `StatusClassifier` coverage passes with the same assertions;
- no split status-classifier test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Diagnostics Test Harness Structure Slice

The next largest PHPUnit structure hotspot is `DiagnosticsTest`, which mixes diagnostics assertions with reusable fake repository classes and support-summary harness code. The next safe test-only cleanup is to move those reusable support classes into a dedicated test support file while leaving diagnostics assertions, fixtures, production code, protocol behavior, schema, Diagnostics output, support-copy shape, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Diagnostics fake site/snapshot repositories now live in `tests/support/diagnostics-test-repositories.php`, while the stable fixture loader remains `tests/support/diagnostics-test-harness.php`; core site/snapshot fixture builders have since moved into `tests/support/diagnostics-core-fixtures.php`. Diagnostics tests load those support files while keeping the existing test methods and fixture expectations. No production PHP, assets, UI strings, Diagnostics output, support-copy shape, protocol behavior, database schema, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `DiagnosticsTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Diagnostics Snapshot Fixture Split Slice

The diagnostics core fixture support still grouped reusable site-row builders with snapshot payload and retained-history row builders. Keep `tests/support/diagnostics-core-fixtures.php` as the stable diagnostics fixture loader while moving snapshot-related builders into a focused support trait.

Implementation status: implemented locally as a test-only support split. Snapshot payload and retained snapshot-row builders now live in `tests/support/diagnostics-snapshot-fixtures.php`, and the core diagnostics fixture trait composes that trait while retaining the same `site()`, `snapshot()`, and `snapshot_row()` fixture method names and values. Existing diagnostics tests keep requiring the same harness path and assertion expectations remain unchanged. No production PHP, assets, UI strings, Diagnostics output, support-copy shape, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused Diagnostics coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Remote Action Repository Double Split Slice

The shared diagnostics repository support file still grouped fake site, snapshot, and remote-action repositories together. The next safe test-only structure cleanup is to move the diagnostics remote-action repository double into its own support file while leaving the existing diagnostics bootstrap, fixture data, aggregate counts, support-summary behavior, and assertions unchanged.

Implementation status: implemented locally as a test-only support split. The fake `Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Remote_Action_Repository` collaborator now lives in `tests/support/diagnostics-test-remote-action-repository.php`, and `tests/support/diagnostics-test-bootstrap.php` loads it after the site/snapshot repository doubles. Class name, constructor signature, count semantics, and empty support-summary behavior are unchanged. No production PHP, assets, UI strings, Diagnostics output, support-copy shape, protocol behavior, database schema, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused diagnostics remote-action aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks pass before commit.
- The split remains test-only and does not alter runtime class loading, production files, diagnostics behavior, support-copy JSON shape, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Diagnostics Test Class Split Slice

After the diagnostics harness and fixture cleanup, `DiagnosticsTest` remained an oversized assertion file covering polling/record counts, source aggregates, remote-action aggregate diagnostics, attention history, recent poll outcomes, support-summary redaction, and support action summaries. The next safe test-only cleanup is to split those assertions into focused diagnostics test classes while preserving existing fixtures, fake repositories, support-summary harness behavior, expected aggregate counts, redaction expectations, production diagnostics code, protocol behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Core dashboard diagnostics counts and support-safe record/source aggregates remain in `tests/DiagnosticsTest.php`. Remote-action aggregate diagnostics moved to `tests/DiagnosticsRemoteActionAggregatesTest.php`. Attention-history, poll-outcome, and support-summary redaction coverage moved to `tests/DiagnosticsHistoryAndSupportTest.php`. Support action-summary aggregate coverage moved to `tests/DiagnosticsSupportActionSummaryTest.php`. Shared diagnostics dependencies now load through `tests/support/diagnostics-test-bootstrap.php`, while the existing diagnostics fixture/support harness remains in `tests/support/diagnostics-test-harness.php`.

Acceptance criteria:

- focused `Diagnostics` coverage passes with the same assertions;
- no split diagnostics test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Diagnostics Backup Source Aggregate Test Class Split Slice

`DiagnosticsTest` still mixed core polling/record-state diagnostics with backup-source aggregate diagnostics after the broader diagnostics split. The next safe test-only cleanup is to move backup-source aggregate assertions into a focused diagnostics class while preserving the same diagnostics service behavior, support-copy JSON shape, redaction expectations, source aggregate counts, and live-site state.

Implementation status: implemented locally as a test-only split. Core polling and record-state diagnostics coverage remains in `tests/DiagnosticsTest.php`. Backup-source reporting/stale/no-upload-evidence aggregate coverage moved to `tests/DiagnosticsBackupSourceAggregatesTest.php`. The shared diagnostics fixture trait now exposes `collect_diagnostics()` for focused diagnostics test classes.

Acceptance criteria:

- focused diagnostics backup-source aggregate coverage passes with the same observable assertions;
- no split diagnostics test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, diagnostics collection behavior, support-copy JSON shape, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Repository Test Class Split Slice

`RemoteActionRepositoryTest` remained one of the largest PHPUnit files after the diagnostics cleanup. It mixed dashboard-owned request/state writes, client report reconciliation, support-summary aggregates, retention/staleness maintenance, schedule preview/apply lookup, rollback-preview readiness, and site-scoped query coverage in one file. The next safe test-only cleanup is to split those assertions into focused repository test classes while preserving existing fake wpdb behavior, fixture helpers, expected SQL shapes, redacted context expectations, production repository code, protocol behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Core request/state write coverage remains in `tests/RemoteActionRepositoryTest.php`. Client report and support-summary context coverage moved to `tests/RemoteActionRepositoryClientReportTest.php`. Retention cleanup and stale reconciliation coverage moved to `tests/RemoteActionRepositoryMaintenanceTest.php`. Schedule preview/apply lookup and site-scoped query coverage moved to `tests/RemoteActionRepositoryScheduleLookupTest.php`. Shared repository dependencies and fake wpdb setup now load through `tests/support/remote-action-repository-test-bootstrap.php`, while the existing fake wpdb and fixture helpers remain in `tests/support/remote-action-repository-test-harness.php`.

Acceptance criteria:

- focused `RemoteActionRepository` coverage passes with the same assertions;
- no split remote-action repository test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Repository Schedule Fixture Split Slice

The shared remote-action repository fixture harness still mixed generic repository fixtures with schedule-preview, schedule-apply, and schedule-rollback-preview lookup fixtures. The next safe test-support cleanup is to keep schedule-specific lookup fixtures in their own support trait while preserving the same focused repository tests and fake wpdb behavior.

Implementation status: implemented locally as a test-support-only split. Schedule-preview and schedule-apply lookup fixture builders now live in `tests/support/remote-action-repository-schedule-fixtures.php`, while rollback-preview capability and rollback-metadata row builders live in `tests/support/remote-action-repository-schedule-rollback-fixtures.php`. The existing remote-action repository test harness composes the split fixture traits and retains generic repository construction, empty-row, and client reconciliation fixtures. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionRepository` coverage passes with the same assertions;
- full tests, lint, build, and whitespace checks pass;
- the split remains test-support-only and does not alter runtime class loading, production files, protocol behavior, database behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Repository Schedule Rollback Capability Fixture Split Slice

After the schedule fixture split, the rollback fixture trait still grouped rollback-metadata action row fixtures with sanitized rollback-preview capability lookup fixtures. Keep `tests/support/remote-action-repository-schedule-rollback-fixtures.php` as the stable loader while moving rollback-preview capability lookup data into a focused support trait.

Implementation status: implemented locally as a test-support-only split. Rollback-preview capability lookup fixtures now live in `tests/support/remote-action-repository-schedule-rollback-capabilities.php`, and the existing rollback fixture trait composes that trait while retaining the same public loader path, fixture method names, fixture payloads, and repository lookup assertions. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused `RemoteActionRepositoryScheduleLookup` coverage passes with the same assertions.
- Full tests, lint, build, and whitespace checks pass.
- The split remains test-support-only and does not alter runtime class loading, production files, protocol behavior, database behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Repository Schedule Apply Capability Fixture Split Slice

After the schedule rollback capability split, the schedule fixture trait still grouped expired schedule-preview action row fixtures with sanitized schedule-apply capability lookup fixtures. Keep `tests/support/remote-action-repository-schedule-fixtures.php` as the stable schedule fixture loader while moving schedule-apply capability lookup data into a focused support trait.

Implementation status: implemented locally as a test-support-only split. Schedule-apply capability lookup fixtures now live in `tests/support/remote-action-repository-schedule-apply-capabilities.php`, and the existing schedule fixture trait composes that trait alongside rollback fixture helpers while retaining the same public loader path, fixture method names, fixture payloads, and repository lookup assertions. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused `RemoteActionRepositoryScheduleLookup` coverage passes with the same assertions.
- Full tests, lint, build, and whitespace checks pass.
- The split remains test-support-only and does not alter runtime class loading, production files, protocol behavior, database behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Capabilities Test Class Split Slice

`RemoteActionCapabilitiesTest` remained the largest PHPUnit file after the repository cleanup. It mixed base allowlist/forbidden-field sanitization, schedule-management policy support, schedule result alias normalization, rollback-preview support, and cleanup-preview support in one file. The next safe test-only cleanup is to split those assertions into focused capability test classes while preserving existing fixture builders, expected sanitized shapes, allowlist behavior, support-helper expectations, production capability code, protocol behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Base capability allowlist and forbidden-field coverage remains in `tests/RemoteActionCapabilitiesTest.php`. Schedule-management policy coverage moved to `tests/RemoteActionCapabilitiesScheduleManagementTest.php`. Schedule result alias coverage moved to `tests/RemoteActionCapabilitiesScheduleResultsTest.php`. Rollback-preview capability coverage moved to `tests/RemoteActionCapabilitiesScheduleRollbackTest.php`. Cleanup-preview capability coverage moved to `tests/RemoteActionCapabilitiesCleanupTest.php`, while shared fixture builders remain in `tests/support/remote-action-capabilities-test-fixtures.php`.

### Remote Action Capabilities Schedule Alias Fixture Split Slice

Implementation status: implemented locally as a test-support-only split. Schedule latest-action alias fixture builders now live in `tests/support/remote-action-capabilities-schedule-alias-fixtures.php`. The existing schedule capability fixture trait composes the new alias fixture trait and retains schedule capability summary builders. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionCapabilities` coverage passes with the same assertions;
- no split remote-action capabilities test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Capabilities Schedule Apply Fixture Split Slice

Implementation status: implemented locally as a test-support-only split. The schedule-apply capability summary fixture now lives in `tests/support/remote-action-capabilities-schedule-apply-fixtures.php`. The existing schedule capability fixture trait composes the new apply fixture trait and retains the reusable Alynt scan/upload schedule builder. Current capability tests keep requiring the same shared fixture loader and expected sanitized output remains unchanged. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused schedule-management capability coverage passes with the same assertions;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Capabilities Third-Party Schedule Fixture Helper Slice

After the schedule fixture splits, `RemoteActionCapabilitiesScheduleManagementTest` still embedded an unsupported third-party schedule row inline to prove the sanitizer ignores non-Alynt schedules. Keep the sanitizer assertion unchanged while moving that unsupported schedule fixture into the shared schedule fixture trait.

Implementation status: implemented locally as a test-support-only cleanup. `tests/support/remote-action-capabilities-schedule-fixtures.php` now exposes `third_party_schedule()`, and `tests/RemoteActionCapabilitiesScheduleManagementTest.php` uses it beside the existing `alynt_scan_upload_schedule()` fixture. The unsupported schedule shape, sanitized output assertions, preview-support assertions, and production capability behavior are unchanged. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused schedule-management capability coverage passes unchanged.
- Full tests, lint, build, and whitespace checks pass.
- The cleanup remains test-support-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Capabilities Schedule Apply Alias Fixture Split Slice

Implementation status: implemented locally as a test-support-only split. Schedule-apply latest-action alias fixture builders now live in `tests/support/remote-action-capabilities-schedule-apply-alias-fixtures.php`. The existing schedule alias fixture trait composes the new apply-alias fixture trait and retains schedule-preview alias builders. Current capability tests keep requiring the same shared fixture loader and expected sanitized output remains unchanged. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused schedule-result alias capability coverage passes with the same assertions;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Capabilities Schedule Apply Rollback Metadata Fixture Helper Slice

After the schedule-apply alias fixture split, the alias fixture still embedded a large rollback-metadata payload inside the schedule-apply summary. Keep the sanitized output and schedule-result assertions unchanged while moving that nested payload into a focused helper.

Implementation status: implemented locally as a test-support-only cleanup. `tests/support/remote-action-capabilities-schedule-apply-alias-fixtures.php` now builds the schedule-apply rollback metadata through `schedule_apply_rollback_metadata()`, while `schedule_apply_alias_summary()` retains the same top-level latest-action fixture and values. No production PHP, UI output, translation string, protocol behavior, database schema, SQL behavior, support-copy JSON shape, remote-action behavior, release, deployment, backup, restore, cleanup/delete, schedule apply/rollback behavior, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused schedule-result alias capability coverage passes unchanged.
- Full tests, lint, build, and whitespace checks pass.
- The cleanup remains test-support-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Dispatcher Test Class Split Slice

`RemoteActionDispatcherTest` remained one of the largest PHPUnit files after the capabilities cleanup. It mixed core scan/upload intent dispatch, schedule preview/apply/rollback-preview dispatch, pre-dispatch rejection paths, client response reconciliation, rate limiting, and safe-transport same-origin/private-resolution coverage in one file. The next safe test-only cleanup is to split those assertions into focused dispatcher test classes while preserving existing fake HTTP clients, fake DNS resolver behavior, fake repository behavior, expected signed intent bodies, redaction expectations, production dispatcher code, protocol behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Core scan/upload dispatch acceptance remains in `tests/RemoteActionDispatcherTest.php`. Schedule preview/apply/rollback-preview dispatch coverage moved to `tests/RemoteActionDispatcherScheduleDispatchTest.php`. Pre-dispatch rejection coverage moved to `tests/RemoteActionDispatcherRejectionTest.php`. Client response mismatch/rate-limit handling moved to `tests/RemoteActionDispatcherClientResponseTest.php`. Same-origin and private-resolution safe-transport coverage moved to `tests/RemoteActionDispatcherTransportTest.php`. Shared dispatcher shims and fake wpdb setup now load through `tests/support/remote-action-dispatcher-test-bootstrap.php`, while the existing dispatcher fixture/fake classes remain in `tests/support/remote-action-dispatcher-test-harness.php`.

Acceptance criteria:

- focused `RemoteActionDispatcher` coverage passes with the same assertions;
- no split remote-action dispatcher test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Remote Action Dispatcher WordPress Shim Bootstrap Split Slice

After the dispatcher test-class split, `tests/support/remote-action-dispatcher-test-bootstrap.php` still grouped local WordPress shims with the fake `wpdb` lifecycle setup trait. Keep the dispatcher bootstrap path stable while moving the local `ARRAY_A`, `current_time()`, and `home_url()` shims into a focused support file.

Implementation status: implemented locally as a test-only support split. Dispatcher-local WordPress shims now live in `tests/support/remote-action-dispatcher-wordpress-shims.php`, and the existing dispatcher test bootstrap loads them before the shared dispatcher harness and fake `wpdb` setup trait. Shim behavior, fake current time, fake control origin, dispatcher fixtures, fake `wpdb` setup, and dispatcher assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, dispatcher behavior, safe transport behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, dispatcher behavior, safe transport behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Status Payload Validator Test Class Split Slice

`StatusPayloadValidatorTest` still grouped base schema validation, backup-source sanitization, remote-action capability sanitization, and restore-readiness sanitization into one test file. The next safe test-only cleanup is to split those assertion groups into focused validator test classes while preserving the same shared fixture data, validator production code, allowlist/security behavior, protocol behavior, schema handling, and live-site state.

Implementation status: implemented locally as a test-only split. Base schema, version, path-field rejection, and UUID mismatch coverage remains in `tests/StatusPayloadValidatorTest.php`. Backup-source sanitization and forbidden nested source coverage moved to `tests/StatusPayloadValidatorBackupSourcesTest.php`. Remote-action capability sanitization and forbidden remote-action field coverage moved to `tests/StatusPayloadValidatorRemoteActionsTest.php`. Restore-readiness sanitization moved to `tests/StatusPayloadValidatorRestoreReadinessTest.php`. Shared validator includes and fixture loading now run through `tests/support/status-payload-validator-test-bootstrap.php`. Generic payload fixtures remain in `tests/support/status-payload-validator-test-fixtures.php`, and backup-source payload builders now live in `tests/support/status-payload-validator-backup-source-fixtures.php` so source-specific fixture shape changes stay isolated.

Acceptance criteria:

- focused `StatusPayloadValidator` coverage passes with the same 10 tests and 63 assertions;
- no split status payload validator test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete, credentials, Drime behavior, or live-site state.

### Admin Page Actions Test Class Split Slice

`AdminPageActionsTest` still grouped pending-site creation, remote action dispatch, dashboard-local polling state changes, local archive/unarchive behavior, nonce validation, and unknown-action handling into one large test file. The next safe test-only cleanup is to split those assertions into action-family test classes while preserving the same admin action harness, nonce/current-user shims, production trait behavior, audit expectations, read-only remote-action boundaries, local archive/polling behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Pending-site creation, expired nonce recovery, and unknown-action coverage remains in `tests/AdminPageActionsTest.php`. Request Backup Now and Cleanup Preview admin action coverage moved to `tests/AdminPageRemoteActionsTest.php`. Pause/resume polling and revoked-pause rejection coverage moved to `tests/AdminPagePollingActionsTest.php`. Local archive/unarchive coverage moved to `tests/AdminPageArchiveActionsTest.php`. Shared POST reset, nonce setup, and harness construction now live in the existing `tests/support/admin-page-actions-test-harness.php` support file.

Acceptance criteria:

- focused `AdminPage*Actions` coverage passes with the same 12 tests and 61 assertions;
- no split admin page action test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, admin behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Schedule Management Test Class Split Slice

`AdminPageScheduleManagementTest` still grouped Site Detail schedule-management panel rendering, apply-gated controls, rollback-preview readiness/evidence, compact Sites-row hints, missing-capability states, payload fixtures, and harness classes into one large test file. The next safe test-only cleanup is to split those concerns into focused schedule rendering test classes while preserving the same schedule-management production helpers, read-only preview boundaries, apply/rollback-preview gating, support-copy strings, row-hint behavior, protocol assumptions, and live-site state.

Implementation status: implemented locally as a test-only split. Preview-only/apply-gated panel rendering and missing-capability panel coverage remains in `tests/AdminPageScheduleManagementTest.php`. Rollback-preview waiting/ready/evidence coverage moved to `tests/AdminPageScheduleRollbackPreviewTest.php`. Compact Sites-row schedule hint coverage moved to `tests/AdminPageScheduleRowHintTest.php`. Shared payload fixtures, rendering harness, and fake remote-action repository now live in `tests/support/admin-page-schedule-management-test-harness.php`.

Acceptance criteria:

- focused `AdminPageSchedule` coverage passes with the same schedule assertions;
- no split schedule-management test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, admin behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Site Repository Test Class Split Slice

`SiteRepositoryTest` still grouped active pending lookup reads, revocation/enrollment write guards, dashboard-local polling runtime writes, fake wpdb behavior, current-time shims, and shared wpdb lifecycle setup into one large test file. The next safe test-only cleanup is to split those repository concerns into focused read/write test classes while preserving the same fake wpdb semantics, repository production classes, SQL expectations, changed-row guards, current-time behavior, database schema assumptions, and live-site state.

Implementation status: implemented locally as a test-only split. Active pending lookup read coverage remains in `tests/SiteRepositoryTest.php`. Dashboard-local pause/resume runtime write coverage moved to `tests/SiteRepositoryRuntimeWritesTest.php`. Revocation and pending-first-poll enrollment write guard coverage moved to `tests/SiteRepositoryEnrollmentWritesTest.php`. Shared fake wpdb, WordPress shims, repository includes, and wpdb lifecycle setup now live in `tests/support/site-repository-test-harness.php`.

Acceptance criteria:

- focused `SiteRepository` coverage passes with the same 6 tests and 28 assertions;
- no split site repository test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, repository behavior, SQL behavior, schema behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Site Repository WPDB Double Support Split Slice

The shared site repository test harness still grouped WordPress shims, production repository includes, fake `wpdb` behavior, and per-test lifecycle setup into one support file. The next safe test-only structure cleanup is to move the fake `wpdb` collaborator into a focused support file while preserving the existing harness entry point, fake class name, prepared-query capture, row/update fixtures, SQL expectations, changed-row guards, and repository assertions.

Implementation status: implemented locally as a test-only support split. The fake `Alynt_Drime_Backups_Dashboard_Test_Site_WPDB` collaborator now lives in `tests/support/site-repository-wpdb-double.php`, with update-result and write-capture behavior isolated in `tests/support/site-repository-wpdb-write-methods.php`. `tests/support/site-repository-test-harness.php` loads the stable double after the repository production includes while keeping WordPress shims and wpdb lifecycle setup in the harness. No production PHP, assets, UI strings, protocol behavior, database schema, repository SQL behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- The site repository harness is smaller and focused on shims/includes/lifecycle setup.
- Site repository read/write/runtime tests pass without assertion changes.
- Full tests, lint, build, and whitespace checks pass before commit.

### Site Repository WPDB Read Helper Split Slice

After the site repository `wpdb` double split, the fake database class still grouped prepared-query capture and row reads with public test state while write behavior already lived in a focused support trait. Keep `tests/support/site-repository-wpdb-double.php` as the stable fake class loader while moving read/query methods into their own support trait.

Implementation status: implemented locally as a test-only support split. `prepare()` and `get_row()` now live in `tests/support/site-repository-wpdb-read-methods.php`, and `Alynt_Drime_Backups_Dashboard_Test_Site_WPDB` composes that trait alongside the existing write-method trait. The fake class name, public properties, prepared-argument capture, row fixture behavior, SQL expectations, changed-row guards, repository assertions, and harness loader are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, repository SQL behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Site repository read/write/runtime tests pass without assertion changes.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, repository behavior, SQL behavior, schema behavior, protocol, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Site Repository WordPress Shim Support Split Slice

After the `wpdb` double split, the shared site repository test harness still grouped local WordPress shims with production repository includes and per-test database lifecycle setup. Keep `tests/support/site-repository-test-harness.php` as the stable loader while moving the local `ARRAY_A` and `current_time()` shims into a focused support file.

Implementation status: implemented locally as a test-only support split. Site repository WordPress shims now live in `tests/support/site-repository-wordpress-shims.php`, and `tests/support/site-repository-test-harness.php` loads them before production repository includes and the fake `wpdb` double. Shim behavior, fake current time, repository includes, lifecycle setup, repository assertions, and SQL expectations are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, repository SQL behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Site repository read/write/runtime tests pass without assertion changes.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, repository behavior, SQL behavior, schema behavior, protocol, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Reconciler Test Class Split Slice

`RemoteActionReconcilerTest` still grouped successful client-action reconciliation, result alias normalization, mismatch/fallback/downgrade/older-report guard behavior, stale maintenance, fake repository behavior, and payload fixtures into one larger test file. The next safe test-only cleanup is to split those concerns into focused reconciler test classes while preserving the same fake repository semantics, reconciler production class, state-transition guards, result alias behavior, stale maintenance behavior, status payload assumptions, and live-site state.

Implementation status: implemented locally as a test-only split. Successful reconciliation and alias normalization coverage remains in `tests/RemoteActionReconcilerTest.php`. Mismatch, sanitizer fallback, terminal-downgrade, and older-report guard coverage moved to `tests/RemoteActionReconcilerGuardsTest.php`. Missing-last-action stale maintenance coverage moved to `tests/RemoteActionReconcilerMaintenanceTest.php`. Shared fake repository and payload fixtures now live in `tests/support/remote-action-reconciler-test-harness.php`.

Acceptance criteria:

- focused `RemoteActionReconciler` coverage passes with the same 7 tests and 23 assertions;
- no split remote action reconciler test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, reconciler behavior, repository behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Diagnostics Rendering Test Class Split Slice

`AdminPageDiagnosticsRenderingTest` still grouped action-history rendering, operator-friendly audit labels, Diagnostics overview aggregate sections, generated-at/refresh evidence, runtime identity copy, and diagnostics rendering harness includes into one larger test file. The next safe test-only cleanup is to split action-history rendering from Diagnostics overview rendering while preserving the same admin rendering helpers, support-safe copy, runtime identity evidence, diagnostics aggregation display, audit labels, and live-site state.

Implementation status: implemented locally as a test-only split. Action-history empty-state and audit-label coverage remains in `tests/AdminPageDiagnosticsRenderingTest.php`. Diagnostics overview aggregate, freshness, and runtime identity coverage moved to `tests/AdminPageDiagnosticsOverviewRenderingTest.php`. Shared diagnostics rendering includes now live in `tests/support/admin-page-diagnostics-rendering-test-harness.php` with the existing rendering harness classes.

Acceptance criteria:

- focused `AdminPageDiagnostics` coverage passes with the same 7 tests and 49 assertions;
- no split diagnostics rendering test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, admin rendering behavior, Diagnostics output, support-copy behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Sites List Test Harness Split Slice

`AdminPageSitesListTest` still embedded its Sites-list rendering harness, fake Sites repository, fake snapshots repository, fake classifier, and `wp_list_pluck()` shim in the assertion file. The next safe test-only cleanup is to move that support code into a dedicated test harness while preserving the same visible-row filtering assertions, archived visibility behavior, Attention count behavior, production Sites-list trait behavior, and live-site state.

Implementation status: implemented locally as a test-only support split. Sites-list assertions remain in `tests/AdminPageSitesListTest.php`. The Sites-list harness, fake repositories/classifier, and `wp_list_pluck()` shim now live in `tests/support/admin-page-sites-list-test-harness.php`.

Acceptance criteria:

- focused `AdminPageSitesList` coverage passes with the same 2 tests and 7 assertions;
- the assertion file remains focused on Sites-list behavior rather than support setup;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, admin rendering behavior, Sites-list filtering behavior, archive visibility behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Safe Transport Test Class Split Slice

`SafeTransportTest` still grouped fixed read-only request preparation, unsafe destination rejection, authorization shape validation, DNS/private-resolution safety, same-origin self-polling behavior, injected HTTP fetch handling, JSON/HTTP/timeout/oversize errors, and shared transport factory setup into one larger test file. The next safe test-only cleanup is to split request preparation from fetch/response handling while preserving the same safe transport production code, origin validation behavior, private-address guardrails, same-origin exception behavior, response limits, and live-site state.

Implementation status: implemented locally as a test-only split. Request preparation and destination/auth safety coverage remains in `tests/SafeTransportTest.php`. Injected HTTP fetch, JSON validation, timeout, HTTP status, and oversize response handling moved to `tests/SafeTransportFetchTest.php`. Shared transport factory setup now lives in `tests/support/safe-transport-test-harness.php`.

Acceptance criteria:

- focused `SafeTransport` coverage passes with the same 10 tests and 31 assertions;
- no split safe transport test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, transport behavior, origin validation behavior, same-origin self-polling behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Safe Transport Fetch Fixture Helper Slice

After the fetch test split, `SafeTransportFetchTest` still repeated the same status-site origin fixture and deterministic polling authorization header across every response-handling assertion. Keep safe transport behavior and response assertions unchanged while centralizing those request fixtures in the existing safe-transport test harness.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/support/safe-transport-test-harness.php` now exposes `status_site()` and `polling_authorization()` helpers, and `tests/SafeTransportFetchTest.php` uses them for the successful JSON, non-JSON, timeout, HTTP status, and oversized-response cases. No production PHP, assets, UI strings, transport behavior, origin validation behavior, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `SafeTransportFetchTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, transport behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Safe Transport Request Fixture Helper Slice

After centralizing fetch fixtures, `SafeTransportTest` still repeated status-site origin rows and the deterministic polling authorization header across request-preparation and unsafe-destination assertions. Reuse the same safe-transport harness fixtures for request-preparation coverage while preserving every expected URL, method, unsafe-destination, authorization-shape, DNS/private-resolution, and same-origin assertion.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/SafeTransportTest.php` now uses `status_site()` and `polling_authorization()` from `tests/support/safe-transport-test-harness.php` for fixed read-only request preparation, unsafe destination rejection, invalid authorization shape, private DNS rejection, and same-origin loopback allowance coverage. No production PHP, assets, UI strings, transport behavior, origin validation behavior, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `SafeTransportTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, transport behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Credential Vault Test Fixture Helper Slice

`CredentialVaultTest` repeated deterministic key material and polling-secret construction across round-trip, fail-closed, short-key, and context-mismatch assertions. Keep the credential-vault implementation and every assertion unchanged while moving repeated test data construction into local private helpers.

Implementation status: implemented locally as a test-only readability cleanup. `tests/CredentialVaultTest.php` now uses `vault()` and `polling_secret()` helpers for deterministic test key material and polling secret values. No production PHP, credential vault behavior, encryption/decryption behavior, error codes, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `CredentialVaultTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, credential behavior, protocol, schema, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Page Remote Action History Rendering Test Class Split Slice

`AdminPageRemoteActionHistoryRenderingTest` still grouped schedule apply history, schedule rollback-preview history, cleanup-preview evidence details, pending schedule-cadence report handling, and compact short-count detail rendering in one test file. The next safe test-only cleanup is to split non-schedule history detail assertions into a focused class while preserving the same admin rendering helpers, support-safe copy, disclosure behavior, evidence-only cleanup framing, schedule-management wording, and live-site state.

Implementation status: implemented locally as a test-only split. Schedule apply, schedule rollback-preview, and pending schedule-cadence report coverage remains in `tests/AdminPageRemoteActionHistoryRenderingTest.php`. Cleanup-preview evidence details and short scan/upload count detail coverage moved to `tests/AdminPageRemoteActionHistoryDetailRenderingTest.php`. Shared remote-action rendering fixtures and harness loading continue through `tests/support/admin-page-polling-state-rendering-bootstrap.php`.

Acceptance criteria:

- focused `AdminPageRemoteActionHistory` coverage passes with the same 8 tests and 46 assertions;
- no split remote-action history rendering test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, admin rendering behavior, Remote Action History output, support-copy behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Diagnostics Restore Readiness Aggregate Test Class Split Slice

`DiagnosticsRemoteActionAggregatesTest` still grouped remote-action capability aggregates and restore-readiness evidence aggregates in one file. The next safe test-only cleanup is to move restore-readiness aggregate assertions into a focused diagnostics test class while preserving the same diagnostics service behavior, support-copy JSON shape, redaction expectations, source-level restore-readiness counts, and live-site state.

Implementation status: implemented locally as a test-only split. Schedule-management aggregate coverage remains in `tests/DiagnosticsRemoteActionAggregatesTest.php`. Cleanup-preview aggregate coverage moved to `tests/DiagnosticsCleanupPreviewAggregatesTest.php`. Restore-readiness aggregate/source count coverage moved to `tests/DiagnosticsRestoreReadinessAggregatesTest.php`. Shared diagnostics fixtures and repository test doubles continue to load through `tests/support/diagnostics-test-bootstrap.php`.

Acceptance criteria:

- focused diagnostics aggregate coverage passes with the same 3 tests and 43 assertions;
- no split diagnostics aggregate test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, diagnostics collection behavior, support-copy JSON shape, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Diagnostics Cleanup Preview Aggregate Test Class Split Slice

`DiagnosticsRemoteActionAggregatesTest` still grouped schedule-management and cleanup-preview aggregate diagnostics after the restore-readiness split. The next safe test-only cleanup is to move cleanup-preview aggregate assertions into a focused diagnostics test class while preserving the same diagnostics service behavior, support-copy JSON shape, redaction expectations, cleanup-preview aggregate counts, and live-site state.

Implementation status: implemented locally as a test-only split. Schedule-management aggregate coverage remains in `tests/DiagnosticsRemoteActionAggregatesTest.php`. Cleanup-preview aggregate coverage moved to `tests/DiagnosticsCleanupPreviewAggregatesTest.php`. Shared diagnostics fixtures and repository test doubles continue to load through `tests/support/diagnostics-test-bootstrap.php`.

Acceptance criteria:

- focused diagnostics aggregate coverage passes with the same assertions;
- no split diagnostics aggregate test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, diagnostics collection behavior, support-copy JSON shape, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Enrollment REST Controller Rejection Test Class Split Slice

`EnrollmentRestControllerTest` still grouped successful enrollment, stored credential/ciphertext assertions, permission and route-argument checks, and all rejection/rate-limit cases in one file. The next safe test-only cleanup is to move rejection-path assertions into a focused class while preserving the same enrollment controller behavior, bearer token boundary, origin/endpoint validation, pairing expiration handling, rate-limit behavior, stored credential expectations, and live-site state.

Implementation status: implemented locally as a test-only split. Successful enrollment, bounded version storage, permission callback, and route-argument coverage remains in `tests/EnrollmentRestControllerTest.php`. Wrong-secret, repeated-invalid/rate-limited, origin mismatch, endpoint mismatch, unsupported schema, and expired-pairing coverage moved to `tests/EnrollmentRestControllerRejectionTest.php`. Shared controller fixtures and repository doubles continue to load through `tests/support/enrollment-rest-controller-test-harness.php`.

Acceptance criteria:

- focused `EnrollmentRestController` coverage passes with the same 10 tests and 88 assertions;
- no split enrollment REST controller test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, enrollment REST behavior, route contracts, origin validation, pairing or credential behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Status Classifier Payload Decoding Test Class Split Slice

`StatusClassifierTest` still grouped baseline status categories, source queue/failure behavior, and stored `payload_json` decoding/fail-closed cases in one file. The next safe test-only cleanup is to move payload decoding assertions into a focused class while preserving the same classifier behavior, malformed-payload handling, fail-closed status messages, source evidence policy, and live-site state.

Implementation status: implemented locally as a test-only split. Baseline pending/incompatible/stale/failure/queue behavior remains in `tests/StatusClassifierTest.php`. Stored `payload_json` decoding, malformed JSON, and empty decoded payload coverage moved to `tests/StatusClassifierPayloadTest.php`. Shared classifier fixtures continue to load through `tests/support/status-classifier-test-bootstrap.php`.

Acceptance criteria:

- focused `StatusClassifier` coverage passes with the same 20 tests and 23 assertions;
- no split status classifier test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, classifier behavior, source evidence policy, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Backup Source Evidence Test Harness Split Slice

`AdminPageBackupSourceEvidenceTest` still embedded its reusable rendering harness, source-policy setup, fixture loader, and production-helper includes below the evidence assertions. The next safe test-only cleanup is to move that reusable support code into a dedicated support file while preserving the same compact/detail evidence assertions, source-policy behavior, WPvivid policy copy, warning copy, and live-site state.

Implementation status: implemented locally as a test-only support split. Backup-source evidence assertions remain in `tests/AdminPageBackupSourceEvidenceTest.php`. The rendering harness, production helper includes, and source-policy setup live in `tests/support/admin-page-backup-source-evidence-test-harness.php`; the JSON fixture loader trait now lives in `tests/support/admin-page-backup-source-evidence-fixtures.php` and is loaded through the stable harness require path.

Acceptance criteria:

- focused `AdminPageBackupSourceEvidence` coverage passes with the same 5 tests and 49 assertions;
- the assertion file remains focused on backup-source evidence behavior rather than support setup;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, backup-source evidence rendering, source-policy behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Backup Source Evidence Harness Class Split Slice

After the backup-source evidence support split, the stable harness loader still grouped production helper includes with the concrete rendering harness class. Keep `tests/support/admin-page-backup-source-evidence-test-harness.php` as the stable loader while moving the concrete harness class into a focused support file.

Implementation status: implemented locally as a test-only support split. `Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness` now lives in `tests/support/admin-page-backup-source-evidence-harness-class.php`, and the existing test harness loader requires it after production helper traits and evidence fixtures. Harness class name, source-policy construction, compact/detail helper exposure, detail-list renderer behavior, fixture loading, and backup-source evidence assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, backup-source evidence rendering, source-policy behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused backup-source evidence coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, backup-source evidence rendering, source-policy behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller Failure Test Class Split Slice

`PollerTest` still grouped the successful manual poll path with invalid payload, missing credential, transport backoff, snapshot-storage failure, success-persistence failure, and failure-persistence failure paths. The next safe test-only cleanup is to move the failure/storage assertions into a focused poller failure class while preserving the same poller behavior, safe transport boundary, snapshot recording behavior, failure counter/backoff behavior, storage error surfacing, and live-site state.

Implementation status: implemented locally as a test-only split. Successful manual poll coverage remains in `tests/PollerTest.php`. Invalid payload, missing credentials, failure backoff, snapshot storage failure, poll success storage failure, and poll failure storage failure coverage moved to `tests/PollerFailureTest.php`. Shared poller fixtures and repository doubles continue to load through `tests/support/poller-test-harness.php` and `tests/support/poller-test-fixtures.php`.

Acceptance criteria:

- focused `Poller` coverage passes with the same 11 tests and 52 assertions;
- no split poller test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, polling behavior, snapshot storage behavior, failure backoff behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Poller Storage Failure Test Class Split Slice

`PollerFailureTest` still mixed transport/credential failure behavior with persistence failure behavior after the first poller split. The next safe test-only cleanup is to move snapshot-storage, success-persistence, and failure-persistence assertions into a focused storage failure class while preserving the same poller behavior, safe transport boundary, snapshot recording behavior, failure counter/backoff behavior, storage error surfacing, and live-site state.

Implementation status: implemented locally as a test-only split. Invalid payload, missing credentials, and failure backoff coverage remains in `tests/PollerFailureTest.php`. Snapshot storage failure, poll success storage failure, and poll failure storage failure coverage moved to `tests/PollerStorageFailureTest.php`. Shared poller fixtures now include the deterministic test credential vault and successful HTTP client helper used by both failure-focused classes.

Acceptance criteria:

- focused `Poller` failure/storage coverage passes with the same observable assertions;
- no split poller failure test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, polling behavior, snapshot storage behavior, failure backoff behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Poller Site Repository Write Helper Split Slice

The shared poller site-repository test double still grouped due-site fixture setup with fake success/failure write-result capture. The next safe test-only support cleanup is to move the write-result capture properties and methods into a focused support trait while preserving the same fake repository class name, due-site behavior, success/failure result capture, poller assertions, and live-site state.

Implementation status: implemented locally as a test-only support split. Success write capture lives in `tests/support/poller-test-site-repository-write-methods.php`, failure write capture lives in `tests/support/poller-test-site-repository-failure-methods.php`, due-for-poll capture lives in `tests/support/poller-test-site-repository-due-methods.php`, and `tests/support/poller-test-site-repository.php` composes those focused traits while retaining site lookup and fixture setup behavior. No production PHP, assets, UI strings, polling behavior, snapshot storage behavior, failure backoff behavior, protocol behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `Poller` coverage passes with the same observable assertions;
- the poller site-repository test double remains focused on fixture setup and due-site reads;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, polling behavior, snapshot storage behavior, failure backoff behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Polling-State Counting Double Split Slice

The admin polling-state rendering support file still grouped snapshot and remote-action counting repository doubles in one helper. The next safe test-only support cleanup is to keep the stable `admin-page-polling-state-rendering-counting-doubles.php` loader for snapshot counting while moving remote-action counting into a focused support file.

Implementation status: implemented locally as a test-only support split. Snapshot counting remains in `tests/support/admin-page-polling-state-rendering-counting-doubles.php`, and remote-action counting lives in `tests/support/admin-page-polling-state-rendering-remote-action-counting-double.php`, loaded through the existing counting-doubles support path. Local record rendering helpers, test class require paths, assertion expectations, production rendering code, remote-action behavior, polling behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, and live-site state remain unchanged.

Acceptance criteria:

- focused admin polling-state/local-record rendering coverage passes unchanged;
- full tests, lint, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, UI output, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state.

### Admin Page Site Detail Attention History Test Class Split Slice

`AdminPageSiteDetailLocalRecordRenderingTest` still grouped Site Detail attention/recovery history rendering with local polling controls, revoked-record guidance, and archive/unarchive controls. The next safe test-only cleanup is to move attention/recovery history assertions into a focused Site Detail history rendering class while preserving the same admin rendering helpers, bounded transition behavior, empty-state copy, local-only control copy, archive behavior, and live-site state.

Implementation status: implemented locally as a test-only split. Local polling control, revoked guidance, and archive/unarchive coverage remains in `tests/AdminPageSiteDetailLocalRecordRenderingTest.php`. Attention/recovery transition, unchanged-history empty-state, and bounded-transition coverage moved to `tests/AdminPageSiteDetailAttentionHistoryRenderingTest.php`. Shared rendering fixtures continue to load through `tests/support/admin-page-polling-state-rendering-bootstrap.php`.

Acceptance criteria:

- focused `AdminPageSiteDetail` rendering coverage passes with the same 8 tests and 28 assertions;
- no split Site Detail local-record rendering test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, Site Detail rendering behavior, attention/recovery history behavior, archive/unarchive behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Local Removal Rendering Test Class Split Slice

`AdminPageSiteDetailLocalRecordRenderingTest` still grouped local polling controls, revoked-record guidance, archive/unarchive controls, and local-removal preview rendering after the attention-history split. The next safe test-only cleanup is to move local-removal preview and row-hint assertions into a focused class while preserving the same rendering helpers, archived-record eligibility behavior, preview-only copy, dependent-count output, and live-site state.

Implementation status: implemented locally as a test-only split. Local polling control, revoked guidance, and archive/unarchive coverage remains in `tests/AdminPageSiteDetailLocalRecordRenderingTest.php`. Local Removal Preview panel assertions and compact archived-row readiness hint assertions moved to `tests/AdminPageLocalRemovalRenderingTest.php`, with a shared terminal archived-record fixture. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused Site Detail local-record and local-removal rendering coverage passes with the same assertions;
- no split local-record rendering class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, Site Detail rendering behavior, local-removal preview behavior, archive/unarchive behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Cleanup Preview Rendering Test Class Split Slice

`AdminPageScheduleCleanupRenderingTest` still grouped cleanup-preview panel rendering with schedule rollback-preview rendering. The next safe test-only cleanup is to move cleanup-preview assertions into a focused class while preserving the same cleanup capability gating, preview-only copy, latest preview evidence display, support-safe category filtering, rollback-preview rendering, and live-site state.

Implementation status: implemented locally as a test-only split. Schedule rollback-preview rendering coverage remains in `tests/AdminPageScheduleCleanupRenderingTest.php`. Cleanup-preview supported/hidden state coverage and latest preview evidence rendering moved to `tests/AdminPageCleanupPreviewRenderingTest.php`. Shared remote-action rendering fixtures continue to load through `tests/support/admin-page-polling-state-rendering-bootstrap.php`.

Acceptance criteria:

- focused schedule/cleanup admin rendering coverage passes with the same 6 tests and 36 assertions;
- no split schedule/cleanup rendering test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, cleanup-preview rendering, schedule rollback-preview rendering, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Cleanup Preview Snapshot Fixture Helper Slice

After the cleanup-preview rendering test class split, `AdminPageCleanupPreviewRenderingTest` still rebuilt the same V2 cleanup-management snapshot envelope in multiple assertions. Keep cleanup-preview rendering unchanged and add a private snapshot helper for capability and latest-preview evidence cases.

Implementation status: implemented locally as a test-only structure cleanup. `tests/AdminPageCleanupPreviewRenderingTest.php` now uses `cleanup_preview_snapshot()` to build the V2 remote-action cleanup capability envelope, with explicit overrides for allowed actions, supported categories, and latest sanitized preview evidence. The preview-only control assertions, latest evidence assertions, support-safe category filtering, hidden-without-capability assertion, and fixture values are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, cleanup-preview behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `AdminPageCleanupPreviewRenderingTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, cleanup-preview behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Enrollment Manager Test Harness Split Slice

`EnrollmentManagerTest` still embedded its fake site repository, production includes, and display-token secret helper alongside pending-enrollment assertions. The next safe test-only cleanup is to move reusable support setup into a dedicated support file while preserving the same pending enrollment behavior, display-once token assertions, duplicate pending guard, origin/label validation, storage-failure behavior, and live-site state.

Implementation status: implemented locally as a test-only support split. Pending-enrollment assertions remain in `tests/EnrollmentManagerTest.php`. The fake site repository, production includes, and token-secret helper trait now live in `tests/support/enrollment-manager-test-harness.php`.

Acceptance criteria:

- focused `EnrollmentManager` coverage passes with the same 5 tests and 23 assertions;
- the assertion file remains focused on pending-enrollment behavior rather than support setup;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, enrollment behavior, pairing token behavior, origin validation, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Pairing Tokens Frozen Fixture Helper Slice

`PairingTokensTest` repeated the same frozen protocol-v1 token construction for both version-prefix and payload-shape assertions. Keep pairing token behavior and decoded payload assertions unchanged while centralizing that frozen fixture in the test file.

Implementation status: implemented locally as a test-only readability cleanup. `tests/PairingTokensTest.php` now uses `frozen_protocol_v1_token()` for the deterministic enrollment ID, dashboard origin, client origin, secret, and expiry fixture. No production PHP, token format behavior, token creation behavior, protocol behavior, database schema, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `PairingTokensTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, enrollment behavior, pairing token behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Enrollment Manager Repository Double Split Slice

The enrollment manager support harness still grouped production includes, the fake site repository, and the token-secret helper trait in one loader file. Keep `tests/support/enrollment-manager-test-harness.php` as the stable test entry point while moving the repository double into a focused support file.

Implementation status: implemented locally as a test-only support split. `Alynt_Drime_Backups_Dashboard_Test_Site_Repository` now lives in `tests/support/enrollment-manager-site-repository-double.php`, and `tests/support/enrollment-manager-test-harness.php` loads it after the production enrollment manager dependencies. The fake repository class name, insert capture, create-result override, active-pending fixture behavior, token-secret helper, and enrollment assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused enrollment manager coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, enrollment behavior, pairing token behavior, origin validation, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Page Request Backup Row Hint Test Class Split Slice

`AdminPageRequestBackupRenderingTest` still grouped compact Sites-row V2.1 hints with Site Detail Request Backup panel rendering. The next safe test-only cleanup is to move row-hint assertions into a focused class while preserving the same V2.1 capability copy, latest client action hint, opt-in-needed hint, detail-panel form gating, action-history rendering, and live-site state.

Implementation status: implemented locally as a test-only split. Site Detail Request Backup panel coverage remains in `tests/AdminPageRequestBackupRenderingTest.php`. Compact row hint coverage moved to `tests/AdminPageRequestBackupRowHintRenderingTest.php`. Shared rendering fixtures continue to load through `tests/support/admin-page-polling-state-rendering-bootstrap.php`.

Acceptance criteria:

- focused `AdminPageRequestBackup` rendering coverage passes with the same 7 tests and 28 assertions;
- no split Request Backup rendering test class remains oversized from this source file;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, Request Backup rendering, row hint behavior, remote-action behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Request Backup Fixture Helper Slice

After the row-hint split, `AdminPageRequestBackupRenderingTest` still repeated the same active detail-site row and V2.1 remote-action snapshot setup across detail-panel assertions. Keep the production Request Backup renderer unchanged and move the repeated test setup into private fixture helpers inside the focused test class.

Implementation status: implemented locally as a test-only structure cleanup. `tests/AdminPageRequestBackupRenderingTest.php` now uses `request_backup_site()` and `request_backup_snapshot()` helpers for signed-form, missing-key, missing-capability, and disabled-capability cases. The helper defaults preserve the same site id, enrollment/polling fields, signing-key fields, V2 protocol fields, allowed action, sodium flag, rendered output, and assertions. No production PHP, assets, UI strings, protocol behavior, database schema, Request Backup behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `AdminPageRequestBackupRenderingTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Request Backup behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Event Log Test Harness Split Slice

`EventLogTest` still embedded WordPress option shims, event-log production includes, and option-storage test globals above the event log assertions. The next safe test-only cleanup is to move reusable support setup into a dedicated support file while preserving the same settings, threshold, redaction, clear/no-op behavior, option autoload assertions, and live-site state.

Implementation status: implemented locally as a test-only support split. Event log assertions remain in `tests/EventLogTest.php`. WordPress option shims now live in `tests/support/event-log-option-shims.php`, while event-log includes, the audit current-user shim, and shared option-storage setup continue to load through `tests/support/event-log-test-harness.php`.

Acceptance criteria:

- focused `EventLogTest` coverage passes with the same 5 tests and 23 assertions;
- the assertion file remains focused on event log behavior rather than support setup;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, event log behavior, redaction behavior, settings behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Action Audit Test Harness Split Slice

`AdminPageActionAuditTest` still embedded WordPress URL/nonce shims, fake enrollment manager/event-log classes, and the admin-actions harness above the audit assertion. The next safe test-only cleanup is to move reusable support setup into a dedicated support file while preserving the same create-pending-site audit behavior, token redaction assertions, nonce behavior, and live-site state.

Implementation status: implemented locally as a test-only support split. The audit assertion remains in `tests/AdminPageActionAuditTest.php`. URL/nonce shims, fake collaborators, and the admin action harness now load through `tests/support/admin-page-action-audit-test-harness.php`.

Acceptance criteria:

- focused `AdminPageActionAuditTest` coverage passes with the same 1 test and 8 assertions;
- the assertion file remains focused on audit behavior rather than support setup;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, admin action behavior, audit behavior, nonce behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Admin Page Action Audit WordPress Shim Split Slice

After the action audit harness split, the audit harness still grouped audit-specific `home_url()` and `wp_verify_nonce()` shims with fake enrollment/event-log collaborators and the concrete audit action handler harness. Keep the existing audit harness loader stable while moving those local WordPress shims into a focused support file.

Implementation status: implemented locally as a test-only support split. The audit-specific `home_url()` and `wp_verify_nonce()` shims now live in `tests/support/admin-page-action-audit-wordpress-shims.php`, and `tests/support/admin-page-action-audit-test-harness.php` loads them before the production action trait and fake collaborators. Shim behavior, nonce globals, fake collaborator behavior, harness class name, exposed handler method, audit assertions, and production admin action behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- focused `AdminPageActionAuditTest` coverage passes unchanged;
- full dashboard tests, lint, build, and whitespace checks continue to pass;
- the split remains test-only and does not alter runtime class loading, production files, admin action behavior, audit behavior, nonce behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Uninstall Safety Test Harness Split Slice

`UninstallSafetyTest` still embedded WordPress lifecycle shims and a minimal `$wpdb` test double above uninstall behavior assertions. The next safe test-only cleanup is to move reusable uninstall support setup into a dedicated support file while preserving the same rollback-copy guard, default data-preservation checks, explicit purge-constant assertions, and live-site state.

Implementation status: implemented locally as a test-only support split. Uninstall safety assertions remain in `tests/UninstallSafetyTest.php`. WordPress lifecycle shims and the minimal database double now live in `tests/support/uninstall-safety-test-harness.php`.

Acceptance criteria:

- focused uninstall safety coverage passes with the same 3 tests and 15 assertions;
- the assertion file remains focused on uninstall safety behavior rather than support setup;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, uninstall behavior, data-retention behavior, explicit purge behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Diagnostics Test Fixture Structure Slice

`DiagnosticsTest` still retained reusable site, snapshot, and snapshot-history fixture builders after the initial diagnostics support harness split. The next safe test-only cleanup is to move those fixture builders into the existing diagnostics test support file while leaving diagnostics assertions, fake repositories, support-summary harness behavior, production code, protocol behavior, schema, Diagnostics output, support-copy shape, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Core diagnostics fixture builders now live in `tests/support/diagnostics-core-fixtures.php` as a dedicated fixture trait, and `tests/support/diagnostics-test-harness.php` composes that trait to preserve the same shared fixture API for `tests/DiagnosticsTest.php` and focused diagnostics classes. Existing test methods and expected diagnostics/support-summary assertions remain unchanged. No production PHP, assets, UI strings, Diagnostics output, support-copy shape, protocol behavior, database schema, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `DiagnosticsTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Admin Diagnostics Rendering Test Harness Structure Slice

`AdminPageDiagnosticsRenderingTest` retained diagnostics rendering harness classes and a diagnostics service stub below the rendering assertions. The next safe test-only cleanup is to move those harness classes into a dedicated support file while leaving rendering assertions, production diagnostics traits, UI output, protocol behavior, database schema, and live-site state unchanged.

Implementation status: implemented locally as a test-only harness split. Diagnostics rendering harnesses and the overview service stub now live in `tests/support/admin-page-diagnostics-rendering-test-harness.php`, and `tests/AdminPageDiagnosticsRenderingTest.php` requires that support file while keeping the existing rendering assertions and expected markup behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Validation scope:

- syntax-check the admin diagnostics rendering test and support harness;
- run the targeted `AdminPageDiagnosticsRenderingTest` suite;
- run the full local test suite, lint, build, and whitespace checks;
- confirm the split remains test-only and does not alter runtime class loading or release behavior.

### Admin Diagnostics Overview Fixture Structure Slice

`AdminPageDiagnosticsOverviewRenderingTest` retained a large inline diagnostics payload fixture for record-state, restore-readiness, attention-history, and local-removal aggregate rendering. The next safe test-only cleanup is to move that reusable payload into a dedicated support fixture trait while leaving rendering assertions, production diagnostics traits, UI output, support-copy shape, protocol behavior, database schema, and live-site state unchanged.

Implementation status: implemented locally as a test-only fixture split. The diagnostics overview aggregate payload now lives in `tests/support/admin-page-diagnostics-overview-fixtures.php`, and `tests/AdminPageDiagnosticsOverviewRenderingTest.php` uses that trait while keeping the existing rendering assertions and expected markup behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `AdminPageDiagnosticsOverviewRenderingTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, Diagnostics rendering behavior, support-copy shape, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Enrollment REST Controller Test Harness Structure Slice

`EnrollmentRestControllerTest` retained WordPress transient shims, a fake site repository, and reusable controller/payload/request fixtures alongside enrollment assertions. The next safe test-only cleanup is to move those shims and fixtures into a dedicated support harness while leaving enrollment assertions, production REST code, pairing/security behavior, storage shape, protocol behavior, database schema, UI output, and live-site state unchanged.

Implementation status: implemented locally as a test-only harness split. Enrollment REST transient shims, the fake repository, and reusable controller/payload/request fixtures now live in `tests/support/enrollment-rest-controller-test-harness.php`, and `tests/EnrollmentRestControllerTest.php` requires that support file while keeping the existing test methods and expected controller behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Validation scope:

- syntax-check the enrollment REST controller test and support harness;
- run the targeted `EnrollmentRestControllerTest` suite;
- run the full local test suite, lint, build, and whitespace checks;
- confirm the split remains test-only and does not alter runtime class loading or release behavior.

### Status Payload Validator Test Fixture Structure Slice

`StatusPayloadValidatorTest` retained reusable valid status-payload and backup-source builders alongside validation assertions. The next safe test-only cleanup is to move those shared fixtures, plus validator construction, into a dedicated test support file while leaving validation assertions, production validator code, sanitized payload shape, protocol behavior, schema handling, UI output, and live-site state unchanged.

Implementation status: implemented locally as a test-only fixture split. Status payload validator fixtures now live in `tests/support/status-payload-validator-test-fixtures.php` as a dedicated fixture trait, and `tests/StatusPayloadValidatorTest.php` requires that support file while keeping the existing test methods and expected validator behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Validation scope:

- syntax-check the status payload validator test and support fixture;
- run the targeted `StatusPayloadValidatorTest` suite;
- run the full local test suite, lint, build, and whitespace checks;
- confirm the split remains test-only and does not alter runtime class loading or release behavior.

### Status Payload Validator Backup Source Fixture Override Slice

After the payload-validator fixture split, `StatusPayloadValidatorBackupSourcesTest` still repeated `array_merge( source_payload(), ... )` setup for allowlist, enum-boundary, warning-boundary, and forbidden-field assertions. Keep validator behavior unchanged and let the shared backup-source fixture accept source overrides directly.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/support/status-payload-validator-backup-source-fixtures.php` now accepts optional overrides in `source_payload()`, and `tests/StatusPayloadValidatorBackupSourcesTest.php` reuses that helper for server and WPvivid source payload variants. Sanitized field assertions, warning bounds, schedule-policy redaction expectations, forbidden-field rejection, payload shape, and fixture values remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, validator behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `StatusPayloadValidatorBackupSourcesTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, validator behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, remote-action, or client-site behavior changes.

### Remote Action Repository Test Fixture Structure Slice

`RemoteActionRepositoryTest` retained repeated repository construction and small stored-action row fixtures after the remote-action repository harness split. The next safe test-only cleanup is to move those reusable test fixtures into the existing repository support file while leaving all remote-action repository assertions, production code, storage shape, protocol behavior, database schema, UI output, and live-site state unchanged.

Implementation status: implemented locally as a test-only fixture split. The repository factory and empty stored-action row fixture now live in `tests/support/remote-action-repository-test-harness.php` as a dedicated fixture trait, and `tests/RemoteActionRepositoryTest.php` uses that trait while keeping the existing test methods and expected repository behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Validation scope:

- syntax-check the remote-action repository test and support harness;
- run the targeted `RemoteActionRepositoryTest` suite;
- run the full local test suite, lint, build, and whitespace checks;
- confirm the split remains test-only and does not alter runtime class loading or release behavior.

### Remote Action Dispatcher Test Harness Structure Slice

The next largest PHPUnit structure hotspot is `RemoteActionDispatcherTest`, which mixes signed-dispatch assertions with reusable wpdb, credential-vault, signer, and remote-action repository test doubles. The next safe test-only cleanup is to move those reusable support classes into a dedicated test support file while leaving dispatcher assertions, fixtures, production code, protocol behavior, schema, dispatch behavior, signing behavior, transport behavior, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Dispatcher wpdb, vault, signer, and remote-action repository test doubles now live in `tests/support/remote-action-dispatcher-test-harness.php`, and `tests/RemoteActionDispatcherTest.php` requires that support file while keeping the existing test methods and fixture builders. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionDispatcherTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Remote Action Dispatcher Test Fixture Structure Slice

`RemoteActionDispatcherTest` still retained dispatcher, site-row, and snapshot-row fixture builders after the initial dispatcher harness split. The next safe test-only cleanup is to move those fixture builders into the existing dispatcher test support file while leaving signed-dispatch assertions, fake collaborator behavior, production code, protocol behavior, schema, dispatch behavior, signing behavior, transport behavior, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Dispatcher fixture builders now live in `tests/support/remote-action-dispatcher-test-harness.php` as a dedicated fixture trait, and `tests/RemoteActionDispatcherTest.php` uses that trait while keeping the existing test methods and dispatch assertions. No production PHP, assets, UI strings, protocol behavior, database schema, dispatch behavior, remote-action permission change, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionDispatcherTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Admin Page Actions Test Harness Structure Slice

The next largest PHPUnit structure hotspot is `AdminPageActionsTest`, which mixes admin action assertions with reusable WordPress shims, enrollment manager, dispatcher, poller, event-log, site-repository test doubles, and the private-action harness. The next safe test-only cleanup is to move those reusable support definitions into a dedicated test support file while leaving admin action assertions, fixtures, production code, nonce behavior, action dispatch behavior, polling behavior, archive/pause behavior, protocol behavior, schema, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Admin action WordPress shims, fake collaborators, and the action harness now live in `tests/support/admin-page-actions-test-harness.php`, and `tests/AdminPageActionsTest.php` requires that support file while keeping the existing test methods and fixtures. The fake admin-action site repository keeps its stable class name in `tests/support/admin-page-actions-sites-double.php`, with pause/resume recording behavior isolated in `tests/support/admin-page-actions-sites-local-state-double.php` and archive/unarchive recording behavior isolated in `tests/support/admin-page-actions-sites-archive-state-double.php`. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `AdminPageActionsTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Poller Test Fixture Structure Slice

`PollerTest` retains poller construction, enrolled-site row, and valid status-payload builders after its harness split. Move those three private builders verbatim into `tests/support/poller-test-fixtures.php`. Separate the two scheduled batch tests into `tests/PollerScheduledPollingTest.php`, sharing the same harness and fixture trait, while retaining every test method and assertion. Keep production code, schedules, status payloads, polling behavior, storage, and release contents unchanged.

Implementation status: implemented and validated locally as a test-only fixture extraction and manual/scheduled test-class split. `PollerTest.php` is 233 lines, `PollerScheduledPollingTest.php` is 89 lines, and the fixture trait is 94 lines. All 12 moved/retained methods match the committed baseline exactly after newline normalization. The focused `--filter Poller` run passes 11 tests/52 assertions; the full suite retains 237 tests/1,285 assertions and 2 existing skips. PHP syntax, lint, build, and whitespace checks pass, with no runtime or generated-file changes.

Validation scope:

- compare the extracted builders and retained test methods against the committed baseline;
- syntax-check all three changed PHP files and run both poller test classes;
- run full tests, lint, build, and whitespace checks;
- confirm no runtime or generated files changed.

### Poller Test Harness Structure Slice

The next largest PHPUnit structure hotspot is `PollerTest`, which mixes poller assertions with reusable production dependency loads, fake site/snapshot repositories, and a fake remote-action reconciler. The next safe test-only cleanup is to move those reusable support definitions into a dedicated test support file while leaving poller assertions, fixtures, scheduling behavior, status-check behavior, snapshot recording behavior, remote-action reconciliation behavior, production code, protocol behavior, schema, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Poller dependency loads, fake repositories, and the remote-action reconciler test double now live in `tests/support/poller-test-harness.php`, and `tests/PollerTest.php` requires that support file while keeping the existing test methods and fixtures. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `PollerTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Remote Action Repository Test Harness Structure Slice

The next remaining PHPUnit structure hotspot with a clean support seam is `RemoteActionRepositoryTest`, which mixes repository storage assertions with reusable WordPress shims and a fake `wpdb` implementation. The next safe test-only cleanup is to move those reusable support definitions into a dedicated test support file while leaving repository assertions, fixtures, redaction expectations, storage-shape checks, cleanup-retention checks, query-scope checks, production code, protocol behavior, schema, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Remote action repository WordPress shims and fake `wpdb` now live in `tests/support/remote-action-repository-test-harness.php`, and `tests/RemoteActionRepositoryTest.php` requires that support file while keeping the existing test methods and fixtures. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionRepositoryTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Remote Action Repository WPDB Double Support Split Slice

The remote action repository test harness still grouped WordPress shims, shared fixture helpers, and the fake `wpdb` class in one support file. Keep the existing bootstrap/harness loader stable while moving the fake database collaborator into a focused support file.

Implementation status: implemented locally as a test-only structure cleanup. The fake `wpdb` collaborator now lives in `tests/support/remote-action-repository-wpdb-double.php`, and `tests/support/remote-action-repository-test-harness.php` requires it before defining WordPress shims and fixture helpers. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused remote action repository coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, repository behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action Repository WPDB Query Helper Split Slice

The remote action repository fake `wpdb` collaborator still grouped insert/update capture with prepared-query and read-result shims. The next safe test-only structure cleanup is to move the query/read shims into a focused support trait while preserving the fake class name, property names, SQL capture behavior, row fixtures, repository assertions, and live-site state.

Implementation status: implemented locally as a test-only support split. Query/read helpers now live in `tests/support/remote-action-repository-wpdb-query-methods.php`, insert/update capture now lives in `tests/support/remote-action-repository-wpdb-write-methods.php`, and `tests/support/remote-action-repository-wpdb-double.php` composes those traits while retaining the same fake class name and public state. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused remote action repository coverage passes unchanged;
- the fake `wpdb` support class remains focused on insert/update capture while query/read helpers are isolated;
- full dashboard tests, lint, build, and whitespace checks continue to pass;
- no production code, repository behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action Repository WPDB Read Helper Split Slice

The remote-action repository fake `wpdb` query helper still grouped prepared-query capture and read-result shims. The next safe test-only structure cleanup is to move row/row-list fixtures and read methods into their own support trait while keeping the stable query helper loader path.

Implementation status: implemented locally as a test-only support split. Read-result state and `get_row()` / `get_results()` shims now live in `tests/support/remote-action-repository-wpdb-read-methods.php`, and `tests/support/remote-action-repository-wpdb-query-methods.php` requires and composes that trait while retaining prepared-query and generic-query capture. The fake `wpdb` class name, public properties, SQL capture behavior, row fixtures, repository assertions, and shared harness loader remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused remote-action repository coverage passes unchanged;
- full dashboard tests, lint, build, and whitespace checks continue to pass;
- no production code, repository behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action Repository WordPress Shim Support Split Slice

After the repository `wpdb` helper splits, the shared remote-action repository harness still grouped local WordPress shims with fake database support and reusable fixture helpers. Keep `tests/support/remote-action-repository-test-harness.php` as the stable loader while moving the local `ARRAY_A` and `current_time()` shims into a focused support file.

Implementation status: implemented locally as a test-only support split. Remote-action repository WordPress shims now live in `tests/support/remote-action-repository-wordpress-shims.php`, and the existing repository test harness loads them before fake `wpdb` support and fixture traits. Shim behavior, fake current time, fake database behavior, repository fixtures, repository assertions, and SQL expectations are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, repository behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action repository coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, repository behavior, SQL behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Repository Client Report Fixture Structure Slice

`RemoteActionRepositoryClientReportTest` still grouped support-safe client report assertions with bulky schedule-apply and schedule-rollback-preview report payloads. The next safe test-only cleanup is to move those reusable payload fixtures into a dedicated support trait while preserving repository assertions, sanitization expectations, support-summary behavior, production code, protocol behavior, schema, and live-site state.

Implementation status: implemented locally as a test-only fixture split. Schedule Apply client-report payload builders now live in `tests/support/remote-action-repository-client-report-fixtures.php`, while Schedule Rollback Preview client-report and rollback-readiness aggregate fixtures live in `tests/support/remote-action-repository-client-report-rollback-fixtures.php`. `tests/RemoteActionRepositoryClientReportTest.php` composes those traits while keeping the existing test methods and expected support-safe context assertions. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionRepositoryClientReportTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading, production files, remote-action repository behavior, protocol behavior, release behavior, deployment state, backups, restore, cleanup/delete apply behavior, credentials, Drime behavior, or live-site state.

### Remote Action Capabilities Test Fixture Structure Slice

`RemoteActionCapabilitiesTest` still repeated remote-action capability construction and Alynt scan/upload schedule summary fixtures across schedule preview, schedule apply, rollback-preview, cleanup-preview, and forbidden-field tests. The next safe test-only cleanup is to move those reusable fixture builders into a dedicated test support trait while leaving capability assertions, edge-case payloads, production code, protocol behavior, schema, sanitization behavior, support decisions, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Remote-action capability fixture builders now live in `tests/support/remote-action-capabilities-test-fixtures.php`, and `tests/RemoteActionCapabilitiesTest.php` uses that trait while keeping the existing test methods and expected sanitization/support decisions. No production PHP, assets, UI strings, protocol behavior, database schema, sanitization behavior, remote-action permission change, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `RemoteActionCapabilitiesTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Admin Rendering Remote Action Double Structure Slice

The admin rendering test support still mixed the polling-state rendering harness with a remote-action repository double used only by schedule rollback-preview rendering assertions. The next safe test-only cleanup is to move that remote-action double into its own support file while leaving rendering assertions, fixtures, production helper calls, protocol behavior, schema, remote-action behavior, database state, release behavior, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. The admin rendering remote-action repository double now lives in `tests/support/admin-page-rendering-remote-action-double.php`, and the shared admin rendering bootstrap requires that support file alongside the existing polling-state rendering harness and remote-action rendering fixtures. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused admin rendering coverage that uses the rollback-preview action double passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### Status Classifier Test Fixture Structure Slice

`StatusClassifierTest` remains one of the larger PHPUnit files because it combines classifier assertions with reusable site, snapshot, healthy-payload, and source-summary fixture builders. The next safe test-only cleanup is to move those fixture builders into a dedicated test support trait while leaving classifier assertions, fixtures, production code, protocol behavior, schema, classification behavior, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Status classifier fixture builders now live in `tests/support/status-classifier-test-fixtures.php`, and `tests/StatusClassifierTest.php` requires that support file while keeping the existing test methods and expected classifications. No production PHP, assets, UI strings, protocol behavior, database schema, classification behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- focused `StatusClassifierTest` coverage passes unchanged;
- PHP syntax, lint, full tests, build, and whitespace checks pass;
- the split remains test-only and does not alter runtime class loading or release behavior.

### V2.3 Schedule Rollback Preview Design Slice

Implementation status: design complete in `docs/V2_3_SCHEDULE_ROLLBACK_PREVIEW_DESIGN.md`. Dashboard-side non-mutating dispatch/UI controls, audit labels, action-history summaries, and Diagnostics support aggregates are released and deployed through dashboard `0.1.43`. They remain capability-gated and hidden unless the latest client status explicitly advertises `schedule_rollback_preview`. Client-side release/enablement is disabled by default and one explicitly approved PureCleanse pilot proof was completed on 2026-09-30. Broad enablement and mutating `schedule_rollback` runtime behavior remain unavailable.

The completed PureCleanse proof confirmed the dashboard can ask a client whether one previous `schedule_apply` action is still safely rollback-previewable, while preserving the rule that the client owns current-state validation and no schedule changes occur during preview. The next possible rollback-adjacent design step is not more preview code; it is a separately approved decision about whether to design mutating `schedule_rollback` at all, with a new protocol/threat-model gate.

Design boundaries:

- keep `schedule_rollback_preview` dashboard dispatch/UI preview-only and client-capability-gated;
- keep any broad client-side rollback-preview enablement separately gated after the completed PureCleanse-only pilot;
- keep `schedule_rollback` unavailable until after rollback preview is implemented, proven, and separately approved;
- limit all future preview planning to `alynt_scan_upload`;
- reject free-form cadence, raw cron, WP-Cron arrays, option names/values, filesystem paths, commands, Drime identifiers, credentials, and arbitrary settings payloads;
- require client-owned rollback metadata, a source apply action reference, metadata fingerprint, expiry checks, and current schedule fingerprint revalidation;
- keep rollback-preview UI non-mutating and distinct from rollback apply.

Dashboard-side result display requirements:

- show rollback-preview results as "would restore" evidence, not as an executed rollback;
- show whether the preview would change cadence or whether no cadence change would be needed;
- keep "preview only" and "rollback execution unavailable" copy visible in the action history details;
- avoid exposing raw fingerprints, credentials, Drime identifiers, paths, commands, or arbitrary client payload fields.

Dashboard-side diagnostics/audit display requirements:

- show the local operator action for rollback preview as `Preview Schedule Rollback` in Diagnostics;
- include aggregate `schedule_rollback_preview` counts in support-safe Diagnostics action summaries;
- keep the stored audit context redacted and support-safe;
- preserve the existing stored audit slug and action history storage behavior.

Acceptance criteria for the design slice:

- a dedicated rollback-preview design artifact exists;
- roadmap/protocol references point to it without approving runtime behavior;
- no rollback execution, schedule mutation, backup creation, restore, cleanup/delete action, credential change, Drime mutation, or broad client enablement is introduced.

### V2.3 Schedule Rollback Decision Slice

Implementation status: planning-only decision record created in `docs/V2_3_SCHEDULE_ROLLBACK_DECISION.md`. After the completed PureCleanse rollback-preview proof, the decision is to keep mutating `schedule_rollback` deferred. Any future rollback execution must start as a fresh design slice with protocol/threat-model updates, explicit client-local rollback opt-in, fresh successful preview requirement, current-state revalidation, tests, release gates, and separate live enablement approval. No runtime behavior, UI control, protocol field, client opt-in, release, deployment, or live-site state is changed by this decision record.

### V2.3 Schedule Rollback Preview Post-Release Proof Slice

After dashboard `0.1.43`, the next safe rollback-adjacent slice is proof and hardening of the already bounded, non-mutating preview path. This is not rollback execution.

Implementation status: completed and released. The approved PureCleanse rollback-preview proof completed successfully on 2026-09-30, the pilot was returned to its intended cadence, rollback-preview support was disabled again, and `schedule_rollback` remains unavailable. The follow-up display-only UI slice is also implemented: Site Detail keeps the latest successful rollback-preview proof visible as support-safe history evidence even after rollback-preview support is disabled, without rendering a rollback execution control or implying current client rollback-preview support. No backup creation, restore, cleanup/delete, Drime mutation, credential change, database migration, broad live-site enablement, or rollback execution was introduced.

Post-uploader-`0.5.21` rollout note: the client-side non-mutating `schedule_rollback_preview` support is released and deployed across the tracked active client rollout set, but it remains disabled by default and hidden from the dashboard unless a client explicitly advertises rollback-preview support. A live proof cannot reuse old Schedule Apply metadata because the client expires rollback metadata one hour after capture. The approved PureCleanse runtime proof completed on 2026-09-30 by creating fresh rollback metadata with a guarded Schedule Apply, running the non-mutating rollback preview while that metadata was still valid, returning the pilot to its intended cadence, and disabling the pilot opt-in again.

Completion note: On 2026-09-30, after user-confirmed restore points, the PureCleanse pilot temporarily enabled rollback-preview opt-in, changed `alynt_scan_upload` from `every_15_minutes` to `every_30_minutes`, ran `schedule_rollback_preview` successfully with a "would restore to every 15 minutes" result, confirmed the preview did not change the active schedule, returned PureCleanse to `every_15_minutes`, disabled rollback-preview opt-in, and verified PureCleanse ended Working with queue count 0, failed count 0, warnings 0, `rollback_preview_supported=false`, and `rollback_supported=false`.

Follow-up local UI slice: keep the latest successful rollback-preview proof visible in the Site Detail Schedule Management panel as support-safe history evidence even after rollback-preview support is disabled again. This must remain display-only, must not add a rollback execution control, and must not imply current client rollback-preview support.

Scope:

- verify the live dashboard keeps rollback-preview controls hidden for clients that do not advertise support;
- make the Site Detail schedule panel explicitly show the pilot-readiness reason: hidden until client support, supported but waiting for successful apply metadata, or ready for non-mutating preview;
- include support-safe Diagnostics aggregate counts for rollback-preview hidden/supported states so pilot readiness can be checked without exposing client identifiers, schedule IDs, raw payloads, credentials, or Drime details;
- verify a separately approved client build advertises rollback-preview support only after explicit client-local opt-in;
- verify old/expired Schedule Apply rollback metadata blocks rollback-preview dispatch with a fail-closed reason;
- prove on one low-risk pilot that rollback preview can reconcile into dashboard action history without changing schedule cadence;
- confirm failure states such as expired metadata, changed current schedule fingerprint, missing metadata, unsupported schedule, and unsafe previous cadence remain visible and non-mutating;
- record pilot findings in support-safe documentation without storing secrets, raw cron payloads, paths, credentials, or Drime identifiers.

Exit criteria:

- one pilot demonstrates successful non-mutating preview or a concrete fail-closed reason;
- the dashboard remains healthy after scheduled polling;
- no rollback apply control is rendered;
- `schedule_rollback` remains rejected/unavailable;
- no live-site broad rollout, backup creation, restore, cleanup/delete action, schedule mutation, credential change, Drime mutation, database migration, or deployment occurs without a separate release/deploy approval gate.

### V2.3 Schedule-Control Stabilization Slice

After guarded Schedule Apply reached the live dashboard, the next safe dashboard-side slice is stabilization rather than a new remote power. This slice should harden operator wording and tests around the already released V2.3 boundary:

Implementation status: implemented in the current dashboard codebase and present in the `0.1.50` release line. Site Detail copy, Schedule Apply confirmation copy, and Remote Action History rendering now state that Schedule Apply is limited to future Alynt uploader scan cadence, rollback execution remains unavailable, rollback metadata is evidence-only, and missing cadence evidence is pending client report rather than a known transition. The slice did not add backup creation, WPvivid schedule changes, server-runner schedule changes, cleanup, delete, restore, rollback dispatch, arbitrary cron, Drime credential storage, or live-site behavior.

- make Site Detail copy unmistakable that Schedule Apply changes only future Alynt uploader scan cadence, even when the client-facing capability label remains `Alynt scan/upload`;
- make rollback status explicit: rollback metadata may be displayed as support evidence, but no `schedule_rollback` action, button, dispatch path, or runtime behavior is available in this version;
- make remote-action history details describe schedule apply as Alynt uploader scan-cadence only so operators do not infer upload-worker cadence, WPvivid, server-runner, Drime, retention, cleanup, delete, restore, or credential control;
- avoid misleading `Unknown → target cadence` history rows while waiting for the client to echo the current cadence; missing cadence evidence should be called out as pending client report rather than treated as a known cadence;
- preserve the existing signed preview/apply behavior, fresh-preview requirement, action history storage, support-safe rollback-readiness metadata display, and per-client Schedule Apply opt-in gate;
- avoid client protocol changes unless a later rollback-readiness or runtime rollback slice is separately planned and approved.

Acceptance criteria:

- Site Detail Schedule Management copy says rollback is unavailable in this release and no rollback control is rendered.
- Apply confirmation copy says the action is only for future Alynt uploader scan cadence and rollback is unavailable.
- Remote Action History labels Schedule Apply details as Alynt uploader scan-cadence only and treats rollback metadata as evidence-only.
- Remote Action History shows a pending-client-report explanation instead of a definitive transition when the current or applied cadence has not yet been reported.
- Existing schedule preview/apply tests pass, and targeted tests cover the hardened wording.
- No remote backup creation, WPvivid schedule changes, server-runner schedule changes, cleanup, delete, restore, rollback dispatch, arbitrary cron, Drime credential storage, or live-site changes are introduced.

### Remote Action History Filtering / Grouping UI Slice

Request Backup Now, Schedule Preview, Schedule Apply, stale reconciliation, rate limiting, and rollback-readiness metadata have made the Site Detail Remote Action History increasingly useful but increasingly noisy. Before adding any runtime rollback capability, add a small dashboard-only filtering slice so operators can review the action audit trail without changing storage, dispatch, client protocol, or remote permissions.

Implementation status: implemented and released through dashboard `0.1.45`. The Site Detail Remote Action History now supports read-only allowlisted GET filters for action type and dashboard action state, a filtered-count summary, reset link, and empty filtered state. Filtering is applied only to already loaded recent history rows at render time and does not change storage, repository queries, retention, reconciliation, polling, dispatch, client payload contracts, protocol, schema, credentials, Drime behavior, backup creation, restore, delete, cleanup, or remote-action permissions.

Implement the smallest safe UI improvement:

- Add read-only GET filters above the Site Detail Remote Action History table for action type and dashboard action state.
- Keep filters allowlisted and sanitized. Unknown filter values should fall back to `All`.
- Filter only the already loaded recent history rows at render time; do not change database schema, repository queries, retention, reconciliation, polling, dispatch, or client payload contracts.
- Show a clear filtered-count summary and a reset link when a filter is active.
- Show an empty filtered state that explains no rows match the current filters, rather than implying no history exists.
- Keep support-safe redaction, rollback metadata wording, and all existing action detail labels.

Acceptance criteria:

- Site Detail action history can be filtered to `Request Backup Now`, `Schedule Preview`, or `Schedule Apply`.
- Site Detail action history can be filtered by dashboard state such as `Succeeded`, `Failed`, `Stale`, or `Rate limited`.
- The filter form preserves the current dashboard page, site tab, and site id.
- Invalid or unexpected filter query values do not alter output or create unsafe markup.
- Existing remote-action rendering tests pass, with added coverage for action/state filtering and empty filtered results.
- No live-site, release, deployment, push, protocol, schema, credential, Drime, backup, restore, delete, cleanup, or new remote-action behavior is introduced.

### Compact Remote Action History Details UI Slice

After adding action-history filters, keep the Site Detail history table readable by reducing default row density. Schedule Apply rows can include cadence transitions, next-run estimates, scope warnings, rollback-readiness metadata, reason codes, and expiry timestamps. Those details are useful for audit/support, but they should not dominate the table by default.

Implementation status: implemented and released through dashboard `0.1.45`. Remote Action History rows now keep full support-safe details available while showing compact default summaries for longer schedule-management rows. Long secondary details are placed behind a native `<details>` disclosure pattern, short Request Backup rows remain flat, and the change does not add JavaScript requirements or alter action labels, result labels, rollback-readiness wording, filtering, redaction, repository storage, dispatch, polling, protocol behavior, or remote-action permissions.

Implement a small dashboard-only rendering slice:

- Keep the existing support-safe detail text available in the row.
- Show a compact Details summary by default, prioritizing cadence transition/count evidence.
- Move long secondary details behind a native disclosure pattern inside the Details cell.
- Preserve all existing action labels, result labels, rollback-readiness wording, filtering, redaction, repository storage, dispatch, polling, and protocol behavior.
- Do not add JavaScript requirements for this interaction.

Acceptance criteria:

- Long Schedule Apply rows show the cadence transition as the default Details-cell summary.
- Rollback metadata, next-run evidence, and scope wording remain available behind an expandable disclosure.
- Short Request Backup rows remain unchanged when they do not need expansion.
- Rendering tests cover the compact default summary and the retained full support-safe detail text.
- No live-site, release, deployment, push, protocol, schema, credential, Drime, backup, restore, delete, cleanup, rollback, or new remote-action behavior is introduced.

### Remote Action History Cleanup Detail Structure Slice

After repeated remote-action UI additions, the remote-action history helper briefly exceeded the preferred production-file size threshold. Keep the behavior stable but split cleanup-preview detail formatting into its own focused trait so the table rendering and generic action labels remain easier to maintain.

Implementation status: implemented, released, and deployed through dashboard `0.1.56` as a structure-only slice. Cleanup-preview history detail helpers now live in `includes/traits/trait-admin-page-remote-action-history-cleanup-details.php`, loaded before the main remote-action-history helper. The change preserves existing cleanup-preview detail text, action labels, table markup, filtering, disclosure behavior, redaction, dispatch, protocol behavior, schema, credentials, Drime behavior, backup creation, restore, cleanup/delete, release, deployment, and live-site state.

Acceptance criteria:

- The remote-action-history helper returns below the preferred production-file size threshold.
- Cleanup-preview Remote Action History details render exactly through the existing helper calls.
- The new trait is loaded before the main helper in the plugin bootstrap.
- Targeted Remote Action History rendering tests pass.
- No live-site, release, deployment, push, protocol, schema, credential, Drime, backup, restore, delete, cleanup, or new remote-action behavior is introduced.

### V2.4 Cleanup And Retention Design Slice

Implementation status: design-only decision record created in `docs/V2_4_CLEANUP_RETENTION_DESIGN.md`. The recommended first V2.4 direction is a narrowly scoped, client-owned local cleanup preview/apply model for Alynt uploader-owned temporary artifacts only. Runtime implementation, release, deploy, live enablement, Drime deletion, remote retention mutation, backup-set deletion, restore, arbitrary filesystem browsing, and dashboard Drime credential storage remain unavailable until later protocol/threat-model updates and explicit approval gates.

Cleanup-preview implementation status: the non-mutating `cleanup_preview` sub-slice is documented in `docs/V2_4_CLEANUP_PREVIEW_IMPLEMENTATION_PLAN.md`, with the capability/action boundary reflected in `docs/PROTOCOL_V2.md` and `docs/THREAT_MODEL_V2.md`. The companion uploader preview action and dashboard consumer/UI/dispatch slice have been implemented, released, deployed, and proven. The dashboard sanitizes `cleanup_management`, shows a capability-gated Site Detail `Cleanup Preview` control, dispatches only the allowlisted preview request for `safe_local_uploader_owned` + `uploader_temp_artifacts`, renders support-safe action-history details, and reports aggregate diagnostics/support counts. The active client rollout set reports cleanup preview support only; `cleanup_apply`, Drime retention/delete, backup-set deletion, restore, arbitrary filesystem browsing, dashboard Drime credentials, and live enablement for mutating cleanup remain behind later gates.

Cleanup-apply decision: `docs/V2_4_CLEANUP_APPLY_DECISION.md` defers `cleanup_apply` because the current preview evidence proves safe visibility but not enough recurring cleanup need to justify a destructive runtime action. The next safe V2.4 work should remain preview-only observability polish or a different non-destructive planning pass unless later preview evidence shows recurring, material, safely removable uploader-owned artifacts.

Cleanup-preview observability polish status: implemented and released in the current dashboard line as a dashboard-only UI/copy hardening slice after the cleanup-apply deferral decision. Site Detail explicitly states that cleanup apply is unavailable in this release and that preview results are evidence only; Remote Action History cleanup-preview details use the same evidence-only framing. This does not change protocol behavior, dispatch, storage, polling, classification, cleanup capability gating, client actions, Drime behavior, release, deploy, or live-site state.

Cleanup-preview latest-evidence summary status: implemented, released, deployed, and post-release monitored through dashboard `0.1.57` as a display-only Site Detail follow-up to the V2.4 cleanup-preview line. The panel shows the latest sanitized `cleanup_preview` summary when the latest client status payload reports one, including aggregate eligible-item count, approximate byte total, preview creation/expiry timestamps when available, and allowlisted category summaries. It hides when no latest cleanup-preview evidence is reported and preserves the existing preview-only action boundary.

Acceptance criteria:

- Site Detail Cleanup Preview shows the latest redacted cleanup-preview evidence summary when present in the latest sanitized client report.
- The summary states that the evidence is preview-only and that no cleanup apply/delete/retention/restore/Drime action is available.
- The summary remains hidden when latest cleanup-preview evidence is not present.
- Rendering tests cover positive and hidden states and prove path-like/raw identifiers are not displayed.
- No protocol, schema, storage, dispatch, classifier, polling, credential, Drime, backup, restore, cleanup/delete, release, deployment, or live-site behavior changes are introduced.

Restore-preparation evidence status: planning-only design created in `docs/V2_6_RESTORE_PREPARATION_EVIDENCE_DESIGN.md`, followed by an implemented, released, and deployed dashboard-side Restore Readiness Evidence Consumer in dashboard `0.1.52`. The companion uploader producer was released and rolled out through uploader `0.5.25`, and the dashboard has observed support-safe `restore_readiness` evidence during scheduled polling. Support-safe Site Detail evidence, compact Sites-row hints, Diagnostics and support-copy aggregate counts, an operator-facing Diagnostics summary row, and source-level Server/WPvivid aggregate counts are implemented, released, deployed, and post-release monitored through dashboard `0.1.55`. The recommended restore-adjacent direction remains read-only readiness evidence, not restore execution. The current visibility polish line is complete for the existing evidence model; any next restore-adjacent slice should start as a separate approved planning decision and must not download, stage, unpack, overwrite, import, restore, delete, browse paths, request Drime credentials, mutate production data, add restore-preparation runtime behavior, or present evidence as a restore guarantee.

Restore-readiness Sites-row hint status: implemented, released, deployed, and post-release monitored through dashboard `0.1.54` and retained in `0.1.55` as a display-only follow-up to the evidence consumer and Diagnostics aggregate slices. The Sites tab shows a compact `Restore evidence` hint when the latest sanitized client payload includes optional `restore_readiness` evidence. The hint summarizes the overall evidence state and source-level candidate states without exposing candidate references, paths, filenames, package names, Drime identifiers, credentials, or restore controls. The detailed evidence remains on Site Detail, and the hint does not affect classification, polling, protocol behavior, schema, credentials, Drime behavior, backup creation, restore, cleanup/delete, release, deploy, or live-site state.

Restore-readiness Diagnostics summary status: implemented, released, deployed, and post-release monitored through dashboard `0.1.55` as a small display-only polish slice after the Sites-row hints. The Diagnostics restore-readiness aggregate panel includes one operator-facing summary row, such as "Evidence incomplete across reporting sites," so operators can scan fleet readiness posture before reading individual aggregate counts. This slice reuses existing support-safe aggregate counts only, does not store new data, does not change the status payload schema, does not expose candidate references, paths, filenames, package names, Drime identifiers, credentials, or raw payloads, and does not add restore controls or imply restore guarantees.

Restore-readiness source-level Diagnostics status: implemented, released, deployed, and post-release monitored through dashboard `0.1.55` as a follow-up display-only aggregate slice. Diagnostics counts server-runner and WPvivid restore-readiness candidates separately so operators can see which backup source is missing complete restore evidence across the fleet. This slice uses only the existing sanitized `restore_readiness.candidates[].source` and state fields, remains aggregate-only and support-safe, and does not expose site labels, domains, candidate references, paths, filenames, package names, Drime identifiers, credentials, raw payloads, restore controls, or restore guarantees.

Recommended next boundary:

- keep V2.4 at `cleanup_preview` unless a separate high-risk cleanup-apply planning decision is explicitly approved;
- keep cleanup apply behind a fresh preview fingerprint, expiry, client-side revalidation, idempotency, client-local opt-in, operator confirmation, and restore-point expectations;
- defer Drime retention/delete and backup-set deletion to V2.5 or later;
- treat any further restore-adjacent work as a separate approved planning decision rather than the next default implementation path;
- do not add dashboard controls, action types, capability advertisements, schema changes, live-site changes, or release behavior from the design slice alone.

The repository path and package identity below were explicitly confirmed before scaffolding. Broad feature implementation should still begin with a fresh restore point or an equivalent baseline snapshot.

## Repository And Package Identity Confirmation Gate

The proposed local path was checked on 2026-08-09 and does not currently exist.

| Identity | Recommended value | State |
| --- | --- | --- |
| Plugin name | `Alynt Drime Backups Dashboard` | Confirmed project decision |
| Local repository path | `C:\Development\WordPress\Plugins\alynt-drime-backups-dashboard` | Available; approval required |
| Repository name | `alynt-drime-backups-dashboard` | Approval required |
| Intended GitHub repository | `NichlasB/alynt-drime-backups-dashboard` | Recommended; create/verify before updater setup |
| Installed folder / plugin slug | `alynt-drime-backups-dashboard` | Approval required |
| Main plugin file | `alynt-drime-backups-dashboard.php` | Approval required |
| Initial version | `0.1.0` | Recommended |
| Text domain | `alynt-drime-backups-dashboard` | Recommended |
| Composer package | `alynt/alynt-drime-backups-dashboard` | Recommended |
| PHP package docblock | `Alynt_Drime_Backups_Dashboard` | Recommended |
| PHP namespace | None in v1; use the sibling-compatible class prefix `Alynt_Drime_Backups_Dashboard_` | Recommended |
| Function/option/hook prefix | `alynt_drime_backups_dashboard_` | Recommended |
| Constant prefix | `ALYNT_DRIME_BACKUPS_DASHBOARD_` | Recommended |
| Dashboard REST namespace | `alynt-drime-backups-dashboard/v1` | Recommended |
| Client status REST namespace | `alynt-drime-backups-uploader/v1` | Recommended |
| Minimum WordPress | `6.0` | Match uploader baseline |
| Minimum PHP | `7.4` | Match uploader baseline |
| Distribution | Owner-managed GitHub release ZIP through Alynt Plugin Updater | Recommended |
| Updater header | `GitHub Plugin URI: NichlasB/alynt-drime-backups-dashboard` | Add only after repository identity exists |

Do not initialize a folder, Git repository, GitHub repository, plugin header, package manifest, or updater workflow until the user approves this identity table. The GitHub owner/repository must be verified after creation rather than inferred from the plugin name.

## Purpose

Provide one WordPress control-center screen that shows whether enrolled sites running Alynt Drime Backups Uploader are reporting healthy, redacted backup-upload status.

Version 1 should answer:

- Which sites are paired and reporting?
- Which sites are healthy, need attention, are not reporting, or have an incompatible payload?
- Which uploader version and status schema is each site using?
- Which sites have failed, queued, or active uploads?
- Which sites report source or cron warnings?
- When did the dashboard last successfully receive status for each site?

## Post-Alpha Backup Freshness And Drime Inventory Slice

The first implemented dashboard baseline intentionally focuses on pairing, read-only polling, coarse uploader health, and redacted status snapshots. It does not yet answer the operator's more important restore-readiness question: whether each enrolled site currently has fresh recoverable backups in Drime by backup source.

Add a follow-up observability slice before treating the dashboard as operationally complete. This slice should remain read-only and should not give the dashboard Drime API credentials or direct Drime mutation powers.

The improved dashboard should answer, per enrolled site and per source:

- When was the latest server-runner/generic-outbox backup package created?
- When was the latest server-runner/generic-outbox backup package uploaded to Drime?
- How many current server-runner/generic-outbox backup package sets are visible in the approved Drime destination?
- When was the latest WPvivid backup set created?
- When was the latest WPvivid backup set uploaded to Drime?
- How many current WPvivid backup sets are visible in the approved Drime destination?
- Is the newest backup evidence fresh enough for the site's expected schedule and retention posture?

Preferred architecture:

1. Extend the uploader's authenticated read-only status payload with optional additive fields, such as a `backup_sources` summary, while keeping path mode disabled.
2. Let the uploader compute per-source freshness and Drime inventory evidence from its local registry, package sidecars, source metadata, and safe Drime destination checks using its existing Drime credentials.
3. Keep the dashboard as a consumer of redacted summaries only. The dashboard must not collect, store, forward, or use client Drime API tokens.
4. Update dashboard validation, snapshot storage, status classification, Sites list, Site Detail, Attention, Diagnostics, and support-copy/export areas to display this evidence clearly.
5. Preserve backward compatibility with schema-1 clients by treating the new fields as optional until a later protocol/schema version requires them.

Useful UI language should separate historical activity from current restore confidence. For example, `uploaded_count` may remain as a lifetime registry count, but the primary operator summary should emphasize latest successful upload age and current remote inventory count for `server` and `wpvivid` separately.

Dashboard-side implementation status: the dashboard now has a local, additive schema-1 consumer slice for optional `backup_sources` summaries. This covers payload allowlisting, status classification, Sites list summaries, Site Detail snapshot evidence, aggregate diagnostics, support-safe export counts, protocol documentation, and focused unit coverage. The companion uploader-side producer work remains a separate implementation slice.

### Compact Sites Backup Evidence UI Slice

Operational use showed that the Sites tab backup evidence column became too dense after source-level freshness, schedule-aware policy, inventory counts, WPvivid activity hints, and explanatory reason lines were all added to each table row. The data is useful, but the default list view should be an at-a-glance monitor first and a detailed explanation surface second.

Implementation status: implemented and released. The Sites tab now renders compact backup-health summaries and short per-source evidence rows while retaining detailed source evidence on Site Detail. This was display-only and did not change classification, status payload handling, polling, credentials, protocol behavior, or remote-action permissions.

Implement a small dashboard-only UI slice that preserves all existing classifications, source-policy logic, status payload handling, and read-only boundaries while making healthy rows easier to scan:

- Add a compact row-level backup health summary such as `Backups: On schedule`, `Backups: WPvivid overdue`, `Backups: Missing evidence`, or `Backups: Unknown`.
- Replace verbose Sites-list source prose with compact source rows, for example `Server runner · Fresh · 14 hours ago · 1 set · expected ≤36 hours` and `WPvivid · Within policy · 5 days ago · 12 sets · expected ≤9 days`.
- Remove default `Why:` and full `Expected:` explanation blocks from the Sites table. Keep operator reasons, exact policy wording, WPvivid activity explanations, evidence type, and warnings available on the Site Detail screen.
- Keep healthy rows visually quiet. Warning, overdue, missing-evidence, and unknown states should remain visibly stronger than healthy detail.
- Do not change client protocol, status classification, schedule-detected freshness policy, remote-action capabilities, credential handling, polling behavior, or dashboard security boundaries.

Acceptance criteria:

- Sites-tab rows can be scanned quickly to determine whether each site's backups are on schedule.
- The Backup Evidence column uses a short backup-health summary plus compact per-source rows by default.
- Detailed source explanations remain available on Site Detail.
- Healthy rows use less vertical space than the previous verbose source evidence block.
- Rendering tests cover the compact output and prove verbose reason labels are not shown in the Sites-list helper.

### Transient Attention / Recovery History Slice

Operational rollout showed that a site can briefly enter `Needs attention` for a concrete reason, such as stale server-runner evidence after a missed scheduled window, then self-recover after the next successful scheduled run and dashboard poll. The current dashboard correctly shows the live state, and Site Detail already has recent status snapshots, but the recovery story is not obvious enough after the row returns to `Working`. Operators should not need to reconstruct transient incidents from local rollout tracker notes.

Implementation status: implemented, released, and deployed through dashboard `0.1.47`. Site Detail now renders a compact `Attention / Recovery History` panel from retained dashboard snapshot summary fields, without adding storage, schema, protocol, credential, polling, remote-action, backup, restore, cleanup/delete, Drime, or live-site changes.

Recommended implementation path:

- Start with Site Detail, not the Sites list. Add a compact `Attention / Recovery History` panel that summarizes recent meaningful status transitions such as `Working -> Needs attention` and `Needs attention -> Working`.
- Prefer deriving transitions from retained snapshot history and existing site status fields before adding schema or storage. A database migration should require a separate justification.
- Keep the Sites tab visually quiet. If needed after the Site Detail proof, add only a small hint such as `Recovered from attention 2 days ago` for recently recovered sites; do not add long explanations back into compact rows.
- Preserve active-alert priority. If a site is currently `Needs attention`, the current reason remains primary and any recovery history is secondary context.
- Include source-aware reasons when they are already available from sanitized snapshot data, for example server-runner stale evidence, WPvivid outside detected policy, missing evidence, polling failure, or cron concern. If the snapshot does not contain enough context, say `Status changed; see snapshot details` rather than guessing.
- Add Diagnostics/support-copy aggregates such as recent recoveries and repeated attention transitions only after the Site Detail panel is proven useful.
- Keep all output support-safe and redacted. Do not expose credentials, local paths, Drime identifiers, raw option blobs, or untrusted payload values without existing sanitization.

### Diagnostics Attention/Recovery Aggregate Slice

After the Site Detail `Attention / Recovery History` panel shipped, the next safe dashboard-only improvement is aggregate Diagnostics visibility. Operators should be able to tell whether retained snapshot history shows recent recoveries or repeated attention transitions across the fleet without opening each site and without exposing client identifiers.

Implementation status: implemented, released, and deployed through dashboard `0.1.48`. Diagnostics and support copy now include support-safe aggregate retained-history counts and a readable attention-history summary so operators can distinguish quiet history, recent recovery, repeated attention, and insufficient retained history without exposing client identifiers or raw payloads.

Scope:

- derive aggregate counts only from retained dashboard-owned snapshot summary rows;
- count records with enough retained history, records whose latest retained status recovered to `working` from a recent attention status, and records with repeated transitions into attention states;
- include the aggregate in Diagnostics and support-copy output without listing site labels, domains, raw payload JSON, paths, credentials, Drime identifiers, schedule IDs, or action fingerprints;
- keep the query path bounded by the existing per-site recent snapshot limit;
- do not change schema, protocol, polling behavior, status classification, pairing, credentials, remote actions, backup/restore/delete/cleanup behavior, Drime behavior, release packaging, or live-site state.

Acceptance criteria:

- Diagnostics shows support-safe aggregate recent recovery / repeated-attention counts.
- Support Copy includes the same aggregate under existing redacted `counts` output.
- Diagnostics and Support Copy include a coarse support-safe interpretation of the aggregate counts so operators can quickly tell whether retained history is quiet, recently recovered, repeatedly entering attention, or not yet sufficient.
- Existing Diagnostics, snapshot, and rendering tests pass, with added coverage for the aggregate.
- No live-site, release, deploy, push, schema, protocol, credential, Drime, backup, restore, delete, cleanup, rollback, or new remote-action behavior is introduced.

### Diagnostics Attention History Metrics Structure Slice

After the broader file-structure pass, `trait-diagnostics-site-metrics.php` became the largest remaining production helper. Keep behavior stable but split the retained Attention/Recovery History aggregate helpers into their own focused trait so site count/polling metrics and retained-history transition logic remain easier to maintain independently.

Implementation status: implemented, released, and deployed through dashboard `0.1.56` as a structure-only slice. Attention/recovery aggregate helpers now live in `includes/traits/trait-diagnostics-attention-history-metrics.php`, loaded before the main site-metrics trait. The change preserves existing Diagnostics/support-copy counts, summary codes, site status counting, polling metrics, backup-source metrics, schedule/cleanup/restore aggregates, redaction, protocol behavior, schema, credentials, Drime behavior, backup creation, restore, cleanup/delete, release, deployment, and live-site state.

Acceptance criteria:

- `trait-diagnostics-site-metrics.php` is comfortably below the preferred production-file size threshold.
- Attention/recovery Diagnostics and support-copy aggregate tests continue to pass.
- The new trait is loaded before the main site-metrics trait in the plugin bootstrap.
- No live-site, release, deployment, push, protocol, schema, credential, Drime, backup, restore, delete, cleanup, or new remote-action behavior is introduced.

Suggested tests:

- Snapshot/repository transition derivation returns bounded, chronological, meaningful changes and ignores repeated same-status snapshots.
- Site Detail rendering shows a recovered transient incident when recent snapshots contain `needs_attention -> working`.
- Active `Needs attention` rendering still prioritizes the current reason over historical recovery context.
- Missing or insufficient historical context renders an empty/quiet state rather than a false explanation.
- Existing Sites, Attention, Diagnostics, polling, classification, and remote-action tests continue to pass.

Acceptance criteria:

- A transient issue that later self-recovers can be explained from dashboard history on the individual Site Detail screen.
- The feature remains dashboard-local, read-only, schema-compatible unless separately approved, and safe for existing enrolled clients.
- Healthy Sites-list rows remain compact; detailed explanations stay on Site Detail unless a later, deliberately small row hint is approved.
- No live-site, release, deployment, push, protocol, credential, Drime, backup, restore, delete, cleanup, or new remote-action behavior is introduced by the local implementation slice.

### Dashboard-Side Backup Freshness Policy Slice

Operational rollout showed that the first backup-source classifier was too strict for WPvivid on sites where WPvivid is intentionally scheduled weekly, biweekly, or as a secondary/manual safety layer. The initial source-level rule treated any configured source with uploader-reported `stale` freshness as `Needs attention`, even when the server-runner source was fresh, queues were empty, failed counts were zero, cron was healthy, and WPvivid upload evidence was only slightly older than the uploader's conservative 36-hour freshness window.

Implementation status: implemented and released. The dashboard keeps server-runner evidence strict while treating WPvivid as a separately cadenced source with a 15-day fallback policy window, preserving hard attention states for current failures, missing required evidence, cron problems, incompatible payloads, polling failures, and unrelated warnings.

Implement a dashboard-side freshness-policy layer before treating source-level attention counts as reliable operational alarms:

- Keep server-runner/generic-outbox evidence strict. Its freshness should continue to reflect the uploader-reported source freshness window unless a later explicit policy UI is added.
- Treat WPvivid as a separately cadenced source. Dashboard v1 should use a default WPvivid policy window of 15 days so weekly and biweekly WPvivid schedules do not continuously produce false `Needs attention` rows.
- Preserve hard attention states for configured sources with failed source uploads, missing upload evidence, non-queue warnings other than `source_latest_upload_stale` while still within the dashboard policy, or WPvivid evidence older than the dashboard policy window.
- Display the dashboard policy in Sites-list and Site-detail evidence so operators can distinguish `Fresh`, `Within policy`, `Stale`, `No upload evidence`, and `Not configured` without assuming the dashboard has performed a direct Drime audit.
- Keep the change dashboard-side, additive, and read-only. Do not give the dashboard Drime API credentials, do not add remote actions, and do not require a status schema-version change unless a future uploader field becomes mandatory.

Acceptance criteria:

- A WPvivid source that reports `freshness_status: stale`, has upload evidence, has no failed source uploads, and has `latest_upload_age_seconds` within 15 days classifies as `Working` when no other payload condition needs attention.
- A WPvivid source older than 15 days still classifies as `Needs attention`.
- A server source that reports stale still classifies as `Needs attention`.
- The Sites list and Site detail views show the source as within the dashboard policy and include the expected window.
- Existing schema-1 clients remain compatible and the v1 read-only boundary remains unchanged.

### WPvivid Schedule-Aware Freshness Policy Slice

The fixed 15-day WPvivid policy is intentionally safer than the uploader's conservative 36-hour source freshness window, but it is still a dashboard-wide fallback. Sites that intentionally run WPvivid every month can still look stale too early, while sites that run WPvivid daily may be allowed too much drift.

Implementation status: implemented and released as an additive dashboard consumer for optional schema-1 `backup_sources.wpvivid.schedule_policy` summaries. The dashboard uses detected positive policy windows when present and falls back to the 15-day WPvivid policy when missing or undetected; it still receives no Drime credentials and performs no remote action.

Implement a small cross-plugin, read-only schedule-awareness slice:

- The uploader reports an optional redacted `backup_sources.wpvivid.schedule_policy` summary on every authenticated status poll.
- The uploader derives the summary from local WPvivid schedule state and WordPress cron schedule metadata only. It must not expose raw WPvivid option blobs, local paths, task IDs, backup names, credentials, database values, or Drime identifiers.
- The dashboard allowlists the optional schedule summary inside schema version `1`, stores only sanitized scalar fields, and ignores it when missing.
- The dashboard uses the detected `policy_window_seconds` for WPvivid freshness classification/display when available, with the existing 15-day WPvivid policy retained as the fallback.
- The first detector should support the known WPvivid Free schedule shape around `wpvivid_schedule_setting`, WPvivid Pro/addon schedule state around `wpvivid_schedule_addon_setting` and `wpvivid_incremental_schedules`, `WPVIVID_MAIN_SCHEDULE_EVENT`, and standard WP-Cron recurrence intervals. Unknown or custom shapes should degrade to `detected: false` rather than guessing.
- When multiple local WPvivid intervals are detected, the policy should use the least frequent supported cadence (the largest interval) so intentionally infrequent scheduled backups do not become false alarms.

Acceptance criteria:

- Existing schema-1 clients without `schedule_policy` continue to classify and render exactly as before.
- A WPvivid source with upload evidence and an age inside the detected policy window stays `Working` / `Within policy`.
- A WPvivid source with upload evidence older than the detected policy window becomes `Needs attention`.
- Sites list and site detail views show whether the WPvivid expected freshness is schedule-detected or using the dashboard fallback.
- The dashboard remains read-only, receives no Drime API credentials, and performs no remote actions.

### Status Classifier WPvivid Freshness Fixture Helper Slice

The WPvivid freshness classifier tests repeated the same full healthy payload plus stale WPvivid source fixture across four policy-window assertions. Keep the production classifier unchanged and reduce the test maintenance surface by extracting the repeated stale-WPvivid fixture setup into a private helper inside `tests/StatusClassifierWpvividFreshnessTest.php`.

Implementation status: implemented locally as a test-only structure cleanup. The helper builds the common healthy/server payload, merges WPvivid-specific stale overrides, and preserves the existing dashboard fallback and schedule-detected freshness assertions without changing production code, protocol, storage, UI, credentials, Drime behavior, backup/restore behavior, or remote actions.

Acceptance criteria:

- The focused `StatusClassifierWpvividFreshness` test coverage passes with the same behavior assertions.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No live-site, release, deployment, protocol, schema, credential, Drime, backup, restore, delete, cleanup, schedule, or remote-action behavior is introduced.

### Status Classifier WPvivid Policy Fixture Helper Slice

The WPvivid policy classifier tests repeated the same healthy payload plus server/WPvivid source wiring across missing-evidence and external-optional policy assertions. Keep the production classifier unchanged and reduce the test maintenance surface by moving the repeated backup-source fixture construction into shared status-classifier fixture helpers.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/status-classifier-backup-source-fixtures.php` now provides `backup_sources_payload()` and `wpvivid_source_payload()` helpers, and `tests/StatusClassifierWpvividPolicyTest.php` reuses them while preserving every WPvivid policy assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, source-policy behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, remote-action behavior, or live-site state changed.

Acceptance criteria:

- Focused `StatusClassifierWpvividPolicyTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, classifier behavior, source-policy behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, remote-action, or client-site behavior changes.

### Status Classifier Baseline Backup Source Fixture Reuse Slice

After adding shared WPvivid/source fixture helpers, the baseline classifier test still carried an inline server/WPvivid source payload for the historical failed-count assertion. Keep the production classifier unchanged and reuse the shared helper so baseline and WPvivid policy tests build source fixtures through the same support path.

Implementation status: implemented locally as a test-only structure cleanup. `tests/StatusClassifierTest.php` now uses `backup_sources_payload()` for the historical failed-count source fixture, preserving the same server failed-count override, healthy WPvivid current evidence, expected `Working` category, and assertion value. No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, source-policy behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, remote-action behavior, or live-site state changed.

Acceptance criteria:

- Focused `StatusClassifierTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, classifier behavior, source-policy behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, remote-action, or client-site behavior changes.

### Status Classifier Source Evidence Fixture Reuse Slice

After the classifier source fixture helpers were reused by baseline and WPvivid policy tests, the generic source-evidence classifier tests still hand-merged source summary arrays for queue-warning, stale-source, and not-configured evidence cases. Keep the production classifier unchanged and let the shared source helper accept source overrides directly.

Implementation status: implemented locally as a test-only structure cleanup. `source_payload()` now accepts optional overrides, and `tests/StatusClassifierSourceEvidenceTest.php` uses that helper instead of local `array_merge()` source fixtures. `backup_sources_payload()` and `wpvivid_source_payload()` continue to build the same server/WPvivid source summaries with caller overrides winning. Existing source-evidence assertions, fixture values, and classifier categories are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, source-policy behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, remote-action behavior, or live-site state changed.

Acceptance criteria:

- Focused `StatusClassifierSourceEvidenceTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, classifier behavior, source-policy behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, remote-action, or client-site behavior changes.

### Event Log Audit Harness Reuse Slice

The audit-history test still carried a local copy of the event-log option shims and direct include list after the shared event-log harness existed. Keep audit behavior unchanged and move the one audit-specific current-user shim into `tests/support/event-log-test-harness.php`, then have `tests/EventLogAuditTest.php` reuse that harness like the main event-log test.

Implementation status: implemented locally as a test-only structure cleanup. The shared harness now provides the option shims, current-user shim, and event-log includes needed by both event-log test files, while the audit test retains its own fixture reset values and assertions.

Acceptance criteria:

- Focused `EventLogAudit` and `EventLogTest` coverage passes.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, UI, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Restore Readiness Rendering Harness Structure Slice

The restore-readiness rendering test mixed assertions and a private rendering harness in one file. Keep the evidence-only rendering assertions unchanged and move the harness class into `tests/support/admin-page-restore-readiness-test-harness.php` so the test file remains focused on expected UI output.

Implementation status: implemented locally as a test-only structure cleanup. The new support harness owns the trait includes, snapshot decoding shim, detail-list renderer, and markup capture helpers used by `tests/AdminPageRestoreReadinessEvidenceTest.php`.

Acceptance criteria:

- Focused `AdminPageRestoreReadinessEvidenceTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, UI behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Polling State Rendering Fixture Helper Slice

The polling-state rendering test repeatedly declared the same active enrolled site row before overriding one or two fields for pending, revoked, paused, archived, and missing-credential cases. Keep the rendering assertions unchanged and add a private `polling_site()` fixture helper inside `tests/AdminPagePollingStateRenderingTest.php` so each case emphasizes only the state under test.

Implementation status: implemented locally as a test-only structure cleanup. The helper provides default active polling fields and each test merges only its state-specific overrides; no production rendering helper changed.

Acceptance criteria:

- Focused `AdminPagePollingStateRenderingTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, UI behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Diagnostics Test Collector Helper Slice

The core diagnostics test repeatedly wired the same fake site repository, fake snapshot repository, classifier, and collector before each assertion group. Keep the diagnostics assertions and fixture data unchanged and add a private `collect_diagnostics()` helper inside `tests/DiagnosticsTest.php` so each test focuses on its site and snapshot inputs.

Implementation status: implemented locally as a test-only structure cleanup. The helper centralizes diagnostics construction for the focused test file and returns collected diagnostics; production diagnostics, repositories, classification, support output, and scheduler logic are unchanged.

Acceptance criteria:

- Focused `DiagnosticsTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, UI behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Poller Failure Fixture Helper Slice

The poller failure-path test repeatedly created the same deterministic credential vault and successful status HTTP response fixtures before changing only the failure trigger under test. Keep all failure assertions unchanged and add private helpers in `tests/PollerFailureTest.php` for the deterministic vault and successful HTTP client fixture.

Implementation status: implemented locally as a test-only structure cleanup. The helpers centralize the repeated key material and success-response payload construction while preserving each failure scenario's explicit site, snapshot, and repository setup.

Acceptance criteria:

- Focused `PollerFailureTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, transport behavior, protocol, schema, credential storage, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action History Rendering Fixture Reuse Slice

The remote-action history rendering test had reusable V2-capable site and snapshot fixtures available in the shared rendering fixture trait, but two schedule-history cases still duplicated those arrays inline. Keep all rendering assertions unchanged and reuse `remote_action_history_site()` plus `remote_action_history_snapshot()` consistently in `tests/AdminPageRemoteActionHistoryRenderingTest.php`.

Implementation status: implemented locally as a test-only structure cleanup. The change removes duplicate fixture declarations from the focused test and leaves the shared harness, production rendering helpers, action history output, protocol, and capability logic unchanged.

Acceptance criteria:

- Focused `AdminPageRemoteActionHistoryRenderingTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, UI behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action History Rendering Row Fixture Helper Slice

After the site/snapshot fixture reuse slice, `AdminPageRemoteActionHistoryRenderingTest` still repeated the same successful action-history row structure and redacted context encoding across schedule apply, rollback-preview, and pending-cadence assertions. Keep all rendering assertions unchanged and add a small helper for the repeated history-row shape.

Implementation status: implemented locally as a test-only structure cleanup. `tests/AdminPageRemoteActionHistoryRenderingTest.php` now uses `history_row()` to build successful remote-action history rows with encoded redacted context while each test still supplies the action type, request time, client summary, and action-specific context payload explicitly. Rendered labels, detail disclosure output, schedule wording, rollback-preview wording, pending-cadence wording, and assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, Remote Action History behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `AdminPageRemoteActionHistoryRenderingTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Remote Action History behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Audit Support Harness Reuse Slice

The diagnostics audit support test duplicated option shims, diagnostics includes, and local empty fake repositories that are already available through shared event-log and diagnostics test harnesses. Keep the support-summary assertions unchanged and have `tests/DiagnosticsAuditSupportTest.php` reuse the shared bootstrap plus empty diagnostics repository doubles.

Implementation status: implemented locally as a test-only structure cleanup. The focused test now relies on `tests/support/event-log-test-harness.php` for option shims and event-log setup, `tests/support/diagnostics-test-bootstrap.php` for diagnostics wiring, and the shared diagnostics fake repositories with empty fixture arrays.

Acceptance criteria:

- Focused `DiagnosticsAuditSupportTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics output, support-copy behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Status Classifier Baseline Fixture Helper Slice

The baseline status classifier test repeated the same active-site snapshot classification call for several payload-only assertions. Keep the classification assertions unchanged and add a private `classify_payload()` helper inside `tests/StatusClassifierTest.php` for the cases that use the default active site, default snapshot capture time, and fixed test clock.

Implementation status: implemented locally as a test-only structure cleanup. The helper centralizes the default active-site classifier call while leaving special pending and stale-snapshot scenarios explicit.

Acceptance criteria:

- Focused `StatusClassifierTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, classification behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action Client Report Fixture Helper Slice

The remote action repository client-report test repeated the same repository setup, empty action row fixture, `mark_client_report()` assertion, and redacted-context decode for multiple report-preservation cases. Keep the client report payloads and assertions unchanged and add a private `client_report_context()` helper inside `tests/RemoteActionRepositoryClientReportTest.php`.

Implementation status: implemented locally as a test-only structure cleanup. The helper centralizes the repository/report/decode flow while preserving each schedule apply and rollback-preview report fixture exactly where it is asserted.

Acceptance criteria:

- Focused `RemoteActionRepositoryClientReportTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, repository behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Admin Page Action Test Double Structure Slice

The admin page action test harness still mixed shared setup, WordPress shims, the minimal action handler harness, and several fake collaborators in one large support file. Keep the admin action assertions unchanged and move the fake enrollment manager, dispatcher, poller, event log, and site repository into a dedicated test support file.

Implementation status: implemented locally as a test-only structure cleanup. The fake admin-action collaborators now live in `tests/support/admin-page-actions-test-doubles.php`, while `tests/support/admin-page-actions-test-harness.php` keeps the shared testcase setup, shims, production trait include, and minimal action handler harness. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused admin page action coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, admin action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action Dispatcher Test Double Structure Slice

The remote action dispatcher test harness still mixed dispatcher fixture builders with fake wpdb, credential vault, signer, and repository collaborators in one support file. Keep the dispatcher assertions unchanged and move those fake collaborators into a dedicated test support file.

Implementation status: implemented locally as a test-only structure cleanup. Dispatcher fake collaborators now live in `tests/support/remote-action-dispatcher-test-doubles.php`, while `tests/support/remote-action-dispatcher-test-harness.php` keeps the shared dispatcher fixture builders. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, dispatcher behavior, safe transport behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Test Bootstrap WordPress Shim Structure Slice

The global PHPUnit bootstrap still mixed path constants, Composer/plugin loading, and every minimal WordPress shim in one file. Keep the bootstrap load order and shim behavior unchanged while moving the reusable WordPress function/class shims into a dedicated support file.

Implementation status: implemented locally as a test-only structure cleanup. Minimal WordPress function and `WP_Error` shims now live in `tests/support/wordpress-shims.php`, while `tests/bootstrap.php` keeps repository path constants, Composer autoloading, shim support loading, and plugin loading. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- Bootstrap still loads Composer, WordPress shims, and the plugin in the same effective order.
- No production code, bootstrap behavior, shim behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### WordPress Test Shim Support Split Slice

After extracting the WordPress shims from the global PHPUnit bootstrap, the new shim support file was itself the only non-build PHP file over the structure threshold. Keep `tests/support/wordpress-shims.php` as a loader and split the shim definitions into core, formatting, and error shim support files.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/wordpress-shims.php` now loads `wordpress-shims-core.php`, `wordpress-shims-formatting.php`, and `wordpress-shims-errors.php` in a stable order. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- Bootstrap still loads the same shim definitions before plugin loading.
- No production code, bootstrap behavior, shim behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### WordPress Error Shim Class Split Slice

The WordPress error shim support file still grouped the `WP_Error` class shim with the `is_wp_error()` helper shim. Keep `tests/support/wordpress-shims-errors.php` as the stable error-shim loader while moving the class definition into a focused support file.

Implementation status: implemented locally as a test-only support split. The minimal `WP_Error` class shim now lives in `tests/support/wordpress-shims-error-class.php`, and `tests/support/wordpress-shims-errors.php` requires it before defining `is_wp_error()`. The shared `tests/support/wordpress-shims.php` loader path, effective shim order, class/function behavior, and existing tests remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- Bootstrap still loads the same error shim behavior before plugin loading.
- No production code, bootstrap behavior, shim behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### WordPress Formatting Shim Support Split Slice

The WordPress formatting shim support file still grouped i18n, escaping, sanitization, unslashing, and JSON encoding shims together. Keep `tests/support/wordpress-shims-formatting.php` as the stable loader path while splitting those definitions into focused support files in the same effective load order.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/wordpress-shims-formatting.php` now loads `wordpress-shims-i18n.php`, `wordpress-shims-escaping.php`, and `wordpress-shims-sanitization.php`. Translation shims load before escaped translation helpers, and sanitization/encoding helpers retain their existing behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state changed.

Acceptance criteria:

- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- Bootstrap still loads the same shim definitions before plugin loading.
- No production code, bootstrap behavior, shim behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### WordPress JSON Shim Support Split Slice

The WordPress sanitization shim support file still grouped sanitization/unslashing helpers with the JSON encoding helper. Keep `tests/support/wordpress-shims-sanitization.php` as the stable loader path while moving the JSON shim into a focused support file.

Implementation status: implemented locally as a test-only structure cleanup. The minimal `wp_json_encode()` shim now lives in `tests/support/wordpress-shims-json.php`, and `tests/support/wordpress-shims-sanitization.php` loads it before defining sanitization helpers. The effective formatting shim loader path and JSON encoding behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state changed.

Acceptance criteria:

- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- Bootstrap still loads the same JSON shim behavior before plugin loading.
- No production code, bootstrap behavior, shim behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### WordPress Admin Shim Support Split Slice

The WordPress core shim support file still grouped generic plugin/path/hook shims with admin URL, query-argument, management-page, and nocache-header shims. Keep `tests/support/wordpress-shims.php` as the stable loader and move admin/url response helpers into a focused support file without changing effective shim behavior.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/wordpress-shims.php` now loads `wordpress-shims-hooks.php` after core path/plugin helpers and `wordpress-shims-admin.php` after formatting shims. `wordpress-shims-core.php` retains generic plugin/path helpers. Hook registration, admin URL, query-argument, management-page, and nocache-header shim behavior is unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- Bootstrap still loads the same shim definitions before plugin loading.
- No production code, bootstrap behavior, shim behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Poller Test Double Structure Slice

The poller test harness mixed production poller dependencies with fake site repository, snapshot repository, and remote-action reconciler collaborators. Keep the poller assertions unchanged and move those fake collaborators into a dedicated test support file.

Implementation status: implemented locally as a test-only structure cleanup. Poller fake collaborators were moved out of the production-dependency harness; the fake site repository now lives in `tests/support/poller-test-site-repository.php`, and the remaining fake snapshot repository plus remote-action reconciler stay in `tests/support/poller-test-doubles.php`. `tests/support/poller-test-harness.php` keeps production poller dependency loading and then loads the test doubles. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused poller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, poller behavior, scheduling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Poller Reconciler Double Split Slice

The poller test-double loader still grouped the fake snapshot repository with the fake remote-action reconciler. Keep `tests/support/poller-test-doubles.php` as the stable loader while moving the reconciler collaborator into a focused support file.

Implementation status: implemented locally as a test-only support split. The fake remote-action reconciler now lives in `tests/support/poller-test-reconciler-double.php`, and `tests/support/poller-test-doubles.php` requires it while retaining the fake snapshot repository. Current poller tests keep requiring the same harness and double loader path, class names, fixture behavior, and assertions. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, snapshot storage behavior, remote-action reconciliation behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused poller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Dispatcher Test Double Support Split Slice

The remote action dispatcher test-double support file still grouped fake database, fake crypto, and fake action-repository collaborators together. Keep the existing loader path stable while splitting those doubles into narrower support files.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/remote-action-dispatcher-test-doubles.php` now loads `remote-action-dispatcher-wpdb-double.php`, `remote-action-dispatcher-crypto-doubles.php`, and `remote-action-dispatcher-actions-double.php`. The dispatcher wpdb double keeps its stable class name while insert/update recording behavior now lives in `tests/support/remote-action-dispatcher-wpdb-write-methods.php`. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, dispatcher behavior, safe transport behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Admin Page Action Test Double Support Split Slice

The admin page action test-double support file still grouped enrollment, remote-action, poller, event-log, and site repository collaborators together. Keep the existing loader path stable while splitting those doubles into narrower support files.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/admin-page-actions-test-doubles.php` now loads enrollment, remote-action dispatcher, poller, event-log, and site repository fake collaborator files; the poller double lives in `tests/support/admin-page-actions-poller-double.php` and is loaded through the stable remote-doubles path. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, polling behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused admin action coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, admin action behavior, polling behavior, archive behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Admin Page Action Handler Harness Split Slice

After the admin action double split, the shared admin action harness file still grouped PHPUnit setup helpers with the concrete trait-harness class that exposes `handle_post_action()`. Keep the existing harness loader stable while moving the concrete handler harness into a focused support file.

Implementation status: implemented locally as a test-only support split. `Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness` now lives in `tests/support/admin-page-actions-handler-harness.php`, and `tests/support/admin-page-actions-test-harness.php` loads it after the WordPress shims, production action trait, and fake collaborator doubles. Test setup helpers, class name, constructor wiring, collaborator doubles, public properties, exposed handler method, assertions, and production admin action behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused admin action, remote-action, archive, and polling-action coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, admin action behavior, polling behavior, archive behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Admin Page Action Test Case Helper Split Slice

After the handler harness split, the shared admin action test-case trait still grouped PHPUnit global lifecycle setup with request helper methods. Keep `tests/support/admin-page-actions-test-harness.php` as the stable loader while moving the nonce setter and handler factory into a focused helper trait.

Implementation status: implemented locally as a test-only support split. `set_valid_nonce()` and `admin_action_harness()` now live in `tests/support/admin-page-actions-test-case-helpers.php`, and `Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case` composes that helper trait while keeping POST/global lifecycle setup in the stable harness file. Helper names, nonce global behavior, default manager creation, handler harness construction, and admin-action assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused admin action, remote-action, archive, and polling-action coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, admin action behavior, nonce behavior, polling behavior, archive behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Page Action WordPress Shim Support Split Slice

The admin page action test harness still grouped shared testcase setup, the minimal action handler harness, and local WordPress shim functions in one support file. Keep the existing harness loader path stable while moving the admin-action-specific WordPress shims into a focused support file.

Implementation status: implemented locally as a test-only structure cleanup. `tests/support/admin-page-actions-test-harness.php` now loads `tests/support/admin-page-actions-wordpress-shims.php` before the production action trait and fake collaborators. The `home_url()`, `wp_verify_nonce()`, and `get_current_user_id()` shim behavior, nonce globals, current-user global, harness setup, and admin-action assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused admin action coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, admin action behavior, nonce behavior, current-user behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Readme Release Metadata Alignment Slice

The plugin readme still identified `0.1.57` as the stable tag even though the latest packaged dashboard release is `0.1.59`. Align the readme stable tag, headline version wording, and changelog with the released `0.1.58` and `0.1.59` notes without changing plugin runtime behavior or preparing a new release.

Implementation status: implemented locally as a documentation/metadata cleanup. `readme.txt` now lists stable tag `0.1.59`, updates the headline release wording to `0.1.59`, and adds missing `0.1.58` and `0.1.59` changelog entries. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Readme metadata matches the latest existing dashboard release.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, runtime behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Changelog Release Metadata Alignment Slice

The Markdown `CHANGELOG.md` release history skipped the already published dashboard `0.1.57` patch even though `README.md`, `readme.txt`, and the GitHub release notes document that release. Align `CHANGELOG.md` with the existing release history by adding the missing `0.1.57` entry without changing plugin runtime behavior or preparing a new release.

Implementation status: implemented locally as a documentation/metadata cleanup. `CHANGELOG.md` now includes the published `0.1.57` cleanup-preview evidence release notes and explicitly preserves the preview-only cleanup boundary. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, filesystem-path exposure, or live-site state was changed.

Acceptance criteria:

- `CHANGELOG.md`, `README.md`, `readme.txt`, and GitHub release history no longer disagree about the `0.1.57` release.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, runtime behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Historical Release Metadata Consistency Slice

The local WordPress readme and Markdown changelog still had a few older historical version-list mismatches after the latest metadata alignment pass: `readme.txt` lacked entries for the published `0.1.29` through `0.1.32` releases, while `CHANGELOG.md` folded the existing `0.1.14` uninstall-safety release into the later `0.1.15` notes instead of listing it as its own published release. Align those historical entries with the existing GitHub release notes without changing runtime behavior or preparing a new release.

Implementation status: implemented locally as a documentation/metadata cleanup. `readme.txt` now includes the published `0.1.29`, `0.1.30`, `0.1.31`, and `0.1.32` entries, and `CHANGELOG.md` now includes the published `0.1.14` uninstall-safety entry as its own release. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, filesystem-path exposure, uninstall behavior, or live-site state was changed.

Acceptance criteria:

- `CHANGELOG.md`, `readme.txt`, and GitHub release history no longer disagree about the `0.1.14` and `0.1.29` through `0.1.32` release entries.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, runtime behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, uninstall, live-site, release, deploy, or remote-action behavior changes.

### Historical Release Metadata Second Consistency Slice

The published GitHub release list also includes `0.1.34` and `0.1.35`, while both local release-history files jumped from `0.1.33` to `0.1.36`. Align `CHANGELOG.md` and `readme.txt` with the existing published release notes without changing runtime behavior or preparing a new release.

Implementation status: implemented locally as a documentation/metadata cleanup. `CHANGELOG.md` and `readme.txt` now include the published `0.1.34` scheduled-poll batch-size entry and the published `0.1.35` Diagnostics freshness/cache-control entry. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, filesystem-path exposure, cache behavior, polling behavior, or live-site state was changed.

Acceptance criteria:

- `CHANGELOG.md`, `readme.txt`, and GitHub release history no longer disagree about the `0.1.34` and `0.1.35` release entries.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, runtime behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, polling, cache, live-site, release, deploy, or remote-action behavior changes.

### Historical Failed-Count Status Policy Slice

Operational `0.5.16` rollout showed another alert-noise case: some clients correctly report queue `0`, no active upload, warning_count `0`, and fresh or policy-valid source evidence, while still carrying historical `failed_count=1` in the uploader registry from an older server-source failure that later recovered or was superseded by newer successful uploads.

Implementation status: implemented and released. Failed counters remain visible as evidence, but they no longer create `Needs attention` by themselves when queues are empty and current source evidence is healthy, policy-valid, or dashboard-optional.

Dashboard classification should treat failed counters as evidence, not as a permanent alarm by themselves:

- Keep failed counts visible in the Sites list, Site Detail, Diagnostics, snapshots, and support copy.
- Continue to classify as `Needs attention` when failed counters are paired with current queued work, current source-level queued failures, missing required upload evidence, source freshness outside policy, hard source warnings, cron problems, or uploader warnings.
- Do not classify as `Needs attention` solely because top-level or source-level `failed_count` is non-zero when queue is `0` and the current source evidence is otherwise healthy or within dashboard policy.
- Keep the change dashboard-side, additive, read-only, and schema-compatible. Do not mutate client failed registries, clear records, trigger backups, or require a status schema-version change.

Acceptance criteria:

- A payload with `failed_count > 0`, `queue_count = 0`, warning_count `0`, and fresh/policy-valid source evidence classifies as `Working`.
- A payload with `failed_count > 0` and `queue_count > 0` still classifies as `Needs attention`.
- A source with `failed_count > 0`, `queued_count = 0`, and fresh/policy-valid source evidence does not by itself classify the site as `Needs attention`.
- A source with `failed_count > 0` and `queued_count > 0` still classifies as `Needs attention`.
- Existing source freshness, missing-evidence, warning, cron, incompatible-schema, stale-reporting, and not-configured behavior remains unchanged.

### Dashboard-Owned Source Optionality Policy Slice

Operational rollout also found a separate class of site: WPvivid is intentionally active, but its configured destination is outside the Alynt uploader's local package/upload path. In that case the dashboard should not claim Alynt-uploaded WPvivid evidence is missing as an operational backup failure, while still showing the operator that WPvivid is being treated as external/optional for that site.

Implementation status: implemented and released. The dashboard stores a local per-site WPvivid source policy override with `required` and `external_optional` modes, exposes the Site Detail toggle with nonce/capability protection, and keeps the setting dashboard-local and read-only.

Implement a small dashboard-local policy override:

- Store per-site source monitoring policy in a dashboard-owned option rather than mutating client settings or adding a table migration.
- Support only the `wpvivid` source initially, with modes `required` and `external_optional`.
- Let `external_optional` suppress WPvivid stale/missing Alynt-upload evidence attention for that site, while preserving hard attention states such as failed source uploads, global failed uploads, cron problems, incompatible payloads, polling failures, or unrelated warnings.
- Show the policy in Sites-list and Site-detail backup evidence as `External / optional` so the row is transparent at a glance.
- Add a nonce- and capability-protected Site Detail toggle that changes dashboard classification only. It must not call the client site, change WPvivid, create backups, delete backups, clean up files, or mutate Drime.

Acceptance criteria:

- A site with server evidence healthy and WPvivid configured but intentionally external can be marked `Working` when WPvivid lacks Alynt-uploaded evidence.
- A site with WPvivid marked external/optional still reports `Needs attention` when the source or global payload has failed uploads.
- The UI clearly states that the policy is dashboard-local and read-only.
- Existing sites without the option continue using the detected/fallback WPvivid freshness policy unchanged.

### Needs-Attention Reason Clarity / Observability Polish Slice

Operational use showed that source-level freshness labels are correct but still require too much interpretation from the Sites tab. Operators need to see whether a row is a real alarm, a schedule-valid WPvivid state, an external/optional WPvivid policy, or merely a queued/informational condition without opening every Site Detail screen.

Implement a small dashboard-side display polish:

- Add a concise operator-facing reason line to compact Sites-row source evidence and Site Detail source cards.
- Keep the summary derived only from the already-redacted schema-1 source evidence and dashboard-local source policy.
- Distinguish `Fresh`, `Within policy`, `External / optional`, `Stale`, `No upload evidence`, `Not configured`, queued packages, and current failed queued uploads in plain language.
- Preserve the existing status classifier, storage, polling, and source-policy behavior unless a separate implementation slice changes those contracts.
- Keep the dashboard read-only. Do not add Drime credentials, direct Drime inventory, client mutation, remote backup creation, restore, delete, cleanup, settings mutation, or schema-version requirements.

Acceptance criteria:

- Sites-row source evidence includes a concise reason line explaining why each source is okay, policy-valid, optional, stale, missing, or needs review.
- Site Detail source evidence includes the same summary as `Operator summary`.
- Schedule-valid WPvivid evidence says it is allowed by the dashboard policy instead of looking like an unexplained stale alarm.
- External/optional WPvivid evidence says it is not required for Alynt-uploaded evidence on this dashboard.
- Existing schema-1 clients remain compatible and the v1/V2 read-only boundaries remain unchanged.

Implementation status: implemented and released through dashboard `0.1.26`. Compact Sites-row source reason lines reuse the existing redacted operator-summary helper already shown on Site Detail. The change is display-only, preserves classifier/source-policy behavior, and keeps the dashboard read-only.

### Dashboard Record-State Diagnostics Clarity Slice

Post-release monitoring showed that Diagnostics can correctly report the total number of dashboard records and the number of polling-ready records, but the difference between those two numbers is not obvious enough for operators. The dashboard should explain when extra records are pending pairing, awaiting first poll, revoked locally, paused, missing credentials, or otherwise not currently eligible for scheduled polling.

Implementation status: implemented and released. Diagnostics and support copy now distinguish total dashboard records from polling-ready records and expose support-safe aggregate record-state counts, including locally revoked and archived local records, without listing domains or secrets.

Implement a small dashboard-only diagnostics slice:

- Keep the Sites tab, polling scheduler, enrollment flow, status classification, and retained records unchanged.
- Add support-safe aggregate enrollment-state counts to Diagnostics and support copy, including active, awaiting first poll, pending, locally revoked, unknown/other, and records not currently polling.
- Rename operator-facing Diagnostics wording from generic “sites” toward “dashboard records” where it helps make clear that revoked/pending local records can still exist in storage for audit/history.
- Do not delete, hide, purge, revoke, re-enroll, or mutate any dashboard records as part of this slice.
- Do not change client protocol, client status payloads, remote actions, Drime credentials, scheduler cadence, or live-site state.

Acceptance criteria:

- Diagnostics clearly explains why total dashboard records can be greater than polling-ready records.
- Support copy contains only aggregate record-state counts and no site labels, domains, credentials, tokens, raw payloads, or response bodies.
- Existing polling and classification behavior remains unchanged.
- Targeted diagnostics tests cover the record-state counts and rendering.

### Diagnostics Freshness And Cache Protection Slice

Post-release monitoring showed that a browser/admin cache can display an older Diagnostics render even after the dashboard plugin and database have current state. Diagnostics already includes current UTC and support-copy timestamps, but operators need clearer freshness evidence and an easy cache-busted refresh path.

Implementation status: implemented and released. The Diagnostics screen now shows visible generated-at evidence and a cache-busted refresh path, and the admin page sends dashboard-scoped no-cache guidance without changing polling, classification, protocol, credentials, or remote actions.

Implement a small admin-only polish slice:

- Send no-cache headers for the dashboard admin page only.
- Show the Diagnostics generated-at UTC timestamp near the top of the Diagnostics screen.
- Provide a “Refresh diagnostics” link that reloads the Diagnostics tab with a cache-busting query parameter.
- Keep all output redacted and support-safe.
- Do not change polling, status classification, remote-action behavior, pairing, protocol fields, credentials, or live-site deployment rules.

Acceptance criteria:

- Diagnostics exposes visible generated-at evidence and a cache-busted refresh link.
- The dashboard admin page discourages stale cached renders.
- Existing Diagnostics support copy remains unchanged except for naturally current timestamps.

### Diagnostics Runtime Identity Slice

Post-release operations repeatedly require confirming which dashboard build is installed before interpreting Diagnostics, post-release monitoring evidence, or operator screenshots. The support-copy JSON already includes the plugin version, but operators should not have to open support copy or run WP-CLI to confirm the active dashboard identity.

Implementation status: implemented, released, and deployed through dashboard `0.1.50`. Diagnostics shows a support-safe Dashboard Runtime panel with active plugin version and protocol/schema contract. The change is display-only and does not alter support-copy JSON, polling, classification, scheduler behavior, protocol behavior, database schema, remote-action permissions, backup creation, restore, cleanup/delete, deployment, or live-site state.

Implement a small display-only runtime identity panel:

- Show the active dashboard plugin name and `ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION` on the Diagnostics tab.
- Show the dashboard polling contract as read-only `Protocol v1 / Status schema v1` operator context.
- Keep this visible panel support-safe: no filesystem paths, domains, labels, credentials, pairing tokens, polling secrets, authorization headers, raw payloads, raw responses, cookies, nonces, salts, Drime identifiers, or server environment details.
- Preserve the existing support-copy JSON shape unless a later support workflow needs additional stable fields.
- Do not change polling, classification, scheduler behavior, protocol behavior, database schema, remote-action permissions, backup creation, restore, cleanup/delete, deployment, or live-site state.

Acceptance criteria:

- Diagnostics visibly exposes the active dashboard version and protocol/schema contract.
- Existing Diagnostics support copy remains unchanged.
- A focused rendering test covers the runtime identity panel.

### Revoked Dashboard Record Guidance Slice

Operational rollout can leave superseded local dashboard records after a site is re-enrolled. Diagnostics now explains total dashboard records versus polling-ready records, and the Sites tab hides superseded revoked duplicates, but the individual Site Detail screen should make the revoked-record boundary explicit when an operator opens a revoked record directly.

Implementation status: implemented and released. Revoked Site Detail records now show local-only retention guidance explaining that the record is retained for audit/history, does not poll, cannot run dashboard actions, and has no permanent-remove control in this release.

Implement a small non-destructive Site Detail guidance slice:

- Render a clear revoked-record panel only for records with `enrollment_status = revoked`.
- Explain that the record is retained locally for audit/history, is not polling, and cannot run Check Now, pause/resume, Request Backup Now, Schedule Preview, or Schedule Apply.
- Explain that monitoring this origin again requires a new pairing token and client opt-in.
- Explicitly state that there is no permanent-remove control in this release.
- Do not delete, purge, archive, hide, re-enroll, or otherwise mutate any dashboard record.
- Do not contact the client site, Drime, WPvivid, server-runner, GridPane, or any remote service.

Acceptance criteria:

- Revoked Site Detail screens show the local-only retention guidance.
- Active/non-revoked Site Detail screens do not show revoked-record guidance.
- Tests cover the revoked-only rendering and wording.
- No new POST action, database write, remote action, protocol field, or live-site behavior is introduced.

### Local Dashboard Record Archive Slice

After revoked-record guidance shipped, the remaining operator problem is local dashboard clutter: revoked or expired-pending records are retained for audit/history, but they can still be opened directly and can inflate total-record diagnostics. Permanent deletion remains intentionally out of scope because dashboard records, snapshots, and action history may be useful during support review.

Implementation status: implemented and released. Dashboard-local archive/unarchive now hides eligible retained local records from default operational views while preserving audit/history, direct detail access, Diagnostics counts, and local unarchive. Active, polling, credentialed, or awaiting-first-poll records remain ineligible for archive.

Implement a small dashboard-local archive/unarchive slice:

- Add an `archived_at` timestamp to the dashboard-owned sites table through an idempotent schema migration.
- Hide archived records from the default Sites table, Attention queue, and scheduled-polling contexts.
- Keep Diagnostics/support copy aware of archived local records as retained dashboard records, not paired active sites.
- Allow archiving only for non-polling local records: revoked records and expired pending records. Active, awaiting-first-poll, paused, or credentialed enrolled records must not be archivable until they are revoked or otherwise no longer polling.
- Provide a clearly labeled archived-records view from the Sites tab so operators can still audit and restore visibility for archived records.
- Provide an unarchive action that only clears `archived_at`; it must not recreate credentials, resume polling, reuse pairing tokens, contact clients, or change backups.
- Keep permanent removal unavailable unless a later purge/delete design is separately approved.
- Do not contact the client site, Drime, WPvivid, server-runner, GridPane, or any remote service.

Acceptance criteria:

- Default Sites and Attention views exclude archived local records.
- Archived records remain available in a dedicated archived-records view and direct Site Detail URLs.
- Revoked and expired pending records can be archived; active/enrolled/polling records cannot.
- Archived records can be unarchived locally without restoring credentials or polling.
- Diagnostics/support copy distinguishes active/polling records, revoked records, and archived local records without exposing secrets.
- Tests cover archive eligibility, archive/unarchive action handling, Sites filtering, and revoked/archived Site Detail guidance.
- No remote action, credential reuse, backup creation, restore, cleanup/delete, protocol change, or live-site behavior is introduced.

### Local Retained Record Removal Design Slice

After archive/unarchive shipped, the remaining dashboard-local record-management question is whether operators should ever be able to permanently remove retained local records. This is intentionally separate from archive/unarchive because it would be destructive to dashboard-owned history, snapshots, and action records even though it would not contact a client site or Drime.

Implementation status: design-only planning is complete in `docs/LOCAL_RECORD_REMOVAL_DESIGN.md`, and the first non-destructive runtime slice is implemented locally as a Site Detail **Local Removal Preview** for archived retained records. The preview shows future-removal eligibility and retained snapshot/action row counts for revoked or expired-pending records with no polling credentials, no action signing credentials, no scheduled polling state, and no non-terminal action history. A 2026-10-06 planning refresh explicitly defers permanent removal as the default next slice unless archived records create concrete operator clutter, measurable database/performance pressure, a formal data-minimization need, or a compliance/retention-policy requirement. Permanent removal remains unavailable and gated because it would be a destructive dashboard-local database action. This preview slice adds no delete/confirm control, schema change, database write, remote action, backup creation, restore, cleanup/delete apply, credential handling, Drime behavior, release, deployment, or live-site state change.

Future implementation target:

- Do not implement permanent removal by default; re-open only for a concrete operational, data-minimization, performance, or compliance reason.
- Keep permanent removal unavailable until explicitly approved as a destructive dashboard-local database action.
- Render no permanent-remove control on the default Sites table.
- Limit any future control to archived records and/or archived Site Detail screens.
- Require a support-safe preview before confirmation, including dependent row counts for site, snapshot, and action rows.
- Re-check eligibility at confirmation time rather than trusting the preview.
- Delete only dashboard-owned rows for the selected dashboard site ID.
- Record redacted audit outcomes for preview and confirmation attempts.
- Preserve uninstall behavior and the existing archive/unarchive flow.

Acceptance criteria for a future implementation:

- Active, awaiting-first-poll, paused, unarchived, credentialed, or non-terminal-action records cannot be removed.
- Eligible archived revoked or expired-pending records can be previewed without deleting data.
- Confirmation requires a fresh nonce, capability check, typed confirmation phrase, and current eligibility re-check.
- Confirmation deletes only the selected dashboard-owned site, snapshot, and action rows.
- Audit events and support output remain redacted and do not expose labels/domains beyond existing support-safe policy.
- No client-site contact, Drime mutation, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential rotation, release, deployment, or live-site behavior is introduced without a separate approval gate.

### Diagnostics Local Removal Readiness Slice

After the Site Detail removal-preview slice, operators can inspect one archived retained record at a time, but Diagnostics still only reports the total archived-record count. The next safe local-only feature is fleet-level support-safe readiness evidence for retained local records, without adding removal controls or deleting anything.

Implementation target:

- Add a Diagnostics aggregate for archived retained local records that are ready for a future local-removal confirmation versus blocked.
- Count retained snapshot rows, retained action-history rows, and non-terminal action rows across archived retained records.
- Reuse the same support-safe eligibility boundary as the Site Detail preview: archived only, revoked or expired-pending only, no polling credentials, no action signing credentials, no next scheduled poll, no paused state, and no non-terminal action history.
- Render the aggregate on Diagnostics as preview/readiness evidence only.
- Include the aggregate in the existing redacted support summary through the `counts` object without adding domains, labels, raw payloads, credentials, paths, Drime identifiers, or row-level details.
- Do not add a remove/delete button, confirmation form, POST handler, schema change, SQL write, remote action, client-site contact, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, release, deployment, or live-site state change.

Implementation status: implemented, released, and deployed through dashboard `0.1.62` as display-only Diagnostics/support-copy readiness evidence for archived retained local records. Diagnostics reports evaluated, ready, blocked, retained snapshot, retained action-history, and non-terminal action counts while preserving the preview-only boundary. No permanent remove/delete control, confirmation form, POST handler, database write, remote action, client-site contact, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, or live-site mutation was introduced by this slice.

Acceptance criteria:

- Diagnostics shows archived retained records evaluated, future-removal-ready records, blocked records, retained snapshot rows, retained action-history rows, and non-terminal action rows.
- Support summary includes only aggregate counts.
- Tests cover the aggregate counts and visible Diagnostics rendering.
- Permanent local removal remains unavailable and no destructive workflow is introduced.
- PHP syntax, targeted tests, full tests, lint, build, and whitespace checks pass before commit.

### Archived Row Local Removal Readiness Hint Slice

After Diagnostics gained fleet-level local removal-readiness aggregates, the archived-records table can safely show the same readiness boundary at row level so operators do not need to open every retained record merely to see whether it is ready for a future separate confirmation gate.

Implementation target:

- Render a compact local-removal readiness hint only for archived rows in the Sites/Archived Local Records table.
- Reuse the existing Site Detail preview eligibility logic and retained row counts.
- Keep the hint display-only: no remove/delete button, no confirmation form, no POST handler, and no database write.
- Preserve the same eligibility boundary: archived only, revoked or expired-pending only, no polling credentials, no action signing credentials, no next scheduled poll, no paused state, and no non-terminal action history.
- Do not change default active Sites rows, scheduled polling, support-copy JSON shape, protocol behavior, remote actions, credential handling, cleanup/delete, backup creation, restore, release, deployment, or live-site state.

Implementation status: implemented, released, and deployed through dashboard `0.1.62` as a display-only Archived Local Records row hint. Archived rows now show compact ready/blocked local-removal readiness evidence with retained snapshot/action/non-terminal counts, reusing the existing Site Detail preview eligibility boundary and without adding a remove/delete button, confirmation form, POST handler, database write, remote action, client-site contact, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, or live-site mutation.

Acceptance criteria:

- Archived rows show a compact ready/blocked local-removal hint with retained snapshot/action/non-terminal counts.
- Unarchived rows render no local-removal hint.
- Tests cover the compact row hint and confirm no form/control is introduced.
- PHP syntax, targeted tests, full tests, lint, build, and whitespace checks pass before commit.

### Settings And Hooks Documentation Alignment Slice

The Settings and Hook reference docs still contained stale release-line wording after the `0.1.61` rollout and later planning slices. This created a small operator-doc mismatch: the hook reference still named `0.1.28`, and the settings overview understated the current split between administrator-configurable local policy/settings options and internal options.

Implementation status: implemented locally as a documentation-only alignment and refreshed after dashboard `0.1.62`. `docs/HOOKS.md` now states that no public custom extension hooks exist in the current `0.1.62` release line and clarifies that V2.1+ action dispatch uses signed outbound client requests rather than public dashboard REST routes. `docs/SETTINGS.md` describes two administrator-configurable local policy/settings options, three internal local options, autoload-disabled source-policy storage, and custom tables that also store encrypted action signing keys and bounded V2.1+ action history. No runtime PHP, schema, SQL, UI control, database write, remote action, backup creation, restore, cleanup/delete apply, credential handling, Drime behavior, release, deployment, or live-site state changed.

Acceptance criteria:

- Settings documentation matches currently stored dashboard options and table-owned state.
- Hook documentation no longer references the stale `0.1.28` line.
- Documentation distinguishes dashboard REST routes from outbound signed client action requests.
- No PHP, schema, protocol, UI, database, credential, Drime, backup, restore, cleanup/delete, release, deployment, or live-site behavior changes are introduced.

### README Operator Entry Point Slice

The repository README had become a release-history archive, making the top-level project entry point hard to scan. The next safe documentation-only cleanup is to turn it back into a concise operator/developer overview while keeping detailed release history in `CHANGELOG.md` and detailed roadmap state in this implementation plan.

Implementation status: implemented locally as a README-only documentation cleanup. `README.md` now summarizes the dashboard purpose, safety boundaries, monitoring surfaces, V2 action limits, restore-readiness evidence posture, typical operator flow, installation requirements, package identity, development checks, release packaging, documentation map, FAQ, and license. It removes the long inline release-history narrative from the README and points readers to `CHANGELOG.md` for detailed release notes. No runtime PHP, asset, translation string, schema, protocol, UI behavior, database write, remote action, backup creation, restore, cleanup/delete apply, credential handling, Drime behavior, release, deployment, or live-site state changed.

Acceptance criteria:

- README gives a concise current overview instead of a long release-history archive.
- README keeps the dashboard read-only/safety boundaries explicit.
- README points detailed release history to `CHANGELOG.md` and roadmap details to `docs/IMPLEMENTATION_PLAN.md`.
- No PHP, asset, schema, protocol, UI, database, credential, Drime, backup, restore, cleanup/delete, release, deployment, or live-site behavior changes are introduced.

### Admin Page Polling-State Counting Doubles Split Slice

The polling-state rendering support harness became the largest remaining non-build PHP file after the broader file-structure cleanup pass. The next safe test-only structure cleanup was to move the local-record counting repository doubles into their own support file while leaving the harness assertions, production traits, UI output, protocol behavior, database schema, and live-site state unchanged.

Implementation status: implemented locally as a test-only support split. The counting snapshot repository double and counting remote-action repository double now live in `tests/support/admin-page-polling-state-rendering-counting-doubles.php`, and the polling-state rendering bootstrap loads that file before the harness. Class names, constructor signatures, count semantics, and exposed harness helper behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state was changed.

Implementation target:

- Move the counting snapshot repository double and counting remote-action repository double from `tests/support/admin-page-polling-state-rendering-test-harness.php` into a dedicated support file.
- Load that support file from the existing polling-state rendering bootstrap before the harness.
- Keep class names, constructor signatures, count semantics, and exposed harness helper behavior unchanged.
- Do not change production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state.

Acceptance criteria:

- The polling-state rendering harness is smaller and focused on harness methods.
- Local-record rendering tests keep using the same counting doubles without assertion or behavior changes.
- Targeted polling/local-record rendering tests, full tests, lint, build, and whitespace checks pass before commit.

### Admin Page Polling-State Local Record Methods Split Slice

After the counting doubles split, the polling-state rendering support harness still grouped local-record exposure methods with the broader polling, request-backup, schedule, cleanup, and history exposure methods. The next safe test-only structure cleanup is to move the local-record exposure methods into their own support trait while leaving production traits, rendered output, assertions, and test method names unchanged.

Implementation target:

- Move `revoked_record_guidance_html()`, `archive_record_panel_html()`, `retained_record_removal_preview_panel_html()`, and `retained_record_removal_row_hint_html()` from `tests/support/admin-page-polling-state-rendering-test-harness.php` into a dedicated test-support trait.
- Load the new support trait from the existing polling-state rendering bootstrap before the harness class.
- Compose the new trait into `Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness`.
- Keep public method names, signatures, repository-double setup, rendered output, assertions, and production trait usage unchanged.
- Do not change production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state.

Implementation status: implemented locally as a test-only support split. Local-record rendering exposure methods now live in `tests/support/admin-page-polling-state-rendering-local-record-methods.php`, and the polling-state rendering bootstrap loads the new trait before the harness class. Public method names, signatures, repository-double setup, rendered output, assertions, and production trait usage are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- The polling-state rendering harness is smaller and less mixed.
- Existing local-record rendering tests pass without assertion changes.
- Full tests, lint, build, and whitespace checks pass before commit.

### Admin Page Polling-State Remote Action Methods Split Slice

After the local-record methods split, the polling-state rendering support harness still grouped request-backup, schedule-management, and cleanup-preview exposure methods with the basic polling/detail exposure methods. The next safe test-only structure cleanup is to move those remote-action exposure methods into their own support trait while leaving production traits, rendered output, assertions, fixture values, and test method names unchanged.

Implementation target:

- Move `request_backup_row_hint_html()`, `request_backup_panel_html()`, `schedule_management_panel_html()`, and `cleanup_preview_panel_html()` from `tests/support/admin-page-polling-state-rendering-test-harness.php` into a dedicated test-support trait.
- Load the new support trait from the existing polling-state rendering bootstrap before the harness class.
- Compose the new trait into `Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness`.
- Keep public method names, signatures, remote-action repository setup, rendered output, assertions, and production trait usage unchanged.
- Do not change production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state.

Implementation status: implemented locally as a test-only support split. Request-backup, schedule-management, and cleanup-preview rendering exposure methods now live in `tests/support/admin-page-polling-state-rendering-remote-action-methods.php`, and the polling-state rendering bootstrap loads the new trait before the harness class. Public method names, signatures, remote-action repository setup, rendered output, assertions, and production trait usage are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- The polling-state rendering harness is smaller and focused on basic polling/detail exposure methods.
- Existing request-backup, schedule-management, and cleanup-preview rendering tests pass without assertion changes.
- Full tests, lint, build, and whitespace checks pass before commit.

### Admin Schedule Management Action Repository Double Split Slice

The schedule-management rendering harness still grouped payload fixtures, rendering exposure methods, and the fake remote-action repository used for schedule apply and rollback-preview rendering coverage. The next safe test-only structure cleanup is to move the fake action repository into its own support file without changing test fixtures, assertions, rendering behavior, or production code.

Implementation target:

- Move `Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions` from `tests/support/admin-page-schedule-management-test-harness.php` into a focused support file.
- Require the new support file from the existing schedule-management harness so test files can keep requiring the same harness entry point.
- Keep fake repository class name, method signatures, returned rows, fixture IDs, fingerprints, cadence values, WP_Error behavior, and test assertions unchanged.
- Do not change production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state.

Implementation status: implemented locally as a test-only support split. The fake schedule-management action repository now lives in `tests/support/admin-page-schedule-management-actions-double.php`, and the existing schedule-management harness loads it before defining payload fixtures and rendering exposure methods. The fake repository class name, method signatures, returned rows, fixture IDs, fingerprints, cadence values, WP_Error behavior, and test assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- The schedule-management rendering harness is smaller and focused on fixtures/rendering exposure.
- Schedule-management and schedule rollback-preview rendering tests pass without assertion changes.
- Full tests, lint, build, and whitespace checks pass before commit.

### Admin Schedule Management Action Lookup Double Split Slice

After the action repository double split, the fake schedule-management action repository still grouped recent-history responses with fresh schedule-apply and rollback-preview lookup responses. Keep `tests/support/admin-page-schedule-management-actions-double.php` as the stable fake repository loader while moving schedule lookup responses into a focused support trait.

Implementation status: implemented locally as a test-only support split. Schedule preview/apply lookup responses now live in `tests/support/admin-page-schedule-management-action-lookups.php`, and `Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions` composes that trait while retaining the recent-history fixture method in the stable fake repository file. The fake repository class name, method signatures, returned rows, fixture IDs, fingerprints, cadence values, WP_Error behavior, and schedule-management rendering assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule-management and rollback-preview rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, schedule-management rendering behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Sites List Test Double Split Slice

The Sites-list test harness still grouped harness exposure methods with fake Sites, snapshots, and classifier collaborators. The next safe test-only structure cleanup is to move those fake collaborators into their own support file while leaving the harness entry point, fixtures, assertions, and production Sites-list rendering behavior unchanged.

Implementation target:

- Move `Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites`, `Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Snapshots`, and `Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Classifier` from `tests/support/admin-page-sites-list-test-harness.php` into a focused support file.
- Require the new support file from the existing Sites-list harness so tests can keep requiring the same harness entry point.
- Keep fake class names, method signatures, fixture rows, classifier category behavior, harness public methods, and test assertions unchanged.
- Do not change production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state.

Implementation status: implemented locally as a test-only support split. Sites-list fake site rows live in `tests/support/admin-page-sites-list-test-doubles.php`, snapshot/classifier collaborators live in `tests/support/admin-page-sites-list-test-collaborators.php`, and the existing Sites-list harness loads them through the stable doubles path before defining the harness class. Public helper exposure methods now live in `tests/support/admin-page-sites-list-exposure-methods.php`, while the stable harness class composes that trait. Fake class names, method signatures, fixture rows, classifier category behavior, harness public methods, and test assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- The Sites-list harness is smaller and focused on exposing rendering/context helpers.
- Sites-list tests pass without assertion changes.
- Full tests, lint, build, and whitespace checks pass before commit.

### Admin Sites List WordPress Shim Split Slice

After the Sites-list double split, the Sites-list harness still embedded the minimal `wp_list_pluck()` test shim. Keep `tests/support/admin-page-sites-list-test-harness.php` as the stable test loader while moving the WordPress shim into a focused support file.

Implementation status: implemented locally as a test-only support split. The Sites-list `wp_list_pluck()` shim now lives in `tests/support/admin-page-sites-list-wordpress-shims.php`, and the existing harness loads it before composing production Sites-list traits and test doubles. The harness class name, helper exposure methods, fake collaborator behavior, fixture rows, and Sites-list assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Sites-list tests pass without assertion changes.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Sites-list behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Local Removal Metric Helpers Split Slice

The diagnostics site metric helper trait mixed general polling/site helper methods with local-removal readiness/counting helpers. The next safe structure-only cleanup was to separate the local-removal diagnostics helpers into their own trait while preserving the existing aggregate counts, support-copy shape, Diagnostics output, protocol behavior, database schema, and live-site state.

Implementation status: implemented locally as a structure-only split. `local_removal_readiness_diagnostics()`, `local_removal_readiness_blocking_reason()`, `is_expired_pending_local_record()`, and `count_repository_rows_for_site()` now live in `includes/traits/trait-diagnostics-local-removal-metric-helpers.php`, and `Alynt_Drime_Backups_Dashboard_Diagnostics` composes the new trait alongside the existing site metric helper trait. Method names, return arrays, readiness boundary, repository-counting behavior, and support-safe aggregate output are unchanged. No UI strings, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, or live-site state was changed.

Implementation target:

- Move `local_removal_readiness_diagnostics()`, `local_removal_readiness_blocking_reason()`, `is_expired_pending_local_record()`, and `count_repository_rows_for_site()` into a dedicated diagnostics local-removal metric helpers trait.
- Compose the new trait into `Alynt_Drime_Backups_Dashboard_Diagnostics` alongside the existing diagnostics site metric helpers.
- Load the new trait from the main plugin bootstrap and diagnostics test bootstrap before `class-diagnostics.php`.
- Keep method names, return arrays, readiness boundary, repository-counting behavior, and support-safe aggregate output unchanged.
- Do not change UI strings, protocol behavior, database schema, remote-action behavior, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, or live-site state.

Acceptance criteria:

- The general diagnostics site metric helper trait is smaller and focused on polling/site helper utilities.
- Diagnostics local-removal readiness aggregate tests continue to pass without assertion changes.
- PHP syntax, targeted diagnostics tests, full tests, lint, build, and whitespace checks pass before commit.

### Diagnostics Local Removal Readiness Test Split Slice

The broad diagnostics test file still grouped polling-state, record-state, and local-removal readiness coverage. The next safe test-only structure cleanup is to move the local-removal readiness aggregate scenario into a focused diagnostics test file without changing fixtures, assertions, production diagnostics, support-copy shape, or runtime behavior.

Implementation target:

- Move `test_local_removal_readiness_counts_archived_records()` from `tests/DiagnosticsTest.php` into a new focused `tests/DiagnosticsLocalRemovalReadinessTest.php` file.
- Keep the existing diagnostics test bootstrap, fixture trait, fixture data, assertions, support-safe expectations, and test method name unchanged.
- Leave production diagnostics, repositories, UI output, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, and live-site state unchanged.

Implementation status: implemented locally as a test-only split. Local-removal readiness aggregate coverage now lives in `tests/DiagnosticsLocalRemovalReadinessTest.php`, while `tests/DiagnosticsTest.php` keeps the broader polling-state and record-state diagnostics coverage. The diagnostics bootstrap, fixture trait, fixture data, assertions, support-safe expectations, and test method name are unchanged. No production diagnostics, repositories, UI output, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- The broad diagnostics test file is smaller and less mixed.
- Focused local-removal diagnostics tests pass without assertion changes.
- Full tests, lint, build, and whitespace checks pass before commit.

### Diagnostics Local Removal Action Count Fixture Split Slice

The local-removal readiness fixture support file still grouped archived site fixtures, snapshot history fixtures, retained action-count fixtures, and non-terminal action-count fixtures in one trait. Keep `tests/support/diagnostics-local-removal-fixtures.php` as the stable loader while moving the action-count fixture helpers into a focused support trait.

Implementation status: implemented locally as a test-only support split. Retained and non-terminal local-removal action-count fixtures now live in `tests/support/diagnostics-local-removal-action-count-fixtures.php`, and the existing local-removal readiness fixture trait composes that focused trait alongside the site fixture trait. Fixture values, helper names, diagnostics assertions, support-safe aggregate behavior, and production diagnostics behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused local-removal diagnostics tests pass unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Site Detail Local Removal Preview Helper Split Slice

The Site Detail local-record panel trait mixed panel rendering, archive/unarchive form rendering, row-hint rendering, and local-removal preview eligibility helpers. The next safe structure-only cleanup is to separate the preview eligibility/counting helpers into their own admin trait while preserving the existing display-only preview boundary.

Implementation target:

- Move `local_record_removal_preview()`, `local_record_removal_blocking_reason()`, `is_expired_pending_local_record()`, and `count_repository_rows_for_site()` into a dedicated Site Detail local-removal preview helper trait.
- Compose the new helper trait into the existing Site Detail local-record panel trait.
- Load the new trait before the local-record panel trait in the plugin bootstrap.
- Keep method names, return arrays, readiness boundary, repository-counting behavior, rendered panel output, and compact archived-row hint output unchanged.
- Do not add remove/delete controls, confirmation forms, POST handlers, database writes, UI string changes, protocol behavior, remote-action behavior, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, or live-site state.

Implementation status: implemented locally as a structure-only split. The Site Detail local-removal preview helpers now live in `includes/traits/trait-admin-page-site-detail-local-removal-preview.php`, and the existing local-record panels trait composes the new helper trait. Method names, return arrays, readiness boundary, repository-counting behavior, rendered panel output, and compact archived-row hint output are unchanged. No remove/delete controls, confirmation forms, POST handlers, database writes, UI string changes, protocol behavior, remote-action behavior, backup creation, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, release behavior, deployment state, or live-site state changed.

Acceptance criteria:

- The Site Detail local-record panel trait is smaller and focused on panel/form/row-hint rendering.
- Archived local-removal preview and row-hint rendering tests continue to pass without assertion changes.
- PHP syntax, targeted local-record rendering tests, full tests, lint, build, and whitespace checks pass before commit.

### Schedule Row Hint Clarity Slice

The Sites table includes compact schedule-management hints when a client reports V2.3 schedule capability. The hint should distinguish preview-only capability from guarded apply-capable clients without adding row-level schedule controls.

Implementation status: implemented, released, and deployed as a display-only row-hint slice. It does not add Sites-row controls, protocol changes, database writes, or remote actions.

Implement a small display-only Sites-row polish:

- Keep the Sites tab display-only for schedule management.
- Label preview-only clients as `Schedule: preview only`.
- Label apply-capable clients as `Schedule: apply gated`, making clear that apply still requires Site Detail, a fresh preview, client opt-in, and confirmation.
- Continue showing the schedule label and current cadence compactly.
- Do not add Sites-row schedule buttons, POST actions, protocol changes, database writes, remote actions, or live-site behavior.

Acceptance criteria:

- Preview-only schedule capability renders a compact Sites-row hint with `Schedule: preview only`.
- Apply-capable schedule capability renders a compact Sites-row hint with `Schedule: apply gated`.
- Missing schedule capability renders no row hint.
- Existing Site Detail schedule controls and action dispatch behavior remain unchanged.

### Enrollment REST Controller Repository Double Structure Slice

The enrollment REST controller test harness still grouped transient shims, fixture builders, and the fake site repository in one support file. Keep the existing harness loader stable while moving the repository double into a focused support file.

Implementation status: implemented locally as a test-only structure cleanup. The fake enrollment REST site repository now lives in `tests/support/enrollment-rest-controller-repository-double.php`, and `tests/support/enrollment-rest-controller-test-harness.php` requires it before defining the transient shims and fixture helpers. No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, release, deployment, backup, restore, cleanup/delete, credential handling, Drime behavior, or live-site state was changed.

Acceptance criteria:

- Focused enrollment REST controller rejection coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, enrollment behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Enrollment REST Controller Transient Shim Split Slice

After the repository double split, the enrollment REST controller test harness still grouped local WordPress transient shims with shared controller fixture builders. Keep the existing harness loader stable while moving the transient shims into a focused support file.

Implementation status: implemented locally as a test-only support split. The `get_transient()`, `set_transient()`, and `delete_transient()` shims now live in `tests/support/enrollment-rest-controller-transient-shims.php`, and `tests/support/enrollment-rest-controller-test-harness.php` loads them before defining shared controller fixtures. Test transient globals, fixture helper names, controller construction, repository double behavior, assertions, and production enrollment behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused enrollment REST controller success and rejection coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, enrollment behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Admin Diagnostics Overview Service Stub Split Slice

The diagnostics rendering harness still grouped action-history rendering helpers, Diagnostics overview rendering helpers, and the fake Diagnostics service used by overview rendering tests. Keep the existing harness loader stable while moving the overview service stub into a focused support file.

Implementation status: implemented locally as a test-only support split. The fake Diagnostics overview service now lives in `tests/support/admin-page-diagnostics-overview-service-stub.php`, and `tests/support/admin-page-diagnostics-rendering-test-harness.php` loads it before defining the overview rendering harness. Harness class names, public methods, fixture payloads, rendered output, assertions, and production diagnostics behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused Diagnostics overview rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Admin Diagnostics Overview Harness Split Slice

After the Diagnostics overview service stub split, the diagnostics rendering harness still grouped the action-history rendering harness and overview rendering harness together. Keep `tests/support/admin-page-diagnostics-rendering-test-harness.php` as the stable loader while moving the overview rendering harness into a focused support file.

Implementation status: implemented locally as a test-only support split. The Diagnostics overview rendering harness now lives in `tests/support/admin-page-diagnostics-overview-test-harness.php`, its no-op renderer stubs live in `tests/support/admin-page-diagnostics-overview-stub-methods.php`, and the existing diagnostics rendering harness loads the overview harness before defining the action-history rendering harness. Existing test files keep requiring the same loader path, harness class names and public methods are unchanged, and rendered output/assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused Diagnostics overview rendering coverage passes unchanged.
- Focused Diagnostics action-history rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Diagnostics Support Summary Test Harness Split Slice

The diagnostics test harness still grouped shared diagnostics fixture builders and the support-summary action aggregate harness together. Keep `tests/support/diagnostics-test-harness.php` as the stable loader while moving the support-summary harness into a focused support file.

Implementation status: implemented locally as a test-only support split. The support-summary action aggregate harness now lives in `tests/support/diagnostics-support-summary-test-harness.php`, and the existing diagnostics test harness loads it alongside the focused core fixture builders in `tests/support/diagnostics-core-fixtures.php`. Existing test files keep requiring the same loader path, harness class names and public methods are unchanged, and diagnostics/support-summary assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused Diagnostics support-summary coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, Diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or remote-action behavior changes.

### Remote Action Dispatcher Snapshot Fixture Split Slice

The remote-action dispatcher test harness still grouped dispatcher builders, site-row builders, and the large remote-action snapshot payload builder together. Keep `tests/support/remote-action-dispatcher-test-harness.php` as the stable loader while moving the snapshot payload fixture into a focused support trait.

Implementation status: implemented locally as a test-only support split. The remote-action snapshot payload fixture now lives in `tests/support/remote-action-dispatcher-snapshot-fixtures.php`, and the existing dispatcher test harness composes it through the same public fixture trait used by existing tests. Existing test files keep requiring the same loader path, fixture method names remain unchanged, and dispatcher assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Dispatcher Schedule Fixture Split Slice

The dispatcher snapshot fixture still grouped top-level remote-action capability fields with the larger nested schedule-management payload. Keep `tests/support/remote-action-dispatcher-snapshot-fixtures.php` as the stable snapshot fixture loader while moving the schedule-management payload builder into a focused support trait.

Implementation status: implemented locally as a test-only support split. Dispatcher schedule-management payload builders now live in `tests/support/remote-action-dispatcher-schedule-fixtures.php`, and the snapshot fixture trait composes that trait while retaining the same `snapshot_row()` fixture method, fixture values, payload shape, and dispatcher assertions. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Dispatcher Accepted HTTP Fixture Helper Slice

After the dispatcher schedule fixture splits, `RemoteActionDispatcherScheduleDispatchTest` still repeated the same accepted HTTP response callback across schedule preview, schedule apply, and schedule rollback-preview dispatch assertions. Keep the dispatcher behavior and signed request assertions unchanged while centralizing the repeated accepted-response test double.

Implementation status: implemented locally as a test-only structure cleanup. `tests/RemoteActionDispatcherScheduleDispatchTest.php` now uses `accepted_http_response()` to capture request details and return the same protocol-2 accepted response shape for schedule preview, apply, and rollback-preview dispatch tests. Each test still provides its own response summary and preserves the legacy preview `code`/`summary` field names where required. Request body assertions, redacted context assertions, action repository capture assertions, and non-mutating rollback-preview assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `RemoteActionDispatcherScheduleDispatchTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Schedule Management Rendering Fixture Split Slice

The admin remote-action rendering fixture file still grouped generic V2 action-history fixtures and the larger schedule-management capability snapshot fixture together. Keep `tests/support/admin-page-remote-action-rendering-fixtures.php` as the stable loader while moving the schedule-management snapshot fixture into a focused support trait.

Implementation status: implemented locally as a test-only support split. The reusable schedule-management rendering snapshot now lives in `tests/support/admin-page-schedule-management-rendering-fixtures.php`, and the existing admin remote-action rendering fixture trait composes it so current tests keep using the same loader, trait, and fixture method names. Existing rendering assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused admin schedule/cleanup rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, schedule behavior, protocol, schema, credential, Drime, backup, restore, cleanup, live-site, release, deploy, or client-site behavior changes.

### Admin Remote Action Context Fixture Split Slice

The admin remote-action rendering fixture file still grouped reusable V2 site/snapshot context fixtures with remote-action history row fixtures. Keep `tests/support/admin-page-remote-action-rendering-fixtures.php` as the stable loader while moving the generic V2 context fixtures into a focused support trait.

Implementation status: implemented locally as a test-only support split. Reusable V2-capable site and snapshot context builders now live in `tests/support/admin-page-remote-action-context-fixtures.php`, and the existing admin remote-action rendering fixture trait composes that trait alongside the schedule-management rendering fixture trait. Current tests keep requiring the same loader path, fixture method names, fixture values, rendered-output assertions, and production rendering behavior. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, schedule behavior, protocol, schema, credential, Drime, backup, restore, cleanup, live-site, release, deploy, or client-site behavior changes.

### Poller Status Payload Fixture Split Slice

The poller test fixture trait still grouped poller collaborator builders, site-row builders, reusable status payloads, and successful HTTP response fixtures together. Keep `tests/support/poller-test-fixtures.php` as the stable loader while moving status payload and HTTP response fixtures into a focused support trait.

Implementation status: implemented locally as a test-only support split. Poller status payload and successful HTTP client fixtures now live in `tests/support/poller-status-payload-fixtures.php`, and the existing poller fixture trait composes them so current tests keep using the same loader, trait, and fixture method names. Existing poller assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused poller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller Site Row Fixture Split Slice

After the status payload split, the poller fixture trait still grouped poller collaborator builders with dashboard site-row construction. Keep `tests/support/poller-test-fixtures.php` as the stable loader while moving site-row construction into a focused support trait.

Implementation status: implemented locally as a test-only support split. Poller dashboard site-row construction now lives in `tests/support/poller-site-row-fixtures.php`, and the existing poller fixture trait composes it alongside status payload fixtures while retaining the same loader path, trait name, `site()` helper name, fixture values, and poller assertions. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused poller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller HTTP Client Fixture Split Slice

After the status payload split, the status payload fixture trait still grouped raw status payload construction with the successful HTTP client fixture. Keep `tests/support/poller-status-payload-fixtures.php` as the stable status fixture loader while moving HTTP response construction into a focused support trait.

Implementation status: implemented locally as a test-only support split. The successful status HTTP client fixture now lives in `tests/support/poller-http-client-fixtures.php`, and the existing status payload fixture trait composes it while retaining the same `payload()` helper, `successful_http_client()` helper, response shape, payload values, and poller assertions. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused poller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller Captured HTTP Client Fixture Helper Slice

After the HTTP client fixture split, the successful manual-poll test still created a one-off HTTP callback to capture request details while returning the same successful status payload response shape already covered by the shared fixture trait. Keep poller behavior and request assertions unchanged while centralizing the captured-response test double.

Implementation status: implemented locally as a test-only support cleanup. `tests/support/poller-http-client-fixtures.php` now exposes `successful_capturing_http_client()` for tests that need both a successful status response and request capture. `tests/PollerTest.php` uses that helper while retaining its URL, cache-bust, method, authorization, snapshot, and site-update assertions. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `PollerTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller Scheduled HTTP Client Fixture Helper Slice

After the captured HTTP helper cleanup, `PollerScheduledPollingTest` still duplicated deterministic vault construction and successful status HTTP callbacks for scheduled-polling assertions. Keep bounded-batch and default-batch assertions unchanged while reusing the shared poller fixture helpers.

Implementation status: implemented locally as a test-only support cleanup. `tests/support/poller-http-client-fixtures.php` now exposes `successful_counting_http_client()` for scheduled-poll tests that need to assert the number of HTTP calls. `tests/PollerScheduledPollingTest.php` now uses the shared `vault()`, `successful_counting_http_client()`, and `successful_http_client()` helpers while retaining the same due-query, processed-count, success-count, and default batch-size assertions. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `PollerScheduledPollingTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller Successful HTTP Response Fixture Helper Slice

After adding captured and counting HTTP client helpers, `tests/support/poller-http-client-fixtures.php` repeated the same successful status response array in each helper. Keep the existing helper names and test behavior unchanged while centralizing the response payload construction inside the fixture trait.

Implementation status: implemented locally as a test-only support cleanup. The poller HTTP fixture trait now uses `successful_http_response()` from `successful_http_client()`, `successful_capturing_http_client()`, and `successful_counting_http_client()`. The successful response code, payload override behavior, request capture behavior, and call-count behavior are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused poller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Poller Manual Test Vault Fixture Helper Slice

After the scheduled poller fixture cleanup, `PollerTest` still constructed the deterministic credential vault inline even though the shared poller fixture trait already exposes `vault()`. Keep the manual poll assertions unchanged while reusing the shared vault fixture.

Implementation status: implemented locally as a test-only readability cleanup. `tests/PollerTest.php` now uses the shared `vault()` helper before building its site row, repositories, captured HTTP client, and poller instance. Snapshot, request, authorization, success, plugin-version, next-poll, and no-failure assertions are unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused `PollerTest` coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Reconciler Repository Double Split Slice

The remote-action reconciler test harness still grouped the fake action repository double and shared reconciler payload fixtures together. Keep `tests/support/remote-action-reconciler-test-harness.php` as the stable loader while moving the repository double into a focused support file.

Implementation status: implemented locally as a test-only support split. The fake reconciler action repository now lives in `tests/support/remote-action-reconciler-action-repository-double.php`, stale-maintenance behavior lives in `tests/support/remote-action-reconciler-stale-methods.php`, and the existing reconciler test harness loads the repository double before defining shared payload fixtures. Current tests keep requiring the same loader path, class names, trait names, and fixture method names. Existing reconciler assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action reconciler coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Reconciler Client Report Methods Split Slice

After the repository double split, the fake remote-action reconciler repository still grouped client-report lookup/recording helpers with stale-maintenance helpers. Keep `tests/support/remote-action-reconciler-action-repository-double.php` as the stable fake repository loader while moving client-report lookup and recording methods into a focused support trait.

Implementation status: implemented locally as a test-only support split. Public-ID lookup and client-report recording methods now live in `tests/support/remote-action-reconciler-client-report-methods.php`, and the existing fake repository composes them alongside the stale-maintenance trait. The fake class name, public properties, method signatures, lookup capture, client-report capture, stale-maintenance behavior, and existing reconciler assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action reconciler coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Dispatcher Schedule Lookup Double Split Slice

The remote-action dispatcher action repository double still grouped fixed schedule-preview/apply lookup responses with request/state recording helpers. Keep `tests/support/remote-action-dispatcher-actions-double.php` as the stable loader while moving schedule lookup responses into a focused support trait.

Implementation status: implemented locally as a test-only support split. Fixed schedule apply and rollback-preview lookup responses now live in `tests/support/remote-action-dispatcher-schedule-lookup-double.php`, and the existing dispatcher action repository double composes them while retaining the same class name and public methods. Existing dispatcher tests keep requiring the same loader path and assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote Action Dispatcher Action State Double Split Slice

After the schedule lookup split, the remote-action dispatcher action repository double still grouped request/state recording helpers with the fixed repository loader. Keep `tests/support/remote-action-dispatcher-actions-double.php` as the stable loader while moving request/state recording methods into a focused support trait.

Implementation status: implemented locally as a test-only support split. Request creation and state/dispatch recording methods now live in `tests/support/remote-action-dispatcher-action-state-double.php`, and the existing dispatcher action repository double composes them alongside the focused schedule lookup trait. The fake class name, public properties, method signatures, returned IDs, captured request/state behavior, schedule lookup behavior, and existing dispatcher assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action dispatcher coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Schedule Management Payload Fixture Split Slice

The admin schedule-management test harness still grouped reusable schedule-management payload fixtures and the rendering harness together. Keep `tests/support/admin-page-schedule-management-test-harness.php` as the stable loader while moving the payload fixture trait into a focused support file.

Implementation status: implemented locally as a test-only support split. The reusable schedule-management payload fixture now lives in `tests/support/admin-page-schedule-management-payload-fixtures.php`, and the existing schedule-management test harness loads it before defining the rendering harness. Current tests keep requiring the same loader path, trait name, class name, and fixture method names. Existing schedule-management rendering assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused admin schedule-management, row-hint, and rollback-preview coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, schedule behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, live-site, release, deploy, or client-site behavior changes.

### Admin Polling-State Rendering Helper Stub Split Slice

The admin polling-state rendering test harness still grouped public test-exposure methods with minimal helper stubs required by composed admin rendering traits. Keep `tests/support/admin-page-polling-state-rendering-test-harness.php` as the stable loader while moving those helper stubs into a focused support trait.

Implementation status: implemented locally as a test-only support split. Minimal decoded-snapshot and backup-source renderer stubs now live in `tests/support/admin-page-polling-state-rendering-helper-stubs.php`, and attention/recovery history exposure now lives in `tests/support/admin-page-polling-state-rendering-history-methods.php`. The existing polling-state rendering harness composes those support traits while retaining the same harness class and public test methods. Current tests keep requiring the same loader path and rendering assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused admin polling-state rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Polling-State Basic Methods Split Slice

After the helper-stub split, the polling-state rendering harness still contained the remaining basic public test-exposure methods for check-status controls, polling pause controls, and next-poll markup. The next safe test-only cleanup is to keep the stable harness loader while moving those basic exposure methods into a focused support trait.

Implementation status: implemented locally as a test-only support split. Basic polling exposure methods now live in `tests/support/admin-page-polling-state-rendering-basic-methods.php`, and `tests/support/admin-page-polling-state-rendering-test-harness.php` composes that trait while retaining the same harness class, public method names, signatures, fixture behavior, and rendered output. Current tests keep requiring the same loader path and rendering assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused polling-state and polling-control rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Polling-State Rendering WordPress Shim Split Slice

After the polling-state rendering helper split, the shared rendering bootstrap still embedded minimal WordPress shim functions alongside support-file loading. Keep `tests/support/admin-page-polling-state-rendering-bootstrap.php` as the stable loader while moving the rendering-only WordPress shims into a focused support file.

Implementation status: implemented locally as a test-only support split. Minimal `wp_nonce_field()`, `esc_html_e()`, `esc_attr_e()`, and `number_format_i18n()` shims now live in `tests/support/admin-page-polling-state-rendering-wordpress-shims.php`, and the existing polling-state rendering bootstrap requires that file before loading production traits and rendering support doubles. Current tests keep requiring the same bootstrap path, rendered output and assertions remain unchanged, and no production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused polling-state rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, polling behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Snapshot Repository Double Split Slice

The diagnostics repository support file still grouped the fake diagnostics site repository and fake diagnostics snapshot repository together. Keep `tests/support/diagnostics-test-repositories.php` as the stable loader while moving the snapshot repository double into a focused support file.

Implementation status: implemented locally as a test-only support split. The fake diagnostics snapshot repository now lives in `tests/support/diagnostics-snapshot-repository-double.php`, retained-history helpers live in `tests/support/diagnostics-snapshot-history-methods.php`, and the existing diagnostics repository support file loads the snapshot repository before defining the fake site repository. Current tests keep requiring the same bootstrap path, class names, and fixture behavior. Existing diagnostics assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused diagnostics coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Enrollment REST Request Fixture Split Slice

The enrollment REST controller test harness still grouped controller/site/payload fixtures with the minimal REST request authorization test double. Keep `tests/support/enrollment-rest-controller-test-harness.php` as the stable loader while moving the request double helper into a focused support trait.

Implementation status: implemented locally as a test-only support split. The minimal REST request authorization fixture now lives in `tests/support/enrollment-rest-controller-request-fixtures.php`, and the existing enrollment controller fixture trait composes it while retaining the same trait name and helper method. Current tests keep requiring the same loader path and enrollment assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused enrollment REST controller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, enrollment behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Enrollment REST Site/Payload Fixture Split Slice

After the request fixture split, the enrollment REST controller test harness still grouped controller construction with pending-site and enrollment payload builders. Keep `tests/support/enrollment-rest-controller-test-harness.php` as the stable loader while moving site/payload construction into a focused support trait.

Implementation status: implemented locally as a test-only support split. Pending enrollment site-row and enrollment payload builders now live in `tests/support/enrollment-rest-controller-site-payload-fixtures.php`, and the existing enrollment REST fixture trait composes them alongside request fixtures while retaining the same loader path, trait name, `pending_site()` helper, `payload()` helper, fixture values, and enrollment assertions. No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused enrollment REST controller coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, enrollment behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Admin Action Audit Doubles Split Slice

The admin action-audit test harness still grouped the fake enrollment manager, fake event log, and trait-action harness together. Keep `tests/support/admin-page-action-audit-test-harness.php` as the stable loader while moving the two doubles into a focused support file.

Implementation status: implemented locally as a test-only support split. The fake enrollment manager and event log now live in `tests/support/admin-page-action-audit-doubles.php`, and the existing action-audit harness loads them before defining the trait-action harness. Current tests keep requiring the same loader path, class names, and helper behavior. Existing action-audit assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused admin action-audit coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, audit behavior, enrollment behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Uninstall Safety Database Double Split Slice

The uninstall safety test harness still grouped global WordPress uninstall shims with the minimal database double used by uninstall regression tests. Keep `tests/support/uninstall-safety-test-harness.php` as the stable loader while moving the database double into a focused support file.

Implementation status: implemented locally as a test-only support split. The uninstall safety database double now lives in `tests/support/uninstall-safety-wpdb-double.php`, and the existing uninstall safety harness loads it before defining global WordPress shim functions. Current tests keep requiring the same loader path and class name. Existing uninstall assertions remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, uninstall behavior, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused uninstall safety coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, uninstall behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Overview Count Fixture Split Slice

The diagnostics overview rendering fixture still grouped scheduler, record-state, attention-history, restore-readiness, and local-removal count arrays inside one broad fixture builder. Keep `tests/support/admin-page-diagnostics-overview-fixtures.php` as the stable loader while moving the count and scheduler fixture builders into a focused support trait.

Implementation status: implemented locally as a test-only support split. Top-level count fixture builders now live in `tests/support/admin-page-diagnostics-overview-count-fixtures.php`, scheduler diagnostics live in `tests/support/admin-page-diagnostics-overview-scheduler-fixtures.php`, and restore-readiness/local-removal aggregate count builders live in `tests/support/admin-page-diagnostics-overview-aggregate-count-fixtures.php`. The existing diagnostics overview fixture trait composes the same public fixture helper through the stable `tests/support/admin-page-diagnostics-overview-fixtures.php` loader. Current tests keep requiring the same loader path, fixture method name, fixture values, rendered-output assertions, and production diagnostics overview behavior. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused diagnostics overview rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Restore-Readiness Fixture Split Slice

The restore-readiness aggregate test still grouped site rows, snapshot rows, and restore-readiness candidate payloads directly inside the assertion method. Keep `tests/DiagnosticsRestoreReadinessAggregatesTest.php` focused on aggregate/support-copy assertions while moving the restore-readiness site/snapshot/candidate fixtures into a dedicated support trait.

Implementation status: implemented locally as a test-only support split. Restore-readiness site and snapshot builders live in `tests/support/diagnostics-restore-readiness-fixtures.php`, while candidate payload builders live in `tests/support/diagnostics-restore-readiness-candidate-fixtures.php`. The focused aggregate test composes the same stable fixture trait while reusing the existing diagnostics collector helper. The assertion method, expected aggregate counts, support-safe redaction checks, production diagnostics behavior, and support-copy shape remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused restore-readiness aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Restore-Readiness Snapshot Fixture Split Slice

The restore-readiness aggregate support trait still grouped site-row fixtures with the larger snapshot payload fixtures. Keep `tests/support/diagnostics-restore-readiness-fixtures.php` as the stable loader while moving restore-readiness snapshot payloads into a focused support trait.

Implementation status: implemented locally as a test-only support split. Restore-readiness snapshot builders now live in `tests/support/diagnostics-restore-readiness-snapshot-fixtures.php`, and the stable restore-readiness fixture trait composes that trait alongside the candidate fixture trait while retaining the same public fixture methods and values. The aggregate assertions, expected counts, redaction checks, production diagnostics behavior, and support-copy shape remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused restore-readiness aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Schedule-Management Fixture Split Slice

The schedule-management aggregate test still grouped site rows, snapshot rows, and remote-action schedule-management payloads directly inside the assertion method. Keep `tests/DiagnosticsRemoteActionAggregatesTest.php` focused on aggregate/support-copy assertions while moving the schedule-management site/snapshot/payload fixtures into a dedicated support trait.

Implementation status: implemented locally as a test-only support split. Schedule-management site, snapshot, and payload fixture builders now live in `tests/support/diagnostics-schedule-management-fixtures.php`, and the focused aggregate test composes that trait while reusing the existing diagnostics collector helper. The assertion method, expected aggregate counts, support-safe redaction checks, production diagnostics behavior, and support-copy shape remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule-management aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Schedule-Management Payload Fixture Split Slice

After the schedule-management fixture split, the support trait still grouped site/snapshot row builders with the larger remote-action schedule-management payload builder. Keep `tests/support/diagnostics-schedule-management-fixtures.php` as the stable aggregate fixture loader while moving payload construction into a focused support trait.

Implementation status: implemented locally as a test-only support split. Schedule-management payload construction now lives in `tests/support/diagnostics-schedule-management-payload-fixtures.php`, and the stable schedule-management fixture trait composes that payload trait while retaining the same public fixture methods, payload values, aggregate assertions, support-safe redaction checks, production diagnostics behavior, and support-copy shape. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule-management aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Cleanup-Preview Fixture Split Slice

The cleanup-preview aggregate test still grouped site rows, snapshot rows, and remote-action cleanup-management payloads directly inside the assertion method. Keep `tests/DiagnosticsCleanupPreviewAggregatesTest.php` focused on aggregate/support-copy assertions while moving the cleanup-preview site/snapshot/payload fixtures into a dedicated support trait.

Implementation status: implemented locally as a test-only support split. Cleanup-preview site, snapshot, and payload fixture builders now live in `tests/support/diagnostics-cleanup-preview-fixtures.php`, and the focused aggregate test composes that trait while reusing the existing diagnostics collector helper. The assertion method, expected aggregate counts, support-safe redaction checks, production diagnostics behavior, and support-copy shape remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused cleanup-preview aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Cleanup-Preview Payload Fixture Split Slice

After the cleanup-preview fixture split, the support trait still grouped site/snapshot row builders with the larger remote-action cleanup-management payload builder. Keep `tests/support/diagnostics-cleanup-preview-fixtures.php` as the stable aggregate fixture loader while moving payload construction into a focused support trait.

Implementation status: implemented locally as a test-only support split. Cleanup-preview payload construction now lives in `tests/support/diagnostics-cleanup-preview-payload-fixtures.php`, and the stable cleanup-preview fixture trait composes that payload trait while retaining the same public fixture methods, payload values, aggregate assertions, support-safe redaction checks, production diagnostics behavior, and support-copy shape. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused cleanup-preview aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Diagnostics Local-Removal Fixture Split Slice

The local-removal readiness aggregate test still grouped archived local-record site rows, retained snapshot histories, retained action counts, and non-terminal action counts directly inside the assertion method. Keep `tests/DiagnosticsLocalRemovalReadinessTest.php` focused on aggregate/support-copy assertions while moving the local-removal fixture builders into a dedicated support trait.

Implementation status: implemented locally as a test-only support split. Local-removal retained snapshot histories, retained action counts, and non-terminal action counts live in `tests/support/diagnostics-local-removal-fixtures.php`, while archived local-record site-row helpers live in `tests/support/diagnostics-local-removal-site-fixtures.php`; the focused aggregate test composes the stable top-level fixture trait while reusing the existing diagnostics collector helper. The assertion method, expected aggregate counts, support-safe redaction checks, production diagnostics behavior, and support-copy shape remain unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused local-removal readiness aggregate coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, diagnostics behavior, support-copy shape, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Capabilities Fixture Split Slice

The remote-action capabilities test still grouped a representative valid capability summary directly inside the main allowlist assertion method. Keep `tests/RemoteActionCapabilitiesTest.php` focused on sanitizer assertions while moving the reusable capability-summary fixture into the existing capabilities fixture trait.

Implementation status: implemented locally as a test-only support split. The representative valid remote-action capability summary now lives in `tests/support/remote-action-capabilities-test-fixtures.php`, and `tests/RemoteActionCapabilitiesTest.php` reuses that fixture while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action capability sanitization coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Cleanup Capability Fixture Split Slice

The cleanup-preview capability test still grouped a larger cleanup-preview capability/result payload directly inside the main sanitizer assertion method. Keep `tests/RemoteActionCapabilitiesCleanupTest.php` focused on cleanup-preview sanitizer assertions while moving the reusable cleanup-preview capability fixture into the existing remote-action capabilities fixture trait.

Implementation status: implemented locally as a test-only support split. The representative cleanup-preview capability summary now lives in `tests/support/remote-action-capabilities-test-fixtures.php`, and `tests/RemoteActionCapabilitiesCleanupTest.php` reuses that fixture while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, cleanup apply behavior, release behavior, deployment state, backups, restore, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused cleanup-preview capability sanitization coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, cleanup behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Schedule Rollback Capability Fixture Split Slice

The schedule rollback-preview capability test still grouped a larger rollback-preview capability/result payload directly inside the main sanitizer assertion method. Keep `tests/RemoteActionCapabilitiesScheduleRollbackTest.php` focused on rollback-preview sanitizer assertions while moving the reusable rollback-preview capability fixture into the existing remote-action capabilities fixture trait.

Implementation status: implemented locally as a test-only support split. The representative schedule rollback-preview capability summary now lives in `tests/support/remote-action-capabilities-test-fixtures.php`, and `tests/RemoteActionCapabilitiesScheduleRollbackTest.php` reuses that fixture while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, cleanup behavior, rollback behavior, release behavior, deployment state, backups, restore, schedule apply/rollback, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule rollback-preview capability sanitization coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, schedule rollback behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Schedule Apply Capability Fixture Split Slice

The schedule-management capability test still grouped the larger schedule-apply capability/result payload directly inside its schedule-apply support assertion method. Keep `tests/RemoteActionCapabilitiesScheduleManagementTest.php` focused on schedule-management support assertions while moving the reusable schedule-apply capability fixture into the existing remote-action capabilities fixture trait.

Implementation status: implemented locally as a test-only support split. The representative schedule-apply capability summary now lives in `tests/support/remote-action-capabilities-test-fixtures.php`, and `tests/RemoteActionCapabilitiesScheduleManagementTest.php` reuses that fixture while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply behavior, rollback behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule-management capability coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, schedule apply behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Repository Schedule Lookup Fixture Split Slice

The schedule lookup repository test still grouped expired schedule-preview rows, successful schedule-apply rows, and sanitized capability arrays directly inside the lookup assertion methods. Keep `tests/RemoteActionRepositoryScheduleLookupTest.php` focused on repository lookup assertions while moving reusable schedule lookup rows and capability fixtures into the existing remote-action repository test fixture trait.

Implementation status: implemented locally as a test-only support split. Expired schedule-preview rows, schedule-apply lookup capabilities, successful schedule-apply rows, and rollback-preview lookup capabilities now live in `tests/support/remote-action-repository-test-harness.php`, and `tests/RemoteActionRepositoryScheduleLookupTest.php` reuses those helpers while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, SQL behavior, remote-action behavior, schedule apply behavior, rollback behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule lookup repository coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, SQL behavior, schedule apply behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Repository Client Reconciliation Fixture Split Slice

The base remote-action repository test still grouped a client reconciliation report payload directly inside the sanitized reconciliation assertion method. Keep `tests/RemoteActionRepositoryTest.php` focused on repository write assertions while moving the reusable client reconciliation payload into the existing remote-action repository fixture trait.

Implementation status: implemented locally as a test-only support split. The client reconciliation report fixture now lives in `tests/support/remote-action-repository-test-harness.php`, and `tests/RemoteActionRepositoryTest.php` reuses that helper while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, SQL behavior, remote-action behavior, schedule apply behavior, rollback behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action repository write coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, SQL behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Repository Support Summary Fixture Split Slice

The client-report repository test still grouped the support-summary aggregate row directly inside the rollback-readiness support-summary assertion method. Keep `tests/RemoteActionRepositoryClientReportTest.php` focused on support-summary assertions while moving the reusable aggregate row into the existing client-report fixture trait.

Implementation status: implemented locally as a test-only support split. The rollback metadata support-summary row fixture now lives in `tests/support/remote-action-repository-client-report-rollback-fixtures.php`, and `tests/RemoteActionRepositoryClientReportTest.php` reuses that helper through the existing client-report fixture trait while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, SQL behavior, support-summary behavior, remote-action behavior, schedule apply behavior, rollback behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action repository client-report coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, SQL behavior, support-summary behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Repository Rollback Summary Fixture Trait Split Slice

After the support-summary fixture split, the rollback client-report fixture trait still grouped rollback-preview payload construction with rollback-readiness aggregate summary row data. Keep the existing client-report fixture loader and test assertions stable while moving the aggregate support-summary row helper into a focused support trait.

Implementation status: implemented locally as a test-only support split. The rollback metadata support-summary row helper now lives in `tests/support/remote-action-repository-client-report-rollback-summary-fixtures.php`, and the existing rollback client-report fixture trait composes that focused trait while preserving helper names, fixture values, client-report assertions, and support-summary assertions. `tests/RemoteActionRepositoryClientReportTest.php` loads the focused summary fixture before the rollback fixture. No production PHP, assets, UI strings, protocol behavior, database schema, SQL behavior, support-summary behavior, remote-action behavior, schedule apply behavior, rollback behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused remote-action repository client-report coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, SQL behavior, support-summary behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Schedule Result Fixture Split Slice

The schedule-result capability test still grouped schedule-preview and schedule-apply latest-action alias payloads directly inside the sanitizer assertion methods. Keep `tests/RemoteActionCapabilitiesScheduleResultsTest.php` focused on alias normalization assertions while moving reusable schedule-result payloads into the existing remote-action capabilities fixture trait.

Implementation status: implemented locally as a test-only support split. Schedule-preview alias and schedule-apply alias summary fixtures now live in `tests/support/remote-action-capabilities-test-fixtures.php`, and `tests/RemoteActionCapabilitiesScheduleResultsTest.php` reuses those helpers while preserving every assertion and fixture value. No production PHP, assets, UI strings, protocol behavior, database schema, support-summary behavior, remote-action behavior, schedule apply behavior, rollback behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, credentials, Drime behavior, or live-site state changed.

Acceptance criteria:

- Focused schedule-result capability coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production code, schedule-result behavior, remote-action behavior, protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

### Remote-Action Capabilities Support Trait Split Slice

The shared remote-action capabilities fixture trait became the largest remaining editable PHP support file after multiple focused fixture moves. Keep `tests/support/remote-action-capabilities-test-fixtures.php` as the stable loader required by current tests while splitting cleanup-preview and schedule-management fixture groups into focused support traits.

Implementation status: implemented locally as a test-only support split. Base capability fixtures now live in `tests/support/remote-action-capabilities-base-fixtures.php`, cleanup-preview capability fixtures live in `tests/support/remote-action-capabilities-cleanup-fixtures.php`, schedule and schedule-result fixtures live in `tests/support/remote-action-capabilities-schedule-fixtures.php`, rollback-preview fixtures live in `tests/support/remote-action-capabilities-rollback-fixtures.php`, and the existing `tests/support/remote-action-capabilities-test-fixtures.php` loader composes those focused traits while retaining the original trait name, helper names, require path, and fixture values. Focused RemoteActionCapabilities coverage passes unchanged. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Implementation notes:

- move cleanup-preview capability fixture builders into a dedicated cleanup support trait;
- move Alynt schedule, schedule-apply, and schedule-result alias fixture builders into a dedicated schedule support trait;
- move rollback-preview fixture builders into a dedicated rollback support trait;
- keep the original trait name, helper method names, fixture values, and test require paths stable;
- do not touch production code or runtime behavior.

Acceptance criteria:

- Focused remote-action capabilities, cleanup, schedule-management, schedule-rollback, and schedule-result tests pass unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Remote-Action Capabilities Latest-Action Fixture Split Slice

After the support trait split, the base capability fixture still embedded the representative latest-action payload inside the top-level capability summary builder. Keep `tests/support/remote-action-capabilities-base-fixtures.php` as the stable base fixture trait while moving the latest-action payload into a focused support trait.

Implementation status: implemented locally as a test-only support split. The representative latest-action fixture now lives in `tests/support/remote-action-capabilities-last-action-fixtures.php`, and the existing base capability summary composes that trait while retaining the same `valid_remote_action_capability_summary()` method name, fixture values, sanitized output expectations, and focused capability assertions. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused remote-action capabilities base coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Remote-Action Capabilities Cleanup Latest-Action Fixture Split Slice

After the cleanup fixture split, the cleanup capability fixture still embedded the cleanup-preview latest-action payload inside the top-level cleanup capability summary builder. Keep `tests/support/remote-action-capabilities-cleanup-fixtures.php` as the stable cleanup fixture trait while moving the nested latest-action payload into a focused support trait.

Implementation status: implemented locally as a test-only support split. The cleanup-preview latest-action fixture now lives in `tests/support/remote-action-capabilities-cleanup-last-action-fixtures.php`, and the existing cleanup capability summary composes that trait while retaining the same `cleanup_preview_capability_summary()` method name, fixture values, sanitized output expectations, and focused cleanup capability assertions. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused remote-action capabilities cleanup coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Remote-Action Capabilities Rollback Latest-Action Fixture Split Slice

After the rollback fixture split, the rollback capability fixture still embedded the schedule rollback-preview latest-action payload inside the top-level rollback capability summary builder. Keep `tests/support/remote-action-capabilities-rollback-fixtures.php` as the stable rollback fixture trait while moving the nested latest-action payload into a focused support trait.

Implementation status: implemented locally as a test-only support split. The schedule rollback-preview latest-action fixture now lives in `tests/support/remote-action-capabilities-rollback-last-action-fixtures.php`, and the existing rollback capability summary composes that trait while retaining the same `schedule_rollback_preview_capability_summary()` method name, fixture values, sanitized output expectations, and focused rollback capability assertions. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused remote-action capabilities rollback coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, schedule apply/rollback behavior, cleanup behavior, release behavior, deployment state, backups, restore, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Enrollment REST Secret Fixture Helper Slice

The enrollment REST rejection tests still repeated deterministic pairing-secret and bearer-header construction inline. Keep `tests/support/enrollment-rest-controller-site-payload-fixtures.php` as the stable enrollment REST fixture trait while adding small reusable secret helpers for rejection scenarios.

Implementation status: implemented locally as a test-only fixture cleanup. The shared enrollment REST site/payload fixture trait now provides deterministic pairing-secret and bearer-header helpers, and `tests/EnrollmentRestControllerRejectionTest.php` reuses them while preserving the same pending-site rows, invalid-secret scenarios, rate-limit behavior, authentication flow, expected error codes, and storage assertions. No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, credential behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused enrollment REST rejection coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, credential behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, Drime behavior, live-site behavior, or client-site behavior changes.

### Request Backup Rendering History Fixture Helper Slice

The request-backup rendering test still embedded a successful remote-action history row directly inside the primary assertion. Keep the test class focused on rendering assertions while moving that deterministic action-history row into a small local fixture helper.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/AdminPageRequestBackupRenderingTest.php` now builds the successful request-backup history row through a helper while preserving the same action type, state, client state, timestamps, summaries, count payload, rendered history assertions, and V2.1 request-backup UI coverage. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, backup behavior, release behavior, deployment state, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused request-backup rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, backup behavior, release behavior, deployment state, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or client-site behavior changes.

### Request Backup Rendering Fixture Trait Split Slice

The request-backup rendering test now has stable site, snapshot, and history-row fixture helpers. Keep `tests/AdminPageRequestBackupRenderingTest.php` focused on rendering assertions while moving those deterministic helpers into a dedicated support trait.

Implementation status: implemented locally as a test-only support split. Request Backup detail-site, remote-action snapshot, and successful history-row fixture builders now live in `tests/support/admin-page-request-backup-rendering-fixtures.php`, while `tests/AdminPageRequestBackupRenderingTest.php` composes the trait and retains the same fixture values, rendered strings, form gating assertions, action-history assertions, and missing/disabled-capability coverage. No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, backup behavior, release behavior, deployment state, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused request-backup rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, remote-action behavior, backup behavior, release behavior, deployment state, restore, cleanup/delete apply, schedule apply/rollback, credential handling, Drime behavior, live-site behavior, or client-site behavior changes.

### Remote Action History Row Fixture Helper Move Slice

The remote-action history rendering detail test still owned a generic `history_row()` helper even though the broader remote-action rendering fixture trait already provides shared history rows, site fixtures, and snapshots. Keep the assertion class focused on schedule-detail rendering while moving the generic encoded-context row helper into the shared rendering fixture trait.

Implementation status: implemented locally as a test-only fixture cleanup. The `history_row()` helper now lives in `tests/support/admin-page-remote-action-rendering-fixtures.php`, and `tests/AdminPageRemoteActionHistoryRenderingTest.php` reuses it through the existing fixture trait while preserving the same action defaults, encoded redacted context payloads, schedule apply details, rollback-preview details, pending-cadence wording, and assertions. No production PHP, assets, UI strings, protocol behavior, database schema, Remote Action History behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused remote-action history rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, Remote Action History behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Status Classifier Classify Payload Fixture Helper Move Slice

The baseline status-classifier test still owned a generic classify-at-default-time helper even though the shared status-classifier fixture trait already owns active-site, snapshot, healthy-payload, and backup-source fixture builders. Keep the baseline test focused on status assertions by moving the generic payload classification helper into the shared fixture trait.

Implementation status: implemented locally as a test-only fixture cleanup. `classify_payload()` now lives in `tests/support/status-classifier-test-fixtures.php`, and `tests/StatusClassifierTest.php` reuses it through the existing fixture trait while preserving the same active-site fixture, snapshot fixture, default timestamp, classifier calls, categories, messages, and backup-source payload assertions. No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused baseline status-classifier coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Diagnostics History Collector Helper Reuse Slice

The diagnostics history/support test still repeated diagnostics object construction even though the shared diagnostics fixture trait already provides a collector helper for focused site, snapshot, history, and action-count fixtures. Keep the test focused on redaction and aggregate assertions by reusing the existing collector helper.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/DiagnosticsHistoryAndSupportTest.php` now uses `collect_diagnostics()` for attention-history, recent poll outcome, and support-summary redaction coverage while preserving the same site rows, retained snapshot histories, secret-bearing fields, aggregate expectations, support-safe JSON assertions, and redaction checks. No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused diagnostics history/support coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, diagnostics behavior, support-copy shape, polling behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Cleanup Preview Rendering Snapshot Fixture Move Slice

The cleanup-preview rendering test still owned a reusable cleanup-preview remote-action snapshot helper even though the shared remote-action rendering fixture trait already supports related Site Detail history and snapshot fixtures. Keep the cleanup-preview test focused on rendering assertions while moving the deterministic snapshot builder into the shared fixture trait.

Implementation status: implemented locally as a test-only fixture cleanup. `cleanup_preview_snapshot()` now lives in `tests/support/admin-page-remote-action-rendering-fixtures.php`, and `tests/AdminPageCleanupPreviewRenderingTest.php` reuses it through the existing trait while preserving the same remote-action payload defaults, cleanup-management defaults, latest preview evidence fixture, supported/unsupported capability coverage, and preview-only assertions. No production PHP, assets, UI strings, protocol behavior, database schema, Cleanup Preview behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused cleanup-preview rendering coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, Cleanup Preview behavior, remote-action behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Status Classifier Source Evidence Helper Reuse Slice

After moving the generic classify-at-default-time helper into the shared classifier fixture trait, the source-evidence classifier tests can reuse it instead of repeating the same active-site, snapshot, and fixture timestamp wrapper in each assertion.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/StatusClassifierSourceEvidenceTest.php` now uses `classify_payload()` for source queue warnings, stale source evidence, no-known-source, and source-summary not-configured coverage while preserving the same payload values, backup-source fixtures, default classification timestamp, categories, and messages. No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, backup-source behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused status-classifier source-evidence coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, classifier behavior, backup-source behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Enrollment Manager Harness Helper Slice

The enrollment manager test repeated the same manager construction with a test repository and origin validator in each case. Keep the test assertions focused on enrollment outcomes by moving that construction into the existing enrollment manager fixture trait.

Implementation status: implemented locally as a test-only fixture cleanup. `tests/support/enrollment-manager-test-harness.php` now provides a `manager()` helper, and `tests/EnrollmentManagerTest.php` reuses it while preserving the same repository doubles, origin validator, input payloads, display-token assertions, duplicate-pending behavior, storage-failure behavior, and error-code expectations. No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, pairing-token behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changed.

Acceptance criteria:

- Focused enrollment manager coverage passes unchanged.
- Full dashboard tests, lint, build, and whitespace checks continue to pass.
- No production PHP, assets, UI strings, protocol behavior, database schema, enrollment behavior, pairing-token behavior, release behavior, deployment state, backups, restore, cleanup/delete apply, schedule apply/rollback, credentials, Drime behavior, live-site behavior, or client-site behavior changes.

### Diagnostics Runtime Boundary Visibility Slice

The Diagnostics Runtime panel already shows the installed dashboard version and polling contract, but it does not explicitly restate the current safety boundary. Add a small support-safe row that makes the runtime boundary visible to operators without changing capabilities or behavior.

Implementation status: implemented locally as a display-only Diagnostics Runtime visibility slice. The Diagnostics Runtime panel now includes a `Remote-action boundary` row stating that only explicitly opted-in signed client actions are available and that the dashboard stores no Drime API credentials. Focused Diagnostics overview rendering coverage confirms the row and copy. No setting, endpoint, database write, remote action, credential path, backup, restore, cleanup/delete apply, schedule apply/rollback behavior, deployment behavior, live-site behavior, or client-site behavior was added.

Implementation notes:

- Add a Diagnostics Runtime row labeled `Remote-action boundary`.
- State that only explicitly opted-in signed client actions are available and that the dashboard stores no Drime API credentials.
- Keep this as display-only UI copy; do not add a new setting, endpoint, database write, remote action, credential path, backup, restore, cleanup/delete apply, schedule apply/rollback behavior, deployment behavior, live-site behavior, or client-site behavior.

Acceptance criteria:

- Diagnostics overview rendering coverage confirms the new runtime-boundary row and copy.
- Full dashboard tests, lint, build, translation template coverage, and whitespace checks pass.
- No protocol, schema, credential, Drime, backup, restore, cleanup, schedule, live-site, release, deploy, or client-site behavior changes.

## Version 1 Non-Goals

Do not add any dashboard-to-client or dashboard-to-Drime mutation:

- no remote backup execution;
- no remote restore or restore preparation;
- no remote deletion or retention cleanup;
- no remote settings changes;
- no remote credential changes;
- no remote local-file cleanup;
- no filesystem browsing or path-mode payloads;
- no Drime API token collection, storage, or forwarding;
- no arbitrary URL health checks;
- no email, Slack, or other notification channels in the initial v1 baseline;
- no multi-tenant customer portal or non-administrator frontend.

Local dashboard actions needed to manage enrollment, polling, and retained status history are in scope. They must remain capability- and nonce-protected.

Remote actions remain outside unresolved v1 scope. V2.1 is the first separately planned and threat-modeled action slice, limited to signed `scan_upload_now`; broader action classes intentionally remain future gated phases. See `docs/V2_REMOTE_ACTIONS_PLAN.md` for the separate planning boundary and candidate feature sequence.

## Eventual Host And Live-Site Gate

The approved eventual target profile is:

| Field | Value |
| --- | --- |
| Site key | `control-sitesmanage` |
| Mode | `live-only` |
| Live site | `https://control.sitesmanage.com` |
| SSH alias | `admin` |
| WordPress path | `/var/www/control.sitesmanage.com/htdocs` |
| Deployment method | `scp` |

This target is recorded for architecture and rollout planning only. Do not inspect, upload, install, activate, configure, migrate, schedule, or pair anything on the live site during repository implementation.

Before the first live operation, present the exact profile above again and obtain explicit approval for the exact operation. Plugin upload, activation, database-table creation, schedule activation, and first client enrollment are separate observable live changes and must be included in that approval scope.

## Ownership Boundary

### Dashboard plugin owns

- dashboard-side site registry;
- pending enrollment records and one-time token lifecycle;
- encrypted outbound polling credentials;
- URL validation and safe HTTP transport;
- manual and scheduled read-only polls;
- latest status and retained snapshots;
- health classification and stale-site detection;
- WordPress administrator screens;
- local revocation and pause/resume of dashboard records, with removal reserved for a later local-only operator slice;
- dashboard diagnostics that never expose credentials or raw sensitive responses.

### Uploader plugin owns

- administrator opt-in on each client site;
- pairing-token entry and destination confirmation;
- a fixed read-only status endpoint, disabled by default;
- polling-credential verification and revocation;
- rate limiting;
- construction of the existing redacted status payload with path mode disabled;
- proof that no secret, filesystem path, request body, signed URL, cookie, nonce, salt, database credential, or package content enters the external payload.

The uploader change is a separate feature slice in the existing uploader repository. It needs its own plan, restore point, tests, version bump, feature reviews, and release. Do not copy uploader internals into the dashboard repository.

## Version 1 User Stories

1. As an administrator, I can register a site label, expected HTTPS URL, and environment, then generate a short-lived one-time pairing token.
2. As a client-site administrator, I can paste that token into Alynt Drime Backups Uploader, see which dashboard origin will receive the enrollment, explicitly opt in, and revoke the connection later.
3. As a dashboard administrator, I can confirm the first authenticated read-only status check before enrollment becomes active.
4. As a dashboard administrator, I can see all enrolled sites in a scannable list with a plain-language status.
5. As a dashboard administrator, I can open a site detail view for the latest redacted payload and recent status history.
6. As a dashboard administrator, I can run **Check Now** without triggering any backup or changing the client.
7. As a dashboard administrator, I can see when a site has stopped reporting or has an authentication, network, schema, or payload error.
8. As a dashboard administrator, I can revoke a local dashboard registration or pause/resume scheduled polling without sending a remote action; a later local-only slice may add permanent removal after separate review.

## Pairing And Authentication Contract

### Token format and lifecycle

- Generate secrets with `random_bytes()` and at least 256 bits of entropy.
- Present one opaque, versioned token such as `adb1.<base64url-payload>` for the operator to paste into the client plugin.
- The encoded payload may carry the dashboard HTTPS origin, a random enrollment identifier, and the one-time secret. Encoding is transport, not encryption.
- Never put a pairing or polling secret in a URL, query string, browser history, diagnostic event, email, screenshot fixture, or support export.
- Store only a one-way verifier for the pending one-time secret.
- Default expiration: 15 minutes.
- Token use: single successful enrollment only. Expire it immediately on success and allow the dashboard administrator to revoke it before use.
- The dashboard must require the expected client HTTPS origin before token generation. The completing client must match that origin after canonical normalization.

### Enrollment handshake

1. Dashboard administrator creates a pending site with label, expected HTTPS origin, and environment.
2. Dashboard creates and displays the one-time pairing token once.
3. Client administrator pastes the token into the uploader, reviews the dashboard origin, checks an explicit opt-in control, and confirms pairing.
4. The uploader sends a small HTTPS POST to the fixed dashboard enrollment endpoint. It includes the one-time credential, `site_uuid`, normalized home URL, uploader version, status schema version, and its fixed status endpoint URL. It does not include the Drime token or the full status payload.
5. The dashboard validates token expiry and single use, exact expected client origin, UUID format, fixed endpoint path, and supported protocol version.
6. The dashboard creates a separate 256-bit, per-site, revocable polling credential. It stores the usable credential only through an authenticated-encryption credential vault and returns it once to the uploader.
7. The uploader stores only the credential identifier and a one-way verifier needed to authenticate future reads.
8. The dashboard performs the first authenticated GET against the fixed client status endpoint. Enrollment becomes active only if the response is valid, redacted, schema-compatible, and carries the same `site_uuid`.
9. The one-time pairing token is consumed whether the final activation succeeds or reaches a terminal failure. A retry that needs new credential material requires a newly generated token.

### Polling credential

- Scope: read-only status endpoint for one site UUID.
- Transport: HTTPS `Authorization` header; never query parameters.
- Dashboard storage: authenticated encryption at rest, isolated behind one credential-vault class. Derive the local encryption key from WordPress secret material with a documented KDF; never store the derived key in the database.
- Client storage: one-way credential verifier, credential identifier, dashboard identifier/origin, paired time, and last authenticated-read time.
- Rotation: re-pair or use a dedicated local credential-rotation flow that does not add remote control.
- Salt/key changes that make a dashboard credential undecryptable must fail closed and require re-pairing.
- Client revocation must immediately reject subsequent requests.

Version 1 uses a narrowly scoped bearer-style polling credential over HTTPS plus strict rate limiting. Do not design a generic remote command authentication framework.

## Client Status Endpoint Contract

Recommended fixed route:

```text
GET /wp-json/alynt-drime-backups-uploader/v1/status
```

Requirements:

- endpoint registration may exist after the uploader update, but access remains disabled until explicit client-site pairing;
- allow `GET` only;
- require the per-site polling credential;
- return the result of the existing health summary with `include_paths` set to `false`;
- return current status schema `1` initially;
- add response headers that discourage storage by intermediate caches;
- use compact, stable error codes without raw exception text;
- apply per-credential and per-origin/IP throttling with bounded storage;
- never return a WordPress nonce as authentication;
- never return WordPress user, plugin setting, path, log, package, or Drime response data outside the documented redacted contract;
- do not register POST, PUT, PATCH, or DELETE command routes in v1.

The dashboard must accept only documented schema-1 fields. Unknown additive fields may be retained only after a redaction-safe allowlist decision; the initial implementation should ignore them. Missing required fields or unsupported schema versions produce an `Incompatible` state rather than best-effort guessing.

## Safe Outbound Polling And SSRF Controls

The dashboard is an authenticated HTTP client and therefore needs an explicit server-side request-forgery boundary.

- Accept public HTTPS origins only in v1.
- Do not accept URL user info, fragments, IP-literal hosts, localhost names, nonstandard ports, or private, loopback, link-local, multicast, carrier-grade NAT, documentation, or reserved address ranges.
- Store a canonical origin, not an arbitrary full endpoint URL.
- Build the endpoint from the canonical origin plus the fixed uploader route.
- Validate the destination at registration, pairing completion, and every poll.
- Use WordPress safe HTTP APIs with unsafe-URL rejection enabled.
- Disable redirects; never forward the authorization header to a redirected origin.
- Send no WordPress cookies or dashboard credentials other than the site-scoped polling credential.
- Set a short timeout, a strict response-size limit, a JSON content expectation, and a conservative JSON-depth limit.
- Revalidate DNS/IP safety at request time and fail closed when resolution is ambiguous.
- Store a sanitized error code and operator-safe summary, not raw response bodies or transport traces.

## Status Payload And Health Classification

The initial consumer contract is uploader status schema `1` from `docs/STATUS_PAYLOAD.md`.

Use dashboard receive time as the authoritative `last_seen_at`. Preserve client timestamps only as status evidence; do not let clock drift determine freshness.

Classification precedence:

1. `Pending` — enrollment has not completed its first valid poll.
2. `Paused` — polling was paused locally by an administrator.
3. `Incompatible` — authentication succeeded but the payload schema or required fields are unsupported.
4. `Not reporting` — no successful poll exists within the stale threshold.
5. `Needs attention` — the latest fresh payload has failed uploads, warnings, an unreadable configured outbox, or a cron status that requires attention.
6. `Not configured` — only when the payload contract can positively prove that no supported source is configured. Do not infer this from missing optional fields.
7. `Working` — the latest payload is fresh and no attention condition applies.

Queue count alone is not automatically a failure. Add an attention condition only when successive snapshots prove that a queue is not draining for a documented threshold. An active upload is informational unless it remains unchanged beyond a documented threshold.

Schema 1 does not prove that default-path WPvivid backups were recently observed. The dashboard must not label them as recently observed unless a later uploader contract adds a redacted field and tests for it.

## Data Model

Use custom tables from the first implementation. Repeated polling history and indexed stale/attention queries are a better fit than autoloaded options.

Recommended tables:

```text
{$wpdb->prefix}alynt_drime_dashboard_sites
- id bigint unsigned primary key
- public_id char(36) unique
- site_uuid char(36) nullable unique
- site_label varchar(191)
- expected_origin varchar(255)
- environment varchar(32)
- enrollment_status varchar(32)
- pairing_secret_hash varchar(255) nullable
- pairing_expires_at datetime nullable
- polling_key_id varchar(64) nullable
- polling_secret_ciphertext longtext nullable
- plugin_version varchar(64) nullable
- payload_schema_version smallint unsigned nullable
- overall_status varchar(32)
- last_poll_attempt_at datetime nullable
- last_seen_at datetime nullable
- next_poll_at datetime nullable
- consecutive_failures int unsigned
- last_error_code varchar(64) nullable
- last_error_summary text nullable
- created_at datetime
- updated_at datetime
```

```text
{$wpdb->prefix}alynt_drime_dashboard_snapshots
- id bigint unsigned primary key
- dashboard_site_id bigint unsigned
- observed_at datetime
- payload_fingerprint char(64)
- overall_status varchar(32)
- queue_count int unsigned
- uploaded_count int unsigned
- failed_count int unsigned
- active_upload tinyint(1)
- warning_count int unsigned
- cron_status varchar(64)
- payload_json longtext
```

Required indexes include sites by `site_uuid`, `enrollment_status`, `next_poll_at`, `last_seen_at`, due polling `(enrollment_status, paused_at, next_poll_at, id)`, and pending-origin lookup `(expected_origin, enrollment_status, pairing_expires_at)`, plus snapshots by `(dashboard_site_id, observed_at)`, `(dashboard_site_id, payload_fingerprint)`, and latest-site lookup `(dashboard_site_id, id)`.

Database migrations must be versioned, idempotent, covered by tests, and run through WordPress activation/upgrade code. Deactivation unschedules polling but keeps data. Uninstall removes plugin-owned schedules, options, tables, pairing verifiers, and encrypted credentials.

## Polling, History, And Retention

- Default poll interval: 15 minutes.
- Default stale threshold: 60 minutes; it must never be less than three polling intervals.
- Add per-site jitter so all sites are not requested at the same second.
- Use a global scheduler lock plus a per-site poll lock with bounded expiry.
- Process a bounded number of due sites per cron run.
- Manual **Check Now** uses the same transport and validation pipeline as scheduled polling.
- Back off repeated network or server failures exponentially, capped at 6 hours, while preserving the visible stale state.
- Authentication failures do not retry aggressively; mark attention and require credential review or re-pairing.
- Store a snapshot on meaningful payload/status change and at most one unchanged heartbeat snapshot per hour.
- Default snapshot retention: 30 days.
- Run bounded daily cleanup in batches and never remove the latest snapshot for an enrolled site.
- Avoid overlapping polls and make late WP-Cron execution visible in dashboard diagnostics.

## WordPress Admin UI

Use server-rendered, WordPress-native admin screens first. Do not add a JavaScript framework for v1.

### Sites

Use `WP_List_Table` conventions with:

- site label and origin;
- environment;
- overall status;
- last report time;
- uploader version;
- queue, failed, and warning counts;
- cron health;
- row links for **View**, **Check Now**, and local scheduled polling **Pause/Resume**.

Include explicit empty, loading, success, error, and incompatible states. Status must use text and icons in addition to color.

### Add Site / Pairing

- collect label, expected HTTPS origin, and environment;
- generate one one-time token;
- show its expiry and explain that it is displayed once;
- never redisplay the token from stored data;
- allow local cancellation before it is used;
- clearly distinguish the pairing token from the Drime API token.

### Site Detail

- latest plain-language health summary;
- latest validated redacted fields;
- recent status timeline;
- sanitized polling failures;
- enrollment and credential state without displaying a secret;
- local actions for check, pause/resume scheduled polling, and revoke; permanent remove remains a deferred local-only operator slice.

### Attention Queue

Provide a filtered Sites view rather than a separate custom application. Include not-reporting, incompatible, authentication-failed, failed-upload, warning, unreadable-outbox, and cron-attention states.

All screens require a dedicated administrator capability, with `manage_options` as the initial mapping. Every state-changing local action requires a nonce and capability check. All text is translatable with text domain `alynt-drime-backups-dashboard`.

## Diagnostics And Privacy

- Log event codes, site public IDs, timing, HTTP status class, and sanitized summaries only.
- Never log or display pairing tokens, polling credentials, authorization headers, raw response bodies, local paths, Drime identifiers that are not in the approved payload, cookies, nonces, salts, or database credentials.
- Provide a redacted diagnostics summary and health checks for scheduler state, table schema, credential decryptability, and recent poll outcomes.
- Treat a remote error body as untrusted input and never persist it verbatim.
- Avoid analytics and external telemetry in v1.

## Tooling, Tests, And Release Strategy

### Initial repository baseline

- install project guardrails after creating the empty plugin folder;
- use a minimal class-based WordPress structure with prefixed classes;
- add Composer development dependencies for WPCS/PHPCS and PHPUnit-compatible unit tests;
- use Brain Monkey or the established sibling pattern for isolated WordPress behavior tests;
- add npm only for command orchestration or a real asset build need;
- do not add runtime Composer dependencies unless implementation proves they are necessary;
- add baseline diagnostics before broad feature work;
- add Alynt Plugin Updater compatibility only after the GitHub repository identity is verified.

### Dashboard test minimums

- activation and idempotent schema migration;
- token entropy, expiry, one-time use, hashing, and redaction;
- credential-vault round trip and fail-closed behavior;
- canonical URL normalization and blocked SSRF targets;
- fixed endpoint construction, redirects disabled, timeouts, and response-size limits;
- authentication, HTTP, malformed JSON, invalid field, and unsupported-schema failures;
- site UUID match enforcement;
- health-classification precedence;
- scheduler locking, batching, jitter, backoff, and stale thresholds;
- snapshot deduplication and retention;
- capability, nonce, escaping, and uninstall cleanup behavior;
- proof that secrets and raw remote bodies do not enter diagnostics.

### Uploader test minimums

- endpoint inaccessible before explicit opt-in/pairing;
- administrator-only pairing and revocation controls;
- token parsing and dashboard-origin validation;
- credential verifier storage without recoverable raw polling secret;
- missing, malformed, wrong, expired/revoked, and rate-limited credential handling;
- GET-only endpoint behavior;
- exact schema-1 redacted payload and `include_paths=false` enforcement;
- regression proof against secret- and path-like keys/values;
- cache-control behavior;
- absence of command or mutation routes.

### Distribution

- expected release tags: `vX.Y.Z`;
- expected release asset: `alynt-drime-backups-dashboard-X.Y.Z.zip`;
- ZIP top-level folder: `alynt-drime-backups-dashboard/`;
- GitHub release workflow must build from the release tag, exclude development/local files, and upload an idempotently named asset;
- Composer dependencies are development-only for v1 release packaging; CI must build npm assets and exclude `vendor/`;
- release packages should exclude tests, source assets, build scripts, Composer/npm manifests, local deployment helpers, and internal engineering docs while preserving runtime PHP, `readme.txt`, `uninstall.php`, `languages/`, `assets/dist/`, and `LICENSE`;
- deployment rollback archives must be stored outside `wp-content/plugins`; a second plugin-like directory can be discovered and deleted by WordPress;
- configuration, ZIP validation, publication, updater runtime testing, and live deployment remain separate approval-gated workflows.

## Implementation Phases And Gates

### Phase 0 — Approve identity and protocol plan

- approve the repository/package identity table;
- approve custom tables, 15-minute polling, 60-minute stale threshold, and 30-day history defaults;
- approve the pairing/authentication shape and strict public-HTTPS-only v1 boundary;
- confirm the current uploader documentation baseline is safely committed or captured before uploader feature work.

Exit: explicit approval; no scaffold yet.

### Phase 1 — Create repository and safety baseline

- create the approved folder and Git repository;
- install project guardrails;
- add the accepted implementation plan;
- scaffold only the plugin header, loader, activation/deactivation/uninstall boundaries, and minimal test/lint commands;
- verify identity values are consistent;
- create a baseline Git commit.

Exit: minimal plugin activates in an isolated test environment and has no live deployment.

### Phase 2 — Dashboard storage and shell

- implement versioned custom-table migrations;
- implement site repository and snapshot repository;
- build Sites, Add Site, Site Detail, and Attention filter shells with fixture data;
- implement status classifier independently of HTTP transport.

Exit: UI and storage tests pass without a client endpoint.

### Phase 3 — Freeze the cross-plugin protocol

- convert the pairing, authentication, URL safety, rate-limit, payload, and error-code sections into a versioned protocol document;
- define exact request/response examples with placeholder credentials only;
- complete focused threat modeling for secret theft, replay, brute force, SSRF, DNS rebinding, redirect leakage, payload abuse, logging leakage, and compromised client behavior.

Exit: protocol is approved before either repository implements both sides.

Approved Phase 3 baseline artifacts:

- `docs/PROTOCOL_V1.md`
- `docs/THREAT_MODEL_V1.md`

### Phase 4 — Add uploader opt-in and read-only endpoint

- create an external restore point for the uploader repository before broad edits;
- resolve the existing dirty documentation state without discarding user work;
- implement client pairing UI, credential verifier, revocation, rate limiting, and GET-only redacted endpoint;
- run uploader build, lint, tests, feature reviews, UI/UX review, security review, and documentation sync;
- release the uploader update separately before dashboard enrollment relies on it.

Exit: endpoint is disabled by default, authenticated when paired, read-only, redacted, released, and proven in an isolated environment.

### Phase 5 — Dashboard enrollment and manual polling

- implement pending enrollment and one-time token flow;
- implement credential vault and safe HTTP transport;
- implement first-poll activation and **Check Now**;
- test with two disposable WordPress environments over HTTPS or an equivalent isolated integration harness.

Exit: end-to-end pairing and manual status polling pass without scheduled polling or live-site work.

Current progress: pending enrollment creation, protocol-v1 token generation, public-HTTPS origin validation, display-once token UI, local dashboard-record revocation scaffolding, credential-vault encryption/decryption, safe status-request preparation, REST enrollment completion, schema-1 payload validation, first-poll activation, snapshot recording, manual **Check Now**, scheduled read-only polling, local scheduled polling pause/resume controls, bounded batching, locks, jitter, retry backoff, 30-day retention cleanup, baseline redacted admin Diagnostics, optional disabled-by-default structured diagnostics logging, always-on redacted operator action history for dashboard-local actions, operator-focused Sites/Attention/Site Detail polish, accessible status guidance, latest redacted snapshot summaries, support-copy diagnostics, dashboard-side `backup_sources` consumption, Sites-row source summaries, compact backup evidence, WPvivid fallback and schedule-aware freshness policy, historical failed-count alert-noise reduction, dashboard-owned WPvivid external/optional source policy, source reason lines, Diagnostics record-state clarity, Diagnostics cache/freshness clarity, revoked-record guidance, local archive/unarchive controls, action-button layout protection, stale-cache protection, V2.1 action opt-in token generation, signed `scan_upload_now` dispatch, bounded redacted remote-action history, V2.3 preview-only schedule capability reporting/display, signed non-mutating `schedule_preview`, guarded `schedule_apply` for the Alynt scan/upload cadence, display-only Sites-row schedule hints, release packaging, and approval-gated live deployment are implemented and deployed to the dashboard host. Dashboard-side rollback-readiness display/support hardening and non-mutating `schedule_rollback_preview` dashboard dispatch/UI controls are released and deployed through dashboard `0.1.43`, with additional local readiness-state UI polish committed after `0.1.45`; controls remain hidden unless the latest client capability explicitly advertises rollback-preview support. One PureCleanse-only rollback-preview proof completed on 2026-09-30 and ended with rollback-preview disabled again, rollback execution unavailable, and PureCleanse restored to `every_15_minutes`. Broad client-side rollback-preview enablement and `schedule_rollback` runtime behavior remain unavailable. A local scheduled-poll throughput tune raises the default bounded batch size so the current enrolled fleet can be refreshed in one normal scheduled run while keeping repository-level caps and explicit test/runtime limits intact.

### Phase 6 — Scheduled polling and history

- implement schedule, jitter, locks, batching, backoff, snapshots, stale detection, and retention cleanup;
- add scheduler diagnostics and time-control tests.

Exit: deterministic automated tests and an isolated multi-site soak test pass.

### Phase 7 — Operator UI and observability completion

- finish Sites, Site Detail, and Attention views;
- add accessible states and plain-language error guidance;
- complete redacted diagnostics and support output;
- run feature light, bloat/structure, UI/UX, security, and documentation-sync workflows.

Exit: v1 feature scope is complete and no v1 remote action exists. V2.1 remote-action work remains governed by the separate V2 protocol/threat-model boundary.

### Phase 8 — Pre-release and local/staging acceptance

- run the toolkit full pre-release workflow at `C:\Users\Captain\Documents\AI Workflows\Toolkits\wp-plugin-toolkit\d4-prompts\ds3-pre-release\FULL_PRE_RELEASE_WORKFLOW_PROMPT.md` using `@FULL_PRE_RELEASE_WORKFLOW_PROMPT.md run`;
- create and inspect the release ZIP;
- test clean activation, upgrade, deactivation, reactivation, and uninstall in disposable WordPress environments;
- test native Alynt Plugin Updater behavior after a release candidate exists;
- verify the dashboard cannot pair arbitrary private-network targets and cannot expose credentials.

Exit: release candidate accepted; still no `control-sitesmanage` change.

### Phase 9 — Approval-gated live rollout

- reconfirm `control-sitesmanage live-only` target details;
- request explicit approval for the exact live upload/install/activate/migrate/schedule scope;
- take a site/database backup or host-level restore point appropriate for the live target before activation;
- deploy one reviewed release artifact;
- verify activation, tables, scheduler, admin access, and diagnostics;
- pair one low-risk known client first and observe before expanding.

Exit: live rollout verified and separately documented.

## Restore-Point Recommendation

No restore point is needed merely to review this planning document, and none was created in this pass.

Before edit-heavy work:

1. Run `@RESTORE_POINT_PROMPT.md run` against `C:\Development\WordPress\Plugins\alynt-drime-backups-uploader` before implementing the client endpoint or pairing UI. The default external snapshot root is `C:\Users\Captain\Documents\AI Workflows\Toolkits\toolkit-snapshots`.
2. Confirm the uploader documentation baseline and preserve any later uncommitted changes before the implementation branch begins.
3. In the new dashboard repository, create a clean baseline commit after minimal scaffolding, then create an external restore point before the first broad storage, enrollment, or polling implementation batch.
4. Before activation on `control.sitesmanage.com`, use an appropriate live-site and production-database backup/restore point in addition to repository snapshots. A source restore point does not protect WordPress runtime data.

## Version 1 Acceptance Criteria

Version 1 is complete only when:

- identity values and release packaging are consistent;
- enrollment is explicit, one-time, expiring, and revocable;
- the client endpoint is disabled before opt-in and accepts GET only;
- the dashboard can poll only a predeclared public HTTPS origin through a fixed route;
- polling credentials are site-scoped, protected at rest, never logged, and revocable;
- the external payload matches the redacted schema contract and contains no path or secret material;
- malformed, oversized, redirected, unsafe-destination, authentication, rate-limit, and incompatible-schema cases fail safely;
- scheduled and manual polling share one validated transport path;
- stale, attention, incompatible, pending, paused, and working states are deterministic and tested;
- status history is bounded and cleanup is tested;
- the UI is accessible, translatable, and understandable without reading logs;
- dashboard, uploader, isolated integration, release-package, and updater tests pass;
- no v1 remote action route, Drime credential field, or client mutation exists; V2.1 `scan_upload_now` remains separate, signed, explicitly opted in, and non-destructive;
- no live deployment occurs without a new explicit `control-sitesmanage live-only` confirmation.

## Decisions Required Before Scaffolding

Approve or revise:

1. the exact repository/package identity table;
2. custom tables from v1;
3. the 15-minute poll, 60-minute stale, and 30-day snapshot-retention defaults;
4. public HTTPS client origins only in v1;
5. the one-time enrollment plus separate revocable polling credential model;
6. GitHub/Alynt Plugin Updater distribution after repository creation.

Until these decisions are confirmed, the next state remains **Phase 0 — Intake**, and scaffolding must not start.
