<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNpsProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nps_project', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('creator_id')->nullable()->comment("refers to users table- creator of the nps project");
            $table->unsignedBigInteger('company_id')->comment("refers to companies table");

            $table->date('start_date')->nullable()->comment("start_date of project survey");
            $table->date('end_date')->nullable()->comment("end_date of project survey");
            $table->string('title', 255)->comment('nps project title');
            $table->enum('type', ['public', 'system'])->nullable()->comment("project survey type (public, system ) ");

            $table->boolean('survey_sent')->default(0)->comment('0 => Survey not sent, 1 => survey sent to user');

            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive survey');

            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('creator_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')->on('companies')
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('nps_project');
        Schema::enableForeignKeyConstraints();
    }
}
