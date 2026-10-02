<script setup lang="ts">
import Input from '@/Components/Input.vue';

// Komponen search murni presentational — hasil pencarian & logic
// filter produk dikerjakan di halaman Transaksi (Fase 1.6), bukan di
// sini, supaya tidak ada API call manual dari komponen (lihat AGENTS.md:
// "jangan API call manual untuk data yang seharusnya lewat Inertia props").
withDefaults(
    defineProps<{
        modelValue: string;
        placeholder?: string;
    }>(),
    {
        placeholder: 'Cari produk (nama, SKU, atau scan barcode)...',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

// Input type="search" selalu emit string (lihat Input.vue handleInput),
// konversi number hanya terjadi untuk type="number".
function handleUpdate(value: string | number) {
    emit('update:modelValue', String(value));
}
</script>

<template>
    <Input
        type="search"
        :model-value="modelValue"
        :placeholder="placeholder"
        @update:model-value="handleUpdate"
    />
</template>
