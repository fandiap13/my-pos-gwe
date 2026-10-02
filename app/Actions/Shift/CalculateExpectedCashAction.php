<?php

namespace App\Actions\Shift;

use App\Models\Shift;
use App\Models\Transaction;

// Hitung kas seharusnya menurut sistem: modal awal + penjualan tunai
// (payment_method 'cash') berstatus 'completed' selama shift berjalan.
// Transaksi 'voided' tidak dihitung karena uangnya sudah dikembalikan
// ke pelanggan. Dipakai oleh CloseShiftAction (saat tutup shift) dan
// halaman Tutup Shift (preview rekap sebelum kasir input kas fisik).
class CalculateExpectedCashAction
{
    public function handle(Shift $shift): int
    {
        $cashSales = (int) Transaction::query()
            ->where('shift_id', $shift->id)
            ->where('payment_method', Transaction::PAYMENT_CASH)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->sum('total');

        return $shift->opening_cash + $cashSales;
    }
}
