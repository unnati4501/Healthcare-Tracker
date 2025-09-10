<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V30;

use App\Http\Controllers\API\V26\RecipeController as v26RecipeController;
use App\Models\Recipe;
use App\Models\RecipeType;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends v26RecipeController
{
    /**
     * Get recipe static data
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recipeStaticData(Request $request)
    {
        try {
            $user       = $this->user();
            $role       = getUserRole($user);
            $company    = $user->company()->select('companies.id', 'companies.parent_id')->first();
            $xDeviceOs  = strtolower($request->header('X-Device-Os', ""));
            $status     = (($xDeviceOs == config('zevolifesettings.PORTAL') && $company->parent_id == null && $role->group == 'reseller') ? 1 : 0);
            $nutritions = $subCategories = $recipeTypes = [];

            $favouritedCount = $user->recipeLogs()
                ->wherePivot('favourited', true)
                ->count('recipe.id');
            SubCategory::select('name', 'id')
                ->where('category_id', 5)
                ->where('status', 1)
                ->orderBy('is_excluded', 'DESC')
                ->get()
                ->each(function ($subCategory) use (&$subCategoriesRecords) {
                    if ($subCategory->recipes()->count('recipe_category.id') > 0) {
                        $subCategoriesRecords[] = [
                            'id'   => $subCategory->id,
                            'name' => $subCategory->name,
                        ];
                    }
                });

            if ($favouritedCount > 0) {
                array_unshift($subCategoriesRecords, [
                    'id'   => 0,
                    'name' => "My ⭐",
                ]);
            } else {
                $subCategoriesRecords[] = [
                    'id'   => 0,
                    'name' => "My ⭐",
                ];
            }

            RecipeType::select('type_name', 'id')
                ->where('status', '1')
                ->get()
                ->each(function ($type) use (&$recipeTypes) {
                    $recipeTypes[] = [
                        'id'   => $type->id,
                        'name' => $type->type_name,
                    ];
                });

            collect(config('zevolifesettings.nutritions'))
                ->each(function ($nutrition, $key) use (&$nutritions) {
                    $nutritions[] = [
                        'id'    => $key,
                        'title' => $nutrition['display_name'],
                    ];
                });

            $recipeFilterData = Recipe::select(
                \DB::raw("IFNULL(MAX(recipe.cooking_time), 0) AS max_cooking_time"),
                \DB::raw("IFNULL(MAX(recipe.calories), 0) AS max_calories"),
                \DB::raw("IFNULL(MAX(CAST(JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(nutritions, '$[3]'), '$.value')) AS DECIMAL(10, 1))), 0) AS max_protein")
            )
                ->join('recipe_company', function ($join) use ($company) {
                    $join
                        ->on('recipe_company.recipe_id', '=', 'recipe.id')
                        ->where('recipe_company.company_id', $company->id);
                })
                ->whereRaw('recipe.status = ? OR (recipe.creator_id = ? AND recipe.status = ?)', [1, $user->id, $status])
                ->first();

            $data = [
                'subcategories' => $subCategoriesRecords,
                'nutritions'    => $nutritions,
                'types'         => $recipeTypes,
                'cookingTime'   => [
                    'min'  => (int) 0,
                    'max'  => timeToSec($recipeFilterData->max_cooking_time),
                    'step' => timeToSec(config('zevolifesettings.recipe_filter_step.cookingTime') . ':00'),
                ],
                'calories'      => [
                    'min'  => (int) 0,
                    'max'  => (int) floor($recipeFilterData->max_calories),
                    'step' => config('zevolifesettings.recipe_filter_step.calories'),
                ],
                'protein'       => [
                    'min'  => (int) 0,
                    'max'  => (int) (empty($recipeFilterData->max_protein) ? 1 : floor($recipeFilterData->max_protein)),
                    'step' => config('zevolifesettings.recipe_filter_step.protein'),
                ],
            ];

            return $this->successResponse([
                'data' => $data,
            ], 'Recipe static data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('api_labels.common.something_wrong_try_again'));
        }
    }
}
