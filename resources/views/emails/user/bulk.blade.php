<x-mail::message>
Hello {{ $user->name }},

{{ $data['body'] }}

Thanks,
<br />
{{ $data['name'] }}
</x-mail::message>
