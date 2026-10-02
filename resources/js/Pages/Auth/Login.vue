<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Checkbox from '@/Components/Checkbox.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <AuthLayout>
        <Head title="Masuk" />

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

            <FormField label="Password" :error="form.errors.password" required>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    :invalid="!!form.errors.password"
                />
            </FormField>

            <label class="flex items-center gap-2">
                <Checkbox v-model="form.remember" />
                <span class="text-sm text-text-muted">Ingat saya</span>
            </label>

            <div class="flex items-center justify-between pt-2">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-text-muted underline hover:text-text"
                >
                    Lupa password?
                </Link>
                <Button
                    type="submit"
                    :loading="form.processing"
                    class="ml-auto"
                >
                    Masuk
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
