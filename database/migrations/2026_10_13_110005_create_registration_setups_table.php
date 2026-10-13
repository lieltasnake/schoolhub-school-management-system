<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('application_fee', 10, 2)->default(0.00);
            $table->unsignedInteger('capacity')->nullable();
            $table->enum('status', ['open', 'closed', 'upcoming'])->default('open');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_setups');
    }
};
