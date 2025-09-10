<?php
namespace Database\Seeders;

use App\Models\Company;
use App\Models\Course;
use Illuminate\Database\Seeder;

class AttachOldMCToAllCompanies extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $comapnies           = Company::all()->pluck('id')->toArray();
        $courses             = Course::select('id')->get();

        if (!empty($comapnies) && !empty($courses)) {
            foreach ($courses as $course) {
                $course->masterclasscompany()->sync($comapnies);
            }
        }
    }
}
