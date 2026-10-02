<script setup lang="ts">
import { ref } from 'vue';

withDefaults(
    defineProps<{
        text: string;
        position?: 'top' | 'bottom';
    }>(),
    {
        position: 'top',
    },
);

const visible = ref(false);
</script>

<template>
    <span
        class="relative inline-flex"
        @mouseenter="visible = true"
        @mouseleave="visible = false"
        @focusin="visible = true"
        @focusout="visible = false"
    >
        <slot />

        <span
            v-show="visible"
            role="tooltip"
            class="pointer-events-none absolute left-1/2 z-50 -translate-x-1/2 whitespace-nowrap rounded-control bg-text px-2 py-1 text-xs text-white"
            :class="position === 'top' ? 'bottom-full mb-2' : 'top-full mt-2'"
        >
            {{ text }}
        </span>
    </span>
</template>
