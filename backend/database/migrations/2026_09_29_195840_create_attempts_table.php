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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('exam_id')->constrained()->restrictOnDelete();
            $table->smallInteger('correct_count');
            $table->smallInteger('total_questions');
            $table->decimal('score', 8, 2);
            $table->decimal('percentage', 5, 2);
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['student_id', 'exam_id']);
            $table->index(['exam_id', 'score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
