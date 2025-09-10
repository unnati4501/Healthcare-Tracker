<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecurringCompletedColumnChallengeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->boolean('recurring_completed')->default(0)->after('recurring_type')->comment('1 if recurring challenge count is completed else 0');
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
                if (Schema::hasColumn('challenges', 'recurring_completed')) {
                    $table->dropColumn('recurring_completed');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
