<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActStoreRequest;
use App\Http\Requests\ActUpdateRequest;
use App\Http\Resources\Act as ActResource;
use App\Models\Act;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ActController extends Controller
{
    public function index(Request $request): View
    {
        $acts = $this->fetchIndexActs();
        $acts->withPath('/acts');

        return view('acts.index', compact('acts'));
    }

    /**
     * @response Paginator<int, Act>
     */
    public function api_index(Request $request): Paginator
    {
        $acts = $this->fetchIndexActs();
        $acts->withPath('/acts');

        return $acts;
    }

    public function mine(Request $request): View
    {
        $acts = $this->fetchMineActs();

        return view('acts.mine', compact('acts'));
    }

    /**
     * @response Paginator<int, Act>
     */
    public function api_mine(Request $request): Paginator
    {
        return $this->fetchMineActs();
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

    /**
     * @response Act
     */
    public function api_store(ActStoreRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            abort(401);
        }

        $act = Act::create($request->validated());

        return response()->json($act, 201);
    }

    public function show(Request $request, Act $act): View
    {
        return view('acts.show', compact('act'));
    }

    public function api_show(Request $request, Act $act): ActResource
    {
        $relations = ['user', 'appreciates', 'comments'];

        return ActResource::make($act->load($relations));
    }

    public function api_public_show(Request $request, Act $act): ActResource
    {
        $relations = ['appreciates', 'comments'];

        return ActResource::make($act->loadCount($relations));
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

    private function fetchIndexActs(): Paginator
    {
        if ($this->isAuth()) {
            return Act::with(['user', 'appreciates'])
                ->withCount(['appreciates', 'comments'])
                ->simplePaginate(20);
        }

        return Cache::remember('acts', 600, function () {
            return Act::with(['appreciates'])
                ->withCount(['flags', 'comments', 'appreciates'])
                ->simplePaginate(12);
        });
    }

    private function fetchMineActs(): Paginator
    {
        return Act::with(['user', 'appreciates'])
            ->where('user_id', Auth::id())
            ->withCount(['appreciates', 'comments'])
            ->simplePaginate(20);
    }
}
