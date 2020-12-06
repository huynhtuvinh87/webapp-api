@if (isset($color))
    <style>
        .button{
            background-color: {{ $color ?? '#0B6739' }} !important;
            border-color: {{ $color ?? '#0B6739' }} !important;
        }
    </style>
@endif
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="center">
                        <table border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center">
                                    <a href="{{ $url }}" class="button button-primary" target="_blank">{{ $slot }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>