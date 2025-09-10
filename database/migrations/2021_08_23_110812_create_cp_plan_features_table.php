<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpPlanFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_plan_features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_id')->comment("refers to company plan table");
            $table->unsignedBigInteger('feature_id')->comment("refers to company features table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table->foreign('plan_id')
                ->references('id')->on('cp_plan')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('feature_id')
                ->references('id')->on('cp_features')
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
        Schema::dropIfExists('cp_plan_features');
        Schema::enableForeignKeyConstraints();
    }
}
