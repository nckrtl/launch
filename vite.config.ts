import { defineLaunchConfig } from "@nckrtl/launch-ui/vite";
import { defineConfig } from "vite-plus";

const launchConfig = await defineLaunchConfig({
    // Agentation comes from nckrtl/laravel-toolbar-agentation, not the bundle.
    agentation: false,
    inertia: { ssr: false },
});

export default defineConfig(async (environment) => ({
    ...(await launchConfig(environment)),
    fmt: { ignorePatterns: [".agents/**"] },
    staged: { "*": "vp check --fix" },
}));
