<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V14;

use App\Http\Controllers\API\V13\RecipeController as v13RecipeController;
use App\Http\Resources\V7\RecipeDetailResource as v7RecipeDetailResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeController extends v13RecipeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get recipe details by id
     *
     * @param Request $request, Recipe $recipe
     * @return \Illuminate\Http\JsonResponse
     */
    public function details(Request $request, Recipe $recipe)
    {
        try {
            $user          = $this->user();
            $role          = getUserRole($user);
            $company       = $user->company()->first();
            $subcategories = $recipe->recipesubcategories()->where('status', 1)->count();

            if ($subcategories == 0) {
                return $this->notFoundResponse('Recipe not found');
            }

            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));

            if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
                $companyId = $company->id;
                // Check recipe available with this company or not
                $checkRecipe = $recipe->recipecompany()->where('company_id', $company->id)->count();

                if ($checkRecipe <= 0) {
                    return $this->notFoundResponse('Recipe not found');
                }
            }

            if ($recipe->status == 0) {
                if ($role->slug == 'user' && $role->default == '1' && $recipe->creator_id != $user->getKey()) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                } elseif ($role->group == 'company' && $recipe->company_id != $user->company()->first()->id) {
                    return $this->notFoundResponse('Recipe is pending for approval by Zevo account manager');
                }
            } else {
                if (!is_null($company)) {
                    $recipe->rewardPortalPointsToUser($user, $company, 'recipe');
                }
            }

            return $this->successResponse(['data' => new v7RecipeDetailResource($recipe)], 'Recipe detail retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
