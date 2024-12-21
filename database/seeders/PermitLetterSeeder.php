<?php

namespace Database\Seeders;

use App\Models\PermitLetters;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermitLetterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PermitLetters::create([
            'uraian' => 'Angkut Subang Bati',
            'no_surat' => 'B/008/I/2023/Korp-Jkt',
            'kategori' => 'OPS',
            'nama_pt' => 'PT Dahana',
            'tanggal_masuk_berkas' => '28 Februari 2023',
            'no_produk_mabes' => 'SI/1128/I/YAN.2.10./2023',
            'status_tahapan' => 'terverifikasi',
        ]);

        PermitLetters::create([
            'uraian' => 'Angkut Subang Bati',
            'no_surat' => 'B/008/I/2023/Korp-Jkt',
            'kategori' => 'OPS',
            'nama_pt' => 'PT Dahana',
            'tanggal_masuk_berkas' => '28 Februari 2023',
            'no_produk_mabes' => 'SI/1128/I/YAN.2.10./2023',
            'status_tahapan' => 'terverifikasi',
        ]);

        PermitLetters::create([
            'uraian' => 'Angkut Subang Bati',
            'no_surat' => 'B/008/I/2023/Korp-Jkt',
            'kategori' => 'OPS',
            'nama_pt' => 'PT Dahana',
            'tanggal_masuk_berkas' => '28 Februari 2023',
            'no_produk_mabes' => 'SI/1128/I/YAN.2.10./2023',
            'status_tahapan' => 'terverifikasi',
        ]);
    }
}
