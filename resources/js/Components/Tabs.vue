<script setup lang="ts">
export interface TabItem {
    value: string;
    label: string;
}

defineProps<{
    modelValue: string;
    tabs: TabItem[];
}>();

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div class="flex gap-1 border-b border-border">
        <button
            v-for="tab in tabs"
            :key="tab.value"
            type="button"
            class="border-b-2 px-4 py-2 text-sm font-medium transition-colors"
            :class="
                modelValue === tab.value
                    ? 'border-primary-dark text-primary-dark'
                    : 'border-transparent text-text-muted hover:text-text'
            "
            @click="$emit('update:modelValue', tab.value)"
        >
            {{ tab.label }}
        </button>
    </div>
</template>
