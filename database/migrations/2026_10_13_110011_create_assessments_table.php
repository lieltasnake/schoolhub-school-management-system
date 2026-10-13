<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assessment_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_scale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('subject_name');
            $table->decimal('max_score', 6, 2)->default(100.00);
            $table->decimal('pass_mark', 6, 2)->default(50.00);
            $table->decimal('weight_percentage', 5, 2)->default(100.00);
            $table->date('assessment_date')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
