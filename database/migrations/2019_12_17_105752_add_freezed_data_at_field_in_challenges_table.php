<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFreezedDataAtFieldInChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dateTime('freezed_data_at')->nullable()->after('challenge_end_at')->comment('date time when challenge data was collected last time.');
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
        if (Schema::hasTable('challenges')) {
            Schema::table('challenges', function (Blueprint $table) {
                if (Schema::hasColumn('challenges', 'freezed_data_at')) {
                    $table->dropColumn('freezed_data_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
