<script setup lang="ts">
import Button from '@/Components/Button.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <AuthLayout>
        <Head title="Verifikasi Email" />

        <p class="mb-4 text-sm text-text-muted">
            Mohon verifikasi email Anda dengan mengklik link yang baru saja kami
            kirimkan. Kalau belum menerima email-nya, kami bisa kirim ulang.
        </p>

        <p
            v-if="verificationLinkSent"
            class="mb-4 text-sm font-medium text-success"
        >
            Link verifikasi baru sudah dikirim ke email Anda.
        </p>

        <form
            class="flex items-center justify-between"
            @submit.prevent="submit"
        >
            <Button type="submit" :loading="form.processing"
                >Kirim Ulang Email Verifikasi</Button
            >

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="text-sm text-text-muted underline hover:text-text"
            >
                Keluar
            </Link>
        </form>
    </AuthLayout>
</template>
