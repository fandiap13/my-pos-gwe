<script setup lang="ts">
import type { Paginated } from '@/types/pagination';
import { Link } from '@inertiajs/vue3';

// Generic tidak dibatasi — komponen hanya memakai links/meta pagination,
// bukan isi data. Terima seluruh objek Paginated<T> langsung dari Inertia
// props supaya pemanggil tidak perlu destrukturisasi manual.
defineProps<{
    paginated: Paginated<unknown>;
}>();
</script>

<template>
    <nav
        v-if="paginated.last_page > 1"
        class="flex items-center justify-between border-t border-border pt-4"
    >
        <p class="text-sm text-text-muted">
            Menampilkan {{ paginated.from }}–{{ paginated.to }} dari
            {{ paginated.total }} data
        </p>

        <div class="flex gap-1">
            <template v-for="(link, index) in paginated.links" :key="index">
                <span
                    v-if="link.url === null"
                    class="rounded-control px-3 py-1.5 text-sm text-text-faint"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    class="rounded-control px-3 py-1.5 text-sm"
                    :class="
                        link.active
                            ? 'bg-primary-dark text-white'
                            : 'text-text hover:bg-primary-light'
                    "
                >
                    <span v-html="link.label" />
                </Link>
            </template>
        </div>
    </nav>
</template>
