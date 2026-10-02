<script setup lang="ts">
import QuantityInput from '@/Components/QuantityInput.vue';
import type { CartLine } from '@/types/models';
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';

const props = defineProps<{
    line: CartLine;
}>();

const emit = defineEmits<{
    'update:quantity': [quantity: number];
    remove: [];
}>();

const subtotal = computed(() =>
    new Intl.NumberFormat('id-ID').format(
        props.line.product.price * props.line.quantity,
    ),
);
</script>

<template>
    <div
        class="flex items-center gap-3 border-b border-border py-3 last:border-0"
    >
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-text">
                {{ line.product.name }}
            </p>
            <p class="text-xs text-text-faint">
                Rp
                {{ new Intl.NumberFormat('id-ID').format(line.product.price) }}
                / item
            </p>
        </div>

        <QuantityInput
            :model-value="line.quantity"
            :max="line.product.stock"
            @update:model-value="emit('update:quantity', $event)"
        />

        <p
            class="w-24 shrink-0 text-right text-sm font-semibold tabular-nums text-text"
        >
            Rp {{ subtotal }}
        </p>

        <button
            type="button"
            class="shrink-0 p-1 text-text-faint hover:text-danger"
            aria-label="Hapus dari keranjang"
            @click="emit('remove')"
        >
            <Trash2 class="h-4 w-4" />
        </button>
    </div>
</template>
