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
        Schema::create('teacher_images', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->foreignId('gender_id')->constrained('teacher_genders');
        });

        DB::table('teacher_images')->insert([
            ['image' => 'https://du.maxxplus.id/storage/teacher1.png', 'gender_id' => 1],
            ['image' => 'https://du.maxxplus.id/storage/teacher2.png', 'gender_id' => 2], 
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_images');
    }
};
