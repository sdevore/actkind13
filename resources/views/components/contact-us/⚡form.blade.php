<?php

use App\Mail\ContactUsMailable;
use App\Models\ContactUs;
use Livewire\Component;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

new class extends Component {
    public string $name = '';

    public string $email = '';

    public string $where_from = '';

    public string $message = '';

    public string $cf_turnstile_response = '';


    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'max:255', 'min:5'],
            'where_from' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:255', 'min:10'],
            'cf_turnstile_response' => ['required', new Turnstile,
            ]
        ];
    }

    public function submit()
    {

        $this->validate();
        // only create a new contact if there isn't one with the same email
        $contact = ContactUs::where('email', $this->email)->first();
        if ($contact) {
            $contact->name = $this->name;
            $contact->where_from = $this->where_from;
            $contact->message = $this->message;
            $contact->save();
            Mail::send(new ContactUsMailable($contact));
            $this->reset();
            session()->flash('danger', 'This email has already contacted us in the past.');
            $this->redirect(route('home'));
        } else {
            $contact = new \App\Models\ContactUs;
            $contact->name = $this->name;
            $contact->email = $this->email;
            $contact->where_from = $this->where_from;
            $contact->message = $this->message;
            $contact->save();
            Mail::send(new ContactUsMailable($contact));
            $this->reset();
            session()->flash('success', 'Your message has been sent successfully.');
            $this->redirect(route('home'));
        }
    }
};
?>

<div>
    <div class="p-2">
        <form wire:submit.prevent="submit" class="flex flex-col">
            <label for="name" class="block">
                <span class="text-gray-700 dark:text-slate-200">Name</span>
                <input
                    class="form-input focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 placeholder-gray-200 shadow-sm focus:border-green-300 focus:ring focus:ring-indigo-200 dark:border-gray-800 dark:bg-slate-200/20"
                    wire:model="name"
                    placeholder="Kind person"
                    id="name"
                    type="text"
                    autocomplete="off"
                    required
                />
            </label>
            @error ('name')
                <span class="mt-1 ml-1 text-sm text-red-700">{{ $message }}</span>
            @enderror

            <label for="email" class="mt-4 block">
                <span class="text-gray-700 dark:text-slate-200">
                    Email
                    <span class="text-xs text-green-600 dark:text-slate-200/80">Where we will send an invitation</span>
                </span>
                <input
                    class="form-input focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 placeholder-gray-200 shadow-sm focus:border-green-300 focus:ring focus:ring-indigo-200 dark:border-gray-800 dark:bg-slate-200/20"
                    wire:model="email"
                    placeholder="kindness@example.com"
                    id="email"
                    autocomplete="off"
                    type="email"
                    required
                />
            </label>
            @error ('email')
                <span class="mt-1 ml-1 text-sm text-red-700">{{ $message }}</span>
            @enderror

            <label for="where_from" class="mt-4 hidden sm:block">
                <span class="text-gray-700 dark:text-slate-200">How did you hear about AckKind.online?</span>
                <input
                    class="form-input focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 placeholder-gray-200 shadow-sm focus:border-green-300 focus:ring focus:ring-indigo-200 dark:border-gray-800 dark:bg-slate-200/20"
                    wire:model="where_from"
                    placeholder="I heard from..."
                    id="where_from"
                    autocomplete="off"
                    type="text"
                />
            </label>
            @error ('where_from')
                <span class="mt-1 ml-1 text-sm text-red-700">{{ $message }}</span>
            @enderror

            <label class="mt-4 block">
                <span class="text-green-700 dark:text-slate-200">
                    Message
                    <span class="text-xs text-green-600 dark:text-slate-200/80">Why do you want to join ActKind.online</span>
                </span>
                <textarea
                    class="form-textarea focus:ring-opacity-50 mt-1 block w-full rounded-md border-gray-300 placeholder-gray-200 shadow-sm focus:border-green-300 focus:ring focus:ring-indigo-200 dark:border-gray-800 dark:bg-slate-200/20"
                    wire:model="message"
                    rows="3"
                    placeholder="I am interested in..."
                ></textarea>
            </label>
            @error ('message')
                <span class="mt-1 ml-1 text-sm text-red-700">{{ $message }}</span>
            @enderror
            <x-turnstile id="my_widget" wire:model="cf_turnstile_response" />
            @error ('cf_turnstile_response')
                <span class="mt-1 ml-1 text-sm text-red-700">{{ $message }}</span>
            @enderror
            <div class="mt-2">
                <x-controls.success-button type="submit" class="w-auto">Send us a message</x-controls.success-button>
            </div>
        </form>
    </div>
</div>
