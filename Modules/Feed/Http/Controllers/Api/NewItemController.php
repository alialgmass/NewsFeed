<?php

namespace Modules\Feed\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Feed\Events\NewItemReaded;
use Modules\Feed\Models\NewItem;

class NewItemController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $news = NewItem::query()
            ->join('interst_categories', function ($join) use ($user) {
                $join->on(
                    'new_items.new_category_id',
                    '=',
                    'interst_categories.new_category_id'
                )
                    ->where('interst_categories.user_id', $user->id);
            })
            ->orderByDesc('interst_categories.level')
            ->orderByDesc('new_items.published_at')
            ->select('new_items.*')
            ->paginate();

        return $this->apiBody([
            'news' => $news,
        ])->apiResponse();
    }

   public function show(NewItem $feed)
   {
       event(new NewItemReaded($feed->new_category_id,auth()->user()));
     return $this->apiBody([
         'news'=> $feed,
         'user_intersts'=>auth()->user()->interestCategories
     ])->apiResponse();
   }
}
