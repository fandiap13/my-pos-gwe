<script setup lang="ts">
import { useToast, type ToastTone } from '@/composables/useToast';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { CircleAlert, CircleCheck, Info, TriangleAlert, X } from '@lucide/vue';
import { watch } from 'vue';

const { toasts, dismiss, success, danger } = useToast();

// Flash message dari redirect backend (->with('success'|'error', ...)) →
// toast. Dipasang di sini karena komponen ini dirender sekali oleh tiap
// Layout (Admin/Kasir/Auth). immediate: true supaya pesan dari redirect
// langsung tampil saat halaman pertama dimount — nilainya sudah ada sejak
// awal, bukan hasil perubahan state.
const page = usePage<PageProps>();

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([flashSuccess, flashError]) => {
        if (flashSuccess) {
            success(flashSuccess);
        }
        if (flashError) {
            danger(flashError);
        }
    },
    { immediate: true },
);

const toneClasses: Record<ToastTone, string> = {
    success: 'bg-primary-light text-primary-dark',
    warning: 'bg-warning/10 text-warning',
    danger: 'bg-danger/10 text-danger',
    info: 'bg-info/10 text-info',
};

const icons = {
    success: CircleCheck,
    warning: TriangleAlert,
    danger: CircleAlert,
    info: Info,
};
</script>

<template>
    <div
        class="pointer-events-none fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-2"
    >
        <TransitionGroup
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-x-4"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex items-start gap-2 rounded-control p-3 text-sm shadow-lg"
                :class="toneClasses[toast.tone]"
            >
                <component
                    :is="icons[toast.tone]"
                    class="h-4 w-4 shrink-0"
                    aria-hidden="true"
                />
                <p class="flex-1">{{ toast.message }}</p>
                <button
                    type="button"
                    aria-label="Tutup"
                    @click="dismiss(toast.id)"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
