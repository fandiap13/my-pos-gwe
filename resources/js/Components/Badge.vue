<script setup lang="ts">
type Tone = 'neutral' | 'success' | 'warning' | 'danger' | 'info';

withDefaults(
    defineProps<{
        tone?: Tone;
    }>(),
    {
        tone: 'neutral',
    },
);

// Badge selalu berupa tint (latar muda + teks gelap), bukan solid —
// lihat docs/UI.md "Aturan pemakaian warna".
const toneClasses: Record<Tone, string> = {
    neutral: 'bg-background text-text-muted',
    success: 'bg-primary-light text-primary-dark',
    warning: 'bg-warning/15 text-warning',
    danger: 'bg-danger/10 text-danger',
    info: 'bg-info/10 text-info',
};

defineExpose({ toneClasses });
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
        :class="toneClasses[tone]"
    >
        <slot />
    </span>
</template>
