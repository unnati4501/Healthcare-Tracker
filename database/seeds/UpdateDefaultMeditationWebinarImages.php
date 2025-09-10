<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MeditationTrack;
use App\Models\Webinar;

class UpdateDefaultMeditationWebinarImages extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Upload default image for meditations
        $trackData = MeditationTrack::all();
        foreach ($trackData as $value) {
            $headerImage = config('zevolifesettings.fallback_image_url.meditation_tracks.default_image');
            if (!empty($value)) {
                $name = $value->id . '_' . \time();
                $value->clearMediaCollection('header_image')
                ->addMediaFromUrl($headerImage)
                ->usingName($name)
                ->toMediaCollection('header_image', config('medialibrary.disk_name'));
            }
        }
        // Upload default image for webinars
        $webinarData = Webinar::all();
        foreach ($webinarData as $value) {
            $headerImage = config('zevolifesettings.fallback_image_url.webinar.default_image');
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
