import { fileURLToPath } from "node:url";

const advisory = "https://github.com/advisories/GHSA-vfj7-8cjw-p6xm";
const expiry = "2026-10-12T00:00:00Z";
const approvedPaths = [
    "shadcn > fast-glob > micromatch > braces",
    "shadcn > ts-morph > @ts-morph/common > fast-glob > micromatch > braces",
].sort((a, b) => a.localeCompare(b));

// Resolve Bun's nested lockfile keys, including scoped package names.
function resolve(packages, parent, dependency) {
    let prefix = parent;
    while (prefix) {
        const key = `${prefix}/${dependency}`;
        if (packages[key]) return key;
        prefix = prefix.slice(0, Math.max(0, prefix.lastIndexOf("/")));
    }
    return packages[dependency] ? dependency : null;
}

export function bracesPaths(lock, manifest) {
    const packages = lock.packages;
    const parents = new Map();
    for (const [key, entry] of Object.entries(packages)) {
        const metadata = entry[2];
        for (const dependency of Object.keys({
            ...metadata.dependencies,
            ...metadata.optionalDependencies,
            ...metadata.peerDependencies,
        })) {
            const target = resolve(packages, key, dependency);
            if (target) {
                if (!parents.has(target)) parents.set(target, new Set());
                parents.get(target).add(key);
            }
        }
    }
    const roots = new Set(
        [manifest, lock.workspaces?.[""] ?? {}].flatMap((root) =>
            Object.keys({
                ...root.dependencies,
                ...root.devDependencies,
                ...root.optionalDependencies,
                ...root.peerDependencies,
            }),
        ),
    );
    const paths = new Set();
    function walk(key, path) {
        if (roots.has(key)) paths.add(path.join(" > "));
        for (const parent of parents.get(key) ?? []) {
            if (!path.includes(parent)) walk(parent, [parent, ...path]);
        }
    }
    walk("braces", ["braces"]);
    return [...paths].sort((a, b) => a.localeCompare(b));
}

export function enforceException(audit, lock, manifest, registry, now = new Date()) {
    if (!Number.isFinite(now.getTime()) || now >= new Date(expiry)) {
        throw new Error(`Braces risk approval expired ${expiry}; no automatic renewal.`);
    }
    const entries = Object.entries(audit);
    if (entries.length !== 1 || entries[0][0] !== "braces" || entries[0][1].length !== 1) {
        throw new Error(
            "Audit scope changed: fix all other advisories; remove this exception if braces is fixed.",
        );
    }
    const finding = audit.braces[0];
    if (
        finding.url !== advisory ||
        finding.severity !== "high" ||
        finding.vulnerable_versions !== "<=3.0.3"
    ) {
        throw new Error(
            "Braces advisory identity, severity or affected range differs from approval.",
        );
    }
    const braces = Object.keys(lock.packages).filter(
        (key) =>
            key === "braces" ||
            key.endsWith("/braces") ||
            lock.packages[key][0].startsWith("braces@"),
    );
    if (braces.length !== 1 || lock.packages.braces[0] !== "braces@3.0.3") {
        throw new Error("Braces version/instances differ from approved 3.0.3.");
    }
    const workspaceNames = Object.keys(lock.workspaces ?? {});
    if (manifest.workspaces || workspaceNames.length !== 1 || workspaceNames[0] !== "") {
        throw new Error("Additional workspaces are outside the approved tooling scope.");
    }
    if (
        [manifest, lock.workspaces[""]].some(
            (root) =>
                root.dependencies?.shadcn ||
                root.optionalDependencies?.shadcn ||
                root.peerDependencies?.shadcn ||
                !root.devDependencies?.shadcn,
        )
    ) {
        throw new Error("shadcn must remain a development-only CLI dependency.");
    }
    const paths = bracesPaths(lock, manifest);
    if (JSON.stringify(paths) !== JSON.stringify(approvedPaths)) {
        throw new Error(`Braces dependency paths differ from approval: ${paths.join("; ")}`);
    }
    if (!registry.versions || !registry.versions["3.0.3"]) {
        throw new Error("Unable to verify braces release metadata; refusing exception.");
    }
    if (
        Object.keys(registry.versions).some((version) => {
            if (!/^\d+\.\d+\.\d+$/.test(version)) return false;
            const [major, minor, patch] = version.split(".").map(Number);
            return major > 3 || (major === 3 && (minor > 0 || patch > 3));
        })
    ) {
        throw new Error(
            "A newer stable braces release exists; investigate the fix and remove the exception.",
        );
    }
    return `UNRESOLVED HIGH RISK: ${advisory}\nNick approved question 59 on 2026-10-05 19:54 CEST; expires ${expiry}.\nBuild/tooling only: ${paths.join("; ")}\nRuntime builds separately reject braces modules. This is risk acceptance, not a clean audit.`;
}

export async function main() {
    const result = Bun.spawnSync(["bun", "audit", "--json"], { stdout: "pipe", stderr: "pipe" });
    const stderr = new TextDecoder().decode(result.stderr);
    if (stderr) process.stderr.write(stderr);
    if (![0, 1].includes(result.exitCode))
        throw new Error(`bun audit failed (${result.exitCode}).`);
    const audit = JSON.parse(new TextDecoder().decode(result.stdout));
    console.error(`Bun audit findings (unfiltered):\n${JSON.stringify(audit, null, 2)}`);
    // Always validate the exception while it exists, even if audit becomes clean.
    const lock = Bun.JSONC.parse(await Bun.file("bun.lock").text());
    const manifest = await Bun.file("package.json").json();
    const response = await fetch("https://registry.npmjs.org/braces", {
        signal: AbortSignal.timeout(30000),
    });
    if (!response.ok) throw new Error(`Braces registry lookup failed (${response.status}).`);
    const registry = await response.json();
    console.error(enforceException(audit, lock, manifest, registry));
}

if (process.argv[1] && fileURLToPath(import.meta.url) === process.argv[1]) {
    main().catch((error) => {
        console.error(error.message);
        process.exitCode = 1;
    });
}
