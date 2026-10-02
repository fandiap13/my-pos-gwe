<script setup lang="ts">
import { computed } from 'vue';

// Input nominal Rupiah: tampilan diformat dengan pemisah ribuan titik
// (mis. "446.700"), tapi v-model tetap mengirim number mentah (bukan
// string berformat) supaya konsumen (Form, Action) menerima integer
// rupiah sesuai AGENTS.md (uang = integer, tanpa desimal).
const props = withDefaults(
    defineProps<{
        modelValue: number | null;
        placeholder?: string;
        disabled?: boolean;
        invalid?: boolean;
    }>(),
    {
        disabled: false,
        invalid: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: number | null];
}>();

const displayValue = computed(() => {
    if (props.modelValue === null || Number.isNaN(props.modelValue)) {
        return '';
    }

    return new Intl.NumberFormat('id-ID').format(props.modelValue);
});

function handleInput(event: Event) {
    const raw = (event.target as HTMLInputElement).value.replace(/\D/g, '');

    if (raw === '') {
        emit('update:modelValue', null);
        return;
    }

    emit('update:modelValue', parseInt(raw, 10));
}
</script>

<template>
    <div class="relative">
        <span
            class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-text-muted"
        >
            Rp
        </span>
        <input
            type="text"
            inputmode="numeric"
            :value="displayValue"
            :placeholder="placeholder"
            :disabled="disabled"
            class="w-full rounded-control border bg-surface py-2 pl-9 pr-3 text-right text-base tabular-nums text-text placeholder:text-text-faint focus:outline-none focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:bg-background disabled:text-text-faint"
            :class="
                invalid ? 'border-danger focus:ring-danger' : 'border-border'
            "
            @input="handleInput"
        />
    </div>
</template>
