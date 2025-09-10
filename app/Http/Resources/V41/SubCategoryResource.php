<?php

namespace App\Http\Resources\V41;

use App\Models\SubCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $return = [
            'id'   => $this->id,
            'name' => $this->name,
            'slug' => $this->short_name,
        ];

        $logoW = 256;
        $logoH = 256;
        if ($this->id != 0 && $this->id != -1) {
            $category = SubCategory::select('categories.short_name as categoryShortName')->
                leftJoin('categories', 'categories.id', '=', 'sub_categories.category_id')->where('sub_categories.id', $this->id)
                ->first();
            if ($category->categoryShortName == 'course' || $category->categoryShortName == 'webinar' || $category->categoryShortName == 'feed' || $category->categoryShortName == 'meditation' || $category->categoryShortName == 'podcast' || $category->categoryShortName == 'shorts') {
                $return['logo']       = $this->getMediaData('logo', ['w' => $logoW, 'h' => $logoH, 'zc' => 3]);
                $return['background'] = $this->getMediaData('background', ['w' => 320, 'h' => 320, 'zc' => 3]);
            }
        } elseif ($this->id == 0) {
            $return['logo'] = [
                "width"  => $logoW,
                "height" => $logoH,
                "url"    => config('zevolifesettings.meditation_images.icons.favorite'),
            ];
            $return['background'] = array('width' => 320, 'height' => 320, "url" => config('zevolifesettings.fallback_image_url.sub_category.favorite'));
        } elseif ($this->id == -1) {
            $return['logo'] = [
                "width"  => $logoW,
                "height" => $logoH,
                "url"    => config('zevolifesettings.meditation_images.icons.view_all'),
            ];
            $return['background'] = array('width' => 320, 'height' => 320, "url" => config('zevolifesettings.fallback_image_url.sub_category.view_all'));
        }

        return $return;
    }
}
