<div class="col-md-6">
    <div class="d-flex">
        <i class="far fa-file-alt me-2 notes-icon">
        </i>
        <div class="col-md-10">
            <p style="word-break: break-all;">
                {{-- {!! substr(strip_tags(nl2br($note->comment)), 0, 100)!!} --}}

                {{-- {!! substr(nl2br($note->comment), 0, 100); !!} --}}
                <?php
                $string = strip_tags($note->comment);
                if (strlen($string) > 100) {

                // truncate string
                $stringCut = substr($string, 0, 100);
                $endPoint = strrpos($stringCut, ' ');

                //if the string doesn't contain any space then it will cut without word basis.
                $string = $endPoint? substr($stringCut, 0, $endPoint) : substr($stringCut, 0);
                $string .= '... <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#diplay-note-model" class="diplay-note-model" id="note-'.$note->id.'" data-content="'.htmlentities($note->comment).'">Read More</a>';
                }
                echo nl2br($string); 
                ?>
            </p>
            <span class="gray-500">
                {{ $note->created_at->format(config('zevolifesettings.date_format.date_format_for_client_notes')) }}
            </span>
        </div>
        {{-- <div class="editnote" data-id="{{$note->id}}"style="float: right;margin-left: 200px;"><i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;<i class="far fa-trash-alt"></i></div> --}}
         <div class="editdelenotes col-md-2">
            @php
                $ticketId = (!empty($ticketId) ? $ticketId : 0);
            @endphp
            <a href="javascript:;" class="open-editNotes-model" data-bs-toggle="modal" data-bs-target="#edit-note-model" data-id="{{$note->id}}" data-clientid="{{$ticketId}}" > <i class="far fa-edit"></i></a>
            <a class="action-icon danger" data-id="{{$note->id}}" data-notefrom="ClientNote" href="javaScript:void(0)" id="clientNoteDelete" title="{{ trans('challenges.buttons.tooltips.delete') }}">
                <i aria-hidden="true" class="far fa-trash-alt">
                </i>
            </a>
        </div>
    </div>
</div>


<div class="modal fade" data-backdrop="static" data-id="0" data-keyboard="false" id="diplay-note-model" role="dialog" tabindex="-1">
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
            <div class="modal-body" id="modal_body">
                
            </div>
        </div>
    </div>
</div>