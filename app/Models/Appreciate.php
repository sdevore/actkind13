<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $appreciable_id
 * @property string $appreciable_type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Appreciate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
    ];

    public function act(): MorphTo
    {
        return $this->morphTo('appreciable');
    }

    public function comment(): MorphTo
    {
        return $this->morphTo('appreciable');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appreciable(): MorphTo
    {
        return $this->morphTo();
    }
}
