<script setup lang="ts">
import Alert from '@/Components/Alert.vue';
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
    enabled: boolean;
}[] = [
    { value: 'cash', label: 'Tunai', icon: Banknote, enabled: true },
    { value: 'transfer', label: 'Transfer', icon: Landmark, enabled: false },
    { value: 'debit', label: 'Debit', icon: CreditCard, enabled: false },
];
</script>

<template>
    <div class="space-y-3">
        <Alert tone="warning">
            Transfer dan debit belum tersedia.
        </Alert>

        <div class="grid grid-cols-3 gap-2">
            <button
                v-for="method in methods"
                :key="method.value"
                type="button"
                class="flex flex-col items-center gap-1.5 rounded-control border p-3 text-base transition-colors disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:border-border disabled:hover:bg-surface"
                :class="
                    modelValue === method.value && method.enabled
                        ? 'border-primary-dark bg-primary-light text-primary-dark'
                        : 'border-border text-text-muted hover:bg-background'
                "
                :disabled="!method.enabled"
                @click="
                    method.enabled &&
                    $emit('update:modelValue', method.value)
                "
            >
                <component
                    :is="method.icon"
                    class="h-5 w-5"
                    aria-hidden="true"
                />
                {{ method.label }}
            </button>
        </div>
    </div>
</template>
