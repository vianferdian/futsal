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
        Schema::create('match_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            
            $table->integer('attendance')->nullable();
            $table->string('match_condition')->default('normal'); // normal, delayed, etc.
            
            $table->boolean('violation_potential')->default(false);
            $table->text('violation_notes')->nullable();
            $table->text('supervisor_notes')->nullable();
            
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            
            $table->timestamps();
            
            $table->unique('match_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_reports');
    }
};
