<script setup lang="ts">
import Card from '@/Components/Card.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

// Placeholder sementara — Dashboard Admin sungguhan (StatCard, grafik,
// dll) dikerjakan di Fase 1.11. Lihat docs/ROADMAP.md & PROGRESS.md.
const page = usePage<PageProps>();
const Layout =
    page.props.auth.user.role === 'admin' ? AdminLayout : KasirLayout;
</script>

<template>
    <Head title="Dashboard" />

    <component :is="Layout">
        <Card>
            <p class="text-sm text-text-muted">
                Anda masuk sebagai {{ page.props.auth.user.name }}.
            </p>
        </Card>
    </component>
</template>
