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
        Schema::create('principal__genders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        DB::table('principal__genders')->insert([
            ['name' => 'male'], 
            ['name' => 'female'], 
        ]);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('principal__genders');
    }
};
