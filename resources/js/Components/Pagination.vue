<script setup lang="ts">
import Select from '@/Components/Select.vue';
import type { Paginated } from '@/types/pagination';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Generic tidak dibatasi — komponen hanya memakai meta pagination,
// bukan isi data. Terima seluruh objek Paginated<T> langsung dari Inertia
// props supaya pemanggil tidak perlu destrukturisasi manual.
const props = defineProps<{
    paginated: Paginated<unknown>;
}>();

const page = usePage();

// Baris per halaman — maksimal 100, dibatasi juga di backend
// (Controller::perPage) supaya client tidak perlu mengetab terlalu banyak.
const perPageOptions = [10, 15, 25, 50, 100].map((value) => ({
    value: String(value),
    label: String(value),
}));

const perPage = ref(String(props.paginated.per_page));

watch(
    () => props.paginated.per_page,
    (value) => {
        perPage.value = String(value);
    },
);

function changePerPage(value: string | null) {
    if (!value) {
        return;
    }

    // Filter aktif (search, kategori, status, dll) ikut dipertahankan dari
    // query string saat ini — hanya per_page yang diganti dan page direset.
    const [path, query = ''] = page.url.split('?');
    const params = new URLSearchParams(query);
    params.set('per_page', value);
    params.delete('page');

    router.get(
        `${path}?${params.toString()}`,
        {},
        {
            preserveScroll: true,
            preserveState: true,
        },
    );
}
</script>

<template>
    <div
        class="flex flex-wrap items-center justify-between gap-4 border-t border-border pt-4"
    >
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <p class="text-sm text-text-muted">
                Menampilkan {{ paginated.from }}–{{ paginated.to }} dari
                {{ paginated.total }} data
            </p>

            <label
                for="per-page"
                class="flex items-center gap-2 text-sm text-text-muted"
            >
                Baris/halaman
                <span class="w-[4.5rem]">
                    <Select
                        id="per-page"
                        v-model="perPage"
                        :options="perPageOptions"
                        @update:model-value="changePerPage"
                    />
                </span>
            </label>
        </div>

        <nav v-if="paginated.last_page > 1" class="flex gap-1">
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
        </nav>
    </div>
</template>
