<script setup lang="ts">
import Button from '@/Components/Button.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Reset Password" />

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

            <FormField
                label="Password Baru"
                :error="form.errors.password"
                required
            >
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    :invalid="!!form.errors.password"
                />
            </FormField>

            <FormField
                label="Konfirmasi Password"
                :error="form.errors.password_confirmation"
                required
            >
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    :invalid="!!form.errors.password_confirmation"
                />
            </FormField>

            <div class="flex justify-end pt-2">
                <Button type="submit" :loading="form.processing"
                    >Reset Password</Button
                >
            </div>
        </form>
    </AuthLayout>
</template>
