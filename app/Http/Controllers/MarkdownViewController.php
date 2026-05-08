<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarkdownViewController extends Controller
{
    public function about(Request $request): View
    {
        $slug = $request->route()->getName();
        $title = Str::title(str_replace('.', ' ', $slug));
        $markdownFile = resource_path('markdown/'.$slug.'.md');

        return view('markdown', [
            'constent' => Str::markdown(file_get_contents($markdownFile)),
        ]);
    }

    public function show(Request $request): View
    {
        $slug = $request->route()->getName();
        $title = Str::title(str_replace('.', ' ', $slug));
        $markdownFile = resource_path('markdown/'.$slug.'.md');

        return view('markdown', [
            'title' => $title,
            'content' => Str::markdown(file_get_contents($markdownFile)),
        ]);
    }
}
