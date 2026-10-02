<script setup lang="ts">
import Button from '@/Components/Button.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <AuthLayout>
        <Head title="Lupa Password" />

        <p class="mb-4 text-sm text-text-muted">
            Lupa password? Masukkan email Anda, kami akan mengirimkan link untuk
            membuat password baru.
        </p>

        <p v-if="status" class="mb-4 text-sm font-medium text-success">
            {{ status }}
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Email" :error="form.errors.email" required>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autofocus
                    autocomplete="username"
                    :invalid="!!form.errors.email"
                />
            </FormField>

            <div class="flex justify-end pt-2">
                <Button type="submit" :loading="form.processing"
                    >Kirim Link Reset</Button
                >
            </div>
        </form>
    </AuthLayout>
</template>
