<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta charset="utf-8">
            <meta content="width=device-width, initial-scale=1" name="viewport">
                <meta name="x-apple-disable-message-reformatting">
                    <meta content="IE=edge" http-equiv="X-UA-Compatible">
                        <meta content="text/html charset=UTF-8" http-equiv="Content-Type"/>
                        <meta content="telephone=no" name="Email">
                            <title>
                            </title>
                            <!-- Common Style Block -->
                            @include('emails.partials.style')
                            <!-- // Common Style Block -->
                        </meta>
                    </meta>
                </meta>
            </meta>
        </meta>
    </head>
    <body style="margin: 0;width:100%;">
        <!-- Header Block -->
        @include('emails.partials.header')
        <!-- // Header Block End -->
        <!-- Body Block -->
        <table cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;font-family: sans-serif;" width="100%">
            <tr style="border-collapse:collapse;">
                <td align="center">
                    <table align="center" bgcolor="#ffffff" cellpadding="0" cellspacing="0" style="text-align: center;padding-top: 20px;background-repeat: no-repeat;background-position: left 25px bottom 25px;background-color: #ffffff;border-left: solid 1px #e9e9e9;border-right: solid 1px #e9e9e9;width: 600px !important;max-width: 600px !important;" width="600">
                        <tr align="center" style="border-collapse:collapse;text-align: center">
                            <td style="padding-top:70px;padding-left: 15px;padding-right: 15px;">
                                <p style="margin: 0;line-height: 1.4;font-size: 18px;color: #333333;">
                                    Hello {{$userName}},
                                    <br/>
                                    <br/>
                                    Following the booking of your 1:1 session, please click the button below to complete the consent form. It's very important you read, understand, and complete the consent form before your appointment, paying special attention to the terms that are offered. The counsellor will spend a few minutes at the beginning of the session exploring the consent form.
                                    <br/>
                                    <a href="{{$consentUrl}}" title="{{$consentUrl}}" style="text-decoration: underline;color:#333333;">
                                        Please click here to complete the Consent form.
                                    </a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:25px 0;">
                            </td>
                        </tr>
                        @if($signOffSignature)
                        <tr>
                            <td style="margin: 0;padding-bottom: 40px;line-height: 1.4;font-size: 18px;color: #333333;text-align: left;padding-left: 15px;padding-right: 15px;">
                                Kind regards,
                                <br/>
                                {{$signOffSignature}}
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
        <!-- // Body Block End -->
        <!-- Footer Block -->
        @include('emails.partials.footer')
        <!-- // Footer End -->
    </body>
</html>