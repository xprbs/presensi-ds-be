<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaporanPresensiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = 'belum hadir';
        if ($this->presence_in) {
            $status = 'hadir';
        } elseif ($this->absence_date) {
            $status = $this->absence_type;
        }
        return [
            'status' => $status,
            'name' => $this->name,
            'nipd' => $this->nipd,
            'classroom' => $this->classroom->class_name . ' - ' . $this->clasroom->type,
            'presence_in' => $this->presence_in,
            'absence_date' => $this->absence_date,
        ];
    }

    public function toResponse($request)
    {
        return $this->toArray($request);
    }
}
