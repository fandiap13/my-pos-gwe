<script setup lang="ts">
import { Minus, Plus } from '@lucide/vue';

const props = withDefaults(
    defineProps<{
        modelValue: number;
        min?: number;
        max?: number;
    }>(),
    {
        min: 1,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

function decrement() {
    if (props.modelValue > props.min) {
        emit('update:modelValue', props.modelValue - 1);
    }
}

function increment() {
    if (props.max === undefined || props.modelValue < props.max) {
        emit('update:modelValue', props.modelValue + 1);
    }
}
</script>

<template>
    <div class="inline-flex items-center rounded-control border border-border">
        <button
            type="button"
            class="p-2 text-text-muted hover:text-text disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="modelValue <= min"
            aria-label="Kurangi"
            @click="decrement"
        >
            <Minus class="h-3.5 w-3.5" />
        </button>
        <span class="w-8 text-center text-base tabular-nums text-text">{{
            modelValue
        }}</span>
        <button
            type="button"
            class="p-2 text-text-muted hover:text-text disabled:cursor-not-allowed disabled:opacity-40"
            :disabled="max !== undefined && modelValue >= max"
            aria-label="Tambah"
            @click="increment"
        >
            <Plus class="h-3.5 w-3.5" />
        </button>
    </div>
</template>
