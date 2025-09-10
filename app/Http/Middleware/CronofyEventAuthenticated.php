<?php
declare (strict_types = 1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Repositories\CronofyRepository;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class CronofyEventAuthenticated
 * @package App\Http\Middleware
 */
class CronofyEventAuthenticated
{
    /**
     * variable to store the Cronofy Repository Repository object
     * @var CronofyRepository $cronofyRepository
     */
    private $cronofyRepository;

    /**
     * contructor to initialize Repository object
     */
    public function __construct(CronofyRepository $cronofyRepository)
    {
        $this->cronofyRepository = $cronofyRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user      = Auth::user();
        $user      = User::where('id', $request->ws_user)->first();
        $wcDetails = $user->wsuser()->first();
        if ($wcDetails->is_authenticate) {
            $authentication = $user->cronofyAuthenticate()->first();
            if (!empty($authentication)) {
                $this->cronofyRepository->refreshToken($authentication);
            }
        }
        return $next($request);
    }
}
