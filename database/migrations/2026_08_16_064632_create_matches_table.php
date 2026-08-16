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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->string('match_number');
            $table->string('round');
            $table->string('group_name')->nullable();
            
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            
            $table->date('match_date');
            $table->time('kickoff_time');
            
            $table->integer('first_half_duration')->default(20); // in minutes
            $table->integer('second_half_duration')->default(20); // in minutes
            
            $table->string('status')->default('draft'); // draft, waiting_lineup, etc.
            
            $table->integer('home_score')->default(0);
            $table->integer('away_score')->default(0);
            $table->integer('home_first_half_score')->default(0);
            $table->integer('away_first_half_score')->default(0);
            
            $table->string('current_period')->nullable(); // first_half, halftime, second_half, finished, etc.
            
            $table->string('timer_status')->default('not_started'); // not_started, running, paused, finished
            $table->timestamp('timer_started_at')->nullable();
            $table->timestamp('timer_paused_at')->nullable();
            $table->integer('elapsed_seconds')->default(0);
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['competition_id', 'match_number']);
            $table->index('match_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
