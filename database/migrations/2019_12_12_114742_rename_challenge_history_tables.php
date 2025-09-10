<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameChallengeHistoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('challenge_inspire_history', 'freezed_challenge_inspire');
        Schema::rename('challenge_participents_history', 'freezed_challenge_participents');
        Schema::rename('challenge_settings_history', 'freezed_challenge_settings');
        Schema::rename('challenge_steps_history', 'freezed_challenge_steps');
        Schema::rename('challenge_exercise_history', 'freezed_challenge_exercise');

        Schema::dropIfExists('challenge_rules_history');
        Schema::dropIfExists('challenge_participant_status');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('freezed_challenge_inspire', 'challenge_inspire_history');
        Schema::rename('freezed_challenge_participents', 'challenge_participents_history');
        Schema::rename('freezed_challenge_settings', 'challenge_settings_history');
        Schema::rename('freezed_challenge_steps', 'challenge_steps_history');
        Schema::rename('freezed_challenge_exercise', 'challenge_exercise_history');
    }
}
