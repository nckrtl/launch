import React from "react";
import { Button } from "@/components/ui/button";
import { Logo } from "@/components/launch/brand";
import { COL, MOBW, Tee, useBP } from "@/components/launch/grid";
import { LaunchIcon } from "@/components/launch/icons";

import { useAppearance } from "@/hooks/use-appearance";

const NAV_LINKS = [
    ["How it works", "#how"],
    ["Features", "#features"],
    ["UI library", "#library"],
];

export function SiteHeader() {
    const bp = useBP();
    const mob = bp === 0;
    const [open, setOpen] = React.useState(false);
    const { resolvedAppearance, updateAppearance } = useAppearance();

    const toggleTheme = () => updateAppearance(resolvedAppearance === "dark" ? "light" : "dark");

    React.useEffect(() => {
        if (!mob) setOpen(false);
    }, [mob]);

    return (
        <header
            style={{
                position: "sticky",
                top: 0,
                zIndex: 50,
                borderBottom: "1px solid var(--border)",
                background: "var(--header-bg)",
                backdropFilter: "blur(12px)",
                WebkitBackdropFilter: "blur(12px)",
            }}
        >
            <div
                style={{
                    ...COL,
                    ...(mob ? MOBW : null),
                    display: "flex",
                    alignItems: "center",
                    height: mob ? 56 : 64,
                }}
            >
                <Tee pos={{ bottom: -5, left: -4 }} />
                <Tee pos={{ bottom: -5, right: -4 }} />
                <a
                    href="#top"
                    style={{
                        display: "flex",
                        alignItems: "flex-end",
                        gap: 8,
                        textDecoration: "none",
                        color: "var(--foreground)",
                    }}
                >
                    <Logo variant="mark" height={15} />
                    <Logo variant="wordmark" height={12} />
                </a>

                {!mob && (
                    <nav
                        style={{
                            position: "absolute",
                            left: "50%",
                            transform: "translateX(-50%)",
                            display: "flex",
                            gap: bp === 1 ? 26 : 40,
                        }}
                    >
                        {NAV_LINKS.map(([t, h]) => (
                            <a
                                key={t}
                                href={h}
                                style={{
                                    fontSize: 14,
                                    fontWeight: 500,
                                    textDecoration: "none",
                                    whiteSpace: "nowrap",
                                }}
                                className="text-muted-foreground transition-colors hover:text-primary"
                            >
                                {t}
                            </a>
                        ))}
                    </nav>
                )}

                <div style={{ marginLeft: "auto", display: "flex", gap: 10, alignItems: "center" }}>
                    <Button
                        variant="outline"
                        size="icon"
                        title={
                            resolvedAppearance === "dark"
                                ? "Switch to light mode"
                                : "Switch to dark mode"
                        }
                        onClick={toggleTheme}
                        aria-label="Toggle theme"
                    >
                        <LaunchIcon
                            name={resolvedAppearance === "dark" ? "sun" : "moon"}
                            size={16}
                            accent={resolvedAppearance === "dark" ? false : "duotone"}
                            style={{
                                color: resolvedAppearance === "dark" ? "#ffffff" : "var(--accent)",
                            }}
                        />
                    </Button>
                    {!mob && (
                        <Button
                            variant="outline"
                            size="icon"
                            title="GitHub"
                            nativeButton={false}
                            render={
                                <a
                                    href="https://github.com/nckrtl/launch-starter-kit"
                                    target="_blank"
                                    rel="noreferrer"
                                />
                            }
                        >
                            <LaunchIcon name="github" size={16} />
                        </Button>
                    )}
                    <Button
                        size={mob ? "sm" : undefined}
                        onClick={() => {
                            const el = document.getElementById("agent-terminal");
                            if (el) el.scrollIntoView({ behavior: "smooth" });
                            try {
                                window.location.href = "terminal://";
                            } catch {
                                // fallback
                            }
                        }}
                    >
                        Launch now
                    </Button>
                    {mob && (
                        <button
                            onClick={() => setOpen((v) => !v)}
                            aria-label="Menu"
                            aria-expanded={open}
                            style={{
                                width: 32,
                                height: 32,
                                flex: "none",
                                display: "grid",
                                placeItems: "center",
                                background: "var(--surface-card)",
                                border: "1px solid var(--border-strong)",
                                borderRadius: 3,
                                cursor: "pointer",
                                padding: 0,
                            }}
                        >
                            <span style={{ display: "grid", gap: 4 }}>
                                <span
                                    style={{
                                        display: "block",
                                        width: 14,
                                        height: 1,
                                        background: "var(--foreground)",
                                        transform: open
                                            ? "translateY(2.5px) rotate(45deg)"
                                            : "none",
                                        transition: "transform .22s var(--ease-out)",
                                    }}
                                />
                                <span
                                    style={{
                                        display: "block",
                                        width: 14,
                                        height: 1,
                                        background: "var(--foreground)",
                                        transform: open
                                            ? "translateY(-2.5px) rotate(-45deg)"
                                            : "none",
                                        transition: "transform .22s var(--ease-out)",
                                    }}
                                />
                            </span>
                        </button>
                    )}
                </div>
            </div>

            {mob && (
                <div
                    style={{
                        overflow: "hidden",
                        maxHeight: open ? 260 : 0,
                        transition: "max-height .3s var(--ease-out)",
                        background: "var(--background)",
                    }}
                >
                    <nav style={{ ...COL, ...MOBW, display: "grid", padding: "4px 0 14px" }}>
                        {NAV_LINKS.map(([t, h]) => (
                            <a
                                key={t}
                                href={h}
                                onClick={() => setOpen(false)}
                                style={{
                                    fontSize: 15,
                                    fontWeight: 500,
                                    color: "var(--muted-foreground)",
                                    textDecoration: "none",
                                    padding: "12px 2px",
                                    borderTop: "1px solid var(--grid-line)",
                                }}
                            >
                                {t}
                            </a>
                        ))}
                    </nav>
                </div>
            )}
        </header>
    );
}
