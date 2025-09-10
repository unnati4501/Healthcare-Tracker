<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;

class UpdateDefaultMasterClassImages extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Upload default image for masterclass
        $courseData = Course::all();
        foreach ($courseData as $value) {
            $headerImage = config('zevolifesettings.fallback_image_url.course.default_image');
            if (!empty($value)) {
                $name = $value->id . '_' . \time();
                $value->clearMediaCollection('header_image')
                ->addMediaFromUrl($headerImage)
                ->usingName($name)
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
            }
        }
    }
}
