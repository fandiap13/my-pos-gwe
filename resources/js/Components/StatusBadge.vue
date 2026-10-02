<script setup lang="ts">
import Badge from '@/Components/Badge.vue';
import { computed } from 'vue';

// Status domain yang dipakai di seluruh aplikasi — lihat docs/DATABASE.md
// untuk status asli tiap tabel (transactions.status, shifts.status, dll)
// dan docs/UI.md "Aturan pemakaian warna" untuk pemetaan warnanya.
type Status =
    | 'completed'
    | 'voided'
    | 'open'
    | 'closed'
    | 'in_stock'
    | 'low_stock'
    | 'out_of_stock'
    | 'active'
    | 'inactive';

const props = defineProps<{
    status: Status;
}>();

const config: Record<
    Status,
    { label: string; tone: 'success' | 'warning' | 'danger' | 'neutral' }
> = {
    completed: { label: 'Selesai', tone: 'success' },
    voided: { label: 'Dibatalkan', tone: 'danger' },
    open: { label: 'Aktif', tone: 'success' },
    closed: { label: 'Ditutup', tone: 'neutral' },
    in_stock: { label: 'Stok Aman', tone: 'success' },
    low_stock: { label: 'Stok Menipis', tone: 'warning' },
    out_of_stock: { label: 'Stok Habis', tone: 'danger' },
    active: { label: 'Aktif', tone: 'success' },
    inactive: { label: 'Nonaktif', tone: 'neutral' },
};

const current = computed(() => config[props.status]);
</script>

<template>
    <Badge :tone="current.tone">{{ current.label }}</Badge>
</template>
