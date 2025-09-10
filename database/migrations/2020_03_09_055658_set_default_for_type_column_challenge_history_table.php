<?php

use Illuminate\Database\Migrations\Migration;

class SetDefaultForTypeColumnChallengeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE challenge_history ALTER challenge_type SET DEFAULT 'individual'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE challenge_history ALTER challenge_type SET DEFAULT null");
    }
}
