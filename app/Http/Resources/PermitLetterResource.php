<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
            'tanggal' => $this->tanggal ? Carbon::parse($this->tanggal)->format('d-m-Y') : null,
            'kategori_permit_letter' => strtoupper($this->kategori_permit_letter),
            'sub_kategori_permit_letter' => $this->sub_kategori_permit_letter,
            'upload_status' => $this->upload_status,
            'status_tahapan' => $this->status_tahapan,
            'nama_pt' => $this->nama_pt,
            'produk_no_surat_mabes' => $this->produk_no_surat_mabes ?? null,
            'note' => $this->note ?? null,
            'dokumen_url' => $this->dokumen ? url($this->dokumen) : null,
            'released_dokumen_url' => $this->released_dokumen ? url($this->released_dokumen) : null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
