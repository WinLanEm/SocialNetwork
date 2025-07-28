<?php

namespace App\Presentation\User\Controllers;

use App\Application\User\Jobs\UpdateLastSeenInDatabaseJob;
use App\Common\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdateLastSeenController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        UpdateLastSeenInDatabaseJob::dispatch($user,$request->input('last_seen'));
        return response()->noContent();
    }
}
