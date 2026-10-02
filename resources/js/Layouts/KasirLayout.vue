<script setup lang="ts">
// Layout minim distraksi untuk Pages/Kasir/ — lihat docs/UI.md "Layout":
// tanpa sidebar penuh, hanya header tipis, agar area checkout maksimal.
import Toast from '@/Components/Toast.vue';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Clock, History, LogOut } from '@lucide/vue';

// shiftIsActive disuplai halaman pemanggil lewat shared Inertia props
// (ditambahkan Fase 1.3 saat EnsureShiftActive middleware dibuat).
defineProps<{
    shiftIsActive?: boolean;
}>();

const page = usePage<PageProps>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <header
            class="flex items-center justify-between border-b border-border bg-surface px-4 py-2.5"
        >
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-text">POS App</span>
                <span
                    v-if="shiftIsActive !== undefined"
                    class="flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="
                        shiftIsActive
                            ? 'bg-primary-light text-primary-dark'
                            : 'bg-danger/10 text-danger'
                    "
                >
                    <Clock class="h-3 w-3" />
                    {{ shiftIsActive ? 'Shift Aktif' : 'Shift Tidak Aktif' }}
                </span>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-sm text-text-muted">{{
                    page.props.auth.user.name
                }}</span>
                <Link
                    href="#"
                    class="rounded-control p-1.5 text-text-muted hover:bg-background"
                    aria-label="Riwayat Transaksi"
                >
                    <History class="h-4 w-4" />
                </Link>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="rounded-control p-1.5 text-text-muted hover:bg-background hover:text-danger"
                    aria-label="Logout"
                >
                    <LogOut class="h-4 w-4" />
                </Link>
            </div>
        </header>

        <main class="flex-1 p-4">
            <slot />
        </main>

        <Toast />
    </div>
</template>
