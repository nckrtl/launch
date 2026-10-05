# Baseline verification handoff

## Candidate and scope

Subtask #1054 verifies the AI-ready, dependency-safe baseline from group base
`be56e805b9ee36ddd55d721c591e4921b61319e7` through implementation head
`9bfe2c2a45334fe7d9cb8d957e56604cdedfd352`. This handoff adds evidence only;
independent review must bind its verdict to the final candidate SHA, including
this document. No merge is authorized.

## Executed checks

On 2026-10-05, the following passed in the task workspace:

- `composer install --no-interaction`: locked install; all 172 packages trusted.
- `bun x playwright install chromium`: Chromium prerequisite available.
- `composer check`: exit 0, including dependency audits, Vet, Pint, Rector dry
  run, PHPStan, VitePlus formatting/lint/types, full PHP suite and browser runner.
- Full PHP suite: **128 passed, 411 assertions**, including architecture,
  hostile inherited-environment isolation, agent refresh failure/crash safety,
  dependency-audit failure handling and runtime dependency build guards.
- Browser suite: **8 passed, 53 assertions**, after successful client and SSR
  builds. Representative end-to-end paths cover homepage hydration, appearance
  modes, mobile dark mode, stack/CTA interaction, keyboard navigation and ARIA
  relationships, with JavaScript-error checks. The runner owns its disposable
  app/SSR services and enables SSR error throwing.
- `AgentContextTest`: actual stdio MCP initialization and `tools/list` expose
  `search-docs` and `application-info`; isolated real Boost refresh preserves
  hand-maintained instructions, skills, selections and MCP configuration.
- Harness `search_docs`, query `Inertia server side rendering testing`, restricted
  to `inertiajs/inertia-laravel`: returned version-specific v3 SSR/testing guidance,
  including `throw_on_error`. This proves documentation search, not live app or
  browser-log inspection through MCP.

## Fresh disposable checkout

An independent dependency install and complete check also passed in a disposable
snapshot created with `git archive HEAD` (no network Git operation). A new local
Git repository was initialized there for tooling. It started without `vendor`,
`node_modules`, `.env`, built assets, or a topology. Commands run in sequence:

```bash
composer install --no-interaction
bun install --frozen-lockfile
bun x playwright install chromium
composer check
```

Both locked installs and the full check exited 0, again producing **128 PHP tests /
411 assertions** and **8 browser tests / 53 assertions**. Tracked manifests and
lockfiles were not updated. The verification runtime was PHP 8.5.9, Composer
2.9.5, Bun 1.4.2 and Node 24.21.0; this is not a claim of execution on every
supported runtime or specifically on the manifest's Bun 1.3.14 version.

## Risks and environmental limitations

- Composer audit reported no advisories. Bun reported the **unresolved high-risk**
  `braces` advisory `GHSA-vfj7-8cjw-p6xm`. The gate passes only with the existing,
  operator-approved, tooling-only exception expiring **2026-10-12 00:00 UTC**.
  See [dependency-policy.md](dependency-policy.md). A passing project check or
  CLEAN code review must not be described as a vulnerability-free audit.
- Bun's prepare hook in the shared checkout encountered `EPERM` attempting to
  chmod the environment-owned generated `.vite-hooks/_/h`. No shared permissions
  were changed and no committed dispatcher was removed. The fresh owned fixture's
  install/prepare step passed without a bypass, distinguishing host ownership
  from a baseline bootstrap failure.
- This host lacks a `bunx` executable; `bun x` was used for its equivalent command.
- SSR builds emitted the existing Inertia plugin sourcemap warning, without a
  build or browser-test failure.
- No UI was changed by this evidence-only subtask, so manual managed-app browser
  validation was not required. Automated real Chromium workflows did run. No
  topology was acquired, and no managed application or live/shared data was used.

## Review evidence

Task-local raw logs are under `.git/orbit/evidence-1054/`:
`composer-install.log`, `bun-install.log`, `chromium-install.log`,
`composer-check.log`, `composer-check-final.log` (after adding this document), and
`fresh-bootstrap-check.log`. The disposable snapshot path
is in `fresh-fixture-path`. These are local artifacts, not published credentials
or portable CI logs. Orbit reruns the canonical project check at handoff.

The `clean-review` deliverable remains the independent reviewer's responsibility:
confirm the final exact candidate, fresh Orbit bootstrap, complete group diff,
context preservation, dependency exception boundaries and passing project gate.
This document supplies implementation evidence; it does not self-certify an
independent CLEAN verdict or change Orbit-owned PR metadata.
