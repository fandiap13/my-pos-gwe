<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'POS App API',
    description: 'Dokumentasi API untuk endpoint eksternal (routes/api.php). Lihat AGENTS.md: endpoint internal app tidak didokumentasikan di sini karena dilayani lewat Inertia, bukan JSON API.'
)]
#[OA\Server(
    url: '/',
    description: 'Server lokal'
)]
abstract class Controller
{
    /**
     * Jumlah baris per halaman untuk halaman daftar (index).
     *
     * `per_page` bukan input form, jadi nilainya dibatasi (dibulatkan ke
     * rentang aman), bukan ditolak — URL yang tidak valid tetap
     * menampilkan data dengan jumlah wajar. Maksimal 100 baris supaya
     * client tidak perlu mengetab terlalu banyak.
     */
    protected function perPage(Request $request, int $default = 15): int
    {
        return max(1, min(100, $request->integer('per_page', $default)));
    }
}
