<?php
declare (strict_types = 1);
namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

/**
 * Class IndustriesSeeder
 */
class IndustriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $industries = \json_decode(
            \file_get_contents(
                __DIR__ . '/data/industries.json'
            ),
            true
        );

        foreach ($industries as $industry) {
            Industry::create([
                'name' => $industry,
            ]);
        }
    }
}
