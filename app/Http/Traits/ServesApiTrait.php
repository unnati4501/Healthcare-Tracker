<?php
declare (strict_types = 1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait ServesApiTrait
 *
 * @package App\Http\Traits
 */
trait ServesApiTrait
{

    /**
     * @param array $data
     * @param null  $message
     * @param array $headers
     * @param int   $options
     * @return JsonResponse
     */
    public function successResponse($data, $message = null, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiSuccessResponse(200, $data, $message, $headers, $options);
    }

    /**
     * @param array $data
     * @param null  $message
     * @param int   $status
     * @param array $headers
     * @param int   $options
     * @return JsonResponse
     */
    public function invalidResponse(array $data = [], $message = null, int $status = 422, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiResponse($status, $data, $message, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    public function notFoundResponse($message, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiResponse(404, [], $message, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    public function unauthorizedResponse($message = null, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiResponse(401, [], $message, $headers, $options);
    }

    /**
     * @param array $headers
     * @param int   $options
     * @return JsonResponse
     */
    public function noContentResponse(array $headers = [], $options = 0): JsonResponse
    {
        return response()->json([], 204, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    public function badRequestResponse($message = null, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiResponse(400, [], $message, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    protected function internalErrorResponse($message, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiResponse(500, [], $message, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    protected function fileTooLargeResponse(): JsonResponse
    {
        return $this->apiResponse(422, [], 'File too large!');
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    public function preConditionsFailedResponse($message = null, array $headers = [], $options = 0): JsonResponse
    {
        return $this->apiResponse(412, [], $message, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    protected function underMaintenanceResponse($message = null, array $data = [], int $status = 503, array $headers = [], $options = 0): JsonResponse
    {
        $message = (!empty($message) ? $message : trans('labels.under_maintenance_message'));
        return $this->apiResponse($status, $data, $message, $headers, $options);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    protected function updateAppResponse($message = null): JsonResponse
    {
        $message = (!empty($message) ? $message : trans('labels.update_app_message'));
        return $this->apiResponse(422, [], $message);
    }

    /**
     * Make Json response
     *
     * @param int         $code Response code
     * @param array       $data Optional result data
     * @param null|string $message Optional message
     * @param array       $headers Optional headers
     * @param int         $options Optional options
     * @return JsonResponse
     */
    public function apiResponse(int $code, array $data = [], string $message = null, array $headers = [], int $options = 0): JsonResponse
    {
        $response = ['code' => $code];

        if (!\is_null($message)) {
            $translator = app('translator');
            $message    = $translator->has($message)
            ? $translator->trans($message)
            : $message;

            $response['message'] = $message;
        }

        if (\array_key_exists('token', $data)) {
            $response['token'] = $data['token'];
            unset($data['token']);
        }

        if ($code == 422) {
            $errors = [];
            foreach ($data as $field => $error) {
                $errors[] = [
                    'field'   => $field,
                    'message' => $error,
                ];
            }
            $response['errors'] = $errors;
        } else if ($code == 307) {
            $result['data']     = $data;
            $response['result'] = $result;
        }

        return response()->json($response, $code, $headers, $options);
    }

    /**
     * Make Json response
     *
     * @param int         $code Response code
     * @param array       $data Optional result data
     * @param null|string $message Optional message
     * @param array       $headers Optional headers
     * @param int         $options Optional options
     * @return JsonResponse
     */
    public function apiSuccessResponse(int $code, $data, string $message = null, array $headers = [], int $options = 0): JsonResponse
    {
        $response = ['code' => $code];

        if (!\is_null($message)) {
            $translator = app('translator');
            $message    = $translator->has($message)
            ? $translator->trans($message)
            : $message;

            $response['message'] = $message;
        }
        
        if ((is_array($data) && \array_key_exists('token', $data)) || (!is_array($data) && property_exists($data, 'token'))) {
            $response['token'] = $data['token'];
            unset($data['token']);
        }

        if ($code == 200) {
            if (!\blank($data)) {
                $response['result'] = $data;
            }
        } else if ($code == 422) {
            $errors = [];
            foreach ($data as $field => $error) {
                $errors[] = [
                    'field'   => $field,
                    'message' => $error,
                ];
            }
            $response['errors'] = $errors;
        }

        $options = JSON_PRESERVE_ZERO_FRACTION;

        return response()->json($response, $code, $headers, $options);
    }

    /**
     * @param string $message
     * @param int    $code
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    public function notaccessFailedResponse($message = null, int $code = 422, array $headers = [], $options = 0): JsonResponse
    {
        $response = ['code' => $code, 'message' => $message];
        $options  = JSON_PRESERVE_ZERO_FRACTION;
        return response()->json($response, $code, $headers, $options);
    }

    /**
     * @param string $message
     * @param array  $headers
     * @param int    $options
     * @return JsonResponse
     */
    protected function tooManyAttemptsResponse($message = null, array $data = [], int $status = 422, array $headers = [], $options = 0): JsonResponse
    {
        $message = (!empty($message) ? $message : trans('labels.too_many_attempts'));
        return $this->apiResponse($status, $data, $message, $headers, $options);
    }
}
