<?php

use App\Models\Principal\Auth\PrincipalAvatar;
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
        Schema::create('principals', function (Blueprint $table) {
            $table->id();
            $table->string("fullname");
            $table->string("nickname");
            $table->string("birth_date");
            $table->string("principal_number");
            $table->foreignId('gender_id')->constrained('principal__genders');
            $table->string("phone_number");
            $table->string("email")->unique();
            $table->string("username");
            $table->string("password");
            $table->foreignId('principal_image_id')->nullable()->constrained('principal__images');;
            $table->foreignId('role_id')->constrained('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('principals');
    }
};
