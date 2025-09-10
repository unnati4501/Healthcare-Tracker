<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V38;

use App\Http\Controllers\API\V33\ConsentFormController as v33ConsentFormController;
use App\Models\ConsentForm;
use App\Models\CronofySchedule;
use App\Models\ConsentFormLogs;
use App\Models\User;
use App\Http\Collections\V33\ConsentFormCollection;
use App\Http\Requests\Api\V33\SubmitConsentFormRequest;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
/**
 * Class ConsentFormController
 */
class ConsentFormController extends v33ConsentFormController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * variable to store the Consent Form object
     * @var consentForm $consentForm
     */
    private $consentForm;
    /**
     * contructor to initialize Repository object
     */
    public function __construct(consentForm $consentForm, ConsentFormLogs $consentFormLogs)
    {
        $this->consentForm      = $consentForm;
        $this->consentFormLogs  = $consentFormLogs;
    }

    /**
     * List the details of consent form.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, User $wellbeingSpecialist, $consentCategory = null)
    {
        $user = $this->user();
        
        // Check if consent form is submitted previously or not
        $getConsentFormLogs = $this->consentFormLogs->where(['user_id' => $user->id, 'ws_id' => $wellbeingSpecialist->id])->get()->first();
        if(!empty($getConsentFormLogs)){
            return $this->notFoundResponse('Consent Form already submited!');
        }
        // Get consent form as per category (ofline or online)
        $consentFormData = $this->consentForm->where('category', $consentCategory)->first();
        $consentFormQuestions = $this->consentForm->leftJoin('consent_form_questions', 'consent_form_questions.consent_id', '=', 'consent_form.id')
        ->where('category', $consentCategory)->get();
        if ($consentFormQuestions->count() > 0) {
            return $this->successResponse([
                'data' => [
                    'title'         => $consentFormData->title,
                    'description'   => $consentFormData->description,
                    'questions'     => new ConsentFormCollection($consentFormQuestions),
                ],
            ], "Consent Form details retrived successfully");
        } else {
            return $this->notFoundResponse('No Consent Form details found.');
        }
    }

    /**
     * Submit the consent form
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitConsentForm(SubmitConsentFormRequest $request){
        try {
            \DB::beginTransaction();
            $payload        = $request->all();
            $user           = $this->user();
            
            //Check if consent form is submitted previously or not
            $getConsentFormLogs = $this->consentFormLogs->where(['user_id' => $user->id, 'ws_id' => $payload['ws_id']])->first();
            if(!empty($getConsentFormLogs)){
                return $this->notFoundResponse('Consent Form already submited!');
            }
            
            $date           = explode('/', $payload['date']);
            $day            = $date[0];
            $month          = $date[1];
            $year           = $date[2];
            $formatedDate   = $year."-".$month."-".$day;
            if (!empty($payload)) {
                $consentFormInput = [
                    'user_id'                  => $user->id,
                    'ws_id'                    => $payload['ws_id'],
                    'email'                    => $payload['email'],
                    'name'                     => $payload['name'],
                    'submitted_at'             => $formatedDate,
                ];
                if($payload['type'] == 1){
                    $consentFormInput['fullname']   = $payload['fullname'];
                    $consentFormInput['address']    = $payload['address'];
                    $consentFormInput['contact_no'] = $payload['contact_no'];
                    $consentFormInput['relation']   = $payload['relation'];
                }
                $this->consentFormLogs->create($consentFormInput);
            }
            \DB::commit();
            return $this->successResponse([], 'Consent Form submitted successfully!');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}