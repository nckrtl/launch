import "../css/app.css";

import { createInertiaApp } from "@inertiajs/react";
import { initializeTheme } from "@/hooks/use-appearance";
import AuthLayout from "@/components/auth-layout";
import AppSidebarLayout from "@/components/app-sidebar-layout";
import SettingsLayout from "@/components/settings-layout";

createInertiaApp({
    title: (title) =>
        title
            ? `${title} - ${import.meta.env.VITE_APP_NAME || "Laravel"}`
            : (import.meta.env.VITE_APP_NAME || "Laravel"),
    layout: (name) => {
        switch (true) {
            case name === "Home":
                return null;
            case name.startsWith("auth/"):
                return AuthLayout;
            case name.startsWith("settings/"):
                return [AppSidebarLayout, SettingsLayout];
            default:
                return AppSidebarLayout;
        }
    },
});

initializeTheme();
