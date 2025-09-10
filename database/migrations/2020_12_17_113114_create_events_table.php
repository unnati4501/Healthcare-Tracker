<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('creator_id')->comment("refers to users table");
            $table->unsignedBigInteger('company_id')->nullable()->comment("refers to companies table");
            $table->unsignedBigInteger('category_id')->comment("refers to categories table");
            $table->unsignedBigInteger('subcategory_id')->comment("refers to sub_categories table");
            $table->string('name', 255)->comment("Name of event");
            $table->double('fees', 8, 0)->default(0)->comment("Fess of event");
            $table->text('description')->nullable()->comment("Description of event");
            $table->time('duration')->comment("Duration of event in HH:mm:ss format");
            $table->integer('capacity')->nullable()->comment("Capacity of event");
            $table->enum('status', [1, 2])->comment("1 => draft, 2 => published");
            $table->timestamp('published_on')->nullable()->comment("date and time when event is se as published");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when event is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when event is updated");

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('sub_categories')->onDelete('cascade');
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
        Schema::dropIfExists('events');
        Schema::enableForeignKeyConstraints();
    }
}
