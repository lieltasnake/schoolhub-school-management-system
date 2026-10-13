<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_scale_id')->constrained()->cascadeOnDelete();
            $table->string('letter_grade'); // A+, A, B+, B, C, D, F
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_point', 4, 2)->nullable(); // 4.0, 3.5, etc.
            $table->string('remarks')->nullable(); // Excellent, Good, Fail
            $table->boolean('is_pass')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scale_items');
    }
};
