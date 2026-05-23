<?php

namespace App\Models;

use App\Notifications\CommentFlagged;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\UnauthorizedException;

/**
 * @property int $id
 * @property string $body
 * @property int $user_id
 * @property int $act_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Comment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'body',
        'user_id',
        'act_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'act_id' => 'integer',
    ];

    /** @return BelongsTo<User, Comment> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Act, Comment> */
    public function act(): BelongsTo
    {
        return $this->belongsTo(Act::class);
    }

    /** @return MorphMany<Appreciate, Comment> */
    public function appreciates(): MorphMany
    {
        return $this->morphMany(Appreciate::class, 'appreciable');
    }

    /** @return MorphMany<Flag, Comment> */
    public function flags(): MorphMany
    {
        return $this->morphMany(Flag::class, 'flaggable');
    }

    public function flag(User $user, string $reason): Flag|Model|bool
    {
        if (! $user->can('flag comments')) {
            throw new UnauthorizedException('You are not authorized to flag acts');
        }
        $flag = $this->flags()->firstOrNew([
            'user_id' => $user->id,
            'flagged_user_id' => $this->user_id,
        ]);
        if (! $flag->id) {
            $flag->reason = $reason;
            $flag->save();
            $this->user->notify(new CommentFlagged($flag));

            return $flag;
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (Comment $comment) {
            foreach ($comment->flags as $flag) {
                $flag->delete();
            }

            foreach ($comment->appreciates as $appreciate) {
                $appreciate->delete();
            }
        });
    }
}
