<?php

namespace App\Presentation\Message\Controllers;

use App\Application\Message\DTOs\MakeMessagesIsReadDTO;
use App\Application\Message\Jobs\MakeMessagesIsReadJob;
use App\Common\Controllers\Controller;
use App\Presentation\Message\Requests\MakeIsReadRequest;

class MarkIsReadController extends Controller
{
    public function __invoke(MakeIsReadRequest $request)
    {
        $dto = MakeMessagesIsReadDTO::fromRequest($request);
        MakeMessagesIsReadJob::dispatch($dto);
        return response()->noContent();
    }
}
