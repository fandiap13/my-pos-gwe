<script setup lang="ts">
// Native <input type="date"> distyle — lihat docs/DECISIONS.md: tidak
// ada dependency kalender custom, cukup fitur bawaan browser.
withDefaults(
    defineProps<{
        modelValue?: string;
        disabled?: boolean;
        min?: string;
        max?: string;
    }>(),
    {
        disabled: false,
    },
);

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <input
        type="date"
        :value="modelValue"
        :disabled="disabled"
        :min="min"
        :max="max"
        class="rounded-control border border-border bg-surface px-3 py-2 text-sm text-text focus:outline-none focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:bg-background disabled:text-text-faint"
        @input="
            $emit(
                'update:modelValue',
                ($event.target as HTMLInputElement).value,
            )
        "
    />
</template>
