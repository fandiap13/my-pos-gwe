<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { PageProps } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';

// Buka Shift = gate sebelum akses halaman kasir lain — lihat docs/UI.md
// "Alur Buka Shift → Transaksi → Tutup Shift".
const page = usePage<PageProps>();

const form = useForm({
    opening_cash: null as number | null,
});

function submit() {
    form.post(route('kasir.shift.store'));
}
</script>

<template>
    <Head title="Buka Shift" />

    <KasirLayout>
        <div class="mx-auto max-w-md">
            <Card>
                <form class="space-y-4" @submit.prevent="submit">
                    <div>
                        <h1 class="text-lg font-semibold text-text">
                            Halo, {{ page.props.auth.user.name }}
                        </h1>
                        <p class="mt-1 text-sm text-text-muted">
                            Anda belum memiliki shift aktif. Hitung modal awal
                            kas di laci, lalu buka shift untuk mulai
                            bertransaksi.
                        </p>
                    </div>

                    <FormField
                        label="Modal Awal Kas"
                        :error="form.errors.opening_cash"
                        helper-text="Uang fisik di laci saat mulai kerja, tanpa desimal."
                        required
                    >
                        <MoneyInput
                            v-model="form.opening_cash"
                            placeholder="0"
                            :invalid="!!form.errors.opening_cash"
                        />
                    </FormField>

                    <Button
                        type="submit"
                        class="w-full"
                        :loading="form.processing"
                    >
                        Buka Shift
                    </Button>
                </form>
            </Card>
        </div>
    </KasirLayout>
</template>
