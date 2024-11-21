<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_avatars', function (Blueprint $table) {
            $table->id();
            $table->string('avatar');
        });

        DB::table('student_avatars')->insert([
            ['avatar' => 'storage/student1.png'], 
            ['avatar' => 'storage/student2.png'], 
        ]);
    }

    /** 
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_avatars');
    }
};
