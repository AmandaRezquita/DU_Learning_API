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
        Schema::create('days', function (Blueprint $table) {
            $table->id();
            $table->string("day");
        });

        DB::table('days')->insert([
            ['day' => 'senin'], 
            ['day' => 'selasa'], 
            ['day' => 'rabu'], 
            ['day' => 'kamis'], 
            ['day' => 'jumat'], 
            ['day' => 'sabtu'], 
            ['day' => 'minggu'], 
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('days');
    }
};
