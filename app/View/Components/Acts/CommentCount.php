<?php

namespace App\View\Components\Acts;

use App\Models\Act;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CommentCount extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public Act $act
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.acts.comment-count', ['act' => $this->act, 'count' => $this->act->comments_count]);
    }
}
