<x-layouts::app :title="__('Acts of Kindness')">
    @if ($acts->count())
        <section>
            <x-acts.acts :acts="$acts" :show-names="false" />
        </section>
    @else
        <section class="flex h-64 items-center justify-center">
            <p class="text-gray-500 dark:text-gray-400">You haven't shared any acts of kindness yet. Start by sharing one above!</p>
        </section>
    @endif
</x-layouts::app>
