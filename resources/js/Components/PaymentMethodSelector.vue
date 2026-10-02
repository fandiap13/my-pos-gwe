<script setup lang="ts">
import type { PaymentMethod } from '@/types/models';
import { Banknote, CreditCard, Landmark } from '@lucide/vue';

defineProps<{
    modelValue: PaymentMethod;
}>();

defineEmits<{
    'update:modelValue': [value: PaymentMethod];
}>();

const methods: {
    value: PaymentMethod;
    label: string;
    icon: typeof Banknote;
}[] = [
    { value: 'cash', label: 'Tunai', icon: Banknote },
    { value: 'transfer', label: 'Transfer', icon: Landmark },
    { value: 'debit', label: 'Debit', icon: CreditCard },
];
</script>

<template>
    <div class="grid grid-cols-3 gap-2">
        <button
            v-for="method in methods"
            :key="method.value"
            type="button"
            class="flex flex-col items-center gap-1.5 rounded-control border p-3 text-sm transition-colors"
            :class="
                modelValue === method.value
                    ? 'border-primary-dark bg-primary-light text-primary-dark'
                    : 'border-border text-text-muted hover:bg-background'
            "
            @click="$emit('update:modelValue', method.value)"
        >
            <component :is="method.icon" class="h-5 w-5" aria-hidden="true" />
            {{ method.label }}
        </button>
    </div>
</template>
