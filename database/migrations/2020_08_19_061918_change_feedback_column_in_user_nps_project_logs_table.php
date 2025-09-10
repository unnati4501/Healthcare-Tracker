<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFeedbackColumnInUserNpsProjectLogsTable extends Migration
{
    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_nps_project_logs', function (Blueprint $table) {
            $table->text('feedback')->nullable()->comment('project survey feedback given by user')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_nps_project_logs', function (Blueprint $table) {
            $table->string('feedback')->nullable()->comment('project survey feedback given by user')->change();
        });
    }
}
