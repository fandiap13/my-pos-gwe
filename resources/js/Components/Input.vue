<script setup lang="ts">
import { Search } from '@lucide/vue';

withDefaults(
    defineProps<{
        type?: 'text' | 'number' | 'password' | 'search' | 'email';
        placeholder?: string;
        disabled?: boolean;
        invalid?: boolean;
        modelValue?: string | number;
    }>(),
    {
        type: 'text',
        disabled: false,
        invalid: false,
    },
);

defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <div class="relative">
        <Search
            v-if="type === 'search'"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-faint"
            aria-hidden="true"
        />
        <input
            :type="type === 'search' ? 'text' : type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            class="w-full rounded-control border bg-surface px-3 py-2 text-sm text-text placeholder:text-text-faint focus:outline-none focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:bg-background disabled:text-text-faint"
            :class="[
                invalid ? 'border-danger focus:ring-danger' : 'border-border',
                type === 'search' ? 'pl-9' : '',
            ]"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).value,
                )
            "
        />
    </div>
</template>
