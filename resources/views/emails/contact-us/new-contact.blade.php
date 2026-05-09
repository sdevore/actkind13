<x-mail::message>

# Hurray, someone would like to join! 🎉

**Name:** {{ $contactUs->name }}

**Email:** {{ $contactUs->email }}

**Where from:** {{ $contactUs->where_from }}

**Message**
> {{ $contactUs->message }}

    Thanks,

    {{ config('app.name') }}

</x-mail::message>
