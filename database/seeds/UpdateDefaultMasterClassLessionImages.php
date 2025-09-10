<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseLession;

class UpdateDefaultMasterClassLessionImages extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Upload default image for lessions
        $lessionData = CourseLession::all();
        foreach ($lessionData as $value) {
            $logo = config('zevolifesettings.fallback_image_url.course_lession.default');
            if (!empty($value)) {
                $name       = $value->id . '_' . \time();
                $extention  = pathinfo($logo, PATHINFO_EXTENSION);
                $value->clearMediaCollection('logo')
                    ->addMediaFromUrl($logo)
                    ->usingName('default-lession.png')
                    ->usingFileName($name.".".$extention)
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));
            }
        }
    }
}
