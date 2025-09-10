<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipe;

class UpdateDefaultRecipeImages extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Upload default image for recipe
        $recipeData = Recipe::all();
        foreach ($recipeData as $value) {
            $headerImage = config('zevolifesettings.fallback_image_url.recipe.default_image');
            if (!empty($value)) {
                $name       = $value->id . '_' . \time();
                $extention  = pathinfo($headerImage, PATHINFO_EXTENSION);
                $value->clearMediaCollection('header_image')
                    ->addMediaFromUrl($headerImage)
                    ->usingName('default-recipe.png')
                    ->usingFileName($name.".".$extention)
                    ->toMediaCollection('header_image', config('medialibrary.disk_name'));
            }
        }
    }
}
