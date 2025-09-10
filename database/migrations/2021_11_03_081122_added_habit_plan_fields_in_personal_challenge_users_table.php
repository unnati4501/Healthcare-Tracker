<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedHabitPlanFieldsInPersonalChallengeUsersTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_challenge_users', function (Blueprint $table) {
            $table->enum('frequency_type', ['daily', 'hourly'])->default('daily')->comment('Set frequency type based on personal challenge plan')->after('reminder_at');
            $table->time('from_time')->nullable()->comment('from time use for habit plan with hourly frequency type')->after('frequency_type');
            $table->time('to_time')->nullable()->comment('to time use for habit plan with hourly frequency type')->after('from_time');
            $table->string('in_every', 255)->nullable()->comment('Use reminder in second for habit plan with hourly frequency type')->after('to_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenge_users', function (Blueprint $table) {
            $table->dropColumn('frequency_type');
            $table->dropColumn('from_time');
            $table->dropColumn('to_time');
            $table->dropColumn('in_every');
        });
    }
}
