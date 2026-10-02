<script setup lang="ts">
import Button from '@/Components/Button.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Konfirmasi Password" />

        <p class="mb-4 text-sm text-text-muted">
            Ini area aman aplikasi. Mohon konfirmasi password Anda sebelum
            melanjutkan.
        </p>

        <form class="space-y-4" @submit.prevent="submit">
            <FormField label="Password" :error="form.errors.password" required>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autofocus
                    autocomplete="current-password"
                    :invalid="!!form.errors.password"
                />
            </FormField>

            <div class="flex justify-end pt-2">
                <Button type="submit" :loading="form.processing"
                    >Konfirmasi</Button
                >
            </div>
        </form>
    </AuthLayout>
</template>
