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
            $table->dropColumn('dokumen_hash');
        });
    }

        /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permit_letters', function (Blueprint $table) {
            $table->string('dokumen_hash', 64)->nullable(false)->default('default_hash_value');
        });
    }
};
