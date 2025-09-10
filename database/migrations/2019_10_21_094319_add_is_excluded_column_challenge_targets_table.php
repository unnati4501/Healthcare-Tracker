<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsExcludedColumnChallengeTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenge_targets', function (Blueprint $table) {
            $table->boolean('is_excluded')->default(0)->after('short_name')->comment('1 if target is excluded in challenge API response else 0');
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
        if (Schema::hasTable('challenge_targets')) {
            Schema::table('challenge_targets', function (Blueprint $table) {
                if (Schema::hasColumn('challenge_targets', 'is_excluded')) {
                    $table->dropColumn('is_excluded');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
