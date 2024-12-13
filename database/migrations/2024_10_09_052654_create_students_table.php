<?php

use App\Models\Role;
use App\Models\Student\Auth\StudentAvatar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string("fullname");
            $table->string("nickname");
            $table->string("birth_date");
            $table->string("student_number");
            $table->foreignId('gender_id')->constrained('student_genders');
            $table->string("phone_number");
            $table->string("email")->unique();
            $table->string("username");
            $table->string("password");
            $table->foreignId('student_image_id')->nullable()->constrained('student_images');;
            $table->foreignId('role_id')->constrained('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
