<?php

namespace Tests\Feature;

use App\Models\PermitLetters;
use App\Models\User;
use Database\Seeders\PermitLetterSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;


class PermitLetterTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    public function testCreatePermitLetterSuccess()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $admin = User::where('role', 'ADMIN')->first();
        $this->assertNotNull($admin, 'Admin user does not exist in the database');
        $uniqueNoSurat = 'B/008/I/2023/Korp-Jkt';
        $uniqueNoProdukMabes = 'SI/1128/I/YAN.2.10./2023';
        $this->post('/api/dev/permit-letters/upload', [
            'uraian' => 'Angkut Subang Bati',
            'no_surat' => $uniqueNoSurat,
            'kategori_permit_letter' => 'ops',
            'nama_pt' => 'PT Dahana',
            'tanggal' => '28-02-2023',
            'produk_no_surat_mabes' => $uniqueNoProdukMabes,
            'dokumen' => UploadedFile::fake()->create('dummy.pdf', 100),
        ], [
            'Authorization' => 'Bearer ' . $admin->token,
        ])->assertStatus(201)
            ->assertJson([
                'data' => [
                    'uraian' => 'Angkut Subang Bati',
                    'no_surat' => $uniqueNoSurat,
                    'kategori_permit_letter' => 'OPS',
                    'nama_pt' => 'PT Dahana',
                    'tanggal' => '28-02-2023',
                    'produk_no_surat_mabes' => $uniqueNoProdukMabes,
                ]
            ]);
    }


    public function testCreatePermitLetterFail()
    {
        $this->seed([UserSeeder::class]);
        $admin = User::where('role', 'ADMIN')->first();
        $this->assertNotNull($admin, 'Admin user does not exist in the database');
        $this->assertNotNull($admin->token, 'Admin token is missing.');

        $response = $this->post('/api/dev/permit-letters/upload', [
            'uraian' => '',
            'no_surat' => '',
            'kategori_permit_letter' => '',
            'nama_pt' => '',
            'tanggal' => '',
            'produk_no_surat_mabes' => '',
            'dokumen' => '',
        ], [
            'Authorization' => 'Bearer ' . $admin->token,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);

        $responseJson = $response->json();

        $this->assertStringContainsString('field is required', $responseJson['message']);
        $this->assertArrayHasKey('errors', $responseJson);
    }


    public function testUpdatePermitLetterSuccess()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $admin = User::where('role', 'ADMIN')->first();
        $permit = PermitLetters::query()->limit(1)->first();
        $this->assertNotNull($admin, 'Admin user does not exist in the database');

        $this->put('/api/dev/permit-letters/' . $permit->id, [
            'uraian' => 'Updated Angkut Subang Bati',
            'no_surat' => 'Updated No Surat',
            'kategori_permit_letter' => 'ops',
            'nama_pt' => 'PT Dahana',
            'tanggal' => '28-02-2023',
            'produk_no_surat_mabes' => 'Updated Produk No Surat Mabes',
            'dokumen' => UploadedFile::fake()->create('new.pdf', 100),
        ], [
            'Authorization' => 'Bearer ' . $admin->token,
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'uraian' => 'Updated Angkut Subang Bati',
                    'no_surat' => 'Updated No Surat',
                    'kategori_permit_letter' => 'OPS',
                    'nama_pt' => 'PT Dahana',
                    'tanggal' => '28-02-2023',
                    'produk_no_surat_mabes' => 'Updated Produk No Surat Mabes',
                ]
            ]);
    }

    public function testDeletePermitLetterSuccess()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $admin = User::where('role', 'ADMIN')->first();
        $permit = PermitLetters::query()->limit(1)->first();

        $this->delete('/api/dev/permit-letters/' . $permit->id, [], [
            'Authorization' => 'Bearer ' . $admin->token,
        ])->assertStatus(200)
            ->assertJson([
                'message' => 'Permit Letter deleted successfully.'
            ]);
    }

    public function testGetAllPermitLetterUserSuccess()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $user = User::first();
        $this->get('/api/dev/permit-letters/', [
            'Authorization' => 'Bearer ' . $user->token,
        ])->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uraian',
                        'no_surat',
                        'kategori_permit_letter',
                        'nama_pt',
                        'tanggal',
                        'produk_no_surat_mabes',
                    ]
                ]
            ]);
    }

    public function testGetAllPermitLetterAdminSuccess()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $admin = User::where('role', 'ADMIN')->first();
        $this->assertNotNull($admin, 'Admin user does not exist in the database');
        $this->get('/api/dev/permit-letters/', [
            'Authorization' => 'Bearer ' . $admin->token,
        ])->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'uraian',
                        'no_surat',
                        'kategori_permit_letter',
                        'nama_pt',
                        'tanggal',
                        'produk_no_surat_mabes',
                    ]
                ]
            ]);
    }

    public function testGetPermitLetterById()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $user = User::first();
        $permit = PermitLetters::query()->limit(1)->first();

        $this->get('/api/dev/permit-letters/' . $permit->id, [
            'Authorization' => 'Bearer ' . $user->token,
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $permit->id,
                    'uraian' => 'Angkut Subang Bati',
                    'no_surat' => $permit->no_surat,
                    'kategori_permit_letter' => strtoupper($permit->kategori_permit_letter),
                    'nama_pt' => 'PT Dahana',
                    'tanggal' => '28-02-2023',
                    'produk_no_surat_mabes' => $permit->produk_no_surat_mabes,
                ]
            ]);
    }

    public function testGetPermitLetterNotFound()
    {
        $this->seed([UserSeeder::class, PermitLetterSeeder::class]);
        $user = User::first();
        $permit = PermitLetters::query()->latest('id')->first();
        $nonExistentId = $permit->id + 1;

        $this->get('/api/dev/permit-letters/' . $nonExistentId, [
            'Authorization' => 'Bearer ' . $user->token,
        ])->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => 'Permit Letter not found.'
                ]
            ]);
    }

}
