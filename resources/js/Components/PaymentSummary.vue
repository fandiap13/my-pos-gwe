<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    subtotal: number;
    total: number;
    paidAmount: number | null;
}>();

// Kembalian hanya ditampilkan kalau nominal dibayar valid (cukup/lebih).
// Perhitungan final & resmi tetap di backend saat submit — lihat
// docs/features/checkout.md: validasi backend tetap wajib.
const changeAmount = computed(() => {
    if (props.paidAmount === null || props.paidAmount < props.total) {
        return null;
    }

    return props.paidAmount - props.total;
});

function format(value: number) {
    return new Intl.NumberFormat('id-ID').format(value);
}
</script>

<template>
    <div class="space-y-2 text-base">
        <div class="flex justify-between text-text-muted">
            <span>Subtotal</span>
            <span class="tabular-nums">Rp {{ format(subtotal) }}</span>
        </div>

        <div
            class="flex justify-between border-t border-border pt-2 text-lg font-semibold text-text"
        >
            <span>Total</span>
            <span class="tabular-nums">Rp {{ format(total) }}</span>
        </div>

        <div
            v-if="paidAmount !== null"
            class="flex justify-between text-text-muted"
        >
            <span>Dibayar</span>
            <span class="tabular-nums">Rp {{ format(paidAmount) }}</span>
        </div>

        <div
            v-if="changeAmount !== null"
            class="flex justify-between text-xl font-bold text-primary-dark"
        >
            <span>Kembalian</span>
            <span class="tabular-nums">Rp {{ format(changeAmount) }}</span>
        </div>
    </div>
</template>
