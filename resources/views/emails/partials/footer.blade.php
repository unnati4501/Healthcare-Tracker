<table width="100%" cellspacing="0" cellpadding="0"
        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;font-family: sans-serif;">
    <tr style="border-collapse:collapse;">
        <td align="center" colspan="2" style="min-width:600px !important;max-width:600px !important;width:600px !important;">
            <table bgcolor="#5261AC" width="600" cellspacing="0" cellpadding="0" align="center" style="text-align: center;background-color: #5261AC;color: #ffffff;width: 600px !important;max-width: 600px !important;">
                <tr style="border-collapse:collapse;text-align: center" align="center">
                    <td style="padding: 50px 15px 0px; vertical-align:top;width:100%;" colspan="2" valign="top">
                        <div style="color: #D4D2DE;padding-bottom: 20px"><img src="{{asset('app_assets/internet.png')}}" alt="image" style="max-height: 26px;width: auto;"></div>
                        @if(isset($cronofySessionEmails) && $cronofySessionEmails == true)
                            <div style="padding-bottom: 20px">Note: Please do not reply to this email.</div>
                            <div style="color: #D4D2DE;font-size: 14px;padding-bottom: 10px">Need Support?</div>
                            <div style="color: #ffffff; font-size: 15px;padding-bottom: 20px"><a href="{{ url(config('zevolifesettings.mail-footer-url')) }}" style="text-decoration: underline;color:#ffffff;" title="{{ url(config('zevolifesettings.mail-footer-url')) }}">Contact our team</a></div>
                        @else
                        @if(isset($isReseller) && $isReseller && isset($portaldomain))
                            <div style="color: #D4D2DE;font-size: 14px;padding-bottom: 10px">Need Help?</div>
                            <div style="color: #ffffff; font-size: 15px;padding-bottom: 20px"><a href="{{ url($portaldomain) }}/contact-us" style="text-decoration: underline;color:#ffffff;" title="{{ url($portaldomain) }}/contact-us">Contact the Support Team</a></div>
                        @else
                            <div style="color: #D4D2DE;font-size: 14px;padding-bottom: 10px">Need Support?</div>
                            <div style="color: #ffffff; font-size: 15px;padding-bottom: 20px"><a href="{{ url(config('zevolifesettings.mail-footer-url')) }}" style="text-decoration: underline;color:#ffffff;" title="{{ url(config('zevolifesettings.mail-footer-url')) }}">Contact our team</a></div>
                        @endif
                        @endif
                    </td>
                    {{-- <td style="padding: 50px 15px 0px; vertical-align:top;width:50%;" valign="top">
                        <div style="color: #D4D2DE;padding-bottom: 20px"><img src="{{asset('app_assets/help-web-button.png')}}" alt="image" style="max-height: 26px;width: auto;"></div>
                        <div style="color: #D4D2DE;font-size: 14px;padding-bottom: 10px">Need more information</div>
                        <div style="color: #ffffff;font-size: 15px;"><a href="{{ url(config('zevolifesettings.mail-footer-url')) }}" title="{{ url(config('zevolifesettings.mail-footer-url')) }}" style="text-decoration: underline;color:#ffffff;" title="Health Coaches">We are available</a></div>
                    </td> --}}
                </tr>
                <tr>
                    <td align="left" style="text-align: left;">
                        <img src="{{ asset('app_assets/plant.png') }}" style="vertical-align:bottom;padding-left:50px" valign="bottom">
                    </td>
                    <td align="right" style="vertical-align:bottom;text-align:right" valign="bottom">
                        <img src="{{ asset('app_assets/setting.png') }}" style="padding-right:50px">
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>