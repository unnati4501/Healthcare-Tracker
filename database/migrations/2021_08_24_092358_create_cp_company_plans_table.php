<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpCompanyPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_company_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('plan_id')->comment("refers to company plan table");
            $table->unsignedBigInteger('company_id')->comment("refers to company features table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
            $table->foreign('plan_id')
                ->references('id')->on('cp_plan')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('company_id')
                ->references('id')->on('companies')
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
        Schema::dropIfExists('cp_company_plans');
        Schema::enableForeignKeyConstraints();
    }
}
