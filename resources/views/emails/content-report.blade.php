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
                                <h1 style="margin: 0;margin-bottom: 30px;line-height: 1.4; font-size: 28px; font-weight: 400;color: #333333;">
                                    Content Report
                                </h1>
                                <br/>
                                <p style="margin: 0;line-height: 1.4;font-size: 18px;color: #333333;">
                                    Please find attached report of content as requested at {!! $requestDatetime !!}.
                                </p>
                            </td>
                        </tr>
                        <tr>
                           <td style="padding:70px 0">&nbsp;</td>
                        </tr>
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