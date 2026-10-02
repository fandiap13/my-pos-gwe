<script setup lang="ts">
withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        disabled?: boolean;
        invalid?: boolean;
        rows?: number;
    }>(),
    {
        disabled: false,
        invalid: false,
        rows: 3,
    },
);

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <textarea
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="rows"
        class="w-full rounded-control border bg-surface px-3 py-2 text-base text-text placeholder:text-text-faint focus:outline-none focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:bg-background disabled:text-text-faint"
        :class="invalid ? 'border-danger focus:ring-danger' : 'border-border'"
        @input="
            $emit(
                'update:modelValue',
                ($event.target as HTMLTextAreaElement).value,
            )
        "
    />
</template>
