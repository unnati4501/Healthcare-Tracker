<?php

namespace App\Http\Collections\V41;

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
        $this->podcast                  =  $contentData['podcast'];
        $this->short                    =  $contentData['short'];
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
            foreach ($this->meditation as $meditationVal) {
                $meditationImage[] = $meditationVal->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);
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
            foreach ($this->feed as $feedVal) {
                $feedImage[] = $feedVal->getMediaData('header_image', ['w' => 800, 'h' => 800]);
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
            foreach ($this->recipe as $recipeVal) {
                $recipeImage[] = $recipeVal->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);
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
            foreach ($this->masterclass as $masterclassVal) {
                $masterclassImage[] = $masterclassVal->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);
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
            foreach ($this->webinar as $webinarVal) {
                $webinarImage[] = $webinarVal->getMediaData('header_image', ['w' => 800, 'h' => 800, 'zc' => 3]);
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

        if (!empty($this->podcast)) {
            foreach ($this->podcast as $podcastVal) {
                $podcastImage[] = $podcastVal->getMediaData('logo', ['w' => 800, 'h' => 800, 'zc' => 3]);
            }
            if (!empty($podcastImage)) {
                $podcastArray = [
                    'category'  => 'podcast',
                    'count'     => count($this->podcast),
                    'images'    => array_slice($podcastImage, 0, 3),

                ];
                array_push($contentImages, $podcastArray);
            }
        }

        if (!empty($this->short)) {
            foreach ($this->short as $shortVal) {
                $headerImage[] = $shortVal->getMediaData('header_image', ['w' => 1080, 'h' => 1920, 'zc' => 3]);
            }
            if (!empty($headerImage)) {
                $shortsArray = [
                    'category'  => 'shorts',
                    'count'     => count($this->short),
                    'images'    => array_slice($headerImage, 0, 3),

                ];
                array_push($contentImages, $shortsArray);
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
