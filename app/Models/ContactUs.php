<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $where_from
 * @property string $message
 * @property ?int $invitation_id
 * @property ?int $user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class ContactUs extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    /** @return BelongsTo<Invitation, ContactUs> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function sendInvitation()
    {
        $invitation = $this->invitation()->make(
            [
                'name' => $this->name,
                'email' => $this->email,
                'message' => 'You have been invited to join '.config('app.name').'.',
            ]
        );

        $invitation = Auth::user()->sendInvitation($invitation);
        $this->invitation_id = $invitation->id;
        $this->save();

        return $invitation;
    }

    public function resendInvitation()
    {
        $invitation = Invitation::findOrFail($this->invitation_id);

        return Auth::user()->sendInvitation($invitation, $invitation->user_id);
    }
}
