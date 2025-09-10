<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModelIdFieldInBadgeUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->integer('model_id')->nullable()->after('status')->comment("model id to get extra fields like exercise id");
            $table->string('model_name', 255)->nullable()->after('status')->comment("model name to get extra fields like exercise");
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
        if (Schema::hasTable('badge_user')) {
            Schema::table('badge_user', function (Blueprint $table) {
                if (Schema::hasColumn('badge_user', 'model_id')) {
                    $table->dropColumn('model_id');
                }
                if (Schema::hasColumn('badge_user', 'model_name')) {
                    $table->dropColumn('model_name');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
