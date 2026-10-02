<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Input from '@/Components/Input.vue';
import Pagination from '@/Components/Pagination.vue';
import Select from '@/Components/Select.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Table from '@/Components/Table.vue';
import TableActions from '@/Components/TableActions.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Category, Product } from '@/types/models';
import type { Paginated } from '@/types/pagination';
import { Head, Link, router } from '@inertiajs/vue3';
import { Package, Pencil, Plus, Trash2 } from '@lucide/vue';
import { ref, watch } from 'vue';

interface ProductWithStatus extends Product {
    category_name: string | null;
    stock_status: 'in_stock' | 'low_stock' | 'out_of_stock';
}

const props = defineProps<{
    products: Paginated<ProductWithStatus>;
    categories: { data: Category[] };
    filters: { search?: string; category_id?: string };
}>();

const search = ref(props.filters.search ?? '');
const categoryId = ref<string | null>(props.filters.category_id ?? null);

watch([search, categoryId], ([newSearch, newCategoryId]) => {
    router.get(
        route('admin.products.index'),
        {
            search: newSearch || undefined,
            category_id: newCategoryId || undefined,
        },
        { preserveState: true, replace: true },
    );
});

const categoryOptions = () =>
    props.categories.data.map((category) => ({
        value: category.id,
        label: category.name,
    }));

const productToDelete = ref<ProductWithStatus | null>(null);
const deleting = ref(false);

function confirmDelete(product: ProductWithStatus) {
    productToDelete.value = product;
}

function destroy() {
    if (!productToDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(route('admin.products.destroy', productToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            productToDelete.value = null;
        },
    });
}

function formatPrice(value: number) {
    return new Intl.NumberFormat('id-ID').format(value);
}
</script>

<template>
    <Head title="Produk" />

    <AdminLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-text">Produk</h1>
                <Link :href="route('admin.products.create')">
                    <Button>
                        <Plus class="h-4 w-4" />
                        Tambah Produk
                    </Button>
                </Link>
            </div>

            <div class="flex flex-wrap gap-3">
                <div class="w-64">
                    <Input
                        v-model="search"
                        type="search"
                        placeholder="Cari nama, SKU, atau barcode..."
                    />
                </div>
                <div class="w-56">
                    <Select
                        v-model="categoryId"
                        placeholder="Semua kategori"
                        :options="categoryOptions()"
                    />
                </div>
            </div>

            <Card :padded="products.data.length === 0">
                <EmptyState
                    v-if="products.data.length === 0"
                    :icon="Package"
                    title="Belum ada produk"
                    description="Tambahkan produk pertama untuk mulai berjualan."
                >
                    <template #action>
                        <Link :href="route('admin.products.create')">
                            <Button size="sm">Tambah Produk</Button>
                        </Link>
                    </template>
                </EmptyState>

                <template v-else>
                    <Table>
                        <template #head>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>SKU/Barcode</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Stok</th>
                            <th>Status</th>
                            <th class="w-10"></th>
                        </template>
                        <template #body>
                            <tr
                                v-for="product in products.data"
                                :key="product.id"
                            >
                                <td class="font-medium">{{ product.name }}</td>
                                <td class="text-text-muted">
                                    {{ product.category_name ?? '—' }}
                                </td>
                                <td class="text-text-muted">
                                    {{ product.sku ?? product.barcode ?? '—' }}
                                </td>
                                <td class="text-right tabular-nums">
                                    Rp {{ formatPrice(product.price) }}
                                </td>
                                <td class="text-right tabular-nums">
                                    {{ product.stock }}
                                </td>
                                <td>
                                    <StatusBadge
                                        v-if="
                                            product.stock_status !== 'in_stock'
                                        "
                                        :status="product.stock_status"
                                    />
                                    <StatusBadge v-else status="in_stock" />
                                </td>
                                <td>
                                    <TableActions>
                                        <Link
                                            :href="
                                                route(
                                                    'admin.products.edit',
                                                    product.id,
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
                                            @click="confirmDelete(product)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            Hapus
                                        </button>
                                    </TableActions>
                                </td>
                            </tr>
                        </template>
                    </Table>

                    <div class="p-4">
                        <Pagination :paginated="products" />
                    </div>
                </template>
            </Card>
        </div>

        <ConfirmDialog
            :show="productToDelete !== null"
            danger
            title="Hapus produk?"
            :message="`Produk &quot;${productToDelete?.name}&quot; akan dihapus. Riwayat transaksi yang sudah ada tetap aman.`"
            confirm-label="Hapus"
            :processing="deleting"
            @confirm="destroy"
            @cancel="productToDelete = null"
        />
    </AdminLayout>
</template>
