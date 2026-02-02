<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ $branding->headerContent ?: config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        body,
        body *:not(html):not(style):not(br):not(tr):not(code) {
        @if($branding->font === \App\Support\MailConfiguration::FONT_ARIAL)
            font-family: Arial, Helvetica, sans-serif;
        @elseif($branding->font === \App\Support\MailConfiguration::FONT_HELVETICA)
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        @elseif($branding->font === \App\Support\MailConfiguration::FONT_GEORGIA)
            font-family: Georgia, Times, "Times New Roman", serif;
        @elseif($branding->font === \App\Support\MailConfiguration::FONT_TIMES_NEW_ROMAN)
            font-family: "Times New Roman", Times, Georgia, serif;
        @else
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
        @endif
        }

        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }

        @if($branding->alignment === 'center')
        .header {
            text-align: center;
        }

        .footer {
            text-align: center;
        }

        .footer p {
            text-align: center;
        }
        @endif
    </style>
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                            <tr>
                                <td class="header">
                                    @if($branding->headerLogo)
                                        <img class="logo" src="{{ $branding->headerLogo }}" alt="{{ $branding->headerContent ?: '' }}">
                                    @else
                                    {{ $branding->headerContent ?: config('app.name') }}
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="content-cell">
                                    {!! \App\Support\SafeMarkdownConverter::parse($message) !!}
                                </td>
                            </tr>

                            @if($branding->footerContent)
                            <tr>
                                <td>
                                    <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                        <tr>
                                            <td class="footer-cell" align="center">
                                                {!! \App\Support\SafeMarkdownConverter::parse($branding->footerContent) !!}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
