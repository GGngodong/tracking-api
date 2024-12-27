<?php

namespace App\Http\Resources;

use App\Helpers\DateParser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermitLetterResource extends JsonResource
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
            'uraian' => $this->uraian,
            'no_surat' => $this->no_surat,
            'tanggal' => $this->tanggal ? \Carbon\Carbon::parse($this->tanggal)->format('d-m-Y') : null,
            'kategori_permit_letter' => strtoupper($this->kategori_permit_letter),
            'nama_pt' => $this->nama_pt,
            'produk_no_surat_mabes' => $this->produk_no_surat_mabes ?? null,
            'dokumen_url' => $this->dokumen_url,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
//            'status_tahapan' => $this->statusTahapan,
        ];
    }
}
