<script setup lang="ts">
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import type { SelectOption } from '@/Components/Select.vue';
import Select from '@/Components/Select.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    categoryOptions: SelectOption[];
}>();

const form = useForm({
    category_id: null as string | null,
    name: '',
    sku: '',
    barcode: '',
    price: null as number | null,
    cost_price: null as number | null,
    initial_stock: 0,
    min_stock: 0,
});

function submit() {
    form.post(route('admin.products.store'));
}
</script>

<template>
    <Head title="Tambah Produk" />

    <AdminLayout>
        <div class="space-y-4">
            <Breadcrumb
                :items="[
                    { label: 'Produk', href: route('admin.products.index') },
                    { label: 'Tambah' },
                ]"
            />

            <Card class="max-w-2xl">
                <form class="space-y-4" @submit.prevent="submit">
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
                            helper-text="Dipakai untuk hitung profit di laporan."
                        >
                            <MoneyInput
                                v-model="form.cost_price"
                                :invalid="!!form.errors.cost_price"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <FormField
                            label="Stok Awal"
                            :error="form.errors.initial_stock"
                            required
                            helper-text="Dicatat sebagai pergerakan stok masuk."
                        >
                            <Input
                                id="initial_stock"
                                v-model="form.initial_stock"
                                type="number"
                                :invalid="!!form.errors.initial_stock"
                            />
                        </FormField>

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
                    </div>

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
