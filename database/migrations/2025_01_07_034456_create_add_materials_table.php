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
        Schema::create('add_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('subject_id')->constrained('class_subjects');
            $table->string('title');
            $table->text('description');
            $table->string('date');
            $table->string('file')->nullable();
            $table->string('link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_materials');
    }
};
