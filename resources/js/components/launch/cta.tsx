import React from "react";
import { Toast } from "@/components/launch/brand";
import { Frame, SectionDivider, useBP } from "@/components/launch/grid";
import { LaunchIcon } from "@/components/launch/icons";
import { useAppearance } from "@/hooks/use-appearance";

export function CTA() {
    const { resolvedAppearance } = useAppearance();
    const isDark = resolvedAppearance === "dark";
    const bp = useBP();
    const mob = bp === 0;
    const [copied, setCopied] = React.useState(false);
    const [chipHov, setChipHov] = React.useState(false);
    const prompt = "Start a new project based on launch.nckrtl.com";

    const copy = () => {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(prompt).catch(() => {});
        }
        setCopied(true);
        setTimeout(() => setCopied(false), 2200);
    };

    const pinstripe = `url("data:image/svg+xml,${encodeURIComponent(
        `<svg xmlns='http://www.w3.org/2000/svg' width='4' height='4'><path d='M-1,1 l2,-2 M0,4 l4,-4 M3,5 l2,-2' stroke='${
            chipHov ? "rgba(251,59,0,0.25)" : isDark ? "rgba(255,255,255,0.06)" : "rgba(0,0,0,0.06)"
        }' stroke-width='1'/></svg>`,
    )}")`;

    return (
        <div>
            <SectionDivider />
            <Frame style={{ padding: mob ? "64px 16px" : "96px 32px", textAlign: "center" }}>
                <h2
                    style={{
                        fontSize: "clamp(30px,7vw,44px)",
                        fontWeight: 500,
                        letterSpacing: "-0.03em",
                        lineHeight: 1.05,
                    }}
                >
                    Give your next idea
                    <br />a head start.
                </h2>
                <p
                    style={{
                        fontSize: mob ? 15 : 16,
                        color: "var(--muted-foreground)",
                        marginTop: 16,
                    }}
                >
                    One prompt to start. Paste it into your agent chat.
                </p>
                <button
                    onClick={copy}
                    title="Copy prompt"
                    onMouseEnter={() => setChipHov(true)}
                    onMouseLeave={() => setChipHov(false)}
                    style={{
                        position: "relative",
                        display: "inline-flex",
                        alignItems: mob ? "flex-start" : "center",
                        textAlign: "left",
                        maxWidth: "100%",
                        gap: mob ? 9 : 12,
                        marginTop: mob ? 26 : 32,
                        fontFamily: "var(--font-mono)",
                        fontSize: mob ? 11.5 : 13,
                        lineHeight: 1.5,
                        color: "var(--foreground)",
                        backgroundColor: "transparent",
                        backgroundImage: pinstripe,
                        backgroundRepeat: "repeat",
                        border: `1px solid ${chipHov ? "rgba(251,59,0,.3)" : "var(--grid-line)"}`,
                        borderRadius: 2,
                        padding: mob ? "12px 14px" : "14px 18px",
                        cursor: "pointer",
                        whiteSpace: mob ? "normal" : "nowrap",
                        boxShadow: "none",
                    }}
                >
                    {["tl", "tr", "bl", "br"].map((at) => {
                        const bb = "1px solid var(--primary)";
                        const cs: React.CSSProperties = {
                            position: "absolute",
                            width: 5,
                            height: 5,
                            pointerEvents: "none",
                            transition: "border-color var(--dur-fast) var(--ease-out)",
                        };
                        if (at[0] === "t") Object.assign(cs, { top: -1, borderTop: bb });
                        else Object.assign(cs, { bottom: -1, borderBottom: bb });
                        if (at[1] === "l") Object.assign(cs, { left: -1, borderLeft: bb });
                        else Object.assign(cs, { right: -1, borderRight: bb });
                        cs[
                            `border${at[0] === "t" ? "Top" : "Bottom"}${at[1] === "l" ? "Left" : "Right"}Radius`
                        ] = 2;
                        return <span key={at} style={cs} />;
                    })}
                    <span style={{ color: "var(--accent)" }}>❯</span>
                    <span>"{prompt}"</span>
                    <LaunchIcon
                        name={copied ? "check" : "copy"}
                        size={15}
                        style={{
                            color: copied ? "var(--success)" : "var(--icon-muted)",
                            flex: "none",
                        }}
                    />
                </button>
                {copied && (
                    <div
                        style={{
                            position: "fixed",
                            bottom: 28,
                            left: "50%",
                            transform: "translateX(-50%)",
                            zIndex: 100,
                            animation: "toastup .3s var(--ease-out) both",
                        }}
                    >
                        <Toast variant="success" title="Copied instructions to clipboard" />
                    </div>
                )}
            </Frame>
        </div>
    );
}
