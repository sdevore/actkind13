<?php

namespace App\Http\Controllers;

use App\Models\Act;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
        // if the user is logged in, redirect to dashboard
        if ($request->user()) {
            return redirect()->route('dashboard');
        }
        // get a random set of acts to display on the welcome page
        $acts = Act::with(['appreciates'])->limit(10)->get();

        return view('welcome', compact('acts'));
    }
}
