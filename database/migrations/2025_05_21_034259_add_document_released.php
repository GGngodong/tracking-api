<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('permit_letters', function (Blueprint $table) {
            $table->string('released_dokumen')->nullable()->after('dokumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('permit_letters', function (Blueprint $table) {
            $table->dropColumn('released_dokumen');
        });
    }
};
