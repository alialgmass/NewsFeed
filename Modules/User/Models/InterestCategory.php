<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;


class InterestCategory extends Model
{
protected $table = 'interst_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'new_category_id',
        'user_id',
        'level',
    ];


}
