<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoReminderColumnChallengeWiseUserLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_wise_user_log', function (Blueprint $table) {
            $table->timestamp('start_remindered_at')->nullable()->after('finished_at')->comment('represents the date time when challenge start reminder send to user.');
            $table->timestamp('end_remindered_at')->nullable()->after('start_remindered_at')->comment('represents the date time when challenge end reminder send to user.');
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
        if (Schema::hasTable('challenge_wise_user_log')) {
            Schema::table('challenge_wise_user_log', function (Blueprint $table) {
                if (Schema::hasColumn('challenge_wise_user_log', 'is_disqualified')) {
                    $table->dropColumn('is_disqualified');
                }
                if (Schema::hasColumn('challenge_wise_user_log', 'disqualified_at')) {
                    $table->dropColumn('disqualified_at');
                }
                if (Schema::hasColumn('challenge_wise_user_log', 'start_remindered_at')) {
                    $table->dropColumn('start_remindered_at');
                }
                if (Schema::hasColumn('challenge_wise_user_log', 'end_remindered_at')) {
                    $table->dropColumn('end_remindered_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
