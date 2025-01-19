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
        Schema::create('student_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('add_tasks')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('file');
            $table->enum('status', ['Belum Dikumpulkan', 'Dikumpulkan', 'Selesai', 'Kadaluarsa'])->default('Belum Dikumpulkan');
            $table->integer('score')->nullable();
            $table->string('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tasks');
    }
};
