<?php
declare (strict_types = 1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

/**
 * Class CommonController
 *
 * @package App\Http\Controllers\Admin
 */
class CommonController extends Controller
{

    /**
     * @param Country $country
     *
     * @return JsonResponse
     */
    public function storeArticleEditorFiles(Request $request)
    {
        $funcNum      = $request->input('CKEditorFuncNum', '');
        $uploadType   = $request->input('upload_type', 'default');
        $responseType = $request->input('responseType', 'json');
        $routeName    = $request->route()->getName();
        $message      = 'Something went wrong!';
        $url          = $foldername          = '';
        $uploaded     = false;
        $maxSize      = (100 * 1024);

        try {
            $typeValidation = [
                'image'      => [
                    "rule"    => "mimetypes:image/jpeg,image/jpg,image/png",
                    'message' => "The file must be a type of JPG, PNG.",
                ],
                'html5audio' => [
                    "rule"    => "mimetypes:audio/mp3,audio/mpeg,audio/m4a,audio/x-m4a",
                    'message' => "The file must be a type of MP3, M4A.",
                ],
                'html5video' => [
                    "rule"    => "mimetypes:video/mp4",
                    'message' => "The file must be a type of MP4.",
                ],
                'default'    => [
                    "rule"    => "mimetypes:image/jpeg,image/jpg,image/png,audio/mp3,audio/mpeg,audio/m4a,audio/x-m4a,video/mp4,application/pdf",
                    'message' => "The file must be a type of Image, Audio, Video, PDF.",
                ],
            ];

            if ($routeName == "admin.ckeditor-upload.feed-description") {
                $maxSize    = config('zevolifesettings.fileSizeValidations.feed.mix_content', (100 * 1024));
                $foldername = config('zevolifesettings.feed_ckeditor_content_foldername');
            } elseif ($routeName == "admin.ckeditor-upload.masterclass-lesson") {
                $maxSize    = config('zevolifesettings.fileSizeValidations.course_lession.mix_content', (100 * 1024));
                $foldername = config('zevolifesettings.masterclass_ckeditor_content_foldername');
            }

            $validator = Validator::make(
                $request->all(),
                [
                    "upload" => [
                        "required",
                        $typeValidation[$uploadType]['rule'],
                        "max:{$maxSize}",
                    ],
                ],
                [
                    "upload.required"  => "Please upload a file.",
                    "upload.mimetypes" => $typeValidation[$uploadType]['message'],
                    "upload.max"       => "Maximum allowed file size is 100MB, Please try again.",
                ]
            );

            if (!$validator->fails()) {
                $disk_name = config('medialibrary.disk_name');
                $file      = $request->file('upload');
                $fileName  = auth()->user()->id . Str::random() . "_" . \time() . "." . $file->getClientOriginalExtension();
                $content   = file_get_contents($file->getPathName());

                if ($disk_name == "spaces") {
                    $root     = config("filesystems.disks.spaces.root");
                    $uploaded = uploadFileToSpaces($content, "{$root}/{$foldername}/{$fileName}", "public");
                    if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                        $url      = $uploaded->get('ObjectURL');
                        $uploaded = true;
                    }
                } elseif ($disk_name == "azure") {
                    $uploaded = uploadeFileToBlob($content, $fileName, config('zevolifesettings.masterclass_ckeditor_content_foldername'));
                    $url      = config("medialibrary.$disk_name.domain") . '/' . $foldername . '/' . $fileName;
                }

                if ($uploaded) {
                    $message  = '';
                    $uploaded = true;
                } else {
                    $uploaded = false;
                    $message  = 'Failed to uploaded! Please try again.';
                    $url      = '';
                }
            } else {
                $message = $validator->errors()->first();
            }

            if ($responseType == 'json') {
                return response()->json([
                    'uploaded' => $uploaded,
                    'url'      => $url,
                    'error'    => [
                        'message' => $message,
                    ],
                ]);
            } else {
                return "<script>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', '{$message}');</script>";
            }
        } catch (\Exception $e) {
            report($e);
            $message = 'Failed to uploaded! Please try again.';
            if ($responseType == 'json') {
                return response()->json([
                    'uploaded' => 0,
                    'url'      => '',
                    'error'    => [
                        'message' => $message,
                        'status'  => 500,
                    ],
                ]);
            } else {
                return "<script>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', '{$message}');</script>";
            }
        }
    }
}
