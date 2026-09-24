<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class InvitationsController extends Controller
{
    public function index(Request $request): View
    {
        $invitations = Invitation::query()
            ->where('user_id', $request->user()->id)
            ->with('user')
            ->orderByDesc('updated_at')
            ->get();

        return view('invitations.index', compact('invitations'));
    }

    public function show(Invitation $invitation): View
    {
        Gate::authorize('view', $invitation);

        return view('invitations.show', compact('invitation'));
    }
}
