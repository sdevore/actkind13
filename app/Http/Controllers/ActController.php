<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActStoreRequest;
use App\Http\Requests\ActUpdateRequest;
use App\Models\Act;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ActController extends Controller
{
    public function index(Request $request): View|Collection|Paginator
    {

        if (! Auth::check()) {

            $acts = Cache::remember('acts', 600, function () {
                return Act::with(['appreciates'])
                    ->withCount(['flags', 'comments', 'appreciates'])
                    ->simplePaginate(12);
            });

        } else {

            $acts = Act::with(['user', 'appreciates'])
                ->withCount(['appreciates', 'comments'])
                ->simplePaginate(20);

        }
        $acts->withPath('/acts');
        if ($request->expectsJson()) {
            return $acts;
        } else {
            return view('acts.index', compact('acts'));
        }

    }

    public function mine(Request $request): View
    {

        $acts = Act::with(['user', 'appreciates'])
            ->where('user_id', Auth::id())
            ->withCount(['appreciates', 'comments'])
            ->simplePaginate(20);

        return view('acts.mine', compact('acts'));
    }

    public function create(Request $request): View
    {
        if (! Auth::check()) {
            abort(401);
        }

        return view('acts.create');
    }

    public function store(ActStoreRequest $request): RedirectResponse
    {
        if (! Auth::check()) {
            abort(401);
        }
        $act = Act::create($request->validated());

        $request->session()->flash('act.id', $act->id);

        return redirect()->route('acts.index');
    }

    public function show(Request $request, Act $act): View
    {
        return view('acts.show', compact('act'));
    }

    public function edit(Request $request, Act $act): View
    {
        if (! Auth::check() || (Auth::user()->canNot('edit acts', $act) && Auth::user()->id !== $act->user_id)) {
            abort(401);
        }

        return view('act.edit', compact('act'));
    }

    public function update(ActUpdateRequest $request, Act $act): RedirectResponse
    {
        if (! Auth::check() || (Auth::user()->canNot('edit acts', $act) && Auth::user()->id !== $act->user_id)) {
            abort(401);
        }
        $act->update($request->validated());

        $request->session()->flash('act.id', $act->id);

        return redirect()->route('acts.index');
    }

    public function destroy(Request $request, Act $act): RedirectResponse
    {
        if (! Auth::check() || (Auth::user()->canNot('delete acts', $act) && Auth::user()->id !== $act->user_id)) {
            abort(401);
        }
        $act->delete();

        return redirect()->route('acts.index');
    }
}
