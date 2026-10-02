<script setup lang="ts">
withDefaults(
    defineProps<{
        label?: string;
        error?: string;
        helperText?: string;
        required?: boolean;
    }>(),
    {
        required: false,
    },
);
</script>

<template>
    <div>
        <label
            v-if="label"
            class="mb-1.5 block text-base font-medium text-text"
        >
            {{ label }}
            <span v-if="required" class="text-danger">*</span>
        </label>

        <slot />

        <p v-if="error" class="mt-1.5 text-sm text-danger">{{ error }}</p>
        <p v-else-if="helperText" class="mt-1.5 text-sm text-text-muted">
            {{ helperText }}
        </p>
    </div>
</template>
