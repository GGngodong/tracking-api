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
        Schema::create('permit_letter_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permit_letter_id');
            $table->foreign('permit_letter_id')->references('id')->on('permit_letters')->onDelete('cascade');
            $table->string('status_tahapan');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_letter_logs');
    }
};
