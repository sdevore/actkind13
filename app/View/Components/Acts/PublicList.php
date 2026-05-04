<?php

namespace App\View\Components\Acts;

use App\Models\Act;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\Component;

class PublicList extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array|Paginator|null $acts = null, public bool $showNames = true, public int $count = 12
    ) {
        //

        if (empty($this->acts) || $this->acts instanceof \__PHP_Incomplete_Class) {
            $page = request()->query('page', 1);
            $this->acts = Cache::flexible('acts.'.$page, [60, 360], function () {
                return Act::with(['appreciates'])
                    ->withCount(['flags', 'comments', 'appreciates'])
                    ->orderBy('created_at', 'desc')
                    ->simplePaginate($this->count);
            });

            if ($this->acts instanceof \__PHP_Incomplete_Class) {
                Cache::forget('acts.'.$page);
                $this->acts = Act::with(['appreciates'])
                    ->withCount(['flags', 'comments', 'appreciates'])
                    ->orderBy('created_at', 'desc')
                    ->simplePaginate($this->count);
            }

            $this->acts->withPath('/acts');
        }
        if (! Auth::check()) {
            $this->showNames = false;
        } else {
            if ($this->showNames) {
                $this->acts->load('user');
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.acts.public-list', ['acts' => $this->acts, 'showNames' => $this->showNames]);
    }
}
