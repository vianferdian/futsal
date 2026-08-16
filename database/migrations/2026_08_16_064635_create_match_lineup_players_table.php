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
        Schema::create('match_lineup_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_lineup_id')->constrained('match_lineups')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->string('playing_status'); // playing, substitute, non_playing
            $table->string('position'); // goalkeeper, anchor, ala, pivot
            $table->boolean('is_goalkeeper')->default(false);
            $table->boolean('is_captain')->default(false);
            $table->timestamps();
            
            $table->unique(['match_lineup_id', 'player_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_lineup_players');
    }
};
