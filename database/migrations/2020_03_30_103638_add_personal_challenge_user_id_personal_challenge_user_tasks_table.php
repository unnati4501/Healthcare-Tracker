<?php

use App\Http\Traits\DisableForeignKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPersonalChallengeUserIdPersonalChallengeUserTasksTable extends Migration
{
    use DisableForeignKeys;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->dropForeign('personal_challenge_user_tasks_user_id_foreign');
            $table->dropColumn('user_id');
        });

        Schema::table('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_challenge_user_id')->after('personal_challenge_id')->comment("refers to personal challenge users table");
            $table->foreign('personal_challenge_user_id')
                ->references('id')
                ->on('personal_challenge_users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Disable foreign key checks!
        $this->disableForeignKeys();

        Schema::table('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('personal_challenge_id')->comment("refers to users table");
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        Schema::table('personal_challenge_user_tasks', function (Blueprint $table) {
            $table->dropForeign('personal_challenge_user_tasks_personal_challenge_user_id_foreign');
            $table->dropColumn('personal_challenge_user_id');
        });

        // Enable foreign key checks!
        $this->enableForeignKeys();
    }
}
