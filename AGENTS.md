# Launch — agent instructions

Launch's marketing and documentation website (`launch.nckrtl.com`). It is built from `nckrtl/launch-starter-kit`, but it is not that repository: the reusable kit lives there. Stack: PHP 8.4+, Laravel 13 + Launch Laravel, React 19, TypeScript 7, Inertia v3 with SSR, VitePlus (Vite 8/Oxc), Tailwind v4, shadcn base-nova on Base UI, Lucide. Package managers: Composer and Bun. Keep both lockfiles.

## Read first

- `docs/agents.md` for MCP setup, Vite options, i18n, routing and file locations.
- Use the Boost `search-docs` MCP tool before you rely on an unfamiliar Laravel ecosystem API.

## Boundaries

- `resources/markdown/` is published product documentation (`/llms.txt`, `/create.md`, `/conventions.md`, `/herd.md`, `/orbit.md`, `/solo.md`). The environment guides tell Launch users how to run their apps. They do not authorize you to change this workspace's infrastructure.
- Never commit secrets, tokens, `.env` or machine-local paths.
- Herd and Orbit already serve the app. Do not run `composer dev` or `php artisan serve` there. `VITE_APP_URL` must match the HTTPS domain.
- `nckrtl/launch-laravel` comes from Packagist. To work on it locally, run `composer link <path-to-checkout>`; `composer unlink <path>` restores it. Never add path repositories.
- Dependency changes follow `docs/dependency-policy.md`.

## Skills (`.agents/skills`)

`laravel-best-practices` for PHP; `pest-testing` for tests; `wayfinder-development` for frontend routes; `vercel-react-best-practices` (directory `react-best-practices`) with `inertia-react-development` for React; `shadcn` for components; `tailwindcss-development` for styling; `vite-plus` for frontend tooling; `reviewing-pull-requests` for reviews.

Inertia owns data, navigation, forms, validation, polling, history and SSR. From the generic React skill, use only the portable performance advice, not Next.js, RSC, Server Actions, `React.cache`, `after()` or SWR. Do not add SWR or better-all to follow an example. Use Wayfinder `@/actions/` URLs, Base UI (no `@radix-ui/*`), and the tokens in `resources/css/theme.css`. Keep SSR deterministic: no browser APIs during server render.

## Commands

| Intent                      | Command                                                                 |
| --------------------------- | ----------------------------------------------------------------------- |
| Install                     | `composer install`, `bun install --frozen-lockfile`                     |
| Build client + SSR          | `bun run build` (`vp build` alone is client-only)                       |
| Unit + Feature tests        | `composer test -- <Pest args>` (git-aware, uses `--tia` after a commit) |
| Browser tests               | `composer test:browser` (builds first, owns its SSR process)            |
| Full quality gate           | `composer check` (Pint, Rector, PHPStan, `vp check`, Pest, browser)     |
| Fix formatting and upgrades | `composer fix`                                                          |
| Dependency audits           | `composer audit:dependencies`                                           |

Browser tests need Chromium once: `bunx playwright install chromium`. `composer test` does not run `tests/Browser`. Feature tests disable SSR and Vite on purpose (`tests/Pest.php`): do not re-enable them, and do not count Feature tests as SSR coverage. The SSR port is pinned to 13719 in `vite.config.ts`; read the comment there before you change it. Keep `defineLaunchConfig()` and `createInertiaApp()`.

`bun install` runs `vp config`, which installs the git hook dispatcher. `.vite-hooks/pre-commit` runs `vp staged`. Skip it once with `VP_GIT_HOOKS=0`. Do not set `hook.*` git config keys.

## Completion gates

- Backend changes need focused Pest tests. Inertia responses need assertions on component and props. User-visible workflows need a Pest Browser test.
- UI changes also need a manual check in a real browser, including the console.
- Report the exact tests and browser checks that passed. If you skip browser checks, say why the change has no UI surface.
- Run `composer check` before you hand off.
