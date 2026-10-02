<script setup lang="ts">
import { Eye, EyeOff, Search } from '@lucide/vue';
import { computed, ref } from 'vue';

const props = withDefaults(
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

// Toggle lihat/sembunyikan password — berlaku otomatis di semua field
// type="password" (Login, Reset Password, ganti password, dll).
const showPassword = ref(false);

const resolvedType = computed(() => {
    if (props.type !== 'password') {
        return props.type === 'search' ? 'text' : props.type;
    }

    return showPassword.value ? 'text' : 'password';
});
</script>

<template>
    <div class="relative">
        <Search
            v-if="type === 'search'"
            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-faint"
            aria-hidden="true"
        />
        <input
            :type="resolvedType"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            class="w-full rounded-control border bg-surface px-3 py-2 text-sm text-text placeholder:text-text-faint focus:outline-none focus:ring-2 focus:ring-primary-dark disabled:cursor-not-allowed disabled:bg-background disabled:text-text-faint"
            :class="[
                invalid ? 'border-danger focus:ring-danger' : 'border-border',
                type === 'search' ? 'pl-9' : '',
                type === 'password' ? 'pr-9' : '',
            ]"
            @input="
                $emit(
                    'update:modelValue',
                    ($event.target as HTMLInputElement).value,
                )
            "
        />
        <button
            v-if="type === 'password'"
            type="button"
            tabindex="-1"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-text-faint hover:text-text-muted"
            :aria-label="
                showPassword ? 'Sembunyikan password' : 'Lihat password'
            "
            @click="showPassword = !showPassword"
        >
            <EyeOff v-if="showPassword" class="h-4 w-4" aria-hidden="true" />
            <Eye v-else class="h-4 w-4" aria-hidden="true" />
        </button>
    </div>
</template>
