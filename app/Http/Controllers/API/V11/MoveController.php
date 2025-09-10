<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V11;

use App\Http\Controllers\API\V1\MoveController as v1MoveController;
use App\Http\Requests\Api\V1\GetStepRequest as v1GetStepRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class MoveController extends v1MoveController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStepsMeCompany(v1GetStepRequest $request)
    {
        try {
            // logged-in user
            $user = $this->user();

            // app timezone and user timezone
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? $appTimezone;
            $company     = $user->company()->first();

            $data = [];
            if ($request->duration == 'weekly') {
                $totalWeeks = getWeeks($request->year, $timezone);
                foreach ($totalWeeks as $week => $dates) {
                    $companyData = $company->members()->join('user_step', 'users.id', '=', 'user_step.user_id')
                        ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'))
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->groupBy('user_step.user_id')
                        ->get()->toArray();

                    $companyAverage = (count($companyData) > 0) ? array_sum(array_column($companyData, 'total')) / count($companyData) : 0;

                    $count = $user->steps()
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '>=', $dates['week_start'])
                        ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '<=', $dates['week_end'])
                        ->sum($request->type);

                    $userAverage = $count / 7;

                    $weekData            = [];
                    $weekData['key']     = $week;
                    $weekData['me']      = round($userAverage, 1);
                    $weekData['company'] = round($companyAverage, 1);

                    array_push($data, $weekData);
                }
            } elseif ($request->duration == 'monthly') {
                $monthArr = getMonths($request->year);
                foreach ($monthArr as $month => $monthName) {
                    $companyData = $company->members()->join('user_step', 'users.id', '=', 'user_step.user_id')
                        ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'))
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->groupBy('user_step.user_id')
                        ->get()->toArray();

                    $companyAverage = (count($companyData) > 0) ? array_sum(array_column($companyData, 'total')) / count($companyData) : 0;

                    $count = $user->steps()
                        ->where(\DB::raw("MONTH(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $month)
                        ->where(\DB::raw("YEAR(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), '=', $request->year)
                        ->sum($request->type);

                    $userAverage = $count / count($monthArr);

                    $monthData            = [];
                    $monthData['key']     = ucfirst($monthName);
                    $monthData['me']      = round($userAverage, 1);
                    $monthData['company'] = round($companyAverage, 1);

                    array_push($data, $monthData);
                }
            } elseif ($request->duration == 'daily') {
                $datesArr = getDates($request->year, $timezone);

                $startDate   = $request->year . '-01-' . '01';
                $endDate     = $request->year . '-12-' . '31';
                $currentYear = Carbon::now($timezone)->format('y');

                if ($currentYear == $request->year) {
                    $date    = Carbon::now($timezone)->format('d');
                    $month   = Carbon::now($timezone)->format('m');
                    $endDate = $request->year . '-' . $month . '-' . $date;
                }

                $companyData = $company->members()->join('user_step', 'users.id', '=', 'user_step.user_id')
                    ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'), \DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}')) as ct_log_date"), \DB::raw('SUM(user_step.steps)/count(DISTINCT user_team.id) as sync_users'))
                    ->whereBetween(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), [$startDate, $endDate])
                    ->groupBy('ct_log_date')
                    ->get()->pluck('sync_users', 'ct_log_date')->toArray();

                $getUsersCount = $user->steps()
                    ->select(\DB::raw('SUM(user_step.' . $request->type . ') as total'), \DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}')) as ct_log_date"))
                    ->whereBetween(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$user->timezone}'))"), [$startDate, $endDate])
                    ->groupBy('ct_log_date')
                    ->get()->pluck('total', 'ct_log_date')->toArray();

                foreach ($datesArr as $key => $date) {
                    $companyAverage = isset($companyData[$date]) ? $companyData[$date] : 0;
                    $count          = isset($getUsersCount[$date]) ? $getUsersCount[$date] : 0;

                    $dateData            = [];
                    $dateData['key']     = date("d M'y", strtotime($date));
                    $dateData['me']      = (int) $count;
                    $dateData['company'] = round((int)$companyAverage, 1);

                    array_push($data, $dateData);
                }

                array_multisort($datesArr, SORT_ASC, $data);
            }

            return $this->successResponse(['data' => $data], 'Company v/s me ' . $request->type . ' data retrived successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
