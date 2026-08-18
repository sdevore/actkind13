<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Http\Resources\Comment as CommentResource;
use App\Models\Act;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(CommentStoreRequest $request, Act $act): JsonResponse
    {
        $comment = $act->comment(Auth::user(), $request->validated()['body']);

        return CommentResource::make($comment->load('user'))
            ->response()
            ->setStatusCode(201);
    }

    public function update(CommentUpdateRequest $request, Comment $comment): CommentResource
    {
        $comment->update($request->validated());

        return CommentResource::make($comment->load('user'));
    }

    public function destroy(Comment $comment): Response
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
