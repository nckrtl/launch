import { defineCraftConfig } from "@hardimpactdev/craft-ui-react/vite";
import { defineConfig } from "vite-plus";

const craftConfig = await defineCraftConfig({
    inertia: { ssr: false },
});

export default defineConfig(async (environment) => ({
    ...(await craftConfig(environment)),
    fmt: { ignorePatterns: [".agents/**"] },
    staged: { "*": "vp check --fix" },
}));
