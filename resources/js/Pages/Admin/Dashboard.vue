<script setup lang="ts">
// Placeholder sementara — Dashboard Admin sungguhan (StatCard, grafik,
// dll) dikerjakan di Fase 1.11. Lihat docs/ROADMAP.md & PROGRESS.md.
import Card from '@/Components/Card.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <Card>
            <h1 class="text-lg font-semibold text-text">
                Selamat datang, {{ page.props.auth.user.name }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                Dashboard ringkas akan dikerjakan di Fase 1.11.
            </p>
        </Card>
    </AdminLayout>
</template>
