<!doctype html>
<html lang="es"><body style="font-family:Arial,sans-serif;color:#171717;line-height:1.5">
    <h1 style="font-size:20px">{{ $heading }}</h1>
    <p>{{ $body }}</p>
    @if ($actionUrl)<p><a href="{{ $actionUrl }}">{{ $actionLabel }}</a></p>@endif
    <p>SAFEAPP · Infinity</p>
</body></html>
