<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue';
import { EllipsisVertical } from '@lucide/vue';

// Trigger titik tiga + dropdown — isi aksi (Edit, Hapus, Void, dll)
// lewat slot default, konsisten dipakai di TableActions tiap baris tabel.
</script>

<template>
    <Dropdown align="right" width="48">
        <template #trigger>
            <button
                type="button"
                class="rounded-control p-1.5 text-text-muted hover:bg-background hover:text-text"
                aria-label="Buka menu aksi"
            >
                <EllipsisVertical class="h-4 w-4" />
            </button>
        </template>

        <template #content>
            <div class="flex flex-col py-1">
                <slot />
            </div>
        </template>
    </Dropdown>
</template>
