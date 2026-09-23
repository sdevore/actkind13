<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarkdownPagesController extends Controller
{
    public function show(Request $request): View
    {
        $slug = $request->route()->getName();

        return view('markdown', [
            'title' => Str::title($slug),
            'content' => Str::markdown(file_get_contents(resource_path("markdown/{$slug}.md"))),
        ]);
    }
}
