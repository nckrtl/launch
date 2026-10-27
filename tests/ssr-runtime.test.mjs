import assert from "node:assert/strict";
import { spawn } from "node:child_process";
import { once } from "node:events";
import { createServer } from "node:net";
import { setTimeout } from "node:timers/promises";
import test from "node:test";
import { loadConfigFromFile } from "vite-plus";

await test("development keeps one Inertia plugin and PHP prop reloads", async () => {
    const loaded = await loadConfigFromFile(
        { command: "serve", mode: "development" },
        "vite.config.ts",
    );
    assert.ok(loaded);
    const plugins = loaded.config.plugins.flat(Infinity).filter(Boolean);
    assert.equal(plugins.filter((plugin) => plugin.name === "@inertiajs/vite").length, 1);

    const phpReload = plugins.find((plugin) => plugin.name === "launch:php-reload");
    assert.ok(phpReload);
    const transformed = phpReload.transform("export {}", "/resources/js/app.tsx");
    assert.match(transformed.code, /__launchRouter\.reload\(\)/);

    const events = [];
    phpReload.hotUpdate.call(
        { environment: { name: "client", hot: { send: (event) => events.push(event) } } },
        { file: "/app/Http/Controllers/HomeController.php", modules: [] },
    );
    assert.equal(events.length, 1);
    assert.equal(events[0].type, "custom");
    assert.equal(events[0].event, "launch:php-update");
});

async function freePort() {
    const socket = createServer();
    socket.listen(0, "127.0.0.1");
    await once(socket, "listening");
    const { port } = socket.address();
    await new Promise((resolve) => socket.close(resolve));
    return port;
}

function start(port) {
    const child = spawn(process.execPath, ["bootstrap/ssr/app.js"], {
        env: { ...process.env, ORBIT_SSR_PORT: String(port) },
        stdio: ["ignore", "pipe", "pipe"],
    });
    let output = "";
    child.stdout.on("data", (data) => (output += data));
    child.stderr.on("data", (data) => (output += data));
    return { child, output: () => output };
}

async function ready(server, port) {
    for (let attempt = 0; attempt < 100; attempt++) {
        assert.equal(server.child.exitCode, null, server.output());
        try {
            const response = await fetch(`http://127.0.0.1:${port}/health`);
            if ((await response.json()).status === "OK") return;
        } catch {}
        await setTimeout(50);
    }
    assert.fail(`SSR did not start on ${port}: ${server.output()}`);
}

async function stop({ child }) {
    if (child.exitCode !== null || child.signalCode !== null) return;
    const exited = once(child, "exit");
    child.kill("SIGTERM");
    await exited;
}

await test("one bundle renders Launch on two runtime-selected ports", async () => {
    const ports = [await freePort(), await freePort()];
    const servers = ports.map(start);
    try {
        await Promise.all(servers.map((server, i) => ready(server, ports[i])));
        for (const port of ports) {
            const response = await fetch(`http://127.0.0.1:${port}/render`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    component: "Home",
                    props: {},
                    url: "/",
                    version: "test",
                }),
            });
            assert.equal(response.status, 200);
            const rendered = await response.json();
            assert.match(rendered.body, /Launch your next idea/);
            assert.match(rendered.body, /How it works/);
            assert.doesNotMatch(rendered.body, /Casa Vittoria/);
        }
    } finally {
        await Promise.all(servers.map(stop));
    }
});

await test("an invalid runtime port refuses startup", async () => {
    const server = start("invalid");
    const timeout = globalThis.setTimeout(() => server.child.kill(), 5000);
    try {
        const [code] = await once(server.child, "exit");
        assert.notEqual(code, 0);
        assert.match(server.output(), /ORBIT_SSR_PORT must be an integer/);
    } finally {
        clearTimeout(timeout);
        await stop(server);
    }
});
