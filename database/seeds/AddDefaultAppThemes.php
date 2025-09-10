<?php
namespace Database\Seeders;

use App\Models\AppTheme;
use Illuminate\Database\Seeder;

class AddDefaultAppThemes extends Seeder
{
    /**
     * Run the database seeds to add default app themes.
     *
     * @return void
     */
    public function run()
    {
        try {
            \DB::beginTransaction();
            $disk      = config('medialibrary.disk_name');
            $doPath    = config("medialibrary.{$disk}.domain");
            $appThemes = config('zevolifesettings.app_theme_path', []);
            foreach ($appThemes as $themeKey => $path) {
                // create thenme
                $theme = AppTheme::create([
                    'slug' => str_replace(' ', '_', strtolower($themeKey)),
                    'name' => ucfirst($themeKey),
                ]);

                // upload JSON to storage
                $name = $theme->id . '_' . \time();
                $theme
                    ->clearMediaCollection('theme')
                    ->addMediaFromUrl("{$doPath}/{$path}", ['application/json', 'text/plain'])
                    ->usingName("{$theme->name}_theme.json")
                    ->usingFileName("{$name}.json")
                    ->toMediaCollection('theme', config('medialibrary.disk_name'));
            }
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollback();
            report($exception);
        }
    }
}
