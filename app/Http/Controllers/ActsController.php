<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActIndexRequest;
use App\Models\Act;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActsController extends Controller
{
    public function index(ActIndexRequest $request): View
    {
        $query = Act::query()
            ->withEngagementCounts()
            ->newestFirst();

        if ($request->user()) {
            $query->with(['user', 'appreciates']);
        }

        $acts = $request->paginate($query, defaultPerPage: 20);

        return view('acts.index', compact('acts'));
    }

    public function mine(ActIndexRequest $request): View
    {
        $query = $request->user()
            ->acts()
            ->with(['user', 'appreciates'])
            ->withEngagementCounts()
            ->newestFirst();

        $acts = $request->paginate($query, defaultPerPage: 20);

        return view('acts.mine', compact('acts'));
    }

    public function show(Request $request, Act $act): View
    {
        return view('acts.show', compact('act'));
    }
}
