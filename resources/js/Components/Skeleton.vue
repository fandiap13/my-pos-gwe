<script setup lang="ts">
withDefaults(
    defineProps<{
        width?: string;
        height?: string;
        rounded?: 'control' | 'card' | 'full';
    }>(),
    {
        width: '100%',
        height: '1rem',
        rounded: 'control',
    },
);

const roundedClasses = {
    control: 'rounded-control',
    card: 'rounded-card',
    full: 'rounded-full',
};
</script>

<template>
    <div
        class="animate-pulse bg-border"
        :class="roundedClasses[rounded]"
        :style="{ width, height }"
    />
</template>
