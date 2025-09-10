<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UpdateSuperAdminUserName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            DB::statement("UPDATE `users` SET `first_name` = 'Zevo' WHERE `users`.`id` = 1;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('users')) {
            DB::statement("UPDATE `users` SET `first_name` = 'Super' WHERE `users`.`id` = 1;");
        }
    }
}
