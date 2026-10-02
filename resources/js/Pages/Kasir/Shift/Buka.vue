<script setup lang="ts">
// Placeholder sementara — form Buka Shift sungguhan (input modal awal
// kas) dikerjakan di Fase 1.5. Lihat docs/ROADMAP.md.
import Card from '@/Components/Card.vue';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage<PageProps>();
</script>

<template>
    <Head title="Buka Shift" />

    <KasirLayout :shift-is-active="false">
        <Card>
            <h1 class="text-lg font-semibold text-text">
                Halo, {{ page.props.auth.user.name }}
            </h1>
            <p class="mt-1 text-sm text-text-muted">
                Anda belum punya shift aktif. Form buka shift akan dikerjakan di
                Fase 1.5.
            </p>
        </Card>
    </KasirLayout>
</template>
