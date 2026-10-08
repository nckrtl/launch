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
