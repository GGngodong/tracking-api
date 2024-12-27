<?php

namespace Database\Seeders;

use App\Models\PermitLetters;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PermitLetterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $uniqueNoSurat = 'B/008/I/2023/Korp-Jkt' . now()->timestamp;
        $uniqueNoProdukMabes = 'SI/1128/I/YAN.2.10./2023' . now()->timestamp;

        PermitLetters::create([
            'uraian' => 'Angkut Subang Bati',
            'no_surat' => $uniqueNoSurat,
            'kategori_permit_letter' => 'OPS',
            'nama_pt' => 'PT Dahana',
            'tanggal' => '2023-02-28',
            'produk_no_surat_mabes' => $uniqueNoProdukMabes,
//            'status_tahapan' => 'terverifikasi',
            'dokumen' => File::get('public/dummy.pdf'),
        ]);

        $uniqueNoProdukMabes2 = 'SI/381/I/YAN.2.12./2024' . now()->timestamp;
        $uniqueNoSurat2 = '306/PHR85200/2023-SOP' . now()->timestamp;
        PermitLetters::create([
            'uraian' => 'P1 6518',
            'no_surat' => $uniqueNoSurat2,
            'kategori_permit_letter' => 'DTM',
            'nama_pt' => 'PH ROKAN',
            'tanggal' => '2024-04-01',
            'produk_no_surat_mabes' => $uniqueNoProdukMabes2,
//            'status_tahapan' => 'terverifikasi',
            'dokumen' => File::get('public/dummy.pdf'),
        ]);

        $uniqueNoProdukMabes3 = 'SI/230/I/YAN.2.12./2024' . now()->timestamp;
        $uniqueNoSurat3 = '139/256/OPS-PT.PDB' . now()->timestamp;
        PermitLetters::create([
            'uraian' => 'P2 6797',
            'no_surat' => $uniqueNoSurat3,
            'kategori_permit_letter' => 'DTU',
            'nama_pt' => 'BARAMARTA',
            'tanggal' => '2023-02-28',
            'produk_no_surat_mabes' => $uniqueNoProdukMabes3,
//            'status_tahapan' => 'terverifikasi',
            'dokumen' => File::get('public/dummy.pdf'),
        ]);

    }
}
