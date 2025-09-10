<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V34;

use App\Http\Controllers\API\V30\ProfileController as v30ProfileController;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends v30ProfileController
{
    /**
     * Accept terms and conditions for DT user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function acceptTerms(Request $request)
    {
        try {
            $user               = $this->user();
            $existingProfile    = $user->profile;

            if (!empty($existingProfile)) {
                $user->profile->update([
                    'is_terms_accepted' => true,
                ]);
            }
            return $this->successResponse(['data' => []], 'Terms accepted.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
