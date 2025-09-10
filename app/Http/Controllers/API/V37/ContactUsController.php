<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V37;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\V26\ContactUsController as v26ContactUsController;
use App\Http\Requests\Api\V26\ContactUsRequest;
use App\Jobs\ContactUsJob;
use App\Models\ContactUs;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactUsController extends v26ContactUsController
{
    /**
     * Details of personal challenge
     *
     * @param  ContactUsRequest $request
     * @param  ContactUs $contactUs
     * @return \Illuminate\Http\JsonResponse
     */
    public function contactUsSubmit(ContactUsRequest $request, ContactUs $contactUs)
    {
        try {
            \DB::beginTransaction();
            $xDeviceOs = strtolower($request->header('X-Device-Os', ""));
            $payload   = $request->all();
            $user      = $this->user();
            if (!empty($payload)) {
                $contactUsInput = [
                    'user_id'     => $user->id,
                    'name'        => $payload['name'],
                    'email'       => $payload['email'],
                    'description' => $payload['description'],
                ];
                $contactUsUser = ContactUs::create($contactUsInput);

                // Upload attachment
                if ($xDeviceOs == config('zevolifesettings.PORTAL') && $request->attachment) {
                    $name = $user->getKey() . '_' . \time();
                    $contactUsUser->clearMediaCollection('attachment')
                        ->addMediaFromBase64($request->attachment)
                        ->usingName($name)
                        ->toMediaCollection('attachment', config('medialibrary.disk_name'));

                    $contactUsInput['attachment'] = array($contactUsUser->getMediaData('attachment', ['w' => 640, 'h' => 1280, 'zc' => 3]));
                }
            }
            $contactUsInput['origin'] = $request->headers->get('origin');
            $contactUsInput['type']   = !empty($payload['type']) ? $payload['type'] : '';
            // Dispatch job to send mail to admin
            dispatch(new ContactUsJob($contactUsInput));
            \DB::commit();
            return $this->successResponse([], 'Email Received<br/>A member of the team will contact you shortly.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
