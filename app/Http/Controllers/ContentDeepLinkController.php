<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentDeepLinkController extends Controller
{
    /**
     * This function use for check valid deeplink url for contents like feed,recipe,webinar and all and redirect to specified urls
     * @param Request $request
     * @return View
     */
    public function verifyDeepLink(Request $request)
    {
        $data['deeplinkUrl'] = $request['url'] ?? null;
        return \view('custom.verify-deeplink', $data);
    }
    
}
