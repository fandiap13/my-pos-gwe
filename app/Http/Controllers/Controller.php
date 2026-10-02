<?php

namespace App\Http\Controllers;

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
    //
}
