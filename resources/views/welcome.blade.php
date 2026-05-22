<x-layouts::app :title="__('Welcome to ActKind.online')">
    <section class="m-6 mb-2 border-b-2 border-neutral-500/50 text-center text-slate-700 dark:border-neutral-500/50 dark:text-slate-200">
        <h1 class="font-serif text-slate-600 dark:text-slate-200">
            ActKind
            <span class="font-sans">Online</span>
        </h1>
        <p>To share acts of kindness will inspire more acts of kindness in our communities.</p>
        @guest ()
            <p class="mx-auto w-3/4 text-left lg:w-1/2">Working to make the world a better place by helping share and to be thanked for those intentional acts of kindness that we want to do. Some people call these
            <strong class="dark:text-slate-100">random acts of kindness</strong>
            or
            <strong class="dark:text-slate-100">small gestures</strong>
            , but these acts can have an impact and they can be infectious. The goal of this project is to try to give people a place to share these acts with others. I believe that when you can see the kind acts of others, you may be inspired to take a moment and be intentionally kind to someone, a person you know, or a person you see who could use lift up, or someone you may never see.</p>
            <p>We are currently invite only but time may change this.</p>
            <p><a href="{{ route('about') }}" class="dark:text-emerald-100">More about ActKind.online</a></p>
        @endguest ()

        @auth ()
            <section class="mb-4 flex justify-center"></section>
        @endauth
    </section>

    <section>
        <x-acts.acts :acts="$acts" :show-names="true" />
    </section>
</x-layouts::app>
