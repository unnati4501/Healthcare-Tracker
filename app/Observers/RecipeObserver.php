<?php declare (strict_types = 1);

namespace App\Observers;

use App\Models\Notification;
use App\Models\Recipe;

/**
 * Class RecipeObserver
 *
 * @package App\Observers
 */
class RecipeObserver
{
    /**
     * @param Recipe $recipe
     */
    public function created(Recipe $recipe)
    {
        $deepLinkURI = "zevolife://zevo/recipe/" . $recipe->getKey();
        $recipe->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Recipe $recipe
     */
    public function deleted(Recipe $recipe)
    {
        $deepLinkURI = "zevolife://zevo/recipe/" . $recipe->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}
