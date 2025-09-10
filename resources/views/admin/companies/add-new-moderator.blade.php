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
                <th>
                    {{ __('') }}
                </th>
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
            <tr class="moderators-wrap" data-id="0"><td class=""><span style="display:none" id="span_id_0"></span><input type="hidden" id="id_0" class="form-control" name="id[0]" value="id0"></td>
                <td class="align-top"><span style="display:none" id="span_first_name_0"></span><input type="text" placeholder="First Name" maxlength="50" id="first_name_0" class="form-control first_name" name="first_name[0]" value=""></td>
                <td class="align-top" ><span style="display:none" id="span_last_name_0"></span><input type="text" placeholder="Last Name" maxlength="50" id="last_name_0" class="form-control last_name" name="last_name[0]" value=""></td>
                <td class="align-top"><span style="display:none" id="span_email_0"></span><input type="text" placeholder="Email" class="form-control email" id="email_0" name="email[0]" value=""></td>
                <td class=" no-sort text-center">
                    <a class="action-icon edit_moderator" style="display:none;" id="edit_moderator_0" href="javascript:void(0);" title="Edit" data-id="0">
                        <i class="far fa-edit">
                        </i>
                    </a>
                    <a class="action-icon save_moderator" id="save_moderator_0" href="javascript:void(0);" title="Save" data-id="0">
                        <i class="far fa-save">
                        </i>
                    </a>
                    {{-- <a class="action-icon delete_moderator danger" href="javascript:void(0);" title="Delete" data-id="0">
                        <i class="far fa-trash-alt">
                        </i>
                    </a> --}}
                </td>
            </tr>
        </tbody>
    </table>
</div>