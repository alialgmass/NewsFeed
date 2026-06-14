<?php

namespace Modules\Search\Models;

use Illuminate\Database\Eloquent\Model;

class SearchTerm extends Model
{
    protected $fillable = ['term', 'frequency'];

    public function getTable(): string
    {
        return 'search_terms';
    }
}
