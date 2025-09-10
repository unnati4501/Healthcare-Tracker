<?php

use Illuminate\Database\Migrations\Migration;

class SetDefaultForTypeColumnChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE challenges ALTER challenge_type SET DEFAULT 'individual'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE challenges ALTER challenge_type SET DEFAULT null");
    }
}
