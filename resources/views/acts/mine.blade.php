<x-layouts::app :title="__('My Acts of Kindness')">
    <section class="border-b border-gray-200 bg-white p-6 lg:p-8 dark:border-gray-700 dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent">
        <h3 class="mb-4 text-center text-lg leading-tight font-semibold text-gray-800 dark:text-gray-200">Add your Act of Kindness</h3>
        <livewire:acts.create class="mx-auto w-5/6 md:w-3/4 lg:w-1/2" />
    </section>
    <section>
        <x-acts.acts :acts="$acts" :show-names="false" />
    </section>
</x-layouts::app>
