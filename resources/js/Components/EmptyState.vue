<script setup lang="ts">
import type { Component } from 'vue';

withDefaults(
    defineProps<{
        icon?: Component;
        title: string;
        description?: string;
    }>(),
    {
        icon: undefined,
    },
);
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <div
            v-if="icon"
            class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-background text-text-faint"
        >
            <component :is="icon" class="h-6 w-6" aria-hidden="true" />
        </div>
        <p class="text-sm font-medium text-text">{{ title }}</p>
        <p v-if="description" class="mt-1 text-sm text-text-muted">
            {{ description }}
        </p>
        <div v-if="$slots.action" class="mt-4">
            <slot name="action" />
        </div>
    </div>
</template>
