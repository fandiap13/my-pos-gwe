<script setup lang="ts">
// Format struk thermal 58mm/80mm, dicetak via window.print() — lihat
// docs/DECISIONS.md (bukan ESC/POS). width-nya dibatasi max-w supaya
// preview di layar menyerupai ukuran kertas sebenarnya; saat dicetak,
// @media print (di app.css) yang mengatur ukuran kertas fisik.
import type { Receipt } from '@/types/models';

defineProps<{
    receipt: Receipt;
    storeName: string;
    storeAddress?: string | null;
}>();

function format(value: number) {
    return new Intl.NumberFormat('id-ID').format(value);
}

const paymentLabels = {
    cash: 'Tunai',
    transfer: 'Transfer',
    debit: 'Debit',
};
</script>

<template>
    <div
        class="mx-auto w-full max-w-[320px] bg-surface p-4 font-mono text-xs text-text print:max-w-none"
    >
        <div class="text-center">
            <p class="text-sm font-bold">{{ storeName }}</p>
            <p v-if="storeAddress" class="text-text-muted">
                {{ storeAddress }}
            </p>
        </div>

        <div class="my-2 border-t border-dashed border-border" />

        <div class="flex justify-between">
            <span>{{ receipt.transaction_number }}</span>
            <span>{{ receipt.created_at }}</span>
        </div>
        <p>Kasir: {{ receipt.cashier_name }}</p>

        <div class="my-2 border-t border-dashed border-border" />

        <div v-for="(item, index) in receipt.items" :key="index" class="mb-1">
            <p>{{ item.product_name }}</p>
            <div class="flex justify-between text-text-muted">
                <span>{{ item.quantity }} x {{ format(item.price) }}</span>
                <span class="tabular-nums">{{ format(item.subtotal) }}</span>
            </div>
        </div>

        <div class="my-2 border-t border-dashed border-border" />

        <div class="flex justify-between">
            <span>Subtotal</span>
            <span class="tabular-nums">{{ format(receipt.subtotal) }}</span>
        </div>
        <div class="flex justify-between font-bold">
            <span>Total</span>
            <span class="tabular-nums">{{ format(receipt.total) }}</span>
        </div>
        <div class="flex justify-between">
            <span>{{ paymentLabels[receipt.payment_method] }}</span>
            <span class="tabular-nums">{{ format(receipt.paid_amount) }}</span>
        </div>
        <div class="flex justify-between">
            <span>Kembalian</span>
            <span class="tabular-nums">{{
                format(receipt.change_amount)
            }}</span>
        </div>

        <div class="my-2 border-t border-dashed border-border" />

        <p class="text-center text-text-muted">Terima kasih</p>
    </div>
</template>
