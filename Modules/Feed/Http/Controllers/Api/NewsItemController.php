<?php

namespace Modules\Feed\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use Modules\Feed\Models\NewsItem;
use Modules\Feed\Services\NewsItemService;

class NewsItemController extends ApiController
{
    public function __construct(private NewsItemService $newsItemService)
    {
        parent::__construct();
    }

    public function index()
    {
        $news = $this->newsItemService->getPaginatedFeed(
            user: $this->user,
            perPage: $this->perPage
        );

        return $this->apiBody([
            'news' => $news,
        ])->apiResponse();
    }

    public function show(NewsItem $newsItem)
    {
        return $this->apiBody(
            $this->newsItemService->getNewsItemDetail($newsItem, $this->user)
        )->apiResponse();
    }
}
