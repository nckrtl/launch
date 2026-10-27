import type { InertiaPluginOptions } from "@inertiajs/vite";
import { defineLaunchConfig } from "@nckrtl/launch-ui/vite";
import { defineConfig } from "vite-plus";

const inertiaConfig = {
    ssr: {},
    frameworks: {
        package: "@inertiajs/react",
        extensions: [".tsx", ".jsx"],
        extractDefault: true,
        ssr: (configureCall) => `
import createServer from '@inertiajs/react/server'
import { renderToString } from 'react-dom/server'

const render = await ${configureCall}
const renderPage = (page) => render(page, renderToString)

if (import.meta.env.PROD) {
    const port = Number(process.env.ORBIT_SSR_PORT ?? 13719)
    if (!Number.isInteger(port) || port < 1 || port > 65535) {
        throw new Error('ORBIT_SSR_PORT must be an integer between 1 and 65535.')
    }
    createServer(renderPage, { port, host: '127.0.0.1' })
}

export default renderPage
`,
    },
} satisfies InertiaPluginOptions;

const launchConfig = await defineLaunchConfig({
    lint: { options: { typeAware: true, typeCheck: true } },
    inertia: inertiaConfig,
});

export default defineConfig(async (environment) => ({
    ...(await launchConfig(environment)),
    fmt: { ignorePatterns: [".agents/**"] },
    // Pre-commit tasks, run against staged files only by `vp staged` from
    // .vite-hooks/pre-commit. Anything they fix is re-staged automatically.
    staged: {
        "*": "vp check --fix",
        "*.php": "vendor/bin/pint",
    },
}));
