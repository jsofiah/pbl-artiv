<x-mail::message>
# {{ $title }}

{{ $body }}

<x-mail::button :url="$url">
Lihat Detail
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>