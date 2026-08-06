# Craft Starterkit React

Laravel 13 + Launch Laravel + React 19 + Inertia v3 + VitePlus + Tailwind CSS v4.2 + shadcn

## Stack

- **Backend**: Laravel 13, Launch Laravel, PHP 8.4+
- **Frontend**: React 19, TypeScript 5.9
- **SPA bridge**: Inertia.js v3 with SSR
- **Styling**: Tailwind CSS v4.2 with `tw-animate-css` and `shadcn/tailwind.css`
- **Components**: shadcn base-nova components backed by Base UI (`@base-ui/react`)
- **Icons**: Lucide React
- **Toolchain**: VitePlus (Vite 8 + Oxc linting/formatting)
- **Vite config**: `defineLaunchConfig()` from `@hardimpactdev/launch-ui/vite`

Launch Laravel is expected as a sibling checkout at `../launch-laravel`. Use `composer link ../launch-laravel` after `composer install` so local package changes are used while developing the starter kit.

## Agent Skills

Use the stack-specific skills whenever their domain is involved:

- Laravel PHP: `laravel-best-practices`
- Pest unit, feature, or browser tests: `pest-testing`
- Waymaker controller routes: `waymaker-development`
- Wayfinder frontend route functions: `wayfinder-development`
- React and Inertia: `vercel-react-best-practices` and
  `inertia-react-development` together
- shadcn/Base UI components: `shadcn`
- Tailwind styling: `tailwindcss-development`
- VitePlus commands or configuration: `vite-plus`

When React and Inertia guidance overlap:

- Inertia owns page data, navigation, forms, validation errors, deferred and
  merged props, polling, prefetching, history state, and SSR. Prefer its APIs
  and Wayfinder-generated actions over client fetch effects or hardcoded URLs.
- Apply the generic React skill's portable rendering, effects, bundle, and
  browser-performance guidance. Ignore its Next.js, React Server Component,
  Server Action, `React.cache`, `after()`, and SWR-specific rules.
- Do not add dependencies such as SWR or `better-all` solely to follow a generic
  example. Use Laravel, Inertia, React, and VitePlus-native mechanisms first.
- SSR is enabled. Keep server and browser renders deterministic, keep browser
  APIs out of server render paths, and preserve the starter kit's
  `createInertiaApp()` and VitePlus SSR setup.
- Use version-specific documentation before applying framework APIs.

## Vite Configuration

`vite.config.ts` uses `defineLaunchConfig()` which bundles all plugins:

```ts
import { defineLaunchConfig } from "@hardimpactdev/launch-ui/vite";
export default await defineLaunchConfig();
```

This configures: laravel-vite-plugin, @inertiajs/vite (with SSR), @vitejs/plugin-react, @tailwindcss/vite, @laravel/vite-plugin-wayfinder, and artisan runners for waymaker + typescript:transform.

### Options

```ts
export default await defineLaunchConfig({
    i18n: true, // Enable i18n (see below)
    i18n: { locale: "nl", fallbackLocale: "en" }, // With options
    react: { babel: { plugins: ["..."] } }, // Pass-through to @vitejs/plugin-react
    wayfinder: { formVariants: true }, // Pass-through to wayfinder
    inertia: false, // Disable @inertiajs/vite
    plugins: [myPlugin()], // Additional Vite plugins
    lint: { options: { typeAware: true } }, // VitePlus lint config
});
```

## Internationalization (i18n)

Enable in `vite.config.ts`:

```ts
export default await defineLaunchConfig({ i18n: true });
```

This auto-loads translation files from `lang/*.json` and injects `initI18n()` into the app entry point. No providers or wrappers needed.

### Translation files

```
lang/
  en.json     # {"Hello :name": "Hello :name", "Home": "Home"}
  nl.json     # {"Hello :name": "Hallo :name", "Home": "Thuis"}
```

### Usage in components

Use `__()` — a plain function import matching Laravel's Blade `__()` helper:

```tsx
import { __ } from "@hardimpactdev/launch-ui/i18n";

function MyComponent() {
    return <p>{__("Hello :name", { name: "Nick" })}</p>;
}
```

Supports `:placeholder`, `:Placeholder` (ucfirst), and `:PLACEHOLDER` (uppercase) replacements.

### Language switching

```tsx
import { __, useLocale, setLocale } from "@hardimpactdev/launch-ui/i18n";

function LanguageSwitcher() {
    const locale = useLocale(); // reactive — triggers re-render on change
    return (
        <div>
            <button onClick={() => setLocale("en")}>EN</button>
            <button onClick={() => setLocale("nl")}>NL</button>
            <p>{__("Home")}</p>
        </div>
    );
}
```

`setLocale()` hot-swaps all translated strings instantly, persists to localStorage and cookie.

## Routing

- **Waymaker** (`hardimpactdev/waymaker`): attribute-based PHP routing. Controllers use `#[Get]`, `#[Post]`, etc. attributes instead of route files. Routes are registered via `Waymaker::routes()` in `routes/web.php`.
- **Wayfinder** (`laravel/wayfinder` + `@laravel/vite-plugin-wayfinder`): auto-generates TypeScript route helpers at build time. Import from `@/actions/` for type-safe URLs.

## Component Registry

`components.json` configured with:

- Style: `base-nova` (shadcn components backed by Base UI primitives, not Radix)
- Icon library: `lucide`
- `@launch` registry for layout components

```bash
npx shadcn add button dialog         # shadcn components backed by Base UI primitives
npx shadcn add @launch/app-sidebar-layout  # Layout from craft registry
```

## Key Directories

```
app/
  Http/Controllers/       # Waymaker-attributed controllers
  Http/Middleware/         # HandleInertiaRequests, CSP middleware
  Support/Csp/            # CSP presets (Basic, Development)
resources/
  css/
    app.css               # Tailwind entrypoint
    theme.css             # Design tokens (oklch colors, radii, fonts)
  js/
    app.tsx               # Inertia app entrypoint (minimal)
    actions/              # Wayfinder-generated action helpers
    components/           # App components
    components/ui/        # shadcn UI components
    hooks/                # React hooks
    lib/                  # Utilities (cn, types)
    pages/                # Inertia page components
    routes/               # Wayfinder-generated route helpers
  views/
    app.blade.php         # Root Blade template (Inertia v3 syntax)
lang/                     # Translation JSON files (when i18n enabled)
```

## Development

```bash
composer dev              # Starts server, queue, logs, and vite concurrently
vp dev                    # VitePlus dev server only
vp build                  # Production build
vp check                  # Lint + format (Oxc)
vp check --fix            # Auto-fix
composer test             # Pest tests
composer lint             # Pint (PHP formatting)
composer analyse          # PHPStan level 9
composer rector           # Rector code upgrades
composer check            # test + analyse + vp check
composer fix              # rector + lint + vp check --fix
```

## Code Quality

- **PHP**: Pest v4, PHPStan level 9, Pint, Rector
- **Browser tests**: Pest Browser for user-visible UI workflows in `tests/Browser/`
- **JS/TS**: VitePlus (Oxc linting + formatting)
- **Git hooks**: Git config-based hooks — pre-commit (lint + check), pre-push (test + analyse)
- **CSP**: Spatie laravel-csp with Basic + Development presets

## Feature Completion Gates

Every feature must include automated test coverage before it is reported as done.

- Backend/domain behavior requires focused PHP feature or unit tests.
- Inertia page responses require Inertia assertions for the expected component and props.
- Any user-visible UI workflow requires a Pest Browser test in `tests/Browser/`.
- Any UI-affecting change must also be manually verified in the Codex in-app browser with `agent-browser`, including checking browser console errors.
- Completion reports must list the exact tests and browser validation that passed.
- If browser testing or browser validation is skipped, the report must explicitly state why the change has no user-visible UI surface.

## Conventions

- CSS design tokens live in `resources/css/theme.css`, not scattered across component files
- Generated shadcn UI components use `@base-ui/react` primitives. Do not install `@radix-ui/*` packages
- Use Wayfinder-generated imports (`@/actions/...`) for route URLs, never hardcoded strings
- Use `__()` for user-facing strings when i18n is enabled
- Page components live in `resources/js/pages/` and are resolved by Inertia automatically
- `VITE_APP_URL` in `.env` must match the Orbit domain for HTTPS dev server to work
