@if(!empty($role->id))
<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{route('admin.roles.edit', $role->id)}}" title="Edit">
    <i aria-hidden="true" class="fal fa-pencil-alt"></i>
</a>
<a href="javaScript:void(0)" class="btn btn-sm btn-outline-danger animated bounceIn slow delete-toast" title="Delete">
    <i class="fal fa-trash-alt" aria-hidden="true" data-id="{{$role->id}}" id="roleDelete"></i>
</a>
@endif