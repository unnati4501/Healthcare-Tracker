@if(!empty(session('is_reseller')) && session('is_reseller') == 1)
{{-- <footer class="main-footer text-center text-md-start">
    <div class="text-center text-md-start d-inline-block">
        <strong>
            {{ trans('layout.footer.texts.copyright') }} {{ date('Y') }}
            Irish Life Wellbeing Limited.
        </strong>
        A private company limited by shares. Registered in Ireland No.686621. Registered Office: Irish Life Centre, Lower Abbey Street, Dublin 1.
    </div>
    <p class="float-md-right font-12 text-center mb-0">
        <a href="https://irishlifeworklife.ie/privacy-notices/" target="_blank" title="Privacy Policy">
            {{ trans('layout.footer.links.privacy_policy') }}
        </a>
        <span>
            |
        </span>
        <a href="https://irishlifeworklife.ie/cookies-policy/" target="_blank" title="Cookie Policy">
            {{ trans('layout.footer.links.cookie_policy') }}
        </a>
    </p>
</footer> --}}
@else
<footer class="main-footer text-center text-md-start">
    <div class="text-center text-md-start d-inline-block">
        <strong>
            {{ trans('layout.footer.texts.copyright') }} {{ date('Y') }}
            <a href="{{ url('/') }}">
                {{config('app.name', env('APP_NAME'))}}
            </a>
            .
        </strong>
        {{ trans('layout.footer.texts.rights') }}
    </div>
    <p class="float-md-right font-12 text-center mb-0">
        <a href="https://www.yopmail.com/privacy-policy/" target="_blank" title="Privacy Policy">
            {{ trans('layout.footer.links.privacy_policy') }}
        </a>
        <span>
            |
        </span>
        <a href="https://www.yopmail.com/cookie-policy-for-zevo-health/" target="_blank" title="Cookie Policy">
            {{ trans('layout.footer.links.cookie_policy') }}
        </a>
    </p>
</footer>
@endif
