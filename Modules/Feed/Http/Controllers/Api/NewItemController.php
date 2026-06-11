<?php

namespace Modules\Feed\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Modules\Feed\Models\NewItem;
use Modules\Feed\Services\NewItemService;

class NewItemController extends ApiController
{
    public function __construct(private NewItemService $feedService)
    {
        parent::__construct();
    }

    public function index()
    {
        $news = $this->feedService->getPaginatedFeed(
            user: $this->user,
            perPage: $this->perPage
        );

        return $this->apiBody([
            'news' => $news,
        ])->apiResponse();
    }

    public function show(NewItem $feed)
    {
        return $this->apiBody(
            $this->feedService->getNewItemDetail($feed, $this->user)
        )->apiResponse();
    }
}
