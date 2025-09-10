<?php
namespace Database\Seeders;

use App\Http\Traits\DisableForeignKeys;
use App\Http\Traits\TruncateTable;
use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            // Disable foreign key checks!
            $this->disableForeignKeys();

            // truncate table
            $this->truncate('services');

            $serviceData = [
                [
                    'name'              => 'Counselling',
                    'default'           => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ],
                [
                    'name'              => 'Coaching',
                    'default'           => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]
            ];
            //Insert data into service table
            Service::insert($serviceData);

            $services        = Service::all()->chunk(500);
            $services->each(function ($serviceChunk) {
                $serviceChunk->each(function ($service) {

                    //Insert logos and icons for coaching and counselling services
                    $name = $service->id . '_' . \time();
                    if ($service['name'] == 'Counselling') {
                        $logo = config('zevolifesettings.fallback_image_url.services.counselling.logo');
                        $icon = config('zevolifesettings.fallback_image_url.services.counselling.icon');
                    } else {
                        $logo = config('zevolifesettings.fallback_image_url.services.coaching.logo');
                        $icon = config('zevolifesettings.fallback_image_url.services.coaching.icon');
                    }
                    $service->clearMediaCollection('logo')
                    ->addMediaFromUrl($logo)
                    ->usingName($name)
                    ->toMediaCollection('logo', config('medialibrary.disk_name'));

                    //Insert icons for coaching and counselling services
                    $service->clearMediaCollection('icon')
                    ->addMediaFromUrl($icon)
                    ->usingName($name)
                    ->toMediaCollection('icon', config('medialibrary.disk_name'));


                    //Insert default subcategories with default logo
                    $subcategory = $service->subcategories()->create([
                        'name'      => 'Default',
                        'default'   => true,
                    ]);

                    $name = $subcategory->id . '_' . \time();
                    if ($service['name'] == 'Counselling') {
                        $logo = config('zevolifesettings.fallback_image_url.services.subcategories.counselling');
                    } else {
                        $logo = config('zevolifesettings.fallback_image_url.services.subcategories.coaching');
                    }
                    $subcategory->clearMediaCollection('sub_category_logo')
                    ->addMediaFromUrl($logo)
                    ->usingName($name)
                    ->toMediaCollection('sub_category_logo', config('medialibrary.disk_name'));
                });
            });

            // Enable foreign key checks!
            $this->enableForeignKeys();
        } catch (\Illuminate\Database\QueryException $e) {
            $this->command->error("SQL Error: " . $e->getMessage() . "\n");
        }
    }
}
