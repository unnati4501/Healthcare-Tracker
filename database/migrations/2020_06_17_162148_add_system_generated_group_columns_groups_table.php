<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSystemGeneratedGroupColumnsGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->integer('model_id')->index('model_id')->nullable()->after('sub_category_id')->comment("model id to get extra fields like challenge id");
            $table->string('model_name', 255)->nullable()->after('model_id')->comment("model name to get extra fields like challenge");
            $table->boolean('is_visible')->default(true)->after('description')->comment("default true, flag sets to false when group is not visible");
            $table->boolean('is_archived')->default(false)->after('is_visible')->comment("default false, flag sets to true when group is archived");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('model_id');
            $table->dropColumn('model_name');
            $table->dropColumn('is_visible');
            $table->dropColumn('is_archived');
        });
    }
}
