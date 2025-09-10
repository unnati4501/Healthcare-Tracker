<?php
declare(strict_types = 1);

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Traits\ServesApiTrait;

class PlanController extends Controller
{
    use ServesApiTrait;
    
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function plans(Request $request)
    {
        $jsonString =  '{"code":200,"message":"Subscription plans retrieved successfully.","result":{"data":{"description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur varius orci tortor, vel dignissim sem bibendum ac. Aenean pulvinar sem id aliquet ornare. Aliquam erat volutpat. Nunc fermentum turpis sit amet ipsum scelerisque, at auctor magna fermentum. Quisque nec dignissim nisi. Curabitur vel auctor est. Proin eu libero eu lacus tempus commodo et laoreet mauris. Aliquam tempus ac lacus in cursus.","plans":[{"id":1,"label":"Monthly","days":30,"amount":40,"info":["Basic info about plan","More info about plans"]},{"id":2,"label":"Yearly","days":365,"amount":100,"info":["Basic info about plan","More info about plans"]}]}}}';
        
        return json_decode($jsonString, true);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlockPlan(Request $request)
    {
        $jsonString =  '{"code":200,"message":"Subscription plan applied successfully.","result":{"data":{"expirationDate":"2019-09-20T12:05:30+0530","isPremium":true}}}';
        
        return json_decode($jsonString, true);
    }
    
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyCoupon(Request $request)
    {
        $jsonString =  '{"code":200,"message":"Coupon code applied successfully.","result":{"data":{"amount":20}}}';
        
        return json_decode($jsonString, true);
    }
}
