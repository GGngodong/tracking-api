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
        Schema::table('permit_letters', function (Blueprint $table) {
            $table->string('sub_kategori_permit_letter', '50')->after('kategori_permit_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permit_letters', function (Blueprint $table) {
            //
        });
    }
};
