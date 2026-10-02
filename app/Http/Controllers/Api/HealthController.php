<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Cek status API',
        description: 'Endpoint sederhana untuk memverifikasi API hidup dan merespons.',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'API berjalan normal',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                    ]
                )
            ),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
