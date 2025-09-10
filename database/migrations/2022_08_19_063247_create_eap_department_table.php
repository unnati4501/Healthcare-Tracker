<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEapDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eap_department', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('eap_id')->nullable()->comment("refers to eap_list table");
            $table->integer('location_id')->comment("refers to company department_location table");
            $table->unsignedBigInteger('department_id')->comment("refers to departments table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('eap_id')
                ->references('id')->on('eap_list')
                ->onDelete('cascade');
            $table->foreign('department_id')
                ->references('id')->on('departments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eap_department');
    }
}
