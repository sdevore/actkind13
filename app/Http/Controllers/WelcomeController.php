<?php

namespace App\Http\Controllers;

use App\Models\Act;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('dashboard');
        }

        $acts = Act::query()
            ->with('user')
            ->withEngagementCounts()
            ->limit(10)
            ->get();

        return view('welcome', compact('acts'));
    }
}
