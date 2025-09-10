<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_features', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('parent_id')->unsigned()->index('parent_id')->nullable();
            $table->string('name', 191)->unique()->comment('title of feature');
            $table->string('slug', 191)->comment('slug for features');
            $table->tinyInteger('manage')->default(1)->comment('1 => Individual, 2 => Group');
            $table->boolean('status')->default(1)->comment('1 => Active, 0 => Inactive current status');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cp_features');
    }
}
