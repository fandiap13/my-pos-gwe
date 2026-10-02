<script setup lang="ts">
import StatusBadge from '@/Components/StatusBadge.vue';
import type { Product } from '@/types/models';
import { computed } from 'vue';

const props = defineProps<{
    product: Product;
}>();

defineEmits<{
    select: [product: Product];
}>();

// Lihat docs/DECISIONS.md: checkout diblokir total kalau stok = 0.
const isOutOfStock = computed(() => props.product.stock <= 0);
const isLowStock = computed(
    () => !isOutOfStock.value && props.product.stock <= props.product.min_stock,
);

const stockStatus = computed(() => {
    if (isOutOfStock.value) return 'out_of_stock';
    if (isLowStock.value) return 'low_stock';
    return 'in_stock';
});

const formattedPrice = computed(() =>
    new Intl.NumberFormat('id-ID').format(props.product.price),
);
</script>

<template>
    <button
        type="button"
        class="flex flex-col items-start gap-1 rounded-card border border-border bg-surface p-4 text-left transition-colors hover:border-primary disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:border-border"
        :disabled="isOutOfStock"
        @click="$emit('select', product)"
    >
        <div class="flex w-full items-start justify-between gap-2">
            <p class="text-sm font-medium text-text">{{ product.name }}</p>
            <StatusBadge
                v-if="stockStatus !== 'in_stock'"
                :status="stockStatus"
            />
        </div>
        <p class="text-base font-semibold tabular-nums text-text">
            Rp {{ formattedPrice }}
        </p>
        <p class="text-xs text-text-faint">Stok: {{ product.stock }}</p>
    </button>
</template>
