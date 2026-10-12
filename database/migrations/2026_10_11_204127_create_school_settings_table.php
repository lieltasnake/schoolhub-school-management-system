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
        Schema::create('school_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('school_name', 191)->nullable();
            $table->string('school_code', 50)->nullable();
            $table->string('official_email', 191)->nullable();
            $table->string('official_phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
