import { defineLaunchConfig } from "@nckrtl/launch-ui/vite";
import { defineConfig } from "vite-plus";

import { runtimeDependencyGuard } from "./scripts/dependency-runtime-guard";

const launchConfig = await defineLaunchConfig({
    // The SSR port is baked into bootstrap/ssr/app.js at build time and has no env
    // override, so it has to be pinned here. 13714-13718 are taken on the main1
    // production node (13717 is toolbar), hence 13719.
    inertia: { ssr: { port: 13719 } },
    lint: { options: { typeAware: true, typeCheck: true } },
});

export default defineConfig(async (environment) => {
    const config = await launchConfig(environment);

    return {
        ...config,
        plugins: [...(config.plugins ?? []), runtimeDependencyGuard],
        fmt: { ignorePatterns: [".agents/**", ".ai/package-guidelines.md", "boost.json"] },
        // Pre-commit tasks, run against staged files only by `vp staged` from
        // .vite-hooks/pre-commit. Anything they fix is re-staged automatically.
        staged: {
            "*": "vp check --fix",
            "*.php": "vendor/bin/pint",
        },
    };
});
