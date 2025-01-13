<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PermitLetters>
 */
class PermitLetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uraian' => $this->faker->sentence,
            'no_surat' => $this->faker->unique(),
            'kategori' => $this->faker->randomElement(['ops','dtm','dtu','dkk']),
            'nama_pt' => $this->faker->company,
            'tanggal' => $this->faker->date('d-m-Y'),
            'produk_no_surat_mabes' => $this->faker->unique(),
            'status_tahapan' => $this->faker->randomElement(['terverifikasi','dalam_proses','selesai']),
        ];
    }
}
