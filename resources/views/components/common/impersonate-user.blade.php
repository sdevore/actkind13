@props (['user',])
<span>
    @canImpersonate ($guard = null)
        <a href="{{ route('impersonate', $user->id) }}" class="btn btn-sm">
            <x-icon name="fas-user-group" class="mr-2 h-4 w-4"></x-icon>
            <span>Impersonate {{ $user->name }}</span>
        </a>
    @endCanImpersonate
</span>
