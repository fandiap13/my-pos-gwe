<script setup lang="ts">
import { CircleAlert, CircleCheck, Info, TriangleAlert } from '@lucide/vue';
import { computed } from 'vue';

type Tone = 'success' | 'warning' | 'danger' | 'info';

const props = withDefaults(
    defineProps<{
        tone?: Tone;
        title?: string;
    }>(),
    {
        tone: 'info',
    },
);

const toneClasses: Record<Tone, string> = {
    success: 'bg-primary-light text-primary-dark',
    warning: 'bg-warning/10 text-warning',
    danger: 'bg-danger/10 text-danger',
    info: 'bg-info/10 text-info',
};

const icons = {
    success: CircleCheck,
    warning: TriangleAlert,
    danger: CircleAlert,
    info: Info,
};

const icon = computed(() => icons[props.tone]);
</script>

<template>
    <div class="flex gap-3 rounded-control p-4" :class="toneClasses[tone]">
        <component :is="icon" class="h-5 w-5 shrink-0" aria-hidden="true" />
        <div class="text-base">
            <p v-if="title" class="font-medium">{{ title }}</p>
            <div class="text-text-muted"><slot /></div>
        </div>
    </div>
</template>
