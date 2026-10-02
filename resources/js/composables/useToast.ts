import { reactive } from 'vue';

export type ToastTone = 'success' | 'warning' | 'danger' | 'info';

export interface ToastItem {
    id: number;
    tone: ToastTone;
    message: string;
}

// State module-level (bukan per-component) supaya semua pemanggil
// useToast() berbagi antrian toast yang sama — dirender sekali di
// AdminLayout/KasirLayout/AuthLayout lewat <Toast />.
const toasts = reactive<ToastItem[]>([]);
let nextId = 1;

function show(message: string, tone: ToastTone = 'info', duration = 4000) {
    const id = nextId++;
    toasts.push({ id, tone, message });

    setTimeout(() => dismiss(id), duration);
}

function dismiss(id: number) {
    const index = toasts.findIndex((toast) => toast.id === id);

    if (index !== -1) {
        toasts.splice(index, 1);
    }
}

export function useToast() {
    return {
        toasts,
        show,
        dismiss,
        success: (message: string) => show(message, 'success'),
        warning: (message: string) => show(message, 'warning'),
        danger: (message: string) => show(message, 'danger'),
        info: (message: string) => show(message, 'info'),
    };
}
