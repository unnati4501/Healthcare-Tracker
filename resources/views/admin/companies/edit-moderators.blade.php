@permission('add-moderator')
<div class="text-end">
    <a class="btn btn-primary add_moderator" href="javascript:void(0);">
        <i class="far fa-plus me-3  ">
        </i>
        <span class=" ">
            Add Moderator
        </span>
    </a>
</div>
@endauth
<div class="table-responsive">
    <table class="table custom-table" id="moderatorsManagment">
        <thead>
            <tr>
                <input type="hidden" id="total_moderators">
                {{-- <th>
                    {{ __('ID') }}
                </th> --}}
                <th>
                    {{ __('First Name') }}
                </th>
                <th>
                    {{ __('Last Name') }}
                </th>
                <th>
                    {{ __('Email') }}
                </th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>