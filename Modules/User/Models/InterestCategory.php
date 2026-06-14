<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class InterestCategory extends Model
{
    protected $table = 'interest_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'new_category_id',
        'user_id',
        'level',
    ];
}
