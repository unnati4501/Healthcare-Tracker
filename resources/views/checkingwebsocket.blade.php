<html>
<head>
	<meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<div id="app">
{{ Auth::id() }}
</div>
<script src="{{ asset('js/app.js') }}"></script>
<script>
window.addEventListener("load", function(){
	console.log(window.location.hostname);
	
  //   Echo.channel('testchallenge')
		// .listen('.MyWebSocket', (e) => {
		// 	console.log(e);
		// });

    Echo.private('myPrivateChannel.user.{{ Auth::id() }}')
	    .listen('.private_message', (e) => {
			console.log(e);
		})
});
</script>
</body>