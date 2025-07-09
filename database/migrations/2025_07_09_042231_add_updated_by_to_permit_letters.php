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
            $table->unsignedBigInteger('updated_by')->nullable()->after('user_id')->comment('Who last edited this permit');
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
