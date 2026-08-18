<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppreciateStoreRequest;
use App\Http\Resources\Appreciate as AppreciateResource;
use App\Models\Act;
use App\Models\Appreciate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AppreciateController extends Controller
{
    public function store(AppreciateStoreRequest $request, Act $act): JsonResponse
    {
        $isNew = $act->appreciate(Auth::user());

        $appreciate = $act->appreciates()
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->load('user');

        if ($isNew) {
            return AppreciateResource::make($appreciate)->response()->setStatusCode(201);
        }

        return AppreciateResource::make($appreciate)->response()->setStatusCode(200);
    }

    public function destroy(Appreciate $appreciation): Response
    {
        Gate::authorize('delete', $appreciation);

        $appreciation->delete();

        return response()->noContent();
    }
}
