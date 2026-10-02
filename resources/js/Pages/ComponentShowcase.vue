<script setup lang="ts">
// Halaman internal untuk memverifikasi seluruh komponen & state secara
// visual — lihat docs/ROADMAP.md Fase 1.2. Bukan bagian dari produk
// (tidak masuk routes/admin.php atau routes/kasir.php), hanya dev tool.
import Alert from '@/Components/Alert.vue';
import Badge from '@/Components/Badge.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import CartItem from '@/Components/CartItem.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import DatePicker from '@/Components/DatePicker.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import Modal from '@/Components/Modal.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import PaymentMethodSelector from '@/Components/PaymentMethodSelector.vue';
import PaymentSummary from '@/Components/PaymentSummary.vue';
import ProductCard from '@/Components/ProductCard.vue';
import QuantityInput from '@/Components/QuantityInput.vue';
import Radio from '@/Components/Radio.vue';
import Select from '@/Components/Select.vue';
import Skeleton from '@/Components/Skeleton.vue';
import Spinner from '@/Components/Spinner.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Table from '@/Components/Table.vue';
import Tabs from '@/Components/Tabs.vue';
import Textarea from '@/Components/Textarea.vue';
import { useToast } from '@/composables/useToast';
import type { CartLine, Product } from '@/types/models';
import { Head } from '@inertiajs/vue3';
import { Package } from '@lucide/vue';
import { ref } from 'vue';

const toast = useToast();

const textValue = ref('');
const moneyValue = ref<number | null>(45000);
const selectValue = ref('');
const checkboxValue = ref(false);
const radioValue = ref('a');
const dateValue = ref('');
const dateRangeValue = ref({ from: '', to: '' });
const activeTab = ref('tab1');
const showModal = ref(false);
const showConfirm = ref(false);

const sampleProduct: Product = {
    id: '1',
    category_id: null,
    name: 'Kopi Susu Gula Aren',
    sku: 'SKU-0001',
    barcode: null,
    price: 18000,
    cost_price: 12000,
    stock: 25,
    min_stock: 10,
};

const sampleOutOfStock: Product = {
    ...sampleProduct,
    name: 'Produk Stok Habis',
    stock: 0,
};
const sampleLowStock: Product = {
    ...sampleProduct,
    name: 'Produk Stok Menipis',
    stock: 5,
};

const cartLine = ref<CartLine>({ product: sampleProduct, quantity: 2 });
const paymentMethod = ref<'cash' | 'transfer' | 'debit'>('cash');
const paidAmount = ref<number | null>(50000);
</script>

<template>
    <Head title="Component Showcase" />

    <div class="min-h-screen space-y-10 bg-background p-8">
        <h1 class="text-2xl font-bold text-text">Component Showcase</h1>

        <!-- Button -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">Button</h2>
            <div class="flex flex-wrap gap-3">
                <Button variant="primary">Primary</Button>
                <Button variant="secondary">Secondary</Button>
                <Button variant="outline">Outline</Button>
                <Button variant="ghost">Ghost</Button>
                <Button variant="danger">Danger</Button>
                <Button loading>Loading</Button>
                <Button disabled>Disabled</Button>
                <Button size="sm">Small</Button>
                <Button size="lg">Large</Button>
            </div>
        </Card>

        <!-- Form controls -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">Form Controls</h2>
            <div class="grid max-w-xl gap-4">
                <FormField label="Input Text" helper-text="Helper text contoh">
                    <Input v-model="textValue" placeholder="Ketik sesuatu..." />
                </FormField>
                <FormField label="Input Error" error="Field ini wajib diisi">
                    <Input invalid placeholder="Invalid state" />
                </FormField>
                <FormField label="Input Search">
                    <Input type="search" placeholder="Cari..." />
                </FormField>
                <FormField label="Money Input">
                    <MoneyInput v-model="moneyValue" />
                </FormField>
                <FormField label="Textarea">
                    <Textarea placeholder="Catatan..." />
                </FormField>
                <FormField label="Select">
                    <Select
                        v-model="selectValue"
                        placeholder="Pilih opsi"
                        :options="[
                            { value: 'a', label: 'Opsi A' },
                            { value: 'b', label: 'Opsi B' },
                        ]"
                    />
                </FormField>
                <label class="flex items-center gap-2">
                    <Checkbox v-model="checkboxValue" />
                    <span class="text-sm text-text">Checkbox</span>
                </label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2">
                        <Radio v-model="radioValue" value="a" />
                        <span class="text-sm text-text">Radio A</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <Radio v-model="radioValue" value="b" />
                        <span class="text-sm text-text">Radio B</span>
                    </label>
                </div>
                <FormField label="Date Picker">
                    <DatePicker v-model="dateValue" />
                </FormField>
                <FormField label="Date Range Picker">
                    <DateRangePicker v-model="dateRangeValue" />
                </FormField>
            </div>
        </Card>

        <!-- Badge & StatusBadge -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">
                Badge & StatusBadge
            </h2>
            <div class="flex flex-wrap gap-2">
                <Badge tone="neutral">Neutral</Badge>
                <Badge tone="success">Success</Badge>
                <Badge tone="warning">Warning</Badge>
                <Badge tone="danger">Danger</Badge>
                <Badge tone="info">Info</Badge>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                <StatusBadge status="completed" />
                <StatusBadge status="voided" />
                <StatusBadge status="open" />
                <StatusBadge status="closed" />
                <StatusBadge status="in_stock" />
                <StatusBadge status="low_stock" />
                <StatusBadge status="out_of_stock" />
                <StatusBadge status="active" />
                <StatusBadge status="inactive" />
            </div>
        </Card>

        <!-- Alert & Toast -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">Alert & Toast</h2>
            <div class="space-y-3">
                <Alert tone="success" title="Berhasil"
                    >Transaksi tersimpan.</Alert
                >
                <Alert tone="warning" title="Perhatian"
                    >Stok produk ini menipis.</Alert
                >
                <Alert tone="danger" title="Gagal">Stok tidak mencukupi.</Alert>
                <Alert tone="info" title="Info">Shift Anda belum dibuka.</Alert>
            </div>
            <div class="mt-3 flex gap-2">
                <Button size="sm" @click="toast.success('Toast sukses')"
                    >Trigger Success Toast</Button
                >
                <Button
                    size="sm"
                    variant="danger"
                    @click="toast.danger('Toast gagal')"
                    >Trigger Danger Toast</Button
                >
            </div>
        </Card>

        <!-- Spinner & Skeleton -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">
                Spinner & Skeleton
            </h2>
            <div class="flex items-center gap-4">
                <Spinner size="sm" />
                <Spinner size="md" />
                <Spinner size="lg" />
            </div>
            <div class="mt-3 max-w-xs space-y-2">
                <Skeleton height="1rem" />
                <Skeleton height="1rem" width="70%" />
            </div>
        </Card>

        <!-- EmptyState -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">EmptyState</h2>
            <EmptyState
                :icon="Package"
                title="Belum ada produk"
                description="Tambahkan produk pertama Anda."
            >
                <template #action>
                    <Button size="sm">Tambah Produk</Button>
                </template>
            </EmptyState>
        </Card>

        <!-- Tabs & Breadcrumb -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">
                Tabs & Breadcrumb
            </h2>
            <Breadcrumb
                :items="[{ label: 'Admin', href: '#' }, { label: 'Produk' }]"
            />
            <div class="mt-4">
                <Tabs
                    v-model="activeTab"
                    :tabs="[
                        { value: 'tab1', label: 'Tab Satu' },
                        { value: 'tab2', label: 'Tab Dua' },
                    ]"
                />
            </div>
        </Card>

        <!-- Table -->
        <Card :padded="false">
            <div class="p-4 pb-0">
                <h2 class="text-lg font-semibold text-text">Table</h2>
            </div>
            <div class="p-4">
                <Table>
                    <template #head>
                        <th>Nama</th>
                        <th>Status</th>
                        <th class="text-right">Harga</th>
                    </template>
                    <template #body>
                        <tr>
                            <td>Produk A</td>
                            <td><StatusBadge status="in_stock" /></td>
                            <td class="text-right tabular-nums">Rp 18.000</td>
                        </tr>
                        <tr>
                            <td>Produk B</td>
                            <td><StatusBadge status="low_stock" /></td>
                            <td class="text-right tabular-nums">Rp 25.000</td>
                        </tr>
                    </template>
                </Table>
            </div>
        </Card>

        <!-- Modal & ConfirmDialog -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">
                Modal & ConfirmDialog
            </h2>
            <div class="flex gap-2">
                <Button @click="showModal = true">Buka Modal</Button>
                <Button variant="danger" @click="showConfirm = true"
                    >Buka ConfirmDialog</Button
                >
            </div>
            <Modal :show="showModal" @close="showModal = false">
                <div class="p-6">
                    <h3 class="text-base font-semibold text-text">
                        Judul Modal
                    </h3>
                    <p class="mt-2 text-sm text-text-muted">
                        Isi modal contoh.
                    </p>
                    <div class="mt-4 flex justify-end">
                        <Button variant="outline" @click="showModal = false"
                            >Tutup</Button
                        >
                    </div>
                </div>
            </Modal>
            <ConfirmDialog
                :show="showConfirm"
                danger
                title="Batalkan Transaksi?"
                message="Aksi ini akan membatalkan transaksi dan mengembalikan stok."
                @confirm="showConfirm = false"
                @cancel="showConfirm = false"
            />
        </Card>

        <!-- Komponen khusus POS -->
        <Card>
            <h2 class="mb-4 text-lg font-semibold text-text">Komponen POS</h2>
            <div class="grid grid-cols-3 gap-3">
                <ProductCard :product="sampleProduct" />
                <ProductCard :product="sampleLowStock" />
                <ProductCard :product="sampleOutOfStock" />
            </div>
            <div class="mt-4 max-w-sm">
                <CartItem
                    :line="cartLine"
                    @update:quantity="cartLine.quantity = $event"
                    @remove="() => {}"
                />
            </div>
            <div class="mt-4 max-w-sm">
                <QuantityInput
                    v-model="cartLine.quantity"
                    :max="sampleProduct.stock"
                />
            </div>
            <div class="mt-4 max-w-sm">
                <PaymentMethodSelector v-model="paymentMethod" />
            </div>
            <div class="mt-4 max-w-sm">
                <PaymentSummary
                    :subtotal="36000"
                    :total="36000"
                    :paid-amount="paidAmount"
                />
            </div>
        </Card>
    </div>
</template>
