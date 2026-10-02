<script setup lang="ts">
import Alert from '@/Components/Alert.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import type { SelectOption } from '@/Components/Select.vue';
import Select from '@/Components/Select.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { Product } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    product: Product;
    categoryOptions: SelectOption[];
}>();

const form = useForm({
    category_id: props.product.category_id,
    name: props.product.name,
    sku: props.product.sku ?? '',
    barcode: props.product.barcode ?? '',
    price: props.product.price,
    cost_price: props.product.cost_price,
    min_stock: props.product.min_stock,
});

function submit() {
    form.put(route('admin.products.update', props.product.id));
}
</script>

<template>
    <Head title="Edit Produk" />

    <AdminLayout>
        <div class="space-y-4">
            <Breadcrumb
                :items="[
                    { label: 'Produk', href: route('admin.products.index') },
                    { label: product.name },
                ]"
            />

            <Card class="max-w-2xl">
                <form class="space-y-4" @submit.prevent="submit">
                    <Alert tone="info">
                        Stok saat ini: <strong>{{ product.stock }}</strong
                        >. Untuk mengubah stok, gunakan halaman Stok &gt;
                        Penyesuaian Manual.
                    </Alert>

                    <FormField
                        label="Nama Produk"
                        :error="form.errors.name"
                        required
                    >
                        <Input
                            id="name"
                            v-model="form.name"
                            autofocus
                            :invalid="!!form.errors.name"
                        />
                    </FormField>

                    <FormField
                        label="Kategori"
                        :error="form.errors.category_id"
                    >
                        <Select
                            v-model="form.category_id"
                            placeholder="Tanpa kategori"
                            :options="categoryOptions"
                            :invalid="!!form.errors.category_id"
                        />
                    </FormField>

                    <div class="grid grid-cols-2 gap-4">
                        <FormField
                            label="SKU (opsional)"
                            :error="form.errors.sku"
                        >
                            <Input
                                id="sku"
                                v-model="form.sku"
                                :invalid="!!form.errors.sku"
                            />
                        </FormField>

                        <FormField
                            label="Barcode (opsional)"
                            :error="form.errors.barcode"
                        >
                            <Input
                                id="barcode"
                                v-model="form.barcode"
                                :invalid="!!form.errors.barcode"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <FormField
                            label="Harga Jual"
                            :error="form.errors.price"
                            required
                        >
                            <MoneyInput
                                v-model="form.price"
                                :invalid="!!form.errors.price"
                            />
                        </FormField>

                        <FormField
                            label="Harga Modal (opsional)"
                            :error="form.errors.cost_price"
                        >
                            <MoneyInput
                                v-model="form.cost_price"
                                :invalid="!!form.errors.cost_price"
                            />
                        </FormField>
                    </div>

                    <FormField
                        label="Stok Minimum"
                        :error="form.errors.min_stock"
                        required
                        helper-text="Batas bawah untuk peringatan stok menipis."
                    >
                        <Input
                            id="min_stock"
                            v-model="form.min_stock"
                            type="number"
                            :invalid="!!form.errors.min_stock"
                        />
                    </FormField>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="submit" :loading="form.processing"
                            >Simpan</Button
                        >
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
