<?php

namespace App\Models;

use App\Mail\InviteUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Mail;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $code
 * @property Carbon $joined
 * @property int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Invitation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'code',
        'message',
        'joined',
        'user_id',
        'joined_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'joined' => 'datetime',
        'user_id' => 'integer',
        'joined_id' => 'integer',
    ];

    /** @return BelongsTo<User, Invitation> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, Invitation> */
    public function invited_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User, Invitation> */
    public function joinedAs(): BelongsTo
    {
        return $this->belongsTo(User::class, 'joined_id');
    }

    public function send(bool $shouldQueue = false): void
    {
        $this->increment('send_ct');
        if ($shouldQueue) {
            Mail::to($this->email, $this->name)
                ->bcc(config('mail.from.address'))
                ->queue(new InviteUser($this));

            return;
        }

        Mail::to(new Address($this->email, $this->name))
            ->bcc('sdevore@me.com')
            ->cc(config('mail.from.address'))
            ->send(new InviteUser($this));

    }
}
