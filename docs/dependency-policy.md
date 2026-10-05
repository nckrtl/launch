# Dependency trust and update policy

This application uses **Composer** (`composer.lock`) and **Bun 1.3.14**
(`bun.lock`, `package.json#packageManager`). Commit both manifests, their lockfiles,
and any reviewed trust changes together. Do not substitute npm/yarn or update
unrelated dependencies to make validation pass.

## Routine updates: wait seven days

A routine release must have been published for **at least seven full days** before
it is selected, including transitive dependencies. Use narrow updates and review
release notes, upstream identity, package changes, install scripts and lockfile
integrity changes. An age gate reduces supply-chain risk; it is not proof of safety.

- **Composer:** Laravel Vet is an explicitly allowed Composer plugin. `vet.json`
  sets `minimum-release-age: 7` (days), filters update candidates, and records
  package versions and content hashes. Do not disable plugins or bulk reinitialize
  trust to get an update through. Run `vendor/bin/vet -v` to read all changes, then
  `vendor/bin/vet` interactively to record only reviewed packages. Commit the
  resulting `vet.json`. Non-interactive installs/checks reject untrusted changes.
- **Bun:** `bunfig.toml` sets `minimumReleaseAge = 604800` (seconds), with an empty
  `minimumReleaseAgeExcludes` list. This applies to newly resolved direct and
  transitive releases, not packages already locked. Do not use a global age bypass.
  Audit review must verify release timestamps if registry metadata lacks them.
- Vet itself is exempt from its own age filter upstream; manually verify its
  routine releases are seven days old. No wildcard exclusions are authorized.
- Reproduce reviewed dependencies with `composer install --no-interaction` and
  `bun install --frozen-lockfile`, not update commands. Keep Composer's default
  vulnerable-release blocking and explicit plugin allowlist. Review any new Bun
  lifecycle-script trust separately; do not blanket-trust dependencies.

### Vet compatibility and initial trust

Vet requires **PHP 8.4+** and its required extensions. This application already
requires PHP `^8.4`; the task runtime is PHP 8.5.9, so `laravel/vet` **0.2.1** is
installed as a development dependency and its plugin approval is committed. Its
release timestamp is recorded in `composer.lock`.

The initial `vet --init --minimum-release-age=7` records the inherited locked
baseline (172 packages including Vet), not a claim that every historical source
file was independently reviewed. The subsequent CommonMark 2.10.0 → 2.10.3 delta
was read in full with `vet -v` and explicitly trusted. Future changes must receive
individual delta review, not `--init`/`--fresh` resets.

On a PHP <8.4 environment, record the PHP version and Vet compatibility blocker;
do **not** raise PHP merely to install Vet, ignore its platform requirements, or
claim Vet passed. Use a compatible authorized validation environment for this
repository's existing requirement. An unavailable audit is a failing gate, not a
silent success.

## Security updates: fix immediately, do not wait

A CVE/GHSA or equivalent upstream security fix **bypasses the routine delay**.
Do not leave a vulnerable release installed just to satisfy the age threshold.
The update request must include:

1. The advisory URL/ID, affected installed version(s), patched version(s), actual
   dependency paths, exposure and urgency.
2. An exact-package, exact-target-version update and any required transitive
   changes. If the fix is younger than seven days, temporarily add only the
   affected package names to Vet's `minimum-release-age-exclude` or Bun's
   `minimumReleaseAgeExcludes`. Pin the target version while resolving: those
   tools' exclusion lists are package-scoped, not version-scoped. Never use `*`,
   an ecosystem-wide delay of zero, `--no-plugins`, or a blanket audit ignore.
3. An exception record naming the owner, advisory, package/version, reason,
   expiry and removal condition. Require **explicit reviewer approval** of the
   security change, code/trust delta and temporary exception before merge. Agent
   approval alone is not human/risk-owner approval.
4. `composer audit:dependencies` and **full `composer check`**, including non-TIA
   tests, client + SSR builds and browser tests. Record exact results; an audit,
   registry or tool outage fails closed. Do not lower severity thresholds.
5. Remove age exclusions immediately once the patch is seven days old. Until
   then, keep only the narrowly reviewed, pinned exception and its expiry. Do not
   silently renew exceptions. Remove temporary overrides when upstream ranges
   admit the safe version; validate both ecosystems and the full check again.

No release-age exceptions were needed for the patches in this baseline: all
selected fixes were already older than seven days. The unpatched risk acceptance
below is an **audit exception**, not a release-age exemption or security fix.

## Required audits

`composer check` starts with `composer audit:dependencies`:

```bash
composer audit --locked                # all production + development PHP packages
vendor/bin/vet --no-interaction        # content trust, no approval prompts
bun scripts/audit-js.mjs               # runs bun audit --json, validates sole exception
```

All are required and failure stops the gate. Raw `bun audit` intentionally returns
non-zero while the unresolved finding below remains. The wrapper does not use
`--ignore` or suppress the advisory: it prints **UNRESOLVED HIGH RISK**, the URL,
owner, fixed expiry and approved paths on every successful exception evaluation.
Use raw `bun audit --json` to inspect the full upstream report. The script also
checks the live npm registry for a newer stable braces release and fails pending
investigation/removal, rather than continuing to waive a potentially fixed issue.

## Sole unresolved risk: braces (operator-authorized)

**Risk owner Nick approved question 59 on 2026-10-05 at 19:54 CEST.** This authorizes
only high [GHSA-vfj7-8cjw-p6xm](https://github.com/advisories/GHSA-vfj7-8cjw-p6xm),
a stack-exhaustion denial of service in **braces <=3.0.3**, with installed version
**3.0.3** and no published patched release at approval. It does not approve this
implementation or waive any other checks.

**Fixed expiry: 2026-10-12 at 00:00 UTC** (exclusive; the gate fails at that instant).
There is **no automatic renewal**. Remove the exception as soon as a fix ships.
At expiry, either patch/remove the vulnerable tooling or obtain a new explicit
risk-owner decision; do not merely move the date. The expiry and advisory scope
are fixed in `scripts/audit-js.mjs`, with negative enforcement tests in
`tests/Unit/DependencyAuditTest.php`.

Evidenced scope, from `bun.lock`'s resolved dependency graph:

```text
shadcn > fast-glob > micromatch > braces
shadcn > ts-morph > @ts-morph/common > fast-glob > micromatch > braces
```

`shadcn` is a **devDependency**, a developer CLI for generating components, not an
application runtime import. These are the only root-to-braces paths, including
optional and installed peer edges. The risk is confined to trusted local
build/tooling input: do not feed attacker-controlled glob patterns into these
commands or expose the CLI as a service. Moving the CLI out of runtime dependencies
makes its role explicit; deployment packaging must not execute it for requests.

Runtime exclusion is independently enforced in **both client and SSR Vite builds**:
`reject-runtime-braces` rejects module IDs and static/dynamic imports of braces
and every package in the approved chain, including SSR-externalized imports.
This prevents an application import from silently creating runtime reachability
while leaving the lockfile graph unchanged. Browser tests still run through the
existing SSR-owning runner. No runtime dependency on this tooling is authorized.

The audit wrapper fails closed if the advisory ID/URL, severity, affected range,
installed version, number of braces instances, root dependency classification,
complete paths, registry availability or fixed expiry no longer matches. Any other
finding fails, including another braces advisory. A clean report also fails with
instructions to remove the obsolete exception rather than leaving dormant trust.
After a fix: narrowly update/remove the tooling, remove the dedicated exception
logic and its approval-specific tests (retain ordinary audits and appropriate
regression coverage), and rerun the full check. The runtime prohibition can remain.

## Baseline security remediation for explicit review

On 2026-10-05, Composer reported two CommonMark advisories; Bun reported 35 findings
across 12 package names. All but the sole risk above were patched. Advisory IDs
below link via `https://github.com/advisories/<ID>`; the lockfile diff supplies exact
versions and integrity changes. No broad ecosystem upgrade or audit suppression
was used.

| Package                   | Narrow remediation                                                                      | Advisory IDs                                                                                                                                                                                                     |
| ------------------------- | --------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| league/commonmark         | 2.10.0 → 2.10.3; all eight changed files read with Vet, then specifically trusted       | GHSA-97jj-33gv-5xf9, GHSA-3q6v-r5mr-hxv8                                                                                                                                                                         |
| brace-expansion           | 5.0.9 → 5.0.12                                                                          | GHSA-qhr7-859c-m2p7, GHSA-6j4f-fj2g-mc7p, GHSA-q2hr-2g5m-vwhr                                                                                                                                                    |
| fast-uri                  | 3.1.5 → 3.1.8                                                                           | GHSA-5jgf-p345-68v8, GHSA-f65p-4m7j-42xc, GHSA-fph4-wmhf-6fwf, GHSA-jqff-g426-hqxp, GHSA-qw65-cvwx-89v3, GHSA-hrr3-gc8f-f4qj                                                                                     |
| hono                      | 4.13.0 → 4.13.7                                                                         | GHSA-gqvv-2mrq-wpjv, GHSA-g6gw-c38x-mqfc, GHSA-crvj-82cr-hjcx, GHSA-hxh3-vqpv-xpqv                                                                                                                               |
| ip-address                | 10.4.0 → 10.7.1                                                                         | GHSA-rpw4-54j3-4h4q, GHSA-2vr4-cq9g-pvrc, GHSA-j6r3-76f7-8jcv, GHSA-h3mg-xc3c-68pw                                                                                                                               |
| js-yaml                   | 4.3.1 → 4.3.2                                                                           | GHSA-2883-xcg3-v3hh                                                                                                                                                                                              |
| nanoid                    | 3.3.17 → 3.3.18                                                                         | GHSA-2v37-7h3g-55p8                                                                                                                                                                                              |
| qs                        | 6.15.3 → 6.16.0                                                                         | GHSA-x5fp-wj9c-mxmx, GHSA-4mjr-xmp4-gh2g                                                                                                                                                                         |
| undici                    | 7.29.0 → 7.29.1                                                                         | GHSA-3wwx-pv8p-q78v, GHSA-pmjh-fq2x-6v4x, GHSA-r53p-7pc4-xj5r, GHSA-rfgv-xxqx-mfg5, GHSA-3xpg-4rpp-hhhm, GHSA-2jfj-6hjv-fm6j, GHSA-2gqq-gqf2-x968, GHSA-w293-vg96-wgc3, GHSA-8436-99hf-9mmv, GHSA-rx4f-c7p8-82vq |
| valibot                   | 1.2.0 → 1.4.2; exact override/resolution because Storybook MCP pins 1.2.0               | GHSA-5qjj-4xww-7phc                                                                                                                                                                                              |
| vitest and @vitest/mocker | 4.1.10 → 4.1.11; exact override/resolution because VitePlus/browser tooling pins 4.1.10 | GHSA-82fw-gwwq-j7x9                                                                                                                                                                                              |

The valibot and Vitest overrides patch the vulnerable packages rather than ignore
advisories; they are limited to compatible minor/patch versions. Remove them when
the upstream toolchain admits safe versions. Reviewer confirmation must cover the
Composer audit, raw Bun finding versus enforced exception, Vet compatibility and
trust, override compatibility, and full-check evidence. The operator's braces
approval is not a substitute for that dependency review.

### Implementation validation (2026-10-05)

- `composer check` passed: Composer reports no advisories; Vet reports all 172
  packages trusted; the Bun wrapper prints the one unresolved high risk and
  validates Nick's narrow exception. Pint, Rector dry-run, PHPStan level 9 and
  VitePlus lint/format/type checks passed.
- Full non-TIA Unit/Feature/Architecture suite: **128 tests, 411 assertions**.
  Enforcement coverage includes expiry, scope/identity/range/severity changes,
  extra/aliased instances, workspaces, optional/peer/runtime paths, new releases,
  missing registry metadata and externalized runtime imports. Real disposable
  client and SSR builds prove static/dynamic forbidden imports fail, while safe
  builds pass (`tests/Unit/RuntimeDependencyBuildTest.php`).
- Production client and SSR builds passed; the existing SSR-owning Pest Browser
  runner passed **8 tests, 53 assertions**, including homepage and desktop/mobile
  appearance interactions with JavaScript-error assertions. No additional manual
  UI discovery was needed: this subtask has no changed user-visible UI surface.
- `composer validate --strict`, `git diff --check`, and locked Bun installation
  passed. Raw Bun audit (also checked on pinned Bun 1.3.14) still reports exactly
  the approved braces advisory; it is **not** claimed to be clean. Registry
  timestamps confirmed every selected JS patch was older than seven days.
- Separately, Bun's `prepare` initially failed with `EPERM` trying to chmod the
  shared `.vite-hooks/_/h` dispatcher. Dependencies were reproduced with
  `bun install --frozen-lockfile --ignore-scripts`; shared hooks and permissions
  were preserved. This installation-access issue did not prevent the full check
  from passing and is not a security or validation waiver.
