<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActIndexRequest;
use App\Http\Resources\Act as ActResource;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\WithRelations;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Act')]
class MyActsController extends Controller
{
    #[WithRelations(ActResource::class, ['user', 'appreciates'])]
    public function index(ActIndexRequest $request): AnonymousResourceCollection
    {
        $query = $request->user()
            ->acts()
            ->with(['user', 'appreciates'])
            ->withEngagementCounts()
            ->newestFirst();

        return ActResource::collection($request->paginate($query, defaultPerPage: 20));
    }
}
