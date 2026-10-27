# Launch

The marketing and documentation website for Launch.

This application is built from [`nckrtl/launch-starter-kit`](https://github.com/nckrtl/launch-starter-kit). The reusable starter remains in that repository; this repository owns the Launch landing page, brand assets, and the guides published on `launch.nckrtl.com`.

## Development

```bash
composer install
bun install
bunx playwright install chromium   # once, for browser tests
composer check                     # Pint, Rector, PHPStan, vp check, Pest, browser tests
composer fix                       # Rector, Pint, vp check --fix
composer audit:dependencies        # Composer and Bun advisory audits
```

Dependency updates follow the [dependency policy](docs/dependency-policy.md). Agent instructions live in `AGENTS.md` (`CLAUDE.md` links to it); see [docs/agents.md](docs/agents.md) for MCP setup.

The Beast development instance is managed by Orbit at `https://launch.test`.

## Orbit SSR

The built SSR server reads `ORBIT_SSR_PORT` at startup. Give each instance its
own port and set `INERTIA_SSR_URL=http://127.0.0.1:<port>` through Orbit's managed
environment. Check Orbit's assigned ports and the node's TCP listeners before
choosing a port for an older instance with no assignment. The default remains
13719 for the production deployment, where ports 13714–13718 are already used.

Build with `bun run build`, then run `node bootstrap/ssr/app.js` as an
instance-owned Orbit process. Do not use `inertia:start-ssr` to probe an endpoint:
it first sends a shutdown request to the configured SSR server.

The default instance serves built assets with Vite stopped, so Laravel uses
`INERTIA_SSR_URL` for SSR. A Vite development server switches Laravel to its hot
SSR endpoint instead. Verify the initial HTML after changing either mode.

Run `node --test tests/ssr-runtime.test.mjs` after building. It starts two copies
of the same bundle on different ports and checks their rendered Launch HTML.
Verify the live homepage's initial HTML with JavaScript disabled too.
