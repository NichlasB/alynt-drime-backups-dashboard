<!--
Guardrail source: wp-workflow-toolkit
Guardrail template version: 1.1.0
Guardrail profile: plugin
Installed or last reconciled: 2026-08-09
-->

# Alynt Drime Backups Dashboard Engineering Standard

## Priority

Apply instructions in this order:

1. user-approved task scope and safety decisions;
2. repository `AGENTS.md`;
3. project-specific `AI_CODING_RULES.md`;
4. this standard;
5. repository architecture, testing, contribution, and release documentation;
6. established source patterns and tool configuration.

Report conflicts before editing. Never silently select the least restrictive interpretation.

## Engineering Process

### Before editing

- Confirm behavior, non-goals, target, environment, and artifact ownership.
- Inspect Git status and preserve unrelated work.
- Locate entry points, callers, public contracts, context files, tests, build configuration, compatibility declarations, and deployment boundaries.
- Trace data and control flow across PHP, JavaScript, CSS, REST, AJAX, database, filesystem, cron, and external services as applicable.
- Identify the risk tier, failure modes, rollback path, and verification layers.

### During implementation

- Keep the patch bounded and reviewable.
- Match established naming and architecture unless those are the defect.
- Prefer explicit state, validation, and ownership over clever code.
- Handle error paths, partial failure, empty states, and repeated execution.
- Add or update evidence at the layer that can actually fail for the protected behavior.
- Stop when evidence contradicts the plan.

### Before completion

- Run the canonical QA contract when available.
- Review the complete diff and any generated output.
- Search for debugging debris, bypasses, new TODOs, weakened assertions, ignored warnings, and accidental scope expansion.
- Distinguish checks that ran from checks that remain pending.
- Record compatibility, security, accessibility, data, content, performance, deployment, and rollback implications.

## Trust Boundaries

For each changed path, identify:

- the data or content source;
- expected type and allowed domain;
- authentication, authorization, and request-intent requirements;
- validation, normalization, sanitization, and escaping;
- storage or database ownership;
- output or transport context;
- failure, retry, and idempotency behavior; and
- sensitive-data and retention implications.

Escape as late as practical for the actual context. Sanitation does not replace validation, and nonces do not replace authorization.

## Database, Files, Remote Requests, And State

- Use WordPress APIs when they preserve the required semantics.
- Prepare dynamic SQL values and allowlist identifiers.
- Bound queries and avoid request-time unindexed scans.
- Version migrations and make reruns or recovery explicit.
- Resolve file paths beneath approved roots and defend against traversal, unsafe overwrite, and hostile archives.
- Restrict user-influenced remote destinations and redirects.
- Set bounded timeouts, response limits, retry rules, and safe failure behavior.
- Require an approved site workflow before LocalWP, staging, live, SSH, database, deployment, or external-service mutation.
- In the AI Workflows environment, that workflow must resolve the registry site key and mode, pass the mandatory target confirmation gate, check Novamira MCP for LocalWP work, and preserve the separate live-write approval gate.

## Errors, Privacy, And Observability

- Return useful errors without exposing stack traces, paths, SQL, secrets, or private records.
- Log structured operational facts, not full requests by default.
- Redact credentials, cookies, authorization headers, nonces, payment data, and unnecessary personal information.
- Do not use suppressed errors or empty catch blocks.
- Remove temporary diagnostics or place them behind an approved, capability-protected system.

## Compatibility, Performance, And Resource Safety

- Honor PHP 7.4, WordPress 6.0, and documented browser/build support.
- Check version-dependent APIs before use.
- Avoid unbounded loops, queries, recursion, remote calls, file reads, or in-memory data construction.
- Batch large work with resumable state.
- Cache only when invalidation, privacy, multisite scope, and failure behavior are understood.
- Never optimize by removing authorization, validation, escaping, accessibility, or meaningful tests.

## Testing Standard

Use the lowest-cost layer that faithfully proves the contract:

| Layer | Appropriate evidence |
|---|---|
| Static/lint | Syntax, style, types, configured rules |
| Pure unit | Deterministic logic without WordPress state |
| WordPress integration | Hooks, capabilities, options, metadata, lifecycle, core APIs |
| Database/migration | Schema, upgrade, rerun, partial failure |
| AJAX/REST | Authorization, nonces, validation, responses, errors |
| Render/template | Escaping and output contracts |
| Browser/end-to-end | Layout, interactions, accessibility, full request path |
| Visual/responsive | Intended appearance across approved viewports |
| Controlled load | Bounded volume, memory, time, rate, and cleanup |

Mocks must not bypass the boundary being claimed as tested. Flaky tests require diagnosis; retries are not a default fix.

## Risk Tiers And Authorization

The user or repository policy assigns the maximum tier. The agent cannot promote itself.

| Tier | Typical work | Minimum control |
|---|---|---|
| R0 | Read-only explanation, inventory, planning | Evidence-backed report |
| R1 | Documentation, tests, formatting, bounded mechanical changes | Diff review and relevant checks |
| R2 | Bounded code without sensitive state or public-contract changes | Plan, automated gates, final review |
| R3 | Authentication, privacy, migrations, destructive data, file operations, remote trust, or public API changes | Explicit approval, strong or human review, integration evidence |
| R4 | Production deployment, live writes, secrets, irreversible operations, release publication | Separate operational workflow and explicit approval; never autonomous by default |

Use the highest tier touched by the task.

## Plugin Lifecycle And Data

- Design activation and upgrades for partial failure and safe reruns.
- Preview destructive cleanup and require approval.
- Do not assume tables, options, roles, upload directories, or scheduled events exist.
- Validate uninstall behavior separately from deactivation.
- Consider network activation and per-site state only after multisite support is explicitly designed.

## Plugin Request Surfaces

- Every REST route needs an explicit `permission_callback`.
- Authenticate and authorize AJAX before sensitive reads or writes.
- Validate settings input and route arguments.
- Protect private downloads and admin actions with capability and ownership checks.
- Test public extension contracts and hook argument stability.

## Plugin Packaging

- Build from documented source inputs.
- Inspect the ZIP root, required runtime dependencies, translations, built assets, and exclusions.
- Do not include repository metadata, tests, coverage, local tooling, agent instructions, secrets, or temporary files unless explicitly required.
- Exclude `AGENTS.md`, `AI_CODING_RULES.md`, `docs/ENGINEERING_STANDARD.md`, `opencode.json`, and `.opencode/` from release ZIPs.

## Canonical QA Contract

- Comprehensive QA: `Not available yet - run PHP syntax checks and focused tests until tooling is installed`.
- Lint and static analysis: `php ./vendor/bin/phpcs` after Composer dev dependencies are installed.
- Automated tests: `php ./vendor/bin/phpunit` after Composer dev dependencies are installed.
- Production build: `Not applicable yet`.
- Runtime acceptance: `clean activation/deactivation/uninstall in an isolated WordPress environment before release`.

If a check is missing, record a tooling gap. Do not redefine completion around the checks that happen to exist.

## Required Completion Report

```text
Outcome: [completed / partially complete / blocked]
Risk tier: [R0-R4 and rationale]
Files and artifacts changed:
- [path or artifact - reason]
Verification executed:
- [exact command or runtime check - result]
Diff and ownership review:
- [scope, security, compatibility, generated/database-resident artifacts]
Implications:
- [security, data, content, compatibility, performance, accessibility, deployment, rollback]
Not verified:
- [item and reason]
Next approval gate:
- [action or none]
```
