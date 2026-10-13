<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('to_academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('from_grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('to_grade_id')->constrained('grades')->cascadeOnDelete();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('promoted_count')->default(0);
            $table->unsignedInteger('repeated_count')->default(0);
            $table->unsignedInteger('graduated_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_promotions');
    }
};
