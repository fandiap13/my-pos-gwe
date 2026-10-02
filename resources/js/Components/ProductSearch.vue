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

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <Input
        type="search"
        :model-value="modelValue"
        :placeholder="placeholder"
        @update:model-value="$emit('update:modelValue', $event)"
    />
</template>
