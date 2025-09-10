<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebinarCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webinar_category', function (Blueprint $table) {
            $table->bigIncrements('id')->comment("primary key of the current table");
            $table->unsignedBigInteger('webinar_id')->nullable()->comment("refers to webinar table");
            $table->unsignedBigInteger('category_id')->default(7)->comment("refers to categories table");
            $table->unsignedBigInteger('sub_category_id')->default(7)->comment("refers to sub categories table");
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");

            $table->foreign('webinar_id')
                ->references('id')->on('webinar')
                ->onDelete('cascade');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
            $table->foreign('sub_category_id')
                ->references('id')
                ->on('sub_categories')
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
        Schema::dropIfExists('webinar_category');
        Schema::enableForeignKeyConstraints();
    }
}
