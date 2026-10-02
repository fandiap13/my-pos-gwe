<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import ReceiptPreview from '@/Components/ReceiptPreview.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { Receipt } from '@/types/models';
import { Head, router } from '@inertiajs/vue3';
import { Plus, Printer } from '@lucide/vue';

// Halaman struk setelah checkout sukses — checkout.md langkah 10.
// "Transaksi Baru" kembali ke keranjang kosong; "Cetak" memakai
// window.print() (lihat docs/DECISIONS.md — format thermal via
// @media print, disempurnakan di Fase 1.7).
defineProps<{
    receipt: Receipt;
    storeName: string;
    storeAddress?: string | null;
}>();

function print() {
    window.print();
}

function newTransaction() {
    router.visit(route('kasir.transaksi'));
}
</script>

<template>
    <Head title="Transaksi Selesai" />

    <KasirLayout>
        <div class="mx-auto flex max-w-md flex-col items-center gap-4">
            <Card class="w-full">
                <div class="text-center">
                    <p class="text-base font-semibold text-primary-dark">
                        Transaksi berhasil disimpan
                    </p>
                    <p class="mt-1 text-sm text-text-muted">
                        {{ receipt.transaction_number }}
                    </p>
                </div>

                <ReceiptPreview
                    class="mt-4"
                    :receipt="receipt"
                    :store-name="storeName"
                    :store-address="storeAddress"
                />
            </Card>

            <div class="flex w-full gap-3 print:hidden">
                <Button class="flex-1" @click="print">
                    <Printer class="h-4 w-4" />
                    Cetak Struk
                </Button>
                <Button
                    variant="outline"
                    class="flex-1"
                    @click="newTransaction"
                >
                    <Plus class="h-4 w-4" />
                    Transaksi Baru
                </Button>
            </div>
        </div>
    </KasirLayout>
</template>
