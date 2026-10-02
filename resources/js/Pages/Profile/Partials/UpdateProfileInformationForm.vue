<script setup lang="ts">
import Button from '@/Components/Button.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import type { PageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const user = usePage<PageProps>().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-semibold text-text">Informasi Profil</h2>
            <p class="mt-1 text-sm text-text-muted">
                Perbarui nama dan email akun Anda.
            </p>
        </header>

        <form
            class="mt-6 space-y-4"
            @submit.prevent="form.patch(route('profile.update'))"
        >
            <FormField label="Nama" :error="form.errors.name" required>
                <Input
                    id="name"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                    :invalid="!!form.errors.name"
                />
            </FormField>

            <FormField label="Email" :error="form.errors.email" required>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="username"
                    :invalid="!!form.errors.email"
                />
            </FormField>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm text-text-muted">
                    Email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-text underline hover:text-primary-dark"
                    >
                        Kirim ulang email verifikasi.
                    </Link>
                </p>

                <p
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-success"
                >
                    Link verifikasi baru sudah dikirim ke email Anda.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" :loading="form.processing">Simpan</Button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-text-muted"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
