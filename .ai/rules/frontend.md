# Frontend rules

Scope: `resources/js/**`, `resources/css/**`, `resources/views/**`, `vite.config.ts`.

## React, Inertia and SSR

Inertia owns page data, navigation, forms, validation errors, deferred/merged props,
polling, prefetching and history. Prefer its APIs and Wayfinder-generated actions
instead of client-fetch effects or hardcoded URLs. Apply portable React rendering,
effects, bundle and browser-performance advice, not Next.js, React Server Components,
Server Actions, `React.cache`, `after()` or SWR-specific rules. Do not add dependencies
like SWR / `better-all` solely to implement a generic example.

SSR is enabled. Keep server and browser renders deterministic, browser APIs out of
server-render paths, and preserve the starter's `createInertiaApp()` and VitePlus SSR
setup. SSR port is currently pinned to 13719 in `vite.config.ts`, without an env override:
read its comment before changing it. Feature tests deliberately set
`inertia.ssr.enabled=false` and use `withoutVite()` in `tests/Pest.php`; they prove
nothing about SSR. Do not re-enable SSR to "fix" them. Use `composer test:browser` for
the owned build/SSR/browser lifecycle.

## Routes and components

- Declare web routes in `routes/web.php` using `Route::get()`, `Route::post()`, etc.
  Give routes dotted names, e.g. `projects.show`.
- Wayfinder generates TypeScript route helpers during builds. Prefer imports from
  `@/actions/` for controller calls (or generated `@/routes` for named routes), never
  hardcoded backend URLs. Do not hand-edit generated helpers.
- Inertia pages live in `resources/js/pages/` and resolve automatically.
- Design tokens belong in `resources/css/theme.css`, not scattered in components.
- Tailwind v4 uses `tw-animate-css` and `shadcn/tailwind.css`.
- `components.json`: base-nova, Base UI (`@base-ui/react`), Lucide, `@launch` registry.
  Do not install `@radix-ui/*` packages for generated shadcn components.

```bash
bunx shadcn add button dialog
bunx shadcn add @launch/app-sidebar-layout
```

## Launch Vite integration

Preserve `defineLaunchConfig()` from `@nckrtl/launch-ui/vite`. It integrates
laravel-vite-plugin, @inertiajs/vite (SSR), @vitejs/plugin-react, @tailwindcss/vite,
@laravel/vite-plugin-wayfinder and the `typescript:transform` Artisan runner.
Check installed package guidance before changing options. Supported examples
for the installed toolchain:

```ts
import { defineLaunchConfig } from "@nckrtl/launch-ui/vite";
export default await defineLaunchConfig({
    i18n: { locale: "nl", fallbackLocale: "en" }, // or true
    wayfinder: { formVariants: true },
    // inertia: false, // deliberately disables Inertia integration
    // plugins: [myPlugin()],
    lint: { options: { typeAware: true } },
});
```

The former `react.babel.plugins` example is obsolete: installed
`@vitejs/plugin-react` 6.1.1 has no Babel option. Launch forwards React options but
cannot make unsupported options work; they are ignored. Do not add Babel or other
dependencies merely to preserve that example.

`VITE_APP_URL` must match the allocated Orbit domain for HTTPS Vite development.
Do not copy the live website's domain into task-local settings.

## Optional internationalization

i18n is not currently enabled in `vite.config.ts`. When enabled with `i18n: true`
or locale options, Launch auto-loads `lang/*.json` and injects `initI18n()` into the
app entrypoint. No provider/wrapper is needed. Example files:

- `lang/en.json`: `{"Hello :name": "Hello :name", "Home": "Home"}`
- `lang/nl.json`: `{"Hello :name": "Hallo :name", "Home": "Thuis"}`

Use `__()` for user-facing strings when enabled:

```tsx
import { __, useLocale, setLocale } from "@nckrtl/launch-ui/i18n";

function LanguageSwitcher() {
    const locale = useLocale();
    return (
        <div lang={locale}>
            <button onClick={() => setLocale("en")}>EN</button>
            <button onClick={() => setLocale("nl")}>NL</button>
            <p>{__("Hello :name", { name: "Nick" })}</p>
        </div>
    );
}
```

Placeholders support `:placeholder`, `:Placeholder` (ucfirst), `:PLACEHOLDER`
(uppercase). `setLocale()` swaps translations reactively and persists to
localStorage and cookie.
