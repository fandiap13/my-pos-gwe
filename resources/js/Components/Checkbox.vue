<script setup lang="ts">
withDefaults(
    defineProps<{
        modelValue?: boolean;
        disabled?: boolean;
    }>(),
    {
        modelValue: false,
        disabled: false,
    },
);

defineEmits<{
    'update:modelValue': [value: boolean];
}>();
</script>

<template>
    <input
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        class="rounded border-border text-primary-dark focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:opacity-50"
        @change="
            $emit(
                'update:modelValue',
                ($event.target as HTMLInputElement).checked,
            )
        "
    />
</template>
