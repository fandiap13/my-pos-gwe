<script setup lang="ts">
import Button from '@/Components/Button.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<InstanceType<typeof Input> | null>(null);
const currentPasswordInput = ref<InstanceType<typeof Input> | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
            }
            if (form.errors.current_password) {
                form.reset('current_password');
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-semibold text-text">Ubah Password</h2>
            <p class="mt-1 text-sm text-text-muted">
                Gunakan password yang panjang dan acak untuk keamanan akun.
            </p>
        </header>

        <form class="mt-6 space-y-4" @submit.prevent="updatePassword">
            <FormField
                label="Password Saat Ini"
                :error="form.errors.current_password"
            >
                <Input
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                    :invalid="!!form.errors.current_password"
                />
            </FormField>

            <FormField label="Password Baru" :error="form.errors.password">
                <Input
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                    :invalid="!!form.errors.password"
                />
            </FormField>

            <FormField
                label="Konfirmasi Password Baru"
                :error="form.errors.password_confirmation"
            >
                <Input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    :invalid="!!form.errors.password_confirmation"
                />
            </FormField>

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
