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
        // Drop the foreign key constraint
        Schema::table('permit_letters', function (Blueprint $table) {
            // Replace 'permit_letters_user_id_foreign' with the actual foreign key name
            $table->dropForeign('permit_letters_user_id_foreign');
            $table->dropColumn('user_id'); // Drop the user_id column (optional)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permit_letters', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id'); // Define the column type
            $table->foreign('user_id')->references('id')->on('users'); // Re-create the foreign key
        });
    }
};
