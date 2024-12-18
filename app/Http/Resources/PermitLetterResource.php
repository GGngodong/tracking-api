<?php

namespace App\Http\Resources;

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
            'tanggal' => $this->tanggal_masuk_berkas ? $this->tanggal_masuk_berkas->format('d-m-Y') : null,
            'ketegori_permit_letter' => $this->kategori,
            'nama_pt' => $this->nama_pt,
            'produk_no_surat_mabes' => $this->no_produk_mabes ?? null,
            'dokumen_url' => $this->dokumen_url,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
//            'status_tahapan' => $this->statusTahapan,
        ];
    }
}
