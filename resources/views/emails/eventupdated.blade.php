<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
    <meta content="telephone=no" name="Email">
    <title></title>
    <!-- Common Style Block -->
    @include('emails.partials.style')
    <!-- // Common Style Block -->
</head>
<body style="margin: 0;width:100%;">
    <!-- Header Block -->
    @include('emails.partials.header')
    <!-- // Header Block End -->
    <!-- Body Block -->
    <table width="100%" cellspacing="0" cellpadding="0"
        style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;font-family: sans-serif;">
        <tr style="border-collapse:collapse;">
            <td align="center">
                <table bgcolor="#ffffff" width="600" cellspacing="0" cellpadding="0" align="center" style="text-align: center;padding-top: 20px;background-repeat: no-repeat;background-position: left 25px bottom 25px;background-color: #ffffff;border-left: solid 1px #e9e9e9;border-right: solid 1px #e9e9e9;width: 600px !important;max-width: 600px !important;">
                        <tr align="center" style="border-collapse:collapse;text-align: center">
                            <td style="padding-top:70px;padding-left: 15px;padding-right: 15px;">
                                <p style="margin: 0;line-height: 1.4;font-size: 18px;color: #333333;">
                                    {!! $message_text !!}

                                    <br/>
                                    <br/>
                                    <table style="text-align: left;font-size: 18px;margin: auto;">
                                        @if($eventName)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Event:
                                            </td>
                                            <td>
                                                {{$eventName}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($bookingDate)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Date:
                                            </td>
                                            <td>
                                                {{$bookingDate}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($eventStartTime)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Time:
                                            </td>
                                            <td>
                                                {{$eventStartTime}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($duration)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Duration:
                                            </td>
                                            <td>
                                                {{$duration}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($presenter)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Presenter:
                                            </td>
                                            <td>
                                                {{$presenter}}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </p>
                            </td>
                        </tr>
                        <tr>
                           <td style="padding:25px 0;">&nbsp;</td>
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