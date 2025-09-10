<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtInUserExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_exercise', function (Blueprint $table) {
            $table->dateTime('deleted_at')->nullable()->after('created_at')->comment('if record is deleted it will not be null');
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
        if (Schema::hasTable('user_exercise')) {
            Schema::table('user_exercise', function (Blueprint $table) {
                if (Schema::hasColumn('user_exercise', 'deleted_at')) {
                    $table->dropColumn('deleted_at');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
