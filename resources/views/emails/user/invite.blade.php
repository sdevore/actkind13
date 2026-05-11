<x-mail::message>

### Hello {{ $invitation->name }} You have been invited by {{ $invitation->user->name }} to join {{ config('app.name') }}. This is a place for sharing and recognizing acts of kindness. If you would like to join, please click the link below to register.

<x-mail::button :url="route('register', ['code' => $invitation->code])">Go to signup</x-mail::button>
<x-mail::panel>
{{ $invitation->message }}
</x-mail::panel>
Thanks,
<br />
{{ $invitation->user->name }} on behalf of {{ config('app.name') }}

<x-mail::panel>
While site is in early preview you may need to use the following username and password just to access the site:

- **Username:** actkind
- **Password:** online

If you want to just post a test act of kindness or a test comment you can put `delete_me` in the post or comment and
before the site is opened up beyond the preview we will remove it. Or you can email me and I can delete it for
you. Thanks for your understanding and helping us test the site.
</x-mail::panel>
<x-mail::panel>
If you did not request this invitation or do not know {{ $invitation->user->name }} who invited you, please ignore this
email.
</x-mail::panel>

<x-mail::panel>
If you have any questions or need help, please email us at [{{ config('mail.from.address') }}](mailto:{{ config('
mail.from.address') }}).

Also, your invitation code is: {{ $invitation->code }} and your invitation email is: {{ $invitation->email }}. You need
to use the same email to register, if you need to change it, you should reach out to the person who invited you, and
they
can send you a new invitation to your preferred email.
</x-mail::panel>

-

</x-mail::message>
