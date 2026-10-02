<script setup lang="ts">
// Placeholder sementara — halaman Transaksi/Checkout sungguhan
// dikerjakan di Fase 1.6. Lihat docs/ROADMAP.md & docs/features/checkout.md.
import Card from '@/Components/Card.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Transaksi" />

    <KasirLayout>
        <Card>
            <h1 class="text-lg font-semibold text-text">
                Selamat datang, {{ page.props.auth.user.name }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                Halaman checkout akan dikerjakan di Fase 1.6.
            </p>
        </Card>
    </KasirLayout>
</template>
