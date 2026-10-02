<script setup lang="ts">
withDefaults(
    defineProps<{
        modelValue?: string;
        value: string;
        disabled?: boolean;
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
        type="radio"
        :checked="modelValue === value"
        :value="value"
        :disabled="disabled"
        class="border-border text-primary-dark focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:opacity-50"
        @change="$emit('update:modelValue', value)"
    />
</template>
