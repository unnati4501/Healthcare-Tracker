<?php

namespace App\Http\Collections\V31;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SavedContentImagesCollection extends ResourceCollection
{
    public function __construct($contentData)
    {
        $this->contentData              =  $contentData;
        $this->meditation               =  $contentData['meditation'];
        $this->feed                     =  $contentData['feed'];
        $this->recipe                   =  $contentData['recipe'];
        $this->masterclass              =  $contentData['masterclass'];
        $this->webinar                  =  $contentData['webinar'];
        parent::__construct($contentData);
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
       
        $contentImages = [];
        if (!empty($this->meditation)) {
            foreach ($this->meditation as $meditationKey => $meditationVal) {
                $meditationImage[] = $meditationVal->getMediaData('cover', ['w' => 640, 'h' => 1280, 'zc' => 3]);
            }
            if (!empty($meditationImage)) {
                $meditationArray = [
                    'category'  => 'meditation',
                    'count'     => count($this->meditation),
                    'images'    => array_slice($meditationImage, 0, 3),

                ];
                array_push($contentImages, $meditationArray);
            }
        }

        if (!empty($this->feed)) {
            foreach ($this->feed as $feedKey => $feedVal) {
                $feedImage[] = $feedVal->getMediaData('featured_image', ['w' => 640, 'h' => 1280, 'zc' => 3]);
            }
            if (!empty($feedImage)) {
                $feedArray     = [
                    'category' => 'Stories',
                    'count'    => count($this->feed),
                    'images'   => array_slice($feedImage, 0, 3),

                ];
                array_push($contentImages, $feedArray);
            }
        }
        
        if (!empty($this->recipe)) {
            foreach ($this->recipe as $recipeKey => $recipeVal) {
                $recipeImage[] = $recipeVal->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
            }
            if (!empty($recipeImage)) {
                $recipeArray   = [
                    'category' => 'Recipe',
                    'count'    => count($this->recipe),
                    'images'   => array_slice($recipeImage, 0, 3)

                ];
                array_push($contentImages, $recipeArray);
            }
        }

        if (!empty($this->masterclass)) {
            foreach ($this->masterclass as $masterclassKey => $masterclassVal) {
                $masterclassImage[] = $masterclassVal->getMediaData('logo', ['w' => 1280, 'h' => 640, 'zc' => 3]);
            }
            if (!empty($masterclassImage)) {
                $masterclassArray = [
                    'category'    => 'MasterClass',
                    'count'       => count($this->masterclass),
                    'images'      => array_slice($masterclassImage, 0, 3)

                ];
                array_push($contentImages, $masterclassArray);
            }
        }

        if (!empty($this->webinar)) {
            foreach ($this->webinar as $webinarKey => $webinarVal) {
                $webinarImage[] = $webinarVal->getMediaData('logo', ['w' => 640, 'h' => 1280, 'zc' => 3]);
            }
            if (!empty($webinarImage)) {
                $webinarArray   = [
                    'category'  => 'Webinar',
                    'count'     => count($this->webinar),
                    'images'    => array_slice($webinarImage, 0, 3)

                ];
                array_push($contentImages, $webinarArray);
            }
        }
        return [
            'data' => $contentImages,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
