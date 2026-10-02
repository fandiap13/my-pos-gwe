<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';

export interface BreadcrumbItem {
    label: string;
    href?: string;
}

defineProps<{
    items: BreadcrumbItem[];
}>();
</script>

<template>
    <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-sm">
        <template v-for="(item, index) in items" :key="index">
            <ChevronRight
                v-if="index > 0"
                class="h-3.5 w-3.5 text-text-faint"
                aria-hidden="true"
            />
            <Link
                v-if="item.href"
                :href="item.href"
                class="text-text-muted hover:text-text"
            >
                {{ item.label }}
            </Link>
            <span v-else class="text-text" aria-current="page">{{
                item.label
            }}</span>
        </template>
    </nav>
</template>
