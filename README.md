# Craft Starterkit React

Laravel 13 + Craft Laravel + React 19 + Inertia v3 + VitePlus + Tailwind CSS v4.2 + shadcn

## Getting started

Run `composer setup` for an interactive guided setup, or do it manually:

```bash
cp .env.example .env
# Edit .env: set APP_NAME, APP_URL, VITE_APP_URL (e.g. https://my-app.test)
composer install
composer link ../craft-laravel
vp install
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
git config --local --replace-all hook.craft-lint.event pre-commit
git config --local --replace-all hook.craft-lint.command "composer lint"
git config --local --replace-all hook.craft-frontend.event pre-commit
git config --local --replace-all hook.craft-frontend.command "vp check --fix"
git config --local --replace-all hook.craft-test.event pre-push
git config --local --replace-all hook.craft-test.command "composer test"
git config --local --replace-all hook.craft-analyse.event pre-push
git config --local --replace-all hook.craft-analyse.command "composer analyse"
orbit link                    # or: herd link
vp build
```

## Development

```bash
composer dev    # Starts server, queue, logs, and vite concurrently
```

## Stack

- **Backend**: Laravel 13, Craft Laravel, PHP 8.4+
- **Frontend**: React 19, TypeScript 5.9
- **SPA bridge**: Inertia.js v3 with SSR
- **Styling**: Tailwind CSS v4.2 with shadcn base-nova style
- **Components**: shadcn base-nova components backed by Base UI (`@base-ui/react`)
- **Icons**: Lucide React
- **Toolchain**: VitePlus (Vite 8 + Oxc linting/formatting)
- **Routing**: Waymaker (PHP attributes) + Wayfinder (TS route helpers)
- **Component registry**: `@craft` shadcn registry from [craft-ui-react](https://github.com/hardimpactdev/craft-ui-react)

## AI-Assisted Development

This project uses hierarchical `CLAUDE.md` files for AI coding assistants. See the root `CLAUDE.md` for the complete structure and development guidelines.

## License

[MIT](https://opensource.org/licenses/MIT)
