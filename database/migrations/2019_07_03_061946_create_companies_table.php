<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");

            $table->unsignedBigInteger('industry_id')->comment("refers to industries table");

            $table->char('code', 6)->unique()->comment("unique code of the company which should of 6 characters");
            $table->string('name', 255)->comment("name of the company");
            $table->text('description')->nullable()->comment("description of the company");
            $table->string('size', 255)->comment("size of the company");
            $table->dateTime('subscription_start_date')->nullable()->comment("subscription start date for the company");
            $table->dateTime('subscription_end_date')->nullable()->comment("subscription end date for the company");
            $table->boolean('has_domain')->default(0)->comment('1 if company has domains binded with it else 0');
            $table->boolean('status')->default(0)->comment('1 if subscription is not finished else 0');
            
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('industry_id')
                ->references('id')->on('industries')
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
        Schema::dropIfExists('companies');
        Schema::enableForeignKeyConstraints();
    }
}
