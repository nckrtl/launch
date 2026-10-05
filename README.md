# Launch

The marketing and documentation website for Launch.

This application is built from [`nckrtl/launch-starter-kit`](https://github.com/nckrtl/launch-starter-kit). The reusable starter remains in that repository; this repository owns the Launch landing page, brand assets, and the guides published on `launch.nckrtl.com`.

## Fresh Orbit workspace

From the repository root, use this single bootstrap and verification path:

```bash
composer install
bun install
bunx playwright install chromium
composer check
```

Prerequisites: PHP 8.4+ with SQLite, Composer, Bun, Node (SSR), Chromium's OS
libraries, and a coverage driver (PCOV/Xdebug) for optional local Pest TIA.
Install **locked** dependencies; do not update them to make a check pass. Bun's
prepare script installs the committed VitePlus hook dispatcher. No production
credentials, database migrations, external service, or topology are needed for
these checks. If Chromium needs privileged OS dependencies, report the missing
access rather than changing shared host permissions.

Orbit workspaces start without a topology. For interactive discovery that needs
one, request an allocated disposable topology through a blocked reviewer consult;
never acquire or release one yourself. Once Orbit supplies a managed URL, use that
URL. Do not run `composer dev` or `php artisan serve` over a managed application.
Do not assume another workspace's `launch.test` belongs to this task. Published
guides under `resources/markdown/environments/` describe users' environments, not
permission to modify this workspace's infrastructure. Orbit owns fetching and
publishing; agents do not fetch or push.

## One quality workflow

```bash
composer fix    # apply configured safe Rector rules, Pint, and VitePlus fixes
composer check  # read-only source validation; required before handoff
```

Review the fix diff: automated refactoring is not a substitute for review. The
check fails on Pint formatting drift, Rector's pending refactors, PHPStan/Larastan
level 9 findings, or VitePlus format/lint/type findings. It then runs **all** Pest
Unit, Feature, and Architecture tests with `--no-tia`, followed by the existing
browser gate. Neither formatting nor refactoring silently changes source in
`check`; tool caches and generated build assets are expected. Rector caches are
workspace-local, not shared `/tmp` caches.

`composer test` retains git-aware TIA for fast local iterations; use
`composer test:full` for an uncached full suite. Architecture tests prohibit
application dependencies on test/static-analysis/refactoring tools and debug
calls, and enforce controller naming. Feature tests check Inertia responses but
deliberately disable Vite/SSR; they do **not** prove SSR or browser behavior.

`composer test:browser` builds both client and SSR assets (`bun run build`), starts
an owned SSR process, requires SSR errors to throw rather than falling back to
CSR, runs Pest Browser workflows (homepage and appearance), and stops only its
owned process even on failure. Browser tests use Pest's disposable loopback app,
not a managed Orbit URL. Do not bypass the runner or kill an unrelated SSR process
to free its port. For user-visible UI changes also verify the allocated managed
app in a real browser, including console errors, and record that evidence.

## Test isolation and credentials

`phpunit.xml` force-overrides inherited host settings: SQLite `:memory:`, array
cache/session/mail, synchronous queues, null broadcasting, and a local filesystem.
External service credentials are empty. `.env.testing` contains only a public,
**test-only** encryption key, not a production secret. The test bootstrap selects
`bootstrap/cache/config.testing.php` instead of the application's cached config
and refuses to boot if that test cache exists. Remove only that disposable test
cache if necessary; do not clear a managed application's configuration to run tests.

`tests/Feature/TestEnvironmentTest.php` proves both the effective configuration
and connection-level database isolation, including in a child Pest process with
fake production-like inherited settings. Use factories/`RefreshDatabase` for tests
that need schema-backed data; never point tests at a provisioned live/shared DB.
Keep `.env` files and credentials uncommitted (the committed `.env.example` and
`.env.testing` are deliberately non-secret fixtures).
