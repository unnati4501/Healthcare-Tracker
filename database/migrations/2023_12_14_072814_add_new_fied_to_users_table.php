<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFiedToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('reset_password_count')->default(0)->after('is_authenticate')->comment("count of send reset password email");
            $table->timestamp('password_reset_at')->nullable()->after('reset_password_count')->comment("date and time when password reset link sent");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reset_password_count');
            $table->dropColumn('password_reset_at');
        });
    }
}
