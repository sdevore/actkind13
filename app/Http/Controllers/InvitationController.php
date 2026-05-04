<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $invitations = Invitation::where('user_id', $user->id)
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('invitation.index', compact('invitations'));
    }

    public function send(Request $request, Invitation $invitation): RedirectResponse
    {
        $invitation->send(shouldQueue: false);
        $request->session()->flash('invitation.id', $invitation->id);

        return redirect()->route('invitations.index');
    }

    public function show(Request $request, Invitation $invitation): View
    {
        Gate::authorize('view', $invitation);

        return view('invitation.show', compact('invitation'));
    }
}
