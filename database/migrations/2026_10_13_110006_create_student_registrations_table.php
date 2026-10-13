<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_setup_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_type_id')->nullable()->constrained('student_types')->nullOnDelete();
            $table->string('registration_number')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth');
            $table->date('registration_date');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('previous_school')->nullable();
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->enum('status', ['pending', 'approved', 'enrolled', 'rejected'])->default('pending');
            $table->boolean('application_fee_paid')->default(false);
            $table->foreignId('converted_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_registrations');
    }
};
