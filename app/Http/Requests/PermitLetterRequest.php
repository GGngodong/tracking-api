<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermitLetterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uraian' => ['required', 'string', 'max:255'],
            'no_surat' => ['required', 'string', 'max:255', 'unique:permit_letters,no_surat'],
            'kategori' => ['required', 'string', 'in:ops,dtm,dtu,dkk'],
            'nama_pt' => ['required', 'string', 'max:255'],
            'tanggal_masuk_berkas' => ['required', 'date', 'date_format:d-m-Y'],
            'no_produk_mabes' => ['nullable', 'string', 'max:255', 'unique:permit_letters,no_produk_mabes'],
            'dokumen' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'status_tahapan' => ['nullable', 'string', 'in:terverifikasi,dalam_proses,selesai'],
        ];
    }
}
