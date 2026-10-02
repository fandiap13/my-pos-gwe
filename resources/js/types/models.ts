// Tipe model domain, mencerminkan shape dari Http/Resources Laravel —
// lihat AGENTS.md. Ditambah bertahap seiring fitur baru dikerjakan.

export interface Category {
    id: string;
    parent_id: string | null;
    parent_name: string | null;
    name: string;
    slug: string;
    products_count: number | null;
    children_count: number | null;
}

export interface Product {
    id: string;
    category_id: string | null;
    name: string;
    sku: string | null;
    barcode: string | null;
    price: number;
    cost_price: number | null;
    stock: number;
    min_stock: number;
}

export type PaymentMethod = 'cash' | 'transfer' | 'debit';

// Item di keranjang kasir — state lokal di halaman Transaksi (Fase 1.6),
// bukan model dari backend. Snapshot nama & harga diambil dari Product
// saat ditambahkan, konsisten dengan transaction_items nanti.
export interface CartLine {
    product: Product;
    quantity: number;
}

// Shape minimal untuk struk (ReceiptPreview, Fase 1.2). Akan diselaraskan
// dengan TransactionResource backend saat Fase 1.6/1.7 (Checkout & Struk)
// benar-benar dikerjakan — field bisa bertambah (voided_at, dll).
export interface ReceiptItem {
    product_name: string;
    price: number;
    quantity: number;
    subtotal: number;
}

export interface Receipt {
    transaction_number: string;
    created_at: string;
    cashier_name: string;
    items: ReceiptItem[];
    subtotal: number;
    total: number;
    paid_amount: number;
    change_amount: number;
    payment_method: PaymentMethod;
}
