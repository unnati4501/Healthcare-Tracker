<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoveFieldsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('step_last_sync_date_time')->nullable()->after('last_login_at')->comment('it is last date time step synced for user');

            $table->dateTime('exercise_last_sync_date_time')->nullable()->after('last_login_at')->comment('it is last date time exercise synced for user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'step_last_sync_date_time')) {
                    $table->dropColumn('step_last_sync_date_time');
                }
                if (Schema::hasColumn('users', 'exercise_last_sync_date_time')) {
                    $table->dropColumn('exercise_last_sync_date_time');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
