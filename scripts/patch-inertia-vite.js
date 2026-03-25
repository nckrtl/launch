/**
 * Patches @inertiajs/vite SSR CSS URL resolution.
 *
 * Bug: resolveDevServerOrigin() reads server.config.server.origin which
 * laravel-vite-plugin sets to a placeholder ("http://__laravel_vite_placeholder__.test").
 * This placeholder is replaced in client-side transforms but not in SSR,
 * causing CSS <link> tags to use the wrong hostname.
 *
 * Fix: Skip the placeholder origin and construct the URL from
 * server.config.server.host + port + https instead.
 *
 * TODO: Remove when fixed upstream in @inertiajs/vite
 */

import { readFileSync, writeFileSync } from "node:fs";

const file = "node_modules/@inertiajs/vite/dist/index.js";
let content = readFileSync(file, "utf-8");

const old = `function resolveDevServerOrigin(server) {
  if (server.resolvedUrls?.local[0]) {
    return new URL(server.resolvedUrls.local[0]).origin;
  }
  const protocol = server.config.server.https ? "https" : "http";
  const port = server.config.server.port ?? 5173;
  return \`\${protocol}://localhost:\${port}\`;
}`;

const patched = `function resolveDevServerOrigin(server) {
  const protocol = server.config.server.https ? "https" : "http";
  const host = server.config.server.host || "localhost";
  const port = server.config.server.port ?? 5173;
  const origin = server.config.server.origin;
  if (origin && !origin.includes("__laravel_vite_placeholder__")) {
    return origin;
  }
  if (typeof host === "string" && host !== "0.0.0.0" && host !== "::") {
    return \`\${protocol}://\${host}:\${port}\`;
  }
  if (server.resolvedUrls?.local?.[0]) {
    return new URL(server.resolvedUrls.local[0]).origin;
  }
  return \`\${protocol}://localhost:\${port}\`;
}`;

if (content.includes(old)) {
    content = content.replace(old, patched);
    writeFileSync(file, content);
    console.log("Patched @inertiajs/vite SSR CSS URL resolution");
} else if (content.includes("__laravel_vite_placeholder__")) {
    console.log("@inertiajs/vite already patched");
} else {
    console.log("@inertiajs/vite patch not needed (function signature changed)");
}
