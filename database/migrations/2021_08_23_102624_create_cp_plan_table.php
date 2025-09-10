<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCpPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cp_plan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->comment("name of Company plan - display plan name");
            $table->string('slug', 255)->comment("slug of plan");
            $table->string('description', 255)->comment("description for the plan");
            $table->tinyInteger('group')->default(1)->comment('1 => Company, 2 => Reseller');
            $table->boolean('default')->default(0)->comment('1 => Default ( Added by system ), 0 => Added By Users');
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
        Schema::dropIfExists('cp_plan');
    }
}
