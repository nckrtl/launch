import { Head } from "@inertiajs/react";
import { __, useLocale, setLocale } from "@hardimpactdev/craft-ui-react/i18n";
import { show } from "@/actions/App/Http/Controllers/HomeController";

export default function Home() {
    const locale = useLocale();

    return (
        <div className="flex min-h-screen items-center justify-center">
            <Head title={__("Home")} />
            <div className="text-center">
                <h1 className="text-4xl font-bold tracking-tight">
                    {__("React Starterkit")}
                </h1>
                <p className="mt-3 text-muted-foreground">
                    {__("Laravel 13 + React + Inertia v3 + Tailwind CSS v4.2")}
                </p>
                <p className="mt-2 text-sm text-muted-foreground">
                    {__("Hello :name, welcome to :app!", { name: "Nick", app: "Craft" })}
                </p>
                <div className="mt-6 flex items-center justify-center gap-2">
                    <span className="text-sm text-muted-foreground">{__("Language")}:</span>
                    <button
                        onClick={() => setLocale("en")}
                        className={`rounded px-2 py-1 text-sm ${locale === "en" ? "bg-primary text-primary-foreground" : "bg-muted"}`}
                    >
                        EN
                    </button>
                    <button
                        onClick={() => setLocale("nl")}
                        className={`rounded px-2 py-1 text-sm ${locale === "nl" ? "bg-primary text-primary-foreground" : "bg-muted"}`}
                    >
                        NL
                    </button>
                </div>
                <a
                    href={show.url()}
                    className="mt-4 inline-block text-sm underline underline-offset-4 hover:text-primary/80"
                >
                    {__("Home")}
                </a>
            </div>
        </div>
    );
}
