<!--
Guardrail source: wp-workflow-toolkit
Guardrail template version: 1.1.0
Guardrail profile: plugin
Installed or last reconciled: 2026-08-09
-->

# Agent Instructions For Alynt Drime Backups Dashboard

These instructions apply to this WordPress plugin repository. They do not grant access to a LocalWP site, staging site, live site, database, SSH host, deployment target, release, or external service.

## Required Reading

Before changing code:

1. Read `AI_CODING_RULES.md`.
2. Read only the task-relevant sections of `docs/ENGINEERING_STANDARD.md`.
3. Read `docs/IMPLEMENTATION_PLAN.md`, repository docs, and handoff files when present and relevant.
4. Inspect actual source, configuration, dependency versions, tests, and Git status.

If a required file is missing or instructions conflict, stop before editing and report the conflict.

## Operating Contract

- Confirm the requested outcome, target, affected behavior, and non-goals.
- Preserve user changes and unrelated behavior.
- Make the smallest coherent change that satisfies the requirement.
- Do not invent APIs, schemas, paths, assignments, versions, commands, test results, or runtime evidence.
- Do not add dependencies, broaden architecture, change compatibility, or modify public contracts without approval.
- Do not weaken, skip, delete, or rewrite tests merely to obtain a green result.
- Never claim a check passed unless it was executed successfully in this task.
- Treat generated, vendor, dependency, build, minified, and release artifacts according to repository policy.
- Explain material security, compatibility, migration, performance, accessibility, deployment, and rollback consequences.

## Work Sequence

For non-trivial changes:

1. Inspect and map the existing behavior and artifact ownership.
2. State a bounded plan and risk tier.
3. Add or identify a failing regression check when practical.
4. Implement in small reviewable slices.
5. Run the narrowest relevant checks after each slice.
6. Run the repository's canonical final QA command when available.
7. Review the final diff for scope, security, compatibility, test integrity, and accidental debris.
8. Report exact evidence and anything not verified.

Use an approval-gated plan for work spanning multiple subsystems, sensitive data, authentication or authorization, schema or content migration, file operations, external services, packaging, deployment, or release behavior.

## Permission Boundaries

Explicit approval is required before:

- installing, removing, or materially upgrading dependencies;
- destructive or state-changing database operations;
- bulk replacement or regeneration with broad impact;
- changing authentication, authorization, roles, capabilities, secrets, or privacy behavior;
- writing outside this repository;
- using LocalWP, staging, a live site, SSH, deployment tools, or external service mutations;
- changing production content or site settings; and
- committing, pushing, tagging, releasing, publishing, or deploying.

The assigned maximum autonomous risk tier is `R2`. The agent may not raise that tier based on its own confidence.

## Project Commands

- Comprehensive QA: `Not available yet - run PHP syntax checks and focused tests until tooling is installed`
- Lint and static analysis: `php ./vendor/bin/phpcs` after Composer dev dependencies are installed
- Automated tests: `php ./vendor/bin/phpunit` after Composer dev dependencies are installed
- Production build: `Not applicable yet`
- Required runtime check: `clean activation/deactivation/uninstall in an isolated WordPress environment before release`

If a command is unavailable, fails because tooling is missing, or requires an unapproved mutation, report the blocker. Do not substitute an easier check and present it as equivalent.

## Completion Evidence

Report:

- files and artifacts changed and why;
- tests added or updated;
- exact commands executed and results;
- runtime, browser, integration, packaging, or placement checks actually performed;
- final diff review result;
- compatibility, data, security, accessibility, deployment, and rollback notes;
- remaining assumptions, failures, or unverified areas; and
- the next approval gate.
