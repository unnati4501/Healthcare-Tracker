<?php
declare (strict_types = 1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Repositories\CronofyRepository;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Class CronofyAuthenticate
 * @package App\Http\Middleware
 */
class CronofyAuthenticate
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
        $user = Auth::user();
        if (isset($request->user) && !empty($request->user)) {
            $user = $request->user;
        } 
        if (empty($user)) { // It's required
            $wsId = $request->cronofySchedule->ws_id;
            $user = User::where('id', $wsId)->first();
        }
        $role = getUserRole($user);
        if ($role->group == 'company') { // This is for company Admin
            $user = User::where('id', $request->ws_user)->first();
        }
        if (!empty($user) && $role->slug == 'user') { // This is for Mobile 
            $authentication = $user->cronofyAuthenticate()->first();
            if (!empty($authentication)) {
                $this->cronofyRepository->refreshToken($authentication);
            }
        } elseif ($role->slug == 'wellbeing_specialist') { // This is for Mobile - Portal
            $wsDetails = $user->wsuser()->first();
            if ($wsDetails->is_authenticate) {
                $authentication = $user->cronofyAuthenticate()->first();
                if (!empty($authentication)) {
                    $this->cronofyRepository->refreshToken($authentication);
                }
                // $this->cronofyRepository->updateUserInfo($user->id, $authentication->id);
            }
        } elseif ($role->slug == 'health_coach') { // This is for WBS
            $wcDetails = $user->healthCoachUser()->first();
            if ($wcDetails->is_authenticate) {
                $authentication = $user->cronofyAuthenticate()->first();
                if (!empty($authentication)) {
                    $this->cronofyRepository->refreshToken($authentication);
                }
            }
        }
        return $next($request);
    }
}
