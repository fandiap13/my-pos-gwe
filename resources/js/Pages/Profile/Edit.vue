<script setup lang="ts">
import Card from '@/Components/Card.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

// Route /profile bersifat shared (Admin & Kasir) — layout dipilih sesuai
// role user yang login, lihat docs/UI.md & docs/DECISIONS.md.
defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const Layout =
    page.props.auth.user.role === 'admin' ? AdminLayout : KasirLayout;
</script>

<template>
    <Head title="Profil" />

    <component :is="Layout">
        <div class="mx-auto max-w-xl space-y-6">
            <h1 class="text-xl font-semibold text-text">Profil</h1>

            <Card>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </Card>

            <Card>
                <UpdatePasswordForm />
            </Card>
        </div>
    </component>
</template>
