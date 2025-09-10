<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class UpdateCompanySizeDropdownSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = Company::Where('size', '500+')->get();
        foreach ($company as $value) {
            $value->size = '501-1000';
            $value->update();
        }
    }
}
