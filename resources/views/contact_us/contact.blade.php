<x-layouts::guest :title="__('Contact ActKind.online')">
    <section class="m-2 mb-2 border-b-2 border-neutral-500/50 text-center text-sm text-slate-700 md:text-lg lg:m-6 dark:text-slate-200">
        <p class="mx-auto text-left md:w-3/4 lg:w-1/2">Working to make the world a better place by helping share and to be thanked for those intentional acts of kindness that we want to do. Some people call these
        <strong class="hover:underline dark:text-slate-100">random acts of kindness</strong>
        or
        <strong class="hover:underline dark:text-slate-100">small gestures</strong>
        , but these acts can have an impact and they can be infectious. The goal of this project is to try to give people a place to share these acts with others. I believe that when you can see the kind acts of others, you may be inspired to take a moment and be intentionally kind to someone, a person you know, or a person you see who could use lift up, or someone you may never see.</p>
        <p>We are currently invite only but time may change this.</p>
        <p><a class="hover:text-slate-300 hover:underline dark:text-slate-100" href="{{ route('about') }}">More about ActKind.online</a></p>
    </section>
    <div class="py-4 pb-20">
        <div class="mx-auto max-w-xl bg-slate-400/10 px-2 shadow lg:px-8">
            <livewire:contact-us.form></livewire:contact-us.form>
        </div>
    </div>
</x-layouts::guest>
