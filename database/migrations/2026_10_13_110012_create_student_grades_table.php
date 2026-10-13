<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 6, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('letter_grade')->nullable();
            $table->decimal('grade_point', 4, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'exempt'])->default('present');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_grades');
    }
};
