<?php

use Livewire\Component;

new class extends Component {
    public $acts;

    public $showNames = false;

    public function mount()
    {
        if (auth()->check()) {
            $this->showNames = true;
            $this->acts = \App\Models\Act::with(['user'])
                ->where('user_id', '!=', auth()->id())
                ->latest()
                ->take(10)
                ->get();
        } else {
            $this->acts = \App\Models\Act::latest()
                ->take(10)
                ->get();
        }
    }
}
?>

<div>
    @ray($acts)
    <x-acts.acts :acts="$acts" :show-names="$showNames" />
</div>
