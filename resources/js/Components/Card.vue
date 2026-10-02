<script setup lang="ts">
withDefaults(
    defineProps<{
        padded?: boolean;
    }>(),
    {
        padded: true,
    },
);
</script>

<template>
    <div
        class="rounded-card border border-border bg-surface shadow-sm"
        :class="padded ? 'p-6' : ''"
    >
        <slot />
    </div>
</template>
