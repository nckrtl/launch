# Launch Starter Kit

Laravel 13 + Launch Laravel + React 19 + Inertia v3 + VitePlus + Tailwind CSS v4.2 + shadcn

## Getting started

```bash
composer create-project nckrtl/launch-starter-kit my-app
cd my-app
composer setup
```

`composer setup` is an interactive guided setup. AI agents should follow
[`/create.md`](resources/markdown/create.md) instead, which is the same flow without prompts.

To do it manually:

```bash
cp .env.example .env
# Edit .env: set APP_NAME, APP_URL, VITE_APP_URL (e.g. https://my-app.test)
composer install
bun install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
git config --local --replace-all hook.launch-lint.event pre-commit
git config --local --replace-all hook.launch-lint.command "composer lint"
git config --local --replace-all hook.launch-frontend.event pre-commit
git config --local --replace-all hook.launch-frontend.command "vp check --fix"
git config --local --replace-all hook.launch-test.event pre-push
git config --local --replace-all hook.launch-test.command "sh -c 'composer test' --"
git config --local --replace-all hook.launch-analyse.event pre-push
git config --local --replace-all hook.launch-analyse.command "sh -c 'composer analyse' --"
orbit link                    # or: herd link
bun run build
```

## Working on the kit itself

The starter kit depends on `nckrtl/launch-laravel` from Packagist. To develop against a local
checkout, link it rather than adding a path repository to `composer.json` — path repositories
resolve only on your machine and break `composer create-project` for everyone else:

```bash
composer link ../../packages/launch-laravel
composer link ../../packages/laravel-toolbar-agentation   # optional toolbar addon
php artisan package:discover
```

`composer link` keeps `composer.json` and `composer.lock` untouched. Run
`composer unlink <path>` to go back to the published package.

## Agent-facing docs

`/llms.txt` and `/create.md` are served from `resources/markdown/` by `AgentDocsController`.
Edit the Markdown files; no rebuild needed.

## Development

```bash
composer dev    # Starts server, queue, logs, and vite concurrently
```

## Stack

- **Backend**: Laravel 13, Launch Laravel, PHP 8.4+
- **Frontend**: React 19, TypeScript 5.9
- **SPA bridge**: Inertia.js v3 with SSR
- **Styling**: Tailwind CSS v4.2 with shadcn base-nova style
- **Components**: shadcn base-nova components backed by Base UI (`@base-ui/react`)
- **Icons**: Lucide React
- **Toolchain**: VitePlus (Vite 8 + Oxc linting/formatting)
- **Routing**: Waymaker (PHP attributes) + Wayfinder (TS route helpers)
- **Component registry**: `@launch` shadcn registry from [launch-ui](https://github.com/nckrtl/launch-ui)

## AI-Assisted Development

This project uses `AGENTS.md` for AI coding assistants. It includes feature completion gates requiring Pest/Pest Browser coverage and `agent-browser` validation for UI-affecting work.

## License

[MIT](https://opensource.org/licenses/MIT)
