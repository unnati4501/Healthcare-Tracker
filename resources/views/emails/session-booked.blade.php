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
                                    Hi @if($toEmail == 'user')
                                    {{$userFirstName}},
                                    @else
                                    {{$wsFirstName}}
                                    @endif
                                    <br/><br/>
                                    
                                    @if($isRescheduled == false)
                                        @if($toEmail == 'user')
                                        Your {{$serviceName}} session with {{$wsName}} on {{$eventDate}} has been confirmed.
                                        @else
                                        You received a booking request for {{$serviceName}} session.
                                        @endif
                                    @elseif($isRescheduled == true)
                                        @if($toEmail == 'user')
                                        Your {{$serviceName}} session with {{$wsName}} on {{$eventDate}} has been rescheduled.
                                        @elseif($toEmail == 'zca')
                                        Your {{$serviceName}} session has been rescheduled.
                                        @else
                                        Your {{$serviceName}} session with {{$userName}} on {{$eventDate}} has been rescheduled.
                                        @endif
                                    @endif
                                    <br/>
                                    <br/>
                                    <table style="text-align: left;font-size: 18px;margin: auto;">
                                        @if($toEmail == 'wellbeing_specialist')
                                            <tr>
                                                <td style="font-weight: bold;">
                                                    Name:
                                                </td>
                                                <td>
                                                    {{$userName}}
                                                </td>
                                            </tr>
                                        @endif
                                        @if($toEmail == 'wellbeing_specialist' || $toEmail == 'zca')
                                            @if($companyName)
                                            <tr>
                                                <td style="font-weight: bold;">
                                                    Company:
                                                </td>
                                                <td>
                                                    {{$companyName}}
                                                </td>
                                            </tr>
                                            @endif
                                        @endif
                                        @if($isGroup == true)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Participants:
                                            </td>
                                            <td>
                                                {{$totalParticipants}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($eventDate)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Date:
                                            </td>
                                            <td>
                                                {{$eventDate}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($eventTime)
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Time:
                                            </td>
                                            <td>
                                                {{$eventTime}}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($duration && $isGroup == false && $toEmail == 'user')
                                        <tr>
                                            <td style="font-weight: bold;">
                                                Duration:
                                            </td>
                                            <td>
                                                {{$duration}} Minutes
                                            </td>
                                        </tr>
                                        @endif
                                        
                                        <tr>
                                            <td style="margin: 0;line-height: 1.4;font-size: 18px;color: #333333;">
                                               
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                
                                            </td>
                                        </tr>
                                    </table>
                                </p>
                                @if($joinSessionLink && $isOnline == true)
                                    <p style="font-size:18px;margin-bottom:25px;">Please join the session at the appropriate date and time by clicking below.</p>
                                    <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{!! $joinSessionLink !!}" style="width:260px;height:42px;v-text-anchor:middle;max-width:260px;min-width:260px;" arcsize="63%" stroke="f" fillcolor="#5261AC">
                                            <v:textbox inset="0,0,0,0">
                                            <w:anchorlock/>
                                            <center>
                                        <![endif]-->
                                        <a href="{!! $joinSessionLink !!}" target="_blank"
                                        style="color:#ffffff;display:block;font-family:sans-serif;font-size:13px;font-weight:400;line-height:40px;text-align:center;text-decoration:none;-webkit-text-size-adjust:none;background-color:#5261AC;border-radius:25px;width:260px;height:40px;min-height:40px;min-width:260px;max-width: 260px;max-height: 40px;margin:0 auto;" class="template-btn" title="Join">Join</a>
                                        <!--[if mso]>
                                            </center>
                                        </v:roundrect>
                                    <![endif]-->
                                @endif
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