<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppreciateStoreRequest;
use App\Http\Resources\Appreciate as AppreciateResource;
use App\Models\Act;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Appreciate')]
class ActAppreciationsController extends Controller
{
    public function store(AppreciateStoreRequest $request, Act $act): JsonResponse
    {
        $isNew = $act->appreciate($request->user());

        $appreciate = $act->appreciates()
            ->where('user_id', $request->user()->id)
            ->firstOrFail()
            ->load('user');

        if ($isNew) {
            return AppreciateResource::make($appreciate)->response()->setStatusCode(201);
        }

        return AppreciateResource::make($appreciate)->response()->setStatusCode(200);
    }
}
