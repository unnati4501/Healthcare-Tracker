<script src="{{asset('assets/plugins/jquery/jquery.min.js?var='.rand())}}"></script>
<div style="display: flex;flex-direction: column;justify-content: center;align-items: center;height: calc(100vh - 16px);">
	<img src="{{asset('assets/dist/img/loader.gif')}}"/>
	<p style="font-size: 22px;color: #675c53;line-height: 30px;margin-top: 80px;"> Your request is being processed </p>
	<p style="margin:0;font-size: 22px;color: #675c53;line-height: 30px;"> Please do not refresh this page or click 'Back' OR 'Close' of the browser. </p>
</div>
<!-- include datatable css -->
<script type="text/javascript">
$(document).ready(function() {
	var redirectUrl = `{{ route('admin.cronofy.sessions.index') }}`;
	setTimeout(function() {
		window.location.href = redirectUrl;
	}, 1500);
});
</script>