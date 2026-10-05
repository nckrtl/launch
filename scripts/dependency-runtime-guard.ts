// Reject the entire approved tooling chain, including SSR-externalized imports.
const toolingChain = "(?:braces|micromatch|fast-glob|ts-morph|@ts-morph/common|shadcn)";
const packageImport = new RegExp(`^${toolingChain}(?:/|$)`);
const installedModule = new RegExp(
    `[/\\\\]node_modules[/\\\\](?:.*[/\\\\])?${toolingChain}[/\\\\]`,
);

export function rejectRuntimeBraces(id: string): void {
    if (packageImport.test(id) || installedModule.test(id)) {
        throw new Error("braces and its approved tooling chain are forbidden in runtime modules.");
    }
}

export const runtimeDependencyGuard = {
    name: "reject-runtime-braces",
    // Applies to client and SSR builds, not the tooling executing Vite.
    moduleParsed(module: { id: string; importedIds: string[]; dynamicallyImportedIds: string[] }) {
        for (const id of [module.id, ...module.importedIds, ...module.dynamicallyImportedIds]) {
            rejectRuntimeBraces(id);
        }
    },
};
