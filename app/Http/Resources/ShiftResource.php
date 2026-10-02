<?php

namespace App\Http\Resources;

use App\Models\Shift;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Shift
 */
class ShiftResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Konversi UTC → zona waktu toko hanya di layer tampilan, tidak
        // pernah di query — lihat docs/DECISIONS.md. StoreSetting::current()
        // sudah di-cache per request (once()).
        $timezone = StoreSetting::current()->timezone;

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn () => $this->user?->name),
            'opening_cash' => $this->opening_cash,
            'closing_cash' => $this->closing_cash,
            'expected_cash' => $this->expected_cash,
            // Selisih kas (closing - expected), dihitung di accessor model
            // — tidak perlu kolom tersendiri, lihat docs/DATABASE.md.
            'cash_difference' => $this->cashDifference(),
            'status' => $this->status,
            'opened_at' => $this->formatTimestamp($this->opened_at, $timezone),
            'closed_at' => $this->formatTimestamp($this->closed_at, $timezone),
        ];
    }

    private function formatTimestamp(?Carbon $timestamp, string $timezone): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        // copy() supaya atribut model asli tidak ikut bergeser timezone.
        return $timestamp->copy()->timezone($timezone)->format('Y-m-d H:i');
    }
}
