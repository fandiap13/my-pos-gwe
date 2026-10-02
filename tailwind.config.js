import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

// Design tokens sesuai docs/UI.md — jangan ubah nilai di sini tanpa
// mengubah docs/UI.md juga (dan sebaliknya), keduanya harus tetap sinkron.
/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: "#16A34A",
                    dark: "#15803D",
                    light: "#DCFCE7",
                },
                background: "#F8FAF9",
                surface: "#FFFFFF",
                border: "#E2E8E4",
                text: {
                    DEFAULT: "#17221B",
                    muted: "#66736B",
                    faint: "#94A39A",
                },
                success: "#16A34A",
                warning: "#F59E0B",
                danger: "#DC2626",
                info: "#2563EB",
            },
            borderRadius: {
                card: "16px",
                control: "10px",
            },
        },
    },

    plugins: [forms],
};
