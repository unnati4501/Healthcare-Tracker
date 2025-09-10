<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Api\V6\Auth;

use App\Http\Controllers\API\V5\Auth\LoginController as v5LoginController;
use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Illuminate\Support\Facades\Auth;
use JWTAuth;

/**
 * Class LoginController
 *
 * @package App\Http\Controllers\Api\Auth
 */
class LoginController extends v5LoginController
{
    
}
