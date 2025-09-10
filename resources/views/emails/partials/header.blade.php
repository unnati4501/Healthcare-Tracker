<!--[if gte mso 9]>
    <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
        <v:fill type="tile" color="#ffffff"></v:fill>
    </v:background>
<![endif]-->
<table width="100%" cellspacing="0" cellpadding="0"
    style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;font-family: sans-serif;">
    <tr style="border-collapse:collapse;">
        <td align="center" style="text-align:center;min-width:100%;max-width:100%;width:100%;">
            @if(!empty($emailHeader))
            <table width="600" cellspacing="0" cellpadding="0" align="center" style="text-align: center;">
                <tr style="border-collapse:collapse;text-align: center" align="center">
                    <td>
                        <a href="{{ $brandingRedirection }}" style="display:block;">
                            <img src="{{ $emailHeader }}" style="display:block;"/>
                        </a>
                    </td>
                </tr>
            </table>
            @else
            <table bgcolor="#5261AC" width="600" cellspacing="0" cellpadding="0" align="center" style="text-align: center;background-color: #5261AC;">
                <tr style="border-collapse:collapse;text-align: center" align="center">
                    <td style="padding-top:20px;padding-left:20px;padding-bottom:20px;padding-right:20px;text-align: center;" align="center">
                        <a href="{{ $brandingRedirection }}" style="display:inline-block;">
                            <img src="{{ $logo }}" />
                        </a>
                    </td>
                </tr>
            </table>
            @endif
        </td>
    </tr>
</table>