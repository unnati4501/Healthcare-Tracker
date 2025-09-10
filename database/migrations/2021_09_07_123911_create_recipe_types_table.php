<?php

use App\Models\RecipeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecipeTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type_name', 255)->comment('name of the recipe type');
            $table->string('slug', 255)->comment('slug of the recipe type name');
            $table->enum('status', ['0', '1'])->default('1')->comment('status of the recipe type');
            $table->timestamp('created_at')->useCurrent()->comment("date and time when record is created");
            $table->timestamp('updated_at')->useCurrent()->comment("date and time when record is updated");
        });

        // adding default data
        RecipeType::insert([
            ['type_name' => 'Vegan', 'slug' => str_replace(' ', '_', strtolower('Vegan'))],
            ['type_name' => 'Veg', 'slug' => str_replace(' ', '_', strtolower('Veg'))],
            ['type_name' => 'Non veg', 'slug' => str_replace(' ', '_', strtolower('Non veg'))],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipe_types');
    }
}
