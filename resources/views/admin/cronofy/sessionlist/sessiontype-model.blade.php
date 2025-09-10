<!-- Modal -->
<div class="modal fade" id="sessionModal" tabindex="-1" role="dialog" aria-labelledby="sessionModal" aria-hidden="true">
    <div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Book Session</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group text-center">
                    <a class="btn btn-primary" href="{{ route('admin.cronofy.sessions.create', [1]) }}">
                        <i class="fa fa-user me-3 align-middle">
                        </i>
                        {{ trans('Cronofy.session_list.title.one_session') }}
                    </a>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group text-center">
                    <a class="btn btn-primary disabled" href="javascript:void(0);">
                        {{-- {{ route('admin.cronofy.sessions.create', [2]) }} --}}
                        <i class="fa fa-users me-3 align-middle">
                        </i>
                        {{ trans('Cronofy.session_list.title.group_session') }}
                    </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>