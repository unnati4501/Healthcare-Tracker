<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultFlagToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('default')->default(0)->after('group')->comment("if role is default - inserted from system admin/database seeder");
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
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                if (Schema::hasColumn('roles', 'default')) {
                    $table->dropColumn('default');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
