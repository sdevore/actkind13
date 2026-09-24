<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActIndexRequest;
use App\Http\Requests\ActStoreRequest;
use App\Http\Requests\ActUpdateRequest;
use App\Http\Resources\Act as ActResource;
use App\Models\Act;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\WithRelations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Act')]
class ActsController extends Controller
{
    #[WithRelations(ActResource::class, ['user', 'appreciates'])]
    public function index(ActIndexRequest $request): AnonymousResourceCollection
    {
        $query = Act::query()
            ->with(['user', 'appreciates'])
            ->withEngagementCounts()
            ->newestFirst();

        return ActResource::collection($request->paginate($query, defaultPerPage: 20));
    }

    public function store(ActStoreRequest $request): JsonResponse
    {
        $act = $request->user()->acts()->create($request->validated());

        return ActResource::make($act)->response()->setStatusCode(201);
    }

    public function show(Act $act): ActResource
    {
        $act->load(['user', 'appreciates.user', 'comments.user'])
            ->loadCount(['appreciates', 'comments']);

        return ActResource::make($act);
    }

    public function update(ActUpdateRequest $request, Act $act): ActResource
    {
        $act->update($request->validated());

        return ActResource::make($act);
    }

    public function destroy(Act $act): Response
    {
        Gate::authorize('delete', $act);

        $act->delete();

        return response()->noContent();
    }
}
