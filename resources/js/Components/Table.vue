<script setup lang="ts">
// Wrapper tabel generik — header & body diisi lewat slot, bukan prop
// columns/rows, supaya kolom custom (badge, aksi, dll) tetap fleksibel.
// Pemakaian: <Table><template #head>...</template><template #body>...</template></Table>
</script>

<template>
    <div class="overflow-hidden rounded-card border border-border bg-surface">
        <table class="w-full text-left text-base">
            <thead class="border-b border-border bg-background">
                <tr
                    class="[&>th]:px-4 [&>th]:py-3 [&>th]:text-sm [&>th]:font-medium [&>th]:text-text-faint"
                >
                    <slot name="head" />
                </tr>
            </thead>
            <tbody
                class="divide-y divide-border [&>tr>td]:px-4 [&>tr>td]:py-3 [&>tr>td]:text-text"
            >
                <slot name="body" />
            </tbody>
        </table>
    </div>
</template>
