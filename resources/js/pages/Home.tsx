import { Head } from "@inertiajs/react";
import { show } from "@/actions/App/Http/Controllers/HomeController";

export default function Home() {
    return (
        <div className="flex min-h-screen items-center justify-center">
            <Head title="Home" />
            <div className="text-center">
                <h1 className="text-4xl font-bold tracking-tight">React Starterkitt</h1>
                <p className="mt-3 text-muted-foreground">
                    Laravel 13 + React + Inertia v3 + Tailwind CSS v4.2
                </p>
                <a
                    href={show.url()}
                    className="mt-6 inline-block text-sm underline underline-offset-4 hover:text-primary/80"
                >
                    Home
                </a>
            </div>
        </div>
    );
}
