<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import FormField from '@/Components/FormField.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { Shift } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

// Tutup Shift: rekap kas sistem vs kas fisik, selisih ditampilkan SEBELUM
// konfirmasi — lihat docs/UI.md "Alur Buka Shift → Transaksi → Tutup Shift"
// & prinsip "Konfirmasi untuk aksi berisiko".
const props = defineProps<{
    shift: Shift;
    expectedCash: number;
}>();

const form = useForm({
    closing_cash: null as number | null,
});

const cashSales = computed(() => props.expectedCash - props.shift.opening_cash);

// Selisih = kas fisik − kas sistem. Positif = lebih, negatif = kurang.
const difference = computed(() =>
    form.closing_cash === null ? null : form.closing_cash - props.expectedCash,
);

const showConfirm = ref(false);

function submit() {
    if (form.closing_cash === null) {
        form.post(route('kasir.shift.close'));
        return;
    }

    // Selisih kas wajib konfirmasi dulu (docs/UI.md Prinsip Desain);
    // kalau kas sesuai sistem, langsung tutup tanpa dialog.
    if (difference.value !== 0) {
        showConfirm.value = true;
        return;
    }

    form.post(route('kasir.shift.close'));
}

function confirmClose() {
    showConfirm.value = false;
    form.post(route('kasir.shift.close'));
}

function formatMoney(value: number) {
    return new Intl.NumberFormat('id-ID').format(value);
}
</script>

<template>
    <Head title="Tutup Shift" />

    <KasirLayout>
        <div class="mx-auto max-w-md space-y-4">
            <Card>
                <h1 class="text-lg font-semibold text-text">Tutup Shift</h1>
                <p class="mt-1 text-sm text-text-muted">
                    Dibuka {{ shift.opened_at }} — pastikan kas fisik di laci
                    sesuai sebelum menutup shift.
                </p>

                <dl class="mt-4 space-y-2 text-base">
                    <div class="flex justify-between">
                        <dt class="text-text-muted">Modal Awal Kas</dt>
                        <dd class="tabular-nums text-text">
                            Rp {{ formatMoney(shift.opening_cash) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-muted">Penjualan Tunai</dt>
                        <dd class="tabular-nums text-text">
                            Rp {{ formatMoney(cashSales) }}
                        </dd>
                    </div>
                    <div
                        class="flex justify-between border-t border-border pt-2"
                    >
                        <dt class="font-medium text-text">
                            Kas Seharusnya (Sistem)
                        </dt>
                        <dd class="font-semibold tabular-nums text-text">
                            Rp {{ formatMoney(expectedCash) }}
                        </dd>
                    </div>
                </dl>
            </Card>

            <Card>
                <form class="space-y-4" @submit.prevent="submit">
                    <FormField
                        label="Kas Fisik Aktual"
                        :error="form.errors.closing_cash"
                        helper-text="Hitung uang fisik di laci, tanpa desimal."
                        required
                    >
                        <MoneyInput
                            v-model="form.closing_cash"
                            placeholder="0"
                            :invalid="!!form.errors.closing_cash"
                        />
                    </FormField>

                    <div
                        v-if="difference !== null"
                        class="rounded-control p-4 text-base"
                        :class="
                            difference === 0
                                ? 'bg-primary-light text-primary-dark'
                                : difference > 0
                                  ? 'bg-warning/15 text-warning'
                                  : 'bg-danger/10 text-danger'
                        "
                    >
                        <p class="font-medium">
                            {{
                                difference === 0
                                    ? 'Kas sesuai sistem'
                                    : `Selisih kas: Rp ${formatMoney(Math.abs(difference))} (${difference > 0 ? 'lebih' : 'kurang'})`
                            }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        variant="danger"
                        :loading="form.processing"
                    >
                        Tutup Shift
                    </Button>
                </form>
            </Card>
        </div>

        <ConfirmDialog
            :show="showConfirm"
            title="Tutup shift dengan selisih kas?"
            :message="
                difference === null
                    ? ''
                    : `Kas fisik ${difference > 0 ? 'lebih' : 'kurang'} Rp ${formatMoney(Math.abs(difference))} dari kas sistem. Tutup shift dengan selisih ini?`
            "
            confirm-label="Tutup Shift"
            danger
            :processing="form.processing"
            @confirm="confirmClose"
            @cancel="showConfirm = false"
        />
    </KasirLayout>
</template>
