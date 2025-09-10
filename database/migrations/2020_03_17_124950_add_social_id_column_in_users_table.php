<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSocialIdColumnInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_id')->nullable()->after('hs_reminded_at')->comment('represents azure AD Id of authenticated user');
            $table->tinyInteger('social_type')->nullable()->after('social_id')->comment('1 -> AzureAd');
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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'social_id')) {
                    $table->dropColumn('social_id');
                }
                if (Schema::hasColumn('users', 'social_type')) {
                    $table->dropColumn('social_type');
                }
            });
        }
        Schema::enableForeignKeyConstraints();
    }
}
