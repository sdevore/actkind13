<?php

use App\Models\Act;
use App\Models\Comment;
use Livewire\Component;

new class extends Component {
    public $comments;

    public $highlightedComment;

    public Act $act;

    public function mount(Act $act)
    {
        $this->act = $act;
        $this->updateComments();
    }

    public function updateComments($commentId = null)
    {
        $this->comments = $this->act->comments()
            ->orderBy('created_at', 'desc')
            ->with(['user'])
            ->get();
        if ($commentId) {
            $this->highlightedComment = Comment::find($commentId)
                ->with(['user']);
        }
    }
};
?>

<div>
    @auth
        <livewire:acts.add-comment :act="$act" @saved="updateComments" />
    @endauth

    <h5 class="text-md font-bold dark:text-slate-200">Comments</h5>
    @forelse ($comments as $comment)
        <div class="my-4 bg-white p-4 shadow-xl sm:rounded-lg dark:bg-gray-800/30 dark:text-slate-200">
            <div class="flex items-center space-x-0.5">
                @auth()
                    <img class="h-6 w-6 rounded-full object-cover" src="{{ $comment->user->profile_photo_url }}"
                         alt="{{ $comment->user->name }}" />
                    <strong class="pr-1">{{ $comment->user->name }}</strong>
                @endauth

                {{ $comment->created_at->diffForHumans() }}
            </div>
            <div class="prose p-4">
                {!! Str::markdown($comment->body) !!}
            </div>
            @can('view flags')
                <livewire:comments.flag :comment="$comment" />
            @endcan
        </div>
    @empty
        <div class="my-4 bg-white p-4 shadow-xl sm:rounded-lg dark:bg-gray-800/30 dark:text-slate-200">
            <p>No comments yet.</p>
        </div>
    @endforelse
</div>
