@if(in_array(env('APP_ENV'),config('zevolifesettings.ga_env_enabled')))
<script async="" src="https://www.googletagmanager.com/gtag/js?id=UA-116449421-2">
</script>
<script type="text/javascript">
    window.dataLayer = window.dataLayer || [];
    function gtag(){
        dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', 'UA-116449421-2');
</script>
@endif
