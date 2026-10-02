<script setup lang="ts">
// Layout untuk halaman login/authentication — lihat docs/UI.md "Layout".
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-background px-4"
    >
        <ApplicationLogo class="h-12 w-12 fill-current text-primary-dark" />

        <div
            class="mt-6 w-full max-w-sm rounded-card border border-border bg-surface p-6 shadow-sm"
        >
            <slot />
        </div>
    </div>
</template>
