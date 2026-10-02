<script setup lang="ts">
// Layout minim distraksi untuk Pages/Kasir/ — lihat docs/UI.md "Layout":
// tanpa sidebar penuh, hanya header tipis, agar area checkout maksimal.
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import Toast from '@/Components/Toast.vue';
import type { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Clock, History, LogOut } from '@lucide/vue';
import { computed, ref } from 'vue';

// shiftIsActive di-share oleh HandleInertiaRequests untuk semua request
// (dihitung di backend) — halaman tidak perlu mengirimnya sendiri.
const page = usePage<PageProps>();

const shiftIsActive = computed(() => page.props.shiftIsActive === true);

// Tombol "Kembali" ke halaman Transaksi (/kasir) — permintaan user:
// kasir yang membuka halaman riwayat/tutup shift tidak punya jalur kembali
// yang jelas. Tidak ditampilkan di halaman Transaksi (sudah di sana) dan di
// Buka Shift (halaman itu gate sebelum /kasir — back ke sana memantul balik).
const showBack = computed(
    () =>
        page.component !== 'Kasir/Transaksi/Index' &&
        page.component !== 'Kasir/Shift/Buka',
);

// Logout adalah aksi berisiko — wajib dialog konfirmasi, tidak langsung
// eksekusi. Lihat docs/UI.md Prinsip Desain. Kasir dengan shift aktif
// diingatkan secara spesifik supaya tidak logout dengan shift menggantung.
const showLogoutConfirm = ref(false);

function logout() {
    router.post(route('logout'));
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <header
            class="flex items-center justify-between border-b border-border bg-surface px-4 py-2.5 print:hidden"
        >
            <div class="flex items-center gap-3">
                <Link
                    v-if="showBack"
                    :href="route('kasir.transaksi')"
                    class="inline-flex items-center gap-1.5 rounded-control px-2 py-1.5 text-base text-text-muted hover:bg-background hover:text-text"
                >
                    <ArrowLeft class="h-4 w-4" aria-hidden="true" />
                    Kembali
                </Link>
                <span class="text-sm font-semibold text-text">POS App</span>
                <span
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
                    v-if="shiftIsActive"
                    :href="route('kasir.shift.tutup')"
                    class="rounded-control px-2 py-1.5 text-sm text-text-muted hover:bg-background hover:text-danger"
                >
                    Tutup Shift
                </Link>
                <Link
                    :href="route('kasir.shift.riwayat')"
                    class="rounded-control p-1.5 text-text-muted hover:bg-background"
                    aria-label="Riwayat Shift"
                >
                    <History class="h-4 w-4" />
                </Link>
                <button
                    type="button"
                    class="rounded-control p-1.5 text-text-muted hover:bg-background hover:text-danger"
                    aria-label="Logout"
                    @click="showLogoutConfirm = true"
                >
                    <LogOut class="h-4 w-4" />
                </button>
            </div>
        </header>

        <main class="flex-1 p-4">
            <slot />
        </main>

        <Toast />

        <ConfirmDialog
            :show="showLogoutConfirm"
            title="Keluar dari akun?"
            :message="
                shiftIsActive
                    ? 'Shift Anda masih aktif. Pastikan tidak ada transaksi yang menggantung sebelum keluar.'
                    : 'Anda akan keluar dari sesi ini dan perlu login kembali untuk melanjutkan.'
            "
            confirm-label="Keluar"
            danger
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />
    </div>
</template>
