<?php
namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class UpdateExistingMeditationSubcategoriesIcons extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $serviceSubCategories    = SubCategory::where('category_id', 4)->select('id', 'name', 'short_name')->get();
        $meditationSubCategories = config('zevolifesettings.meditation_images.icons');

        foreach ($serviceSubCategories as $categories) {
            $path        = (in_array($categories['short_name'], $meditationSubCategories)) ? $meditationSubCategories[$categories['short_name']] : $meditationSubCategories['move'];
            $data        = file_get_contents($path);
            $base64Image = 'data:image/jpeg;base64,' . base64_encode($data);
            $name        = $categories->id . '_' . \time();
            $categories->clearMediaCollection('logo')
                ->addMediaFromBase64($base64Image)
                ->usingName($name)
                ->toMediaCollection('logo', config('medialibrary.disk_name'));
        }
    }
}
