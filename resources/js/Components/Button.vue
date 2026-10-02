<script setup lang="ts">
import { Loader2 } from '@lucide/vue';
import { computed } from 'vue';

type Variant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger';
type Size = 'sm' | 'md' | 'lg';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        size?: Size;
        type?: 'button' | 'submit' | 'reset';
        disabled?: boolean;
        loading?: boolean;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        type: 'button',
        disabled: false,
        loading: false,
    },
);

const isDisabled = computed(() => props.disabled || props.loading);

const variantClasses: Record<Variant, string> = {
    primary:
        'bg-primary-dark text-white hover:bg-primary-dark/90 focus-visible:outline-primary-dark',
    secondary:
        'bg-surface text-text border border-border hover:bg-primary-light focus-visible:outline-primary-dark',
    outline:
        'bg-transparent text-text border border-border hover:bg-primary-light focus-visible:outline-primary-dark',
    ghost: 'bg-transparent text-text hover:bg-primary-light focus-visible:outline-primary-dark',
    danger: 'bg-danger text-white hover:bg-danger/90 focus-visible:outline-danger',
};

const sizeClasses: Record<Size, string> = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-4 py-2 text-sm',
    lg: 'px-6 py-3 text-base',
};
</script>

<template>
    <button
        :type="type"
        :disabled="isDisabled"
        class="inline-flex items-center justify-center gap-2 rounded-control font-medium transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        :class="[variantClasses[variant], sizeClasses[size]]"
    >
        <Loader2
            v-if="loading"
            class="h-4 w-4 animate-spin"
            aria-hidden="true"
        />
        <slot />
    </button>
</template>
