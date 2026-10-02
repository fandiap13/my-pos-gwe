<script setup lang="ts">
import { ChevronDown } from '@lucide/vue';

export interface SelectOption {
    value: string;
    label: string;
}

withDefaults(
    defineProps<{
        modelValue?: string;
        options: SelectOption[];
        placeholder?: string;
        disabled?: boolean;
        invalid?: boolean;
    }>(),
    {
        disabled: false,
        invalid: false,
    },
);

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div class="relative">
        <select
            :value="modelValue"
            :disabled="disabled"
            class="w-full appearance-none rounded-control border bg-surface px-3 py-2 pr-9 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:bg-background disabled:text-text-faint"
            :class="
                invalid ? 'border-danger focus:ring-danger' : 'border-border'
            "
            @change="
                $emit(
                    'update:modelValue',
                    ($event.target as HTMLSelectElement).value,
                )
            "
        >
            <option v-if="placeholder" value="" disabled>
                {{ placeholder }}
            </option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <ChevronDown
            class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-faint"
            aria-hidden="true"
        />
    </div>
</template>
