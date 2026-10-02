<script setup lang="ts">
import Alert from '@/Components/Alert.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import CartItem from '@/Components/CartItem.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FormField from '@/Components/FormField.vue';
import Modal from '@/Components/Modal.vue';
import MoneyInput from '@/Components/MoneyInput.vue';
import PaymentMethodSelector from '@/Components/PaymentMethodSelector.vue';
import PaymentSummary from '@/Components/PaymentSummary.vue';
import ProductCard from '@/Components/ProductCard.vue';
import ProductSearch from '@/Components/ProductSearch.vue';
import { useToast } from '@/composables/useToast';
import KasirLayout from '@/Layouts/KasirLayout.vue';
import type { CartLine, PaymentMethod, Product } from '@/types/models';
import { Head, useForm } from '@inertiajs/vue3';
import { SearchX, X } from '@lucide/vue';
import { computed, ref } from 'vue';

// Halaman utama kasir — lihat docs/features/checkout.md & docs/UI.md
// "Alur Buka Shift → Transaksi". Semua produk dimuat lewat props sekali
// per kunjungan; pencarian/scan difilter client-side (keputusan ada di
// docs/DECISIONS.md). Validasi stok/harga/uang tetap di backend.
const props = defineProps<{ products: Product[] }>();

const toast = useToast();

const search = ref('');
const cart = ref<CartLine[]>([]);
const showPayment = ref(false);
const paymentMethod = ref<PaymentMethod>('cash');
const paidAmount = ref<number | null>(null);

const form = useForm({
    items: [] as { product_id: string; quantity: number; price: number }[],
    payment_method: 'cash' as PaymentMethod,
    paid_amount: null as number | null,
});

const filteredProducts = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return props.products;
    }

    return props.products.filter(
        (product) =>
            product.name.toLowerCase().includes(query) ||
            (product.sku ?? '').toLowerCase().includes(query) ||
            (product.barcode ?? '').toLowerCase().includes(query),
    );
});

// Batasi kartu yang dirender supaya ringan walau katalog besar.
const visibleProducts = computed(() => filteredProducts.value.slice(0, 24));

const resultLabel = computed(() => {
    const query = search.value.trim();

    if (!query) {
        return `${props.products.length} produk tersedia — pilih produk atau scan barcode.`;
    }

    const found = filteredProducts.value.length;

    if (found === 0) {
        return 'Produk tidak ditemukan.';
    }

    return found > visibleProducts.value.length
        ? `Menampilkan ${visibleProducts.value.length} dari ${found} hasil untuk "${query}".`
        : `${found} hasil untuk "${query}".`;
});

const itemCount = computed(() =>
    cart.value.reduce((count, line) => count + line.quantity, 0),
);

const subtotal = computed(() =>
    cart.value.reduce(
        (sum, line) => sum + line.product.price * line.quantity,
        0,
    ),
);

// Belum ada diskon/pajak (PRD §6 "perlu diisi") — total = subtotal.
const total = computed(() => subtotal.value);

// Untuk transfer/debit nominal dibayar otomatis = total (tanpa kembalian).
const displayPaidAmount = computed(() =>
    paymentMethod.value === 'cash' ? paidAmount.value : total.value,
);

const isCashShort = computed(
    () =>
        paymentMethod.value === 'cash' &&
        paidAmount.value !== null &&
        paidAmount.value < total.value,
);

const canSubmit = computed(
    () =>
        cart.value.length > 0 &&
        (paymentMethod.value !== 'cash' ||
            (paidAmount.value !== null && paidAmount.value >= total.value)),
);

// ValidationException dari backend (stok habis, harga berubah, shift
// ditutup, bayar kurang, dll) tampil sebagai banner di modal pembayaran.
const checkoutError = computed(() => {
    const messages = Object.values(form.errors).filter(Boolean);

    return messages.length > 0 ? messages[0] : null;
});

function addToCart(product: Product) {
    if (product.stock <= 0) {
        return;
    }

    const line = cart.value.find((item) => item.product.id === product.id);

    if (line) {
        if (line.quantity >= product.stock) {
            toast.warning(
                `Stok ${product.name} tidak cukup (tersisa ${product.stock}).`,
            );
            return;
        }

        line.quantity += 1;
    } else {
        cart.value.push({ product, quantity: 1 });
    }

    search.value = '';
    toast.success(`${product.name} ditambahkan`);
}

function updateQuantity(line: CartLine, quantity: number) {
    line.quantity = Math.min(Math.max(quantity, 1), line.product.stock);
}

function removeLine(line: CartLine) {
    cart.value = cart.value.filter(
        (item) => item.product.id !== line.product.id,
    );
}

// Enter di search = konfirmasi scan barcode (keyboard input biasa dari
// scanner, lihat PRD §7): cocokkan barcode/SKU persis, atau satu-satunya
// hasil pencarian.
function handleSearchEnter() {
    const query = search.value.trim();

    if (!query) {
        return;
    }

    const match =
        filteredProducts.value.find(
            (product) =>
                product.barcode === query ||
                (product.sku ?? '').toLowerCase() === query.toLowerCase(),
        ) ??
        (filteredProducts.value.length === 1
            ? filteredProducts.value[0]
            : undefined);

    if (match) {
        addToCart(match);
        return;
    }

    toast.warning(`Produk "${query}" tidak ditemukan.`);
}

function openPayment() {
    if (cart.value.length === 0) {
        return;
    }

    paymentMethod.value = 'cash';
    paidAmount.value = null;
    showPayment.value = true;
}

function submit() {
    form.items = cart.value.map((line) => ({
        product_id: line.product.id,
        quantity: line.quantity,
        price: line.product.price,
    }));
    form.payment_method = paymentMethod.value;
    form.paid_amount =
        paymentMethod.value === 'cash' ? paidAmount.value : total.value;

    form.post(route('kasir.transaksi.store'), {
        onSuccess: () => {
            // Redirect ke halaman struk me-unmount halaman ini (keranjang
            // reset otomatis) — clear juga sebagai pengaman.
            showPayment.value = false;
            cart.value = [];
            search.value = '';
        },
    });
}
</script>

<template>
    <Head title="Transaksi" />

    <KasirLayout>
        <div class="flex flex-col gap-4 lg:flex-row">
            <!-- Kiri: cari & pilih produk -->
            <div class="min-w-0 flex-1 space-y-4">
                <ProductSearch
                    v-model="search"
                    @keydown.enter="handleSearchEnter"
                />

                <p class="text-sm text-text-muted">
                    {{ resultLabel }}
                </p>

                <div
                    v-if="visibleProducts.length > 0"
                    class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in visibleProducts"
                        :key="product.id"
                        :product="product"
                        @select="addToCart"
                    />
                </div>

                <Card v-else>
                    <EmptyState
                        :icon="SearchX"
                        title="Produk tidak ditemukan"
                        description="Coba kata kunci lain, atau periksa nama, SKU, atau barcode produk."
                    />
                </Card>
            </div>

            <!-- Kanan: keranjang & aksi bayar (selalu terlihat) -->
            <Card
                class="w-full shrink-0 lg:sticky lg:top-4 lg:w-[24rem] lg:self-start"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-text">Keranjang</h2>
                    <span
                        class="rounded-full bg-primary-light px-2.5 py-0.5 text-sm font-medium text-primary-dark"
                    >
                        {{ itemCount }} item
                    </span>
                </div>

                <div class="mt-2">
                    <template v-if="cart.length > 0">
                        <CartItem
                            v-for="line in cart"
                            :key="line.product.id"
                            :line="line"
                            @update:quantity="updateQuantity(line, $event)"
                            @remove="removeLine(line)"
                        />
                    </template>
                    <EmptyState
                        v-else
                        title="Keranjang kosong"
                        description="Pilih produk di samping, atau scan barcode untuk mulai."
                    />
                </div>

                <div class="mt-4 border-t border-border pt-4">
                    <PaymentSummary
                        :subtotal="subtotal"
                        :total="total"
                        :paid-amount="null"
                    />

                    <Button
                        class="mt-4 w-full"
                        size="lg"
                        :disabled="cart.length === 0"
                        @click="openPayment"
                    >
                        Bayar (Rp
                        {{ new Intl.NumberFormat('id-ID').format(total) }})
                    </Button>
                </div>
            </Card>
        </div>

        <!-- Modal pembayaran -->
        <Modal :show="showPayment" max-width="md" @close="showPayment = false">
            <div class="space-y-4 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-text">
                            Pembayaran
                        </h2>
                        <p class="text-sm text-text-muted">
                            {{ cart.length }} produk · {{ itemCount }} item
                        </p>
                    </div>
                    <button
                        type="button"
                        class="p-1 text-text-faint hover:text-text"
                        aria-label="Tutup"
                        @click="showPayment = false"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <PaymentMethodSelector v-model="paymentMethod" />

                <FormField
                    v-if="paymentMethod === 'cash'"
                    label="Nominal Dibayar"
                    helper-text="Uang yang diterima dari pelanggan."
                    required
                >
                    <MoneyInput
                        v-model="paidAmount"
                        placeholder="0"
                        :invalid="isCashShort"
                    />
                </FormField>

                <p v-if="isCashShort" class="text-sm text-danger">
                    Nominal dibayar kurang dari total.
                </p>

                <div class="rounded-control bg-background p-4">
                    <PaymentSummary
                        :subtotal="subtotal"
                        :total="total"
                        :paid-amount="displayPaidAmount"
                    />
                </div>

                <Alert
                    v-if="checkoutError"
                    tone="danger"
                    title="Transaksi gagal"
                >
                    {{ checkoutError }}
                </Alert>

                <div class="flex justify-end gap-2">
                    <Button
                        variant="secondary"
                        :disabled="form.processing"
                        @click="showPayment = false"
                    >
                        Batal
                    </Button>
                    <Button
                        :loading="form.processing"
                        :disabled="!canSubmit"
                        @click="submit"
                    >
                        Selesaikan Transaksi
                    </Button>
                </div>
            </div>
        </Modal>
    </KasirLayout>
</template>
