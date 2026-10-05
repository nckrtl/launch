# Shared agent context

`AGENTS.md` is the lean entrypoint for Pi/Codex and `CLAUDE.md` is its existing
symlink for Claude Code. `.agents/skills` is the single maintained skill tree;
`.claude/skills` links to it. Scoped rules are indexed in `rules/index.md`.

## Boost ownership and refresh

`boost.json` selects Claude Code and Codex, guidelines for the installed framework
and useful directly required packages, and no new skills/cloud integrations.
Inertia and Wayfinder are auto-detected first-party integrations; `packages` selects
Launch's third-party guidance (Boost normalizes first-party entries out on refresh). Existing skills contain project adaptations and are
maintained separately: do not regenerate or remove them on an assumed duplication.
Review remote skill content before executing any included scripts.

Boost 2.9 supports per-agent `guidelines_path` overrides. `config/boost.php` sends
both agents to `.ai/package-guidelines.md`, keeping the generated managed block out
of AGENTS/CLAUDE. Agents explicitly read it for relevant APIs; it need not inflate
every task's startup context. Custom choices live in `rules/`, not in the generated
file. The application's commands, safety boundaries and scoped project rules take
precedence over generic package examples (notably npm commands, service startup,
and weaker testing advice). Use our `pest-testing` skill, not the upstream reference
to an uninstalled `testing-best-practices` skill. Launch's package guide describes
scaffolding consumers, not this website: its Waymaker reference is not permission
to undo this app's Laravel route files / Wayfinder. Do not run setup commands just
to maintain this application. Do not interpret runtime PHP 8.5 in generated guidance
as a change to the declared PHP 8.4+ requirement.

After Composer updates, `post-update-cmd` runs `scripts/update-agent-context.php`:

```bash
php artisan boost:update --no-discover --ignore-skills --no-interaction
```

The wrapper skips production (`COMPOSER_DEV_MODE=0`) or missing Boost. No prompt,
new package discovery, skill overwrite or MCP rewrite occurs. VitePlus excludes
Boost-owned `boost.json` and `.ai/package-guidelines.md` from formatting so a refresh
cannot create formatter drift; hand-maintained rules still run through `vp check`.
Inspect the diff and
commit the refreshed guidance alongside relevant dependency updates. To add newly
useful packages, review their vendor guidance and edit selections deliberately.
No Boost upgrade is required for this configuration.

## MCP compatibility

- **Codex:** tracked `.codex/config.toml` launches `php artisan boost:mcp` over stdio.
  Trust/enable the project config in the client and run from this workspace root.
- **Claude Code:** `.mcp.example.json` is the secret-free baseline. If `.mcp.json`
  does not exist, copy the example there. If it exists, merge ONLY the
  `laravel-boost` server entry into `mcpServers`; preserve Orbit or other local
  servers and their settings. Never overwrite the whole file.
- **Pi / Orbit:** the harness exposes version-aware `search_docs`. Use that instead
  of assuming Pi reads Claude's MCP file. Orbit's HTTP MCP search server is injected
  locally, not a project endpoint to commit. A client with generic MCP support can
  use the same `php` + `["artisan", "boost:mcp"]` stdio registration.

Boost's installer is intentionally not allowed to rewrite MCP settings
(`mcp: false`); registration is explicit and preserves environment-owned servers.
No credentials or environment-specific URLs are in the tracked configs. Restart
or reconnect the client after configuring it; verify `tools/list` includes
`search-docs` and `application-info`. The focused `AgentContextTest` exercises the
stdio initialization and tool discovery without needing a topology or web service.
Application/schema/browser-log inspection requiring a running task app must use
an allocated topology through the reviewer; tool discovery alone is not proof of
live browser integration.

## Preservation review map (approved gap plan G1–G3)

- Original stack, skills and completion gates: shortened into `AGENTS.md`.
- Original React/Inertia narrowing, SSR invariants, routes, tokens, Base UI,
  registry, Launch config options and i18n examples: `rules/frontend.md`.
- Original directory knowledge, CSP and package-link workflow: `rules/project.md`;
  corrected the nonexistent README section and assumed monorepo path.
- Original shared CLAUDE arrangement: retained; skills gain a shared Claude link.
- Stale `defineCraftConfig()` skill instruction: corrected to `defineLaunchConfig()`.
- Incorrect browser-command description: now describes owned build/SSR lifecycle.
- Environment addenda remain product documentation, not task authorization.

No original skill or unique project constraint was deleted. Long examples were
moved out of startup context, not replaced by generic Boost advice. Review this map
against the baseline `AGENTS.md` and the diff when confirming context preservation.
