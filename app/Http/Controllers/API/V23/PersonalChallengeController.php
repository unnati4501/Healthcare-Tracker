<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V23;

use App\Http\Controllers\API\V22\PersonalChallengeController as v22PersonalChallengeController;
use App\Http\Requests\Api\V21\CreatePersonalChallengeRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeUser;
use App\Models\PersonalChallengeUserTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ChallengeImageLibrary;

class PersonalChallengeController extends v22PersonalChallengeController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Create personal challenge from app
     *
     * @param CreatePersonalChallengeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreatePersonalChallengeRequest $request)
    {
        try {
            \DB::beginTransaction();
            // logged-in user
            $user    = $this->user();
            $payload = $request->all();

            $personalChallengeInput = [
                'creator_id'     => $user->id,
                'company_id'     => !is_null($user->company->first()) ? $user->company->first()->id : null,
                'logo'           => (array_key_exists('logo', $payload) && !empty($payload['logo'])) ? $payload['logo']->getClientOriginalName() : '',
                'title'          => $payload['name'],
                'duration'       => $payload['duration'],
                'challenge_type' => $payload['challengetype'],
                'type'           => $payload['type'],
                'description'    => $payload['description'],
                'recursive'      => ($payload['isRecursive'] == 'true') ? 1 : 0,
            ];

            if ($payload['challengetype'] == 'challenge') {
                $personalChallengeInput['target_value'] = $payload['target_value'];
            }

            $record = PersonalChallenge::create($personalChallengeInput);

            if ($record) {
                if ($payload['challengetype'] == 'routine') {
                    $personalChallengeTaskInput = [];
                    $tasks                      = $payload['tasks'];

                    foreach ($tasks as $key => $value) {
                        $personalChallengeTaskInput[] = [
                            'personal_challenge_id' => $record->id,
                            'task_name'             => $value,
                        ];
                    }

                    $record->personalChallengeTasks()->insert($personalChallengeTaskInput);
                }

                if (isset($payload['logo']) && !empty($payload['logo'])) {
                    $name = $record->id . '_' . \time();
                    $record->clearMediaCollection('logo')
                        ->addMediaFromRequest('logo')
                        ->usingName($name)
                        ->usingFileName($name . '.' . $payload['logo']->extension())
                        ->toMediaCollection('logo', config('medialibrary.disk_name'));
                } else {
                    $record->library_image_id = $request->imageId;
                    $record->save();
                }
            }

            $currentChallenges = PersonalChallengeUser::where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->get();

            if (count($currentChallenges) >= 10) {
                return $this->invalidResponse([], "You have reached limit of joining maximum of 10 personal challenges at a time.");
            }

            $startDate = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->toDateTimeString();
            $endDate   = Carbon::parse($payload['startDate'], $user->timezone)->setTimezone(config('app.timezone'))->addDays($record->duration)->toDateTimeString();

            $personalChallengeUserInput = [
                'personal_challenge_id' => $record->id,
                'joined'                => 1,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'reminder_at'           => $payload['reminderAt'],
                'completed'             => 0,
            ];

            if (!empty($payload['isRecursive']) && $payload['isRecursive'] == "true") {
                $personalChallengeUserInput['recursive_count']     = $payload['recursiveCount'];
                $personalChallengeUserInput['recursive_completed'] = 0;
            }

            $record->personalChallengeUsers()->attach($user->id, $personalChallengeUserInput);

            $userData = $record->personalChallengeUsers()
                ->where('user_id', $user->id)
                ->where('joined', 1)
                ->where('completed', 0)
                ->orderBy('id', 'DESC')
                ->first();

            $personalChallengeUserTaskInput = [];
            if ($record->type == 'streak') {
                $taskData = $record->personalChallengeTasks()->first();

                for ($i = 0; $i < $record->duration; $i++) {
                    $date                             = Carbon::parse($payload['startDate'])->addDays($i)->toDateTimeString();
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $taskData->personal_challenge_id,
                        'personal_challenge_user_id'  => $userData->pivot->id,
                        'personal_challenge_tasks_id' => $taskData->id,
                        'date'                        => $date,
                    ];
                }
            } else {
                $taskData = $record->personalChallengeTasks()->get();

                $taskData->each(function ($item, $key) use (&$personalChallengeUserTaskInput, $userData) {
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $item->personal_challenge_id,
                        'personal_challenge_user_id'  => $userData->pivot->id,
                        'personal_challenge_tasks_id' => $item->id,
                    ];
                });
            }

            if (!empty($personalChallengeUserTaskInput)) {
                PersonalChallengeUserTask::insert($personalChallengeUserTaskInput);
            }

            if (!empty($userData)) {
                if (Carbon::now()->setTimezone($user->timezone)->toDateTimeString() < $startDate) {
                    $this->setStartReminder($record, $startDate, $userData->pivot->id);
                }
                $endDateTz = Carbon::parse($endDate, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
                $this->setEndReminder($record, $endDateTz, $userData->pivot->id);
            }

            $data = [
                'id'        => $record->id,
                'mappingId' => $userData->pivot->id,
            ];

            \DB::commit();

            return $this->successResponse(['data' => $data], trans('api_messages.personalChallenge.create'));
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
