<?php

namespace App\Models;

use App\Enums\ActType;
use App\Notifications\ActAppreciated;
use App\Notifications\ActCommented;
use App\Notifications\ActFlagged;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\UnauthorizedException;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $type
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Act extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'type',
        'user_id',

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'type' => ActType::class,
    ];

    /** @return BelongsTo<User, Act> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Comment, Act> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function comment(User $user, string $body): Comment
    {
        $comment = new Comment([
            'body' => $body,
            'user_id' => $user->id,
        ]);
        $this->comments()->save($comment);

        $this->user->notify(new ActCommented($comment));

        return $comment;
    }

    /** @return MorphMany<Appreciate, Act> */
    public function appreciates(): MorphMany
    {
        return $this->morphMany(Appreciate::class, 'appreciable');
    }

    /**
     * @throws Exception
     */
    public function appreciate(User $user): bool
    {
        if ($this->user_id === $user->id) {
            throw new Exception('You cannot appreciate your own act');
        }
        $appreciation = $this->appreciates()->firstOrNew(
            ['user_id' => $user->id]
        );
        if (! $appreciation->exists) {
            $appreciation->save();
            $this->user->notify(new ActAppreciated($appreciation));

            return true;
        }

        return false;
    }

    /** @return MorphMany<Flag, Act> */
    public function flags(): MorphMany
    {
        return $this->morphMany(Flag::class, 'flaggable');
    }

    public function flag(User $user, string $reason): Flag|Model|bool
    {
        if (! $user->can('flag acts')) {
            throw new UnauthorizedException('You are not authorized to flag acts');
        }
        $flag = $this->flags()->firstOrNew([
            'user_id' => $user->id,
            'flagged_user_id' => $this->user_id,
        ]);
        if (! $flag->id) {
            $flag->reason = $reason;
            $flag->save();
            $this->user->notify(new ActFlagged($flag));

            return $flag;
        }

        return false;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function (Act $act) {
            foreach ($act->comments as $comment) {
                $comment->delete();
            }

            foreach ($act->appreciates as $appreciate) {
                $appreciate->delete();
            }

            foreach ($act->flags as $flag) {
                $flag->delete();
            }
        });
    }
}
