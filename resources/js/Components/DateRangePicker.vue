<script setup lang="ts">
import DatePicker from '@/Components/DatePicker.vue';

export interface DateRange {
    from: string;
    to: string;
}

const props = defineProps<{
    modelValue: DateRange;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: DateRange];
}>();

// Preset cepat untuk Laporan & Riwayat Transaksi — lihat docs/UI.md.
function applyPreset(days: number) {
    const to = new Date();
    const from = new Date();
    from.setDate(from.getDate() - (days - 1));

    emit('update:modelValue', {
        from: from.toISOString().slice(0, 10),
        to: to.toISOString().slice(0, 10),
    });
}

function updateFrom(value: string) {
    emit('update:modelValue', { ...props.modelValue, from: value });
}

function updateTo(value: string) {
    emit('update:modelValue', { ...props.modelValue, to: value });
}
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <DatePicker
            :model-value="modelValue.from"
            :max="modelValue.to"
            @update:model-value="updateFrom"
        />
        <span class="text-sm text-text-muted">s/d</span>
        <DatePicker
            :model-value="modelValue.to"
            :min="modelValue.from"
            @update:model-value="updateTo"
        />

        <div class="ml-2 flex gap-1">
            <button
                type="button"
                class="rounded-control border border-border px-2.5 py-1 text-xs text-text-muted hover:bg-primary-light hover:text-primary-dark"
                @click="applyPreset(7)"
            >
                7 hari
            </button>
            <button
                type="button"
                class="rounded-control border border-border px-2.5 py-1 text-xs text-text-muted hover:bg-primary-light hover:text-primary-dark"
                @click="applyPreset(30)"
            >
                30 hari
            </button>
        </div>
    </div>
</template>
