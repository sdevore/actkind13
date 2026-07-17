<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'where_from',
        'message',
    ];

    protected $guarded = [];

    /** @return BelongsTo<Invitation, $this> */
    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function sendInvitation(User $inviter): Invitation
    {
        $invitation = $this->invitation()->make($this->invitationAttributes());

        $invitation = $inviter->sendInvitation($invitation);
        $this->invitation_id = $invitation->id;
        $this->save();

        return $invitation;
    }

    public function resendInvitation(User $inviter): Invitation
    {
        $invitation = $this->invitation()->firstOrFail();

        return $inviter->sendInvitation($invitation, $invitation->user_id);
    }

    /**
     * @return array{code: string, name: string, email: string, message: string}
     */
    private function invitationAttributes(): array
    {
        $appName = config('app.name');

        return [
            'code' => Str::random(10),
            'name' => $this->name,
            'email' => $this->email,
            'message' => "You have been invited to join {$appName}.",
        ];
    }
}
