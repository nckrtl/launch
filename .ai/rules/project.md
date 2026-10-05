# Project knowledge

This repository owns Launch's landing page, brand assets and documentation published
on `launch.nckrtl.com`. The reusable starter kit is a separate repository. The application
depends on `nckrtl/launch-laravel` from Packagist; do not assume a monorepo or an existing
`../../packages/launch-laravel` checkout. For explicitly authorized local package work,
`composer link <checkout>` after install leaves composer.json/lock untouched;
`composer unlink <checkout>` restores the published package. Confirm an allocated
checkout first. This repository's README has no "Working on the kit itself" section.

## Directory map

- `app/Http/Controllers`: route controllers; `app/Http/Middleware`: Inertia and CSP.
- `app/Support/Csp`: Spatie CSP Basic + Development presets; preserve them.
- `resources/css/app.css`: Tailwind entrypoint; `theme.css`: design tokens / fonts / radii.
- `resources/js/app.tsx`: minimal Inertia entrypoint.
- `resources/js/actions`, `routes`: generated Wayfinder helpers.
- `resources/js/components` (`ui` for shadcn), `hooks`, `lib`, `pages`: frontend code.
- `resources/views/app.blade.php`: root Blade template using Inertia v3 syntax.
- `resources/markdown`: agent-facing **published product** docs served by
  AgentDocsController at `/llms.txt`, `/create.md`, `/conventions.md`, `/herd.md`,
  `/orbit.md`, `/solo.md`. Edit Markdown directly; no rebuild is required.
- `lang/*.json`: optional translations when i18n is enabled.

Published environment guides describe how Launch users run their applications;
this task's brief and README govern this workspace's infrastructure boundaries.
