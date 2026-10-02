<script setup lang="ts">
import EmptyState from '@/Components/EmptyState.vue';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Table from '@/Components/Table.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { Shift } from '@/types/models';
import type { Paginated } from '@/types/pagination';
import { Head } from '@inertiajs/vue3';
import { Clock } from '@lucide/vue';

defineProps<{
    shifts: Paginated<Shift>;
}>();

// Shift masih berjalan → kolom tutup/akhir kas belum ada, tampilkan '—'.
function formatMoney(value: number) {
    return new Intl.NumberFormat('id-ID').format(value);
}
</script>

<template>
    <Head title="Riwayat Shift" />

    <KasirLayout>
        <div class="space-y-4">
            <h1 class="text-xl font-semibold text-text">Riwayat Shift</h1>

            <div
                v-if="shifts.data.length === 0"
                class="rounded-card border border-border bg-surface"
            >
                <EmptyState
                    :icon="Clock"
                    title="Belum ada shift"
                    description="Riwayat shift Anda akan muncul di sini setelah buka shift pertama."
                />
            </div>

            <template v-else>
                <Table>
                    <template #head>
                        <th>Dibuka</th>
                        <th>Ditutup</th>
                        <th class="text-right">Modal Awal</th>
                        <th class="text-right">Kas Sistem</th>
                        <th class="text-right">Kas Fisik</th>
                        <th class="text-right">Selisih</th>
                        <th>Status</th>
                    </template>
                    <template #body>
                        <tr v-for="shift in shifts.data" :key="shift.id">
                            <td class="whitespace-nowrap text-text-muted">
                                {{ shift.opened_at }}
                            </td>
                            <td class="whitespace-nowrap text-text-muted">
                                {{ shift.closed_at ?? '—' }}
                            </td>
                            <td class="text-right tabular-nums">
                                Rp {{ formatMoney(shift.opening_cash) }}
                            </td>
                            <td class="text-right tabular-nums">
                                {{
                                    shift.expected_cash === null
                                        ? '—'
                                        : `Rp ${formatMoney(shift.expected_cash)}`
                                }}
                            </td>
                            <td class="text-right tabular-nums">
                                {{
                                    shift.closing_cash === null
                                        ? '—'
                                        : `Rp ${formatMoney(shift.closing_cash)}`
                                }}
                            </td>
                            <td
                                class="text-right tabular-nums"
                                :class="
                                    !shift.cash_difference
                                        ? 'text-text-muted'
                                        : shift.cash_difference > 0
                                          ? 'text-warning'
                                          : 'text-danger'
                                "
                            >
                                {{
                                    shift.cash_difference === null
                                        ? '—'
                                        : `Rp ${formatMoney(shift.cash_difference)}`
                                }}
                            </td>
                            <td>
                                <StatusBadge :status="shift.status" />
                            </td>
                        </tr>
                    </template>
                </Table>

                <Pagination :paginated="shifts" />
            </template>
        </div>
    </KasirLayout>
</template>
