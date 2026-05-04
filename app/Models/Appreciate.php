<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Appreciate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appreciable(): MorphTo
    {
        return $this->morphTo();
    }
}
