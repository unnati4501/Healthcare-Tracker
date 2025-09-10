@if(!($edit))
    @include('admin.companies.add-new-moderator')
@else
    @include('admin.companies.edit-moderators')
@endif