# React Starterkit

Laravel 13 + React 19 + Inertia v3 + Vite 8 + Tailwind CSS v4.2 + Base UI

## Stack

- **Backend**: Laravel 13, PHP 8.3+
- **Frontend**: React 19, TypeScript 5.9
- **SPA bridge**: Inertia.js v3 (beta)
- **Styling**: Tailwind CSS v4.2 with `tw-animate-css`
- **UI primitives**: Base UI (`@base-ui/react`) via shadcn base-nova style
- **Icons**: Lucide React
- **Build**: Vite 8 with `@tailwindcss/vite`, `@vitejs/plugin-react`, `@laravel/vite-plugin-wayfinder`

## Routing

- **Waymaker** (`hardimpactdev/waymaker`): attribute-based PHP routing. Controllers use `#[Get]`, `#[Post]`, etc. attributes instead of route files. Routes are registered via `Waymaker::routes()` in `routes/web.php`.
- **Wayfinder** (`laravel/wayfinder` + `@laravel/vite-plugin-wayfinder`): auto-generates TypeScript route helpers and action helpers at build time into `resources/js/routes/` and `resources/js/actions/`. Import from these to get type-safe URLs.

## Component Registry

`components.json` is configured with:
- Style: `base-nova` (Base UI primitives, not Radix)
- Icon library: `lucide`
- `@craft` registry at `http://localhost:4100/r/{name}.json` for layout components

Use `npx shadcn add <component>` for base-nova UI components and `npx shadcn add @craft/<name>` for layout components.

## Key Directories

```
app/
  Http/Controllers/       # Waymaker-attributed controllers
  Http/Middleware/         # HandleInertiaRequests shares app/location props
  Models/                 # Eloquent models
  Providers/              # AppServiceProvider
resources/
  css/
    app.css               # Tailwind entrypoint, imports theme.css
    theme.css             # Design tokens (colors, radii, fonts)
  js/
    app.tsx               # Inertia app entrypoint
    actions/              # Wayfinder-generated action helpers (auto-generated)
    components/           # App components (layout, nav, etc.)
    components/ui/        # shadcn base-nova UI primitives
    hooks/                # React hooks
    lib/types.ts          # Shared TypeScript types (BreadcrumbItem, NavItem, User, etc.)
    lib/utils.ts          # cn() utility
    pages/                # Inertia page components
    routes/               # Wayfinder-generated route helpers (auto-generated)
    types/index.d.ts      # Ambient type declarations (SharedProps)
    wayfinder/            # Wayfinder runtime helpers (auto-generated)
  views/
    app.blade.php         # Root Blade template for Inertia
routes/
  web.php                 # Waymaker::routes() loader
  waymaker.php            # Explicit route overrides (if needed)
```

## Development

```bash
composer dev              # Starts server, queue, logs, and vite concurrently
npm run dev               # Vite dev server only
npm run build             # Production build
composer test             # Run tests
```

## Conventions

- CSS design tokens live in `resources/css/theme.css`, not scattered across component files.
- The dark mode variant is `@custom-variant dark (&:is(.dark *))` defined once in `app.css`.
- UI primitives come from `@base-ui/react`. Do not install `@radix-ui/*` packages.
- Use Wayfinder-generated imports for route URLs, never hardcoded strings.
- Page components live in `resources/js/pages/` and are resolved by Inertia automatically.
