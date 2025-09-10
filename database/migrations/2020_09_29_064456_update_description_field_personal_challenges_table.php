<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDescriptionFieldPersonalChallengesTable extends Migration
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
        Schema::table('personal_challenges', function (Blueprint $table) {
            $table->text('description')->nullable()->after('type')->comment("description for the personal challenge")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_challenges', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->after('type')->comment("description for the personal challenge")->change();
        });
    }
}
