<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Resources\Comment as CommentResource;
use App\Models\Act;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;

#[Group('Comment')]
class ActCommentsController extends Controller
{
    public function store(CommentStoreRequest $request, Act $act): JsonResponse
    {
        $comment = $act->comment($request->user(), $request->validated('body'));

        return CommentResource::make($comment->load('user'))
            ->response()
            ->setStatusCode(201);
    }
}
