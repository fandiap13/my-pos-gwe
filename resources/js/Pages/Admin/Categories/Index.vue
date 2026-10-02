<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Table from '@/Components/Table.vue';
import TableActions from '@/Components/TableActions.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Category } from '@/types/models';
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Tags, Trash2 } from '@lucide/vue';
import { ref } from 'vue';

defineProps<{
    categories: { data: Category[] };
}>();

const categoryToDelete = ref<Category | null>(null);
const deleting = ref(false);

function confirmDelete(category: Category) {
    categoryToDelete.value = category;
}

function destroy() {
    if (!categoryToDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(
        route('admin.categories.destroy', categoryToDelete.value.id),
        {
            preserveScroll: true,
            onFinish: () => {
                deleting.value = false;
                categoryToDelete.value = null;
            },
        },
    );
}
</script>

<template>
    <Head title="Kategori" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-text">Kategori</h1>
                <Link :href="route('admin.categories.create')">
                    <Button>
                        <Plus class="h-4 w-4" />
                        Tambah Kategori
                    </Button>
                </Link>
            </div>

            <Card :padded="categories.data.length === 0">
                <EmptyState
                    v-if="categories.data.length === 0"
                    :icon="Tags"
                    title="Belum ada kategori"
                    description="Tambahkan kategori pertama untuk mulai mengelompokkan produk."
                >
                    <template #action>
                        <Link :href="route('admin.categories.create')">
                            <Button size="sm">Tambah Kategori</Button>
                        </Link>
                    </template>
                </EmptyState>

                <Table v-else>
                    <template #head>
                        <th>Nama</th>
                        <th>Parent</th>
                        <th class="text-right">Produk</th>
                        <th class="text-right">Subkategori</th>
                        <th class="w-10"></th>
                    </template>
                    <template #body>
                        <tr
                            v-for="category in categories.data"
                            :key="category.id"
                        >
                            <td class="font-medium">{{ category.name }}</td>
                            <td class="text-text-muted">
                                {{ category.parent_name ?? '—' }}
                            </td>
                            <td class="text-right tabular-nums">
                                {{ category.products_count }}
                            </td>
                            <td class="text-right tabular-nums">
                                {{ category.children_count }}
                            </td>
                            <td>
                                <TableActions>
                                    <Link
                                        :href="
                                            route(
                                                'admin.categories.edit',
                                                category.id,
                                            )
                                        "
                                        class="flex items-center gap-2 px-4 py-2 text-sm text-text hover:bg-background"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-danger hover:bg-background"
                                        @click="confirmDelete(category)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        Hapus
                                    </button>
                                </TableActions>
                            </td>
                        </tr>
                    </template>
                </Table>
            </Card>
        </div>

        <ConfirmDialog
            :show="categoryToDelete !== null"
            danger
            title="Hapus kategori?"
            :message="`Kategori &quot;${categoryToDelete?.name}&quot; akan dihapus. Tindakan ini tidak bisa dibatalkan kalau tidak ada produk/subkategori terkait.`"
            confirm-label="Hapus"
            :processing="deleting"
            @confirm="destroy"
            @cancel="categoryToDelete = null"
        />
    </AdminLayout>
</template>
