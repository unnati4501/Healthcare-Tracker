<div class="col-md-6">
    <div class="d-flex">
        <i class="far fa-file-alt me-2 notes-icon">
        </i>
        <div class="col-md-10">
            <p style="word-break: break-all;">
                <?php
                $string = strip_tags($note['userNote']);
                if (strlen($string) > 100) {

                    // truncate string
                    $stringCut = substr($string, 0, 100);
                    $endPoint = strrpos($stringCut, ' ');

                    //if the string doesn't contain any space then it will cut without word basis.
                    $string = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
                    $string .= '... <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#diplay-user-note-model" class="diplay-session-note-model" id="note-'.$note['scheduleId'].'" data-content="'.htmlentities($note['userNote']).'">Read More</a>';
                    }
                    echo nl2br($string); 
                ?>
            </p>
            <span class="gray-500">
                {{ Carbon\Carbon::parse($note['created_at'])->format(config('zevolifesettings.date_format.date_format_for_client_notes'))}}
            </span>
        </div>
    </div>
</div>

<div class="modal fade" data-backdrop="static" data-id="0" data-keyboard="false" id="diplay-user-note-model" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ __('View Note') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body" id="modal_session_body">
                
            </div>
        </div>
    </div>
</div>