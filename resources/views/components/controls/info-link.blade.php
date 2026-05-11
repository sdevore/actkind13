<a
    {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-sky-800 border border-sky-300 dark:border-sky-500 rounded-md font-semibold text-xs text-sky-700 dark:text-sky-300 uppercase tracking-widest shadow-sm hover:bg-sky-50 dark:hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-sky-800 disabled:opacity-25 transition ease-in-out duration-150']) }}
>
    {{ $slot }}
</a>
