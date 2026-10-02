<script setup lang="ts">
import { Loader2 } from '@lucide/vue';

withDefaults(
    defineProps<{
        size?: 'sm' | 'md' | 'lg';
    }>(),
    {
        size: 'md',
    },
);

const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-6 w-6',
    lg: 'h-10 w-10',
};
</script>

<template>
    <Loader2
        class="animate-spin text-primary-dark"
        :class="sizeClasses[size]"
        aria-hidden="true"
    />
</template>
