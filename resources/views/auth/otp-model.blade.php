<div class="modal fade" id="twoFactorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">{{ trans('non-auth.login.popup.verify_your_email') }}</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center otp-modal">
          <div class="otp-img-wrap">
            <img src="{{ asset('assets/dist/img/email-img.svg') }}" alt="" />
          </div>
          <p>
            {{ trans('non-auth.login.popup.6-digit_code') }} <span id="email_text"></span>
            <a href="Javascript:void(0)" data-bs-dismiss="modal">{{ trans('non-auth.login.popup.change') }}</a>
          </p>
          <form method="POST" action="{{ route('verify-otp') }}" class="digit-group" data-group-name="digits" data-autosubmit="false" autocomplete="off">
            <input type="text" class="form-control def-txt-input" id="digit-1" name="digit[]" data-next="digit-2" />
            <input type="text" class="form-control def-txt-input" id="digit-2" name="digit[]" data-next="digit-3" data-previous="digit-1" />
            <input type="text" class="form-control def-txt-input" id="digit-3" name="digit[]" data-next="digit-4" data-previous="digit-2" />
            <input type="text" class="form-control def-txt-input" id="digit-4" name="digit[]" data-next="digit-5" data-previous="digit-3" />
            <input type="text" class="form-control def-txt-input" id="digit-5" name="digit[]" data-next="digit-6" data-previous="digit-4" />
            <input type="text" class="form-control def-txt-input" id="digit-6" name="digit[]" data-previous="digit-5" />
          </form>
            <div class="otp-inner-width">
              <ul>
                <li>{{ trans('non-auth.login.popup.validate') }}</li>
                <li id="resent-msg">
                  {{ trans('non-auth.login.popup.validate1') }}
                  <a href="Javascript:void(0)" id="resend-otp"> {{ trans('non-auth.login.popup.resend') }} </a>
                </li>
                <li id="resent-again" class="d-none">
                  {{ trans('non-auth.login.popup.resend_again') }} <strong class="countdown"></strong>
                </li>
              </ul>
              <div class="d-grid">
                <button class="btn btn-primary br-100 btn-h-40 verify-btn" type="button">
                  {{ trans('non-auth.login.popup.verify') }}
                </button>
              </div>
            </div>
          <div class="mt-3 mb-3">
            Trouble logging in ?
            <a href="javascript:;" data-bs-dismiss="modal" onclick="zE('webWidget', 'open')">Need Help</a>
          </div>
        </div>
      </div>
    </div>
</div>
<script src="{{ mix('js/auth/otp-modal.js') }}" type="text/javascript">
</script>