<?php
namespace Database\Seeders;

use App\Models\MeditationCategory;
use App\Models\ZevoMeditationCategory;
use App\Models\ZevoMeditationTrack;
use Illuminate\Database\Seeder;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\UnreachableUrl;

class InspireMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // Connect to ZevoHealth's database
            $inspireAlbums = ZevoMeditationCategory::orderBy('id', 'DESC')->get();

            // Get table data from zevohealth tables
            $inspireCategories = [];
            foreach ($inspireAlbums as $c => $inspireAlbum) {
                //fetch all tracks by inspire category id
                $zevoTracks = ZevoMeditationTrack::where('inspire_album_id', $inspireAlbum->id)->orderBy('id', 'DESC')->get();

                if ($zevoTracks->count() > 0) {
                    $inspireCategories[$c]['title']      = $inspireAlbum->title;
                    $inspireCategories[$c]['cover_url']  = $inspireAlbum->logo;
                    $inspireCategories[$c]['cover_name'] = $inspireAlbum->logofile;

                    $inspireCategories[$c]['zevoTracks'] = [];
                    foreach ($zevoTracks as $zt => $zevoTrack) {
                        $inspireCategories[$c]['zevoTracks'][$zt]['title']      = $zevoTrack->title;
                        $inspireCategories[$c]['zevoTracks'][$zt]['duration']   = $zevoTrack->duration;
                        $inspireCategories[$c]['zevoTracks'][$zt]['cover_url']  = $zevoTrack->logo;
                        $inspireCategories[$c]['zevoTracks'][$zt]['cover_name'] = $zevoTrack->logofile;
                        $inspireCategories[$c]['zevoTracks'][$zt]['track_url']  = $zevoTrack->track;
                        $inspireCategories[$c]['zevoTracks'][$zt]['track_name'] = $zevoTrack->trackfile;
                    }
                }
            }

            \DB::beginTransaction();

            foreach ($inspireCategories as $inspireCategory) {
                // Save data to staging database - default db connection

                $category = MeditationCategory::create([
                    'title' => $inspireCategory['title'],
                ]);

                if (!empty($inspireCategory['cover_url'])) {
                    $name    = $category->id . '_' . \time();

                    try {
                        $category->clearMediaCollection('logo')
                            ->addMediaFromUrl($inspireCategory['cover_url'])
                            ->usingName($name)
                            ->usingFileName($name . '.' . $inspireCategory['cover_name'])
                            ->toMediaCollection('logo', 'spaces');
                    } catch (UnreachableUrl $exception) {
                        continue;
                    }
                }

                if (!empty($inspireCategory['zevoTracks']) && count($inspireCategory['zevoTracks'])) {
                    foreach ($inspireCategory['zevoTracks'] as $zevoTrack) {
                        $inputData = [
                            'title'      => $zevoTrack['title'],
                            'duration'   => $zevoTrack['duration'],
                            'title'      => $zevoTrack['title'],
                            'is_premium' => false,
                            'coach_id'   => 1,
                            'tag'        => 'inspire',

                        ];

                        $track = $category->tracks()->create($inputData);

                        try {
                            if (!empty($zevoTrack['cover_url'])) {
                                $name    = $track->id . '_' . \time();

                                $track->clearMediaCollection('cover')
                                    ->addMediaFromUrl($zevoTrack['cover_url'])
                                    ->usingName($name)
                                    ->usingFileName($name . '.' . $zevoTrack['cover_name'])
                                    ->toMediaCollection('cover', 'spaces');
                            }

                            if (!empty($zevoTrack['track_url'])) {
                                $name    = $track->id . '_' . \time();

                                $track->clearMediaCollection('track')
                                    ->addMediaFromUrl($zevoTrack['track_url'])
                                    ->usingName($name)
                                    ->usingFileName($name . '.' . $zevoTrack['track_name'])
                                    ->toMediaCollection('track', 'spaces');
                            }
                        } catch (UnreachableUrl $exception) {
                            continue;
                        }
                    }
                }
            }

            \DB::commit();

            //\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Illuminate\Database\QueryException $e) {
            \DB::rollback();
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
