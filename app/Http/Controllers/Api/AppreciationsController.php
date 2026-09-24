<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appreciate;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

#[Group('Appreciate')]
class AppreciationsController extends Controller
{
    public function destroy(Appreciate $appreciation): Response
    {
        Gate::authorize('delete', $appreciation);

        $appreciation->delete();

        return response()->noContent();
    }
}
