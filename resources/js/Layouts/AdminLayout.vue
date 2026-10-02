<script setup lang="ts">
// Sidebar + header untuk Pages/Admin/ — lihat docs/UI.md "Layout" &
// "Referensi Visual". Menu mengikuti docs/UI.md "Daftar Halaman" Mode
// Admin. Route belum ada (Fase 1.3+), href sementara "#" — diisi saat
// routes/admin.php dibuat.
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Toast from '@/Components/Toast.vue';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    ChevronDown,
    LayoutDashboard,
    LogOut,
    Package,
    Search,
    Settings,
    ShoppingCart,
    Tags,
    Users,
    Warehouse,
} from '@lucide/vue';
import { ref } from 'vue';

const page = usePage<PageProps>();

const collapsed = ref(false);

interface MenuItem {
    label: string;
    icon: typeof LayoutDashboard;
    href: string;
}

interface MenuGroup {
    label: string;
    icon: typeof LayoutDashboard;
    children: MenuItem[];
}

const menu: (MenuItem | MenuGroup)[] = [
    { label: 'Dashboard', icon: LayoutDashboard, href: '#' },
    {
        label: 'Produk',
        icon: Package,
        children: [
            { label: 'Daftar Produk', icon: Package, href: '#' },
            { label: 'Kategori', icon: Tags, href: '#' },
        ],
    },
    { label: 'Stok', icon: Warehouse, href: '#' },
    { label: 'Transaksi', icon: ShoppingCart, href: '#' },
    {
        label: 'Laporan',
        icon: BarChart3,
        children: [
            { label: 'Penjualan', icon: BarChart3, href: '#' },
            { label: 'Per Shift/Kasir', icon: BarChart3, href: '#' },
        ],
    },
    { label: 'Pengguna', icon: Users, href: '#' },
    { label: 'Pengaturan', icon: Settings, href: '#' },
];

function isGroup(item: MenuItem | MenuGroup): item is MenuGroup {
    return 'children' in item;
}

const openGroups = ref<Set<string>>(new Set());

function toggleGroup(label: string) {
    if (openGroups.value.has(label)) {
        openGroups.value.delete(label);
    } else {
        openGroups.value.add(label);
    }
}
</script>

<template>
    <div class="flex min-h-screen bg-background">
        <!-- Sidebar -->
        <aside
            class="hidden shrink-0 flex-col border-r border-border bg-surface transition-all md:flex"
            :class="collapsed ? 'w-16' : 'w-[220px]'"
        >
            <div class="flex items-center gap-2 border-b border-border p-4">
                <ApplicationLogo
                    class="h-7 w-7 shrink-0 fill-current text-primary-dark"
                />
                <span
                    v-if="!collapsed"
                    class="truncate text-sm font-semibold text-text"
                >
                    POS App
                </span>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto p-2">
                <template v-for="item in menu" :key="item.label">
                    <button
                        v-if="isGroup(item)"
                        type="button"
                        class="flex w-full items-center gap-3 rounded-control px-3 py-2 text-sm text-text-muted hover:bg-primary-light hover:text-primary-dark"
                        @click="toggleGroup(item.label)"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed" class="flex-1 text-left">{{
                            item.label
                        }}</span>
                        <ChevronDown
                            v-if="!collapsed"
                            class="h-3.5 w-3.5 transition-transform"
                            :class="
                                openGroups.has(item.label) ? 'rotate-180' : ''
                            "
                        />
                    </button>
                    <div
                        v-if="
                            isGroup(item) &&
                            openGroups.has(item.label) &&
                            !collapsed
                        "
                        class="ml-7 space-y-1"
                    >
                        <Link
                            v-for="child in item.children"
                            :key="child.label"
                            :href="child.href"
                            class="block rounded-control px-3 py-1.5 text-sm text-text-muted hover:bg-primary-light hover:text-primary-dark"
                        >
                            {{ child.label }}
                        </Link>
                    </div>
                    <Link
                        v-else-if="!isGroup(item)"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-control px-3 py-2 text-sm text-text-muted hover:bg-primary-light hover:text-primary-dark"
                    >
                        <component :is="item.icon" class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </aside>

        <div class="flex flex-1 flex-col">
            <!-- Header -->
            <header
                class="flex items-center justify-between border-b border-border bg-surface px-6 py-3"
            >
                <button
                    type="button"
                    class="rounded-control p-1.5 text-text-muted hover:bg-background md:hidden"
                    @click="collapsed = !collapsed"
                >
                    <Search class="h-5 w-5" />
                </button>

                <div class="relative hidden md:block">
                    <Search
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-faint"
                    />
                    <input
                        type="search"
                        placeholder="Cari..."
                        class="w-72 rounded-control border border-border bg-background py-1.5 pl-9 pr-12 text-sm placeholder:text-text-faint focus:outline-none focus:ring-2 focus:ring-primary-dark"
                    />
                    <kbd
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded border border-border bg-surface px-1.5 py-0.5 text-[10px] text-text-faint"
                    >
                        ⌘K
                    </kbd>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm text-text-muted">{{
                        page.props.auth.user.name
                    }}</span>
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

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>

        <Toast />
    </div>
</template>
