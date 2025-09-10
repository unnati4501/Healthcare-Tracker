<?php
namespace Database\Seeders;

use App\Models\Recipe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateExistingRecipeTeam extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $recipeRecords = Recipe::select('id')->get()->toArray();
        foreach ($recipeRecords as $value) {
            $checkRecords = DB::table('recipe_team')->where('recipe_id', $value['id'])->select('id')->count();

            if ($checkRecords <= 0) {
                $getCompanyRecords = DB::table('recipe_company')->join('team_location', 'team_location.company_id', '=', 'recipe_company.company_id')->where('recipe_company.recipe_id', $value['id'])->select('team_location.team_id')->get()->pluck('team_id')->toArray();

                $recipeTeam_input = [];

                foreach ($getCompanyRecords as $cValue) {
                    $recipeTeam_input[] = [
                        'recipe_id' => $value['id'],
                        'team_id'   => $cValue,
                    ];
                }
                DB::table('recipe_team')->insert($recipeTeam_input);
            }
        }
    }
}
