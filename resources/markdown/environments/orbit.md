# Setting up on Orbit

Addendum to [the main setup guide](https://launch.nckrtl.com/create.md). Read that first.
This page replaces its environment and run steps; everything else there still applies.

Orbit serves the application through a FrankenPHP process managed by the gateway. **Do not run
`php artisan serve`, and do not run `composer dev`** — the app is already being served.

> There is no `orbit link` command. Running it prints the help listing and exits `0`, so it
> looks like it worked. Registering an existing directory is `orbit instance:register`.

## 1. Register the project directory

From the project root:

```bash
orbit instance:register my-app --path="$(pwd)" --root=public --php-version=8.5
```

The command is idempotent and does not clone — it adopts the path you are already in. The kit
requires PHP 8.4 or newer, so pass 8.4 or 8.5.

To create the app and clone it in one step instead of adopting an existing checkout, use
`orbit app:new` — but for the `composer create-project` flow in the main guide, the directory
already exists, so `instance:register` is the right command.

## 2. Read back the URL Orbit assigned

```bash
orbit app:show my-app
```

Look at the `Routes` line and the `URL` column. It will be something like
`https://my-app.<node>` — for example `https://launch.nmbp` on a node named `NMBP`. Some apps
route as `<app>.test` instead. Use exactly what the command prints; do not guess it.

## 3. Point .env at that URL

Both values must be the URL from step 2, matching exactly:

```dotenv
APP_NAME="My App"
APP_URL=https://my-app.nmbp
VITE_APP_URL=https://my-app.nmbp
```

A mismatch makes Vite serve assets from the wrong origin and the page loads unstyled. This is
the single most common Orbit setup failure with this kit.

## 4. Run only Vite

```bash
bun run dev
```

Orbit runs PHP for you. If you want the queue worker and log tailer as well, either start them
by hand or register them as Orbit processes with `orbit process:add`.

## 5. Verify

```bash
orbit doctor
```

That checks Orbit's health and reports drift. Then:

```bash
curl -sI https://my-app.nmbp | head -1
```

Expect `HTTP/2 200`. Open the URL — you should see the Launch homepage, styled.

Useful when something looks wrong:

- `orbit app:show my-app` — current routes, PHP version, and processes
- `orbit app:log my-app` — tail the Laravel log for the resolved app
- `orbit php:use` — change the PHP runtime for the instance
- `orbit instance:register …` again — idempotent, re-converges runtime and route intent
