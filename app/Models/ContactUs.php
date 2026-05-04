<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ContactUs extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function invitation()
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
        $invitation = Invitation::find($this->invitation_id)->first();

        return Auth::user()->sendInvitation($invitation, $invitation->user_id);
    }
}
