<?php
if(session('message') != null ){
    $message  = session('message');
    session()->forget('message');
}
?>
@if ($errors->any())
<script>
    toastr.error( "{{ implode('', $errors->all()) }}");
</script>
@endif

@if(isset($message) && is_array($message) &&  in_array('data',array_keys($message) ) )
    @if(isset($message['status']) && isset($message['data'])  && ( $message['status']) == 1 )
<script>
    $(document).ready(function() {
        toastr.success( "{{$message['data']}}");
    });
</script>
@elseif(isset($message['status']) && isset($message['data']) && ( $message['status']) == 0 )
<script>
    $(document).ready(function() {
        toastr.error( "{{$message['data']}}");
    });
</script>
@elseif(isset($message['status']) && isset($message['data']) && ( $message['status']) == 2 )
<script>
    $(document).ready(function() {
        toastr.warning("{{$message['data']}}");
    });
</script>
@else
<script>
    $(document).ready(function() {
        toastr.error(trans('layout.toastr.message'));
    });
</script>
@endif
@endif
