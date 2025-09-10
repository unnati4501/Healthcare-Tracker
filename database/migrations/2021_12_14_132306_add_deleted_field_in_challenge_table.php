<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeletedFieldInChallengeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->unsignedBigInteger('deleted_by')->nullable()->after('deep_link_uri')->comment("stored user id which user can delete the challenge");
            $table->string('deleted_reason')->after('deleted_by')->comment("stored reason why user can delete the challenge");
            $table->foreign('deleted_by')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
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
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropForeign(['deleted_by']);
            $table->dropColumn('deleted_by');
            $table->dropColumn('deleted_reason');
        });
        Schema::enableForeignKeyConstraints();
    }
}
