<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;

class ServerinfoController extends Controller
{
    /**
     * This function use for check server enable extension and php version
     */
    public function info()
    {
        // Just for Information
    }
    

    public function WebSocketInfo()
    {
        $data = [];
        return \view('checkingwebsocket', $data);
    }
}
