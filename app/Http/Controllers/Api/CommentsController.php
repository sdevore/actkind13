<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentUpdateRequest;
use App\Http\Resources\Comment as CommentResource;
use App\Models\Comment;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Comment')]
class CommentsController extends Controller
{
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
