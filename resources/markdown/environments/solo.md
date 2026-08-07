# Running the project with Solo

Addendum to [the main setup guide](https://launch.nckrtl.com/create.md). Read that first.

Solo is **not** an alternative to Herd or Orbit. Those decide how the app is _served_; Solo
decides how the long-running dev processes are _run and observed_. Combine them freely —
Orbit + Solo, Herd + Solo, or Solo on its own.

Use this page if you are an agent with the Solo MCP server available. It replaces the
backgrounded `php artisan serve` and the `composer dev` guidance in the main guide with
managed processes you can start, inspect, and stop through tool calls.

## 1. Confirm Solo is available

This is not a shell check. Look at your own tool list for tools named `mcp__solo__*` — for
example `mcp__solo__list_projects` or `mcp__solo__start_all_commands`. If they are there, Solo
is available to you and this page applies.

If you have no Solo tools, this page does not apply, even when the `solo` binary exists on
`PATH`. A CLI you cannot drive does not help you. Go back to the main guide.

## 2. Know what the kit already gives you

The starter kit ships a `solo.yml` in the project root defining four processes:

| Process  | Command                                          | Auto-start |
| -------- | ------------------------------------------------ | ---------- |
| `Vite`   | `bun run dev`                                    | yes        |
| `Queue`  | `php artisan queue:listen --tries=1 --timeout=0` | yes        |
| `Logs`   | `php artisan pail --timeout=0`                   | yes        |
| `Server` | `php artisan serve`                              | **no**     |

`Server` is deliberately off. Herd and Orbit already serve the app, and starting it would put
a second PHP server on port 8000. Only enable it when nothing else is serving the project.

You do not need to write this file — it is already there. Editing it is how you change the
process set, since `solo.yml` is the source of truth for YAML-backed commands.

## 3. Register the project

```
mcp__solo__list_projects
```

If the project is already listed, select it with `mcp__solo__select_project`. If not, create
it with `mcp__solo__create_project` pointed at the project root. Solo parses `solo.yml` on
load and syncs the processes into its local state.

> **Trust gate:** YAML-defined commands cannot start until they are trusted in the Solo UI,
> and changing `command`, `working_dir`, `auto_start`, `auto_restart`, `restart_when_changed`,
> or `env` can require re-review. If a process refuses to start, this is almost always why —
> tell the user to approve it rather than working around it.

## 4. Start the processes

```
mcp__solo__start_all_commands
```

That starts everything with `auto_start: true`. To bring up just one, use
`mcp__solo__start_process`.

If nothing else is serving the app — no Herd, no Orbit — also start `Server`:

```
mcp__solo__start_process   (name: "Server")
```

## 5. Wait for readiness instead of scraping logs

```
mcp__solo__wait_for_bound_port
```

This blocks until a process exposes a listening port and returns the URL. It returns
`ready=false` with `timed_out=true` if nothing came up, which is a real signal — do not treat
a timeout as success.

`mcp__solo__services_list` shows every detected service with its readiness state and URL, and
`mcp__solo__get_process_ports` narrows that to one process.

## 6. Verify and debug

```
mcp__solo__get_process_status     — is it running, did it exit
mcp__solo__get_process_output     — recent stdout/stderr
mcp__solo__search_output          — find an error without pulling the whole log
```

Read `Vite` output if the page loads unstyled; the usual cause is `VITE_APP_URL` not matching
`APP_URL`, and Vite says so on startup.

## What not to do when you have Solo

- Do not run `composer dev`. It runs the same four processes under `concurrently` and never
  exits, so it blocks your turn and gives you no way to inspect them afterwards.
- Do not background processes with `&` and later `pkill` them. Solo already tracks lifetime,
  output, and ports; shell backgrounding throws all of that away.
- Do not start `Server` without checking for Herd or Orbit first.
