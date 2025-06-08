<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiswaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_siswa' => $this->nama_siswa,
            'nisn' => $this->nisn,
            // 'fotos' akan menjadi array berisi URL foto dari FotoSiswaResource
            'fotos' => FotoSiswaResource::collection($this->whenLoaded('fotos')),
        ];
    }
}
