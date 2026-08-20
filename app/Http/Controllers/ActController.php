<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActStoreRequest;
use App\Http\Requests\ActUpdateRequest;
use App\Http\Resources\Act as ActResource;
use App\Models\Act;
use Dedoc\Scramble\Attributes\WithRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ActController extends Controller
{
    public function index(Request $request): View
    {
        $acts = $this->fetchIndexActs($request);
        $acts->withPath('/acts');

        return view('acts.index', compact('acts'));
    }

    public function api_index(Request $request): AnonymousResourceCollection
    {
        $acts = $this->fetchIndexActs($request);
        $acts->withPath('/acts');

        return ActResource::collection($acts);
    }

    /**
     * Always authenticated (behind auth:sanctum), so the response always
     * includes `user` and `appreciates` — unlike api_index, which also
     * backs the guest /acts route and can't declare a single fixed shape.
     */
    #[WithRelations(ActResource::class, ['user', 'appreciates'])]
    public function api_index_private(Request $request): AnonymousResourceCollection
    {
        return ActResource::collection($this->fetchIndexActs($request));
    }

    public function mine(Request $request): View
    {
        $acts = $this->fetchMineActs($request);

        return view('acts.mine', compact('acts'));
    }

    #[WithRelations(ActResource::class, ['user', 'appreciates'])]
    public function api_mine(Request $request): AnonymousResourceCollection
    {
        return ActResource::collection($this->fetchMineActs($request));
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

        $act = Act::create([...$request->validated(), 'user_id' => Auth::id()]);

        $request->session()->flash('act.id', $act->id);

        return redirect()->route('acts.index');
    }

    public function api_store(ActStoreRequest $request): JsonResponse
    {
        if (! Auth::check()) {
            abort(401);
        }

        $act = Act::create([...$request->validated(), 'user_id' => Auth::id()]);

        return ActResource::make($act)->response()->setStatusCode(201);
    }

    public function show(Request $request, Act $act): View
    {
        return view('acts.show', compact('act'));
    }

    public function api_show(Request $request, Act $act): ActResource
    {
        $relations = ['user', 'appreciates.user', 'comments.user'];

        return ActResource::make($act->load($relations)
            ->loadCount(['appreciates', 'comments']));
    }

    public function api_public_show(Request $request, Act $act): ActResource
    {
        $relations = ['appreciates', 'comments'];

        return ActResource::make($act->loadCount($relations));
    }

    public function api_update(ActUpdateRequest $request, Act $act): ActResource
    {
        $act->update($request->validated());

        return ActResource::make($act);
    }

    public function api_destroy(Act $act): Response
    {
        Gate::authorize('delete', $act);

        $act->delete();

        return response()->noContent();
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

    private function fetchIndexActs(Request $request): Paginator
    {
        if ($this->isAuth()) {
            $perPage = $this->resolvePerPage($request, default: 20);

            return Act::with(['user', 'appreciates'])
                ->withCount(['appreciates', 'comments'])
                ->orderByDesc('created_at')
                ->simplePaginate($perPage);
        }

        $perPage = $this->resolvePerPage($request, default: 12);
        $page = $request->integer('page', 1);

        return Cache::remember("acts.{$page}.{$perPage}", 600, function () use ($perPage) {
            return Act::withCount(['comments', 'appreciates'])
                ->orderByDesc('created_at')
                ->simplePaginate($perPage);
        });
    }

    private function resolvePerPage(Request $request, int $default, int $max = 50): int
    {
        $perPage = (int) $request->query('per_page', $default);

        if ($perPage < 1) {
            return $default;
        }

        return min($perPage, $max);
    }

    private function fetchMineActs(Request $request): Paginator
    {
        $perPage = $this->resolvePerPage($request, default: 20);

        return Act::with(['user', 'appreciates'])
            ->where('user_id', Auth::id())
            ->withCount(['appreciates', 'comments'])
            ->orderByDesc('created_at')
            ->simplePaginate($perPage);
    }
}
