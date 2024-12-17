<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permit_letters', function (Blueprint $table) {
            $table->id();
            $table->string('uraian', 100)->nullable(false);
            $table->string('no_surat', 100)->nullable(false)->unique('no_surat_permit_letter_unique');
            $table->date('tanggal')->nullable(false);
            $table->enum('kategori_permit_letter',['OPS', 'DTU', 'DTM', 'DKK'])->nullable(false);
            $table->string('nama_pt',100)->nullable(false);
            $table->string('produk_no_surat_mabes', 100)->nullable()->unique('produk_no_surat_mabes_unique');
            $table->binary('dokumen')->nullable(false);
            $table->string('dokumen_hash', 64)->nullable(false);
            $table->timestamps();
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_letters');
    }
};
