<?php

namespace App\View\Components\Acts;

use App\Models\Act;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class ActCard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Act $act,
        public bool $showName = true,
    ) {
        if (! Auth::check()) {
            $this->showName = false;
        }

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.acts.act-card');
    }
}
