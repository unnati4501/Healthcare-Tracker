<?php
declare (strict_types = 1);

use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\ContentChallenge;
use App\Models\ContentChallengeActivity;
use App\Models\ContentPointCalculation;
use App\Models\CpFeatures;
use App\Models\Department;
use App\Models\EventRegisteredUserLog;
use App\Models\Team;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\ZcSurveyUserLog;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;

if (!function_exists('upper')) {
    /**
     * Convert the given string to upper-case.
     *
     * @param  string  $value
     * @return string
     */
    function upper($value): string
    {
        return Str::upper($value);
    }
}

if (!function_exists('lower')) {
    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    function lower($value): string
    {
        return Str::lower($value);
    }
}

if (!function_exists('getYoutubeVideoId')) {
    /**
     * @param string $url
     * @return null|string
     */
    function getYoutubeVideoId(string $url):  ? string
    {
        \preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i',
            $url,
            $matches
        );

        return $matches[1];
    }
}

if (!function_exists('getYoutubeVideoCover')) {
    /**
     * @param string $id
     * @param string $size
     * @return string
     */
    function getYoutubeVideoCover(string $id, string $size = 'hqdefault') : string
    {
        return "https://i1.ytimg.com/vi/{$id}/{$size}.jpg";
    }
}

if (!function_exists('getFileLink')) {
    /**
     * @param BadgeCategory $category
     *
     * @return JsonResponse
     */
    function getFileLink($importId, $fileName = "", $module = "", $import = 0, $storageLink = 0): string
    {
        if (!empty($fileName)) {
            if ($import == 1) {
                $link = $module . '_import/' . $importId . '/' . $fileName;
            } else {
                $link = $module . '_import/' . $importId . '/' . str_replace($module . '_import', 'corrected', $fileName);
            }
        } else {
            $link = $module . '_import/' . $importId;
        }

        if ($storageLink) {
            $disk = config('medialibrary.disk_name');
            if ($disk == "spaces") {
                $storage = Storage::disk($disk);
                $link    = $storage->url($link);
            } elseif ($disk == "azure") {
                $azureAccountName = config('filesystems.disks.azure.account.name');
                $azureAccountKey  = config('filesystems.disks.azure.account.key');
                $container        = config('filesystems.disks.azure.container');
                $prefix           = config('filesystems.disks.azure.prefix');
                $connectionString = "DefaultEndpointsProtocol=https;AccountName={$azureAccountName};AccountKey={$azureAccountKey};";

                $blobClient = BlobRestProxy::createBlobService($connectionString);
                $link       = $blobClient->getBlobUrl($container, "$prefix/$link");
            }
        }
        return $link;
    }
}

if (!function_exists('uploadFileToSpaces')) {
    function uploadFileToSpaces($object, $path, $visibility = 'private')
    {
        $config = [
            'version'     => 'latest',
            'region'      => config('filesystems.disks.spaces.region'),
            'endpoint'    => config('filesystems.disks.spaces.endpoint'),
            'credentials' => [
                'key'    => config('filesystems.disks.spaces.key'),
                'secret' => config('filesystems.disks.spaces.secret'),
            ],
            'debug'       => false,
        ];

        $objectConfiguration = [
            'Bucket' => config('filesystems.disks.spaces.bucket'),
            'Key'    => $path,
            'Body'   => $object,
        ];

        if (isset($visibility) && $visibility === 'public') {
            //'private|public-read|public-read-write|authenticated-read',]);
            $objectConfiguration['ACL'] = 'public-read';
        } else {
            $objectConfiguration['ACL'] = 'private';
        }
        $client = new S3Client($config);
        return $client->putObject($objectConfiguration);
    }
}

if (!function_exists('pr')) {
    function pr($data, $exit = 0)
    {
        echo "<pre>";
        print_r($data);
        if ($exit == 1) {
            exit;
        }
    }
}

if (!function_exists('getStates')) {
    /**
     * @param array $needles
     * @param array $haystack
     *
     * @return boolean
     */
    function getStates($countryID): array
    {
        $country = App\Models\Country::find($countryID);

        $statesArr = [];
        $states    = $country->states;

        if ($states) {
            foreach ($states as $state) {
                $statesArr[$state->getKey()] = $state->name;
            }
        }

        return $statesArr;
    }
}

if (!function_exists('getTimezones')) {
    /**
     * @param array $needles
     * @param array $haystack
     *
     * @return boolean
     */
    function getTimezones($countryID): array
    {
        $country = App\Models\Country::find($countryID);

        $timezonesArr = [];
        $timezones    = App\Models\Timezone::where('country_code', $country->sortname)->get();

        if ($timezones) {
            foreach ($timezones as $timezone) {
                $timezonesArr[$timezone->name] = $timezone->name;
            }
        }

        return $timezonesArr;
    }
}

if (!function_exists('getRoles')) {
    /**
     * @param array $needles
     * @param array $haystack
     *
     * @return boolean
     */
    function getRoles($group): array
    {
        $roles = App\Models\Role::where('group', $group)->get(); //->whereNotIn('slug', ['super_admin'])

        $rolesArr = [];
        if ($roles) {
            foreach ($roles as $role) {
                $rolesArr[$role->getKey()] = $role->name;
            }
        }
        return $rolesArr;
    }
}

if (!function_exists('getDepartments')) {
    /**
     * @param array $needles
     * @param array $haystack
     *
     * @return boolean
     */
    function getDepartments($companyID): array
    {
        $company = App\Models\Company::find($companyID);

        $returnArr = [];
        $records   = $company->departments;

        if ($records) {
            foreach ($records as $record) {
                $returnArr[$record->getKey()] = $record->name;
            }
        }

        return $returnArr;
    }
}

if (!function_exists('getTeams')) {
    /**
     * @param $departmentID
     * @return array
     */
    function getTeams($departmentID): array
    {
        $returnArr = [];
        $dept      = Department::select('id')->find($departmentID);
        $teams     = $dept->teams()->select('teams.id', 'teams.name')->get();
        foreach ($teams as $team) {
            $returnArr[$team->id] = $team->name;
        }
        return $returnArr;
    }
}

if (!function_exists('getLimitWiseTeams')) {
    /**
     * To get team list limit wise
     *
     * @param $department
     * @param $team
     * @return array
     */
    function getLimitWiseTeams($department, $team = null): array
    {
        $data       = [];
        $department = Department::select('id', 'company_id')->find($department);
        $company    = $department->company()->select('companies.id', 'companies.auto_team_creation', 'companies.team_limit')->first();
        $department
            ->teams()
            ->select('teams.id', 'teams.name', 'teams.default')
            ->when($company->auto_team_creation, function ($query) use ($company, $team) {
                $query
                    ->withCount('users')
                    ->having('users_count', '<', $company->team_limit, 'or')
                    ->having('teams.default', '=', true, 'or');
                if (!is_null($team)) {
                    $query->having('teams.id', '=', $team, 'or');
                }
            })
            ->get()
            ->each(function ($team) use (&$data) {
                $data[$team->id] = $team->name;
            });
        return $data;
    }
}

if (!function_exists('generateProcessKey')) {
    function generateProcessKey()
    {
        return date('YmdHis') . rand(100, 50000);
    }
}

if (!function_exists('getTeamMembersData')) {
    /**
     * @param array $needles
     * @param array $haystack
     *
     * @return boolean
     */
    function getTeamMembersData(): array
    {
        $companies = Company::where('subscription_start_date', '<=', Carbon::now())->get();

        // get companies, departments, teams and users
        $companyData = [];
        foreach ($companies as $company) {
            $depts = $company->departments;

            if (!$depts->isEmpty()) {
                $departmentsTeams = [];
                foreach ($depts as $dept) {
                    $teams = $dept->teams;

                    if (!$teams->isEmpty()) {
                        $teamsData = [];
                        foreach ($teams as $team) {
                            $members     = $team->users;
                            $membersData = [];
                            if (!$members->isEmpty()) {
                                foreach ($members as $member) {
                                    if (($member->can_access_app || $member->can_access_portal) && !$member->is_blocked) {
                                        $membersData[] = [
                                            'id'   => $member->id,
                                            'name' => $member->first_name . " " . $member->last_name,
                                        ];
                                    }
                                }
                            }

                            $teamsData[] = [
                                'id'      => $team->id,
                                'name'    => $team->name,
                                'code'    => 'code: ' . $team->code,
                                'members' => $membersData,
                            ];
                        }

                        $departmentsTeams[] = [
                            'id'    => $dept->id,
                            'name'  => $dept->name,
                            'teams' => $teamsData,
                        ];
                    }
                }
                $companyData[] = [
                    'id'          => $company->id,
                    'name'        => $company->name,
                    'departments' => $departmentsTeams,
                ];
            }
        }
        return $companyData;
    }
}

if (!function_exists('getWeeks')) {
    function getWeeks($year, $timezone)
    {
        $commonDate = new DateTime('December 28th, ' . $year);
        $totalWeeks = (int) $commonDate->format('W'); # 53

        $currentPoint = "";
        if ($year == date('Y')) {
            $currentPoint = now($timezone)->toDateString();
        }

        $returnArr = [];

        for ($week = 1; $week <= $totalWeeks; $week++) {
            $weekDates = [];

            $weekKey = "";

            $date = new DateTime();
            $date->setISODate((int) $year, $week);

            $start     = $date->format('Y-m-d');
            $startWeek = $date->format('d M');

            $date->modify('+6 days');

            $end     = $date->format('Y-m-d');
            $endWeek = $date->format('d M');

            if (!empty($currentPoint) && ($currentPoint < $start && $currentPoint < $end)) {
                break;
            }

            $weekKey                  = $startWeek . '-' . $endWeek;
            $weekDates['week_start']  = $start;
            $weekDates['week_end']    = $end;
            $weekDates['week_number'] = (int) $date->format('W');

            $returnArr[$weekKey] = $weekDates;
        }

        return $returnArr;
    }
}

if (!function_exists('getMonths')) {
    function getMonths($year)
    {
        $returnArr = [];

        $currentMonth = (int) date('m');

        $count = 12;
        if ($year == date('Y')) {
            $count = $currentMonth;
        }
        for ($m = 1; $m <= $count; $m++) {
            $month     = date('m', mktime(0, 0, 0, $m, 1, (int) $year));
            $monthName = date('M', mktime(0, 0, 0, $m, 1, (int) $year));

            $returnArr[$month] = $monthName;
        }

        return $returnArr;
    }
}

if (!function_exists('getDates')) {
    function getDates($year, $timezone)
    {
        $returnArr = [];

        $start = strtotime($year . '-01-01');
        $end   = strtotime($year . '-12-31');

        if ($year == date('Y')) {
            $end = strtotime(now($timezone)->toDateString());
        }

        do {
            $returnArr[date('d M', $start)] = date('Y-m-d', $start);
            $start                          = strtotime("+ 1 day", $start);
        } while ($start <= $end);

        return $returnArr;
    }
}

if (!function_exists('getWeeksbyKeys')) {
    function getWeeksbyKeys($date, $timezone)
    {
        $today = Carbon::parse(now()->toDateTimeString())->setTimeZone($timezone)->toDateString();
        $date  = Carbon::parse($date)->setTimeZone($timezone);
        $year  = (int) $date->format('Y');
        $week  = (int) $date->format('W');
        $weeks = [];

        for ($i = 0; $i < 6; $i++) {
            $date = new Carbon();
            $date->setISODate($year, $week)->setTimeZone($timezone);
            $start_date = $date->startOfWeek()->format('Y-m-d');
            $end_date   = $date->endOfWeek()->format('Y-m-d');

            if ($today < $start_date && $today < $end_date) {
                break;
            }

            $weeks[$week] = $date->startOfWeek()->format('d M') . '-' . $date->endOfWeek()->format('d M');
            $week++;
        }

        return $weeks;
    }
}
if (!function_exists('getDaysbyKeys')) {
    function getDaysbyKeys($date, $timezone)
    {
        $date = Carbon::parse($date)->setTimeZone($timezone);
        $days = [];

        for ($i = 1; $i < 8; $i++) {
            $days[$date->toDateString()] = $date->format('jS-M');
            $date->addDays(1);
        }

        return $days;
    }
}

if (!function_exists('getWeeksBetweenDatebyKeys')) {
    function getWeeksBetweenDatebyKeys($timezone, $range_start_date, $range_end_date = "")
    {
        $range_start_date = Carbon::parse($range_start_date, $timezone)->setTimeZone($timezone);
        $year             = (int) $range_start_date->format('Y');
        $week             = (int) $range_start_date->format('W');
        $weeks            = [];

        for ($i = 0; $i < 27; $i++) {
            $date = new Carbon();
            $date->setISODate($year, $week)->setTimeZone($timezone);
            $start_date = $date->startOfWeek()->format('Y-m-d');
            $end_date   = $date->endOfWeek()->format('Y-m-d');

            if ($range_end_date < $start_date && $range_end_date < $end_date) {
                break;
            }

            $weeks[$week] = $date->startOfWeek()->format('d M') . '-' . $date->endOfWeek()->format('d M');
            $week++;
        }

        return $weeks;
    }
}

if (!function_exists('getMonthsBetweenDatebyKeys')) {
    function getMonthsBetweenDatebyKeys($timezone, $range_start_date, $range_end_date)
    {
        $months           = [];
        $range_start_date = Carbon::parse($range_start_date, $timezone)->setTimeZone($timezone);
        $range_end_date   = Carbon::parse($range_end_date, $timezone)->setTimeZone($timezone);
        $period           = CarbonPeriod::create($range_start_date, '1 month', $range_end_date);
        foreach ($period as $month) {
            $months[$month->format("Y_m")] = $month->format("M-y");
        }
        return $months;
    }
}

if (!function_exists('getMonthsbyKeys')) {
    function getMonthsbyKeys($date, $timezone, $key_with_year = false)
    {
        $months = [];
        $date   = Carbon::parse($date, $timezone)->setTimeZone($timezone)->format('Y-m-01');
        for ($i = 1; $i <= 12; $i++) {
            $strtotime = strtotime($date . " +$i months");
            $key       = date("n", $strtotime);
            $months[$key] = date("M-Y", $strtotime);
        }
        return $months;
    }
}
if (!function_exists('getUnMappedTrackerExercises')) {
    /**
     * @param array $needles
     * @param array $haystack
     *
     * @return boolean
     */
    function getUnMappedTrackerExercises(): array
    {
        $mapped = \DB::table('exercise_mapping')->pluck('tracker_exercise_id')->toArray();

        $trackers = \DB::table('tracker_exercises')->groupBy('tracker_title')->pluck('tracker_title')->toArray();

        // get exercises
        $returnData = [];
        foreach ($trackers as $tracker) {
            $exercises = \DB::table('tracker_exercises')->select('*')->where('tracker_title', $tracker)->whereNotIn('id', $mapped)->get();

            $exerciseData = [];

            if (!empty($exercises)) {
                foreach ($exercises as $key => $exercise) {
                    $exerciseData[$key]['id']   = $exercise->id;
                    $exerciseData[$key]['name'] = $exercise->name;
                }
            }

            $returnData[] = [
                'name'      => $tracker,
                'exercises' => $exerciseData,
            ];
        }
        return $returnData;
    }
}

if (!function_exists('access')) {

    /**
     * Access (lol) the Access:: facade as a simple function.
     */
    function access()
    {
        return app('access');
    }
}

if (!function_exists('calculatDayHrMin')) {
    function calculatDayHrMin($start, $end)
    {
        $date1 = strtotime($start);
        $date2 = strtotime($end);

        $diff = abs($date2 - $date1);

        $years = floor($diff / (365 * 60 * 60 * 24));

        $days = floor(($diff - $years * 365 * 60 * 60 * 24 * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        $hours = floor(($diff - $years * 365 * 60 * 60 * 24 * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));

        $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);

        $calData = array();

        $calData['day']    = $days;
        $calData['hour']   = $hours;
        $calData['minute'] = $minutes;

        return $calData;
    }
}

if (!function_exists('getLasXDates')) {
    function getLasXDates($year, $timezone)
    {
        $returnArr = [];

        $start = \Carbon\Carbon::today($timezone)->subDays(1); // start from yesterday
        $end   = \Carbon\Carbon::today($timezone)->subDays(8);

        do {
            $returnArr[$start->format('d M')] = $start->toDateString();
            $start                            = $start->subDays(1);
        } while ($start > $end);

        return $returnArr;
    }
}

if (!function_exists('createDateRange')) {
    /**
     * Generate an array of DateTimeObjects
     * between two provided dates
     *
     * @param DateTime $start
     * @param DateTime $end
     *
     * @return array
     * @throws Exception
     */
    function createDateRange(\DateTime $start, \DateTime $dt): array
    {
        $startDate = $start->toDateString();
        $endDate   = $dt->toDateString();

        $range = [];
        do {
            $range[]   = Carbon::parse($startDate);
            $startDate = Carbon::parse($startDate)->addDays(1)->toDateString();
        } while ($startDate <= $endDate);

        return $range;
    }
}

if (!function_exists('getUserRole')) {

    /**
     * This function will return the role id of current logged in user
     *
     * @param App\Models\User\User
     *
     * @return object
     */
    function getUserRole($user = null)
    {
        if (is_null($user)) {
            $user = Auth::User();
        }

        if (is_null($user)) {
            return false;
        }

        $roles = $user->roles;

        if (is_null($roles->first())) {
            return false;
        }

        return $roles->first();
    }
}

if (!function_exists('decimalToTime')) {

    /**
     * This function will return the role id of current logged in user
     *
     * @param App\Models\User\User
     *
     * @return object
     */
    function decimalToTime($decimal)
    {
        $hours   = floor($decimal / 60);
        $minutes = floor($decimal % 60);
        $seconds = $decimal - (int) $decimal;
        $seconds = round($seconds * 60);

        return str_pad((string) $hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad((string) $minutes, 2, "0", STR_PAD_LEFT) . ":" . str_pad((string) $seconds, 2, "0", STR_PAD_LEFT);
    }
}

if (!function_exists('timeToDecimal')) {

    /**
     * This function will return the role id of current logged in user
     *
     * @param App\Models\User\User
     *
     * @return object
     */
    function timeToDecimal($time)
    {
        $timeArr = explode(':', $time);
        return ($timeArr[0] * 60) + ($timeArr[1]) + ($timeArr[2] / 60);
    }
}

if (!function_exists('timeToSec')) {
    /**
     * This function will convert hh:mm:ss or hh:mm format time to seconds
     *
     * @return void
     * @author
     **/
    function timeToSec($time)
    {
        $sec = 0;
        foreach (array_reverse(explode(':', $time)) as $k => $v) {
            $sec += pow(60, $k) * $v;
        }

        return $sec;
    }
}

if (!function_exists('getAscendingArray')) {

    /**
     * This function will return the role id of current logged in user
     *
     * @param App\Models\User\User
     *
     * @return object
     */
    function getAscendingArray($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
}

if (!function_exists('getDescendingArray')) {

    /**
     * This function will return the role id of current logged in user
     *
     * @param App\Models\User\User
     *
     * @return object
     */
    function getDescendingArray($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? -1 : 1;
    }
}

if (!function_exists('cronlog')) {
    function cronlog($data, $update = 0)
    {
        if ($update == 1) {
            $cron = \DB::table('cron_logs')
                ->where('cron_name', $data['cron_name'])
                ->where('unique_key', $data['unique_key'])
                ->orderByDesc('id')
                ->limit(1);
        }
        if (isset($cron)) {
            $data['end_time']       = \Carbon\Carbon::now();
            $data['execution_time'] = gmdate('H:i:s', $data['end_time']->diffInSeconds(Carbon::parse($cron->pluck('start_time')->first())));
            $data['updated_at']     = \Carbon\Carbon::now();
            $cron->update($data);
        } else {
            \DB::table('cron_logs')->insert([
                'cron_name'  => $data['cron_name'],
                'unique_key' => $data['unique_key'],
                'start_time' => \Carbon\Carbon::now(),
                'created_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}

if (!function_exists('numberFormatShort')) {
    /**
     * This function will converts a number into a short version, eg: 1000 -> 1k
     *
     * @param Actaul number
     * @param Decimal precision
     *
     * @return object
     */
    function numberFormatShort($n, $precision = 1)
    {
        if ($n != null) {
            $n = (float) $n;
            if ($n < 999) {
                $n_format = number_format($n, $precision); // 0 - 900
                $suffix   = '';
            } elseif ($n < 999999) {
                $n_format = number_format($n / 1000, $precision); // 0.9k-850k
                $suffix   = 'K';
            } elseif ($n < 999999999) {
                $n_format = number_format($n / 1000000, $precision); // 0.9m-850m
                $suffix   = 'M';
            } elseif ($n < 999999999999) {
                $n_format = number_format($n / 1000000000, $precision); // 0.9b-850b
                $suffix   = 'B';
            } else {
                $n_format = number_format($n / 1000000000000, $precision); // 0.9t+
                $suffix   = 'T';
            }

            // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
            // Intentionally does not affect partials, eg "1.50" -> "1.50"
            if ($precision > 0) {
                $dotzero  = '.' . str_repeat('0', $precision);
                $n_format = str_replace($dotzero, '', $n_format);
            }
            return $n_format . $suffix;
        }
        return 0;
    }
}

if (!function_exists('spGetUser')) {
    /**
     * This function will gives you total number of users based upon filters
     *
     * @param $payload - array of filter
     *
     * @return Array
     */
    function spGetUser($payLoad)
    {
        $users = \DB::select('CALL sp_get_users(?, ?, ?, ?, ?, ?, ?, ?)', $payLoad);
        $users = (!empty($users[0]) ? $users[0]->active_users : 0);
        return $users;
    }
}

if (!function_exists('convertToHoursMins')) {
    /**
     * This function will converts miuntes into nice format like 6:57 to 06 hr 57 mins
     *
     * @param time minubtes
     * @param boolean wholeString
     * @param string Format
     *
     * @return String
     */
    function convertToHoursMins($time, $wholeString = true, $format = '%02d hr %s mins')
    {
        if ($time < 1) {
            return;
        }
        $hours   = floor($time / 60);
        $minutes = ($time % 60);
        if ($wholeString) {
            return sprintf($format, $hours, $minutes);
        } else {
            if ((int) $hours == 1) {
                $hoursDisplay = (int) $hours . " Hour";
            } elseif ((int) $hours > 1) {
                $hoursDisplay = (int) $hours . " Hours";
            } else {
                $hoursDisplay = "";
            }
            return sprintf($format, $hoursDisplay, (($minutes > 0) ? (int) $minutes . " Minutes" : ""));
        }
    }
}

if (!function_exists('getNotificationSetting')) {
    /**
     * This function will check notificaiton setting for the passed module for a user and return boolean value
     *
     * @param $user
     * @param $module
     *
     * @return Boolean
     */
    function getNotificationSetting($user, $module)
    {
        $notificationSetting = $user->notificationSettings()
            ->whereRaw("(user_notification_settings.module = ? OR user_notification_settings.module = ?)", [$module, "all"])
            ->count();
        return ($notification_setting > 0);
    }
}

if (!function_exists('getDefaultFallbackImageURL')) {
    /**
     * This function will return default fallback image definded for model and collection in zevolifesettings config incase not found then default fallback image will be returned
     *
     * @param $model
     * @param $collection
     *
     * @return String
     */
    function getDefaultFallbackImageURL(string $model = "", string $collection = ""): string
    {
        return config("zevolifesettings.fallback_image_url.{$model}.{$collection}", config('zevolifesettings.default_fallback_white_image_url'));
    }
}

if (!function_exists('eQL')) {
    function eQL()
    {
        DB::enableQueryLog();
    }
}

if (!function_exists('gQL')) {
    function gQL($data = [])
    {
        dd(array_merge([DB::getQueryLog()], (array) $data));
    }
}

if (!function_exists('getApiVersion')) {
    /**
     * This function will return API version from specified route object if not specified then it will use current route
     *
     * @param $route
     *
     * @return Intrger
     */
    function getApiVersion($route = '')
    {
        $version   = 1;
        $route     = ((!empty($route)) ? $route : Route::current());
        $namespace = ($route->action['namespace'] ?? '');
        if (!empty($namespace)) {
            $stack   = explode("\\", $namespace);
            $version = (isset($stack[4]) ? $stack[4] : 1);
            $version = (!empty($version) ? substr($version, 1) : 1);
        }
        return (int) $version;
    }
}

/**
 * This function will update notification setting modules for specified user
 *
 * @param $route
 *
 * @return String
 */
if (!function_exists('updateNotificationSettingModules')) {
    function updateNotificationSettingModules($user)
    {
        $notificationsSettings = $user->notificationSettings();
        $defaultNotifications  = ['all' => false] + config('zevolifesettings.notificationModules');
        if ($notificationsSettings->count() < sizeof($defaultNotifications)) {
            $modules         = $notificationsSettings->get()->pluck('flag', 'module')->toArray();
            $newModules      = array_diff_key($defaultNotifications, $modules);
            $newModulesArray = [];
            if (sizeof($newModules) > 0) {
                foreach ($newModules as $key => $value) {
                    $newModulesArray[] = [
                        'module' => $key,
                        'flag'   => $value,
                    ];
                }
                $user->notificationSettings()->createMany($newModulesArray);
            }
        }
    }
}
if (!function_exists('getAzureAuthHeader')) {
    function getAzureAuthHeader()
    {
        $header                  = [];
        $header['x-ms-date']     = Carbon::now('UTC')->toDateTimeString();
        $header['Authorization'] = "SharedKey " . config('filesystems.disks.azure.account.name') . ":" . config('filesystems.disks.azure.account.key');

        return $header;
    }
}

if (!function_exists('uploadeFileToBlob')) {
    function uploadeFileToBlob($content, $fileName, $path)
    {
        $azureAccountName      = config('filesystems.disks.azure.account.name');
        $azureAccountKey       = config('filesystems.disks.azure.account.key');
        $azureAccountContainer = config('filesystems.disks.azure.container');
        $prefix                = config('filesystems.disks.azure.prefix');
        $connectionString      = "DefaultEndpointsProtocol=https;AccountName={$azureAccountName};AccountKey={$azureAccountKey};";
        $azureStoragePath      = $azureAccountContainer . '/' . $prefix . '/' . $path;
        $result                = null;

        $blobClient = BlobRestProxy::createBlobService($connectionString);
        $result     = $blobClient->createBlockBlob($azureStoragePath, $fileName, $content);
        if ($result != null && is_object($result)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('convertSecondToMinute')) {
    /**
     * This function will converts miuntes into nice format like 6:57 to 06 hr 57 mins
     *
     * @param Time minubtes
     * @param Format
     *
     * @return String
     */
    function convertSecondToMinute($seconds)
    {
        if (empty($seconds)) {
            return 0;
        }
        $sec     = 0;
        $minutes = 0;
        $minutes = (int) floor($seconds / 60);
        $sec     = ($seconds % 60);

        if ($sec >= 30) {
            $minutes = $minutes + 1;
        }

        return $minutes;
    }
}

if (!function_exists('getStaticAlertIconUrl')) {
    /**
     * return static icon url of alert according tag
     *
     * @param  string  $tag
     * @return string
     */
    function getStaticAlertIconUrl(string $tag): string
    {
        $disk = config('medialibrary.disk_name');
        $path = config("medialibrary.{$disk}.domain");
        $icon = config("zevolifesettings.static_image.notification_icons.{$tag}");

        return (empty($icon)) ? getDefaultFallbackImageURL('notification', 'logo') : "{$path}/{$icon}";
    }
}

if (!function_exists('getSizeHelpTooltipText')) {
    /**
     * return max file size string as per the collection passed
     *
     * @param  string  $collection
     * @return string
     */
    function getSizeHelpTooltipText(string $collection): string
    {
        $sizeInKB = config("zevolifesettings.fileSizeValidations.{$collection}", 2048);
        $sizeInMB = ($sizeInKB / 1024);
        return "File size: {$sizeInMB} MB max";
    }
}

if (!function_exists('getDimensionHelpTooltipText')) {
    /**
     * return recommended dimensions string as per the collection and size passed
     *
     * @param  string  $collection
     * @param  string  $size
     * @return string
     */
    function getDimensionHelpTooltipText(string $collection = ""): string
    {
        $dimension = config("zevolifesettings.imageConversions.{$collection}", ['width' => 1280, 'height' => 840]);
        return "Recommended dimensions: {$dimension['width']}x{$dimension['height']}";
    }
}

if (!function_exists('getHelpTooltipText')) {
    /**
     * return recommended dimensions and max file size help tooltip string as per the collection and size passed
     *
     * @param  string  $collection
     * @param  string  $size
     * @return string
     */
    function getHelpTooltipText(string $collection): string
    {
        $sizeText      = getSizeHelpTooltipText($collection);
        $dimensiontext = getDimensionHelpTooltipText($collection);
        return "{$dimensiontext} \n {$sizeText}";
    }
}

if (!function_exists('getBrandingData')) {
    /**
     * @sql :- ShareViewVariables {Middleware sql query}
     * @param string $companyId
     * @return stdClass
     */
    function getBrandingData($companyId = 'NULL')
    {
        $cmpbranding                 = new stdClass();
        $cmpbranding->company_name   = 'Zevo Health';
        $cmpbranding->is_reseller    = 0;
        if ($companyId !== 'NULL') {
            $branding = CompanyBranding::where('company_id', $companyId)->where('status', 1)->get()->first();
            $company  = Company::where('id', $companyId)->get()->first();
            if ($company->is_reseller == 1 || !is_null($company->parent_id)) {
                $cmpbranding->is_reseller    = 1;
                $cmpbranding->privacy_policy = 'https://irishlifeworklife.ie/privacy-notices/';
                $cmpbranding->cookie_policy  = 'https://irishlifeworklife.ie/cookies-policy/';
            }
            $cmpbranding->company_name = $company->name;
            if (null == $company || null == $branding) {
                $cmpbranding                           = new stdClass();
                $cmpbranding->company_name             = (!is_null($company) ? $company->name : 'Zevo Health');
                $cmpbranding->company_logo             = asset('assets/dist/img/zevo-white-logo.png');
                $cmpbranding->branding_logo_background = asset('assets/dist/img/login-banner.jpg');
                $cmpbranding->title                    = config('zevolifesettings.domain_branding.default_branding_title');
                $cmpbranding->description              = config('zevolifesettings.domain_branding.default_branding_description');
                $cmpbranding->url                      = url('/');
                $cmpbranding->sub_domain               = null;
                $cmpbranding->portal_domain            = null;
                $cmpbranding->status                   = 0;
            } else {
                $cmpbranding->company_logo             = $company->getBrandingLogo(['w' => 250, 'h' => 100, 'ct' => 1], false);
                $cmpbranding->branding_logo_background = $company->getBrandingLoginBackgroundLogo(['w' => 1920, 'h' => 1280, 'ct' => 0, 'zc' => 0]);
                $cmpbranding->title                    = (!empty($branding->onboarding_title)) ? $branding->onboarding_title : config('zevolifesettings.domain_branding.default_branding_title');

                $cmpbranding->description   = (!empty($branding->onboarding_description)) ? $branding->onboarding_description : config('zevolifesettings.domain_branding.default_branding_description');
                $cmpbranding->status        = 1;
                $cmpbranding->url           = url('/');
                $cmpbranding->sub_domain    = $branding->sub_domain;
                $cmpbranding->portal_domain = $branding->portal_domain;
            }
            if (!isset($cmpbranding->status)) {
                $cmpbranding->status = 0;
            }

            if (empty($cmpbranding->company_logo)) {
                $cmpbranding->company_logo = asset('assets/dist/img/zevo-white-logo.png');
            }
            return $cmpbranding;
        }

        $allowedDomainBrandingList = CompanyBranding::where("status", true)->pluck("sub_domain")->toArray();
        $host                      = request()->getHost();
        $serverNameAsArray         = explode('.', $host);
        $brandingDomainFromRequest = "";
        if (count($serverNameAsArray) == 4) {
            $brandingDomainFromRequest = $serverNameAsArray[0];
        }
        if (count($serverNameAsArray) == 3) {
            $brandingDomainFromRequest = isset($serverNameAsArray[0]) && !empty($serverNameAsArray[0]) ? $serverNameAsArray[0] : null;
        }

        if (count($serverNameAsArray) == 2) {
            $brandingDomainFromRequest = isset($serverNameAsArray[0]) && !empty($serverNameAsArray[0]) ? $serverNameAsArray[0] : null;
        }

        $cmpbranding->url          = url('/');
        $cmpbranding->company_logo = asset('assets/dist/img/full-logo.png');

        if (in_array($brandingDomainFromRequest, $allowedDomainBrandingList) || in_array($brandingDomainFromRequest, config('zevolifesettings.domain_branding.PLATFORM_DOMAIN')) || app()->environment() == "local") {
            // Branding is enable.
            $brandingObject = CompanyBranding::where('sub_domain', '=', $brandingDomainFromRequest)
                ->where('status', '=', 1)
                ->whereNotNull('company_id')
                ->get()
                ->first();
            if (null !== $brandingObject) {
                $company = Company::where('id', $brandingObject->company_id)
                    ->get()
                    ->first();
                if ($company->is_reseller == 1 || !is_null($company->parent_id)) {
                    $cmpbranding->is_reseller    = 1;
                    $cmpbranding->privacy_policy = 'https://irishlifeworklife.ie/privacy-notices/';
                    $cmpbranding->cookie_policy  = 'https://irishlifeworklife.ie/cookies-policy/';
                }
                $cmpbranding->url          = url('/');
                $cmpbranding->company_name = $company->name;
                if ($company->is_branding) {
                    $cmpbranding->company_name = $company->name;
                    $cmpbranding->company_logo = $company->getBrandingLogo(['w' => 250, 'h' => 100, 'ct' => 1], false);
                    if (empty($cmpbranding->company_logo)) {
                        $cmpbranding->company_logo = asset('assets/dist/img/full-logo.png');
                    }
                    $cmpbranding->branding_logo_background = $company->getBrandingLoginBackgroundLogo(['w' => 1920, 'h' => 1280, 'ct' => 0, 'zc' => 0]);
                    $cmpbranding->title                    = (!empty($brandingObject->onboarding_title)) ? $brandingObject->onboarding_title : config('zevolifesettings.domain_branding.default_branding_title');

                    $cmpbranding->description   = (!empty($brandingObject->onboarding_description)) ? $brandingObject->onboarding_description : config('zevolifesettings.domain_branding.default_branding_description');
                    $cmpbranding->status        = 1;
                    $cmpbranding->sub_domain    = $brandingObject->sub_domain;
                    $cmpbranding->portal_domain = $brandingObject->portal_domain;
                } else {
                    $cmpbranding->company_logo             = asset('assets/dist/img/full-logo.png');
                    $cmpbranding->branding_logo_background = asset('assets/dist/img/login-banner.jpg');
                    $cmpbranding->title                    = config('zevolifesettings.domain_branding.default_branding_title');
                    $cmpbranding->description              = config('zevolifesettings.domain_branding.default_branding_description');
                    $cmpbranding->sub_domain               = null;
                    $cmpbranding->portal_domain            = null;
                    $cmpbranding->status                   = 0;
                }
            } else {
                $cmpbranding->company_logo             = asset('assets/dist/img/full-logo.png');
                $cmpbranding->branding_logo_background = asset('assets/dist/img/login-banner.jpg');
                $cmpbranding->title                    = config('zevolifesettings.domain_branding.default_branding_title');
                $cmpbranding->description              = config('zevolifesettings.domain_branding.default_branding_description');
                $cmpbranding->sub_domain               = null;
                $cmpbranding->portal_domain            = null;
                $cmpbranding->status                   = 0;
            }
        }
        return $cmpbranding;
    }
}

if (!function_exists('getBrandingDataByCompanyId')) {
    /**
     * @param string $companyId
     * @return stdClass
     */
    function getBrandingDataByCompanyId($companyID)
    {
        $cmpbranding = new stdClass();
        $company     = Company::where('id', $companyID)
            ->get()
            ->first();
        $brandingObject = CompanyBranding::where('company_id', '=', $companyID)
            ->where('status', '=', 1)
            ->whereNotNull('company_id')
            ->get()
            ->first();
        $cmpbranding->company_name = $company->name;
        if (null !== $brandingObject) {
            $cmpbranding->company_logo = $company->getBrandingLogo(['w' => 250, 'h' => 100, 'ct' => 1], false);
            if (empty($cmpbranding->company_logo)) {
                $cmpbranding->company_logo = asset('assets/dist/img/full-logo.png');
            }
            $cmpbranding->branding_logo_background = $company->getBrandingLoginBackgroundLogo(['w' => 1920, 'h' => 1280, 'ct' => 0, 'zc' => 0]);
            $cmpbranding->title                    = (!empty($brandingObject->onboarding_title)) ? $brandingObject->onboarding_title : config('zevolifesettings.domain_branding.default_branding_title');
            $cmpbranding->description              = (!empty($brandingObject->onboarding_description)) ? $brandingObject->onboarding_description : config('zevolifesettings.domain_branding.default_branding_description');
            $cmpbranding->status                   = 1;
            $cmpbranding->sub_domain               = $brandingObject->sub_domain;
        } else {
            $cmpbranding->company_logo             = asset('assets/dist/img/full-logo.png');
            $cmpbranding->branding_logo_background = asset('assets/dist/img/login-banner.jpg');
            $cmpbranding->title                    = config('zevolifesettings.domain_branding.default_branding_title');
            $cmpbranding->description              = config('zevolifesettings.domain_branding.default_branding_description');
            $cmpbranding->sub_domain               = null;
            $cmpbranding->status                   = 0;
        }
        return $cmpbranding;
    }
}

if (!function_exists('getBrandingUrl')) {
    function getBrandingUrl($data = null, $setDomain = '')
    {
        if (is_string($data) && !empty($setDomain)) {
            $explodeString = explode('/', $data);

            $appEnvironment = app()->environment();

            if ($appEnvironment == "production") {
                $explodeString[2] = $setDomain . ".zevo.app";
            } elseif ($appEnvironment == "qa" || $appEnvironment == "uat" || $appEnvironment == "dev" || $appEnvironment == "performance") {
                $explodeString[2] = $setDomain . "." . $appEnvironment . ".zevolife.com";
            } else {
                $explodeString[2] = $setDomain . ".zevolife.local";
            }

            return implode('/', $explodeString);
        } else {
            return $data;
        }
    }
}

if (!function_exists('getDefaultDomain')) {
    function getDefaultDomain()
    {
        $defaultDomain  = "";
        $appEnvironment = app()->environment();

        if ($appEnvironment == "production") {
            $defaultDomain = "zevo.app";
        } elseif ($appEnvironment == "qa" || $appEnvironment == "uat" || $appEnvironment == "dev") {
            $defaultDomain = $appEnvironment . ".zevolife.com";
        } else {
            $defaultDomain = "zevolife.local";
        }

        return $defaultDomain;
    }
}

if (!function_exists('getThumbURL')) {
    /**
     * return dynamic thumb url as per passed params
     *
     * @param  array  $param - it contains src, height, width
     * @param  array  $param - it contains src, height, width
     * @param  array  $param - it contains src, height, width
     * @return string
     */
    function getThumbURL(array $param, string $module = "", string $collection = ""): string
    {
        /**
         * All options info are available
         * https://github.com/mindsharelabs/mthumb#mthumb-parameters
         */
        /**
         * zc - Zoom & Crop
         * 0   Resize to Fit specified dimensions (no cropping)
         * 1   Crop and resize to best fit the dimensions (default)
         * 2   Resize proportionally to fit entire image into specified dimensions, and add borders if required
         * 3   Resize proportionally adjusting size of scaled image so there are no borders gaps (Medialibrary by default crops this way)
         */
        if (!isset($param['zc'])) {
            $param['zc'] = 2;
        }
        /**
         * ct - Canvas Transparency - 0/1
         */
        if (!isset($param['ct'])) {
            $param['ct'] = 0;
        }
        if (!isset($param['h'])) {
            $param['h'] = 640;
        }
        if (!isset($param['w'])) {
            $param['w'] = 1280;
        }

        /**
         * q - quality - 0-100
         */
        if (!isset($param['q'])) {
            $param['q'] = 100;
        }

        if (isset($param['conversion'])) {
            unset($param['conversion']);
        }

        if (!isset($param['src'])) {
            $param['src'] = getDefaultFallbackImageURL($module, $collection);
        }

        // if ($collection == 'logo_image_url' || $collection == 'portal_logo_main' || $collection == 'portal_logo_optional' || $collection == 'portal_footer_logo') {
        //     $url = $param['src'];
        // } else {
        //     $url = urldecode(route('thumb-generation', $param));
        // }
        $url = $param['src'];
        $envUrl    = config('app.url');
        $returnUrl = str_replace('https://zevo-app-svc', $envUrl, $url);

        $env = config('app.env');
        if ($env != 'local') {
            $host              = request()->getHost();
            $serverNameAsArray = explode('.', $host);

            if (count($serverNameAsArray) == 4) {
                $brandingDomainFromRequest = $serverNameAsArray[0];
                $url                       = str_replace($brandingDomainFromRequest . ".", "", $url);
            } elseif (count($serverNameAsArray) == 1) {
                $url = str_replace('https://zevo-app-svc/', $envUrl, $url);
            }

            $subdomainUrl = injectSubdomain($envUrl, 'assets');
            $returnUrl    = str_replace($envUrl, $subdomainUrl, $url);
        }

        return $returnUrl;
    }
}

if (!function_exists('injectSubdomain')) {
    /**
     * return subdomain URL
     *
     * @param  string  $url
     * @param  string  $subdomain
     * @return string
     */
    function injectSubdomain($url, $subdomain)
    {
        strstr($url, 'www') ?
        $url_parts = explode('://www', $url) :
        $url_parts = explode('://', $url);

        return $url_parts[0] . '://' . $subdomain . '.' . $url_parts[1];
    }
}

if (!function_exists('getStaticNpsEmojiUrl')) {
    /**
     * return static icon url of alert according tag
     *
     * @param  string  $tag
     * @return string
     */
    function getStaticNpsEmojiUrl(string $tag): string
    {
        $disk = config('medialibrary.disk_name');
        $path = config("medialibrary.{$disk}.domain");
        $icon = config("zevolifesettings.static_image.nps_emoji.{$tag}");

        return (empty($icon)) ? getDefaultFallbackImageURL('nps', 'logo') : "{$path}/{$icon}";
    }
}

if (!function_exists('isValidJson')) {

    /**
     * @param $string
     * @return bool
     */
    function isValidJson($string)
    {
        if (isset($string) && is_string($string) && !empty($string)) {
            json_decode($string);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }

}

if (!function_exists('getScoreColor')) {
    /**
     * This function will return the color code for the specific score.
     *
     * @param $score
     *
     * @return color code - string
     */
    function getScoreColor($score = 0)
    {
        if ($score <= 0) {
            $colorCode = config('zevolifesettings.zc_survey_score_color_code.red');
        } elseif ($score >= 60 && $score < 80) {
            $colorCode = config('zevolifesettings.zc_survey_score_color_code.yellow');
        } elseif ($score >= 80 && $score <= 100) {
            $colorCode = config('zevolifesettings.zc_survey_score_color_code.green');
        } else {
            $colorCode = config('zevolifesettings.zc_survey_score_color_code.red');
        }
        return $colorCode;
    }
}

if (!function_exists('getResellerBrandingUrl')) {
    function getResellerBrandingUrl($data = null, $portalDomain = '')
    {
        if (is_string($data) && !empty($portalDomain)) {
            $explodeString = explode('/', $data);

            $appEnvironment = app()->environment();

            $explodeString[2] = $portalDomain;

            return implode('/', $explodeString);
        } else {
            return $data;
        }
    }
}

if (!function_exists('addhttp')) {
    function addhttp($url)
    {
        if ($url) {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "http://" . $url;
            }
        }
        return $url;
    }
}

if (!function_exists('removeUserFromChallengeTypeGroups')) {
    /**
     * This function will remove user from challenge type groups
     *
     * @param User $user
     * @param mixed $company - company id
     * @return bool
     */
    function removeUserFromChallengeTypeGroups(User $user, $companyId = null)
    {
        if (is_null($companyId)) {
            $companyId = $user->company()->first()->id;
        }
        $user
            ->myGroups()
            ->join('challenges', function ($join) {
                $join->on('challenges.id', '=', 'groups.model_id');
            })
            ->where(function ($query) use ($companyId) {
                $query
                    ->where('groups.company_id', $companyId)
                    ->orWhere('groups.company_id', null);
            })
            ->where('groups.model_name', 'challenge')
            ->where('challenges.challenge_type', '!=', 'individual')
            ->groupBy('group_members.group_id')
            ->each(function ($group) use ($user) {
                $group->members()->detach([$user->getKey()]);
            });
    }
}

if (!function_exists('removeFileToSpaces')) {
    /**
     * Remove file from bucket
     *
     * @param string $path
     *
     * @return boolean
     */
    function removeFileToSpaces($path)
    {
        $config = [
            'version'     => 'latest',
            'region'      => config('filesystems.disks.spaces.region'),
            'endpoint'    => config('filesystems.disks.spaces.endpoint'),
            'credentials' => [
                'key'    => config('filesystems.disks.spaces.key'),
                'secret' => config('filesystems.disks.spaces.secret'),
            ],
        ];

        $objectConfiguration = [
            'Bucket' => config('filesystems.disks.spaces.bucket'),
            'Key'    => $path,
        ];

        $client = new S3Client($config);
        $bucket = $client->deleteObject($objectConfiguration);
        return $bucket;
    }
}

if (!function_exists('portalDeeplinkURL')) {
    /**
     * Send deep link url for portal - It's for redirection.
     *
     * @param string $tag
     * @param string $title
     * @param string $deeplinkurl
     *
     * @return string $url
     */
    function portalDeeplinkURL($tag, $title, $deeplinkurl)
    {
        $url = "";
        $id  = "";
        if ($deeplinkurl) {
            $splitUrl = explode('/', $deeplinkurl);
            $id       = ($tag == 'meditation' || $tag == 'webinar' || $tag == 'consent-form') ? $splitUrl[count($splitUrl) - 2] : $splitUrl[count($splitUrl) - 1];
        }
        switch ($tag) {
            case 'recipe':
                $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.recipe'));
                break;
            case 'masterclass':
                if ($title == trans('notifications.masterclass.csat.title')) {
                    $url = __(config('zevolifesettings.portal_notification.csat_masterclass'), [
                        'id' => $id,
                    ]);
                } else {
                    $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.masterclass'));
                }
                break;
            case 'meditation':
                $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.meditation'));
                break;
            case 'webinar':
                $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.webinar'));
                break;
            case 'event':
                $url = config('zevolifesettings.portal_notification.event');
                if ($title == trans('notifications.events.event-registered.title') || $title == trans('notifications.events.event-reminder-tomorrow.title') || $title == trans('notifications.events.event-reminder-today.title') || $title == trans('notifications.events.event-updated.title') || $title == trans('notifications.events.event-added.title')) {
                    $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.event_registered'));
                } elseif ($title == trans('notifications.events.csat.title')) {
                    $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.csat_event'));
                }
                break;
            case 'eap':
                $url = config('zevolifesettings.portal_notification.eap');
                break;
            case 'feed':
                $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.feed'));
                break;
            case 'survey':
                $url = config('zevolifesettings.portal_notification.csat');
                break;
            case 'audit-survey':
                $url = __(config('zevolifesettings.portal_notification.audit_survey'), [
                    'id' => $id,
                ]);
                break;
            case 'eap-completed':
                $url = config('zevolifesettings.portal_notification.eap_feedback');
                break;
            case 'new-eap':
                $url = __(config('zevolifesettings.portal_notification.new_eap'), [
                    'id' => $id,
                ]);
                break;
            case 'digital-therapy':
                $url = str_replace('{id}', $id, config('zevolifesettings.portal_notification.digital_therapy'));
                break;
            case 'consent-form':
                $type     = $splitUrl[count($splitUrl) - 1];
                $urlRoute = ($type == 1) ? config('zevolifesettings.portal_notification.consent_form_online') : config('zevolifesettings.portal_notification.consent_form');
                $url      = __($urlRoute, [
                    'id'   => $id,
                    'type' => $type,
                ]);
                break;
            default:
                $url = "";
                break;
        }
        return $url;
    }
}
if (!function_exists('portalSurveyColorCode')) {
    /**
     * Send color code for portal survey category as per score
     *
     * @param  int $percentage
     *
     * @return string $colorCode
     */
    function portalSurveyColorCode($percentage)
    {
        $colorCode = config('zevolifesettings.portal_survey_color_code.0-60');
        if ($percentage >= 80 && $percentage <= 100) {
            $colorCode = config('zevolifesettings.portal_survey_color_code.80-100');
        } elseif ($percentage >= 60 && $percentage <= 79) {
            $colorCode = config('zevolifesettings.portal_survey_color_code.60-80');
        }

        return $colorCode;
    }
}

if (!function_exists('readFileToSpaces')) {
    /**
     * Read content file from bucket
     *
     * @param string $path
     *
     * @return boolean
     */
    function readFileToSpaces($path)
    {
        $config = [
            'version'     => 'latest',
            'region'      => config('filesystems.disks.spaces.region'),
            'endpoint'    => config('filesystems.disks.spaces.endpoint'),
            'credentials' => [
                'key'    => config('filesystems.disks.spaces.key'),
                'secret' => config('filesystems.disks.spaces.secret'),
            ],
        ];

        $objectConfiguration = [
            'Bucket' => config('filesystems.disks.spaces.bucket'),
            'Key'    => $path,
        ];

        $client = new S3Client($config);
        $result = $client->getObject($objectConfiguration);
        return $result['Body']->getContents();
    }
}
if (!function_exists('getIdFromVimeoURL')) {
    /**
     * Get Id from vimeo url
     *
     * @param string $path
     *
     * @return boolean
     */
    function getIdFromVimeoURL($vimeo, $type = null)
    {
        if (!empty($type) && $type == 'shorts') {
            $url  = parse_url($vimeo);
            $path = explode("/", $url['path']);
            return !empty($path[3]) ? $path[3] : null;
        } else {
            if (preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/?(showcase\/)*([0-9))([a-z]*\/)*([0-9]{6,11})[?]?.*/", $vimeo, $output_array)) {
                return $output_array[6];
            }
        }
        
    }
}
if (!function_exists('getVimeoThumb')) {
    /**
     * Gets a vimeo thumbnail url
     * @param mixed $id A vimeo id (ie. 1185346)
     * @return thumbnail's url
     */
    function getVimeoThumb($id)
    {
        $data = file_get_contents("http://vimeo.com/api/v2/video/$id.json");
        $data = json_decode($data);
        return $data[0]->thumbnail_large;
    }
}
if (!function_exists('getPortalDomain')) {
    /**
     * Get Portal Domain Dropdown value
     * @return array
     */
    function getPortalDomain()
    {
        $appEnv = config('app.env');
        return config("zevolifesettings.portal_domain.{$appEnv}", []);
    }
}
if (!function_exists('generateiCal')) {
    /**
     * Function for iCal generation
     * @return string
     */
    function generateiCal($data, $status = "confirmed")
    {
        $iCal      = [];
        $userEmail = "test@yopmail.com";
        $data      = (object) $data;
        // validate all required fields are exist or not
        if (!isset($data->inviteTitle) || !isset($data->appName) || !isset($data->timezone) || !isset($data->startTime) || !isset($data->endTime) || !isset($data->today) || !isset($data->uid) || !isset($data->orgName) || !isset($data->orgEamil) || !isset($data->description)) {
            return "";
        }

        if (isset($data->userEmail)) {
            $userEmail = $data->userEmail;
        }
        // prepare iCal
        if ($status == "confirmed") {
            $sequence = (isset($data->sequence) ? $data->sequence : 0);
            $iCal     = [
                "BEGIN:VCALENDAR",
                "PRODID:-//{$data->inviteTitle}//{$data->appName}//EN",
                "VERSION:2.0",
                "X-WR-CALNAME:{$data->inviteTitle}",
                "X-WR-TIMEZONE:{$data->timezone}",
                "NAME:{$data->inviteTitle}",
                "CALSCALE:GREGORIAN",
                "METHOD:REQUEST",
                "BEGIN:VEVENT",
                "DTSTART:{$data->startTime}",
                "DTEND:{$data->endTime}",
                "DTSTAMP:{$data->today}",
                "ORGANIZER;CN={$data->orgName}:mailto:{$data->orgEamil}",
                "UID:{$data->uid}",
                "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=
                TRUE;CN={$userEmail};X-NUM-GUESTS=0:mailto:{$userEmail}",
                "CREATED:{$data->today}",
                "DESCRIPTION:{$data->description}",
                "LAST-MODIFIED:{$data->today}",
                "SEQUENCE:{$sequence}",
                "STATUS:CONFIRMED",
                "SUMMARY:{$data->inviteTitle}",
                "TRANSP:OPAQUE",
                "END:VEVENT",
                "END:VCALENDAR",
            ];
        } elseif ($status == "cancelled") {
            $method   = "REQUEST";
            if (strpos($userEmail, 'gmail') !== false) {
                $method = "CANCEL";
            }

            $iCal = [
                "BEGIN:VCALENDAR",
                "PRODID:-//{$data->inviteTitle}//{$data->appName}//EN",
                "VERSION:2.0",
                "CALSCALE:GREGORIAN",
                "METHOD:{$method}",
                "BEGIN:VEVENT",
                "DTSTART:{$data->startTime}",
                "DTEND:{$data->endTime}",
                "DTSTAMP:{$data->today}",
                "UID:{$data->uid}",
                "ORGANIZER;CN={$data->orgName}:mailto:{$data->orgEamil}",
                "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;CN={$userEmail};X-NUM-GUESTS=0:mailto:{$userEmail}",
                "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;CN={$data->orgEamil};X-NUM-GUESTS=0:mailto:{$data->orgEamil}",
                "CREATED:{$data->today}",
                "DESCRIPTION:{$data->description}",
                "LAST-MODIFIED:{$data->today}",
                "SEQUENCE:1",
                "STATUS:CANCELLED",
                "SUMMARY:{$data->inviteTitle}",
                "TRANSP:TRANSPARENT",
                "END:VEVENT",
                "END:VCALENDAR",
            ];
        }

        // return iCal string
        return implode("\r\n", $iCal);
    }
}

if (!function_exists('generateUniqueTeamNames')) {
    /**
     * Function for to generate series team name
     *
     * @param string $template
     * @param int $limit
     * @param int $departmentId
     * @param int $companyLocationId
     * @return array
     */
    function generateUniqueTeamNames($template, $limit, $departmentId, $companyLocationId)
    {
        $names    = [];
        $sequence = 1;

        // Generate team names with sequence like $template-{$sequence} based on limit
        for ($i = 1; $i <= $limit; $i++) {
            $exist = 0;

            // check is same $template-{$sequence} pattern team exist then skip that number and take +1
            do {
                $name  = "$template-{$sequence}";
                $exist = Team::where('name', 'like', "$name%")
                    ->where('department_id', $departmentId)
                    ->whereHas('teamlocation', function ($query) use ($companyLocationId) {
                        $query->where('company_locations.id', $companyLocationId);
                    })
                    ->count('id');
                if ($exist > 0) {
                    $sequence++;
                }
            } while ($exist > 0);

            $names[] = $name;

            // increase sequence by +1
            $sequence++;
        }
        return $names;
    }
}

if (!function_exists('getBrandingUrlSurvey')) {
    function getBrandingUrlSurvey($setDomain = '')
    {
        if (!empty($setDomain)) {
            $appEnvironment = app()->environment();

            if ($appEnvironment == "production") {
                $explodeString[2] = $setDomain . ".zevo.app";
            } elseif ($appEnvironment == "qa" || $appEnvironment == "uat" || $appEnvironment == "dev" || $appEnvironment == "performance") {
                $explodeString[2] = $setDomain . "." . $appEnvironment . ".zevolife.com";
            } else {
                $explodeString[2] = $setDomain . ".zevolife.local";
            }

            return implode('/', $explodeString);
        } else {
            return $setDomain;
        }
    }
}

/**
 * Get Company Plan Records
 * @param $user
 * return array
 */
if (!function_exists('getCompanyPlanRecords')) {
    function getCompanyPlanRecords($user)
    {
        $xDeviceOs   = request()->header('X-Device-Os');
        $companyCode = Request()->get('companyCode');
        $company     = $user->company()->first();
        if (is_null($company) && !is_null($companyCode)) {
            $company = Company::where('code', $companyCode)->first();
        }
        $companyPlanGroupType = ($xDeviceOs == config('zevolifesettings.PORTAL') ? 2 : 1);
        $featuresList         = [];
        /*if (!empty($company) && $company->parent_id != '') {
        $company = Company::where('id', $company->parent_id)->first();
        }*/
        $companyPlan = $company->companyplan()->first();
        $defaultPlan = config('zevolifesettings.default_plan');

        if (!empty($companyPlan)) {
            if (($company->is_reseller || !is_null($company->parent_id)) && $xDeviceOs != config('zevolifesettings.PORTAL')) {
                $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
            } else {
                $companyPlanFeature = $companyPlan->planFeatures()->select('feature_id')->get()->pluck('feature_id')->toArray();
            }
        } else {
            $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
        }
        $parentFeatures = CpFeatures::select('id', 'parent_id', 'name', 'slug', 'manage')->where('parent_id', null)->where('group', $companyPlanGroupType)->get();
        foreach ($parentFeatures as $value) {
            $result = CpFeatures::select('id', 'name', 'slug')->where('parent_id', $value->id)->get()->toArray();
            if (!empty($result)) {
                $tempArray = [];
                foreach ($result as $childvalue) {
                    $slug             = str_replace('-', '_', $childvalue['slug']);
                    $tempArray[$slug] = (in_array($childvalue['id'], $companyPlanFeature));
                }
                $slug                = str_replace('-', '_', $value->slug);
                $featuresList[$slug] = $tempArray;
            } else {
                $slug                = str_replace('-', '_', $value->slug);
                $featuresList[$slug] = (in_array($value->id, $companyPlanFeature));
            }
        }

        return $featuresList;
    }
}

/**
 * Get Company Plan Access
 * @param $user
 * return array
 */
if (!function_exists('getCompanyPlanAccess')) {
    function getCompanyPlanAccess($user, $module = '', $company = [])
    {
        // For zevo and reseller role have full access from company.
        $xDeviceOs   = request()->header('X-Device-Os');
        $result      = true;
        $defaultPlan = config('zevolifesettings.default_plan');
        if (!empty($user)) {
            $company = $user->company()->first();
            if (!empty($company) && ($company->parent_id != '' || ($company->is_reseller && is_null($company->parent_id)))) {
                $featureGroup = 2;
            } else {
                $featureGroup = 1;
            }
            $role = getUserRole($user);
            if (!empty($company) && ($role->group == 'company' || $role->group == 'reseller')) {
                $result      = false;
                $companyPlan = $company->companyplan()->first();
                if (!empty($companyPlan)) {
                    if (($company->is_reseller || !is_null($company->parent_id)) && !empty($xDeviceOs) && $xDeviceOs != config('zevolifesettings.PORTAL')) {
                        $featureGroup       = 1;
                        $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
                    } else {
                        $companyPlanFeature = $companyPlan->planFeatures()->select('feature_id')->get()->pluck('feature_id')->toArray();
                    }
                } else {
                    $featureGroup       = 1;
                    $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
                }
                $moduleFeatures  = CpFeatures::select('id')->where('slug', $module)->where('group', $featureGroup)->get()->first();
                $modelFeaturesId = !empty($moduleFeatures) ? $moduleFeatures->id : 0;
                if (in_array($modelFeaturesId, $companyPlanFeature)) {
                    $result = true;
                }
            }
        } elseif (!empty($company)) {
            if (($company->is_reseller && is_null($company->parent_id)) || $company->is_reseller == false || !is_null($company->parent_id)) {
                $result = false;
                if (!empty($company) && ($company->parent_id != '' || ($company->is_reseller && is_null($company->parent_id)))) {
                    $featureGroup = 2;
                } else {
                    $featureGroup = 1;
                }

                $companyPlan = $company->companyplan()->first();
                if (!empty($companyPlan)) {
                    if (($company->is_reseller || !is_null($company->parent_id)) && $xDeviceOs != config('zevolifesettings.PORTAL')) {
                        $featureGroup       = 1;
                        $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
                    } else {
                        $companyPlanFeature = $companyPlan->planFeatures()->select('feature_id')->get()->pluck('feature_id')->toArray();
                    }
                } else {
                    $featureGroup       = 1;
                    $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
                }
                $moduleFeatures  = CpFeatures::select('id')->where('slug', $module)->where('group', $featureGroup)->get()->first();
                $modelFeaturesId = !empty($moduleFeatures) ? $moduleFeatures->id : 0;
                if (in_array($modelFeaturesId, $companyPlanFeature)) {
                    $result = true;
                }
            }
        }
        return $result;
    }
}

if (!function_exists('getStaticGroupIconUrl')) {
    /**
     * return static icon url of alert according tag
     *
     * @param  string  $tag
     * @return string
     */
    function getStaticGroupIconUrl(string $tag): string
    {
        $disk = config('medialibrary.disk_name');
        $path = config("medialibrary.{$disk}.domain");
        $icon = config("zevolifesettings.static_image.group_icons.{$tag}");

        return (empty($icon)) ? getDefaultFallbackImageURL('group', 'logo') : "{$path}/{$icon}";
    }
}

if (!function_exists('splitTime')) {
    /**
     * split the slots as per the time durations
     *
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $duration
     * @return string
     */
    function splitTime($startTime, $endTime, $duration)
    {
        $returnArray = array(); // Define output
        $startTime   = strtotime($startTime); //Get Timestamp
        $endTime     = strtotime($endTime); //Get Timestamp

        $addMins = $duration * 60;
        $i       = 0;
        while ($startTime <= $endTime) {
            //Run loop
            $start = $startTime;
            $startTime += $addMins; //Endtime check
            if ($startTime <= $endTime) {
                $returnArray[$i]['date'] = date("Y-m-d", $startTime);
                $returnArray[$i]['from'] = date("h:i A", $start);
                $returnArray[$i]['to']   = date("h:i A", $startTime);
            }
            $i++;
        }
        return $returnArray;
    }
}

if (!function_exists('ConvertHoursToMinutes')) {
    /**
     * convert the hours into minutes
     *
     * @param  string  $startTime
     * @param  string  $endTime
     * @param  string  $duration
     * @return string
     */
    function convertHoursToMinutes($string)
    {
        $hour          = substr($string, 0, 2); // we get the first two values from the hh:mm:ss string
        $hour          = (int) $hour;
        $hourtomin     = $hour * 60; // after we have the hour we multiply by 60 to get the min
        $min           = substr($string, 3, 5); //now we do a substring 3 to 4 because we want to get only the min, and we don't want to get the : which is in position 2
        $min           = (int) $min;
        $totalDuration = (int) $hourtomin + (int) $min;
        return $totalDuration ?? 0;
    }
}
if (!function_exists('multiArraySearch')) {
    /**
     * find multi array search
     *
     * @param  string  $value
     * @param  string  $key
     * @param  array  $array
     * @return string
     */
    function multiArraySearch($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, multiArraySearch($subarray, $key, $value));
            }
        }

        return $results;
    }
}
if (!function_exists('alignedAvailability')) {
    /**
     * set availability aligned
     *
     * @param  string  $array
     * @param  string  $array1
     * @return string
     */
    function alignedAvailability($companyAvailability, $userAvailability)
    {
        $results = $resultsUser = array();
        $today   = Carbon::today()->toDateString();
        foreach ($userAvailability as $users) {
            if (in_array($users['day'], array_column($companyAvailability, 'day'))) {
                $companyKey = array_search($users['day'], array_column($companyAvailability, 'day'));

                $comTest = $companyAvailability[$companyKey];
                if (Carbon::parse($users['start_time'])->timestamp < Carbon::parse($comTest['start_time'])->timestamp) {
                    $startTime = $comTest['start_time'];
                } else {
                    $startTime = $users['start_time'];
                }
                if (Carbon::parse($users['end_time'])->format("H:i") == '00:00') {
                    $users['end_time'] = "24:00:00";
                }
                if (Carbon::parse($comTest['end_time'])->format("H:i") == '00:00') {
                    $comTest['end_time'] = "24:00:00";
                }
                if (Carbon::parse($users['end_time'])->timestamp > Carbon::parse($comTest['end_time'])->timestamp) {
                    $endTime = $comTest['end_time'];
                } else {
                    $endTime = $users['end_time'];
                }
                $finalStartTime = Carbon::parse($today . ' ' . $startTime)->format('H:i:s');
                if ($endTime != '24:00:00') {
                    $finalEndTime   = Carbon::parse($today . ' ' . $endTime)->format('H:i:s');
                } else {
                    $finalEndTime = '24:00:00';
                }
                if (Carbon::parse($finalStartTime)->timestamp < Carbon::parse($finalEndTime)->timestamp) {
                    $resultsUser[] = [
                        'day'        => $users['day'],
                        'start_time' => Carbon::parse($today . ' ' . $startTime)->format('H:i:s'),
                        'end_time'   => Carbon::parse($today . ' ' . $endTime)->format('H:i:s'),
                    ];
                }
            }
        }

        foreach ($companyAvailability as $users) {
            if (in_array($users['day'], array_column($userAvailability, 'day'))) {
                $companyKey = array_search($users['day'], array_column($userAvailability, 'day'));
                $comTest    = $userAvailability[$companyKey];
                if (Carbon::parse($users['start_time'])->timestamp < Carbon::parse($comTest['start_time'])->timestamp) {
                    $startTime = $comTest['start_time'];
                } else {
                    $startTime = $users['start_time'];
                }
                if (Carbon::parse($users['end_time'])->format("H:i") == '00:00') {
                    $users['end_time'] = "24:00:00";
                }
                if (Carbon::parse($comTest['end_time'])->format("H:i") == '00:00') {
                    $comTest['end_time'] = "24:00:00";
                }
                if (Carbon::parse($users['end_time'])->timestamp >= Carbon::parse($comTest['end_time'])->timestamp) {
                    $endTime = $comTest['end_time'];
                } else {
                    $endTime = $users['end_time'];
                }
                $finalStartTime = Carbon::parse($today . ' ' . $startTime)->format('H:i:s');
                if ($endTime != '24:00:00') {
                    $finalEndTime   = Carbon::parse($today . ' ' . $endTime)->format('H:i:s');
                } else {
                    $finalEndTime = '24:00:00';
                }
                if (Carbon::parse($finalStartTime)->timestamp <= Carbon::parse($finalEndTime)->timestamp) {

                    $startTime = Carbon::parse($today . ' ' . $startTime)->format('H:i:s');
                    $endTime   = Carbon::parse($today . ' ' . $endTime)->format('H:i:s');
                    $flag      = true;
                    if (!empty($results) && in_array($users['day'], array_column($results, 'day'))) {
                        $key = array_search($users['day'], array_column($results, 'day'));
                        if (Carbon::parse($startTime)->timestamp == Carbon::parse($results[$key]['start_time'])->timestamp && Carbon::parse($endTime)->timestamp == Carbon::parse($results[$key]['end_time'])->timestamp) {
                            $flag = false;
                        }

                    }
                    if ($flag) {
                        $results[] = [
                            'day'        => $users['day'],
                            'start_time' => $startTime,
                            'end_time'   => $endTime,
                        ];
                    }
                }
            }
        }
        if (count($results) < count($resultsUser)) {
            $results = $resultsUser;
        }

        if (empty($results)) {
            $mytime     = Carbon::now();
            $start_time = $mytime->toTimeString();
            $end_time   = $mytime->addSecond(1)->toTimeString();
            $results    = [
                [
                    'day'        => 'mon',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
                [
                    'day'        => 'tue',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
                [
                    'day'        => 'wed',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
                [
                    'day'        => 'thu',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
                [
                    'day'        => 'fri',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
                [
                    'day'        => 'sat',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
                [
                    'day'        => 'sun',
                    'start_time' => $start_time,
                    'end_time'   => $end_time,
                ],
            ];
        }
        return $results;
    }
}
if (!function_exists('generateQueryPeriod')) {
    /**
     * Generate Query period for UI element
     *
     * @param array  $combinedAvailability
     * @param string $appTimezone
     * @param string $startTime
     * @param string $endTime
     * @param string $timezone
     * @return string
     */
    function generateQueryPeriod(array $combinedAvailability, $startTime, $endTime, string $appTimezone, string $timezone, array $healthCoachUnavailable = [], $duration = 30, $specificAvailabilities = [])
    {
        $queryPeriod = array();
        $begin       = Carbon::parse($startTime)->toDateString();
        $end         = Carbon::parse($endTime)->toDateString();
        $period      = CarbonPeriod::create($begin, $end);

        if (!empty($combinedAvailability)) {
            // Excusive specific availability from general Availability
            $avilableDayList = array_column($combinedAvailability, 'day');
            // Iterate over the period
            foreach ($period as $date) {
                $availabilityFlag = true;
                $tempArray        = array();
                $day              = strtolower($date->format('D'));
                if (!empty($healthCoachUnavailable)) {
                    foreach ($healthCoachUnavailable as $unavailable) {
                        $startDate = Carbon::parse($unavailable['from_date'])->setTimezone($timezone)->format('Y-m-d');
                        $endDate   = Carbon::parse($unavailable['to_date'])->setTimezone($timezone)->format('Y-m-d');
                        $checkDate = Carbon::parse($date)->format('Y-m-d');
                        if (($startDate <= $checkDate && $endDate >= $checkDate)) {
                            $availabilityFlag = false;
                        }
                    }
                }
                if (in_array($day, $avilableDayList) && $availabilityFlag) {
                    $key = array_keys($avilableDayList, $day);
                    foreach ($key as $value) {
                        $keySetAvailability = $combinedAvailability[$value];
                        $tempDate           = $date->toDateString();
                        $tempStartTime      = Carbon::parse($tempDate . ' ' . $keySetAvailability['start_time'], $timezone)->setTimezone($appTimezone)->toDateTimeString();
                        if (Carbon::parse($keySetAvailability['end_time'])->format('H:i') != '00:00') {
                            $tempEndTime = Carbon::parse($tempDate . ' ' . $keySetAvailability['end_time'], $timezone)->setTimezone($appTimezone)->toDateTimeString();
                        } else {
                            $tempDate    = Carbon::parse($tempDate)->addDays(1)->toDateString();
                            $tempEndTime = Carbon::parse($tempDate . ' 00:00', $timezone)->setTimezone($appTimezone)->toDateTimeString();
                        }
                        $queryPeriodFlag = true;
                        if (!empty($specificAvailabilities)) {
                            foreach ($specificAvailabilities as $sValue) {
                                $sStartDate = Carbon::parse($sValue['from_date'], $timezone)->setTimezone($appTimezone)->toDateTimeString();
                                $sEndDate   = Carbon::parse($sValue['to_date'], $timezone)->setTimezone($appTimezone)->toDateTimeString();

                                if ($tempStartTime <= $sStartDate && $tempEndTime >= $sEndDate) {

                                    $queryPeriodFlag = false;
                                    if (strtotime($sStartDate) > strtotime($tempStartTime)) {
                                        $startTime         = $tempStartTime;
                                        $endTime           = $sStartDate;
                                        $dateCheckDuration = Carbon::parse($startTime);
                                        $diff              = $dateCheckDuration->diffInMinutes($endTime);
                                        if ($diff >= $duration) {
                                            $tempArray['start'] = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                                            $tempArray['end']   = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));

                                            $queryPeriod[] = $tempArray;
                                        }
                                    }
                                    if (strtotime($tempEndTime) > strtotime($sEndDate)) {
                                        $startTime = $sEndDate;
                                        $endTime   = $tempEndTime;

                                        $dateCheckDuration = Carbon::parse($startTime);
                                        $diff              = $dateCheckDuration->diffInMinutes($endTime);
                                        if ($diff >= $duration) {
                                            $tempArray['start'] = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                                            $tempArray['end']   = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
                                            $queryPeriod[]      = $tempArray;
                                        }
                                    }
                                    if ((strtotime($sStartDate) > strtotime($tempStartTime)) && (strtotime($tempEndTime) > strtotime($sEndDate))) {
                                        $startTime = $tempStartTime;
                                        $endTime   = $sStartDate;

                                        $dateCheckDuration = Carbon::parse($startTime);
                                        $diff              = $dateCheckDuration->diffInMinutes($endTime);
                                        if ($diff >= $duration) {
                                            $tempArray['start'] = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                                            $tempArray['end']   = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
                                            $queryPeriod[]      = $tempArray;
                                        }
                                    }

                                } else {
                                    $chStartTime    = Carbon::createFromFormat('Y-m-d H:i:s', $tempStartTime);
                                    $chEndTime      = Carbon::createFromFormat('Y-m-d H:i:s', $tempEndTime);
                                    $checkEndFlag   = Carbon::parse($sEndDate)->between($chStartTime, $chEndTime);
                                    $checkStartFlag = Carbon::parse($sStartDate)->between($chStartTime, $chEndTime);

                                    if ($checkEndFlag) {
                                        $startTime = $sEndDate;
                                        $endTime   = $tempEndTime;
                                    } elseif ($checkStartFlag) {
                                        $startTime = $tempStartTime;
                                        $endTime   = $sStartDate;
                                    }
                                    if ($checkStartFlag || $checkEndFlag) {
                                        $queryPeriodFlag   = false;
                                        $dateCheckDuration = Carbon::parse($startTime);
                                        $diff              = $dateCheckDuration->diffInMinutes($endTime);
                                        if ($diff >= $duration) {
                                            $tempArray['start'] = date("Y-m-d\TH:i:s.000\Z", strtotime($startTime));
                                            $tempArray['end']   = date("Y-m-d\TH:i:s.000\Z", strtotime($endTime));
                                            $queryPeriod[]      = $tempArray;
                                        }
                                    }
                                }
                            }
                        }
                        if ($tempStartTime < $startTime) {
                            $tempStartTime = $startTime;
                        }
                        if ($tempEndTime >= $startTime && $tempEndTime > $tempStartTime && $queryPeriodFlag) {
                            $dateCheckDuration = Carbon::parse($tempStartTime);
                            $diff              = $dateCheckDuration->diffInMinutes($tempEndTime);
                            if ($diff >= $duration) {
                                $tempArray['start'] = date("Y-m-d\TH:i:s.000\Z", strtotime($tempStartTime));
                                $tempArray['end']   = date("Y-m-d\TH:i:s.000\Z", strtotime($tempEndTime));

                                $queryPeriod[] = $tempArray;
                            }
                        }
                    }
                }
            }
        }
        return $queryPeriod;
    }
}
/**
 * Get Company Plan Records when company code is verified
 * @param $company
 * return array
 */
if (!function_exists('getCompanyPlanRecordsForVerifyCompanyCode')) {
    function getCompanyPlanRecordsForVerifyCompanyCode($company)
    {
        $xDeviceOs            = request()->header('X-Device-Os');
        $companyPlanGroupType = ($xDeviceOs == config('zevolifesettings.PORTAL') ? 2 : 1);
        $featuresList         = [];
        $companyPlan = $company->companyplan()->first();

        $defaultPlan = config('zevolifesettings.default_plan');

        if (!empty($companyPlan)) {
            if (($company->is_reseller || !is_null($company->parent_id)) && $xDeviceOs != config('zevolifesettings.PORTAL')) {
                $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
            } else {
                $companyPlanFeature = $companyPlan->planFeatures()->select('feature_id')->get()->pluck('feature_id')->toArray();
            }
        } else {
            $companyPlanFeature = DB::table('cp_plan_features')->select('feature_id')->where('plan_id', $defaultPlan)->get()->pluck('feature_id')->toArray();
        }
        $parentFeatures = CpFeatures::select('id', 'parent_id', 'name', 'slug', 'manage')->where('parent_id', null)->where('group', $companyPlanGroupType)->get();
        foreach ($parentFeatures as $value) {
            $result = CpFeatures::select('id', 'name', 'slug')->where('parent_id', $value->id)->get()->toArray();
            if (!empty($result)) {
                $tempArray = [];
                foreach ($result as $childvalue) {
                    $slug             = str_replace('-', '_', $childvalue['slug']);
                    $tempArray[$slug] = (in_array($childvalue['id'], $companyPlanFeature));
                }
                $slug                = str_replace('-', '_', $value->slug);
                $featuresList[$slug] = $tempArray;
            } else {
                $slug                = str_replace('-', '_', $value->slug);
                $featuresList[$slug] = (in_array($value->id, $companyPlanFeature));
            }
        }

        return $featuresList;
    }
}

if (!function_exists('generateRealTimeQueryPeriod')) {
    /**
     * Generate Query period for UI element
     *
     * @param string $startTime
     * @param string $endTime
     * @param array $healthCoachUnavailable
     * @return string
     */
    function generateRealTimeQueryPeriod($startTime, $endTime, array $healthCoachUnavailable = [])
    {
        $appTimezone       = config('app.timezone');
        $user              = Auth::user();
        $timezone          = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $customQueryPeriod = [];
        foreach ($healthCoachUnavailable as $unavailable) {
            if (!empty($customQueryPeriod)) {
                foreach ($customQueryPeriod as $key => $queryPeriod) {
                    $fromDate = Carbon::parse($unavailable['from_date'], $appTimezone)->setTimezone($timezone)->toDateString();
                    $toDate   = Carbon::parse($unavailable['to_date'], $appTimezone)->setTimezone($timezone)->toDateString();
                    $startT   = Carbon::parse($queryPeriod['start'])->toDateString();
                    $endT     = Carbon::parse($queryPeriod['end'])->toDateString();
                    if ($startT <= $fromDate && $endT >= $toDate) {
                        unset($customQueryPeriod[$key]);
                        $customQueryPeriod[] = [
                            "start" => $queryPeriod['start'],
                            "end"   => $unavailable['from_date'],
                        ];
                        $customQueryPeriod[] = [
                            "start" => $unavailable['to_date'],
                            "end"   => $queryPeriod['end'],
                        ];
                    }
                }
            } else {
                $fromDate = Carbon::parse($unavailable['from_date'], $appTimezone)->setTimezone($timezone)->toDateString();
                $toDate   = Carbon::parse($unavailable['to_date'], $appTimezone)->setTimezone($timezone)->toDateString();
                $startT   = Carbon::parse($startTime)->toDateString();
                $endT     = Carbon::parse($endTime)->toDateString();
                if ($startT <= $fromDate && $endT >= $toDate) {
                    $customQueryPeriod[] = [
                        "start" => $startTime,
                        "end"   => $unavailable['from_date'],
                    ];
                    $customQueryPeriod[] = [
                        "start" => $unavailable['to_date'],
                        "end"   => $endTime,
                    ];
                }
            }
        }
        return (array) $customQueryPeriod;
    }
}

if (!function_exists('UpdatePointContentActivities')) {
    /**
     * Update point based on content activities
     *
     * @param string $modelType
     * @param int $id
     * @param int $userId
     * @param string $activity
     * @return boolean
     */
    function UpdatePointContentActivities($modelType, $id, $userId, $activity, $flag = false, $extraPointFlag = true)
    {
        $timezone        = config('app.timezone');
        $modelId         = config('zevolifesettings.content_challenge_points.default_limit_per_day.' . $modelType . '.id');
        $point           = config('zevolifesettings.content_challenge_points.' . $modelType . '.' . $activity);
        $currentDateTime = now($timezone)->toDateTimeString();
        $todayDate       = now($timezone)->toDateString();
        $contentName     = config('zevolifesettings.content_challenge_points.contents.' . $modelType);
        // Already added point for same content and same day
        $anyExist = ContentPointCalculation::where([
            'user_id'    => $userId,
            'category'   => $modelType,
            'target_id'  => $id,
            'activities' => $activity,
        ])->whereDate('log_date', $todayDate)->count();

        if ($anyExist <= 0) {
            $limitCheck = ($flag) ? config('zevolifesettings.content_challenge_points.default_limit_per_day.' . $modelType . '_' . $activity) : config('zevolifesettings.content_challenge_points.default_limit_per_day.' . $modelType);

            // Get data from backend which admin had added.
            $contentChallengeActivity = ContentChallengeActivity::where('category_id', $modelId)
                ->select(
                    DB::raw('REPLACE(LOWER(activity), " ", "_") as activity'),
                    'daily_limit',
                    'points_per_action'
                )
                ->get()
                ->toArray();
            if (!empty($contentChallengeActivity)) {
                $contentKey          = array_search($activity, array_column($contentChallengeActivity, 'activity'));
                $limitCheck['limit'] = $contentChallengeActivity[$contentKey]['daily_limit'];
                $point               = $contentChallengeActivity[$contentKey]['points_per_action'];
            }

            // Check how many point already as per day
            $dailyLimit = ContentPointCalculation::where([
                'user_id'  => $userId,
                'category' => $modelType,
            ])->where('activities', $activity)->whereDate('log_date', $todayDate)->count();

            if ((int) $dailyLimit < (int) $limitCheck['limit']) {
                // Check condition based on daily limit
                $extraPoints = ($flag) ? config('zevolifesettings.content_challenge_points.default_limit_per_day.' . $modelType . '_' . $activity . '.extraPoint') : config('zevolifesettings.content_challenge_points.default_limit_per_day.' . $modelType . '.extraPoint');

                if ($extraPoints > 0) {
                    $point = ($extraPointFlag) ? $point * $extraPoints : $point;
                }

                $content = ContentChallenge::where('category', $contentName)->select('id')->first();
                return ContentPointCalculation::create([
                    'user_id'     => $userId,
                    'category'    => $modelType,
                    'category_id' => $content->id,
                    'target_id'   => $id,
                    'activities'  => $activity,
                    'points'      => $point,
                    'log_date'    => $currentDateTime,
                ]);
            }
        }
        return $anyExist;
    }
}

if (!function_exists('RemovePointContentActivities')) {
    /**
     * Update point based on content activities
     *
     * @param string $modelType
     * @param int $id
     * @param int $userId
     * @param string $activity
     * @return boolean
     */
    function RemovePointContentActivities($modelType, $id, $userId, $activity)
    {
        return ContentPointCalculation::where([
            'user_id'    => $userId,
            'category'   => $modelType,
            'target_id'  => $id,
            'activities' => $activity,
        ])->delete();
    }
}

if (!function_exists('GetUserCountBasedOnFields')) {
    /**
     * Get user count based on fields for irish life data extract
     *
     * @param string $subcategoryKey
     * @param int $companyId
     * @param string $type
     * @param string $countType
     * @return array
     */
    function GetUserCountBasedOnFields($subcategoryKey, $companyId, $type, $countType = 'number')
    {
        $age      = [];
        $lastDays = null;
        if ($type != 'active' && str_contains($subcategoryKey, '-')) {
            $age = explode('-', $subcategoryKey);
        }
        if ($type == 'active') {
            $lastDays = Carbon::parse(now()->toDateTimeString())->subDays($subcategoryKey)->format('Y-m-d 00:00:00');
        }
        $records = User::join('user_profile', 'user_profile.user_id', 'users.id')
            ->join('user_team', 'user_team.user_id', 'users.id');
        if ($countType == 'avg') {
            $records->select(
                DB::raw("AVG(user_profile.age) as users_count"),
                'user_profile.gender'
            );
        } else {
            $records->select(
                DB::raw("COUNT(users.id) as users_count"),
                'user_profile.gender'
            );
        }
        $records->where('user_team.company_id', $companyId);
        if (!empty($age)) {
            $records->where('user_profile.age', '>=', $age[0])
                ->where('user_profile.age', '<=', $age[1]);
        }
        if (!is_null($lastDays)) {
            $records->whereDate('users.last_activity_at', '>=', $lastDays);
        }
        $records = $records->groupBy('user_profile.gender')
            ->get()
            ->pluck('users_count', 'gender')
            ->toArray();

        return $records;
    }
}

if (!function_exists('GetUserCountBasedOnContents')) {
    /**
     * Get user count based on masterclass, meditation, stories, webinar, recipe for irish life data extract
     *
     * @param int $id
     * @param int $companyId
     * @param string $contentType
     * @param string $countType
     * @return array
     */
    function GetUserCountBasedOnContents($id, $companyId, $contentType, $countType = 'number')
    {
        $records = UserProfile::join('user_team', 'user_team.user_id', '=', 'user_profile.user_id');
        if ($contentType == 'masterclass') {
            // Masterclass
            $records->join('user_course', 'user_course.user_id', '=', 'user_profile.user_id')
                ->where('user_course.course_id', $id);

            if ($countType == 'avg') {
                $records->addSelect(DB::raw("COUNT(DISTINCT user_course.user_id) as users_count"));
            }
        } elseif ($contentType == 'feed') {
            // Feed
            $records->join('feed_user', 'feed_user.user_id', 'user_profile.user_id')
                ->where('feed_user.feed_id', $id);
            if ($countType == 'avg') {
                $records->addSelect(DB::raw("COUNT(DISTINCT feed_user.user_id) as users_count"));
            }
        } elseif ($contentType == 'meditation') {
            // Meditation
            $records->join('user_listened_tracks', 'user_listened_tracks.user_id', 'user_profile.user_id')
                ->where('user_listened_tracks.meditation_track_id', $id);
            if ($countType == 'avg') {
                $records->addSelect(DB::raw("COUNT(DISTINCT user_listened_tracks.user_id) as users_count"));
            }
        } elseif ($contentType == 'webinar') {
            // Webinar
            $records->join('webinar_user', 'webinar_user.user_id', 'user_profile.user_id')
                ->where('webinar_user.webinar_id', $id);
            if ($countType == 'avg') {
                $records->addSelect(DB::raw("COUNT(DISTINCT webinar_user.user_id) as users_count"));
            }
        } else {
            // Recipe
            $records->join('recipe_user', 'recipe_user.user_id', 'user_profile.user_id')
                ->where('recipe_user.recipe_id', $id);
            if ($countType == 'avg') {
                $records->addSelect(DB::raw("COUNT(DISTINCT recipe_user.user_id) as users_count"));
            }
        }
        if ($countType == 'avg') {
            $records->select(
                DB::raw("AVG(user_profile.age) as users_count"),
                'user_profile.gender'
            );
        } else {
            $records->select(
                'user_profile.gender'
            )->groupBy('user_profile.gender');
        }
        $records = $records->get()
            ->pluck('users_count', 'gender')
            ->toArray();

        return $records;
    }
}

if (!function_exists('GetUserCountBasedOnWellbeingSurvey')) {
    /**
     * Get user count based on Wellbeing Survey for irish life data extract
     *
     * @param int $id
     * @param int $companyId
     * @param string $type
     * @param string $countType
     * @return array
     */
    function GetUserCountBasedOnWellbeingSurvey($id, $companyId, $type, $countType = 'number')
    {
        $age = [];
        if (str_contains($type, '-')) {
            $age = explode('-', $type);
        }
        $response = ZcSurveyUserLog::join('zc_survey_log', 'zc_survey_log.id', '=', 'zc_survey_user_log.survey_log_id')
            ->join('user_profile', 'user_profile.user_id', '=', 'zc_survey_user_log.user_id')
            ->join('user_team', 'user_team.user_id', '=', 'zc_survey_user_log.user_id')
            ->where('user_team.company_id', $companyId)
            ->where('zc_survey_log.survey_id', $id);
        if (!empty($age)) {
            $response->where('user_profile.age', '>=', $age[0])
                ->where('user_profile.age', '<=', $age[1]);
        } elseif ($type == 'response') {
            $response->whereNotNull('zc_survey_user_log.survey_submitted_at');
        }
        $response = $response->select(
            DB::raw("COUNT(DISTINCT zc_survey_user_log.user_id) as users_count"),
            'user_profile.gender'
        )
            ->groupBy('user_profile.gender')
            ->get()
            ->pluck('users_count', 'gender')
            ->toArray();

        return $response;
    }
}

if (!function_exists('GetUserCountBasedOnEventRegistered')) {
    /**
     * Get user count based on Event Registered for irish life data extract
     *
     * @param int $id
     * @param int $eventId
     * @return array
     */
    function GetUserCountBasedOnEventRegistered($id, $eventId)
    {
        $response = EventRegisteredUserLog::join('user_profile', 'user_profile.user_id', '=', 'event_registered_users_logs.user_id')
            ->where('event_registered_users_logs.event_booking_log_id', $id)
            ->where('event_registered_users_logs.event_id', $eventId)
            ->select(
                DB::raw("COUNT(DISTINCT event_registered_users_logs.user_id) as users_count"),
                'user_profile.gender'
            )
            ->groupBy('user_profile.gender')
            ->distinct()
            ->get()
            ->pluck('users_count', 'gender')
            ->toArray();

        $responseAvg = EventRegisteredUserLog::join('user_profile', 'user_profile.user_id', '=', 'event_registered_users_logs.user_id')
            ->where('event_registered_users_logs.event_booking_log_id', $id)
            ->where('event_registered_users_logs.event_id', $eventId)
            ->select(
                DB::raw("AVG(user_profile.age) as users_count"),
            )
            ->distinct()
            ->get()
            ->pluck('users_count')
            ->toArray();

        if (!empty($responseAvg)) {
            $response['avg'] = round($responseAvg[0], 2);
        }

        return $response;
    }
}

if (!function_exists('generalSpecificQueryPeriod')) {
    /**
     * General Specific Query Period
     *
     * @param array $data
     * @param string $appTimezone
     * @return array
     */
    function generalSpecificQueryPeriod($slot, $appTimezone, $duration = 30)
    {
        $targetUnavailable = [];
        if (!empty($slot['data'])) {
            $todayDate = Carbon::now()->setTimezone($appTimezone)->toDateTimeString();
            foreach ($slot['data'] as $queryPeriod) {
                if ($queryPeriod['start_time'] < $queryPeriod['end_time']) {
                    $start = Carbon::parse($queryPeriod['start_time'], $slot['timezone'])->setTimezone($appTimezone)->toDateTimeString();
                    $end   = Carbon::parse($queryPeriod['end_time'], $slot['timezone'])->setTimezone($appTimezone)->toDateTimeString();
                    if ($end >= $todayDate && $end > $start) {
                        if ($start < $todayDate) {
                            $start = $todayDate;
                        }
                        $dateCheckDuration = Carbon::parse($start);
                        $diff              = $dateCheckDuration->diffInMinutes($end);
                        if ($diff >= $duration) {
                            $tempArray = [
                                'start' => date("Y-m-d\TH:i:s.000\Z", strtotime($start)),
                                'end'   => date("Y-m-d\TH:i:s.000\Z", strtotime($end)),
                            ];
                            array_push($targetUnavailable, $tempArray);
                        }
                    }
                }
            }
        }
        return $targetUnavailable;
    }

    /**
     * Get company plan for parent and child company access
     * @param $user
     * return array
     */
    if (!function_exists('getDTAccessForParentsChildCompany')) {
        function getDTAccessForParentsChildCompany($user, $module = '')
        {
            // For zevo and reseller role have full access from company.
            $result      = false;
            if (!empty($user)) {
                $company            = $user->company()->first();
                $featureGroup       = 2;
                $companyPlanFeature = [];
                $role               = getUserRole($user);
                if (!empty($company) && $role->group == 'reseller') {
                    $childCompanies     = Company::select('id')->where('id', $company->id)->orWhere('parent_id', $company->id)->pluck('id')->toArray();
                    $companyPlanFeature = Company::select(DB::raw('DISTINCT(cp_features.id)'), 'cp_features.slug')->leftJoin('cp_company_plans', 'companies.id', '=', 'cp_company_plans.company_id')
                        ->leftJoin('cp_plan', 'cp_plan.id', '=', 'cp_company_plans.plan_id')
                        ->leftJoin('cp_plan_features', 'cp_plan_features.plan_id', '=', 'cp_plan.id')
                        ->leftJoin('cp_features', 'cp_features.id', '=', 'cp_plan_features.feature_id')
                        ->whereIn('cp_company_plans.company_id', $childCompanies)
                        ->pluck('cp_features.id')->toArray();

                    $moduleFeatures  = CpFeatures::select('id')->where('slug', $module)->where('group', $featureGroup)->get()->first();
                    $modelFeaturesId = !empty($moduleFeatures) ? $moduleFeatures->id : 0;
                    if (in_array($modelFeaturesId, $companyPlanFeature)) {
                        $result = true;
                    }
                }
            }
            return $result;
        }
    }
}