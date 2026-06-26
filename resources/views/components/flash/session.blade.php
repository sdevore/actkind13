@php
    $notices = session()->only(['status', 'error', 'warning', 'info', 'success']);

    $show = !empty($notices);
    $ids = [];
@endphp

@if ($show)
    <div class="fixed top-0 right-0 flex flex-col space-y-4">
        @foreach ($notices as $style => $message)
            @php
                $icon = match ($style) {
                    'error' => 'heroicon-o-x-circle',
                    'warning' => 'heroicon-o-exclamation-circle',
                    'info' => 'heroicon-o-information-circle',
                    'success' => 'heroicon-o-check-circle',
                    default => 'heroicon-o-information-circle',
                };
                $css = match ($style) {
                    'error' => 'border-red-500 bg-red-100/90 text-red-700',
                    'warning' => 'border-yellow-500 bg-yellow-100/90 text-yellow-700',
                    'info' => 'border-blue-500 bg-blue-100/90 text-blue-700',
                    'success' => 'border-green-500 bg-green-100/90 text-green-700',
                    default => 'border-indigo-500 bg-indigo-100/90 text-indigo-700',
                };
                $id = uniqid('flash-');
                $ids[] = $id;
            @endphp
            <div id="{{ $id }}" class="{{ $css }} max-w-lg rounded-b border-t-4 px-4 py-3 shadow-md" role="alert">
                <div class="flex items-center">
                    <div class="mr-2 w-5 py-1">
                        <x-icon :name="$icon" />
                    </div>
                    <div>
                        <p class="font-bold">{{ $message }}</p>
                    </div>
                </div>
            </div>
        @endforeach

        <script>
            setTimeout(() => {
                const ids = @json ($ids);
                ids.forEach((id) => {
                    const element = document.getElementById(id);
                    element.classList.add('transition-opacity', 'duration-500', 'opacity-0');
                    element.addEventListener('transitionend', function () {
                        element.remove();
                    });
                });
            }, 2000);
        </script>
    </div>
@endif
