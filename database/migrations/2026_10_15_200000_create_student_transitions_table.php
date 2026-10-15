<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('transition_type', 32); // withdrawal, graduation, repeat, readmission
            $table->foreignId('from_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('to_academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->foreignId('from_grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('to_grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->foreignId('from_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('to_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->date('transition_date');
            $table->string('reason', 255)->nullable();
            $table->string('certificate_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['branch_id', 'transition_type']);
            $table->index(['student_id', 'transition_type']);
            $table->index('transition_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_transitions');
    }
};
