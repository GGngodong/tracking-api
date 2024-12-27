<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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
        $rules = [
            'uraian' => ['nullable', 'string', 'max:255'],
            'no_surat' => ['required', 'string', 'max:255', 'unique:permit_letters,no_surat,' . $this->route('id')],
            'kategori_permit_letter' => ['nullable', 'string', 'in:ops,dtm,dtu,dkk'],
            'nama_pt' => ['nullable', 'string', 'max:255'],
            'tanggal' => ['nullable', 'date', 'date_format:d-m-Y'],
            'produk_no_surat_mabes' => ['nullable', 'string', 'max:255', 'unique:permit_letters,produk_no_surat_mabes,' . $this->route('id')],
            'dokumen' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            // Allow fields to be nullable during update
            $rules['uraian'] = ['nullable', 'string', 'max:255'];
            $rules['no_surat'] = ['nullable', 'string', 'max:255', 'unique:permit_letters,no_surat,' . $this->route('id')];
            $rules['kategori_permit_letter'] = ['nullable', 'string', 'in:ops,dtm,dtu,dkk'];
            $rules['nama_pt'] = ['nullable', 'string', 'max:255'];
            $rules['tanggal'] = ['nullable', 'date', 'date_format:d-m-Y'];
            $rules['produk_no_surat_mabes'] = ['nullable', 'string', 'max:255', 'unique:permit_letters,produk_no_surat_mabes,' . $this->route('id')];
            $rules['dokumen'] = ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'];
        }
        if ($this->isMethod('post')) {
            $rules['uraian'] = ['required', 'string', 'max:255'];
            $rules['no_surat'] = ['required', 'string', 'max:255', 'unique:permit_letters,no_surat'];
            $rules['kategori_permit_letter'] = ['required', 'string', 'in:ops,dtm,dtu,dkk'];
            $rules['nama_pt'] = ['required', 'string', 'max:255'];
            $rules['tanggal'] = ['required', 'date', 'date_format:d-m-Y'];
            $rules['dokumen'] = ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'uraian.required' => 'The uraian field is required.',
            'no_surat.required' => 'The no surat field is required.',
            'kategori_permit_letter.required' => 'The kategori permit letter field is required.',
            'nama_pt.required' => 'The nama pt field is required.',
            'tanggal.required' => 'The tanggal field is required.',
            'tanggal.date_format' => 'The tanggal must be in the format dd-mm-yyyy.',
            'produk_no_surat_mabes.required' => 'The produk no surat mabes field is required.',
            'dokumen.mimes' => 'The dokumen must be a file of type: pdf, doc, docx, jpeg, png.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            'errors' => $validator->errors(),
        ], 400));
    }
}
