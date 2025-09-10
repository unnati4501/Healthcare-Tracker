<input id="deeplink" value="{{$deeplinkUrl}}" type="hidden">
<script src="{{asset('assets/plugins/jquery/jquery.min.js?var='.rand())}}"></script>
<script type="text/javascript">
    $(document).ready( function() {
        var url = $("#deeplink").val();
        /* Step 1
        if (url.match("zevolife://zevo")) {
            window.location = url;
        } else {
            if(navigator.userAgent.toLowerCase().indexOf("android") > -1){
                window.location = 'http://play.google.com/store/apps/details?id=com.truecaller&hl=en';
            }
            if(navigator.userAgent.toLowerCase().indexOf("iphone") > -1){
                window.location = 'http://itunes.apple.com/lb/app/truecaller-caller-id-number/id448142450?mt=8';
            }
        }
        */
        /* Step 2
        window.location.href = url;
        setTimeout(function () {
            window.location.href = "http://play.google.com/store/apps/details?id=com.truecaller&hl=en";
        }, 1000);
        */
       /* Step 4
        window.location.replace('zevolife://zevo/moods');
        window.location.href = url;
        setTimeout(function () {
            window.location.replace("https://play.google.com/store/apps/details?id=com.zevolife.app");
        }, 1000);
        */

        window.location.href = url;
        setTimeout(function () {
            if (navigator.userAgent.toLowerCase().indexOf("android") > -1) {
                window.location.replace("https://play.google.com/store/apps/details?id=com.zevolife.app");
            } else if(navigator.userAgent.toLowerCase().indexOf("iphone") > -1){
                window.location.replace("itms-apps://itunes.apple.com/app/my-app/id1490234528?mt=8");
            } else{
                window.location.replace("https://www.yopmail.com/");
            }
        }, 1000);

    });
</script>
