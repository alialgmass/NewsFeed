<?php

namespace Modules\Search\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use Modules\Search\Http\Requests\AutocompleteRequest;
use Modules\Search\Services\AutocompleteService;
use Modules\Search\Services\SearchTracker;

class AutocompleteController extends ApiController
{
    public function __construct(
        protected AutocompleteService $autocompleteService,
        protected SearchTracker $searchTracker,
    ) {}

    public function suggest(AutocompleteRequest $request)
    {
        $results = $this->autocompleteService->suggest(
            $request->string('q')->value(),
            $request->integer('limit', 10)
        );

        return $this->apiBody()
            ->apiBody($results)
            ->apiResponse();
    }

    public function track(AutocompleteRequest $request)
    {
        $this->searchTracker->record(
            $request->string('q')->value()
        );

        return $this->apiBody()
            ->apiMessage('Search term recorded')
            ->apiResponse();
    }
}
