<div class="row-full" bgcolor="#f69131" style="
    margin: 30px 0;
    padding: 15px 0;
    min-width: 100%;
    text-align: center;
    background-color: {{ $background_color ?? '#f69131' }};
    color: {{ $text_color ?? 'white' }};
    font-size: 1.5em;
    font-weight: bold;
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    ">
    @if ($link)
        <a href="{{ $link }}" style="
            text-decoration: none;
            color: {{ $text_color ?? 'white' }};
        ">{{ $slot }}</a>
    @else
        {{ $slot }}
    @endif
</div>
