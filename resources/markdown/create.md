# Create a new project with the Launch starter kit

You are most likely an AI coding agent that was told to start a new project based on
`launch.nckrtl.com`. This document contains everything you need. Follow it top to bottom.

Do not run `composer setup` — that script is interactive and expects a human at a TTY.
The steps below are the equivalent non-interactive path.

## What you are building on

| Layer            | Choice                                                         |
| ---------------- | -------------------------------------------------------------- |
| Backend          | Laravel 13, PHP 8.4+, `nckrtl/launch-laravel`                  |
| Frontend         | React 19, TypeScript                                           |
| SPA bridge       | Inertia.js v3, SSR enabled                                     |
| Styling          | Tailwind CSS v4 with `tw-animate-css`                          |
| Components       | shadcn `base-nova` style, backed by Base UI (`@base-ui/react`) |
| Icons            | Lucide React                                                   |
| Toolchain        | VitePlus (Vite 8 + Oxc lint/format), Bun as package manager    |
| PHP routing      | Waymaker — PHP attributes, no route files                      |
| Frontend routing | Wayfinder — generated TypeScript route helpers                 |
| Database         | SQLite by default                                              |

## Prerequisites

Verify these before starting. If one is missing, stop and tell the user rather than
guessing at a workaround.

- PHP 8.4 or newer (`php -v`)
- Composer 2 (`composer -V`)
- Bun 1.3 or newer (`bun -v`)
- Optionally Orbit or Herd for local HTTPS domains

## 1. Create the project

```bash
composer create-project nckrtl/launch-starter-kit my-app
cd my-app
```

## 2. Configure the environment

```bash
cp .env.example .env
```

Then set these three values in `.env`. `VITE_APP_URL` must match `APP_URL` exactly, or the
Vite dev server will serve assets from the wrong origin and the page will load unstyled.

```dotenv
APP_NAME="My App"
APP_URL=http://localhost:8000
VITE_APP_URL=http://localhost:8000
```

If the user has Orbit or Herd, use the secured domain for both URLs instead, for example
`https://my-app.test`.

## 3. Install dependencies

```bash
composer install
bun install
```

## 4. Initialise the application

```bash
php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
```

## 5. Build assets

```bash
bun run build
```

## 6. Link the site (optional)

Only if Orbit or Herd is installed:

```bash
orbit link      # or: herd link
```

## 7. Install git hooks (optional but recommended)

The kit uses git config-based hooks rather than a hook manager:

```bash
git config --local --replace-all hook.launch-lint.event pre-commit
git config --local --replace-all hook.launch-lint.command "composer lint"
git config --local --replace-all hook.launch-frontend.event pre-commit
git config --local --replace-all hook.launch-frontend.command "vp check --fix"
git config --local --replace-all hook.launch-test.event pre-push
git config --local --replace-all hook.launch-test.command "sh -c 'composer test' --"
git config --local --replace-all hook.launch-analyse.event pre-push
git config --local --replace-all hook.launch-analyse.command "sh -c 'composer analyse' --"
```

## 8. Verify the install

```bash
composer test
```

Then start the dev environment. This runs the PHP server, queue worker, log tailer, and
Vite together, and does not exit:

```bash
composer dev
```

Load `APP_URL` in a browser. You should see the Launch homepage. If it renders unstyled,
`VITE_APP_URL` does not match `APP_URL`.

---

# Conventions to follow when building

Once the project runs, these are the rules that keep it consistent. They are also written to
`AGENTS.md` in the project root, which is the authoritative copy.

## Routing: Waymaker, not route files

`routes/web.php` contains only `Waymaker::routes()`. Routes are declared as PHP attributes on
controller methods:

```php
namespace App\Http\Controllers;

use NckRtl\Waymaker\Get;
use Inertia\Response;

class ProjectController extends Controller
{
    #[Get(uri: '/projects/{project}')]
    public function show(Project $project): Response
    {
        return inertia('Projects/Show', [
            'project' => $project,
        ]);
    }
}
```

Do not add routes to `routes/web.php`.

## URLs in the frontend: Wayfinder, not strings

Wayfinder generates typed helpers at build time. Import them; never hardcode a URL.

```tsx
import { show } from "@/actions/App/Http/Controllers/ProjectController";

<Link href={show(project.id)}>Open</Link>;
```

## Components: shadcn base-nova on Base UI

```bash
bunx shadcn add button dialog          # shadcn components on Base UI primitives
bunx shadcn add @launch/app-sidebar-layout  # layouts from the @launch registry
```

Never install `@radix-ui/*` packages. This kit is configured for Base UI, and mixing the two
gives you two incompatible primitive layers.

## Styling

Design tokens live in `resources/css/theme.css` as oklch values. Put new tokens there rather
than scattering literal colors through components.

## Pages

Page components go in `resources/js/pages/` and are resolved by Inertia automatically. SSR is
on, so keep server and browser renders deterministic and keep browser-only APIs
(`window`, `localStorage`, `matchMedia`) out of render paths — read them in effects.

## Internationalisation

Off by default. Enable in `vite.config.ts`:

```ts
export default await defineLaunchConfig({ i18n: true });
```

Then add `lang/en.json`, and use the `__()` helper in components:

```tsx
import { __ } from "@nckrtl/launch-ui/i18n";

<p>{__("Hello :name", { name: "Nick" })}</p>;
```

## Commands

| Command            | Does                                   |
| ------------------ | -------------------------------------- |
| `composer dev`     | Server, queue, logs, and Vite together |
| `composer test`    | Pest test suite                        |
| `composer analyse` | PHPStan at level 9                     |
| `composer lint`    | Pint (PHP formatting)                  |
| `composer check`   | test + analyse + frontend lint         |
| `composer fix`     | Rector + Pint + frontend autofix       |
| `bun run dev`      | Vite dev server only                   |
| `bun run build`    | Production build                       |

## Quality gates

The kit is configured for PHPStan level 9 and Pest 5. Before reporting a feature as done:

- Backend behaviour needs a Pest feature or unit test.
- Inertia responses need assertions on the component name and props.
- User-visible workflows need a Pest Browser test in `tests/Browser/`.
- `composer check` must pass.
