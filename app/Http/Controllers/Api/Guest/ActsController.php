<?php

namespace App\Http\Controllers\Api\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActIndexRequest;
use App\Http\Resources\Act as ActResource;
use App\Models\Act;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Act')]
class ActsController extends Controller
{
    /**
     * Public feed: counts only. A request carrying a valid Sanctum token also gets `user` and `appreciates`.
     */
    public function index(ActIndexRequest $request): AnonymousResourceCollection
    {
        $query = Act::query()
            ->withEngagementCounts()
            ->newestFirst();

        if ($request->user('sanctum')) {
            $query->with(['user', 'appreciates']);
        }

        return ActResource::collection($request->paginate($query, defaultPerPage: 12));
    }

    public function show(Act $act): ActResource
    {
        return ActResource::make($act->loadCount(['appreciates', 'comments']));
    }
}
